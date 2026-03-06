<?php
/**
 * Страница регистрации нового пользователя
 * 
 * Функционал:
 * - Валидация всех полей формы
 * - Проверка уникальности email и username
 * - Хеширование пароля
 * - Защита от SQL-инъекций и XSS
 * - CSRF защита
 */

require_once 'includes/db.php';
require_once 'includes/functions.php';

$pageTitle = 'Registreerimine';

// Если пользователь уже авторизован, перенаправляем на главную
if (isLoggedIn()) {
    redirect('index.php');
}

// Массив для хранения ошибок
$errors = [];
$formData = [];

// Обработка формы регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Проверка CSRF токена
    if (!verifyCsrfToken()) {
        $errors[] = 'Turva viga. Proovige uuesti.';
    } else {
        
        // Получение и очистка данных из формы
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';
        $phone = trim($_POST['phone'] ?? '');
        
        // Сохраняем данные для заполнения формы в случае ошибки
        $formData = [
            'username' => $username,
            'email' => $email,
            'phone' => $phone
        ];
        
        // Валидация имени пользователя
        if (empty($username)) {
            $errors[] = 'Kasutajanimi on kohustuslik';
        } elseif (strlen($username) < 3) {
            $errors[] = 'Kasutajanimi peab olema vähemalt 3 tähemärki';
        } elseif (strlen($username) > 50) {
            $errors[] = 'Kasutajanimi ei tohi ületada 50 tähemärki';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Kasutajanimi võib sisaldada ainult ladina tähti, numbreid ja alakriipsu';
        }
        
        // Валидация email
        if (empty($email)) {
            $errors[] = 'E-post on kohustuslik';
        } elseif (!isValidEmail($email)) {
            $errors[] = 'Vale e-posti formaat';
        }
        
        // Валидация пароля
        if (empty($password)) {
            $errors[] = 'Parool on kohustuslik';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Parool peab olema vähemalt 6 tähemärki';
        } elseif ($password !== $password_confirm) {
            $errors[] = 'Paroolid ei ühti';
        }
        
        // Валидация телефона (необязательно)
        if (!empty($phone) && !isValidPhone($phone)) {
            $errors[] = 'Vale telefoniformaat';
        }
        
        // Если нет ошибок валидации, проверяем уникальность
        if (empty($errors)) {
            
            // Проверка уникальности username
            $existingUser = fetchOne($pdo, 
                "SELECT id FROM users WHERE username = ?", 
                [$username]
            );
            
            if ($existingUser) {
                $errors[] = 'Selle nimega kasutaja on juba olemas';
            }
            
            // Проверка уникальности email
            $existingEmail = fetchOne($pdo, 
                "SELECT id FROM users WHERE email = ?", 
                [$email]
            );
            
            if ($existingEmail) {
                $errors[] = 'Selle e-postiga kasutaja on juba registreeritud';
            }
        }
        
        // Если ошибок нет, регистрируем пользователя
        if (empty($errors)) {
            
            // Хеширование пароля
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            // Вставка нового пользователя в базу данных
            $sql = "INSERT INTO users (username, email, password, phone) 
                    VALUES (?, ?, ?, ?)";
            
            $userId = insert($pdo, $sql, [$username, $email, $passwordHash, $phone]);
            
            if ($userId) {
                // Успешная регистрация - авторизуем пользователя
                $user = fetchOne($pdo, "SELECT * FROM users WHERE id = ?", [$userId]);
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user'] = $user;
                
                setFlashMessage('success', 'Registreerimine õnnestus! Tere tulemast, ' . clean($username) . '!');
                
                // Редирект на главную или на сохранённый URL
                $redirectUrl = $_SESSION['redirect_after_login'] ?? 'index.php';
                unset($_SESSION['redirect_after_login']);
                redirect($redirectUrl);
            } else {
                $errors[] = 'Viga registreerimisel. Proovige hiljem.';
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
                    <i class="bi bi-person-plus-fill text-primary me-2"></i>
                    Registreerimine
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
                
                <form method="POST" action="register.php" class="needs-validation" novalidate>
                    <?php echo csrfField(); ?>
                    
                    <!-- Kasutajanimi -->
                    <div class="mb-3">
                        <label for="username" class="form-label">
                            <i class="bi bi-person me-1"></i>
                            Kasutajanimi <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control <?php echo isset($errors[0]) && strpos($errors[0], 'Kasutajanimi') !== false ? 'is-invalid' : ''; ?>" 
                               id="username" 
                               name="username" 
                               value="<?php echo clean($formData['username'] ?? ''); ?>"
                               placeholder="Sisesta kasutajanimi"
                               required
                               minlength="3"
                               maxlength="50"
                               pattern="[a-zA-Z0-9_]+">
                        <div class="form-text">Ainult ladina tähed, numbrid ja alakriips (vähemalt 3 tähemärki)</div>
                    </div>
                    
                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope me-1"></i>
                            Email <span class="text-danger">*</span>
                        </label>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               value="<?php echo clean($formData['email'] ?? ''); ?>"
                               placeholder="example@mail.com"
                               required>
                    </div>
                    
                    <!-- Telefon -->
                    <div class="mb-3">
                        <label for="phone" class="form-label">
                            <i class="bi bi-telephone me-1"></i>
                            Telefon
                        </label>
                        <input type="tel" 
                               class="form-control" 
                               id="phone" 
                               name="phone" 
                               value="<?php echo clean($formData['phone'] ?? ''); ?>"
                               placeholder="+372 5XXX XXXX">
                        <div class="form-text">Valikuline väli</div>
                    </div>
                    
                    <!-- Parool -->
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock me-1"></i>
                            Parool <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Sisesta parool"
                                   required
                                   minlength="6">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">Vähemalt 6 tähemärki</div>
                    </div>
                    
                    <!-- Parooli kinnitus -->
                    <div class="mb-3">
                        <label for="password_confirm" class="form-label">
                            <i class="bi bi-lock-fill me-1"></i>
                            Kinnita parool <span class="text-danger">*</span>
                        </label>
                        <input type="password" 
                               class="form-control" 
                               id="password_confirm" 
                               name="password_confirm" 
                               placeholder="Korda parooli"
                               required
                               minlength="6">
                    </div>
                    
                    <!-- Registreerimise nupp -->
                    <div class="d-grid gap-2 mb-3">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle me-2"></i>
                            Registreeru
                        </button>
                    </div>
                    
                    <!-- Link sisselogimise lehele -->
                    <div class="text-center">
                        <p class="mb-0">
                            Kas teil on juba konto? 
                            <a href="login.php" class="text-decoration-none">Logi sisse</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Lisainfo -->
        <div class="card mt-3 bg-light">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="bi bi-info-circle me-2"></i>
                    Registreerumise eelised:
                </h6>
                <ul class="mb-0">
                    <li>Loo oma spordiüritusi</li>
                    <li>Registreeru teiste kasutajate mängudele</li>
                    <li>Saa usaldusväärsuse reiting</li>
                    <li>Jälgi oma mängude ajalugu</li>
                    <li>Saa teateid ürituste kohta</li>
                </ul>
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
