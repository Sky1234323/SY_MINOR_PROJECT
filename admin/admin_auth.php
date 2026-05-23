<?php
session_start();
require_once 'db_connect.php';

$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $response['message'] = 'Username and password required!';
    } else {
        $query = "SELECT id, username, email, password, full_name, role, is_active FROM admin WHERE username = ?";
        $result = executeQuery($query, "s", array($username));

        if ($result && $result->num_rows == 1) {
            $admin = $result->fetch_assoc();

            if ($admin['is_active'] == 0) {
                $response['message'] = 'Admin account inactive!';
                recordLogin('admin', $admin['id'], $admin['email'], 'failed');
            } elseif (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_name'] = $admin['full_name'];
                $_SESSION['admin_role'] = $admin['role'];
                $_SESSION['admin_email'] = $admin['email'];

                // Update last login
                $updateQuery = "UPDATE admin SET last_login = NOW() WHERE id = ?";
                executeUpdate($updateQuery, "i", array($admin['id']));

                recordLogin('admin', $admin['id'], $admin['email'], 'success');

                $response['success'] = true;
                $response['message'] = 'Login successful!';
                $response['redirect'] = '../admin/admin_dashboard.php';
            } else {
                $response['message'] = 'Invalid password!';
                recordLogin('admin', $admin['id'], $admin['email'], 'failed');
            }
        } else {
            $response['message'] = 'Username not found!';
            recordLogin('admin', 0, $username, 'failed');
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>