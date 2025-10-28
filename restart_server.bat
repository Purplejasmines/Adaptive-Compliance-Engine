@echo off
echo ============================================
echo RESTARTING ZRA COMPLIANCE ENGINE SERVER
echo ============================================
echo.
echo Stopping any running Python processes...
taskkill /F /IM python.exe /T 2>nul
timeout /t 2 /nobreak >nul

echo.
echo Starting server with NEW dashboards from 'dash' folder...
echo.
python main.py

pause
