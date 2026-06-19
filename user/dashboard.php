<?php
require_once '../config/db.php';
require_once '../includes/auth_helper.php';
require_once '../includes/achievements.php';

// Secure the route to 'user' role
restrict_to_role('user');

$user_id = $_SESSION['user_id'];
$success_msg = $_GET['success'] ?? '';

try {
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    $posts = [];
    $error = "Could not fetch your posts.";
}

$recommended_resources = [];
if (!empty($_GET['rec'])) {
    $rec_ids = array_filter(array_map('intval', explode(',', $_GET['rec'])));
    if (!empty($rec_ids)) {
        $placeholders = implode(',', array_fill(0, count($rec_ids), '?'));
        $stmt = $pdo->prepare("SELECT * FROM resources WHERE id IN ($placeholders)");
        $stmt->execute($rec_ids);
        $recommended_resources = $stmt->fetchAll();
    }
}

require_once '../includes/header.php';
?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">Menu</h3>
        <ul class="sidebar-nav">
            <li><a href="<?php echo $base_url; ?>/user/dashboard.php" class="active">My Posts</a></li>
            <li><a href="<?php echo $base_url; ?>/user/create_post.php">Create New Post</a></li>
            <li><a href="<?php echo $base_url; ?>/user/mood_log.php">Log Mood</a></li>
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
        <div>
            <h1 style="font-size: 2rem; margin-bottom: 0.5rem;">My Dashboard</h1>
            <p style="color: var(--text-secondary);">Manage your anonymous posts and view replies from volunteers.</p>
        </div>

        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_msg); ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($recommended_resources)): ?>
            <div class="card recommend-banner">
                <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">📚 Recommended For You</h3>
                <div class="resource-grid">
                    <?php foreach ($recommended_resources as $resource): ?>
                        <div class="card resource-card">
                            <div>
                                <span class="post-category" style="margin-bottom: 0.8rem; display: inline-block;"><?php echo htmlspecialchars($resource['category']); ?></span>
                                <h4 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($resource['title']); ?></h4>
                                <p style="color: var(--text-secondary); font-size: 0.85rem;"><?php echo htmlspecialchars(substr($resource['content'], 0, 100)); ?>...</p>
                            </div>
                            <a href="<?php echo $base_url; ?>/resources.php" style="font-size: 0.85rem; color: var(--accent-secondary); margin-top: 0.8rem; display: inline-block;">Read more &rarr;</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="card">
            <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">My Achievements</h3>
            <?php echo render_badges($pdo, $user_id); ?>
        </div>

        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <?php if (empty($posts)): ?>
                <div class="card" style="text-align: center;">
                    <p style="color: var(--text-secondary); margin-bottom: 1rem;">You haven't created any posts yet.</p>
                    <a href="<?php echo $base_url; ?>/user/create_post.php" class="btn btn-primary">Create Your First Post</a>
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="card">
                        <div class="post-header">
                            <div>
                                <span class="post-category"><?php echo htmlspecialchars($post['category']); ?></span>
                                <span class="post-status status-<?php echo $post['status']; ?>" style="margin-left: 0.5rem;">
                                    <?php echo ucfirst($post['status']); ?>
                                </span>
                            </div>
                            <span><?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                        </div>
                        <h3 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                        <p class="post-body" style="font-size: 0.95rem; color: var(--text-secondary);"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--glass-border); padding-top: 1rem; margin-top: 1rem;">
                            <div>
                                <a href="<?php echo $base_url; ?>/user/edit_post.php?id=<?php echo $post['id']; ?>" class="btn btn-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.85rem; margin-right: 0.5rem;">Edit</a>
                                <a href="<?php echo $base_url; ?>/user/delete_post.php?id=<?php echo $post['id']; ?>" class="btn btn-danger btn-confirm-delete" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">Delete</a>
                            </div>
                            
                            <?php if ($post['status'] === 'approved'): ?>
                                <a href="<?php echo $base_url; ?>/index.php" style="color: var(--accent-secondary); text-decoration: none; font-size: 0.9rem; font-weight: 500;">View in Public Feed &rarr;</a>
                            <?php else: ?>
                                <span style="font-size: 0.85rem; color: var(--text-secondary); font-style: italic;">Awaiting moderation approval to display publicly.</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
