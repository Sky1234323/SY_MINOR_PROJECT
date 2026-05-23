<?php
session_start();
require_once 'db_connect.php';

$response = array('success' => false, 'profile' => array(), 'message' => '');

if (!isset($_SESSION['faculty_id'])) {
    $response['message'] = 'Unauthorized!';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = sanitizeInput($_POST['id']);
    $type = sanitizeInput($_POST['type']); // 'student' or 'teacher'

    if (empty($id) || empty($type)) {
        $response['message'] = 'ID and Type required!';
        echo json_encode($response);
        exit();
    }

    if ($type == 'student') {
        $query = "SELECT * FROM students WHERE id = ? AND is_active = 1";
    } elseif ($type == 'teacher') {
        $query = "SELECT * FROM teachers WHERE id = ? AND is_active = 1";
    } else {
        $response['message'] = 'Invalid type!';
        echo json_encode($response);
        exit();
    }

    $result = executeQuery($query, "i", array($id));

    if ($result && $result->num_rows == 1) {
        $profile = $result->fetch_assoc();
        
        // Add full name
        if ($type == 'student') {
            $profile['full_name'] = $profile['first_name'] . ' ' . 
                                   ($profile['middle_name'] ? $profile['middle_name'] . ' ' : '') . 
                                   $profile['last_name'];
            $profile['category'] = 'Student';
        } else {
            $profile['full_name'] = $profile['first_name'] . ' ' . 
                                   ($profile['middle_name'] ? $profile['middle_name'] . ' ' : '') . 
                                   $profile['last_name'];
            $profile['category'] = 'Teacher';
        }

        $response['success'] = true;
        $response['profile'] = $profile;
        $response['type'] = $type;
    } else {
        $response['message'] = 'Profile not found!';
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>