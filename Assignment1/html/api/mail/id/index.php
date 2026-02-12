<?php
require '../../../vendor/autoload.php';
// Sajad Mirpour 
// February 11 2026
// index.php
// This file implements the API endpoints for managing mail items
use Application\Mail;
use Application\Page;

$dsn = "pgsql:host=" . getenv('DB_PROD_HOST') . ";dbname=" . getenv('DB_PROD_NAME');
try {
    $pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo "Database connection failed: " . $e->getMessage();
    exit;
}

$mail = new Mail($pdo);
$page = new Page();

// Extract ID from the URL
$uri = $_SERVER['REQUEST_URI'];
$parts = explode('/', trim($uri, '/'));
$id = (int) end($parts);

if ($id <= 0) {
    $page->badRequest();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $mail->getMail($id);
    if ($result) {
        $page->item($result);
    } else {
        $page->notFound();
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    if (!isset($data['subject']) || !isset($data['body'])) {
        $page->badRequest();
        exit;
    }

    $updated = $mail->updateMail($id, $data['subject'], $data['body']);
    if ($updated) {
        $page->item($mail->getMail($id));
    } else {
        $page->notFound();
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $deleted = $mail->deleteMail($id);
    if ($deleted) {
        http_response_code(200);
        echo json_encode(["message" => "Deleted"]);
    } else {
        $page->notFound();
    }
    exit;
}

$page->badRequest();
