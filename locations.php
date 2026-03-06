<?php
/**
 * Список мест для игр
 * 
 * Функционал:
 * - Отображение всех доступных мест
 * - Фильтр по городу и виду спорта
 * - Поиск по названию
 */

require_once 'includes/db.php';
require_once 'includes/functions.php';

$pageTitle = 'Kohad mängimiseks';

// Параметры фильтрации
$city = $_GET['city'] ?? '';
$sportType = $_GET['sport'] ?? '';
$search = $_GET['search'] ?? '';

// Построение SQL запроса
$where = [];
$params = [];

if (!empty($city)) {
    $where[] = "city = ?";
    $params[] = $city;
}

if (!empty($sportType)) {
    $where[] = "sport_types LIKE ?";
    $params[] = "%$sportType%";
}

if (!empty($search)) {
    $where[] = "(name LIKE ? OR address LIKE ? OR description LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Получение локаций
$sql = "SELECT * FROM locations $whereClause ORDER BY city, name";
$locations = fetchAll($pdo, $sql, $params);

// Список городов для фильтра
$cities = fetchAll($pdo, "SELECT DISTINCT city FROM locations ORDER BY city");

require_once 'includes/header.php';
?>

<!-- Заголовок -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2>
                <i class="bi bi-geo-alt text-primary me-2"></i>
                Kohad mängimiseks
            </h2>
            <?php if (isLoggedIn()): ?>
                <a href="add_location.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Lisa koht
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Фильтры -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="locations.php" class="row g-3">
            <!-- Поиск -->
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" 
                           class="form-control" 
                           name="search" 
                           value="<?php echo clean($search); ?>"
                           placeholder="Otsi nime või aadressi järgi...">
                </div>
            </div>
            
            <!-- Город -->
            <div class="col-md-3">
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
            
            <!-- Вид спорта -->
            <div class="col-md-3">
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
            
            <!-- Кнопки -->
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
            </div>
        </form>
        
        <!-- Активные фильтры -->
        <?php if (!empty($city) || !empty($sportType) || !empty($search)): ?>
            <div class="mt-3">
                <span class="text-muted me-2">Aktiivsed filtrid:</span>
                <?php if (!empty($search)): ?>
                    <span class="badge bg-secondary me-1">
                        Otsing: <?php echo clean($search); ?>
                        <a href="?<?php echo http_build_query(array_diff_key($_GET, ['search' => ''])); ?>" 
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
                <?php if (!empty($sportType)): ?>
                    <span class="badge bg-secondary me-1">
                        <?php echo clean($sportType); ?>
                        <a href="?<?php echo http_build_query(array_diff_key($_GET, ['sport' => ''])); ?>" 
                           class="text-white ms-1">×</a>
                    </span>
                <?php endif; ?>
                <a href="locations.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Lähtesta
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Список локаций -->
<?php if (empty($locations)): ?>
    <div class="card text-center py-5">
        <div class="card-body">
            <i class="bi bi-geo display-1 text-muted mb-3"></i>
            <h4 class="text-muted">Kohti ei leitud</h4>
            <p class="text-muted mb-4">Proovi muuta otsingu parameetreid või lisa uus koht</p>
            <?php if (isLoggedIn()): ?>
                <a href="add_location.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Lisa koht
                </a>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-info mb-4">
        <i class="bi bi-info-circle me-2"></i>
        Leitud kohti: <strong><?php echo count($locations); ?></strong>
    </div>
    
    <div class="row g-4">
        <?php foreach ($locations as $location): ?>
            <?php
            // Подсчёт предстоящих событий в этой локации
            $eventsCountResult = fetchOne($pdo, 
                "SELECT COUNT(*) as count FROM events 
                 WHERE location_id = ? AND status = 'active' AND event_date >= DATE('now')",
                [$location['id']]
            );
            $eventsCount = $eventsCountResult ? $eventsCountResult['count'] : 0;
            
            // Разбиваем виды спорта
            $sports = explode(',', $location['sport_types']);
            ?>
            
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <?php if (!empty($location['image'])): ?>
                        <img src="uploads/locations/<?php echo clean($location['image']); ?>" 
                             class="card-img-top" 
                             alt="<?php echo clean($location['name']); ?>">
                    <?php else: ?>
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                             style="height: 200px;">
                            <i class="bi bi-geo-alt display-1 text-muted"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo clean($location['name']); ?></h5>
                        
                        <!-- Адрес -->
                        <div class="mb-2">
                            <i class="bi bi-geo-alt text-primary me-2"></i>
                            <small><?php echo clean($location['address']); ?></small>
                        </div>
                        
                        <!-- Город -->
                        <div class="mb-3">
                            <i class="bi bi-building text-primary me-2"></i>
                            <small><?php echo clean($location['city']); ?></small>
                        </div>
                        
                        <!-- Виды спорта -->
                        <div class="mb-3">
                            <?php foreach ($sports as $sport): ?>
                                <?php
                                $sport = trim($sport);
                                $sportIcon = ['Футбол' => '⚽', 'Волейбол' => '🏐', 'Баскетбол' => '🏀'][$sport] ?? '🏅';
                                ?>
                                <span class="badge bg-primary me-1">
                                    <?php echo $sportIcon; ?> <?php echo clean($sport); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Описание -->
                        <?php if (!empty($location['description'])): ?>
                            <p class="card-text text-muted small mb-3">
                                <?php echo mb_substr(clean($location['description']), 0, 100); ?>
                                <?php echo mb_strlen($location['description']) > 100 ? '...' : ''; ?>
                            </p>
                        <?php endif; ?>
                        
                        <!-- Статистика -->
                        <div class="mt-auto">
                            <?php if ($eventsCount > 0): ?>
                                <div class="alert alert-success py-2 mb-3">
                                    <i class="bi bi-calendar-check me-1"></i>
                                    <small>
                                        <?php echo $eventsCount; ?> 
                                        <?php echo plural($eventsCount, 'tulev üritus', 'tulevat üritust', 'tulevat üritust'); ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Кнопки -->
                            <div class="d-grid gap-2">
                                <a href="index.php?city=<?php echo urlencode($location['city']); ?>" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-calendar-event me-2"></i>
                                    Leia siit üritusi
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Информационный блок -->
<div class="row mt-5">
    <div class="col-12">
        <div class="card bg-light">
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="text-center">
                            <i class="bi bi-geo-alt text-primary display-4 mb-3"></i>
                            <h5>Palju kohti</h5>
                            <p class="text-muted small mb-0">
                                Vali laiast spordiväljakute nimekirjast oma linnas
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="text-center">
                            <i class="bi bi-trophy text-primary display-4 mb-3"></i>
                            <h5>Erinevad spordialad</h5>
                            <p class="text-muted small mb-0">
                                Jalgpall, võrkpall, korvpall - leia koht oma lemmikspordile
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <i class="bi bi-people text-primary display-4 mb-3"></i>
                            <h5>Mugav kõigile</h5>
                            <p class="text-muted small mb-0">
                                Lisa oma lemmikkohti ja jaga neid kogukonnaga
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
