<?php
// Cerrar sesión administrativa
require_once __DIR__ . '/forms/db.php';

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

// Destruir sesión de forma segura
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'] ?? '',
        $params['secure'] ?? false, $params['httponly'] ?? false
    );
}
session_destroy();
header('Location: admin_login.php');
exit;
