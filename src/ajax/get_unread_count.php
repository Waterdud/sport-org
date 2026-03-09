<?php
/**
 * AJAX - Получить количество непрочитанных уведомлений
 */

require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['count' => 0]);
    exit;
}

$result = fetchOne($pdo,
    "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0",
    [getCurrentUserId()]);

echo json_encode(['count' => $result['count'] ?? 0]);
?>
