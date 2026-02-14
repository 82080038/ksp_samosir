-- Tax System Tables
-- KSP Samosir - Tax calculations, reporting, and compliance

CREATE TABLE tax_filings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tax_type ENUM('pph21', 'pph23', 'pph25', 'annual_return') NOT NULL,
    period VARCHAR(7) NOT NULL, -- YYYY-MM or YYYY for annual
    tax_amount DECIMAL(15,2) NOT NULL,
    filing_date DATE NOT NULL,
    status ENUM('draft', 'filed', 'approved', 'rejected') DEFAULT 'draft',
    reference_number VARCHAR(50),
    notes TEXT,
    filed_by INT,
    approved_by INT,
    approved_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (filed_by) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);

CREATE TABLE tax_payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tax_type ENUM('pph21', 'pph23', 'pph25', 'pph_final') NOT NULL,
    period VARCHAR(7) NOT NULL,
    payment_amount DECIMAL(15,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method VARCHAR(50),
    bank_reference VARCHAR(100),
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    paid_by INT,
    verified_by INT,
    verified_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (paid_by) REFERENCES users(id),
    FOREIGN KEY (verified_by) REFERENCES users(id)
);

CREATE TABLE withholding_tax (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_id INT NOT NULL,
    recipient_type ENUM('supplier', 'service_provider', 'other') NOT NULL,
    recipient_name VARCHAR(255) NOT NULL,
    recipient_npwp VARCHAR(20),
    gross_amount DECIMAL(15,2) NOT NULL,
    tax_rate DECIMAL(5,2) NOT NULL,
    tax_amount DECIMAL(15,2) NOT NULL,
    net_amount DECIMAL(15,2) NOT NULL,
    tax_type ENUM('pph21', 'pph23', 'pph26', 'pph4_2') NOT NULL,
    transaction_date DATE NOT NULL,
    reported TINYINT(1) DEFAULT 0,
    reported_date DATE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE service_payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    supplier_id INT,
    service_type VARCHAR(100) NOT NULL,
    description TEXT,
    amount DECIMAL(15,2) NOT NULL,
    payment_date DATE NOT NULL,
    invoice_number VARCHAR(50),
    tax_rate DECIMAL(5,2) DEFAULT 2.00, -- Default PPh 23 rate
    tax_amount DECIMAL(15,2),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE tax_compliance_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    compliance_type VARCHAR(100) NOT NULL,
    check_date DATE NOT NULL,
    status ENUM('compliant', 'warning', 'error') NOT NULL,
    details JSON,
    corrective_actions TEXT,
    checked_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (checked_by) REFERENCES users(id)
);

-- Indexes
CREATE INDEX idx_tax_filings_type ON tax_filings(tax_type);
CREATE INDEX idx_tax_filings_period ON tax_filings(period);
CREATE INDEX idx_tax_filings_status ON tax_filings(status);
CREATE INDEX idx_tax_payments_type ON tax_payments(tax_type);
CREATE INDEX idx_tax_payments_period ON tax_payments(period);
CREATE INDEX idx_tax_payments_status ON tax_payments(status);
CREATE INDEX idx_withholding_tax_type ON withholding_tax(tax_type);
CREATE INDEX idx_withholding_tax_date ON withholding_tax(transaction_date);
CREATE INDEX idx_withholding_tax_reported ON withholding_tax(reported);
CREATE INDEX idx_service_payments_supplier ON service_payments(supplier_id);
CREATE INDEX idx_service_payments_date ON service_payments(payment_date);
CREATE INDEX idx_tax_compliance_logs_type ON tax_compliance_logs(compliance_type);
CREATE INDEX idx_tax_compliance_logs_date ON tax_compliance_logs(check_date);
