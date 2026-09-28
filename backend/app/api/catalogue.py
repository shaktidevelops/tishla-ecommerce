from fastapi import APIRouter, HTTPException, Query

from ..services.catalogue import get_fabrics, get_product, list_products

router = APIRouter(prefix="/api/catalogue", tags=["catalogue"])


@router.get("/products")
def products(
    q: str | None = Query(default=None),
    shoot_type: str | None = Query(default=None),
    fabric: str | None = Query(default=None),
    price_min: float | None = Query(default=None, ge=0),
    price_max: float | None = Query(default=None, ge=0),
    limit: int = Query(default=60, ge=1, le=200),
    offset: int = Query(default=0, ge=0),
):
    return {
        "success": True,
        "data": list_products(q, shoot_type, fabric, price_min, price_max, limit, offset),
        "limit": limit,
        "offset": offset,
    }


@router.get("/products/{identifier}")
def product(identifier: str):
    result = get_product(identifier)
    if not result:
        raise HTTPException(status_code=404, detail="Product not found.")
    return {"success": True, "data": result}


@router.get("/fabrics")
def fabrics():
    return {"success": True, "data": get_fabrics()}
