<?php
session_start();
require_once '../php/admin_session_check.php';
require_once '../php/db_connect.php';

// Fetch admin data
$admin_query = "SELECT id, username, full_name, email, phone FROM admin WHERE id = ?";
$admin_result = executeQuery($admin_query, "i", array($_SESSION['admin_id']));
$admin_data = $admin_result->fetch_assoc();

$admin_id = 'ADMIN' . str_pad($admin_data['id'], 3, '0', STR_PAD_LEFT);
$admin_name = $admin_data['full_name'];
$admin_email = $admin_data['email'];
$admin_phone = $admin_data['phone'] ?? '+91 9876543210';

// Get statistics
$students_count = executeQuery("SELECT COUNT(*) as total FROM students WHERE is_active = 1")->fetch_assoc()['total'];
$teachers_count = executeQuery("SELECT COUNT(*) as total FROM teachers WHERE is_active = 1")->fetch_assoc()['total'];
$faculty_count = executeQuery("SELECT COUNT(*) as total FROM faculty WHERE is_active = 1")->fetch_assoc()['total'];

// Get recent activity logs
$logs_query = "SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 5";
$logs_result = executeQuery($logs_query);
$activity_logs = [];
while ($row = $logs_result->fetch_assoc()) {
    $activity_logs[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MITAOE</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f5f7fa;
            min-height: 100vh;
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
            transition: transform 0.3s ease;
        }

        .sidebar.mobile-hidden {
            transform: translateX(-100%);
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
            transition: margin-left 0.3s ease;
        }

        .main-content.full-width {
            margin-left: 0;
        }

        /* Header */
        .header {
            position: sticky;
            top: 0;
            height: 70px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            z-index: 50;
        }

        .hamburger {
            display: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #2c3e50;
        }

        .breadcrumb {
            font-size: 0.9375rem;
            color: #718096;
        }

        .breadcrumb span {
            color: #4EA685;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .profile-dropdown {
            position: relative;
        }

        .profile-trigger {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            background: rgba(78,166,133,0.1);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .profile-trigger:hover {
            background: rgba(78,166,133,0.15);
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4EA685, #57B894);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.125rem;
            border: 2px solid #4EA685;
        }

        .dropdown-menu {
            position: absolute;
            right: 0;
            top: calc(100% + 0.5rem);
            width: 320px;
            background: white;
            border-radius: 1rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            display: none;
            animation: slideDown 0.3s ease;
        }

        .dropdown-menu.show {
            display: block;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            text-align: center; /* Center all content */
            display: flex;
            flex-direction: column; /* Stack items vertically */
            align-items: center; /* Center horizontally */
            gap: 1rem;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            opacity: 0.1;
        }

        .stat-card.blue::before {
            background: #3498db;
        }

        .stat-card.green::before {
            background: #2ecc71;
        }

        .stat-card.purple::before {
            background: #9b59b6;
        }

        .stat-card.orange::before {
            background: #e67e22;
        }

        .stat-number {
            font-size: 3.0rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1.0rem;
            color: #718096;
            font-weight: 500;
        }

        .stat-icon {
            width: 90px;
            height: 80px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
            flex-shrink: 0;
            margin: 0 auto; /* Center the icon container */
        }

        .stat-icon i {
            font-size: 1.5rem;
            color: white;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .action-card {
            background: white;
            border-radius: 0.75rem;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border: 2px solid transparent;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .action-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border-color: #4EA685;
        }

        .action-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 1rem;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(78,166,133,0.1);
        }

        .action-icon i {
            font-size: 2rem;
            color: #4EA685;
        }

        .action-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .action-subtitle {
            font-size: 0.875rem;
            color: #718096;
        }

        /* Activity Table */
        .activity-section {
            background: white;
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2c3e50;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f8f9fa;
            padding: 0.75rem 1rem;
            text-align: left;
            font-size: 0.875rem;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 2px solid #e5e7eb;
        }

        td {
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            color: #718096;
            border-bottom: 1px solid #e5e7eb;
        }

        tr:hover {
            background: #f0f9ff;
        }

        /* ===== ENHANCED RESPONSIVE DESIGN ===== */

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

    /* Adjust stats grid for tablet */
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1.25rem;
    }

    /* Quick actions - 2 columns on tablet */
    .quick-actions {
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

    .profile-trigger {
        padding: 0.4rem 0.75rem;
    }

    .avatar {
        width: 35px;
        height: 35px;
        font-size: 1rem;
    }

    .profile-trigger > div {
        display: none; /* Hide name on mobile */
    }

    /* Show only avatar on mobile */
    .profile-trigger .avatar {
        display: flex;
    }

    /* Dropdown menu full width on mobile */
    .dropdown-menu {
        width: calc(100vw - 2rem);
        right: 1rem;
        left: 1rem;
    }

    /* Stats - single column on mobile */
    .stats-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .stat-card {
        padding: 1.5rem;
        flex-direction: row;
        text-align: left;
    }

    .stat-icon {
        width: 70px;
        height: 70px;
        margin: 0;
    }

    .stat-icon i {
        font-size: 1.25rem;
    }

    .stat-number {
        font-size: 2.25rem;
    }

    .stat-label {
        font-size: 0.9375rem;
    }

    /* Quick actions - single column */
    .quick-actions {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .action-card {
        padding: 1.25rem;
    }

    .action-icon {
        width: 56px;
        height: 56px;
    }

    .action-icon i {
        font-size: 1.75rem;
    }

    .action-title {
        font-size: 1rem;
    }

    .action-subtitle {
        font-size: 0.8125rem;
    }

    /* Activity section */
    .activity-section {
        padding: 1.25rem;
    }

    .section-title {
        font-size: 1.25rem;
    }

    /* ===== ACTIVITY TABLE - IMPROVED ===== */
table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: #f8f9fa;
    padding: 0.75rem 1rem;
    text-align: left;
    font-size: 0.875rem;
    font-weight: 600;
    color: #2c3e50;
    border-bottom: 2px solid #e5e7eb;
}

td {
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
    color: #718096;
    border-bottom: 1px solid #e5e7eb;
}

tr:hover {
    background: #f0f9ff;
}

/* Table wrapper for horizontal scroll */
.table-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

/* Mobile table improvements */
@media (max-width: 768px) {
    .activity-section {
        padding: 1rem;
        overflow: hidden;
    }

    .section-header {
        margin-bottom: 1rem;
    }

    .section-title {
        font-size: 1.125rem;
    }

    /* Create horizontal scroll container */
    .activity-section > div {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    /* Table must maintain minimum width */
    table {
        min-width: 700px; /* Force horizontal scroll instead of shrinking */
        font-size: 0.75rem;
    }

    th {
        padding: 0.625rem 0.5rem;
        font-size: 0.75rem;
        white-space: nowrap;
    }

    td {
        padding: 0.625rem 0.5rem;
        font-size: 0.75rem;
    }

    /* Make first column sticky */
    th:first-child,
    td:first-child {
        position: sticky;
        left: 0;
        background: #f8f9fa;
        z-index: 2;
        box-shadow: 2px 0 4px rgba(0,0,0,0.05);
    }

    th:first-child {
        background: #f8f9fa;
    }

    /* Ensure proper column widths */
    th:nth-child(1), td:nth-child(1) { min-width: 120px; } /* Action */
    th:nth-child(2), td:nth-child(2) { min-width: 200px; } /* Details */
    th:nth-child(3), td:nth-child(3) { min-width: 120px; } /* IP */
    th:nth-child(4), td:nth-child(4) { min-width: 140px; } /* Date */
}

@media (max-width: 480px) {
    table {
        min-width: 600px;
        font-size: 0.6875rem;
    }

    th, td {
        padding: 0.5rem 0.375rem;
        font-size: 0.6875rem;
    }

    th:nth-child(1), td:nth-child(1) { min-width: 100px; }
    th:nth-child(2), td:nth-child(2) { min-width: 180px; }
    th:nth-child(3), td:nth-child(3) { min-width: 100px; }
    th:nth-child(4), td:nth-child(4) { min-width: 120px; }
}

    /* Sidebar overlay on mobile */
    .sidebar {
        width: 280px;
        box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    }

    /* Page content padding */
    main {
        padding: 1.5rem 1rem;
    }
}

/* Mobile Medium - 480px and below */
@media (max-width: 480px) {
    .header {
        padding: 0 0.75rem;
    }

    .breadcrumb {
        font-size: 0.8125rem;
    }

    /* Hide breadcrumb text on very small screens */
    .breadcrumb a {
        display: none;
    }

    .breadcrumb span {
        font-size: 0.875rem;
    }

    .hamburger {
        font-size: 1.25rem;
    }

    /* Stat cards more compact */
    .stat-card {
        padding: 1.25rem;
        gap: 1rem;
    }

    .stat-icon {
        width: 60px;
        height: 60px;
    }

    .stat-number {
        font-size: 2rem;
    }

    .stat-label {
        font-size: 0.875rem;
    }

    /* Quick actions */
    .action-card {
        padding: 1rem;
    }

    .action-icon {
        width: 52px;
        height: 52px;
    }

    .action-title {
        font-size: 0.9375rem;
    }

    /* Activity table */
    table {
        font-size: 0.6875rem;
    }

    th, td {
        padding: 0.4rem;
    }

    /* Section headers */
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

    .section-header h2 {
        font-size: 1.125rem;
    }

    .section-header a {
        width: 100%;
        justify-content: center;
    }

    /* Activity section */
    .activity-section {
        padding: 1rem;
    }

    /* Reduce padding everywhere */
    main {
        padding: 1rem 0.75rem;
    }
}

/* Mobile Small - 360px and below */
@media (max-width: 360px) {
    .stat-number {
        font-size: 1.75rem;
    }

    .stat-label {
        font-size: 0.8125rem;
    }

    .action-title {
        font-size: 0.875rem;
    }

    .action-subtitle {
        font-size: 0.75rem;
    }

    table {
        font-size: 0.625rem;
    }
}

/* Landscape mobile phones */
@media (max-height: 500px) and (orientation: landscape) {
    .sidebar {
        overflow-y: auto;
    }

    .sidebar-logo {
        padding: 1rem;
    }

    .sidebar-logo h1 {
        font-size: 1.125rem;
    }

    .menu-item {
        padding: 0.75rem 1rem;
    }

    .stat-card {
        padding: 1rem;
    }
}

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
    <!-- Overlay -->
    <div class="overlay" id="overlay" onclick="closeSidebar()"></div>

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
    <div class="main-content" id="mainContent">
        <!-- Header -->
        <header class="header">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <i class='bx bx-menu hamburger' id="hamburger" onclick="toggleSidebar()"></i>
                <div class="breadcrumb">Dashboard <span>&gt; Home</span></div>
            </div>

            <div class="header-right">
                <div class="profile-dropdown">
                    <div class="profile-trigger" onclick="toggleDropdown()">
                        <div class="avatar"><?php echo strtoupper(substr($admin_name, 0, 1)); ?></div>
                        <div style="text-align: left;">
                            <div style="font-size: 0.875rem; font-weight: 600; color: #2c3e50;"><?php echo htmlspecialchars($admin_name); ?></div>
                            <div style="font-size: 0.75rem; color: #e74c3c; font-weight: 500;">Administrator</div>
                        </div>
                        <i class='bx bx-chevron-down' style="font-size: 1.25rem; color: #718096;"></i>
                    </div>

                    <div class="dropdown-menu" id="dropdownMenu">
                        <div style="padding: 1.5rem; text-align: center; background: linear-gradient(135deg, #4EA685, #57B894);">
                            <div class="avatar" style="width: 80px; height: 80px; font-size: 2rem; margin: 0 auto 1rem; background: linear-gradient(135deg, #2c3e50, #34495e);">
                                <?php echo strtoupper(substr($admin_name, 0, 1)); ?>
                            </div>
                            <div style="color: white; font-weight: 600; font-size: 1.125rem;"><?php echo htmlspecialchars($admin_name); ?></div>
                            <div style="color: rgba(255,255,255,0.9); font-size: 0.875rem; margin-top: 0.25rem;">Administrator</div>
                        </div>

                        <div style="padding: 1rem; background: #f9fafb;">
                            <div style="padding: 0.75rem; display: flex; align-items: center; gap: 0.75rem; font-size: 0.875rem;">
                                <i class='bx bx-hash' style="font-size: 1.25rem; color: #4EA685;"></i>
                                <div>
                                    <div style="font-size: 0.6875rem; color: #718096;">Admin ID</div>
                                    <div style="font-weight: 500; color: #2c3e50;"><?php echo $admin_id; ?></div>
                                </div>
                            </div>
                            <div style="padding: 0.75rem; display: flex; align-items: center; gap: 0.75rem; font-size: 0.875rem;">
                                <i class='bx bx-envelope' style="font-size: 1.25rem; color: #4EA685;"></i>
                                <div>
                                    <div style="font-size: 0.6875rem; color: #718096;">Email</div>
                                    <div style="font-weight: 500; color: #2c3e50;"><?php echo htmlspecialchars($admin_email); ?></div>
                                </div>
                            </div>
                            <div style="padding: 0.75rem; display: flex; align-items: center; gap: 0.75rem; font-size: 0.875rem;">
                                <i class='bx bx-phone' style="font-size: 1.25rem; color: #4EA685;"></i>
                                <div>
                                    <div style="font-size: 0.6875rem; color: #718096;">Mobile</div>
                                    <div style="font-weight: 500; color: #2c3e50;"><?php echo htmlspecialchars($admin_phone); ?></div>
                                </div>
                            </div>
                        </div>

                        <div style="padding: 0.5rem; border-top: 1px solid #e5e7eb;">
                            <a href="../php/admin_logout.php" onclick="return confirm('Are you sure you want to logout?')" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; color: #ef4444; text-decoration: none; border-radius: 0.5rem; transition: background 0.2s;">
                                <i class='bx bx-log-out' style="font-size: 1.25rem;"></i>
                                <span style="font-size: 0.875rem; font-weight: 500;">Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content -->
        <main style="padding: 2rem; max-width: 1400px; margin: 0 auto;">
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-number"><?php echo $students_count; ?></div>
                    <div class="stat-label">Total Students</div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, #3498db, #2980b9);">
                        <i class='bx bxs-graduation'></i>
                    </div>
                </div>

                <div class="stat-card green">
                    <div class="stat-number"><?php echo $teachers_count; ?></div>
                    <div class="stat-label">Total Teachers</div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, #2ecc71, #27ae60);">
                        <i class='bx bx-group'></i>
                    </div>
                </div>

                <div class="stat-card purple">
                    <div class="stat-number"><?php echo $faculty_count; ?></div>
                    <div class="stat-label">Faculty Accounts</div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, #9b59b6, #8e44ad);">
                        <i class='bx bx-user'></i>
                    </div>
                </div>

                <div class="stat-card orange">
                    <div class="stat-number"><?php echo count($activity_logs); ?></div>
                    <div class="stat-label">Recent Actions</div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, #e67e22, #d35400);">
                        <i class='bx bx-bar-chart'></i>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <h2 style="font-size: 1.5rem; font-weight: 600; color: #2c3e50; margin-bottom: 1.5rem;">Quick Actions</h2>
            <div class="quick-actions">
                <a href="add_record.php?type=student" class="action-card">
                    <div class="action-icon"><i class='bx bx-user-plus'></i></div>
                    <div class="action-title">Add New Student</div>
                    <div class="action-subtitle">Add single student record</div>
                </a>

                <a href="add_record.php?type=teacher" class="action-card">
                    <div class="action-icon"><i class='bx bx-user-plus'></i></div>
                    <div class="action-title">Add New Teacher</div>
                    <div class="action-subtitle">Add single teacher record</div>
                </a>

                <a href="upload_excel.php" class="action-card">
                    <div class="action-icon"><i class='bx bx-upload'></i></div>
                    <div class="action-title">Bulk Upload</div>
                    <div class="action-subtitle">Upload CSV/Excel files</div>
                </a>

                <a href="view_all.php" class="action-card">
                    <div class="action-icon"><i class='bx bxs-dashboard'></i></div>
                    <div class="action-title">View All Records</div>
                    <div class="action-subtitle">Browse all data</div>
                </a>

                <a href="search.php" class="action-card">
                    <div class="action-icon"><i class='bx bx-search'></i></div>
                    <div class="action-title">Search Records</div>
                    <div class="action-subtitle">Find specific records</div>
                </a>

                <a href="delete_record.php" class="action-card">
                    <div class="action-icon"><i class='bx bx-trash' style="color: #ef4444;"></i></div>
                    <div class="action-title">Delete Records</div>
                    <div class="action-subtitle">Remove student/teacher</div>
                </a>
            </div>

            <!-- Approve Faculty Card -->
            <!-- Approve Faculty Action Card -->
<a href="approve_faculty.php" class="action-card" style="background: linear-gradient(135deg, #07ffac41 0%, #060808 100%);">
    <div class="action-icon" style="background: rgba(255, 255, 255, 0.2);">
        <i class='bx bx-user-check' style="color: white;"></i>
    </div>
    <div class="action-title" style="color: white;">Faculty Approvals</div>
    <div class="action-subtitle" style="color: rgba(255, 255, 255, 0.9);">
        <?php
        $pendingCount = 0;
        $pendingQuery = "SELECT COUNT(*) as count FROM faculty WHERE approval_status = 'pending' AND email_verified = 1";
        $pendingResult = executeQuery($pendingQuery, "", array());
        if ($pendingResult) {
            $pendingCount = $pendingResult->fetch_assoc()['count'];
        }
        echo $pendingCount;
        ?> pending approval<?php echo $pendingCount != 1 ? 's' : ''; ?>
    </div>
</a>

            <!-- Recent Activity -->
            <div class="activity-section">
                <div class="section-header">
                    <h2 class="section-title">Recent Activity</h2>
                    <a href="activity_logs.php" style="padding: 0.5rem 1rem; background: rgba(78,166,133,0.1); color: #4EA685; border-radius: 0.5rem; text-decoration: none; font-size: 0.875rem; font-weight: 500;">View All</a>
                </div>

                <div class="table-wrapper" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                    <table>
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>Details</th>
                                <th>IP Address</th>
                                <th>Date & Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($activity_logs)): ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 2rem; color: #718096;">No recent activity</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($activity_logs as $log): ?>
                                    <tr>
                                        <td style="font-weight: 500; color: #2c3e50;"><?php echo htmlspecialchars($log['action']); ?></td>
                                        <td><?php echo htmlspecialchars($log['details']); ?></td>
                                        <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($log['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            sidebar.classList.toggle('mobile-show');
            overlay.classList.toggle('show');
        }

        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            sidebar.classList.remove('mobile-show');
            overlay.classList.remove('show');
        }

        function toggleDropdown() {
            const dropdown = document.getElementById('dropdownMenu');
            dropdown.classList.toggle('show');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('dropdownMenu');
            const trigger = event.target.closest('.profile-trigger');
            
            if (!trigger && dropdown.classList.contains('show')) {
                dropdown.classList.remove('show');
            }
        });
    </script>
    <script>
    // Mobile sidebar toggle
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.getElementById('overlay');
        sidebar.classList.toggle('mobile-show');
        overlay.classList.toggle('show');
    }

    function closeSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.getElementById('overlay');
        sidebar.classList.remove('mobile-show');
        overlay.classList.remove('show');
    }

    // Close sidebar when clicking on menu item (mobile only)
    if (window.innerWidth <= 1024) {
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', closeSidebar);
        });
    }

    // Profile dropdown toggle
    function toggleDropdown() {
        const dropdown = document.getElementById('dropdownMenu');
        dropdown.classList.toggle('show');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('dropdownMenu');
        const trigger = event.target.closest('.profile-trigger');
        
        if (!trigger && dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
        }
    });

    // Close dropdowns on window resize
    window.addEventListener('resize', () => {
        if (window.innerWidth > 1024) {
            document.getElementById('overlay').classList.remove('show');
            document.querySelector('.sidebar').classList.remove('mobile-show');
        }
    });
</script>
</body>
</html>