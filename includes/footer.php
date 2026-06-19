<?php
// Calculate base URL locally if not set
if (!isset($base_url)) {
    $script_dir = dirname($_SERVER['SCRIPT_NAME']);
    $clean_dir = str_replace(['/user', '/volunteer', '/admin'], '', $script_dir);
    $base_url = rtrim($clean_dir, '/\\');
}
?>
    </main>
    <footer>
        <div class="footer-container">
            <p>&copy; <?php echo date('Y'); ?> Community Connect. Designed with care for safe communities. 🌱</p>
            <p style="font-size: 0.8rem; margin-top: 0.5rem; color: var(--text-secondary);">Your anonymity is fully respected. If you are experiencing a severe crisis, please visit our <a href="<?php echo $base_url; ?>/emergency.php" style="color: var(--accent-secondary); text-decoration: underline;">Emergency Help</a> section immediately.</p>
        </div>
    </footer>
    <script src="<?php echo $base_url; ?>/assets/js/main.js"></script>
</body>
</html>
