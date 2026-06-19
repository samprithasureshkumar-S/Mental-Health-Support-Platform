<?php
require_once '../config/db.php';
require_once '../includes/auth_helper.php';

restrict_to_role('user');

$user_id = $_SESSION['user_id'];
$volunteer_id = (int)($_GET['volunteer_id'] ?? $_POST['volunteer_id'] ?? 0);
$error = '';

$stmt = $pdo->prepare("SELECT id, username FROM users WHERE id = ? AND role = 'volunteer'");
$stmt->execute([$volunteer_id]);
$volunteer = $stmt->fetch();

if (!$volunteer) {
    header("Location: " . $base_url . "/user/volunteers.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notes = trim($_POST['notes'] ?? '');
    try {
        $stmt = $pdo->prepare("INSERT INTO appointments (user_id, volunteer_id, notes) VALUES (?, ?, ?)");
        if ($stmt->execute([$user_id, $volunteer_id, $notes !== '' ? $notes : null])) {
            header("Location: " . $base_url . "/user/appointments.php?success=Appointment request sent to @" . urlencode($volunteer['username']) . ".");
            exit;
        } else {
            $error = 'Could not submit the request. Please try again.';
        }
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

require_once '../includes/header.php';
?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">Menu</h3>
        <ul class="sidebar-nav">
            <li><a href="<?php echo $base_url; ?>/user/dashboard.php">My Posts</a></li>
            <li><a href="<?php echo $base_url; ?>/user/volunteers.php">Volunteers</a></li>
            <li><a href="<?php echo $base_url; ?>/user/appointments.php">My Appointments</a></li>
            <li><a href="<?php echo $base_url; ?>/resources.php">Resources</a></li>
            <li><a href="<?php echo $base_url; ?>/emergency.php">Emergency Contacts</a></li>
        </ul>
    </aside>

    <div class="main-content">
        <div class="card" style="max-width: 650px; width: 100%;">
            <h2 style="margin-bottom: 1rem;">Request an Appointment</h2>
            <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 2rem;">
                Requesting a support appointment with @<?php echo htmlspecialchars($volunteer['username']); ?>. They will accept or decline your request.
            </p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="appointment_request.php?volunteer_id=<?php echo $volunteer_id; ?>" method="POST">
                <div class="form-group">
                    <label for="notes">What would you like to talk about? (optional)</label>
                    <textarea id="notes" name="notes" class="form-control" rows="4" placeholder="Briefly describe what's on your mind..."></textarea>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Send Request</button>
                    <a href="<?php echo $base_url; ?>/user/volunteers.php" class="btn btn-secondary" style="flex: 1; text-align: center;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
