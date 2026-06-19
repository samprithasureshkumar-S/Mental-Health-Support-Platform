<?php
require_once 'config/db.php';
require_once 'includes/auth_helper.php';

check_login();

$user_id = $_SESSION['user_id'];
$error_msg = $_GET['error'] ?? '';

try {
    $polls = $pdo->query("SELECT * FROM polls WHERE is_active = 1 ORDER BY created_at DESC")->fetchAll();

    $voted_stmt = $pdo->prepare("SELECT poll_id, option_id FROM poll_votes WHERE user_id = ? AND poll_id = ?");

    foreach ($polls as &$poll) {
        $opt_stmt = $pdo->prepare("
            SELECT po.id, po.option_text, COUNT(pv.id) AS vote_count
            FROM poll_options po
            LEFT JOIN poll_votes pv ON pv.option_id = po.id
            WHERE po.poll_id = ?
            GROUP BY po.id
        ");
        $opt_stmt->execute([$poll['id']]);
        $poll['options'] = $opt_stmt->fetchAll();
        $poll['total_votes'] = array_sum(array_column($poll['options'], 'vote_count'));

        $voted_stmt->execute([$user_id, $poll['id']]);
        $voted = $voted_stmt->fetch();
        $poll['my_vote'] = $voted ? $voted['option_id'] : null;
    }
    unset($poll);
} catch (PDOException $e) {
    $polls = [];
    $error_msg = "Could not load polls.";
}

require_once 'includes/header.php';
?>

<div style="max-width: 800px; margin: 4rem auto; padding: 0 2rem; width: 100%;">
    <div style="text-align: center; margin-bottom: 3rem;">
        <h1 style="font-size: 2.5rem; margin-bottom: 1rem;">Community Polls</h1>
        <p style="color: var(--text-secondary); max-width: 600px; margin: 0 auto;">
            Vote anonymously and see how the community is feeling. Your vote is never shown alongside your name.
        </p>
    </div>

    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_msg); ?></div>
    <?php endif; ?>

    <?php if (empty($polls)): ?>
        <div class="card" style="text-align: center;">
            <p style="color: var(--text-secondary);">No active polls right now. Check back soon!</p>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <?php foreach ($polls as $poll): ?>
                <div class="card">
                    <h3 style="margin-bottom: 1rem;"><?php echo htmlspecialchars($poll['question']); ?></h3>

                    <?php if ($poll['my_vote'] === null): ?>
                        <form action="poll_vote.php" method="POST">
                            <input type="hidden" name="poll_id" value="<?php echo $poll['id']; ?>">
                            <?php foreach ($poll['options'] as $opt): ?>
                                <label style="display: block; margin-bottom: 0.6rem; cursor: pointer;">
                                    <input type="radio" name="option_id" value="<?php echo $opt['id']; ?>" required>
                                    <?php echo htmlspecialchars($opt['option_text']); ?>
                                </label>
                            <?php endforeach; ?>
                            <button type="submit" class="btn btn-primary" style="margin-top: 0.8rem;">Vote</button>
                        </form>
                    <?php else: ?>
                        <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1rem;"><?php echo $poll['total_votes']; ?> votes total — thanks for voting!</p>
                        <?php foreach ($poll['options'] as $opt): ?>
                            <?php $pct = $poll['total_votes'] > 0 ? round(($opt['vote_count'] / $poll['total_votes']) * 100) : 0; ?>
                            <div class="poll-option-bar">
                                <div class="poll-result-fill<?php echo $opt['id'] == $poll['my_vote'] ? ' mine' : ''; ?>" style="width: <?php echo $pct; ?>%;"></div>
                                <span><?php echo htmlspecialchars($opt['option_text']); ?> — <?php echo $pct; ?>% (<?php echo $opt['vote_count']; ?>)<?php echo $opt['id'] == $poll['my_vote'] ? ' ✓ your vote' : ''; ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
