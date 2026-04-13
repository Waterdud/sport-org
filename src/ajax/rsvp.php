<?php
require_once dirname(__DIR__, 2) . '/src/config/bootstrap.php';
require_once BASE_PATH . '/src/services/ParticipationService.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authorized']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$eventId = (int)($_POST['event_id'] ?? $_GET['event_id'] ?? 0);
$userId = getCurrentUserId();

try {
    $service = new ParticipationService($pdo);

    switch ($action) {
        case 'join':
            $status = $_POST['status'] ?? 'going';
            $service->joinGame($userId, $eventId, $status);
            echo json_encode(['ok' => true, 'msg' => 'Joined']);
            break;

        case 'update':
            $status = $_POST['status'] ?? 'going';
            $service->updateRsvp($eventId, $userId, $status);
            echo json_encode(['ok' => true, 'msg' => 'Updated']);
            break;

        case 'leave':
            $service->leaveGame($eventId, $userId);
            echo json_encode(['ok' => true, 'msg' => 'Left']);
            break;

        case 'get_participants':
            $participants = $service->getGameParticipants($eventId);
            echo json_encode(['ok' => true, 'participants' => $participants]);
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
