# Migration Guide: Old AI System → Improved AI System

## Overview
This guide helps you migrate from the old AI/ML system to the improved version with minimal disruption.

---

## 🎯 Migration Strategy

### Option 1: Gradual Migration (Recommended)
Deploy new system alongside old, gradually shift traffic.

### Option 2: Direct Replacement
Replace old system entirely (requires more testing).

---

## 📋 Pre-Migration Checklist

- [ ] Backup current models and data
- [ ] Review new system documentation
- [ ] Run all tests successfully
- [ ] Train initial models
- [ ] Setup monitoring
- [ ] Prepare rollback plan
- [ ] Notify stakeholders

---

## 🔄 Step-by-Step Migration

### Step 1: Setup (Day 1)

```bash
# Ensure all dependencies are installed
pip install -r requirements.txt

# Run tests to verify installation
pytest tests/test_ml_improvements.py -v

# Run example to verify functionality
python examples/complete_ml_workflow.py
```

### Step 2: Train Initial Models (Day 1-2)

```python
from app.layers.improved_ai_ml import ImprovedAIMLService
import pandas as pd

# Initialize service
ai_service = ImprovedAIMLService(db)

# Load historical data
# Option A: From database
historical_data = pd.read_sql(
    "SELECT * FROM transactions WHERE date >= '2024-01-01'",
    db.connection()
)

# Option B: From CSV
# historical_data = pd.read_csv('historical_data.csv')

# Train models
results = await ai_service.train_models(historical_data)

print(f"Models trained: {len(results)}")
for model_name, result in results.items():
    print(f"  {model_name}: Accuracy={result['metrics']['accuracy']:.4f}")
```

### Step 3: Parallel Deployment (Week 1)

#### Update API Endpoints

**Before:**
```python
# app/api/v1/endpoints/ai.py
from app.layers.ai_ml import AIMLService

@router.post("/api/v1/ai/risk/assess")
async def assess_risk(request: dict, db: Session = Depends(get_db)):
    service = AIMLService(db)
    result = await service.assess_risk(request)
    return result
```

**After (Parallel):**
```python
# app/api/v1/endpoints/ai.py
from app.layers.ai_ml import AIMLService
from app.layers.improved_ai_ml import ImprovedAIMLService
from app.core.config import settings

@router.post("/api/v1/ai/risk/assess")
async def assess_risk(request: dict, db: Session = Depends(get_db)):
    # Feature flag for gradual rollout
    use_improved = request.get("use_improved", False) or settings.USE_IMPROVED_AI
    
    if use_improved:
        service = ImprovedAIMLService(db)
    else:
        service = AIMLService(db)
    
    result = await service.assess_risk(request)
    result["model_version"] = "improved" if use_improved else "legacy"
    return result
```

#### Add Feature Flag

```python
# app/core/config.py
class Settings(BaseSettings):
    # ... existing settings ...
    USE_IMPROVED_AI: bool = False  # Start with False
    IMPROVED_AI_TRAFFIC_PERCENT: int = 0  # Gradually increase
```

### Step 4: A/B Testing (Week 1-2)

```python
# app/api/v1/endpoints/ai.py
import random

@router.post("/api/v1/ai/risk/assess")
async def assess_risk(request: dict, db: Session = Depends(get_db)):
    # Randomly route traffic based on percentage
    use_improved = random.random() < (settings.IMPROVED_AI_TRAFFIC_PERCENT / 100)
    
    if use_improved:
        service = ImprovedAIMLService(db)
        model_version = "improved"
    else:
        service = AIMLService(db)
        model_version = "legacy"
    
    result = await service.assess_risk(request)
    result["model_version"] = model_version
    
    # Log for comparison
    await log_prediction(result, model_version)
    
    return result
```

#### Traffic Rollout Schedule

| Week | Traffic % | Action |
|------|-----------|--------|
| 1 | 10% | Monitor metrics, fix issues |
| 2 | 25% | Compare performance |
| 3 | 50% | Validate at scale |
| 4 | 100% | Full migration |

### Step 5: Monitor & Compare (Ongoing)

