<?php
require_once '../config/db.php';
require_once '../includes/auth_helper.php';

restrict_to_role('user');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $user_id = $_SESSION['user_id'];

    if (empty($title) || empty($content) || empty($category)) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO posts (user_id, title, content, category, status) VALUES (?, ?, ?, ?, 'pending')");
            if ($stmt->execute([$user_id, $title, $content, $category])) {
                header("Location: " . $base_url . "/user/dashboard.php?success=Post submitted successfully. It will appear publicly once approved by moderators.");
                exit;
            } else {
                $error = 'Could not save the post. Please try again.';
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
            <li><a href="<?php echo $base_url; ?>/user/create_post.php" class="active">Create New Post</a></li>
            <li><a href="<?php echo $base_url; ?>/resources.php">Resources</a></li>
            <li><a href="<?php echo $base_url; ?>/emergency.php">Emergency Contacts</a></li>
        </ul>
    </aside>

    <div class="main-content">
        <div class="card" style="max-width: 650px; width: 100%;">
            <h2 style="margin-bottom: 1rem;">Share Your Concern Anonymously</h2>
            <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 2rem;">
                Write down what is on your mind. Admins will review your post to ensure safety guidelines are met before it is shown on the community feed.
            </p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="create_post.php" method="POST">
                <div class="form-group">
                    <label for="title">Title / Summary</label>
                    <input type="text" id="title" name="title" class="form-control" placeholder="E.g., Feeling overwhelmed by upcoming finals" required value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category" class="form-control" required>
                        <option value="">-- Select Category --</option>
                        <option value="Academic Stress">Academic Stress</option>
                        <option value="Anxiety & Depression">Anxiety & Depression</option>
                        <option value="Relationships">Relationships</option>
                        <option value="Loneliness">Loneliness</option>
                        <option value="General Wellbeing">General Wellbeing</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="content">What's on your mind?</label>
                    <textarea id="content" name="content" class="form-control" rows="8" placeholder="Describe how you are feeling... Remember, please do not share personal details like your real name, address, or phone number to maintain full anonymity." required><?php echo isset($content) ? htmlspecialchars($content) : ''; ?></textarea>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Submit Post</button>
                    <a href="<?php echo $base_url; ?>/user/dashboard.php" class="btn btn-secondary" style="flex: 1; text-align: center;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
