<?php
require_once dirname(__DIR__, 2) . '/src/config/bootstrap.php';
require_once BASE_PATH . '/src/services/NotificationService.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authorized']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$userId = getCurrentUserId();

try {
    $service = new NotificationService($pdo);

    switch ($action) {
        case 'count':
            $count = $service->getUnreadCount($userId);
            echo json_encode(['ok' => true, 'count' => $count]);
            break;

        case 'list':
            $notifications = $service->getUserNotifications($userId, 20);
            echo json_encode(['ok' => true, 'notifications' => $notifications]);
            break;

        case 'mark_read':
            $notifId = (int)($_POST['notification_id'] ?? 0);
            $service->markAsRead($notifId);
            echo json_encode(['ok' => true]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
