<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$email = sanitizeInput($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validation
if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
    exit();
}

// Get faculty record
$query = "SELECT id, full_name, email, password, phone, department, 
                 is_active, approval_status, email_verified, profile_completed 
          FROM faculty 
          WHERE email = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    // Log failed attempt
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $logQuery = "INSERT INTO login_logs (user_type, user_id, identifier, status, details, ip_address) 
                 VALUES ('faculty', 0, ?, 'failed', 'Email not found', ?)";
    $logStmt = $conn->prepare($logQuery);
    $logStmt->bind_param("ss", $email, $ipAddress);
    $logStmt->execute();
    
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    exit();
}

$faculty = $result->fetch_assoc();

// Verify password
if (!password_verify($password, $faculty['password'])) {
    // Log failed attempt
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $logQuery = "INSERT INTO login_logs (user_type, user_id, identifier, status, details, ip_address) 
                 VALUES ('faculty', ?, ?, 'failed', 'Invalid password', ?)";
    $logStmt = $conn->prepare($logQuery);
    $logStmt->bind_param("iss", $faculty['id'], $email, $ipAddress);
    $logStmt->execute();
    
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    exit();
}

// Check if account is active
if ($faculty['is_active'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Your account has been deactivated. Please contact administrator.']);
    exit();
}

// Check email verification
if ($faculty['email_verified'] != 1) {
    echo json_encode([
        'success' => false, 
        'message' => 'Please verify your email first. Check your inbox for the verification link.'
    ]);
    exit();
}

// Check approval status
if ($faculty['approval_status'] === 'pending') {
    echo json_encode([
        'success' => false,
        'message' => 'Your account is pending admin approval. You will receive an email once approved.'
    ]);
    exit();
}

if ($faculty['approval_status'] === 'rejected') {
    echo json_encode([
        'success' => false,
        'message' => 'Your account registration was not approved. Please contact administrator for more information.'
    ]);
    exit();
}

// Successful login
$_SESSION['faculty_id'] = $faculty['id'];
$_SESSION['faculty_name'] = $faculty['full_name'];
$_SESSION['faculty_email'] = $faculty['email'];

// Update last login
$updateQuery = "UPDATE faculty SET last_login = NOW() WHERE id = ?";
$updateStmt = $conn->prepare($updateQuery);
$updateStmt->bind_param("i", $faculty['id']);
$updateStmt->execute();

// Log successful login
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$logQuery = "INSERT INTO login_logs (user_type, user_id, identifier, status, details, ip_address) 
             VALUES ('faculty', ?, ?, 'success', 'Login successful', ?)";
$logStmt = $conn->prepare($logQuery);
$logStmt->bind_param("iss", $faculty['id'], $email, $ipAddress);
$logStmt->execute();

// Log activity
$activityQuery = "INSERT INTO activity_logs (admin_id, action, details, ip_address) 
                  VALUES (0, 'FACULTY_LOGIN', ?, ?)";
$activityStmt = $conn->prepare($activityQuery);
$activityDetails = "Faculty login: {$faculty['full_name']} ({$faculty['email']})";
$activityStmt->bind_param("ss", $activityDetails, $ipAddress);
$activityStmt->execute();

// Determine redirect
if ($faculty['profile_completed'] == 0) {
    $redirect = 'complete_profile.php';
} else {
    $redirect = 'faculty_dashboard.php';
}

echo json_encode([
    'success' => true,
    'message' => 'Login successful!',
    'redirect' => $redirect
]);

$conn->close();
?>