<?php
class RatingService {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function submitRating($ratedUserId, $raterId, $eventId, $attendance, $cooperation, $sportsmanship, $comment = '') {
        try {
            // Validate event is finished
            $event = $this->pdo->prepare("SELECT status FROM events WHERE id = ?");
            $event->execute([$eventId]);
            if (!($e = $event->fetch()) || $e['status'] !== 'finished') {
                throw new Exception('Can only rate finished games');
            }

            // Check no duplicate
            $existing = $this->pdo->prepare("SELECT id FROM ratings WHERE event_id = ? AND rater_user_id = ? AND rated_user_id = ?");
            $existing->execute([$eventId, $raterId, $ratedUserId]);
            if ($existing->fetch()) {
                throw new Exception('Already rated this user for this game');
            }

            // Insert rating
            $stmt = $this->pdo->prepare("
                INSERT INTO ratings (rated_user_id, rater_user_id, event_id, attendance_score, cooperation_score, sportsmanship_score, comment)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$ratedUserId, $raterId, $eventId, $attendance, $cooperation, $sportsmanship, $comment]);

            // Recalculate user reliability
            $this->recalculateReliability($ratedUserId);

            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function recalculateReliability($userId) {
        $stmt = $this->pdo->prepare("
            SELECT AVG(attendance_score) * 0.5 + AVG(cooperation_score) * 0.25 + AVG(sportsmanship_score) * 0.25 as weighted_score,
                   COUNT(*) as total_ratings
            FROM ratings WHERE rated_user_id = ?
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();

        $score = $result['weighted_score'] ? round($result['weighted_score'], 2) : 5.0;

        $update = $this->pdo->prepare("UPDATE users SET reliability_rating = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $update->execute([$score, $userId]);
    }

    public function getUserRatingBreakdown($userId) {
        $stmt = $this->pdo->prepare("
            SELECT AVG(attendance_score) as attendance, AVG(cooperation_score) as cooperation, 
                   AVG(sportsmanship_score) as sportsmanship, COUNT(*) as count
            FROM ratings WHERE rated_user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
}
?>
