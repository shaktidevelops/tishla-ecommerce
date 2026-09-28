@echo off
setlocal EnableExtensions
cd /d "%~dp0"

title Tishla Frontend - Next.js

echo ==========================================================
echo          TISHLA FRONTEND - DIRECT NEXT.JS
echo ==========================================================
echo Project: %CD%
echo.

where node >nul 2>&1
if errorlevel 1 (
    echo ERROR: Node.js is not available in PATH.
    echo Install Node.js 22 LTS, then CLOSE and reopen Command Prompt.
    echo.
    pause
    exit /b 1
)

where npm >nul 2>&1
if errorlevel 1 (
    echo ERROR: npm is not available in PATH.
    echo.
    pause
    exit /b 1
)

for /f "tokens=1 delims=." %%V in ('node -p "process.versions.node"') do set NODE_MAJOR=%%V
echo Node.js: 
node --version
echo npm:
npm --version
echo.

if not defined NODE_MAJOR (
    echo ERROR: Could not determine Node.js version.
    pause
    exit /b 1
)

if %NODE_MAJOR% LSS 20 (
    echo ERROR: This frontend requires Node.js 20.9 or newer.
    echo Your Node.js major version is %NODE_MAJOR%.
    echo Install Node.js 22 LTS and reopen Command Prompt.
    echo.
    pause
    exit /b 1
)

if not exist "%CD%\package.json" (
    echo ERROR: Root package.json not found.
    echo Expected: %CD%\package.json
    pause
    exit /b 1
)

if not exist "%CD%\apps\web\package.json" (
    echo ERROR: apps\web\package.json not found.
    pause
    exit /b 1
)

echo [1/2] Checking frontend dependencies...
if not exist "%CD%\node_modules\next" if not exist "%CD%\apps\web\node_modules\next" (
    echo Dependencies are missing. Running npm install...
    call npm install --no-audit --no-fund
    if errorlevel 1 (
        echo.
        echo ==========================================================
        echo ERROR: npm install FAILED.
        echo ==========================================================
        echo The real npm error is shown above.
        echo Do not open Chrome yet.
        echo.
        pause
        exit /b 1
    )
) else (
    echo Dependencies are present.
)

echo.
echo [2/2] Starting Next.js development server...
echo.
echo IMPORTANT:
echo - Keep this window open.
echo - The actual Next.js error, if any, will appear here.
echo - Browser will NOT be opened automatically.
echo.
echo Opening another Command Prompt is intentionally avoided.
echo This prevents the previous launcher/quoting problem.
echo.
echo URL after successful startup:
echo     http://localhost:3000
echo.

call npm --workspace apps/web run dev -- --hostname 127.0.0.1 --port 3000

echo.
echo ==========================================================
echo Next.js process stopped.
echo ==========================================================
echo Review the error/output above.
echo.
pause
endlocal
