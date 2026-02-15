-- KSP Unified System Migration
-- Migration untuk sistem terpadu KSP Samosir v2.0
-- 
-- Tables yang dibutuhkan:
-- 1. pages - konfigurasi halaman
-- 2. modules - grouping halaman
-- 3. activity_logs - log aktivitas user
-- 4. navigation - menu navigasi (sudah ada sebelumnya)
-- 5. settings - pengaturan sistem (sudah ada sebelumnya)

-- Create modules table
CREATE TABLE IF NOT EXISTS `modules` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `module_key` varchar(50) NOT NULL COMMENT 'Unique module identifier',
    `title` varchar(100) NOT NULL COMMENT 'Module display title',
    `description` text DEFAULT NULL COMMENT 'Module description',
    `icon_class` varchar(50) DEFAULT NULL COMMENT 'Bootstrap icon class',
    `sort_order` int(11) DEFAULT 0 COMMENT 'Display order',
    `is_active` tinyint(1) DEFAULT 1 COMMENT 'Module status',
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_module_key` (`module_key`),
    KEY `idx_is_active` (`is_active`),
    KEY `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Module configuration for grouping pages';

-- Create pages table
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
    KEY `idx_roles_required` (`roles_required`),
    CONSTRAINT `fk_pages_module` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Page configuration for unified system';

-- Create activity_logs table
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

-- Insert default modules
INSERT INTO `modules` (`module_key`, `title`, `description`, `icon_class`, `sort_order`, `is_active`) VALUES
('dashboard', 'Dashboard', 'Halaman utama dan overview sistem', 'bi-speedometer2', 1, 1),
('membership', 'Keanggotaan', 'Manajemen data anggota KSP', 'bi-people', 2, 1),
('savings', 'Simpanan', 'Manajemen simpanan anggota', 'bi-piggy-bank', 3, 1),
('loans', 'Pinjaman', 'Manajemen pinjaman dan kredit', 'bi-cash-stack', 4, 1),
('accounting', 'Akuntansi', 'Sistem akuntansi dan keuangan', 'bi-calculator', 5, 1),
('shu', 'SHU', 'Sisa Hasil Usaha dan bagi hasil', 'bi-graph-up', 6, 1),
('sales', 'Penjualan', 'Modul penjualan dan promosi', 'bi-cart3', 7, 1),
('service', 'Layanan', 'Customer service dan support', 'bi-headset', 8, 1),
('billing', 'Tagihan', 'Invoice dan penagihan', 'bi-receipt', 9, 1),
('system', 'Sistem', 'Pengaturan dan konfigurasi', 'bi-gear', 10, 1),
('reports', 'Laporan', 'Laporan-laporan sistem', 'bi-file-earmark-text', 11, 1),
('profile', 'Profil', 'Profil pengguna', 'bi-person', 12, 1);

-- Insert default pages
INSERT INTO `pages` (`page_key`, `title`, `description`, `module_id`, `breadcrumb_title`, `roles_required`, `meta_title`, `meta_description`) VALUES
-- Dashboard
('dashboard', 'Dashboard', 'Halaman utama dashboard KSP Samosir', 1, 'Dashboard', 'admin,staff,member,customer_service,invoice,accounting', 'Dashboard KSP Samosir', 'Dashboard sistem KSP Samosir untuk monitoring overview'),

-- Membership
('anggota', 'Anggota', 'Manajemen data anggota KSP', 2, 'Anggota', 'admin,staff', 'Data Anggota KSP', 'Daftar data anggota KSP Samosir'),
('anggota/create', 'Tambah Anggota', 'Form tambah anggota baru', 2, 'Tambah Anggota', 'admin,staff', 'Tambah Anggota Baru', 'Form pendaftaran anggota baru KSP'),
('anggota/edit', 'Edit Anggota', 'Edit data anggota', 2, 'Edit Anggota', 'admin,staff', 'Edit Data Anggota', 'Form edit data anggota KSP'),

-- Savings
('simpanan', 'Simpanan', 'Manajemen simpanan anggota', 3, 'Simpanan', 'admin,staff,member', 'Data Simpanan', 'Daftar simpanan anggota KSP'),
('simpanan/create', 'Tambah Simpanan', 'Form tambah simpanan', 3, 'Tambah Simpanan', 'admin,staff', 'Tambah Simpanan', 'Form penambahan simpanan anggota'),

-- Loans
('pinjaman', 'Pinjaman', 'Manajemen pinjaman anggota', 4, 'Pinjaman', 'admin,staff,member', 'Data Pinjaman', 'Daftar pinjaman anggota KSP'),
('pinjaman/create', 'Ajukan Pinjaman', 'Form ajukan pinjaman', 4, 'Ajukan Pinjaman', 'admin,staff,member', 'Pengajuan Pinjaman', 'Form pengajuan pinjaman KSP'),

-- Accounting
('accounting', 'Akuntansi', 'Sistem akuntansi KSP', 5, 'Akuntansi', 'admin,accounting', 'Sistem Akuntansi', 'Dashboard akuntansi KSP Samosir'),
('accounting/jurnal', 'Jurnal', 'Jurnal umum transaksi', 5, 'Jurnal', 'admin,accounting', 'Jurnal Umum', 'Jurnal umum transaksi KSP'),
('accounting/neraca', 'Neraca', 'Laporan neraca keuangan', 5, 'Neraca', 'admin,accounting', 'Laporan Neraca', 'Laporan neraca KSP Samosir'),
('accounting/laba_rugi', 'Laba Rugi', 'Laporan laba rugi', 5, 'Laba Rugi', 'admin,accounting', 'Laporan Laba Rugi', 'Laporan laba rugi KSP'),

