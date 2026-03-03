-- Agrega la columna email_integration_notification a la tabla agencies
-- Ejecutar desde phpMyAdmin

ALTER TABLE `agencies`
ADD COLUMN `email_integration_notification` VARCHAR(255) NULL DEFAULT NULL AFTER `configurations`;
