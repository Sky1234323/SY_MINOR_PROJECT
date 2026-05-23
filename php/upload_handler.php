<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

session_start();

// Check authentication
if (!isset($_SESSION['admin_id'])) {
    $response = array('success' => false, 'message' => 'Unauthorized access!');
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

require_once 'db_connect.php';

$response = array('success' => false, 'message' => '');

// Check if file was uploaded
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $response['message'] = 'No file uploaded or upload error occurred!';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Get type
$type = isset($_POST['type']) ? $_POST['type'] : '';
if (!in_array($type, ['students', 'teachers'])) {
    $response['message'] = 'Invalid upload type!';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

$file = $_FILES['file'];
$fileName = $file['name'];
$fileTmpName = $file['tmp_name'];
$fileSize = $file['size'];
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

// Validate file extension
$allowedExtensions = ['csv', 'xlsx', 'xls'];
if (!in_array($fileExt, $allowedExtensions)) {
    $response['message'] = 'Invalid file type! Only CSV and Excel files are allowed.';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Validate file size (10MB max)
$maxSize = 10 * 1024 * 1024; // 10MB
if ($fileSize > $maxSize) {
    $response['message'] = 'File too large! Maximum size is 10MB.';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

try {
    // Process CSV file
    if ($fileExt === 'csv') {
        $data = processCSV($fileTmpName);
    } else {
        // For Excel files, you would need a library like PhpSpreadsheet
        // For now, we'll show a message to convert to CSV
        $response['message'] = 'Excel files are not yet supported. Please convert to CSV and upload again.';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    if (empty($data)) {
        $response['message'] = 'No valid data found in file!';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    // Import data based on type
    if ($type === 'students') {
        $result = importStudents($data);
    } else {
        $result = importTeachers($data);
    }

    if ($result['success']) {
        // Log activity
        $admin_id = $_SESSION['admin_id'];
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        $logQuery = "INSERT INTO activity_logs (admin_id, action, details, ip_address, user_agent) 
                     VALUES (?, ?, ?, ?, ?)";
        
        $action = strtoupper('BULK_UPLOAD_' . rtrim($type, 's'));
        $details = "Uploaded {$result['imported']} " . rtrim($type, 's') . "(s) via CSV. File: $fileName";
        
        executeUpdate($logQuery, "issss", array($admin_id, $action, $details, $ip, $userAgent));

        $response['success'] = true;
        $response['message'] = "✅ Successfully imported {$result['imported']} records! ";
        if ($result['skipped'] > 0) {
            $response['message'] .= "Skipped {$result['skipped']} duplicate(s).";
        }
        $response['imported'] = $result['imported'];
        $response['skipped'] = $result['skipped'];
    } else {
        $response['message'] = $result['message'];
    }

} catch (Exception $e) {
    $response['message'] = 'An error occurred: ' . $e->getMessage();
    error_log("Upload Error: " . $e->getMessage());
}

header('Content-Type: application/json');
echo json_encode($response);

/**
 * Process CSV file
 */
function processCSV($filePath) {
    $data = array();
    
    if (($handle = fopen($filePath, 'r')) !== false) {
        // Get header row
        $headers = fgetcsv($handle);
        
        // Clean headers (remove BOM, trim whitespace)
        $headers = array_map(function($header) {
            $header = trim($header);
            // Remove UTF-8 BOM if present
            $header = str_replace("\xEF\xBB\xBF", '', $header);
            return strtolower(str_replace(' ', '_', $header));
        }, $headers);
        
        // Read data rows
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === count($headers)) {
                $data[] = array_combine($headers, $row);
            }
        }
        
        fclose($handle);
    }
    
    return $data;
}

/**
 * Import students
 */
function importStudents($data) {
    $imported = 0;
    $skipped = 0;
    
    foreach ($data as $row) {
        // Validate required fields
        $requiredFields = ['prn', 'first_name', 'last_name', 'email', 'phone', 'dob', 'gender', 'department', 'class', 'division', 'roll_number', 'batch_year'];
        
        $missingFields = array_filter($requiredFields, function($field) use ($row) {
            return empty($row[$field]);
        });
        
        if (!empty($missingFields)) {
            $skipped++;
            continue;
        }
        
        // Check if student already exists
        $checkQuery = "SELECT id FROM students WHERE prn = ? OR email = ?";
        $checkResult = executeQuery($checkQuery, "ss", array($row['prn'], $row['email']));
        
        if ($checkResult && $checkResult->num_rows > 0) {
            $skipped++;
            continue;
        }
        
        // Insert student
        $insertQuery = "INSERT INTO students (
            prn, first_name, middle_name, last_name, email, phone, dob, gender,
            address, city, state, department, class, division, roll_number,
            batch_year, parent_name, parent_phone, is_active
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
        
        $result = executeUpdate(
            $insertQuery,
            "ssssssssssssssssss",
            array(
                sanitizeInput($row['prn']),
                sanitizeInput($row['first_name']),
                sanitizeInput($row['middle_name'] ?? ''),
                sanitizeInput($row['last_name']),
                sanitizeInput($row['email']),
                sanitizeInput($row['phone']),
                sanitizeInput($row['dob']),
                sanitizeInput($row['gender']),
                sanitizeInput($row['address'] ?? ''),
                sanitizeInput($row['city'] ?? ''),
                sanitizeInput($row['state'] ?? ''),
                sanitizeInput($row['department']),
                sanitizeInput($row['class']),
                sanitizeInput($row['division']),
                sanitizeInput($row['roll_number']),
                sanitizeInput($row['batch_year']),
                sanitizeInput($row['parent_name'] ?? ''),
                sanitizeInput($row['parent_phone'] ?? '')
            )
        );
        
        if ($result) {
            $imported++;
        } else {
            $skipped++;
        }
    }
    
    return array(
        'success' => true,
        'imported' => $imported,
        'skipped' => $skipped
    );
}

/**
 * Import teachers
 */
function importTeachers($data) {
    $imported = 0;
    $skipped = 0;
    
    foreach ($data as $row) {
        // Validate required fields
        $requiredFields = ['first_name', 'last_name', 'email', 'phone', 'gender', 'department', 'designation', 'qualification', 'experience'];
        
        $missingFields = array_filter($requiredFields, function($field) use ($row) {
            return empty($row[$field]);
        });
        
        if (!empty($missingFields)) {
            $skipped++;
            continue;
        }
        
        // Check if teacher already exists
        $checkQuery = "SELECT id FROM teachers WHERE email = ?";
        $checkResult = executeQuery($checkQuery, "s", array($row['email']));
        
        if ($checkResult && $checkResult->num_rows > 0) {
            $skipped++;
            continue;
        }
        
        // Insert teacher
        $insertQuery = "INSERT INTO teachers (
            first_name, middle_name, last_name, email, phone, gender,
            address, city, state, department, designation, qualification,
            experience, is_active
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
        
        $result = executeUpdate(
            $insertQuery,
            "ssssssssssssi",
            array(
                sanitizeInput($row['first_name']),
                sanitizeInput($row['middle_name'] ?? ''),
                sanitizeInput($row['last_name']),
                sanitizeInput($row['email']),
                sanitizeInput($row['phone']),
                sanitizeInput($row['gender']),
                sanitizeInput($row['address'] ?? ''),
                sanitizeInput($row['city'] ?? ''),
                sanitizeInput($row['state'] ?? ''),
                sanitizeInput($row['department']),
                sanitizeInput($row['designation']),
                sanitizeInput($row['qualification']),
                (int)sanitizeInput($row['experience'])
            )
        );
        
        if ($result) {
            $imported++;
        } else {
            $skipped++;
        }
    }
    
    return array(
        'success' => true,
        'imported' => $imported,
        'skipped' => $skipped
    );
}
?>