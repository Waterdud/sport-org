<?php
/**
 * Header компонент - навигация и шапка сайта
 */

$currentUser = getCurrentUser();
$isLoggedIn = isLoggedIn();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="et">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo isset($pageTitle) ? clean($pageTitle) . ' - ' : ''; ?>SportOrg</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand d-flex align-items-center" href="<?php echo SITE_URL; ?>/">
                <i class="bi bi-trophy-fill me-2 fs-4"></i>
                <span class="fw-bold">SportOrg</span>
            </a>
            
            <!-- Mobile Toggle -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navigation Menu -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <!-- Avaleht (Home) -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/">
                            <i class="bi bi-house-door me-1"></i> Avaleht
                        </a>
                    </li>
                    
                    <!-- Treeningud (Events) -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/events/list.php">
                            <i class="bi bi-calendar-event me-1"></i> Treeningud
                        </a>
                    </li>
                    
                    <!-- Kohad (Locations) -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/locations/list.php">
                            <i class="bi bi-geo-alt me-1"></i> Kohad
                        </a>
                    </li>
                    
                    <?php if ($isLoggedIn): ?>
                        <!-- Loo üritus (Create Event) -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/events/create.php">
                                <i class="bi bi-plus-circle me-1"></i> Loo üritus
                            </a>
                        </li>
                        
                        <!-- Minu treeningud (My Events) -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/events/my.php">
                                <i class="bi bi-calendar-check me-1"></i> Minu treeningud
                            </a>
                        </li>
                        
                        <!-- Teated (Notifications) -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/user/notifications.php">
                                <i class="bi bi-bell me-1"></i> 
                                <span id="notificationCount" class="badge bg-danger">0</span>
                            </a>
                        </li>
                        
                        <!-- Profiil (Profile) -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1"></i> <?php echo clean($currentUser['username']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/user/profile.php">
                                    <i class="bi bi-person me-2"></i> Profiil
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/auth/logout.php">
                                    <i class="bi bi-box-arrow-right me-2"></i> Logi välja
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <!-- Logi sisse (Login) -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/auth/login.php">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Logi sisse
                            </a>
                        </li>
                        
                        <!-- Registreeru (Register) -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/auth/register.php">
                                <i class="bi bi-person-plus me-1"></i> Registreeru
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="main-content">
        <div class="container my-4">
