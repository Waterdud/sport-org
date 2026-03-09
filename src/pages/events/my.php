<?php
/**
 * Мои события - Minu treeningud
 */

require_once dirname(__DIR__, 3) . '/config/bootstrap.php';
requireAuth();

$pageTitle = 'Minu treeningud';

$userId = getCurrentUserId();

// Мои события
$myEvents = fetchAll($pdo,
    "SELECT DISTINCT e.* FROM events e 
     WHERE e.creator_id = ? 
     ORDER BY e.event_date DESC",
    [$userId]);

// События в которых участвую
$joineEvents = fetchAll($pdo,
    "SELECT e.* FROM events e 
     JOIN participants p ON e.id = p.event_id 
     WHERE p.user_id = ? 
     ORDER BY e.event_date DESC",
    [$userId]);

require_once BASE_PATH . '/src/components/Header.php';
?>

<h2>
    <i class="bi bi-calendar-check me-2"></i>Minu treeningud
</h2>

<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link active" href="#created">Loodud</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#joined">Liitunud</a>
    </li>
</ul>

<div id="created">
    <h3>Minu loodud treeningud</h3>
    <div class="row g-4">
        <?php foreach ($myEvents as $event): ?>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5><?php echo clean($event['title']); ?></h5>
                        <p><?php echo formatDateEt($event['event_date']); ?></p>
                        <a href="view.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-primary">Vaata</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once BASE_PATH . '/src/components/Footer.php'; ?>
