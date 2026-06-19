<?php
require_once 'config/db.php';
require_once 'includes/auth_helper.php';

// Redirect if already logged in
redirect_if_logged_in();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_or_email = trim($_POST['username_or_email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username_or_email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        // Fetch user from DB
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username_or_email, $username_or_email]);
        $user = $stmt->fetch();

        
        if ($user && password_verify($password, $user['password'])) {
            // Start session and store credentials
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: " . $base_url . "/admin/dashboard.php");
            } elseif ($user['role'] === 'volunteer') {
                header("Location: " . $base_url . "/volunteer/dashboard.php");
            } else {
                header("Location: " . $base_url . "/user/dashboard.php");
            }
            exit;
        } else {
            $error = 'Invalid username/email or password.';
        }
    }
}

require_once 'includes/header.php';
?>

<div class="form-container card">
    <h2 style="text-align: center; margin-bottom: 1.5rem;">Welcome Back</h2>
    <p style="color: var(--text-secondary); text-align: center; font-size: 0.9rem; margin-bottom: 2rem;">
        Log in to access your dashboard, view your posts, or respond to threads.
    </p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="form-group">
            <label for="username_or_email">Username or Email</label>
            <input type="text" id="username_or_email" name="username_or_email" class="form-control" required value="<?php echo isset($username_or_email) ? htmlspecialchars($username_or_email) : ''; ?>">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Login</button>
    </form>

    <div style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem;">
        <span style="color: var(--text-secondary);">Don't have an account? </span>
        <a href="register.php" style="color: var(--accent-primary); text-decoration: none; font-weight: 500;">Join here</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
