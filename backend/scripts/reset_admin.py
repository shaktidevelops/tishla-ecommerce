import os
import sys

import psycopg

sys.path.insert(0, os.path.abspath(os.path.join(os.path.dirname(__file__), "..")))

from app.core.config import get_settings
from app.core.security import hash_password
from app.db import open_pool, close_pool, get_connection


settings = get_settings()


def main():
    email = input(f"Admin email [{settings.admin_bootstrap_email}]: ").strip() or settings.admin_bootstrap_email
    name = input(f"Admin name [{settings.admin_bootstrap_name}]: ").strip() or settings.admin_bootstrap_name
    password = input("New admin password (min 8 chars): ").strip()

    if len(password) < 8:
        raise SystemExit("Password must be at least 8 characters.")

    open_pool()
    try:
        with get_connection() as conn:
            with conn.transaction():
                with conn.cursor() as cur:
                    try:
                        cur.execute(
                            "SELECT id FROM auth.users WHERE email=%s",
                            (email,),
                        )
                    except psycopg.errors.UndefinedTable as exc:
                        raise SystemExit(
                            "The Tishla database schema is not initialized yet. "
                            "Run: .\\backend\\.venv\\Scripts\\python.exe "
                            ".\\backend\\scripts\\init_db_windows.py"
                        ) from exc

                    existing = cur.fetchone()

                    if existing:
                        user_id = existing["id"]
                        cur.execute(
                            """
                            UPDATE auth.users
                            SET password_hash=%s,
                                full_name=%s,
                                status='active',
                                updated_at=now()
                            WHERE id=%s
                            """,
                            (hash_password(password), name, user_id),
                        )
                        message = f"Admin password reset: {email}"
                    else:
                        cur.execute(
                            """
                            INSERT INTO auth.users(email,password_hash,full_name,status)
                            VALUES(%s,%s,%s,'active')
                            RETURNING id
                            """,
                            (email, hash_password(password), name),
                        )
                        user_id = cur.fetchone()["id"]
                        message = f"Admin created: {email}"

                    cur.execute("SELECT id FROM auth.roles WHERE code='admin'")
                    role = cur.fetchone()
                    if not role:
                        raise SystemExit("The 'admin' role does not exist. Run the database initialization first.")

                    cur.execute(
                        """
                        INSERT INTO auth.user_roles(user_id,role_id)
                        VALUES(%s,%s)
                        ON CONFLICT DO NOTHING
                        """,
                        (user_id, role["id"]),
                    )

                    cur.execute(
                        "DELETE FROM auth.sessions WHERE user_id=%s",
                        (user_id,),
                    )

        print(message)
    finally:
        close_pool()


if __name__ == "__main__":
    main()
