<?php
require_once 'config/db.php';
require_once 'includes/auth_helper.php';

// Redirect if already logged in
redirect_if_logged_in();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Simple Form Validation
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->rowCount() > 0) {
            $error = 'Username or email already registered.';
        } else {
            // Hash password and insert user
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
            if ($stmt->execute([$username, $email, $hashed_password])) {
                $success = 'Account created successfully! You can now log in.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

require_once 'includes/header.php';
?>

<div class="form-container card">
    <h2 style="text-align: center; margin-bottom: 1.5rem;">Join Anonymously</h2>
    <p style="color: var(--text-secondary); text-align: center; font-size: 0.9rem; margin-bottom: 2rem;">
        Your identity is stored securely. Your username is never shown publicly on community posts.
    </p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form action="register.php" method="POST">
        <div class="form-group">
            <label for="username">Username (Alias)</label>
            <input type="text" id="username" name="username" class="form-control" required value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
        </div>
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" class="form-control" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Create Account</button>
    </form>

    <div style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem;">
        <span style="color: var(--text-secondary);">Already have an account? </span>
        <a href="login.php" style="color: var(--accent-primary); text-decoration: none; font-weight: 500;">Login here</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
