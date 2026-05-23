<?php
require_once '../php/db_connect.php';

$response = array('success' => false, 'message' => '');

// Check if admin already exists
$checkAdmin = executeQuery("SELECT id FROM admin LIMIT 1");
if ($checkAdmin && $checkAdmin->num_rows > 0) {
    $response['message'] = 'Admin account already exists! Setup aborted.';
    echo json_encode($response);
    exit();
}

// Fixed Admin Credentials (CHANGE THESE TO YOUR DESIRED VALUES)
$admin_username = 'admin';
$admin_email = 'admin@mitaoe.ac.in';
$admin_password = 'admin@1234'; // Change this to a strong password
$admin_full_name = 'System Administrator';
$admin_phone = '9876543210';
$admin_role = 'Super Admin';

// Validate credentials
if (strlen($admin_password) < 8) {
    $response['message'] = 'Password must be at least 8 characters!';
    echo json_encode($response);
    exit();
}

// Hash password with bcrypt
$hashed_password = password_hash($admin_password, PASSWORD_BCRYPT, ['cost' => 12]);

// Insert admin account
$query = "INSERT INTO admin (username, email, password, full_name, phone, role, is_active) 
          VALUES (?, ?, ?, ?, ?, ?, 1)";

if (executeUpdate($query, "ssssss", array(
    $admin_username, $admin_email, $hashed_password, $admin_full_name, $admin_phone, $admin_role
))) {
    $response['success'] = true;
    $response['message'] = 'Admin account created successfully!';
    $response['admin_id'] = getLastInsertId();
    $response['credentials'] = array(
        'username' => $admin_username,
        'email' => $admin_email,
        'password' => $admin_password,
        'note' => 'SAVE THESE CREDENTIALS SECURELY. There is NO password reset for admin.'
    );
} else {
    $response['message'] = 'Failed to create admin account!';
}

header('Content-Type: application/json');
echo json_encode($response);
?>