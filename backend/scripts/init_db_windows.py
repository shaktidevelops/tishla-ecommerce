from pathlib import Path
import sys

import psycopg
from psycopg import sql
from psycopg.conninfo import conninfo_to_dict

ROOT = Path(__file__).resolve().parents[2]
DATABASE_FILE = ROOT / "database" / "TISHLA_DATABASE.sql"

sys.path.insert(0, str(ROOT / "backend"))
from app.core.config import get_settings


def ensure_database(database_url: str) -> str:
    """Ensure the target database exists and return its database name."""
    params = conninfo_to_dict(database_url)
    dbname = params.get("dbname") or "tishla"
    admin_params = dict(params)
    admin_params["dbname"] = "postgres"

    with psycopg.connect(**admin_params, autocommit=True) as conn:
        exists = conn.execute(
            "SELECT 1 FROM pg_database WHERE datname=%s",
            (dbname,),
        ).fetchone()

        if not exists:
            conn.execute(
                sql.SQL("CREATE DATABASE {}").format(sql.Identifier(dbname))
            )
            print(f"Created database: {dbname}")
        else:
            print(f"Database already exists: {dbname}")

    return dbname


def apply_database(database_url: str) -> None:
    if not DATABASE_FILE.exists():
        raise FileNotFoundError(f"Database file not found: {DATABASE_FILE}")

    print(f"Applying {DATABASE_FILE.name} ...")
    database_sql = DATABASE_FILE.read_text(encoding="utf-8")

    params = conninfo_to_dict(database_url)
    params["dbname"] = ensure_database(database_url)

    # TISHLA_DATABASE.sql contains its own BEGIN/COMMIT.
    with psycopg.connect(**params, autocommit=True) as conn:
        conn.execute(database_sql)

        checks = {
            "auth.users": conn.execute(
                "SELECT to_regclass('auth.users')"
            ).fetchone()[0],
            "auth.roles": conn.execute(
                "SELECT to_regclass('auth.roles')"
            ).fetchone()[0],
            "store.products": conn.execute(
                "SELECT to_regclass('store.products')"
            ).fetchone()[0],
        }

    missing = [name for name, value in checks.items() if value is None]
    if missing:
        raise RuntimeError(
            "Database initialization finished but required tables are missing: "
            + ", ".join(missing)
        )

    print("Database initialization completed successfully.")
    print("Required schemas/tables verified: auth.users, auth.roles, store.products.")


if __name__ == "__main__":
    try:
        settings = get_settings()
        apply_database(settings.database_url)
    except Exception as exc:
        print(f"DATABASE INITIALIZATION FAILED: {exc}", file=sys.stderr)
        raise
