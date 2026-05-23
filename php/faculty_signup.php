<?php
session_start();
require_once 'db_connect.php';
require_once 'email_config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get form data
$fullName = sanitizeInput($_POST['full_name'] ?? '');
$email = sanitizeInput($_POST['email'] ?? '');
$phone = sanitizeInput($_POST['phone'] ?? '');
$department = sanitizeInput($_POST['department'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// Validation
if (empty($fullName) || empty($email) || empty($password) || empty($confirmPassword)) {
    echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit();
}

if ($password !== $confirmPassword) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
    exit();
}

if (strlen($password) < 8) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long']);
    exit();
}

// Check if email already exists in faculty table
$checkQuery = "SELECT id, approval_status, email_verified FROM faculty WHERE email = ?";
$checkStmt = $conn->prepare($checkQuery);
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult && $checkResult->num_rows > 0) {
    $existing = $checkResult->fetch_assoc();
    
    if ($existing['approval_status'] === 'pending') {
        echo json_encode([
            'success' => false, 
            'message' => 'An account with this email is already pending approval. Please check your email for verification link.'
        ]);
        exit();
    } elseif ($existing['approval_status'] === 'approved') {
        echo json_encode([
            'success' => false, 
            'message' => 'An account with this email already exists. Please login instead.'
        ]);
        exit();
    } elseif ($existing['approval_status'] === 'rejected') {
        echo json_encode([
            'success' => false, 
            'message' => 'Your previous registration was rejected. Please contact administrator for more information.'
        ]);
        exit();
    }
}

// Check if teacher with same email exists in teachers table
$teacherQuery = "SELECT id, first_name, middle_name, last_name, phone, department FROM teachers WHERE email = ?";
$teacherStmt = $conn->prepare($teacherQuery);
$teacherStmt->bind_param("s", $email);
$teacherStmt->execute();
$teacherResult = $teacherStmt->get_result();
$teacherId = null;

if ($teacherResult && $teacherResult->num_rows > 0) {
    $teacher = $teacherResult->fetch_assoc();
    $teacherId = $teacher['id'];
    
    // Verify if name matches (optional strict check - can be commented out)
    $teacherFullName = trim($teacher['first_name'] . ' ' . ($teacher['middle_name'] ?? '') . ' ' . $teacher['last_name']);
    if (strtolower(trim($fullName)) !== strtolower(trim($teacherFullName))) {
        echo json_encode([
            'success' => false,
            'message' => 'A teacher record exists with this email but the name does not match. Please contact administrator.'
        ]);
        exit();
    }
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Generate email verification token
$verificationToken = generateVerificationToken();
$tokenExpiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

// Insert into faculty table
$insertQuery = "INSERT INTO faculty (
    full_name, email, password, phone, department, 
    teacher_id, approval_status, email_verified, 
    email_verification_token, email_token_expiry, is_active
) VALUES (?, ?, ?, ?, ?, ?, 'pending', 0, ?, ?, 1)";

$insertStmt = $conn->prepare($insertQuery);
$insertStmt->bind_param(
    "sssssiss", 
    $fullName, $email, $hashedPassword, $phone, $department, $teacherId, $verificationToken, $tokenExpiry
);

if ($insertStmt->execute()) {
    $facultyId = $conn->insert_id;
    
    // If no teacher record exists, create one
    if ($teacherId === null) {
        // Parse name
        $nameParts = explode(' ', trim($fullName));
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[count($nameParts) - 1] ?? '';
        $middleName = count($nameParts) > 2 ? implode(' ', array_slice($nameParts, 1, -1)) : null;
        
        $teacherInsertQuery = "INSERT INTO teachers (
            first_name, middle_name, last_name, email, phone, 
            gender, department, designation, qualification, experience, is_active
        ) VALUES (?, ?, ?, ?, ?, 'Other', ?, 'Faculty', 'To be updated', 0, 1)";
        
        $teacherInsertStmt = $conn->prepare($teacherInsertQuery);
        $teacherInsertStmt->bind_param(
            "ssssss",
            $firstName, $middleName, $lastName, $email, $phone, $department
        );
        
        if ($teacherInsertStmt->execute()) {
            $newTeacherId = $conn->insert_id;
            
            // Update faculty record with new teacher_id
            $updateQuery = "UPDATE faculty SET teacher_id = ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("ii", $newTeacherId, $facultyId);
            $updateStmt->execute();
        }
    }
    
    // Send verification email
    $emailSent = sendFacultyVerificationEmail($email, $fullName, $verificationToken);
    // Log activity
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $logQuery = "INSERT INTO activity_logs (admin_id, action, details, ip_address) VALUES (0, 'FACULTY_SIGNUP', ?, ?)";
    $logStmt = $conn->prepare($logQuery);
    $logDetails = "New faculty signup: $fullName ($email)";
    $logStmt->bind_param("ss", $logDetails, $ipAddress);
    $logStmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful! Please check your email to verify your account.',
        'email_sent' => $emailSent,
        'redirect' => 'faculty_auth.php'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Registration failed. Please try again later.'
    ]);
}

$conn->close();
?>