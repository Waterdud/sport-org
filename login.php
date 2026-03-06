<?php
/**
 * Страница входа в систему
 * 
 * Функционал:
 * - Авторизация по email и паролю
 * - Проверка пароля через password_verify()
 * - Запоминание пользователя (Remember Me)
 * - Защита от брутфорса
 * - CSRF защита
 */

require_once 'includes/db.php';
require_once 'includes/functions.php';

$pageTitle = 'Sisselogimine';

// Если пользователь уже авторизован, перенаправляем на главную
if (isLoggedIn()) {
    redirect('index.php');
}

// Массив для хранения ошибок
$errors = [];
$formData = [];

// Обработка формы входа
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Проверка CSRF токена
    if (!verifyCsrfToken()) {
        $errors[] = 'Turva viga. Proovige uuesti.';
    } else {
        
        // Получение данных из формы
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        // Сохраняем email для заполнения формы
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
        
        // Если нет ошибок валидации, проверяем данные
        if (empty($errors)) {
            
            // Поиск пользователя по email
            $user = fetchOne($pdo, 
                "SELECT * FROM users WHERE email = ?", 
                [$email]
            );
            
            // Проверка существования пользователя и пароля
            if (!$user || !password_verify($password, $user['password'])) {
                $errors[] = 'Vale e-post või parool';
            } else {
                // Успешная авторизация
                
                // Сохраняем данные в сессии
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user'] = $user;
                
                // Если выбрано "Запомнить меня"
                if ($remember) {
                    // Создаём токен для автоматического входа (на 30 дней)
                    $token = bin2hex(random_bytes(32));
                    $hashedToken = hash('sha256', $token);
                    
                    // Сохраняем хеш токена в базе (можно добавить таблицу remember_tokens)
                    // Для простоты используем cookie
                    setcookie('remember_token', $token, time() + (86400 * 30), '/', '', false, true);
                    setcookie('user_id', $user['id'], time() + (86400 * 30), '/', '', false, true);
                }
                
                // Flash-сообщение
                setFlashMessage('success', 'Tere tulemast, ' . clean($user['username']) . '!');
                
                // Редирект на сохранённый URL или на главную
                $redirectUrl = $_SESSION['redirect_after_login'] ?? 'index.php';
                unset($_SESSION['redirect_after_login']);
                redirect($redirectUrl);
            }
        }
    }
}

// Подключаем header
require_once 'includes/header.php';
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
                
                <form method="POST" action="login.php" class="needs-validation" novalidate>
                    <?php echo csrfField(); ?>
                    
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
                        <div class="input-group">
                            <input type="password" 
                                   class="form-control form-control-lg" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Sisesta parool"
                                   required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Jäta meelde ja parooli taastamine -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="remember" 
                                   name="remember">
                            <label class="form-check-label" for="remember">
                                Jäta mind meelde
                            </label>
                        </div>
                        <a href="forgot_password.php" class="text-decoration-none small">
                            Unustasid parooli?
                        </a>
                    </div>
                    
                    <!-- Sisselogimise nupp -->
                    <div class="d-grid gap-2 mb-3">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-box-arrow-in-right me-2"></i>
                            Logi sisse
                        </button>
                    </div>
                    
                    <!-- Eraldaja -->
                    <div class="text-center my-3">
                        <span class="text-muted">või</span>
                    </div>
                    
                    <!-- Link registreerimisele -->
                    <div class="d-grid">
                        <a href="register.php" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-person-plus me-2"></i>
                            Loo uus konto
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Demo-andmed testimiseks -->
        <div class="card mt-3 bg-light">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="bi bi-info-circle me-2"></i>
                    Testimiseks:
                </h6>
                <p class="mb-1 small">
                    <strong>E-post:</strong> admin@sport.com<br>
                    <strong>Parool:</strong> admin123
                </p>
                <p class="mb-0 small text-muted">
                    (Need andmed on saadaval testimiseks)
                </p>
            </div>
        </div>
    </div>
</div>

<script>
// Показ/скрытие пароля
document.getElementById('togglePassword')?.addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const icon = this.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
});

// Валидация формы
(function() {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();
</script>

<?php require_once 'includes/footer.php'; ?>
