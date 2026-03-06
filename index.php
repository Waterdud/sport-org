<?php
/**
 * Главная страница - список спортивных событий
 * 
 * Функционал:
 * - Отображение всех активных событий
 * - Фильтры (вид спорта, город, дата, уровень)
 * - Поиск по названию
 * - Пагинация
 * - Сортировка
 */

require_once 'includes/db.php';
require_once 'includes/functions.php';

$pageTitle = 'Spordiüritused';

// Получение параметров фильтрации
$sportType = $_GET['sport'] ?? '';
$city = $_GET['city'] ?? '';
$date = $_GET['date'] ?? '';
$skillLevel = $_GET['skill'] ?? '';
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 9; // Событий на страницу

// Построение SQL запроса с фильтрами
$where = ["e.status = 'Открыто'"];
$params = [];

if (!empty($sportType)) {
    $where[] = "e.sport_type = ?";
    $params[] = $sportType;
}

if (!empty($city)) {
    $where[] = "l.city = ?";
    $params[] = $city;
}

if (!empty($date)) {
    $where[] = "e.event_date = ?";
    $params[] = $date;
}

if (!empty($skillLevel)) {
    $where[] = "e.skill_level = ?";
    $params[] = $skillLevel;
}

if (!empty($search)) {
    $where[] = "(e.title LIKE ? OR e.description LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
}

$whereClause = implode(' AND ', $where);

// Подсчёт общего количества событий
$countSql = "SELECT COUNT(*) as total 
             FROM events e 
             LEFT JOIN locations l ON e.location_id = l.id 
             WHERE $whereClause";
$totalResult = fetchOne($pdo, $countSql, $params);
$totalEvents = $totalResult['total'];

// Пагинация
$pagination = paginate($totalEvents, $perPage, $page);
$offset = $pagination['offset'];

// Получение событий с учётом фильтров и пагинации
$sql = "SELECT e.*, 
               u.username as creator_name, 
               u.rating as creator_rating,
               l.name as location_name,
               l.address as location_address,
               l.city as location_city
        FROM events e
        LEFT JOIN users u ON e.creator_id = u.id
        LEFT JOIN locations l ON e.location_id = l.id
        WHERE $whereClause
        ORDER BY e.event_date ASC, e.event_time ASC
        LIMIT $perPage OFFSET $offset";

$events = fetchAll($pdo, $sql, $params);

// Получение списка городов для фильтра
$cities = fetchAll($pdo, "SELECT DISTINCT city FROM locations ORDER BY city");

// Подключаем header
require_once 'includes/header.php';
?>

