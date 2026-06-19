<?php
require_once '../config/db.php';
require_once '../includes/auth_helper.php';

restrict_to_role('user');

try {
    $stmt = $pdo->query("SELECT id, username, created_at FROM users WHERE role = 'volunteer' ORDER BY username ASC");
    $volunteers = $stmt->fetchAll();
} catch (PDOException $e) {
    $volunteers = [];
    $error = "Could not load volunteers.";
}

require_once '../includes/header.php';
?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">Menu</h3>
        <ul class="sidebar-nav">
            <li><a href="<?php echo $base_url; ?>/user/dashboard.php">My Posts</a></li>
            <li><a href="<?php echo $base_url; ?>/user/create_post.php">Create New Post</a></li>
            <li><a href="<?php echo $base_url; ?>/user/mood_log.php">Log Mood</a></li>
            <li><a href="<?php echo $base_url; ?>/user/mood_history.php">Mood History</a></li>
            <li><a href="<?php echo $base_url; ?>/user/journal.php">Wellness Journal</a></li>
            <li><a href="<?php echo $base_url; ?>/user/volunteers.php" class="active">Volunteers</a></li>
            <li><a href="<?php echo $base_url; ?>/user/messages.php">Messages</a></li>
            <li><a href="<?php echo $base_url; ?>/user/appointments.php">My Appointments</a></li>
            <li><a href="<?php echo $base_url; ?>/resources.php">Resources</a></li>
            <li><a href="<?php echo $base_url; ?>/emergency.php">Emergency Contacts</a></li>
        </ul>
    </aside>

    <div class="main-content">
        <div>
            <h1 style="font-size: 2rem; margin-bottom: 0.5rem;">Volunteer Directory</h1>
            <p style="color: var(--text-secondary);">Reach out privately to a volunteer for a chat or to request a support appointment.</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (empty($volunteers)): ?>
            <div class="card" style="text-align: center;">
                <p style="color: var(--text-secondary);">No volunteers are available right now.</p>
            </div>
        <?php else: ?>
            <div class="resource-grid">
                <?php foreach ($volunteers as $v): ?>
                    <div class="card volunteer-card">
                        <div>
                            <h3 style="margin-bottom: 0.5rem;">@<?php echo htmlspecialchars($v['username']); ?></h3>
                            <p style="color: var(--text-secondary); font-size: 0.85rem;">Volunteer since <?php echo date('M Y', strtotime($v['created_at'])); ?></p>
                        </div>
                        <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
                            <a href="<?php echo $base_url; ?>/user/chat.php?volunteer_id=<?php echo $v['id']; ?>" class="btn btn-primary" style="flex: 1; text-align: center; padding: 0.5rem;">Message</a>
                            <a href="<?php echo $base_url; ?>/user/appointment_request.php?volunteer_id=<?php echo $v['id']; ?>" class="btn btn-secondary" style="flex: 1; text-align: center; padding: 0.5rem;">Request Appointment</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
