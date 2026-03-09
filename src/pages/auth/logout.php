<?php
/**
 * Выход из системы - Logi välja
 */

require_once dirname(__DIR__, 3) . '/src/config/bootstrap.php';

// Очищаем сессию
session_destroy();
setcookie('PHPSESSID', '', time() - 3600, '/');
setcookie('remember_user', '', time() - 3600, '/');

// Редирект на главную
redirect('/');
?>
