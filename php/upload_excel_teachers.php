<?php
session_start();
require_once 'db_connect.php';
require_once 'log_admin_activity.php';

$response = array('success' => false, 'message' => '', 'details' => array());

if (!isset($_SESSION['admin_id'])) {
    $response['message'] = 'Unauthorized!';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    
    // Validate file upload
    if ($file['error'] != 0) {
        $response['message'] = 'File upload error!';
        echo json_encode($response);
        exit();
    }
    
    $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($fileType, array('csv', 'xlsx', 'xls'))) {
        $response['message'] = 'Only CSV or Excel files allowed!';
        echo json_encode($response);
        exit();
    }
    
    $uploadDir = '../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileName = time() . '_teachers.' . $fileType;
    $filePath = $uploadDir . $fileName;
    
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        $response['message'] = 'Failed to save file!';
        echo json_encode($response);
        exit();
    }
    
    // Process CSV file
    if ($fileType == 'csv') {
        $handle = fopen($filePath, 'r');
        
        if ($handle === false) {
            $response['message'] = 'Cannot read file!';
            echo json_encode($response);
            exit();
        }
        
        $header = fgetcsv($handle); // Skip header row
        $rowCount = 0;
        $successCount = 0;
        $failCount = 0;
        $errors = array();
        
        while (($data = fgetcsv($handle)) !== false) {
            $rowCount++;
            
            // Expected CSV format: FirstName, MiddleName, LastName, Email, Phone, Gender, City, State, Address, Department, Designation, Qualification, Experience
            
            if (count($data) < 13) {
                $failCount++;
                $errors[] = "Row $rowCount: Incomplete data";
                continue;
            }
            
            $first_name = sanitizeInput($data[0]);
            $middle_name = sanitizeInput($data[1]);
            $last_name = sanitizeInput($data[2]);
            $email = sanitizeInput($data[3]);
            $phone = sanitizeInput($data[4]);
            $gender = sanitizeInput($data[5]);
            $city = sanitizeInput($data[6]);
            $state = sanitizeInput($data[7]);
            $address = sanitizeInput($data[8]);
            $department = sanitizeInput($data[9]);
            $designation = sanitizeInput($data[10]);
            $qualification = sanitizeInput($data[11]);
            $experience = sanitizeInput($data[12]);
            
            // Validate required fields
            if (empty($first_name) || empty($last_name) || empty($email)) {
                $failCount++;
                $errors[] = "Row $rowCount: Missing required fields (Name/Email)";
                continue;
            }
            
            // Check if Email exists
            $checkEmail = executeQuery("SELECT id FROM teachers WHERE email = ?", "s", array($email));
            if ($checkEmail && $checkEmail->num_rows > 0) {
                $failCount++;
                $errors[] = "Row $rowCount: Email $email already exists";
                continue;
            }
            
            // Insert teacher
            $query = "INSERT INTO teachers (first_name, middle_name, last_name, email, phone, gender, city, state, address, department, designation, qualification, experience) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            if (executeUpdate($query, "sssssssssssss", array(
                $first_name, $middle_name, $last_name, $email, $phone, $gender, 
                $city, $state, $address, $department, $designation, $qualification, $experience
            ))) {
                $successCount++;
            } else {
                $failCount++;
                $errors[] = "Row $rowCount: Failed to insert";
            }
        }
        
        fclose($handle);
        
        // Log admin activity
        logAdminActivity($_SESSION['admin_id'], 'BULK_UPLOAD_TEACHERS', "Uploaded $successCount teachers from CSV", null);
        
        $response['success'] = true;
        $response['message'] = "Upload complete! Success: $successCount, Failed: $failCount";
        $response['details'] = array(
            'total_rows' => $rowCount,
            'success' => $successCount,
            'failed' => $failCount,
            'errors' => $errors
        );
    } else {
        // For Excel files (.xlsx/.xls)
        $response['message'] = 'Excel processing requires PHPSpreadsheet library. Please convert to CSV format.';
    }
    
    // Clean up uploaded file
    unlink($filePath);
}

header('Content-Type: application/json');
echo json_encode($response);
?>