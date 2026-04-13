<?php
class ParticipationService {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function joinGame($userId, $eventId, $rsvpStatus = 'going') {
        if (!in_array($rsvpStatus, ['going', 'maybe', 'not_going'])) {
            throw new Exception('Invalid RSVP status');
        }

        $gameStatusService = new GameStatusService($this->pdo);
        $canJoin = $gameStatusService->canJoinGame($eventId, $rsvpStatus);
        if (!$canJoin['ok']) throw new Exception($canJoin['msg']);

        // Check if already joined
        $check = $this->pdo->prepare("SELECT id FROM game_participants WHERE event_id = ? AND user_id = ?");
        $check->execute([$eventId, $userId]);
        
        if ($check->fetch()) {
            return $this->updateRsvp($eventId, $userId, $rsvpStatus);
        }

        $insert = $this->pdo->prepare("INSERT INTO game_participants (event_id, user_id, rsvp_status) VALUES (?, ?, ?)");
        $insert->execute([$eventId, $userId, $rsvpStatus]);

        $gameStatusService->checkAndUpdateCapacity($eventId);
        return true;
    }

    public function updateRsvp($eventId, $userId, $newStatus) {
        if (!in_array($newStatus, ['going', 'maybe', 'not_going', 'cancelled'])) {
            throw new Exception('Invalid status');
        }

        $gameStatusService = new GameStatusService($this->pdo);
        if ($newStatus === 'going') {
            $canJoin = $gameStatusService->canJoinGame($eventId, 'going');
            if (!$canJoin['ok']) throw new Exception($canJoin['msg']);
        }

        $update = $this->pdo->prepare("UPDATE game_participants SET rsvp_status = ?, updated_at = CURRENT_TIMESTAMP WHERE event_id = ? AND user_id = ?");
        $update->execute([$newStatus, $eventId, $userId]);

        $gameStatusService->checkAndUpdateCapacity($eventId);
        return true;
    }

    public function leaveGame($eventId, $userId) {
        $event = $this->pdo->prepare("SELECT creator_id FROM events WHERE id = ?");
        $event->execute([$eventId]);
        $e = $event->fetch();

        if ($e['creator_id'] == $userId) {
            throw new Exception('Creator cannot leave own event');
        }

        $delete = $this->pdo->prepare("DELETE FROM game_participants WHERE event_id = ? AND user_id = ?");
        $delete->execute([$eventId, $userId]);

        $gameStatusService = new GameStatusService($this->pdo);
        $gameStatusService->checkAndUpdateCapacity($eventId);
        return true;
    }

    public function getGameParticipants($eventId) {
        $stmt = $this->pdo->prepare("
            SELECT gp.*, u.username, u.avatar_url, u.reliability_rating
            FROM game_participants gp
            JOIN users u ON gp.user_id = u.id
            WHERE gp.event_id = ?
            ORDER BY CASE WHEN gp.rsvp_status = 'going' THEN 0 ELSE 1 END, gp.joined_at
        ");
        $stmt->execute([$eventId]);
        return $stmt->fetchAll();
    }

    public function getUserRsvp($eventId, $userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM game_participants WHERE event_id = ? AND user_id = ?");
        $stmt->execute([$eventId, $userId]);
        return $stmt->fetch();
    }
}
?>
