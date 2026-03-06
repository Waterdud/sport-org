<?php
/**
 * Профиль пользователя
 * 
 * Функционал:
 * - Просмотр информации о пользователе
 * - Статистика (рейтинг, события, посещаемость)
 * - История участия в событиях
 * - Редактирование своего профиля
 */

require_once 'includes/db.php';
require_once 'includes/functions.php';

// Получение ID пользователя (свой или чужой профиль)
$userId = $_GET['id'] ?? getCurrentUserId();

if (!$userId) {
    redirect('login.php');
}

// Получение данных пользователя
$user = fetchOne($pdo, "SELECT * FROM users WHERE id = ?", [$userId]);

if (!$user) {
    setFlashMessage('error', 'Пользователь не найден');
    redirect('index.php');
}

$pageTitle = 'Profiil - ' . clean($user['username']);
$isOwnProfile = isLoggedIn() && getCurrentUserId() == $userId;

// Обработка редактирования профиля
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isOwnProfile) {
    
    if (!verifyCsrfToken()) {
        setFlashMessage('error', 'Ошибка безопасности');
        redirect('profile.php');
    }
    
    $phone = trim($_POST['phone'] ?? '');
    $errors = [];
    
    // Валидация телефона
    if (!empty($phone) && !isValidPhone($phone)) {
        $errors[] = 'Vale telefoni formaat';
    }
    
    // Обработка загрузки аватара
    $avatar = $user['avatar'];
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadFile($_FILES['avatar'], 'uploads/avatars', ['jpg', 'jpeg', 'png', 'gif'], 2097152);
        
        if ($uploadResult['success']) {
            // Удаляем старый аватар (если есть)
            if (!empty($user['avatar']) && $user['avatar'] !== 'default-avatar.png' && file_exists('uploads/avatars/' . $user['avatar'])) {
                deleteFile('uploads/avatars/' . $user['avatar']);
            }
            $avatar = $uploadResult['filename'];
        } else {
            $errors[] = $uploadResult['error'];
        }
    }
    
    // Если аватар не установлен, оставляем NULL
    if (empty($avatar)) {
        $avatar = null;
    }
    
    if (empty($errors)) {
        $sql = "UPDATE users SET phone = ?, avatar = ? WHERE id = ?";
        if (execute($pdo, $sql, [$phone, $avatar, $userId])) {
            // Обновляем данные пользователя из БД
            $user = fetchOne($pdo, "SELECT * FROM users WHERE id = ?", [$userId]);
            
            // Обновляем данные в сессии
            $_SESSION['user']['phone'] = $phone;
            $_SESSION['user']['avatar'] = $avatar;
            
            setFlashMessage('success', 'Profiil uuendatud');
        } else {
            setFlashMessage('error', 'Viga uuendamisel');
        }
        redirect('profile.php');
    } else {
        foreach ($errors as $error) {
            setFlashMessage('error', $error);
        }
    }
}

// Получение созданных событий
$createdEvents = fetchAll($pdo, 
    "SELECT e.*, l.city 
     FROM events e 
     LEFT JOIN locations l ON e.location_id = l.id 
     WHERE e.creator_id = ? 
     ORDER BY e.event_date DESC 
     LIMIT 5", 
    [$userId]
);

// Получение участий
$participatedEvents = fetchAll($pdo,
    "SELECT e.*, p.status, l.city
     FROM participants p
     JOIN events e ON p.event_id = e.id
     LEFT JOIN locations l ON e.location_id = l.id
     WHERE p.user_id = ?
     ORDER BY e.event_date DESC
     LIMIT 5",
    [$userId]
);

// Подсчёт статистики
$stats = [
    'created' => fetchOne($pdo, "SELECT COUNT(*) as count FROM events WHERE creator_id = ?", [$userId])['count'],
    'participated' => fetchOne($pdo, "SELECT COUNT(*) as count FROM participants WHERE user_id = ? AND status IN ('Записан', 'Подтвержден', 'Пришёл')", [$userId])['count'],
    'completed' => fetchOne($pdo, "SELECT COUNT(*) as count FROM participants WHERE user_id = ? AND status = 'Пришёл'", [$userId])['count']
];

