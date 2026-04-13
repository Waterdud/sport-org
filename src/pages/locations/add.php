<?php
/**
 * Добавление нового места - Lisa koht
 */

require_once dirname(__DIR__, 3) . '/src/config/bootstrap.php';
requireAuth();

$pageTitle = 'Lisa koht';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $sportTypes = implode(',', array_filter($_POST['sports'] ?? []));
    $description = trim($_POST['description'] ?? '');
    
    if (empty($name)) $errors[] = 'Nimi on kohustuslik';
    if (empty($address)) $errors[] = 'Aadress on kohustuslik';
    if (empty($city)) $errors[] = 'Linn on kohustuslik';
    
    if (empty($errors)) {
        execute($pdo,
            "INSERT INTO locations (name, address, city, sport_types, description) VALUES (?, ?, ?, ?, ?)",
            [$name, $address, $city, $sportTypes, $description]);
        redirect('/locations');
    }
}

require_once BASE_PATH . '/src/components/Header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <h2>
            <i class="bi bi-plus-circle me-2"></i>Lisa uus koht
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
                    <label class="form-label">Nimi</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Aadress</label>
                    <input type="text" name="address" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Linn</label>
                    <input type="text" name="city" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Spordialad</label>
                    <div class="form-check">
                        <input type="checkbox" name="sports[]" value="Футбол" class="form-check-input" id="football">
                        <label class="form-check-label" for="football">Jalgpall</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="sports[]" value="Волейбол" class="form-check-input" id="volleyball">
                        <label class="form-check-label" for="volleyball">Võrkpall</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="sports[]" value="Баскетбол" class="form-check-input" id="basketball">
                        <label class="form-check-label" for="basketball">Korvpall</label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Lisa koht</button>
            </div>
        </form>
    </div>
</div>

<?php require_once BASE_PATH . '/src/components/Footer.php'; ?>
