<?php
require_once '../config/db.php';
require_once '../includes/auth_helper.php';

restrict_to_role('admin');

$action = $_GET['action'] ?? '';
$poll_id = $_GET['id'] ?? null;

if ($poll_id && $action === 'delete') {
    $stmt = $pdo->prepare("DELETE FROM polls WHERE id = ?");
    $stmt->execute([$poll_id]);
    header("Location: /admin/polls.php?success=Poll deleted.");
    exit;
}

if ($poll_id && $action === 'toggle') {
    $stmt = $pdo->prepare("UPDATE polls SET is_active = NOT is_active WHERE id = ?");
    $stmt->execute([$poll_id]);
    header("Location: /admin/polls.php?success=Poll status updated.");
    exit;
}

header("Location: /admin/polls.php");
exit;
