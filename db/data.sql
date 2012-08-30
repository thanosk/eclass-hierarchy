-- MySQL dump 10.13  Distrib 5.1.54, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: hierarchy
-- ------------------------------------------------------
-- Server version	5.1.54-1ubuntu4

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
-- Dumping data for table `hierarchy`
--

LOCK TABLES `hierarchy` WRITE;
/*!40000 ALTER TABLE `hierarchy` DISABLE KEYS */;
INSERT INTO `hierarchy` VALUES (1,'Πανεπιστήμιο Πατρών',1,28,'',0,0),(2,'Τμήμα Ηλεκτρολόγων Μηχανικών',17,26,'',1,0),(3,'Τμήμα Μηχανικών Υπολογιστών',15,16,'',1,0),(7,'Τομέας Η/Υ',24,25,'',0,0),(12,'Πολυτεχνική Σχολή',14,27,NULL,0,0),(9,'Τομέας Τηλεπικοινωνιών',22,23,'',0,0),(10,'Τομέας ΣΑΕ',20,21,'',0,0),(11,'Τομέας ΣΗΕ',18,19,'',0,0),(13,'Σχολή Επιστημών Υγείας',8,13,NULL,0,0),(14,'Τμήμα Ιατρικής',11,12,NULL,1,0),(15,'Τμήμα Φαρμακευτικής',9,10,NULL,1,0),(16,'Σχολή Θετικών Επιστημών',2,7,NULL,0,0),(17,'Τμήμα Μαθηματικών',5,6,NULL,1,0),(18,'Τμήμα Φυσικής',3,4,NULL,1,0);
/*!40000 ALTER TABLE `hierarchy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `course_type`
--

LOCK TABLES `course_type` WRITE;
/*!40000 ALTER TABLE `course_type` DISABLE KEYS */;
INSERT INTO `course_type` VALUES (1,'Προπτυχιακά'),(2,'Μεταπτυχιακά'),(3,'Άλλα');
/*!40000 ALTER TABLE `course_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `course`
--

LOCK TABLES `course` WRITE;
/*!40000 ALTER TABLE `course` DISABLE KEYS */;
INSERT INTO `course` VALUES (1,'Ανατομία');
/*!40000 ALTER TABLE `course` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `course_department`
--

LOCK TABLES `course_department` WRITE;
/*!40000 ALTER TABLE `course_department` DISABLE KEYS */;
INSERT INTO `course_department` VALUES (1,1,14);
/*!40000 ALTER TABLE `course_department` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `course_is_type`
--

LOCK TABLES `course_is_type` WRITE;
/*!40000 ALTER TABLE `course_is_type` DISABLE KEYS */;
INSERT INTO `course_is_type` VALUES (1,1,1);
/*!40000 ALTER TABLE `course_is_type` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2011-10-05 18:13:20
