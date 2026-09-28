$ErrorActionPreference = 'Stop'
$Root = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass -Force
Write-Host 'TISHLA COMMERCE — Windows Setup' -ForegroundColor Magenta
Write-Host "Root: $Root"
& (Join-Path $Root 'scripts\setup-windows.ps1')
Write-Host ''
Write-Host 'NEXT:' -ForegroundColor Cyan
Write-Host '1. .\scripts\run-all-windows.ps1'
Write-Host '2. Open http://localhost:3000'
Write-Host '3. Open http://127.0.0.1:8000/docs'
