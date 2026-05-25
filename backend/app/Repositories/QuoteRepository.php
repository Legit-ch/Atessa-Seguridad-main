<?php
/**
 * Repository para cotizaciones (acceso a datos)
 */

namespace App\Repositories;

use App\Config\Database;
use PDO;

class QuoteRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    /**
     * Guardar nueva cotización
     */
    public function create(array $data): array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO quotes (name, email, phone, property_type, service, urgency, message, ip_address, user_agent)
             VALUES (:name, :email, :phone, :property_type, :service, :urgency, :message, :ip_address, :user_agent)'
        );

        $stmt->execute([
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':phone' => $data['phone'],
            ':property_type' => $data['property_type'],
            ':service' => $data['service'],
            ':urgency' => $data['urgency'],
            ':message' => $data['message'] ?? '',
            ':ip_address' => $data['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? 'N/A',
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
        ]);

        return $this->findById($this->pdo->lastInsertId());
    }

    /**
     * Obtener cotización por ID
     */
    public function findById(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM quotes WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: [];
    }

    /**
     * Obtener todas las cotizaciones (con límite)
     */
    public function getAll(int $limit = 100): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM quotes ORDER BY created_at DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
