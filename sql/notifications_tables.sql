-- Real-time Notifications System Tables for KSP Samosir
-- Based on fintech trends 2024 - Real-time user engagement and notifications

-- Notifications Table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL, -- NULL for broadcast notifications
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error', 'loan_approved', 'loan_rejected', 'payment_due', 'document_signed', 'system_alert') DEFAULT 'info',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    category ENUM('loan', 'savings', 'payment', 'document', 'system', 'member', 'general') DEFAULT 'general',

    -- Related entities
    related_entity_type ENUM('loan', 'saving', 'member', 'document', 'transaction', 'system') NULL,
    related_entity_id INT NULL,

    -- Notification settings
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,

    -- Sender information
    sent_by INT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Delivery tracking
    email_sent BOOLEAN DEFAULT FALSE,
    sms_sent BOOLEAN DEFAULT FALSE,
    push_sent BOOLEAN DEFAULT FALSE,
    email_sent_at TIMESTAMP NULL,
    sms_sent_at TIMESTAMP NULL,
    push_sent_at TIMESTAMP NULL,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (sent_by) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_user (user_id),
    INDEX idx_type (type),
    INDEX idx_category (category),
    INDEX idx_read (is_read),
    INDEX idx_sent (sent_at),
    INDEX idx_expires (expires_at)
);

-- Notification Templates
CREATE TABLE IF NOT EXISTS notification_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_key VARCHAR(100) NOT NULL UNIQUE,
    title_template VARCHAR(200) NOT NULL,
    message_template TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error', 'loan_approved', 'loan_rejected', 'payment_due', 'document_signed', 'system_alert') DEFAULT 'info',
    category ENUM('loan', 'savings', 'payment', 'document', 'system', 'member', 'general') DEFAULT 'general',
    variables JSON, -- Available template variables
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_key (template_key),
    INDEX idx_active (is_active)
);

-- User Notification Preferences
CREATE TABLE IF NOT EXISTS user_notification_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    notification_type ENUM('email', 'sms', 'push', 'in_app') NOT NULL,
    category ENUM('loan', 'savings', 'payment', 'document', 'system', 'member', 'general') NOT NULL,
    enabled BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    UNIQUE KEY unique_user_type_category (user_id, notification_type, category),
    INDEX idx_user (user_id),
    INDEX idx_type (notification_type),
    INDEX idx_category (category)
);

-- Notification Queue (for batch processing)
CREATE TABLE IF NOT EXISTS notification_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    notification_id INT NOT NULL,
    delivery_method ENUM('email', 'sms', 'push') NOT NULL,
    recipient_contact VARCHAR(200) NOT NULL, -- email or phone number
    status ENUM('pending', 'processing', 'sent', 'failed') DEFAULT 'pending',
    retry_count INT DEFAULT 0,
    max_retries INT DEFAULT 3,
    next_retry_at TIMESTAMP NULL,
    sent_at TIMESTAMP NULL,
    error_message TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,

    INDEX idx_status (status),
    INDEX idx_method (delivery_method),
    INDEX idx_next_retry (next_retry_at)
);

-- Push Notification Tokens (for mobile apps)
CREATE TABLE IF NOT EXISTS push_notification_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    device_type ENUM('ios', 'android', 'web') NOT NULL,
    device_token VARCHAR(255) NOT NULL UNIQUE,
    app_version VARCHAR(50) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_used TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_user (user_id),
    INDEX idx_device (device_type),
    INDEX idx_active (is_active),
    INDEX idx_token (device_token)
);

-- Insert default notification templates
INSERT INTO notification_templates (template_key, title_template, message_template, type, category, variables) VALUES
('loan_approved', 'Pengajuan Pinjaman Disetujui', 'Selamat! Pengajuan pinjaman Anda sebesar Rp {{amount}} telah disetujui. Silakan datang ke kantor untuk proses pencairan.', 'success', 'loan', JSON_OBJECT('amount', 'Loan amount', 'loan_number', 'Loan number')),
('loan_rejected', 'Pengajuan Pinjaman Ditolak', 'Maaf, pengajuan pinjaman Anda sebesar Rp {{amount}} tidak dapat disetujui saat ini. Silakan hubungi kami untuk informasi lebih lanjut.', 'error', 'loan', JSON_OBJECT('amount', 'Loan amount', 'reason', 'Rejection reason')),
('payment_due', 'Pengingat Pembayaran Angsuran', 'Pengingat: Angsuran pinjaman {{loan_number}} sebesar Rp {{amount}} sudah jatuh tempo. Silakan lakukan pembayaran.', 'warning', 'payment', JSON_OBJECT('loan_number', 'Loan number', 'amount', 'Payment amount', 'due_date', 'Due date')),
('document_signed', 'Dokumen Telah Ditandatangani', 'Dokumen {{document_title}} telah berhasil ditandatangani secara elektronik.', 'success', 'document', JSON_OBJECT('document_title', 'Document title', 'signer_name', 'Signer name')),
('savings_deposit', 'Setoran Simpanan Berhasil', 'Setoran simpanan sebesar Rp {{amount}} ke rekening {{account_number}} telah berhasil diproses.', 'success', 'savings', JSON_OBJECT('amount', 'Deposit amount', 'account_number', 'Account number')),
('system_maintenance', 'Pemeliharaan Sistem', 'Sistem akan mengalami pemeliharaan pada {{maintenance_time}}. Beberapa layanan mungkin tidak tersedia sementara.', 'info', 'system', JSON_OBJECT('maintenance_time', 'Maintenance schedule')),
('member_registration', 'Selamat Bergabung!', 'Selamat bergabung sebagai anggota KSP Samosir. Nomor anggota Anda: {{member_number}}.', 'success', 'member', JSON_OBJECT('member_number', 'Member number')),
('loan_disbursement', 'Dana Pinjaman Dicairkan', 'Dana pinjaman sebesar Rp {{amount}} telah dicairkan ke rekening Anda.', 'success', 'loan', JSON_OBJECT('amount', 'Disbursement amount', 'account_number', 'Account number'));

-- Insert sample notifications
INSERT INTO notifications (user_id, title, message, type, priority, category, related_entity_type, related_entity_id, sent_by) VALUES
(NULL, 'Sistem Maintenance', 'Sistem akan mengalami maintenance pada hari Minggu pukul 02:00-04:00 WIB. Mohon maaf atas ketidaknyamanannya.', 'system_alert', 'medium', 'system', 'system', NULL, 1),
(NULL, 'Update Fitur Baru', 'Fitur AI Credit Scoring telah tersedia. Pengajuan pinjaman sekarang lebih cepat dan akurat.', 'info', 'low', 'system', 'system', NULL, 1);
