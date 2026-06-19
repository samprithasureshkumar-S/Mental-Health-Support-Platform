<?php
require_once 'config/db.php';
require_once 'includes/header.php';

// Fetch approved posts with their categories and reply count
try {
    $stmt = $pdo->prepare("
        SELECT p.*, COUNT(r.id) AS reply_count 
        FROM posts p 
        LEFT JOIN replies r ON p.id = r.post_id 
        WHERE p.status = 'approved' 
        GROUP BY p.id 
        ORDER BY p.created_at DESC
    ");
    $stmt->execute();
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    $posts = [];
    $db_error = "Could not load posts. Please ensure the database is initialized.";
}
?>

<div class="hero">
    <h1>Find Support, Anonymously.</h1>
    <p>Share your mental health struggles without fear. Connect with compassionate NGO volunteers who are here to listen and guide you.</p>
    <div class="hero-buttons">
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="/register.php" class="btn btn-primary">Share Your Story</a>
            <a href="/login.php" class="btn btn-secondary">Login to Reply</a>
        <?php else: ?>
            <a href="/user/dashboard.php" class="btn btn-primary">Go to Dashboard</a>
        <?php endif; ?>
    </div>
</div>

<div style="max-width: 800px; margin: 0 auto 4rem auto; padding: 0 2rem; width: 100%;">
    <h2 style="margin-bottom: 1.5rem; text-align: center;">Community Support Feed</h2>

    <?php if (isset($db_error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($db_error); ?></div>
    <?php endif; ?>

    <?php if (empty($posts)): ?>
        <div class="card" style="text-align: center;">
            <p style="color: var(--text-secondary);">No support requests have been posted yet. Be the first to share in a supportive environment.</p>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 2rem;">
            <?php foreach ($posts as $post): ?>
                <div class="card feed-post">
                    <div class="post-header">
                        <span class="post-category"><?php echo htmlspecialchars($post['category']); ?></span>
                        <span><?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                    </div>
                    <h3 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                    <p class="post-body"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                    
                    <!-- Replies Section -->
                    <div class="replies-section">
                        <h4 style="margin-bottom: 1rem; font-size: 0.95rem; display: flex; align-items: center; gap: 0.5rem;">
                            <span>💬</span> Support Replies (<?php echo $post['reply_count']; ?>)
                        </h4>
                        
                        <?php
                        // Fetch replies for this specific post
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

                        <?php if (empty($replies)): ?>
                            <p style="color: var(--text-secondary); font-size: 0.9rem; font-style: italic;">No replies from volunteers yet. A listener will be with you shortly.</p>
                        <?php else: ?>
                            <?php foreach ($replies as $reply): ?>
                                <div class="reply-card">
                                    <p style="font-size: 0.95rem;">
                                       <?php echo nl2br(htmlspecialchars($reply['content'])); ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
