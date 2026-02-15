-- ============================================
-- Seed sidebar_menus: Koperasi-specific modules
-- 10 jenis koperasi × their specific modules
-- ============================================

-- KSP - Simpan Pinjam (3 modules)
INSERT INTO sidebar_menus (id, parent_id, menu_type, title, url, icon, roles, sort_order) VALUES
(100, NULL, 'section', 'KSP - Simpan Pinjam', NULL, NULL, '["admin","staff"]', 1000),
(101, 100, 'item', 'Deposito Berjangka', 'koperasi_modul/ksp_deposito', 'bi-safe', '["admin","staff"]', 1001),
(102, 100, 'item', 'E-Wallet', 'koperasi_modul/ksp_wallet', 'bi-wallet2', '["admin","staff"]', 1002),
(103, 100, 'item', 'QRIS Payment', 'koperasi_modul/ksp_qris', 'bi-qr-code', '["admin","staff"]', 1003);

-- KPN - Pertanian (5 modules)
INSERT INTO sidebar_menus (id, parent_id, menu_type, title, url, icon, roles, sort_order) VALUES
(110, NULL, 'section', 'KPN - Pertanian', NULL, NULL, '["admin","staff"]', 1100),
(111, 110, 'item', 'Manajemen Lahan', 'koperasi_modul/kpn_lahan', 'bi-map', '["admin","staff"]', 1101),
(112, 110, 'item', 'Jadwal Tanam', 'koperasi_modul/kpn_tanam', 'bi-calendar-range', '["admin","staff"]', 1102),
(113, 110, 'item', 'Pupuk & Pestisida', 'koperasi_modul/kpn_pupuk', 'bi-droplet', '["admin","staff"]', 1103),
(114, 110, 'item', 'Prediksi Panen', 'koperasi_modul/kpn_panen', 'bi-graph-up-arrow', '["admin","staff"]', 1104),
(115, 110, 'item', 'Sistem Irigasi', 'koperasi_modul/kpn_irigasi', 'bi-moisture', '["admin","staff"]', 1105);

-- KPT - Ternak (4 modules)
INSERT INTO sidebar_menus (id, parent_id, menu_type, title, url, icon, roles, sort_order) VALUES
(120, NULL, 'section', 'KPT - Peternakan', NULL, NULL, '["admin","staff"]', 1200),
(121, 120, 'item', 'Manajemen Ternak', 'koperasi_modul/kpt_ternak', 'bi-heart-pulse', '["admin","staff"]', 1201),
(122, 120, 'item', 'Manajemen Pakan', 'koperasi_modul/kpt_pakan', 'bi-basket', '["admin","staff"]', 1202),
(123, 120, 'item', 'Kesehatan Hewan', 'koperasi_modul/kpt_kesahatan', 'bi-clipboard2-pulse', '["admin","staff"]', 1203),
(124, 120, 'item', 'Program Kawin', 'koperasi_modul/kpt_reproduksi', 'bi-heart', '["admin","staff"]', 1204);

-- KPI - Industri (4 modules)
INSERT INTO sidebar_menus (id, parent_id, menu_type, title, url, icon, roles, sort_order) VALUES
(130, NULL, 'section', 'KPI - Industri', NULL, NULL, '["admin","staff"]', 1300),
(131, 130, 'item', 'Manajemen Produksi', 'koperasi_modul/kpi_produksi', 'bi-gear-wide-connected', '["admin","staff"]', 1301),
(132, 130, 'item', 'Inventory Bahan Baku', 'koperasi_modul/kpi_inventory', 'bi-boxes', '["admin","staff"]', 1302),
(133, 130, 'item', 'Quality Control', 'koperasi_modul/kpi_quality', 'bi-check-circle', '["admin","staff"]', 1303),
(134, 130, 'item', 'Supply Chain', 'koperasi_modul/kpi_supply', 'bi-truck', '["admin","staff"]', 1304);

-- KPD - Dagang (4 modules)
INSERT INTO sidebar_menus (id, parent_id, menu_type, title, url, icon, roles, sort_order) VALUES
(140, NULL, 'section', 'KPD - Perdagangan', NULL, NULL, '["admin","staff"]', 1400),
(141, 140, 'item', 'Manajemen Supplier', 'koperasi_modul/kpd_supplier', 'bi-truck', '["admin","staff"]', 1401),
(142, 140, 'item', 'Katalog Produk', 'koperasi_modul/kpd_produk', 'bi-tags', '["admin","staff"]', 1402),
(143, 140, 'item', 'Manajemen Gudang', 'koperasi_modul/kpd_gudang', 'bi-house-gear', '["admin","staff"]', 1403),
(144, 140, 'item', 'Distribusi', 'koperasi_modul/kpd_distribution', 'bi-send', '["admin","staff"]', 1404);

