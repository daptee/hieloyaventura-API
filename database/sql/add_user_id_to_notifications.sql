-- ============================================================
-- Agregar campo user_id a la tabla notifications
-- Ejecutar en phpMyAdmin
-- ============================================================

-- 1. Agregar columna user_id a la tabla notifications
ALTER TABLE `notifications` ADD COLUMN `user_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 2. Agregar la relación de clave foránea con la tabla users
ALTER TABLE `notifications` ADD CONSTRAINT `notifications_user_id_foreign` 
FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
