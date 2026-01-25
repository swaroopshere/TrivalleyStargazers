<?php
/**
 * TVS Authentication System
 *
 * Handles user authentication, session management, and security
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

class Auth {
    private static $instance = null;
    private $user = null;

    private function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->checkSession();
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Check and refresh session
     */
    private function checkSession(): void {
        if (isset($_SESSION['user_id'])) {
            // Check session timeout
            if (isset($_SESSION['last_activity']) &&
                (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
                $this->logout();
                return;
            }
            $_SESSION['last_activity'] = time();

            // Load user data
            $this->user = dbQueryOne(
                "SELECT id, username, email, role, is_active FROM users WHERE id = ? AND is_active = 1",
                [$_SESSION['user_id']]
            );

            if (!$this->user) {
                $this->logout();
            }
        }
    }

    /**
     * Attempt to log in a user
     */
    public function login(string $username, string $password): array {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';

        // Check for rate limiting
        if ($this->isLockedOut($username, $ip)) {
            return ['success' => false, 'error' => 'Too many login attempts. Please try again later.'];
        }

        // Get user
        $user = dbQueryOne(
            "SELECT * FROM users WHERE username = ? AND is_active = 1",
            [$username]
        );

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->recordLoginAttempt($username, $ip, false);
            return ['success' => false, 'error' => 'Invalid username or password.'];
        }

        // Successful login
        $this->recordLoginAttempt($username, $ip, true);
        $this->createSession($user);

        // Update last login
        dbExecute(
            "UPDATE users SET last_login = NOW() WHERE id = ?",
            [$user['id']]
        );

        logAudit($user['id'], 'login', 'users', $user['id']);

        return ['success' => true];
    }

    /**
     * Log out the current user
     */
    public function logout(): void {
        if ($this->user) {
            logAudit($this->user['id'], 'logout', 'users', $this->user['id']);
        }

        $this->user = null;
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn(): bool {
        return $this->user !== null;
    }

    /**
     * Get current user
     */
    public function getUser(): ?array {
        return $this->user;
    }

    /**
     * Get current user ID
     */
    public function getUserId(): int {
        return $this->user ? (int)$this->user['id'] : 0;
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool {
        if (!$this->user) return false;

        // Admin has all roles
        if ($this->user['role'] === ROLE_ADMIN) return true;

        return $this->user['role'] === $role;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool {
        return $this->hasRole(ROLE_ADMIN);
    }

    /**
     * Create a new session for user
     */
    private function createSession(array $user): void {
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['last_activity'] = time();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        $this->user = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'],
            'is_active' => $user['is_active']
        ];
    }

    /**
     * Check if username/IP is locked out
     */
    private function isLockedOut(string $username, string $ip): bool {
        $cutoff = date('Y-m-d H:i:s', time() - LOGIN_LOCKOUT_TIME);

        $attempts = dbQueryOne(
            "SELECT COUNT(*) as count FROM login_attempts
             WHERE (username = ? OR ip_address = ?)
             AND success = 0 AND attempted_at > ?",
            [$username, $ip, $cutoff]
        );

        return ($attempts['count'] ?? 0) >= MAX_LOGIN_ATTEMPTS;
    }

    /**
     * Record a login attempt
     */
    private function recordLoginAttempt(string $username, string $ip, bool $success): void {
        dbInsert(
            "INSERT INTO login_attempts (username, ip_address, success) VALUES (?, ?, ?)",
            [$username, $ip, $success ? 1 : 0]
        );
    }

    /**
     * Generate CSRF token
     */
    public function getCSRFToken(): string {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate CSRF token
     */
    public function validateCSRF(string $token): bool {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Validate password strength
     */
    private function validatePassword(string $password): array {
        if (strlen($password) < 12) {
            return ['valid' => false, 'error' => 'Password must be at least 12 characters.'];
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return ['valid' => false, 'error' => 'Password must contain at least one uppercase letter.'];
        }
        if (!preg_match('/[a-z]/', $password)) {
            return ['valid' => false, 'error' => 'Password must contain at least one lowercase letter.'];
        }
        if (!preg_match('/[0-9]/', $password)) {
            return ['valid' => false, 'error' => 'Password must contain at least one number.'];
        }
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            return ['valid' => false, 'error' => 'Password must contain at least one special character.'];
        }
        // Check for common passwords
        $commonPasswords = ['password123', 'admin123', '123456789', 'qwerty123'];
        if (in_array(strtolower($password), $commonPasswords)) {
            return ['valid' => false, 'error' => 'Password is too common. Please choose a stronger password.'];
        }
        return ['valid' => true];
    }

    /**
     * Create a new user
     */
    public function createUser(string $username, string $password, string $email, string $role = ROLE_PUBLISHER): array {
        // Validate inputs
        if (strlen($username) < 3) {
            return ['success' => false, 'error' => 'Username must be at least 3 characters.'];
        }

        $passwordValidation = $this->validatePassword($password);
        if (!$passwordValidation['valid']) {
            return ['success' => false, 'error' => $passwordValidation['error']];
        }

        // Check if username exists
        $existing = dbQueryOne("SELECT id FROM users WHERE username = ?", [$username]);
        if ($existing) {
            return ['success' => false, 'error' => 'Username already exists.'];
        }

        // Create user
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $userId = dbInsert(
            "INSERT INTO users (username, password_hash, email, role) VALUES (?, ?, ?, ?)",
            [$username, $passwordHash, $email, $role]
        );

        logAudit($this->getUserId(), 'create_user', 'users', $userId);

        return ['success' => true, 'user_id' => $userId];
    }

    /**
     * Update user password
     */
    public function updatePassword(int $userId, string $newPassword): array {
        $passwordValidation = $this->validatePassword($newPassword);
        if (!$passwordValidation['valid']) {
            return ['success' => false, 'error' => $passwordValidation['error']];
        }

        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        dbExecute(
            "UPDATE users SET password_hash = ? WHERE id = ?",
            [$passwordHash, $userId]
        );

        logAudit($this->getUserId(), 'update_password', 'users', $userId);

        return ['success' => true];
    }

    /**
     * Update user details
     */
    public function updateUser(int $userId, string $email, string $role): array {
        dbExecute(
            "UPDATE users SET email = ?, role = ? WHERE id = ?",
            [$email, $role, $userId]
        );

        logAudit($this->getUserId(), 'update_user', 'users', $userId);

        return ['success' => true];
    }

    /**
     * Deactivate a user
     */
    public function deactivateUser(int $userId): void {
        dbExecute("UPDATE users SET is_active = 0 WHERE id = ?", [$userId]);
        logAudit($this->getUserId(), 'deactivate_user', 'users', $userId);
    }

    /**
     * Activate a user
     */
    public function activateUser(int $userId): void {
        dbExecute("UPDATE users SET is_active = 1 WHERE id = ?", [$userId]);
        logAudit($this->getUserId(), 'activate_user', 'users', $userId);
    }
}

// Helper functions

function auth(): Auth {
    return Auth::getInstance();
}

/**
 * Require authentication - redirect to login if not logged in
 */
function requireAuth(): void {
    if (!auth()->isLoggedIn()) {
        header('Location: ' . BASE_PATH . '/admin/login.php');
        exit;
    }
}

/**
 * Require admin role
 */
function requireAdmin(): void {
    requireAuth();
    if (!auth()->isAdmin()) {
        header('HTTP/1.1 403 Forbidden');
        echo 'Access denied. Administrator privileges required.';
        exit;
    }
}

/**
 * Generate CSRF field for forms
 */
function csrfField(): string {
    $token = auth()->getCSRFToken();
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . htmlspecialchars($token) . '">';
}

/**
 * Validate CSRF from POST
 */
function validateCSRF(): bool {
    $token = $_POST[CSRF_TOKEN_NAME] ?? '';
    return auth()->validateCSRF($token);
}

/**
 * Check CSRF and die if invalid
 */
function requireCSRF(): void {
    if (!validateCSRF()) {
        header('HTTP/1.1 403 Forbidden');
        echo 'Invalid security token. Please refresh and try again.';
        exit;
    }
}
