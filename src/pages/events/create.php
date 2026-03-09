<?php
/**
 * Создание события - Loo üritus
 */

require_once dirname(__DIR__, 3) . '/src/config/bootstrap.php';
requireAuth();

$pageTitle = 'Loo üritus';

$errors = [];
$success = false;

// Обработка создания нового места
if (isset($_POST['create_location'])) {
    $locationName = trim($_POST['location_name'] ?? '');
    $locationAddress = trim($_POST['location_address'] ?? '');
    $locationCity = trim($_POST['location_city'] ?? '');
    
    if (!empty($locationName) && !empty($locationAddress) && !empty($locationCity)) {
        execute($pdo,
            "INSERT INTO locations (name, address, city) VALUES (?, ?, ?)",
            [$locationName, $locationAddress, $locationCity]
        );
        // Перезагружаем список мест
    } else {
        $errors[] = 'Kõik väljad on kohustuslikud';
    }
}

// Обработка создания события
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['create_location'])) {
    $title = trim($_POST['title'] ?? '');
    $locationId = $_POST['location_id'] ?? null;
    $sportType = $_POST['sport_type'] ?? '';
    $eventDate = $_POST['event_date'] ?? '';
    $eventTime = $_POST['event_time'] ?? '';
    $maxParticipants = (int)($_POST['max_participants'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $skillLevel = $_POST['skill_level'] ?? 'Algaja';
    
    // Валидация
    if (empty($title)) $errors[] = 'Pealkiri on kohustuslik';
    if (empty($eventDate)) $errors[] = 'Kuupäev on kohustuslik';
    if (empty($eventTime)) $errors[] = 'Kellaaeg on kohustuslik';
    if ($maxParticipants < 2) $errors[] = 'Minimaalne osalejaate arv on 2';
    if (empty($sportType)) $errors[] = 'Spordialad on kohustuslik';
    
    if (empty($errors)) {
        execute($pdo,
            "INSERT INTO events (creator_id, title, sport_type, location_id, event_date, event_time, max_participants, skill_level, description, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Avatud')",
            [getCurrentUserId(), $title, $sportType, $locationId, $eventDate, $eventTime, $maxParticipants, $skillLevel, $description]
        );
        redirect('/events');
    }
}

// Получение мест
$locations = fetchAll($pdo, "SELECT id, name, city FROM locations ORDER BY name");

require_once BASE_PATH . '/src/components/Header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h2 class="mb-4">
                <i class="bi bi-plus-circle me-2"></i>Loo uus üritus
            </h2>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    <strong>Viga!</strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo clean($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" novalidate>
                        <!-- Pealkiri -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-pencil me-1"></i>Üritus pealkiri <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="title" class="form-control form-control-lg" 
                                   placeholder="nt. Jalgpalli mäng pargi staadionil" required>
                            <small class="form-text text-muted">Lühike ja kirjeldav pealkiri</small>
                        </div>

                        <!-- Spordialad -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-suitcase me-1"></i>Spordialad <span class="text-danger">*</span>
                            </label>
                            <select name="sport_type" class="form-select form-select-lg" required>
                                <option value="">Vali spordialad...</option>
                                <option value="Jalgpall">⚽ Jalgpall</option>
                                <option value="Võrkpall">🏐 Võrkpall</option>
                                <option value="Korvpall">🏀 Korvpall</option>
                                <option value="Tennis">🎾 Tennis</option>
                                <option value="Ujumine">🏊 Ujumine</option>
                            </select>
                        </div>

                        <!-- Koht -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-geo-alt me-1"></i>Koht
                            </label>
                            <select name="location_id" class="form-select form-select-lg">
                                <option value="">Vali olemasolev koht...</option>
                                <?php foreach ($locations as $loc): ?>
                                    <option value="<?php echo $loc['id']; ?>">
                                        <?php echo clean($loc['name']); ?> (<?php echo clean($loc['city']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Koha saad valida või luua uue</small>
                        </div>

                        <!-- Kuupäev ja kellaaeg -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-calendar me-1"></i>Kuupäev <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="event_date" class="form-control form-control-lg" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-clock me-1"></i>Kellaaeg <span class="text-danger">*</span>
                                </label>
                                <input type="time" name="event_time" class="form-control form-control-lg" required>
                            </div>
                        </div>

                        <!-- Osalejaate arv ja taseme -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-people me-1"></i>Maksimaalne osalejaate arv <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="max_participants" class="form-control form-control-lg" 
                                       value="10" min="2" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-award me-1"></i>Taseme
                                </label>
                                <select name="skill_level" class="form-select form-select-lg">
                                    <option value="Algaja">🟢 Algaja</option>
                                    <option value="Keskmine" selected>🟡 Keskmine</option>
                                    <option value="Areneb">🔵 Areneb</option>
                                    <option value="Professionaal">🔴 Professionaal</option>
                                </select>
                            </div>
                        </div>

                        <!-- Kirjeldus -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="bi bi-chat me-1"></i>Kirjeldus
                            </label>
                            <textarea name="description" class="form-control" rows="3" 
                                      placeholder="Lisa ürituse täpsem kirjeldus..."></textarea>
                        </div>

                        <!-- Nupud -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle me-2"></i>Loo üritus
                            </button>
                            <a href="/events" class="btn btn-outline-secondary btn-lg">
                                <i class="bi bi-x-circle me-2"></i>Tühista
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Nupp uue koha loomiseks -->
            <div class="text-center mt-4">
                <button class="btn btn-sm btn-outline-info" data-bs-toggle="offcanvas" data-bs-target="#newLocationCanvas">
                    <i class="bi bi-plus-circle me-1"></i>Loo uus koht
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Offcanvas: Loo uus koht -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="newLocationCanvas">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">
            <i class="bi bi-geo-alt me-2"></i>Lisa uus koht
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <form method="POST">
            <input type="hidden" name="create_location" value="1">
            
            <div class="mb-3">
                <label class="form-label fw-bold">Koha nimi <span class="text-danger">*</span></label>
                <input type="text" name="location_name" class="form-control" 
                       placeholder="nt. Tallinna Linnastaadion" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Aadress <span class="text-danger">*</span></label>
                <input type="text" name="location_address" class="form-control" 
                       placeholder="nt. Jakobi 2" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Linn <span class="text-danger">*</span></label>
                <select name="location_city" class="form-select" required>
                    <option value="">Vali linn...</option>
                    <option value="Tallinn">Tallinn</option>
                    <option value="Tartu">Tartu</option>
                    <option value="Pärnu">Pärnu</option>
                    <option value="Narva">Narva</option>
                    <option value="Rakvere">Rakvere</option>
                </select>
            </div>

            <button type="submit" class="btn btn-success w-100">
                <i class="bi bi-check-circle me-2"></i>Salvesta
            </button>
        </form>
    </div>
</div>

<?php require_once BASE_PATH . '/src/components/Footer.php'; ?>
