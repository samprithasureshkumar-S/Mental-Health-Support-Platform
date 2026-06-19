<?php
require_once '../config/db.php';
require_once '../includes/auth_helper.php';

restrict_to_role('volunteer');

$volunteer_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT COUNT(*) AS total, AVG(rating) AS avg_rating FROM ratings WHERE volunteer_id = ?");
$stmt->execute([$volunteer_id]);
$summary = $stmt->fetch();

$stmt = $pdo->prepare("SELECT rating, comment, created_at FROM ratings WHERE volunteer_id = ? ORDER BY created_at DESC");
$stmt->execute([$volunteer_id]);
$ratings = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">Volunteer Menu</h3>
        <ul class="sidebar-nav">
            <li><a href="<?php echo $base_url; ?>/volunteer/dashboard.php">Support Board</a></li>
            <li><a href="<?php echo $base_url; ?>/volunteer/messages.php">Messages</a></li>
            <li><a href="<?php echo $base_url; ?>/volunteer/appointments.php">Appointments</a></li>
            <li><a href="<?php echo $base_url; ?>/volunteer/ratings.php" class="active">My Ratings</a></li>
            <li><a href="<?php echo $base_url; ?>/resources.php">Resources</a></li>
            <li><a href="<?php echo $base_url; ?>/emergency.php">Emergency Helpline</a></li>
        </ul>
    </aside>

    <div class="main-content">
        <div>
            <h1 style="font-size: 2rem; margin-bottom: 0.5rem;">My Performance</h1>
            <p style="color: var(--text-secondary);">Feedback from community members you've supported.</p>
        </div>

        <div class="stats-grid">
            <div class="card stat-card">
                <span class="stat-val"><?php echo $summary['total'] > 0 ? round($summary['avg_rating'], 1) : '—'; ?></span>
                <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 0.5rem;">Average Rating</p>
            </div>
            <div class="card stat-card">
                <span class="stat-val" style="color: var(--accent-secondary);"><?php echo $summary['total']; ?></span>
                <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 0.5rem;">Total Ratings</p>
            </div>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">Feedback</h3>
            <?php if (empty($ratings)): ?>
                <p style="color: var(--text-secondary); text-align: center; padding: 2rem 0;">No ratings yet. Complete an appointment to start receiving feedback.</p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php foreach ($ratings as $r): ?>
                        <div style="background-color: rgba(15,23,42,0.4); border: 1px solid var(--glass-border); padding: 1rem; border-radius: 8px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="color: var(--warning);"><?php echo str_repeat('★', $r['rating']) . str_repeat('☆', 5 - $r['rating']); ?></span>
                                <span style="font-size: 0.8rem; color: var(--text-secondary);"><?php echo date('M d, Y', strtotime($r['created_at'])); ?></span>
                            </div>
                            <?php if ($r['comment']): ?>
                                <p style="font-size: 0.9rem; color: var(--text-secondary); margin-top: 0.5rem;">"<?php echo htmlspecialchars($r['comment']); ?>" — From a community member</p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
