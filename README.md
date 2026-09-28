# Tishla Commerce Platform v2

A complete multi-page fashion e-commerce foundation for Tishla by Purnika Sales.

## Stack

- Next.js 16.3.3
- React 19.3
- TypeScript 5.9
- Lucide React 1.48
- Python 3.12–3.14
- FastAPI 0.141.1
- Psycopg 3.3.6
- PostgreSQL 18-compatible SQL
- Windows-native development first; Docker/VPS deployment remains optional

Next.js 16.3.3 is pinned from the current active LTS line; React 19.3 is the current stable React release line. PostgreSQL 18 is the current supported major release. See `docs/SOURCES.md` for references.

## Start on Windows

1. Copy the project to `E:\Shakti\GitHub\tishla-ecommerce`.
2. Keep the root `.env` supplied with the package for local development, then rotate credentials before production.
3. Open a new PowerShell and run `Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass -Force`.
4. Run `.\START_HERE_WINDOWS.ps1` (PowerShell path syntax: `.\START_HERE_WINDOWS.ps1`).
5. After setup, run `.\scripts\run-all-windows.ps1`.

Then open `http://localhost:3000`.

## Database

Use `database/TISHLA_DATABASE.sql` for a consolidated install. Sequential migrations are in `database/migrations`.

The database is divided into `auth`, `store`, `cms`, and `audit` schemas. The store covers catalogue, departments, collections, variants, pricing, inventory, customers, wholesale, carts, orders, payments, shipping, returns, coupons, enquiries, reviews and SEO. CMS covers pages, banners, homepage sections, navigation, lookbooks, blog posts and FAQs.

## Single-command Windows development

From `E:\\Shakti\\GitHub\\tishla-ecommerce`, either double-click `START_TISHLA_DEV.bat` or run `START_TISHLA_DEV.ps1` from PowerShell. This prepares dependencies, initializes PostgreSQL, starts the FastAPI API and Next.js storefront, and opens `http://localhost:3000`.
