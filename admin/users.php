<?php
require_once '../config/db.php';
require_once '../includes/auth_helper.php';

restrict_to_role('admin');

$success_msg = $_GET['success'] ?? '';
$error_msg = $_GET['error'] ?? '';

// Handle Role Change or Delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['change_role'])) {
        $target_user_id = $_POST['user_id'];
        $new_role = $_POST['role'];
        
        // Prevent changing own role
        if ($target_user_id == $_SESSION['user_id']) {
            $error_msg = "You cannot change your own role.";
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                $stmt->execute([$new_role, $target_user_id]);
                $success_msg = "User role updated successfully.";
            } catch (PDOException $e) {
                $error_msg = "Failed to update role.";
            }
        }
    }
    
    if (isset($_POST['delete_user'])) {
        $target_user_id = $_POST['user_id'];
        
        // Prevent deleting own account
        if ($target_user_id == $_SESSION['user_id']) {
            $error_msg = "You cannot delete your own account.";
        } else {
            try {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$target_user_id]);
                $success_msg = "User deleted successfully.";
            } catch (PDOException $e) {
                $error_msg = "Failed to delete user.";
            }
        }
    }
}

// Fetch all users
try {
    $stmt = $pdo->query("SELECT id, username, email, role, created_at FROM users ORDER BY role ASC, username ASC");
    $all_users = $stmt->fetchAll();
} catch (PDOException $e) {
    $all_users = [];
    $error_msg = "Could not load users.";
}

require_once '../includes/header.php';
?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">Admin Menu</h3>
        <ul class="sidebar-nav">
            <li><a href="/admin/dashboard.php">Overview & Moderation</a></li>
            <li><a href="/admin/analytics.php">Analytics</a></li>
            <li><a href="/admin/users.php" class="active">Manage Users</a></li>
            <li><a href="/admin/resources.php">Manage Resources</a></li>
            <li><a href="/admin/polls.php">Manage Polls</a></li>
            <li><a href="/resources.php">View Resources</a></li>
            <li><a href="/emergency.php">Emergency Helpline</a></li>
        </ul>
    </aside>

    <div class="main-content">
        <div>
            <h1 style="font-size: 2rem; margin-bottom: 0.5rem;">User Management</h1>
            <p style="color: var(--text-secondary);">Manage account credentials, change permissions, or remove users/volunteers from the platform.</p>
        </div>

        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_msg); ?></div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_msg); ?></div>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_users as $user): ?>
                        <tr>
                            <td><strong>@<?php echo htmlspecialchars($user['username']); ?></strong></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <form action="users.php" method="POST" style="display: inline-block;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <select name="role" class="form-control" style="padding: 0.2rem 0.5rem; width: auto; font-size: 0.85rem;" onchange="this.form.submit()">
                                        <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                        <option value="volunteer" <?php echo $user['role'] === 'volunteer' ? 'selected' : ''; ?>>Volunteer</option>
                                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                    <input type="hidden" name="change_role" value="1">
                                </form>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <form action="users.php" method="POST" style="display: inline;" class="btn-confirm-delete">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="delete_user" class="btn btn-danger" style="padding: 0.3rem 0.7rem; font-size: 0.8rem;">Delete</button>
                                    </form>
                                <?php else: ?>
                                    <span style="color: var(--text-secondary); font-style: italic; font-size: 0.85rem;">Active (You)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
