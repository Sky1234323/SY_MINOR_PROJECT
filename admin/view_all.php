<?php
session_start();
require_once '../php/admin_session_check.php';
require_once '../php/db_connect.php';

// Get type from URL
$type = isset($_GET['type']) ? $_GET['type'] : 'students';
if (!in_array($type, ['students', 'teachers', 'faculty'])) {
    $type = 'students';
}

// Get admin data
$admin_query = "SELECT full_name FROM admin WHERE id = ?";
$admin_result = executeQuery($admin_query, "i", array($_SESSION['admin_id']));
$admin_data = $admin_result->fetch_assoc();

// Pagination
$records_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $records_per_page;

// Get records based on type
if ($type === 'students') {
    $count_query = "SELECT COUNT(*) as total FROM students";
    $query = "SELECT id, prn, first_name, middle_name, last_name, email, phone, department, class, is_active 
              FROM students ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $title = "All Students";
    $icon = "bxs-graduation";
} elseif ($type === 'teachers') {
    $count_query = "SELECT COUNT(*) as total FROM teachers";
    $query = "SELECT id, first_name, middle_name, last_name, email, phone, department, designation, is_active 
              FROM teachers ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $title = "All Teachers";
    $icon = "bxs-user-badge";
} else {
    $count_query = "SELECT COUNT(*) as total FROM faculty";
    $query = "SELECT id, full_name, email, phone, department, is_active 
              FROM faculty ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $title = "Faculty Members";
    $icon = "bxs-group";
}

