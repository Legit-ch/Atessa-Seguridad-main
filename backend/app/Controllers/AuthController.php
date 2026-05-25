<?php
/**
 * Controlador de autenticación
 */

namespace App\Controllers;

use App\Services\AuthService;

class AuthController
{
    private AuthService $service;

    public function __construct()
    {
        $this->service = new AuthService();
    }

    /**
     * POST /api/auth/login - Login
     */
    public function login(): void
    {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $this->respondJson(['success' => false, 'message' => 'Usuario y contraseña requeridos']);
            return;
        }

        $result = $this->service->login($username, $password);
        $this->respondJson($result);
    }

    /**
     * GET /api/auth/user - Obtener usuario actual (requiere auth)
     */
    public function getCurrentUser(): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->respondJson(['success' => false, 'message' => 'No autenticado'], 401);
            return;
        }

        $user = $this->service->getUserById($_SESSION['user_id']);
        if (empty($user)) {
            $this->respondJson(['success' => false, 'message' => 'Usuario no encontrado'], 404);
            return;
        }

        $this->respondJson(['success' => true, 'data' => $user]);
    }

    /**
     * Responder con JSON
     */
    private function respondJson(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
