from fastapi import APIRouter, Depends, HTTPException
from pydantic import BaseModel, Field
from typing import Any

from .deps import require_role
from ..core.config import get_settings
from ..core.security import hash_password
from ..db import get_connection

router = APIRouter(prefix="/api/admin", tags=["admin"])
settings = get_settings()

ALLOWED_STATUS_TRANSITIONS = {
    "pending_payment": {"paid", "payment_failed", "cancelled"},
    "paid": {"confirmed", "cancelled", "refunded"},
    "confirmed": {"processing", "cancelled"},
    "processing": {"packed", "cancelled"},
    "packed": {"shipped"},
    "shipped": {"delivered", "returned"},
    "delivered": {"returned"},
    "payment_failed": {"pending_payment", "cancelled"},
    "cancelled": {"refunded"},
    "refunded": set(),
    "returned": {"refunded"},
}


class ProductPayload(BaseModel):
    sku: str = Field(min_length=2, max_length=80)
    slug: str = Field(min_length=2, max_length=120)
    name: str = Field(min_length=2, max_length=200)
    fabric: str | None = None
    product_type: str | None = None
    shoot_type: str | None = None
    base_price: float | None = Field(default=None, ge=0)
    gst_rate: float = Field(default=settings.default_gst_rate, ge=0, le=100)
    description: str | None = None
    short_description: str | None = None
    status: str = "active"


class ProductPatch(BaseModel):
    sku: str | None = Field(default=None, min_length=2, max_length=80)
    slug: str | None = Field(default=None, min_length=2, max_length=120)
    name: str | None = Field(default=None, min_length=2, max_length=200)
    fabric: str | None = None
    product_type: str | None = None
    shoot_type: str | None = None
    base_price: float | None = Field(default=None, ge=0)
    gst_rate: float | None = Field(default=None, ge=0, le=100)
    description: str | None = None
    short_description: str | None = None
    status: str | None = None
    featured: bool | None = None
    min_order_qty: int | None = Field(default=None, ge=1)
    department_id: str | None = None


class SettingPayload(BaseModel):
    value: dict[str, Any]


class StatusPayload(BaseModel):
    status: str
    note: str | None = Field(default=None, max_length=1000)


class InventoryAdjustment(BaseModel):
    variant_id: str
    quantity: int
    movement_type: str = "adjustment"
    reason: str = Field(min_length=2, max_length=500)




@router.get("/products")
def admin_products(
    q: str | None = None,
    status: str | None = None,
    _user: dict = Depends(require_role("catalogue_manager")),
):
    with get_connection() as conn:
        with conn.cursor() as cur:
            conditions = ["1=1"]
            params: list[Any] = []
            if q:
                conditions.append("(p.name ILIKE %s OR p.sku ILIKE %s OR COALESCE(p.fabric, '') ILIKE %s)")
                params.extend([f"%{q}%", f"%{q}%", f"%{q}%"])
            if status:
                conditions.append("p.status = %s::store.product_status")
                params.append(status)
            cur.execute(
                f"""
                SELECT p.id, p.sku, p.slug, p.name, p.fabric, p.product_type, p.shoot_type,
                       p.base_price, p.gst_rate, p.status, p.featured, p.min_order_qty,
                       p.department_id, p.created_at, p.updated_at,
                       COUNT(DISTINCT v.id)::int AS variant_count,
                       COUNT(DISTINCT pi.id)::int AS image_count
                FROM store.products p
                LEFT JOIN store.product_variants v ON v.product_id = p.id
                LEFT JOIN store.product_images pi ON pi.product_id = p.id
                WHERE {' AND '.join(conditions)}
                GROUP BY p.id
                ORDER BY p.updated_at DESC, p.name ASC
                LIMIT 200
                """,
                params,
            )
            return {"success": True, "data": cur.fetchall()}


