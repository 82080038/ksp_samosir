-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Waktu pembuatan: 14 Feb 2026 pada 15.52
-- Versi server: 10.11.14-MariaDB-0ubuntu0.24.04.1
-- Versi PHP: 8.3.6

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
CREATE DEFINER=`root`@`localhost` PROCEDURE `calculate_shu_period` (IN `period_start` DATE, IN `period_end` DATE, IN `calculation_method` VARCHAR(20))   BEGIN
    DECLARE total_shu_amount DECIMAL(15,2) DEFAULT 0$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_address_options` (IN `parent_type` VARCHAR(20), IN `parent_id` INT)   BEGIN
    CASE parent_type
        WHEN 'province' THEN
            SELECT id, name FROM ref_provinces ORDER BY name$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_full_address` (IN `province_id` INT, IN `regency_id` INT, IN `district_id` INT, IN `village_id` INT)   BEGIN
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
    LIMIT 1$$

--
-- Fungsi
--
CREATE DEFINER=`root`@`localhost` FUNCTION `validate_address_id` (`table_name` VARCHAR(20), `id_value` INT) RETURNS TINYINT(1) DETERMINISTIC READS SQL DATA BEGIN
    DECLARE result BOOLEAN DEFAULT FALSE$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ab_tests`
--

CREATE TABLE `ab_tests` (
  `id` int(11) NOT NULL,
  `test_name` varchar(100) NOT NULL,
  `test_group` varchar(50) NOT NULL,
  `variant_a` varchar(100) NOT NULL,
  `variant_b` varchar(100) NOT NULL,
  `winner` varchar(10) DEFAULT NULL,
  `confidence_level` decimal(5,2) DEFAULT 0.00,
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `status` enum('running','completed','cancelled') DEFAULT 'running',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ab_test_participants`
--

CREATE TABLE `ab_test_participants` (
  `id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `assigned_variant` char(1) NOT NULL,
  `participated_at` datetime DEFAULT current_timestamp(),
  `converted` tinyint(1) DEFAULT 0,
  `conversion_value` decimal(10,2) DEFAULT 0.00,
  `session_duration` int(11) DEFAULT NULL,
  `page_views` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ab_test_results`
--

CREATE TABLE `ab_test_results` (
  `id` int(11) NOT NULL,
  `test_id` int(11) NOT NULL,
  `variant` varchar(10) NOT NULL,
  `participants` int(11) DEFAULT 0,
  `conversions` int(11) DEFAULT 0,
  `conversion_rate` decimal(5,2) DEFAULT 0.00,
  `measured_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `access_logs`
--

CREATE TABLE `access_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `request_method` varchar(10) NOT NULL,
  `request_uri` varchar(500) NOT NULL,
  `response_code` int(11) NOT NULL,
  `response_time` decimal(5,2) DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `addresses`
--

CREATE TABLE `addresses` (
  `id` int(11) NOT NULL,
  `address_type` enum('home','office','billing','shipping','other') DEFAULT 'home',
  `street_address` varchar(255) NOT NULL,
  `address_line_2` varchar(255) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'Indonesia',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Struktur dari tabel `ai_conversations`
--

CREATE TABLE `ai_conversations` (
  `id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `conversation_type` enum('support','guidance','transaction','general') DEFAULT 'general',
  `status` enum('active','completed','transferred') DEFAULT 'active',
  `started_at` datetime DEFAULT current_timestamp(),
  `last_message_at` datetime DEFAULT current_timestamp(),
  `resolved` tinyint(1) DEFAULT 0,
  `satisfaction_rating` decimal(2,1) DEFAULT NULL,
  `transferred_to` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ai_decisions`
--

CREATE TABLE `ai_decisions` (
  `id` int(11) NOT NULL,
  `decision_type` varchar(100) NOT NULL,
  `confidence_score` decimal(5,2) DEFAULT 0.00,
  `status` enum('automated','manual_review','rejected') DEFAULT 'automated',
  `input_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`input_data`)),
  `output_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`output_data`)),
  `processing_time` decimal(5,2) DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ai_ethics_monitoring`
--

CREATE TABLE `ai_ethics_monitoring` (
  `id` int(11) NOT NULL,
  `model_name` varchar(100) NOT NULL,
  `bias_check_type` varchar(50) NOT NULL,
  `bias_score` decimal(5,4) DEFAULT 0.0000,
  `affected_groups` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`affected_groups`)),
  `mitigation_actions` text DEFAULT NULL,
  `compliance_status` enum('compliant','review_required','non_compliant') DEFAULT 'compliant',
  `checked_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ai_fraud_alerts`
--

CREATE TABLE `ai_fraud_alerts` (
  `id` int(11) NOT NULL,
  `alert_type` varchar(50) NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `risk_level` enum('low','medium','high','critical') DEFAULT 'medium',
  `description` text NOT NULL,
  `alert_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`alert_data`)),
  `status` enum('active','investigating','resolved','dismissed') DEFAULT 'active',
  `resolved_by` int(11) DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ai_fraud_models`
--

CREATE TABLE `ai_fraud_models` (
  `id` int(11) NOT NULL,
  `model_name` varchar(100) NOT NULL,
  `model_type` varchar(50) NOT NULL,
  `training_data_size` int(11) DEFAULT 0,
  `accuracy_score` decimal(5,4) DEFAULT 0.0000,
  `false_positive_rate` decimal(5,4) DEFAULT 0.0000,
  `false_negative_rate` decimal(5,4) DEFAULT 0.0000,
  `last_trained` datetime DEFAULT NULL,
  `status` enum('active','training','inactive') DEFAULT 'inactive',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ai_messages`
--

CREATE TABLE `ai_messages` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender` enum('user','ai','agent') DEFAULT 'user',
  `message_type` enum('text','option','form','file') DEFAULT 'text',
  `content` text NOT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `confidence_score` decimal(5,4) DEFAULT NULL,
  `intent_detected` varchar(100) DEFAULT NULL,
  `entities_detected` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`entities_detected`)),
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ai_model_data`
--

CREATE TABLE `ai_model_data` (
  `id` int(11) NOT NULL,
  `model_type` varchar(50) NOT NULL,
  `training_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`training_data`)),
  `model_parameters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`model_parameters`)),
  `accuracy_score` decimal(5,2) DEFAULT NULL,
  `last_trained` timestamp NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ai_model_performance`
--

CREATE TABLE `ai_model_performance` (
  `id` int(11) NOT NULL,
  `model_name` varchar(100) NOT NULL,
  `metric_type` varchar(50) NOT NULL,
  `metric_value` decimal(10,4) NOT NULL,
  `test_period_start` date DEFAULT NULL,
  `test_period_end` date DEFAULT NULL,
  `improvement_percentage` decimal(5,2) DEFAULT NULL,
  `recorded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ai_recommendations`
--

CREATE TABLE `ai_recommendations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `recommendation_score` decimal(5,2) DEFAULT NULL,
  `recommendation_reason` varchar(255) DEFAULT NULL,
  `was_purchased` tinyint(1) DEFAULT 0,
  `purchased_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ai_training_data`
--

