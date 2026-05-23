<?php
/**
 * Database Connection & Helper Functions
 * MITAOE Portal - Main Database Configuration
 */

// Enable error logging (but don't display errors in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mitaoe_portal');
define('DB_PORT', 3306);
define('DB_CHARSET', 'utf8mb4');

// Create global connection
$conn = null;

/**
 * Initialize database connection
 */
function initDatabase() {
    global $conn;
    
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        
        // Check connection
        if ($conn->connect_error) {
            error_log("Database Connection Failed: " . $conn->connect_error);
            die("Database connection failed. Please contact administrator.");
        }
        
        // Set charset
        if (!$conn->set_charset(DB_CHARSET)) {
            error_log("Error setting charset: " . $conn->error);
        }
    }
    
    return $conn;
}

// Initialize connection immediately
initDatabase();

/**
 * Execute SELECT queries
 * @param string $query SQL query
 * @param string $types Parameter types (e.g., "ssi" for string, string, int)
 * @param array $params Parameters to bind
 * @return mysqli_result|false
 */
function executeQuery($query, $types = "", $params = array()) {
    global $conn;
    
    // Make sure connection exists
    if ($conn === null) {
        initDatabase();
    }
    
    // If no parameters, execute directly
    if (empty($types) || empty($params)) {
        $result = $conn->query($query);
        if (!$result) {
            error_log("Query failed: " . $conn->error . " | Query: " . $query);
        }
        return $result;
    }
    
    // Prepare statement
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error . " | Query: " . $query);
        return false;
    }
    
    // Bind parameters
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    // Execute
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error . " | Query: " . $query);
        $stmt->close();
        return false;
    }
    
    // Get result
    $result = $stmt->get_result();
    
    return $result;
}

/**
 * Execute INSERT, UPDATE, DELETE queries
 * @param string $query SQL query
 * @param string $types Parameter types
 * @param array $params Parameters to bind
 * @return bool Success status
 */
function executeUpdate($query, $types = "", $params = array()) {
    global $conn;
    
    // Make sure connection exists
    if ($conn === null) {
        initDatabase();
    }
    
    // If no parameters, execute directly
    if (empty($types) || empty($params)) {
        $result = $conn->query($query);
        if (!$result) {
            error_log("Update failed: " . $conn->error . " | Query: " . $query);
        }
        return $result;
    }
    
    // Prepare statement
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error . " | Query: " . $query);
        return false;
    }
    
    // Bind parameters
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    // Execute
    $result = $stmt->execute();
    if (!$result) {
        error_log("Execute failed: " . $stmt->error . " | Query: " . $query);
    }
    
    $stmt->close();
    
    return $result;
}

/**
 * Sanitize user input
 * @param mixed $data Input data
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    if (is_null($data)) {
        return null;
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Get last inserted ID
 * @return int Last insert ID
 */
function getLastInsertId() {
    global $conn;
    
    if ($conn === null) {
        initDatabase();
    }
    
    return $conn->insert_id;
}

/**
 * Close database connection
 */
function closeConnection() {
    global $conn;
    
    if ($conn !== null && $conn instanceof mysqli) {
        $conn->close();
        $conn = null;
    }
}

/**
 * Record login attempts (for security logging)
 * @param string $user_type Type: 'admin' or 'faculty'
 * @param int $user_id User ID (0 if not found)
 * @param string $identifier Username/Email
 * @param string $status 'success' or 'failed'
 * @param string $details Additional details
 * @return bool Success status
 */
function recordLogin($user_type, $user_id, $identifier, $status, $details = '') {
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'Unknown';
    $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown';
    
    $query = "INSERT INTO login_logs (user_type, user_id, identifier, status, details, ip_address, user_agent) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    // ✅ FIXED: Changed "sissss" to "sisssss" (7 chars for 7 params)
    return executeUpdate($query, "sisssss", array($user_type, $user_id, $identifier, $status, $details, $ip, $userAgent));
}

/**
 * Check if database connection is alive
 * @return bool Connection status
 */
function isDatabaseConnected() {
    global $conn;
    
    if ($conn === null) {
        return false;
    }
    
    return $conn->ping();
}

/**
 * Escape string for SQL queries (use with caution, prefer prepared statements)
 * @param string $string String to escape
 * @return string Escaped string
 */
function escapeString($string) {
    global $conn;
    
    if ($conn === null) {
        initDatabase();
    }
    
    return $conn->real_escape_string($string);
}

// Register shutdown function to close connection
register_shutdown_function('closeConnection');
?>