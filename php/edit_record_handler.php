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
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if (!in_array($type, ['student', 'teacher'])) {
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
        if ($type === 'student') {
            // ============================================
            // UPDATE STUDENT RECORD
            // ============================================
            
            // Get student data
            $prn = sanitizeInput($_POST['prn']); // Read-only but included for reference
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

            // Validate required fields
            if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($dob) || empty($gender) || empty($department) || empty($class) || empty($division) || empty($roll_number) || empty($batch_year)) {
                $response['message'] = 'Please fill all required fields!';
                header('Content-Type: application/json');
                echo json_encode($response);
                exit();
            }

            // Check if email exists for other students
            $checkEmail = "SELECT id FROM students WHERE email = ? AND id != ?";
            $checkResult = executeQuery($checkEmail, "si", array($email, $id));
            if ($checkResult && $checkResult->num_rows > 0) {
                $response['message'] = 'Another student with this email already exists!';
                header('Content-Type: application/json');
                echo json_encode($response);
                exit();
            }

            // Update student record
            $updateQuery = "UPDATE students SET 
                first_name = ?, middle_name = ?, last_name = ?, email = ?, phone = ?, 
                dob = ?, gender = ?, address = ?, city = ?, state = ?, 
                department = ?, class = ?, division = ?, roll_number = ?, batch_year = ?, 
                parent_name = ?, parent_phone = ?, is_active = ?, updated_at = NOW()
                WHERE id = ?";

            $updateResult = executeUpdate(
                $updateQuery,
                "sssssssssssssssssii",
                array(
                    $first_name, $middle_name, $last_name, $email, $phone,
                    $dob, $gender, $address, $city, $state,
                    $department, $class, $division, $roll_number, $batch_year,
                    $parent_name, $parent_phone, $is_active, $id
                )
            );

            if ($updateResult) {
                // Log activity
                $admin_id = $_SESSION['admin_id'];
                $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
                $logQuery = "INSERT INTO activity_logs (admin_id, action, details, ip_address, user_agent) 
                             VALUES (?, 'UPDATE_STUDENT', ?, ?, ?)";
                $details = "Updated student: $first_name $last_name (PRN: $prn, ID: $id)";
                executeUpdate($logQuery, "isss", array($admin_id, $details, $ip, $userAgent));

                $response['success'] = true;
                $response['message'] = "Student updated successfully!";
            } else {
                $response['message'] = 'Failed to update student. Please try again.';
            }

        } else {
            // ============================================
            // UPDATE TEACHER RECORD
            // ============================================
            
            // Get teacher data
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

            // Validate required fields
            if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($gender) || empty($department) || empty($designation) || empty($qualification) || empty($experience)) {
                $response['message'] = 'Please fill all required fields!';
                header('Content-Type: application/json');
                echo json_encode($response);
                exit();
            }

            // Check if email exists for other teachers
            $checkEmail = "SELECT id FROM teachers WHERE email = ? AND id != ?";
            $checkResult = executeQuery($checkEmail, "si", array($email, $id));
            if ($checkResult && $checkResult->num_rows > 0) {
                $response['message'] = 'Another teacher with this email already exists!';
                header('Content-Type: application/json');
                echo json_encode($response);
                exit();
            }

            // Update teacher record
            $updateQuery = "UPDATE teachers SET 
                first_name = ?, middle_name = ?, last_name = ?, email = ?, phone = ?, 
                gender = ?, address = ?, city = ?, state = ?, department = ?, 
                designation = ?, qualification = ?, experience = ?, is_active = ?, updated_at = NOW()
                WHERE id = ?";

            $updateResult = executeUpdate(
                $updateQuery,
                "sssssssssssssii",
                array(
                    $first_name, $middle_name, $last_name, $email, $phone,
                    $gender, $address, $city, $state, $department,
                    $designation, $qualification, $experience, $is_active, $id
                )
            );

            if ($updateResult) {
                // Log activity
                $admin_id = $_SESSION['admin_id'];
                $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
                $logQuery = "INSERT INTO activity_logs (admin_id, action, details, ip_address, user_agent) 
                             VALUES (?, 'UPDATE_TEACHER', ?, ?, ?)";
                $details = "Updated teacher: $first_name $last_name (Email: $email, ID: $id)";
                executeUpdate($logQuery, "isss", array($admin_id, $details, $ip, $userAgent));

                $response['success'] = true;
                $response['message'] = "Teacher updated successfully!";
            } else {
                $response['message'] = 'Failed to update teacher. Please try again.';
            }
        }

    } catch (Exception $e) {
        $response['message'] = 'An error occurred: ' . $e->getMessage();
        error_log("Edit Record Error: " . $e->getMessage());
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>
