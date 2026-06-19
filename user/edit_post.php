<?php
require_once '../config/db.php';
require_once '../includes/auth_helper.php';
require_once '../includes/risk_engine.php';
require_once '../includes/mailer.php';

restrict_to_role('user');

$error = '';
$post_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$post_id) {
    header("Location: /user/dashboard.php");
    exit;
}

// Fetch the post, ensuring it belongs to the logged-in user
try {
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
    $stmt->execute([$post_id, $user_id]);
    $post = $stmt->fetch();
    
    if (!$post) {
        header("Location: /user/dashboard.php");
        exit;
    }
} catch (PDOException $e) {
    header("Location: /user/dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = trim($_POST['category'] ?? '');

    if (empty($title) || empty($content) || empty($category)) {
        $error = 'Please fill in all fields.';
    } else {
        $risk = analyze_post_risk($title . ' ' . $content);
        try {
            // Re-edit resets status to pending so it goes through moderation again
            $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ?, category = ?, status = 'pending', is_urgent = ?, risk_level = ?, sentiment_label = ?, sentiment_score = ? WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$title, $content, $category, $risk['is_urgent'] ? 1 : 0, $risk['risk_level'], $risk['sentiment_label'], $risk['sentiment_score'], $post_id, $user_id])) {
                if ($risk['is_urgent']) {
                    send_emergency_alert($pdo, $title);
                }
                header("Location: /user/dashboard.php?success=Post updated successfully. It has been queued for moderation.");
                exit;
            } else {
                $error = 'Could not update post.';
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
            <li><a href="/user/dashboard.php" class="active">My Posts</a></li>
            <li><a href="/user/create_post.php">Create New Post</a></li>
            <li><a href="/user/mood_log.php">Log Mood</a></li>
            <li><a href="/user/mood_history.php">Mood History</a></li>
            <li><a href="/user/journal.php">Wellness Journal</a></li>
            <li><a href="/user/volunteers.php">Volunteers</a></li>
            <li><a href="/user/messages.php">Messages</a></li>
            <li><a href="/user/appointments.php">My Appointments</a></li>
            <li><a href="/resources.php">Resources</a></li>
            <li><a href="/emergency.php">Emergency Contacts</a></li>
        </ul>
    </aside>

    <div class="main-content">
        <div class="card" style="max-width: 650px; width: 100%;">
            <h2 style="margin-bottom: 1rem;">Edit Post</h2>
            <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 2rem;">
                Modifying your post will return it to the moderation queue to ensure safety guidelines are maintained.
            </p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="edit_post.php?id=<?php echo $post_id; ?>" method="POST">
                <div class="form-group">
                    <label for="title">Title / Summary</label>
                    <input type="text" id="title" name="title" class="form-control" required value="<?php echo htmlspecialchars($post['title']); ?>">
                </div>

                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category" class="form-control" required>
                        <option value="Academic Stress" <?php echo $post['category'] === 'Academic Stress' ? 'selected' : ''; ?>>Academic Stress</option>
                        <option value="Anxiety & Depression" <?php echo $post['category'] === 'Anxiety & Depression' ? 'selected' : ''; ?>>Anxiety & Depression</option>
                        <option value="Relationships" <?php echo $post['category'] === 'Relationships' ? 'selected' : ''; ?>>Relationships</option>
                        <option value="Loneliness" <?php echo $post['category'] === 'Loneliness' ? 'selected' : ''; ?>>Loneliness</option>
                        <option value="General Wellbeing" <?php echo $post['category'] === 'General Wellbeing' ? 'selected' : ''; ?>>General Wellbeing</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="content">What's on your mind?</label>
                    <textarea id="content" name="content" class="form-control" rows="8" required><?php echo htmlspecialchars($post['content']); ?></textarea>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Save Changes</button>
                    <a href="/user/dashboard.php" class="btn btn-secondary" style="flex: 1; text-align: center;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
