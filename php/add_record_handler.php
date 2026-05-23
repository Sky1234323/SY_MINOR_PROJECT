<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

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

    if (!in_array($type, ['student', 'teacher'])) {
        $response['message'] = 'Invalid record type!';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    try {
        if ($type === 'student') {
            // Get student data
            $prn = sanitizeInput($_POST['prn']);
            $first_name = sanitizeInput($_POST['first_name']);
            $middle_name = sanitizeInput($_POST['middle_name']);
            $last_name = sanitizeInput($_POST['last_name']);
            $email = sanitizeInput($_POST['email']);
            $phone = sanitizeInput($_POST['phone']);
            $dob = sanitizeInput($_POST['dob']);
            $gender = sanitizeInput($_POST['gender']);
            $address = sanitizeInput($_POST['address']);
            $city = sanitizeInput($_POST['city']);
            $state = sanitizeInput($_POST['state']);
            $department = sanitizeInput($_POST['department']);
            $class = sanitizeInput($_POST['class']);
            $division = sanitizeInput($_POST['division']);
            $roll_number = sanitizeInput($_POST['roll_number']);
            $batch_year = sanitizeInput($_POST['batch_year']);
            $parent_name = sanitizeInput($_POST['parent_name']);
            $parent_phone = sanitizeInput($_POST['parent_phone']);
            $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

            // Check if PRN exists
            $checkPRN = "SELECT id FROM students WHERE prn = ?";
            $checkResult = executeQuery($checkPRN, "s", array($prn));
            if ($checkResult && $checkResult->num_rows > 0) {
                $response['message'] = 'Student with this PRN already exists!';
                header('Content-Type: application/json');
                echo json_encode($response);
                exit();
            }

            // Check if email exists
            $checkEmail = "SELECT id FROM students WHERE email = ?";
            $checkResult = executeQuery($checkEmail, "s", array($email));
            if ($checkResult && $checkResult->num_rows > 0) {
                $response['message'] = 'Student with this email already exists!';
                header('Content-Type: application/json');
                echo json_encode($response);
                exit();
            }

            // Insert student
            $insertQuery = "INSERT INTO students (
                prn, first_name, middle_name, last_name, email, phone, dob, gender,
                address, city, state, department, class, division, roll_number,
                batch_year, parent_name, parent_phone, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $insertResult = executeUpdate(
                $insertQuery,
                "ssssssssssssssssssi",
                array(
                    $prn, $first_name, $middle_name, $last_name, $email, $phone, $dob, $gender,
                    $address, $city, $state, $department, $class, $division, $roll_number,
                    $batch_year, $parent_name, $parent_phone, $is_active
                )
            );

            if ($insertResult) {
                // Log activity
                $admin_id = $_SESSION['admin_id'];
                $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
                $logQuery = "INSERT INTO activity_logs (admin_id, action, details, ip_address, user_agent) 
                             VALUES (?, 'ADD_STUDENT', ?, ?, ?)";
                $details = "Added student: $first_name $last_name (PRN: $prn)";
                executeUpdate($logQuery, "isss", array($admin_id, $details, $ip, $userAgent));

                $response['success'] = true;
                $response['message'] = "Student added successfully! PRN: $prn";
            } else {
                $response['message'] = 'Failed to add student. Please try again.';
            }

        } else {
            // Teacher logic (same as before)
            $first_name = sanitizeInput($_POST['first_name']);
            $middle_name = sanitizeInput($_POST['middle_name']);
            $last_name = sanitizeInput($_POST['last_name']);
            $email = sanitizeInput($_POST['email']);
            $phone = sanitizeInput($_POST['phone']);
            $gender = sanitizeInput($_POST['gender']);
            $address = sanitizeInput($_POST['address']);
            $city = sanitizeInput($_POST['city']);
            $state = sanitizeInput($_POST['state']);
            $department = sanitizeInput($_POST['department']);
            $designation = sanitizeInput($_POST['designation']);
            $qualification = sanitizeInput($_POST['qualification']);
            $experience = sanitizeInput($_POST['experience']);
            $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

            // Check if email exists
            $checkEmail = "SELECT id FROM teachers WHERE email = ?";
            $checkResult = executeQuery($checkEmail, "s", array($email));
            if ($checkResult && $checkResult->num_rows > 0) {
                $response['message'] = 'Teacher with this email already exists!';
                header('Content-Type: application/json');
                echo json_encode($response);
                exit();
            }

            // Insert teacher
            $insertQuery = "INSERT INTO teachers (
                first_name, middle_name, last_name, email, phone, gender,
                address, city, state, department, designation, qualification,
                experience, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $insertResult = executeUpdate(
                $insertQuery,
                "ssssssssssssii",
                array(
                    $first_name, $middle_name, $last_name, $email, $phone, $gender,
                    $address, $city, $state, $department, $designation, $qualification,
                    $experience, $is_active
                )
            );

            if ($insertResult) {
                // Log activity
                $admin_id = $_SESSION['admin_id'];
                $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
                $logQuery = "INSERT INTO activity_logs (admin_id, action, details, ip_address, user_agent) 
                             VALUES (?, 'ADD_TEACHER', ?, ?, ?)";
                $details = "Added teacher: $first_name $last_name (Email: $email)";
                executeUpdate($logQuery, "isss", array($admin_id, $details, $ip, $userAgent));

                $response['success'] = true;
                $response['message'] = "Teacher added successfully! Email: $email";
            } else {
                $response['message'] = 'Failed to add teacher. Please try again.';
            }
        }

    } catch (Exception $e) {
        $response['message'] = 'An error occurred: ' . $e->getMessage();
        error_log("Add Record Error: " . $e->getMessage());
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>
