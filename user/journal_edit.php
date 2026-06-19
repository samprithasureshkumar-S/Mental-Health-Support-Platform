<?php
require_once '../config/db.php';
require_once '../includes/auth_helper.php';

restrict_to_role('user');

$error = '';
$entry_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$entry_id) {
    header("Location: " . $base_url . "/user/journal.php");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM journal_entries WHERE id = ? AND user_id = ?");
    $stmt->execute([$entry_id, $user_id]);
    $entry = $stmt->fetch();

    if (!$entry) {
        header("Location: " . $base_url . "/user/journal.php");
        exit;
    }
} catch (PDOException $e) {
    header("Location: " . $base_url . "/user/journal.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if (empty($title) || empty($content)) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE journal_entries SET title = ?, content = ? WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$title, $content, $entry_id, $user_id])) {
                header("Location: " . $base_url . "/user/journal.php?success=Journal entry updated.");
                exit;
            } else {
                $error = 'Could not update the entry.';
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
            <li><a href="<?php echo $base_url; ?>/user/journal.php" class="active">Wellness Journal</a></li>
            <li><a href="<?php echo $base_url; ?>/resources.php">Resources</a></li>
            <li><a href="<?php echo $base_url; ?>/emergency.php">Emergency Contacts</a></li>
        </ul>
    </aside>

    <div class="main-content">
        <div class="card" style="max-width: 650px; width: 100%;">
            <h2 style="margin-bottom: 1rem;">Edit Journal Entry</h2>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="journal_edit.php?id=<?php echo $entry_id; ?>" method="POST">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" class="form-control" required value="<?php echo htmlspecialchars($entry['title']); ?>">
                </div>

                <div class="form-group">
                    <label for="content">Entry</label>
                    <textarea id="content" name="content" class="form-control" rows="10" required><?php echo htmlspecialchars($entry['content']); ?></textarea>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Save Changes</button>
                    <a href="<?php echo $base_url; ?>/user/journal.php" class="btn btn-secondary" style="flex: 1; text-align: center;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