@router.patch("/products/{product_id}")
def update_product(
    product_id: str,
    payload: ProductPatch,
    _user: dict = Depends(require_role("catalogue_manager")),
):
    fields = payload.model_dump(exclude_unset=True)
    if not fields:
        raise HTTPException(status_code=400, detail="No fields supplied.")

    if "status" in fields and fields["status"] not in {"draft", "active", "archived"}:
        raise HTTPException(status_code=400, detail="Invalid product status.")

    allowed = {
        "sku", "slug", "name", "fabric", "product_type", "shoot_type",
        "base_price", "gst_rate", "description", "short_description",
        "status", "featured", "min_order_qty", "department_id"
    }
    fields = {k: v for k, v in fields.items() if k in allowed}
    assignments = []
    params: list[Any] = []
    for key, value in fields.items():
        if key == "status":
            assignments.append("status=%s::store.product_status")
        else:
            assignments.append(f"{key}=%s")
        params.append(value)
    params.append(product_id)

    with get_connection() as conn:
        with conn.transaction():
            with conn.cursor() as cur:
                cur.execute(
                    f"UPDATE store.products SET {', '.join(assignments)} WHERE id=%s RETURNING id,sku,slug,name,status,updated_at",
                    params,
                )
                result = cur.fetchone()
                if not result:
                    raise HTTPException(status_code=404, detail="Product not found.")
    return {"success": True, "data": result}


@router.get("/settings")
def get_settings_admin(_user: dict = Depends(require_role("admin"))):
    with get_connection() as conn:
        with conn.cursor() as cur:
            cur.execute("SELECT key, value, updated_at FROM store.settings ORDER BY key")
            return {"success": True, "data": cur.fetchall()}


@router.put("/settings/{key}")
def save_setting(key: str, payload: SettingPayload, _user: dict = Depends(require_role("admin"))):
    if not key or len(key) > 80 or not key.replace("_", "").replace("-", "").isalnum():
        raise HTTPException(status_code=400, detail="Invalid settings key.")
    with get_connection() as conn:
        with conn.transaction():
            with conn.cursor() as cur:
                cur.execute(
                    """
                    INSERT INTO store.settings(key,value,updated_at)
                    VALUES(%s,%s::jsonb,now())
                    ON CONFLICT(key) DO UPDATE SET value=EXCLUDED.value,updated_at=now()
                    RETURNING key,value,updated_at
                    """,
                    (key, __import__("json").dumps(payload.value)),
                )
                return {"success": True, "data": cur.fetchone()}


@router.get("/dashboard")
def dashboard(_user: dict = Depends(require_role("admin"))):
    with get_connection() as conn:
        with conn.cursor() as cur:
            cur.execute("SELECT * FROM store.v_admin_dashboard")
            return {"success": True, "data": cur.fetchone()}


@router.get("/orders")
def recent_orders(
    q: str | None = None,
    status: str | None = None,
    _user: dict = Depends(require_role("order_manager")),
):
    with get_connection() as conn:
        with conn.cursor() as cur:
            conditions = ["1=1"]
            params: list[Any] = []
            if q:
                conditions.append(
                    "(order_number ILIKE %s OR customer_name ILIKE %s OR customer_phone ILIKE %s OR customer_email::text ILIKE %s)"
                )
                term = f"%{q}%"
                params.extend([term, term, term, term])
            if status:
                valid_statuses = {
                    "pending_payment", "payment_failed", "paid", "confirmed", "processing",
                    "packed", "shipped", "delivered", "cancelled", "refunded", "returned"
                }
                if status not in valid_statuses:
                    raise HTTPException(status_code=400, detail="Invalid order status.")
                conditions.append("status = %s::store.order_status")
                params.append(status)
            where_sql = " AND ".join(conditions)
            cur.execute(
                "SELECT * FROM store.v_order_totals WHERE " + where_sql + " ORDER BY created_at DESC LIMIT 200",
                params,
            )
            return {"success": True, "data": cur.fetchall()}


