<?php
require_once 'config/db.php';
require_once 'includes/header.php';

try {
    $stmt = $pdo->query("SELECT * FROM helplines ORDER BY id ASC");
    $helplines = $stmt->fetchAll();
} catch (PDOException $e) {
    $helplines = [];
    $error = "Could not load helplines.";
}
?>

<div style="max-width: 900px; margin: 4rem auto; padding: 0 2rem; width: 100%;">
    <div style="text-align: center; margin-bottom: 3rem;">
        <h1 style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--danger);">Emergency Support & Helplines</h1>
        <p style="color: var(--text-secondary); max-width: 600px; margin: 0 auto;">
            If you or someone you know is in immediate danger or experiencing a life-threatening crisis, please contact these emergency resources right away.
        </p>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        <?php if (empty($helplines)): ?>
            <div class="card helpline-card">
                <h3 style="color: var(--danger); margin-bottom: 0.5rem;">Immediate Emergency Services</h3>
                <p style="margin-bottom: 0.5rem;">Please call your local emergency services hotline (e.g., 911, 112, 100) immediately.</p>
                <span style="font-weight: bold;">Available: 24/7</span>
            </div>
        <?php else: ?>
            <?php foreach ($helplines as $helpline): ?>
                <div class="card helpline-card">
                    <h3 style="color: var(--danger); margin-bottom: 0.5rem;"><?php echo htmlspecialchars($helpline['name']); ?></h3>
                    <p style="margin-bottom: 0.5rem; font-size: 1.1rem;">Phone/Contact: <strong style="color: var(--text-primary);"><?php echo htmlspecialchars($helpline['phone']); ?></strong></p>
                    <p style="color: var(--text-secondary); font-size: 0.95rem; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($helpline['description']); ?></p>
                    <span style="font-size: 0.85rem; background-color: rgba(239, 68, 68, 0.1); color: var(--danger); padding: 0.2rem 0.6rem; border-radius: 4px; font-weight: 600;">
                        Hours: <?php echo htmlspecialchars($helpline['hours']); ?>
                    </span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
