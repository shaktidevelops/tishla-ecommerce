"""Validate and import the legacy Tishla catalogue CSV into PostgreSQL.

The importer is intentionally two-phase:

1. Default/--preview mode validates the entire CSV and produces a report.
2. --apply performs the database changes in one transaction after validation.

Supported legacy fields include SKU/design code, catalogue name, shoot type,
fabric/material, product type, price, colours, stock, MOQ, Drive folder link,
and image URLs. Header matching is case-insensitive and tolerant of punctuation.

Examples:
    python backend/scripts/import_catalogue_csv.py data/catalogue.csv
    python backend/scripts/import_catalogue_csv.py data/catalogue.csv --apply
    python backend/scripts/import_catalogue_csv.py data/catalogue.csv --apply --strict
    python backend/scripts/import_catalogue_csv.py data/catalogue.csv --report data/import_reports/catalogue.json
"""

from __future__ import annotations

import argparse
import csv
import hashlib
import json
import re
import sys
import uuid
from dataclasses import asdict, dataclass, field
from decimal import Decimal, InvalidOperation
from pathlib import Path
from typing import Any, Iterable

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))

from app.core.config import get_settings
from app.db import close_pool, get_connection, open_pool

settings = get_settings()


HEADER_ALIASES: dict[str, list[str]] = {
    "sku": ["sku", "design code", "design code / sku", "product code", "item code", "style code"],
    "name": ["catalogue name", "catalog name", "product name", "name", "title"],
    "shoot_type": ["shoot type", "shoot", "catalogue type", "photo type"],
    "fabric": ["fabric / material", "fabric", "material", "fabric name"],
    "product_type": ["outfit / work type", "product type", "type", "work type", "outfit type"],
    "price": ["price (₹)", "price (inr)", "price", "selling price", "retail price", "mrp"],
    "colors": ["available colors", "available colours", "colors", "colours", "color variations", "colour variations"],
    "stock": ["stock", "on hand", "on_hand", "quantity", "qty", "available quantity", "inventory"],
    "moq": ["moq", "minimum order qty", "minimum order quantity", "min order qty"],
    "folder_link": ["drive folder link", "drive folder", "folder link", "google drive folder", "folder"],
    "images": ["all images", "images", "image urls", "image links", "photos", "photo urls"],
}


@dataclass
class Issue:
    row_number: int
    severity: str
    code: str
    message: str
    raw_row: dict[str, str] = field(default_factory=dict)


@dataclass
class ProductRow:
    row_number: int
    sku: str
    name: str
    shoot_type: str
    fabric: str
    product_type: str
    base_price: Decimal | None
    colors: list[str]
    stock: int | None
    moq: int
    folder_link: str | None
    images: list[str]
    category_slug: str
    raw: dict[str, str]


@dataclass
class ImportStats:
    total_rows: int = 0
    valid_rows: int = 0
    created_products: int = 0
    updated_products: int = 0
    created_variants: int = 0
    updated_variants: int = 0
    created_media: int = 0
    reused_media: int = 0
    created_images: int = 0
    skipped_rows: int = 0
    error_rows: int = 0
    warning_rows: int = 0


def normalize_header(value: str) -> str:
    return re.sub(r"[^a-z0-9]+", " ", (value or "").strip().lower()).strip()


def normalize_text(value: str | None) -> str:
    value = value or ""
    value = value.replace("\u00a0", " ")
    value = re.sub(r"\s+", " ", value).strip()
    value = re.sub(r"^\*+|\*+$", "", value).strip()
    return value


def find_column(headers: list[str], aliases: Iterable[str]) -> int | None:
    normalized = [normalize_header(h) for h in headers]
    alias_list = [normalize_header(a) for a in aliases]
    # Prefer exact matches.
    for alias in alias_list:
        for i, header in enumerate(normalized):
            if header == alias:
                return i
    # Then accept contained phrases.
    for alias in alias_list:
        for i, header in enumerate(normalized):
            if alias and alias in header:
                return i
    return None


def clean_price(value: str | None) -> Decimal | None:
    value = normalize_text(value)
    if not value:
        return None
    # First numeric token, accepting Indian comma formatting.
    match = re.search(r"\d[\d,\.]*", value)
    if not match:
        return None
    token = match.group(0).replace(",", "")
    try:
        amount = Decimal(token)
    except InvalidOperation:
        return None
    if amount < 0 or amount > Decimal("99999999.99"):
        return None
    return amount.quantize(Decimal("0.01"))


