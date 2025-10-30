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
  `credit_limit` double DEFAULT NULL,
  `payment_terms` int DEFAULT NULL COMMENT 'dalam satuan hari',
  `price_list` int DEFAULT NULL,
  `no_ktp` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer`
--

LOCK TABLES `customer` WRITE;
/*!40000 ALTER TABLE `customer` DISABLE KEYS */;
INSERT INTO `customer` VALUES (1,'TES','TES','TES','0','-','TES','3572','35','0','IDR','2025-10-18 07:06:19','2025-10-29 23:00:35',NULL,1,'CUST25OCT0001','TES',2000000,14,NULL,'01234');
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
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `items` (
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
  `files` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'berisi file product catalog',
  `path_files` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creator` int DEFAULT NULL,
  `model_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `items`
--

LOCK TABLES `items` WRITE;
/*!40000 ALTER TABLE `items` DISABLE KEYS */;
INSERT INTO `items` VALUES (1,'P25OCT0002','TES',1,1,'test',NULL,'2025-10-18 07:37:36','2025-10-18 07:37:36',12000,NULL,NULL,1,'TES');
/*!40000 ALTER TABLE `items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `items_log`
--

DROP TABLE IF EXISTS `items_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `items_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `item` int DEFAULT NULL,
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
-- Dumping data for table `items_log`
--

LOCK TABLES `items_log` WRITE;
/*!40000 ALTER TABLE `items_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `items_log` ENABLE KEYS */;
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
-- Table structure for table `price_list`
--

