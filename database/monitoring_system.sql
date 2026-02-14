-- Monitoring System for KSP Samosir
-- Performance tracking, health checks, and alerting

CREATE TABLE system_monitoring (
    id INT PRIMARY KEY AUTO_INCREMENT,
    check_type ENUM('database', 'application', 'performance', 'security', 'storage') NOT NULL,
    check_name VARCHAR(100) NOT NULL,
    status ENUM('healthy', 'warning', 'critical', 'error') DEFAULT 'healthy',
    response_time DECIMAL(8,2), -- in milliseconds
    memory_usage DECIMAL(10,2), -- in MB
    cpu_usage DECIMAL(5,2), -- percentage
    disk_usage DECIMAL(5,2), -- percentage
    error_message TEXT,
    details JSON,
    checked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE performance_metrics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    metric_type VARCHAR(50) NOT NULL, -- 'query_time', 'page_load', 'api_response', etc.
    metric_name VARCHAR(100) NOT NULL,
    value DECIMAL(15,4) NOT NULL,
    unit VARCHAR(20) NOT NULL, -- 'ms', 'seconds', 'MB', '%', etc.
    threshold_warning DECIMAL(15,4),
    threshold_critical DECIMAL(15,4),
    measured_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    context JSON -- additional context data
);

CREATE TABLE application_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    log_level ENUM('debug', 'info', 'warning', 'error', 'critical') DEFAULT 'info',
    category VARCHAR(50) NOT NULL, -- 'database', 'authentication', 'business_logic', etc.
    message TEXT NOT NULL,
    context JSON,
    user_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    logged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE alerts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    alert_type VARCHAR(50) NOT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('active', 'acknowledged', 'resolved') DEFAULT 'active',
    acknowledged_by INT,
    acknowledged_at DATETIME,
    resolved_by INT,
    resolved_at DATETIME,
    auto_resolve TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE system_health_checks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    check_name VARCHAR(100) NOT NULL,
    check_category VARCHAR(50) NOT NULL,
    last_check TIMESTAMP,
    next_check TIMESTAMP,
    status ENUM('passing', 'warning', 'failing') DEFAULT 'passing',
    response_time DECIMAL(8,2),
    details JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for performance
CREATE INDEX idx_system_monitoring_type ON system_monitoring(check_type, checked_at);
CREATE INDEX idx_performance_metrics_type ON performance_metrics(metric_type, measured_at);
CREATE INDEX idx_application_logs_level ON application_logs(log_level, logged_at);
CREATE INDEX idx_application_logs_category ON application_logs(category, logged_at);
CREATE INDEX idx_alerts_status ON alerts(status, created_at);
CREATE INDEX idx_system_health_checks_status ON system_health_checks(status);
