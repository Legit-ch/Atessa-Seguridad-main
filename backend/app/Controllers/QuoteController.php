<?php
/**
 * Controlador de cotizaciones
 */

namespace App\Controllers;

use App\Services\QuoteService;

class QuoteController
{
    private QuoteService $service;

    public function __construct()
    {
        $this->service = new QuoteService();
    }

    /**
     * POST /api/quotes - Crear cotización
     */
    public function store(): void
    {
        $data = $this->getPostData();
        $result = $this->service->create($data);
        $this->respondJson($result);
    }

    /**
     * GET /api/quotes - Obtener cotizaciones (requiere auth)
     */
    public function index(): void
    {
        $quotes = $this->service->getAll(100);
        $this->respondJson(['success' => true, 'data' => $quotes]);
    }

    /**
     * Obtener datos POST como array
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
