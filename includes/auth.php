<?php
/**
 * auth.php — Session-based authentication helpers
 */

/**
 * Attempt login with username and password.
 * Returns user array on success, false on failure.
 */
function attemptLogin(string $username, string $password)
{
    $stmt = db()->prepare('SELECT * FROM users WHERE username = ? AND is_active = 1 LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        return $user;
    }
    return false;
}

/**
 * Logout the current user
 */
function logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

/**
 * Get current logged-in user data from DB
 */
function currentUser(): ?array
{
    if (!isLoggedIn())
        return null;
    static $user = null;
    if ($user === null) {
        $stmt = db()->prepare('SELECT * FROM users WHERE id = ? AND is_active = 1 LIMIT 1');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;
    }
    return $user;
}

/**
 * Get current user's role
 */
function currentRole(): string
{
    return $_SESSION['role'] ?? '';
}

/**
 * Require user to be logged in, redirect to login if not
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        flash('error', 'Silakan login terlebih dahulu.');
        redirect(url('/login'));
    }
}

/**
 * Require specific role(s). Redirects to 403 if unauthorized.
 */
function requireRole(string ...$roles): void
{
    requireLogin();
    if (!in_array(currentRole(), $roles, true)) {
        http_response_code(403);
        include BASE_PATH . '/pages/errors/403.php';
        exit;
    }
}

/**
 * Check if current user has any of the given roles
 */
function hasRole(string ...$roles): bool
{
    return in_array(currentRole(), $roles, true);
}
