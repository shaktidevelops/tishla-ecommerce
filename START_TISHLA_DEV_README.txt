TISHLA DEVELOPMENT START
=========================

Preferred:
  Double-click START_TISHLA_DEV.bat

Or PowerShell:
  Set-ExecutionPolicy -Scope Process Bypass -Force
  .\START_TISHLA_DEV.ps1

The launcher:
  1. Ensures Python environment and dependencies
  2. Ensures Node dependencies
  3. Initializes/updates PostgreSQL schema and seed data
  4. Starts FastAPI and Next.js in separate PowerShell windows
  5. Opens http://localhost:3000

Do not close the API/web windows while developing.
