<?php
require_once '../config/db.php';
require_once '../includes/auth_helper.php';

restrict_to_role('user');

$user_id = $_SESSION['user_id'];
$success_msg = $_GET['success'] ?? '';

$mood_score = [
    'Depressed' => 1,
    'Stressed' => 2,
    'Sad' => 3,
    'Neutral' => 4,
    'Happy' => 5,
];
$mood_emoji = [
    'Happy' => '😊',
    'Neutral' => '😐',
    'Sad' => '😢',
    'Stressed' => '😣',
    'Depressed' => '😔',
];

try {
    $stmt = $pdo->prepare("SELECT * FROM moods WHERE user_id = ? AND created_at >= (NOW() - INTERVAL 30 DAY) ORDER BY created_at ASC");
    $stmt->execute([$user_id]);
    $recent_moods = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT * FROM moods WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
    $stmt->execute([$user_id]);
    $history = $stmt->fetchAll();
} catch (PDOException $e) {
    $recent_moods = [];
    $history = [];
    $error = "Could not load mood history.";
}

// Aggregate average mood score per day for the last 30 days
$daily_scores = [];
foreach ($recent_moods as $entry) {
    $day = date('Y-m-d', strtotime($entry['created_at']));
    $daily_scores[$day][] = $mood_score[$entry['mood']];
}

$monthly_labels = [];
$monthly_data = [];
for ($i = 29; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-$i days"));
    $monthly_labels[] = date('M d', strtotime($day));
    $scores = $daily_scores[$day] ?? [];
    $monthly_data[] = $scores ? round(array_sum($scores) / count($scores), 2) : null;
}

$weekly_labels = array_slice($monthly_labels, -7);
$weekly_data = array_slice($monthly_data, -7);

require_once '../includes/header.php';
?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">Menu</h3>
        <ul class="sidebar-nav">
            <li><a href="<?php echo $base_url; ?>/user/dashboard.php">My Posts</a></li>
            <li><a href="<?php echo $base_url; ?>/user/create_post.php">Create New Post</a></li>
            <li><a href="<?php echo $base_url; ?>/user/mood_log.php">Log Mood</a></li>
            <li><a href="<?php echo $base_url; ?>/user/mood_history.php" class="active">Mood History</a></li>
            <li><a href="<?php echo $base_url; ?>/user/journal.php">Wellness Journal</a></li>
            <li><a href="<?php echo $base_url; ?>/user/volunteers.php">Volunteers</a></li>
            <li><a href="<?php echo $base_url; ?>/user/messages.php">Messages</a></li>
            <li><a href="<?php echo $base_url; ?>/user/appointments.php">My Appointments</a></li>
            <li><a href="<?php echo $base_url; ?>/resources.php">Resources</a></li>
            <li><a href="<?php echo $base_url; ?>/emergency.php">Emergency Contacts</a></li>
        </ul>
    </aside>

    <div class="main-content">
        <div>
            <h1 style="font-size: 2rem; margin-bottom: 0.5rem;">Mood History</h1>
            <p style="color: var(--text-secondary);">Track your emotional wellbeing trends over time.</p>
        </div>

        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_msg); ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
            <div class="card chart-container">
                <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">Weekly Mood Trend</h3>
                <canvas id="weeklyChart"></canvas>
            </div>
            <div class="card chart-container">
                <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">Monthly Mood Trend</h3>
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">Recent Entries</h3>
            <?php if (empty($history)): ?>
                <p style="color: var(--text-secondary); text-align: center; padding: 2rem 0;">No mood entries yet. <a href="<?php echo $base_url; ?>/user/mood_log.php" style="color: var(--accent-primary);">Log your first mood</a>.</p>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Mood</th>
                                <th>Note</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($history as $entry): ?>
                                <tr>
                                    <td><?php echo $mood_emoji[$entry['mood']]; ?> <?php echo htmlspecialchars($entry['mood']); ?></td>
                                    <td style="color: var(--text-secondary);"><?php echo $entry['note'] ? htmlspecialchars($entry['note']) : '—'; ?></td>
                                    <td><?php echo date('M d, Y h:i A', strtotime($entry['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
const chartOptions = {
    scales: {
        y: {
            min: 1,
            max: 5,
            ticks: {
                stepSize: 1,
                callback: function (value) {
                    const labels = { 1: 'Depressed', 2: 'Stressed', 3: 'Sad', 4: 'Neutral', 5: 'Happy' };
                    return labels[value] || '';
                },
                color: '#94a3b8'
            },
            grid: { color: 'rgba(255,255,255,0.05)' }
        },
        x: {
            ticks: { color: '#94a3b8' },
            grid: { color: 'rgba(255,255,255,0.05)' }
        }
    },
    plugins: {
        legend: { display: false }
    },
    spanGaps: true
};

new Chart(document.getElementById('weeklyChart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($weekly_labels); ?>,
        datasets: [{
            label: 'Mood',
            data: <?php echo json_encode($weekly_data); ?>,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.15)',
            tension: 0.3,
            fill: true
        }]
    },
    options: chartOptions
});

new Chart(document.getElementById('monthlyChart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($monthly_labels); ?>,
        datasets: [{
            label: 'Mood',
            data: <?php echo json_encode($monthly_data); ?>,
            borderColor: '#06b6d4',
            backgroundColor: 'rgba(6, 182, 212, 0.15)',
            tension: 0.3,
            fill: true
        }]
    },
    options: chartOptions
});
</script>

<?php require_once '../includes/footer.php'; ?>
