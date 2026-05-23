<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    $response = array('success' => false, 'message' => 'Unauthorized!');
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

require_once 'db_connect.php';

$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = isset($_POST['type']) ? trim($_POST['type']) : '';
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if (!in_array($type, ['student', 'teacher', 'faculty'])) {
        $response['message'] = 'Invalid record type!';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    if ($id <= 0) {
        $response['message'] = 'Invalid record ID!';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    try {
        // Determine table name
        $table = $type === 'student' ? 'students' : ($type === 'teacher' ? 'teachers' : 'faculty');

        // Delete record
        $deleteQuery = "DELETE FROM $table WHERE id = ?";
        $deleteResult = executeUpdate($deleteQuery, "i", array($id));

        if ($deleteResult) {
            // Log activity
            $admin_id = $_SESSION['admin_id'];
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            $logQuery = "INSERT INTO activity_logs (admin_id, action, details, ip_address, user_agent) 
                         VALUES (?, ?, ?, ?, ?)";
            $details = "Deleted $type record (ID: $id)";
            executeUpdate($logQuery, "issss", array($admin_id, strtoupper("DELETE_$type"), $details, $ip, $userAgent));

            $response['success'] = true;
            $response['message'] = ucfirst($type) . ' deleted successfully!';
        } else {
            $response['message'] = 'Failed to delete record. Please try again.';
        }

    } catch (Exception $e) {
        $response['message'] = 'An error occurred: ' . $e->getMessage();
        error_log("Delete Record Error: " . $e->getMessage());
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>
