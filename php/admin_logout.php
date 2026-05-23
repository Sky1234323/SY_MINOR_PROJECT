<?php
// Start session
session_start();

// Include database connection
require_once 'db_connect.php';

// Log logout activity
if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'Unknown';
    $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown';
    
    // Log activity
    $logQuery = "INSERT INTO activity_logs (admin_id, action, details, ip_address, user_agent) 
                 VALUES (?, 'ADMIN_LOGOUT', 'Admin logged out', ?, ?)";
    executeUpdate($logQuery, "iss", array($admin_id, $ip, $userAgent));
}

// Destroy all session data
$_SESSION = array();

// Delete session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy session
session_destroy();

// Redirect to login page
header("Location: ../admin/admin_login.php?logged_out=1");
exit();
?>