<?php
/**
 * Panel de administración básico para revisar solicitudes de cotización y mensajes de contacto.
 * Protegido mediante autenticación básica HTTP.
 */

require_once __DIR__ . '/forms/db.php';

// Sesión segura para la administración
session_set_cookie_params([
  'lifetime' => 0,
  'path' => '/',
  'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
  'httponly' => true,
  'samesite' => 'Lax'
]);
session_start();

// Requiere que el administrador y el hash estén configurados en el entorno
$adminUser = getEnvValue('ADMIN_USER');
$adminPassHash = getEnvValue('ADMIN_PASS_HASH');
if ($adminUser === '' || $adminPassHash === '') {
  http_response_code(500);
  echo 'Error de configuración: faltan credenciales de administrador.';
  exit;
}

// En producción, forzar HTTPS (o comprobar X-Forwarded-Proto detrás de proxy)
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
  (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
if (!$isHttps) {
  http_response_code(403);
  echo 'HTTPS requerido.';
  exit;
}

// Verificar sesión administrativa
if (empty($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
  header('Location: admin_login.php');
  exit;
}

try {
    $pdo = getDbConnection();
    $quotes = $pdo->query('SELECT * FROM quotes ORDER BY created_at DESC LIMIT 100')->fetchAll();
    $contacts = $pdo->query('SELECT * FROM contacts ORDER BY created_at DESC LIMIT 100')->fetchAll();
} catch (PDOException $e) {
    http_response_code(500);
    echo '<h1>Error de conexión a la base de datos</h1>';
    echo '<p>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Atessa Technologies</title>
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f7f9fc; color: #2c3e50; }
    .admin-header { margin: 30px 0; }
    .table-responsive { margin-bottom: 40px; }
  </style>
</head>
<body>
  <div class="container">
    <div class="admin-header text-center">
      <h1>Panel de Administración</h1>
      <p class="text-muted">Solicitudes de cotización y mensajes de contacto registrados en la base de datos.</p>
      <div class="mt-2"><a href="admin_logout.php" class="btn btn-sm btn-outline-secondary">Cerrar sesión</a></div>
    </div>

    <div class="table-responsive">
      <h2>Solicitudes de Cotización</h2>
      <table class="table table-striped table-bordered table-hover">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Teléfono</th>
            <th>Propiedad</th>
            <th>Servicio</th>
            <th>Urgencia</th>
            <th>Mensaje</th>
            <th>IP</th>
            <th>Fecha</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($quotes as $quote): ?>
            <tr>
              <td><?= htmlspecialchars($quote['id']) ?></td>
              <td><?= htmlspecialchars($quote['name']) ?></td>
              <td><?= htmlspecialchars($quote['email']) ?></td>
              <td><?= htmlspecialchars($quote['phone']) ?></td>
              <td><?= htmlspecialchars($quote['property_type']) ?></td>
              <td><?= htmlspecialchars($quote['service']) ?></td>
              <td><?= htmlspecialchars($quote['urgency']) ?></td>
              <td><?= nl2br(htmlspecialchars($quote['message'])) ?></td>
              <td><?= htmlspecialchars($quote['ip_address']) ?></td>
              <td><?= htmlspecialchars($quote['created_at']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="table-responsive">
      <h2>Mensajes de Contacto</h2>
      <table class="table table-striped table-bordered table-hover">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Asunto</th>
            <th>Mensaje</th>
            <th>IP</th>
            <th>Fecha</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($contacts as $contact): ?>
            <tr>
              <td><?= htmlspecialchars($contact['id']) ?></td>
              <td><?= htmlspecialchars($contact['name']) ?></td>
              <td><?= htmlspecialchars($contact['email']) ?></td>
              <td><?= htmlspecialchars($contact['subject']) ?></td>
              <td><?= nl2br(htmlspecialchars($contact['message'])) ?></td>
              <td><?= htmlspecialchars($contact['ip_address']) ?></td>
              <td><?= htmlspecialchars($contact['created_at']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
