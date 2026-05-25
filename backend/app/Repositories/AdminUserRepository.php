<?php
/**
 * Repository para usuarios admin (acceso a datos)
 */

namespace App\Repositories;

use App\Config\Database;
use PDO;

class AdminUserRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    /**
     * Obtener usuario por username
     */
    public function findByUsername(string $username): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM admin_users WHERE username = :username AND is_active = 1');
        $stmt->execute([':username' => $username]);
        return $stmt->fetch() ?: [];
    }

    /**
     * Obtener usuario por ID
     */
    public function findById(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM admin_users WHERE id = :id AND is_active = 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: [];
    }

    /**
     * Obtener todos los usuarios admin
     */
    public function getAll(): array
    {
        $stmt = $this->pdo->prepare('SELECT id, username, email, role, is_active, created_at, last_login FROM admin_users ORDER BY created_at DESC');
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Crear nuevo usuario admin
     */
    public function create(array $data): array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO admin_users (username, password_hash, email, role) VALUES (:username, :password_hash, :email, :role)'
        );

        $stmt->execute([
            ':username' => $data['username'],
            ':password_hash' => $data['password_hash'],
            ':email' => $data['email'] ?? null,
            ':role' => $data['role'] ?? 'admin',
        ]);

        return $this->findById($this->pdo->lastInsertId());
    }

    /**
     * Actualizar last_login
     */
    public function updateLastLogin(int $userId): bool
    {
        $stmt = $this->pdo->prepare('UPDATE admin_users SET last_login = NOW() WHERE id = :id');
        return $stmt->execute([':id' => $userId]);
    }
}
