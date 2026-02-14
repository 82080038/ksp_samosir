-- AI-Powered Credit Scoring System for KSP Samosir

-- Credit scoring model tables
CREATE TABLE IF NOT EXISTS credit_scoring_models (
    id INT PRIMARY KEY AUTO_INCREMENT,
    model_name VARCHAR(100) NOT NULL,
    model_version VARCHAR(20) NOT NULL,
    model_type ENUM('traditional', 'machine_learning', 'hybrid') DEFAULT 'traditional',
    accuracy_score DECIMAL(5,4) DEFAULT 0,
    features_used JSON,
    training_data_size INT DEFAULT 0,
    last_trained DATETIME,
    status ENUM('active', 'inactive', 'training') DEFAULT 'inactive',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_version (model_version)
);

CREATE TABLE IF NOT EXISTS credit_scores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    loan_application_id INT,
    credit_score DECIMAL(5,2) NOT NULL,
    score_range ENUM('poor', 'fair', 'good', 'excellent') NOT NULL,
    risk_level ENUM('low', 'medium', 'high', 'very_high') NOT NULL,
    confidence_score DECIMAL(5,4) DEFAULT 0,
    factors JSON,
    model_used VARCHAR(100),
    calculated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    INDEX idx_member (member_id),
    INDEX idx_score (credit_score),
    INDEX idx_risk_level (risk_level),
    INDEX idx_calculated_at (calculated_at)
);

CREATE TABLE IF NOT EXISTS credit_score_factors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    factor_name VARCHAR(100) NOT NULL,
    factor_category VARCHAR(50) NOT NULL,
    weight DECIMAL(5,4) DEFAULT 0,
    description TEXT,
    data_source VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category (factor_category),
    INDEX idx_active (is_active)
);

-- Automated collections system
CREATE TABLE IF NOT EXISTS collection_automation (
    id INT PRIMARY KEY AUTO_INCREMENT,
    loan_id INT NOT NULL,
    member_id INT NOT NULL,
    collection_strategy VARCHAR(50) NOT NULL,
    overdue_days INT NOT NULL,
    amount_overdue DECIMAL(15,2) NOT NULL,
    last_payment_date DATE,
    next_action_date DATE,
    action_taken VARCHAR(100),
    communication_log JSON,
    status ENUM('active', 'paused', 'completed', 'escalated') DEFAULT 'active',
    priority_level ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    assigned_agent_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_loan (loan_id),
    INDEX idx_member (member_id),
    INDEX idx_status (status),
    INDEX idx_priority (priority_level),
    INDEX idx_next_action (next_action_date)
);

CREATE TABLE IF NOT EXISTS collection_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    template_name VARCHAR(100) NOT NULL,
    template_type ENUM('email', 'sms', 'letter', 'call_script') NOT NULL,
    overdue_days_min INT DEFAULT 0,
    overdue_days_max INT DEFAULT 999,
    priority_level ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    subject VARCHAR(200),
    content TEXT NOT NULL,
    variables JSON,
    is_active BOOLEAN DEFAULT TRUE,
    success_rate DECIMAL(5,2) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (template_type),
    INDEX idx_active (is_active),
    INDEX idx_overdue_range (overdue_days_min, overdue_days_max)
);

CREATE TABLE IF NOT EXISTS collection_actions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    collection_id INT NOT NULL,
    action_type ENUM('email', 'sms', 'call', 'letter', 'payment_plan', 'legal_action') NOT NULL,
    action_status ENUM('scheduled', 'sent', 'delivered', 'responded', 'failed') DEFAULT 'scheduled',
    scheduled_date DATETIME,
    executed_date DATETIME,
    response_received BOOLEAN DEFAULT FALSE,
    response_details TEXT,
    cost DECIMAL(10,2) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_collection (collection_id),
    INDEX idx_type (action_type),
    INDEX idx_status (action_status),
    INDEX idx_scheduled (scheduled_date)
);

-- Predictive analytics tables
CREATE TABLE IF NOT EXISTS predictive_models (
    id INT PRIMARY KEY AUTO_INCREMENT,
    model_name VARCHAR(100) NOT NULL,
    prediction_type ENUM('loan_default', 'member_churn', 'savings_growth', 'market_trends') NOT NULL,
    algorithm_used VARCHAR(50) NOT NULL,
    accuracy_score DECIMAL(5,4) DEFAULT 0,
    features_used JSON,
    training_period_start DATE,
    training_period_end DATE,
    last_retrained DATETIME,
    model_file_path VARCHAR(500),
    status ENUM('active', 'inactive', 'training') DEFAULT 'inactive',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (prediction_type),
    INDEX idx_status (status)
);

