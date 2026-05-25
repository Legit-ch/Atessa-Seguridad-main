<?php
/**
 * Servicio de autenticación (lógica de negocio)
 */

namespace App\Services;

use App\Repositories\AdminUserRepository;

class AuthService
{
    private AdminUserRepository $repository;

    public function __construct()
    {
        $this->repository = new AdminUserRepository();
    }

    /**
     * Autenticar usuario
     */
    public function login(string $username, string $password): array
    {
        $user = $this->repository->findByUsername($username);

        if (empty($user) || !password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Credenciales inválidas'];
        }

        // Actualizar last_login
        $this->repository->updateLastLogin($user['id']);

        return [
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ];
    }

    /**
     * Obtener usuario por ID
     */
    public function getUserById(int $id): array
    {
        return $this->repository->findById($id) ?: [];
    }

    /**
     * Obtener todos los usuarios
     */
    public function getAllUsers(): array
    {
        return $this->repository->getAll();
    }
}
