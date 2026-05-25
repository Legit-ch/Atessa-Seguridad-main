<?php
/**
 * Configuración centralizada de base de datos
 */

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    /**
     * Obtiene instancia singleton de PDO
     */
    public static function getInstance(): PDO
    {
        if (self::$instance instanceof PDO) {
            return self::$instance;
        }

        self::$instance = self::connect();
        return self::$instance;
    }

    /**
     * Conecta a la base de datos
     */
    private static function connect(): PDO
    {
        $dbHost = self::getEnv('DB_HOST', '127.0.0.1');
        $dbName = self::getEnv('DB_NAME', 'atessa_security');
        $dbUser = self::getEnv('DB_USER', 'root');
        $dbPass = self::getEnv('DB_PASS', '');
        $dbCharset = self::getEnv('DB_CHARSET', 'utf8mb4');

        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $dbHost, $dbName, $dbCharset);

        try {
            $pdo = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            return $pdo;
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene variable de entorno
     */
    private static function getEnv(string $name, string $default = ''): string
    {
        $value = getenv($name);
        if ($value !== false) {
            return $value;
        }
        return $_ENV[$name] ?? $default;
    }
}
