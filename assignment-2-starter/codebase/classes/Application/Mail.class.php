<?php
namespace Application;

// required for using the PDO::FETCH_ASSOC constant
use PDO;

class Mail
{
    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function createMail($name, $message, $userId)
    {
        $stmt = $this->db->prepare("INSERT INTO prod.mail (name, message, userId) VALUES (:name, :message, :userId)");
        $stmt->execute(['name' => $name, 'message' => $message, 'userId' => $userId]);

        return $this->db->lastInsertId();
    }

    public function listMail($userId = null)
    {
        if ($userId === null) {
            // Admin return all mail
            $result = $this->db->query("SELECT id, name, message, userId FROM prod.mail ORDER BY id");
        } else {
            // Regular user: return only their mail
            $stmt = $this->db->prepare("SELECT id, name, message, userId FROM prod.mail WHERE userId = :userId ORDER BY id");
            $stmt->execute(['userId' => $userId]);
            $result = $stmt;
        }

        return $result->fetchAll(PDO::FETCH_ASSOC);
    }
}