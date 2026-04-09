-- =====================================================
-- SISTEMA DE INSCRIPCIONES — Schema v2
-- Compatible: MySQL 8+ / MariaDB 10.4+
-- Ejecutar desde phpMyAdmin o línea de comandos
-- =====================================================

SET NAMES utf8mb4;
SET time_zone = '-03:00';

-- Crear base de datos si no existe
CREATE DATABASE IF NOT EXISTS `inscripciones_db`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `inscripciones_db`;

-- =====================================================
-- Tabla: usuarios del sistema
-- =====================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id`                   INT AUTO_INCREMENT PRIMARY KEY,
    `name`                 VARCHAR(100) NOT NULL,
    `email`                VARCHAR(150) UNIQUE NOT NULL,
    `password`             VARCHAR(255) NOT NULL,
    `role`                 ENUM('super_admin', 'admin', 'editor') DEFAULT 'admin',
    `is_active`            TINYINT(1) DEFAULT 1,
    `must_change_password` TINYINT(1) DEFAULT 1,
    `last_login_at`        TIMESTAMP NULL,
    `created_by`           INT NULL,
    `created_at`           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`           TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabla: intentos de login fallidos (para bloqueo temporal)
-- =====================================================
CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id`           INT AUTO_INCREMENT PRIMARY KEY,
    `email`        VARCHAR(150) NOT NULL,
    `ip_address`   VARCHAR(45),
    `attempted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_email_ip` (`email`, `ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabla: sesiones activas (para invalidación forzada)
-- =====================================================
CREATE TABLE IF NOT EXISTS `user_sessions` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`       INT NOT NULL,
    `session_token` VARCHAR(128) UNIQUE NOT NULL,
    `ip_address`    VARCHAR(45),
    `user_agent`    TEXT,
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expires_at`    TIMESTAMP NOT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_token` (`session_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabla: eventos
-- =====================================================
CREATE TABLE IF NOT EXISTS `events` (
    `id`               INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`          INT NOT NULL,
    `title`            VARCHAR(255) NOT NULL,
    `slug`             VARCHAR(255) UNIQUE NOT NULL,
    `description`      TEXT,
    `cover_image`      VARCHAR(255),
    `location`         VARCHAR(255),
    `start_date`       DATETIME,
    `end_date`         DATETIME,
    `max_capacity`     INT DEFAULT NULL,
    `status`           ENUM('draft', 'published', 'finished') DEFAULT 'draft',
    `notify_email`     VARCHAR(150),
    `meta_description` TEXT,
    `deleted_at`       TIMESTAMP NULL DEFAULT NULL,
    `created_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
    INDEX `idx_slug` (`slug`),
    INDEX `idx_status` (`status`),
    INDEX `idx_deleted` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabla: formularios (estructura dinámica en JSON)
-- =====================================================
CREATE TABLE IF NOT EXISTS `forms` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `event_id`    INT NOT NULL,
    `title`       VARCHAR(255),
    `fields_json` LONGTEXT NOT NULL COMMENT 'JSON con la estructura del formulario',
    `is_active`   TINYINT(1) DEFAULT 1,
    `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabla: inscripciones / respuestas
-- =====================================================
CREATE TABLE IF NOT EXISTS `submissions` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `form_id`       INT NOT NULL,
    `event_id`      INT NOT NULL,
    `response_data` LONGTEXT NOT NULL COMMENT 'JSON {"field_uuid": "valor"}',
    `status`        ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    `ip_address`    VARCHAR(45),
    `user_agent`    TEXT,
    `deleted_at`    TIMESTAMP NULL DEFAULT NULL,
    `submitted_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`form_id`) REFERENCES `forms`(`id`),
    FOREIGN KEY (`event_id`) REFERENCES `events`(`id`),
    INDEX `idx_event` (`event_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_deleted` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabla: archivos adjuntos de inscripciones
-- =====================================================
CREATE TABLE IF NOT EXISTS `submission_files` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `submission_id` INT NOT NULL,
    `field_id`      VARCHAR(50) NOT NULL,
    `file_path`     VARCHAR(255) NOT NULL,
    `original_name` VARCHAR(255),
    `mime_type`     VARCHAR(100),
    `file_size`     INT,
    `uploaded_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`submission_id`) REFERENCES `submissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabla: cola de emails (envío asíncrono simple)
-- =====================================================
CREATE TABLE IF NOT EXISTS `mail_queue` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `to_email`   VARCHAR(150) NOT NULL,
    `to_name`    VARCHAR(100),
    `subject`    VARCHAR(255) NOT NULL,
    `body_html`  LONGTEXT NOT NULL,
    `status`     ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    `attempts`   INT DEFAULT 0,
    `sent_at`    TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabla: configuración del sistema (clave-valor)
-- =====================================================
CREATE TABLE IF NOT EXISTS `settings` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `key_name`   VARCHAR(100) UNIQUE NOT NULL,
    `value_data` TEXT,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabla: log de auditoría
-- =====================================================
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id`          INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`     INT NULL,
    `action`      VARCHAR(100) NOT NULL COMMENT 'ej: event.created, user.login',
    `resource`    VARCHAR(100) COMMENT 'ej: Event, User',
    `resource_id` INT NULL,
    `details`     LONGTEXT NULL COMMENT 'JSON con detalles adicionales',
    `ip_address`  VARCHAR(45),
    `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Datos iniciales
-- =====================================================

-- Super Admin inicial
-- Contraseña: Admin@2025! (hash bcrypt cost 12)
-- IMPORTANTE: Cambiar en el primer login
INSERT INTO `users` (`name`, `email`, `password`, `role`, `is_active`, `must_change_password`)
VALUES (
    'Super Administrador',
    'admin@sistema.com',
    '$2y$12$.cEQ6o/6ODP2uZO8yFxgRu0U7rVZRqd3hIUWlPacL6jXOVqMpRtJ2', -- Admin@2025!
    'super_admin',
    1,
    1
) ON DUPLICATE KEY UPDATE `id` = `id`;

-- Configuración inicial del sistema
INSERT INTO `settings` (`key_name`, `value_data`) VALUES
('app_name',       'Sistema de Inscripciones'),
('app_logo',       ''),
('hero_title',     'Bienvenido a nuestros eventos'),
('hero_subtitle',  'Explorá y registrate en los próximos eventos'),
('hero_image',     ''),
('footer_text',    ''),
('smtp_configured','0'),
('session_lifetime','7200'),
('login_max_attempts','5'),
('login_lockout_minutes','15'),
('rate_limit_submissions','5'),
('rate_limit_window','3600')
ON DUPLICATE KEY UPDATE `value_data` = VALUES(`value_data`);