CREATE TABLE `ai_training_data` (
  `id` int(11) NOT NULL,
  `intent` varchar(100) NOT NULL,
  `utterance` text NOT NULL,
  `response` text NOT NULL,
  `context` varchar(100) DEFAULT NULL,
  `confidence_threshold` decimal(5,4) DEFAULT 0.8000,
  `usage_count` int(11) DEFAULT 0,
  `success_rate` decimal(5,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `alerts`
--

CREATE TABLE `alerts` (
  `id` int(11) NOT NULL,
  `alert_type` varchar(50) NOT NULL,
  `severity` enum('low','medium','high','critical') DEFAULT 'medium',
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `status` enum('active','acknowledged','resolved') DEFAULT 'active',
  `acknowledged_by` int(11) DEFAULT NULL,
  `acknowledged_at` datetime DEFAULT NULL,
  `resolved_by` int(11) DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `auto_resolve` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `village_id` int(11) DEFAULT NULL,
  `address_id` int(11) DEFAULT NULL,
  `primary_contact_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `anggota`
--

INSERT INTO `anggota` (`id`, `no_anggota`, `nama_lengkap`, `nik`, `tempat_lahir`, `tanggal_lahir`, `jenis_kelamin`, `alamat`, `no_hp`, `email`, `pekerjaan`, `pendapatan_bulanan`, `tanggal_gabung`, `status`, `created_by`, `created_at`, `updated_at`, `province_id`, `regency_id`, `district_id`, `village_id`, `address_id`, `primary_contact_id`) VALUES
(1, 'TEST001', 'Test User', '1234567890123456', 'Test', '1990-01-01', 'L', 'Test Address', '08123456789', 'test@example.com', 'Test', 5000000.00, '2024-01-01', 'aktif', 1, '2026-02-12 20:34:05', '2026-02-12 20:34:05', NULL, NULL, NULL, NULL, NULL, NULL),
(4, '002', 'Test Member', '9876543210987654', NULL, NULL, NULL, 'Alamat Test', '08987654321', NULL, NULL, NULL, '2026-02-13', 'aktif', NULL, '2026-02-13 01:48:21', '2026-02-13 01:48:21', NULL, NULL, NULL, NULL, NULL, NULL);

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
-- Struktur dari tabel `application_logs`
--

CREATE TABLE `application_logs` (
  `id` int(11) NOT NULL,
  `log_level` enum('debug','info','warning','error','critical') DEFAULT 'info',
  `category` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `context` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`context`)),
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `logged_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `app_sessions`
--

CREATE TABLE `app_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `device_id` varchar(255) DEFAULT NULL,
  `platform` enum('ios','android','web') DEFAULT 'web',
  `app_version` varchar(20) DEFAULT NULL,
  `session_start` datetime DEFAULT current_timestamp(),
  `session_end` datetime DEFAULT NULL,
  `duration` int(11) DEFAULT 0,
  `features_used` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features_used`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `asset_depreciation`
--

CREATE TABLE `asset_depreciation` (
  `id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `depreciation_date` date NOT NULL,
  `depreciation_amount` decimal(15,2) NOT NULL,
  `accumulated_depreciation` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `asset_disposals`
--

CREATE TABLE `asset_disposals` (
  `id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `disposal_date` date NOT NULL,
  `disposal_method` enum('sale','scrap','donation','loss') NOT NULL,
  `disposal_value` decimal(15,2) DEFAULT 0.00,
  `reason` text DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `asset_holdings`
--

CREATE TABLE `asset_holdings` (
  `id` int(11) NOT NULL,
  `wallet_id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `balance` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `locked_balance` decimal(20,8) DEFAULT 0.00000000,
  `last_transaction_hash` varchar(100) DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `asset_maintenance`
--

CREATE TABLE `asset_maintenance` (
  `id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `maintenance_date` date NOT NULL,
  `maintenance_type` enum('preventive','corrective','predictive','major_repair','inspection') NOT NULL,
  `description` text NOT NULL,
  `cost` decimal(15,2) DEFAULT 0.00,
  `performed_by` varchar(255) DEFAULT NULL,
  `next_maintenance_date` date DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `automated_processes`
--

CREATE TABLE `automated_processes` (
  `id` int(11) NOT NULL,
  `process_name` varchar(100) NOT NULL,
  `process_type` varchar(50) NOT NULL,
  `status` enum('pending','running','completed','failed') DEFAULT 'pending',
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `duration` int(11) DEFAULT 0,
  `records_processed` int(11) DEFAULT 0,
  `success_rate` decimal(5,2) DEFAULT 0.00,
  `error_message` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `b2b_partners`
--

CREATE TABLE `b2b_partners` (
  `id` int(11) NOT NULL,
  `partner_name` varchar(200) NOT NULL,
  `partner_type` varchar(50) NOT NULL,
  `api_endpoint` varchar(500) DEFAULT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `shared_services` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`shared_services`)),
  `integration_status` enum('pending','testing','active','inactive') DEFAULT 'pending',
  `last_sync` datetime DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `contact_email` varchar(100) DEFAULT NULL,
  `contract_start` date DEFAULT NULL,
  `contract_end` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `backup_files`
--

CREATE TABLE `backup_files` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `type` enum('full','incremental','partial') DEFAULT 'full',
  `description` text DEFAULT NULL,
  `file_size` bigint(20) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `backup_logs`
--

CREATE TABLE `backup_logs` (
  `id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL,
  `details` text DEFAULT NULL,
  `performed_by` int(11) DEFAULT NULL,
  `performed_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `backup_schedules`
--

CREATE TABLE `backup_schedules` (
  `id` int(11) NOT NULL,
  `frequency` enum('hourly','daily','weekly','monthly') DEFAULT 'daily',
  `scheduled_time` time DEFAULT '02:00:00',
  `enabled` tinyint(1) DEFAULT 1,
  `last_run` datetime DEFAULT NULL,
  `next_run` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `blockchain_blocks`
--

CREATE TABLE `blockchain_blocks` (
  `id` int(11) NOT NULL,
  `block_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`block_data`)),
  `previous_hash` varchar(64) NOT NULL DEFAULT '0',
  `current_hash` varchar(64) NOT NULL,
  `block_type` enum('sale','payment','loan','savings','governance','general') NOT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `blockchain_oracles`
--

CREATE TABLE `blockchain_oracles` (
  `id` int(11) NOT NULL,
  `oracle_name` varchar(100) NOT NULL,
  `oracle_type` varchar(50) NOT NULL,
  `data_source` varchar(200) NOT NULL,
  `update_frequency` int(11) DEFAULT 3600,
  `last_update` datetime DEFAULT NULL,
  `confidence_score` decimal(5,4) DEFAULT 1.0000,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `blockchain_transactions`
--

CREATE TABLE `blockchain_transactions` (
  `id` int(11) NOT NULL,
  `transaction_hash` varchar(100) NOT NULL,
  `block_number` bigint(20) DEFAULT NULL,
  `block_hash` varchar(100) DEFAULT NULL,
  `from_address` varchar(100) NOT NULL,
  `to_address` varchar(100) NOT NULL,
  `value` decimal(20,8) NOT NULL,
  `gas_used` bigint(20) DEFAULT NULL,
  `gas_price` decimal(20,8) DEFAULT NULL,
  `transaction_fee` decimal(20,8) DEFAULT NULL,
  `transaction_type` varchar(50) NOT NULL,
  `status` enum('pending','confirmed','failed') DEFAULT 'pending',
  `confirmations` int(11) DEFAULT 0,
  `asset_symbol` varchar(20) DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `confirmed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `block_verifications`
--

CREATE TABLE `block_verifications` (
  `id` int(11) NOT NULL,
  `block_id` int(11) NOT NULL,
  `verification_hash` varchar(64) NOT NULL,
  `verification_status` enum('valid','invalid','tampered') DEFAULT 'valid',
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `bridge_transactions`
--

CREATE TABLE `bridge_transactions` (
  `id` int(11) NOT NULL,
  `bridge_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `source_tx_hash` varchar(100) NOT NULL,
  `target_tx_hash` varchar(100) DEFAULT NULL,
  `amount` decimal(20,8) NOT NULL,
  `asset_symbol` varchar(20) NOT NULL,
  `fee_amount` decimal(20,8) DEFAULT 0.00000000,
  `status` enum('pending','processing','completed','failed') DEFAULT 'pending',
  `initiated_at` datetime DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL
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
-- Struktur dari tabel `business_metrics`
--

CREATE TABLE `business_metrics` (
  `id` int(11) NOT NULL,
  `metric_category` varchar(50) NOT NULL,
  `metric_name` varchar(100) NOT NULL,
  `metric_value` decimal(10,2) DEFAULT 0.00,
  `target_value` decimal(10,2) DEFAULT 0.00,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `category_type` varchar(50) NOT NULL,
  `category_code` varchar(50) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `parent_category_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `certificates`
--

CREATE TABLE `certificates` (
  `id` int(11) NOT NULL,
  `enrollment_id` int(11) NOT NULL,
  `certificate_number` varchar(50) NOT NULL,
  `issued_date` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `issued_by` int(11) DEFAULT NULL,
  `verification_code` varchar(100) DEFAULT NULL,
  `status` enum('active','revoked') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `chain_bridges`
--

CREATE TABLE `chain_bridges` (
  `id` int(11) NOT NULL,
  `bridge_name` varchar(100) NOT NULL,
  `source_chain` varchar(50) NOT NULL,
  `target_chain` varchar(50) NOT NULL,
  `bridge_contract` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive','maintenance') DEFAULT 'active',
  `daily_volume_limit` decimal(20,8) DEFAULT NULL,
  `current_daily_volume` decimal(20,8) DEFAULT 0.00000000,
  `fee_structure` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`fee_structure`)),
  `created_at` datetime DEFAULT current_timestamp()
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
(1, '1', 'AKTIVA', 'debit', 1, NULL, 0.00, 1, '2026-02-12 20:02:24'),
(2, '11', 'AKTIVA LANCAR', 'debit', 2, NULL, 0.00, 1, '2026-02-12 20:02:24'),
(3, '111', 'Kas', 'debit', 3, NULL, 0.00, 1, '2026-02-12 20:02:24'),
(4, '112', 'Bank', 'debit', 3, NULL, 0.00, 1, '2026-02-12 20:02:24'),
(5, '12', 'AKTIVA TETAP', 'debit', 2, NULL, 0.00, 1, '2026-02-12 20:02:24'),
(6, '121', 'Tanah & Bangunan', 'debit', 3, NULL, 0.00, 1, '2026-02-12 20:02:24'),
(7, '2', 'PASSIVA', 'kredit', 1, NULL, 0.00, 1, '2026-02-12 20:02:24'),
(8, '21', 'KEWAJIBAN LANCAR', 'kredit', 2, NULL, 0.00, 1, '2026-02-12 20:02:24'),
(9, '211', 'Simpanan Anggota', 'kredit', 3, NULL, 0.00, 1, '2026-02-12 20:02:24'),
(10, '3', 'EKUITAS', 'kredit', 1, NULL, 0.00, 1, '2026-02-12 20:02:24'),
(11, '31', 'MODAL', 'kredit', 2, NULL, 0.00, 1, '2026-02-12 20:02:24'),
(12, '4', 'PENDAPATAN', 'kredit', 1, NULL, 0.00, 1, '2026-02-12 20:02:24'),
(13, '5', 'BEBAN', 'debit', 1, NULL, 0.00, 1, '2026-02-12 20:02:24'),
(14, '1000', 'Kas', 'debit', 1, NULL, 0.00, 1, '2026-02-12 20:41:46'),
(15, '1100', 'Bank', 'debit', 1, NULL, 0.00, 1, '2026-02-12 20:41:46'),
(16, '1200', 'Piutang Anggota', 'debit', 1, NULL, 0.00, 1, '2026-02-12 20:41:46'),
(17, '1300', 'Piutang Pinjaman', 'debit', 1, NULL, 0.00, 1, '2026-02-12 20:41:46'),
(18, '1400', 'Inventaris', 'debit', 1, NULL, 0.00, 1, '2026-02-12 20:41:46'),
(19, '1500', 'Aset Tetap', 'debit', 1, NULL, 0.00, 1, '2026-02-12 20:41:46'),
(20, '2000', 'Simpanan Anggota', 'kredit', 1, NULL, 0.00, 1, '2026-02-12 20:41:46'),
(21, '2100', 'Pinjaman Bank', 'kredit', 1, NULL, 0.00, 1, '2026-02-12 20:41:46'),
(22, '2200', 'Hutang Usaha', 'kredit', 1, NULL, 0.00, 1, '2026-02-12 20:41:46'),
(23, '3000', 'Modal Pokok', 'kredit', 1, NULL, 0.00, 1, '2026-02-12 20:41:46'),
(24, '3100', 'Modal Penyerta', 'kredit', 1, NULL, 0.00, 1, '2026-02-12 20:41:46'),
(25, '3200', 'Cadangan Resiko', 'kredit', 1, NULL, 0.00, 1, '2026-02-12 20:41:46'),
(26, '3300', 'SHU Tahun Berjalan', 'kredit', 1, NULL, 0.00, 1, '2026-02-12 20:41:46'),
(27, '3400', 'SHU Ditahan', 'kredit', 1, NULL, 0.00, 1, '2026-02-12 20:41:46'),
(28, '4000', 'Pendapatan Bunga Pinjaman', 'kredit', 1, NULL, 0.00, 1, '2026-02-12 20:41:46'),
(29, '4100', 'Pendapatan Jasa Administrasi', 'kredit', 1, NULL, 0.00, 1, '2026-02-12 20:41:46'),
(30, '4200', 'Pendapatan Penjualan', 'kredit', 1, NULL, 0.00, 1, '2026-02-12 20:41:46'),
(31, '4300', 'Pendapatan Lain-lain', 'kredit', 1, NULL, 0.00, 1, '2026-02-12 20:41:46'),
(32, '5000', 'Beban Bunga Simpanan', 'debit', 1, NULL, 0.00, 1, '2026-02-12 20:41:46'),
(33, '5100', 'Beban Operasional', 'debit', 1, NULL, 0.00, 1, '2026-02-12 20:41:46'),
(34, '5200', 'Beban Penyusutan', 'debit', 1, NULL, 0.00, 1, '2026-02-12 20:41:46'),
(35, '5300', 'Beban Lain-lain', 'debit', 1, NULL, 0.00, 1, '2026-02-12 20:41:46');

-- --------------------------------------------------------

--
-- Struktur dari tabel `collection_actions`
--

CREATE TABLE `collection_actions` (
  `id` int(11) NOT NULL,
  `collection_id` int(11) NOT NULL,
  `action_type` enum('email','sms','call','letter','payment_plan','legal_action') NOT NULL,
  `action_status` enum('scheduled','sent','delivered','responded','failed') DEFAULT 'scheduled',
  `scheduled_date` datetime DEFAULT NULL,
  `executed_date` datetime DEFAULT NULL,
  `response_received` tinyint(1) DEFAULT 0,
  `response_details` text DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `collection_automation`
--

CREATE TABLE `collection_automation` (
  `id` int(11) NOT NULL,
  `loan_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `collection_strategy` varchar(50) NOT NULL,
  `overdue_days` int(11) NOT NULL,
  `amount_overdue` decimal(15,2) NOT NULL,
  `last_payment_date` date DEFAULT NULL,
  `next_action_date` date DEFAULT NULL,
  `action_taken` varchar(100) DEFAULT NULL,
  `communication_log` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`communication_log`)),
  `status` enum('active','paused','completed','escalated') DEFAULT 'active',
  `priority_level` enum('low','medium','high','critical') DEFAULT 'medium',
  `assigned_agent_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `collection_templates`
--

CREATE TABLE `collection_templates` (
  `id` int(11) NOT NULL,
  `template_name` varchar(100) NOT NULL,
  `template_type` enum('email','sms','letter','call_script') NOT NULL,
  `overdue_days_min` int(11) DEFAULT 0,
  `overdue_days_max` int(11) DEFAULT 999,
  `priority_level` enum('low','medium','high','critical') DEFAULT 'medium',
  `subject` varchar(200) DEFAULT NULL,
  `content` text NOT NULL,
  `variables` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`variables`)),
  `is_active` tinyint(1) DEFAULT 1,
  `success_rate` decimal(5,2) DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `collection_templates`
--

INSERT INTO `collection_templates` (`id`, `template_name`, `template_type`, `overdue_days_min`, `overdue_days_max`, `priority_level`, `subject`, `content`, `variables`, `is_active`, `success_rate`, `created_at`) VALUES
(1, 'Friendly Reminder', 'email', 1, 7, 'low', 'Friendly Reminder: Your KSP Samosir Loan Payment', 'Dear {member_name},\n\nWe hope this email finds you well. We noticed your loan payment of Rp {amount_due} is due on {due_date}.\n\nPlease make your payment to avoid any late fees.\n\nBest regards,\nKSP Samosir Team', NULL, 1, 0.00, '2026-02-14 22:00:02'),
(2, 'Payment Overdue Notice', 'email', 8, 30, 'medium', 'Important: Your Loan Payment is Overdue', 'Dear {member_name},\n\nYour loan payment of Rp {amount_due} was due on {due_date} and is now {days_overdue} days overdue.\n\nPlease contact us immediately to discuss payment arrangements.\n\nBest regards,\nKSP Samosir Team', NULL, 1, 0.00, '2026-02-14 22:00:02'),
(3, 'Final Notice', 'email', 31, 60, 'high', 'FINAL NOTICE: Immediate Action Required', 'Dear {member_name},\n\nThis is your final notice regarding the overdue loan payment of Rp {amount_due}.\n\nFailure to make payment within 7 days may result in legal action.\n\nContact us immediately.\n\nBest regards,\nKSP Samosir Team', NULL, 1, 0.00, '2026-02-14 22:00:02');

-- --------------------------------------------------------

--
-- Struktur dari tabel `community_activities`
--

CREATE TABLE `community_activities` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity_type` varchar(50) NOT NULL,
  `activity_target_id` int(11) DEFAULT NULL,
  `points_earned` int(11) DEFAULT 0,
  `engagement_score` decimal(5,2) DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `compliance_audits`
--

CREATE TABLE `compliance_audits` (
  `id` int(11) NOT NULL,
  `cooperative_id` int(11) NOT NULL,
  `audit_type` enum('internal','external','regulatory','financial') NOT NULL,
  `audit_period` varchar(20) DEFAULT NULL,
  `audit_firm` varchar(200) DEFAULT NULL,
  `auditor_name` varchar(100) DEFAULT NULL,
  `audit_scope` text DEFAULT NULL,
  `audit_objectives` text DEFAULT NULL,
  `findings` text DEFAULT NULL,
  `recommendations` text DEFAULT NULL,
  `corrective_actions` text DEFAULT NULL,
  `audit_result` enum('passed','conditional_pass','failed') DEFAULT 'passed',
  `compliance_score` decimal(5,2) DEFAULT NULL,
  `critical_findings` int(11) DEFAULT 0,
  `action_deadline` date DEFAULT NULL,
  `action_status` enum('pending','in_progress','completed','overdue') DEFAULT 'pending',
  `follow_up_date` date DEFAULT NULL,
  `audit_report_file` varchar(500) DEFAULT NULL,
  `management_response` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `compliance_checks`
--

CREATE TABLE `compliance_checks` (
  `id` int(11) NOT NULL,
  `check_type` varchar(100) NOT NULL,
  `check_name` varchar(255) NOT NULL,
  `status` enum('compliant','warning','error') DEFAULT 'compliant',
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `last_checked` timestamp NULL DEFAULT current_timestamp(),
  `next_check` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `compliance_metrics`
--

CREATE TABLE `compliance_metrics` (
  `id` int(11) NOT NULL,
  `regulation` varchar(100) NOT NULL,
  `compliance_status` enum('compliant','non_compliant','pending_review') DEFAULT 'pending_review',
  `last_audit` date DEFAULT NULL,
  `next_audit` date DEFAULT NULL,
  `findings` text DEFAULT NULL,
  `remediation_plan` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `compliance_monitoring`
--

CREATE TABLE `compliance_monitoring` (
  `id` int(11) NOT NULL,
  `governance_id` int(11) NOT NULL,
  `cooperative_id` int(11) NOT NULL,
  `compliance_status` enum('compliant','non_compliant','partial','exempted') DEFAULT 'compliant',
  `compliance_score` decimal(5,2) DEFAULT 100.00,
  `assessment_date` date NOT NULL,
  `next_assessment` date DEFAULT NULL,
  `findings` text DEFAULT NULL,
  `corrective_actions` text DEFAULT NULL,
  `action_deadline` date DEFAULT NULL,
  `action_status` enum('pending','in_progress','completed','overdue') DEFAULT 'pending',
  `assessed_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `contact_type` enum('phone','mobile','email','fax','website') NOT NULL,
  `contact_value` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `contract_interactions`
--

CREATE TABLE `contract_interactions` (
  `id` int(11) NOT NULL,
  `contract_id` int(11) NOT NULL,
  `interaction_type` enum('deploy','call','query','event') NOT NULL,
  `method_name` varchar(100) DEFAULT NULL,
  `parameters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`parameters`)),
  `result` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`result`)),
  `transaction_hash` varchar(100) DEFAULT NULL,
  `gas_used` bigint(20) DEFAULT NULL,
  `status` enum('success','failed','pending') DEFAULT 'success',
  `executed_by` int(11) DEFAULT NULL,
  `executed_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `contract_templates`
--

CREATE TABLE `contract_templates` (
  `id` int(11) NOT NULL,
  `template_name` varchar(100) NOT NULL,
  `template_type` varchar(50) NOT NULL,
  `solidity_code` text NOT NULL,
  `abi_template` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`abi_template`)),
  `parameters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`parameters`)),
  `description` text DEFAULT NULL,
  `version` varchar(20) DEFAULT '1.0.0',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `conversion_metrics`
--

CREATE TABLE `conversion_metrics` (
  `id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `conversion_type` varchar(50) NOT NULL,
  `conversion_value` decimal(10,2) DEFAULT 0.00,
  `funnel_step` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cooperative_accounts`
--

CREATE TABLE `cooperative_accounts` (
  `id` int(11) NOT NULL,
  `cooperative_id` int(11) NOT NULL,
  `account_code` varchar(20) NOT NULL,
  `account_name` varchar(200) NOT NULL,
  `account_type` enum('asset','liability','equity','income','expense') NOT NULL,
  `account_category` varchar(100) DEFAULT NULL,
  `parent_account_id` int(11) DEFAULT NULL,
  `balance_sheet_classification` enum('current_asset','noncurrent_asset','current_liability','noncurrent_liability','equity') DEFAULT NULL,
  `income_statement_classification` enum('operating_income','other_income','operating_expense','other_expense') DEFAULT NULL,
  `normal_balance` enum('debit','credit') DEFAULT NULL,
  `allow_manual_entry` tinyint(1) DEFAULT 1,
  `requires_approval` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cooperative_activities`
--

CREATE TABLE `cooperative_activities` (
  `id` int(11) NOT NULL,
  `cooperative_id` int(11) NOT NULL,
  `activity_name` varchar(200) NOT NULL,
  `activity_type` enum('economic','social','educational','cultural','environmental') NOT NULL,
  `activity_category` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `budget_allocated` decimal(12,2) DEFAULT NULL,
  `budget_used` decimal(12,2) DEFAULT NULL,
  `target_members` int(11) DEFAULT NULL,
  `target_community` int(11) DEFAULT NULL,
  `actual_beneficiaries` int(11) DEFAULT NULL,
  `responsible_person` varchar(100) DEFAULT NULL,
  `partners` text DEFAULT NULL,
  `status` enum('planned','ongoing','completed','cancelled') DEFAULT 'planned',
  `results_achieved` text DEFAULT NULL,
  `impact_metrics` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`impact_metrics`)),
  `lessons_learned` text DEFAULT NULL,
  `activity_reports` text DEFAULT NULL,
  `photos_videos` text DEFAULT NULL,
  `ministry_approval_required` tinyint(1) DEFAULT 0,
  `ministry_approval_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cooperative_members`
--

CREATE TABLE `cooperative_members` (
  `id` int(11) NOT NULL,
  `member_number` varchar(20) NOT NULL,
  `cooperative_id` int(11) NOT NULL,
  `full_name` varchar(200) NOT NULL,
  `nik` varchar(20) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `birth_place` varchar(100) DEFAULT NULL,
  `gender` enum('L','P') DEFAULT NULL,
  `marital_status` enum('single','married','divorced','widowed') DEFAULT NULL,
  `religion` varchar(50) DEFAULT NULL,
  `nationality` varchar(50) DEFAULT 'Indonesia',
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `membership_date` date NOT NULL,
  `membership_type` enum('regular','honorary','collective') DEFAULT 'regular',
  `membership_status` enum('active','inactive','suspended','resigned','expelled') DEFAULT 'active',
  `share_value` decimal(10,2) DEFAULT 0.00,
  `total_shares` int(11) DEFAULT 0,
  `share_certificate_number` varchar(50) DEFAULT NULL,
  `mandatory_savings` decimal(12,2) DEFAULT 0.00,
  `voluntary_savings` decimal(12,2) DEFAULT 0.00,
  `special_savings` decimal(12,2) DEFAULT 0.00,
  `employment_status` enum('employed','self_employed','unemployed','student','retired') DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `employer` varchar(200) DEFAULT NULL,
  `rat_participation_years` int(11) DEFAULT 0,
  `last_rat_attendance` date DEFAULT NULL,
  `kyc_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `aml_risk_level` enum('low','medium','high') DEFAULT 'low',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cooperative_network`
--

CREATE TABLE `cooperative_network` (
  `id` int(11) NOT NULL,
  `cooperative_name` varchar(200) NOT NULL,
  `cooperative_code` varchar(20) DEFAULT NULL,
  `partnership_type` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive','pending') DEFAULT 'pending',
  `shared_services` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`shared_services`)),
  `api_endpoints` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`api_endpoints`)),
  `joined_at` datetime DEFAULT current_timestamp(),
  `last_sync` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cooperative_structure`
--

CREATE TABLE `cooperative_structure` (
  `id` int(11) NOT NULL,
  `cooperative_name` varchar(200) NOT NULL,
  `cooperative_code` varchar(20) NOT NULL,
  `legal_entity_number` varchar(50) DEFAULT NULL,
  `establishment_date` date NOT NULL,
  `business_sector` varchar(100) NOT NULL,
  `cooperative_type` enum('primary','secondary','tertiary') DEFAULT 'primary',
  `membership_type` enum('open','closed') DEFAULT 'open',
  `operational_area` varchar(100) DEFAULT NULL,
  `address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `province` varchar(100) NOT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `website` varchar(200) DEFAULT NULL,
  `chairman_name` varchar(100) DEFAULT NULL,
  `vice_chairman_name` varchar(100) DEFAULT NULL,
  `secretary_name` varchar(100) DEFAULT NULL,
  `treasurer_name` varchar(100) DEFAULT NULL,
  `supervisory_board_chairman` varchar(100) DEFAULT NULL,
  `authorized_capital` decimal(15,2) DEFAULT NULL,
  `issued_capital` decimal(15,2) DEFAULT NULL,
  `paid_up_capital` decimal(15,2) DEFAULT NULL,
  `reserve_fund` decimal(15,2) DEFAULT 0.00,
  `education_fund` decimal(15,2) DEFAULT 0.00,
  `registration_number` varchar(50) DEFAULT NULL,
  `ministry_registration_date` date DEFAULT NULL,
  `ojk_registration_number` varchar(50) DEFAULT NULL,
  `ojk_registration_date` date DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `business_license_number` varchar(50) DEFAULT NULL,
  `operational_status` enum('active','inactive','dissolved','under_supervision') DEFAULT 'active',
  `last_audit_date` date DEFAULT NULL,
  `next_audit_date` date DEFAULT NULL,
  `compliance_status` enum('compliant','warning','non_compliant') DEFAULT 'compliant',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `course_modules`
--

CREATE TABLE `course_modules` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `module_title` varchar(255) NOT NULL,
  `module_description` text DEFAULT NULL,
  `module_content` longtext DEFAULT NULL,
  `module_order` int(11) NOT NULL,
  `duration_minutes` int(11) DEFAULT 0,
  `resources` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `course_ratings`
--

CREATE TABLE `course_ratings` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `review` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `credit_scores`
--

CREATE TABLE `credit_scores` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `loan_application_id` int(11) DEFAULT NULL,
  `credit_score` decimal(5,2) NOT NULL,
  `score_range` enum('poor','fair','good','excellent') NOT NULL,
  `risk_level` enum('low','medium','high','very_high') NOT NULL,
  `confidence_score` decimal(5,4) DEFAULT 0.0000,
  `factors` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`factors`)),
  `model_used` varchar(100) DEFAULT NULL,
  `calculated_at` datetime DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `credit_score_factors`
--

CREATE TABLE `credit_score_factors` (
  `id` int(11) NOT NULL,
  `factor_name` varchar(100) NOT NULL,
  `factor_category` varchar(50) NOT NULL,
  `weight` decimal(5,4) DEFAULT 0.0000,
  `description` text DEFAULT NULL,
  `data_source` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `credit_score_factors`
--

INSERT INTO `credit_score_factors` (`id`, `factor_name`, `factor_category`, `weight`, `description`, `data_source`, `is_active`, `created_at`) VALUES
(1, 'payment_history', 'credit_history', 0.3500, 'History of on-time payments', 'loan_payment_history', 1, '2026-02-14 22:00:02'),
(2, 'credit_utilization', 'credit_usage', 0.2000, 'Ratio of credit used to credit available', 'current_loans', 1, '2026-02-14 22:00:02'),
(3, 'credit_age', 'credit_history', 0.1500, 'Length of credit history', 'member_join_date', 1, '2026-02-14 22:00:02'),
(4, 'income_stability', 'income', 0.1000, 'Consistency of income', 'savings_deposits', 1, '2026-02-14 22:00:02'),
(5, 'employment_stability', 'employment', 0.1000, 'Length and stability of employment', 'employment_records', 1, '2026-02-14 22:00:02'),
(6, 'debt_to_income_ratio', 'debt', 0.1000, 'Total debt relative to income', 'current_obligations', 1, '2026-02-14 22:00:02');

-- --------------------------------------------------------

--
-- Struktur dari tabel `credit_scoring_models`
--

CREATE TABLE `credit_scoring_models` (
  `id` int(11) NOT NULL,
  `model_name` varchar(100) NOT NULL,
  `model_version` varchar(20) NOT NULL,
  `model_type` enum('traditional','machine_learning','hybrid') DEFAULT 'traditional',
  `accuracy_score` decimal(5,4) DEFAULT 0.0000,
  `features_used` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features_used`)),
  `training_data_size` int(11) DEFAULT 0,
  `last_trained` datetime DEFAULT NULL,
  `status` enum('active','inactive','training') DEFAULT 'inactive',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cross_border_payments`
--

CREATE TABLE `cross_border_payments` (
  `id` int(11) NOT NULL,
  `payment_reference` varchar(20) NOT NULL,
  `sender_coop_id` int(11) NOT NULL,
  `receiver_coop_id` int(11) NOT NULL,
  `sender_country` varchar(100) NOT NULL,
  `receiver_country` varchar(100) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'IDR',
  `exchange_rate` decimal(10,4) DEFAULT 1.0000,
  `amount_in_receiver_currency` decimal(15,2) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_provider` varchar(100) DEFAULT NULL,
  `transaction_fees` decimal(10,2) DEFAULT 0.00,
  `status` enum('pending','processing','completed','failed','cancelled') DEFAULT 'pending',
  `compliance_status` enum('pending_review','approved','rejected','flagged') DEFAULT 'pending_review',
  `aml_check_result` enum('passed','failed','pending') DEFAULT 'pending',
  `processing_time_hours` int(11) DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `customer_invoices`
--

CREATE TABLE `customer_invoices` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(20) NOT NULL,
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `total_amount` decimal(15,2) NOT NULL,
  `status` enum('unpaid','paid','overdue','cancelled') DEFAULT 'unpaid',
  `due_date` date DEFAULT NULL,
  `paid_date` date DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `decision_rules`
--

CREATE TABLE `decision_rules` (
  `id` int(11) NOT NULL,
  `rule_name` varchar(200) NOT NULL,
  `rule_category` varchar(50) NOT NULL,
  `conditions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`conditions`)),
  `actions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`actions`)),
  `priority` int(11) DEFAULT 1,
  `confidence_threshold` decimal(5,4) DEFAULT 0.8000,
  `requires_approval` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `usage_count` int(11) DEFAULT 0,
  `success_rate` decimal(5,2) DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `demand_forecasts`
--

CREATE TABLE `demand_forecasts` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `forecast_period` varchar(20) NOT NULL,
  `forecast_date` date NOT NULL,
  `forecasted_quantity` decimal(10,2) NOT NULL,
  `confidence_level` decimal(5,2) DEFAULT 0.00,
  `actual_quantity` decimal(10,2) DEFAULT NULL,
  `forecast_accuracy` decimal(5,2) DEFAULT NULL,
  `factors_considered` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`factors_considered`)),
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `department_code` varchar(20) NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Struktur dari tabel `digital_assets`
--

CREATE TABLE `digital_assets` (
  `id` int(11) NOT NULL,
  `asset_name` varchar(100) NOT NULL,
  `asset_symbol` varchar(20) NOT NULL,
  `asset_type` enum('savings_token','loan_token','equity_token','reward_token') DEFAULT 'savings_token',
  `total_supply` decimal(20,8) NOT NULL,
  `circulating_supply` decimal(20,8) DEFAULT 0.00000000,
  `contract_address` varchar(100) DEFAULT NULL,
  `blockchain_network` varchar(50) DEFAULT 'polygon',
  `decimals` int(11) DEFAULT 18,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `digital_products`
--

CREATE TABLE `digital_products` (
  `id` int(11) NOT NULL,
  `product_type` enum('insurance','investment','loan_product','savings_plan') NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `provider_id` int(11) DEFAULT NULL,
  `terms_conditions` text DEFAULT NULL,
  `pricing_model` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`pricing_model`)),
  `eligibility_criteria` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`eligibility_criteria`)),
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `documents_required` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`documents_required`)),
  `status` enum('active','inactive','coming_soon') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `digital_wallets`
--

CREATE TABLE `digital_wallets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `wallet_address` varchar(100) NOT NULL,
  `wallet_type` enum('hot','cold','custodial') DEFAULT 'custodial',
  `blockchain_network` varchar(50) DEFAULT 'polygon',
  `balance` decimal(20,8) DEFAULT 0.00000000,
  `is_verified` tinyint(1) DEFAULT 0,
  `kyc_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `education_fund_utilization`
--

CREATE TABLE `education_fund_utilization` (
  `id` int(11) NOT NULL,
  `cooperative_id` int(11) NOT NULL,
  `utilization_year` year(4) NOT NULL,
  `program_name` varchar(300) NOT NULL,
  `program_type` enum('training','education','seminar','workshop','study_tour','research') NOT NULL,
  `participants` int(11) DEFAULT NULL,
  `target_audience` varchar(200) DEFAULT NULL,
  `program_provider` varchar(200) DEFAULT NULL,
  `program_duration_days` int(11) DEFAULT NULL,
  `allocated_budget` decimal(10,2) DEFAULT NULL,
  `actual_cost` decimal(10,2) DEFAULT NULL,
  `completion_status` enum('completed','ongoing','cancelled') DEFAULT 'completed',
  `participants_satisfied` int(11) DEFAULT NULL,
  `satisfaction_rating` decimal(3,1) DEFAULT NULL,
  `learning_outcomes` text DEFAULT NULL,
  `program_report` varchar(500) DEFAULT NULL,
  `certificates_issued` int(11) DEFAULT NULL,
  `photos_evidence` text DEFAULT NULL,
  `ministry_approval_required` tinyint(1) DEFAULT 0,
  `ministry_approval_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `efficiency_metrics`
--

CREATE TABLE `efficiency_metrics` (
  `id` int(11) NOT NULL,
  `process_name` varchar(100) NOT NULL,
  `manual_time` decimal(5,2) DEFAULT 0.00,
  `automated_time` decimal(5,2) DEFAULT 0.00,
  `efficiency_gain` decimal(5,2) DEFAULT 0.00,
  `cost_savings` decimal(15,2) DEFAULT 0.00,
  `measured_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(20) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `position` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
  `basic_salary` decimal(15,2) NOT NULL,
  `allowance` decimal(15,2) DEFAULT 0.00,
  `deduction` decimal(15,2) DEFAULT 0.00,
  `supervisor_id` int(11) DEFAULT NULL,
  `hire_date` date NOT NULL,
  `termination_date` date DEFAULT NULL,
  `status` enum('active','inactive','terminated') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `address_id` int(11) DEFAULT NULL,
  `primary_contact_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `position_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `employee_attendance`
--

CREATE TABLE `employee_attendance` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `hours_worked` decimal(5,2) DEFAULT NULL,
  `status` enum('present','absent','late','half_day','overtime') DEFAULT 'present',
  `notes` text DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `recorded_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `entity_addresses`
--

CREATE TABLE `entity_addresses` (
  `id` int(11) NOT NULL,
  `entity_type` enum('member','supplier','employee','customer','other') NOT NULL,
  `entity_id` int(11) NOT NULL,
  `address_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `entity_contacts`
--

CREATE TABLE `entity_contacts` (
  `id` int(11) NOT NULL,
  `entity_type` enum('member','supplier','employee','customer','other') NOT NULL,
  `entity_id` int(11) NOT NULL,
  `contact_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `error_logs`
--

CREATE TABLE `error_logs` (
  `id` int(11) NOT NULL,
  `error_level` varchar(20) NOT NULL,
  `error_message` text NOT NULL,
  `error_file` varchar(500) DEFAULT NULL,
  `error_line` int(11) DEFAULT NULL,
  `error_context` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`error_context`)),
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `financial_periods`
--

CREATE TABLE `financial_periods` (
  `id` int(11) NOT NULL,
  `cooperative_id` int(11) NOT NULL,
  `period_year` year(4) NOT NULL,
  `period_type` enum('annual','quarterly','monthly') DEFAULT 'annual',
  `period_number` int(11) DEFAULT 1,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('open','closed','locked') DEFAULT 'open',
  `closed_by` int(11) DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `total_assets` decimal(15,2) DEFAULT NULL,
  `total_liabilities` decimal(15,2) DEFAULT NULL,
  `total_equity` decimal(15,2) DEFAULT NULL,
  `net_income` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `fixed_assets`
--

CREATE TABLE `fixed_assets` (
  `id` int(11) NOT NULL,
  `asset_code` varchar(20) NOT NULL,
  `asset_name` varchar(255) NOT NULL,
  `category` enum('tanah','bangunan','kendaraan','peralatan','inventaris','lainnya') NOT NULL,
  `acquisition_date` date NOT NULL,
  `acquisition_cost` decimal(15,2) NOT NULL,
  `useful_life_years` int(11) NOT NULL,
  `salvage_value` decimal(15,2) DEFAULT 0.00,
  `location` varchar(255) DEFAULT NULL,
  `condition_status` enum('excellent','good','fair','poor','critical','disposed') DEFAULT 'excellent',
  `disposal_date` date DEFAULT NULL,
  `disposal_value` decimal(15,2) DEFAULT NULL,
  `last_maintenance_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `fraud_alerts`
--

CREATE TABLE `fraud_alerts` (
  `id` int(11) NOT NULL,
  `alert_type` varchar(50) NOT NULL,
  `severity` enum('low','medium','high','critical') DEFAULT 'medium',
  `transaction_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `risk_score` decimal(5,4) NOT NULL,
  `risk_factors` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`risk_factors`)),
  `ai_prediction` tinyint(1) DEFAULT 0,
  `investigated` tinyint(1) DEFAULT 0,
  `investigation_result` varchar(50) DEFAULT NULL,
  `investigator_id` int(11) DEFAULT NULL,
  `investigated_at` datetime DEFAULT NULL,
  `action_taken` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `fraud_patterns`
--

CREATE TABLE `fraud_patterns` (
  `id` int(11) NOT NULL,
  `pattern_name` varchar(100) NOT NULL,
  `pattern_type` varchar(50) NOT NULL,
  `detection_rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`detection_rules`)),
  `risk_weight` decimal(3,2) DEFAULT 1.00,
  `false_positive_rate` decimal(5,4) DEFAULT 0.0000,
  `detection_count` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `governance_bodies`
--

CREATE TABLE `governance_bodies` (
  `id` int(11) NOT NULL,
  `cooperative_id` int(11) NOT NULL,
  `body_type` enum('board_of_directors','supervisory_board','executive_committee') NOT NULL,
  `position_title` varchar(100) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `external_member_name` varchar(200) DEFAULT NULL,
  `is_external` tinyint(1) DEFAULT 0,
  `term_start_date` date NOT NULL,
  `term_end_date` date NOT NULL,
  `term_years` int(11) DEFAULT 4,
  `authorities` text DEFAULT NULL,
  `responsibilities` text DEFAULT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `appointment_date` date DEFAULT NULL,
  `resignation_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `governance_delegates`
--

CREATE TABLE `governance_delegates` (
  `id` int(11) NOT NULL,
  `delegator_id` int(11) NOT NULL,
  `delegate_id` int(11) NOT NULL,
  `voting_power` decimal(20,8) NOT NULL,
  `delegation_start` datetime DEFAULT current_timestamp(),
  `delegation_end` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `governance_proposals`
--

CREATE TABLE `governance_proposals` (
  `id` int(11) NOT NULL,
  `proposal_title` varchar(200) NOT NULL,
  `proposal_description` text NOT NULL,
  `proposal_type` enum('parameter_change','fund_allocation','contract_upgrade','membership_rules') NOT NULL,
  `proposer_id` int(11) NOT NULL,
  `proposer_address` varchar(100) DEFAULT NULL,
  `status` enum('draft','active','passed','rejected','executed') DEFAULT 'draft',
  `voting_start` datetime DEFAULT NULL,
  `voting_end` datetime DEFAULT NULL,
  `quorum_required` decimal(5,2) DEFAULT 10.00,
  `approval_threshold` decimal(5,2) DEFAULT 51.00,
  `total_votes` decimal(20,8) DEFAULT 0.00000000,
  `yes_votes` decimal(20,8) DEFAULT 0.00000000,
  `no_votes` decimal(20,8) DEFAULT 0.00000000,
  `abstain_votes` decimal(20,8) DEFAULT 0.00000000,
  `execution_tx_hash` varchar(100) DEFAULT NULL,
  `executed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `governance_votes`
--

CREATE TABLE `governance_votes` (
  `id` int(11) NOT NULL,
  `proposal_id` int(11) NOT NULL,
  `voter_id` int(11) NOT NULL,
  `voter_address` varchar(100) DEFAULT NULL,
  `vote_choice` enum('yes','no','abstain') NOT NULL,
  `voting_power` decimal(20,8) NOT NULL,
  `vote_tx_hash` varchar(100) DEFAULT NULL,
  `voted_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `improvement_initiatives`
--

CREATE TABLE `improvement_initiatives` (
  `id` int(11) NOT NULL,
  `initiative_name` varchar(200) NOT NULL,
  `initiative_description` text DEFAULT NULL,
  `category` enum('process','technology','customer_experience','operations','compliance') NOT NULL,
  `priority` enum('low','medium','high','critical') DEFAULT 'medium',
  `status` enum('identified','planned','in_progress','completed','cancelled') DEFAULT 'identified',
  `expected_benefits` text DEFAULT NULL,
  `estimated_cost` decimal(15,2) DEFAULT NULL,
  `estimated_effort_days` int(11) DEFAULT NULL,
  `actual_cost` decimal(15,2) DEFAULT NULL,
  `actual_effort_days` int(11) DEFAULT NULL,
  `success_metrics` text DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `target_completion_date` date DEFAULT NULL,
  `actual_completion_date` date DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `improvement_measurements`
--

CREATE TABLE `improvement_measurements` (
  `id` int(11) NOT NULL,
  `initiative_id` int(11) NOT NULL,
  `metric_name` varchar(100) NOT NULL,
  `baseline_value` decimal(10,2) DEFAULT NULL,
  `target_value` decimal(10,2) DEFAULT NULL,
  `current_value` decimal(10,2) DEFAULT NULL,
  `measurement_date` date NOT NULL,
  `measured_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `international_opportunities`
--

CREATE TABLE `international_opportunities` (
  `id` int(11) NOT NULL,
  `opportunity_title` varchar(200) NOT NULL,
  `target_country` varchar(100) NOT NULL,
  `opportunity_type` enum('market_expansion','technology_partnership','supply_chain','investment','cultural_exchange') NOT NULL,
  `market_size` decimal(15,2) DEFAULT NULL,
  `growth_potential` decimal(5,2) DEFAULT NULL,
  `competitive_landscape` text DEFAULT NULL,
  `entry_barriers` text DEFAULT NULL,
  `required_resources` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`required_resources`)),
  `timeline_estimate` varchar(100) DEFAULT NULL,
  `risk_level` enum('low','medium','high') DEFAULT 'medium',
  `potential_roi` decimal(5,2) DEFAULT NULL,
  `contact_network` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`contact_network`)),
  `status` enum('identified','analyzing','pursuing','implemented','abandoned') DEFAULT 'identified',
  `identified_by` int(11) DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `international_partnerships`
--

CREATE TABLE `international_partnerships` (
  `id` int(11) NOT NULL,
  `partnership_name` varchar(200) NOT NULL,
  `local_coop_id` int(11) NOT NULL,
  `international_partner` varchar(200) NOT NULL,
  `partner_country` varchar(100) NOT NULL,
  `partnership_type` enum('trade_agreement','technology_transfer','joint_venture','market_expansion','cultural_exchange') DEFAULT 'trade_agreement',
  `partnership_scope` text DEFAULT NULL,
  `strategic_objectives` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`strategic_objectives`)),
  `timeline` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`timeline`)),
  `investment_required` decimal(15,2) DEFAULT 0.00,
  `expected_benefits` text DEFAULT NULL,
  `risk_assessment` text DEFAULT NULL,
  `legal_framework` varchar(200) DEFAULT NULL,
  `status` enum('planning','negotiating','active','completed','terminated') DEFAULT 'planning',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `key_contacts` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`key_contacts`)),
  `progress_metrics` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`progress_metrics`)),
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `inter_coop_trades`
--

CREATE TABLE `inter_coop_trades` (
  `id` int(11) NOT NULL,
  `trade_reference` varchar(20) NOT NULL,
  `seller_coop_id` int(11) NOT NULL,
  `buyer_coop_id` int(11) NOT NULL,
  `trade_type` enum('goods','services','technology','resources','joint_venture') DEFAULT 'goods',
  `trade_category` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `total_value` decimal(15,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'IDR',
  `payment_terms` varchar(200) DEFAULT NULL,
  `delivery_terms` varchar(200) DEFAULT NULL,
  `trade_status` enum('negotiating','agreed','in_progress','completed','cancelled','disputed') DEFAULT 'negotiating',
  `contract_signed` tinyint(1) DEFAULT 0,
  `contract_document` varchar(500) DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `completion_date` date DEFAULT NULL,
  `quality_rating` decimal(3,1) DEFAULT NULL,
  `feedback_text` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `inventory_items`
--

CREATE TABLE `inventory_items` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `batch_number` varchar(50) DEFAULT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `quantity_on_hand` decimal(10,2) DEFAULT 0.00,
  `quantity_reserved` decimal(10,2) DEFAULT 0.00,
  `quantity_available` decimal(10,2) DEFAULT 0.00,
  `unit_cost` decimal(10,2) DEFAULT 0.00,
  `location_code` varchar(50) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `manufacturing_date` date DEFAULT NULL,
  `quality_status` enum('good','damaged','expired','quarantined') DEFAULT 'good',
  `last_inventory_check` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `inventory_transactions`
--

CREATE TABLE `inventory_transactions` (
  `id` int(11) NOT NULL,
  `inventory_item_id` int(11) NOT NULL,
  `transaction_type` enum('receipt','issue','adjustment','transfer','return') NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `from_location` varchar(100) DEFAULT NULL,
  `to_location` varchar(100) DEFAULT NULL,
  `performed_by` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `transaction_date` datetime DEFAULT current_timestamp()
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
-- Struktur dari tabel `journal_entries`
--

CREATE TABLE `journal_entries` (
  `id` int(11) NOT NULL,
  `cooperative_id` int(11) NOT NULL,
  `entry_number` varchar(20) NOT NULL,
  `entry_date` date NOT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('draft','posted','reversed') DEFAULT 'draft',
  `posted_by` int(11) DEFAULT NULL,
  `posted_at` datetime DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `journal_lines`
--

CREATE TABLE `journal_lines` (
  `id` int(11) NOT NULL,
  `journal_entry_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `debit` decimal(15,2) DEFAULT 0.00,
  `credit` decimal(15,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL
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
-- Struktur dari tabel `knowledge_base`
--

CREATE TABLE `knowledge_base` (
  `id` int(11) NOT NULL,
  `title` varchar(300) NOT NULL,
  `content_type` enum('article','case_study','best_practice','research','video','webinar') NOT NULL,
  `category` varchar(100) NOT NULL,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `content` text DEFAULT NULL,
  `author_coop_id` int(11) DEFAULT NULL,
  `author_name` varchar(100) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `view_count` int(11) DEFAULT 0,
  `download_count` int(11) DEFAULT 0,
  `rating` decimal(3,1) DEFAULT 0.0,
  `rating_count` int(11) DEFAULT 0,
  `language` varchar(10) DEFAULT 'id',
  `access_level` enum('public','network_only','premium') DEFAULT 'network_only',
  `expires_at` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `knowledge_ratings`
--

CREATE TABLE `knowledge_ratings` (
  `id` int(11) NOT NULL,
  `knowledge_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` decimal(3,1) NOT NULL,
  `review` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
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
    )$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `laporan_pengawas`
--

CREATE TABLE `laporan_pengawas` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `periode_mulai` date DEFAULT NULL,
  `periode_akhir` date DEFAULT NULL,
  `isi_laporan` text DEFAULT NULL,
  `rekomendasi` text DEFAULT NULL,
  `status` enum('draft','final','disetujui') DEFAULT 'draft',
  `created_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `learning_analytics`
--

CREATE TABLE `learning_analytics` (
  `id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `enrollment_id` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `event_type` varchar(50) NOT NULL,
  `event_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`event_data`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `learning_courses`
--

CREATE TABLE `learning_courses` (
  `id` int(11) NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` enum('koperasi_dasar','manajemen','keuangan','hukum','teknologi','pengembangan_diri','lainnya') NOT NULL,
  `difficulty_level` enum('beginner','intermediate','advanced') DEFAULT 'beginner',
  `duration_hours` int(11) NOT NULL,
  `max_participants` int(11) DEFAULT 0,
  `enrollment_deadline` date DEFAULT NULL,
  `course_content` longtext DEFAULT NULL,
  `prerequisites` text DEFAULT NULL,
  `learning_objectives` text DEFAULT NULL,
  `status` enum('draft','active','inactive','completed') DEFAULT 'draft',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `learning_enrollments`
--

CREATE TABLE `learning_enrollments` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `enrollment_date` date NOT NULL,
  `completion_date` date DEFAULT NULL,
  `progress_percentage` decimal(5,2) DEFAULT 0.00,
  `status` enum('active','completed','dropped','failed') DEFAULT 'active',
  `certificate_issued` tinyint(1) DEFAULT 0,
  `final_score` decimal(5,2) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `learning_progress`
--

CREATE TABLE `learning_progress` (
  `id` int(11) NOT NULL,
  `enrollment_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `status` enum('not_started','in_progress','completed') DEFAULT 'not_started',
  `completion_date` date DEFAULT NULL,
  `time_spent_minutes` int(11) DEFAULT 0,
  `score` decimal(5,2) DEFAULT NULL,
  `attempts` int(11) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ledger_entries`
--

CREATE TABLE `ledger_entries` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `account_type` varchar(50) NOT NULL,
  `account_id` int(11) NOT NULL,
  `debit` decimal(20,8) DEFAULT 0.00000000,
  `credit` decimal(20,8) DEFAULT 0.00000000,
  `balance` decimal(20,8) DEFAULT 0.00000000,
  `asset_symbol` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `entry_type` enum('user_transaction','system_adjustment','fee','reward') DEFAULT 'user_transaction',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `legal_documents`
--

CREATE TABLE `legal_documents` (
  `id` int(11) NOT NULL,
  `cooperative_id` int(11) NOT NULL,
  `document_type` enum('bylaws','articles_of_association','registration_certificate','business_license','tax_certificate','rat_minutes','board_decisions','contracts','regulatory_approvals') NOT NULL,
  `document_title` varchar(300) NOT NULL,
  `document_number` varchar(100) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `issuing_authority` varchar(200) DEFAULT NULL,
  `document_file` varchar(500) DEFAULT NULL,
  `document_content` text DEFAULT NULL,
  `digital_signature` text DEFAULT NULL,
  `status` enum('active','expired','revoked','superseded') DEFAULT 'active',
  `compliance_required` tinyint(1) DEFAULT 1,
  `renewal_required` tinyint(1) DEFAULT 0,
  `renewal_date` date DEFAULT NULL,
  `access_level` enum('public','members_only','board_only','management_only') DEFAULT 'members_only',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `logistics_providers`
--

CREATE TABLE `logistics_providers` (
  `id` int(11) NOT NULL,
  `provider_name` varchar(100) NOT NULL,
  `provider_code` varchar(20) NOT NULL,
  `api_endpoint` varchar(500) DEFAULT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `service_types` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`service_types`)),
  `rate_card` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`rate_card`)),
  `status` enum('active','inactive') DEFAULT 'active',
  `contract_start_date` date DEFAULT NULL,
  `contract_end_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(30, 1, 'login', 'users', 1, NULL, '{\"id\":1,\"username\":\"admin\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"admin@ksp_samosir.com\",\"full_name\":\"Administrator\",\"is_active\":1,\"last_login\":\"2026-02-13 03:56:34\",\"role\":\"admin\",\"role_description\":\"Administrator with management access\"}', '127.0.0.1', 'curl/7.81.0', '2026-02-12 20:59:32'),
(31, 1, 'login', 'users', 1, NULL, '{\"id\":1,\"username\":\"admin\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"admin@ksp_samosir.com\",\"full_name\":\"Administrator\",\"is_active\":1,\"last_login\":\"2026-02-13 03:59:32\",\"role\":\"admin\",\"role_description\":\"Administrator with management access\"}', '127.0.0.1', 'Mozilla/5.0 (X11; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-13 01:03:42'),
(32, 1, 'login', 'users', 1, NULL, '{\"id\":1,\"username\":\"admin\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"admin@ksp_samosir.com\",\"full_name\":\"Administrator\",\"is_active\":1,\"last_login\":\"2026-02-13 08:03:42\",\"role\":\"admin\",\"role_description\":\"Administrator with management access\"}', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-13 01:10:08'),
(33, 1, 'login', 'users', 1, NULL, '{\"id\":1,\"username\":\"admin\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"admin@ksp_samosir.com\",\"full_name\":\"Administrator\",\"is_active\":1,\"last_login\":\"2026-02-13 08:10:08\",\"role\":\"admin\",\"role_description\":\"Administrator with management access\"}', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-13 01:40:16'),
(34, 1, 'login', 'users', 1, NULL, '{\"id\":1,\"username\":\"admin\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"admin@ksp_samosir.com\",\"full_name\":\"Administrator\",\"is_active\":1,\"last_login\":\"2026-02-13 08:40:16\",\"role\":\"admin\",\"role_description\":\"Administrator with management access\"}', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-13 02:27:40'),
(35, 1, 'login', 'users', 1, NULL, '{\"id\":1,\"username\":\"admin\",\"password\":\"$2y$10$puqQ47Z6i\\/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m\\/il.UeIdYvu\",\"email\":\"admin@ksp_samosir.com\",\"full_name\":\"Administrator\",\"is_active\":1,\"last_login\":\"2026-02-13 09:27:40\",\"role\":\"admin\",\"role_description\":\"Administrator with management access\"}', '::1', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-13 03:54:36');

-- --------------------------------------------------------

--
-- Struktur dari tabel `loyalty_program`
--

CREATE TABLE `loyalty_program` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `current_points` int(11) DEFAULT 0,
  `total_points_earned` int(11) DEFAULT 0,
  `total_points_redeemed` int(11) DEFAULT 0,
  `tier` enum('bronze','silver','gold','platinum') DEFAULT 'bronze',
  `tier_upgrade_date` date DEFAULT NULL,
  `tier_expiry_date` date DEFAULT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `loyalty_rewards`
--

CREATE TABLE `loyalty_rewards` (
  `id` int(11) NOT NULL,
  `reward_name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `reward_type` enum('discount','free_product','cashback','exclusive_access') NOT NULL,
  `points_required` int(11) NOT NULL,
  `value` decimal(10,2) DEFAULT NULL,
  `quantity_available` int(11) DEFAULT NULL,
  `valid_from` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `terms_conditions` text DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `loyalty_rewards`
--

INSERT INTO `loyalty_rewards` (`id`, `reward_name`, `description`, `reward_type`, `points_required`, `value`, `quantity_available`, `valid_from`, `valid_until`, `image_url`, `terms_conditions`, `active`, `created_at`) VALUES
(1, '5% Discount Voucher', 'Get 5% discount on your next purchase', 'discount', 100, 50000.00, 1000, NULL, NULL, NULL, NULL, 1, '2026-02-14 22:00:06'),
(2, 'Free Shipping', 'Free shipping on orders over Rp 100,000', 'discount', 150, 25000.00, 500, NULL, NULL, NULL, NULL, 1, '2026-02-14 22:00:06'),
(3, 'Exclusive Member Event', 'Invitation to exclusive member-only events', 'exclusive_access', 300, 0.00, 50, NULL, NULL, NULL, NULL, 1, '2026-02-14 22:00:06'),
(4, 'Bonus Savings Interest', 'Extra 0.5% interest on savings for 3 months', 'cashback', 200, 100000.00, 200, NULL, NULL, NULL, NULL, 1, '2026-02-14 22:00:06');

-- --------------------------------------------------------

--
-- Struktur dari tabel `loyalty_transactions`
--

CREATE TABLE `loyalty_transactions` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `transaction_type` enum('earned','redeemed','expired','bonus') NOT NULL,
  `points` int(11) NOT NULL,
  `reason` varchar(200) DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `processed_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `marketplace_analytics`
--

CREATE TABLE `marketplace_analytics` (
  `id` int(11) NOT NULL,
  `metric_type` varchar(50) NOT NULL,
  `metric_value` decimal(10,2) DEFAULT NULL,
  `dimension` varchar(50) DEFAULT NULL,
  `dimension_value` varchar(100) DEFAULT NULL,
  `recorded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `marketplace_campaigns`
--

CREATE TABLE `marketplace_campaigns` (
  `id` int(11) NOT NULL,
  `campaign_name` varchar(200) NOT NULL,
  `campaign_type` enum('discount','bogo','flash_sale','loyalty_bonus') NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `target_audience` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`target_audience`)),
  `discount_percentage` decimal(5,2) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT NULL,
  `minimum_purchase` decimal(10,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `usage_count` int(11) DEFAULT 0,
  `coupon_code` varchar(20) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `marketplace_cart`
--

CREATE TABLE `marketplace_cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `marketplace_categories`
--

CREATE TABLE `marketplace_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `marketplace_categories`
--

INSERT INTO `marketplace_categories` (`id`, `name`, `description`, `parent_id`, `icon`, `sort_order`, `active`, `created_at`) VALUES
(1, 'Electronics', 'Electronic devices and gadgets', NULL, 'device-desktop', 1, 1, '2026-02-14 22:00:06'),
(2, 'Fashion & Clothing', 'Clothing, shoes, and fashion accessories', NULL, 'shirt', 2, 1, '2026-02-14 22:00:06'),
(3, 'Home & Garden', 'Home improvement and gardening supplies', NULL, 'house', 3, 1, '2026-02-14 22:00:06'),
(4, 'Books & Education', 'Books, educational materials, and courses', NULL, 'book', 4, 1, '2026-02-14 22:00:06'),
(5, 'Sports & Recreation', 'Sports equipment and recreational items', NULL, 'balloon', 5, 1, '2026-02-14 22:00:06'),
(6, 'Automotive', 'Car parts, accessories, and automotive services', NULL, 'car', 6, 1, '2026-02-14 22:00:06'),
(7, 'Health & Beauty', 'Health products and beauty items', NULL, 'heart', 7, 1, '2026-02-14 22:00:06'),
(8, 'Services', 'Professional services and consultations', NULL, 'wrench', 8, 1, '2026-02-14 22:00:06'),
(9, 'Collectibles', 'Rare items and collectibles', NULL, 'star', 9, 1, '2026-02-14 22:00:06'),
(10, 'Other', 'Miscellaneous items', NULL, 'box', 10, 1, '2026-02-14 22:00:06');

-- --------------------------------------------------------

--
-- Struktur dari tabel `marketplace_favorites`
--

CREATE TABLE `marketplace_favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `marketplace_products`
--

CREATE TABLE `marketplace_products` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `seller_id` int(11) NOT NULL,
  `seller_type` enum('member','cooperative','vendor') DEFAULT 'member',
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `original_price` decimal(12,2) DEFAULT NULL,
  `quantity_available` int(11) NOT NULL DEFAULT 0,
  `condition_status` enum('new','used','refurbished') DEFAULT 'new',
  `location` varchar(100) DEFAULT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `featured` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive','sold_out','removed') DEFAULT 'active',
  `view_count` int(11) DEFAULT 0,
  `favorite_count` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `marketplace_reviews`
--

CREATE TABLE `marketplace_reviews` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `rating` decimal(3,1) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `title` varchar(200) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `verified_purchase` tinyint(1) DEFAULT 0,
  `helpful_votes` int(11) DEFAULT 0,
  `reported` tinyint(1) DEFAULT 0,
  `status` enum('active','hidden','removed') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `marketplace_transactions`
--

CREATE TABLE `marketplace_transactions` (
  `id` int(11) NOT NULL,
  `transaction_type` varchar(50) NOT NULL,
  `buyer_id` int(11) DEFAULT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `platform_fee` decimal(10,2) DEFAULT 0.00,
  `status` enum('pending','completed','cancelled','refunded') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Struktur dari tabel `member_feedback`
--

CREATE TABLE `member_feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `feedback_type` varchar(50) NOT NULL,
  `rating` decimal(3,1) DEFAULT 0.0,
  `feedback_text` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `responded` tinyint(1) DEFAULT 0,
  `response_text` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
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
-- Struktur dari tabel `monitoring_metrics`
--

CREATE TABLE `monitoring_metrics` (
  `id` int(11) NOT NULL,
  `category` varchar(50) NOT NULL,
  `metric_name` varchar(100) NOT NULL,
  `metric_value` decimal(10,2) DEFAULT 0.00,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `network_analytics`
--

CREATE TABLE `network_analytics` (
  `id` int(11) NOT NULL,
  `metric_type` varchar(50) NOT NULL,
  `metric_period` varchar(20) NOT NULL,
  `metric_value` decimal(15,2) DEFAULT NULL,
  `dimension` varchar(50) DEFAULT NULL,
  `dimension_value` varchar(100) DEFAULT NULL,
  `cooperative_id` int(11) DEFAULT NULL,
  `recorded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `network_governance`
--

CREATE TABLE `network_governance` (
  `id` int(11) NOT NULL,
  `governance_type` varchar(100) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `applicable_to` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`applicable_to`)),
  `effective_date` date NOT NULL,
  `review_date` date DEFAULT NULL,
  `compliance_deadline` date DEFAULT NULL,
  `enforcement_level` enum('guideline','mandatory','critical') DEFAULT 'guideline',
  `status` enum('draft','active','under_review','deprecated') DEFAULT 'draft',
  `created_by` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `notification_logs`
--

CREATE TABLE `notification_logs` (
  `id` int(11) NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `channel` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `recipient` varchar(20) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'sent',
  `sent_at` timestamp NULL DEFAULT current_timestamp(),
  `error_message` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `operational_costs`
--

CREATE TABLE `operational_costs` (
  `id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `subcategory` varchar(100) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'IDR',
  `period` date NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `optimization_alerts`
--

CREATE TABLE `optimization_alerts` (
  `id` int(11) NOT NULL,
  `alert_type` varchar(100) NOT NULL,
  `alert_title` varchar(200) NOT NULL,
  `alert_description` text DEFAULT NULL,
  `severity` enum('info','warning','critical') DEFAULT 'info',
  `affected_component` varchar(200) DEFAULT NULL,
  `current_value` decimal(10,2) DEFAULT NULL,
  `threshold_value` decimal(10,2) DEFAULT NULL,
  `suggested_action` text DEFAULT NULL,
  `auto_resolve` tinyint(1) DEFAULT 0,
  `resolved` tinyint(1) DEFAULT 0,
  `resolved_by` int(11) DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `optimization_executions`
--

CREATE TABLE `optimization_executions` (
  `id` int(11) NOT NULL,
  `rule_id` int(11) NOT NULL,
  `execution_status` enum('success','failed','partial') DEFAULT 'success',
  `triggered_by` varchar(100) DEFAULT NULL,
  `execution_time` decimal(5,2) DEFAULT NULL,
  `impact_metrics` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`impact_metrics`)),
  `error_message` text DEFAULT NULL,
  `executed_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `optimization_predictions`
--

CREATE TABLE `optimization_predictions` (
  `id` int(11) NOT NULL,
  `prediction_type` varchar(100) NOT NULL,
  `target_component` varchar(200) NOT NULL,
  `prediction_basis` text DEFAULT NULL,
  `predicted_issue` text DEFAULT NULL,
  `confidence_level` decimal(5,2) DEFAULT NULL,
  `time_to_impact` varchar(50) DEFAULT NULL,
  `recommended_action` text DEFAULT NULL,
  `preventive_measures` text DEFAULT NULL,
  `status` enum('predicted','prevented','occurred','dismissed') DEFAULT 'predicted',
  `created_at` datetime DEFAULT current_timestamp(),
  `occurred_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `optimization_rules`
--

CREATE TABLE `optimization_rules` (
  `id` int(11) NOT NULL,
  `rule_name` varchar(200) NOT NULL,
  `rule_description` text DEFAULT NULL,
  `trigger_condition` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`trigger_condition`)),
  `optimization_action` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`optimization_action`)),
  `priority` enum('low','medium','high','critical') DEFAULT 'medium',
  `execution_frequency` enum('realtime','hourly','daily','weekly','monthly') DEFAULT 'daily',
  `last_executed` datetime DEFAULT NULL,
  `execution_count` int(11) DEFAULT 0,
  `success_count` int(11) DEFAULT 0,
  `failure_count` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `oracle_data_feeds`
--

CREATE TABLE `oracle_data_feeds` (
  `id` int(11) NOT NULL,
  `oracle_id` int(11) NOT NULL,
  `data_key` varchar(100) NOT NULL,
  `data_value` decimal(20,8) DEFAULT NULL,
  `data_timestamp` datetime DEFAULT current_timestamp(),
  `block_number` bigint(20) DEFAULT NULL,
  `transaction_hash` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `page_views`
--

CREATE TABLE `page_views` (
  `id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `page_url` varchar(500) NOT NULL,
  `page_title` varchar(200) DEFAULT NULL,
  `referrer` varchar(500) DEFAULT NULL,
  `time_on_page` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `payment_attempts`
--

CREATE TABLE `payment_attempts` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_gateway` varchar(50) NOT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `snap_token` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `amount` decimal(15,2) DEFAULT NULL,
  `currency` varchar(3) DEFAULT 'IDR',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `payment_logs`
--

CREATE TABLE `payment_logs` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `gateway` varchar(50) NOT NULL,
  `notification_type` varchar(50) DEFAULT NULL,
  `transaction_status` varchar(50) DEFAULT NULL,
  `payment_type` varchar(50) DEFAULT NULL,
  `fraud_status` varchar(50) DEFAULT NULL,
  `raw_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`raw_data`)),
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `payrolls`
--

CREATE TABLE `payrolls` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `period` varchar(7) NOT NULL,
  `basic_salary` decimal(15,2) NOT NULL,
  `allowance` decimal(15,2) DEFAULT 0.00,
  `deduction` decimal(15,2) DEFAULT 0.00,
  `gross_salary` decimal(15,2) NOT NULL,
  `tax` decimal(15,2) DEFAULT 0.00,
  `net_salary` decimal(15,2) NOT NULL,
  `status` enum('draft','processed','paid') DEFAULT 'processed',
  `payment_date` date DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `payroll_components`
--

CREATE TABLE `payroll_components` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `component_type` enum('allowance','deduction','bonus','overtime') NOT NULL,
  `component_name` varchar(255) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `is_recurring` tinyint(1) DEFAULT 1,
  `effective_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
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
-- Struktur dari tabel `pelanggaran`
--

CREATE TABLE `pelanggaran` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `jenis_pelanggaran` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `tanggal_pelanggaran` date NOT NULL,
  `sanksi_id` int(11) DEFAULT NULL,
  `status` enum('investigasi','diputuskan','dieksekusi','ditutup') DEFAULT 'investigasi',
  `decided_by` int(11) DEFAULT NULL,
  `decided_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
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
-- Struktur dari tabel `performance_benchmarks`
--

CREATE TABLE `performance_benchmarks` (
  `id` int(11) NOT NULL,
  `benchmark_name` varchar(100) NOT NULL,
  `metric_type` varchar(50) NOT NULL,
  `target_value` decimal(10,2) NOT NULL,
  `current_value` decimal(10,2) DEFAULT 0.00,
  `achievement_percentage` decimal(5,2) DEFAULT 0.00,
  `last_updated` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `performance_metrics`
--

CREATE TABLE `performance_metrics` (
  `id` int(11) NOT NULL,
  `metric_type` varchar(50) NOT NULL,
  `metric_name` varchar(100) NOT NULL,
  `value` decimal(15,4) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `threshold_warning` decimal(15,4) DEFAULT NULL,
  `threshold_critical` decimal(15,4) DEFAULT NULL,
  `measured_at` timestamp NULL DEFAULT current_timestamp(),
  `context` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`context`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `performance_optimization_history`
--

CREATE TABLE `performance_optimization_history` (
  `id` int(11) NOT NULL,
  `optimization_type` varchar(100) NOT NULL,
  `target_component` varchar(200) NOT NULL,
  `baseline_metric` decimal(10,2) DEFAULT NULL,
  `optimized_metric` decimal(10,2) DEFAULT NULL,
  `improvement_percentage` decimal(5,2) DEFAULT NULL,
  `implementation_cost` decimal(10,2) DEFAULT NULL,
  `implementation_time_hours` decimal(5,2) DEFAULT NULL,
  `long_term_benefits` text DEFAULT NULL,
  `status` enum('proposed','implemented','measured','reverted') DEFAULT 'proposed',
  `implemented_by` int(11) DEFAULT NULL,
  `implemented_at` datetime DEFAULT NULL,
  `measured_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
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
-- Struktur dari tabel `personalization_rules`
--

CREATE TABLE `personalization_rules` (
  `id` int(11) NOT NULL,
  `rule_name` varchar(200) NOT NULL,
  `trigger_conditions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`trigger_conditions`)),
  `personalization_actions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`personalization_actions`)),
  `target_segment` varchar(100) DEFAULT NULL,
  `priority` int(11) DEFAULT 1,
  `effectiveness_score` decimal(5,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Struktur dari tabel `positions`
--

CREATE TABLE `positions` (
  `id` int(11) NOT NULL,
  `position_code` varchar(20) NOT NULL,
  `position_name` varchar(100) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT 1,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `predictions`
--

CREATE TABLE `predictions` (
  `id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `prediction_type` varchar(50) NOT NULL,
  `predicted_value` decimal(10,2) DEFAULT NULL,
  `confidence_score` decimal(5,4) DEFAULT 0.0000,
  `prediction_factors` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`prediction_factors`)),
  `time_horizon` varchar(20) DEFAULT '3_months',
  `predicted_at` datetime DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `prediction_accuracy`
--

CREATE TABLE `prediction_accuracy` (
  `id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `prediction_id` int(11) NOT NULL,
  `actual_value` decimal(10,2) DEFAULT NULL,
  `predicted_value` decimal(10,2) DEFAULT NULL,
  `accuracy_score` decimal(5,4) DEFAULT NULL,
  `measured_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `predictive_models`
--

CREATE TABLE `predictive_models` (
  `id` int(11) NOT NULL,
  `model_name` varchar(100) NOT NULL,
  `prediction_type` enum('loan_default','member_churn','savings_growth','market_trends') NOT NULL,
  `algorithm_used` varchar(50) NOT NULL,
  `accuracy_score` decimal(5,4) DEFAULT 0.0000,
  `features_used` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features_used`)),
  `training_period_start` date DEFAULT NULL,
  `training_period_end` date DEFAULT NULL,
  `last_retrained` datetime DEFAULT NULL,
  `model_file_path` varchar(500) DEFAULT NULL,
  `status` enum('active','inactive','training') DEFAULT 'inactive',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `predictive_models`
--

INSERT INTO `predictive_models` (`id`, `model_name`, `prediction_type`, `algorithm_used`, `accuracy_score`, `features_used`, `training_period_start`, `training_period_end`, `last_retrained`, `model_file_path`, `status`, `created_at`) VALUES
(1, 'Loan Default Predictor', 'loan_default', 'logistic_regression', 0.0000, NULL, NULL, NULL, NULL, NULL, 'active', '2026-02-14 22:00:02'),
(2, 'Member Churn Predictor', 'member_churn', 'random_forest', 0.0000, NULL, NULL, NULL, NULL, NULL, 'active', '2026-02-14 22:00:02'),
(3, 'Savings Growth Predictor', 'savings_growth', 'linear_regression', 0.0000, NULL, NULL, NULL, NULL, NULL, 'active', '2026-02-14 22:00:02');

-- --------------------------------------------------------

--
-- Struktur dari tabel `product_recommendations`
--

CREATE TABLE `product_recommendations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `recommendation_score` decimal(5,4) DEFAULT NULL,
  `recommendation_reason` varchar(100) DEFAULT NULL,
  `algorithm_used` varchar(50) DEFAULT NULL,
  `shown` tinyint(1) DEFAULT 0,
  `clicked` tinyint(1) DEFAULT 0,
  `purchased` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
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
-- Struktur dari tabel `promos`
--

CREATE TABLE `promos` (
  `id` int(11) NOT NULL,
  `kode_promo` varchar(20) NOT NULL,
  `jenis_diskon` enum('persen','nominal') NOT NULL,
  `nilai_diskon` decimal(10,2) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_akhir` date NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `status` enum('aktif','nonaktif','kadaluarsa') DEFAULT 'aktif',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` int(11) NOT NULL,
  `po_number` varchar(20) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `expected_delivery_date` date DEFAULT NULL,
  `actual_delivery_date` date DEFAULT NULL,
  `status` enum('draft','approved','ordered','partial_delivery','delivered','cancelled') DEFAULT 'draft',
  `total_amount` decimal(15,2) DEFAULT 0.00,
  `tax_amount` decimal(15,2) DEFAULT 0.00,
  `discount_amount` decimal(15,2) DEFAULT 0.00,
  `shipping_cost` decimal(15,2) DEFAULT 0.00,
  `payment_status` enum('unpaid','partial','paid') DEFAULT 'unpaid',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchase_order_items`
--

CREATE TABLE `purchase_order_items` (
  `id` int(11) NOT NULL,
  `po_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `quantity_ordered` decimal(10,2) NOT NULL,
  `quantity_received` decimal(10,2) DEFAULT 0.00,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(12,2) NOT NULL,
  `quality_status` enum('pending','passed','failed','rejected') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `quality_check_criteria`
--

CREATE TABLE `quality_check_criteria` (
  `id` int(11) NOT NULL,
  `inspection_id` int(11) NOT NULL,
  `criteria_name` varchar(200) NOT NULL,
  `expected_value` varchar(100) DEFAULT NULL,
  `actual_value` varchar(100) DEFAULT NULL,
  `result` enum('pass','fail','na') DEFAULT 'pass',
  `severity` enum('critical','major','minor') DEFAULT 'minor',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `quality_inspections`
--

CREATE TABLE `quality_inspections` (
  `id` int(11) NOT NULL,
  `reference_type` varchar(50) NOT NULL,
  `reference_id` int(11) NOT NULL,
  `inspection_type` enum('incoming','in_process','final','random') DEFAULT 'incoming',
  `inspector_id` int(11) NOT NULL,
  `inspection_date` datetime DEFAULT current_timestamp(),
  `overall_result` enum('passed','failed','conditional') DEFAULT 'passed',
  `notes` text DEFAULT NULL,
  `corrective_actions` text DEFAULT NULL,
  `follow_up_required` tinyint(1) DEFAULT 0,
  `follow_up_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `rapat`
--

CREATE TABLE `rapat` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `jenis_rapat` enum('rapat_anggota','rapat_pengurus','rapat_pengawas') NOT NULL,
  `tanggal` date NOT NULL,
  `waktu` time DEFAULT NULL,
  `lokasi` text DEFAULT NULL,
  `agenda` text DEFAULT NULL,
  `status` enum('terjadwal','berlangsung','selesai','dibatalkan') DEFAULT 'terjadwal',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `rapat_keputusan`
--

CREATE TABLE `rapat_keputusan` (
  `id` int(11) NOT NULL,
  `rapat_id` int(11) NOT NULL,
  `keputusan` text NOT NULL,
  `status_pelaksanaan` enum('belum_dilaksanakan','dalam_proses','selesai') DEFAULT 'belum_dilaksanakan',
  `pic` int(11) DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `rapat_notulen`
--

CREATE TABLE `rapat_notulen` (
  `id` int(11) NOT NULL,
  `rapat_id` int(11) NOT NULL,
  `isi_notulen` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `rapat_peserta`
--

CREATE TABLE `rapat_peserta` (
  `id` int(11) NOT NULL,
  `rapat_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status_kehadiran` enum('hadir','tidak_hadir','izin') DEFAULT 'tidak_hadir',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `rat_attendance`
--

CREATE TABLE `rat_attendance` (
  `id` int(11) NOT NULL,
  `rat_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `attendance_type` enum('present','proxy','absent') DEFAULT 'present',
  `proxy_holder_name` varchar(100) DEFAULT NULL,
  `proxy_holder_nik` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `rat_meetings`
--

CREATE TABLE `rat_meetings` (
  `id` int(11) NOT NULL,
  `cooperative_id` int(11) NOT NULL,
  `rat_year` year(4) NOT NULL,
  `rat_type` enum('annual','extraordinary') DEFAULT 'annual',
  `meeting_date` date NOT NULL,
  `meeting_time` time DEFAULT NULL,
  `venue` varchar(200) DEFAULT NULL,
  `agenda` text DEFAULT NULL,
  `total_members` int(11) NOT NULL,
  `quorum_required` int(11) NOT NULL,
  `members_present` int(11) DEFAULT 0,
  `proxies_present` int(11) DEFAULT 0,
  `total_attendance` int(11) DEFAULT 0,
  `quorum_achieved` tinyint(1) DEFAULT 0,
  `chairman_elected` varchar(100) DEFAULT NULL,
  `vice_chairman_elected` varchar(100) DEFAULT NULL,
  `secretary_elected` varchar(100) DEFAULT NULL,
  `treasurer_elected` varchar(100) DEFAULT NULL,
  `supervisory_board_elected` text DEFAULT NULL,
  `financial_report_approved` tinyint(1) DEFAULT 0,
  `budget_approved` tinyint(1) DEFAULT 0,
  `dividend_distribution` text DEFAULT NULL,
  `resolutions` text DEFAULT NULL,
  `minutes_document` varchar(500) DEFAULT NULL,
  `attendance_list` varchar(500) DEFAULT NULL,
  `financial_statements` varchar(500) DEFAULT NULL,
  `ministry_notification_date` date DEFAULT NULL,
  `ministry_approval_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `recommendation_engine`
--

CREATE TABLE `recommendation_engine` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_type` varchar(50) NOT NULL,
  `item_id` int(11) NOT NULL,
  `recommendation_score` decimal(5,4) NOT NULL,
  `recommendation_reason` varchar(200) DEFAULT NULL,
  `algorithm_used` varchar(50) DEFAULT NULL,
  `shown` tinyint(1) DEFAULT 0,
  `clicked` tinyint(1) DEFAULT 0,
  `purchased` tinyint(1) DEFAULT 0,
  `feedback_rating` decimal(2,1) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
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
-- Struktur dari tabel `regulatory_reports`
--

CREATE TABLE `regulatory_reports` (
  `id` int(11) NOT NULL,
  `cooperative_id` int(11) NOT NULL,
  `report_type` enum('monthly_financial','quarterly_financial','annual_financial','annual_activity','rat_minutes','membership_changes') NOT NULL,
  `report_period` varchar(20) NOT NULL,
  `report_year` year(4) NOT NULL,
  `report_quarter` int(11) DEFAULT NULL,
  `report_month` int(11) DEFAULT NULL,
  `report_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`report_data`)),
  `generated_at` datetime DEFAULT current_timestamp(),
  `submitted_to` enum('ministry_of_cooperatives','ojk','province','district') DEFAULT NULL,
  `submission_date` date DEFAULT NULL,
  `submission_reference` varchar(100) DEFAULT NULL,
  `approval_status` enum('pending','approved','rejected','revision_required') DEFAULT 'pending',
  `approval_date` date DEFAULT NULL,
  `approval_notes` text DEFAULT NULL,
  `report_file` varchar(500) DEFAULT NULL,
  `supporting_documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`supporting_documents`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `replenishment_rules`
--

CREATE TABLE `replenishment_rules` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `min_stock_level` decimal(10,2) DEFAULT 0.00,
  `max_stock_level` decimal(10,2) DEFAULT 0.00,
  `reorder_point` decimal(10,2) DEFAULT 0.00,
  `reorder_quantity` decimal(10,2) DEFAULT 0.00,
  `lead_time_days` int(11) DEFAULT 7,
  `supplier_priority` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`supplier_priority`)),
  `auto_reorder_enabled` tinyint(1) DEFAULT 0,
  `last_reorder_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `reserve_funds`
--

CREATE TABLE `reserve_funds` (
  `id` int(11) NOT NULL,
  `cooperative_id` int(11) NOT NULL,
  `fund_type` enum('reserve_fund','education_fund','welfare_fund','development_fund') NOT NULL,
  `fund_year` year(4) NOT NULL,
  `allocated_amount` decimal(12,2) NOT NULL,
  `allocation_percentage` decimal(5,2) DEFAULT NULL,
  `allocation_source` varchar(200) DEFAULT NULL,
  `utilized_amount` decimal(12,2) DEFAULT 0.00,
  `utilization_details` text DEFAULT NULL,
  `opening_balance` decimal(12,2) DEFAULT 0.00,
  `closing_balance` decimal(12,2) DEFAULT 0.00,
  `ministry_approval_required` tinyint(1) DEFAULT 1,
  `ministry_approval_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `revenue_metrics`
--

CREATE TABLE `revenue_metrics` (
  `id` int(11) NOT NULL,
  `source_category` varchar(100) NOT NULL,
  `source_subcategory` varchar(100) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'IDR',
  `transaction_count` int(11) DEFAULT 1,
  `period` date NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `risk_alerts`
--

CREATE TABLE `risk_alerts` (
  `id` int(11) NOT NULL,
  `type` enum('transaction','customer','invoice','system','compliance') NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `risk_type` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `severity` enum('low','medium','high','critical') DEFAULT 'medium',
  `entity_name` varchar(255) DEFAULT NULL,
  `status` enum('active','resolved','dismissed') DEFAULT 'active',
  `resolved_at` datetime DEFAULT NULL,
  `resolved_by` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `risk_metrics`
--

CREATE TABLE `risk_metrics` (
  `id` int(11) NOT NULL,
  `risk_type` varchar(50) NOT NULL,
  `risk_level` enum('low','medium','high','critical') DEFAULT 'low',
  `risk_score` decimal(5,2) DEFAULT 0.00,
  `mitigation_status` enum('identified','mitigating','resolved') DEFAULT 'identified',
  `affected_records` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `roi_tracking`
--

CREATE TABLE `roi_tracking` (
  `id` int(11) NOT NULL,
  `investment` decimal(15,2) NOT NULL,
  `revenue_increase` decimal(15,2) DEFAULT 0.00,
  `cost_reduction` decimal(15,2) DEFAULT 0.00,
  `period` varchar(20) NOT NULL,
  `calculated_roi` decimal(5,2) DEFAULT 0.00,
  `calculated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 1, '2026-02-14 15:00:14'),
(1, 2, '2026-02-14 15:00:14'),
(1, 3, '2026-02-14 15:00:14'),
(1, 4, '2026-02-14 15:00:14'),
(1, 5, '2026-02-14 15:00:14'),
(1, 6, '2026-02-14 15:00:14'),
(1, 7, '2026-02-14 15:00:14'),
(1, 8, '2026-02-14 15:00:14'),
(1, 9, '2026-02-14 15:00:14'),
(1, 10, '2026-02-14 15:00:14'),
(1, 11, '2026-02-14 15:00:14'),
(1, 12, '2026-02-14 15:00:14'),
(1, 13, '2026-02-14 15:00:14'),
(1, 14, '2026-02-14 15:00:14'),
(1, 15, '2026-02-14 15:00:14'),
(1, 16, '2026-02-14 15:00:14'),
(1, 17, '2026-02-14 15:00:14'),
(1, 18, '2026-02-14 15:00:14'),
(1, 19, '2026-02-14 15:00:14'),
(1, 20, '2026-02-14 15:00:14'),
(1, 21, '2026-02-14 15:00:14'),
(1, 22, '2026-02-14 15:00:14'),
(1, 23, '2026-02-14 15:00:14'),
(1, 24, '2026-02-14 15:00:14'),
(1, 25, '2026-02-14 15:00:14'),
(1, 26, '2026-02-14 15:00:14'),
(1, 27, '2026-02-14 15:00:14'),
(1, 28, '2026-02-14 15:00:14'),
(1, 29, '2026-02-14 15:00:14'),
(1, 30, '2026-02-14 15:00:14'),
(1, 31, '2026-02-14 15:00:14'),
(1, 32, '2026-02-14 15:00:14'),
(1, 33, '2026-02-14 15:00:14'),
(2, 1, '2026-02-14 15:00:14'),
(2, 2, '2026-02-14 15:00:14'),
(2, 3, '2026-02-14 15:00:14'),
(2, 4, '2026-02-14 15:00:14'),
(2, 5, '2026-02-14 15:00:14'),
(2, 6, '2026-02-14 15:00:14'),
(2, 7, '2026-02-14 15:00:14'),
(2, 8, '2026-02-14 15:00:14'),
(2, 9, '2026-02-14 15:00:14'),
(2, 10, '2026-02-14 15:00:14'),
(2, 11, '2026-02-14 15:00:14'),
(2, 12, '2026-02-14 15:00:14'),
(2, 13, '2026-02-14 15:00:14'),
(2, 14, '2026-02-14 15:00:14'),
(2, 15, '2026-02-14 15:00:14'),
(2, 16, '2026-02-14 15:00:14'),
(2, 17, '2026-02-14 15:00:14'),
(2, 18, '2026-02-14 15:00:14'),
(2, 19, '2026-02-14 15:00:14'),
(2, 20, '2026-02-14 15:00:14'),
(2, 21, '2026-02-14 15:00:14'),
(2, 22, '2026-02-14 15:00:14'),
(2, 23, '2026-02-14 15:00:14'),
(2, 24, '2026-02-14 15:00:14'),
(2, 25, '2026-02-14 15:00:14'),
(2, 26, '2026-02-14 15:00:14'),
(2, 27, '2026-02-14 15:00:14'),
(2, 28, '2026-02-14 15:00:14'),
(2, 29, '2026-02-14 15:00:14'),
(2, 30, '2026-02-14 15:00:14'),
(2, 31, '2026-02-14 15:00:14'),
(3, 1, '2026-02-14 15:00:14'),
(3, 2, '2026-02-14 15:00:14'),
(3, 6, '2026-02-14 15:00:14'),
(3, 11, '2026-02-14 15:00:14'),
(3, 15, '2026-02-14 15:00:14'),
(3, 17, '2026-02-14 15:00:14'),
(3, 21, '2026-02-14 15:00:14'),
(3, 25, '2026-02-14 15:00:14'),
(4, 1, '2026-02-14 15:00:14'),
(4, 2, '2026-02-14 15:00:14'),
(4, 3, '2026-02-14 15:00:14'),
(4, 4, '2026-02-14 15:00:14'),
(4, 6, '2026-02-14 15:00:14'),
(4, 7, '2026-02-14 15:00:14'),
(4, 10, '2026-02-14 15:00:14'),
(4, 11, '2026-02-14 15:00:14'),
(4, 12, '2026-02-14 15:00:14'),
(4, 17, '2026-02-14 15:00:14'),
(4, 18, '2026-02-14 15:00:14'),
(4, 21, '2026-02-14 15:00:14'),
(4, 22, '2026-02-14 15:00:14'),
(4, 25, '2026-02-14 15:00:14'),
(5, 1, '2026-02-14 15:00:14'),
(5, 6, '2026-02-14 15:00:14'),
(5, 10, '2026-02-14 15:00:14'),
(5, 25, '2026-02-14 15:00:14');

-- --------------------------------------------------------

--
-- Struktur dari tabel `rpa_executions`
--

CREATE TABLE `rpa_executions` (
  `id` int(11) NOT NULL,
  `process_id` int(11) NOT NULL,
  `execution_status` enum('running','completed','failed','cancelled') DEFAULT 'running',
  `started_at` datetime DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL,
  `execution_time_seconds` int(11) DEFAULT 0,
  `records_processed` int(11) DEFAULT 0,
  `errors_encountered` int(11) DEFAULT 0,
  `error_details` text DEFAULT NULL,
  `savings_achieved` decimal(15,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `rpa_processes`
--

CREATE TABLE `rpa_processes` (
  `id` int(11) NOT NULL,
  `process_name` varchar(100) NOT NULL,
  `process_category` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `trigger_condition` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`trigger_condition`)),
  `steps` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`steps`)),
  `estimated_savings` decimal(15,2) DEFAULT 0.00,
  `execution_time_seconds` int(11) DEFAULT 0,
  `success_rate` decimal(5,2) DEFAULT 0.00,
  `status` enum('active','inactive','testing') DEFAULT 'inactive',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sanksi`
--

CREATE TABLE `sanksi` (
  `id` int(11) NOT NULL,
  `jenis_sanksi` enum('teguran_lisan','teguran_tertulis','pemberhentian_sementara') NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `dasar_hukum` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `service_subscriptions`
--

CREATE TABLE `service_subscriptions` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `subscriber_coop_id` int(11) NOT NULL,
  `subscription_start` date NOT NULL,
  `subscription_end` date DEFAULT NULL,
  `billing_cycle` varchar(50) DEFAULT 'monthly',
  `monthly_fee` decimal(10,2) DEFAULT 0.00,
  `status` enum('active','suspended','cancelled','expired') DEFAULT 'active',
  `auto_renew` tinyint(1) DEFAULT 1,
  `usage_metrics` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`usage_metrics`)),
  `satisfaction_rating` decimal(3,1) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Struktur dari tabel `shared_services`
--

CREATE TABLE `shared_services` (
  `id` int(11) NOT NULL,
  `service_name` varchar(200) NOT NULL,
  `service_category` enum('accounting','legal','technology','marketing','training','procurement','logistics','hr') NOT NULL,
  `service_description` text DEFAULT NULL,
  `service_provider_id` int(11) DEFAULT NULL,
  `service_type` enum('cloud_service','consulting','outsourcing','shared_resource') DEFAULT 'cloud_service',
  `pricing_model` enum('subscription','pay_per_use','one_time','revenue_share') DEFAULT 'subscription',
  `base_price` decimal(10,2) DEFAULT 0.00,
  `pricing_unit` varchar(50) DEFAULT 'month',
  `minimum_commitment` int(11) DEFAULT 1,
  `availability_status` enum('available','limited','unavailable') DEFAULT 'available',
  `max_subscribers` int(11) DEFAULT NULL,
  `current_subscribers` int(11) DEFAULT 0,
  `service_level_agreement` text DEFAULT NULL,
  `technical_requirements` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`technical_requirements`)),
  `status` enum('active','inactive','deprecated') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `shipments`
--

CREATE TABLE `shipments` (
  `id` int(11) NOT NULL,
  `shipment_number` varchar(20) NOT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `carrier_name` varchar(100) DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `shipment_date` datetime DEFAULT NULL,
  `estimated_delivery` date DEFAULT NULL,
  `actual_delivery` datetime DEFAULT NULL,
  `origin_address` text DEFAULT NULL,
  `destination_address` text DEFAULT NULL,
  `shipping_cost` decimal(10,2) DEFAULT 0.00,
  `status` enum('pending','in_transit','delivered','delayed','lost','returned') DEFAULT 'pending',
  `weight_kg` decimal(8,2) DEFAULT NULL,
  `dimensions` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `shipment_items`
--

CREATE TABLE `shipment_items` (
  `id` int(11) NOT NULL,
  `shipment_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_weight` decimal(5,2) DEFAULT NULL,
  `quality_check_status` enum('pending','passed','failed') DEFAULT 'pending',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `shipping_details`
--

CREATE TABLE `shipping_details` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `courier` varchar(50) NOT NULL,
  `service` varchar(100) NOT NULL,
  `cost` decimal(10,2) NOT NULL,
  `estimated_days` varchar(50) DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 'JASA_MODAL_ANGGOTA', 'Jasa Modal Anggota', 'jasa_modal', 40.00, 'Bagian SHU untuk anggota berdasarkan simpanan', 1, '2026-02-12 20:53:43'),
(2, 'JASA_MODAL_PENGURUS', 'Jasa Modal Pengurus', 'jasa_modal', 5.00, 'Bagian SHU untuk modal pengurus koperasi', 1, '2026-02-12 20:53:43'),
(3, 'JASA_USHA_ANGGOTA', 'Jasa Usaha Anggota', 'jasa_usaha', 35.00, 'Bagian SHU dari transaksi dengan anggota', 1, '2026-02-12 20:53:43'),
(4, 'JASA_USHA_KOPERASI', 'Jasa Usaha Koperasi', 'jasa_usaha', 15.00, 'Bagian SHU dari usaha langsung koperasi', 1, '2026-02-12 20:53:43'),
(5, 'PENDIDIKAN', 'Pendidikan Sosial', 'pendidikan_sosial', 3.00, 'Dana pendidikan anggota dan masyarakat', 1, '2026-02-12 20:53:43'),
(6, 'HONORARIUM_PENGURUS', 'Honorarium Pengurus', 'honorarium', 2.00, 'Honorarium untuk pengurus aktif', 1, '2026-02-12 20:53:43'),
(7, 'DANA_CADANGAN', 'Dana Cadangan', 'lainnya', 5.00, 'Dana cadangan resiko dan pengembangan', 1, '2026-02-12 20:53:43');

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
-- Struktur dari tabel `smart_contracts`
--

CREATE TABLE `smart_contracts` (
  `id` int(11) NOT NULL,
  `contract_name` varchar(100) NOT NULL,
  `contract_type` enum('loan_agreement','savings_contract','governance_token','reward_system') NOT NULL,
  `contract_address` varchar(100) DEFAULT NULL,
  `abi` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`abi`)),
  `bytecode` text DEFAULT NULL,
  `deployed_network` varchar(50) DEFAULT 'polygon',
  `deployer_address` varchar(100) DEFAULT NULL,
  `deployment_tx_hash` varchar(100) DEFAULT NULL,
  `status` enum('draft','deployed','active','paused','deprecated') DEFAULT 'draft',
  `version` varchar(20) DEFAULT '1.0.0',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `status_types`
--

CREATE TABLE `status_types` (
  `id` int(11) NOT NULL,
  `category` varchar(50) NOT NULL,
  `status_code` varchar(50) NOT NULL,
  `status_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
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
-- Struktur dari tabel `supplier_performance`
--

CREATE TABLE `supplier_performance` (
  `id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `evaluation_period` varchar(20) NOT NULL,
  `evaluation_date` date NOT NULL,
  `on_time_delivery_rate` decimal(5,2) DEFAULT 0.00,
  `quality_rating` decimal(3,1) DEFAULT 0.0,
  `responsiveness_rating` decimal(3,1) DEFAULT 0.0,
  `price_competitiveness` decimal(3,1) DEFAULT 0.0,
  `overall_score` decimal(5,2) DEFAULT 0.00,
  `improvement_areas` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `supply_chain_alerts`
--

CREATE TABLE `supply_chain_alerts` (
  `id` int(11) NOT NULL,
  `alert_type` varchar(50) NOT NULL,
  `severity` enum('low','medium','high','critical') DEFAULT 'medium',
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `suggested_actions` text DEFAULT NULL,
  `acknowledged` tinyint(1) DEFAULT 0,
  `acknowledged_by` int(11) DEFAULT NULL,
  `acknowledged_at` datetime DEFAULT NULL,
  `resolved` tinyint(1) DEFAULT 0,
  `resolved_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `system_alerts`
--

CREATE TABLE `system_alerts` (
  `id` int(11) NOT NULL,
  `alert_type` varchar(50) NOT NULL,
  `alert_level` enum('info','warning','critical') DEFAULT 'info',
  `message` text NOT NULL,
  `resolved` tinyint(1) DEFAULT 0,
  `resolved_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `system_health_checks`
--

CREATE TABLE `system_health_checks` (
  `id` int(11) NOT NULL,
  `check_name` varchar(100) NOT NULL,
  `check_category` varchar(50) NOT NULL,
  `last_check` timestamp NULL DEFAULT NULL,
  `next_check` timestamp NULL DEFAULT NULL,
  `status` enum('passing','warning','failing') DEFAULT 'passing',
  `response_time` decimal(8,2) DEFAULT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `system_learning_patterns`
--

CREATE TABLE `system_learning_patterns` (
  `id` int(11) NOT NULL,
  `pattern_type` varchar(100) NOT NULL,
  `pattern_description` text DEFAULT NULL,
  `trigger_conditions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`trigger_conditions`)),
  `learned_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`learned_response`)),
  `success_rate` decimal(5,2) DEFAULT 0.00,
  `usage_count` int(11) DEFAULT 0,
  `last_used` datetime DEFAULT NULL,
  `confidence_score` decimal(5,4) DEFAULT 0.0000,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `system_metrics`
--

CREATE TABLE `system_metrics` (
  `id` int(11) NOT NULL,
  `metric_type` varchar(50) NOT NULL,
  `metric_value` decimal(10,2) DEFAULT 0.00,
  `duration` decimal(10,2) DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `system_monitoring`
--

CREATE TABLE `system_monitoring` (
  `id` int(11) NOT NULL,
  `check_type` enum('database','application','performance','security','storage') NOT NULL,
  `check_name` varchar(100) NOT NULL,
  `status` enum('healthy','warning','critical','error') DEFAULT 'healthy',
  `response_time` decimal(8,2) DEFAULT NULL,
  `memory_usage` decimal(10,2) DEFAULT NULL,
  `cpu_usage` decimal(5,2) DEFAULT NULL,
  `disk_usage` decimal(5,2) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `checked_at` timestamp NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `system_uptime`
--

CREATE TABLE `system_uptime` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `uptime_percentage` decimal(5,2) DEFAULT 100.00,
  `downtime_minutes` int(11) DEFAULT 0,
  `incidents_count` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tax_filings`
--

CREATE TABLE `tax_filings` (
  `id` int(11) NOT NULL,
  `tax_type` enum('pph21','pph23','pph25','annual_return') NOT NULL,
  `period` varchar(7) NOT NULL,
  `tax_amount` decimal(15,2) NOT NULL,
  `filing_date` date NOT NULL,
  `status` enum('draft','filed','approved','rejected') DEFAULT 'draft',
  `reference_number` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `filed_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tax_payments`
--

CREATE TABLE `tax_payments` (
  `id` int(11) NOT NULL,
  `tax_type` enum('pph21','pph23','pph25','pph_final') NOT NULL,
  `period` varchar(7) NOT NULL,
  `payment_amount` decimal(15,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `bank_reference` varchar(100) DEFAULT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `paid_by` int(11) DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `token_economics`
--

CREATE TABLE `token_economics` (
  `id` int(11) NOT NULL,
  `token_symbol` varchar(20) NOT NULL,
  `economic_model` varchar(50) NOT NULL,
  `total_supply` decimal(20,8) NOT NULL,
  `circulating_supply` decimal(20,8) DEFAULT 0.00000000,
  `staking_rewards` decimal(5,2) DEFAULT 0.00,
  `transaction_fees` decimal(5,2) DEFAULT 0.00,
  `burn_rate` decimal(5,2) DEFAULT 0.00,
  `vesting_schedule` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`vesting_schedule`)),
  `distribution_schedule` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`distribution_schedule`)),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `token_staking`
--

CREATE TABLE `token_staking` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `wallet_address` varchar(100) NOT NULL,
  `staked_amount` decimal(20,8) NOT NULL,
  `staking_start` datetime DEFAULT current_timestamp(),
  `staking_end` datetime DEFAULT NULL,
  `reward_rate` decimal(5,2) NOT NULL,
  `accumulated_rewards` decimal(20,8) DEFAULT 0.00000000,
  `status` enum('active','unstaking','completed') DEFAULT 'active',
  `unstaking_tx_hash` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `trade_items`
--

CREATE TABLE `trade_items` (
  `id` int(11) NOT NULL,
  `trade_id` int(11) NOT NULL,
  `item_name` varchar(200) NOT NULL,
  `item_description` text DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(12,2) NOT NULL,
  `specifications` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`specifications`)),
  `quality_standards` varchar(200) DEFAULT NULL,
  `delivery_schedule` date DEFAULT NULL,
  `status` enum('pending','in_transit','delivered','accepted','rejected') DEFAULT 'pending',
  `quality_check_result` enum('pending','passed','failed') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaction_metrics`
--

CREATE TABLE `transaction_metrics` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `transaction_type` varchar(50) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `processing_time` decimal(5,2) DEFAULT 0.00,
  `success` tinyint(1) DEFAULT 1,
  `error_message` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
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
    WHERE id = NEW.simpanan_id$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `transparency_logs`
--

CREATE TABLE `transparency_logs` (
  `id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `old_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_data`)),
  `new_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_data`)),
  `recorded_by` int(11) DEFAULT NULL,
  `recorded_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 'admin', '$2y$10$puqQ47Z6i/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m/il.UeIdYvu', 'admin@ksp_samosir.com', 'Administrator', 1, 0, NULL, 1, '2026-02-13 10:54:36', '2026-02-12 20:02:24', '2026-02-13 03:54:36'),
(2, 'staff', '$2y$10$puqQ47Z6i/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m/il.UeIdYvu', 'staff@ksp_samosir.com', 'Staff User', 4, 0, NULL, 1, '2026-02-13 03:35:08', '2026-02-12 20:34:31', '2026-02-12 20:37:55'),
(3, 'member', '$2y$10$puqQ47Z6i/JkHQFpOj7GUOPCuW5o1AC4GMJgLRC08m/il.UeIdYvu', 'member@ksp_samosir.com', 'Member User', 5, 0, NULL, 1, '2026-02-13 03:48:06', '2026-02-12 20:34:34', '2026-02-12 20:48:06');

-- --------------------------------------------------------

--
-- Struktur dari tabel `user_activities`
--

CREATE TABLE `user_activities` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity_type` varchar(100) NOT NULL,
  `activity_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`activity_data`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `user_behavior_analytics`
--

CREATE TABLE `user_behavior_analytics` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `behavior_type` varchar(50) NOT NULL,
  `behavior_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`behavior_data`)),
  `session_context` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`session_context`)),
  `device_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`device_info`)),
  `location_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`location_data`)),
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `user_profiles`
--

CREATE TABLE `user_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `profile_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`profile_data`)),
  `preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferences`)),
  `behavior_patterns` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`behavior_patterns`)),
  `risk_profile` varchar(50) DEFAULT NULL,
  `engagement_score` decimal(5,2) DEFAULT 0.00,
  `last_updated` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Struktur dari tabel `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `session_start` datetime DEFAULT current_timestamp(),
  `last_activity` datetime DEFAULT current_timestamp(),
  `session_duration` int(11) DEFAULT 0,
  `page_views` int(11) DEFAULT 0,
  `device_type` enum('desktop','mobile','tablet') DEFAULT 'desktop',
  `browser` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ux_experiment_results`
