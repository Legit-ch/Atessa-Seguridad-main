<?php
/**
 * Formulario de Cotización - Atessa Technologies
 * Procesa solicitudes de cotización de servicios de seguridad
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
    $phone = filter_var(trim($_POST['phone']), FILTER_SANITIZE_STRING);
    $property_type = filter_var(trim($_POST['property_type']), FILTER_SANITIZE_STRING);
    $service = filter_var(trim($_POST['service']), FILTER_SANITIZE_STRING);
    $urgency = filter_var(trim($_POST['urgency']), FILTER_SANITIZE_STRING);
    $message = filter_var(trim($_POST['message']), FILTER_SANITIZE_STRING);
    
    // Validaciones
    $errors = array();
    
    if (empty($name)) {
        $errors[] = "El nombre es requerido";
    }
    
    if (empty($email) || !$email) {
        $errors[] = "Email válido es requerido";
    }
    
    if (empty($phone)) {
        $errors[] = "El teléfono es requerido";
    }
    
    if (empty($property_type)) {
        $errors[] = "El tipo de propiedad es requerido";
    }
    
    if (empty($service)) {
        $errors[] = "Debe seleccionar un servicio";
    }
    
    if (empty($urgency)) {
        $errors[] = "Debe indicar la urgencia";
    }
    
    // Si hay errores, mostrar mensaje
    if (!empty($errors)) {
        echo json_encode(['status' => 'error', 'message' => implode(', ', $errors)]);
        exit;
    }

    // Guardar la solicitud en la base de datos
    $dbSaved = false;
    try {
        $pdo = getDbConnection();
        $insert = $pdo->prepare(
            'INSERT INTO quotes (name, email, phone, property_type, service, urgency, message, ip_address, user_agent)
             VALUES (:name, :email, :phone, :property_type, :service, :urgency, :message, :ip_address, :user_agent)'
        );
        $insert->execute([
            ':name' => $name,
            ':email' => $email,
            ':phone' => $phone,
            ':property_type' => $property_type,
            ':service' => $service,
            ':urgency' => $urgency,
            ':message' => $message,
            ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
        ]);
        $dbSaved = true;
    } catch (PDOException $e) {
        error_log('DB insert error (appointment): ' . $e->getMessage());
    }

    // Preparar el email
    $email_subject = "Nueva Solicitud de Cotización - Atessa Technologies";
    
    $email_body = "Nueva solicitud de cotización desde el sitio web de Atessa Technologies\n\n";
    $email_body .= "=== DATOS DEL CLIENTE ===\n";
    $email_body .= "Nombre: " . $name . "\n";
    $email_body .= "Email: " . $email . "\n";
    $email_body .= "Teléfono: " . $phone . "\n\n";
    
    $email_body .= "=== DETALLES DEL PROYECTO ===\n";
    $email_body .= "Tipo de Propiedad: " . $property_type . "\n";
    $email_body .= "Servicio Solicitado: " . $service . "\n";
    $email_body .= "Urgencia: " . $urgency . "\n\n";
    
    if (!empty($message)) {
        $email_body .= "=== MENSAJE ADICIONAL ===\n";
        $email_body .= $message . "\n\n";
    }
    
    $email_body .= "=== INFORMACIÓN TÉCNICA ===\n";
    $email_body .= "Enviado desde: " . $_SERVER['HTTP_HOST'] . "\n";
    $email_body .= "IP del cliente: " . $_SERVER['REMOTE_ADDR'] . "\n";
    $email_body .= "Fecha y hora: " . date('Y-m-d H:i:s') . "\n";
    $email_body .= "User Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\n";
    
    // Headers del email
    $headers = array();
    $headers[] = "From: Atessa Technologies <no-reply@atessatechnologies.com>";
    $headers[] = "Reply-To: " . $email;
    $headers[] = "Content-Type: text/plain; charset=UTF-8";
    $headers[] = "X-Mailer: Atessa Technologies Quote Form";
    $headers[] = "X-Priority: 2"; // Alta prioridad para cotizaciones
    
    // Enviar email
    $mailSent = mail($receiving_email_address, $email_subject, $email_body, implode("\r\n", $headers));

    if ($mailSent || $dbSaved) {
        if (!$mailSent) {
            error_log('Mail send failed for appointment request from ' . $email);
        }

        echo json_encode([
            'status' => 'success', 
            'message' => 'Su solicitud de cotización ha sido recibida. Hemos guardado su solicitud y nos contactaremos pronto.'
        ]);
    } else {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Error al procesar la cotización. Por favor llámenos directamente al +504 2239-4200.'
        ]);
    }
    
} else {
    // Si no es POST, redirigir
    header('Location: ../index.html');
    exit;
}
?>
