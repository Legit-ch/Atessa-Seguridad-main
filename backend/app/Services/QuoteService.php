<?php
/**
 * Servicio de cotizaciones (lógica de negocio)
 */

namespace App\Services;

use App\Repositories\QuoteRepository;

class QuoteService
{
    private QuoteRepository $repository;
    private string $receivingEmail;

    public function __construct()
    {
        $this->repository = new QuoteRepository();
        $this->receivingEmail = $_ENV['ADMIN_EMAIL'] ?? 'info@atessatechnologies.com';
    }

    /**
     * Crear nueva cotización con validación
     */
    public function create(array $data): array
    {
        // Sanitizar
        $data = [
            'name' => filter_var(trim($data['name'] ?? ''), FILTER_SANITIZE_STRING),
            'email' => filter_var(trim($data['email'] ?? ''), FILTER_VALIDATE_EMAIL),
            'phone' => filter_var(trim($data['phone'] ?? ''), FILTER_SANITIZE_STRING),
            'property_type' => filter_var(trim($data['property_type'] ?? ''), FILTER_SANITIZE_STRING),
            'service' => filter_var(trim($data['service'] ?? ''), FILTER_SANITIZE_STRING),
            'urgency' => filter_var(trim($data['urgency'] ?? ''), FILTER_SANITIZE_STRING),
            'message' => filter_var(trim($data['message'] ?? ''), FILTER_SANITIZE_STRING),
        ];

        // Validar
        $errors = $this->validate($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        try {
            // Guardar en BD
            $quote = $this->repository->create($data);

            // Enviar email
            $this->sendEmail($quote);

            return ['success' => true, 'message' => 'Su solicitud ha sido recibida.', 'data' => $quote];
        } catch (\Exception $e) {
            error_log('Error creating quote: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error al procesar la cotización.'];
        }
    }

    /**
     * Validar datos de cotización
     */
    private function validate(array $data): array
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = 'El nombre es requerido';
        }

        if (empty($data['email']) || !$data['email']) {
            $errors[] = 'Email válido es requerido';
        }

        if (empty($data['phone'])) {
            $errors[] = 'El teléfono es requerido';
        }

        if (empty($data['property_type'])) {
            $errors[] = 'El tipo de propiedad es requerido';
        }

        if (empty($data['service'])) {
            $errors[] = 'Debe seleccionar un servicio';
        }

        if (empty($data['urgency'])) {
            $errors[] = 'Debe indicar la urgencia';
        }

        return $errors;
    }

    /**
     * Enviar email de confirmación
     */
    private function sendEmail(array $quote): bool
    {
        $subject = "Nueva Solicitud de Cotización - Atessa Technologies";
        $body = $this->buildEmailBody($quote);
        $headers = $this->buildEmailHeaders($quote['email']);

        return mail($this->receivingEmail, $subject, $body, $headers);
    }

    /**
     * Construir cuerpo del email
     */
    private function buildEmailBody(array $quote): string
    {
        return "Nueva solicitud de cotización desde el sitio web de Atessa Technologies\n\n"
            . "=== DATOS DEL CLIENTE ===\n"
            . "Nombre: " . $quote['name'] . "\n"
            . "Email: " . $quote['email'] . "\n"
            . "Teléfono: " . $quote['phone'] . "\n\n"
            . "=== DETALLES DEL PROYECTO ===\n"
            . "Tipo de Propiedad: " . $quote['property_type'] . "\n"
            . "Servicio Solicitado: " . $quote['service'] . "\n"
            . "Urgencia: " . $quote['urgency'] . "\n\n"
            . "=== INFORMACIÓN TÉCNICA ===\n"
            . "Enviado desde: " . $_SERVER['HTTP_HOST'] . "\n"
            . "IP del cliente: " . $_SERVER['REMOTE_ADDR'] . "\n"
            . "Fecha y hora: " . date('Y-m-d H:i:s') . "\n";
    }

    /**
     * Construir headers del email
     */
    private function buildEmailHeaders(string $replyTo): string
    {
        return implode("\r\n", [
            "From: Atessa Technologies <no-reply@atessatechnologies.com>",
            "Reply-To: " . $replyTo,
            "Content-Type: text/plain; charset=UTF-8",
            "X-Mailer: Atessa Technologies API"
        ]);
    }

    /**
     * Obtener todas las cotizaciones
     */
    public function getAll(int $limit = 100): array
    {
        return $this->repository->getAll($limit);
    }
}
