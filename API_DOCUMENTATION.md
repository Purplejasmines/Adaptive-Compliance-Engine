# ZRA System API Documentation

## Overview
The Zero-Trust Revenue Administration (ZRA) system provides a comprehensive API for tax compliance, fraud detection, and regulatory oversight.

## Base URL
```
http://localhost:8000/api/v1
```

## Authentication
All API endpoints (except health checks) require authentication using JWT tokens.

### Login
```http
POST /security/auth/login
Content-Type: application/json

{
  "username": "admin",
  "password": "admin123",
  "device_id": "device-001",
  "location": {
    "lat": -6.7924,
    "lng": 39.2083
  }
}
```

**Response:**
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "token_type": "bearer",
  "expires_in": 1800,
  "user_info": {
    "username": "admin",
    "device_id": "device-001",
    "location": {"lat": -6.7924, "lng": 39.2083}
  }
}
```

## API Endpoints

### 1. Governance & Policy Layer

#### Get Policies
```http
GET /governance/policies?category=tax&status=active
Authorization: Bearer <token>
```

#### Create Policy
```http
POST /governance/policies
Authorization: Bearer <token>
Content-Type: application/json

{
  "policy_id": "POL-003",
  "policy_content": "New policy content...",
  "effective_date": "2024-02-01",
  "compliance_requirements": ["filing", "payment"]
}
```

#### Check Compliance
```http
GET /governance/policies/{policy_id}/compliance?entity_id=entity-001
Authorization: Bearer <token>
```

#### Get Ethics Monitoring
```http
GET /governance/ethics/monitoring?model_id=MODEL-001
Authorization: Bearer <token>
```

### 2. Data & Integration Layer

#### Get Data Sources
```http
GET /data/sources?source_type=EFD&status=active
Authorization: Bearer <token>
```

#### Add Data Source
```http
POST /data/sources
Authorization: Bearer <token>
Content-Type: application/json

{
  "source_type": "EFD",
  "endpoint": "https://efd.tra.go.tz/api",
  "credentials": {"api_key": "secret"},
  "data_schema": {"fields": ["amount", "timestamp"]}
}
```

#### Get Source Data
```http
GET /data/sources/{source_id}/data?start_date=2024-01-01&end_date=2024-01-31&limit=100
Authorization: Bearer <token>
```

#### Get Data Lineage
```http
GET /data/lineage/{entity_id}
Authorization: Bearer <token>
```

#### Manage Consent
```http
POST /data/consent
Authorization: Bearer <token>
Content-Type: application/json

{
  "entity_id": "entity-001",
  "data_types": ["sales", "transactions"],
  "consent_given": true,
  "consent_date": "2024-01-01",
  "expiry_date": "2025-01-01"
}
```

### 3. AI/ML Core

#### Assess Risk
```http
POST /ai/risk/assess
Authorization: Bearer <token>
Content-Type: application/json

{
  "entity_id": "entity-001",
  "data_points": {
    "transaction_amount": 50000,
    "transaction_frequency": 10,
    "unusual_location": false
  },
  "risk_types": ["fraud", "anomaly", "compliance"]
}
```

#### Get Risk Scores
```http
GET /ai/risk/scores/{entity_id}?risk_type=fraud
Authorization: Bearer <token>
```

#### Explain Prediction
```http
POST /ai/explain
Authorization: Bearer <token>
Content-Type: application/json

{
  "model_id": "MODEL-001",
  "prediction_id": "PRED-001",
  "explanation_type": "SHAP"
}
```

#### Get Models
```http
GET /ai/models?model_type=fraud_detection&status=active
Authorization: Bearer <token>
```

#### Train Model
```http
POST /ai/models/train
Authorization: Bearer <token>
Content-Type: application/json

{
  "data": [{"feature1": 1.0, "feature2": 2.0}],
  "labels": [0, 1],
  "model_type": "fraud_detection"
}
```

#### Detect Anomalies
```http
GET /ai/anomalies?entity_id=entity-001&threshold=0.8
Authorization: Bearer <token>
```

### 4. Security & Zero-Trust Layer

#### Verify Authentication
```http
POST /security/auth/verify
Authorization: Bearer <token>
Content-Type: application/json

{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "context": {"location": {"lat": -6.7924, "lng": 39.2083}}
}
```

#### Check Access
```http
POST /security/access/check
Authorization: Bearer <token>
Content-Type: application/json

