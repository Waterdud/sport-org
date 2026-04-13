<?php
class GameStatusService {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function transitionStatus($eventId, $newStatus, $creatorId) {
        $event = $this->pdo->prepare("SELECT creator_id, status FROM events WHERE id = ?");
        $event->execute([$eventId]);
        $e = $event->fetch();

        if (!$e) throw new Exception('Event not found');
        if ($e['creator_id'] != $creatorId) throw new Exception('Only creator can change status');

        $validTransitions = [
            'planned' => ['full', 'ongoing', 'cancelled'],
            'full' => ['ongoing', 'cancelled', 'planned'],
            'ongoing' => ['finished', 'cancelled'],
            'finished' => [],
            'cancelled' => []
        ];

        if (!in_array($newStatus, $validTransitions[$e['status']] ?? [])) {
            throw new Exception("Cannot transition from {$e['status']} to $newStatus");
        }

        $update = $this->pdo->prepare("UPDATE events SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $update->execute([$newStatus, $eventId]);

        return true;
    }

    public function checkAndUpdateCapacity($eventId) {
        $count = $this->pdo->prepare("SELECT COUNT(*) as cnt FROM game_participants WHERE event_id = ? AND rsvp_status = 'going'");
        $count->execute([$eventId]);
        $going = $count->fetch()['cnt'];

        $event = $this->pdo->prepare("SELECT max_participants, status FROM events WHERE id = ?");
        $event->execute([$eventId]);
        $e = $event->fetch();

        $update = $this->pdo->prepare("UPDATE events SET current_participants = ? WHERE id = ?");
        $update->execute([$going, $eventId]);

        if ($going >= $e['max_participants'] && $e['status'] === 'planned') {
            $this->pdo->prepare("UPDATE events SET status = 'full' WHERE id = ?")->execute([$eventId]);
        } elseif ($going < $e['max_participants'] && $e['status'] === 'full') {
            $this->pdo->prepare("UPDATE events SET status = 'planned' WHERE id = ?")->execute([$eventId]);
        }
    }

    public function canJoinGame($eventId, $rsvpStatus = 'going') {
        $event = $this->pdo->prepare("SELECT status, current_participants, max_participants FROM events WHERE id = ?");
        $event->execute([$eventId]);
        $e = $event->fetch();

        if (!$e) return ['ok' => false, 'msg' => 'Event not found'];
        if ($e['status'] === 'cancelled') return ['ok' => false, 'msg' => 'Event cancelled'];
        if ($e['status'] === 'finished') return ['ok' => false, 'msg' => 'Event finished'];
        if ($rsvpStatus === 'going' && $e['current_participants'] >= $e['max_participants']) {
            return ['ok' => false, 'msg' => 'Event full'];
        }

        return ['ok' => true];
    }

    public function getDisplayStatus($event) {
        // If cancelled, always cancelled
        if ($event['status'] === 'cancelled') return 'cancelled';
        
        // Check if event date/time has passed
        $eventDateTime = $event['event_date'] . ' ' . $event['event_time'];
        $now = date('Y-m-d H:i');
        
        if ($eventDateTime <= $now) {
            return 'finished';
        }
        
        return $event['status'];
    }
}
?>
