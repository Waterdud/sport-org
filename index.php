<?php
/**
 * SportOrg - Main Entry Point
 * 
 * Development environment entry point
 * Redirects to the appropriate page based on request
 */

require_once __DIR__ . '/src/config/bootstrap.php';

// Parse request
$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// Remove base path if present
$basePath = trim(parse_url(SITE_URL, PHP_URL_PATH), '/');
if ($basePath && strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
}

// Simple routing
switch ($path) {
    // Home
    case '':
    case '/':
    case 'home':
        require_once BASE_PATH . '/src/pages/home.php';
        break;
    
    // Auth
    case 'login':
        require_once BASE_PATH . '/src/pages/auth/login.php';
        break;
    case 'register':
        require_once BASE_PATH . '/src/pages/auth/register.php';
        break;
    case 'logout':
        require_once BASE_PATH . '/src/pages/auth/logout.php';
        break;
    
    // Events
    case 'events':
        require_once BASE_PATH . '/src/pages/events/list.php';
        break;
    case 'events/create':
        require_once BASE_PATH . '/src/pages/events/create.php';
        break;
    case 'events/my':
        require_once BASE_PATH . '/src/pages/events/my.php';
        break;
    case 'events/view':
        require_once BASE_PATH . '/src/pages/events/view.php';
        break;
    
    // Locations
    case 'locations':
        require_once BASE_PATH . '/src/pages/locations/list.php';
        break;
    case 'locations/add':
        require_once BASE_PATH . '/src/pages/locations/add.php';
        break;
    
    // User
    case 'profile':
        require_once BASE_PATH . '/src/pages/user/profile.php';
        break;
    case 'notifications':
        require_once BASE_PATH . '/src/pages/user/notifications.php';
        break;
    
    // AJAX
    case 'ajax/join-event':
        require_once BASE_PATH . '/src/ajax/join_event.php';
        break;
    case 'ajax/leave-event':
        require_once BASE_PATH . '/src/ajax/leave_event.php';
        break;
    case 'ajax/unread-count':
        require_once BASE_PATH . '/src/ajax/get_unread_count.php';
        break;
    case 'ajax/add-comment':
        require_once BASE_PATH . '/src/ajax/add_comment.php';
        break;
    case 'ajax/mark-read':
        require_once BASE_PATH . '/src/ajax/mark_notification_read.php';
        break;
    
    // 404
    default:
        http_response_code(404);
        echo "404 - Lehekülg ei leitud";
        break;
}
?>
