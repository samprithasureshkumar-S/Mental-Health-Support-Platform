<?php
require_once '../config/db.php';
require_once '../includes/auth_helper.php';

// Secure the route to 'admin' role
restrict_to_role('admin');

$success_msg = $_GET['success'] ?? '';
$error_msg = $_GET['error'] ?? '';

// Fetch Stats
try {
    $user_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $pending_count = $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'pending'")->fetchColumn();
    $approved_count = $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'approved'")->fetchColumn();
    $resource_count = $pdo->query("SELECT COUNT(*) FROM resources")->fetchColumn();
    
    // Fetch Pending Posts for Moderation
    $stmt = $pdo->query("SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.id WHERE p.status = 'pending' ORDER BY p.created_at ASC");
    $pending_posts = $stmt->fetchAll();
} catch (PDOException $e) {
    $error_msg = "Database error: " . $e->getMessage();
}

require_once '../includes/header.php';
?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">Admin Menu</h3>
        <ul class="sidebar-nav">
            <li><a href="/admin/dashboard.php" class="active">Overview & Moderation</a></li>
            <li><a href="/admin/users.php">Manage Users</a></li>
            <li><a href="/admin/resources.php">Manage Resources</a></li>
            <li><a href="/resources.php">View Resources</a></li>
            <li><a href="/emergency.php">Emergency Helpline</a></li>
        </ul>
    </aside>

    <div class="main-content">
        <div>
            <h1 style="font-size: 2rem; margin-bottom: 0.5rem;">Admin Dashboard</h1>
            <p style="color: var(--text-secondary);">Moderate anonymous posts, manage users, and update wellness library content.</p>
        </div>

        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_msg); ?></div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_msg); ?></div>
        <?php endif; ?>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="card stat-card">
                <span class="stat-val"><?php echo $user_count; ?></span>
                <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 0.5rem;">Registered Users</p>
            </div>
            <div class="card stat-card" style="border-color: rgba(245, 158, 11, 0.3);">
                <span class="stat-val" style="color: var(--warning);"><?php echo $pending_count; ?></span>
                <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 0.5rem;">Pending Moderation</p>
            </div>
            <div class="card stat-card" style="border-color: rgba(16, 185, 129, 0.3);">
                <span class="stat-val"><?php echo $approved_count; ?></span>
                <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 0.5rem;">Approved Posts</p>
            </div>
            <div class="card stat-card">
                <span class="stat-val" style="color: var(--accent-secondary);"><?php echo $resource_count; ?></span>
                <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 0.5rem;">Library Resources</p>
            </div>
        </div>

        <!-- Moderation Section -->
        <div class="card" style="margin-top: 1.5rem;">
            <h2 style="margin-bottom: 1.5rem; font-size: 1.3rem; border-bottom: 1px solid var(--glass-border); padding-bottom: 0.5rem;">
                Pending Posts Moderation Queue (<?php echo count($pending_posts); ?>)
            </h2>

            <?php if (empty($pending_posts)): ?>
                <p style="color: var(--text-secondary); text-align: center; padding: 2rem 0;">No pending posts require review at this time. Good job!</p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <?php foreach ($pending_posts as $post): ?>
                        <div style="background-color: rgba(15, 23, 42, 0.4); border: 1px solid var(--glass-border); padding: 1.5rem; border-radius: 8px;">
                            <div class="post-header" style="margin-bottom: 0.8rem;">
                                <span>Author alias: <strong>@<?php echo htmlspecialchars($post['username']); ?></strong> (ID: <?php echo $post['user_id']; ?>)</span>
                                <span>Category: <strong class="post-category"><?php echo htmlspecialchars($post['category']); ?></strong></span>
                            </div>
                            <h4 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($post['title']); ?></h4>
                            <p style="color: var(--text-secondary); font-size: 0.95rem; margin-bottom: 1.2rem;"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                            
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="/admin/moderate.php?action=approve&id=<?php echo $post['id']; ?>" class="btn btn-primary" style="padding: 0.4rem 1rem; font-size: 0.85rem;">Approve & Publish</a>
                                <a href="/admin/moderate.php?action=reject&id=<?php echo $post['id']; ?>" class="btn btn-danger" style="padding: 0.4rem 1rem; font-size: 0.85rem;">Reject & Delete</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
