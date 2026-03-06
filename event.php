<?php
/**
 * Страница отдельного события
 * 
 * Функционал:
 * - Просмотр полной информации о событии
 * - Запись/отмена записи на событие
 * - Список участников
 * - Комментарии
 * - Управление для организатора
 */

require_once 'includes/db.php';
require_once 'includes/functions.php';

// Получение ID события
$eventId = $_GET['id'] ?? 0;

if (!$eventId) {
    redirect('index.php');
}

// Получение данных события
$event = fetchOne($pdo, "SELECT e.*, 
                                 u.username as creator_name, 
                                 u.rating as creator_rating,
                                 u.id as creator_id,
                                 l.name as location_name,
                                 l.address as location_address,
                                 l.city as location_city,
                                 l.description as location_description
                          FROM events e
                          LEFT JOIN users u ON e.creator_id = u.id
                          LEFT JOIN locations l ON e.location_id = l.id
                          WHERE e.id = ?", [$eventId]);

if (!$event) {
    setFlashMessage('error', 'Событие не найдено');
    redirect('index.php');
}

$pageTitle = clean($event['title']);

// Проверка, является ли текущий пользователь организатором
$isCreator = isLoggedIn() && getCurrentUserId() == $event['creator_id'];

// Проверка, записан ли текущий пользователь
$isParticipant = false;
$myParticipation = null;
if (isLoggedIn()) {
    $myParticipation = fetchOne($pdo, 
        "SELECT * FROM participants WHERE event_id = ? AND user_id = ?",
        [$eventId, getCurrentUserId()]
    );
    $isParticipant = !empty($myParticipation) && in_array($myParticipation['status'], ['Записан', 'Подтвержден', 'Пришёл']);
}

// Получение списка участников
$participants = fetchAll($pdo, 
    "SELECT p.*, u.username, u.rating, u.avatar, u.attended_events, u.total_events
     FROM participants p
     JOIN users u ON p.user_id = u.id
     WHERE p.event_id = ? AND p.status IN ('Записан', 'Подтвержден', 'Пришёл')
     ORDER BY p.joined_at ASC", 
    [$eventId]
);

// Получение комментариев
$comments = fetchAll($pdo,
    "SELECT c.*, u.username, u.avatar, u.rating
     FROM comments c
     JOIN users u ON c.user_id = u.id
     WHERE c.event_id = ?
     ORDER BY c.created_at DESC",
    [$eventId]
);

// Обработка действий
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    
    if (!verifyCsrfToken()) {
        setFlashMessage('error', 'Ошибка безопасности');
        redirect('event.php?id=' . $eventId);
    }
    
    $action = $_POST['action'] ?? '';
    
    // Запись на событие
    if ($action === 'join' && !$isParticipant && !$isCreator) {
        if ($event['current_participants'] >= $event['max_participants']) {
            setFlashMessage('error', 'Kõik kohad on täidetud');
        } else {
            $sql = "INSERT INTO participants (event_id, user_id, status) VALUES (?, ?, 'Записан')";
            if (execute($pdo, $sql, [$eventId, getCurrentUserId()])) {
                // Создаём уведомление для организатора
                $notifSql = "INSERT INTO notifications (user_id, event_id, type, message) 
                             VALUES (?, ?, 'Zapiss', ?)";
                $currentUser = getCurrentUser();
                $message = $currentUser['username'] . ' registreerus sinu üritusele "' . $event['title'] . '"';
                execute($pdo, $notifSql, [$event['creator_id'], $eventId, $message]);
                
                setFlashMessage('success', 'Oled edukalt üritusele registreeritud!');
            } else {
                setFlashMessage('error', 'Viga registreerimisel');
            }
        }
        redirect('event.php?id=' . $eventId);
    }
    
    // Отмена записи
    if ($action === 'leave' && $isParticipant && !$isCreator) {
        $sql = "UPDATE participants SET status = 'Отменил' WHERE event_id = ? AND user_id = ?";
        if (execute($pdo, $sql, [$eventId, getCurrentUserId()])) {
            // Уведомление организатору
            $notifSql = "INSERT INTO notifications (user_id, event_id, type, message) 
                         VALUES (?, ?, 'Tühistamine', ?)";
            $currentUser = getCurrentUser();
            $message = $currentUser['username'] . ' tühistas registreeringu üritusele "' . $event['title'] . '"';
            execute($pdo, $notifSql, [$event['creator_id'], $eventId, $message]);
            
            setFlashMessage('info', 'Registreering tühistatud');
        }
        redirect('event.php?id=' . $eventId);
    }
    
    // Добавление комментария
    if ($action === 'comment') {
        $commentText = trim($_POST['comment'] ?? '');
        if (!empty($commentText)) {
            $sql = "INSERT INTO comments (event_id, user_id, comment) VALUES (?, ?, ?)";
            if (execute($pdo, $sql, [$eventId, getCurrentUserId(), $commentText])) {
                setFlashMessage('success', 'Kommentaar lisatud');
            }
        }
        redirect('event.php?id=' . $eventId);
    }
    
    // Отмена события (только для организатора)
    if ($action === 'cancel' && $isCreator) {
        $sql = "UPDATE events SET status = 'Отменено' WHERE id = ?";
        if (execute($pdo, $sql, [$eventId])) {
            // Уведомления всем участникам
            foreach ($participants as $participant) {
                if ($participant['user_id'] != $event['creator_id']) {
                    $notifSql = "INSERT INTO notifications (user_id, event_id, type, message) 
                                 VALUES (?, ?, 'Muutmine', ?)";
                    $message = 'Üritus "' . $event['title'] . '" on korraldaja poolt tühistatud';
                    execute($pdo, $notifSql, [$participant['user_id'], $eventId, $message]);
                }
            }
            setFlashMessage('info', 'Üritus tühistatud');
        }
        redirect('event.php?id=' . $eventId);
    }
}

