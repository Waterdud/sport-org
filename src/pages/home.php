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
$where = ["status = 'Открыто'", "event_date >= DATE('now')"];
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
        <h1>
            <i class="bi bi-calendar-event text-primary me-2"></i>
            Treeningud
        </h1>
        <p class="text-muted">Leia ja liitu oma lemmik treiningtega</p>
    </div>
</div>

<!-- Фильтры -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/" class="row g-3">
            <div class="col-md-4">
                <select name="sport" class="form-select">
                    <option value="">Kõik spordialad</option>
                    <option value="Футбол" <?php echo $sportType === 'Футбол' ? 'selected' : ''; ?>>⚽ Jalgpall</option>
                    <option value="Волейбол" <?php echo $sportType === 'Волейбол' ? 'selected' : ''; ?>>🏐 Võrkpall</option>
                    <option value="Баскетбол" <?php echo $sportType === 'Баскетбол' ? 'selected' : ''; ?>>🏀 Korvpall</option>
                </select>
            </div>
            <div class="col-md-4">
                <select name="city" class="form-select">
                    <option value="">Kõik linnad</option>
                    <?php foreach ($cities as $c): ?>
                        <option value="<?php echo clean($c['city']); ?>" <?php echo $city === $c['city'] ? 'selected' : ''; ?>>
                            <?php echo clean($c['city']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel me-2"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- События -->
<?php if (empty($events)): ?>
    <div class="card text-center py-5">
        <i class="bi bi-calendar-x display-1 text-muted mb-3"></i>
        <h4>Üritusi ei leitud</h4>
        <p class="text-muted">Loo esimene üritus!</p>
        <?php if (isLoggedIn()): ?>
            <a href="/events/create" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Loo üritus
            </a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($events as $event): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo clean($event['title']); ?></h5>
                        <p class="card-text text-muted"><?php echo clean($event['location_name']); ?></p>
                        <small class="d-block mb-2">
                            📅 <?php echo formatDateEt($event['event_date']); ?>
                            ⏰ <?php echo formatTimeEt($event['event_time']); ?>
                        </small>
                        <small class="d-block mb-2">
                            👥 <?php echo $event['current_participants']; ?>/<?php echo $event['max_participants']; ?>
                        </small>
                        <a href="<?php echo SITE_URL; ?>/src/pages/events/view.php?id=<?php echo $event['id']; ?>" 
                           class="btn btn-sm btn-primary">
                            Vaata üritust
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once BASE_PATH . '/src/components/Footer.php'; ?>