DROP TABLE IF EXISTS `price_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `price_list` (
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
-- Dumping data for table `price_list`
--

LOCK TABLES `price_list` WRITE;
/*!40000 ALTER TABLE `price_list` DISABLE KEYS */;
INSERT INTO `price_list` VALUES (1,'ECER',NULL,NULL,NULL,NULL),(2,'GROSIR',NULL,NULL,NULL,NULL),(3,'RESELLER',NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `price_list` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=9475 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `region`
--

LOCK TABLES `region` WRITE;
/*!40000 ALTER TABLE `region` DISABLE KEYS */;
INSERT INTO `region` VALUES (11,'ACEH',NULL,'11',NULL,'PROVINSI',NULL,NULL,NULL,'5.5510','95.3221'),(12,'SUMATERA UTARA',NULL,'12',NULL,'PROVINSI',NULL,NULL,NULL,'3.0000','98.0000'),(13,'SUMATERA BARAT',NULL,'13',NULL,'PROVINSI',NULL,NULL,NULL,'-0.9380','100.4080'),(14,'RIAU',NULL,'14',NULL,'PROVINSI',NULL,NULL,NULL,'0.5000','101.4500'),(15,'JAMBI',NULL,'15',NULL,'PROVINSI',NULL,NULL,NULL,'-1.6167','103.6167'),(16,'SUMATERA SELATAN',NULL,'16',NULL,'PROVINSI',NULL,NULL,NULL,'-3.2500','104.7500'),(17,'BENGKULU',NULL,'17',NULL,'PROVINSI',NULL,NULL,NULL,'-3.8000','102.2670'),(18,'LAMPUNG',NULL,'18',NULL,'PROVINSI',NULL,NULL,NULL,'-5.4297','105.2539'),(19,'KEPULAUAN BANGKA BELITUNG',NULL,'19',NULL,'PROVINSI',NULL,NULL,NULL,'-2.2000','106.4500'),(21,'KEPULAUAN RIAU',NULL,'21',NULL,'PROVINSI',NULL,NULL,NULL,'0.9400','104.4000'),(31,'DKI JAKARTA',NULL,'31',NULL,'PROVINSI',NULL,NULL,NULL,'-6.2088','106.8456'),(32,'JAWA BARAT',NULL,'32',NULL,'PROVINSI',NULL,NULL,NULL,'-6.9635','107.6000'),(33,'JAWA TENGAH',NULL,'33',NULL,'PROVINSI',NULL,NULL,NULL,'-7.1500','110.3500'),(34,'DI YOGYAKARTA',NULL,'34',NULL,'PROVINSI',NULL,NULL,NULL,'-7.8000','110.3700'),(35,'JAWA TIMUR',NULL,'35',NULL,'PROVINSI',NULL,NULL,NULL,'-7.2500','112.7500'),(36,'BANTEN',NULL,'36',NULL,'PROVINSI',NULL,NULL,NULL,'-6.6011','106.3924'),(51,'BALI',NULL,'51',NULL,'PROVINSI',NULL,NULL,NULL,'-8.4095','115.1889'),(52,'NUSA TENGGARA BARAT',NULL,'52',NULL,'PROVINSI',NULL,NULL,NULL,'-8.4500','116.0000'),(53,'NUSA TENGGARA TIMUR',NULL,'53',NULL,'PROVINSI',NULL,NULL,NULL,'-8.5000','120.0000'),(61,'KALIMANTAN BARAT',NULL,'61',NULL,'PROVINSI',NULL,NULL,NULL,'0.0000','109.5000'),(62,'KALIMANTAN TENGAH',NULL,'62',NULL,'PROVINSI',NULL,NULL,NULL,'-2.0000','113.5000'),(63,'KALIMANTAN SELATAN',NULL,'63',NULL,'PROVINSI',NULL,NULL,NULL,'-3.0000','115.5000'),(64,'KALIMANTAN TIMUR',NULL,'64',NULL,'PROVINSI',NULL,NULL,NULL,'0.5000','116.5000'),(65,'KALIMANTAN UTARA',NULL,'65',NULL,'PROVINSI',NULL,NULL,NULL,'3.0000','117.0000'),(71,'SULAWESI UTARA',NULL,'71',NULL,'PROVINSI',NULL,NULL,NULL,'1.5000','124.0000'),(72,'SULAWESI TENGAH',NULL,'72',NULL,'PROVINSI',NULL,NULL,NULL,'-1.5000','120.5000'),(73,'SULAWESI SELATAN',NULL,'73',NULL,'PROVINSI',NULL,NULL,NULL,'-3.6650','119.9400'),(74,'SULAWESI TENGGARA',NULL,'74',NULL,'PROVINSI',NULL,NULL,NULL,'-3.4600','122.5300'),(75,'GORONTALO',NULL,'75',NULL,'PROVINSI',NULL,NULL,NULL,'0.5570','123.0600'),(76,'SULAWESI BARAT',NULL,'76',NULL,'PROVINSI',NULL,NULL,NULL,'-3.6564','119.5000'),(81,'MALUKU',NULL,'81',NULL,'PROVINSI',NULL,NULL,NULL,'-3.0000','128.0000'),(82,'MALUKU UTARA',NULL,'82',NULL,'PROVINSI',NULL,NULL,NULL,'1.0000','127.5000'),(91,'PAPUA BARAT',NULL,'91',NULL,'PROVINSI',NULL,NULL,NULL,'-2.0000','133.5000'),(94,'PAPUA',NULL,'94',NULL,'PROVINSI',NULL,NULL,NULL,'-4.0000','136.0000'),(1101,'KABUPATEN SIMEULUE',NULL,'1101','11','KOTA',NULL,NULL,NULL,'2.0500','96.0500'),(1102,'KABUPATEN ACEH SINGKIL',NULL,'1102','11','KOTA',NULL,NULL,NULL,'2.4227','97.2764'),(1103,'KABUPATEN ACEH SELATAN',NULL,'1103','11','KOTA',NULL,NULL,NULL,'3.3027','97.5103'),(1104,'KABUPATEN ACEH TENGGARA',NULL,'1104','11','KOTA',NULL,NULL,NULL,'3.5015','97.6119'),(1105,'KABUPATEN ACEH TIMUR',NULL,'1105','11','KOTA',NULL,NULL,NULL,'4.2783','97.9981'),(1106,'KABUPATEN ACEH TENGAH',NULL,'1106','11','KOTA',NULL,NULL,NULL,'4.6324','97.6152'),(1107,'KABUPATEN ACEH BARAT',NULL,'1107','11','KOTA',NULL,NULL,NULL,'4.2072','95.3839'),(1108,'KABUPATEN ACEH BESAR',NULL,'1108','11','KOTA',NULL,NULL,NULL,'5.5746','95.5169'),(1109,'KABUPATEN PIDIE',NULL,'1109','11','KOTA',NULL,NULL,NULL,'5.2462','96.8253'),(1110,'KABUPATEN BIREUEN',NULL,'1110','11','KOTA',NULL,NULL,NULL,'5.1616','96.7703'),(1111,'KABUPATEN ACEH UTARA',NULL,'1111','11','KOTA',NULL,NULL,NULL,'5.0081','97.5182'),(1112,'KABUPATEN ACEH BARAT DAYA',NULL,'1112','11','KOTA',NULL,NULL,NULL,'4.4769','96.2393'),(1113,'KABUPATEN GAYO LUES',NULL,'1113','11','KOTA',NULL,NULL,NULL,'3.6033','97.7213'),(1114,'KABUPATEN ACEH TAMIANG',NULL,'1114','11','KOTA',NULL,NULL,NULL,'4.2365','98.0033'),(1115,'KABUPATEN NAGAN RAYA',NULL,'1115','11','KOTA',NULL,NULL,NULL,'4.4827','96.2435'),(1116,'KABUPATEN ACEH JAYA',NULL,'1116','11','KOTA',NULL,NULL,NULL,'4.3504','95.5542'),(1117,'KABUPATEN BENER MERIAH',NULL,'1117','11','KOTA',NULL,NULL,NULL,'4.6741','96.8356'),(1118,'KABUPATEN PIDIE JAYA',NULL,'1118','11','KOTA',NULL,NULL,NULL,'5.2989','96.0197'),(1171,'KOTA BANDA ACEH',NULL,'1171','11','KOTA',NULL,NULL,NULL,'5.5510','95.3221'),(1172,'KOTA SABANG',NULL,'1172','11','KOTA',NULL,NULL,NULL,'5.3167','95.3127'),(1173,'KOTA LANGSA',NULL,'1173','11','KOTA',NULL,NULL,NULL,'4.4498','97.9614'),(1174,'KOTA LHOKSEUMAWE',NULL,'1174','11','KOTA',NULL,NULL,NULL,'4.1943','97.3910'),(1175,'KOTA SUBULUSSALAM',NULL,'1175','11','KOTA',NULL,NULL,NULL,'3.2549','97.5816'),(1201,'KABUPATEN NIAS',NULL,'1201','12','KOTA',NULL,NULL,NULL,'1.1297','97.5176'),(1202,'KABUPATEN MANDAILING NATAL',NULL,'1202','12','KOTA',NULL,NULL,NULL,'1.6470','99.2886'),(1203,'KABUPATEN TAPANULI SELATAN',NULL,'1203','12','KOTA',NULL,NULL,NULL,'2.5486','98.9185'),(1204,'KABUPATEN TAPANULI TENGAH',NULL,'1204','12','KOTA',NULL,NULL,NULL,'2.5486','98.9185'),(1205,'KABUPATEN TAPANULI UTARA',NULL,'1205','12','KOTA',NULL,NULL,NULL,'2.5486','98.9185'),(1206,'KABUPATEN TOBA SAMOSIR',NULL,'1206','12','KOTA',NULL,NULL,NULL,'2.7880','98.9719'),(1207,'KABUPATEN LABUHAN BATU',NULL,'1207','12','KOTA',NULL,NULL,NULL,'2.4060','99.2136'),(1208,'KABUPATEN ASAHAN',NULL,'1208','12','KOTA',NULL,NULL,NULL,'2.9687','99.2481'),(1209,'KABUPATEN SIMALUNGUN',NULL,'1209','12','KOTA',NULL,NULL,NULL,'2.9210','99.0165'),(1210,'KABUPATEN DAIRI',NULL,'1210','12','KOTA',NULL,NULL,NULL,'2.7413','98.4614'),(1211,'KABUPATEN KARO',NULL,'1211','12','KOTA',NULL,NULL,NULL,'3.0897','98.4304'),(1212,'KABUPATEN DELI SERDANG',NULL,'1212','12','KOTA',NULL,NULL,NULL,'3.4088','98.8711'),(1213,'KABUPATEN LANGKAT',NULL,'1213','12','KOTA',NULL,NULL,NULL,'3.0833','98.4667'),(1214,'KABUPATEN NIAS SELATAN',NULL,'1214','12','KOTA',NULL,NULL,NULL,'0.7053','97.7123'),(1215,'KABUPATEN HUMBANG HASUNDUTAN',NULL,'1215','12','KOTA',NULL,NULL,NULL,'2.4580','98.4117'),(1216,'KABUPATEN PAKPAK BHARAT',NULL,'1216','12','KOTA',NULL,NULL,NULL,'3.3872','98.1480'),(1217,'KABUPATEN SAMOSIR',NULL,'1217','12','KOTA',NULL,NULL,NULL,'2.7485','98.6178'),(1218,'KABUPATEN SERDANG BEDAGAI',NULL,'1218','12','KOTA',NULL,NULL,NULL,'3.2679','99.2434'),(1219,'KABUPATEN BATU BARA',NULL,'1219','12','KOTA',NULL,NULL,NULL,'3.0277','99.0801'),(1220,'KABUPATEN PADANG LAWAS UTARA',NULL,'1220','12','KOTA',NULL,NULL,NULL,'1.2440','99.3990'),(1221,'KABUPATEN PADANG LAWAS',NULL,'1221','12','KOTA',NULL,NULL,NULL,'1.2157','99.4039'),(1222,'KABUPATEN LABUHAN BATU SELATAN',NULL,'1222','12','KOTA',NULL,NULL,NULL,'2.2783','99.3592'),(1223,'KABUPATEN LABUHAN BATU UTARA',NULL,'1223','12','KOTA',NULL,NULL,NULL,'2.4739','99.1980'),(1224,'KABUPATEN NIAS UTARA',NULL,'1224','12','KOTA',NULL,NULL,NULL,'1.3490','97.6623'),(1225,'KABUPATEN NIAS BARAT',NULL,'1225','12','KOTA',NULL,NULL,NULL,'1.2843',' 97.9012'),(1271,'KOTA SIBOLGA',NULL,'1271','12','KOTA',NULL,NULL,NULL,'1.7475','98.9400'),(1272,'KOTA TANJUNG BALAI',NULL,'1272','12','KOTA',NULL,NULL,NULL,'2.9599','99.7982'),(1273,'KOTA PEMATANG SIANTAR',NULL,'1273','12','KOTA',NULL,NULL,NULL,'2.9545','99.0714'),(1274,'KOTA TEBING TINGGI',NULL,'1274','12','KOTA',NULL,NULL,NULL,'3.3130','99.1598'),(1275,'KOTA MEDAN',NULL,'1275','12','KOTA',NULL,NULL,NULL,'3.5952','98.6722'),(1276,'KOTA BINJAI',NULL,'1276','12','KOTA',NULL,NULL,NULL,'3.6095','98.5112'),(1277,'KOTA PADANGSIDIMPUAN',NULL,'1277','12','KOTA',NULL,NULL,NULL,'1.3773','98.9647'),(1278,'KOTA GUNUNGSITOLI',NULL,'1278','12','KOTA',NULL,NULL,NULL,'1.3028','97.7133'),(1301,'KABUPATEN KEPULAUAN MENTAWAI',NULL,'1301','13','KOTA',NULL,NULL,NULL,'-2.2307','99.7922'),(1302,'KABUPATEN PESISIR SELATAN',NULL,'1302','13','KOTA',NULL,NULL,NULL,'-1.0651','100.3666'),(1303,'KABUPATEN SOLOK',NULL,'1303','13','KOTA',NULL,NULL,NULL,'-0.7298','100.6492'),(1304,'KABUPATEN SIJUNJUNG',NULL,'1304','13','KOTA',NULL,NULL,NULL,'-0.2310','100.5313'),(1305,'KABUPATEN TANAH DATAR',NULL,'1305','13','KOTA',NULL,NULL,NULL,'-0.3609','100.5602'),(1306,'KABUPATEN PADANG PARIAMAN',NULL,'1306','13','KOTA',NULL,NULL,NULL,'-0.5823','100.1486'),(1307,'KABUPATEN AGAM',NULL,'1307','13','KOTA',NULL,NULL,NULL,'-0.3489','100.0360'),(1308,'KABUPATEN LIMA PULUH KOTA',NULL,'1308','13','KOTA',NULL,NULL,NULL,'-0.6909','100.5859'),(1309,'KABUPATEN PASAMAN',NULL,'1309','13','KOTA',NULL,NULL,NULL,'-0.5520','99.6397'),(1310,'KABUPATEN SOLOK SELATAN',NULL,'1310','13','KOTA',NULL,NULL,NULL,'-1.1087','101.3003'),(1311,'KABUPATEN DHARMASRAYA',NULL,'1311','13','KOTA',NULL,NULL,NULL,'-1.0574','101.4274'),(1312,'KABUPATEN PASAMAN BARAT',NULL,'1312','13','KOTA',NULL,NULL,NULL,'-0.3195','99.7434'),(1371,'KOTA PADANG',NULL,'1371','13','KOTA',NULL,NULL,NULL,'-0.9492','100.4013'),(1372,'KOTA SOLOK',NULL,'1372','13','KOTA',NULL,NULL,NULL,'-0.8647','100.6546'),(1373,'KOTA SAWAH LUNTO',NULL,'1373','13','KOTA',NULL,NULL,NULL,'-0.7850','100.6220'),(1374,'KOTA PADANG PANJANG',NULL,'1374','13','KOTA',NULL,NULL,NULL,'-0.4980','100.3978'),(1375,'KOTA BUKITTINGGI',NULL,'1375','13','KOTA',NULL,NULL,NULL,'-0.2957','100.3677'),(1376,'KOTA PAYAKUMBUH',NULL,'1376','13','KOTA',NULL,NULL,NULL,'-0.2670','100.6200'),(1377,'KOTA PARIAMAN',NULL,'1377','13','KOTA',NULL,NULL,NULL,'-0.9764','100.1327'),(1401,'KABUPATEN KUANTAN SINGINGI',NULL,'1401','14','KOTA',NULL,NULL,NULL,'0.7564','101.5121'),(1402,'KABUPATEN INDRAGIRI HULU',NULL,'1402','14','KOTA',NULL,NULL,NULL,'0.4324','101.5899'),(1403,'KABUPATEN INDRAGIRI HILIR',NULL,'1403','14','KOTA',NULL,NULL,NULL,'1.2000','103.0700'),(1404,'KABUPATEN PELALAWAN',NULL,'1404','14','KOTA',NULL,NULL,NULL,'1.4600','102.1150'),(1405,'KABUPATEN SIAK',NULL,'1405','14','KOTA',NULL,NULL,NULL,'0.5597','102.0850'),(1406,'KABUPATEN KAMPAR',NULL,'1406','14','KOTA',NULL,NULL,NULL,'0.4875','101.3900'),(1407,'KABUPATEN ROKAN HULU',NULL,'1407','14','KOTA',NULL,NULL,NULL,'0.6639','101.2500'),(1408,'KABUPATEN BENGKALIS',NULL,'1408','14','KOTA',NULL,NULL,NULL,'1.6801','101.6165'),(1409,'KABUPATEN ROKAN HILIR',NULL,'1409','14','KOTA',NULL,NULL,NULL,'1.0260','101.2300'),(1410,'KABUPATEN KEPULAUAN MERANTI',NULL,'1410','14','KOTA',NULL,NULL,NULL,'2.2764','102.2321'),(1471,'KOTA PEKANBARU',NULL,'1471','14','KOTA',NULL,NULL,NULL,'0.5290','101.4476'),(1473,'KOTA DUMAI',NULL,'1473','14','KOTA',NULL,NULL,NULL,'1.6378','101.4472'),(1501,'KABUPATEN KERINCI',NULL,'1501','15','KOTA',NULL,NULL,NULL,'-2.1111','101.2667'),(1502,'KABUPATEN MERANGIN',NULL,'1502','15','KOTA',NULL,NULL,NULL,'-2.1450','102.3500'),(1503,'KABUPATEN SAROLANGUN',NULL,'1503','15','KOTA',NULL,NULL,NULL,'-2.0167','102.8167'),(1504,'KABUPATEN BATANG HARI',NULL,'1504','15','KOTA',NULL,NULL,NULL,'-1.6000','102.9333'),(1505,'KABUPATEN MUARO JAMBI',NULL,'1505','15','KOTA',NULL,NULL,NULL,'-1.6000','103.6000'),(1506,'KABUPATEN TANJUNG JABUNG TIMUR',NULL,'1506','15','KOTA',NULL,NULL,NULL,'-1.7500','104.1667'),(1507,'KABUPATEN TANJUNG JABUNG BARAT',NULL,'1507','15','KOTA',NULL,NULL,NULL,'-1.1675','103.2833'),(1508,'KABUPATEN TEBO',NULL,'1508','15','KOTA',NULL,NULL,NULL,'-1.7167','102.2167'),(1509,'KABUPATEN BUNGO',NULL,'1509','15','KOTA',NULL,NULL,NULL,'-1.3000','102.4167'),(1571,'KOTA JAMBI',NULL,'1571','15','KOTA',NULL,NULL,NULL,'-1.6250','103.6167'),(1572,'KOTA SUNGAI PENUH',NULL,'1572','15','KOTA',NULL,NULL,NULL,'-2.0500','101.4167'),(1601,'KABUPATEN OGAN KOMERING ULU',NULL,'1601','16','KOTA',NULL,NULL,NULL,'-3.5333','104.3167'),(1602,'KABUPATEN OGAN KOMERING ILIR',NULL,'1602','16','KOTA',NULL,NULL,NULL,'-3.5000','104.6667'),(1603,'KABUPATEN MUARA ENIM',NULL,'1603','16','KOTA',NULL,NULL,NULL,'-3.2500','104.0000'),(1604,'KABUPATEN LAHAT',NULL,'1604','16','KOTA',NULL,NULL,NULL,'-3.5667','104.0000'),(1605,'KABUPATEN MUSI RAWAS',NULL,'1605','16','KOTA',NULL,NULL,NULL,'-3.0333','103.6667'),(1606,'KABUPATEN MUSI BANYUASIN',NULL,'1606','16','KOTA',NULL,NULL,NULL,'-3.0000','104.5000'),(1607,'KABUPATEN BANYU ASIN',NULL,'1607','16','KOTA',NULL,NULL,NULL,'-3.3167','104.8167'),(1608,'KABUPATEN OGAN KOMERING ULU SELATAN',NULL,'1608','16','KOTA',NULL,NULL,NULL,'-3.4167','104.3667'),(1609,'KABUPATEN OGAN KOMERING ULU TIMUR',NULL,'1609','16','KOTA',NULL,NULL,NULL,'-3.3000','104.3167'),(1610,'KABUPATEN OGAN ILIR',NULL,'1610','16','KOTA',NULL,NULL,NULL,'-3.3333','104.5833'),(1611,'KABUPATEN EMPAT LAWANG',NULL,'1611','16','KOTA',NULL,NULL,NULL,'-3.3833','103.5167'),(1612,'KABUPATEN PENUKAL ABAB LEMATANG ILIR',NULL,'1612','16','KOTA',NULL,NULL,NULL,'-3.3000','104.3500'),(1613,'KABUPATEN MUSI RAWAS UTARA',NULL,'1613','16','KOTA',NULL,NULL,NULL,'3.3300','103.5340'),(1671,'KOTA PALEMBANG',NULL,'1671','16','KOTA',NULL,NULL,NULL,'-2.9917','104.7561'),(1672,'KOTA PRABUMULIH',NULL,'1672','16','KOTA',NULL,NULL,NULL,'-3.4667','104.1667'),(1673,'KOTA PAGAR ALAM',NULL,'1673','16','KOTA',NULL,NULL,NULL,'-4.0167','104.0833'),(1674,'KOTA LUBUKLINGGAU',NULL,'1674','16','KOTA',NULL,NULL,NULL,'-3.3000','102.9000'),(1701,'KABUPATEN BENGKULU SELATAN',NULL,'1701','17','KOTA',NULL,NULL,NULL,'-4.2000','102.4000'),(1702,'KABUPATEN REJANG LEBONG',NULL,'1702','17','KOTA',NULL,NULL,NULL,'-3.1667','102.4500'),(1703,'KABUPATEN BENGKULU UTARA',NULL,'1703','17','KOTA',NULL,NULL,NULL,'-3.5000','102.4667'),(1704,'KABUPATEN KAUR',NULL,'1704','17','KOTA',NULL,NULL,NULL,'-4.6833','103.1333'),(1705,'KABUPATEN SELUMA',NULL,'1705','17','KOTA',NULL,NULL,NULL,'-4.0333','102.1667'),(1706,'KABUPATEN MUKOMUKO',NULL,'1706','17','KOTA',NULL,NULL,NULL,'-3.8167','102.0667'),(1707,'KABUPATEN LEBONG',NULL,'1707','17','KOTA',NULL,NULL,NULL,'-3.8333','102.2167'),(1708,'KABUPATEN KEPAHIANG',NULL,'1708','17','KOTA',NULL,NULL,NULL,'-3.3333','102.3500'),(1709,'KABUPATEN BENGKULU TENGAH',NULL,'1709','17','KOTA',NULL,NULL,NULL,'-3.8333','102.5000'),(1771,'KOTA BENGKULU',NULL,'1771','17','KOTA',NULL,NULL,NULL,'-3.8000','102.2667'),(1801,'KABUPATEN LAMPUNG BARAT',NULL,'1801','18','KOTA',NULL,NULL,NULL,'-5.5514','104.8394'),(1802,'KABUPATEN TANGGAMUS',NULL,'1802','18','KOTA',NULL,NULL,NULL,'-5.2167','104.8667'),(1803,'KABUPATEN LAMPUNG SELATAN',NULL,'1803','18','KOTA',NULL,NULL,NULL,'-5.5850','105.4000'),(1804,'KABUPATEN LAMPUNG TIMUR',NULL,'1804','18','KOTA',NULL,NULL,NULL,'-4.0022','105.7175'),(1805,'KABUPATEN LAMPUNG TENGAH',NULL,'1805','18','KOTA',NULL,NULL,NULL,'-4.2494','105.2892'),(1806,'KABUPATEN LAMPUNG UTARA',NULL,'1806','18','KOTA',NULL,NULL,NULL,'-4.5122','104.4694'),(1807,'KABUPATEN WAY KANAN',NULL,'1807','18','KOTA',NULL,NULL,NULL,'-4.4481','104.1442'),(1808,'KABUPATEN TULANGBAWANG',NULL,'1808','18','KOTA',NULL,NULL,NULL,'-4.0297','105.4897'),(1809,'KABUPATEN PESAWARAN',NULL,'1809','18','KOTA',NULL,NULL,NULL,'-5.2500','105.5000'),(1810,'KABUPATEN PRINGSEWU',NULL,'1810','18','KOTA',NULL,NULL,NULL,'-5.3097','105.1592'),(1811,'KABUPATEN MESUJI',NULL,'1811','18','KOTA',NULL,NULL,NULL,'-4.4291','104.6691'),(1812,'KABUPATEN TULANG BAWANG BARAT',NULL,'1812','18','KOTA',NULL,NULL,NULL,'-4.7833','104.4417'),(1813,'KABUPATEN PESISIR BARAT',NULL,'1813','18','KOTA',NULL,NULL,NULL,'-5.5450','104.4383'),(1871,'KOTA BANDAR LAMPUNG',NULL,'1871','18','KOTA',NULL,NULL,NULL,'-5.4275','105.2575'),(1872,'KOTA METRO',NULL,'1872','18','KOTA',NULL,NULL,NULL,'-5.1167','105.3170'),(1901,'KABUPATEN BANGKA',NULL,'1901','19','KOTA',NULL,NULL,NULL,'-2.2000','106.1167'),(1902,'KABUPATEN BELITUNG',NULL,'1902','19','KOTA',NULL,NULL,NULL,'-2.4333','106.1167'),(1903,'KABUPATEN BANGKA BARAT',NULL,'1903','19','KOTA',NULL,NULL,NULL,'-2.2667','105.9833'),(1904,'KABUPATEN BANGKA TENGAH',NULL,'1904','19','KOTA',NULL,NULL,NULL,'-2.7833','106.5000'),(1905,'KABUPATEN BANGKA SELATAN',NULL,'1905','19','KOTA',NULL,NULL,NULL,'-3.0500','106.2000'),(1906,'KABUPATEN BELITUNG TIMUR',NULL,'1906','19','KOTA',NULL,NULL,NULL,'-2.7000','106.1167'),(1971,'KOTA PANGKAL PINANG',NULL,'1971','19','KOTA',NULL,NULL,NULL,'-2.1333','106.1167'),(2101,'KABUPATEN KARIMUN',NULL,'2101','21','KOTA',NULL,NULL,NULL,'0.9000','102.7000'),(2102,'KABUPATEN BINTAN',NULL,'2102','21','KOTA',NULL,NULL,NULL,'0.8500','104.5000'),(2103,'KABUPATEN NATUNA',NULL,'2103','21','KOTA',NULL,NULL,NULL,'3.9500','108.0000'),(2104,'KABUPATEN LINGGA',NULL,'2104','21','KOTA',NULL,NULL,NULL,'0.3500','104.6000'),(2105,'KABUPATEN KEPULAUAN ANAMBAS',NULL,'2105','21','KOTA',NULL,NULL,NULL,'3.1000','105.4000'),(2171,'KOTA BATAM',NULL,'2171','21','KOTA',NULL,NULL,NULL,'1.0500','104.3000'),(2172,'KOTA TANJUNG PINANG',NULL,'2172','21','KOTA',NULL,NULL,NULL,'0.9167','104.4500'),(3101,'KABUPATEN KEPULAUAN SERIBU',NULL,'3101','31','KOTA',NULL,NULL,NULL,'-5.7500','106.7000'),(3171,'KOTA JAKARTA SELATAN',NULL,'3171','31','KOTA',NULL,NULL,NULL,'-6.2900','106.7950'),(3172,'KOTA JAKARTA TIMUR',NULL,'3172','31','KOTA',NULL,NULL,NULL,'-6.2267','106.8833'),(3173,'KOTA JAKARTA PUSAT',NULL,'3173','31','KOTA',NULL,NULL,NULL,'-6.1775','106.8272'),(3174,'KOTA JAKARTA BARAT',NULL,'3174','31','KOTA',NULL,NULL,NULL,'-6.2000','106.7667'),(3175,'KOTA JAKARTA UTARA',NULL,'3175','31','KOTA',NULL,NULL,NULL,'-6.1250','106.9000'),(3201,'KABUPATEN BOGOR',NULL,'3201','32','KOTA',NULL,NULL,NULL,'-6.5950','106.7890'),(3202,'KABUPATEN SUKABUMI',NULL,'3202','32','KOTA',NULL,NULL,NULL,'-6.9333','106.9500'),(3203,'KABUPATEN CIANJUR',NULL,'3203','32','KOTA',NULL,NULL,NULL,'-6.9667','107.1667'),(3204,'KABUPATEN BANDUNG',NULL,'3204','32','KOTA',NULL,NULL,NULL,'-7.0333','107.6215'),(3205,'KABUPATEN GARUT',NULL,'3205','32','KOTA',NULL,NULL,NULL,'-7.2250','107.9450'),(3206,'KABUPATEN TASIKMALAYA',NULL,'3206','32','KOTA',NULL,NULL,NULL,'-7.6167','108.2167'),(3207,'KABUPATEN CIAMIS',NULL,'3207','32','KOTA',NULL,NULL,NULL,'-7.3667','108.1667'),(3208,'KABUPATEN KUNINGAN',NULL,'3208','32','KOTA',NULL,NULL,NULL,'-6.9667','108.5000'),(3209,'KABUPATEN CIREBON',NULL,'3209','32','KOTA',NULL,NULL,NULL,'-6.7000','108.5500'),(3210,'KABUPATEN MAJALENGKA',NULL,'3210','32','KOTA',NULL,NULL,NULL,'-6.8167','108.2333'),(3211,'KABUPATEN SUMEDANG',NULL,'3211','32','KOTA',NULL,NULL,NULL,'-6.8500','107.9167'),(3212,'KABUPATEN INDRAMAYU',NULL,'3212','32','KOTA',NULL,NULL,NULL,'-6.3167','108.4167'),(3213,'KABUPATEN SUBANG',NULL,'3213','32','KOTA',NULL,NULL,NULL,'-6.6533','107.7533'),(3214,'KABUPATEN PURWAKARTA',NULL,'3214','32','KOTA',NULL,NULL,NULL,'-6.5264','107.5539'),(3215,'KABUPATEN KARAWANG',NULL,'3215','32','KOTA',NULL,NULL,NULL,'-6.3500','107.3000'),(3216,'KABUPATEN BEKASI',NULL,'3216','32','KOTA',NULL,NULL,NULL,'-6.2333','106.9833'),(3217,'KABUPATEN BANDUNG BARAT',NULL,'3217','32','KOTA',NULL,NULL,NULL,'-6.9514','107.5708'),(3218,'KABUPATEN PANGANDARAN',NULL,'3218','32','KOTA',NULL,NULL,NULL,'-7.7000','108.6833'),(3271,'KOTA BOGOR',NULL,'3271','32','KOTA',NULL,NULL,NULL,'-6.5950','106.7890'),(3272,'KOTA SUKABUMI',NULL,'3272','32','KOTA',NULL,NULL,NULL,'-6.9333','106.9500'),(3273,'KOTA BANDUNG',NULL,'3273','32','KOTA',NULL,NULL,NULL,'-6.9175','107.6191'),(3274,'KOTA CIREBON',NULL,'3274','32','KOTA',NULL,NULL,NULL,'-6.7000','108.5500'),(3275,'KOTA BEKASI',NULL,'3275','32','KOTA',NULL,NULL,NULL,'-6.2333','106.9833'),(3276,'KOTA DEPOK',NULL,'3276','32','KOTA',NULL,NULL,NULL,'-6.4020','106.7920'),(3277,'KOTA CIMAHI',NULL,'3277','32','KOTA',NULL,NULL,NULL,'-6.8900','107.5500'),(3278,'KOTA TASIKMALAYA',NULL,'3278','32','KOTA',NULL,NULL,NULL,'-7.3200','108.2167'),(3279,'KOTA BANJAR',NULL,'3279','32','KOTA',NULL,NULL,NULL,'-7.3333','108.5500'),(3301,'KABUPATEN CILACAP',NULL,'3301','33','KOTA',NULL,NULL,NULL,'-7.7000','108.9833'),(3302,'KABUPATEN BANYUMAS',NULL,'3302','33','KOTA',NULL,NULL,NULL,'-7.4167','109.3833'),(3303,'KABUPATEN PURBALINGGA',NULL,'3303','33','KOTA',NULL,NULL,NULL,'-7.3667','109.3667'),(3304,'KABUPATEN BANJARNEGARA',NULL,'3304','33','KOTA',NULL,NULL,NULL,'-7.4167','109.9000'),(3305,'KABUPATEN KEBUMEN',NULL,'3305','33','KOTA',NULL,NULL,NULL,'-7.6833','109.6333'),(3306,'KABUPATEN PURWOREJO',NULL,'3306','33','KOTA',NULL,NULL,NULL,'-7.7000','109.9000'),(3307,'KABUPATEN WONOSOBO',NULL,'3307','33','KOTA',NULL,NULL,NULL,'-7.4000','109.9833'),(3308,'KABUPATEN MAGELANG',NULL,'3308','33','KOTA',NULL,NULL,NULL,'-7.4708','110.2281'),(3309,'KABUPATEN BOYOLALI',NULL,'3309','33','KOTA',NULL,NULL,NULL,'-7.5333','110.6167'),(3310,'KABUPATEN KLATEN',NULL,'3310','33','KOTA',NULL,NULL,NULL,'-7.7167','110.6167'),(3311,'KABUPATEN SUKOHARJO',NULL,'3311','33','KOTA',NULL,NULL,NULL,'-7.5000','110.8333'),(3312,'KABUPATEN WONOGIRI',NULL,'3312','33','KOTA',NULL,NULL,NULL,'-7.6333','111.0333'),(3313,'KABUPATEN KARANGANYAR',NULL,'3313','33','KOTA',NULL,NULL,NULL,'-7.5833','111.4167'),(3314,'KABUPATEN SRAGEN',NULL,'3314','33','KOTA',NULL,NULL,NULL,'-7.3500','111.0167'),(3315,'KABUPATEN GROBOGAN',NULL,'3315','33','KOTA',NULL,NULL,NULL,'-7.3000','110.9500'),(3316,'KABUPATEN BLORA',NULL,'3316','33','KOTA',NULL,NULL,NULL,'-7.0167','111.5000'),(3317,'KABUPATEN REMBANG',NULL,'3317','33','KOTA',NULL,NULL,NULL,'-6.7000','111.3000'),(3318,'KABUPATEN PATI',NULL,'3318','33','KOTA',NULL,NULL,NULL,'-6.7500','111.0000'),(3319,'KABUPATEN KUDUS',NULL,'3319','33','KOTA',NULL,NULL,NULL,'-6.8833','110.8333'),(3320,'KABUPATEN JEPARA',NULL,'3320','33','KOTA',NULL,NULL,NULL,'-6.5333','110.6333'),(3321,'KABUPATEN DEMAK',NULL,'3321','33','KOTA',NULL,NULL,NULL,'-6.9667','110.6500'),(3322,'KABUPATEN SEMARANG',NULL,'3322','33','KOTA',NULL,NULL,NULL,'-7.0167','110.4167'),(3323,'KABUPATEN TEMANGGUNG',NULL,'3323','33','KOTA',NULL,NULL,NULL,'-7.5167','110.1667'),(3324,'KABUPATEN KENDAL',NULL,'3324','33','KOTA',NULL,NULL,NULL,'-7.1667','109.8667'),(3325,'KABUPATEN BATANG',NULL,'3325','33','KOTA',NULL,NULL,NULL,'-6.8833','109.7500'),(3326,'KABUPATEN PEKALONGAN',NULL,'3326','33','KOTA',NULL,NULL,NULL,'-6.8833','109.6667'),(3327,'KABUPATEN PEMALANG',NULL,'3327','33','KOTA',NULL,NULL,NULL,'-7.1000','109.3667'),(3328,'KABUPATEN TEGAL',NULL,'3328','33','KOTA',NULL,NULL,NULL,'-6.8833','109.1667'),(3329,'KABUPATEN BREBES',NULL,'3329','33','KOTA',NULL,NULL,NULL,'-6.8667','108.5667'),(3371,'KOTA MAGELANG',NULL,'3371','33','KOTA',NULL,NULL,NULL,'-7.4708','110.2281'),(3372,'KOTA SURAKARTA',NULL,'3372','33','KOTA',NULL,NULL,NULL,'-7.5667','110.8236'),(3373,'KOTA SALATIGA',NULL,'3373','33','KOTA',NULL,NULL,NULL,'-7.3333','110.5000'),(3374,'KOTA SEMARANG',NULL,'3374','33','KOTA',NULL,NULL,NULL,'-6.9667','110.4167'),(3375,'KOTA PEKALONGAN',NULL,'3375','33','KOTA',NULL,NULL,NULL,'-6.8911','109.6667'),(3376,'KOTA TEGAL',NULL,'3376','33','KOTA',NULL,NULL,NULL,'-6.8689','109.1397'),(3401,'KABUPATEN KULON PROGO',NULL,'3401','34','KOTA',NULL,NULL,NULL,'-7.8167','110.0000'),(3402,'KABUPATEN BANTUL',NULL,'3402','34','KOTA',NULL,NULL,NULL,'-7.8547','110.3297'),(3403,'KABUPATEN GUNUNG KIDUL',NULL,'3403','34','KOTA',NULL,NULL,NULL,'-8.0000','110.5000'),(3404,'KABUPATEN SLEMAN',NULL,'3404','34','KOTA',NULL,NULL,NULL,'-7.5833','110.4139'),(3471,'KOTA YOGYAKARTA',NULL,'3471','34','KOTA',NULL,NULL,NULL,'-7.7956','110.3695'),(3501,'KABUPATEN PACITAN',NULL,'3501','35','KOTA',NULL,NULL,NULL,'-8.1833','111.4000'),(3502,'KABUPATEN PONOROGO',NULL,'3502','35','KOTA',NULL,NULL,NULL,'-7.8667','111.5000'),(3503,'KABUPATEN TRENGGALEK',NULL,'3503','35','KOTA',NULL,NULL,NULL,'-8.1167','111.6333'),(3504,'KABUPATEN TULUNGAGUNG',NULL,'3504','35','KOTA',NULL,NULL,NULL,'-8.0667','111.8833'),(3505,'KABUPATEN BLITAR',NULL,'3505','35','KOTA',NULL,NULL,NULL,'-8.1150','112.2256'),(3506,'KABUPATEN KEDIRI',NULL,'3506','35','KOTA',NULL,NULL,NULL,'-7.7425','112.0133'),(3507,'KABUPATEN MALANG',NULL,'3507','35','KOTA',NULL,NULL,NULL,'-8.0597','112.5247'),(3508,'KABUPATEN LUMAJANG',NULL,'3508','35','KOTA',NULL,NULL,NULL,'-8.1328','113.2422'),(3509,'KABUPATEN JEMBER',NULL,'3509','35','KOTA',NULL,NULL,NULL,'-8.1689','113.6917'),(3510,'KABUPATEN BANYUWANGI',NULL,'3510','35','KOTA',NULL,NULL,NULL,'-8.2167','114.3667'),(3511,'KABUPATEN BONDOWOSO',NULL,'3511','35','KOTA',NULL,NULL,NULL,'-7.9692','113.8206'),(3512,'KABUPATEN SITUBONDO',NULL,'3512','35','KOTA',NULL,NULL,NULL,'-7.6956','114.0672'),(3513,'KABUPATEN PROBOLINGGO',NULL,'3513','35','KOTA',NULL,NULL,NULL,'-7.8425','113.2164'),(3514,'KABUPATEN PASURUAN',NULL,'3514','35','KOTA',NULL,NULL,NULL,'-7.6397','112.9078'),(3515,'KABUPATEN SIDOARJO',NULL,'3515','35','KOTA',NULL,NULL,NULL,'-7.4667','112.7178'),(3516,'KABUPATEN MOJOKERTO',NULL,'3516','35','KOTA',NULL,NULL,NULL,'-7.4667','112.4333'),(3517,'KABUPATEN JOMBANG',NULL,'3517','35','KOTA',NULL,NULL,NULL,'-7.5556','112.2278'),(3518,'KABUPATEN NGANJUK',NULL,'3518','35','KOTA',NULL,NULL,NULL,'-7.6111','111.9278'),(3519,'KABUPATEN MADIUN',NULL,'3519','35','KOTA',NULL,NULL,NULL,'-7.6297','111.5278'),(3520,'KABUPATEN MAGETAN',NULL,'3520','35','KOTA',NULL,NULL,NULL,'-7.6450','111.4364'),(3521,'KABUPATEN NGAWI',NULL,'3521','35','KOTA',NULL,NULL,NULL,'-7.4178','111.4417'),(3522,'KABUPATEN BOJONEGORO',NULL,'3522','35','KOTA',NULL,NULL,NULL,'-7.1500','111.8833'),(3523,'KABUPATEN TUBAN',NULL,'3523','35','KOTA',NULL,NULL,NULL,'-6.8961','112.0397'),(3524,'KABUPATEN LAMONGAN',NULL,'3524','35','KOTA',NULL,NULL,NULL,'-7.1433','112.3892'),(3525,'KABUPATEN GRESIK',NULL,'3525','35','KOTA',NULL,NULL,NULL,'-7.1542','112.6572'),(3526,'KABUPATEN BANGKALAN',NULL,'3526','35','KOTA',NULL,NULL,NULL,'-7.0536','113.5958'),(3527,'KABUPATEN SAMPANG',NULL,'3527','35','KOTA',NULL,NULL,NULL,'-7.2697','113.2342'),(3528,'KABUPATEN PAMEKASAN',NULL,'3528','35','KOTA',NULL,NULL,NULL,'-7.1428','113.4100'),(3529,'KABUPATEN SUMENEP',NULL,'3529','35','KOTA',NULL,NULL,NULL,'-7.0319','113.8833'),(3571,'KOTA KEDIRI',NULL,'3571','35','KOTA',NULL,NULL,NULL,'-7.8386','112.0114'),(3572,'KOTA BLITAR',NULL,'3572','35','KOTA',NULL,NULL,NULL,'-8.1150','112.2256'),(3573,'KOTA MALANG',NULL,'3573','35','KOTA',NULL,NULL,NULL,'-7.9667','112.6328'),(3574,'KOTA PROBOLINGGO',NULL,'3574','35','KOTA',NULL,NULL,NULL,'-7.8425','113.2164'),(3575,'KOTA PASURUAN',NULL,'3575','35','KOTA',NULL,NULL,NULL,'-7.6419','112.9186'),(3576,'KOTA MOJOKERTO',NULL,'3576','35','KOTA',NULL,NULL,NULL,'-7.4667','112.4333'),(3577,'KOTA MADIUN',NULL,'3577','35','KOTA',NULL,NULL,NULL,'-7.6333','111.5167'),(3578,'KOTA SURABAYA',NULL,'3578','35','KOTA',NULL,NULL,NULL,'-7.2504','112.7688'),(3579,'KOTA BATU',NULL,'3579','35','KOTA',NULL,NULL,NULL,'-7.9136','112.5203'),(3601,'KABUPATEN PANDEGLANG',NULL,'3601','36','KOTA',NULL,NULL,NULL,'-6.3078','105.1083'),(3602,'KABUPATEN LEBAK',NULL,'3602','36','KOTA',NULL,NULL,NULL,'-6.5078','106.3172'),(3603,'KABUPATEN TANGERANG',NULL,'3603','36','KOTA',NULL,NULL,NULL,'-6.1578','106.5514'),(3604,'KABUPATEN SERANG',NULL,'3604','36','KOTA',NULL,NULL,NULL,'-6.1500','106.1400'),(3671,'KOTA TANGERANG',NULL,'3671','36','KOTA',NULL,NULL,NULL,'-6.1785','106.6303'),(3672,'KOTA CILEGON',NULL,'3672','36','KOTA',NULL,NULL,NULL,'-6.0019','106.0561'),(3673,'KOTA SERANG',NULL,'3673','36','KOTA',NULL,NULL,NULL,'-6.1142','106.1556'),(3674,'KOTA TANGERANG SELATAN',NULL,'3674','36','KOTA',NULL,NULL,NULL,'-6.2967','106.6528'),(5101,'KABUPATEN JEMBRANA',NULL,'5101','51','KOTA',NULL,NULL,NULL,'-8.1697','114.4800'),(5102,'KABUPATEN TABANAN',NULL,'5102','51','KOTA',NULL,NULL,NULL,'-8.5356','115.0917'),(5103,'KABUPATEN BADUNG',NULL,'5103','51','KOTA',NULL,NULL,NULL,'-8.6047','115.1686'),(5104,'KABUPATEN GIANYAR',NULL,'5104','51','KOTA',NULL,NULL,NULL,'-8.5247','115.2667'),(5105,'KABUPATEN KLUNGKUNG',NULL,'5105','51','KOTA',NULL,NULL,NULL,'-8.5447','115.3897'),(5106,'KABUPATEN BANGLI',NULL,'5106','51','KOTA',NULL,NULL,NULL,'-8.4300','115.2850'),(5107,'KABUPATEN KARANG ASEM',NULL,'5107','51','KOTA',NULL,NULL,NULL,'-8.3478','115.5378'),(5108,'KABUPATEN BULELENG',NULL,'5108','51','KOTA',NULL,NULL,NULL,'-8.1242','114.9600'),(5171,'KOTA DENPASAR',NULL,'5171','51','KOTA',NULL,NULL,NULL,'-8.4095','115.1889'),(5201,'KABUPATEN LOMBOK BARAT',NULL,'5201','52','KOTA',NULL,NULL,NULL,'-8.5756','116.1036'),(5202,'KABUPATEN LOMBOK TENGAH',NULL,'5202','52','KOTA',NULL,NULL,NULL,'-8.6833','116.2500'),(5203,'KABUPATEN LOMBOK TIMUR',NULL,'5203','52','KOTA',NULL,NULL,NULL,'-8.5097','116.5111'),(5204,'KABUPATEN SUMBAWA',NULL,'5204','52','KOTA',NULL,NULL,NULL,'-8.3497','116.4678'),(5205,'KABUPATEN DOMPU',NULL,'5205','52','KOTA',NULL,NULL,NULL,'-8.4811','118.6250'),(5206,'KABUPATEN BIMA',NULL,'5206','52','KOTA',NULL,NULL,NULL,'-8.4600','118.7178'),(5207,'KABUPATEN SUMBAWA BARAT',NULL,'5207','52','KOTA',NULL,NULL,NULL,'-8.9014','116.7228'),(5208,'KABUPATEN LOMBOK UTARA',NULL,'5208','52','KOTA',NULL,NULL,NULL,'-8.3747','116.4692'),(5271,'KOTA MATARAM',NULL,'5271','52','KOTA',NULL,NULL,NULL,'-8.5833','116.1167'),(5272,'KOTA BIMA',NULL,'5272','52','KOTA',NULL,NULL,NULL,'-8.4531','118.7314'),(5301,'KABUPATEN SUMBA BARAT',NULL,'5301','53','KOTA',NULL,NULL,NULL,'-9.5778','119.0247'),(5302,'KABUPATEN SUMBA TIMUR',NULL,'5302','53','KOTA',NULL,NULL,NULL,'-9.6806','119.1000'),(5303,'KABUPATEN KUPANG',NULL,'5303','53','KOTA',NULL,NULL,NULL,'-10.2014','123.6806'),(5304,'KABUPATEN TIMOR TENGAH SELATAN',NULL,'5304','53','KOTA',NULL,NULL,NULL,'-9.3600','124.2736'),(5305,'KABUPATEN TIMOR TENGAH UTARA',NULL,'5305','53','KOTA',NULL,NULL,NULL,'-9.1500','124.5000'),(5306,'KABUPATEN BELU',NULL,'5306','53','KOTA',NULL,NULL,NULL,'-9.0681','124.5019'),(5307,'KABUPATEN ALOR',NULL,'5307','53','KOTA',NULL,NULL,NULL,'-8.1619','123.1864'),(5308,'KABUPATEN LEMBATA',NULL,'5308','53','KOTA',NULL,NULL,NULL,'-8.3036','123.5556'),(5309,'KABUPATEN FLORES TIMUR',NULL,'5309','53','KOTA',NULL,NULL,NULL,'-8.2797','122.0247'),(5310,'KABUPATEN SIKKA',NULL,'5310','53','KOTA',NULL,NULL,NULL,'-8.6564','122.1600'),(5311,'KABUPATEN ENDE',NULL,'5311','53','KOTA',NULL,NULL,NULL,'-8.8564','121.6500'),(5312,'KABUPATEN NGADA',NULL,'5312','53','KOTA',NULL,NULL,NULL,'-8.5536','121.0578'),(5313,'KABUPATEN MANGGARAI',NULL,'5313','53','KOTA',NULL,NULL,NULL,'-8.6078','120.5319'),(5314,'KABUPATEN ROTE NDAO',NULL,'5314','53','KOTA',NULL,NULL,NULL,'-10.7667','123.0678'),(5315,'KABUPATEN MANGGARAI BARAT',NULL,'5315','53','KOTA',NULL,NULL,NULL,'-8.6014','119.0319'),(5316,'KABUPATEN SUMBA TENGAH',NULL,'5316','53','KOTA',NULL,NULL,NULL,'-9.4000','119.2500'),(5317,'KABUPATEN SUMBA BARAT DAYA',NULL,'5317','53','KOTA',NULL,NULL,NULL,'-9.3806','119.2678'),(5318,'KABUPATEN NAGEKEO',NULL,'5318','53','KOTA',NULL,NULL,NULL,'-8.7525','120.6264'),(5319,'KABUPATEN MANGGARAI TIMUR',NULL,'5319','53','KOTA',NULL,NULL,NULL,'-8.3667','120.5000'),(5320,'KABUPATEN SABU RAIJUA',NULL,'5320','53','KOTA',NULL,NULL,NULL,'-9.2044','119.5578'),(5321,'KABUPATEN MALAKA',NULL,'5321','53','KOTA',NULL,NULL,NULL,'-9.4697','124.3497'),(5371,'KOTA KUPANG',NULL,'5371','53','KOTA',NULL,NULL,NULL,'-10.1603','123.5958'),(6101,'KABUPATEN SAMBAS',NULL,'6101','61','KOTA',NULL,NULL,NULL,'1.5106','109.2683'),(6102,'KABUPATEN BENGKAYANG',NULL,'6102','61','KOTA',NULL,NULL,NULL,'1.7400','109.5075'),(6103,'KABUPATEN LANDAK',NULL,'6103','61','KOTA',NULL,NULL,NULL,'0.6167','109.5000'),(6104,'KABUPATEN MEMPAWAH',NULL,'6104','61','KOTA',NULL,NULL,NULL,'0.8850','109.3086'),(6105,'KABUPATEN SANGGAU',NULL,'6105','61','KOTA',NULL,NULL,NULL,'0.0422','110.4356'),(6106,'KABUPATEN KETAPANG',NULL,'6106','61','KOTA',NULL,NULL,NULL,'-1.9981','109.9828'),(6107,'KABUPATEN SINTANG',NULL,'6107','61','KOTA',NULL,NULL,NULL,'0.2378','111.4892'),(6108,'KABUPATEN KAPUAS HULU',NULL,'6108','61','KOTA',NULL,NULL,NULL,'1.1572','112.6436'),(6109,'KABUPATEN SEKADAU',NULL,'6109','61','KOTA',NULL,NULL,NULL,'0.9194','111.9747'),(6110,'KABUPATEN MELAWI',NULL,'6110','61','KOTA',NULL,NULL,NULL,'0.4556','111.8111'),(6111,'KABUPATEN KAYONG UTARA',NULL,'6111','61','KOTA',NULL,NULL,NULL,'1.2461','109.3297'),(6112,'KABUPATEN KUBU RAYA',NULL,'6112','61','KOTA',NULL,NULL,NULL,'0.0336','109.3494'),(6171,'KOTA PONTIANAK',NULL,'6171','61','KOTA',NULL,NULL,NULL,'-0.0236','109.3389'),(6172,'KOTA SINGKAWANG',NULL,'6172','61','KOTA',NULL,NULL,NULL,'1.0536','109.9750'),(6201,'KABUPATEN KOTAWARINGIN BARAT',NULL,'6201','62','KOTA',NULL,NULL,NULL,'-2.3497','111.6208'),(6202,'KABUPATEN KOTAWARINGIN TIMUR',NULL,'6202','62','KOTA',NULL,NULL,NULL,'-2.0900','113.0667'),(6203,'KABUPATEN KAPUAS',NULL,'6203','62','KOTA',NULL,NULL,NULL,'-0.1997','113.6111'),(6204,'KABUPATEN BARITO SELATAN',NULL,'6204','62','KOTA',NULL,NULL,NULL,'-2.7208','115.1497'),(6205,'KABUPATEN BARITO UTARA',NULL,'6205','62','KOTA',NULL,NULL,NULL,'-2.6444','115.5064'),(6206,'KABUPATEN SUKAMARA',NULL,'6206','62','KOTA',NULL,NULL,NULL,'-2.5736','113.4783'),(6207,'KABUPATEN LAMANDAU',NULL,'6207','62','KOTA',NULL,NULL,NULL,'-2.0561','112.8686'),(6208,'KABUPATEN SERUYAN',NULL,'6208','62','KOTA',NULL,NULL,NULL,'-2.5361','113.5844'),(6209,'KABUPATEN KATINGAN',NULL,'6209','62','KOTA',NULL,NULL,NULL,'-1.9794','113.0300'),(6210,'KABUPATEN PULANG PISAU',NULL,'6210','62','KOTA',NULL,NULL,NULL,'-2.5100','113.8500'),(6211,'KABUPATEN GUNUNG MAS',NULL,'6211','62','KOTA',NULL,NULL,NULL,'-2.4461','113.9764'),(6212,'KABUPATEN BARITO TIMUR',NULL,'6212','62','KOTA',NULL,NULL,NULL,'-1.6694','115.3889'),(6213,'KABUPATEN MURUNG RAYA',NULL,'6213','62','KOTA',NULL,NULL,NULL,'-2.9656','114.6417'),(6271,'KOTA PALANGKA RAYA',NULL,'6271','62','KOTA',NULL,NULL,NULL,'-2.2111','113.9214'),(6301,'KABUPATEN TANAH LAUT',NULL,'6301','63','KOTA',NULL,NULL,NULL,'-3.4167','114.8333'),(6302,'KABUPATEN KOTA BARU',NULL,'6302','63','KOTA',NULL,NULL,NULL,'-3.3667','114.9500'),(6303,'KABUPATEN BANJAR',NULL,'6303','63','KOTA',NULL,NULL,NULL,'-3.3581','114.7431'),(6304,'KABUPATEN BARITO KUALA',NULL,'6304','63','KOTA',NULL,NULL,NULL,'-3.0428','114.8261'),(6305,'KABUPATEN TAPIN',NULL,'6305','63','KOTA',NULL,NULL,NULL,'-3.1256','115.0333'),(6306,'KABUPATEN HULU SUNGAI SELATAN',NULL,'6306','63','KOTA',NULL,NULL,NULL,'-3.0667','115.3400'),(6307,'KABUPATEN HULU SUNGAI TENGAH',NULL,'6307','63','KOTA',NULL,NULL,NULL,'-2.7047','115.3486'),(6308,'KABUPATEN HULU SUNGAI UTARA',NULL,'6308','63','KOTA',NULL,NULL,NULL,'-2.5714','115.2972'),(6309,'KABUPATEN TABALONG',NULL,'6309','63','KOTA',NULL,NULL,NULL,'-2.8783','115.2514'),(6310,'KABUPATEN TANAH BUMBU',NULL,'6310','63','KOTA',NULL,NULL,NULL,'-3.4167','115.5167'),(6311,'KABUPATEN BALANGAN',NULL,'6311','63','KOTA',NULL,NULL,NULL,'-2.8014','115.3667'),(6371,'KOTA BANJARMASIN',NULL,'6371','63','KOTA',NULL,NULL,NULL,'-3.3192','114.5940'),(6372,'KOTA BANJAR BARU',NULL,'6372','63','KOTA',NULL,NULL,NULL,'-3.4167','114.5667'),(6401,'KABUPATEN PASER',NULL,'6401','64','KOTA',NULL,NULL,NULL,'-1.2497','116.9500'),(6402,'KABUPATEN KUTAI BARAT',NULL,'6402','64','KOTA',NULL,NULL,NULL,'-1.7500','115.6333'),(6403,'KABUPATEN KUTAI KARTANEGARA',NULL,'6403','64','KOTA',NULL,NULL,NULL,'-0.3111','117.1514'),(6404,'KABUPATEN KUTAI TIMUR',NULL,'6404','64','KOTA',NULL,NULL,NULL,'-0.4167','117.6000'),(6405,'KABUPATEN BERAU',NULL,'6405','64','KOTA',NULL,NULL,NULL,'2.0500','117.5000'),(6409,'KABUPATEN PENAJAM PASER UTARA',NULL,'6409','64','KOTA',NULL,NULL,NULL,'1.0586','116.9200'),(6411,'KABUPATEN MAHAKAM HULU',NULL,'6411','64','KOTA',NULL,NULL,NULL,'-0.7597','116.7558'),(6471,'KOTA BALIKPAPAN',NULL,'6471','64','KOTA',NULL,NULL,NULL,'-1.2561','116.8917'),(6472,'KOTA SAMARINDA',NULL,'6472','64','KOTA',NULL,NULL,NULL,'-0.5033','117.1494'),(6474,'KOTA BONTANG',NULL,'6474','64','KOTA',NULL,NULL,NULL,'0.1333','117.5014'),(6501,'KABUPATEN MALINAU',NULL,'6501','65','KOTA',NULL,NULL,NULL,'3.1267','116.8722'),(6502,'KABUPATEN BULUNGAN',NULL,'6502','65','KOTA',NULL,NULL,NULL,'3.0181','117.4872'),(6503,'KABUPATEN TANA TIDUNG',NULL,'6503','65','KOTA',NULL,NULL,NULL,'3.4111','116.7733'),(6504,'KABUPATEN NUNUKAN',NULL,'6504','65','KOTA',NULL,NULL,NULL,'4.1833','117.4667'),(6571,'KOTA TARAKAN',NULL,'6571','65','KOTA',NULL,NULL,NULL,'3.3256','117.6014'),(7501,'KABUPATEN BOALEMO',NULL,'7501','75','KOTA',NULL,NULL,NULL,'1.1806','122.6361'),(7502,'KABUPATEN GORONTALO',NULL,'7502','75','KOTA',NULL,NULL,NULL,'0.5411','123.0700'),(7503,'KABUPATEN POHUWATO',NULL,'7503','75','KOTA',NULL,NULL,NULL,'0.8039','120.7183'),(7504,'KABUPATEN BONE BOLANGO',NULL,'7504','75','KOTA',NULL,NULL,NULL,'0.7833','123.3667'),(7505,'KABUPATEN GORONTALO UTARA',NULL,'7505','75','KOTA',NULL,NULL,NULL,'0.7364','123.1361'),(7571,'KOTA GORONTALO',NULL,'7571','75','KOTA',NULL,NULL,NULL,'0.5411','123.0700'),(7601,'KABUPATEN MAJENE',NULL,'7601','76','KOTA',NULL,NULL,NULL,'-3.5072','118.9769'),(7602,'KABUPATEN POLEWALI MANDAR',NULL,'7602','76','KOTA',NULL,NULL,NULL,'-3.0167','119.2500'),(7603,'KABUPATEN MAMASA',NULL,'7603','76','KOTA',NULL,NULL,NULL,'-2.7672','119.5000'),(7604,'KABUPATEN MAMUJU',NULL,'7604','76','KOTA',NULL,NULL,NULL,'-2.6217','118.9461'),(7605,'KABUPATEN MAMUJU UTARA',NULL,'7605','76','KOTA',NULL,NULL,NULL,'-2.8047','118.4747'),(7606,'KABUPATEN MAMUJU TENGAH',NULL,'7606','76','KOTA',NULL,NULL,NULL,'-3.5972','118.6244'),(8101,'KABUPATEN MALUKU TENGGARA BARAT',NULL,'8101','81','KOTA',NULL,NULL,NULL,'-6.3350','134.7769'),(8102,'KABUPATEN MALUKU TENGGARA',NULL,'8102','81','KOTA',NULL,NULL,NULL,'-5.0400','134.6000'),(8103,'KABUPATEN MALUKU TENGAH',NULL,'8103','81','KOTA',NULL,NULL,NULL,'-3.4850','129.3750'),(8104,'KABUPATEN BURU',NULL,'8104','81','KOTA',NULL,NULL,NULL,'-3.3333','126.7500'),(8105,'KABUPATEN KEPULAUAN ARU',NULL,'8105','81','KOTA',NULL,NULL,NULL,'-6.2000','131.4000'),(8106,'KABUPATEN SERAM BAGIAN BARAT',NULL,'8106','81','KOTA',NULL,NULL,NULL,'-3.0911','129.2178'),(8107,'KABUPATEN SERAM BAGIAN TIMUR',NULL,'8107','81','KOTA',NULL,NULL,NULL,'-3.4264','129.6322'),(8108,'KABUPATEN MALUKU BARAT DAYA',NULL,'8108','81','KOTA',NULL,NULL,NULL,'-7.4433','130.2833'),(8109,'KABUPATEN BURU SELATAN',NULL,'8109','81','KOTA',NULL,NULL,NULL,'-3.7500','126.5167'),(8171,'KOTA AMBON',NULL,'8171','81','KOTA',NULL,NULL,NULL,'-3.6957','128.1889'),(8172,'KOTA TUAL',NULL,'8172','81','KOTA',NULL,NULL,NULL,'-5.6672','132.7500'),(8201,'KABUPATEN HALMAHERA BARAT',NULL,'8201','82','KOTA',NULL,NULL,NULL,'1.5064','126.5033'),(8202,'KABUPATEN HALMAHERA TENGAH',NULL,'8202','82','KOTA',NULL,NULL,NULL,'1.9764','127.2708'),(8203,'KABUPATEN KEPULAUAN SULA',NULL,'8203','82','KOTA',NULL,NULL,NULL,'-2.5583','126.5389'),(8204,'KABUPATEN HALMAHERA SELATAN',NULL,'8204','82','KOTA',NULL,NULL,NULL,'2.4000','128.2000'),(8205,'KABUPATEN HALMAHERA UTARA',NULL,'8205','82','KOTA',NULL,NULL,NULL,'1.6500','127.8667'),(8206,'KABUPATEN HALMAHERA TIMUR',NULL,'8206','82','KOTA',NULL,NULL,NULL,'1.5000','128.2000'),(8207,'KABUPATEN PULAU MOROTAI',NULL,'8207','82','KOTA',NULL,NULL,NULL,'2.0500','128.2500'),(8208,'KABUPATEN PULAU TALIABU',NULL,'8208','82','KOTA',NULL,NULL,NULL,'2.7500','125.6750'),(8271,'KOTA TERNATE',NULL,'8271','82','KOTA',NULL,NULL,NULL,'0.8036','127.3797'),(8272,'KOTA TIDORE KEPULAUAN',NULL,'8272','82','KOTA',NULL,NULL,NULL,'0.6911','127.3650'),(9101,'KABUPATEN FAKFAK',NULL,'9101','91','KOTA',NULL,NULL,NULL,'-3.6961','132.9606'),(9102,'KABUPATEN KAIMANA',NULL,'9102','91','KOTA',NULL,NULL,NULL,'-4.0761','133.6644'),(9103,'KABUPATEN TELUK WONDAMA',NULL,'9103','91','KOTA',NULL,NULL,NULL,'-3.1400','134.2483'),(9104,'KABUPATEN TELUK BINTUNI',NULL,'9104','91','KOTA',NULL,NULL,NULL,'-3.0283','133.9600'),(9105,'KABUPATEN MANOKWARI',NULL,'9105','91','KOTA',NULL,NULL,NULL,'-0.8833','134.0500'),(9106,'KABUPATEN SORONG SELATAN',NULL,'9106','91','KOTA',NULL,NULL,NULL,'-1.4722','131.0233'),(9107,'KABUPATEN SORONG',NULL,'9107','91','KOTA',NULL,NULL,NULL,'-0.8833','131.2550'),(9108,'KABUPATEN RAJA AMPAT',NULL,'9108','91','KOTA',NULL,NULL,NULL,'-0.2222','130.5583'),(9109,'KABUPATEN TAMBRAUW',NULL,'9109','91','KOTA',NULL,NULL,NULL,'-1.2317','133.1511'),(9110,'KABUPATEN MAYBRAT',NULL,'9110','91','KOTA',NULL,NULL,NULL,'-1.4906','133.5750'),(9111,'KABUPATEN MANOKWARI SELATAN',NULL,'9111','91','KOTA',NULL,NULL,NULL,'-2.0806','134.5183'),(9112,'KABUPATEN PEGUNUNGAN ARFAK',NULL,'9112','91','KOTA',NULL,NULL,NULL,'-1.2906','134.9078'),(9171,'KOTA SORONG',NULL,'9171','91','KOTA',NULL,NULL,NULL,'-0.8794','131.2578'),(9401,'KABUPATEN MERAUKE',NULL,'9401','94','KOTA',NULL,NULL,NULL,'-8.4850','140.4011'),(9402,'KABUPATEN JAYAWIJAYA',NULL,'9402','94','KOTA',NULL,NULL,NULL,'-4.0939','138.9306'),(9403,'KABUPATEN JAYAPURA',NULL,'9403','94','KOTA',NULL,NULL,NULL,'-2.5320','140.7180'),(9404,'KABUPATEN NABIRE',NULL,'9404','94','KOTA',NULL,NULL,NULL,'-3.3633','135.4722'),(9408,'KABUPATEN KEPULAUAN YAPEN',NULL,'9408','94','KOTA',NULL,NULL,NULL,'-1.0744','139.0422'),(9409,'KABUPATEN BIAK NUMFOR',NULL,'9409','94','KOTA',NULL,NULL,NULL,'-1.1711','136.1467'),(9410,'KABUPATEN PANIAI',NULL,'9410','94','KOTA',NULL,NULL,NULL,'-3.7933','135.6783'),(9411,'KABUPATEN PUNCAK JAYA',NULL,'9411','94','KOTA',NULL,NULL,NULL,'-4.5500','138.9128'),(9412,'KABUPATEN MIMIKA',NULL,'9412','94','KOTA',NULL,NULL,NULL,'-4.5600','137.6122'),(9413,'KABUPATEN BOVEN DIGOEL',NULL,'9413','94','KOTA',NULL,NULL,NULL,'-5.2950','140.3656'),(9414,'KABUPATEN MAPPI',NULL,'9414','94','KOTA',NULL,NULL,NULL,'-5.5100','139.2894'),(9415,'KABUPATEN ASMAT',NULL,'9415','94','KOTA',NULL,NULL,NULL,'-5.5433','137.7211'),(9416,'KABUPATEN YAHUKIMO',NULL,'9416','94','KOTA',NULL,NULL,NULL,'-4.1222','139.7633'),(9417,'KABUPATEN PEGUNUNGAN BINTANG',NULL,'9417','94','KOTA',NULL,NULL,NULL,'-4.5028','140.3494'),(9418,'KABUPATEN TOLIKARA',NULL,'9418','94','KOTA',NULL,NULL,NULL,'-4.1244','139.0806'),(9419,'KABUPATEN SARMI',NULL,'9419','94','KOTA',NULL,NULL,NULL,'-2.0906','139.9144'),(9420,'KABUPATEN KEEROM',NULL,'9420','94','KOTA',NULL,NULL,NULL,'-2.2639','140.2428'),(9426,'KABUPATEN WAROPEN',NULL,'9426','94','KOTA',NULL,NULL,NULL,'-2.6167','136.1250'),(9427,'KABUPATEN SUPIORI',NULL,'9427','94','KOTA',NULL,NULL,NULL,'-1.4911','136.0611'),(9428,'KABUPATEN MAMBERAMO RAYA',NULL,'9428','94','KOTA',NULL,NULL,NULL,'-3.4778','137.8778'),(9429,'KABUPATEN NDUGA',NULL,'9429','94','KOTA',NULL,NULL,NULL,'-4.8597','138.9178'),(9430,'KABUPATEN LANNY JAYA',NULL,'9430','94','KOTA',NULL,NULL,NULL,'-4.4133','138.9306'),(9431,'KABUPATEN MAMBERAMO TENGAH',NULL,'9431','94','KOTA',NULL,NULL,NULL,'-3.5099','138.1314'),(9432,'KABUPATEN YALIMO',NULL,'9432','94','KOTA',NULL,NULL,NULL,'-4.0674','140.7244'),(9433,'KABUPATEN PUNCAK',NULL,'9433','94','KOTA',NULL,NULL,NULL,'-4.2570','138.5522'),(9434,'KABUPATEN DOGIYAI',NULL,'9434','94','KOTA',NULL,NULL,NULL,'-3.5113','137.5633'),(9435,'KABUPATEN INTAN JAYA',NULL,'9435','94','KOTA',NULL,NULL,NULL,'-4.2622','137.9574'),(9436,'KABUPATEN DEIYAI',NULL,'9436','94','KOTA',NULL,NULL,NULL,'-3.4911','139.8647'),(9471,'KOTA JAYAPURA',NULL,'9471','94','KOTA',NULL,NULL,NULL,'-2.5320','140.7180'),(9472,'TEST PROVINSI',NULL,'P24NOV0001',NULL,'PROVINSI','2024-11-28 05:24:00','2024-11-28 05:22:24','2024-11-28 05:24:00',NULL,NULL),(9473,'TEST KOTAS',NULL,'R24NOV0001','11','KOTA','2024-11-28 05:32:46','2024-11-28 05:32:33','2024-11-28 05:32:46',NULL,NULL),(9474,'KABUPATEN BANTAENG',NULL,'R25MAY0001','73','KOTA',NULL,'2025-05-26 09:44:11','2025-05-26 09:44:29',NULL,NULL);
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

-- Dump completed on 2025-10-30 13:00:56
