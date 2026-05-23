<?php
session_start();
require_once 'db_connect.php';

$response = array('success' => false, 'students' => array(), 'total' => 0, 'message' => '');

if (!isset($_SESSION['admin_id'])) {
    $response['message'] = 'Unauthorized!';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;
    $sort_by = isset($_POST['sort_by']) ? sanitizeInput($_POST['sort_by']) : 'created_at';
    $sort_order = isset($_POST['sort_order']) ? sanitizeInput($_POST['sort_order']) : 'DESC';

    // Validate sort order
    if (!in_array($sort_order, array('ASC', 'DESC'))) {
        $sort_order = 'DESC';
    }

    // Validate sort column
    $allowed_sorts = array('prn', 'first_name', 'last_name', 'email', 'department', 'created_at');
    if (!in_array($sort_by, $allowed_sorts)) {
        $sort_by = 'created_at';
    }

    // Calculate offset
    $offset = ($page - 1) * $limit;

    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM students WHERE is_active = 1";
    $countResult = executeQuery($countQuery);
    $countRow = $countResult->fetch_assoc();
    $total = $countRow['total'];

    // Get students with pagination
    $query = "SELECT id, prn, first_name, middle_name, last_name, email, phone, city, 
                     department, class, division, roll_number, batch_year, created_at 
              FROM students 
              WHERE is_active = 1 
              ORDER BY $sort_by $sort_order 
              LIMIT ? OFFSET ?";

    $result = executeQuery($query, "ii", array($limit, $offset));

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $row['full_name'] = $row['first_name'] . ' ' . 
                               ($row['middle_name'] ? $row['middle_name'] . ' ' : '') . 
                               $row['last_name'];
            $response['students'][] = $row;
        }
        $response['success'] = true;
        $response['total'] = $total;
        $response['page'] = $page;
        $response['limit'] = $limit;
        $response['total_pages'] = ceil($total / $limit);
        $response['message'] = 'Students retrieved successfully!';
    } else {
        $response['message'] = 'No students found!';
        $response['success'] = true;
        $response['total'] = 0;
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>