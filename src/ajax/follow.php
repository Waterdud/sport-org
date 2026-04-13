<?php
require_once dirname(__DIR__, 2) . '/src/config/bootstrap.php';
require_once BASE_PATH . '/src/services/FollowService.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authorized']);
    exit;
}

$action = $_POST['action'] ?? '';
$userId = getCurrentUserId();

try {
    $service = new FollowService($pdo);

    switch ($action) {
        case 'follow':
            $followeeId = (int)($_POST['followee_id'] ?? 0);
            $service->followUser($userId, $followeeId);
            echo json_encode(['ok' => true, 'msg' => 'Following']);
            break;

        case 'unfollow':
            $followeeId = (int)($_POST['followee_id'] ?? 0);
            $service->unfollowUser($userId, $followeeId);
            echo json_encode(['ok' => true, 'msg' => 'Unfollowed']);
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
