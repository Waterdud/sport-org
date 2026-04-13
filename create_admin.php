<?php
require_once __DIR__ . '/src/config/bootstrap.php';

// Make admin@example.com an admin
$email = 'admin@example.com';

try {
    $stmt = $pdo->prepare('UPDATE users SET role = ? WHERE email = ?');
    $stmt->execute(['admin', $email]);
    
    $stmt = $pdo->prepare('SELECT username FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "User updated to admin!\n";
        echo "Email: $email\n";
        echo "Username: " . $user['username'] . "\n";
    } else {
        echo "No user found with that email\n";
        echo "Creating new admin user...\n";
        
        $username = 'admin';
        $password = 'admin123';
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)');
        $stmt->execute([$username, $email, $password_hash, 'admin']);
        
        echo "Admin user created!\n";
        echo "Username: $username\n";
        echo "Email: $email\n";
        echo "Password: $password\n";
    }
    
    echo "\nLogin at: http://localhost:8000?page=login\n";
    echo "Admin panel: http://localhost:8000?page=admin-dashboard\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
