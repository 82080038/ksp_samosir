/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.6.23-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: ksp_samosir_business
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
-- Table structure for table `koperasi_jenis`
--

DROP TABLE IF EXISTS `koperasi_jenis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `koperasi_jenis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_jenis` varchar(100) NOT NULL,
  `kode_jenis` varchar(10) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `modul_aktif` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`modul_aktif`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_jenis` (`kode_jenis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `koperasi_jenis`
--

LOCK TABLES `koperasi_jenis` WRITE;
/*!40000 ALTER TABLE `koperasi_jenis` DISABLE KEYS */;
/*!40000 ALTER TABLE `koperasi_jenis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `koperasi_modul`
--

DROP TABLE IF EXISTS `koperasi_modul`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `koperasi_modul` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_modul` varchar(100) NOT NULL,
  `kode_modul` varchar(10) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `konfigurasi` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`konfigurasi`)),
  `status` enum('aktif','non-aktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_modul` (`kode_modul`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `koperasi_modul`
--

LOCK TABLES `koperasi_modul` WRITE;
/*!40000 ALTER TABLE `koperasi_modul` DISABLE KEYS */;
/*!40000 ALTER TABLE `koperasi_modul` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ksp_agricultural_lahan`
--

DROP TABLE IF EXISTS `ksp_agricultural_lahan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ksp_agricultural_lahan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `anggota_id` int(11) NOT NULL,
  `luas_lahan` decimal(8,2) NOT NULL,
  `lokasi` text DEFAULT NULL,
  `jenis_tanah` enum('sawah','tegalan','ladang','kebun') NOT NULL,
  `status_kepemilikan` enum('milik','sewa','bagi_hasil') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_anggota` (`anggota_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ksp_agricultural_lahan`
--

LOCK TABLES `ksp_agricultural_lahan` WRITE;
/*!40000 ALTER TABLE `ksp_agricultural_lahan` DISABLE KEYS */;
/*!40000 ALTER TABLE `ksp_agricultural_lahan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ksp_agricultural_planning`
--

DROP TABLE IF EXISTS `ksp_agricultural_planning`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ksp_agricultural_planning` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lahan_id` int(11) NOT NULL,
  `tanaman_id` int(11) NOT NULL,
  `rencana_tanam` date NOT NULL,
  `rencana_panen` date DEFAULT NULL,
  `estimasi_hasil` decimal(10,2) DEFAULT NULL,
  `status` enum('rencana','proses','panen','selesai') DEFAULT 'rencana',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `tanaman_id` (`tanaman_id`),
  KEY `idx_lahan` (`lahan_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `ksp_agricultural_planning_ibfk_1` FOREIGN KEY (`lahan_id`) REFERENCES `ksp_agricultural_lahan` (`id`),
  CONSTRAINT `ksp_agricultural_planning_ibfk_2` FOREIGN KEY (`tanaman_id`) REFERENCES `ksp_agricultural_tanaman` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ksp_agricultural_planning`
--

LOCK TABLES `ksp_agricultural_planning` WRITE;
/*!40000 ALTER TABLE `ksp_agricultural_planning` DISABLE KEYS */;
/*!40000 ALTER TABLE `ksp_agricultural_planning` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ksp_agricultural_tanaman`
--

DROP TABLE IF EXISTS `ksp_agricultural_tanaman`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ksp_agricultural_tanaman` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_tanaman` varchar(100) NOT NULL,
  `jenis_tanaman` enum('padi','palawija','sayuran','buah','lainnya') NOT NULL,
  `masa_tanam` int(11) DEFAULT NULL,
  `perkiraan_has` decimal(10,2) DEFAULT NULL,
  `harga_per_kg` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ksp_agricultural_tanaman`
--

LOCK TABLES `ksp_agricultural_tanaman` WRITE;
/*!40000 ALTER TABLE `ksp_agricultural_tanaman` DISABLE KEYS */;
/*!40000 ALTER TABLE `ksp_agricultural_tanaman` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'ksp_samosir_business'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-15 17:38:35
