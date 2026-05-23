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

$response = array('success' => false, 'message' => '', 'token' => '');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $otp = isset($_POST['otp']) ? trim($_POST['otp']) : '';

    // Validate inputs
    if (empty($email) || empty($otp)) {
        $response['message'] = 'Please enter the OTP!';
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

    // Validate OTP format (6 digits)
    if (!preg_match('/^\d{6}$/', $otp)) {
        $response['message'] = 'OTP must be 6 digits!';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    try {
        // Check if email exists in faculty table
        $facultyQuery = "SELECT id FROM faculty WHERE email = ? AND is_active = 1";
        $facultyResult = executeQuery($facultyQuery, "s", array($email));

        if (!$facultyResult || $facultyResult->num_rows === 0) {
            $response['message'] = 'Invalid email address!';
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }

        $faculty = $facultyResult->fetch_assoc();
        $faculty_id = $faculty['id'];

        // Check OTP in database
        $query = "SELECT id, token, expires_at, used FROM password_resets 
                  WHERE email = ? AND token = ? AND user_type = 'faculty' 
                  ORDER BY created_at DESC LIMIT 1";
        $result = executeQuery($query, "ss", array($email, $otp));

        if (!$result || $result->num_rows === 0) {
            $response['message'] = 'Invalid OTP! Please check and try again.';
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }

        $otpData = $result->fetch_assoc();

        // Check if OTP is already used
        if ($otpData['used'] == 1) {
            $response['message'] = 'This OTP has already been used!';
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }

        // Check if OTP has expired
        $currentTime = date('Y-m-d H:i:s');
        if ($currentTime > $otpData['expires_at']) {
            $response['message'] = 'OTP has expired! Please request a new one.';
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }

        // Generate unique token for password reset
        $resetToken = bin2hex(random_bytes(32));

        // Update OTP as used and store reset token
        $updateQuery = "UPDATE password_resets SET used = 1, token = ? 
                        WHERE id = ? AND email = ? AND user_type = 'faculty'";
        $updateResult = executeUpdate($updateQuery, "sis", array($resetToken, $otpData['id'], $email));

        if ($updateResult) {
            // Log activity
            $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'Unknown';
            $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown';
            $logQuery = "INSERT INTO activity_logs (admin_id, action, details, ip_address, user_agent) 
                         VALUES (?, 'OTP_VERIFIED', 'Faculty verified OTP for password reset', ?, ?)";
            executeUpdate($logQuery, "iss", array($faculty_id, $ip, $userAgent));

            $response['success'] = true;
            $response['message'] = 'OTP verified successfully!';
            $response['token'] = $resetToken;
        } else {
            $response['message'] = 'Failed to verify OTP. Please try again!';
        }

    } catch (Exception $e) {
        $response['message'] = 'An error occurred: ' . $e->getMessage();
        error_log("Verify OTP Error: " . $e->getMessage());
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>