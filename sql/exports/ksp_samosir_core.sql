/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.6.23-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: ksp_samosir_core
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
-- Table structure for table `anggota`
--

DROP TABLE IF EXISTS `anggota`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `anggota` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no_anggota` varchar(20) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `nik` varchar(16) NOT NULL,
  `tempat_lahir` varchar(50) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `pekerjaan` varchar(100) DEFAULT NULL,
  `pendapatan_bulanan` decimal(12,2) DEFAULT NULL,
  `tanggal_gabung` date DEFAULT curdate(),
  `status` enum('aktif','non-aktif','keluar') DEFAULT 'aktif',
  `registration_source` enum('manual','digital') DEFAULT 'manual',
  `registration_ip` varchar(45) DEFAULT NULL,
  `digital_signature` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`digital_signature`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `provinsi_id` int(11) DEFAULT NULL COMMENT 'Reference to alamat_db.provinsi',
  `kabupaten_id` int(11) DEFAULT NULL COMMENT 'Reference to alamat_db.kabupaten',
  `kecamatan_id` int(11) DEFAULT NULL COMMENT 'Reference to alamat_db.kecamatan',
  `kelurahan_id` int(11) DEFAULT NULL COMMENT 'Reference to alamat_db.kelurahan',
  `kode_pos` varchar(10) DEFAULT NULL COMMENT 'Postal code from alamat_db',
  `alamat_lengkap` varchar(255) DEFAULT NULL COMMENT 'Complete formatted address',
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_anggota` (`no_anggota`),
  UNIQUE KEY `nik` (`nik`),
  KEY `idx_no_anggota` (`no_anggota`),
  KEY `idx_nik` (`nik`),
  KEY `idx_status` (`status`),
  KEY `idx_tanggal_gabung` (`tanggal_gabung`),
  KEY `idx_anggota_provinsi` (`provinsi_id`),
  KEY `idx_anggota_kecamatan` (`kecamatan_id`),
  KEY `idx_anggota_kelurahan` (`kelurahan_id`),
  KEY `idx_anggota_kode_pos` (`kode_pos`),
  KEY `idx_anggota_kabupaten` (`kabupaten_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `anggota`
--

LOCK TABLES `anggota` WRITE;
/*!40000 ALTER TABLE `anggota` DISABLE KEYS */;
/*!40000 ALTER TABLE `anggota` ENABLE KEYS */;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER validate_address_before_insert
BEFORE INSERT ON anggota
FOR EACH ROW
BEGIN
    DECLARE prov_count INT DEFAULT 0;
    DECLARE kab_count INT DEFAULT 0;
    
    
    IF NEW.provinsi_id IS NOT NULL THEN
        SELECT COUNT(*) INTO prov_count 
        FROM alamat_db.provinsi 
        WHERE id = NEW.provinsi_id;
        
        IF prov_count = 0 THEN
            SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'Invalid province ID';
        END IF;
    END IF;
    
    
    IF NEW.kabkota_id IS NOT NULL AND NEW.provinsi_id IS NOT NULL THEN
        SELECT COUNT(*) INTO kab_count 
        FROM alamat_db.kabkota 
        WHERE id = NEW.kabkota_id AND province_id = NEW.provinsi_id;
        
        IF kab_count = 0 THEN
            SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'Invalid regency ID or does not belong to selected province';
        END IF;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

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
  `jumlah_angsuran` decimal(12,2) NOT NULL,
  `pokok` decimal(12,2) NOT NULL,
  `bunga` decimal(12,2) NOT NULL,
  `jatuh_tempo` date NOT NULL,
  `tanggal_bayar` date DEFAULT NULL,
  `jumlah_bayar` decimal(12,2) DEFAULT 0.00,
  `sisa_pinjaman` decimal(12,2) DEFAULT NULL,
  `status` enum('belum_bayar','sebagian','lunas','terlambat') DEFAULT 'belum_bayar',
  `denda` decimal(12,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pinjaman` (`pinjaman_id`),
  KEY `idx_jatuh_tempo` (`jatuh_tempo`),
  KEY `idx_status` (`status`),
  CONSTRAINT `angsuran_ibfk_1` FOREIGN KEY (`pinjaman_id`) REFERENCES `pinjaman` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `angsuran`
--

LOCK TABLES `angsuran` WRITE;
/*!40000 ALTER TABLE `angsuran` DISABLE KEYS */;
/*!40000 ALTER TABLE `angsuran` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jenis_pinjaman`
--

DROP TABLE IF EXISTS `jenis_pinjaman`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jenis_pinjaman` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode` varchar(10) NOT NULL,
  `nama_pinjaman` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `minimal_pinjaman` decimal(12,2) DEFAULT NULL,
  `maksimal_pinjaman` decimal(12,2) DEFAULT NULL,
  `bunga_tahunan` decimal(5,2) DEFAULT NULL,
  `tenor_minimal` int(11) DEFAULT NULL,
  `tenor_maksimal` int(11) DEFAULT NULL,
  `persetujuan_otomatis` tinyint(1) DEFAULT 0,
  `status` enum('aktif','non-aktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jenis_pinjaman`
--

LOCK TABLES `jenis_pinjaman` WRITE;
/*!40000 ALTER TABLE `jenis_pinjaman` DISABLE KEYS */;
/*!40000 ALTER TABLE `jenis_pinjaman` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jenis_simpanan`
--

DROP TABLE IF EXISTS `jenis_simpanan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jenis_simpanan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode` varchar(10) NOT NULL,
  `nama_simpanan` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `minimal_setoran` decimal(12,2) DEFAULT 0.00,
  `maksimal_setoran` decimal(12,2) DEFAULT NULL,
  `bunga_tahunan` decimal(5,2) DEFAULT 0.00,
  `status` enum('aktif','non-aktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jenis_simpanan`
--

LOCK TABLES `jenis_simpanan` WRITE;
/*!40000 ALTER TABLE `jenis_simpanan` DISABLE KEYS */;
/*!40000 ALTER TABLE `jenis_simpanan` ENABLE KEYS */;
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
  `jenis_pinjaman_id` int(11) NOT NULL,
  `no_pinjaman` varchar(30) NOT NULL,
  `jumlah_pinjaman` decimal(15,2) NOT NULL,
  `bunga_tahunan` decimal(5,2) DEFAULT NULL,
  `tenor_bulan` int(11) NOT NULL,
  `angsuran_per_bulan` decimal(12,2) DEFAULT NULL,
  `total_bunga` decimal(12,2) DEFAULT NULL,
  `total_pengembalian` decimal(12,2) DEFAULT NULL,
  `tanggal_pengajuan` date DEFAULT curdate(),
  `tanggal_disetujui` date DEFAULT NULL,
  `tanggal_cair` date DEFAULT NULL,
  `tanggal_jatuh_tempo` date DEFAULT NULL,
  `status` enum('pengajuan','disetujui','dicairkan','lunas','ditolak') DEFAULT 'pengajuan',
  `alasan_penolakan` text DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_pinjaman` (`no_pinjaman`),
  KEY `jenis_pinjaman_id` (`jenis_pinjaman_id`),
  KEY `approved_by` (`approved_by`),
  KEY `idx_anggota` (`anggota_id`),
  KEY `idx_no_pinjaman` (`no_pinjaman`),
  KEY `idx_status` (`status`),
  KEY `idx_tanggal_pengajuan` (`tanggal_pengajuan`),
  CONSTRAINT `pinjaman_ibfk_1` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pinjaman_ibfk_2` FOREIGN KEY (`jenis_pinjaman_id`) REFERENCES `jenis_pinjaman` (`id`),
  CONSTRAINT `pinjaman_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`)
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
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','number','boolean','json') DEFAULT 'text',
  `deskripsi` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `updated_by` (`updated_by`),
  KEY `idx_setting_key` (`setting_key`),
  CONSTRAINT `settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'address_integration_enabled','true','boolean','Enable alamat_db integration for addresses',NULL,'2026-02-15 04:48:59'),(2,'address_validation_enabled','true','boolean','Enable address validation against alamat_db',NULL,'2026-02-15 04:48:59'),(3,'default_province_id','2','number','Default province ID (Sumatera Utara)',NULL,'2026-02-15 04:53:22'),(4,'default_regency_id','40','number','Default regency ID (check actual ID)',NULL,'2026-02-15 04:53:22'),(5,'address_autocomplete_enabled','true','boolean','Enable address autocomplete feature',NULL,'2026-02-15 04:48:59');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

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
  `tanggal_buka` date DEFAULT curdate(),
  `tanggal_tutup` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_rekening` (`no_rekening`),
  KEY `jenis_simpanan_id` (`jenis_simpanan_id`),
  KEY `idx_anggota` (`anggota_id`),
  KEY `idx_no_rekening` (`no_rekening`),
  KEY `idx_status` (`status`),
  CONSTRAINT `simpanan_ibfk_1` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`) ON DELETE CASCADE,
  CONSTRAINT `simpanan_ibfk_2` FOREIGN KEY (`jenis_simpanan_id`) REFERENCES `jenis_simpanan` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `simpanan`
--

LOCK TABLES `simpanan` WRITE;
/*!40000 ALTER TABLE `simpanan` DISABLE KEYS */;
/*!40000 ALTER TABLE `simpanan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaksi_pinjaman`
--

DROP TABLE IF EXISTS `transaksi_pinjaman`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaksi_pinjaman` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pinjaman_id` int(11) NOT NULL,
  `angsuran_id` int(11) DEFAULT NULL,
  `jenis_transaksi` enum('pembayaran_angsuran','pelunasan','denda') NOT NULL,
  `jumlah` decimal(12,2) NOT NULL,
  `tanggal_transaksi` date DEFAULT curdate(),
  `keterangan` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `angsuran_id` (`angsuran_id`),
  KEY `created_by` (`created_by`),
  KEY `idx_pinjaman` (`pinjaman_id`),
  KEY `idx_tanggal` (`tanggal_transaksi`),
  CONSTRAINT `transaksi_pinjaman_ibfk_1` FOREIGN KEY (`pinjaman_id`) REFERENCES `pinjaman` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transaksi_pinjaman_ibfk_2` FOREIGN KEY (`angsuran_id`) REFERENCES `angsuran` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transaksi_pinjaman_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaksi_pinjaman`
--

LOCK TABLES `transaksi_pinjaman` WRITE;
/*!40000 ALTER TABLE `transaksi_pinjaman` DISABLE KEYS */;
/*!40000 ALTER TABLE `transaksi_pinjaman` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaksi_simpanan`
--

