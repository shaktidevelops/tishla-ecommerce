from fastapi import APIRouter, HTTPException, Query
from ..db import get_connection

router = APIRouter(prefix="/api/storefront", tags=["storefront"])

@router.get("/products")
def storefront_products(
    q: str | None = Query(None),
    department: str | None = Query(None),
    collection: str | None = Query(None),
    limit: int = Query(40, ge=1, le=100),
    offset: int = Query(0, ge=0),
):
    where = ["p.status = 'active'"]
    params: list = []
    if q:
        where.append("(p.search_document @@ websearch_to_tsquery('simple', %s) OR p.name ILIKE %s OR p.sku ILIKE %s)")
        params += [q, f"%{q}%", f"%{q}%"]
    if department:
        where.append("d.slug = %s")
        params.append(department)
    if collection:
        where.append("c.slug = %s")
        params.append(collection)
    params += [limit, offset]
    sql = f"""
        SELECT p.*
        FROM store.v_storefront_products p
        WHERE {' AND '.join(where)}
        ORDER BY p.featured DESC, p.created_at DESC
        LIMIT %s OFFSET %s
    """
    with get_connection() as conn:
        with conn.cursor() as cur:
            # View already resolves all merchandising fields; collection/department
            # filtering is handled through EXISTS clauses here for portability.
            where2 = ["p.status = 'active'"]
            p2=[]
            if q:
                where2.append("(p.search_document @@ websearch_to_tsquery('simple', %s) OR p.name ILIKE %s OR p.sku ILIKE %s)")
                p2 += [q, f"%{q}%", f"%{q}%"]
            if department:
                where2.append("EXISTS (SELECT 1 FROM store.departments dd WHERE dd.id = p.department_id AND dd.slug = %s)")
                p2.append(department)
            if collection:
                where2.append("EXISTS (SELECT 1 FROM store.collection_products cp JOIN store.collections cc ON cc.id=cp.collection_id WHERE cp.product_id=p.id AND cc.slug=%s AND cc.is_active)")
                p2.append(collection)
            p2 += [limit, offset]
            cur.execute(f"SELECT * FROM store.v_storefront_products p WHERE {' AND '.join(where2)} ORDER BY p.featured DESC, p.created_at DESC LIMIT %s OFFSET %s", p2)
            return cur.fetchall()

@router.get("/products/{slug}")
def storefront_product(slug: str):
    with get_connection() as conn:
        with conn.cursor() as cur:
            cur.execute("SELECT * FROM store.v_storefront_products WHERE slug=%s", (slug,))
            row = cur.fetchone()
            if not row:
                raise HTTPException(404, "Product not found")
            return row

@router.get("/navigation")
def navigation():
    with get_connection() as conn:
        with conn.cursor() as cur:
            cur.execute("SELECT * FROM cms.v_navigation_items ORDER BY menu_code, sort_order")
            return cur.fetchall()

@router.get("/collections")
def collections():
    with get_connection() as conn:
        with conn.cursor() as cur:
            cur.execute("SELECT id,name,slug,description,hero_image_url,sort_order FROM store.collections WHERE is_active ORDER BY sort_order,name")
            return cur.fetchall()

@router.get("/departments")
def departments():
    with get_connection() as conn:
        with conn.cursor() as cur:
            cur.execute("SELECT id,name,slug,description,hero_image_url,sort_order FROM store.departments WHERE is_active ORDER BY sort_order,name")
            return cur.fetchall()
