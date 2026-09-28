$ErrorActionPreference = 'Stop'
$Root = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass -Force

Write-Host ''
Write-Host '==================================================' -ForegroundColor DarkYellow
Write-Host '          TISHLA COMMERCE — DEV START             ' -ForegroundColor Magenta
Write-Host '==================================================' -ForegroundColor DarkYellow
Write-Host "Project: $Root" -ForegroundColor Gray

$envFile = Join-Path $Root '.env'
if (-not (Test-Path $envFile)) {
    throw "Missing .env file at $envFile"
}

Write-Host ''
Write-Host '[1/3] Preparing Python, Node.js and PostgreSQL...' -ForegroundColor Cyan
& (Join-Path $Root 'scripts\setup-windows.ps1')
if ($LASTEXITCODE -ne 0) { throw 'Tishla setup failed.' }

Write-Host ''
Write-Host '[2/3] Starting API and storefront...' -ForegroundColor Cyan
& (Join-Path $Root 'scripts\run-all-windows.ps1')

Write-Host ''
Write-Host '[3/3] Opening Tishla storefront...' -ForegroundColor Cyan
Start-Sleep -Seconds 3
Start-Process 'http://localhost:3000'

Write-Host ''
Write-Host 'TISHLA IS STARTING' -ForegroundColor Green
Write-Host 'Storefront : http://localhost:3000' -ForegroundColor White
Write-Host 'API        : http://127.0.0.1:8000' -ForegroundColor White
Write-Host 'API Docs   : http://127.0.0.1:8000/docs' -ForegroundColor White
Write-Host ''
Write-Host 'Keep the API and web PowerShell windows open while developing.' -ForegroundColor Yellow
