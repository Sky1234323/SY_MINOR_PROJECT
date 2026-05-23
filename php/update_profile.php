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
    $address = sanitizeInput($_POST['address'] ?? '');
    $department = sanitizeInput($_POST['department']);
    $designation = sanitizeInput($_POST['designation']);
    $experience = sanitizeInput($_POST['experience']);
    $qualification = sanitizeInput($_POST['qualification']);
    $specialization = sanitizeInput($_POST['specialization'] ?? '');
    $college = sanitizeInput($_POST['college'] ?? '');
    $year_of_passing = sanitizeInput($_POST['year_of_passing'] ?? '');
    $aicte_id = sanitizeInput($_POST['aicte_id'] ?? '');
    $apaar_id = sanitizeInput($_POST['apaar_id'] ?? '');
    $bio = sanitizeInput($_POST['bio'] ?? '');
    $professional_links = $_POST['professional_links'] ?? '[]';

    // Validate required fields
    if (empty($phone) || empty($dob) || empty($gender) || empty($department) || 
        empty($designation) || empty($experience) || empty($qualification)) {
        $response['message'] = 'Please fill in all required fields!';
        echo json_encode($response);
        exit();
    }

    // Validate phone number
    if (!preg_match('/^\d{10}$/', $phone)) {
        $response['message'] = 'Please enter a valid 10-digit phone number!';
        echo json_encode($response);
        exit();
    }

    // Handle profile photo upload
    $profile_photo = null;
    $photoUploaded = false;
    
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
                // Delete old photo if exists
                $oldPhotoQuery = "SELECT profile_photo FROM faculty WHERE id = ?";
                $oldPhotoResult = executeQuery($oldPhotoQuery, "i", array($faculty_id));
                if ($oldPhotoResult && $oldPhotoResult->num_rows > 0) {
                    $oldPhoto = $oldPhotoResult->fetch_assoc()['profile_photo'];
                    if (!empty($oldPhoto) && file_exists('../' . $oldPhoto)) {
                        unlink('../' . $oldPhoto);
                    }
                }

                $fileName = 'faculty_' . $faculty_id . '_' . time() . '.' . $fileExtension;
                $filePath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $filePath)) {
                    $profile_photo = 'uploads/profiles/' . $fileName;
                    $photoUploaded = true;
                } else {
                    $response['message'] = 'Failed to upload profile photo!';
                    echo json_encode($response);
                    exit();
                }
            } else {
                $response['message'] = 'Profile photo size must be less than 5MB!';
                echo json_encode($response);
                exit();
            }
        } else {
            $response['message'] = 'Only JPG, JPEG, PNG, and GIF files are allowed!';
            echo json_encode($response);
            exit();
        }
    }

    // Build update query
    if ($photoUploaded) {
        $updateQuery = "UPDATE faculty SET 
                        phone = ?,
                        whatsapp = ?,
                        date_of_birth = ?,
                        gender = ?,
                        address = ?,
                        department = ?,
                        designation = ?,
                        experience = ?,
                        qualification = ?,
                        specialization = ?,
                        college = ?,
                        year_of_passing = ?,
                        aicte_id = ?,
                        apaar_id = ?,
                        bio = ?,
                        professional_links = ?,
                        profile_photo = ?,
                        updated_at = NOW()
                        WHERE id = ?";
        
        $result = executeUpdate($updateQuery, "sssssssssssssssssi", array(
            $phone, $whatsapp, $dob, $gender, $address, $department, $designation,
            $experience, $qualification, $specialization, $college, $year_of_passing,
            $aicte_id, $apaar_id, $bio, $professional_links, $profile_photo, $faculty_id
        ));
    } else {
        $updateQuery = "UPDATE faculty SET 
                        phone = ?,
                        whatsapp = ?,
                        date_of_birth = ?,
                        gender = ?,
                        address = ?,
                        department = ?,
                        designation = ?,
                        experience = ?,
                        qualification = ?,
                        specialization = ?,
                        college = ?,
                        year_of_passing = ?,
                        aicte_id = ?,
                        apaar_id = ?,
                        bio = ?,
                        professional_links = ?,
                        updated_at = NOW()
                        WHERE id = ?";
        
        $result = executeUpdate($updateQuery, "ssssssssssssssssi", array(
            $phone, $whatsapp, $dob, $gender, $address, $department, $designation,
            $experience, $qualification, $specialization, $college, $year_of_passing,
            $aicte_id, $apaar_id, $bio, $professional_links, $faculty_id
        ));
    }

    if ($result) {
        // Log activity
        $ip = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $logQuery = "INSERT INTO activity_logs (admin_id, action, details, ip_address, user_agent) 
                     VALUES (?, 'PROFILE_UPDATED', 'Faculty updated profile information', ?, ?)";
        executeUpdate($logQuery, "iss", array($faculty_id, $ip, $userAgent));

        $response['success'] = true;
        $response['message'] = 'Profile updated successfully!';
    } else {
        $response['message'] = 'Failed to update profile. Database error!';
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>