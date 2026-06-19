<?php
require_once '../config/db.php';
require_once '../includes/auth_helper.php';

restrict_to_role('admin');

$action = $_GET['action'] ?? '';
$post_id = $_GET['id'] ?? null;

if ($post_id && ($action === 'approve' || $action === 'reject')) {
    $status = ($action === 'approve') ? 'approved' : 'rejected';
    try {
        $stmt = $pdo->prepare("UPDATE posts SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $post_id])) {
            header("Location: /admin/dashboard.php?success=Post status updated to " . $status);
            exit;
        } else {
            header("Location: /admin/dashboard.php?error=Failed to update post status.");
            exit;
        }
    } catch (PDOException $e) {
        header("Location: /admin/dashboard.php?error=" . urlencode($e->getMessage()));
        exit;
    }
}

header("Location: /admin/dashboard.php");
exit;
?>
