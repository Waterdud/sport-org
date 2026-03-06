<?php
/**
 * Страница уведомлений пользователя
 * 
 * Функционал:
 * - Просмотр всех уведомлений
 * - Фильтрация по типу и статусу
 * - Отметка прочитанными
 * - Удаление уведомлений
 */

require_once 'includes/db.php';
require_once 'includes/functions.php';

requireAuth();

$pageTitle = 'Уведомления';
$userId = getUserId();

// Обработка действий
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verifyCsrfToken()) {
        
        // Отметить все как прочитанные
        if (isset($_POST['mark_all_read'])) {
            execute($pdo, "UPDATE notifications SET is_read = 1 WHERE user_id = ?", [$userId]);
            setFlashMessage('success', 'Все уведомления отмечены как прочитанные');
            redirect('notifications.php');
        }
        
        // Удалить все прочитанные
        if (isset($_POST['delete_read'])) {
            execute($pdo, "DELETE FROM notifications WHERE user_id = ? AND is_read = 1", [$userId]);
            setFlashMessage('success', 'Прочитанные уведомления удалены');
            redirect('notifications.php');
        }
        
        // Удалить конкретное уведомление
        if (isset($_POST['delete_notification'])) {
            $notificationId = (int)$_POST['notification_id'];
            execute($pdo, "DELETE FROM notifications WHERE id = ? AND user_id = ?", [$notificationId, $userId]);
            setFlashMessage('success', 'Уведомление удалено');
            redirect('notifications.php');
        }
    }
}

// Фильтры
$typeFilter = $_GET['type'] ?? 'all';
$statusFilter = $_GET['status'] ?? 'all';

// Построение SQL запроса
$sql = "
    SELECT n.*, e.title as event_title
    FROM notifications n
    LEFT JOIN events e ON n.event_id = e.id
    WHERE n.user_id = ?
";
$params = [$userId];

if ($typeFilter !== 'all') {
    $sql .= " AND n.type = ?";
    $params[] = $typeFilter;
}

if ($statusFilter === 'unread') {
    $sql .= " AND n.is_read = 0";
} elseif ($statusFilter === 'read') {
    $sql .= " AND n.is_read = 1";
}

$sql .= " ORDER BY n.created_at DESC LIMIT 100";

$notifications = fetchAll($pdo, $sql, $params);

