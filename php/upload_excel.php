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
    $upload_type = sanitizeInput($_POST['upload_type']); // 'students' or 'teachers'
    
    if ($upload_type == 'students') {
        require_once 'upload_excel_students.php';
    } elseif ($upload_type == 'teachers') {
        require_once 'upload_excel_teachers.php';
    } else {
        $response['message'] = 'Invalid upload type!';
        echo json_encode($response);
    }
}
?>