<?php
require_once 'config.php';

try {
    $pdo = new PDO('sqlite:' . SQLITE_DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

echo "=== EVENTS TABLE SCHEMA ===\n";
$result = $pdo->query("PRAGMA table_info(events)")->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $col) {
    echo "Column: {$col['name']}, Type: {$col['type']}, NotNull: {$col['notnull']}, Default: {$col['dflt_value']}\n";
}

echo "\n=== FOREIGN KEYS IN EVENTS ===\n";
$result = $pdo->query("PRAGMA foreign_key_list(events)")->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $fk) {
    print_r($fk);
}

echo "\n=== PARTICIPANTS TABLE SCHEMA ===\n";
$result = $pdo->query("PRAGMA table_info(participants)")->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $col) {
    echo "Column: {$col['name']}, Type: {$col['type']}, NotNull: {$col['notnull']}, Default: {$col['dflt_value']}\n";
}

echo "\n=== FOREIGN KEYS IN PARTICIPANTS ===\n";
$result = $pdo->query("PRAGMA foreign_key_list(participants)")->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $fk) {
    print_r($fk);
}

echo "\n=== AVAILABLE LOCATIONS ===\n";
$locations = $pdo->query("SELECT id, name FROM locations")->fetchAll(PDO::FETCH_ASSOC);
foreach ($locations as $loc) {
    echo "ID: {$loc['id']}, Name: {$loc['name']}\n";
}

echo "\n=== CHECK EVENT STATUS VALUES ===\n";
$statuses = $pdo->query("SELECT DISTINCT status FROM events")->fetchAll(PDO::FETCH_ASSOC);
foreach ($statuses as $s) {
    echo "Status: '{$s['status']}'\n";
}
