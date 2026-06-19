<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Dynamically determine base URL
$script_dir = dirname($_SERVER['SCRIPT_NAME']);
$clean_dir = str_replace(['/user', '/volunteer', '/admin'], '', $script_dir);
$base_url = rtrim($clean_dir, '/\\');

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to home page
header("Location: " . $base_url . "/index.php");
exit;
?>
