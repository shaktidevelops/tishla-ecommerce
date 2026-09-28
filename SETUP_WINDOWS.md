# Tishla — Windows setup

## Prerequisites

- Windows 10/11
- Python 3.12, 3.13 or 3.14
- Node.js 20.9+ (Node 22 LTS is a good development target)
- PostgreSQL 18 preferred, or another supported PostgreSQL version

## First setup

Open PowerShell in the project root:

```powershell
cd E:\Shakti\GitHub\tishla-ecommerce
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass -Force
.\START_HERE_WINDOWS.ps1
```

The script creates `backend\\.venv`, installs Python dependencies, installs Next.js dependencies, initializes PostgreSQL using Psycopg (not `psql`), and applies migrations/seeds.

## Run

```powershell
.\scripts\run-all-windows.ps1
```

Then open:

- http://localhost:3000
- http://127.0.0.1:8000/docs
- http://127.0.0.1:8000/health

## Import catalogue

Preview:

```powershell
.\scripts\run-import-windows.ps1 .\data\catalogue.csv
```

Apply:

```powershell
.\scripts\run-import-windows.ps1 .\data\catalogue.csv -Apply
```

## Notes

`psql` is optional. Docker is optional for local Windows development. The root `.env` is the active local configuration; `.env.example` files are templates only.

## One-command developer start

Use `START_TISHLA_DEV.bat` for the simplest Windows experience. It invokes PowerShell with ExecutionPolicy Bypass for the current process, so you do not need to change your machine-wide PowerShell policy.
