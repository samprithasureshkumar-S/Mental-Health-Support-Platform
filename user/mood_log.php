<?php
require_once '../config/db.php';
require_once '../includes/auth_helper.php';
require_once '../includes/achievements.php';

restrict_to_role('user');

$user_id = $_SESSION['user_id'];
$error = '';

$moods = ['Happy', 'Neutral', 'Sad', 'Stressed', 'Depressed'];
$mood_emoji = [
    'Happy' => '😊',
    'Neutral' => '😐',
    'Sad' => '😢',
    'Stressed' => '😣',
    'Depressed' => '😔',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mood = $_POST['mood'] ?? '';
    $note = trim($_POST['note'] ?? '');

    if (!in_array($mood, $moods, true)) {
        $error = 'Please select a valid mood.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO moods (user_id, mood, note) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $mood, $note !== '' ? $note : null]);
            evaluate_user_badges($pdo, $user_id, 'user');
            header("Location: " . $base_url . "/user/mood_history.php?success=Mood logged successfully.");
            exit;
        } catch (PDOException $e) {
            $error = 'Could not save your mood entry. Please try again.';
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
            <li><a href="<?php echo $base_url; ?>/user/create_post.php">Create New Post</a></li>
            <li><a href="<?php echo $base_url; ?>/user/mood_log.php" class="active">Log Mood</a></li>
            <li><a href="<?php echo $base_url; ?>/user/mood_history.php">Mood History</a></li>
            <li><a href="<?php echo $base_url; ?>/user/journal.php">Wellness Journal</a></li>
            <li><a href="<?php echo $base_url; ?>/user/volunteers.php">Volunteers</a></li>
            <li><a href="<?php echo $base_url; ?>/user/messages.php">Messages</a></li>
            <li><a href="<?php echo $base_url; ?>/user/appointments.php">My Appointments</a></li>
            <li><a href="<?php echo $base_url; ?>/resources.php">Resources</a></li>
            <li><a href="<?php echo $base_url; ?>/emergency.php">Emergency Contacts</a></li>
        </ul>
    </aside>

    <div class="main-content">
        <div class="card" style="max-width: 650px; width: 100%;">
            <h2 style="margin-bottom: 1rem;">How are you feeling today?</h2>
            <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 2rem;">
                Logging your mood regularly helps you and your support team understand your wellbeing trends over time.
            </p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="mood_log.php" method="POST">
                <div class="mood-picker">
                    <?php foreach ($moods as $m): ?>
                        <label class="mood-option">
                            <input type="radio" name="mood" value="<?php echo $m; ?>" required>
                            <span class="mood-emoji"><?php echo $mood_emoji[$m]; ?></span>
                            <span><?php echo $m; ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="form-group" style="margin-top: 1.5rem;">
                    <label for="note">Optional note</label>
                    <textarea id="note" name="note" class="form-control" rows="3" placeholder="What's contributing to how you feel today?"></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Save Mood</button>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.mood-option input').forEach(function (input) {
    input.addEventListener('change', function () {
        document.querySelectorAll('.mood-option').forEach(function (label) {
            label.classList.remove('selected');
        });
        input.closest('.mood-option').classList.add('selected');
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
