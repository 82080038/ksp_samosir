# Rencana Pengembangan KSP Samosir
Rencana singkat untuk membangun dan menyelesaikan aplikasi koperasi KSP Samosir berbasis PHP MVC.

## Tujuan & Ruang Lingkup
- Menyelesaikan fitur inti: autentikasi, dashboard statistik, manajemen anggota, simpanan, pinjaman, penjualan, laporan.
- Menjaga arsitektur MVC sederhana (PHP 8, MySQL, Bootstrap 5 + jQuery), mengutamakan keamanan dasar (session, CSRF, sanitasi input), dan kinerja query.

## Segmentasi Pengguna & Kebutuhan FE/BE
- **Pengurus koperasi (ketua/wakil/sekretaris/bendahara, bisa merangkap agen/investor)**: FE dashboard operasional, approvals, CRUD anggota/simpanan/pinjaman/produk, laporan; BE role-based access, workflow persetujuan, log aktivitas, transaksi finansial dengan ACID.
- **Pengawas koperasi (dapat bertransaksi sebagai pembeli/agen)**: FE akses monitoring, laporan, log audit; BE read-only + hak approve terbatas, audit trail, transparansi konflik kepentingan.
- **Anggota koperasi**: FE profil anggota, simpanan/pinjaman, riwayat pembayaran, pembelian; BE portal anggota (autentikasi & rate limit), validasi data, perhitungan saldo/bunga/denda, batas kredit.
- **Agen/Reseller (anggota/pengurus/pengawas/pihak ketiga)**: FE input penjualan, stok agen, komisi; BE role & conflict check, approval penjualan oleh pengurus, stok per agen, laporan per agen.
- **Investor pihak ketiga**: FE ringkasan modal penyertaan, laporan bagi hasil; BE pencatatan modal, aturan kepemilikan hybrid, pembagian SHU/dividen, compliance.
- **Pelanggan umum/non-anggota**: FE katalog & checkout sederhana; BE order intake, pembayaran, status pesanan, tanpa akses modul internal.
- **Pemasok/Vendor & Mitra**: FE formulir onboarding & daftar PO/kontrak; BE master data pemasok/mitra, PO, penerimaan barang, pembayaran, evaluasi kinerja.

## Artefak & Lingkungan
- Konfigurasi: `config/config.php`, `config/database.php`.
- Entry point & routing: `public/index.php`.
- Struktur: controllers/models/views di `app/`, asset di `public/assets`, skema di `database/ksp_samosir.sql`.
- Prasyarat: PHP 8.x, MySQL, web server root ke `public/`, mod_rewrite aktif.

## Tahapan Kerja (Urutan Disarankan)
1) **Baseline & Setup**: verifikasi konfigurasi env (DB, URL), jalankan schema SQL, pastikan session & error log berfungsi.
2) **Keamanan Dasar**: audit helpers (CSRF token, sanitize, flash); perkuat password hashing, session hardening, dan validasi input umum.
3) **Autentikasi & Session**: finalisasi login/logout, pengelolaan role, middleware requireLogin; tambahkan rate limit dasar/log aktivitas jika perlu.
4) **Dashboard**: pastikan statistik & chart bulanan akurat (query agregasi, timezone, null/zero handling) dan tampil responsif.
5) **Manajemen Anggota**: CRUD anggota dengan validasi, pencarian/paginasi, soft-delete atau status aktif/nonaktif.
6) **Simpan Pinjam**: alur setoran/penarikan, pengajuan pinjaman, persetujuan, pencairan, jadwal angsuran, penandaan terlambat, saldo terhitung konsisten.
7) **Penjualan & Stok**: CRUD produk, transaksi penjualan, stok minimal/alert, kalkulasi total & pajak/discount jika ada.
8) **Laporan**: laporan keuangan & operasional (periode custom), ekspor PDF/CSV bila dibutuhkan; pastikan konsistensi angka dengan sumber data.
9) **API/Internal Endpoint**: jika ada `public/api/`, definisikan kontrak, autentikasi, dan limitasi akses.
10) **UI/UX**: konsisten dengan Bootstrap 5; formulir jelas, notifikasi flash, state loading/error, dan mobile-friendly.
11) **Testing & QA**: uji manual alur utama, seed data contoh, cek error log, lakukan smoke test setelah setiap modul.

## Rencana Database
- **Skema inti**: `users`, `anggota`, `simpanan`, `pinjaman`, `angsuran`, `produk`, `penjualan`, `jurnal`, `settings`, `logs` (rujuk `docs/DEVELOPMENT_PLAN.md` dan `database/ksp_samosir.sql`).
- **Relasi utama**: users→anggota (1..n), anggota→simpanan (1..n), anggota→pinjaman (1..n), produk→penjualan (1..n); siapkan FK + indeks pada kolom pencarian/foreign key.
- **Integritas finansial**: gunakan transaksi untuk operasi simpanan/pinjaman/penjualan; jalur approval pinjaman; perhitungan bunga/denda; penandaan tunggakan; audit trail di `logs`.
- **Peran & akses data**: role-based access untuk pengurus/pengawas/anggota/agen/investor; data pelanggan umum tanpa akses modul internal; pisahkan data pemasok/mitra.
- **Kinerja & keamanan**: prepared statements, sanitasi input, batasan ukuran hasil (paginasi), indeks, backup/restore rutin, timezone konsisten (Asia/Jakarta).

