<?php
/**
 * Профиль пользователя - Profiil
 */

require_once dirname(__DIR__, 3) . '/src/config/bootstrap.php';
requireAuth();

$pageTitle = 'Profiil';
$user = getCurrentUser();

require_once BASE_PATH . '/src/components/Header.php';
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-body">
                <h2>
                    <i class="bi bi-person me-2"></i><?php echo clean($user['username']); ?>
                </h2>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <p><strong>Email:</strong> <?php echo clean($user['email']); ?></p>
                        <p><strong>Reiting:</strong> ⭐ <?php echo $user['rating'] ?? 5; ?>/10</p>
                        <p><strong>Üritused:</strong> <?php echo $user['total_events'] ?? 0; ?></p>
                        <p><strong>Osales:</strong> <?php echo $user['attended_events'] ?? 0; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . '/src/components/Footer.php'; ?>
