-- Additional Tables for KSP Samosir Application
-- These tables support advanced reporting and financial features

-- Reports Table - Track generated reports
CREATE TABLE IF NOT EXISTS reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    jenis_laporan VARCHAR(50) NOT NULL,
    periode VARCHAR(20) DEFAULT 'monthly',
    start_date DATE,
    end_date DATE,
    format VARCHAR(10) DEFAULT 'html',
    generated_by INT NOT NULL,
    parameters JSON,
    file_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (generated_by) REFERENCES users(id)
);

-- Profit Distributions Table - Track SHU distributions
CREATE TABLE IF NOT EXISTS profit_distributions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tahun INT NOT NULL,
    total_shu DECIMAL(15,2) NOT NULL DEFAULT 0,
    shu_anggota DECIMAL(15,2) NOT NULL DEFAULT 0,
    shu_pengurus DECIMAL(15,2) NOT NULL DEFAULT 0,
    shu_pengawas DECIMAL(15,2) NOT NULL DEFAULT 0,
    shu_jasa_simpanan DECIMAL(15,2) NOT NULL DEFAULT 0,
    shu_jasa_pinjaman DECIMAL(15,2) NOT NULL DEFAULT 0,
    status ENUM('draft', 'approved', 'distributed') DEFAULT 'draft',
    tanggal_penetapan DATE,
    tanggal_distribusi DATE,
    approved_by INT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (approved_by) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Operational Costs Table - Track operational expenses
CREATE TABLE IF NOT EXISTS operational_costs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kategori VARCHAR(50) NOT NULL,
    deskripsi TEXT,
    jumlah DECIMAL(15,2) NOT NULL,
    tanggal DATE NOT NULL,
    bulan INT GENERATED ALWAYS AS (MONTH(tanggal)) STORED,
    tahun INT GENERATED ALWAYS AS (YEAR(tanggal)) STORED,
    bukti_path VARCHAR(255),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Indexes for performance
CREATE INDEX idx_reports_created_at ON reports(created_at);
CREATE INDEX idx_profit_distributions_tahun ON profit_distributions(tahun);
CREATE INDEX idx_operational_costs_tanggal ON operational_costs(tanggal);
CREATE INDEX idx_operational_costs_bulan_tahun ON operational_costs(bulan, tahun);

-- Insert sample data for development
INSERT INTO reports (jenis_laporan, periode, generated_by) VALUES
('simpanan', 'monthly', 1),
('pinjaman', 'monthly', 1),
('neraca', 'yearly', 1);

INSERT INTO profit_distributions (tahun, total_shu, shu_anggota, shu_pengurus, shu_pengawas, status) VALUES
(2024, 50000000, 40000000, 5000000, 5000000, 'distributed');

INSERT INTO operational_costs (kategori, deskripsi, jumlah, tanggal, created_by) VALUES
('Listrik', 'Biaya listrik bulan Januari', 2500000, '2024-01-31', 1),
('Gaji', 'Gaji karyawan bulan Januari', 15000000, '2024-01-31', 1),
('Sewa', 'Biaya sewa kantor bulan Januari', 5000000, '2024-01-31', 1);
