-- ZRA Database Initialization Script

-- Create extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- Create audit log function
CREATE OR REPLACE FUNCTION audit_trigger_function()
RETURNS TRIGGER AS $$
BEGIN
    INSERT INTO audit_logs (
        log_id,
        event_type,
        entity_id,
        operation,
        details,
        audit_hash,
        timestamp
    ) VALUES (
        'AUDIT-' || extract(epoch from now())::bigint,
        TG_TABLE_NAME,
        COALESCE(NEW.id::text, OLD.id::text),
        TG_OP,
        jsonb_build_object(
            'old', CASE WHEN TG_OP = 'DELETE' THEN to_jsonb(OLD) ELSE NULL END,
            'new', CASE WHEN TG_OP = 'INSERT' OR TG_OP = 'UPDATE' THEN to_jsonb(NEW) ELSE NULL END
        ),
        encode(digest(COALESCE(NEW::text, OLD::text), 'sha256'), 'hex'),
        now()
    );
    RETURN COALESCE(NEW, OLD);
END;
$$ LANGUAGE plpgsql;

-- Create audit triggers for critical tables
CREATE TRIGGER users_audit_trigger
    AFTER INSERT OR UPDATE OR DELETE ON users
    FOR EACH ROW EXECUTE FUNCTION audit_trigger_function();

CREATE TRIGGER entities_audit_trigger
    AFTER INSERT OR UPDATE OR DELETE ON entities
    FOR EACH ROW EXECUTE FUNCTION audit_trigger_function();

CREATE TRIGGER cases_audit_trigger
    AFTER INSERT OR UPDATE OR DELETE ON cases
    FOR EACH ROW EXECUTE FUNCTION audit_trigger_function();

-- Create indexes for performance
CREATE INDEX IF NOT EXISTS idx_audit_logs_timestamp ON audit_logs(timestamp);
CREATE INDEX IF NOT EXISTS idx_audit_logs_entity_id ON audit_logs(entity_id);
CREATE INDEX IF NOT EXISTS idx_audit_logs_event_type ON audit_logs(event_type);

CREATE INDEX IF NOT EXISTS idx_entities_tin ON entities(tin);
CREATE INDEX IF NOT EXISTS idx_entities_status ON entities(status);
CREATE INDEX IF NOT EXISTS idx_entities_compliance_score ON entities(compliance_score);

CREATE INDEX IF NOT EXISTS idx_cases_status ON cases(status);
CREATE INDEX IF NOT EXISTS idx_cases_priority ON cases(priority);
CREATE INDEX IF NOT EXISTS idx_cases_entity_id ON cases(entity_id);

CREATE INDEX IF NOT EXISTS idx_risk_assessments_entity_id ON risk_assessments(entity_id);
CREATE INDEX IF NOT EXISTS idx_risk_assessments_risk_type ON risk_assessments(risk_type);
CREATE INDEX IF NOT EXISTS idx_risk_assessments_created_at ON risk_assessments(created_at);

-- Create views for common queries
CREATE OR REPLACE VIEW entity_compliance_summary AS
SELECT 
    e.entity_id,
    e.name,
    e.type,
    e.compliance_score,
    e.risk_score,
    COUNT(c.id) as total_cases,
    COUNT(CASE WHEN c.status = 'active' THEN 1 END) as active_cases,
    COUNT(o.id) as total_obligations,
    COUNT(CASE WHEN o.status = 'pending' THEN 1 END) as pending_obligations
FROM entities e
LEFT JOIN cases c ON e.id = c.entity_id
LEFT JOIN obligations o ON e.id = o.entity_id
GROUP BY e.id, e.entity_id, e.name, e.type, e.compliance_score, e.risk_score;

CREATE OR REPLACE VIEW risk_summary AS
SELECT 
    ra.risk_type,
    COUNT(*) as total_assessments,
    AVG(ra.score) as average_score,
    COUNT(CASE WHEN ra.risk_level = 'high' THEN 1 END) as high_risk_count,
    COUNT(CASE WHEN ra.risk_level = 'critical' THEN 1 END) as critical_risk_count
FROM risk_assessments ra
WHERE ra.created_at >= CURRENT_DATE - INTERVAL '30 days'
GROUP BY ra.risk_type;

-- Insert sample data
INSERT INTO users (username, email, hashed_password, full_name, role) VALUES
('admin', 'admin@zra.go.tz', '$2b$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewdBPj4J/4.5.6.7', 'System Administrator', 'admin'),
('officer1', 'officer1@zra.go.tz', '$2b$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewdBPj4J/4.5.6.7', 'John Officer', 'officer'),
('officer2', 'officer2@zra.go.tz', '$2b$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewdBPj4J/4.5.6.7', 'Jane Officer', 'officer');

INSERT INTO entities (entity_id, name, type, tin, compliance_score, risk_score) VALUES
('entity-001', 'ABC Trading Company', 'business', '123456789', 0.85, 0.25),
('entity-002', 'XYZ Manufacturing Ltd', 'business', '987654321', 0.92, 0.15),
('entity-003', 'John Doe', 'individual', '111222333', 0.78, 0.35);

INSERT INTO policies (policy_id, title, category, content, compliance_requirements, effective_date) VALUES
('POL-001', 'Tax Compliance Policy', 'tax', 'Comprehensive tax compliance requirements...', '["filing", "payment", "reporting"]', '2024-01-01'),
('POL-002', 'Customs Regulation Policy', 'customs', 'Customs and trade compliance requirements...', '["declaration", "duty_payment", "documentation"]', '2024-01-01');

INSERT INTO data_sources (source_id, name, type, endpoint, status) VALUES
('EFD-001', 'Electronic Fiscal Device System', 'EFD', 'https://efd.tra.go.tz/api', 'active'),
('CUSTOMS-001', 'Customs Data System', 'Customs', 'https://customs.tra.go.tz/api', 'active'),
('BANKING-001', 'Banking Integration System', 'Banking', 'https://banking.tra.go.tz/api', 'active');


