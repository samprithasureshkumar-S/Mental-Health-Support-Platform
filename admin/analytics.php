<?php
require_once '../config/db.php';
require_once '../includes/auth_helper.php';

restrict_to_role('admin');

// Posts per month (last 12 months)
$monthly_counts = [];
$stmt = $pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS total FROM posts GROUP BY ym");
foreach ($stmt->fetchAll() as $row) {
    $monthly_counts[$row['ym']] = (int)$row['total'];
}
$posts_labels = [];
$posts_data = [];
for ($i = 11; $i >= 0; $i--) {
    $ym = date('Y-m', strtotime("-$i months"));
    $posts_labels[] = date('M Y', strtotime("-$i months"));
    $posts_data[] = $monthly_counts[$ym] ?? 0;
}

// Category distribution
$stmt = $pdo->query("SELECT category, COUNT(*) AS total FROM posts GROUP BY category ORDER BY total DESC");
$category_rows = $stmt->fetchAll();
$category_labels = array_column($category_rows, 'category');
$category_data = array_map('intval', array_column($category_rows, 'total'));

// Site-wide mood trend (last 30 days)
$mood_score = ['Depressed' => 1, 'Stressed' => 2, 'Sad' => 3, 'Neutral' => 4, 'Happy' => 5];
$stmt = $pdo->query("SELECT mood, created_at FROM moods WHERE created_at >= (NOW() - INTERVAL 30 DAY)");
$daily_scores = [];
foreach ($stmt->fetchAll() as $row) {
    $day = date('Y-m-d', strtotime($row['created_at']));
    $daily_scores[$day][] = $mood_score[$row['mood']];
}
$mood_labels = [];
$mood_data = [];
for ($i = 29; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-$i days"));
    $mood_labels[] = date('M d', strtotime($day));
    $scores = $daily_scores[$day] ?? [];
    $mood_data[] = $scores ? round(array_sum($scores) / count($scores), 2) : null;
}

// Volunteer activity (reply counts + average rating)
$stmt = $pdo->query("
    SELECT u.id, u.username,
           (SELECT COUNT(*) FROM replies r WHERE r.volunteer_id = u.id) AS reply_count,
           (SELECT AVG(rating) FROM ratings ra WHERE ra.volunteer_id = u.id) AS avg_rating
    FROM users u
    WHERE u.role = 'volunteer'
    ORDER BY reply_count DESC
");
$volunteer_rows = $stmt->fetchAll();
$volunteer_labels = array_column($volunteer_rows, 'username');
$volunteer_data = array_map('intval', array_column($volunteer_rows, 'reply_count'));

require_once '../includes/header.php';
?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">Admin Menu</h3>
        <ul class="sidebar-nav">
            <li><a href="/admin/dashboard.php">Overview & Moderation</a></li>
            <li><a href="/admin/analytics.php" class="active">Analytics</a></li>
            <li><a href="/admin/users.php">Manage Users</a></li>
            <li><a href="/admin/resources.php">Manage Resources</a></li>
            <li><a href="/admin/polls.php">Manage Polls</a></li>
            <li><a href="/resources.php">View Resources</a></li>
            <li><a href="/emergency.php">Emergency Helpline</a></li>
        </ul>
    </aside>

    <div class="main-content">
        <div>
            <h1 style="font-size: 2rem; margin-bottom: 0.5rem;">Analytics Dashboard</h1>
            <p style="color: var(--text-secondary);">Site-wide trends across posts, moods, and volunteer activity.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
            <div class="card chart-container">
                <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">Posts per Month</h3>
                <canvas id="postsChart"></canvas>
            </div>
            <div class="card chart-container">
                <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">Category Distribution</h3>
                <canvas id="categoryChart"></canvas>
            </div>
            <div class="card chart-container">
                <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">Site-wide Mood Trend (30 days)</h3>
                <canvas id="moodChart"></canvas>
            </div>
            <div class="card chart-container">
                <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">Volunteer Activity (Replies)</h3>
                <canvas id="volunteerChart"></canvas>
            </div>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">Volunteer Performance</h3>
            <?php if (empty($volunteer_rows)): ?>
                <p style="color: var(--text-secondary); text-align: center; padding: 1rem 0;">No volunteers yet.</p>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Volunteer</th>
                                <th>Replies</th>
                                <th>Average Rating</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($volunteer_rows as $v): ?>
                                <tr>
                                    <td>@<?php echo htmlspecialchars($v['username']); ?></td>
                                    <td><?php echo $v['reply_count']; ?></td>
                                    <td><?php echo $v['avg_rating'] ? round($v['avg_rating'], 1) . ' ★' : '—'; ?></td>
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
const axisColor = '#94a3b8';
const gridColor = 'rgba(255,255,255,0.05)';

new Chart(document.getElementById('postsChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($posts_labels); ?>,
        datasets: [{
            label: 'Posts',
            data: <?php echo json_encode($posts_data); ?>,
            backgroundColor: 'rgba(16, 185, 129, 0.6)'
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            x: { ticks: { color: axisColor }, grid: { color: gridColor } },
            y: { ticks: { color: axisColor }, grid: { color: gridColor }, beginAtZero: true }
        }
    }
});

new Chart(document.getElementById('categoryChart'), {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($category_labels); ?>,
        datasets: [{
            data: <?php echo json_encode($category_data); ?>,
            backgroundColor: ['#10b981', '#06b6d4', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899']
        }]
    },
    options: {
        plugins: { legend: { position: 'bottom', labels: { color: axisColor } } }
    }
});

new Chart(document.getElementById('moodChart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($mood_labels); ?>,
        datasets: [{
            label: 'Avg Mood',
            data: <?php echo json_encode($mood_data); ?>,
            borderColor: '#06b6d4',
            backgroundColor: 'rgba(6, 182, 212, 0.15)',
            tension: 0.3,
            fill: true,
            spanGaps: true
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            x: { ticks: { color: axisColor }, grid: { color: gridColor } },
            y: { min: 1, max: 5, ticks: { stepSize: 1, color: axisColor }, grid: { color: gridColor } }
        }
    }
});

new Chart(document.getElementById('volunteerChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($volunteer_labels); ?>,
        datasets: [{
            label: 'Replies',
            data: <?php echo json_encode($volunteer_data); ?>,
            backgroundColor: 'rgba(6, 182, 212, 0.6)'
        }]
    },
    options: {
        indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: {
            x: { ticks: { color: axisColor }, grid: { color: gridColor }, beginAtZero: true },
            y: { ticks: { color: axisColor }, grid: { color: gridColor } }
        }
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
