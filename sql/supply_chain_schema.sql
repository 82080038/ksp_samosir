-- Supply Chain Integration Database Schema for KSP Samosir

-- Vendor/Supplier Management
CREATE TABLE IF NOT EXISTS vendors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    vendor_code VARCHAR(20) UNIQUE NOT NULL,
    vendor_name VARCHAR(200) NOT NULL,
    vendor_type ENUM('supplier', 'service_provider', 'manufacturer') DEFAULT 'supplier',
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    city VARCHAR(100),
    province VARCHAR(100),
    postal_code VARCHAR(10),
    tax_id VARCHAR(50),
    payment_terms VARCHAR(100),
    credit_limit DECIMAL(15,2) DEFAULT 0,
    current_balance DECIMAL(15,2) DEFAULT 0,
    performance_rating DECIMAL(3,1) DEFAULT 5.0,
    contract_start_date DATE,
    contract_end_date DATE,
    status ENUM('active', 'inactive', 'suspended', 'blacklisted') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_type (vendor_type),
    INDEX idx_performance (performance_rating)
);

-- Purchase Orders
CREATE TABLE IF NOT EXISTS purchase_orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    po_number VARCHAR(20) UNIQUE NOT NULL,
    vendor_id INT NOT NULL,
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    expected_delivery_date DATE,
    actual_delivery_date DATE,
    status ENUM('draft', 'approved', 'ordered', 'partial_delivery', 'delivered', 'cancelled') DEFAULT 'draft',
    total_amount DECIMAL(15,2) DEFAULT 0,
    tax_amount DECIMAL(15,2) DEFAULT 0,
    discount_amount DECIMAL(15,2) DEFAULT 0,
    shipping_cost DECIMAL(15,2) DEFAULT 0,
    payment_status ENUM('unpaid', 'partial', 'paid') DEFAULT 'unpaid',
    approved_by INT,
    approved_at DATETIME,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (vendor_id) REFERENCES vendors(id),
    INDEX idx_status (status),
    INDEX idx_vendor (vendor_id),
    INDEX idx_date (order_date)
);

-- Purchase Order Items
CREATE TABLE IF NOT EXISTS purchase_order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    po_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    quantity_ordered DECIMAL(10,2) NOT NULL,
    quantity_received DECIMAL(10,2) DEFAULT 0,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(12,2) NOT NULL,
    quality_status ENUM('pending', 'passed', 'failed', 'rejected') DEFAULT 'pending',
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (po_id) REFERENCES purchase_orders(id),
    INDEX idx_po (po_id),
    INDEX idx_product (product_id),
    INDEX idx_quality (quality_status)
);

-- Inventory Management
CREATE TABLE IF NOT EXISTS inventory_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    warehouse_id INT,
    batch_number VARCHAR(50),
    serial_number VARCHAR(100),
    quantity_on_hand DECIMAL(10,2) DEFAULT 0,
    quantity_reserved DECIMAL(10,2) DEFAULT 0,
    quantity_available DECIMAL(10,2) DEFAULT 0,
    unit_cost DECIMAL(10,2) DEFAULT 0,
    location_code VARCHAR(50),
    expiry_date DATE,
    manufacturing_date DATE,
    quality_status ENUM('good', 'damaged', 'expired', 'quarantined') DEFAULT 'good',
    last_inventory_check DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_product (product_id),
    INDEX idx_warehouse (warehouse_id),
    INDEX idx_batch (batch_number),
    INDEX idx_quality (quality_status),
    INDEX idx_expiry (expiry_date)
);

-- Inventory Transactions
CREATE TABLE IF NOT EXISTS inventory_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    inventory_item_id INT NOT NULL,
    transaction_type ENUM('receipt', 'issue', 'adjustment', 'transfer', 'return') NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    reference_type VARCHAR(50), -- 'purchase_order', 'sales_order', 'adjustment'
    reference_id INT,
    from_location VARCHAR(100),
    to_location VARCHAR(100),
    performed_by INT NOT NULL,
    notes TEXT,
    transaction_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id),
    INDEX idx_type (transaction_type),
    INDEX idx_reference (reference_type, reference_id),
    INDEX idx_date (transaction_date)
);

