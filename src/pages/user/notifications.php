<?php
/**
 * Уведомления - Teated
 */

require_once dirname(__DIR__, 3) . '/src/config/bootstrap.php';
requireAuth();

$pageTitle = 'Teated';

$notifications = fetchAll($pdo,
    "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50",
    [getCurrentUserId()]);

require_once BASE_PATH . '/src/components/Header.php';
?>

<h2>
    <i class="bi bi-bell me-2"></i>Teated
</h2>

<div class="list-group">
    <?php foreach ($notifications as $notif): ?>
        <div class="list-group-item">
            <h6><?php echo clean($notif['type']); ?></h6>
            <p><?php echo clean($notif['message']); ?></p>
            <small class="text-muted"><?php echo formatDateEt($notif['created_at']); ?></small>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once BASE_PATH . '/src/components/Footer.php'; ?>
