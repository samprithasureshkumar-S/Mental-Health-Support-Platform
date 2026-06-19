<?php
require_once '../config/db.php';
require_once '../includes/auth_helper.php';
require_once '../includes/messages_helper.php';

restrict_to_role('volunteer');

$conversations = get_conversations($pdo, 'volunteer', $_SESSION['user_id']);

require_once '../includes/header.php';
?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">Volunteer Menu</h3>
        <ul class="sidebar-nav">
            <li><a href="<?php echo $base_url; ?>/volunteer/dashboard.php">Support Board</a></li>
            <li><a href="<?php echo $base_url; ?>/volunteer/messages.php" class="active">Messages</a></li>
            <li><a href="<?php echo $base_url; ?>/volunteer/appointments.php">Appointments</a></li>
            <li><a href="<?php echo $base_url; ?>/volunteer/ratings.php">My Ratings</a></li>
            <li><a href="<?php echo $base_url; ?>/resources.php">Resources</a></li>
            <li><a href="<?php echo $base_url; ?>/emergency.php">Emergency Helpline</a></li>
        </ul>
    </aside>

    <div class="main-content">
        <div>
            <h1 style="font-size: 2rem; margin-bottom: 0.5rem;">Messages</h1>
            <p style="color: var(--text-secondary);">Your private conversations with community members.</p>
        </div>

        <div class="card" style="padding: 0;">
            <?php if (empty($conversations)): ?>
                <p style="color: var(--text-secondary); text-align: center; padding: 2rem;">No conversations yet. Reply to a post on the <a href="<?php echo $base_url; ?>/volunteer/dashboard.php" style="color: var(--accent-primary);">Support Board</a> to start one.</p>
            <?php else: ?>
                <?php foreach ($conversations as $c): ?>
                    <a href="<?php echo $base_url; ?>/volunteer/chat.php?user_id=<?php echo $c['partner_id']; ?>" class="conversation-list-item">
                        <div>
                            <strong>@<?php echo htmlspecialchars($c['partner_username']); ?></strong>
                            <p style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 0.2rem;">
                                <?php echo htmlspecialchars(substr($c['last_message'] ?? '', 0, 60)); ?>
                            </p>
                        </div>
                        <div style="text-align: right;">
                            <span style="font-size: 0.8rem; color: var(--text-secondary);"><?php echo $c['last_created_at'] ? date('M d, h:i A', strtotime($c['last_created_at'])) : ''; ?></span>
                            <?php if ($c['unread_count'] > 0): ?>
                                <div><span class="unread-badge"><?php echo $c['unread_count']; ?></span></div>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
