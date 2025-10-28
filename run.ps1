# Run.ps1 - Script to start the FastAPI application

# Check if virtual environment exists
$venvPath = ".\venv"
$activateScript = "$venvPath\Scripts\Activate.ps1"

if (-not (Test-Path $activateScript)) {
    Write-Host "Creating virtual environment..."
    python -m venv $venvPath
    if (-not $?) {
        Write-Error "Failed to create virtual environment"
        exit 1
    }
    
    Write-Host "Installing dependencies..."
    & "$venvPath\Scripts\pip" install -r requirements.txt
    if (-not $?) {
        Write-Error "Failed to install dependencies"
        exit 1
    }
}

# Activate virtual environment and run the app
Write-Host "Starting FastAPI application..."
Write-Host "Press Ctrl+C to stop" -ForegroundColor Yellow
Write-Host ""

& {
    & $activateScript
    uvicorn main:app --reload --host 0.0.0.0 --port 8000
}

# Keep the window open if there was an error
if ($LASTEXITCODE -ne 0) {
    Write-Host ""
    Write-Host "The application exited with code $LASTEXITCODE" -ForegroundColor Red
    Write-Host "Press any key to continue..."
    $null = $Host.UI.RawUI.ReadKey('NoEcho,IncludeKeyDown')
}
