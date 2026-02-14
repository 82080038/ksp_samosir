-- Invoice Management Tables
-- KSP Samosir - Complete invoice system for customers and suppliers

CREATE TABLE customer_invoices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_number VARCHAR(20) UNIQUE NOT NULL,
    order_id INT NOT NULL,
    customer_id INT NOT NULL,
    total_amount DECIMAL(15,2) NOT NULL,
    status ENUM('unpaid', 'paid', 'overdue', 'cancelled') DEFAULT 'unpaid',
    due_date DATE,
    paid_date DATE,
    payment_method VARCHAR(50),
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES penjualan(id),
    FOREIGN KEY (customer_id) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE supplier_invoices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    po_id INT NOT NULL,
    supplier_id INT NOT NULL,
    nomor_invoice VARCHAR(50) UNIQUE NOT NULL,
    tanggal_invoice DATE NOT NULL,
    tanggal_jatuh_tempo DATE,
    total_nilai DECIMAL(15,2) NOT NULL,
    status_pembayaran ENUM('belum_lunas', 'lunas', 'overdue') DEFAULT 'belum_lunas',
    tanggal_bayar DATE,
    metode_pembayaran VARCHAR(50),
    catatan TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (po_id) REFERENCES purchase_orders(id),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE invoice_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    invoice_id INT NOT NULL,
    invoice_type ENUM('customer', 'supplier') NOT NULL,
    action VARCHAR(50) NOT NULL, -- generated, sent, paid, overdue, etc.
    details TEXT,
    performed_by INT,
    performed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (performed_by) REFERENCES users(id)
);

-- Indexes
CREATE INDEX idx_customer_invoices_order ON customer_invoices(order_id);
CREATE INDEX idx_customer_invoices_customer ON customer_invoices(customer_id);
CREATE INDEX idx_customer_invoices_status ON customer_invoices(status);
CREATE INDEX idx_customer_invoices_due_date ON customer_invoices(due_date);
CREATE INDEX idx_supplier_invoices_po ON supplier_invoices(po_id);
CREATE INDEX idx_supplier_invoices_supplier ON supplier_invoices(supplier_id);
CREATE INDEX idx_supplier_invoices_status ON supplier_invoices(status_pembayaran);
CREATE INDEX idx_supplier_invoices_due_date ON supplier_invoices(tanggal_jatuh_tempo);
CREATE INDEX idx_invoice_logs_invoice ON invoice_logs(invoice_id);
CREATE INDEX idx_invoice_logs_type ON invoice_logs(invoice_type);
CREATE INDEX idx_invoice_logs_action ON invoice_logs(action);
