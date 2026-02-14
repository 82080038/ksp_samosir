-- Enhanced Return & Refund System Tables
-- KSP Samosir - Complete return processing and refund management

CREATE TABLE refunds (
    id INT PRIMARY KEY AUTO_INCREMENT,
    return_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    method ENUM('transfer', 'cash', 'ewallet') DEFAULT 'transfer',
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    bank_account VARCHAR(100),
    reference_number VARCHAR(100),
    created_by INT,
    processed_by INT,
    processed_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (return_id) REFERENCES returns(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (processed_by) REFERENCES users(id)
);

CREATE TABLE return_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    return_id INT NOT NULL,
    action VARCHAR(50) NOT NULL, -- created, approved, processed, rejected
    details TEXT,
    performed_by INT,
    performed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (return_id) REFERENCES returns(id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(id)
);

CREATE TABLE refund_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    refund_id INT NOT NULL,
    action VARCHAR(50) NOT NULL, -- created, processed, completed, failed
    details TEXT,
    performed_by INT,
    performed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (refund_id) REFERENCES refunds(id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(id)
);

-- Add new columns to returns table if not exists
ALTER TABLE returns 
ADD COLUMN kategori_return ENUM('produk_rusak', 'salah_kirim', 'tidak_sesuai_pesanan', 'produk_cacat', 'ubah_pikiran', 'lainnya') DEFAULT 'produk_rusak' AFTER alasan_return,
ADD COLUMN bukti_foto VARCHAR(255) AFTER kategori_return,
ADD COLUMN approved_by INT AFTER status,
ADD COLUMN approved_at DATETIME AFTER approved_by,
ADD COLUMN processed_by INT AFTER approved_at,
ADD COLUMN alasan_keputusan TEXT AFTER processed_by,
ADD COLUMN metode_refund ENUM('transfer', 'cash', 'ewallet') AFTER jumlah_refund;

-- Indexes
CREATE INDEX idx_refunds_return ON refunds(return_id);
CREATE INDEX idx_refunds_status ON refunds(status);
CREATE INDEX idx_refunds_processed_at ON refunds(processed_at);
CREATE INDEX idx_return_logs_return ON return_logs(return_id);
CREATE INDEX idx_return_logs_action ON return_logs(action);
CREATE INDEX idx_refund_logs_refund ON refund_logs(refund_id);
CREATE INDEX idx_refund_logs_action ON refund_logs(action);
CREATE INDEX idx_returns_kategori ON returns(kategori_return);
CREATE INDEX idx_returns_approved_by ON returns(approved_by);
CREATE INDEX idx_returns_processed_by ON returns(processed_by);
