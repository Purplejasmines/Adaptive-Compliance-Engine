# AI/ML Compliance Engine - Quick Start Guide

## 🚀 Quick Start (5 Minutes)

### 1. Install Dependencies
```bash
pip install -r requirements.txt
```

### 2. Run Complete Example
```bash
python examples/complete_ml_workflow.py
```

This will demonstrate:
- ✅ Data validation and quality checks
- ✅ Feature engineering (50+ features)
- ✅ Model training (Random Forest, XGBoost)
- ✅ Ensemble optimization
- ✅ SHAP/LIME explanations
- ✅ Drift detection
- ✅ Bias monitoring
- ✅ Model governance
- ✅ Real-time processing

### 3. Run Tests
```bash
pytest tests/test_ml_improvements.py -v
```

## 📖 Basic Usage Examples

### Train a Model
```python
from app.ml import ModelTrainer
import pandas as pd

# Load your data
df = pd.read_csv('training_data.csv')
X = df[feature_columns].values
y = df['is_fraud'].values

# Train model
trainer = ModelTrainer(model_dir="models")
result = trainer.train_fraud_detection_model(X, y, model_type="xgboost")

# Save model
model_path = trainer.save_model(result["model_name"])
print(f"Model saved: {model_path}")
print(f"Accuracy: {result['metrics']['accuracy']:.4f}")
```

### Make Predictions with Explanations
```python
from app.ml import ModelTrainer, ExplainabilityEngine

# Load model
trainer = ModelTrainer()
loaded = trainer.load_model("fraud_detection_xgboost", version="latest")
model = loaded["model"]
scaler = loaded["scaler"]

# Setup explainer
explainer = ExplainabilityEngine()
explainer.setup_shap_explainer(model, X_train[:100], "fraud_model")

# Make prediction
X_test_scaled = scaler.transform(X_test)
prediction = model.predict(X_test_scaled[0:1])[0]

# Get explanation
explanation = explainer.explain_prediction_shap(
    "fraud_model", X_test_scaled[0:1], feature_names
)

print(f"Prediction: {prediction}")
print(f"Explanation: {explanation['summary']}")
```

### Monitor for Drift
```python
from app.ml import DriftDetector

# Set baseline
detector = DriftDetector()
detector.set_baseline(X_train, y_train, feature_names)

# Check current data for drift
drift_result = detector.detect_feature_drift(X_current, feature_names)

if drift_result["overall_drift"]:
    print(f"⚠️ Drift detected in {drift_result['drift_percentage']:.1f}% of features")
    print(f"Features affected: {drift_result['features_with_drift']}")
    print("Recommendation: Retrain model")
```

### Check Fairness
```python
from app.ml import BiasMonitor

monitor = BiasMonitor()

# Generate fairness report
report = monitor.generate_fairness_report(
    model_name="fraud_model",
    y_true=y_test,
    y_pred=predictions,
    y_pred_proba=probabilities,
    protected_attrs={'region': region_data, 'age_group': age_data}
)

print(f"Fairness: {report['overall_fairness']}")

if report['requires_attention']:
    recommendations = monitor.get_mitigation_recommendations(report)
    for rec in recommendations:
        print(f"  - {rec}")
```

### Real-Time Processing
```python
from app.ml import RealTimeProcessor
import asyncio

async def process_transactions():
    # Setup processor
    processor = RealTimeProcessor(batch_size=32)
    processor.set_model(model, scaler)
    
    # Start processing
    asyncio.create_task(processor.start_processing())
    
    # Add transactions
    for transaction in transactions:
        txn_id = await processor.add_transaction(transaction)
        
        # Get result (non-blocking)
        result = await processor.get_result(txn_id, timeout=1.0)
        if result:
            print(f"Transaction {txn_id}: {result['risk_level']}")
    
    # Get metrics
    metrics = processor.get_metrics()
    print(f"Processed: {metrics['processed_count']}")
    print(f"Latency P95: {metrics['latency_p95']:.2f}ms")

asyncio.run(process_transactions())
```

## 🔧 Integration with Existing Code

### Update Your AI Service
```python
# Old way (app/layers/ai_ml.py)
from app.layers.ai_ml import AIMLService

# New way (app/layers/improved_ai_ml.py)
from app.layers.improved_ai_ml import ImprovedAIMLService

# Initialize
ai_service = ImprovedAIMLService(db)

# Train models (do this once or on schedule)
await ai_service.train_models()

# Use for predictions (same interface, better results)
assessment = await ai_service.assess_risk({
    "entity_id": "ENT-001",
    "data_points": {...},
    "risk_types": ["fraud", "compliance"]
})
```

