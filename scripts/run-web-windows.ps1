$ErrorActionPreference = 'Stop'
$Root = Split-Path -Parent $PSScriptRoot
Push-Location (Join-Path $Root 'apps\web')
try { npm run dev }
finally { Pop-Location }
