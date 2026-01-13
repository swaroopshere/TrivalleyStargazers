<?php
/**
 * Authentication functions for newsletter admin
 */
require_once __DIR__ . '/config.php';

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Require login - redirect if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Login user
 */
function login($username, $password) {
    // Simple authentication (you can enhance this with database lookup)
    if ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD_HASH)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        $_SESSION['login_time'] = time();
        return true;
    }
    return false;
}

/**
 * Logout user
 */
function logout() {
    $_SESSION = array();
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    session_destroy();
}

/**
 * Check session timeout
 */
function checkSessionTimeout() {
    if (isset($_SESSION['login_time'])) {
        if (time() - $_SESSION['login_time'] > SESSION_LIFETIME) {
            logout();
            return false;
        }
        // Update last activity time
        $_SESSION['login_time'] = time();
    }
    return true;
}
?>

