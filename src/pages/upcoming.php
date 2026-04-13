<?php
/**
 * Мої предстоящие события
 * 
 * Показывает события, на которые пользователь записался
 * с возможностью установки напоминаний
 */

require_once dirname(__DIR__, 2) . '/src/config/bootstrap.php';
requireAuth();

$pageTitle = 'Minu tulevased treeningud';

// Получить предстоящие события пользователя
$reminderService = new ReminderService($pdo);
$upcoming = $reminderService->getUpcomingEventsForUser(getCurrentUserId(), 30);

require_once BASE_PATH . '/src/components/Header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="mb-1">
                    <i class="bi bi-calendar-check text-primary me-2"></i>
                    Minu tulevased treeningud
                </h1>
                <p class="text-muted mb-0">Halluta oma kalendrit ja meeldetuletus</p>
            </div>
            <a href="/events" class="btn btn-outline-primary">
                <i class="bi bi-search me-1"></i>Otsi treeninguid
            </a>
        </div>
    </div>
</div>

<?php if (empty($upcoming)): ?>
    <div class="card">
        <div class="empty-state py-5">
            <i class="bi bi-calendar-plus empty-state-icon"></i>
            <h4 class="empty-state-title">Tulevasi treeningud puuduvad</h4>
            <p class="empty-state-text">Liitu mõne treininguga, et näha neid siin</p>
            <a href="/events" class="btn btn-primary">
                <i class="bi bi-search me-1"></i>Vaata saadavalolevaid treeninguid
            </a>
        </div>
    </div>
<?php else: ?>
    <!-- Statistika -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-calendar-event text-primary" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-1"><?php echo count($upcoming); ?></h3>
                    <p class="text-muted mb-0">Tulevasi treeninguid</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-bell text-success" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-1" id="reminderCount">0</h3>
                    <p class="text-muted mb-0">Meeldetuletus seadistatud</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-hourglass-split text-info" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-1" id="daysUntilFirst">-</h3>
                    <p class="text-muted mb-0">Päeva esimeseni</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card">
                <div class="card-body text-center">
                    <i class="bi bi-bar-chart text-warning" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-1"><?php echo count(array_unique(array_map(function($e) { return $e['sport_type']; }, $upcoming))); ?></h3>
                    <p class="text-muted mb-0">Erinevaid spordialasid</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Список событий -->
    <div class="row g-3">
        <?php foreach ($upcoming as $event): 
            $daysUntil = (new DateTime($event['event_date']))->diff(new DateTime())->days;
        ?>
            <div class="col-12 col-lg-6 fade-in">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="card-title mb-1"><?php echo clean($event['title']); ?></h5>
                                <span class="badge badge-primary"><?php echo clean($event['sport_type']); ?></span>
                                <span class="badge badge-<?php echo $daysUntil <= 1 ? 'danger' : ($daysUntil <= 3 ? 'warning' : 'info'); ?>">
                                    <?php echo $daysUntil == 0 ? 'Täna' : ($daysUntil == 1 ? 'Homme' : $daysUntil . ' päevad'); ?>
                                </span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <p class="text-muted mb-2">
                                <i class="bi bi-calendar3"></i>
                                <strong><?php echo formatDateEt($event['event_date']); ?></strong>
                            </p>
                            <p class="text-muted mb-2">
                                <i class="bi bi-clock"></i>
                                <strong><?php echo formatTimeEt($event['event_time']); ?></strong>
                            </p>
                            <p class="text-muted">
                                <i class="bi bi-geo-alt"></i>
                                <?php echo clean($event['location_name'] ?? 'Koht määramata'); ?>
                                <?php echo $event['city'] ? '(' . clean($event['city']) . ')' : ''; ?>
                            </p>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="<?php echo SITE_URL; ?>/src/pages/events/view.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-primary flex-grow-1">
                                <i class="bi bi-eye me-1"></i>Vaata üritust
                            </a>
                            <button class="btn btn-sm btn-outline-success reminder-btn" data-event-id="<?php echo $event['id']; ?>">
                                <i class="bi bi-bell"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
// Получить предстоящие события с напоминаниями
fetch('<?php echo SITE_URL; ?>/src/ajax/reminders.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'action=upcoming'
})
.then(r => r.json())
.then(data => {
    if (data.ok && data.events.length > 0) {
        // Обновить количество дней до первого события
        const firstDate = new Date(data.events[0].event_date);
        const today = new Date();
        const diff = Math.ceil((firstDate - today) / (1000 * 60 * 60 * 24));
        document.getElementById('daysUntilFirst').textContent = diff >= 0 ? diff : '0';
    }
});

// Управление напоминаниями через кнопки
document.querySelectorAll('.reminder-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const eventId = this.dataset.eventId;
        const formData = new FormData();
        formData.append('action', 'create');
        formData.append('event_id', eventId);
        
        fetch('<?php echo SITE_URL; ?>/src/ajax/reminders.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                this.classList.add('active');
                this.innerHTML = '<i class="bi bi-bell-fill"></i>';
                alert('Meeldetuletus seadistatud!');
            } else {
                alert('Viga: ' + (data.error || 'Tundmatu viga'));
            }
        });
    });
});
</script>

<?php require_once BASE_PATH . '/src/components/Footer.php'; ?>
