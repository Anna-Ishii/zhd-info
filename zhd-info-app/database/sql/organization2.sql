-- MySQL dump 10.13  Distrib 5.7.41, for osx10.17 (x86_64)
--
-- Host: 127.0.0.1    Database: laravel
-- ------------------------------------------------------
-- Server version	8.0.32

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `organization2`
--

DROP TABLE IF EXISTS `organization2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organization2` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `organization2_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `organization2`
--

LOCK TABLES `organization2` WRITE;
/*!40000 ALTER TABLE `organization2` DISABLE KEYS */;
INSERT INTO `organization2` VALUES (1,'北海道',NULL,NULL),(2,'東北・北関東',NULL,NULL),(3,'千葉',NULL,NULL),(4,'東京・埼玉',NULL,NULL),(5,'西東京',NULL,NULL),(6,'横浜',NULL,NULL),(7,'神奈川・山梨',NULL,NULL),(8,'沖縄',NULL,NULL),(9,'静岡',NULL,NULL),(10,'東海',NULL,NULL),(11,'中部・北陸',NULL,NULL),(12,'滋賀・京都',NULL,NULL),(13,'大阪北',NULL,NULL),(14,'大阪・奈良',NULL,NULL),(15,'大阪南',NULL,NULL),(16,'兵庫',NULL,NULL),(17,'中国東',NULL,NULL),(18,'中国中',NULL,NULL),(19,'中国西',NULL,NULL),(20,'九州北',NULL,NULL),(21,'九州中',NULL,NULL),(22,'九州南',NULL,NULL);
/*!40000 ALTER TABLE `organization2` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2023-07-08 16:49:44
