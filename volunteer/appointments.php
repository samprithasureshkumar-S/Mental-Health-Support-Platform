<?php
require_once '../config/db.php';
require_once '../includes/auth_helper.php';

restrict_to_role('volunteer');

$volunteer_id = $_SESSION['user_id'];
$success_msg = $_GET['success'] ?? '';
$error_msg = $_GET['error'] ?? '';

try {
    $stmt = $pdo->prepare("
        SELECT a.*, u.username AS user_username
        FROM appointments a
        JOIN users u ON a.user_id = u.id
        WHERE a.volunteer_id = ?
        ORDER BY (a.status = 'pending') DESC, a.created_at DESC
    ");
    $stmt->execute([$volunteer_id]);
    $appointments = $stmt->fetchAll();
} catch (PDOException $e) {
    $appointments = [];
    $error_msg = "Could not load appointments.";
}

require_once '../includes/header.php';
?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">Volunteer Menu</h3>
        <ul class="sidebar-nav">
            <li><a href="<?php echo $base_url; ?>/volunteer/dashboard.php">Support Board</a></li>
            <li><a href="<?php echo $base_url; ?>/volunteer/messages.php">Messages</a></li>
            <li><a href="<?php echo $base_url; ?>/volunteer/appointments.php" class="active">Appointments</a></li>
            <li><a href="<?php echo $base_url; ?>/volunteer/ratings.php">My Ratings</a></li>
            <li><a href="<?php echo $base_url; ?>/resources.php">Resources</a></li>
            <li><a href="<?php echo $base_url; ?>/emergency.php">Emergency Helpline</a></li>
        </ul>
    </aside>

    <div class="main-content">
        <div>
            <h1 style="font-size: 2rem; margin-bottom: 0.5rem;">Appointment Requests</h1>
            <p style="color: var(--text-secondary);">Review, accept, or decline incoming support appointment requests.</p>
        </div>

        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_msg); ?></div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_msg); ?></div>
        <?php endif; ?>

        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <?php if (empty($appointments)): ?>
                <div class="card" style="text-align: center;">
                    <p style="color: var(--text-secondary);">No appointment requests yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($appointments as $appt): ?>
                    <div class="card">
                        <div class="post-header">
                            <span>From: <strong>@<?php echo htmlspecialchars($appt['user_username']); ?></strong></span>
                            <span class="post-status status-<?php echo $appt['status'] === 'accepted' ? 'approved' : ($appt['status'] === 'cancelled' ? 'rejected' : ($appt['status'] === 'completed' ? 'completed' : 'pending')); ?>">
                                <?php echo ucfirst($appt['status']); ?>
                            </span>
                        </div>
                        <?php if ($appt['notes']): ?>
                            <p class="post-body" style="font-size: 0.95rem; color: var(--text-secondary);"><?php echo nl2br(htmlspecialchars($appt['notes'])); ?></p>
                        <?php endif; ?>
                        <span style="font-size: 0.8rem; color: var(--text-secondary);">Requested <?php echo date('M d, Y', strtotime($appt['created_at'])); ?></span>

                        <div style="display: flex; gap: 0.5rem; margin-top: 1rem; border-top: 1px solid var(--glass-border); padding-top: 1rem;">
                            <?php if ($appt['status'] === 'pending'): ?>
                                <a href="appointment_action.php?action=accept&id=<?php echo $appt['id']; ?>" class="btn btn-primary" style="padding: 0.4rem 1rem; font-size: 0.85rem;">Accept</a>
                                <a href="appointment_action.php?action=cancel&id=<?php echo $appt['id']; ?>" class="btn btn-danger" style="padding: 0.4rem 1rem; font-size: 0.85rem;">Decline</a>
                            <?php elseif ($appt['status'] === 'accepted'): ?>
                                <a href="appointment_action.php?action=complete&id=<?php echo $appt['id']; ?>" class="btn btn-primary" style="padding: 0.4rem 1rem; font-size: 0.85rem;">Mark Completed</a>
                                <a href="appointment_action.php?action=cancel&id=<?php echo $appt['id']; ?>" class="btn btn-danger" style="padding: 0.4rem 1rem; font-size: 0.85rem;">Cancel</a>
                            <?php endif; ?>
                            <a href="<?php echo $base_url; ?>/volunteer/chat.php?user_id=<?php echo $appt['user_id']; ?>" class="btn btn-secondary" style="padding: 0.4rem 1rem; font-size: 0.85rem;">Message</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
