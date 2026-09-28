from pathlib import Path
import os
import sys
import psycopg
from dotenv import load_dotenv

ROOT = Path(__file__).resolve().parents[2]
load_dotenv(ROOT / ".env")

DB_NAME = os.getenv("POSTGRES_DB", "tishla")
DB_USER = os.getenv("POSTGRES_USER", "postgres")
DB_PASSWORD = os.getenv("POSTGRES_PASSWORD", "")
DB_HOST = os.getenv("POSTGRES_HOST", "localhost")
DB_PORT = int(os.getenv("POSTGRES_PORT", "5432"))


def connect(dbname: str):
    return psycopg.connect(host=DB_HOST, port=DB_PORT, user=DB_USER, password=DB_PASSWORD, dbname=dbname, autocommit=True)


def ensure_database():
    with connect("postgres") as conn:
        exists = conn.execute("SELECT 1 FROM pg_database WHERE datname=%s", (DB_NAME,)).fetchone()
        if not exists:
            conn.execute(f'CREATE DATABASE "{DB_NAME.replace(chr(34), chr(34)+chr(34))}"')
            print(f"Created database: {DB_NAME}")
        else:
            print(f"Database already exists: {DB_NAME}")


def apply_sql():
    files = [
        ROOT / "database" / "migrations" / "001_initial.sql",
        ROOT / "database" / "migrations" / "002_catalogue_import.sql",
        ROOT / "database" / "migrations" / "003_storefront_restructure.sql",
        ROOT / "database" / "migrations" / "004_seed_storefront.sql",
        ROOT / "database" / "migrations" / "005_demo_catalogue.sql",
    ]
    with connect(DB_NAME) as conn:
        for file in files:
            print(f"Applying {file.name} ...")
            sql = file.read_text(encoding="utf-8")
            conn.execute(sql)
    print("Database initialization completed successfully.")

if __name__ == "__main__":
    try:
        ensure_database()
        apply_sql()
    except Exception as exc:
        print(f"DATABASE INITIALIZATION FAILED: {exc}", file=sys.stderr)
        raise
