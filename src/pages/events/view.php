<?php
/**
 * Просмотр события - Ürituse kuvamine
 */

require_once dirname(__DIR__, 3) . '/src/config/bootstrap.php';

$pageTitle = 'Ürituse vaatamine';

$id = (int)($_GET['id'] ?? 0);
if ($id === 0) redirect('list.php');

$event = fetchOne($pdo, 
    "SELECT e.*, u.username, l.name as location_name 
     FROM events e 
     JOIN users u ON e.creator_id = u.id 
     LEFT JOIN locations l ON e.location_id = l.id 
     WHERE e.id = ?", 
    [$id]);

if (!$event) redirect('list.php');

require_once BASE_PATH . '/src/components/Header.php';
?>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-body">
                <h1><?php echo clean($event['title']); ?></h1>
                <p class="text-muted"><?php echo clean($event['username']); ?> poolt</p>
                
                <div class="mb-3">
                    <p><strong>📍 Asukoht:</strong> <?php echo clean($event['location_name']); ?></p>
                    <p><strong>📅 Kuupäev:</strong> <?php echo formatDateEt($event['event_date']); ?></p>
                    <p><strong>⏰ Kellaaeg:</strong> <?php echo formatTimeEt($event['event_time']); ?></p>
                    <p><strong>🏅 Vilumuse tase:</strong> <?php echo translateSkillLevel($event['skill_level']); ?></p>
                    <p><strong>👥 Osalised:</strong> <?php echo $event['current_participants']; ?>/<?php echo $event['max_participants']; ?></p>
                </div>
                
                <?php if (!empty($event['description'])): ?>
                    <p><?php echo nl2br(clean($event['description'])); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . '/src/components/Footer.php'; ?>
