<?php
/**
 * Newsletter Admin System Configuration
 * Database and system configuration
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'tvs_newsletters');
define('DB_USER', 'your_db_user');  // Update with your database credentials
define('DB_PASS', 'your_db_pass');  // Update with your database credentials

// Use SQLite as fallback (no server setup needed)
define('USE_SQLITE', true);
define('DB_PATH', __DIR__ . '/../data/newsletters.db');

// Session configuration
define('SESSION_NAME', 'tvs_admin_session');
define('SESSION_LIFETIME', 3600); // 1 hour

// File paths
define('NEWSLETTERS_DIR', __DIR__ . '/../newsletters');
define('IMAGES_DIR', __DIR__ . '/../images');
define('NEWSCOVER_PATH', __DIR__ . '/../images/newscover.jpg');

// Admin credentials (change these!)
define('ADMIN_USERNAME', 'admin');
// Default password: changeme123
// To change password, use change_password.php or manually update this hash
define('ADMIN_PASSWORD_HASH', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); // changeme123

// PDF processing (ImageMagick or Ghostscript)
define('IMAGEMAGICK_PATH', '/usr/bin/convert'); // Linux/Mac
define('GHOSTSCRIPT_PATH', '/usr/bin/gs'); // Linux/Mac
// For Windows, you may need to set full paths or use PHP libraries

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

/**
 * Get database connection
 */
function getDB() {
    if (USE_SQLITE) {
        // Ensure directory exists
        $dbDir = dirname(DB_PATH);
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }
        
        try {
            $pdo = new PDO('sqlite:' . DB_PATH);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    } else {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            return $pdo;
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
}

/**
 * Initialize database schema
 */
function initDatabase() {
    $pdo = getDB();
    
    $sql = "CREATE TABLE IF NOT EXISTS newsletters (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        year INTEGER NOT NULL,
        month INTEGER NOT NULL,
        filename VARCHAR(255) NOT NULL,
        filepath VARCHAR(500) NOT NULL,
        uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        uploaded_by VARCHAR(100),
        UNIQUE(year, month)
    )";
    
    $pdo->exec($sql);
    
    // Create index for faster queries
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_year_month ON newsletters(year, month DESC)");
}

// Initialize database on first run
initDatabase();
?>

