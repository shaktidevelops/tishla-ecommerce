param([Parameter(Mandatory=$true)][string]$CsvPath,[switch]$Apply,[switch]$Strict)
$ErrorActionPreference = 'Stop'
$Root = Split-Path -Parent $PSScriptRoot
& (Join-Path $Root 'scripts\ensure-python-env-windows.ps1')
$Py = Join-Path $Root 'backend\.venv\Scripts\python.exe'
$args = @((Join-Path $Root 'backend\scripts\import_catalogue_csv.py'), $CsvPath)
if ($Apply) { $args += '--apply' }
if ($Strict) { $args += '--strict' }
& $Py @args
exit $LASTEXITCODE
