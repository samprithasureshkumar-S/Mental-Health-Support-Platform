<?php
require_once '../config/db.php';
require_once '../includes/auth_helper.php';

restrict_to_role('user');

$user_id = $_SESSION['user_id'];
$success_msg = $_GET['success'] ?? '';

try {
    $stmt = $pdo->prepare("
        SELECT a.*, u.username AS volunteer_username
        FROM appointments a
        JOIN users u ON a.volunteer_id = u.id
        WHERE a.user_id = ?
        ORDER BY a.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $appointments = $stmt->fetchAll();

    $rating_stmt = $pdo->prepare("SELECT appointment_id, rating FROM ratings WHERE user_id = ?");
    $rating_stmt->execute([$user_id]);
    $ratings_by_appt = [];
    foreach ($rating_stmt->fetchAll() as $r) {
        $ratings_by_appt[$r['appointment_id']] = $r['rating'];
    }
} catch (PDOException $e) {
    $appointments = [];
    $ratings_by_appt = [];
    $error = "Could not load appointments.";
}

require_once '../includes/header.php';
?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">Menu</h3>
        <ul class="sidebar-nav">
            <li><a href="<?php echo $base_url; ?>/user/dashboard.php">My Posts</a></li>
            <li><a href="<?php echo $base_url; ?>/user/journal.php">Wellness Journal</a></li>
            <li><a href="<?php echo $base_url; ?>/user/volunteers.php">Volunteers</a></li>
            <li><a href="<?php echo $base_url; ?>/user/messages.php">Messages</a></li>
            <li><a href="<?php echo $base_url; ?>/user/appointments.php" class="active">My Appointments</a></li>
            <li><a href="<?php echo $base_url; ?>/resources.php">Resources</a></li>
            <li><a href="<?php echo $base_url; ?>/emergency.php">Emergency Contacts</a></li>
        </ul>
    </aside>

    <div class="main-content">
        <div>
            <h1 style="font-size: 2rem; margin-bottom: 0.5rem;">My Appointments</h1>
            <p style="color: var(--text-secondary);">Track your support appointment requests.</p>
        </div>

        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_msg); ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <?php if (empty($appointments)): ?>
                <div class="card" style="text-align: center;">
                    <p style="color: var(--text-secondary); margin-bottom: 1rem;">You haven't requested any appointments yet.</p>
                    <a href="<?php echo $base_url; ?>/user/volunteers.php" class="btn btn-primary">Browse Volunteers</a>
                </div>
            <?php else: ?>
                <?php foreach ($appointments as $appt): ?>
                    <div class="card">
                        <div class="post-header">
                            <span>With: <strong>@<?php echo htmlspecialchars($appt['volunteer_username']); ?></strong></span>
                            <span class="post-status status-<?php echo $appt['status'] === 'accepted' ? 'approved' : ($appt['status'] === 'cancelled' ? 'rejected' : ($appt['status'] === 'completed' ? 'completed' : 'pending')); ?>">
                                <?php echo ucfirst($appt['status']); ?>
                            </span>
                        </div>
                        <?php if ($appt['notes']): ?>
                            <p class="post-body" style="font-size: 0.95rem; color: var(--text-secondary);"><?php echo nl2br(htmlspecialchars($appt['notes'])); ?></p>
                        <?php endif; ?>
                        <?php if ($appt['response_note']): ?>
                            <p style="font-size: 0.85rem; color: var(--text-secondary); border-top: 1px solid var(--glass-border); padding-top: 0.8rem; margin-top: 0.8rem;">
                                Volunteer note: <?php echo htmlspecialchars($appt['response_note']); ?>
                            </p>
                        <?php endif; ?>
                        <span style="font-size: 0.8rem; color: var(--text-secondary);">Requested <?php echo date('M d, Y', strtotime($appt['created_at'])); ?></span>

                        <?php if ($appt['status'] === 'completed'): ?>
                            <div style="border-top: 1px solid var(--glass-border); padding-top: 0.8rem; margin-top: 0.8rem;">
                                <?php if (isset($ratings_by_appt[$appt['id']])): ?>
                                    <span style="color: var(--warning);"><?php echo str_repeat('★', $ratings_by_appt[$appt['id']]) . str_repeat('☆', 5 - $ratings_by_appt[$appt['id']]); ?></span>
                                <?php else: ?>
                                    <a href="<?php echo $base_url; ?>/user/rate_appointment.php?id=<?php echo $appt['id']; ?>" class="btn btn-secondary" style="padding: 0.4rem 1rem; font-size: 0.85rem;">Rate this volunteer</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
