<?php
session_start();
require_once 'db_connect.php';

$response = array('success' => false, 'students' => array(), 'message' => '');

if (!isset($_SESSION['admin_id'])) {
    $response['message'] = 'Unauthorized!';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $search_query = sanitizeInput($_POST['search_query']);
    $search_type = sanitizeInput($_POST['search_type']); // 'name', 'prn', 'email', 'city', 'phone'

    if (empty($search_query)) {
        $response['message'] = 'Search query required!';
        echo json_encode($response);
        exit();
    }

    // Build query based on search type
    if ($search_type == 'name') {
        $query = "SELECT id, prn, first_name, middle_name, last_name, email, phone, city, department, class FROM students 
                  WHERE (first_name LIKE ? OR middle_name LIKE ? OR last_name LIKE ?) AND is_active = 1";
        $searchTerm = '%' . $search_query . '%';
        $result = executeQuery($query, "sss", array($searchTerm, $searchTerm, $searchTerm));
    } elseif ($search_type == 'prn') {
        $query = "SELECT id, prn, first_name, middle_name, last_name, email, phone, city, department, class FROM students 
                  WHERE prn LIKE ? AND is_active = 1";
        $searchTerm = '%' . $search_query . '%';
        $result = executeQuery($query, "s", array($searchTerm));
    } elseif ($search_type == 'email') {
        $query = "SELECT id, prn, first_name, middle_name, last_name, email, phone, city, department, class FROM students 
                  WHERE email LIKE ? AND is_active = 1";
        $searchTerm = '%' . $search_query . '%';
        $result = executeQuery($query, "s", array($searchTerm));
    } elseif ($search_type == 'city') {
        $query = "SELECT id, prn, first_name, middle_name, last_name, email, phone, city, department, class FROM students 
                  WHERE city LIKE ? AND is_active = 1";
        $searchTerm = '%' . $search_query . '%';
        $result = executeQuery($query, "s", array($searchTerm));
    } elseif ($search_type == 'phone') {
        $query = "SELECT id, prn, first_name, middle_name, last_name, email, phone, city, department, class FROM students 
                  WHERE phone LIKE ? AND is_active = 1";
        $searchTerm = '%' . $search_query . '%';
        $result = executeQuery($query, "s", array($searchTerm));
    } else {
        $response['message'] = 'Invalid search type!';
        echo json_encode($response);
        exit();
    }

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $row['full_name'] = $row['first_name'] . ' ' . ($row['middle_name'] ? $row['middle_name'] . ' ' : '') . $row['last_name'];
            $response['students'][] = $row;
        }
        $response['success'] = true;
        $response['message'] = $result->num_rows . ' student(s) found!';
    } else {
        $response['message'] = 'No students found!';
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>