import os
import sys

sys.path.insert(0, os.path.abspath(os.path.join(os.path.dirname(__file__), "..")))

from app.core.config import get_settings
from app.core.security import hash_password
from app.db import open_pool, close_pool, get_connection


settings = get_settings()


def main():
    email = input(f"Admin email [{settings.admin_bootstrap_email}]: ").strip() or settings.admin_bootstrap_email
    name = input(f"Admin name [{settings.admin_bootstrap_name}]: ").strip() or settings.admin_bootstrap_name
    password = input("Admin password (min 8 chars): ").strip()
    if len(password) < 8:
        raise SystemExit("Password must be at least 8 characters.")

    open_pool()
    try:
        with get_connection() as conn:
            with conn.transaction():
                with conn.cursor() as cur:
                    cur.execute("SELECT id FROM auth.users WHERE email=%s", (email,))
                    existing = cur.fetchone()
                    if existing:
                        raise SystemExit("A user with this email already exists.")

                    cur.execute(
                        """
                        INSERT INTO auth.users(email,password_hash,full_name,status)
                        VALUES(%s,%s,%s,'active')
                        RETURNING id
                        """,
                        (email, hash_password(password), name),
                    )
                    user_id = cur.fetchone()["id"]
                    cur.execute("SELECT id FROM auth.roles WHERE code='admin'")
                    role_id = cur.fetchone()["id"]
                    cur.execute(
                        "INSERT INTO auth.user_roles(user_id,role_id) VALUES(%s,%s)",
                        (user_id, role_id),
                    )
        print(f"Admin created: {email}")
    finally:
        close_pool()


if __name__ == "__main__":
    main()