-- Logistics and Shipping
CREATE TABLE IF NOT EXISTS shipments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    shipment_number VARCHAR(20) UNIQUE NOT NULL,
    reference_type VARCHAR(50), -- 'purchase_order', 'sales_order'
    reference_id INT,
    carrier_name VARCHAR(100),
    tracking_number VARCHAR(100),
    shipment_date DATETIME,
    estimated_delivery DATE,
    actual_delivery DATETIME,
    origin_address TEXT,
    destination_address TEXT,
    shipping_cost DECIMAL(10,2) DEFAULT 0,
    status ENUM('pending', 'in_transit', 'delivered', 'delayed', 'lost', 'returned') DEFAULT 'pending',
    weight_kg DECIMAL(8,2),
    dimensions TEXT, -- JSON: {"length": 10, "width": 5, "height": 3}
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_reference (reference_type, reference_id),
    INDEX idx_status (status),
    INDEX idx_tracking (tracking_number)
);

CREATE TABLE IF NOT EXISTS shipment_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    shipment_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit_weight DECIMAL(5,2),
    quality_check_status ENUM('pending', 'passed', 'failed') DEFAULT 'pending',
    notes TEXT,
    FOREIGN KEY (shipment_id) REFERENCES shipments(id),
    INDEX idx_shipment (shipment_id),
    INDEX idx_product (product_id)
);

-- Quality Control
CREATE TABLE IF NOT EXISTS quality_inspections (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reference_type VARCHAR(50) NOT NULL, -- 'purchase_order', 'shipment', 'inventory'
    reference_id INT NOT NULL,
    inspection_type ENUM('incoming', 'in_process', 'final', 'random') DEFAULT 'incoming',
    inspector_id INT NOT NULL,
    inspection_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    overall_result ENUM('passed', 'failed', 'conditional') DEFAULT 'passed',
    notes TEXT,
    corrective_actions TEXT,
    follow_up_required BOOLEAN DEFAULT FALSE,
    follow_up_date DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_reference (reference_type, reference_id),
    INDEX idx_type (inspection_type),
    INDEX idx_result (overall_result),
    INDEX idx_inspector (inspector_id)
);

CREATE TABLE IF NOT EXISTS quality_check_criteria (
    id INT PRIMARY KEY AUTO_INCREMENT,
    inspection_id INT NOT NULL,
    criteria_name VARCHAR(200) NOT NULL,
    expected_value VARCHAR(100),
    actual_value VARCHAR(100),
    result ENUM('pass', 'fail', 'na') DEFAULT 'pass',
    severity ENUM('critical', 'major', 'minor') DEFAULT 'minor',
    notes TEXT,
    FOREIGN KEY (inspection_id) REFERENCES quality_inspections(id),
    INDEX idx_inspection (inspection_id),
    INDEX idx_result (result),
    INDEX idx_severity (severity)
);

-- Demand Forecasting
CREATE TABLE IF NOT EXISTS demand_forecasts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    forecast_period VARCHAR(20) NOT NULL, -- 'weekly', 'monthly', 'quarterly'
    forecast_date DATE NOT NULL,
    forecasted_quantity DECIMAL(10,2) NOT NULL,
    confidence_level DECIMAL(5,2) DEFAULT 0,
    actual_quantity DECIMAL(10,2),
    forecast_accuracy DECIMAL(5,2),
    factors_considered JSON,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_product (product_id),
    INDEX idx_period (forecast_period),
    INDEX idx_date (forecast_date)
);

-- Automated Replenishment Rules
CREATE TABLE IF NOT EXISTS replenishment_rules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    min_stock_level DECIMAL(10,2) DEFAULT 0,
    max_stock_level DECIMAL(10,2) DEFAULT 0,
    reorder_point DECIMAL(10,2) DEFAULT 0,
    reorder_quantity DECIMAL(10,2) DEFAULT 0,
    lead_time_days INT DEFAULT 7,
    supplier_priority JSON, -- Preferred suppliers with priority
    auto_reorder_enabled BOOLEAN DEFAULT FALSE,
    last_reorder_date DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_product (product_id),
    INDEX idx_auto_reorder (auto_reorder_enabled)
);