def clean_int(value: str | None, *, minimum: int = 0, maximum: int = 10_000_000) -> int | None:
    value = normalize_text(value)
    if not value:
        return None
    match = re.search(r"-?\d[\d,]*", value)
    if not match:
        return None
    try:
        result = int(match.group(0).replace(",", ""))
    except ValueError:
        return None
    if result < minimum or result > maximum:
        return None
    return result


def slugify(value: str) -> str:
    value = normalize_text(value).lower()
    value = re.sub(r"[^a-z0-9]+", "-", value).strip("-")
    return (value[:120] or "product").rstrip("-")


def split_values(value: str | None) -> list[str]:
    if not value:
        return []
    raw = value.replace("\r", "\n")
    raw = raw.replace("|", "\n").replace(";", "\n")
    pieces: list[str] = []
    for part in raw.split("\n"):
        # Commas are treated as separators here because legacy colour/image cells are list-like.
        pieces.extend(part.split(","))
    seen: set[str] = set()
    result: list[str] = []
    for piece in pieces:
        cleaned = normalize_text(piece).strip("[]{}\"'")
        if cleaned and cleaned.lower() not in seen:
            seen.add(cleaned.lower())
            result.append(cleaned)
    return result


def extract_urls(value: str | None) -> list[str]:
    if not value:
        return []
    urls = re.findall(r"https?://[^\s<>{}\[\]\"']+", value)
    cleaned: list[str] = []
    seen: set[str] = set()
    for url in urls:
        url = url.rstrip(".,;)")
        if url not in seen:
            seen.add(url)
            cleaned.append(url)
    if cleaned:
        return cleaned
    # Fall back to legacy list-style cells where URLs are separated by commas/newlines.
    return [x for x in split_values(value) if x.startswith(("http://", "https://"))]


def category_for_fabric(fabric: str) -> str:
    text = fabric.lower()
    if "dola" in text:
        return "dola-silk"
    if "georgette" in text:
        return "georgette"
    if "organza" in text:
        return "organza"
    if "cotton" in text or "linen" in text:
        return "cotton-linen"
    return "sarees"


