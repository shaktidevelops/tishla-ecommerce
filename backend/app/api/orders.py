from fastapi import APIRouter, HTTPException
from pydantic import BaseModel, Field, EmailStr

from ..services.orders import create_order, get_order_by_number

router = APIRouter(prefix="/api/orders", tags=["orders"])


class Address(BaseModel):
    line1: str = Field(min_length=3)
    line2: str | None = None
    landmark: str | None = None
    city: str = Field(min_length=2)
    state: str = Field(min_length=2)
    postal_code: str = Field(min_length=4, max_length=10)
    country_code: str = "IN"


class OrderItem(BaseModel):
    variant_id: str
    quantity: int = Field(ge=1, le=500)


class CreateOrder(BaseModel):
    customer_name: str = Field(min_length=2, max_length=120)
    phone: str = Field(min_length=8, max_length=20)
    email: EmailStr | None = None
    shipping_address: Address
    items: list[OrderItem] = Field(min_length=1, max_length=100)
    payment_method: str = Field(default="cod", pattern="^(cod|online)$")
    notes: str | None = Field(default=None, max_length=1000)
    source: str = "web"
    shipping_total: float = Field(default=0, ge=0)


@router.post("")
def order(payload: CreateOrder):
    try:
        result = create_order(payload.model_dump())
    except ValueError as exc:
        raise HTTPException(status_code=400, detail=str(exc)) from exc
    return {"success": True, "data": result}


@router.get("/{order_number}")
def order_lookup(order_number: str, email: EmailStr | None = None):
    result = get_order_by_number(order_number, email)
    if not result:
        raise HTTPException(status_code=404, detail="Order not found.")
    return {"success": True, "data": result}
