-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Waktu pembuatan: 13 Feb 2026 pada 04.15
-- Versi server: 10.6.23-MariaDB-0ubuntu0.22.04.1
-- Versi PHP: 8.1.2-1ubuntu2.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ksp_samosir`
--

DELIMITER $$
--
-- Prosedur
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `calculate_shu_period` (IN `period_start` DATE, IN `period_end` DATE, IN `calculation_method` VARCHAR(20))  BEGIN
    DECLARE total_shu_amount DECIMAL(15,2) DEFAULT 0;
    DECLARE period_id INT;
    
    
    INSERT INTO shu_periods (period_start, period_end, calculation_method, status, calculated_by, calculated_at)
    VALUES (period_start, period_end, calculation_method, 'calculated', @user_id, NOW());
    
    SET period_id = LAST_INSERT_ID();
    
    
    SELECT SUM(CASE WHEN transaction_type = 'credit' THEN amount ELSE -amount END)
    INTO total_shu_amount
    FROM koperasi_transactions 
    WHERE transaction_date BETWEEN period_start AND period_end 
    AND status = 'posted';
    
    
    UPDATE shu_periods SET total_shu = total_shu_amount WHERE id = period_id;
    
    
    
    INSERT INTO shu_member_distribution (shu_period_id, member_id, component_code, base_amount, calculated_shu, percentage_share, status)
    SELECT 
        period_id,
        kt.member_id,
        sc.component_code,
        COALESCE(SUM(CASE WHEN kt.transaction_type = 'credit' THEN kt.amount ELSE 0 END), 0) as base_amount,
        (COALESCE(SUM(CASE WHEN kt.transaction_type = 'credit' THEN kt.amount ELSE 0 END), 0) * sc.percentage_weight / 100) as calculated_shu,
        sc.percentage_weight as percentage_share,
        'calculated'
    FROM koperasi_transactions kt
    CROSS JOIN shu_components sc
    WHERE kt.transaction_date BETWEEN period_start AND period_end 
    AND kt.status = 'posted'
    AND kt.member_id IS NOT NULL
    AND sc.is_active = 1
    GROUP BY kt.member_id, sc.component_code, sc.percentage_weight;
    
    SELECT period_id as result;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_address_options` (IN `parent_type` VARCHAR(20), IN `parent_id` INT)  BEGIN
    CASE parent_type
        WHEN 'province' THEN
            SELECT id, name FROM ref_provinces ORDER BY name;
        WHEN 'regency' THEN
            SELECT id, name FROM ref_regencies WHERE province_id = parent_id ORDER BY name;
        WHEN 'district' THEN
            SELECT id, name FROM ref_districts WHERE regency_id = parent_id ORDER BY name;
        WHEN 'village' THEN
            SELECT id, name, kodepos FROM ref_villages WHERE district_id = parent_id ORDER BY name;
        ELSE
            SELECT '' as id, '' as name;
    END CASE;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_full_address` (IN `province_id` INT, IN `regency_id` INT, IN `district_id` INT, IN `village_id` INT)  BEGIN
    SELECT 
        CONCAT(
            IFNULL(v.name, ''), ', ',
            IFNULL(d.name, ''), ', ',
            IFNULL(r.name, ''), ', ',
            IFNULL(p.name, '')
        ) as alamat_lengkap,
        v.kodepos
    FROM ref_provinces p
    LEFT JOIN ref_regencies r ON r.province_id = p.id AND r.id = regency_id
    LEFT JOIN ref_districts d ON d.regency_id = r.id AND d.id = district_id
    LEFT JOIN ref_villages v ON v.district_id = d.id AND v.id = village_id
    WHERE p.id = province_id
    LIMIT 1;
END$$

--
-- Fungsi
--
CREATE DEFINER=`root`@`localhost` FUNCTION `validate_address_id` (`table_name` VARCHAR(20), `id_value` INT) RETURNS TINYINT(1) READS SQL DATA
    DETERMINISTIC
BEGIN
    DECLARE result BOOLEAN DEFAULT FALSE;
    
    CASE table_name
        WHEN 'province' THEN
            SET result = (SELECT COUNT(*) > 0 FROM ref_provinces WHERE id = id_value);
        WHEN 'regency' THEN
            SET result = (SELECT COUNT(*) > 0 FROM ref_regencies WHERE id = id_value);
        WHEN 'district' THEN
            SET result = (SELECT COUNT(*) > 0 FROM ref_districts WHERE id = id_value);
        WHEN 'village' THEN
            SET result = (SELECT COUNT(*) > 0 FROM ref_villages WHERE id = id_value);
        ELSE
            SET result = FALSE;
    END CASE;
    
    RETURN result;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `address_stats`
--

CREATE TABLE `address_stats` (
  `id` int(11) NOT NULL,
  `total_provinces` int(11) DEFAULT 0,
  `total_regencies` int(11) DEFAULT 0,
  `total_districts` int(11) DEFAULT 0,
  `total_villages` int(11) DEFAULT 0,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `address_stats`
--

INSERT INTO `address_stats` (`id`, `total_provinces`, `total_regencies`, `total_districts`, `total_villages`, `last_updated`) VALUES
(1, 34, 34, 34, 34, '2026-02-12 20:45:12');

-- --------------------------------------------------------

--
-- Struktur dari tabel `anggota`
--

CREATE TABLE `anggota` (
  `id` int(11) NOT NULL,
  `no_anggota` varchar(20) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `nik` varchar(20) NOT NULL,
  `tempat_lahir` varchar(50) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `pekerjaan` varchar(50) DEFAULT NULL,
  `pendapatan_bulanan` decimal(12,2) DEFAULT NULL,
  `tanggal_gabung` date DEFAULT NULL,
  `status` enum('aktif','nonaktif','keluar') DEFAULT 'aktif',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `province_id` int(11) DEFAULT NULL,
  `regency_id` int(11) DEFAULT NULL,
  `district_id` int(11) DEFAULT NULL,
  `village_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `anggota`
--