def parse_csv(path: Path) -> tuple[list[str], list[ProductRow], list[Issue], str]:
    raw_bytes = path.read_bytes()
    checksum = hashlib.sha256(raw_bytes).hexdigest()
    text = raw_bytes.decode("utf-8-sig", errors="replace")

    sample = text[:10_000]
    try:
        dialect = csv.Sniffer().sniff(sample, delimiters=",;\t|")
    except csv.Error:
        dialect = csv.excel

    rows = list(csv.reader(text.splitlines(), dialect))
    if not rows:
        raise ValueError("CSV is empty.")
    if len(rows) == 1:
        raise ValueError("CSV contains a header but no data rows.")

    headers = [normalize_text(h) for h in rows[0]]
    indexes = {field: find_column(headers, aliases) for field, aliases in HEADER_ALIASES.items()}
    if indexes["name"] is None:
        raise ValueError("Required 'Catalogue Name' column could not be identified.")
    if indexes["sku"] is None:
        raise ValueError("Required 'Design Code / SKU' column could not be identified.")

    products: list[ProductRow] = []
    issues: list[Issue] = []
    seen_skus: dict[str, int] = {}
    seen_names: dict[str, int] = {}

    def get(row: list[str], field: str) -> str:
        idx = indexes[field]
        return normalize_text(row[idx]) if idx is not None and idx < len(row) else ""

    for csv_row_number, row in enumerate(rows[1:], start=2):
        raw = {headers[i]: normalize_text(row[i]) if i < len(row) else "" for i in range(len(headers))}
        # Completely blank rows are ignored, not counted as errors.
        if not any(raw.values()):
            continue

        sku = get(row, "sku")
        name = get(row, "name")
        if not sku:
            issues.append(Issue(csv_row_number, "error", "MISSING_SKU", "Design Code / SKU is required.", raw))
            continue
        if not name:
            issues.append(Issue(csv_row_number, "error", "MISSING_NAME", "Catalogue Name is required.", raw))
            continue

        sku_key = sku.casefold()
        name_key = name.casefold()
        if sku_key in seen_skus:
            issues.append(Issue(csv_row_number, "error", "DUPLICATE_SKU_IN_FILE", f"SKU duplicates row {seen_skus[sku_key]} in the same file.", raw))
            continue
        seen_skus[sku_key] = csv_row_number
        if name_key in seen_names:
            issues.append(Issue(csv_row_number, "warning", "DUPLICATE_NAME_IN_FILE", f"Catalogue name duplicates row {seen_names[name_key]} in the same file.", raw))
        else:
            seen_names[name_key] = csv_row_number

        shoot = get(row, "shoot_type") or "Model Shoot"
        fabric = get(row, "fabric") or "Saree"
        product_type = get(row, "product_type") or "Designer Saree"
        base_price = clean_price(get(row, "price"))
        if get(row, "price") and base_price is None:
            issues.append(Issue(csv_row_number, "error", "INVALID_PRICE", "Price is present but could not be parsed as a valid non-negative amount.", raw))
            continue
        if base_price is None:
            issues.append(Issue(csv_row_number, "warning", "MISSING_PRICE", "No price supplied; product will import with a null base price.", raw))

        colors = split_values(get(row, "colors"))
        if not colors:
            colors = ["Default"]
            issues.append(Issue(csv_row_number, "warning", "MISSING_COLORS", "No colour list supplied; a Default variant will be created.", raw))

        stock_raw = get(row, "stock")
        stock = clean_int(stock_raw, minimum=0)
        if stock_raw and stock is None:
            issues.append(Issue(csv_row_number, "error", "INVALID_STOCK", "Stock is present but could not be parsed as a non-negative integer.", raw))
            continue

        moq_raw = get(row, "moq")
        moq = clean_int(moq_raw, minimum=1, maximum=100_000) if moq_raw else 1
        if moq is None:
            issues.append(Issue(csv_row_number, "error", "INVALID_MOQ", "MOQ must be a positive integer.", raw))
            continue

        images = extract_urls(get(row, "images"))
        folder_link = get(row, "folder_link") or None
        if folder_link and not folder_link.startswith(("http://", "https://")):
            issues.append(Issue(csv_row_number, "warning", "INVALID_FOLDER_LINK", "Drive folder link is not an HTTP(S) URL and will be stored only as metadata.", raw))

        products.append(
            ProductRow(
                row_number=csv_row_number,
                sku=sku,
                name=name,
                shoot_type=shoot,
                fabric=fabric,
                product_type=product_type,
                base_price=base_price,
                colors=colors,
                stock=stock,
                moq=moq,
                folder_link=folder_link,
                images=images,
                category_slug=category_for_fabric(fabric),
                raw=raw,
            )
        )

    return headers, products, issues, checksum


def create_report(batch_id: uuid.UUID, source: Path, mode: str, headers: list[str], products: list[ProductRow], issues: list[Issue], stats: ImportStats, checksum: str) -> dict[str, Any]:
    return {
        "batch_id": str(batch_id),
        "source_file": str(source),
        "source_checksum_sha256": checksum,
        "mode": mode,
        "headers": headers,
        "stats": asdict(stats),
        "issues": [
            {
                "row_number": issue.row_number,
                "severity": issue.severity,
                "code": issue.code,
                "message": issue.message,
            }
            for issue in issues
        ],
        "valid_rows": [
            {
                "row_number": row.row_number,
                "sku": row.sku,
                "name": row.name,
                "category_slug": row.category_slug,
                "shoot_type": row.shoot_type,
                "fabric": row.fabric,
                "product_type": row.product_type,
                "price": str(row.base_price) if row.base_price is not None else None,
                "colors": row.colors,
                "stock": row.stock,
                "moq": row.moq,
                "image_count": len(row.images),
            }
            for row in products
        ],
    }


def db_value(value: Any) -> Any:
    if isinstance(value, Decimal):
        return value
    return value


