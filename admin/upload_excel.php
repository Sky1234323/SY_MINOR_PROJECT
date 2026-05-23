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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Excel/CSV - MITAOE Admin</title>
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
            max-width: 1200px;
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

        /* Type Selector */
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

        /* Upload Card */
        .upload-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            animation: fadeInUp 0.6s ease;
            margin-bottom: 2rem;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Upload Zone */
        .upload-zone {
            border: 3px dashed #4EA685;
            border-radius: 15px;
            padding: 3rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            background: rgba(78,166,133,0.03);
            margin-bottom: 2rem;
        }

        .upload-zone:hover {
            border-color: #57B894;
            background: rgba(78,166,133,0.08);
            transform: translateY(-2px);
        }

        .upload-zone.dragover {
            border-color: #57B894;
            background: rgba(78,166,133,0.15);
            transform: scale(1.02);
        }

        .upload-icon {
            font-size: 4rem;
            color: #4EA685;
            margin-bottom: 1rem;
        }

        .upload-zone h3 {
            font-size: 1.5rem;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .upload-zone p {
            font-size: 1rem;
            color: #718096;
            margin-bottom: 1rem;
        }

        .file-info {
            font-size: 0.875rem;
            color: #a0aec0;
        }

        #fileInput {
            display: none;
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

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* File Preview */
        .file-preview {
            display: none;
            background: rgba(78,166,133,0.1);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .file-preview.active {
            display: block;
            animation: fadeInUp 0.3s ease;
        }

        .file-preview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .file-name {
            font-weight: 600;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .remove-file {
            color: #ef4444;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .remove-file:hover {
            background: rgba(239,68,68,0.1);
        }

        /* Info Boxes */
        .info-boxes {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .info-box {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border-left: 5px solid #4EA685;
        }

        .info-box h3 {
            color: #2c3e50;
            font-size: 1.125rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-box h3 i {
            color: #4EA685;
            font-size: 1.5rem;
        }

        .info-box ul {
            list-style: none;
            padding: 0;
        }

        .info-box li {
            padding: 0.5rem 0;
            color: #718096;
            display: flex;
            align-items: start;
            gap: 0.5rem;
        }

        .info-box li i {
            color: #4EA685;
            margin-top: 0.25rem;
        }

        /* Sample Template */
        .template-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .template-section h3 {
            color: #2c3e50;
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .template-section h3 i {
            color: #4EA685;
            font-size: 1.5rem;
        }

        .template-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-secondary {
            background: rgba(78,166,133,0.1);
            color: #4EA685;
            border: 2px solid #4EA685;
        }

        .btn-secondary:hover {
            background: rgba(78,166,133,0.2);
            transform: translateY(-2px);
        }

        /* Alert */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: none;
            animation: slideDown 0.3s ease;
            font-weight: 600;
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

        .alert-info {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border-left: 5px solid #2471a3;
        }

        /* Progress */
        .upload-progress {
            display: none;
            margin-top: 1.5rem;
        }

        .upload-progress.active {
            display: block;
        }

        .progress-bar {
            height: 30px;
            background: #e5e7eb;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 1rem;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, #4EA685, #57B894);
            width: 0%;
            transition: width 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .progress-info {
            text-align: center;
            color: #718096;
            font-size: 0.9375rem;
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

    /* Info boxes - 2 columns */
    .info-boxes {
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

    /* Upload card */
    .upload-card {
        padding: 2rem 1.5rem;
        border-radius: 15px;
    }

    /* Upload zone */
    .upload-zone {
        padding: 2.5rem 1.5rem;
        border-radius: 12px;
    }

    .upload-icon {
        font-size: 3.5rem;
    }

    .upload-zone h3 {
        font-size: 1.25rem;
    }

    .upload-zone p {
        font-size: 0.9375rem;
    }

    .file-info {
        font-size: 0.8125rem;
    }

    /* File preview */
    .file-preview {
        padding: 1.25rem;
        border-radius: 12px;
        margin-bottom: 1.25rem;
    }

    .file-preview-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .file-name {
        font-size: 0.875rem;
        word-break: break-all;
    }

    .remove-file {
        align-self: flex-end;
        padding: 0.375rem;
    }

    /* Upload button */
    .btn {
        padding: 0.875rem 1.5rem;
        font-size: 0.9375rem;
    }

    /* Template section */
    .template-section {
        padding: 1.5rem;
        border-radius: 12px;
    }

    .template-section h3 {
        font-size: 1.125rem;
        margin-bottom: 1.25rem;
    }

    .template-section p {
        font-size: 0.875rem;
    }

    .template-buttons {
        flex-direction: column;
        gap: 0.75rem;
    }

    .btn-secondary {
        width: 100%;
        justify-content: center;
    }

    /* Info boxes - single column */
    .info-boxes {
        grid-template-columns: 1fr;
        gap: 1.25rem;
    }

    .info-box {
        padding: 1.5rem;
        border-radius: 12px;
    }

    .info-box h3 {
        font-size: 1rem;
        margin-bottom: 0.875rem;
    }

    .info-box h3 i {
        font-size: 1.25rem;
    }

    .info-box li {
        font-size: 0.875rem;
        padding: 0.375rem 0;
    }

    /* Progress bar */
    .progress-bar {
        height: 25px;
    }

    .progress-fill {
        font-size: 0.875rem;
    }

    .progress-info {
        font-size: 0.875rem;
    }

    /* Alert */
    .alert {
        padding: 0.875rem 1rem;
        font-size: 0.875rem;
        border-radius: 10px;
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

    .type-btn {
        padding: 0.75rem 1.25rem;
        font-size: 0.875rem;
    }

    .upload-card {
        padding: 1.5rem 1rem;
    }

    .upload-zone {
        padding: 2rem 1.25rem;
    }

    .upload-icon {
        font-size: 3rem;
    }

    .upload-zone h3 {
        font-size: 1.125rem;
    }

    .upload-zone p {
        font-size: 0.875rem;
    }

    .file-info {
        font-size: 0.75rem;
    }

    .file-preview {
        padding: 1rem;
    }

    .file-name {
        font-size: 0.8125rem;
    }

    .btn {
        padding: 0.75rem 1.25rem;
        font-size: 0.875rem;
    }

    .template-section {
        padding: 1.25rem;
    }

    .template-section h3 {
        font-size: 1rem;
    }

    .info-box {
        padding: 1.25rem;
    }

    .info-box h3 {
        font-size: 0.9375rem;
    }

    .info-box li {
        font-size: 0.8125rem;
    }

    .progress-bar {
        height: 22px;
    }

    .progress-fill {
        font-size: 0.8125rem;
    }
}

/* Mobile Small - 360px and below */
@media (max-width: 360px) {
    .page-header h1 {
        font-size: 1.375rem;
    }

    .upload-card {
        padding: 1.25rem 0.875rem;
    }

    .upload-zone {
        padding: 1.75rem 1rem;
    }

    .upload-icon {
        font-size: 2.5rem;
    }

    .upload-zone h3 {
        font-size: 1rem;
    }

    .upload-zone p {
        font-size: 0.8125rem;
    }

    .btn {
        padding: 0.625rem 1rem;
        font-size: 0.8125rem;
    }

    .template-section {
        padding: 1rem;
    }

    .info-box {
        padding: 1rem;
    }
}

/* Landscape mobile */
@media (max-height: 500px) and (orientation: landscape) {
    .page-header {
        margin-bottom: 1rem;
    }

    .upload-card {
        padding: 1.5rem;
    }

    .upload-zone {
        padding: 1.5rem;
    }

    .info-boxes {
        grid-template-columns: repeat(2, 1fr);
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
            <a href="upload_excel.php" class="menu-item active">
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
                <span>Upload Excel/CSV</span>
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
                <h1>📤 Bulk Upload Records</h1>
                <p>Upload multiple students or teachers at once using CSV/Excel files</p>
            </div>

            <!-- Type Selector -->
            <div class="type-selector">
                <a href="upload_excel.php?type=students" class="type-btn <?php echo $type === 'students' ? 'active' : ''; ?>">
                    <i class='bx bxs-graduation'></i> Upload Students
                </a>
                <a href="upload_excel.php?type=teachers" class="type-btn <?php echo $type === 'teachers' ? 'active' : ''; ?>">
                    <i class='bx bxs-user-badge'></i> Upload Teachers
                </a>
            </div>

            <!-- Alert Message -->
            <div id="alertMessage" class="alert"></div>

            <!-- Upload Card -->
            <div class="upload-card">
                <!-- File Preview -->
                <div id="filePreview" class="file-preview">
                    <div class="file-preview-header">
                        <div class="file-name">
                            <i class='bx bx-file'></i>
                            <span id="fileName">No file selected</span>
                        </div>
                        <i class='bx bx-x remove-file' onclick="removeFile()"></i>
                    </div>
                    <div class="file-info">
                        <span id="fileSize">0 KB</span> • 
                        <span id="fileType">-</span>
                    </div>
                </div>

                <!-- Upload Zone -->
                <div class="upload-zone" id="uploadZone" onclick="document.getElementById('fileInput').click()">
                    <i class='bx bx-cloud-upload upload-icon'></i>
                    <h3>Drag & Drop or Click to Upload</h3>
                    <p>Upload CSV or Excel (.xlsx, .xls) file</p>
                    <p class="file-info">Maximum file size: 10MB</p>
                </div>

                <input type="file" id="fileInput" accept=".csv,.xlsx,.xls" onchange="handleFileSelect(event)">

                <!-- Upload Progress -->
                <div id="uploadProgress" class="upload-progress">
                    <div class="progress-bar">
                        <div class="progress-fill" id="progressFill">0%</div>
                    </div>
                    <div class="progress-info" id="progressInfo">Preparing upload...</div>
                </div>

                <!-- Upload Button -->
                <div style="text-align: center; margin-top: 1.5rem;">
                    <button class="btn btn-primary" id="uploadBtn" onclick="uploadFile()" disabled>
                        <i class='bx bx-upload'></i>
                        Upload and Import
                    </button>
                </div>
            </div>

            <!-- Template Section -->
            <div class="template-section">
                <h3>
                    <i class='bx bx-download'></i>
                    Download Sample Template
                </h3>
                <p style="color: #718096; margin-bottom: 1.5rem;">
                    Download our sample template to ensure your data is formatted correctly
                </p>
                <div class="template-buttons">
                    <a href="../templates/<?php echo $type; ?>_template.csv" download class="btn btn-secondary">
                        <i class='bx bx-file'></i>
                        Download CSV Template
                    </a>
                    <a href="../templates/<?php echo $type; ?>_template.xlsx" download class="btn btn-secondary">
                        <i class='bx bx-file-blank'></i>
                        Download Excel Template
                    </a>
                </div>
            </div>

            <!-- Info Boxes -->
            <div class="info-boxes">
                <!-- Format Requirements -->
                <div class="info-box">
                    <h3>
                        <i class='bx bx-info-circle'></i>
                        Format Requirements
                    </h3>
                    <ul>
                        <li><i class='bx bx-check'></i> First row must contain column headers</li>
                        <li><i class='bx bx-check'></i> Use UTF-8 encoding for special characters</li>
                        <li><i class='bx bx-check'></i> Date format: YYYY-MM-DD (e.g., 2024-01-15)</li>
                        <li><i class='bx bx-check'></i> Required fields cannot be empty</li>
                    </ul>
                </div>

                <!-- Required Columns -->
                <div class="info-box">
                    <h3>
                        <i class='bx bx-list-check'></i>
                        <?php echo $type === 'students' ? 'Student' : 'Teacher'; ?> Columns
                    </h3>
                    <ul>
                        <?php if ($type === 'students'): ?>
                            <li><i class='bx bx-check'></i> PRN (Student ID) *</li>
                            <li><i class='bx bx-check'></i> First Name, Last Name *</li>
                            <li><i class='bx bx-check'></i> Email, Phone *</li>
                            <li><i class='bx bx-check'></i> Department, Class, Division *</li>
                            <li><i class='bx bx-check'></i> Date of Birth (DOB) *</li>
                        <?php else: ?>
                            <li><i class='bx bx-check'></i> First Name, Last Name *</li>
                            <li><i class='bx bx-check'></i> Email, Phone *</li>
                            <li><i class='bx bx-check'></i> Department, Designation *</li>
                            <li><i class='bx bx-check'></i> Qualification, Experience *</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Tips -->
                <div class="info-box">
                    <h3>
                        <i class='bx bx-bulb'></i>
                        Upload Tips
                    </h3>
                    <ul>
                        <li><i class='bx bx-check'></i> Validate data before uploading</li>
                        <li><i class='bx bx-check'></i> Check for duplicate emails/PRNs</li>
                        <li><i class='bx bx-check'></i> Remove any merged cells</li>
                        <li><i class='bx bx-check'></i> Test with small file first</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <script>
        const type = '<?php echo $type; ?>';
        let selectedFile = null;

        // Drag and drop handlers
        const uploadZone = document.getElementById('uploadZone');

        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });

        uploadZone.addEventListener('dragleave', () => {
            uploadZone.classList.remove('dragover');
        });

        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFile(files[0]);
            }
        });

        // File input change handler
        function handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                handleFile(file);
            }
        }

        // Process selected file
        function handleFile(file) {
            // Validate file type
            const validTypes = ['.csv', '.xlsx', '.xls'];
            const fileName = file.name.toLowerCase();
            const isValid = validTypes.some(type => fileName.endsWith(type));

            if (!isValid) {
                showAlert('Invalid file type! Please upload CSV or Excel file.', 'danger');
                return;
            }

            // Validate file size (10MB max)
            const maxSize = 10 * 1024 * 1024; // 10MB
            if (file.size > maxSize) {
                showAlert('File too large! Maximum size is 10MB.', 'danger');
                return;
            }

            // Store file and show preview
            selectedFile = file;
            showFilePreview(file);
            
            // Enable upload button
            document.getElementById('uploadBtn').disabled = false;
        }

        // Show file preview
        function showFilePreview(file) {
            const preview = document.getElementById('filePreview');
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');
            const fileType = document.getElementById('fileType');

            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            fileType.textContent = getFileExtension(file.name).toUpperCase();

            preview.classList.add('active');
            uploadZone.style.display = 'none';
        }

        // Remove selected file
        function removeFile() {
            selectedFile = null;
            document.getElementById('filePreview').classList.remove('active');
            document.getElementById('uploadZone').style.display = 'block';
            document.getElementById('fileInput').value = '';
            document.getElementById('uploadBtn').disabled = true;
        }

        // Format file size
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        // Get file extension
        function getFileExtension(filename) {
            return filename.slice((filename.lastIndexOf('.') - 1 >>> 0) + 2);
        }

        // Upload file
        async function uploadFile() {
            if (!selectedFile) {
                showAlert('Please select a file first!', 'danger');
                return;
            }

            // Disable upload button
            const uploadBtn = document.getElementById('uploadBtn');
            const originalText = uploadBtn.innerHTML;
            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Uploading...';

            // Show progress
            const uploadProgress = document.getElementById('uploadProgress');
            const progressFill = document.getElementById('progressFill');
            const progressInfo = document.getElementById('progressInfo');
            uploadProgress.classList.add('active');

            try {
                // Create form data
                const formData = new FormData();
                formData.append('file', selectedFile);
                formData.append('type', type);

                // Upload with progress
                const xhr = new XMLHttpRequest();

                xhr.upload.addEventListener('progress', (e) => {
                    if (e.lengthComputable) {
                        const percentComplete = Math.round((e.loaded / e.total) * 100);
                        progressFill.style.width = percentComplete + '%';
                        progressFill.textContent = percentComplete + '%';
                        progressInfo.textContent = `Uploading... ${percentComplete}%`;
                    }
                });

                xhr.addEventListener('load', () => {
                    if (xhr.status === 200) {
                        try {
                            const data = JSON.parse(xhr.responseText);
                            
                            if (data.success) {
                                progressFill.style.width = '100%';
                                progressFill.textContent = '100%';
                                progressInfo.textContent = 'Upload complete!';
                                
                                showAlert(data.message, 'success');
                                
                                // Reset after 3 seconds
                                setTimeout(() => {
                                    removeFile();
                                    uploadProgress.classList.remove('active');
                                    progressFill.style.width = '0%';
                                    uploadBtn.innerHTML = originalText;
                                    uploadBtn.disabled = false;
                                }, 3000);
                            } else {
                                showAlert(data.message, 'danger');
                                uploadBtn.innerHTML = originalText;
                                uploadBtn.disabled = false;
                                uploadProgress.classList.remove('active');
                            }
                        } catch (e) {
                            console.error('Parse error:', e);
                            showAlert('Server returned invalid response', 'danger');
                            uploadBtn.innerHTML = originalText;
                            uploadBtn.disabled = false;
                            uploadProgress.classList.remove('active');
                        }
                    } else {
                        showAlert('Upload failed! Server error.', 'danger');
                        uploadBtn.innerHTML = originalText;
                        uploadBtn.disabled = false;
                        uploadProgress.classList.remove('active');
                    }
                });

                xhr.addEventListener('error', () => {
                    showAlert('Upload failed! Network error.', 'danger');
                    uploadBtn.innerHTML = originalText;
                    uploadBtn.disabled = false;
                    uploadProgress.classList.remove('active');
                });

                xhr.open('POST', '../php/upload_handler.php');
                xhr.send(formData);

            } catch (error) {
                console.error('Error:', error);
                showAlert('An error occurred. Please try again.', 'danger');
                uploadBtn.innerHTML = originalText;
                uploadBtn.disabled = false;
                uploadProgress.classList.remove('active');
            }
        }

        // Show alert
        function showAlert(message, type) {
            const alert = document.getElementById('alertMessage');
            alert.textContent = message;
            alert.className = `alert alert-${type} active`;
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                alert.classList.remove('active');
            }, 5000);

            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    </script>
</body>
</html>