-- KPK - Konsumsi (3 modules)
INSERT INTO sidebar_menus (id, parent_id, menu_type, title, url, icon, roles, sort_order) VALUES
(150, NULL, 'section', 'KPK - Konsumsi', NULL, NULL, '["admin","staff"]', 1500),
(151, 150, 'item', 'Jadwal Belanja Rutin', 'koperasi_modul/kpk_ritual', 'bi-calendar-check', '["admin","staff"]', 1501),
(152, 150, 'item', 'Belanja Grosir', 'koperasi_modul/kpk_grosir', 'bi-basket2', '["admin","staff"]', 1502),
(153, 150, 'item', 'Distributor Barang', 'koperasi_modul/kpk_distributor', 'bi-shop-window', '["admin","staff"]', 1503);

-- KPP - Perikanan (4 modules)
INSERT INTO sidebar_menus (id, parent_id, menu_type, title, url, icon, roles, sort_order) VALUES
(160, NULL, 'section', 'KPP - Perikanan', NULL, NULL, '["admin","staff"]', 1600),
(161, 160, 'item', 'Manajemen Kapal', 'koperasi_modul/kpp_kapal', 'bi-water', '["admin","staff"]', 1601),
(162, 160, 'item', 'Alat Tangkap', 'koperasi_modul/kpp_alat_tangkap', 'bi-tools', '["admin","staff"]', 1602),
(163, 160, 'item', 'Kualitas Air', 'koperasi_modul/kpp_kualitas_air', 'bi-droplet-half', '["admin","staff"]', 1603),
(164, 160, 'item', 'Lelang Ikan', 'koperasi_modul/kpp_lelang', 'bi-hammer', '["admin","staff"]', 1604);

-- KPTK - Pariwisata (4 modules)
INSERT INTO sidebar_menus (id, parent_id, menu_type, title, url, icon, roles, sort_order) VALUES
(170, NULL, 'section', 'KPTK - Pariwisata', NULL, NULL, '["admin","staff"]', 1700),
(171, 170, 'item', 'Paket Wisata', 'koperasi_modul/kptk_paket', 'bi-suitcase', '["admin","staff"]', 1701),
(172, 170, 'item', 'Transportasi', 'koperasi_modul/kptk_transportasi', 'bi-bus-front', '["admin","staff"]', 1702),
(173, 170, 'item', 'Akomodasi', 'koperasi_modul/kptk_akomodasi', 'bi-house-door', '["admin","staff"]', 1703),
(174, 170, 'item', 'Tour Guide', 'koperasi_modul/kptk_guide', 'bi-person-badge', '["admin","staff"]', 1704);

-- KPE - Energi (4 modules)
INSERT INTO sidebar_menus (id, parent_id, menu_type, title, url, icon, roles, sort_order) VALUES
(180, NULL, 'section', 'KPE - Energi', NULL, NULL, '["admin","staff"]', 1800),
(181, 180, 'item', 'Solar Panel', 'koperasi_modul/kpe_panel', 'bi-sun', '["admin","staff"]', 1801),
(182, 180, 'item', 'Battery Storage', 'koperasi_modul/kpe_baterai', 'bi-battery-charging', '["admin","staff"]', 1802),
(183, 180, 'item', 'Smart Grid', 'koperasi_modul/kpe_grid', 'bi-plugin', '["admin","staff"]', 1803),
(184, 180, 'item', 'Energy Monitoring', 'koperasi_modul/kpe_monitoring', 'bi-graph-up', '["admin","staff"]', 1804);

-- KPSD - Sumber Daya (4 modules)
INSERT INTO sidebar_menus (id, parent_id, menu_type, title, url, icon, roles, sort_order) VALUES
(190, NULL, 'section', 'KPSD - Sumber Daya', NULL, NULL, '["admin","staff"]', 1900),
(191, 190, 'item', 'Manajemen Hutan', 'koperasi_modul/kpsd_hutan', 'bi-tree', '["admin","staff"]', 1901),
(192, 190, 'item', 'Sumber Daya Mineral', 'koperasi_modul/kpsd_mineral', 'bi-gem', '["admin","staff"]', 1902),
(193, 190, 'item', 'Sumber Daya Air', 'koperasi_modul/kpsd_air', 'bi-droplet', '["admin","staff"]', 1903),
(194, 190, 'item', 'Konservasi', 'koperasi_modul/kpsd_konservasi', 'bi-flower1', '["admin","staff"]', 1904);
