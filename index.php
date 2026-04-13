<?php
/**
 * SportOrg - Main Entry Point
 * 
 * Development environment entry point
 * Supports both PATH-based and QUERY-based routing
 */

require_once __DIR__ . '/src/config/bootstrap.php';

// Support query parameter routing (?page=login)
if (isset($_GET['page'])) {
    $page = preg_replace('/[^a-z0-9_-]/', '', strtolower($_GET['page']));
} else {
    // Parse request path for PATH-based routing (/login, /events, etc)
    $path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

    // Remove base path if present
    $basePath = trim(parse_url(SITE_URL, PHP_URL_PATH), '/');
    if ($basePath && strpos($path, $basePath) === 0) {
        $path = substr($path, strlen($basePath));
    }

    // Remove .php extension if present (for direct .php file requests)
    $path = preg_replace('/\.php$/', '', $path);
    
    // Map path to page parameter
    $pathToPage = [
        '' => 'home',
        '/' => 'home',
        'home' => 'home',
        'auth/login' => 'login',
        'login' => 'login',
        'auth/register' => 'register',
        'register' => 'register',
        'auth/logout' => 'logout',
        'logout' => 'logout',
        'events' => 'events',
        'events/list' => 'events',
        'events/create' => 'event-create',
        'events/my' => 'event-my',
        'events/view' => 'event-view',
        'locations' => 'locations',
        'locations/list' => 'locations',
        'locations/add' => 'location-add',
        'user/profile' => 'profile',
        'profile' => 'profile',
        'user/notifications' => 'notifications',
        'notifications' => 'notifications',
        'leaderboard' => 'leaderboard',
        'upcoming' => 'upcoming',
        'ajax/join-event' => 'ajax-join',
        'ajax/leave-event' => 'ajax-leave',
        'ajax/unread-count' => 'ajax-unread',
        'ajax/add-comment' => 'ajax-comment',
        'ajax/mark-read' => 'ajax-mark-read',
        'admin-dashboard' => 'admin-dashboard',
        'admin-users' => 'admin-users',
        'admin-events' => 'admin-events',
    ];
    
    $page = $pathToPage[$path] ?? 'home';
}

// Route mapping
$routes = [
    'home' => BASE_PATH . '/src/pages/home.php',
    'login' => BASE_PATH . '/src/pages/auth/login.php',
    'register' => BASE_PATH . '/src/pages/auth/register.php',
    'logout' => BASE_PATH . '/src/pages/auth/logout.php',
    'events' => BASE_PATH . '/src/pages/events/list.php',
    'event-create' => BASE_PATH . '/src/pages/events/create.php',
    'event-view' => BASE_PATH . '/src/pages/events/view.php',
    'event-my' => BASE_PATH . '/src/pages/events/my.php',
    'locations' => BASE_PATH . '/src/pages/locations/list.php',
    'location-add' => BASE_PATH . '/src/pages/locations/add.php',
    'profile' => BASE_PATH . '/src/pages/user/profile.php',
    'notifications' => BASE_PATH . '/src/pages/user/notifications.php',
    'leaderboard' => BASE_PATH . '/src/pages/leaderboard.php',
    'upcoming' => BASE_PATH . '/src/pages/upcoming.php',
    'admin-dashboard' => BASE_PATH . '/src/pages/admin/dashboard.php',
    'admin-users' => BASE_PATH . '/src/pages/admin/users.php',
    'admin-events' => BASE_PATH . '/src/pages/admin/events.php',
];

// Load the appropriate page
$filePath = $routes[$page] ?? BASE_PATH . '/src/pages/home.php';

if (file_exists($filePath)) {
    require_once $filePath;
} else {
    http_response_code(404);
    require_once BASE_PATH . '/src/components/Header.php';
    ?>
    <div class="text-center py-5">
        <h1 class="display-1">404</h1>
        <h2>Page Not Found</h2>
        <p class="text-muted">The requested page could not be found.</p>
        <a href="<?php echo SITE_URL; ?>" class="btn btn-primary">Back home</a>
    </div>
    <?php
    require_once BASE_PATH . '/src/components/Footer.php';
}
?>
