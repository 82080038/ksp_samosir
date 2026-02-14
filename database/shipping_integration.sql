-- Shipping Integration Tables
-- KSP Samosir - Shipping and courier integration

CREATE TABLE shipping_details (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    courier VARCHAR(50) NOT NULL,
    service VARCHAR(100) NOT NULL,
    cost DECIMAL(10,2) NOT NULL,
    estimated_days VARCHAR(50),
    tracking_number VARCHAR(100),
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES penjualan(id) ON DELETE CASCADE
);

-- Indexes
CREATE INDEX idx_shipping_details_order ON shipping_details(order_id);
CREATE INDEX idx_shipping_details_courier ON shipping_details(courier);
CREATE INDEX idx_shipping_details_status ON shipping_details(status);
