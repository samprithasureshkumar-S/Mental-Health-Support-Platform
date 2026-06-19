<?php
require_once '../config/db.php';
require_once '../includes/auth_helper.php';

restrict_to_role('volunteer');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = $_POST['post_id'] ?? null;
    $content = trim($_POST['content'] ?? '');
    $volunteer_id = $_SESSION['user_id'];

    if (!$post_id || empty($content)) {
        header("Location: /volunteer/dashboard.php?error=Reply cannot be empty.");
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO replies (post_id, volunteer_id, content) VALUES (?, ?, ?)");
        if ($stmt->execute([$post_id, $volunteer_id, $content])) {
            header("Location: /volunteer/dashboard.php?success=Reply submitted successfully!");
            exit;
        } else {
            header("Location: /volunteer/dashboard.php?error=Failed to submit reply.");
            exit;
        }
    } catch (PDOException $e) {
        header("Location: /volunteer/dashboard.php?error=" . urlencode($e->getMessage()));
        exit;
    }
}

header("Location: /volunteer/dashboard.php");
exit;
?>
