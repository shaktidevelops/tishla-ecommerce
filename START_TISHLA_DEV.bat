@echo off
setlocal
cd /d "%~dp0"
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0START_TISHLA_DEV.ps1"
if errorlevel 1 (
  echo.
  echo Tishla startup failed. Review the error above.
  pause
)
