<?php
/**
 * Просмотр события - Ürituse kuvamine
 */

require_once dirname(__DIR__, 3) . '/src/config/bootstrap.php';
require_once BASE_PATH . '/src/services/ParticipationService.php';
require_once BASE_PATH . '/src/services/GameStatusService.php';

$pageTitle = 'Ürituse vaatamine';

$id = (int)($_GET['id'] ?? 0);
if ($id === 0) redirect('/events');

$event = fetchOne($pdo, 
    "SELECT e.*, u.username, l.name as location_name 
     FROM events e 
     JOIN users u ON e.creator_id = u.id 
     LEFT JOIN locations l ON e.location_id = l.id 
     WHERE e.id = ?", 
    [$id]);

if (!$event) redirect('/events');

// Initialize GameStatusService to get correct display status
$gameStatusService = new GameStatusService($pdo);
$displayStatus = $gameStatusService->getDisplayStatus($event);

// RSVP status colors
$statusColors = ['planned' => 'primary', 'full' => 'danger', 'ongoing' => 'success', 'finished' => 'secondary', 'cancelled' => 'dark'];

// Get user's RSVP status if logged in
$userRsvp = null;
if (isLoggedIn()) {
    $participationService = new ParticipationService($pdo);
    $userRsvp = $participationService->getUserRsvp($event['id'], getCurrentUserId());
    $participants = $participationService->getGameParticipants($event['id']);
} else {
    $participants = [];
}

require_once BASE_PATH . '/src/components/Header.php';
?>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h1><?php echo clean($event['title']); ?></h1>
                        <span class="badge bg-<?php echo $statusColors[$displayStatus] ?? 'secondary'; ?>">
                            <?php echo ucfirst($displayStatus); ?>
                        </span>
                    </div>
                </div>
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

        <!-- RSVP Section -->
        <?php if (isLoggedIn()): ?>
        <div class="card mb-4">
            <div class="card-body">
                <h5>Your Response</h5>
                <?php if (!$userRsvp): ?>
                <div class="btn-group">
                    <button class="btn btn-success btn-sm" onclick="joinGame('going')">Going</button>
                    <button class="btn btn-warning btn-sm" onclick="joinGame('maybe')">Maybe</button>
                    <button class="btn btn-secondary btn-sm" onclick="joinGame('not_going')">Not Going</button>
                </div>
                <?php else: ?>
                <p>Status: <strong><?php echo ucfirst($userRsvp['rsvp_status']); ?></strong></p>
                <div class="btn-group">
                    <button class="btn btn-info btn-sm" onclick="updateRsvpStatus('going')">Going</button>
                    <button class="btn btn-warning btn-sm" onclick="updateRsvpStatus('maybe')">Maybe</button>
                    <button class="btn btn-secondary btn-sm" onclick="updateRsvpStatus('not_going')">Not Going</button>
                </div>
                <button class="btn btn-danger btn-sm ms-2" onclick="leaveGame()">Leave</button>
                <?php endif; ?>
                
                <!-- Delete Button for Creator -->
                <?php if ((int)$event['creator_id'] === getCurrentUserId()): ?>
                <hr>
                <p class="text-muted mb-2"><small>Organisaatori valikud:</small></p>
                <button class="btn btn-danger" style="width: 100%;" onclick="deleteEvent(<?php echo $event['id']; ?>)">
                    <i class="bi bi-trash me-2"></i>Kustuta see üritus
                </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Participants Section -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Participants (<?php echo count($participants); ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($participants)): ?>
                    <?php foreach ($participants as $p): ?>
                    <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
                        <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 16px; font-weight: bold; flex-shrink: 0;">
                            <?php echo strtoupper(substr($p['username'], 0, 1)); ?>
                        </div>
                        <div class="flex-grow-1 ms-2">
                            <strong><?php echo clean($p['username']); ?></strong>
                            <span class="badge bg-info ms-2"><?php echo ucfirst($p['rsvp_status']); ?></span>
                            <small class="text-muted ms-2">⭐ <?php echo $p['reliability_rating']; ?>/5</small>
                        </div>
                        <?php if (isLoggedIn() && $displayStatus === 'finished'): ?>
                        <button class="btn btn-sm btn-outline-warning" 
                                onclick="openRatingModal(<?php echo $p['id']; ?>, '<?php echo clean($p['username']); ?>', <?php echo $event['id']; ?>)">
                            <i class="bi bi-star me-1"></i>Hindama
                        </button>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                <p class="text-muted">No participants yet</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function joinGame(status) {
    fetch('<?php echo SITE_URL; ?>/src/ajax/rsvp.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=join&event_id=<?php echo $event['id']; ?>&status=' + status
    })
    .then(r => r.json())
    .then(d => d.ok ? location.reload() : alert('Error: ' + d.error));
}

function updateRsvpStatus(status) {
    fetch('<?php echo SITE_URL; ?>/src/ajax/rsvp.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=update&event_id=<?php echo $event['id']; ?>&status=' + status
    }).then(r => r.json()).then(d => d.ok ? location.reload() : alert('Error: ' + d.error));
}

