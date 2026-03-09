<?php
/**
 * Создание события - Loo üritus
 */

require_once dirname(__DIR__, 3) . '/config/bootstrap.php';
requireAuth();

$pageTitle = 'Loo üritus';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $locationId = $_POST['location_id'] ?? null;
    $sportType = $_POST['sport_type'] ?? '';
    $eventDate = $_POST['event_date'] ?? '';
    $eventTime = $_POST['event_time'] ?? '';
    $maxParticipants = (int)($_POST['max_participants'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $skillLevel = $_POST['skill_level'] ?? 'Любитель';
    
    // Валидация
    if (empty($title)) $errors[] = 'Pealkiri on kohustuslik';
    if (empty($eventDate)) $errors[] = 'Kuupäev on kohustuslik';
    if (empty($eventTime)) $errors[] = 'Kellaaeg on kohustuslik';
    if ($maxParticipants < 2) $errors[] = 'Minimaalne osalejaate arv on 2';
    
    if (empty($errors)) {
        execute($pdo,
            "INSERT INTO events (creator_id, title, sport_type, location_id, event_date, event_time, max_participants, skill_level, description, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Открыто')",
            [getCurrentUserId(), $title, $sportType, $locationId, $eventDate, $eventTime, $maxParticipants, $skillLevel, $description]
        );
        redirect('list.php');
    }
}

// Получение мест
$locations = fetchAll($pdo, "SELECT id, name, city FROM locations ORDER BY name");

require_once BASE_PATH . '/src/components/Header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <h2>
            <i class="bi bi-plus-circle me-2"></i>Loo üritus
        </h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo $error; ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Pealkiri</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Kuupäev</label>
                    <input type="date" name="event_date" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Kellaaeg</label>
                    <input type="time" name="event_time" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Spordialad</label>
                    <select name="sport_type" class="form-select" required>
                        <option value="Футбол">⚽ Jalgpall</option>
                        <option value="Волейбол">🏐 Võrkpall</option>
                        <option value="Баскетбол">🏀 Korvpall</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Maksimaalne osalejaate arv</label>
                    <input type="number" name="max_participants" class="form-control" value="10" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Loo üritus</button>
            </div>
        </form>
    </div>
</div>

<?php require_once BASE_PATH . '/src/components/Footer.php'; ?>
