<?php
/**
 * AJAX обработчик: Отметить уведомление как прочитанное
 * 
 * Возвращает JSON с результатом операции
 */

require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Проверка авторизации
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Необходимо авторизоваться']);
    exit;
}

// Проверка метода
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Неверный метод запроса']);
    exit;
}

// Проверка CSRF
if (!verifyCsrfToken()) {
    echo json_encode(['success' => false, 'error' => 'Ошибка безопасности']);
    exit;
}

$notificationId = (int)($_POST['notification_id'] ?? 0);
$userId = getUserId();

if ($notificationId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Неверный ID уведомления']);
    exit;
}

try {
    // Проверяем, что уведомление принадлежит пользователю
    $notification = fetchOne($pdo, 
        "SELECT * FROM notifications WHERE id = ? AND user_id = ?",
        [$notificationId, $userId]
    );
    
    if (!$notification) {
        echo json_encode(['success' => false, 'error' => 'Уведомление не найдено']);
        exit;
    }
    
    // Отмечаем как прочитанное
    execute($pdo, 
        "UPDATE notifications SET is_read = 1 WHERE id = ?",
        [$notificationId]
    );
    
    // Получаем обновлённое количество непрочитанных
    $unreadCount = fetchOne($pdo, 
        "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0",
        [$userId]
    )['count'];
    
    echo json_encode([
        'success' => true,
        'unread_count' => (int)$unreadCount
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Ошибка сервера']);
}
