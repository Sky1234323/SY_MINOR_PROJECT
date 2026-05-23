<?php
session_start();
require_once 'db_connect.php';

$response = array('success' => false, 'results' => array(), 'message' => '');

if (!isset($_SESSION['faculty_id'])) {
    $response['message'] = 'Unauthorized!';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $search_query = sanitizeInput($_POST['search_query']);
    $search_type = sanitizeInput($_POST['search_type']); // 'name', 'surname', 'prn', 'city', 'email', 'phone'

    if (empty($search_query)) {
        $response['message'] = 'Search query required!';
        echo json_encode($response);
        exit();
    }

    $searchTerm = '%' . $search_query . '%';
    $results = array();

    // Search Students
    if ($search_type == 'name') {
        $query = "SELECT id, 'student' as type, first_name, middle_name, last_name, prn, email, phone, 
                         city, department FROM students 
                  WHERE (first_name LIKE ? OR middle_name LIKE ?) AND is_active = 1";
        $result = executeQuery($query, "ss", array($searchTerm, $searchTerm));
    } elseif ($search_type == 'surname') {
        $query = "SELECT id, 'student' as type, first_name, middle_name, last_name, prn, email, phone, 
                         city, department FROM students 
                  WHERE last_name LIKE ? AND is_active = 1";
        $result = executeQuery($query, "s", array($searchTerm));
    } elseif ($search_type == 'prn') {
        $query = "SELECT id, 'student' as type, first_name, middle_name, last_name, prn, email, phone, 
                         city, department FROM students 
                  WHERE prn LIKE ? AND is_active = 1";
        $result = executeQuery($query, "s", array($searchTerm));
    } elseif ($search_type == 'email') {
        $query = "SELECT id, 'student' as type, first_name, middle_name, last_name, prn, email, phone, 
                         city, department FROM students 
                  WHERE email LIKE ? AND is_active = 1";
        $result = executeQuery($query, "s", array($searchTerm));
    } elseif ($search_type == 'city') {
        $query = "SELECT id, 'student' as type, first_name, middle_name, last_name, prn, email, phone, 
                         city, department FROM students 
                  WHERE city LIKE ? AND is_active = 1";
        $result = executeQuery($query, "s", array($searchTerm));
    } elseif ($search_type == 'phone') {
        $query = "SELECT id, 'student' as type, first_name, middle_name, last_name, prn, email, phone, 
                         city, department FROM students 
                  WHERE phone LIKE ? AND is_active = 1";
        $result = executeQuery($query, "s", array($searchTerm));
    }

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $row['full_name'] = $row['first_name'] . ' ' . 
                               ($row['middle_name'] ? $row['middle_name'] . ' ' : '') . 
                               $row['last_name'];
            $row['category'] = 'Student';
            $results[] = $row;
        }
    }

    // Search Teachers (same search type)
    if ($search_type == 'name') {
        $query = "SELECT id, 'teacher' as type, first_name, middle_name, last_name, email, phone, 
                         city, department, designation FROM teachers 
                  WHERE (first_name LIKE ? OR middle_name LIKE ?) AND is_active = 1";
        $result = executeQuery($query, "ss", array($searchTerm, $searchTerm));
    } elseif ($search_type == 'surname') {
        $query = "SELECT id, 'teacher' as type, first_name, middle_name, last_name, email, phone, 
                         city, department, designation FROM teachers 
                  WHERE last_name LIKE ? AND is_active = 1";
        $result = executeQuery($query, "s", array($searchTerm));
    } elseif ($search_type == 'email') {
        $query = "SELECT id, 'teacher' as type, first_name, middle_name, last_name, email, phone, 
                         city, department, designation FROM teachers 
                  WHERE email LIKE ? AND is_active = 1";
        $result = executeQuery($query, "s", array($searchTerm));
    } elseif ($search_type == 'city') {
        $query = "SELECT id, 'teacher' as type, first_name, middle_name, last_name, email, phone, 
                         city, department, designation FROM teachers 
                  WHERE city LIKE ? AND is_active = 1";
        $result = executeQuery($query, "s", array($searchTerm));
    } elseif ($search_type == 'phone') {
        $query = "SELECT id, 'teacher' as type, first_name, middle_name, last_name, email, phone, 
                         city, department, designation FROM teachers 
                  WHERE phone LIKE ? AND is_active = 1";
        $result = executeQuery($query, "s", array($searchTerm));
    }

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $row['full_name'] = $row['first_name'] . ' ' . 
                               ($row['middle_name'] ? $row['middle_name'] . ' ' : '') . 
                               $row['last_name'];
            $row['category'] = 'Teacher';
            $results[] = $row;
        }
    }

    if (count($results) > 0) {
        $response['success'] = true;
        $response['results'] = $results;
        $response['message'] = count($results) . ' result(s) found!';
    } else {
        $response['message'] = 'No results found!';
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>