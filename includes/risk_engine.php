<?php
// Rule-based crisis detection and sentiment scoring for post content.
// No external API required - pure keyword/word-list scoring.

const CRISIS_KEYWORDS = [
    'suicide',
    'self harm',
    'self-harm',
    'kill myself',
    'hopeless',
    'worthless',
    'end my life',
];

const NEGATIVE_WORDS = [
    'sad', 'depressed', 'depression', 'anxious', 'anxiety', 'stressed', 'stress',
    'tired', 'exhausted', 'alone', 'lonely', 'lonliness', 'loneliness', 'crying',
    'cry', 'scared', 'afraid', 'fear', 'angry', 'hate', 'overwhelmed', 'panic',
    'numb', 'empty', 'broken', 'fail', 'failure', 'pain', 'hurt', 'struggling',
    'struggle', 'lost', 'dark', 'pressure', 'burnout', 'burned out',
];

const POSITIVE_WORDS = [
    'happy', 'grateful', 'thankful', 'hope', 'hopeful', 'better', 'improving',
    'calm', 'relieved', 'support', 'supported', 'love', 'loved', 'proud',
    'progress', 'healing', 'recovered', 'recovering', 'good', 'great', 'glad',
    'okay', 'fine', 'stable', 'motivated', 'confident',
];

function analyze_post_risk(string $content): array {
    $lower = strtolower($content);

    foreach (CRISIS_KEYWORDS as $keyword) {
        if (str_contains($lower, $keyword)) {
            return [
                'is_urgent' => true,
                'risk_level' => 'high',
                'sentiment_label' => 'critical',
                'sentiment_score' => -1.0,
            ];
        }
    }

    $words = preg_split('/\W+/', $lower, -1, PREG_SPLIT_NO_EMPTY);
    $word_count = max(count($words), 1);

    $negative_hits = 0;
    $positive_hits = 0;
    foreach (NEGATIVE_WORDS as $word) {
        if (str_contains($lower, $word)) {
            $negative_hits++;
        }
    }
    foreach (POSITIVE_WORDS as $word) {
        if (str_contains($lower, $word)) {
            $positive_hits++;
        }
    }

    $raw_score = ($positive_hits - $negative_hits) / max($positive_hits + $negative_hits, 1);
    $sentiment_score = round(max(-1.0, min(1.0, $raw_score)), 2);

    if ($sentiment_score <= -0.6) {
        $sentiment_label = 'negative';
        $risk_level = 'medium';
    } elseif ($sentiment_score < 0.2) {
        $sentiment_label = 'neutral';
        $risk_level = $negative_hits > 0 ? 'low' : 'none';
    } else {
        $sentiment_label = 'positive';
        $risk_level = 'none';
    }

    return [
        'is_urgent' => false,
        'risk_level' => $risk_level,
        'sentiment_label' => $sentiment_label,
        'sentiment_score' => $sentiment_score,
    ];
}
