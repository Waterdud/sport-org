<?php
/**
 * AJAX - Добавить комментарий
 */

require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false]);
    exit;
}

$eventId = (int)($_POST['event_id'] ?? 0);
$comment = trim($_POST['comment'] ?? '');
$userId = getCurrentUserId();

if (empty($comment) || $eventId === 0) {
    echo json_encode(['success' => false]);
    exit;
}

execute($pdo,
    "INSERT INTO comments (event_id, user_id, comment) VALUES (?, ?, ?)",
    [$eventId, $userId, $comment]);

echo json_encode(['success' => true]);
?>
