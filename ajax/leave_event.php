<?php
/**
 * AJAX обработчик: Отказаться от участия в событии
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

$eventId = (int)($_POST['event_id'] ?? 0);
$userId = getUserId();

if ($eventId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Неверный ID события']);
    exit;
}

try {
    // Получаем информацию о событии
    $event = fetchOne($pdo, "SELECT * FROM events WHERE id = ?", [$eventId]);
    
    if (!$event) {
        echo json_encode(['success' => false, 'error' => 'Событие не найдено']);
        exit;
    }
    
    // Проверка, что событие не в прошлом
    if (strtotime($event['event_date'] . ' ' . $event['event_time']) < time()) {
        echo json_encode(['success' => false, 'error' => 'Событие уже прошло']);
        exit;
    }
    
    // Проверка, что не организатор
    if ($event['creator_id'] == $userId) {
        echo json_encode(['success' => false, 'error' => 'Организатор не может покинуть своё событие']);
        exit;
    }
    
    // Проверка, что участвует
    $participant = fetchOne($pdo, 
        "SELECT * FROM participants WHERE event_id = ? AND user_id = ?",
        [$eventId, $userId]
    );
    
    if (!$participant) {
        echo json_encode(['success' => false, 'error' => 'Вы не участвуете в этом событии']);
        exit;
    }
    
    // Удаляем участника
    execute($pdo, 
        "DELETE FROM participants WHERE event_id = ? AND user_id = ?",
        [$eventId, $userId]
    );
    
    // Получаем обновлённое количество участников
    $updatedEvent = fetchOne($pdo, "SELECT current_participants FROM events WHERE id = ?", [$eventId]);
    
    // Создаём уведомление для организатора
    $notificationText = "Пользователь {$_SESSION['user']['username']} отказался от участия в событии '{$event['title']}'";
    execute($pdo, 
        "INSERT INTO notifications (user_id, event_id, type, message) VALUES (?, ?, 'cancellation', ?)",
        [$event['creator_id'], $eventId, $notificationText]
    );
    
    echo json_encode([
        'success' => true,
        'message' => 'Вы отказались от участия в событии',
        'current_participants' => $updatedEvent['current_participants'],
        'max_participants' => $event['max_participants']
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Ошибка сервера']);
}
