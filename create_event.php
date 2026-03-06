<?php
/**
 * Создание нового спортивного события
 * 
 * Функционал:
 * - Создание события с полной информацией
 * - Выбор локации из существующих
 * - Валидация всех полей
 * - Проверка даты (только будущие события)
 */

require_once 'includes/db.php';
require_once 'includes/functions.php';

// Требуем авторизацию
requireAuth();

$pageTitle = 'Loo üritus';

$errors = [];
$formData = [];

// Получение списка локаций
$locations = fetchAll($pdo, "SELECT * FROM locations ORDER BY city, name");

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Проверка CSRF токена
    if (!verifyCsrfToken()) {
        $errors[] = 'Turvaline viga. Proovige uuesti.';
    } else {
        
        // Получение данных
        $title = trim($_POST['title'] ?? '');
        $sportType = $_POST['sport_type'] ?? '';
        $locationId = $_POST['location_id'] ?? '';
        $eventDate = $_POST['event_date'] ?? '';
        $eventTime = $_POST['event_time'] ?? '';
        $duration = (int)($_POST['duration'] ?? 120);
        $maxParticipants = (int)($_POST['max_participants'] ?? 0);
        $skillLevel = $_POST['skill_level'] ?? '';
        $description = trim($_POST['description'] ?? '');
        
        // Сохраняем для заполнения формы при ошибке
        $formData = compact('title', 'sportType', 'locationId', 'eventDate', 'eventTime', 
                           'duration', 'maxParticipants', 'skillLevel', 'description');
        
        // Валидация
        if (empty($title)) {
            $errors[] = 'Ürituse pealkiri on kohustuslik';
        } elseif (strlen($title) < 5) {
            $errors[] = 'Pealkiri peab sisaldama vähemalt 5 tähemärki';
        }
        
        if (empty($sportType) || !in_array($sportType, ['Jalgpall', 'Võrkpall', 'Korvpall'])) {
            $errors[] = 'Vali spordiala';
        }
        
        if (empty($locationId)) {
            $errors[] = 'Vali toimumiskoht';
        }
        
        if (empty($eventDate)) {
            $errors[] = 'Sisesta ürituse kuupäev';
        } else {
            $selectedDate = new DateTime($eventDate . ' ' . $eventTime);
            $now = new DateTime();
            if ($selectedDate <= $now) {
                $errors[] = 'Ürituse kuupäev ja kellaaeg peavad olema tulevikus';
            }
        }
        
        if (empty($eventTime)) {
            $errors[] = 'Sisesta ürituse kellaaeg';
        }
        
        if ($maxParticipants < 2) {
            $errors[] = 'Minimaalne osalejate arv - 2';
        } elseif ($maxParticipants > 100) {
            $errors[] = 'Maksimaalne osalejate arv - 100';
        }
        
        if (empty($skillLevel) || !in_array($skillLevel, ['Algaja', 'Harrastaja', 'Edasijõudnu', 'Professionaal'])) {
            $errors[] = 'Vali mängu tase';
        }
        
        if ($duration < 30 || $duration > 480) {
            $errors[] = 'Kestvus peab olema vahemikus 30 kuni 480 minutit';
        }
        
        // Если нет ошибок, создаём событие
        if (empty($errors)) {
            $sql = "INSERT INTO events (
                        creator_id, title, sport_type, location_id, 
                        event_date, event_time, duration, max_participants, 
                        skill_level, description, status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')";
            
            $eventId = insert($pdo, $sql, [
                getCurrentUserId(),
                $title,
                $sportType,
                $locationId,
                $eventDate,
                $eventTime,
                $duration,
                $maxParticipants,
                $skillLevel,
                $description
            ]);
            
            if ($eventId) {
                // Автоматически записываем создателя как участника
                $participantSql = "INSERT INTO participants (event_id, user_id, status) 
                                   VALUES (?, ?, 'confirmed')";
                execute($pdo, $participantSql, [$eventId, getCurrentUserId()]);
                
                // Обновляем статистику пользователя
                execute($pdo, "UPDATE users SET total_events = total_events + 1 WHERE id = ?", 
                       [getCurrentUserId()]);
                
                setFlashMessage('success', 'Üritus on edukalt loodud!');
                redirect('event.php?id=' . $eventId);
            } else {
                $errors[] = 'Viga ürituse loomisel. Proovige hiljem uuesti.';
            }
        }
    }
}

