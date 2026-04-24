-- SQL para agregar soporte a WeTravel en la tabla user_reservations
-- Ejecutar esto en phpMyAdmin

-- Agregar columna para el ID del payment link de WeTravel
ALTER TABLE `user_reservations` 
ADD COLUMN `wetravel_payment_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID del payment link generado por WeTravel' AFTER `pdf`;

-- Agregar columna para especificar el medio de pago
ALTER TABLE `user_reservations` 
ADD COLUMN `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Medio de pago utilizado (mercadopago, wetravel, etc)' AFTER `wetravel_payment_id`;

-- Agregar índice para búsquedas rápidas por wetravel_payment_id
ALTER TABLE `user_reservations`
ADD KEY `user_reservations_wetravel_payment_id` (`wetravel_payment_id`);

-- Agregar índice para búsquedas por payment_method
ALTER TABLE `user_reservations`
ADD KEY `user_reservations_payment_method` (`payment_method`);

-- Verificar que los campos se agregaron correctamente
-- Ejecutar esto para verificar:
-- DESCRIBE user_reservations;
