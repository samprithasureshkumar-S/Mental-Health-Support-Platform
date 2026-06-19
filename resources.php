<?php
require_once 'config/db.php';
require_once 'includes/header.php';

try {
    $stmt = $pdo->query("SELECT * FROM resources ORDER BY created_at DESC");
    $resources = $stmt->fetchAll();
} catch (PDOException $e) {
    $resources = [];
    $error = "Could not load resources.";
}
?>

<div style="max-width: 1200px; margin: 4rem auto; padding: 0 2rem; width: 100%;">
    <div style="text-align: center; margin-bottom: 3rem;">
        <h1 style="font-size: 2.5rem; margin-bottom: 1rem;">Wellness Resource Library</h1>
        <p style="color: var(--text-secondary); max-width: 600px; margin: 0 auto;">
            Explore curated articles, mental health tips, and relaxation guides compiled by our support team and mental health experts.
        </p>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger" style="max-width: 600px; margin: 0 auto 2rem auto;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (empty($resources)): ?>
        <div class="card" style="text-align: center; max-width: 600px; margin: 0 auto;">
            <p style="color: var(--text-secondary);">No articles are currently available in the resource library. Check back soon!</p>
        </div>
    <?php else: ?>
        <div class="resource-grid">
            <?php foreach ($resources as $resource): ?>
                <div class="card resource-card">
                    <div>
                        <span class="post-category" style="margin-bottom: 1rem; display: inline-block;">
                            <?php echo htmlspecialchars($resource['category']); ?>
                        </span>
                        <h3 style="margin-bottom: 1rem; font-weight: 600;"><?php echo htmlspecialchars($resource['title']); ?></h3>
                        <p style="color: var(--text-secondary); font-size: 0.95rem; margin-bottom: 1.5rem;">
                            <?php echo nl2br(htmlspecialchars($resource['content'])); ?>
                        </p>
                    </div>
                    <div style="border-top: 1px solid var(--glass-border); padding-top: 1rem; font-size: 0.85rem; color: var(--text-secondary); display: flex; justify-content: space-between;">
                        <span>By: <strong><?php echo htmlspecialchars($resource['author']); ?></strong></span>
                        <span><?php echo date('M d, Y', strtotime($resource['created_at'])); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
