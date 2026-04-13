<?php
/**
 * Главная страница - Avaleht
 * 
 * Список всех спортивных событий с фильтрами
 */

require_once dirname(__DIR__, 2) . '/src/config/bootstrap.php';

$pageTitle = 'Avaleht';

// Параметры фильтрации
$sportType = $_GET['sport'] ?? '';
$city = $_GET['city'] ?? '';
$date = $_GET['date'] ?? '';

// Получение событий
$where = ["e.status NOT IN ('cancelled')", "(e.event_date > DATE('now') OR (e.event_date = DATE('now') AND e.event_time > TIME('now')))"];
$params = [];

if (!empty($sportType)) {
    $where[] = "sport_type = ?";
    $params[] = $sportType;
}

if (!empty($city)) {
    $where[] = "l.city = ?";
    $params[] = $city;
}

$whereClause = implode(' AND ', $where);
$sql = "SELECT e.*, u.username, l.name as location_name, l.city 
        FROM events e 
        JOIN users u ON e.creator_id = u.id 
        LEFT JOIN locations l ON e.location_id = l.id 
        WHERE $whereClause 
        ORDER BY e.event_date ASC 
        LIMIT 50";

$events = fetchAll($pdo, $sql, $params);

// Города для фильтра
$cities = fetchAll($pdo, "SELECT DISTINCT l.city FROM locations l ORDER BY city");

require_once BASE_PATH . '/src/components/Header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="mb-1">
                    <i class="bi bi-calendar-event text-primary me-2"></i>
                    Treeningud
                </h1>
                <p class="text-muted mb-0">Leia ja liitu oma lemmik treiningtega</p>
            </div>
            <?php if (isLoggedIn()): ?>
                <a href="/events/create" class="btn btn-primary btn-lg">
                    <i class="bi bi-plus-circle me-1"></i>Loo üritus
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Фильтры и поиск -->
<div class="filters-card">
    <form method="GET" action="/" class="row g-3">
        <!-- Поиск -->
        <div class="col-md-4 col-lg-3">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" 
                       placeholder="Otsi üritusi..." value="<?php echo isset($_GET['search']) ? clean($_GET['search']) : ''; ?>">
            </div>
        </div>

        <!-- Spordialad -->
        <div class="col-md-4 col-lg-3">
            <select name="sport" class="form-select">
                <option value="">Kõik spordialad</option>
                <option value="Jalgpall" <?php echo $sportType === 'Jalgpall' ? 'selected' : ''; ?>>Jalgpall</option>
                <option value="Võrkpall" <?php echo $sportType === 'Võrkpall' ? 'selected' : ''; ?>>Võrkpall</option>
                <option value="Korvpall" <?php echo $sportType === 'Korvpall' ? 'selected' : ''; ?>>Korvpall</option>
                <option value="Tennis" <?php echo $sportType === 'Tennis' ? 'selected' : ''; ?>>Tennis</option>
                <option value="Ujumine" <?php echo $sportType === 'Ujumine' ? 'selected' : ''; ?>>Ujumine</option>
            </select>
        </div>

        <!-- Linn -->
        <div class="col-md-4 col-lg-3">
            <select name="city" class="form-select">
                <option value="">Kõik linnad</option>
                <?php foreach ($cities as $c): ?>
                    <option value="<?php echo clean($c['city']); ?>" <?php echo $city === $c['city'] ? 'selected' : ''; ?>>
                        <?php echo clean($c['city']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Nupp -->
        <div class="col-md-12 col-lg-3">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
        </div>
    </form>
</div>

<!-- События -->
<?php if (empty($events)): ?>
    <div class="card text-center py-5">
        <div class="empty-state">
            <i class="bi bi-calendar-x empty-state-icon"></i>
            <h4 class="empty-state-title">Üritusi ei leitud</h4>
            <p class="empty-state-text">Muuda otsingutingimusi või loo esimene üritus!</p>
            <?php if (isLoggedIn()): ?>
                <a href="/events/create" class="btn btn-primary btn-lg">
                    <i class="bi bi-plus-circle me-1"></i>Loo üritus
                </a>
            <?php else: ?>
                <a href="/login" class="btn btn-primary btn-lg">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Logi sisse
                </a>
            <?php endif; ?>
        </div>
    </card>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($events as $event): ?>
            <div class="col-md-6 col-lg-4 fade-in">
                <div class="card event-card h-100">
                    <span class="event-badge"><?php echo clean($event['sport_type']); ?></span>
                    
                    <div class="card-body event-content">
                        <h5 class="card-title"><?php echo clean($event['title']); ?></h5>
                        <p class="card-text text-muted"><?php echo clean($event['description'] ?? 'Ürituse kirjeldus puudub'); ?></p>
                        
                        <div class="event-meta">
                            <div class="event-date">
                                <i class="bi bi-calendar3"></i>
                                <?php echo formatDateEt($event['event_date']); ?>
                                <i class="bi bi-clock ms-2"></i>
                                <?php echo formatTimeEt($event['event_time']); ?>
                            </div>
                        </div>

                        <div class="event-meta">
                            <div class="event-location">
                                <i class="bi bi-geo-alt"></i>
                                <?php echo clean($event['location_name'] ?? 'Koht määramata'); ?>
                            </div>
                        </div>

                        <div class="event-meta d-flex justify-content-between align-items-center">
                            <div class="participants-count">
                                <i class="bi bi-people"></i>
                                <span><?php echo $event['current_participants'] ?? 0; ?>/<?php echo $event['max_participants']; ?></span>
                            </div>
                            <span class="badge badge-primary"><?php echo ucfirst($event['skill_level'] ?? 'Keskmine'); ?></span>
                        </div>
                    </div>

                    <div style="padding: 1rem;border-top: 1px solid #e2e8f0;">
                        <a href="<?php echo SITE_URL; ?>/src/pages/events/view.php?id=<?php echo $event['id']; ?>" 
                           class="btn btn-primary w-100">
                            <i class="bi bi-eye me-1"></i>Vaata üritust
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once BASE_PATH . '/src/components/Footer.php'; ?>
