<?php
// CRITICAL: Start output buffering to prevent any output before JSON
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

session_start();

// Check authentication
if (!isset($_SESSION['admin_id'])) {
    ob_clean(); // Clear any output
    $response = array('success' => false, 'message' => 'Unauthorized access!');
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

require_once 'db_connect.php';

$response = array('success' => false, 'message' => '');

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['type']) || !isset($data['ids'])) {
    ob_clean();
    $response['message'] = 'Invalid request data!';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

$type = $data['type'];
$ids = $data['ids'];

// Validate type
if (!in_array($type, ['student', 'teacher'])) {
    ob_clean();
    $response['message'] = 'Invalid record type!';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Validate IDs
if (!is_array($ids) || empty($ids)) {
    ob_clean();
    $response['message'] = 'No records selected for deletion!';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Sanitize IDs (ensure they are integers)
$ids = array_map('intval', $ids);
$ids = array_filter($ids, function($id) { return $id > 0; });

if (empty($ids)) {
    ob_clean();
    $response['message'] = 'Invalid record IDs!';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

try {
    // Determine table
    $table = $type === 'student' ? 'students' : 'teachers';
    
    // Create placeholders for prepared statement
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    
    // Delete records
    $deleteQuery = "DELETE FROM $table WHERE id IN ($placeholders)";
    
    // Prepare statement
    $stmt = $GLOBALS['conn']->prepare($deleteQuery);
    if (!$stmt) {
        throw new Exception('Failed to prepare delete statement: ' . $GLOBALS['conn']->error);
    }
    
    // Bind parameters
    $stmt->bind_param($types, ...$ids);
    
    // Execute
    $result = $stmt->execute();
    
    if ($result) {
        $deleted_count = $stmt->affected_rows;
        
        if ($deleted_count > 0) {
            // Log activity
            $admin_id = $_SESSION['admin_id'];
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            
            $logQuery = "INSERT INTO activity_logs (admin_id, action, details, ip_address, user_agent) 
                         VALUES (?, ?, ?, ?, ?)";
            
            $action = strtoupper('BULK_DELETE_' . $type);
            $details = "Bulk deleted $deleted_count {$type}(s). IDs: " . implode(', ', $ids);
            
            executeUpdate($logQuery, "issss", array(
                $admin_id,
                $action,
                $details,
                $ip,
                $userAgent
            ));
            
            ob_clean(); // Clear any output before JSON
            $response['success'] = true;
            $response['message'] = "Successfully deleted $deleted_count " . ($deleted_count === 1 ? $type : $type . 's') . "!";
            $response['deleted_count'] = $deleted_count;
        } else {
            ob_clean();
            $response['message'] = 'No records were deleted. They may have already been removed.';
        }
    } else {
        ob_clean();
        $response['message'] = 'Failed to delete records: ' . $stmt->error;
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    ob_clean();
    $response['message'] = 'An error occurred: ' . $e->getMessage();
    error_log("Bulk Delete Error: " . $e->getMessage());
}

// Clear output buffer and send JSON
ob_clean();
header('Content-Type: application/json');
echo json_encode($response);
ob_end_flush();
exit();
?>