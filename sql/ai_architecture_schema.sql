-- AI-First Architecture Database Schema for KSP Samosir

-- Conversational AI tables
CREATE TABLE IF NOT EXISTS ai_conversations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(255) NOT NULL,
    user_id INT,
    conversation_type ENUM('support', 'guidance', 'transaction', 'general') DEFAULT 'general',
    status ENUM('active', 'completed', 'transferred') DEFAULT 'active',
    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_message_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    resolved BOOLEAN DEFAULT FALSE,
    satisfaction_rating DECIMAL(2,1),
    transferred_to VARCHAR(100),
    INDEX idx_session (session_id),
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_started_at (started_at)
);

CREATE TABLE IF NOT EXISTS ai_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    conversation_id INT NOT NULL,
    sender ENUM('user', 'ai', 'agent') DEFAULT 'user',
    message_type ENUM('text', 'option', 'form', 'file') DEFAULT 'text',
    content TEXT NOT NULL,
    metadata JSON,
    confidence_score DECIMAL(5,4),
    intent_detected VARCHAR(100),
    entities_detected JSON,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES ai_conversations(id),
    INDEX idx_conversation (conversation_id),
    INDEX idx_sender (sender),
    INDEX idx_created_at (created_at)
);

CREATE TABLE IF NOT EXISTS ai_training_data (
    id INT PRIMARY KEY AUTO_INCREMENT,
    intent VARCHAR(100) NOT NULL,
    utterance TEXT NOT NULL,
    response TEXT NOT NULL,
    context VARCHAR(100),
    confidence_threshold DECIMAL(5,4) DEFAULT 0.8,
    usage_count INT DEFAULT 0,
    success_rate DECIMAL(5,2) DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_intent (intent),
    INDEX idx_active (is_active)
);

-- Automated Decision Making tables
CREATE TABLE IF NOT EXISTS ai_decisions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    decision_type VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT NOT NULL,
    input_data JSON NOT NULL,
    decision_made VARCHAR(100) NOT NULL,
    confidence_score DECIMAL(5,4) DEFAULT 0,
    reasoning TEXT,
    approved BOOLEAN DEFAULT TRUE,
    approved_by INT,
    approved_at DATETIME,
    executed BOOLEAN DEFAULT FALSE,
    executed_at DATETIME,
    outcome VARCHAR(50),
    feedback TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (decision_type),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_confidence (confidence_score),
    INDEX idx_created_at (created_at)
);

CREATE TABLE IF NOT EXISTS decision_rules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    rule_name VARCHAR(200) NOT NULL,
    rule_category VARCHAR(50) NOT NULL,
    conditions JSON NOT NULL,
    actions JSON NOT NULL,
    priority INT DEFAULT 1,
    confidence_threshold DECIMAL(5,4) DEFAULT 0.8,
    requires_approval BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    usage_count INT DEFAULT 0,
    success_rate DECIMAL(5,2) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category (rule_category),
    INDEX idx_active (is_active)
);

-- Personalized Services tables
CREATE TABLE IF NOT EXISTS user_profiles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    profile_data JSON,
    preferences JSON,
    behavior_patterns JSON,
    risk_profile VARCHAR(50),
    engagement_score DECIMAL(5,2) DEFAULT 0,
    last_updated DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_risk_profile (risk_profile),
    INDEX idx_engagement (engagement_score)
);

CREATE TABLE IF NOT EXISTS personalization_rules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    rule_name VARCHAR(200) NOT NULL,
    trigger_conditions JSON NOT NULL,
    personalization_actions JSON NOT NULL,
    target_segment VARCHAR(100),
    priority INT DEFAULT 1,
    effectiveness_score DECIMAL(5,2) DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_segment (target_segment),
    INDEX idx_active (is_active)
);

