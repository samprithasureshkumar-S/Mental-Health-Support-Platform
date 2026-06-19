<?php
// Shared helpers for the private user <-> volunteer messaging system.

function get_conversations(PDO $pdo, string $role, int $own_id): array {
    if ($role === 'volunteer') {
        $sql = "
            SELECT u.id AS partner_id, u.username AS partner_username,
                   (SELECT content FROM messages m2 WHERE m2.volunteer_id = ? AND m2.user_id = u.id ORDER BY m2.created_at DESC LIMIT 1) AS last_message,
                   (SELECT created_at FROM messages m3 WHERE m3.volunteer_id = ? AND m3.user_id = u.id ORDER BY m3.created_at DESC LIMIT 1) AS last_created_at,
                   (SELECT COUNT(*) FROM messages m4 WHERE m4.volunteer_id = ? AND m4.user_id = u.id AND m4.sender_id = u.id AND m4.is_read = 0) AS unread_count
            FROM users u
            WHERE u.id IN (SELECT DISTINCT user_id FROM messages WHERE volunteer_id = ?)
            ORDER BY last_created_at DESC
        ";
        $params = [$own_id, $own_id, $own_id, $own_id];
    } else {
        $sql = "
            SELECT u.id AS partner_id, u.username AS partner_username,
                   (SELECT content FROM messages m2 WHERE m2.user_id = ? AND m2.volunteer_id = u.id ORDER BY m2.created_at DESC LIMIT 1) AS last_message,
                   (SELECT created_at FROM messages m3 WHERE m3.user_id = ? AND m3.volunteer_id = u.id ORDER BY m3.created_at DESC LIMIT 1) AS last_created_at,
                   (SELECT COUNT(*) FROM messages m4 WHERE m4.user_id = ? AND m4.volunteer_id = u.id AND m4.sender_id = u.id AND m4.is_read = 0) AS unread_count
            FROM users u
            WHERE u.id IN (SELECT DISTINCT volunteer_id FROM messages WHERE user_id = ?)
            ORDER BY last_created_at DESC
        ";
        $params = [$own_id, $own_id, $own_id, $own_id];
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function get_thread(PDO $pdo, int $user_id, int $volunteer_id): array {
    $stmt = $pdo->prepare("SELECT * FROM messages WHERE user_id = ? AND volunteer_id = ? ORDER BY created_at ASC");
    $stmt->execute([$user_id, $volunteer_id]);
    return $stmt->fetchAll();
}

function mark_thread_read(PDO $pdo, int $user_id, int $volunteer_id, string $reader_role): void {
    // Mark messages sent by the OTHER party as read.
    $sender_to_clear = $reader_role === 'volunteer' ? $user_id : $volunteer_id;
    $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE user_id = ? AND volunteer_id = ? AND sender_id = ? AND is_read = 0");
    $stmt->execute([$user_id, $volunteer_id, $sender_to_clear]);
}

function send_message(PDO $pdo, int $user_id, int $volunteer_id, int $sender_id, string $content): bool {
    $stmt = $pdo->prepare("INSERT INTO messages (user_id, volunteer_id, sender_id, content) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$user_id, $volunteer_id, $sender_id, $content]);
}
