-- KSP SAMOSIR - MULTI-DATABASE ARCHITECTURE SETUP
-- Rekomendasi 5 Database untuk performa dan scalability optimal

-- =====================================================
-- DATABASE 1: ksp_samosir_core (Database Utama Koperasi)
-- =====================================================
CREATE DATABASE IF NOT EXISTS ksp_samosir_core CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE ksp_samosir_core;

-- Core tables untuk koperasi
-- Anggota & User Management
CREATE TABLE IF NOT EXISTS anggota (
    id INT PRIMARY KEY AUTO_INCREMENT,
    no_anggota VARCHAR(20) UNIQUE NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    nik VARCHAR(16) UNIQUE NOT NULL,
    tempat_lahir VARCHAR(50),
    tanggal_lahir DATE,
    jenis_kelamin ENUM('Laki-laki', 'Perempuan'),
    alamat TEXT,
    no_hp VARCHAR(15),
    email VARCHAR(100),
    pekerjaan VARCHAR(100),
    pendapatan_bulanan DECIMAL(12,2),
    tanggal_gabung DATE DEFAULT CURRENT_DATE,
    status ENUM('aktif', 'non-aktif', 'keluar') DEFAULT 'aktif',
    registration_source ENUM('manual', 'digital') DEFAULT 'manual',
    registration_ip VARCHAR(45),
    digital_signature JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_no_anggota (no_anggota),
    INDEX idx_nik (nik),
    INDEX idx_status (status),
    INDEX idx_tanggal_gabung (tanggal_gabung)
);

CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    no_hp VARCHAR(15),
    anggota_id INT,
    role ENUM('admin', 'pengurus', 'anggota', 'staff') DEFAULT 'anggota',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (anggota_id) REFERENCES anggota(id) ON DELETE SET NULL,
    INDEX idx_username (username),
    INDEX idx_role (role),
    INDEX idx_status (status)
);

CREATE TABLE IF NOT EXISTS roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    permissions JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS user_roles (
    user_id INT,
    role_id INT,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

-- Simpanan & Pinjaman
CREATE TABLE IF NOT EXISTS jenis_simpanan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode VARCHAR(10) UNIQUE NOT NULL,
    nama_simpanan VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    minimal_setoran DECIMAL(12,2) DEFAULT 0,
    maksimal_setoran DECIMAL(12,2),
    bunga_tahunan DECIMAL(5,2) DEFAULT 0,
    status ENUM('aktif', 'non-aktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS simpanan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    anggota_id INT NOT NULL,
    jenis_simpanan_id INT NOT NULL,
    no_rekening VARCHAR(30) UNIQUE NOT NULL,
    saldo DECIMAL(15,2) DEFAULT 0,
    status ENUM('aktif', 'ditutup', 'dibekukan') DEFAULT 'aktif',
    tanggal_buka DATE DEFAULT CURRENT_DATE,
    tanggal_tutup DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (anggota_id) REFERENCES anggota(id) ON DELETE CASCADE,
    FOREIGN KEY (jenis_simpanan_id) REFERENCES jenis_simpanan(id),
    INDEX idx_anggota (anggota_id),
    INDEX idx_no_rekening (no_rekening),
    INDEX idx_status (status)
);

CREATE TABLE IF NOT EXISTS jenis_pinjaman (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode VARCHAR(10) UNIQUE NOT NULL,
    nama_pinjaman VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    minimal_pinjaman DECIMAL(12,2),
    maksimal_pinjaman DECIMAL(12,2),
    bunga_tahunan DECIMAL(5,2),
    tenor_minimal INT,
    tenor_maksimal INT,
    persetujuan_otomatis BOOLEAN DEFAULT FALSE,
    status ENUM('aktif', 'non-aktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS pinjaman (
    id INT PRIMARY KEY AUTO_INCREMENT,
    anggota_id INT NOT NULL,
    jenis_pinjaman_id INT NOT NULL,
    no_pinjaman VARCHAR(30) UNIQUE NOT NULL,
    jumlah_pinjaman DECIMAL(15,2) NOT NULL,
    bunga_tahunan DECIMAL(5,2),
    tenor_bulan INT NOT NULL,
    angsuran_per_bulan DECIMAL(12,2),
    total_bunga DECIMAL(12,2),
    total_pengembalian DECIMAL(12,2),
    tanggal_pengajuan DATE DEFAULT CURRENT_DATE,
    tanggal_disetujui DATE NULL,
    tanggal_cair DATE NULL,
    tanggal_jatuh_tempo DATE,
    status ENUM('pengajuan', 'disetujui', 'dicairkan', 'lunas', 'ditolak') DEFAULT 'pengajuan',
    alasan_penolakan TEXT,
    approved_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (anggota_id) REFERENCES anggota(id) ON DELETE CASCADE,
    FOREIGN KEY (jenis_pinjaman_id) REFERENCES jenis_pinjaman(id),
    FOREIGN KEY (approved_by) REFERENCES users(id),
    INDEX idx_anggota (anggota_id),
    INDEX idx_no_pinjaman (no_pinjaman),
    INDEX idx_status (status),
    INDEX idx_tanggal_pengajuan (tanggal_pengajuan)
);

CREATE TABLE IF NOT EXISTS angsuran (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pinjaman_id INT NOT NULL,
    no_angsuran INT NOT NULL,
    jumlah_angsuran DECIMAL(12,2) NOT NULL,
    pokok DECIMAL(12,2) NOT NULL,
    bunga DECIMAL(12,2) NOT NULL,
    jatuh_tempo DATE NOT NULL,
    tanggal_bayar DATE NULL,
    jumlah_bayar DECIMAL(12,2) DEFAULT 0,
    sisa_pinjaman DECIMAL(12,2),
    status ENUM('belum_bayar', 'sebagian', 'lunas', 'terlambat') DEFAULT 'belum_bayar',
    denda DECIMAL(12,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pinjaman_id) REFERENCES pinjaman(id) ON DELETE CASCADE,
    INDEX idx_pinjaman (pinjaman_id),
    INDEX idx_jatuh_tempo (jatuh_tempo),
    INDEX idx_status (status)
);

-- Transaksi
CREATE TABLE IF NOT EXISTS transaksi_simpanan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    simpanan_id INT NOT NULL,
    jenis_transaksi ENUM('setoran', 'penarikan') NOT NULL,
    jumlah DECIMAL(12,2) NOT NULL,
    saldo_sebelum DECIMAL(12,2),
    saldo_sesudah DECIMAL(12,2),
    tanggal_transaksi DATE DEFAULT CURRENT_DATE,
    keterangan TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (simpanan_id) REFERENCES simpanan(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_simpanan (simpanan_id),
    INDEX idx_tanggal (tanggal_transaksi),
    INDEX idx_jenis (jenis_transaksi)
);

CREATE TABLE IF NOT EXISTS transaksi_pinjaman (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pinjaman_id INT NOT NULL,
    angsuran_id INT,
    jenis_transaksi ENUM('pembayaran_angsuran', 'pelunasan', 'denda') NOT NULL,
    jumlah DECIMAL(12,2) NOT NULL,
    tanggal_transaksi DATE DEFAULT CURRENT_DATE,
    keterangan TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pinjaman_id) REFERENCES pinjaman(id) ON DELETE CASCADE,
    FOREIGN KEY (angsuran_id) REFERENCES angsuran(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_pinjaman (pinjaman_id),
    INDEX idx_tanggal (tanggal_transaksi)
);

-- Settings & Configuration
CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('text', 'number', 'boolean', 'json') DEFAULT 'text',
    deskripsi TEXT,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id),
    INDEX idx_setting_key (setting_key)
);

