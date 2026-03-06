<?php
/**
 * Конфигурация приложения
 */

// Настройки базы данных
// Используем SQLite для быстрого тестирования
define('DB_TYPE', 'sqlite'); // 'mysql' или 'sqlite'
define('DB_HOST', 'localhost');
define('DB_NAME', 'sport_events');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('SQLITE_DB_PATH', __DIR__ . '/database/sport_events.db');

// Настройки приложения
define('SITE_URL', 'http://localhost:8000');
define('SITE_NAME', 'SportConnect');

// Пути к директориям
define('UPLOAD_DIR', __DIR__ . '/uploads');
define('AVATAR_DIR', UPLOAD_DIR . '/avatars');
define('LOCATION_DIR', UPLOAD_DIR . '/locations');

// Настройки безопасности
define('SESSION_LIFETIME', 86400); // 24 часа
define('REMEMBER_ME_LIFETIME', 2592000); // 30 дней

// Настройки загрузки файлов
define('MAX_AVATAR_SIZE', 2097152); // 2 МБ
define('MAX_LOCATION_IMAGE_SIZE', 5242880); // 5 МБ
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// Настройки пагинации
define('EVENTS_PER_PAGE', 9);
define('COMMENTS_PER_PAGE', 20);

// Часовой пояс
date_default_timezone_set('Europe/Moscow');

// Режим разработки
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
