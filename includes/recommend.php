<?php
// Rule-based resource recommendation engine: matches post category/content
// against a small topic keyword map, then searches the resources library.

const TOPIC_KEYWORDS = [
    'Stress' => ['stress', 'stressed', 'overwhelmed', 'pressure', 'deadline', 'exam', 'finals'],
    'Anxiety' => ['anxiety', 'anxious', 'panic', 'worry', 'worried', 'nervous'],
    'Depression' => ['depression', 'depressed', 'hopeless', 'sad', 'empty', 'numb'],
    'Burnout' => ['burnout', 'burned out', 'exhausted', 'tired', 'fatigue'],
    'Loneliness' => ['lonely', 'loneliness', 'alone', 'isolated', 'isolation'],
];

function recommend_resources(PDO $pdo, string $category, string $content): array {
    $haystack = strtolower($category . ' ' . $content);
    $matched_topics = [];

    foreach (TOPIC_KEYWORDS as $topic => $keywords) {
        foreach ($keywords as $keyword) {
            if (str_contains($haystack, $keyword)) {
                $matched_topics[] = $topic;
                break;
            }
        }
    }

    $resources = [];
    $seen_ids = [];

    foreach ($matched_topics as $topic) {
        $like = '%' . $topic . '%';
        $stmt = $pdo->prepare("SELECT * FROM resources WHERE category LIKE ? OR title LIKE ? OR content LIKE ? ORDER BY created_at DESC LIMIT 3");
        $stmt->execute([$like, $like, $like]);
        foreach ($stmt->fetchAll() as $resource) {
            if (!in_array($resource['id'], $seen_ids, true)) {
                $resources[] = $resource;
                $seen_ids[] = $resource['id'];
            }
        }
        if (count($resources) >= 3) {
            break;
        }
    }

    if (empty($resources)) {
        $stmt = $pdo->query("SELECT * FROM resources ORDER BY created_at DESC LIMIT 3");
        $resources = $stmt->fetchAll();
    }

    return array_slice($resources, 0, 3);
}
