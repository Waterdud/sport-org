<?php
require_once dirname(__DIR__) . '/config/bootstrap.php';
require_once BASE_PATH . '/src/services/RatingService.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authorized']);
    exit;
}

$action = $_POST['action'] ?? '';
$userId = getCurrentUserId();

try {
    $service = new RatingService($pdo);

    switch ($action) {
        case 'submit':
            $ratedUserId = (int)($_POST['user_id'] ?? $_POST['rated_user_id'] ?? 0);
            $eventId = (int)($_POST['event_id'] ?? 0);
            $att = (int)($_POST['attendance'] ?? 0);
            $coop = (int)($_POST['cooperation'] ?? 0);
            $sport = (int)($_POST['sportsmanship'] ?? 0);
            $comment = $_POST['comment'] ?? '';

            if (!$ratedUserId || !$eventId) {
                throw new Exception('Missing required fields');
            }

            $service->submitRating($ratedUserId, $userId, $eventId, $att, $coop, $sport, $comment);
            echo json_encode(['ok' => true, 'msg' => 'Rating submitted']);
            break;

        case 'get_breakdown':
            $ratedUserId = (int)($_GET['user_id'] ?? 0);
            $breakdown = $service->getUserRatingBreakdown($ratedUserId);
            echo json_encode(['ok' => true, 'breakdown' => $breakdown]);
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