// Статистика
$stats = fetchOne($pdo, "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread,
        SUM(CASE WHEN is_read = 1 THEN 1 ELSE 0 END) as read
    FROM notifications 
    WHERE user_id = ?
", [$userId]);

require_once 'includes/header.php';
?>

<div class="row">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>
                <i class="bi bi-bell text-primary me-2"></i>
                Уведомления
            </h2>
            
            <!-- Действия -->
            <div class="btn-group">
                <form method="POST" class="d-inline" onsubmit="return confirm('Отметить все как прочитанные?')">
                    <?php echo csrfField(); ?>
                    <button type="submit" name="mark_all_read" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-check-all"></i>
                        Прочитать все
                    </button>
                </form>
                
                <?php if ($stats['read'] > 0): ?>
                    <form method="POST" class="d-inline" onsubmit="return confirm('Удалить все прочитанные уведомления?')">
                        <?php echo csrfField(); ?>
                        <button type="submit" name="delete_read" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash"></i>
                            Очистить
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Статистика -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="mb-0"><?php echo $stats['total']; ?></h3>
                        <small class="text-muted">Всего</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="mb-0 text-primary"><?php echo $stats['unread']; ?></h3>
                        <small class="text-muted">Непрочитанных</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="mb-0 text-success"><?php echo $stats['read']; ?></h3>
                        <small class="text-muted">Прочитанных</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Фильтры -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Тип уведомления</label>
                        <select name="type" class="form-select" onchange="this.form.submit()">
                            <option value="all" <?php echo $typeFilter === 'all' ? 'selected' : ''; ?>>Все типы</option>
                            <option value="participation" <?php echo $typeFilter === 'participation' ? 'selected' : ''; ?>>Участие</option>
                            <option value="cancellation" <?php echo $typeFilter === 'cancellation' ? 'selected' : ''; ?>>Отмена</option>
                            <option value="comment" <?php echo $typeFilter === 'comment' ? 'selected' : ''; ?>>Комментарии</option>
                            <option value="rating" <?php echo $typeFilter === 'rating' ? 'selected' : ''; ?>>Оценки</option>
                            <option value="event_update" <?php echo $typeFilter === 'event_update' ? 'selected' : ''; ?>>Обновления</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Статус</label>
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>Все</option>
                            <option value="unread" <?php echo $statusFilter === 'unread' ? 'selected' : ''; ?>>Непрочитанные</option>
                            <option value="read" <?php echo $statusFilter === 'read' ? 'selected' : ''; ?>>Прочитанные</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Список уведомлений -->
        <?php if (empty($notifications)): ?>
            <div class="text-center py-5">
                <i class="bi bi-bell-slash" style="font-size: 4rem; opacity: 0.3;"></i>
                <p class="text-muted mt-3">Нет уведомлений</p>
            </div>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($notifications as $notification): ?>
                    <div class="list-group-item <?php echo $notification['is_read'] ? '' : 'list-group-item-primary'; ?>">
                        <div class="d-flex justify-content-between">
                            <div class="flex-grow-1">
                                <!-- Иконка по типу -->
                                <?php
                                $icon = 'bell';
                                $iconClass = 'text-primary';
                                
                                switch ($notification['type']) {
                                    case 'participation':
                                        $icon = 'person-plus';
                                        $iconClass = 'text-success';
                                        break;
                                    case 'cancellation':
                                        $icon = 'person-dash';
                                        $iconClass = 'text-warning';
                                        break;
                                    case 'comment':
                                        $icon = 'chat-dots';
                                        $iconClass = 'text-info';
                                        break;
                                    case 'rating':
                                        $icon = 'star-fill';
                                        $iconClass = 'text-warning';
                                        break;
                                    case 'event_update':
                                        $icon = 'info-circle';
                                        $iconClass = 'text-secondary';
                                        break;
                                }
                                ?>
                                
                                <i class="bi bi-<?php echo $icon; ?> <?php echo $iconClass; ?> me-2"></i>
                                
                                <!-- Сообщение -->
                                <span><?php echo clean($notification['message']); ?></span>
                                
                                <!-- Время -->
                                <div class="small text-muted mt-1">
                                    <i class="bi bi-clock me-1"></i>
                                    <?php echo timeAgo($notification['created_at']); ?>
                                    
                                    <!-- Ссылка на событие -->
                                    <?php if ($notification['event_id']): ?>
                                        <a href="event.php?id=<?php echo $notification['event_id']; ?>" class="ms-2">
                                            Перейти к событию
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Действия -->
                            <div class="d-flex gap-2">
                                <?php if (!$notification['is_read']): ?>
                                    <form method="POST" class="d-inline">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary mark-read-btn"
                                                onclick="markNotificationRead(<?php echo $notification['id']; ?>, this)"
                                                title="Отметить прочитанным">
                                            <i class="bi bi-check"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <form method="POST" class="d-inline" onsubmit="return confirm('Удалить уведомление?')">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                    <button type="submit" 
                                            name="delete_notification" 
                                            class="btn btn-sm btn-outline-danger"
                                            title="Удалить">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Боковая панель -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-info-circle me-2"></i>
                    О уведомлениях
                </h5>
                <p class="card-text small">
                    Здесь вы получаете уведомления о:
                </p>
                <ul class="small">
                    <li>Новых участниках ваших событий</li>
                    <li>Отменах участия</li>
                    <li>Комментариях к вашим событиям</li>
                    <li>Полученных оценках</li>
                    <li>Изменениях в событиях, где вы участвуете</li>
                </ul>
                
                <hr>
                
                <h6>Полезные ссылки</h6>
                <div class="d-grid gap-2">
                    <a href="my_events.php" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-calendar-event me-1"></i>
                        Мои события
                    </a>
                    <a href="profile.php" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-person me-1"></i>
                        Мой профиль
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Отметить уведомление как прочитанное через AJAX
function markNotificationRead(notificationId, button) {
    fetch('ajax/mark_notification_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `notification_id=${notificationId}&csrf_token=${encodeURIComponent('<?php echo $_SESSION['csrf_token'] ?? ''; ?>')}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Обновляем UI
            const listItem = button.closest('.list-group-item');
            listItem.classList.remove('list-group-item-primary');
            button.remove();
            
            // Обновляем счётчик в шапке
            updateUnreadCount();
            
            showNotification('Уведомление отмечено как прочитанное', 'success');
        } else {
            showNotification(data.error || 'Ошибка', 'error');
        }
    })
    .catch(error => {
        showNotification('Ошибка сети', 'error');
    });
}

// Обновить счётчик непрочитанных
function updateUnreadCount() {
    fetch('ajax/get_unread_count.php')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const badge = document.querySelector('.notification-badge');
            if (badge) {
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = 'inline';
                } else {
                    badge.style.display = 'none';
                }
            }
        }
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