@router.get("/orders/{order_id}")
def order_detail(
    order_id: str,
    _user: dict = Depends(require_role("order_manager")),
):
    with get_connection() as conn:
        with conn.cursor() as cur:
            cur.execute(
                """
                SELECT id,order_number,status,currency,subtotal,discount_total,taxable_total,
                       tax_total,shipping_total,grand_total,customer_id,customer_name,
                       customer_email,customer_phone,billing_address,shipping_address,
                       notes,source,payment_method,external_payment_reference,
                       external_checkout_reference,placed_at,confirmed_at,cancelled_at,
                       delivered_at,created_at,updated_at
                FROM store.orders
                WHERE id=%s
                """,
                (order_id,),
            )
            order = cur.fetchone()
            if not order:
                raise HTTPException(status_code=404, detail="Order not found.")

            cur.execute(
                """
                SELECT id,sku,product_name,variant_name,quantity,unit_price,
                       tax_rate,tax_total,line_total
                FROM store.order_items
                WHERE order_id=%s
                ORDER BY created_at ASC
                """,
                (order_id,),
            )
            items = cur.fetchall()

            cur.execute(
                """
                SELECT id,provider,amount,currency,status,method,paid_at,created_at
                FROM store.payments
                WHERE order_id=%s
                ORDER BY created_at DESC
                LIMIT 10
                """,
                (order_id,),
            )
            payments = cur.fetchall()

            cur.execute(
                """
                SELECT id,provider,tracking_number,status,shipping_method
                FROM store.shipments
                WHERE order_id=%s
                ORDER BY created_at DESC
                LIMIT 10
                """,
                (order_id,),
            )
            shipments = cur.fetchall()

            cur.execute(
                """
                SELECT h.id,h.from_status,h.to_status,h.note,h.created_at,
                       COALESCE(u.full_name,u.email,'System') AS changed_by
                FROM store.order_status_history h
                LEFT JOIN auth.users u ON u.id=h.changed_by
                WHERE h.order_id=%s
                ORDER BY h.created_at DESC
                LIMIT 50
                """,
                (order_id,),
            )
            history = cur.fetchall()

    return {"success": True, "data": {"order": order, "items": items, "payments": payments, "shipments": shipments, "history": history}}


@router.patch("/orders/{order_id}/status")
def update_order_status(
    order_id: str,
    payload: StatusPayload,
    user: dict = Depends(require_role("order_manager")),
):
    with get_connection() as conn:
        with conn.transaction():
            with conn.cursor() as cur:
                cur.execute("SELECT id,status FROM store.orders WHERE id=%s FOR UPDATE", (order_id,))
                order = cur.fetchone()
                if not order:
                    raise HTTPException(status_code=404, detail="Order not found.")

                current = order["status"]
                target = payload.status
                if target not in ALLOWED_STATUS_TRANSITIONS.get(current, set()):
                    raise HTTPException(
                        status_code=400,
                        detail=f"Invalid order status transition: {current} -> {target}",
                    )

                cur.execute(
                    """
                    UPDATE store.orders
                    SET status=%s,
                        confirmed_at=CASE WHEN %s='confirmed' THEN COALESCE(confirmed_at,now()) ELSE confirmed_at END,
                        cancelled_at=CASE WHEN %s='cancelled' THEN now() ELSE cancelled_at END,
                        delivered_at=CASE WHEN %s='delivered' THEN now() ELSE delivered_at END
                    WHERE id=%s
                    RETURNING id,order_number,status
                    """,
                    (target, target, target, target, order_id),
                )
                result = cur.fetchone()

                if target in {"cancelled", "refunded", "payment_failed"}:
                    cur.execute(
                        """
                        UPDATE store.inventory_stock s
                        SET reserved = GREATEST(0, s.reserved - oi.quantity),
                            updated_at = now()
                        FROM store.order_items oi
                        WHERE oi.order_id=%s
                          AND s.variant_id=oi.variant_id
                        """,
                        (order_id,),
                    )

                if target == "shipped":
                    cur.execute(
                        """
                        UPDATE store.inventory_stock s
                        SET on_hand = s.on_hand - oi.quantity,
                            reserved = GREATEST(0, s.reserved - oi.quantity),
                            updated_at = now()
                        FROM store.order_items oi
                        WHERE oi.order_id=%s
                          AND s.variant_id=oi.variant_id
                        """,
                        (order_id,),
                    )
                    cur.execute(
                        """
                        INSERT INTO store.inventory_movements(
                            location_id,variant_id,movement_type,quantity,reference_type,reference_id,reason,created_by
                        )
                        SELECT
                            s.location_id, oi.variant_id, 'sale', oi.quantity,
                            'order', %s, 'Order dispatched', %s
                        FROM store.order_items oi
                        JOIN store.inventory_stock s ON s.variant_id=oi.variant_id
                        WHERE oi.order_id=%s
                        """,
                        (order_id, user["sub"], order_id),
                    )

                cur.execute(
                    """
                    UPDATE store.order_status_history
                    SET changed_by=%s, note=%s
                    WHERE id=(
                        SELECT id FROM store.order_status_history
                        WHERE order_id=%s ORDER BY id DESC LIMIT 1
                    )
                    """,
                    (user["sub"], payload.note, order_id),
                )

    return {"success": True, "data": result}


