-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 23-04-2026 a las 19:24:52
-- Versión del servidor: 5.7.44
-- Versión de PHP: 8.1.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `hieloyaventura_dev_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_reservations`
--

CREATE TABLE `user_reservations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reservation_number` bigint(20) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `agency_id` int(11) DEFAULT NULL,
  `user_agency_id` int(11) DEFAULT NULL,
  `hotel_id` bigint(20) UNSIGNED DEFAULT NULL,
  `excurtion_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reservation_status_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reason_cancellation` text COLLATE utf8mb4_unicode_ci,
  `hotel_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(8,2) DEFAULT NULL,
  `children_price` decimal(8,2) DEFAULT NULL,
  `special_discount` decimal(8,2) DEFAULT NULL,
  `is_paid` tinyint(4) DEFAULT NULL,
  `is_transfer` tinyint(4) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `turn` time DEFAULT NULL,
  `pdf` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Este es el path del pdf que se genera cuando se paga la reserva',
  `language_id` int(11) DEFAULT NULL,
  `internal_closed` tinyint(4) NOT NULL DEFAULT '0',
  `confirmation_attempts` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `user_reservations`
--
ALTER TABLE `user_reservations`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD KEY `user_reservations_user_id_foreign` (`user_id`) USING BTREE,
  ADD KEY `user_reservations_excurtion_id_foreign` (`excurtion_id`) USING BTREE,
  ADD KEY `user_reservations_reservation_status_id_foreign` (`reservation_status_id`) USING BTREE;

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `user_reservations`
--
ALTER TABLE `user_reservations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
