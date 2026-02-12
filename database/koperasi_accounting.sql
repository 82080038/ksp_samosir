-- Koperasi Accounting System Enhancement for KSP Samosir
-- Based on koperasi_db structure with cooperative accounting principles

-- Chart of Accounts (COA) - Enhanced
CREATE TABLE IF NOT EXISTS coa (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    type ENUM('asset', 'liability', 'equity', 'revenue', 'expense') NOT NULL,
    parent_id INT NULL,
    level INT DEFAULT 1,
    is_active TINYINT(1) DEFAULT 1,
    cooperative_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES coa(id) ON DELETE SET NULL
);

-- Insert Standard COA for Koperasi (using existing table structure)
INSERT INTO coa (kode_coa, nama_coa, tipe, level) VALUES
-- Assets (1xxx) - debit type
('1000', 'Kas', 'debit', 1),
('1100', 'Bank', 'debit', 1),
('1200', 'Piutang Anggota', 'debit', 1),
('1300', 'Piutang Pinjaman', 'debit', 1),
('1400', 'Inventaris', 'debit', 1),
('1500', 'Aset Tetap', 'debit', 1),

-- Liabilities (2xxx) - credit type  
('2000', 'Simpanan Anggota', 'kredit', 1),
('2100', 'Pinjaman Bank', 'kredit', 1),
('2200', 'Hutang Usaha', 'kredit', 1),

-- Equity (3xxx) - credit type
('3000', 'Modal Pokok', 'kredit', 1),
('3100', 'Modal Penyerta', 'kredit', 1),
('3200', 'Cadangan Resiko', 'kredit', 1),
('3300', 'SHU Tahun Berjalan', 'kredit', 1),
('3400', 'SHU Ditahan', 'kredit', 1),

-- Revenue (4xxx) - credit type
('4000', 'Pendapatan Bunga Pinjaman', 'kredit', 1),
('4100', 'Pendapatan Jasa Administrasi', 'kredit', 1),
('4200', 'Pendapatan Penjualan', 'kredit', 1),
('4300', 'Pendapatan Lain-lain', 'kredit', 1),

-- Expenses (5xxx) - debit type
('5000', 'Beban Bunga Simpanan', 'debit', 1),
('5100', 'Beban Operasional', 'debit', 1),
('5200', 'Beban Penyusutan', 'debit', 1),
('5300', 'Beban Lain-lain', 'debit', 1)
ON DUPLICATE KEY UPDATE nama_coa=VALUES(nama_coa);