require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h2 class="card-title mb-4">
                    <i class="bi bi-plus-circle text-primary me-2"></i>
                    Loo uus üritus
                </h2>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <strong>Vead:</strong>
                        <ul class="mb-0 mt-2">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo clean($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="create_event.php" class="needs-validation" novalidate>
                    <?php echo csrfField(); ?>
                    
                    <!-- Название события -->
                    <div class="mb-3">
                        <label for="title" class="form-label">
                            <i class="bi bi-pencil me-1"></i>
                            Ürituse pealkiri <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="title" 
                               name="title" 
                               value="<?php echo clean($formData['title'] ?? ''); ?>"
                               placeholder="Näiteks: Jalgpall Kadrioru pargis"
                               required
                               minlength="5"
                               maxlength="200">
                        <div class="form-text">Vähemalt 5 tähemärki</div>
                    </div>
                    
                    <!-- Вид спорта -->
                    <div class="mb-3">
                        <label for="sport_type" class="form-label">
                            <i class="bi bi-dribbble me-1"></i>
                            Spordiala <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="sport_type" name="sport_type" required>
                            <option value="">Vali spordiala</option>
                            <option value="Jalgpall" <?php echo ($formData['sportType'] ?? '') === 'Jalgpall' ? 'selected' : ''; ?>>
                                ⚽ Jalgpall
                            </option>
                            <option value="Võrkpall" <?php echo ($formData['sportType'] ?? '') === 'Võrkpall' ? 'selected' : ''; ?>>
                                🏐 Võrkpall
                            </option>
                            <option value="Korvpall" <?php echo ($formData['sportType'] ?? '') === 'Korvpall' ? 'selected' : ''; ?>>
                                🏀 Korvpall
                            </option>
                        </select>
                    </div>
                    
                    <!-- Место проведения -->
                    <div class="mb-3">
                        <label for="location_id" class="form-label">
                            <i class="bi bi-geo-alt me-1"></i>
                            Toimumiskoht <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="location_id" name="location_id" required>
                            <option value="">Vali koht</option>
                            <?php foreach ($locations as $location): ?>
                                <option value="<?php echo $location['id']; ?>"
                                        <?php echo ($formData['locationId'] ?? '') == $location['id'] ? 'selected' : ''; ?>>
                                    <?php echo clean($location['name']); ?> 
                                    (<?php echo clean($location['city']); ?>)
                                    - <?php echo clean($location['sport_types']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">
                            Ei leidnud sobivat kohta? 
                            <a href="add_location.php" target="_blank">Lisa uus koht</a>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Дата -->
                        <div class="col-md-4 mb-3">
                            <label for="event_date" class="form-label">
                                <i class="bi bi-calendar-event me-1"></i>
                                Kuupäev <span class="text-danger">*</span>
                            </label>
                            <input type="date" 
                                   class="form-control" 
                                   id="event_date" 
                                   name="event_date" 
                                   value="<?php echo clean($formData['eventDate'] ?? ''); ?>"
                                   min="<?php echo date('Y-m-d'); ?>"
                                   required>
                        </div>
                        
                        <!-- Время -->
                        <div class="col-md-4 mb-3">
                            <label for="event_time" class="form-label">
                                <i class="bi bi-clock me-1"></i>
                                Kellaaeg <span class="text-danger">*</span>
                            </label>
                            <input type="time" 
                                   class="form-control" 
                                   id="event_time" 
                                   name="event_time" 
                                   value="<?php echo clean($formData['eventTime'] ?? ''); ?>"
                                   required>
                        </div>
                        
                        <!-- Длительность -->
                        <div class="col-md-4 mb-3">
                            <label for="duration" class="form-label">
                                <i class="bi bi-hourglass-split me-1"></i>
                                Kestvus (min)
                            </label>
                            <input type="number" 
                                   class="form-control" 
                                   id="duration" 
                                   name="duration" 
                                   value="<?php echo clean($formData['duration'] ?? 120); ?>"
                                   min="30"
                                   max="480"
                                   step="15">
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Количество участников -->
                        <div class="col-md-6 mb-3">
                            <label for="max_participants" class="form-label">
                                <i class="bi bi-people me-1"></i>
                                Osalejate arv <span class="text-danger">*</span>
                            </label>
                            <input type="number" 
                                   class="form-control" 
                                   id="max_participants" 
                                   name="max_participants" 
                                   value="<?php echo clean($formData['maxParticipants'] ?? ''); ?>"
                                   min="2"
                                   max="100"
                                   required
                                   placeholder="Näiteks: 10">
                            <div class="form-text">2 kuni 100 inimest</div>
                        </div>
                        
                        <!-- Уровень игры -->
                        <div class="col-md-6 mb-3">
                            <label for="skill_level" class="form-label">
                                <i class="bi bi-bar-chart me-1"></i>
                                Mängu tase <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="skill_level" name="skill_level" required>
                                <option value="">Vali tase</option>
                                <option value="Algaja" <?php echo ($formData['skillLevel'] ?? '') === 'Algaja' ? 'selected' : ''; ?>>
                                    Algaja
                                </option>
                                <option value="Harrastaja" <?php echo ($formData['skillLevel'] ?? '') === 'Harrastaja' ? 'selected' : ''; ?>>
                                    Harrastaja
                                </option>
                                <option value="Edasijõudnu" <?php echo ($formData['skillLevel'] ?? '') === 'Edasijõudnu' ? 'selected' : ''; ?>>
                                    Edasijõudnu
                                </option>
                                <option value="Professionaal" <?php echo ($formData['skillLevel'] ?? '') === 'Professionaal' ? 'selected' : ''; ?>>
                                    Professionaal
                                </option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Описание -->
                    <div class="mb-4">
                        <label for="description" class="form-label">
                            <i class="bi bi-card-text me-1"></i>
                            Ürituse kirjeldus
                        </label>
                        <textarea class="form-control" 
                                  id="description" 
                                  name="description" 
                                  rows="5"
                                  placeholder="Kirjelda üritust täpsemalt: mida kaasa võtta, reeglid, eripärad..."><?php echo clean($formData['description'] ?? ''); ?></textarea>
                        <div class="form-text">Valikuline väli, kuid soovitatud täita</div>
                    </div>
                    
                    <!-- Кнопки -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Tühista
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle me-2"></i>
                            Loo üritus
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Подсказки -->
        <div class="card mt-3 bg-light">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="bi bi-lightbulb me-2"></i>
                    Nõuanded ürituse loomiseks:
                </h6>
                <ul class="mb-0 small">
                    <li>Vali arusaadav ja atraktiivne pealkiri</li>
                    <li>Märgi täpne koht ja aeg</li>
                    <li>Kirjelduses märgi, mida kaasa võtta</li>
                    <li>Ole aus mängu taseme valikul</li>
                    <li>Jälgi osalejaid ja vasta küsimustele</li>
                    <li>Pärast mängu ära unusta osalejaid hinnata</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
