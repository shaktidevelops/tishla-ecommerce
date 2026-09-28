$ErrorActionPreference = 'Stop'
$Root = Split-Path -Parent $PSScriptRoot
& (Join-Path $Root 'scripts\ensure-python-env-windows.ps1')
Push-Location (Join-Path $Root 'backend')
try { & '.\.venv\Scripts\python.exe' -m uvicorn app.main:app --reload --host 127.0.0.1 --port 8000 }
finally { Pop-Location }
