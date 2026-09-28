# Tishla Commerce Platform

Tishla by Purnika Sales — English-only luxury Indian fashion storefront and administration platform.

## Development

From the project root on Windows:

```powershell
git pull origin main
.\START_TISHLA_DEV.bat
```

The launcher starts the FastAPI backend and Next.js storefront, waits for both services, and opens the storefront. The Admin Control Room is available at `/admin`.

## Architecture

- Next.js 16 + React 19 storefront and Admin UI
- FastAPI backend
- PostgreSQL database
- Same-origin `/api/*` browser requests proxied by Next.js to FastAPI in local development

Do not commit real `.env` files or production secrets.
