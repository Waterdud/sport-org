<?php
/**
 * Router for PHP Development Server
 * 
 * Use with: php -S localhost:8000 router.php
 * Routes all requests through index.php for clean URL support
 */

$uri = $_SERVER["REQUEST_URI"];
$requested_file = __DIR__ . parse_url($uri, PHP_URL_PATH);

// Serve static files directly (if they exist)
if (file_exists($requested_file) && is_file($requested_file)) {
    // Check if it's a static file type
    if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|webp|woff|woff2|ttf|eot|db)$/i', $requested_file)) {
        return false; // Let the PHP server serve the static file
    }
}

// Route everything else through index.php
require __DIR__ . '/index.php';
