-- Advanced Monitoring & Analytics Database Schema for KSP Samosir

-- System monitoring tables
CREATE TABLE IF NOT EXISTS monitoring_metrics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category VARCHAR(50) NOT NULL,
    metric_name VARCHAR(100) NOT NULL,
    metric_value DECIMAL(10,2) DEFAULT 0,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_metric (category, metric_name, timestamp)
);

CREATE TABLE IF NOT EXISTS system_metrics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    metric_type VARCHAR(50) NOT NULL,
    metric_value DECIMAL(10,2) DEFAULT 0,
    duration DECIMAL(10,2) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type_time (metric_type, created_at)
);

CREATE TABLE IF NOT EXISTS system_alerts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    alert_type VARCHAR(50) NOT NULL,
    alert_level ENUM('info', 'warning', 'critical') DEFAULT 'info',
    message TEXT NOT NULL,
    resolved BOOLEAN DEFAULT FALSE,
    resolved_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type_level (alert_type, alert_level),
    INDEX idx_resolved (resolved)
);

CREATE TABLE IF NOT EXISTS system_uptime (
    id INT PRIMARY KEY AUTO_INCREMENT,
    date DATE NOT NULL,
    uptime_percentage DECIMAL(5,2) DEFAULT 100.00,
    downtime_minutes INT DEFAULT 0,
    incidents_count INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_date (date)
);

-- Business metrics tables
CREATE TABLE IF NOT EXISTS business_metrics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    metric_category VARCHAR(50) NOT NULL,
    metric_name VARCHAR(100) NOT NULL,
    metric_value DECIMAL(10,2) DEFAULT 0,
    target_value DECIMAL(10,2) DEFAULT 0,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category_period (metric_category, period_start, period_end)
);

CREATE TABLE IF NOT EXISTS roi_tracking (
    id INT PRIMARY KEY AUTO_INCREMENT,
    investment DECIMAL(15,2) NOT NULL,
    revenue_increase DECIMAL(15,2) DEFAULT 0,
    cost_reduction DECIMAL(15,2) DEFAULT 0,
    period VARCHAR(20) NOT NULL,
    calculated_roi DECIMAL(5,2) DEFAULT 0,
    calculated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_period (period, calculated_at)
);

-- User behavior tracking
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(255) NOT NULL,
    user_id INT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    session_start DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_activity DATETIME DEFAULT CURRENT_TIMESTAMP,
    session_duration INT DEFAULT 0,
    page_views INT DEFAULT 0,
    device_type ENUM('desktop', 'mobile', 'tablet') DEFAULT 'desktop',
    browser VARCHAR(100),
    INDEX idx_user_session (user_id, session_id),
    INDEX idx_last_activity (last_activity)
);

CREATE TABLE IF NOT EXISTS page_views (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(255) NOT NULL,
    user_id INT NULL,
    page_url VARCHAR(500) NOT NULL,
    page_title VARCHAR(200),
    referrer VARCHAR(500),
    time_on_page INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session_page (session_id, page_url),
    INDEX idx_created_at (created_at)
);

CREATE TABLE IF NOT EXISTS user_activities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    activity_type VARCHAR(100) NOT NULL,
    activity_data JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_activity (user_id, activity_type),
    INDEX idx_created_at (created_at)
);

-- Transaction and business activity tracking
CREATE TABLE IF NOT EXISTS transaction_metrics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_id INT NOT NULL,
    transaction_type VARCHAR(50) NOT NULL,
    payment_method VARCHAR(50),
    amount DECIMAL(15,2) NOT NULL,
    processing_time DECIMAL(5,2) DEFAULT 0,
    success BOOLEAN DEFAULT TRUE,
    error_message TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type_method (transaction_type, payment_method),
    INDEX idx_created_at (created_at)
);

CREATE TABLE IF NOT EXISTS app_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    device_id VARCHAR(255),
    platform ENUM('ios', 'android', 'web') DEFAULT 'web',
    app_version VARCHAR(20),
    session_start DATETIME DEFAULT CURRENT_TIMESTAMP,
    session_end DATETIME NULL,
    duration INT DEFAULT 0,
    features_used JSON,
    INDEX idx_user_device (user_id, device_id),
    INDEX idx_platform (platform)
);

