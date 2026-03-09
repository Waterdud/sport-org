<?php
/**
 * AJAX - Покинуть событие
 */

require_once dirname(__DIR__, 2) . '/src/config/bootstrap.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false]);
    exit;
}

$eventId = (int)($_POST['event_id'] ?? 0);
$userId = getCurrentUserId();

execute($pdo, "DELETE FROM participants WHERE event_id = ? AND user_id = ?", [$eventId, $userId]);
execute($pdo, "UPDATE events SET current_participants = current_participants - 1 WHERE id = ?", [$eventId]);

echo json_encode(['success' => true]);
?>
