/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.14-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: ksp_samosir
-- ------------------------------------------------------
-- Server version	10.11.14-MariaDB-0ubuntu0.24.04.1

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
-- Table structure for table `ab_test_participants`
--

DROP TABLE IF EXISTS `ab_test_participants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ab_test_participants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `test_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `assigned_variant` char(1) NOT NULL,
  `participated_at` datetime DEFAULT current_timestamp(),
  `converted` tinyint(1) DEFAULT 0,
  `conversion_value` decimal(10,2) DEFAULT 0.00,
  `session_duration` int(11) DEFAULT NULL,
  `page_views` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_participation` (`test_id`,`user_id`),
  KEY `idx_test` (`test_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_variant` (`assigned_variant`),
  CONSTRAINT `ab_test_participants_ibfk_1` FOREIGN KEY (`test_id`) REFERENCES `ab_tests` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ab_test_results`
--

DROP TABLE IF EXISTS `ab_test_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ab_test_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `test_id` int(11) NOT NULL,
  `variant` varchar(10) NOT NULL,
  `participants` int(11) DEFAULT 0,
  `conversions` int(11) DEFAULT 0,
  `conversion_rate` decimal(5,2) DEFAULT 0.00,
  `measured_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_test_variant` (`test_id`,`variant`),
  CONSTRAINT `ab_test_results_ibfk_1` FOREIGN KEY (`test_id`) REFERENCES `ab_tests` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ab_tests`
--

