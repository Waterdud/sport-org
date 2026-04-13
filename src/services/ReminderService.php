<?php
/**
 * ReminderService - Система напоминаний о событиях
 * 
 * Функционал:
 * - Создание напоминаний
 * - Проверка и отправка напоминаний
 * - Управление напоминаниями
 */

class ReminderService {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Создать напоминание о событии
     * 
     * @param int $eventId - ID события
     * @param int $userId - ID пользователя
     * @param int $minutesBefore - За сколько минут до события
     * @return bool
     */
    public function createReminder($eventId, $userId, $minutesBefore = 24 * 60) {
        $stmt = $this->pdo->prepare("
            INSERT OR IGNORE INTO reminders (event_id, user_id, minutes_before, created_at)
            VALUES (?, ?, ?, CURRENT_TIMESTAMP)
        ");
        return $stmt->execute([$eventId, $userId, $minutesBefore]);
    }

    /**
     * Получить все напоминания пользователя
     */
    public function getUserReminders($userId) {
        $stmt = $this->pdo->prepare("
            SELECT r.*, e.title, e.event_date, e.event_time
            FROM reminders r
            JOIN events e ON r.event_id = e.id
            WHERE r.user_id = ?
            ORDER BY e.event_date, e.event_time
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Получить события, для которых нужно отправить напоминание
     */
    public function getPendingReminders() {
        $stmt = $this->pdo->prepare("
            SELECT r.*, e.title, e.event_date, e.event_time, u.username
            FROM reminders r
            JOIN events e ON r.event_id = e.id
            JOIN users u ON r.user_id = u.id
            WHERE r.sent_at IS NULL
            AND datetime(e.event_date || ' ' || e.event_time, '-' || r.minutes_before || ' minutes') 
                BETWEEN datetime('now', '-5 minutes') AND datetime('now')
            ORDER BY e.event_date, e.event_time
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Создать уведомление о предстоящем событии
     */
    public function sendReminder($reminderId, $userId, $eventId, $eventTitle) {
        global $pdo;
        
        // Создать уведомление
        $notificationService = new NotificationService($pdo);
        $notificationService->create(
            $userId,
            'event_reminder',
            'Предстоящее событие: ' . $eventTitle,
            'Ваше событие «' . $eventTitle . '» начнется через 24 часа.',
            '/src/pages/events/view.php?id=' . $eventId,
            $eventId
        );

        // Отметить напоминание как отправленное
        $stmt = $pdo->prepare("
            UPDATE reminders SET sent_at = CURRENT_TIMESTAMP WHERE id = ?
        ");
        $stmt->execute([$reminderId]);
    }

    /**
     * Удалить напоминание
     */
    public function deleteReminder($reminderId, $userId) {
        $stmt = $this->pdo->prepare("
            DELETE FROM reminders WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$reminderId, $userId]);
    }

    /**
     * Проверить, снят ли уже reminder для пользователя и события
     */
    public function hasReminder($eventId, $userId) {
        $stmt = $this->pdo->prepare("
            SELECT id FROM reminders 
            WHERE event_id = ? AND user_id = ? LIMIT 1
        ");
        $stmt->execute([$eventId, $userId]);
        return $stmt->fetch() ? true : false;
    }

    /**
     * Получить все события пользователя, которые начнутся в ближайшие 7 дней
     */
    public function getUpcomingEventsForUser($userId, $days = 7) {
        $stmt = $this->pdo->prepare("
            SELECT e.*, l.name as location_name, l.city
            FROM events e
            LEFT JOIN locations l ON e.location_id = l.id
            JOIN game_participants gp ON e.id = gp.event_id
            WHERE gp.user_id = ?
            AND e.event_date >= DATE('now')
            AND e.event_date <= DATE('now', '+' || ? || ' days')
            AND e.status IN ('planned', 'ongoing')
            ORDER BY e.event_date, e.event_time
        ");
        $stmt->execute([$userId, $days]);
        return $stmt->fetchAll();
    }
}
?>
