<?php
/**
 * Authentication helper functions
 */

// Load configuration
require_once __DIR__ . '/../config/auth_config.php';

/**
 * Initialize secure session
 */
function initSession() {
    // Set secure session parameters
    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_trans_sid', 0);
    ini_set('session.cookie_httponly', SESSION_HTTP_ONLY);
    ini_set('session.cookie_secure', SESSION_SECURE);
    ini_set('session.cookie_lifetime', SESSION_LIFETIME);
    
    // Set session name
    session_name(SESSION_NAME);
    
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Verify login credentials
 * 
 * @param string $username Username input
 * @param string $password Password input
 * @return bool True if credentials are valid
 */
function verifyLogin($username, $password) {
    // Check login attempts
    if (isLockedOut()) {
        return false;
    }
    
    // Verify credentials
    if ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD_HASH)) {
        // Reset login attempts on success
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_login_attempt'] = null;
        
        // Set logged in status
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = ADMIN_USERNAME;
        $_SESSION['login_time'] = time();
        
        return true;
    }
    
    // Increment failed login attempts
    incrementLoginAttempt();
    return false;
}

/**
 * Check if user is authenticated
 * 
 * @return bool True if user is logged in
 */
function isAuthenticated() {
    return (
        isset($_SESSION['logged_in']) && 
        $_SESSION['logged_in'] === true && 
        isset($_SESSION['login_time']) && 
        (time() - $_SESSION['login_time']) < SESSION_LIFETIME
    );
}

/**
 * Refresh the session timeout
 */
function refreshSession() {
    if (isAuthenticated()) {
        $_SESSION['login_time'] = time();
    }
}

/**
 * Log the user out
 */
function logout() {
    // Unset all session variables
    $_SESSION = [];
    
    // Delete the session cookie
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), 
            '', 
            time() - 42000,
            $params['path'], 
            $params['domain'],
            $params['secure'], 
            $params['httponly']
        );
    }
    
    // Destroy the session
    session_destroy();
}

/**
 * Check if the user is locked out due to too many failed login attempts
 * 
 * @return bool True if user is locked out
 */
function isLockedOut() {
    if (
        isset($_SESSION['login_attempts']) && 
        $_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS && 
        isset($_SESSION['last_login_attempt']) && 
        (time() - $_SESSION['last_login_attempt']) < LOGIN_LOCKOUT_TIME
    ) {
        return true;
    }
    
    // Reset lockout if time has passed
    if (
        isset($_SESSION['last_login_attempt']) && 
        (time() - $_SESSION['last_login_attempt']) >= LOGIN_LOCKOUT_TIME
    ) {
        $_SESSION['login_attempts'] = 0;
    }
    
    return false;
}

/**
 * Increment failed login attempts
 */
function incrementLoginAttempt() {
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
    }
    
    $_SESSION['login_attempts']++;
    $_SESSION['last_login_attempt'] = time();
}

/**
 * Get remaining lockout time in minutes
 * 
 * @return int Remaining lockout time in minutes
 */
function getRemainingLockoutTime() {
    if (!isset($_SESSION['last_login_attempt'])) {
        return 0;
    }
    
    $elapsedTime = time() - $_SESSION['last_login_attempt'];
    $remainingTime = LOGIN_LOCKOUT_TIME - $elapsedTime;
    
    return max(0, ceil($remainingTime / 60));
}

/**
 * Require authentication or redirect to login page
 * 
 * @param string $loginPage Login page URL
 */
function requireAuth($loginPage = '/dashboard.php') {
    initSession();
    
    if (!isAuthenticated()) {
        header("Location: $loginPage");
        exit;
    }
    
    refreshSession();
}
