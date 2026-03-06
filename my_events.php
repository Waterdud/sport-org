<?php
/**
 * Мои события
 * 
 * Функционал:
 * - События, которые создал пользователь
 * - События, на которые записан пользователь
 * - История прошедших событий
 */

require_once 'includes/db.php';
require_once 'includes/functions.php';

requireAuth();

$pageTitle = 'Minu üritused';
$currentUser = getCurrentUser();

// Вкладка (created/participating/history)
$tab = $_GET['tab'] ?? 'created';

// Получение созданных событий
$createdEvents = fetchAll($pdo, 
    "SELECT e.*, 
            l.name as location_name,
            l.city as location_city,
            COUNT(DISTINCT p.id) as participants_count
     FROM events e
     LEFT JOIN locations l ON e.location_id = l.id
     LEFT JOIN participants p ON e.id = p.event_id AND p.status IN ('confirmed', 'pending')
     WHERE e.creator_id = ?
     GROUP BY e.id
     ORDER BY e.event_date DESC, e.event_time DESC", 
    [getCurrentUserId()]
);

// Получение событий, на которые записан
$participatingEvents = fetchAll($pdo,
    "SELECT e.*, 
            p.status as my_status,
            p.joined_at,
            u.username as creator_name,
            l.name as location_name,
            l.city as location_city
     FROM participants p
     JOIN events e ON p.event_id = e.id
     LEFT JOIN users u ON e.creator_id = u.id
     LEFT JOIN locations l ON e.location_id = l.id
     WHERE p.user_id = ? AND p.status IN ('confirmed', 'pending')
     ORDER BY e.event_date ASC, e.event_time ASC",
    [getCurrentUserId()]
);

// История (завершённые и отменённые)
$historyEvents = fetchAll($pdo,
    "SELECT e.*, 
            p.status as my_status,
            u.username as creator_name,
            l.name as location_name,
            l.city as location_city,
            CASE 
                WHEN e.creator_id = ? THEN 'creator'
                ELSE 'participant'
            END as my_role
     FROM events e
     LEFT JOIN participants p ON e.id = p.event_id AND p.user_id = ?
     LEFT JOIN users u ON e.creator_id = u.id
     LEFT JOIN locations l ON e.location_id = l.id
     WHERE (e.creator_id = ? OR p.user_id = ?)
           AND (e.status IN ('completed', 'cancelled') OR p.status IN ('attended', 'missed', 'cancelled'))
     GROUP BY e.id
     ORDER BY e.event_date DESC, e.event_time DESC
     LIMIT 20",
    [getCurrentUserId(), getCurrentUserId(), getCurrentUserId(), getCurrentUserId()]
);

require_once 'includes/header.php';
?>

<!-- Заголовок -->
<div class="row mb-4">
    <div class="col-12">
        <h2>
            <i class="bi bi-calendar-check text-primary me-2"></i>
            Minu üritused
        </h2>
    </div>
</div>

<!-- Навигация по вкладкам -->
<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item">
        <a class="nav-link <?php echo $tab === 'created' ? 'active' : ''; ?>" 
           href="?tab=created">
            <i class="bi bi-calendar-plus me-2"></i>
            Loodud
            <span class="badge bg-primary ms-2"><?php echo count($createdEvents); ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $tab === 'participating' ? 'active' : ''; ?>" 
           href="?tab=participating">
            <i class="bi bi-person-check me-2"></i>
            Osalen
            <span class="badge bg-success ms-2"><?php echo count($participatingEvents); ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $tab === 'history' ? 'active' : ''; ?>" 
           href="?tab=history">
            <i class="bi bi-clock-history me-2"></i>
            Ajalugu
            <span class="badge bg-secondary ms-2"><?php echo count($historyEvents); ?></span>
        </a>
    </li>
</ul>

