from fastapi import APIRouter, HTTPException
from pydantic import BaseModel, EmailStr, Field

from ..core.security import (
    create_access_token,
    hash_password,
    hash_refresh_token,
    new_refresh_token,
    verify_password,
)
from ..db import get_connection

router = APIRouter(prefix="/api/auth", tags=["auth"])


class LoginPayload(BaseModel):
    email: EmailStr
    password: str = Field(min_length=8, max_length=200)


@router.post("/login")
def login(payload: LoginPayload):
    with get_connection() as conn:
        with conn.cursor() as cur:
            cur.execute(
                """
                SELECT
                    u.id, u.email, u.password_hash, u.full_name, u.status,
                    COALESCE(array_agg(r.code) FILTER (WHERE r.code IS NOT NULL), '{}') AS roles
                FROM auth.users u
                LEFT JOIN auth.user_roles ur ON ur.user_id = u.id
                LEFT JOIN auth.roles r ON r.id = ur.role_id
                WHERE u.email = %s
                GROUP BY u.id
                """,
                (payload.email,),
            )
            user = cur.fetchone()

            if not user or user["status"] != "active" or not verify_password(payload.password, user["password_hash"]):
                raise HTTPException(status_code=401, detail="Invalid email or password.")

            cur.execute("UPDATE auth.users SET last_login_at = now() WHERE id = %s", (user["id"],))

            access = create_access_token(str(user["id"]), list(user["roles"]))
            refresh = new_refresh_token()
            cur.execute(
                """
                INSERT INTO auth.sessions(user_id, refresh_token_hash, expires_at)
                VALUES(%s,%s, now() + interval '30 days')
                """,
                (user["id"], hash_refresh_token(refresh)),
            )

    return {
        "success": True,
        "access_token": access,
        "refresh_token": refresh,
        "user": {
            "id": str(user["id"]),
            "email": user["email"],
            "full_name": user["full_name"],
            "roles": list(user["roles"]),
        },
    }


@router.post("/wholesale-application")
def wholesale_application(payload: dict):
    required = ["name", "phone"]
    if any(not payload.get(k) for k in required):
        raise HTTPException(status_code=400, detail="Name and phone are required.")

    with get_connection() as conn:
        with conn.transaction():
            with conn.cursor() as cur:
                cur.execute(
                    """
                    INSERT INTO store.customers(
                        phone, first_name, last_name, company_name, gstin, customer_type
                    )
                    VALUES(%s,%s,NULL,%s,%s,'wholesale')
                    RETURNING id
                    """,
                    (
                        payload["phone"],
                        payload["name"],
                        payload.get("company_name"),
                        payload.get("gstin"),
                    ),
                )
                customer = cur.fetchone()
                cur.execute(
                    """
                    INSERT INTO store.wholesale_accounts(customer_id)
                    VALUES(%s)
                    ON CONFLICT(customer_id) DO NOTHING
                    """,
                    (customer["id"],),
                )

    return {"success": True, "customer_id": str(customer["id"]), "message": "Wholesale application submitted."}