```python
# app/monitoring/comparison.py
async def compare_model_performance():
    """Compare old vs new model performance"""
    
    # Get predictions from both models
    legacy_metrics = await get_legacy_metrics()
    improved_metrics = await get_improved_metrics()
    
    comparison = {
        "accuracy": {
            "legacy": legacy_metrics["accuracy"],
            "improved": improved_metrics["accuracy"],
            "improvement": improved_metrics["accuracy"] - legacy_metrics["accuracy"]
        },
        "latency": {
            "legacy": legacy_metrics["avg_latency_ms"],
            "improved": improved_metrics["avg_latency_ms"],
            "improvement_pct": (
                (legacy_metrics["avg_latency_ms"] - improved_metrics["avg_latency_ms"]) 
                / legacy_metrics["avg_latency_ms"] * 100
            )
        },
        "false_positives": {
            "legacy": legacy_metrics["false_positive_rate"],
            "improved": improved_metrics["false_positive_rate"],
            "reduction_pct": (
                (legacy_metrics["false_positive_rate"] - improved_metrics["false_positive_rate"])
                / legacy_metrics["false_positive_rate"] * 100
            )
        }
    }
    
    return comparison
```

### Step 6: Full Migration (Week 4)

```python
# app/api/v1/endpoints/ai.py
from app.layers.improved_ai_ml import ImprovedAIMLService

@router.post("/api/v1/ai/risk/assess")
async def assess_risk(request: dict, db: Session = Depends(get_db)):
    # Use improved service exclusively
    service = ImprovedAIMLService(db)
    result = await service.assess_risk(request)
    return result
```

### Step 7: Cleanup (Week 5)

```python
# Remove old AI/ML service
# Keep for reference but mark as deprecated

# app/layers/ai_ml.py
"""
DEPRECATED: Use improved_ai_ml.py instead
This file is kept for reference only
"""
```

---

## 🔍 Validation & Testing

### Functional Testing

```python
# tests/test_migration.py
import pytest
from app.layers.ai_ml import AIMLService
from app.layers.improved_ai_ml import ImprovedAIMLService

@pytest.mark.asyncio
async def test_api_compatibility():
    """Ensure new service maintains API compatibility"""
    
    request = {
        "entity_id": "ENT-001",
        "data_points": {
            "transaction_amount": 50000,
            "transaction_frequency": 10
        },
        "risk_types": ["fraud", "compliance"]
    }
    
    # Both should accept same input
    legacy_service = AIMLService(db)
    improved_service = ImprovedAIMLService(db)
    
    legacy_result = await legacy_service.assess_risk(request)
    improved_result = await improved_service.assess_risk(request)
    
    # Check structure compatibility
    assert "entity_id" in improved_result
    assert "results" in improved_result
    assert "timestamp" in improved_result
```

### Performance Testing

```python
# tests/test_performance.py
import time
import pytest

@pytest.mark.asyncio
async def test_latency_improvement():
    """Verify improved latency"""
    
    improved_service = ImprovedAIMLService(db)
    
    start = time.time()
    result = await improved_service.assess_risk(test_request)
    latency = (time.time() - start) * 1000
    
    assert latency < 100, f"Latency {latency}ms exceeds 100ms threshold"
```

### Accuracy Testing

```python
# tests/test_accuracy.py
@pytest.mark.asyncio
async def test_accuracy_improvement():
    """Verify improved accuracy"""
    
    improved_service = ImprovedAIMLService(db)
    
    # Test on validation set
    predictions = []
    for sample in validation_set:
        result = await improved_service.assess_risk(sample)
        predictions.append(result)
    
    accuracy = calculate_accuracy(predictions, ground_truth)
    
    assert accuracy > 0.85, f"Accuracy {accuracy} below threshold"
```

---

## 🚨 Rollback Plan

### If Issues Occur

```python
# Quick rollback
# app/core/config.py
USE_IMPROVED_AI = False
IMPROVED_AI_TRAFFIC_PERCENT = 0

# Restart services
docker-compose restart
```

### Rollback Checklist

- [ ] Set feature flags to 0%
- [ ] Restart services
- [ ] Verify old system working
- [ ] Investigate issues
- [ ] Fix and redeploy
- [ ] Resume migration

---

## 📊 Success Criteria

### Must Have (Go/No-Go)
- ✅ All tests passing
- ✅ Accuracy ≥ 85%
- ✅ Latency < 100ms (P95)
- ✅ No critical bugs
- ✅ Monitoring working