CREATE TABLE IF NOT EXISTS recommendation_engine (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    item_type VARCHAR(50) NOT NULL,
    item_id INT NOT NULL,
    recommendation_score DECIMAL(5,4) NOT NULL,
    recommendation_reason VARCHAR(200),
    algorithm_used VARCHAR(50),
    shown BOOLEAN DEFAULT FALSE,
    clicked BOOLEAN DEFAULT FALSE,
    purchased BOOLEAN DEFAULT FALSE,
    feedback_rating DECIMAL(2,1),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_item (item_type, item_id),
    INDEX idx_score (recommendation_score),
    INDEX idx_created_at (created_at)
);

-- Advanced Fraud Prevention tables
CREATE TABLE IF NOT EXISTS ai_fraud_models (
    id INT PRIMARY KEY AUTO_INCREMENT,
    model_name VARCHAR(100) NOT NULL,
    model_type VARCHAR(50) NOT NULL,
    training_data_size INT DEFAULT 0,
    accuracy_score DECIMAL(5,4) DEFAULT 0,
    false_positive_rate DECIMAL(5,4) DEFAULT 0,
    false_negative_rate DECIMAL(5,4) DEFAULT 0,
    last_trained DATETIME,
    status ENUM('active', 'training', 'inactive') DEFAULT 'inactive',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (model_type),
    INDEX idx_status (status)
);

CREATE TABLE IF NOT EXISTS fraud_alerts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    alert_type VARCHAR(50) NOT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    transaction_id INT,
    user_id INT,
    risk_score DECIMAL(5,4) NOT NULL,
    risk_factors JSON,
    ai_prediction BOOLEAN DEFAULT FALSE,
    investigated BOOLEAN DEFAULT FALSE,
    investigation_result VARCHAR(50),
    investigator_id INT,
    investigated_at DATETIME,
    action_taken VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (alert_type),
    INDEX idx_severity (severity),
    INDEX idx_user (user_id),
    INDEX idx_transaction (transaction_id),
    INDEX idx_investigated (investigated),
    INDEX idx_created_at (created_at)
);

CREATE TABLE IF NOT EXISTS fraud_patterns (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pattern_name VARCHAR(100) NOT NULL,
    pattern_type VARCHAR(50) NOT NULL,
    detection_rules JSON NOT NULL,
    risk_weight DECIMAL(3,2) DEFAULT 1.0,
    false_positive_rate DECIMAL(5,4) DEFAULT 0,
    detection_count INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (pattern_type),
    INDEX idx_active (is_active)
);

-- AI Model Performance Tracking
CREATE TABLE IF NOT EXISTS ai_model_performance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    model_name VARCHAR(100) NOT NULL,
    metric_type VARCHAR(50) NOT NULL,
    metric_value DECIMAL(10,4) NOT NULL,
    test_period_start DATE,
    test_period_end DATE,
    improvement_percentage DECIMAL(5,2),
    recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_model (model_name),
    INDEX idx_metric (metric_type),
    INDEX idx_recorded (recorded_at)
);

-- User Behavior Analytics for AI Training
CREATE TABLE IF NOT EXISTS user_behavior_analytics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    behavior_type VARCHAR(50) NOT NULL,
    behavior_data JSON,
    session_context JSON,
    device_info JSON,
    location_data JSON,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_behavior (behavior_type),
    INDEX idx_timestamp (timestamp)
);

-- AI Ethics and Bias Monitoring
CREATE TABLE IF NOT EXISTS ai_ethics_monitoring (
    id INT PRIMARY KEY AUTO_INCREMENT,
    model_name VARCHAR(100) NOT NULL,
    bias_check_type VARCHAR(50) NOT NULL,
    bias_score DECIMAL(5,4) DEFAULT 0,
    affected_groups JSON,
    mitigation_actions TEXT,
    compliance_status ENUM('compliant', 'review_required', 'non_compliant') DEFAULT 'compliant',
    checked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_model (model_name),
    INDEX idx_type (bias_check_type),
    INDEX idx_status (compliance_status),
    INDEX idx_checked (checked_at)
);
