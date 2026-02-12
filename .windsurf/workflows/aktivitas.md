# Aktivitas Koperasi KSP Samosir

## Dasar Hukum Koperasi
Berdasarkan UU No. 25 Tahun 1992 tentang Perkoperasian dan AD/ART Koperasi Pemasaran Kepolisian Polres Samosir.

## Aktivitas Utama Koperasi

### 1. Aktivitas Simpanan (Pasal 23 UU 25/1992)
**Peraturan:**
- Simpanan pokok: sekali bayar saat pendaftaran anggota
- Simpanan wajib: berkala sesuai ketentuan yang ditetapkan AD/ART
- Simpanan sukarela: sesuai kemampuan anggota
- Bunga simpanan: sesuai keputusan rapat anggota

**Implementasi Aplikasi:**
- [x] Simpanan Pokok - sekali bayar
- [x] Simpanan Wajib - berkala otomatis
- [x] Simpanan Sukarela - fleksibel
- [ ] Bunga Simpanan - perlu konfigurasi di settings

### 2. Aktivitas Pinjaman (Pasal 24 UU 25/1992)
**Peraturan:**
- Pinjaman kepada anggota untuk kebutuhan produktif
- Pinjaman tidak boleh melebihi batas yang ditetapkan
- Bunga pinjaman: sesuai keputusan rapat pengurus
- Jaminan: sesuai ketentuan AD/ART

**Implementasi Aplikasi:**
- [x] Pinjaman Anggota - untuk kebutuhan produktif
- [x] Approval Workflow - sesuai struktur pengurus
- [x] Penjadwalan Angsuran - otomatis
- [ ] Batas Pinjaman - perlu konfigurasi
- [ ] Bunga Pinjaman - perlu konfigurasi di settings

### 3. Aktivitas Jual Beli (Pasal 25 UU 25/1992)
**Peraturan:**
- Menjual produk anggota dengan harga wajar
- Menjual produk koperasi hasil usaha bersama
- Pembelian kebutuhan operasional koperasi
- Transparansi harga dan kualitas

**Implementasi Aplikasi:**
- [x] Katalog Produk - manajemen produk
- [x] Jual Produk Anggota - platform penjualan
- [x] Sistem Pesanan - tracking pesanan
- [ ] Pembelian Operasional - perlu integrasi
- [ ] Transparansi Harga - perlu validasi

### 4. SHU (Sisa Hasil Usaha) - Pasal 26 UU 25/1992
**Peraturan:**
- SHU dibagikan setelah tutup buku tahunan
- Bagian jasa modal anggota: maksimal 40%
- Bagian jasa usaha anggota: maksimal 35%
- Bagian pendidikan sosial: minimal 3%
- Honorarium pengurus: sesuai keputusan rapat

**Implementasi Aplikasi:**
- [x] Perhitungan SHU otomatis
- [x] Komponen pembagian SHU
- [x] Distribusi SHU ke anggota
- [ ] Persentase dinamis - perlu konfigurasi
- [ ] Laporan SHU - perlu implementasi

## Kewajiban Pengurus (Pasal 47 UU 25/1992)
### 1. Ketua
- Memimpin rapat anggota
- Menandatangani pelaksanaan AD/ART
- Mewakili koperasi di luar pengadilan

### 2. Wakil Ketua
- Membantu ketua dalam tugasnya
- Menggantikan ketua saat berhalangan

### 3. Sekretaris
- Mencatat notulen rapat
- Mengelola administrasi dan surat-menyurat
- Menyimpan dokumen koperasi

### 4. Bendahara
- Mengelola keuangan koperasi
- Membuat laporan keuangan
- Melakukan pembayaran dan penerimaan

## Kewajiban Pengawas (Pasal 48 UU 25/1992)
### 1. Ketua Pengawas
- Memimpin rapat pengawas
- Melakukan pemeriksaan rutin
- Menerbitkan laporan pengawasan

