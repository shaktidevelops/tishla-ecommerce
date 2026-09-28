$ErrorActionPreference = 'Stop'
$Root = Split-Path -Parent $PSScriptRoot
$Venv = Join-Path $Root 'backend\.venv'
$Python = $null

function Find-Python {
    $candidates = @(
        @{ Command = 'py'; Args = @('-3') },
        @{ Command = 'python'; Args = @() },
        @{ Command = 'python3'; Args = @() }
    )
    foreach ($c in $candidates) {
        try {
            $null = & $c.Command @c.Args --version 2>$null
            if ($LASTEXITCODE -eq 0) {
                $ver = (& $c.Command @c.Args -c "import sys; print(f'{sys.version_info.major}.{sys.version_info.minor}')").Trim()
                $parts = $ver.Split('.')
                if ([int]$parts[0] -eq 3 -and [int]$parts[1] -ge 12 -and [int]$parts[1] -le 14) { return ,@($c.Command) + $c.Args }
            }
        } catch {}
    }
    return $null
}

$resolved = Find-Python
if (-not $resolved) { throw 'A usable Python 3.12–3.14 installation was not found. Install CPython from python.org and reopen PowerShell.' }
$pythonCmd = $resolved[0]
$pythonArgs = if ($resolved.Count -gt 1) { $resolved[1..($resolved.Count-1)] } else { @() }

if (-not (Test-Path (Join-Path $Venv 'Scripts\python.exe'))) {
    Write-Host "Creating Python virtual environment..." -ForegroundColor Cyan
    & $pythonCmd @pythonArgs -m venv $Venv
    if ($LASTEXITCODE -ne 0) { throw 'Python virtual environment creation failed.' }
}

$VenvPython = Join-Path $Venv 'Scripts\python.exe'
if (-not (Test-Path $VenvPython)) { throw "Virtual environment Python was not created: $VenvPython" }

& $VenvPython -m pip install --upgrade pip setuptools wheel --disable-pip-version-check
if ($LASTEXITCODE -ne 0) { throw 'pip bootstrap failed.' }
& $VenvPython -m pip install --prefer-binary -r (Join-Path $Root 'backend\requirements.txt') --disable-pip-version-check
if ($LASTEXITCODE -ne 0) { throw 'Python dependency installation failed.' }

Write-Host "Python environment ready: $VenvPython" -ForegroundColor Green
