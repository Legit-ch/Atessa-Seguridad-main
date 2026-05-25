<?php
/**
 * Repository para contactos (acceso a datos)
 */

namespace App\Repositories;

use App\Config\Database;
use PDO;

class ContactRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    /**
     * Guardar nuevo contacto
     */
    public function create(array $data): array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO contacts (name, email, subject, message, ip_address, user_agent)
             VALUES (:name, :email, :subject, :message, :ip_address, :user_agent)'
        );

        $stmt->execute([
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':subject' => $data['subject'],
            ':message' => $data['message'],
            ':ip_address' => $data['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? 'N/A',
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
        ]);

        return $this->findById($this->pdo->lastInsertId());
    }

    /**
     * Obtener contacto por ID
     */
    public function findById(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM contacts WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: [];
    }

    /**
     * Obtener todos los contactos (con límite)
     */
    public function getAll(int $limit = 100): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM contacts ORDER BY created_at DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
