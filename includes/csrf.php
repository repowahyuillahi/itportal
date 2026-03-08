<?php
/**
 * csrf.php — CSRF token generation and validation
 */

/**
 * Generate or get CSRF token for current session
 */
function csrfToken(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

/**
 * Output hidden input field with CSRF token
 */
function csrfField(): string
{
    return '<input type="hidden" name="_csrf_token" value="' . e(csrfToken()) . '">';
}

/**
 * Verify CSRF token from POST
 * Dies with 403 on failure
 */
function verifyCsrf(): void
{
    $token = $_POST['_csrf_token'] ?? '';
    if (!hash_equals(csrfToken(), $token)) {
        http_response_code(403);
        die('CSRF token mismatch. Silakan refresh halaman dan coba lagi.');
    }
}
