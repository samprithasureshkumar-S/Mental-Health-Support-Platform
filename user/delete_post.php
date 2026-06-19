<?php
require_once '../config/db.php';
require_once '../includes/auth_helper.php';

restrict_to_role('user');

$post_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];

if ($post_id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$post_id, $user_id])) {
            header("Location: /user/dashboard.php?success=Post deleted successfully.");
            exit;
        }
    } catch (PDOException $e) {
        header("Location: /user/dashboard.php?error=Failed to delete post.");
        exit;
    }
}

header("Location: /user/dashboard.php");
exit;
?>