INSERT INTO `anggota` (`id`, `no_anggota`, `nama_lengkap`, `nik`, `tempat_lahir`, `tanggal_lahir`, `jenis_kelamin`, `alamat`, `no_hp`, `email`, `pekerjaan`, `pendapatan_bulanan`, `tanggal_gabung`, `status`, `created_by`, `created_at`, `updated_at`, `province_id`, `regency_id`, `district_id`, `village_id`) VALUES
(1, 'TEST001', 'Test User', '1234567890123456', 'Test', '1990-01-01', 'L', 'Test Address', '08123456789', 'test@example.com', 'Test', '5000000.00', '2024-01-01', 'aktif', 1, '2026-02-12 20:34:05', '2026-02-12 20:34:05', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `angsuran`
--

CREATE TABLE `angsuran` (
  `id` int(11) NOT NULL,
  `pinjaman_id` int(11) NOT NULL,
  `no_angsuran` int(11) NOT NULL,
  `jumlah_angsuran` decimal(15,2) NOT NULL,
  `pokok` decimal(15,2) NOT NULL,
  `bunga` decimal(15,2) NOT NULL,
  `denda` decimal(15,2) DEFAULT 0.00,
  `total_bayar` decimal(15,2) NOT NULL,
  `tanggal_jatuh_tempo` date NOT NULL,
  `tanggal_bayar` datetime DEFAULT NULL,
  `status` enum('belum_bayar','terlambat','lunas') DEFAULT 'belum_bayar',
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `buku_besar`
--

CREATE TABLE `buku_besar` (
  `id` int(11) NOT NULL,
  `coa_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `description` text DEFAULT NULL,
  `debit` decimal(15,2) DEFAULT 0.00,
  `credit` decimal(15,2) DEFAULT 0.00,
  `saldo` decimal(15,2) DEFAULT 0.00,
  `jurnal_detail_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `coa`
--

CREATE TABLE `coa` (
  `id` int(11) NOT NULL,
  `kode_coa` varchar(20) NOT NULL,
  `nama_coa` varchar(100) NOT NULL,
  `tipe` enum('debit','kredit') NOT NULL,
  `level` int(11) DEFAULT 1,
  `parent_id` int(11) DEFAULT NULL,
  `saldo_awal` decimal(15,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `coa`
--

INSERT INTO `coa` (`id`, `kode_coa`, `nama_coa`, `tipe`, `level`, `parent_id`, `saldo_awal`, `is_active`, `created_at`) VALUES
(1, '1', 'AKTIVA', 'debit', 1, NULL, '0.00', 1, '2026-02-12 20:02:24'),
(2, '11', 'AKTIVA LANCAR', 'debit', 2, NULL, '0.00', 1, '2026-02-12 20:02:24'),
(3, '111', 'Kas', 'debit', 3, NULL, '0.00', 1, '2026-02-12 20:02:24'),
(4, '112', 'Bank', 'debit', 3, NULL, '0.00', 1, '2026-02-12 20:02:24'),
(5, '12', 'AKTIVA TETAP', 'debit', 2, NULL, '0.00', 1, '2026-02-12 20:02:24'),
(6, '121', 'Tanah & Bangunan', 'debit', 3, NULL, '0.00', 1, '2026-02-12 20:02:24'),
(7, '2', 'PASSIVA', 'kredit', 1, NULL, '0.00', 1, '2026-02-12 20:02:24'),
(8, '21', 'KEWAJIBAN LANCAR', 'kredit', 2, NULL, '0.00', 1, '2026-02-12 20:02:24'),
(9, '211', 'Simpanan Anggota', 'kredit', 3, NULL, '0.00', 1, '2026-02-12 20:02:24'),
(10, '3', 'EKUITAS', 'kredit', 1, NULL, '0.00', 1, '2026-02-12 20:02:24'),
(11, '31', 'MODAL', 'kredit', 2, NULL, '0.00', 1, '2026-02-12 20:02:24'),
(12, '4', 'PENDAPATAN', 'kredit', 1, NULL, '0.00', 1, '2026-02-12 20:02:24'),
(13, '5', 'BEBAN', 'debit', 1, NULL, '0.00', 1, '2026-02-12 20:02:24'),
(14, '1000', 'Kas', 'debit', 1, NULL, '0.00', 1, '2026-02-12 20:41:46'),
(15, '1100', 'Bank', 'debit', 1, NULL, '0.00', 1, '2026-02-12 20:41:46'),
(16, '1200', 'Piutang Anggota', 'debit', 1, NULL, '0.00', 1, '2026-02-12 20:41:46'),
(17, '1300', 'Piutang Pinjaman', 'debit', 1, NULL, '0.00', 1, '2026-02-12 20:41:46'),
(18, '1400', 'Inventaris', 'debit', 1, NULL, '0.00', 1, '2026-02-12 20:41:46'),
(19, '1500', 'Aset Tetap', 'debit', 1, NULL, '0.00', 1, '2026-02-12 20:41:46'),
(20, '2000', 'Simpanan Anggota', 'kredit', 1, NULL, '0.00', 1, '2026-02-12 20:41:46'),
(21, '2100', 'Pinjaman Bank', 'kredit', 1, NULL, '0.00', 1, '2026-02-12 20:41:46'),
(22, '2200', 'Hutang Usaha', 'kredit', 1, NULL, '0.00', 1, '2026-02-12 20:41:46'),
(23, '3000', 'Modal Pokok', 'kredit', 1, NULL, '0.00', 1, '2026-02-12 20:41:46'),
(24, '3100', 'Modal Penyerta', 'kredit', 1, NULL, '0.00', 1, '2026-02-12 20:41:46'),
(25, '3200', 'Cadangan Resiko', 'kredit', 1, NULL, '0.00', 1, '2026-02-12 20:41:46'),
(26, '3300', 'SHU Tahun Berjalan', 'kredit', 1, NULL, '0.00', 1, '2026-02-12 20:41:46'),
(27, '3400', 'SHU Ditahan', 'kredit', 1, NULL, '0.00', 1, '2026-02-12 20:41:46'),
(28, '4000', 'Pendapatan Bunga Pinjaman', 'kredit', 1, NULL, '0.00', 1, '2026-02-12 20:41:46'),
(29, '4100', 'Pendapatan Jasa Administrasi', 'kredit', 1, NULL, '0.00', 1, '2026-02-12 20:41:46'),
(30, '4200', 'Pendapatan Penjualan', 'kredit', 1, NULL, '0.00', 1, '2026-02-12 20:41:46'),
(31, '4300', 'Pendapatan Lain-lain', 'kredit', 1, NULL, '0.00', 1, '2026-02-12 20:41:46'),
(32, '5000', 'Beban Bunga Simpanan', 'debit', 1, NULL, '0.00', 1, '2026-02-12 20:41:46'),
(33, '5100', 'Beban Operasional', 'debit', 1, NULL, '0.00', 1, '2026-02-12 20:41:46'),
(34, '5200', 'Beban Penyusutan', 'debit', 1, NULL, '0.00', 1, '2026-02-12 20:41:46'),
(35, '5300', 'Beban Lain-lain', 'debit', 1, NULL, '0.00', 1, '2026-02-12 20:41:46');

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_jurnal`
--

CREATE TABLE `detail_jurnal` (
  `id` int(11) NOT NULL,
  `jurnal_id` int(11) NOT NULL,
  `coa_id` int(11) NOT NULL,
  `debit` decimal(15,2) DEFAULT 0.00,
  `kredit` decimal(15,2) DEFAULT 0.00,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_penjualan`
--

CREATE TABLE `detail_penjualan` (
  `id` int(11) NOT NULL,
  `penjualan_id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_satuan` decimal(12,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jenis_pinjaman`
--

CREATE TABLE `jenis_pinjaman` (
  `id` int(11) NOT NULL,
  `nama_pinjaman` varchar(50) NOT NULL,
  `kode` varchar(10) NOT NULL,
  `plafond_maksimal` decimal(15,2) DEFAULT 0.00,
  `bunga_pertahun` decimal(5,2) DEFAULT 0.00,
  `tenor_maksimal` int(11) DEFAULT 12,
  `denda_persen` decimal(5,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jenis_simpanan`
--

CREATE TABLE `jenis_simpanan` (
  `id` int(11) NOT NULL,
  `nama_simpanan` varchar(50) NOT NULL,
  `kode` varchar(10) NOT NULL,
  `jenis` enum('wajib','sukarela','berjangka') NOT NULL,
  `minimal_setoran` decimal(12,2) DEFAULT 0.00,
  `bunga_pertahun` decimal(5,2) DEFAULT 0.00,
  `periode_bulan` int(11) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jurnal`
--

CREATE TABLE `jurnal` (
  `id` int(11) NOT NULL,
  `no_jurnal` varchar(30) NOT NULL,
  `tanggal_jurnal` date NOT NULL,
  `keterangan` text DEFAULT NULL,
  `total_debit` decimal(15,2) DEFAULT 0.00,
  `total_kredit` decimal(15,2) DEFAULT 0.00,
  `status` enum('draft','posted') DEFAULT 'draft',
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jurnal_detail`
--

CREATE TABLE `jurnal_detail` (
  `id` int(11) NOT NULL,
  `jurnal_id` int(11) NOT NULL,
  `coa_id` int(11) NOT NULL,
  `debit` decimal(15,2) DEFAULT 0.00,
  `credit` decimal(15,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori_produk`
--

CREATE TABLE `kategori_produk` (
  `id` int(11) NOT NULL,
  `nama_kategori` varchar(50) NOT NULL,
  `kode` varchar(10) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `koperasi_activities`
--

CREATE TABLE `koperasi_activities` (
  `id` int(11) NOT NULL,
  `activity_code` varchar(50) NOT NULL,
  `activity_name` varchar(255) NOT NULL,
  `activity_type` enum('simpanan','pinjaman','jual_beli','investasi','jasa_lain') NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `koperasi_activities`
--

INSERT INTO `koperasi_activities` (`id`, `activity_code`, `activity_name`, `activity_type`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'SIM_POKOK', 'Simpanan Pokok', 'simpanan', 'Simpanan wajib sekali saat pendaftaran anggota', 1, '2026-02-12 20:53:43', '2026-02-12 20:53:43'),
(2, 'SIM_WAJIB', 'Simpanan Wajib', 'simpanan', 'Simpanan berkala sesuai ketentuan koperasi', 1, '2026-02-12 20:53:43', '2026-02-12 20:53:43'),
(3, 'SIM_SUKARELA', 'Simpanan Sukarela', 'simpanan', 'Simpanan sukarela dari anggota', 1, '2026-02-12 20:53:43', '2026-02-12 20:53:43'),
(4, 'SIM_BERJANGKA', 'Simpanan Berjangka', 'simpanan', 'Simpanan dengan periode tertentu', 1, '2026-02-12 20:53:43', '2026-02-12 20:53:43'),
(5, 'PINJ_ANGGOTA', 'Pinjaman Anggota', 'pinjaman', 'Pinjaman kepada anggota untuk kebutuhan produktif', 1, '2026-02-12 20:53:43', '2026-02-12 20:53:43'),
(6, 'PINJ_INVESTASI', 'Pinjaman Investasi', 'pinjaman', 'Pinjaman untuk investasi usaha anggota', 1, '2026-02-12 20:53:43', '2026-02-12 20:53:43'),
(7, 'PINJ_KONSUMTIF', 'Pinjaman Konsumtif', 'pinjaman', 'Pinjaman untuk kebutuhan konsumtif anggota', 1, '2026-02-12 20:53:43', '2026-02-12 20:53:43'),
(8, 'PINJ_KEPEMILIK', 'Pinjaman Kepemilik', 'pinjaman', 'Pinjaman kepada kepemilik koperasi', 1, '2026-02-12 20:53:43', '2026-02-12 20:53:43'),
(9, 'JUAL_PRODUK_ANGGOTA', 'Jual Produk Anggota', 'jual_beli', 'Penjualan produk dari anggota', 1, '2026-02-12 20:53:43', '2026-02-12 20:53:43'),
(10, 'JUAL_PRODUK_KOPERASI', 'Jual Produk Koperasi', 'jual_beli', 'Penjualan produk hasil usaha koperasi', 1, '2026-02-12 20:53:43', '2026-02-12 20:53:43'),
(11, 'BELI_KEBUTUHAN', 'Beli Kebutuhan Operasional', 'jual_beli', 'Pembelian barang untuk operasional koperasi', 1, '2026-02-12 20:53:43', '2026-02-12 20:53:43'),
(12, 'JUAL_EKSTERNAL', 'Jual ke Non-Anggota', 'jual_beli', 'Penjualan produk ke masyarakat umum', 1, '2026-02-12 20:53:43', '2026-02-12 20:53:43'),
(13, 'INVEST_MODAL_POKOK', 'Investasi Modal Pokok', 'investasi', 'Investasi modal pokok dari anggota', 1, '2026-02-12 20:53:43', '2026-02-12 20:53:43'),
(14, 'INVEST_MODAL_PENYERTA', 'Investasi Modal Penyerta', 'investasi', 'Investasi modal penyertaan anggota', 1, '2026-02-12 20:53:43', '2026-02-12 20:53:43'),
(15, 'INVEST_REKSA', 'Investasi Dana Cadangan', 'investasi', 'Investasi dana cadangan resiko', 1, '2026-02-12 20:53:43', '2026-02-12 20:53:43'),
(16, 'JASA_ADMINISTRASI', 'Jasa Administrasi', 'jasa_lain', 'Biaya administrasi dan jasa pengelolaan', 1, '2026-02-12 20:53:43', '2026-02-12 20:53:43'),
(17, 'JASA_PENYIMPANAN', 'Jasa Penyimpanan', 'jasa_lain', 'Jasa penyimpanan barang/produk', 1, '2026-02-12 20:53:43', '2026-02-12 20:53:43'),
(18, 'JASA_TRANSPORTASI', 'Jasa Transportasi', 'jasa_lain', 'Jasa pengiriman dan distribusi', 1, '2026-02-12 20:53:43', '2026-02-12 20:53:43'),
(19, 'JASA_KONSULTASI', 'Jasa Konsultasi', 'jasa_lain', 'Jasa konsultasi bisnis dan manajemen', 1, '2026-02-12 20:53:43', '2026-02-12 20:53:43');

-- --------------------------------------------------------

--
-- Struktur dari tabel `koperasi_meetings`
--

CREATE TABLE `koperasi_meetings` (
  `id` int(11) NOT NULL,
  `meeting_type` enum('rapat_anggota','rapat_pengurus','rapat_pengawas','rapat_kombinasi') NOT NULL,
  `meeting_title` varchar(255) NOT NULL,
  `meeting_date` datetime NOT NULL,
  `meeting_location` varchar(255) DEFAULT NULL,
  `agenda` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('scheduled','in_progress','completed','cancelled') DEFAULT 'scheduled',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `koperasi_sanctions`
--

CREATE TABLE `koperasi_sanctions` (
  `id` int(11) NOT NULL,
  `sanction_date` date NOT NULL,
  `member_id` int(11) NOT NULL,
  `sanction_type` enum('teguran_lisan','teguran_tertulis','suspensi','pemberhentian','pemecatan') NOT NULL,
  `sanction_reason` text NOT NULL,
  `sanction_period_days` int(11) DEFAULT 0,
  `issued_by` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `status` enum('issued','serving','completed','appealed','cancelled') DEFAULT 'issued',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `koperasi_transactions`
--

CREATE TABLE `koperasi_transactions` (
  `id` int(11) NOT NULL,
  `transaction_date` date NOT NULL,
  `activity_code` varchar(50) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `transaction_type` enum('debit','credit') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `description` text DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `status` enum('draft','posted','cancelled') DEFAULT 'draft',
  `created_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Trigger `koperasi_transactions`
--
DELIMITER $$
CREATE TRIGGER `log_koperasi_transaction` AFTER INSERT ON `koperasi_transactions` FOR EACH ROW BEGIN
    INSERT INTO logs (user_id, action, table_name, record_id, old_values, new_values, created_at)
    VALUES (
        NEW.created_by,
        'CREATE',
        'koperasi_transactions',
        NEW.id,
        NULL,
        JSON_OBJECT(
            'activity_code', NEW.activity_code,
            'amount', NEW.amount,
            'transaction_type', NEW.transaction_type,
            'member_id', NEW.member_id
        ),
        NOW()
    );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_data`)),
  `new_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_data`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `logs`
--

INSERT INTO `logs` (`id`, `user_id`, `action`, `table_name`, `record_id`, `old_data`, `new_data`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'login', 'users', 1, NULL, '{\"id\":1,\"username\":\"admin\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"admin@ksp_samosir.com\",\"full_name\":\"Administrator\",\"role\":\"admin\",\"is_active\":1}', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-12 20:03:15'),
(2, 1, 'login', 'users', 1, NULL, '{\"id\":1,\"username\":\"admin\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"admin@ksp_samosir.com\",\"full_name\":\"Administrator\",\"role\":\"admin\",\"is_active\":1}', '127.0.0.1', 'curl/7.81.0', '2026-02-12 20:24:58'),
(3, 1, 'login', 'users', 1, NULL, '{\"id\":1,\"username\":\"admin\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"admin@ksp_samosir.com\",\"full_name\":\"Administrator\",\"role\":\"admin\",\"is_active\":1}', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-12 20:25:17'),
(4, 1, 'login', 'users', 1, NULL, '{\"id\":1,\"username\":\"admin\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"admin@ksp_samosir.com\",\"full_name\":\"Administrator\",\"role\":\"admin\",\"is_active\":1}', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-12 20:25:29'),
(5, 1, 'login', 'users', 1, NULL, '{\"id\":1,\"username\":\"admin\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"admin@ksp_samosir.com\",\"full_name\":\"Administrator\",\"role\":\"admin\",\"is_active\":1}', '127.0.0.1', 'curl/7.81.0', '2026-02-12 20:26:50'),
(6, 1, 'login', 'users', 1, NULL, '{\"id\":1,\"username\":\"admin\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"admin@ksp_samosir.com\",\"full_name\":\"Administrator\",\"role\":\"admin\",\"is_active\":1}', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-12 20:27:19'),
(7, 1, 'login', 'users', 1, NULL, '{\"id\":1,\"username\":\"admin\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"admin@ksp_samosir.com\",\"full_name\":\"Administrator\",\"role\":\"admin\",\"is_active\":1}', '127.0.0.1', 'curl/7.81.0', '2026-02-12 20:28:12'),
(8, 1, 'login', 'users', 1, NULL, '{\"id\":1,\"username\":\"admin\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"admin@ksp_samosir.com\",\"full_name\":\"Administrator\",\"role\":\"admin\",\"is_active\":1}', '127.0.0.1', 'curl/7.81.0', '2026-02-12 20:29:01'),
(9, 1, 'login', 'users', 1, NULL, '{\"id\":1,\"username\":\"admin\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"admin@ksp_samosir.com\",\"full_name\":\"Administrator\",\"role\":\"admin\",\"is_active\":1}', '127.0.0.1', 'curl/7.81.0', '2026-02-12 20:33:02'),
(10, 1, 'login', 'users', 1, NULL, '{\"id\":1,\"username\":\"admin\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"admin@ksp_samosir.com\",\"full_name\":\"Administrator\",\"role\":\"admin\",\"is_active\":1}', '127.0.0.1', 'curl/7.81.0', '2026-02-12 20:33:31'),
(11, 1, 'login', 'users', 1, NULL, '{\"id\":1,\"username\":\"admin\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"admin@ksp_samosir.com\",\"full_name\":\"Administrator\",\"role\":\"admin\",\"is_active\":1}', '127.0.0.1', 'curl/7.81.0', '2026-02-12 20:33:45'),
(12, 1, 'login', 'users', 1, NULL, '{\"id\":1,\"username\":\"admin\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"admin@ksp_samosir.com\",\"full_name\":\"Administrator\",\"role\":\"admin\",\"is_active\":1}', '127.0.0.1', 'curl/7.81.0', '2026-02-12 20:34:05'),
(13, 2, 'login', 'users', 2, NULL, '{\"id\":2,\"username\":\"staff\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"staff@ksp_samosir.com\",\"full_name\":\"Staff User\",\"role\":\"staff\",\"is_active\":1}', '127.0.0.1', 'curl/7.81.0', '2026-02-12 20:34:37'),
(14, 3, 'login', 'users', 3, NULL, '{\"id\":3,\"username\":\"member\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"member@ksp_samosir.com\",\"full_name\":\"Member User\",\"role\":\"member\",\"is_active\":1}', '127.0.0.1', 'curl/7.81.0', '2026-02-12 20:34:40'),
(15, 1, 'login', 'users', 1, NULL, '{\"id\":1,\"username\":\"admin\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"admin@ksp_samosir.com\",\"full_name\":\"Administrator\",\"role\":\"admin\",\"is_active\":1}', '127.0.0.1', 'curl/7.81.0', '2026-02-12 20:35:05'),
(16, 2, 'login', 'users', 2, NULL, '{\"id\":2,\"username\":\"staff\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"staff@ksp_samosir.com\",\"full_name\":\"Staff User\",\"role\":\"staff\",\"is_active\":1}', '127.0.0.1', 'curl/7.81.0', '2026-02-12 20:35:08'),
(17, 3, 'login', 'users', 3, NULL, '{\"id\":3,\"username\":\"member\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"member@ksp_samosir.com\",\"full_name\":\"Member User\",\"role\":\"member\",\"is_active\":1}', '127.0.0.1', 'curl/7.81.0', '2026-02-12 20:35:10'),
(18, 3, 'login', 'users', 3, NULL, '{\"id\":3,\"username\":\"member\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"member@ksp_samosir.com\",\"full_name\":\"Member User\",\"role\":\"member\",\"is_active\":1}', '127.0.0.1', 'curl/7.81.0', '2026-02-12 20:35:23'),
(19, 3, 'login', 'users', 3, NULL, '{\"id\":3,\"username\":\"member\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"member@ksp_samosir.com\",\"full_name\":\"Member User\",\"role\":\"member\",\"is_active\":1}', '127.0.0.1', 'curl/7.81.0', '2026-02-12 20:35:44'),
(20, 1, 'login', 'users', 1, NULL, '{\"id\":1,\"username\":\"admin\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"admin@ksp_samosir.com\",\"full_name\":\"Administrator\",\"is_active\":1,\"last_login\":\"2026-02-13 03:35:05\",\"role\":\"super_admin\",\"role_description\":\"Super administrator with full system access\"}', '127.0.0.1', 'curl/7.81.0', '2026-02-12 20:42:45'),
(21, 1, 'login', 'users', 1, NULL, '{\"id\":1,\"username\":\"admin\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"admin@ksp_samosir.com\",\"full_name\":\"Administrator\",\"is_active\":1,\"last_login\":\"2026-02-13 03:42:45\",\"role\":\"super_admin\",\"role_description\":\"Super administrator with full system access\"}', '127.0.0.1', 'curl/7.81.0', '2026-02-12 20:43:09'),
(22, 1, 'login', 'users', 1, NULL, '{\"id\":1,\"username\":\"admin\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"admin@ksp_samosir.com\",\"full_name\":\"Administrator\",\"is_active\":1,\"last_login\":\"2026-02-13 03:43:09\",\"role\":\"super_admin\",\"role_description\":\"Super administrator with full system access\"}', '127.0.0.1', 'curl/7.81.0', '2026-02-12 20:43:23'),
(23, 1, 'login', 'users', 1, NULL, '{\"id\":1,\"username\":\"admin\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"admin@ksp_samosir.com\",\"full_name\":\"Administrator\",\"is_active\":1,\"last_login\":\"2026-02-13 03:43:23\",\"role\":\"super_admin\",\"role_description\":\"Super administrator with full system access\"}', '127.0.0.1', 'curl/7.81.0', '2026-02-12 20:43:35'),
(24, 1, 'login', 'users', 1, NULL, '{\"id\":1,\"username\":\"admin\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"admin@ksp_samosir.com\",\"full_name\":\"Administrator\",\"is_active\":1,\"last_login\":\"2026-02-13 03:43:35\",\"role\":\"super_admin\",\"role_description\":\"Super administrator with full system access\"}', '127.0.0.1', 'curl/7.81.0', '2026-02-12 20:43:46'),
(25, 1, 'login', 'users', 1, NULL, '{\"id\":1,\"username\":\"admin\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"admin@ksp_samosir.com\",\"full_name\":\"Administrator\",\"is_active\":1,\"last_login\":\"2026-02-13 03:43:46\",\"role\":\"super_admin\",\"role_description\":\"Super administrator with full system access\"}', '127.0.0.1', 'curl/7.81.0', '2026-02-12 20:46:33'),
(26, 1, 'login', 'users', 1, NULL, '{\"id\":1,\"username\":\"admin\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"admin@ksp_samosir.com\",\"full_name\":\"Administrator\",\"is_active\":1,\"last_login\":\"2026-02-13 03:46:33\",\"role\":\"super_admin\",\"role_description\":\"Super administrator with full system access\"}', '127.0.0.1', 'curl/7.81.0', '2026-02-12 20:46:56'),
(27, 3, 'login', 'users', 3, NULL, '{\"id\":3,\"username\":\"member\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"member@ksp_samosir.com\",\"full_name\":\"Member User\",\"is_active\":1,\"last_login\":\"2026-02-13 03:35:44\",\"role\":\"member\",\"role_description\":\"Regular member with limited access\"}', '127.0.0.1', 'curl/7.81.0', '2026-02-12 20:48:06'),
(28, 1, 'login', 'users', 1, NULL, '{\"id\":1,\"username\":\"admin\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"admin@ksp_samosir.com\",\"full_name\":\"Administrator\",\"is_active\":1,\"last_login\":\"2026-02-13 03:46:56\",\"role\":\"admin\",\"role_description\":\"Administrator with management access\"}', '127.0.0.1', 'curl/7.81.0', '2026-02-12 20:49:22'),
(29, 1, 'login', 'users', 1, NULL, '{\"id\":1,\"username\":\"admin\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"admin@ksp_samosir.com\",\"full_name\":\"Administrator\",\"is_active\":1,\"last_login\":\"2026-02-13 03:49:22\",\"role\":\"admin\",\"role_description\":\"Administrator with management access\"}', '127.0.0.1', 'curl/7.81.0', '2026-02-12 20:56:34'),
(30, 1, 'login', 'users', 1, NULL, '{\"id\":1,\"username\":\"admin\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"admin@ksp_samosir.com\",\"full_name\":\"Administrator\",\"is_active\":1,\"last_login\":\"2026-02-13 03:56:34\",\"role\":\"admin\",\"role_description\":\"Administrator with management access\"}', '127.0.0.1', 'curl/7.81.0', '2026-02-12 20:59:32');

-- --------------------------------------------------------

--
-- Struktur dari tabel `meeting_attendance`
--

CREATE TABLE `meeting_attendance` (
  `id` int(11) NOT NULL,
  `meeting_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `attendance_status` enum('hadir','izin','tanpa_keterangan','tidak_hadir') DEFAULT 'tidak_hadir',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `meeting_decisions`
--

CREATE TABLE `meeting_decisions` (
  `id` int(11) NOT NULL,
  `meeting_id` int(11) NOT NULL,
  `decision_number` int(11) NOT NULL,
  `decision_title` varchar(255) NOT NULL,
  `decision_content` text NOT NULL,
  `decision_type` enum('kebijakan','program','anggaran','personalia','lainnya') DEFAULT 'kebijakan',
  `implementation_status` enum('belum_direalisasi','sedang_direalisasi','selesai','dibatalkan') DEFAULT 'belum_direalisasi',
  `responsible_person` int(11) DEFAULT NULL,
  `target_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `modal_pokok`
--

CREATE TABLE `modal_pokok` (
  `id` int(11) NOT NULL,
  `anggota_id` int(11) NOT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `jenis` enum('masuk','keluar') NOT NULL,
  `tanggal` date NOT NULL,
  `description` text DEFAULT NULL,
  `bukti` varchar(255) DEFAULT NULL,
  `status` enum('draft','approved','rejected') DEFAULT 'draft',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id` int(11) NOT NULL,
  `kode_pelanggan` varchar(20) NOT NULL,
  `nama_pelanggan` varchar(100) NOT NULL,
  `alamat` text DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `jenis_pelanggan` enum('member','non_member') DEFAULT 'non_member',
  `anggota_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengaturan_koperasi`
--

CREATE TABLE `pengaturan_koperasi` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `type` enum('string','number','decimal','boolean','date') DEFAULT 'string',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengaturan_koperasi`
--

INSERT INTO `pengaturan_koperasi` (`id`, `setting_key`, `value`, `description`, `type`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'nama_koperasi', 'KSP Samosir', 'Nama Koperasi', 'string', 1, '2026-02-12 20:42:00', '2026-02-12 20:42:00'),
(2, 'alamat', 'Jl. Contoh No. 123', 'Alamat Koperasi', 'string', 1, '2026-02-12 20:42:00', '2026-02-12 20:42:00'),
(3, 'no_telp', '(021) 1234567', 'Nomor Telepon', 'string', 1, '2026-02-12 20:42:00', '2026-02-12 20:42:00'),
(4, 'email', 'info@ksp_samosir.co.id', 'Email Koperasi', 'string', 1, '2026-02-12 20:42:00', '2026-02-12 20:42:00'),
(5, 'bunga_simpanan_wajib', '3.00', 'Bunga Simpanan Wajib (%)', 'decimal', 1, '2026-02-12 20:42:00', '2026-02-12 20:42:00'),
(6, 'bunga_simpanan_sukarela', '4.00', 'Bunga Simpanan Sukarela (%)', 'decimal', 1, '2026-02-12 20:42:00', '2026-02-12 20:42:00'),
(7, 'bunga_pinjaman', '12.00', 'Bunga Pinjaman (%)', 'decimal', 1, '2026-02-12 20:42:00', '2026-02-12 20:42:00'),
(8, 'provisi_rugi', '5.00', 'Provisi Rugi (%)', 'decimal', 1, '2026-02-12 20:42:00', '2026-02-12 20:42:00'),
(9, 'cadangan_resiko', '2.00', 'Cadangan Resiko (%)', 'decimal', 1, '2026-02-12 20:42:00', '2026-02-12 20:42:00'),
(10, 'periode_akuntansi', 'tahunan', 'Periode Akuntansi', 'string', 1, '2026-02-12 20:42:00', '2026-02-12 20:42:00'),
(11, 'tahun_buku', '2024', 'Tahun Buku', 'string', 1, '2026-02-12 20:42:00', '2026-02-12 20:42:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `penjualan`
--

CREATE TABLE `penjualan` (
  `id` int(11) NOT NULL,
  `no_faktur` varchar(30) NOT NULL,
  `pelanggan_id` int(11) DEFAULT NULL,
  `total_harga` decimal(15,2) NOT NULL,
  `total_bayar` decimal(15,2) DEFAULT 0.00,
  `kembalian` decimal(15,2) DEFAULT 0.00,
  `status_pembayaran` enum('lunas','belum_lunas','cicil') DEFAULT 'belum_lunas',
  `metode_pembayaran` enum('cash','transfer','debit','kredit') DEFAULT 'cash',
  `tanggal_penjualan` datetime DEFAULT current_timestamp(),
  `user_id` int(11) NOT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `module` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `description`, `module`, `created_at`, `is_active`) VALUES
(1, 'view_dashboard', 'View dashboard', 'dashboard', '2026-02-12 20:37:32', 1),
(2, 'view_anggota', 'View member list', 'anggota', '2026-02-12 20:37:32', 1),
(3, 'create_anggota', 'Create new member', 'anggota', '2026-02-12 20:37:32', 1),
(4, 'edit_anggota', 'Edit member information', 'anggota', '2026-02-12 20:37:32', 1),
(5, 'delete_anggota', 'Delete member', 'anggota', '2026-02-12 20:37:32', 1),
(6, 'view_simpanan', 'View savings', 'simpanan', '2026-02-12 20:37:32', 1),
(7, 'create_simpanan', 'Create savings account', 'simpanan', '2026-02-12 20:37:32', 1),
(8, 'edit_simpanan', 'Edit savings', 'simpanan', '2026-02-12 20:37:32', 1),
(9, 'delete_simpanan', 'Delete savings', 'simpanan', '2026-02-12 20:37:32', 1),
(10, 'transaksi_simpanan', 'Process savings transactions', 'simpanan', '2026-02-12 20:37:32', 1),
(11, 'view_pinjaman', 'View loan applications', 'pinjaman', '2026-02-12 20:37:32', 1),
(12, 'create_pinjaman', 'Create loan application', 'pinjaman', '2026-02-12 20:37:32', 1),
(13, 'edit_pinjaman', 'Edit loan details', 'pinjaman', '2026-02-12 20:37:32', 1),
(14, 'delete_pinjaman', 'Delete loan', 'pinjaman', '2026-02-12 20:37:32', 1),
(15, 'approve_pinjaman', 'Approve loan applications', 'pinjaman', '2026-02-12 20:37:32', 1),
(16, 'cairkan_pinjaman', 'Disburse approved loans', 'pinjaman', '2026-02-12 20:37:32', 1),
(17, 'view_produk', 'View products', 'produk', '2026-02-12 20:37:32', 1),
(18, 'create_produk', 'Create new product', 'produk', '2026-02-12 20:37:32', 1),
(19, 'edit_produk', 'Edit product information', 'produk', '2026-02-12 20:37:32', 1),
(20, 'delete_produk', 'Delete product', 'produk', '2026-02-12 20:37:32', 1),
(21, 'view_penjualan', 'View sales', 'penjualan', '2026-02-12 20:37:32', 1),
(22, 'create_penjualan', 'Create sale transaction', 'penjualan', '2026-02-12 20:37:32', 1),
(23, 'edit_penjualan', 'Edit sale', 'penjualan', '2026-02-12 20:37:32', 1),
(24, 'delete_penjualan', 'Delete sale', 'penjualan', '2026-02-12 20:37:32', 1),
(25, 'view_laporan', 'View reports', 'laporan', '2026-02-12 20:37:32', 1),
(26, 'generate_laporan', 'Generate reports', 'laporan', '2026-02-12 20:37:32', 1),
(27, 'export_laporan', 'Export reports', 'laporan', '2026-02-12 20:37:32', 1),
(28, 'view_settings', 'View settings', 'settings', '2026-02-12 20:37:32', 1),
(29, 'edit_settings', 'Edit settings', 'settings', '2026-02-12 20:37:32', 1),
(30, 'manage_users', 'Manage user accounts', 'settings', '2026-02-12 20:37:32', 1),
(31, 'view_logs', 'View audit logs', 'system', '2026-02-12 20:37:32', 1),
(32, 'manage_permissions', 'Manage role permissions', 'system', '2026-02-12 20:37:32', 1),
(33, 'admin_access', 'Full administrative access', 'system', '2026-02-12 20:37:32', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `pinjaman`
--

CREATE TABLE `pinjaman` (
  `id` int(11) NOT NULL,
  `anggota_id` int(11) NOT NULL,
  `jenis_pinjaman_id` int(11) NOT NULL,
  `no_pinjaman` varchar(30) NOT NULL,
  `jumlah_pinjaman` decimal(15,2) NOT NULL,
  `bunga_persen` decimal(5,2) NOT NULL,
  `tenor_bulan` int(11) NOT NULL,
  `angsuran_pokok` decimal(15,2) NOT NULL,
  `angsuran_bunga` decimal(15,2) NOT NULL,
  `total_angsuran` decimal(15,2) NOT NULL,
  `status` enum('pengajuan','disetujui','dicairkan','lunas','ditolak') DEFAULT 'pengajuan',
  `tanggal_pengajuan` date DEFAULT NULL,
  `tanggal_disetujui` date DEFAULT NULL,
  `tanggal_pencairan` date DEFAULT NULL,
  `tanggal_jatuh_tempo` date DEFAULT NULL,
  `tujuan_pinjaman` text DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `id` int(11) NOT NULL,
  `kode_produk` varchar(30) NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `kategori_id` int(11) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga_beli` decimal(12,2) DEFAULT 0.00,
  `harga_jual` decimal(12,2) DEFAULT 0.00,
  `stok` int(11) DEFAULT 0,
  `stok_minimal` int(11) DEFAULT 0,
  `satuan` varchar(20) DEFAULT 'pcs',
  `gambar` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ref_districts`
--

CREATE TABLE `ref_districts` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `regency_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `ref_districts`
--

INSERT INTO `ref_districts` (`id`, `name`, `regency_id`, `created_at`) VALUES
(1, 'Banda Aceh', 1, '2026-02-12 20:45:11'),
(2, 'Medan Kota', 2, '2026-02-12 20:45:11'),
(3, 'Padang Barat', 3, '2026-02-12 20:45:11'),
(4, 'Pekanbaru Kota', 4, '2026-02-12 20:45:11'),
(5, 'Jambi Selatan', 5, '2026-02-12 20:45:11'),
(6, 'Palembang', 6, '2026-02-12 20:45:11'),
(7, 'Bengkulu', 7, '2026-02-12 20:45:11'),
(8, 'Bandar Lampung', 8, '2026-02-12 20:45:11'),
(9, 'Pangkal Pinang', 9, '2026-02-12 20:45:11'),
(10, 'Tanjung Pinang', 10, '2026-02-12 20:45:11'),
(11, 'Tanah Abang', 11, '2026-02-12 20:45:11'),
(12, 'Bandung', 12, '2026-02-12 20:45:11'),
(13, 'Semarang Barat', 13, '2026-02-12 20:45:11'),
(14, 'Yogyakarta', 14, '2026-02-12 20:45:11'),
(15, 'Surabaya', 15, '2026-02-12 20:45:11'),
(16, 'Serang', 16, '2026-02-12 20:45:11'),
(17, 'Denpasar', 17, '2026-02-12 20:45:11'),
(18, 'Mataram', 18, '2026-02-12 20:45:11'),
(19, 'Kupang', 19, '2026-02-12 20:45:11'),
(20, 'Pontianak Kota', 20, '2026-02-12 20:45:11'),
(21, 'Palangka Raya', 21, '2026-02-12 20:45:11'),
(22, 'Banjarmasin', 22, '2026-02-12 20:45:11'),
(23, 'Samarinda', 23, '2026-02-12 20:45:11'),
(24, 'Tarakan', 24, '2026-02-12 20:45:11'),
(25, 'Manado', 25, '2026-02-12 20:45:11'),
(26, 'Palu Barat', 26, '2026-02-12 20:45:11'),
(27, 'Makassar', 27, '2026-02-12 20:45:11'),
(28, 'Kendari', 28, '2026-02-12 20:45:11'),
(29, 'Gorontalo', 29, '2026-02-12 20:45:11'),
(30, 'Mamuju', 30, '2026-02-12 20:45:11'),
(31, 'Ambon', 31, '2026-02-12 20:45:11'),
(32, 'Ternate', 32, '2026-02-12 20:45:11'),
(33, 'Sorong', 33, '2026-02-12 20:45:11'),
(34, 'Jayapura', 34, '2026-02-12 20:45:11');

-- --------------------------------------------------------

--
-- Struktur dari tabel `ref_provinces`
--

CREATE TABLE `ref_provinces` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `ref_provinces`
--

INSERT INTO `ref_provinces` (`id`, `name`, `created_at`) VALUES
(1, 'Aceh', '2026-02-12 20:45:11'),
(2, 'Sumatera Utara', '2026-02-12 20:45:11'),
(3, 'Sumatera Barat', '2026-02-12 20:45:11'),
(4, 'Riau', '2026-02-12 20:45:11'),
(5, 'Jambi', '2026-02-12 20:45:11'),
(6, 'Sumatera Selatan', '2026-02-12 20:45:11'),
(7, 'Bengkulu', '2026-02-12 20:45:11'),
(8, 'Lampung', '2026-02-12 20:45:11'),
(9, 'Kepulauan Bangka Belitung', '2026-02-12 20:45:11'),
(10, 'Kepulauan Riau', '2026-02-12 20:45:11'),
(11, 'DKI Jakarta', '2026-02-12 20:45:11'),
(12, 'Jawa Barat', '2026-02-12 20:45:11'),
(13, 'Jawa Tengah', '2026-02-12 20:45:11'),
(14, 'DI Yogyakarta', '2026-02-12 20:45:11'),
(15, 'Jawa Timur', '2026-02-12 20:45:11'),
(16, 'Banten', '2026-02-12 20:45:11'),
(17, 'Bali', '2026-02-12 20:45:11'),
(18, 'Nusa Tenggara Barat', '2026-02-12 20:45:11'),
(19, 'Nusa Tenggara Timur', '2026-02-12 20:45:11'),
(20, 'Kalimantan Barat', '2026-02-12 20:45:11'),
(21, 'Kalimantan Tengah', '2026-02-12 20:45:11'),
(22, 'Kalimantan Selatan', '2026-02-12 20:45:11'),
(23, 'Kalimantan Timur', '2026-02-12 20:45:11'),
(24, 'Kalimantan Utara', '2026-02-12 20:45:11'),
(25, 'Sulawesi Utara', '2026-02-12 20:45:11'),
(26, 'Sulawesi Tengah', '2026-02-12 20:45:11'),
(27, 'Sulawesi Selatan', '2026-02-12 20:45:11'),
(28, 'Sulawesi Tenggara', '2026-02-12 20:45:11'),
(29, 'Gorontalo', '2026-02-12 20:45:11'),
(30, 'Sulawesi Barat', '2026-02-12 20:45:11'),
(31, 'Maluku', '2026-02-12 20:45:11'),
(32, 'Maluku Utara', '2026-02-12 20:45:11'),
(33, 'Papua Barat', '2026-02-12 20:45:11'),
(34, 'Papua', '2026-02-12 20:45:11');

-- --------------------------------------------------------

--
-- Struktur dari tabel `ref_regencies`
--

CREATE TABLE `ref_regencies` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `province_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `ref_regencies`
--

INSERT INTO `ref_regencies` (`id`, `name`, `province_id`, `created_at`) VALUES
(1, 'Kota Banda Aceh', 1, '2026-02-12 20:45:11'),
(2, 'Kota Medan', 2, '2026-02-12 20:45:11'),
(3, 'Kota Padang', 3, '2026-02-12 20:45:11'),
(4, 'Kota Pekanbaru', 4, '2026-02-12 20:45:11'),
(5, 'Kota Jambi', 5, '2026-02-12 20:45:11'),
(6, 'Kota Palembang', 6, '2026-02-12 20:45:11'),
(7, 'Kota Bengkulu', 7, '2026-02-12 20:45:11'),
(8, 'Kota Bandar Lampung', 8, '2026-02-12 20:45:11'),
(9, 'Kota Pangkal Pinang', 9, '2026-02-12 20:45:11'),
(10, 'Kota Tanjung Pinang', 10, '2026-02-12 20:45:11'),
(11, 'Kota Jakarta Pusat', 11, '2026-02-12 20:45:11'),
(12, 'Kota Bandung', 12, '2026-02-12 20:45:11'),
(13, 'Kota Semarang', 13, '2026-02-12 20:45:11'),
(14, 'Kota Yogyakarta', 14, '2026-02-12 20:45:11'),
(15, 'Kota Surabaya', 15, '2026-02-12 20:45:11'),
(16, 'Kota Serang', 16, '2026-02-12 20:45:11'),
(17, 'Kota Denpasar', 17, '2026-02-12 20:45:11'),
(18, 'Kota Mataram', 18, '2026-02-12 20:45:11'),
(19, 'Kota Kupang', 19, '2026-02-12 20:45:11'),
(20, 'Kota Pontianak', 20, '2026-02-12 20:45:11'),
(21, 'Kota Palangka Raya', 21, '2026-02-12 20:45:11'),
(22, 'Kota Banjarmasin', 22, '2026-02-12 20:45:11'),
(23, 'Kota Samarinda', 23, '2026-02-12 20:45:11'),
(24, 'Kota Tarakan', 24, '2026-02-12 20:45:11'),
(25, 'Kota Manado', 25, '2026-02-12 20:45:11'),
(26, 'Kota Palu', 26, '2026-02-12 20:45:11'),
(27, 'Kota Makassar', 27, '2026-02-12 20:45:11'),
(28, 'Kota Kendari', 28, '2026-02-12 20:45:11'),
(29, 'Kota Gorontalo', 29, '2026-02-12 20:45:11'),
(30, 'Kota Mamuju', 30, '2026-02-12 20:45:11'),
(31, 'Kota Ambon', 31, '2026-02-12 20:45:11'),
(32, 'Kota Ternate', 32, '2026-02-12 20:45:11'),
(33, 'Kota Sorong', 33, '2026-02-12 20:45:11'),
(34, 'Kota Jayapura', 34, '2026-02-12 20:45:11');

-- --------------------------------------------------------

--
-- Struktur dari tabel `ref_villages`
--

CREATE TABLE `ref_villages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `district_id` int(11) NOT NULL,
  `kodepos` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `ref_villages`
--

INSERT INTO `ref_villages` (`id`, `name`, `district_id`, `kodepos`, `created_at`) VALUES
(1, 'Peunayong', 1, '23111', '2026-02-12 20:45:11'),
(2, 'Medan Kota', 2, '20211', '2026-02-12 20:45:11'),
(3, 'Padang Pasir', 3, '25111', '2026-02-12 20:45:11'),
(4, 'Pekanbaru Kota', 4, '28111', '2026-02-12 20:45:11'),
(5, 'Jambi Selatan', 5, '36111', '2026-02-12 20:45:11'),
(6, 'Palembang', 6, '30111', '2026-02-12 20:45:11'),
(7, 'Bengkulu', 7, '38111', '2026-02-12 20:45:11'),
(8, 'Bandar Lampung', 8, '35111', '2026-02-12 20:45:11'),
(9, 'Pangkal Pinang', 9, '33111', '2026-02-12 20:45:11'),
(10, 'Tanjung Pinang', 10, '29111', '2026-02-12 20:45:11'),
(11, 'Tanah Abang', 11, '10230', '2026-02-12 20:45:11'),
(12, 'Bandung', 12, '40111', '2026-02-12 20:45:11'),
(13, 'Semarang Barat', 13, '50111', '2026-02-12 20:45:11'),
(14, 'Yogyakarta', 14, '55111', '2026-02-12 20:45:11'),
(15, 'Surabaya', 15, '60111', '2026-02-12 20:45:11'),
(16, 'Serang', 16, '42111', '2026-02-12 20:45:11'),
(17, 'Denpasar', 17, '80111', '2026-02-12 20:45:11'),
(18, 'Mataram', 18, '83111', '2026-02-12 20:45:11'),
(19, 'Kupang', 19, '85111', '2026-02-12 20:45:11'),
(20, 'Pontianak Kota', 20, '78111', '2026-02-12 20:45:11'),
(21, 'Palangka Raya', 21, '73111', '2026-02-12 20:45:11'),
(22, 'Banjarmasin', 22, '70111', '2026-02-12 20:45:11'),
(23, 'Samarinda', 23, '75111', '2026-02-12 20:45:11'),
(24, 'Tarakan', 24, '77111', '2026-02-12 20:45:11'),
(25, 'Manado', 25, '95111', '2026-02-12 20:45:11'),
(26, 'Palu Barat', 26, '94111', '2026-02-12 20:45:11'),
(27, 'Makassar', 27, '90111', '2026-02-12 20:45:11'),
(28, 'Kendari', 28, '93111', '2026-02-12 20:45:11'),
(29, 'Gorontalo', 29, '96111', '2026-02-12 20:45:11'),
(30, 'Mamuju', 30, '91511', '2026-02-12 20:45:11'),
(31, 'Ambon', 31, '97111', '2026-02-12 20:45:11'),
(32, 'Ternate', 32, '97711', '2026-02-12 20:45:11'),
(33, 'Sorong', 33, '98411', '2026-02-12 20:45:11'),
(34, 'Jayapura', 34, '99111', '2026-02-12 20:45:11');

-- --------------------------------------------------------

--
-- Struktur dari tabel `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `created_at`, `is_active`) VALUES
(1, 'super_admin', 'Super administrator with full system access', '2026-02-12 20:37:32', 1),
(2, 'admin', 'Administrator with management access', '2026-02-12 20:37:32', 1),
(3, 'supervisor', 'Supervisor with approval access', '2026-02-12 20:37:32', 1),
(4, 'staff', 'Staff member with operational access', '2026-02-12 20:37:32', 1),
(5, 'member', 'Regular member with limited access', '2026-02-12 20:37:32', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`, `assigned_at`) VALUES
(1, 1, '2026-02-12 20:37:55'),
(1, 2, '2026-02-12 20:37:55'),
(1, 3, '2026-02-12 20:37:55'),
(1, 4, '2026-02-12 20:37:55'),
(1, 5, '2026-02-12 20:37:55'),
(1, 6, '2026-02-12 20:37:55'),
(1, 7, '2026-02-12 20:37:55'),
(1, 8, '2026-02-12 20:37:55'),
(1, 9, '2026-02-12 20:37:55'),
(1, 10, '2026-02-12 20:37:55'),
(1, 11, '2026-02-12 20:37:55'),
(1, 12, '2026-02-12 20:37:55'),
(1, 13, '2026-02-12 20:37:55'),
(1, 14, '2026-02-12 20:37:55'),
(1, 15, '2026-02-12 20:37:55'),
(1, 16, '2026-02-12 20:37:55'),
(1, 17, '2026-02-12 20:37:55'),
(1, 18, '2026-02-12 20:37:55'),
(1, 19, '2026-02-12 20:37:55'),
(1, 20, '2026-02-12 20:37:55'),
(1, 21, '2026-02-12 20:37:55'),
(1, 22, '2026-02-12 20:37:55'),
(1, 23, '2026-02-12 20:37:55'),
(1, 24, '2026-02-12 20:37:55'),
(1, 25, '2026-02-12 20:37:55'),
(1, 26, '2026-02-12 20:37:55'),
(1, 27, '2026-02-12 20:37:55'),
(1, 28, '2026-02-12 20:37:55'),
(1, 29, '2026-02-12 20:37:55'),
(1, 30, '2026-02-12 20:37:55'),
(1, 31, '2026-02-12 20:37:55'),
(1, 32, '2026-02-12 20:37:55'),
(1, 33, '2026-02-12 20:37:55'),
(2, 1, '2026-02-12 20:37:55'),
(2, 2, '2026-02-12 20:37:55'),
(2, 3, '2026-02-12 20:37:55'),
(2, 4, '2026-02-12 20:37:55'),
(2, 5, '2026-02-12 20:37:55'),
(2, 6, '2026-02-12 20:37:55'),
(2, 7, '2026-02-12 20:37:55'),
(2, 8, '2026-02-12 20:37:55'),
(2, 9, '2026-02-12 20:37:55'),
(2, 10, '2026-02-12 20:37:55'),
(2, 11, '2026-02-12 20:37:55'),
(2, 12, '2026-02-12 20:37:55'),
(2, 13, '2026-02-12 20:37:55'),
(2, 14, '2026-02-12 20:37:55'),
(2, 15, '2026-02-12 20:37:55'),
(2, 16, '2026-02-12 20:37:55'),
(2, 17, '2026-02-12 20:37:55'),
(2, 18, '2026-02-12 20:37:55'),
(2, 19, '2026-02-12 20:37:55'),
(2, 20, '2026-02-12 20:37:55'),
(2, 21, '2026-02-12 20:37:55'),
(2, 22, '2026-02-12 20:37:55'),
(2, 23, '2026-02-12 20:37:55'),
(2, 24, '2026-02-12 20:37:55'),
(2, 25, '2026-02-12 20:37:55'),
(2, 26, '2026-02-12 20:37:55'),
(2, 27, '2026-02-12 20:37:55'),
(2, 28, '2026-02-12 20:37:55'),
(2, 29, '2026-02-12 20:37:55'),
(2, 30, '2026-02-12 20:37:55'),
(2, 31, '2026-02-12 20:37:55'),
(3, 1, '2026-02-12 20:37:55'),
(3, 2, '2026-02-12 20:37:55'),
(3, 6, '2026-02-12 20:37:55'),
(3, 11, '2026-02-12 20:37:55'),
(3, 15, '2026-02-12 20:37:55'),
(3, 17, '2026-02-12 20:37:55'),
(3, 21, '2026-02-12 20:37:55'),
(3, 25, '2026-02-12 20:37:55'),
(4, 1, '2026-02-12 20:37:55'),
(4, 2, '2026-02-12 20:37:55'),
(4, 3, '2026-02-12 20:37:55'),
(4, 4, '2026-02-12 20:37:55'),
(4, 6, '2026-02-12 20:37:55'),
(4, 7, '2026-02-12 20:37:55'),
(4, 10, '2026-02-12 20:37:55'),
(4, 11, '2026-02-12 20:37:55'),
(4, 12, '2026-02-12 20:37:55'),
(4, 17, '2026-02-12 20:37:55'),
(4, 18, '2026-02-12 20:37:55'),
(4, 21, '2026-02-12 20:37:55'),
(4, 22, '2026-02-12 20:37:55'),
(4, 25, '2026-02-12 20:37:55'),
(5, 1, '2026-02-12 20:37:55'),
(5, 6, '2026-02-12 20:37:55'),
(5, 10, '2026-02-12 20:37:55'),
(5, 25, '2026-02-12 20:37:55');

-- --------------------------------------------------------

--
-- Struktur dari tabel `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','number','boolean','json') DEFAULT 'text',
  `deskripsi` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `deskripsi`, `updated_by`, `updated_at`) VALUES
(1, 'app_name', 'KSP Samosir', 'text', 'Nama Aplikasi', NULL, '2026-02-12 20:02:24'),
(2, 'app_version', '1.0.0', 'text', 'Versi Aplikasi', NULL, '2026-02-12 20:02:24'),
(3, 'bunga_simpanan_wajib', '3.00', 'number', 'Bunga Simpanan Wajib (%)', NULL, '2026-02-12 20:02:24'),
(4, 'bunga_simpanan_sukarela', '4.00', 'number', 'Bunga Simpanan Sukarela (%)', NULL, '2026-02-12 20:02:24'),
(5, 'denda_keterlambatan', '2.00', 'number', 'Denda Keterlambatan (%)', NULL, '2026-02-12 20:02:24');

-- --------------------------------------------------------

--
-- Struktur dari tabel `shu_anggota`
--

CREATE TABLE `shu_anggota` (
  `id` int(11) NOT NULL,
  `anggota_id` int(11) NOT NULL,
  `shu_periode_id` int(11) NOT NULL,
  `jumlah_simpanan` decimal(15,2) DEFAULT 0.00,
  `total_shu` decimal(15,2) DEFAULT 0.00,
  `persentase_shu` decimal(5,2) DEFAULT 0.00,
  `status` enum('calculated','paid','reserved') DEFAULT 'calculated',
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `shu_components`
--

CREATE TABLE `shu_components` (
  `id` int(11) NOT NULL,
  `component_code` varchar(50) NOT NULL,
  `component_name` varchar(255) NOT NULL,
  `component_type` enum('jasa_modal','jasa_usaha','pendidikan_sosial','honorarium','lainnya') NOT NULL,
  `percentage_weight` decimal(5,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `shu_components`
--

INSERT INTO `shu_components` (`id`, `component_code`, `component_name`, `component_type`, `percentage_weight`, `description`, `is_active`, `created_at`) VALUES
(1, 'JASA_MODAL_ANGGOTA', 'Jasa Modal Anggota', 'jasa_modal', '40.00', 'Bagian SHU untuk anggota berdasarkan simpanan', 1, '2026-02-12 20:53:43'),
(2, 'JASA_MODAL_PENGURUS', 'Jasa Modal Pengurus', 'jasa_modal', '5.00', 'Bagian SHU untuk modal pengurus koperasi', 1, '2026-02-12 20:53:43'),
(3, 'JASA_USHA_ANGGOTA', 'Jasa Usaha Anggota', 'jasa_usaha', '35.00', 'Bagian SHU dari transaksi dengan anggota', 1, '2026-02-12 20:53:43'),
(4, 'JASA_USHA_KOPERASI', 'Jasa Usaha Koperasi', 'jasa_usaha', '15.00', 'Bagian SHU dari usaha langsung koperasi', 1, '2026-02-12 20:53:43'),
(5, 'PENDIDIKAN', 'Pendidikan Sosial', 'pendidikan_sosial', '3.00', 'Dana pendidikan anggota dan masyarakat', 1, '2026-02-12 20:53:43'),
(6, 'HONORARIUM_PENGURUS', 'Honorarium Pengurus', 'honorarium', '2.00', 'Honorarium untuk pengurus aktif', 1, '2026-02-12 20:53:43'),
(7, 'DANA_CADANGAN', 'Dana Cadangan', 'lainnya', '5.00', 'Dana cadangan resiko dan pengembangan', 1, '2026-02-12 20:53:43');

-- --------------------------------------------------------

--
-- Struktur dari tabel `shu_member_distribution`
--

CREATE TABLE `shu_member_distribution` (
  `id` int(11) NOT NULL,
  `shu_period_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `component_code` varchar(50) NOT NULL,
  `base_amount` decimal(15,2) DEFAULT 0.00,
  `calculated_shu` decimal(15,2) DEFAULT 0.00,
  `percentage_share` decimal(5,2) DEFAULT 0.00,
  `status` enum('calculated','approved','paid','reserved') DEFAULT 'calculated',
  `paid_amount` decimal(15,2) DEFAULT 0.00,
  `paid_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `shu_periode`
--

CREATE TABLE `shu_periode` (
  `id` int(11) NOT NULL,
  `periode_start` date NOT NULL,
  `periode_end` date NOT NULL,
  `total_shu` decimal(15,2) DEFAULT 0.00,
  `persentase_modal` decimal(5,2) DEFAULT 0.00,
  `persentase_jasa` decimal(5,2) DEFAULT 0.00,
  `status` enum('draft','calculated','distributed') DEFAULT 'draft',
  `calculated_at` timestamp NULL DEFAULT NULL,
  `distributed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `shu_periods`
--

CREATE TABLE `shu_periods` (
  `id` int(11) NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `total_shu` decimal(15,2) DEFAULT 0.00,
  `calculation_method` enum('standard','custom') DEFAULT 'standard',
  `status` enum('draft','calculated','approved','distributed') DEFAULT 'draft',
  `calculated_by` int(11) DEFAULT NULL,
  `calculated_at` timestamp NULL DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `distributed_by` int(11) DEFAULT NULL,
  `distributed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `simpanan`
--

CREATE TABLE `simpanan` (
  `id` int(11) NOT NULL,
  `anggota_id` int(11) NOT NULL,
  `jenis_simpanan_id` int(11) NOT NULL,
  `no_rekening` varchar(30) NOT NULL,
  `saldo` decimal(15,2) DEFAULT 0.00,
  `status` enum('aktif','ditutup','dibekukan') DEFAULT 'aktif',
  `tanggal_buka` date DEFAULT NULL,
  `tanggal_tutup` date DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `supervision_records`
--

CREATE TABLE `supervision_records` (
  `id` int(11) NOT NULL,
  `supervision_date` date NOT NULL,
  `supervisor_id` int(11) NOT NULL,
  `supervised_person_id` int(11) NOT NULL,
  `supervision_type` enum('audit','monitoring','evaluation','investigation') NOT NULL,
  `findings` text DEFAULT NULL,
  `recommendations` text DEFAULT NULL,
  `action_required` enum('none','minor','major','critical') DEFAULT 'none',
  `follow_up_date` date DEFAULT NULL,
  `status` enum('open','in_progress','closed','escalated') DEFAULT 'open',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi_simpanan`
--

CREATE TABLE `transaksi_simpanan` (
  `id` int(11) NOT NULL,
  `simpanan_id` int(11) NOT NULL,
  `jenis_transaksi` enum('setoran','penarikan','bunga') NOT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `saldo_sebelum` decimal(15,2) NOT NULL,
  `saldo_setelah` decimal(15,2) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `tanggal_transaksi` datetime DEFAULT current_timestamp(),
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Trigger `transaksi_simpanan`
--
DELIMITER $$
CREATE TRIGGER `update_saldo_simpanan` AFTER INSERT ON `transaksi_simpanan` FOR EACH ROW BEGIN
    UPDATE simpanan 
    SET saldo = NEW.saldo_setelah,
        updated_at = NOW()
    WHERE id = NEW.simpanan_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `mfa_enabled` tinyint(1) DEFAULT 0,
  `mfa_secret` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `full_name`, `role_id`, `mfa_enabled`, `mfa_secret`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$puqQ47Z6i/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m/il.UeIdYvu', 'admin@ksp_samosir.com', 'Administrator', 1, 0, NULL, 1, '2026-02-13 03:59:32', '2026-02-12 20:02:24', '2026-02-12 20:59:32'),
(2, 'staff', '$2y$10$puqQ47Z6i/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m/il.UeIdYvu', 'staff@ksp_samosir.com', 'Staff User', 4, 0, NULL, 1, '2026-02-13 03:35:08', '2026-02-12 20:34:31', '2026-02-12 20:37:55'),
(3, 'member', '$2y$10$puqQ47Z6i/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m/il.UeIdYvu', 'member@ksp_samosir.com', 'Member User', 5, 0, NULL, 1, '2026-02-13 03:48:06', '2026-02-12 20:34:34', '2026-02-12 20:48:06');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `user_roles`
--

INSERT INTO `user_roles` (`user_id`, `role_id`, `assigned_at`, `assigned_by`) VALUES
(1, 2, '2026-02-12 20:37:55', 1),
(2, 4, '2026-02-12 20:37:55', 2),
(3, 5, '2026-02-12 20:37:55', 3);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `v_anggota_alamat`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `v_anggota_alamat` (
`id` int(11)
,`nama_lengkap` varchar(100)
,`alamat` text
,`province_name` varchar(255)
,`regency_name` varchar(255)
,`district_name` varchar(255)
,`village_name` varchar(255)
,`kodepos` varchar(10)
,`alamat_lengkap` mediumtext
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `v_koperasi_activity_summary`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `v_koperasi_activity_summary` (
`activity_type` enum('simpanan','pinjaman','jual_beli','investasi','jasa_lain')
,`total_transactions` bigint(21)
,`total_debit` decimal(37,2)
,`total_credit` decimal(37,2)
,`net_amount` decimal(37,2)
,`unique_members` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `v_shu_calculation_summary`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `v_shu_calculation_summary` (
`period_start` date
,`period_end` date
,`total_shu` decimal(15,2)
,`total_distributions` bigint(21)
,`distributed_amount` decimal(37,2)
,`status` enum('draft','calculated','approved','distributed')
);

-- --------------------------------------------------------

--
-- Struktur untuk view `v_anggota_alamat`
--
DROP TABLE IF EXISTS `v_anggota_alamat`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_anggota_alamat`  AS SELECT `a`.`id` AS `id`, `a`.`nama_lengkap` AS `nama_lengkap`, `a`.`alamat` AS `alamat`, `p`.`name` AS `province_name`, `r`.`name` AS `regency_name`, `d`.`name` AS `district_name`, `v`.`name` AS `village_name`, `v`.`kodepos` AS `kodepos`, concat(`a`.`alamat`,', ',`v`.`name`,', ',`d`.`name`,', ',`r`.`name`,', ',`p`.`name`) AS `alamat_lengkap` FROM ((((`anggota` `a` left join `ref_provinces` `p` on(`a`.`province_id` = `p`.`id`)) left join `ref_regencies` `r` on(`a`.`regency_id` = `r`.`id`)) left join `ref_districts` `d` on(`a`.`district_id` = `d`.`id`)) left join `ref_villages` `v` on(`a`.`village_id` = `v`.`id`)) ;

-- --------------------------------------------------------

--
-- Struktur untuk view `v_koperasi_activity_summary`
--
DROP TABLE IF EXISTS `v_koperasi_activity_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_koperasi_activity_summary`  AS SELECT `ka`.`activity_type` AS `activity_type`, count(0) AS `total_transactions`, sum(case when `kt`.`transaction_type` = 'debit' then `kt`.`amount` else 0 end) AS `total_debit`, sum(case when `kt`.`transaction_type` = 'credit' then `kt`.`amount` else 0 end) AS `total_credit`, sum(`kt`.`amount`) AS `net_amount`, count(distinct `kt`.`member_id`) AS `unique_members` FROM (`koperasi_activities` `ka` left join `koperasi_transactions` `kt` on(`ka`.`activity_code` = `kt`.`activity_code`)) WHERE `ka`.`is_active` = 1 GROUP BY `ka`.`activity_type` ;

-- --------------------------------------------------------

--
-- Struktur untuk view `v_shu_calculation_summary`
--
DROP TABLE IF EXISTS `v_shu_calculation_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_shu_calculation_summary`  AS SELECT `sp`.`period_start` AS `period_start`, `sp`.`period_end` AS `period_end`, `sp`.`total_shu` AS `total_shu`, count(`smd`.`id`) AS `total_distributions`, sum(`smd`.`calculated_shu`) AS `distributed_amount`, `sp`.`status` AS `status` FROM (`shu_periods` `sp` left join `shu_member_distribution` `smd` on(`sp`.`id` = `smd`.`shu_period_id`)) GROUP BY `sp`.`id`, `sp`.`period_start`, `sp`.`period_end`, `sp`.`total_shu`, `sp`.`status` ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `address_stats`
--
ALTER TABLE `address_stats`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `anggota`
--
ALTER TABLE `anggota`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_anggota` (`no_anggota`),
  ADD UNIQUE KEY `nik` (`nik`),
  ADD KEY `idx_anggota_no_anggota` (`no_anggota`),
  ADD KEY `idx_anggota_created_by` (`created_by`),
  ADD KEY `idx_anggota_province` (`province_id`),
  ADD KEY `idx_anggota_regency` (`regency_id`),
  ADD KEY `idx_anggota_district` (`district_id`),
  ADD KEY `idx_anggota_village` (`village_id`);

--
-- Indeks untuk tabel `angsuran`
--
ALTER TABLE `angsuran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_angsuran_pinjaman` (`pinjaman_id`),
  ADD KEY `idx_angsuran_status` (`status`),
  ADD KEY `idx_angsuran_jatuh_tempo` (`tanggal_jatuh_tempo`);

--
-- Indeks untuk tabel `buku_besar`
--
ALTER TABLE `buku_besar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `coa_id` (`coa_id`),
  ADD KEY `jurnal_detail_id` (`jurnal_detail_id`);

--
-- Indeks untuk tabel `coa`
--
ALTER TABLE `coa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_coa` (`kode_coa`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indeks untuk tabel `detail_jurnal`
--
ALTER TABLE `detail_jurnal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jurnal_id` (`jurnal_id`),
  ADD KEY `idx_detail_jurnal_coa` (`coa_id`);

--
-- Indeks untuk tabel `detail_penjualan`
--
ALTER TABLE `detail_penjualan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_detail_penjualan_penjualan` (`penjualan_id`),
  ADD KEY `idx_detail_penjualan_produk` (`produk_id`);

--
-- Indeks untuk tabel `jenis_pinjaman`
--
ALTER TABLE `jenis_pinjaman`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode` (`kode`);

--
-- Indeks untuk tabel `jenis_simpanan`
--
ALTER TABLE `jenis_simpanan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode` (`kode`);

--
-- Indeks untuk tabel `jurnal`
--
ALTER TABLE `jurnal`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_jurnal` (`no_jurnal`),
  ADD KEY `idx_jurnal_no_jurnal` (`no_jurnal`),
  ADD KEY `idx_jurnal_user` (`user_id`);

--
-- Indeks untuk tabel `jurnal_detail`
--
ALTER TABLE `jurnal_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jurnal_id` (`jurnal_id`),
  ADD KEY `coa_id` (`coa_id`);

--
-- Indeks untuk tabel `kategori_produk`
--
ALTER TABLE `kategori_produk`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode` (`kode`);

--
-- Indeks untuk tabel `koperasi_activities`
--
ALTER TABLE `koperasi_activities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `activity_code` (`activity_code`);

--
-- Indeks untuk tabel `koperasi_meetings`
--
ALTER TABLE `koperasi_meetings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indeks untuk tabel `koperasi_sanctions`
--
ALTER TABLE `koperasi_sanctions`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `koperasi_transactions`
--
ALTER TABLE `koperasi_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_code` (`activity_code`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indeks untuk tabel `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_logs_created_at` (`created_at`);

--
-- Indeks untuk tabel `meeting_attendance`
--
ALTER TABLE `meeting_attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `meeting_id` (`meeting_id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indeks untuk tabel `meeting_decisions`
--
ALTER TABLE `meeting_decisions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `meeting_id` (`meeting_id`),
  ADD KEY `responsible_person` (`responsible_person`);

--
-- Indeks untuk tabel `modal_pokok`
--
ALTER TABLE `modal_pokok`
  ADD PRIMARY KEY (`id`),
  ADD KEY `anggota_id` (`anggota_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indeks untuk tabel `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_pelanggan` (`kode_pelanggan`),
  ADD KEY `anggota_id` (`anggota_id`),
  ADD KEY `idx_pelanggan_kode` (`kode_pelanggan`);

--
-- Indeks untuk tabel `pengaturan_koperasi`
--
ALTER TABLE `pengaturan_koperasi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indeks untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_faktur` (`no_faktur`),
  ADD KEY `idx_penjualan_no_faktur` (`no_faktur`),
  ADD KEY `idx_penjualan_pelanggan` (`pelanggan_id`),
  ADD KEY `idx_penjualan_user` (`user_id`),
  ADD KEY `idx_penjualan_tanggal` (`tanggal_penjualan`);

--
-- Indeks untuk tabel `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indeks untuk tabel `pinjaman`
--
ALTER TABLE `pinjaman`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_pinjaman` (`no_pinjaman`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_pinjaman_no_pinjaman` (`no_pinjaman`),
  ADD KEY `idx_pinjaman_anggota` (`anggota_id`),
  ADD KEY `idx_pinjaman_jenis` (`jenis_pinjaman_id`),
  ADD KEY `idx_pinjaman_status` (`status`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_produk` (`kode_produk`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_produk_kode_produk` (`kode_produk`),
  ADD KEY `idx_produk_kategori` (`kategori_id`);

--
-- Indeks untuk tabel `ref_districts`
--
ALTER TABLE `ref_districts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_districts_regency` (`regency_id`);

--
-- Indeks untuk tabel `ref_provinces`
--
ALTER TABLE `ref_provinces`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `ref_regencies`
--
ALTER TABLE `ref_regencies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_regencies_province` (`province_id`);

--
-- Indeks untuk tabel `ref_villages`
--
ALTER TABLE `ref_villages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_villages_district` (`district_id`);

--
-- Indeks untuk tabel `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indeks untuk tabel `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indeks untuk tabel `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `idx_settings_key` (`setting_key`);

--
-- Indeks untuk tabel `shu_anggota`
--
ALTER TABLE `shu_anggota`
  ADD PRIMARY KEY (`id`),
  ADD KEY `anggota_id` (`anggota_id`),
  ADD KEY `shu_periode_id` (`shu_periode_id`);

--
-- Indeks untuk tabel `shu_components`
--
ALTER TABLE `shu_components`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `component_code` (`component_code`);

--
-- Indeks untuk tabel `shu_member_distribution`
--
ALTER TABLE `shu_member_distribution`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shu_period_id` (`shu_period_id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `component_code` (`component_code`);

--
-- Indeks untuk tabel `shu_periode`
--
ALTER TABLE `shu_periode`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `shu_periods`
--
ALTER TABLE `shu_periods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `calculated_by` (`calculated_by`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `distributed_by` (`distributed_by`);

--
-- Indeks untuk tabel `simpanan`
--
ALTER TABLE `simpanan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_rekening` (`no_rekening`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_simpanan_no_rekening` (`no_rekening`),
  ADD KEY `idx_simpanan_anggota` (`anggota_id`),
  ADD KEY `idx_simpanan_jenis` (`jenis_simpanan_id`);

--
-- Indeks untuk tabel `supervision_records`
--
ALTER TABLE `supervision_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supervisor_id` (`supervisor_id`),
  ADD KEY `supervised_person_id` (`supervised_person_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indeks untuk tabel `transaksi_simpanan`
--
ALTER TABLE `transaksi_simpanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_trans_simpanan_simpanan` (`simpanan_id`),
  ADD KEY `idx_trans_simpanan_user` (`user_id`),
  ADD KEY `idx_trans_simpanan_tanggal` (`tanggal_transaksi`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `assigned_by` (`assigned_by`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `address_stats`
--
ALTER TABLE `address_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `anggota`
--
ALTER TABLE `anggota`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `angsuran`
--
ALTER TABLE `angsuran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `buku_besar`
--
ALTER TABLE `buku_besar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `coa`
--
ALTER TABLE `coa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT untuk tabel `detail_jurnal`
--
ALTER TABLE `detail_jurnal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `detail_penjualan`
--
ALTER TABLE `detail_penjualan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `jenis_pinjaman`
--
ALTER TABLE `jenis_pinjaman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `jenis_simpanan`
--
ALTER TABLE `jenis_simpanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `jurnal`
--
ALTER TABLE `jurnal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `jurnal_detail`
--
ALTER TABLE `jurnal_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `kategori_produk`
--
ALTER TABLE `kategori_produk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `koperasi_activities`
--
ALTER TABLE `koperasi_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT untuk tabel `koperasi_meetings`
--
ALTER TABLE `koperasi_meetings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `koperasi_sanctions`
--
ALTER TABLE `koperasi_sanctions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `koperasi_transactions`
--
ALTER TABLE `koperasi_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT untuk tabel `meeting_attendance`
--
ALTER TABLE `meeting_attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `meeting_decisions`
--
ALTER TABLE `meeting_decisions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `modal_pokok`
--
ALTER TABLE `modal_pokok`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pengaturan_koperasi`
--
ALTER TABLE `pengaturan_koperasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT untuk tabel `pinjaman`
--
ALTER TABLE `pinjaman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `shu_anggota`
--
ALTER TABLE `shu_anggota`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `shu_components`
--
ALTER TABLE `shu_components`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `shu_member_distribution`
--
ALTER TABLE `shu_member_distribution`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `shu_periode`
--
ALTER TABLE `shu_periode`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `shu_periods`
--
ALTER TABLE `shu_periods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `simpanan`
--
ALTER TABLE `simpanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `supervision_records`
--
ALTER TABLE `supervision_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `transaksi_simpanan`
--
ALTER TABLE `transaksi_simpanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `anggota`
--
ALTER TABLE `anggota`
  ADD CONSTRAINT `anggota_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_anggota_district` FOREIGN KEY (`district_id`) REFERENCES `ref_districts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_anggota_province` FOREIGN KEY (`province_id`) REFERENCES `ref_provinces` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_anggota_regency` FOREIGN KEY (`regency_id`) REFERENCES `ref_regencies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_anggota_village` FOREIGN KEY (`village_id`) REFERENCES `ref_villages` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `angsuran`
--
ALTER TABLE `angsuran`
  ADD CONSTRAINT `angsuran_ibfk_1` FOREIGN KEY (`pinjaman_id`) REFERENCES `pinjaman` (`id`),
  ADD CONSTRAINT `angsuran_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `buku_besar`
--
ALTER TABLE `buku_besar`
  ADD CONSTRAINT `buku_besar_ibfk_1` FOREIGN KEY (`coa_id`) REFERENCES `coa` (`id`),
  ADD CONSTRAINT `buku_besar_ibfk_2` FOREIGN KEY (`jurnal_detail_id`) REFERENCES `jurnal_detail` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `coa`
--
ALTER TABLE `coa`
  ADD CONSTRAINT `coa_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `coa` (`id`);

--
-- Ketidakleluasaan untuk tabel `detail_jurnal`
--
ALTER TABLE `detail_jurnal`
  ADD CONSTRAINT `detail_jurnal_ibfk_1` FOREIGN KEY (`jurnal_id`) REFERENCES `jurnal` (`id`),
  ADD CONSTRAINT `detail_jurnal_ibfk_2` FOREIGN KEY (`coa_id`) REFERENCES `coa` (`id`);

--
-- Ketidakleluasaan untuk tabel `detail_penjualan`
--
ALTER TABLE `detail_penjualan`
  ADD CONSTRAINT `detail_penjualan_ibfk_1` FOREIGN KEY (`penjualan_id`) REFERENCES `penjualan` (`id`),
  ADD CONSTRAINT `detail_penjualan_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`);

--
-- Ketidakleluasaan untuk tabel `jurnal`
--
ALTER TABLE `jurnal`
  ADD CONSTRAINT `jurnal_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `jurnal_detail`
--
ALTER TABLE `jurnal_detail`
  ADD CONSTRAINT `jurnal_detail_ibfk_1` FOREIGN KEY (`jurnal_id`) REFERENCES `jurnal` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `jurnal_detail_ibfk_2` FOREIGN KEY (`coa_id`) REFERENCES `coa` (`id`);

--
-- Ketidakleluasaan untuk tabel `koperasi_meetings`
--
ALTER TABLE `koperasi_meetings`
  ADD CONSTRAINT `koperasi_meetings_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `koperasi_transactions`
--
ALTER TABLE `koperasi_transactions`
  ADD CONSTRAINT `koperasi_transactions_ibfk_1` FOREIGN KEY (`activity_code`) REFERENCES `koperasi_activities` (`activity_code`),
  ADD CONSTRAINT `koperasi_transactions_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `anggota` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `koperasi_transactions_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `koperasi_transactions_ibfk_4` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `meeting_attendance`
--
ALTER TABLE `meeting_attendance`
  ADD CONSTRAINT `meeting_attendance_ibfk_1` FOREIGN KEY (`meeting_id`) REFERENCES `koperasi_meetings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `meeting_attendance_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `anggota` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `meeting_decisions`
--
ALTER TABLE `meeting_decisions`
  ADD CONSTRAINT `meeting_decisions_ibfk_1` FOREIGN KEY (`meeting_id`) REFERENCES `koperasi_meetings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `meeting_decisions_ibfk_2` FOREIGN KEY (`responsible_person`) REFERENCES `anggota` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `modal_pokok`
--
ALTER TABLE `modal_pokok`
  ADD CONSTRAINT `modal_pokok_ibfk_1` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `modal_pokok_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD CONSTRAINT `pelanggan_ibfk_1` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`);

--
-- Ketidakleluasaan untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  ADD CONSTRAINT `penjualan_ibfk_1` FOREIGN KEY (`pelanggan_id`) REFERENCES `pelanggan` (`id`),
  ADD CONSTRAINT `penjualan_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `pinjaman`
--
ALTER TABLE `pinjaman`
  ADD CONSTRAINT `pinjaman_ibfk_1` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`),
  ADD CONSTRAINT `pinjaman_ibfk_2` FOREIGN KEY (`jenis_pinjaman_id`) REFERENCES `jenis_pinjaman` (`id`),
  ADD CONSTRAINT `pinjaman_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `pinjaman_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori_produk` (`id`),
  ADD CONSTRAINT `produk_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `ref_districts`
--
ALTER TABLE `ref_districts`
  ADD CONSTRAINT `ref_districts_ibfk_1` FOREIGN KEY (`regency_id`) REFERENCES `ref_regencies` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `ref_regencies`
--
ALTER TABLE `ref_regencies`
  ADD CONSTRAINT `ref_regencies_ibfk_1` FOREIGN KEY (`province_id`) REFERENCES `ref_provinces` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `ref_villages`
--
ALTER TABLE `ref_villages`
  ADD CONSTRAINT `ref_villages_ibfk_1` FOREIGN KEY (`district_id`) REFERENCES `ref_districts` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `settings`
--
ALTER TABLE `settings`
  ADD CONSTRAINT `settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `shu_anggota`
--
ALTER TABLE `shu_anggota`
  ADD CONSTRAINT `shu_anggota_ibfk_1` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shu_anggota_ibfk_2` FOREIGN KEY (`shu_periode_id`) REFERENCES `shu_periode` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `shu_member_distribution`
--
ALTER TABLE `shu_member_distribution`
  ADD CONSTRAINT `shu_member_distribution_ibfk_1` FOREIGN KEY (`shu_period_id`) REFERENCES `shu_periods` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shu_member_distribution_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `anggota` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shu_member_distribution_ibfk_3` FOREIGN KEY (`component_code`) REFERENCES `shu_components` (`component_code`);

--
-- Ketidakleluasaan untuk tabel `shu_periods`
--
ALTER TABLE `shu_periods`
  ADD CONSTRAINT `shu_periods_ibfk_1` FOREIGN KEY (`calculated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `shu_periods_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `shu_periods_ibfk_3` FOREIGN KEY (`distributed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `simpanan`
--
ALTER TABLE `simpanan`
  ADD CONSTRAINT `simpanan_ibfk_1` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`),
  ADD CONSTRAINT `simpanan_ibfk_2` FOREIGN KEY (`jenis_simpanan_id`) REFERENCES `jenis_simpanan` (`id`),
  ADD CONSTRAINT `simpanan_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `supervision_records`
--
ALTER TABLE `supervision_records`
  ADD CONSTRAINT `supervision_records_ibfk_1` FOREIGN KEY (`supervisor_id`) REFERENCES `anggota` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `supervision_records_ibfk_2` FOREIGN KEY (`supervised_person_id`) REFERENCES `anggota` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `supervision_records_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `transaksi_simpanan`
--
ALTER TABLE `transaksi_simpanan`
  ADD CONSTRAINT `transaksi_simpanan_ibfk_1` FOREIGN KEY (`simpanan_id`) REFERENCES `simpanan` (`id`),
  ADD CONSTRAINT `transaksi_simpanan_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
