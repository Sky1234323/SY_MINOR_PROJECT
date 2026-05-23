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

if (!in_array($type, ['student', 'teacher', 'faculty']) || $id <= 0) {
    header("Location: view_all.php?type=students");
    exit();
}

// Get record details
if ($type === 'student') {
    $query = "SELECT * FROM students WHERE id = ?";
    $title = "Student Details";
    $icon = "bxs-graduation";
} elseif ($type === 'teacher') {
    $query = "SELECT * FROM teachers WHERE id = ?";
    $title = "Teacher Details";
    $icon = "bxs-user-badge";
} else {
    $query = "SELECT * FROM faculty WHERE id = ?";
    $title = "Faculty Details";
    $icon = "bxs-group";
}

$result = executeQuery($query, "i", array($id));

if (!$result || $result->num_rows === 0) {
    header("Location: view_all.php?type=" . $type . "s");
    exit();
}

$record = $result->fetch_assoc();
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

        /* Sidebar - Same as other pages */
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
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Profile Header */
        .profile-header {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .profile-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #4EA685, #57B894);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .profile-icon i {
            font-size: 4rem;
            color: white;
        }

        .profile-info {
            flex: 1;
        }

        .profile-name {
            font-size: 2rem;
            font-weight: 800;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .profile-meta {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #718096;
            font-size: 0.9375rem;
        }

        .meta-item i {
            color: #4EA685;
            font-size: 1.25rem;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.875rem;
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

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
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

        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            box-shadow: 0 4px 12px rgba(239,68,68,0.3);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239,68,68,0.4);
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #2c3e50;
        }

        .btn-secondary:hover {
            background: #d1d5db;
        }

        /* Details Grid */
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .detail-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
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

        .detail-item {
            margin-bottom: 1.25rem;
        }

        .detail-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #718096;
            margin-bottom: 0.25rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .detail-value {
            font-size: 1rem;
            color: #2c3e50;
            font-weight: 500;
        }

        .detail-value.empty {
            color: #a0aec0;
            font-style: italic;
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

    /* Details grid - 2 columns on tablet */
    .details-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
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

    /* Profile header - vertical on mobile */
    .profile-header {
        flex-direction: column;
        text-align: center;
        padding: 1.5rem;
        gap: 1.5rem;
        border-radius: 15px;
    }

    .profile-icon {
        width: 100px;
        height: 100px;
    }

    .profile-icon i {
        font-size: 3rem;
    }

    .profile-name {
        font-size: 1.5rem;
    }

    .profile-meta {
        justify-content: center;
        gap: 1rem;
    }

    .meta-item {
        font-size: 0.875rem;
    }

    .meta-item i {
        font-size: 1.125rem;
    }

    /* Action buttons - vertical */
    .action-buttons {
        flex-direction: column;
        gap: 0.75rem;
        margin-top: 1.25rem;
    }

    .btn {
        width: 100%;
        justify-content: center;
        padding: 0.75rem 1.25rem;
        font-size: 0.875rem;
    }

    /* Details grid - single column */
    .details-grid {
        grid-template-columns: 1fr;
        gap: 1.25rem;
    }

    .detail-section {
        padding: 1.5rem;
        border-radius: 15px;
    }

    .section-title {
        font-size: 1.125rem;
        margin-bottom: 1.25rem;
        padding-bottom: 0.625rem;
    }

    .section-title i {
        font-size: 1.25rem;
    }

    .detail-item {
        margin-bottom: 1rem;
    }

    .detail-label {
        font-size: 0.75rem;
        margin-bottom: 0.375rem;
    }

    .detail-value {
        font-size: 0.9375rem;
    }
}

/* Mobile Medium - 480px and below */
@media (max-width: 480px) {
    .page-content {
        padding: 1rem 0.75rem;
    }

    .profile-header {
        padding: 1.25rem;
    }

    .profile-icon {
        width: 90px;
        height: 90px;
    }

    .profile-icon i {
        font-size: 2.5rem;
    }

    .profile-name {
        font-size: 1.375rem;
    }

    .profile-meta {
        flex-direction: column;
        gap: 0.75rem;
    }

    .meta-item {
        font-size: 0.8125rem;
        justify-content: center;
    }

    .status-badge {
        padding: 0.375rem 0.875rem;
        font-size: 0.8125rem;
    }

    .btn {
        padding: 0.625rem 1rem;
        font-size: 0.8125rem;
        gap: 0.5rem;
    }

    .btn i {
        font-size: 1rem;
    }

    .detail-section {
        padding: 1.25rem;
    }

    .section-title {
        font-size: 1rem;
    }

    .detail-label {
        font-size: 0.6875rem;
    }

    .detail-value {
        font-size: 0.875rem;
    }
}

/* Mobile Small - 360px and below */
@media (max-width: 360px) {
    .profile-header {
        padding: 1rem;
    }

    .profile-icon {
        width: 80px;
        height: 80px;
    }

    .profile-icon i {
        font-size: 2.25rem;
    }

    .profile-name {
        font-size: 1.25rem;
    }

    .detail-section {
        padding: 1rem;
    }

    .section-title {
        font-size: 0.9375rem;
    }

    .btn {
        padding: 0.5rem 0.875rem;
        font-size: 0.75rem;
    }
}

