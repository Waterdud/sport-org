# Setup Admin User

This script contains SQL commands to set up an admin user.

## Option 1: Make First User an Admin
If you already have a user with ID 1:

```sql
UPDATE users SET role = 'admin' WHERE id = 1;
```

## Option 2: Create New Admin User via Code

You can execute this PHP script directly or create an admin through the web interface after making yourself an admin:

```php
<?php
require_once __DIR__ . '/src/config/bootstrap.php';

// Make user with email 'admin@example.com' an admin
$stmt = $pdo->prepare('UPDATE users SET role = ? WHERE email = ?');
$stmt->execute(['admin', 'admin@example.com']);

echo "Admin role assigned successfully!";
?>
```

## Option 3: Via admin panel
1. Navigate to `?page=admin-users`
2. Click "Toggle Role" next to any user to make them admin
3. Click "Toggle Role" again to remove admin status

## Testing Admin Access
After making a user admin, login with that account and try:
- `?page=admin-dashboard` - Should work
- `?page=admin-users` - Should work
- `?page=admin-events` - Should work

If you're not an admin, you'll see: "Access denied. Admin privileges required."
