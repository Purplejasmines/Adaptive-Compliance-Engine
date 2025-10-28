@echo off
echo Installing ZRA AI Compliance Engine Dependencies
echo ================================================

echo.
echo [1/5] Installing Core Framework...
pip install fastapi uvicorn pydantic sqlalchemy

echo.
echo [2/5] Installing Database Drivers...
pip install psycopg2-binary redis neo4j

echo.
echo [3/5] Installing AI/ML Core...
pip install scikit-learn xgboost lightgbm numpy pandas

echo.
echo [4/5] Installing Explainability Tools...
pip install shap lime

echo.
echo [5/5] Installing Additional Tools...
pip install prophet optuna python-dotenv requests

echo.
echo ================================================
echo Installation Complete!
echo.
echo Optional: Install Deep Learning (takes longer)
echo   pip install torch transformers
echo.
pause
