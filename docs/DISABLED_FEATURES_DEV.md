# Catatan Fitur yang DINONAKTIFKAN (Mode Development)

Dokumentasi ini mencatat perubahan sementara yang menonaktifkan keamanan/akses agar tidak lupa saat kembali ke mode produksi.

## 1) index.php
- **Auth check**: Dinonaktifkan. Blok kode redirect login untuk halaman non-public di-comment.
- **Role-based access control**: Dinonaktifkan. Blok `canAccessModule` di-comment.

## 2) Session Config (config/config.php)
- Session name diubah ke `ksp_samosir_dev`.
- Session lifetime diperpanjang menjadi 24 jam (86400 detik) untuk dev.

## 3) Controllers
- **DashboardController**: `requirePermission('view_dashboard')` di-comment.
- **PinjamanController**: `ensureLoginAndRole` di-comment di index/create/store; compliance validation di-comment di store.
- **AnggotaController**: `ensureLoginAndRole` di-comment di index/create/store/edit/update.
- Kemungkinan controller lain masih punya `ensureLoginAndRole`; cari string itu jika ingin re-enable cepat.

## 4) Compliance Checks
- Validasi compliance pinjaman (batas pinjaman, eligibility) dinonaktifkan di PinjamanController@store.

## 5) Cara Aktifkan Kembali (Mode Produksi)
- index.php: uncomment blok auth & canAccessModule.
- config.php: kembalikan SESSION_NAME/LIFETIME ke nilai produksi (ksp_samosir_session, 7200 detik) jika perlu.
- Controllers: uncomment `ensureLoginAndRole` dan `requirePermission`, serta compliance validation di Pinjaman.
- Pertimbangkan re-enable logika compliance di `koperasi_compliance.php` sesuai kebutuhan.

## 6) Pemeriksaan Cepat
- Cari: `DISABLED for development` di repo untuk melihat semua bagian yang di-comment.
- Cari: `ensureLoginAndRole` atau `requirePermission` untuk re-enable akses kontrol.

## 7) Catatan
- Mode dev ini hanya untuk eksplorasi alur/logic. Pastikan mengembalikan kontrol akses sebelum go-live.
