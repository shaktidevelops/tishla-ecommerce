$ErrorActionPreference='Stop'
$Root=Split-Path -Parent $PSScriptRoot
New-Item -ItemType Directory -Force -Path (Join-Path $Root 'backups') | Out-Null
$pgDump=Get-ChildItem 'C:\Program Files\PostgreSQL' -Filter pg_dump.exe -Recurse -ErrorAction SilentlyContinue | Select-Object -First 1
if(-not $pgDump){ throw 'pg_dump.exe was not found. PostgreSQL client tools are required for this optional backup command.' }
$stamp=Get-Date -Format 'yyyyMMdd-HHmmss'
$out=Join-Path (Join-Path $Root 'backups') "tishla-$stamp.dump"
& $pgDump.FullName -U postgres -h localhost -d tishla -Fc -f $out
if($LASTEXITCODE -ne 0){ throw 'Database backup failed.' }
Write-Host "Created: $out" -ForegroundColor Green
