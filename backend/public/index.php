<?php
/**
 * Entry point de la API
 * Archivo: backend/public/index.php
 * 
 * Configurar el servidor web para que todas las peticiones vayan a este archivo:
 * 
 * Apache (.htaccess):
 *   RewriteEngine On
 *   RewriteCond %{REQUEST_FILENAME} !-f
 *   RewriteCond %{REQUEST_FILENAME} !-d
 *   RewriteRule ^ index.php [QSA,L]
 * 
 * Nginx:
 *   location /backend/public {
 *     try_files $uri /backend/public/index.php?$query_string;
 *   }
 */

// Cargar configuración de entorno
require_once __DIR__ . '/../../forms/db.php';
loadEnvFile(__DIR__ . '/../../.env');

// Establecer headers de seguridad y CORS
header('Access-Control-Allow-Origin: ' . ($_ENV['APP_CORS_ORIGIN'] ?? 'http://localhost'));
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// Responder a preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Autoload simple (reemplazar con Composer si es necesario)
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    if (strpos($class, $prefix) === 0) {
        $file = __DIR__ . '/../' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_readable($file)) {
            require_once $file;
        }
    }
});

// Parsear PATH_INFO
$pathInfo = $_SERVER['PATH_INFO'] ?? $_SERVER['REQUEST_URI'] ?? '/';
if (strpos($pathInfo, '/backend/public') === 0) {
    $pathInfo = substr($pathInfo, strlen('/backend/public'));
}
$pathInfo = parse_url($pathInfo, PHP_URL_PATH);

$method = $_SERVER['REQUEST_METHOD'];
$key = "$method $pathInfo";

// Cargar rutas
$routes = require_once __DIR__ . '/../routes/api.php';

// Buscar ruta
if (isset($routes[$key])) {
    call_user_func($routes[$key]);
} else {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Ruta no encontrada']);
}
