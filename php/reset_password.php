<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db_connect.php';

$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $token = isset($_POST['token']) ? trim($_POST['token']) : '';
    $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // Validate inputs
    if (empty($email) || empty($token) || empty($newPassword) || empty($confirmPassword)) {
        $response['message'] = 'All fields are required!';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email address!';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    // Basic password validation (minimum 8 characters only)
    if (strlen($newPassword) < 8) {
        $response['message'] = 'Password must be at least 8 characters long!';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    // Check if passwords match
    if ($newPassword !== $confirmPassword) {
        $response['message'] = 'Passwords do not match!';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    try {
        // Check if email exists in faculty table
        $facultyQuery = "SELECT id, password FROM faculty WHERE email = ? AND is_active = 1";
        $facultyResult = executeQuery($facultyQuery, "s", array($email));

        if (!$facultyResult || $facultyResult->num_rows === 0) {
            $response['message'] = 'Invalid email address!';
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }

        $faculty = $facultyResult->fetch_assoc();
        $faculty_id = $faculty['id'];

        // 🔴 FIXED: Verify token (check if it's the RESET TOKEN, not OTP)
        // The token here is the LONG reset token generated after OTP verification
        $tokenQuery = "SELECT id, expires_at, used FROM password_resets 
                       WHERE email = ? AND token = ? AND user_type = 'faculty' AND used = 1
                       ORDER BY created_at DESC LIMIT 1";
        $tokenResult = executeQuery($tokenQuery, "ss", array($email, $token));

        if (!$tokenResult || $tokenResult->num_rows === 0) {
            $response['message'] = 'Invalid or expired reset link!';
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }

        $tokenData = $tokenResult->fetch_assoc();

        // Check if token has expired (10 minutes from OTP creation)
        $currentTime = date('Y-m-d H:i:s');
        if ($currentTime > $tokenData['expires_at']) {
            $response['message'] = 'Reset link has expired!';
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }
        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        // Update password in faculty table
        $updatePasswordQuery = "UPDATE faculty SET password = ?, updated_at = NOW() WHERE id = ?";
        $updateResult = executeUpdate($updatePasswordQuery, "si", array($hashedPassword, $faculty_id));

        if (!$updateResult) {
            $response['message'] = 'Failed to update password. Please try again!';
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }

        // Delete the used reset token
        $deleteTokenQuery = "DELETE FROM password_resets WHERE id = ?";
        executeUpdate($deleteTokenQuery, "i", array($tokenData['id']));

        // Delete all old password reset tokens for this email
        $deleteOldTokensQuery = "DELETE FROM password_resets WHERE email = ? AND user_type = 'faculty'";
        executeUpdate($deleteOldTokensQuery, "s", array($email));

        // Log activity
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'Unknown';
        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown';
        $logQuery = "INSERT INTO activity_logs (admin_id, action, details, ip_address, user_agent) 
                     VALUES (?, 'PASSWORD_RESET', 'Faculty password reset successfully', ?, ?)";
        executeUpdate($logQuery, "iss", array($faculty_id, $ip, $userAgent));

        $response['success'] = true;
        $response['message'] = 'Password reset successfully! You can now login with your new password.';

    } catch (Exception $e) {
        $response['message'] = 'An error occurred: ' . $e->getMessage();
        error_log("Reset Password Error: " . $e->getMessage());
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>