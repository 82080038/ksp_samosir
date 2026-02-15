-- Migration: Add new settings keys for tab-based Settings
-- File: add_settings_keys_20260215.sql
-- Purpose: Insert new settings keys with default values into settings table
-- Run after: Ensure settings table exists

-- Insert new settings keys (ignore duplicates)
INSERT IGNORE INTO settings (setting_key, setting_value, setting_type, deskripsi, updated_at) VALUES
-- Identitas & Legal
('nama_koperasi', 'KSP Samosir', 'text', 'Nama resmi koperasi', NOW()),
('email', 'info@ksp_samosir.co.id', 'text', 'Email resmi koperasi', NOW()),
('no_telp', '(021) 1234567', 'text', 'Nomor telepon koperasi', NOW()),
('alamat', 'Jl. Contoh No.123', 'text', 'Alamat kantor', NOW()),
('no_badan_hukum', '', 'text', 'Nomor Badan Hukum', NOW()),
('tanggal_akta', '', 'text', 'Tanggal Akta Pendirian', NOW()),
('npwp_koperasi', '', 'text', 'NPWP Koperasi', NOW()),
('nib_oss', '', 'text', 'NIB/OSS', NOW()),
('no_sk_kemenkumham', '', 'text', 'No. SK Kemenkumham', NOW()),

-- Parameter Keuangan
('bunga_simpanan_wajib', '3.00', 'number', 'Bunga simpanan wajib (%)', NOW()),
('bunga_simpanan_sukarela', '4.00', 'number', 'Bunga simpanan sukarela (%)', NOW()),
('metode_perhitungan_bunga', 'flat', 'text', 'Metode perhitungan bunga (flat/efektif)', NOW()),
('simpanan_pokok_minimum', '1000000', 'number', 'Simpanan pokok minimum (Rp)', NOW()),
('bunga_pinjaman', '12.00', 'number', 'Bunga pinjaman (%)', NOW()),
('biaya_administrasi', '1.00', 'number', 'Biaya administrasi (%)', NOW()),
('biaya_provisi', '1.00', 'number', 'Biaya provisi (%)', NOW()),
('denda_keterlambatan', '5.00', 'number', 'Denda keterlambatan (%)/bulan', NOW()),
('max_loan_limit', '20000000', 'number', 'Batas pinjaman maksimum (Rp)', NOW()),
('daily_transaction_limit', '50000000', 'number', 'Batas transaksi harian (Rp)', NOW()),

-- KYC & Compliance
('kyc_wajib_ktp', '1', 'boolean', 'Wajib upload KTP (1/0)', NOW()),
('kyc_wajib_selfie', '1', 'boolean', 'Wajib selfie dengan KTP (1/0)', NOW()),
('kyc_validasi_nik', '0', 'boolean', 'Validasi NIK otomatis (1/0)', NOW()),
('kyc_wajib_kk', '0', 'boolean', 'Wajib upload KK (1/0)', NOW()),
('limit_ctr', '100000000', 'number', 'Limit CTR (Rp)', NOW()),
('limit_str', '500000000', 'number', 'Limit STR (Rp)', NOW()),
('compliance_approval_berlapis', '0', 'boolean', 'Approval berlapis untuk pinjaman > limit (1/0)', NOW()),
('compliance_blacklist_check', '1', 'boolean', 'Cek blacklist otomatis (1/0)', NOW()),
('compliance_sanction_check', '0', 'boolean', 'Cek sanction list (1/0)', NOW()),

-- Akuntansi
('tahun_fiskal', '2026', 'text', 'Tahun fiskal', NOW()),
('closing_date', '2024-12-31', 'text', 'Closing date', NOW()),
('metode_amortisasi', 'straight_line', 'text', 'Metode amortisasi (straight_line/declining_balance)', NOW()),
('akun_kas', '110.000', 'text', 'Akun kas', NOW()),
('akun_bank', '120.000', 'text', 'Akun bank', NOW()),
('akun_piutang_pinjaman', '130.000', 'text', 'Akun piutang pinjaman', NOW()),
('akun_cadangan_kerugian', '135.000', 'text', 'Akun cadangan kerugian', NOW()),
('akun_pendapatan_bunga', '410.000', 'text', 'Akun pendapatan bunga', NOW()),
('akun_beban_bunga_simpanan', '520.000', 'text', 'Akun beban bunga simpanan', NOW()),

-- Notifikasi & Pengingat
('smtp_host', '', 'text', 'SMTP host', NOW()),
('smtp_port', '587', 'text', 'SMTP port', NOW()),
('smtp_encryption', 'tls', 'text', 'SMTP encryption (tls/ssl/none)', NOW()),
('smtp_username', '', 'text', 'SMTP username', NOW()),
('smtp_password', '', 'text', 'SMTP password', NOW()),
('wa_api_url', '', 'text', 'WhatsApp API URL', NOW()),
('wa_token', '', 'text', 'WhatsApp token', NOW()),
('sms_gateway_url', '', 'text', 'SMS gateway URL', NOW()),
('sms_username', '', 'text', 'SMS username', NOW()),
('sms_password', '', 'text', 'SMS password', NOW()),
('reminder_jatuh_tempo', '3', 'number', 'Reminder jatuh tempo (hari)', NOW()),
('reminder_denda', '1', 'number', 'Reminder denda (hari)', NOW()),
('reminder_setoran', '5', 'number', 'Reminder setoran (hari)', NOW()),
('reminder_jam_kirim', '09:00', 'text', 'Jam kirim reminder', NOW()),

-- Operasional
('jam_buka', '08:00', 'text', 'Jam buka', NOW()),
('jam_tutup', '16:00', 'text', 'Jam tutup', NOW()),
('cutoff_transaksi', '15:00', 'text', 'Cutoff transaksi', NOW()),
('hari_libur', 'Sabtu, Minggu', 'text', 'Hari libur', NOW()),
('rekening_kas', '', 'text', 'Rekening kas', NOW()),
('rekening_bank1', '', 'text', 'Rekening bank 1', NOW()),
('rekening_bank2', '', 'text', 'Rekening bank 2', NOW());

-- Show summary
SELECT 
    COUNT(*) as total_settings
FROM settings;

-- Show some sample settings
SELECT setting_key, setting_value, deskripsi 
FROM settings 
WHERE setting_key IN ('nama_koperasi', 'bunga_simpanan_wajib', 'kyc_wajib_ktp', 'tahun_fiskal', 'jam_buka')
ORDER BY setting_key;
