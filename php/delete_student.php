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
    $student_id = sanitizeInput($_POST['student_id']);
    $delete_by = sanitizeInput($_POST['delete_by']); // 'prn' or 'email'

    if (empty($student_id)) {
        $response['message'] = 'Student ID/PRN/Email required!';
        echo json_encode($response);
        exit();
    }

    // Find student
    if ($delete_by == 'prn') {
        $query = "SELECT id FROM students WHERE prn = ?";
    } elseif ($delete_by == 'email') {
        $query = "SELECT id FROM students WHERE email = ?";
    } else {
        $query = "SELECT id FROM students WHERE id = ?";
    }

    $result = executeQuery($query, "s", array($student_id));

    if (!$result || $result->num_rows == 0) {
        $response['message'] = 'Student not found!';
        echo json_encode($response);
        exit();
    }

    $student = $result->fetch_assoc();
    $student_id = $student['id'];

    // Delete student (soft delete - mark as inactive)
    $deleteQuery = "UPDATE students SET is_active = 0 WHERE id = ?";
    
    if (executeUpdate($deleteQuery, "i", array($student_id))) {
        $response['success'] = true;
        $response['message'] = 'Student record deleted successfully!';
    } else {
        $response['message'] = 'Failed to delete student record!';
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>