from fastapi import APIRouter, HTTPException
from pydantic import BaseModel, Field, EmailStr

from ..db import get_connection

router = APIRouter(prefix="/api/enquiries", tags=["enquiries"])


class EnquiryPayload(BaseModel):
    name: str = Field(min_length=2, max_length=120)
    phone: str = Field(min_length=8, max_length=20)
    email: EmailStr | None = None
    product_id: str | None = None
    variant_id: str | None = None
    message: str | None = Field(default=None, max_length=2000)
    source: str = "whatsapp"


@router.post("")
def create_enquiry(payload: EnquiryPayload):
    with get_connection() as conn:
        with conn.transaction():
            with conn.cursor() as cur:
                cur.execute(
                    """
                    INSERT INTO store.enquiries(
                        name, phone, email, product_id, variant_id, message, source
                    )
                    VALUES(%s,%s,%s,%s,%s,%s,%s)
                    RETURNING id, status, created_at
                    """,
                    (
                        payload.name,
                        payload.phone,
                        payload.email,
                        payload.product_id,
                        payload.variant_id,
                        payload.message,
                        payload.source,
                    ),
                )
                row = cur.fetchone()
    return {"success": True, "data": row}
