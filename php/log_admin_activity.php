<?php
require_once 'db_connect.php';

/**
 * Log admin activity for audit trail
 * @param $admin_id - Admin ID
 * @param $action - Action type (ADD_STUDENT, DELETE_STUDENT, etc.)
 * @param $details - Details of the action
 * @param $record_id - ID of record affected (student/teacher ID)
 */
function logAdminActivity($admin_id, $action, $details, $record_id = null) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $query = "INSERT INTO activity_logs (admin_id, action, details, record_id, ip_address, user_agent) 
              VALUES (?, ?, ?, ?, ?, ?)";
    
    return executeUpdate($query, "isssiss", array(
        $admin_id, $action, $details, $record_id, $ip, $user_agent
    ));<?php
require_once 'db_connect.php';

/**
 * Log admin activity for audit trail
 * @param $admin_id - Admin ID
 * @param $action - Action type (ADD_STUDENT, DELETE_STUDENT, etc.)
 * @param $details - Details of the action
 * @param $record_id - ID of record affected (student/teacher ID)
 */
function logAdminActivity($admin_id, $action, $details, $record_id = null) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $query = "INSERT INTO activity_logs (admin_id, action, details, record_id, ip_address, user_agent) 
              VALUES (?, ?, ?, ?, ?, ?)";
    
    return executeUpdate($query, "ississ", array(
        $admin_id, $action, $details, $record_id, $ip, $user_agent
    ));
}

/**
 * Get admin activity logs
 * @param $admin_id - Admin ID (null for all)
 * @param $limit - Number of records to fetch
 * @return - Query result
 */
function getActivityLogs($admin_id = null, $limit = 100) {
    if ($admin_id) {
        $query = "SELECT * FROM activity_logs WHERE admin_id = ? 
                  ORDER BY created_at DESC LIMIT ?";
        return executeQuery($query, "ii", array($admin_id, $limit));
    } else {
        $query = "SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT ?";
        return executeQuery($query, "i", array($limit));
    }
}
?>
}

/**
 * Get admin activity logs
 * @param $admin_id - Admin ID (null for all)
 * @param $limit - Number of records to fetch
 * @return - Query result
 */
function getActivityLogs($admin_id = null, $limit = 100) {
    if ($admin_id) {
        $query = "SELECT * FROM activity_logs WHERE admin_id = ? 
                  ORDER BY created_at DESC LIMIT ?";
        return executeQuery($query, "ii", array($admin_id, $limit));
    } else {
        $query = "SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT ?";
        return executeQuery($query, "i", array($limit));
    }
}
?>