<?php
/**
 * Вспомогательные функции для приложения
 */

// Запуск сессии, если она ещё не запущена
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Tõlgi sündmuse olek eesti keelde
 * 
 * @param string $status - olek inglise keeles
 * @return string
 */
function translateEventStatus($status) {
    $translations = [
        'active' => 'Avatud',
        'closed' => 'Suletud',
        'completed' => 'Lõpetatud',
        'cancelled' => 'Tühistatud'
    ];
    return $translations[$status] ?? $status;
}

/**
 * Tõlgi osalejaolek eesti keelde
 * 
 * @param string $status - olek inglise keeles
 * @return string
 */
function translateParticipantStatus($status) {
    $translations = [
        'confirmed' => 'Kinnitatud',
        'pending' => 'Ootel',
        'cancelled' => 'Tühistatud',
        'attended' => 'Osales',
        'missed' => 'Ei tulnud'
    ];
    return $translations[$status] ?? $status;
}

/**
 * Проверка авторизации пользователя
 * 
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Получение ID текущего пользователя
 * 
 * @return int|null
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Получение данных текущего пользователя
 * 
 * @return array|null
 */
function getCurrentUser() {
    return $_SESSION['user'] ?? null;
}

/**
 * Редирект на страницу
 * 
 * @param string $url - URL для редиректа
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Редирект на страницу входа, если пользователь не авторизован
 */
function requireAuth() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect('login.php');
    }
}

/**
 * Защита от XSS атак
 * 
 * @param string $data - данные для очистки
 * @return string
 */
function clean($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Валидация email
 * 
 * @param string $email
 * @return bool
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Валидация телефона (российский формат)
 * 
 * @param string $phone
 * @return bool
 */
function isValidPhone($phone) {
    // Удаляем все символы кроме цифр
    $phone = preg_replace('/[^0-9]/', '', $phone);
    // Проверяем длину (10 или 11 цифр)
    return strlen($phone) >= 10 && strlen($phone) <= 11;
}

/**
 * Установка flash-сообщения
 * 
 * @param string $type - тип (success, error, warning, info)
 * @param string $message - текст сообщения
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Получение и удаление flash-сообщения
 * 
 * @return array|null
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Отображение flash-сообщения (HTML)
 * 
 * @return string
 */
function displayFlashMessage() {
    $flash = getFlashMessage();
    if (!$flash) {
        return '';
    }
    
    $alertClass = [
        'success' => 'alert-success',
        'error' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info'
    ];
    
    $class = $alertClass[$flash['type']] ?? 'alert-info';
    
    return sprintf(
        '<div class="alert %s alert-dismissible fade show" role="alert">
            %s
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>',
        $class,
        clean($flash['message'])
    );
}

/**
 * Форматирование даты
 * 
 * @param string $date - дата в формате Y-m-d
 * @return string
 */
function formatDate($date) {
    $months = [
        1 => 'jaanuar', 2 => 'veebruar', 3 => 'märts', 4 => 'aprill',
        5 => 'mai', 6 => 'juuni', 7 => 'juuli', 8 => 'august',
        9 => 'september', 10 => 'oktoober', 11 => 'november', 12 => 'detsember'
    ];
    
    $timestamp = strtotime($date);
    $day = date('j', $timestamp);
    $month = $months[(int)date('n', $timestamp)];
    $year = date('Y', $timestamp);
    
    return "$day $month $year";
}

/**
 * Форматирование времени
 * 
 * @param string $time - время в формате H:i:s
 * @return string
 */
function formatTime($time) {
    return date('H:i', strtotime($time));
}

/**
 * Форматирование даты и времени вместе
 * 
 * @param string $date - дата
 * @param string $time - время
 * @return string
 */
function formatDateTime($date, $time) {
    return formatDate($date) . ' kell ' . formatTime($time);
}

/**
 * Получение относительного времени (например, "2 часа назад")
 * 
 * @param string $datetime - дата и время
 * @return string
 */
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'äsja';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' minutit tagasi';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' tundi tagasi';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' päeva tagasi';
    } else {
        return formatDate(date('Y-m-d', $timestamp));
    }
}

/**
 * Правильное склонение слов в русском языке
 * 
 * @param int $number - число
 * @param string $one - форма для 1 (например, "день")
 * @param string $two - форма для 2-4 (например, "дня")
 * @param string $five - форма для 5+ (например, "дней")
 * @return string
 */
