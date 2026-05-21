<?php
/**
 * Database connection and schema initialization for Atessa Technologies.
 *
 * Modifique las constantes de conexión si su servidor MySQL usa otras credenciales.
 */

// Configuración de base de datos
const DB_HOST = '127.0.0.1';
const DB_NAME = 'atessa_security';
const DB_USER = 'root';
const DB_PASS = '';
const DB_CHARSET = 'utf8mb4';

/**
 * Devuelve una conexión PDO reutilizable.
 *
 * @return PDO
 * @throws PDOException
 */
function getDbConnection(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        initializeDatabaseSchema($pdo);

        return $pdo;
    } catch (PDOException $exception) {
        // Si la base de datos no existe, intentamos crearla automáticamente.
        if (stripos($exception->getMessage(), 'Unknown database') !== false || stripos($exception->getMessage(), 'database .* does not exist') !== false) {
            createDatabaseIfMissing();
            return getDbConnection();
        }

        error_log('Database connection failed: ' . $exception->getMessage());
        throw $exception;
    }
}

/**
 * Crea la base de datos si no existe.
 *
 * @return void
 */
function createDatabaseIfMissing(): void
{
    $dsn = sprintf('mysql:host=%s;charset=%s', DB_HOST, DB_CHARSET);

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        $pdo->exec(sprintf(
            'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET %s COLLATE %s_unicode_ci',
            DB_NAME,
            DB_CHARSET,
            DB_CHARSET
        ));
    } catch (PDOException $exception) {
        error_log('Database creation failed: ' . $exception->getMessage());
        throw $exception;
    }
}

/**
 * Crea las tablas necesarias si no existen.
 *
 * @param PDO $pdo
 * @return void
 */
function initializeDatabaseSchema(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS quotes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            email VARCHAR(150) NOT NULL,
            phone VARCHAR(50) NOT NULL,
            property_type VARCHAR(120) NOT NULL,
            service VARCHAR(120) NOT NULL,
            urgency VARCHAR(60) NOT NULL,
            message TEXT,
            ip_address VARCHAR(45) NOT NULL,
            user_agent TEXT,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS contacts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            email VARCHAR(150) NOT NULL,
            subject VARCHAR(150) NOT NULL,
            message TEXT NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            user_agent TEXT,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
}