-- =====================================================
-- DATABASE 2: ksp_samosir_registration (Pendaftaran Digital)
-- =====================================================
CREATE DATABASE IF NOT EXISTS ksp_samosir_registration CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE ksp_samosir_registration;

CREATE TABLE IF NOT EXISTS registration_forms (
    id INT PRIMARY KEY AUTO_INCREMENT,
    form_name VARCHAR(100) NOT NULL,
    form_type ENUM('anggota_baru', 'pengurus', 'calon_anggota') DEFAULT 'anggota_baru',
    form_template TEXT NOT NULL,
    form_fields JSON NOT NULL,
    signature_required BOOLEAN DEFAULT TRUE,
    photo_required BOOLEAN DEFAULT TRUE,
    ktp_required BOOLEAN DEFAULT TRUE,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS registration_submissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    form_id INT NOT NULL,
    submission_token VARCHAR(100) UNIQUE NOT NULL,
    personal_data JSON NOT NULL,
    address_data JSON,
    financial_data JSON,
    document_data JSON,
    signature_data JSON,
    photo_data JSON,
    status ENUM('draft', 'submitted', 'review', 'approved', 'rejected', 'completed') DEFAULT 'draft',
    submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    review_date TIMESTAMP NULL,
    approved_date TIMESTAMP NULL,
    approved_by INT,
    rejection_reason TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (form_id) REFERENCES registration_forms(id),
    INDEX idx_status (status),
    INDEX idx_submission_date (submission_date),
    INDEX idx_token (submission_token)
);

CREATE TABLE IF NOT EXISTS digital_signatures (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    document_type ENUM('registration_form', 'loan_agreement', 'membership_card') NOT NULL,
    document_id INT NOT NULL,
    signature_image VARCHAR(255),
    signature_coordinates JSON,
    device_info JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_verified BOOLEAN DEFAULT FALSE,
    verification_method ENUM('biometric', 'otp', 'admin') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_document (document_type, document_id)
);

CREATE TABLE IF NOT EXISTS registration_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    submission_id INT,
    action ENUM('created', 'updated', 'submitted', 'reviewed', 'approved', 'rejected', 'printed'),
    actor_id INT,
    actor_type ENUM('user', 'admin', 'system'),
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (submission_id) REFERENCES registration_submissions(id),
    INDEX idx_submission (submission_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);

