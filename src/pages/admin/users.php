<?php
require_once dirname(__DIR__, 2) . '/config/bootstrap.php';
require_once dirname(__DIR__, 2) . '/admin/AdminAuth.php';

requireAuth();
requireAdmin();

$users = [];
$action = $_GET['action'] ?? 'list';

if ($action === 'delete' && isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
    try {
        $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$user_id]);
        header('Location: ' . SITE_URL . '?page=admin-users');
        exit();
    } catch (Exception $e) {
        $error = 'Error deleting user';
    }
}

if ($action === 'toggle-role' && isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
    try {
        $stmt = $pdo->prepare('SELECT role FROM users WHERE id = ?');
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        $current_role = $user['role'] ?? 'user';
        $new_role = $current_role === 'admin' ? 'user' : 'admin';
        $pdo->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$new_role, $user_id]);
        header('Location: ' . SITE_URL . '?page=admin-users');
        exit();
    } catch (Exception $e) {
        $error = 'Error updating user role: ' . $e->getMessage();
    }
}

try {
    $result = $pdo->query('SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC');
    $users = $result->fetchAll();
} catch (Exception $e) {
    $error = 'Error loading users';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - SportOrg Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f5f5f5; }
        .sidebar { background-color: #2c3e50; color: white; min-height: 100vh; padding: 20px; }
        .sidebar a { color: white; text-decoration: none; display: block; padding: 10px; border-radius: 5px; margin: 5px 0; }
        .sidebar a:hover { background-color: #34495e; }
        .sidebar a.active { background-color: #3498db; }
        .main-content { padding: 30px; }
        .badge-admin { background-color: #e74c3c; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 sidebar">
                <h3 style="margin-bottom: 30px;">SportOrg Admin</h3>
                <a href="<?php echo SITE_URL; ?>?page=admin-dashboard">Dashboard</a>
                <a href="<?php echo SITE_URL; ?>?page=admin-users" class="active">Users</a>
                <a href="<?php echo SITE_URL; ?>?page=admin-events">Events</a>
                <hr style="background-color: white; opacity: 0.3;">
                <a href="<?php echo SITE_URL; ?>">Back to Site</a>
                <a href="<?php echo SITE_URL; ?>?page=logout">Logout</a>
            </div>
            <div class="col-md-9 main-content">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                    <h1>Manage Users</h1>
                    <a href="<?php echo SITE_URL; ?>?page=admin-dashboard" class="btn btn-secondary btn-sm">Back</a>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $user['role'] === 'admin' ? 'badge-admin' : 'bg-secondary'; ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <a href="<?php echo SITE_URL; ?>?page=admin-users&action=toggle-role&user_id=<?php echo $user['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary">Toggle Role</a>
                                        <a href="<?php echo SITE_URL; ?>?page=admin-users&action=delete&user_id=<?php echo $user['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           onclick="return confirm('Are you sure?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
