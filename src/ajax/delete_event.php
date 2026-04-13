<?php
/**
 * Delete Event - Üritus kustutamine
 * 
 * Позволяет удалить событие только создателю
 */

require_once dirname(__DIR__) . '/config/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

// Проверка авторизации
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';
$eventId = (int)($_POST['event_id'] ?? 0);
$userId = getCurrentUserId();

try {
    if ($action === 'delete') {
        if ($eventId === 0) {
            throw new Exception('Invalid event ID');
        }

        // Проверяем, что пользователь - создатель события
        $event = fetchOne($pdo, 
            "SELECT creator_id FROM events WHERE id = ?", 
            [$eventId]);

        if (!$event) {
            throw new Exception('Event not found');
        }
        
        error_log("DEBUG: event creator_id=" . $event['creator_id'] . ", current user=" . $userId . ", types: " . gettype($event['creator_id']) . " vs " . gettype($userId));

        if (intval($event['creator_id']) !== intval($userId)) {
            http_response_code(403);
            throw new Exception('Only event creator can delete it (creator=' . intval($event['creator_id']) . ', user=' . intval($userId) . ')');
        }

        // Получим информацию об участниках для логирования
        $participants = fetchAll($pdo,
            "SELECT user_id FROM game_participants WHERE event_id = ?",
            [$eventId]);

        // Удаляем событие (каскадное удаление заботится об участниках благодаря ON DELETE CASCADE)
        execute($pdo, 
            "DELETE FROM events WHERE id = ?", 
            [$eventId]);

        // Создаем уведомления для всех участников
        $notificationService = new NotificationService($pdo);
        foreach ($participants as $participant) {
            if ($participant['user_id'] !== $userId) {
                $notificationService->createNotification(
                    $participant['user_id'],
                    'event_cancelled',
                    'Üritus tühistati',
                    'Üritus, kuhu otsustasid minna, on praegu tühistatud.',
                    '/events'
                );
            }
        }

        echo json_encode([
            'ok' => true,
            'message' => 'Üritus edukalt kustutatud'
        ]);
    } else {
        throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ]);
}