/* Landscape mobile */
@media (max-height: 500px) and (orientation: landscape) {
    .profile-header {
        flex-direction: row;
        text-align: left;
        padding: 1.25rem;
    }

    .profile-icon {
        width: 80px;
        height: 80px;
    }

    .action-buttons {
        flex-direction: row;
        flex-wrap: wrap;
    }

    .details-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
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
                <span><?php echo $title; ?></span>
            </div>
            <div class="header-right">
                <a href="view_all.php?type=<?php echo $type; ?>s" class="back-btn">
                    <i class='bx bx-arrow-back'></i>
                    Back to List
                </a>
            </div>
        </header>

        <!-- Page Content -->
        <div class="page-content">
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-icon">
                    <i class='bx <?php echo $icon; ?>'></i>
                </div>
                <div class="profile-info">
                    <h1 class="profile-name">
                        <?php 
                        if ($type === 'student' || $type === 'teacher') {
                            echo htmlspecialchars($record['first_name'] . ' ' . $record['middle_name'] . ' ' . $record['last_name']);
                        } else {
                            echo htmlspecialchars($record['full_name']);
                        }
                        ?>
                    </h1>
                    <div class="profile-meta">
                        <?php if ($type === 'student'): ?>
                            <div class="meta-item">
                                <i class='bx bx-id-card'></i>
                                <span>PRN: <strong><?php echo htmlspecialchars($record['prn']); ?></strong></span>
                            </div>
                        <?php endif; ?>
                        <div class="meta-item">
                            <i class='bx bx-envelope'></i>
                            <span><?php echo htmlspecialchars($record['email']); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class='bx bx-phone'></i>
                            <span><?php echo htmlspecialchars($record['phone']); ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="status-badge <?php echo $record['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo $record['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>
                    </div>
                    <div class="action-buttons">
                        <a href="edit_record.php?type=<?php echo $type; ?>&id=<?php echo $record['id']; ?>" class="btn btn-primary">
                            <i class='bx bx-edit'></i>
                            Edit Details
                        </a>
                        <button onclick="deleteRecord()" class="btn btn-danger">
                            <i class='bx bx-trash'></i>
                            Delete Record
                        </button>
                        <a href="view_all.php?type=<?php echo $type; ?>s" class="btn btn-secondary">
                            <i class='bx bx-list-ul'></i>
                            Back to List
                        </a>
                    </div>
                </div>
            </div>

            <!-- Details Grid -->
            <div class="details-grid">
                <?php if ($type === 'student'): ?>
                    <!-- Basic Information -->
                    <div class="detail-section">
                        <div class="section-title">
                            <i class='bx bx-user'></i>
                            Basic Information
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">PRN (Student ID)</div>
                            <div class="detail-value"><?php echo htmlspecialchars($record['prn']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">First Name</div>
                            <div class="detail-value"><?php echo htmlspecialchars($record['first_name']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Middle Name</div>
                            <div class="detail-value <?php echo empty($record['middle_name']) ? 'empty' : ''; ?>">
                                <?php echo !empty($record['middle_name']) ? htmlspecialchars($record['middle_name']) : 'Not provided'; ?>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Last Name</div>
                            <div class="detail-value"><?php echo htmlspecialchars($record['last_name']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Date of Birth</div>
                            <div class="detail-value"><?php echo date('F d, Y', strtotime($record['dob'])); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Gender</div>
                            <div class="detail-value"><?php echo htmlspecialchars($record['gender']); ?></div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="detail-section">
                        <div class="section-title">
                            <i class='bx bx-phone'></i>
                            Contact Information
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Email Address</div>
                            <div class="detail-value"><?php echo htmlspecialchars($record['email']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Phone Number</div>
                            <div class="detail-value"><?php echo htmlspecialchars($record['phone']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Address</div>
                            <div class="detail-value <?php echo empty($record['address']) ? 'empty' : ''; ?>">
                                <?php echo !empty($record['address']) ? htmlspecialchars($record['address']) : 'Not provided'; ?>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">City</div>
                            <div class="detail-value <?php echo empty($record['city']) ? 'empty' : ''; ?>">
                                <?php echo !empty($record['city']) ? htmlspecialchars($record['city']) : 'Not provided'; ?>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">State</div>
                            <div class="detail-value <?php echo empty($record['state']) ? 'empty' : ''; ?>">
                                <?php echo !empty($record['state']) ? htmlspecialchars($record['state']) : 'Not provided'; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Academic Information -->
                    <div class="detail-section">
                        <div class="section-title">
                            <i class='bx bx-book'></i>
                            Academic Information
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Department</div>
                            <div class="detail-value"><?php echo htmlspecialchars($record['department']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Class</div>
                            <div class="detail-value"><?php echo htmlspecialchars($record['class']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Division</div>
                            <div class="detail-value"><?php echo htmlspecialchars($record['division']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Roll Number</div>
                            <div class="detail-value"><?php echo htmlspecialchars($record['roll_number']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Batch Year</div>
                            <div class="detail-value"><?php echo htmlspecialchars($record['batch_year']); ?></div>
                        </div>
                    </div>

                    <!-- Parent Information -->
                    <div class="detail-section">
                        <div class="section-title">
                            <i class='bx bx-user-circle'></i>
                            Parent/Guardian Information
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Parent Name</div>
                            <div class="detail-value <?php echo empty($record['parent_name']) ? 'empty' : ''; ?>">
                                <?php echo !empty($record['parent_name']) ? htmlspecialchars($record['parent_name']) : 'Not provided'; ?>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Parent Phone</div>
                            <div class="detail-value <?php echo empty($record['parent_phone']) ? 'empty' : ''; ?>">
                                <?php echo !empty($record['parent_phone']) ? htmlspecialchars($record['parent_phone']) : 'Not provided'; ?>
                            </div>
                        </div>
                    </div>

                <?php elseif ($type === 'teacher'): ?>
                    <!-- Basic Information -->
                    <div class="detail-section">
                        <div class="section-title">
                            <i class='bx bx-user'></i>
                            Basic Information
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">First Name</div>
                            <div class="detail-value"><?php echo htmlspecialchars($record['first_name']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Middle Name</div>
                            <div class="detail-value <?php echo empty($record['middle_name']) ? 'empty' : ''; ?>">
                                <?php echo !empty($record['middle_name']) ? htmlspecialchars($record['middle_name']) : 'Not provided'; ?>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Last Name</div>
                            <div class="detail-value"><?php echo htmlspecialchars($record['last_name']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Gender</div>
                            <div class="detail-value"><?php echo htmlspecialchars($record['gender']); ?></div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="detail-section">
                        <div class="section-title">
                            <i class='bx bx-phone'></i>
                            Contact Information
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Email Address</div>
                            <div class="detail-value"><?php echo htmlspecialchars($record['email']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Phone Number</div>
                            <div class="detail-value"><?php echo htmlspecialchars($record['phone']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Address</div>
                            <div class="detail-value <?php echo empty($record['address']) ? 'empty' : ''; ?>">
                                <?php echo !empty($record['address']) ? htmlspecialchars($record['address']) : 'Not provided'; ?>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">City</div>
                            <div class="detail-value <?php echo empty($record['city']) ? 'empty' : ''; ?>">
                                <?php echo !empty($record['city']) ? htmlspecialchars($record['city']) : 'Not provided'; ?>
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">State</div>
                            <div class="detail-value <?php echo empty($record['state']) ? 'empty' : ''; ?>">
                                <?php echo !empty($record['state']) ? htmlspecialchars($record['state']) : 'Not provided'; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Professional Information -->
                    <div class="detail-section">
                        <div class="section-title">
                            <i class='bx bx-briefcase'></i>
                            Professional Information
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Department</div>
                            <div class="detail-value"><?php echo htmlspecialchars($record['department']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Designation</div>
                            <div class="detail-value"><?php echo htmlspecialchars($record['designation']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Qualification</div>
                            <div class="detail-value"><?php echo htmlspecialchars($record['qualification']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Experience</div>
                            <div class="detail-value"><?php echo htmlspecialchars($record['experience']); ?> years</div>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Faculty Information -->
                    <div class="detail-section">
                        <div class="section-title">
                            <i class='bx bx-user'></i>
                            Faculty Information
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Full Name</div>
                            <div class="detail-value"><?php echo htmlspecialchars($record['full_name']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Email</div>
                            <div class="detail-value"><?php echo htmlspecialchars($record['email']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Phone</div>
                            <div class="detail-value"><?php echo htmlspecialchars($record['phone']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Department</div>
                            <div class="detail-value"><?php echo htmlspecialchars($record['department']); ?></div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- System Information -->
                <div class="detail-section">
                    <div class="section-title">
                        <i class='bx bx-info-circle'></i>
                        System Information
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Record ID</div>
                        <div class="detail-value"><?php echo $record['id']; ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Account Status</div>
                        <div class="detail-value">
                            <span class="status-badge <?php echo $record['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo $record['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Created At</div>
                        <div class="detail-value"><?php echo date('F d, Y h:i A', strtotime($record['created_at'])); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Last Updated</div>
                        <div class="detail-value"><?php echo date('F d, Y h:i A', strtotime($record['updated_at'])); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function deleteRecord() {
            const type = '<?php echo $type; ?>';
            const id = <?php echo $record['id']; ?>;
            const identifier = '<?php echo $type === 'student' ? htmlspecialchars($record['prn'], ENT_QUOTES) : htmlspecialchars($record['email'], ENT_QUOTES); ?>';

            if (confirm(`Are you sure you want to delete this ${type}?\n\n${identifier}\n\nThis action cannot be undone!`)) {
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
                        window.location.href = `view_all.php?type=${type}s`;
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