<?php
/**
 * helpers.php — Common utility functions
 */

/**
 * Escape HTML output (anti XSS)
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Generate URL relative to app base
 */
function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

/**
 * Asset URL helper
 */
function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

/**
 * Redirect to a URL
 */
function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/**
 * Get old input value (from flash)
 */
function old(string $key, string $default = ''): string
{
    return $_SESSION['_old_input'][$key] ?? $default;
}

/**
 * Store old input to session (before redirect)
 */
function storeOldInput(): void
{
    $_SESSION['_old_input'] = $_POST;
}

/**
 * Clear old input
 */
function clearOldInput(): void
{
    unset($_SESSION['_old_input']);
}

/**
 * Format datetime to display format
 */
function formatDate(?string $datetime, string $format = 'd M Y H:i'): string
{
    if (!$datetime)
        return '-';
    return date($format, strtotime($datetime));
}

/**
 * Time ago format
 */
function timeAgo(string $datetime): string
{
    $diff = time() - strtotime($datetime);
    if ($diff < 60)
        return 'Baru saja';
    if ($diff < 3600)
        return floor($diff / 60) . ' menit lalu';
    if ($diff < 86400)
        return floor($diff / 3600) . ' jam lalu';
    if ($diff < 604800)
        return floor($diff / 86400) . ' hari lalu';
    return formatDate($datetime);
}

/**
 * Status badge HTML
 */
function statusBadge(string $status): string
{
    $map = [
        'open' => ['bg-blue', 'Open'],
        'in_progress' => ['bg-yellow', 'In Progress'],
        'waiting' => ['bg-orange', 'Waiting'],
        'resolved' => ['bg-green', 'Resolved'],
        'closed' => ['bg-secondary', 'Closed'],
    ];
    $s = $map[$status] ?? ['bg-secondary', ucfirst(str_replace('_', ' ', $status))];
    return '<span class="badge ' . $s[0] . '">' . e($s[1]) . '</span>';
}

/**
 * Priority badge HTML
 */
function priorityBadge(string $priority): string
{
    $map = [
        'low' => ['bg-green', 'Low'],
        'medium' => ['bg-blue', 'Medium'],
        'high' => ['bg-orange', 'High'],
        'urgent' => ['bg-red', 'Urgent'],
    ];
    $p = $map[$priority] ?? ['bg-secondary', ucfirst($priority)];
    return '<span class="badge ' . $p[0] . '">' . e($p[1]) . '</span>';
}

/**
 * Truncate string
 */
function truncate(string $text, int $length = 50): string
{
    if (mb_strlen($text) <= $length)
        return $text;
    return mb_substr($text, 0, $length) . '...';
}

/**
 * Check current page for active sidebar
 */
function isActivePage(string $path): string
{
    $current = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $base = parse_url(BASE_URL, PHP_URL_PATH);
    $current = str_replace($base, '', $current);
    $current = '/' . ltrim($current, '/');
    if ($path === $current)
        return 'active';
    if ($path !== '/' && str_starts_with($current, $path))
        return 'active';
    return '';
}