def ensure_import_batch(cur, batch_id: uuid.UUID, source: Path, checksum: str, mode: str, stats: ImportStats, status: str, metadata: dict[str, Any] | None = None) -> None:
    cur.execute(
        """
        INSERT INTO store.catalogue_import_batches(
            id, source_filename, source_type, source_checksum_sha256, mode, status,
            total_rows, valid_rows, created_products, updated_products,
            created_variants, updated_variants, created_media, reused_media,
            created_images, skipped_rows, error_rows, warning_rows,
            completed_at, metadata
        )
        VALUES(
            %s,%s,'csv',%s,%s,%s,
            %s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,
            CASE WHEN %s IN ('previewed','completed','completed_with_errors','failed') THEN now() ELSE NULL END,
            %s
        )
        ON CONFLICT (id) DO UPDATE SET
            status=EXCLUDED.status,
            total_rows=EXCLUDED.total_rows,
            valid_rows=EXCLUDED.valid_rows,
            created_products=EXCLUDED.created_products,
            updated_products=EXCLUDED.updated_products,
            created_variants=EXCLUDED.created_variants,
            updated_variants=EXCLUDED.updated_variants,
            created_media=EXCLUDED.created_media,
            reused_media=EXCLUDED.reused_media,
            created_images=EXCLUDED.created_images,
            skipped_rows=EXCLUDED.skipped_rows,
            error_rows=EXCLUDED.error_rows,
            warning_rows=EXCLUDED.warning_rows,
            completed_at=EXCLUDED.completed_at,
            metadata=EXCLUDED.metadata
        """,
        (
            batch_id,
            source.name,
            checksum,
            mode,
            status,
            stats.total_rows,
            stats.valid_rows,
            stats.created_products,
            stats.updated_products,
            stats.created_variants,
            stats.updated_variants,
            stats.created_media,
            stats.reused_media,
            stats.created_images,
            stats.skipped_rows,
            stats.error_rows,
            stats.warning_rows,
            status,
            json.dumps(metadata or {}),
        ),
    )


def record_issues(cur, batch_id: uuid.UUID, issues: list[Issue]) -> None:
    for issue in issues:
        cur.execute(
            """
            INSERT INTO store.catalogue_import_issues(batch_id,row_number,severity,issue_code,message,raw_row)
            VALUES(%s,%s,%s,%s,%s,%s::jsonb)
            """,
            (batch_id, issue.row_number, issue.severity, issue.code, issue.message, json.dumps(issue.raw_row)),
        )


def ensure_media(cur, url: str, alt_text: str, stats: ImportStats) -> uuid.UUID:
    cur.execute(
        "SELECT id FROM store.media_assets WHERE public_url=%s ORDER BY created_at LIMIT 1",
        (url,),
    )
    found = cur.fetchone()
    if found:
        stats.reused_media += 1
        return found["id"]
    cur.execute(
        """
        INSERT INTO store.media_assets(storage_provider,public_url,alt_text,metadata)
        VALUES('legacy_drive',%s,%s,%s::jsonb)
        RETURNING id
        """,
        (url, alt_text, json.dumps({"source": "legacy_catalogue_import"})),
    )
    stats.created_media += 1
    return cur.fetchone()["id"]


def ensure_product_image(cur, product_id: uuid.UUID, variant_id: uuid.UUID | None, media_id: uuid.UUID, sort_order: int, primary: bool, image_code: str | None, stats: ImportStats) -> None:
    cur.execute(
        """
        SELECT id FROM store.product_images
        WHERE product_id=%s AND media_asset_id=%s
          AND (variant_id=%s OR (variant_id IS NULL AND %s IS NULL))
        LIMIT 1
        """,
        (product_id, media_id, variant_id, variant_id),
    )
    found = cur.fetchone()
    if found:
        cur.execute(
            """
            UPDATE store.product_images
            SET sort_order=%s,is_primary=%s,image_code=%s
            WHERE id=%s
            """,
            (sort_order, primary, image_code, found["id"]),
        )
        return
    cur.execute(
        """
        INSERT INTO store.product_images(product_id,variant_id,media_asset_id,sort_order,is_primary,image_code)
        VALUES(%s,%s,%s,%s,%s,%s)
        """,
        (product_id, variant_id, media_id, sort_order, primary, image_code),
    )
    stats.created_images += 1


def get_or_create_category(cur, slug: str) -> uuid.UUID:
    cur.execute("SELECT id FROM store.categories WHERE slug=%s LIMIT 1", (slug,))
    row = cur.fetchone()
    if row:
        return row["id"]
    # Defensive fallback if a custom fabric category is encountered later.
    name_map = {
        "sarees": "Sarees",
        "dola-silk": "Dola Silk",
        "georgette": "Georgette",
        "organza": "Organza",
        "cotton-linen": "Cotton / Linen",
    }
    cur.execute(
        """
        INSERT INTO store.categories(name,slug,description,sort_order)
        VALUES(%s,%s,%s,99)
        RETURNING id
        """,
        (name_map.get(slug, slug.replace("-", " ").title()), slug, f"Imported {slug} catalogue category"),
    )
    return cur.fetchone()["id"]


