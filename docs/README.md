# KSP Samosir - Sistem Manajemen Koperasi

Aplikasi web manajemen koperasi multi-jenis untuk Koperasi Pemasaran Kepolisian Polres Samosir. Dibangun dengan PHP 8, MySQL, Bootstrap 5.3, dan jQuery.

---

## Tech Stack

| Layer | Teknologi |
|---|---|
| **Backend** | PHP 8.0+, MVC pattern, PDO prepared statements |
| **Frontend** | Bootstrap 5.3.2, jQuery 3.7.1, Bootstrap Icons 1.11 |
| **Database** | MySQL 8.0+ (database: `ksp_samosir`) |
| **Charting** | Chart.js 4.4 |
| **UI Library** | `ksp-ui.js` — unified toast, modal, form, table, AJAX helpers |

---

## Struktur Direktori

```
ksp_samosir/
├── index.php                  # Main router (switch-based)
├── simple_controller.php      # Login/auth front controller
├── config/
│   ├── config.php             # App config, helpers (base_url, hasRole, isActivePage, etc.)
│   ├── database.php           # DB connection (fetchRow, fetchAll, executeNonQuery)
│   ├── database_safe.php      # Null-safe DB wrappers
│   ├── role_management.php    # Role & permission helpers
│   └── ...                    # Other config modules
├── app/
│   ├── core/
│   │   └── BaseController.php # Abstract base (DB, render, JSON, auth, pagination)
│   ├── controllers/           # 30+ controllers (one per module)
│   │   ├── DashboardController.php
│   │   ├── AnggotaCRUDController.php
│   │   ├── SimpananCRUDController.php
│   │   ├── PinjamanCRUDController.php
│   │   ├── AccountingController.php
│   │   ├── KoperasiModulController.php   # Generic controller for 39 koperasi modules
│   │   └── ...
│   ├── helpers/
│   │   ├── DependencyManager.php
│   │   ├── SidebarHelper.php  # Dynamic sidebar from DB (getSidebarMenus, renderSidebarMenus)
│   │   ├── FormatHelper.php
│   │   └── UnitHelper.php
│   ├── services/              # Business logic services
│   └── views/
│       ├── layouts/
│       │   └── main.php       # Master layout (navbar, dynamic sidebar, scripts)
│       ├── dashboard/
│       ├── anggota/
│       ├── simpanan/
│       ├── pinjaman/
│       ├── accounting/
│       ├── koperasi_modul/    # Generic views for all koperasi-specific modules
│       │   ├── index.php      # Overview of all jenis koperasi & modules
│       │   └── module_dashboard.php  # Per-module dashboard (adaptive)
│       └── ...                # 20+ module view directories
├── assets/
│   ├── css/ksp-ui.css
│   └── js/ksp-ui.js          # Unified KSP UI library
├── database/
│   ├── migrations/            # SQL migration scripts
│   └── seeds/                 # Seed data scripts
├── docs/                      # Documentation (this directory)
├── shared/php/                # Shared PHP utilities (formatters, compliance, etc.)
├── api/                       # REST API endpoints
├── sql/                       # Schema SQL files
├── tests/                     # Test suites
└── logs/                      # Application logs
```

---

## Modul Aplikasi (27 Core Modules)

Semua modul di-render melalui sidebar dinamis dari tabel `sidebar_menus`.

### Keanggotaan
| Modul | Route | Controller |
|---|---|---|
| Anggota | `/anggota` | `AnggotaCRUDController` |
| Simpanan | `/simpanan` | `SimpananCRUDController` |
| Pinjaman | `/pinjaman` | `PinjamanCRUDController` |

### Keuangan
| Modul | Route | Controller |
|---|---|---|
| Akuntansi | `/accounting` | `AccountingController` |
| Invoice | `/invoice` | `InvoiceController` |
| Perpajakan | `/tax` | BaseController (view-only) |
| Penggajian | `/payroll` | BaseController (view-only) |
| SHU | `/shu` | `ShuController` |
| Aset | `/asset` | `AssetController` |

### Bisnis
| Modul | Route | Controller |
|---|---|---|
| Produk | `/produk` | `ProdukCRUDController` |
| Penjualan | `/penjualan` | `PenjualanCRUDController` |
| Inventaris | `/inventory` | `InventoryController` |

### Layanan
| Modul | Route | Controller |
|---|---|---|
| Customer Service | `/customer_service` | `CustomerServiceController` |
| Notifikasi | `/notifications` | BaseController (view-only) |
| Rapat | `/rapat` | `RapatController` |
| Learning Center | `/learning` | BaseController (view-only) |

### Analisis & Risiko
| Modul | Route | Controller |
|---|---|---|
| Laporan | `/laporan` | `LaporanController` |
| Manajemen Risiko | `/risk` | `RiskController` |
| AI Kredit Skor | `/ai_credit` | `AICreditController` |
| Blockchain | `/blockchain` | `BlockchainController` |

### Sistem (admin only)
| Modul | Route | Controller |
|---|---|---|
| Monitoring | `/monitoring` | `MonitoringController` |
| Pengawas | `/pengawas` | `PengawasController` |
| Audit Log | `/logs` | `LogsController` |
| Dokumen Digital | `/digital_documents` | `DigitalDocumentsController` |
| Backup & Restore | `/backup` | `BackupController` |
| Pengaturan | `/settings` | `SettingsController` |

