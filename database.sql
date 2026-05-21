-- Base de datos para Atessa Technologies
CREATE DATABASE IF NOT EXISTS `atessa_security` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `atessa_security`;

CREATE TABLE IF NOT EXISTS `quotes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `property_type` VARCHAR(120) NOT NULL,
  `service` VARCHAR(120) NOT NULL,
  `urgency` VARCHAR(60) NOT NULL,
  `message` TEXT,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` TEXT,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `contacts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `subject` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` TEXT,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
