<?php
/**
 * Application entry point
 * 
 * Route all requests to appropriate pages
 */

// Load bootstrap
require_once __DIR__ . '/src/config/bootstrap.php';

// Get page from URL
$page = $_GET['page'] ?? 'home';
$action = $_GET['action'] ?? 'list';

// Security - sanitize parameters
$page = preg_replace('/[^a-z0-9_-]/', '', strtolower($page));
$action = preg_replace('/[^a-z0-9_-]/', '', strtolower($action));

// Routes
$routes = [
    // Main
    'home' => BASE_PATH . '/src/pages/home.php',
    
    // Auth
    'login' => BASE_PATH . '/src/pages/auth/login.php',
    'register' => BASE_PATH . '/src/pages/auth/register.php',
    'logout' => BASE_PATH . '/src/pages/auth/logout.php',
    
    // Events
    'events' => BASE_PATH . '/src/pages/events/list.php',
    'event-create' => BASE_PATH . '/src/pages/events/create.php',
    'event-view' => BASE_PATH . '/src/pages/events/view.php',
    'event-my' => BASE_PATH . '/src/pages/events/my.php',
    
    // Locations
    'locations' => BASE_PATH . '/src/pages/locations/list.php',
    'location-add' => BASE_PATH . '/src/pages/locations/add.php',
    
    // User
    'profile' => BASE_PATH . '/src/pages/user/profile.php',
    'notifications' => BASE_PATH . '/src/pages/user/notifications.php',
    
    // Admin
    'admin-dashboard' => BASE_PATH . '/src/pages/admin/dashboard.php',
    'admin-users' => BASE_PATH . '/src/pages/admin/users.php',
    'admin-events' => BASE_PATH . '/src/pages/admin/events.php',
];

// Ищем маршрут
$filePath = $routes[$page] ?? BASE_PATH . '/src/pages/home.php';

// Check if file exists
if (file_exists($filePath)) {
    require_once $filePath;
} else {
    // File not found - show 404
    header('HTTP/1.0 404 Not Found');
    require_once BASE_PATH . '/src/components/Header.php';
    ?>
    <div class="text-center py-5">
        <h1 class="display-1">404</h1>
        <h2>Page Not Found</h2>
        <p class="text-muted">The requested page could not be found.</p>
        <a href="<?php echo SITE_URL; ?>" class="btn btn-primary">
            Back home
        </a>
    </div>
    <?php
    require_once BASE_PATH . '/src/components/Footer.php';
}
?>