---

## Jenis Koperasi (10 Tipe)

Aplikasi mendukung 10 jenis koperasi, masing-masing dengan modul spesifik. Data disimpan di tabel `koperasi_jenis` dan `koperasi_modul`.

| Kode | Nama | Modul Spesifik | Route Pattern |
|---|---|---|---|
| **KSP** | Simpan Pinjam | Deposito, E-Wallet, QRIS | `/koperasi_modul/ksp_*` |
| **KPN** | Pertanian | Lahan, Tanam, Pupuk, Panen, Irigasi | `/koperasi_modul/kpn_*` |
| **KPT** | Peternakan | Ternak, Pakan, Kesehatan, Reproduksi | `/koperasi_modul/kpt_*` |
| **KPI** | Industri | Produksi, Inventory, QC, Supply Chain | `/koperasi_modul/kpi_*` |
| **KPD** | Perdagangan | Supplier, Produk, Gudang, Distribusi | `/koperasi_modul/kpd_*` |
| **KPK** | Konsumsi | Belanja Rutin, Grosir, Distributor | `/koperasi_modul/kpk_*` |
| **KPP** | Perikanan | Kapal, Alat Tangkap, Kualitas Air, Lelang | `/koperasi_modul/kpp_*` |
| **KPTK** | Pariwisata | Paket, Transportasi, Akomodasi, Guide | `/koperasi_modul/kptk_*` |
| **KPE** | Energi | Solar, Baterai, Grid, Monitoring | `/koperasi_modul/kpe_*` |
| **KPSD** | Sumber Daya | Hutan, Mineral, Air, Konservasi | `/koperasi_modul/kpsd_*` |

Semua 39 modul spesifik ditangani oleh `KoperasiModulController` dan di-render oleh view `koperasi_modul/module_dashboard.php` yang adaptif.

---

## Arsitektur Sidebar Dinamis

Sidebar navigasi sepenuhnya berasal dari database (bukan hardcode).

### Tabel `sidebar_menus`

| Kolom | Tipe | Fungsi |
|---|---|---|
| `id` | INT PK | Auto-increment |
| `parent_id` | INT NULL | FK → sections (NULL = top-level) |
| `menu_type` | ENUM | `section` (header) atau `item` (link) |
| `title` | VARCHAR(100) | Teks tampilan |
| `url` | VARCHAR(255) | Route path (e.g. `anggota`, `koperasi_modul/kpn_lahan`) |
| `icon` | VARCHAR(100) | Bootstrap Icons class (e.g. `bi-people`) |
| `roles` | JSON | Array role yang bisa lihat: `["admin","staff"]` |
| `sort_order` | INT | Urutan tampil |
| `is_active` | TINYINT | 0 = hidden, 1 = visible |
| `badge_query` | VARCHAR(500) | SQL opsional untuk badge count dinamis |

### Alur Kerja

```
sidebar_menus (93 rows)
    ↓ SidebarHelper::getSidebarMenus($role)
    ↓ Filter by role (JSON), group sections → items
    ↓ Cache in $_SESSION per role
    ↓ renderSidebarMenus() → HTML
    ↓ main.php layout: <?= renderSidebarMenus() ?>
```

### Cara Menambah Menu Baru

```sql
-- Tambah section baru
INSERT INTO sidebar_menus (parent_id, menu_type, title, roles, sort_order)
VALUES (NULL, 'section', 'Nama Seksi', '["admin","staff"]', 2000);

-- Tambah item ke section (parent_id = id section di atas)
INSERT INTO sidebar_menus (parent_id, menu_type, title, url, icon, roles, sort_order)
VALUES (LAST_INSERT_ID(), 'item', 'Nama Menu', 'route_path', 'bi-icon', '["admin","staff"]', 2001);
```

Setelah update, panggil `clearSidebarCache()` atau logout/login ulang agar cache session ter-refresh.

---

## Akun Development

| Role | Username | Password |
|---|---|---|
| Admin | `admin` | `admin123` |
| Manager | `manager` | `manager123` |
| Staff | `staff` | `staff123` |
| Anggota | `anggota` | `anggota123` |

---

## Database

- **Host**: `localhost`
- **User**: `root`
- **Password**: `root`
- **Database**: `ksp_samosir`
- **Charset**: `utf8mb4`

### Tabel Penting

| Tabel | Fungsi |
|---|---|
| `sidebar_menus` | Navigasi sidebar dinamis |
| `koperasi_jenis` | 10 jenis koperasi |
| `koperasi_modul` | 44 modul (5 core + 39 spesifik) |
| `koperasi_modul_mapping` | Mapping modul ↔ jenis koperasi |
| `settings` | Pengaturan aplikasi (key-value) |
| `anggota` | Data anggota koperasi |
| `simpanan` | Transaksi simpanan |
| `pinjaman` | Transaksi pinjaman |
| `logs` | Audit trail |

---

## Lisensi

Internal use — Koperasi Pemasaran Kepolisian Polres Samosir.
