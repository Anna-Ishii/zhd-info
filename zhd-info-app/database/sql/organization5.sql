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
-- Table structure for table `organization5`
--

DROP TABLE IF EXISTS `organization5`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organization5` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `organization5_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `organization5`
--

LOCK TABLES `organization5` WRITE;
/*!40000 ALTER TABLE `organization5` DISABLE KEYS */;
INSERT INTO `organization5` VALUES (1,'北海道',NULL,NULL),(2,'東北・北関東',NULL,NULL),(3,'千葉',NULL,NULL),(4,'東京・埼玉',NULL,NULL),(5,'西東京',NULL,NULL),(6,'横浜',NULL,NULL),(7,'神奈川・山梨',NULL,NULL),(8,'沖縄',NULL,NULL),(9,'静岡',NULL,NULL),(10,'東海',NULL,NULL),(11,'中部・北陸',NULL,NULL),(12,'滋賀・京都',NULL,NULL),(13,'大阪北',NULL,NULL),(14,'大阪・奈良',NULL,NULL),(15,'大阪南',NULL,NULL),(16,'兵庫',NULL,NULL),(17,'中国東',NULL,NULL),(18,'中国中',NULL,NULL),(19,'中国西',NULL,NULL),(20,'九州北',NULL,NULL),(21,'九州中',NULL,NULL),(22,'九州南',NULL,NULL),(23,'常盤',NULL,NULL),(24,'栃木',NULL,NULL),(25,'茨城',NULL,NULL),(26,'千葉北',NULL,NULL),(27,'千葉南',NULL,NULL),(28,'関東',NULL,NULL),(29,'神奈川東',NULL,NULL),(30,'神奈川西',NULL,NULL),(31,'信越',NULL,NULL),(32,'京阪',NULL,NULL),(33,'阪神',NULL,NULL),(34,'阪奈',NULL,NULL),(35,'中国',NULL,NULL),(36,'東京南・神奈川',NULL,NULL),(37,'東京西',NULL,NULL),(38,'埼京',NULL,NULL),(39,'千葉・茨城',NULL,NULL),(40,'東京・川崎',NULL,NULL),(41,'多摩・湘南',NULL,NULL),(42,'茨城・千葉東',NULL,NULL),(43,'北関東',NULL,NULL),(44,'埼玉',NULL,NULL),(45,'千葉西',NULL,NULL),(46,'山梨・静岡',NULL,NULL);
/*!40000 ALTER TABLE `organization5` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2023-07-08 16:50:32
