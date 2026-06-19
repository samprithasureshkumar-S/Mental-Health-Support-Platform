<?php
require_once '../config/db.php';
require_once '../includes/auth_helper.php';

restrict_to_role('user');

$user_id = $_SESSION['user_id'];
$success_msg = $_GET['success'] ?? '';
$q = trim($_GET['q'] ?? '');

try {
    if ($q !== '') {
        $stmt = $pdo->prepare("SELECT * FROM journal_entries WHERE user_id = ? AND (title LIKE ? OR content LIKE ?) ORDER BY created_at DESC");
        $like = '%' . $q . '%';
        $stmt->execute([$user_id, $like, $like]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM journal_entries WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
    }
    $entries = $stmt->fetchAll();
} catch (PDOException $e) {
    $entries = [];
    $error = "Could not load journal entries.";
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
            <li><a href="<?php echo $base_url; ?>/user/journal.php" class="active">Wellness Journal</a></li>
            <li><a href="<?php echo $base_url; ?>/user/volunteers.php">Volunteers</a></li>
            <li><a href="<?php echo $base_url; ?>/user/messages.php">Messages</a></li>
            <li><a href="<?php echo $base_url; ?>/user/appointments.php">My Appointments</a></li>
            <li><a href="<?php echo $base_url; ?>/resources.php">Resources</a></li>
            <li><a href="<?php echo $base_url; ?>/emergency.php">Emergency Contacts</a></li>
        </ul>
    </aside>

    <div class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap;">
            <div>
                <h1 style="font-size: 2rem; margin-bottom: 0.5rem;">Wellness Journal</h1>
                <p style="color: var(--text-secondary);">A private space for your thoughts. Only you can see these entries.</p>
            </div>
            <a href="<?php echo $base_url; ?>/user/journal_create.php" class="btn btn-primary">New Entry</a>
        </div>

        <form action="journal.php" method="GET" style="display: flex; gap: 0.5rem;">
            <input type="text" name="q" class="form-control" placeholder="Search your journal..." aria-label="Search your journal" value="<?php echo htmlspecialchars($q); ?>">
            <button type="submit" class="btn btn-secondary">Search</button>
        </form>

        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_msg); ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <?php if (empty($entries)): ?>
                <div class="card" style="text-align: center;">
                    <p style="color: var(--text-secondary);"><?php echo $q !== '' ? 'No entries match your search.' : 'You haven\'t written any journal entries yet.'; ?></p>
                </div>
            <?php else: ?>
                <?php foreach ($entries as $entry): ?>
                    <div class="card">
                        <div class="post-header">
                            <span><?php echo date('M d, Y h:i A', strtotime($entry['created_at'])); ?></span>
                        </div>
                        <h3 class="post-title"><?php echo htmlspecialchars($entry['title']); ?></h3>
                        <p class="post-body" style="font-size: 0.95rem; color: var(--text-secondary);"><?php echo nl2br(htmlspecialchars($entry['content'])); ?></p>
                        <div style="border-top: 1px solid var(--glass-border); padding-top: 1rem; margin-top: 1rem;">
                            <a href="<?php echo $base_url; ?>/user/journal_edit.php?id=<?php echo $entry['id']; ?>" class="btn btn-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.85rem; margin-right: 0.5rem;">Edit</a>
                            <a href="<?php echo $base_url; ?>/user/journal_delete.php?id=<?php echo $entry['id']; ?>" class="btn btn-danger btn-confirm-delete" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
