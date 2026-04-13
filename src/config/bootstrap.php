<?php
/**
 * Bootstrap application initialization
 * 
 * Include at the start of every PHP file:
 * require_once dirname(__DIR__) . '/config/bootstrap.php';
 */

// Define base directory
$rootDir = dirname(__DIR__, 2);

// Load config
require_once $rootDir . '/src/config/config.php';

// Load helpers
require_once $rootDir . '/src/helpers/functions.php';

// Ensure PDO is connected
if (!isset($pdo)) {
    try {
        $dbPath = $rootDir . '/database/sport_events.db';
        $pdo = new PDO('sqlite:' . $dbPath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        $pdo->exec('PRAGMA foreign_keys = ON');
    } catch (PDOException $e) {
        die('Database Error: ' . $e->getMessage());
    }
}

// Set error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (DEBUG_MODE) {
        echo "<pre style='background:#fee;padding:10px;margin:10px;'>
            <strong>Error:</strong> $errstr
            <br><strong>File:</strong> $errfile:$errline
        </pre>";
    }
    return true;
});

// Database helper functions
if (!function_exists('fetchOne')) {
    function fetchOne($pdo, $sql, $params = []) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
}

if (!function_exists('fetchAll')) {
    function fetchAll($pdo, $sql, $params = []) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

if (!function_exists('execute')) {
    function execute($pdo, $sql, $params = []) {
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }
}

if (!function_exists('lastInsertId')) {
    function lastInsertId($pdo) {
        return $pdo->lastInsertId();
    }
}

// Load service classes
if (!function_exists('loadServices')) {
    function loadServices() {
        $rootDir = dirname(__DIR__, 2);
        $services = [
            'RatingService.php',
            'GameStatusService.php',
            'ParticipationService.php',
            'NotificationService.php',
            'FollowService.php',
            'AnalyticsService.php',
            'ReminderService.php'
        ];
        
        foreach ($services as $service) {
            $path = $rootDir . '/src/services/' . $service;
            if (file_exists($path)) {
                require_once $path;
            }
        }
    }
    loadServices();
}

// Готово!
?>