-- =====================================================
-- DATABASE 3: ksp_samosir_analytics (Analytics & Reporting)
-- =====================================================
CREATE DATABASE IF NOT EXISTS ksp_samosir_analytics CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE ksp_samosir_analytics;

CREATE TABLE IF NOT EXISTS analytics_events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_type VARCHAR(50) NOT NULL,
    event_category VARCHAR(50),
    user_id INT,
    session_id VARCHAR(100),
    properties JSON,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    INDEX idx_event_type (event_type),
    INDEX idx_timestamp (timestamp),
    INDEX idx_user (user_id)
);

CREATE TABLE IF NOT EXISTS report_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    template_name VARCHAR(100) NOT NULL,
    report_type ENUM('financial', 'membership', 'loan', 'savings', 'custom') NOT NULL,
    query_sql TEXT,
    parameters JSON,
    schedule_config JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS report_instances (
    id INT PRIMARY KEY AUTO_INCREMENT,
    template_id INT NOT NULL,
    report_name VARCHAR(100),
    parameters JSON,
    generated_data JSON,
    file_path VARCHAR(255),
    status ENUM('generating', 'completed', 'failed') DEFAULT 'generating',
    generated_at TIMESTAMP NULL,
    expires_at TIMESTAMP,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (template_id) REFERENCES report_templates(id),
    INDEX idx_template (template_id),
    INDEX idx_status (status),
    INDEX idx_generated_at (generated_at)
);

CREATE TABLE IF NOT EXISTS dashboard_widgets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    widget_name VARCHAR(100) NOT NULL,
    widget_type ENUM('chart', 'metric', 'table', 'custom') NOT NULL,
    data_source JSON,
    config JSON,
    position JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- DATABASE 4: ksp_samosir_business (Business Modules)
-- =====================================================
CREATE DATABASE IF NOT EXISTS ksp_samosir_business CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE ksp_samosir_business;

-- Agricultural Module
CREATE TABLE IF NOT EXISTS ksp_agricultural_tanaman (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_tanaman VARCHAR(100) NOT NULL,
    jenis_tanaman ENUM('padi', 'palawija', 'sayuran', 'buah', 'lainnya') NOT NULL,
    masa_tanam INT,
    perkiraan_has DECIMAL(10,2),
    harga_per_kg DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS ksp_agricultural_lahan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    anggota_id INT NOT NULL,
    luas_lahan DECIMAL(8,2) NOT NULL,
    lokasi TEXT,
    jenis_tanah ENUM('sawah', 'tegalan', 'ladang', 'kebun') NOT NULL,
    status_kepemilikan ENUM('milik', 'sewa', 'bagi_hasil') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_anggota (anggota_id)
);

CREATE TABLE IF NOT EXISTS ksp_agricultural_planning (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lahan_id INT NOT NULL,
    tanaman_id INT NOT NULL,
    rencana_tanam DATE NOT NULL,
    rencana_panen DATE,
    estimasi_hasil DECIMAL(10,2),
    status ENUM('rencana', 'proses', 'panen', 'selesai') DEFAULT 'rencana',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lahan_id) REFERENCES ksp_agricultural_lahan(id),
    FOREIGN KEY (tanaman_id) REFERENCES ksp_agricultural_tanaman(id),
    INDEX idx_lahan (lahan_id),
    INDEX idx_status (status)
);

-- Multi-Jenis Koperasi Module
CREATE TABLE IF NOT EXISTS koperasi_jenis (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_jenis VARCHAR(100) NOT NULL,
    kode_jenis VARCHAR(10) UNIQUE NOT NULL,
    deskripsi TEXT,
    modul_aktif JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS koperasi_modul (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_modul VARCHAR(100) NOT NULL,
    kode_modul VARCHAR(10) UNIQUE NOT NULL,
    deskripsi TEXT,
    konfigurasi JSON,
    status ENUM('aktif', 'non-aktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- DATABASE 5: ksp_samosir_system (System & Infrastructure)
-- =====================================================
CREATE DATABASE IF NOT EXISTS ksp_samosir_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE ksp_samosir_system;

CREATE TABLE IF NOT EXISTS system_configs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    config_key VARCHAR(100) UNIQUE NOT NULL,
    config_value TEXT,
    config_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    is_encrypted BOOLEAN DEFAULT FALSE,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_config_key (config_key)
);

CREATE TABLE IF NOT EXISTS user_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    session_id VARCHAR(128) UNIQUE NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_session_id (session_id),
    INDEX idx_expires_at (expires_at)
);

CREATE TABLE IF NOT EXISTS notification_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    notification_type ENUM('email', 'sms', 'whatsapp', 'push', 'in_app') NOT NULL,
    recipient VARCHAR(255) NOT NULL,
    subject VARCHAR(255),
    message TEXT,
    status ENUM('pending', 'sent', 'delivered', 'failed') DEFAULT 'pending',
    sent_at TIMESTAMP NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_type (notification_type)
);

CREATE TABLE IF NOT EXISTS audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(100),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_table (table_name),
    INDEX idx_created_at (created_at)
);

SELECT 'MULTI-DATABASE ARCHITECTURE SETUP COMPLETED!' as status;