DROP TABLE IF EXISTS `ab_tests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ab_tests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `test_name` varchar(100) NOT NULL,
  `test_group` varchar(50) NOT NULL,
  `variant_a` varchar(100) NOT NULL,
  `variant_b` varchar(100) NOT NULL,
  `winner` varchar(10) DEFAULT NULL,
  `confidence_level` decimal(5,2) DEFAULT 0.00,
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `status` enum('running','completed','cancelled') DEFAULT 'running',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_name_status` (`test_name`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `access_logs`
--

DROP TABLE IF EXISTS `access_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `access_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `request_method` varchar(10) NOT NULL,
  `request_uri` varchar(500) NOT NULL,
  `response_code` int(11) NOT NULL,
  `response_time` decimal(5,2) DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_ip` (`user_id`,`ip_address`),
  KEY `idx_response_code` (`response_code`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `address_stats`
--

DROP TABLE IF EXISTS `address_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `address_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `total_provinces` int(11) DEFAULT 0,
  `total_regencies` int(11) DEFAULT 0,
  `total_districts` int(11) DEFAULT 0,
  `total_villages` int(11) DEFAULT 0,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `addresses`
--

DROP TABLE IF EXISTS `addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ai_conversations`
--

DROP TABLE IF EXISTS `ai_conversations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_conversations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `conversation_type` enum('support','guidance','transaction','general') DEFAULT 'general',
  `status` enum('active','completed','transferred') DEFAULT 'active',
  `started_at` datetime DEFAULT current_timestamp(),
  `last_message_at` datetime DEFAULT current_timestamp(),
  `resolved` tinyint(1) DEFAULT 0,
  `satisfaction_rating` decimal(2,1) DEFAULT NULL,
  `transferred_to` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_session` (`session_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_started_at` (`started_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ai_decisions`
--

DROP TABLE IF EXISTS `ai_decisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_decisions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `decision_type` varchar(100) NOT NULL,
  `confidence_score` decimal(5,2) DEFAULT 0.00,
  `status` enum('automated','manual_review','rejected') DEFAULT 'automated',
  `input_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`input_data`)),
  `output_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`output_data`)),
  `processing_time` decimal(5,2) DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type_status` (`decision_type`,`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ai_ethics_monitoring`
--

DROP TABLE IF EXISTS `ai_ethics_monitoring`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_ethics_monitoring` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model_name` varchar(100) NOT NULL,
  `bias_check_type` varchar(50) NOT NULL,
  `bias_score` decimal(5,4) DEFAULT 0.0000,
  `affected_groups` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`affected_groups`)),
  `mitigation_actions` text DEFAULT NULL,
  `compliance_status` enum('compliant','review_required','non_compliant') DEFAULT 'compliant',
  `checked_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_model` (`model_name`),
  KEY `idx_type` (`bias_check_type`),
  KEY `idx_status` (`compliance_status`),
  KEY `idx_checked` (`checked_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ai_fraud_alerts`
--

DROP TABLE IF EXISTS `ai_fraud_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_fraud_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `resolved_by` (`resolved_by`),
  KEY `created_by` (`created_by`),
  KEY `idx_ai_fraud_alerts_type` (`alert_type`),
  KEY `idx_ai_fraud_alerts_risk` (`risk_level`),
  KEY `idx_ai_fraud_alerts_status` (`status`),
  KEY `idx_ai_fraud_alerts_created` (`created_at`),
  CONSTRAINT `ai_fraud_alerts_ibfk_1` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`),
  CONSTRAINT `ai_fraud_alerts_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ai_fraud_models`
--

DROP TABLE IF EXISTS `ai_fraud_models`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_fraud_models` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model_name` varchar(100) NOT NULL,
  `model_type` varchar(50) NOT NULL,
  `training_data_size` int(11) DEFAULT 0,
  `accuracy_score` decimal(5,4) DEFAULT 0.0000,
  `false_positive_rate` decimal(5,4) DEFAULT 0.0000,
  `false_negative_rate` decimal(5,4) DEFAULT 0.0000,
  `last_trained` datetime DEFAULT NULL,
  `status` enum('active','training','inactive') DEFAULT 'inactive',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type` (`model_type`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ai_messages`
--

DROP TABLE IF EXISTS `ai_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `conversation_id` int(11) NOT NULL,
  `sender` enum('user','ai','agent') DEFAULT 'user',
  `message_type` enum('text','option','form','file') DEFAULT 'text',
  `content` text NOT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `confidence_score` decimal(5,4) DEFAULT NULL,
  `intent_detected` varchar(100) DEFAULT NULL,
  `entities_detected` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`entities_detected`)),
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_conversation` (`conversation_id`),
  KEY `idx_sender` (`sender`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `ai_messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `ai_conversations` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ai_model_data`
--

DROP TABLE IF EXISTS `ai_model_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_model_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model_type` varchar(50) NOT NULL,
  `training_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`training_data`)),
  `model_parameters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`model_parameters`)),
  `accuracy_score` decimal(5,2) DEFAULT NULL,
  `last_trained` timestamp NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ai_model_data_type` (`model_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ai_model_performance`
--

DROP TABLE IF EXISTS `ai_model_performance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_model_performance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model_name` varchar(100) NOT NULL,
  `metric_type` varchar(50) NOT NULL,
  `metric_value` decimal(10,4) NOT NULL,
  `test_period_start` date DEFAULT NULL,
  `test_period_end` date DEFAULT NULL,
  `improvement_percentage` decimal(5,2) DEFAULT NULL,
  `recorded_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_model` (`model_name`),
  KEY `idx_metric` (`metric_type`),
  KEY `idx_recorded` (`recorded_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ai_recommendations`
--

DROP TABLE IF EXISTS `ai_recommendations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_recommendations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `recommendation_score` decimal(5,2) DEFAULT NULL,
  `recommendation_reason` varchar(255) DEFAULT NULL,
  `was_purchased` tinyint(1) DEFAULT 0,
  `purchased_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ai_recommendations_user` (`user_id`),
  KEY `idx_ai_recommendations_product` (`product_id`),
  KEY `idx_ai_recommendations_created` (`created_at`),
  CONSTRAINT `ai_recommendations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `ai_recommendations_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `produk` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ai_training_data`
--

DROP TABLE IF EXISTS `ai_training_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_training_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `intent` varchar(100) NOT NULL,
  `utterance` text NOT NULL,
  `response` text NOT NULL,
  `context` varchar(100) DEFAULT NULL,
  `confidence_threshold` decimal(5,4) DEFAULT 0.8000,
  `usage_count` int(11) DEFAULT 0,
  `success_rate` decimal(5,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_intent` (`intent`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `alerts`
--

DROP TABLE IF EXISTS `alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_alerts_status` (`status`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `anggota`
--

DROP TABLE IF EXISTS `anggota`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `anggota` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `primary_contact_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_anggota` (`no_anggota`),
  UNIQUE KEY `nik` (`nik`),
  KEY `idx_anggota_no_anggota` (`no_anggota`),
  KEY `idx_anggota_created_by` (`created_by`),
  KEY `idx_anggota_province` (`province_id`),
  KEY `idx_anggota_regency` (`regency_id`),
  KEY `idx_anggota_district` (`district_id`),
  KEY `idx_anggota_village` (`village_id`),
  KEY `address_id` (`address_id`),
  KEY `primary_contact_id` (`primary_contact_id`),
  CONSTRAINT `anggota_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `anggota_ibfk_2` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`),
  CONSTRAINT `anggota_ibfk_3` FOREIGN KEY (`primary_contact_id`) REFERENCES `contacts` (`id`),
  CONSTRAINT `fk_anggota_district` FOREIGN KEY (`district_id`) REFERENCES `ref_districts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_anggota_province` FOREIGN KEY (`province_id`) REFERENCES `ref_provinces` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_anggota_regency` FOREIGN KEY (`regency_id`) REFERENCES `ref_regencies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_anggota_village` FOREIGN KEY (`village_id`) REFERENCES `ref_villages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `angsuran`
--

DROP TABLE IF EXISTS `angsuran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `angsuran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_angsuran_pinjaman` (`pinjaman_id`),
  KEY `idx_angsuran_status` (`status`),
  KEY `idx_angsuran_jatuh_tempo` (`tanggal_jatuh_tempo`),
  CONSTRAINT `angsuran_ibfk_1` FOREIGN KEY (`pinjaman_id`) REFERENCES `pinjaman` (`id`),
  CONSTRAINT `angsuran_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `app_sessions`
--

DROP TABLE IF EXISTS `app_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `app_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `device_id` varchar(255) DEFAULT NULL,
  `platform` enum('ios','android','web') DEFAULT 'web',
  `app_version` varchar(20) DEFAULT NULL,
  `session_start` datetime DEFAULT current_timestamp(),
  `session_end` datetime DEFAULT NULL,
  `duration` int(11) DEFAULT 0,
  `features_used` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features_used`)),
  PRIMARY KEY (`id`),
  KEY `idx_user_device` (`user_id`,`device_id`),
  KEY `idx_platform` (`platform`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `application_logs`
--

DROP TABLE IF EXISTS `application_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `log_level` enum('debug','info','warning','error','critical') DEFAULT 'info',
  `category` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `context` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`context`)),
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `logged_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_application_logs_level` (`log_level`,`logged_at`),
  KEY `idx_application_logs_category` (`category`,`logged_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `asset_depreciation`
--

DROP TABLE IF EXISTS `asset_depreciation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_depreciation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL,
  `depreciation_date` date NOT NULL,
  `depreciation_amount` decimal(15,2) NOT NULL,
  `accumulated_depreciation` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_asset_depreciation_asset_id` (`asset_id`),
  KEY `idx_asset_depreciation_date` (`depreciation_date`),
  CONSTRAINT `asset_depreciation_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `fixed_assets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `asset_disposals`
--

DROP TABLE IF EXISTS `asset_disposals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_disposals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL,
  `disposal_date` date NOT NULL,
  `disposal_method` enum('sale','scrap','donation','loss') NOT NULL,
  `disposal_value` decimal(15,2) DEFAULT 0.00,
  `reason` text DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `approved_by` (`approved_by`),
  KEY `recorded_by` (`recorded_by`),
  KEY `idx_asset_disposals_asset_id` (`asset_id`),
  KEY `idx_asset_disposals_date` (`disposal_date`),
  CONSTRAINT `asset_disposals_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `fixed_assets` (`id`),
  CONSTRAINT `asset_disposals_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  CONSTRAINT `asset_disposals_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `asset_holdings`
--

DROP TABLE IF EXISTS `asset_holdings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_holdings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wallet_id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `balance` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `locked_balance` decimal(20,8) DEFAULT 0.00000000,
  `last_transaction_hash` varchar(100) DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_holding` (`wallet_id`,`asset_id`),
  KEY `idx_wallet` (`wallet_id`),
  KEY `idx_asset` (`asset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `asset_maintenance`
--

DROP TABLE IF EXISTS `asset_maintenance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_maintenance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL,
  `maintenance_date` date NOT NULL,
  `maintenance_type` enum('preventive','corrective','predictive','major_repair','inspection') NOT NULL,
  `description` text NOT NULL,
  `cost` decimal(15,2) DEFAULT 0.00,
  `performed_by` varchar(255) DEFAULT NULL,
  `next_maintenance_date` date DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `recorded_by` (`recorded_by`),
  KEY `idx_asset_maintenance_asset_id` (`asset_id`),
  KEY `idx_asset_maintenance_date` (`maintenance_date`),
  KEY `idx_asset_maintenance_type` (`maintenance_type`),
  CONSTRAINT `asset_maintenance_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `fixed_assets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `asset_maintenance_ibfk_2` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `automated_processes`
--

DROP TABLE IF EXISTS `automated_processes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `automated_processes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `process_name` varchar(100) NOT NULL,
  `process_type` varchar(50) NOT NULL,
  `status` enum('pending','running','completed','failed') DEFAULT 'pending',
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `duration` int(11) DEFAULT 0,
  `records_processed` int(11) DEFAULT 0,
  `success_rate` decimal(5,2) DEFAULT 0.00,
  `error_message` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type_status` (`process_type`,`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_automated_recent` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `b2b_partners`
--

DROP TABLE IF EXISTS `b2b_partners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `b2b_partners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type` (`partner_type`),
  KEY `idx_status` (`integration_status`),
  KEY `idx_last_sync` (`last_sync`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `backup_files`
--

DROP TABLE IF EXISTS `backup_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `backup_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `type` enum('full','incremental','partial') DEFAULT 'full',
  `description` text DEFAULT NULL,
  `file_size` bigint(20) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_backup_files_created_at` (`created_at`),
  KEY `idx_backup_files_type` (`type`),
  CONSTRAINT `backup_files_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `backup_logs`
--

DROP TABLE IF EXISTS `backup_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `backup_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(50) NOT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL,
  `details` text DEFAULT NULL,
  `performed_by` int(11) DEFAULT NULL,
  `performed_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `performed_by` (`performed_by`),
  KEY `idx_backup_logs_action` (`action`),
  KEY `idx_backup_logs_status` (`status`),
  KEY `idx_backup_logs_performed_at` (`performed_at`),
  CONSTRAINT `backup_logs_ibfk_1` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `backup_schedules`
--

DROP TABLE IF EXISTS `backup_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `backup_schedules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `frequency` enum('hourly','daily','weekly','monthly') DEFAULT 'daily',
  `scheduled_time` time DEFAULT '02:00:00',
  `enabled` tinyint(1) DEFAULT 1,
  `last_run` datetime DEFAULT NULL,
  `next_run` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_backup_schedules_enabled` (`enabled`),
  KEY `idx_backup_schedules_next_run` (`next_run`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `block_verifications`
--

DROP TABLE IF EXISTS `block_verifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `block_verifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `block_id` int(11) NOT NULL,
  `verification_hash` varchar(64) NOT NULL,
  `verification_status` enum('valid','invalid','tampered') DEFAULT 'valid',
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `verified_by` (`verified_by`),
  KEY `idx_block_verifications_block` (`block_id`),
  KEY `idx_block_verifications_status` (`verification_status`),
  CONSTRAINT `block_verifications_ibfk_1` FOREIGN KEY (`block_id`) REFERENCES `blockchain_blocks` (`id`),
  CONSTRAINT `block_verifications_ibfk_2` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `blockchain_blocks`
--

DROP TABLE IF EXISTS `blockchain_blocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `blockchain_blocks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `block_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`block_data`)),
  `previous_hash` varchar(64) NOT NULL DEFAULT '0',
  `current_hash` varchar(64) NOT NULL,
  `block_type` enum('sale','payment','loan','savings','governance','general') NOT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `recorded_by` (`recorded_by`),
  KEY `idx_blockchain_blocks_type` (`block_type`),
  KEY `idx_blockchain_blocks_created` (`created_at`),
  KEY `idx_blockchain_blocks_hash` (`current_hash`),
  CONSTRAINT `blockchain_blocks_ibfk_1` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `blockchain_oracles`
--

DROP TABLE IF EXISTS `blockchain_oracles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `blockchain_oracles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `oracle_name` varchar(100) NOT NULL,
  `oracle_type` varchar(50) NOT NULL,
  `data_source` varchar(200) NOT NULL,
  `update_frequency` int(11) DEFAULT 3600,
  `last_update` datetime DEFAULT NULL,
  `confidence_score` decimal(5,4) DEFAULT 1.0000,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type` (`oracle_type`),
  KEY `idx_active` (`is_active`),
  KEY `idx_last_update` (`last_update`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `blockchain_transactions`
--

DROP TABLE IF EXISTS `blockchain_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `blockchain_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_hash` (`transaction_hash`),
  KEY `idx_hash` (`transaction_hash`),
  KEY `idx_from` (`from_address`),
  KEY `idx_to` (`to_address`),
  KEY `idx_type` (`transaction_type`),
  KEY `idx_status` (`status`),
  KEY `idx_reference` (`reference_type`,`reference_id`),
  KEY `idx_confirmed` (`confirmed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bridge_transactions`
--

DROP TABLE IF EXISTS `bridge_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bridge_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bridge_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `source_tx_hash` varchar(100) NOT NULL,
  `target_tx_hash` varchar(100) DEFAULT NULL,
  `amount` decimal(20,8) NOT NULL,
  `asset_symbol` varchar(20) NOT NULL,
  `fee_amount` decimal(20,8) DEFAULT 0.00000000,
  `status` enum('pending','processing','completed','failed') DEFAULT 'pending',
  `initiated_at` datetime DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_bridge` (`bridge_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_source_tx` (`source_tx_hash`),
  KEY `idx_target_tx` (`target_tx_hash`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `buku_besar`
--

DROP TABLE IF EXISTS `buku_besar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `buku_besar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `coa_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `description` text DEFAULT NULL,
  `debit` decimal(15,2) DEFAULT 0.00,
  `credit` decimal(15,2) DEFAULT 0.00,
  `saldo` decimal(15,2) DEFAULT 0.00,
  `jurnal_detail_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `coa_id` (`coa_id`),
  KEY `jurnal_detail_id` (`jurnal_detail_id`),
  CONSTRAINT `buku_besar_ibfk_1` FOREIGN KEY (`coa_id`) REFERENCES `coa` (`id`),
  CONSTRAINT `buku_besar_ibfk_2` FOREIGN KEY (`jurnal_detail_id`) REFERENCES `jurnal_detail` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `business_metrics`
--

DROP TABLE IF EXISTS `business_metrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `business_metrics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `metric_category` varchar(50) NOT NULL,
  `metric_name` varchar(100) NOT NULL,
  `metric_value` decimal(10,2) DEFAULT 0.00,
  `target_value` decimal(10,2) DEFAULT 0.00,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_category_period` (`metric_category`,`period_start`,`period_end`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_type` varchar(50) NOT NULL,
  `category_code` varchar(50) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `parent_category_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `parent_category_id` (`parent_category_id`),
  CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `certificates`
--

DROP TABLE IF EXISTS `certificates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `certificates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `enrollment_id` int(11) NOT NULL,
  `certificate_number` varchar(50) NOT NULL,
  `issued_date` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `issued_by` int(11) DEFAULT NULL,
  `verification_code` varchar(100) DEFAULT NULL,
  `status` enum('active','revoked') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `enrollment_id` (`enrollment_id`),
  UNIQUE KEY `certificate_number` (`certificate_number`),
  UNIQUE KEY `verification_code` (`verification_code`),
  KEY `issued_by` (`issued_by`),
  KEY `idx_certificates_enrollment` (`enrollment_id`),
  KEY `idx_certificates_number` (`certificate_number`),
  CONSTRAINT `certificates_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `learning_enrollments` (`id`),
  CONSTRAINT `certificates_ibfk_2` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chain_bridges`
--

DROP TABLE IF EXISTS `chain_bridges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `chain_bridges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bridge_name` varchar(100) NOT NULL,
  `source_chain` varchar(50) NOT NULL,
  `target_chain` varchar(50) NOT NULL,
  `bridge_contract` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive','maintenance') DEFAULT 'active',
  `daily_volume_limit` decimal(20,8) DEFAULT NULL,
  `current_daily_volume` decimal(20,8) DEFAULT 0.00000000,
  `fee_structure` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`fee_structure`)),
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_chains` (`source_chain`,`target_chain`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `coa`
--

DROP TABLE IF EXISTS `coa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `coa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_coa` varchar(20) NOT NULL,
  `nama_coa` varchar(100) NOT NULL,
  `tipe` enum('debit','kredit') NOT NULL,
  `level` int(11) DEFAULT 1,
  `parent_id` int(11) DEFAULT NULL,
  `saldo_awal` decimal(15,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_coa` (`kode_coa`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `coa_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `coa` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `collection_actions`
--

DROP TABLE IF EXISTS `collection_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `collection_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `collection_id` int(11) NOT NULL,
  `action_type` enum('email','sms','call','letter','payment_plan','legal_action') NOT NULL,
  `action_status` enum('scheduled','sent','delivered','responded','failed') DEFAULT 'scheduled',
  `scheduled_date` datetime DEFAULT NULL,
  `executed_date` datetime DEFAULT NULL,
  `response_received` tinyint(1) DEFAULT 0,
  `response_details` text DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_collection` (`collection_id`),
  KEY `idx_type` (`action_type`),
  KEY `idx_status` (`action_status`),
  KEY `idx_scheduled` (`scheduled_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `collection_automation`
--

DROP TABLE IF EXISTS `collection_automation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `collection_automation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_loan` (`loan_id`),
  KEY `idx_member` (`member_id`),
  KEY `idx_status` (`status`),
  KEY `idx_priority` (`priority_level`),
  KEY `idx_next_action` (`next_action_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `collection_templates`
--

DROP TABLE IF EXISTS `collection_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `collection_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type` (`template_type`),
  KEY `idx_active` (`is_active`),
  KEY `idx_overdue_range` (`overdue_days_min`,`overdue_days_max`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `community_activities`
--

DROP TABLE IF EXISTS `community_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `community_activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `activity_type` varchar(50) NOT NULL,
  `activity_target_id` int(11) DEFAULT NULL,
  `points_earned` int(11) DEFAULT 0,
  `engagement_score` decimal(5,2) DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_type` (`user_id`,`activity_type`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `compliance_audits`
--

DROP TABLE IF EXISTS `compliance_audits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `compliance_audits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_cooperative` (`cooperative_id`),
  KEY `idx_type` (`audit_type`),
  KEY `idx_result` (`audit_result`),
  KEY `idx_status` (`action_status`),
  KEY `idx_audits_cooperative_type` (`cooperative_id`,`audit_type`),
  CONSTRAINT `compliance_audits_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `cooperative_structure` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `compliance_checks`
--

DROP TABLE IF EXISTS `compliance_checks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `compliance_checks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `check_type` varchar(100) NOT NULL,
  `check_name` varchar(255) NOT NULL,
  `status` enum('compliant','warning','error') DEFAULT 'compliant',
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `last_checked` timestamp NULL DEFAULT current_timestamp(),
  `next_check` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_compliance_checks_type` (`check_type`),
  KEY `idx_compliance_checks_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `compliance_metrics`
--

DROP TABLE IF EXISTS `compliance_metrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `compliance_metrics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `regulation` varchar(100) NOT NULL,
  `compliance_status` enum('compliant','non_compliant','pending_review') DEFAULT 'pending_review',
  `last_audit` date DEFAULT NULL,
  `next_audit` date DEFAULT NULL,
  `findings` text DEFAULT NULL,
  `remediation_plan` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_regulation_status` (`regulation`,`compliance_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `compliance_monitoring`
--

DROP TABLE IF EXISTS `compliance_monitoring`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `compliance_monitoring` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_governance` (`governance_id`),
  KEY `idx_cooperative` (`cooperative_id`),
  KEY `idx_status` (`compliance_status`),
  KEY `idx_assessment` (`assessment_date`),
  KEY `idx_action_status` (`action_status`),
  CONSTRAINT `compliance_monitoring_ibfk_1` FOREIGN KEY (`governance_id`) REFERENCES `network_governance` (`id`),
  CONSTRAINT `compliance_monitoring_ibfk_2` FOREIGN KEY (`cooperative_id`) REFERENCES `cooperative_network` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_type` enum('phone','mobile','email','fax','website') NOT NULL,
  `contact_value` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contract_interactions`
--

DROP TABLE IF EXISTS `contract_interactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `contract_interactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contract_id` int(11) NOT NULL,
  `interaction_type` enum('deploy','call','query','event') NOT NULL,
  `method_name` varchar(100) DEFAULT NULL,
  `parameters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`parameters`)),
  `result` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`result`)),
  `transaction_hash` varchar(100) DEFAULT NULL,
  `gas_used` bigint(20) DEFAULT NULL,
  `status` enum('success','failed','pending') DEFAULT 'success',
  `executed_by` int(11) DEFAULT NULL,
  `executed_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_contract` (`contract_id`),
  KEY `idx_type` (`interaction_type`),
  KEY `idx_hash` (`transaction_hash`),
  KEY `idx_executed` (`executed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contract_templates`
--

DROP TABLE IF EXISTS `contract_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `contract_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_name` varchar(100) NOT NULL,
  `template_type` varchar(50) NOT NULL,
  `solidity_code` text NOT NULL,
  `abi_template` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`abi_template`)),
  `parameters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`parameters`)),
  `description` text DEFAULT NULL,
  `version` varchar(20) DEFAULT '1.0.0',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type` (`template_type`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `conversion_metrics`
--

DROP TABLE IF EXISTS `conversion_metrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `conversion_metrics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `conversion_type` varchar(50) NOT NULL,
  `conversion_value` decimal(10,2) DEFAULT 0.00,
  `funnel_step` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_session_type` (`session_id`,`conversion_type`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cooperative_accounts`
--

DROP TABLE IF EXISTS `cooperative_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cooperative_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `parent_account_id` (`parent_account_id`),
  KEY `idx_cooperative` (`cooperative_id`),
  KEY `idx_type` (`account_type`),
  KEY `idx_code` (`account_code`),
  KEY `idx_active` (`is_active`),
  KEY `idx_accounts_cooperative_type` (`cooperative_id`,`account_type`,`is_active`),
  CONSTRAINT `cooperative_accounts_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `cooperative_structure` (`id`),
  CONSTRAINT `cooperative_accounts_ibfk_2` FOREIGN KEY (`parent_account_id`) REFERENCES `cooperative_accounts` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cooperative_activities`
--

DROP TABLE IF EXISTS `cooperative_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cooperative_activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_cooperative` (`cooperative_id`),
  KEY `idx_type` (`activity_type`),
  KEY `idx_status` (`status`),
  KEY `idx_date` (`start_date`,`end_date`),
  KEY `idx_activities_cooperative_type` (`cooperative_id`,`activity_type`,`status`),
  CONSTRAINT `cooperative_activities_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `cooperative_structure` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cooperative_members`
--

DROP TABLE IF EXISTS `cooperative_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cooperative_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_number` (`member_number`),
  UNIQUE KEY `nik` (`nik`),
  KEY `idx_cooperative` (`cooperative_id`),
  KEY `idx_status` (`membership_status`),
  KEY `idx_membership_date` (`membership_date`),
  KEY `idx_kyc` (`kyc_status`),
  KEY `idx_members_cooperative` (`cooperative_id`,`membership_status`),
  CONSTRAINT `cooperative_members_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `cooperative_structure` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cooperative_network`
--

DROP TABLE IF EXISTS `cooperative_network`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cooperative_network` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cooperative_name` varchar(200) NOT NULL,
  `cooperative_code` varchar(20) DEFAULT NULL,
  `partnership_type` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive','pending') DEFAULT 'pending',
  `shared_services` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`shared_services`)),
  `api_endpoints` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`api_endpoints`)),
  `joined_at` datetime DEFAULT current_timestamp(),
  `last_sync` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cooperative_code` (`cooperative_code`),
  KEY `idx_status` (`status`),
  KEY `idx_joined_at` (`joined_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cooperative_structure`
--

DROP TABLE IF EXISTS `cooperative_structure`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cooperative_structure` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `cooperative_code` (`cooperative_code`),
  KEY `idx_status` (`operational_status`),
  KEY `idx_compliance` (`compliance_status`),
  KEY `idx_sector` (`business_sector`),
  KEY `idx_type` (`cooperative_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `course_modules`
--

DROP TABLE IF EXISTS `course_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `course_modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `module_title` varchar(255) NOT NULL,
  `module_description` text DEFAULT NULL,
  `module_content` longtext DEFAULT NULL,
  `module_order` int(11) NOT NULL,
  `duration_minutes` int(11) DEFAULT 0,
  `resources` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_course_modules_course` (`course_id`),
  KEY `idx_course_modules_order` (`module_order`),
  CONSTRAINT `course_modules_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `learning_courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `course_ratings`
--

DROP TABLE IF EXISTS `course_ratings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `course_ratings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `review` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_rating` (`course_id`,`student_id`),
  KEY `idx_course_ratings_course` (`course_id`),
  KEY `idx_course_ratings_student` (`student_id`),
  CONSTRAINT `course_ratings_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `learning_courses` (`id`),
  CONSTRAINT `course_ratings_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `credit_score_factors`
--

DROP TABLE IF EXISTS `credit_score_factors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `credit_score_factors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `factor_name` varchar(100) NOT NULL,
  `factor_category` varchar(50) NOT NULL,
  `weight` decimal(5,4) DEFAULT 0.0000,
  `description` text DEFAULT NULL,
  `data_source` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_category` (`factor_category`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `credit_scores`
--

DROP TABLE IF EXISTS `credit_scores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `credit_scores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `loan_application_id` int(11) DEFAULT NULL,
  `credit_score` decimal(5,2) NOT NULL,
  `score_range` enum('poor','fair','good','excellent') NOT NULL,
  `risk_level` enum('low','medium','high','very_high') NOT NULL,
  `confidence_score` decimal(5,4) DEFAULT 0.0000,
  `factors` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`factors`)),
  `model_used` varchar(100) DEFAULT NULL,
  `calculated_at` datetime DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_member` (`member_id`),
  KEY `idx_score` (`credit_score`),
  KEY `idx_risk_level` (`risk_level`),
  KEY `idx_calculated_at` (`calculated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `credit_scoring_models`
--

DROP TABLE IF EXISTS `credit_scoring_models`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `credit_scoring_models` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model_name` varchar(100) NOT NULL,
  `model_version` varchar(20) NOT NULL,
  `model_type` enum('traditional','machine_learning','hybrid') DEFAULT 'traditional',
  `accuracy_score` decimal(5,4) DEFAULT 0.0000,
  `features_used` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features_used`)),
  `training_data_size` int(11) DEFAULT 0,
  `last_trained` datetime DEFAULT NULL,
  `status` enum('active','inactive','training') DEFAULT 'inactive',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_version` (`model_version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cross_border_payments`
--

DROP TABLE IF EXISTS `cross_border_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cross_border_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_reference` (`payment_reference`),
  KEY `idx_sender` (`sender_coop_id`),
  KEY `idx_receiver` (`receiver_coop_id`),
  KEY `idx_status` (`status`),
  KEY `idx_compliance` (`compliance_status`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `cross_border_payments_ibfk_1` FOREIGN KEY (`sender_coop_id`) REFERENCES `cooperative_network` (`id`),
  CONSTRAINT `cross_border_payments_ibfk_2` FOREIGN KEY (`receiver_coop_id`) REFERENCES `cooperative_network` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `customer_invoices`
--

DROP TABLE IF EXISTS `customer_invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `order_id` (`order_id`),
  KEY `customer_id` (`customer_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `customer_invoices_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `penjualan` (`id`),
  CONSTRAINT `customer_invoices_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
  CONSTRAINT `customer_invoices_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `decision_rules`
--

DROP TABLE IF EXISTS `decision_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `decision_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_category` (`rule_category`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `demand_forecasts`
--

DROP TABLE IF EXISTS `demand_forecasts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `demand_forecasts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `forecast_period` varchar(20) NOT NULL,
  `forecast_date` date NOT NULL,
  `forecasted_quantity` decimal(10,2) NOT NULL,
  `confidence_level` decimal(5,2) DEFAULT 0.00,
  `actual_quantity` decimal(10,2) DEFAULT NULL,
  `forecast_accuracy` decimal(5,2) DEFAULT NULL,
  `factors_considered` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`factors_considered`)),
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_period` (`forecast_period`),
  KEY `idx_date` (`forecast_date`),
  KEY `idx_forecasts_product_period` (`product_id`,`forecast_period`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `department_code` varchar(20) NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `department_code` (`department_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `detail_jurnal`
--

DROP TABLE IF EXISTS `detail_jurnal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `detail_jurnal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jurnal_id` int(11) NOT NULL,
  `coa_id` int(11) NOT NULL,
  `debit` decimal(15,2) DEFAULT 0.00,
  `kredit` decimal(15,2) DEFAULT 0.00,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `jurnal_id` (`jurnal_id`),
  KEY `idx_detail_jurnal_coa` (`coa_id`),
  CONSTRAINT `detail_jurnal_ibfk_1` FOREIGN KEY (`jurnal_id`) REFERENCES `jurnal` (`id`),
  CONSTRAINT `detail_jurnal_ibfk_2` FOREIGN KEY (`coa_id`) REFERENCES `coa` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `detail_penjualan`
--

DROP TABLE IF EXISTS `detail_penjualan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `detail_penjualan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `penjualan_id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_satuan` decimal(12,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_detail_penjualan_penjualan` (`penjualan_id`),
  KEY `idx_detail_penjualan_produk` (`produk_id`),
  CONSTRAINT `detail_penjualan_ibfk_1` FOREIGN KEY (`penjualan_id`) REFERENCES `penjualan` (`id`),
  CONSTRAINT `detail_penjualan_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `digital_assets`
--

DROP TABLE IF EXISTS `digital_assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `digital_assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_name` varchar(100) NOT NULL,
  `asset_symbol` varchar(20) NOT NULL,
  `asset_type` enum('savings_token','loan_token','equity_token','reward_token') DEFAULT 'savings_token',
  `total_supply` decimal(20,8) NOT NULL,
  `circulating_supply` decimal(20,8) DEFAULT 0.00000000,
  `contract_address` varchar(100) DEFAULT NULL,
  `blockchain_network` varchar(50) DEFAULT 'polygon',
  `decimals` int(11) DEFAULT 18,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_symbol` (`asset_symbol`),
  KEY `idx_type` (`asset_type`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `digital_products`
--

DROP TABLE IF EXISTS `digital_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `digital_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type` (`product_type`),
  KEY `idx_provider` (`provider_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `digital_wallets`
--

DROP TABLE IF EXISTS `digital_wallets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `digital_wallets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `wallet_address` varchar(100) NOT NULL,
  `wallet_type` enum('hot','cold','custodial') DEFAULT 'custodial',
  `blockchain_network` varchar(50) DEFAULT 'polygon',
  `balance` decimal(20,8) DEFAULT 0.00000000,
  `is_verified` tinyint(1) DEFAULT 0,
  `kyc_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `wallet_address` (`wallet_address`),
  KEY `idx_user` (`user_id`),
  KEY `idx_address` (`wallet_address`),
  KEY `idx_verified` (`is_verified`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `education_fund_utilization`
--

DROP TABLE IF EXISTS `education_fund_utilization`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `education_fund_utilization` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_cooperative` (`cooperative_id`),
  KEY `idx_year` (`utilization_year`),
  KEY `idx_type` (`program_type`),
  KEY `idx_status` (`completion_status`),
  CONSTRAINT `education_fund_utilization_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `cooperative_structure` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `efficiency_metrics`
--

DROP TABLE IF EXISTS `efficiency_metrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `efficiency_metrics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `process_name` varchar(100) NOT NULL,
  `manual_time` decimal(5,2) DEFAULT 0.00,
  `automated_time` decimal(5,2) DEFAULT 0.00,
  `efficiency_gain` decimal(5,2) DEFAULT 0.00,
  `cost_savings` decimal(15,2) DEFAULT 0.00,
  `measured_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_process_date` (`process_name`,`measured_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `employee_attendance`
--

DROP TABLE IF EXISTS `employee_attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `hours_worked` decimal(5,2) DEFAULT NULL,
  `status` enum('present','absent','late','half_day','overtime') DEFAULT 'present',
  `notes` text DEFAULT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `recorded_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `recorded_by` (`recorded_by`),
  KEY `idx_employee_attendance_employee_id` (`employee_id`),
  KEY `idx_employee_attendance_date` (`attendance_date`),
  CONSTRAINT `employee_attendance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  CONSTRAINT `employee_attendance_ibfk_2` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `position_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_id` (`employee_id`),
  KEY `supervisor_id` (`supervisor_id`),
  KEY `idx_employees_employee_id` (`employee_id`),
  KEY `idx_employees_status` (`status`),
  KEY `idx_employees_department` (`department`),
  KEY `address_id` (`address_id`),
  KEY `primary_contact_id` (`primary_contact_id`),
  KEY `department_id` (`department_id`),
  KEY `position_id` (`position_id`),
  CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`id`),
  CONSTRAINT `employees_ibfk_2` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`),
  CONSTRAINT `employees_ibfk_3` FOREIGN KEY (`primary_contact_id`) REFERENCES `contacts` (`id`),
  CONSTRAINT `employees_ibfk_4` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  CONSTRAINT `employees_ibfk_5` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `entity_addresses`
--

DROP TABLE IF EXISTS `entity_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `entity_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entity_type` enum('member','supplier','employee','customer','other') NOT NULL,
  `entity_id` int(11) NOT NULL,
  `address_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `address_id` (`address_id`),
  CONSTRAINT `entity_addresses_ibfk_1` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `entity_contacts`
--

DROP TABLE IF EXISTS `entity_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `entity_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entity_type` enum('member','supplier','employee','customer','other') NOT NULL,
  `entity_id` int(11) NOT NULL,
  `contact_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `contact_id` (`contact_id`),
  CONSTRAINT `entity_contacts_ibfk_1` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `error_logs`
--

DROP TABLE IF EXISTS `error_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `error_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `error_level` varchar(20) NOT NULL,
  `error_message` text NOT NULL,
  `error_file` varchar(500) DEFAULT NULL,
  `error_line` int(11) DEFAULT NULL,
  `error_context` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`error_context`)),
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_level_date` (`error_level`,`created_at`),
  KEY `idx_user_error` (`user_id`,`error_level`),
  KEY `idx_errors_recent` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `financial_periods`
--

DROP TABLE IF EXISTS `financial_periods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `financial_periods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `net_income` decimal(15,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cooperative` (`cooperative_id`),
  KEY `idx_year` (`period_year`),
  KEY `idx_period` (`period_type`,`period_number`),
  KEY `idx_status` (`status`),
  CONSTRAINT `financial_periods_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `cooperative_structure` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fixed_assets`
--

DROP TABLE IF EXISTS `fixed_assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fixed_assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `asset_code` (`asset_code`),
  KEY `created_by` (`created_by`),
  KEY `idx_fixed_assets_asset_code` (`asset_code`),
  KEY `idx_fixed_assets_category` (`category`),
  KEY `idx_fixed_assets_condition` (`condition_status`),
  KEY `idx_fixed_assets_location` (`location`),
  CONSTRAINT `fixed_assets_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fraud_alerts`
--

DROP TABLE IF EXISTS `fraud_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fraud_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type` (`alert_type`),
  KEY `idx_severity` (`severity`),
  KEY `idx_user` (`user_id`),
  KEY `idx_transaction` (`transaction_id`),
  KEY `idx_investigated` (`investigated`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fraud_patterns`
--

DROP TABLE IF EXISTS `fraud_patterns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fraud_patterns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pattern_name` varchar(100) NOT NULL,
  `pattern_type` varchar(50) NOT NULL,
  `detection_rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`detection_rules`)),
  `risk_weight` decimal(3,2) DEFAULT 1.00,
  `false_positive_rate` decimal(5,4) DEFAULT 0.0000,
  `detection_count` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type` (`pattern_type`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `governance_bodies`
--

DROP TABLE IF EXISTS `governance_bodies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `governance_bodies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `resignation_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cooperative` (`cooperative_id`),
  KEY `idx_type` (`body_type`),
  KEY `idx_member` (`member_id`),
  KEY `idx_status` (`status`),
  KEY `idx_governance_cooperative_type` (`cooperative_id`,`body_type`,`status`),
  CONSTRAINT `governance_bodies_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `cooperative_structure` (`id`),
  CONSTRAINT `governance_bodies_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `cooperative_members` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `governance_delegates`
--

DROP TABLE IF EXISTS `governance_delegates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `governance_delegates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `delegator_id` int(11) NOT NULL,
  `delegate_id` int(11) NOT NULL,
  `voting_power` decimal(20,8) NOT NULL,
  `delegation_start` datetime DEFAULT current_timestamp(),
  `delegation_end` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_delegation` (`delegator_id`,`delegate_id`),
  KEY `idx_delegator` (`delegator_id`),
  KEY `idx_delegate` (`delegate_id`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `governance_proposals`
--

DROP TABLE IF EXISTS `governance_proposals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `governance_proposals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_proposer` (`proposer_id`),
  KEY `idx_status` (`status`),
  KEY `idx_voting` (`voting_start`,`voting_end`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `governance_votes`
--

DROP TABLE IF EXISTS `governance_votes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `governance_votes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `proposal_id` int(11) NOT NULL,
  `voter_id` int(11) NOT NULL,
  `voter_address` varchar(100) DEFAULT NULL,
  `vote_choice` enum('yes','no','abstain') NOT NULL,
  `voting_power` decimal(20,8) NOT NULL,
  `vote_tx_hash` varchar(100) DEFAULT NULL,
  `voted_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_vote` (`proposal_id`,`voter_id`),
  KEY `idx_proposal` (`proposal_id`),
  KEY `idx_voter` (`voter_id`),
  KEY `idx_choice` (`vote_choice`),
  KEY `idx_voted` (`voted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `improvement_initiatives`
--

DROP TABLE IF EXISTS `improvement_initiatives`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `improvement_initiatives` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_priority` (`priority`),
  KEY `idx_status` (`status`),
  KEY `idx_assigned` (`assigned_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `improvement_measurements`
--

DROP TABLE IF EXISTS `improvement_measurements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `improvement_measurements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `initiative_id` int(11) NOT NULL,
  `metric_name` varchar(100) NOT NULL,
  `baseline_value` decimal(10,2) DEFAULT NULL,
  `target_value` decimal(10,2) DEFAULT NULL,
  `current_value` decimal(10,2) DEFAULT NULL,
  `measurement_date` date NOT NULL,
  `measured_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_initiative` (`initiative_id`),
  KEY `idx_metric` (`metric_name`),
  KEY `idx_date` (`measurement_date`),
  CONSTRAINT `improvement_measurements_ibfk_1` FOREIGN KEY (`initiative_id`) REFERENCES `improvement_initiatives` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inter_coop_trades`
--

DROP TABLE IF EXISTS `inter_coop_trades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `inter_coop_trades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `trade_reference` (`trade_reference`),
  KEY `idx_seller` (`seller_coop_id`),
  KEY `idx_buyer` (`buyer_coop_id`),
  KEY `idx_type` (`trade_type`),
  KEY `idx_status` (`trade_status`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `inter_coop_trades_ibfk_1` FOREIGN KEY (`seller_coop_id`) REFERENCES `cooperative_network` (`id`),
  CONSTRAINT `inter_coop_trades_ibfk_2` FOREIGN KEY (`buyer_coop_id`) REFERENCES `cooperative_network` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `international_opportunities`
--

DROP TABLE IF EXISTS `international_opportunities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `international_opportunities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_country` (`target_country`),
  KEY `idx_type` (`opportunity_type`),
  KEY `idx_risk` (`risk_level`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `international_partnerships`
--

DROP TABLE IF EXISTS `international_partnerships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `international_partnerships` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_local_coop` (`local_coop_id`),
  KEY `idx_country` (`partner_country`),
  KEY `idx_type` (`partnership_type`),
  KEY `idx_status` (`status`),
  CONSTRAINT `international_partnerships_ibfk_1` FOREIGN KEY (`local_coop_id`) REFERENCES `cooperative_network` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inventory_items`
--

DROP TABLE IF EXISTS `inventory_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_warehouse` (`warehouse_id`),
  KEY `idx_batch` (`batch_number`),
  KEY `idx_quality` (`quality_status`),
  KEY `idx_expiry` (`expiry_date`),
  KEY `idx_inventory_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inventory_transactions`
--

DROP TABLE IF EXISTS `inventory_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inventory_item_id` int(11) NOT NULL,
  `transaction_type` enum('receipt','issue','adjustment','transfer','return') NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `from_location` varchar(100) DEFAULT NULL,
  `to_location` varchar(100) DEFAULT NULL,
  `performed_by` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `transaction_date` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type` (`transaction_type`),
  KEY `idx_reference` (`reference_type`,`reference_id`),
  KEY `idx_date` (`transaction_date`),
  KEY `idx_inventory_transactions_item` (`inventory_item_id`),
  CONSTRAINT `inventory_transactions_ibfk_1` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jenis_pinjaman`
--

DROP TABLE IF EXISTS `jenis_pinjaman`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jenis_pinjaman` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_pinjaman` varchar(50) NOT NULL,
  `kode` varchar(10) NOT NULL,
  `plafond_maksimal` decimal(15,2) DEFAULT 0.00,
  `bunga_pertahun` decimal(5,2) DEFAULT 0.00,
  `tenor_maksimal` int(11) DEFAULT 12,
  `denda_persen` decimal(5,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jenis_simpanan`
--

DROP TABLE IF EXISTS `jenis_simpanan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jenis_simpanan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_simpanan` varchar(50) NOT NULL,
  `kode` varchar(10) NOT NULL,
  `jenis` enum('wajib','sukarela','berjangka') NOT NULL,
  `minimal_setoran` decimal(12,2) DEFAULT 0.00,
  `bunga_pertahun` decimal(5,2) DEFAULT 0.00,
  `periode_bulan` int(11) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `journal_entries`
--

DROP TABLE IF EXISTS `journal_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `journal_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `approved_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entry_number` (`entry_number`),
  KEY `idx_cooperative` (`cooperative_id`),
  KEY `idx_date` (`entry_date`),
  KEY `idx_status` (`status`),
  KEY `idx_reference` (`reference_type`,`reference_id`),
  KEY `idx_journal_cooperative_date` (`cooperative_id`,`entry_date`,`status`),
  CONSTRAINT `journal_entries_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `cooperative_structure` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `journal_lines`
--

DROP TABLE IF EXISTS `journal_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `journal_lines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `journal_entry_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `debit` decimal(15,2) DEFAULT 0.00,
  `credit` decimal(15,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_journal` (`journal_entry_id`),
  KEY `idx_account` (`account_id`),
  CONSTRAINT `journal_lines_ibfk_1` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`id`),
  CONSTRAINT `journal_lines_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `cooperative_accounts` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jurnal`
--

DROP TABLE IF EXISTS `jurnal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jurnal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no_jurnal` varchar(30) NOT NULL,
  `tanggal_jurnal` date NOT NULL,
  `keterangan` text DEFAULT NULL,
  `total_debit` decimal(15,2) DEFAULT 0.00,
  `total_kredit` decimal(15,2) DEFAULT 0.00,
  `status` enum('draft','posted') DEFAULT 'draft',
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_jurnal` (`no_jurnal`),
  KEY `idx_jurnal_no_jurnal` (`no_jurnal`),
  KEY `idx_jurnal_user` (`user_id`),
  CONSTRAINT `jurnal_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jurnal_detail`
--

DROP TABLE IF EXISTS `jurnal_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jurnal_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jurnal_id` int(11) NOT NULL,
  `coa_id` int(11) NOT NULL,
  `debit` decimal(15,2) DEFAULT 0.00,
  `credit` decimal(15,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `jurnal_id` (`jurnal_id`),
  KEY `coa_id` (`coa_id`),
  CONSTRAINT `jurnal_detail_ibfk_1` FOREIGN KEY (`jurnal_id`) REFERENCES `jurnal` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jurnal_detail_ibfk_2` FOREIGN KEY (`coa_id`) REFERENCES `coa` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kategori_produk`
--

DROP TABLE IF EXISTS `kategori_produk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `kategori_produk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(50) NOT NULL,
  `kode` varchar(10) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `knowledge_base`
--

DROP TABLE IF EXISTS `knowledge_base`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `knowledge_base` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `author_coop_id` (`author_coop_id`),
  KEY `idx_type` (`content_type`),
  KEY `idx_category` (`category`),
  KEY `idx_featured` (`is_featured`),
  KEY `idx_access` (`access_level`),
  KEY `idx_language` (`language`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `knowledge_base_ibfk_1` FOREIGN KEY (`author_coop_id`) REFERENCES `cooperative_network` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `knowledge_ratings`
--

DROP TABLE IF EXISTS `knowledge_ratings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `knowledge_ratings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `knowledge_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` decimal(3,1) NOT NULL,
  `review` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_rating` (`knowledge_id`,`user_id`),
  KEY `idx_knowledge` (`knowledge_id`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `knowledge_ratings_ibfk_1` FOREIGN KEY (`knowledge_id`) REFERENCES `knowledge_base` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `koperasi_activities`
--

DROP TABLE IF EXISTS `koperasi_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `koperasi_activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_code` varchar(50) NOT NULL,
  `activity_name` varchar(255) NOT NULL,
  `activity_type` enum('simpanan','pinjaman','jual_beli','investasi','jasa_lain') NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `activity_code` (`activity_code`)
) ENGINE=InnoDB AUTO_INCREMENT=77 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `koperasi_meetings`
--

DROP TABLE IF EXISTS `koperasi_meetings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `koperasi_meetings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `meeting_type` enum('rapat_anggota','rapat_pengurus','rapat_pengawas','rapat_kombinasi') NOT NULL,
  `meeting_title` varchar(255) NOT NULL,
  `meeting_date` datetime NOT NULL,
  `meeting_location` varchar(255) DEFAULT NULL,
  `agenda` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('scheduled','in_progress','completed','cancelled') DEFAULT 'scheduled',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `koperasi_meetings_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `koperasi_sanctions`
--

DROP TABLE IF EXISTS `koperasi_sanctions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `koperasi_sanctions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `koperasi_transactions`
--

DROP TABLE IF EXISTS `koperasi_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `koperasi_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `activity_code` (`activity_code`),
  KEY `member_id` (`member_id`),
  KEY `created_by` (`created_by`),
  KEY `approved_by` (`approved_by`),
  CONSTRAINT `koperasi_transactions_ibfk_1` FOREIGN KEY (`activity_code`) REFERENCES `koperasi_activities` (`activity_code`),
  CONSTRAINT `koperasi_transactions_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `anggota` (`id`) ON DELETE SET NULL,
  CONSTRAINT `koperasi_transactions_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `koperasi_transactions_ibfk_4` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `log_koperasi_transaction` AFTER INSERT ON `koperasi_transactions` FOR EACH ROW BEGIN
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
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `laporan_pengawas`
--

DROP TABLE IF EXISTS `laporan_pengawas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `laporan_pengawas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `approved_by` (`approved_by`),
  KEY `idx_laporan_pengawas_status` (`status`),
  KEY `idx_laporan_pengawas_created_by` (`created_by`),
  CONSTRAINT `laporan_pengawas_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `laporan_pengawas_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `learning_analytics`
--

DROP TABLE IF EXISTS `learning_analytics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `learning_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) DEFAULT NULL,
  `enrollment_id` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `event_type` varchar(50) NOT NULL,
  `event_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`event_data`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `enrollment_id` (`enrollment_id`),
  KEY `idx_learning_analytics_course` (`course_id`),
  KEY `idx_learning_analytics_student` (`student_id`),
  KEY `idx_learning_analytics_event` (`event_type`),
  CONSTRAINT `learning_analytics_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `learning_courses` (`id`),
  CONSTRAINT `learning_analytics_ibfk_2` FOREIGN KEY (`enrollment_id`) REFERENCES `learning_enrollments` (`id`),
  CONSTRAINT `learning_analytics_ibfk_3` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `learning_courses`
--

DROP TABLE IF EXISTS `learning_courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `learning_courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_code` (`course_code`),
  KEY `created_by` (`created_by`),
  KEY `idx_learning_courses_code` (`course_code`),
  KEY `idx_learning_courses_category` (`category`),
  KEY `idx_learning_courses_status` (`status`),
  CONSTRAINT `learning_courses_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `learning_enrollments`
--

DROP TABLE IF EXISTS `learning_enrollments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `learning_enrollments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_learning_enrollments_course` (`course_id`),
  KEY `idx_learning_enrollments_student` (`student_id`),
  KEY `idx_learning_enrollments_status` (`status`),
  CONSTRAINT `learning_enrollments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `learning_courses` (`id`),
  CONSTRAINT `learning_enrollments_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `learning_progress`
--

DROP TABLE IF EXISTS `learning_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `learning_progress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `enrollment_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `status` enum('not_started','in_progress','completed') DEFAULT 'not_started',
  `completion_date` date DEFAULT NULL,
  `time_spent_minutes` int(11) DEFAULT 0,
  `score` decimal(5,2) DEFAULT NULL,
  `attempts` int(11) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_learning_progress_enrollment` (`enrollment_id`),
  KEY `idx_learning_progress_module` (`module_id`),
  KEY `idx_learning_progress_status` (`status`),
  CONSTRAINT `learning_progress_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `learning_enrollments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `learning_progress_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `course_modules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ledger_entries`
--

DROP TABLE IF EXISTS `ledger_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ledger_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` int(11) NOT NULL,
  `account_type` varchar(50) NOT NULL,
  `account_id` int(11) NOT NULL,
  `debit` decimal(20,8) DEFAULT 0.00000000,
  `credit` decimal(20,8) DEFAULT 0.00000000,
  `balance` decimal(20,8) DEFAULT 0.00000000,
  `asset_symbol` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `entry_type` enum('user_transaction','system_adjustment','fee','reward') DEFAULT 'user_transaction',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_transaction` (`transaction_id`),
  KEY `idx_account` (`account_type`,`account_id`),
  KEY `idx_asset` (`asset_symbol`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `legal_documents`
--

DROP TABLE IF EXISTS `legal_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `legal_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_cooperative` (`cooperative_id`),
  KEY `idx_type` (`document_type`),
  KEY `idx_status` (`status`),
  KEY `idx_expiry` (`expiry_date`),
  KEY `idx_access` (`access_level`),
  KEY `idx_documents_cooperative_type` (`cooperative_id`,`document_type`,`status`),
  CONSTRAINT `legal_documents_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `cooperative_structure` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logistics_providers`
--

DROP TABLE IF EXISTS `logistics_providers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `logistics_providers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `provider_name` varchar(100) NOT NULL,
  `provider_code` varchar(20) NOT NULL,
  `api_endpoint` varchar(500) DEFAULT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `service_types` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`service_types`)),
  `rate_card` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`rate_card`)),
  `status` enum('active','inactive') DEFAULT 'active',
  `contract_start_date` date DEFAULT NULL,
  `contract_end_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `provider_code` (`provider_code`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_data`)),
  `new_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_data`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_logs_created_at` (`created_at`),
  CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `loyalty_program`
--

DROP TABLE IF EXISTS `loyalty_program`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `loyalty_program` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `current_points` int(11) DEFAULT 0,
  `total_points_earned` int(11) DEFAULT 0,
  `total_points_redeemed` int(11) DEFAULT 0,
  `tier` enum('bronze','silver','gold','platinum') DEFAULT 'bronze',
  `tier_upgrade_date` date DEFAULT NULL,
  `tier_expiry_date` date DEFAULT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_member` (`member_id`),
  KEY `idx_tier` (`tier`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `loyalty_rewards`
--

DROP TABLE IF EXISTS `loyalty_rewards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `loyalty_rewards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type` (`reward_type`),
  KEY `idx_points` (`points_required`),
  KEY `idx_active` (`active`),
  KEY `idx_valid` (`valid_from`,`valid_until`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `loyalty_transactions`
--

DROP TABLE IF EXISTS `loyalty_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `loyalty_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `transaction_type` enum('earned','redeemed','expired','bonus') NOT NULL,
  `points` int(11) NOT NULL,
  `reason` varchar(200) DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `processed_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_member` (`member_id`),
  KEY `idx_type` (`transaction_type`),
  KEY `idx_reference` (`reference_type`,`reference_id`),
  KEY `idx_expiry` (`expiry_date`),
  KEY `idx_processed` (`processed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `marketplace_analytics`
--

DROP TABLE IF EXISTS `marketplace_analytics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `marketplace_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `metric_type` varchar(50) NOT NULL,
  `metric_value` decimal(10,2) DEFAULT NULL,
  `dimension` varchar(50) DEFAULT NULL,
  `dimension_value` varchar(100) DEFAULT NULL,
  `recorded_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type` (`metric_type`),
  KEY `idx_dimension` (`dimension`,`dimension_value`),
  KEY `idx_recorded` (`recorded_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `marketplace_campaigns`
--

DROP TABLE IF EXISTS `marketplace_campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `marketplace_campaigns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type` (`campaign_type`),
  KEY `idx_dates` (`start_date`,`end_date`),
  KEY `idx_active` (`active`),
  KEY `idx_code` (`coupon_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `marketplace_cart`
--

DROP TABLE IF EXISTS `marketplace_cart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `marketplace_cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_cart_item` (`user_id`,`product_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `marketplace_categories`
--

DROP TABLE IF EXISTS `marketplace_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `marketplace_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_parent` (`parent_id`),
  KEY `idx_active` (`active`),
  KEY `idx_sort` (`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `marketplace_favorites`
--

DROP TABLE IF EXISTS `marketplace_favorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `marketplace_favorites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_favorite` (`user_id`,`product_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `marketplace_products`
--

DROP TABLE IF EXISTS `marketplace_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `marketplace_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_seller` (`seller_id`,`seller_type`),
  KEY `idx_category` (`category_id`),
  KEY `idx_status` (`status`),
  KEY `idx_price` (`price`),
  KEY `idx_featured` (`featured`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `marketplace_reviews`
--

DROP TABLE IF EXISTS `marketplace_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `marketplace_reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `transaction_id` (`transaction_id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_seller` (`seller_id`),
  KEY `idx_reviewer` (`reviewer_id`),
  KEY `idx_rating` (`rating`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `marketplace_reviews_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `marketplace_transactions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `marketplace_transactions`
--

DROP TABLE IF EXISTS `marketplace_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `marketplace_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_type` varchar(50) NOT NULL,
  `buyer_id` int(11) DEFAULT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `platform_fee` decimal(10,2) DEFAULT 0.00,
  `status` enum('pending','completed','cancelled','refunded') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type_status` (`transaction_type`,`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_marketplace_recent` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `meeting_attendance`
--

DROP TABLE IF EXISTS `meeting_attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `meeting_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `meeting_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `attendance_status` enum('hadir','izin','tanpa_keterangan','tidak_hadir') DEFAULT 'tidak_hadir',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `meeting_id` (`meeting_id`),
  KEY `member_id` (`member_id`),
  CONSTRAINT `meeting_attendance_ibfk_1` FOREIGN KEY (`meeting_id`) REFERENCES `koperasi_meetings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `meeting_attendance_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `anggota` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `meeting_decisions`
--

DROP TABLE IF EXISTS `meeting_decisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `meeting_decisions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `meeting_id` int(11) NOT NULL,
  `decision_number` int(11) NOT NULL,
  `decision_title` varchar(255) NOT NULL,
  `decision_content` text NOT NULL,
  `decision_type` enum('kebijakan','program','anggaran','personalia','lainnya') DEFAULT 'kebijakan',
  `implementation_status` enum('belum_direalisasi','sedang_direalisasi','selesai','dibatalkan') DEFAULT 'belum_direalisasi',
  `responsible_person` int(11) DEFAULT NULL,
  `target_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `meeting_id` (`meeting_id`),
  KEY `responsible_person` (`responsible_person`),
  CONSTRAINT `meeting_decisions_ibfk_1` FOREIGN KEY (`meeting_id`) REFERENCES `koperasi_meetings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `meeting_decisions_ibfk_2` FOREIGN KEY (`responsible_person`) REFERENCES `anggota` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `member_feedback`
--

DROP TABLE IF EXISTS `member_feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `member_feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `feedback_type` varchar(50) NOT NULL,
  `rating` decimal(3,1) DEFAULT 0.0,
  `feedback_text` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `responded` tinyint(1) DEFAULT 0,
  `response_text` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_type` (`user_id`,`feedback_type`),
  KEY `idx_rating` (`rating`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_feedback_recent` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `modal_pokok`
--

DROP TABLE IF EXISTS `modal_pokok`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `modal_pokok` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `anggota_id` int(11) NOT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `jenis` enum('masuk','keluar') NOT NULL,
  `tanggal` date NOT NULL,
  `description` text DEFAULT NULL,
  `bukti` varchar(255) DEFAULT NULL,
  `status` enum('draft','approved','rejected') DEFAULT 'draft',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `anggota_id` (`anggota_id`),
  KEY `approved_by` (`approved_by`),
  CONSTRAINT `modal_pokok_ibfk_1` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`) ON DELETE CASCADE,
  CONSTRAINT `modal_pokok_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `monitoring_metrics`
--

DROP TABLE IF EXISTS `monitoring_metrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `monitoring_metrics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(50) NOT NULL,
  `metric_name` varchar(100) NOT NULL,
  `metric_value` decimal(10,2) DEFAULT 0.00,
  `timestamp` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_metric` (`category`,`metric_name`,`timestamp`),
  KEY `idx_monitoring_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `network_analytics`
--

DROP TABLE IF EXISTS `network_analytics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `network_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `metric_type` varchar(50) NOT NULL,
  `metric_period` varchar(20) NOT NULL,
  `metric_value` decimal(15,2) DEFAULT NULL,
  `dimension` varchar(50) DEFAULT NULL,
  `dimension_value` varchar(100) DEFAULT NULL,
  `cooperative_id` int(11) DEFAULT NULL,
  `recorded_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type` (`metric_type`),
  KEY `idx_period` (`metric_period`),
  KEY `idx_dimension` (`dimension`,`dimension_value`),
  KEY `idx_cooperative` (`cooperative_id`),
  KEY `idx_recorded` (`recorded_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `network_governance`
--

DROP TABLE IF EXISTS `network_governance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `network_governance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type` (`governance_type`),
  KEY `idx_status` (`status`),
  KEY `idx_effective` (`effective_date`),
  KEY `idx_enforcement` (`enforcement_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `notification_logs`
--

DROP TABLE IF EXISTS `notification_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference_id` int(11) DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `channel` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `recipient` varchar(20) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'sent',
  `sent_at` timestamp NULL DEFAULT current_timestamp(),
  `error_message` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_notification_logs_reference` (`reference_id`),
  KEY `idx_notification_logs_type` (`type`),
  KEY `idx_notification_logs_channel` (`channel`),
  KEY `idx_notification_logs_user` (`user_id`),
  KEY `idx_notification_logs_sent_at` (`sent_at`),
  CONSTRAINT `notification_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `notification_logs_ibfk_2` FOREIGN KEY (`reference_id`) REFERENCES `penjualan` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `operational_costs`
--

DROP TABLE IF EXISTS `operational_costs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `operational_costs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(100) NOT NULL,
  `subcategory` varchar(100) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'IDR',
  `period` date NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_category_period` (`category`,`period`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `optimization_alerts`
--

DROP TABLE IF EXISTS `optimization_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `optimization_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type` (`alert_type`),
  KEY `idx_severity` (`severity`),
  KEY `idx_resolved` (`resolved`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `optimization_executions`
--

DROP TABLE IF EXISTS `optimization_executions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `optimization_executions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_id` int(11) NOT NULL,
  `execution_status` enum('success','failed','partial') DEFAULT 'success',
  `triggered_by` varchar(100) DEFAULT NULL,
  `execution_time` decimal(5,2) DEFAULT NULL,
  `impact_metrics` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`impact_metrics`)),
  `error_message` text DEFAULT NULL,
  `executed_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_rule` (`rule_id`),
  KEY `idx_status` (`execution_status`),
  KEY `idx_executed` (`executed_at`),
  CONSTRAINT `optimization_executions_ibfk_1` FOREIGN KEY (`rule_id`) REFERENCES `optimization_rules` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `optimization_predictions`
--

DROP TABLE IF EXISTS `optimization_predictions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `optimization_predictions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `occurred_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_type` (`prediction_type`),
  KEY `idx_status` (`status`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `optimization_rules`
--

DROP TABLE IF EXISTS `optimization_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `optimization_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_active` (`is_active`),
  KEY `idx_priority` (`priority`),
  KEY `idx_frequency` (`execution_frequency`),
  KEY `idx_last_executed` (`last_executed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oracle_data_feeds`
--

DROP TABLE IF EXISTS `oracle_data_feeds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `oracle_data_feeds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `oracle_id` int(11) NOT NULL,
  `data_key` varchar(100) NOT NULL,
  `data_value` decimal(20,8) DEFAULT NULL,
  `data_timestamp` datetime DEFAULT current_timestamp(),
  `block_number` bigint(20) DEFAULT NULL,
  `transaction_hash` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_oracle` (`oracle_id`),
  KEY `idx_key` (`data_key`),
  KEY `idx_timestamp` (`data_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `page_views`
--

DROP TABLE IF EXISTS `page_views`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `page_views` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `page_url` varchar(500) NOT NULL,
  `page_title` varchar(200) DEFAULT NULL,
  `referrer` varchar(500) DEFAULT NULL,
  `time_on_page` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_session_page` (`session_id`,`page_url`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `payment_attempts`
--

DROP TABLE IF EXISTS `payment_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `payment_gateway` varchar(50) NOT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `snap_token` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `amount` decimal(15,2) DEFAULT NULL,
  `currency` varchar(3) DEFAULT 'IDR',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_payment_attempts_order` (`order_id`),
  KEY `idx_payment_attempts_transaction` (`transaction_id`),
  CONSTRAINT `payment_attempts_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `penjualan` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `payment_logs`
--

DROP TABLE IF EXISTS `payment_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL,
  `gateway` varchar(50) NOT NULL,
  `notification_type` varchar(50) DEFAULT NULL,
  `transaction_status` varchar(50) DEFAULT NULL,
  `payment_type` varchar(50) DEFAULT NULL,
  `fraud_status` varchar(50) DEFAULT NULL,
  `raw_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`raw_data`)),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_payment_logs_order` (`order_id`),
  KEY `idx_payment_logs_created` (`created_at`),
  CONSTRAINT `payment_logs_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `penjualan` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `payroll_components`
--

DROP TABLE IF EXISTS `payroll_components`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `payroll_components` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `component_type` enum('allowance','deduction','bonus','overtime') NOT NULL,
  `component_name` varchar(255) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `is_recurring` tinyint(1) DEFAULT 1,
  `effective_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_payroll_components_employee_id` (`employee_id`),
  KEY `idx_payroll_components_type` (`component_type`),
  CONSTRAINT `payroll_components_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  CONSTRAINT `payroll_components_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `payrolls`
--

DROP TABLE IF EXISTS `payrolls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `payrolls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `processed_by` (`processed_by`),
  KEY `idx_payrolls_employee_id` (`employee_id`),
  KEY `idx_payrolls_period` (`period`),
  KEY `idx_payrolls_status` (`status`),
  CONSTRAINT `payrolls_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  CONSTRAINT `payrolls_ibfk_2` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pelanggan`
--

DROP TABLE IF EXISTS `pelanggan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pelanggan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_pelanggan` varchar(20) NOT NULL,
  `nama_pelanggan` varchar(100) NOT NULL,
  `alamat` text DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `jenis_pelanggan` enum('member','non_member') DEFAULT 'non_member',
  `anggota_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_pelanggan` (`kode_pelanggan`),
  KEY `anggota_id` (`anggota_id`),
  KEY `idx_pelanggan_kode` (`kode_pelanggan`),
  CONSTRAINT `pelanggan_ibfk_1` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pelanggaran`
--

DROP TABLE IF EXISTS `pelanggaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pelanggaran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `jenis_pelanggaran` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `tanggal_pelanggaran` date NOT NULL,
  `sanksi_id` int(11) DEFAULT NULL,
  `status` enum('investigasi','diputuskan','dieksekusi','ditutup') DEFAULT 'investigasi',
  `decided_by` int(11) DEFAULT NULL,
  `decided_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `sanksi_id` (`sanksi_id`),
  KEY `decided_by` (`decided_by`),
  KEY `idx_pelanggaran_user` (`user_id`),
  KEY `idx_pelanggaran_status` (`status`),
  KEY `idx_pelanggaran_tanggal` (`tanggal_pelanggaran`),
  CONSTRAINT `pelanggaran_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `pelanggaran_ibfk_2` FOREIGN KEY (`sanksi_id`) REFERENCES `sanksi` (`id`),
  CONSTRAINT `pelanggaran_ibfk_3` FOREIGN KEY (`decided_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pengaturan_koperasi`
--

DROP TABLE IF EXISTS `pengaturan_koperasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengaturan_koperasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `type` enum('string','number','decimal','boolean','date') DEFAULT 'string',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `penjualan`
--

DROP TABLE IF EXISTS `penjualan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `penjualan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_faktur` (`no_faktur`),
  KEY `idx_penjualan_no_faktur` (`no_faktur`),
  KEY `idx_penjualan_pelanggan` (`pelanggan_id`),
  KEY `idx_penjualan_user` (`user_id`),
  KEY `idx_penjualan_tanggal` (`tanggal_penjualan`),
  CONSTRAINT `penjualan_ibfk_1` FOREIGN KEY (`pelanggan_id`) REFERENCES `pelanggan` (`id`),
  CONSTRAINT `penjualan_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `performance_benchmarks`
--

DROP TABLE IF EXISTS `performance_benchmarks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `performance_benchmarks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `benchmark_name` varchar(100) NOT NULL,
  `metric_type` varchar(50) NOT NULL,
  `target_value` decimal(10,2) NOT NULL,
  `current_value` decimal(10,2) DEFAULT 0.00,
  `achievement_percentage` decimal(5,2) DEFAULT 0.00,
  `last_updated` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type_name` (`metric_type`,`benchmark_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `performance_metrics`
--

DROP TABLE IF EXISTS `performance_metrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `performance_metrics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `metric_type` varchar(50) NOT NULL,
  `metric_name` varchar(100) NOT NULL,
  `value` decimal(15,4) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `threshold_warning` decimal(15,4) DEFAULT NULL,
  `threshold_critical` decimal(15,4) DEFAULT NULL,
  `measured_at` timestamp NULL DEFAULT current_timestamp(),
  `context` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`context`)),
  PRIMARY KEY (`id`),
  KEY `idx_performance_metrics_type` (`metric_type`,`measured_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `performance_optimization_history`
--

DROP TABLE IF EXISTS `performance_optimization_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `performance_optimization_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type` (`optimization_type`),
  KEY `idx_status` (`status`),
  KEY `idx_implemented` (`implemented_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `module` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `personalization_rules`
--

DROP TABLE IF EXISTS `personalization_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `personalization_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_name` varchar(200) NOT NULL,
  `trigger_conditions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`trigger_conditions`)),
  `personalization_actions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`personalization_actions`)),
  `target_segment` varchar(100) DEFAULT NULL,
  `priority` int(11) DEFAULT 1,
  `effectiveness_score` decimal(5,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_segment` (`target_segment`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pinjaman`
--

DROP TABLE IF EXISTS `pinjaman`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pinjaman` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_pinjaman` (`no_pinjaman`),
  KEY `approved_by` (`approved_by`),
  KEY `created_by` (`created_by`),
  KEY `idx_pinjaman_no_pinjaman` (`no_pinjaman`),
  KEY `idx_pinjaman_anggota` (`anggota_id`),
  KEY `idx_pinjaman_jenis` (`jenis_pinjaman_id`),
  KEY `idx_pinjaman_status` (`status`),
  CONSTRAINT `pinjaman_ibfk_1` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`),
  CONSTRAINT `pinjaman_ibfk_2` FOREIGN KEY (`jenis_pinjaman_id`) REFERENCES `jenis_pinjaman` (`id`),
  CONSTRAINT `pinjaman_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  CONSTRAINT `pinjaman_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `positions`
--

DROP TABLE IF EXISTS `positions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `positions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `position_code` varchar(20) NOT NULL,
  `position_name` varchar(100) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT 1,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `position_code` (`position_code`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `positions_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `prediction_accuracy`
--

DROP TABLE IF EXISTS `prediction_accuracy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `prediction_accuracy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model_id` int(11) NOT NULL,
  `prediction_id` int(11) NOT NULL,
  `actual_value` decimal(10,2) DEFAULT NULL,
  `predicted_value` decimal(10,2) DEFAULT NULL,
  `accuracy_score` decimal(5,4) DEFAULT NULL,
  `measured_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_model` (`model_id`),
  KEY `idx_prediction` (`prediction_id`),
  KEY `idx_accuracy` (`accuracy_score`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `predictions`
--

DROP TABLE IF EXISTS `predictions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `predictions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model_id` int(11) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `prediction_type` varchar(50) NOT NULL,
  `predicted_value` decimal(10,2) DEFAULT NULL,
  `confidence_score` decimal(5,4) DEFAULT 0.0000,
  `prediction_factors` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`prediction_factors`)),
  `time_horizon` varchar(20) DEFAULT '3_months',
  `predicted_at` datetime DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_model` (`model_id`),
  KEY `idx_entity` (`entity_type`,`entity_id`),
  KEY `idx_type` (`prediction_type`),
  KEY `idx_predicted_at` (`predicted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `predictive_models`
--

DROP TABLE IF EXISTS `predictive_models`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `predictive_models` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type` (`prediction_type`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `product_recommendations`
--

DROP TABLE IF EXISTS `product_recommendations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_recommendations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `recommendation_score` decimal(5,4) DEFAULT NULL,
  `recommendation_reason` varchar(100) DEFAULT NULL,
  `algorithm_used` varchar(50) DEFAULT NULL,
  `shown` tinyint(1) DEFAULT 0,
  `clicked` tinyint(1) DEFAULT 0,
  `purchased` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_score` (`recommendation_score`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `produk`
--

DROP TABLE IF EXISTS `produk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `produk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_produk` (`kode_produk`),
  KEY `created_by` (`created_by`),
  KEY `idx_produk_kode_produk` (`kode_produk`),
  KEY `idx_produk_kategori` (`kategori_id`),
  CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori_produk` (`id`),
  CONSTRAINT `produk_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `promos`
--

DROP TABLE IF EXISTS `promos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `promos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_promo` varchar(20) NOT NULL,
  `jenis_diskon` enum('persen','nominal') NOT NULL,
  `nilai_diskon` decimal(10,2) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_akhir` date NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `status` enum('aktif','nonaktif','kadaluarsa') DEFAULT 'aktif',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_promo` (`kode_promo`),
  KEY `created_by` (`created_by`),
  KEY `idx_promos_kode` (`kode_promo`),
  KEY `idx_promos_status` (`status`),
  KEY `idx_promos_tanggal` (`tanggal_mulai`,`tanggal_akhir`),
  CONSTRAINT `promos_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `purchase_order_items`
--

DROP TABLE IF EXISTS `purchase_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `po_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `quantity_ordered` decimal(10,2) NOT NULL,
  `quantity_received` decimal(10,2) DEFAULT 0.00,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(12,2) NOT NULL,
  `quality_status` enum('pending','passed','failed','rejected') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_po` (`po_id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_quality` (`quality_status`),
  KEY `idx_po_items_po` (`po_id`),
  CONSTRAINT `purchase_order_items_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `purchase_orders`
--

DROP TABLE IF EXISTS `purchase_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `po_number` (`po_number`),
  KEY `idx_status` (`status`),
  KEY `idx_vendor` (`vendor_id`),
  KEY `idx_date` (`order_date`),
  CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quality_check_criteria`
--

DROP TABLE IF EXISTS `quality_check_criteria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `quality_check_criteria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inspection_id` int(11) NOT NULL,
  `criteria_name` varchar(200) NOT NULL,
  `expected_value` varchar(100) DEFAULT NULL,
  `actual_value` varchar(100) DEFAULT NULL,
  `result` enum('pass','fail','na') DEFAULT 'pass',
  `severity` enum('critical','major','minor') DEFAULT 'minor',
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_inspection` (`inspection_id`),
  KEY `idx_result` (`result`),
  KEY `idx_severity` (`severity`),
  KEY `idx_quality_criteria_inspection` (`inspection_id`),
  CONSTRAINT `quality_check_criteria_ibfk_1` FOREIGN KEY (`inspection_id`) REFERENCES `quality_inspections` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quality_inspections`
--

DROP TABLE IF EXISTS `quality_inspections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `quality_inspections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_reference` (`reference_type`,`reference_id`),
  KEY `idx_type` (`inspection_type`),
  KEY `idx_result` (`overall_result`),
  KEY `idx_inspector` (`inspector_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rapat`
--

DROP TABLE IF EXISTS `rapat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rapat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) NOT NULL,
  `jenis_rapat` enum('rapat_anggota','rapat_pengurus','rapat_pengawas') NOT NULL,
  `tanggal` date NOT NULL,
  `waktu` time DEFAULT NULL,
  `lokasi` text DEFAULT NULL,
  `agenda` text DEFAULT NULL,
  `status` enum('terjadwal','berlangsung','selesai','dibatalkan') DEFAULT 'terjadwal',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_rapat_tanggal` (`tanggal`),
  KEY `idx_rapat_status` (`status`),
  KEY `idx_rapat_jenis` (`jenis_rapat`),
  CONSTRAINT `rapat_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rapat_keputusan`
--

DROP TABLE IF EXISTS `rapat_keputusan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rapat_keputusan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rapat_id` int(11) NOT NULL,
  `keputusan` text NOT NULL,
  `status_pelaksanaan` enum('belum_dilaksanakan','dalam_proses','selesai') DEFAULT 'belum_dilaksanakan',
  `pic` int(11) DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `pic` (`pic`),
  KEY `idx_rapat_keputusan_rapat` (`rapat_id`),
  CONSTRAINT `rapat_keputusan_ibfk_1` FOREIGN KEY (`rapat_id`) REFERENCES `rapat` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rapat_keputusan_ibfk_2` FOREIGN KEY (`pic`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rapat_notulen`
--

DROP TABLE IF EXISTS `rapat_notulen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rapat_notulen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rapat_id` int(11) NOT NULL,
  `isi_notulen` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_rapat_notulen_rapat` (`rapat_id`),
  CONSTRAINT `rapat_notulen_ibfk_1` FOREIGN KEY (`rapat_id`) REFERENCES `rapat` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rapat_notulen_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rapat_peserta`
--

DROP TABLE IF EXISTS `rapat_peserta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rapat_peserta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rapat_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status_kehadiran` enum('hadir','tidak_hadir','izin') DEFAULT 'tidak_hadir',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_rapat_peserta_rapat` (`rapat_id`),
  KEY `idx_rapat_peserta_user` (`user_id`),
  CONSTRAINT `rapat_peserta_ibfk_1` FOREIGN KEY (`rapat_id`) REFERENCES `rapat` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rapat_peserta_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rat_attendance`
--

DROP TABLE IF EXISTS `rat_attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rat_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rat_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `attendance_type` enum('present','proxy','absent') DEFAULT 'present',
  `proxy_holder_name` varchar(100) DEFAULT NULL,
  `proxy_holder_nik` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_attendance` (`rat_id`,`member_id`),
  KEY `member_id` (`member_id`),
  CONSTRAINT `rat_attendance_ibfk_1` FOREIGN KEY (`rat_id`) REFERENCES `rat_meetings` (`id`),
  CONSTRAINT `rat_attendance_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `cooperative_members` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rat_meetings`
--

DROP TABLE IF EXISTS `rat_meetings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rat_meetings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_cooperative` (`cooperative_id`),
  KEY `idx_year` (`rat_year`),
  KEY `idx_date` (`meeting_date`),
  KEY `idx_quorum` (`quorum_achieved`),
  KEY `idx_rat_cooperative_year` (`cooperative_id`,`rat_year`),
  CONSTRAINT `rat_meetings_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `cooperative_structure` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `recommendation_engine`
--

DROP TABLE IF EXISTS `recommendation_engine`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `recommendation_engine` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_item` (`item_type`,`item_id`),
  KEY `idx_score` (`recommendation_score`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ref_districts`
--

DROP TABLE IF EXISTS `ref_districts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ref_districts` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `regency_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_districts_regency` (`regency_id`),
  CONSTRAINT `ref_districts_ibfk_1` FOREIGN KEY (`regency_id`) REFERENCES `ref_regencies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ref_provinces`
--

DROP TABLE IF EXISTS `ref_provinces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ref_provinces` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ref_regencies`
--

DROP TABLE IF EXISTS `ref_regencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ref_regencies` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `province_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_regencies_province` (`province_id`),
  CONSTRAINT `ref_regencies_ibfk_1` FOREIGN KEY (`province_id`) REFERENCES `ref_provinces` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ref_villages`
--

DROP TABLE IF EXISTS `ref_villages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ref_villages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `district_id` int(11) NOT NULL,
  `kodepos` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_villages_district` (`district_id`),
  CONSTRAINT `ref_villages_ibfk_1` FOREIGN KEY (`district_id`) REFERENCES `ref_districts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `regulatory_reports`
--

DROP TABLE IF EXISTS `regulatory_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `regulatory_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `supporting_documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`supporting_documents`)),
  PRIMARY KEY (`id`),
  KEY `idx_cooperative` (`cooperative_id`),
  KEY `idx_type` (`report_type`),
  KEY `idx_period` (`report_year`,`report_quarter`,`report_month`),
  KEY `idx_submission` (`submitted_to`,`submission_date`),
  KEY `idx_approval` (`approval_status`),
  KEY `idx_reports_cooperative_type` (`cooperative_id`,`report_type`,`report_year`),
  CONSTRAINT `regulatory_reports_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `cooperative_structure` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `replenishment_rules`
--

DROP TABLE IF EXISTS `replenishment_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `replenishment_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_auto_reorder` (`auto_reorder_enabled`),
  KEY `idx_replenishment_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reserve_funds`
--

DROP TABLE IF EXISTS `reserve_funds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `reserve_funds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_cooperative` (`cooperative_id`),
  KEY `idx_type` (`fund_type`),
  KEY `idx_year` (`fund_year`),
  CONSTRAINT `reserve_funds_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `cooperative_structure` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `revenue_metrics`
--

DROP TABLE IF EXISTS `revenue_metrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `revenue_metrics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source_category` varchar(100) NOT NULL,
  `source_subcategory` varchar(100) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'IDR',
  `transaction_count` int(11) DEFAULT 1,
  `period` date NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_category_period` (`source_category`,`period`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `risk_alerts`
--

DROP TABLE IF EXISTS `risk_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `risk_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `resolved_by` (`resolved_by`),
  KEY `created_by` (`created_by`),
  KEY `idx_risk_alerts_type` (`type`),
  KEY `idx_risk_alerts_severity` (`severity`),
  KEY `idx_risk_alerts_status` (`status`),
  KEY `idx_risk_alerts_created_at` (`created_at`),
  CONSTRAINT `risk_alerts_ibfk_1` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`),
  CONSTRAINT `risk_alerts_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `risk_metrics`
--

DROP TABLE IF EXISTS `risk_metrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `risk_metrics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `risk_type` varchar(50) NOT NULL,
  `risk_level` enum('low','medium','high','critical') DEFAULT 'low',
  `risk_score` decimal(5,2) DEFAULT 0.00,
  `mitigation_status` enum('identified','mitigating','resolved') DEFAULT 'identified',
  `affected_records` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type_level` (`risk_type`,`risk_level`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `roi_tracking`
--

DROP TABLE IF EXISTS `roi_tracking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `roi_tracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `investment` decimal(15,2) NOT NULL,
  `revenue_increase` decimal(15,2) DEFAULT 0.00,
  `cost_reduction` decimal(15,2) DEFAULT 0.00,
  `period` varchar(20) NOT NULL,
  `calculated_roi` decimal(5,2) DEFAULT 0.00,
  `calculated_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_period` (`period`,`calculated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rpa_executions`
--

DROP TABLE IF EXISTS `rpa_executions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rpa_executions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `process_id` int(11) NOT NULL,
  `execution_status` enum('running','completed','failed','cancelled') DEFAULT 'running',
  `started_at` datetime DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL,
  `execution_time_seconds` int(11) DEFAULT 0,
  `records_processed` int(11) DEFAULT 0,
  `errors_encountered` int(11) DEFAULT 0,
  `error_details` text DEFAULT NULL,
  `savings_achieved` decimal(15,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `idx_process` (`process_id`),
  KEY `idx_status` (`execution_status`),
  KEY `idx_started` (`started_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rpa_processes`
--

DROP TABLE IF EXISTS `rpa_processes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rpa_processes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `process_name` varchar(100) NOT NULL,
  `process_category` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `trigger_condition` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`trigger_condition`)),
  `steps` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`steps`)),
  `estimated_savings` decimal(15,2) DEFAULT 0.00,
  `execution_time_seconds` int(11) DEFAULT 0,
  `success_rate` decimal(5,2) DEFAULT 0.00,
  `status` enum('active','inactive','testing') DEFAULT 'inactive',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_category` (`process_category`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sanksi`
--

DROP TABLE IF EXISTS `sanksi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sanksi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jenis_sanksi` enum('teguran_lisan','teguran_tertulis','pemberhentian_sementara') NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `dasar_hukum` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `service_subscriptions`
--

DROP TABLE IF EXISTS `service_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `service_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_service` (`service_id`),
  KEY `idx_subscriber` (`subscriber_coop_id`),
  KEY `idx_status` (`status`),
  KEY `idx_end_date` (`subscription_end`),
  CONSTRAINT `service_subscriptions_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `shared_services` (`id`),
  CONSTRAINT `service_subscriptions_ibfk_2` FOREIGN KEY (`subscriber_coop_id`) REFERENCES `cooperative_network` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','number','boolean','json') DEFAULT 'text',
  `deskripsi` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `updated_by` (`updated_by`),
  KEY `idx_settings_key` (`setting_key`),
  CONSTRAINT `settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shared_services`
--

DROP TABLE IF EXISTS `shared_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `shared_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_category` (`service_category`),
  KEY `idx_provider` (`service_provider_id`),
  KEY `idx_type` (`service_type`),
  KEY `idx_status` (`status`),
  KEY `idx_availability` (`availability_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shipment_items`
--

DROP TABLE IF EXISTS `shipment_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `shipment_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shipment_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_weight` decimal(5,2) DEFAULT NULL,
  `quality_check_status` enum('pending','passed','failed') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_shipment` (`shipment_id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_shipment_items_shipment` (`shipment_id`),
  CONSTRAINT `shipment_items_ibfk_1` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shipments`
--

DROP TABLE IF EXISTS `shipments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `shipments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `shipment_number` (`shipment_number`),
  KEY `idx_reference` (`reference_type`,`reference_id`),
  KEY `idx_status` (`status`),
  KEY `idx_tracking` (`tracking_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shipping_details`
--

DROP TABLE IF EXISTS `shipping_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `shipping_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `courier` varchar(50) NOT NULL,
  `service` varchar(100) NOT NULL,
  `cost` decimal(10,2) NOT NULL,
  `estimated_days` varchar(50) DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_shipping_details_order` (`order_id`),
  KEY `idx_shipping_details_courier` (`courier`),
  KEY `idx_shipping_details_status` (`status`),
  CONSTRAINT `shipping_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `penjualan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shu_anggota`
--

DROP TABLE IF EXISTS `shu_anggota`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `shu_anggota` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `anggota_id` int(11) NOT NULL,
  `shu_periode_id` int(11) NOT NULL,
  `jumlah_simpanan` decimal(15,2) DEFAULT 0.00,
  `total_shu` decimal(15,2) DEFAULT 0.00,
  `persentase_shu` decimal(5,2) DEFAULT 0.00,
  `status` enum('calculated','paid','reserved') DEFAULT 'calculated',
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `anggota_id` (`anggota_id`),
  KEY `shu_periode_id` (`shu_periode_id`),
  CONSTRAINT `shu_anggota_ibfk_1` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`) ON DELETE CASCADE,
  CONSTRAINT `shu_anggota_ibfk_2` FOREIGN KEY (`shu_periode_id`) REFERENCES `shu_periode` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shu_components`
--

DROP TABLE IF EXISTS `shu_components`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `shu_components` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `component_code` varchar(50) NOT NULL,
  `component_name` varchar(255) NOT NULL,
  `component_type` enum('jasa_modal','jasa_usaha','pendidikan_sosial','honorarium','lainnya') NOT NULL,
  `percentage_weight` decimal(5,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `component_code` (`component_code`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shu_member_distribution`
--

DROP TABLE IF EXISTS `shu_member_distribution`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `shu_member_distribution` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `shu_period_id` (`shu_period_id`),
  KEY `member_id` (`member_id`),
  KEY `component_code` (`component_code`),
  CONSTRAINT `shu_member_distribution_ibfk_1` FOREIGN KEY (`shu_period_id`) REFERENCES `shu_periods` (`id`) ON DELETE CASCADE,
  CONSTRAINT `shu_member_distribution_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `anggota` (`id`) ON DELETE CASCADE,
  CONSTRAINT `shu_member_distribution_ibfk_3` FOREIGN KEY (`component_code`) REFERENCES `shu_components` (`component_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shu_periode`
--

DROP TABLE IF EXISTS `shu_periode`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `shu_periode` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `periode_start` date NOT NULL,
  `periode_end` date NOT NULL,
  `total_shu` decimal(15,2) DEFAULT 0.00,
  `persentase_modal` decimal(5,2) DEFAULT 0.00,
  `persentase_jasa` decimal(5,2) DEFAULT 0.00,
  `status` enum('draft','calculated','distributed') DEFAULT 'draft',
  `calculated_at` timestamp NULL DEFAULT NULL,
  `distributed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `shu_periods`
--

DROP TABLE IF EXISTS `shu_periods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `shu_periods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `calculated_by` (`calculated_by`),
  KEY `approved_by` (`approved_by`),
  KEY `distributed_by` (`distributed_by`),
  CONSTRAINT `shu_periods_ibfk_1` FOREIGN KEY (`calculated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `shu_periods_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `shu_periods_ibfk_3` FOREIGN KEY (`distributed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `simpanan`
--

DROP TABLE IF EXISTS `simpanan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `simpanan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `anggota_id` int(11) NOT NULL,
  `jenis_simpanan_id` int(11) NOT NULL,
  `no_rekening` varchar(30) NOT NULL,
  `saldo` decimal(15,2) DEFAULT 0.00,
  `status` enum('aktif','ditutup','dibekukan') DEFAULT 'aktif',
  `tanggal_buka` date DEFAULT NULL,
  `tanggal_tutup` date DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_rekening` (`no_rekening`),
  KEY `created_by` (`created_by`),
  KEY `idx_simpanan_no_rekening` (`no_rekening`),
  KEY `idx_simpanan_anggota` (`anggota_id`),
  KEY `idx_simpanan_jenis` (`jenis_simpanan_id`),
  CONSTRAINT `simpanan_ibfk_1` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`),
  CONSTRAINT `simpanan_ibfk_2` FOREIGN KEY (`jenis_simpanan_id`) REFERENCES `jenis_simpanan` (`id`),
  CONSTRAINT `simpanan_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smart_contracts`
--

DROP TABLE IF EXISTS `smart_contracts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `smart_contracts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `contract_address` (`contract_address`),
  KEY `idx_type` (`contract_type`),
  KEY `idx_status` (`status`),
  KEY `idx_network` (`deployed_network`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `status_types`
--

DROP TABLE IF EXISTS `status_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `status_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(50) NOT NULL,
  `status_code` varchar(50) NOT NULL,
  `status_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `supervision_records`
--

DROP TABLE IF EXISTS `supervision_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `supervision_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `supervisor_id` (`supervisor_id`),
  KEY `supervised_person_id` (`supervised_person_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `supervision_records_ibfk_1` FOREIGN KEY (`supervisor_id`) REFERENCES `anggota` (`id`) ON DELETE CASCADE,
  CONSTRAINT `supervision_records_ibfk_2` FOREIGN KEY (`supervised_person_id`) REFERENCES `anggota` (`id`) ON DELETE CASCADE,
  CONSTRAINT `supervision_records_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `supplier_performance`
--

DROP TABLE IF EXISTS `supplier_performance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `supplier_performance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_id` int(11) NOT NULL,
  `evaluation_period` varchar(20) NOT NULL,
  `evaluation_date` date NOT NULL,
  `on_time_delivery_rate` decimal(5,2) DEFAULT 0.00,
  `quality_rating` decimal(3,1) DEFAULT 0.0,
  `responsiveness_rating` decimal(3,1) DEFAULT 0.0,
  `price_competitiveness` decimal(3,1) DEFAULT 0.0,
  `overall_score` decimal(5,2) DEFAULT 0.00,
  `improvement_areas` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_vendor` (`vendor_id`),
  KEY `idx_period` (`evaluation_period`),
  KEY `idx_date` (`evaluation_date`),
  KEY `idx_score` (`overall_score`),
  KEY `idx_performance_vendor_period` (`vendor_id`,`evaluation_period`),
  CONSTRAINT `supplier_performance_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `supply_chain_alerts`
--

DROP TABLE IF EXISTS `supply_chain_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `supply_chain_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type` (`alert_type`),
  KEY `idx_severity` (`severity`),
  KEY `idx_reference` (`reference_type`,`reference_id`),
  KEY `idx_acknowledged` (`acknowledged`),
  KEY `idx_resolved` (`resolved`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `system_alerts`
--

DROP TABLE IF EXISTS `system_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alert_type` varchar(50) NOT NULL,
  `alert_level` enum('info','warning','critical') DEFAULT 'info',
  `message` text NOT NULL,
  `resolved` tinyint(1) DEFAULT 0,
  `resolved_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type_level` (`alert_type`,`alert_level`),
  KEY `idx_resolved` (`resolved`),
  KEY `idx_alerts_unresolved` (`resolved`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `system_health_checks`
--

DROP TABLE IF EXISTS `system_health_checks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_health_checks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `check_name` varchar(100) NOT NULL,
  `check_category` varchar(50) NOT NULL,
  `last_check` timestamp NULL DEFAULT NULL,
  `next_check` timestamp NULL DEFAULT NULL,
  `status` enum('passing','warning','failing') DEFAULT 'passing',
  `response_time` decimal(8,2) DEFAULT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_system_health_checks_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `system_learning_patterns`
--

DROP TABLE IF EXISTS `system_learning_patterns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_learning_patterns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type` (`pattern_type`),
  KEY `idx_active` (`is_active`),
  KEY `idx_confidence` (`confidence_score`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `system_metrics`
--

DROP TABLE IF EXISTS `system_metrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_metrics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `metric_type` varchar(50) NOT NULL,
  `metric_value` decimal(10,2) DEFAULT 0.00,
  `duration` decimal(10,2) DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type_time` (`metric_type`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `system_monitoring`
--

DROP TABLE IF EXISTS `system_monitoring`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_monitoring` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_system_monitoring_type` (`check_type`,`checked_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `system_uptime`
--

DROP TABLE IF EXISTS `system_uptime`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_uptime` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `uptime_percentage` decimal(5,2) DEFAULT 100.00,
  `downtime_minutes` int(11) DEFAULT 0,
  `incidents_count` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tax_filings`
--

DROP TABLE IF EXISTS `tax_filings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tax_filings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `filed_by` (`filed_by`),
  KEY `approved_by` (`approved_by`),
  CONSTRAINT `tax_filings_ibfk_1` FOREIGN KEY (`filed_by`) REFERENCES `users` (`id`),
  CONSTRAINT `tax_filings_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tax_payments`
--

DROP TABLE IF EXISTS `tax_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tax_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `paid_by` (`paid_by`),
  KEY `verified_by` (`verified_by`),
  CONSTRAINT `tax_payments_ibfk_1` FOREIGN KEY (`paid_by`) REFERENCES `users` (`id`),
  CONSTRAINT `tax_payments_ibfk_2` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `token_economics`
--

DROP TABLE IF EXISTS `token_economics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `token_economics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token_symbol` varchar(20) NOT NULL,
  `economic_model` varchar(50) NOT NULL,
  `total_supply` decimal(20,8) NOT NULL,
  `circulating_supply` decimal(20,8) DEFAULT 0.00000000,
  `staking_rewards` decimal(5,2) DEFAULT 0.00,
  `transaction_fees` decimal(5,2) DEFAULT 0.00,
  `burn_rate` decimal(5,2) DEFAULT 0.00,
  `vesting_schedule` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`vesting_schedule`)),
  `distribution_schedule` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`distribution_schedule`)),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_token` (`token_symbol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `token_staking`
--

DROP TABLE IF EXISTS `token_staking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `token_staking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `wallet_address` varchar(100) NOT NULL,
  `staked_amount` decimal(20,8) NOT NULL,
  `staking_start` datetime DEFAULT current_timestamp(),
  `staking_end` datetime DEFAULT NULL,
  `reward_rate` decimal(5,2) NOT NULL,
  `accumulated_rewards` decimal(20,8) DEFAULT 0.00000000,
  `status` enum('active','unstaking','completed') DEFAULT 'active',
  `unstaking_tx_hash` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_wallet` (`wallet_address`),
  KEY `idx_status` (`status`),
  KEY `idx_staking_end` (`staking_end`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `trade_items`
--

DROP TABLE IF EXISTS `trade_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `trade_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_trade` (`trade_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `trade_items_ibfk_1` FOREIGN KEY (`trade_id`) REFERENCES `inter_coop_trades` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `transaction_metrics`
--

DROP TABLE IF EXISTS `transaction_metrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaction_metrics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` int(11) NOT NULL,
  `transaction_type` varchar(50) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `processing_time` decimal(5,2) DEFAULT 0.00,
  `success` tinyint(1) DEFAULT 1,
  `error_message` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_type_method` (`transaction_type`,`payment_method`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_transactions_recent` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `transaksi_simpanan`
--

DROP TABLE IF EXISTS `transaksi_simpanan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaksi_simpanan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `simpanan_id` int(11) NOT NULL,
  `jenis_transaksi` enum('setoran','penarikan','bunga') NOT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `saldo_sebelum` decimal(15,2) NOT NULL,
  `saldo_setelah` decimal(15,2) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `tanggal_transaksi` datetime DEFAULT current_timestamp(),
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_trans_simpanan_simpanan` (`simpanan_id`),
  KEY `idx_trans_simpanan_user` (`user_id`),
  KEY `idx_trans_simpanan_tanggal` (`tanggal_transaksi`),
  CONSTRAINT `transaksi_simpanan_ibfk_1` FOREIGN KEY (`simpanan_id`) REFERENCES `simpanan` (`id`),
  CONSTRAINT `transaksi_simpanan_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `update_saldo_simpanan` AFTER INSERT ON `transaksi_simpanan` FOR EACH ROW BEGIN
    UPDATE simpanan 
    SET saldo = NEW.saldo_setelah,
        updated_at = NOW()
    WHERE id = NEW.simpanan_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `transparency_logs`
--

DROP TABLE IF EXISTS `transparency_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `transparency_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `old_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_data`)),
  `new_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_data`)),
  `recorded_by` int(11) DEFAULT NULL,
  `recorded_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `recorded_by` (`recorded_by`),
  KEY `idx_transparency_logs_action` (`action`),
  KEY `idx_transparency_logs_entity` (`entity_type`,`entity_id`),
  CONSTRAINT `transparency_logs_ibfk_1` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_activities`
--

DROP TABLE IF EXISTS `user_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `activity_type` varchar(100) NOT NULL,
  `activity_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`activity_data`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_activity` (`user_id`,`activity_type`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_activities_recent` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_behavior_analytics`
--

DROP TABLE IF EXISTS `user_behavior_analytics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_behavior_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `behavior_type` varchar(50) NOT NULL,
  `behavior_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`behavior_data`)),
  `session_context` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`session_context`)),
  `device_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`device_info`)),
  `location_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`location_data`)),
  `timestamp` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_behavior` (`behavior_type`),
  KEY `idx_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_profiles`
--

DROP TABLE IF EXISTS `user_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `profile_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`profile_data`)),
  `preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferences`)),
  `behavior_patterns` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`behavior_patterns`)),
  `risk_profile` varchar(50) DEFAULT NULL,
  `engagement_score` decimal(5,2) DEFAULT 0.00,
  `last_updated` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_risk_profile` (`risk_profile`),
  KEY `idx_engagement` (`engagement_score`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_roles`
--

DROP TABLE IF EXISTS `user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role_id` (`role_id`),
  KEY `assigned_by` (`assigned_by`),
  CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_roles_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_sessions`
--

DROP TABLE IF EXISTS `user_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `session_start` datetime DEFAULT current_timestamp(),
  `last_activity` datetime DEFAULT current_timestamp(),
  `session_duration` int(11) DEFAULT 0,
  `page_views` int(11) DEFAULT 0,
  `device_type` enum('desktop','mobile','tablet') DEFAULT 'desktop',
  `browser` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_session` (`user_id`,`session_id`),
  KEY `idx_last_activity` (`last_activity`),
  KEY `idx_sessions_active` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ux_experiment_results`
--

DROP TABLE IF EXISTS `ux_experiment_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ux_experiment_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `experiment_id` int(11) NOT NULL,
  `design_variant` varchar(10) NOT NULL,
  `unique_visitors` int(11) DEFAULT 0,
  `total_interactions` int(11) DEFAULT 0,
  `conversion_events` int(11) DEFAULT 0,
  `average_session_duration` decimal(5,2) DEFAULT NULL,
  `bounce_rate` decimal(5,2) DEFAULT NULL,
  `measured_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_experiment` (`experiment_id`),
  KEY `idx_variant` (`design_variant`),
  CONSTRAINT `ux_experiment_results_ibfk_1` FOREIGN KEY (`experiment_id`) REFERENCES `ux_optimization_experiments` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ux_optimization_experiments`
--

DROP TABLE IF EXISTS `ux_optimization_experiments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ux_optimization_experiments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_page` (`page_url`),
  KEY `idx_status` (`status`),
  KEY `idx_dates` (`start_date`,`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Temporary table structure for view `v_anggota_alamat`
--

DROP TABLE IF EXISTS `v_anggota_alamat`;
/*!50001 DROP VIEW IF EXISTS `v_anggota_alamat`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `v_anggota_alamat` AS SELECT
 1 AS `id`,
  1 AS `nama_lengkap`,
  1 AS `alamat`,
  1 AS `province_name`,
  1 AS `regency_name`,
  1 AS `district_name`,
  1 AS `village_name`,
  1 AS `kodepos`,
  1 AS `alamat_lengkap` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_koperasi_activity_summary`
--

DROP TABLE IF EXISTS `v_koperasi_activity_summary`;
/*!50001 DROP VIEW IF EXISTS `v_koperasi_activity_summary`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `v_koperasi_activity_summary` AS SELECT
 1 AS `activity_type`,
  1 AS `total_transactions`,
  1 AS `total_debit`,
  1 AS `total_credit`,
  1 AS `net_amount`,
  1 AS `unique_members` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_shu_calculation_summary`
--

DROP TABLE IF EXISTS `v_shu_calculation_summary`;
/*!50001 DROP VIEW IF EXISTS `v_shu_calculation_summary`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `v_shu_calculation_summary` AS SELECT
 1 AS `period_start`,
  1 AS `period_end`,
  1 AS `total_shu`,
  1 AS `total_distributions`,
  1 AS `distributed_amount`,
  1 AS `status` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `vendors`
--

DROP TABLE IF EXISTS `vendors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `vendor_code` (`vendor_code`),
  KEY `idx_status` (`status`),
  KEY `idx_type` (`vendor_type`),
  KEY `idx_performance` (`performance_rating`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `warehouse_zones`
--

DROP TABLE IF EXISTS `warehouse_zones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `warehouse_zones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `warehouse_id` int(11) NOT NULL,
  `zone_code` varchar(20) NOT NULL,
  `zone_name` varchar(100) NOT NULL,
  `zone_type` enum('storage','picking','shipping','receiving','damaged') DEFAULT 'storage',
  `capacity` decimal(10,2) DEFAULT NULL,
  `current_usage` decimal(10,2) DEFAULT 0.00,
  `temperature_controlled` tinyint(1) DEFAULT 0,
  `security_level` enum('low','medium','high') DEFAULT 'low',
  PRIMARY KEY (`id`),
  KEY `idx_warehouse` (`warehouse_id`),
  KEY `idx_type` (`zone_type`),
  KEY `idx_zones_warehouse` (`warehouse_id`),
  CONSTRAINT `warehouse_zones_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `warehouses`
--

DROP TABLE IF EXISTS `warehouses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `warehouses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `warehouse_code` (`warehouse_code`),
  KEY `idx_status` (`status`),
  KEY `idx_manager` (`manager_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `withholding_tax`
--

DROP TABLE IF EXISTS `withholding_tax`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `withholding_tax` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `withholding_tax_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping routines for database 'ksp_samosir'
--
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP FUNCTION IF EXISTS `validate_address_id` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `validate_address_id`(`table_name` VARCHAR(20), `id_value` INT) RETURNS tinyint(1)
    READS SQL DATA
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
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `calculate_shu_period` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `calculate_shu_period`(IN `period_start` DATE, IN `period_end` DATE, IN `calculation_method` VARCHAR(20))
BEGIN
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
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `get_address_options` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `get_address_options`(IN `parent_type` VARCHAR(20), IN `parent_id` INT)
BEGIN
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
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `get_full_address` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `get_full_address`(IN `province_id` INT, IN `regency_id` INT, IN `district_id` INT, IN `village_id` INT)
BEGIN
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
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Final view structure for view `v_anggota_alamat`
--

/*!50001 DROP VIEW IF EXISTS `v_anggota_alamat`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_anggota_alamat` AS select `a`.`id` AS `id`,`a`.`nama_lengkap` AS `nama_lengkap`,`a`.`alamat` AS `alamat`,`p`.`name` AS `province_name`,`r`.`name` AS `regency_name`,`d`.`name` AS `district_name`,`v`.`name` AS `village_name`,`v`.`kodepos` AS `kodepos`,concat(`a`.`alamat`,', ',`v`.`name`,', ',`d`.`name`,', ',`r`.`name`,', ',`p`.`name`) AS `alamat_lengkap` from ((((`anggota` `a` left join `ref_provinces` `p` on(`a`.`province_id` = `p`.`id`)) left join `ref_regencies` `r` on(`a`.`regency_id` = `r`.`id`)) left join `ref_districts` `d` on(`a`.`district_id` = `d`.`id`)) left join `ref_villages` `v` on(`a`.`village_id` = `v`.`id`)) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_koperasi_activity_summary`
--

/*!50001 DROP VIEW IF EXISTS `v_koperasi_activity_summary`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb3 */;
/*!50001 SET character_set_results     = utf8mb3 */;
/*!50001 SET collation_connection      = utf8mb3_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_koperasi_activity_summary` AS select `ka`.`activity_type` AS `activity_type`,count(0) AS `total_transactions`,sum(case when `kt`.`transaction_type` = 'debit' then `kt`.`amount` else 0 end) AS `total_debit`,sum(case when `kt`.`transaction_type` = 'credit' then `kt`.`amount` else 0 end) AS `total_credit`,sum(`kt`.`amount`) AS `net_amount`,count(distinct `kt`.`member_id`) AS `unique_members` from (`koperasi_activities` `ka` left join `koperasi_transactions` `kt` on(`ka`.`activity_code` = `kt`.`activity_code`)) where `ka`.`is_active` = 1 group by `ka`.`activity_type` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_shu_calculation_summary`
--

/*!50001 DROP VIEW IF EXISTS `v_shu_calculation_summary`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb3 */;
/*!50001 SET character_set_results     = utf8mb3 */;
/*!50001 SET collation_connection      = utf8mb3_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_shu_calculation_summary` AS select `sp`.`period_start` AS `period_start`,`sp`.`period_end` AS `period_end`,`sp`.`total_shu` AS `total_shu`,count(`smd`.`id`) AS `total_distributions`,sum(`smd`.`calculated_shu`) AS `distributed_amount`,`sp`.`status` AS `status` from (`shu_periods` `sp` left join `shu_member_distribution` `smd` on(`sp`.`id` = `smd`.`shu_period_id`)) group by `sp`.`id`,`sp`.`period_start`,`sp`.`period_end`,`sp`.`total_shu`,`sp`.`status` */;
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

-- Dump completed on 2026-02-14 22:01:09
