<?php
/**
 * bootstrap.php — Application bootstrap
 * Loaded on every request
 */

// Error reporting (auto-detect from APP_ENV)
error_reporting(E_ALL);
ini_set('display_errors', getenv('APP_ENV') === 'production' ? '0' : '1');
ini_set('log_errors', '1');

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load config
require_once __DIR__ . '/../config/config.php';

// Load includes
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/flash.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/validator.php';
require_once __DIR__ . '/uploader.php';
require_once __DIR__ . '/ticket_helpers.php';
require_once __DIR__ . '/NotificationService.php';
