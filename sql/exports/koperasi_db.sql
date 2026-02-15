/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.6.23-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: koperasi_db
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
-- Table structure for table `akuntansi_jenis`
--

DROP TABLE IF EXISTS `akuntansi_jenis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `akuntansi_jenis` (
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
  CONSTRAINT `akuntansi_jenis_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `akuntansi_jenis` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `akuntansi_jenis`
--

LOCK TABLES `akuntansi_jenis` WRITE;
/*!40000 ALTER TABLE `akuntansi_jenis` DISABLE KEYS */;
INSERT INTO `akuntansi_jenis` VALUES (1,0,'1000','Kas','asset',NULL,1,'2026-02-03 14:13:20'),(2,0,'1100','Bank','asset',NULL,1,'2026-02-03 14:13:20'),(3,0,'2000','Simpanan Anggota','liability',NULL,1,'2026-02-03 14:13:20'),(4,0,'2100','Pinjaman Anggota','asset',NULL,1,'2026-02-03 14:13:20'),(5,0,'3000','Modal','equity',NULL,1,'2026-02-03 14:13:20'),(6,0,'4000','Pendapatan Bunga','revenue',NULL,1,'2026-02-03 14:13:20'),(7,0,'5000','Beban Bunga','expense',NULL,1,'2026-02-03 14:13:20'),(8,0,'5100','Beban Operasional','expense',NULL,1,'2026-02-03 14:13:20'),(10,4,'1000','Kas','asset',NULL,1,'2026-02-04 17:30:29'),(11,4,'1100','Bank','asset',NULL,1,'2026-02-04 17:30:29'),(12,4,'2000','Simpanan Anggota','liability',NULL,1,'2026-02-04 17:30:29'),(13,4,'2100','Pinjaman Anggota','asset',NULL,1,'2026-02-04 17:30:29'),(14,4,'3000','Modal','equity',NULL,1,'2026-02-04 17:30:29'),(15,4,'3100','Cadangan','equity',NULL,1,'2026-02-04 17:30:29'),(16,4,'4000','Pendapatan Bunga','revenue',NULL,1,'2026-02-04 17:30:29'),(17,4,'4100','Pendapatan Operasional','revenue',NULL,1,'2026-02-04 17:30:29'),(18,4,'5000','Beban Bunga','expense',NULL,1,'2026-02-04 17:30:29'),(19,4,'5100','Beban Operasional','expense',NULL,1,'2026-02-04 17:30:29'),(20,4,'5200','Beban Administrasi','expense',NULL,1,'2026-02-04 17:30:29');
/*!40000 ALTER TABLE `akuntansi_jenis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `akuntansi_transaksi`
--

DROP TABLE IF EXISTS `akuntansi_transaksi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `akuntansi_transaksi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cooperative_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `journal_entry_id` int(11) DEFAULT NULL,
  `jenis` enum('debit','credit') NOT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `trans_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_cooperative` (`cooperative_id`),
  KEY `idx_account` (`account_id`),
  KEY `idx_journal` (`journal_entry_id`),
  KEY `idx_trans_date` (`trans_date`),
  CONSTRAINT `akuntansi_transaksi_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `koperasi_tenant` (`id`),
  CONSTRAINT `akuntansi_transaksi_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `akuntansi_jenis` (`id`),
  CONSTRAINT `akuntansi_transaksi_ibfk_3` FOREIGN KEY (`journal_entry_id`) REFERENCES `jurnal` (`id`),
  CONSTRAINT `akuntansi_transaksi_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `pengguna` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `akuntansi_transaksi`
--

LOCK TABLES `akuntansi_transaksi` WRITE;
/*!40000 ALTER TABLE `akuntansi_transaksi` DISABLE KEYS */;
/*!40000 ALTER TABLE `akuntansi_transaksi` ENABLE KEYS */;
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
  `cooperative_id` int(11) DEFAULT NULL,
  `status_keanggotaan` enum('active','inactive','suspended') DEFAULT 'active',
  `nomor_anggota` varchar(20) NOT NULL,
  `joined_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nomor_anggota` (`nomor_anggota`),
  KEY `idx_anggota_user` (`user_id`),
  KEY `idx_anggota_cooperative` (`cooperative_id`),
  CONSTRAINT `anggota_fk_pengguna` FOREIGN KEY (`user_id`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_anggota_cooperative` FOREIGN KEY (`cooperative_id`) REFERENCES `koperasi_tenant` (`id`) ON DELETE CASCADE
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
-- Table structure for table `animal_feed`
--

DROP TABLE IF EXISTS `animal_feed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `animal_feed` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `feed_code` varchar(50) NOT NULL,
  `feed_name` varchar(255) NOT NULL,
  `feed_type` enum('concentrate','roughage','supplement','mineral','vitamin') NOT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `composition` text DEFAULT NULL,
  `nutritional_content` text DEFAULT NULL,
  `protein_content` decimal(5,2) DEFAULT NULL,
  `energy_content` decimal(8,2) DEFAULT NULL,
  `fiber_content` decimal(5,2) DEFAULT NULL,
  `form` enum('pellet','mash','crumble','granule','liquid') DEFAULT NULL,
  `unit` varchar(20) NOT NULL DEFAULT 'kg',
  `current_stock` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `min_stock` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `unit_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `supplier` varchar(255) DEFAULT NULL,
  `storage_location` varchar(255) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `target_species` varchar(255) DEFAULT NULL,
  `feeding_recommendation` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `unit_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_feed_code` (`feed_code`),
  KEY `idx_feed_type` (`feed_type`),
  KEY `idx_brand` (`brand`),
  KEY `idx_active` (`is_active`),
  KEY `idx_unit` (`unit_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `animal_feed`
--

LOCK TABLES `animal_feed` WRITE;
/*!40000 ALTER TABLE `animal_feed` DISABLE KEYS */;
INSERT INTO `animal_feed` VALUES (1,'PAK001','Pakan Sapi Komplit','concentrate','Jaya Feed',NULL,NULL,18.50,NULL,NULL,NULL,'kg',0.0000,0.0000,8000.00,NULL,NULL,NULL,NULL,NULL,1,1,1,'2026-02-12 18:03:13','2026-02-12 18:03:13'),(2,'PAK002','Rumput Gajah','roughage',NULL,NULL,NULL,8.20,NULL,NULL,NULL,'kg',0.0000,0.0000,1500.00,NULL,NULL,NULL,NULL,NULL,1,1,1,'2026-02-12 18:03:13','2026-02-12 18:03:13'),(3,'PAK003','Pakan Ayam Broiler','concentrate','Joyo Feed',NULL,NULL,21.00,NULL,NULL,NULL,'kg',0.0000,0.0000,7500.00,NULL,NULL,NULL,NULL,NULL,1,1,1,'2026-02-12 18:03:13','2026-02-12 18:03:13'),(4,'PAK004','Pakan Ayam Petelur','concentrate','Joyo Feed',NULL,NULL,19.50,NULL,NULL,NULL,'kg',0.0000,0.0000,7200.00,NULL,NULL,NULL,NULL,NULL,1,1,1,'2026-02-12 18:03:13','2026-02-12 18:03:13'),(5,'PAK005','Vitamin Mix','supplement','VitaFarm',NULL,NULL,NULL,NULL,NULL,NULL,'kg',0.0000,0.0000,25000.00,NULL,NULL,NULL,NULL,NULL,1,1,1,'2026-02-12 18:03:13','2026-02-12 18:03:13');
/*!40000 ALTER TABLE `animal_feed` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bahan_baku`
--

DROP TABLE IF EXISTS `bahan_baku`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bahan_baku` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `koperasi_id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `satuan` varchar(20) NOT NULL,
  `stok` decimal(10,2) DEFAULT 0.00,
  `stok_minimum` decimal(10,2) DEFAULT 0.00,
  `harga_beli` decimal(15,2) DEFAULT 0.00,
  `supplier` varchar(100) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `koperasi_id` (`koperasi_id`),
  CONSTRAINT `bahan_baku_ibfk_1` FOREIGN KEY (`koperasi_id`) REFERENCES `koperasi_tenant` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bahan_baku`
--

LOCK TABLES `bahan_baku` WRITE;
/*!40000 ALTER TABLE `bahan_baku` DISABLE KEYS */;
/*!40000 ALTER TABLE `bahan_baku` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `breeding_program`
--

DROP TABLE IF EXISTS `breeding_program`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `breeding_program` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `program_code` varchar(50) NOT NULL,
  `program_name` varchar(255) NOT NULL,
  `livestock_id` int(11) NOT NULL,
  `male_livestock_id` int(11) DEFAULT NULL,
  `female_livestock_id` int(11) DEFAULT NULL,
  `breeding_method` enum('natural','artificial_insemination','embryo_transfer') NOT NULL DEFAULT 'natural',
  `breeding_date` date NOT NULL,
  `expected_birth_date` date DEFAULT NULL,
  `actual_birth_date` date DEFAULT NULL,
  `pregnancy_status` enum('confirmed','pending','failed','birthed') DEFAULT NULL,
  `offspring_count` int(11) DEFAULT NULL,
  `male_offspring` int(11) DEFAULT NULL,
  `female_offspring` int(11) DEFAULT NULL,
  `birth_weight` decimal(8,2) DEFAULT NULL,
  `success_rate` decimal(5,2) DEFAULT NULL,
  `cost` decimal(15,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('planned','in_progress','completed','failed') NOT NULL DEFAULT 'planned',
  `unit_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_program_code` (`program_code`),
  KEY `idx_livestock` (`livestock_id`),
  KEY `idx_male_livestock` (`male_livestock_id`),
  KEY `idx_female_livestock` (`female_livestock_id`),
  KEY `idx_breeding_date` (`breeding_date`),
  KEY `idx_status` (`status`),
  KEY `idx_unit` (`unit_id`),
  CONSTRAINT `fk_breeding_female` FOREIGN KEY (`female_livestock_id`) REFERENCES `livestock` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_breeding_livestock` FOREIGN KEY (`livestock_id`) REFERENCES `livestock` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_breeding_male` FOREIGN KEY (`male_livestock_id`) REFERENCES `livestock` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `breeding_program`
--

LOCK TABLES `breeding_program` WRITE;
/*!40000 ALTER TABLE `breeding_program` DISABLE KEYS */;
/*!40000 ALTER TABLE `breeding_program` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `buku_besar`
--

DROP TABLE IF EXISTS `buku_besar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `buku_besar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `period` date NOT NULL,
  `cooperative_id` int(11) DEFAULT NULL,
  `beginning_balance` decimal(15,2) DEFAULT 0.00,
  `debit_total` decimal(15,2) DEFAULT 0.00,
  `credit_total` decimal(15,2) DEFAULT 0.00,
  `ending_balance` decimal(15,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_id` (`account_id`,`period`),
  KEY `idx_buku_besar_cooperative` (`cooperative_id`),
  CONSTRAINT `buku_besar_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `akuntansi_jenis` (`id`),
  CONSTRAINT `fk_buku_besar_cooperative` FOREIGN KEY (`cooperative_id`) REFERENCES `koperasi_tenant` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `buku_besar`
--

LOCK TABLES `buku_besar` WRITE;
/*!40000 ALTER TABLE `buku_besar` DISABLE KEYS */;
/*!40000 ALTER TABLE `buku_besar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catch_tracking`
--

DROP TABLE IF EXISTS `catch_tracking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `catch_tracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trip_id` int(11) NOT NULL,
  `catch_code` varchar(50) NOT NULL,
  `species` varchar(100) NOT NULL,
  `variety` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `weight` decimal(15,2) NOT NULL DEFAULT 0.00,
  `size_range` varchar(50) DEFAULT NULL,
  `quality_grade` enum('premium','good','fair','poor') DEFAULT NULL,
  `catch_method` enum('net','line','trap','spear','hand') DEFAULT NULL,
  `catch_location` varchar(255) DEFAULT NULL,
  `catch_time` datetime NOT NULL,
  `water_depth` decimal(6,2) DEFAULT NULL,
  `water_temperature` decimal(5,2) DEFAULT NULL,
  `market_price` decimal(15,2) DEFAULT NULL,
  `total_value` decimal(15,2) DEFAULT NULL,
  `buyer` varchar(255) DEFAULT NULL,
  `sale_date` date DEFAULT NULL,
  `status` enum('caught','processed','sold','disposed') NOT NULL DEFAULT 'caught',
  `notes` text DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_catch_code` (`catch_code`),
  KEY `idx_trip` (`trip_id`),
  KEY `idx_species` (`species`),
  KEY `idx_catch_date` (`catch_time`),
  KEY `idx_status` (`status`),
  KEY `idx_unit` (`unit_id`),
  CONSTRAINT `fk_catch_trip` FOREIGN KEY (`trip_id`) REFERENCES `fishing_trip` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catch_tracking`
--

LOCK TABLES `catch_tracking` WRITE;
/*!40000 ALTER TABLE `catch_tracking` DISABLE KEYS */;
/*!40000 ALTER TABLE `catch_tracking` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detail_pemesanan`
--

DROP TABLE IF EXISTS `detail_pemesanan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `detail_pemesanan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pemesanan_id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `jumlah` decimal(10,2) NOT NULL,
  `harga` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `catatan` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pemesanan_id` (`pemesanan_id`),
  KEY `produk_id` (`produk_id`),
  CONSTRAINT `detail_pemesanan_ibfk_1` FOREIGN KEY (`pemesanan_id`) REFERENCES `pemesanan_konsumsi` (`id`),
  CONSTRAINT `detail_pemesanan_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk_konsumsi` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detail_pemesanan`
--

LOCK TABLES `detail_pemesanan` WRITE;
/*!40000 ALTER TABLE `detail_pemesanan` DISABLE KEYS */;
/*!40000 ALTER TABLE `detail_pemesanan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feed_consumption`
--

DROP TABLE IF EXISTS `feed_consumption`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `feed_consumption` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `consumption_code` varchar(50) NOT NULL,
  `feed_id` int(11) NOT NULL,
  `livestock_id` int(11) NOT NULL,
  `consumption_date` date NOT NULL,
  `consumption_amount` decimal(10,4) NOT NULL DEFAULT 0.0000,
  `consumption_unit` varchar(20) NOT NULL DEFAULT 'kg',
  `cost_per_unit` decimal(15,2) DEFAULT NULL,
  `total_cost` decimal(15,2) DEFAULT NULL,
  `livestock_count` int(11) DEFAULT NULL,
  `consumption_per_head` decimal(8,4) DEFAULT NULL,
  `waste_amount` decimal(10,4) DEFAULT NULL,
  `waste_percentage` decimal(5,2) DEFAULT NULL,
  `feeding_efficiency` decimal(5,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_consumption_code` (`consumption_code`),
  KEY `idx_feed` (`feed_id`),
  KEY `idx_livestock` (`livestock_id`),
  KEY `idx_consumption_date` (`consumption_date`),
  KEY `idx_unit` (`unit_id`),
  CONSTRAINT `fk_consumption_feed` FOREIGN KEY (`feed_id`) REFERENCES `animal_feed` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_consumption_livestock` FOREIGN KEY (`livestock_id`) REFERENCES `livestock` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `feed_consumption`
--

LOCK TABLES `feed_consumption` WRITE;
/*!40000 ALTER TABLE `feed_consumption` DISABLE KEYS */;
/*!40000 ALTER TABLE `feed_consumption` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feed_inventory`
--

DROP TABLE IF EXISTS `feed_inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `feed_inventory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `feed_id` int(11) NOT NULL,
  `current_stock` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `min_stock` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `last_restock_date` date DEFAULT NULL,
  `average_daily_consumption` decimal(10,4) DEFAULT NULL,
  `days_of_supply` int(11) DEFAULT NULL,
  `storage_location` varchar(255) DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_feed_inventory` (`feed_id`,`unit_id`),
  KEY `idx_current_stock` (`current_stock`),
  KEY `idx_unit` (`unit_id`),
  CONSTRAINT `fk_feed_inventory_feed` FOREIGN KEY (`feed_id`) REFERENCES `animal_feed` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `feed_inventory`
--

LOCK TABLES `feed_inventory` WRITE;
/*!40000 ALTER TABLE `feed_inventory` DISABLE KEYS */;
INSERT INTO `feed_inventory` VALUES (1,1,500.0000,100.0000,NULL,NULL,NULL,NULL,1,1,'2026-02-12 18:03:13','2026-02-12 18:03:13'),(2,2,2000.0000,500.0000,NULL,NULL,NULL,NULL,1,1,'2026-02-12 18:03:13','2026-02-12 18:03:13'),(3,3,300.0000,50.0000,NULL,NULL,NULL,NULL,1,1,'2026-02-12 18:03:13','2026-02-12 18:03:13'),(4,4,250.0000,50.0000,NULL,NULL,NULL,NULL,1,1,'2026-02-12 18:03:13','2026-02-12 18:03:13'),(5,5,50.0000,10.0000,NULL,NULL,NULL,NULL,1,1,'2026-02-12 18:03:13','2026-02-12 18:03:13');
/*!40000 ALTER TABLE `feed_inventory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fish`
--

DROP TABLE IF EXISTS `fish`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fish` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fish_code` varchar(50) NOT NULL,
  `fish_name` varchar(255) NOT NULL,
  `species` varchar(100) NOT NULL,
  `variety` varchar(255) DEFAULT NULL,
  `origin` varchar(255) DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  `acquisition_date` date DEFAULT NULL,
  `initial_quantity` int(11) NOT NULL DEFAULT 0,
  `initial_weight` decimal(8,2) NOT NULL DEFAULT 0.00,
  `average_size` decimal(6,2) DEFAULT NULL,
  `growth_rate` decimal(5,2) DEFAULT NULL,
  `feed_type` varchar(100) DEFAULT NULL,
  `feed_conversion_ratio` decimal(5,2) DEFAULT NULL,
  `optimal_temperature` decimal(5,2) DEFAULT NULL,
  `optimal_ph` decimal(4,2) DEFAULT NULL,
  `optimal_oxygen` decimal(5,2) DEFAULT NULL,
  `market_price` decimal(15,2) DEFAULT NULL,
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_fish_code` (`fish_code`,`unit_id`),
  KEY `idx_species` (`species`),
  KEY `idx_status` (`status`),
  KEY `idx_unit` (`unit_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fish`
--

LOCK TABLES `fish` WRITE;
/*!40000 ALTER TABLE `fish` DISABLE KEYS */;
INSERT INTO `fish` VALUES (1,'IKN001','Ikan Nila','Nila',NULL,NULL,NULL,NULL,100,50.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,1,1,'2026-02-12 18:00:02','2026-02-12 18:00:02'),(2,'IKN002','Ikan Lele','Lele',NULL,NULL,NULL,NULL,150,25.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,1,1,'2026-02-12 18:00:02','2026-02-12 18:00:02'),(3,'IKN003','Ikan Mas','Mas',NULL,NULL,NULL,NULL,80,75.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,1,1,'2026-02-12 18:00:02','2026-02-12 18:00:02'),(4,'IKN004','Ikan Gurame','Gurame',NULL,NULL,NULL,NULL,50,100.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,1,1,'2026-02-12 18:00:02','2026-02-12 18:00:02'),(5,'IKN005','Ikan Patin','Patin',NULL,NULL,NULL,NULL,120,30.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,1,1,'2026-02-12 18:00:02','2026-02-12 18:00:02');
/*!40000 ALTER TABLE `fish` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb3 */ ;
/*!50003 SET character_set_results = utf8mb3 */ ;
/*!50003 SET collation_connection  = utf8mb3_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER IF NOT EXISTS `tr_fish_stock_create` 
AFTER INSERT ON `fish`
FOR EACH ROW
BEGIN
    INSERT INTO `fish_stock` (fish_id, current_stock, average_weight, total_weight, unit_id, created_by, created_at)
    VALUES (NEW.id, NEW.initial_quantity, NEW.initial_weight, NEW.initial_quantity * NEW.initial_weight, NEW.unit_id, NEW.created_by, NOW());
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `fish_feeding`
--

DROP TABLE IF EXISTS `fish_feeding`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fish_feeding` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `feeding_code` varchar(50) NOT NULL,
  `fish_id` int(11) NOT NULL,
  `feeding_date` date NOT NULL,
  `feeding_time` time DEFAULT NULL,
  `feed_type` varchar(100) NOT NULL,
  `feed_amount` decimal(10,4) NOT NULL DEFAULT 0.0000,
  `feed_unit` varchar(20) NOT NULL DEFAULT 'kg',
  `feed_cost` decimal(15,2) DEFAULT NULL,
  `fish_count` int(11) DEFAULT NULL,
  `water_temperature` decimal(5,2) DEFAULT NULL,
  `weather_condition` varchar(100) DEFAULT NULL,
  `feeding_method` enum('broadcast','hand_feeding','automatic_feeder') DEFAULT NULL,
  `fish_behavior` enum('active','normal','lethargic') DEFAULT NULL,
  `appetite` enum('good','normal','poor') DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_feeding_code` (`feeding_code`),
  KEY `idx_fish` (`fish_id`),
  KEY `idx_feeding_date` (`feeding_date`),
  KEY `idx_unit` (`unit_id`),
  CONSTRAINT `fk_feeding_fish` FOREIGN KEY (`fish_id`) REFERENCES `fish` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fish_feeding`
--

LOCK TABLES `fish_feeding` WRITE;
/*!40000 ALTER TABLE `fish_feeding` DISABLE KEYS */;
/*!40000 ALTER TABLE `fish_feeding` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb3 */ ;
/*!50003 SET character_set_results = utf8mb3 */ ;
/*!50003 SET collation_connection  = utf8mb3_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER IF NOT EXISTS `tr_feeding_stock_update` 
AFTER INSERT ON `fish_feeding`
FOR EACH ROW
BEGIN
    UPDATE `fish_stock` 
    SET last_feeding_time = NOW(),
        updated_at = NOW()
    WHERE fish_id = NEW.fish_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `fish_harvest`
--

DROP TABLE IF EXISTS `fish_harvest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fish_harvest` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `harvest_code` varchar(50) NOT NULL,
  `fish_id` int(11) NOT NULL,
  `harvest_date` date NOT NULL,
  `harvest_method` enum('partial','full','selective') NOT NULL DEFAULT 'partial',
  `quantity` int(11) NOT NULL DEFAULT 0,
  `average_weight` decimal(8,2) NOT NULL DEFAULT 0.00,
  `total_weight` decimal(15,2) NOT NULL DEFAULT 0.00,
  `size_distribution` text DEFAULT NULL,
  `quality_grade` enum('premium','good','fair','poor') DEFAULT NULL,
  `market_price` decimal(15,2) DEFAULT NULL,
  `total_revenue` decimal(15,2) DEFAULT NULL,
  `production_cost` decimal(15,2) DEFAULT NULL,
  `profit_loss` decimal(15,2) DEFAULT NULL,
  `buyer` varchar(255) DEFAULT NULL,
  `sale_date` date DEFAULT NULL,
  `transport_method` varchar(100) DEFAULT NULL,
  `survival_rate` decimal(5,2) DEFAULT 100.00,
  `notes` text DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_harvest_code` (`harvest_code`),
  KEY `idx_fish` (`fish_id`),
  KEY `idx_harvest_date` (`harvest_date`),
  KEY `idx_unit` (`unit_id`),
  CONSTRAINT `fk_harvest_fish` FOREIGN KEY (`fish_id`) REFERENCES `fish` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fish_harvest`
--

LOCK TABLES `fish_harvest` WRITE;
/*!40000 ALTER TABLE `fish_harvest` DISABLE KEYS */;
/*!40000 ALTER TABLE `fish_harvest` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb3 */ ;
/*!50003 SET character_set_results = utf8mb3 */ ;
/*!50003 SET collation_connection  = utf8mb3_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER IF NOT EXISTS `tr_harvest_stock_update` 
AFTER INSERT ON `fish_harvest`
FOR EACH ROW
BEGIN
    UPDATE `fish_stock` 
    SET current_stock = current_stock - NEW.quantity,
        updated_at = NOW()
    WHERE fish_id = NEW.fish_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `fish_health`
--

DROP TABLE IF EXISTS `fish_health`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fish_health` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `health_code` varchar(50) NOT NULL,
  `fish_id` int(11) NOT NULL,
  `check_date` date NOT NULL,
  `check_time` time DEFAULT NULL,
  `health_status` enum('healthy','sick','stressed','dead') NOT NULL DEFAULT 'healthy',
  `symptoms` text DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `treatment` text DEFAULT NULL,
  `medication` varchar(255) DEFAULT NULL,
  `dosage` varchar(100) DEFAULT NULL,
  `mortality_count` int(11) DEFAULT 0,
  `mortality_rate` decimal(5,2) DEFAULT 0.00,
  `water_ph` decimal(4,2) DEFAULT NULL,
  `water_temperature` decimal(5,2) DEFAULT NULL,
  `water_oxygen` decimal(5,2) DEFAULT NULL,
  `water_ammonia` decimal(6,4) DEFAULT NULL,
  `water_nitrite` decimal(6,4) DEFAULT NULL,
  `water_nitrate` decimal(6,4) DEFAULT NULL,
  `checked_by` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_health_code` (`health_code`),
  KEY `idx_fish` (`fish_id`),
  KEY `idx_check_date` (`check_date`),
  KEY `idx_health_status` (`health_status`),
  KEY `idx_unit` (`unit_id`),
  CONSTRAINT `fk_health_fish` FOREIGN KEY (`fish_id`) REFERENCES `fish` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fish_health`
--

LOCK TABLES `fish_health` WRITE;
/*!40000 ALTER TABLE `fish_health` DISABLE KEYS */;
/*!40000 ALTER TABLE `fish_health` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb3 */ ;
/*!50003 SET character_set_results = utf8mb3 */ ;
/*!50003 SET collation_connection  = utf8mb3_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER IF NOT EXISTS `tr_health_stock_update` 
AFTER INSERT ON `fish_health`
FOR EACH ROW
BEGIN
    UPDATE `fish_stock` 
    SET last_health_check = NOW(),
        updated_at = NOW()
    WHERE fish_id = NEW.fish_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `fish_stock`
--

DROP TABLE IF EXISTS `fish_stock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fish_stock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fish_id` int(11) NOT NULL,
  `current_stock` int(11) NOT NULL DEFAULT 0,
  `average_weight` decimal(8,2) NOT NULL DEFAULT 0.00,
  `total_weight` decimal(15,2) NOT NULL DEFAULT 0.00,
  `feeding_frequency` enum('harian','dua_hari','tiga_hari','mingguan') DEFAULT NULL,
  `water_quality_ph` decimal(4,2) DEFAULT NULL,
  `water_quality_temperature` decimal(5,2) DEFAULT NULL,
  `water_quality_oxygen` decimal(5,2) DEFAULT NULL,
  `water_quality_ammonia` decimal(6,4) DEFAULT NULL,
  `water_quality_nitrite` decimal(6,4) DEFAULT NULL,
  `water_quality_nitrate` decimal(6,4) DEFAULT NULL,
  `last_feeding_time` timestamp NULL DEFAULT NULL,
  `last_water_test` timestamp NULL DEFAULT NULL,
  `last_health_check` timestamp NULL DEFAULT NULL,
  `mortality_rate` decimal(5,2) DEFAULT 0.00,
  `survival_rate` decimal(5,2) DEFAULT 100.00,
  `unit_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_fish_stock` (`fish_id`,`unit_id`),
  KEY `idx_current_stock` (`current_stock`),
  KEY `idx_unit` (`unit_id`),
  CONSTRAINT `fk_fish_stock_fish` FOREIGN KEY (`fish_id`) REFERENCES `fish` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fish_stock`
--

LOCK TABLES `fish_stock` WRITE;
/*!40000 ALTER TABLE `fish_stock` DISABLE KEYS */;
INSERT INTO `fish_stock` VALUES (1,1,95,52.50,4987.50,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,100.00,1,1,'2026-02-12 18:00:02','2026-02-12 18:00:02'),(2,2,145,27.50,3987.50,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,100.00,1,1,'2026-02-12 18:00:02','2026-02-12 18:00:02'),(3,3,78,78.00,6084.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,100.00,1,1,'2026-02-12 18:00:02','2026-02-12 18:00:02'),(4,4,48,105.00,5040.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,100.00,1,1,'2026-02-12 18:00:02','2026-02-12 18:00:02'),(5,5,115,32.00,3680.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,100.00,1,1,'2026-02-12 18:00:02','2026-02-12 18:00:02');
/*!40000 ALTER TABLE `fish_stock` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fishing_license`
--

DROP TABLE IF EXISTS `fishing_license`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fishing_license` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `license_code` varchar(50) NOT NULL,
  `license_name` varchar(255) NOT NULL,
  `license_type` enum('commercial','traditional','research','aquaculture','recreational') NOT NULL,
  `issuing_authority` varchar(255) DEFAULT NULL,
  `license_number` varchar(100) DEFAULT NULL,
  `area_coverage` text DEFAULT NULL,
  `species_allowed` text DEFAULT NULL,
  `equipment_allowed` text DEFAULT NULL,
  `quota_limit` decimal(15,2) DEFAULT NULL,
  `quota_unit` varchar(20) DEFAULT NULL,
  `issue_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `status` enum('active','expired','suspended','revoked') NOT NULL DEFAULT 'active',
  `renewal_count` int(11) DEFAULT 0,
  `last_renewal_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_license_code` (`license_code`),
  KEY `idx_license_type` (`license_type`),
  KEY `idx_status` (`status`),
  KEY `idx_expiry_date` (`expiry_date`),
  KEY `idx_unit` (`unit_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fishing_license`
--

LOCK TABLES `fishing_license` WRITE;
/*!40000 ALTER TABLE `fishing_license` DISABLE KEYS */;
INSERT INTO `fishing_license` VALUES (1,'LIC001','Izin Penangkapan Komersial','commercial','DKP','001/DKP/2025',NULL,NULL,NULL,NULL,NULL,'2025-01-01','2025-12-31','active',0,NULL,NULL,1,1,'2026-02-12 18:00:02','2026-02-12 18:00:02'),(2,'LIC002','Izin Budidaya Ikan','aquaculture','DKP','002/DKP/2025',NULL,NULL,NULL,NULL,NULL,'2025-01-01','2025-12-31','active',0,NULL,NULL,1,1,'2026-02-12 18:00:02','2026-02-12 18:00:02'),(3,'LIC003','Izin Penelitian Perikanan','research','DKP','003/DKP/2025',NULL,NULL,NULL,NULL,NULL,'2025-01-01','2025-12-31','active',0,NULL,NULL,1,1,'2026-02-12 18:00:02','2026-02-12 18:00:02');
/*!40000 ALTER TABLE `fishing_license` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fishing_trip`
--

DROP TABLE IF EXISTS `fishing_trip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fishing_trip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trip_code` varchar(50) NOT NULL,
  `trip_name` varchar(255) NOT NULL,
  `license_id` int(11) DEFAULT NULL,
  `vessel_name` varchar(255) DEFAULT NULL,
  `vessel_type` varchar(100) DEFAULT NULL,
  `captain_name` varchar(255) DEFAULT NULL,
  `crew_count` int(11) DEFAULT NULL,
  `departure_date` datetime NOT NULL,
  `return_date` datetime DEFAULT NULL,
  `duration_hours` decimal(6,2) DEFAULT NULL,
  `fishing_area` text DEFAULT NULL,
  `weather_condition` varchar(100) DEFAULT NULL,
  `sea_condition` enum('calm','moderate','rough','very_rough') DEFAULT NULL,
  `status` enum('planned','in_progress','completed','cancelled') NOT NULL DEFAULT 'planned',
  `notes` text DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_trip_code` (`trip_code`),
  KEY `idx_license` (`license_id`),
  KEY `idx_departure_date` (`departure_date`),
  KEY `idx_status` (`status`),
  KEY `idx_unit` (`unit_id`),
  CONSTRAINT `fk_trip_license` FOREIGN KEY (`license_id`) REFERENCES `fishing_license` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fishing_trip`
--

LOCK TABLES `fishing_trip` WRITE;
/*!40000 ALTER TABLE `fishing_trip` DISABLE KEYS */;
/*!40000 ALTER TABLE `fishing_trip` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `form_validation_errors`
--

DROP TABLE IF EXISTS `form_validation_errors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `form_validation_errors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `input_value` text DEFAULT NULL,
  `field_type` varchar(50) DEFAULT NULL,
  `error_type` varchar(50) DEFAULT NULL,
  `user_ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `form_validation_errors`
--

LOCK TABLES `form_validation_errors` WRITE;
/*!40000 ALTER TABLE `form_validation_errors` DISABLE KEYS */;
/*!40000 ALTER TABLE `form_validation_errors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `integration_settings`
--

DROP TABLE IF EXISTS `integration_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `integration_settings` (
  `id` int(11) NOT NULL DEFAULT 1,
  `reminder_due_days` int(11) DEFAULT 3,
  `reminder_channel` varchar(50) DEFAULT 'email',
  `payment_channel` varchar(50) DEFAULT 'transfer',
  `transfer_fee` decimal(8,2) DEFAULT 0.00,
  `cutoff_time` varchar(10) DEFAULT '17:00',
  `rat_reminder_days` int(11) DEFAULT 7,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `integration_settings`
--

LOCK TABLES `integration_settings` WRITE;
/*!40000 ALTER TABLE `integration_settings` DISABLE KEYS */;
INSERT INTO `integration_settings` VALUES (1,3,'email','transfer',0.00,'17:00',7,'2026-02-08 18:27:54','2026-02-08 18:27:54');
/*!40000 ALTER TABLE `integration_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory`
--

DROP TABLE IF EXISTS `inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_code` varchar(50) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `unit` varchar(50) NOT NULL,
  `current_stock` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `min_stock` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `max_stock` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `reorder_point` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `unit_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `sale_price` decimal(15,2) DEFAULT 0.00,
  `cost_price` decimal(15,2) DEFAULT 0.00,
  `location` varchar(255) DEFAULT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `weight` decimal(10,4) DEFAULT NULL,
  `dimensions` varchar(100) DEFAULT NULL,
  `expiry_tracking` tinyint(1) NOT NULL DEFAULT 0,
  `batch_tracking` tinyint(1) NOT NULL DEFAULT 0,
  `serial_tracking` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `unit_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_item_code` (`item_code`,`unit_id`),
  KEY `idx_category` (`category_id`),
  KEY `idx_supplier` (`supplier_id`),
  KEY `idx_unit` (`unit_id`),
  KEY `idx_active` (`is_active`),
  KEY `idx_stock_level` (`current_stock`,`min_stock`),
  CONSTRAINT `fk_inventory_category` FOREIGN KEY (`category_id`) REFERENCES `inventory_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_inventory_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory`
--

LOCK TABLES `inventory` WRITE;
/*!40000 ALTER TABLE `inventory` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_batches`
--

DROP TABLE IF EXISTS `inventory_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_batches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inventory_id` int(11) NOT NULL,
  `batch_number` varchar(100) NOT NULL,
  `quantity` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `remaining_quantity` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `manufacturing_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `unit_cost` decimal(15,2) DEFAULT 0.00,
  `supplier_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `unit_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_inventory_batch` (`inventory_id`,`batch_number`),
  KEY `idx_batch_number` (`batch_number`),
  KEY `idx_expiry_date` (`expiry_date`),
  KEY `idx_unit` (`unit_id`),
  KEY `fk_batch_supplier` (`supplier_id`),
  CONSTRAINT `fk_batch_inventory` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_batch_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_batches`
--

LOCK TABLES `inventory_batches` WRITE;
/*!40000 ALTER TABLE `inventory_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_categories`
--

DROP TABLE IF EXISTS `inventory_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_parent` (`parent_id`),
  KEY `idx_unit` (`unit_id`),
  KEY `idx_active` (`is_active`),
  CONSTRAINT `fk_inventory_category_parent` FOREIGN KEY (`parent_id`) REFERENCES `inventory_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_categories`
--

LOCK TABLES `inventory_categories` WRITE;
/*!40000 ALTER TABLE `inventory_categories` DISABLE KEYS */;
INSERT INTO `inventory_categories` VALUES (1,'Bahan Baku','Bahan baku untuk produksi',NULL,NULL,1,'2026-02-12 17:54:58','2026-02-12 17:54:58'),(2,'Bahan Penolong','Bahan penunjang produksi',NULL,NULL,1,'2026-02-12 17:54:58','2026-02-12 17:54:58'),(3,'Produk Jadi','Produk yang sudah selesai diproduksi',NULL,NULL,1,'2026-02-12 17:54:58','2026-02-12 17:54:58'),(4,'Produk Setengah Jadi','Produk dalam proses',NULL,NULL,1,'2026-02-12 17:54:58','2026-02-12 17:54:58'),(5,'Kemasan','Material kemasan',NULL,NULL,1,'2026-02-12 17:54:58','2026-02-12 17:54:58'),(6,'Alat Tulis Kantor','ATK dan perlengkapan kantor',NULL,NULL,1,'2026-02-12 17:54:58','2026-02-12 17:54:58'),(7,'Peralatan','Peralatan dan mesin',NULL,NULL,1,'2026-02-12 17:54:58','2026-02-12 17:54:58'),(8,'Suku Cadang','Spare part dan komponen',NULL,NULL,1,'2026-02-12 17:54:58','2026-02-12 17:54:58'),(9,'Lain-lain','Kategori lainnya',NULL,NULL,1,'2026-02-12 17:54:58','2026-02-12 17:54:58');
/*!40000 ALTER TABLE `inventory_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_count_items`
--

DROP TABLE IF EXISTS `inventory_count_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_count_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `count_id` int(11) NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `system_quantity` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `counted_quantity` decimal(15,4) DEFAULT NULL,
  `variance` decimal(15,4) DEFAULT 0.0000,
  `variance_value` decimal(15,2) DEFAULT 0.00,
  `unit_cost` decimal(15,2) DEFAULT 0.00,
  `counted_by` int(11) DEFAULT NULL,
  `counted_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_count_inventory` (`count_id`,`inventory_id`),
  KEY `idx_inventory` (`inventory_id`),
  CONSTRAINT `fk_count_item_count` FOREIGN KEY (`count_id`) REFERENCES `inventory_counts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_count_item_inventory` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_count_items`
--

LOCK TABLES `inventory_count_items` WRITE;
/*!40000 ALTER TABLE `inventory_count_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_count_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_counts`
--

DROP TABLE IF EXISTS `inventory_counts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_counts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `count_code` varchar(50) NOT NULL,
  `count_type` enum('full','partial','cycle') NOT NULL DEFAULT 'full',
  `status` enum('draft','in_progress','completed','verified') NOT NULL DEFAULT 'draft',
  `count_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `variance_amount` decimal(15,2) DEFAULT 0.00,
  `variance_percentage` decimal(5,2) DEFAULT 0.00,
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_count_code` (`count_code`),
  KEY `idx_status` (`status`),
  KEY `idx_count_date` (`count_date`),
  KEY `idx_unit` (`unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_counts`
--

LOCK TABLES `inventory_counts` WRITE;
/*!40000 ALTER TABLE `inventory_counts` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_counts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_serial_numbers`
--

DROP TABLE IF EXISTS `inventory_serial_numbers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_serial_numbers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inventory_id` int(11) NOT NULL,
  `serial_number` varchar(100) NOT NULL,
  `status` enum('available','sold','reserved','damaged','lost') NOT NULL DEFAULT 'available',
  `batch_number` varchar(100) DEFAULT NULL,
  `manufacturing_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `cost` decimal(15,2) DEFAULT 0.00,
  `sale_price` decimal(15,2) DEFAULT 0.00,
  `location` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_inventory_serial` (`inventory_id`,`serial_number`),
  KEY `idx_serial_number` (`serial_number`),
  KEY `idx_status` (`status`),
  KEY `idx_unit` (`unit_id`),
  CONSTRAINT `fk_serial_inventory` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_serial_numbers`
--

LOCK TABLES `inventory_serial_numbers` WRITE;
/*!40000 ALTER TABLE `inventory_serial_numbers` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_serial_numbers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_stock_movements`
--

DROP TABLE IF EXISTS `inventory_stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_stock_movements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inventory_id` int(11) NOT NULL,
  `movement_type` enum('in','out','adjustment_in','adjustment_out','transfer_in','transfer_out','return_in','return_out') NOT NULL,
  `quantity` decimal(15,4) NOT NULL,
  `unit_cost` decimal(15,2) DEFAULT 0.00,
  `total_cost` decimal(15,2) DEFAULT 0.00,
  `reference` varchar(100) DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `batch_number` varchar(100) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `from_location` varchar(255) DEFAULT NULL,
  `to_location` varchar(255) DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_inventory` (`inventory_id`),
  KEY `idx_movement_type` (`movement_type`),
  KEY `idx_reference` (`reference`),
  KEY `idx_date` (`created_at`),
  KEY `idx_unit` (`unit_id`),
  CONSTRAINT `fk_stock_movement_inventory` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_stock_movements`
--

LOCK TABLES `inventory_stock_movements` WRITE;
/*!40000 ALTER TABLE `inventory_stock_movements` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_stock_movements` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb3 */ ;
/*!50003 SET character_set_results = utf8mb3 */ ;
/*!50003 SET collation_connection  = utf8mb3_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER IF NOT EXISTS `tr_stock_movement_insert` 
AFTER INSERT ON `inventory_stock_movements`
FOR EACH ROW
BEGIN
    DECLARE stock_change DECIMAL(15,4);
    
    SET stock_change = CASE NEW.movement_type
        WHEN 'in' THEN NEW.quantity
        WHEN 'out' THEN -NEW.quantity
        WHEN 'adjustment_in' THEN NEW.quantity
        WHEN 'adjustment_out' THEN -NEW.quantity
        WHEN 'transfer_in' THEN NEW.quantity
        WHEN 'transfer_out' THEN -NEW.quantity
        WHEN 'return_in' THEN NEW.quantity
        WHEN 'return_out' THEN -NEW.quantity
        ELSE 0
    END;
    
    UPDATE `inventory` 
    SET `current_stock` = `current_stock` + stock_change,
        `updated_at` = NOW()
    WHERE `id` = NEW.inventory_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb3 */ ;
/*!50003 SET character_set_results = utf8mb3 */ ;
/*!50003 SET collation_connection  = utf8mb3_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER IF NOT EXISTS `tr_batch_movement_insert` 
AFTER INSERT ON `inventory_stock_movements`
FOR EACH ROW
BEGIN
    IF NEW.batch_number IS NOT NULL THEN
        UPDATE `inventory_batches` 
        SET `remaining_quantity` = CASE NEW.movement_type
            WHEN 'in' THEN `remaining_quantity` + NEW.quantity
            WHEN 'out' THEN `remaining_quantity` - NEW.quantity
            WHEN 'adjustment_in' THEN `remaining_quantity` + NEW.quantity
            WHEN 'adjustment_out' THEN `remaining_quantity` - NEW.quantity
            ELSE `remaining_quantity`
        END,
        `updated_at` = NOW()
        WHERE `inventory_id` = NEW.inventory_id 
        AND `batch_number` = NEW.batch_number;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb3 */ ;
/*!50003 SET character_set_results = utf8mb3 */ ;
/*!50003 SET collation_connection  = utf8mb3_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER IF NOT EXISTS `tr_stock_movement_delete` 
AFTER DELETE ON `inventory_stock_movements`
FOR EACH ROW
BEGIN
    DECLARE stock_change DECIMAL(15,4);
    
    SET stock_change = CASE OLD.movement_type
        WHEN 'in' THEN -OLD.quantity
        WHEN 'out' THEN OLD.quantity
        WHEN 'adjustment_in' THEN -OLD.quantity
        WHEN 'adjustment_out' THEN OLD.quantity
        WHEN 'transfer_in' THEN -OLD.quantity
        WHEN 'transfer_out' THEN OLD.quantity
        WHEN 'return_in' THEN -OLD.quantity
        WHEN 'return_out' THEN OLD.quantity
        ELSE 0
    END;
    
    UPDATE `inventory` 
    SET `current_stock` = `current_stock` + stock_change,
        `updated_at` = NOW()
    WHERE `id` = OLD.inventory_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `inventory_transfer_items`
--

DROP TABLE IF EXISTS `inventory_transfer_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_transfer_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transfer_id` int(11) NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `quantity` decimal(15,4) NOT NULL,
  `unit_cost` decimal(15,2) DEFAULT 0.00,
  `total_cost` decimal(15,2) DEFAULT 0.00,
  `batch_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_transfer` (`transfer_id`),
  KEY `idx_inventory` (`inventory_id`),
  CONSTRAINT `fk_transfer_item_inventory` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`id`),
  CONSTRAINT `fk_transfer_item_transfer` FOREIGN KEY (`transfer_id`) REFERENCES `inventory_transfers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_transfer_items`
--

LOCK TABLES `inventory_transfer_items` WRITE;
/*!40000 ALTER TABLE `inventory_transfer_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_transfer_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_transfers`
--

DROP TABLE IF EXISTS `inventory_transfers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_transfers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transfer_code` varchar(50) NOT NULL,
  `from_unit_id` int(11) NOT NULL,
  `to_unit_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected','completed') NOT NULL DEFAULT 'pending',
  `transfer_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `completed_by` int(11) DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_transfer_code` (`transfer_code`),
  KEY `idx_from_unit` (`from_unit_id`),
  KEY `idx_to_unit` (`to_unit_id`),
  KEY `idx_status` (`status`),
  KEY `idx_transfer_date` (`transfer_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_transfers`
--

LOCK TABLES `inventory_transfers` WRITE;
/*!40000 ALTER TABLE `inventory_transfers` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_transfers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `irigasi`
--

DROP TABLE IF EXISTS `irigasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `irigasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `irrigation_code` varchar(50) NOT NULL,
  `irrigation_name` varchar(255) NOT NULL,
  `irrigation_type` enum('irigasi_permukaan','irigasi_sprinkle','irigasi_drip','irigasi_sub_irigasi','irigasi_curah','manual') NOT NULL,
  `water_source` enum('sungai','danau','sumur','embung','hujan','air_tanah') NOT NULL,
  `coverage_area` decimal(10,4) DEFAULT NULL,
  `coverage_unit` enum('m2','ha','are') DEFAULT 'm2',
  `flow_rate` decimal(10,4) DEFAULT NULL,
  `flow_unit` varchar(20) DEFAULT 'liter/menit',
  `pump_power` decimal(8,2) DEFAULT NULL,
  `pump_unit` varchar(20) DEFAULT 'HP',
  `pipe_diameter` decimal(6,2) DEFAULT NULL,
  `pipe_unit` varchar(10) DEFAULT 'inch',
  `efficiency` decimal(5,2) DEFAULT NULL,
  `installation_date` date DEFAULT NULL,
  `last_maintenance` date DEFAULT NULL,
  `next_maintenance` date DEFAULT NULL,
  `status` enum('active','inactive','maintenance','broken') NOT NULL DEFAULT 'active',
  `coordinates` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_irrigation_code` (`irrigation_code`),
  KEY `idx_irrigation_type` (`irrigation_type`),
  KEY `idx_water_source` (`water_source`),
  KEY `idx_status` (`status`),
  KEY `idx_unit` (`unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `irigasi`
--

LOCK TABLES `irigasi` WRITE;
/*!40000 ALTER TABLE `irigasi` DISABLE KEYS */;
/*!40000 ALTER TABLE `irigasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `irigasi_maintenance`
--

DROP TABLE IF EXISTS `irigasi_maintenance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `irigasi_maintenance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `irrigation_id` int(11) NOT NULL,
  `maintenance_type` enum('preventive','corrective','emergency','routine') NOT NULL,
  `maintenance_date` date NOT NULL,
  `description` text NOT NULL,
  `cost` decimal(15,2) DEFAULT NULL,
  `parts_replaced` text DEFAULT NULL,
  `technician_name` varchar(255) DEFAULT NULL,
  `next_maintenance_date` date DEFAULT NULL,
  `status` enum('planned','in_progress','completed','cancelled') NOT NULL DEFAULT 'planned',
  `completion_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_irrigation` (`irrigation_id`),
  KEY `idx_maintenance_date` (`maintenance_date`),
  KEY `idx_maintenance_type` (`maintenance_type`),
  KEY `idx_status` (`status`),
  KEY `idx_unit` (`unit_id`),
  CONSTRAINT `fk_maintenance_irrigation` FOREIGN KEY (`irrigation_id`) REFERENCES `irigasi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `irigasi_maintenance`
--

LOCK TABLES `irigasi_maintenance` WRITE;
/*!40000 ALTER TABLE `irigasi_maintenance` DISABLE KEYS */;
/*!40000 ALTER TABLE `irigasi_maintenance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `irigasi_schedule`
--

DROP TABLE IF EXISTS `irigasi_schedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `irigasi_schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `irrigation_id` int(11) NOT NULL,
  `land_id` int(11) DEFAULT NULL,
  `schedule_date` date NOT NULL,
  `schedule_time` time DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `water_amount` decimal(10,4) DEFAULT NULL,
  `water_unit` varchar(20) DEFAULT 'liter',
  `frequency` enum('harian','mingguan','dua_mingguan','bulanan','sesuai_kebutuhan') DEFAULT NULL,
  `status` enum('scheduled','completed','skipped','cancelled') NOT NULL DEFAULT 'scheduled',
  `executed_at` timestamp NULL DEFAULT NULL,
  `executed_by` int(11) DEFAULT NULL,
  `actual_duration` int(11) DEFAULT NULL,
  `actual_water_amount` decimal(10,4) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_irrigation` (`irrigation_id`),
  KEY `idx_land` (`land_id`),
  KEY `idx_schedule_date` (`schedule_date`),
  KEY `idx_status` (`status`),
  KEY `idx_unit` (`unit_id`),
  CONSTRAINT `fk_schedule_irrigation` FOREIGN KEY (`irrigation_id`) REFERENCES `irigasi` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_schedule_land` FOREIGN KEY (`land_id`) REFERENCES `lahan` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `irigasi_schedule`
--

LOCK TABLES `irigasi_schedule` WRITE;
/*!40000 ALTER TABLE `irigasi_schedule` DISABLE KEYS */;
/*!40000 ALTER TABLE `irigasi_schedule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `izin_modul`
--

DROP TABLE IF EXISTS `izin_modul`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `izin_modul` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `izin_modul`
--

LOCK TABLES `izin_modul` WRITE;
/*!40000 ALTER TABLE `izin_modul` DISABLE KEYS */;
INSERT INTO `izin_modul` VALUES (1,'view_users','View user list','2026-02-03 14:13:20',1),(2,'create_users','Create new users','2026-02-03 14:13:20',1),(3,'edit_users','Edit user information','2026-02-03 14:13:20',1),(4,'delete_users','Delete users','2026-02-03 14:13:20',1),(5,'view_members','View members','2026-02-03 14:13:20',1),(6,'manage_members','Manage member data','2026-02-03 14:13:20',1),(7,'view_savings','View savings transactions','2026-02-03 14:13:20',1),(8,'manage_savings','Manage savings','2026-02-03 14:13:20',1),(9,'view_loans','View loan applications','2026-02-03 14:13:20',1),(10,'manage_loans','Manage loans','2026-02-03 14:13:20',1),(11,'view_accounts','View chart of accounts','2026-02-03 14:13:20',1),(12,'manage_accounts','Manage accounting','2026-02-03 14:13:20',1),(13,'view_reports','View reports','2026-02-03 14:13:20',1),(14,'generate_reports','Generate financial reports','2026-02-03 14:13:20',1),(15,'vote','Participate in voting','2026-02-03 14:13:20',1),(16,'manage_votes','Manage voting sessions','2026-02-03 14:13:20',1),(17,'view_audit','View audit logs','2026-02-03 14:13:20',1),(18,'admin_access','Full administrative access','2026-02-03 14:13:20',1);
/*!40000 ALTER TABLE `izin_modul` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jurnal`
--

DROP TABLE IF EXISTS `jurnal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jurnal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entry_date` date NOT NULL,
  `description` text NOT NULL,
  `reference_number` varchar(50) DEFAULT NULL,
  `status` enum('draft','posted') DEFAULT 'draft',
  `cooperative_id` int(11) DEFAULT NULL,
  `posted_by` int(11) DEFAULT NULL,
  `posted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_journal_posted_by` (`posted_by`),
  KEY `idx_jurnal_cooperative` (`cooperative_id`),
  CONSTRAINT `fk_jurnal_cooperative` FOREIGN KEY (`cooperative_id`) REFERENCES `koperasi_tenant` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jurnal_fk_posted_by` FOREIGN KEY (`posted_by`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jurnal`
--

LOCK TABLES `jurnal` WRITE;
/*!40000 ALTER TABLE `jurnal` DISABLE KEYS */;
/*!40000 ALTER TABLE `jurnal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jurnal_detail`
--

DROP TABLE IF EXISTS `jurnal_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jurnal_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `journal_entry_id` int(11) NOT NULL,
  `cooperative_id` int(11) DEFAULT NULL,
  `account_id` int(11) NOT NULL,
  `debit` decimal(15,2) DEFAULT 0.00,
  `credit` decimal(15,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `journal_entry_id` (`journal_entry_id`),
  KEY `account_id` (`account_id`),
  KEY `idx_jurnal_detail_cooperative` (`cooperative_id`),
  CONSTRAINT `fk_jurnal_detail_cooperative` FOREIGN KEY (`cooperative_id`) REFERENCES `koperasi_tenant` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jurnal_detail_ibfk_1` FOREIGN KEY (`journal_entry_id`) REFERENCES `jurnal` (`id`),
  CONSTRAINT `jurnal_detail_ibfk_2` FOREIGN KEY (`account_id`) REFERENCES `akuntansi_jenis` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jurnal_detail`
--

LOCK TABLES `jurnal_detail` WRITE;
/*!40000 ALTER TABLE `jurnal_detail` DISABLE KEYS */;
/*!40000 ALTER TABLE `jurnal_detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `konfigurasi`
--

DROP TABLE IF EXISTS `konfigurasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `konfigurasi` (
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
-- Dumping data for table `konfigurasi`
--

LOCK TABLES `konfigurasi` WRITE;
/*!40000 ALTER TABLE `konfigurasi` DISABLE KEYS */;
INSERT INTO `konfigurasi` VALUES (1,'coop_name','Koperasi Simpan Pinjam','Nama koperasi','2026-02-03 14:13:20'),(2,'interest_rate_savings','3.5','Suku bunga simpanan tahunan (%)','2026-02-03 14:13:20'),(3,'interest_rate_loans','12.0','Suku bunga pinjaman tahunan (%)','2026-02-03 14:13:20'),(4,'penalty_rate','2.0','Denda keterlambatan (%) per hari','2026-02-03 14:13:20'),(5,'shu_distribution_ratio','70','Persentase SHU untuk anggota (%)','2026-02-03 14:13:20');
/*!40000 ALTER TABLE `konfigurasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `koperasi_cross_unit_projects`
--

DROP TABLE IF EXISTS `koperasi_cross_unit_projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `koperasi_cross_unit_projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_name` varchar(255) NOT NULL,
  `project_description` text DEFAULT NULL,
  `project_type` enum('joint_venture','coordination','resource_sharing','expansion') NOT NULL,
  `lead_unit_id` int(11) NOT NULL,
  `participating_units` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`participating_units`)),
  `project_status` enum('planning','active','on_hold','completed','cancelled') DEFAULT 'planning',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `budget_allocated` decimal(15,2) DEFAULT 0.00,
  `budget_used` decimal(15,2) DEFAULT 0.00,
  `project_manager` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `lead_unit_id` (`lead_unit_id`),
  KEY `project_manager` (`project_manager`),
  CONSTRAINT `koperasi_cross_unit_projects_ibfk_1` FOREIGN KEY (`lead_unit_id`) REFERENCES `koperasi_unit` (`id`) ON DELETE CASCADE,
  CONSTRAINT `koperasi_cross_unit_projects_ibfk_2` FOREIGN KEY (`project_manager`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `koperasi_cross_unit_projects`
--

LOCK TABLES `koperasi_cross_unit_projects` WRITE;
/*!40000 ALTER TABLE `koperasi_cross_unit_projects` DISABLE KEYS */;
/*!40000 ALTER TABLE `koperasi_cross_unit_projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `koperasi_dokumen_riwayat`
--

DROP TABLE IF EXISTS `koperasi_dokumen_riwayat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `koperasi_dokumen_riwayat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `koperasi_id` int(11) NOT NULL,
  `document_type` enum('nomor_bh','nib','nik_koperasi','modal_pokok') NOT NULL,
  `document_number_lama` varchar(50) DEFAULT NULL,
  `document_number_baru` varchar(50) DEFAULT NULL,
  `document_value_lama` decimal(15,2) DEFAULT NULL,
  `document_value_baru` decimal(15,2) DEFAULT NULL,
  `tanggal_efektif` date NOT NULL,
  `change_reason` varchar(255) DEFAULT NULL,
  `pengguna_id` int(11) DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_cooperative_document` (`koperasi_id`,`document_type`),
  KEY `idx_tanggal_efektif` (`tanggal_efektif`),
  KEY `idx_document_type` (`document_type`),
  KEY `koperasi_dok_fk_pengguna` (`pengguna_id`),
  CONSTRAINT `koperasi_dok_fk_koperasi` FOREIGN KEY (`koperasi_id`) REFERENCES `koperasi_tenant` (`id`) ON DELETE CASCADE,
  CONSTRAINT `koperasi_dok_fk_pengguna` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL,
  CONSTRAINT `koperasi_dokumen_riwayat_ibfk_1` FOREIGN KEY (`koperasi_id`) REFERENCES `koperasi_tenant` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `koperasi_dokumen_riwayat`
--

LOCK TABLES `koperasi_dokumen_riwayat` WRITE;
/*!40000 ALTER TABLE `koperasi_dokumen_riwayat` DISABLE KEYS */;
/*!40000 ALTER TABLE `koperasi_dokumen_riwayat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `koperasi_enterprise_audit`
--

DROP TABLE IF EXISTS `koperasi_enterprise_audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `koperasi_enterprise_audit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cooperative_id` int(11) NOT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `action_type` enum('unit_creation','resource_sharing','communication','project_creation','permission_change','metric_reporting') NOT NULL,
  `action_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`action_details`)),
  `performed_by` int(11) NOT NULL,
  `affected_entities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`affected_entities`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `unit_id` (`unit_id`),
  KEY `performed_by` (`performed_by`),
  KEY `idx_action_type` (`action_type`,`created_at`),
  KEY `idx_cooperative` (`cooperative_id`,`created_at`),
  CONSTRAINT `koperasi_enterprise_audit_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `koperasi_tenant` (`id`) ON DELETE CASCADE,
  CONSTRAINT `koperasi_enterprise_audit_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `koperasi_unit` (`id`) ON DELETE CASCADE,
  CONSTRAINT `koperasi_enterprise_audit_ibfk_3` FOREIGN KEY (`performed_by`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `koperasi_enterprise_audit`
--

LOCK TABLES `koperasi_enterprise_audit` WRITE;
/*!40000 ALTER TABLE `koperasi_enterprise_audit` DISABLE KEYS */;
/*!40000 ALTER TABLE `koperasi_enterprise_audit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `koperasi_jenis`
--

DROP TABLE IF EXISTS `koperasi_jenis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `koperasi_jenis` (
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
-- Dumping data for table `koperasi_jenis`
--

LOCK TABLES `koperasi_jenis` WRITE;
/*!40000 ALTER TABLE `koperasi_jenis` DISABLE KEYS */;
INSERT INTO `koperasi_jenis` VALUES (1,'Koperasi Simpan Pinjam (KSP)','Koperasi yang bergerak di bidang simpan pinjam untuk anggota, menyediakan layanan tabungan, kredit, dan jasa keuangan lainnya sesuai PP No. 7 Tahun 2021','KSP','finansial',1,'2026-02-04 07:36:54'),(2,'Koperasi Konsumsi','Koperasi yang bergerak di bidang pemenuhan kebutuhan konsumsi anggota, menyediakan barang dan jasa kebutuhan sehari-hari sesuai PP No. 7 Tahun 2021','KK','konsumsi',1,'2026-02-04 07:36:54'),(3,'Koperasi Produksi','Koperasi yang bergerak di bidang produksi barang/jasa anggota, mengelola pengolahan, pemasaran, dan distribusi hasil produksi sesuai PP No. 7 Tahun 2021','KP','produksi',1,'2026-02-04 07:36:54'),(4,'Koperasi Pemasaran','Koperasi yang bergerak di bidang pemasaran hasil produksi anggota, menyediakan layanan distribusi, penjualan, dan ekspor sesuai PP No. 7 Tahun 2021','KPAS','produksi',1,'2026-02-04 07:36:54'),(5,'Koperasi Jasa','Koperasi yang bergerak di bidang penyediaan jasa untuk anggota, seperti transportasi, komunikasi, konsultasi, dan jasa lainnya sesuai PP No. 7 Tahun 2021','KJ','jasa',1,'2026-02-04 07:36:54'),(6,'Koperasi Serba Usaha (KSU)','Koperasi yang menjalankan berbagai jenis usaha kombinasi dari beberapa jenis koperasi dalam satu organisasi sesuai PP No. 7 Tahun 2021','KSU','serba_usaha',1,'2026-02-04 07:36:54'),(7,'Koperasi Karyawan','Koperasi yang bergerak di bidang kesejahteraan karyawan perusahaan, menyediakan simpan pinjam, konsumsi, dan jasa untuk karyawan sesuai PP No. 7 Tahun 2021','KKAR','karyawan',1,'2026-02-04 09:19:02'),(8,'Koperasi Pertanian','Koperasi yang bergerak di bidang pertanian, menyediakan sarana produksi, pengolahan hasil, dan pemasaran produk pertanian sesuai PP No. 7 Tahun 2021','KOPERTA','produksi',1,'2026-02-04 09:20:25'),(9,'Koperasi Nelayan','Koperasi yang bergerak di bidang perikanan, menyediakan alat tangkap, pengolahan hasil, dan pemasaran hasil perikanan sesuai PP No. 7 Tahun 2021','KOPERNAL','produksi',1,'2026-02-04 09:20:25'),(10,'Koperasi Peternakan','Koperasi yang bergerak di bidang peternakan, menyediakan pakan, pengolahan, dan pemasaran hasil peternakan sesuai PP No. 7 Tahun 2021','KOPERTAK','produksi',1,'2026-02-04 09:20:25'),(11,'Koperasi Perdagangan','Koperasi yang bergerak di bidang perdagangan grosir dan eceran, menyediakan barang dagangan untuk anggota sesuai PP No. 7 Tahun 2021','KOPERDAG','konsumsi',1,'2026-02-04 09:20:25'),(12,'Koperasi Pondok Pesantren','Koperasi yang bergerak di lingkungan pondok pesantren, menyediakan kebutuhan santri dan wali santri sesuai PP No. 7 Tahun 2021','KOPONTREN','serba_usaha',1,'2026-02-04 09:20:25');
/*!40000 ALTER TABLE `koperasi_jenis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `koperasi_jenis_modul_config`
--

DROP TABLE IF EXISTS `koperasi_jenis_modul_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `koperasi_jenis_modul_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cooperative_type_code` varchar(20) NOT NULL,
  `modul_id` int(11) NOT NULL,
  `config_key` varchar(100) NOT NULL,
  `config_value` text DEFAULT NULL,
  `config_type` enum('string','integer','boolean','json','float') DEFAULT 'string',
  `is_required` tinyint(1) DEFAULT 0,
  `validation_rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`validation_rules`)),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_type_modul_config` (`cooperative_type_code`,`modul_id`,`config_key`),
  KEY `modul_id` (`modul_id`),
  CONSTRAINT `koperasi_jenis_modul_config_ibfk_1` FOREIGN KEY (`modul_id`) REFERENCES `modul` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `koperasi_jenis_modul_config`
--

LOCK TABLES `koperasi_jenis_modul_config` WRITE;
/*!40000 ALTER TABLE `koperasi_jenis_modul_config` DISABLE KEYS */;
INSERT INTO `koperasi_jenis_modul_config` VALUES (1,'KSP',9,'max_credit_score','850','integer',1,NULL),(2,'KSP',9,'min_credit_score','300','integer',1,NULL),(3,'KOPERTA',11,'supported_crops','[\"padi\",\"jagung\",\"kedelai\",\"tebu\"]','json',0,NULL),(4,'KOPERNAL',15,'supported_fish_types','[\"tuna\",\"cakalang\",\"kembung\",\"teri\"]','json',0,NULL),(5,'KOPERTAK',17,'supported_livestock','[\"sapi\",\"kambing\",\"ayam\",\"bebek\"]','json',0,NULL);
/*!40000 ALTER TABLE `koperasi_jenis_modul_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `koperasi_jenis_modul_requirement`
--

DROP TABLE IF EXISTS `koperasi_jenis_modul_requirement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `koperasi_jenis_modul_requirement` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cooperative_type_code` varchar(20) NOT NULL,
  `modul_id` int(11) NOT NULL,
  `requirement_level` enum('required','recommended','optional') DEFAULT 'optional',
  `priority` int(11) DEFAULT 5,
  `auto_enable` tinyint(1) DEFAULT 0,
  `custom_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_config`)),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_type_module` (`cooperative_type_code`,`modul_id`),
  KEY `modul_id` (`modul_id`),
  KEY `idx_type_code` (`cooperative_type_code`),
  KEY `idx_priority` (`priority`),
  CONSTRAINT `koperasi_jenis_modul_requirement_ibfk_1` FOREIGN KEY (`modul_id`) REFERENCES `modul` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `koperasi_jenis_modul_requirement`
--

LOCK TABLES `koperasi_jenis_modul_requirement` WRITE;
/*!40000 ALTER TABLE `koperasi_jenis_modul_requirement` DISABLE KEYS */;
INSERT INTO `koperasi_jenis_modul_requirement` VALUES (1,'KSP',3,'required',1,1,NULL),(2,'KSP',4,'required',2,1,NULL),(3,'KSP',9,'required',3,1,NULL),(4,'KSP',10,'recommended',4,1,NULL),(5,'KSP',5,'required',5,1,NULL),(6,'KOPERTA',11,'required',1,1,NULL),(7,'KOPERTA',12,'required',2,1,NULL),(8,'KOPERTA',13,'recommended',3,1,NULL),(9,'KOPERTA',22,'recommended',4,0,NULL),(10,'KOPERNAL',14,'required',1,1,NULL),(11,'KOPERNAL',15,'required',2,1,NULL),(12,'KOPERNAL',16,'recommended',3,1,NULL),(13,'KOPERTAK',17,'required',1,1,NULL),(14,'KOPERTAK',18,'required',2,1,NULL),(15,'KOPERTAK',19,'recommended',3,1,NULL),(16,'KP',20,'required',1,1,NULL),(17,'KP',21,'required',2,1,NULL),(18,'KP',22,'required',3,1,NULL),(19,'KJ',23,'required',1,1,NULL),(20,'KJ',24,'recommended',2,1,NULL),(21,'KJ',25,'recommended',3,0,NULL),(22,'KK',22,'required',1,1,NULL),(23,'KK',27,'recommended',2,1,NULL);
/*!40000 ALTER TABLE `koperasi_jenis_modul_requirement` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `koperasi_keuangan_pengaturan`
--

DROP TABLE IF EXISTS `koperasi_keuangan_pengaturan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `koperasi_keuangan_pengaturan` (
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
  CONSTRAINT `koperasi_keuangan_pengaturan_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `koperasi_tenant` (`id`) ON DELETE CASCADE,
  CONSTRAINT `koperasi_keuangan_pengaturan_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `koperasi_keuangan_pengaturan`
--

LOCK TABLES `koperasi_keuangan_pengaturan` WRITE;
/*!40000 ALTER TABLE `koperasi_keuangan_pengaturan` DISABLE KEYS */;
/*!40000 ALTER TABLE `koperasi_keuangan_pengaturan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `koperasi_pengurus`
--

DROP TABLE IF EXISTS `koperasi_pengurus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `koperasi_pengurus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `koperasi_tenant_id` int(11) NOT NULL,
  `jabatan` enum('ketua','wakil_ketua','sekretaris','wakil_sekretaris','bendahara','ketua_pengawas','anggota_pengawas') NOT NULL,
  `orang_id` int(11) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_akhir` date DEFAULT NULL,
  `surat_keputusan` varchar(255) DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `diperbarui_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `koperasi_tenant_id` (`koperasi_tenant_id`),
  KEY `orang_id` (`orang_id`),
  CONSTRAINT `koperasi_pengurus_ibfk_1` FOREIGN KEY (`koperasi_tenant_id`) REFERENCES `koperasi_tenant` (`id`),
  CONSTRAINT `koperasi_pengurus_ibfk_2` FOREIGN KEY (`orang_id`) REFERENCES `orang` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `koperasi_pengurus`
--

LOCK TABLES `koperasi_pengurus` WRITE;
/*!40000 ALTER TABLE `koperasi_pengurus` DISABLE KEYS */;
/*!40000 ALTER TABLE `koperasi_pengurus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `koperasi_resource_sharing`
--

DROP TABLE IF EXISTS `koperasi_resource_sharing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `koperasi_resource_sharing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `resource_type` enum('members','facilities','equipment','personnel','funds','data') NOT NULL,
  `provider_unit_id` int(11) NOT NULL,
  `consumer_unit_id` int(11) NOT NULL,
  `sharing_terms` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`sharing_terms`)),
  `sharing_status` enum('active','inactive','pending','terminated') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `provider_unit_id` (`provider_unit_id`),
  KEY `consumer_unit_id` (`consumer_unit_id`),
  CONSTRAINT `koperasi_resource_sharing_ibfk_1` FOREIGN KEY (`provider_unit_id`) REFERENCES `koperasi_unit` (`id`) ON DELETE CASCADE,
  CONSTRAINT `koperasi_resource_sharing_ibfk_2` FOREIGN KEY (`consumer_unit_id`) REFERENCES `koperasi_unit` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `koperasi_resource_sharing`
--

LOCK TABLES `koperasi_resource_sharing` WRITE;
/*!40000 ALTER TABLE `koperasi_resource_sharing` DISABLE KEYS */;
/*!40000 ALTER TABLE `koperasi_resource_sharing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `koperasi_status_riwayat`
--

DROP TABLE IF EXISTS `koperasi_status_riwayat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `koperasi_status_riwayat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `koperasi_id` int(11) NOT NULL,
  `status_sebelumnya` varchar(50) DEFAULT NULL,
  `status_baru` varchar(50) NOT NULL,
  `tanggal_efektif` date DEFAULT NULL,
  `dokumen_path` varchar(255) DEFAULT NULL,
  `pengguna_id` int(11) DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `change_reason` varchar(255) DEFAULT NULL,
  `approval_status` enum('pending','approved','rejected') DEFAULT 'approved',
  `pengguna_disetujui_id` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cooperative_id` (`koperasi_id`),
  KEY `idx_tanggal_efektif` (`tanggal_efektif`),
  KEY `idx_approval_status` (`approval_status`),
  KEY `koperasi_status_fk_pengguna` (`pengguna_id`),
  KEY `koperasi_status_fk_pengguna_approve` (`pengguna_disetujui_id`),
  CONSTRAINT `koperasi_status_fk_koperasi` FOREIGN KEY (`koperasi_id`) REFERENCES `koperasi_tenant` (`id`) ON DELETE CASCADE,
  CONSTRAINT `koperasi_status_fk_pengguna` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL,
  CONSTRAINT `koperasi_status_fk_pengguna_approve` FOREIGN KEY (`pengguna_disetujui_id`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `koperasi_status_riwayat`
--

LOCK TABLES `koperasi_status_riwayat` WRITE;
/*!40000 ALTER TABLE `koperasi_status_riwayat` DISABLE KEYS */;
/*!40000 ALTER TABLE `koperasi_status_riwayat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `koperasi_tenant`
--

DROP TABLE IF EXISTS `koperasi_tenant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `koperasi_tenant` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_koperasi` varchar(255) NOT NULL,
  `jenis_koperasi` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`jenis_koperasi`)),
  `badan_hukum` varchar(255) DEFAULT NULL,
  `status_badan_hukum` enum('belum_terdaftar','terdaftar','badan_hukum') DEFAULT 'belum_terdaftar',
  `approval_status` enum('pending_review','awaiting_documents','approved','rejected','active','suspended') DEFAULT 'pending_review',
  `approval_stage` varchar(50) DEFAULT 'auto_checks',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_by` int(11) DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `approval_notes` text DEFAULT NULL,
  `auto_checks_passed` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`auto_checks_passed`)),
  `tanggal_status_terakhir` date DEFAULT NULL,
  `catatan_status` text DEFAULT NULL,
  `tanggal_pendirian` date DEFAULT NULL,
  `npwp` varchar(50) DEFAULT NULL,
  `nomor_badan_hukum` varchar(50) DEFAULT NULL,
  `nib` varchar(20) DEFAULT NULL,
  `nik_koperasi` varchar(20) DEFAULT NULL,
  `modal_pokok` decimal(15,2) DEFAULT 0.00,
  `alamat_legal` text DEFAULT NULL,
  `kontak_resmi` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `dibuat_oleh` int(11) DEFAULT NULL,
  `dibuat_pada` timestamp NULL DEFAULT current_timestamp(),
  `diperbarui_pada` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `provinsi_id` int(11) DEFAULT NULL,
  `kabkota_id` int(11) DEFAULT NULL,
  `kecamatan_id` int(11) DEFAULT NULL,
  `kelurahan_id` int(11) DEFAULT NULL,
  `allowed_occupations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`allowed_occupations`)),
  `savings_settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`savings_settings`)),
  `loans_settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`loans_settings`)),
  `reports_settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`reports_settings`)),
  `akta_pendirian` varchar(255) DEFAULT NULL,
  `ad_art` varchar(255) DEFAULT NULL,
  `berita_acara_rapat` varchar(255) DEFAULT NULL,
  `rencana_kegiatan` varchar(255) DEFAULT NULL,
  `dewan_pengawas_count` int(11) DEFAULT 0,
  `dewan_pengurus_count` int(11) DEFAULT 0,
  `anggota_count` int(11) DEFAULT 0,
  `simpanan_pokok_total` decimal(15,2) DEFAULT 0.00,
  `rat_terakhir` date DEFAULT NULL,
  `laporan_tahunan_terakhir` date DEFAULT NULL,
  `rencana_kerja_3tahun` varchar(255) DEFAULT NULL,
  `pernyataan_admin` varchar(255) DEFAULT NULL,
  `daftar_sarana` varchar(255) DEFAULT NULL,
  `is_parent_cooperative` tinyint(1) DEFAULT 0,
  `parent_cooperative_id` int(11) DEFAULT NULL,
  `hierarchy_level` enum('parent','unit','sub_unit') DEFAULT 'parent',
  `unit_specialization` varchar(100) DEFAULT NULL,
  `operational_scope` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`operational_scope`)),
  `resource_sharing` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`resource_sharing`)),
  `autonomy_level` enum('full','partial','minimal') DEFAULT 'partial',
  PRIMARY KEY (`id`),
  KEY `idx_cooperative_province` (`provinsi_id`),
  KEY `idx_cooperative_regency` (`kabkota_id`),
  KEY `idx_cooperative_district` (`kecamatan_id`),
  KEY `idx_cooperative_village` (`kelurahan_id`),
  KEY `idx_nomor_bh` (`nomor_badan_hukum`),
  KEY `idx_nib` (`nib`),
  KEY `idx_nik_koperasi` (`nik_koperasi`),
  KEY `idx_status_badan_hukum` (`status_badan_hukum`),
  KEY `idx_tanggal_status_terakhir` (`tanggal_status_terakhir`),
  KEY `parent_cooperative_id` (`parent_cooperative_id`),
  KEY `idx_approval_status` (`approval_status`),
  KEY `idx_approval_stage` (`approval_stage`),
  KEY `idx_approved_by` (`approved_by`),
  KEY `idx_approved_at` (`approved_at`),
  CONSTRAINT `koperasi_tenant_ibfk_1` FOREIGN KEY (`parent_cooperative_id`) REFERENCES `koperasi_tenant` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `koperasi_tenant`
--

LOCK TABLES `koperasi_tenant` WRITE;
/*!40000 ALTER TABLE `koperasi_tenant` DISABLE KEYS */;
INSERT INTO `koperasi_tenant` VALUES (8,'KSP POLRES SAMOSIR','[\"1\"]',NULL,'belum_terdaftar','approved','completed',NULL,'2026-02-08 11:52:06',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,'jl. Danau Toba no 03, PASAR PANGURURAN, PANGURURAN, KABUPATEN SAMOSIR, SUMATERA UTARA, 22392',NULL,NULL,NULL,'2026-02-08 11:52:06','2026-02-10 15:43:04',3,40,590,10617,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0.00,NULL,NULL,NULL,NULL,NULL,0,NULL,'parent',NULL,NULL,NULL,'partial'),(9,'KSP-PEB Pusat','[\"KSP\"]',NULL,'belum_terdaftar','approved','completed',NULL,'2026-02-09 03:19:34',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,NULL,NULL,NULL,NULL,'2026-02-09 03:19:34','2026-02-10 15:43:04',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,0,0.00,NULL,NULL,NULL,NULL,NULL,1,NULL,'parent','Central Management',NULL,NULL,'partial');
/*!40000 ALTER TABLE `koperasi_tenant` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `koperasi_unit`
--

DROP TABLE IF EXISTS `koperasi_unit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `koperasi_unit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cooperative_id` int(11) NOT NULL,
  `nama_unit` varchar(100) NOT NULL,
  `kode_unit` varchar(20) NOT NULL,
  `jenis_unit` enum('simpan_pinjam','pemasaran','produksi','jasa','administrasi') NOT NULL,
  `template_id` int(11) DEFAULT NULL,
  `custom_settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_settings`)),
  `deskripsi` text DEFAULT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `manager_user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cooperative_unit` (`cooperative_id`,`kode_unit`),
  KEY `manager_user_id` (`manager_user_id`),
  KEY `template_id` (`template_id`),
  CONSTRAINT `fk_koperasi_unit_cooperative` FOREIGN KEY (`cooperative_id`) REFERENCES `koperasi_tenant` (`id`) ON DELETE CASCADE,
  CONSTRAINT `koperasi_unit_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `koperasi_tenant` (`id`) ON DELETE CASCADE,
  CONSTRAINT `koperasi_unit_ibfk_2` FOREIGN KEY (`manager_user_id`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL,
  CONSTRAINT `koperasi_unit_ibfk_3` FOREIGN KEY (`template_id`) REFERENCES `koperasi_unit_type_template` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `koperasi_unit`
--

LOCK TABLES `koperasi_unit` WRITE;
/*!40000 ALTER TABLE `koperasi_unit` DISABLE KEYS */;
INSERT INTO `koperasi_unit` VALUES (4,8,'Unit Simpan Pinjam Pusat','USP-01','simpan_pinjam',NULL,NULL,NULL,'active',NULL,'2026-02-09 20:31:13','2026-02-09 20:31:13'),(5,8,'Unit Pemasaran Regional','UPM-01','pemasaran',NULL,NULL,NULL,'active',NULL,'2026-02-09 20:31:13','2026-02-09 20:31:13'),(6,9,'Unit Administrasi Inti','UAI-01','administrasi',NULL,NULL,NULL,'active',NULL,'2026-02-09 20:31:13','2026-02-09 20:31:13');
/*!40000 ALTER TABLE `koperasi_unit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `koperasi_unit_communication`
--

DROP TABLE IF EXISTS `koperasi_unit_communication`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `koperasi_unit_communication` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_unit_id` int(11) NOT NULL,
  `recipient_unit_id` int(11) DEFAULT NULL,
  `communication_type` enum('announcement','request','report','coordination','alert') NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `status` enum('sent','read','responded','archived') DEFAULT 'sent',
  `response_required` tinyint(1) DEFAULT 0,
  `response_deadline` date DEFAULT NULL,
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attachments`)),
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `sender_unit_id` (`sender_unit_id`),
  KEY `recipient_unit_id` (`recipient_unit_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `koperasi_unit_communication_ibfk_1` FOREIGN KEY (`sender_unit_id`) REFERENCES `koperasi_unit` (`id`) ON DELETE CASCADE,
  CONSTRAINT `koperasi_unit_communication_ibfk_2` FOREIGN KEY (`recipient_unit_id`) REFERENCES `koperasi_unit` (`id`) ON DELETE SET NULL,
  CONSTRAINT `koperasi_unit_communication_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `koperasi_unit_communication`
--

LOCK TABLES `koperasi_unit_communication` WRITE;
/*!40000 ALTER TABLE `koperasi_unit_communication` DISABLE KEYS */;
/*!40000 ALTER TABLE `koperasi_unit_communication` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `koperasi_unit_hierarchy`
--

DROP TABLE IF EXISTS `koperasi_unit_hierarchy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `koperasi_unit_hierarchy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_unit_id` int(11) NOT NULL,
  `child_unit_id` int(11) NOT NULL,
  `relationship_type` enum('manages','supports','coordinates','reports_to') DEFAULT 'manages',
  `relationship_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`relationship_details`)),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_parent_child` (`parent_unit_id`,`child_unit_id`),
  KEY `child_unit_id` (`child_unit_id`),
  CONSTRAINT `koperasi_unit_hierarchy_ibfk_1` FOREIGN KEY (`parent_unit_id`) REFERENCES `koperasi_unit` (`id`) ON DELETE CASCADE,
  CONSTRAINT `koperasi_unit_hierarchy_ibfk_2` FOREIGN KEY (`child_unit_id`) REFERENCES `koperasi_unit` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `koperasi_unit_hierarchy`
--

LOCK TABLES `koperasi_unit_hierarchy` WRITE;
/*!40000 ALTER TABLE `koperasi_unit_hierarchy` DISABLE KEYS */;
/*!40000 ALTER TABLE `koperasi_unit_hierarchy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `koperasi_unit_izin`
--

DROP TABLE IF EXISTS `koperasi_unit_izin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `koperasi_unit_izin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `izin_modul_id` int(11) NOT NULL,
  `akses_level` enum('read','write','admin','none') DEFAULT 'read',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_unit_permission` (`unit_id`,`izin_modul_id`),
  KEY `izin_modul_id` (`izin_modul_id`),
  CONSTRAINT `koperasi_unit_izin_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `koperasi_unit` (`id`) ON DELETE CASCADE,
  CONSTRAINT `koperasi_unit_izin_ibfk_2` FOREIGN KEY (`izin_modul_id`) REFERENCES `izin_modul` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `koperasi_unit_izin`
--

LOCK TABLES `koperasi_unit_izin` WRITE;
/*!40000 ALTER TABLE `koperasi_unit_izin` DISABLE KEYS */;
/*!40000 ALTER TABLE `koperasi_unit_izin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `koperasi_unit_keuangan`
--

DROP TABLE IF EXISTS `koperasi_unit_keuangan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `koperasi_unit_keuangan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `tahun_buku` year(4) NOT NULL,
  `periode_mulai` date NOT NULL,
  `periode_akhir` date NOT NULL,
  `simpanan_pokok` decimal(15,2) DEFAULT 0.00,
  `simpanan_wajib` decimal(15,2) DEFAULT 0.00,
  `bunga_pinjaman` decimal(5,2) DEFAULT 12.00,
  `denda_telat` decimal(5,2) DEFAULT 2.00,
  `periode_shu` enum('yearly','semi_annual','quarterly') DEFAULT 'yearly',
  `status` enum('active','inactive','closed') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_unit_year` (`unit_id`,`tahun_buku`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `koperasi_unit_keuangan_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `koperasi_unit` (`id`) ON DELETE CASCADE,
  CONSTRAINT `koperasi_unit_keuangan_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `koperasi_unit_keuangan`
--

LOCK TABLES `koperasi_unit_keuangan` WRITE;
/*!40000 ALTER TABLE `koperasi_unit_keuangan` DISABLE KEYS */;
/*!40000 ALTER TABLE `koperasi_unit_keuangan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `koperasi_unit_manager_permissions`
--

DROP TABLE IF EXISTS `koperasi_unit_manager_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `koperasi_unit_manager_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `manager_user_id` int(11) NOT NULL,
  `permission_scope` enum('unit_only','unit_and_subunits','cross_unit') DEFAULT 'unit_only',
  `financial_limit` decimal(15,2) DEFAULT NULL,
  `approval_limit` decimal(15,2) DEFAULT NULL,
  `can_create_subunits` tinyint(1) DEFAULT 0,
  `can_manage_personnel` tinyint(1) DEFAULT 1,
  `can_access_parent_data` tinyint(1) DEFAULT 0,
  `can_initiate_projects` tinyint(1) DEFAULT 0,
  `permission_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permission_details`)),
  `granted_at` timestamp NULL DEFAULT current_timestamp(),
  `granted_by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_unit_manager` (`unit_id`,`manager_user_id`),
  KEY `manager_user_id` (`manager_user_id`),
  KEY `granted_by` (`granted_by`),
  CONSTRAINT `koperasi_unit_manager_permissions_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `koperasi_unit` (`id`) ON DELETE CASCADE,
  CONSTRAINT `koperasi_unit_manager_permissions_ibfk_2` FOREIGN KEY (`manager_user_id`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE,
  CONSTRAINT `koperasi_unit_manager_permissions_ibfk_3` FOREIGN KEY (`granted_by`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `koperasi_unit_manager_permissions`
--

LOCK TABLES `koperasi_unit_manager_permissions` WRITE;
/*!40000 ALTER TABLE `koperasi_unit_manager_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `koperasi_unit_manager_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `koperasi_unit_metrics`
--

DROP TABLE IF EXISTS `koperasi_unit_metrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `koperasi_unit_metrics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `metric_type` enum('financial','operational','member','quality','efficiency') NOT NULL,
  `metric_name` varchar(100) NOT NULL,
  `metric_value` decimal(15,4) DEFAULT NULL,
  `metric_unit` varchar(20) DEFAULT NULL,
  `reporting_period` date NOT NULL,
  `target_value` decimal(15,4) DEFAULT NULL,
  `status` enum('on_track','behind','exceeded','critical') DEFAULT 'on_track',
  `notes` text DEFAULT NULL,
  `reported_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_unit_period` (`unit_id`,`reporting_period`),
  KEY `idx_metric_type` (`metric_type`,`reporting_period`),
  CONSTRAINT `koperasi_unit_metrics_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `koperasi_unit` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `koperasi_unit_metrics`
--

LOCK TABLES `koperasi_unit_metrics` WRITE;
/*!40000 ALTER TABLE `koperasi_unit_metrics` DISABLE KEYS */;
/*!40000 ALTER TABLE `koperasi_unit_metrics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `koperasi_unit_modul`
--

DROP TABLE IF EXISTS `koperasi_unit_modul`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `koperasi_unit_modul` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `modul_id` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `custom_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_config`)),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_unit_module` (`unit_id`,`modul_id`),
  KEY `modul_id` (`modul_id`),
  CONSTRAINT `koperasi_unit_modul_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `koperasi_unit` (`id`) ON DELETE CASCADE,
  CONSTRAINT `koperasi_unit_modul_ibfk_2` FOREIGN KEY (`modul_id`) REFERENCES `modul` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `koperasi_unit_modul`
--

LOCK TABLES `koperasi_unit_modul` WRITE;
/*!40000 ALTER TABLE `koperasi_unit_modul` DISABLE KEYS */;
INSERT INTO `koperasi_unit_modul` VALUES (6,4,2,1,NULL,'2026-02-09 20:33:14'),(7,4,3,1,NULL,'2026-02-09 20:33:14'),(8,5,5,1,NULL,'2026-02-09 20:33:14'),(9,5,26,1,NULL,'2026-02-09 20:33:14'),(10,6,6,1,NULL,'2026-02-09 20:33:14');
/*!40000 ALTER TABLE `koperasi_unit_modul` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `koperasi_unit_pengaturan`
--

DROP TABLE IF EXISTS `koperasi_unit_pengaturan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `koperasi_unit_pengaturan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('string','integer','boolean','json','float') DEFAULT 'string',
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_unit_setting` (`unit_id`,`setting_key`),
  CONSTRAINT `koperasi_unit_pengaturan_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `koperasi_unit` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `koperasi_unit_pengaturan`
--

LOCK TABLES `koperasi_unit_pengaturan` WRITE;
/*!40000 ALTER TABLE `koperasi_unit_pengaturan` DISABLE KEYS */;
/*!40000 ALTER TABLE `koperasi_unit_pengaturan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `koperasi_unit_type_modules`
--

DROP TABLE IF EXISTS `koperasi_unit_type_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `koperasi_unit_type_modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) NOT NULL,
  `modul_id` int(11) NOT NULL,
  `is_default_active` tinyint(1) DEFAULT 1,
  `can_be_disabled` tinyint(1) DEFAULT 1,
  `default_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`default_config`)),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_template_module` (`template_id`,`modul_id`),
  KEY `modul_id` (`modul_id`),
  CONSTRAINT `koperasi_unit_type_modules_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `koperasi_unit_type_template` (`id`) ON DELETE CASCADE,
  CONSTRAINT `koperasi_unit_type_modules_ibfk_2` FOREIGN KEY (`modul_id`) REFERENCES `modul` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `koperasi_unit_type_modules`
--

LOCK TABLES `koperasi_unit_type_modules` WRITE;
/*!40000 ALTER TABLE `koperasi_unit_type_modules` DISABLE KEYS */;
INSERT INTO `koperasi_unit_type_modules` VALUES (1,1,2,1,1,NULL),(2,1,5,1,1,NULL),(3,1,4,1,1,NULL),(4,1,3,1,1,NULL),(8,2,2,1,1,NULL),(9,2,5,1,1,NULL),(10,2,6,1,1,NULL),(11,12,2,1,1,NULL),(12,12,5,1,1,NULL),(13,12,6,1,1,NULL),(14,8,2,1,1,NULL),(15,8,5,1,1,NULL),(16,8,6,1,1,NULL),(17,10,2,1,1,NULL),(18,10,5,1,1,NULL),(19,10,6,1,1,NULL),(20,9,2,1,1,NULL),(21,9,5,1,1,NULL),(22,9,6,1,1,NULL),(23,3,2,1,1,NULL),(24,3,5,1,1,NULL),(25,3,6,1,1,NULL),(26,7,2,1,1,NULL),(27,7,5,1,1,NULL),(28,7,6,1,1,NULL),(29,11,2,1,1,NULL),(30,11,5,1,1,NULL),(31,11,6,1,1,NULL);
/*!40000 ALTER TABLE `koperasi_unit_type_modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `koperasi_unit_type_settings`
--

DROP TABLE IF EXISTS `koperasi_unit_type_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `koperasi_unit_type_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_label` varchar(255) NOT NULL,
  `setting_description` text DEFAULT NULL,
  `setting_type` enum('string','integer','boolean','json','float','select','multiselect') DEFAULT 'string',
  `default_value` text DEFAULT NULL,
  `validation_rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`validation_rules`)),
  `is_required` tinyint(1) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `category` varchar(100) DEFAULT 'general',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_template_setting` (`template_id`,`setting_key`),
  KEY `idx_category` (`category`),
  CONSTRAINT `koperasi_unit_type_settings_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `koperasi_unit_type_template` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `koperasi_unit_type_settings`
--

LOCK TABLES `koperasi_unit_type_settings` WRITE;
/*!40000 ALTER TABLE `koperasi_unit_type_settings` DISABLE KEYS */;
INSERT INTO `koperasi_unit_type_settings` VALUES (1,1,'interest_rate_savings','Suku Bunga Simpanan','Suku bunga tahunan untuk simpanan (%)','float','4.5',NULL,1,0,'financial'),(2,1,'interest_rate_loans','Suku Bunga Pinjaman','Suku bunga tahunan untuk pinjaman (%)','float','12.0',NULL,1,0,'financial'),(3,1,'penalty_rate','Denda Keterlambatan','Denda per hari untuk keterlambatan pembayaran (%)','float','2.0',NULL,1,0,'financial');
/*!40000 ALTER TABLE `koperasi_unit_type_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `koperasi_unit_type_template`
--

DROP TABLE IF EXISTS `koperasi_unit_type_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `koperasi_unit_type_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_name` varchar(100) NOT NULL,
  `template_code` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `base_cooperative_type` varchar(50) DEFAULT NULL,
  `is_custom` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `template_code` (`template_code`),
  KEY `idx_template_code` (`template_code`),
  KEY `idx_base_type` (`base_cooperative_type`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `koperasi_unit_type_template`
--

LOCK TABLES `koperasi_unit_type_template` WRITE;
/*!40000 ALTER TABLE `koperasi_unit_type_template` DISABLE KEYS */;
INSERT INTO `koperasi_unit_type_template` VALUES (1,'Unit Simpan Pinjam','USP','Unit untuk mengelola simpanan dan pinjaman anggota','KSP',0,1,'2026-02-09 03:12:46'),(2,'Unit Pemasaran','UPM','Unit untuk mengelola pemasaran dan penjualan produk','KPAS',0,1,'2026-02-09 03:12:46'),(3,'Unit Produksi','UPR','Unit untuk mengelola proses produksi','KP',0,1,'2026-02-09 03:12:46'),(4,'Unit Jasa','UJS','Unit untuk mengelola layanan dan konsultasi','KJ',0,1,'2026-02-09 03:12:46'),(5,'Unit Administrasi','UAD','Unit untuk mengelola administrasi dan keuangan',NULL,0,1,'2026-02-09 03:12:46'),(6,'Unit Konsumsi','UKS','Unit untuk mengelola kebutuhan konsumsi anggota','KK',0,1,'2026-02-09 03:12:46'),(7,'Unit Pertanian','UPT','Unit untuk mengelola kegiatan pertanian','KOPERTA',0,1,'2026-02-09 03:12:46'),(8,'Unit Nelayan','UNL','Unit untuk mengelola kegiatan perikanan','KOPERNAL',0,1,'2026-02-09 03:12:46'),(9,'Unit Peternakan','UPK','Unit untuk mengelola kegiatan peternakan','KOPERTAK',0,1,'2026-02-09 03:12:46'),(10,'Unit Perdagangan','UPD','Unit untuk mengelola kegiatan perdagangan','KOPERDAG',0,1,'2026-02-09 03:12:46'),(11,'Unit Serba Usaha','USU','Unit untuk mengelola berbagai jenis usaha','KSU',0,1,'2026-02-09 03:12:46'),(12,'Unit Karyawan','UKR','Unit untuk mengelola kesejahteraan karyawan','KKAR',0,1,'2026-02-09 03:12:46');
/*!40000 ALTER TABLE `koperasi_unit_type_template` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `koperasi_workflow_events`
--

DROP TABLE IF EXISTS `koperasi_workflow_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `koperasi_workflow_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `koperasi_id` int(11) NOT NULL,
  `event_type` enum('registration_submitted','auto_check_passed','auto_check_failed','manual_review','approval','rejection','activation','document_request','document_submitted') NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_koperasi_id` (`koperasi_id`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `koperasi_workflow_events_ibfk_1` FOREIGN KEY (`koperasi_id`) REFERENCES `koperasi_tenant` (`id`) ON DELETE CASCADE,
  CONSTRAINT `koperasi_workflow_events_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `koperasi_workflow_events`
--

LOCK TABLES `koperasi_workflow_events` WRITE;
/*!40000 ALTER TABLE `koperasi_workflow_events` DISABLE KEYS */;
INSERT INTO `koperasi_workflow_events` VALUES (1,8,'registration_submitted','{\"cooperative_name\": \"KSP POLRES SAMOSIR\", \"auto_check_results\": {\"duplicates\": false, \"validation\": true}}',NULL,'2026-02-08 11:52:06'),(2,9,'registration_submitted','{\"cooperative_name\": \"KSP-PEB Pusat\", \"auto_check_results\": {\"duplicates\": false, \"validation\": true}}',NULL,'2026-02-09 03:19:34');
/*!40000 ALTER TABLE `koperasi_workflow_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kredit`
--

DROP TABLE IF EXISTS `kredit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `kredit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cooperative_id` int(11) NOT NULL,
  `anggota_id` int(11) NOT NULL,
  `nomor_kredit` varchar(50) NOT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `bunga` decimal(5,2) NOT NULL,
  `tenor` int(11) NOT NULL,
  `cicilan` decimal(15,2) NOT NULL,
  `total_pembayaran` decimal(15,2) NOT NULL,
  `total_bunga` decimal(15,2) NOT NULL,
  `tujuan` text NOT NULL,
  `status` enum('pending','approved','rejected','active','completed') DEFAULT 'pending',
  `tanggal_pengajuan` date NOT NULL,
  `tanggal_approval` date DEFAULT NULL,
  `tanggal_pencairan` date DEFAULT NULL,
  `tanggal_jatuh_tempo` date DEFAULT NULL,
  `tanggal_pelunasan` date DEFAULT NULL,
  `jumlah_disetujui` decimal(15,2) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `disbursed_by` int(11) DEFAULT NULL,
  `alasan_approval` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nomor_kredit` (`nomor_kredit`),
  KEY `approved_by` (`approved_by`),
  KEY `disbursed_by` (`disbursed_by`),
  KEY `created_by` (`created_by`),
  KEY `idx_cooperative` (`cooperative_id`),
  KEY `idx_anggota` (`anggota_id`),
  KEY `idx_status` (`status`),
  KEY `idx_nomor_kredit` (`nomor_kredit`),
  CONSTRAINT `kredit_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `koperasi_tenant` (`id`),
  CONSTRAINT `kredit_ibfk_2` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`),
  CONSTRAINT `kredit_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `pengguna` (`id`),
  CONSTRAINT `kredit_ibfk_4` FOREIGN KEY (`disbursed_by`) REFERENCES `pengguna` (`id`),
  CONSTRAINT `kredit_ibfk_5` FOREIGN KEY (`created_by`) REFERENCES `pengguna` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kredit`
--

LOCK TABLES `kredit` WRITE;
/*!40000 ALTER TABLE `kredit` DISABLE KEYS */;
/*!40000 ALTER TABLE `kredit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kredit_angsuran`
--

DROP TABLE IF EXISTS `kredit_angsuran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `kredit_angsuran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kredit_id` int(11) NOT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `tanggal_angsuran` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_kredit` (`kredit_id`),
  KEY `idx_tanggal` (`tanggal_angsuran`),
  CONSTRAINT `kredit_angsuran_ibfk_1` FOREIGN KEY (`kredit_id`) REFERENCES `kredit` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kredit_angsuran_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `pengguna` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kredit_angsuran`
--

LOCK TABLES `kredit_angsuran` WRITE;
/*!40000 ALTER TABLE `kredit_angsuran` DISABLE KEYS */;
/*!40000 ALTER TABLE `kredit_angsuran` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lahan`
--

DROP TABLE IF EXISTS `lahan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lahan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `land_code` varchar(50) NOT NULL,
  `land_name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `land_type` enum('sawah','tegalan','kebun','perkebunan','lahan_kering','lahan_basah','greenhouse','hidroponik') NOT NULL,
  `area_size` decimal(10,4) NOT NULL,
  `area_unit` enum('m2','ha','are') NOT NULL DEFAULT 'm2',
  `soil_type` varchar(100) DEFAULT NULL,
  `water_source` enum('hujan','irigasi','sumur','sungai','embung','air_tanah') DEFAULT NULL,
  `drainage_system` enum('baik','sedang','buruk','tidak_ada') DEFAULT NULL,
  `fertility_level` enum('sangat_subur','subur','sedang','kurang_subur','tidak_subur') DEFAULT NULL,
  `ph_level` decimal(4,2) DEFAULT NULL,
  `organic_matter` decimal(5,2) DEFAULT NULL,
  `ownership_type` enum('milik_sendiri','sewa','bagi_hasil','pinjam_pakai') DEFAULT NULL,
  `ownership_document` varchar(255) DEFAULT NULL,
  `coordinates` varchar(100) DEFAULT NULL,
  `current_planting_id` int(11) DEFAULT NULL,
  `status` enum('available','planted','maintenance','fallow','deleted') NOT NULL DEFAULT 'available',
  `notes` text DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_land_code` (`land_code`,`unit_id`),
  KEY `idx_location` (`location`),
  KEY `idx_land_type` (`land_type`),
  KEY `idx_status` (`status`),
  KEY `idx_current_planting` (`current_planting_id`),
  KEY `idx_unit` (`unit_id`),
  CONSTRAINT `fk_lahan_current_planting` FOREIGN KEY (`current_planting_id`) REFERENCES `tanam_planting` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lahan`
--

LOCK TABLES `lahan` WRITE;
/*!40000 ALTER TABLE `lahan` DISABLE KEYS */;
/*!40000 ALTER TABLE `lahan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `layanan`
--

DROP TABLE IF EXISTS `layanan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `layanan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `koperasi_id` int(11) NOT NULL,
  `kode_layanan` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga` decimal(15,2) NOT NULL,
  `durasi` int(11) DEFAULT NULL COMMENT 'Dalam menit',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `koperasi_id` (`koperasi_id`,`kode_layanan`),
  CONSTRAINT `layanan_ibfk_1` FOREIGN KEY (`koperasi_id`) REFERENCES `koperasi_tenant` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `layanan`
--

LOCK TABLES `layanan` WRITE;
/*!40000 ALTER TABLE `layanan` DISABLE KEYS */;
/*!40000 ALTER TABLE `layanan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `livestock`
--

DROP TABLE IF EXISTS `livestock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `livestock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `livestock_code` varchar(50) NOT NULL,
  `livestock_name` varchar(255) NOT NULL,
  `species` varchar(100) NOT NULL,
  `breed` varchar(255) DEFAULT NULL,
  `origin` varchar(255) DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  `acquisition_date` date DEFAULT NULL,
  `initial_quantity` int(11) NOT NULL DEFAULT 0,
  `initial_weight` decimal(8,2) NOT NULL DEFAULT 0.00,
  `average_age` int(11) DEFAULT NULL,
  `growth_rate` decimal(5,2) DEFAULT NULL,
  `feed_type` varchar(100) DEFAULT NULL,
  `feed_conversion_ratio` decimal(5,2) DEFAULT NULL,
  `optimal_temperature` decimal(5,2) DEFAULT NULL,
  `optimal_humidity` decimal(5,2) DEFAULT NULL,
  `market_price` decimal(15,2) DEFAULT NULL,
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_livestock_code` (`livestock_code`,`unit_id`),
  KEY `idx_species` (`species`),
  KEY `idx_status` (`status`),
  KEY `idx_unit` (`unit_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `livestock`
--

LOCK TABLES `livestock` WRITE;
/*!40000 ALTER TABLE `livestock` DISABLE KEYS */;
INSERT INTO `livestock` VALUES (1,'TRN001','Sapi Perah','Sapi',NULL,NULL,NULL,NULL,20,400.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,1,1,'2026-02-12 18:03:13','2026-02-12 18:03:13'),(2,'TRN002','Kambing Etawa','Kambing',NULL,NULL,NULL,NULL,15,45.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,1,1,'2026-02-12 18:03:13','2026-02-12 18:03:13'),(3,'TRN003','Ayam Broiler','Ayam',NULL,NULL,NULL,NULL,100,1.50,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,1,1,'2026-02-12 18:03:13','2026-02-12 18:03:13'),(4,'TRN004','Ayam Petelur','Ayam',NULL,NULL,NULL,NULL,50,1.80,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,1,1,'2026-02-12 18:03:13','2026-02-12 18:03:13'),(5,'TRN005','Babi','Babi',NULL,NULL,NULL,NULL,10,80.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,1,1,'2026-02-12 18:03:13','2026-02-12 18:03:13');
/*!40000 ALTER TABLE `livestock` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb3 */ ;
/*!50003 SET character_set_results = utf8mb3 */ ;
/*!50003 SET collation_connection  = utf8mb3_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER IF NOT EXISTS `tr_livestock_stock_create` 
AFTER INSERT ON `livestock`
FOR EACH ROW
BEGIN
    INSERT INTO `livestock_stock` (livestock_id, current_stock, average_weight, total_weight, unit_id, created_by, created_at)
    VALUES (NEW.id, NEW.initial_quantity, NEW.initial_weight, NEW.initial_quantity * NEW.initial_weight, NEW.unit_id, NEW.created_by, NOW());
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `livestock_feeding`
--

DROP TABLE IF EXISTS `livestock_feeding`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `livestock_feeding` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `feeding_code` varchar(50) NOT NULL,
  `livestock_id` int(11) NOT NULL,
  `feeding_date` date NOT NULL,
  `feeding_time` time DEFAULT NULL,
  `feed_type` varchar(100) NOT NULL,
  `feed_amount` decimal(10,4) NOT NULL DEFAULT 0.0000,
  `feed_unit` varchar(20) NOT NULL DEFAULT 'kg',
  `feed_cost` decimal(15,2) DEFAULT NULL,
  `livestock_count` int(11) DEFAULT NULL,
  `average_weight_before` decimal(8,2) DEFAULT NULL,
  `average_weight_after` decimal(8,2) DEFAULT NULL,
  `weather_condition` varchar(100) DEFAULT NULL,
  `feeding_method` enum('free_feeding','controlled_feeding','individual_feeding') DEFAULT NULL,
  `appetite_level` enum('good','normal','poor') DEFAULT NULL,
  `health_condition` enum('healthy','sick','recovering') DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_feeding_code` (`feeding_code`),
  KEY `idx_livestock` (`livestock_id`),
  KEY `idx_feeding_date` (`feeding_date`),
  KEY `idx_unit` (`unit_id`),
  CONSTRAINT `fk_feeding_livestock` FOREIGN KEY (`livestock_id`) REFERENCES `livestock` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `livestock_feeding`
--

LOCK TABLES `livestock_feeding` WRITE;
/*!40000 ALTER TABLE `livestock_feeding` DISABLE KEYS */;
/*!40000 ALTER TABLE `livestock_feeding` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `livestock_health`
--

DROP TABLE IF EXISTS `livestock_health`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `livestock_health` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `health_code` varchar(50) NOT NULL,
  `livestock_id` int(11) NOT NULL,
  `check_date` date NOT NULL,
  `check_time` time DEFAULT NULL,
  `health_status` enum('healthy','sick','recovering','dead') NOT NULL DEFAULT 'healthy',
  `symptoms` text DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `treatment` text DEFAULT NULL,
  `medication` varchar(255) DEFAULT NULL,
  `dosage` varchar(100) DEFAULT NULL,
  `vaccination_type` varchar(255) DEFAULT NULL,
  `vaccination_batch` varchar(100) DEFAULT NULL,
  `deworming_type` varchar(255) DEFAULT NULL,
  `mortality_count` int(11) DEFAULT 0,
  `mortality_rate` decimal(5,2) DEFAULT 0.00,
  `body_condition_score` decimal(3,1) DEFAULT NULL,
  `temperature` decimal(4,2) DEFAULT NULL,
  `heart_rate` int(11) DEFAULT NULL,
  `respiratory_rate` int(11) DEFAULT NULL,
  `checked_by` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_health_code` (`health_code`),
  KEY `idx_livestock` (`livestock_id`),
  KEY `idx_check_date` (`check_date`),
  KEY `idx_health_status` (`health_status`),
  KEY `idx_unit` (`unit_id`),
  CONSTRAINT `fk_health_livestock` FOREIGN KEY (`livestock_id`) REFERENCES `livestock` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `livestock_health`
--

LOCK TABLES `livestock_health` WRITE;
/*!40000 ALTER TABLE `livestock_health` DISABLE KEYS */;
/*!40000 ALTER TABLE `livestock_health` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `livestock_production`
--

DROP TABLE IF EXISTS `livestock_production`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `livestock_production` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `production_code` varchar(50) NOT NULL,
  `livestock_id` int(11) NOT NULL,
  `production_date` date NOT NULL,
  `production_type` enum('milk','eggs','meat','wool','honey','other') NOT NULL,
  `production_amount` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `production_unit` varchar(20) NOT NULL DEFAULT 'kg',
  `quality_grade` enum('premium','good','fair','poor') DEFAULT NULL,
  `average_weight` decimal(8,2) DEFAULT NULL,
  `size_distribution` text DEFAULT NULL,
  `market_price` decimal(15,2) DEFAULT NULL,
  `total_revenue` decimal(15,2) DEFAULT NULL,
  `production_cost` decimal(15,2) DEFAULT NULL,
  `profit_loss` decimal(15,2) DEFAULT NULL,
  `buyer` varchar(255) DEFAULT NULL,
  `sale_date` date DEFAULT NULL,
  `storage_condition` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_production_code` (`production_code`),
  KEY `idx_livestock` (`livestock_id`),
  KEY `idx_production_date` (`production_date`),
  KEY `idx_production_type` (`production_type`),
  KEY `idx_unit` (`unit_id`),
  CONSTRAINT `fk_production_livestock` FOREIGN KEY (`livestock_id`) REFERENCES `livestock` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `livestock_production`
--

LOCK TABLES `livestock_production` WRITE;
/*!40000 ALTER TABLE `livestock_production` DISABLE KEYS */;
/*!40000 ALTER TABLE `livestock_production` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `livestock_stock`
--

DROP TABLE IF EXISTS `livestock_stock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `livestock_stock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `livestock_id` int(11) NOT NULL,
  `current_stock` int(11) NOT NULL DEFAULT 0,
  `average_weight` decimal(8,2) NOT NULL DEFAULT 0.00,
  `total_weight` decimal(15,2) NOT NULL DEFAULT 0.00,
  `feeding_frequency` enum('harian','dua_hari','tiga_hari','mingguan') DEFAULT NULL,
  `last_feeding_time` timestamp NULL DEFAULT NULL,
  `last_health_check` timestamp NULL DEFAULT NULL,
  `last_vaccination` timestamp NULL DEFAULT NULL,
  `last_deworming` timestamp NULL DEFAULT NULL,
  `mortality_rate` decimal(5,2) DEFAULT 0.00,
  `production_rate` decimal(5,2) DEFAULT 0.00,
  `health_status` enum('healthy','sick','recovering') DEFAULT NULL,
  `environment_status` enum('good','fair','poor') DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_livestock_stock` (`livestock_id`,`unit_id`),
  KEY `idx_current_stock` (`current_stock`),
  KEY `idx_unit` (`unit_id`),
  CONSTRAINT `fk_livestock_stock_livestock` FOREIGN KEY (`livestock_id`) REFERENCES `livestock` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `livestock_stock`
--

LOCK TABLES `livestock_stock` WRITE;
/*!40000 ALTER TABLE `livestock_stock` DISABLE KEYS */;
INSERT INTO `livestock_stock` VALUES (1,1,18,425.00,7650.00,NULL,NULL,NULL,NULL,NULL,0.00,0.00,NULL,NULL,1,1,'2026-02-12 18:03:13','2026-02-12 18:03:13'),(2,2,14,48.50,679.00,NULL,NULL,NULL,NULL,NULL,0.00,0.00,NULL,NULL,1,1,'2026-02-12 18:03:13','2026-02-12 18:03:13'),(3,3,95,2.10,199.50,NULL,NULL,NULL,NULL,NULL,0.00,0.00,NULL,NULL,1,1,'2026-02-12 18:03:13','2026-02-12 18:03:13'),(4,4,48,1.95,93.60,NULL,NULL,NULL,NULL,NULL,0.00,0.00,NULL,NULL,1,1,'2026-02-12 18:03:13','2026-02-12 18:03:13'),(5,5,9,85.00,765.00,NULL,NULL,NULL,NULL,NULL,0.00,0.00,NULL,NULL,1,1,'2026-02-12 18:03:13','2026-02-12 18:03:13');
/*!40000 ALTER TABLE `livestock_stock` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loan_types`
--

DROP TABLE IF EXISTS `loan_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `loan_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `interest_rate` decimal(8,4) DEFAULT 0.0000,
  `interest_method` enum('flat','menurun') DEFAULT 'flat',
  `max_tenor_months` int(11) DEFAULT 0,
  `max_plafon_savings_ratio` decimal(8,2) DEFAULT 0.00,
  `max_installment_income_ratio` decimal(8,2) DEFAULT 0.00,
  `admin_fee` decimal(15,2) DEFAULT 0.00,
  `provision_fee` decimal(8,2) DEFAULT 0.00,
  `penalty_rate` decimal(8,4) DEFAULT 0.0000,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `insurance_rate` decimal(8,2) DEFAULT 0.00,
  `require_insurance` tinyint(1) DEFAULT 0,
  `ltv_ratio` decimal(8,2) DEFAULT 0.00,
  `collateral_type` varchar(100) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loan_types`
--

LOCK TABLES `loan_types` WRITE;
/*!40000 ALTER TABLE `loan_types` DISABLE KEYS */;
INSERT INTO `loan_types` VALUES (1,'Konsumtif',18.0000,'menurun',24,3.00,40.00,50000.00,1.50,0.1000,'Pinjaman konsumsi, plafon kecil-menengah',1,'2026-02-08 17:40:36','2026-02-08 17:58:19',0.00,0,0.00,''),(2,'Produktif',14.0000,'menurun',36,5.00,40.00,75000.00,2.00,0.1000,'Modal usaha/produktif',1,'2026-02-08 17:40:36','2026-02-08 17:40:36',0.00,0,0.00,''),(3,'Darurat',12.0000,'flat',6,1.00,30.00,25000.00,1.00,0.0500,'Plafon kecil, proses cepat',1,'2026-02-08 17:40:36','2026-02-08 17:40:36',0.00,0,0.00,'');
/*!40000 ALTER TABLE `loan_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log_audit`
--

DROP TABLE IF EXISTS `log_audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `log_audit` (
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
  CONSTRAINT `log_audit_fk_pengguna` FOREIGN KEY (`user_id`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_audit`
--

LOCK TABLES `log_audit` WRITE;
/*!40000 ALTER TABLE `log_audit` DISABLE KEYS */;
/*!40000 ALTER TABLE `log_audit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modal_pokok_perubahan`
--

DROP TABLE IF EXISTS `modal_pokok_perubahan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `modal_pokok_perubahan` (
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
  CONSTRAINT `modal_pokok_perubahan_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `koperasi_tenant` (`id`) ON DELETE CASCADE,
  CONSTRAINT `modal_pokok_perubahan_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `pengguna` (`id`),
  CONSTRAINT `modal_pokok_perubahan_ibfk_3` FOREIGN KEY (`referensi_id`) REFERENCES `rat_sesi` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modal_pokok_perubahan`
--

LOCK TABLES `modal_pokok_perubahan` WRITE;
/*!40000 ALTER TABLE `modal_pokok_perubahan` DISABLE KEYS */;
/*!40000 ALTER TABLE `modal_pokok_perubahan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modul`
--

DROP TABLE IF EXISTS `modul`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `modul` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(50) NOT NULL,
  `nama_tampil` varchar(100) NOT NULL,
  `ikon` varchar(50) DEFAULT NULL,
  `permission_required` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `urutan` int(11) DEFAULT 0,
  `show_in_navbar` tinyint(1) DEFAULT 0,
  `kategori_id` int(11) DEFAULT NULL,
  `module_type` enum('core','type_specific','optional','custom') DEFAULT 'optional',
  `cooperative_types` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`cooperative_types`)),
  `prerequisites` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`prerequisites`)),
  `dependencies` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dependencies`)),
  `version` varchar(20) DEFAULT '1.0.0',
  `is_deprecated` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nama` (`nama`),
  KEY `kategori_id` (`kategori_id`),
  CONSTRAINT `modul_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `modul_kategori` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modul`
--

LOCK TABLES `modul` WRITE;
/*!40000 ALTER TABLE `modul` DISABLE KEYS */;
INSERT INTO `modul` VALUES (1,'dashboard','Dashboard','bi-house-door',NULL,1,1,0,NULL,'optional',NULL,NULL,NULL,'1.0.0',0),(2,'anggota','Data Anggota','bi-people-fill','view_members',1,2,0,3,'core',NULL,NULL,NULL,'1.0.0',0),(3,'simpanan','Simpanan','bi-wallet2','view_savings',1,3,0,2,'core',NULL,NULL,NULL,'1.0.0',0),(4,'pinjaman','Pinjaman','bi-cash-coin','view_loans',1,4,0,2,'type_specific','[\"KSP\"]',NULL,NULL,'1.0.0',0),(5,'laporan','Laporan','bi-file-earmark-bar-graph','view_reports',1,5,0,1,'core',NULL,NULL,NULL,'1.0.0',0),(6,'pengaturan','Pengaturan','bi-gear','admin_access',1,6,1,1,'core',NULL,NULL,NULL,'1.0.0',0),(7,'coop_details','Detail Koperasi','bi-building','manage_cooperative',1,7,0,NULL,'optional',NULL,NULL,NULL,'1.0.0',0),(8,'profil','Profil','bi-person',NULL,1,8,1,1,'core',NULL,NULL,NULL,'1.0.0',0),(9,'kredit','Manajemen Kredit','bi-credit-card','manage_loans',1,0,0,2,'type_specific','[\"KSP\"]',NULL,NULL,'1.0.0',0),(10,'akuntansi','Akuntansi','bi-calculator','view_accounts',1,0,0,2,'type_specific','[\"KSP\"]',NULL,NULL,'1.0.0',0),(11,'lahan','Manajemen Lahan','bi-map','manage_production',1,0,0,7,'type_specific','[\"KOPERTA\"]',NULL,NULL,'1.0.0',0),(12,'tanam','Siklus Tanam','bi-seedling','manage_production',1,0,0,7,'type_specific','[\"KOPERTA\"]',NULL,NULL,'1.0.0',0),(13,'irigasi','Sistem Irigasi','bi-droplet','manage_production',1,0,0,7,'type_specific','[\"KOPERTA\"]',NULL,NULL,'1.0.0',0),(14,'izin_tangkap','Izin Penangkapan','bi-file-earmark-text','manage_production',1,0,0,8,'type_specific','[\"KOPERNAL\"]',NULL,NULL,'1.0.0',0),(15,'tracking_tangkap','Tracking Tangkapan','bi-geo-alt','manage_production',1,0,0,8,'type_specific','[\"KOPERNAL\"]',NULL,NULL,'1.0.0',0),(16,'cuaca_laut','Informasi Cuaca Laut','bi-cloud-rain','manage_production',1,0,0,8,'type_specific','[\"KOPERNAL\"]',NULL,NULL,'1.0.0',0),(17,'ternak','Manajemen Ternak','bi-heart-pulse','manage_production',1,0,0,9,'type_specific','[\"KOPERTAK\"]',NULL,NULL,'1.0.0',0),(18,'pakan','Manajemen Pakan','bi-cup-straw','manage_production',1,0,0,9,'type_specific','[\"KOPERTAK\"]',NULL,NULL,'1.0.0',0),(19,'breeding','Program Breeding','bi-plus-circle','manage_production',1,0,0,9,'type_specific','[\"KOPERTAK\"]',NULL,NULL,'1.0.0',0),(20,'produksi','Manajemen Produksi','bi-cogs','manage_production',1,0,0,4,'type_specific','[\"KP\"]',NULL,NULL,'1.0.0',0),(21,'kualitas','Kontrol Kualitas','bi-check-circle','manage_production',1,0,0,4,'type_specific','[\"KP\"]',NULL,NULL,'1.0.0',0),(22,'inventory','Inventory Barang','bi-boxes','manage_production',1,0,0,4,'type_specific','[\"KP\",\"KK\"]',NULL,NULL,'1.0.0',0),(23,'layanan','Manajemen Layanan','bi-tools','manage_services',1,0,0,10,'type_specific','[\"KJ\"]',NULL,NULL,'1.0.0',0),(24,'booking','Sistem Booking','bi-calendar-check','manage_services',1,0,0,10,'type_specific','[\"KJ\"]',NULL,NULL,'1.0.0',0),(25,'feedback','Customer Feedback','bi-chat-quote','manage_services',1,0,0,10,'type_specific','[\"KJ\"]',NULL,NULL,'1.0.0',0),(26,'promosi','Manajemen Promosi','bi-megaphone','manage_marketing',1,0,0,5,'type_specific','[\"KPAS\"]',NULL,NULL,'1.0.0',0),(27,'distribusi','Jaringan Distribusi','bi-diagram-3','manage_marketing',1,0,0,5,'type_specific','[\"KPAS\",\"KK\"]',NULL,NULL,'1.0.0',0);
/*!40000 ALTER TABLE `modul` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modul_compatibility`
--

DROP TABLE IF EXISTS `modul_compatibility`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `modul_compatibility` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `modul_id` int(11) NOT NULL,
  `compatible_modul_id` int(11) NOT NULL,
  `compatibility_type` enum('required','recommended','conflicts','enhances') DEFAULT 'recommended',
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_modul_compatibility` (`modul_id`,`compatible_modul_id`),
  KEY `compatible_modul_id` (`compatible_modul_id`),
  CONSTRAINT `modul_compatibility_ibfk_1` FOREIGN KEY (`modul_id`) REFERENCES `modul` (`id`) ON DELETE CASCADE,
  CONSTRAINT `modul_compatibility_ibfk_2` FOREIGN KEY (`compatible_modul_id`) REFERENCES `modul` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modul_compatibility`
--

LOCK TABLES `modul_compatibility` WRITE;
/*!40000 ALTER TABLE `modul_compatibility` DISABLE KEYS */;
/*!40000 ALTER TABLE `modul_compatibility` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modul_kategori`
--

DROP TABLE IF EXISTS `modul_kategori`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `modul_kategori` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'bi-circle',
  `urutan` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_nama_kategori` (`nama_kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modul_kategori`
--

LOCK TABLES `modul_kategori` WRITE;
/*!40000 ALTER TABLE `modul_kategori` DISABLE KEYS */;
INSERT INTO `modul_kategori` VALUES (1,'Core System','Modul inti yang diperlukan semua koperasi','bi-house-door',1,1),(2,'Keuangan','Modul untuk pengelolaan keuangan dan akuntansi','bi-cash-coin',2,1),(3,'Anggota','Modul untuk manajemen anggota koperasi','bi-people',3,1),(4,'Produksi','Modul untuk pengelolaan produksi dan operasional','bi-gear',4,1),(5,'Pemasaran','Modul untuk pemasaran dan penjualan','bi-graph-up',5,1),(6,'Logistik','Modul untuk distribusi dan logistik','bi-truck',6,1),(7,'Pertanian','Modul khusus untuk kegiatan pertanian','bi-tree',7,1),(8,'Perikanan','Modul khusus untuk kegiatan perikanan','bi-water',8,1),(9,'Peternakan','Modul khusus untuk kegiatan peternakan','bi-heart',9,1),(10,'Jasa','Modul untuk pengelolaan layanan','bi-tools',10,1);
/*!40000 ALTER TABLE `modul_kategori` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifikasi`
--

DROP TABLE IF EXISTS `notifikasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifikasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','warning','error') DEFAULT 'info',
  `sent_at` timestamp NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_notifications_user_read` (`user_id`,`read_at`),
  CONSTRAINT `notifikasi_fk_pengguna` FOREIGN KEY (`user_id`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifikasi`
--

LOCK TABLES `notifikasi` WRITE;
/*!40000 ALTER TABLE `notifikasi` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifikasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orang`
--

DROP TABLE IF EXISTS `orang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `orang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pengguna_id` int(11) NOT NULL,
  `nama_lengkap` varchar(255) NOT NULL,
  `nama_depan` varchar(100) DEFAULT NULL,
  `nama_tengah` varchar(100) DEFAULT NULL,
  `nama_belakang` varchar(100) DEFAULT NULL,
  `hp` varchar(20) DEFAULT NULL,
  `hp_alternatif` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `nik` varchar(20) DEFAULT NULL,
  `kewarganegaraan` varchar(50) DEFAULT NULL,
  `agama` varchar(50) DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `alamat_lengkap` text DEFAULT NULL,
  `province_id` int(11) DEFAULT NULL,
  `regency_id` int(11) DEFAULT NULL,
  `district_id` int(11) DEFAULT NULL,
  `village_id` int(11) DEFAULT NULL,
  `nama_jalan` varchar(255) DEFAULT NULL,
  `nomor_rumah` varchar(10) DEFAULT NULL,
  `rt` varchar(5) DEFAULT NULL,
  `rw` varchar(5) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `pekerjaan` varchar(100) DEFAULT NULL,
  `instansi` varchar(255) DEFAULT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `dibuat_oleh` int(11) DEFAULT NULL,
  `dibuat_pada` timestamp NULL DEFAULT current_timestamp(),
  `diperbarui_pada` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `pengguna_id` (`pengguna_id`),
  CONSTRAINT `orang_ibfk_1` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orang`
--

LOCK TABLES `orang` WRITE;
/*!40000 ALTER TABLE `orang` DISABLE KEYS */;
INSERT INTO `orang` VALUES (5,10,'admin paling baik di dunia','admin','paling baik di','dunia','6281265511982',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'jl. Danau Toba no 03, PASAR PANGURURAN, PANGURURAN, KABUPATEN SAMOSIR, SUMATERA UTARA, 22392',3,40,590,10617,'jl. Danau Toba no','03',NULL,NULL,'22392','Administrator Koperasi','KSP POLRES SAMOSIR','Administrator','Dibuat otomatis saat registrasi koperasi',10,'2026-02-08 04:52:06','2026-02-08 11:52:06');
/*!40000 ALTER TABLE `orang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pekerjaan_master`
--

DROP TABLE IF EXISTS `pekerjaan_master`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pekerjaan_master` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_pekerjaan` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pekerjaan_master`
--

LOCK TABLES `pekerjaan_master` WRITE;
/*!40000 ALTER TABLE `pekerjaan_master` DISABLE KEYS */;
INSERT INTO `pekerjaan_master` VALUES (1,'PNS','Pegawai Negeri Sipil','2026-02-08 14:27:47'),(2,'Swasta','Karyawan Swasta','2026-02-08 14:27:47'),(3,'Wiraswasta','Wiraswasta/Entrepreneur','2026-02-08 14:27:47'),(4,'Pelajar','Pelajar/Mahasiswa','2026-02-08 14:27:47'),(5,'Ibu Rumah Tangga','Ibu Rumah Tangga','2026-02-08 14:27:47'),(6,'TNI/Polri','Tentara Nasional Indonesia/Polisi Republik Indonesia','2026-02-08 14:27:47'),(7,'Buruh','Buruh/Pekerja Kasar','2026-02-08 14:27:47'),(8,'Lainnya','Pekerjaan Lainnya','2026-02-08 14:27:47');
/*!40000 ALTER TABLE `pekerjaan_master` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pekerjaan_pangkat`
--

DROP TABLE IF EXISTS `pekerjaan_pangkat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pekerjaan_pangkat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pekerjaan_master_id` int(11) DEFAULT NULL,
  `nama_pangkat` varchar(100) NOT NULL,
  `level` int(11) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `pekerjaan_master_id` (`pekerjaan_master_id`),
  CONSTRAINT `pekerjaan_pangkat_ibfk_1` FOREIGN KEY (`pekerjaan_master_id`) REFERENCES `pekerjaan_master` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pekerjaan_pangkat`
--

LOCK TABLES `pekerjaan_pangkat` WRITE;
/*!40000 ALTER TABLE `pekerjaan_pangkat` DISABLE KEYS */;
INSERT INTO `pekerjaan_pangkat` VALUES (1,1,'I/a',1,'Pangkat I/a','2026-02-08 14:27:53'),(2,1,'I/b',2,'Pangkat I/b','2026-02-08 14:27:53'),(3,1,'I/c',3,'Pangkat I/c','2026-02-08 14:27:53'),(4,1,'I/d',4,'Pangkat I/d','2026-02-08 14:27:53'),(5,1,'II/a',5,'Pangkat II/a','2026-02-08 14:27:53'),(6,1,'II/b',6,'Pangkat II/b','2026-02-08 14:27:53'),(7,1,'II/c',7,'Pangkat II/c','2026-02-08 14:27:53'),(8,1,'II/d',8,'Pangkat II/d','2026-02-08 14:27:53'),(9,1,'III/a',9,'Pangkat III/a','2026-02-08 14:27:53'),(10,1,'III/b',10,'Pangkat III/b','2026-02-08 14:27:53'),(11,1,'III/c',11,'Pangkat III/c','2026-02-08 14:27:53'),(12,1,'III/d',12,'Pangkat III/d','2026-02-08 14:27:53'),(13,1,'IV/a',13,'Pangkat IV/a','2026-02-08 14:27:53'),(14,1,'IV/b',14,'Pangkat IV/b','2026-02-08 14:27:53'),(15,1,'IV/c',15,'Pangkat IV/c','2026-02-08 14:27:53'),(16,1,'IV/d',16,'Pangkat IV/d','2026-02-08 14:27:53'),(17,2,'Staff',1,'Staff','2026-02-08 14:27:53'),(18,2,'Supervisor',2,'Supervisor','2026-02-08 14:27:53'),(19,2,'Manager',3,'Manager','2026-02-08 14:27:53'),(20,2,'Direktur',4,'Direktur','2026-02-08 14:27:53'),(21,3,'Pemilik Usaha',1,'Pemilik Usaha Kecil','2026-02-08 14:27:53'),(22,3,'Entrepreneur',2,'Wiraswasta','2026-02-08 14:27:53'),(23,6,'Prada',1,'Prada','2026-02-08 14:27:53'),(24,6,'Pratu',2,'Pratu','2026-02-08 14:27:53'),(25,6,'Praka',3,'Praka','2026-02-08 14:27:53'),(26,6,'Kopda',4,'Kopda','2026-02-08 14:27:53'),(27,6,'Koptu',5,'Koptu','2026-02-08 14:27:53'),(28,6,'Kopka',6,'Kopka','2026-02-08 14:27:53'),(29,6,'Serma',7,'Serma','2026-02-08 14:27:53'),(30,6,'Serka',8,'Serka','2026-02-08 14:27:53'),(31,6,'Sertu',9,'Sertu','2026-02-08 14:27:53'),(32,6,'Serda',10,'Serda','2026-02-08 14:27:53');
/*!40000 ALTER TABLE `pekerjaan_pangkat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pemesanan_konsumsi`
--

DROP TABLE IF EXISTS `pemesanan_konsumsi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pemesanan_konsumsi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `koperasi_id` int(11) NOT NULL,
  `anggota_id` int(11) NOT NULL,
  `tanggal_pesan` date NOT NULL,
  `tanggal_kirim` date DEFAULT NULL,
  `status` enum('draft','diproses','dikirim','selesai','batal') DEFAULT 'draft',
  `total` decimal(15,2) NOT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `koperasi_id` (`koperasi_id`),
  KEY `anggota_id` (`anggota_id`),
  CONSTRAINT `pemesanan_konsumsi_ibfk_1` FOREIGN KEY (`koperasi_id`) REFERENCES `koperasi_tenant` (`id`),
  CONSTRAINT `pemesanan_konsumsi_ibfk_2` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pemesanan_konsumsi`
--

LOCK TABLES `pemesanan_konsumsi` WRITE;
/*!40000 ALTER TABLE `pemesanan_konsumsi` DISABLE KEYS */;
/*!40000 ALTER TABLE `pemesanan_konsumsi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengawas`
--

DROP TABLE IF EXISTS `pengawas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengawas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pengguna_id` int(11) NOT NULL,
  `jabatan` varchar(100) NOT NULL,
  `periode_start` date NOT NULL,
  `periode_end` date DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pengawas_user` (`pengguna_id`),
  CONSTRAINT `pengawas_fk_pengguna` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE
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
-- Table structure for table `pengguna`
--

DROP TABLE IF EXISTS `pengguna`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengguna` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `sandi_hash` varchar(255) NOT NULL,
  `sumber_pengguna_id` int(11) NOT NULL,
  `cooperative_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive','pending') DEFAULT 'active',
  `dibuat_pada` timestamp NULL DEFAULT current_timestamp(),
  `diperbarui_pada` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `hp` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `idx_users_user_db_id` (`sumber_pengguna_id`),
  KEY `idx_pengguna_cooperative` (`cooperative_id`),
  CONSTRAINT `fk_pengguna_cooperative` FOREIGN KEY (`cooperative_id`) REFERENCES `koperasi_tenant` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengguna`
--

LOCK TABLES `pengguna` WRITE;
/*!40000 ALTER TABLE `pengguna` DISABLE KEYS */;
INSERT INTO `pengguna` VALUES (10,'root','$2y$10$mr69A.e7sEpZN2CAvGPgYu2wUWSJZXgbgSx.f9pB/Bk4/PxrsjkRS',1,NULL,'active','2026-02-08 11:52:06','2026-02-08 11:52:06','6281265511982');
/*!40000 ALTER TABLE `pengguna` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengguna_izin_peran`
--

DROP TABLE IF EXISTS `pengguna_izin_peran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengguna_izin_peran` (
  `peran_jenis_id` int(11) NOT NULL,
  `izin_modul_id` int(11) NOT NULL,
  `assigned_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`peran_jenis_id`,`izin_modul_id`),
  KEY `permission_id` (`izin_modul_id`),
  CONSTRAINT `pengguna_izin_peran_fk_izin` FOREIGN KEY (`izin_modul_id`) REFERENCES `izin_modul` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pengguna_izin_peran_fk_peran` FOREIGN KEY (`peran_jenis_id`) REFERENCES `peran_jenis` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengguna_izin_peran`
--

LOCK TABLES `pengguna_izin_peran` WRITE;
/*!40000 ALTER TABLE `pengguna_izin_peran` DISABLE KEYS */;
INSERT INTO `pengguna_izin_peran` VALUES (1,1,'2026-02-09 03:21:49'),(1,2,'2026-02-09 03:21:49'),(1,3,'2026-02-09 03:21:49'),(1,4,'2026-02-09 03:21:49'),(1,5,'2026-02-09 03:21:49'),(1,6,'2026-02-09 03:21:49'),(1,7,'2026-02-09 03:21:49'),(1,8,'2026-02-09 03:21:49'),(1,9,'2026-02-09 03:21:49'),(1,10,'2026-02-09 03:21:49'),(1,11,'2026-02-09 03:21:49'),(1,12,'2026-02-09 03:21:49'),(1,13,'2026-02-09 03:21:49'),(1,14,'2026-02-09 03:21:49'),(1,15,'2026-02-09 03:21:49'),(1,16,'2026-02-09 03:21:49'),(1,17,'2026-02-09 03:21:49'),(1,18,'2026-02-09 03:21:49'),(2,1,'2026-02-08 11:52:06'),(2,2,'2026-02-08 11:52:06'),(2,3,'2026-02-08 11:52:06'),(2,4,'2026-02-08 11:52:06'),(2,5,'2026-02-08 11:52:06'),(2,6,'2026-02-08 11:52:06'),(2,7,'2026-02-08 11:52:06'),(2,8,'2026-02-08 11:52:06'),(2,9,'2026-02-08 11:52:06'),(2,10,'2026-02-08 11:52:06'),(2,11,'2026-02-08 11:52:06'),(2,12,'2026-02-08 11:52:06'),(2,13,'2026-02-08 11:52:06'),(2,14,'2026-02-08 11:52:06'),(2,15,'2026-02-08 11:52:06'),(2,16,'2026-02-08 11:52:06'),(2,17,'2026-02-08 11:52:06'),(2,18,'2026-02-08 11:52:06');
/*!40000 ALTER TABLE `pengguna_izin_peran` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengguna_peran`
--

DROP TABLE IF EXISTS `pengguna_peran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengguna_peran` (
  `pengguna_id` int(11) NOT NULL,
  `peran_jenis_id` int(11) NOT NULL,
  `assigned_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`pengguna_id`,`peran_jenis_id`),
  KEY `role_id` (`peran_jenis_id`),
  CONSTRAINT `pengguna_peran_fk_pengguna` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pengguna_peran_fk_peran` FOREIGN KEY (`peran_jenis_id`) REFERENCES `peran_jenis` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengguna_peran`
--

LOCK TABLES `pengguna_peran` WRITE;
/*!40000 ALTER TABLE `pengguna_peran` DISABLE KEYS */;
INSERT INTO `pengguna_peran` VALUES (10,1,'2026-02-08 11:52:06');
/*!40000 ALTER TABLE `pengguna_peran` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengurus`
--

DROP TABLE IF EXISTS `pengurus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengurus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pengguna_id` int(11) NOT NULL,
  `jabatan` varchar(100) NOT NULL,
  `periode_start` date NOT NULL,
  `periode_end` date DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pengurus_user` (`pengguna_id`),
  CONSTRAINT `pengurus_fk_pengguna` FOREIGN KEY (`pengguna_id`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE
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
-- Table structure for table `penjadwalan`
--

DROP TABLE IF EXISTS `penjadwalan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `penjadwalan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `koperasi_id` int(11) NOT NULL,
  `layanan_id` int(11) NOT NULL,
  `anggota_id` int(11) NOT NULL,
  `penyedia_id` int(11) DEFAULT NULL COMMENT 'Penyedia jasa (jika ada)',
  `tanggal` date NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `status` enum('draft','dipesan','diproses','selesai','batal') DEFAULT 'draft',
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `koperasi_id` (`koperasi_id`),
  KEY `layanan_id` (`layanan_id`),
  KEY `anggota_id` (`anggota_id`),
  CONSTRAINT `penjadwalan_ibfk_1` FOREIGN KEY (`koperasi_id`) REFERENCES `koperasi_tenant` (`id`),
  CONSTRAINT `penjadwalan_ibfk_2` FOREIGN KEY (`layanan_id`) REFERENCES `layanan` (`id`),
  CONSTRAINT `penjadwalan_ibfk_3` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penjadwalan`
--

LOCK TABLES `penjadwalan` WRITE;
/*!40000 ALTER TABLE `penjadwalan` DISABLE KEYS */;
/*!40000 ALTER TABLE `penjadwalan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `penjualan_agen`
--

DROP TABLE IF EXISTS `penjualan_agen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `penjualan_agen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agent_id` int(11) NOT NULL,
  `pesanan_id` int(11) NOT NULL,
  `commission` decimal(15,2) NOT NULL,
  `approved` tinyint(1) DEFAULT 0,
  `pengguna_disetujui_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`pesanan_id`),
  KEY `agent_id` (`agent_id`),
  KEY `approved_by` (`pengguna_disetujui_id`),
  CONSTRAINT `penjualan_agen_fk_pengguna_approve` FOREIGN KEY (`pengguna_disetujui_id`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL,
  CONSTRAINT `penjualan_agen_fk_pesanan` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`),
  CONSTRAINT `penjualan_agen_ibfk_1` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`),
  CONSTRAINT `penjualan_agen_ibfk_2` FOREIGN KEY (`agent_id`) REFERENCES `anggota` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penjualan_agen`
--

LOCK TABLES `penjualan_agen` WRITE;
/*!40000 ALTER TABLE `penjualan_agen` DISABLE KEYS */;
/*!40000 ALTER TABLE `penjualan_agen` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `peran_izin`
--

DROP TABLE IF EXISTS `peran_izin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `peran_izin` (
  `peran_jenis_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`peran_jenis_id`,`permission_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `peran_izin_ibfk_1` FOREIGN KEY (`peran_jenis_id`) REFERENCES `peran_jenis` (`id`) ON DELETE CASCADE,
  CONSTRAINT `peran_izin_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `peran_izin`
--

LOCK TABLES `peran_izin` WRITE;
/*!40000 ALTER TABLE `peran_izin` DISABLE KEYS */;
INSERT INTO `peran_izin` VALUES (1,1,'2026-02-07 18:25:07'),(1,2,'2026-02-07 18:25:07'),(1,3,'2026-02-07 18:25:07'),(1,4,'2026-02-07 18:25:07'),(2,1,'2026-02-07 18:25:07'),(2,2,'2026-02-07 18:25:07'),(2,3,'2026-02-07 18:25:07');
/*!40000 ALTER TABLE `peran_izin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `peran_jenis`
--

DROP TABLE IF EXISTS `peran_jenis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `peran_jenis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `peran_jenis`
--

LOCK TABLES `peran_jenis` WRITE;
/*!40000 ALTER TABLE `peran_jenis` DISABLE KEYS */;
INSERT INTO `peran_jenis` VALUES (1,'super_admin','Super administrator with all access','2026-02-03 14:13:20'),(2,'admin','Administrator/Pengurus','2026-02-03 14:13:20'),(3,'pengawas','Pengawas with read/approve access','2026-02-03 14:13:20'),(4,'anggota','Regular member','2026-02-03 14:13:20'),(5,'calon_anggota','Prospective member','2026-02-03 14:13:20');
/*!40000 ALTER TABLE `peran_jenis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permission_key` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `permission_key` (`permission_key`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'manage_cooperative','Manage cooperative details and settings','2026-02-07 18:25:07'),(2,'manage_members','Manage cooperative members','2026-02-07 18:25:07'),(3,'view_reports','View financial reports','2026-02-07 18:25:07'),(4,'approve_loans','Approve loan applications','2026-02-07 18:25:07');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pesanan`
--

DROP TABLE IF EXISTS `pesanan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pesanan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pengguna_id` int(11) DEFAULT NULL,
  `dibuat_pada` timestamp NULL DEFAULT current_timestamp(),
  `total` decimal(15,2) NOT NULL,
  `status` enum('pending','confirmed','shipped','delivered','cancelled') DEFAULT 'pending',
  `alamat_pengiriman` text DEFAULT NULL,
  `status_pembayaran` enum('unpaid','paid','refunded') DEFAULT 'unpaid',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pesanan`
--

LOCK TABLES `pesanan` WRITE;
/*!40000 ALTER TABLE `pesanan` DISABLE KEYS */;
/*!40000 ALTER TABLE `pesanan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pesanan_detail`
--

DROP TABLE IF EXISTS `pesanan_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pesanan_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pesanan_id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `kuantitas` int(11) NOT NULL,
  `harga_satuan` decimal(15,2) NOT NULL,
  `total` decimal(15,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`pesanan_id`),
  KEY `product_id` (`produk_id`),
  CONSTRAINT `pesanan_detail_ibfk_1` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`),
  CONSTRAINT `pesanan_detail_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pesanan_detail`
--

LOCK TABLES `pesanan_detail` WRITE;
/*!40000 ALTER TABLE `pesanan_detail` DISABLE KEYS */;
/*!40000 ALTER TABLE `pesanan_detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pestisida`
--

DROP TABLE IF EXISTS `pestisida`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pestisida` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pesticide_code` varchar(50) NOT NULL,
  `pesticide_name` varchar(255) NOT NULL,
  `pesticide_type` enum('insektisida','fungisida','herbisida','rodentisida','akarisida','nematisida','moluskisida') NOT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `active_ingredient` varchar(255) DEFAULT NULL,
  `concentration` varchar(50) DEFAULT NULL,
  `form` enum('cair','padat','bubuk','granul','emulsi') DEFAULT NULL,
  `unit` varchar(20) NOT NULL DEFAULT 'ml',
  `current_stock` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `min_stock` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `unit_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `supplier` varchar(255) DEFAULT NULL,
  `storage_location` varchar(255) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `application_method` text DEFAULT NULL,
  `dosage_recommendation` text DEFAULT NULL,
  `pre_harvest_interval` int(11) DEFAULT NULL,
  `toxicity_level` enum('sangat_rendah','rendah','sedang','tinggi','sangat_tinggi') DEFAULT NULL,
  `is_restricted` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `unit_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_pesticide_code` (`pesticide_code`),
  KEY `idx_pesticide_type` (`pesticide_type`),
  KEY `idx_brand` (`brand`),
  KEY `idx_active` (`is_active`),
  KEY `idx_unit` (`unit_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pestisida`
--

LOCK TABLES `pestisida` WRITE;
/*!40000 ALTER TABLE `pestisida` DISABLE KEYS */;
INSERT INTO `pestisida` VALUES (1,'PES001','Curacron','insektisida','BASF','Profenofos','500 g/L',NULL,'ml',0.0000,0.0000,15000.00,NULL,NULL,NULL,NULL,NULL,NULL,'sedang',0,1,1,1,'2026-02-12 17:56:59','2026-02-12 17:56:59'),(2,'PES002','Furadan','insektisida','FMC','Carbofuran','3% GR',NULL,'kg',0.0000,0.0000,25000.00,NULL,NULL,NULL,NULL,NULL,NULL,'tinggi',0,1,1,1,'2026-02-12 17:56:59','2026-02-12 17:56:59'),(3,'PES003','Dithane','fungisida','Dow','Mancozeb','80% WP',NULL,'kg',0.0000,0.0000,35000.00,NULL,NULL,NULL,NULL,NULL,NULL,'rendah',0,1,1,1,'2026-02-12 17:56:59','2026-02-12 17:56:59'),(4,'PES004','Roundup','herbisida','Monsanto','Glyphosate','486 g/L',NULL,'ml',0.0000,0.0000,12000.00,NULL,NULL,NULL,NULL,NULL,NULL,'sedang',0,1,1,1,'2026-02-12 17:56:59','2026-02-12 17:56:59'),(5,'PES005','Regent','insektisida','BASF','Fipronil','0.5% GR',NULL,'kg',0.0000,0.0000,45000.00,NULL,NULL,NULL,NULL,NULL,NULL,'tinggi',0,1,1,1,'2026-02-12 17:56:59','2026-02-12 17:56:59');
/*!40000 ALTER TABLE `pestisida` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pestisida_application`
--

DROP TABLE IF EXISTS `pestisida_application`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pestisida_application` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `application_code` varchar(50) NOT NULL,
  `land_id` int(11) NOT NULL,
  `planting_id` int(11) DEFAULT NULL,
  `pesticide_id` int(11) NOT NULL,
  `application_date` date NOT NULL,
  `amount` decimal(10,4) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `method` enum('semprot','kocor','titik','buburan','fumigasi') DEFAULT NULL,
  `pest_type` varchar(255) DEFAULT NULL,
  `infestation_level` enum('ringan','sedang','berat','sangat_berat') DEFAULT NULL,
  `weather_condition` varchar(100) DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('planned','applied','cancelled') NOT NULL DEFAULT 'planned',
  `applied_by` int(11) DEFAULT NULL,
  `applied_at` timestamp NULL DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_pesticide_application` (`application_code`),
  KEY `idx_land` (`land_id`),
  KEY `idx_planting` (`planting_id`),
  KEY `idx_pesticide` (`pesticide_id`),
  KEY `idx_application_date` (`application_date`),
  KEY `idx_status` (`status`),
  KEY `idx_unit` (`unit_id`),
  CONSTRAINT `fk_pesticide_app_land` FOREIGN KEY (`land_id`) REFERENCES `lahan` (`id`),
  CONSTRAINT `fk_pesticide_app_pesticide` FOREIGN KEY (`pesticide_id`) REFERENCES `pestisida` (`id`),
  CONSTRAINT `fk_pesticide_app_planting` FOREIGN KEY (`planting_id`) REFERENCES `tanam_planting` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pestisida_application`
--

LOCK TABLES `pestisida_application` WRITE;
/*!40000 ALTER TABLE `pestisida_application` DISABLE KEYS */;
/*!40000 ALTER TABLE `pestisida_application` ENABLE KEYS */;
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
  `pengguna_disetujui_id` int(11) DEFAULT NULL,
  `disbursed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `approved_by` (`pengguna_disetujui_id`),
  KEY `idx_pinjaman_anggota` (`anggota_id`),
  KEY `idx_pinjaman_status` (`status`),
  CONSTRAINT `pinjaman_fk_pengguna_approve` FOREIGN KEY (`pengguna_disetujui_id`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pinjaman_ibfk_1` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`)
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
  `angsuran_ke` int(11) NOT NULL,
  `tanggal_jatuh_tempo` date NOT NULL,
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
-- Table structure for table `planting_history`
--

DROP TABLE IF EXISTS `planting_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `planting_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `land_id` int(11) NOT NULL,
  `planting_id` int(11) DEFAULT NULL,
  `plant_name` varchar(255) NOT NULL,
  `plant_variety` varchar(255) DEFAULT NULL,
  `plant_category` varchar(50) DEFAULT NULL,
  `planting_date` date NOT NULL,
  `harvest_date` date DEFAULT NULL,
  `status` enum('harvested','failed','abandoned') NOT NULL,
  `yield_amount` decimal(15,4) DEFAULT NULL,
  `yield_unit` varchar(20) DEFAULT NULL,
  `quality_grade` varchar(20) DEFAULT NULL,
  `market_price` decimal(15,2) DEFAULT NULL,
  `total_revenue` decimal(15,2) DEFAULT NULL,
  `production_cost` decimal(15,2) DEFAULT NULL,
  `profit_loss` decimal(15,2) DEFAULT NULL,
  `failure_reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_land` (`land_id`),
  KEY `idx_planting` (`planting_id`),
  KEY `idx_plant_name` (`plant_name`),
  KEY `idx_planting_date` (`planting_date`),
  KEY `idx_status` (`status`),
  KEY `idx_unit` (`unit_id`),
  CONSTRAINT `fk_history_land` FOREIGN KEY (`land_id`) REFERENCES `lahan` (`id`),
  CONSTRAINT `fk_history_planting` FOREIGN KEY (`planting_id`) REFERENCES `tanam_planting` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `planting_history`
--

LOCK TABLES `planting_history` WRITE;
/*!40000 ALTER TABLE `planting_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `planting_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `produk`
--

DROP TABLE IF EXISTS `produk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `produk` (
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
-- Dumping data for table `produk`
--

LOCK TABLES `produk` WRITE;
/*!40000 ALTER TABLE `produk` DISABLE KEYS */;
/*!40000 ALTER TABLE `produk` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `produk_konsumsi`
--

DROP TABLE IF EXISTS `produk_konsumsi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `produk_konsumsi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `koperasi_id` int(11) NOT NULL,
  `kode_produk` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kategori` varchar(50) NOT NULL,
  `satuan` varchar(20) NOT NULL,
  `harga` decimal(15,2) NOT NULL,
  `stok` decimal(10,2) DEFAULT 0.00,
  `stok_minimum` decimal(10,2) DEFAULT 0.00,
  `supplier` varchar(100) DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `koperasi_id` (`koperasi_id`,`kode_produk`),
  CONSTRAINT `produk_konsumsi_ibfk_1` FOREIGN KEY (`koperasi_id`) REFERENCES `koperasi_tenant` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produk_konsumsi`
--

LOCK TABLES `produk_konsumsi` WRITE;
/*!40000 ALTER TABLE `produk_konsumsi` DISABLE KEYS */;
/*!40000 ALTER TABLE `produk_konsumsi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `produksi_bahan_baku`
--

DROP TABLE IF EXISTS `produksi_bahan_baku`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `produksi_bahan_baku` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `proses_id` int(11) NOT NULL,
  `bahan_baku_id` int(11) NOT NULL,
  `jumlah` decimal(10,2) NOT NULL,
  `catatan` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `proses_id` (`proses_id`),
  KEY `bahan_baku_id` (`bahan_baku_id`),
  CONSTRAINT `produksi_bahan_baku_ibfk_1` FOREIGN KEY (`proses_id`) REFERENCES `proses_produksi` (`id`),
  CONSTRAINT `produksi_bahan_baku_ibfk_2` FOREIGN KEY (`bahan_baku_id`) REFERENCES `bahan_baku` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produksi_bahan_baku`
--

LOCK TABLES `produksi_bahan_baku` WRITE;
/*!40000 ALTER TABLE `produksi_bahan_baku` DISABLE KEYS */;
/*!40000 ALTER TABLE `produksi_bahan_baku` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proses_produksi`
--

DROP TABLE IF EXISTS `proses_produksi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `proses_produksi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `koperasi_id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `nama_proses` varchar(100) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `status` enum('draft','berjalan','selesai','batal') DEFAULT 'draft',
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `koperasi_id` (`koperasi_id`),
  KEY `produk_id` (`produk_id`),
  CONSTRAINT `proses_produksi_ibfk_1` FOREIGN KEY (`koperasi_id`) REFERENCES `koperasi_tenant` (`id`),
  CONSTRAINT `proses_produksi_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proses_produksi`
--

LOCK TABLES `proses_produksi` WRITE;
/*!40000 ALTER TABLE `proses_produksi` DISABLE KEYS */;
/*!40000 ALTER TABLE `proses_produksi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pupuk`
--

DROP TABLE IF EXISTS `pupuk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pupuk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fertilizer_code` varchar(50) NOT NULL,
  `fertilizer_name` varchar(255) NOT NULL,
  `fertilizer_type` enum('organik','anorganik','bio_fertilizer','kompos','pupuk_kandang','pupuk_hijau') NOT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `npk_ratio` varchar(20) DEFAULT NULL,
  `nutrient_content` text DEFAULT NULL,
  `form` enum('cair','padat','granul','bubuk','pellet') DEFAULT NULL,
  `unit` varchar(20) NOT NULL DEFAULT 'kg',
  `current_stock` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `min_stock` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `unit_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `supplier` varchar(255) DEFAULT NULL,
  `storage_location` varchar(255) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `application_method` text DEFAULT NULL,
  `dosage_recommendation` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `unit_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_fertilizer_code` (`fertilizer_code`),
  KEY `idx_fertilizer_type` (`fertilizer_type`),
  KEY `idx_brand` (`brand`),
  KEY `idx_active` (`is_active`),
  KEY `idx_unit` (`unit_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pupuk`
--

LOCK TABLES `pupuk` WRITE;
/*!40000 ALTER TABLE `pupuk` DISABLE KEYS */;
INSERT INTO `pupuk` VALUES (1,'PUP001','Urea','anorganik','Petrokimia','46-0-0',NULL,NULL,'kg',0.0000,0.0000,5000.00,NULL,NULL,NULL,NULL,NULL,1,1,1,'2026-02-12 17:56:59','2026-02-12 17:56:59'),(2,'PUP002','SP-36','anorganik','Petrokimia','0-36-0',NULL,NULL,'kg',0.0000,0.0000,8000.00,NULL,NULL,NULL,NULL,NULL,1,1,1,'2026-02-12 17:56:59','2026-02-12 17:56:59'),(3,'PUP003','KCL','anorganik','Petrokimia','0-0-60',NULL,NULL,'kg',0.0000,0.0000,7000.00,NULL,NULL,NULL,NULL,NULL,1,1,1,'2026-02-12 17:56:59','2026-02-12 17:56:59'),(4,'PUP004','ZA','anorganik','Petrokimia','15-0-14',NULL,NULL,'kg',0.0000,0.0000,4500.00,NULL,NULL,NULL,NULL,NULL,1,1,1,'2026-02-12 17:56:59','2026-02-12 17:56:59'),(5,'PUP005','Kompos','organik',NULL,NULL,NULL,NULL,'kg',0.0000,0.0000,2000.00,NULL,NULL,NULL,NULL,NULL,1,1,1,'2026-02-12 17:56:59','2026-02-12 17:56:59'),(6,'PUP006','Pupuk Kandang','organik',NULL,NULL,NULL,NULL,'kg',0.0000,0.0000,1500.00,NULL,NULL,NULL,NULL,NULL,1,1,1,'2026-02-12 17:56:59','2026-02-12 17:56:59');
/*!40000 ALTER TABLE `pupuk` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pupuk_application`
--

DROP TABLE IF EXISTS `pupuk_application`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pupuk_application` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `application_code` varchar(50) NOT NULL,
  `land_id` int(11) NOT NULL,
  `planting_id` int(11) DEFAULT NULL,
  `fertilizer_id` int(11) NOT NULL,
  `application_date` date NOT NULL,
  `amount` decimal(10,4) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `method` enum('tanah','daun','semprot','kocor','titik') DEFAULT NULL,
  `growth_stage` varchar(50) DEFAULT NULL,
  `weather_condition` varchar(100) DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('planned','applied','cancelled') NOT NULL DEFAULT 'planned',
  `applied_by` int(11) DEFAULT NULL,
  `applied_at` timestamp NULL DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_fertilizer_application` (`application_code`),
  KEY `idx_land` (`land_id`),
  KEY `idx_planting` (`planting_id`),
  KEY `idx_fertilizer` (`fertilizer_id`),
  KEY `idx_application_date` (`application_date`),
  KEY `idx_status` (`status`),
  KEY `idx_unit` (`unit_id`),
  CONSTRAINT `fk_fertilizer_app_fertilizer` FOREIGN KEY (`fertilizer_id`) REFERENCES `pupuk` (`id`),
  CONSTRAINT `fk_fertilizer_app_land` FOREIGN KEY (`land_id`) REFERENCES `lahan` (`id`),
  CONSTRAINT `fk_fertilizer_app_planting` FOREIGN KEY (`planting_id`) REFERENCES `tanam_planting` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pupuk_application`
--

LOCK TABLES `pupuk_application` WRITE;
/*!40000 ALTER TABLE `pupuk_application` DISABLE KEYS */;
/*!40000 ALTER TABLE `pupuk_application` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qc_categories`
--

DROP TABLE IF EXISTS `qc_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `qc_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_parent` (`parent_id`),
  KEY `idx_unit` (`unit_id`),
  KEY `idx_active` (`is_active`),
  CONSTRAINT `fk_qc_category_parent` FOREIGN KEY (`parent_id`) REFERENCES `qc_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qc_categories`
--

LOCK TABLES `qc_categories` WRITE;
/*!40000 ALTER TABLE `qc_categories` DISABLE KEYS */;
INSERT INTO `qc_categories` VALUES (1,'Physical Properties','Properti fisik produk',NULL,NULL,1,'2026-02-12 17:54:56','2026-02-12 17:54:56'),(2,'Chemical Properties','Properti kimia produk',NULL,NULL,1,'2026-02-12 17:54:56','2026-02-12 17:54:56'),(3,'Microbiological','Uji mikrobiologi',NULL,NULL,1,'2026-02-12 17:54:56','2026-02-12 17:54:56'),(4,'Sensory','Uji organoleptik/indra',NULL,NULL,1,'2026-02-12 17:54:56','2026-02-12 17:54:56'),(5,'Packaging','Kemasan dan label',NULL,NULL,1,'2026-02-12 17:54:56','2026-02-12 17:54:56'),(6,'Safety','Parameter keamanan',NULL,NULL,1,'2026-02-12 17:54:56','2026-02-12 17:54:56');
/*!40000 ALTER TABLE `qc_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qc_inspections`
--

DROP TABLE IF EXISTS `qc_inspections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `qc_inspections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inspection_code` varchar(50) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `batch_number` varchar(100) DEFAULT NULL,
  `production_date` date DEFAULT NULL,
  `inspection_date` date NOT NULL,
  `inspector_id` int(11) NOT NULL,
  `inspector_name` varchar(255) NOT NULL,
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `status` enum('pending','in_progress','approved','rejected') NOT NULL DEFAULT 'pending',
  `pass_rate` decimal(5,2) DEFAULT 0.00,
  `total_tests` int(11) DEFAULT 0,
  `passed_tests` int(11) DEFAULT 0,
  `failed_tests` int(11) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_inspection_code` (`inspection_code`),
  KEY `idx_product` (`product_id`),
  KEY `idx_inspector` (`inspector_id`),
  KEY `idx_status` (`status`),
  KEY `idx_priority` (`priority`),
  KEY `idx_inspection_date` (`inspection_date`),
  KEY `idx_unit` (`unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qc_inspections`
--

LOCK TABLES `qc_inspections` WRITE;
/*!40000 ALTER TABLE `qc_inspections` DISABLE KEYS */;
/*!40000 ALTER TABLE `qc_inspections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qc_parameters`
--

DROP TABLE IF EXISTS `qc_parameters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `qc_parameters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parameter_code` varchar(50) NOT NULL,
  `parameter_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `type` enum('numeric','text','boolean','date') NOT NULL DEFAULT 'numeric',
  `min_value` decimal(15,4) DEFAULT NULL,
  `max_value` decimal(15,4) DEFAULT NULL,
  `standard_value` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_parameter_code` (`parameter_code`,`unit_id`),
  KEY `idx_category` (`category_id`),
  KEY `idx_unit` (`unit_id`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qc_parameters`
--

LOCK TABLES `qc_parameters` WRITE;
/*!40000 ALTER TABLE `qc_parameters` DISABLE KEYS */;
INSERT INTO `qc_parameters` VALUES (1,'PHYS001','Color','Warna produk','-','text',NULL,NULL,NULL,1,1,1,1,'2026-02-12 17:54:56','2026-02-12 17:54:56'),(2,'PHYS002','Odor','Bau produk','-','text',NULL,NULL,NULL,1,1,1,1,'2026-02-12 17:54:56','2026-02-12 17:54:56'),(3,'PHYS003','Texture','Tekstur produk','-','text',NULL,NULL,NULL,1,1,1,1,'2026-02-12 17:54:56','2026-02-12 17:54:56'),(4,'PHYS004','Appearance','Penampilan visual','-','text',NULL,NULL,NULL,1,1,1,1,'2026-02-12 17:54:56','2026-02-12 17:54:56'),(5,'CHEM001','pH','Tingkat keasaman','pH','numeric',3.0000,9.0000,NULL,2,1,1,1,'2026-02-12 17:54:56','2026-02-12 17:54:56'),(6,'CHEM002','Moisture Content','Kadar air','%','numeric',0.0000,100.0000,NULL,2,1,1,1,'2026-02-12 17:54:56','2026-02-12 17:54:56'),(7,'CHEM003','Ash Content','Kadar abu','%','numeric',0.0000,100.0000,NULL,2,1,1,1,'2026-02-12 17:54:56','2026-02-12 17:54:56'),(8,'CHEM004','Protein','Kadar protein','%','numeric',0.0000,100.0000,NULL,2,1,1,1,'2026-02-12 17:54:56','2026-02-12 17:54:56'),(9,'CHEM005','Fat','Kadar lemak','%','numeric',0.0000,100.0000,NULL,2,1,1,1,'2026-02-12 17:54:56','2026-02-12 17:54:56'),(10,'CHEM006','Carbohydrate','Kadar karbohidrat','%','numeric',0.0000,100.0000,NULL,2,1,1,1,'2026-02-12 17:54:56','2026-02-12 17:54:56'),(11,'MICR001','Total Plate Count','Jumlah total mikroba','CFU/g','numeric',0.0000,100000.0000,NULL,3,1,1,1,'2026-02-12 17:54:56','2026-02-12 17:54:56'),(12,'MICR002','E. Coli','Kontaminasi E. Coli','CFU/g','numeric',0.0000,100.0000,NULL,3,1,1,1,'2026-02-12 17:54:56','2026-02-12 17:54:56'),(13,'MICR003','Salmonella','Kontaminasi Salmonella','-','boolean',NULL,NULL,NULL,3,1,1,1,'2026-02-12 17:54:56','2026-02-12 17:54:56'),(14,'MICR004','Yeast & Mold','Khamir dan kapang','CFU/g','numeric',0.0000,1000.0000,NULL,3,1,1,1,'2026-02-12 17:54:56','2026-02-12 17:54:56'),(15,'SENS001','Taste','Rasa produk','-','text',NULL,NULL,NULL,4,1,1,1,'2026-02-12 17:54:56','2026-02-12 17:54:56'),(16,'SENS002','Aroma','Aroma produk','-','text',NULL,NULL,NULL,4,1,1,1,'2026-02-12 17:54:56','2026-02-12 17:54:56'),(17,'PACK001','Seal Integrity','Integritas segel','-','boolean',NULL,NULL,NULL,5,1,1,1,'2026-02-12 17:54:56','2026-02-12 17:54:56'),(18,'PACK002','Label Information','Informasi label','-','text',NULL,NULL,NULL,5,1,1,1,'2026-02-12 17:54:56','2026-02-12 17:54:56'),(19,'PACK003','Package Condition','Kondisi kemasan','-','text',NULL,NULL,NULL,5,1,1,1,'2026-02-12 17:54:56','2026-02-12 17:54:56'),(20,'SAFE001','Heavy Metals','Logam berat','ppm','numeric',0.0000,10.0000,NULL,6,1,1,1,'2026-02-12 17:54:56','2026-02-12 17:54:56'),(21,'SAFE002','Pesticide Residue','Sisa pestisida','ppm','numeric',0.0000,5.0000,NULL,6,1,1,1,'2026-02-12 17:54:56','2026-02-12 17:54:56');
/*!40000 ALTER TABLE `qc_parameters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qc_standard_parameters`
--

DROP TABLE IF EXISTS `qc_standard_parameters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `qc_standard_parameters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `standard_id` int(11) NOT NULL,
  `parameter_id` int(11) NOT NULL,
  `min_value` decimal(15,4) DEFAULT NULL,
  `max_value` decimal(15,4) DEFAULT NULL,
  `standard_value` varchar(255) DEFAULT NULL,
  `tolerance` decimal(5,2) DEFAULT 0.00,
  `is_required` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_standard_parameter` (`standard_id`,`parameter_id`),
  KEY `idx_standard` (`standard_id`),
  KEY `idx_parameter` (`parameter_id`),
  CONSTRAINT `fk_qc_sp_parameter` FOREIGN KEY (`parameter_id`) REFERENCES `qc_parameters` (`id`),
  CONSTRAINT `fk_qc_sp_standard` FOREIGN KEY (`standard_id`) REFERENCES `qc_standards` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qc_standard_parameters`
--

LOCK TABLES `qc_standard_parameters` WRITE;
/*!40000 ALTER TABLE `qc_standard_parameters` DISABLE KEYS */;
/*!40000 ALTER TABLE `qc_standard_parameters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qc_standards`
--

DROP TABLE IF EXISTS `qc_standards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `qc_standards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `standard_code` varchar(50) NOT NULL,
  `standard_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `version` varchar(20) DEFAULT NULL,
  `effective_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_standard_code` (`standard_code`),
  KEY `idx_category` (`category_id`),
  KEY `idx_unit` (`unit_id`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qc_standards`
--

LOCK TABLES `qc_standards` WRITE;
/*!40000 ALTER TABLE `qc_standards` DISABLE KEYS */;
/*!40000 ALTER TABLE `qc_standards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qc_test_results`
--

DROP TABLE IF EXISTS `qc_test_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `qc_test_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inspection_id` int(11) NOT NULL,
  `parameter_id` int(11) NOT NULL,
  `parameter_name` varchar(255) NOT NULL,
  `test_value` varchar(255) DEFAULT NULL,
  `numeric_value` decimal(15,4) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `min_value` decimal(15,4) DEFAULT NULL,
  `max_value` decimal(15,4) DEFAULT NULL,
  `standard_value` varchar(255) DEFAULT NULL,
  `status` enum('pass','fail','pending') NOT NULL DEFAULT 'pending',
  `deviation` decimal(15,4) DEFAULT NULL,
  `deviation_percentage` decimal(5,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `tested_by` int(11) NOT NULL,
  `tested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_inspection_parameter` (`inspection_id`,`parameter_id`),
  KEY `idx_inspection` (`inspection_id`),
  KEY `idx_parameter` (`parameter_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_qc_results_inspection` FOREIGN KEY (`inspection_id`) REFERENCES `qc_inspections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_qc_results_parameter` FOREIGN KEY (`parameter_id`) REFERENCES `qc_parameters` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qc_test_results`
--

LOCK TABLES `qc_test_results` WRITE;
/*!40000 ALTER TABLE `qc_test_results` DISABLE KEYS */;
/*!40000 ALTER TABLE `qc_test_results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rat_checklist`
--

DROP TABLE IF EXISTS `rat_checklist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rat_checklist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `koperasi_tenant_id` int(11) NOT NULL,
  `item` varchar(200) NOT NULL,
  `required` tinyint(1) DEFAULT 1,
  `status` enum('pending','done') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `order_no` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rat_checklist`
--

LOCK TABLES `rat_checklist` WRITE;
/*!40000 ALTER TABLE `rat_checklist` DISABLE KEYS */;
/*!40000 ALTER TABLE `rat_checklist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rat_sesi`
--

DROP TABLE IF EXISTS `rat_sesi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rat_sesi` (
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
  CONSTRAINT `rat_sesi_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `koperasi_tenant` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rat_sesi_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `pengguna` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rat_sesi`
--

LOCK TABLES `rat_sesi` WRITE;
/*!40000 ALTER TABLE `rat_sesi` DISABLE KEYS */;
/*!40000 ALTER TABLE `rat_sesi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `savings_types`
--

DROP TABLE IF EXISTS `savings_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `savings_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `interest_rate` decimal(8,4) DEFAULT 0.0000,
  `min_deposit` decimal(15,2) DEFAULT 0.00,
  `admin_fee` decimal(15,2) DEFAULT 0.00,
  `penalty_rate` decimal(8,4) DEFAULT 0.0000,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `lock_period_days` int(11) DEFAULT 0,
  `early_withdraw_fee` decimal(8,2) DEFAULT 0.00,
  `min_balance` decimal(15,2) DEFAULT 0.00,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `savings_types`
--

LOCK TABLES `savings_types` WRITE;
/*!40000 ALTER TABLE `savings_types` DISABLE KEYS */;
INSERT INTO `savings_types` VALUES (1,'Simpanan Pokok',0.0000,0.00,0.00,0.0000,'Simpanan wajib saat masuk anggota',1,'2026-02-08 17:59:38','2026-02-08 17:59:38',0,0.00,0.00),(2,'Simpanan Wajib',0.0000,50000.00,0.00,0.0000,'Setoran bulanan anggota',1,'2026-02-08 17:59:38','2026-02-08 17:59:38',0,0.00,0.00),(3,'Simpanan Sukarela',4.0000,50000.00,0.00,0.0000,'Sukarela, bisa ditarik, bunga ringan',1,'2026-02-08 17:59:38','2026-02-08 17:59:38',0,0.00,0.00);
/*!40000 ALTER TABLE `savings_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shu_anggota`
--

DROP TABLE IF EXISTS `shu_anggota`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `shu_anggota` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `anggota_id` int(11) NOT NULL,
  `shu_distribution_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `paid` tinyint(1) DEFAULT 0,
  `paid_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `anggota_id` (`anggota_id`),
  KEY `shu_distribution_id` (`shu_distribution_id`),
  CONSTRAINT `shu_anggota_ibfk_1` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`),
  CONSTRAINT `shu_anggota_ibfk_2` FOREIGN KEY (`shu_distribution_id`) REFERENCES `shu_distribusi` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shu_anggota`
--

LOCK TABLES `shu_anggota` WRITE;
/*!40000 ALTER TABLE `shu_anggota` DISABLE KEYS */;
/*!40000 ALTER TABLE `shu_anggota` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shu_distribusi`
--

DROP TABLE IF EXISTS `shu_distribusi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `shu_distribusi` (
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
-- Dumping data for table `shu_distribusi`
--

LOCK TABLES `shu_distribusi` WRITE;
/*!40000 ALTER TABLE `shu_distribusi` DISABLE KEYS */;
/*!40000 ALTER TABLE `shu_distribusi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `simpanan_jenis`
--

DROP TABLE IF EXISTS `simpanan_jenis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `simpanan_jenis` (
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
-- Dumping data for table `simpanan_jenis`
--

LOCK TABLES `simpanan_jenis` WRITE;
/*!40000 ALTER TABLE `simpanan_jenis` DISABLE KEYS */;
/*!40000 ALTER TABLE `simpanan_jenis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `simpanan_transaksi`
--

DROP TABLE IF EXISTS `simpanan_transaksi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `simpanan_transaksi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `anggota_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `nilai` decimal(15,2) NOT NULL,
  `transaction_type` enum('deposit','withdraw') NOT NULL,
  `balance_after` decimal(15,2) NOT NULL,
  `tanggal_transaksi` date DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `pengguna_disetujui_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `type_id` (`type_id`),
  KEY `approved_by` (`pengguna_disetujui_id`),
  KEY `idx_simpanan_anggota` (`anggota_id`),
  KEY `idx_simpanan_date` (`tanggal_transaksi`),
  CONSTRAINT `simpanan_transaksi_fk_pengguna_approve` FOREIGN KEY (`pengguna_disetujui_id`) REFERENCES `pengguna` (`id`) ON DELETE SET NULL,
  CONSTRAINT `simpanan_transaksi_fk_simpanan_jenis` FOREIGN KEY (`type_id`) REFERENCES `simpanan_jenis` (`id`) ON DELETE CASCADE,
  CONSTRAINT `simpanan_transaksi_ibfk_1` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `simpanan_transaksi`
--

LOCK TABLES `simpanan_transaksi` WRITE;
/*!40000 ALTER TABLE `simpanan_transaksi` DISABLE KEYS */;
/*!40000 ALTER TABLE `simpanan_transaksi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_code` varchar(50) NOT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `payment_terms` varchar(100) DEFAULT NULL,
  `credit_limit` decimal(15,2) DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `unit_id` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_supplier_code` (`supplier_code`),
  KEY `idx_unit` (`unit_id`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
INSERT INTO `suppliers` VALUES (1,'SUP001','Supplier Default 1','Contact Person 1','081234567890',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,1,NULL,1,'2026-02-12 17:54:58','2026-02-12 17:54:58'),(2,'SUP002','Supplier Default 2','Contact Person 2','081234567891',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,1,NULL,1,'2026-02-12 17:54:58','2026-02-12 17:54:58'),(3,'SUP003','Supplier Default 3','Contact Person 3','081234567892',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,1,NULL,1,'2026-02-12 17:54:58','2026-02-12 17:54:58');
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tanam_planting`
--

DROP TABLE IF EXISTS `tanam_planting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tanam_planting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `planting_code` varchar(50) NOT NULL,
  `land_id` int(11) NOT NULL,
  `plant_name` varchar(255) NOT NULL,
  `plant_variety` varchar(255) DEFAULT NULL,
  `plant_category` enum('padi','palawija','sayuran','buah_buahan','rerumputan','tanaman_industri','tanaman_obat') NOT NULL,
  `planting_date` date NOT NULL,
  `estimated_harvest_date` date DEFAULT NULL,
  `actual_harvest_date` date DEFAULT NULL,
  `planting_method` enum('tugal','jarik','larik','baris','acak','hidroponik','organik') DEFAULT NULL,
  `seed_amount` decimal(10,4) DEFAULT NULL,
  `seed_unit` varchar(20) DEFAULT NULL,
  `spacing` varchar(50) DEFAULT NULL,
  `planting_density` decimal(8,2) DEFAULT NULL,
  `growth_stage` enum('pembibitan','vegetatif','generatif','pemasakan','panen') DEFAULT NULL,
  `status` enum('planned','planted','growing','ready_to_harvest','harvested','failed') NOT NULL DEFAULT 'planned',
  `failure_reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_planting_code` (`planting_code`),
  KEY `idx_land` (`land_id`),
  KEY `idx_plant_name` (`plant_name`),
  KEY `idx_plant_category` (`plant_category`),
  KEY `idx_planting_date` (`planting_date`),
  KEY `idx_status` (`status`),
  KEY `idx_growth_stage` (`growth_stage`),
  KEY `idx_unit` (`unit_id`),
  CONSTRAINT `fk_planting_land` FOREIGN KEY (`land_id`) REFERENCES `lahan` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tanam_planting`
--

LOCK TABLES `tanam_planting` WRITE;
/*!40000 ALTER TABLE `tanam_planting` DISABLE KEYS */;
/*!40000 ALTER TABLE `tanam_planting` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb3 */ ;
/*!50003 SET character_set_results = utf8mb3 */ ;
/*!50003 SET collation_connection  = utf8mb3_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER IF NOT EXISTS `tr_planting_land_update` 
AFTER INSERT ON `tanam_planting`
FOR EACH ROW
BEGIN
    UPDATE `lahan` 
    SET `current_planting_id` = NEW.id,
        `status` = 'planted',
        `updated_at` = NOW()
    WHERE `id` = NEW.land_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb3 */ ;
/*!50003 SET character_set_results = utf8mb3 */ ;
/*!50003 SET collation_connection  = utf8mb3_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER IF NOT EXISTS `tr_planting_status_update` 
AFTER UPDATE ON `tanam_planting`
FOR EACH ROW
BEGIN
    IF NEW.status IN ('harvested', 'failed') AND OLD.status NOT IN ('harvested', 'failed') THEN
        UPDATE `lahan` 
        SET `current_planting_id` = NULL,
            `status` = 'available',
            `updated_at` = NOW()
        WHERE `id` = NEW.land_id;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb3 */ ;
/*!50003 SET character_set_results = utf8mb3 */ ;
/*!50003 SET collation_connection  = utf8mb3_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER IF NOT EXISTS `tr_planting_history_create` 
AFTER UPDATE ON `tanam_planting`
FOR EACH ROW
BEGIN
    IF NEW.status IN ('harvested', 'failed') AND OLD.status NOT IN ('harvested', 'failed') THEN
        INSERT INTO `planting_history` 
        (land_id, planting_id, plant_name, plant_variety, plant_category, 
         planting_date, harvest_date, status, unit_id, created_by, created_at)
        VALUES 
        (NEW.land_id, NEW.id, NEW.plant_name, NEW.plant_variety, NEW.plant_category,
         NEW.planting_date, NEW.actual_harvest_date, NEW.status, NEW.unit_id, NEW.created_by, NOW());
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `tenant_konfigurasi`
--

DROP TABLE IF EXISTS `tenant_konfigurasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tenant_konfigurasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cooperative_id` int(11) NOT NULL,
  `active_modules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`active_modules`)),
  `feature_flags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`feature_flags`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `cooperative_id` (`cooperative_id`),
  CONSTRAINT `tenant_konfigurasi_ibfk_1` FOREIGN KEY (`cooperative_id`) REFERENCES `koperasi_tenant` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenant_konfigurasi`
--

LOCK TABLES `tenant_konfigurasi` WRITE;
/*!40000 ALTER TABLE `tenant_konfigurasi` DISABLE KEYS */;
/*!40000 ALTER TABLE `tenant_konfigurasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `v_koperasi_lengkap`
--

DROP TABLE IF EXISTS `v_koperasi_lengkap`;

-- failed on view `v_koperasi_lengkap`: CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_koperasi_lengkap` AS select `c`.`id` AS `id`,`c`.`nama_koperasi` AS `nama`,`c`.`jenis_koperasi` AS `jenis`,`c`.`badan_hukum` AS `badan_hukum`,`c`.`tanggal_pendirian` AS `tanggal_pendirian`,`c`.`npwp` AS `npwp`,`c`.`alamat_legal` AS `alamat_legal`,`c`.`kontak_resmi` AS `kontak_resmi`,`c`.`logo` AS `logo`,`c`.`dibuat_oleh` AS `dibuat_oleh`,`c`.`dibuat_pada` AS `dibuat_pada`,`c`.`diperbarui_pada` AS `diperbarui_pada`,`p`.`name` AS `province_name`,`r`.`name` AS `regency_name`,`d`.`name` AS `district_name`,`v`.`name` AS `village_name` from ((((`koperasi_db`.`koperasi_tenant` `c` left join `alamat_db`.`provinsi` `p` on(`c`.`provinsi_id` = `p`.`id`)) left join `alamat_db`.`kabkota` `r` on(`c`.`kabkota_id` = `r`.`id`)) left join `alamat_db`.`kecamatan` `d` on(`c`.`kecamatan_id` = `d`.`id`)) left join `alamat_db`.`kelurahan` `v` on(`c`.`kelurahan_id` = `v`.`id`))


--
-- Table structure for table `voting`
--

DROP TABLE IF EXISTS `voting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `voting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agenda` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `status` enum('draft','active','closed') DEFAULT 'draft',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_votes_created_by` (`created_by`),
  CONSTRAINT `fk_votes_created_by` FOREIGN KEY (`created_by`) REFERENCES `people_db`.`users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voting`
--

LOCK TABLES `voting` WRITE;
/*!40000 ALTER TABLE `voting` DISABLE KEYS */;
/*!40000 ALTER TABLE `voting` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voting_suara`
--

DROP TABLE IF EXISTS `voting_suara`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `voting_suara` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vote_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `choice` varchar(100) NOT NULL,
  `voted_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_vote_ballots_vote_user` (`vote_id`,`user_id`),
  CONSTRAINT `voting_suara_ibfk_1` FOREIGN KEY (`vote_id`) REFERENCES `voting` (`id`),
  CONSTRAINT `voting_suara_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `people_db`.`users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voting_suara`
--

LOCK TABLES `voting_suara` WRITE;
/*!40000 ALTER TABLE `voting_suara` DISABLE KEYS */;
/*!40000 ALTER TABLE `voting_suara` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `water_quality_monitoring`
--

DROP TABLE IF EXISTS `water_quality_monitoring`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `water_quality_monitoring` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `monitoring_code` varchar(50) NOT NULL,
  `location` varchar(255) NOT NULL,
  `monitoring_date` date NOT NULL,
  `monitoring_time` time DEFAULT NULL,
  `ph_level` decimal(4,2) DEFAULT NULL,
  `temperature` decimal(5,2) DEFAULT NULL,
  `dissolved_oxygen` decimal(5,2) DEFAULT NULL,
  `ammonia` decimal(6,4) DEFAULT NULL,
  `nitrite` decimal(6,4) DEFAULT NULL,
  `nitrate` decimal(6,4) DEFAULT NULL,
  `salinity` decimal(5,2) DEFAULT NULL,
  `turbidity` decimal(6,2) DEFAULT NULL,
  `alkalinity` decimal(6,2) DEFAULT NULL,
  `hardness` decimal(6,2) DEFAULT NULL,
  `carbon_dioxide` decimal(6,4) DEFAULT NULL,
  `weather_condition` varchar(100) DEFAULT NULL,
  `water_color` varchar(50) DEFAULT NULL,
  `odor` varchar(100) DEFAULT NULL,
  `status` enum('optimal','good','warning','critical') DEFAULT NULL,
  `recommendations` text DEFAULT NULL,
  `checked_by` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_monitoring_code` (`monitoring_code`),
  KEY `idx_location` (`location`),
  KEY `idx_monitoring_date` (`monitoring_date`),
  KEY `idx_status` (`status`),
  KEY `idx_unit` (`unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `water_quality_monitoring`
--

LOCK TABLES `water_quality_monitoring` WRITE;
/*!40000 ALTER TABLE `water_quality_monitoring` DISABLE KEYS */;
/*!40000 ALTER TABLE `water_quality_monitoring` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'koperasi_db'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-15 17:39:48
