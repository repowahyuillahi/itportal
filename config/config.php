<?php
/**
 * config.php — load .env and set app config
 */

// Parse .env file
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);
        // Remove surrounding quotes
        if (preg_match('/^"(.*)"$/', $value, $m)) $value = $m[1];
        if (preg_match("/^'(.*)'$/", $value, $m)) $value = $m[1];
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

/**
 * Get config value from ENV
 */
function config(string $key, $default = null)
{
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

// App config constants
define('APP_NAME',    config('APP_NAME', 'IT Portal Helpdesk'));
define('APP_ENV',     config('APP_ENV', 'local'));
define('APP_URL',     rtrim(config('APP_URL', 'http://localhost/itportal'), '/'));
define('BASE_PATH',   realpath(__DIR__ . '/..'));
define('BASE_URL',    APP_URL);

// DB
define('DB_HOST', config('DB_HOST', 'localhost'));
define('DB_PORT', config('DB_PORT', '3306'));
define('DB_NAME', config('DB_NAME', 'itportal'));
define('DB_USER', config('DB_USER', 'root'));
define('DB_PASS', config('DB_PASS', ''));

// Upload
define('UPLOAD_MAX_MB', (int) config('UPLOAD_MAX_MB', 10));
define('UPLOAD_DIR',    BASE_PATH . '/storage/uploads');

// Token secret
define('TOKEN_SECRET', config('TOKEN_SECRET', 'default-secret'));

// WA
define('WA_NUMBER_DEFAULT', config('WA_NUMBER_DEFAULT', '62xxxxxxxxxx'));
