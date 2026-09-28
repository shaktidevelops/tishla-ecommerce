$ErrorActionPreference='Stop'
$node = Get-Command node -ErrorAction SilentlyContinue
$npm = Get-Command npm -ErrorAction SilentlyContinue
if(-not $node -or -not $npm){
  throw 'Node.js/npm was not found. Install Node.js 22 LTS (or another supported Node 20.9+ release) from https://nodejs.org/ and reopen PowerShell.'
}
$ver = (& node --version).TrimStart('v')
$major = [int]($ver.Split('.')[0])
if($major -lt 20){ throw "Node.js $ver is too old. Tishla requires Node.js 20.9+; Node 22 LTS is recommended." }
Write-Host "Node.js $ver detected." -ForegroundColor Green
