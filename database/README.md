# Tishla database

Run the complete database with `TISHLA_DATABASE.sql`, or apply the migrations in filename order.

Recommended PostgreSQL production major: 18. PostgreSQL 18 is the current supported major release; do not use PostgreSQL 19 beta for production.

The `store` schema is authoritative for product, price, stock and order data. The `cms` schema controls storefront presentation data. `auth` controls staff accounts and sessions. `audit` stores operational audit records.
