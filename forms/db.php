<?php
/**
 * Database connection and schema initialization for Atessa Technologies.
 *
 * Modifique las constantes de conexión si su servidor MySQL usa otras credenciales.
 */

// Configuración de base de datos por defecto.
// En producción debe configurar estas variables mediante el servidor o un archivo .env fuera del control de versiones.
const DB_HOST = '127.0.0.1';
const DB_NAME = 'atessa_security';
const DB_USER = 'root';
const DB_PASS = '';
const DB_CHARSET = 'utf8mb4';

loadEnvFile(__DIR__ . '/../.env');

/**
 * Devuelve una variable de entorno con fallback.
 *
 * @param string $name
 * @param string $default
 * @return string
 */
function getEnvValue(string $name, string $default = ''): string
{
    $value = getenv($name);

    if ($value !== false) {
        return $value;
    }

    if (array_key_exists($name, $_ENV) && $_ENV[$name] !== '') {
        return $_ENV[$name];
    }

    return $default;
}

/**
 * Carga un archivo .env simple si existe.
 *
 * @param string $filePath
 * @return void
 */
function loadEnvFile(string $filePath): void
{
    if (!is_readable($filePath)) {
        return;
    }

    foreach (file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);

        if ($line === '' || $line[0] === '#') {
            continue;
        }

        if (strpos($line, '=') === false) {
            continue;
        }

        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        if ($name === '' || getenv($name) !== false) {
            continue;
        }

        putenv("$name=$value");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

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

    $dbHost = getEnvValue('DB_HOST', DB_HOST);
    $dbName = getEnvValue('DB_NAME', DB_NAME);
    $dbUser = getEnvValue('DB_USER', DB_USER);
    $dbPass = getEnvValue('DB_PASS', DB_PASS);
    $dbCharset = getEnvValue('DB_CHARSET', DB_CHARSET);

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $dbHost, $dbName, $dbCharset);

    try {
        $pdo = new PDO($dsn, $dbUser, $dbPass, [
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

    // Tabla para usuarios administradores (gestión de acceso)
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS admin_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            email VARCHAR(255),
            role VARCHAR(50) NOT NULL DEFAULT "admin",
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            last_login DATETIME NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
}