### Should Have
- ✅ Accuracy improvement > 5%
- ✅ Latency improvement > 20%
- ✅ False positive reduction > 10%
- ✅ Explainability available
- ✅ Drift detection active

### Nice to Have
- ✅ Fairness monitoring
- ✅ Model governance
- ✅ Real-time processing
- ✅ Hyperparameter tuning
- ✅ Comprehensive docs

---

## 🔧 Configuration Updates

### Environment Variables

```bash
# .env
# Old settings (keep for now)
AI_MODEL_PATH=models/legacy/

# New settings
IMPROVED_AI_MODEL_PATH=models/
USE_IMPROVED_AI=true
IMPROVED_AI_TRAFFIC_PERCENT=100

# Drift detection
DRIFT_CHECK_ENABLED=true
DRIFT_CHECK_INTERVAL_HOURS=24

# Fairness monitoring
FAIRNESS_CHECK_ENABLED=true
PROTECTED_ATTRIBUTES=region,age_group

# Model governance
MODEL_GOVERNANCE_ENABLED=true
REQUIRED_APPROVALS=2
```

### Database Migrations

```sql
-- Add new tables for model governance
CREATE TABLE IF NOT EXISTS model_registry (
    model_id VARCHAR(100) PRIMARY KEY,
    model_name VARCHAR(100),
    version VARCHAR(50),
    status VARCHAR(50),
    metrics JSONB,
    registered_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS model_predictions (
    prediction_id VARCHAR(100) PRIMARY KEY,
    model_id VARCHAR(100),
    entity_id VARCHAR(100),
    prediction JSONB,
    explanation JSONB,
    created_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_predictions_entity ON model_predictions(entity_id);
CREATE INDEX idx_predictions_created ON model_predictions(created_at);
```

---

## 📈 Monitoring Dashboard

### Key Metrics to Track

```python
# app/monitoring/dashboards.py
MIGRATION_METRICS = {
    "model_performance": [
        "accuracy",
        "precision",
        "recall",
        "f1_score",
        "roc_auc"
    ],
    "system_performance": [
        "avg_latency_ms",
        "p95_latency_ms",
        "p99_latency_ms",
        "throughput_per_sec",
        "error_rate"
    ],
    "business_metrics": [
        "fraud_detection_rate",
        "false_positive_rate",
        "false_negative_rate",
        "total_predictions",
        "high_risk_alerts"
    ],
    "quality_metrics": [
        "drift_detected",
        "fairness_score",
        "explanation_coverage",
        "data_quality_score"
    ]
}
```

---

## 🎓 Team Training

### For Developers
1. Review `ML_QUICK_START.md`
2. Run examples locally
3. Understand new API
4. Practice integration

### For Data Scientists
1. Review `AI_IMPROVEMENTS_GUIDE.md`
2. Understand new features
3. Learn model governance
4. Practice model training

### For Operations
1. Understand monitoring
2. Learn rollback procedures
3. Practice incident response
4. Review SLAs

---

## ✅ Post-Migration Tasks

### Week 1
- [ ] Monitor all metrics
- [ ] Address any issues
- [ ] Gather user feedback
- [ ] Document lessons learned

### Month 1
- [ ] Optimize performance
- [ ] Fine-tune models
- [ ] Update documentation
- [ ] Plan next improvements

### Quarter 1
- [ ] Full performance review
- [ ] ROI analysis
- [ ] Plan advanced features
- [ ] Share success story

---

## 📞 Support During Migration

### Contact Points
- **Technical Issues**: ai-ml-team@zra.go.tz
- **Business Questions**: product-team@zra.go.tz
- **Urgent Issues**: on-call engineer (Slack: #ai-ml-support)

### Office Hours
- **Daily Standups**: 9:00 AM (during migration)
- **Weekly Reviews**: Friday 2:00 PM
- **On-call Support**: 24/7 during migration period

---

## 🎉 Migration Complete!

Once you've successfully migrated:

1. ✅ Update documentation
2. ✅ Celebrate with team! 🎊
3. ✅ Share metrics with stakeholders
4. ✅ Plan next improvements
5. ✅ Document lessons learned

---

**Good luck with your migration! 🚀**

*For questions, refer to documentation or contact the AI/ML team.*
