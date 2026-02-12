<?php
// Sajad Mirpour 
// February 11 2026
// Mail.php
// This file implements the Mail class which provides methods to manage mail items in the database
namespace Application;
use PDO;

class Mail {
    protected PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    // Create mail
    public function createMail($subject, $body) {
        $stmt = $this->pdo->prepare("INSERT INTO mail (subject, body) VALUES (?, ?) RETURNING id");
        $stmt->execute([$subject, $body]);

        return $stmt->fetchColumn();
    }
    // Get mail by ID
    public function getMail($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM mail WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: false;
    }
    // Get all mail
    public function getAllMail() {
        $stmt = $this->pdo->query("SELECT * FROM mail");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Update mail
    public function updateMail($id, $subject, $body) {
        $stmt = $this->pdo->prepare("UPDATE mail SET subject = ?, body = ? WHERE id = ?");
        $stmt->execute([$subject, $body, $id]);

        return $stmt->rowCount() > 0;
    }
    // Delete mail
    public function deleteMail($id) {
        $stmt = $this->pdo->prepare("DELETE FROM mail WHERE id = ?");
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }
}