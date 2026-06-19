<?php
require_once '../config/db.php';
require_once '../includes/auth_helper.php';
require_once '../includes/chatbot.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['reply' => '']);
    exit;
}

$message = trim($_POST['message'] ?? '');
$reply = $message !== '' ? get_bot_reply($message) : "Could you share a bit more about how you're feeling?";

echo json_encode(['reply' => $reply]);
