<?php
require_once dirname(__DIR__, 2) . '/src/config/bootstrap.php';
require_once BASE_PATH . '/src/services/AnalyticsService.php';

$pageTitle = 'Leaderboard';
$analyticsService = new AnalyticsService($pdo);
$leaderboard = $analyticsService->getLeaderboard(20);

require_once BASE_PATH . '/src/components/Header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1>Leaderboard</h1>
            <p class="text-muted">Most reliable players</p>
        </div>
    </div>

    <div class="card">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Player</th>
                    <th>⭐ Rating</th>
                    <th>Games</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leaderboard as $i => $u): ?>
                <tr>
                    <td><strong><?php echo $i + 1; ?></strong></td>
                    <td>
                        <img src="<?php echo $u['avatar_url'] ?? SITE_URL . '/public/assets/images/default-avatar.png'; ?>" 
                             width="32" class="rounded-circle me-2">
                        <?php echo clean($u['username']); ?>
                    </td>
                    <td><?php echo $u['reliability_rating']; ?>/5.0</td>
                    <td><?php echo $u['games_attended']; ?></td>
                    <td>
                        <a href="<?php echo SITE_URL; ?>/src/pages/user/profile.php?id=<?php echo $u['id']; ?>" 
                           class="btn btn-sm btn-primary">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once BASE_PATH . '/src/components/Footer.php'; ?>
