<?php
/**
 * Servicio de contactos (lógica de negocio)
 */

namespace App\Services;

use App\Repositories\ContactRepository;

class ContactService
{
    private ContactRepository $repository;
    private string $receivingEmail;

    public function __construct()
    {
        $this->repository = new ContactRepository();
        $this->receivingEmail = $_ENV['ADMIN_EMAIL'] ?? 'info@atessatechnologies.com';
    }

    /**
     * Crear nuevo contacto con validación
     */
    public function create(array $data): array
    {
        // Sanitizar
        $data = [
            'name' => filter_var(trim($data['name'] ?? ''), FILTER_SANITIZE_STRING),
            'email' => filter_var(trim($data['email'] ?? ''), FILTER_VALIDATE_EMAIL),
            'subject' => filter_var(trim($data['subject'] ?? ''), FILTER_SANITIZE_STRING),
            'message' => filter_var(trim($data['message'] ?? ''), FILTER_SANITIZE_STRING),
        ];

        // Validar
        $errors = $this->validate($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        try {
            // Guardar en BD
            $contact = $this->repository->create($data);

            // Enviar email
            $this->sendEmail($contact);

            return ['success' => true, 'message' => 'Su mensaje ha sido recibido.', 'data' => $contact];
        } catch (\Exception $e) {
            error_log('Error creating contact: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error al enviar su mensaje.'];
        }
    }

    /**
     * Validar datos de contacto
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

        if (empty($data['subject'])) {
            $errors[] = 'El asunto es requerido';
        }

        if (empty($data['message'])) {
            $errors[] = 'El mensaje es requerido';
        }

        return $errors;
    }

    /**
     * Enviar email de confirmación
     */
    private function sendEmail(array $contact): bool
    {
        $subject = "Contacto desde Atessa Technologies: " . $contact['subject'];
        $body = "Nuevo mensaje de contacto desde el sitio web de Atessa Technologies\n\n"
            . "Nombre: " . $contact['name'] . "\n"
            . "Email: " . $contact['email'] . "\n"
            . "Asunto: " . $contact['subject'] . "\n"
            . "Mensaje:\n" . $contact['message'] . "\n\n"
            . "---\n"
            . "Enviado desde: " . $_SERVER['HTTP_HOST'] . "\n"
            . "IP del remitente: " . $_SERVER['REMOTE_ADDR'] . "\n"
            . "Fecha: " . date('Y-m-d H:i:s') . "\n";

        $headers = implode("\r\n", [
            "From: Atessa Technologies <no-reply@atessatechnologies.com>",
            "Reply-To: " . $contact['email'],
            "Content-Type: text/plain; charset=UTF-8",
            "X-Mailer: Atessa Technologies API"
        ]);

        return mail($this->receivingEmail, $subject, $body, $headers);
    }

    /**
     * Obtener todos los contactos
     */
    public function getAll(int $limit = 100): array
    {
        return $this->repository->getAll($limit);
    }
}
