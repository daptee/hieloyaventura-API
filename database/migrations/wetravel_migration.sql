-- SQL para agregar soporte genérico a múltiples métodos de pago en user_reservations
-- Ejecutar esto en phpMyAdmin

-- Agregar columna para el ID del pago (genérico para cualquier proveedor)
ALTER TABLE `user_reservations` 
ADD COLUMN `payment_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID del pago en el proveedor (MercadoPago, WeTravel, Stripe, etc)' AFTER `pdf`;

-- Agregar columna para especificar el medio de pago
ALTER TABLE `user_reservations` 
ADD COLUMN `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Método de pago utilizado (mercadopago, wetravel, stripe, paypal, etc)' AFTER `payment_id`;

-- Agregar columna para el estado del pago (opcional pero recomendado)
ALTER TABLE `user_reservations` 
ADD COLUMN `payment_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Estado del pago (pending, completed, failed, refunded, cancelled)' AFTER `payment_method`;

-- Agregar índices para búsquedas rápidas
ALTER TABLE `user_reservations`
ADD KEY `user_reservations_payment_id` (`payment_id`);

ALTER TABLE `user_reservations`
ADD KEY `user_reservations_payment_method` (`payment_method`);

ALTER TABLE `user_reservations`
ADD KEY `user_reservations_payment_status` (`payment_status`);

-- Verificar que los campos se agregaron correctamente
-- Ejecutar esto para verificar:
-- DESCRIBE user_reservations;
