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

// Get type from URL
$type = isset($_GET['type']) ? $_GET['type'] : 'students';
if (!in_array($type, ['students', 'teachers'])) {
    $type = 'students';
}

// Pagination
$records_per_page = 20;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $records_per_page;

// Get records based on type
if ($type === 'students') {
    $count_query = "SELECT COUNT(*) as total FROM students";
    $query = "SELECT id, prn, first_name, middle_name, last_name, email, department, class, is_active 
              FROM students ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $title = "Delete Students";
    $icon = "bxs-graduation";
} else {
    $count_query = "SELECT COUNT(*) as total FROM teachers";
    $query = "SELECT id, first_name, middle_name, last_name, email, department, designation, is_active 
              FROM teachers ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $title = "Delete Teachers";
    $icon = "bxs-user-badge";
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

/* Background Particles - GREEN */
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
    background: rgba(78, 166, 133, 0.1); /* GREEN */
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
    background: rgba(78,166,133,0.2); /* GREEN */
    color: white;
    border-left-color: #4EA685; /* GREEN */
}

.menu-item i {
    font-size: 1.25rem;
    color: #4EA685; /* GREEN */
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
    color: #4EA685; /* GREEN */
    text-decoration: none;
}

.breadcrumb a:hover {
    text-decoration: underline;
}

