-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: sigainv
-- ------------------------------------------------------
-- Server version	8.4.3

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
-- Table structure for table `ajustes_inventario`
--

DROP TABLE IF EXISTS `ajustes_inventario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ajustes_inventario` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bodega_id` bigint unsigned NOT NULL,
  `usuario_id` bigint unsigned DEFAULT NULL,
  `fecha` date NOT NULL,
  `motivo` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('borrador','confirmado','anulado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  `es_saldo_inicial` tinyint(1) NOT NULL DEFAULT '0',
  `observacion` text COLLATE utf8mb4_unicode_ci,
  `confirmado_en` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ajustes_inventario_numero_unique` (`numero`),
  KEY `ajustes_inventario_usuario_id_foreign` (`usuario_id`),
  KEY `ajustes_inventario_bodega_id_index` (`bodega_id`),
  KEY `ajustes_inventario_fecha_index` (`fecha`),
  KEY `ajustes_inventario_estado_index` (`estado`),
  CONSTRAINT `ajustes_inventario_bodega_id_foreign` FOREIGN KEY (`bodega_id`) REFERENCES `bodegas` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `ajustes_inventario_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ajustes_inventario`
--

LOCK TABLES `ajustes_inventario` WRITE;
/*!40000 ALTER TABLE `ajustes_inventario` DISABLE KEYS */;
/*!40000 ALTER TABLE `ajustes_inventario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auditoria_documentos`
--

DROP TABLE IF EXISTS `auditoria_documentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auditoria_documentos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `documento_tipo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tipo: compra, venta, remision',
  `documento_id` bigint unsigned NOT NULL COMMENT 'ID del documento',
  `usuario_id` bigint unsigned DEFAULT NULL,
  `campo_modificado` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nombre del campo que cambió (null para operaciones de create/delete)',
  `valor_anterior` text COLLATE utf8mb4_unicode_ci COMMENT 'Valor antes del cambio',
  `valor_nuevo` text COLLATE utf8mb4_unicode_ci COMMENT 'Valor después del cambio',
  `estado_documento` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Estado en que estaba el documento',
  `accion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tipo de acción: create, update, delete, confirm, mark_paid, cancel',
  `observacion` text COLLATE utf8mb4_unicode_ci COMMENT 'Razón o nota del cambio',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'IP del usuario que realizó la acción',
  `user_agent` text COLLATE utf8mb4_unicode_ci COMMENT 'User agent del navegador',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `auditoria_documentos_documento_tipo_documento_id_index` (`documento_tipo`,`documento_id`),
  KEY `auditoria_documentos_usuario_id_foreign` (`usuario_id`),
  CONSTRAINT `auditoria_documentos_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auditoria_documentos`
--

LOCK TABLES `auditoria_documentos` WRITE;
/*!40000 ALTER TABLE `auditoria_documentos` DISABLE KEYS */;
INSERT INTO `auditoria_documentos` VALUES (1,'producto',1,1,NULL,'null','{\"codigo\":\"PROD-0000001\",\"nombre\":\"DDR3 4G (1600L) OEM (USADA)\",\"precio_compra\":\"49000.00\",\"precio_venta\":\"70000.00\"}','activo','create','Producto creado','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-17 07:58:49','2026-06-17 07:58:49'),(2,'producto',2,1,NULL,'null','{\"codigo\":\"PROD-0000002\",\"nombre\":\"DDR4 8G (3200) ADATA XPG SPECTRIX D35G RGB\",\"precio_compra\":\"309000.00\",\"precio_venta\":\"441428.57\"}','activo','create','Producto creado','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-19 08:14:53','2026-06-19 08:14:53'),(3,'producto',3,1,NULL,'null','{\"codigo\":\"PROD-0000003\",\"nombre\":\"DDR4 8G (3200) VIPER PATRIOT STEEL RGB\",\"precio_compra\":\"349000.00\",\"precio_venta\":\"498571.43\"}','activo','create','Producto creado','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-19 08:15:28','2026-06-19 08:15:28'),(4,'producto',4,1,NULL,'null','{\"codigo\":\"PROD-0000004\",\"nombre\":\"DDR4 8G (3200) KINGSTON FURY BEAST RGB\",\"precio_compra\":\"0.00\",\"precio_venta\":\"0.00\"}','activo','create','Producto creado','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-19 08:16:03','2026-06-19 08:16:03'),(5,'producto',5,1,NULL,'null','{\"codigo\":\"PROD-0000005\",\"nombre\":\"DDR4 8G (3200) CORSAIR VENGEANCE RGB RS\",\"precio_compra\":\"349000.00\",\"precio_venta\":\"498571.43\"}','activo','create','Producto creado','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-19 08:18:10','2026-06-19 08:18:10'),(6,'producto',6,1,NULL,'null','{\"codigo\":\"PROD-0000006\",\"nombre\":\"DDR4 8G (3200) CORSAIR VENGEANCE RGB PRO\",\"precio_compra\":\"349000.00\",\"precio_venta\":\"498571.43\"}','activo','create','Producto creado','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-19 08:18:51','2026-06-19 08:18:51'),(7,'producto',7,1,NULL,'null','{\"codigo\":\"PROD-0000007\",\"nombre\":\"DDR4 16G (3200) ADATA XPG SPECTRIX D35G RGB\",\"precio_compra\":\"545000.00\",\"precio_venta\":\"778571.43\"}','activo','create','Producto creado','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-19 08:19:25','2026-06-19 08:19:25'),(8,'producto',8,1,NULL,'null','{\"codigo\":\"PROD-0000008\",\"nombre\":\"DDR4 16G (3200) CORSAIR VENGEANCE RGB RS\",\"precio_compra\":\"555.00\",\"precio_venta\":\"792.86\"}','activo','create','Producto creado','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-19 08:19:52','2026-06-19 08:19:52'),(9,'producto',9,1,NULL,'null','{\"codigo\":\"PROD-0000009\",\"nombre\":\"DDR4 32G (3200) ADATA XPG SPECTRIX D50 GRIS RGB\",\"precio_compra\":\"929000.00\",\"precio_venta\":\"1327142.86\"}','activo','create','Producto creado','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-19 08:20:26','2026-06-19 08:20:26'),(10,'producto',10,1,NULL,'null','{\"codigo\":\"PROD-0000010\",\"nombre\":\"DDR5 16G (5600) KINGSTON NO DISIPADA\",\"precio_compra\":\"885000.00\",\"precio_venta\":\"1264285.71\"}','activo','create','Producto creado','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-19 08:57:47','2026-06-19 08:57:47'),(11,'producto',11,1,NULL,'null','{\"codigo\":\"PROD-0000011\",\"nombre\":\"DDR5 16G (5600) KINGSTON FURY BEAST\",\"precio_compra\":\"979000.00\",\"precio_venta\":\"1398571.43\"}','activo','create','Producto creado','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-19 08:58:21','2026-06-19 08:58:21'),(12,'producto',12,1,NULL,'null','{\"codigo\":\"PROD-0000012\",\"nombre\":\"DDR5 16G (5600) PATRIOT VIPER BLANCA ELITE 5 RGB\",\"precio_compra\":\"979000.00\",\"precio_venta\":\"1398571.43\"}','activo','create','Producto creado','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-19 08:58:48','2026-06-19 08:58:48'),(13,'producto',13,1,NULL,'null','{\"codigo\":\"PROD-0000013\",\"nombre\":\"PORTATIL DDR4 8G (3200) SAMSUNG  OEM USADA\",\"precio_compra\":\"199000.00\",\"precio_venta\":\"284285.71\"}','activo','create','Producto creado','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-19 08:59:34','2026-06-19 08:59:34'),(14,'producto',14,1,NULL,'null','{\"codigo\":\"PROD-0000014\",\"nombre\":\"PORTATIL DDR4 16G (3200) ADATA\",\"precio_compra\":\"499000.00\",\"precio_venta\":\"712857.14\"}','activo','create','Producto creado','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-19 09:00:10','2026-06-19 09:00:10'),(15,'producto',15,1,NULL,'null','{\"codigo\":\"PROD-0000015\",\"nombre\":\"PORTATIL DDR4 16G (3200) CORSAIR VENGEANCE\",\"precio_compra\":\"509000.00\",\"precio_venta\":\"727142.86\"}','activo','create','Producto creado','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-19 09:00:41','2026-06-19 09:00:41'),(16,'producto',16,1,NULL,'null','{\"codigo\":\"PROD-0000016\",\"nombre\":\"PORTATIL DDR4 16G (3200) KINGSTON\",\"precio_compra\":\"509000.00\",\"precio_venta\":\"727142.86\"}','activo','create','Producto creado','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-19 09:01:15','2026-06-19 09:01:15'),(17,'producto',17,1,NULL,'null','{\"codigo\":\"PROD-0000017\",\"nombre\":\"PORTATIL DDR4 16G (3200) CRUCIAL\",\"precio_compra\":\"509000.00\",\"precio_venta\":\"727142.86\"}','activo','create','Producto creado','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-19 09:02:04','2026-06-19 09:02:04'),(18,'producto',18,1,NULL,'null','{\"codigo\":\"PROD-0000018\",\"nombre\":\"PORTATIL DDR4 32G (3200) ADATA\",\"precio_compra\":\"1055000.00\",\"precio_venta\":\"1507142.86\"}','activo','create','Producto creado','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-19 09:02:38','2026-06-19 09:02:38'),(19,'producto',19,1,NULL,'null','{\"codigo\":\"PROD-0000019\",\"nombre\":\"SOLIDO SATA (SSD) 480GB KINGSTON A400\",\"precio_compra\":\"405000.00\",\"precio_venta\":\"578571.43\"}','activo','create','Producto creado','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-19 09:03:50','2026-06-19 09:03:50'),(20,'producto',20,1,NULL,'null','{\"codigo\":\"PROD-0000020\",\"nombre\":\"SOLIDO SATA (SSD) 500GB CRUCIAL BX500\",\"precio_compra\":\"415000.00\",\"precio_venta\":\"592857.14\"}','activo','create','Producto creado','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-19 09:04:24','2026-06-19 09:04:24'),(21,'producto',21,1,NULL,'null','{\"codigo\":\"PROD-0000021\",\"nombre\":\"SOLIDO SATA (SSD) 960GB KINGSTON A400\",\"precio_compra\":\"545000.00\",\"precio_venta\":\"778571.43\"}','activo','create','Producto creado','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-19 09:05:02','2026-06-19 09:05:02'),(22,'producto',22,1,NULL,'null','{\"codigo\":\"PROD-0000022\",\"nombre\":\"SOLIDO SATA (SSD) 1TB P220 PATRIOT\",\"precio_compra\":\"539000.00\",\"precio_venta\":\"770000.00\"}','activo','create','Producto creado','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-19 09:05:32','2026-06-19 09:05:32'),(23,'producto',23,1,NULL,'null','{\"codigo\":\"PROD-0000023\",\"nombre\":\"SSD (M2) NVME 500GB KINGSTON NV3 (5000X3000)\",\"precio_compra\":\"459000.00\",\"precio_venta\":\"655714.29\"}','activo','create','Producto creado','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-19 09:06:42','2026-06-19 09:06:42'),(24,'producto',24,1,NULL,'null','{\"codigo\":\"PROD-0000024\",\"nombre\":\"SSD (M2) NVME 1TB CRUCIAL E100 GEN4 (5000X3000)\",\"precio_compra\":\"659000.00\",\"precio_venta\":\"941428.57\"}','activo','create','Producto creado','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-19 09:07:15','2026-06-19 09:07:15');
/*!40000 ALTER TABLE `auditoria_documentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auditorias`
--

DROP TABLE IF EXISTS `auditorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auditorias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` bigint unsigned DEFAULT NULL,
  `tabla` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `operacion` enum('INSERT','UPDATE','DELETE') COLLATE utf8mb4_unicode_ci NOT NULL,
  `registro_id` bigint unsigned NOT NULL,
  `datos_anteriores` json DEFAULT NULL,
  `datos_nuevos` json DEFAULT NULL,
  `fecha_operacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `auditorias_usuario_id_index` (`usuario_id`),
  KEY `auditorias_tabla_index` (`tabla`),
  KEY `auditorias_operacion_index` (`operacion`),
  KEY `auditorias_fecha_operacion_index` (`fecha_operacion`),
  KEY `auditorias_tabla_registro_idx` (`tabla`,`registro_id`),
  CONSTRAINT `auditorias_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auditorias`
--

LOCK TABLES `auditorias` WRITE;
/*!40000 ALTER TABLE `auditorias` DISABLE KEYS */;
/*!40000 ALTER TABLE `auditorias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bancos`
--

DROP TABLE IF EXISTS `bancos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bancos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre_banco` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero_cuenta` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_cuenta` enum('ahorros','corriente') COLLATE utf8mb4_unicode_ci NOT NULL,
  `saldo_inicial` decimal(15,2) NOT NULL DEFAULT '0.00',
  `moneda` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'COP',
  `estado` enum('activo','inactivo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `codigo_swift` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Para transacciones internacionales',
  `usuario_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bancos_numero_cuenta_unique` (`numero_cuenta`),
  KEY `bancos_usuario_id_foreign` (`usuario_id`),
  KEY `bancos_nombre_tipo_idx` (`nombre_banco`,`tipo_cuenta`),
  KEY `bancos_nombre_estado_idx` (`nombre_banco`,`estado`),
  CONSTRAINT `bancos_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bancos`
--

LOCK TABLES `bancos` WRITE;
/*!40000 ALTER TABLE `bancos` DISABLE KEYS */;
/*!40000 ALTER TABLE `bancos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bodegas`
--

DROP TABLE IF EXISTS `bodegas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bodegas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `direccion1` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccion2` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `departamento_id` bigint unsigned NOT NULL,
  `ciudad_id` bigint unsigned NOT NULL,
  `es_principal` tinyint(1) NOT NULL DEFAULT '0',
  `numero_cajas_pos` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'Cantidad de cajas POS asociadas a esta bodega/sucursal',
  `estado` enum('activo','inactivo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `usuario_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bodegas_nombre_unique` (`nombre`),
  KEY `bodegas_departamento_id_foreign` (`departamento_id`),
  KEY `bodegas_ciudad_id_foreign` (`ciudad_id`),
  KEY `bodegas_usuario_id_foreign` (`usuario_id`),
  CONSTRAINT `bodegas_ciudad_id_foreign` FOREIGN KEY (`ciudad_id`) REFERENCES `ciudades` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `bodegas_departamento_id_foreign` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `bodegas_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bodegas`
--

LOCK TABLES `bodegas` WRITE;
/*!40000 ALTER TABLE `bodegas` DISABLE KEYS */;
INSERT INTO `bodegas` VALUES (1,'BODEGA PRINCIPAL','Bodega principal de la empresa','Urb Hibiscos',NULL,54,885,1,0,'activo',2,'2026-06-17 06:01:48','2026-06-17 07:57:23');
/*!40000 ALTER TABLE `bodegas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('sigainv-cache-356a192b7913b04c54574d18c28d46e6395428ab','i:3;',1781665092),('sigainv-cache-356a192b7913b04c54574d18c28d46e6395428ab:timer','i:1781665092;',1781665092),('sigainv-cache-livewire-rate-limiter:056fc329aaaa757d31db450f525da23fde4d1b36','i:2;',1781841423),('sigainv-cache-livewire-rate-limiter:056fc329aaaa757d31db450f525da23fde4d1b36:timer','i:1781841423;',1781841423),('sigainv-cache-spatie.permission.cache','a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:132:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:10:\"config.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:13:\"config.editar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:11:\"empresa.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:14:\"empresa.editar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:10:\"bodega.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:12:\"bodega.crear\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:13:\"bodega.editar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:15:\"bodega.eliminar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:13:\"categoria.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:15:\"categoria.crear\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:16:\"categoria.editar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:18:\"categoria.eliminar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:9:\"marca.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:11:\"marca.crear\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:12:\"marca.editar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:15;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:14:\"marca.eliminar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:16;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:12:\"impuesto.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:17;a:4:{s:1:\"a\";i:18;s:1:\"b\";s:14:\"impuesto.crear\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:18;a:4:{s:1:\"a\";i:19;s:1:\"b\";s:15:\"impuesto.editar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:19;a:4:{s:1:\"a\";i:20;s:1:\"b\";s:17:\"impuesto.eliminar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:20;a:4:{s:1:\"a\";i:21;s:1:\"b\";s:14:\"forma_pago.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:21;a:4:{s:1:\"a\";i:22;s:1:\"b\";s:16:\"forma_pago.crear\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:22;a:4:{s:1:\"a\";i:23;s:1:\"b\";s:17:\"forma_pago.editar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:23;a:4:{s:1:\"a\";i:24;s:1:\"b\";s:19:\"forma_pago.eliminar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:24;a:4:{s:1:\"a\";i:25;s:1:\"b\";s:14:\"numeracion.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:25;a:4:{s:1:\"a\";i:26;s:1:\"b\";s:16:\"numeracion.crear\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:26;a:4:{s:1:\"a\";i:27;s:1:\"b\";s:17:\"numeracion.editar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:27;a:4:{s:1:\"a\";i:28;s:1:\"b\";s:19:\"numeracion.eliminar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:28;a:4:{s:1:\"a\";i:29;s:1:\"b\";s:17:\"unidad_medida.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:29;a:4:{s:1:\"a\";i:30;s:1:\"b\";s:19:\"unidad_medida.crear\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:30;a:4:{s:1:\"a\";i:31;s:1:\"b\";s:20:\"unidad_medida.editar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:31;a:4:{s:1:\"a\";i:32;s:1:\"b\";s:22:\"unidad_medida.eliminar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:32;a:4:{s:1:\"a\";i:33;s:1:\"b\";s:9:\"banco.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:33;a:4:{s:1:\"a\";i:34;s:1:\"b\";s:11:\"banco.crear\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:34;a:4:{s:1:\"a\";i:35;s:1:\"b\";s:12:\"banco.editar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:35;a:4:{s:1:\"a\";i:36;s:1:\"b\";s:14:\"banco.eliminar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:36;a:4:{s:1:\"a\";i:37;s:1:\"b\";s:9:\"admin.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:37;a:4:{s:1:\"a\";i:38;s:1:\"b\";s:12:\"usuarios.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:38;a:4:{s:1:\"a\";i:39;s:1:\"b\";s:14:\"usuarios.crear\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:39;a:4:{s:1:\"a\";i:40;s:1:\"b\";s:15:\"usuarios.editar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:40;a:4:{s:1:\"a\";i:41;s:1:\"b\";s:17:\"usuarios.eliminar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:41;a:4:{s:1:\"a\";i:42;s:1:\"b\";s:9:\"roles.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:42;a:4:{s:1:\"a\";i:43;s:1:\"b\";s:11:\"roles.crear\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:43;a:4:{s:1:\"a\";i:44;s:1:\"b\";s:12:\"roles.editar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:44;a:4:{s:1:\"a\";i:45;s:1:\"b\";s:14:\"roles.eliminar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:45;a:4:{s:1:\"a\";i:46;s:1:\"b\";s:13:\"auditoria.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:46;a:4:{s:1:\"a\";i:47;s:1:\"b\";s:12:\"producto.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:47;a:4:{s:1:\"a\";i:48;s:1:\"b\";s:14:\"producto.crear\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:48;a:4:{s:1:\"a\";i:49;s:1:\"b\";s:15:\"producto.editar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:49;a:4:{s:1:\"a\";i:50;s:1:\"b\";s:17:\"producto.eliminar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:50;a:4:{s:1:\"a\";i:51;s:1:\"b\";s:9:\"stock.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:51;a:4:{s:1:\"a\";i:52;s:1:\"b\";s:25:\"movimiento_inventario.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:52;a:4:{s:1:\"a\";i:53;s:1:\"b\";s:21:\"historico_precios.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:53;a:4:{s:1:\"a\";i:54;s:1:\"b\";s:12:\"traslado.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:54;a:4:{s:1:\"a\";i:55;s:1:\"b\";s:14:\"traslado.crear\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:55;a:4:{s:1:\"a\";i:56;s:1:\"b\";s:15:\"traslado.editar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:56;a:4:{s:1:\"a\";i:57;s:1:\"b\";s:17:\"traslado.eliminar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:57;a:4:{s:1:\"a\";i:58;s:1:\"b\";s:18:\"traslado.confirmar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:58;a:4:{s:1:\"a\";i:59;s:1:\"b\";s:15:\"traslado.anular\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:59;a:4:{s:1:\"a\";i:60;s:1:\"b\";s:13:\"proveedor.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:60;a:4:{s:1:\"a\";i:61;s:1:\"b\";s:15:\"proveedor.crear\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:61;a:4:{s:1:\"a\";i:62;s:1:\"b\";s:16:\"proveedor.editar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:62;a:4:{s:1:\"a\";i:63;s:1:\"b\";s:18:\"proveedor.eliminar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:63;a:4:{s:1:\"a\";i:64;s:1:\"b\";s:20:\"cliente_catalogo.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:64;a:4:{s:1:\"a\";i:65;s:1:\"b\";s:22:\"cliente_catalogo.crear\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:65;a:4:{s:1:\"a\";i:66;s:1:\"b\";s:23:\"cliente_catalogo.editar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:66;a:4:{s:1:\"a\";i:67;s:1:\"b\";s:25:\"cliente_catalogo.eliminar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:67;a:4:{s:1:\"a\";i:68;s:1:\"b\";s:10:\"compra.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:68;a:4:{s:1:\"a\";i:69;s:1:\"b\";s:12:\"compra.crear\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:69;a:4:{s:1:\"a\";i:70;s:1:\"b\";s:13:\"compra.editar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:70;a:4:{s:1:\"a\";i:71;s:1:\"b\";s:15:\"compra.eliminar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:71;a:4:{s:1:\"a\";i:72;s:1:\"b\";s:16:\"compra.confirmar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:72;a:4:{s:1:\"a\";i:73;s:1:\"b\";s:18:\"pago_proveedor.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:73;a:4:{s:1:\"a\";i:74;s:1:\"b\";s:20:\"pago_proveedor.crear\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:74;a:4:{s:1:\"a\";i:75;s:1:\"b\";s:21:\"pago_proveedor.editar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:75;a:4:{s:1:\"a\";i:76;s:1:\"b\";s:23:\"pago_proveedor.eliminar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:76;a:4:{s:1:\"a\";i:77;s:1:\"b\";s:14:\"cotizacion.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:77;a:4:{s:1:\"a\";i:78;s:1:\"b\";s:16:\"cotizacion.crear\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:78;a:4:{s:1:\"a\";i:79;s:1:\"b\";s:17:\"cotizacion.editar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:79;a:4:{s:1:\"a\";i:80;s:1:\"b\";s:19:\"cotizacion.eliminar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:80;a:4:{s:1:\"a\";i:81;s:1:\"b\";s:12:\"remision.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:81;a:4:{s:1:\"a\";i:82;s:1:\"b\";s:14:\"remision.crear\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:82;a:4:{s:1:\"a\";i:83;s:1:\"b\";s:15:\"remision.editar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:83;a:4:{s:1:\"a\";i:84;s:1:\"b\";s:17:\"remision.eliminar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:84;a:4:{s:1:\"a\";i:85;s:1:\"b\";s:18:\"remision.confirmar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:85;a:4:{s:1:\"a\";i:86;s:1:\"b\";s:9:\"venta.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:86;a:4:{s:1:\"a\";i:87;s:1:\"b\";s:11:\"venta.crear\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:87;a:4:{s:1:\"a\";i:88;s:1:\"b\";s:12:\"venta.editar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:88;a:4:{s:1:\"a\";i:89;s:1:\"b\";s:14:\"venta.eliminar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:89;a:4:{s:1:\"a\";i:90;s:1:\"b\";s:15:\"venta.confirmar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:90;a:4:{s:1:\"a\";i:91;s:1:\"b\";s:16:\"pago_cliente.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:91;a:4:{s:1:\"a\";i:92;s:1:\"b\";s:18:\"pago_cliente.crear\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:92;a:4:{s:1:\"a\";i:93;s:1:\"b\";s:19:\"pago_cliente.editar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:93;a:4:{s:1:\"a\";i:94;s:1:\"b\";s:21:\"pago_cliente.eliminar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:94;a:4:{s:1:\"a\";i:95;s:1:\"b\";s:18:\"transformacion.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:95;a:4:{s:1:\"a\";i:96;s:1:\"b\";s:20:\"transformacion.crear\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:96;a:4:{s:1:\"a\";i:97;s:1:\"b\";s:21:\"transformacion.editar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:97;a:4:{s:1:\"a\";i:98;s:1:\"b\";s:23:\"transformacion.eliminar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:98;a:4:{s:1:\"a\";i:99;s:1:\"b\";s:24:\"transformacion.confirmar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:99;a:4:{s:1:\"a\";i:100;s:1:\"b\";s:26:\"formula_transformacion.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:100;a:4:{s:1:\"a\";i:101;s:1:\"b\";s:28:\"formula_transformacion.crear\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:101;a:4:{s:1:\"a\";i:102;s:1:\"b\";s:29:\"formula_transformacion.editar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:102;a:4:{s:1:\"a\";i:103;s:1:\"b\";s:31:\"formula_transformacion.eliminar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:103;a:4:{s:1:\"a\";i:104;s:1:\"b\";s:21:\"ajuste_inventario.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:104;a:4:{s:1:\"a\";i:105;s:1:\"b\";s:23:\"ajuste_inventario.crear\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:105;a:4:{s:1:\"a\";i:106;s:1:\"b\";s:24:\"ajuste_inventario.editar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:106;a:4:{s:1:\"a\";i:107;s:1:\"b\";s:26:\"ajuste_inventario.eliminar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:107;a:4:{s:1:\"a\";i:108;s:1:\"b\";s:27:\"ajuste_inventario.confirmar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:108;a:4:{s:1:\"a\";i:109;s:1:\"b\";s:17:\"conteo_fisico.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:109;a:4:{s:1:\"a\";i:110;s:1:\"b\";s:19:\"conteo_fisico.crear\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:110;a:4:{s:1:\"a\";i:111;s:1:\"b\";s:20:\"conteo_fisico.editar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:111;a:4:{s:1:\"a\";i:112;s:1:\"b\";s:22:\"conteo_fisico.eliminar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:112;a:4:{s:1:\"a\";i:113;s:1:\"b\";s:20:\"conteo_fisico.cerrar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:113;a:4:{s:1:\"a\";i:114;s:1:\"b\";s:28:\"conteo_fisico.generar_ajuste\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:114;a:4:{s:1:\"a\";i:115;s:1:\"b\";s:11:\"reporte.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:115;a:4:{s:1:\"a\";i:116;s:1:\"b\";s:16:\"reporte.exportar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:116;a:4:{s:1:\"a\";i:117;s:1:\"b\";s:16:\"reporte.imprimir\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:117;a:4:{s:1:\"a\";i:118;s:1:\"b\";s:13:\"dashboard.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:118;a:4:{s:1:\"a\";i:119;s:1:\"b\";s:10:\"portal.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:119;a:4:{s:1:\"a\";i:120;s:1:\"b\";s:23:\"portal.mis_cotizaciones\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:120;a:4:{s:1:\"a\";i:121;s:1:\"b\";s:21:\"portal.mis_remisiones\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:121;a:4:{s:1:\"a\";i:122;s:1:\"b\";s:17:\"portal.mis_ventas\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:122;a:4:{s:1:\"a\";i:123;s:1:\"b\";s:23:\"portal.mi_estado_cuenta\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:123;a:4:{s:1:\"a\";i:124;s:1:\"b\";s:8:\"caja.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:124;a:4:{s:1:\"a\";i:125;s:1:\"b\";s:10:\"caja.crear\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:125;a:4:{s:1:\"a\";i:126;s:1:\"b\";s:11:\"caja.editar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:126;a:4:{s:1:\"a\";i:127;s:1:\"b\";s:13:\"caja.eliminar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:127;a:4:{s:1:\"a\";i:128;s:1:\"b\";s:19:\"movimiento_caja.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:128;a:4:{s:1:\"a\";i:129;s:1:\"b\";s:21:\"movimiento_caja.crear\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:129;a:4:{s:1:\"a\";i:130;s:1:\"b\";s:22:\"movimiento_caja.editar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:130;a:4:{s:1:\"a\";i:131;s:1:\"b\";s:24:\"movimiento_caja.eliminar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:131;a:4:{s:1:\"a\";i:132;s:1:\"b\";s:9:\"turno.ver\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}}s:5:\"roles\";a:4:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:13:\"administrador\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:8:\"auxiliar\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:8:\"contador\";s:1:\"c\";s:3:\"web\";}i:3;a:3:{s:1:\"a\";i:4;s:1:\"b\";s:8:\"vendedor\";s:1:\"c\";s:3:\"web\";}}}',1781925150);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cajas`
--

DROP TABLE IF EXISTS `cajas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cajas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('caja_general','caja_menor','caja_sucursal','caja_pos') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'caja_general',
  `estado` enum('activa','inactiva') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activa',
  `saldo_inicial` decimal(15,2) NOT NULL DEFAULT '0.00',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `usuario_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cajas_usuario_id_foreign` (`usuario_id`),
  KEY `cajas_tipo_activo_idx` (`tipo`,`activo`),
  CONSTRAINT `cajas_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cajas`
--

LOCK TABLES `cajas` WRITE;
/*!40000 ALTER TABLE `cajas` DISABLE KEYS */;
INSERT INTO `cajas` VALUES (1,'Caja Principal','caja_general','activa',0.00,1,NULL,'2026-06-17 06:01:48','2026-06-17 06:01:48');
/*!40000 ALTER TABLE `cajas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categorias`
--

DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `categoria_id` bigint unsigned DEFAULT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categorias_categoria_id_foreign` (`categoria_id`),
  CONSTRAINT `categorias_categoria_id_foreign` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias`
--

LOCK TABLES `categorias` WRITE;
/*!40000 ALTER TABLE `categorias` DISABLE KEYS */;
INSERT INTO `categorias` VALUES (1,NULL,'MEMORIAS RAM',NULL,1,'2026-06-17 07:58:29','2026-06-17 07:58:29'),(2,NULL,'UNIDADES SSD / DISCOS DUROS',NULL,1,'2026-06-19 09:03:30','2026-06-19 09:03:30');
/*!40000 ALTER TABLE `categorias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ciudades`
--

DROP TABLE IF EXISTS `ciudades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ciudades` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `departamento_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ciudades_departamento_id_foreign` (`departamento_id`),
  CONSTRAINT `ciudades_departamento_id_foreign` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1202 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ciudades`
--

LOCK TABLES `ciudades` WRITE;
/*!40000 ALTER TABLE `ciudades` DISABLE KEYS */;
INSERT INTO `ciudades` VALUES (1,'LETICIA',91,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(2,'EL ENCANTO',91,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(3,'LA CHORRERA',91,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(4,'PUERTO ALEGRE',91,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(5,'PUERTO NARIÑO',91,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(6,'PUERTO SANTANDER',91,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(7,'TURBACO',91,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(8,'CARURÚ',91,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(9,'MIRITÍ-PARANÁ',91,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(10,'SAN JOSÉ',91,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(11,'ABEJORRAL',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(12,'AMAGÁ',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(13,'AMALFI',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(14,'ANDÉS',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(15,'ANGELOPOLIS',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(16,'ANGOSTURA',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(17,'ANORÍ',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(18,'APARTADÓ',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(19,'ARBOLEDA',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(20,'ARGELIA',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(21,'ARMENIA',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(22,'BARCELONA',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(23,'BELMIRA',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(24,'BETANIA',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(25,'BETULIA',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(26,'BRICEÑO',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(27,'BURITICÁ',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(28,'CABEZAS',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(29,'CAICEDO',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(30,'CARAMANTA',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(31,'CAREPA',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(32,'CAROLINA DEL PRÍNCIPE',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(33,'CARMEN DE VIBORAL',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(34,'CAROLINA',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(35,'CATAS DE BUSTOS',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(36,'CAUJERÍA',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(37,'CERRITO',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(38,'CERRO AZUL',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(39,'CHIGORODÓ',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(40,'CISNEROS',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(41,'CIUDAD BOLÍVAR',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(42,'COCORNÁ',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(43,'CONCEPCIÓN',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(44,'CONCORDIA',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(45,'COPACABANA',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(46,'DAVEIBA',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(47,'DON MATÍAS',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(48,'EBÉJICO',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(49,'EL BAGRE',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(50,'EL CARMEN DE VIBORAL',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(51,'EL PEÑOL',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(52,'EL RETIRO',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(53,'EL SANTUARIO',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(54,'ENTRERRIOS',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(55,'ENVIGADO',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(56,'FREDONIA',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(57,'FRONTINO',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(58,'GIRALDO',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(59,'GIRARDOTA',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(60,'GÓMEZ PLATA',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(61,'GRANADA',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(62,'GUADALUPE',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(63,'GUARNE',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(64,'GUATAPÉ',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(65,'HELICONIA',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(66,'HISPANIA',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(67,'ITAGÜÍ',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(68,'ITUANGO',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(69,'JARDÍN',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(70,'JERICÓ',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(71,'LA CEJA DEL TAMBO',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(72,'LA ESTRELLA',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(73,'LA PINTADA',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(74,'LA UNIÓN',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(75,'LIBORINA',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(76,'MACEO',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(77,'MARINILLA',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(78,'MEDELLÍN',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(79,'MONTEBELLO',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(80,'MURINDÓ',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(81,'MUTATÁ',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(82,'NARIÑO',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(83,'NECOCLÍ',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(84,'NECHÍ',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(85,'OLAYA',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(86,'PEQUE',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(87,'PUEBLORRICO',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(88,'PUERTO BERRÍO',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(89,'PUERTO NARE',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(90,'REMEDIOS',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(91,'RIONEGRO',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(92,'SABANETA',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(93,'SALGAR',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(94,'SAN ANDRÉS DE CUERQUIA',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(95,'SAN CARLOS',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(96,'SAN FRANCISCO',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(97,'SAN JERÓNIMO',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(98,'SAN JOSÉ DE LA MONTAÑA',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(99,'SAN JUAN DE URABÁ',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(100,'SAN LUIS',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(101,'SAN PEDRO',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(102,'SAN RAFAEL',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(103,'SAN ROQUE',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(104,'SAN VICENTE',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(105,'SANTA BÁRBARA',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(106,'SANTA FE DE ANTIOQUIA',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(107,'SANTA ROSA DE OSOS',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(108,'SANTO DOMINGO',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(109,'EL SOCORRO',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(110,'SOPETRÁN',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(111,'TÁMESIS',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(112,'TARAZÁ',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(113,'TASCO',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(114,'TITIRIBÍ',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(115,'TOLEDO',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(116,'TURBO',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(117,'URAMITA',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(118,'VALDIVIA',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(119,'VALLE DE ABURRÁ',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(120,'VALLE DE SAN NICOLÁS',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(121,'VÉLEZ',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(122,'VILLAMARÍA',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(123,'YALÍ',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(124,'YARUMAL',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(125,'YOLOMBÓ',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(126,'YONDO',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(127,'ZARAGOZA',5,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(128,'ARAUCA',81,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(129,'ARAUQUITA',81,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(130,'CRAVO NORTE',81,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(131,'FORTUL',81,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(132,'PUERTO RONDÓN',81,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(133,'SARAVENA',81,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(134,'TAME',81,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(135,'BARANOA',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(136,'CAMPO DE LA CRUZ',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(137,'CANDELARIA',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(138,'COLOSO',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(139,'CURUMANÍ',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(140,'FONSECA',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(141,'GALAPA',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(142,'GRAN ESTACIÓN',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(143,'GUACAMAYAS',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(144,'GUARANDA',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(145,'LA AVANZADA',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(146,'LA CEJA',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(147,'LA ESPERANZA',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(148,'LA PAZ',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(149,'MANATÍ',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(150,'PALMAR DE VARELA',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(151,'PIJIÑO DEL CARMEN',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(152,'PILONES',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(153,'PISIÓN',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(154,'PLATO',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(155,'PUERTO COLOMBIA',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(156,'RIOVEHÍCULO',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(157,'SABANAGRANDE',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(158,'SABANALARGA',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(159,'SANTA BÁRBARA',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(160,'SANTA CATALINA',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(161,'SANTA ROSA',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(162,'SANTO DOMINGO',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(163,'SAN JUAN NEPOMUCENO',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(164,'SAN MARCOS',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(165,'SUAN',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(166,'TALAMANCA',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(167,'USIACURÍ',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(168,'VALLEDUPAR',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(169,'VÉLEZ',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(170,'VENECIA',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(171,'VILLACOLOR',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(172,'ZAPAYÁN',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(173,'ZONA BANANERA',8,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(174,'BOGOTÁ',11,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(175,'ACHI',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(176,'ALTOS DEL ROSARIO',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(177,'AMAYA',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(178,'ARJONA',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(179,'ARROYOHONDO',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(180,'BARRACAS',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(181,'BRAZUELO DE LA VICTORIA',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(182,'CALAMA',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(183,'CANTAGALLO',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(184,'CICUCO',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(185,'CLEMENCIA',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(186,'EL CARMEN DE BOLIVAR',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(187,'EL GUAMO',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(188,'EL PEÑON',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(189,'HATILLO DE LOBA',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(190,'MAGANGUÉ',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(191,'MAHATES',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(192,'MARGARITA',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(193,'MARÍA LA BAJA',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(194,'MONTECRISTO',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(195,'MORALES',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(196,'NOROSI',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(197,'PINILLOS',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(198,'REGIDOR',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(199,'RIO VIEJO',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(200,'SAN CRISTÓBAL',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(201,'SAN ESTANISLAO',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(202,'SAN FERNANDO',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(203,'SAN JACINTO',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(204,'SAN JACINTO DEL CAUCA',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(205,'SAN JUAN NEPOMUCENO',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(206,'SAN MARTÍN DE LOBA',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(207,'SAN PABLO',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(208,'SANTA CATALINA',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(209,'SANTA ROSA',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(210,'SANTA ROSA DEL SUR',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(211,'SIMITÍ',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(212,'SOPLAVIENTO',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(213,'TALAURIAL',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(214,'TIQUISIO',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(215,'TURBACO',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(216,'TURBANA',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(217,'VILLANUEVA',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(218,'ZAMBRANO',13,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(219,'ALMEIDA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(220,'AQUITANIA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(221,'ARCABUCO',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(222,'BELÉN',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(223,'BERBEO',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(224,'BETÉITIVA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(225,'BOYACÁ',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(226,'BRICEÑO',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(227,'BUENAVISTA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(228,'BUSBANZÁ',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(229,'CALDAS',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(230,'CAMPOHERMOSO',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(231,'CERINZA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(232,'CHINAVITA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(233,'CHIQUINQUIRÁ',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(234,'CHISCAS',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(235,'CHITARAQUE',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(236,'CHIVATA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(237,'CIÉNEGA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(238,'CÓMBITA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(239,'COPER',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(240,'CORRALES',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(241,'COVARACHÍA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(242,'CUBARÁ',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(243,'CUCAITA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(244,'CUITIVA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(245,'CHÍCHARO',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(246,'EL COPEY',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(247,'FIRAVITOBA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(248,'FLORESTA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(249,'GACHANTIVÁ',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(250,'GAMEZA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(251,'GARAGOA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(252,'GUACAMAYAS',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(253,'GUATEQUE',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(254,'GUAYATÁ',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(255,'GÜICÁN DE LA SIERRA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(256,'IZA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(257,'JENESANO',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(258,'JERICÓ',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(259,'LABRANZAGRANDE',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(260,'LA ULABA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(261,'LEIVA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(262,'MACANAL',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(263,'MARIPÍ',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(264,'MIRAFLORES',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(265,'MONGUA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(266,'MONSERRAT',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(267,'MOTAVITA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(268,'MUZO',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(269,'NOBSA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(270,'NUEVO COLÓN',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(271,'OICATÁ',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(272,'OTANCHE',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(273,'PACHAVITA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(274,'PÁEZ',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(275,'PAIPA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(276,'PAJARITO',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(277,'PALMAR DE VARELA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(278,'PANTANO',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(279,'PARAMO',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(280,'PÁRAMO',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(281,'PAZ DE RÍO',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(282,'PESCA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(283,'PISBA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(284,'PUERTO BOYACÁ',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(285,'QUÍPAMA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(286,'RAMIRIQUÍ',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(287,'RÁQUIRA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(288,'RONDÓN',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(289,'SABOYÁ',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(290,'SÁCHICA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(291,'SAMACÁ',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(292,'SAN EDUARDO',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(293,'SAN JOSÉ DE PARE',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(294,'SAN LUIS DE GACENO',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(295,'SAN MATEO',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(296,'SANTA SOFÍA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(297,'SANTUARIO',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(298,'SATIVANORTE',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(299,'SATIVASUR',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(300,'SIACHOQUE',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(301,'SOATÁ',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(302,'SOCOTÁ',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(303,'SOCHA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(304,'SOGAMOSO',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(305,'SOMONDOCO',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(306,'SORA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(307,'SOTAQUIRÁ',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(308,'SUSACÓN',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(309,'SUTAMARCHÁN',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(310,'SUTATENZA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(311,'TASCO',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(312,'TENERIFE',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(313,'TIBACUY',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(314,'TINJACÁ',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(315,'TIPACOQUE',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(316,'TOGÜÍ',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(317,'TÓPAGA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(318,'TOTA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(319,'TUNUNGUI',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(320,'TURMEQUÉ',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(321,'TUTA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(322,'TUTAZÁ',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(323,'UMBITÁ',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(324,'VENTAQUEMADA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(325,'VILLA DE LEYVA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(326,'YACOPÍ',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(327,'ZETAQUIRA',15,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(328,'AGUADAS',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(329,'ANSERMA',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(330,'ARUETO',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(331,'BAHÍA SOLANO',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(332,'BALBOA',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(333,'BOLÍVAR',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(334,'BRICEÑO',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(335,'BUENOS AIRES',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(336,'CALDAS',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(337,'CHINCHINÁ',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(338,'CÓRDOBA',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(339,'DOS QUEBRADAS',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(340,'GUACAMAYAS',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(341,'GUATICA',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(342,'GAVETE',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(343,'HIRACACHA',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(344,'JARDÍN',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(345,'LA DORADA',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(346,'LA MERCED',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(347,'MANIZALES',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(348,'MARQUETALIA',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(349,'MARULANDA',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(350,'MISTRATÓ',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(351,'NEIRA',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(352,'NORCASIA',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(353,'PÁCORA',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(354,'PALESTINA',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(355,'PENSILVANIA',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(356,'PEZ VÁZQUEZ',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(357,'PUEBLO RICO',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(358,'PUERTO ROMERO',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(359,'QUIBDÓ',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(360,'RÍO SUÁREZ',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(361,'SAMANÁ',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(362,'SAN JOSÉ',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(363,'SUSACÓN',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(364,'SUTATENZA',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(365,'TEMBLORES',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(366,'TOTA',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(367,'URABA',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(368,'VALPARAÍSO',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(369,'VÉLEZ',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(370,'VILLAMARÍA',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(371,'YOLOMBÓ',17,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(372,'ALBANIA',18,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(373,'BELEN DE LOS ANDAQUIES',18,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(374,'BELÉN',18,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(375,'BOYACÁ',18,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(376,'CURILLO',18,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(377,'EL DONCELLO',18,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(378,'EL PAUJIL',18,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(379,'FLORENCIA',18,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(380,'LA MONTAÑITA',18,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(381,'MORELIA',18,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(382,'PUERTO RICO',18,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(383,'SAN JOSÉ DEL FRAGUA',18,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(384,'SAN VICENTE DEL CAGUÁN',18,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(385,'SOLANO',18,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(386,'SOLARTA',18,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(387,'VALPARAÍSO',18,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(388,'VISTAHERMOSA',18,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(389,'AGUAZUL',85,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(390,'CHÁMEZA',85,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(391,'HATO COROZAL',85,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(392,'LA SALINA',85,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(393,'MANÍ',85,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(394,'MONTERREY',85,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(395,'NUNCHÍA',85,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(396,'OROCUÉ',85,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(397,'PAZ DE ARIPORO',85,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(398,'PUERTO DE CÚCUTA',85,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(399,'PUERTO NARE',85,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(400,'PUERTO SALGAR',85,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(401,'REMOLINO',85,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(402,'SABANAS DE SAN ÁNGEL',85,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(403,'SACAMA',85,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(404,'SAN LUIS DE GACENO',85,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(405,'SAN VICENTE DEL CAGUÁN',85,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(406,'SANTA ROSA',85,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(407,'TAMARA',85,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(408,'TAURAMENA',85,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(409,'TRINIDAD',85,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(410,'VILLANUEVA',85,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(411,'AGUACHICA',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(412,'ALMAGUER',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(413,'ARGELIA',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(414,'BALBOA',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(415,'BOLÍVAR',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(416,'BUENOS AIRES',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(417,'CAJIBÍO',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(418,'CALDONÓ',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(419,'CAPUL',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(420,'CARLOSPA',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(421,'CHAPARRAL',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(422,'COLOSO',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(423,'CORINTO',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(424,'CUBARÁ',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(425,'CURILLO',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(426,'FLORENCIA',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(427,'GUAPI',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(428,'INZERCIÓN',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(429,'JAMBALÓ',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(430,'LA SIERRA',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(431,'LA VEGA',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(432,'LOPEZ DE MICAY',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(433,'MERCADERES',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(434,'MIRANDA',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(435,'MORALES',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(436,'PADILLA',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(437,'PÁEZ',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(438,'PATIA',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(439,'PIAMONTE',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(440,'PIENDAMO',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(441,'PUERTO TEJADA',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(442,'PUPIAL',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(443,'ROSAS',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(444,'SAN SEBASTIÁN',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(445,'SANTANDER DE QUILICHAO',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(446,'SANTA ROSA',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(447,'SILVIA',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(448,'SUCRE',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(449,'SUÁREZ',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(450,'TIMBIQUÍ',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(451,'TORIBIO',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(452,'TOTORÓ',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(453,'VILLA RICA',19,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(454,'AGUACHICA',20,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(455,'AGUSTÍN CODAZZI',20,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(456,'ASTREA',20,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(457,'BECERRIL',20,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(458,'BOSCONIA',20,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(459,'CHIRIGUANA',20,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(460,'CODAZZI',20,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(461,'CURUMANÍ',20,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(462,'EL COPEY',20,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(463,'EL PASO',20,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(464,'GAMARRA',20,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(465,'GONZÁLEZ',20,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(466,'LA PAZ',20,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(467,'MANAURE',20,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(468,'PAILITAS',20,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(469,'PELAYA',20,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(470,'PUEBLO BELLO',20,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(471,'RÍO DE ORO',20,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(472,'SAN ALBERTO',20,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(473,'SAN DIEGO',20,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(474,'SAN JUAN DEL CESAR',20,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(475,'TAMALAMEQUE',20,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(476,'VALLEDUPAR',20,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(477,'ACANDÍ',27,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(478,'ALTO BAUDÓ',27,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(479,'ATRATO',27,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(480,'BAGADÓ',27,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(481,'BAHÍA SOLANO',27,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(482,'BAJO BAUDÓ',27,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(483,'BELÉN DE LOS ANDAQUÍES',27,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(484,'BETÉITIVA',27,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(485,'BOJAYA',27,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(486,'CANTÓN DE SAN PABLO',27,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(487,'CERRO AZUL',27,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(488,'CONDOTO',27,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(489,'EL CARMEN DE ATRATO',27,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(490,'EL LITORAL DEL SAN JUAN',27,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(491,'ISTMINA',27,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(492,'JURADÓ',27,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(493,'LLORÓ',27,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(494,'MEDIO ATRATO',27,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(495,'MEDIO BAUDÓ',27,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(496,'MEDIO SAN JUAN',27,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(497,'NOVITA',27,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(498,'NUÍ',27,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(499,'PUEBLO RICO',27,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(500,'QUIBDÓ',27,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(501,'RÍO IRO',27,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(502,'RÍO QUITO',27,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(503,'RIOSUCIO',27,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(504,'SAN JOSÉ DEL PALMAR',27,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(505,'SANTA GENOVEVA DE DOCO',27,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(506,'SANTA ROSA',27,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(507,'SANTO DOMINGO',27,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(508,'SIPI',27,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(509,'TADÓ',27,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(510,'UNGUÍA',27,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(511,'UNIÓN PANAMERICANA',27,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(512,'AYAPEL',23,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(513,'BUENAVISTA',23,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(514,'CANALETE',23,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(515,'CERETÉ',23,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(516,'CHIMÁ',23,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(517,'CHINÚ',23,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(518,'CIÉNAGA DE ORO',23,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(519,'COTORRA',23,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(520,'LA APARTADA',23,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(521,'LOS CÓRDOBAS',23,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(522,'MOMIL',23,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(523,'MONTELÍBANO',23,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(524,'MONTERÍA',23,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(525,'MOÑITOS',23,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(526,'PLANES DE BOLÍVAR',23,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(527,'PUEBLO NUEVO',23,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(528,'PUERTO ESCONDIDO',23,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(529,'PUERTO LIBERTADOR',23,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(530,'PURÍSIMA',23,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(531,'SAHAGÚN',23,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(532,'SAN ANDRÉS DE SOTAVENTO',23,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(533,'SAN ANTERO',23,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(534,'SAN BERNARDO DEL VIENTO',23,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(535,'SAN CARLOS',23,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(536,'SAN PELAYO',23,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(537,'TIERRALTA',23,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(538,'TUCHÍN',23,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(539,'VALLEDUPAR',23,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(540,'VALENCIA',23,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(541,'AGUA DE DIOS',25,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(542,'ALBAN',25,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(543,'ANOLAIMA',25,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(544,'ARBELAEZ',25,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(545,'BELTRAN',25,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(546,'BITUIMA',25,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(547,'BOJACA',25,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(548,'CABRERA',25,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(549,'CACHIPAY',25,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(550,'CAJICA',25,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(551,'CAPARRAPI',25,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(552,'CAQUEZA',25,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(553,'CARMEN DE CARUPA',25,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(554,'CHAGUANI',25,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(555,'CHIA',25,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(556,'CHIPAQUE',25,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(557,'CHOACHI',25,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(558,'CHOCONTÁ',25,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(559,'CIENAGA',25,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(560,'COGUA',25,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(561,'COTACACHI',25,'2026-06-17 06:01:46','2026-06-17 06:01:46'),(562,'CUCUNUBA',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(563,'EL COLEGIAL',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(564,'EL ROSAL',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(565,'FACATATIVA',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(566,'FOMEQUE',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(567,'FONSECA',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(568,'FUNZA',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(569,'FÚQUENE',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(570,'FUSAGASUGÁ',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(571,'GACHALA',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(572,'GACHANCIPA',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(573,'GACHETA',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(574,'GAMA',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(575,'GIRARDOT',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(576,'GRANADA',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(577,'GUACAMAYAS',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(578,'GUADUAS',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(579,'GUASCA',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(580,'GUATAQUI',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(581,'GUAYABAL DE SIQUIMA',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(582,'GUAYABETAL',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(583,'HONDA',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(584,'MADRID',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(585,'MANTA',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(586,'MEDINA',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(587,'MOSQUERA',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(588,'NEMOCÓN',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(589,'NILO',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(590,'NIMAIMA',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(591,'NOCAIMA',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(592,'PACHO',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(593,'PAIME',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(594,'PANDI',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(595,'PARATEBUENO',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(596,'PASCA',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(597,'PUERTO SALGAR',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(598,'PULÍ',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(599,'QUEBRADANEGRA',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(600,'QUETAME',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(601,'QUIPILE',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(602,'RICAURTE',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(603,'SAN ANTONIO DEL TEQUENDAMA',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(604,'SAN FRANCISCO',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(605,'SAN JUAN DE RIOSEO',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(606,'SAN LUIS DE GACENO',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(607,'SAN MARCOS',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(608,'SASAIMA',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(609,'SESQUILÉ',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(610,'SIBATÉ',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(611,'SILVANIA',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(612,'SIMIJACA',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(613,'SOACHA',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(614,'SOPO',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(615,'SUBACHOQUE',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(616,'SUESCA',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(617,'SUPATÁ',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(618,'SUSA',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(619,'SUTATAUSA',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(620,'TABIO',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(621,'TENA',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(622,'TENJO',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(623,'TIBACUY',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(624,'TIERRA DENTRO',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(625,'TIMIZA',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(626,'TOCAIMA',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(627,'TOCANCIPÁ',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(628,'TOPAIPI',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(629,'UBALDO',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(630,'UBAQUE',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(631,'UBATE',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(632,'UNE',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(633,'ÚTICA',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(634,'VALLE DE SAN JOSE',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(635,'VIANI',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(636,'VIOTA',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(637,'YACOPÍ',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(638,'ZAPALLAL',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(639,'ZARZAL',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(640,'ZIPAQUIRÁ',25,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(641,'INÍRIDA',94,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(642,'BARRANCO MINAS',94,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(643,'MAPIRIPANA',94,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(644,'SAN FELIPE',94,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(645,'PUERTO COLOMBIA',94,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(646,'LA GUADALUPE',94,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(647,'CACHIRÚ',94,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(648,'PANA PANA',94,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(649,'PUERTO SÁNCHEZ',94,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(650,'TAMARA',94,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(651,'SAN JOSÉ DEL GUAVIARE',95,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(652,'CALAMAR',95,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(653,'EL RETORNO',95,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(654,'MIRAFLORES',95,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(655,'PUERTO SANTANDER',95,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(656,'LAURELES',95,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(657,'CUMARAL',95,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(658,'SAN FELIPE',95,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(659,'BARRANCA DE UPIA',95,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(660,'CABUYARO',95,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(661,'ACEVEDO',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(662,'AGRADO',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(663,'AIPE',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(664,'ALGECIRAS',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(665,'ALTAMIRA',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(666,'BARAYA',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(667,'CAMPOALEGRE',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(668,'COLOMBIA',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(669,'ELÍAS',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(670,'FENTON',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(671,'GARZÓN',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(672,'GIGANTE',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(673,'GUADALAS',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(674,'HOBO',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(675,'IQUIRA',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(676,'ISNOS',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(677,'JAMBALÓ',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(678,'LAS VEGAS',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(679,'LA ARGENTA',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(680,'LA PLATA',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(681,'MACOA',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(682,'MESETAS',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(683,'MONTAGUA',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(684,'MUÑICO',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(685,'NEIVA',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(686,'NOVIEMBRE DE 1821',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(687,'ORITO',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(688,'PAICOL',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(689,'PALERMO',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(690,'PALESTINA',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(691,'PITAL',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(692,'PITALITO',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(693,'RIVERA',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(694,'ROSELLÓN',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(695,'SALADOBLANCO',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(696,'SAN AGUSTÍN',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(697,'SAN ANTONIO',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(698,'SAN LUIS',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(699,'SANTA MARÍA',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(700,'SUAZA',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(701,'TARQUI',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(702,'TESALIA',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(703,'TIMANÁ',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(704,'VILLAVIEJA',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(705,'YAGUARA',41,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(706,'ALBANIA',44,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(707,'BARRANCAS',44,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(708,'DIBULLA',44,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(709,'DISTRACCIÓN',44,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(710,'EL MOLINO',44,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(711,'FONSECA',44,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(712,'GARZON',44,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(713,'GONZÁLEZ',44,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(714,'HATONUEVO',44,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(715,'LABATECA',44,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(716,'LA JAGUA DEL PILAR',44,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(717,'MAICAO',44,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(718,'MANAURE',44,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(719,'MORROA',44,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(720,'NOVITA',44,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(721,'PIJIÑO DEL CARMEN',44,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(722,'PLATO',44,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(723,'RIOHACHA',44,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(724,'SAN JUAN DE LA COSTA',44,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(725,'SAN JUAN DEL CESAR',44,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(726,'TAMARA',44,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(727,'TARIFA',44,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(728,'URIBIA',44,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(729,'URUMITA',44,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(730,'VILLANUEVA',44,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(731,'ALGARROBO',47,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(732,'ALGARROBO',47,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(733,'ALMERÍA',47,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(734,'BARRANCAS',47,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(735,'BOCACHICO',47,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(736,'BUENA VISTA',47,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(737,'CAMELIA',47,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(738,'CERRO DE SAN ANTONIO',47,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(739,'CHIVOLO',47,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(740,'CIÉNAGA',47,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(741,'CONCORDIA',47,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(742,'EL BANCO',47,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(743,'EL PIÑÓN',47,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(744,'EL RETEN',47,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(745,'FUNDACIÓN',47,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(746,'GUAMAL',47,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(747,'NUEVA GRANADA',47,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(748,'PEDRAZA',47,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(749,'PIJIÑO DEL CARMEN',47,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(750,'PILAR',47,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(751,'PLATO',47,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(752,'PUEBLO VIEJO',47,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(753,'REMOLINO',47,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(754,'SABANAS DE SAN ÁNGEL',47,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(755,'SALAMINA',47,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(756,'SAN SEBASTIÁN DE BUENAVISTA',47,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(757,'SAN ZENÓN',47,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(758,'SANTA ANA',47,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(759,'SANTA BÁRBARA DE PINTO',47,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(760,'SITIONUEVO',47,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(761,'TENERIFE',47,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(762,'VALLEDUPAR',47,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(763,'VEGANOS',47,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(764,'ZAPAYÁN',47,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(765,'ZONA BANANERA',47,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(766,'ACACÍAS',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(767,'BARRANCA DE UPIA',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(768,'CABUYARO',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(769,'CASTILLA LA NUEVA',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(770,'CUBARRAL',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(771,'CUMARAL',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(772,'EL CALVARIO',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(773,'EL CASTILLO',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(774,'EL DORADO',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(775,'FUENTE DE ORO',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(776,'GRANADA',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(777,'GUAMAL',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(778,'LA MACARENA',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(779,'LA URIBE',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(780,'LEJÍAS',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(781,'MAPIRIPÁ',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(782,'MESETAS',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(783,'LA VICTORIA',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(784,'OBANDO',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(785,'PUERTO CONCEPCIÓN',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(786,'PUERTO LLERAS',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(787,'PUERTO SANTANDER',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(788,'REGIDOR',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(789,'REMONTE',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(790,'RESTREPOS',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(791,'RÍO FRÍO',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(792,'SALDAÑA',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(793,'SAN CARLOS DE GUAROA',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(794,'SAN FRANCISCO',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(795,'SAN JUAN DE ARAMA',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(796,'SAN JOSÉ DEL PACÍFICO',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(797,'SAN LUIS DE GACENO',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(798,'SAN PEDRO DE LOS MILAGROS',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(799,'SAN VICENTE DEL CAGUÁN',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(800,'SANTA ROSA DE YOPAL',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(801,'SATIFICA',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(802,'SEOY',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(803,'SERREZUELA',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(804,'SOLANO',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(805,'SOLARTA',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(806,'TÁMARA',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(807,'TAURAMENA',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(808,'TESALIA',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(809,'TIMAYA',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(810,'VISTAHERMOSA',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(811,'VILLAVICENCIO',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(812,'YOPAL',50,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(813,'ABREGO',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(814,'ALDANA',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(815,'ANCUYA',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(816,'ARBOLEDA',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(817,'BARBACOAS',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(818,'BELÉN',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(819,'BUESACO',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(820,'CÓRDOBA',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(821,'CHACHAGÜÍ',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(822,'COLÓN',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(823,'CONSACA',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(824,'CONTADERO',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(825,'CÓRDOBA',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(826,'CUASPÚD',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(827,'CUMBAL',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(828,'CUMBITARA',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(829,'DEPARTAMENTO DE NARIÑO',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(830,'EL CHARCO',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(831,'EL PEÑOL',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(832,'EL ROSARIO',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(833,'EL TABLÓN DE GÓMEZ',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(834,'EL TAMBO',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(835,'FUNES',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(836,'GUACHUCAL',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(837,'GUAITARILLA',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(838,'GUALMATÁN',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(839,'ILES',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(840,'IMUÉS',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(841,'IPIALES',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(842,'LA CRUZ',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(843,'LA FLORIDA',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(844,'LA LLANADA',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(845,'LA TOLA',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(846,'LA UNIÓN',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(847,'LEIVA',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(848,'LINARES',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(849,'LOS ANDES',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(850,'MAGÜÍ',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(851,'MALLAMA',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(852,'MOSQUERA',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(853,'NARIÑO',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(854,'OLAYA HERRERA',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(855,'OSPINA',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(856,'PÁEZ',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(857,'PATIA',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(858,'PIEDRANCHA',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(859,'PUERRES',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(860,'PUERTO CARREÑO',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(861,'PUPiales',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(862,'RICAURTE',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(863,'ROBERTO PAYAN',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(864,'SAMANIEGO',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(865,'SAN ANDRÉS DE TUMACO',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(866,'SAN JOSÉ',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(867,'SAN JUAN DE PASTO',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(868,'SAN LORENZO',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(869,'SAN PABLO',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(870,'SAN PEDRO DE CARTAGO',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(871,'SANTA BÁRBARA',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(872,'SANTACRUZ',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(873,'SAPUYES',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(874,'TAMINANGO',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(875,'TANGUA',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(876,'TUMACO',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(877,'TUQUERRES',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(878,'YACUANQUER',52,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(879,'ABREGO',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(880,'ARBOLEDAS',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(881,'BOCHALEMA',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(882,'BUCARASICA',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(883,'CÁCOTA',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(884,'CÓRDOBA',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(885,'CUCUTA',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(886,'CUMARAL',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(887,'DURANIA',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(888,'EL CARMELO',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(889,'EL TARRA',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(890,'FACATATIVA',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(891,'GAMARRA',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(892,'GONZÁLEZ',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(893,'HACARÍ',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(894,'HERRAN',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(895,'LABATECA',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(896,'LA ESPERANZA',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(897,'LA PAZ',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(898,'LA PLAYA',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(899,'LOS PATIOS',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(900,'LOURDES',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(901,'MUTISCUA',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(902,'OCAÑA',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(903,'PAMPLONA',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(904,'PUERTO SANTANDER',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(905,'RAGONVALIA',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(906,'SALAZAR',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(907,'SAN CALIXTO',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(908,'SAN CAYETANO',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(909,'SANTIAGO',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(910,'SANTO DOMINGO',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(911,'SARDINATA',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(912,'TEORAMA',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(913,'TIBÚ',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(914,'TOLEDO',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(915,'VILLA CARO',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(916,'VILLA DEL ROSARIO',54,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(917,'PUERTO ASIS',86,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(918,'VILLAGARZÓN',86,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(919,'MIRITÍ-PARANÁ',86,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(920,'SAN FRANCISCO',86,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(921,'SAN MIGUEL',86,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(922,'SANTIAGO',86,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(923,'Leticia',86,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(924,'EL ENCANTO',86,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(925,'LA CHORRERA',86,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(926,'PUERTO ALEGRE',86,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(927,'PUERTO NARIÑO',86,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(928,'PUERTO SANTANDER',86,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(929,'TURBACO',86,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(930,'EL BANCO',86,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(931,'LA PRIMAVERA',86,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(932,'CARURÚ',86,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(933,'PASTO',86,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(934,'MOCOA',86,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(935,'ORITO',86,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(936,'PUERTO LEGUÍZAMO',86,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(937,'SUCRE',86,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(938,'VALLE DEL GUAMUEZ',86,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(939,'ARMENIA',63,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(940,'BUENAVISTA',63,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(941,'CALARCA',63,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(942,'CIRCASIA',63,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(943,'COATÁ',63,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(944,'FILANDIA',63,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(945,'GENEVA',63,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(946,'LA TEBAIDA',63,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(947,'MONTENEGRO',63,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(948,'PUERTO RICO',63,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(949,'QUIMBAYA',63,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(950,'SALONICA',63,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(951,'SAN JUAN DE PACAMAL',63,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(952,'VILLATURNA',63,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(953,'AGUADAS',66,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(954,'ANSERMA',66,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(955,'ARAUCA',66,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(956,'BELÉN DE UMBRÍA',66,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(957,'CHINCHINÁ',66,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(958,'FILANDIA',66,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(959,'GENEVA',66,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(960,'GUATICÁ',66,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(961,'MANIZALES',66,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(962,'MARQUETALIA',66,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(963,'MARULANDA',66,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(964,'MISTRATÓ',66,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(965,'PACORA',66,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(966,'PALESTINA',66,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(967,'PENSILVANIA',66,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(968,'RIOSUCIO',66,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(969,'RISARALDA',66,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(970,'SALAMINA',66,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(971,'SAMANÁ',66,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(972,'SAN JOSÉ',66,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(973,'SUPIA',66,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(974,'VICTORIA',66,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(975,'VILLAMARÍA',66,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(976,'YOLOMBÓ',66,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(977,'SAN ANDRÉS',88,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(978,'PROVIDENCIA',88,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(979,'CACAOTAL',88,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(980,'CENTRO',88,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(981,'GRACIAS A DIOS',88,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(982,'LA LAGUNA',88,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(983,'LA PAZ',88,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(984,'LOS ROBLES',88,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(985,'MARAVILLA',88,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(986,'MORGANS',88,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(987,'ABREGO',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(988,'ALBANIA',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(989,'ARATOCA',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(990,'BARBOSA',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(991,'BARICHARA',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(992,'BARRANCABERMEJA',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(993,'BETULIA',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(994,'BOLÍVAR',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(995,'CABEZAS',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(996,'CALIFORNIA',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(997,'CAPITANEJO',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(998,'CARCASI',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(999,'CEPITÁ',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1000,'CERRITO',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1001,'CHARALÁ',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1002,'CHARTA',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1003,'CHIMICHINA',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1004,'CHIPATÁ',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1005,'CIMITARRA',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1006,'CONFINES',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1007,'CONTRATACIÓN',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1008,'COROMORO',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1009,'CURITÍ',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1010,'EL CARMEN DE CHUCURÚ',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1011,'EL GUACAMAYO',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1012,'EL PEÑÓN',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1013,'ENCINO',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1014,'FLORIDABLANCA',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1015,'GALÁN',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1016,'GÁMBITA',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1017,'GIRÓN',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1018,'GUACA',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1019,'GUADALUPE',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1020,'GUAPOTA',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1021,'GUAVATA',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1022,'GÜEPSA',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1023,'HATO',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1024,'JORDÁN',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1025,'LA BELLEZA',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1026,'LANDÁZURI',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1027,'LA FLORIDA',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1028,'LA PAZ',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1029,'LEBRIJA',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1030,'LOS SANTOS',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1031,'MACARAVITA',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1032,'MÁLAGA',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1033,'MATANZA',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1034,'MOGOTES',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1035,'MOLAGAVITA',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1036,'MORALES',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1037,'NOREÑA',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1038,'OIBA',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1039,'ONZAGA',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1040,'PALMAR',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1041,'PALMAS DEL SOCORRO',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1042,'PÁRAMO',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1043,'PIEDECUESTA',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1044,'PINCHOTE',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1045,'PUENTE NACIONAL',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1046,'PUERTO BERRÍO',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1047,'PUERTO FLOREZ',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1048,'PUERTO SANTANDER',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1049,'RAFAEL URIBE URIBE',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1050,'RIONEGRO',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1051,'SABANA DE TORRES',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1052,'SAN ANDRÉS',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1053,'SAN BENITO',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1054,'SAN GIL',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1055,'SAN JOAQUÍN',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1056,'SAN JOSÉ DE MIRANDA',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1057,'SAN MARCIAL',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1058,'SAN MIGUEL',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1059,'SAN VICENTE DE CHUCURÍ',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1060,'SANTA BÁRBARA',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1061,'SANTA HELENA DEL OPÓN',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1062,'SIMACOTA',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1063,'SOCORRO',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1064,'SUAITA',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1065,'SUCRE',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1066,'SURATA',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1067,'TONA',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1068,'VALDEZUELA',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1069,'VÉLEZ',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1070,'VILLANUEVA',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1071,'YARUMAL',68,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1072,'BUENAVISTA',70,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1073,'CAIMITO',70,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1074,'CHINÚ',70,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1075,'CIÉNAGA DE ORO',70,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1076,'COLOSO',70,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1077,'COROZAL',70,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1078,'COVEÑAS',70,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1079,'EL ROBLE',70,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1080,'GALERAS',70,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1081,'GUARANDA',70,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1082,'LA UNIÓN',70,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1083,'LOS CÓRDOBAS',70,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1084,'MÁLAGA',70,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1085,'MORROA',70,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1086,'OVEJAS',70,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1087,'PALMITO',70,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1088,'SAMPUÉS',70,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1089,'SAN BENITO ABAD',70,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1090,'SAN JOSÉ DE TOLU',70,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1091,'SAN JUAN DE BETULIA',70,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1092,'SAN MARCOS',70,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1093,'SAN ONOFRE',70,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1094,'SAN PEDRO',70,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1095,'SAN VICENTE DE CHUCURÍ',70,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1096,'SINCE',70,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1097,'SINCELEJO',70,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1098,'SUCRE',70,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1099,'TOLÚ VIEJO',70,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1100,'TOLÚ',70,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1101,'VALLEDUPAR',70,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1102,'ALVARADO',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1103,'AMBALEMA',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1104,'ANZOÁTEGUI',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1105,'ARMENIA',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1106,'ATACO',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1107,'CAJAMARCA',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1108,'CARRILLO',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1109,'CASABIANCA',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1110,'CHAPARRAL',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1111,'COYAIMA',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1112,'CUNDAY',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1113,'DOLORES',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1114,'ESPINAL',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1115,'FALAN',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1116,'FLANDES',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1117,'FRESNO',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1118,'GUAMO',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1119,'HERVEO',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1120,'HONDA',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1121,'IBAGUÉ',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1122,'ICONONZO',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1123,'LÉRIDA',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1124,'LIBANO',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1125,'MARIQUITA',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1126,'MELGAR',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1127,'MURILLO',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1128,'NATAGAIMA',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1129,'ORTEGA',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1130,'PALOCABILDO',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1131,'PRAEDO',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1132,'PURIFICACIÓN',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1133,'RICAURTE',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1134,'ROLDANILLO',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1135,'RONCESVALLES',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1136,'SAN ANTONIO',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1137,'SAN LUIS',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1138,'SANTA ISABEL',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1139,'SUAREZ',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1140,'VALLE DE SAN JOSÉ',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1141,'VENADAS',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1142,'VILLAHERMOSA',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1143,'VILLARRICA',73,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1144,'ALCALÁ',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1145,'ANDALUCÍA',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1146,'ANSERMANUEVO',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1147,'ARGELIA',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1148,'BOLÍVAR',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1149,'BUENAVENTURA',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1150,'BUGA',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1151,'BUGALAGRANDE',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1152,'CAICEDONIA',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1153,'CALI',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1154,'CANDELARIA',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1155,'CARTAGO',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1156,'DAGUA',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1157,'EL ÁGUILA',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1158,'EL CAIRO',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1159,'EL CERRITO',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1160,'EL DOVIO',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1161,'FLORIDA',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1162,'GINEBRA',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1163,'GUACARÍ',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1164,'JAMUNDÍ',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1165,'LA CUMBRE',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1166,'LA UNIÓN',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1167,'LA VICTORIA',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1168,'OBANDO',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1169,'PALMIRA',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1170,'PRADERA',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1171,'RESTREPOS',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1172,'RIOFRÍO',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1173,'ROLDANILLO',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1174,'SAN PEDRO',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1175,'SEVILLA',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1176,'TORO',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1177,'TRUJILLO',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1178,'TULUÁ',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1179,'YOTOCO',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1180,'YUMBO',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1181,'ZARZAL',76,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1182,'MITÚ',97,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1183,'CARURÚ',97,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1184,'PACOA',97,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1185,'TARAIRA',97,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1186,'JOSE IGNACIO RONDON',97,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1187,'CUMARIBO',97,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1188,'MAPIRIPANA',97,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1189,'PUERTO CONCHAVIA',97,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1190,'SAN JUAN DE MAPIRIPANA',97,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1191,'YAHUA',97,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1192,'CUMARAL',99,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1193,'LA PRIMAVERA',99,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1194,'PUERTO GAITÁN',99,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1195,'PUERTO López',99,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1196,'PUERTO NARE',99,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1197,'PUERTO SANTANDER',99,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1198,'RESTREPOS',99,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1199,'SAN JOSÉ DE MIRANDA',99,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1200,'TRINIDAD',99,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(1201,'VILLAVICENCIO',99,'2026-06-17 06:01:47','2026-06-17 06:01:47');
/*!40000 ALTER TABLE `ciudades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clientes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `documento` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_documento` enum('CC','NIT','CE','PP') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'CC',
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccion1` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccion2` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `departamento_id` bigint unsigned NOT NULL,
  `ciudad_id` bigint unsigned NOT NULL,
  `saldo` decimal(15,2) NOT NULL DEFAULT '0.00',
  `pais` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Colombia',
  `estado` enum('activo','inactivo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `limite_credito` decimal(15,2) NOT NULL DEFAULT '0.00',
  `dias_credito` int NOT NULL DEFAULT '0',
  `dias_pago` int NOT NULL DEFAULT '0',
  `contacto_principal` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sitio_web` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `porcentaje_descuento` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT 'Descuento comercial del cliente (integradores/subdistribuidores)',
  `usuario_id` bigint unsigned DEFAULT NULL,
  `portal_acceso` enum('sin_acceso','pendiente','activo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sin_acceso' COMMENT 'Nivel de acceso al portal de clientes',
  `user_id_portal` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Hash bcrypt/argon2 — null = sin acceso al portal',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Token de sesión persistente (Auth estándar)',
  `email_verified_at` timestamp NULL DEFAULT NULL COMMENT 'Verificación de email (Auth estándar)',
  `password_changed_at` timestamp NULL DEFAULT NULL COMMENT 'null = contraseña temporal; forzar cambio en primer login',
  `portal_last_login_at` timestamp NULL DEFAULT NULL COMMENT 'Auditoría — último acceso al portal /clientes',
  PRIMARY KEY (`id`),
  UNIQUE KEY `clientes_documento_unique` (`documento`),
  UNIQUE KEY `clientes_email_unique` (`email`),
  KEY `clientes_departamento_id_foreign` (`departamento_id`),
  KEY `clientes_ciudad_id_foreign` (`ciudad_id`),
  KEY `clientes_usuario_id_foreign` (`usuario_id`),
  KEY `clientes_user_id_portal_foreign` (`user_id_portal`),
  CONSTRAINT `clientes_ciudad_id_foreign` FOREIGN KEY (`ciudad_id`) REFERENCES `ciudades` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `clientes_departamento_id_foreign` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `clientes_user_id_portal_foreign` FOREIGN KEY (`user_id_portal`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `clientes_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES (1,'CLIENTES VARIOS','999999999','CC','9999999999','no_tiene_correo@correo.com','SIN INFORMACION',NULL,54,889,0.00,'Colombia','activo',0.00,0,0,NULL,NULL,0.00,2,'sin_acceso',NULL,'2026-06-17 06:01:47','2026-06-17 06:01:47',NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `compras`
--

DROP TABLE IF EXISTS `compras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `compras` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('borrador','registrada','pendiente','pagada','anulada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador' COMMENT 'Ciclo de vida del documento',
  `confirmada_en` timestamp NULL DEFAULT NULL COMMENT 'Fecha en que se registró la compra',
  `proveedor_id` bigint unsigned NOT NULL,
  `bodega_id` bigint unsigned NOT NULL,
  `usuario_id` bigint unsigned DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `descuento` decimal(15,2) NOT NULL DEFAULT '0.00',
  `impuestos` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_confirmado` decimal(15,2) DEFAULT NULL COMMENT 'Total capturado en el momento del registro',
  `impuestos_confirmados` decimal(15,2) DEFAULT NULL COMMENT 'Impuestos capturados en el momento del registro',
  `snapshot_confirmacion` json DEFAULT NULL COMMENT 'Snapshot JSON de datos financieros al registrar',
  `saldo_pendiente` decimal(15,2) NOT NULL DEFAULT '0.00',
  `fecha_vencimiento` date DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `compras_numero_unique` (`numero`),
  KEY `compras_proveedor_id_foreign` (`proveedor_id`),
  KEY `compras_bodega_id_foreign` (`bodega_id`),
  KEY `compras_fecha_idx` (`fecha`),
  KEY `compras_estado_idx` (`estado`),
  KEY `compras_usuario_idx` (`usuario_id`),
  CONSTRAINT `compras_bodega_id_foreign` FOREIGN KEY (`bodega_id`) REFERENCES `bodegas` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `compras_proveedor_id_foreign` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `compras_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `compras`
--

LOCK TABLES `compras` WRITE;
/*!40000 ALTER TABLE `compras` DISABLE KEYS */;
/*!40000 ALTER TABLE `compras` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conteos_fisicos`
--

DROP TABLE IF EXISTS `conteos_fisicos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `conteos_fisicos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bodega_id` bigint unsigned NOT NULL,
  `usuario_id` bigint unsigned DEFAULT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_cierre` date DEFAULT NULL,
  `estado` enum('abierto','cerrado','ajustado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'abierto',
  `es_saldo_inicial` tinyint(1) NOT NULL DEFAULT '0',
  `observacion` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `conteos_fisicos_numero_unique` (`numero`),
  KEY `conteos_fisicos_usuario_id_foreign` (`usuario_id`),
  KEY `conteos_fisicos_bodega_id_index` (`bodega_id`),
  KEY `conteos_fisicos_fecha_inicio_index` (`fecha_inicio`),
  KEY `conteos_fisicos_estado_index` (`estado`),
  CONSTRAINT `conteos_fisicos_bodega_id_foreign` FOREIGN KEY (`bodega_id`) REFERENCES `bodegas` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `conteos_fisicos_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conteos_fisicos`
--

LOCK TABLES `conteos_fisicos` WRITE;
/*!40000 ALTER TABLE `conteos_fisicos` DISABLE KEYS */;
INSERT INTO `conteos_fisicos` VALUES (1,'CNT-00001',1,1,'2026-06-17',NULL,'abierto',1,'Inventario inicial','2026-06-17 07:57:54','2026-06-17 07:57:54');
/*!40000 ALTER TABLE `conteos_fisicos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cotizaciones`
--

DROP TABLE IF EXISTS `cotizaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cotizaciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cliente_id` bigint unsigned NOT NULL,
  `bodega_id` bigint unsigned NOT NULL,
  `usuario_id` bigint unsigned DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_vigencia` date DEFAULT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `descuento` decimal(15,2) NOT NULL DEFAULT '0.00',
  `impuestos` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `estado` enum('pendiente','enviada','aceptada','rechazada','vencida') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cotizaciones_numero_unique` (`numero`),
  KEY `cotizaciones_bodega_id_foreign` (`bodega_id`),
  KEY `cotizaciones_usuario_id_foreign` (`usuario_id`),
  KEY `cotizaciones_cliente_idx` (`cliente_id`),
  KEY `cotizaciones_estado_idx` (`estado`),
  KEY `cotizaciones_fecha_idx` (`fecha`),
  CONSTRAINT `cotizaciones_bodega_id_foreign` FOREIGN KEY (`bodega_id`) REFERENCES `bodegas` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `cotizaciones_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `cotizaciones_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cotizaciones`
--

LOCK TABLES `cotizaciones` WRITE;
/*!40000 ALTER TABLE `cotizaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `cotizaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departamentos`
--

DROP TABLE IF EXISTS `departamentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `departamentos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `departamentos_nombre_unique` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departamentos`
--

LOCK TABLES `departamentos` WRITE;
/*!40000 ALTER TABLE `departamentos` DISABLE KEYS */;
INSERT INTO `departamentos` VALUES (5,'ANTIOQUIA',NULL,NULL),(8,'ATLÁNTICO',NULL,NULL),(11,'BOGOTÁ, D.C.',NULL,NULL),(13,'BOLÍVAR',NULL,NULL),(15,'BOYACÁ',NULL,NULL),(17,'CALDAS',NULL,NULL),(18,'CAQUETÁ',NULL,NULL),(19,'CAUCA',NULL,NULL),(20,'CESAR',NULL,NULL),(23,'CÓRDOBA',NULL,NULL),(25,'CUNDINAMARCA',NULL,NULL),(27,'CHOCÓ',NULL,NULL),(41,'HUILA',NULL,NULL),(44,'LA GUAJIRA',NULL,NULL),(47,'MAGDALENA',NULL,NULL),(50,'META',NULL,NULL),(52,'NARIÑO',NULL,NULL),(54,'NORTE DE SANTANDER',NULL,NULL),(63,'QUINDÍO',NULL,NULL),(66,'RISARALDA',NULL,NULL),(68,'SANTANDER',NULL,NULL),(70,'SUCRE',NULL,NULL),(73,'TOLIMA',NULL,NULL),(76,'VALLE DEL CAUCA',NULL,NULL),(81,'ARAUCA',NULL,NULL),(85,'CASANARE',NULL,NULL),(86,'PUTUMAYO',NULL,NULL),(88,'ARCHIPIÉLAGO DE SAN ANDRÉS, PROVIDENCIA Y SANTA CATALINA',NULL,NULL),(91,'AMAZONAS',NULL,NULL),(94,'GUAINÍA',NULL,NULL),(95,'GUAVIARE',NULL,NULL),(97,'VAUPÉS',NULL,NULL),(99,'VICHADA',NULL,NULL);
/*!40000 ALTER TABLE `departamentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_ajustes_inventario`
--

DROP TABLE IF EXISTS `detalle_ajustes_inventario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_ajustes_inventario` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ajuste_inventario_id` bigint unsigned NOT NULL,
  `producto_id` bigint unsigned NOT NULL,
  `stock_sistema` decimal(15,3) NOT NULL DEFAULT '0.000',
  `stock_fisico` decimal(15,3) NOT NULL DEFAULT '0.000',
  `diferencia` decimal(15,3) NOT NULL DEFAULT '0.000',
  `costo_unitario` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `detalle_ajustes_inventario_ajuste_inventario_id_index` (`ajuste_inventario_id`),
  KEY `detalle_ajustes_inventario_producto_id_index` (`producto_id`),
  CONSTRAINT `detalle_ajustes_inventario_ajuste_inventario_id_foreign` FOREIGN KEY (`ajuste_inventario_id`) REFERENCES `ajustes_inventario` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detalle_ajustes_inventario_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_ajustes_inventario`
--

LOCK TABLES `detalle_ajustes_inventario` WRITE;
/*!40000 ALTER TABLE `detalle_ajustes_inventario` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalle_ajustes_inventario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_compras`
--

DROP TABLE IF EXISTS `detalle_compras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_compras` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `compra_id` bigint unsigned NOT NULL,
  `producto_id` bigint unsigned NOT NULL,
  `lote` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `serial` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cantidad` decimal(10,3) NOT NULL,
  `precio_unitario` decimal(15,2) NOT NULL,
  `descuento_unitario` decimal(15,2) NOT NULL DEFAULT '0.00',
  `impuesto_id` bigint unsigned DEFAULT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `detalle_compras_impuesto_id_foreign` (`impuesto_id`),
  KEY `det_compra_idx` (`compra_id`),
  KEY `det_compra_producto_idx` (`producto_id`),
  CONSTRAINT `detalle_compras_compra_id_foreign` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detalle_compras_impuesto_id_foreign` FOREIGN KEY (`impuesto_id`) REFERENCES `impuestos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `detalle_compras_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_compras`
--

LOCK TABLES `detalle_compras` WRITE;
/*!40000 ALTER TABLE `detalle_compras` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalle_compras` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_conteos_fisicos`
--

DROP TABLE IF EXISTS `detalle_conteos_fisicos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_conteos_fisicos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `conteo_fisico_id` bigint unsigned NOT NULL,
  `producto_id` bigint unsigned NOT NULL,
  `stock_sistema` decimal(15,3) NOT NULL DEFAULT '0.000',
  `cantidad_contada` decimal(15,3) DEFAULT NULL,
  `diferencia` decimal(15,3) NOT NULL DEFAULT '0.000',
  `ajuste_inventario_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `detalle_conteos_fisicos_ajuste_inventario_id_foreign` (`ajuste_inventario_id`),
  KEY `detalle_conteos_fisicos_conteo_fisico_id_index` (`conteo_fisico_id`),
  KEY `detalle_conteos_fisicos_producto_id_index` (`producto_id`),
  CONSTRAINT `detalle_conteos_fisicos_ajuste_inventario_id_foreign` FOREIGN KEY (`ajuste_inventario_id`) REFERENCES `ajustes_inventario` (`id`) ON DELETE SET NULL,
  CONSTRAINT `detalle_conteos_fisicos_conteo_fisico_id_foreign` FOREIGN KEY (`conteo_fisico_id`) REFERENCES `conteos_fisicos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detalle_conteos_fisicos_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_conteos_fisicos`
--

LOCK TABLES `detalle_conteos_fisicos` WRITE;
/*!40000 ALTER TABLE `detalle_conteos_fisicos` DISABLE KEYS */;
INSERT INTO `detalle_conteos_fisicos` VALUES (1,1,1,0.000,20.000,20.000,NULL,'2026-06-17 07:58:49','2026-06-17 07:58:53'),(2,1,2,0.000,20.000,20.000,NULL,'2026-06-19 08:14:53','2026-06-19 08:18:17'),(3,1,3,0.000,20.000,20.000,NULL,'2026-06-19 08:15:28','2026-06-19 08:18:18'),(4,1,4,0.000,20.000,20.000,NULL,'2026-06-19 08:16:03','2026-06-19 08:18:19'),(5,1,5,0.000,20.000,20.000,NULL,'2026-06-19 08:18:10','2026-06-19 08:18:22'),(6,1,6,0.000,20.000,20.000,NULL,'2026-06-19 08:18:51','2026-06-19 08:18:55'),(7,1,7,0.000,20.000,20.000,NULL,'2026-06-19 08:19:25','2026-06-19 08:19:58'),(8,1,8,0.000,20.000,20.000,NULL,'2026-06-19 08:19:52','2026-06-19 08:20:00'),(9,1,9,0.000,20.000,20.000,NULL,'2026-06-19 08:20:26','2026-06-19 08:20:31'),(10,1,10,0.000,20.000,20.000,NULL,'2026-06-19 08:57:47','2026-06-19 08:59:47'),(11,1,11,0.000,20.000,20.000,NULL,'2026-06-19 08:58:21','2026-06-19 08:59:48'),(12,1,12,0.000,20.000,20.000,NULL,'2026-06-19 08:58:48','2026-06-19 08:59:49'),(13,1,13,0.000,20.000,20.000,NULL,'2026-06-19 08:59:34','2026-06-19 08:59:51'),(14,1,14,0.000,20.000,20.000,NULL,'2026-06-19 09:00:10','2026-06-19 09:00:15'),(15,1,15,0.000,20.000,20.000,NULL,'2026-06-19 09:00:41','2026-06-19 09:00:46'),(16,1,16,0.000,20.000,20.000,NULL,'2026-06-19 09:01:15','2026-06-19 09:02:13'),(17,1,17,0.000,20.000,20.000,NULL,'2026-06-19 09:02:04','2026-06-19 09:02:15'),(18,1,18,0.000,20.000,20.000,NULL,'2026-06-19 09:02:38','2026-06-19 09:02:43'),(19,1,19,0.000,10.000,10.000,NULL,'2026-06-19 09:03:50','2026-06-19 09:03:56'),(20,1,20,0.000,10.000,10.000,NULL,'2026-06-19 09:04:24','2026-06-19 09:04:36'),(21,1,21,0.000,10.000,10.000,NULL,'2026-06-19 09:05:02','2026-06-19 09:05:12'),(22,1,22,0.000,10.000,10.000,NULL,'2026-06-19 09:05:32','2026-06-19 09:05:58'),(23,1,23,0.000,10.000,10.000,NULL,'2026-06-19 09:06:42','2026-06-19 09:07:20'),(24,1,24,0.000,10.000,10.000,NULL,'2026-06-19 09:07:15','2026-06-19 09:07:23');
/*!40000 ALTER TABLE `detalle_conteos_fisicos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_cotizaciones`
--

DROP TABLE IF EXISTS `detalle_cotizaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_cotizaciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cotizacion_id` bigint unsigned NOT NULL,
  `producto_id` bigint unsigned NOT NULL,
  `lote` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `cantidad` decimal(10,3) NOT NULL,
  `precio_unitario` decimal(15,2) NOT NULL,
  `descuento_unitario` decimal(15,2) NOT NULL DEFAULT '0.00',
  `impuesto_id` bigint unsigned DEFAULT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `detalle_cotizaciones_impuesto_id_foreign` (`impuesto_id`),
  KEY `det_cotizacion_idx` (`cotizacion_id`),
  KEY `det_cotizacion_producto_idx` (`producto_id`),
  CONSTRAINT `detalle_cotizaciones_cotizacion_id_foreign` FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizaciones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detalle_cotizaciones_impuesto_id_foreign` FOREIGN KEY (`impuesto_id`) REFERENCES `impuestos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `detalle_cotizaciones_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_cotizaciones`
--

LOCK TABLES `detalle_cotizaciones` WRITE;
/*!40000 ALTER TABLE `detalle_cotizaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalle_cotizaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_notas`
--

DROP TABLE IF EXISTS `detalle_notas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_notas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nota_id` bigint unsigned NOT NULL,
  `producto_id` bigint unsigned NOT NULL,
  `cantidad` decimal(10,3) NOT NULL,
  `precio_unitario` decimal(15,2) NOT NULL,
  `impuestos` decimal(15,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `detalle_notas_nota_id_foreign` (`nota_id`),
  KEY `detalle_notas_producto_id_foreign` (`producto_id`),
  CONSTRAINT `detalle_notas_nota_id_foreign` FOREIGN KEY (`nota_id`) REFERENCES `notas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detalle_notas_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_notas`
--

LOCK TABLES `detalle_notas` WRITE;
/*!40000 ALTER TABLE `detalle_notas` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalle_notas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_pago_clientes`
--

DROP TABLE IF EXISTS `detalle_pago_clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_pago_clientes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pago_cliente_id` bigint unsigned NOT NULL,
  `documento_tipo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'venta o remision',
  `documento_id` bigint unsigned NOT NULL,
  `monto_aplicado` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `detpagocli_pago_idx` (`pago_cliente_id`),
  KEY `detpagocli_doc_idx` (`documento_tipo`,`documento_id`),
  CONSTRAINT `detalle_pago_clientes_pago_cliente_id_foreign` FOREIGN KEY (`pago_cliente_id`) REFERENCES `pago_clientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_pago_clientes`
--

LOCK TABLES `detalle_pago_clientes` WRITE;
/*!40000 ALTER TABLE `detalle_pago_clientes` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalle_pago_clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_pago_proveedores`
--

DROP TABLE IF EXISTS `detalle_pago_proveedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_pago_proveedores` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pago_proveedor_id` bigint unsigned NOT NULL,
  `compra_id` bigint unsigned NOT NULL,
  `monto_aplicado` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `detpagopro_pago_idx` (`pago_proveedor_id`),
  KEY `detpagopro_compra_idx` (`compra_id`),
  CONSTRAINT `detalle_pago_proveedores_compra_id_foreign` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `detalle_pago_proveedores_pago_proveedor_id_foreign` FOREIGN KEY (`pago_proveedor_id`) REFERENCES `pago_proveedores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_pago_proveedores`
--

LOCK TABLES `detalle_pago_proveedores` WRITE;
/*!40000 ALTER TABLE `detalle_pago_proveedores` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalle_pago_proveedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_remisiones`
--

DROP TABLE IF EXISTS `detalle_remisiones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_remisiones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `remision_id` bigint unsigned NOT NULL,
  `producto_id` bigint unsigned NOT NULL,
  `lote` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `serial` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cantidad` decimal(10,3) NOT NULL,
  `precio_unitario` decimal(15,2) NOT NULL,
  `descuento_unitario` decimal(15,2) NOT NULL DEFAULT '0.00',
  `costo_unitario` decimal(15,4) DEFAULT NULL,
  `impuesto_id` bigint unsigned DEFAULT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `detalle_remisiones_impuesto_id_foreign` (`impuesto_id`),
  KEY `det_remision_idx` (`remision_id`),
  KEY `det_remision_producto_idx` (`producto_id`),
  CONSTRAINT `detalle_remisiones_impuesto_id_foreign` FOREIGN KEY (`impuesto_id`) REFERENCES `impuestos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `detalle_remisiones_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `detalle_remisiones_remision_id_foreign` FOREIGN KEY (`remision_id`) REFERENCES `remisiones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_remisiones`
--

LOCK TABLES `detalle_remisiones` WRITE;
/*!40000 ALTER TABLE `detalle_remisiones` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalle_remisiones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_ventas`
--

DROP TABLE IF EXISTS `detalle_ventas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_ventas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `venta_id` bigint unsigned NOT NULL,
  `producto_id` bigint unsigned NOT NULL,
  `lote` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `serial` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cantidad` decimal(10,3) NOT NULL,
  `precio_unitario` decimal(15,2) NOT NULL,
  `descuento_unitario` decimal(15,2) NOT NULL DEFAULT '0.00',
  `impuesto_id` bigint unsigned DEFAULT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `costo_unitario` decimal(15,4) DEFAULT NULL COMMENT 'Costo promedio del producto al momento de la venta (para cálculo de utilidades)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `detalle_ventas_impuesto_id_foreign` (`impuesto_id`),
  KEY `det_venta_idx` (`venta_id`),
  KEY `det_venta_producto_idx` (`producto_id`),
  CONSTRAINT `detalle_ventas_impuesto_id_foreign` FOREIGN KEY (`impuesto_id`) REFERENCES `impuestos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `detalle_ventas_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `detalle_ventas_venta_id_foreign` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_ventas`
--

LOCK TABLES `detalle_ventas` WRITE;
/*!40000 ALTER TABLE `detalle_ventas` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalle_ventas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalles_devoluciones`
--

DROP TABLE IF EXISTS `detalles_devoluciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalles_devoluciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `devolucion_id` bigint unsigned NOT NULL,
  `producto_id` bigint unsigned NOT NULL,
  `cantidad` int unsigned NOT NULL COMMENT 'Cantidad devuelta',
  `precio_unitario` decimal(15,2) NOT NULL COMMENT 'Precio unitario en el documento original',
  `subtotal` decimal(15,2) NOT NULL,
  `defectuoso` tinyint(1) NOT NULL DEFAULT '0' COMMENT '¿Producto defectuoso? (para garantía)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `detalles_devoluciones_devolucion_id_index` (`devolucion_id`),
  KEY `detalles_devoluciones_producto_id_index` (`producto_id`),
  CONSTRAINT `detalles_devoluciones_devolucion_id_foreign` FOREIGN KEY (`devolucion_id`) REFERENCES `devoluciones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detalles_devoluciones_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalles_devoluciones`
--

LOCK TABLES `detalles_devoluciones` WRITE;
/*!40000 ALTER TABLE `detalles_devoluciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalles_devoluciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalles_devoluciones_compras`
--

DROP TABLE IF EXISTS `detalles_devoluciones_compras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalles_devoluciones_compras` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `devolucion_compra_id` bigint unsigned NOT NULL,
  `producto_id` bigint unsigned NOT NULL,
  `cantidad` decimal(15,3) NOT NULL DEFAULT '1.000',
  `precio_unitario` decimal(15,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `costo_unitario` decimal(15,4) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `detalles_devoluciones_compras_devolucion_compra_id_foreign` (`devolucion_compra_id`),
  KEY `detalles_devoluciones_compras_producto_id_foreign` (`producto_id`),
  CONSTRAINT `detalles_devoluciones_compras_devolucion_compra_id_foreign` FOREIGN KEY (`devolucion_compra_id`) REFERENCES `devoluciones_compras` (`id`),
  CONSTRAINT `detalles_devoluciones_compras_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalles_devoluciones_compras`
--

LOCK TABLES `detalles_devoluciones_compras` WRITE;
/*!40000 ALTER TABLE `detalles_devoluciones_compras` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalles_devoluciones_compras` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `devoluciones`
--

DROP TABLE IF EXISTS `devoluciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `devoluciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_documento` enum('remision','venta') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'De qué tipo de documento se devuelve',
  `documento_id` bigint unsigned NOT NULL COMMENT 'remisiones.id o ventas.id',
  `cliente_id` bigint unsigned NOT NULL,
  `estado` enum('borrador','confirmada','anulada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  `confirmada_en` timestamp NULL DEFAULT NULL,
  `motivo` enum('cambio','defecto','error_pedido','otro') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `descuento` decimal(15,2) NOT NULL DEFAULT '0.00',
  `impuestos` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `usuario_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `devoluciones_numero_unique` (`numero`),
  KEY `devoluciones_usuario_id_foreign` (`usuario_id`),
  KEY `devoluciones_tipo_documento_index` (`tipo_documento`),
  KEY `devoluciones_cliente_id_index` (`cliente_id`),
  KEY `devoluciones_estado_index` (`estado`),
  KEY `devoluciones_tipo_documento_documento_id_index` (`tipo_documento`,`documento_id`),
  CONSTRAINT `devoluciones_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `devoluciones_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `devoluciones`
--

LOCK TABLES `devoluciones` WRITE;
/*!40000 ALTER TABLE `devoluciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `devoluciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `devoluciones_compras`
--

DROP TABLE IF EXISTS `devoluciones_compras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `devoluciones_compras` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `compra_id` bigint unsigned NOT NULL,
  `proveedor_id` bigint unsigned NOT NULL,
  `bodega_id` bigint unsigned NOT NULL,
  `estado` enum('borrador','confirmada','anulada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  `motivo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha` date NOT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `descuento` decimal(15,2) NOT NULL DEFAULT '0.00',
  `impuestos` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `confirmada_en` datetime DEFAULT NULL,
  `anulada_en` datetime DEFAULT NULL,
  `usuario_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `devoluciones_compras_numero_unique` (`numero`),
  KEY `devoluciones_compras_compra_id_foreign` (`compra_id`),
  KEY `devoluciones_compras_proveedor_id_foreign` (`proveedor_id`),
  KEY `devoluciones_compras_bodega_id_foreign` (`bodega_id`),
  KEY `devoluciones_compras_usuario_id_foreign` (`usuario_id`),
  CONSTRAINT `devoluciones_compras_bodega_id_foreign` FOREIGN KEY (`bodega_id`) REFERENCES `bodegas` (`id`),
  CONSTRAINT `devoluciones_compras_compra_id_foreign` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`),
  CONSTRAINT `devoluciones_compras_proveedor_id_foreign` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`),
  CONSTRAINT `devoluciones_compras_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `devoluciones_compras`
--

LOCK TABLES `devoluciones_compras` WRITE;
/*!40000 ALTER TABLE `devoluciones_compras` DISABLE KEYS */;
/*!40000 ALTER TABLE `devoluciones_compras` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empresa`
--

DROP TABLE IF EXISTS `empresa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empresa` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `razon_social` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_comercial` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nit` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `digito_verificacion` tinyint unsigned NOT NULL,
  `tipo_persona` enum('natural','juridica') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'juridica',
  `regimen_tributario` enum('simplificado','comun','gran_contribuyente') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'comun',
  `responsable_iva` tinyint(1) NOT NULL DEFAULT '1',
  `usa_seriales` tinyint(1) NOT NULL DEFAULT '0',
  `una_sola_bodega` tinyint(1) NOT NULL DEFAULT '0',
  `actividad_ciiu` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `departamento_id` bigint unsigned DEFAULT NULL,
  `ciudad_id` bigint unsigned DEFAULT NULL,
  `pais` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Colombia',
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `celular` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_documentos` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sitio_web` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resolucion_dian` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `resolucion_fecha_expedicion` date DEFAULT NULL,
  `resolucion_fecha_vencimiento` date DEFAULT NULL,
  `resolucion_desde` int unsigned DEFAULT NULL,
  `resolucion_hasta` int unsigned DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_pos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_impresion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pie_pagina` text COLLATE utf8mb4_unicode_ci,
  `notas_factura` text COLLATE utf8mb4_unicode_ci,
  `margen_ganancia_default` decimal(5,2) NOT NULL DEFAULT '30.00' COMMENT 'Margen de ganancia por defecto para precios de venta',
  `margen_ganancia_minimo` decimal(5,2) NOT NULL DEFAULT '10.00' COMMENT 'Margen de ganancia mínimo permitido',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `empresa_departamento_id_foreign` (`departamento_id`),
  KEY `empresa_ciudad_id_foreign` (`ciudad_id`),
  CONSTRAINT `empresa_ciudad_id_foreign` FOREIGN KEY (`ciudad_id`) REFERENCES `ciudades` (`id`) ON DELETE SET NULL,
  CONSTRAINT `empresa_departamento_id_foreign` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empresa`
--

LOCK TABLES `empresa` WRITE;
/*!40000 ALTER TABLE `empresa` DISABLE KEYS */;
INSERT INTO `empresa` VALUES (1,'Tienda Siga Inv','Tienda Siga Inv','88198588',9,'natural','simplificado',0,0,1,'45050','Urb Hibiscos',54,885,'Colombia','3025987676','3025987676','joseforozco@gmail.com','joseforozco@gmail.com',NULL,NULL,NULL,NULL,NULL,NULL,'empresa/01KV9R651NCNH3S0ER814EJNVB.png','empresa/01KV9R651VCX3GTX615X8SH4Y4.png','empresa/01KV9R651S6K7R163TT4M63K6A.png',NULL,NULL,30.00,25.00,'2026-06-17 07:57:23','2026-06-17 07:57:23');
/*!40000 ALTER TABLE `empresa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exports`
--

DROP TABLE IF EXISTS `exports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `exports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `completed_at` timestamp NULL DEFAULT NULL,
  `file_disk` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exporter` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `processed_rows` int unsigned NOT NULL DEFAULT '0',
  `total_rows` int unsigned NOT NULL,
  `successful_rows` int unsigned NOT NULL DEFAULT '0',
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `exports_user_id_foreign` (`user_id`),
  CONSTRAINT `exports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exports`
--

LOCK TABLES `exports` WRITE;
/*!40000 ALTER TABLE `exports` DISABLE KEYS */;
/*!40000 ALTER TABLE `exports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_import_rows`
--

DROP TABLE IF EXISTS `failed_import_rows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_import_rows` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `data` json NOT NULL,
  `import_id` bigint unsigned NOT NULL,
  `validation_error` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `failed_import_rows_import_id_foreign` (`import_id`),
  CONSTRAINT `failed_import_rows_import_id_foreign` FOREIGN KEY (`import_id`) REFERENCES `imports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_import_rows`
--

LOCK TABLES `failed_import_rows` WRITE;
/*!40000 ALTER TABLE `failed_import_rows` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_import_rows` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `formas_pago`
--

DROP TABLE IF EXISTS `formas_pago`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `formas_pago` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `requiere_banco` tinyint(1) NOT NULL DEFAULT '0',
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `formas_pago_nombre_unique` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `formas_pago`
--

LOCK TABLES `formas_pago` WRITE;
/*!40000 ALTER TABLE `formas_pago` DISABLE KEYS */;
INSERT INTO `formas_pago` VALUES (1,'Efectivo',0,'Pago en efectivo',1,'2026-06-17 06:01:48','2026-06-17 06:01:48'),(2,'Transferencia',1,'Transferencia bancaria o ACH',1,'2026-06-17 06:01:48','2026-06-17 06:01:48'),(3,'Cheque',1,'Cheque bancario',1,'2026-06-17 06:01:48','2026-06-17 06:01:48'),(4,'Tarjeta Débito',1,'Pago con tarjeta débito',1,'2026-06-17 06:01:48','2026-06-17 06:01:48'),(5,'Tarjeta Crédito',0,'Pago con tarjeta crédito',0,'2026-06-17 06:01:48','2026-06-17 06:01:48'),(6,'Nequi',1,'Pago por Nequi',1,'2026-06-17 06:01:48','2026-06-17 06:01:48'),(7,'Daviplata',1,'Pago por Daviplata',1,'2026-06-17 06:01:48','2026-06-17 06:01:48'),(8,'PSE',1,'Pago en línea PSE',1,'2026-06-17 06:01:48','2026-06-17 06:01:48'),(9,'Crédito Directo',0,'Crédito otorgado directamente por la empresa',0,'2026-06-17 06:01:48','2026-06-17 06:01:48'),(10,'Compensación',0,'Cruce de cuentas o compensación de cartera',0,'2026-06-17 06:01:48','2026-06-17 06:01:48');
/*!40000 ALTER TABLE `formas_pago` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `formula_transformacion_detalles`
--

DROP TABLE IF EXISTS `formula_transformacion_detalles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `formula_transformacion_detalles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `formula_transformacion_id` bigint unsigned NOT NULL,
  `tipo_linea` enum('insumo','producto') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'insumo',
  `producto_id` bigint unsigned NOT NULL,
  `cantidad` decimal(10,3) NOT NULL,
  `lote` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `costo_unitario` decimal(15,4) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ftdetalles_producto_fk` (`producto_id`),
  KEY `ftdetalles_tipo_idx` (`formula_transformacion_id`,`tipo_linea`),
  CONSTRAINT `ftdetalles_formula_fk` FOREIGN KEY (`formula_transformacion_id`) REFERENCES `formula_transformaciones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ftdetalles_producto_fk` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `formula_transformacion_detalles`
--

LOCK TABLES `formula_transformacion_detalles` WRITE;
/*!40000 ALTER TABLE `formula_transformacion_detalles` DISABLE KEYS */;
/*!40000 ALTER TABLE `formula_transformacion_detalles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `formula_transformaciones`
--

DROP TABLE IF EXISTS `formula_transformaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `formula_transformaciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `tipo` enum('combo','promo','reenvase','fabricacion') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fabricacion',
  `producto_final_nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `producto_final_id` bigint unsigned DEFAULT NULL,
  `cantidad_producto_final` decimal(10,3) NOT NULL DEFAULT '1.000',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `usuario_id` bigint unsigned DEFAULT NULL,
  `bloqueada` tinyint(1) NOT NULL DEFAULT '0',
  `tiene_transformaciones` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `formula_transformaciones_producto_final_nombre_unique` (`producto_final_nombre`),
  KEY `formula_transformaciones_producto_final_id_foreign` (`producto_final_id`),
  KEY `formula_transformaciones_usuario_id_foreign` (`usuario_id`),
  KEY `formula_transformaciones_activo_idx` (`activo`),
  KEY `formula_transformaciones_tipo_idx` (`tipo`),
  CONSTRAINT `formula_transformaciones_producto_final_id_foreign` FOREIGN KEY (`producto_final_id`) REFERENCES `productos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `formula_transformaciones_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `formula_transformaciones`
--

LOCK TABLES `formula_transformaciones` WRITE;
/*!40000 ALTER TABLE `formula_transformaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `formula_transformaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `historico_precios`
--

DROP TABLE IF EXISTS `historico_precios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `historico_precios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `producto_id` bigint unsigned NOT NULL,
  `proveedor_id` bigint unsigned DEFAULT NULL,
  `precio_compra` decimal(15,2) NOT NULL,
  `usuario_id` bigint unsigned DEFAULT NULL,
  `fecha_cambio` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `histprecio_producto_proveedor_unique` (`producto_id`,`proveedor_id`),
  KEY `historico_precios_proveedor_id_foreign` (`proveedor_id`),
  KEY `historico_precios_usuario_id_foreign` (`usuario_id`),
  KEY `histprecio_fecha_idx` (`fecha_cambio`),
  CONSTRAINT `historico_precios_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `historico_precios_proveedor_id_foreign` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE CASCADE,
  CONSTRAINT `historico_precios_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `historico_precios`
--

LOCK TABLES `historico_precios` WRITE;
/*!40000 ALTER TABLE `historico_precios` DISABLE KEYS */;
/*!40000 ALTER TABLE `historico_precios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `imports`
--

DROP TABLE IF EXISTS `imports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `imports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `completed_at` timestamp NULL DEFAULT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `importer` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `processed_rows` int unsigned NOT NULL DEFAULT '0',
  `total_rows` int unsigned NOT NULL,
  `successful_rows` int unsigned NOT NULL DEFAULT '0',
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `imports_user_id_foreign` (`user_id`),
  CONSTRAINT `imports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `imports`
--

LOCK TABLES `imports` WRITE;
/*!40000 ALTER TABLE `imports` DISABLE KEYS */;
/*!40000 ALTER TABLE `imports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `impuestos`
--

DROP TABLE IF EXISTS `impuestos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `impuestos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('IVA','INC','ICO','ReteIVA','ReteICA','ReteRenta','Otro') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'IVA',
  `porcentaje` decimal(5,2) NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `impuestos`
--

LOCK TABLES `impuestos` WRITE;
/*!40000 ALTER TABLE `impuestos` DISABLE KEYS */;
INSERT INTO `impuestos` VALUES (1,'IVA 0%','IVA',0.00,'IVA tarifa 0% — bienes exentos y excluidos',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(2,'IVA 5%','IVA',5.00,'IVA tarifa diferencial 5%',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(3,'IVA 19%','IVA',19.00,'IVA tarifa general 19%',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(4,'INC 4%','INC',4.00,'Impuesto Nacional al Consumo 4% — telefonía y datos',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(5,'INC 8%','INC',8.00,'Impuesto Nacional al Consumo 8% — restaurantes',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(6,'INC 16%','INC',16.00,'Impuesto Nacional al Consumo 16% — vehículos',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(7,'ReteRenta 3.5%','ReteRenta',3.50,'Retención en la fuente 3.5% — servicios en general',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(8,'ReteRenta 4%','ReteRenta',4.00,'Retención en la fuente 4% — arrendamientos',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(9,'ReteRenta 11%','ReteRenta',11.00,'Retención en la fuente 11% — honorarios y comisiones',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(10,'ReteIVA 15%','ReteIVA',15.00,'Retención de IVA 15% — aplica sobre el valor del IVA',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(11,'ReteICA 0.414%','ReteICA',0.41,'Retención ICA Cúcuta — comercio (verificar tarifa vigente)',1,'2026-06-17 06:01:47','2026-06-17 06:01:47');
/*!40000 ALTER TABLE `impuestos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `marcas`
--

DROP TABLE IF EXISTS `marcas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `marcas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `marcas_nombre_unique` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marcas`
--

LOCK TABLES `marcas` WRITE;
/*!40000 ALTER TABLE `marcas` DISABLE KEYS */;
INSERT INTO `marcas` VALUES (1,'GENERICO',NULL,1,'2026-06-17 07:58:40','2026-06-17 07:58:40'),(2,'ADATA',NULL,1,'2026-06-19 08:14:38','2026-06-19 08:14:38'),(3,'PATRIOT',NULL,1,'2026-06-19 08:15:15','2026-06-19 08:15:15'),(4,'KINGSTON',NULL,1,'2026-06-19 08:15:59','2026-06-19 08:15:59'),(5,'CORSAIR',NULL,1,'2026-06-19 08:17:58','2026-06-19 08:17:58'),(6,'SAMSUNG',NULL,1,'2026-06-19 08:59:18','2026-06-19 08:59:18'),(7,'CRUCIAL',NULL,1,'2026-06-19 09:01:52','2026-06-19 09:01:52');
/*!40000 ALTER TABLE `marcas` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'0001_01_01_000003_create_permission_tables',1),(5,'0001_01_01_000004_create_departamentos_table',1),(6,'0001_01_01_000005_create_ciudades_table',1),(7,'0001_01_01_000006_create_clientes_table',1),(8,'0001_01_01_000007_create_proveedores_table',1),(9,'0001_01_01_000008_create_empresa_table',1),(10,'2025_05_25_041509_create_auditorias_table',1),(11,'2026_02_24_010100_create_unidades_medida_table',1),(12,'2026_02_24_010200_create_categorias_table',1),(13,'2026_02_24_010300_create_marcas_table',1),(14,'2026_02_24_010400_create_impuestos_table',1),(15,'2026_02_24_010500_create_formas_pago_table',1),(16,'2026_02_24_010600_create_numeraciones_table',1),(17,'2026_02_24_020100_create_bancos_table',1),(18,'2026_02_24_020200_create_bodegas_table',1),(19,'2026_02_24_030100_create_productos_table',1),(20,'2026_02_24_030200_create_stock_bodegas_table',1),(21,'2026_02_24_030201_create_stock_bodega_lotes_table',1),(22,'2026_02_24_030202_create_stock_bodega_serials_table',1),(23,'2026_02_24_030300_create_movimientos_inventario_table',1),(24,'2026_02_24_040100_create_cotizaciones_table',1),(25,'2026_02_24_040200_create_detalle_cotizaciones_table',1),(26,'2026_02_24_040300_create_remisiones_table',1),(27,'2026_02_24_040400_create_detalle_remisiones_table',1),(28,'2026_02_24_040500_create_ventas_table',1),(29,'2026_02_24_040600_create_detalle_ventas_table',1),(30,'2026_02_24_040700_create_compras_table',1),(31,'2026_02_24_040800_create_detalle_compras_table',1),(32,'2026_02_24_040900_create_cajas_table',1),(33,'2026_02_24_041000_create_movimientos_cajas_table',1),(34,'2026_02_24_050100_create_pago_clientes_table',1),(35,'2026_02_24_050200_create_pago_proveedores_table',1),(36,'2026_02_24_050300_create_movimientos_bancos_table',1),(37,'2026_02_24_060100_create_historico_precios_table',1),(38,'2026_02_28_500000_create_formula_transformaciones_table',1),(39,'2026_03_01_000002_create_auditoria_documentos_table',1),(40,'2026_03_02_000000_create_transformaciones_tables',1),(41,'2026_03_04_000001_create_traslados_tables',1),(42,'2026_03_07_100000_create_notifications_table',1),(43,'2026_03_11_000001_create_devoluciones_table',1),(44,'2026_03_11_000002_create_detalles_devoluciones_table',1),(45,'2026_03_11_000003_create_movimientos_saldo_cliente_table',1),(46,'2026_03_16_000001_create_ajustes_inventario_table',1),(47,'2026_03_16_000002_create_detalle_ajustes_inventario_table',1),(48,'2026_03_16_000003_create_conteos_fisicos_table',1),(49,'2026_03_16_000004_create_detalle_conteos_fisicos_table',1),(50,'2026_04_11_042705_create_personal_access_tokens_table',1),(51,'2026_05_04_005733_create_producto_proveedor_codigos_table',1),(52,'2026_05_04_010431_create_notas_tables',1),(53,'2026_05_10_021951_create_imports_table',1),(54,'2026_05_10_021952_create_exports_table',1),(55,'2026_05_10_021953_create_failed_import_rows_table',1),(56,'2026_05_13_182218_create_devolucion_compras_table',1),(57,'2026_05_20_000002_drop_logo_email_url_from_empresa',1),(58,'2026_06_02_173842_create_turnos_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',1),(2,'App\\Models\\User',2),(3,'App\\Models\\User',3),(4,'App\\Models\\User',4);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movimientos_bancos`
--

DROP TABLE IF EXISTS `movimientos_bancos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `movimientos_bancos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `banco_id` bigint unsigned NOT NULL,
  `usuario_id` bigint unsigned DEFAULT NULL,
  `forma_pago_id` bigint unsigned DEFAULT NULL,
  `fecha_movimiento` datetime NOT NULL,
  `tipo` enum('deposito','retiro','transferencia') COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto` decimal(15,2) NOT NULL,
  `saldo_actual` decimal(15,2) NOT NULL COMMENT 'Saldo después del movimiento',
  `referencia` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `concepto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `traslado_destino_tipo` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `traslado_destino_id` bigint unsigned DEFAULT NULL,
  `documento_tipo` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `documento_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `movimientos_bancos_usuario_id_foreign` (`usuario_id`),
  KEY `movimientos_bancos_forma_pago_id_foreign` (`forma_pago_id`),
  KEY `movbanco_banco_fecha_idx` (`banco_id`,`fecha_movimiento`),
  KEY `movbanco_tipo_idx` (`tipo`),
  KEY `movbanco_documento_idx` (`documento_tipo`,`documento_id`),
  CONSTRAINT `movimientos_bancos_banco_id_foreign` FOREIGN KEY (`banco_id`) REFERENCES `bancos` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `movimientos_bancos_forma_pago_id_foreign` FOREIGN KEY (`forma_pago_id`) REFERENCES `formas_pago` (`id`) ON DELETE SET NULL,
  CONSTRAINT `movimientos_bancos_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimientos_bancos`
--

LOCK TABLES `movimientos_bancos` WRITE;
/*!40000 ALTER TABLE `movimientos_bancos` DISABLE KEYS */;
/*!40000 ALTER TABLE `movimientos_bancos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movimientos_cajas`
--

DROP TABLE IF EXISTS `movimientos_cajas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `movimientos_cajas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `caja_id` bigint unsigned NOT NULL,
  `usuario_id` bigint unsigned DEFAULT NULL,
  `forma_pago_id` bigint unsigned DEFAULT NULL,
  `fecha_movimiento` datetime NOT NULL,
  `tipo` enum('ingreso','egreso','traslado') COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto` decimal(15,2) NOT NULL,
  `saldo_actual` decimal(15,2) NOT NULL COMMENT 'Saldo después del movimiento',
  `categoria` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'gasto_operativo, ingreso_operativo, etc.',
  `referencia` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `concepto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `traslado_destino_tipo` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `traslado_destino_id` bigint unsigned DEFAULT NULL,
  `documento_tipo` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `documento_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `movimientos_cajas_usuario_id_foreign` (`usuario_id`),
  KEY `movimientos_cajas_forma_pago_id_foreign` (`forma_pago_id`),
  KEY `movcaja_caja_fecha_idx` (`caja_id`,`fecha_movimiento`),
  KEY `movcaja_tipo_idx` (`tipo`),
  KEY `movcaja_documento_idx` (`documento_tipo`,`documento_id`),
  CONSTRAINT `movimientos_cajas_caja_id_foreign` FOREIGN KEY (`caja_id`) REFERENCES `cajas` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `movimientos_cajas_forma_pago_id_foreign` FOREIGN KEY (`forma_pago_id`) REFERENCES `formas_pago` (`id`) ON DELETE SET NULL,
  CONSTRAINT `movimientos_cajas_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimientos_cajas`
--

LOCK TABLES `movimientos_cajas` WRITE;
/*!40000 ALTER TABLE `movimientos_cajas` DISABLE KEYS */;
/*!40000 ALTER TABLE `movimientos_cajas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movimientos_inventario`
--

DROP TABLE IF EXISTS `movimientos_inventario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `movimientos_inventario` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `producto_id` bigint unsigned NOT NULL,
  `bodega_id` bigint unsigned NOT NULL,
  `tipo_movimiento` enum('saldo_inicial','entrada_compra','salida_venta','salida_remision','entrada_devolucion','salida_devolucion','traslado_entrada','traslado_salida','salida_traslado','entrada_traslado','reverso_traslado','ajuste_positivo','ajuste_negativo','ajuste_costo_promedio','ajuste_inicial','ajuste_conteo','reverso_anulacion','facturacion_remision','anulacion_venta_remision','entrada_transformacion','salida_transformacion','reverso_transformacion') COLLATE utf8mb4_unicode_ci NOT NULL,
  `cantidad` decimal(10,3) NOT NULL,
  `costo_unitario` decimal(15,2) NOT NULL DEFAULT '0.00',
  `lote` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `stock_resultante` decimal(10,3) NOT NULL,
  `documento_tipo` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `documento_id` bigint unsigned DEFAULT NULL,
  `detalle_compra_id` bigint unsigned DEFAULT NULL,
  `detalle_venta_id` bigint unsigned DEFAULT NULL,
  `detalle_remision_id` bigint unsigned DEFAULT NULL,
  `observacion` text COLLATE utf8mb4_unicode_ci,
  `usuario_id` bigint unsigned DEFAULT NULL,
  `fecha_movimiento` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `movimientos_inventario_unique_compra_detalle` (`documento_tipo`,`documento_id`,`detalle_compra_id`,`tipo_movimiento`),
  UNIQUE KEY `movimientos_inventario_unique_venta_detalle` (`documento_tipo`,`documento_id`,`detalle_venta_id`,`tipo_movimiento`),
  UNIQUE KEY `movimientos_inventario_unique_remision_detalle` (`documento_tipo`,`documento_id`,`detalle_remision_id`,`tipo_movimiento`),
  KEY `movimientos_inventario_bodega_id_foreign` (`bodega_id`),
  KEY `movimientos_inventario_usuario_id_foreign` (`usuario_id`),
  KEY `mov_producto_bodega_idx` (`producto_id`,`bodega_id`),
  KEY `mov_documento_idx` (`documento_tipo`,`documento_id`),
  KEY `movimientos_inventario_documento_id_index` (`documento_id`),
  KEY `movimientos_inventario_documento_tipo_index` (`documento_tipo`),
  KEY `mov_fecha_idx` (`fecha_movimiento`),
  KEY `mov_tipo_idx` (`tipo_movimiento`),
  CONSTRAINT `movimientos_inventario_bodega_id_foreign` FOREIGN KEY (`bodega_id`) REFERENCES `bodegas` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `movimientos_inventario_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `movimientos_inventario_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimientos_inventario`
--

LOCK TABLES `movimientos_inventario` WRITE;
/*!40000 ALTER TABLE `movimientos_inventario` DISABLE KEYS */;
/*!40000 ALTER TABLE `movimientos_inventario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movimientos_saldo_cliente`
--

DROP TABLE IF EXISTS `movimientos_saldo_cliente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `movimientos_saldo_cliente` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cliente_id` bigint unsigned NOT NULL,
  `tipo` enum('compra','venta','devolucion','pago','ajuste') COLLATE utf8mb4_unicode_ci NOT NULL,
  `referencia` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ej: venta_id, remision_id, devolucion_id',
  `monto` decimal(15,2) NOT NULL COMMENT 'Positivo: aumenta deuda, Negativo: disminuye/crea crédito',
  `saldo_anterior` decimal(15,2) NOT NULL,
  `saldo_nuevo` decimal(15,2) NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `usuario_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `movimientos_saldo_cliente_usuario_id_foreign` (`usuario_id`),
  KEY `movimientos_saldo_cliente_cliente_id_index` (`cliente_id`),
  KEY `movimientos_saldo_cliente_tipo_index` (`tipo`),
  KEY `movimientos_saldo_cliente_referencia_index` (`referencia`),
  KEY `movimientos_saldo_cliente_created_at_index` (`created_at`),
  CONSTRAINT `movimientos_saldo_cliente_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `movimientos_saldo_cliente_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimientos_saldo_cliente`
--

LOCK TABLES `movimientos_saldo_cliente` WRITE;
/*!40000 ALTER TABLE `movimientos_saldo_cliente` DISABLE KEYS */;
/*!40000 ALTER TABLE `movimientos_saldo_cliente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notas`
--

DROP TABLE IF EXISTS `notas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tipo` enum('nota_credito','nota_debito') COLLATE utf8mb4_unicode_ci NOT NULL,
  `numero` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `venta_id` bigint unsigned DEFAULT NULL,
  `cliente_id` bigint unsigned NOT NULL,
  `fecha` timestamp NOT NULL,
  `motivo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `impuestos` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `estado` enum('borrador','confirmada','anulada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  `confirmada_en` timestamp NULL DEFAULT NULL,
  `usuario_id` bigint unsigned NOT NULL,
  `xml_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pdf_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `notas_numero_unique` (`numero`),
  KEY `notas_venta_id_foreign` (`venta_id`),
  KEY `notas_cliente_id_foreign` (`cliente_id`),
  KEY `notas_usuario_id_foreign` (`usuario_id`),
  CONSTRAINT `notas_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notas_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`),
  CONSTRAINT `notas_venta_id_foreign` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notas`
--

LOCK TABLES `notas` WRITE;
/*!40000 ALTER TABLE `notas` DISABLE KEYS */;
/*!40000 ALTER TABLE `notas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `numeraciones`
--

DROP TABLE IF EXISTS `numeraciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `numeraciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tipo_documento` enum('venta','nota_credito','nota_debito','documento_equivalente','remision','cotizacion') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Documentos electrónicos DIAN e internos (Remisión/Cotización)',
  `prefijo` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `resolucion_numero` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Número de resolución DIAN',
  `resolucion_fecha_expedicion` date DEFAULT NULL COMMENT 'Fecha de expedición',
  `resolucion_fecha_vencimiento` date DEFAULT NULL COMMENT 'Fecha de vencimiento',
  `observaciones` text COLLATE utf8mb4_unicode_ci COMMENT 'Notas sobre esta numeración',
  `consecutivo_desde` int unsigned NOT NULL DEFAULT '1',
  `consecutivo_hasta` int unsigned NOT NULL DEFAULT '9999999',
  `consecutivo_actual` int unsigned NOT NULL DEFAULT '0',
  `anno` smallint unsigned NOT NULL,
  `estado` enum('activo','inactivo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numeraciones_tipo_anno_unique` (`tipo_documento`,`anno`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `numeraciones`
--

LOCK TABLES `numeraciones` WRITE;
/*!40000 ALTER TABLE `numeraciones` DISABLE KEYS */;
INSERT INTO `numeraciones` VALUES (1,'remision','RE',NULL,NULL,NULL,'Numeración de remisiones',1,9999999,0,2026,'activo','2026-06-17 06:01:47','2026-06-17 06:01:47'),(2,'cotizacion','COT',NULL,NULL,NULL,'Numeración de cotizaciones',1,9999999,0,2026,'activo','2026-06-17 06:01:47','2026-06-17 06:01:47');
/*!40000 ALTER TABLE `numeraciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pago_clientes`
--

DROP TABLE IF EXISTS `pago_clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pago_clientes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cliente_id` bigint unsigned NOT NULL,
  `forma_pago_id` bigint unsigned NOT NULL,
  `banco_id` bigint unsigned DEFAULT NULL,
  `caja_id` bigint unsigned DEFAULT NULL,
  `usuario_id` bigint unsigned DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `monto` decimal(15,2) NOT NULL,
  `referencia` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pago_clientes_numero_unique` (`numero`),
  KEY `pago_clientes_forma_pago_id_foreign` (`forma_pago_id`),
  KEY `pago_clientes_banco_id_foreign` (`banco_id`),
  KEY `pago_clientes_caja_id_foreign` (`caja_id`),
  KEY `pago_clientes_usuario_id_foreign` (`usuario_id`),
  KEY `pagocli_cliente_idx` (`cliente_id`),
  KEY `pagocli_fecha_idx` (`fecha`),
  CONSTRAINT `pago_clientes_banco_id_foreign` FOREIGN KEY (`banco_id`) REFERENCES `bancos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pago_clientes_caja_id_foreign` FOREIGN KEY (`caja_id`) REFERENCES `cajas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pago_clientes_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `pago_clientes_forma_pago_id_foreign` FOREIGN KEY (`forma_pago_id`) REFERENCES `formas_pago` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `pago_clientes_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pago_clientes`
--

LOCK TABLES `pago_clientes` WRITE;
/*!40000 ALTER TABLE `pago_clientes` DISABLE KEYS */;
/*!40000 ALTER TABLE `pago_clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pago_proveedores`
--

DROP TABLE IF EXISTS `pago_proveedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pago_proveedores` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `proveedor_id` bigint unsigned NOT NULL,
  `forma_pago_id` bigint unsigned NOT NULL,
  `banco_id` bigint unsigned DEFAULT NULL,
  `caja_id` bigint unsigned DEFAULT NULL,
  `usuario_id` bigint unsigned DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `monto` decimal(15,2) NOT NULL,
  `referencia` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pago_proveedores_numero_unique` (`numero`),
  KEY `pago_proveedores_forma_pago_id_foreign` (`forma_pago_id`),
  KEY `pago_proveedores_banco_id_foreign` (`banco_id`),
  KEY `pago_proveedores_caja_id_foreign` (`caja_id`),
  KEY `pago_proveedores_usuario_id_foreign` (`usuario_id`),
  KEY `pagopro_proveedor_idx` (`proveedor_id`),
  KEY `pagopro_fecha_idx` (`fecha`),
  CONSTRAINT `pago_proveedores_banco_id_foreign` FOREIGN KEY (`banco_id`) REFERENCES `bancos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pago_proveedores_caja_id_foreign` FOREIGN KEY (`caja_id`) REFERENCES `cajas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pago_proveedores_forma_pago_id_foreign` FOREIGN KEY (`forma_pago_id`) REFERENCES `formas_pago` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `pago_proveedores_proveedor_id_foreign` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `pago_proveedores_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pago_proveedores`
--

LOCK TABLES `pago_proveedores` WRITE;
/*!40000 ALTER TABLE `pago_proveedores` DISABLE KEYS */;
/*!40000 ALTER TABLE `pago_proveedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=133 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'config.ver','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(2,'config.editar','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(3,'empresa.ver','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(4,'empresa.editar','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(5,'bodega.ver','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(6,'bodega.crear','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(7,'bodega.editar','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(8,'bodega.eliminar','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(9,'categoria.ver','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(10,'categoria.crear','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(11,'categoria.editar','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(12,'categoria.eliminar','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(13,'marca.ver','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(14,'marca.crear','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(15,'marca.editar','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(16,'marca.eliminar','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(17,'impuesto.ver','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(18,'impuesto.crear','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(19,'impuesto.editar','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(20,'impuesto.eliminar','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(21,'forma_pago.ver','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(22,'forma_pago.crear','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(23,'forma_pago.editar','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(24,'forma_pago.eliminar','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(25,'numeracion.ver','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(26,'numeracion.crear','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(27,'numeracion.editar','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(28,'numeracion.eliminar','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(29,'unidad_medida.ver','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(30,'unidad_medida.crear','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(31,'unidad_medida.editar','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(32,'unidad_medida.eliminar','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(33,'banco.ver','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(34,'banco.crear','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(35,'banco.editar','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(36,'banco.eliminar','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(37,'admin.ver','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(38,'usuarios.ver','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(39,'usuarios.crear','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(40,'usuarios.editar','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(41,'usuarios.eliminar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(42,'roles.ver','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(43,'roles.crear','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(44,'roles.editar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(45,'roles.eliminar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(46,'auditoria.ver','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(47,'producto.ver','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(48,'producto.crear','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(49,'producto.editar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(50,'producto.eliminar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(51,'stock.ver','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(52,'movimiento_inventario.ver','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(53,'historico_precios.ver','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(54,'traslado.ver','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(55,'traslado.crear','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(56,'traslado.editar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(57,'traslado.eliminar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(58,'traslado.confirmar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(59,'traslado.anular','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(60,'proveedor.ver','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(61,'proveedor.crear','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(62,'proveedor.editar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(63,'proveedor.eliminar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(64,'cliente_catalogo.ver','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(65,'cliente_catalogo.crear','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(66,'cliente_catalogo.editar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(67,'cliente_catalogo.eliminar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(68,'compra.ver','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(69,'compra.crear','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(70,'compra.editar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(71,'compra.eliminar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(72,'compra.confirmar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(73,'pago_proveedor.ver','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(74,'pago_proveedor.crear','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(75,'pago_proveedor.editar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(76,'pago_proveedor.eliminar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(77,'cotizacion.ver','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(78,'cotizacion.crear','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(79,'cotizacion.editar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(80,'cotizacion.eliminar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(81,'remision.ver','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(82,'remision.crear','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(83,'remision.editar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(84,'remision.eliminar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(85,'remision.confirmar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(86,'venta.ver','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(87,'venta.crear','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(88,'venta.editar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(89,'venta.eliminar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(90,'venta.confirmar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(91,'pago_cliente.ver','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(92,'pago_cliente.crear','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(93,'pago_cliente.editar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(94,'pago_cliente.eliminar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(95,'transformacion.ver','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(96,'transformacion.crear','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(97,'transformacion.editar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(98,'transformacion.eliminar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(99,'transformacion.confirmar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(100,'formula_transformacion.ver','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(101,'formula_transformacion.crear','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(102,'formula_transformacion.editar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(103,'formula_transformacion.eliminar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(104,'ajuste_inventario.ver','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(105,'ajuste_inventario.crear','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(106,'ajuste_inventario.editar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(107,'ajuste_inventario.eliminar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(108,'ajuste_inventario.confirmar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(109,'conteo_fisico.ver','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(110,'conteo_fisico.crear','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(111,'conteo_fisico.editar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(112,'conteo_fisico.eliminar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(113,'conteo_fisico.cerrar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(114,'conteo_fisico.generar_ajuste','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(115,'reporte.ver','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(116,'reporte.exportar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(117,'reporte.imprimir','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(118,'dashboard.ver','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(119,'portal.ver','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(120,'portal.mis_cotizaciones','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(121,'portal.mis_remisiones','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(122,'portal.mis_ventas','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(123,'portal.mi_estado_cuenta','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(124,'caja.ver','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(125,'caja.crear','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(126,'caja.editar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(127,'caja.eliminar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(128,'movimiento_caja.ver','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(129,'movimiento_caja.crear','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(130,'movimiento_caja.editar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(131,'movimiento_caja.eliminar','web','2026-06-17 06:01:45','2026-06-17 06:01:45'),(132,'turno.ver','web','2026-06-17 06:01:45','2026-06-17 06:01:45');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `producto_proveedor_codigos`
--

DROP TABLE IF EXISTS `producto_proveedor_codigos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `producto_proveedor_codigos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `proveedor_id` bigint unsigned NOT NULL,
  `producto_id` bigint unsigned NOT NULL,
  `codigo_proveedor` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion_proveedor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prod_prov_cod_unique` (`proveedor_id`,`codigo_proveedor`),
  KEY `producto_proveedor_codigos_producto_id_foreign` (`producto_id`),
  CONSTRAINT `producto_proveedor_codigos_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `producto_proveedor_codigos_proveedor_id_foreign` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `producto_proveedor_codigos`
--

LOCK TABLES `producto_proveedor_codigos` WRITE;
/*!40000 ALTER TABLE `producto_proveedor_codigos` DISABLE KEYS */;
/*!40000 ALTER TABLE `producto_proveedor_codigos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos`
--

DROP TABLE IF EXISTS `productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `productos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_barras` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_comun` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_producto` enum('comprado','manufacturado','materia_prima','servicio') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'comprado' COMMENT 'Tipo de producto: comprado, manufacturado, materia_prima, servicio',
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `precio_compra` decimal(15,2) NOT NULL DEFAULT '0.00',
  `costo_promedio` decimal(15,4) NOT NULL DEFAULT '0.0000' COMMENT 'Costo promedio ponderado - se calcula al confirmar compras',
  `precio_venta` decimal(15,2) NOT NULL DEFAULT '0.00',
  `stock_minimo` decimal(10,3) NOT NULL DEFAULT '0.000',
  `stock_maximo` decimal(10,3) NOT NULL DEFAULT '0.000',
  `imagen` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `con_formula` tinyint(1) NOT NULL DEFAULT '0',
  `tiene_movimientos` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indica si el producto ya tiene transacciones (lo hace inmutable)',
  `exige_lote` tinyint(1) NOT NULL DEFAULT '0',
  `exige_serial` tinyint(1) NOT NULL DEFAULT '0',
  `categoria_id` bigint unsigned NOT NULL DEFAULT '1',
  `marca_id` bigint unsigned NOT NULL DEFAULT '1',
  `unidad_medida_id` bigint unsigned NOT NULL DEFAULT '1',
  `impuesto_id` bigint unsigned NOT NULL DEFAULT '1',
  `usuario_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `productos_codigo_unique` (`codigo`),
  UNIQUE KEY `productos_codigo_barras_unique` (`codigo_barras`),
  KEY `productos_marca_id_foreign` (`marca_id`),
  KEY `productos_unidad_medida_id_foreign` (`unidad_medida_id`),
  KEY `productos_impuesto_id_foreign` (`impuesto_id`),
  KEY `productos_usuario_id_foreign` (`usuario_id`),
  KEY `productos_codigo_idx` (`codigo`),
  KEY `productos_nombre_idx` (`nombre`),
  KEY `productos_categoria_idx` (`categoria_id`),
  KEY `productos_activo_idx` (`activo`),
  KEY `productos_tipo_producto_idx` (`tipo_producto`),
  CONSTRAINT `productos_categoria_id_foreign` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `productos_impuesto_id_foreign` FOREIGN KEY (`impuesto_id`) REFERENCES `impuestos` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `productos_marca_id_foreign` FOREIGN KEY (`marca_id`) REFERENCES `marcas` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `productos_unidad_medida_id_foreign` FOREIGN KEY (`unidad_medida_id`) REFERENCES `unidades_medida` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `productos_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos`
--

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
INSERT INTO `productos` VALUES (1,'PROD-0000001',NULL,'DDR3 4G (1600L) OEM (USADA)','DDR3 4G (1600L) OEM (USADA)','comprado',NULL,49000.00,49000.0000,70000.00,0.000,0.000,NULL,1,0,0,0,0,1,1,1,1,1,'2026-06-17 07:58:49','2026-06-17 07:58:49'),(2,'PROD-0000002',NULL,'DDR4 8G (3200) ADATA XPG SPECTRIX D35G RGB','DDR4 8G (3200) ADATA XPG SPECTRIX D35G RGB','comprado',NULL,309000.00,309000.0000,441428.57,0.000,0.000,NULL,1,0,0,0,0,1,2,1,1,1,'2026-06-19 08:14:53','2026-06-19 08:14:53'),(3,'PROD-0000003',NULL,'DDR4 8G (3200) VIPER PATRIOT STEEL RGB','DDR4 8G (3200) VIPER PATRIOT STEEL RGB','comprado',NULL,349000.00,349000.0000,498571.43,0.000,0.000,NULL,1,0,0,0,0,1,3,1,1,1,'2026-06-19 08:15:28','2026-06-19 08:15:28'),(4,'PROD-0000004',NULL,'DDR4 8G (3200) KINGSTON FURY BEAST RGB','DDR4 8G (3200) KINGSTON FURY BEAST RGB','comprado',NULL,0.00,0.0000,0.00,0.000,0.000,NULL,1,0,0,0,0,1,4,1,1,1,'2026-06-19 08:16:03','2026-06-19 08:16:03'),(5,'PROD-0000005',NULL,'DDR4 8G (3200) CORSAIR VENGEANCE RGB RS','DDR4 8G (3200) CORSAIR VENGEANCE RGB RS','comprado',NULL,349000.00,349000.0000,498571.43,0.000,0.000,NULL,1,0,0,0,0,1,5,1,1,1,'2026-06-19 08:18:10','2026-06-19 08:18:10'),(6,'PROD-0000006',NULL,'DDR4 8G (3200) CORSAIR VENGEANCE RGB PRO','DDR4 8G (3200) CORSAIR VENGEANCE RGB PRO','comprado',NULL,349000.00,349000.0000,498571.43,0.000,0.000,NULL,1,0,0,0,0,1,5,1,1,1,'2026-06-19 08:18:51','2026-06-19 08:18:51'),(7,'PROD-0000007',NULL,'DDR4 16G (3200) ADATA XPG SPECTRIX D35G RGB','DDR4 16G (3200) ADATA XPG SPECTRIX D35G RGB','comprado',NULL,545000.00,545000.0000,778571.43,0.000,0.000,NULL,1,0,0,0,0,1,2,1,1,1,'2026-06-19 08:19:25','2026-06-19 08:19:25'),(8,'PROD-0000008',NULL,'DDR4 16G (3200) CORSAIR VENGEANCE RGB RS','DDR4 16G (3200) CORSAIR VENGEANCE RGB RS','comprado',NULL,555.00,555.0000,792.86,0.000,0.000,NULL,1,0,0,0,0,1,5,1,1,1,'2026-06-19 08:19:52','2026-06-19 08:19:52'),(9,'PROD-0000009',NULL,'DDR4 32G (3200) ADATA XPG SPECTRIX D50 GRIS RGB','DDR4 32G (3200) ADATA XPG SPECTRIX D50 GRIS RGB','comprado',NULL,929000.00,929000.0000,1327142.86,0.000,0.000,NULL,1,0,0,0,0,1,2,1,1,1,'2026-06-19 08:20:26','2026-06-19 08:20:26'),(10,'PROD-0000010',NULL,'DDR5 16G (5600) KINGSTON NO DISIPADA','DDR5 16G (5600) KINGSTON NO DISIPADA','comprado',NULL,885000.00,885000.0000,1264285.71,0.000,0.000,NULL,1,0,0,0,0,1,4,1,1,1,'2026-06-19 08:57:47','2026-06-19 08:57:47'),(11,'PROD-0000011',NULL,'DDR5 16G (5600) KINGSTON FURY BEAST','DDR5 16G (5600) KINGSTON FURY BEAST','comprado',NULL,979000.00,979000.0000,1398571.43,0.000,0.000,NULL,1,0,0,0,0,1,4,1,1,1,'2026-06-19 08:58:21','2026-06-19 08:58:21'),(12,'PROD-0000012',NULL,'DDR5 16G (5600) PATRIOT VIPER BLANCA ELITE 5 RGB','DDR5 16G (5600) PATRIOT VIPER BLANCA ELITE 5 RGB','comprado',NULL,979000.00,979000.0000,1398571.43,0.000,0.000,NULL,1,0,0,0,0,1,3,1,1,1,'2026-06-19 08:58:48','2026-06-19 08:58:48'),(13,'PROD-0000013',NULL,'PORTATIL DDR4 8G (3200) SAMSUNG  OEM USADA','PORTATIL DDR4 8G (3200) SAMSUNG  OEM USADA','comprado',NULL,199000.00,199000.0000,284285.71,0.000,0.000,NULL,1,0,0,0,0,1,6,1,1,1,'2026-06-19 08:59:34','2026-06-19 08:59:34'),(14,'PROD-0000014',NULL,'PORTATIL DDR4 16G (3200) ADATA','PORTATIL DDR4 16G (3200) ADATA','comprado',NULL,499000.00,499000.0000,712857.14,0.000,0.000,NULL,1,0,0,0,0,1,2,1,1,1,'2026-06-19 09:00:10','2026-06-19 09:00:10'),(15,'PROD-0000015',NULL,'PORTATIL DDR4 16G (3200) CORSAIR VENGEANCE','PORTATIL DDR4 16G (3200) CORSAIR VENGEANCE','comprado',NULL,509000.00,509000.0000,727142.86,0.000,0.000,NULL,1,0,0,0,0,1,5,1,1,1,'2026-06-19 09:00:41','2026-06-19 09:00:41'),(16,'PROD-0000016',NULL,'PORTATIL DDR4 16G (3200) KINGSTON','PORTATIL DDR4 16G (3200) KINGSTON','comprado',NULL,509000.00,509000.0000,727142.86,0.000,0.000,NULL,1,0,0,0,0,1,4,1,1,1,'2026-06-19 09:01:15','2026-06-19 09:01:15'),(17,'PROD-0000017',NULL,'PORTATIL DDR4 16G (3200) CRUCIAL','PORTATIL DDR4 16G (3200) CRUCIAL','comprado',NULL,509000.00,509000.0000,727142.86,0.000,0.000,NULL,1,0,0,0,0,1,7,1,1,1,'2026-06-19 09:02:04','2026-06-19 09:02:04'),(18,'PROD-0000018',NULL,'PORTATIL DDR4 32G (3200) ADATA','PORTATIL DDR4 32G (3200) ADATA','comprado',NULL,1055000.00,1055000.0000,1507142.86,0.000,0.000,NULL,1,0,0,0,0,1,2,1,1,1,'2026-06-19 09:02:38','2026-06-19 09:02:38'),(19,'PROD-0000019',NULL,'SOLIDO SATA (SSD) 480GB KINGSTON A400','SOLIDO SATA (SSD) 480GB KINGSTON A400','comprado',NULL,405000.00,405000.0000,578571.43,0.000,0.000,NULL,1,0,0,0,0,2,4,1,1,1,'2026-06-19 09:03:50','2026-06-19 09:03:50'),(20,'PROD-0000020',NULL,'SOLIDO SATA (SSD) 500GB CRUCIAL BX500','SOLIDO SATA (SSD) 500GB CRUCIAL BX500','comprado',NULL,415000.00,415000.0000,592857.14,0.000,0.000,NULL,1,0,0,0,0,2,7,1,1,1,'2026-06-19 09:04:24','2026-06-19 09:04:24'),(21,'PROD-0000021',NULL,'SOLIDO SATA (SSD) 960GB KINGSTON A400','SOLIDO SATA (SSD) 960GB KINGSTON A400','comprado',NULL,545000.00,545000.0000,778571.43,0.000,0.000,NULL,1,0,0,0,0,2,4,1,1,1,'2026-06-19 09:05:02','2026-06-19 09:05:02'),(22,'PROD-0000022',NULL,'SOLIDO SATA (SSD) 1TB P220 PATRIOT','SOLIDO SATA (SSD) 1TB P220 PATRIOT','comprado',NULL,539000.00,539000.0000,770000.00,0.000,0.000,NULL,1,0,0,0,0,1,3,1,1,1,'2026-06-19 09:05:32','2026-06-19 09:05:32'),(23,'PROD-0000023',NULL,'SSD (M2) NVME 500GB KINGSTON NV3 (5000X3000)','SSD (M2) NVME 500GB KINGSTON NV3 (5000X3000)','comprado',NULL,459000.00,459000.0000,655714.29,0.000,0.000,NULL,1,0,0,0,0,2,4,1,1,1,'2026-06-19 09:06:42','2026-06-19 09:06:42'),(24,'PROD-0000024',NULL,'SSD (M2) NVME 1TB CRUCIAL E100 GEN4 (5000X3000)','SSD (M2) NVME 1TB CRUCIAL E100 GEN4 (5000X3000)','comprado',NULL,659000.00,659000.0000,941428.57,0.000,0.000,NULL,1,0,0,0,0,2,7,1,1,1,'2026-06-19 09:07:15','2026-06-19 09:07:15');
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proveedores`
--

DROP TABLE IF EXISTS `proveedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `proveedores` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `documento` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_documento` enum('CC','NIT','CE','PP') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NIT',
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccion1` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccion2` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `departamento_id` bigint unsigned NOT NULL,
  `ciudad_id` bigint unsigned NOT NULL,
  `saldo` decimal(15,2) NOT NULL DEFAULT '0.00',
  `pais` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Colombia',
  `estado` enum('activo','inactivo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `limite_credito` decimal(15,2) NOT NULL DEFAULT '0.00',
  `dias_credito` int NOT NULL DEFAULT '0',
  `dias_pago` int NOT NULL DEFAULT '0',
  `contacto_principal` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sitio_web` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usuario_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `proveedores_documento_unique` (`documento`),
  UNIQUE KEY `proveedores_email_unique` (`email`),
  KEY `proveedores_departamento_id_foreign` (`departamento_id`),
  KEY `proveedores_ciudad_id_foreign` (`ciudad_id`),
  KEY `proveedores_usuario_id_foreign` (`usuario_id`),
  CONSTRAINT `proveedores_ciudad_id_foreign` FOREIGN KEY (`ciudad_id`) REFERENCES `ciudades` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `proveedores_departamento_id_foreign` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `proveedores_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedores`
--

LOCK TABLES `proveedores` WRITE;
/*!40000 ALTER TABLE `proveedores` DISABLE KEYS */;
INSERT INTO `proveedores` VALUES (1,'PROVEEDORES VARIOS','9999999999','NIT','999999999','no_tiene_correo@correo.com','SIN INFORMACION',NULL,54,889,0.00,'Colombia','activo',0.00,0,0,NULL,NULL,2,'2026-06-17 06:01:47','2026-06-17 06:01:47');
/*!40000 ALTER TABLE `proveedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `remisiones`
--

DROP TABLE IF EXISTS `remisiones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `remisiones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('borrador','confirmada','facturada','anulada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador' COMMENT 'Ciclo de vida del documento',
  `confirmada_en` timestamp NULL DEFAULT NULL COMMENT 'Fecha en que se confirmó la remisión',
  `cliente_id` bigint unsigned NOT NULL,
  `bodega_id` bigint unsigned NOT NULL,
  `usuario_id` bigint unsigned DEFAULT NULL,
  `cotizacion_id` bigint unsigned DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `descuento` decimal(15,2) NOT NULL DEFAULT '0.00',
  `impuestos` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_confirmado` decimal(15,2) DEFAULT NULL COMMENT 'Total capturado en el momento de la confirmación',
  `impuestos_confirmados` decimal(15,2) DEFAULT NULL COMMENT 'Impuestos capturados en el momento de la confirmación',
  `snapshot_confirmacion` json DEFAULT NULL COMMENT 'Snapshot JSON de datos financieros al confirmar',
  `saldo_pendiente` decimal(15,2) NOT NULL DEFAULT '0.00',
  `estado_pago` enum('pagado','pendiente','parcial','anulada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `fecha_vencimiento` date DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `remisiones_numero_unique` (`numero`),
  KEY `remisiones_bodega_id_foreign` (`bodega_id`),
  KEY `remisiones_usuario_id_foreign` (`usuario_id`),
  KEY `remisiones_cotizacion_id_foreign` (`cotizacion_id`),
  KEY `remisiones_cliente_idx` (`cliente_id`),
  KEY `remisiones_estado_idx` (`estado`),
  KEY `remisiones_estado_pago_idx` (`estado_pago`),
  KEY `remisiones_fecha_idx` (`fecha`),
  CONSTRAINT `remisiones_bodega_id_foreign` FOREIGN KEY (`bodega_id`) REFERENCES `bodegas` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `remisiones_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `remisiones_cotizacion_id_foreign` FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizaciones` (`id`) ON DELETE SET NULL,
  CONSTRAINT `remisiones_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `remisiones`
--

LOCK TABLES `remisiones` WRITE;
/*!40000 ALTER TABLE `remisiones` DISABLE KEYS */;
/*!40000 ALTER TABLE `remisiones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(11,1),(12,1),(13,1),(14,1),(15,1),(16,1),(17,1),(18,1),(19,1),(20,1),(21,1),(22,1),(23,1),(24,1),(25,1),(26,1),(27,1),(28,1),(29,1),(30,1),(31,1),(32,1),(33,1),(34,1),(35,1),(36,1),(37,1),(38,1),(39,1),(40,1),(41,1),(42,1),(43,1),(44,1),(45,1),(46,1),(47,1),(48,1),(49,1),(50,1),(51,1),(52,1),(53,1),(54,1),(55,1),(56,1),(57,1),(58,1),(59,1),(60,1),(61,1),(62,1),(63,1),(64,1),(65,1),(66,1),(67,1),(68,1),(69,1),(70,1),(71,1),(72,1),(73,1),(74,1),(75,1),(76,1),(77,1),(78,1),(79,1),(80,1),(81,1),(82,1),(83,1),(84,1),(85,1),(86,1),(87,1),(88,1),(89,1),(90,1),(91,1),(92,1),(93,1),(94,1),(95,1),(96,1),(97,1),(98,1),(99,1),(100,1),(101,1),(102,1),(103,1),(104,1),(105,1),(106,1),(107,1),(108,1),(109,1),(110,1),(111,1),(112,1),(113,1),(114,1),(115,1),(116,1),(117,1),(118,1),(119,1),(120,1),(121,1),(122,1),(123,1),(124,1),(125,1),(126,1),(127,1),(128,1),(129,1),(130,1),(131,1),(132,1),(1,2),(3,2),(5,2),(9,2),(13,2),(17,2),(21,2),(25,2),(29,2),(33,2),(47,2),(48,2),(49,2),(50,2),(51,2),(52,2),(53,2),(54,2),(55,2),(56,2),(57,2),(58,2),(59,2),(60,2),(61,2),(62,2),(63,2),(64,2),(65,2),(66,2),(67,2),(68,2),(69,2),(70,2),(71,2),(72,2),(73,2),(74,2),(75,2),(76,2),(77,2),(78,2),(79,2),(80,2),(81,2),(82,2),(83,2),(84,2),(85,2),(86,2),(87,2),(88,2),(89,2),(90,2),(91,2),(92,2),(93,2),(94,2),(95,2),(96,2),(97,2),(98,2),(99,2),(100,2),(101,2),(102,2),(103,2),(104,2),(105,2),(106,2),(107,2),(108,2),(109,2),(110,2),(111,2),(112,2),(113,2),(114,2),(115,2),(116,2),(117,2),(118,2),(1,3),(46,3),(47,3),(48,3),(49,3),(50,3),(51,3),(52,3),(53,3),(54,3),(55,3),(56,3),(57,3),(58,3),(59,3),(60,3),(61,3),(62,3),(63,3),(64,3),(65,3),(66,3),(67,3),(68,3),(69,3),(70,3),(71,3),(72,3),(73,3),(74,3),(75,3),(76,3),(77,3),(78,3),(79,3),(80,3),(81,3),(82,3),(83,3),(84,3),(85,3),(86,3),(87,3),(88,3),(89,3),(90,3),(91,3),(92,3),(93,3),(94,3),(95,3),(96,3),(97,3),(98,3),(99,3),(100,3),(101,3),(102,3),(103,3),(104,3),(109,3),(115,3),(116,3),(117,3),(118,3),(47,4),(77,4),(78,4),(79,4),(81,4),(82,4),(83,4),(86,4),(87,4),(88,4),(118,4);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'administrador','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(2,'auxiliar','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(3,'contador','web','2026-06-17 06:01:44','2026-06-17 06:01:44'),(4,'vendedor','web','2026-06-17 06:01:44','2026-06-17 06:01:44');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('Ov5fhn5CCCmKiTxbMbMtfkaR3MBCSnvSixWpcTMJ',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','YTo3OntzOjY6Il90b2tlbiI7czo0MDoiV0thTm5qcTlvM0xHQ2RndU8xWW85YUpMOTlEQ0J1SmFGSXBxemppUCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzI6Imh0dHBzOi8vc2lnYWludi50ZXN0L2FkbWluL2NhamFzIjtzOjU6InJvdXRlIjtzOjM2OiJmaWxhbWVudC5hZG1pbi5yZXNvdXJjZXMuY2FqYXMuaW5kZXgiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6MTc6InBhc3N3b3JkX2hhc2hfd2ViIjtzOjY0OiIyNDUxNzYyYjI2Nzc2YjgzZTlkMTNjYTUyOGQ4NGM4Mjg5MDFkNjAxOTYyMGViNmIyNGVlMjk1ZDZlZGM2ODRkIjtzOjY6InRhYmxlcyI7YTo0OntzOjQwOiI5ZDI1NmFlYzJjMGM3MTFkMzkyM2RkN2E0MWNlMWVlYV9jb2x1bW5zIjthOjc6e2k6MDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo2OiJudW1lcm8iO3M6NToibGFiZWwiO3M6MzoiTsKwIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo1OiJmZWNoYSI7czo1OiJsYWJlbCI7czo1OiJGZWNoYSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjI7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTQ6ImNsaWVudGUubm9tYnJlIjtzOjU6ImxhYmVsIjtzOjc6IkNsaWVudGUiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTozO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjY6ImVzdGFkbyI7czo1OiJsYWJlbCI7czo2OiJFc3RhZG8iO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTo0O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjU6InRvdGFsIjtzOjU6ImxhYmVsIjtzOjU6IlRvdGFsIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxNToic2FsZG9fcGVuZGllbnRlIjtzOjU6ImxhYmVsIjtzOjU6IlNhbGRvIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMjoidXN1YXJpby5uYW1lIjtzOjU6ImxhYmVsIjtzOjc6IlVzdWFyaW8iO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjowO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjoxO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7YjoxO319czo0MDoiOGU0NDg5NTg0NDUwYzg0ZTQ0ZTIxZDM3NGM5OGVmYjJfY29sdW1ucyI7YTo4OntpOjA7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6NjoibnVtZXJvIjtzOjU6ImxhYmVsIjtzOjc6Ik7Dum1lcm8iO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToxO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEzOiJib2RlZ2Eubm9tYnJlIjtzOjU6ImxhYmVsIjtzOjY6IkJvZGVnYSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjI7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTY6ImVzX3NhbGRvX2luaWNpYWwiO3M6NToibGFiZWwiO3M6MTM6IlNhbGRvIEluaWNpYWwiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTozO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEyOiJmZWNoYV9pbmljaW8iO3M6NToibGFiZWwiO3M6MTI6IkZlY2hhIEluaWNpbyI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjQ7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTI6ImZlY2hhX2NpZXJyZSI7czo1OiJsYWJlbCI7czoxMjoiRmVjaGEgQ2llcnJlIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxNDoiZGV0YWxsZXNfY291bnQiO3M6NToibGFiZWwiO3M6MTE6IiMgUHJvZHVjdG9zIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6NjthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxNzoiZGlmZXJlbmNpYXNfY291bnQiO3M6NToibGFiZWwiO3M6MTM6IiMgRGlmZXJlbmNpYXMiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTo3O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjY6ImVzdGFkbyI7czo1OiJsYWJlbCI7czo2OiJFc3RhZG8iO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9fXM6NDA6ImEyOTczN2ZiZGIyNDZkOTg0NTE2MmU4MGU3YzA2YTgwX2NvbHVtbnMiO2E6Njp7aTowO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjE1OiJwcm9kdWN0by5jb2RpZ28iO3M6NToibGFiZWwiO3M6NzoiQ8OzZGlnbyI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjE7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTU6InByb2R1Y3RvLm5vbWJyZSI7czo1OiJsYWJlbCI7czo4OiJQcm9kdWN0byI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjI7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6Mjg6InByb2R1Y3RvLnVuaWRhZE1lZGlkYS5ub21icmUiO3M6NToibGFiZWwiO3M6NjoiVW5pZGFkIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMzoic3RvY2tfc2lzdGVtYSI7czo1OiJsYWJlbCI7czoxMzoiU3RvY2sgU2lzdGVtYSI7czo4OiJpc0hpZGRlbiI7YjowO3M6OToiaXNUb2dnbGVkIjtiOjE7czoxMjoiaXNUb2dnbGVhYmxlIjtiOjA7czoyNDoiaXNUb2dnbGVkSGlkZGVuQnlEZWZhdWx0IjtOO31pOjQ7YTo3OntzOjQ6InR5cGUiO3M6NjoiY29sdW1uIjtzOjQ6Im5hbWUiO3M6MTY6ImNhbnRpZGFkX2NvbnRhZGEiO3M6NToibGFiZWwiO3M6MTM6IkNhbnQuIENvbnRhZGEiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTo1O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEwOiJkaWZlcmVuY2lhIjtzOjU6ImxhYmVsIjtzOjEwOiJEaWZlcmVuY2lhIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fX1zOjQwOiI4YTIzMDhiY2FhNzc2YWVmZDFhYWJmYmRmOGQ1YmVlZF9jb2x1bW5zIjthOjY6e2k6MDthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo2OiJub21icmUiO3M6NToibGFiZWwiO3M6NjoiTm9tYnJlIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MTthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czo0OiJ0aXBvIjtzOjU6ImxhYmVsIjtzOjQ6IlRpcG8iO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aToyO2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEzOiJzYWxkb19pbmljaWFsIjtzOjU6ImxhYmVsIjtzOjEzOiJTYWxkbyBJbmljaWFsIjtzOjg6ImlzSGlkZGVuIjtiOjA7czo5OiJpc1RvZ2dsZWQiO2I6MTtzOjEyOiJpc1RvZ2dsZWFibGUiO2I6MDtzOjI0OiJpc1RvZ2dsZWRIaWRkZW5CeURlZmF1bHQiO047fWk6MzthOjc6e3M6NDoidHlwZSI7czo2OiJjb2x1bW4iO3M6NDoibmFtZSI7czoxMjoic2FsZG9fYWN0dWFsIjtzOjU6ImxhYmVsIjtzOjEyOiJTYWxkbyBBY3R1YWwiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTo0O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjY6ImVzdGFkbyI7czo1OiJsYWJlbCI7czo2OiJFc3RhZG8iO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9aTo1O2E6Nzp7czo0OiJ0eXBlIjtzOjY6ImNvbHVtbiI7czo0OiJuYW1lIjtzOjEyOiJ1c3VhcmlvLm5hbWUiO3M6NToibGFiZWwiO3M6MTA6IkNyZWFkYSBwb3IiO3M6ODoiaXNIaWRkZW4iO2I6MDtzOjk6ImlzVG9nZ2xlZCI7YjoxO3M6MTI6ImlzVG9nZ2xlYWJsZSI7YjowO3M6MjQ6ImlzVG9nZ2xlZEhpZGRlbkJ5RGVmYXVsdCI7Tjt9fX1zOjg6ImZpbGFtZW50IjthOjA6e319',1781842072);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_bodega_lotes`
--

DROP TABLE IF EXISTS `stock_bodega_lotes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_bodega_lotes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `stock_bodega_id` bigint unsigned NOT NULL,
  `lote` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `cantidad` decimal(10,3) NOT NULL DEFAULT '0.000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sbl_lote_idx` (`stock_bodega_id`,`lote`),
  KEY `sbl_vence_idx` (`fecha_vencimiento`),
  CONSTRAINT `stock_bodega_lotes_stock_bodega_id_foreign` FOREIGN KEY (`stock_bodega_id`) REFERENCES `stock_bodegas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_bodega_lotes`
--

LOCK TABLES `stock_bodega_lotes` WRITE;
/*!40000 ALTER TABLE `stock_bodega_lotes` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_bodega_lotes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_bodega_serials`
--

DROP TABLE IF EXISTS `stock_bodega_serials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_bodega_serials` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `stock_bodega_id` bigint unsigned NOT NULL,
  `serial` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available',
  `lote` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stock_bodega_serials_serial_unique` (`serial`),
  KEY `sbs_status_idx` (`stock_bodega_id`,`status`),
  CONSTRAINT `stock_bodega_serials_stock_bodega_id_foreign` FOREIGN KEY (`stock_bodega_id`) REFERENCES `stock_bodegas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_bodega_serials`
--

LOCK TABLES `stock_bodega_serials` WRITE;
/*!40000 ALTER TABLE `stock_bodega_serials` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_bodega_serials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_bodegas`
--

DROP TABLE IF EXISTS `stock_bodegas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_bodegas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `producto_id` bigint unsigned NOT NULL,
  `bodega_id` bigint unsigned NOT NULL,
  `cantidad` decimal(10,3) NOT NULL DEFAULT '0.000',
  `ubicacion` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stock_producto_bodega_unique` (`producto_id`,`bodega_id`),
  KEY `stock_bodega_producto_idx` (`bodega_id`,`producto_id`),
  CONSTRAINT `stock_bodegas_bodega_id_foreign` FOREIGN KEY (`bodega_id`) REFERENCES `bodegas` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `stock_bodegas_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_bodegas`
--

LOCK TABLES `stock_bodegas` WRITE;
/*!40000 ALTER TABLE `stock_bodegas` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_bodegas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transformacion_detalles`
--

DROP TABLE IF EXISTS `transformacion_detalles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transformacion_detalles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `transformacion_id` bigint unsigned NOT NULL,
  `tipo_linea` enum('insumo','producto') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'insumo',
  `producto_id` bigint unsigned NOT NULL,
  `cantidad` decimal(10,3) NOT NULL,
  `lote` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `serial` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `costo_unitario` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transformacion_detalles_producto_id_foreign` (`producto_id`),
  KEY `transformacion_detalles_tipo_idx` (`transformacion_id`,`tipo_linea`),
  CONSTRAINT `transformacion_detalles_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  CONSTRAINT `transformacion_detalles_transformacion_id_foreign` FOREIGN KEY (`transformacion_id`) REFERENCES `transformaciones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transformacion_detalles`
--

LOCK TABLES `transformacion_detalles` WRITE;
/*!40000 ALTER TABLE `transformacion_detalles` DISABLE KEYS */;
/*!40000 ALTER TABLE `transformacion_detalles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transformaciones`
--

DROP TABLE IF EXISTS `transformaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transformaciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bodega_id` bigint unsigned NOT NULL,
  `tipo` enum('combo','promo','reenvase','fabricacion') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fabricacion',
  `tipo_promo` enum('descuento','cantidad','empaquetado') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Solo aplica si tipo = promo',
  `fecha_vencimiento` datetime DEFAULT NULL COMMENT 'Solo aplica si tipo = promo',
  `producto_final_id` bigint unsigned DEFAULT NULL,
  `formula_transformacion_id` bigint unsigned DEFAULT NULL,
  `cantidad_a_producir` decimal(10,3) NOT NULL DEFAULT '1.000' COMMENT 'Cuántos productos finales se desean crear con esta fórmula',
  `tipo_calculo_precio` enum('margen','manual') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'margen',
  `costo_total` decimal(15,2) DEFAULT NULL,
  `precio_sugerido` decimal(15,2) DEFAULT NULL,
  `margen_deseado` decimal(5,2) NOT NULL DEFAULT '30.00',
  `estado` enum('borrador','confirmada','revertida') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  `confirmada_en` datetime DEFAULT NULL,
  `revertida_en` datetime DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `usuario_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transformaciones_producto_final_id_foreign` (`producto_final_id`),
  KEY `transformaciones_formula_transformacion_id_foreign` (`formula_transformacion_id`),
  KEY `transformaciones_usuario_id_foreign` (`usuario_id`),
  KEY `transformaciones_bodega_fecha_idx` (`bodega_id`,`fecha`),
  KEY `transformaciones_tipo_estado_idx` (`tipo`,`estado`),
  KEY `transformaciones_fecha_vencimiento_idx` (`fecha_vencimiento`),
  CONSTRAINT `transformaciones_bodega_id_foreign` FOREIGN KEY (`bodega_id`) REFERENCES `bodegas` (`id`),
  CONSTRAINT `transformaciones_formula_transformacion_id_foreign` FOREIGN KEY (`formula_transformacion_id`) REFERENCES `formula_transformaciones` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transformaciones_producto_final_id_foreign` FOREIGN KEY (`producto_final_id`) REFERENCES `productos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transformaciones_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transformaciones`
--

LOCK TABLES `transformaciones` WRITE;
/*!40000 ALTER TABLE `transformaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `transformaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `traslado_detalles`
--

DROP TABLE IF EXISTS `traslado_detalles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `traslado_detalles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `traslado_id` bigint unsigned NOT NULL,
  `producto_id` bigint unsigned NOT NULL,
  `cantidad` decimal(10,3) NOT NULL,
  `lote` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `traslado_detalles_producto_id_foreign` (`producto_id`),
  KEY `traslado_detalles_producto_idx` (`traslado_id`,`producto_id`),
  CONSTRAINT `traslado_detalles_producto_id_foreign` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  CONSTRAINT `traslado_detalles_traslado_id_foreign` FOREIGN KEY (`traslado_id`) REFERENCES `traslados` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `traslado_detalles`
--

LOCK TABLES `traslado_detalles` WRITE;
/*!40000 ALTER TABLE `traslado_detalles` DISABLE KEYS */;
/*!40000 ALTER TABLE `traslado_detalles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `traslados`
--

DROP TABLE IF EXISTS `traslados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `traslados` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bodega_origen_id` bigint unsigned NOT NULL,
  `bodega_destino_id` bigint unsigned NOT NULL,
  `estado` enum('borrador','confirmada','revertida','anulada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador',
  `confirmada_en` datetime DEFAULT NULL,
  `revertida_en` datetime DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `usuario_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `traslados_usuario_id_foreign` (`usuario_id`),
  KEY `traslados_origen_fecha_idx` (`bodega_origen_id`,`fecha`),
  KEY `traslados_destino_fecha_idx` (`bodega_destino_id`,`fecha`),
  KEY `traslados_estado_idx` (`estado`),
  CONSTRAINT `traslados_bodega_destino_id_foreign` FOREIGN KEY (`bodega_destino_id`) REFERENCES `bodegas` (`id`),
  CONSTRAINT `traslados_bodega_origen_id_foreign` FOREIGN KEY (`bodega_origen_id`) REFERENCES `bodegas` (`id`),
  CONSTRAINT `traslados_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `traslados`
--

LOCK TABLES `traslados` WRITE;
/*!40000 ALTER TABLE `traslados` DISABLE KEYS */;
/*!40000 ALTER TABLE `traslados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `turnos`
--

DROP TABLE IF EXISTS `turnos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `turnos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `caja_id` bigint unsigned NOT NULL,
  `bodega_id` bigint unsigned DEFAULT '1',
  `usuario_id` bigint unsigned NOT NULL,
  `fecha_apertura` datetime NOT NULL,
  `fecha_cierre` datetime DEFAULT NULL,
  `saldo_inicial` decimal(15,2) NOT NULL DEFAULT '0.00',
  `saldo_final_esperado` decimal(15,2) DEFAULT NULL,
  `saldo_final_real` decimal(15,2) DEFAULT NULL,
  `diferencia` decimal(15,2) DEFAULT NULL,
  `estado` enum('abierto','cerrado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'abierto',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `turnos_bodega_id_foreign` (`bodega_id`),
  KEY `turnos_usuario_id_foreign` (`usuario_id`),
  KEY `turnos_caja_estado_idx` (`caja_id`,`estado`),
  CONSTRAINT `turnos_bodega_id_foreign` FOREIGN KEY (`bodega_id`) REFERENCES `bodegas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `turnos_caja_id_foreign` FOREIGN KEY (`caja_id`) REFERENCES `cajas` (`id`),
  CONSTRAINT `turnos_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `turnos`
--

LOCK TABLES `turnos` WRITE;
/*!40000 ALTER TABLE `turnos` DISABLE KEYS */;
/*!40000 ALTER TABLE `turnos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unidades_medida`
--

DROP TABLE IF EXISTS `unidades_medida`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `unidades_medida` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `simbolo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unidades_medida`
--

LOCK TABLES `unidades_medida` WRITE;
/*!40000 ALTER TABLE `unidades_medida` DISABLE KEYS */;
INSERT INTO `unidades_medida` VALUES (1,'Unidad','UND','Unidad genérica',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(2,'Par','PAR','Conjunto de dos unidades',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(3,'Docena','DOC','12 unidades',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(4,'Ciento','CTO','100 unidades',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(5,'Caja','CJA','Caja (contenido variable)',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(6,'Paquete','PQT','Paquete (contenido variable)',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(7,'Rollo','RLL','Rollo',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(8,'Juego','JGO','Juego o set de piezas',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(9,'Gramo','g','Gramo',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(10,'Kilogramo','kg','Kilogramo (1000 g)',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(11,'Tonelada','ton','Tonelada métrica (1000 kg)',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(12,'Libra','lb','Libra (0.453592 kg)',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(13,'Onza','oz','Onza (28.3495 g)',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(14,'Mililitro','ml','Mililitro',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(15,'Litro','L','Litro (1000 ml)',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(16,'Galón','gal','Galón (3.785 L)',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(17,'Metro cúbico','m³','Metro cúbico',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(18,'Milímetro','mm','Milímetro',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(19,'Centímetro','cm','Centímetro',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(20,'Metro','m','Metro',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(21,'Kilómetro','km','Kilómetro',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(22,'Pulgada','in','Pulgada (2.54 cm)',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(23,'Pie','ft','Pie (30.48 cm)',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(24,'Yarda','yd','Yarda (91.44 cm)',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(25,'Metro cuadrado','m²','Metro cuadrado',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(26,'Hora','h','Hora de servicio',1,'2026-06-17 06:01:47','2026-06-17 06:01:47'),(27,'Día','día','Día de servicio',1,'2026-06-17 06:01:47','2026-06-17 06:01:47');
/*!40000 ALTER TABLE `unidades_medida` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `celular` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` enum('pendiente','activo','inactivo','bloqueado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `fecha_nacimiento` date DEFAULT NULL,
  `cargo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar_url` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_fields` json DEFAULT NULL,
  `locale` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `theme_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_changed_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `app_authentication_secret` text COLLATE utf8mb4_unicode_ci,
  `app_authentication_recovery_codes` text COLLATE utf8mb4_unicode_ci,
  `has_email_authentication` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'José Francisco Orozco','joseforozco@gmail.com','2026-06-17 06:01:45','3001234567','activo',NULL,'Administrador del Sistema',NULL,NULL,NULL,NULL,NULL,NULL,'$2y$12$.xwgB.slenBBoOtr9gvXd.3epaQjh/nYDsZxn2j8.s.GCpRAwyLpO',NULL,'2026-06-17 06:01:45','2026-06-17 06:01:45',NULL,NULL,0),(2,'Usuario Auxiliar','auxiliar@sigainv.test','2026-06-17 06:01:45','3001111111','activo',NULL,'Auxiliar Administrativo',NULL,NULL,NULL,NULL,NULL,NULL,'$2y$12$f8r.2zALI8MPwc7YDkYlMulO5g4n9m395SqAQd4WNf3ZtWUDqgXlS',NULL,'2026-06-17 06:01:45','2026-06-17 06:01:45',NULL,NULL,0),(3,'Usuario Contador','contador@sigainv.test','2026-06-17 06:01:46','3002222222','activo',NULL,'Contador',NULL,NULL,NULL,NULL,NULL,NULL,'$2y$12$bgQS7903gtlDFiXUR/WrbuW9LZk9fl3X/anrevE4DlqECZz158nXi',NULL,'2026-06-17 06:01:46','2026-06-17 06:01:46',NULL,NULL,0),(4,'Usuario Vendedor','vendedor@sigainv.test','2026-06-17 06:01:46','3003333333','activo',NULL,'Vendedor',NULL,NULL,NULL,NULL,NULL,NULL,'$2y$12$T.iMn14RcQ9W2PAsVuxO6eco8BkomvOnX6Wtn0VHVCrJSw.MG6Rem',NULL,'2026-06-17 06:01:46','2026-06-17 06:01:46',NULL,NULL,0);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ventas`
--

DROP TABLE IF EXISTS `ventas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ventas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `numero` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('borrador','confirmada','pagada','anulada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrador' COMMENT 'Ciclo de vida del documento',
  `confirmada_en` timestamp NULL DEFAULT NULL COMMENT 'Fecha en que se confirmó la venta',
  `cliente_id` bigint unsigned NOT NULL,
  `bodega_id` bigint unsigned NOT NULL,
  `usuario_id` bigint unsigned DEFAULT NULL,
  `cotizacion_id` bigint unsigned DEFAULT NULL,
  `remision_id` bigint unsigned DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `descuento` decimal(15,2) NOT NULL DEFAULT '0.00',
  `impuestos` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_confirmado` decimal(15,2) DEFAULT NULL COMMENT 'Total capturado en el momento de la confirmación',
  `impuestos_confirmados` decimal(15,2) DEFAULT NULL COMMENT 'Impuestos capturados en el momento de la confirmación',
  `snapshot_confirmacion` json DEFAULT NULL COMMENT 'Snapshot JSON de datos financieros al confirmar',
  `saldo_pendiente` decimal(15,2) NOT NULL DEFAULT '0.00',
  `estado_pago` enum('pagado','pendiente','parcial','anulada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `fecha_vencimiento` date DEFAULT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ventas_numero_unique` (`numero`),
  KEY `ventas_bodega_id_foreign` (`bodega_id`),
  KEY `ventas_cotizacion_id_foreign` (`cotizacion_id`),
  KEY `ventas_remision_id_foreign` (`remision_id`),
  KEY `ventas_fecha_idx` (`fecha`),
  KEY `ventas_estado_idx` (`estado`),
  KEY `ventas_cliente_estado_idx` (`cliente_id`,`estado_pago`),
  KEY `ventas_usuario_idx` (`usuario_id`),
  CONSTRAINT `ventas_bodega_id_foreign` FOREIGN KEY (`bodega_id`) REFERENCES `bodegas` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `ventas_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `ventas_cotizacion_id_foreign` FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizaciones` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ventas_remision_id_foreign` FOREIGN KEY (`remision_id`) REFERENCES `remisiones` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ventas_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ventas`
--

LOCK TABLES `ventas` WRITE;
/*!40000 ALTER TABLE `ventas` DISABLE KEYS */;
/*!40000 ALTER TABLE `ventas` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-18 23:10:09