DROP TABLE IF EXISTS `transaksi_simpanan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaksi_simpanan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `simpanan_id` int(11) NOT NULL,
  `jenis_transaksi` enum('setoran','penarikan') NOT NULL,
  `jumlah` decimal(12,2) NOT NULL,
  `saldo_sebelum` decimal(12,2) DEFAULT NULL,
  `saldo_sesudah` decimal(12,2) DEFAULT NULL,
  `tanggal_transaksi` date DEFAULT curdate(),
  `keterangan` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_simpanan` (`simpanan_id`),
  KEY `idx_tanggal` (`tanggal_transaksi`),
  KEY `idx_jenis` (`jenis_transaksi`),
  CONSTRAINT `transaksi_simpanan_ibfk_1` FOREIGN KEY (`simpanan_id`) REFERENCES `simpanan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transaksi_simpanan_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaksi_simpanan`
--

LOCK TABLES `transaksi_simpanan` WRITE;
/*!40000 ALTER TABLE `transaksi_simpanan` DISABLE KEYS */;
/*!40000 ALTER TABLE `transaksi_simpanan` ENABLE KEYS */;
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
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_roles`
--

LOCK TABLES `user_roles` WRITE;
/*!40000 ALTER TABLE `user_roles` DISABLE KEYS */;
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
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `anggota_id` int(11) DEFAULT NULL,
  `role` enum('admin','pengurus','anggota','staff') DEFAULT 'anggota',
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `anggota_id` (`anggota_id`),
  KEY `idx_username` (`username`),
  KEY `idx_role` (`role`),
  KEY `idx_status` (`status`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`anggota_id`) REFERENCES `anggota` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `v_anggota_alamat_lengkap`
--

DROP TABLE IF EXISTS `v_anggota_alamat_lengkap`;

-- failed on view `v_anggota_alamat_lengkap`: CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_anggota_alamat_lengkap` AS select `a`.`id` AS `id`,`a`.`no_anggota` AS `no_anggota`,`a`.`nama_lengkap` AS `nama_lengkap`,`a`.`alamat` AS `alamat_manual`,`p`.`name` AS `provinsi`,`k`.`name` AS `kabupaten`,`kc`.`name` AS `kecamatan`,`kel`.`name` AS `kelurahan`,`a`.`kode_pos` AS `kode_pos`,concat(coalesce(`kel`.`name`,''),case when `kel`.`name` is not null and `kc`.`name` is not null then ', ' else '' end,coalesce(`kc`.`name`,''),case when `kc`.`name` is not null and `k`.`name` is not null then ', ' else '' end,coalesce(`k`.`name`,''),case when `k`.`name` is not null and `p`.`name` is not null then ', ' else '' end,coalesce(`p`.`name`,''),case when `p`.`name` is not null and `a`.`kode_pos` is not null then ' ' else '' end,coalesce(`a`.`kode_pos`,'')) AS `alamat_format`,`a`.`status` AS `status`,`a`.`tanggal_gabung` AS `tanggal_gabung` from ((((`ksp_samosir_core`.`anggota` `a` left join `alamat_db`.`provinsi` `p` on(`a`.`provinsi_id` = `p`.`id`)) left join `alamat_db`.`kabupaten` `k` on(`a`.`kabupaten_id` = `k`.`id`)) left join `alamat_db`.`kecamatan` `kc` on(`a`.`kecamatan_id` = `kc`.`id`)) left join `alamat_db`.`kelurahan` `kel` on(`a`.`kelurahan_id` = `kel`.`id`))


--
-- Dumping routines for database 'ksp_samosir_core'
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
