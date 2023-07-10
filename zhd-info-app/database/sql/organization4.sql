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
-- Table structure for table `organization4`
--

DROP TABLE IF EXISTS `organization4`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organization4` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `organization4_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `organization4`
--

LOCK TABLES `organization4` WRITE;
/*!40000 ALTER TABLE `organization4` DISABLE KEYS */;
INSERT INTO `organization4` VALUES (1,'札幌北AR',NULL,NULL),(2,'道央AR',NULL,NULL),(3,'道東AR',NULL,NULL),(4,'道南AR',NULL,NULL),(5,'愛知AR',NULL,NULL),(6,'茨城・栃木AR',NULL,NULL),(7,'横浜AR',NULL,NULL),(8,'宮城AR',NULL,NULL),(9,'京都AR',NULL,NULL),(10,'阪神AR',NULL,NULL),(11,'埼京AR',NULL,NULL),(12,'山陰AR',NULL,NULL),(13,'山梨AR',NULL,NULL),(14,'滋賀三重AR',NULL,NULL),(15,'静岡AR',NULL,NULL),(16,'千葉中央AR',NULL,NULL),(17,'千葉南AR',NULL,NULL),(18,'千葉北AR',NULL,NULL),(19,'相模AR',NULL,NULL),(20,'大阪南AR',NULL,NULL),(21,'大阪北AR',NULL,NULL),(22,'長野AR',NULL,NULL),(23,'東京AR',NULL,NULL),(24,'奈良AR',NULL,NULL),(25,'南多摩AR',NULL,NULL),(26,'播磨AR',NULL,NULL),(27,'磐越AR',NULL,NULL),(28,'福岡AR',NULL,NULL),(29,'兵庫AR',NULL,NULL),(30,'北関東AR',NULL,NULL),(31,'北多摩AR',NULL,NULL),(32,'北東北AR',NULL,NULL),(33,'北陸AR',NULL,NULL),(34,'練馬AR',NULL,NULL);
/*!40000 ALTER TABLE `organization4` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2023-07-08 16:50:19