def get_retail_price_list(cur) -> uuid.UUID:
    cur.execute("SELECT id FROM store.price_lists WHERE code='RETAIL' LIMIT 1")
    row = cur.fetchone()
    if not row:
        cur.execute(
            """
            INSERT INTO store.price_lists(code,name,customer_type,priority,currency,is_active)
            VALUES('RETAIL','Retail Prices','retail',10,'INR',true)
            RETURNING id
            """
        )
        return cur.fetchone()["id"]
    return row["id"]


def import_rows(rows: list[ProductRow], issues: list[Issue], source: Path, checksum: str, batch_id: uuid.UUID, mode: str, strict: bool) -> ImportStats:
    stats = ImportStats(total_rows=len(rows) + len({i.row_number for i in issues if i.severity == 'error'}))
    stats.valid_rows = len(rows)
    stats.error_rows = len({i.row_number for i in issues if i.severity == "error"})
    stats.warning_rows = len({i.row_number for i in issues if i.severity == "warning"})
    stats.skipped_rows = stats.error_rows

    open_pool()
    try:
        with get_connection() as conn:
            with conn.transaction():
                with conn.cursor() as cur:
                    ensure_import_batch(cur, batch_id, source, checksum, mode, stats, "started")
                    record_issues(cur, batch_id, issues)

                    if mode == "preview":
                        ensure_import_batch(cur, batch_id, source, checksum, mode, stats, "previewed", {"strict": strict})
                        return stats

                    if strict and stats.error_rows:
                        ensure_import_batch(cur, batch_id, source, checksum, mode, stats, "failed", {"reason": "strict_validation_failed"})
                        raise ValueError(f"Strict mode blocked import because {stats.error_rows} row(s) contain errors.")

                    cur.execute("SELECT id FROM store.brands WHERE slug='tishla' LIMIT 1")
                    brand = cur.fetchone()
                    if not brand:
                        cur.execute("INSERT INTO store.brands(name,slug) VALUES('Tishla','tishla') RETURNING id")
                        brand_id = cur.fetchone()["id"]
                    else:
                        brand_id = brand["id"]

                    location_id = None
                    cur.execute("SELECT id FROM store.inventory_locations WHERE code='SURAT_MAIN' LIMIT 1")
                    location = cur.fetchone()
                    if location:
                        location_id = location["id"]
                    else:
                        cur.execute(
                            "INSERT INTO store.inventory_locations(code,name,address) VALUES('SURAT_MAIN','Surat Main Warehouse','Surat, Gujarat, India') RETURNING id"
                        )
                        location_id = cur.fetchone()["id"]

                    retail_price_list_id = get_retail_price_list(cur)

                    error_rows = {issue.row_number for issue in issues if issue.severity == "error"}
                    for product in rows:
                        if product.row_number in error_rows:
                            continue

                        category_id = get_or_create_category(cur, product.category_slug)
                        product_slug = slugify(f"{product.sku}-{product.name}")
                        metadata = {
                            "source": "legacy_google_sheet",
                            "import_batch_id": str(batch_id),
                            "folder_link": product.folder_link,
                        }

                        cur.execute("SELECT id FROM store.products WHERE sku=%s", (product.sku,))
                        existing = cur.fetchone()
                        if existing:
                            product_id = existing["id"]
                            cur.execute(
                                """
                                UPDATE store.products
                                SET slug=%s,name=%s,fabric=%s,product_type=%s,shoot_type=%s,
                                    base_price=%s,gst_rate=%s,category_id=%s,brand_id=%s,
                                    min_order_qty=%s,status='active',
                                    metadata=metadata || %s::jsonb
                                WHERE id=%s
                                """,
                                (
                                    product_slug,
                                    product.name,
                                    product.fabric,
                                    product.product_type,
                                    product.shoot_type,
                                    db_value(product.base_price),
                                    settings.default_gst_rate,
                                    category_id,
                                    brand_id,
                                    product.moq,
                                    json.dumps(metadata),
                                    product_id,
                                ),
                            )
                            stats.updated_products += 1
                        else:
                            cur.execute(
                                """
                                INSERT INTO store.products(
                                    sku,slug,name,fabric,product_type,shoot_type,base_price,
                                    gst_rate,brand_id,category_id,status,min_order_qty,metadata
                                )
                                VALUES(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,'active',%s,%s::jsonb)
                                RETURNING id
                                """,
                                (
                                    product.sku,
                                    product_slug,
                                    product.name,
                                    product.fabric,
                                    product.product_type,
                                    product.shoot_type,
                                    db_value(product.base_price),
                                    settings.default_gst_rate,
                                    brand_id,
                                    category_id,
                                    product.moq,
                                    json.dumps(metadata),
                                ),
                            )
                            product_id = cur.fetchone()["id"]
                            stats.created_products += 1

                        # Ensure variants. Existing variants are preserved; legacy colours are the desired list.
                        variant_ids: list[uuid.UUID] = []
                        for index, color in enumerate(product.colors):
                            suffix = re.sub(r"[^A-Za-z0-9]+", "", color).upper()[:18]
                            variant_sku = product.sku if len(product.colors) == 1 else f"{product.sku}-{suffix or index + 1}"
                            variant_name = color
                            cur.execute("SELECT id FROM store.product_variants WHERE sku=%s", (variant_sku,))
                            existing_variant = cur.fetchone()
                            if existing_variant:
                                variant_id = existing_variant["id"]
                                cur.execute(
                                    """
                                    UPDATE store.product_variants
                                    SET product_id=%s,name=%s,color_name=%s,price=%s,is_active=true
                                    WHERE id=%s
                                    """,
                                    (product_id, variant_name, color, db_value(product.base_price), variant_id),
                                )
                                stats.updated_variants += 1
                            else:
                                cur.execute(
                                    """
                                    INSERT INTO store.product_variants(product_id,sku,name,color_name,price,is_active)
                                    VALUES(%s,%s,%s,%s,%s,true)
                                    RETURNING id
                                    """,
                                    (product_id, variant_sku, variant_name, color, db_value(product.base_price)),
                                )
                                variant_id = cur.fetchone()["id"]
                                stats.created_variants += 1
                            variant_ids.append(variant_id)

                            # Retail list always mirrors the imported variant price when one exists.
                            if product.base_price is not None:
                                cur.execute(
                                    """
                                    INSERT INTO store.variant_prices(price_list_id,variant_id,price)
                                    VALUES(%s,%s,%s)
                                    ON CONFLICT(price_list_id,variant_id) DO UPDATE SET price=EXCLUDED.price
                                    """,
                                    (retail_price_list_id, variant_id, db_value(product.base_price)),
                                )

                            cur.execute(
                                """
                                SELECT on_hand FROM store.inventory_stock
                                WHERE location_id=%s AND variant_id=%s
                                FOR UPDATE
                                """,
                                (location_id, variant_id),
                            )
                            stock_row = cur.fetchone()
                            if stock_row is None:
                                opening = product.stock or 0
                                cur.execute(
                                    """
                                    INSERT INTO store.inventory_stock(location_id,variant_id,on_hand,reserved,reorder_level)
                                    VALUES(%s,%s,%s,0,0)
                                    """,
                                    (location_id, variant_id, opening),
                                )
                                if opening:
                                    cur.execute(
                                        """
                                        INSERT INTO store.inventory_movements(
                                            location_id,variant_id,movement_type,quantity,reference_type,reference_id,reason,metadata
                                        )
                                        VALUES(%s,%s,'opening',%s,'catalogue_import',%s,'Initial stock from legacy catalogue import',%s::jsonb)
                                        """,
                                        (location_id, variant_id, opening, batch_id, json.dumps({"row_number": product.row_number})),
                                    )
                            elif product.stock is not None:
                                current = int(stock_row["on_hand"])
                                delta = product.stock - current
                                if delta:
                                    cur.execute(
                                        """
                                        UPDATE store.inventory_stock
                                        SET on_hand=%s,updated_at=now()
                                        WHERE location_id=%s AND variant_id=%s
                                        """,
                                        (product.stock, location_id, variant_id),
                                    )
                                    cur.execute(
                                        """
                                        INSERT INTO store.inventory_movements(
                                            location_id,variant_id,movement_type,quantity,reference_type,reference_id,reason,metadata
                                        )
                                        VALUES(%s,%s,'adjustment',%s,'catalogue_import',%s,'Stock synchronized from legacy catalogue import',%s::jsonb)
                                        """,
                                        (location_id, variant_id, delta, batch_id, json.dumps({"row_number": product.row_number, "old": current, "new": product.stock})),
                                    )

                        # Poster/product gallery: first image is primary at product level.
                        for image_index, image_url in enumerate(product.images):
                            media_id = ensure_media(cur, image_url, f"{product.name} - Image {image_index + 1}", stats)
                            if image_index == 0:
                                ensure_product_image(cur, product_id, None, media_id, 0, True, "000", stats)
                            elif image_index - 1 < len(variant_ids):
                                variant_id = variant_ids[image_index - 1]
                                ensure_product_image(cur, product_id, variant_id, media_id, image_index, False, product.colors[image_index - 1], stats)
                            else:
                                ensure_product_image(cur, product_id, None, media_id, image_index, False, None, stats)

                    final_status = "completed_with_errors" if stats.error_rows else "completed"
                    ensure_import_batch(cur, batch_id, source, checksum, mode, stats, final_status, {"strict": strict})
                    return stats
    finally:
        close_pool()


