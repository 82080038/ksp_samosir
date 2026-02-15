/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.6.23-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: coop_db
-- ------------------------------------------------------
-- Server version	10.6.23-MariaDB-0ubuntu0.22.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `agent_sales`
--

DROP TABLE IF EXISTS `agent_sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `agent_sales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agent_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `commission` decimal(15,2) NOT NULL,
  `approved` tinyint(1) DEFAULT 0,
  `approved_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `agent_id` (`agent_id`),
  KEY `approved_by` (`approved_by`),
  CONSTRAINT `agent_sales_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `agent_sales_ibfk_2` FOREIGN KEY (`agent_id`) REFERENCES `anggota` (`id`),
  CONSTRAINT `agent_sales_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `people_db`.`users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `agent_sales`
--

LOCK TABLES `agent_sales` WRITE;
/*!40000 ALTER TABLE `agent_sales` DISABLE KEYS */;
/*!40000 ALTER TABLE `agent_sales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `anggota`
--

DROP TABLE IF EXISTS `anggota`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `anggota` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `status_keanggotaan` enum('active','inactive','suspended') DEFAULT 'active',
  `nomor_anggota` varchar(20) NOT NULL,
  `joined_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `anggota_status_id` int(10) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nomor_anggota` (`nomor_anggota`),
  KEY `idx_anggota_user` (`user_id`),
  KEY `idx_anggota_status_id` (`anggota_status_id`),
  CONSTRAINT `anggota_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `people_db`.`users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_anggota_status` FOREIGN KEY (`anggota_status_id`) REFERENCES `anggota_status` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `anggota`
--

LOCK TABLES `anggota` WRITE;
/*!40000 ALTER TABLE `anggota` DISABLE KEYS */;
/*!40000 ALTER TABLE `anggota` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `anggota_status`
--

DROP TABLE IF EXISTS `anggota_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `anggota_status` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(32) NOT NULL,
  `label` varchar(64) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `anggota_status`
--

LOCK TABLES `anggota_status` WRITE;
/*!40000 ALTER TABLE `anggota_status` DISABLE KEYS */;
INSERT INTO `anggota_status` VALUES (1,'AKTIF','AKTIF',1,'2026-02-05 20:15:23','2026-02-05 20:15:23'),(2,'PENDING','PENDING',1,'2026-02-05 20:15:23','2026-02-05 20:15:23'),(3,'NONAKTIF','NON AKTIF',1,'2026-02-05 20:15:23','2026-02-05 20:15:23');
/*!40000 ALTER TABLE `anggota_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_name` varchar(50) NOT NULL,
  `record_id` int(11) NOT NULL,
  `field_name` varchar(50) NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_table_record` (`table_name`,`record_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `people_db`.`users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chart_of_accounts`
--

DROP TABLE IF EXISTS `chart_of_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `chart_of_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cooperative_id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('asset','liability','equity','revenue','expense') NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cooperative_code` (`cooperative_id`,`code`),
  KEY `parent_id` (`parent_id`),
  KEY `idx_chart_cooperative` (`cooperative_id`),
  CONSTRAINT `chart_of_accounts_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `chart_of_accounts` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chart_of_accounts`
--

LOCK TABLES `chart_of_accounts` WRITE;
/*!40000 ALTER TABLE `chart_of_accounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `chart_of_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `configs`
--

DROP TABLE IF EXISTS `configs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `configs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key_name` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_name` (`key_name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configs`
--

LOCK TABLES `configs` WRITE;
/*!40000 ALTER TABLE `configs` DISABLE KEYS */;
/*!40000 ALTER TABLE `configs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cooperative_document_history`
--

DROP TABLE IF EXISTS `cooperative_document_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cooperative_document_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cooperative_id` int(11) NOT NULL,
  `document_type` enum('nomor_bh','nib','nik_koperasi','modal_pokok') NOT NULL,
  `document_number_lama` varchar(50) DEFAULT NULL,
  `document_number_baru` varchar(50) DEFAULT NULL,
  `document_value_lama` decimal(15,2) DEFAULT NULL,
  `document_value_baru` decimal(15,2) DEFAULT NULL,
  `tanggal_efektif` date NOT NULL,
  `change_reason` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_cooperative_document` (`cooperative_id`,`document_type`),
  KEY `idx_tanggal_efektif` (`tanggal_efektif`),
  KEY `idx_document_type` (`document_type`),
  CONSTRAINT `cooperative_document_history_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `cooperatives` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cooperative_document_history`
--

LOCK TABLES `cooperative_document_history` WRITE;
/*!40000 ALTER TABLE `cooperative_document_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `cooperative_document_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cooperative_financial_settings`
--

DROP TABLE IF EXISTS `cooperative_financial_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cooperative_financial_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cooperative_id` int(11) NOT NULL,
  `tahun_buku` year(4) NOT NULL,
  `periode_mulai` date NOT NULL,
  `periode_akhir` date NOT NULL,
  `simpanan_pokok` decimal(15,2) DEFAULT 0.00,
  `simpanan_wajib` decimal(15,2) DEFAULT 0.00,
  `bunga_pinjaman` decimal(5,2) DEFAULT 12.00,
  `denda_telat` decimal(5,2) DEFAULT 2.00,
  `periode_shu` enum('yearly','semi_annual','quarterly') DEFAULT 'yearly',
  `status` enum('active','inactive','closed') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_cooperative_year` (`cooperative_id`,`tahun_buku`),
  KEY `created_by` (`created_by`),
  KEY `idx_cooperative_year` (`cooperative_id`,`tahun_buku`),
  KEY `idx_tahun_buku` (`tahun_buku`),
  CONSTRAINT `cooperative_financial_settings_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `cooperatives` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cooperative_financial_settings_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cooperative_financial_settings`
--

LOCK TABLES `cooperative_financial_settings` WRITE;
/*!40000 ALTER TABLE `cooperative_financial_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `cooperative_financial_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cooperative_status_history`
--

DROP TABLE IF EXISTS `cooperative_status_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cooperative_status_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cooperative_id` int(11) NOT NULL,
  `status_sebelumnya` varchar(50) DEFAULT NULL,
  `status_baru` varchar(50) NOT NULL,
  `tanggal_efektif` date DEFAULT NULL,
  `dokumen_path` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `change_reason` varchar(255) DEFAULT NULL,
  `approval_status` enum('pending','approved','rejected') DEFAULT 'approved',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cooperative_id` (`cooperative_id`),
  KEY `idx_tanggal_efektif` (`tanggal_efektif`),
  KEY `idx_approval_status` (`approval_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cooperative_status_history`
--

LOCK TABLES `cooperative_status_history` WRITE;
/*!40000 ALTER TABLE `cooperative_status_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `cooperative_status_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cooperative_types`
--

DROP TABLE IF EXISTS `cooperative_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cooperative_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `code` varchar(20) DEFAULT NULL,
  `category` enum('finansial','produksi','jasa','konsumsi','serba_usaha','karyawan') DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cooperative_types`
--

LOCK TABLES `cooperative_types` WRITE;
/*!40000 ALTER TABLE `cooperative_types` DISABLE KEYS */;
/*!40000 ALTER TABLE `cooperative_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cooperatives`
--

DROP TABLE IF EXISTS `cooperatives`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cooperatives` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `jenis` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`jenis`)),
  `badan_hukum` varchar(255) DEFAULT NULL,
  `status_badan_hukum` enum('belum_terdaftar','terdaftar','badan_hukum') DEFAULT 'belum_terdaftar',
  `tanggal_status_terakhir` date DEFAULT NULL,
  `status_notes` text DEFAULT NULL,
  `tanggal_pendirian` date DEFAULT NULL,
  `npwp` varchar(50) DEFAULT NULL,
  `nomor_bh` varchar(50) DEFAULT NULL,
  `nib` varchar(20) DEFAULT NULL,
  `nik_koperasi` varchar(20) DEFAULT NULL,
  `modal_pokok` decimal(15,2) DEFAULT 0.00,
  `alamat_legal` text DEFAULT NULL,
  `kontak_resmi` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `province_id` int(11) DEFAULT NULL,
  `regency_id` int(11) DEFAULT NULL,
  `district_id` int(11) DEFAULT NULL,
  `village_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cooperative_province` (`province_id`),
  KEY `idx_cooperative_regency` (`regency_id`),
  KEY `idx_cooperative_district` (`district_id`),
  KEY `idx_cooperative_village` (`village_id`),
  KEY `idx_nomor_bh` (`nomor_bh`),
  KEY `idx_nib` (`nib`),
  KEY `idx_nik_koperasi` (`nik_koperasi`),
  KEY `idx_status_badan_hukum` (`status_badan_hukum`),
  KEY `idx_tanggal_status_terakhir` (`tanggal_status_terakhir`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cooperatives`
--

LOCK TABLES `cooperatives` WRITE;
/*!40000 ALTER TABLE `cooperatives` DISABLE KEYS */;
INSERT INTO `cooperatives` VALUES (5,'KSP POLRES SAMOSIR','{\"jenis\":\"koperasi simpan pinjam\"}',NULL,'terdaftar',NULL,NULL,'2024-01-01','0012345678901001',NULL,'9120101234567','3171011001900001',0.00,'jl. danau toba no. 03','081265511982',NULL,NULL,'2026-02-05 20:35:56','2026-02-05 20:35:56',3,40,590,10617);
/*!40000 ALTER TABLE `cooperatives` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `general_ledger`
--

DROP TABLE IF EXISTS `general_ledger`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `general_ledger` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `period` date NOT NULL,
  `beginning_balance` decimal(15,2) DEFAULT 0.00,
  `debit_total` decimal(15,2) DEFAULT 0.00,
  `credit_total` decimal(15,2) DEFAULT 0.00,
  `ending_balance` decimal(15,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_id` (`account_id`,`period`),
  CONSTRAINT `general_ledger_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `chart_of_accounts` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `general_ledger`
--

LOCK TABLES `general_ledger` WRITE;
/*!40000 ALTER TABLE `general_ledger` DISABLE KEYS */;
/*!40000 ALTER TABLE `general_ledger` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `journal_entries`
--

DROP TABLE IF EXISTS `journal_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `journal_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entry_date` date NOT NULL,
  `description` text NOT NULL,
  `reference_number` varchar(50) DEFAULT NULL,
  `status` enum('draft','posted') DEFAULT 'draft',
  `posted_by` int(11) DEFAULT NULL,
  `posted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_journal_posted_by` (`posted_by`),
  CONSTRAINT `fk_journal_posted_by` FOREIGN KEY (`posted_by`) REFERENCES `people_db`.`users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `journal_entries`
--

LOCK TABLES `journal_entries` WRITE;
/*!40000 ALTER TABLE `journal_entries` DISABLE KEYS */;
/*!40000 ALTER TABLE `journal_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `journal_entry_details`
--

DROP TABLE IF EXISTS `journal_entry_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `journal_entry_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `journal_entry_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `debit` decimal(15,2) DEFAULT 0.00,
  `credit` decimal(15,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `journal_entry_id` (`journal_entry_id`),
  KEY `account_id` (`account_id`),
  CONSTRAINT `journal_entry_details_ibfk_1` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`id`),
  CONSTRAINT `journal_entry_details_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `chart_of_accounts` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `journal_entry_details`
--

LOCK TABLES `journal_entry_details` WRITE;
/*!40000 ALTER TABLE `journal_entry_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `journal_entry_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `member_shu`
--

DROP TABLE IF EXISTS `member_shu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `member_shu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `anggota_id` int(11) NOT NULL,
  `shu_distribution_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `paid` tinyint(1) DEFAULT 0,
  `paid_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `anggota_id` (`anggota_id`),
  KEY `shu_distribution_id` (`shu_distribution_id`),
  CONSTRAINT `member_shu_ibfk_1` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`),
  CONSTRAINT `member_shu_ibfk_2` FOREIGN KEY (`shu_distribution_id`) REFERENCES `shu_distributions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `member_shu`
--

LOCK TABLES `member_shu` WRITE;
/*!40000 ALTER TABLE `member_shu` DISABLE KEYS */;
/*!40000 ALTER TABLE `member_shu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modal_pokok_changes`
--

DROP TABLE IF EXISTS `modal_pokok_changes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `modal_pokok_changes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cooperative_id` int(11) NOT NULL,
  `modal_pokok_lama` decimal(15,2) NOT NULL,
  `modal_pokok_baru` decimal(15,2) NOT NULL,
  `persentase_perubahan` decimal(5,2) NOT NULL,
  `tanggal_efektif` date NOT NULL,
  `perubahan_type` enum('manual','rat','other') NOT NULL,
  `referensi_id` int(11) DEFAULT NULL,
  `alasan_perubahan` text DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `referensi_id` (`referensi_id`),
  KEY `idx_cooperative_date` (`cooperative_id`,`tanggal_efektif`),
  KEY `idx_perubahan_type` (`perubahan_type`),
  KEY `idx_tanggal_efektif` (`tanggal_efektif`),
  CONSTRAINT `modal_pokok_changes_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `cooperatives` (`id`) ON DELETE CASCADE,
  CONSTRAINT `modal_pokok_changes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `modal_pokok_changes_ibfk_3` FOREIGN KEY (`referensi_id`) REFERENCES `rat_sessions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modal_pokok_changes`
--

LOCK TABLES `modal_pokok_changes` WRITE;
/*!40000 ALTER TABLE `modal_pokok_changes` DISABLE KEYS */;
/*!40000 ALTER TABLE `modal_pokok_changes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','warning','error') DEFAULT 'info',
  `sent_at` timestamp NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_notifications_user_read` (`user_id`,`read_at`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `people_db`.`users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_details`
--

DROP TABLE IF EXISTS `order_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `total` decimal(15,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_details`
--

LOCK TABLES `order_details` WRITE;
/*!40000 ALTER TABLE `order_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) DEFAULT NULL,
  `order_date` timestamp NULL DEFAULT current_timestamp(),
  `total_amount` decimal(15,2) NOT NULL,
  `status` enum('pending','confirmed','shipped','delivered','cancelled') DEFAULT 'pending',
  `shipping_address` text DEFAULT NULL,
  `payment_status` enum('unpaid','paid','refunded') DEFAULT 'unpaid',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengawas`
--

DROP TABLE IF EXISTS `pengawas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengawas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `jabatan` varchar(100) NOT NULL,
  `periode_start` date NOT NULL,
  `periode_end` date DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pengawas_user` (`user_id`),
  CONSTRAINT `pengawas_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `people_db`.`users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengawas`
--

LOCK TABLES `pengawas` WRITE;
/*!40000 ALTER TABLE `pengawas` DISABLE KEYS */;
/*!40000 ALTER TABLE `pengawas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengurus`
--

DROP TABLE IF EXISTS `pengurus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengurus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `jabatan` varchar(100) NOT NULL,
  `periode_start` date NOT NULL,
  `periode_end` date DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pengurus_user` (`user_id`),
  CONSTRAINT `pengurus_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `people_db`.`users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengurus`
--

LOCK TABLES `pengurus` WRITE;
/*!40000 ALTER TABLE `pengurus` DISABLE KEYS */;
/*!40000 ALTER TABLE `pengurus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pinjaman`
--

DROP TABLE IF EXISTS `pinjaman`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pinjaman` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `anggota_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `interest_rate` decimal(5,2) NOT NULL,
  `term_months` int(11) NOT NULL,
  `status` enum('pending','approved','active','paid','rejected') DEFAULT 'pending',
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `disbursed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `approved_by` (`approved_by`),
  KEY `idx_pinjaman_anggota` (`anggota_id`),
  KEY `idx_pinjaman_status` (`status`),
  CONSTRAINT `pinjaman_ibfk_1` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`),
  CONSTRAINT `pinjaman_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `people_db`.`users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pinjaman`
--

LOCK TABLES `pinjaman` WRITE;
/*!40000 ALTER TABLE `pinjaman` DISABLE KEYS */;
/*!40000 ALTER TABLE `pinjaman` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pinjaman_angsuran`
--

DROP TABLE IF EXISTS `pinjaman_angsuran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pinjaman_angsuran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pinjaman_id` int(11) NOT NULL,
  `installment_number` int(11) NOT NULL,
  `due_date` date NOT NULL,
  `principal_amount` decimal(15,2) NOT NULL,
  `interest_amount` decimal(15,2) NOT NULL,
  `total_amount` decimal(15,2) NOT NULL,
  `paid_amount` decimal(15,2) DEFAULT 0.00,
  `paid_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','paid','overdue') DEFAULT 'pending',
  `penalty` decimal(15,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `pinjaman_id` (`pinjaman_id`),
  CONSTRAINT `pinjaman_angsuran_ibfk_1` FOREIGN KEY (`pinjaman_id`) REFERENCES `pinjaman` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pinjaman_angsuran`
--

LOCK TABLES `pinjaman_angsuran` WRITE;
/*!40000 ALTER TABLE `pinjaman_angsuran` DISABLE KEYS */;
/*!40000 ALTER TABLE `pinjaman_angsuran` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(15,2) NOT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `category` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rat_sessions`
--

DROP TABLE IF EXISTS `rat_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rat_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cooperative_id` int(11) NOT NULL,
  `tahun` int(11) NOT NULL,
  `tanggal_rapat` date NOT NULL,
  `tempat` varchar(255) DEFAULT NULL,
  `agenda` text DEFAULT NULL,
  `status` enum('scheduled','in_progress','completed','cancelled') DEFAULT 'scheduled',
  `modal_pokok_sebelum` decimal(15,2) DEFAULT 0.00,
  `modal_pokok_setelah` decimal(15,2) DEFAULT 0.00,
  `persentase_perubahan` decimal(5,2) DEFAULT 0.00,
  `alasan_perubahan` text DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `approved_by` (`approved_by`),
  KEY `idx_cooperative_tahun` (`cooperative_id`,`tahun`),
  KEY `idx_tanggal_rapat` (`tanggal_rapat`),
  KEY `idx_status` (`status`),
  CONSTRAINT `rat_sessions_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `cooperatives` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rat_sessions_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rat_sessions`
--

LOCK TABLES `rat_sessions` WRITE;
/*!40000 ALTER TABLE `rat_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `rat_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `assigned_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (6,'super_admin','Super Administrator','2026-02-05 18:17:58'),(7,'admin','Administrator','2026-02-05 18:17:58'),(8,'pengawas','Supervisor','2026-02-05 18:17:58'),(9,'anggota','Member','2026-02-05 18:17:58');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shu_distributions`
--

DROP TABLE IF EXISTS `shu_distributions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `shu_distributions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `year` year(4) NOT NULL,
  `total_shu` decimal(15,2) NOT NULL,
  `distributed_at` timestamp NULL DEFAULT NULL,
  `status` enum('calculated','distributed') DEFAULT 'calculated',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shu_distributions`
--

LOCK TABLES `shu_distributions` WRITE;
/*!40000 ALTER TABLE `shu_distributions` DISABLE KEYS */;
/*!40000 ALTER TABLE `shu_distributions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `simpanan_transactions`
--

DROP TABLE IF EXISTS `simpanan_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `simpanan_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `anggota_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `transaction_type` enum('deposit','withdraw') NOT NULL,
  `balance_after` decimal(15,2) NOT NULL,
  `transaction_date` timestamp NULL DEFAULT current_timestamp(),
  `description` text DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `type_id` (`type_id`),
  KEY `approved_by` (`approved_by`),
  KEY `idx_simpanan_anggota` (`anggota_id`),
  KEY `idx_simpanan_date` (`transaction_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `simpanan_transactions`
--

LOCK TABLES `simpanan_transactions` WRITE;
/*!40000 ALTER TABLE `simpanan_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `simpanan_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `simpanan_types`
--

DROP TABLE IF EXISTS `simpanan_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `simpanan_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `interest_rate` decimal(5,2) DEFAULT 0.00,
  `minimum_balance` decimal(15,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `simpanan_types`
--

LOCK TABLES `simpanan_types` WRITE;
/*!40000 ALTER TABLE `simpanan_types` DISABLE KEYS */;
/*!40000 ALTER TABLE `simpanan_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenant_configs`
--

DROP TABLE IF EXISTS `tenant_configs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tenant_configs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cooperative_id` int(11) NOT NULL,
  `active_modules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`active_modules`)),
  `feature_flags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`feature_flags`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `cooperative_id` (`cooperative_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenant_configs`
--

LOCK TABLES `tenant_configs` WRITE;
/*!40000 ALTER TABLE `tenant_configs` DISABLE KEYS */;
/*!40000 ALTER TABLE `tenant_configs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_roles`
--

DROP TABLE IF EXISTS `user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `assigned_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_roles`
--

LOCK TABLES `user_roles` WRITE;
/*!40000 ALTER TABLE `user_roles` DISABLE KEYS */;
INSERT INTO `user_roles` VALUES (6,6,'2026-02-05 18:17:58');
/*!40000 ALTER TABLE `user_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `user_db_id` int(11) NOT NULL,
  `status` enum('active','inactive','pending') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `id_koperasi` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `idx_users_user_db_id` (`user_db_id`),
  KEY `idx_users_id_koperasi` (`id_koperasi`),
  CONSTRAINT `fk_users_cooperative` FOREIGN KEY (`id_koperasi`) REFERENCES `cooperatives` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (6,'820800','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',1,'active','2026-02-05 18:17:43','2026-02-05 20:38:16',5);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `v_cooperative_complete`
--

DROP TABLE IF EXISTS `v_cooperative_complete`;
/*!50001 DROP VIEW IF EXISTS `v_cooperative_complete`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `v_cooperative_complete` AS SELECT
 1 AS `id`,
  1 AS `nama`,
  1 AS `jenis`,
  1 AS `badan_hukum`,
  1 AS `tanggal_pendirian`,
  1 AS `npwp`,
  1 AS `alamat_legal`,
  1 AS `kontak_resmi`,
  1 AS `logo`,
  1 AS `created_by`,
  1 AS `created_at`,
  1 AS `updated_at`,
  1 AS `province_name`,
  1 AS `regency_name`,
  1 AS `district_name`,
  1 AS `village_name`,
  1 AS `admin_name`,
  1 AS `admin_email`,
  1 AS `admin_phone` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `vote_ballots`
--

DROP TABLE IF EXISTS `vote_ballots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vote_ballots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vote_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `choice` varchar(100) NOT NULL,
  `voted_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_vote_ballots_vote_user` (`vote_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vote_ballots`
--

LOCK TABLES `vote_ballots` WRITE;
/*!40000 ALTER TABLE `vote_ballots` DISABLE KEYS */;
/*!40000 ALTER TABLE `vote_ballots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `votes`
--

DROP TABLE IF EXISTS `votes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `votes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agenda` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `status` enum('draft','active','closed') DEFAULT 'draft',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_votes_created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `votes`
--

LOCK TABLES `votes` WRITE;
/*!40000 ALTER TABLE `votes` DISABLE KEYS */;
/*!40000 ALTER TABLE `votes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'coop_db'
--

--
-- Final view structure for view `v_cooperative_complete`
--

/*!50001 DROP VIEW IF EXISTS `v_cooperative_complete`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb3 */;
/*!50001 SET character_set_results     = utf8mb3 */;
/*!50001 SET collation_connection      = utf8mb3_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_cooperative_complete` AS select `c`.`id` AS `id`,`c`.`nama` AS `nama`,`c`.`jenis` AS `jenis`,`c`.`badan_hukum` AS `badan_hukum`,`c`.`tanggal_pendirian` AS `tanggal_pendirian`,`c`.`npwp` AS `npwp`,`c`.`alamat_legal` AS `alamat_legal`,`c`.`kontak_resmi` AS `kontak_resmi`,`c`.`logo` AS `logo`,`c`.`created_by` AS `created_by`,`c`.`created_at` AS `created_at`,`c`.`updated_at` AS `updated_at`,`p`.`name` AS `province_name`,`r`.`name` AS `regency_name`,`d`.`name` AS `district_name`,`v`.`name` AS `village_name`,`u`.`nama` AS `admin_name`,`u`.`email` AS `admin_email`,`u`.`phone` AS `admin_phone` from ((((((`coop_db`.`cooperatives` `c` left join `alamat_db`.`provinces` `p` on(`c`.`province_id` = `p`.`id`)) left join `alamat_db`.`regencies` `r` on(`c`.`regency_id` = `r`.`id`)) left join `alamat_db`.`districts` `d` on(`c`.`district_id` = `d`.`id`)) left join `alamat_db`.`villages` `v` on(`c`.`village_id` = `v`.`id`)) left join `coop_db`.`users` `cu` on(`c`.`created_by` = `cu`.`id`)) left join `people_db`.`users` `u` on(`cu`.`user_db_id` = `u`.`id`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-15 17:38:35
