<?php
require_once 'db_connect.php';

$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = sanitizeInput($_POST['token']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($token) || empty($new_password)) {
        $response['message'] = 'Token and password required!';
        echo json_encode($response);
        exit();
    }

    if ($new_password !== $confirm_password) {
        $response['message'] = 'Passwords do not match!';
        echo json_encode($response);
        exit();
    }

    if (strlen($new_password) < 6) {
        $response['message'] = 'Password must be at least 6 characters!';
        echo json_encode($response);
        exit();
    }

    // Verify token
    $query = "SELECT email FROM password_resets WHERE token = ? AND user_type = 'faculty' AND used = 0 AND expires_at > NOW()";
    $result = executeQuery($query, "s", array($token));

    if (!$result || $result->num_rows == 0) {
        $response['message'] = 'Invalid or expired reset token!';
        echo json_encode($response);
        exit();
    }

    $reset = $result->fetch_assoc();
    $email = $reset['email'];

    // Hash new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update faculty password
    $updateQuery = "UPDATE faculty SET password = ? WHERE email = ?";
    
    if (executeUpdate($updateQuery, "ss", array($hashed_password, $email))) {
        // Mark token as used
        $markQuery = "UPDATE password_resets SET used = 1 WHERE token = ?";
        executeUpdate($markQuery, "s", array($token));

        $response['success'] = true;
        $response['message'] = 'Password reset successful! Please login.';
    } else {
        $response['message'] = 'Failed to reset password!';
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>