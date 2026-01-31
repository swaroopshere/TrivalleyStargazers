<?php
/**
 * PHPUnit Bootstrap File
 * Sets up the test environment
 */

// Define test environment
define('TEST_ENVIRONMENT', true);

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Define constants needed by the application
define('ROOT_PATH', dirname(__DIR__, 2));
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('DATA_PATH', ROOT_PATH . '/data');

// Database constants for testing (use SQLite in-memory for tests)
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'tvs_test');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

// Security constants
define('SESSION_TIMEOUT', 1800);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900);
define('CSRF_TOKEN_NAME', 'csrf_token');

// Role constants
define('ROLE_ADMIN', 'admin');
define('ROLE_PUBLISHER', 'publisher');
define('ROLE_VIEWER', 'viewer');

// Upload constants
define('MAX_UPLOAD_SIZE', 10485760);
define('ALLOWED_UPLOAD_TYPES', ['application/pdf']);

// Month names
define('MONTH_NAMES', [
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
]);

// Set timezone
date_default_timezone_set('America/Los_Angeles');

// Start session for tests that need it
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
