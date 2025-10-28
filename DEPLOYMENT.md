# ZRA System Deployment Guide

## Overview
This guide provides step-by-step instructions for deploying the Zero-Trust Revenue Administration (ZRA) system.

## Prerequisites
- Docker and Docker Compose
- PostgreSQL 15+
- Redis 7+
- Python 3.11+ (for development)
- Node.js 18+ (for frontend development)

## Quick Start with Docker Compose

### 1. Clone the Repository
```bash
git clone <repository-url>
cd ZRA
```

### 2. Configure Environment Variables
```bash
cp env.example .env
# Edit .env with your configuration
```

### 3. Start the System
```bash
docker-compose up -d
```

### 4. Verify Deployment
```bash
# Check system health
curl http://localhost:8000/health

# Check API documentation
open http://localhost:8000/docs
```

## Manual Deployment

### 1. Database Setup
```bash
# Install PostgreSQL
sudo apt-get install postgresql postgresql-contrib

# Create database
sudo -u postgres createdb zra_db
sudo -u postgres createuser zra_user
sudo -u postgres psql -c "ALTER USER zra_user PASSWORD 'zra_password';"
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE zra_db TO zra_user;"

# Run initialization script
psql -U zra_user -d zra_db -f init.sql
```

### 2. Redis Setup
```bash
# Install Redis
sudo apt-get install redis-server

# Start Redis
sudo systemctl start redis-server
sudo systemctl enable redis-server
```

### 3. Application Setup
```bash
# Install Python dependencies
pip install -r requirements.txt

# Set environment variables
export DATABASE_URL="postgresql://zra_user:zra_password@localhost:5432/zra_db"
export REDIS_URL="redis://localhost:6379"
export SECRET_KEY="your-secret-key-here"

# Run database migrations
alembic upgrade head

# Start the application
python main.py
```

## Configuration

### Environment Variables
- `DATABASE_URL`: PostgreSQL connection string
- `REDIS_URL`: Redis connection string
- `SECRET_KEY`: JWT secret key
- `JWT_ALGORITHM`: JWT algorithm (default: HS256)
- `JWT_EXPIRE_MINUTES`: Token expiration time
- `ETHEREUM_RPC_URL`: Ethereum RPC endpoint for blockchain
- `PRIVATE_KEY`: Ethereum private key for blockchain
- `CONTRACT_ADDRESS`: Smart contract address

### Database Configuration
The system uses PostgreSQL with the following key tables:
- `users`: User accounts and authentication
- `entities`: Tax entities (businesses, individuals)
- `policies`: Regulatory policies
- `cases`: Investigation cases
- `audit_logs`: Immutable audit trail
- `risk_assessments`: AI/ML risk scores

### Security Configuration
- Zero-trust architecture with continuous authentication
- JWT-based access tokens
- Context-aware access control
- Immutable audit ledger with blockchain integration
- End-to-end encryption for sensitive data

## Monitoring and Observability

### Prometheus Metrics
The system exposes metrics at `/metrics`:
- `zra_requests_total`: Total API requests
- `zra_request_duration_seconds`: Request duration histogram
- `zra_compliance_score`: Current compliance score
- `zra_fraud_detections_total`: Total fraud detections
- `zra_audit_logs_total`: Total audit logs generated

### Grafana Dashboards
Access Grafana at http://localhost:3000 (admin/admin):
- System performance dashboard
- Compliance monitoring dashboard
- Fraud detection dashboard
- Security monitoring dashboard

### Logging
- Structured logging with JSON format
- Audit logs stored in PostgreSQL and blockchain
- Application logs in `/app/logs`

## API Documentation

### Authentication
```bash
# Login
curl -X POST "http://localhost:8000/api/v1/security/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "admin",
    "password": "admin123",
    "device_id": "device-001",
    "location": {"lat": -6.7924, "lng": 39.2083}
  }'
```

### Risk Assessment
```bash
# Assess risk
curl -X POST "http://localhost:8000/api/v1/ai/risk/assess" \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{
    "entity_id": "entity-001",
    "data_points": {"transaction_amount": 50000},
    "risk_types": ["fraud", "compliance"]
  }'
```

## Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Check PostgreSQL is running
   - Verify connection string in .env
   - Ensure database exists and user has permissions

2. **Redis Connection Failed**
   - Check Redis is running
   - Verify Redis URL in .env
   - Check Redis memory usage

3. **Blockchain Integration Issues**
   - Verify Ethereum RPC URL
   - Check private key and contract address
   - Ensure sufficient ETH for gas fees

4. **High Memory Usage**
   - Check Redis memory limits
   - Monitor database connection pool
   - Review application logs for memory leaks

### Logs
```bash
# Application logs
docker-compose logs zra-app

# Database logs
docker-compose logs postgres

# Redis logs
docker-compose logs redis
```

## Security Considerations

1. **Change Default Passwords**
   - Update database passwords
   - Change JWT secret key
   - Use strong passwords for all accounts

2. **Network Security**
   - Use HTTPS in production
   - Configure firewall rules
   - Implement rate limiting

3. **Data Protection**
   - Encrypt sensitive data at rest
   - Use secure communication protocols
   - Regular security audits

4. **Access Control**
   - Implement role-based access control
   - Regular access reviews
   - Monitor privileged access

## Performance Optimization

1. **Database Optimization**
   - Regular VACUUM and ANALYZE
   - Proper indexing
   - Connection pooling

2. **Caching Strategy**
   - Redis for session data
   - Application-level caching
   - CDN for static assets

3. **Monitoring**
   - Set up alerts for key metrics
   - Regular performance reviews
   - Capacity planning

## Backup and Recovery

1. **Database Backup**
   ```bash
   pg_dump -U zra_user zra_db > backup.sql
   ```

2. **Redis Backup**
   ```bash
   redis-cli BGSAVE
   cp /var/lib/redis/dump.rdb /backup/
   ```

3. **Application Backup**
   - Backup configuration files
   - Backup uploaded files
   - Backup logs

## Scaling

1. **Horizontal Scaling**
   - Multiple application instances
   - Load balancer configuration
   - Database read replicas

2. **Vertical Scaling**
   - Increase server resources
   - Optimize database configuration
   - Tune application parameters

## Support

For technical support:
- Check logs for error messages
- Review monitoring dashboards
- Contact system administrator
- Submit issue to repository


