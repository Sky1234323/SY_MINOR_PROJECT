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
    $prn = sanitizeInput($_POST['prn']);
    $first_name = sanitizeInput($_POST['first_name']);
    $middle_name = sanitizeInput($_POST['middle_name'] ?? '');
    $last_name = sanitizeInput($_POST['last_name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $gender = sanitizeInput($_POST['gender'] ?? '');
    $city = sanitizeInput($_POST['city'] ?? '');
    $state = sanitizeInput($_POST['state'] ?? '');
    $department = sanitizeInput($_POST['department'] ?? '');
    $class = sanitizeInput($_POST['class'] ?? '');
    $division = sanitizeInput($_POST['division'] ?? '');
    $roll_number = sanitizeInput($_POST['roll_number'] ?? '');
    $batch_year = sanitizeInput($_POST['batch_year'] ?? '');

    // Validation
    if (empty($prn) || empty($first_name) || empty($last_name) || empty($email)) {
        $response['message'] = 'PRN, First Name, Last Name, and Email are required!';
        echo json_encode($response);
        exit();
    }

    // Check if PRN exists
    $checkPrn = executeQuery("SELECT id FROM students WHERE prn = ?", "s", array($prn));
    if ($checkPrn && $checkPrn->num_rows > 0) {
        $response['message'] = 'PRN already exists!';
        echo json_encode($response);
        exit();
    }

    // Check if Email exists
    $checkEmail = executeQuery("SELECT id FROM students WHERE email = ?", "s", array($email));
    if ($checkEmail && $checkEmail->num_rows > 0) {
        $response['message'] = 'Email already exists!';
        echo json_encode($response);
        exit();
    }

    // Insert student
    $query = "INSERT INTO students (prn, first_name, middle_name, last_name, email, phone, gender, city, state, department, class, division, roll_number, batch_year) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if (executeUpdate($query, "ssssssssssssss", array(
        $prn, $first_name, $middle_name, $last_name, $email, $phone, 
        $gender, $city, $state, $department, $class, $division, $roll_number, $batch_year
    ))) {
        $response['success'] = true;
        $response['message'] = 'Student added successfully!';
        $response['student_id'] = getLastInsertId();
    } else {
        $response['message'] = 'Failed to add student!';
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>