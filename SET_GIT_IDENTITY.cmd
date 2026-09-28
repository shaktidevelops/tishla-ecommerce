@echo off
setlocal
cd /d "%~dp0"
git config --global user.name "Shakti Develops"
git config --global user.email "shaktidevelops@gmail.com"
echo.
echo Git identity configured:
echo   Name : Shakti Develops
echo   Email: shaktidevelops@gmail.com
echo.
git config --global --get user.name
git config --global --get user.email
endlocal
pause
