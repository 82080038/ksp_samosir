-- Accounting System Tables for KSP Samosir
-- Chart of Accounts
CREATE TABLE IF NOT EXISTS coa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_perkiraan VARCHAR(20) NOT NULL UNIQUE,
    nama_perkiraan VARCHAR(100) NOT NULL,
    tipe_akun ENUM('Aktiva', 'Kewajiban', 'Ekuitas', 'Pendapatan', 'Beban') NOT NULL,
    level_akun INT DEFAULT 1,
    akun_induk INT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (akun_induk) REFERENCES coa(id) ON DELETE SET NULL,
    INDEX idx_kode (kode_perkiraan),
    INDEX idx_tipe (tipe_akun)
);

-- General Journal
CREATE TABLE IF NOT EXISTS jurnal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tanggal_jurnal DATE NOT NULL,
    nomor_jurnal VARCHAR(50) NOT NULL UNIQUE,
    keterangan TEXT,
    status_jurnal ENUM('Draft', 'Posted', 'Cancelled') DEFAULT 'Posted',
    total_debet DECIMAL(15,2) DEFAULT 0,
    total_kredit DECIMAL(15,2) DEFAULT 0,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tanggal (tanggal_jurnal),
    INDEX idx_nomor (nomor_jurnal)
);

-- Journal Details
CREATE TABLE IF NOT EXISTS detail_jurnal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jurnal_id INT NOT NULL,
    nama_perkiraan_id INT NOT NULL,
    debet DECIMAL(15,2) DEFAULT 0,
    kredit DECIMAL(15,2) DEFAULT 0,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (jurnal_id) REFERENCES jurnal(id) ON DELETE CASCADE,
    FOREIGN KEY (nama_perkiraan_id) REFERENCES coa(id) ON DELETE RESTRICT,
    INDEX idx_jurnal (jurnal_id),
    INDEX idx_perkiraan (nama_perkiraan_id)
);

-- Cash Book
CREATE TABLE IF NOT EXISTS buku_kas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tanggal_transaksi DATE NOT NULL,
    jenis_transaksi ENUM('Masuk', 'Keluar') NOT NULL,
    keterangan TEXT NOT NULL,
    jumlah DECIMAL(15,2) NOT NULL,
    saldo_akhir DECIMAL(15,2) NOT NULL,
    referensi_type VARCHAR(50) NULL, -- 'jurnal', 'simpanan', 'pinjaman', etc
    referensi_id INT NULL,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tanggal (tanggal_transaksi),
    INDEX idx_jenis (jenis_transaksi)
);

-- Bank Book
CREATE TABLE IF NOT EXISTS buku_bank (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bank_id INT NOT NULL,
    tanggal_transaksi DATE NOT NULL,
    jenis_transaksi ENUM('Masuk', 'Keluar') NOT NULL,
    keterangan TEXT NOT NULL,
    jumlah DECIMAL(15,2) NOT NULL,
    saldo_akhir DECIMAL(15,2) NOT NULL,
    referensi_type VARCHAR(50) NULL,
    referensi_id INT NULL,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tanggal (tanggal_transaksi),
    INDEX idx_bank (bank_id)
);

-- Bank Accounts
CREATE TABLE IF NOT EXISTS bank_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_bank VARCHAR(100) NOT NULL,
    nomor_rekening VARCHAR(50) NOT NULL,
    nama_rekening VARCHAR(100) NOT NULL,
    saldo_awal DECIMAL(15,2) DEFAULT 0,
    saldo_akhir DECIMAL(15,2) DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_bank (nama_bank)
);

-- Insert Default Chart of Accounts
INSERT INTO coa (kode_perkiraan, nama_perkiraan, tipe_akun, level_akun) VALUES
-- Aktiva Lancar
('100', 'KAS', 'Aktiva', 1),
('101', 'KAS TUNAI', 'Aktiva', 2),
('102', 'KAS DI BANK', 'Aktiva', 2),
('110', 'PIUTANG ANGGOTA', 'Aktiva', 1),
('111', 'PIUTANG PINJAMAN', 'Aktiva', 2),
('112', 'PIUTANG LAINNYA', 'Aktiva', 2),
('120', 'PERSEDIAAN', 'Aktiva', 1),
('121', 'BARANG DAGANG', 'Aktiva', 2),
-- Aktiva Tetap
('200', 'AKTIVA TETAP', 'Aktiva', 1),
('210', 'TANAH', 'Aktiva', 2),
('220', 'GEDUNG', 'Aktiva', 2),
('230', 'KENDARAAN', 'Aktiva', 2),
('240', 'PERALATAN', 'Aktiva', 2),
('250', 'AKUMULASI PENYUSUTAN', 'Aktiva', 2),
-- Kewajiban
('300', 'KEWAJIBAN LANCAR', 'Kewajiban', 1),
('310', 'SIMPANAN ANGGOTA', 'Kewajiban', 2),
('311', 'HUTANG USAHA', 'Kewajiban', 2),
('312', 'HUTANG LAINNYA', 'Kewajiban', 2),
-- Ekuitas
('400', 'EKUITAS', 'Ekuitas', 1),
('410', 'MODAL SIMPANAN POKOK', 'Ekuitas', 2),
('420', 'MODAL BERLAIN', 'Ekuitas', 2),
('430', 'SALDO LABA', 'Ekuitas', 2),
-- Pendapatan
('500', 'PENDAPATAN USAHA', 'Pendapatan', 1),
('510', 'PENDAPATAN JASA SIMPINAN', 'Pendapatan', 2),
('511', 'PENDAPATAN BUNGA PINJAMAN', 'Pendapatan', 2),
('512', 'PENDAPATAN PENJUALAN', 'Pendapatan', 2),
('513', 'PENDAPATAN LAINNYA', 'Pendapatan', 2),
-- Beban
('600', 'BEBAN USAHA', 'Beban', 1),
('610', 'BEBAN BUNGA SIMPANAN', 'Beban', 2),
('620', 'BEBAN OPERASIONAL', 'Beban', 2),
('621', 'BEBAN GAJI', 'Beban', 3),
('622', 'BEBAN LISTRIK & AIR', 'Beban', 3),
('623', 'BEBAN SEWA', 'Beban', 3),
('624', 'BEBAN ATK', 'Beban', 3),
('630', 'BEBAN PENYUSUTAN', 'Beban', 2),
('640', 'BEBAN LAINNYA', 'Beban', 2);

-- Insert Default Bank Account
INSERT INTO bank_accounts (nama_bank, nomor_rekening, nama_rekening, saldo_awal, saldo_akhir) VALUES
('Bank BCA', '1234567890', 'KSP Samosir', 0, 0),
('Bank Mandiri', '0987654321', 'KSP Samosir', 0, 0);
