<?php
require_once '../config/db.php';
require_once '../includes/auth_helper.php';

restrict_to_role('admin');

$success_msg = $_GET['success'] ?? '';
$error_msg = $_GET['error'] ?? '';

// Handle add or delete resource actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_resource'])) {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $author = trim($_POST['author'] ?? '');

        if (empty($title) || empty($content) || empty($category) || empty($author)) {
            $error_msg = "Please fill in all fields.";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO resources (title, content, category, author) VALUES (?, ?, ?, ?)");
                $stmt->execute([$title, $content, $category, $author]);
                $success_msg = "Resource added successfully!";
            } catch (PDOException $e) {
                $error_msg = "Failed to add resource.";
            }
        }
    }

    if (isset($_POST['delete_resource'])) {
        $resource_id = $_POST['resource_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM resources WHERE id = ?");
            $stmt->execute([$resource_id]);
            $success_msg = "Resource deleted successfully.";
        } catch (PDOException $e) {
            $error_msg = "Failed to delete resource.";
        }
    }
}

// Fetch resources
try {
    $stmt = $pdo->query("SELECT * FROM resources ORDER BY created_at DESC");
    $resources = $stmt->fetchAll();
} catch (PDOException $e) {
    $resources = [];
    $error_msg = "Failed to fetch resources.";
}

require_once '../includes/header.php';
?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">Admin Menu</h3>
        <ul class="sidebar-nav">
            <li><a href="/admin/dashboard.php">Overview & Moderation</a></li>
            <li><a href="/admin/analytics.php">Analytics</a></li>
            <li><a href="/admin/users.php">Manage Users</a></li>
            <li><a href="/admin/resources.php" class="active">Manage Resources</a></li>
            <li><a href="/admin/polls.php">Manage Polls</a></li>
            <li><a href="/resources.php">View Resources</a></li>
            <li><a href="/emergency.php">Emergency Helpline</a></li>
        </ul>
    </aside>

    <div class="main-content">
        <div>
            <h1 style="font-size: 2rem; margin-bottom: 0.5rem;">Resource Library Management</h1>
            <p style="color: var(--text-secondary);">Add, edit, or remove mental health self-care guides and educational articles.</p>
        </div>

        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_msg); ?></div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_msg); ?></div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <!-- Add Resource Form -->
            <div class="card">
                <h3 style="margin-bottom: 1.5rem;">Add New Resource</h3>
                <form action="resources.php" method="POST">
                    <input type="hidden" name="add_resource" value="1">
                    <div class="form-group">
                        <label for="title">Article Title</label>
                        <input type="text" id="title" name="title" class="form-control" placeholder="E.g., Mindfulness Hacks" required>
                    </div>
                    <div class="form-group">
                        <label for="category">Category</label>
                        <input type="text" id="category" name="category" class="form-control" placeholder="E.g., Self-care" required>
                    </div>
                    <div class="form-group">
                        <label for="author">Author</label>
                        <input type="text" id="author" name="author" class="form-control" placeholder="E.g., Jane Doe, Psychologist" required>
                    </div>
                    <div class="form-group">
                        <label for="content">Article Content</label>
                        <textarea id="content" name="content" class="form-control" rows="5" placeholder="Write the content details..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Publish Resource</button>
                </form>
            </div>

            <!-- Existing Resources List -->
            <div class="card" style="display: flex; flex-direction: column; gap: 1rem; max-height: 600px; overflow-y: auto;">
                <h3 style="margin-bottom: 0.5rem;">Existing Resources</h3>
                <?php if (empty($resources)): ?>
                    <p style="color: var(--text-secondary); text-align: center; margin-top: 2rem;">No resources created yet.</p>
                <?php else: ?>
                    <?php foreach ($resources as $resource): ?>
                        <div style="background-color: rgba(15,23,42,0.4); border: 1px solid var(--glass-border); padding: 1rem; border-radius: 8px; position: relative;">
                            <h4 style="font-size: 1rem; margin-bottom: 0.3rem;"><?php echo htmlspecialchars($resource['title']); ?></h4>
                            <span class="post-category" style="font-size: 0.75rem; padding: 0.1rem 0.4rem;"><?php echo htmlspecialchars($resource['category']); ?></span>
                            <p style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.5rem; line-height: 1.4;">
                                <?php echo htmlspecialchars(substr($resource['content'], 0, 100)) . '...'; ?>
                            </p>
                            <div style="margin-top: 1rem; display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 0.8rem; color: var(--text-secondary);">By: <?php echo htmlspecialchars($resource['author']); ?></span>
                                <form action="resources.php" method="POST" class="btn-confirm-delete" style="display: inline;">
                                    <input type="hidden" name="resource_id" value="<?php echo $resource['id']; ?>">
                                    <button type="submit" name="delete_resource" class="btn btn-danger" style="padding: 0.25rem 0.6rem; font-size: 0.8rem;">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