def write_report(path: Path, report: dict[str, Any]) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text(json.dumps(report, indent=2, ensure_ascii=False), encoding="utf-8")


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Validate/import Tishla legacy catalogue CSV")
    parser.add_argument("csv_path", type=Path)
    parser.add_argument("--apply", action="store_true", help="Write validated rows to PostgreSQL. Without this flag the importer is preview-only.")
    parser.add_argument("--strict", action="store_true", help="Abort --apply when any validation errors exist.")
    parser.add_argument("--report", type=Path, default=None, help="JSON report path. Defaults to data/import_reports/<file>-<batch>.json")
    return parser.parse_args()


def main() -> int:
    args = parse_args()
    csv_path = args.csv_path.resolve()
    if not csv_path.exists():
        print(f"ERROR: file not found: {csv_path}", file=sys.stderr)
        return 2

    try:
        headers, products, issues, checksum = parse_csv(csv_path)
    except Exception as exc:
        print(f"ERROR: {exc}", file=sys.stderr)
        return 2

    batch_id = uuid.uuid4()
    stats = ImportStats(
        total_rows=len(products) + len({i.row_number for i in issues if i.severity == 'error'}),
        valid_rows=len(products),
        error_rows=len({i.row_number for i in issues if i.severity == 'error'}),
        warning_rows=len({i.row_number for i in issues if i.severity == 'warning'}),
        skipped_rows=len({i.row_number for i in issues if i.severity == 'error'}),
    )

    if args.apply:
        try:
            stats = import_rows(products, issues, csv_path, checksum, batch_id, "apply", args.strict)
        except Exception as exc:
            print(f"ERROR: import failed: {exc}", file=sys.stderr)
            return 1
    else:
        try:
            stats = import_rows(products, issues, csv_path, checksum, batch_id, "preview", args.strict)
        except Exception as exc:
            print(f"ERROR: preview failed: {exc}", file=sys.stderr)
            return 1

    report = create_report(batch_id, csv_path, "apply" if args.apply else "preview", headers, products, issues, stats, checksum)
    report_path = args.report or (Path("data") / "import_reports" / f"catalogue-{batch_id}.json")
    write_report(report_path, report)

    print("\n=== TISHLA CATALOGUE IMPORT ===")
    print(f"Batch ID       : {batch_id}")
    print(f"Mode           : {'APPLY' if args.apply else 'PREVIEW'}")
    print(f"Source         : {csv_path}")
    print(f"Rows detected  : {stats.total_rows}")
    print(f"Valid rows     : {stats.valid_rows}")
    print(f"Errors         : {stats.error_rows}")
    print(f"Warnings       : {stats.warning_rows}")
    if args.apply:
        print(f"Products +     : {stats.created_products}")
        print(f"Products ~     : {stats.updated_products}")
        print(f"Variants +     : {stats.created_variants}")
        print(f"Variants ~     : {stats.updated_variants}")
        print(f"Media +        : {stats.created_media}")
        print(f"Media reused   : {stats.reused_media}")
        print(f"Images +       : {stats.created_images}")
    print(f"Report         : {report_path.resolve()}")

    if issues:
        print("\nValidation issues:")
        for issue in issues[:25]:
            print(f"  row {issue.row_number}: [{issue.severity.upper()}] {issue.code} — {issue.message}")
        if len(issues) > 25:
            print(f"  ... and {len(issues) - 25} more; see the JSON report.")

    if args.strict and stats.error_rows:
        return 1
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
