<?php
/**
 * index.php — Front Controller (Page-Based Router)
 */

require_once __DIR__ . '/includes/bootstrap.php';

// Parse the request URI
$requestUri = $_SERVER['REQUEST_URI'];
$basePath = parse_url(BASE_URL, PHP_URL_PATH) ?: '';
$path = parse_url($requestUri, PHP_URL_PATH);

// Remove base path
if ($basePath && str_starts_with($path, $basePath)) {
    $path = substr($path, strlen($basePath));
}
$path = '/' . ltrim($path, '/');

// Remove trailing slash (except root)
if ($path !== '/' && str_ends_with($path, '/')) {
    $path = rtrim($path, '/');
}

// Route mapping: URL path -> page file
$routes = [
    '/' => 'pages/dashboard/index.php',
    '/login' => 'pages/auth/login.php',
    '/logout' => 'pages/auth/logout.php',
    '/dashboard' => 'pages/dashboard/index.php',
    // Tickets (removed — maintenance only)
    // Profile
    '/profile' => 'pages/profile/index.php',
    // Maintenance Reports
    '/maintenance' => 'pages/maintenance/index.php',
    '/maintenance/view' => 'pages/maintenance/view.php',
    '/maintenance/create' => 'pages/maintenance/create.php',
    '/maintenance/edit' => 'pages/maintenance/edit.php',
    // Data Master
    '/dealers' => 'pages/dealers/index.php',
    '/divisi' => 'pages/divisi/index.php',
    '/sertifikat' => 'pages/sertifikat/index.php',
    '/sertifikat/download' => 'pages/sertifikat/download.php',
    '/sertifikat/debug' => 'pages/sertifikat/debug_dl.php',
    '/zahir' => 'pages/zahir/index.php',
    // Public
    '/track' => 'pages/track/index.php',
    '/wa' => 'pages/wa/index.php',
    // Admin
    '/admin/users' => 'pages/admin/users.php',
    '/admin/settings' => 'pages/admin/settings.php',
];

// Public routes (no login required)
$publicRoutes = ['/login', '/track', '/wa'];

// Find matching route
$pageFile = $routes[$path] ?? null;

if ($pageFile && file_exists(BASE_PATH . '/' . $pageFile)) {
    // Auth check for non-public routes
    if (!in_array($path, $publicRoutes, true)) {
        requireLogin();
    }
    require BASE_PATH . '/' . $pageFile;
} else {
    http_response_code(404);
    require BASE_PATH . '/pages/errors/404.php';
}

