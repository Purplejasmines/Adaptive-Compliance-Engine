# Start.ps1 - Script to start the FastAPI application

$venvPath = ".\venv"
$pythonPath = "$venvPath\Scripts\python.exe"

# Check if Python exists in virtual environment
if (-not (Test-Path $pythonPath)) {
    Write-Host "Creating virtual environment..."
    python -m venv $venvPath
    if (-not $?) {
        Write-Error "Failed to create virtual environment"
        exit 1
    }
    
    Write-Host "Installing dependencies..."
    & $pythonPath -m pip install --upgrade pip
    & $pythonPath -m pip install -r requirements.txt
    if (-not $?) {
        Write-Error "Failed to install dependencies"
        exit 1
    }
}

# Run the application
Write-Host "Starting FastAPI application..."
Write-Host "API will be available at: http://localhost:8000" -ForegroundColor Green
Write-Host "API documentation: http://localhost:8000/docs" -ForegroundColor Green
Write-Host "Press Ctrl+C to stop" -ForegroundColor Yellow
Write-Host ""

# Run the application
& $pythonPath -m uvicorn main:app --reload --host 0.0.0.0 --port 8000

# Keep the window open if there was an error
if ($LASTEXITCODE -ne 0) {
    Write-Host ""
    Write-Host "The application exited with code $LASTEXITCODE" -ForegroundColor Red
    Write-Host "Press any key to continue..."
    $null = $Host.UI.RawUI.ReadKey('NoEcho,IncludeKeyDown')
}
