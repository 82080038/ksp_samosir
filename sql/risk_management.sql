-- Risk Management Tables
-- KSP Samosir - Risk monitoring and compliance management

CREATE TABLE risk_alerts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type ENUM('transaction', 'customer', 'invoice', 'system', 'compliance') NOT NULL,
    reference_id INT,
    risk_type VARCHAR(100) NOT NULL, -- large_transaction, frequent_returns, overdue_payment, etc.
    description TEXT NOT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    entity_name VARCHAR(255),
    status ENUM('active', 'resolved', 'dismissed') DEFAULT 'active',
    resolved_at DATETIME,
    resolved_by INT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (resolved_by) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE compliance_checks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    check_type VARCHAR(100) NOT NULL,
    check_name VARCHAR(255) NOT NULL,
    status ENUM('compliant', 'warning', 'error') DEFAULT 'compliant',
    details JSON,
    last_checked TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    next_check DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes
CREATE INDEX idx_risk_alerts_type ON risk_alerts(type);
CREATE INDEX idx_risk_alerts_severity ON risk_alerts(severity);
CREATE INDEX idx_risk_alerts_status ON risk_alerts(status);
CREATE INDEX idx_risk_alerts_created_at ON risk_alerts(created_at);
CREATE INDEX idx_compliance_checks_type ON compliance_checks(check_type);
CREATE INDEX idx_compliance_checks_status ON compliance_checks(status);
