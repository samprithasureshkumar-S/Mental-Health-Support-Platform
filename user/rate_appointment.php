<?php
require_once '../config/db.php';
require_once '../includes/auth_helper.php';

restrict_to_role('user');

$user_id = $_SESSION['user_id'];
$appt_id = $_GET['id'] ?? $_POST['appointment_id'] ?? null;
$error = '';

$stmt = $pdo->prepare("
    SELECT a.*, u.username AS volunteer_username
    FROM appointments a
    JOIN users u ON a.volunteer_id = u.id
    WHERE a.id = ? AND a.user_id = ? AND a.status = 'completed'
");
$stmt->execute([$appt_id, $user_id]);
$appointment = $stmt->fetch();

if (!$appointment) {
    header("Location: " . $base_url . "/user/appointments.php");
    exit;
}

$existing_stmt = $pdo->prepare("SELECT * FROM ratings WHERE appointment_id = ?");
$existing_stmt->execute([$appt_id]);
if ($existing_stmt->fetch()) {
    header("Location: " . $base_url . "/user/appointments.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = (int)($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $error = 'Please select a rating between 1 and 5 stars.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO ratings (appointment_id, user_id, volunteer_id, rating, comment) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$appt_id, $user_id, $appointment['volunteer_id'], $rating, $comment !== '' ? $comment : null]);
            header("Location: " . $base_url . "/user/appointments.php?success=Thank you for your feedback.");
            exit;
        } catch (PDOException $e) {
            $error = 'Could not save your rating. Please try again.';
        }
    }
}

require_once '../includes/header.php';
?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">Menu</h3>
        <ul class="sidebar-nav">
            <li><a href="<?php echo $base_url; ?>/user/dashboard.php">My Posts</a></li>
            <li><a href="<?php echo $base_url; ?>/user/appointments.php" class="active">My Appointments</a></li>
            <li><a href="<?php echo $base_url; ?>/resources.php">Resources</a></li>
            <li><a href="<?php echo $base_url; ?>/emergency.php">Emergency Contacts</a></li>
        </ul>
    </aside>

    <div class="main-content">
        <div class="card" style="max-width: 550px; width: 100%;">
            <h2 style="margin-bottom: 1rem;">Rate Your Support Session</h2>
            <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 2rem;">
                How was your appointment with @<?php echo htmlspecialchars($appointment['volunteer_username']); ?>?
            </p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="rate_appointment.php?id=<?php echo $appt_id; ?>" method="POST">
                <input type="hidden" name="appointment_id" value="<?php echo $appt_id; ?>">
                <div class="star-rating">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required>
                        <label for="star<?php echo $i; ?>">★</label>
                    <?php endfor; ?>
                </div>

                <div class="form-group" style="margin-top: 1.5rem;">
                    <label for="comment">Comment (optional)</label>
                    <textarea id="comment" name="comment" class="form-control" rows="4" placeholder="Share more about your experience..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Submit Rating</button>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
