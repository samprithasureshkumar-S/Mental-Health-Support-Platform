<?php
require_once '../config/db.php';
require_once '../includes/auth_helper.php';

restrict_to_role('user');

$entry_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];

if ($entry_id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM journal_entries WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$entry_id, $user_id])) {
            header("Location: /user/journal.php?success=Journal entry deleted.");
            exit;
        }
    } catch (PDOException $e) {
        header("Location: /user/journal.php");
        exit;
    }
}

header("Location: /user/journal.php");
exit;