### API Endpoint Example
```python
from fastapi import APIRouter, Depends
from app.layers.improved_ai_ml import ImprovedAIMLService

router = APIRouter()

@router.post("/api/v1/ai/risk/assess")
async def assess_risk(request: dict, ai_service: ImprovedAIMLService = Depends()):
    """Enhanced risk assessment with validation and explainability"""
    result = await ai_service.assess_risk(request)
    return result

@router.get("/api/v1/ai/models/{model_id}/explain")
async def explain_prediction(
    model_id: str, 
    data_points: dict,
    ai_service: ImprovedAIMLService = Depends()
):
    """Get explanation for a prediction"""
    explanation = await ai_service.explain_prediction({
        "model_id": model_id,
        "data_points": data_points,
        "explanation_type": "shap"
    })
    return explanation

@router.get("/api/v1/ai/drift/check")
async def check_drift(ai_service: ImprovedAIMLService = Depends()):
    """Check for model drift"""
    # Get recent data
    X_current = get_recent_transactions()
    drift_result = await ai_service.check_model_drift(X_current)
    return drift_result
```

## 📊 Monitoring & Maintenance

### Daily Tasks
```python
# Check drift
drift = await ai_service.check_model_drift(yesterday_data)
if drift["overall_drift"]:
    send_alert("Model drift detected")

# Check fairness
fairness_report = monitor.generate_fairness_report(...)
if fairness_report["requires_attention"]:
    send_alert("Bias detected in model")
```

### Weekly Tasks
```python
# Retrain models
new_data = load_last_week_data()
await ai_service.retrain_model("fraud_detection_xgboost", new_data)

# Review performance
performance = await ai_service.get_model_performance()
log_metrics(performance)
```

### Monthly Tasks
```python
# Hyperparameter tuning
tuner = HyperparameterTuner(n_trials=100)
result = tuner.tune_xgboost(X_train, y_train)

# Retrain with best params
# ... use result['best_params']

# Generate compliance report
governance = ModelGovernance()
report = governance.generate_compliance_report(model_id)
```

## 🎯 Performance Benchmarks

### Training Performance
- **Random Forest**: ~30 seconds (5000 samples, 50 features)
- **XGBoost**: ~20 seconds (5000 samples, 50 features)
- **LightGBM**: ~15 seconds (5000 samples, 50 features)

### Inference Performance
- **Single Prediction**: <10ms
- **Batch (32)**: <50ms
- **Batch (1000)**: <500ms

### Feature Engineering
- **50+ features**: ~2 seconds (5000 samples)

### Explainability
- **SHAP**: ~100ms per prediction
- **LIME**: ~200ms per prediction

## 🐛 Troubleshooting

### Model Training Fails
```python
# Check data quality
validator = DataValidator()
quality = validator.check_data_quality(df)
if quality['quality_score'] < 50:
    print(f"Issues: {quality['issues']}")
```

### Low Model Performance
```python
# Try hyperparameter tuning
tuner = HyperparameterTuner(n_trials=50)
result = tuner.tune_xgboost(X_train, y_train)

# Use best params
# ... retrain with result['best_params']
```

### Drift Detected
```python
# Retrain model with recent data
recent_data = load_recent_data()
await ai_service.retrain_model(model_id, recent_data)

# Verify drift is resolved
new_drift = await ai_service.check_model_drift(test_data)
```

### Bias Detected
```python
# Get recommendations
recommendations = monitor.get_mitigation_recommendations(fairness_report)

# Common solutions:
# 1. Rebalance training data
# 2. Adjust decision thresholds per group
# 3. Apply fairness constraints
# 4. Recalibrate predictions
```

## 📚 Additional Resources

- **Full Guide**: `AI_IMPROVEMENTS_GUIDE.md`
- **API Docs**: `API_DOCUMENTATION.md`
- **Tests**: `tests/test_ml_improvements.py`
- **Examples**: `examples/complete_ml_workflow.py`

## 💡 Tips & Best Practices

1. **Always validate input data** before predictions
2. **Monitor drift regularly** (daily recommended)
3. **Check fairness** across protected attributes
4. **Version your models** with governance system
5. **Log all predictions** with explanations for audit
6. **Retrain periodically** (weekly/monthly based on drift)
7. **Use ensemble methods** for better accuracy
8. **Cache frequent predictions** in Redis
9. **Batch predictions** for better throughput
10. **Test in staging** before production deployment

## 🎓 Learning Path

1. **Start**: Run `examples/complete_ml_workflow.py`
2. **Understand**: Read `AI_IMPROVEMENTS_GUIDE.md`
3. **Practice**: Modify examples with your data
4. **Test**: Run `pytest tests/test_ml_improvements.py`
5. **Integrate**: Update your API endpoints
6. **Deploy**: Follow governance workflow
7. **Monitor**: Setup drift and fairness checks
8. **Optimize**: Use hyperparameter tuning

## 🚀 Ready to Deploy?

### Pre-deployment Checklist
- [ ] Models trained and validated
- [ ] Tests passing
- [ ] Drift detection configured
- [ ] Fairness monitoring setup
- [ ] Models registered in governance
- [ ] Approval workflow completed
- [ ] API endpoints updated
- [ ] Monitoring dashboards ready
- [ ] Documentation updated
- [ ] Team trained

### Deploy Command
```bash
# After approval
governance.promote_to_production(model_id, user="admin")

# Update API to use new models
# Restart services
docker-compose restart
```

---

**Need Help?** Check the documentation or contact the AI/ML team.
