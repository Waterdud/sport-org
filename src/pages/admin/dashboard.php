<?php
require_once dirname(__DIR__, 2) . '/config/bootstrap.php';
require_once dirname(__DIR__, 2) . '/admin/AdminAuth.php';

requireAuth();
requireAdmin();

$stats = [];
try {
    $stats['total_users'] = $pdo->query('SELECT COUNT(*) as count FROM users')->fetch()['count'];
    $stats['total_events'] = $pdo->query('SELECT COUNT(*) as count FROM events')->fetch()['count'];
    $stats['total_participants'] = $pdo->query('SELECT COUNT(*) as count FROM game_participants')->fetch()['count'];
} catch (Exception $e) {
    $stats = ['total_users' => 0, 'total_events' => 0, 'total_participants' => 0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SportOrg</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo SITE_URL; ?>/assets/css/style.css" rel="stylesheet">
    <style>
        body { background-color: #f5f5f5; }
        .sidebar { background-color: #2c3e50; color: white; min-height: 100vh; padding: 20px; }
        .sidebar a { color: white; text-decoration: none; display: block; padding: 10px; border-radius: 5px; margin: 5px 0; }
        .sidebar a:hover { background-color: #34495e; }
        .sidebar a.active { background-color: #3498db; }
        .main-content { padding: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; margin: 15px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .stat-number { font-size: 2.5em; font-weight: bold; color: #3498db; }
        .stat-label { color: #7f8c8d; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 sidebar">
                <h3 style="margin-bottom: 30px;">SportOrg Admin</h3>
                <a href="<?php echo SITE_URL; ?>?page=admin-dashboard" class="active">Dashboard</a>
                <a href="<?php echo SITE_URL; ?>?page=admin-users">Users</a>
                <a href="<?php echo SITE_URL; ?>?page=admin-events">Events</a>
                <hr style="background-color: white; opacity: 0.3;">
                <a href="<?php echo SITE_URL; ?>">Back to Site</a>
                <a href="<?php echo SITE_URL; ?>?page=logout">Logout</a>
            </div>
            <div class="col-md-9 main-content">
                <h1>Dashboard</h1>
                <p class="text-muted">Welcome to the admin panel</p>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $stats['total_users']; ?></div>
                            <div class="stat-label">Total Users</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $stats['total_events']; ?></div>
                            <div class="stat-label">Total Events</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-number"><?php echo $stats['total_participants']; ?></div>
                            <div class="stat-label">Event Participants</div>
                        </div>
                    </div>
                </div>

                <div class="stat-card" style="margin-top: 30px;">
                    <h5>Quick Actions</h5>
                    <div class="mt-3">
                        <a href="<?php echo SITE_URL; ?>?page=admin-users" class="btn btn-primary btn-sm">Manage Users</a>
                        <a href="<?php echo SITE_URL; ?>?page=admin-events" class="btn btn-primary btn-sm">Manage Events</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
