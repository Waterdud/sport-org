<?php
class NotificationService {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function create($userId, $type, $title, $message, $link = null, $eventId = null) {
        $insert = $this->pdo->prepare("
            INSERT INTO notifications (user_id, notification_type, title, message, action_link, event_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $insert->execute([$userId, $type, $title, $message, $link, $eventId]);
    }

    public function getUserNotifications($userId, $limit = 20) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    public function getUnreadCount($userId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as cnt FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$userId]);
        return $stmt->fetch()['cnt'];
    }

    public function markAsRead($notificationId) {
        $update = $this->pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
        return $update->execute([$notificationId]);
    }
}
?>
