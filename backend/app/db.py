from contextlib import contextmanager

from psycopg.rows import dict_row
from psycopg_pool import ConnectionPool

from .core.config import get_settings


settings = get_settings()

# Explicitly open the synchronous pool on construction so requests cannot
# arrive before FastAPI's lifespan hook initializes it.
pool = ConnectionPool(
    conninfo=settings.database_url,
    min_size=settings.database_min_size,
    max_size=settings.database_max_size,
    kwargs={"row_factory": dict_row},
    open=True,
)


def open_pool() -> None:
    """Ensure the pool is open and ready for application traffic."""
    if pool.closed:
        pool.open()
    pool.wait(timeout=15)


def close_pool() -> None:
    pool.close()


@contextmanager
def get_connection():
    # Defensive guard for reload/development startup ordering.
    if pool.closed:
        pool.open()
    with pool.connection() as conn:
        yield conn
