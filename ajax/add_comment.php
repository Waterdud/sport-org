<?php
/**
 * AJAX обработчик: Добавить комментарий
 * 
 * Возвращает JSON с HTML комментария или ошибкой
 */

require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Проверка авторизации
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Необходимо авторизоваться']);
    exit;
}

// Проверка метода
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Неверный метод запроса']);
    exit;
}

// Проверка CSRF
if (!verifyCsrfToken()) {
    echo json_encode(['success' => false, 'error' => 'Ошибка безопасности']);
    exit;
}

$eventId = (int)($_POST['event_id'] ?? 0);
$comment = trim($_POST['comment'] ?? '');
$userId = getUserId();

if ($eventId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Неверный ID события']);
    exit;
}

if (empty($comment)) {
    echo json_encode(['success' => false, 'error' => 'Комментарий не может быть пустым']);
    exit;
}

if (strlen($comment) > 1000) {
    echo json_encode(['success' => false, 'error' => 'Комментарий слишком длинный (макс. 1000 символов)']);
    exit;
}

try {
    // Проверяем существование события
    $event = fetchOne($pdo, "SELECT * FROM events WHERE id = ?", [$eventId]);
    
    if (!$event) {
        echo json_encode(['success' => false, 'error' => 'Событие не найдено']);
        exit;
    }
    
    // Добавляем комментарий
    $commentId = insert($pdo, 
        "INSERT INTO comments (event_id, user_id, comment) VALUES (?, ?, ?)",
        [$eventId, $userId, $comment]
    );
    
    // Получаем данные добавленного комментария с информацией о пользователе
    $newComment = fetchOne($pdo, "
        SELECT c.*, u.username, u.avatar 
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.id = ?
    ", [$commentId]);
    
    // Уведомляем организатора (если комментарий не от него)
    if ($event['creator_id'] != $userId) {
        $notificationText = "Пользователь {$_SESSION['user']['username']} оставил комментарий к событию '{$event['title']}'";
        execute($pdo, 
            "INSERT INTO notifications (user_id, event_id, type, message) VALUES (?, ?, 'comment', ?)",
            [$event['creator_id'], $eventId, $notificationText]
        );
    }
    
    // Формируем HTML комментария
    $avatarPath = $newComment['avatar'] ? 'uploads/avatars/' . $newComment['avatar'] : 'https://via.placeholder.com/50';
    $commentHtml = '
    <div class="d-flex mb-3 comment-item" data-comment-id="' . $commentId . '">
        <img src="' . clean($avatarPath) . '" 
             alt="' . clean($newComment['username']) . '" 
             class="rounded-circle me-3" 
             width="50" 
             height="50"
             style="object-fit: cover;">
        <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <a href="profile.php?id=' . $newComment['user_id'] . '" class="fw-bold text-decoration-none">
                    ' . clean($newComment['username']) . '
                </a>
                <small class="text-muted">' . timeAgo($newComment['created_at']) . '</small>
            </div>
            <p class="mb-0">' . nl2br(clean($newComment['comment'])) . '</p>
        </div>
    </div>';
    
    echo json_encode([
        'success' => true,
        'message' => 'Комментарий добавлен',
        'html' => $commentHtml,
        'comment_id' => $commentId
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Ошибка при добавлении комментария']);
}
