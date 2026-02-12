-- KSP Samosir Database Schema
-- Aplikasi Koperasi Simpan Pinjam & Pemasaran

CREATE DATABASE IF NOT EXISTS ksp_samosir;
USE ksp_samosir;

-- Users Table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'staff', 'member') DEFAULT 'member',
    mfa_enabled TINYINT(1) DEFAULT 0,
    mfa_secret VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Anggota Table
CREATE TABLE anggota (
    id INT PRIMARY KEY AUTO_INCREMENT,
    no_anggota VARCHAR(20) UNIQUE NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    nik VARCHAR(20) UNIQUE NOT NULL,
    tempat_lahir VARCHAR(50),
    tanggal_lahir DATE,
    jenis_kelamin ENUM('L', 'P'),
    alamat TEXT,
    no_hp VARCHAR(20),
    email VARCHAR(100),
    pekerjaan VARCHAR(50),
    pendapatan_bulanan DECIMAL(12,2),
    tanggal_gabung DATE,
    status ENUM('aktif', 'nonaktif', 'keluar') DEFAULT 'aktif',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Jenis Simpanan
CREATE TABLE jenis_simpanan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_simpanan VARCHAR(50) NOT NULL,
    kode VARCHAR(10) UNIQUE NOT NULL,
    jenis ENUM('wajib', 'sukarela', 'berjangka') NOT NULL,
    minimal_setoran DECIMAL(12,2) DEFAULT 0,
    bunga_pertahun DECIMAL(5,2) DEFAULT 0,
    periode_bulan INT DEFAULT 1,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Simpanan
CREATE TABLE simpanan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    anggota_id INT NOT NULL,
    jenis_simpanan_id INT NOT NULL,
    no_rekening VARCHAR(30) UNIQUE NOT NULL,
    saldo DECIMAL(15,2) DEFAULT 0,
    status ENUM('aktif', 'ditutup', 'dibekukan') DEFAULT 'aktif',
    tanggal_buka DATE,
    tanggal_tutup DATE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (anggota_id) REFERENCES anggota(id),
    FOREIGN KEY (jenis_simpanan_id) REFERENCES jenis_simpanan(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Transaksi Simpanan
CREATE TABLE transaksi_simpanan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    simpanan_id INT NOT NULL,
    jenis_transaksi ENUM('setoran', 'penarikan', 'bunga') NOT NULL,
    jumlah DECIMAL(15,2) NOT NULL,
    saldo_sebelum DECIMAL(15,2) NOT NULL,
    saldo_setelah DECIMAL(15,2) NOT NULL,
    keterangan TEXT,
    tanggal_transaksi DATETIME DEFAULT CURRENT_TIMESTAMP,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (simpanan_id) REFERENCES simpanan(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Jenis Pinjaman
CREATE TABLE jenis_pinjaman (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_pinjaman VARCHAR(50) NOT NULL,
    kode VARCHAR(10) UNIQUE NOT NULL,
    plafond_maksimal DECIMAL(15,2) DEFAULT 0,
    bunga_pertahun DECIMAL(5,2) DEFAULT 0,
    tenor_maksimal INT DEFAULT 12,
    denda_persen DECIMAL(5,2) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Pinjaman
CREATE TABLE pinjaman (
    id INT PRIMARY KEY AUTO_INCREMENT,
    anggota_id INT NOT NULL,
    jenis_pinjaman_id INT NOT NULL,
    no_pinjaman VARCHAR(30) UNIQUE NOT NULL,
    jumlah_pinjaman DECIMAL(15,2) NOT NULL,
    bunga_persen DECIMAL(5,2) NOT NULL,
    tenor_bulan INT NOT NULL,
    angsuran_pokok DECIMAL(15,2) NOT NULL,
    angsuran_bunga DECIMAL(15,2) NOT NULL,
    total_angsuran DECIMAL(15,2) NOT NULL,
    status ENUM('pengajuan', 'disetujui', 'dicairkan', 'lunas', 'ditolak') DEFAULT 'pengajuan',
    tanggal_pengajuan DATE,
    tanggal_disetujui DATE,
    tanggal_pencairan DATE,
    tanggal_jatuh_tempo DATE,
    tujuan_pinjaman TEXT,
    catatan TEXT,
    approved_by INT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (anggota_id) REFERENCES anggota(id),
    FOREIGN KEY (jenis_pinjaman_id) REFERENCES jenis_pinjaman(id),
    FOREIGN KEY (approved_by) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Angsuran
CREATE TABLE angsuran (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pinjaman_id INT NOT NULL,
    no_angsuran INT NOT NULL,
    jumlah_angsuran DECIMAL(15,2) NOT NULL,
    pokok DECIMAL(15,2) NOT NULL,
    bunga DECIMAL(15,2) NOT NULL,
    denda DECIMAL(15,2) DEFAULT 0,
    total_bayar DECIMAL(15,2) NOT NULL,
    tanggal_jatuh_tempo DATE NOT NULL,
    tanggal_bayar DATETIME,
    status ENUM('belum_bayar', 'terlambat', 'lunas') DEFAULT 'belum_bayar',
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pinjaman_id) REFERENCES pinjaman(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Kategori Produk
CREATE TABLE kategori_produk (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_kategori VARCHAR(50) NOT NULL,
    kode VARCHAR(10) UNIQUE NOT NULL,
    deskripsi TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Produk
CREATE TABLE produk (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_produk VARCHAR(30) UNIQUE NOT NULL,
    nama_produk VARCHAR(100) NOT NULL,
    kategori_id INT NOT NULL,
    deskripsi TEXT,
    harga_beli DECIMAL(12,2) DEFAULT 0,
    harga_jual DECIMAL(12,2) DEFAULT 0,
    stok INT DEFAULT 0,
    stok_minimal INT DEFAULT 0,
    satuan VARCHAR(20) DEFAULT 'pcs',
    gambar VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori_produk(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Pelanggan
CREATE TABLE pelanggan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_pelanggan VARCHAR(20) UNIQUE NOT NULL,
    nama_pelanggan VARCHAR(100) NOT NULL,
    alamat TEXT,
    no_hp VARCHAR(20),
    email VARCHAR(100),
    jenis_pelanggan ENUM('member', 'non_member') DEFAULT 'non_member',
    anggota_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (anggota_id) REFERENCES anggota(id)
);

-- Penjualan
CREATE TABLE penjualan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    no_faktur VARCHAR(30) UNIQUE NOT NULL,
    pelanggan_id INT,
    total_harga DECIMAL(15,2) NOT NULL,
    total_bayar DECIMAL(15,2) DEFAULT 0,
    kembalian DECIMAL(15,2) DEFAULT 0,
    status_pembayaran ENUM('lunas', 'belum_lunas', 'cicil') DEFAULT 'belum_lunas',
    metode_pembayaran ENUM('cash', 'transfer', 'debit', 'kredit') DEFAULT 'cash',
    tanggal_penjualan DATETIME DEFAULT CURRENT_TIMESTAMP,
    user_id INT NOT NULL,
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Detail Penjualan
CREATE TABLE detail_penjualan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    penjualan_id INT NOT NULL,
    produk_id INT NOT NULL,
    jumlah INT NOT NULL,
    harga_satuan DECIMAL(12,2) NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (penjualan_id) REFERENCES penjualan(id),
    FOREIGN KEY (produk_id) REFERENCES produk(id)
);

-- COA (Chart of Accounts)
CREATE TABLE coa (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_coa VARCHAR(20) UNIQUE NOT NULL,
    nama_coa VARCHAR(100) NOT NULL,
    tipe ENUM('debit', 'kredit') NOT NULL,
    level INT DEFAULT 1,
    parent_id INT NULL,
    saldo_awal DECIMAL(15,2) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES coa(id)
);

-- Jurnal Akuntansi
CREATE TABLE jurnal (
    id INT PRIMARY KEY AUTO_INCREMENT,
    no_jurnal VARCHAR(30) UNIQUE NOT NULL,
    tanggal_jurnal DATE NOT NULL,
    keterangan TEXT,
    total_debit DECIMAL(15,2) DEFAULT 0,
    total_kredit DECIMAL(15,2) DEFAULT 0,
    status ENUM('draft', 'posted') DEFAULT 'draft',
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Detail Jurnal
CREATE TABLE detail_jurnal (
    id INT PRIMARY KEY AUTO_INCREMENT,
    jurnal_id INT NOT NULL,
    coa_id INT NOT NULL,
    debit DECIMAL(15,2) DEFAULT 0,
    kredit DECIMAL(15,2) DEFAULT 0,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (jurnal_id) REFERENCES jurnal(id),
    FOREIGN KEY (coa_id) REFERENCES coa(id)
);

-- Settings
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('text', 'number', 'boolean', 'json') DEFAULT 'text',
    deskripsi TEXT,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

-- Logs
CREATE TABLE logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_data JSON,
    new_data JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert Default Data
INSERT INTO users (username, password, email, full_name, role) VALUES
('admin', '$2y$10$puqQ47Z6i/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m/il.UeIdYvu', 'admin@ksp_samosir.com', 'Administrator', 'admin');

INSERT INTO coa (kode_coa, nama_coa, tipe, level) VALUES
('1', 'AKTIVA', 'debit', 1),
('11', 'AKTIVA LANCAR', 'debit', 2),
('111', 'Kas', 'debit', 3),
('112', 'Bank', 'debit', 3),
('12', 'AKTIVA TETAP', 'debit', 2),
('121', 'Tanah & Bangunan', 'debit', 3),
('2', 'PASSIVA', 'kredit', 1),
('21', 'KEWAJIBAN LANCAR', 'kredit', 2),
('211', 'Simpanan Anggota', 'kredit', 3),
('3', 'EKUITAS', 'kredit', 1),
('31', 'MODAL', 'kredit', 2),
('4', 'PENDAPATAN', 'kredit', 1),
('5', 'BEBAN', 'debit', 1);

INSERT INTO settings (setting_key, setting_value, setting_type, deskripsi) VALUES
('app_name', 'KSP Samosir', 'text', 'Nama Aplikasi'),
('app_version', '1.0.0', 'text', 'Versi Aplikasi'),
('bunga_simpanan_wajib', '3.00', 'number', 'Bunga Simpanan Wajib (%)'),
('bunga_simpanan_sukarela', '4.00', 'number', 'Bunga Simpanan Sukarela (%)'),
('denda_keterlambatan', '2.00', 'number', 'Denda Keterlambatan (%)');

-- Create Indexes
CREATE INDEX idx_anggota_no_anggota ON anggota(no_anggota);
CREATE INDEX idx_simpanan_no_rekening ON simpanan(no_rekening);
CREATE INDEX idx_pinjaman_no_pinjaman ON pinjaman(no_pinjaman);
CREATE INDEX idx_produk_kode_produk ON produk(kode_produk);
CREATE INDEX idx_penjualan_no_faktur ON penjualan(no_faktur);
CREATE INDEX idx_jurnal_no_jurnal ON jurnal(no_jurnal);
CREATE INDEX idx_logs_created_at ON logs(created_at);
-- FK/lookup indexes
CREATE INDEX idx_anggota_created_by ON anggota(created_by);
CREATE INDEX idx_simpanan_anggota ON simpanan(anggota_id);
CREATE INDEX idx_simpanan_jenis ON simpanan(jenis_simpanan_id);
CREATE INDEX idx_trans_simpanan_simpanan ON transaksi_simpanan(simpanan_id);
CREATE INDEX idx_trans_simpanan_user ON transaksi_simpanan(user_id);
CREATE INDEX idx_trans_simpanan_tanggal ON transaksi_simpanan(tanggal_transaksi);
CREATE INDEX idx_pinjaman_anggota ON pinjaman(anggota_id);
CREATE INDEX idx_pinjaman_jenis ON pinjaman(jenis_pinjaman_id);
CREATE INDEX idx_pinjaman_status ON pinjaman(status);
CREATE INDEX idx_angsuran_pinjaman ON angsuran(pinjaman_id);
CREATE INDEX idx_angsuran_status ON angsuran(status);
CREATE INDEX idx_angsuran_jatuh_tempo ON angsuran(tanggal_jatuh_tempo);
CREATE INDEX idx_produk_kategori ON produk(kategori_id);
CREATE INDEX idx_pelanggan_kode ON pelanggan(kode_pelanggan);
CREATE INDEX idx_penjualan_pelanggan ON penjualan(pelanggan_id);
CREATE INDEX idx_penjualan_user ON penjualan(user_id);
CREATE INDEX idx_penjualan_tanggal ON penjualan(tanggal_penjualan);
CREATE INDEX idx_detail_penjualan_penjualan ON detail_penjualan(penjualan_id);
CREATE INDEX idx_detail_penjualan_produk ON detail_penjualan(produk_id);
CREATE INDEX idx_jurnal_user ON jurnal(user_id);
CREATE INDEX idx_detail_jurnal_coa ON detail_jurnal(coa_id);
CREATE INDEX idx_settings_key ON settings(setting_key);

-- Create Triggers
DELIMITER //
CREATE TRIGGER update_saldo_simpanan 
AFTER INSERT ON transaksi_simpanan
FOR EACH ROW
BEGIN
    UPDATE simpanan 
    SET saldo = NEW.saldo_setelah,
        updated_at = NOW()
    WHERE id = NEW.simpanan_id;
END//

CREATE TRIGGER update_stok_produk 
AFTER INSERT ON detail_penjualan
FOR EACH NEW
BEGIN
    UPDATE produk 
    SET stok = stok - NEW.jumlah,
        updated_at = NOW()
    WHERE id = NEW.produk_id;
END//
DELIMITER ;
