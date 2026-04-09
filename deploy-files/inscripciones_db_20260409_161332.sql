-- MariaDB dump 10.19  Distrib 10.4.28-MariaDB, for osx10.10 (x86_64)
--
-- Host: localhost    Database: inscripciones_db
-- ------------------------------------------------------
-- Server version	10.4.28-MariaDB

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
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL COMMENT 'ej: event.created, user.login',
  `resource` varchar(100) DEFAULT NULL COMMENT 'ej: Event, User',
  `resource_id` int(11) DEFAULT NULL,
  `details` longtext DEFAULT NULL COMMENT 'JSON con detalles adicionales',
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_action` (`action`),
  CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,1,'user.login','User',1,'{\"ip\":\"::1\"}','::1','2026-04-08 00:59:19'),(2,1,'user.password_changed','User',1,NULL,'::1','2026-04-08 00:59:46'),(3,1,'user.login','User',1,'{\"ip\":\"::1\"}','::1','2026-04-08 09:58:34'),(4,1,'event.updated','Event',1,'{\"title\":\"Conferencia de Tecnología 2025\"}','::1','2026-04-08 10:12:32'),(5,1,'event.updated','Event',2,'{\"title\":\"Taller de Programación Web\"}','::1','2026-04-08 10:13:11'),(6,1,'user.login','User',1,'{\"ip\":\"::1\"}','::1','2026-04-08 18:27:06'),(7,1,'settings.updated','Settings',NULL,NULL,'::1','2026-04-08 18:27:28'),(8,1,'settings.updated','Settings',NULL,NULL,'::1','2026-04-08 18:28:14'),(9,1,'settings.updated','Settings',NULL,NULL,'::1','2026-04-08 18:28:15'),(10,1,'settings.updated','Settings',NULL,NULL,'::1','2026-04-08 18:28:16'),(11,1,'settings.updated','Settings',NULL,NULL,'::1','2026-04-08 18:28:16'),(12,1,'user.login','User',1,'{\"ip\":\"::1\"}','::1','2026-04-09 10:25:58'),(13,1,'export.pdf_submission','Submission',6,NULL,'::1','2026-04-09 10:33:48'),(14,1,'export.pdf_submission','Submission',3,NULL,'::1','2026-04-09 10:33:59'),(15,1,'export.pdf_submission','Submission',3,NULL,'::1','2026-04-09 10:37:00'),(16,1,'export.pdf_submission','Submission',2,NULL,'::1','2026-04-09 10:37:58'),(17,1,'user.login','User',1,'{\"ip\":\"::1\"}','::1','2026-04-09 18:15:50'),(18,1,'event.updated','Event',1,'{\"title\":\"Conferencia de Tecnología 2025\"}','::1','2026-04-09 18:28:16'),(19,1,'submission.deleted','Submission',5,NULL,'::1','2026-04-09 18:31:55'),(20,1,'submission.deleted','Submission',6,NULL,'::1','2026-04-09 18:35:08'),(21,1,'submission.deleted','Submission',1,NULL,'::1','2026-04-09 18:35:12'),(22,1,'submission.deleted','Submission',2,NULL,'::1','2026-04-09 18:35:14'),(23,1,'submission.deleted','Submission',3,NULL,'::1','2026-04-09 18:35:17'),(24,1,'submission.deleted','Submission',4,NULL,'::1','2026-04-09 18:35:20'),(25,1,'settings.updated','Settings',NULL,NULL,'::1','2026-04-09 18:35:59'),(26,1,'settings.updated','Settings',NULL,NULL,'::1','2026-04-09 18:36:11'),(27,1,'settings.updated','Settings',NULL,NULL,'::1','2026-04-09 18:40:38'),(28,1,'settings.updated','Settings',NULL,NULL,'::1','2026-04-09 18:40:55'),(29,1,'settings.updated','Settings',NULL,NULL,'::1','2026-04-09 18:41:06'),(30,1,'settings.updated','Settings',NULL,NULL,'::1','2026-04-09 18:45:54'),(31,1,'settings.updated','Settings',NULL,NULL,'::1','2026-04-09 18:46:13'),(32,1,'settings.updated','Settings',NULL,NULL,'::1','2026-04-09 18:46:14'),(33,1,'user.logout','User',1,NULL,'::1','2026-04-09 18:47:17'),(34,1,'user.login','User',1,'{\"ip\":\"::1\"}','::1','2026-04-09 18:48:41'),(35,1,'settings.updated','Settings',NULL,NULL,'::1','2026-04-09 18:48:55'),(36,1,'settings.updated','Settings',NULL,NULL,'::1','2026-04-09 19:02:09');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `max_capacity` int(11) DEFAULT NULL,
  `status` enum('draft','published','finished') DEFAULT 'draft',
  `visibility` enum('public','private') DEFAULT 'public',
  `notify_email` varchar(150) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `user_id` (`user_id`),
  KEY `idx_slug` (`slug`),
  KEY `idx_status` (`status`),
  KEY `idx_deleted` (`deleted_at`),
  CONSTRAINT `events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` VALUES (1,2,'Conferencia de Tecnología 2025','conferencia-tecnologia-2025','<p>Una conferencia dedicada a las últimas tendencias en tecnología, inteligencia artificial y desarrollo de software. Contará con speakers internacionales y talleres prácticos.</p><p>El evento se realizará en el Centro de Convenciones de Buenos Aires, con capacidad para 500 asistentes.</p>','uploads/events/69d62a106aed92.27101442_fc827064.png','Centro de Convenciones, Buenos Aires','2026-04-23 09:00:00','2026-04-23 18:00:00',200,'published','private','organizador@ejemplo.com','Conferencia de tecnología 2025 en Buenos Aires. IA, desarrollo web y más.',NULL,'2026-04-08 00:57:22','2026-04-09 18:28:16'),(2,2,'Taller de Programación Web','taller-programacion-web','<p>Taller intensivo de 8 horas sobre desarrollo web moderno con PHP, JavaScript y bases de datos. Ideal para principiantes y desarrolladores que quieran actualizar sus conocimientos.</p>','uploads/events/69d62a3717aad8.73126851_6919fa72.png','Aula Magna, Universidad Tecnológica','2026-05-08 10:00:00','2026-05-08 18:00:00',30,'published','public','taller@ejemplo.com','Taller de programación web PHP y JavaScript.',NULL,'2026-04-08 00:57:22','2026-04-08 10:13:11'),(3,2,'Evento Borrador — No Publicado','evento-borrador-prueba','<p>Este es un evento en estado borrador, no visible en el listado público.</p>',NULL,'Por confirmar','2026-06-07 09:00:00','2026-06-07 17:00:00',NULL,'draft','public','draft@ejemplo.com','',NULL,'2026-04-08 00:57:22','2026-04-08 00:57:22');
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forms`
--

