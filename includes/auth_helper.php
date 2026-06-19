<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Dynamically determine the base URL of the project
$script_dir = dirname($_SERVER['SCRIPT_NAME']);
$clean_dir = str_replace(['/user', '/volunteer', '/admin'], '', $script_dir);
$base_url = rtrim($clean_dir, '/\\');

function check_login() {
    global $base_url;
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . $base_url . "/login.php");
        exit;
    }
}

function restrict_to_role($role) {
    global $base_url;
    check_login();
    if ($_SESSION['role'] !== $role) {
        header("Location: " . $base_url . "/index.php");
        exit;
    }
}

function redirect_if_logged_in() {
    global $base_url;
    if (isset($_SESSION['user_id'])) {
        if ($_SESSION['role'] === 'admin') {
            header("Location: " . $base_url . "/admin/dashboard.php");
        } elseif ($_SESSION['role'] === 'volunteer') {
            header("Location: " . $base_url . "/volunteer/dashboard.php");
        } else {
            header("Location: " . $base_url . "/user/dashboard.php");
        }
        exit;
    }
}
?>
