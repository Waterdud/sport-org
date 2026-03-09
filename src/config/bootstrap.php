<?php
/**
 * BOOTSTRAP - Инициализация приложения
 * 
 * Подключи в начале каждого PHP файла:
 * require_once dirname(__DIR__) . '/config/bootstrap.php';
 */

// Определяем базовую папку
$rootDir = dirname(__DIR__, 2);

// Подключаем конфиг
require_once $rootDir . '/src/config/config.php';

// Подключаем помощников
require_once $rootDir . '/src/helpers/functions.php';

// Подключаем БД (старая версия для совместимости)
// require_once $rootDir . '/includes/db.php';

// Если используется старая БД структура, подключим её
if (file_exists($rootDir . '/includes/db.php')) {
    require_once $rootDir . '/includes/db.php';
}

// Убедимся, что PDO подключена
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

// Устанавливаем обработчик ошибок
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (DEBUG_MODE) {
        echo "<pre style='background:#fee;padding:10px;margin:10px;'>
            <strong>Error:</strong> $errstr
            <br><strong>File:</strong> $errfile:$errline
        </pre>";
    }
    return true;
});

// Функции для работы с БД (из старого db.php)
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

// Готово!
?>
