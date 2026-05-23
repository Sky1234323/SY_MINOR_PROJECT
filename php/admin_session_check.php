<?php
// Admin Session Check - Include this at the top of all admin pages

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    // Not logged in, redirect to login page
    header("Location: admin_login.php");
    exit();
}

// Optional: Check session timeout (30 minutes)
$timeout_duration = 1800; // 30 minutes in seconds

if (isset($_SESSION['admin_login_time'])) {
    $elapsed_time = time() - strtotime($_SESSION['admin_login_time']);
    
    if ($elapsed_time > $timeout_duration) {
        // Session expired
        session_unset();
        session_destroy();
        header("Location: admin_login.php?timeout=1");
        exit();
    }
}

// Update last activity time
$_SESSION['admin_last_activity'] = time();

// Optional: Regenerate session ID periodically for security
if (!isset($_SESSION['admin_session_created'])) {
    $_SESSION['admin_session_created'] = time();
} else if (time() - $_SESSION['admin_session_created'] > 3600) {
    // Regenerate session ID every hour
    session_regenerate_id(true);
    $_SESSION['admin_session_created'] = time();
}
?>