// Процент посещаемости
$attendedEvents = $user['attended_events'] ?? 0;
$totalEvents = $user['total_events'] ?? 0;
$attendanceRate = $totalEvents > 0 
    ? round(($attendedEvents / $totalEvents) * 100) 
    : 0;

require_once 'includes/header.php';
?>

<div class="row">
    <!-- Основная информация -->
    <div class="col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-body text-center p-4">
                <!-- Аватар -->
                <div class="position-relative d-inline-block mb-3">
                    <?php 
                    if (!empty($user['avatar']) && file_exists('uploads/avatars/' . $user['avatar'])) {
                        $avatarPath = 'uploads/avatars/' . $user['avatar'];
                    } else {
                        $avatarPath = 'https://via.placeholder.com/150?text=' . urlencode(substr($user['username'], 0, 1));
                    }
                    ?>
                    <img src="<?php echo clean($avatarPath); ?>" 
                         alt="Avatar" 
                         class="avatar-xl rounded-circle shadow"
                         id="avatarPreview">
                    
                    <?php if ($isOwnProfile): ?>
                        <label for="avatarInput" 
                               class="position-absolute bottom-0 end-0 btn btn-sm btn-primary rounded-circle" 
                               style="width: 40px; height: 40px; cursor: pointer;"
                               title="Muuda avataari">
                            <i class="bi bi-camera"></i>
                        </label>
                    <?php endif; ?>
                </div>
                
                <!-- Имя и рейтинг -->
                <h3 class="mb-2"><?php echo clean($user['username']); ?></h3>
                <div class="mb-3">
                    <span class="badge bg-warning text-dark fs-5">
                        <i class="bi bi-star-fill"></i>
                        <?php echo number_format($user['rating'], 2); ?>
                    </span>
                </div>
                
                <!-- Контакты -->
                <div class="text-start mb-3">
                    <p class="mb-2">
                        <i class="bi bi-envelope me-2"></i>
                        <small><?php echo clean($user['email']); ?></small>
                    </p>
                    <?php if (!empty($user['phone'])): ?>
                        <p class="mb-0">
                            <i class="bi bi-telephone me-2"></i>
                            <small><?php echo clean($user['phone']); ?></small>
                        </p>
                    <?php endif; ?>
                </div>
                
                <hr>
                
                <!-- Кнопки -->
                <?php if ($isOwnProfile): ?>
                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                        <i class="bi bi-pencil me-2"></i>Muuda profiili
                    </button>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Статистика -->
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h5 class="card-title mb-4">
                    <i class="bi bi-graph-up me-2"></i>
                    Statistika
                </h5>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Loodud üritusi:</span>
                        <strong><?php echo $stats['created']; ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Osales:</span>
                        <strong><?php echo $stats['participated']; ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Lõpetatud:</span>
                        <strong><?php echo $stats['completed']; ?></strong>
                    </div>
                </div>
                
                <hr>
                
                <div class="mb-2">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Osalemine:</span>
                        <strong><?php echo $attendanceRate; ?>%</strong>
                    </div>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar <?php echo $attendanceRate >= 75 ? 'bg-success' : ($attendanceRate >= 50 ? 'bg-warning' : 'bg-danger'); ?>" 
                             role="progressbar" 
                             style="width: <?php echo $attendanceRate; ?>%">
                            <?php echo $attendanceRate; ?>%
                        </div>
                    </div>
                    <small class="text-muted">
                        Tuli kohale <?php echo $attendedEvents; ?> / <?php echo $totalEvents; ?> üritusest
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- События -->
    <div class="col-lg-8">
        <!-- Созданные события -->
        <div class="card shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-calendar-plus me-2"></i>
                        Loodud üritused
                    </h5>
                    <?php if ($isOwnProfile): ?>
                        <a href="create_event.php" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle me-1"></i>Loo
                        </a>
                    <?php endif; ?>
                </div>
                
                <?php if (empty($createdEvents)): ?>
                    <p class="text-muted text-center py-3">
                        <?php echo $isOwnProfile ? 'Sa pole veel ühtegi üritust loonud' : 'Kasutaja pole ühtegi üritust loonud'; ?>
                    </p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($createdEvents as $event): ?>
                            <?php
                            $sportIcon = ['Футбол' => '⚽', 'Волейбол' => '🏐', 'Баскетбол' => '🏀'][$event['sport_type']] ?? '⚽';
                            $statusClass = ['Открыто' => 'success', 'Закрыто' => 'secondary', 'Завершено' => 'info', 'Отменено' => 'danger'][$event['status']] ?? 'secondary';
                            ?>
                            <a href="event.php?id=<?php echo $event['id']; ?>" 
                               class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">
                                            <?php echo $sportIcon; ?>
                                            <?php echo clean($event['title']); ?>
                                        </h6>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar me-1"></i>
                                            <?php echo formatDate($event['event_date']); ?>
                                            •
                                            <i class="bi bi-people me-1"></i>
                                            <?php echo $event['current_participants']; ?>/<?php echo $event['max_participants']; ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-<?php echo $statusClass; ?>">
                                        <?php echo $event['status']; ?>
                                    </span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($stats['created'] > 5): ?>
                        <div class="text-center mt-3">
                            <a href="my_events.php" class="btn btn-outline-primary btn-sm">
                                Näita kõiki (<?php echo $stats['created']; ?>)
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Участия в событиях -->
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h5 class="card-title mb-4">
                    <i class="bi bi-calendar-check me-2"></i>
                    Osalemine üritustel
                </h5>
                
                <?php if (empty($participatedEvents)): ?>
                    <p class="text-muted text-center py-3">
                        <?php echo $isOwnProfile ? 'Sa pole veel üheski ürituses osalenud' : 'Kasutaja pole üheski ürituses osalenud'; ?>
                    </p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($participatedEvents as $event): ?>
                            <?php
                            $sportIcon = ['Футбол' => '⚽', 'Волейбол' => '🏐', 'Баскетбол' => '🏀'][$event['sport_type']] ?? '⚽';
                            $statusClass = ['Записан' => 'primary', 'Подтвержден' => 'success', 'Пришёл' => 'success', 'Не пришёл' => 'danger', 'Отменил' => 'secondary'][$event['status']] ?? 'secondary';
                            ?>
                            <a href="event.php?id=<?php echo $event['id']; ?>" 
                               class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">
                                            <?php echo $sportIcon; ?>
                                            <?php echo clean($event['title']); ?>
                                        </h6>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar me-1"></i>
                                            <?php echo formatDate($event['event_date']); ?>
                                            <?php if ($event['city']): ?>
                                                •
                                                <i class="bi bi-geo-alt me-1"></i>
                                                <?php echo clean($event['city']); ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-<?php echo $statusClass; ?>">
                                        <?php echo $event['status']; ?>
                                    </span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($stats['participated'] > 5): ?>
                        <div class="text-center mt-3">
                            <a href="my_events.php" class="btn btn-outline-primary btn-sm">
                                Näita kõiki (<?php echo $stats['participated']; ?>)
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно редактирования профиля -->
<?php if ($isOwnProfile): ?>
<div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil me-2"></i>
                    Muuda profiili
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="profile.php" enctype="multipart/form-data">
                <?php echo csrfField(); ?>
                <div class="modal-body">
                    <!-- Аватар -->
                    <div class="mb-3">
                        <label for="avatarInput" class="form-label">
                            <i class="bi bi-image me-1"></i>
                            Avatar
                        </label>
                        <input type="file" 
                               class="form-control" 
                               id="avatarInput" 
                               name="avatar" 
                               accept="image/*"
                               onchange="previewImage(this, 'avatarPreview')">
                        <div class="form-text">JPG, PNG või GIF. Maksimaalselt 2 MB</div>
                    </div>
                    
                    <!-- Телефон -->
                    <div class="mb-3">
                        <label for="phone" class="form-label">
                            <i class="bi bi-telephone me-1"></i>
                            Telefon
                        </label>
                        <input type="tel" 
                               class="form-control" 
                               id="phone" 
                               name="phone" 
                               value="<?php echo clean($user['phone']); ?>"
                               placeholder="+7 (999) 123-45-67">
                    </div>
                    
                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle me-2"></i>
                        E-posti ja kasutajanime ei saa muuta. Parooli muutmiseks võta ühendust tugiteenusega.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Katkesta</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>Salvesta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
