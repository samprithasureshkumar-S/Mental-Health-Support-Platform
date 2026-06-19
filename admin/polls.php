<?php
require_once '../config/db.php';
require_once '../includes/auth_helper.php';

restrict_to_role('admin');

$success_msg = $_GET['success'] ?? '';
$error_msg = $_GET['error'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_poll'])) {
    $question = trim($_POST['question'] ?? '');
    $options = array_filter(array_map('trim', $_POST['options'] ?? []));

    if (empty($question) || count($options) < 2) {
        $error_msg = "Please provide a question and at least 2 options.";
    } else {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO polls (question, created_by) VALUES (?, ?)");
            $stmt->execute([$question, $_SESSION['user_id']]);
            $poll_id = $pdo->lastInsertId();

            $opt_stmt = $pdo->prepare("INSERT INTO poll_options (poll_id, option_text) VALUES (?, ?)");
            foreach ($options as $option_text) {
                $opt_stmt->execute([$poll_id, $option_text]);
            }
            $pdo->commit();
            $success_msg = "Poll created successfully!";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error_msg = "Failed to create poll.";
        }
    }
}

try {
    $polls = $pdo->query("SELECT * FROM polls ORDER BY created_at DESC")->fetchAll();
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
    }
    unset($poll);
} catch (PDOException $e) {
    $polls = [];
    $error_msg = "Could not load polls.";
}

require_once '../includes/header.php';
?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">Admin Menu</h3>
        <ul class="sidebar-nav">
            <li><a href="/admin/dashboard.php">Overview & Moderation</a></li>
            <li><a href="/admin/analytics.php">Analytics</a></li>
            <li><a href="/admin/users.php">Manage Users</a></li>
            <li><a href="/admin/resources.php">Manage Resources</a></li>
            <li><a href="/admin/polls.php" class="active">Manage Polls</a></li>
            <li><a href="/resources.php">View Resources</a></li>
            <li><a href="/emergency.php">Emergency Helpline</a></li>
        </ul>
    </aside>

    <div class="main-content">
        <div>
            <h1 style="font-size: 2rem; margin-bottom: 0.5rem;">Anonymous Polls</h1>
            <p style="color: var(--text-secondary);">Create community polls and view live results.</p>
        </div>

        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_msg); ?></div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_msg); ?></div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <div class="card">
                <h3 style="margin-bottom: 1.5rem;">Create New Poll</h3>
                <form action="polls.php" method="POST">
                    <input type="hidden" name="create_poll" value="1">
                    <div class="form-group">
                        <label for="question">Poll Question</label>
                        <input type="text" id="question" name="question" class="form-control" placeholder="E.g., How are you feeling today?" required>
                    </div>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <div class="form-group">
                            <label for="option<?php echo $i; ?>">Option <?php echo $i; ?><?php echo $i > 2 ? ' (optional)' : ''; ?></label>
                            <input type="text" id="option<?php echo $i; ?>" name="options[]" class="form-control" <?php echo $i <= 2 ? 'required' : ''; ?>>
                        </div>
                    <?php endfor; ?>
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Create Poll</button>
                </form>
            </div>

            <div class="card" style="display: flex; flex-direction: column; gap: 1.5rem; max-height: 600px; overflow-y: auto;">
                <h3>Existing Polls</h3>
                <?php if (empty($polls)): ?>
                    <p style="color: var(--text-secondary); text-align: center; margin-top: 2rem;">No polls created yet.</p>
                <?php else: ?>
                    <?php foreach ($polls as $poll): ?>
                        <div style="background-color: rgba(15,23,42,0.4); border: 1px solid var(--glass-border); padding: 1rem; border-radius: 8px;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                <h4 style="font-size: 1rem;"><?php echo htmlspecialchars($poll['question']); ?></h4>
                                <a href="poll_action.php?action=delete&id=<?php echo $poll['id']; ?>" class="btn btn-danger btn-confirm-delete" style="padding: 0.25rem 0.6rem; font-size: 0.8rem;">Delete</a>
                            </div>
                            <p style="font-size: 0.8rem; color: var(--text-secondary); margin: 0.3rem 0 0.8rem;">
                                <?php echo $poll['is_active'] ? 'Active' : 'Inactive'; ?> · <?php echo $poll['total_votes']; ?> votes
                            </p>
                            <?php foreach ($poll['options'] as $opt): ?>
                                <?php $pct = $poll['total_votes'] > 0 ? round(($opt['vote_count'] / $poll['total_votes']) * 100) : 0; ?>
                                <div class="poll-option-bar">
                                    <div class="poll-result-fill" style="width: <?php echo $pct; ?>%;"></div>
                                    <span><?php echo htmlspecialchars($opt['option_text']); ?> — <?php echo $pct; ?>% (<?php echo $opt['vote_count']; ?>)</span>
                                </div>
                            <?php endforeach; ?>
                            <a href="poll_action.php?action=toggle&id=<?php echo $poll['id']; ?>" class="btn btn-secondary" style="padding: 0.25rem 0.6rem; font-size: 0.8rem; margin-top: 0.8rem;">
                                <?php echo $poll['is_active'] ? 'Deactivate' : 'Activate'; ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
