<?php
require_once '../config/db.php';
require_once '../includes/auth_helper.php';

restrict_to_role('user');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $user_id = $_SESSION['user_id'];

    if (empty($title) || empty($content)) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO journal_entries (user_id, title, content) VALUES (?, ?, ?)");
            if ($stmt->execute([$user_id, $title, $content])) {
                header("Location: " . $base_url . "/user/journal.php?success=Journal entry saved.");
                exit;
            } else {
                $error = 'Could not save the entry. Please try again.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
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
            <li><a href="<?php echo $base_url; ?>/user/journal.php">Wellness Journal</a></li>
            <li><a href="<?php echo $base_url; ?>/user/journal_create.php" class="active">New Entry</a></li>
            <li><a href="<?php echo $base_url; ?>/resources.php">Resources</a></li>
            <li><a href="<?php echo $base_url; ?>/emergency.php">Emergency Contacts</a></li>
        </ul>
    </aside>

    <div class="main-content">
        <div class="card" style="max-width: 650px; width: 100%;">
            <h2 style="margin-bottom: 1rem;">New Journal Entry</h2>
            <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 2rem;">
                This entry is private and visible only to you.
            </p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="journal_create.php" method="POST">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" class="form-control" required value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="content">Entry</label>
                    <textarea id="content" name="content" class="form-control" rows="10" required><?php echo isset($content) ? htmlspecialchars($content) : ''; ?></textarea>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Save Entry</button>
                    <a href="<?php echo $base_url; ?>/user/journal.php" class="btn btn-secondary" style="flex: 1; text-align: center;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
