<?php
/**
 * AJAX - Отметить уведомление как прочитанное
 */

require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false]);
    exit;
}

$notificationId = (int)($_POST['id'] ?? 0);

execute($pdo, "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?", 
    [$notificationId, getCurrentUserId()]);

echo json_encode(['success' => true]);
?>
