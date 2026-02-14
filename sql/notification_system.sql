-- Notification System Tables
-- KSP Samosir - SMS and WhatsApp notification logging

CREATE TABLE notification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reference_id INT NULL, -- Order ID, return ID, etc.
    type VARCHAR(50) NOT NULL, -- order_confirmation, payment_success, shipping, etc.
    channel VARCHAR(20) NOT NULL, -- whatsapp, sms, email
    message TEXT NOT NULL,
    user_id INT NULL,
    recipient VARCHAR(20) NULL, -- Phone number
    status VARCHAR(20) DEFAULT 'sent', -- sent, failed, pending
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    error_message TEXT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (reference_id) REFERENCES penjualan(id) -- Can be extended for other references
);

-- Indexes
CREATE INDEX idx_notification_logs_reference ON notification_logs(reference_id);
CREATE INDEX idx_notification_logs_type ON notification_logs(type);
CREATE INDEX idx_notification_logs_channel ON notification_logs(channel);
CREATE INDEX idx_notification_logs_user ON notification_logs(user_id);
CREATE INDEX idx_notification_logs_sent_at ON notification_logs(sent_at);
