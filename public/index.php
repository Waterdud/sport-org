<?php
/**
 * Главная точка входа приложения SportOrg
 * 
 * Этот файл должен быть в корне /public/ или /
 * Маршрутизирует все запросы на нужные страницы
 */

// Подключаем bootstrap
require_once __DIR__ . '/src/config/bootstrap.php';

// Определяем текущую страницу из URL
$page = $_GET['page'] ?? 'home';
$action = $_GET['action'] ?? 'list';

// Безопасность - очищаем параметры
$page = preg_replace('/[^a-z0-9_-]/', '', strtolower($page));
$action = preg_replace('/[^a-z0-9_-]/', '', strtolower($action));

// Маршруты
$routes = [
    // Главная
    'home' => BASE_PATH . '/src/pages/home.php',
    
    // Аутентификация
    'login' => BASE_PATH . '/src/pages/auth/login.php',
    'register' => BASE_PATH . '/src/pages/auth/register.php',
    'logout' => BASE_PATH . '/src/pages/auth/logout.php',
    
    // События/Тренировки
    'events' => BASE_PATH . '/src/pages/events/list.php',
    'event-create' => BASE_PATH . '/src/pages/events/create.php',
    'event-view' => BASE_PATH . '/src/pages/events/view.php',
    'event-my' => BASE_PATH . '/src/pages/events/my.php',
    
    // Локации/Места
    'locations' => BASE_PATH . '/src/pages/locations/list.php',
    'location-add' => BASE_PATH . '/src/pages/locations/add.php',
    
    // Пользователь
    'profile' => BASE_PATH . '/src/pages/user/profile.php',
    'notifications' => BASE_PATH . '/src/pages/user/notifications.php',
];

// Ищем маршрут
$filePath = $routes[$page] ?? BASE_PATH . '/src/pages/home.php';

// Проверяем существование файла
if (file_exists($filePath)) {
    require_once $filePath;
} else {
    // Файл не найден - показываем 404
    header('HTTP/1.0 404 Not Found');
    require_once BASE_PATH . '/src/components/Header.php';
    ?>
    <div class="text-center py-5">
        <h1 class="display-1">404</h1>
        <h2>Leht ei leitud</h2>
        <p class="text-muted">Kahjuks ei leidnud otsitud lehte.</p>
        <a href="<?php echo SITE_URL; ?>" class="btn btn-primary">
            Tagasi avalehele
        </a>
    </div>
    <?php
    require_once BASE_PATH . '/src/components/Footer.php';
}
?>
