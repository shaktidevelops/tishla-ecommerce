from decimal import Decimal, ROUND_HALF_UP
from typing import Any

from ..db import get_connection


MONEY = Decimal("0.01")


def _money(value: Decimal) -> Decimal:
    return value.quantize(MONEY, rounding=ROUND_HALF_UP)


def create_order(payload: dict[str, Any]) -> dict[str, Any]:
    items = payload["items"]
    if not items:
        raise ValueError("Cart is empty.")

    with get_connection() as conn:
        with conn.transaction():
            with conn.cursor() as cur:
                customer = _upsert_customer(cur, payload)
                subtotal = Decimal("0")
                tax_total = Decimal("0")
                order_items: list[dict[str, Any]] = []

                for item in items:
                    cur.execute(
                        """
                        SELECT
                            v.id AS variant_id,
                            v.sku AS variant_sku,
                            v.name AS variant_name,
                            p.id AS product_id,
                            p.sku AS product_sku,
                            p.name AS product_name,
                            COALESCE(vp.price, v.price, p.base_price, 0) AS unit_price,
                            p.gst_rate,
                            COALESCE(
                                (SELECT SUM(available_quantity)
                                 FROM store.v_variant_inventory vi
                                 WHERE vi.variant_id = v.id), 0
                            )::int AS available_quantity,
                            (
                                SELECT ma.public_url
                                FROM store.product_images pi
                                JOIN store.media_assets ma ON ma.id = pi.media_asset_id
                                WHERE pi.product_id = p.id
                                ORDER BY pi.is_primary DESC, pi.sort_order ASC
                                LIMIT 1
                            ) AS image_url
                        FROM store.product_variants v
                        JOIN store.products p ON p.id = v.product_id
                        LEFT JOIN store.price_lists pl
                               ON pl.code = 'RETAIL' AND pl.is_active = true
                        LEFT JOIN store.variant_prices vp
                               ON vp.variant_id = v.id AND vp.price_list_id = pl.id
                        WHERE v.id = %s AND v.is_active = true AND p.status = 'active'
                        FOR UPDATE OF v
                        """,
                        (item["variant_id"],),
                    )
                    product = cur.fetchone()
                    if not product:
                        raise ValueError(f"Variant not found: {item['variant_id']}")

                    quantity = int(item["quantity"])
                    if quantity < 1:
                        raise ValueError("Quantity must be at least 1.")
                    if product["available_quantity"] < quantity:
                        raise ValueError(
                            f"Insufficient stock for {product['product_name']} / {product['variant_name']}."
                        )

                    unit_price = Decimal(str(product["unit_price"]))
                    line_subtotal = _money(unit_price * quantity)
                    rate = Decimal(str(product["gst_rate"] or 0))
                    line_tax = _money(line_subtotal * rate / Decimal("100"))

                    subtotal += line_subtotal
                    tax_total += line_tax

                    order_items.append({
                        "product_id": product["product_id"],
                        "variant_id": product["variant_id"],
                        "sku": product["variant_sku"] or product["product_sku"],
                        "product_name": product["product_name"],
                        "variant_name": product["variant_name"],
                        "quantity": quantity,
                        "unit_price": unit_price,
                        "tax_rate": rate,
                        "tax_total": line_tax,
                        "line_total": line_subtotal + line_tax,
                        "image_url": product["image_url"],
                    })

                shipping = Decimal(str(payload.get("shipping_total", 0)))
                grand_total = _money(subtotal + tax_total + shipping)

                payment_method = payload.get("payment_method", "cod")
                initial_status = "pending_payment" if payment_method == "online" else "confirmed"

                cur.execute(
                    """
                    INSERT INTO store.orders (
                        customer_id, status, customer_name, customer_email, customer_phone,
                        shipping_address, billing_address, notes, source, payment_method,
                        subtotal, taxable_total, tax_total, shipping_total, grand_total,
                        confirmed_at
                    )
                    VALUES (
                        %s,%s,%s,%s,%s,%s::jsonb,%s::jsonb,%s,%s,%s,
                        %s,%s,%s,%s,%s,
                        CASE WHEN %s = 'confirmed' THEN now() ELSE NULL END
                    )
                    RETURNING id, order_number, status, grand_total, currency
                    """,
                    (
                        customer["id"],
                        initial_status,
                        payload["customer_name"],
                        payload.get("email"),
                        payload["phone"],
                        payload["shipping_address"],
                        payload["shipping_address"],
                        payload.get("notes"),
                        payload.get("source", "web"),
                        payment_method,
                        subtotal,
                        subtotal,
                        tax_total,
                        shipping,
                        grand_total,
                        initial_status,
                    ),
                )
                order = cur.fetchone()

                for oi in order_items:
                    cur.execute(
                        """
                        INSERT INTO store.order_items (
                            order_id, product_id, variant_id, sku, product_name, variant_name,
                            quantity, unit_price, taxable_total, tax_rate, tax_total, line_total, image_url
                        )
                        VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)
                        """,
                        (
                            order["id"],
                            oi["product_id"],
                            oi["variant_id"],
                            oi["sku"],
                            oi["product_name"],
                            oi["variant_name"],
                            oi["quantity"],
                            oi["unit_price"],
                            oi["unit_price"] * oi["quantity"],
                            oi["tax_rate"],
                            oi["tax_total"],
                            oi["line_total"],
                            oi["image_url"],
                        ),
                    )

                    # Reserve stock at the first active location.
                    cur.execute(
                        """
                        UPDATE store.inventory_stock
                        SET reserved = reserved + %s, updated_at = now()
                        WHERE location_id = (
                            SELECT id FROM store.inventory_locations
                            WHERE is_active = true ORDER BY code LIMIT 1
                        )
                        AND variant_id = %s
                        AND on_hand - reserved >= %s
                        """,
                        (oi["quantity"], oi["variant_id"], oi["quantity"]),
                    )
                    if cur.rowcount != 1:
                        raise ValueError(f"Unable to reserve stock for {oi['sku']}.")

                cur.execute(
                    """
                    INSERT INTO store.payments(order_id,provider,amount,currency,status,method)
                    VALUES(%s,%s,%s,%s,%s,%s)
                    """,
                    (
                        order["id"],
                        "cod" if payment_method == "cod" else "razorpay",
                        grand_total,
                        order["currency"],
                        "pending" if payment_method == "online" else "authorized",
                        payment_method,
                    ),
                )

                return {
                    "id": str(order["id"]),
                    "order_number": order["order_number"],
                    "status": order["status"],
                    "grand_total": float(order["grand_total"]),
                    "currency": order["currency"],
                    "payment_method": payment_method,
                }


