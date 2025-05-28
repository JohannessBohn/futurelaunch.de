<?php
/**
 * Authentication configuration file
 * Contains settings for admin authentication
 */

// Secure hashed password (default: admin123)
// To change password, generate a new hash using password_hash('new_password', PASSWORD_DEFAULT)
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD_HASH', '$2y$10$5nv5UXge4VCwm7miPeDp8OaQs9b6WJn/YxPUb.QcGH2WG8.ZIiG3O'); // Default: admin123

// Session security settings
define('SESSION_NAME', 'futurelaunch_admin');
define('SESSION_LIFETIME', 3600); // 1 hour in seconds
define('SESSION_SECURE', false); // Set to true if using HTTPS
define('SESSION_HTTP_ONLY', true);

// Login attempts protection
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 1800); // 30 minutes in seconds