<!-- Содержимое вкладок -->
<?php if ($tab === 'created'): ?>
    <!-- Созданные события -->
    <div class="row">
        <div class="col-12">
            <?php if (empty($createdEvents)): ?>
                <div class="card text-center py-5">
                    <div class="card-body">
                        <i class="bi bi-calendar-x display-1 text-muted mb-3"></i>
                        <h4 class="text-muted">Sa pole veel ühtegi üritust loonud</h4>
                        <p class="text-muted mb-4">Loo oma esimene üritus ja kutsu sõpru!</p>
                        <a href="create_event.php" class="btn btn-primary btn-lg">
                            <i class="bi bi-plus-circle me-2"></i>Loo üritus
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($createdEvents as $event): ?>
                        <?php
                        $sportClass = ['Jalgpall' => 'sport-football', 'Võrkpall' => 'sport-volleyball', 'Korvpall' => 'sport-basketball'][$event['sport_type']] ?? 'bg-secondary';
                        $statusClass = ['Avatud' => 'badge-open', 'Suletud' => 'badge-closed', 'Lõpetatud' => 'badge-completed', 'Tühistatud' => 'badge-cancelled'][translateEventStatus($event['status'])] ?? 'bg-secondary';
                        $fillPercentage = ($event['current_participants'] / $event['max_participants']) * 100;
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card event-card shadow-sm h-100">
                                <span class="badge <?php echo $sportClass; ?> badge-sport">
                                    <?php echo clean($event['sport_type']); ?>
                                </span>
                                
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title mb-0">
                                            <a href="event.php?id=<?php echo $event['id']; ?>" class="text-decoration-none text-dark">
                                                <?php echo clean($event['title']); ?>
                                            </a>
                                        </h5>
                                        <span class="badge <?php echo $statusClass; ?>">
                                            <?php echo translateEventStatus($event['status']); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <i class="bi bi-calendar-event text-muted me-2"></i>
                                        <small><?php echo formatDateTime($event['event_date'], $event['event_time']); ?></small>
                                    </div>
                                    
                                    <?php if ($event['location_name']): ?>
                                        <div class="mb-2">
                                            <i class="bi bi-geo-alt text-muted me-2"></i>
                                            <small><?php echo clean($event['location_name']); ?></small>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <small class="text-muted">
                                                <i class="bi bi-people me-1"></i>
                                                <?php echo $event['current_participants']; ?> / <?php echo $event['max_participants']; ?>
                                            </small>
                                        </div>
                                        
                                        <div class="progress mb-3" style="height: 8px;">
                                            <div class="progress-bar" 
                                                 role="progressbar" 
                                                 style="width: <?php echo $fillPercentage; ?>%">
                                            </div>
                                        </div>
                                        
                                        <div class="d-grid gap-2">
                                            <a href="event.php?id=<?php echo $event['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="bi bi-eye me-1"></i>Halda
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php elseif ($tab === 'participating'): ?>
    <!-- События, на которые записан -->
    <div class="row">
        <div class="col-12">
            <?php if (empty($participatingEvents)): ?>
                <div class="card text-center py-5">
                    <div class="card-body">
                        <i class="bi bi-calendar-x display-1 text-muted mb-3"></i>
                        <h4 class="text-muted">Sa pole veel ühessegi üritusesse registreerunud</h4>
                        <p class="text-muted mb-4">Leia huvitav üritus ja liitu!</p>
                        <a href="index.php" class="btn btn-primary btn-lg">
                            <i class="bi bi-search me-2"></i>Leia üritusi
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($participatingEvents as $event): ?>
                        <?php
                        $sportClass = ['Jalgpall' => 'sport-football', 'Võrkpall' => 'sport-volleyball', 'Korvpall' => 'sport-basketball'][$event['sport_type']] ?? 'bg-secondary';
                        $statusBadge = ['Kinnitatud' => 'bg-success', 'Ootel' => 'bg-primary', 'Tühistatud' => 'bg-secondary'][translateParticipantStatus($event['my_status'])] ?? 'bg-secondary';
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card event-card shadow-sm h-100">
                                <span class="badge <?php echo $sportClass; ?> badge-sport">
                                    <?php echo clean($event['sport_type']); ?>
                                </span>
                                
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title mb-0">
                                            <a href="event.php?id=<?php echo $event['id']; ?>" class="text-decoration-none text-dark">
                                                <?php echo clean($event['title']); ?>
                                            </a>
                                        </h5>
                                        <span class="badge <?php echo $statusBadge; ?>">
                                            <?php echo translateParticipantStatus($event['my_status']); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <i class="bi bi-calendar-event text-muted me-2"></i>
                                        <small><?php echo formatDateTime($event['event_date'], $event['event_time']); ?></small>
                                    </div>
                                    
                                    <?php if ($event['location_name']): ?>
                                        <div class="mb-2">
                                            <i class="bi bi-geo-alt text-muted me-2"></i>
                                            <small><?php echo clean($event['location_name']); ?></small>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="mb-2">
                                        <i class="bi bi-person-badge text-muted me-2"></i>
                                        <small>Korraldaja: <?php echo clean($event['creator_name']); ?></small>
                                    </div>
                                    
                                    <div class="mt-auto">
                                        <div class="d-grid gap-2">
                                            <a href="event.php?id=<?php echo $event['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="bi bi-eye me-1"></i>Vaata lähemalt
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php else: ?>
    <!-- История -->
    <div class="row">
        <div class="col-12">
            <?php if (empty($historyEvents)): ?>
                <div class="card text-center py-5">
                    <div class="card-body">
                        <i class="bi bi-clock-history display-1 text-muted mb-3"></i>
                        <h4 class="text-muted">Ajalugu on tühi</h4>
                        <p class="text-muted">Siin kuvatakse lõppenud ja tühistatud üritused</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="list-group">
                    <?php foreach ($historyEvents as $event): ?>
                        <?php
                        $sportIcon = ['Футбол' => '⚽', 'Волейбол' => '🏐', 'Баскетбол' => '🏀'][$event['sport_type']] ?? '⚽';
                        $statusClass = ['Lõpetatud' => 'success', 'Tühistatud' => 'danger', 'Osales' => 'success', 'Ei tulnud' => 'danger', 'Tühistatud' => 'secondary'][translateEventStatus($event['status'])] ?? (isset($event['my_status']) ? ['Kinnitatud' => 'success', 'Tühistatud' => 'secondary'][translateParticipantStatus($event['my_status'])] ?? 'secondary' : 'secondary');
                        $roleText = $event['my_role'] === 'creator' ? 'Korraldaja' : 'Osaleja';
                        ?>
                        <a href="event.php?id=<?php echo $event['id']; ?>" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <h6 class="mb-0 me-3">
                                            <?php echo $sportIcon; ?>
                                            <?php echo clean($event['title']); ?>
                                        </h6>
                                        <span class="badge bg-<?php echo $statusClass; ?>">
                                            <?php echo translateEventStatus($event['status']); ?>
                                        </span>
                                        <?php if ($event['my_status']): ?>
                                            <span class="badge bg-info ms-2">
                                                <?php echo translateParticipantStatus($event['my_status']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex gap-3 flex-wrap">
                                        <small class="text-muted">
                                            <i class="bi bi-calendar me-1"></i>
                                            <?php echo formatDate($event['event_date']); ?>
                                        </small>
                                        <?php if ($event['location_city']): ?>
                                            <small class="text-muted">
                                                <i class="bi bi-geo-alt me-1"></i>
                                                <?php echo clean($event['location_city']); ?>
                                            </small>
                                        <?php endif; ?>
                                        <small class="text-muted">
                                            <i class="bi bi-person me-1"></i>
                                            <?php echo $roleText; ?>
                                        </small>
                                    </div>
                                </div>
                                <i class="bi bi-chevron-right text-muted"></i>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
