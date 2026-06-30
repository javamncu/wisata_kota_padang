
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
DROP TABLE IF EXISTS `article_destination`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `article_destination` (
  `article_id` bigint unsigned NOT NULL,
  `destination_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`article_id`,`destination_id`),
  KEY `article_destination_destination_id_foreign` (`destination_id`),
  CONSTRAINT `article_destination_article_id_foreign` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `article_destination_destination_id_foreign` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `article_destination` WRITE;
/*!40000 ALTER TABLE `article_destination` DISABLE KEYS */;
INSERT INTO `article_destination` VALUES (1,1),(1,2),(1,4),(1,6),(3,13),(3,14),(3,16),(2,18),(2,19),(2,20),(2,27);
/*!40000 ALTER TABLE `article_destination` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `articles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `excerpt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `cover_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_id` bigint unsigned DEFAULT NULL,
  `status` enum('draft','published') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `articles_slug_unique` (`slug`),
  KEY `articles_author_id_foreign` (`author_id`),
  KEY `articles_status_index` (`status`),
  CONSTRAINT `articles_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `articles` WRITE;
/*!40000 ALTER TABLE `articles` DISABLE KEYS */;
INSERT INTO `articles` VALUES (1,'5 Pantai Terbaik di Padang untuk Berburu Sunset','5-pantai-terbaik-di-padang-untuk-berburu-sunset','Dari Taplau yang ikonik sampai Pulau Pasumpahan yang jernih — ini deretan pantai wajib kunjung di Padang.','<p>Kota Padang dianugerahi garis pantai yang memesona. Berikut lima pantai favorit untuk menikmati matahari terbenam.</p><h1>Pantai Padang (Taplau)</h1><p>Pantai di tepi pusat kota, ramai di sore hari dengan aneka jajanan khas Minang.</p><h1>Pantai Air Manis</h1><p>Berlatar legenda Malin Kundang, cocok untuk keluarga.</p><h1>Pulau Pasumpahan</h1><p>Dijuluki \"Maldives-nya Padang\", surga snorkeling.</p><p>Selamat berlibur dan jangan lupa jaga kebersihan pantai!</p>','images/blog/pantai_terbaik.png',1,'published','2026-06-29 07:38:19','5 Pantai Terbaik di Padang','Rekomendasi pantai terbaik di Kota Padang untuk menikmati sunset dan bermain air.','2026-06-28 11:15:50','2026-06-29 07:38:19'),(2,'Kuliner Wajib Coba Saat Berkunjung ke Padang','kuliner-wajib-coba-saat-berkunjung-ke-padang','Rendang hanyalah awal. Jelajahi sate Padang, soto, hingga es durian legendaris kota ini.','<p>Padang adalah surga kuliner. Saat berkunjung, sempatkan mencicipi sajian berikut.</p><ul><li>Sate Padang dengan kuah kuning berempah</li><li>Soto Padang yang gurih</li><li>Es durian untuk penutup yang segar</li></ul><p>Selamat berwisata kuliner!</p>','images/blog/kuliner_wajib.png',1,'published','2026-06-29 07:38:19','Kuliner Wajib di Padang','Daftar kuliner khas Padang yang wajib dicoba wisatawan.','2026-06-28 11:15:50','2026-06-29 07:38:19'),(3,'Menyusuri Wisata Religi & Sejarah Kota Padang','menyusuri-wisata-religi-sejarah-kota-padang','Masjid Raya yang ikonik, Kota Tua, hingga Museum Adityawarman — jejak budaya Minangkabau.','<p>Selain alam dan kuliner, Padang kaya akan situs religi dan sejarah.</p><h1>Masjid Raya Sumatera Barat</h1><p>Berarsitektur gonjong khas Minangkabau tanpa kubah.</p><h1>Museum Adityawarman</h1><p>Menyimpan ribuan koleksi budaya Minangkabau.</p>','images/blog/wisata_kuliner.png',1,'published','2026-06-29 07:38:19','Wisata Religi & Sejarah Padang','Panduan wisata religi dan sejarah di Kota Padang.','2026-06-28 11:15:50','2026-06-29 07:38:19');
/*!40000 ALTER TABLE `articles` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('laravel-cache-app.settings','a:14:{s:9:\"site_name\";s:18:\"Wisata Kota Padang\";s:13:\"contact_email\";s:23:\"info@wisatapadang.my.id\";s:13:\"contact_phone\";s:0:\"\";s:16:\"social_instagram\";s:17:\"@wisatakotapadang\";s:10:\"hero_title\";s:30:\"Jelajahi keindahan Kota Padang\";s:13:\"hero_subtitle\";s:87:\"Temukan tempat wisata, kuliner, dan budaya terbaik — disesuaikan dengan preferensimu.\";s:10:\"about_text\";s:61:\"Satu pintu informasi wisata, kuliner, dan budaya Kota Padang.\";s:14:\"featured_slugs\";a:6:{i:0;s:22:\"air-terjun-lubuk-hitam\";i:1;s:24:\"pantai-caroline-carolina\";i:2;s:17:\"pasar-raya-padang\";i:3;s:22:\"es-durian-iko-gantinyo\";i:4;s:21:\"jembatan-siti-nurbaya\";i:5;s:23:\"katupek-pitalah-purus-3\";}s:13:\"budget_gratis\";s:26:\"Tidak dipungut biaya masuk\";s:12:\"budget_murah\";s:27:\"Di bawah Rp50.000 per orang\";s:13:\"budget_sedang\";s:38:\"Sekitar Rp50.000 - Rp150.000 per orang\";s:14:\"budget_premium\";s:27:\"Di atas Rp150.000 per orang\";s:8:\"per_page\";i:12;s:12:\"default_sort\";s:7:\"populer\";}',2098031317),('laravel-cache-concierge:127.0.0.1','i:8;',1782759772),('laravel-cache-concierge:127.0.0.1:timer','i:1782759772;',1782759772),('laravel-cache-concierge:models','a:28:{i:0;s:16:\"gemini-2.5-flash\";i:1;s:14:\"gemini-2.5-pro\";i:2;s:16:\"gemini-2.0-flash\";i:3;s:20:\"gemini-2.0-flash-001\";i:4;s:25:\"gemini-2.0-flash-lite-001\";i:5;s:21:\"gemini-2.0-flash-lite\";i:6;s:28:\"gemini-2.5-flash-preview-tts\";i:7;s:26:\"gemini-2.5-pro-preview-tts\";i:8;s:19:\"gemini-flash-latest\";i:9;s:24:\"gemini-flash-lite-latest\";i:10;s:17:\"gemini-pro-latest\";i:11;s:21:\"gemini-2.5-flash-lite\";i:12;s:22:\"gemini-2.5-flash-image\";i:13;s:20:\"gemini-3-pro-preview\";i:14;s:22:\"gemini-3-flash-preview\";i:15;s:22:\"gemini-3.1-pro-preview\";i:16;s:34:\"gemini-3.1-pro-preview-customtools\";i:17;s:29:\"gemini-3.1-flash-lite-preview\";i:18;s:21:\"gemini-3.1-flash-lite\";i:19;s:26:\"gemini-3-pro-image-preview\";i:20;s:18:\"gemini-3-pro-image\";i:21;s:30:\"gemini-3.1-flash-image-preview\";i:22;s:22:\"gemini-3.1-flash-image\";i:23;s:16:\"gemini-3.5-flash\";i:24;s:28:\"gemini-3.1-flash-tts-preview\";i:25;s:30:\"gemini-robotics-er-1.5-preview\";i:26;s:30:\"gemini-robotics-er-1.6-preview\";i:27;s:39:\"gemini-2.5-computer-use-preview-10-2025\";}',1782745415),('laravel-cache-concierge:usage:gemini-2.0-flash-lite:2026-06-29','i:1;',1782777600),('laravel-cache-concierge:usage:gemini-2.0-flash:2026-06-29','i:2;',1782777600),('laravel-cache-concierge:usage:gemini-2.5-flash-lite:2026-06-29','i:10;',1782777600),('laravel-cache-concierge:usage:gemini-2.5-pro:2026-06-29','i:1;',1782777600);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`),
  KEY `categories_is_active_index` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Wisata Alam','wisata-alam','Pantai, perbukitan, dan keindahan alam Kota Padang.','mountain',1,'2026-06-28 09:13:11','2026-06-28 09:13:11',NULL),(2,'Wisata Sejarah & Budaya','wisata-sejarah-budaya','Bangunan tua, museum, dan jejak sejarah kota.','landmark',1,'2026-06-28 09:13:11','2026-06-28 09:13:11',NULL),(3,'Wisata Religi','wisata-religi','Masjid dan tempat ibadah bersejarah.','mosque',1,'2026-06-28 09:13:11','2026-06-28 09:13:11',NULL),(4,'Kuliner','kuliner','Sajian khas Minang dan kuliner legendaris Padang.','utensils',1,'2026-06-28 09:13:11','2026-06-28 09:13:11',NULL),(5,'Belanja & Oleh-oleh','belanja-oleh-oleh','Pusat oleh-oleh dan pasar tradisional.','shopping-bag',1,'2026-06-28 09:13:11','2026-06-28 09:13:11',NULL),(6,'Rekreasi & Hiburan','rekreasi-hiburan','Tempat bersantai dan hiburan keluarga.','ferris-wheel',1,'2026-06-28 09:13:11','2026-06-28 09:13:11',NULL);
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `destination_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `destination_images` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `destination_id` bigint unsigned NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `destination_images_destination_id_foreign` (`destination_id`),
  KEY `destination_images_sort_order_index` (`sort_order`),
  CONSTRAINT `destination_images_destination_id_foreign` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `destination_images` WRITE;
/*!40000 ALTER TABLE `destination_images` DISABLE KEYS */;
INSERT INTO `destination_images` VALUES (42,8,'images/destinations/air-terjun-lubuk-hitam.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(43,10,'images/destinations/bukit-nobita.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(44,22,'images/destinations/christine-hakim.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(45,20,'images/destinations/es-durian-iko-gantinyo.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(46,12,'images/destinations/jembatan-siti-nurbaya.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(47,31,'images/destinations/katupek-pitalah-purus-3.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(48,38,'images/destinations/kelenteng-see-hin-kiong.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(49,23,'images/destinations/keripik-balado-mahkota-shirley.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(50,14,'images/destinations/kota-tua-padang-kampung-pondok-kampung-cina.webp',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(51,40,'images/destinations/lapangan-imam-bonjol.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(52,9,'images/destinations/lubuk-paraku.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(53,21,'images/destinations/martabak-kubang-hayuda.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(54,35,'images/destinations/masjid-agung-nurul-iman.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(55,17,'images/destinations/masjid-al-hakim.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(56,16,'images/destinations/masjid-raya-sumatera-barat.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(57,36,'images/destinations/miniatur-makkah.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(58,13,'images/destinations/museum-adityawarman.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(59,2,'images/destinations/pantai-air-manis.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(60,3,'images/destinations/pantai-caroline-carolina.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(61,4,'images/destinations/pantai-nirwana.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(62,1,'images/destinations/pantai-padang-taplau.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(63,5,'images/destinations/pantai-pasir-jambak.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(64,24,'images/destinations/pasar-raya-padang.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(65,39,'images/destinations/plaza-andalas.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(66,32,'images/destinations/pondok-ikan-bakar-khatib-sulaiman.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(67,6,'images/destinations/pulau-pasumpahan.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(68,7,'images/destinations/pulau-sikuai.webp',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(69,41,'images/destinations/pulau-sirandah.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(70,33,'images/destinations/pusat-oleh-oleh-ummi-aufa-hakim.png',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(71,18,'images/destinations/rm-lamun-ombak.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(72,29,'images/destinations/rm-pagi-sore.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(73,28,'images/destinations/rm-sederhana.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(74,19,'images/destinations/sate-manang-kabau.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(75,34,'images/destinations/silungkang-art-centre.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(76,11,'images/destinations/sitinjau-lauik.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(77,26,'images/destinations/skywalk-pantai-air-manis.webp',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(78,27,'images/destinations/soto-garuda.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(79,37,'images/destinations/taman-budaya-sumatera-barat.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(80,15,'images/destinations/taman-muaro-lasak-monumen-merpati-perdamaian.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(81,25,'images/destinations/taman-siti-nurbaya.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19'),(82,30,'images/destinations/warung-kopi-nan-yo.jpg',0,'2026-06-28 10:25:19','2026-06-28 10:25:19');
/*!40000 ALTER TABLE `destination_images` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `destination_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `destination_tag` (
  `destination_id` bigint unsigned NOT NULL,
  `tag_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`destination_id`,`tag_id`),
  KEY `destination_tag_tag_id_foreign` (`tag_id`),
  CONSTRAINT `destination_tag_destination_id_foreign` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `destination_tag_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `destination_tag` WRITE;
/*!40000 ALTER TABLE `destination_tag` DISABLE KEYS */;
INSERT INTO `destination_tag` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(7,1),(9,1),(15,1),(20,1),(21,1),(25,1),(27,1),(30,1),(31,1),(32,1),(40,1),(1,2),(12,2),(14,2),(18,2),(19,2),(21,2),(22,2),(23,2),(24,2),(28,2),(29,2),(31,2),(33,2),(39,2),(40,2),(4,3),(7,3),(12,3),(17,3),(25,3),(1,4),(2,4),(4,4),(6,4),(7,4),(10,4),(11,4),(12,4),(13,4),(14,4),(15,4),(16,4),(17,4),(26,4),(36,4),(38,4),(41,4),(3,5),(5,5),(6,5),(8,5),(9,5),(10,5),(25,5),(41,5),(2,6),(12,6),(13,6),(14,6),(16,6),(30,6),(34,6),(35,6),(37,6),(38,6),(6,7),(8,7),(10,7),(11,7),(26,7),(41,7),(1,8),(2,8),(3,8),(4,8),(5,8),(6,8),(7,8),(8,8),(9,8),(10,8),(11,8),(12,8),(13,8),(14,8),(15,8),(16,8),(17,8),(25,8),(26,8),(35,8),(36,8),(37,8),(38,8),(40,8),(41,8),(3,9),(5,9),(6,9),(7,9),(8,9),(9,9),(41,9),(8,10),(10,10),(2,11),(5,11),(13,11),(14,11),(16,11),(33,11),(34,11),(36,11),(37,11),(38,11),(1,12),(2,12),(3,12),(4,12),(6,12),(7,12),(9,12),(12,12),(15,12),(25,12),(40,12),(41,12),(16,13),(17,13),(35,13),(36,13),(38,13),(22,14),(23,14),(24,14),(33,14),(34,14),(39,14),(1,15),(11,15),(12,15),(14,15),(18,15),(19,15),(20,15),(21,15),(24,15),(27,15),(28,15),(29,15),(30,15),(31,15),(32,15),(39,15),(1,16),(2,16),(3,16),(4,16),(5,16),(8,16),(9,16),(10,16),(11,16),(12,16),(13,16),(14,16),(15,16),(16,16),(17,16),(18,16),(19,16),(20,16),(21,16),(22,16),(23,16),(24,16),(25,16),(26,16),(27,16),(28,16),(29,16),(30,16),(31,16),(32,16),(33,16),(34,16),(35,16),(36,16),(37,16),(38,16),(39,16),(40,16),(1,17),(2,17),(3,17),(5,17),(6,17),(7,17),(13,17),(15,17),(16,17),(17,17),(18,17),(19,17),(21,17),(22,17),(24,17),(25,17),(28,17),(29,17),(32,17),(35,17),(36,17),(37,17),(39,17),(41,17),(1,18),(13,18),(16,18),(17,18),(18,18),(19,18),(28,18),(29,18),(35,18),(39,19),(13,21),(18,21),(39,21),(13,22),(16,22),(35,22),(39,22);
/*!40000 ALTER TABLE `destination_tag` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `destinations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `destinations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description_short` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description_long` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `opening_hours` json DEFAULT NULL,
  `price_info` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_instagram` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price_range` enum('gratis','murah','sedang','premium') COLLATE utf8mb4_unicode_ci NOT NULL,
  `zone` enum('pusat_kota','pesisir','selatan','kepulauan','perbukitan') COLLATE utf8mb4_unicode_ci NOT NULL,
  `indoor_outdoor` enum('indoor','outdoor','campuran') COLLATE utf8mb4_unicode_ci NOT NULL,
  `duration` enum('singkat','sedang','lama') COLLATE utf8mb4_unicode_ci NOT NULL,
  `cocok_untuk` json DEFAULT NULL,
  `waktu_ideal` json DEFAULT NULL,
  `status` enum('draft','aktif') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `rating_cache` decimal(3,2) DEFAULT NULL,
  `review_count_cache` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `destinations_slug_unique` (`slug`),
  KEY `destinations_category_id_foreign` (`category_id`),
  KEY `destinations_price_range_index` (`price_range`),
  KEY `destinations_zone_index` (`zone`),
  KEY `destinations_indoor_outdoor_index` (`indoor_outdoor`),
  KEY `destinations_duration_index` (`duration`),
  KEY `destinations_status_index` (`status`),
  CONSTRAINT `destinations_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `destinations` WRITE;
/*!40000 ALTER TABLE `destinations` DISABLE KEYS */;
INSERT INTO `destinations` VALUES (1,1,'Pantai Padang (Taplau)','pantai-padang-taplau','Pantai ikonik di tepi pusat kota (Tepi Lauik), terkenal untuk menikmati sunset sambil mencicipi jajanan khas Minang.','Pantai ikonik di tepi pusat kota (Tepi Lauik), terkenal untuk menikmati sunset sambil mencicipi jajanan khas Minang.','Kota Padang, Sumatera Barat',-0.9606000,100.3539000,NULL,NULL,NULL,NULL,NULL,'gratis','pesisir','outdoor','sedang','[\"keluarga\", \"pasangan\", \"rombongan\"]','[\"sore\", \"malam\"]','aktif',NULL,0,'2026-06-28 09:13:11','2026-06-28 09:13:11',NULL),(2,1,'Pantai Air Manis','pantai-air-manis','Pantai berlatar legenda Malin Kundang dengan Batu Malin Kundang yang melegenda, sekitar 10 km dari pusat kota.','Pantai berlatar legenda Malin Kundang dengan Batu Malin Kundang yang melegenda, sekitar 10 km dari pusat kota.','Kota Padang, Sumatera Barat',-1.0086110,100.3555560,NULL,NULL,NULL,NULL,NULL,'murah','selatan','outdoor','sedang','[\"keluarga\", \"pasangan\", \"rombongan\"]','[\"pagi\", \"sore\"]','aktif',NULL,0,'2026-06-28 09:13:11','2026-06-28 09:13:11',NULL),(3,1,'Pantai Caroline (Carolina)','pantai-caroline-carolina','Pantai berpasir putih di Teluk Bungus dengan air jernih, cocok untuk berenang dan menyeberang ke pulau-pulau kecil.','Pantai berpasir putih di Teluk Bungus dengan air jernih, cocok untuk berenang dan menyeberang ke pulau-pulau kecil.','Kota Padang, Sumatera Barat',-1.0253570,100.4134900,NULL,NULL,NULL,NULL,NULL,'murah','selatan','outdoor','lama','[\"keluarga\", \"rombongan\"]','[\"pagi\", \"siang\"]','aktif',NULL,0,'2026-06-28 09:13:11','2026-06-28 10:45:25',NULL),(4,1,'Pantai Nirwana','pantai-nirwana','Pantai tenang dengan air biru dan ombak landai, salah satu spot sunset favorit di selatan Padang.','Pantai tenang dengan air biru dan ombak landai, salah satu spot sunset favorit di selatan Padang.','Kota Padang, Sumatera Barat',-0.9959600,100.3957810,NULL,NULL,NULL,NULL,NULL,'murah','selatan','outdoor','sedang','[\"pasangan\", \"keluarga\"]','[\"sore\"]','aktif',NULL,0,'2026-06-28 09:13:11','2026-06-28 10:45:25',NULL),(5,1,'Pantai Pasir Jambak','pantai-pasir-jambak','Pantai berpasir putih luas dengan area konservasi penyu, cocok untuk piknik keluarga dan wisata edukasi.','Pantai berpasir putih luas dengan area konservasi penyu, cocok untuk piknik keluarga dan wisata edukasi.','Kota Padang, Sumatera Barat',-0.8351580,100.3168280,NULL,NULL,NULL,NULL,NULL,'murah','pesisir','outdoor','lama','[\"keluarga\", \"rombongan\"]','[\"pagi\", \"sore\"]','aktif',NULL,0,'2026-06-28 09:13:11','2026-06-28 10:45:25',NULL),(6,1,'Pulau Pasumpahan','pulau-pasumpahan','Pulau berpasir putih dijuluki \'Maldives-nya Padang\', spot snorkeling jernih, diakses naik boat dari Bungus.','Pulau berpasir putih dijuluki \'Maldives-nya Padang\', spot snorkeling jernih, diakses naik boat dari Bungus.','Kota Padang, Sumatera Barat',-1.0833000,100.3667000,NULL,NULL,NULL,NULL,NULL,'sedang','kepulauan','outdoor','lama','[\"rombongan\", \"pasangan\", \"solo\"]','[\"pagi\", \"siang\"]','aktif',NULL,0,'2026-06-28 09:13:11','2026-06-28 09:13:11',NULL),(7,1,'Pulau Sikuai','pulau-sikuai','Pulau tropis dengan air bening dan pasir putih, cocok untuk snorkeling, diving, atau bersantai.','Pulau tropis dengan air bening dan pasir putih, cocok untuk snorkeling, diving, atau bersantai.','Kota Padang, Sumatera Barat',-1.0315750,100.3533420,NULL,NULL,NULL,NULL,NULL,'sedang','kepulauan','outdoor','lama','[\"pasangan\", \"rombongan\"]','[\"pagi\", \"siang\"]','aktif',NULL,0,'2026-06-28 09:13:11','2026-06-28 10:45:25',NULL),(8,1,'Air Terjun Lubuk Hitam','air-terjun-lubuk-hitam','Air terjun bertingkat dengan bukit hijau di kawasan Bungus, segar untuk bermain air dan trekking ringan.','Air terjun bertingkat dengan bukit hijau di kawasan Bungus, segar untuk bermain air dan trekking ringan.','Kota Padang, Sumatera Barat',-1.0425020,100.4137280,NULL,NULL,NULL,NULL,NULL,'murah','selatan','outdoor','sedang','[\"rombongan\", \"solo\"]','[\"pagi\", \"siang\"]','aktif',NULL,0,'2026-06-28 09:13:11','2026-06-28 10:45:25',NULL),(9,1,'Lubuk Paraku','lubuk-paraku','Pemandian alam berair jernih dan rindang di Lubuk Kilangan, favorit untuk piknik dan berenang.','Pemandian alam berair jernih dan rindang di Lubuk Kilangan, favorit untuk piknik dan berenang.','Kota Padang, Sumatera Barat',-0.9427010,100.4682340,NULL,NULL,NULL,NULL,NULL,'murah','perbukitan','outdoor','sedang','[\"keluarga\", \"rombongan\"]','[\"pagi\", \"siang\"]','aktif',NULL,0,'2026-06-28 09:13:11','2026-06-28 10:45:25',NULL),(10,1,'Bukit Nobita','bukit-nobita','Bukit di Lubuk Begalung dengan panorama kota dari ketinggian, populer untuk berburu sunrise dan kabut pagi.','Bukit di Lubuk Begalung dengan panorama kota dari ketinggian, populer untuk berburu sunrise dan kabut pagi.','Kota Padang, Sumatera Barat',-0.9634730,100.4163900,NULL,NULL,NULL,NULL,NULL,'murah','perbukitan','outdoor','sedang','[\"solo\", \"pasangan\", \"rombongan\"]','[\"pagi\"]','aktif',NULL,0,'2026-06-28 09:13:11','2026-06-28 10:45:25',NULL),(11,1,'Sitinjau Lauik','sitinjau-lauik','Spot panorama jalan berkelok dan perbukitan hijau di jalur Padang-Solok, dikelilingi kafe kekinian.','Spot panorama jalan berkelok dan perbukitan hijau di jalur Padang-Solok, dikelilingi kafe kekinian.','Kota Padang, Sumatera Barat',-0.9463530,100.4608350,NULL,NULL,NULL,NULL,NULL,'gratis','perbukitan','outdoor','singkat','[\"solo\", \"pasangan\", \"rombongan\"]','[\"pagi\", \"sore\"]','aktif',NULL,0,'2026-06-28 09:13:11','2026-06-28 10:45:25',NULL),(12,2,'Jembatan Siti Nurbaya','jembatan-siti-nurbaya','Landmark kota terkait novel Siti Nurbaya; ramai di sore-malam dengan pedagang kuliner dan pemandangan Batang Arau.','Landmark kota terkait novel Siti Nurbaya; ramai di sore-malam dengan pedagang kuliner dan pemandangan Batang Arau.','Kota Padang, Sumatera Barat',-0.9499000,100.3618000,NULL,NULL,NULL,NULL,NULL,'gratis','pusat_kota','outdoor','singkat','[\"pasangan\", \"keluarga\", \"solo\"]','[\"sore\", \"malam\"]','aktif',NULL,0,'2026-06-28 09:13:11','2026-06-28 09:13:11',NULL),(13,2,'Museum Adityawarman','museum-adityawarman','Museum budaya Minangkabau berbentuk Rumah Gadang dengan ribuan koleksi sejarah, pakaian adat, dan senjata tradisional.','Museum budaya Minangkabau berbentuk Rumah Gadang dengan ribuan koleksi sejarah, pakaian adat, dan senjata tradisional.','Kota Padang, Sumatera Barat',-0.9333000,100.3556000,NULL,NULL,NULL,NULL,NULL,'murah','pusat_kota','indoor','sedang','[\"keluarga\", \"lansia\", \"rombongan\", \"ramah_difabel\"]','[\"pagi\", \"siang\"]','aktif',NULL,0,'2026-06-28 09:13:11','2026-06-28 09:13:11',NULL),(14,2,'Kota Tua Padang (Kampung Pondok / Kampung Cina)','kota-tua-padang-kampung-pondok-kampung-cina','Kawasan bangunan kolonial dan pecinan; saat malam dipenuhi lampion dan kuliner khas Tionghoa-Minang.','Kawasan bangunan kolonial dan pecinan; saat malam dipenuhi lampion dan kuliner khas Tionghoa-Minang.','Kota Padang, Sumatera Barat',-0.9616330,100.3649430,NULL,NULL,NULL,NULL,NULL,'gratis','pusat_kota','outdoor','sedang','[\"solo\", \"pasangan\", \"rombongan\"]','[\"sore\", \"malam\"]','aktif',NULL,0,'2026-06-28 09:13:11','2026-06-28 10:45:25',NULL),(15,2,'Taman Muaro Lasak & Monumen Merpati Perdamaian','taman-muaro-lasak-monumen-merpati-perdamaian','Taman tepi pantai dengan monumen origami merpati setinggi 8 meter, landmark foto di kawasan Pantai Padang.','Taman tepi pantai dengan monumen origami merpati setinggi 8 meter, landmark foto di kawasan Pantai Padang.','Kota Padang, Sumatera Barat',-0.9329710,100.3512210,NULL,NULL,NULL,NULL,NULL,'gratis','pesisir','outdoor','singkat','[\"keluarga\", \"pasangan\"]','[\"pagi\", \"sore\"]','aktif',NULL,0,'2026-06-28 09:13:12','2026-06-28 10:45:25',NULL),(16,3,'Masjid Raya Sumatera Barat','masjid-raya-sumatera-barat','Masjid ikonik tanpa kubah dengan arsitektur atap gonjong Minangkabau; pusat wisata religi kebanggaan Sumbar.','Masjid ikonik tanpa kubah dengan arsitektur atap gonjong Minangkabau; pusat wisata religi kebanggaan Sumbar.','Kota Padang, Sumatera Barat',-0.9243000,100.3601000,NULL,NULL,NULL,NULL,NULL,'gratis','pusat_kota','campuran','sedang','[\"keluarga\", \"lansia\", \"rombongan\", \"ramah_difabel\"]','[\"pagi\", \"siang\", \"sore\"]','aktif',NULL,0,'2026-06-28 09:13:12','2026-06-28 09:13:12',NULL),(17,3,'Masjid Al Hakim','masjid-al-hakim','Masjid putih bergaya megah di tepi Pantai Padang yang sering disebut mirip Taj Mahal, indah saat sore.','Masjid putih bergaya megah di tepi Pantai Padang yang sering disebut mirip Taj Mahal, indah saat sore.','Kota Padang, Sumatera Barat',-0.9470000,100.3530000,NULL,NULL,NULL,NULL,NULL,'gratis','pesisir','campuran','singkat','[\"keluarga\", \"pasangan\"]','[\"pagi\", \"sore\"]','aktif',NULL,0,'2026-06-28 09:13:12','2026-06-28 09:13:12',NULL),(18,4,'RM Lamun Ombak','rm-lamun-ombak','Rumah makan Padang populer dengan aneka gulai dan masakan Minang otentik, cocok untuk makan keluarga.','Rumah makan Padang populer dengan aneka gulai dan masakan Minang otentik, cocok untuk makan keluarga.','Kota Padang, Sumatera Barat',-0.9130000,100.3570000,NULL,NULL,NULL,NULL,NULL,'sedang','pusat_kota','indoor','sedang','[\"keluarga\", \"rombongan\", \"lansia\"]','[\"siang\", \"malam\"]','aktif',NULL,0,'2026-06-28 09:13:12','2026-06-28 09:13:12',NULL),(19,4,'Sate Manang Kabau','sate-manang-kabau','Sate Padang legendaris di Jl. Khatib Sulaiman dengan tiga varian kuah; tersedia ruang VIP dan area taman.','Sate Padang legendaris di Jl. Khatib Sulaiman dengan tiga varian kuah; tersedia ruang VIP dan area taman.','Kota Padang, Sumatera Barat',-0.9262980,100.3582450,NULL,NULL,NULL,NULL,NULL,'sedang','pusat_kota','indoor','singkat','[\"keluarga\", \"solo\", \"rombongan\"]','[\"siang\", \"malam\"]','aktif',NULL,0,'2026-06-28 09:13:12','2026-06-28 10:45:25',NULL),(20,4,'Es Durian Iko Gantinyo','es-durian-iko-gantinyo','Kedai es durian legendaris Padang dengan topping melimpah, segar dinikmati siang atau malam.','Kedai es durian legendaris Padang dengan topping melimpah, segar dinikmati siang atau malam.','Kota Padang, Sumatera Barat',-0.9587390,100.3614210,NULL,NULL,NULL,NULL,NULL,'murah','pusat_kota','indoor','singkat','[\"pasangan\", \"solo\", \"keluarga\"]','[\"siang\", \"malam\"]','aktif',NULL,0,'2026-06-28 09:13:12','2026-06-28 10:45:25',NULL),(21,4,'Martabak Kubang Hayuda','martabak-kubang-hayuda','Martabak telur khas Kubang berisi daging tebal dengan kuah cuka, ikon kuliner malam di kawasan Ulak Karang.','Martabak telur khas Kubang berisi daging tebal dengan kuah cuka, ikon kuliner malam di kawasan Ulak Karang.','Kota Padang, Sumatera Barat',-0.9509420,100.3639200,NULL,NULL,NULL,NULL,NULL,'murah','pusat_kota','indoor','singkat','[\"keluarga\", \"solo\", \"rombongan\"]','[\"malam\"]','aktif',NULL,0,'2026-06-28 09:13:12','2026-06-28 10:45:25',NULL),(22,5,'Christine Hakim','christine-hakim','Pusat oleh-oleh khas Padang terkenal: keripik balado, rendang kemasan, dan aneka camilan Minang.','Pusat oleh-oleh khas Padang terkenal: keripik balado, rendang kemasan, dan aneka camilan Minang.','Kota Padang, Sumatera Barat',-0.9480000,100.3580000,NULL,NULL,NULL,NULL,NULL,'sedang','pusat_kota','indoor','singkat','[\"keluarga\", \"rombongan\"]','[\"pagi\", \"siang\", \"sore\"]','aktif',NULL,0,'2026-06-28 09:13:12','2026-06-28 09:13:12',NULL),(23,5,'Keripik Balado Mahkota / Shirley','keripik-balado-mahkota-shirley','Toko oleh-oleh keripik balado dan sanjai, pilihan klasik untuk buah tangan khas Padang.','Toko oleh-oleh keripik balado dan sanjai, pilihan klasik untuk buah tangan khas Padang.','Kota Padang, Sumatera Barat',-0.9547390,100.3546280,NULL,NULL,NULL,NULL,NULL,'sedang','pusat_kota','indoor','singkat','[\"rombongan\", \"keluarga\"]','[\"pagi\", \"siang\"]','aktif',NULL,0,'2026-06-28 09:13:12','2026-06-28 10:45:25',NULL),(24,5,'Pasar Raya Padang','pasar-raya-padang','Pasar tradisional terbesar di pusat kota untuk berburu oleh-oleh, kain, kuliner, dan kebutuhan harian.','Pasar tradisional terbesar di pusat kota untuk berburu oleh-oleh, kain, kuliner, dan kebutuhan harian.','Kota Padang, Sumatera Barat',-0.9521190,100.3644210,NULL,NULL,NULL,NULL,NULL,'murah','pusat_kota','campuran','sedang','[\"rombongan\", \"solo\"]','[\"pagi\", \"siang\"]','aktif',NULL,0,'2026-06-28 09:13:12','2026-06-28 10:45:25',NULL),(25,6,'Taman Siti Nurbaya','taman-siti-nurbaya','Taman di perbukitan dekat pusat kota dengan pemandangan teluk, cocok untuk jogging, bersantai, dan berfoto.','Taman di perbukitan dekat pusat kota dengan pemandangan teluk, cocok untuk jogging, bersantai, dan berfoto.','Kota Padang, Sumatera Barat',-0.9632340,100.3508420,NULL,NULL,NULL,NULL,NULL,'murah','pusat_kota','outdoor','sedang','[\"keluarga\", \"pasangan\", \"rombongan\"]','[\"pagi\", \"sore\"]','aktif',NULL,0,'2026-06-28 09:13:12','2026-06-28 10:45:25',NULL),(26,6,'Skywalk Pantai Air Manis','skywalk-pantai-air-manis','Jembatan kaca terbaru di kawasan Pantai Air Manis dengan pemandangan laut yang instagramable.','Jembatan kaca terbaru di kawasan Pantai Air Manis dengan pemandangan laut yang instagramable.','Kota Padang, Sumatera Barat',-0.9705120,100.3671210,NULL,NULL,NULL,NULL,NULL,'murah','selatan','outdoor','singkat','[\"pasangan\", \"rombongan\", \"keluarga\"]','[\"pagi\", \"sore\"]','aktif',NULL,0,'2026-06-28 09:13:12','2026-06-28 10:45:25',NULL),(27,4,'Soto Garuda','soto-garuda','Kedai soto Padang legendaris dengan kuah gurih dan perkedel, favorit untuk sarapan atau makan siang.','Kedai soto Padang legendaris dengan kuah gurih dan perkedel, favorit untuk sarapan atau makan siang.','Kota Padang, Sumatera Barat',-0.9169420,100.3564280,NULL,NULL,NULL,NULL,NULL,'murah','pusat_kota','indoor','singkat','[\"keluarga\", \"solo\", \"rombongan\"]','[\"pagi\", \"siang\"]','aktif',NULL,0,'2026-06-28 09:13:12','2026-06-28 10:45:25',NULL),(28,4,'RM Sederhana','rm-sederhana','Rumah makan Padang ikonik yang berawal dari Kota Padang, menyajikan rendang dan aneka gulai khas Minang.','Rumah makan Padang ikonik yang berawal dari Kota Padang, menyajikan rendang dan aneka gulai khas Minang.','Kota Padang, Sumatera Barat',-0.9419150,100.3615120,NULL,NULL,NULL,NULL,NULL,'sedang','pusat_kota','indoor','sedang','[\"keluarga\", \"rombongan\", \"lansia\"]','[\"siang\", \"malam\"]','aktif',NULL,0,'2026-06-28 09:13:12','2026-06-28 10:45:25',NULL),(29,4,'RM Pagi Sore','rm-pagi-sore','Rumah makan Padang ternama dengan cita rasa rendang dan gulai yang melegenda di kalangan warga kota.','Rumah makan Padang ternama dengan cita rasa rendang dan gulai yang melegenda di kalangan warga kota.','Kota Padang, Sumatera Barat',-0.9525410,100.3585140,NULL,NULL,NULL,NULL,NULL,'sedang','pusat_kota','indoor','sedang','[\"keluarga\", \"rombongan\"]','[\"siang\", \"malam\"]','aktif',NULL,0,'2026-06-28 09:13:12','2026-06-28 10:45:25',NULL),(30,4,'Warung Kopi Nan Yo','warung-kopi-nan-yo','Warung kopi legendaris di kawasan Pondok dengan kopi khas Minang dan sate Padang, ramai sejak pagi.','Warung kopi legendaris di kawasan Pondok dengan kopi khas Minang dan sate Padang, ramai sejak pagi.','Kota Padang, Sumatera Barat',-0.9598280,100.3611280,NULL,NULL,NULL,NULL,NULL,'murah','pusat_kota','campuran','singkat','[\"solo\", \"keluarga\", \"rombongan\"]','[\"pagi\", \"malam\"]','aktif',NULL,0,'2026-06-28 09:13:12','2026-06-28 10:45:25',NULL),(31,4,'Katupek Pitalah Purus 3','katupek-pitalah-purus-3','Kedai sarapan legendaris di Jl. Purus III dekat Pantai Padang: katupek pitalah, lontong pical, dan teh talua.','Kedai sarapan legendaris di Jl. Purus III dekat Pantai Padang: katupek pitalah, lontong pical, dan teh talua.','Kota Padang, Sumatera Barat',-0.9392230,100.3533280,NULL,NULL,NULL,NULL,NULL,'murah','pusat_kota','campuran','singkat','[\"solo\", \"keluarga\", \"rombongan\"]','[\"pagi\"]','aktif',NULL,0,'2026-06-28 09:13:12','2026-06-28 10:45:25',NULL),(32,4,'Pondok Ikan Bakar Khatib Sulaiman','pondok-ikan-bakar-khatib-sulaiman','Restoran ikan bakar dua lantai menghadap Pantai Padang di Jl. Samudera; seafood dibakar dengan batok kelapa.','Restoran ikan bakar dua lantai menghadap Pantai Padang di Jl. Samudera; seafood dibakar dengan batok kelapa.','Kota Padang, Sumatera Barat',-0.9238910,100.3571210,NULL,NULL,NULL,NULL,NULL,'sedang','pesisir','campuran','sedang','[\"keluarga\", \"rombongan\", \"pasangan\"]','[\"siang\", \"malam\"]','aktif',NULL,0,'2026-06-28 09:13:12','2026-06-28 10:45:25',NULL),(33,5,'Pusat Oleh-Oleh Ummi Aufa Hakim','pusat-oleh-oleh-ummi-aufa-hakim','Pusat oleh-oleh di Jl. Veteran dengan produksi keripik sanjai balado sendiri; pengunjung bisa melihat prosesnya.','Pusat oleh-oleh di Jl. Veteran dengan produksi keripik sanjai balado sendiri; pengunjung bisa melihat prosesnya.','Kota Padang, Sumatera Barat',-0.9392810,100.3576420,NULL,NULL,NULL,NULL,NULL,'sedang','pusat_kota','indoor','singkat','[\"keluarga\", \"rombongan\"]','[\"pagi\", \"siang\"]','aktif',NULL,0,'2026-06-28 09:13:12','2026-06-28 10:45:25',NULL),(34,5,'Silungkang Art Centre','silungkang-art-centre','Sentra songket Minangkabau dekat Pasar Raya: kain songket, batik, mukena sulam, dan aneka suvenir khas.','Sentra songket Minangkabau dekat Pasar Raya: kain songket, batik, mukena sulam, dan aneka suvenir khas.','Kota Padang, Sumatera Barat',-0.9555120,100.3641210,NULL,NULL,NULL,NULL,NULL,'premium','pusat_kota','indoor','singkat','[\"pasangan\", \"rombongan\", \"lansia\"]','[\"pagi\", \"siang\"]','aktif',NULL,0,'2026-06-28 09:13:12','2026-06-28 10:45:25',NULL),(35,3,'Masjid Agung Nurul Iman','masjid-agung-nurul-iman','Salah satu masjid besar di pusat kota Padang, tempat ibadah sekaligus singgah bagi wisatawan muslim.','Salah satu masjid besar di pusat kota Padang, tempat ibadah sekaligus singgah bagi wisatawan muslim.','Kota Padang, Sumatera Barat',-0.9567900,100.3647460,NULL,NULL,NULL,NULL,NULL,'gratis','pusat_kota','campuran','singkat','[\"keluarga\", \"lansia\", \"rombongan\"]','[\"pagi\", \"siang\", \"sore\"]','aktif',NULL,0,'2026-06-28 09:13:12','2026-06-28 10:45:25',NULL),(36,3,'Miniatur Makkah','miniatur-makkah','Replika kawasan untuk latihan manasik haji yang juga jadi spot edukasi religi dan foto bagi keluarga.','Replika kawasan untuk latihan manasik haji yang juga jadi spot edukasi religi dan foto bagi keluarga.','Kota Padang, Sumatera Barat',-0.8499210,100.3831250,NULL,NULL,NULL,NULL,NULL,'murah','pusat_kota','outdoor','singkat','[\"keluarga\", \"rombongan\"]','[\"pagi\", \"siang\"]','aktif',NULL,0,'2026-06-28 09:13:12','2026-06-28 10:45:25',NULL),(37,2,'Taman Budaya Sumatera Barat','taman-budaya-sumatera-barat','Pusat seni dan budaya yang rutin menggelar pameran, pentas tari, dan pertunjukan seni khas Sumatera Barat.','Pusat seni dan budaya yang rutin menggelar pameran, pentas tari, dan pertunjukan seni khas Sumatera Barat.','Kota Padang, Sumatera Barat',-0.9507930,100.3541780,NULL,NULL,NULL,NULL,NULL,'murah','pusat_kota','campuran','sedang','[\"keluarga\", \"rombongan\", \"solo\"]','[\"pagi\", \"siang\"]','aktif',NULL,0,'2026-06-28 09:13:12','2026-06-28 10:45:25',NULL),(38,2,'Kelenteng See Hin Kiong','kelenteng-see-hin-kiong','Kelenteng bersejarah di kawasan Pondok dengan arsitektur Tionghoa yang kaya warna dan nilai budaya.','Kelenteng bersejarah di kawasan Pondok dengan arsitektur Tionghoa yang kaya warna dan nilai budaya.','Kota Padang, Sumatera Barat',-0.9620400,100.3644250,NULL,NULL,NULL,NULL,NULL,'gratis','pusat_kota','campuran','singkat','[\"solo\", \"pasangan\", \"rombongan\"]','[\"pagi\", \"siang\"]','aktif',NULL,0,'2026-06-28 09:13:12','2026-06-28 10:45:25',NULL),(39,6,'Plaza Andalas','plaza-andalas','Pusat perbelanjaan di jantung kota dengan retail, food court, dan hiburan keluarga, nyaman saat cuaca panas/hujan.','Pusat perbelanjaan di jantung kota dengan retail, food court, dan hiburan keluarga, nyaman saat cuaca panas/hujan.','Kota Padang, Sumatera Barat',-0.9485000,100.3605000,NULL,NULL,NULL,NULL,NULL,'sedang','pusat_kota','indoor','sedang','[\"keluarga\", \"rombongan\", \"pasangan\"]','[\"siang\", \"malam\"]','aktif',NULL,0,'2026-06-28 09:13:12','2026-06-28 09:13:12',NULL),(40,6,'Lapangan Imam Bonjol','lapangan-imam-bonjol','Ruang terbuka publik di pusat kota yang ramai di akhir pekan untuk olahraga, jajan, dan kegiatan warga.','Ruang terbuka publik di pusat kota yang ramai di akhir pekan untuk olahraga, jajan, dan kegiatan warga.','Kota Padang, Sumatera Barat',-0.9455000,100.3595000,NULL,NULL,NULL,NULL,NULL,'gratis','pusat_kota','outdoor','sedang','[\"keluarga\", \"rombongan\"]','[\"pagi\", \"sore\"]','aktif',NULL,0,'2026-06-28 09:13:12','2026-06-28 09:13:12',NULL),(41,1,'Pulau Sirandah','pulau-sirandah','Pulau kecil berair jernih dekat Pulau Sikuai, cocok untuk snorkeling, camping, dan bersantai jauh dari keramaian.','Pulau kecil berair jernih dekat Pulau Sikuai, cocok untuk snorkeling, camping, dan bersantai jauh dari keramaian.','Kota Padang, Sumatera Barat',-1.0371420,100.3347590,NULL,NULL,NULL,NULL,NULL,'sedang','kepulauan','outdoor','lama','[\"pasangan\", \"rombongan\"]','[\"pagi\", \"siang\"]','aktif',NULL,0,'2026-06-28 09:13:12','2026-06-28 10:45:25',NULL);
/*!40000 ALTER TABLE `destinations` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `favorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `favorites` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `destination_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `favorites_user_id_destination_id_unique` (`user_id`,`destination_id`),
  KEY `favorites_destination_id_foreign` (`destination_id`),
  CONSTRAINT `favorites_destination_id_foreign` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `favorites_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `favorites` WRITE;
/*!40000 ALTER TABLE `favorites` DISABLE KEYS */;
/*!40000 ALTER TABLE `favorites` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_06_28_100000_create_categories_table',1),(5,'2026_06_28_100100_create_tags_table',1),(6,'2026_06_28_100200_create_destinations_table',1),(7,'2026_06_28_100300_create_destination_images_table',1),(8,'2026_06_28_100400_create_destination_tag_table',1),(9,'2026_06_28_100500_create_reviews_table',1),(10,'2026_06_28_100600_create_favorites_table',1),(11,'2026_06_28_110000_add_is_active_to_users_table',1),(12,'2026_06_29_120000_create_settings_table',2),(13,'2026_06_29_130000_create_articles_table',3),(14,'2026_06_29_130100_create_article_destination_table',3);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `destination_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `rating` tinyint unsigned NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','published') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reviews_user_id_destination_id_unique` (`user_id`,`destination_id`),
  KEY `reviews_destination_id_foreign` (`destination_id`),
  KEY `reviews_status_index` (`status`),
  CONSTRAINT `reviews_destination_id_foreign` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;
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

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('C4Tq5NeI9IdXSjSibSv7DnTiu5xKPdVjqbBd5ckp',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiVUgyVG1aamtXNmlPTXR6SFhBVlZPU2Z3bVlhYzZkNGlod3VEWENndSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9fQ==',1782744146),('Es0YxfVmR0f8LvT5cdENkWtCmkCeOY6Ujo5wMb8U',NULL,'127.0.0.1','curl/8.14.1','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUjFvYWxFeXRRbDBTem1JSGpraEJDUHVEclZjcnZXc2p4a09OcmM1TiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODEzOCI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1782743904),('iIIcyFiijWgicxiW941qW2FTP5wH2EZwP2GMae63',NULL,'127.0.0.1','curl/8.14.1','YTozOntzOjY6Il90b2tlbiI7czo0MDoiYzZMd1JLaWFlNjVwWWlpeExPZnpXTFdxR1phWlR3Q3cxTWhZVU9DaiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODEzNy9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1782742307),('KlJ3sHlOX4uFYjSjihtDCvz5696xd7BtGNcEPYTL',NULL,'127.0.0.1','curl/8.14.1','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSmtQazM1MW1NcjhaaE5DV3RDREpJWHpXOHVPUWtFVDlXRFlPZDI5ciI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzA6Imh0dHA6Ly8xMjcuMC4wLjE6ODEzNy9yZWdpc3RlciI7czo1OiJyb3V0ZSI7czo4OiJyZWdpc3RlciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1782742309),('MwCBOyPNaTU7xcBQzcn4GkEUwDuRHHf07kGVa5P2',NULL,'127.0.0.1','curl/8.14.1','YTozOntzOjY6Il90b2tlbiI7czo0MDoieXJBdnVFbG1hbDhjT2JWRGppN3JLVEVEalpJc0xGQ0lSNUtCendsbyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODEzNy9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1782742305),('oE8wt8hsEDZtzOMQhOp6RYjS5BHR9slka4oWP9en',NULL,'127.0.0.1','curl/8.14.1','YTozOntzOjY6Il90b2tlbiI7czo0MDoiV1hWZE96T0dsWnVub084UG1jOWcyYmJDbjMzc2pXUWVGYU1DOVY0OCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODEzNy9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1782742306),('PHmynAXg6FZvqa1SE7tEu5O0FJE6BpseZMmH0pJs',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZFkwYzVoWFFXeXptcWlZZENGaUt0YjBPaFVUbUhuRjdWeURISUNuNyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1782735708),('Te6YXDOJvjrQ2LWX3B0PL7X3NCLShVsYNVFAR8bf',NULL,'127.0.0.1','curl/8.14.1','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUjY4Q3lacnBFNkpjNDV4TXphOE5zRzlmTU93TnFhYTRGb0tjekNGTiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzA6Imh0dHA6Ly8xMjcuMC4wLjE6ODEzNy9yZWdpc3RlciI7czo1OiJyb3V0ZSI7czo4OiJyZWdpc3RlciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1782742308),('vqvOGg0sH1SDQYHTljjVcOislc4rmIz2RfDzl8uj',NULL,'127.0.0.1','curl/8.14.1','YTozOntzOjY6Il90b2tlbiI7czo0MDoia1NTYVBVN1BXcFl4U01URnJNZUFBMkdOcERaVXlHWWdJSlNkS1k5ZyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjY6Imh0dHA6Ly8xMjcuMC4wLjE6ODEzOC9ibG9nIjtzOjU6InJvdXRlIjtzOjEwOiJibG9nLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1782743905),('xUwdXX6WaPqzAu1MLzsPXmdepSgATimu9C8ccOHx',NULL,'127.0.0.1','curl/8.14.1','YTozOntzOjY6Il90b2tlbiI7czo0MDoieUF6R1ZOQ2pxR0hDazlneGpJS3kyOG8wMTRJd05sMWZMR2tnNGExUyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODEzNyI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1782742303),('zbUTMNyLDdr8CV8AakWmEU5z02C6Pag0EyMI3WRG',NULL,'127.0.0.1','curl/8.14.1','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRGR0NkRHU093c3R4SEVYRG5hUEVORFh1WlV2OVpLT2R4STlIWmpSTCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjY6Imh0dHA6Ly8xMjcuMC4wLjE6ODEzOC9ibG9nIjtzOjU6InJvdXRlIjtzOjEwOiJibG9nLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1782743906);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'featured_slugs','[\"air-terjun-lubuk-hitam\", \"pantai-caroline-carolina\", \"pasar-raya-padang\", \"es-durian-iko-gantinyo\", \"jembatan-siti-nurbaya\", \"katupek-pitalah-purus-3\"]','2026-06-28 11:19:49','2026-06-28 11:28:37'),(2,'contact_email','\"info@wisatapadang.my.id\"','2026-06-28 11:26:59','2026-06-28 11:26:59');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tags` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('suasana','aktivitas','fasilitas') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tags_slug_unique` (`slug`),
  KEY `tags_type_index` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `tags` WRITE;
/*!40000 ALTER TABLE `tags` DISABLE KEYS */;
INSERT INTO `tags` VALUES (1,'Santai','santai','suasana','2026-06-28 09:13:11','2026-06-28 09:13:11'),(2,'Ramai/hidup','ramai-hidup','suasana','2026-06-28 09:13:11','2026-06-28 09:13:11'),(3,'Romantis','romantis','suasana','2026-06-28 09:13:11','2026-06-28 09:13:11'),(4,'Instagramable','instagramable','suasana','2026-06-28 09:13:11','2026-06-28 09:13:11'),(5,'Asri/sejuk','asri-sejuk','suasana','2026-06-28 09:13:11','2026-06-28 09:13:11'),(6,'Klasik/bersejarah','klasik-bersejarah','suasana','2026-06-28 09:13:11','2026-06-28 09:13:11'),(7,'Petualangan','petualangan','suasana','2026-06-28 09:13:11','2026-06-28 09:13:11'),(8,'Foto-foto','foto-foto','aktivitas','2026-06-28 09:13:11','2026-06-28 09:13:11'),(9,'Berenang','berenang','aktivitas','2026-06-28 09:13:11','2026-06-28 09:13:11'),(10,'Hiking','hiking','aktivitas','2026-06-28 09:13:11','2026-06-28 09:13:11'),(11,'Edukasi/sejarah','edukasi-sejarah','aktivitas','2026-06-28 09:13:11','2026-06-28 09:13:11'),(12,'Relaksasi','relaksasi','aktivitas','2026-06-28 09:13:11','2026-06-28 09:13:11'),(13,'Ibadah','ibadah','aktivitas','2026-06-28 09:13:11','2026-06-28 09:13:11'),(14,'Belanja','belanja','aktivitas','2026-06-28 09:13:11','2026-06-28 09:13:11'),(15,'Kulineran','kulineran','aktivitas','2026-06-28 09:13:11','2026-06-28 09:13:11'),(16,'Parkir','parkir','fasilitas','2026-06-28 09:13:11','2026-06-28 09:13:11'),(17,'Toilet','toilet','fasilitas','2026-06-28 09:13:11','2026-06-28 09:13:11'),(18,'Mushola','mushola','fasilitas','2026-06-28 09:13:11','2026-06-28 09:13:11'),(19,'Wifi','wifi','fasilitas','2026-06-28 09:13:11','2026-06-28 09:13:11'),(20,'Spot foto','spot-foto','fasilitas','2026-06-28 09:13:11','2026-06-28 09:13:11'),(21,'Area anak','area-anak','fasilitas','2026-06-28 09:13:11','2026-06-28 09:13:11'),(22,'Ramah difabel','ramah-difabel','fasilitas','2026-06-28 09:13:11','2026-06-28 09:13:11');
/*!40000 ALTER TABLE `tags` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('user','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_index` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin Wisata Padang','admin@wisatapadang.test','2026-06-28 09:13:11','$2y$12$/RwJHcfOcK2Y9Cc7.wgl6.VTuED0XiUBLp4fyT4BOWD6nBV/ghHV2','admin',1,NULL,NULL,'2026-06-28 09:13:11','2026-06-28 09:13:11'),(2,'Pengguna Contoh','user@wisatapadang.test','2026-06-28 09:13:11','$2y$12$gNyjtl/lXvv0pg/kZqg/MugYUzr7q/LTf5qCLjYMcXX5hkiAhFJeG','user',1,NULL,NULL,'2026-06-28 09:13:11','2026-06-28 09:13:11');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

