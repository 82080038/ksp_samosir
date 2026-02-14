-- Fixed Asset Management Tables
-- KSP Samosir - Asset tracking, depreciation, and maintenance

CREATE TABLE fixed_assets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    asset_code VARCHAR(20) UNIQUE NOT NULL,
    asset_name VARCHAR(255) NOT NULL,
    category ENUM('tanah', 'bangunan', 'kendaraan', 'peralatan', 'inventaris', 'lainnya') NOT NULL,
    acquisition_date DATE NOT NULL,
    acquisition_cost DECIMAL(15,2) NOT NULL,
    useful_life_years INT NOT NULL,
    salvage_value DECIMAL(15,2) DEFAULT 0,
    location VARCHAR(255),
    condition_status ENUM('excellent', 'good', 'fair', 'poor', 'critical', 'disposed') DEFAULT 'excellent',
    disposal_date DATE,
    disposal_value DECIMAL(15,2),
    last_maintenance_date DATE,
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE asset_depreciation (
    id INT PRIMARY KEY AUTO_INCREMENT,
    asset_id INT NOT NULL,
    depreciation_date DATE NOT NULL,
    depreciation_amount DECIMAL(15,2) NOT NULL,
    accumulated_depreciation DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (asset_id) REFERENCES fixed_assets(id) ON DELETE CASCADE
);

CREATE TABLE asset_maintenance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    asset_id INT NOT NULL,
    maintenance_date DATE NOT NULL,
    maintenance_type ENUM('preventive', 'corrective', 'predictive', 'major_repair', 'inspection') NOT NULL,
    description TEXT NOT NULL,
    cost DECIMAL(15,2) DEFAULT 0,
    performed_by VARCHAR(255),
    next_maintenance_date DATE,
    recorded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (asset_id) REFERENCES fixed_assets(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id)
);

CREATE TABLE asset_disposals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    asset_id INT NOT NULL,
    disposal_date DATE NOT NULL,
    disposal_method ENUM('sale', 'scrap', 'donation', 'loss') NOT NULL,
    disposal_value DECIMAL(15,2) DEFAULT 0,
    reason TEXT,
    approved_by INT,
    recorded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (asset_id) REFERENCES fixed_assets(id),
    FOREIGN KEY (approved_by) REFERENCES users(id),
    FOREIGN KEY (recorded_by) REFERENCES users(id)
);

-- Indexes
CREATE INDEX idx_fixed_assets_asset_code ON fixed_assets(asset_code);
CREATE INDEX idx_fixed_assets_category ON fixed_assets(category);
CREATE INDEX idx_fixed_assets_condition ON fixed_assets(condition_status);
CREATE INDEX idx_fixed_assets_location ON fixed_assets(location);
CREATE INDEX idx_asset_depreciation_asset_id ON asset_depreciation(asset_id);
CREATE INDEX idx_asset_depreciation_date ON asset_depreciation(depreciation_date);
CREATE INDEX idx_asset_maintenance_asset_id ON asset_maintenance(asset_id);
CREATE INDEX idx_asset_maintenance_date ON asset_maintenance(maintenance_date);
CREATE INDEX idx_asset_maintenance_type ON asset_maintenance(maintenance_type);
CREATE INDEX idx_asset_disposals_asset_id ON asset_disposals(asset_id);
CREATE INDEX idx_asset_disposals_date ON asset_disposals(disposal_date);
