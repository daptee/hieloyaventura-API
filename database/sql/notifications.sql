-- ============================================================
-- Sistema de Notificaciones - Hielo y Aventura
-- Ejecutar en phpMyAdmin en el orden indicado
-- ============================================================

-- 0. Nuevo módulo: Notificaciones (id = 7)
--    Ajustar "Notificaciones" si la tabla modules tiene columna "name" con otro nombre.
--    Verificar primero: SELECT * FROM modules;
INSERT INTO `modules` (`id`, `name`) VALUES (7, 'Notificaciones');
-- Luego asignar el módulo a los usuarios admin que lo necesiten desde el panel
-- o con: INSERT INTO user_modules (user_id, module_id) VALUES (<id_usuario>, 7);

-- 1. Tabla principal de notificaciones
CREATE TABLE `notifications` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `body` longtext NOT NULL,
  `recipients_type` enum('admins','all') NOT NULL DEFAULT 'all'
    COMMENT 'admins = solo admins de agencia, all = todos los usuarios de agencia',
  `send_to_all_agencies` tinyint(1) NOT NULL DEFAULT 1
    COMMENT '1 = se envia a todas las agencias, 0 = solo a las indicadas en notification_agencies',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Agencias destinatarias (solo se inserta cuando send_to_all_agencies = 0)
CREATE TABLE `notification_agencies` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `notification_id` int(10) UNSIGNED NOT NULL,
  `agency_code` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notification_agencies_notification_id_index` (`notification_id`),
  KEY `notification_agencies_agency_code_index` (`agency_code`),
  CONSTRAINT `notification_agencies_notification_id_foreign`
    FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Registro de lecturas: qué usuario leyó qué notificación y cuándo
CREATE TABLE `notification_reads` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `notification_id` int(10) UNSIGNED NOT NULL,
  `agency_user_id` int(10) UNSIGNED NOT NULL,
  `read_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `notification_reads_notification_agency_user_unique` (`notification_id`, `agency_user_id`),
  KEY `notification_reads_notification_id_index` (`notification_id`),
  KEY `notification_reads_agency_user_id_index` (`agency_user_id`),
  CONSTRAINT `notification_reads_notification_id_foreign`
    FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
