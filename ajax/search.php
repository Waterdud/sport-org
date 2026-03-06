<?php
/**
 * AJAX обработчик: Живой поиск событий
 * 
 * Возвращает JSON с массивом событий
 */

require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');

if (empty($query) || strlen($query) < 2) {
    echo json_encode(['success' => false, 'results' => [], 'message' => 'Введите минимум 2 символа']);
    exit;
}

try {
    $searchPattern = '%' . $query . '%';
    
    $sql = "
        SELECT 
            e.id,
            e.title,
            e.sport_type,
            e.event_date,
            e.event_time,
            e.current_participants,
            e.max_participants,
            e.status,
            l.name as location_name,
            l.city
        FROM events e
        LEFT JOIN locations l ON e.location_id = l.id
        WHERE e.status = 'active'
          AND (e.title LIKE ? OR l.city LIKE ? OR l.name LIKE ?)
          AND e.event_date >= CURDATE()
        ORDER BY e.event_date ASC, e.event_time ASC
        LIMIT 10
    ";
    
    $events = fetchAll($pdo, $sql, [$searchPattern, $searchPattern, $searchPattern]);
    
    // Форматируем результаты
    $results = [];
    foreach ($events as $event) {
        $results[] = [
            'id' => $event['id'],
            'title' => $event['title'],
            'sport_type' => $event['sport_type'],
            'date' => formatDate($event['event_date']),
            'time' => formatTime($event['event_time']),
            'location' => $event['location_name'],
            'city' => $event['city'],
            'participants' => $event['current_participants'] . '/' . $event['max_participants'],
            'url' => 'event.php?id=' . $event['id']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'results' => $results,
        'count' => count($results)
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'results' => [], 'error' => 'Ошибка поиска']);
}
