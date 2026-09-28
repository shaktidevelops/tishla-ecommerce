# Tishla Commerce Platform v2 — Project Manifest

## Frontend

- `apps/web/app` — all public and admin routes
- `apps/web/components` — header/mega menu, hero, product cards, cart, wishlist, shared sections
- `apps/web/lib` — demo catalogue data and API bridge
- `apps/web/public/branding` — Tishla logo, banner and favicon from the supplied prototype

## Backend

- `backend/app` — FastAPI application, authentication, catalogue, orders, enquiries, visual search and storefront APIs
- `backend/scripts` — admin creation, catalogue import, Windows database initialization
- `backend/tests` — importer test scaffold

## Database

- `database/migrations/001_initial.sql` — commerce/auth/audit core
- `002_catalogue_import.sql` — migration batches and issues
- `003_storefront_restructure.sql` — departments, collections, CMS, navigation, wishlist, reviews and storefront views
- `004_seed_storefront.sql` — navigation, CMS and merchandising seed
- `005_demo_catalogue.sql` — demo products, variants, media, reviews and collection mappings
- `database/TISHLA_DATABASE.sql` — consolidated install script
- `database/SCHEMA_MAP.md` — entity and schema map

## Operations

- `scripts/START_HERE_WINDOWS.ps1` — root setup entry point
- `scripts/run-all-windows.ps1` — start API and storefront
- `scripts/backup-db-windows.ps1` — PostgreSQL dump helper
- `docker-compose.yml` — optional VPS/Docker deployment
- `ops/nginx.conf` — reverse proxy

## Legacy

`legacy_reference/` retains the supplied Apps Script, HTML/CSS, logo, banner and favicon for migration reference.

## Windows launcher policy

Canonical developer entry points are CMD files to avoid PowerShell signing/execution-policy blockers. PowerShell scripts remain optional maintenance utilities.
