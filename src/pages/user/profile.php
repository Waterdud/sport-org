<?php
/**
 * Профиль пользователя - Profiil
 */

require_once dirname(__DIR__, 3) . '/src/config/bootstrap.php';
require_once BASE_PATH . '/src/services/AnalyticsService.php';
require_once BASE_PATH . '/src/services/FollowService.php';
requireAuth();

$pageTitle = 'Profiil';
$user = getCurrentUser();

// Use analytics service for stats
$analyticsService = new AnalyticsService($pdo);
$stats = $analyticsService->getUserStats($user['id']);

$totalEvents = $stats['games_organized'];
$joinedCount = $stats['games_attended'];
$userRating = $user['reliability_rating'] ?? 5.0;

// Get user's recent events
$recentEvents = fetchAll($pdo, 
    "SELECT e.* FROM events e WHERE e.creator_id = ? ORDER BY e.event_date DESC LIMIT 5",
    [$user['id']]
);

require_once BASE_PATH . '/src/components/Header.php';
?>

<div class="container py-4">
    <!-- Профиль пользователя -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div style="width: 120px; height: 120px; margin: 0 auto; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 48px; font-weight: bold;">
                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                    </div>
                    <h3 class="mt-3 mb-1"><?php echo clean($user['username']); ?></h3>
                    <p class="text-muted"><?php echo clean($user['email']); ?></p>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                        <i class="bi bi-pencil me-1"></i>Muuda profiili
                    </button>
                </div>
            </div>
        </div>

        <!-- Статистика -->
        <div class="col-md-8">
            <div class="row g-3">
                <!-- Созданные события -->
                <div class="col-md-6">
                    <div class="card bg-primary bg-opacity-10 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="text-muted mb-0">Loodud üritused</h6>
                                    <h3 class="mb-0"><?php echo $totalEvents; ?></h3>
                                </div>
                                <div style="font-size: 40px; color: #667eea;">
                                    <i class="bi bi-calendar-plus"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Присоединённые события -->
                <div class="col-md-6">
                    <div class="card bg-success bg-opacity-10 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="text-muted mb-0">Osalused üritused</h6>
                                    <h3 class="mb-0"><?php echo $joinedCount; ?></h3>
                                </div>
                                <div style="font-size: 40px; color: #28a745;">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Рейтинг -->
                <div class="col-md-12">
                    <div class="card bg-warning bg-opacity-10 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="text-muted mb-0">Reiting</h6>
                                    <div class="mt-2">
                                        <?php 
                                        $rating = $userRating;
                                        for ($i = 0; $i < 5; $i++):
                                            if ($i < round($rating)): ?>
                                                <i class="bi bi-star-fill" style="color: #ffc107;"></i>
                                            <?php else: ?>
                                                <i class="bi bi-star" style="color: #ffc107;"></i>
                                            <?php endif;
                                        endfor;
                                        ?>
                                    </div>
                                </div>
                                <div style="font-size: 32px; color: #ffc107;">
                                    <strong><?php echo number_format($rating, 1); ?>/5.0</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Недавние события пользователя -->
    <?php if (!empty($recentEvents)): ?>
    <div class="row">
        <div class="col-md-12">
            <h5 class="mb-3">
                <i class="bi bi-clock-history me-2"></i>Viimased üritused
            </h5>
            <div class="row g-3">
                <?php foreach ($recentEvents as $event): ?>
                    <div class="col-md-6">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="card-title mb-0"><?php echo clean($event['title']); ?></h6>
                                    <span class="badge bg-info"><?php echo clean($event['sport_type']); ?></span>
                                </div>
                                <p class="text-muted small mb-2">
                                    <i class="bi bi-calendar me-1"></i><?php echo formatDateEt($event['event_date']); ?> 
                                    <i class="bi bi-clock ms-2 me-1"></i><?php echo $event['event_time']; ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="bi bi-people me-1"></i>
                                        Osalejaid
                                    </small>
                                    <span class="badge bg-secondary"><?php echo $event['max_participants']; ?></span>
                                </div>
                                <div class="mt-3">
                                    <a href="/events/view?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>Vaata
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="return deleteEvent(<?php echo (int)$event['id']; ?>)">
                                        <i class="bi bi-trash me-1"></i>Kustuta
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Модаль редактирования профиля -->
<div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Muuda profiili</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    Muudatused hakkavad kehtima peale lehe uuesti laadimist.
                </div>
                <p><strong>Kasutajanimi:</strong> <?php echo clean($user['username']); ?></p>
                <p><strong>Email:</strong> <?php echo clean($user['email']); ?></p>
                <p class="text-muted small mb-0">Profiilit saab muuta administraatoriga võttes ühendust.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Sulge</button>
            </div>
        </div>
    </div>
</div>

<script>
function deleteEvent(eventId) {
    console.log('deleteEvent called with ID:', eventId);
    
    const message = 'Kas KINDEL oled, et soovid selle üritus KUSTUTADA?\n\nSeda ei saa tagasi võtta!';
    const userConfirmed = confirm(message);
    
    console.log('User confirmed:', userConfirmed);
    
    if (!userConfirmed) {
        console.log('Cancelled by user');
        return false;
    }
    
    console.log('Confirmed, deleting event:', eventId);
    
    const data = new FormData();
    data.append('action', 'delete');
    data.append('event_id', eventId);
    
    fetch('<?php echo SITE_URL; ?>/src/ajax/delete_event.php', {
        method: 'POST',
        body: data
    })
    .then(r => {
        console.log('Status:', r.status);
        return r.text(); // Read as text first
    })
    .then(text => {
        console.log('Raw response:', text);
        try {
            const d = JSON.parse(text);
            console.log('Parsed response:', d);
            if (d.ok) {
                alert('✓ Üritus kustutatud!');
                location.reload();
            } else {
                alert('✗ Viga: ' + d.error);
            }
        } catch (e) {
            console.error('JSON parse error:', e);
            alert('Serveri viga: ' + text);
        }
    })
    .catch(e => {
        console.error('Fetch error:', e);
        alert('Viga: ' + e);
    });
    
    return false;
}
</script>

<?php require_once BASE_PATH . '/src/components/Footer.php'; ?>
