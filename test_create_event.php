<?php
session_start();
require_once 'config.php';

try {
    $pdo = new PDO('sqlite:' . SQLITE_DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Тестовый юзер - FIRST CHECK WHAT USERS EXIST
echo "=== ALL USERS IN DATABASE ===\n";
$allUsers = $pdo->query("SELECT id, username, email FROM users")->fetchAll();
foreach ($allUsers as $u) {
    echo "  ID: {$u['id']}, Username: {$u['username']}, Email: {$u['email']}\n";
}
echo "\n";

$userId = 1;  // Use first user
echo "Using User ID: $userId\n\n";

// Проверяем, что пользователь существует
$userCheck = $pdo->query("SELECT id, username FROM users WHERE id = $userId")->fetch();
if (!$userCheck) {
    die("User with ID $userId not found!\n");
}
echo "User found: {$userCheck['username']}\n\n";

// Проверяем locations
$locations = $pdo->query("SELECT id, name FROM locations")->fetchAll();
echo "Available locations:\n";
foreach ($locations as $loc) {
    echo "  ID: {$loc['id']}, Name: {$loc['name']}\n";
}
echo "\n";

// Пытаемся создать событие с минимальными данными
$testData = [
    'creator_id' => $userId,
    'title' => 'Test Event',
    'sport_type' => 'Футбол',
    'location_id' => 1, // Kadriorg Stadium
    'event_date' => '2026-03-01',
    'event_time' => '14:00',
    'duration' => 120,
    'max_participants' => 10,
    'skill_level' => 'Любитель',
    'description' => 'Test description',
    'status' => 'active'
];

echo "Attempting to insert event with data:\n";
print_r($testData);
echo "\n";

try {
    $sql = "INSERT INTO events (
                creator_id, title, sport_type, location_id, 
                event_date, event_time, duration, max_participants, 
                skill_level, description, status
            ) VALUES (:creator_id, :title, :sport_type, :location_id, 
                      :event_date, :event_time, :duration, :max_participants, 
                      :skill_level, :description, :status)";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute($testData);
    
    if ($result) {
        $eventId = $pdo->lastInsertId();
        echo "SUCCESS! Event created with ID: $eventId\n\n";
        
        // Теперь пытаемся добавить участника
        echo "Adding participant...\n";
        $participantSql = "INSERT INTO participants (event_id, user_id, status) VALUES (?, ?, ?)";
        $stmt2 = $pdo->prepare($participantSql);
        $result2 = $stmt2->execute([$eventId, $userId, 'confirmed']);
        
        if ($result2) {
            echo "SUCCESS! Participant added\n";
        } else {
            echo "ERROR adding participant!\n";
            print_r($stmt2->errorInfo());
        }
    } else {
        echo "ERROR!\n";
        print_r($stmt->errorInfo());
    }
} catch (PDOException $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
}