.breadcrumb span {
    color: #4EA685; /* GREEN */
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
    background: rgba(78,166,133,0.1); /* GREEN */
    color: #4EA685; /* GREEN */
    border-radius: 8px;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.back-btn:hover {
    background: rgba(78,166,133,0.2); /* GREEN */
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
    background: linear-gradient(135deg, #4EA685, #57B894); /* GREEN */
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

/* Warning Banner */
.warning-banner {
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    border-left: 5px solid #f59e0b;
    padding: 1.5rem;
    border-radius: 15px;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 4px 15px rgba(245,158,11,0.2);
    animation: fadeInUp 0.6s ease;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

.warning-banner i {
    font-size: 2rem;
    color: #f59e0b;
}

.warning-banner div h3 {
    color: #92400e;
    font-size: 1.125rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.warning-banner div p {
    color: #78350f;
    font-size: 0.9375rem;
}

/* Type Selector - RED for delete theme */
.type-selector {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-bottom: 2rem;
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
}

.type-btn.active {
    background: linear-gradient(135deg, #ef4444, #dc2626); /* RED for delete */
    color: white;
    border-color: #ef4444;
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(239,68,68,0.3);
}

.type-btn:hover:not(.active) {
    border-color: #ef4444;
    transform: translateY(-2px);
}

/* Selection Controls */
.selection-controls {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.selection-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.selected-count {
    font-size: 1.125rem;
    font-weight: 700;
    color: #2c3e50;
}

.selected-count span {
    color: #ef4444;
    font-size: 1.5rem;
}

.selection-actions {
    display: flex;
    gap: 0.75rem;
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

.btn-select-all {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
    border: 2px solid transparent;
}

.btn-select-all:hover {
    background: rgba(59, 130, 246, 0.2);
    border-color: #3b82f6;
}

.btn-deselect-all {
    background: rgba(107, 114, 128, 0.1);
    color: #6b7280;
    border: 2px solid transparent;
}

.btn-deselect-all:hover {
    background: rgba(107, 114, 128, 0.2);
    border-color: #6b7280;
}

.btn-delete-selected {
    background: linear-gradient(135deg, #ef4444, #dc2626); /* RED for delete */
    color: white;
    box-shadow: 0 4px 12px rgba(239,68,68,0.3);
}

.btn-delete-selected:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(239,68,68,0.4);
}

.btn-delete-selected:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Table Card */
.table-card {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

/* Table */
table {
    width: 100%;
    border-collapse: collapse;
}

thead {
    background: linear-gradient(135deg, #ef4444, #dc2626); /* RED for delete theme */
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

thead th:first-child {
    width: 50px;
    text-align: center;
}

tbody tr {
    border-bottom: 1px solid #e5e7eb;
    transition: all 0.3s ease;
}

tbody tr:hover {
    background: rgba(239,68,68,0.05);
}

tbody tr.selected {
    background: rgba(239,68,68,0.1);
}

tbody td {
    padding: 1rem;
    font-size: 0.9375rem;
    color: #2c3e50;
}

tbody td:first-child {
    text-align: center;
}

/* Checkbox */
.record-checkbox {
    width: 20px;
    height: 20px;
    cursor: pointer;
    accent-color: #ef4444;
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
    color: #ef4444; /* RED for delete theme */
    border: 2px solid #e5e7eb;
}

.pagination a:hover {
    border-color: #ef4444;
    background: rgba(239,68,68,0.1);
}

.pagination span.current {
    background: linear-gradient(135deg, #ef4444, #dc2626); /* RED */
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

    /* Selection controls */
    .selection-controls {
        flex-direction: column;
        gap: 1rem;
    }

    .selection-info {
        width: 100%;
    }

    .selection-actions {
        width: 100%;
        flex-direction: column;
    }

    .btn {
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
    .page-header h1 {
        font-size: 1.75rem;
    }

    .page-header p {
        font-size: 0.9375rem;
    }

    /* Warning banner */
    .warning-banner {
        flex-direction: column;
        padding: 1.25rem;
        gap: 0.75rem;
        text-align: center;
    }

    .warning-banner i {
        font-size: 1.75rem;
    }

    .warning-banner div h3 {
        font-size: 1rem;
    }

    .warning-banner div p {
        font-size: 0.875rem;
    }

    /* Type selector */
    .type-selector {
        flex-direction: column;
        gap: 0.75rem;
    }

    .type-btn {
        width: 100%;
        justify-content: center;
        padding: 0.875rem 1.5rem;
    }

    /* Selection controls */
    .selection-controls {
        padding: 1.25rem;
        border-radius: 12px;
    }

    .selected-count {
        font-size: 1rem;
    }

    .selected-count span {
        font-size: 1.25rem;
    }

    .selection-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .btn {
        padding: 0.75rem 1.25rem;
        font-size: 0.875rem;
    }

    /* Table card */
    .table-card {
        padding: 1.5rem;
        border-radius: 15px;
    }

    /* Table - horizontal scroll */
    table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
        font-size: 0.75rem;
        -webkit-overflow-scrolling: touch;
    }

    thead, tbody, tr {
        display: table;
        width: 100%;
        table-layout: fixed;
    }

    thead th {
        padding: 0.75rem 0.5rem;
        font-size: 0.75rem;
    }

    tbody td {
        padding: 0.75rem 0.5rem;
        font-size: 0.75rem;
    }

    /* Checkbox */
    .record-checkbox {
        width: 18px;
        height: 18px;
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

    /* Pagination */
    .pagination {
        flex-wrap: wrap;
        gap: 0.375rem;
    }

    .pagination a, .pagination span {
        padding: 0.375rem 0.75rem;
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

    .warning-banner {
        padding: 1rem;
    }

    .warning-banner i {
        font-size: 1.5rem;
    }

    .warning-banner div h3 {
        font-size: 0.9375rem;
    }

    .warning-banner div p {
        font-size: 0.8125rem;
    }

    .type-btn {
        padding: 0.75rem 1.25rem;
        font-size: 0.875rem;
    }

    .selection-controls {
        padding: 1rem;
    }

    .selected-count {
        font-size: 0.9375rem;
    }

    .btn {
        padding: 0.625rem 1rem;
        font-size: 0.8125rem;
        gap: 0.5rem;
    }

    .table-card {
        padding: 1rem;
    }

    /* Make table more compact */
    table {
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

    /* Sticky first column on scroll */
    thead th:first-child,
    tbody td:first-child {
        position: sticky;
        left: 0;
        background: white;
        z-index: 1;
        box-shadow: 2px 0 4px rgba(0,0,0,0.05);
    }

    thead th:first-child {
        background: linear-gradient(135deg, #ef4444, #dc2626);
    }
}

/* Mobile Small - 360px and below */
@media (max-width: 360px) {
    .page-header h1 {
        font-size: 1.375rem;
    }

    .warning-banner {
        padding: 0.875rem;
    }

    .selection-controls {
        padding: 0.875rem;
    }

    .btn {
        padding: 0.5rem 0.875rem;
        font-size: 0.75rem;
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

    .warning-banner {
        padding: 1rem;
        flex-direction: row;
        text-align: left;
    }

    .selection-controls {
        padding: 1rem;
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
            <a href="delete_record.php" class="menu-item active">
                <i class='bx bx-trash'></i>
                <span>Delete Records</span>
            </a>
            <a href="upload_excel.php" class="menu-item">
                <i class='bx bx-upload'></i>
                <span>Upload Excel/CSV</span>
            </a>
            <a href="activity_logs.php" class="menu-item">
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
                <span>Delete Records</span>
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
                <h1>🗑️ Delete Records</h1>
                <p>Select and delete multiple records at once</p>
            </div>

            <!-- Warning Banner -->
            <div class="warning-banner">
                <i class='bx bx-error'></i>
                <div>
                    <h3>⚠️ Warning: Permanent Action</h3>
                    <p>Deleted records cannot be recovered. Please review your selection carefully before confirming deletion.</p>
                </div>
            </div>

            <!-- Type Selector -->
            <div class="type-selector">
                <a href="delete_record.php?type=students" class="type-btn <?php echo $type === 'students' ? 'active' : ''; ?>">
                    <i class='bx bxs-graduation'></i> Delete Students
                </a>
                <a href="delete_record.php?type=teachers" class="type-btn <?php echo $type === 'teachers' ? 'active' : ''; ?>">
                    <i class='bx bxs-user-badge'></i> Delete Teachers
                </a>
            </div>

            <!-- Selection Controls -->
            <div class="selection-controls">
                <div class="selection-info">
                    <p class="selected-count">
                        Selected: <span id="selectedCount">0</span> / <?php echo $total_records; ?>
                    </p>
                </div>
                <div class="selection-actions">
                    <button class="btn btn-select-all" onclick="selectAll()">
                        <i class='bx bx-checkbox-checked'></i>
                        Select All
                    </button>
                    <button class="btn btn-deselect-all" onclick="deselectAll()">
                        <i class='bx bx-checkbox'></i>
                        Deselect All
                    </button>
                    <button class="btn btn-delete-selected" id="deleteBtn" onclick="deleteSelected()" disabled>
                        <i class='bx bx-trash'></i>
                        Delete Selected (<span id="deleteBtnCount">0</span>)
                    </button>
                </div>
            </div>

            <!-- Table Card -->
            <div class="table-card">
                <?php if ($result && $result->num_rows > 0): ?>
                    <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
    <table style="min-width: 850px;">
        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAllCheckbox" class="record-checkbox" onclick="toggleAll(this)">
                                </th>
                                <?php if ($type === 'students'): ?>
                                    <th>PRN</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Department</th>
                                    <th>Class</th>
                                    <th>Status</th>
                                <?php else: ?>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Department</th>
                                    <th>Designation</th>
                                    <th>Status</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr id="row-<?php echo $row['id']; ?>">
                                    <td>
                                        <input 
                                            type="checkbox" 
                                            class="record-checkbox row-checkbox" 
                                            value="<?php echo $row['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($type === 'students' ? $row['first_name'] . ' ' . $row['last_name'] : $row['first_name'] . ' ' . $row['last_name']); ?>"
                                            onchange="updateSelection()">
                                    </td>
                                    <?php if ($type === 'students'): ?>
                                        <td><strong><?php echo htmlspecialchars($row['prn']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td><?php echo htmlspecialchars($row['department']); ?></td>
                                        <td><?php echo htmlspecialchars($row['class']); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $row['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                                <?php echo $row['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                    <?php else: ?>
                                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td><?php echo htmlspecialchars($row['department']); ?></td>
                                        <td><?php echo htmlspecialchars($row['designation']); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $row['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                                <?php echo $row['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>


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
                    <div class="empty-state">
                        <i class='bx bx-folder-open'></i>
                        <h3>No Records Found</h3>
                        <p>There are no <?php echo $type; ?> in the database to delete.</p>
                        <a href="add_record.php?type=<?php echo rtrim($type, 's'); ?>" class="btn btn-delete-selected" style="margin-top: 1.5rem; display: inline-flex;">
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
    const type = '<?php echo $type; ?>';
    let selectedIds = [];

    // Update selection count and button state
    function updateSelection() {
        const checkboxes = document.querySelectorAll('.row-checkbox:checked');
        selectedIds = Array.from(checkboxes).map(cb => cb.value);
        const count = selectedIds.length;
        
        // Update count displays
        document.getElementById('selectedCount').textContent = count;
        document.getElementById('deleteBtnCount').textContent = count;
        
        // Enable/disable delete button
        const deleteBtn = document.getElementById('deleteBtn');
        deleteBtn.disabled = count === 0;
        
        // Update row highlighting
        document.querySelectorAll('tbody tr').forEach(row => {
            const checkbox = row.querySelector('.row-checkbox');
            if (checkbox && checkbox.checked) {
                row.classList.add('selected');
            } else {
                row.classList.remove('selected');
            }
        });
        
        // Update select all checkbox
        const allCheckboxes = document.querySelectorAll('.row-checkbox');
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');
        selectAllCheckbox.checked = allCheckboxes.length > 0 && count === allCheckboxes.length;
    }

    // Toggle all checkboxes from header checkbox
    function toggleAll(checkbox) {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = checkbox.checked;
        });
        updateSelection();
    }

    // Select all records
    function selectAll() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = true;
        });
        document.getElementById('selectAllCheckbox').checked = true;
        updateSelection();
    }

    // Deselect all records
    function deselectAll() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = false;
        });
        document.getElementById('selectAllCheckbox').checked = false;
        updateSelection();
    }

    // Delete selected records - FIXED VERSION
    async function deleteSelected() {
        if (selectedIds.length === 0) {
            alert('Please select at least one record to delete.');
            return;
        }

        // Get names of selected records for confirmation
        const selectedCheckboxes = document.querySelectorAll('.row-checkbox:checked');
        const names = Array.from(selectedCheckboxes).map(cb => cb.dataset.name);
        
        // Build confirmation message
        let confirmMessage = `⚠️ WARNING: You are about to permanently delete ${selectedIds.length} ${type === 'students' ? 'student' : 'teacher'}${selectedIds.length > 1 ? 's' : ''}:\n\n`;
        
        if (names.length <= 5) {
            confirmMessage += names.join('\n');
        } else {
            confirmMessage += names.slice(0, 5).join('\n');
            confirmMessage += `\n... and ${names.length - 5} more`;
        }
        
        confirmMessage += '\n\n❌ This action CANNOT be undone!\n\nType "DELETE" to confirm:';
        
        // Ask for confirmation
        const userInput = prompt(confirmMessage);
        
        if (userInput !== 'DELETE') {
            if (userInput !== null) {
                alert('Deletion cancelled. You must type "DELETE" exactly to confirm.');
            }
            return;
        }

        // Show loading state
        const deleteBtn = document.getElementById('deleteBtn');
        const originalText = deleteBtn.innerHTML;
        deleteBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Deleting...';
        deleteBtn.disabled = true;

        try {
            const response = await fetch('../php/bulk_delete_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    type: type === 'students' ? 'student' : 'teacher',
                    ids: selectedIds
                })
            });

            // Check if response is ok
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            // Get response text first to see what we're getting
            const responseText = await response.text();
            
            // Debug logging (you can remove these after fixing)
            console.log('Raw Response:', responseText);
            console.log('Response Length:', responseText.length);
            
            // Try to parse as JSON
            let data;
            try {
                data = JSON.parse(responseText);
            } catch (e) {
                console.error('JSON Parse Error:', e);
                console.error('Response Text:', responseText);
                throw new Error('Server returned invalid JSON. Check console for details.');
            }

            // Re-enable button first
            deleteBtn.innerHTML = originalText;
            deleteBtn.disabled = false;

            // Check if deletion was successful
            if (data.success) {
                alert(`✅ Success!\n\n${data.message}`);
                
                // Remove deleted rows from table with animation
                selectedIds.forEach(id => {
                    const row = document.getElementById('row-' + id);
                    if (row) {
                        row.style.transition = 'all 0.3s ease';
                        row.style.opacity = '0';
                        row.style.transform = 'translateX(-20px)';
                        setTimeout(() => row.remove(), 300);
                    }
                });
                
                // Reset selection
                selectedIds = [];
                updateSelection();
                
                // Reload page after 2 seconds to update counts
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
                
            } else {
                // Show error from server
                alert('❌ Error: ' + data.message);
            }
        } catch (error) {
            // Re-enable button on error
            deleteBtn.innerHTML = originalText;
            deleteBtn.disabled = false;
            
            console.error('Delete Error:', error);
            alert('❌ An error occurred: ' + error.message + '\n\nPlease check the browser console (F12) for details.');
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateSelection();
    });

    // Confirm before leaving if records are selected
    window.addEventListener('beforeunload', function(e) {
        if (selectedIds.length > 0) {
            e.preventDefault();
            e.returnValue = 'You have selected records. Are you sure you want to leave?';
            return e.returnValue;
        }
    });
</script>