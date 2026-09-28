@echo off
setlocal
cd /d "%~dp0"
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0GIT_PUSH_WINDOWS.ps1" %*
endlocal
