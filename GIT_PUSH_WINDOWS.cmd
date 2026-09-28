@echo off
setlocal EnableExtensions
cd /d "%~dp0"
set "MSG=%~1"
if "%MSG%"=="" set "MSG=Update Tishla Commerce Platform"

git rev-parse --is-inside-work-tree >nul 2>&1
if errorlevel 1 (
    echo Initializing Git repository...
    git init
    git branch -M main
)

git config --global user.name "Shakti Develops"
git config --global user.email "shaktidevelops@gmail.com"

rem Protect the active environment file if it was accidentally staged previously.
git ls-files --error-unmatch .env >nul 2>&1
if not errorlevel 1 (
    echo Removing .env from Git tracking while keeping the local file...
    git rm --cached .env
)

echo.
echo Git status:
git status

echo.
git add .
git diff --cached --quiet
if errorlevel 1 (
    git commit -m "%MSG%"
) else (
    echo No staged changes to commit.
)

git remote get-url origin >nul 2>&1
if errorlevel 1 (
    echo.
    echo No GitHub remote named 'origin' is configured yet.
    echo Add it with:
    echo git remote add origin https://github.com/YOUR_USERNAME/tishla-ecommerce.git
    echo Then run this file again.
    pause
    exit /b 2
)

git push
if errorlevel 1 (
    echo.
    echo Git push failed. Check your GitHub authentication or remote URL.
    pause
    exit /b 1
)

echo.
echo Tishla pushed successfully.
endlocal
