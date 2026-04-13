<?php
require_once dirname(__DIR__, 2) . '/config/bootstrap.php';
require_once dirname(__DIR__, 2) . '/admin/AdminAuth.php';

requireAuth();
requireAdmin();

$events = [];
$action = $_GET['action'] ?? 'list';

if ($action === 'delete' && isset($_GET['event_id'])) {
    $event_id = intval($_GET['event_id']);
    try {
        $pdo->prepare('DELETE FROM events WHERE id = ?')->execute([$event_id]);
        header('Location: ' . SITE_URL . '?page=admin-events');
        exit();
    } catch (Exception $e) {
        $error = 'Error deleting event';
    }
}

try {
    $result = $pdo->query('SELECT e.id, e.title, e.sport_type as sport, e.event_date, l.name as location, u.username, e.status FROM events e LEFT JOIN users u ON e.creator_id = u.id LEFT JOIN locations l ON e.location_id = l.id ORDER BY e.event_date DESC');
    $events = $result->fetchAll();
} catch (Exception $e) {
    $error = 'Error loading events: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - SportOrg Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f5f5f5; }
        .sidebar { background-color: #2c3e50; color: white; min-height: 100vh; padding: 20px; }
        .sidebar a { color: white; text-decoration: none; display: block; padding: 10px; border-radius: 5px; margin: 5px 0; }
        .sidebar a:hover { background-color: #34495e; }
        .sidebar a.active { background-color: #3498db; }
        .main-content { padding: 30px; }
        .status-badge { font-size: 0.85em; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 sidebar">
                <h3 style="margin-bottom: 30px;">SportOrg Admin</h3>
                <a href="<?php echo SITE_URL; ?>?page=admin-dashboard">Dashboard</a>
                <a href="<?php echo SITE_URL; ?>?page=admin-users">Users</a>
                <a href="<?php echo SITE_URL; ?>?page=admin-events" class="active">Events</a>
                <hr style="background-color: white; opacity: 0.3;">
                <a href="<?php echo SITE_URL; ?>">Back to Site</a>
                <a href="<?php echo SITE_URL; ?>?page=logout">Logout</a>
            </div>
            <div class="col-md-9 main-content">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                    <h1>Manage Events</h1>
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
                                <th>Title</th>
                                <th>Sport</th>
                                <th>Location</th>
                                <th>Date</th>
                                <th>Created By</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $event): ?>
                                <tr>
                                    <td><?php echo $event['id']; ?></td>
                                    <td><?php echo htmlspecialchars($event['title']); ?></td>
                                    <td><?php echo htmlspecialchars($event['sport']); ?></td>
                                    <td><?php echo htmlspecialchars($event['location']); ?></td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($event['event_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($event['username'] ?? 'Unknown'); ?></td>
                                    <td>
                                        <span class="badge status-badge <?php echo $event['status'] === 'cancelled' ? 'bg-danger' : 'bg-success'; ?>">
                                            <?php echo ucfirst($event['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?php echo SITE_URL; ?>?page=event-view&id=<?php echo $event['id']; ?>" 
                                           class="btn btn-sm btn-outline-info">View</a>
                                        <a href="<?php echo SITE_URL; ?>?page=admin-events&action=delete&event_id=<?php echo $event['id']; ?>" 
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
