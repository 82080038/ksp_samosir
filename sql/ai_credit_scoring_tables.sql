-- AI/ML Credit Scoring Tables for KSP Samosir
-- Based on fintech trends 2024 - AI adoption in cooperative lending

-- AI Credit Scores Table
CREATE TABLE IF NOT EXISTS ai_credit_scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    loan_id INT NOT NULL,
    total_score DECIMAL(5,2) NOT NULL, -- 300-1000 range
    grade ENUM('A', 'B', 'C', 'D', 'E') NOT NULL,
    risk_level ENUM('Low Risk', 'Medium Risk', 'High Risk', 'Very High Risk') NOT NULL,
    recommendation TEXT NOT NULL,
    factors_data JSON, -- Store detailed scoring factors
    confidence DECIMAL(5,2) DEFAULT 85.00, -- ML model confidence score
    model_version VARCHAR(20) DEFAULT '1.0',
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (loan_id) REFERENCES pinjaman(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_loan (loan_id),
    INDEX idx_score (total_score),
    INDEX idx_grade (grade),
    INDEX idx_risk (risk_level),
    INDEX idx_created (created_at)
);

-- AI Model Training Data
CREATE TABLE IF NOT EXISTS ai_training_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    loan_id INT NOT NULL,
    member_id INT NOT NULL,
    actual_outcome ENUM('good', 'bad') NOT NULL, -- Based on loan repayment
    predicted_score DECIMAL(5,2),
    model_accuracy DECIMAL(5,2),
    training_date DATE NOT NULL,
    model_version VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (loan_id) REFERENCES pinjaman(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES anggota(id) ON DELETE CASCADE,

    INDEX idx_loan (loan_id),
    INDEX idx_member (member_id),
    INDEX idx_outcome (actual_outcome),
    INDEX idx_training_date (training_date)
);

-- AI Fraud Detection Logs
CREATE TABLE IF NOT EXISTS ai_fraud_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NULL,
    member_id INT NULL,
    alert_type ENUM('suspicious_pattern', 'unusual_amount', 'location_anomaly', 'behavior_change') NOT NULL,
    risk_score DECIMAL(5,2) NOT NULL,
    alert_details JSON,
    status ENUM('active', 'investigated', 'false_positive') DEFAULT 'active',
    investigated_by INT NULL,
    investigated_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (transaction_id) REFERENCES pinjaman(id) ON DELETE SET NULL,
    FOREIGN KEY (member_id) REFERENCES anggota(id) ON DELETE SET NULL,
    FOREIGN KEY (investigated_by) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_member (member_id),
    INDEX idx_type (alert_type),
    INDEX idx_status (status),
    INDEX idx_risk (risk_score)
);

-- AI Model Performance Metrics
CREATE TABLE IF NOT EXISTS ai_model_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    model_version VARCHAR(20) NOT NULL,
    metric_type ENUM('accuracy', 'precision', 'recall', 'f1_score', 'auc') NOT NULL,
    metric_value DECIMAL(7,4) NOT NULL,
    training_samples INT NOT NULL,
    test_samples INT NOT NULL,
    evaluated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_version (model_version),
    INDEX idx_type (metric_type),
    INDEX idx_evaluated (evaluated_at)
);

-- AI Recommendations History
CREATE TABLE IF NOT EXISTS ai_recommendations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entity_type ENUM('loan', 'member', 'transaction') NOT NULL,
    entity_id INT NOT NULL,
    recommendation_type ENUM('approve', 'reject', 'review', 'reduce_amount', 'extend_tenor') NOT NULL,
    confidence_score DECIMAL(5,2) NOT NULL,
    reasoning TEXT,
    action_taken ENUM('accepted', 'rejected', 'modified', 'pending') DEFAULT 'pending',
    action_taken_by INT NULL,
    action_taken_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (action_taken_by) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_type (recommendation_type),
    INDEX idx_action (action_taken),
    INDEX idx_created (created_at)
);

-- Insert sample AI model metrics
INSERT INTO ai_model_metrics (model_version, metric_type, metric_value, training_samples, test_samples) VALUES
('1.0', 'accuracy', 85.30, 1000, 200),
('1.0', 'precision', 82.10, 1000, 200),
('1.0', 'recall', 88.70, 1000, 200),
('1.0', 'f1_score', 85.20, 1000, 200),
('1.0', 'auc', 0.867, 1000, 200);

-- Create sample credit scores for existing loans
INSERT INTO ai_credit_scores (loan_id, total_score, grade, risk_level, recommendation, factors_data, confidence, model_version)
SELECT
    p.id as loan_id,
    CASE
        WHEN RAND() > 0.8 THEN 850 + (RAND() * 150)  -- High score
        WHEN RAND() > 0.6 THEN 650 + (RAND() * 150)  -- Medium score
        WHEN RAND() > 0.3 THEN 450 + (RAND() * 150)  -- Low score
        ELSE 300 + (RAND() * 150)  -- Very low score
    END as total_score,
    CASE
        WHEN RAND() > 0.7 THEN 'A'
        WHEN RAND() > 0.5 THEN 'B'
        WHEN RAND() > 0.3 THEN 'C'
        WHEN RAND() > 0.1 THEN 'D'
        ELSE 'E'
    END as grade,
    CASE
        WHEN RAND() > 0.7 THEN 'Low Risk'
        WHEN RAND() > 0.5 THEN 'Medium Risk'
        WHEN RAND() > 0.2 THEN 'High Risk'
        ELSE 'Very High Risk'
    END as risk_level,
    CASE
        WHEN RAND() > 0.6 THEN 'APPROVE: Good credit profile'
        WHEN RAND() > 0.3 THEN 'REVIEW: Additional documents required'
        ELSE 'REJECT: High risk profile'
    END as recommendation,
    JSON_OBJECT(
        'age', JSON_OBJECT('score', 80 + (RAND() * 40), 'description', 'Age factor assessment'),
        'income', JSON_OBJECT('score', 70 + (RAND() * 50), 'description', 'Income stability assessment'),
        'history', JSON_OBJECT('score', 60 + (RAND() * 60), 'description', 'Loan history assessment')
    ) as factors_data,
    80 + (RAND() * 15) as confidence,
    '1.0' as model_version
FROM pinjaman p
WHERE NOT EXISTS (SELECT 1 FROM ai_credit_scores acs WHERE acs.loan_id = p.id)
LIMIT 10;
