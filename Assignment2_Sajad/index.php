<?php
require __DIR__ . '/../../../autoload.php';

use Application\Mail;
use Application\Database;
use Application\Page;
use Application\Verifier;

$database = new Database('prod');
$page = new Page();
$mail = new Mail($database->getDb());

$verifier = new Verifier(); //create a new Verifier instance from the startfiles verifier.class.php
$verifier->decode($_SERVER['HTTP_AUTHORIZATION'] ?? ''); // decode the token from auth header  

// returns 401 if token is missing or invalid
if (empty($verifier->userId)) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (array_key_exists('name', $data) && array_key_exists('message', $data)) {
        //Admin can post for any of the regular users and the regular users use their own userid and only post for themselves
        $userId = $verifier->userId;
        $id = $mail->createMail($data['name'], $data['message'], $userId);
        $page->item(["id" => $id]);
    } else {
        $page->badRequest();
    }

} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($verifier->role === 'admin') {
        //Admin sees all mail regardless of userid in token
        $page->item($mail->listMail());
    } else {
        //regular user sees only their own mail based on userid in token
        $page->item($mail->listMail($verifier->userId));
    }
} else {
    $page->badRequest();
}