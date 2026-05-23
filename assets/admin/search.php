<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

require_once '../php/db_connect.php';

// Get admin data
$admin_query = "SELECT full_name FROM admin WHERE id = ?";
$admin_result = executeQuery($admin_query, "i", array($_SESSION['admin_id']));
$admin_data = $admin_result->fetch_assoc();

// Get search parameters
$search_type = isset($_GET['search_type']) ? $_GET['search_type'] : 'students';
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$department = isset($_GET['department']) ? trim($_GET['department']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$class = isset($_GET['class']) ? trim($_GET['class']) : '';
$designation = isset($_GET['designation']) ? trim($_GET['designation']) : '';

// Departments list
$departments = ['Computer Science', 'Information Technology', 'Electronics', 'Mechanical', 'Civil', 'Electrical'];

// Initialize results
$results = array();
$total_results = 0;

// Execute search if query provided
if (!empty($search_query) || !empty($department) || !empty($status) || !empty($class) || !empty($designation)) {
    if ($search_type === 'students') {
        // Build student search query
        $sql = "SELECT * FROM students WHERE 1=1";
        $params = array();
        $types = "";
        
        if (!empty($search_query)) {
            $sql .= " AND (prn LIKE ? OR first_name LIKE ? OR middle_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
            $search_param = "%$search_query%";
            $params = array_merge($params, array($search_param, $search_param, $search_param, $search_param, $search_param, $search_param));
            $types .= "ssssss";
        }
        
        if (!empty($department)) {
            $sql .= " AND department = ?";
            $params[] = $department;
            $types .= "s";
        }
        
        if (!empty($status)) {
            $sql .= " AND is_active = ?";
            $params[] = (int)$status;
            $types .= "i";
        }
        
        if (!empty($class)) {
            $sql .= " AND class = ?";
            $params[] = $class;
            $types .= "s";
        }
        
        $sql .= " ORDER BY first_name ASC LIMIT 50";
        
        $result = executeQuery($sql, $types, $params);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $results[] = $row;
            }
            $total_results = count($results);
        }
        
    } else {
        // Build teacher search query
        $sql = "SELECT * FROM teachers WHERE 1=1";
        $params = array();
        $types = "";
        
        if (!empty($search_query)) {
            $sql .= " AND (first_name LIKE ? OR middle_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
            $search_param = "%$search_query%";
            $params = array_merge($params, array($search_param, $search_param, $search_param, $search_param, $search_param));
            $types .= "sssss";
        }
        
        if (!empty($department)) {
            $sql .= " AND department = ?";
            $params[] = $department;
            $types .= "s";
        }
        
        if (!empty($status)) {
            $sql .= " AND is_active = ?";
            $params[] = (int)$status;
            $types .= "i";
        }
        
        if (!empty($designation)) {
            $sql .= " AND designation = ?";
            $params[] = $designation;
            $types .= "s";
        }
        
        $sql .= " ORDER BY first_name ASC LIMIT 50";
        
        $result = executeQuery($sql, $types, $params);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $results[] = $row;
            }
            $total_results = count($results);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Search - MITAOE Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        /* Background Particles */
        .bg-particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            background: rgba(78, 166, 133, 0.1);
            border-radius: 50%;
            animation: float 20s infinite ease-in-out;
        }

        .particle:nth-child(1) { width: 80px; height: 80px; top: 10%; left: 10%; animation-delay: 0s; }
        .particle:nth-child(2) { width: 60px; height: 60px; top: 60%; left: 80%; animation-delay: 2s; }
        .particle:nth-child(3) { width: 100px; height: 100px; top: 80%; left: 20%; animation-delay: 4s; }
        .particle:nth-child(4) { width: 50px; height: 50px; top: 30%; left: 70%; animation-delay: 6s; }
        .particle:nth-child(5) { width: 70px; height: 70px; top: 50%; left: 50%; animation-delay: 8s; }

        @keyframes float {
            0%, 100% { transform: translateY(0) translateX(0) scale(1); opacity: 0.3; }
            25% { transform: translateY(-30px) translateX(20px) scale(1.1); opacity: 0.5; }
            50% { transform: translateY(-60px) translateX(-20px) scale(0.9); opacity: 0.3; }
            75% { transform: translateY(-30px) translateX(30px) scale(1.05); opacity: 0.4; }
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 260px;
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            color: white;
            z-index: 100;
            overflow-y: auto;
        }

        .sidebar-logo {
            padding: 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-logo i {
            font-size: 2rem;
            color: #4EA685;
            margin-bottom: 0.5rem;
        }

        .sidebar-logo h1 {
            font-size: 1.25rem;
            font-weight: 700;
        }

        .sidebar-logo p {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.7);
            margin-top: 0.25rem;
        }

        .sidebar-menu {
            padding: 1rem 0;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.5rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .menu-item:hover {
            background: rgba(255,255,255,0.05);
            color: white;
        }

        .menu-item.active {
            background: rgba(78,166,133,0.2);
            color: white;
            border-left-color: #4EA685;
        }

        .menu-item i {
            font-size: 1.25rem;
            color: #4EA685;
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
            position: relative;
            z-index: 1;
        }

        /* Header */
        .header {
            position: sticky;
            top: 0;
            height: 70px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            z-index: 50;
        }

        .breadcrumb {
            font-size: 0.9375rem;
            color: #718096;
        }

        .breadcrumb a {
            color: #4EA685;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .breadcrumb span {
            color: #4EA685;
            font-weight: 600;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(78,166,133,0.1);
            color: #4EA685;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: rgba(78,166,133,0.2);
            transform: translateX(-5px);
        }

        /* Page Content */
        .page-content {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Page Header */
        .page-header {
            text-align: center;
            margin-bottom: 2rem;
            animation: fadeInDown 0.6s ease;
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #4EA685, #57B894);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .page-header p {
            font-size: 1rem;
            color: #718096;
            font-weight: 500;
        }

        /* Search Card */
        .search-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            animation: fadeInUp 0.6s ease;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Type Selector */
        .type-selector {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            justify-content: center;
        }

        .type-btn {
            padding: 0.75rem 2rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 3px solid #e5e7eb;
            background: white;
            color: #718096;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }

        .type-btn.active {
            background: linear-gradient(135deg, #4EA685, #57B894);
            color: white;
            border-color: #4EA685;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(78,166,133,0.3);
        }

        .type-btn:hover:not(.active) {
            border-color: #4EA685;
            transform: translateY(-2px);
        }

        /* Search Form */
        .search-form {
            display: grid;
            gap: 2rem;
        }

        .search-main {
            position: relative;
            margin-bottom: 1.5rem; 
        }

        .search-icon {
            position: absolute;
            left: 1.5rem;
            top: 50%;
            transform: translateY(-50%);
            color: #4EA685;
            font-size: 1.5rem;
        }

        .search-input {
            width: 100%;
            padding: 1.25rem 1.5rem 1.25rem 4rem;
            font-size: 1.125rem;
            border: 3px solid #e5e7eb;
            border-radius: 15px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #4EA685;
            box-shadow: 0 0 0 6px rgba(78,166,133,0.1);
        }

        /* Filters */
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem; /* Increased from 1rem */
            margin-top: 1.5rem; /* Added margin */
            margin-bottom: 2rem; /* Added margin */
        }
        
        .filter-group {
            margin-bottom: 0; /* Ensure no extra margin */
        }

        .filter-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.75rem; /* Increased from 0.5rem */
        }



        .filter-select {
            width: 100%;
            padding: 0.875rem 1rem; /* Increased from 0.75rem */
            font-size: 0.9375rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            cursor: pointer;
        }


        .filter-select:focus {
            outline: none;
            border-color: #4EA685;
            box-shadow: 0 0 0 4px rgba(78,166,133,0.1);
        }

        /* Search Button */
        .search-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2.5rem; /* Added margin */
            padding-top: 1.5rem; /* Added padding */
            border-top: 2px solid #f3f4f6; /* Added visual separator */
        }


       .btn {
            padding: 1rem 2.5rem;
            font-size: 1rem;
            font-weight: 700;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            justify-content: center; /* Added */
        }

        .btn-primary {
            background: linear-gradient(135deg, #4EA685, #57B894);
            color: white;
            box-shadow: 0 6px 20px rgba(78,166,133,0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(78,166,133,0.4);
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #2c3e50;
        }

        .btn-secondary:hover {
            background: #d1d5db;
        }

        /* Results */
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .results-count {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2c3e50;
        }

        .results-count span {
            color: #4EA685;
            font-size: 1.5rem;
        }

        /* Results Table */
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        thead {
            background: linear-gradient(135deg, #4EA685, #57B894);
        }

        thead th {
            padding: 1rem;
            text-align: left;
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
        }

        tbody tr {
            border-bottom: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        tbody tr:hover {
            background: rgba(78,166,133,0.05);
        }

        tbody td {
            padding: 1rem;
            font-size: 0.9375rem;
            color: #2c3e50;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-active {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
        }

        .status-inactive {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .action-btns {
            display: flex;
            gap: 0.5rem;
        }

        .action-btn {
            padding: 0.5rem;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-view {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .btn-view:hover {
            background: rgba(59, 130, 246, 0.2);
        }

        .btn-edit {
            background: rgba(251, 191, 36, 0.1);
            color: #fbbf24;
        }

        .btn-edit:hover {
            background: rgba(251, 191, 36, 0.2);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-state i {
            font-size: 5rem;
            color: #e5e7eb;
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            color: #718096;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #a0aec0;
        }
        /* ============================================
   COMPREHENSIVE RESPONSIVE DESIGN
   ============================================ */

/* Tablet - 1024px and below */
@media (max-width: 1024px) {
    .sidebar {
        transform: translateX(-100%);
    }

    .sidebar.mobile-show {
        transform: translateX(0);
        box-shadow: 2px 0 20px rgba(0,0,0,0.2);
    }

    .main-content {
        margin-left: 0;
    }

    /* Show hamburger */
    .hamburger {
        display: block;
        font-size: 1.5rem;
        cursor: pointer;
        color: #2c3e50;
    }

    /* Filters - 2 columns on tablet */
    .filters-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1.25rem;
    }

    /* Search card */
    .search-card {
        padding: 2rem;
    }

    /* Overlay */
    .overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 90;
    }

    .overlay.show {
        display: block;
    }
}

/* Mobile Large - 768px and below */
@media (max-width: 768px) {
    .header {
        padding: 0 1rem;
        height: 60px;
    }

    .breadcrumb {
        font-size: 0.875rem;
    }

    .breadcrumb a {
        display: none;
    }

    .back-btn {
        padding: 0.4rem 0.75rem;
        font-size: 0.8125rem;
    }

    .back-btn span {
        display: none;
    }

    /* Page content */
    .page-content {
        padding: 1.5rem 1rem;
    }

    /* Page header */
    .page-header h1 {
        font-size: 1.75rem;
    }

    .page-header p {
        font-size: 0.9375rem;
    }

    /* Type selector - vertical */
    .type-selector {
        flex-direction: column;
        gap: 0.75rem;
    }

    .type-btn {
        width: 100%;
        justify-content: center;
        padding: 0.875rem 1.5rem;
    }

    /* Search card */
    .search-card {
        padding: 1.5rem;
        border-radius: 15px;
    }

    /* Main search box */
    .search-main {
        margin-bottom: 1.25rem;
    }

    .search-input {
        padding: 1rem 1.25rem 1rem 3.5rem;
        font-size: 1rem;
        border-radius: 12px;
    }

    .search-icon {
        left: 1.25rem;
        font-size: 1.25rem;
    }

    /* Filters - single column on mobile */
    .filters-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
        margin-top: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .filter-group label {
        font-size: 0.8125rem;
        margin-bottom: 0.625rem;
    }

    .filter-select {
        padding: 0.75rem 0.875rem;
        font-size: 0.875rem;
        border-radius: 8px;
    }

    /* Search actions - vertical */
    .search-actions {
        flex-direction: column;
        gap: 0.75rem;
        margin-top: 2rem;
        padding-top: 1.25rem;
    }

    .btn {
        width: 100%;
        justify-content: center;
        padding: 0.875rem 1.5rem;
        font-size: 0.9375rem;
    }

    /* Results header */
    .results-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

    .results-count {
        font-size: 1.125rem;
    }

    .results-count span {
        font-size: 1.25rem;
    }

    .results-header a {
        width: 100%;
        justify-content: center;
        padding: 0.625rem 1rem;
    }

    /* ===== TABLE - IMPROVED FOR MOBILE ===== */
table {
    width: 100%;
    border-collapse: collapse;
}

thead {
    background: linear-gradient(135deg, #4EA685, #57B894);
}

thead th {
    padding: 1rem;
    text-align: left;
    color: white;
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

tbody tr {
    border-bottom: 1px solid #e5e7eb;
    transition: all 0.3s ease;
}

tbody tr:hover {
    background: rgba(78,166,133,0.05);
}

tbody td {
    padding: 1rem;
    font-size: 0.9375rem;
    color: #2c3e50;
}

/* Mobile improvements */
@media (max-width: 768px) {
    .table-card {
        padding: 1rem;
        overflow: hidden;
    }

    /* Force horizontal scroll with minimum width */
    table {
        min-width: 900px; /* Students table needs more space */
        font-size: 0.75rem;
    }

    thead th {
        padding: 0.75rem 0.5rem;
        font-size: 0.75rem;
    }

    tbody td {
        padding: 0.75rem 0.5rem;
        font-size: 0.75rem;
    }

    /* Sticky first column (PRN or Name) */
    thead th:first-child,
    tbody td:first-child {
        position: sticky;
        left: 0;
        z-index: 2;
        background: white;
        box-shadow: 2px 0 4px rgba(0,0,0,0.05);
    }

    thead th:first-child {
        background: linear-gradient(135deg, #4EA685, #57B894);
    }

    /* Column minimum widths for students */
    thead th:nth-child(1), tbody td:nth-child(1) { min-width: 100px; } /* PRN */
    thead th:nth-child(2), tbody td:nth-child(2) { min-width: 150px; } /* Name */
    thead th:nth-child(3), tbody td:nth-child(3) { min-width: 180px; } /* Email */
    thead th:nth-child(4), tbody td:nth-child(4) { min-width: 120px; } /* Phone */
    thead th:nth-child(5), tbody td:nth-child(5) { min-width: 140px; } /* Department */
    thead th:nth-child(6), tbody td:nth-child(6) { min-width: 80px; }  /* Class */
    thead th:nth-child(7), tbody td:nth-child(7) { min-width: 80px; }  /* Status */
    thead th:nth-child(8), tbody td:nth-child(8) { min-width: 120px; } /* Actions */

    /* Action buttons */
    .action-btns {
        display: flex;
        gap: 0.375rem;
        flex-wrap: nowrap;
    }

    .action-btn {
        padding: 0.375rem;
        flex-shrink: 0;
    }

    .status-badge {
        white-space: nowrap;
    }
}
/* ===== TABLE - IMPROVED FOR MOBILE ===== */
@media (max-width: 768px) {
    .table-card {
        padding: 1rem;
        overflow: hidden;
    }

    table {
        min-width: 850px;
        font-size: 0.75rem;
    }

    thead th {
        padding: 0.75rem 0.5rem;
        font-size: 0.75rem;
        white-space: nowrap;
    }

    tbody td {
        padding: 0.75rem 0.5rem;
        font-size: 0.75rem;
    }

    /* Sticky checkbox column */
    thead th:first-child,
    tbody td:first-child {
        position: sticky;
        left: 0;
        z-index: 2;
        background: white;
        box-shadow: 2px 0 4px rgba(0,0,0,0.05);
    }

    thead th:first-child {
        background: linear-gradient(135deg, #ef4444, #dc2626);
    }

    /* Column widths */
    thead th:nth-child(1), tbody td:nth-child(1) { min-width: 60px; }  /* Checkbox */
    thead th:nth-child(2), tbody td:nth-child(2) { min-width: 100px; } /* PRN/Name */
    thead th:nth-child(3), tbody td:nth-child(3) { min-width: 150px; } /* Name */
    thead th:nth-child(4), tbody td:nth-child(4) { min-width: 180px; } /* Email */
    thead th:nth-child(5), tbody td:nth-child(5) { min-width: 140px; } /* Department */
    thead th:nth-child(6), tbody td:nth-child(6) { min-width: 100px; } /* Class/Designation */
    thead th:nth-child(7), tbody td:nth-child(7) { min-width: 80px; }  /* Status */

    .record-checkbox {
        width: 18px;
        height: 18px;
    }

    .status-badge {
        white-space: nowrap;
    }
}

@media (max-width: 480px) {
    table {
        min-width: 750px;
        font-size: 0.6875rem;
    }

    thead th, tbody td {
        padding: 0.5rem 0.375rem;
        font-size: 0.6875rem;
    }

    .record-checkbox {
        width: 16px;
        height: 16px;
    }
}


    /* Action buttons in table */
    .action-btns {
        gap: 0.375rem;
    }

    .action-btn {
        padding: 0.375rem;
        border-radius: 6px;
    }

    .action-btn i {
        font-size: 1rem;
    }

    /* Status badges */
    .status-badge {
        padding: 0.1875rem 0.625rem;
        font-size: 0.6875rem;
    }

    /* Empty state */
    .empty-state {
        padding: 3rem 1.5rem;
    }

    .empty-state i {
        font-size: 4rem;
    }

    .empty-state h3 {
        font-size: 1.125rem;
    }

    .empty-state p {
        font-size: 0.875rem;
    }

    /* Reduce particles */
    .bg-particles .particle {
        opacity: 0.1 !important;
    }
}

/* Mobile Medium - 480px and below */
@media (max-width: 480px) {
    .page-content {
        padding: 1rem 0.75rem;
    }

    .page-header h1 {
        font-size: 1.5rem;
    }

    .page-header p {
        font-size: 0.875rem;
    }

    .search-card {
        padding: 1.25rem;
    }

    .search-input {
        padding: 0.875rem 1rem 0.875rem 3rem;
        font-size: 0.9375rem;
    }

    .search-icon {
        left: 1rem;
        font-size: 1.125rem;
    }

    .filter-group label {
        font-size: 0.75rem;
    }

    .filter-select {
        padding: 0.625rem 0.75rem;
        font-size: 0.8125rem;
    }

    .btn {
        padding: 0.75rem 1.25rem;
        font-size: 0.875rem;
    }

    /* Type selector */
    .type-btn {
        padding: 0.75rem 1.25rem;
        font-size: 0.875rem;
    }

    /* Results */
    .results-count {
        font-size: 1rem;
    }

    /* Table - even smaller */
    table {
        font-size: 0.6875rem;
    }

    thead th, tbody td {
        padding: 0.5rem 0.375rem;
        font-size: 0.6875rem;
    }

    /* Make first column sticky on horizontal scroll */
    thead th:first-child,
    tbody td:first-child {
        position: sticky;
        left: 0;
        background: white;
        z-index: 1;
        box-shadow: 2px 0 4px rgba(0,0,0,0.05);
    }

    thead th:first-child {
        background: linear-gradient(135deg, #4EA685, #57B894);
    }
}

/* Mobile Small - 360px and below */
@media (max-width: 360px) {
    .page-header h1 {
        font-size: 1.375rem;
    }

    .search-card {
        padding: 1rem;
    }

    .search-input {
        padding: 0.75rem 0.875rem 0.75rem 2.75rem;
        font-size: 0.875rem;
        border-radius: 10px;
    }

    .search-icon {
        left: 0.875rem;
    }

    .filter-select {
        padding: 0.5rem 0.625rem;
        font-size: 0.75rem;
    }

    .btn {
        padding: 0.625rem 1rem;
        font-size: 0.8125rem;
    }

    .type-btn {
        padding: 0.625rem 1rem;
        font-size: 0.8125rem;
    }

    table {
        font-size: 0.625rem;
    }

    thead th, tbody td {
        padding: 0.375rem 0.25rem;
        font-size: 0.625rem;
    }
}

/* Landscape mobile */
@media (max-height: 500px) and (orientation: landscape) {
    .page-header {
        margin-bottom: 1rem;
    }

    .search-card {
        padding: 1.5rem;
    }

    .filters-grid {
        margin-top: 1rem;
        margin-bottom: 1rem;
    }

    .search-actions {
        margin-top: 1.5rem;
    }
}

/* Desktop - prevent overlay */
@media (min-width: 1025px) {
    .overlay {
        display: none !important;
    }
}
        
    </style>
</head>
<body>
    <!-- Background Particles -->
    <div class="bg-particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>
    <!-- Sidebar -->
    <!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <i class='bx bxs-graduation'></i>
        <h1>MITAOE</h1>
        <p>Admin Panel</p>
    </div>

    <nav class="sidebar-menu">
        <a href="admin_dashboard.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'admin_dashboard.php' ? 'active' : ''; ?>">
            <i class='bx bxs-dashboard'></i>
            <span>Dashboard</span>
        </a>
        <a href="add_record.php?type=student" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'add_record.php' && isset($_GET['type']) && $_GET['type'] === 'student' ? 'active' : ''; ?>">
            <i class='bx bx-user-plus'></i>
            <span>Add Student</span>
        </a>
        <a href="add_record.php?type=teacher" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'add_record.php' && isset($_GET['type']) && $_GET['type'] === 'teacher' ? 'active' : ''; ?>">
            <i class='bx bx-user-plus'></i>
            <span>Add Teacher</span>
        </a>
        <a href="view_all.php?type=students" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'view_all.php' && isset($_GET['type']) && $_GET['type'] === 'students' ? 'active' : ''; ?>">
            <i class='bx bx-group'></i>
            <span>View All Students</span>
        </a>
        <a href="view_all.php?type=teachers" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'view_all.php' && isset($_GET['type']) && $_GET['type'] === 'teachers' ? 'active' : ''; ?>">
            <i class='bx bx-group'></i>
            <span>View All Teachers</span>
        </a>
        <a href="search.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'search.php' ? 'active' : ''; ?>">
            <i class='bx bx-search'></i>
            <span>Search Records</span>
        </a>
        <a href="delete_record.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'delete_record.php' ? 'active' : ''; ?>">
            <i class='bx bx-trash'></i>
            <span>Delete Records</span>
        </a>
        <a href="upload_excel.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'upload_excel.php' ? 'active' : ''; ?>">
            <i class='bx bx-upload'></i>
            <span>Upload Excel/CSV</span>
        </a>
        <a href="activity_logs.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'activity_logs.php' ? 'active' : ''; ?>">
            <i class='bx bx-history'></i>
            <span>Activity Logs</span>
        </a>
    </nav>
</aside>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <header class="header">
            <div class="breadcrumb">
                <a href="admin_dashboard.php">Admin</a> <span>&gt;</span> 
                <span>Advanced Search</span>
            </div>
            <div class="header-right">
                <a href="admin_dashboard.php" class="back-btn">
                    <i class='bx bx-arrow-back'></i>
                    Back to Dashboard
                </a>
            </div>
        </header>

        <!-- Page Content -->
        <div class="page-content">
            <!-- Page Header -->
            <div class="page-header">
                <h1>🔍 Advanced Search</h1>
                <p>Search for students and teachers with powerful filters</p>
            </div>

            <!-- Search Card -->
            <div class="search-card">
                <form method="GET" action="search.php" id="searchForm">
                    <!-- Type Selector -->
                    <div class="type-selector">
                        <button type="button" class="type-btn <?php echo $search_type === 'students' ? 'active' : ''; ?>" onclick="setSearchType('students')">
                            <i class='bx bxs-graduation'></i> Search Students
                        </button>
                        <button type="button" class="type-btn <?php echo $search_type === 'teachers' ? 'active' : ''; ?>" onclick="setSearchType('teachers')">
                            <i class='bx bxs-user-badge'></i> Search Teachers
                        </button>
                    </div>

                    <input type="hidden" name="search_type" id="search_type" value="<?php echo $search_type; ?>">

                    <!-- Main Search Box -->
                    <div class="search-main">
                        <i class='bx bx-search search-icon'></i>
                        <input 
                            type="text" 
                            name="q" 
                            class="search-input" 
                            placeholder="Search by name, email, phone, <?php echo $search_type === 'students' ? 'PRN' : 'designation'; ?>..." 
                            value="<?php echo htmlspecialchars($search_query); ?>"
                            autofocus>
                    </div>

                    <!-- Filters -->
                    <div class="filters-grid">
                        <!-- Department Filter -->
                        <div class="filter-group">
                            <label><i class='bx bx-building'></i> Department</label>
                            <select name="department" class="filter-select">
                                <option value="">All Departments</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?php echo $dept; ?>" <?php echo $department === $dept ? 'selected' : ''; ?>>
                                        <?php echo $dept; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div class="filter-group">
                            <label><i class='bx bx-check-circle'></i> Status</label>
                            <select name="status" class="filter-select">
                                <option value="">All Status</option>
                                <option value="1" <?php echo $status === '1' ? 'selected' : ''; ?>>Active</option>
                                <option value="0" <?php echo $status === '0' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>

                        <?php if ($search_type === 'students'): ?>
                            <!-- Class Filter (Students Only) -->
                            <div class="filter-group">
                                <label><i class='bx bx-book'></i> Class</label>
                                <select name="class" class="filter-select">
                                    <option value="">All Classes</option>
                                    <option value="FE" <?php echo $class === 'FE' ? 'selected' : ''; ?>>FE (First Year)</option>
                                    <option value="SE" <?php echo $class === 'SE' ? 'selected' : ''; ?>>SE (Second Year)</option>
                                    <option value="TE" <?php echo $class === 'TE' ? 'selected' : ''; ?>>TE (Third Year)</option>
                                    <option value="BE" <?php echo $class === 'BE' ? 'selected' : ''; ?>>BE (Final Year)</option>
                                </select>
                            </div>
                        <?php else: ?>
                            <!-- Designation Filter (Teachers Only) -->
                            <div class="filter-group">
                                <label><i class='bx bx-briefcase'></i> Designation</label>
                                <select name="designation" class="filter-select">
                                    <option value="">All Designations</option>
                                    <option value="Professor" <?php echo $designation === 'Professor' ? 'selected' : ''; ?>>Professor</option>
                                    <option value="Associate Professor" <?php echo $designation === 'Associate Professor' ? 'selected' : ''; ?>>Associate Professor</option>
                                    <option value="Assistant Professor" <?php echo $designation === 'Assistant Professor' ? 'selected' : ''; ?>>Assistant Professor</option>
                                    <option value="Lecturer" <?php echo $designation === 'Lecturer' ? 'selected' : ''; ?>>Lecturer</option>
                                    <option value="HOD" <?php echo $designation === 'HOD' ? 'selected' : ''; ?>>HOD</option>
                                    <option value="Lab Assistant" <?php echo $designation === 'Lab Assistant' ? 'selected' : ''; ?>>Lab Assistant</option>
                                </select>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Search Actions -->
                    <div class="search-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class='bx bx-search'></i>
                            Search Now
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="clearFilters()">
                            <i class='bx bx-reset'></i>
                            Clear Filters
                        </button>
                    </div>
                </form>
            </div>

            <!-- Results Section -->
            <?php if (!empty($search_query) || !empty($department) || !empty($status) || !empty($class) || !empty($designation)): ?>
                <div class="search-card">
                    <div class="results-header">
                        <h2 class="results-count">
                            Found <span><?php echo $total_results; ?></span> 
                            <?php echo $total_results === 1 ? 'result' : 'results'; ?>
                        </h2>
                        <?php if ($total_results > 0): ?>
                            <a href="view_all.php?type=<?php echo $search_type; ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                <i class='bx bx-list-ul'></i>
                                View All <?php echo ucfirst($search_type); ?>
                            </a>
                        <?php endif; ?>
                    </div>

                    <?php if ($total_results > 0): ?>
                        <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
    <table style="min-width: 900px;">
        <thead>
                                <tr>
                                    <?php if ($search_type === 'students'): ?>
                                        <th>PRN</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Department</th>
                                        <th>Class</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    <?php else: ?>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Department</th>
                                        <th>Designation</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $row): ?>
                                    <tr>
                                        <?php if ($search_type === 'students'): ?>
                                            <td><strong><?php echo htmlspecialchars($row['prn']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                            <td><?php echo htmlspecialchars($row['department']); ?></td>
                                            <td><?php echo htmlspecialchars($row['class']); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo $row['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                                    <?php echo $row['is_active'] ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-btns">
                                                    <a href="view_profile.php?type=student&id=<?php echo $row['id']; ?>" class="action-btn btn-view" title="View Details">
                                                        <i class='bx bx-show'></i>
                                                    </a>
                                                    <a href="edit_record.php?type=student&id=<?php echo $row['id']; ?>" class="action-btn btn-edit" title="Edit">
                                                        <i class='bx bx-edit'></i>
                                                    </a>
                                                </div>
                                            </td>
                                        <?php else: ?>
                                            <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                            <td><?php echo htmlspecialchars($row['department']); ?></td>
                                            <td><?php echo htmlspecialchars($row['designation']); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo $row['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                                    <?php echo $row['is_active'] ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-btns">
                                                    <a href="view_profile.php?type=teacher&id=<?php echo $row['id']; ?>" class="action-btn btn-view" title="View Details">
                                                        <i class='bx bx-show'></i>
                                                    </a>
                                                    <a href="edit_record.php?type=teacher&id=<?php echo $row['id']; ?>" class="action-btn btn-edit" title="Edit">
                                                        <i class='bx bx-edit'></i>
                                                    </a>
                                                </div>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class='bx bx-search-alt'></i>
                            <h3>No Results Found</h3>
                            <p>Try adjusting your search criteria or filters</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Initial State -->
                <div class="search-card">
                    <div class="empty-state">
                        <i class='bx bx-search-alt-2'></i>
                        <h3>Start Searching</h3>
                        <p>Enter keywords or use filters above to search for records</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<!-- Overlay for mobile menu -->
<div class="overlay" id="overlay" onclick="closeSidebar()"></div>

<script>
// ===== MOBILE SIDEBAR TOGGLE =====
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('overlay');
    sidebar.classList.toggle('mobile-show');
    overlay.classList.toggle('show');
    
    // Prevent body scroll when sidebar is open
    if (sidebar.classList.contains('mobile-show')) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
}

function closeSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('overlay');
    sidebar.classList.remove('mobile-show');
    overlay.classList.remove('show');
    document.body.style.overflow = '';
}

// Close sidebar when clicking menu item on mobile
if (window.innerWidth <= 1024) {
    document.querySelectorAll('.menu-item').forEach(item => {
        item.addEventListener('click', closeSidebar);
    });
}

// Close sidebar on window resize to desktop
window.addEventListener('resize', () => {
    if (window.innerWidth > 1024) {
        closeSidebar();
    }
});

// Prevent scroll issues on orientation change
window.addEventListener('orientationchange', () => {
    setTimeout(() => {
        window.scrollTo(0, 0);
    }, 100);
});
</script>
    <script>
        // Set search type
        function setSearchType(type) {
            document.getElementById('search_type').value = type;
            
            // Update active state
            document.querySelectorAll('.type-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.closest('.type-btn').classList.add('active');
        }

        // Clear all filters
        function clearFilters() {
            document.querySelector('input[name="q"]').value = '';
            document.querySelector('select[name="department"]').value = '';
            document.querySelector('select[name="status"]').value = '';
            
            const classSelect = document.querySelector('select[name="class"]');
            if (classSelect) classSelect.value = '';
            
            const designationSelect = document.querySelector('select[name="designation"]');
            if (designationSelect) designationSelect.value = '';
            
            // Optionally submit form or just clear
            // document.getElementById('searchForm').submit();
        }

        // Auto-submit on filter change (optional)
        document.querySelectorAll('.filter-select').forEach(select => {
            select.addEventListener('change', function() {
                // Uncomment to enable auto-submit on filter change
                // document.getElementById('searchForm').submit();
            });
        });
    </script>
</body>
</html>