def get_order_by_number(order_number: str, email: str | None = None):
    with get_connection() as conn:
        with conn.cursor() as cur:
            sql = """
                SELECT *
                FROM store.v_order_totals
                WHERE order_number = %s
            """
            params = [order_number]
            if email:
                sql += " AND customer_email = %s"
                params.append(email)
            cur.execute(sql, params)
            order = cur.fetchone()
            if not order:
                return None

            cur.execute(
                """
                SELECT sku, product_name, variant_name, quantity, unit_price, tax_total, line_total, image_url
                FROM store.order_items
                WHERE order_id = %s
                ORDER BY created_at
                """,
                (order["id"],),
            )
            order["items"] = cur.fetchall()
            return _normalize(order)


def _upsert_customer(cur, payload):
    email = (payload.get("email") or "").strip() or None
    phone = (payload.get("phone") or "").strip() or None

    if email:
        cur.execute("SELECT * FROM store.customers WHERE email = %s LIMIT 1", (email,))
        customer = cur.fetchone()
        if customer:
            return customer

    if phone:
        cur.execute("SELECT * FROM store.customers WHERE phone = %s ORDER BY created_at LIMIT 1", (phone,))
        customer = cur.fetchone()
        if customer:
            return customer

    cur.execute(
        """
        INSERT INTO store.customers(email,phone,first_name,customer_type)
        VALUES(%s,%s,%s,'retail')
        RETURNING *
        """,
        (email, phone, payload["customer_name"]),
    )
    return cur.fetchone()


def _normalize(value):
    if isinstance(value, list):
        return [_normalize(v) for v in value]
    if isinstance(value, dict):
        return {k: _normalize(v) for k, v in value.items()}
    if isinstance(value, Decimal):
        return float(value)
    return value
