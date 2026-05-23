<?php
session_start();

// Check if faculty is logged in
if (!isset($_SESSION['faculty_id'])) {
    header("Location: faculty_login.php");
    exit();
}

// Verify faculty still exists and is active
require_once '../php/db_connect.php';

$query = "SELECT id, is_active FROM faculty WHERE id = ?";
$result = executeQuery($query, "i", array($_SESSION['faculty_id']));

if (!$result || $result->num_rows == 0) {
    session_destroy();
    header("Location: faculty_login.php");
    exit();
}

$faculty = $result->fetch_assoc();

if ($faculty['is_active'] == 0) {
    session_destroy();
    header("Location: faculty_login.php?msg=Account+Disabled");
    exit();
}

// Session timeout - 30 minutes of inactivity
$timeout = 1800; // 30 minutes in seconds
if (isset($_SESSION['last_activity'])) {
    $elapsed = time() - $_SESSION['last_activity'];
    if ($elapsed > $timeout) {
        session_destroy();
        header("Location: faculty_login.php?msg=Session+Expired");
        exit();
    }
}

// Update last activity
$_SESSION['last_activity'] = time();

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
?>