@router.post("/inventory/adjust")
def adjust_inventory(payload: InventoryAdjustment, user: dict = Depends(require_role("catalogue_manager"))):
    supported = {
        "opening", "purchase", "sale", "sale_reversal", "adjustment",
        "damage", "return", "transfer_in", "transfer_out"
    }
    if payload.movement_type not in supported:
        raise HTTPException(status_code=400, detail="Unsupported inventory movement type.")

    with get_connection() as conn:
        with conn.transaction():
            with conn.cursor() as cur:
                cur.execute(
                    """
                    INSERT INTO store.inventory_movements(
                        location_id,variant_id,movement_type,quantity,reason,created_by
                    )
                    SELECT id,%s,%s,%s,%s,%s
                    FROM store.inventory_locations
                    WHERE code='SURAT_MAIN' AND is_active=true
                    RETURNING id
                    """,
                    (
                        payload.variant_id,
                        payload.movement_type,
                        payload.quantity,
                        payload.reason,
                        user["sub"],
                    ),
                )
                movement = cur.fetchone()
                if not movement:
                    raise HTTPException(status_code=400, detail="Main inventory location is unavailable.")

                cur.execute(
                    """
                    SELECT store.refresh_inventory_stock(
                        (SELECT id FROM store.inventory_locations WHERE code='SURAT_MAIN'),
                        %s
                    )
                    """,
                    (payload.variant_id,),
                )

    return {"success": True, "movement_id": str(movement["id"])}


@router.get("/low-stock")
def low_stock(_user: dict = Depends(require_role("catalogue_manager"))):
    with get_connection() as conn:
        with conn.cursor() as cur:
            cur.execute(
                """
                SELECT * FROM store.v_variant_inventory
                WHERE is_low_stock
                ORDER BY available_quantity ASC, product_name
                LIMIT 200
                """
            )
            return {"success": True, "data": cur.fetchall()}


@router.post("/products")
def create_product(payload: ProductPayload, _user: dict = Depends(require_role("catalogue_manager"))):
    with get_connection() as conn:
        with conn.transaction():
            with conn.cursor() as cur:
                cur.execute(
                    """
                    INSERT INTO store.products(
                        sku, slug, name, fabric, product_type, shoot_type,
                        base_price, gst_rate, description, short_description, status
                    )
                    VALUES(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)
                    RETURNING id, sku, slug, name
                    """,
                    (
                        payload.sku, payload.slug, payload.name, payload.fabric,
                        payload.product_type, payload.shoot_type, payload.base_price,
                        payload.gst_rate, payload.description, payload.short_description,
                        payload.status,
                    ),
                )
                row = cur.fetchone()
    return {"success": True, "data": row}


@router.post("/bootstrap-admin")
def bootstrap_admin(_user: dict = Depends(require_role("admin"))):
    with get_connection() as conn:
        with conn.transaction():
            with conn.cursor() as cur:
                cur.execute("SELECT id FROM auth.users WHERE email = %s", (settings.admin_bootstrap_email,))
                existing = cur.fetchone()
                if existing:
                    return {"success": True, "message": "Bootstrap admin already exists."}

                cur.execute(
                    """
                    INSERT INTO auth.users(email,password_hash,full_name,status)
                    VALUES(%s,%s,%s,'active')
                    RETURNING id
                    """,
                    (
                        settings.admin_bootstrap_email,
                        hash_password(settings.admin_bootstrap_password),
                        settings.admin_bootstrap_name,
                    ),
                )
                user_id = cur.fetchone()["id"]
                cur.execute("SELECT id FROM auth.roles WHERE code='admin'")
                role_id = cur.fetchone()["id"]
                cur.execute("INSERT INTO auth.user_roles(user_id,role_id) VALUES(%s,%s)", (user_id, role_id))

    return {"success": True, "message": "Bootstrap admin created."}
