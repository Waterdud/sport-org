<?php
require_once __DIR__ . '/src/config/bootstrap.php';

// Reset admin password
$new_password = 'admin123';
$password_hash = password_hash($new_password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE username = ?');
    $stmt->execute([$password_hash, 'admin']);
    
    echo "✓ Admin password reset!\n\n";
    echo "Username: admin\n";
    echo "Email: admin@sport.com\n";
    echo "Password: $new_password\n\n";
    echo "Login at: http://localhost:8000?page=login\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