-- Supplier Performance Tracking
CREATE TABLE IF NOT EXISTS supplier_performance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    vendor_id INT NOT NULL,
    evaluation_period VARCHAR(20) NOT NULL, -- 'monthly', 'quarterly'
    evaluation_date DATE NOT NULL,
    on_time_delivery_rate DECIMAL(5,2) DEFAULT 0,
    quality_rating DECIMAL(3,1) DEFAULT 0,
    responsiveness_rating DECIMAL(3,1) DEFAULT 0,
    price_competitiveness DECIMAL(3,1) DEFAULT 0,
    overall_score DECIMAL(5,2) DEFAULT 0,
    improvement_areas TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vendor_id) REFERENCES vendors(id),
    INDEX idx_vendor (vendor_id),
    INDEX idx_period (evaluation_period),
    INDEX idx_date (evaluation_date),
    INDEX idx_score (overall_score)
);

-- Warehouse Management
CREATE TABLE IF NOT EXISTS warehouses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    warehouse_code VARCHAR(20) UNIQUE NOT NULL,
    warehouse_name VARCHAR(100) NOT NULL,
    address TEXT,
    city VARCHAR(100),
    province VARCHAR(100),
    manager_id INT,
    capacity_sqft DECIMAL(10,2),
    current_utilization DECIMAL(5,2) DEFAULT 0,
    status ENUM('active', 'inactive', 'maintenance') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_manager (manager_id)
);

CREATE TABLE IF NOT EXISTS warehouse_zones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    warehouse_id INT NOT NULL,
    zone_code VARCHAR(20) NOT NULL,
    zone_name VARCHAR(100) NOT NULL,
    zone_type ENUM('storage', 'picking', 'shipping', 'receiving', 'damaged') DEFAULT 'storage',
    capacity DECIMAL(10,2),
    current_usage DECIMAL(10,2) DEFAULT 0,
    temperature_controlled BOOLEAN DEFAULT FALSE,
    security_level ENUM('low', 'medium', 'high') DEFAULT 'low',
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id),
    INDEX idx_warehouse (warehouse_id),
    INDEX idx_type (zone_type)
);

-- Integration with external logistics providers
CREATE TABLE IF NOT EXISTS logistics_providers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    provider_name VARCHAR(100) NOT NULL,
    provider_code VARCHAR(20) UNIQUE NOT NULL,
    api_endpoint VARCHAR(500),
    api_key VARCHAR(255),
    service_types JSON, -- ['express', 'standard', 'economy']
    rate_card JSON, -- Pricing information
    status ENUM('active', 'inactive') DEFAULT 'active',
    contract_start_date DATE,
    contract_end_date DATE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status)
);

-- Automated alerts for supply chain events
CREATE TABLE IF NOT EXISTS supply_chain_alerts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    alert_type VARCHAR(50) NOT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    reference_type VARCHAR(50),
    reference_id INT,
    message TEXT NOT NULL,
    suggested_actions TEXT,
    acknowledged BOOLEAN DEFAULT FALSE,
    acknowledged_by INT,
    acknowledged_at DATETIME,
    resolved BOOLEAN DEFAULT FALSE,
    resolved_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (alert_type),
    INDEX idx_severity (severity),
    INDEX idx_reference (reference_type, reference_id),
    INDEX idx_acknowledged (acknowledged),
    INDEX idx_resolved (resolved)
);

-- Create indexes for performance
CREATE INDEX idx_po_items_po ON purchase_order_items(po_id);
CREATE INDEX idx_inventory_product ON inventory_items(product_id);
CREATE INDEX idx_inventory_transactions_item ON inventory_transactions(inventory_item_id);
CREATE INDEX idx_shipment_items_shipment ON shipment_items(shipment_id);
CREATE INDEX idx_quality_criteria_inspection ON quality_check_criteria(inspection_id);
CREATE INDEX idx_forecasts_product_period ON demand_forecasts(product_id, forecast_period);
CREATE INDEX idx_replenishment_product ON replenishment_rules(product_id);
CREATE INDEX idx_performance_vendor_period ON supplier_performance(vendor_id, evaluation_period);
CREATE INDEX idx_zones_warehouse ON warehouse_zones(warehouse_id);
