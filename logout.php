<?php
/**
 * Выход из системы
 * 
 * Функционал:
 * - Удаление всех данных сессии
 * - Удаление cookies
 * - Редирект на главную страницу
 */

require_once 'includes/functions.php';

// Запуск сессии
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Удаление cookies "Запомнить меня"
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}
if (isset($_COOKIE['user_id'])) {
    setcookie('user_id', '', time() - 3600, '/', '', false, true);
}

// Очистка всех переменных сессии
$_SESSION = [];

// Уничтожение сессии
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Уничтожаем сессию
session_destroy();

// Редирект на главную с сообщением
session_start();
setFlashMessage('info', 'Вы успешно вышли из системы');
redirect('index.php');
