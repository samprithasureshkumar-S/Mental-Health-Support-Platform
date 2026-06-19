<?php
// Rule-based achievement badges, awarded by simple count thresholds.

const BADGES = [
    'first_post' => [
        'name' => 'First Post',
        'description' => 'Shared your first post with the community.',
        'icon' => '📝',
    ],
    'first_reply' => [
        'name' => 'First Reply',
        'description' => 'Offered your first supportive reply.',
        'icon' => '💬',
    ],
    'seven_day_checkin' => [
        'name' => '7 Day Check-In',
        'description' => 'Logged your mood on 7 different days.',
        'icon' => '📅',
    ],
    'wellness_champion' => [
        'name' => 'Wellness Champion',
        'description' => 'Logged 30 mood entries.',
        'icon' => '🏆',
    ],
    'community_helper' => [
        'name' => 'Community Helper',
        'description' => 'Submitted 10 supportive replies.',
        'icon' => '🤝',
    ],
];

function award_badge(PDO $pdo, int $user_id, string $code): void {
    $stmt = $pdo->prepare("INSERT IGNORE INTO user_achievements (user_id, badge_code) VALUES (?, ?)");
    $stmt->execute([$user_id, $code]);
}

function evaluate_user_badges(PDO $pdo, int $user_id, string $role): void {
    if ($role === 'user') {
        $post_count = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE user_id = ?");
        $post_count->execute([$user_id]);
        if ($post_count->fetchColumn() >= 1) {
            award_badge($pdo, $user_id, 'first_post');
        }

        $mood_days = $pdo->prepare("SELECT COUNT(DISTINCT DATE(created_at)) FROM moods WHERE user_id = ?");
        $mood_days->execute([$user_id]);
        if ($mood_days->fetchColumn() >= 7) {
            award_badge($pdo, $user_id, 'seven_day_checkin');
        }

        $mood_total = $pdo->prepare("SELECT COUNT(*) FROM moods WHERE user_id = ?");
        $mood_total->execute([$user_id]);
        if ($mood_total->fetchColumn() >= 30) {
            award_badge($pdo, $user_id, 'wellness_champion');
        }
    } elseif ($role === 'volunteer') {
        $reply_count = $pdo->prepare("SELECT COUNT(*) FROM replies WHERE volunteer_id = ?");
        $reply_count->execute([$user_id]);
        $total = $reply_count->fetchColumn();
        if ($total >= 1) {
            award_badge($pdo, $user_id, 'first_reply');
        }
        if ($total >= 10) {
            award_badge($pdo, $user_id, 'community_helper');
        }
    }
}

function render_badges(PDO $pdo, int $user_id): string {
    $stmt = $pdo->prepare("SELECT badge_code FROM user_achievements WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $earned = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $html = '<div class="badge-grid">';
    foreach (BADGES as $code => $badge) {
        $is_earned = in_array($code, $earned, true);
        $html .= '<div class="badge-card' . ($is_earned ? '' : ' locked') . '">';
        $html .= '<span class="badge-icon">' . $badge['icon'] . '</span>';
        $html .= '<strong>' . htmlspecialchars($badge['name']) . '</strong>';
        $html .= '<p>' . htmlspecialchars($badge['description']) . '</p>';
        $html .= '</div>';
    }
    $html .= '</div>';
    return $html;
}
