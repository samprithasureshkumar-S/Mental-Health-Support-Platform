<?php
require_once '../config/db.php';
require_once '../includes/auth_helper.php';
require_once '../includes/mailer.php';

restrict_to_role('admin');

$action = $_GET['action'] ?? '';
$post_id = $_GET['id'] ?? null;

if ($post_id && ($action === 'approve' || $action === 'reject')) {
    $status = ($action === 'approve') ? 'approved' : 'rejected';
    try {
        $stmt = $pdo->prepare("UPDATE posts SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $post_id])) {
            if ($status === 'approved') {
                $owner_stmt = $pdo->prepare("SELECT u.email, p.title FROM posts p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
                $owner_stmt->execute([$post_id]);
                $owner = $owner_stmt->fetch();
                if ($owner) {
                    send_email($owner['email'], 'Your post has been approved', "Your post \"{$owner['title']}\" is now live on the Community Connect public feed.");
                }
            }
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