## Peningkatan dari Referensi Eksternal (Best Practices)
- **Keamanan & Kepatuhan**: tambahkan MFA untuk login staf; 72-hour incident reporting SOP; rutin risk assessment memakai template (ACET/FFIEC-style checklist); incident response playbook per ancaman (phishing/ransomware); audit vendor/third-party secara berkala.
- **Data Protection**: enkripsi at-rest untuk field sensitif (minimal hashing + salt untuk kredensial, pertimbangkan libsodium/OpenSSL untuk data rahasia); TLS enforcement di produksi; pembatasan akses DB per role (least privilege); rotasi kredensial.
- **Audit & Logging**: perluasan `logs` dengan kategori keamanan (login fail, privilege change, data export); SIEM-friendly format; retensi & purge policy.
- **Operasional**: backup terjadwal + uji restore; monitoring availability & error rate; rate limiting login/form; training keamanan untuk role tinggi (teller/loan officer).
- **Privasi & Akses**: masking data PII di tampilan non-privileged; export/download guarded oleh izin dan watermarking/logging.
- **Kualitas & Kinerja**: indeks kolom pencarian utama; batasi query tanpa batasan; gunakan pagination dan caching ringan untuk dashboard.
- **MFA & Layered Security (FFIEC-aligned)**: lakukan risk assessment berkala untuk semua user (staf, anggota portal, pihak ketiga); terapkan MFA yang phishing-resistant di area sensitif; terapkan layered controls (prevent/detect/correct), monitoring & logging aktif untuk akses tidak sah; program edukasi keamanan berkelanjutan.

## Tampilan & Akses per Pengguna (FE/BE)
- **Pengurus**: FE dashboard keuangan/operasional, modul approval (pinjaman/PO), CRUD anggota-simpanan-pinjaman-produk, laporan ekspor; BE full RBA, workflow approval, audit trail, batas ekspor data.
- **Pengawas**: FE view-only dashboard/laporan/log audit, akses approve terbatas; BE read-mostly dengan audit ketat, notifikasi aktivitas sensitif.
- **Anggota (portal anggota)**: FE profil, status simpanan/pinjaman, riwayat bayar, pengajuan pinjaman, pembelian; BE scoped data-by-owner, rate limit, notifikasi jatuh tempo, perhitungan bunga/denda.
- **Agen/Reseller**: FE input penjualan, stok agen, komisi; BE otorisasi per agen, approval penjualan (jika agen pengurus/pengawas), kontrol konflik kepentingan, laporan per agen.
- **Investor**: FE ringkasan modal penyertaan & bagi hasil; BE kontrak/investasi, pembagian SHU/dividen, kepemilikan hybrid, compliance check.
- **Pelanggan umum/non-anggota**: FE katalog/checkout minimal; BE order intake, pembayaran, tracking pesanan, tanpa akses modul internal.
- **Pemasok/Vendor & Mitra**: FE onboarding & daftar PO/kontrak, penerimaan barang; BE master data pemasok/mitra, PO, penerimaan/pembayaran, evaluasi vendor, approval.
- **Kontrol akses silang**: navbar/menu dinamis sesuai role; middleware `requireLogin` + pengecekan role di controller; sembunyikan aksi tidak berhak; audit pada aksi sensitif (approval, ekspor, perubahan role).

## Reuse & Abstraksi Kode
- **Helper DB & Transaksi**: centralize `executeQuery/fetchRow/fetchAll/executeNonQuery` dan tambahkan wrapper transaksi (begin/commit/rollback) untuk alur finansial.
- **Base Controller**: siapkan kelas dasar yang memuat guard login/role, loader helper, response helper (redirect/flash/json) untuk dipakai semua controller.
- **Helper Umum**: konsolidasikan sanitasi, CSRF, flash message, date/number formatting; gunakan di semua form & view.
- **Komponen View**: partial layout (header/sidebar/navbar), card/table/form partial untuk CRUD, komponen notifikasi; menu dinamis berdasarkan role.
- **Audit Utility**: fungsi global/log service untuk catat aksi sensitif (login, approval, ekspor, perubahan role) agar konsisten lintas modul.

## Alur Aplikasi & Kontrol Akses/Data
- **Login**: form dengan CSRF + rate limit; verifikasi kredensial (hash), cek `is_active`, set session + role; log aktivitas; rencana MFA untuk staf.
- **Register**: jika diaktifkan, hanya publik/anggota; validasi identitas dasar; set status pending approval pengurus; kirim notifikasi.
- **Dashboard per role**: server-side filter menu dan data sesuai role (pengurus/pengawas/anggota/agen/investor/pelanggan/pemasok); kartu statistik hanya sesuai hak; hide aksi di UI + enforce di controller.
- **Hak akses**: middleware `requireLogin` + role check di controller; batas ekspor/download per role; audit untuk approval, ekspor, perubahan role.
- **Data per pengguna**: anggota/agen hanya melihat data milik sendiri; pengawas mostly-read; pengurus full-modify sesuai modul; investor melihat portofolio sendiri; pelanggan umum hanya order mereka.
- **Batas data per device/sesi**: paginasi default + LIMIT; rate limit API/form; batasi size payload (list/exports); timeout session 2 jam; opsi device cap (mis. sesi konkuren per user dibatasi) jika dibutuhkan.

## Risiko & Mitigasi
- **Integritas data finansial**: gunakan transaksi DB untuk operasi simpan/pinjam/penjualan; validasi saldo & batasan; audit log.
- **Keamanan**: CSRF/XSS/SQL injection; gunakan prepared statements, sanitasi, token CSRF, escape output.
- **Kinerja query**: indeks kolom pencarian/foreign key; batasi `LIMIT`+paginasi; caching ringan bila diperlukan.

## Keputusan Terbuka / Klarifikasi
- Apakah diperlukan role granular (admin/teller/anggota) dan pembatasan per fitur?
- Format laporan utama (PDF, CSV) dan periode default?
- Aturan bisnis pinjaman (bunga, tenor, denda terlambat) dan batas kredit per anggota?
- Kebijakan stok & penjualan (diskon, pajak, satuan harga)?
