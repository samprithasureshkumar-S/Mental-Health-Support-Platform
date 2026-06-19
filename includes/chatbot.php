<?php
require_once __DIR__ . '/risk_engine.php';

const BOT_TOPICS = [
    'stress' => [
        'keywords' => ['stress', 'stressed', 'overwhelmed', 'pressure', 'deadline', 'exam', 'finals'],
        'reply' => "It sounds like you're under a lot of pressure. Try breaking your tasks into smaller steps, take short breaks, and remember it's okay to ask for help. Would you like a breathing exercise to help you reset?",
    ],
    'breathing' => [
        'keywords' => ['breathing', 'breathe', 'breath', 'panic', 'panic attack'],
        'reply' => "Let's try Box Breathing together: inhale slowly for 4 seconds, hold for 4 seconds, exhale for 4 seconds, hold for 4 seconds. Repeat this 5 times. Notice how your body starts to settle.",
    ],
    'grounding' => [
        'keywords' => ['grounding', 'disconnected', 'dissociat', 'racing thoughts', 'cant focus', "can't focus"],
        'reply' => "Try the 5-4-3-2-1 grounding technique: name 5 things you can see, 4 things you can touch, 3 things you can hear, 2 things you can smell, and 1 thing you can taste. This can help bring you back to the present moment.",
    ],
    'lonely' => [
        'keywords' => ['lonely', 'loneliness', 'alone', 'isolated', 'no friends', 'nobody'],
        'reply' => "Feeling alone is really hard. Reaching out, even in small ways, can help — consider messaging one of our volunteers or joining a community poll to see you're not alone in how you feel. You matter here.",
    ],
    'sad' => [
        'keywords' => ['sad', 'depressed', 'depression', 'down', 'empty', 'numb'],
        'reply' => "I'm sorry you're feeling this way. These feelings are valid, and you don't have to carry them alone. Logging your mood and journaling can help you track patterns — and our volunteers are here to listen whenever you're ready.",
    ],
    'coping' => [
        'keywords' => ['cope', 'coping', 'cant handle', "can't handle", 'struggling'],
        'reply' => "Some coping strategies that help many people: grounding exercises, talking to someone you trust, gentle movement, and writing down your thoughts in a journal. Would you like to try a breathing or grounding exercise now?",
    ],
    'resources' => [
        'keywords' => ['ngo', 'helpline', 'organization', 'resource', 'resources', 'support group'],
        'reply' => "You can find curated articles and self-care guides in our Resource Library, and a list of crisis helplines on the Emergency Help page. Would you like me to point you to either of those?",
    ],
];

function get_bot_reply(string $message): string {
    $lower = strtolower($message);

    foreach (CRISIS_KEYWORDS as $keyword) {
        if (str_contains($lower, $keyword)) {
            return "I'm really concerned about what you've shared. Please reach out for immediate support — visit our Emergency Help page or contact a crisis helpline right now. You don't have to go through this alone.";
        }
    }

    foreach (BOT_TOPICS as $topic) {
        foreach ($topic['keywords'] as $keyword) {
            if (str_contains($lower, $keyword)) {
                return $topic['reply'];
            }
        }
    }

    return "Thank you for sharing that with me. I'm here to help with stress management, breathing exercises, grounding techniques, and coping strategies. Could you tell me a bit more about what's on your mind?";
}
