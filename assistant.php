<?php
require_once 'config/db.php';
require_once 'includes/auth_helper.php';

check_login();

require_once 'includes/header.php';
?>

<div style="max-width: 700px; margin: 3rem auto; padding: 0 2rem; width: 100%;">
    <div style="text-align: center; margin-bottom: 2rem;">
        <h1 style="font-size: 2.2rem; margin-bottom: 0.5rem;">AI Mental Health Assistant</h1>
        <p style="color: var(--text-secondary);">A supportive space for stress management tips, breathing exercises, and coping strategies. This is a rule-based assistant, not a substitute for professional help.</p>
    </div>

    <div class="card" style="padding: 0; display: flex; flex-direction: column; height: 65vh;">
        <div id="chatWindow" class="chat-window">
            <div class="chat-bubble theirs">
                <p>Hi, I'm here to listen. You can ask me about stress, breathing exercises, grounding techniques, coping strategies, or finding support resources.</p>
                <span class="chat-time">Assistant</span>
            </div>
        </div>

        <form id="chatForm" class="chat-input-bar">
            <input type="text" id="chatInput" class="form-control" placeholder="Type how you're feeling..." aria-label="Type how you're feeling" required autocomplete="off">
            <button type="submit" class="btn btn-primary" id="chatSendBtn">Send</button>
        </form>
    </div>
</div>

<script>
(function () {
    const chatWindow = document.getElementById('chatWindow');
    const form = document.getElementById('chatForm');
    const input = document.getElementById('chatInput');
    const sendBtn = document.getElementById('chatSendBtn');

    function addBubble(text, who) {
        const bubble = document.createElement('div');
        bubble.className = 'chat-bubble ' + who;
        const p = document.createElement('p');
        p.textContent = text;
        bubble.appendChild(p);
        chatWindow.appendChild(bubble);
        chatWindow.scrollTop = chatWindow.scrollHeight;
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const message = input.value.trim();
        if (!message) return;

        addBubble(message, 'mine');
        input.value = '';
        sendBtn.disabled = true;
        sendBtn.textContent = 'Sending…';

        fetch('<?php echo $base_url; ?>/api/chatbot_reply.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'message=' + encodeURIComponent(message)
        })
            .then(res => res.json())
            .then(data => {
                addBubble(data.reply || "Sorry, I couldn't process that.", 'theirs');
            })
            .catch(() => {
                addBubble("Something went wrong. Please try again.", 'theirs');
            })
            .finally(() => {
                sendBtn.disabled = false;
                sendBtn.textContent = 'Send';
            });
    });
})();
</script>

<?php require_once 'includes/footer.php'; ?>
