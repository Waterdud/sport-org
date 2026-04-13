<?php
/**
 * Helper functions
 * 
 * Authentication, validation, utilities
 */

// ===== AUTHENTICATION =====

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user data
 */
function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

/**
 * Require authentication (redirect to login if needed)
 */
function requireAuth() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . SITE_URL . '/login');
        exit();
    }
}

/**
 * Redirect to page
 */
function redirect($url) {
    if (strpos($url, 'http') === false) {
        $url = SITE_URL . '/' . ltrim($url, '/');
    }
    header("Location: $url");
    exit();
}

// ===== SECURITY =====

/**
 * Clean from XSS attacks (escape HTML)
 */
function clean($data) {
    if (is_array($data)) {
        return array_map('clean', $data);
    }
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate password (minimum 6 characters)
 */
function isValidPassword($password) {
    return strlen($password) >= 6;
}
function translateParticipantStatus($status) {
    $translations = [
        'Записан' => 'Registreeritud',
        'Подтвержден' => 'Kinnitatud',
        'Не пришёл' => 'Ei tulnud',
        'Пришёл' => 'Osales',
        'Отменил' => 'Tühistanud'
    ];
    return $translations[$status] ?? $status;
}

/**
 * Переводы видов спорта
 */
function translateSport($sport) {
    $translations = [
        'Футбол' => 'Jalgpall',
        'Волейбол' => 'Võrkpall',
        'Баскетбол' => 'Korvpall'
    ];
    return $translations[$sport] ?? $sport;
}

/**
 * Переводы уровней мастерства
 */
function translateSkillLevel($level) {
    $translations = [
        'Начинающий' => 'Algaja',
        'Любитель' => 'Harrastaja',
        'Продвинутый' => 'Edasijõudnu',
        'Профессионал' => 'Professionaal'
    ];
    return $translations[$level] ?? $level;
}

// ===== УТИЛИТЫ =====

/**
 * Переводящая функция (по количеству)
 */
function plural($count, $one, $two, $five) {
    if ($count % 10 == 1 && $count % 100 != 11) {
        return $one;
    } elseif ($count % 10 >= 2 && $count % 10 <= 4 && ($count % 100 < 10 || $count % 100 >= 20)) {
        return $two;
    } else {
        return $five;
    }
}

/**
 * Форматирование даты (эстонский)
 */
function formatDateEt($date) {
    if (!$date) return '';
    
    $months = [
        'jaanuar', 'veebruar', 'märts', 'aprill', 'mai', 'juuni',
        'juuli', 'august', 'september', 'oktoober', 'november', 'detsember'
    ];
    
    $timestamp = strtotime($date);
    $day = date('d', $timestamp);
    $month = $months[(int)date('m', $timestamp) - 1];
    $year = date('Y', $timestamp);
    
    return "$day. $month $year";
}

/**
 * Форматирование времени (эстонский)
 */
function formatTimeEt($time) {
    if (!$time) return '';
    return date('H:i', strtotime($time));
}

/**
 * Сокращение текста
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . $suffix;
}

/**
 * Получить название города (локация)
 */
function getCityName($city) {
    $names = [
        'Tallinn' => 'Tallinn',
        'Tartu' => 'Tartu',
        'Pärnu' => 'Pärnu',
        'Narva' => 'Narva'
    ];
    return $names[$city] ?? $city;
}

?>
