<?php
require_once 'config/db.php';
require_once 'includes/auth_helper.php';

check_login();

$user_id = $_SESSION['user_id'];
$poll_id = $_POST['poll_id'] ?? null;
$option_id = $_POST['option_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $poll_id && $option_id) {
    try {
        $stmt = $pdo->prepare("INSERT INTO poll_votes (poll_id, option_id, user_id) VALUES (?, ?, ?)");
        $stmt->execute([$poll_id, $option_id, $user_id]);
    } catch (PDOException $e) {
        // Unique constraint violation means the user already voted - ignore silently.
    }
}

header("Location: /polls.php");
exit;
