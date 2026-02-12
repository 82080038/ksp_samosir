# RENCANA APLIKASI KOPERASI PEMASARAN KEPOLISIAN POLRES SAMOSIR

## DAFTAR ISI
1. [Analisis Dokumen Akta Pendirian](#analisis-dokumen-akta-pendirian)
2. [Modul Aplikasi Lengkap](#modul-aplikasi-lengkap)
3. [Arsitektur Teknis](#arsitektur-teknis)
4. [Struktur Database](#struktur-database)
5. [Prioritas Pengembangan](#prioritas-pengembangan)
6. [Kesimpulan](#kesimpulan)

---

## ANALISIS DOKUMEN AKTA PENDIRIAN

### Informasi Penting dari Dokumen

1. **Identitas Koperasi**
   - Nama: Koperasi Pemasaran Kepolisian Polres Samosir
   - Jenis: Koperasi Pemasaran
   - Tanggal Pendirian: 13 November 2025
   - Nomor Akta: 08

2. **Struktur Pengurus (Pengangkatan Pertama)**
   - Ketua: EDWARD SIDAURUK, SE
   - Wakil Ketua: SURUNG SAGALA
   - Sekretaris: BILMAR SITUMORANG
   - Wakil Sekretaris: AGUSTIAWAN SINAGA
   - Bendahara: MUHARRAM SYAHRI

3. **Struktur Pengawas**
   - Ketua Pengawas: TARMIZI LUBIS
   - Anggota Pengawas:
     - FERRY ARIANDY
     - DONAL P SITANGGANG
     - PENGEJAPEN
     - DARMONO SAMOSIR

4. **Ketentuan Penting dari AD/ART**
   - Pasal 47: Tata Cara Pengangkatan Pengurus
   - Pasal 88: Anggaran Rumah Tangga (ART) dan Peraturan Khusus
   - Sistem Sanksi: Teguran lisan, surat teguran tertulis, pemberhentian sementara
   - Pengawasan: Pengawas dapat menerbitkan SK pemberhentian sementara pengurus
   - Rapat Anggota: Pengesahan pengangkatan pengurus dilakukan dalam rapat anggota pertama setelah pengesahan akta

---

## MODUL APLIKASI LENGKAP

### A. MODUL CORE KOPERASI

#### A1. Modul Manajemen Anggota & Pengurus
- **Fitur:**
  - Data anggota lengkap (NIK, alamat, kontak, foto, status)
  - Data pengurus sesuai struktur (Ketua, Wakil Ketua, Sekretaris, Wakil Sekretaris, Bendahara)
  - Data pengawas (Ketua + 4 Anggota)
  - Status keanggotaan (aktif/non-aktif/keluar)
  - Riwayat keanggotaan
  - Pencarian dan filter
  - **Multiple Roles Support:**
    - Pengurus bisa juga menjadi investor/pemodal
    - Pengurus bisa juga menjadi agen/penjual
    - Pengurus bisa juga menjadi pembeli
    - Sistem tracking multiple roles per user
    - Conflict of interest handling (jika pengurus juga investor/agen)

#### A2. Modul Manajemen Simpanan
- **Fitur:**
  - Simpanan Pokok (sekali bayar saat pendaftaran)
  - Simpanan Wajib (berkala sesuai ketentuan)
  - Simpanan Sukarela
  - Pencatatan setoran dan penarikan
  - Riwayat simpanan per anggota
  - Laporan total simpanan

#### A3. Modul Manajemen Pinjaman
- **Fitur:**
  - Pengajuan pinjaman oleh anggota
  - Approval workflow (sesuai struktur pengurus)
  - Pencairan pinjaman
  - Penjadwalan angsuran
  - Pencatatan pembayaran angsuran
  - Perhitungan bunga dan denda
  - Monitoring tunggakan

#### A4. Modul Manajemen Rapat
- **Fitur:**
  - Pencatatan rapat anggota
  - Notulen rapat
  - Keputusan rapat
  - Daftar hadir
  - Agenda rapat
  - Arsip dokumen rapat

#### A5. Modul Manajemen Pengawasan
- **Fitur:**
  - Log aktivitas pengurus
  - Laporan pengawas
  - Sistem sanksi dan teguran
  - Pencatatan pelanggaran
  - SK pemberhentian sementara (jika diperlukan)
  - Audit trail

#### A6. Modul Perhitungan SHU
- **Fitur:**
  - Perhitungan SHU untuk anggota
  - SHU dari transaksi dengan anggota
  - SHU berdasarkan partisipasi anggota
  - Pembagian SHU sesuai AD/ART
  - Laporan SHU per anggota

---

### B. MODUL E-COMMERCE

#### B1. Modul Katalog Produk
- **Fitur:**
  - Manajemen produk (CRUD)
  - Kategori produk
  - Foto produk
  - Harga produk
  - Stok produk
  - Deskripsi produk

#### B2. Modul Pembelian oleh Anggota/Umum
- **Fitur:**
  - Katalog produk online
  - Pembelian oleh anggota (login)
  - **Pembelian oleh pengurus** (pengurus juga bisa menjadi pembeli)
  - **Pembelian oleh pengawas** (pengawas juga bisa menjadi pembeli)
  - Pembelian oleh umum (non-anggota)
  - Keranjang belanja (cart)
  - Checkout
  - Pilih metode pengambilan/pengiriman
  - Input alamat pengiriman
  - Pilih lokasi penjemputan
  - Pilih metode pembayaran
  - **Transparansi:**
    - Riwayat pembelian pengurus tercatat terpisah
    - Tidak ada diskon khusus untuk pengurus (kecuali sesuai kebijakan)

#### B3. Modul Manajemen Pesanan
- **Fitur:**
  - Daftar pesanan masuk
  - Status pesanan (pending, diproses, siap diambil, dikirim, selesai, dibatalkan)
  - Detail pesanan
  - Tracking pesanan
  - Konfirmasi pembayaran
  - Upload bukti pembayaran
  - Verifikasi pembayaran oleh admin

#### B4. Modul Pengiriman & Penjemputan Barang
- **Fitur:**
  - Metode pengambilan/pengiriman:
    - Ambil di Toko Koperasi
    - Ambil di Lokasi Pihak Ketiga
    - Dikirim ke Alamat Pelanggan
  - Manajemen lokasi
  - Manajemen pengiriman
  - Manajemen penjemputan
  - Status pengiriman/penjemputan

#### B5. Modul Penjualan oleh Agen/Reseller
- **Fitur:**
  - Input penjualan oleh agen
  - **Agen bisa berasal dari:**
    - Anggota koperasi
    - **Pengurus koperasi** (jika diizinkan)
    - **Pengawas koperasi** (jika diizinkan)
    - Pihak ketiga eksternal
  - Validasi penjualan
  - Pencatatan penjualan (tercatat sebagai penjualan koperasi)
  - Manajemen stok untuk agen
  - Invoice penjualan
  - Penerimaan pembayaran
  - Return dari pelanggan
  - **Conflict of Interest Management:**
    - Tracking jika pengurus juga agen
    - Approval khusus untuk penjualan oleh agen pengurus
    - Transparansi dalam komisi untuk agen pengurus
  - Laporan penjualan per agen

#### B6. Modul Konsinyasi
- **Fitur:**
  - Pengiriman barang ke agen (konsinyasi)
  - Stok di agen
  - Return barang dari agen
  - Laporan konsinyasi

---

### C. MODUL MANAJEMEN PEMASOK & MITRA

#### C1. Modul Manajemen Pemasok (Supplier/Vendor)
- **Fitur:**
  - Registrasi pemasok baru
  - Profil pemasok
  - Evaluasi kinerja pemasok
  - Kategori pemasok
  - Pencarian dan filter pemasok
  - Laporan pemasok

#### C2. Modul Manajemen Mitra/Pihak Ketiga
- **Fitur:**
  - Registrasi mitra kerjasama
  - Jenis mitra
  - Manajemen kontrak/kerjasama
  - Riwayat kerjasama
  - Laporan mitra

#### C3. Modul Purchase Order (PO) & Pembelian
- **Fitur:**
  - Pembuatan Purchase Order (PO)
  - Approval PO
  - Penerimaan barang (Goods Receipt)
  - Invoice dari pemasok
  - Pembayaran ke pemasok
  - Return barang ke pemasok
  - Laporan pembelian

#### C4. Modul Penjualan ke Pihak Ketiga
- **Fitur:**
  - Penjualan ke non-anggota
  - Penjualan ke mitra
  - Invoice penjualan
  - Penerimaan pembayaran dari pihak ketiga
  - Return dari pelanggan
  - Laporan penjualan

---

### D. MODUL MANAJEMEN INVESTOR

#### D1. Modul Manajemen Modal Penyertaan dari Pihak Ketiga
- **Fitur:**
  - Registrasi investor pihak ketiga
  - **Investor bisa berasal dari:**
    - Pihak ketiga eksternal (non-anggota)
    - Anggota koperasi
    - Pengurus koperasi (jika diizinkan)
    - Pengawas koperasi (jika diizinkan)
  - Pencatatan modal penyertaan
  - Manajemen perjanjian modal penyertaan
  - Tracking modal penyertaan
  - Batasan modal penyertaan
  - **Conflict of Interest Management:**
    - Tracking jika pengurus juga investor
    - Aturan khusus untuk pengurus yang menjadi investor
    - Transparansi dalam keputusan yang melibatkan investor pengurus
  - Laporan modal penyertaan

#### D2. Modul Pembagian Keuntungan (SHU & Dividen)
- **Fitur:**
  - Perhitungan SHU untuk anggota
  - Perhitungan dividen untuk investor pihak ketiga
  - Manajemen pembagian keuntungan
  - Pencatatan pembayaran
  - Laporan pembagian keuntungan

#### D3. Modul Struktur Kepemilikan Hybrid
- **Fitur:**
  - Struktur kepemilikan
  - Manajemen hak suara (jika ada)
  - Dashboard kepemilikan
  - Compliance monitoring

---

### E. MODUL KEUANGAN & AKUNTANSI

#### E1. Modul Manajemen Keuangan
- **Fitur:**
  - Pencatatan pemasukan dan pengeluaran
  - Rekonsiliasi bank
  - Neraca
  - Laba Rugi
  - Arus Kas
  - Anggaran dan monitoring

#### E2. Modul Sistem Akuntansi Lengkap
- **Fitur:**
  - Chart of Accounts (COA)
  - Jurnal Umum
  - Buku Besar (General Ledger)
  - Neraca Saldo (Trial Balance)
  - Jurnal Penyesuaian
  - Laporan Keuangan Standar

#### E3. Modul Manajemen Aset Tetap
- **Fitur:**
  - Registrasi aset tetap
  - Kategori aset
  - Depresiasi/Penyusutan
  - Pemeliharaan aset
  - Penjualan/Penghapusan aset
  - Laporan aset

#### E4. Modul Sistem Payroll/Gaji
- **Fitur:**
  - Data karyawan/pengurus/pengawas
  - Komponen gaji
  - Perhitungan gaji
  - Pencatatan pembayaran gaji
  - Laporan payroll

#### E5. Modul Sistem Perpajakan
- **Fitur:**
  - PPh (Pajak Penghasilan)
  - PPN (Pajak Pertambahan Nilai)
  - SPT (Surat Pemberitahuan Tahunan)
  - Bukti Potong Pajak
  - Laporan pajak

#### E6. Modul Manajemen Biaya Operasional
- **Fitur:**
  - Kategori biaya operasional
  - Setting biaya pengiriman
  - Setting biaya penjemputan
  - Perhitungan biaya otomatis
  - Pencatatan biaya operasional
  - Manajemen biaya lainnya
  - Laporan biaya operasional

---

### F. MODUL OPERASIONAL

#### F1. Modul Customer Service & Helpdesk
- **Fitur:**
  - Ticketing system
  - Chatbot AI (opsional)
  - FAQ (Frequently Asked Questions)
  - Laporan customer service

#### F2. Modul Return & Refund
- **Fitur:**
  - Pengajuan return/refund
  - Approval return
  - Proses refund
  - Tukar barang
  - Laporan return & refund

#### F3. Modul Garansi & Kualitas Produk
- **Fitur:**
  - Manajemen garansi produk
  - Klaim garansi
  - Manajemen kualitas produk
  - Laporan kualitas

#### F4. Modul Manajemen Risiko & Compliance
- **Fitur:**
  - Identifikasi risiko
  - Penilaian risiko
  - Monitoring compliance
  - Audit trail

#### F5. Modul Asuransi & Perlindungan
- **Fitur:**
  - Manajemen asuransi produk
  - Klaim asuransi
  - Laporan asuransi

---

### G. MODUL MARKETING & KOMUNIKASI

#### G1. Modul Marketing & Promosi
- **Fitur:**
  - Manajemen promo/diskon
  - Kupon/voucher
  - Email marketing
  - Laporan marketing

#### G2. Modul Sistem Reward/Loyalty/Poin
- **Fitur:**
  - Sistem poin
  - Program loyalitas
  - Redeem poin
  - Laporan reward

#### G3. Modul Berita & Pengumuman
- **Fitur:**
  - Manajemen berita
  - Manajemen pengumuman
  - Notifikasi
  - Laporan

#### G4. Modul Chat/Obrolan Internal
- **Fitur:**
  - Chat antar pengguna
  - Broadcast message
  - Notifikasi chat

---

### H. MODUL LAPORAN & ANALITIK

#### H1. Modul Dashboard & Analitik
- **Fitur:**
  - Dashboard pengurus (ringkasan keuangan, statistik anggota, grafik)
  - Dashboard anggota (simpanan, pinjaman, riwayat)
  - Dashboard investor (data investasi)
  - Grafik tren simpanan/pinjaman
  - Statistik keanggotaan

#### H2. Modul Laporan & Dashboard
- **Fitur:**
  - Laporan keuangan (bulanan/tahunan)
  - Laporan untuk Rapat Anggota Tahunan (RAT)
  - Laporan pengawasan
  - Ekspor laporan (PDF/Excel)
  - Laporan custom

#### H3. Modul Analitik & Business Intelligence (BI)
- **Fitur:**
  - Analitik penjualan
  - Analitik keuangan
  - Prediksi & forecasting
  - Laporan custom

---

### I. MODUL TEKNOLOGI & INTEGRASI

#### I1. Modul Integrasi API Pihak Ketiga
- **Fitur:**
  - Integrasi RajaOngkir
  - Integrasi Payment Gateway
  - Integrasi Bank
  - Integrasi E-Wallet
  - Integrasi Kurir
  - Setting API

#### I2. Modul QR Code
- **Fitur:**
  - Generate QR Code
  - Scan QR Code
  - Laporan QR Code

#### I3. Modul Point of Sale (POS)
- **Fitur:**
  - Kasir digital
  - Receipt printer
  - Laporan penjualan POS

#### I4. Modul Backup & Disaster Recovery
- **Fitur:**
  - Backup otomatis
  - Restore data
  - Disaster recovery plan
  - Monitoring backup

#### I5. Modul Manajemen Dokumentasi/Arsip
- **Fitur:**
  - Kategori dokumen
  - Upload & storage dokumen
  - Pencarian dokumen
  - Akses dokumen
  - Laporan

#### I6. Modul E-Modul/Pelatihan
- **Fitur:**
  - Materi pelatihan
  - Quiz/Test
  - Sertifikat

---

### J. MODUL KONFIGURASI & ADMINISTRASI

#### J1. Modul Konfigurasi & Administrasi
- **Fitur:**
  - Pengaturan koperasi (nama, alamat, kontak, logo)
  - Pengaturan bunga pinjaman
  - Pengaturan denda keterlambatan
  - Pengaturan simpanan wajib
  - Pengaturan periode SHU
  - Manajemen user & role
  - Backup & restore data
  - Template dokumen

#### J2. Modul Notifikasi & Pengingat
- **Fitur:**
  - Notifikasi jatuh tempo angsuran
  - Notifikasi simpanan wajib
  - Notifikasi persetujuan pinjaman
  - Pengumuman koperasi
  - Notifikasi via WhatsApp/SMS (opsional)

#### J3. Modul Keamanan & Akses
- **Fitur:**
  - Login multi-user
  - Role-based access control:
    - Super Admin (Ketua)
    - Admin (Pengurus)
    - Pengawas (read-only + approval)
    - Anggota (akses terbatas)
  - **Multiple Roles per User:**
    - Satu user bisa memiliki multiple roles (contoh: Pengurus + Investor + Agen + Pembeli)
    - Sistem permission berdasarkan kombinasi roles
    - Dashboard berbeda sesuai roles yang dimiliki
  - **Conflict of Interest Prevention:**
    - Alert jika pengurus melakukan transaksi sebagai investor/agen
    - Approval khusus untuk transaksi pengurus sebagai investor/agen
    - Logging semua aktivitas pengurus dalam multiple roles
  - Audit trail
  - Backup data otomatis

---

### K. MODUL ADVANCED (OPSIONAL)

#### K1. Modul Rekomendasi Produk (AI/ML - Opsional)
- **Fitur:**
  - Sistem rekomendasi produk
  - Personalisasi

#### K2. Modul Deteksi Penipuan (AI/ML - Opsional)
- **Fitur:**
  - Deteksi transaksi mencurigakan
  - Monitoring keamanan

#### K3. Modul Mobile App (Opsional - Fase Lanjutan)
- **Fitur:**
  - Mobile app untuk anggota
  - Mobile app untuk agen
  - Mobile app untuk pengurus

---

### L. MODUL SPESIAL: PROGRAM MAKAN BERGIZI GRATIS (MBG)

> **CATATAN OPERASIONAL:**
> Untuk detail prosedur operasional standar (SOP), job description, dan template dokumen fisik, silakan merujuk pada dokumen terpisah: **[MANUAL_OPERASIONAL_DAN_SOP.md](MANUAL_OPERASIONAL_DAN_SOP.md)**.
> Dokumen tersebut mencakup aspek kepatuhan terhadap regulasi (UU Koperasi, Permenkes Hygiene Sanitasi) dan integrasi alur kerja manual dengan sistem aplikasi ini.

#### L1. Modul Dapur & Operasional
- **Fitur:**
  - **Manajemen Stok Dapur:**
    - Pencatatan stok bahan makanan masuk dari koperasi (Integrasi dengan Modul Inventaris)
    - Monitoring penggunaan bahan baku harian (FIFO/FEFO)
    - Notifikasi stok kritis otomatis
    - Rekonsiliasi stok antara data sistem dan fisik (Stock Opname)
  - **Manajemen Produksi:**
    - Pengelolaan menu harian/mingguan (Siklus Menu)
    - Estimasi kebutuhan bahan baku berdasarkan menu dan jumlah porsi
    - Pelacakan waktu persiapan (Preparation Time) dan produksi (Cooking Time)
    - Log aktivitas dapur (Shift pagi/siang/sore)
  - **Audit Trail:**
    - Pencatatan seluruh transaksi keluar-masuk bahan makanan
    - Pelacakan batch produksi untuk traceability

#### L2. Modul Gizi & Menu
- **Fitur:**
  - **Analisis Nilai Gizi:**
    - Perhitungan nilai gizi per menu (Kalori, Protein, Lemak, Karbohidrat, Vitamin)
    - Database komposisi bahan makanan (DKBM)
  - **Perencanaan Diet:**
    - Penyesuaian menu berdasarkan kebutuhan diet khusus (Alergi, Diabetes, Rendah Garam, dll)
    - Analisis kecukupan gizi untuk populasi target (AKG)
  - **Pelaporan Gizi:**
    - Laporan asupan gizi harian/mingguan
    - Grafik tren pemenuhan gizi
    - Rekomendasi perbaikan menu

#### L3. Modul Distribusi Makanan
- **Fitur:**
  - **Penjadwalan & Tracking:**
    - Sistem penjadwalan distribusi ke unit/sekolah/penerima
    - Pelacakan jumlah paket makanan yang didistribusikan (Real-time)
    - Monitoring waktu keberangkatan dan kedatangan
  - **Verifikasi Penerimaan:**
    - Bukti terima digital (Foto & Tanda Tangan)
    - Verifikasi kondisi makanan saat diterima (Suhu, Kemasan, Kelayakan)
    - Input feedback langsung dari penerima
  - **Armada:**
    - Manajemen driver dan kendaraan distribusi

#### L4. Integrasi Sistem Inventaris Koperasi
- **Fitur:**
  - **Otomatisasi Pemesanan:**
    - Generate Purchase Request (PR) otomatis dari Dapur ke Koperasi saat stok minimum
    - Approval workflow untuk pengadaan bahan
  - **Traceability:**
    - Pelacakan asal bahan makanan dari supplier koperasi hingga ke piring penerima
  - **Laporan Terintegrasi:**
    - Laporan penggunaan bahan makanan periodik
    - Analisis efisiensi biaya per porsi (Food Costing)
    - Laporan sisa makanan (Food Waste)

#### L5. Pelaporan & Monitoring
- **Fitur:**
  - **Laporan Operasional:**
    - Laporan harian/mingguan/bulanan (Produksi, Distribusi, Stok)
    - Dashboard monitoring real-time untuk manajer
  - **Notifikasi System:**
    - Alert stok kritis
    - Alert keterlambatan distribusi
    - Alert penyimpangan standar gizi

---

## ARSITEKTUR TEKNIS

### Frontend
- HTML5 + CSS3 + Bootstrap 5 (mobile-first)
- jQuery untuk interaksi dan AJAX
- Responsif untuk desktop, tablet, dan mobile
- PWA (Progressive Web App - opsional)

### Backend
- PHP (native atau framework sederhana)
- RESTful API untuk komunikasi frontend-backend
- Session management untuk autentikasi
- Input validation & sanitization

### API

#### API Internal: RESTful API
- **Format:** JSON (JavaScript Object Notation)
- **Base URL:** `/api/v1/`
- **Authentication:** JWT Token / Session Token
- **Method:** GET, POST, PUT, PATCH, DELETE
- **Versioning:** v1, v2, dll

#### API Pihak Ketiga yang Diintegrasikan:
- **Payment Gateway:**
  - Midtrans API (Credit Card, VA, E-Wallet, QRIS)
  - Doku API (alternatif payment gateway)
  - Xendit API (payment gateway Indonesia)
- **Shipping/Logistics:**
  - RajaOngkir API (cek ongkir & tracking)
  - JNE API (tracking)
  - J&T API (tracking)
  - SiCepat API (tracking)
  - Pos Indonesia API (tracking)
- **Bank:**
  - Virtual Account API (BCA, Mandiri, BNI, BRI)
  - Rekonsiliasi otomatis (Flip API, Doku API)
- **E-Wallet:**
  - GoPay API
  - OVO API
  - DANA API
  - LinkAja API
- **SMS/WhatsApp:**
  - WhatsApp Business API (Twilio, Fonnte)
  - SMS Gateway API (Zenziva, Twilio)
- **Email:**
  - SMTP (Gmail, SendGrid, Mailgun)
- **Maps & Location:**
  - Google Maps API (lokasi, jarak, geocoding)
  - OpenStreetMap API (alternatif gratis)
- **QR Code:**
  - QR Code Generator API
- **Cloud Storage:**
  - Google Drive API (backup dokumen)
  - Dropbox API (backup dokumen)
  - AWS S3 API (file storage profesional)

### Database

#### Database Utama: MySQL 8.0 / MariaDB 10.6+
- **Engine:** InnoDB (untuk transaksi ACID)
- **Character Set:** utf8mb4 (mendukung emoji dan karakter khusus)
- **Collation:** utf8mb4_unicode_ci
- **Backup:** SQL Dump otomatis (harian/mingguan)
- **Cache:** Redis (opsional untuk session dan cache query)

#### Struktur Tabel Utama:
- **Manajemen User & Role:**
  - `users` - Data pengguna (anggota, pengurus, pengawas)
  - `roles` - Peran pengguna
  - `user_roles` - Multiple roles per user
  - `permissions` - Permission sistem
- **Koperasi Core:**
  - `anggota` - Data anggota lengkap
  - `pengurus` - Data pengurus
  - `pengawas` - Data pengawas
  - `simpanan_types` - Jenis simpanan
  - `simpanan_transactions` - Transaksi simpanan
  - `pinjaman` - Data pinjaman
  - `pinjaman_angsuran` - Jadwal & pembayaran angsuran
- **E-Commerce:**
  - `produk` - Katalog produk/jasa
  - `product_categories` - Kategori produk
  - `orders` - Pesanan
  - `order_details` - Detail pesanan
  - `cart` - Keranjang belanja
- **Pemasok & Mitra:**
  - `suppliers` - Data pemasok
  - `partners` - Data mitra
  - `contracts` - Kontrak kerjasama
  - `purchase_orders` - Purchase Order
  - `supplier_invoices` - Invoice pemasok
- **Investor:**
  - `investors` - Data investor
  - `capital_investments` - Modal penyertaan
  - `profit_distributions` - Pembagian keuntungan
  - `member_shu` - SHU anggota
  - `investor_dividends` - Dividen investor
- **Agen & Penjualan:**
  - `agents` - Data agen/reseller
  - `agent_sales` - Penjualan oleh agen
  - `agent_commissions` - Komisi agen
- **Keuangan & Akuntansi:**
  - `chart_of_accounts` - Chart of Accounts
  - `journal_entries` - Jurnal umum
  - `general_ledger` - Buku besar
  - `fixed_assets` - Aset tetap
  - `asset_depreciations` - Depresiasi aset
- **Operasional:**
  - `tickets` - Customer service tickets
  - `returns` - Return & refund
  - `warranties` - Garansi produk
  - `operational_costs` - Biaya operasional
- **Laporan & Log:**
  - `reports` - Laporan
  - `audit_logs` - Audit trail
  - `system_logs` - System logs
- **Konfigurasi:**
  - `config` - Konfigurasi sistem
  - `notifications` - Notifikasi
  - Dan banyak lagi sesuai kebutuhan modul

### Keamanan
- Password hashing (bcrypt)
- SQL injection prevention (prepared statements)
- XSS protection
- CSRF protection
- Input validation
- Role-based access control (RBAC)
- Session timeout
- SSL/TLS untuk komunikasi

---

## PRIORITAS PENGEMBANGAN

### Fase 1: MVP (Core Features) - 3-4 Bulan
1. Login & manajemen user dengan role
2. Manajemen anggota (CRUD)
3. Manajemen simpanan (pokok, wajib, sukarela)
4. Manajemen pinjaman (pengajuan, approval, angsuran)
5. Manajemen produk (katalog produk)
6. Pembelian oleh anggota/umum (basic)
7. Manajemen pesanan (basic)
8. Manajemen investor pihak ketiga (basic)
9. Manajemen modal penyertaan (basic)
10. Transaksi keuangan dasar
11. Laporan sederhana

### Fase 2: Fitur Operasional - 4-6 Bulan
1. Modul pemasaran lengkap
2. Purchase Order & pembelian dari pemasok
3. Invoice & pembayaran ke pemasok
4. Penjualan lengkap (langsung + online + oleh agen)
5. Sistem komisi otomatis
6. Manajemen pengiriman lengkap
7. Manajemen penjemputan lengkap
8. Manajemen biaya operasional
9. Customer service & helpdesk
10. Return & refund
11. Manajemen mitra & kontrak
12. Perhitungan SHU & dividen otomatis
13. Dashboard analitik
14. Manajemen rapat
15. Notifikasi & pengingat
16. Laporan keuangan lengkap

### Fase 3: Fitur Lanjutan - 3-4 Bulan
1. Sistem konsinyasi (jika diperlukan)
2. Evaluasi kinerja pemasok & agen
3. Manajemen pengawasan & sanksi
4. Compliance monitoring otomatis
5. Manajemen risiko
6. Garansi & kualitas produk
7. Marketing & promosi
8. Integrasi pembayaran digital
9. Aging report (hutang & piutang)
10. Advanced reporting & analytics
11. Integrasi dengan jasa kurir pihak ketiga
12. Dashboard untuk investor
13. Sistem akuntansi lengkap
14. Manajemen aset tetap
15. Sistem payroll
16. Sistem perpajakan

### Fase 4: Fitur Advanced (Opsional) - 2-3 Bulan
1. Sistem rekomendasi produk (AI/ML)
2. Deteksi penipuan (AI/ML)
3. Chatbot AI
4. Mobile app
5. Blockchain untuk transparansi (jika diperlukan)
6. Sistem reward/loyalty
7. Berita & pengumuman
8. Chat internal
9. E-modul/pelatihan

---

## MULTIPLE ROLES & FLEKSIBILITAS SISTEM

### Pengurus Bisa Memiliki Multiple Roles

**Ya, dalam aplikasi ini pengurus bisa memiliki multiple roles:**

1. **Pengurus sebagai Pemodal/Investor:**
   - Pengurus bisa menjadi investor dan menyertakan modal
   - Sistem akan tracking bahwa pengurus juga investor
   - Ada conflict of interest management untuk transparansi
   - Approval khusus untuk keputusan yang melibatkan pengurus sebagai investor

2. **Pengurus sebagai Penjual/Agen:**
   - Pengurus bisa menjadi agen/reseller
   - Penjualan oleh pengurus tetap tercatat sebagai penjualan koperasi
   - Komisi untuk pengurus sebagai agen
   - Transparansi dalam approval penjualan

3. **Pengurus sebagai Pembeli:**
   - Pengurus bisa membeli produk dari koperasi
   - Riwayat pembelian pengurus tercatat terpisah
   - Tidak ada diskon khusus kecuali sesuai kebijakan koperasi
   - Transparansi dalam transaksi pembelian pengurus

### Aturan Khusus Multiple Roles

- **Transparansi:** Semua transaksi pengurus dalam multiple roles dicatat dan bisa diaudit
- **Conflict of Interest:** Sistem akan alert jika ada potensi conflict of interest
- **Approval Khusus:** Transaksi pengurus sebagai investor/agen memerlukan approval khusus
- **Audit Trail:** Semua aktivitas pengurus dalam multiple roles tercatat lengkap
- **Laporan Terpisah:** Laporan untuk pengurus sebagai investor/agen/pembeli terpisah untuk transparansi

### Contoh Skenario

- **Ketua sebagai Investor + Pembeli:**
  - Ketua menyertakan modal sebagai investor
  - Ketua juga membeli produk dari koperasi
  - Sistem tracking kedua roles tersebut
  - Laporan terpisah untuk investasi dan pembelian

- **Bendahara sebagai Agen + Pembeli:**
  - Bendahara menjadi agen dan menjual produk
  - Bendahara juga membeli produk untuk kebutuhan pribadi
  - Komisi agen dan pembelian tercatat terpisah
  - Transparansi dalam semua transaksi

## KESIMPULAN

Rencana aplikasi ini mencakup:

- **48+ Modul Utama** yang saling terintegrasi
- **Model Bisnis Hybrid Lengkap:**
  - Koperasi (prinsip koperasi tetap dipertahankan)
  - E-commerce (platform online)
  - Investor pihak ketiga (modal penyertaan)
- **Fitur Operasional Lengkap:**
  - Manajemen anggota, simpanan, pinjaman
  - E-commerce lengkap
  - Manajemen pemasok & mitra
  - Manajemen investor
  - Customer service
  - Return & refund
  - Marketing & promosi
- **Multiple Roles Support:**
  - Pengurus bisa menjadi pemodal/investor
  - Pengurus bisa menjadi penjual/agen
  - Pengurus bisa menjadi pembeli
  - Sistem conflict of interest management
  - Transparansi penuh dalam multiple roles
- **Fitur Advanced (Opsional):**
  - AI/ML untuk rekomendasi & deteksi penipuan
  - Chatbot AI
  - Mobile app
- **Compliance & Keamanan:**
  - Manajemen risiko
  - Compliance monitoring
  - Audit trail
  - Keamanan data

Rencana ini siap untuk implementasi dan dapat disesuaikan sesuai kebutuhan spesifik koperasi.

---

**Dokumen ini dibuat untuk:**
Koperasi Pemasaran Kepolisian Polres Samosir

**Dibuat oleh:**
AIPDA PATRI SIHALOHO, SH

**Tanggal:** 2025

**Versi:** 1.0

# DATABASE DAN API UNTUK APLIKASI KOPERASI

## JENIS DATABASE YANG DIGUNAKAN

### 1. Database Utama: MySQL/MariaDB

**Pilihan:** MySQL 8.0 atau MariaDB 10.6+

**Alasan Pemilihan:**
- **Kompatibilitas:** Sangat kompatibel dengan PHP dan XAMPP
- **Stabilitas:** Sudah terbukti untuk aplikasi koperasi
- **Kemudahan:** Mudah diinstall dan dikonfigurasi
- **Komunitas:** Komunitas besar dan dokumentasi lengkap
- **Gratis:** Open source, tidak ada biaya lisensi
- **ACID Compliance:** Mendukung transaksi yang kompleks
- **Relational:** Cocok untuk data terstruktur koperasi

**Spesifikasi Database:**
- Engine: InnoDB (untuk transaksi ACID)
- Character Set: utf8mb4 (mendukung emoji dan karakter khusus)
- Collation: utf8mb4_unicode_ci
- Storage: MyISAM untuk tabel log/audit (opsional)

### 2. Database Backup: File-based Backup

**Jenis:**
- SQL Dump (mysqldump)
- File backup otomatis (harian/mingguan)

**Storage:**
- Local storage (server)
- Cloud storage (opsional: Google Drive, Dropbox, dll)

### 3. Database Cache (Opsional): Redis/Memcached

**Untuk:**
- Session storage
- Cache query yang sering digunakan
- Temporary data

**Pilihan:** Redis (lebih modern) atau Memcached

---

## STRUKTUR DATABASE

### Tabel Utama (Core Tables)

#### A. Tabel Manajemen User & Role
```sql
- users (id, nama, email, password, role_utama, is_investor, is_agen, is_pembeli, status, created_at, updated_at)
- roles (id, nama_role, deskripsi, permissions)
- user_roles (user_id, role_id, status, created_at)
- permissions (id, nama_permission, deskripsi)
- role_permissions (role_id, permission_id)
```

#### B. Tabel Koperasi Core
```sql
- anggota (id, user_id, nik, alamat, telepon, foto, status_keanggotaan, created_at, updated_at)
- pengurus (id, user_id, jabatan, periode_mulai, periode_akhir, status, created_at)
- pengawas (id, user_id, jabatan, periode_mulai, periode_akhir, status, created_at)
- simpanan_types (id, nama_simpanan, jenis, deskripsi, created_at)
- simpanan_transactions (id, anggota_id, simpanan_type_id, jumlah, tanggal, jenis_transaksi, created_at)
- pinjaman (id, anggota_id, jumlah_pinjaman, bunga, jangka_waktu, tanggal_pengajuan, status, approved_by, created_at)
- pinjaman_angsuran (id, pinjaman_id, angsuran_ke, jumlah_angsuran, tanggal_jatuh_tempo, tanggal_bayar, status, created_at)
```

#### C. Tabel E-Commerce
```sql
- produk (id, kode_produk, nama_produk, kategori_id, harga, stok, foto, deskripsi, status, created_at, updated_at)
- product_categories (id, nama_kategori, deskripsi, created_at)
- orders (id, nomor_order, customer_id, customer_type, tanggal_order, total_harga, total_biaya_operasional, total_bayar, metode_pengambilan, lokasi_pengambilan_id, alamat_pengiriman, metode_pembayaran, status_pembayaran, status_order, created_at, updated_at)
- order_details (id, order_id, produk_id, qty, harga_satuan, diskon, subtotal, created_at)
- cart (id, user_id, produk_id, qty, created_at, updated_at)
```

#### D. Tabel Pemasok & Mitra
```sql
- suppliers (id, nama_perusahaan, npwp, alamat, telepon, email, kategori, status, rating, created_at, updated_at)
- partners (id, nama_perusahaan, jenis_mitra, npwp, alamat, telepon, email, status, created_at, updated_at)
- contracts (id, partner_id, nomor_kontrak, tanggal_mulai, tanggal_berakhir, nilai_kontrak, syarat_ketentuan, dokumen_path, status, created_at, updated_at)
- purchase_orders (id, nomor_po, supplier_id, tanggal_po, tanggal_pengiriman, total_nilai, syarat_pembayaran, status, approved_by, created_at, updated_at)
- purchase_order_details (id, po_id, produk_id, qty, harga_satuan, subtotal, created_at)
- supplier_invoices (id, po_id, supplier_id, nomor_invoice, tanggal_invoice, total_nilai, status_pembayaran, tanggal_jatuh_tempo, created_at, updated_at)
```

#### E. Tabel Investor & Modal
```sql
- investors (id, nama, jenis, npwp, alamat, telepon, email, dokumen_path, status, created_at, updated_at)
- capital_investments (id, investor_id, nomor_perjanjian, besar_modal, tanggal_penyertaan, tanggal_berakhir, persentase_kepemilikan, syarat_ketentuan, dokumen_perjanjian_path, status, created_at, updated_at)
- capital_changes (id, investment_id, jenis, jumlah, tanggal, alasan, created_at)
- profit_distributions (id, periode, tanggal_distribusi, total_keuntungan, shu_anggota, dividen_investor, cadangan_koperasi, status, approved_by, created_at, updated_at)
- member_shu (id, distribution_id, member_id, shu_dari_transaksi, shu_dari_partisipasi, total_shu, status_pembayaran, tanggal_bayar, metode_pembayaran, bukti_pembayaran_path, created_at, updated_at)
- investor_dividends (id, distribution_id, investor_id, investment_id, persentase_dividen, jumlah_dividen, status_pembayaran, tanggal_bayar, metode_pembayaran, bukti_pembayaran_path, created_at, updated_at)
```

#### F. Tabel Agen & Penjualan
```sql
- agents (id, user_id, partner_id, jenis_agen, wilayah_penjualan, komisi_persen, batas_kredit, status, created_at, updated_at)
- agent_sales (id, agent_id, nomor_transaksi, tanggal_penjualan, pelanggan_nama, pelanggan_alamat, pelanggan_telp, total_nilai, komisi, status_approval, bukti_transaksi_path, created_by, created_at, updated_at)
- agent_sales_details (id, agent_sale_id, produk_id, qty, harga_jual, subtotal, komisi_item, created_at)
- agent_commissions (id, agent_id, agent_sale_id, periode, total_penjualan, total_komisi, status_pembayaran, tanggal_bayar, metode_pembayaran, bukti_pembayaran, created_at, updated_at)
```

#### G. Tabel Keuangan & Akuntansi
```sql
- chart_of_accounts (id, kode_akun, nama_akun, kategori, parent_id, saldo_awal, created_at, updated_at)
- journal_entries (id, tanggal, nomor_jurnal, deskripsi, created_by, created_at, updated_at)
- journal_entry_details (id, journal_entry_id, account_id, debit, kredit, created_at)
- general_ledger (id, account_id, tanggal, debit, kredit, saldo, reference_type, reference_id, created_at)
- fixed_assets (id, kode_aset, nama_aset, kategori, nilai_perolehan, tanggal_perolehan, metode_depresiasi, umur_ekonomis, nilai_buku, status, created_at, updated_at)
- asset_depreciations (id, asset_id, periode, nilai_depresiasi, nilai_buku_setelah, created_at)
```

#### H. Tabel Operasional
```sql
- tickets (id, customer_id, kategori, prioritas, subjek, deskripsi, status, assigned_to, resolved_at, created_at, updated_at)
- returns (id, order_id, alasan_return, status, keputusan, jumlah_refund, tanggal_refund, created_at, updated_at)
- warranties (id, produk_id, periode_garansi, syarat_ketentuan, created_at, updated_at)
- warranty_claims (id, warranty_id, order_id, alasan_klaim, status, created_at, updated_at)
- operational_costs (id, kategori_biaya, deskripsi, jumlah, order_id, tanggal, dibebankan_ke, approved_by, created_at, updated_at)
```

#### I. Tabel Laporan & Log
```sql
- reports (id, jenis_laporan, periode, data_json, file_path, created_by, created_at)
- audit_logs (id, user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent, created_at)
- system_logs (id, level, message, context, created_at)
```

#### J. Tabel Konfigurasi
```sql
- config (id, key, value, description, updated_at)
- email_templates (id, nama_template, subject, body, created_at, updated_at)
- notifications (id, user_id, tipe, pesan, status_baca, created_at)
```

#### K. Tabel MBG (Makan Bergizi Gratis)
```sql
- mbg_locations (id, nama_lokasi, tipe, alamat, kapasitas_produksi, penanggung_jawab_id, created_at)
- mbg_recipients (id, nama_penerima, tipe_instansi, alamat, jumlah_target, kontak_person, lokasi_id_supply, created_at)
- mbg_ingredients (id, nama_bahan, satuan, kategori, masa_simpan_hari, standar_gizi, created_at)
- mbg_menus (id, nama_menu, siklus_hari, total_kalori, deskripsi, status, created_at)
- mbg_menu_ingredients (id, menu_id, ingredient_id, qty_per_porsi, created_at)
- mbg_inventory (id, location_id, ingredient_id, qty_stok, min_stok, created_at, updated_at)
- mbg_batches (id, ingredient_id, inventory_id, batch_number, qty, tgl_produksi, tgl_kadaluwarsa, supplier_id, status_qc, created_at)
- mbg_production_plans (id, location_id, tgl_produksi, menu_id, target_porsi, status, created_at)
- mbg_qc_logs (id, production_id, stage, parameter, hasil_cek, petugas_qc_id, foto_bukti, waktu_cek, status, created_at)
- mbg_distributions (id, production_id, recipient_id, driver_id, vehicle_no, waktu_berangkat, waktu_sampai, qty_kirim, qty_terima, foto_bukti_terima, penerima_nama, status, created_at)
```

---

## JENIS API YANG DIGUNAKAN

### 1. API INTERNAL: RESTful API

**Teknologi:** PHP Native atau Framework Sederhana

**Format Data:** JSON (JavaScript Object Notation)

**Metode HTTP:**
- GET: Mengambil data
- POST: Membuat data baru
- PUT: Update data lengkap
- PATCH: Update data sebagian
- DELETE: Menghapus data

**Endpoint Structure:**
```
Base URL: https://koperasi.domain.com/api/v1/

Contoh Endpoint:
- GET    /api/v1/anggota              - Daftar anggota
- GET    /api/v1/anggota/{id}         - Detail anggota
- POST   /api/v1/anggota              - Tambah anggota
- PUT    /api/v1/anggota/{id}         - Update anggota
- DELETE /api/v1/anggota/{id}        - Hapus anggota

- GET    /api/v1/simpanan             - Daftar simpanan
- POST   /api/v1/simpanan              - Tambah simpanan
- GET    /api/v1/simpanan/{id}         - Detail simpanan

- GET    /api/v1/pinjaman             - Daftar pinjaman
- POST   /api/v1/pinjaman              - Pengajuan pinjaman
- GET    /api/v1/pinjaman/{id}        - Detail pinjaman
- PUT    /api/v1/pinjaman/{id}/approve - Approve pinjaman

- GET    /api/v1/produk               - Daftar produk
- GET    /api/v1/produk/{id}          - Detail produk
- POST   /api/v1/produk               - Tambah produk

- GET    /api/v1/orders               - Daftar pesanan
- POST   /api/v1/orders               - Buat pesanan
- GET    /api/v1/orders/{id}          - Detail pesanan
- PUT    /api/v1/orders/{id}/status   - Update status pesanan

- GET    /api/v1/reports/financial    - Laporan keuangan
- GET    /api/v1/reports/sales        - Laporan penjualan
- GET    /api/v1/reports/members      - Laporan anggota
```

**Authentication:**
- Token-based authentication (JWT atau Session Token)
- API Key untuk akses khusus
- OAuth 2.0 (opsional untuk integrasi eksternal)

**Response Format:**
```json
{
  "status": "success",
  "code": 200,
  "message": "Data berhasil diambil",
  "data": {
    // Data response
  },
  "pagination": {
    "page": 1,
    "per_page": 10,
    "total": 100,
    "total_pages": 10
  }
}
```

**Error Response Format:**
```json
{
  "status": "error",
  "code": 400,
  "message": "Error message",
  "errors": {
    "field_name": ["Error detail"]
  }
}
```

---

### 2. API PIHAK KETIGA YANG AKAN DIINTEGRASIKAN

#### A. Payment Gateway API

**1. Midtrans API**
- **Fungsi:** Payment gateway untuk transaksi online
- **Metode Pembayaran:**
  - Credit Card
  - Bank Transfer (VA - Virtual Account)
  - E-Wallet (GoPay, OVO, DANA, LinkAja, dll)
  - QRIS
  - Alfamart/Indomaret
- **Endpoint:** https://api.midtrans.com/v2/
- **Dokumentasi:** https://docs.midtrans.com/

**2. Doku API**
- **Fungsi:** Payment gateway alternatif
- **Metode Pembayaran:** Similar dengan Midtrans
- **Endpoint:** https://api.doku.com/

**3. Xendit API**
- **Fungsi:** Payment gateway untuk Indonesia
- **Metode Pembayaran:** Bank Transfer, E-Wallet, QRIS
- **Endpoint:** https://api.xendit.co/

#### B. Shipping/Logistics API

**1. RajaOngkir API**
- **Fungsi:** Cek ongkos kirim dan tracking pengiriman
- **Fitur:**
  - Cek tarif pengiriman (JNE, J&T, SiCepat, Pos Indonesia, dll)
  - Tracking pengiriman
  - Daftar kota/kecamatan
- **Endpoint:** https://api.rajaongkir.com/starter/ atau /pro/
- **Dokumentasi:** https://rajaongkir.com/dokumentasi

**2. API Kurir Langsung:**
- **JNE API:** Tracking dan cek tarif
- **J&T API:** Tracking dan cek tarif
- **SiCepat API:** Tracking dan cek tarif
- **Pos Indonesia API:** Tracking dan cek tarif

#### C. Bank API

**1. Virtual Account (VA) API**
- **Bank BCA:** BCA Virtual Account API
- **Bank Mandiri:** Mandiri Virtual Account API
- **Bank BNI:** BNI Virtual Account API
- **Bank BRI:** BRI Virtual Account API
- **Bank lainnya:** Sesuai kebutuhan

**2. Rekonsiliasi Otomatis:**
- **Flip API:** Untuk rekonsiliasi otomatis pembayaran
- **Doku API:** Rekonsiliasi pembayaran
- **Midtrans API:** Rekonsiliasi pembayaran

#### D. E-Wallet API

**1. GoPay API**
- **Fungsi:** Integrasi dengan GoPay
- **Fitur:** Payment, refund, status check

**2. OVO API**
- **Fungsi:** Integrasi dengan OVO
- **Fitur:** Payment, refund, status check

**3. DANA API**
- **Fungsi:** Integrasi dengan DANA
- **Fitur:** Payment, refund, status check

**4. LinkAja API**
- **Fungsi:** Integrasi dengan LinkAja
- **Fitur:** Payment, refund, status check

#### E. SMS/WhatsApp API

**1. WhatsApp Business API**
- **Fungsi:** Notifikasi via WhatsApp
- **Provider:**
  - Twilio WhatsApp API
  - WhatsApp Business API (resmi)
  - Wabox API
  - Fonnte API

**2. SMS Gateway API**
- **Fungsi:** Notifikasi via SMS
- **Provider:**
  - Zenziva API
  - SMS Gateway API
  - Twilio SMS API
  - Nexmo/Vonage API

#### F. Email API

**1. SMTP/Email Service**
- **Gmail SMTP:** Untuk email gratis
- **SendGrid API:** Email service profesional
- **Mailgun API:** Email service profesional
- **Amazon SES:** Email service AWS

#### G. Maps & Location API

**1. Google Maps API**
- **Fungsi:** 
  - Menampilkan lokasi toko koperasi
  - Menampilkan lokasi pihak ketiga
  - Menghitung jarak untuk ongkir
  - Geocoding (alamat ke koordinat)
- **Endpoint:** https://maps.googleapis.com/maps/api/

**2. OpenStreetMap API (Alternatif Gratis)**
- **Fungsi:** Similar dengan Google Maps
- **Gratis:** Tidak perlu API key berbayar

#### H. QR Code API

**1. QR Code Generator API**
- **Fungsi:** Generate QR Code untuk pembayaran/produk
- **Provider:**
  - QR Server API (gratis)
  - QR Code API (gratis)
  - Custom QR Code generator

#### I. Document/File Storage API

**1. Cloud Storage API**
- **Google Drive API:** Untuk backup dokumen
- **Dropbox API:** Untuk backup dokumen
- **AWS S3 API:** Untuk file storage profesional
- **Local Storage:** File disimpan di server lokal

---

## ARSITEKTUR API

### Struktur API Internal

```
/api/v1/
â”œâ”€â”€ auth/
â”‚   â”œâ”€â”€ login
â”‚   â”œâ”€â”€ logout
â”‚   â”œâ”€â”€ register
â”‚   â””â”€â”€ refresh-token
â”œâ”€â”€ anggota/
â”‚   â”œâ”€â”€ GET    /              - List anggota
â”‚   â”œâ”€â”€ GET    /{id}          - Detail anggota
â”‚   â”œâ”€â”€ POST   /              - Tambah anggota
â”‚   â”œâ”€â”€ PUT    /{id}          - Update anggota
â”‚   â””â”€â”€ DELETE /{id}          - Hapus anggota
â”œâ”€â”€ simpanan/
â”‚   â”œâ”€â”€ GET    /              - List simpanan
â”‚   â”œâ”€â”€ POST   /              - Tambah simpanan
â”‚   â””â”€â”€ GET    /anggota/{id}  - Simpanan per anggota
â”œâ”€â”€ pinjaman/
â”‚   â”œâ”€â”€ GET    /              - List pinjaman
â”‚   â”œâ”€â”€ POST   /              - Pengajuan pinjaman
â”‚   â”œâ”€â”€ PUT    /{id}/approve  - Approve pinjaman
â”‚   â””â”€â”€ GET    /anggota/{id}  - Pinjaman per anggota
â”œâ”€â”€ produk/
â”‚   â”œâ”€â”€ GET    /              - List produk
â”‚   â”œâ”€â”€ POST   /              - Tambah produk
â”‚   â”œâ”€â”€ PUT    /{id}          - Update produk
â”‚   â””â”€â”€ DELETE /{id}          - Hapus produk
â”œâ”€â”€ orders/
â”‚   â”œâ”€â”€ GET    /              - List pesanan
â”‚   â”œâ”€â”€ POST   /              - Buat pesanan
â”‚   â”œâ”€â”€ GET    /{id}          - Detail pesanan
â”‚   â””â”€â”€ PUT    /{id}/status   - Update status
â”œâ”€â”€ agen/
â”‚   â”œâ”€â”€ GET    /              - List agen
â”‚   â”œâ”€â”€ POST   /              - Registrasi agen
â”‚   â”œâ”€â”€ POST   /sales         - Input penjualan agen
â”‚   â””â”€â”€ GET    /commissions   - Komisi agen
â”œâ”€â”€ investor/
â”‚   â”œâ”€â”€ GET    /              - List investor
â”‚   â”œâ”€â”€ POST   /              - Registrasi investor
â”‚   â”œâ”€â”€ POST   /investment   - Pencatatan investasi
â”‚   â””â”€â”€ GET    /dividends     - Dividen investor
â”œâ”€â”€ reports/
â”‚   â”œâ”€â”€ GET    /financial     - Laporan keuangan
â”‚   â”œâ”€â”€ GET    /sales         - Laporan penjualan
â”‚   â”œâ”€â”€ GET    /members       - Laporan anggota
â”‚   â””â”€â”€ GET    /custom        - Laporan custom
â””â”€â”€ config/
    â”œâ”€â”€ GET    /              - Konfigurasi
    â””â”€â”€ PUT    /              - Update konfigurasi
```

### Rate Limiting

- **Limit:** 100 requests per menit per user
- **Burst:** 200 requests per menit
- **Method:** Token bucket algorithm

### API Versioning

- **Current Version:** v1
- **Format:** /api/v1/...
- **Future:** /api/v2/... (jika ada breaking changes)

---

## KEAMANAN API

### 1. Authentication
- **JWT Token:** Untuk stateless authentication
- **Session Token:** Untuk stateful authentication
- **API Key:** Untuk akses khusus pihak ketiga

### 2. Authorization
- **Role-based:** Berdasarkan role user
- **Permission-based:** Berdasarkan permission spesifik
- **Resource-based:** Berdasarkan ownership resource

### 3. Security Headers
- **HTTPS:** Wajib untuk semua request
- **CORS:** Configure untuk domain yang diizinkan
- **CSRF Protection:** Token untuk form submission
- **XSS Protection:** Input sanitization

### 4. Data Validation
- **Input Validation:** Validasi semua input
- **SQL Injection Prevention:** Prepared statements
- **XSS Prevention:** Output encoding

---

## INTEGRASI API PIHAK KETIGA

### Flow Integrasi Payment Gateway

```
1. User checkout â†’ Aplikasi
2. Aplikasi â†’ Midtrans API (create transaction)
3. Midtrans â†’ Return payment URL/token
4. User â†’ Redirect ke payment page Midtrans
5. User â†’ Bayar di Midtrans
6. Midtrans â†’ Webhook ke aplikasi (callback)
7. Aplikasi â†’ Update status pembayaran
8. Aplikasi â†’ Update status pesanan
```

### Flow Integrasi Shipping

```
1. User checkout â†’ Pilih pengiriman
2. Aplikasi â†’ RajaOngkir API (cek ongkir)
3. RajaOngkir â†’ Return tarif pengiriman
4. User â†’ Pilih kurir & bayar
5. Aplikasi â†’ Create shipment
6. Aplikasi â†’ RajaOngkir API (tracking)
7. RajaOngkir â†’ Return status pengiriman
```

---

## CONTOH IMPLEMENTASI API

### Contoh Request API Internal

```javascript
// Login
POST /api/v1/auth/login
{
  "email": "user@example.com",
  "password": "password123"
}

// Response
{
  "status": "success",
  "code": 200,
  "message": "Login berhasil",
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "user": {
      "id": 1,
      "nama": "John Doe",
      "email": "user@example.com",
      "roles": ["pengurus", "investor", "pembeli"]
    }
  }
}

// Get Daftar Anggota
GET /api/v1/anggota?page=1&per_page=10&search=john
Headers: Authorization: Bearer {token}

// Response
{
  "status": "success",
  "code": 200,
  "data": {
    "anggota": [
      {
        "id": 1,
        "nama": "John Doe",
        "nik": "1234567890",
        "alamat": "Jl. Contoh No. 123",
        "status": "aktif"
      }
    ]
  },
  "pagination": {
    "page": 1,
    "per_page": 10,
    "total": 50,
    "total_pages": 5
  }
}
```

### Contoh Integrasi Midtrans

```php
// Create Transaction
$midtrans = new Midtrans();
$transaction = $midtrans->createTransaction([
    'transaction_details' => [
        'order_id' => 'ORDER-123',
        'gross_amount' => 100000
    ],
    'customer_details' => [
        'first_name' => 'John',
        'email' => 'john@example.com'
    ]
]);

// Webhook Handler
POST /api/v1/payment/webhook/midtrans
{
  "transaction_status": "settlement",
  "order_id": "ORDER-123",
  "gross_amount": "100000"
}
```

### Contoh Integrasi RajaOngkir

```php
// Cek Ongkir
$rajaongkir = new RajaOngkir();
$ongkir = $rajaongkir->getCost([
    'origin' => 501, // Yogyakarta
    'destination' => 114, // Bandung
    'weight' => 1000, // 1 kg
    'courier' => 'jne'
]);

// Response
{
  "rajaongkir": {
    "results": [
      {
        "code": "jne",
        "name": "Jalur Nugraha Ekakurir (JNE)",
        "costs": [
          {
            "service": "OKE",
            "description": "Ongkos Kirim Ekonomis",
            "cost": [
              {
                "value": 15000,
                "etd": "2-3",
                "note": ""
              }
            ]
          }
        ]
      }
    ]
  }
}
```

---

## KESIMPULAN

### Database
- **Utama:** MySQL 8.0 / MariaDB 10.6+
- **Backup:** SQL Dump otomatis
- **Cache:** Redis (opsional)

### API Internal
- **Jenis:** RESTful API
- **Format:** JSON
- **Authentication:** JWT Token / Session Token
- **Versioning:** v1, v2, dll

### API Pihak Ketiga
- **Payment:** Midtrans, Doku, Xendit
- **Shipping:** RajaOngkir, JNE, J&T, SiCepat, Pos Indonesia
- **Bank:** Virtual Account (BCA, Mandiri, BNI, BRI)
- **E-Wallet:** GoPay, OVO, DANA, LinkAja
- **SMS/WhatsApp:** Twilio, Zenziva, Fonnte
- **Email:** SMTP, SendGrid, Mailgun
- **Maps:** Google Maps API
- **QR Code:** QR Code Generator API

Semua API akan diintegrasikan dengan sistem internal melalui RESTful API yang aman dan terstruktur.

# MULTIPLE ROLES PENGURUS DALAM APLIKASI KOPERASI

## PENJELASAN MULTIPLE ROLES

**Ya, dalam aplikasi ini pengurus bisa memiliki multiple roles (peran ganda):**

### 1. Pengurus sebagai Pemodal/Investor

- **Pengurus bisa menjadi investor** dan menyertakan modal ke koperasi
- Sistem akan tracking bahwa pengurus juga investor
- Ada **conflict of interest management** untuk transparansi
- **Approval khusus** untuk keputusan yang melibatkan pengurus sebagai investor
- Laporan investasi pengurus terpisah untuk transparansi

**Contoh:**
- Ketua menyertakan modal Rp 100 juta sebagai investor
- Sistem mencatat: Ketua = Pengurus + Investor
- Semua keputusan terkait investasi ketua memerlukan approval khusus
- Laporan investasi ketua terpisah dari laporan pengurus

### 2. Pengurus sebagai Penjual/Agen

- **Pengurus bisa menjadi agen/reseller** dan menjual produk koperasi
- Penjualan oleh pengurus tetap **tercatat sebagai penjualan koperasi**
- Komisi untuk pengurus sebagai agen
- **Transparansi** dalam approval penjualan
- Laporan penjualan agen pengurus terpisah

**Contoh:**
- Bendahara menjadi agen dan menjual produk senilai Rp 50 juta
- Sistem mencatat: Bendahara = Pengurus + Agen
- Komisi agen untuk bendahara dicatat terpisah
- Semua penjualan oleh bendahara sebagai agen memerlukan approval khusus

### 3. Pengurus sebagai Pembeli

- **Pengurus bisa membeli produk** dari koperasi
- Riwayat pembelian pengurus tercatat terpisah
- Tidak ada diskon khusus kecuali sesuai kebijakan koperasi
- **Transparansi** dalam transaksi pembelian pengurus
- Laporan pembelian pengurus terpisah

**Contoh:**
- Sekretaris membeli produk senilai Rp 5 juta
- Sistem mencatat: Sekretaris = Pengurus + Pembeli
- Riwayat pembelian sekretaris tercatat terpisah
- Tidak ada diskon khusus untuk sekretaris sebagai pengurus

## ATURAN KHUSUS MULTIPLE ROLES

### 1. Transparansi
- Semua transaksi pengurus dalam multiple roles dicatat dan bisa diaudit
- Laporan terpisah untuk setiap role yang dimiliki pengurus
- Dashboard berbeda untuk setiap role

### 2. Conflict of Interest Prevention
- Sistem akan **alert** jika ada potensi conflict of interest
- Contoh: Pengurus yang juga investor tidak bisa approve investasi sendiri
- Contoh: Pengurus yang juga agen tidak bisa approve komisi sendiri

### 3. Approval Khusus
- Transaksi pengurus sebagai investor/agen memerlukan **approval khusus**
- Approval dilakukan oleh pengurus lain atau pengawas
- Log semua approval tercatat untuk audit

### 4. Audit Trail
- Semua aktivitas pengurus dalam multiple roles tercatat lengkap
- Log mencatat: siapa, kapan, apa, dalam role apa
- Bisa ditelusuri untuk audit

### 5. Laporan Terpisah
- Laporan untuk pengurus sebagai investor terpisah
- Laporan untuk pengurus sebagai agen terpisah
- Laporan untuk pengurus sebagai pembeli terpisah
- Laporan gabungan untuk overview

## CONTOH SKENARIO MULTIPLE ROLES

### Skenario 1: Ketua sebagai Investor + Pembeli
- **Ketua menyertakan modal** Rp 100 juta sebagai investor
- **Ketua membeli produk** senilai Rp 10 juta untuk kebutuhan pribadi
- **Sistem tracking:**
  - Ketua = Pengurus + Investor + Pembeli
  - Laporan investasi: Rp 100 juta
  - Laporan pembelian: Rp 10 juta
  - Dashboard ketua menampilkan semua roles

### Skenario 2: Bendahara sebagai Agen + Pembeli
- **Bendahara menjadi agen** dan menjual produk senilai Rp 50 juta
- **Bendahara membeli produk** senilai Rp 5 juta untuk kebutuhan pribadi
- **Sistem tracking:**
  - Bendahara = Pengurus + Agen + Pembeli
  - Komisi agen: Rp 2.5 juta (5%)
  - Laporan pembelian: Rp 5 juta
  - Approval khusus untuk komisi agen bendahara

### Skenario 3: Sekretaris sebagai Investor + Agen + Pembeli
- **Sekretaris menyertakan modal** Rp 50 juta sebagai investor
- **Sekretaris menjadi agen** dan menjual produk senilai Rp 30 juta
- **Sekretaris membeli produk** senilai Rp 3 juta
- **Sistem tracking:**
  - Sekretaris = Pengurus + Investor + Agen + Pembeli
  - Laporan investasi: Rp 50 juta
  - Komisi agen: Rp 1.5 juta
  - Laporan pembelian: Rp 3 juta
  - Dashboard sekretaris menampilkan semua roles

## IMPLEMENTASI TEKNIS

### Database Structure
```sql
-- Tabel Users dengan Multiple Roles
users (
  id, nama, email, password, 
  role_utama (pengurus/pengawas/anggota),
  is_investor (boolean),
  is_agen (boolean),
  is_pembeli (boolean),
  created_at, updated_at
)

-- Tabel User Roles (Many-to-Many)
user_roles (
  user_id, role_type (investor/agen/pembeli),
  status, tanggal_aktif, created_at
)

-- Tabel Conflict of Interest Log
conflict_interest_logs (
  id, user_id, transaksi_id, jenis_transaksi,
  role_yang_terlibat, status, approved_by, created_at
)
```

### Fitur Sistem
1. **Role Assignment:**
   - Admin bisa assign multiple roles ke pengurus
   - Pengurus bisa request untuk menjadi investor/agen
   - Approval untuk multiple roles

2. **Dashboard Multiple Roles:**
   - Dashboard berbeda untuk setiap role
   - Overview dashboard untuk semua roles
   - Quick switch antara roles

3. **Conflict Detection:**
   - Sistem otomatis detect conflict of interest
   - Alert jika pengurus melakukan transaksi dalam multiple roles
   - Require approval khusus

4. **Reporting:**
   - Laporan per role
   - Laporan gabungan
   - Laporan conflict of interest

## KESIMPULAN

Aplikasi ini mendukung **multiple roles untuk pengurus**, sehingga:
- Pengurus bisa menjadi pemodal/investor
- Pengurus bisa menjadi penjual/agen
- Pengurus bisa menjadi pembeli
- Sistem mengelola conflict of interest
- Transparansi penuh dalam semua transaksi
- Audit trail lengkap untuk semua aktivitas

Ini memberikan fleksibilitas maksimal sambil menjaga transparansi dan akuntabilitas.

# RENCANA PENGEMBANGAN APLIKASI KOPERASI
## Koperasi Pemasaran Kepolisian Polres Samosir

---

## TIMELINE PENGEMBANGAN

### Fase 1: MVP (Minimum Viable Product) - 3-4 Bulan

**Tujuan:** Membuat aplikasi dasar yang dapat digunakan untuk operasional koperasi

**Modul yang Dikembangkan:**
1. **Sistem Autentikasi & User Management** (2 minggu)
   - Login/logout
   - Manajemen user dengan role
   - Multiple roles support
   - Password reset

2. **Manajemen Anggota** (2 minggu)
   - CRUD anggota
   - Upload foto
   - Status keanggotaan
   - Pencarian & filter

3. **Manajemen Simpanan** (2 minggu)
   - Simpanan Pokok
   - Simpanan Wajib
   - Simpanan Sukarela
   - Riwayat transaksi simpanan

4. **Manajemen Pinjaman** (3 minggu)
   - Pengajuan pinjaman
   - Approval workflow
   - Pencairan pinjaman
   - Penjadwalan angsuran
   - Pencatatan pembayaran angsuran

5. **Katalog Produk** (2 minggu)
   - CRUD produk
   - Kategori produk
   - Upload foto produk
   - Manajemen stok

6. **Pembelian Dasar** (2 minggu)
   - Pembelian oleh anggota/umum
   - Keranjang belanja
   - Checkout dasar
   - Manajemen pesanan dasar

7. **Manajemen Investor & Modal** (2 minggu)
   - Registrasi investor
   - Pencatatan modal penyertaan
   - Tracking modal

8. **Transaksi Keuangan Dasar** (2 minggu)
   - Pencatatan pemasukan/pengeluaran
   - Rekonsiliasi dasar

9. **Laporan Sederhana** (1 minggu)
   - Laporan anggota
   - Laporan simpanan
   - Laporan pinjaman
   - Laporan penjualan dasar

10. **Testing & Bug Fix** (2 minggu)
    - Unit testing
    - Integration testing
    - User acceptance testing
    - Bug fixing

**Total Durasi:** 20 minggu (5 bulan) - bisa dipercepat menjadi 3-4 bulan dengan tim yang lebih besar

---

### Fase 2: Fitur Operasional - 4-6 Bulan

**Tujuan:** Melengkapi fitur operasional untuk mendukung bisnis koperasi secara penuh

**Modul yang Dikembangkan:**

1. **Manajemen Pemasok** (2 minggu)
   - CRUD pemasok
   - Rating pemasok
   - Riwayat transaksi dengan pemasok

2. **Purchase Order & Procurement** (3 minggu)
   - Buat Purchase Order
   - Approval PO
   - Tracking PO
   - Penerimaan barang

3. **Invoice & Pembayaran Pemasok** (2 minggu)
   - Manajemen invoice pemasok
   - Pembayaran ke pemasok
   - Return barang ke pemasok

4. **Penjualan Lengkap** (3 minggu)
   - Penjualan langsung
   - Penjualan online (e-commerce)
   - Penjualan oleh agen/reseller
   - Tracking penjualan

5. **Manajemen Agen/Reseller** (2 minggu)
   - Registrasi agen
   - Manajemen agen
   - Input penjualan agen
   - Tracking penjualan agen

6. **Sistem Komisi Otomatis** (2 minggu)
   - Perhitungan komisi agen
   - Pencatatan komisi
   - Pembayaran komisi

7. **Manajemen Pengiriman** (2 minggu)
   - Integrasi RajaOngkir
   - Cek ongkir
   - Tracking pengiriman
   - Manajemen lokasi pengiriman

8. **Manajemen Penjemputan** (2 minggu)
   - Lokasi penjemputan
   - Manajemen penjemputan
   - Tracking penjemputan

9. **Manajemen Biaya Operasional** (2 minggu)
   - Kategori biaya
   - Setting biaya pengiriman/penjemputan
   - Pencatatan biaya operasional
   - Laporan biaya

10. **Customer Service & Helpdesk** (2 minggu)
    - Ticketing system
    - FAQ
    - Chat support dasar

11. **Return & Refund** (2 minggu)
    - Pengajuan return
    - Approval return
    - Proses refund

12. **Manajemen Mitra & Kontrak** (2 minggu)
    - CRUD mitra
    - Manajemen kontrak
    - Tracking kontrak

13. **Perhitungan SHU & Dividen** (3 minggu)
    - Perhitungan SHU otomatis
    - Perhitungan dividen investor
    - Pembagian keuntungan
    - Laporan pembagian

14. **Dashboard Analitik** (2 minggu)
    - Dashboard pengurus
    - Dashboard anggota
    - Dashboard investor
    - Grafik & statistik

15. **Manajemen Rapat** (2 minggu)
    - Pencatatan rapat
    - Notulen rapat
    - Daftar hadir
    - Arsip dokumen

16. **Notifikasi & Pengingat** (2 minggu)
    - Notifikasi email
    - Notifikasi SMS/WhatsApp
    - Pengingat jatuh tempo
    - Pengingat pembayaran

17. **Laporan Keuangan Lengkap** (3 minggu)
    - Neraca
    - Laba Rugi
    - Arus Kas
    - Laporan lainnya

18. **Testing & Bug Fix** (3 minggu)
    - Comprehensive testing
    - Performance testing
    - Security testing
    - Bug fixing

**Total Durasi:** 40 minggu (10 bulan) - bisa dipercepat menjadi 4-6 bulan dengan tim yang lebih besar

---

### Fase 3: Fitur Lanjutan - 3-4 Bulan

**Tujuan:** Menambahkan fitur lanjutan untuk meningkatkan efisiensi dan compliance

**Modul yang Dikembangkan:**

1. **Sistem Konsinyasi** (2 minggu)
   - Pengiriman barang ke agen (konsinyasi)
   - Tracking konsinyasi
   - Laporan konsinyasi

2. **Manajemen Pengawasan & Sanksi** (2 minggu)
   - Log aktivitas pengurus
   - Laporan pengawas
   - Sistem sanksi
   - SK pemberhentian

3. **Compliance Monitoring** (2 minggu)
   - Monitoring batasan modal penyertaan
   - Monitoring hak suara investor
   - Alert compliance

4. **Manajemen Risiko** (2 minggu)
   - Identifikasi risiko
   - Assessment risiko
   - Mitigasi risiko
   - Monitoring risiko

5. **Garansi & Kualitas Produk** (2 minggu)
   - Manajemen garansi
   - Klaim garansi
   - Tracking kualitas produk

6. **Marketing & Promosi** (2 minggu)
   - Kupon/diskon
   - Promosi produk
   - Campaign management

7. **Integrasi Pembayaran Digital** (3 minggu)
   - Integrasi Midtrans
   - Integrasi Doku
   - Integrasi E-Wallet
   - Virtual Account

8. **Aging Report** (1 minggu)
   - Hutang aging
   - Piutang aging
   - Laporan aging

9. **Advanced Reporting & Analytics** (2 minggu)
   - Custom report builder
   - Export ke Excel/PDF
   - Advanced analytics

10. **Integrasi Kurir Pihak Ketiga** (2 minggu)
    - Integrasi JNE, J&T, SiCepat
    - Tracking otomatis
    - Notifikasi pengiriman

11. **Dashboard Investor** (1 minggu)
    - Dashboard khusus investor
    - Laporan investasi
    - Laporan dividen

12. **Sistem Akuntansi Lengkap** (4 minggu)
    - Chart of Accounts
    - Jurnal Umum
    - Buku Besar
    - Neraca Saldo
    - Laporan Keuangan Standar

13. **Manajemen Aset Tetap** (2 minggu)
    - Registrasi aset
    - Depresiasi
    - Pemeliharaan aset
    - Laporan aset

14. **Sistem Payroll** (2 minggu)
    - Data karyawan
    - Perhitungan gaji
    - Pencatatan pembayaran gaji
    - Laporan payroll

15. **Sistem Perpajakan** (2 minggu)
    - PPh
    - PPN
    - SPT
    - Bukti Potong Pajak

16. **Testing & Bug Fix** (2 minggu)
    - Testing fitur baru
    - Integration testing
    - Bug fixing

**Total Durasi:** 33 minggu (8 bulan) - bisa dipercepat menjadi 3-4 bulan dengan tim yang lebih besar

---

### Fase 4: Fitur Advanced (Opsional) - 2-3 Bulan

**Tujuan:** Menambahkan fitur canggih untuk meningkatkan user experience dan efisiensi

**Modul yang Dikembangkan:**

1. **Sistem Rekomendasi Produk (AI/ML)** (3 minggu)
   - Machine learning untuk rekomendasi
   - Personalisasi produk
   - Collaborative filtering

2. **Deteksi Penipuan (AI/ML)** (3 minggu)
   - Deteksi transaksi mencurigakan
   - Pattern recognition
   - Alert otomatis

3. **Chatbot AI** (2 minggu)
   - Chatbot untuk customer service
   - Natural language processing
   - Auto-response

4. **Mobile App** (6 minggu)
   - Mobile app untuk anggota
   - Mobile app untuk agen
   - Mobile app untuk pengurus
   - Push notification

5. **Blockchain untuk Transparansi** (4 minggu)
   - Blockchain untuk transaksi penting
   - Smart contract (jika diperlukan)
   - Transparansi data

6. **Sistem Reward/Loyalty** (2 minggu)
   - Point system
   - Reward program
   - Loyalty tier

7. **Berita & Pengumuman** (1 minggu)
   - Manajemen berita
   - Pengumuman koperasi
   - Notifikasi berita

8. **Chat Internal** (2 minggu)
   - Chat antar pengurus
   - Chat dengan anggota
   - Group chat

9. **E-Modul/Pelatihan** (2 minggu)
   - Modul pelatihan online
   - Quiz & assessment
   - Sertifikat

10. **Testing & Bug Fix** (2 minggu)
    - Testing fitur advanced
    - Performance optimization
    - Bug fixing

**Total Durasi:** 27 minggu (7 bulan) - bisa dipercepat menjadi 2-3 bulan dengan tim yang lebih besar

---

## TOTAL TIMELINE

**Total Waktu Pengembangan:** 
- Fase 1: 3-4 bulan
- Fase 2: 4-6 bulan
- Fase 3: 3-4 bulan
- Fase 4: 2-3 bulan (opsional)

**Total:** 12-17 bulan (atau 15-21 bulan jika termasuk Fase 4)

**Catatan:** Timeline bisa dipercepat dengan:
- Tim yang lebih besar
- Parallel development
- Prioritas fitur yang lebih ketat
- MVP yang lebih minimal

---

## RESOURCE & TEAM STRUCTURE

### Tim Pengembangan Minimal (MVP)

1. **Project Manager** (1 orang)
   - Mengelola timeline
   - Koordinasi tim
   - Komunikasi dengan stakeholder

2. **Backend Developer** (1-2 orang)
   - PHP development
   - API development
   - Database design

3. **Frontend Developer** (1 orang)
   - HTML/CSS/Bootstrap
   - jQuery/AJAX
   - UI/UX implementation

4. **Database Administrator** (1 orang - bisa part-time)
   - Database design
   - Query optimization
   - Backup & recovery

5. **QA/Tester** (1 orang - bisa part-time)
   - Testing
   - Bug reporting
   - Quality assurance

6. **UI/UX Designer** (1 orang - bisa part-time)
   - Design mockup
   - User experience
   - Design system

**Total Tim Minimal:** 5-7 orang

### Tim Pengembangan Lengkap (Fase 2-4)

1. **Project Manager** (1 orang)
2. **Backend Developer** (2-3 orang)
3. **Frontend Developer** (2 orang)
4. **Mobile Developer** (1-2 orang - untuk Fase 4)
5. **Database Administrator** (1 orang)
6. **QA/Tester** (2 orang)
7. **UI/UX Designer** (1 orang)
8. **DevOps Engineer** (1 orang - untuk deployment)
9. **Business Analyst** (1 orang - bisa part-time)

**Total Tim Lengkap:** 12-15 orang

---

## BUDGET ESTIMASI

### Biaya Pengembangan (per bulan)

**Tim Minimal (MVP):**
- Project Manager: Rp 15.000.000/bulan
- Backend Developer: Rp 12.000.000/bulan x 2 = Rp 24.000.000/bulan
- Frontend Developer: Rp 10.000.000/bulan
- Database Administrator: Rp 8.000.000/bulan (part-time)
- QA/Tester: Rp 7.000.000/bulan (part-time)
- UI/UX Designer: Rp 8.000.000/bulan (part-time)

**Total per bulan:** Rp 72.000.000/bulan
**Total Fase 1 (4 bulan):** Rp 288.000.000

**Tim Lengkap (Fase 2-4):**
- Project Manager: Rp 15.000.000/bulan
- Backend Developer: Rp 12.000.000/bulan x 3 = Rp 36.000.000/bulan
- Frontend Developer: Rp 10.000.000/bulan x 2 = Rp 20.000.000/bulan
- Mobile Developer: Rp 12.000.000/bulan x 2 = Rp 24.000.000/bulan (Fase 4)
- Database Administrator: Rp 10.000.000/bulan
- QA/Tester: Rp 8.000.000/bulan x 2 = Rp 16.000.000/bulan
- UI/UX Designer: Rp 10.000.000/bulan
- DevOps Engineer: Rp 12.000.000/bulan
- Business Analyst: Rp 8.000.000/bulan (part-time)

**Total per bulan:** Rp 155.000.000/bulan
**Total Fase 2-4 (12 bulan):** Rp 1.860.000.000

### Biaya Infrastruktur & Tools

**Per Bulan:**
- Server Hosting: Rp 500.000 - Rp 2.000.000/bulan
- Domain: Rp 150.000/tahun
- SSL Certificate: Rp 500.000/tahun
- API Keys (Midtrans, RajaOngkir, dll): Rp 1.000.000 - Rp 5.000.000/bulan
- Cloud Storage (backup): Rp 200.000/bulan
- Development Tools (GitHub, dll): Rp 500.000/bulan

**Total Infrastruktur per bulan:** Rp 2.000.000 - Rp 8.000.000/bulan

**Per Tahun:** Rp 24.000.000 - Rp 96.000.000/tahun

### Biaya Maintenance (setelah launch)

**Per Bulan:**
- Maintenance Developer: Rp 10.000.000/bulan
- Server Hosting: Rp 500.000 - Rp 2.000.000/bulan
- API Keys: Rp 1.000.000 - Rp 5.000.000/bulan
- Backup & Storage: Rp 200.000/bulan
- Support & Bug Fix: Rp 5.000.000/bulan

**Total Maintenance per bulan:** Rp 16.700.000 - Rp 22.700.000/bulan

**Per Tahun:** Rp 200.400.000 - Rp 272.400.000/tahun

---

## TOTAL BUDGET ESTIMASI

### Pengembangan
- **Fase 1 (MVP):** Rp 288.000.000
- **Fase 2-4:** Rp 1.860.000.000
- **Total Pengembangan:** Rp 2.148.000.000

### Infrastruktur (selama pengembangan)
- **12-17 bulan:** Rp 24.000.000 - Rp 136.000.000

### Total Budget Pengembangan: Rp 2.172.000.000 - Rp 2.284.000.000

### Maintenance Tahunan: Rp 200.400.000 - Rp 272.400.000/tahun

---

## RISIKO & MITIGASI

### Risiko Teknis

1. **Risiko:** Keterlambatan pengembangan
   - **Mitigasi:** 
     - Timeline yang realistis
     - Buffer time di setiap fase
     - Prioritas fitur yang jelas

2. **Risiko:** Bug yang tidak terdeteksi
   - **Mitigasi:**
     - Testing yang komprehensif
     - Code review
     - User acceptance testing

3. **Risiko:** Performa aplikasi lambat
   - **Mitigasi:**
     - Database optimization
     - Caching strategy
     - Load testing

4. **Risiko:** Keamanan data
   - **Mitigasi:**
     - Security audit
     - Penetration testing
     - Regular security updates

### Risiko Bisnis

1. **Risiko:** Perubahan requirement
   - **Mitigasi:**
     - Requirement yang jelas di awal
     - Change management process
     - Agile methodology

2. **Risiko:** Budget overrun
   - **Mitigasi:**
     - Budget monitoring
     - Prioritas fitur
     - Scope management

3. **Risiko:** User adoption rendah
   - **Mitigasi:**
     - User training
     - User-friendly interface
     - Support yang baik

### Risiko Operasional

1. **Risiko:** Server down
   - **Mitigasi:**
     - Backup server
     - Monitoring system
     - Disaster recovery plan

2. **Risiko:** Data loss
   - **Mitigasi:**
     - Regular backup
     - Multiple backup locations
     - Backup testing

---

## METRIK KEBERHASILAN

### Metrik Teknis
- Uptime: > 99.5%
- Response time: < 2 detik
- Bug rate: < 1% dari total fitur
- Security: Zero critical vulnerabilities

### Metrik Bisnis
- User adoption: > 80% anggota aktif
- Transaction volume: Sesuai target koperasi
- User satisfaction: > 4.0/5.0
- Error rate: < 0.1%

### Metrik Operasional
- Support response time: < 24 jam
- Bug fix time: < 48 jam untuk critical bugs
- System availability: > 99.5%

---

## KESIMPULAN

Rencana pengembangan aplikasi koperasi ini dirancang secara bertahap dengan:
- **Timeline realistis:** 12-17 bulan untuk pengembangan lengkap
- **Budget yang jelas:** Rp 2.1-2.3 Miliar untuk pengembangan
- **Tim yang tepat:** 5-15 orang sesuai fase
- **Risiko yang teridentifikasi:** Dengan mitigasi yang jelas
- **Metrik keberhasilan:** Untuk monitoring progress

Dengan rencana ini, aplikasi koperasi dapat dikembangkan secara sistematis dan terukur.

# USE CASE & USER STORIES
## Aplikasi Koperasi Pemasaran Kepolisian Polres Samosir

---

## USE CASE DIAGRAM OVERVIEW

### Aktor Utama:
1. **Super Admin (Ketua)**
2. **Admin (Pengurus)**
3. **Pengawas**
4. **Anggota**
5. **Investor**
6. **Agen/Reseller**
7. **Pemasok**
8. **Umum (Non-Anggota)**

---

## USE CASE PER MODUL

### A. MODUL MANAJEMEN ANGGOTA & PENGURUS

#### UC-A1.1: Registrasi Anggota Baru
**Aktor:** Admin (Pengurus)
**Precondition:** Admin sudah login
**Main Flow:**
1. Admin membuka halaman "Tambah Anggota"
2. Admin mengisi data anggota (NIK, nama, alamat, telepon, email)
3. Admin upload foto anggota
4. Admin pilih status keanggotaan
5. Admin klik "Simpan"
6. Sistem validasi data
7. Sistem simpan data anggota
8. Sistem kirim notifikasi ke anggota (jika ada email)
9. Sistem tampilkan konfirmasi "Anggota berhasil ditambahkan"

**Alternative Flow:**
- 6a. Data tidak valid â†’ Sistem tampilkan error, kembali ke step 2
- 6b. NIK sudah terdaftar â†’ Sistem tampilkan error "NIK sudah terdaftar"

**Postcondition:** Data anggota baru tersimpan di database

---

#### UC-A1.2: Login sebagai Pengurus dengan Multiple Roles
**Aktor:** Pengurus (yang juga Investor + Agen + Pembeli)
**Precondition:** Pengurus sudah terdaftar dengan multiple roles
**Main Flow:**
1. Pengurus buka halaman login
2. Pengurus input email dan password
3. Pengurus klik "Login"
4. Sistem validasi credentials
5. Sistem cek roles pengurus
6. Sistem tampilkan dashboard dengan menu sesuai roles:
   - Menu Pengurus (manajemen anggota, simpanan, pinjaman)
   - Menu Investor (lihat investasi, dividen)
   - Menu Agen (input penjualan, komisi)
   - Menu Pembeli (belanja, riwayat pembelian)
7. Sistem log aktivitas login

**Alternative Flow:**
- 4a. Credentials salah â†’ Sistem tampilkan error "Email atau password salah"
- 4b. Akun nonaktif â†’ Sistem tampilkan error "Akun Anda nonaktif"

**Postcondition:** Pengurus berhasil login dan melihat dashboard sesuai roles

---

### B. MODUL SIMPANAN

#### UC-B1.1: Setor Simpanan Wajib
**Aktor:** Anggota
**Precondition:** Anggota sudah login, memiliki simpanan wajib yang belum dibayar
**Main Flow:**
1. Anggota buka halaman "Simpanan Saya"
2. Anggota lihat daftar simpanan wajib yang belum dibayar
3. Anggota klik "Bayar Simpanan Wajib"
4. Sistem tampilkan detail simpanan wajib (jumlah, jatuh tempo)
5. Anggota pilih metode pembayaran
6. Anggota klik "Bayar"
7. Sistem proses pembayaran
8. Sistem update status simpanan wajib menjadi "Lunas"
9. Sistem kirim notifikasi konfirmasi pembayaran
10. Sistem tampilkan konfirmasi "Simpanan wajib berhasil dibayar"

**Postcondition:** Simpanan wajib tercatat sebagai lunas, saldo simpanan anggota bertambah

---

#### UC-B1.2: Setor Simpanan Sukarela
**Aktor:** Anggota
**Precondition:** Anggota sudah login
**Main Flow:**
1. Anggota buka halaman "Simpanan Saya"
2. Anggota klik "Setor Simpanan Sukarela"
3. Anggota input jumlah yang ingin disetor
4. Anggota pilih metode pembayaran
5. Anggota klik "Setor"
6. Sistem validasi jumlah (minimal sesuai ketentuan)
7. Sistem proses pembayaran
8. Sistem catat transaksi simpanan sukarela
9. Sistem update saldo simpanan anggota
10. Sistem kirim notifikasi konfirmasi
11. Sistem tampilkan konfirmasi "Simpanan sukarela berhasil disetor"

**Alternative Flow:**
- 6a. Jumlah kurang dari minimum â†’ Sistem tampilkan error "Jumlah minimal Rp X"

**Postcondition:** Simpanan sukarela tercatat, saldo simpanan anggota bertambah

---

### C. MODUL PINJAMAN

#### UC-C1.1: Pengajuan Pinjaman
**Aktor:** Anggota
**Precondition:** Anggota sudah login, memiliki simpanan yang cukup sebagai jaminan
**Main Flow:**
1. Anggota buka halaman "Pinjaman"
2. Anggota klik "Ajukan Pinjaman"
3. Anggota input jumlah pinjaman
4. Sistem tampilkan maksimal pinjaman berdasarkan simpanan
5. Anggota pilih jangka waktu pinjaman
6. Sistem tampilkan perkiraan angsuran per bulan
7. Anggota upload dokumen pendukung (jika diperlukan)
8. Anggota klik "Ajukan Pinjaman"
9. Sistem validasi pengajuan
10. Sistem simpan pengajuan pinjaman dengan status "Menunggu Approval"
11. Sistem kirim notifikasi ke pengurus untuk approval
12. Sistem tampilkan konfirmasi "Pengajuan pinjaman berhasil dikirim"

**Alternative Flow:**
- 3a. Jumlah melebihi maksimal â†’ Sistem tampilkan error "Maksimal pinjaman Rp X"
- 9a. Anggota masih memiliki pinjaman aktif â†’ Sistem tampilkan error "Anda masih memiliki pinjaman aktif"

**Postcondition:** Pengajuan pinjaman tersimpan, menunggu approval pengurus

---

#### UC-C1.2: Approval Pinjaman oleh Pengurus
**Aktor:** Admin (Pengurus - Ketua/Bendahara)
**Precondition:** Ada pengajuan pinjaman yang menunggu approval
**Main Flow:**
1. Pengurus buka halaman "Approval Pinjaman"
2. Pengurus lihat daftar pengajuan pinjaman
3. Pengurus klik pengajuan pinjaman untuk melihat detail
4. Pengurus review data anggota, jumlah pinjaman, jangka waktu
5. Pengurus review dokumen pendukung
6. Pengurus pilih "Approve" atau "Reject"
7. Jika Approve:
   - Pengurus input catatan (opsional)
   - Pengurus klik "Setujui Pinjaman"
   - Sistem update status pinjaman menjadi "Disetujui"
   - Sistem buat jadwal angsuran otomatis
   - Sistem kirim notifikasi ke anggota
8. Jika Reject:
   - Pengurus input alasan penolakan
   - Pengurus klik "Tolak Pinjaman"
   - Sistem update status pinjaman menjadi "Ditolak"
   - Sistem kirim notifikasi ke anggota dengan alasan

**Postcondition:** Pinjaman sudah disetujui/ditolak, jadwal angsuran dibuat jika disetujui

---

### D. MODUL E-COMMERCE

#### UC-D1.1: Pembelian Produk oleh Anggota
**Aktor:** Anggota
**Precondition:** Anggota sudah login, ada produk tersedia
**Main Flow:**
1. Anggota buka halaman "Katalog Produk"
2. Anggota browse produk atau cari produk
3. Anggota klik produk untuk melihat detail
4. Anggota pilih jumlah yang ingin dibeli
5. Anggota klik "Tambah ke Keranjang"
6. Sistem tambahkan produk ke keranjang
7. Anggota klik "Keranjang" untuk melihat isi keranjang
8. Anggota klik "Checkout"
9. Sistem tampilkan form checkout:
   - Review produk di keranjang
   - Pilih metode pengambilan (ambil di toko / kirim / ambil di lokasi pihak ketiga)
   - Input alamat pengiriman (jika kirim)
   - Pilih lokasi penjemputan (jika ambil di lokasi pihak ketiga)
   - Pilih metode pembayaran
10. Anggota klik "Buat Pesanan"
11. Sistem validasi stok produk
12. Sistem hitung total harga + biaya operasional
13. Sistem buat pesanan dengan status "Menunggu Pembayaran"
14. Sistem kurangi stok produk
15. Sistem kirim notifikasi ke anggota
16. Sistem tampilkan konfirmasi "Pesanan berhasil dibuat"

**Alternative Flow:**
- 11a. Stok tidak cukup â†’ Sistem tampilkan error "Stok tidak mencukupi"
- 12a. Anggota pilih kirim â†’ Sistem hitung ongkir via RajaOngkir API

**Postcondition:** Pesanan dibuat, stok berkurang, menunggu pembayaran

---

#### UC-D1.2: Pembayaran Pesanan
**Aktor:** Anggota
**Precondition:** Ada pesanan yang menunggu pembayaran
**Main Flow:**
1. Anggota buka halaman "Pesanan Saya"
2. Anggota klik pesanan yang belum dibayar
3. Anggota klik "Bayar Sekarang"
4. Sistem redirect ke payment gateway (Midtrans)
5. Anggota pilih metode pembayaran di Midtrans
6. Anggota selesaikan pembayaran
7. Midtrans kirim webhook ke sistem
8. Sistem update status pembayaran menjadi "Lunas"
9. Sistem update status pesanan menjadi "Dibayar"
10. Sistem kirim notifikasi konfirmasi pembayaran ke anggota
11. Sistem proses pesanan (siapkan barang, kirim, dll)

**Alternative Flow:**
- 6a. Pembayaran gagal â†’ Sistem tetap tampilkan status "Menunggu Pembayaran"
- 6b. Pembayaran expired â†’ Sistem update status menjadi "Kadaluarsa"

**Postcondition:** Pembayaran tercatat, pesanan diproses

---

### E. MODUL AGEN/RESELLER

#### UC-E1.1: Input Penjualan oleh Agen
**Aktor:** Agen (yang juga Pengurus)
**Precondition:** Agen sudah login, memiliki akses sebagai agen
**Main Flow:**
1. Agen buka halaman "Penjualan Saya" (menu Agen)
2. Agen klik "Input Penjualan Baru"
3. Agen input data penjualan:
   - Nama pelanggan
   - Alamat pelanggan
   - Telepon pelanggan
   - Produk yang dijual
   - Jumlah produk
   - Harga jual
4. Sistem hitung total penjualan
5. Sistem hitung komisi agen otomatis
6. Agen upload bukti transaksi (jika ada)
7. Agen klik "Simpan Penjualan"
8. Sistem validasi data
9. Sistem simpan penjualan dengan status "Menunggu Approval" (karena agen adalah pengurus)
10. Sistem kirim notifikasi ke pengawas untuk approval
11. Sistem tampilkan konfirmasi "Penjualan berhasil diinput, menunggu approval"

**Alternative Flow:**
- 8a. Data tidak valid â†’ Sistem tampilkan error
- 9a. Jika agen bukan pengurus â†’ Sistem langsung approve dan catat sebagai penjualan koperasi

**Postcondition:** Penjualan tercatat, menunggu approval jika agen adalah pengurus

---

#### UC-E1.2: Approval Penjualan Agen oleh Pengawas
**Aktor:** Pengawas
**Precondition:** Ada penjualan agen yang menunggu approval (karena agen adalah pengurus)
**Main Flow:**
1. Pengawas buka halaman "Approval Penjualan Agen"
2. Pengawas lihat daftar penjualan yang menunggu approval
3. Pengawas klik penjualan untuk melihat detail
4. Pengawas review data penjualan, produk, harga
5. Pengawas review bukti transaksi
6. Pengawas cek apakah ada conflict of interest
7. Pengawas pilih "Approve" atau "Reject"
8. Jika Approve:
   - Pengawas klik "Setujui Penjualan"
   - Sistem update status menjadi "Disetujui"
   - Sistem catat sebagai penjualan koperasi
   - Sistem kurangi stok produk
   - Sistem hitung komisi agen
   - Sistem kirim notifikasi ke agen
9. Jika Reject:
   - Pengawas input alasan penolakan
   - Pengawas klik "Tolak Penjualan"
   - Sistem update status menjadi "Ditolak"
   - Sistem kirim notifikasi ke agen dengan alasan

**Postcondition:** Penjualan disetujui/ditolak, tercatat sebagai penjualan koperasi jika disetujui

---

### F. MODUL INVESTOR

#### UC-F1.1: Registrasi Investor oleh Pengurus
**Aktor:** Admin (Pengurus)
**Precondition:** Pengurus sudah login
**Main Flow:**
1. Pengurus buka halaman "Manajemen Investor"
2. Pengurus klik "Tambah Investor"
3. Pengurus input data investor:
   - Nama investor
   - Jenis investor (individu/perusahaan)
   - NPWP
   - Alamat
   - Telepon
   - Email
4. Pengurus upload dokumen investor
5. Pengurus klik "Simpan"
6. Sistem validasi data
7. Sistem simpan data investor
8. Sistem tampilkan konfirmasi "Investor berhasil ditambahkan"

**Postcondition:** Data investor tersimpan

---

#### UC-F1.2: Pencatatan Modal Penyertaan
**Aktor:** Admin (Pengurus)
**Precondition:** Investor sudah terdaftar
**Main Flow:**
1. Pengurus buka halaman "Modal Penyertaan"
2. Pengurus klik "Tambah Modal Penyertaan"
3. Pengurus pilih investor
4. Pengurus input data modal:
   - Besar modal
   - Tanggal penyertaan
   - Tanggal berakhir (jika ada)
   - Persentase kepemilikan
   - Syarat dan ketentuan
5. Pengurus upload dokumen perjanjian
6. Sistem validasi:
   - Total modal penyertaan tidak melebihi 25% dari modal koperasi
   - Persentase kepemilikan sesuai ketentuan
7. Jika valid:
   - Pengurus klik "Simpan"
   - Sistem simpan modal penyertaan
   - Sistem update total modal koperasi
   - Sistem kirim notifikasi ke investor
   - Sistem tampilkan konfirmasi "Modal penyertaan berhasil dicatat"
8. Jika tidak valid:
   - Sistem tampilkan error (misalnya "Modal penyertaan melebihi batas 25%")

**Alternative Flow:**
- 6a. Modal melebihi batas â†’ Sistem tampilkan error dan minta approval khusus
- 6b. Investor adalah pengurus â†’ Sistem kirim notifikasi ke pengawas untuk monitoring

**Postcondition:** Modal penyertaan tercatat, total modal koperasi terupdate

---

#### UC-F1.3: Pengurus sebagai Investor (Multiple Roles)
**Aktor:** Pengurus (yang juga Investor)
**Precondition:** Pengurus sudah terdaftar sebagai investor
**Main Flow:**
1. Pengurus login dengan multiple roles
2. Pengurus buka dashboard
3. Pengurus lihat menu "Investasi Saya" (karena juga investor)
4. Pengurus klik "Investasi Saya"
5. Sistem tampilkan:
   - Daftar investasi pengurus
   - Total investasi
   - Dividen yang diterima
   - Status investasi
6. Pengurus klik "Lihat Detail Investasi"
7. Sistem tampilkan detail investasi:
   - Besar modal
   - Tanggal investasi
   - Persentase kepemilikan
   - Dividen per periode
   - Riwayat dividen

**Postcondition:** Pengurus melihat informasi investasinya sendiri

**Catatan:** Sistem akan tracking bahwa pengurus juga investor untuk conflict of interest management

---

### G. MODUL PEMBAGIAN KEUNTUNGAN

#### UC-G1.1: Perhitungan SHU oleh Pengurus
**Aktor:** Admin (Pengurus - Bendahara)
**Precondition:** Periode pembagian SHU sudah tiba
**Main Flow:**
1. Pengurus buka halaman "Pembagian Keuntungan"
2. Pengurus klik "Hitung SHU"
3. Sistem tampilkan form:
   - Pilih periode
   - Total keuntungan koperasi
   - Persentase untuk SHU anggota
   - Persentase untuk cadangan koperasi
4. Pengurus input data sesuai AD/ART
5. Pengurus klik "Hitung"
6. Sistem hitung SHU per anggota:
   - SHU dari transaksi dengan anggota
   - SHU berdasarkan partisipasi anggota
   - Total SHU per anggota
7. Sistem tampilkan preview perhitungan SHU
8. Pengurus review perhitungan
9. Pengurus klik "Setujui Perhitungan"
10. Sistem simpan perhitungan SHU
11. Sistem kirim notifikasi ke anggota tentang SHU mereka
12. Sistem tampilkan konfirmasi "Perhitungan SHU berhasil"

**Postcondition:** SHU sudah dihitung dan dicatat, anggota mendapat notifikasi

---

#### UC-G1.2: Pembayaran SHU ke Anggota
**Aktor:** Admin (Pengurus - Bendahara)
**Precondition:** SHU sudah dihitung
**Main Flow:**
1. Pengurus buka halaman "Pembayaran SHU"
2. Pengurus lihat daftar anggota yang akan menerima SHU
3. Pengurus klik anggota untuk melihat detail SHU
4. Pengurus pilih metode pembayaran (transfer bank / tunai / tambah ke simpanan)
5. Pengurus input bukti pembayaran (jika transfer)
6. Pengurus klik "Bayar SHU"
7. Sistem update status pembayaran SHU menjadi "Sudah Dibayar"
8. Sistem catat transaksi pembayaran SHU
9. Sistem kirim notifikasi ke anggota
10. Sistem tampilkan konfirmasi "SHU berhasil dibayar"

**Postcondition:** SHU sudah dibayar, tercatat di sistem, anggota mendapat notifikasi

---

#### UC-G1.3: Perhitungan Dividen Investor
**Aktor:** Admin (Pengurus - Bendahara)
**Precondition:** Periode pembagian dividen sudah tiba
**Main Flow:**
1. Pengurus buka halaman "Pembagian Dividen"
2. Pengurus klik "Hitung Dividen"
3. Sistem tampilkan form:
   - Pilih periode
   - Total keuntungan untuk dividen
   - Persentase dividen sesuai perjanjian
4. Pengurus input data
5. Pengurus klik "Hitung"
6. Sistem hitung dividen per investor berdasarkan:
   - Besar modal penyertaan
   - Persentase dividen sesuai perjanjian
   - Total dividen per investor
7. Sistem tampilkan preview perhitungan dividen
8. Pengurus review perhitungan
9. Pengurus klik "Setujui Perhitungan"
10. Sistem simpan perhitungan dividen
11. Sistem kirim notifikasi ke investor tentang dividen mereka
12. Sistem tampilkan konfirmasi "Perhitungan dividen berhasil"

**Postcondition:** Dividen sudah dihitung dan dicatat, investor mendapat notifikasi

---

### H. MODUL LAPORAN

#### UC-H1.1: Generate Laporan Keuangan
**Aktor:** Admin (Pengurus - Bendahara)
**Precondition:** Pengurus sudah login
**Main Flow:**
1. Pengurus buka halaman "Laporan"
2. Pengurus klik "Laporan Keuangan"
3. Pengurus pilih jenis laporan:
   - Neraca
   - Laba Rugi
   - Arus Kas
   - Laporan lainnya
4. Pengurus pilih periode laporan
5. Pengurus klik "Generate Laporan"
6. Sistem generate laporan berdasarkan data transaksi
7. Sistem tampilkan laporan dalam format tabel/grafik
8. Pengurus klik "Export" untuk download (PDF/Excel)
9. Sistem download file laporan

**Postcondition:** Laporan keuangan berhasil di-generate dan bisa di-download

---

#### UC-H1.2: Laporan Penjualan per Agen
**Aktor:** Admin (Pengurus)
**Precondition:** Ada penjualan oleh agen
**Main Flow:**
1. Pengurus buka halaman "Laporan Penjualan"
2. Pengurus klik "Laporan Penjualan per Agen"
3. Pengurus pilih periode
4. Pengurus pilih agen (atau semua agen)
5. Pengurus klik "Generate"
6. Sistem generate laporan:
   - Total penjualan per agen
   - Komisi per agen
   - Status pembayaran komisi
   - Grafik penjualan
7. Sistem tampilkan laporan
8. Pengurus bisa export laporan

**Postcondition:** Laporan penjualan per agen berhasil di-generate

---

## USER STORIES

### Epic 1: Manajemen Anggota

**US-1.1:** Sebagai Admin, saya ingin menambahkan anggota baru agar data anggota tercatat dengan lengkap.
- **Acceptance Criteria:**
  - Admin bisa input data anggota (NIK, nama, alamat, telepon, email)
  - Admin bisa upload foto anggota
  - Sistem validasi NIK tidak duplikat
  - Sistem kirim notifikasi ke anggota setelah registrasi

**US-1.2:** Sebagai Anggota, saya ingin melihat data saya sendiri agar saya bisa memverifikasi kebenaran data.
- **Acceptance Criteria:**
  - Anggota bisa lihat profil sendiri setelah login
  - Anggota bisa lihat status keanggotaan
  - Anggota bisa lihat riwayat keanggotaan

**US-1.3:** Sebagai Pengurus, saya ingin memiliki multiple roles (investor, agen, pembeli) agar saya bisa berpartisipasi dalam berbagai kapasitas.
- **Acceptance Criteria:**
  - Pengurus bisa terdaftar sebagai investor
  - Pengurus bisa terdaftar sebagai agen
  - Pengurus bisa membeli produk sebagai pembeli
  - Sistem tracking multiple roles per user
  - Sistem conflict of interest management

---

### Epic 2: Simpanan

**US-2.1:** Sebagai Anggota, saya ingin membayar simpanan wajib saya agar saya memenuhi kewajiban sebagai anggota.
- **Acceptance Criteria:**
  - Anggota bisa lihat simpanan wajib yang belum dibayar
  - Anggota bisa bayar simpanan wajib online
  - Sistem update status setelah pembayaran
  - Sistem kirim konfirmasi pembayaran

**US-2.2:** Sebagai Anggota, saya ingin menyetor simpanan sukarela agar saya bisa menabung melalui koperasi.
- **Acceptance Criteria:**
  - Anggota bisa setor simpanan sukarela kapan saja
  - Anggota bisa pilih jumlah setoran (minimal sesuai ketentuan)
  - Sistem catat transaksi simpanan sukarela
  - Sistem update saldo simpanan anggota

---

### Epic 3: Pinjaman

**US-3.1:** Sebagai Anggota, saya ingin mengajukan pinjaman agar saya bisa mendapatkan dana untuk kebutuhan saya.
- **Acceptance Criteria:**
  - Anggota bisa ajukan pinjaman online
  - Sistem tampilkan maksimal pinjaman berdasarkan simpanan
  - Sistem tampilkan perkiraan angsuran
  - Sistem kirim notifikasi ke pengurus untuk approval

**US-3.2:** Sebagai Pengurus, saya ingin menyetujui atau menolak pengajuan pinjaman agar pinjaman dikelola dengan baik.
- **Acceptance Criteria:**
  - Pengurus bisa lihat daftar pengajuan pinjaman
  - Pengurus bisa review detail pengajuan
  - Pengurus bisa approve atau reject
  - Sistem buat jadwal angsuran otomatis jika approve
  - Sistem kirim notifikasi ke anggota

---

### Epic 4: E-Commerce

**US-4.1:** Sebagai Anggota, saya ingin membeli produk dari koperasi secara online agar saya bisa berbelanja dengan mudah.
- **Acceptance Criteria:**
  - Anggota bisa browse katalog produk
  - Anggota bisa cari produk
  - Anggota bisa tambah produk ke keranjang
  - Anggota bisa checkout
  - Anggota bisa pilih metode pengambilan (ambil/kirim)
  - Sistem hitung ongkir otomatis jika kirim

**US-4.2:** Sebagai Anggota, saya ingin membayar pesanan saya agar pesanan bisa diproses.
- **Acceptance Criteria:**
  - Anggota bisa lihat pesanan yang belum dibayar
  - Anggota bisa bayar via payment gateway
  - Sistem update status setelah pembayaran
  - Sistem kirim konfirmasi pembayaran

---

### Epic 5: Agen/Reseller

**US-5.1:** Sebagai Agen, saya ingin menginput penjualan saya agar penjualan tercatat dan saya mendapat komisi.
- **Acceptance Criteria:**
  - Agen bisa input penjualan online
  - Sistem hitung komisi otomatis
  - Sistem catat sebagai penjualan koperasi
  - Jika agen adalah pengurus, perlu approval pengawas

**US-5.2:** Sebagai Pengawas, saya ingin menyetujui penjualan agen yang juga pengurus agar transparansi terjaga.
- **Acceptance Criteria:**
  - Pengawas bisa lihat penjualan agen pengurus yang menunggu approval
  - Pengawas bisa review detail penjualan
  - Pengawas bisa approve atau reject
  - Sistem catat sebagai penjualan koperasi jika approve

---

### Epic 6: Investor

**US-6.1:** Sebagai Pengurus, saya ingin mencatat modal penyertaan investor agar modal koperasi tercatat dengan benar.
- **Acceptance Criteria:**
  - Pengurus bisa catat modal penyertaan
  - Sistem validasi batasan modal penyertaan (maks 25%)
  - Sistem update total modal koperasi
  - Sistem kirim notifikasi ke investor

**US-6.2:** Sebagai Investor (yang juga Pengurus), saya ingin melihat investasi saya agar saya tahu status investasi.
- **Acceptance Criteria:**
  - Investor pengurus bisa lihat investasinya sendiri
  - Investor bisa lihat dividen yang diterima
  - Sistem tracking bahwa investor adalah pengurus

---

### Epic 7: Pembagian Keuntungan

**US-7.1:** Sebagai Pengurus, saya ingin menghitung SHU anggota agar pembagian keuntungan adil.
- **Acceptance Criteria:**
  - Pengurus bisa hitung SHU per periode
  - Sistem hitung SHU berdasarkan transaksi dan partisipasi
  - Sistem tampilkan preview perhitungan
  - Sistem kirim notifikasi ke anggota

**US-7.2:** Sebagai Anggota, saya ingin melihat SHU saya agar saya tahu berapa keuntungan yang saya terima.
- **Acceptance Criteria:**
  - Anggota bisa lihat SHU yang diterima
  - Anggota bisa lihat detail perhitungan SHU
  - Anggota bisa lihat status pembayaran SHU

---

## KESIMPULAN

Dokumen Use Case dan User Stories ini menjelaskan:
- **Use Case:** Detail alur penggunaan aplikasi per modul
- **User Stories:** Kebutuhan user dalam format story
- **Acceptance Criteria:** Kriteria penerimaan untuk setiap fitur

Dokumen ini akan menjadi acuan untuk:
- Development team dalam membuat fitur
- QA team dalam testing
- Product owner dalam validasi fitur
- User dalam memahami cara menggunakan aplikasi

# TESTING PLAN
## Aplikasi Koperasi Pemasaran Kepolisian Polres Samosir

---

## STRATEGI TESTING

### 1. Testing Levels

#### 1.1 Unit Testing
**Tujuan:** Menguji setiap fungsi/unit code secara individual
**Scope:**
- Setiap function/method di backend
- Setiap function di frontend (jQuery)
- Database queries
- API endpoints

**Tools:**
- PHPUnit (untuk PHP)
- Jest (untuk JavaScript - opsional)
- Manual testing untuk jQuery functions

**Coverage Target:** > 80%

---

#### 1.2 Integration Testing
**Tujuan:** Menguji integrasi antar komponen
**Scope:**
- Integrasi frontend-backend
- Integrasi dengan database
- Integrasi dengan API pihak ketiga (Midtrans, RajaOngkir, dll)
- Integrasi antar modul

**Tools:**
- Postman (untuk API testing)
- Selenium (untuk UI testing - opsional)
- Manual testing

---

#### 1.3 System Testing
**Tujuan:** Menguji sistem secara keseluruhan
**Scope:**
- End-to-end testing setiap modul
- Performance testing
- Security testing
- Compatibility testing

**Tools:**
- Manual testing
- Browser DevTools (untuk performance)
- Security scanning tools

---

#### 1.4 User Acceptance Testing (UAT)
**Tujuan:** Validasi bahwa aplikasi sesuai kebutuhan user
**Scope:**
- Testing oleh pengurus koperasi
- Testing oleh anggota
- Testing oleh investor
- Testing oleh agen

**Participants:**
- Stakeholder koperasi
- End users

---

## TESTING MATRIX PER MODUL

### A. MODUL MANAJEMEN ANGGOTA & PENGURUS

#### Test Cases:

**TC-A1.1: Tambah Anggota Baru**
- **Precondition:** Admin sudah login
- **Steps:**
  1. Buka halaman "Tambah Anggota"
  2. Input data anggota lengkap
  3. Upload foto
  4. Klik "Simpan"
- **Expected Result:** Anggota berhasil ditambahkan, data tersimpan di database
- **Test Data:** Data anggota valid dan invalid

**TC-A1.2: Login dengan Multiple Roles**
- **Precondition:** User memiliki multiple roles (Pengurus + Investor + Agen)
- **Steps:**
  1. Login dengan email dan password
  2. Lihat dashboard
- **Expected Result:** Dashboard menampilkan menu sesuai semua roles yang dimiliki
- **Test Data:** User dengan kombinasi roles berbeda

**TC-A1.3: Edit Data Anggota**
- **Precondition:** Ada data anggota di database
- **Steps:**
  1. Buka halaman "Daftar Anggota"
  2. Klik "Edit" pada anggota tertentu
  3. Ubah data
  4. Klik "Simpan"
- **Expected Result:** Data anggota berhasil diupdate
- **Test Data:** Data valid dan invalid

**TC-A1.4: Hapus Anggota**
- **Precondition:** Ada data anggota di database
- **Steps:**
  1. Buka halaman "Daftar Anggota"
  2. Klik "Hapus" pada anggota tertentu
  3. Konfirmasi penghapusan
- **Expected Result:** Anggota berhasil dihapus (soft delete), status menjadi "Keluar"
- **Test Data:** Anggota dengan dan tanpa transaksi aktif

---

### B. MODUL SIMPANAN

#### Test Cases:

**TC-B1.1: Setor Simpanan Pokok**
- **Precondition:** Anggota baru terdaftar
- **Steps:**
  1. Login sebagai admin
  2. Buka halaman anggota baru
  3. Klik "Setor Simpanan Pokok"
  4. Input jumlah
  5. Pilih metode pembayaran
  6. Klik "Bayar"
- **Expected Result:** Simpanan pokok tercatat, saldo simpanan anggota bertambah
- **Test Data:** Jumlah valid dan invalid

**TC-B1.2: Setor Simpanan Wajib**
- **Precondition:** Anggota sudah login, ada simpanan wajib yang belum dibayar
- **Steps:**
  1. Login sebagai anggota
  2. Buka halaman "Simpanan Saya"
  3. Klik "Bayar Simpanan Wajib"
  4. Pilih metode pembayaran
  5. Klik "Bayar"
- **Expected Result:** Simpanan wajib tercatat sebagai lunas
- **Test Data:** Simpanan wajib dengan berbagai status

**TC-B1.3: Setor Simpanan Sukarela**
- **Precondition:** Anggota sudah login
- **Steps:**
  1. Login sebagai anggota
  2. Buka halaman "Simpanan Saya"
  3. Klik "Setor Simpanan Sukarela"
  4. Input jumlah (minimal sesuai ketentuan)
  5. Pilih metode pembayaran
  6. Klik "Setor"
- **Expected Result:** Simpanan sukarela tercatat, saldo bertambah
- **Test Data:** Jumlah di atas dan di bawah minimum

**TC-B1.4: Tarik Simpanan Sukarela**
- **Precondition:** Anggota memiliki saldo simpanan sukarela
- **Steps:**
  1. Login sebagai anggota
  2. Buka halaman "Simpanan Saya"
  3. Klik "Tarik Simpanan Sukarela"
  4. Input jumlah yang ingin ditarik
  5. Klik "Tarik"
- **Expected Result:** Simpanan sukarela berkurang, transaksi tercatat
- **Test Data:** Jumlah di bawah dan di atas saldo

---

### C. MODUL PINJAMAN

#### Test Cases:

**TC-C1.1: Pengajuan Pinjaman**
- **Precondition:** Anggota sudah login, memiliki simpanan yang cukup
- **Steps:**
  1. Login sebagai anggota
  2. Buka halaman "Pinjaman"
  3. Klik "Ajukan Pinjaman"
  4. Input jumlah pinjaman
  5. Pilih jangka waktu
  6. Upload dokumen pendukung
  7. Klik "Ajukan"
- **Expected Result:** Pengajuan pinjaman tersimpan dengan status "Menunggu Approval"
- **Test Data:** Jumlah di bawah dan di atas maksimal pinjaman

**TC-C1.2: Approval Pinjaman**
- **Precondition:** Ada pengajuan pinjaman yang menunggu approval
- **Steps:**
  1. Login sebagai pengurus
  2. Buka halaman "Approval Pinjaman"
  3. Klik pengajuan untuk melihat detail
  4. Klik "Approve"
  5. Input catatan (opsional)
  6. Klik "Setujui"
- **Expected Result:** Pinjaman disetujui, jadwal angsuran dibuat otomatis
- **Test Data:** Pengajuan dengan berbagai kondisi

**TC-C1.3: Reject Pinjaman**
- **Precondition:** Ada pengajuan pinjaman yang menunggu approval
- **Steps:**
  1. Login sebagai pengurus
  2. Buka halaman "Approval Pinjaman"
  3. Klik pengajuan untuk melihat detail
  4. Klik "Reject"
  5. Input alasan penolakan
  6. Klik "Tolak"
- **Expected Result:** Pinjaman ditolak, anggota mendapat notifikasi dengan alasan
- **Test Data:** Pengajuan dengan berbagai kondisi

**TC-C1.4: Bayar Angsuran**
- **Precondition:** Ada pinjaman dengan angsuran yang jatuh tempo
- **Steps:**
  1. Login sebagai anggota
  2. Buka halaman "Pinjaman Saya"
  3. Klik pinjaman aktif
  4. Klik "Bayar Angsuran"
  5. Pilih angsuran yang akan dibayar
  6. Pilih metode pembayaran
  7. Klik "Bayar"
- **Expected Result:** Angsuran tercatat sebagai lunas, sisa pokok pinjaman berkurang
- **Test Data:** Angsuran tepat waktu dan terlambat

---

### D. MODUL E-COMMERCE

#### Test Cases:

**TC-D1.1: Browse Katalog Produk**
- **Precondition:** Ada produk di database
- **Steps:**
  1. Buka halaman "Katalog Produk"
  2. Browse produk
  3. Cari produk dengan keyword
  4. Filter produk berdasarkan kategori
- **Expected Result:** Produk ditampilkan dengan benar, search dan filter berfungsi
- **Test Data:** Produk dengan berbagai kategori dan status

**TC-D1.2: Tambah Produk ke Keranjang**
- **Precondition:** User sudah login, ada produk tersedia
- **Steps:**
  1. Buka halaman produk
  2. Pilih jumlah yang ingin dibeli
  3. Klik "Tambah ke Keranjang"
  4. Buka halaman keranjang
- **Expected Result:** Produk berhasil ditambahkan ke keranjang
- **Test Data:** Produk dengan stok cukup dan tidak cukup

**TC-D1.3: Checkout Pesanan**
- **Precondition:** Ada produk di keranjang
- **Steps:**
  1. Buka halaman keranjang
  2. Klik "Checkout"
  3. Pilih metode pengambilan
  4. Input alamat pengiriman (jika kirim)
  5. Pilih metode pembayaran
  6. Klik "Buat Pesanan"
- **Expected Result:** Pesanan berhasil dibuat, stok produk berkurang
- **Test Data:** Berbagai metode pengambilan dan pembayaran

**TC-D1.4: Pembayaran via Payment Gateway**
- **Precondition:** Ada pesanan yang menunggu pembayaran
- **Steps:**
  1. Login sebagai anggota
  2. Buka halaman "Pesanan Saya"
  3. Klik pesanan yang belum dibayar
  4. Klik "Bayar Sekarang"
  5. Redirect ke Midtrans
  6. Pilih metode pembayaran
  7. Selesaikan pembayaran
- **Expected Result:** Pembayaran berhasil, status pesanan update menjadi "Dibayar"
- **Test Data:** Berbagai metode pembayaran (VA, E-Wallet, Credit Card)

**TC-D1.5: Cek Ongkir via RajaOngkir**
- **Precondition:** User memilih metode pengiriman
- **Steps:**
  1. Di halaman checkout, pilih "Kirim ke Alamat"
  2. Input alamat pengiriman
  3. Sistem hitung ongkir otomatis
- **Expected Result:** Ongkir ditampilkan untuk berbagai kurir (JNE, J&T, SiCepat, dll)
- **Test Data:** Berbagai alamat pengiriman

---

### E. MODUL AGEN/RESELLER

#### Test Cases:

**TC-E1.1: Input Penjualan oleh Agen**
- **Precondition:** Agen sudah login
- **Steps:**
  1. Login sebagai agen
  2. Buka halaman "Penjualan Saya"
  3. Klik "Input Penjualan Baru"
  4. Input data penjualan (pelanggan, produk, jumlah, harga)
  5. Upload bukti transaksi
  6. Klik "Simpan"
- **Expected Result:** Penjualan tersimpan, jika agen adalah pengurus perlu approval
- **Test Data:** Penjualan dengan berbagai produk dan jumlah

**TC-E1.2: Approval Penjualan Agen oleh Pengawas**
- **Precondition:** Ada penjualan agen pengurus yang menunggu approval
- **Steps:**
  1. Login sebagai pengawas
  2. Buka halaman "Approval Penjualan Agen"
  3. Klik penjualan untuk melihat detail
  4. Klik "Approve"
- **Expected Result:** Penjualan disetujui, tercatat sebagai penjualan koperasi, komisi dihitung
- **Test Data:** Penjualan dengan berbagai kondisi

**TC-E1.3: Perhitungan Komisi Agen**
- **Precondition:** Ada penjualan agen yang sudah disetujui
- **Steps:**
  1. Sistem hitung komisi otomatis berdasarkan persentase
  2. Tampilkan komisi di halaman agen
- **Expected Result:** Komisi dihitung dengan benar sesuai persentase yang ditetapkan
- **Test Data:** Penjualan dengan berbagai persentase komisi

---

### F. MODUL INVESTOR

#### Test Cases:

**TC-F1.1: Registrasi Investor**
- **Precondition:** Pengurus sudah login
- **Steps:**
  1. Login sebagai pengurus
  2. Buka halaman "Manajemen Investor"
  3. Klik "Tambah Investor"
  4. Input data investor lengkap
  5. Upload dokumen
  6. Klik "Simpan"
- **Expected Result:** Investor berhasil ditambahkan
- **Test Data:** Investor individu dan perusahaan

**TC-F1.2: Pencatatan Modal Penyertaan**
- **Precondition:** Investor sudah terdaftar
- **Steps:**
  1. Login sebagai pengurus
  2. Buka halaman "Modal Penyertaan"
  3. Klik "Tambah Modal Penyertaan"
  4. Pilih investor
  5. Input besar modal, tanggal, persentase
  6. Upload dokumen perjanjian
  7. Klik "Simpan"
- **Expected Result:** Modal penyertaan tercatat, total modal koperasi terupdate
- **Test Data:** Modal di bawah dan di atas batas 25%

**TC-F1.3: Pengurus sebagai Investor (Multiple Roles)**
- **Precondition:** Pengurus juga terdaftar sebagai investor
- **Steps:**
  1. Login sebagai pengurus dengan multiple roles
  2. Buka menu "Investasi Saya"
  3. Lihat daftar investasi
- **Expected Result:** Pengurus bisa melihat investasinya sendiri, sistem tracking multiple roles
- **Test Data:** Pengurus dengan berbagai kombinasi roles

---

### G. MODUL PEMBAGIAN KEUNTUNGAN

#### Test Cases:

**TC-G1.1: Perhitungan SHU**
- **Precondition:** Periode pembagian SHU sudah tiba
- **Steps:**
  1. Login sebagai pengurus (Bendahara)
  2. Buka halaman "Pembagian Keuntungan"
  3. Klik "Hitung SHU"
  4. Input periode dan total keuntungan
  5. Klik "Hitung"
- **Expected Result:** SHU per anggota dihitung dengan benar berdasarkan transaksi dan partisipasi
- **Test Data:** Berbagai skenario keuntungan dan transaksi

**TC-G1.2: Pembayaran SHU**
- **Precondition:** SHU sudah dihitung
- **Steps:**
  1. Login sebagai pengurus
  2. Buka halaman "Pembayaran SHU"
  3. Pilih anggota
  4. Pilih metode pembayaran
  5. Klik "Bayar SHU"
- **Expected Result:** SHU berhasil dibayar, status update, anggota mendapat notifikasi
- **Test Data:** Berbagai metode pembayaran

**TC-G1.3: Perhitungan Dividen Investor**
- **Precondition:** Periode pembagian dividen sudah tiba
- **Steps:**
  1. Login sebagai pengurus
  2. Buka halaman "Pembagian Dividen"
  3. Klik "Hitung Dividen"
  4. Input periode dan total keuntungan
  5. Klik "Hitung"
- **Expected Result:** Dividen per investor dihitung dengan benar berdasarkan modal dan persentase
- **Test Data:** Berbagai skenario keuntungan dan modal

---

## PERFORMANCE TESTING

### Test Scenarios:

**PT-1: Load Testing**
- **Tujuan:** Menguji performa aplikasi under normal load
- **Scenario:** 50 concurrent users melakukan berbagai aktivitas
- **Expected Result:** Response time < 2 detik, tidak ada error

**PT-2: Stress Testing**
- **Tujuan:** Menguji batas maksimal aplikasi
- **Scenario:** 100+ concurrent users
- **Expected Result:** Aplikasi masih bisa handle, mungkin response time lebih lambat tapi tidak crash

**PT-3: Database Performance**
- **Tujuan:** Menguji performa query database
- **Scenario:** Query dengan data besar (1000+ records)
- **Expected Result:** Query selesai dalam waktu wajar (< 3 detik)

---

## SECURITY TESTING

### Test Scenarios:

**ST-1: SQL Injection**
- **Tujuan:** Menguji kerentanan SQL injection
- **Method:** Input SQL commands di form input
- **Expected Result:** Input di-sanitize, tidak ada SQL injection yang berhasil

**ST-2: XSS (Cross-Site Scripting)**
- **Tujuan:** Menguji kerentanan XSS
- **Method:** Input JavaScript code di form input
- **Expected Result:** Input di-encode, tidak ada XSS yang berhasil

**ST-3: CSRF (Cross-Site Request Forgery)**
- **Tujuan:** Menguji kerentanan CSRF
- **Method:** Coba submit form dari external site
- **Expected Result:** CSRF token validation, request ditolak

**ST-4: Authentication & Authorization**
- **Tujuan:** Menguji keamanan login dan akses
- **Method:** 
  - Coba login dengan credentials salah
  - Coba akses halaman tanpa login
  - Coba akses halaman dengan role yang tidak sesuai
- **Expected Result:** 
  - Login gagal dengan credentials salah
  - Redirect ke login jika tidak login
  - Access denied jika role tidak sesuai

**ST-5: Password Security**
- **Tujuan:** Menguji keamanan password
- **Method:** 
  - Coba password yang lemah
  - Coba reset password
- **Expected Result:** 
  - Password harus memenuhi kriteria kuat
  - Password di-hash dengan bcrypt
  - Reset password memerlukan verifikasi

---

## INTEGRATION TESTING

### Test Scenarios:

**IT-1: Integrasi Midtrans Payment Gateway**
- **Tujuan:** Menguji integrasi dengan Midtrans
- **Steps:**
  1. Buat pesanan
  2. Pilih pembayaran via Midtrans
  3. Redirect ke Midtrans
  4. Selesaikan pembayaran
  5. Webhook dari Midtrans ke sistem
- **Expected Result:** Pembayaran berhasil, status update otomatis

**IT-2: Integrasi RajaOngkir**
- **Tujuan:** Menguji integrasi dengan RajaOngkir
- **Steps:**
  1. Pilih metode pengiriman
  2. Input alamat pengiriman
  3. Sistem hitung ongkir via RajaOngkir API
- **Expected Result:** Ongkir ditampilkan untuk berbagai kurir

**IT-3: Integrasi SMS/WhatsApp Gateway**
- **Tujuan:** Menguji integrasi notifikasi
- **Steps:**
  1. Trigger notifikasi (contoh: pembayaran berhasil)
  2. Sistem kirim SMS/WhatsApp
- **Expected Result:** Notifikasi berhasil dikirim

---

## COMPATIBILITY TESTING

### Test Scenarios:

**CT-1: Browser Compatibility**
- **Browsers:** Chrome, Firefox, Safari, Edge
- **Expected Result:** Aplikasi berfungsi dengan baik di semua browser

**CT-2: Mobile Responsiveness**
- **Devices:** Smartphone, Tablet
- **Expected Result:** Aplikasi responsive, UI sesuai untuk mobile

**CT-3: Operating System**
- **OS:** Windows, Linux, macOS (untuk admin)
- **Expected Result:** Aplikasi web-based, bisa diakses dari semua OS

---

## TESTING SCHEDULE

### Fase 1 (MVP) - 2 Minggu Testing
- Week 1: Unit Testing & Integration Testing
- Week 2: System Testing & UAT

### Fase 2 - 3 Minggu Testing
- Week 1-2: Unit Testing & Integration Testing
- Week 3: System Testing & UAT

### Fase 3 - 2 Minggu Testing
- Week 1: Unit Testing & Integration Testing
- Week 2: System Testing & UAT

### Fase 4 - 2 Minggu Testing
- Week 1: Unit Testing & Integration Testing
- Week 2: System Testing & UAT

---

## BUG TRACKING

### Bug Severity Levels:
1. **Critical:** Aplikasi crash, data loss, security breach
2. **High:** Fitur utama tidak berfungsi
3. **Medium:** Fitur sekunder tidak berfungsi, UI issue
4. **Low:** Typo, minor UI issue

### Bug Resolution Time:
- Critical: < 24 jam
- High: < 48 jam
- Medium: < 1 minggu
- Low: < 2 minggu

---

## TESTING TOOLS

### Tools yang Digunakan:
1. **PHPUnit:** Unit testing untuk PHP
2. **Postman:** API testing
3. **Browser DevTools:** Performance testing
4. **Manual Testing:** User acceptance testing
5. **Security Scanning Tools:** OWASP ZAP (opsional)

---

## KESIMPULAN

Testing Plan ini mencakup:
- **Testing Levels:** Unit, Integration, System, UAT
- **Test Cases:** Detail untuk setiap modul
- **Performance Testing:** Load, stress, database
- **Security Testing:** SQL injection, XSS, CSRF, authentication
- **Integration Testing:** API pihak ketiga
- **Compatibility Testing:** Browser, mobile, OS
- **Schedule:** Timeline testing per fase
- **Bug Tracking:** Severity dan resolution time

Dengan testing plan ini, aplikasi akan diuji secara komprehensif sebelum launch.

# DEPLOYMENT PLAN
## Aplikasi Koperasi Pemasaran Kepolisian Polres Samosir

---

## PRE-DEPLOYMENT CHECKLIST

### 1. Code Review & Quality Assurance
- [ ] Semua code sudah di-review
- [ ] Tidak ada critical bugs
- [ ] Code mengikuti coding standards
- [ ] Documentation lengkap

### 2. Testing
- [ ] Unit testing passed (> 80% coverage)
- [ ] Integration testing passed
- [ ] System testing passed
- [ ] UAT passed
- [ ] Performance testing passed
- [ ] Security testing passed

### 3. Database
- [ ] Database schema sudah final
- [ ] Migration scripts sudah siap
- [ ] Seed data sudah siap
- [ ] Backup strategy sudah ditentukan

### 4. Configuration
- [ ] Environment variables sudah dikonfigurasi
- [ ] API keys sudah didapatkan
- [ ] Payment gateway sudah terdaftar
- [ ] Domain sudah dibeli
- [ ] SSL certificate sudah diinstall

### 5. Infrastructure
- [ ] Server sudah disiapkan
- [ ] Database server sudah disiapkan
- [ ] Backup server sudah disiapkan
- [ ] Monitoring tools sudah diinstall

---

## DEPLOYMENT ENVIRONMENT

### 1. Development Environment
**Purpose:** Untuk development dan testing
**Server:** Local (XAMPP) atau development server
**Database:** MySQL/MariaDB lokal
**URL:** http://localhost/koperasi atau http://dev.koperasi.domain.com

### 2. Staging Environment
**Purpose:** Untuk testing sebelum production
**Server:** Staging server (mirip production)
**Database:** MySQL/MariaDB staging
**URL:** http://staging.koperasi.domain.com

### 3. Production Environment
**Purpose:** Untuk penggunaan aktual
**Server:** Production server (high availability)
**Database:** MySQL/MariaDB production dengan replication
**URL:** https://koperasi.domain.com

---

## SERVER REQUIREMENTS

### Production Server Specifications:

**Minimum:**
- CPU: 4 cores
- RAM: 8 GB
- Storage: 100 GB SSD
- Bandwidth: 100 Mbps
- OS: Linux (Ubuntu 20.04 LTS atau CentOS 8)

**Recommended:**
- CPU: 8 cores
- RAM: 16 GB
- Storage: 500 GB SSD
- Bandwidth: 1 Gbps
- OS: Linux (Ubuntu 20.04 LTS atau CentOS 8)

### Database Server Specifications:

**Minimum:**
- CPU: 4 cores
- RAM: 8 GB
- Storage: 200 GB SSD
- OS: Linux (Ubuntu 20.04 LTS atau CentOS 8)

**Recommended:**
- CPU: 8 cores
- RAM: 16 GB
- Storage: 500 GB SSD dengan replication
- OS: Linux (Ubuntu 20.04 LTS atau CentOS 8)

---

## SOFTWARE STACK

### Server Software:
- **Web Server:** Apache 2.4 atau Nginx 1.18+
- **PHP:** PHP 8.0 atau 8.1
- **Database:** MySQL 8.0 atau MariaDB 10.6+
- **Cache:** Redis 6.0+ (opsional)
- **SSL:** Let's Encrypt atau commercial SSL

### PHP Extensions Required:
- mysqli atau PDO
- mbstring
- curl
- json
- openssl
- gd (untuk image processing)
- zip (untuk backup)

---

## DEPLOYMENT STEPS

### Phase 1: Server Setup

#### Step 1.1: Setup Server
1. Provision server (VPS atau dedicated server)
2. Install OS (Ubuntu/CentOS)
3. Update system packages
4. Setup firewall (UFW atau firewalld)
5. Setup SSH key authentication
6. Disable root login (security)

#### Step 1.2: Install Web Server
1. Install Apache atau Nginx
2. Configure virtual host
3. Setup SSL certificate (Let's Encrypt)
4. Configure PHP-FPM
5. Test web server

#### Step 1.3: Install Database
1. Install MySQL/MariaDB
2. Secure MySQL installation
3. Create database user
4. Configure database
5. Setup database backup

#### Step 1.4: Install PHP
1. Install PHP 8.0 atau 8.1
2. Install required PHP extensions
3. Configure PHP (php.ini)
4. Setup PHP-FPM
5. Test PHP

---

### Phase 2: Application Deployment

#### Step 2.1: Prepare Application Files
1. Clone atau upload code ke server
2. Setup file permissions
3. Configure .htaccess (jika Apache)
4. Setup environment variables (.env)

#### Step 2.2: Database Migration
1. Backup database existing (jika ada)
2. Run database migration scripts
3. Seed initial data (jika diperlukan)
4. Verify database structure

#### Step 2.3: Configuration
1. Configure database connection
2. Configure API keys (Midtrans, RajaOngkir, dll)
3. Configure email SMTP
4. Configure file upload settings
5. Configure session settings

#### Step 2.4: File Permissions
```bash
# Set proper permissions
chown -R www-data:www-data /var/www/koperasi
chmod -R 755 /var/www/koperasi
chmod -R 775 /var/www/koperasi/storage
chmod -R 775 /var/www/koperasi/uploads
```

---

### Phase 3: Security Setup

#### Step 3.1: SSL Certificate
1. Install Let's Encrypt SSL
2. Configure auto-renewal
3. Force HTTPS redirect
4. Test SSL

#### Step 3.2: Firewall Configuration
```bash
# Allow HTTP, HTTPS, SSH
ufw allow 80/tcp
ufw allow 443/tcp
ufw allow 22/tcp
ufw enable
```

#### Step 3.3: Security Headers
- Setup security headers di .htaccess atau Nginx config
- X-Frame-Options: DENY
- X-Content-Type-Options: nosniff
- X-XSS-Protection: 1; mode=block
- Strict-Transport-Security: max-age=31536000

#### Step 3.4: Database Security
- Change default MySQL root password
- Create dedicated database user
- Grant minimal privileges
- Disable remote root login

---

### Phase 4: Monitoring & Backup

#### Step 4.1: Setup Monitoring
1. Install monitoring tools (Nagios, Zabbix, atau cloud monitoring)
2. Setup alerts untuk:
   - Server down
   - High CPU/RAM usage
   - Disk space low
   - Database connection issues
3. Setup log monitoring

#### Step 4.2: Setup Backup
1. Database backup (daily):
   ```bash
   # Daily backup script
   mysqldump -u user -p database > backup_$(date +%Y%m%d).sql
   ```
2. File backup (daily):
   ```bash
   # Backup application files
   tar -czf backup_files_$(date +%Y%m%d).tar.gz /var/www/koperasi
   ```
3. Setup backup retention (30 days)
4. Test backup restoration

#### Step 4.3: Setup Logging
1. Setup application logs
2. Setup error logs
3. Setup access logs
4. Setup log rotation

---

### Phase 5: Testing & Verification

#### Step 5.1: Functional Testing
1. Test semua modul utama
2. Test login/logout
3. Test CRUD operations
4. Test payment gateway
5. Test API integrations

#### Step 5.2: Performance Testing
1. Test page load time
2. Test database query performance
3. Test API response time
4. Load testing

#### Step 5.3: Security Testing
1. Test SQL injection prevention
2. Test XSS prevention
3. Test CSRF protection
4. Test authentication/authorization

---

## DEPLOYMENT PROCEDURE

### Pre-Deployment (1 hari sebelum):
1. Notify stakeholders tentang maintenance window
2. Backup database dan files
3. Prepare deployment package
4. Review deployment checklist

### Deployment Day:

**Timeline:** 4-6 jam

**Step 1: Maintenance Mode (15 menit)**
- Enable maintenance mode
- Notify users tentang maintenance

**Step 2: Backup (30 menit)**
- Backup database
- Backup application files

**Step 3: Deploy Code (1 jam)**
- Upload new code
- Run database migrations
- Update configuration

**Step 4: Testing (1-2 jam)**
- Test critical functions
- Test payment gateway
- Test API integrations

**Step 5: Go Live (15 menit)**
- Disable maintenance mode
- Monitor for errors
- Notify users

**Step 6: Post-Deployment (1 jam)**
- Monitor logs
- Monitor performance
- Fix any critical issues

---

## ROLLBACK PROCEDURE

Jika ada masalah setelah deployment:

### Step 1: Identify Issue
- Check error logs
- Check monitoring alerts
- Identify root cause

### Step 2: Decision
- Critical issue â†’ Rollback immediately
- Minor issue â†’ Fix on production (jika cepat)

### Step 3: Rollback
1. Enable maintenance mode
2. Restore database from backup
3. Restore application files from backup
4. Verify rollback success
5. Disable maintenance mode

### Step 4: Post-Rollback
- Analyze issue
- Fix issue in development
- Plan re-deployment

---

## POST-DEPLOYMENT

### Week 1: Monitoring
- Monitor server performance
- Monitor error logs
- Monitor user feedback
- Fix any critical bugs

### Week 2-4: Stabilization
- Fix minor bugs
- Optimize performance
- User training
- Documentation update

### Ongoing: Maintenance
- Regular updates
- Security patches
- Performance optimization
- Backup verification

---

## DEPLOYMENT CHECKLIST

### Pre-Deployment:
- [ ] Code review completed
- [ ] Testing completed
- [ ] Database migration scripts ready
- [ ] Configuration files ready
- [ ] API keys obtained
- [ ] SSL certificate ready
- [ ] Server provisioned
- [ ] Backup strategy defined

### Deployment:
- [ ] Server setup completed
- [ ] Web server installed
- [ ] Database installed
- [ ] PHP installed
- [ ] Application deployed
- [ ] Database migrated
- [ ] Configuration done
- [ ] SSL installed
- [ ] Security configured
- [ ] Monitoring setup
- [ ] Backup configured

### Post-Deployment:
- [ ] Functional testing passed
- [ ] Performance testing passed
- [ ] Security testing passed
- [ ] User training completed
- [ ] Documentation updated

---

## KESIMPULAN

Deployment Plan ini mencakup:
- **Pre-Deployment Checklist:** Persiapan sebelum deployment
- **Deployment Environments:** Development, Staging, Production
- **Server Requirements:** Spesifikasi server yang dibutuhkan
- **Deployment Steps:** Langkah-langkah deployment detail
- **Security Setup:** Konfigurasi keamanan
- **Monitoring & Backup:** Setup monitoring dan backup
- **Rollback Procedure:** Prosedur rollback jika ada masalah
- **Post-Deployment:** Monitoring dan maintenance setelah deployment

Dengan deployment plan ini, aplikasi dapat di-deploy dengan aman dan terstruktur.

# MAINTENANCE PLAN
## Aplikasi Koperasi Pemasaran Kepolisian Polres Samosir

---

## JENIS MAINTENANCE

### 1. Preventive Maintenance (Pemeliharaan Pencegahan)
**Tujuan:** Mencegah masalah sebelum terjadi
**Frekuensi:** Harian, Mingguan, Bulanan

### 2. Corrective Maintenance (Pemeliharaan Korektif)
**Tujuan:** Memperbaiki masalah yang terjadi
**Frekuensi:** Sesuai kebutuhan (on-demand)

### 3. Adaptive Maintenance (Pemeliharaan Adaptif)
**Tujuan:** Menyesuaikan dengan perubahan lingkungan
**Frekuensi:** Sesuai kebutuhan

### 4. Perfective Maintenance (Pemeliharaan Perfektif)
**Tujuan:** Meningkatkan performa dan fitur
**Frekuensi:** Bulanan, Triwulanan

---

## SCHEDULE MAINTENANCE

### Daily Maintenance (Setiap Hari)

#### 1. Database Backup
**Waktu:** 02:00 WIB (off-peak hours)
**Tugas:**
- Backup database otomatis
- Verify backup success
- Check backup file size
- Alert jika backup gagal

**Script:**
```bash
#!/bin/bash
DATE=$(date +%Y%m%d)
mysqldump -u user -p password database > /backup/db_backup_$DATE.sql
gzip /backup/db_backup_$DATE.sql
# Keep only last 30 days
find /backup -name "db_backup_*.sql.gz" -mtime +30 -delete
```

#### 2. Log Monitoring
**Waktu:** Setiap 4 jam
**Tugas:**
- Check error logs
- Check access logs untuk anomaly
- Check application logs
- Alert jika ada critical errors

#### 3. Server Health Check
**Waktu:** Setiap jam
**Tugas:**
- Check CPU usage
- Check RAM usage
- Check disk space
- Check database connections
- Alert jika melebihi threshold

**Threshold:**
- CPU: > 80%
- RAM: > 85%
- Disk: > 90%
- Database connections: > 80% dari max

---

### Weekly Maintenance (Setiap Minggu)

#### 1. Database Optimization
**Waktu:** Minggu malam (off-peak)
**Tugas:**
- Optimize database tables
- Analyze tables
- Check for slow queries
- Update table statistics

**Script:**
```sql
-- Optimize all tables
OPTIMIZE TABLE table1, table2, ...;

-- Analyze tables
ANALYZE TABLE table1, table2, ...;
```

#### 2. File Cleanup
**Waktu:** Minggu malam
**Tugas:**
- Clean temporary files
- Clean old log files
- Clean old cache files
- Clean old upload files (jika ada retention policy)

#### 3. Security Scan
**Waktu:** Minggu malam
**Tugas:**
- Check for security updates
- Scan for malware
- Check for suspicious activities
- Review access logs

#### 4. Performance Review
**Waktu:** Akhir minggu
**Tugas:**
- Review performance metrics
- Identify bottlenecks
- Review error rates
- Generate weekly report

---

### Monthly Maintenance (Setiap Bulan)

#### 1. System Updates
**Waktu:** Akhir bulan (weekend)
**Tugas:**
- Update OS packages
- Update PHP (jika ada patch security)
- Update database (jika ada patch security)
- Test updates di staging sebelum production

#### 2. Database Maintenance
**Waktu:** Akhir bulan
**Tugas:**
- Review database size
- Archive old data (jika diperlukan)
- Review and optimize indexes
- Check database integrity

#### 3. Backup Verification
**Waktu:** Akhir bulan
**Tugas:**
- Test restore dari backup
- Verify backup integrity
- Review backup retention policy
- Update backup strategy jika diperlukan

#### 4. Security Audit
**Waktu:** Akhir bulan
**Tugas:**
- Review user access
- Review API keys
- Check for unused accounts
- Review security logs
- Penetration testing (opsional)

#### 5. Performance Optimization
**Waktu:** Akhir bulan
**Tugas:**
- Review slow queries
- Optimize database queries
- Review caching strategy
- Optimize code jika diperlukan

#### 6. Documentation Update
**Waktu:** Akhir bulan
**Tugas:**
- Update technical documentation
- Update user manual jika ada perubahan
- Update API documentation
- Document any changes

---

### Quarterly Maintenance (Setiap 3 Bulan)

#### 1. Comprehensive Security Audit
**Waktu:** Akhir kuartal
**Tugas:**
- Full security scan
- Penetration testing
- Review security policies
- Update security measures

#### 2. Performance Tuning
**Waktu:** Akhir kuartal
**Tugas:**
- Comprehensive performance review
- Database tuning
- Server tuning
- Code optimization

#### 3. Disaster Recovery Test
**Waktu:** Akhir kuartal
**Tugas:**
- Test disaster recovery procedure
- Test backup restoration
- Review disaster recovery plan
- Update disaster recovery plan jika diperlukan

#### 4. User Feedback Review
**Waktu:** Akhir kuartal
**Tugas:**
- Collect user feedback
- Analyze user feedback
- Prioritize improvements
- Plan enhancements

---

## CORRECTIVE MAINTENANCE

### Response Time SLA:

**Critical Issues:**
- Response time: < 1 jam
- Resolution time: < 4 jam
- Examples: Server down, data loss, security breach

**High Priority Issues:**
- Response time: < 4 jam
- Resolution time: < 24 jam
- Examples: Fitur utama tidak berfungsi, payment gateway error

**Medium Priority Issues:**
- Response time: < 24 jam
- Resolution time: < 3 hari
- Examples: Fitur sekunder tidak berfungsi, UI issue

**Low Priority Issues:**
- Response time: < 3 hari
- Resolution time: < 1 minggu
- Examples: Typo, minor UI issue

---

## MONITORING & ALERTING

### Monitoring Tools:

1. **Server Monitoring:**
   - CPU, RAM, Disk usage
   - Network traffic
   - Uptime monitoring

2. **Application Monitoring:**
   - Error rates
   - Response times
   - API performance
   - Database query performance

3. **Database Monitoring:**
   - Database connections
   - Query performance
   - Database size
   - Replication status (jika ada)

4. **Security Monitoring:**
   - Failed login attempts
   - Suspicious activities
   - Security events
   - Access logs

### Alert Channels:

1. **Email:** Untuk semua alerts
2. **SMS:** Untuk critical alerts
3. **WhatsApp:** Untuk critical alerts (opsional)
4. **Dashboard:** Real-time monitoring dashboard

---

## BACKUP STRATEGY

### Database Backup:

**Frequency:** Daily
**Retention:** 30 days
**Location:** 
- Local server (primary)
- Cloud storage (secondary - Google Drive/Dropbox/AWS S3)

**Backup Types:**
- Full backup: Daily
- Incremental backup: Setiap 6 jam (opsional)

### File Backup:

**Frequency:** Daily
**Retention:** 30 days
**Location:**
- Local server
- Cloud storage

**Files to Backup:**
- Application files
- Upload files
- Configuration files
- Log files (optional)

### Backup Verification:

**Frequency:** Weekly
**Method:** Test restore dari backup
**Documentation:** Record backup test results

---

## DISASTER RECOVERY

### Recovery Time Objective (RTO): < 4 jam
### Recovery Point Objective (RPO): < 24 jam

### Disaster Recovery Procedure:

1. **Identify Disaster:**
   - Server crash
   - Data corruption
   - Security breach
   - Natural disaster

2. **Assess Impact:**
   - Scope of damage
   - Data loss
   - Downtime impact

3. **Activate DR Plan:**
   - Notify stakeholders
   - Activate backup server (jika ada)
   - Restore from backup
   - Verify restoration

4. **Recovery:**
   - Restore database
   - Restore application files
   - Verify functionality
   - Go live

5. **Post-Recovery:**
   - Analyze root cause
   - Update DR plan
   - Document lessons learned

---

## PERFORMANCE MONITORING

### Key Performance Indicators (KPIs):

1. **Uptime:** > 99.5%
2. **Response Time:** < 2 detik (average)
3. **Error Rate:** < 0.1%
4. **Database Query Time:** < 1 detik (average)
5. **API Response Time:** < 500ms (average)

### Performance Metrics to Monitor:

- Page load time
- Database query performance
- API response time
- Server resource usage
- User session duration
- Transaction success rate

---

## SECURITY MAINTENANCE

### Regular Security Tasks:

1. **Weekly:**
   - Review security logs
   - Check for suspicious activities
   - Review failed login attempts

2. **Monthly:**
   - Review user access
   - Review API keys
   - Check for security updates
   - Review firewall rules

3. **Quarterly:**
   - Full security audit
   - Penetration testing
   - Review security policies
   - Update security measures

### Security Updates:

- **OS Updates:** Monthly (security patches)
- **PHP Updates:** As needed (security patches)
- **Database Updates:** As needed (security patches)
- **Application Updates:** As needed (security fixes)

---

## USER SUPPORT

### Support Channels:

1. **Email:** support@koperasi.domain.com
2. **Phone:** (jika tersedia)
3. **Helpdesk/Ticketing System:** Internal system
4. **FAQ:** Di website

### Support Hours:

- **Weekdays:** 08:00 - 17:00 WIB
- **Weekends:** Emergency only
- **Holidays:** Emergency only

### Support SLA:

- **Critical:** < 1 jam response
- **High:** < 4 jam response
- **Medium:** < 24 jam response
- **Low:** < 3 hari response

---

## MAINTENANCE TEAM

### Roles & Responsibilities:

1. **System Administrator:**
   - Server maintenance
   - Database maintenance
   - Backup & recovery
   - Security monitoring

2. **Developer:**
   - Bug fixes
   - Code updates
   - Performance optimization
   - Feature enhancements

3. **QA/Tester:**
   - Testing after updates
   - Quality assurance
   - Bug reporting

4. **Support Staff:**
   - User support
   - Issue tracking
   - User training

---

## MAINTENANCE BUDGET

### Monthly Maintenance Cost:

**Personnel:**
- System Administrator (part-time): Rp 5.000.000/bulan
- Developer (part-time): Rp 8.000.000/bulan
- Support Staff (part-time): Rp 3.000.000/bulan

**Infrastructure:**
- Server Hosting: Rp 500.000 - Rp 2.000.000/bulan
- API Keys: Rp 1.000.000 - Rp 5.000.000/bulan
- Backup Storage: Rp 200.000/bulan
- Monitoring Tools: Rp 500.000/bulan

**Total Monthly:** Rp 18.200.000 - Rp 23.700.000/bulan

**Total Yearly:** Rp 218.400.000 - Rp 284.400.000/tahun

---

## MAINTENANCE LOG

### Format Log Entry:

```
Date: YYYY-MM-DD
Time: HH:MM
Type: [Preventive/Corrective/Adaptive/Perfective]
Category: [Database/Server/Application/Security]
Description: [What was done]
Performed by: [Name]
Duration: [Time taken]
Status: [Success/Failed]
Notes: [Additional notes]
```

### Log Retention:

- **Maintenance Logs:** 1 tahun
- **Error Logs:** 6 bulan
- **Access Logs:** 3 bulan
- **Security Logs:** 1 tahun

---

## KESIMPULAN

Maintenance Plan ini mencakup:
- **Jenis Maintenance:** Preventive, Corrective, Adaptive, Perfective
- **Schedule:** Daily, Weekly, Monthly, Quarterly
- **Monitoring & Alerting:** Tools dan channels
- **Backup Strategy:** Database dan files
- **Disaster Recovery:** RTO dan RPO
- **Performance Monitoring:** KPIs dan metrics
- **Security Maintenance:** Regular security tasks
- **User Support:** Channels dan SLA
- **Maintenance Team:** Roles dan responsibilities
- **Budget:** Monthly dan yearly cost

Dengan maintenance plan ini, aplikasi dapat di-maintain dengan baik dan berkelanjutan.

---

## INFORMASI PENGEMBANG

**Aplikasi ini dikembangkan oleh:**
**AIPDA PATRI SIHALOHO, SH**

**Untuk:**
Koperasi Pemasaran Kepolisian Polres Samosir

**Tahun:** 2025

---
