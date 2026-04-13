<?php
/**
 * Application configuration
 * 
 * Database, paths, constants, security settings
 */

// ===== DATABASE =====
define('DB_TYPE', 'sqlite');
define('DB_HOST', 'localhost');
define('DB_NAME', 'sport_events');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('SQLITE_DB_PATH', __DIR__ . '/../../database/sport_events.db');

// ===== APPLICATION =====
define('SITE_URL', 'http://localhost:8000');
define('SITE_NAME', 'SportOrg');
define('SITE_LANG', 'en');
define('DEBUG_MODE', true);

// ===== PATHS =====
$baseDir = realpath(__DIR__ . '/../../');
define('BASE_PATH', $baseDir);
define('PUBLIC_PATH', $baseDir . '/public');
define('UPLOAD_DIR', $baseDir . '/public/uploads');
define('AVATAR_DIR', UPLOAD_DIR . '/avatars');
define('LOCATION_DIR', UPLOAD_DIR . '/locations');
define('ASSETS_PATH', $baseDir . '/public/assets');
define('GYMS_PATH', ASSETS_PATH . '/images/gyms');

// ===== SECURITY =====
define('SESSION_LIFETIME', 86400);
define('MAX_AVATAR_SIZE', 2097152);
define('MAX_IMAGE_SIZE', 5242880);
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// ===== INITIALIZATION =====
session_start();
error_reporting(DEBUG_MODE ? E_ALL : 0);
ini_set('display_errors', DEBUG_MODE ? 1 : 0);
ini_set('default_charset', 'UTF-8');
header('Content-Type: text/html; charset=utf-8');
?>
