<?php
/**
 * AJAX - Присоединиться к событию
 */

require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Kõigepealt logi sisse']);
    exit;
}

$eventId = (int)($_POST['event_id'] ?? 0);
$userId = getCurrentUserId();

if ($eventId === 0) {
    echo json_encode(['success' => false, 'message' => 'Vale üritus']);
    exit;
}

try {
    execute($pdo,
        "INSERT INTO participants (event_id, user_id, status) VALUES (?, ?, 'Записан')",
        [$eventId, $userId]);
    
    // Update counter
    execute($pdo, "UPDATE events SET current_participants = current_participants + 1 WHERE id = ?", [$eventId]);
    
    echo json_encode(['success' => true, 'message' => 'Liitusid üritusega']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Viga']);
}
?>
