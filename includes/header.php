<?php
/**
 * Шапка сайта (header)
 * Содержит навигацию, мета-теги, подключение стилей
 */

// Подключаем необходимые файлы
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// Получаем текущего пользователя
$currentUser = getCurrentUser();
$isLoggedIn = isLoggedIn();
?>
<!DOCTYPE html>
<html lang="et">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo isset($pageTitle) ? clean($pageTitle) . ' - ' : ''; ?>Spordiüritused</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Собственные стили -->
    <link rel="stylesheet" href="css/style.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="favicon.ico">
</head>
<body>
    <!-- Навигационное меню -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <i class="bi bi-trophy-fill me-2 fs-4"></i>
                <span class="fw-bold">Spordiüritused</span>
            </a>
            
            <!-- Mobiilmenüü nupp -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Lülita navigeerimine">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Меню навигации -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <!-- Avaleht -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" 
                           href="index.php">
                            <i class="bi bi-house-door me-1"></i> Avaleht
                        </a>
                    </li>
                    
                    <!-- Kohad -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'locations.php' ? 'active' : ''; ?>" 
                           href="locations.php">
                            <i class="bi bi-geo-alt me-1"></i> Kohad
                        </a>
                    </li>
                    
                    <?php if ($isLoggedIn): ?>
                        <!-- Loo üritus -->
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'create_event.php' ? 'active' : ''; ?>" 
                               href="create_event.php">
                                <i class="bi bi-plus-circle me-1"></i> Loo üritus
                            </a>
                        </li>
                        
                        <!-- Minu üritused -->
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'my_events.php' ? 'active' : ''; ?>" 
                               href="my_events.php">
                                <i class="bi bi-calendar-check me-1"></i> Minu üritused
                            </a>
                        </li>
                        
                        <!-- Выпадающее меню профиля -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="profileDropdown" 
                               role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php 
                                if (!empty($currentUser['avatar']) && file_exists('uploads/avatars/' . $currentUser['avatar'])) {
                                    $headerAvatar = 'uploads/avatars/' . $currentUser['avatar'];
                                } else {
                                    $headerAvatar = 'https://via.placeholder.com/32?text=' . urlencode(substr($currentUser['username'] ?? 'U', 0, 1));
                                }
                                ?>
                                <img src="<?php echo clean($headerAvatar); ?>" 
                                     alt="Аватар" class="rounded-circle me-2" width="32" height="32" 
                                     style="object-fit: cover;">
                                <?php echo clean($currentUser['username'] ?? 'Пользователь'); ?>
                                <span class="badge bg-warning text-dark ms-2">
                                    <i class="bi bi-star-fill"></i> <?php echo number_format($currentUser['rating'] ?? 5.0, 1); ?>
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                                <li>
                                    <a class="dropdown-item" href="profile.php">
                                        <i class="bi bi-person me-2"></i> Minu profiil
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="notifications.php">
                                        <i class="bi bi-bell me-2"></i> Teated
                                        <?php
                                        // Получаем количество непрочитанных уведомлений
                                        $unreadCount = fetchOne($pdo, 
                                            "SELECT COUNT(*) as count FROM notifications 
                                             WHERE user_id = ? AND is_read = 0", 
                                            [$currentUser['id']]
                                        );
                                        if ($unreadCount && $unreadCount['count'] > 0):
                                        ?>
                                            <span class="badge bg-danger ms-1"><?php echo $unreadCount['count']; ?></span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="logout.php">
                                        <i class="bi bi-box-arrow-right me-2"></i> Logi välja
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <!-- Sisselogimine ja registreerimine nupud -->
                        <li class="nav-item ms-lg-2">
                            <a class="nav-link" href="login.php">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Logi sisse
                            </a>
                        </li>
                        <li class="nav-item ms-lg-1">
                            <a class="btn btn-warning text-dark" href="register.php">
                                <i class="bi bi-person-plus me-1"></i> Registreeru
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Основной контейнер -->
    <main class="main-content">
        <div class="container py-4">
            <?php
            // Отображение flash-сообщений
            echo displayFlashMessage();
            ?>