{
  "user_id": "user-001",
  "resource": "admin_dashboard",
  "action": "read",
  "context": {"time": {"hour": 14}}
}
```

#### Get Audit Ledger
```http
GET /security/audit/ledger?entity_id=entity-001&event_type=data_access
Authorization: Bearer <token>
```

#### Verify Audit Integrity
```http
GET /security/audit/verify/{audit_id}
Authorization: Bearer <token>
```

#### Get Detected Threats
```http
GET /security/threats/detected?threat_type=brute_force&severity=high
Authorization: Bearer <token>
```

### 5. User Interaction Layer

#### Get Officer Dashboard
```http
GET /users/dashboard/officer?filters={"status":"active"}
Authorization: Bearer <token>
```

#### Get Taxpayer Dashboard
```http
GET /users/dashboard/taxpayer/{entity_id}
Authorization: Bearer <token>
```

#### Get Donor Dashboard
```http
GET /users/dashboard/donor?filters={"period":"30d"}
Authorization: Bearer <token>
```

#### Get Cases
```http
GET /users/cases?status=active&priority=high&assigned_officer=officer-001
Authorization: Bearer <token>
```

#### Create Case
```http
POST /users/cases
Authorization: Bearer <token>
Content-Type: application/json

{
  "case_type": "fraud_investigation",
  "priority": "high",
  "description": "Suspicious transaction patterns detected",
  "assigned_officer": "officer-001"
}
```

#### Get Compliance Scores
```http
GET /users/compliance/scores/{entity_id}?score_type=overall
Authorization: Bearer <token>
```

#### Get Obligations
```http
GET /users/obligations/{entity_id}?obligation_type=tax_filing&status=pending
Authorization: Bearer <token>
```

#### Get Heatmaps
```http
GET /users/heatmaps?heatmap_type=fraud&region=Dar es Salaam&time_period=30d
Authorization: Bearer <token>
```

### 6. Monitoring & Reporting Layer

#### Get KPIs
```http
GET /monitoring/kpis?kpi_type=revenue_collection&time_period=30d
Authorization: Bearer <token>
```

#### Get KPI Trends
```http
GET /monitoring/kpis/trends?kpi_type=compliance_rate&time_period=90d
Authorization: Bearer <token>
```

#### Get Incidents
```http
GET /monitoring/incidents?status=active&severity=high&time_period=7d
Authorization: Bearer <token>
```

#### Create Incident
```http
POST /monitoring/incidents
Authorization: Bearer <token>
Content-Type: application/json

{
  "incident_type": "system_outage",
  "severity": "high",
  "description": "Database connection timeout",
  "affected_entities": ["entity-001", "entity-002"]
}
```

#### Get Available Reports
```http
GET /monitoring/reports/available?report_category=revenue
Authorization: Bearer <token>
```

#### Generate Report
```http
POST /monitoring/reports/generate
Authorization: Bearer <token>
Content-Type: application/json

{
  "report_type": "monthly_revenue",
  "format": "pdf",
  "parameters": {"month": "2024-01"}
}
```

#### Get Alerts
```http
GET /monitoring/alerts?alert_type=kpi_threshold&status=active
Authorization: Bearer <token>
```

## Error Responses

### 400 Bad Request
```json
{
  "error": "Invalid request data",
  "details": "Missing required field: entity_id"
}
```

### 401 Unauthorized
```json
{
  "error": "Authentication required"
}
```

### 403 Forbidden
```json
{
  "error": "Insufficient privileges for admin resource"
}
```

### 404 Not Found
```json
{
  "error": "Entity not found",
  "entity_id": "entity-999"
}
```

### 500 Internal Server Error
```json
{
  "error": "Internal server error",
  "request_id": "req-123456"
}
```

## Rate Limiting
- 1000 requests per hour per user
- 100 requests per minute per IP
- Burst limit: 50 requests per 10 seconds

## Pagination
List endpoints support pagination:
```http
GET /users/cases?page=1&limit=20&offset=0
```

**Response:**
```json
{
  "data": [...],
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 150,
    "pages": 8
  }
}
```

## Filtering and Sorting
Most list endpoints support filtering and sorting:
```http
GET /users/cases?status=active&sort_by=created_at&sort_order=desc
```

## Webhooks
The system supports webhooks for real-time notifications:
```http
POST /webhooks/register
Authorization: Bearer <token>
Content-Type: application/json

{
  "url": "https://your-app.com/webhook",
  "events": ["case_created", "risk_alert", "compliance_violation"]
}
```

## SDKs and Libraries
- Python: `pip install zra-sdk`
- JavaScript: `npm install @zra/sdk`
- Java: Available in Maven Central

## Support
- API Documentation: http://localhost:8000/docs
- OpenAPI Spec: http://localhost:8000/openapi.json
- Support Email: support@zra.go.tz
- Issue Tracker: https://github.com/zra/issues


