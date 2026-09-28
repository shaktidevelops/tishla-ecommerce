from decimal import Decimal
from typing import Any

from ..db import get_connection


def _serialize_decimal(value: Any):
    if isinstance(value, Decimal):
        return float(value)
    return value


def list_products(
    q: str | None = None,
    shoot_type: str | None = None,
    fabric: str | None = None,
    price_min: float | None = None,
    price_max: float | None = None,
    limit: int = 60,
    offset: int = 0,
):
    conditions = ["status = 'active'"]
    params: list[Any] = []

    if q:
        conditions.append("(search_document @@ plainto_tsquery('simple', %s) OR name ILIKE %s OR sku ILIKE %s)")
        params.extend([q, f"%{q}%", f"%{q}%"])
    if shoot_type and shoot_type != "all":
        conditions.append("shoot_type = %s")
        params.append(shoot_type)
    if fabric and fabric != "all":
        conditions.append("fabric ILIKE %s")
        params.append(f"%{fabric}%")
    if price_min is not None:
        conditions.append("COALESCE(base_price, 0) >= %s")
        params.append(price_min)
    if price_max is not None:
        conditions.append("COALESCE(base_price, 0) <= %s")
        params.append(price_max)

    sql = f"""
        SELECT *
        FROM store.v_catalogue_products
        WHERE {' AND '.join(conditions)}
        ORDER BY featured DESC, name ASC
        LIMIT %s OFFSET %s
    """
    params.extend([limit, offset])

    with get_connection() as conn:
        with conn.cursor() as cur:
            cur.execute(sql, params)
            rows = cur.fetchall()

    return _normalize_rows(rows)


def get_product(identifier: str):
    with get_connection() as conn:
        with conn.cursor() as cur:
            cur.execute(
                """
                SELECT *
                FROM store.v_catalogue_products
                WHERE sku = %s OR slug = %s
                LIMIT 1
                """,
                (identifier, identifier),
            )
            row = cur.fetchone()

            if not row:
                return None

            cur.execute(
                """
                SELECT v.id, v.sku, v.name, v.color_name, v.color_hex,
                       COALESCE(vp.price, v.price, p.base_price) AS price,
                       COALESCE(SUM(inv.available_quantity), 0)::int AS available_quantity
                FROM store.product_variants v
                JOIN store.products p ON p.id = v.product_id
                LEFT JOIN store.price_lists pl
                       ON pl.code = 'RETAIL' AND pl.is_active = true
                LEFT JOIN store.variant_prices vp
                       ON vp.variant_id = v.id AND vp.price_list_id = pl.id
                LEFT JOIN store.v_variant_inventory inv
                       ON inv.variant_id = v.id
                WHERE p.id = %s AND v.is_active = true
                GROUP BY v.id, p.base_price, vp.price
                ORDER BY v.name
                """,
                (row["id"],),
            )
            row["variants"] = cur.fetchall()

    return _normalize_rows([row])[0]


def get_fabrics():
    with get_connection() as conn:
        with conn.cursor() as cur:
            cur.execute(
                """
                SELECT DISTINCT fabric
                FROM store.products
                WHERE status = 'active' AND fabric IS NOT NULL AND btrim(fabric) <> ''
                ORDER BY fabric
                """
            )
            return [r["fabric"] for r in cur.fetchall()]


def _normalize_rows(rows):
    normalized = []
    for row in rows:
        item = dict(row)
        for key, value in list(item.items()):
            if isinstance(value, Decimal):
                item[key] = float(value)
        normalized.append(item)
    return normalized
