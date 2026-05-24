<?php
/**
 * Página de login para el panel de administración.
 */
require_once __DIR__ . '/forms/db.php';

// Sesión segura
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

// Si ya está autenticado, redirigir
if (!empty($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    header('Location: admin.php');
    exit;
}

$error = '';
// Generar token CSRF simple
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    // Verificar CSRF
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(400);
        $error = 'Token inválido.';
    } else {
        // Rate limit simple por sesión
        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
        if ($_SESSION['login_attempts'] > 10) {
            http_response_code(429);
            $error = 'Demasiados intentos. Intente más tarde.';
        } else {
            $envUser = getEnvValue('ADMIN_USER');
            $hash = getEnvValue('ADMIN_PASS_HASH');
            if ($user === $envUser && $hash !== '' && password_verify($pass, $hash)) {
                session_regenerate_id(true);
                $_SESSION['is_admin'] = true;
                unset($_SESSION['login_attempts']);
                header('Location: admin.php');
                exit;
            }
            $error = 'Credenciales inválidas.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login - Admin</title>
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <style>body{background:#f7f9fc;color:#2c3e50} .login-box{max-width:420px;margin:80px auto;padding:24px;background:#fff;border-radius:6px;box-shadow:0 6px 24px rgba(0,0,0,.08)}</style>
</head>
<body>
  <div class="container">
    <div class="login-box">
      <h3 class="mb-3">Iniciar sesión</h3>
      <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form method="post" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <div class="mb-3">
          <label class="form-label">Usuario</label>
          <input name="user" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Contraseña</label>
          <input name="pass" type="password" class="form-control" required>
        </div>
        <div class="d-grid">
          <button class="btn btn-primary">Entrar</button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
