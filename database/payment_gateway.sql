-- Payment Gateway Integration Tables
-- KSP Samosir - Payment processing and logging

CREATE TABLE payment_attempts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    payment_gateway VARCHAR(50) NOT NULL,
    transaction_id VARCHAR(100) NOT NULL,
    snap_token TEXT,
    status VARCHAR(50) DEFAULT 'pending',
    amount DECIMAL(15,2),
    currency VARCHAR(3) DEFAULT 'IDR',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES penjualan(id)
);

CREATE TABLE payment_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    gateway VARCHAR(50) NOT NULL,
    notification_type VARCHAR(50),
    transaction_status VARCHAR(50),
    payment_type VARCHAR(50),
    fraud_status VARCHAR(50),
    raw_data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES penjualan(id)
);

-- Indexes
CREATE INDEX idx_payment_attempts_order ON payment_attempts(order_id);
CREATE INDEX idx_payment_attempts_transaction ON payment_attempts(transaction_id);
CREATE INDEX idx_payment_logs_order ON payment_logs(order_id);
CREATE INDEX idx_payment_logs_created ON payment_logs(created_at);