function plural($number, $one, $two, $five) {
    $number = abs($number) % 100;
    $n1 = $number % 10;
    
    if ($number > 10 && $number < 20) {
        return $five;
    }
    if ($n1 > 1 && $n1 < 5) {
        return $two;
    }
    if ($n1 == 1) {
        return $one;
    }
    return $five;
}

/**
 * Загрузка файла (аватар, фото)
 * 
 * @param array $file - $_FILES['fieldname']
 * @param string $uploadDir - директория загрузки
 * @param array $allowedTypes - разрешённые типы файлов
 * @param int $maxSize - максимальный размер в байтах
 * @return array - ['success' => bool, 'filename' => string, 'error' => string]
 */
function uploadFile($file, $uploadDir, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'], $maxSize = 5242880) {
    // Проверка на ошибки загрузки
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Ошибка при загрузке файла'];
    }
    
    // Проверка размера файла
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'Размер файла превышает ' . ($maxSize / 1048576) . ' МБ'];
    }
    
    // Получение расширения файла
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Проверка типа файла
    if (!in_array($extension, $allowedTypes)) {
        return ['success' => false, 'error' => 'Недопустимый тип файла. Разрешены: ' . implode(', ', $allowedTypes)];
    }
    
    // Генерация уникального имени файла
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . '/' . $filename;
    
    // Создание директории, если она не существует
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Перемещение загруженного файла
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename];
    } else {
        return ['success' => false, 'error' => 'Не удалось сохранить файл'];
    }
}

/**
 * Удаление файла
 * 
 * @param string $filepath - путь к файлу
 * @return bool
 */
function deleteFile($filepath) {
    if (file_exists($filepath) && is_file($filepath)) {
        return unlink($filepath);
    }
    return false;
}

/**
 * Пагинация
 * 
 * @param int $totalItems - общее количество записей
 * @param int $perPage - записей на страницу
 * @param int $currentPage - текущая страница
 * @return array - ['offset' => int, 'totalPages' => int]
 */
function paginate($totalItems, $perPage = 10, $currentPage = 1) {
    $totalPages = ceil($totalItems / $perPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;
    
    return [
        'offset' => $offset,
        'totalPages' => $totalPages,
        'currentPage' => $currentPage,
        'perPage' => $perPage
    ];
}

/**
 * Генерация HTML для пагинации
 * 
 * @param int $totalPages - всего страниц
 * @param int $currentPage - текущая страница
 * @param string $baseUrl - базовый URL (например, "events.php?page=")
 * @return string
 */
function renderPagination($totalPages, $currentPage, $baseUrl) {
    if ($totalPages <= 1) {
        return '';
    }
    
    $html = '<nav aria-label="Навигация по страницам"><ul class="pagination justify-content-center">';
    
    // Кнопка "Назад"
    $prevDisabled = $currentPage == 1 ? 'disabled' : '';
    $prevPage = max(1, $currentPage - 1);
    $html .= sprintf(
        '<li class="page-item %s"><a class="page-link" href="%s%d">Назад</a></li>',
        $prevDisabled,
        $baseUrl,
        $prevPage
    );
    
    // Страницы
    for ($i = 1; $i <= $totalPages; $i++) {
        $active = $i == $currentPage ? 'active' : '';
        $html .= sprintf(
            '<li class="page-item %s"><a class="page-link" href="%s%d">%d</a></li>',
            $active,
            $baseUrl,
            $i,
            $i
        );
    }
    
    // Кнопка "Вперёд"
    $nextDisabled = $currentPage == $totalPages ? 'disabled' : '';
    $nextPage = min($totalPages, $currentPage + 1);
    $html .= sprintf(
        '<li class="page-item %s"><a class="page-link" href="%s%d">Вперёд</a></li>',
        $nextDisabled,
        $baseUrl,
        $nextPage
    );
    
    $html .= '</ul></nav>';
    
    return $html;
}

/**
 * Проверка CSRF токена
 * 
 * @return bool
 */
function verifyCsrfToken() {
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

/**
 * Генерация CSRF токена
 * 
 * @return string
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * HTML для скрытого поля CSRF токена
 * 
 * @return string
 */
function csrfField() {
    $token = generateCsrfToken();
    return sprintf('<input type="hidden" name="csrf_token" value="%s">', $token);
}
