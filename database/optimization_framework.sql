-- Optimization Framework Database Schema for KSP Samosir

-- A/B Testing Framework
CREATE TABLE IF NOT EXISTS ab_tests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    test_name VARCHAR(200) NOT NULL,
    test_description TEXT,
    test_type ENUM('ui_ux', 'pricing', 'features', 'messaging', 'process') DEFAULT 'ui_ux',
    variant_a_description TEXT NOT NULL,
    variant_b_description TEXT NOT NULL,
    target_metric VARCHAR(100) NOT NULL,
    minimum_sample_size INT DEFAULT 1000,
    confidence_threshold DECIMAL(5,4) DEFAULT 0.95,
    start_date DATETIME,
    end_date DATETIME,
    status ENUM('draft', 'running', 'completed', 'cancelled') DEFAULT 'draft',
    winner_variant CHAR(1),
    improvement_percentage DECIMAL(5,2),
    statistical_significance DECIMAL(5,4),
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (test_type),
    INDEX idx_status (status),
    INDEX idx_dates (start_date, end_date)
);

CREATE TABLE IF NOT EXISTS ab_test_participants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    test_id INT NOT NULL,
    user_id INT NOT NULL,
    assigned_variant CHAR(1) NOT NULL,
    participated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    converted BOOLEAN DEFAULT FALSE,
    conversion_value DECIMAL(10,2) DEFAULT 0,
    session_duration INT,
    page_views INT,
    UNIQUE KEY unique_participation (test_id, user_id),
    FOREIGN KEY (test_id) REFERENCES ab_tests(id),
    INDEX idx_test (test_id),
    INDEX idx_user (user_id),
    INDEX idx_variant (assigned_variant)
);

CREATE TABLE IF NOT EXISTS ab_test_results (
    id INT PRIMARY KEY AUTO_INCREMENT,
    test_id INT NOT NULL,
    variant CHAR(1) NOT NULL,
    participants INT DEFAULT 0,
    conversions INT DEFAULT 0,
    conversion_rate DECIMAL(5,2) DEFAULT 0,
    average_order_value DECIMAL(10,2) DEFAULT 0,
    statistical_significance DECIMAL(5,4),
    confidence_interval_start DECIMAL(5,2),
    confidence_interval_end DECIMAL(5,2),
    measured_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (test_id) REFERENCES ab_tests(id),
    INDEX idx_test_variant (test_id, variant)
);

-- Continuous Improvement Framework
CREATE TABLE IF NOT EXISTS improvement_initiatives (
    id INT PRIMARY KEY AUTO_INCREMENT,
    initiative_name VARCHAR(200) NOT NULL,
    initiative_description TEXT,
    category ENUM('process', 'technology', 'customer_experience', 'operations', 'compliance') NOT NULL,
    priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    status ENUM('identified', 'planned', 'in_progress', 'completed', 'cancelled') DEFAULT 'identified',
    expected_benefits TEXT,
    estimated_cost DECIMAL(15,2),
    estimated_effort_days INT,
    actual_cost DECIMAL(15,2),
    actual_effort_days INT,
    success_metrics TEXT,
    start_date DATE,
    target_completion_date DATE,
    actual_completion_date DATE,
    assigned_to INT,
    approved_by INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_priority (priority),
    INDEX idx_status (status),
    INDEX idx_assigned (assigned_to)
);

CREATE TABLE IF NOT EXISTS improvement_measurements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    initiative_id INT NOT NULL,
    metric_name VARCHAR(100) NOT NULL,
    baseline_value DECIMAL(10,2),
    target_value DECIMAL(10,2),
    current_value DECIMAL(10,2),
    measurement_date DATE NOT NULL,
    measured_by INT,
    notes TEXT,
    FOREIGN KEY (initiative_id) REFERENCES improvement_initiatives(id),
    INDEX idx_initiative (initiative_id),
    INDEX idx_metric (metric_name),
    INDEX idx_date (measurement_date)
);

-- Automated Optimization Rules
CREATE TABLE IF NOT EXISTS optimization_rules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    rule_name VARCHAR(200) NOT NULL,
    rule_description TEXT,
    trigger_condition JSON NOT NULL,
    optimization_action JSON NOT NULL,
    priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    execution_frequency ENUM('realtime', 'hourly', 'daily', 'weekly', 'monthly') DEFAULT 'daily',
    last_executed DATETIME,
    execution_count INT DEFAULT 0,
    success_count INT DEFAULT 0,
    failure_count INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_active (is_active),
    INDEX idx_priority (priority),
    INDEX idx_frequency (execution_frequency),
    INDEX idx_last_executed (last_executed)
);