CREATE TABLE IF NOT EXISTS predictions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    model_id INT NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT NOT NULL,
    prediction_type VARCHAR(50) NOT NULL,
    predicted_value DECIMAL(10,2),
    confidence_score DECIMAL(5,4) DEFAULT 0,
    prediction_factors JSON,
    time_horizon VARCHAR(20) DEFAULT '3_months',
    predicted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    INDEX idx_model (model_id),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_type (prediction_type),
    INDEX idx_predicted_at (predicted_at)
);

CREATE TABLE IF NOT EXISTS prediction_accuracy (
    id INT PRIMARY KEY AUTO_INCREMENT,
    model_id INT NOT NULL,
    prediction_id INT NOT NULL,
    actual_value DECIMAL(10,2),
    predicted_value DECIMAL(10,2),
    accuracy_score DECIMAL(5,4),
    measured_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_model (model_id),
    INDEX idx_prediction (prediction_id),
    INDEX idx_accuracy (accuracy_score)
);

-- RPA (Robotic Process Automation) tables
CREATE TABLE IF NOT EXISTS rpa_processes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    process_name VARCHAR(100) NOT NULL,
    process_category VARCHAR(50) NOT NULL,
    description TEXT,
    trigger_condition JSON,
    steps JSON,
    estimated_savings DECIMAL(15,2) DEFAULT 0,
    execution_time_seconds INT DEFAULT 0,
    success_rate DECIMAL(5,2) DEFAULT 0,
    status ENUM('active', 'inactive', 'testing') DEFAULT 'inactive',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category (process_category),
    INDEX idx_status (status)
);

CREATE TABLE IF NOT EXISTS rpa_executions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    process_id INT NOT NULL,
    execution_status ENUM('running', 'completed', 'failed', 'cancelled') DEFAULT 'running',
    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME,
    execution_time_seconds INT DEFAULT 0,
    records_processed INT DEFAULT 0,
    errors_encountered INT DEFAULT 0,
    error_details TEXT,
    savings_achieved DECIMAL(15,2) DEFAULT 0,
    INDEX idx_process (process_id),
    INDEX idx_status (execution_status),
    INDEX idx_started (started_at)
);

-- Default credit scoring factors
INSERT IGNORE INTO credit_score_factors (factor_name, factor_category, weight, description, data_source) VALUES
('payment_history', 'credit_history', 0.35, 'History of on-time payments', 'loan_payment_history'),
('credit_utilization', 'credit_usage', 0.20, 'Ratio of credit used to credit available', 'current_loans'),
('credit_age', 'credit_history', 0.15, 'Length of credit history', 'member_join_date'),
('income_stability', 'income', 0.10, 'Consistency of income', 'savings_deposits'),
('employment_stability', 'employment', 0.10, 'Length and stability of employment', 'employment_records'),
('debt_to_income_ratio', 'debt', 0.10, 'Total debt relative to income', 'current_obligations');

-- Default collection templates
INSERT IGNORE INTO collection_templates (template_name, template_type, overdue_days_min, overdue_days_max, priority_level, subject, content) VALUES
('Friendly Reminder', 'email', 1, 7, 'low', 'Friendly Reminder: Your KSP Samosir Loan Payment',
 'Dear {member_name},\n\nWe hope this email finds you well. We noticed your loan payment of Rp {amount_due} is due on {due_date}.\n\nPlease make your payment to avoid any late fees.\n\nBest regards,\nKSP Samosir Team'),

('Payment Overdue Notice', 'email', 8, 30, 'medium', 'Important: Your Loan Payment is Overdue',
 'Dear {member_name},\n\nYour loan payment of Rp {amount_due} was due on {due_date} and is now {days_overdue} days overdue.\n\nPlease contact us immediately to discuss payment arrangements.\n\nBest regards,\nKSP Samosir Team'),

('Final Notice', 'email', 31, 60, 'high', 'FINAL NOTICE: Immediate Action Required',
 'Dear {member_name},\n\nThis is your final notice regarding the overdue loan payment of Rp {amount_due}.\n\nFailure to make payment within 7 days may result in legal action.\n\nContact us immediately.\n\nBest regards,\nKSP Samosir Team');

-- Default predictive models
INSERT IGNORE INTO predictive_models (model_name, prediction_type, algorithm_used, status) VALUES
('Loan Default Predictor', 'loan_default', 'logistic_regression', 'active'),
('Member Churn Predictor', 'member_churn', 'random_forest', 'active'),
('Savings Growth Predictor', 'savings_growth', 'linear_regression', 'active');
