<?php
/**
 * Страница входа в систему - Sisselogimine
 * 
 * Функционал:
 * - Авторизация по email и паролю
 * - Проверка пароля через password_verify()
 * - Запоминание пользователя (Remember Me)
 */

require_once dirname(__DIR__, 3) . '/src/config/bootstrap.php';

$pageTitle = 'Sisselogimine';

// Если пользователь уже авторизован
if (isLoggedIn()) {
    redirect('/');
}

$errors = [];
$formData = [];

// Обработка формы входа
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    $formData['email'] = $email;
    
    // Валидация
    if (empty($email)) {
        $errors[] = 'E-post on kohustuslik';
    } elseif (!isValidEmail($email)) {
        $errors[] = 'Vale e-posti formaat';
    }
    
    if (empty($password)) {
        $errors[] = 'Parool on kohustuslik';
    }
    
    // Если нет ошибок валидации
    if (empty($errors)) {
        // Поиск пользователя по email
        $user = fetchOne($pdo, "SELECT * FROM users WHERE email = ?", [$email]);
        
        // Проверка пароля
        if (!$user || !password_verify($password, $user['password'] ?? '')) {
            $errors[] = 'Vale e-post või parool';
        } else {
            // Успешная авторизация
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = $user;
            
            // Remember Me
            if ($remember) {
                setcookie('remember_user', $user['id'], time() + (86400 * 30), '/');
            }
            
            redirect('/');
        }
    }
}

require_once BASE_PATH . '/src/components/Header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h2 class="card-title text-center mb-4">
                    <i class="bi bi-box-arrow-in-right text-primary me-2"></i>
                    Sisselogimine
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
                
                <form method="POST" action="/login" novalidate>
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
                               required
                               autofocus>
                    </div>
                    
                    <!-- Parool -->
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock me-1"></i>
                            Parool <span class="text-danger">*</span>
                        </label>
                        <input type="password" 
                               class="form-control form-control-lg" 
                               id="password" 
                               name="password" 
                               placeholder="Sisesta parool"
                               required>
                    </div>
                    
                    <!-- Jäta meelde -->
                    <div class="form-check mb-4">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="remember" 
                               name="remember">
                        <label class="form-check-label" for="remember">
                            Jäta mind meelde
                        </label>
                    </div>
                    
                    <!-- Sisselogimise nupp -->
                    <div class="d-grid gap-2 mb-3">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-box-arrow-in-right me-2"></i>
                            Logi sisse
                        </button>
                    </div>
                    
                    <!-- Link registreerimisele -->
                    <div class="d-grid">
                        <a href="/register" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-person-plus me-2"></i>
                            Loo uus konto
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . '/src/components/Footer.php'; ?>
