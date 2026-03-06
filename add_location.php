<?php
/**
 * Добавление нового места для игр
 * 
 * Функционал:
 * - Форма добавления места
 * - Загрузка фото
 * - Валидация данных
 */

require_once 'includes/db.php';
require_once 'includes/functions.php';

requireAuth();

$pageTitle = 'Lisa toimumiskoht';

$errors = [];
$formData = [];

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!verifyCsrfToken()) {
        $errors[] = 'Turvaline viga. Proovige uuesti.';
    } else {
        
        // Получение данных
        $name = trim($_POST['name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $sportTypes = $_POST['sport_types'] ?? [];
        $description = trim($_POST['description'] ?? '');
        
        // Сохраняем данные
        $formData = compact('name', 'address', 'city', 'sportTypes', 'description');
        
        // Валидация
        if (empty($name)) {
            $errors[] = 'Toimumiskoha nimi on kohustuslik';
        } elseif (strlen($name) < 3) {
            $errors[] = 'Nimi peab sisaldama vähemalt 3 tähemärki';
        }
        
        if (empty($address)) {
            $errors[] = 'Aadress on kohustuslik';
        }
        
        if (empty($city)) {
            $errors[] = 'Linn on kohustuslik';
        }
        
        if (empty($sportTypes)) {
            $errors[] = 'Valige vähemalt üks spordiala';
        }
        
        // Обработка загрузки фото
        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // Создаём директорию если не существует
            if (!is_dir('uploads/locations')) {
                mkdir('uploads/locations', 0755, true);
            }
            
            $uploadResult = uploadFile($_FILES['image'], 'uploads/locations', ['jpg', 'jpeg', 'png'], 5242880);
            
            if ($uploadResult['success']) {
                $image = $uploadResult['filename'];
            } else {
                $errors[] = $uploadResult['error'];
            }
        }
        
        // Если нет ошибок, добавляем место
        if (empty($errors)) {
            $sportTypesStr = implode(',', $sportTypes);
            
            $sql = "INSERT INTO locations (name, address, city, sport_types, description, image) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $locationId = insert($pdo, $sql, [
                $name,
                $address,
                $city,
                $sportTypesStr,
                $description,
                $image
            ]);
            
            if ($locationId) {
                setFlashMessage('success', 'Toimumiskoht edukalt lisatud!');
                redirect('locations.php');
            } else {
                $errors[] = 'Viga toimumiskoha lisamisel. Proovige hiljem uuesti.';
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
                    Lisa uus toimumiskoht
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
                
                <form method="POST" action="add_location.php" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <?php echo csrfField(); ?>
                    
                    <!-- Название -->
                    <div class="mb-3">
                        <label for="name" class="form-label">
                            <i class="bi bi-tag me-1"></i>
                            Toimumiskoha nimi <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="name" 
                               name="name" 
                               value="<?php echo clean($formData['name'] ?? ''); ?>"
                               placeholder="Näiteks: Keskstaadion"
                               required
                               minlength="3"
                               maxlength="200">
                    </div>
                    
                    <!-- Адрес -->
                    <div class="mb-3">
                        <label for="address" class="form-label">
                            <i class="bi bi-geo-alt me-1"></i>
                            Aadress <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="address" 
                               name="address" 
                               value="<?php echo clean($formData['address'] ?? ''); ?>"
                               placeholder="Näiteks: Vabaduse väljak 10"
                               required
                               maxlength="255">
                    </div>
                    
                    <!-- Город -->
                    <div class="mb-3">
                        <label for="city" class="form-label">
                            <i class="bi bi-building me-1"></i>
                            Linn <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="city" 
                               name="city" 
                               value="<?php echo clean($formData['city'] ?? ''); ?>"
                               placeholder="Tallinn"
                               required
                               maxlength="100">
                        <div class="form-text">Märkige linn, kus toimumiskoht asub</div>
                    </div>
                    
                    <!-- Виды спорта -->
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="bi bi-trophy me-1"></i>
                            Sobivad spordialad <span class="text-danger">*</span>
                        </label>
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="sport_types[]" 
                                   value="Jalgpall" 
                                   id="sport_football"
                                   <?php echo in_array('Jalgpall', $formData['sportTypes'] ?? []) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="sport_football">
                                ⚽ Jalgpall
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="sport_types[]" 
                                   value="Võrkpall" 
                                   id="sport_volleyball"
                                   <?php echo in_array('Võrkpall', $formData['sportTypes'] ?? []) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="sport_volleyball">
                                🏐 Võrkpall
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="sport_types[]" 
                                   value="Korvpall" 
                                   id="sport_basketball"
                                   <?php echo in_array('Korvpall', $formData['sportTypes'] ?? []) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="sport_basketball">
                                🏀 Korvpall
                            </label>
                        </div>
                        <div class="form-text">Valige üks või mitu spordiala</div>
                    </div>
                    
                    <!-- Описание -->
                    <div class="mb-3">
                        <label for="description" class="form-label">
                            <i class="bi bi-card-text me-1"></i>
                            Toimumiskoha kirjeldus
                        </label>
                        <textarea class="form-control" 
                                  id="description" 
                                  name="description" 
                                  rows="4"
                                  placeholder="Kirjeldage toimumiskohta: milline on kate, kas on riietusruumid, valgustus, parkla..."><?php echo clean($formData['description'] ?? ''); ?></textarea>
                        <div class="form-text">Valikuline väli, kuid soovitatav täita</div>
                    </div>
                    
                    <!-- Фото -->
                    <div class="mb-4">
                        <label for="image" class="form-label">
                            <i class="bi bi-image me-1"></i>
                            Toimumiskoha foto
                        </label>
                        <input type="file" 
                               class="form-control" 
                               id="image" 
                               name="image" 
                               accept="image/*"
                               onchange="previewImage(this, 'imagePreview')">
                        <div class="form-text">JPG, PNG. Maksimaalselt 5 MB</div>
                        
                        <!-- Предпросмотр фото -->
                        <img id="imagePreview" 
                             src="" 
                             alt="Eelvaade" 
                             class="mt-3 rounded shadow-sm" 
                             style="max-width: 100%; max-height: 300px; display: none;">
                    </div>
                    
                    <!-- Кнопки -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                        <a href="locations.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Tühista
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle me-2"></i>
                            Lisa toimumiskoht
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
                    Soovitused toimumiskoha lisamiseks:
                </h6>
                <ul class="mb-0 small">
                    <li>Märkige täpne ja täielik aadress</li>
                    <li>Kirjelduses märkige: katte tüüp, riietusruumide olemasolu, valgustus</li>
                    <li>Laadige üles kvaliteetne foto kohast</li>
                    <li>Märkige kõik sobivad spordialad</li>
                    <li>Kontrollige, et sellist kohta pole veel lisatud</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
