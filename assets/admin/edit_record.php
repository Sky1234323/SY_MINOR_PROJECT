<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

require_once '../php/db_connect.php';

// Get type and ID from URL
$type = isset($_GET['type']) ? $_GET['type'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!in_array($type, ['student', 'teacher']) || $id <= 0) {
    header("Location: view_all.php?type=students");
    exit();
}

// Get existing record
if ($type === 'student') {
    $query = "SELECT * FROM students WHERE id = ?";
    $title = "Edit Student";
} else {
    $query = "SELECT * FROM teachers WHERE id = ?";
    $title = "Edit Teacher";
}

$result = executeQuery($query, "i", array($id));

if (!$result || $result->num_rows === 0) {
    header("Location: view_all.php?type=" . $type . "s");
    exit();
}

$record = $result->fetch_assoc();

// Get admin data
$admin_query = "SELECT full_name FROM admin WHERE id = ?";
$admin_result = executeQuery($admin_query, "i", array($_SESSION['admin_id']));
$admin_data = $admin_result->fetch_assoc();

// Departments list
$departments = ['Computer Science', 'Information Technology', 'Electronics', 'Mechanical', 'Civil', 'Electrical'];
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
            position: relative;
            overflow-x: hidden;
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
            max-width: 1200px;
            margin: 0 auto;
        }

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

        /* Form Card */
        .form-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            animation: fadeInUp 0.6s ease;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Alert */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: none;
            animation: slideDown 0.3s ease;
            font-weight: 600;
            font-size: 0.9375rem;
        }

        .alert.active { display: block; }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-success {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
            border-left: 5px solid #229954;
        }

        .alert-danger {
            background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
            color: white;
            border-left: 5px solid #c0392b;
        }

        /* Form */
        .form-section {
            margin-bottom: 2.5rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 3px solid #4EA685;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-title i {
            color: #4EA685;
            font-size: 1.5rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .form-group label .required {
            color: #dc3545;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            font-size: 0.9375rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #4EA685;
            box-shadow: 0 0 0 4px rgba(78,166,133,0.1);
        }

        select.form-control {
            cursor: pointer;
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        .radio-group {
            display: flex;
            gap: 1.5rem;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .radio-option input[type="radio"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #4EA685;
        }

        /* Buttons */
        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2.5rem;
            padding-top: 2rem;
            border-top: 2px solid #e5e7eb;
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
        }

        .btn-primary {
            background: linear-gradient(135deg, #4EA685, #57B894);
            color: white;
            box-shadow: 0 6px 20px rgba(78,166,133,0.3);
        }

        .btn-primary:hover:not(:disabled) {
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

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        /* ============================================
   COMPREHENSIVE RESPONSIVE DESIGN
   ============================================ */

/* Tablet - 1024px and below */
@media (max-width: 1024px) {
    /* Hide sidebar on tablet */
    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }

    .sidebar.mobile-show {
        transform: translateX(0);
        box-shadow: 2px 0 20px rgba(0,0,0,0.2);
    }

    .main-content {
        margin-left: 0;
    }

    /* Show hamburger menu button */
    .hamburger {
        display: block;
        font-size: 1.5rem;
        cursor: pointer;
        color: #2c3e50;
    }

    /* Form grid - 2 columns on tablet */
    .form-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1.25rem;
    }

    .form-card {
        padding: 2.5rem 2rem;
    }

    .page-header h1 {
        font-size: 2rem;
    }

    /* Overlay for mobile menu */
    .overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 90;
        transition: opacity 0.3s ease;
    }

    .overlay.show {
        display: block;
    }
}

/* Mobile Large - 768px and below */
@media (max-width: 768px) {
    /* Header adjustments */
    .header {
        padding: 0 1rem;
        height: 60px;
    }

    .breadcrumb {
        font-size: 0.875rem;
    }

    /* Hide breadcrumb links on mobile */
    .breadcrumb a {
        display: none;
    }

    .back-btn {
        padding: 0.4rem 0.75rem;
        font-size: 0.8125rem;
    }

    /* Hide "Back to Dashboard" text, show only icon */
    .back-btn span {
        display: none;
    }

    .back-btn i {
        margin: 0;
    }

    /* Page content */
    .page-content {
        padding: 1.5rem 1rem;
    }

    /* Page header */
    .page-header {
        margin-bottom: 1.5rem;
    }

    .page-header h1 {
        font-size: 1.75rem;
    }

    .page-header p {
        font-size: 0.9375rem;
    }

    /* Type selector - vertical stack on mobile */
    .type-selector {
        flex-direction: column;
        gap: 0.75rem;
    }

    .type-btn {
        width: 100%;
        justify-content: center;
        padding: 0.875rem 1.5rem;
    }

    /* Form card */
    .form-card {
        padding: 2rem 1.5rem;
        border-radius: 15px;
    }

    /* Form grid - single column on mobile */
    .form-grid {
        grid-template-columns: 1fr;
        gap: 1.25rem;
    }

    /* Section titles */
    .section-title {
        font-size: 1.125rem;
        margin-bottom: 1.25rem;
        padding-bottom: 0.625rem;
    }

    .section-title i {
        font-size: 1.25rem;
    }

    /* Form controls */
    .form-group label {
        font-size: 0.8125rem;
        margin-bottom: 0.5rem;
    }

    .form-control {
        padding: 0.75rem 0.875rem;
        font-size: 0.875rem;
        border-radius: 8px;
    }

    select.form-control {
        padding-right: 2rem;
    }

    textarea.form-control {
        min-height: 80px;
    }

    /* Radio groups - vertical */
    .radio-group {
        flex-direction: column;
        gap: 0.75rem;
        align-items: flex-start;
    }

    .radio-option {
        width: 100%;
    }

    /* Form sections */
    .form-section {
        margin-bottom: 2rem;
    }

    /* Form actions - vertical stack */
    .form-actions {
        flex-direction: column;
        gap: 0.75rem;
        margin-top: 2rem;
        padding-top: 1.5rem;
    }

    .btn {
        width: 100%;
        justify-content: center;
        padding: 0.875rem 1.5rem;
        font-size: 0.9375rem;
    }

    /* Alert messages */
    .alert {
        padding: 0.875rem 1rem;
        font-size: 0.875rem;
        border-radius: 10px;
    }

    /* Reduce particle animations on mobile for performance */
    .bg-particles .particle {
        opacity: 0.15 !important;
        animation-duration: 30s !important;
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

    .form-card {
        padding: 1.5rem 1rem;
    }

    .section-title {
        font-size: 1rem;
    }

    .form-group label {
        font-size: 0.75rem;
    }

    .form-control {
        padding: 0.625rem 0.75rem;
        font-size: 0.8125rem;
    }

    .btn {
        padding: 0.75rem 1.25rem;
        font-size: 0.875rem;
        gap: 0.5rem;
    }

    .btn i {
        font-size: 1.125rem;
    }

    /* Type selector buttons smaller */
    .type-btn {
        padding: 0.75rem 1.25rem;
        font-size: 0.875rem;
    }

    /* Radio buttons */
    .radio-option input[type="radio"] {
        width: 18px;
        height: 18px;
    }

    /* Reduce alert padding */
    .alert {
        padding: 0.75rem 0.875rem;
        font-size: 0.8125rem;
    }
}

/* Mobile Small - 360px and below */
@media (max-width: 360px) {
    .page-header h1 {
        font-size: 1.375rem;
    }

    .form-card {
        padding: 1.25rem 0.875rem;
    }

    .section-title {
        font-size: 0.9375rem;
    }

    .form-control {
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
}

/* Landscape mobile phones */
@media (max-height: 500px) and (orientation: landscape) {
    .page-header {
        margin-bottom: 1rem;
    }

    .page-header h1 {
        font-size: 1.5rem;
    }

    .form-card {
        padding: 1.5rem 1rem;
    }

    .form-section {
        margin-bottom: 1.5rem;
    }

    .section-title {
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
    }

    .form-actions {
        margin-top: 1.5rem;
        padding-top: 1rem;
    }
}

/* Prevent overlay on desktop */
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
                <a href="view_all.php?type=<?php echo $type; ?>s">View All</a> <span>&gt;</span> 
                <span>Edit <?php echo ucfirst($type); ?></span>
            </div>
            <div class="header-right">
                <a href="view_profile.php?type=<?php echo $type; ?>&id=<?php echo $id; ?>" class="back-btn">
                    <i class='bx bx-arrow-back'></i>
                    Back to Profile
                </a>
            </div>
        </header>

        <!-- Page Content -->
        <div class="page-content">
            <!-- Page Header -->
            <div class="page-header">
                <h1>✏️ <?php echo $title; ?></h1>
                <p>Update the details below to modify the <?php echo $type; ?> record</p>
            </div>

            <!-- Form Card -->
            <div class="form-card">
                <div id="alertMessage" class="alert"></div>

                <form id="editRecordForm">
                    <input type="hidden" name="type" value="<?php echo $type; ?>">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">

                    <?php if ($type === 'student'): ?>
                        <!-- STUDENT FORM -->
                        
                        <!-- Basic Information -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class='bx bx-user'></i>
                                Basic Information
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>PRN (Student ID) <span class="required">*</span></label>
                                    <input type="text" name="prn" class="form-control" value="<?php echo htmlspecialchars($record['prn']); ?>" required readonly style="background: #f3f4f6; cursor: not-allowed;">
                                </div>
                                <div class="form-group">
                                    <label>First Name <span class="required">*</span></label>
                                    <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($record['first_name']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Middle Name</label>
                                    <input type="text" name="middle_name" class="form-control" value="<?php echo htmlspecialchars($record['middle_name']); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Last Name <span class="required">*</span></label>
                                    <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($record['last_name']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Date of Birth <span class="required">*</span></label>
                                    <input type="date" name="dob" class="form-control" value="<?php echo $record['dob']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Gender <span class="required">*</span></label>
                                    <select name="gender" class="form-control" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male" <?php echo $record['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo $record['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                                        <option value="Other" <?php echo $record['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class='bx bx-phone'></i>
                                Contact Information
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Email <span class="required">*</span></label>
                                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($record['email']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Phone Number <span class="required">*</span></label>
                                    <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($record['phone']); ?>" required>
                                </div>
                                <div class="form-group full-width">
                                    <label>Address</label>
                                    <textarea name="address" class="form-control"><?php echo htmlspecialchars($record['address']); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>City</label>
                                    <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($record['city']); ?>">
                                </div>
                                <div class="form-group">
                                    <label>State</label>
                                    <input type="text" name="state" class="form-control" value="<?php echo htmlspecialchars($record['state']); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Academic Information -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class='bx bx-book'></i>
                                Academic Information
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Department <span class="required">*</span></label>
                                    <select name="department" class="form-control" required>
                                        <option value="">Select Department</option>
                                        <?php foreach ($departments as $dept): ?>
                                            <option value="<?php echo $dept; ?>" <?php echo $record['department'] === $dept ? 'selected' : ''; ?>>
                                                <?php echo $dept; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Class <span class="required">*</span></label>
                                    <select name="class" class="form-control" required>
                                        <option value="">Select Class</option>
                                        <option value="FE" <?php echo $record['class'] === 'FE' ? 'selected' : ''; ?>>FE (First Year)</option>
                                        <option value="SE" <?php echo $record['class'] === 'SE' ? 'selected' : ''; ?>>SE (Second Year)</option>
                                        <option value="TE" <?php echo $record['class'] === 'TE' ? 'selected' : ''; ?>>TE (Third Year)</option>
                                        <option value="BE" <?php echo $record['class'] === 'BE' ? 'selected' : ''; ?>>BE (Final Year)</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Division <span class="required">*</span></label>
                                    <select name="division" class="form-control" required>
                                        <option value="">Select Division</option>
                                        <option value="A" <?php echo $record['division'] === 'A' ? 'selected' : ''; ?>>Division A</option>
                                        <option value="B" <?php echo $record['division'] === 'B' ? 'selected' : ''; ?>>Division B</option>
                                        <option value="C" <?php echo $record['division'] === 'C' ? 'selected' : ''; ?>>Division C</option>
                                        <option value="D" <?php echo $record['division'] === 'D' ? 'selected' : ''; ?>>Division D</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Roll Number <span class="required">*</span></label>
                                    <input type="text" name="roll_number" class="form-control" value="<?php echo htmlspecialchars($record['roll_number']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Batch Year <span class="required">*</span></label>
                                    <input type="number" name="batch_year" class="form-control" value="<?php echo $record['batch_year']; ?>" required min="2000" max="2100">
                                </div>
                            </div>
                        </div>

                        <!-- Parent Information -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class='bx bx-user-circle'></i>
                                Parent/Guardian Information
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Parent Name</label>
                                    <input type="text" name="parent_name" class="form-control" value="<?php echo htmlspecialchars($record['parent_name']); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Parent Phone</label>
                                    <input type="tel" name="parent_phone" class="form-control" value="<?php echo htmlspecialchars($record['parent_phone']); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class='bx bx-check-circle'></i>
                                Account Status
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Status <span class="required">*</span></label>
                                    <div class="radio-group">
                                        <div class="radio-option">
                                            <input type="radio" id="active" name="is_active" value="1" <?php echo $record['is_active'] == 1 ? 'checked' : ''; ?>>
                                            <label for="active">Active</label>
                                        </div>
                                        <div class="radio-option">
                                            <input type="radio" id="inactive" name="is_active" value="0" <?php echo $record['is_active'] == 0 ? 'checked' : ''; ?>>
                                            <label for="inactive">Inactive</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php else: ?>
                        <!-- TEACHER FORM -->
                        
                        <!-- Basic Information -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class='bx bx-user'></i>
                                Basic Information
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>First Name <span class="required">*</span></label>
                                    <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($record['first_name']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Middle Name</label>
                                    <input type="text" name="middle_name" class="form-control" value="<?php echo htmlspecialchars($record['middle_name']); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Last Name <span class="required">*</span></label>
                                    <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($record['last_name']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Gender <span class="required">*</span></label>
                                    <select name="gender" class="form-control" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male" <?php echo $record['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo $record['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                                        <option value="Other" <?php echo $record['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class='bx bx-phone'></i>
                                Contact Information
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Email (MITAOE) <span class="required">*</span></label>
                                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($record['email']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Phone Number <span class="required">*</span></label>
                                    <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($record['phone']); ?>" required>
                                </div>
                                <div class="form-group full-width">
                                    <label>Address</label>
                                    <textarea name="address" class="form-control"><?php echo htmlspecialchars($record['address']); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>City</label>
                                    <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($record['city']); ?>">
                                </div>
                                <div class="form-group">
                                    <label>State</label>
                                    <input type="text" name="state" class="form-control" value="<?php echo htmlspecialchars($record['state']); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Professional Information -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class='bx bx-briefcase'></i>
                                Professional Information
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Department <span class="required">*</span></label>
                                    <select name="department" class="form-control" required>
                                        <option value="">Select Department</option>
                                        <?php foreach ($departments as $dept): ?>
                                            <option value="<?php echo $dept; ?>" <?php echo $record['department'] === $dept ? 'selected' : ''; ?>>
                                                <?php echo $dept; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Designation <span class="required">*</span></label>
                                    <select name="designation" class="form-control" required>
                                        <option value="">Select Designation</option>
                                        <option value="Professor" <?php echo $record['designation'] === 'Professor' ? 'selected' : ''; ?>>Professor</option>
                                        <option value="Associate Professor" <?php echo $record['designation'] === 'Associate Professor' ? 'selected' : ''; ?>>Associate Professor</option>
                                        <option value="Assistant Professor" <?php echo $record['designation'] === 'Assistant Professor' ? 'selected' : ''; ?>>Assistant Professor</option>
                                        <option value="Lecturer" <?php echo $record['designation'] === 'Lecturer' ? 'selected' : ''; ?>>Lecturer</option>
                                        <option value="HOD" <?php echo $record['designation'] === 'HOD' ? 'selected' : ''; ?>>HOD (Head of Department)</option>
                                        <option value="Lab Assistant" <?php echo $record['designation'] === 'Lab Assistant' ? 'selected' : ''; ?>>Lab Assistant</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Qualification <span class="required">*</span></label>
                                    <select name="qualification" class="form-control" required>
                                        <option value="">Select Qualification</option>
                                        <option value="PhD" <?php echo $record['qualification'] === 'PhD' ? 'selected' : ''; ?>>PhD</option>
                                        <option value="M.Tech" <?php echo $record['qualification'] === 'M.Tech' ? 'selected' : ''; ?>>M.Tech</option>
                                        <option value="M.E." <?php echo $record['qualification'] === 'M.E.' ? 'selected' : ''; ?>>M.E.</option>
                                        <option value="M.Sc" <?php echo $record['qualification'] === 'M.Sc' ? 'selected' : ''; ?>>M.Sc</option>
                                        <option value="B.Tech" <?php echo $record['qualification'] === 'B.Tech' ? 'selected' : ''; ?>>B.Tech</option>
                                        <option value="B.E." <?php echo $record['qualification'] === 'B.E.' ? 'selected' : ''; ?>>B.E.</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Experience (Years) <span class="required">*</span></label>
                                    <input type="number" name="experience" class="form-control" value="<?php echo $record['experience']; ?>" required min="0" max="50">
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="form-section">
                            <div class="section-title">
                                <i class='bx bx-check-circle'></i>
                                Account Status
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Status <span class="required">*</span></label>
                                    <div class="radio-group">
                                        <div class="radio-option">
                                            <input type="radio" id="active_teacher" name="is_active" value="1" <?php echo $record['is_active'] == 1 ? 'checked' : ''; ?>>
                                            <label for="active_teacher">Active</label>
                                        </div>
                                        <div class="radio-option">
                                            <input type="radio" id="inactive_teacher" name="is_active" value="0" <?php echo $record['is_active'] == 0 ? 'checked' : ''; ?>>
                                            <label for="inactive_teacher">Inactive</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php endif; ?>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <a href="view_profile.php?type=<?php echo $type; ?>&id=<?php echo $id; ?>" class="btn btn-secondary">
                            <i class='bx bx-x'></i>
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span id="btnText">
                                <i class='bx bx-save'></i>
                                Update <?php echo ucfirst($type); ?>
                            </span>
                            <div id="btnSpinner" class="spinner" style="display: none;"></div>
                        </button>
                    </div>
                </form>
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
        const form = document.getElementById('editRecordForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const btnSpinner = document.getElementById('btnSpinner');
        const alertMessage = document.getElementById('alertMessage');

        // Form submission
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Get form data
            const formData = new FormData(this);

            // Disable button and show loading
            submitBtn.disabled = true;
            btnText.style.display = 'none';
            btnSpinner.style.display = 'block';

            try {
                const response = await fetch('../php/edit_record_handler.php', {
                    method: 'POST',
                    body: formData
                });

                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Server response:', text);
                    throw new Error('Server returned invalid response');
                }

                const data = await response.json();

                // Re-enable button
                submitBtn.disabled = false;
                btnText.style.display = 'flex';
                btnSpinner.style.display = 'none';

                if (data.success) {
                    showAlert(data.message, 'success');
                    
                    // Redirect to profile after 2 seconds
                    setTimeout(() => {
                        const type = formData.get('type');
                        const id = formData.get('id');
                        window.location.href = `view_profile.php?type=${type}&id=${id}`;
                    }, 2000);
                } else {
                    showAlert(data.message, 'danger');
                }
            } catch (error) {
                // Re-enable button
                submitBtn.disabled = false;
                btnText.style.display = 'flex';
                btnSpinner.style.display = 'none';

                console.error('Error:', error);
                showAlert('An error occurred. Please try again.', 'danger');
            }
        });

        // Show alert function
        function showAlert(message, type) {
            alertMessage.textContent = message;
            alertMessage.className = `alert alert-${type} active`;
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                alertMessage.classList.remove('active');
            }, 5000);

            // Scroll to top to show alert
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Phone number formatting
        const phoneInputs = document.querySelectorAll('input[type="tel"]');
        phoneInputs.forEach(input => {
            input.addEventListener('input', function() {
                // Remove non-numeric characters except +
                this.value = this.value.replace(/[^\d+]/g, '');
            });
        });
    </script>
</body>
</html>