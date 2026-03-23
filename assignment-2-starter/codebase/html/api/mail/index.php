// Sajad Mirpour
// March, 21, 2026
// index.php
<?php
require __DIR__ . '/../../../autoload.php';

use Application\Mail;
use Application\Database;
use Application\Page;
use Application\Verifier;

$database = new Database('prod');
$page = new Page();
$mail = new Mail($database->getDb());

$verifier = new Verifier();
$verifier->decode($_SERVER['HTTP_AUTHORIZATION'] ?? '');

// returns 401 if token is missing or invalid
if (empty($verifier->userId)) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (array_key_exists('name', $data) && array_key_exists('message', $data)) {
        // Admin can post on behalf of anyone; regular users use their own userId
        $userId = $verifier->userId;
        $id = $mail->createMail($data['name'], $data['message'], $userId);
        $page->item(["id" => $id]);
    } else {
        $page->badRequest();
    }

} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($verifier->role === 'admin') {
        //Admin sees all mail regardless of userId in token
        $page->item($mail->listMail());
    } else {
        //regular user sees only their own mail based on userId in token
        $page->item($mail->listMail($verifier->userId));
    }
} else {
    $page->badRequest();
}