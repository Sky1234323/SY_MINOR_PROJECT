<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connect.php';

$response = array('success' => false, 'message' => '');

if (!isset($_SESSION['faculty_id'])) {
    $response['message'] = 'Unauthorized! Please login.';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $faculty_id = $_SESSION['faculty_id'];
    
    // Sanitize inputs
    $phone = sanitizeInput($_POST['phone']);
    $whatsapp = sanitizeInput($_POST['whatsapp'] ?? '');
    $dob = sanitizeInput($_POST['dob']);
    $gender = sanitizeInput($_POST['gender']);
    $department = sanitizeInput($_POST['department']);
    $designation = sanitizeInput($_POST['designation']);
    $address = sanitizeInput($_POST['address'] ?? '');
    $qualification = sanitizeInput($_POST['qualification']);
    $specialization = sanitizeInput($_POST['specialization'] ?? '');
    $college = sanitizeInput($_POST['college'] ?? '');
    $year_of_passing = sanitizeInput($_POST['year_of_passing'] ?? '');
    $experience = sanitizeInput($_POST['experience']);
    $aicte_id = sanitizeInput($_POST['aicte_id'] ?? '');
    $apaar_id = sanitizeInput($_POST['apaar_id'] ?? '');
    $bio = sanitizeInput($_POST['bio'] ?? '');
    $professional_links = $_POST['professional_links'] ?? '[]';

    // Validate required fields
    if (empty($phone) || empty($dob) || empty($gender) || empty($department) || 
        empty($designation) || empty($qualification) || empty($experience)) {
        $response['message'] = 'Please fill in all required fields!';
        echo json_encode($response);
        exit();
    }

    // Handle profile photo upload
    $profile_photo = null;
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
        $uploadDir = '../uploads/profiles/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileExtension = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');

        if (in_array($fileExtension, $allowedExtensions)) {
            // Check file size (max 5MB)
            if ($_FILES['profile_photo']['size'] <= 5242880) {
                $fileName = 'faculty_' . $faculty_id . '_' . time() . '.' . $fileExtension;
                $filePath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $filePath)) {
                    $profile_photo = 'uploads/profiles/' . $fileName;
                }
            }
        }
    }

    // Check if columns exist before updating
    $checkQuery = "SHOW COLUMNS FROM faculty LIKE 'profile_completed'";
    $checkResult = executeQuery($checkQuery);
    
    if (!$checkResult || $checkResult->num_rows == 0) {
        $response['message'] = 'Please run the SQL update first to add required columns!';
        echo json_encode($response);
        exit();
    }

    // Build update query
    if ($profile_photo) {
        $updateQuery = "UPDATE faculty SET 
                        phone = ?,
                        whatsapp = ?,
                        date_of_birth = ?,
                        gender = ?,
                        department = ?,
                        designation = ?,
                        address = ?,
                        qualification = ?,
                        specialization = ?,
                        college = ?,
                        year_of_passing = ?,
                        experience = ?,
                        aicte_id = ?,
                        apaar_id = ?,
                        bio = ?,
                        professional_links = ?,
                        profile_photo = ?,
                        profile_completed = 1
                        WHERE id = ?";
        
        $result = executeUpdate($updateQuery, "sssssssssssssssssi", array(
            $phone, $whatsapp, $dob, $gender, $department, $designation, $address,
            $qualification, $specialization, $college, $year_of_passing, $experience,
            $aicte_id, $apaar_id, $bio, $professional_links, $profile_photo, $faculty_id
        ));
    } else {
        $updateQuery = "UPDATE faculty SET 
                        phone = ?,
                        whatsapp = ?,
                        date_of_birth = ?,
                        gender = ?,
                        department = ?,
                        designation = ?,
                        address = ?,
                        qualification = ?,
                        specialization = ?,
                        college = ?,
                        year_of_passing = ?,
                        experience = ?,
                        aicte_id = ?,
                        apaar_id = ?,
                        bio = ?,
                        professional_links = ?,
                        profile_completed = 1
                        WHERE id = ?";
        
        $result = executeUpdate($updateQuery, "ssssssssssssssssi", array(
            $phone, $whatsapp, $dob, $gender, $department, $designation, $address,
            $qualification, $specialization, $college, $year_of_passing, $experience,
            $aicte_id, $apaar_id, $bio, $professional_links, $faculty_id
        ));
    }

    if ($result) {
        $response['success'] = true;
        $response['message'] = 'Profile completed successfully!';
    } else {
        $response['message'] = 'Failed to update profile. Database error!';
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>