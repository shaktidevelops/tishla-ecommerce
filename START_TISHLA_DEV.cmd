@echo off
setlocal EnableExtensions EnableDelayedExpansion

cd /d "%~dp0"
set "ROOT=%CD%"

echo.
echo ==================================================
echo        TISHLA LOCAL DEVELOPMENT LAUNCHER
echo ==================================================
echo Root: %ROOT%
echo.

if not exist "%ROOT%\.env" (
    echo ERROR: .env not found in project root.
    pause
    exit /b 1
)

if not exist "%ROOT%\backend\.venv\Scripts\python.exe" (
    echo ERROR: Python virtual environment not found:
    echo        %ROOT%\backend\.venv\Scripts\python.exe
    pause
    exit /b 1
)

where node >nul 2>&1
if errorlevel 1 (
    echo ERROR: Node.js is not installed or not on PATH.
    pause
    exit /b 1
)

where npm >nul 2>&1
if errorlevel 1 (
    echo ERROR: npm is not installed or not on PATH.
    pause
    exit /b 1
)

echo [1/3] Starting Tishla API on port 8000...
start "Tishla API" cmd /k "cd /d ""%ROOT%\backend"" && set ""PYTHONPATH=%ROOT%\backend"" && ""%ROOT%\backend\.venv\Scripts\python.exe"" -m uvicorn app.main:app --reload --host 127.0.0.1 --port 8000"

echo Waiting for API...
set "API_READY=0"
for /L %%I in (1,1,60) do (
    curl.exe -fsS --max-time 1 http://127.0.0.1:8000/health >nul 2>&1
    if not errorlevel 1 (
        set "API_READY=1"
        goto :api_ready
    )
    timeout /t 1 /nobreak >nul
)

:api_ready
if "!API_READY!"=="0" (
    echo.
    echo ERROR: FastAPI did not become ready within 60 seconds.
    echo Check the "Tishla API" window for the database/startup error.
    echo.
    pause
    exit /b 1
)

echo API is ready.
echo.

echo [2/3] Starting Next.js on port 3000...
start "Tishla Web" cmd /k "cd /d ""%ROOT%"" && npm --workspace apps\web run dev -- --hostname 127.0.0.1 --port 3000"

echo Waiting for Next.js...
set "WEB_READY=0"
for /L %%I in (1,1,90) do (
    curl.exe -fsS --max-time 1 http://127.0.0.1:3000/ >nul 2>&1
    if not errorlevel 1 (
        set "WEB_READY=1"
        goto :web_ready
    )
    timeout /t 1 /nobreak >nul
)

:web_ready
if "!WEB_READY!"=="0" (
    echo.
    echo ERROR: Next.js did not become ready within 90 seconds.
    echo Check the "Tishla Web" window for the actual Next.js build error.
    echo.
    pause
    exit /b 1
)

echo Web is ready.
echo.

echo [3/3] Opening Tishla Control Room...
start "" "http://localhost:3000/admin"

echo.
echo ==================================================
echo TISHLA IS RUNNING
echo Storefront : http://localhost:3000
echo Admin      : http://localhost:3000/admin
echo API        : http://127.0.0.1:8000/health
echo ==================================================
echo.
echo Keep the "Tishla API" and "Tishla Web" windows open.
echo.
endlocal
exit /b 0