-- SHU
('shu', 'SHU', 'Sisa Hasil Usaha', 6, 'SHU', 'admin,accounting', 'SHU KSP', 'Dashboard Sisa Hasil Usaha'),
('shu/calculate', 'Hitung SHU', 'Perhitungan SHU', 6, 'Hitung SHU', 'admin,accounting', 'Perhitungan SHU', 'Kalkulasi Sisa Hasil Usaha'),
('shu/distribute', 'Distribusi SHU', 'Distribusi SHU ke anggota', 6, 'Distribusi SHU', 'admin,accounting', 'Distribusi SHU', 'Bagi hasil Sisa Hasil Usaha'),
('shu/reports', 'Laporan SHU', 'Laporan SHU', 6, 'Laporan SHU', 'admin,accounting', 'Laporan SHU', 'Laporan Sisa Hasil Usaha'),

-- Sales
('penjualan', 'Penjualan', 'Modul penjualan', 7, 'Penjualan', 'admin,staff', 'Penjualan KSP', 'Dashboard penjualan KSP'),
('penjualan/promos', 'Promo', 'Manajemen promo', 7, 'Promo', 'admin,staff', 'Promo Penjualan', 'Daftar promo penjualan'),
('penjualan/commissions', 'Komisi', 'Manajemen komisi', 7, 'Komisi', 'admin,staff', 'Komisi Penjualan', 'Daftar komisi penjualan'),

-- Service
('customer_service', 'Customer Service', 'Layanan pelanggan', 8, 'Customer Service', 'admin,customer_service', 'Customer Service', 'Dashboard layanan pelanggan'),
('customer_service/tickets', 'Tickets', 'Manajemen tickets', 8, 'Tickets', 'admin,customer_service', 'Customer Service Tickets', 'Daftar tickets pelanggan'),

-- Billing
('invoice', 'Invoice', 'Manajemen invoice', 9, 'Invoice', 'admin,invoice', 'Invoice KSP', 'Dashboard invoice KSP'),
('invoice/customer', 'Invoice Customer', 'Invoice customer', 9, 'Invoice Customer', 'admin,invoice', 'Invoice Customer', 'Daftar invoice customer'),
('invoice/supplier', 'Invoice Supplier', 'Invoice supplier', 9, 'Invoice Supplier', 'admin,invoice', 'Invoice Supplier', 'Daftar invoice supplier'),

-- System
('settings', 'Pengaturan', 'Pengaturan sistem', 10, 'Pengaturan', 'admin', 'Pengaturan KSP', 'Pengaturan sistem KSP Samosir'),

-- Reports
('laporan', 'Laporan', 'Laporan sistem', 11, 'Laporan', 'admin,staff', 'Laporan KSP', 'Dashboard laporan KSP'),

-- Profile
('profile', 'Profil Saya', 'Profil pengguna', 12, 'Profil Saya', 'admin,staff,member,customer_service,invoice,accounting', 'Profil Pengguna', 'Profil pengguna KSP');

-- Create indexes for performance
CREATE INDEX IF NOT EXISTS `idx_pages_module_active` ON `pages` (`module_id`, `is_active`);
CREATE INDEX IF NOT EXISTS `idx_activity_logs_user_created` ON `activity_logs` (`user_id`, `created_at`);

-- Add triggers for automatic timestamp updates
DELIMITER $$

-- Trigger for pages table
CREATE TRIGGER IF NOT EXISTS `pages_before_update` 
BEFORE UPDATE ON `pages` 
FOR EACH ROW 
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END$$

-- Trigger for modules table
CREATE TRIGGER IF NOT EXISTS `modules_before_update` 
BEFORE UPDATE ON `modules` 
FOR EACH ROW 
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END$$

DELIMITER ;

-- Update navigation table dengan module_id jika ada
UPDATE navigation n 
SET module_id = (SELECT id FROM modules WHERE module_key = 
    CASE n.page_key
        WHEN 'dashboard' THEN 'dashboard'
        WHEN 'anggota' THEN 'membership'
        WHEN 'simpanan' THEN 'savings'
        WHEN 'pinjaman' THEN 'loans'
        WHEN 'accounting' THEN 'accounting'
        WHEN 'shu' THEN 'shu'
        WHEN 'penjualan' THEN 'sales'
        WHEN 'customer_service' THEN 'service'
        WHEN 'invoice' THEN 'billing'
        WHEN 'settings' THEN 'system'
        WHEN 'laporan' THEN 'reports'
        WHEN 'profile' THEN 'profile'
        ELSE 'system'
    END
)
WHERE module_id IS NULL;

-- Verify data
SELECT 
    'Modules' as table_name, COUNT(*) as record_count FROM modules
UNION ALL
SELECT 
    'Pages' as table_name, COUNT(*) as record_count FROM pages
UNION ALL
SELECT 
    'Navigation' as table_name, COUNT(*) as record_count FROM navigation
UNION ALL
SELECT 
    'Settings' as table_name, COUNT(*) as record_count FROM settings;

-- Show sample data
SELECT 
    m.module_key,
    m.title as module_title,
    COUNT(p.id) as page_count
FROM modules m
LEFT JOIN pages p ON m.id = p.module_id
WHERE m.is_active = 1
GROUP BY m.id, m.module_key, m.title
ORDER BY m.sort_order;

COMMIT;
