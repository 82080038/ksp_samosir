-- ============================================
-- Dynamic Sidebar Menus Table
-- KSP Samosir - Navigasi Dinamis dari Database
-- ============================================

CREATE TABLE IF NOT EXISTS sidebar_menus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT DEFAULT NULL COMMENT 'NULL = top-level item or section header',
    menu_type ENUM('section','item') NOT NULL DEFAULT 'item' COMMENT 'section = group header, item = clickable link',
    title VARCHAR(100) NOT NULL COMMENT 'Display text',
    url VARCHAR(255) DEFAULT NULL COMMENT 'Route path relative to base_url, NULL for sections',
    icon VARCHAR(100) DEFAULT NULL COMMENT 'Bootstrap Icons class e.g. bi-speedometer2',
    roles JSON NOT NULL COMMENT 'Array of roles that can see this menu, e.g. ["admin","staff"]',
    sort_order INT NOT NULL DEFAULT 0 COMMENT 'Display order within parent group',
    is_active TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0=hidden, 1=visible',
    badge_query VARCHAR(500) DEFAULT NULL COMMENT 'Optional SQL to show dynamic badge count',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_parent (parent_id),
    INDEX idx_sort (sort_order),
    INDEX idx_active (is_active),
    FOREIGN KEY (parent_id) REFERENCES sidebar_menus(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Seed: Dashboard (all roles)
-- ============================================
INSERT INTO sidebar_menus (id, parent_id, menu_type, title, url, icon, roles, sort_order) VALUES
(1, NULL, 'item', 'Dashboard', 'dashboard', 'bi-speedometer2', '["admin","staff","member","manager"]', 0);

-- ============================================
-- Seed: Keanggotaan (admin, staff)
-- ============================================
INSERT INTO sidebar_menus (id, parent_id, menu_type, title, url, icon, roles, sort_order) VALUES
(10, NULL, 'section', 'Keanggotaan', NULL, NULL, '["admin","staff"]', 100),
(11, 10,   'item', 'Anggota',   'anggota',   'bi-people',      '["admin","staff"]', 101),
(12, 10,   'item', 'Simpanan',  'simpanan',  'bi-piggy-bank',  '["admin","staff"]', 102),
(13, 10,   'item', 'Pinjaman',  'pinjaman',  'bi-cash-stack',  '["admin","staff"]', 103);

-- ============================================
-- Seed: Keuangan (admin, staff)
-- ============================================
INSERT INTO sidebar_menus (id, parent_id, menu_type, title, url, icon, roles, sort_order) VALUES
(20, NULL, 'section', 'Keuangan', NULL, NULL, '["admin","staff"]', 200),
(21, 20,   'item', 'Akuntansi',   'accounting', 'bi-calculator',  '["admin","staff"]', 201),
(22, 20,   'item', 'Invoice',     'invoice',    'bi-receipt',     '["admin","staff"]', 202),
(23, 20,   'item', 'Perpajakan',  'tax',        'bi-percent',     '["admin","staff"]', 203),
(24, 20,   'item', 'Penggajian',  'payroll',    'bi-wallet2',     '["admin","staff"]', 204),
(25, 20,   'item', 'SHU',         'shu',        'bi-pie-chart',   '["admin","staff"]', 205),
(26, 20,   'item', 'Aset',        'asset',      'bi-building',    '["admin","staff"]', 206);

-- ============================================
-- Seed: Bisnis (admin, staff)
-- ============================================
INSERT INTO sidebar_menus (id, parent_id, menu_type, title, url, icon, roles, sort_order) VALUES
(30, NULL, 'section', 'Bisnis', NULL, NULL, '["admin","staff"]', 300),
(31, 30,   'item', 'Produk',     'produk',     'bi-tags',      '["admin","staff"]', 301),
(32, 30,   'item', 'Penjualan',  'penjualan',  'bi-cart',      '["admin","staff"]', 302),
(33, 30,   'item', 'Inventaris', 'inventory',  'bi-box-seam',  '["admin","staff"]', 303);

-- ============================================
-- Seed: Layanan (admin, staff)
-- ============================================
INSERT INTO sidebar_menus (id, parent_id, menu_type, title, url, icon, roles, sort_order) VALUES
(40, NULL, 'section', 'Layanan', NULL, NULL, '["admin","staff"]', 400),
(41, 40,   'item', 'Customer Service', 'customer_service', 'bi-headset',        '["admin","staff"]', 401),
(42, 40,   'item', 'Notifikasi',       'notifications',    'bi-bell',           '["admin","staff"]', 402),
(43, 40,   'item', 'Rapat',            'rapat',            'bi-calendar-event', '["admin","staff"]', 403),
(44, 40,   'item', 'Learning Center',  'learning',         'bi-mortarboard',    '["admin","staff"]', 404);

-- ============================================
-- Seed: Analisis & Risiko (admin, staff)
-- ============================================
INSERT INTO sidebar_menus (id, parent_id, menu_type, title, url, icon, roles, sort_order) VALUES
(50, NULL, 'section', 'Analisis & Risiko', NULL, NULL, '["admin","staff"]', 500),
(51, 50,   'item', 'Laporan',           'laporan',    'bi-bar-chart-line',      '["admin","staff"]', 501),
(52, 50,   'item', 'Manajemen Risiko',  'risk',       'bi-shield-exclamation',  '["admin","staff"]', 502),
(53, 50,   'item', 'AI Kredit Skor',    'ai_credit',  'bi-robot',              '["admin","staff"]', 503),
(54, 50,   'item', 'Blockchain',        'blockchain', 'bi-link-45deg',         '["admin","staff"]', 504);

-- ============================================
-- Seed: Sistem (admin only)
-- ============================================
INSERT INTO sidebar_menus (id, parent_id, menu_type, title, url, icon, roles, sort_order) VALUES
(60, NULL, 'section', 'Sistem', NULL, NULL, '["admin"]', 600),
(61, 60,   'item', 'Monitoring',       'monitoring',         'bi-activity',          '["admin"]', 601),
(62, 60,   'item', 'Pengawas',         'pengawas',           'bi-shield-check',      '["admin"]', 602),
(63, 60,   'item', 'Audit Log',        'logs',               'bi-journal-text',      '["admin"]', 603),
(64, 60,   'item', 'Dokumen Digital',  'digital_documents',  'bi-file-earmark-text', '["admin"]', 604),
(65, 60,   'item', 'Backup & Restore', 'backup',             'bi-cloud-arrow-up',    '["admin"]', 605),
(66, 60,   'item', 'Pengaturan',       'settings',           'bi-gear',              '["admin"]', 606);

-- ============================================
-- Seed: Member menu
-- ============================================
INSERT INTO sidebar_menus (id, parent_id, menu_type, title, url, icon, roles, sort_order) VALUES
(70, NULL, 'section', 'Akun Saya', NULL, NULL, '["member"]', 700),
(71, 70,   'item', 'Profil Saya',       'profile',    'bi-person',       '["member"]', 701),
(72, 70,   'item', 'Simpanan Saya',     'simpanan',   'bi-piggy-bank',   '["member"]', 702),
(73, 70,   'item', 'Pinjaman Saya',     'pinjaman',   'bi-cash-stack',   '["member"]', 703),
(74, 70,   'item', 'Riwayat Transaksi', 'transaksi',  'bi-receipt',      '["member"]', 704),
(75, 70,   'item', 'Ajukan Pinjaman',   'permohonan', 'bi-plus-circle',  '["member"]', 705),
(76, 70,   'item', 'Laporan Saya',      'laporan',    'bi-file-earmark-text', '["member"]', 706);

-- ============================================
-- Seed: Manager menu
-- ============================================
INSERT INTO sidebar_menus (id, parent_id, menu_type, title, url, icon, roles, sort_order) VALUES
(80, NULL, 'section', 'Manajemen', NULL, NULL, '["manager"]', 800),
(81, 80,   'item', 'Dashboard Manager',    'dashboard', 'bi-speedometer2',  '["manager"]', 801),
(82, 80,   'item', 'Antrian Persetujuan',  'approval',  'bi-check-circle',  '["manager"]', 802),
(83, 80,   'item', 'Laporan Manajemen',    'reports',   'bi-bar-chart',     '["manager"]', 803);