function leaveGame() {
    if (confirm('Leave this game?')) fetch('<?php echo SITE_URL; ?>/src/ajax/rsvp.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=leave&event_id=<?php echo $event['id']; ?>'
    }).then(r => r.json()).then(d => d.ok ? location.reload() : alert('Error: ' + d.error));
}

function openRatingModal(userId, userName, eventId) {
    document.getElementById('ratedUserName').textContent = userName;
    document.getElementById('ratedUserId').value = userId;
    document.getElementById('eventId').value = eventId;
    new bootstrap.Modal(document.getElementById('ratingModal')).show();
}

function submitRating() {
    const userId = document.getElementById('ratedUserId').value;
    const eventId = document.getElementById('eventId').value;
    const attendance = document.getElementById('attendanceRating').value;
    const cooperation = document.getElementById('cooperationRating').value;
    const sportsmanship = document.getElementById('sportsmanshipRating').value;
    const comment = document.getElementById('ratingComment').value;
    
    const data = new FormData();
    data.append('action', 'submit');
    data.append('user_id', userId);
    data.append('event_id', eventId);
    data.append('attendance', attendance);
    data.append('cooperation', cooperation);
    data.append('sportsmanship', sportsmanship);
    data.append('comment', comment);
    
    fetch('<?php echo SITE_URL; ?>/src/ajax/ratings.php', {
        method: 'POST',
        body: data
    })
    .then(r => r.json())
    .then(d => {
        if (d.ok) {
            alert('✓ Hindamine salvestatud!');
            bootstrap.Modal.getInstance(document.getElementById('ratingModal')).hide();
            location.reload();
        } else {
            alert('✗ Viga: ' + d.msg);
        }
    })
    .catch(e => alert('Viga: ' + e));
}
</script>

<!-- Rating Modal -->
<div class="modal fade" id="ratingModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-star me-2"></i>
                    Hindama kasutajat: <strong id="ratedUserName"></strong>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="ratedUserId">
                <input type="hidden" id="eventId">
                
                <!-- Attendance Rating -->
                <div class="mb-4">
                    <label class="form-label fw-bold">
                        <i class="bi bi-clock me-2"></i>Kohalviimine
                    </label>
                    <div class="rating-stars">
                        <select class="form-select" id="attendanceRating">
                            <option value="">Vali hindamine...</option>
                            <option value="1">⭐ Väga halb - hiljem tuli</option>
                            <option value="2">⭐⭐ Halb - veidi hiljem</option>
                            <option value="3">⭐⭐⭐ Rahuldav - õigeaegselt</option>
                            <option value="4">⭐⭐⭐⭐ Hea - vara tuli</option>
                            <option value="5">⭐⭐⭐⭐⭐ Suurepärane - väga vara</option>
                        </select>
                    </div>
                </div>
                
                <!-- Cooperation Rating -->
                <div class="mb-4">
                    <label class="form-label fw-bold">
                        <i class="bi bi-people me-2"></i>Koostöö ja meeskonnatöö
                    </label>
                    <select class="form-select" id="cooperationRating">
                        <option value="">Vali hindamine...</option>
                        <option value="1">⭐ Väga halb - koostöö puudub</option>
                        <option value="2">⭐⭐ Halb - vähene koostöö</option>
                        <option value="3">⭐⭐⭐ Rahuldav - rahuldav koostöö</option>
                        <option value="4">⭐⭐⭐⭐ Hea - hea meeskonnamängija</option>
                        <option value="5">⭐⭐⭐⭐⭐ Suurepärane - suurepärane koostöö</option>
                    </select>
                </div>
                
                <!-- Sportsmanship Rating -->
                <div class="mb-4">
                    <label class="form-label fw-bold">
                        <i class="bi bi-heart me-2"></i>Spordimeisterlikkus
                    </label>
                    <select class="form-select" id="sportsmanshipRating">
                        <option value="">Vali hindamine...</option>
                        <option value="1">⭐ Väga halb - ebaspordimeelik käitumine</option>
                        <option value="2">⭐⭐ Halb - kohati ebaspordimeelik</option>
                        <option value="3">⭐⭐⭐ Rahuldav - neutraalne käitumine</option>
                        <option value="4">⭐⭐⭐⭐ Hea - spordimeelik käitumine</option>
                        <option value="5">⭐⭐⭐⭐⭐ Suurepärane - eetiline mängija</option>
                    </select>
                </div>
                
                <!-- Comment -->
                <div class="mb-3">
                    <label class="form-label fw-bold">
                        <i class="bi bi-chat me-2"></i>Kommentaar (valikuline)
                    </label>
                    <textarea class="form-control" id="ratingComment" rows="3" placeholder="Kirjuta kommentaar..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Sulge</button>
                <button type="button" class="btn btn-success" onclick="submitRating()">
                    <i class="bi bi-check-circle me-2"></i>Salvesta hindamine
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . '/src/components/Footer.php'; ?>
