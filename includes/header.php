<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);

// Dynamically determine the base URL of the project for assets and links
$script_dir = dirname($_SERVER['SCRIPT_NAME']);
$clean_dir = str_replace(['/user', '/volunteer', '/admin'], '', $script_dir);
$base_url = rtrim($clean_dir, '/\\');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Connect - Anonymous Mental Health Support</title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/style.css">
</head>
<body>
    <header>
        <div class="nav-container">
            <a href="<?php echo $base_url; ?>/index.php" class="logo">
                <span>🌱</span> Community Connect
            </a>
            <ul class="nav-links">
                <li><a href="<?php echo $base_url; ?>/index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">Home</a></li>
                <li><a href="<?php echo $base_url; ?>/resources.php" class="<?php echo $current_page == 'resources.php' ? 'active' : ''; ?>">Resources</a></li>
                <li><a href="<?php echo $base_url; ?>/emergency.php" class="<?php echo $current_page == 'emergency.php' ? 'active' : ''; ?>">Emergency Help</a></li>
                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] !== 'admin'): ?>
                    <li><a href="<?php echo $base_url; ?>/polls.php" class="<?php echo $current_page == 'polls.php' ? 'active' : ''; ?>">Polls</a></li>
                <?php endif; ?>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="<?php echo $base_url; ?>/assistant.php" class="<?php echo $current_page == 'assistant.php' ? 'active' : ''; ?>">AI Assistant</a></li>
                <?php endif; ?>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li><a href="<?php echo $base_url; ?>/admin/dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">Admin Panel</a></li>
                    <?php elseif ($_SESSION['role'] === 'volunteer'): ?>
                        <li><a href="<?php echo $base_url; ?>/volunteer/dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">Volunteer Portal</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo $base_url; ?>/user/dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a></li>
                    <?php endif; ?>
                    <li><span style="color: var(--text-secondary);">(@<?php echo htmlspecialchars($_SESSION['username']); ?>)</span></li>
                    <li><a href="<?php echo $base_url; ?>/logout.php" class="btn btn-secondary">Logout</a></li>
                <?php else: ?>
                    <li><a href="<?php echo $base_url; ?>/login.php" class="<?php echo $current_page == 'login.php' ? 'active' : ''; ?>">Login</a></li>
                    <li><a href="<?php echo $base_url; ?>/register.php" class="btn btn-primary">Join Anonymously</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </header>
    <main style="flex: 1; display: flex; flex-direction: column;">
