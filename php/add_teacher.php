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
    $first_name = sanitizeInput($_POST['first_name']);
    $middle_name = sanitizeInput($_POST['middle_name'] ?? '');
    $last_name = sanitizeInput($_POST['last_name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $gender = sanitizeInput($_POST['gender'] ?? '');
    $city = sanitizeInput($_POST['city'] ?? '');
    $state = sanitizeInput($_POST['state'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $department = sanitizeInput($_POST['department'] ?? '');
    $designation = sanitizeInput($_POST['designation'] ?? '');
    $qualification = sanitizeInput($_POST['qualification'] ?? '');
    $experience = sanitizeInput($_POST['experience'] ?? '');

    // Validation
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $response['message'] = 'First Name, Last Name, and Email are required!';
        echo json_encode($response);
        exit();
    }

    // Check if Email exists
    $checkEmail = executeQuery("SELECT id FROM teachers WHERE email = ?", "s", array($email));
    if ($checkEmail && $checkEmail->num_rows > 0) {
        $response['message'] = 'Email already exists!';
        echo json_encode($response);
        exit();
    }

    // Insert teacher
    $query = "INSERT INTO teachers (first_name, middle_name, last_name, email, phone, gender, city, state, address, department, designation, qualification, experience) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if (executeUpdate($query, "ssssssssssss", array(
        $first_name, $middle_name, $last_name, $email, $phone, $gender, 
        $city, $state, $address, $department, $designation, $qualification, $experience
    ))) {
        $response['success'] = true;
        $response['message'] = 'Teacher added successfully!';
        $response['teacher_id'] = getLastInsertId();
    } else {
        $response['message'] = 'Failed to add teacher!';
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>