-- Automated process tracking
CREATE TABLE IF NOT EXISTS automated_processes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    process_name VARCHAR(100) NOT NULL,
    process_type VARCHAR(50) NOT NULL,
    status ENUM('pending', 'running', 'completed', 'failed') DEFAULT 'pending',
    start_time DATETIME,
    end_time DATETIME,
    duration INT DEFAULT 0,
    records_processed INT DEFAULT 0,
    success_rate DECIMAL(5,2) DEFAULT 0,
    error_message TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type_status (process_type, status),
    INDEX idx_created_at (created_at)
);

-- Risk and compliance tracking
CREATE TABLE IF NOT EXISTS risk_metrics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    risk_type VARCHAR(50) NOT NULL,
    risk_level ENUM('low', 'medium', 'high', 'critical') DEFAULT 'low',
    risk_score DECIMAL(5,2) DEFAULT 0,
    mitigation_status ENUM('identified', 'mitigating', 'resolved') DEFAULT 'identified',
    affected_records INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type_level (risk_type, risk_level),
    INDEX idx_created_at (created_at)
);

CREATE TABLE IF NOT EXISTS compliance_metrics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    regulation VARCHAR(100) NOT NULL,
    compliance_status ENUM('compliant', 'non_compliant', 'pending_review') DEFAULT 'pending_review',
    last_audit DATE,
    next_audit DATE,
    findings TEXT,
    remediation_plan TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_regulation_status (regulation, compliance_status)
);

-- Cost and efficiency tracking
CREATE TABLE IF NOT EXISTS operational_costs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category VARCHAR(100) NOT NULL,
    subcategory VARCHAR(100),
    amount DECIMAL(15,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    period DATE NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category_period (category, period),
    INDEX idx_created_at (created_at)
);

CREATE TABLE IF NOT EXISTS efficiency_metrics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    process_name VARCHAR(100) NOT NULL,
    manual_time DECIMAL(5,2) DEFAULT 0, -- in minutes
    automated_time DECIMAL(5,2) DEFAULT 0, -- in minutes
    efficiency_gain DECIMAL(5,2) DEFAULT 0, -- percentage
    cost_savings DECIMAL(15,2) DEFAULT 0,
    measured_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_process_date (process_name, measured_at)
);

-- Marketplace and ecosystem tracking
CREATE TABLE IF NOT EXISTS marketplace_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_type VARCHAR(50) NOT NULL,
    buyer_id INT,
    seller_id INT,
    product_id INT,
    amount DECIMAL(15,2) NOT NULL,
    platform_fee DECIMAL(10,2) DEFAULT 0,
    status ENUM('pending', 'completed', 'cancelled', 'refunded') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type_status (transaction_type, status),
    INDEX idx_created_at (created_at)
);

CREATE TABLE IF NOT EXISTS community_activities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    activity_type VARCHAR(50) NOT NULL,
    activity_target_id INT,
    points_earned INT DEFAULT 0,
    engagement_score DECIMAL(5,2) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_type (user_id, activity_type),
    INDEX idx_created_at (created_at)
);

-- AI and automation tracking
CREATE TABLE IF NOT EXISTS ai_decisions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    decision_type VARCHAR(100) NOT NULL,
    confidence_score DECIMAL(5,2) DEFAULT 0,
    status ENUM('automated', 'manual_review', 'rejected') DEFAULT 'automated',
    input_data JSON,
    output_data JSON,
    processing_time DECIMAL(5,2) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type_status (decision_type, status),
    INDEX idx_created_at (created_at)
);

CREATE TABLE IF NOT EXISTS cooperative_network (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cooperative_name VARCHAR(200) NOT NULL,
    cooperative_code VARCHAR(20) UNIQUE,
    partnership_type VARCHAR(50),
    status ENUM('active', 'inactive', 'pending') DEFAULT 'pending',
    shared_services JSON,
    api_endpoints JSON,
    joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_sync DATETIME,
    INDEX idx_status (status),
    INDEX idx_joined_at (joined_at)
);

