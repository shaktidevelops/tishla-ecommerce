$ErrorActionPreference = 'Stop'
$Root = Split-Path -Parent $PSScriptRoot
Start-Process powershell.exe -ArgumentList '-NoExit','-ExecutionPolicy','Bypass','-File', (Join-Path $Root 'scripts\run-api-windows.ps1')
Start-Process powershell.exe -ArgumentList '-NoExit','-ExecutionPolicy','Bypass','-File', (Join-Path $Root 'scripts\run-web-windows.ps1')
Write-Host 'Started API and web in separate PowerShell windows.' -ForegroundColor Green
