<?php
/**
 * Controlador de contactos
 */

namespace App\Controllers;

use App\Services\ContactService;

class ContactController
{
    private ContactService $service;

    public function __construct()
    {
        $this->service = new ContactService();
    }

    /**
     * POST /api/contacts - Crear contacto
     */
    public function store(): void
    {
        $data = $this->getPostData();
        $result = $this->service->create($data);
        $this->respondJson($result);
    }

    /**
     * GET /api/contacts - Obtener contactos (requiere auth)
     */
    public function index(): void
    {
        $contacts = $this->service->getAll(100);
        $this->respondJson(['success' => true, 'data' => $contacts]);
    }

    /**
     * Obtener datos POST
     */
    private function getPostData(): array
    {
        return $_POST;
    }

    /**
     * Responder con JSON
     */
    private function respondJson(array $data): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
