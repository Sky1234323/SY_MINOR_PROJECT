<?php
require_once 'db_connect.php';

$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitizeInput($_POST['email']);

    if (empty($email)) {
        $response['message'] = 'Email required!';
        echo json_encode($response);
        exit();
    }

    // Check if email exists in faculty table
    $query = "SELECT id FROM faculty WHERE email = ?";
    $result = executeQuery($query, "s", array($email));

    if (!$result || $result->num_rows == 0) {
        $response['message'] = 'Email not registered!';
        echo json_encode($response);
        exit();
    }

    $faculty = $result->fetch_assoc();
    $faculty_id = $faculty['id'];

    // Generate unique token
    $token = bin2hex(random_bytes(50));
    $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Save reset token
    $insertQuery = "INSERT INTO password_resets (email, token, user_type, expires_at) 
                    VALUES (?, ?, ?, ?)";

    if (executeUpdate($insertQuery, "ssss", array($email, $token, 'faculty', $expires_at))) {
        $response['success'] = true;
        $response['message'] = 'Password reset link sent! (Token generated)';
        $response['token'] = $token;
        $response['reset_link'] = "faculty_reset_password.php?token=" . $token;
    } else {
        $response['message'] = 'Failed to generate reset token!';
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>