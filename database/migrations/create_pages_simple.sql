-- Simple migration untuk unified system
-- Hanya create tables yang belum ada

-- Create pages table jika belum ada
CREATE TABLE IF NOT EXISTS `pages` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `page_key` varchar(100) NOT NULL COMMENT 'Unique page identifier',
    `title` varchar(100) NOT NULL COMMENT 'Page display title',
    `description` text DEFAULT NULL COMMENT 'Page description',
    `keywords` varchar(255) DEFAULT NULL COMMENT 'SEO keywords',
    `module_id` int(11) DEFAULT NULL COMMENT 'Reference to modules table',
    `breadcrumb_title` varchar(100) DEFAULT NULL COMMENT 'Title for breadcrumb',
    `layout` varchar(50) DEFAULT 'default' COMMENT 'Layout template',
    `show_header` tinyint(1) DEFAULT 1 COMMENT 'Show page header',
    `show_sidebar` tinyint(1) DEFAULT 1 COMMENT 'Show sidebar',
    `show_breadcrumb` tinyint(1) DEFAULT 1 COMMENT 'Show breadcrumb',
    `roles_required` varchar(255) DEFAULT NULL COMMENT 'Required roles (comma separated)',
    `meta_title` varchar(255) DEFAULT NULL COMMENT 'Meta title for SEO',
    `meta_description` text DEFAULT NULL COMMENT 'Meta description',
    `meta_keywords` varchar(255) DEFAULT NULL COMMENT 'Meta keywords',
    `meta_author` varchar(100) DEFAULT NULL COMMENT 'Meta author',
    `meta_robots` varchar(50) DEFAULT 'index,follow' COMMENT 'Meta robots',
    `actions` text DEFAULT NULL COMMENT 'Page actions (JSON)',
    `scripts` text DEFAULT NULL COMMENT 'Custom scripts (JSON)',
    `styles` text DEFAULT NULL COMMENT 'Custom styles (JSON)',
    `is_active` tinyint(1) DEFAULT 1 COMMENT 'Page status',
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `created_by` int(11) DEFAULT NULL,
    `updated_by` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_page_key` (`page_key`),
    KEY `idx_module_id` (`module_id`),
    KEY `idx_is_active` (`is_active`),
    KEY `idx_roles_required` (`roles_required`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Page configuration for unified system';

-- Create activity_logs table jika belum ada
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) DEFAULT NULL COMMENT 'User who performed action',
    `action` varchar(100) NOT NULL COMMENT 'Action performed',
    `description` text DEFAULT NULL COMMENT 'Action description',
    `table_name` varchar(50) DEFAULT NULL COMMENT 'Affected table',
    `record_id` int(11) DEFAULT NULL COMMENT 'Affected record ID',
    `data` json DEFAULT NULL COMMENT 'Action data (JSON)',
    `ip_address` varchar(45) DEFAULT NULL COMMENT 'User IP address',
    `user_agent` text DEFAULT NULL COMMENT 'User agent string',
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_action` (`action`),
    KEY `idx_table_name` (`table_name`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User activity logs for audit trail';

-- Insert default pages (ignore duplicates)
INSERT IGNORE INTO `pages` (`page_key`, `title`, `description`, `breadcrumb_title`, `roles_required`, `meta_title`, `meta_description`) VALUES
-- Dashboard
('dashboard', 'Dashboard', 'Halaman utama dashboard KSP Samosir', 'Dashboard', 'admin,staff,member,customer_service,invoice,accounting', 'Dashboard KSP Samosir', 'Dashboard sistem KSP Samosir untuk monitoring overview'),

-- Membership
('anggota', 'Anggota', 'Manajemen data anggota KSP', 'Anggota', 'admin,staff', 'Data Anggota KSP', 'Daftar data anggota KSP Samosir'),
('anggota/create', 'Tambah Anggota', 'Form tambah anggota baru', 'Tambah Anggota', 'admin,staff', 'Tambah Anggota Baru', 'Form pendaftaran anggota baru KSP'),
('anggota/edit', 'Edit Anggota', 'Edit data anggota', 'Edit Anggota', 'admin,staff', 'Edit Data Anggota', 'Form edit data anggota KSP'),

-- Savings
('simpanan', 'Simpanan', 'Manajemen simpanan anggota', 'Simpanan', 'admin,staff,member', 'Data Simpanan', 'Daftar simpanan anggota KSP'),
('simpanan/create', 'Tambah Simpanan', 'Form tambah simpanan', 'Tambah Simpanan', 'admin,staff', 'Tambah Simpanan', 'Form penambahan simpanan anggota'),

-- Loans
('pinjaman', 'Pinjaman', 'Manajemen pinjaman anggota', 'Pinjaman', 'admin,staff,member', 'Data Pinjaman', 'Daftar pinjaman anggota KSP'),
('pinjaman/create', 'Ajukan Pinjaman', 'Form ajukan pinjaman', 'Ajukan Pinjaman', 'admin,staff,member', 'Pengajuan Pinjaman', 'Form pengajuan pinjaman KSP'),

-- Accounting
('accounting', 'Akuntansi', 'Sistem akuntansi KSP', 'Akuntansi', 'admin,accounting', 'Sistem Akuntansi', 'Dashboard akuntansi KSP Samosir'),
('accounting/jurnal', 'Jurnal', 'Jurnal umum transaksi', 'Jurnal', 'admin,accounting', 'Jurnal Umum', 'Jurnal umum transaksi KSP'),
('accounting/neraca', 'Neraca', 'Laporan neraca keuangan', 'Neraca', 'admin,accounting', 'Laporan Neraca', 'Laporan neraca KSP Samosir'),
('accounting/laba_rugi', 'Laba Rugi', 'Laporan laba rugi', 'Laba Rugi', 'admin,accounting', 'Laporan Laba Rugi', 'Laporan laba rugi KSP'),

-- SHU
('shu', 'SHU', 'Sisa Hasil Usaha', 'SHU', 'admin,accounting', 'SHU KSP', 'Dashboard Sisa Hasil Usaha'),
('shu/calculate', 'Hitung SHU', 'Perhitungan SHU', 'Hitung SHU', 'admin,accounting', 'Perhitungan SHU', 'Kalkulasi Sisa Hasil Usaha'),
('shu/distribute', 'Distribusi SHU', 'Distribusi SHU ke anggota', 'Distribusi SHU', 'admin,accounting', 'Distribusi SHU', 'Bagi hasil Sisa Hasil Usaha'),
('shu/reports', 'Laporan SHU', 'Laporan SHU', 'Laporan SHU', 'admin,accounting', 'Laporan SHU', 'Laporan Sisa Hasil Usaha'),

-- Sales
('penjualan', 'Penjualan', 'Modul penjualan', 'Penjualan', 'admin,staff', 'Penjualan KSP', 'Dashboard penjualan KSP'),
('penjualan/promos', 'Promo', 'Manajemen promo', 'Promo', 'admin,staff', 'Promo Penjualan', 'Daftar promo penjualan'),
('penjualan/commissions', 'Komisi', 'Manajemen komisi', 'Komisi', 'admin,staff', 'Komisi Penjualan', 'Daftar komisi penjualan'),

-- Service
('customer_service', 'Customer Service', 'Layanan pelanggan', 'Customer Service', 'admin,customer_service', 'Customer Service', 'Dashboard layanan pelanggan'),
('customer_service/tickets', 'Tickets', 'Manajemen tickets', 'Tickets', 'admin,customer_service', 'Customer Service Tickets', 'Daftar tickets pelanggan'),

-- Billing
('invoice', 'Invoice', 'Manajemen invoice', 'Invoice', 'admin,invoice', 'Invoice KSP', 'Dashboard invoice KSP'),
('invoice/customer', 'Invoice Customer', 'Invoice customer', 'Invoice Customer', 'admin,invoice', 'Invoice Customer', 'Daftar invoice customer'),
('invoice/supplier', 'Invoice Supplier', 'Invoice supplier', 'Invoice Supplier', 'admin,invoice', 'Invoice Supplier', 'Daftar invoice supplier'),

-- System
('settings', 'Pengaturan', 'Pengaturan sistem', 'Pengaturan', 'admin', 'Pengaturan KSP', 'Pengaturan sistem KSP Samosir'),

-- Reports
('laporan', 'Laporan', 'Laporan sistem', 'Laporan', 'admin,staff', 'Laporan KSP', 'Dashboard laporan KSP'),

-- Profile
('profile', 'Profil Saya', 'Profil pengguna', 'Profil Saya', 'admin,staff,member,customer_service,invoice,accounting', 'Profil Pengguna', 'Profil pengguna KSP');

COMMIT;
