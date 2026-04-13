<?php
/**
 * Список всех событий - Treeningud
 */

require_once dirname(__DIR__, 3) . '/src/config/bootstrap.php';

$pageTitle = 'Treeningud';

// Получение всех событий
$sql = "SELECT e.*, u.username, l.name as location_name
        FROM events e 
        JOIN users u ON e.creator_id = u.id 
        LEFT JOIN locations l ON e.location_id = l.id 
        WHERE e.status NOT IN ('cancelled') 
          AND (e.event_date > DATE('now') 
               OR (e.event_date = DATE('now') AND e.event_time > TIME('now')))
        ORDER BY e.event_date ASC";

$events = fetchAll($pdo, $sql, []);

require_once BASE_PATH . '/src/components/Header.php';
?>

<h2>
    <i class="bi bi-calendar-event me-2"></i>Kõik treeningud
</h2>

<div class="row g-4">
    <?php foreach ($events as $event): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?php echo clean($event['title']); ?></h5>
                    <p class="card-text"><?php echo clean($event['location_name']); ?></p>
                    <small>📅 <?php echo formatDateEt($event['event_date']); ?></small>
                    <a href="/events/view?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-primary">Vaata</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once BASE_PATH . '/src/components/Footer.php'; ?>
