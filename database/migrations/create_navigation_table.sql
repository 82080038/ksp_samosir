-- Navigation Table Migration
-- Untuk menyimpan konfigurasi navigation yang dinamis
-- Mendukung multi-role navigation dan hierarki

-- Create navigation table
CREATE TABLE IF NOT EXISTS `navigation` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `page_key` varchar(100) NOT NULL COMMENT 'Unique key untuk identifikasi page',
    `title` varchar(100) NOT NULL COMMENT 'Judul yang ditampilkan di navigation',
    `url` varchar(255) NOT NULL COMMENT 'URL navigation link',
    `icon_class` varchar(50) DEFAULT NULL COMMENT 'Bootstrap icon class',
    `parent_id` int(11) DEFAULT NULL COMMENT 'ID parent untuk sub-navigation',
    `sort_order` int(11) DEFAULT 0 COMMENT 'Urutan tampil',
    `is_active` tinyint(1) DEFAULT 1 COMMENT 'Apakah navigation aktif',
    `role_required` varchar(100) DEFAULT NULL COMMENT 'Role yang dibutuhkan (comma separated)',
    `description` text DEFAULT NULL COMMENT 'Deskripsi navigation item',
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `created_by` int(11) DEFAULT NULL,
    `updated_by` int(11) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_page_key` (`page_key`),
    KEY `idx_parent_id` (`parent_id`),
    KEY `idx_is_active` (`is_active`),
    KEY `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Navigation configuration for dynamic menu system';

-- Insert default navigation data
INSERT INTO `navigation` (`page_key`, `title`, `url`, `icon_class`, `parent_id`, `sort_order`, `is_active`, `role_required`, `description`) VALUES
-- Main Navigation
('dashboard', 'Dashboard', '/ksp_samosir/dashboard', 'bi-speedometer2', NULL, 1, 1, NULL, 'Halaman utama dashboard'),

-- Core Modules
('accounting', 'Akuntansi', '/ksp_samosir/accounting', 'bi-calculator', NULL, 2, 1, 'admin,accounting', 'Modul akuntansi dan keuangan'),
('anggota', 'Anggota', '/ksp_samosir/anggota', 'bi-people', NULL, 3, 1, 'admin,staff', 'Manajemen data anggota'),
('simpanan', 'Simpanan', '/ksp_samosir/simpanan', 'bi-piggy-bank', NULL, 4, 1, 'admin,staff', 'Manajemen simpanan anggota'),
('pinjaman', 'Pinjaman', '/ksp_samosir/pinjaman', 'bi-cash-stack', NULL, 5, 1, 'admin,staff', 'Manajemen pinjaman'),
('penjualan', 'Penjualan', '/ksp_samosir/penjualan', 'bi-cart3', NULL, 6, 1, 'admin,staff', 'Modul penjualan dan promosi'),

-- Advanced Modules
('customer_service', 'Customer Service', '/ksp_samosir/customer_service', 'bi-headset', NULL, 7, 1, 'admin,customer_service', 'Layanan pelanggan dan support'),
('invoice', 'Invoice', '/ksp_samosir/invoice', 'bi-receipt', NULL, 8, 1, 'admin,invoice', 'Manajemen invoice dan penagihan'),
('shu', 'SHU', '/ksp_samosir/shu', 'bi-graph-up', NULL, 9, 1, 'admin,accounting', 'Sisa Hasil Usaha dan bagi hasil'),

-- Reports and Analytics
('laporan', 'Laporan', '/ksp_samosir/laporan', 'bi-file-earmark-text', NULL, 10, 1, 'admin,staff', 'Laporan-laporan sistem'),

-- System and Settings
('settings', 'Pengaturan', '/ksp_samosir/settings', 'bi-gear', NULL, 11, 1, 'admin', 'Pengaturan sistem'),

-- Sub-Menu Items (jika diperlukan)
('accounting_jurnal', 'Jurnal', '/ksp_samosir/accounting/jurnal', 'bi-journal-text', 2, 1, 1, 'admin,accounting', 'Jurnal umum'),
('accounting_neraca', 'Neraca', '/ksp_samosir/accounting/neraca', 'bi-balance-scale', 2, 2, 1, 'admin,accounting', 'Laporan neraca'),
('accounting_laba_rugi', 'Laba Rugi', '/ksp_samosir/accounting/laba_rugi', 'bi-graph-up-arrow', 2, 3, 1, 'admin,accounting', 'Laporan laba rugi'),

('shu_index', 'SHU Dashboard', '/ksp_samosir/shu', 'bi-graph-up', 9, 1, 1, 'admin,accounting', 'Dashboard SHU'),
('shu_calculate', 'Hitung SHU', '/ksp_samosir/shu/calculate', 'bi-calculator', 9, 2, 1, 'admin,accounting', 'Perhitungan SHU'),
('shu_distribute', 'Distribusi SHU', '/ksp_samosir/shu/distribute', 'bi-arrow-left-right', 9, 3, 1, 'admin,accounting', 'Distribusi SHU'),
('shu_reports', 'Laporan SHU', '/ksp_samosir/shu/reports', 'bi-file-earmark-text', 9, 4, 1, 'admin,accounting', 'Laporan SHU'),

('penjualan_index', 'Dashboard Penjualan', '/ksp_samosir/penjualan', 'bi-cart3', 5, 1, 1, 'admin,staff', 'Dashboard penjualan'),
('penjualan_promos', 'Promo', '/ksp_samosir/penjualan/promos', 'bi-tag', 5, 2, 1, 'admin,staff', 'Manajemen promo'),
('penjualan_commissions', 'Komisi', '/ksp_samosir/penjualan/commissions', 'bi-percent', 5, 3, 1, 'admin,staff', 'Manajemen komisi'),
('penjualan_agent_sales', 'Penjualan Agen', '/ksp_samosir/penjualan/agent_sales', 'bi-person-check', 5, 4, 1, 'admin,staff', 'Data penjualan agen');

-- Create index untuk performance
CREATE INDEX IF NOT EXISTS `idx_navigation_role` ON `navigation` (`is_active`, `role_required`);
CREATE INDEX IF NOT EXISTS `idx_navigation_parent_sort` ON `navigation` (`parent_id`, `sort_order`);

-- Add foreign key constraint untuk self-reference
ALTER TABLE `navigation` 
ADD CONSTRAINT `fk_navigation_parent` 
FOREIGN KEY (`parent_id`) REFERENCES `navigation` (`id`) 
ON DELETE SET NULL ON UPDATE CASCADE;

-- Create trigger untuk updated_at
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS `navigation_before_update` 
BEFORE UPDATE ON `navigation` 
FOR EACH ROW 
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END$$
DELIMITER ;

-- Insert default admin user reference (jika ada users table)
-- UPDATE navigation SET created_by = 1, updated_by = 1 WHERE created_by IS NULL;

-- Commit changes
COMMIT;

-- Verify data
SELECT 
    page_key,
    title,
    url,
    icon_class,
    parent_id,
    sort_order,
    is_active,
    role_required,
    description
FROM navigation 
WHERE is_active = 1 
ORDER BY parent_id ASC, sort_order ASC;