<!-- Баннер приветствия -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-primary text-white shadow-lg">
            <div class="card-body p-5 text-center">
                <h1 class="display-4 fw-bold mb-3">
                    <i class="bi bi-trophy-fill me-3"></i>
                    Leia oma mäng!
                </h1>
                <p class="lead mb-4">
                    Liitu spordiüritustega sinu linnas. 
                    Jalgpall, võrkpall, korvpall - vali ja mängi!
                </p>
                <?php if (!isLoggedIn()): ?>
                    <a href="register.php" class="btn btn-warning btn-lg me-2">
                        <i class="bi bi-person-plus me-2"></i>Registreeru
                    </a>
                    <a href="login.php" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Logi sisse
                    </a>
                <?php else: ?>
                    <a href="create_event.php" class="btn btn-warning btn-lg">
                        <i class="bi bi-plus-circle me-2"></i>Loo üritus
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Фильтры и поиск -->
<div class="filters-section mb-4">
    <form method="GET" action="index.php" class="row g-3">
        <!-- Поиск -->
        <div class="col-md-3">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" 
                       class="form-control" 
                       name="search" 
                       value="<?php echo clean($search); ?>"
                       placeholder="Otsi üritusi...">
            </div>
        </div>
        
        <!-- Вид спорта -->
        <div class="col-md-2">
            <select class="form-select" name="sport">
                <option value="">Kõik spordialad</option>
                <option value="Футбол" <?php echo $sportType === 'Футбол' ? 'selected' : ''; ?>>
                    ⚽ Jalgpall
                </option>
                <option value="Волейбол" <?php echo $sportType === 'Волейбол' ? 'selected' : ''; ?>>
                    🏐 Võrkpall
                </option>
                <option value="Баскетбол" <?php echo $sportType === 'Баскетбол' ? 'selected' : ''; ?>>
                    🏀 Korvpall
                </option>
            </select>
        </div>
        
        <!-- Город -->
        <div class="col-md-2">
            <select class="form-select" name="city">
                <option value="">Kõik linnad</option>
                <?php foreach ($cities as $cityItem): ?>
                    <option value="<?php echo clean($cityItem['city']); ?>"
                            <?php echo $city === $cityItem['city'] ? 'selected' : ''; ?>>
                        <?php echo clean($cityItem['city']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <!-- Дата -->
        <div class="col-md-2">
            <input type="date" 
                   class="form-control" 
                   name="date" 
                   value="<?php echo clean($date); ?>"
                   min="<?php echo date('Y-m-d'); ?>">
        </div>
        
        <!-- Уровень -->
        <div class="col-md-2">
            <select class="form-select" name="skill">
                <option value="">Kõik tasemed</option>
                <option value="Начинающий" <?php echo $skillLevel === 'Начинающий' ? 'selected' : ''; ?>>
                    Algaja
                </option>
                <option value="Любитель" <?php echo $skillLevel === 'Любитель' ? 'selected' : ''; ?>>
                    Harrastaja
                </option>
                <option value="Продвинутый" <?php echo $skillLevel === 'Продвинутый' ? 'selected' : ''; ?>>
                    Edasijõudnu
                </option>
                <option value="Профессионал" <?php echo $skillLevel === 'Профессионал' ? 'selected' : ''; ?>>
                    Professionaal
                </option>
            </select>
        </div>
        
        <!-- Кнопки -->
        <div class="col-md-1">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-funnel"></i>
            </button>
        </div>
    </form>
    
    <!-- Активные фильтры -->
    <?php if (!empty($sportType) || !empty($city) || !empty($date) || !empty($skillLevel) || !empty($search)): ?>
        <div class="mt-3">
            <span class="text-muted me-2">Aktiivsed filtrid:</span>
            <?php if (!empty($search)): ?>
                <span class="badge bg-secondary me-1">
                    Otsing: <?php echo clean($search); ?>
                    <a href="?<?php echo http_build_query(array_diff_key($_GET, ['search' => ''])); ?>" 
                       class="text-white ms-1">×</a>
                </span>
            <?php endif; ?>
            <?php if (!empty($sportType)): ?>
                <span class="badge bg-secondary me-1">
                    <?php echo clean($sportType); ?>
                    <a href="?<?php echo http_build_query(array_diff_key($_GET, ['sport' => ''])); ?>" 
                       class="text-white ms-1">×</a>
                </span>
            <?php endif; ?>
            <?php if (!empty($city)): ?>
                <span class="badge bg-secondary me-1">
                    <?php echo clean($city); ?>
                    <a href="?<?php echo http_build_query(array_diff_key($_GET, ['city' => ''])); ?>" 
                       class="text-white ms-1">×</a>
                </span>
            <?php endif; ?>
            <?php if (!empty($date)): ?>
                <span class="badge bg-secondary me-1">
                    <?php echo formatDate($date); ?>
                    <a href="?<?php echo http_build_query(array_diff_key($_GET, ['date' => ''])); ?>" 
                       class="text-white ms-1">×</a>
                </span>
            <?php endif; ?>
            <?php if (!empty($skillLevel)): ?>
                <span class="badge bg-secondary me-1">
                    <?php echo clean($skillLevel); ?>
                    <a href="?<?php echo http_build_query(array_diff_key($_GET, ['skill' => ''])); ?>" 
                       class="text-white ms-1">×</a>
                </span>
            <?php endif; ?>
            <a href="index.php" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-x-circle me-1"></i>Lähtesta kõik
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Статистика -->
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-info d-flex align-items-center">
            <i class="bi bi-info-circle me-2 fs-4"></i>
            <div>
                Leitud üritusi: <strong><?php echo $totalEvents; ?></strong>
                <?php if ($totalEvents > 0): ?>
                    (показаны <?php echo $offset + 1; ?>-<?php echo min($offset + $perPage, $totalEvents); ?>)
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Список событий -->
<?php if (empty($events)): ?>
    <div class="row">
        <div class="col-12">
            <div class="card text-center py-5">
                <div class="card-body">
                    <i class="bi bi-calendar-x display-1 text-muted mb-3"></i>
                    <h3 class="text-muted">Üritusi ei leitud</h3>
                    <p class="text-muted">Proovi muuta filtri parameetreid või loo oma üritus</p>
                    <?php if (isLoggedIn()): ?>
                        <a href="create_event.php" class="btn btn-primary mt-3">
                            <i class="bi bi-plus-circle me-2"></i>Loo üritus
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($events as $event): ?>
            <?php
            // Определяем класс для вида спорта
            $sportClass = [
                'Футбол' => 'sport-football',
                'Волейбол' => 'sport-volleyball',
                'Баскетбол' => 'sport-basketball'
            ][$event['sport_type']] ?? 'bg-secondary';
            
            // Определяем класс для уровня
            $skillClass = [
                'Начинающий' => 'badge-beginner',
                'Любитель' => 'badge-amateur',
                'Продвинутый' => 'badge-advanced',
                'Профессионал' => 'badge-professional'
            ][$event['skill_level']] ?? 'bg-secondary';
            
            // Процент заполненности
            $fillPercentage = ($event['current_participants'] / $event['max_participants']) * 100;
            
            // Проверяем, записан ли текущий пользователь
            $isParticipant = false;
            if (isLoggedIn()) {
                $participation = fetchOne($pdo, 
                    "SELECT * FROM participants WHERE event_id = ? AND user_id = ? AND status IN ('Записан', 'Подтвержден')",
                    [$event['id'], getCurrentUserId()]
                );
                $isParticipant = !empty($participation);
            }
            ?>
            
            <div class="col-md-6 col-lg-4">
                <div class="card event-card shadow-sm h-100 fade-in" data-event-id="<?php echo $event['id']; ?>">
                    <!-- Бейдж вида спорта -->
                    <span class="badge <?php echo $sportClass; ?> badge-sport">
                        <?php echo clean($event['sport_type']); ?>
                    </span>
                    
                    <div class="card-body d-flex flex-column">
                        <!-- Заголовок -->
                        <h5 class="card-title mb-3">
                            <a href="event.php?id=<?php echo $event['id']; ?>" 
                               class="text-decoration-none text-dark">
                                <?php echo clean($event['title']); ?>
                            </a>
                        </h5>
                        
                        <!-- Дата и время -->
                        <div class="event-date mb-2">
                            <i class="bi bi-calendar-event me-2"></i>
                            <?php echo formatDateTime($event['event_date'], $event['event_time']); ?>
                        </div>
                        
                        <!-- Локация -->
                        <?php if ($event['location_name']): ?>
                            <div class="event-location mb-2">
                                <i class="bi bi-geo-alt me-2"></i>
                                <?php echo clean($event['location_name']); ?>
                                <?php if ($event['location_city']): ?>
                                    <small>(<?php echo clean($event['location_city']); ?>)</small>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Уровень -->
                        <div class="mb-2">
                            <span class="badge <?php echo $skillClass; ?>">
                                <?php echo clean($event['skill_level']); ?>
                            </span>
                        </div>
                        
                        <!-- Участники -->
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">
                                    <i class="bi bi-people me-1"></i>
                                    <span class="participants-count"><?php echo $event['current_participants']; ?></span>
                                    / <?php echo $event['max_participants']; ?> osalejat
                                </small>
                                <!-- Организатор -->
                                <small class="text-muted" title="Организатор">
                                    <i class="bi bi-person-badge"></i>
                                    <?php echo clean($event['creator_name']); ?>
                                    <span class="badge bg-warning text-dark ms-1">
                                        ⭐ <?php echo number_format($event['creator_rating'], 1); ?>
                                    </span>
                                </small>
                            </div>
                            
                            <!-- Прогресс-бар -->
                            <div class="progress mb-3" style="height: 8px;">
                                <div class="progress-bar" 
                                     role="progressbar" 
                                     style="width: <?php echo $fillPercentage; ?>%"
                                     aria-valuenow="<?php echo $event['current_participants']; ?>"
                                     aria-valuemin="0"
                                     aria-valuemax="<?php echo $event['max_participants']; ?>">
                                </div>
                            </div>
                            
                            <!-- Кнопки действий -->
                            <div class="d-grid gap-2">
                                <?php if (!isLoggedIn()): ?>
                                    <a href="login.php" class="btn btn-primary">
                                        <i class="bi bi-box-arrow-in-right me-2"></i>Logi sisse registreerimiseks
                                    </a>
                                <?php elseif ($event['creator_id'] == getCurrentUserId()): ?>
                                    <a href="event.php?id=<?php echo $event['id']; ?>" class="btn btn-info">
                                        <i class="bi bi-pencil me-2"></i>Halda
                                    </a>
                                <?php elseif ($isParticipant): ?>
                                    <a href="event.php?id=<?php echo $event['id']; ?>" class="btn btn-success">
                                        <i class="bi bi-check-circle me-2"></i>Oled registreeritud
                                    </a>
                                <?php elseif ($event['current_participants'] >= $event['max_participants']): ?>
                                    <button class="btn btn-secondary" disabled>
                                        <i class="bi bi-x-circle me-2"></i>Kohti pole
                                    </button>
                                <?php else: ?>
                                    <a href="event.php?id=<?php echo $event['id']; ?>" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-2"></i>Registreeru
                                    </a>
                                <?php endif; ?>
                                
                                <a href="event.php?id=<?php echo $event['id']; ?>" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-eye me-1"></i>Vaata lähemalt
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Пагинация -->
    <?php if ($pagination['totalPages'] > 1): ?>
        <div class="row mt-5">
            <div class="col-12">
                <?php
                // Сохраняем GET параметры для пагинации
                $queryParams = $_GET;
                unset($queryParams['page']);
                $baseUrl = 'index.php?' . http_build_query($queryParams) . '&page=';
                echo renderPagination($pagination['totalPages'], $pagination['currentPage'], $baseUrl);
                ?>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
