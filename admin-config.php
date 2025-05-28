<?php
// Admin credentials
$admin_username = 'admin';
$admin_password = '!Admin1234'; // Change this to a strong password

// Session configuration
session_start();

// Check if user is logged in
function isAdmin() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Redirect to login if not logged in
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: admin-login.php');
        exit();
    }
}
?>
