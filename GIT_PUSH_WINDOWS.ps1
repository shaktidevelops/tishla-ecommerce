param(
  [string]$Message = "Update Tishla Commerce Platform"
)
$ErrorActionPreference = "Stop"
Set-Location (Split-Path -Parent $MyInvocation.MyCommand.Path)
Write-Host "Tishla Git push" -ForegroundColor Magenta
if (-not (Test-Path .git)) { git init | Out-Host; git branch -M main }
git status
git add .
if (-not (git diff --cached --quiet)) {
  git commit -m $Message | Out-Host
} else {
  Write-Host "Nothing new to commit." -ForegroundColor Yellow
}
git push
