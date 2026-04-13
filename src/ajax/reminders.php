<?php
/**
 * AJAX: Управление напоминаниями о событиях
 */

header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__, 3) . '/src/config/bootstrap.php';

// Проверка авторизации
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Peate olema sisse logitud']);
    exit;
}

$action = $_POST['action'] ?? '';
$userId = getCurrentUserId();
$eventId = (int)($_POST['event_id'] ?? 0);

$reminderService = new ReminderService($pdo);

try {
    switch ($action) {
        case 'create':
            // Создать напоминание
            $minutesBefore = (int)($_POST['minutes_before'] ?? 1440); // 24 часа по умолчанию
            
            if ($reminderService->createReminder($eventId, $userId, $minutesBefore)) {
                echo json_encode([
                    'ok' => true,
                    'message' => 'Meeldetuletus loodud!'
                ]);
            } else {
                echo json_encode([
                    'ok' => false,
                    'error' => 'Meeldetuletus on juba olemas'
                ]);
            }
            break;

        case 'delete':
            // Удалить напоминание
            $reminderId = (int)($_POST['reminder_id'] ?? 0);
            
            if ($reminderService->deleteReminder($reminderId, $userId)) {
                echo json_encode([
                    'ok' => true,
                    'message' => 'Meeldetuletus kustutatud'
                ]);
            } else {
                echo json_encode([
                    'ok' => false,
                    'error' => 'Meeldetuletus ei leitud'
                ]);
            }
            break;

        case 'has_reminder':
            // Проверить, есть ли уже напоминание
            $hasReminder = $reminderService->hasReminder($eventId, $userId);
            echo json_encode([
                'ok' => true,
                'has_reminder' => $hasReminder
            ]);
            break;

        case 'list':
            // Получить все напоминания пользователя
            $reminders = $reminderService->getUserReminders($userId);
            echo json_encode([
                'ok' => true,
                'reminders' => $reminders
            ]);
            break;

        case 'upcoming':
            // Получить предстоящие события с напоминаниями
            $days = (int)($_POST['days'] ?? 7);
            $events = $reminderService->getUpcomingEventsForUser($userId, $days);
            echo json_encode([
                'ok' => true,
                'events' => $events,
                'count' => count($events)
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Tegevus pole määratud']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Viga: ' . $e->getMessage()]);
}
?>
