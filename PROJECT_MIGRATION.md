# Tishla migration

The main branch is now a clean Laravel 13 application. The previous application is preserved in the branch legacy-next-fastapi-postgresql.

Removed from main:
- Next.js and React
- FastAPI and Python
- PostgreSQL
- Node/npm package files
- Docker development stack
- JWT browser authentication
- PostgreSQL-specific functions, views and triggers

New:
- PHP 8.3
- Laravel 13
- MariaDB
- Blade
- Laravel session authentication
- Composer
- MariaDB migrations
- Lightweight browser JavaScript

Business concepts retained:
catalogue, variants, media, departments, collections, pricing, inventory, customers, wholesale-ready customer types, carts, orders, payments, shipments, returns, coupons, enquiries, CMS, navigation, lookbooks, blog, FAQ, SEO and audit records.
