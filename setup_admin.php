<?php
require_once __DIR__ . '/src/config/bootstrap.php';

// Check users
$stmt = $pdo->query('SELECT id, username, email, role FROM users LIMIT 10');
$users = $stmt->fetchAll();

if (empty($users)) {
    echo "No users found. Creating admin...\n";
    $username = 'admin';
    $email = 'admin@example.com';
    $password = 'admin123';
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)');
        $stmt->execute([$username, $email, $password_hash, 'admin']);
        echo "✓ Admin created!\n";
        echo "User: $username\n";
        echo "Email: $email\n";
        echo "Pass: $password\n";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "Existing users:\n";
    foreach ($users as $u) {
        echo "- ID: {$u['id']}, User: {$u['username']}, Email: {$u['email']}, Role: {$u['role']}\n";
    }
    
    // Make first user admin
    echo "\nMaking first user admin...\n";
    $first = $users[0];
    $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE id = ?');
    $stmt->execute(['admin', $first['id']]);
    echo "✓ User '{$first['username']}' is now admin!\n";
}

echo "\nLogin: http://localhost:8000?page=login\n";
echo "Admin: http://localhost:8000?page=admin-dashboard\n";
?>
