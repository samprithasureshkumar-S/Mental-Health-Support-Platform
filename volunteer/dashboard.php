<?php
require_once '../config/db.php';
require_once '../includes/auth_helper.php';
require_once '../includes/achievements.php';

// Secure the route to 'volunteer' role
restrict_to_role('volunteer');

$success_msg = $_GET['success'] ?? '';
$error_msg = $_GET['error'] ?? '';

// Fetch all approved posts
try {
    $stmt = $pdo->query("
        SELECT p.*, COUNT(r.id) AS reply_count 
        FROM posts p 
        LEFT JOIN replies r ON p.id = r.post_id 
        WHERE p.status = 'approved' 
        GROUP BY p.id 
        ORDER BY p.created_at DESC
    ");
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    $posts = [];
    $error_msg = "Could not fetch posts.";
}

require_once '../includes/header.php';
?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">Volunteer Menu</h3>
        <ul class="sidebar-nav">
            <li><a href="/volunteer/dashboard.php" class="active">Support Board</a></li>
            <li><a href="/volunteer/messages.php">Messages</a></li>
            <li><a href="/volunteer/appointments.php">Appointments</a></li>
            <li><a href="/volunteer/ratings.php">My Ratings</a></li>
            <li><a href="/resources.php">Resources</a></li>
            <li><a href="/emergency.php">Emergency Helpline</a></li>
        </ul>
    </aside>

    <div class="main-content">
        <div>
            <h1 style="font-size: 2rem; margin-bottom: 0.5rem;">Volunteer Portal</h1>
            <p style="color: var(--text-secondary);">Browse community posts and reply with supportive, comforting messages.</p>
        </div>

        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_msg); ?></div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_msg); ?></div>
        <?php endif; ?>

        <div class="card">
            <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">My Achievements</h3>
            <?php echo render_badges($pdo, $_SESSION['user_id']); ?>
        </div>

        <div style="display: flex; flex-direction: column; gap: 2rem;">
            <?php if (empty($posts)): ?>
                <div class="card" style="text-align: center;">
                    <p style="color: var(--text-secondary);">No active community posts require attention right now.</p>
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="card feed-post<?php echo $post['is_urgent'] ? ' post-urgent' : ''; ?>">
                        <div class="post-header">
                            <span class="post-category"><?php echo htmlspecialchars($post['category']); ?></span>
                            <span><?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                        </div>
                        <?php if ($post['is_urgent']): ?>
                            <span class="urgent-badge">⚠ URGENT — needs immediate attention</span>
                        <?php endif; ?>
                        <h3 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                        <p class="post-body"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                        
                        <!-- Replies Section -->
                        <div class="replies-section">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                <h4 style="font-size: 0.95rem;">
                                    💬 Past Support Replies (<?php echo $post['reply_count']; ?>)
                                </h4>
                                <a href="<?php echo $base_url; ?>/volunteer/chat.php?user_id=<?php echo $post['user_id']; ?>" class="btn btn-secondary" style="padding: 0.3rem 0.8rem; font-size: 0.8rem;">Message Author</a>
                            </div>

                            <?php
                            $reply_stmt = $pdo->prepare("
                                SELECT r.*, u.username 
                                FROM replies r 
                                JOIN users u ON r.volunteer_id = u.id 
                                WHERE r.post_id = ? 
                                ORDER BY r.created_at ASC
                            ");
                            $reply_stmt->execute([$post['id']]);
                            $replies = $reply_stmt->fetchAll();
                            ?>

                            <?php foreach ($replies as $reply): ?>
                                <div class="reply-card">
                                    <div class="reply-header">
                                        <span>Volunteer: <strong class="volunteer-badge"><?php echo htmlspecialchars($reply['username']); ?></strong></span>
                                        <span><?php echo date('M d, Y', strtotime($reply['created_at'])); ?></span>
                                    </div>
                                    <p style="font-size: 0.95rem;"><?php echo nl2br(htmlspecialchars($reply['content'])); ?></p>
                                </div>
                            <?php endforeach; ?>

                            <!-- Add Reply Form -->
                            <div style="margin-top: 1.5rem; background-color: rgba(255,255,255,0.02); padding: 1.5rem; border-radius: 8px; border: 1px solid var(--glass-border);">
                                <h5 style="margin-bottom: 0.8rem; font-size: 0.9rem;">Respond to this post:</h5>
                                <form action="/volunteer/reply.php" method="POST">
                                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                    <div class="form-group">
                                        <textarea name="content" class="form-control" rows="3" placeholder="Write an encouraging, supportive message..." required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary" style="padding: 0.4rem 1rem; font-size: 0.85rem; margin-top: 0.5rem;">Submit Response</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
