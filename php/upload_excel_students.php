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
    
    $fileName = time() . '_students.' . $fileType;
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
            
            // Expected CSV format: PRN, FirstName, MiddleName, LastName, Email, Phone, Gender, City, State, Department, Class, Division, RollNumber, BatchYear
            
            if (count($data) < 14) {
                $failCount++;
                $errors[] = "Row $rowCount: Incomplete data";
                continue;
            }
            
            $prn = sanitizeInput($data[0]);
            $first_name = sanitizeInput($data[1]);
            $middle_name = sanitizeInput($data[2]);
            $last_name = sanitizeInput($data[3]);
            $email = sanitizeInput($data[4]);
            $phone = sanitizeInput($data[5]);
            $gender = sanitizeInput($data[6]);
            $city = sanitizeInput($data[7]);
            $state = sanitizeInput($data[8]);
            $department = sanitizeInput($data[9]);
            $class = sanitizeInput($data[10]);
            $division = sanitizeInput($data[11]);
            $roll_number = sanitizeInput($data[12]);
            $batch_year = sanitizeInput($data[13]);
            
            // Validate required fields
            if (empty($prn) || empty($first_name) || empty($last_name) || empty($email)) {
                $failCount++;
                $errors[] = "Row $rowCount: Missing required fields (PRN/Name/Email)";
                continue;
            }
            
            // Check if PRN exists
            $checkPrn = executeQuery("SELECT id FROM students WHERE prn = ?", "s", array($prn));
            if ($checkPrn && $checkPrn->num_rows > 0) {
                $failCount++;
                $errors[] = "Row $rowCount: PRN $prn already exists";
                continue;
            }
            
            // Check if Email exists
            $checkEmail = executeQuery("SELECT id FROM students WHERE email = ?", "s", array($email));
            if ($checkEmail && $checkEmail->num_rows > 0) {
                $failCount++;
                $errors[] = "Row $rowCount: Email $email already exists";
                continue;
            }
            
            // Insert student
            $query = "INSERT INTO students (prn, first_name, middle_name, last_name, email, phone, gender, city, state, department, class, division, roll_number, batch_year) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            if (executeUpdate($query, "ssssssssssssss", array(
                $prn, $first_name, $middle_name, $last_name, $email, $phone, 
                $gender, $city, $state, $department, $class, $division, $roll_number, $batch_year
            ))) {
                $successCount++;
            } else {
                $failCount++;
                $errors[] = "Row $rowCount: Failed to insert";
            }
        }
        
        fclose($handle);
        
        // Log admin activity
        logAdminActivity($_SESSION['admin_id'], 'BULK_UPLOAD_STUDENTS', "Uploaded $successCount students from CSV", null);
        
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