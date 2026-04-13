<?php
class AnalyticsService {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getUserStats($userId) {
        $stats = [];

        $stmt = $this->pdo->prepare("SELECT COUNT(*) as cnt FROM game_participants WHERE user_id = ? AND rsvp_status = 'going'");
        $stmt->execute([$userId]);
        $stats['games_attended'] = $stmt->fetch()['cnt'];

        $stmt = $this->pdo->prepare("SELECT COUNT(*) as cnt FROM events WHERE creator_id = ?");
        $stmt->execute([$userId]);
        $stats['games_organized'] = $stmt->fetch()['cnt'];

        $stmt = $this->pdo->prepare("SELECT COUNT(*) as cnt FROM game_participants WHERE user_id = ? AND rsvp_status = 'going' AND event_id IN (SELECT id FROM events WHERE status = 'finished')");
        $stmt->execute([$userId]);
        $attended = $stmt->fetch()['cnt'];

        $stats['attendance_rate'] = $stats['games_attended'] > 0 ? round(($attended / $stats['games_attended']) * 100, 1) : 0;

        $stmt = $this->pdo->prepare("SELECT reliability_rating FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $stats['reliability_rating'] = $stmt->fetch()['reliability_rating'] ?? 5.0;

        return $stats;
    }

    public function getLeaderboard($limit = 10) {
        $stmt = $this->pdo->prepare("
            SELECT id, username, reliability_rating, games_attended, avatar_url
            FROM users WHERE games_attended > 0
            ORDER BY reliability_rating DESC, games_attended DESC LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
?>
