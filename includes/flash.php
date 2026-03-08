<?php
/**
 * flash.php — Flash messages via session
 */

/**
 * Set a flash message
 */
function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear flash messages
 */
function getFlash(): array
{
    $messages = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $messages;
}

/**
 * Check if there are flash messages
 */
function hasFlash(): bool
{
    return !empty($_SESSION['_flash']);
}
