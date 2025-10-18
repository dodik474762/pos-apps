-- MySQL dump 10.13  Distrib 8.0.42, for Linux (x86_64)
--
-- Host: localhost    Database: pos
-- ------------------------------------------------------
-- Server version	8.0.42-0ubuntu0.24.04.2

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `actors`
--

DROP TABLE IF EXISTS `actors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `actors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `users` int DEFAULT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `action` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `actors`
--

LOCK TABLES `actors` WRITE;
/*!40000 ALTER TABLE `actors` DISABLE KEYS */;
/*!40000 ALTER TABLE `actors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company`
--

DROP TABLE IF EXISTS `company`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `company` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_company` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted` timestamp NULL DEFAULT NULL,
  `type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'VENDOR|FINANCE|NOTARIS',
  `status` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_hp` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `akun_bank` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `akun_bank_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `akun_bank_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `branch_bank` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `longitude` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat_pengiriman` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `files` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `path_files` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company`
--

LOCK TABLES `company` WRITE;
/*!40000 ALTER TABLE `company` DISABLE KEYS */;
INSERT INTO `company` VALUES (1,'DEFAULT','-','2025-10-12 12:53:16',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(2,'PT. NATURALISTA',NULL,'2025-10-18 06:57:59','2025-10-18 06:57:59',NULL,NULL,'APPROVED','-','0','BRI','HIZBATUL IKRIMA','-','BLITAR',NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `company` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer`
--

DROP TABLE IF EXISTS `customer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_customer` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pic` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `office_contact` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kota` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provinsi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `npwp` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted` timestamp NULL DEFAULT NULL,
  `customer_category` int DEFAULT NULL,
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numbering_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer`
--

LOCK TABLES `customer` WRITE;
/*!40000 ALTER TABLE `customer` DISABLE KEYS */;
INSERT INTO `customer` VALUES (1,'TES','TES','TES','0','-','TES','TES','TES','0','IDR','2025-10-18 07:06:19','2025-10-18 07:06:19',NULL,1,'CUST25OCT0001','TES');
/*!40000 ALTER TABLE `customer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_category`
--

DROP TABLE IF EXISTS `customer_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_category` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_category`
--

LOCK TABLES `customer_category` WRITE;
/*!40000 ALTER TABLE `customer_category` DISABLE KEYS */;
INSERT INTO `customer_category` VALUES (1,'TES','TES',NULL,'2025-10-18 07:03:52','2025-10-18 07:03:52');
/*!40000 ALTER TABLE `customer_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dictionary`
--

DROP TABLE IF EXISTS `dictionary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dictionary` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `term_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keterangan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `context` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `file` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dictionary`
--

LOCK TABLES `dictionary` WRITE;
/*!40000 ALTER TABLE `dictionary` DISABLE KEYS */;
/*!40000 ALTER TABLE `dictionary` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document`
--

DROP TABLE IF EXISTS `document`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `document` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `no_document` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipe` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document`
--

LOCK TABLES `document` WRITE;
/*!40000 ALTER TABLE `document` DISABLE KEYS */;
/*!40000 ALTER TABLE `document` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document_transaction`
--

DROP TABLE IF EXISTS `document_transaction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `document_transaction` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `no_document` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actors` int DEFAULT NULL,
  `state` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_transaction`
--

LOCK TABLES `document_transaction` WRITE;
/*!40000 ALTER TABLE `document_transaction` DISABLE KEYS */;
/*!40000 ALTER TABLE `document_transaction` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee`
--

DROP TABLE IF EXISTS `employee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company` int DEFAULT NULL,
  `nik` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_lengkap` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jabatan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `contact` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_by` int DEFAULT NULL,
  `approved_date` datetime DEFAULT NULL,
  `alamat` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_ortu` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_kerabat` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_complete_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `group` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee`
--

LOCK TABLES `employee` WRITE;
/*!40000 ALTER TABLE `employee` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `karyawan`
--

DROP TABLE IF EXISTS `karyawan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `karyawan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company` int DEFAULT NULL,
  `nik` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_lengkap` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jabatan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `contact` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_by` int DEFAULT NULL,
  `approved_date` datetime DEFAULT NULL,
  `alamat` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_ortu` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_kerabat` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_complete_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `group` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `karyawan`
--

LOCK TABLES `karyawan` WRITE;
/*!40000 ALTER TABLE `karyawan` DISABLE KEYS */;
INSERT INTO `karyawan` VALUES (1,1,'administrator','ADMINISTRATOR','ADMINISTRATOR',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'GROUP_25JAN0003');
/*!40000 ALTER TABLE `karyawan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `karyawan_group`
--

DROP TABLE IF EXISTS `karyawan_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `karyawan_group` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `karyawan` int DEFAULT NULL,
  `group` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default` int DEFAULT '0',
  `deleted` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `karyawan_group`
--

LOCK TABLES `karyawan_group` WRITE;
/*!40000 ALTER TABLE `karyawan_group` DISABLE KEYS */;
INSERT INTO `karyawan_group` VALUES (1,1,'GROUP_25JAN0003',1,NULL,'2025-10-12 12:52:58',NULL);
/*!40000 ALTER TABLE `karyawan_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu`
--

DROP TABLE IF EXISTS `menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `menu_alias` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort` int DEFAULT NULL,
  `routing_mobile` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon_mobile` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `routing` int DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=114 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu`
--

LOCK TABLES `menu` WRITE;
/*!40000 ALTER TABLE `menu` DISABLE KEYS */;
INSERT INTO `menu` VALUES (1,'Master','bx bx-layout',NULL,NULL,NULL,2,NULL,NULL,NULL,NULL,NULL,0),(2,'Menu',NULL,NULL,'master/menu','1',18,NULL,NULL,NULL,NULL,NULL,0),(3,'Users & Permissions','bx bx-user-circle',NULL,'-',NULL,3,NULL,NULL,NULL,'2022-07-26 18:28:30','2022-07-26 18:28:30',0),(4,'Users',NULL,NULL,'master/users','3',1,NULL,NULL,NULL,'2022-07-26 18:28:42','2024-11-13 22:32:30',0),(5,'Permissions',NULL,NULL,'master/permission','3',2,NULL,NULL,NULL,'2022-07-26 18:28:56','2022-07-26 18:28:56',0),(6,'Data','bx bx-detail',NULL,'-',NULL,4,NULL,NULL,NULL,'2022-07-26 21:47:58','2022-07-26 21:47:58',0),(7,'Karyawan',NULL,NULL,'master/karyawan','1',11,NULL,NULL,NULL,'2022-07-26 21:48:12','2024-12-03 02:48:13',0),(8,'Perusahaan',NULL,NULL,'master/company','1',1,NULL,NULL,NULL,'2022-07-26 21:50:11','2024-11-13 22:42:30',0),(9,'Roles',NULL,NULL,'master/roles','3',NULL,NULL,NULL,NULL,'2022-07-26 21:50:56','2022-07-26 21:50:56',0),(10,'Dashboard','bx bx-home-circle',NULL,'dashboard',NULL,1,NULL,NULL,NULL,'2022-07-26 21:57:29','2022-07-26 21:57:29',0),(14,'Transaksi','bx bx-file',NULL,'-',NULL,4,NULL,NULL,NULL,'2022-07-28 00:08:03','2022-07-28 00:08:03',0),(28,'Vendor',NULL,NULL,'master/vendor','1',10,NULL,NULL,NULL,'2022-08-31 19:53:40','2024-12-03 02:47:57',0),(34,'Invoice',NULL,NULL,'transaksi/invoice','14',12,NULL,NULL,NULL,'2022-09-30 18:39:08','2024-01-06 09:57:57',0),(36,'Report','bx bxs-report',NULL,'-',NULL,7,NULL,NULL,NULL,'2022-10-28 07:08:04','2022-10-28 07:08:04',0),(55,'Approval','bx bx-paper-plane',NULL,'-',NULL,5,NULL,NULL,NULL,'2024-10-15 12:54:18','2024-10-15 12:54:18',0),(63,'Customer',NULL,NULL,'master/customer','1',3,NULL,NULL,NULL,'2024-11-26 08:50:34','2024-12-03 02:46:52',0),(64,'Customer Category',NULL,NULL,'master/customer_category','1',2,NULL,NULL,NULL,'2024-11-26 09:07:40','2024-11-26 09:07:40',0),(65,'Product Unit',NULL,NULL,'master/unit','1',5,NULL,NULL,NULL,'2024-11-26 09:07:58','2024-11-26 09:07:58',0),(66,'Attandance','bx bxs-user-account',NULL,'-',NULL,6,NULL,NULL,NULL,'2024-11-28 02:20:19','2024-11-28 02:20:19',0),(67,'Day Off',NULL,NULL,'master/dayoff','1',14,NULL,NULL,NULL,'2024-11-28 02:20:47','2024-12-03 02:49:02',0),(68,'Work Hour',NULL,NULL,'master/working_hours','1',15,NULL,NULL,NULL,'2024-11-28 02:48:57','2024-12-03 02:49:18',0),(69,'Job Category',NULL,NULL,'master/job_category','1',7,NULL,NULL,NULL,'2024-11-28 03:18:36','2024-11-28 03:18:36',0),(70,'Travel Item',NULL,NULL,'master/travel_item','1',8,NULL,NULL,NULL,'2024-11-28 03:19:00','2024-11-28 03:19:00',0),(71,'Product Type',NULL,NULL,'master/product_type','1',4,NULL,NULL,NULL,'2024-11-28 03:33:15','2024-11-28 03:33:15',0),(72,'Product',NULL,NULL,'master/product','1',6,NULL,NULL,NULL,'2024-11-28 03:40:20','2024-12-03 02:47:31',0),(73,'Wilayah','bx bx-map-alt',NULL,'-',NULL,8,NULL,NULL,NULL,'2024-11-28 04:52:13','2024-11-28 04:52:13',0),(74,'Province',NULL,NULL,'master/province','1',16,NULL,NULL,NULL,'2024-11-28 04:52:45','2024-12-03 02:49:46',0),(75,'City',NULL,NULL,'master/city','1',17,NULL,NULL,NULL,'2024-11-28 04:53:02','2024-12-03 02:50:03',0),(76,'Forecast',NULL,NULL,'transaksi/forecast','14',1,NULL,NULL,NULL,'2024-11-28 09:11:26','2024-11-28 09:11:26',0),(77,'Visit Plan',NULL,NULL,'transaksi/visit_plan','14',3,NULL,NULL,NULL,'2024-11-28 09:11:55','2024-11-28 09:11:55',0),(78,'Budgeting',NULL,NULL,'transaksi/budgeting','14',9,NULL,NULL,NULL,'2024-11-28 09:12:17','2024-11-28 09:12:17',1),(79,'Reimbursement',NULL,NULL,'transaksi/reimbursment','14',8,NULL,NULL,NULL,'2024-11-28 09:12:51','2024-11-28 09:12:51',1),(80,'Presensi',NULL,NULL,'transaksi/presensi','14',10,NULL,NULL,NULL,'2024-11-28 09:13:24','2024-12-03 02:52:10',0),(81,'Employee Leave',NULL,NULL,'transaksi/employee_leave','14',11,NULL,NULL,NULL,'2024-11-28 09:14:13','2024-12-03 02:52:27',1),(82,'Budgeting',NULL,NULL,'approval/bugeting','55',1,NULL,NULL,NULL,'2024-12-02 06:46:37','2024-12-02 06:46:37',0),(83,'Routing Approval',NULL,NULL,'master/routing','1',13,NULL,NULL,NULL,'2024-12-02 08:46:45','2024-12-03 02:48:37',0),(84,'Generate Id Project',NULL,NULL,'transaksi/generate_project','14',2,NULL,NULL,NULL,'2024-12-03 02:51:45','2024-12-03 02:51:45',0),(85,'Perdin',NULL,NULL,'approval/perdin','55',2,NULL,NULL,NULL,'2024-12-03 02:53:14','2024-12-03 02:53:14',0),(86,'Realisasi Perdin',NULL,NULL,'approval/realisasi_perdin','55',3,NULL,NULL,NULL,'2024-12-03 02:53:42','2024-12-03 02:53:42',0),(87,'Reimbursement',NULL,NULL,'approval/approval_reimbursement','55',4,NULL,NULL,NULL,'2024-12-03 02:54:23','2024-12-03 02:54:23',0),(88,'Leave',NULL,NULL,'approval/approval_leave','55',6,NULL,NULL,NULL,'2024-12-03 02:54:52','2024-12-03 02:54:52',0),(89,'Perdin',NULL,NULL,'transaksi/perdin','14',5,NULL,NULL,NULL,'2024-12-03 02:55:42','2024-12-03 02:55:42',1),(90,'Sales',NULL,NULL,'transaksi/project','14',4,NULL,NULL,NULL,'2024-12-04 01:58:01','2024-12-04 01:58:01',0),(91,'Realisasi Perdin',NULL,NULL,'transaksi/realisasi_perdin','14',7,NULL,NULL,NULL,'2024-12-04 03:08:24','2024-12-04 03:08:24',1),(92,'Travel Cost',NULL,NULL,'master/travel_cost','1',9,NULL,NULL,NULL,'2024-12-26 07:15:36','2024-12-26 07:15:36',0),(93,'Payment Perdin',NULL,NULL,'transaksi/payment_perdin','14',6,NULL,NULL,NULL,'2024-12-27 09:08:00','2024-12-27 09:08:00',0),(94,'Karyawan Group',NULL,NULL,'master/group','1',12,NULL,NULL,NULL,'2025-01-08 22:01:36','2025-01-08 22:01:36',0),(95,'Perdin After Sales',NULL,NULL,'transaksi/perdin_after_sales','14',13,NULL,NULL,NULL,'2025-01-09 08:51:15','2025-01-09 08:51:15',1),(96,'Perdin After Sales',NULL,NULL,'approval/perdin_after_sales','55',5,NULL,NULL,NULL,'2025-01-09 08:51:37','2025-01-09 08:51:37',0),(97,'Tax Sales',NULL,NULL,'master/tax_sales','1',19,NULL,NULL,NULL,'2025-01-14 03:04:49','2025-01-14 03:04:49',0),(98,'Purchase Order',NULL,NULL,'transaksi/purchase_order','14',14,NULL,NULL,NULL,'2025-01-14 07:08:19','2025-01-14 07:08:19',1),(99,'Invoice',NULL,NULL,'transaksi/invoice','14',15,NULL,NULL,NULL,'2025-01-14 07:08:38','2025-01-14 07:08:38',1),(100,'Purchase Order',NULL,NULL,'approval/purchase_order','55',7,NULL,NULL,NULL,'2025-01-14 07:09:44','2025-01-14 07:09:44',0),(101,'Invoice',NULL,NULL,'approval/invoice','55',8,NULL,NULL,NULL,'2025-01-14 07:10:00','2025-01-14 07:10:00',0),(102,'Pengembalian Perdin',NULL,NULL,'transaksi/pengembalian','14',16,NULL,NULL,NULL,'2025-02-07 00:51:33','2025-02-07 00:51:33',0),(103,'Company Profile',NULL,NULL,'master/company_profile','1',NULL,NULL,NULL,NULL,'2025-04-17 08:33:14','2025-04-17 08:33:14',0),(104,'Product Catalog',NULL,NULL,'master/product_catalog','1',NULL,NULL,NULL,NULL,'2025-04-17 08:33:32','2025-04-17 08:33:32',0),(105,'Report Perdin Item',NULL,NULL,'report/perdin-item','36',NULL,NULL,NULL,NULL,'2025-07-17 22:46:29','2025-07-17 22:46:29',0),(106,'SPK Jasa Instalasi',NULL,NULL,'transaksi/spk_jasa_instalasi','14',17,NULL,NULL,NULL,'2025-07-21 22:29:26','2025-07-21 22:29:26',1),(107,'SPK Jasa Instalasi',NULL,NULL,'approval/spk','55',9,NULL,NULL,NULL,'2025-07-23 22:21:19','2025-07-23 22:21:19',0),(108,'Forecast Detail',NULL,NULL,'report/forecast_detail','36',NULL,NULL,NULL,NULL,'2025-08-01 22:59:07','2025-08-01 22:59:07',0),(109,'Sales Detail',NULL,NULL,'report/sales_detail','36',NULL,NULL,NULL,NULL,'2025-08-01 22:59:20','2025-08-01 22:59:20',0),(110,'Forecast Vs Sales',NULL,NULL,'report/forecast_vs_sales','36',NULL,NULL,NULL,NULL,'2025-08-06 22:30:35','2025-08-06 22:30:35',0),(111,'Sales Shipment',NULL,NULL,'report/sales_shipment','36',NULL,NULL,NULL,NULL,'2025-08-09 01:53:26','2025-08-09 01:53:26',0),(112,'Gross Profit',NULL,NULL,'report/gross_profit','36',NULL,NULL,NULL,NULL,'2025-08-11 23:02:40','2025-08-11 23:02:40',0),(113,'Budgeting Report',NULL,NULL,'report/budgeting_report','36',NULL,NULL,NULL,NULL,'2025-08-12 22:44:02','2025-08-12 22:44:02',0);
/*!40000 ALTER TABLE `menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product`
--

DROP TABLE IF EXISTS `product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_type` int DEFAULT NULL,
  `unit` int DEFAULT NULL,
  `remarks` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `purchase_price` double DEFAULT NULL,
  `selling_price` double DEFAULT NULL,
  `files` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'berisi file product catalog',
  `path_files` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creator` int DEFAULT NULL,
  `model_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product`
--

LOCK TABLES `product` WRITE;
/*!40000 ALTER TABLE `product` DISABLE KEYS */;
INSERT INTO `product` VALUES (1,'P25OCT0001','TES',1,1,'test',NULL,'2025-10-18 07:21:15','2025-10-18 07:21:15',12000,13000,NULL,NULL,1,'TES');
/*!40000 ALTER TABLE `product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_log`
--

DROP TABLE IF EXISTS `product_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product` int DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_type` int DEFAULT NULL,
  `unit` int DEFAULT NULL,
  `remarks` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `purchase_price` double DEFAULT NULL,
  `selling_price` double DEFAULT NULL,
  `files` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'berisi file product catalog',
  `path_files` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creator` int DEFAULT NULL,
  `model_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_log`
--

LOCK TABLES `product_log` WRITE;
/*!40000 ALTER TABLE `product_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_type`
--

DROP TABLE IF EXISTS `product_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_type` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_type`
--

LOCK TABLES `product_type` WRITE;
/*!40000 ALTER TABLE `product_type` DISABLE KEYS */;
INSERT INTO `product_type` VALUES (1,'TES','-',NULL,'2025-10-18 07:18:25','2025-10-18 07:18:25');
/*!40000 ALTER TABLE `product_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `region`
--

DROP TABLE IF EXISTS `region`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `region` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `latitude` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `longitude` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `region`
--

LOCK TABLES `region` WRITE;
/*!40000 ALTER TABLE `region` DISABLE KEYS */;
/*!40000 ALTER TABLE `region` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `routing_header`
--

DROP TABLE IF EXISTS `routing_header`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `routing_header` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_group` int DEFAULT NULL,
  `company` int DEFAULT NULL,
  `deleted` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `menu` int DEFAULT NULL,
  `group` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `routing_header`
--

LOCK TABLES `routing_header` WRITE;
/*!40000 ALTER TABLE `routing_header` DISABLE KEYS */;
/*!40000 ALTER TABLE `routing_header` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `routing_permission`
--

DROP TABLE IF EXISTS `routing_permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `routing_permission` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `routing_header` int DEFAULT NULL,
  `menu` int DEFAULT NULL,
  `prev_state` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `users` int DEFAULT NULL,
  `is_active` int DEFAULT NULL,
  `deleted` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `group` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `routing_permission`
--

LOCK TABLES `routing_permission` WRITE;
/*!40000 ALTER TABLE `routing_permission` DISABLE KEYS */;
/*!40000 ALTER TABLE `routing_permission` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `routing_reminder`
--

DROP TABLE IF EXISTS `routing_reminder`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `routing_reminder` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `routing_header` int DEFAULT NULL,
  `menu` int DEFAULT NULL,
  `users` int DEFAULT NULL,
  `deleted` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `routing_reminder`
--

LOCK TABLES `routing_reminder` WRITE;
/*!40000 ALTER TABLE `routing_reminder` DISABLE KEYS */;
/*!40000 ALTER TABLE `routing_reminder` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unit`
--

DROP TABLE IF EXISTS `unit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `unit` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unit`
--

LOCK TABLES `unit` WRITE;
/*!40000 ALTER TABLE `unit` DISABLE KEYS */;
INSERT INTO `unit` VALUES (1,'PCS',NULL,NULL,'2025-10-18 07:09:46','2025-10-18 07:09:46'),(2,'BOX',NULL,NULL,'2025-10-18 07:09:54','2025-10-18 07:09:54'),(3,'KARTON',NULL,NULL,'2025-10-18 07:10:01','2025-10-18 07:10:01');
/*!40000 ALTER TABLE `unit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nik` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_group` int DEFAULT NULL,
  `deleted` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `fcm_token` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'administrator','ADMINISTRATOR','administrator','$2y$10$A5WJ2GLR721Lr/dGNdMOKung/IFiku/kZH9UBHQPz7CDn.a3nUlgm',1,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_group`
--

DROP TABLE IF EXISTS `users_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users_group` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `group` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_group`
--

LOCK TABLES `users_group` WRITE;
/*!40000 ALTER TABLE `users_group` DISABLE KEYS */;
INSERT INTO `users_group` VALUES (1,'SUPERADMIN','IT',NULL,'2025-10-12 12:44:38',NULL);
/*!40000 ALTER TABLE `users_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_permissions`
--

DROP TABLE IF EXISTS `users_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users_permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `users_group` int DEFAULT NULL,
  `menu` int DEFAULT NULL,
  `insert` int DEFAULT NULL,
  `update` int DEFAULT NULL,
  `delete` int DEFAULT NULL,
  `view` int DEFAULT NULL,
  `print` int DEFAULT NULL,
  `deleted` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5032 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_permissions`
--

LOCK TABLES `users_permissions` WRITE;
/*!40000 ALTER TABLE `users_permissions` DISABLE KEYS */;
INSERT INTO `users_permissions` VALUES (4971,1,1,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(4972,1,2,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(4973,1,7,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(4974,1,8,1,1,1,1,0,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(4975,1,28,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(4976,1,63,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(4977,1,64,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(4978,1,65,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(4979,1,67,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(4980,1,68,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(4981,1,69,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(4982,1,70,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(4983,1,71,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(4984,1,72,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(4985,1,74,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(4986,1,75,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(4987,1,83,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(4988,1,92,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(4989,1,94,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(4990,1,97,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(4991,1,103,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(4992,1,104,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(4993,1,3,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(4994,1,4,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(4995,1,5,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(4996,1,9,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(4997,1,10,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(4998,1,14,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(4999,1,76,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(5000,1,77,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(5001,1,78,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(5002,1,79,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(5003,1,80,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(5004,1,81,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(5005,1,84,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(5006,1,89,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(5007,1,90,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(5008,1,91,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(5009,1,93,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(5010,1,95,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(5011,1,98,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(5012,1,99,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(5013,1,102,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(5014,1,106,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(5015,1,36,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(5016,1,108,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(5017,1,109,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(5018,1,110,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(5019,1,111,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(5020,1,112,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(5021,1,113,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(5022,1,55,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(5023,1,82,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(5024,1,85,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(5025,1,86,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(5026,1,87,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(5027,1,88,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(5028,1,96,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(5029,1,100,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(5030,1,101,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28'),(5031,1,107,1,1,1,1,1,NULL,'2025-08-12 22:44:28','2025-08-12 22:44:28');
/*!40000 ALTER TABLE `users_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'pos'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-10-18 21:22:50