--

CREATE TABLE `ux_experiment_results` (
  `id` int(11) NOT NULL,
  `experiment_id` int(11) NOT NULL,
  `design_variant` varchar(10) NOT NULL,
  `unique_visitors` int(11) DEFAULT 0,
  `total_interactions` int(11) DEFAULT 0,
  `conversion_events` int(11) DEFAULT 0,
  `average_session_duration` decimal(5,2) DEFAULT NULL,
  `bounce_rate` decimal(5,2) DEFAULT NULL,
  `measured_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ux_optimization_experiments`
--

CREATE TABLE `ux_optimization_experiments` (
  `id` int(11) NOT NULL,
  `experiment_name` varchar(200) NOT NULL,
  `page_url` varchar(500) NOT NULL,
  `element_selector` varchar(500) NOT NULL,
  `original_design` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`original_design`)),
  `optimized_design` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`optimized_design`)),
  `target_metric` varchar(100) NOT NULL,
  `traffic_percentage` decimal(5,2) DEFAULT 50.00,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `status` enum('draft','running','completed','cancelled') DEFAULT 'draft',
  `winner_design` varchar(10) DEFAULT NULL,
  `improvement_score` decimal(5,2) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `vendors`
--

CREATE TABLE `vendors` (
  `id` int(11) NOT NULL,
  `vendor_code` varchar(20) NOT NULL,
  `vendor_name` varchar(200) NOT NULL,
  `vendor_type` enum('supplier','service_provider','manufacturer') DEFAULT 'supplier',
  `contact_person` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `payment_terms` varchar(100) DEFAULT NULL,
  `credit_limit` decimal(15,2) DEFAULT 0.00,
  `current_balance` decimal(15,2) DEFAULT 0.00,
  `performance_rating` decimal(3,1) DEFAULT 5.0,
  `contract_start_date` date DEFAULT NULL,
  `contract_end_date` date DEFAULT NULL,
  `status` enum('active','inactive','suspended','blacklisted') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Struktur dari tabel `warehouses`
--

CREATE TABLE `warehouses` (
  `id` int(11) NOT NULL,
  `warehouse_code` varchar(20) NOT NULL,
  `warehouse_name` varchar(100) NOT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `capacity_sqft` decimal(10,2) DEFAULT NULL,
  `current_utilization` decimal(5,2) DEFAULT 0.00,
  `status` enum('active','inactive','maintenance') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `warehouse_zones`
--

CREATE TABLE `warehouse_zones` (
  `id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `zone_code` varchar(20) NOT NULL,
  `zone_name` varchar(100) NOT NULL,
  `zone_type` enum('storage','picking','shipping','receiving','damaged') DEFAULT 'storage',
  `capacity` decimal(10,2) DEFAULT NULL,
  `current_usage` decimal(10,2) DEFAULT 0.00,
  `temperature_controlled` tinyint(1) DEFAULT 0,
  `security_level` enum('low','medium','high') DEFAULT 'low'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `withholding_tax`
--

CREATE TABLE `withholding_tax` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `recipient_type` enum('supplier','service_provider','other') NOT NULL,
  `recipient_name` varchar(255) NOT NULL,
  `recipient_npwp` varchar(20) DEFAULT NULL,
  `gross_amount` decimal(15,2) NOT NULL,
  `tax_rate` decimal(5,2) NOT NULL,
  `tax_amount` decimal(15,2) NOT NULL,
  `net_amount` decimal(15,2) NOT NULL,
  `tax_type` enum('pph21','pph23','pph26','pph4_2') NOT NULL,
  `transaction_date` date NOT NULL,
  `reported` tinyint(1) DEFAULT 0,
  `reported_date` date DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Indeks untuk tabel `ab_tests`
--
ALTER TABLE `ab_tests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_name_status` (`test_name`,`status`);

--
-- Indeks untuk tabel `ab_test_participants`
--
ALTER TABLE `ab_test_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_participation` (`test_id`,`user_id`),
  ADD KEY `idx_test` (`test_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_variant` (`assigned_variant`);

--
-- Indeks untuk tabel `ab_test_results`
--
ALTER TABLE `ab_test_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_test_variant` (`test_id`,`variant`);

--
-- Indeks untuk tabel `access_logs`
--
ALTER TABLE `access_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_ip` (`user_id`,`ip_address`),
  ADD KEY `idx_response_code` (`response_code`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indeks untuk tabel `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `address_stats`
--
ALTER TABLE `address_stats`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `ai_conversations`
--
ALTER TABLE `ai_conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_session` (`session_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_started_at` (`started_at`);

--
-- Indeks untuk tabel `ai_decisions`
--
ALTER TABLE `ai_decisions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type_status` (`decision_type`,`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indeks untuk tabel `ai_ethics_monitoring`
--
ALTER TABLE `ai_ethics_monitoring`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_model` (`model_name`),
  ADD KEY `idx_type` (`bias_check_type`),
  ADD KEY `idx_status` (`compliance_status`),
  ADD KEY `idx_checked` (`checked_at`);

--
-- Indeks untuk tabel `ai_fraud_alerts`
--
ALTER TABLE `ai_fraud_alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resolved_by` (`resolved_by`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_ai_fraud_alerts_type` (`alert_type`),
  ADD KEY `idx_ai_fraud_alerts_risk` (`risk_level`),
  ADD KEY `idx_ai_fraud_alerts_status` (`status`),
  ADD KEY `idx_ai_fraud_alerts_created` (`created_at`);

--
-- Indeks untuk tabel `ai_fraud_models`
--
ALTER TABLE `ai_fraud_models`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`model_type`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `ai_messages`
--
ALTER TABLE `ai_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_conversation` (`conversation_id`),
  ADD KEY `idx_sender` (`sender`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indeks untuk tabel `ai_model_data`
--
ALTER TABLE `ai_model_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ai_model_data_type` (`model_type`);

--
-- Indeks untuk tabel `ai_model_performance`
--
ALTER TABLE `ai_model_performance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_model` (`model_name`),
  ADD KEY `idx_metric` (`metric_type`),
  ADD KEY `idx_recorded` (`recorded_at`);

--
-- Indeks untuk tabel `ai_recommendations`
--
ALTER TABLE `ai_recommendations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ai_recommendations_user` (`user_id`),
  ADD KEY `idx_ai_recommendations_product` (`product_id`),
  ADD KEY `idx_ai_recommendations_created` (`created_at`);

--
-- Indeks untuk tabel `ai_training_data`
--
ALTER TABLE `ai_training_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_intent` (`intent`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indeks untuk tabel `alerts`
--
ALTER TABLE `alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_alerts_status` (`status`,`created_at`);

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
  ADD KEY `idx_anggota_village` (`village_id`),
  ADD KEY `address_id` (`address_id`),
  ADD KEY `primary_contact_id` (`primary_contact_id`),
  ADD KEY `idx_anggota_search` (`nama_lengkap`,`no_anggota`),
  ADD KEY `idx_anggota_created_at` (`created_at`),
  ADD KEY `idx_anggota_province_status` (`province_id`,`status`);

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
-- Indeks untuk tabel `application_logs`
--
ALTER TABLE `application_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_application_logs_level` (`log_level`,`logged_at`),
  ADD KEY `idx_application_logs_category` (`category`,`logged_at`);

--
-- Indeks untuk tabel `app_sessions`
--
ALTER TABLE `app_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_device` (`user_id`,`device_id`),
  ADD KEY `idx_platform` (`platform`);

--
-- Indeks untuk tabel `asset_depreciation`
--
ALTER TABLE `asset_depreciation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_asset_depreciation_asset_id` (`asset_id`),
  ADD KEY `idx_asset_depreciation_date` (`depreciation_date`);

--
-- Indeks untuk tabel `asset_disposals`
--
ALTER TABLE `asset_disposals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `recorded_by` (`recorded_by`),
  ADD KEY `idx_asset_disposals_asset_id` (`asset_id`),
  ADD KEY `idx_asset_disposals_date` (`disposal_date`);

--
-- Indeks untuk tabel `asset_holdings`
--
ALTER TABLE `asset_holdings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_holding` (`wallet_id`,`asset_id`),
  ADD KEY `idx_wallet` (`wallet_id`),
  ADD KEY `idx_asset` (`asset_id`);

--
-- Indeks untuk tabel `asset_maintenance`
--
ALTER TABLE `asset_maintenance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recorded_by` (`recorded_by`),
  ADD KEY `idx_asset_maintenance_asset_id` (`asset_id`),
  ADD KEY `idx_asset_maintenance_date` (`maintenance_date`),
  ADD KEY `idx_asset_maintenance_type` (`maintenance_type`);

--
-- Indeks untuk tabel `automated_processes`
--
ALTER TABLE `automated_processes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type_status` (`process_type`,`status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_automated_recent` (`created_at`);

--
-- Indeks untuk tabel `b2b_partners`
--
ALTER TABLE `b2b_partners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`partner_type`),
  ADD KEY `idx_status` (`integration_status`),
  ADD KEY `idx_last_sync` (`last_sync`);

--
-- Indeks untuk tabel `backup_files`
--
ALTER TABLE `backup_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_backup_files_created_at` (`created_at`),
  ADD KEY `idx_backup_files_type` (`type`);

--
-- Indeks untuk tabel `backup_logs`
--
ALTER TABLE `backup_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `performed_by` (`performed_by`),
  ADD KEY `idx_backup_logs_action` (`action`),
  ADD KEY `idx_backup_logs_status` (`status`),
  ADD KEY `idx_backup_logs_performed_at` (`performed_at`);

--
-- Indeks untuk tabel `backup_schedules`
--
ALTER TABLE `backup_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_backup_schedules_enabled` (`enabled`),
  ADD KEY `idx_backup_schedules_next_run` (`next_run`);

--
-- Indeks untuk tabel `blockchain_blocks`
--
ALTER TABLE `blockchain_blocks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recorded_by` (`recorded_by`),
  ADD KEY `idx_blockchain_blocks_type` (`block_type`),
  ADD KEY `idx_blockchain_blocks_created` (`created_at`),
  ADD KEY `idx_blockchain_blocks_hash` (`current_hash`);

--
-- Indeks untuk tabel `blockchain_oracles`
--
ALTER TABLE `blockchain_oracles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`oracle_type`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_last_update` (`last_update`);

--
-- Indeks untuk tabel `blockchain_transactions`
--
ALTER TABLE `blockchain_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_hash` (`transaction_hash`),
  ADD KEY `idx_hash` (`transaction_hash`),
  ADD KEY `idx_from` (`from_address`),
  ADD KEY `idx_to` (`to_address`),
  ADD KEY `idx_type` (`transaction_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_reference` (`reference_type`,`reference_id`),
  ADD KEY `idx_confirmed` (`confirmed_at`);

--
-- Indeks untuk tabel `block_verifications`
--
ALTER TABLE `block_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `verified_by` (`verified_by`),
  ADD KEY `idx_block_verifications_block` (`block_id`),
  ADD KEY `idx_block_verifications_status` (`verification_status`);

--
-- Indeks untuk tabel `bridge_transactions`
--
ALTER TABLE `bridge_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_bridge` (`bridge_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_source_tx` (`source_tx_hash`),
  ADD KEY `idx_target_tx` (`target_tx_hash`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `buku_besar`
--
ALTER TABLE `buku_besar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `coa_id` (`coa_id`),
  ADD KEY `jurnal_detail_id` (`jurnal_detail_id`);

--
-- Indeks untuk tabel `business_metrics`
--
ALTER TABLE `business_metrics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category_period` (`metric_category`,`period_start`,`period_end`);

--
-- Indeks untuk tabel `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_category_id` (`parent_category_id`);

--
-- Indeks untuk tabel `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `enrollment_id` (`enrollment_id`),
  ADD UNIQUE KEY `certificate_number` (`certificate_number`),
  ADD UNIQUE KEY `verification_code` (`verification_code`),
  ADD KEY `issued_by` (`issued_by`),
  ADD KEY `idx_certificates_enrollment` (`enrollment_id`),
  ADD KEY `idx_certificates_number` (`certificate_number`);

--
-- Indeks untuk tabel `chain_bridges`
--
ALTER TABLE `chain_bridges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_chains` (`source_chain`,`target_chain`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `coa`
--
ALTER TABLE `coa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_coa` (`kode_coa`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indeks untuk tabel `collection_actions`
--
ALTER TABLE `collection_actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_collection` (`collection_id`),
  ADD KEY `idx_type` (`action_type`),
  ADD KEY `idx_status` (`action_status`),
  ADD KEY `idx_scheduled` (`scheduled_date`);

--
-- Indeks untuk tabel `collection_automation`
--
ALTER TABLE `collection_automation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_loan` (`loan_id`),
  ADD KEY `idx_member` (`member_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_priority` (`priority_level`),
  ADD KEY `idx_next_action` (`next_action_date`);

--
-- Indeks untuk tabel `collection_templates`
--
ALTER TABLE `collection_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`template_type`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_overdue_range` (`overdue_days_min`,`overdue_days_max`);

--
-- Indeks untuk tabel `community_activities`
--
ALTER TABLE `community_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_type` (`user_id`,`activity_type`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indeks untuk tabel `compliance_audits`
--
ALTER TABLE `compliance_audits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cooperative` (`cooperative_id`),
  ADD KEY `idx_type` (`audit_type`),
  ADD KEY `idx_result` (`audit_result`),
  ADD KEY `idx_status` (`action_status`),
  ADD KEY `idx_audits_cooperative_type` (`cooperative_id`,`audit_type`);

--
-- Indeks untuk tabel `compliance_checks`
--
ALTER TABLE `compliance_checks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_compliance_checks_type` (`check_type`),
  ADD KEY `idx_compliance_checks_status` (`status`);

--
-- Indeks untuk tabel `compliance_metrics`
--
ALTER TABLE `compliance_metrics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_regulation_status` (`regulation`,`compliance_status`);

--
-- Indeks untuk tabel `compliance_monitoring`
--
ALTER TABLE `compliance_monitoring`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_governance` (`governance_id`),
  ADD KEY `idx_cooperative` (`cooperative_id`),
  ADD KEY `idx_status` (`compliance_status`),
  ADD KEY `idx_assessment` (`assessment_date`),
  ADD KEY `idx_action_status` (`action_status`);

--
-- Indeks untuk tabel `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `contract_interactions`
--
ALTER TABLE `contract_interactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_contract` (`contract_id`),
  ADD KEY `idx_type` (`interaction_type`),
  ADD KEY `idx_hash` (`transaction_hash`),
  ADD KEY `idx_executed` (`executed_at`);

--
-- Indeks untuk tabel `contract_templates`
--
ALTER TABLE `contract_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`template_type`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indeks untuk tabel `conversion_metrics`
--
ALTER TABLE `conversion_metrics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_session_type` (`session_id`,`conversion_type`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indeks untuk tabel `cooperative_accounts`
--
ALTER TABLE `cooperative_accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_account_id` (`parent_account_id`),
  ADD KEY `idx_cooperative` (`cooperative_id`),
  ADD KEY `idx_type` (`account_type`),
  ADD KEY `idx_code` (`account_code`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_accounts_cooperative_type` (`cooperative_id`,`account_type`,`is_active`);

--
-- Indeks untuk tabel `cooperative_activities`
--
ALTER TABLE `cooperative_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cooperative` (`cooperative_id`),
  ADD KEY `idx_type` (`activity_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_date` (`start_date`,`end_date`),
  ADD KEY `idx_activities_cooperative_type` (`cooperative_id`,`activity_type`,`status`);

--
-- Indeks untuk tabel `cooperative_members`
--
ALTER TABLE `cooperative_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `member_number` (`member_number`),
  ADD UNIQUE KEY `nik` (`nik`),
  ADD KEY `idx_cooperative` (`cooperative_id`),
  ADD KEY `idx_status` (`membership_status`),
  ADD KEY `idx_membership_date` (`membership_date`),
  ADD KEY `idx_kyc` (`kyc_status`),
  ADD KEY `idx_members_cooperative` (`cooperative_id`,`membership_status`);

--
-- Indeks untuk tabel `cooperative_network`
--
ALTER TABLE `cooperative_network`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cooperative_code` (`cooperative_code`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_joined_at` (`joined_at`);

--
-- Indeks untuk tabel `cooperative_structure`
--
ALTER TABLE `cooperative_structure`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cooperative_code` (`cooperative_code`),
  ADD KEY `idx_status` (`operational_status`),
  ADD KEY `idx_compliance` (`compliance_status`),
  ADD KEY `idx_sector` (`business_sector`),
  ADD KEY `idx_type` (`cooperative_type`);

--
-- Indeks untuk tabel `course_modules`
--
ALTER TABLE `course_modules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_course_modules_course` (`course_id`),
  ADD KEY `idx_course_modules_order` (`module_order`);

--
-- Indeks untuk tabel `course_ratings`
--
ALTER TABLE `course_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_rating` (`course_id`,`student_id`),
  ADD KEY `idx_course_ratings_course` (`course_id`),
  ADD KEY `idx_course_ratings_student` (`student_id`);

--
-- Indeks untuk tabel `credit_scores`
--
ALTER TABLE `credit_scores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member` (`member_id`),
  ADD KEY `idx_score` (`credit_score`),
  ADD KEY `idx_risk_level` (`risk_level`),
  ADD KEY `idx_calculated_at` (`calculated_at`);

--
-- Indeks untuk tabel `credit_score_factors`
--
ALTER TABLE `credit_score_factors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`factor_category`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indeks untuk tabel `credit_scoring_models`
--
ALTER TABLE `credit_scoring_models`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_version` (`model_version`);

--
-- Indeks untuk tabel `cross_border_payments`
--
ALTER TABLE `cross_border_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_reference` (`payment_reference`),
  ADD KEY `idx_sender` (`sender_coop_id`),
  ADD KEY `idx_receiver` (`receiver_coop_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_compliance` (`compliance_status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indeks untuk tabel `customer_invoices`
--
ALTER TABLE `customer_invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indeks untuk tabel `decision_rules`
--
ALTER TABLE `decision_rules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`rule_category`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indeks untuk tabel `demand_forecasts`
--
ALTER TABLE `demand_forecasts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_period` (`forecast_period`),
  ADD KEY `idx_date` (`forecast_date`),
  ADD KEY `idx_forecasts_product_period` (`product_id`,`forecast_period`);

--
-- Indeks untuk tabel `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `department_code` (`department_code`);

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
-- Indeks untuk tabel `digital_assets`
--
ALTER TABLE `digital_assets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_symbol` (`asset_symbol`),
  ADD KEY `idx_type` (`asset_type`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indeks untuk tabel `digital_products`
--
ALTER TABLE `digital_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`product_type`),
  ADD KEY `idx_provider` (`provider_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `digital_wallets`
--
ALTER TABLE `digital_wallets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wallet_address` (`wallet_address`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_address` (`wallet_address`),
  ADD KEY `idx_verified` (`is_verified`);

--
-- Indeks untuk tabel `education_fund_utilization`
--
ALTER TABLE `education_fund_utilization`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cooperative` (`cooperative_id`),
  ADD KEY `idx_year` (`utilization_year`),
  ADD KEY `idx_type` (`program_type`),
  ADD KEY `idx_status` (`completion_status`);

--
-- Indeks untuk tabel `efficiency_metrics`
--
ALTER TABLE `efficiency_metrics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_process_date` (`process_name`,`measured_at`);

--
-- Indeks untuk tabel `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD KEY `supervisor_id` (`supervisor_id`),
  ADD KEY `idx_employees_employee_id` (`employee_id`),
  ADD KEY `idx_employees_status` (`status`),
  ADD KEY `idx_employees_department` (`department`),
  ADD KEY `address_id` (`address_id`),
  ADD KEY `primary_contact_id` (`primary_contact_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `position_id` (`position_id`);

--
-- Indeks untuk tabel `employee_attendance`
--
ALTER TABLE `employee_attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recorded_by` (`recorded_by`),
  ADD KEY `idx_employee_attendance_employee_id` (`employee_id`),
  ADD KEY `idx_employee_attendance_date` (`attendance_date`);

--
-- Indeks untuk tabel `entity_addresses`
--
ALTER TABLE `entity_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_entity_addresses_entity_type_id` (`entity_type`,`entity_id`),
  ADD KEY `idx_entity_addresses_address_id` (`address_id`);

--
-- Indeks untuk tabel `entity_contacts`
--
ALTER TABLE `entity_contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contact_id` (`contact_id`);

--
-- Indeks untuk tabel `error_logs`
--
ALTER TABLE `error_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_level_date` (`error_level`,`created_at`),
  ADD KEY `idx_user_error` (`user_id`,`error_level`),
  ADD KEY `idx_errors_recent` (`created_at`);

--
-- Indeks untuk tabel `financial_periods`
--
ALTER TABLE `financial_periods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cooperative` (`cooperative_id`),
  ADD KEY `idx_year` (`period_year`),
  ADD KEY `idx_period` (`period_type`,`period_number`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `fixed_assets`
--
ALTER TABLE `fixed_assets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `asset_code` (`asset_code`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_fixed_assets_asset_code` (`asset_code`),
  ADD KEY `idx_fixed_assets_category` (`category`),
  ADD KEY `idx_fixed_assets_condition` (`condition_status`),
  ADD KEY `idx_fixed_assets_location` (`location`);

--
-- Indeks untuk tabel `fraud_alerts`
--
ALTER TABLE `fraud_alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`alert_type`),
  ADD KEY `idx_severity` (`severity`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_transaction` (`transaction_id`),
  ADD KEY `idx_investigated` (`investigated`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indeks untuk tabel `fraud_patterns`
--
ALTER TABLE `fraud_patterns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`pattern_type`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indeks untuk tabel `governance_bodies`
--
ALTER TABLE `governance_bodies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cooperative` (`cooperative_id`),
  ADD KEY `idx_type` (`body_type`),
  ADD KEY `idx_member` (`member_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_governance_cooperative_type` (`cooperative_id`,`body_type`,`status`);

--
-- Indeks untuk tabel `governance_delegates`
--
ALTER TABLE `governance_delegates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_delegation` (`delegator_id`,`delegate_id`),
  ADD KEY `idx_delegator` (`delegator_id`),
  ADD KEY `idx_delegate` (`delegate_id`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indeks untuk tabel `governance_proposals`
--
ALTER TABLE `governance_proposals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_proposer` (`proposer_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_voting` (`voting_start`,`voting_end`);

--
-- Indeks untuk tabel `governance_votes`
--
ALTER TABLE `governance_votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_vote` (`proposal_id`,`voter_id`),
  ADD KEY `idx_proposal` (`proposal_id`),
  ADD KEY `idx_voter` (`voter_id`),
  ADD KEY `idx_choice` (`vote_choice`),
  ADD KEY `idx_voted` (`voted_at`);

--
-- Indeks untuk tabel `improvement_initiatives`
--
ALTER TABLE `improvement_initiatives`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_assigned` (`assigned_to`);

--
-- Indeks untuk tabel `improvement_measurements`
--
ALTER TABLE `improvement_measurements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_initiative` (`initiative_id`),
  ADD KEY `idx_metric` (`metric_name`),
  ADD KEY `idx_date` (`measurement_date`);

--
-- Indeks untuk tabel `international_opportunities`
--
ALTER TABLE `international_opportunities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_country` (`target_country`),
  ADD KEY `idx_type` (`opportunity_type`),
  ADD KEY `idx_risk` (`risk_level`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `international_partnerships`
--
ALTER TABLE `international_partnerships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_local_coop` (`local_coop_id`),
  ADD KEY `idx_country` (`partner_country`),
  ADD KEY `idx_type` (`partnership_type`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `inter_coop_trades`
--
ALTER TABLE `inter_coop_trades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `trade_reference` (`trade_reference`),
  ADD KEY `idx_seller` (`seller_coop_id`),
  ADD KEY `idx_buyer` (`buyer_coop_id`),
  ADD KEY `idx_type` (`trade_type`),
  ADD KEY `idx_status` (`trade_status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indeks untuk tabel `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_warehouse` (`warehouse_id`),
  ADD KEY `idx_batch` (`batch_number`),
  ADD KEY `idx_quality` (`quality_status`),
  ADD KEY `idx_expiry` (`expiry_date`),
  ADD KEY `idx_inventory_product` (`product_id`);

--
-- Indeks untuk tabel `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`transaction_type`),
  ADD KEY `idx_reference` (`reference_type`,`reference_id`),
  ADD KEY `idx_date` (`transaction_date`),
  ADD KEY `idx_inventory_transactions_item` (`inventory_item_id`);

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
-- Indeks untuk tabel `journal_entries`
--
ALTER TABLE `journal_entries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `entry_number` (`entry_number`),
  ADD KEY `idx_cooperative` (`cooperative_id`),
  ADD KEY `idx_date` (`entry_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_reference` (`reference_type`,`reference_id`),
  ADD KEY `idx_journal_cooperative_date` (`cooperative_id`,`entry_date`,`status`);

--
-- Indeks untuk tabel `journal_lines`
--
ALTER TABLE `journal_lines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_journal` (`journal_entry_id`),
  ADD KEY `idx_account` (`account_id`);

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
-- Indeks untuk tabel `knowledge_base`
--
ALTER TABLE `knowledge_base`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_coop_id` (`author_coop_id`),
  ADD KEY `idx_type` (`content_type`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_featured` (`is_featured`),
  ADD KEY `idx_access` (`access_level`),
  ADD KEY `idx_language` (`language`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indeks untuk tabel `knowledge_ratings`
--
ALTER TABLE `knowledge_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_rating` (`knowledge_id`,`user_id`),
  ADD KEY `idx_knowledge` (`knowledge_id`),
  ADD KEY `idx_user` (`user_id`);

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
-- Indeks untuk tabel `laporan_pengawas`
--
ALTER TABLE `laporan_pengawas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `idx_laporan_pengawas_status` (`status`),
  ADD KEY `idx_laporan_pengawas_created_by` (`created_by`);

--
-- Indeks untuk tabel `learning_analytics`
--
ALTER TABLE `learning_analytics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `enrollment_id` (`enrollment_id`),
  ADD KEY `idx_learning_analytics_course` (`course_id`),
  ADD KEY `idx_learning_analytics_student` (`student_id`),
  ADD KEY `idx_learning_analytics_event` (`event_type`);

--
-- Indeks untuk tabel `learning_courses`
--
ALTER TABLE `learning_courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_code` (`course_code`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_learning_courses_code` (`course_code`),
  ADD KEY `idx_learning_courses_category` (`category`),
  ADD KEY `idx_learning_courses_status` (`status`);

--
-- Indeks untuk tabel `learning_enrollments`
--
ALTER TABLE `learning_enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_learning_enrollments_course` (`course_id`),
  ADD KEY `idx_learning_enrollments_student` (`student_id`),
  ADD KEY `idx_learning_enrollments_status` (`status`);

--
-- Indeks untuk tabel `learning_progress`
--
ALTER TABLE `learning_progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_learning_progress_enrollment` (`enrollment_id`),
  ADD KEY `idx_learning_progress_module` (`module_id`),
  ADD KEY `idx_learning_progress_status` (`status`);

--
-- Indeks untuk tabel `ledger_entries`
--
ALTER TABLE `ledger_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_transaction` (`transaction_id`),
  ADD KEY `idx_account` (`account_type`,`account_id`),
  ADD KEY `idx_asset` (`asset_symbol`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indeks untuk tabel `legal_documents`
--
ALTER TABLE `legal_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cooperative` (`cooperative_id`),
  ADD KEY `idx_type` (`document_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_expiry` (`expiry_date`),
  ADD KEY `idx_access` (`access_level`),
  ADD KEY `idx_documents_cooperative_type` (`cooperative_id`,`document_type`,`status`);

--
-- Indeks untuk tabel `logistics_providers`
--
ALTER TABLE `logistics_providers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `provider_code` (`provider_code`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_logs_created_at` (`created_at`);

--
-- Indeks untuk tabel `loyalty_program`
--
ALTER TABLE `loyalty_program`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member` (`member_id`),
  ADD KEY `idx_tier` (`tier`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `loyalty_rewards`
--
ALTER TABLE `loyalty_rewards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`reward_type`),
  ADD KEY `idx_points` (`points_required`),
  ADD KEY `idx_active` (`active`),
  ADD KEY `idx_valid` (`valid_from`,`valid_until`);

--
-- Indeks untuk tabel `loyalty_transactions`
--
ALTER TABLE `loyalty_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_member` (`member_id`),
  ADD KEY `idx_type` (`transaction_type`),
  ADD KEY `idx_reference` (`reference_type`,`reference_id`),
  ADD KEY `idx_expiry` (`expiry_date`),
  ADD KEY `idx_processed` (`processed_at`);

--
-- Indeks untuk tabel `marketplace_analytics`
--
ALTER TABLE `marketplace_analytics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`metric_type`),
  ADD KEY `idx_dimension` (`dimension`,`dimension_value`),
  ADD KEY `idx_recorded` (`recorded_at`);

--
-- Indeks untuk tabel `marketplace_campaigns`
--
ALTER TABLE `marketplace_campaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`campaign_type`),
  ADD KEY `idx_dates` (`start_date`,`end_date`),
  ADD KEY `idx_active` (`active`),
  ADD KEY `idx_code` (`coupon_code`);

--
-- Indeks untuk tabel `marketplace_cart`
--
ALTER TABLE `marketplace_cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cart_item` (`user_id`,`product_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_product` (`product_id`);

--
-- Indeks untuk tabel `marketplace_categories`
--
ALTER TABLE `marketplace_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_parent` (`parent_id`),
  ADD KEY `idx_active` (`active`),
  ADD KEY `idx_sort` (`sort_order`);

--
-- Indeks untuk tabel `marketplace_favorites`
--
ALTER TABLE `marketplace_favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_favorite` (`user_id`,`product_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_product` (`product_id`);

--
-- Indeks untuk tabel `marketplace_products`
--
ALTER TABLE `marketplace_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_seller` (`seller_id`,`seller_type`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_price` (`price`),
  ADD KEY `idx_featured` (`featured`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indeks untuk tabel `marketplace_reviews`
--
ALTER TABLE `marketplace_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_seller` (`seller_id`),
  ADD KEY `idx_reviewer` (`reviewer_id`),
  ADD KEY `idx_rating` (`rating`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indeks untuk tabel `marketplace_transactions`
--
ALTER TABLE `marketplace_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type_status` (`transaction_type`,`status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_marketplace_recent` (`created_at`);

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
-- Indeks untuk tabel `member_feedback`
--
ALTER TABLE `member_feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_type` (`user_id`,`feedback_type`),
  ADD KEY `idx_rating` (`rating`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_feedback_recent` (`created_at`);

--
-- Indeks untuk tabel `modal_pokok`
--
ALTER TABLE `modal_pokok`
  ADD PRIMARY KEY (`id`),
  ADD KEY `anggota_id` (`anggota_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indeks untuk tabel `monitoring_metrics`
--
ALTER TABLE `monitoring_metrics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_metric` (`category`,`metric_name`,`timestamp`),
  ADD KEY `idx_monitoring_timestamp` (`timestamp`);

--
-- Indeks untuk tabel `network_analytics`
--
ALTER TABLE `network_analytics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`metric_type`),
  ADD KEY `idx_period` (`metric_period`),
  ADD KEY `idx_dimension` (`dimension`,`dimension_value`),
  ADD KEY `idx_cooperative` (`cooperative_id`),
  ADD KEY `idx_recorded` (`recorded_at`);

--
-- Indeks untuk tabel `network_governance`
--
ALTER TABLE `network_governance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`governance_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_effective` (`effective_date`),
  ADD KEY `idx_enforcement` (`enforcement_level`);

--
-- Indeks untuk tabel `notification_logs`
--
ALTER TABLE `notification_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notification_logs_reference` (`reference_id`),
  ADD KEY `idx_notification_logs_type` (`type`),
  ADD KEY `idx_notification_logs_channel` (`channel`),
  ADD KEY `idx_notification_logs_user` (`user_id`),
  ADD KEY `idx_notification_logs_sent_at` (`sent_at`);

--
-- Indeks untuk tabel `operational_costs`
--
ALTER TABLE `operational_costs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category_period` (`category`,`period`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indeks untuk tabel `optimization_alerts`
--
ALTER TABLE `optimization_alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`alert_type`),
  ADD KEY `idx_severity` (`severity`),
  ADD KEY `idx_resolved` (`resolved`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indeks untuk tabel `optimization_executions`
--
ALTER TABLE `optimization_executions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rule` (`rule_id`),
  ADD KEY `idx_status` (`execution_status`),
  ADD KEY `idx_executed` (`executed_at`);

--
-- Indeks untuk tabel `optimization_predictions`
--
ALTER TABLE `optimization_predictions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`prediction_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indeks untuk tabel `optimization_rules`
--
ALTER TABLE `optimization_rules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_frequency` (`execution_frequency`),
  ADD KEY `idx_last_executed` (`last_executed`);

--
-- Indeks untuk tabel `oracle_data_feeds`
--
ALTER TABLE `oracle_data_feeds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_oracle` (`oracle_id`),
  ADD KEY `idx_key` (`data_key`),
  ADD KEY `idx_timestamp` (`data_timestamp`);

--
-- Indeks untuk tabel `page_views`
--
ALTER TABLE `page_views`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_session_page` (`session_id`,`page_url`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indeks untuk tabel `payment_attempts`
--
ALTER TABLE `payment_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_payment_attempts_order` (`order_id`),
  ADD KEY `idx_payment_attempts_transaction` (`transaction_id`);

--
-- Indeks untuk tabel `payment_logs`
--
ALTER TABLE `payment_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_payment_logs_order` (`order_id`),
  ADD KEY `idx_payment_logs_created` (`created_at`);

--
-- Indeks untuk tabel `payrolls`
--
ALTER TABLE `payrolls`
  ADD PRIMARY KEY (`id`),
  ADD KEY `processed_by` (`processed_by`),
  ADD KEY `idx_payrolls_employee_id` (`employee_id`),
  ADD KEY `idx_payrolls_period` (`period`),
  ADD KEY `idx_payrolls_status` (`status`);

--
-- Indeks untuk tabel `payroll_components`
--
ALTER TABLE `payroll_components`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_payroll_components_employee_id` (`employee_id`),
  ADD KEY `idx_payroll_components_type` (`component_type`);

--
-- Indeks untuk tabel `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_pelanggan` (`kode_pelanggan`),
  ADD KEY `anggota_id` (`anggota_id`),
  ADD KEY `idx_pelanggan_kode` (`kode_pelanggan`);

--
-- Indeks untuk tabel `pelanggaran`
--
ALTER TABLE `pelanggaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sanksi_id` (`sanksi_id`),
  ADD KEY `decided_by` (`decided_by`),
  ADD KEY `idx_pelanggaran_user` (`user_id`),
  ADD KEY `idx_pelanggaran_status` (`status`),
  ADD KEY `idx_pelanggaran_tanggal` (`tanggal_pelanggaran`);

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
-- Indeks untuk tabel `performance_benchmarks`
--
ALTER TABLE `performance_benchmarks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type_name` (`metric_type`,`benchmark_name`);

--
-- Indeks untuk tabel `performance_metrics`
--
ALTER TABLE `performance_metrics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_performance_metrics_type` (`metric_type`,`measured_at`);

--
-- Indeks untuk tabel `performance_optimization_history`
--
ALTER TABLE `performance_optimization_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`optimization_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_implemented` (`implemented_at`);

--
-- Indeks untuk tabel `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indeks untuk tabel `personalization_rules`
--
ALTER TABLE `personalization_rules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_segment` (`target_segment`),
  ADD KEY `idx_active` (`is_active`);

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
  ADD KEY `idx_pinjaman_status` (`status`),
  ADD KEY `idx_pinjaman_status_due_date` (`status`,`tanggal_jatuh_tempo`),
  ADD KEY `idx_pinjaman_status_created` (`status`,`created_at`),
  ADD KEY `idx_pinjaman_amount_status` (`jumlah_pinjaman`,`status`),
  ADD KEY `idx_pinjaman_anggota_id` (`anggota_id`),
  ADD KEY `idx_pinjaman_jenis_pinjaman_id` (`jenis_pinjaman_id`),
  ADD KEY `idx_pinjaman_status_created_at` (`status`,`created_at`),
  ADD KEY `idx_pinjaman_status_jatuh_tempo` (`status`,`tanggal_jatuh_tempo`);

--
-- Indeks untuk tabel `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `position_code` (`position_code`),
  ADD KEY `department_id` (`department_id`);

--
-- Indeks untuk tabel `predictions`
--
ALTER TABLE `predictions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_model` (`model_id`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_type` (`prediction_type`),
  ADD KEY `idx_predicted_at` (`predicted_at`);

--
-- Indeks untuk tabel `prediction_accuracy`
--
ALTER TABLE `prediction_accuracy`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_model` (`model_id`),
  ADD KEY `idx_prediction` (`prediction_id`),
  ADD KEY `idx_accuracy` (`accuracy_score`);

--
-- Indeks untuk tabel `predictive_models`
--
ALTER TABLE `predictive_models`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`prediction_type`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `product_recommendations`
--
ALTER TABLE `product_recommendations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_score` (`recommendation_score`),
  ADD KEY `idx_created` (`created_at`);

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
-- Indeks untuk tabel `promos`
--
ALTER TABLE `promos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_promo` (`kode_promo`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_promos_kode` (`kode_promo`),
  ADD KEY `idx_promos_status` (`status`),
  ADD KEY `idx_promos_tanggal` (`tanggal_mulai`,`tanggal_akhir`);

--
-- Indeks untuk tabel `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `po_number` (`po_number`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_vendor` (`vendor_id`),
  ADD KEY `idx_date` (`order_date`);

--
-- Indeks untuk tabel `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_po` (`po_id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_quality` (`quality_status`),
  ADD KEY `idx_po_items_po` (`po_id`);

--
-- Indeks untuk tabel `quality_check_criteria`
--
ALTER TABLE `quality_check_criteria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_inspection` (`inspection_id`),
  ADD KEY `idx_result` (`result`),
  ADD KEY `idx_severity` (`severity`),
  ADD KEY `idx_quality_criteria_inspection` (`inspection_id`);

--
-- Indeks untuk tabel `quality_inspections`
--
ALTER TABLE `quality_inspections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reference` (`reference_type`,`reference_id`),
  ADD KEY `idx_type` (`inspection_type`),
  ADD KEY `idx_result` (`overall_result`),
  ADD KEY `idx_inspector` (`inspector_id`);

--
-- Indeks untuk tabel `rapat`
--
ALTER TABLE `rapat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_rapat_tanggal` (`tanggal`),
  ADD KEY `idx_rapat_status` (`status`),
  ADD KEY `idx_rapat_jenis` (`jenis_rapat`);

--
-- Indeks untuk tabel `rapat_keputusan`
--
ALTER TABLE `rapat_keputusan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pic` (`pic`),
  ADD KEY `idx_rapat_keputusan_rapat` (`rapat_id`);

--
-- Indeks untuk tabel `rapat_notulen`
--
ALTER TABLE `rapat_notulen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_rapat_notulen_rapat` (`rapat_id`);

--
-- Indeks untuk tabel `rapat_peserta`
--
ALTER TABLE `rapat_peserta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rapat_peserta_rapat` (`rapat_id`),
  ADD KEY `idx_rapat_peserta_user` (`user_id`);

--
-- Indeks untuk tabel `rat_attendance`
--
ALTER TABLE `rat_attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_attendance` (`rat_id`,`member_id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indeks untuk tabel `rat_meetings`
--
ALTER TABLE `rat_meetings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cooperative` (`cooperative_id`),
  ADD KEY `idx_year` (`rat_year`),
  ADD KEY `idx_date` (`meeting_date`),
  ADD KEY `idx_quorum` (`quorum_achieved`),
  ADD KEY `idx_rat_cooperative_year` (`cooperative_id`,`rat_year`);

--
-- Indeks untuk tabel `recommendation_engine`
--
ALTER TABLE `recommendation_engine`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_item` (`item_type`,`item_id`),
  ADD KEY `idx_score` (`recommendation_score`),
  ADD KEY `idx_created_at` (`created_at`);

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
-- Indeks untuk tabel `regulatory_reports`
--
ALTER TABLE `regulatory_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cooperative` (`cooperative_id`),
  ADD KEY `idx_type` (`report_type`),
  ADD KEY `idx_period` (`report_year`,`report_quarter`,`report_month`),
  ADD KEY `idx_submission` (`submitted_to`,`submission_date`),
  ADD KEY `idx_approval` (`approval_status`),
  ADD KEY `idx_reports_cooperative_type` (`cooperative_id`,`report_type`,`report_year`);

--
-- Indeks untuk tabel `replenishment_rules`
--
ALTER TABLE `replenishment_rules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_auto_reorder` (`auto_reorder_enabled`),
  ADD KEY `idx_replenishment_product` (`product_id`);

--
-- Indeks untuk tabel `reserve_funds`
--
ALTER TABLE `reserve_funds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cooperative` (`cooperative_id`),
  ADD KEY `idx_type` (`fund_type`),
  ADD KEY `idx_year` (`fund_year`);

--
-- Indeks untuk tabel `revenue_metrics`
--
ALTER TABLE `revenue_metrics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category_period` (`source_category`,`period`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indeks untuk tabel `risk_alerts`
--
ALTER TABLE `risk_alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resolved_by` (`resolved_by`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_risk_alerts_type` (`type`),
  ADD KEY `idx_risk_alerts_severity` (`severity`),
  ADD KEY `idx_risk_alerts_status` (`status`),
  ADD KEY `idx_risk_alerts_created_at` (`created_at`);

--
-- Indeks untuk tabel `risk_metrics`
--
ALTER TABLE `risk_metrics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type_level` (`risk_type`,`risk_level`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indeks untuk tabel `roi_tracking`
--
ALTER TABLE `roi_tracking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_period` (`period`,`calculated_at`);

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
-- Indeks untuk tabel `rpa_executions`
--
ALTER TABLE `rpa_executions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_process` (`process_id`),
  ADD KEY `idx_status` (`execution_status`),
  ADD KEY `idx_started` (`started_at`);

--
-- Indeks untuk tabel `rpa_processes`
--
ALTER TABLE `rpa_processes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`process_category`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `sanksi`
--
ALTER TABLE `sanksi`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `service_subscriptions`
--
ALTER TABLE `service_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_service` (`service_id`),
  ADD KEY `idx_subscriber` (`subscriber_coop_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_end_date` (`subscription_end`);

--
-- Indeks untuk tabel `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `idx_settings_key` (`setting_key`);

--
-- Indeks untuk tabel `shared_services`
--
ALTER TABLE `shared_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`service_category`),
  ADD KEY `idx_provider` (`service_provider_id`),
  ADD KEY `idx_type` (`service_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_availability` (`availability_status`);

--
-- Indeks untuk tabel `shipments`
--
ALTER TABLE `shipments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `shipment_number` (`shipment_number`),
  ADD KEY `idx_reference` (`reference_type`,`reference_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_tracking` (`tracking_number`);

--
-- Indeks untuk tabel `shipment_items`
--
ALTER TABLE `shipment_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_shipment` (`shipment_id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_shipment_items_shipment` (`shipment_id`);

--
-- Indeks untuk tabel `shipping_details`
--
ALTER TABLE `shipping_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_shipping_details_order` (`order_id`),
  ADD KEY `idx_shipping_details_courier` (`courier`),
  ADD KEY `idx_shipping_details_status` (`status`);

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
  ADD KEY `idx_simpanan_jenis` (`jenis_simpanan_id`),
  ADD KEY `idx_simpanan_member_type` (`anggota_id`,`jenis_simpanan_id`),
  ADD KEY `idx_simpanan_status_saldo` (`status`,`saldo`),
  ADD KEY `idx_simpanan_anggota_id` (`anggota_id`),
  ADD KEY `idx_simpanan_jenis_simpanan_id` (`jenis_simpanan_id`),
  ADD KEY `idx_simpanan_anggota_status` (`anggota_id`,`status`),
  ADD KEY `idx_simpanan_jenis_created` (`jenis_simpanan_id`,`created_at`);

--
-- Indeks untuk tabel `smart_contracts`
--
ALTER TABLE `smart_contracts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `contract_address` (`contract_address`),
  ADD KEY `idx_type` (`contract_type`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_network` (`deployed_network`);

--
-- Indeks untuk tabel `status_types`
--
ALTER TABLE `status_types`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `supervision_records`
--
ALTER TABLE `supervision_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supervisor_id` (`supervisor_id`),
  ADD KEY `supervised_person_id` (`supervised_person_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indeks untuk tabel `supplier_performance`
--
ALTER TABLE `supplier_performance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_vendor` (`vendor_id`),
  ADD KEY `idx_period` (`evaluation_period`),
  ADD KEY `idx_date` (`evaluation_date`),
  ADD KEY `idx_score` (`overall_score`),
  ADD KEY `idx_performance_vendor_period` (`vendor_id`,`evaluation_period`);

--
-- Indeks untuk tabel `supply_chain_alerts`
--
ALTER TABLE `supply_chain_alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`alert_type`),
  ADD KEY `idx_severity` (`severity`),
  ADD KEY `idx_reference` (`reference_type`,`reference_id`),
  ADD KEY `idx_acknowledged` (`acknowledged`),
  ADD KEY `idx_resolved` (`resolved`);

--
-- Indeks untuk tabel `system_alerts`
--
ALTER TABLE `system_alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type_level` (`alert_type`,`alert_level`),
  ADD KEY `idx_resolved` (`resolved`),
  ADD KEY `idx_alerts_unresolved` (`resolved`,`created_at`);

--
-- Indeks untuk tabel `system_health_checks`
--
ALTER TABLE `system_health_checks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_system_health_checks_status` (`status`);

--
-- Indeks untuk tabel `system_learning_patterns`
--
ALTER TABLE `system_learning_patterns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`pattern_type`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_confidence` (`confidence_score`);

--
-- Indeks untuk tabel `system_metrics`
--
ALTER TABLE `system_metrics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type_time` (`metric_type`,`created_at`);

--
-- Indeks untuk tabel `system_monitoring`
--
ALTER TABLE `system_monitoring`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_system_monitoring_type` (`check_type`,`checked_at`);

--
-- Indeks untuk tabel `system_uptime`
--
ALTER TABLE `system_uptime`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_date` (`date`);

--
-- Indeks untuk tabel `tax_filings`
--
ALTER TABLE `tax_filings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `filed_by` (`filed_by`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indeks untuk tabel `tax_payments`
--
ALTER TABLE `tax_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `paid_by` (`paid_by`),
  ADD KEY `verified_by` (`verified_by`);

--
-- Indeks untuk tabel `token_economics`
--
ALTER TABLE `token_economics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_token` (`token_symbol`);

--
-- Indeks untuk tabel `token_staking`
--
ALTER TABLE `token_staking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_wallet` (`wallet_address`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_staking_end` (`staking_end`);

--
-- Indeks untuk tabel `trade_items`
--
ALTER TABLE `trade_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_trade` (`trade_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `transaction_metrics`
--
ALTER TABLE `transaction_metrics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type_method` (`transaction_type`,`payment_method`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_transactions_recent` (`created_at`);

--
-- Indeks untuk tabel `transaksi_simpanan`
--
ALTER TABLE `transaksi_simpanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_trans_simpanan_simpanan` (`simpanan_id`),
  ADD KEY `idx_trans_simpanan_user` (`user_id`),
  ADD KEY `idx_trans_simpanan_tanggal` (`tanggal_transaksi`),
  ADD KEY `idx_transaksi_simpanan_account_date` (`simpanan_id`,`tanggal_transaksi`),
  ADD KEY `idx_transaksi_simpanan_created` (`created_at`),
  ADD KEY `idx_transaksi_simpanan_simpanan_id` (`simpanan_id`),
  ADD KEY `idx_transaksi_simpanan_tanggal` (`tanggal_transaksi`);

--
-- Indeks untuk tabel `transparency_logs`
--
ALTER TABLE `transparency_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recorded_by` (`recorded_by`),
  ADD KEY `idx_transparency_logs_action` (`action`),
  ADD KEY `idx_transparency_logs_entity` (`entity_type`,`entity_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `user_activities`
--
ALTER TABLE `user_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_activity` (`user_id`,`activity_type`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_activities_recent` (`created_at`);

--
-- Indeks untuk tabel `user_behavior_analytics`
--
ALTER TABLE `user_behavior_analytics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_behavior` (`behavior_type`),
  ADD KEY `idx_timestamp` (`timestamp`);

--
-- Indeks untuk tabel `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_risk_profile` (`risk_profile`),
  ADD KEY `idx_engagement` (`engagement_score`);

--
-- Indeks untuk tabel `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `assigned_by` (`assigned_by`);

--
-- Indeks untuk tabel `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_session` (`user_id`,`session_id`),
  ADD KEY `idx_last_activity` (`last_activity`),
  ADD KEY `idx_sessions_active` (`last_activity`);

--
-- Indeks untuk tabel `ux_experiment_results`
--
ALTER TABLE `ux_experiment_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_experiment` (`experiment_id`),
  ADD KEY `idx_variant` (`design_variant`);

--
-- Indeks untuk tabel `ux_optimization_experiments`
--
ALTER TABLE `ux_optimization_experiments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_page` (`page_url`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_dates` (`start_date`,`end_date`);

--
-- Indeks untuk tabel `vendors`
--
ALTER TABLE `vendors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vendor_code` (`vendor_code`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_type` (`vendor_type`),
  ADD KEY `idx_performance` (`performance_rating`);

--
-- Indeks untuk tabel `warehouses`
--
ALTER TABLE `warehouses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `warehouse_code` (`warehouse_code`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_manager` (`manager_id`);

--
-- Indeks untuk tabel `warehouse_zones`
--
ALTER TABLE `warehouse_zones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_warehouse` (`warehouse_id`),
  ADD KEY `idx_type` (`zone_type`),
  ADD KEY `idx_zones_warehouse` (`warehouse_id`);

--
-- Indeks untuk tabel `withholding_tax`
--
ALTER TABLE `withholding_tax`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `ab_tests`
--
ALTER TABLE `ab_tests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ab_test_participants`
--
ALTER TABLE `ab_test_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ab_test_results`
--
ALTER TABLE `ab_test_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `access_logs`
--
ALTER TABLE `access_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `address_stats`
--
ALTER TABLE `address_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `ai_conversations`
--
ALTER TABLE `ai_conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ai_decisions`
--
ALTER TABLE `ai_decisions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ai_ethics_monitoring`
--
ALTER TABLE `ai_ethics_monitoring`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ai_fraud_alerts`
--
ALTER TABLE `ai_fraud_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ai_fraud_models`
--
ALTER TABLE `ai_fraud_models`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ai_messages`
--
ALTER TABLE `ai_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ai_model_data`
--
ALTER TABLE `ai_model_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ai_model_performance`
--
ALTER TABLE `ai_model_performance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ai_recommendations`
--
ALTER TABLE `ai_recommendations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ai_training_data`
--
ALTER TABLE `ai_training_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `alerts`
--
ALTER TABLE `alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `anggota`
--
ALTER TABLE `anggota`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `angsuran`
--
ALTER TABLE `angsuran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `application_logs`
--
ALTER TABLE `application_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `app_sessions`
--
ALTER TABLE `app_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `asset_depreciation`
--
ALTER TABLE `asset_depreciation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `asset_disposals`
--
ALTER TABLE `asset_disposals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `asset_holdings`
--
ALTER TABLE `asset_holdings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `asset_maintenance`
--
ALTER TABLE `asset_maintenance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `automated_processes`
--
ALTER TABLE `automated_processes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `b2b_partners`
--
ALTER TABLE `b2b_partners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `backup_files`
--
ALTER TABLE `backup_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `backup_logs`
--
ALTER TABLE `backup_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `backup_schedules`
--
ALTER TABLE `backup_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `blockchain_blocks`
--
ALTER TABLE `blockchain_blocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `blockchain_oracles`
--
ALTER TABLE `blockchain_oracles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `blockchain_transactions`
--
ALTER TABLE `blockchain_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `block_verifications`
--
ALTER TABLE `block_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `bridge_transactions`
--
ALTER TABLE `bridge_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `buku_besar`
--
ALTER TABLE `buku_besar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `business_metrics`
--
ALTER TABLE `business_metrics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `chain_bridges`
--
ALTER TABLE `chain_bridges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `coa`
--
ALTER TABLE `coa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT untuk tabel `collection_actions`
--
ALTER TABLE `collection_actions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `collection_automation`
--
ALTER TABLE `collection_automation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `collection_templates`
--
ALTER TABLE `collection_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `community_activities`
--
ALTER TABLE `community_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `compliance_audits`
--
ALTER TABLE `compliance_audits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `compliance_checks`
--
ALTER TABLE `compliance_checks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `compliance_metrics`
--
ALTER TABLE `compliance_metrics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `compliance_monitoring`
--
ALTER TABLE `compliance_monitoring`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `contract_interactions`
--
ALTER TABLE `contract_interactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `contract_templates`
--
ALTER TABLE `contract_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `conversion_metrics`
--
ALTER TABLE `conversion_metrics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `cooperative_accounts`
--
ALTER TABLE `cooperative_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `cooperative_activities`
--
ALTER TABLE `cooperative_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `cooperative_members`
--
ALTER TABLE `cooperative_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `cooperative_network`
--
ALTER TABLE `cooperative_network`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `cooperative_structure`
--
ALTER TABLE `cooperative_structure`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `course_modules`
--
ALTER TABLE `course_modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `course_ratings`
--
ALTER TABLE `course_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `credit_scores`
--
ALTER TABLE `credit_scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `credit_score_factors`
--
ALTER TABLE `credit_score_factors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `credit_scoring_models`
--
ALTER TABLE `credit_scoring_models`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `cross_border_payments`
--
ALTER TABLE `cross_border_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `customer_invoices`
--
ALTER TABLE `customer_invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `decision_rules`
--
ALTER TABLE `decision_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `demand_forecasts`
--
ALTER TABLE `demand_forecasts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT untuk tabel `digital_assets`
--
ALTER TABLE `digital_assets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `digital_products`
--
ALTER TABLE `digital_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `digital_wallets`
--
ALTER TABLE `digital_wallets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `education_fund_utilization`
--
ALTER TABLE `education_fund_utilization`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `efficiency_metrics`
--
ALTER TABLE `efficiency_metrics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `employee_attendance`
--
ALTER TABLE `employee_attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `entity_addresses`
--
ALTER TABLE `entity_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `entity_contacts`
--
ALTER TABLE `entity_contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `error_logs`
--
ALTER TABLE `error_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `financial_periods`
--
ALTER TABLE `financial_periods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `fixed_assets`
--
ALTER TABLE `fixed_assets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `fraud_alerts`
--
ALTER TABLE `fraud_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `fraud_patterns`
--
ALTER TABLE `fraud_patterns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `governance_bodies`
--
ALTER TABLE `governance_bodies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `governance_delegates`
--
ALTER TABLE `governance_delegates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `governance_proposals`
--
ALTER TABLE `governance_proposals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `governance_votes`
--
ALTER TABLE `governance_votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `improvement_initiatives`
--
ALTER TABLE `improvement_initiatives`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `improvement_measurements`
--
ALTER TABLE `improvement_measurements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `international_opportunities`
--
ALTER TABLE `international_opportunities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `international_partnerships`
--
ALTER TABLE `international_partnerships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `inter_coop_trades`
--
ALTER TABLE `inter_coop_trades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `inventory_items`
--
ALTER TABLE `inventory_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
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
-- AUTO_INCREMENT untuk tabel `journal_entries`
--
ALTER TABLE `journal_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `journal_lines`
--
ALTER TABLE `journal_lines`
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
-- AUTO_INCREMENT untuk tabel `knowledge_base`
--
ALTER TABLE `knowledge_base`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `knowledge_ratings`
--
ALTER TABLE `knowledge_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `koperasi_activities`
--
ALTER TABLE `koperasi_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

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
-- AUTO_INCREMENT untuk tabel `laporan_pengawas`
--
ALTER TABLE `laporan_pengawas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `learning_analytics`
--
ALTER TABLE `learning_analytics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `learning_courses`
--
ALTER TABLE `learning_courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `learning_enrollments`
--
ALTER TABLE `learning_enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `learning_progress`
--
ALTER TABLE `learning_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ledger_entries`
--
ALTER TABLE `ledger_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `legal_documents`
--
ALTER TABLE `legal_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `logistics_providers`
--
ALTER TABLE `logistics_providers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT untuk tabel `loyalty_program`
--
ALTER TABLE `loyalty_program`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `loyalty_rewards`
--
ALTER TABLE `loyalty_rewards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `loyalty_transactions`
--
ALTER TABLE `loyalty_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `marketplace_analytics`
--
ALTER TABLE `marketplace_analytics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `marketplace_campaigns`
--
ALTER TABLE `marketplace_campaigns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `marketplace_cart`
--
ALTER TABLE `marketplace_cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `marketplace_categories`
--
ALTER TABLE `marketplace_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `marketplace_favorites`
--
ALTER TABLE `marketplace_favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `marketplace_products`
--
ALTER TABLE `marketplace_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `marketplace_reviews`
--
ALTER TABLE `marketplace_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `marketplace_transactions`
--
ALTER TABLE `marketplace_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT untuk tabel `member_feedback`
--
ALTER TABLE `member_feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `modal_pokok`
--
ALTER TABLE `modal_pokok`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `monitoring_metrics`
--
ALTER TABLE `monitoring_metrics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `network_analytics`
--
ALTER TABLE `network_analytics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `network_governance`
--
ALTER TABLE `network_governance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `notification_logs`
--
ALTER TABLE `notification_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `operational_costs`
--
ALTER TABLE `operational_costs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `optimization_alerts`
--
ALTER TABLE `optimization_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `optimization_executions`
--
ALTER TABLE `optimization_executions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `optimization_predictions`
--
ALTER TABLE `optimization_predictions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `optimization_rules`
--
ALTER TABLE `optimization_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `oracle_data_feeds`
--
ALTER TABLE `oracle_data_feeds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `page_views`
--
ALTER TABLE `page_views`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `payment_attempts`
--
ALTER TABLE `payment_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `payment_logs`
--
ALTER TABLE `payment_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `payrolls`
--
ALTER TABLE `payrolls`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `payroll_components`
--
ALTER TABLE `payroll_components`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pelanggaran`
--
ALTER TABLE `pelanggaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pengaturan_koperasi`
--
ALTER TABLE `pengaturan_koperasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `performance_benchmarks`
--
ALTER TABLE `performance_benchmarks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `performance_metrics`
--
ALTER TABLE `performance_metrics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `performance_optimization_history`
--
ALTER TABLE `performance_optimization_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT untuk tabel `personalization_rules`
--
ALTER TABLE `personalization_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pinjaman`
--
ALTER TABLE `pinjaman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `positions`
--
ALTER TABLE `positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `predictions`
--
ALTER TABLE `predictions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `prediction_accuracy`
--
ALTER TABLE `prediction_accuracy`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `predictive_models`
--
ALTER TABLE `predictive_models`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `product_recommendations`
--
ALTER TABLE `product_recommendations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `promos`
--
ALTER TABLE `promos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `quality_check_criteria`
--
ALTER TABLE `quality_check_criteria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `quality_inspections`
--
ALTER TABLE `quality_inspections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `rapat`
--
ALTER TABLE `rapat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `rapat_keputusan`
--
ALTER TABLE `rapat_keputusan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `rapat_notulen`
--
ALTER TABLE `rapat_notulen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `rapat_peserta`
--
ALTER TABLE `rapat_peserta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `rat_attendance`
--
ALTER TABLE `rat_attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `rat_meetings`
--
ALTER TABLE `rat_meetings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `recommendation_engine`
--
ALTER TABLE `recommendation_engine`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `regulatory_reports`
--
ALTER TABLE `regulatory_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `replenishment_rules`
--
ALTER TABLE `replenishment_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `reserve_funds`
--
ALTER TABLE `reserve_funds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `revenue_metrics`
--
ALTER TABLE `revenue_metrics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `risk_alerts`
--
ALTER TABLE `risk_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `risk_metrics`
--
ALTER TABLE `risk_metrics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `roi_tracking`
--
ALTER TABLE `roi_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `rpa_executions`
--
ALTER TABLE `rpa_executions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `rpa_processes`
--
ALTER TABLE `rpa_processes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `sanksi`
--
ALTER TABLE `sanksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `service_subscriptions`
--
ALTER TABLE `service_subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `shared_services`
--
ALTER TABLE `shared_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `shipments`
--
ALTER TABLE `shipments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `shipment_items`
--
ALTER TABLE `shipment_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `shipping_details`
--
ALTER TABLE `shipping_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `shu_anggota`
--
ALTER TABLE `shu_anggota`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `shu_components`
--
ALTER TABLE `shu_components`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

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
-- AUTO_INCREMENT untuk tabel `smart_contracts`
--
ALTER TABLE `smart_contracts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `status_types`
--
ALTER TABLE `status_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `supervision_records`
--
ALTER TABLE `supervision_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `supplier_performance`
--
ALTER TABLE `supplier_performance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `supply_chain_alerts`
--
ALTER TABLE `supply_chain_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `system_alerts`
--
ALTER TABLE `system_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `system_health_checks`
--
ALTER TABLE `system_health_checks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `system_learning_patterns`
--
ALTER TABLE `system_learning_patterns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `system_metrics`
--
ALTER TABLE `system_metrics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `system_monitoring`
--
ALTER TABLE `system_monitoring`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `system_uptime`
--
ALTER TABLE `system_uptime`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tax_filings`
--
ALTER TABLE `tax_filings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tax_payments`
--
ALTER TABLE `tax_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `token_economics`
--
ALTER TABLE `token_economics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `token_staking`
--
ALTER TABLE `token_staking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `trade_items`
--
ALTER TABLE `trade_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `transaction_metrics`
--
ALTER TABLE `transaction_metrics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `transaksi_simpanan`
--
ALTER TABLE `transaksi_simpanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `transparency_logs`
--
ALTER TABLE `transparency_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `user_activities`
--
ALTER TABLE `user_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `user_behavior_analytics`
--
ALTER TABLE `user_behavior_analytics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ux_experiment_results`
--
ALTER TABLE `ux_experiment_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ux_optimization_experiments`
--
ALTER TABLE `ux_optimization_experiments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `vendors`
--
ALTER TABLE `vendors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `warehouses`
--
ALTER TABLE `warehouses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `warehouse_zones`
--
ALTER TABLE `warehouse_zones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `withholding_tax`
--
ALTER TABLE `withholding_tax`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `ab_test_participants`
--
ALTER TABLE `ab_test_participants`
  ADD CONSTRAINT `ab_test_participants_ibfk_1` FOREIGN KEY (`test_id`) REFERENCES `ab_tests` (`id`);

--
-- Ketidakleluasaan untuk tabel `ab_test_results`
--
ALTER TABLE `ab_test_results`
  ADD CONSTRAINT `ab_test_results_ibfk_1` FOREIGN KEY (`test_id`) REFERENCES `ab_tests` (`id`);

--
-- Ketidakleluasaan untuk tabel `ai_fraud_alerts`
--
ALTER TABLE `ai_fraud_alerts`
  ADD CONSTRAINT `ai_fraud_alerts_ibfk_1` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `ai_fraud_alerts_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `ai_messages`
--
ALTER TABLE `ai_messages`
  ADD CONSTRAINT `ai_messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `ai_conversations` (`id`);

--
-- Ketidakleluasaan untuk tabel `ai_recommendations`
--
ALTER TABLE `ai_recommendations`
  ADD CONSTRAINT `ai_recommendations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `ai_recommendations_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `produk` (`id`);

--
-- Ketidakleluasaan untuk tabel `anggota`
--
ALTER TABLE `anggota`
  ADD CONSTRAINT `anggota_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `anggota_ibfk_2` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`),
  ADD CONSTRAINT `anggota_ibfk_3` FOREIGN KEY (`primary_contact_id`) REFERENCES `contacts` (`id`),
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
-- Ketidakleluasaan untuk tabel `asset_depreciation`
--
ALTER TABLE `asset_depreciation`
  ADD CONSTRAINT `asset_depreciation_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `fixed_assets` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `asset_disposals`
--
ALTER TABLE `asset_disposals`
  ADD CONSTRAINT `asset_disposals_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `fixed_assets` (`id`),
  ADD CONSTRAINT `asset_disposals_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `asset_disposals_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `asset_maintenance`
--
ALTER TABLE `asset_maintenance`
  ADD CONSTRAINT `asset_maintenance_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `fixed_assets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `asset_maintenance_ibfk_2` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `backup_files`
--
ALTER TABLE `backup_files`
  ADD CONSTRAINT `backup_files_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `backup_logs`
--
ALTER TABLE `backup_logs`
  ADD CONSTRAINT `backup_logs_ibfk_1` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `blockchain_blocks`
--
ALTER TABLE `blockchain_blocks`
  ADD CONSTRAINT `blockchain_blocks_ibfk_1` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `block_verifications`
--
ALTER TABLE `block_verifications`
  ADD CONSTRAINT `block_verifications_ibfk_1` FOREIGN KEY (`block_id`) REFERENCES `blockchain_blocks` (`id`),
  ADD CONSTRAINT `block_verifications_ibfk_2` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `buku_besar`
--
ALTER TABLE `buku_besar`
  ADD CONSTRAINT `buku_besar_ibfk_1` FOREIGN KEY (`coa_id`) REFERENCES `coa` (`id`),
  ADD CONSTRAINT `buku_besar_ibfk_2` FOREIGN KEY (`jurnal_detail_id`) REFERENCES `jurnal_detail` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_category_id`) REFERENCES `categories` (`id`);

--
-- Ketidakleluasaan untuk tabel `certificates`
--
ALTER TABLE `certificates`
  ADD CONSTRAINT `certificates_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `learning_enrollments` (`id`),
  ADD CONSTRAINT `certificates_ibfk_2` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `coa`
--
ALTER TABLE `coa`
  ADD CONSTRAINT `coa_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `coa` (`id`);

--
-- Ketidakleluasaan untuk tabel `compliance_audits`
--
ALTER TABLE `compliance_audits`
  ADD CONSTRAINT `compliance_audits_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `cooperative_structure` (`id`);

--
-- Ketidakleluasaan untuk tabel `compliance_monitoring`
--
ALTER TABLE `compliance_monitoring`
  ADD CONSTRAINT `compliance_monitoring_ibfk_1` FOREIGN KEY (`governance_id`) REFERENCES `network_governance` (`id`),
  ADD CONSTRAINT `compliance_monitoring_ibfk_2` FOREIGN KEY (`cooperative_id`) REFERENCES `cooperative_network` (`id`);

--
-- Ketidakleluasaan untuk tabel `cooperative_accounts`
--
ALTER TABLE `cooperative_accounts`
  ADD CONSTRAINT `cooperative_accounts_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `cooperative_structure` (`id`),
  ADD CONSTRAINT `cooperative_accounts_ibfk_2` FOREIGN KEY (`parent_account_id`) REFERENCES `cooperative_accounts` (`id`);

--
-- Ketidakleluasaan untuk tabel `cooperative_activities`
--
ALTER TABLE `cooperative_activities`
  ADD CONSTRAINT `cooperative_activities_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `cooperative_structure` (`id`);

--
-- Ketidakleluasaan untuk tabel `cooperative_members`
--
ALTER TABLE `cooperative_members`
  ADD CONSTRAINT `cooperative_members_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `cooperative_structure` (`id`);

--
-- Ketidakleluasaan untuk tabel `course_modules`
--
ALTER TABLE `course_modules`
  ADD CONSTRAINT `course_modules_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `learning_courses` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `course_ratings`
--
ALTER TABLE `course_ratings`
  ADD CONSTRAINT `course_ratings_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `learning_courses` (`id`),
  ADD CONSTRAINT `course_ratings_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `cross_border_payments`
--
ALTER TABLE `cross_border_payments`
  ADD CONSTRAINT `cross_border_payments_ibfk_1` FOREIGN KEY (`sender_coop_id`) REFERENCES `cooperative_network` (`id`),
  ADD CONSTRAINT `cross_border_payments_ibfk_2` FOREIGN KEY (`receiver_coop_id`) REFERENCES `cooperative_network` (`id`);

--
-- Ketidakleluasaan untuk tabel `customer_invoices`
--
ALTER TABLE `customer_invoices`
  ADD CONSTRAINT `customer_invoices_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `penjualan` (`id`),
  ADD CONSTRAINT `customer_invoices_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `customer_invoices_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

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
-- Ketidakleluasaan untuk tabel `education_fund_utilization`
--
ALTER TABLE `education_fund_utilization`
  ADD CONSTRAINT `education_fund_utilization_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `cooperative_structure` (`id`);

--
-- Ketidakleluasaan untuk tabel `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `employees_ibfk_2` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`),
  ADD CONSTRAINT `employees_ibfk_3` FOREIGN KEY (`primary_contact_id`) REFERENCES `contacts` (`id`),
  ADD CONSTRAINT `employees_ibfk_4` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  ADD CONSTRAINT `employees_ibfk_5` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`);

--
-- Ketidakleluasaan untuk tabel `employee_attendance`
--
ALTER TABLE `employee_attendance`
  ADD CONSTRAINT `employee_attendance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `employee_attendance_ibfk_2` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `entity_addresses`
--
ALTER TABLE `entity_addresses`
  ADD CONSTRAINT `entity_addresses_ibfk_1` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `entity_contacts`
--
ALTER TABLE `entity_contacts`
  ADD CONSTRAINT `entity_contacts_ibfk_1` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `financial_periods`
--
ALTER TABLE `financial_periods`
  ADD CONSTRAINT `financial_periods_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `cooperative_structure` (`id`);

--
-- Ketidakleluasaan untuk tabel `fixed_assets`
--
ALTER TABLE `fixed_assets`
  ADD CONSTRAINT `fixed_assets_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `governance_bodies`
--
ALTER TABLE `governance_bodies`
  ADD CONSTRAINT `governance_bodies_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `cooperative_structure` (`id`),
  ADD CONSTRAINT `governance_bodies_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `cooperative_members` (`id`);

--
-- Ketidakleluasaan untuk tabel `improvement_measurements`
--
ALTER TABLE `improvement_measurements`
  ADD CONSTRAINT `improvement_measurements_ibfk_1` FOREIGN KEY (`initiative_id`) REFERENCES `improvement_initiatives` (`id`);

--
-- Ketidakleluasaan untuk tabel `international_partnerships`
--
ALTER TABLE `international_partnerships`
  ADD CONSTRAINT `international_partnerships_ibfk_1` FOREIGN KEY (`local_coop_id`) REFERENCES `cooperative_network` (`id`);

--
-- Ketidakleluasaan untuk tabel `inter_coop_trades`
--
ALTER TABLE `inter_coop_trades`
  ADD CONSTRAINT `inter_coop_trades_ibfk_1` FOREIGN KEY (`seller_coop_id`) REFERENCES `cooperative_network` (`id`),
  ADD CONSTRAINT `inter_coop_trades_ibfk_2` FOREIGN KEY (`buyer_coop_id`) REFERENCES `cooperative_network` (`id`);

--
-- Ketidakleluasaan untuk tabel `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD CONSTRAINT `inventory_transactions_ibfk_1` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items` (`id`);

--
-- Ketidakleluasaan untuk tabel `journal_entries`
--
ALTER TABLE `journal_entries`
  ADD CONSTRAINT `journal_entries_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `cooperative_structure` (`id`);

--
-- Ketidakleluasaan untuk tabel `journal_lines`
--
ALTER TABLE `journal_lines`
  ADD CONSTRAINT `journal_lines_ibfk_1` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`id`),
  ADD CONSTRAINT `journal_lines_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `cooperative_accounts` (`id`);

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
-- Ketidakleluasaan untuk tabel `knowledge_base`
--
ALTER TABLE `knowledge_base`
  ADD CONSTRAINT `knowledge_base_ibfk_1` FOREIGN KEY (`author_coop_id`) REFERENCES `cooperative_network` (`id`);

--
-- Ketidakleluasaan untuk tabel `knowledge_ratings`
--
ALTER TABLE `knowledge_ratings`
  ADD CONSTRAINT `knowledge_ratings_ibfk_1` FOREIGN KEY (`knowledge_id`) REFERENCES `knowledge_base` (`id`);

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
-- Ketidakleluasaan untuk tabel `laporan_pengawas`
--
ALTER TABLE `laporan_pengawas`
  ADD CONSTRAINT `laporan_pengawas_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `laporan_pengawas_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `learning_analytics`
--
ALTER TABLE `learning_analytics`
  ADD CONSTRAINT `learning_analytics_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `learning_courses` (`id`),
  ADD CONSTRAINT `learning_analytics_ibfk_2` FOREIGN KEY (`enrollment_id`) REFERENCES `learning_enrollments` (`id`),
  ADD CONSTRAINT `learning_analytics_ibfk_3` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `learning_courses`
--
ALTER TABLE `learning_courses`
  ADD CONSTRAINT `learning_courses_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `learning_enrollments`
--
ALTER TABLE `learning_enrollments`
  ADD CONSTRAINT `learning_enrollments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `learning_courses` (`id`),
  ADD CONSTRAINT `learning_enrollments_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `learning_progress`
--
ALTER TABLE `learning_progress`
  ADD CONSTRAINT `learning_progress_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `learning_enrollments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `learning_progress_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `course_modules` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `legal_documents`
--
ALTER TABLE `legal_documents`
  ADD CONSTRAINT `legal_documents_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `cooperative_structure` (`id`);

--
-- Ketidakleluasaan untuk tabel `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `marketplace_reviews`
--
ALTER TABLE `marketplace_reviews`
  ADD CONSTRAINT `marketplace_reviews_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `marketplace_transactions` (`id`);

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
-- Ketidakleluasaan untuk tabel `notification_logs`
--
ALTER TABLE `notification_logs`
  ADD CONSTRAINT `notification_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `notification_logs_ibfk_2` FOREIGN KEY (`reference_id`) REFERENCES `penjualan` (`id`);

--
-- Ketidakleluasaan untuk tabel `optimization_executions`
--
ALTER TABLE `optimization_executions`
  ADD CONSTRAINT `optimization_executions_ibfk_1` FOREIGN KEY (`rule_id`) REFERENCES `optimization_rules` (`id`);

--
-- Ketidakleluasaan untuk tabel `payment_attempts`
--
ALTER TABLE `payment_attempts`
  ADD CONSTRAINT `payment_attempts_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `penjualan` (`id`);

--
-- Ketidakleluasaan untuk tabel `payment_logs`
--
ALTER TABLE `payment_logs`
  ADD CONSTRAINT `payment_logs_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `penjualan` (`id`);

--
-- Ketidakleluasaan untuk tabel `payrolls`
--
ALTER TABLE `payrolls`
  ADD CONSTRAINT `payrolls_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `payrolls_ibfk_2` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `payroll_components`
--
ALTER TABLE `payroll_components`
  ADD CONSTRAINT `payroll_components_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `payroll_components_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD CONSTRAINT `pelanggan_ibfk_1` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`);

--
-- Ketidakleluasaan untuk tabel `pelanggaran`
--
ALTER TABLE `pelanggaran`
  ADD CONSTRAINT `pelanggaran_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `pelanggaran_ibfk_2` FOREIGN KEY (`sanksi_id`) REFERENCES `sanksi` (`id`),
  ADD CONSTRAINT `pelanggaran_ibfk_3` FOREIGN KEY (`decided_by`) REFERENCES `users` (`id`);

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
-- Ketidakleluasaan untuk tabel `positions`
--
ALTER TABLE `positions`
  ADD CONSTRAINT `positions_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`);

--
-- Ketidakleluasaan untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori_produk` (`id`),
  ADD CONSTRAINT `produk_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `promos`
--
ALTER TABLE `promos`
  ADD CONSTRAINT `promos_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`);

--
-- Ketidakleluasaan untuk tabel `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD CONSTRAINT `purchase_order_items_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`);

--
-- Ketidakleluasaan untuk tabel `quality_check_criteria`
--
ALTER TABLE `quality_check_criteria`
  ADD CONSTRAINT `quality_check_criteria_ibfk_1` FOREIGN KEY (`inspection_id`) REFERENCES `quality_inspections` (`id`);

--
-- Ketidakleluasaan untuk tabel `rapat`
--
ALTER TABLE `rapat`
  ADD CONSTRAINT `rapat_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `rapat_keputusan`
--
ALTER TABLE `rapat_keputusan`
  ADD CONSTRAINT `rapat_keputusan_ibfk_1` FOREIGN KEY (`rapat_id`) REFERENCES `rapat` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rapat_keputusan_ibfk_2` FOREIGN KEY (`pic`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `rapat_notulen`
--
ALTER TABLE `rapat_notulen`
  ADD CONSTRAINT `rapat_notulen_ibfk_1` FOREIGN KEY (`rapat_id`) REFERENCES `rapat` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rapat_notulen_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `rapat_peserta`
--
ALTER TABLE `rapat_peserta`
  ADD CONSTRAINT `rapat_peserta_ibfk_1` FOREIGN KEY (`rapat_id`) REFERENCES `rapat` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rapat_peserta_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `rat_attendance`
--
ALTER TABLE `rat_attendance`
  ADD CONSTRAINT `rat_attendance_ibfk_1` FOREIGN KEY (`rat_id`) REFERENCES `rat_meetings` (`id`),
  ADD CONSTRAINT `rat_attendance_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `cooperative_members` (`id`);

--
-- Ketidakleluasaan untuk tabel `rat_meetings`
--
ALTER TABLE `rat_meetings`
  ADD CONSTRAINT `rat_meetings_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `cooperative_structure` (`id`);

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
-- Ketidakleluasaan untuk tabel `regulatory_reports`
--
ALTER TABLE `regulatory_reports`
  ADD CONSTRAINT `regulatory_reports_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `cooperative_structure` (`id`);

--
-- Ketidakleluasaan untuk tabel `reserve_funds`
--
ALTER TABLE `reserve_funds`
  ADD CONSTRAINT `reserve_funds_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `cooperative_structure` (`id`);

--
-- Ketidakleluasaan untuk tabel `risk_alerts`
--
ALTER TABLE `risk_alerts`
  ADD CONSTRAINT `risk_alerts_ibfk_1` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `risk_alerts_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `service_subscriptions`
--
ALTER TABLE `service_subscriptions`
  ADD CONSTRAINT `service_subscriptions_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `shared_services` (`id`),
  ADD CONSTRAINT `service_subscriptions_ibfk_2` FOREIGN KEY (`subscriber_coop_id`) REFERENCES `cooperative_network` (`id`);

--
-- Ketidakleluasaan untuk tabel `settings`
--
ALTER TABLE `settings`
  ADD CONSTRAINT `settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `shipment_items`
--
ALTER TABLE `shipment_items`
  ADD CONSTRAINT `shipment_items_ibfk_1` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`);

--
-- Ketidakleluasaan untuk tabel `shipping_details`
--
ALTER TABLE `shipping_details`
  ADD CONSTRAINT `shipping_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `penjualan` (`id`) ON DELETE CASCADE;

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
-- Ketidakleluasaan untuk tabel `supplier_performance`
--
ALTER TABLE `supplier_performance`
  ADD CONSTRAINT `supplier_performance_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`);

--
-- Ketidakleluasaan untuk tabel `tax_filings`
--
ALTER TABLE `tax_filings`
  ADD CONSTRAINT `tax_filings_ibfk_1` FOREIGN KEY (`filed_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `tax_filings_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `tax_payments`
--
ALTER TABLE `tax_payments`
  ADD CONSTRAINT `tax_payments_ibfk_1` FOREIGN KEY (`paid_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `tax_payments_ibfk_2` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `trade_items`
--
ALTER TABLE `trade_items`
  ADD CONSTRAINT `trade_items_ibfk_1` FOREIGN KEY (`trade_id`) REFERENCES `inter_coop_trades` (`id`);

--
-- Ketidakleluasaan untuk tabel `transaksi_simpanan`
--
ALTER TABLE `transaksi_simpanan`
  ADD CONSTRAINT `transaksi_simpanan_ibfk_1` FOREIGN KEY (`simpanan_id`) REFERENCES `simpanan` (`id`),
  ADD CONSTRAINT `transaksi_simpanan_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `transparency_logs`
--
ALTER TABLE `transparency_logs`
  ADD CONSTRAINT `transparency_logs_ibfk_1` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `ux_experiment_results`
--
ALTER TABLE `ux_experiment_results`
  ADD CONSTRAINT `ux_experiment_results_ibfk_1` FOREIGN KEY (`experiment_id`) REFERENCES `ux_optimization_experiments` (`id`);

--
-- Ketidakleluasaan untuk tabel `warehouse_zones`
--
ALTER TABLE `warehouse_zones`
  ADD CONSTRAINT `warehouse_zones_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Ketidakleluasaan untuk tabel `withholding_tax`
--
ALTER TABLE `withholding_tax`
  ADD CONSTRAINT `withholding_tax_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