### 2. Anggota Pengawas
- Memantau pelaksanaan tugas pengurus
- Memberikan laporan tertulis kepada ketua pengawas
- Dapat mengusulkan pemberhentian sementara pengurus

## Sanksi dan Peraturan (Pasal 88 UU 25/1992)
### Jenis Sanksi:
1. **Teguran Lisan** - untuk pelanggaran ringan
2. **Teguran Tertulis** - untuk pelanggaran sedang
3. **Pemberhentian Sementara** - untuk pelanggaran berat
4. **Pemberhentian** - untuk pelanggaran sangat berat

### Prosedur Sanksi:
- Teguran lisan: langsung diberikan
- Teguran tertulis: surat teguran dari pengawas
- Pemberhentian: SK pengawas dengan alasan jelas
- Pemecatan: keputusan rapat anggota

## Rapat Anggota (Pasal 27 UU 25/1992)
### Jenis Rapat:
1. **Rapat Anggota Tahunan** - wajib, untuk pengesahan laporan tahunan
2. **Rapat Anggota Luar Biasa** - untuk keputusan penting
3. **Rapat Anggota Khusus** - untuk keputusan perubahan AD/ART

### Materi Rapat Wajib:
- Laporan pertanggungjawaban pengurus
- Laporan keuangan tahunan
- Pembagian SHU
- Perubahan AD/ART
- Pemilihan pengurus baru

## Implementasi di Aplikasi

### 1. Menu Settings → Koperasi Activities
- Konfigurasi parameter aktivitas koperasi
- Aturan bunga simpanan dan pinjaman
- Persentase pembagian SHU
- Batasan transaksi

### 2. Menu Simpanan
- Jenis simpanan (pokok, wajib, sukarela)
- Bunga simpanan otomatis
- Laporan simpanan per anggota

### 3. Menu Pinjaman
- Pengajuan pinjaman anggota
- Approval workflow pengurus
- Penjadwalan angsuran otomatis
- Monitoring tunggakan

### 4. Menu Jual Beli
- Katalog produk
- Platform jual beli
- Sistem pesanan dan tracking
- Laporan penjualan

### 5. Menu Laporan → SHU
- Perhitungan SHU otomatis
- Laporan pembagian SHU
- Riwayat SHU per anggota
- Laporan kepatutan SHU

### 6. Menu Rapat
- Jadwal rapat
- Notulen rapat otomatis
- Daftar hadir elektronik
- Keputusan rapat dan tindak lanjutan

### 7. Menu Pengawasan
- Log aktivitas pengurus
- Laporan pengawasan
- Sistem sanksi otomatis
- Pencatatan pelanggaran

## Validasi Kepatuhanan
Aplikasi akan memvalidasi:
1. **Batas Pinjaman** - tidak melebihi batas yang ditetapkan
2. **Bunga Simpanan** - sesuai dengan keputusan rapat
3. **Persentase SHU** - sesuai batasan UU 25/1992
4. **Kuorum Rapat** - rapat anggota harus kuorum
5. **Prosedur Sanksi** - mengikuti hierarki sanksi
6. **Transparansi** - semua transaksi tercatat dan dapat diaudit

## Dokumentasi
- [ ] AD/ART Koperasi Pemasaran Kepolisian Polres Samosir
- [ ] UU No. 25 Tahun 1992 tentang Perkoperasian
- [ ] Peraturan Menteri Koperasi dan UKM
- [] Standar Akuntansi Koperasi

## Monitoring dan Pelaporan
Aplikasi menyediakan:
- Laporan aktivitas koperasi harian/mingguan/tahunan
- Laporan kepatuhan terhadap peraturan
- Audit trail untuk semua transaksi penting
- Dashboard monitoring kesehatan koperasi
- Early warning system untuk pelanggaran

## Update Berkala
Sistem akan diperbarui secara berkala untuk:
- Menyesuaikan parameter sesuai keputusan rapat
- Mengikuti perubahan peraturan perundang-undangan
- Menambah fitur baru sesuai kebutuhan koperasi
- Memperbaiki efisiensi dan transparansi