-- Access and error logs
CREATE TABLE IF NOT EXISTS access_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    request_method VARCHAR(10) NOT NULL,
    request_uri VARCHAR(500) NOT NULL,
    response_code INT NOT NULL,
    response_time DECIMAL(5,2) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_ip (user_id, ip_address),
    INDEX idx_response_code (response_code),
    INDEX idx_created_at (created_at)
);

CREATE TABLE IF NOT EXISTS error_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    error_level VARCHAR(20) NOT NULL,
    error_message TEXT NOT NULL,
    error_file VARCHAR(500),
    error_line INT,
    error_context JSON,
    user_id INT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_level_date (error_level, created_at),
    INDEX idx_user_error (user_id, error_level)
);

-- Feedback and satisfaction tracking
CREATE TABLE IF NOT EXISTS member_feedback (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    feedback_type VARCHAR(50) NOT NULL,
    rating DECIMAL(3,1) DEFAULT 0,
    feedback_text TEXT,
    category VARCHAR(50),
    responded BOOLEAN DEFAULT FALSE,
    response_text TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_type (user_id, feedback_type),
    INDEX idx_rating (rating),
    INDEX idx_created_at (created_at)
);

-- Revenue and financial tracking
CREATE TABLE IF NOT EXISTS revenue_metrics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    source_category VARCHAR(100) NOT NULL,
    source_subcategory VARCHAR(100),
    amount DECIMAL(15,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'IDR',
    transaction_count INT DEFAULT 1,
    period DATE NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category_period (source_category, period),
    INDEX idx_created_at (created_at)
);

-- Conversion tracking
CREATE TABLE IF NOT EXISTS conversion_metrics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(255) NOT NULL,
    user_id INT NULL,
    conversion_type VARCHAR(50) NOT NULL,
    conversion_value DECIMAL(10,2) DEFAULT 0,
    funnel_step VARCHAR(50),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session_type (session_id, conversion_type),
    INDEX idx_created_at (created_at)
);

-- A/B testing and optimization
CREATE TABLE IF NOT EXISTS ab_tests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    test_name VARCHAR(100) NOT NULL,
    test_group VARCHAR(50) NOT NULL,
    variant_a VARCHAR(100) NOT NULL,
    variant_b VARCHAR(100) NOT NULL,
    winner VARCHAR(10),
    confidence_level DECIMAL(5,2) DEFAULT 0,
    start_date DATETIME NOT NULL,
    end_date DATETIME,
    status ENUM('running', 'completed', 'cancelled') DEFAULT 'running',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name_status (test_name, status)
);

CREATE TABLE IF NOT EXISTS ab_test_results (
    id INT PRIMARY KEY AUTO_INCREMENT,
    test_id INT NOT NULL,
    variant VARCHAR(10) NOT NULL,
    participants INT DEFAULT 0,
    conversions INT DEFAULT 0,
    conversion_rate DECIMAL(5,2) DEFAULT 0,
    measured_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (test_id) REFERENCES ab_tests(id),
    INDEX idx_test_variant (test_id, variant)
);

-- Performance benchmarks
CREATE TABLE IF NOT EXISTS performance_benchmarks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    benchmark_name VARCHAR(100) NOT NULL,
    metric_type VARCHAR(50) NOT NULL,
    target_value DECIMAL(10,2) NOT NULL,
    current_value DECIMAL(10,2) DEFAULT 0,
    achievement_percentage DECIMAL(5,2) DEFAULT 0,
    last_updated DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type_name (metric_type, benchmark_name)
);

-- Create indexes for better performance
CREATE INDEX idx_monitoring_timestamp ON monitoring_metrics(timestamp);
CREATE INDEX idx_alerts_unresolved ON system_alerts(resolved, created_at);
CREATE INDEX idx_sessions_active ON user_sessions(last_activity);
CREATE INDEX idx_activities_recent ON user_activities(created_at);
CREATE INDEX idx_transactions_recent ON transaction_metrics(created_at);
CREATE INDEX idx_automated_recent ON automated_processes(created_at);
CREATE INDEX idx_marketplace_recent ON marketplace_transactions(created_at);
CREATE INDEX idx_feedback_recent ON member_feedback(created_at);
CREATE INDEX idx_errors_recent ON error_logs(created_at);
