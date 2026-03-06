<?php
/**
 * AJAX обработчик: Получить количество непрочитанных уведомлений
 * 
 * Возвращает JSON с количеством
 */

require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Проверка авторизации
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'count' => 0]);
    exit;
}

$userId = getUserId();

try {
    $unreadCount = fetchOne($pdo, 
        "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0",
        [$userId]
    )['count'];
    
    echo json_encode([
        'success' => true,
        'count' => (int)$unreadCount
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'count' => 0]);
}
