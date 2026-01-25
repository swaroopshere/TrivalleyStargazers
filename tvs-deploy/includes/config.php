<?php
/**
 * TVS Website Configuration
 *
 * This file contains all configuration settings for the website.
 * Sensitive values should be set via environment variables in production.
 */

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.cookie_secure', 1);  // Only send cookies over HTTPS
ini_set('session.use_strict_mode', 1);  // Reject uninitialized session IDs
ini_set('session.gc_maxlifetime', 1800);  // 30 minutes

// Site configuration
define('SITE_NAME', 'Tri-Valley Stargazers');
define('SITE_URL', 'https://trivalleystargazers.org');
define('SITE_EMAIL', 'secretary@trivalleystargazers.org');

// Base path for URLs (auto-detect from script path)
$scriptPath = dirname($_SERVER['SCRIPT_NAME'] ?? '');
$basePath = ($scriptPath === '/' || $scriptPath === '\\') ? '' : preg_replace('#/admin$#', '', $scriptPath);
define('BASE_PATH', getenv('TVS_BASE_PATH') ?: $basePath);

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('DATA_PATH', ROOT_PATH . '/data');
define('ADMIN_PATH', ROOT_PATH . '/admin');
define('NEWSLETTERS_PATH', ROOT_PATH . '/newsletters');

// MySQL Database Configuration
// Credentials loaded from environment variables (set in .htaccess)
define('DB_HOST', getenv('TVS_DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('TVS_DB_NAME') ?: '');
define('DB_USER', getenv('TVS_DB_USER') ?: '');
define('DB_PASS', getenv('TVS_DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// Security
define('SESSION_TIMEOUT', 30 * 60); // 30 minutes
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 15 * 60); // 15 minutes
define('CSRF_TOKEN_NAME', 'csrf_token');

// Groups.io API (set via environment variable)
define('GROUPS_IO_API_KEY', getenv('GROUPS_IO_API_KEY') ?: '');
define('GROUPS_IO_GROUP', 'trivalleystargazers');

// Meeting location defaults
define('DEFAULT_MEETING_LOCATION', 'Unitarian Universalist Church');
define('DEFAULT_MEETING_ADDRESS', '1893 N. Vasco Rd., Livermore');

// File upload settings
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10 MB
define('ALLOWED_UPLOAD_TYPES', ['application/pdf']);

// Month names for display
define('MONTH_NAMES', [
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
]);

// User roles
define('ROLE_ADMIN', 'admin');
define('ROLE_PUBLISHER', 'publisher');
define('ROLE_VIEWER', 'viewer');

// Timezone
date_default_timezone_set('America/Los_Angeles');
