<?php
/**
 * Admin Authentication Helper
 */

function isAdmin() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';
}

function requireAdmin() {
    if (!isAdmin()) {
        header('HTTP/1.0 403 Forbidden');
        die('Access denied. Admin privileges required.');
    }
}

function canAccessAdmin() {
    return isLoggedIn() && isAdmin();
}
?>
