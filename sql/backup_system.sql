-- Backup System Tables
-- KSP Samosir - Automated backup and disaster recovery

CREATE TABLE backup_files (
    id INT PRIMARY KEY AUTO_INCREMENT,
    filename VARCHAR(255) NOT NULL,
    type ENUM('full', 'incremental', 'partial') DEFAULT 'full',
    description TEXT,
    file_size BIGINT NOT NULL,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE backup_schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    frequency ENUM('hourly', 'daily', 'weekly', 'monthly') DEFAULT 'daily',
    scheduled_time TIME DEFAULT '02:00:00',
    enabled TINYINT(1) DEFAULT 1,
    last_run DATETIME,
    next_run DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE backup_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    action VARCHAR(50) NOT NULL, -- create, restore, delete, schedule
    filename VARCHAR(255),
    status VARCHAR(20) NOT NULL, -- success, failed, in_progress
    details TEXT,
    performed_by INT,
    performed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (performed_by) REFERENCES users(id)
);

-- Indexes
CREATE INDEX idx_backup_files_created_at ON backup_files(created_at);
CREATE INDEX idx_backup_files_type ON backup_files(type);
CREATE INDEX idx_backup_schedules_enabled ON backup_schedules(enabled);
CREATE INDEX idx_backup_schedules_next_run ON backup_schedules(next_run);
CREATE INDEX idx_backup_logs_action ON backup_logs(action);
CREATE INDEX idx_backup_logs_status ON backup_logs(status);
CREATE INDEX idx_backup_logs_performed_at ON backup_logs(performed_at);
