<?php
session_start();
require_once 'db_connect.php';

$response = array('success' => false, 'message' => '');

if (!isset($_SESSION['admin_id'])) {
    $response['message'] = 'Unauthorized!';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $teacher_id = sanitizeInput($_POST['teacher_id']);
    $delete_by = sanitizeInput($_POST['delete_by']); // 'email' or 'id'

    if (empty($teacher_id)) {
        $response['message'] = 'Teacher ID or Email required!';
        echo json_encode($response);
        exit();
    }

    // Find teacher
    if ($delete_by == 'email') {
        $query = "SELECT id FROM teachers WHERE email = ?";
    } else {
        $query = "SELECT id FROM teachers WHERE id = ?";
    }

    $result = executeQuery($query, "s", array($teacher_id));

    if (!$result || $result->num_rows == 0) {
        $response['message'] = 'Teacher not found!';
        echo json_encode($response);
        exit();
    }

    $teacher = $result->fetch_assoc();
    $teacher_id = $teacher['id'];

    // Delete teacher (soft delete - mark as inactive)
    $deleteQuery = "UPDATE teachers SET is_active = 0 WHERE id = ?";
    
    if (executeUpdate($deleteQuery, "i", array($teacher_id))) {
        $response['success'] = true;
        $response['message'] = 'Teacher record deleted successfully!';
    } else {
        $response['message'] = 'Failed to delete teacher record!';
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>