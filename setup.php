<?php
/**
 * Script para crear la base de datos y tablas automáticamente.
 * Ejecuta: php setup.php
 */

// Intenta conectarse sin especificar base de datos
$dsn = 'mysql:host=127.0.0.1;charset=utf8mb4';
$user = 'root';
$pass = isset($argv[1]) ? $argv[1] : ''; // Contraseña como parámetro, vacía por defecto

try {
    $pdo = new PDO($dsn, $user, $pass);
    echo "[✓] Conectado a MySQL exitosamente\n";

    // Crear base de datos
    $pdo->exec('CREATE DATABASE IF NOT EXISTS `atessa_security` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    echo "[✓] Base de datos 'atessa_security' creada o ya existe\n";

    // Seleccionar la base de datos
    $pdo->exec('USE `atessa_security`');
    echo "[✓] Base de datos seleccionada\n";

    // Crear tabla de cotizaciones
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS `quotes` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    echo "[✓] Tabla 'quotes' creada o ya existe\n";

    // Crear tabla de contactos
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS `contacts` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(150) NOT NULL,
            `email` VARCHAR(150) NOT NULL,
            `subject` VARCHAR(150) NOT NULL,
            `message` TEXT NOT NULL,
            `ip_address` VARCHAR(45) NOT NULL,
            `user_agent` TEXT,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
    echo "[✓] Tabla 'contacts' creada o ya existe\n";

    echo "\n✅ Base de datos 'atessa_security' configurada correctamente.\n";
    echo "Usa las credenciales en forms/db.php para conectarte.\n";

} catch (PDOException $e) {
    echo "[✗] Error: " . $e->getMessage() . "\n";
    echo "\nSi MySQL tiene contraseña, ejecuta:\n";
    echo "  php setup.php \"tu_contraseña_aqui\"\n";
    echo "\nEjemplo:\n";
    echo "  php setup.php \"contraseña123\"\n\n";
    exit(1);
}
?>
