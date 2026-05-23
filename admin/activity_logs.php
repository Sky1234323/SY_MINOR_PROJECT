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

// Filters
$filter_action = isset($_GET['action']) ? $_GET['action'] : '';
$filter_admin = isset($_GET['admin_id']) ? (int)$_GET['admin_id'] : 0;
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';

// Pagination
$records_per_page = 20;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $records_per_page;

// Build query
$where_clauses = array();
$params = array();
$types = "";

if (!empty($filter_action)) {
    $where_clauses[] = "al.action = ?";
    $params[] = $filter_action;
    $types .= "s";
}

if ($filter_admin > 0) {
    $where_clauses[] = "al.admin_id = ?";
    $params[] = $filter_admin;
    $types .= "i";
}

if (!empty($filter_date)) {
    $where_clauses[] = "DATE(al.created_at) = ?";
    $params[] = $filter_date;
    $types .= "s";
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Get total count
$count_query = "SELECT COUNT(*) as total FROM activity_logs al $where_sql";
if (!empty($params)) {
    $count_result = executeQuery($count_query, $types, $params);
} else {
    $count_result = executeQuery($count_query);
}
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get logs
$query = "SELECT al.*, a.username, a.full_name 
          FROM activity_logs al 
          LEFT JOIN admin a ON al.admin_id = a.id 
          $where_sql 
          ORDER BY al.created_at DESC 
          LIMIT ? OFFSET ?";

$params[] = $records_per_page;
$params[] = $offset;
$types .= "ii";

$stmt = $GLOBALS['conn']->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$logs_result = $stmt->get_result();

// Get all admins for filter
$admins_query = "SELECT id, username, full_name FROM admin ORDER BY full_name";
$admins_result = executeQuery($admins_query);

// Get unique actions for filter
$actions_query = "SELECT DISTINCT action FROM activity_logs ORDER BY action";
$actions_result = executeQuery($actions_query);

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total_logs,
    COUNT(DISTINCT admin_id) as unique_admins,
    COUNT(DISTINCT DATE(created_at)) as active_days
FROM activity_logs";
$stats_result = executeQuery($stats_query);
$stats = $stats_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs - MITAOE Admin</title>
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

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
            animation: fadeInUp 0.6s ease;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            gap: 1.5rem;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: white;
            flex-shrink: 0;
        }

        .stat-details h3 {
            font-size: 2rem;
            font-weight: 800;
            color: #2c3e50;
            margin-bottom: 0.25rem;
        }

        .stat-details p {
            font-size: 0.875rem;
            color: #718096;
            font-weight: 600;
        }

        /* Filter Card */
        .filter-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }

        .filter-card h3 {
            font-size: 1.25rem;
            color: #2c3e50;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .filter-card h3 i {
            color: #4EA685;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .filter-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .filter-select {
            width: 100%;
            padding: 0.75rem 1rem;
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

        .filter-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9375rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4EA685, #57B894);
            color: white;
            box-shadow: 0 4px 12px rgba(78,166,133,0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(78,166,133,0.4);
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #2c3e50;
        }

        .btn-secondary:hover {
            background: #d1d5db;
        }

        /* Logs Table */
        .logs-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .logs-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .logs-count {
            font-size: 1.125rem;
            font-weight: 700;
            color: #2c3e50;
        }

        .logs-count span {
            color: #4EA685;
            font-size: 1.5rem;
        }

        /* Timeline */
        .timeline {
            position: relative;
            padding-left: 2rem;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 0.5rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(180deg, #4EA685, rgba(78,166,133,0.2));
        }

        .timeline-item {
            position: relative;
            padding-bottom: 2rem;
            animation: fadeInLeft 0.5s ease;
        }

        @keyframes fadeInLeft {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -1.5rem;
            top: 0.5rem;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #4EA685;
            border: 3px solid white;
            box-shadow: 0 0 0 4px rgba(78,166,133,0.2);
        }

        .log-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            border-left: 4px solid #4EA685;
            transition: all 0.3s ease;
        }

        .log-card:hover {
            background: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transform: translateX(5px);
        }

        .log-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 0.75rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .log-action {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .action-add {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
        }

        .action-update {
            background: rgba(251, 191, 36, 0.1);
            color: #fbbf24;
        }

        .action-delete {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .action-login {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .action-other {
            background: rgba(107, 114, 128, 0.1);
            color: #6b7280;
        }

        .log-time {
            font-size: 0.875rem;
            color: #718096;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .log-admin {
            font-size: 0.9375rem;
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .log-admin i {
            color: #4EA685;
        }

        .log-details {
            font-size: 0.875rem;
            color: #718096;
            line-height: 1.6;
        }

        .log-meta {
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid #e5e7eb;
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            font-size: 0.75rem;
            color: #a0aec0;
        }

        .log-meta span {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .pagination a, .pagination span {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .pagination a {
            background: white;
            color: #4EA685;
            border: 2px solid #e5e7eb;
        }

        .pagination a:hover {
            border-color: #4EA685;
            background: rgba(78,166,133,0.1);
        }

        .pagination span.current {
            background: linear-gradient(135deg, #4EA685, #57B894);
            color: white;
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

        /* ===== COMPREHENSIVE RESPONSIVE DESIGN ===== */

/* Tablet - 1024px and below */
@media (max-width: 1024px) {
    .sidebar {
        transform: translateX(-100%);
    }

    .sidebar.mobile-show {
        transform: translateX(0);
    }

    .main-content {
        margin-left: 0;
    }

    .hamburger {
        display: block;
    }

    /* Stats grid - 2 columns */
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1.25rem;
    }

    /* Filters - 2 columns */
    .filters-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1.25rem;
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

    /* Stats grid - single column */
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .stat-card {
        padding: 1.25rem;
    }

    .stat-icon {
        width: 55px;
        height: 55px;
        font-size: 1.5rem;
    }

    .stat-details h3 {
        font-size: 1.75rem;
    }

    .stat-details p {
        font-size: 0.8125rem;
    }

    /* Filter card */
    .filter-card {
        padding: 1.5rem;
        border-radius: 12px;
    }

    .filter-card h3 {
        font-size: 1.125rem;
        margin-bottom: 1.25rem;
    }

    /* Filters grid - single column */
    .filters-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    .filter-group label {
        font-size: 0.8125rem;
        margin-bottom: 0.5rem;
    }

    .filter-select {
        padding: 0.625rem 0.875rem;
        font-size: 0.875rem;
        border-radius: 8px;
    }

    /* Filter actions - vertical */
    .filter-actions {
        flex-direction: column;
        gap: 0.75rem;
    }

    .btn {
        width: 100%;
        justify-content: center;
        padding: 0.75rem 1.25rem;
        font-size: 0.875rem;
    }

    /* Logs card */
    .logs-card {
        padding: 1.5rem;
        border-radius: 12px;
    }

    .logs-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    .logs-count {
        font-size: 1rem;
    }

    .logs-count span {
        font-size: 1.25rem;
    }

    /* Timeline */
    .timeline {
        padding-left: 1.5rem;
    }

    .timeline::before {
        left: 0.375rem;
    }

    .timeline-item {
        padding-bottom: 1.5rem;
    }

    .timeline-item::before {
        left: -1.25rem;
        width: 10px;
        height: 10px;
        border-width: 2px;
    }

    /* Log cards */
    .log-card {
        padding: 1.25rem;
        border-radius: 10px;
        border-left-width: 3px;
    }

    .log-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.625rem;
        margin-bottom: 0.625rem;
    }

    .log-action {
        padding: 0.375rem 0.875rem;
        font-size: 0.8125rem;
    }

    .log-action i {
        font-size: 1rem;
    }

    .log-time {
        font-size: 0.8125rem;
    }

    .log-admin {
        font-size: 0.875rem;
        margin-bottom: 0.625rem;
    }

    .log-details {
        font-size: 0.8125rem;
        line-height: 1.5;
    }

    .log-meta {
        margin-top: 0.625rem;
        padding-top: 0.625rem;
        gap: 1rem;
        font-size: 0.6875rem;
        flex-direction: column;
        align-items: flex-start;
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

    /* Pagination */
    .pagination {
        flex-wrap: wrap;
        gap: 0.375rem;
        margin-top: 1.5rem;
    }

    .pagination a, .pagination span {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
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

    .stat-card {
        padding: 1rem;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        font-size: 1.375rem;
    }

    .stat-details h3 {
        font-size: 1.5rem;
    }

    .stat-details p {
        font-size: 0.75rem;
    }

    .filter-card {
        padding: 1.25rem;
    }

    .filter-card h3 {
        font-size: 1rem;
    }

    .btn {
        padding: 0.625rem 1rem;
        font-size: 0.8125rem;
    }

    .logs-card {
        padding: 1.25rem;
    }

    .timeline {
        padding-left: 1.25rem;
    }

    .timeline-item::before {
        left: -1.125rem;
        width: 8px;
        height: 8px;
    }

    .log-card {
        padding: 1rem;
    }

    .log-action {
        padding: 0.3125rem 0.75rem;
        font-size: 0.75rem;
    }

    .log-time {
        font-size: 0.75rem;
    }

    .log-admin {
        font-size: 0.8125rem;
    }

    .log-details {
        font-size: 0.75rem;
    }

    .log-meta {
        font-size: 0.625rem;
    }
}

/* Mobile Small - 360px and below */
@media (max-width: 360px) {
    .page-header h1 {
        font-size: 1.375rem;
    }

    .stat-card {
        padding: 0.875rem;
    }

    .stat-details h3 {
        font-size: 1.375rem;
    }

    .filter-card {
        padding: 1rem;
    }

    .logs-card {
        padding: 1rem;
    }

    .log-card {
        padding: 0.875rem;
    }

    .btn {
        padding: 0.5rem 0.875rem;
        font-size: 0.75rem;
    }
}

/* Landscape mobile */
@media (max-height: 500px) and (orientation: landscape) {
    .page-header {
        margin-bottom: 1rem;
    }

    .stats-grid {
        grid-template-columns: repeat(3, 1fr);
    }

    .filter-card {
        padding: 1.25rem;
    }

    .timeline-item {
        padding-bottom: 1.25rem;
    }
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
    <aside class="sidebar">
        <div class="sidebar-logo">
            <i class='bx bxs-graduation'></i>
            <h1>MITAOE</h1>
            <p>Admin Panel</p>
        </div>

        <nav class="sidebar-menu">
            <a href="admin_dashboard.php" class="menu-item">
                <i class='bx bxs-dashboard'></i>
                <span>Dashboard</span>
            </a>
            <a href="add_record.php?type=student" class="menu-item">
                <i class='bx bx-user-plus'></i>
                <span>Add Student</span>
            </a>
            <a href="add_record.php?type=teacher" class="menu-item">
                <i class='bx bx-user-plus'></i>
                <span>Add Teacher</span>
            </a>
            <a href="view_all.php?type=students" class="menu-item">
                <i class='bx bx-group'></i>
                <span>View All Students</span>
            </a>
            <a href="view_all.php?type=teachers" class="menu-item">
                <i class='bx bx-group'></i>
                <span>View All Teachers</span>
            </a>
            <a href="search.php" class="menu-item">
                <i class='bx bx-search'></i>
                <span>Search Records</span>
            </a>
            <a href="delete_record.php" class="menu-item">
                <i class='bx bx-trash'></i>
                <span>Delete Records</span>
            </a>
            <a href="upload_excel.php" class="menu-item">
                <i class='bx bx-upload'></i>
                <span>Upload Excel/CSV</span>
            </a>
            <a href="activity_logs.php" class="menu-item active">
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
                <span>Activity Logs</span>
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
                <h1>📊 Activity Logs</h1>
                <p>Track all admin actions and system activities</p>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #4EA685, #57B894);">
                        <i class='bx bx-history'></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo number_format($stats['total_logs']); ?></h3>
                        <p>Total Activities</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                        <i class='bx bx-user'></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['unique_admins']; ?></h3>
                        <p>Active Admins</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                        <i class='bx bx-calendar'></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['active_days']; ?></h3>
                        <p>Active Days</p>
                    </div>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="filter-card">
                <h3>
                    <i class='bx bx-filter'></i>
                    Filter Logs
                </h3>
                <form method="GET" action="activity_logs.php" id="filterForm">
                    <div class="filters-grid">
                        <!-- Action Filter -->
                        <div class="filter-group">
                            <label>Action Type</label>
                            <select name="action" class="filter-select">
                                <option value="">All Actions</option>
                                <?php if ($actions_result && $actions_result->num_rows > 0): ?>
                                    <?php while ($action_row = $actions_result->fetch_assoc()): ?>
                                        <option value="<?php echo htmlspecialchars($action_row['action']); ?>" 
                                                <?php echo $filter_action === $action_row['action'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($action_row['action']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Admin Filter -->
                        <div class="filter-group">
                            <label>Admin User</label>
                            <select name="admin_id" class="filter-select">
                                <option value="0">All Admins</option>
                                <?php if ($admins_result && $admins_result->num_rows > 0): ?>
                                    <?php while ($admin_row = $admins_result->fetch_assoc()): ?>
                                        <option value="<?php echo $admin_row['id']; ?>" 
                                                <?php echo $filter_admin === $admin_row['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($admin_row['full_name']); ?> (@<?php echo htmlspecialchars($admin_row['username']); ?>)
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Date Filter -->
                        <div class="filter-group">
                            <label>Date</label>
                            <input type="date" name="date" class="filter-select" value="<?php echo htmlspecialchars($filter_date); ?>">
                        </div>
                    </div>

                    <div class="filter-actions">
                        <a href="activity_logs.php" class="btn btn-secondary">
                            <i class='bx bx-reset'></i>
                            Clear Filters
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class='bx bx-search'></i>
                            Apply Filters
                        </button>
                    </div>
                </form>
            </div>

            <!-- Logs Card -->
            <div class="logs-card">
                <div class="logs-header">
                    <h3 class="logs-count">
                        Showing <span><?php echo $logs_result->num_rows; ?></span> of <?php echo number_format($total_records); ?> logs
                    </h3>
                </div>

                <?php if ($logs_result && $logs_result->num_rows > 0): ?>
                    <div class="timeline">
                        <?php while ($log = $logs_result->fetch_assoc()): ?>
                            <div class="timeline-item">
                                <div class="log-card">
                                    <div class="log-header">
                                        <div class="log-action <?php echo getActionClass($log['action']); ?>">
                                            <i class='bx <?php echo getActionIcon($log['action']); ?>'></i>
                                            <?php echo htmlspecialchars($log['action']); ?>
                                        </div>
                                        <div class="log-time">
                                            <i class='bx bx-time'></i>
                                            <?php echo date('M d, Y h:i A', strtotime($log['created_at'])); ?>
                                        </div>
                                    </div>

                                    <div class="log-admin">
                                        <i class='bx bx-user-circle'></i>
                                        <?php echo htmlspecialchars($log['full_name'] ?? 'Unknown Admin'); ?>
                                        <span style="color: #a0aec0; font-weight: 400;">(@<?php echo htmlspecialchars($log['username'] ?? 'unknown'); ?>)</span>
                                    </div>

                                    <div class="log-details">
                                        <?php echo htmlspecialchars($log['details'] ?? 'No details available'); ?>
                                    </div>

                                    <div class="log-meta">
                                        <span>
                                            <i class='bx bx-network-chart'></i>
                                            IP: <?php echo htmlspecialchars($log['ip_address'] ?? 'Unknown'); ?>
                                        </span>
                                        <span>
                                            <i class='bx bx-mobile'></i>
                                            <?php echo getBrowserInfo($log['user_agent'] ?? ''); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($current_page > 1): ?>
                                <a href="?page=<?php echo $current_page - 1; ?><?php echo !empty($filter_action) ? '&action=' . urlencode($filter_action) : ''; ?><?php echo $filter_admin > 0 ? '&admin_id=' . $filter_admin : ''; ?><?php echo !empty($filter_date) ? '&date=' . $filter_date : ''; ?>">
                                    <i class='bx bx-chevron-left'></i> Previous
                                </a>
                            <?php endif; ?>

                            <?php
                            $start_page = max(1, $current_page - 2);
                            $end_page = min($total_pages, $current_page + 2);
                            
                            for ($i = $start_page; $i <= $end_page; $i++):
                            ?>
                                <?php if ($i == $current_page): ?>
                                    <span class="current"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?page=<?php echo $i; ?><?php echo !empty($filter_action) ? '&action=' . urlencode($filter_action) : ''; ?><?php echo $filter_admin > 0 ? '&admin_id=' . $filter_admin : ''; ?><?php echo !empty($filter_date) ? '&date=' . $filter_date : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($current_page < $total_pages): ?>
                                <a href="?page=<?php echo $current_page + 1; ?><?php echo !empty($filter_action) ? '&action=' . urlencode($filter_action) : ''; ?><?php echo $filter_admin > 0 ? '&admin_id=' . $filter_admin : ''; ?><?php echo !empty($filter_date) ? '&date=' . $filter_date : ''; ?>">
                                    Next <i class='bx bx-chevron-right'></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="empty-state">
                        <i class='bx bx-time'></i>
                        <h3>No Activity Logs Found</h3>
                        <p>No activities match your filter criteria</p>
                        <?php if (!empty($filter_action) || $filter_admin > 0 || !empty($filter_date)): ?>
                            <a href="activity_logs.php" class="btn btn-primary" style="margin-top: 1.5rem; display: inline-flex;">
                                <i class='bx bx-reset'></i>
                                Clear Filters
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
/**
 * Get action CSS class based on action type
 */
function getActionClass($action) {
    if (strpos($action, 'ADD') !== false || strpos($action, 'CREATE') !== false) {
        return 'action-add';
    } elseif (strpos($action, 'UPDATE') !== false || strpos($action, 'EDIT') !== false) {
        return 'action-update';
    } elseif (strpos($action, 'DELETE') !== false || strpos($action, 'REMOVE') !== false) {
        return 'action-delete';
    } elseif (strpos($action, 'LOGIN') !== false) {
        return 'action-login';
    } else {
        return 'action-other';
    }
}

/**
 * Get action icon based on action type
 */
function getActionIcon($action) {
    if (strpos($action, 'ADD') !== false || strpos($action, 'CREATE') !== false) {
        return 'bx-plus-circle';
    } elseif (strpos($action, 'UPDATE') !== false || strpos($action, 'EDIT') !== false) {
        return 'bx-edit';
    } elseif (strpos($action, 'DELETE') !== false || strpos($action, 'REMOVE') !== false) {
        return 'bx-trash';
    } elseif (strpos($action, 'LOGIN') !== false) {
        return 'bx-log-in';
    } elseif (strpos($action, 'LOGOUT') !== false) {
        return 'bx-log-out';
    } elseif (strpos($action, 'UPLOAD') !== false) {
        return 'bx-upload';
    } elseif (strpos($action, 'DOWNLOAD') !== false) {
        return 'bx-download';
    } elseif (strpos($action, 'SEARCH') !== false) {
        return 'bx-search';
    } else {
        return 'bx-file';
    }
}

/**
 * Get simplified browser info from user agent
 */
function getBrowserInfo($userAgent) {
    if (empty($userAgent)) {
        return 'Unknown Browser';
    }

    // Detect browser
    if (strpos($userAgent, 'Chrome') !== false && strpos($userAgent, 'Edg') === false) {
        $browser = 'Chrome';
    } elseif (strpos($userAgent, 'Firefox') !== false) {
        $browser = 'Firefox';
    } elseif (strpos($userAgent, 'Safari') !== false && strpos($userAgent, 'Chrome') === false) {
        $browser = 'Safari';
    } elseif (strpos($userAgent, 'Edg') !== false) {
        $browser = 'Edge';
    } elseif (strpos($userAgent, 'Opera') !== false || strpos($userAgent, 'OPR') !== false) {
        $browser = 'Opera';
    } else {
        $browser = 'Unknown';
    }

    // Detect OS
    if (strpos($userAgent, 'Windows') !== false) {
        $os = 'Windows';
    } elseif (strpos($userAgent, 'Mac') !== false) {
        $os = 'macOS';
    } elseif (strpos($userAgent, 'Linux') !== false) {
        $os = 'Linux';
    } elseif (strpos($userAgent, 'Android') !== false) {
        $os = 'Android';
    } elseif (strpos($userAgent, 'iOS') !== false || strpos($userAgent, 'iPhone') !== false || strpos($userAgent, 'iPad') !== false) {
        $os = 'iOS';
    } else {
        $os = 'Unknown';
    }

    return $browser . ' on ' . $os;
}
?>

    <script>
        // Auto-submit form on filter change (optional)
        const filterSelects = document.querySelectorAll('.filter-select');
        
        filterSelects.forEach(select => {
            select.addEventListener('change', function() {
                // Uncomment to enable auto-submit
                // document.getElementById('filterForm').submit();
            });
        });

        // Smooth scroll for timeline items
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateX(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.timeline-item').forEach(item => {
            item.style.opacity = '0';
            item.style.transform = 'translateX(-20px)';
            item.style.transition = 'all 0.5s ease';
            observer.observe(item);
        });

        // Search within logs (client-side filter)
        function searchLogs() {
            const searchTerm = document.getElementById('searchInput')?.value.toLowerCase();
            if (!searchTerm) return;

            const logCards = document.querySelectorAll('.log-card');
            
            logCards.forEach(card => {
                const text = card.textContent.toLowerCase();
                const timelineItem = card.closest('.timeline-item');
                
                if (text.includes(searchTerm)) {
                    timelineItem.style.display = 'block';
                } else {
                    timelineItem.style.display = 'none';
                }
            });
        }

        // Export logs to CSV (bonus feature)
        function exportLogs() {
            const logs = [];
            
            document.querySelectorAll('.log-card').forEach(card => {
                const action = card.querySelector('.log-action')?.textContent.trim();
                const time = card.querySelector('.log-time')?.textContent.trim();
                const admin = card.querySelector('.log-admin')?.textContent.trim();
                const details = card.querySelector('.log-details')?.textContent.trim();
                
                logs.push({
                    action,
                    time,
                    admin,
                    details
                });
            });

            // Convert to CSV
            const csv = [
                ['Action', 'Time', 'Admin', 'Details'],
                ...logs.map(log => [log.action, log.time, log.admin, log.details])
            ].map(row => row.map(cell => `"${cell}"`).join(',')).join('\n');

            // Download
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `activity_logs_${new Date().toISOString().split('T')[0]}.csv`;
            a.click();
            window.URL.revokeObjectURL(url);
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + F for focus on filter
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                document.querySelector('.filter-select')?.focus();
            }

            // Ctrl/Cmd + R for refresh/clear filters
            if ((e.ctrlKey || e.metaKey) && e.key === 'r' && e.shiftKey) {
                e.preventDefault();
                window.location.href = 'activity_logs.php';
            }
        });

        // Highlight current admin's activities
        const currentAdminLogs = document.querySelectorAll('.log-admin');
        currentAdminLogs.forEach(log => {
            if (log.textContent.includes('<?php echo $admin_data['full_name']; ?>')) {
                log.closest('.log-card').style.borderLeftColor = '#f59e0b';
                log.closest('.log-card').style.borderLeftWidth = '5px';
            }
        });

        // Auto-refresh option (optional)
        let autoRefresh = false;
        let refreshInterval;

        function toggleAutoRefresh() {
            autoRefresh = !autoRefresh;
            
            if (autoRefresh) {
                refreshInterval = setInterval(() => {
                    location.reload();
                }, 30000); // Refresh every 30 seconds
                console.log('Auto-refresh enabled (30s)');
            } else {
                clearInterval(refreshInterval);
                console.log('Auto-refresh disabled');
            }
        }

        // Uncomment to add auto-refresh toggle button
        // Add this to your HTML: <button onclick="toggleAutoRefresh()">Toggle Auto-Refresh</button>

        // Show tooltip for long user agents
        document.querySelectorAll('.log-meta span:last-child').forEach(span => {
            span.title = 'Click to see full user agent';
            span.style.cursor = 'pointer';
            
            span.addEventListener('click', function() {
                const userAgent = this.getAttribute('data-full-ua');
                if (userAgent) {
                    alert('Full User Agent:\n\n' + userAgent);
                }
            });
        });

        // Add smooth scroll to top button
        const scrollBtn = document.createElement('button');
        scrollBtn.innerHTML = '<i class="bx bx-up-arrow-alt"></i>';
        scrollBtn.style.cssText = `
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4EA685, #57B894);
            color: white;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(78,166,133,0.3);
            display: none;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            z-index: 1000;
        `;

        scrollBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        document.body.appendChild(scrollBtn);

        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                scrollBtn.style.display = 'flex';
            } else {
                scrollBtn.style.display = 'none';
            }
        });

        scrollBtn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 6px 20px rgba(78,166,133,0.4)';
        });

        scrollBtn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 4px 15px rgba(78,166,133,0.3)';
        });
    </script>
</body>
</html>