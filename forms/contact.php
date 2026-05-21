<?php
/**
 * Formulario de Contacto - Atessa Technologies
 * Procesa mensajes de contacto del sitio web
 */

// Configuración de email
$receiving_email_address = 'info@atessatechnologies.com';

require_once __DIR__ . '/db.php';

// Verificar que sea una petición POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    header('Content-Type: application/json; charset=UTF-8');
    
    // Sanitizar y validar datos de entrada
    $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $subject = filter_var(trim($_POST['subject']), FILTER_SANITIZE_STRING);
    $message = filter_var(trim($_POST['message']), FILTER_SANITIZE_STRING);
    
    // Validaciones
    $errors = array();
    
    if (empty($name)) {
        $errors[] = "El nombre es requerido";
    }
    
    if (empty($email) || !$email) {
        $errors[] = "Email válido es requerido";
    }
    
    if (empty($subject)) {
        $errors[] = "El asunto es requerido";
    }
    
    if (empty($message)) {
        $errors[] = "El mensaje es requerido";
    }
    
    // Si hay errores, mostrar mensaje
    if (!empty($errors)) {
        echo json_encode(['status' => 'error', 'message' => implode(', ', $errors)]);
        exit;
    }

    // Guardar el contacto en la base de datos
    $dbSaved = false;
    try {
        $pdo = getDbConnection();
        $insert = $pdo->prepare(
            'INSERT INTO contacts (name, email, subject, message, ip_address, user_agent)
             VALUES (:name, :email, :subject, :message, :ip_address, :user_agent)'
        );
        $insert->execute([
            ':name' => $name,
            ':email' => $email,
            ':subject' => $subject,
            ':message' => $message,
            ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
        ]);
        $dbSaved = true;
    } catch (PDOException $e) {
        error_log('DB insert error (contact): ' . $e->getMessage());
    }
    
    // Preparar el email
    $email_subject = "Contacto desde Atessa Technologies: " . $subject;
    
    $email_body = "Nuevo mensaje de contacto desde el sitio web de Atessa Technologies\n\n";
    $email_body .= "Nombre: " . $name . "\n";
    $email_body .= "Email: " . $email . "\n";
    $email_body .= "Asunto: " . $subject . "\n";
    $email_body .= "Mensaje:\n" . $message . "\n\n";
    $email_body .= "---\n";
    $email_body .= "Enviado desde: " . $_SERVER['HTTP_HOST'] . "\n";
    $email_body .= "IP del remitente: " . $_SERVER['REMOTE_ADDR'] . "\n";
    $email_body .= "Fecha: " . date('Y-m-d H:i:s') . "\n";
    
    // Headers del email
    $headers = array();
    $headers[] = "From: Atessa Technologies <no-reply@atessatechnologies.com>";
    $headers[] = "Reply-To: " . $email;
    $headers[] = "Content-Type: text/plain; charset=UTF-8";
    $headers[] = "X-Mailer: Atessa Technologies Contact Form";
    
    // Enviar email
    $mailSent = mail($receiving_email_address, $email_subject, $email_body, implode("\r\n", $headers));

    if ($mailSent || $dbSaved) {
        if (!$mailSent) {
            error_log('Mail send failed for contact form from ' . $email);
        }

        echo json_encode([
            'status' => 'success', 
            'message' => 'Su mensaje ha sido recibido. Hemos guardado su consulta y nos contactaremos pronto.'
        ]);
    } else {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Error al enviar su mensaje. Intente nuevamente o contáctenos directamente por teléfono.'
        ]);
    }
    
} else {
    // Si no es POST, redirigir
    header('Location: ../index.html');
    exit;
}
?>
