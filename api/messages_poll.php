<?php
require_once '../config/db.php';
require_once '../includes/auth_helper.php';
require_once '../includes/messages_helper.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['user', 'volunteer'], true)) {
    http_response_code(401);
    echo json_encode(['messages' => []]);
    exit;
}

$own_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$with = (int)($_GET['with'] ?? 0);
$since = (int)($_GET['since'] ?? 0);

$user_id = $role === 'volunteer' ? $with : $own_id;
$volunteer_id = $role === 'volunteer' ? $own_id : $with;

$stmt = $pdo->prepare("SELECT * FROM messages WHERE user_id = ? AND volunteer_id = ? AND id > ? ORDER BY created_at ASC");
$stmt->execute([$user_id, $volunteer_id, $since]);
$rows = $stmt->fetchAll();

mark_thread_read($pdo, $user_id, $volunteer_id, $role);

$messages = array_map(function ($row) use ($own_id) {
    return [
        'id' => (int)$row['id'],
        'content' => $row['content'],
        'time' => date('h:i A', strtotime($row['created_at'])),
        'is_own' => (int)$row['sender_id'] === (int)$own_id,
    ];
}, $rows);

echo json_encode(['messages' => $messages]);