// Get total count
$count_result = executeQuery($count_query);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Get records
$stmt = $GLOBALS['conn']->prepare($query);
$stmt->bind_param("ii", $records_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - MITAOE Admin</title>
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

        /* Sidebar - Same as add_record.php */
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

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .page-title i {
            font-size: 2.5rem;
            color: #4EA685;
        }

        .page-title h1 {
            font-size: 2rem;
            font-weight: 800;
            color: #2c3e50;
        }

        .page-actions {
            display: flex;
            gap: 1rem;
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

        /* Table Card */
        .table-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding: 0.75rem 1rem 0.75rem 3rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            width: 300px;
            font-size: 0.9375rem;
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #4EA685;
        }

        /* Table */
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

        .btn-delete {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .btn-delete:hover {
            background: rgba(239, 68, 68, 0.2);
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
    }

    .main-content {
        margin-left: 0;
    }

    .hamburger {
        display: block;
        font-size: 1.5rem;
        cursor: pointer;
        color: #2c3e50;
    }

    /* Page header adjustments */
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

    .page-actions {
        width: 100%;
    }

    .page-actions .btn {
        width: 100%;
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
    .page-header {
        margin-bottom: 1.5rem;
    }

    .page-title h1 {
        font-size: 1.5rem;
    }

    .page-title i {
        font-size: 2rem;
    }

    .btn {
        padding: 0.625rem 1.25rem;
        font-size: 0.875rem;
    }

    /* Table card */
    .table-card {
        padding: 1.5rem;
        border-radius: 15px;
    }

    /* Table header */
    .table-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    .table-header h3 {
        font-size: 1rem;
    }

    .table-header h3 span {
        font-size: 1.25rem;
    }

    /* Search box */
    .search-box {
        width: 100%;
    }

    .search-box input {
        width: 100%;
        padding: 0.625rem 0.875rem 0.625rem 2.75rem;
        font-size: 0.875rem;
        border-radius: 8px;
    }

    .search-box i {
        left: 0.875rem;
        font-size: 1rem;
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

@media (max-width: 480px) {
    table {
        min-width: 800px;
        font-size: 0.6875rem;
    }

    thead th, tbody td {
        padding: 0.5rem 0.375rem;
        font-size: 0.6875rem;
    }

    thead th:nth-child(1), tbody td:nth-child(1) { min-width: 90px; }
    thead th:nth-child(2), tbody td:nth-child(2) { min-width: 130px; }
    thead th:nth-child(3), tbody td:nth-child(3) { min-width: 160px; }
    thead th:nth-child(4), tbody td:nth-child(4) { min-width: 110px; }
    thead th:nth-child(5), tbody td:nth-child(5) { min-width: 130px; }
    thead th:nth-child(6), tbody td:nth-child(6) { min-width: 70px; }
    thead th:nth-child(7), tbody td:nth-child(7) { min-width: 70px; }
    thead th:nth-child(8), tbody td:nth-child(8) { min-width: 110px; }
}
    /* Action buttons */
    .action-btns {
        gap: 0.375rem;
        flex-wrap: nowrap;
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

    .empty-state .btn {
        margin-top: 1.25rem;
        width: 100%;
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
        border-radius: 6px;
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

    .page-title h1 {
        font-size: 1.375rem;
    }

    .page-title i {
        font-size: 1.75rem;
    }

    .btn {
        padding: 0.5rem 1rem;
        font-size: 0.8125rem;
        gap: 0.375rem;
    }

    .btn i {
        font-size: 1rem;
    }

    .table-card {
        padding: 1rem;
    }

    .table-header h3 {
        font-size: 0.9375rem;
    }

    .search-box input {
        padding: 0.5rem 0.75rem 0.5rem 2.5rem;
        font-size: 0.8125rem;
    }

    .search-box i {
        left: 0.75rem;
        font-size: 0.9375rem;
    }

    /* Make table even more compact */
    table {
        font-size: 0.6875rem;
    }

    thead th, tbody td {
        padding: 0.5rem 0.375rem;
        font-size: 0.6875rem;
    }

    /* Sticky first column on horizontal scroll */
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

    /* Stack action buttons if needed */
    .action-btns {
        gap: 0.25rem;
    }

    .action-btn {
        padding: 0.3125rem;
    }

    .action-btn i {
        font-size: 0.9375rem;
    }

    /* Pagination - compact */
    .pagination {
        gap: 0.25rem;
    }

    .pagination a, .pagination span {
        padding: 0.3125rem 0.625rem;
        font-size: 0.8125rem;
    }
}

/* Mobile Small - 360px and below */
@media (max-width: 360px) {
    .page-title h1 {
        font-size: 1.25rem;
    }

    .table-card {
        padding: 0.875rem;
    }

    .search-box input {
        padding: 0.4375rem 0.625rem 0.4375rem 2.25rem;
        font-size: 0.75rem;
    }

    table {
        font-size: 0.625rem;
    }

    thead th, tbody td {
        padding: 0.375rem 0.25rem;
        font-size: 0.625rem;
    }

    .btn {
        padding: 0.4375rem 0.875rem;
        font-size: 0.75rem;
    }
}

/* Landscape mobile */
@media (max-height: 500px) and (orientation: landscape) {
    .page-header {
        margin-bottom: 1rem;
    }

    .table-card {
        padding: 1.25rem;
    }

    /* Reduce vertical spacing in landscape */
    thead th, tbody td {
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
    }
}

/* Desktop */
@media (min-width: 1025px) {
    .overlay {
        display: none !important;
    }
}
    </style>
</head>
<body>
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
                <a href="view_all.php?type=<?php echo $type; ?>">View Records</a> <span>&gt;</span> 
                <span><?php echo $title; ?></span>
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
                <div class="page-title">
                    <i class='bx <?php echo $icon; ?>'></i>
                    <h1><?php echo $title; ?></h1>
                </div>
                <div class="page-actions">
                    <a href="add_record.php?type=<?php echo rtrim($type, 's'); ?>" class="btn btn-primary">
                        <i class='bx bx-plus'></i>
                        Add New <?php echo ucfirst(rtrim($type, 's')); ?>
                    </a>
                </div>
            </div>

            <!-- Table Card -->
            <div class="table-card">
                <div class="table-header">
                    <div>
                        <h3 style="margin: 0; font-size: 1.25rem; color: #2c3e50;">
                            Total Records: <span style="color: #4EA685; font-weight: 800;"><?php echo $total_records; ?></span>
                        </h3>
                    </div>
                    <div class="search-box">
                        <i class='bx bx-search'></i>
                        <input type="text" id="searchInput" placeholder="Search records..." onkeyup="searchTable()">
                    </div>
                </div>
                <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
    <table style="min-width: 900px;">
</div>
                <?php if ($result && $result->num_rows > 0): ?>
                    <table id="recordsTable">
                        <thead>
                            <tr>
                                <?php if ($type === 'students'): ?>
                                    <th>PRN</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Department</th>
                                    <th>Class</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                <?php elseif ($type === 'teachers'): ?>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Department</th>
                                    <th>Designation</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                <?php else: ?>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <?php if ($type === 'students'): ?>
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
                                                <a href="#" onclick="deleteRecord('student', <?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['prn']); ?>')" class="action-btn btn-delete" title="Delete">
                                                    <i class='bx bx-trash'></i>
                                                </a>
                                            </div>
                                        </td>
                                    <?php elseif ($type === 'teachers'): ?>
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
                                                <a href="#" onclick="deleteRecord('teacher', <?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['email']); ?>')" class="action-btn btn-delete" title="Delete">
                                                    <i class='bx bx-trash'></i>
                                                </a>
                                            </div>
                                        </td>
                                    <?php else: ?>
                                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($row['department']); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $row['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                                <?php echo $row['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-btns">
                                                <a href="view_profile.php?type=faculty&id=<?php echo $row['id']; ?>" class="action-btn btn-view" title="View Details">
                                                    <i class='bx bx-show'></i>
                                                </a>
                                                <a href="edit_record.php?type=faculty&id=<?php echo $row['id']; ?>" class="action-btn btn-edit" title="Edit">
                                                    <i class='bx bx-edit'></i>
                                                </a>
                                                <a href="#" onclick="deleteRecord('faculty', <?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['email']); ?>')" class="action-btn btn-delete" title="Delete">
                                                    <i class='bx bx-trash'></i>
                                                </a>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($current_page > 1): ?>
                                <a href="?type=<?php echo $type; ?>&page=<?php echo $current_page - 1; ?>">
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
                                    <a href="?type=<?php echo $type; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($current_page < $total_pages): ?>
                                <a href="?type=<?php echo $type; ?>&page=<?php echo $current_page + 1; ?>">
                                    Next <i class='bx bx-chevron-right'></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div style="text-align: center; padding: 4rem 2rem;">
                        <i class='bx bx-folder-open' style="font-size: 5rem; color: #e5e7eb;"></i>
                        <h3 style="color: #718096; margin-top: 1rem;">No Records Found</h3>
                        <p style="color: #a0aec0; margin-top: 0.5rem;">There are no <?php echo $type; ?> in the database yet.</p>
                        <a href="add_record.php?type=<?php echo rtrim($type, 's'); ?>" class="btn btn-primary" style="margin-top: 1.5rem; display: inline-flex;">
                            <i class='bx bx-plus'></i>
                            Add First <?php echo ucfirst(rtrim($type, 's')); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
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
        // Search function
        function searchTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('recordsTable');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const cells = row.getElementsByTagName('td');
                let found = false;

                for (let j = 0; j < cells.length; j++) {
                    const cell = cells[j];
                    if (cell) {
                        const textValue = cell.textContent || cell.innerText;
                        if (textValue.toLowerCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }

                row.style.display = found ? '' : 'none';
            }
        }

        // Delete record function
        function deleteRecord(type, id, identifier) {
            if (confirm(`Are you sure you want to delete this ${type}?\n\n${identifier}\n\nThis action cannot be undone!`)) {
                // Send delete request
                fetch('../php/delete_record_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `type=${type}&id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
            }
        }
    </script>
</body>
</html>