require_once 'includes/header.php';

// Определяем класс для вида спорта
$sportClass = [
    'Футбол' => 'sport-football',
    'Волейбол' => 'sport-volleyball',
    'Баскетбол' => 'sport-basketball'
][$event['sport_type']] ?? 'bg-secondary';

$skillClass = [
    'Algaja' => 'badge-beginner',
    'Harrastaja' => 'badge-amateur',
    'Edasijõudnu' => 'badge-advanced',
    'Professionaal' => 'badge-professional'
][$event['skill_level']] ?? 'bg-secondary';

$statusClass = [
    'Avatud' => 'badge-open',
    'Suletud' => 'badge-closed',
    'Lõpetatud' => 'badge-completed',
    'Tühistatud' => 'badge-cancelled'
][translateEventStatus($event['status'])] ?? 'bg-secondary';
?>

<div class="row">
    <!-- Основная информация -->
    <div class="col-lg-8">
        <!-- Карточка события -->
        <div class="card shadow-sm mb-4">
            <div class="card-body p-4">
                <!-- Заголовок с бейджами -->
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h2 class="card-title mb-0"><?php echo clean($event['title']); ?></h2>
                    <div>
                        <span class="badge <?php echo $sportClass; ?> me-2">
                            <?php echo clean($event['sport_type']); ?>
                        </span>
                        <span class="badge <?php echo $statusClass; ?>">
                            <?php echo clean(translateEventStatus($event['status'])); ?>
                        </span>
                    </div>
                </div>
                
                <!-- Информация о событии -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-calendar-event text-primary fs-4 me-3"></i>
                            <div>
                                <small class="text-muted d-block">Kuupäev ja kellaaeg</small>
                                <strong><?php echo formatDateTime($event['event_date'], $event['event_time']); ?></strong>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-hourglass-split text-primary fs-4 me-3"></i>
                            <div>
                                <small class="text-muted d-block">Kestus</small>
                                <strong><?php echo $event['duration']; ?> minutit</strong>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-geo-alt text-primary fs-4 me-3"></i>
                            <div>
                                <small class="text-muted d-block">Koht</small>
                                <strong><?php echo clean($event['location_name']); ?></strong><br>
                                <small class="text-muted">
                                    <?php echo clean($event['location_address']); ?>, 
                                    <?php echo clean($event['location_city']); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-bar-chart text-primary fs-4 me-3"></i>
                            <div>
                                <small class="text-muted d-block">Mängutase</small>
                                <span class="badge <?php echo $skillClass; ?>">
                                    <?php echo clean($event['skill_level']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Описание -->
                <?php if (!empty($event['description'])): ?>
                    <div class="mb-4">
                        <h5 class="mb-3">
                            <i class="bi bi-card-text me-2"></i>
                            Kirjeldus
                        </h5>
                        <p class="text-muted"><?php echo nl2br(clean($event['description'])); ?></p>
                    </div>
                <?php endif; ?>
                
                <!-- Прогресс участников -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">
                            <i class="bi bi-people me-2"></i>
                            Osalejad
                        </h5>
                        <span class="badge bg-primary">
                            <?php echo $event['current_participants']; ?> / <?php echo $event['max_participants']; ?>
                        </span>
                    </div>
                    <div class="progress" style="height: 25px;">
                        <?php $fillPercentage = ($event['current_participants'] / $event['max_participants']) * 100; ?>
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" 
                             style="width: <?php echo $fillPercentage; ?>%"
                             aria-valuenow="<?php echo $event['current_participants']; ?>"
                             aria-valuemin="0"
                             aria-valuemax="<?php echo $event['max_participants']; ?>">
                            <?php echo round($fillPercentage); ?>%
                        </div>
                    </div>
                </div>
                
                <!-- Кнопки действий -->
                <?php if ($event['status'] === 'active'): ?>
                    <div class="d-grid gap-2">
                        <?php if (!isLoggedIn()): ?>
                            <a href="login.php" class="btn btn-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                Logi sisse registreerimiseks
                            </a>
                        <?php elseif ($isCreator): ?>
                            <form method="POST" action="event.php?id=<?php echo $eventId; ?>" 
                                  onsubmit="return confirm('Üritus tühistatakse. Oled kindel?');">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="action" value="cancel">
                                <button type="submit" class="btn btn-danger btn-lg w-100">
                                    <i class="bi bi-x-circle me-2"></i>
                                    Tühista üritus
                                </button>
                            </form>
                        <?php elseif ($isParticipant): ?>
                            <button class="btn btn-success btn-lg disabled">
                                <i class="bi bi-check-circle me-2"></i>
                                Oled registreeritud
                            </button>
                            <form method="POST" action="event.php?id=<?php echo $eventId; ?>"
                                  onsubmit="return confirm('Tühistad registreerimise. Oled kindel?');">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="action" value="leave">
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="bi bi-x-circle me-2"></i>
                                    Tühista registreerimine
                                </button>
                            </form>
                        <?php elseif ($event['current_participants'] >= $event['max_participants']): ?>
                            <button class="btn btn-secondary btn-lg" disabled>
                                <i class="bi bi-x-circle me-2"></i>
                                Kõik kohad on täidetud
                            </button>
                        <?php else: ?>
                            <form method="POST" action="event.php?id=<?php echo $eventId; ?>">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="action" value="join">
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Registreeru üritusele
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        See üritus on <?php echo mb_strtolower(translateEventStatus($event['status'])); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Список участников -->
        <div class="card shadow-sm mb-4">
            <div class="card-body p-4">
                <h5 class="card-title mb-4">
                    <i class="bi bi-people-fill me-2"></i>
                    Osalejad (<?php echo count($participants); ?>)
                </h5>
                
                <?php if (empty($participants)): ?>
                    <p class="text-muted text-center py-3">Keegi pole veel registreerunud</p>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($participants as $participant): ?>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center p-2 bg-light rounded">
                                    <img src="uploads/avatars/<?php echo clean($participant['avatar']); ?>" 
                                         alt="Аватар" 
                                         class="avatar me-3">
                                    <div class="flex-grow-1">
                                        <div class="fw-bold">
                                            <?php echo clean($participant['username']); ?>
                                            <?php if ($participant['user_id'] == $event['creator_id']): ?>
                                                <span class="badge bg-warning text-dark ms-1">Korraldaja</span>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted">
                                            <i class="bi bi-star-fill text-warning"></i>
                                            <?php echo number_format($participant['rating'], 1); ?>
                                            •
                                            <?php 
                                            $attendance = $participant['total_events'] > 0 
                                                ? round(($participant['attended_events'] / $participant['total_events']) * 100) 
                                                : 0;
                                            ?>
                                            <?php echo $attendance; ?>% osalemine
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Комментарии -->
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h5 class="card-title mb-4">
                    <i class="bi bi-chat-dots me-2"></i>
                    Kommentaarid (<?php echo count($comments); ?>)
                </h5>
                
                <!-- Форма добавления комментария -->
                <?php if (isLoggedIn()): ?>
                    <form method="POST" action="event.php?id=<?php echo $eventId; ?>" class="mb-4">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="comment">
                        <div class="mb-3">
                            <textarea class="form-control" 
                                      name="comment" 
                                      rows="3" 
                                      placeholder="Kirjuta kommentaar..." 
                                      required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-2"></i>Saada
                        </button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-info">
                        <a href="login.php">Logi sisse</a>, et kommenteerida
                    </div>
                <?php endif; ?>
                
                <!-- Список комментариев -->
                <?php if (empty($comments)): ?>
                    <p class="text-muted text-center py-3">Kommentaare pole veel</p>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment-item">
                            <div class="d-flex align-items-start">
                                <img src="uploads/avatars/<?php echo clean($comment['avatar']); ?>" 
                                     alt="Аватар" 
                                     class="avatar me-3">
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div>
                                            <span class="comment-author"><?php echo clean($comment['username']); ?></span>
                                            <span class="badge bg-warning text-dark ms-2">
                                                ⭐ <?php echo number_format($comment['rating'], 1); ?>
                                            </span>
                                        </div>
                                        <span class="comment-time"><?php echo timeAgo($comment['created_at']); ?></span>
                                    </div>
                                    <div class="comment-text"><?php echo nl2br(clean($comment['comment'])); ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Боковая панель -->
    <div class="col-lg-4">
        <!-- Организатор -->
        <div class="card shadow-sm mb-4">
            <div class="card-body text-center p-4">
                <h5 class="card-title mb-3">Korraldaja</h5>
                <img src="uploads/avatars/<?php echo clean($event['avatar'] ?? 'default-avatar.png'); ?>" 
                     alt="Аватар" 
                     class="avatar-xl mb-3">
                <h5><?php echo clean($event['creator_name']); ?></h5>
                <div class="mb-3">
                    <span class="badge bg-warning text-dark fs-6">
                        <i class="bi bi-star-fill"></i>
                        <?php echo number_format($event['creator_rating'], 1); ?>
                    </span>
                </div>
                <a href="profile.php?id=<?php echo $event['creator_id']; ?>" class="btn btn-outline-primary">
                    <i class="bi bi-person me-2"></i>Profiil
                </a>
            </div>
        </div>
        
        <!-- Информация о месте -->
        <?php if ($event['location_description']): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-info-circle me-2"></i>
                        Kohast
                    </h5>
                    <p class="small text-muted mb-0">
                        <?php echo nl2br(clean($event['location_description'])); ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Поделиться -->
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h5 class="card-title mb-3">
                    <i class="bi bi-share me-2"></i>
                    Jaga
                </h5>
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary btn-sm" 
                            onclick="copyToClipboard('<?php echo 'http://' . $_SERVER['HTTP_HOST'] . '/event.php?id=' . $eventId; ?>')">
                        <i class="bi bi-link-45deg me-2"></i>Kopeeri link
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
