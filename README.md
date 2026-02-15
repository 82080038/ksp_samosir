# KSP Samosir

Sistem Manajemen Koperasi Multi-Jenis — PHP 8 / MySQL / Bootstrap 5.3

## Quick Start

```bash
# 1. Setup database
mysql -u root -p -e "CREATE DATABASE ksp_samosir CHARACTER SET utf8mb4;"
mysql -u root -p ksp_samosir < database/migrations/create_sidebar_menus_table.sql
mysql -u root -p ksp_samosir < database/migrations/seed_koperasi_sidebar_menus.sql

# 2. Akses
http://localhost/ksp_samosir/
# Login: admin / admin123
```

## Dokumentasi

- [README lengkap](docs/README.md) — Struktur, modul, arsitektur sidebar
- [Setup & Instalasi](docs/SETUP.md) — Prasyarat, langkah instalasi, troubleshooting
- [API Documentation](docs/API_Documentation_Complete.md) — Endpoint reference

## Modul

- **27 modul inti** — Anggota, Simpanan, Pinjaman, Akuntansi, Invoice, dll.
- **39 modul koperasi spesifik** — 10 jenis koperasi (KSP, KPN, KPT, KPI, KPD, KPK, KPP, KPTK, KPE, KPSD)
- **Sidebar dinamis** — Navigasi dari database (`sidebar_menus`), role-based, session-cached
