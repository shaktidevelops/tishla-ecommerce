from contextlib import contextmanager

from psycopg.rows import dict_row
from psycopg_pool import ConnectionPool

from .core.config import get_settings


settings = get_settings()

pool = ConnectionPool(
    conninfo=settings.database_url,
    min_size=settings.database_min_size,
    max_size=settings.database_max_size,
    kwargs={"row_factory": dict_row},
    open=False,
)


def open_pool() -> None:
    pool.open()


def close_pool() -> None:
    pool.close()


@contextmanager
def get_connection():
    with pool.connection() as conn:
        yield conn
