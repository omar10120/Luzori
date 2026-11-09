@echo off
REM Luzori Cashier Mode — silent print to default printer (no print dialog)
REM 1) Set your thermal printer as Windows Default Printer
REM 2) Double-click this file to open the dashboard in Chrome cashier mode

set "CHROME="
if exist "%ProgramFiles%\Google\Chrome\Application\chrome.exe" set "CHROME=%ProgramFiles%\Google\Chrome\Application\chrome.exe"
if exist "%ProgramFiles(x86)%\Google\Chrome\Application\chrome.exe" set "CHROME=%ProgramFiles(x86)%\Google\Chrome\Application\chrome.exe"
if exist "%LocalAppData%\Google\Chrome\Application\chrome.exe" set "CHROME=%LocalAppData%\Google\Chrome\Application\chrome.exe"

if "%CHROME%"=="" (
  echo Google Chrome not found. Please install Chrome.
  pause
  exit /b 1
)

REM Change this URL to your center dashboard if needed
set "DASHBOARD_URL=https://dashboard.luzori.com"

start "" "%CHROME%" --kiosk-printing --disable-print-preview "%DASHBOARD_URL%"
