$ErrorActionPreference = 'Stop'
$Root = Split-Path -Parent $PSScriptRoot
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass -Force
& (Join-Path $Root 'scripts\ensure-python-env-windows.ps1')
& (Join-Path $Root 'scripts\ensure-node-windows.ps1')
Push-Location (Join-Path $Root 'apps\web')
try { npm install; if ($LASTEXITCODE -ne 0) { throw 'Node dependency installation failed.' } }
finally { Pop-Location }
& (Join-Path $Root 'scripts\init-db-windows.ps1')
Write-Host 'Tishla local setup completed.' -ForegroundColor Green