CREATE TABLE IF NOT EXISTS optimization_executions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    rule_id INT NOT NULL,
    execution_status ENUM('success', 'failed', 'partial') DEFAULT 'success',
    triggered_by VARCHAR(100),
    execution_time DECIMAL(5,2),
    impact_metrics JSON,
    error_message TEXT,
    executed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rule_id) REFERENCES optimization_rules(id),
    INDEX idx_rule (rule_id),
    INDEX idx_status (execution_status),
    INDEX idx_executed (executed_at)
);

-- User Experience Optimization
CREATE TABLE IF NOT EXISTS ux_optimization_experiments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    experiment_name VARCHAR(200) NOT NULL,
    page_url VARCHAR(500) NOT NULL,
    element_selector VARCHAR(500) NOT NULL,
    original_design JSON,
    optimized_design JSON,
    target_metric VARCHAR(100) NOT NULL,
    traffic_percentage DECIMAL(5,2) DEFAULT 50.00,
    start_date DATETIME,
    end_date DATETIME,
    status ENUM('draft', 'running', 'completed', 'cancelled') DEFAULT 'draft',
    winner_design VARCHAR(10),
    improvement_score DECIMAL(5,2),
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_page (page_url),
    INDEX idx_status (status),
    INDEX idx_dates (start_date, end_date)
);

CREATE TABLE IF NOT EXISTS ux_experiment_results (
    id INT PRIMARY KEY AUTO_INCREMENT,
    experiment_id INT NOT NULL,
    design_variant VARCHAR(10) NOT NULL,
    unique_visitors INT DEFAULT 0,
    total_interactions INT DEFAULT 0,
    conversion_events INT DEFAULT 0,
    average_session_duration DECIMAL(5,2),
    bounce_rate DECIMAL(5,2),
    measured_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (experiment_id) REFERENCES ux_optimization_experiments(id),
    INDEX idx_experiment (experiment_id),
    INDEX idx_variant (design_variant)
);

-- Performance Optimization Tracking
CREATE TABLE IF NOT EXISTS performance_optimization_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    optimization_type VARCHAR(100) NOT NULL,
    target_component VARCHAR(200) NOT NULL,
    baseline_metric DECIMAL(10,2),
    optimized_metric DECIMAL(10,2),
    improvement_percentage DECIMAL(5,2),
    implementation_cost DECIMAL(10,2),
    implementation_time_hours DECIMAL(5,2),
    long_term_benefits TEXT,
    status ENUM('proposed', 'implemented', 'measured', 'reverted') DEFAULT 'proposed',
    implemented_by INT,
    implemented_at DATETIME,
    measured_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (optimization_type),
    INDEX idx_status (status),
    INDEX idx_implemented (implemented_at)
);

-- Automated Alert System for Optimization
CREATE TABLE IF NOT EXISTS optimization_alerts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    alert_type VARCHAR(100) NOT NULL,
    alert_title VARCHAR(200) NOT NULL,
    alert_description TEXT,
    severity ENUM('info', 'warning', 'critical') DEFAULT 'info',
    affected_component VARCHAR(200),
    current_value DECIMAL(10,2),
    threshold_value DECIMAL(10,2),
    suggested_action TEXT,
    auto_resolve BOOLEAN DEFAULT FALSE,
    resolved BOOLEAN DEFAULT FALSE,
    resolved_by INT,
    resolved_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (alert_type),
    INDEX idx_severity (severity),
    INDEX idx_resolved (resolved),
    INDEX idx_created (created_at)
);

-- Learning and Adaptation System
CREATE TABLE IF NOT EXISTS system_learning_patterns (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pattern_type VARCHAR(100) NOT NULL,
    pattern_description TEXT,
    trigger_conditions JSON,
    learned_response JSON,
    success_rate DECIMAL(5,2) DEFAULT 0,
    usage_count INT DEFAULT 0,
    last_used DATETIME,
    confidence_score DECIMAL(5,4) DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_type (pattern_type),
    INDEX idx_active (is_active),
    INDEX idx_confidence (confidence_score)
);

-- Predictive Optimization
CREATE TABLE IF NOT EXISTS optimization_predictions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    prediction_type VARCHAR(100) NOT NULL,
    target_component VARCHAR(200) NOT NULL,
    prediction_basis TEXT,
    predicted_issue TEXT,
    confidence_level DECIMAL(5,2),
    time_to_impact VARCHAR(50),
    recommended_action TEXT,
    preventive_measures TEXT,
    status ENUM('predicted', 'prevented', 'occurred', 'dismissed') DEFAULT 'predicted',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    occurred_at DATETIME,
    INDEX idx_type (prediction_type),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
);
