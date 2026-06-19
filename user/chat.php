<?php
require_once '../config/db.php';
require_once '../includes/auth_helper.php';
require_once '../includes/messages_helper.php';

restrict_to_role('user');

$user_id = $_SESSION['user_id'];
$volunteer_id = (int)($_GET['volunteer_id'] ?? 0);

$stmt = $pdo->prepare("SELECT id, username FROM users WHERE id = ? AND role = 'volunteer'");
$stmt->execute([$volunteer_id]);
$volunteer = $stmt->fetch();

if (!$volunteer) {
    header("Location: " . $base_url . "/user/volunteers.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content'] ?? '');
    if ($content !== '') {
        send_message($pdo, $user_id, $volunteer_id, $user_id, $content);
    }
    header("Location: " . $base_url . "/user/chat.php?volunteer_id=" . $volunteer_id);
    exit;
}

mark_thread_read($pdo, $user_id, $volunteer_id, 'user');
$thread = get_thread($pdo, $user_id, $volunteer_id);
$last_id = !empty($thread) ? end($thread)['id'] : 0;

require_once '../includes/header.php';
?>

<div class="dashboard-layout">
    <aside class="sidebar">
        <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">Menu</h3>
        <ul class="sidebar-nav">
            <li><a href="<?php echo $base_url; ?>/user/dashboard.php">My Posts</a></li>
            <li><a href="<?php echo $base_url; ?>/user/create_post.php">Create New Post</a></li>
            <li><a href="<?php echo $base_url; ?>/user/mood_log.php">Log Mood</a></li>
            <li><a href="<?php echo $base_url; ?>/user/mood_history.php">Mood History</a></li>
            <li><a href="<?php echo $base_url; ?>/user/journal.php">Wellness Journal</a></li>
            <li><a href="<?php echo $base_url; ?>/user/volunteers.php">Volunteers</a></li>
            <li><a href="<?php echo $base_url; ?>/user/messages.php" class="active">Messages</a></li>
            <li><a href="<?php echo $base_url; ?>/user/appointments.php">My Appointments</a></li>
            <li><a href="<?php echo $base_url; ?>/resources.php">Resources</a></li>
            <li><a href="<?php echo $base_url; ?>/emergency.php">Emergency Contacts</a></li>
        </ul>
    </aside>

    <div class="main-content">
        <div class="card" style="padding: 0; display: flex; flex-direction: column; height: 70vh;">
            <div style="padding: 1.2rem; border-bottom: 1px solid var(--glass-border);">
                <h3>@<?php echo htmlspecialchars($volunteer['username']); ?></h3>
            </div>

            <div id="chatWindow" class="chat-window" data-last-id="<?php echo $last_id; ?>">
                <?php foreach ($thread as $msg): ?>
                    <div class="chat-bubble <?php echo $msg['sender_id'] == $user_id ? 'mine' : 'theirs'; ?>">
                        <p><?php echo nl2br(htmlspecialchars($msg['content'])); ?></p>
                        <span class="chat-time"><?php echo date('h:i A', strtotime($msg['created_at'])); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <form action="chat.php?volunteer_id=<?php echo $volunteer_id; ?>" method="POST" class="chat-input-bar" onsubmit="this.querySelector('button').disabled = true; this.querySelector('button').textContent = 'Sending…';">
                <input type="text" name="content" class="form-control" placeholder="Type a message..." required autocomplete="off" aria-label="Type a message">
                <button type="submit" class="btn btn-primary">Send</button>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    const chatWindow = document.getElementById('chatWindow');
    chatWindow.scrollTop = chatWindow.scrollHeight;
    let lastId = parseInt(chatWindow.dataset.lastId, 10) || 0;

    function poll() {
        fetch('<?php echo $base_url; ?>/api/messages_poll.php?with=<?php echo $volunteer_id; ?>&since=' + lastId)
            .then(res => res.json())
            .then(data => {
                if (!data.messages || !data.messages.length) return;
                data.messages.forEach(msg => {
                    const bubble = document.createElement('div');
                    bubble.className = 'chat-bubble ' + (msg.is_own ? 'mine' : 'theirs');
                    const p = document.createElement('p');
                    p.textContent = msg.content;
                    const span = document.createElement('span');
                    span.className = 'chat-time';
                    span.textContent = msg.time;
                    bubble.appendChild(p);
                    bubble.appendChild(span);
                    chatWindow.appendChild(bubble);
                    lastId = msg.id;
                });
                chatWindow.scrollTop = chatWindow.scrollHeight;
            })
            .catch(() => {});
    }

    setInterval(poll, 4000);
})();
</script>

<?php require_once '../includes/footer.php'; ?>