DROP TABLE IF EXISTS `forms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `fields_json` longtext NOT NULL COMMENT 'JSON con la estructura del formulario',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`),
  CONSTRAINT `forms_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forms`
--

LOCK TABLES `forms` WRITE;
/*!40000 ALTER TABLE `forms` DISABLE KEYS */;
INSERT INTO `forms` VALUES (1,1,'Formulario Conferencia de Tecnología 2025','{\n    \"version\": \"1.0\",\n    \"settings\": {\n        \"submit_label\": \"Confirmar inscripción\",\n        \"success_message\": \"¡Gracias! Tu inscripción a la Conferencia de Tecnología fue recibida. Te enviaremos un email con los detalles.\",\n        \"notify_email\": \"organizador@ejemplo.com\",\n        \"max_submissions\": null,\n        \"allow_duplicates\": false\n    },\n    \"fields\": [\n        {\n            \"id\": \"field_fe6a2bed74124f85\",\n            \"type\": \"text\",\n            \"label\": \"Nombre completo\",\n            \"placeholder\": \"Ej: Juan Pérez\",\n            \"required\": true,\n            \"help_text\": \"\",\n            \"width\": \"half\",\n            \"order\": 1,\n            \"validations\": {\n                \"min_length\": 3,\n                \"max_length\": 100\n            }\n        },\n        {\n            \"id\": \"field_8bf424a8de5728b1\",\n            \"type\": \"email\",\n            \"label\": \"Correo electrónico\",\n            \"placeholder\": \"tucorreo@ejemplo.com\",\n            \"required\": true,\n            \"help_text\": \"Recibirás la confirmación en este email\",\n            \"width\": \"half\",\n            \"order\": 2,\n            \"validations\": []\n        },\n        {\n            \"id\": \"field_820234a8b9448ede\",\n            \"type\": \"tel\",\n            \"label\": \"Teléfono de contacto\",\n            \"placeholder\": \"+54 11 1234-5678\",\n            \"required\": false,\n            \"help_text\": \"\",\n            \"width\": \"half\",\n            \"order\": 3,\n            \"validations\": []\n        },\n        {\n            \"id\": \"field_a3cdd621977ce01f\",\n            \"type\": \"select\",\n            \"label\": \"Tipo de entrada\",\n            \"required\": true,\n            \"help_text\": \"\",\n            \"width\": \"half\",\n            \"order\": 4,\n            \"options\": [\n                {\n                    \"value\": \"general\",\n                    \"label\": \"General (gratuita)\"\n                },\n                {\n                    \"value\": \"vip\",\n                    \"label\": \"VIP (con acceso a workshops)\"\n                },\n                {\n                    \"value\": \"estudiante\",\n                    \"label\": \"Estudiante (con DNI)\"\n                }\n            ]\n        },\n        {\n            \"id\": \"field_0bf3b75b228041c9\",\n            \"type\": \"select\",\n            \"label\": \"¿Cómo te enteraste del evento?\",\n            \"required\": false,\n            \"help_text\": \"\",\n            \"width\": \"half\",\n            \"order\": 5,\n            \"options\": [\n                {\n                    \"value\": \"redes\",\n                    \"label\": \"Redes sociales\"\n                },\n                {\n                    \"value\": \"email\",\n                    \"label\": \"Email \\/ newsletter\"\n                },\n                {\n                    \"value\": \"amigo\",\n                    \"label\": \"Un amigo o colega\"\n                },\n                {\n                    \"value\": \"web\",\n                    \"label\": \"Búsqueda en internet\"\n                },\n                {\n                    \"value\": \"otro\",\n                    \"label\": \"Otro\"\n                }\n            ]\n        },\n        {\n            \"id\": \"field_1fa49a4fb971c338\",\n            \"type\": \"checkbox\",\n            \"label\": \"¿Qué temas te interesan?\",\n            \"required\": false,\n            \"help_text\": \"Podés seleccionar más de uno\",\n            \"width\": \"full\",\n            \"order\": 6,\n            \"options\": [\n                {\n                    \"value\": \"ia\",\n                    \"label\": \"Inteligencia Artificial\"\n                },\n                {\n                    \"value\": \"web\",\n                    \"label\": \"Desarrollo Web\"\n                },\n                {\n                    \"value\": \"mobile\",\n                    \"label\": \"Desarrollo Mobile\"\n                },\n                {\n                    \"value\": \"devops\",\n                    \"label\": \"DevOps \\/ Cloud\"\n                },\n                {\n                    \"value\": \"seguridad\",\n                    \"label\": \"Ciberseguridad\"\n                }\n            ]\n        },\n        {\n            \"id\": \"field_726732beaa566a3b\",\n            \"type\": \"textarea\",\n            \"label\": \"Comentarios adicionales\",\n            \"placeholder\": \"Alguna consulta o comentario...\",\n            \"required\": false,\n            \"help_text\": \"\",\n            \"width\": \"full\",\n            \"order\": 7,\n            \"validations\": {\n                \"max_length\": 500\n            }\n        }\n    ]\n}',1,'2026-04-08 00:57:22','2026-04-08 00:57:22'),(2,2,'Formulario Taller de Programación Web','{\n    \"version\": \"1.0\",\n    \"settings\": {\n        \"submit_label\": \"Reservar mi lugar\",\n        \"success_message\": \"¡Lugar reservado! Te enviaremos un email con los detalles del taller.\",\n        \"notify_email\": \"taller@ejemplo.com\",\n        \"max_submissions\": 30,\n        \"allow_duplicates\": false\n    },\n    \"fields\": [\n        {\n            \"id\": \"field_b9df4e287554583e\",\n            \"type\": \"text\",\n            \"label\": \"Nombre y apellido\",\n            \"placeholder\": \"Ej: María García\",\n            \"required\": true,\n            \"help_text\": \"\",\n            \"width\": \"half\",\n            \"order\": 1,\n            \"validations\": {\n                \"min_length\": 3,\n                \"max_length\": 100\n            }\n        },\n        {\n            \"id\": \"field_4cb229a60bd1bf2b\",\n            \"type\": \"email\",\n            \"label\": \"Email\",\n            \"placeholder\": \"tucorreo@ejemplo.com\",\n            \"required\": true,\n            \"help_text\": \"\",\n            \"width\": \"half\",\n            \"order\": 2,\n            \"validations\": []\n        },\n        {\n            \"id\": \"field_a6f1f5f68b673630\",\n            \"type\": \"select\",\n            \"label\": \"Nivel de experiencia\",\n            \"required\": true,\n            \"help_text\": \"\",\n            \"width\": \"full\",\n            \"order\": 3,\n            \"options\": [\n                {\n                    \"value\": \"principiante\",\n                    \"label\": \"Principiante (sin experiencia previa)\"\n                },\n                {\n                    \"value\": \"intermedio\",\n                    \"label\": \"Intermedio (algo de experiencia)\"\n                },\n                {\n                    \"value\": \"avanzado\",\n                    \"label\": \"Avanzado (trabajo con código)\"\n                }\n            ]\n        },\n        {\n            \"id\": \"field_e0f6482957123f4a\",\n            \"type\": \"radio\",\n            \"label\": \"¿Traés laptop?\",\n            \"required\": true,\n            \"help_text\": \"El taller es práctico, se recomienda traer computadora\",\n            \"width\": \"full\",\n            \"order\": 4,\n            \"options\": [\n                {\n                    \"value\": \"si\",\n                    \"label\": \"Sí, traigo mi computadora\"\n                },\n                {\n                    \"value\": \"no\",\n                    \"label\": \"No, necesito una prestada\"\n                }\n            ]\n        }\n    ]\n}',1,'2026-04-08 00:57:22','2026-04-08 00:57:22');
/*!40000 ALTER TABLE `forms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(150) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_email_ip` (`email`,`ip_address`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_attempts`
--

LOCK TABLES `login_attempts` WRITE;
/*!40000 ALTER TABLE `login_attempts` DISABLE KEYS */;
INSERT INTO `login_attempts` VALUES (4,'rl:form_submit:::1','::1','2026-04-09 10:24:41');
/*!40000 ALTER TABLE `login_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mail_queue`
--

DROP TABLE IF EXISTS `mail_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mail_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `to_email` varchar(150) NOT NULL,
  `to_name` varchar(100) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `body_html` longtext NOT NULL,
  `status` enum('pending','sent','failed') DEFAULT 'pending',
  `attempts` int(11) DEFAULT 0,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mail_queue`
--

LOCK TABLES `mail_queue` WRITE;
/*!40000 ALTER TABLE `mail_queue` DISABLE KEYS */;
INSERT INTO `mail_queue` VALUES (1,'ivankorner@gmail.com','Ivan Korner','Confirmación de inscripción — Conferencia de Tecnología 2025','<!DOCTYPE html>\n<html lang=\"es\">\n<head><meta charset=\"UTF-8\"><title>Confirmación de inscripción</title></head>\n<body style=\"font-family:Arial,sans-serif;background:#f4f4f4;padding:20px\">\n  <div style=\"max-width:600px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden\">\n    <div style=\"background:#4f46e5;padding:30px;text-align:center\">\n      <h1 style=\"color:#fff;margin:0;font-size:24px\">Sistema de Inscripciones</h1>\n    </div>\n    <div style=\"padding:30px\">\n      <h2>¡Tu inscripción fue recibida!</h2>\n      <p>Gracias por inscribirte a <strong>Conferencia de Tecnología 2025</strong>. A continuación el resumen de tus datos:</p>\n      <table style=\"width:100%;border-collapse:collapse;margin:20px 0\"><tr><td style=\'padding:8px;background:#f9f9f9;font-weight:bold\'>Nombre completo</td><td style=\'padding:8px\'>Ivan Korner</td></tr><tr><td style=\'padding:8px;background:#f9f9f9;font-weight:bold\'>Correo electrónico</td><td style=\'padding:8px\'>ivankorner@gmail.com</td></tr><tr><td style=\'padding:8px;background:#f9f9f9;font-weight:bold\'>Teléfono de contacto</td><td style=\'padding:8px\'>3751505333</td></tr><tr><td style=\'padding:8px;background:#f9f9f9;font-weight:bold\'>Tipo de entrada</td><td style=\'padding:8px\'>general</td></tr><tr><td style=\'padding:8px;background:#f9f9f9;font-weight:bold\'>¿Cómo te enteraste del evento?</td><td style=\'padding:8px\'>redes</td></tr><tr><td style=\'padding:8px;background:#f9f9f9;font-weight:bold\'>¿Qué temas te interesan?</td><td style=\'padding:8px\'>ia</td></tr><tr><td style=\'padding:8px;background:#f9f9f9;font-weight:bold\'>Comentarios adicionales</td><td style=\'padding:8px\'>Estoy ansioso de verlo</td></tr></table>\n      <p style=\"color:#666;font-size:14px\">Si tenés alguna consulta, respondé este email o contactá a los organizadores del evento.</p>\n    </div>\n  </div>\n</body>\n</html>','pending',1,NULL,'2026-04-09 10:24:41'),(2,'organizador@ejemplo.com','Organizador','Nueva inscripción — Conferencia de Tecnología 2025','<!DOCTYPE html>\n<html lang=\"es\"><head><meta charset=\"UTF-8\"><title>Nueva inscripción</title></head>\n<body style=\"font-family:Arial,sans-serif;background:#f4f4f4;padding:20px\">\n  <div style=\"max-width:600px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden\">\n    <div style=\"background:#059669;padding:30px;text-align:center\">\n      <h1 style=\"color:#fff;margin:0;font-size:24px\">Nueva inscripción recibida</h1>\n    </div>\n    <div style=\"padding:30px\">\n      <p>Hay una nueva inscripción en <strong>Conferencia de Tecnología 2025</strong> recibida el <strong>09/04/2026 07:24</strong> desde la IP <code>::1</code>.</p>\n      <table style=\"width:100%;border-collapse:collapse;margin:20px 0\"><tr><td style=\'padding:8px;background:#f9f9f9;font-weight:bold\'>Nombre completo</td><td style=\'padding:8px\'>Ivan Korner</td></tr><tr><td style=\'padding:8px;background:#f9f9f9;font-weight:bold\'>Correo electrónico</td><td style=\'padding:8px\'>ivankorner@gmail.com</td></tr><tr><td style=\'padding:8px;background:#f9f9f9;font-weight:bold\'>Teléfono de contacto</td><td style=\'padding:8px\'>3751505333</td></tr><tr><td style=\'padding:8px;background:#f9f9f9;font-weight:bold\'>Tipo de entrada</td><td style=\'padding:8px\'>general</td></tr><tr><td style=\'padding:8px;background:#f9f9f9;font-weight:bold\'>¿Cómo te enteraste del evento?</td><td style=\'padding:8px\'>redes</td></tr><tr><td style=\'padding:8px;background:#f9f9f9;font-weight:bold\'>¿Qué temas te interesan?</td><td style=\'padding:8px\'>ia</td></tr><tr><td style=\'padding:8px;background:#f9f9f9;font-weight:bold\'>Comentarios adicionales</td><td style=\'padding:8px\'>Estoy ansioso de verlo</td></tr></table>\n      <p style=\"color:#666;font-size:12px\">— Sistema de Inscripciones</p>\n    </div>\n  </div>\n</body></html>','pending',1,NULL,'2026-04-09 10:24:41');
/*!40000 ALTER TABLE `mail_queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key_name` varchar(100) NOT NULL,
  `value_data` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_name` (`key_name`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'app_name','Eventos CDE','2026-04-09 19:02:09'),(2,'app_logo','uploads/system/69d7f18f4aea24.01818488_a25da838.png','2026-04-09 18:35:59'),(3,'hero_title','Bienvenido a nuestros eventos','2026-04-08 00:56:28'),(4,'hero_subtitle','Explorá y registrate en los próximos eventos','2026-04-08 00:56:28'),(5,'hero_image','','2026-04-08 00:56:28'),(6,'smtp_configured','0','2026-04-08 00:56:28'),(7,'session_lifetime','7200','2026-04-08 00:56:28'),(8,'login_max_attempts','5','2026-04-08 00:56:28'),(9,'login_lockout_minutes','15','2026-04-08 00:56:28'),(10,'rate_limit_submissions','5','2026-04-08 00:56:28'),(11,'rate_limit_window','3600','2026-04-08 00:56:28'),(37,'footer_text','','2026-04-09 18:41:06');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `submission_files`
--

DROP TABLE IF EXISTS `submission_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `submission_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `submission_id` int(11) NOT NULL,
  `field_id` varchar(50) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `submission_id` (`submission_id`),
  CONSTRAINT `submission_files_ibfk_1` FOREIGN KEY (`submission_id`) REFERENCES `submissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `submission_files`
--

LOCK TABLES `submission_files` WRITE;
/*!40000 ALTER TABLE `submission_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `submission_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `submissions`
--

DROP TABLE IF EXISTS `submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `response_data` longtext NOT NULL COMMENT 'JSON {"field_uuid": "valor"}',
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `form_id` (`form_id`),
  KEY `idx_event` (`event_id`),
  KEY `idx_status` (`status`),
  KEY `idx_deleted` (`deleted_at`),
  CONSTRAINT `submissions_ibfk_1` FOREIGN KEY (`form_id`) REFERENCES `forms` (`id`),
  CONSTRAINT `submissions_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `submissions`
--

LOCK TABLES `submissions` WRITE;
/*!40000 ALTER TABLE `submissions` DISABLE KEYS */;
INSERT INTO `submissions` VALUES (1,1,1,'{\"field_fe6a2bed74124f85\":\"Ana Mart\\u00ednez\",\"field_8bf424a8de5728b1\":\"ana@mail.com\",\"field_820234a8b9448ede\":\"+54 11 2743-4441\",\"field_a3cdd621977ce01f\":\"general\",\"field_0bf3b75b228041c9\":\"redes\"}','pending','192.168.1.10',NULL,'2026-04-09 18:35:12','2026-04-08 05:57:22'),(2,1,1,'{\"field_fe6a2bed74124f85\":\"Carlos L\\u00f3pez\",\"field_8bf424a8de5728b1\":\"carlos@mail.com\",\"field_820234a8b9448ede\":\"+54 11 8378-3294\",\"field_a3cdd621977ce01f\":\"vip\",\"field_0bf3b75b228041c9\":\"email\"}','confirmed','192.168.1.11',NULL,'2026-04-09 18:35:14','2026-04-06 05:57:22'),(3,1,1,'{\"field_fe6a2bed74124f85\":\"Sof\\u00eda Rodr\\u00edguez\",\"field_8bf424a8de5728b1\":\"sofia@mail.com\",\"field_820234a8b9448ede\":\"+54 11 8929-7662\",\"field_a3cdd621977ce01f\":\"estudiante\",\"field_0bf3b75b228041c9\":\"amigo\"}','confirmed','192.168.1.12',NULL,'2026-04-09 18:35:17','2026-04-04 05:57:22'),(4,1,1,'{\"field_fe6a2bed74124f85\":\"Diego Fern\\u00e1ndez\",\"field_8bf424a8de5728b1\":\"diego@mail.com\",\"field_820234a8b9448ede\":\"+54 11 9460-8045\",\"field_a3cdd621977ce01f\":\"general\",\"field_0bf3b75b228041c9\":\"web\"}','pending','192.168.1.13',NULL,'2026-04-09 18:35:20','2026-04-02 05:57:22'),(5,1,1,'{\"field_fe6a2bed74124f85\":\"Valentina Garc\\u00eda\",\"field_8bf424a8de5728b1\":\"vale@mail.com\",\"field_820234a8b9448ede\":\"+54 11 3130-5455\",\"field_a3cdd621977ce01f\":\"vip\",\"field_0bf3b75b228041c9\":\"redes\"}','cancelled','192.168.1.14',NULL,'2026-04-09 18:31:55','2026-03-31 05:57:22'),(6,1,1,'{\"field_fe6a2bed74124f85\":\"Ivan Korner\",\"field_8bf424a8de5728b1\":\"ivankorner@gmail.com\",\"field_820234a8b9448ede\":\"3751505333\",\"field_a3cdd621977ce01f\":\"general\",\"field_0bf3b75b228041c9\":\"redes\",\"field_1fa49a4fb971c338\":[\"ia\"],\"field_726732beaa566a3b\":\"Estoy ansioso de verlo\"}','pending','::1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-04-09 18:35:08','2026-04-09 10:24:41');
/*!40000 ALTER TABLE `submissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_sessions`
--

DROP TABLE IF EXISTS `user_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(128) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_token` (`session_token`),
  KEY `user_id` (`user_id`),
  KEY `idx_token` (`session_token`),
  CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_sessions`
--

LOCK TABLES `user_sessions` WRITE;
/*!40000 ALTER TABLE `user_sessions` DISABLE KEYS */;
INSERT INTO `user_sessions` VALUES (1,1,'53b14b8e42cacb49165734ec5605471650853c0ec084d2c6cd0478214a98122e','::1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-04-08 00:59:19','2026-04-08 02:59:19'),(2,1,'c4441f7ee616a4b879b31dca4984df8b69da99133d675d03c81a38866ba8e164','::1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-04-08 09:58:34','2026-04-08 11:58:34'),(3,1,'165f1b314c3cd603f23f226790644d25c79af047fcadd871363b3d6399e616a8','::1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-04-08 18:27:06','2026-04-08 20:27:06'),(4,1,'4f49994a545d602308b20c8c48ab23554f3aab11cd7957f3b494058dc56f66af','::1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.4 Safari/605.1.15','2026-04-09 10:25:58','2026-04-09 12:25:58'),(5,1,'10b45cb5f2989c2700a57906f8834cb79f638964903d65343cd77954aa144b51','::1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','2026-04-09 18:15:50','2026-04-09 20:15:50'),(6,1,'21d7c2a648c7f0181428c533371c417e937c479fe6c290dadb43f257d5f0c3c0','::1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.4 Safari/605.1.15','2026-04-09 18:48:41','2026-04-09 20:48:41');
/*!40000 ALTER TABLE `user_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','editor') DEFAULT 'admin',
  `is_active` tinyint(1) DEFAULT 1,
  `must_change_password` tinyint(1) DEFAULT 1,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Super Administrador','admin@sistema.com','$2y$12$VeVi/w53/nljkwQ2oyaqxuTwIjyw.APiaxSt.Pdn.f0/wnE9V.jna','super_admin',1,0,'2026-04-09 18:48:41',NULL,'2026-04-08 00:56:28','2026-04-09 18:48:41'),(2,'Administrador Demo','demo@sistema.com','$2y$12$gvfJNRVIZqjU.Z.g.Qo27eeQw1VKq18gTmbQ2tE.b.UJl1n8JLF46','admin',1,0,NULL,NULL,'2026-04-08 00:57:22','2026-04-08 00:57:22');
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

-- Dump completed on 2026-04-09 16:13:32
