@echo off
echo ============================================
echo ZRA System Startup
echo ============================================
echo.

echo Checking Python installation...
python --version
if errorlevel 1 (
    echo ERROR: Python not found!
    pause
    exit /b 1
)

echo.
echo Starting ZRA System...
echo.
echo Access the system at:
echo - Main Dashboard: http://localhost:8000/dashboard
echo - Admin Dashboard: http://localhost:8000/admin
echo - Taxpayer Dashboard: http://localhost:8000/taxpayer
echo - Data Sharing: http://localhost:8000/data-sharing
echo - API Documentation: http://localhost:8000/docs
echo.
echo Press Ctrl+C to stop the server
echo.

python main.py

pause