-- Jurnal/General Ledger
CREATE TABLE IF NOT EXISTS jurnal (
    id INT PRIMARY KEY AUTO_INCREMENT,
    entry_date DATE NOT NULL,
    description TEXT NOT NULL,
    reference_number VARCHAR(50),
    status ENUM('draft', 'posted') DEFAULT 'draft',
    cooperative_id INT DEFAULT 1,
    posted_by INT,
    posted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (posted_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Jurnal Detail
CREATE TABLE IF NOT EXISTS jurnal_detail (
    id INT PRIMARY KEY AUTO_INCREMENT,
    jurnal_id INT NOT NULL,
    coa_id INT NOT NULL,
    debit DECIMAL(15,2) DEFAULT 0,
    credit DECIMAL(15,2) DEFAULT 0,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (jurnal_id) REFERENCES jurnal(id) ON DELETE CASCADE,
    FOREIGN KEY (coa_id) REFERENCES coa(id) ON DELETE RESTRICT
);

-- Buku Besar
CREATE TABLE IF NOT EXISTS buku_besar (
    id INT PRIMARY KEY AUTO_INCREMENT,
    coa_id INT NOT NULL,
    tanggal DATE NOT NULL,
    description TEXT,
    debit DECIMAL(15,2) DEFAULT 0,
    credit DECIMAL(15,2) DEFAULT 0,
    saldo DECIMAL(15,2) DEFAULT 0,
    jurnal_detail_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coa_id) REFERENCES coa(id) ON DELETE RESTRICT,
    FOREIGN KEY (jurnal_detail_id) REFERENCES jurnal_detail(id) ON DELETE SET NULL
);

-- SHU (Sisa Hasil Usaha) Distribution
CREATE TABLE IF NOT EXISTS shu_periode (
    id INT PRIMARY KEY AUTO_INCREMENT,
    periode_start DATE NOT NULL,
    periode_end DATE NOT NULL,
    total_shu DECIMAL(15,2) DEFAULT 0,
    persentase_modal DECIMAL(5,2) DEFAULT 0,
    persentase_jasa DECIMAL(5,2) DEFAULT 0,
    status ENUM('draft', 'calculated', 'distributed') DEFAULT 'draft',
    calculated_at TIMESTAMP NULL,
    distributed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- SHU Anggota Distribution
CREATE TABLE IF NOT EXISTS shu_anggota (
    id INT PRIMARY KEY AUTO_INCREMENT,
    anggota_id INT NOT NULL,
    shu_periode_id INT NOT NULL,
    jumlah_simpanan DECIMAL(15,2) DEFAULT 0,
    total_shu DECIMAL(15,2) DEFAULT 0,
    persentase_shu DECIMAL(5,2) DEFAULT 0,
    status ENUM('calculated', 'paid', 'reserved') DEFAULT 'calculated',
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (anggota_id) REFERENCES anggota(id) ON DELETE CASCADE,
    FOREIGN KEY (shu_periode_id) REFERENCES shu_periode(id) ON DELETE CASCADE
);

-- Modal Pokok Perubahan
CREATE TABLE IF NOT EXISTS modal_pokok (
    id INT PRIMARY KEY AUTO_INCREMENT,
    anggota_id INT NOT NULL,
    jumlah DECIMAL(15,2) NOT NULL,
    jenis ENUM('masuk', 'keluar') NOT NULL,
    tanggal DATE NOT NULL,
    description TEXT,
    bukti VARCHAR(255),
    status ENUM('draft', 'approved', 'rejected') DEFAULT 'draft',
    approved_by INT,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (anggota_id) REFERENCES anggota(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Cooperative Settings
CREATE TABLE IF NOT EXISTS pengaturan_koperasi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    value TEXT,
    description TEXT,
    type ENUM('string', 'number', 'decimal', 'boolean', 'date') DEFAULT 'string',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default settings
INSERT INTO pengaturan_koperasi (setting_key, value, description, type) VALUES
('nama_koperasi', 'KSP Samosir', 'Nama Koperasi', 'string'),
('alamat', 'Jl. Contoh No. 123', 'Alamat Koperasi', 'string'),
('no_telp', '(021) 1234567', 'Nomor Telepon', 'string'),
('email', 'info@ksp_samosir.co.id', 'Email Koperasi', 'string'),
('bunga_simpanan_wajib', '3.00', 'Bunga Simpanan Wajib (%)', 'decimal'),
('bunga_simpanan_sukarela', '4.00', 'Bunga Simpanan Sukarela (%)', 'decimal'),
('bunga_pinjaman', '12.00', 'Bunga Pinjaman (%)', 'decimal'),
('provisi_rugi', '5.00', 'Provisi Rugi (%)', 'decimal'),
('cadangan_resiko', '2.00', 'Cadangan Resiko (%)', 'decimal'),
('periode_akuntansi', 'tahunan', 'Periode Akuntansi', 'string'),
('tahun_buku', '2024', 'Tahun Buku', 'string')
ON DUPLICATE KEY UPDATE value=VALUES(value);

-- Views for reporting
CREATE OR REPLACE VIEW v_neraca AS
SELECT 
    coa.code,
    coa.name,
    coa.type,
    SUM(CASE WHEN bb.debit > 0 THEN bb.debit ELSE 0 END) as total_debit,
    SUM(CASE WHEN bb.credit > 0 THEN bb.credit ELSE 0 END) as total_credit,
    SUM(bb.saldo) as saldo
FROM coa 
LEFT JOIN buku_besar bb ON coa.id = bb.coa_id
WHERE coa.is_active = 1
GROUP BY coa.id, coa.code, coa.name, coa.type
ORDER BY coa.code;

CREATE OR REPLACE VIEW v_laba_rugi AS
SELECT 
    SUM(CASE WHEN coa.type = 'revenue' THEN bb.credit ELSE 0 END) as total_pendapatan,
    SUM(CASE WHEN coa.type = 'expense' THEN bb.debit ELSE 0 END) as total_beban,
    SUM(CASE WHEN coa.type = 'revenue' THEN bb.credit ELSE 0 END) - 
    SUM(CASE WHEN coa.type = 'expense' THEN bb.debit ELSE 0 END) as laba_rugi_bersih
FROM coa 
JOIN buku_besar bb ON coa.id = bb.coa_id
WHERE coa.type IN ('revenue', 'expense') AND coa.is_active = 1
GROUP BY coa.type;

-- Triggers for automatic posting
DELIMITER //
CREATE TRIGGER post_jurnal_detail 
AFTER INSERT ON jurnal_detail
FOR EACH ROW
BEGIN
    -- Update buku besar
    INSERT INTO buku_besar (coa_id, tanggal, description, debit, credit, saldo, jurnal_detail_id)
    SELECT 
        NEW.coa_id, 
        (SELECT entry_date FROM jurnal WHERE id = NEW.jurnal_id),
        NEW.description,
        NEW.debit,
        NEW.credit,
        (
            SELECT IFNULL(SUM(saldo), 0) 
            FROM buku_besar 
            WHERE coa_id = NEW.coa_id 
            AND tanggal = (SELECT entry_date FROM jurnal WHERE id = NEW.jurnal_id)
        ) + NEW.debit - NEW.credit,
        NEW.id
    ON DUPLICATE KEY UPDATE 
        debit = debit + NEW.debit,
        credit = credit + NEW.credit,
        saldo = saldo + NEW.debit - NEW.credit;
END//
DELIMITER ;

-- Stored Procedures for SHU calculation
DELIMITER //
CREATE PROCEDURE calculate_shu(IN periode_start DATE, IN periode_end DATE)
BEGIN
    DECLARE total_shu DECIMAL(15,2) DEFAULT 0;
    DECLARE modal_awal DECIMAL(15,2) DEFAULT 0;
    DECLARE jasa_anggota DECIMAL(15,2) DEFAULT 0;
    
    -- Calculate total SHU
    SELECT 
        SUM(CASE WHEN coa.type = 'revenue' THEN bb.credit ELSE 0 END) - 
        SUM(CASE WHEN coa.type = 'expense' THEN bb.debit ELSE 0 END)
    INTO total_shu
    FROM coa 
    JOIN buku_besar bb ON coa.id = bb.coa_id
    WHERE coa.type IN ('revenue', 'expense') 
    AND bb.tanggal BETWEEN periode_start AND periode_end
    AND coa.is_active = 1;
    
    -- Get modal pokok awal
    SELECT SUM(CASE WHEN bb.debit > 0 THEN bb.debit ELSE 0 END)
    INTO modal_awal
    FROM coa 
    JOIN buku_besar bb ON coa.id = bb.coa_id
    WHERE coa.code = '3000' AND bb.tanggal < periode_start
    AND coa.is_active = 1;
    
    -- Calculate jasa anggota (25% of revenue)
    SELECT SUM(CASE WHEN coa.code = '4000' THEN bb.credit ELSE 0 END) * 0.25
    INTO jasa_anggota
    FROM coa 
    JOIN buku_besar bb ON coa.id = bb.coa_id
    WHERE coa.code = '4000' 
    AND bb.tanggal BETWEEN periode_start AND periode_end
    AND coa.is_active = 1;
    
    -- Insert SHU periode record
    INSERT INTO shu_periode (periode_start, periode_end, total_shu, persentase_modal, persentase_jasa, status, calculated_at)
    VALUES (periode_start, periode_end, total_shu, 
            IF(modal_awal > 0, (total_shu - jasa_anggota) / modal_awal * 100, 0),
            IF(jasa_anggota > 0, jasa_anggota / total_shu * 100, 0),
            'calculated', NOW());
END//
DELIMITER ;
