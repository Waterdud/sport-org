<?php
/**
 * Регистрация - Registreeru
 */

require_once dirname(__DIR__, 3) . '/src/config/bootstrap.php';

$pageTitle = 'Registreeru';

if (isLoggedIn()) {
    redirect('/');
}

$errors = [];
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    $formData['username'] = $username;
    $formData['email'] = $email;
    
    // Валидация
    if (empty($username)) {
        $errors[] = 'Kasutajanimi on kohustuslik';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Kasutajanimi peab olema vähemalt 3 tähemärki';
    }
    
    if (empty($email)) {
        $errors[] = 'E-post on kohustuslik';
    } elseif (!isValidEmail($email)) {
        $errors[] = 'Vale e-posti formaat';
    }
    
    if (empty($password)) {
        $errors[] = 'Parool on kohustuslik';
    } elseif (!isValidPassword($password)) {
        $errors[] = 'Parool peab olema vähemalt 6 tähemärki';
    }
    
    if ($password !== $confirm) {
        $errors[] = 'Paroolid ei kattu';
    }
    
    if (empty($errors)) {
        // Проверяем, что email не занят
        $existing = fetchOne($pdo, "SELECT id FROM users WHERE email = ?", [$email]);
        if ($existing) {
            $errors[] = 'Email on juba registreeritud';
        }
    }
    
    if (empty($errors)) {
        // Создаём пользователя
        execute($pdo,
            "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)",
            [$username, $email, password_hash($password, PASSWORD_DEFAULT)]);
        
        redirect('/login');
    }
}

require_once BASE_PATH . '/src/components/Header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h2 class="card-title text-center mb-4">
                    <i class="bi bi-person-plus text-primary me-2"></i>
                    Registreeru
                </h2>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <strong>Viga:</strong>
                        <ul class="mb-0 mt-2">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo clean($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="/register" novalidate>
                    <!-- Username -->
                    <div class="mb-3">
                        <label for="username" class="form-label">
                            <i class="bi bi-person me-1"></i>
                            Kasutajanimi <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="username" 
                               name="username" 
                               value="<?php echo clean($formData['username'] ?? ''); ?>"
                               placeholder="Sinu kasutajanimi"
                               required>
                    </div>
                    
                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope me-1"></i>
                            Email <span class="text-danger">*</span>
                        </label>
                        <input type="email" 
                               class="form-control form-control-lg" 
                               id="email" 
                               name="email" 
                               value="<?php echo clean($formData['email'] ?? ''); ?>"
                               placeholder="example@mail.com"
                               required>
                    </div>
                    
                    <!-- Password -->
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock me-1"></i>
                            Parool <span class="text-danger">*</span>
                        </label>
                        <input type="password" 
                               class="form-control form-control-lg" 
                               id="password" 
                               name="password" 
                               placeholder="Vähemalt 6 tähemärki"
                               required>
                    </div>
                    
                    <!-- Confirm Password -->
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">
                            <i class="bi bi-lock me-1"></i>
                            Kinnita parool <span class="text-danger">*</span>
                        </label>
                        <input type="password" 
                               class="form-control form-control-lg" 
                               id="confirm_password" 
                               name="confirm_password" 
                               placeholder="Kinnita parool"
                               required>
                    </div>
                    
                    <!-- Register Button -->
                    <div class="d-grid gap-2 mb-3">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-person-plus me-2"></i>
                            Registreeru
                        </button>
                    </div>
                    
                    <!-- Link to Login -->
                    <div class="d-grid">
                        <a href="/login" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-box-arrow-in-right me-2"></i>
                            Mul on juba konto
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . '/src/components/Footer.php'; ?>
