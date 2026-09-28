$ErrorActionPreference = 'Stop'
$Root = Split-Path -Parent $PSScriptRoot
& (Join-Path $Root 'scripts\ensure-python-env-windows.ps1')
$Py = Join-Path $Root 'backend\.venv\Scripts\python.exe'
& $Py (Join-Path $Root 'backend\scripts\init_db_windows.py')
if ($LASTEXITCODE -ne 0) { throw 'Database initialization failed.' }
