<?php
session_start();
require_once '../php/db_connect.php';
require_once '../php/email_config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$adminId = $_SESSION['admin_id'];
$adminName = $_SESSION['admin_name'] ?? 'Admin';

// Get pending approvals
$pendingQuery = "SELECT f.id, f.full_name, f.email, f.phone, f.department, f.designation, 
                        f.created_at, f.approval_status, f.email_verified,
                        t.id as teacher_id, t.first_name, t.middle_name, t.last_name
                 FROM faculty f
                 LEFT JOIN teachers t ON f.teacher_id = t.id
                 WHERE f.approval_status = 'pending' AND f.email_verified = 1
                 ORDER BY f.created_at DESC";

$pendingResult = $conn->query($pendingQuery);
$pendingFaculty = [];
if ($pendingResult) {
    while ($row = $pendingResult->fetch_assoc()) {
        $pendingFaculty[] = $row;
    }
}

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $facultyId = intval($_POST['faculty_id'] ?? 0);
    $action = $_POST['action'];
    $reason = sanitizeInput($_POST['reason'] ?? '');
    
    if ($facultyId > 0) {
        if ($action === 'approve') {
            // Approve faculty
            $updateQuery = "UPDATE faculty 
                           SET approval_status = 'approved', 
                               approved_by = ?, 
                               approved_at = NOW() 
                           WHERE id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("ii", $adminId, $facultyId);
            $result = $updateStmt->execute();
            
            if ($result) {
                // Get faculty details
                $facultyQuery = "SELECT full_name, email FROM faculty WHERE id = ?";
                $facultyStmt = $conn->prepare($facultyQuery);
                $facultyStmt->bind_param("i", $facultyId);
                $facultyStmt->execute();
                $facultyResult = $facultyStmt->get_result();
                
                if ($facultyResult && $facultyResult->num_rows > 0) {
                    $faculty = $facultyResult->fetch_assoc();
                    
                    // Send approval email
                    sendFacultyApprovedEmail($faculty['email'], $faculty['full_name']);
                    
                    // Update approval request
                    $requestUpdate = "UPDATE faculty_approval_requests 
                                     SET status = 'approved', 
                                         responded_by = ?, 
                                         responded_at = NOW(), 
                                         admin_response = 'Approved' 
                                     WHERE faculty_id = ? AND status = 'pending'";
                    $requestStmt = $conn->prepare($requestUpdate);
                    $requestStmt->bind_param("ii", $adminId, $facultyId);
                    $requestStmt->execute();
                    
                    // Log activity
                    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
                    $logQuery = "INSERT INTO activity_logs (admin_id, action, details, ip_address) 
                                VALUES (?, 'FACULTY_APPROVED', ?, ?)";
                    $logStmt = $conn->prepare($logQuery);
                    $logDetails = "Approved faculty: {$faculty['full_name']} ({$faculty['email']})";
                    $logStmt->bind_param("iss", $adminId, $logDetails, $ipAddress);
                    $logStmt->execute();
                }
                
                $_SESSION['success_message'] = 'Faculty approved successfully!';
            }
        } elseif ($action === 'reject') {
            // Validate reason
            if (strlen($reason) < 10) {
                $_SESSION['error_message'] = 'Rejection reason must be at least 10 characters long.';
                header("Location: approve_faculty.php");
                exit();
            }
            
            // Reject faculty
            $updateQuery = "UPDATE faculty 
                           SET approval_status = 'rejected', 
                               approved_by = ?, 
                               approved_at = NOW(), 
                               rejection_reason = ? 
                           WHERE id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("isi", $adminId, $reason, $facultyId);
            $result = $updateStmt->execute();
            
            if ($result) {
                // Get faculty details
                $facultyQuery = "SELECT full_name, email FROM faculty WHERE id = ?";
                $facultyStmt = $conn->prepare($facultyQuery);
                $facultyStmt->bind_param("i", $facultyId);
                $facultyStmt->execute();
                $facultyResult = $facultyStmt->get_result();
                
                if ($facultyResult && $facultyResult->num_rows > 0) {
                    $faculty = $facultyResult->fetch_assoc();
                    
                    // Send rejection email
                    sendFacultyRejectedEmail($faculty['email'], $faculty['full_name'], $reason);
                    
                    // Update approval request
                    $requestUpdate = "UPDATE faculty_approval_requests 
                                     SET status = 'rejected', 
                                         responded_by = ?, 
                                         responded_at = NOW(), 
                                         admin_response = ? 
                                     WHERE faculty_id = ? AND status = 'pending'";
                    $requestStmt = $conn->prepare($requestUpdate);
                    $requestStmt->bind_param("isi", $adminId, $reason, $facultyId);
                    $requestStmt->execute();
                    
                    // Log activity
                    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
                    $logQuery = "INSERT INTO activity_logs (admin_id, action, details, ip_address) 
                                VALUES (?, 'FACULTY_REJECTED', ?, ?)";
                    $logStmt = $conn->prepare($logQuery);
                    $logDetails = "Rejected faculty: {$faculty['full_name']} ({$faculty['email']}) - Reason: $reason";
                    $logStmt->bind_param("iss", $adminId, $logDetails, $ipAddress);
                    $logStmt->execute();
                }
                
                $_SESSION['success_message'] = 'Faculty rejected successfully!';
            }
        }
        
        header("Location: approve_faculty.php");
        exit();
    }
}

// Get statistics
$approvedQuery = "SELECT COUNT(*) as count FROM faculty WHERE approval_status = 'approved'";
$approvedResult = $conn->query($approvedQuery);
$approvedCount = $approvedResult ? $approvedResult->fetch_assoc()['count'] : 0;

$rejectedQuery = "SELECT COUNT(*) as count FROM faculty WHERE approval_status = 'rejected'";
$rejectedResult = $conn->query($rejectedQuery);
$rejectedCount = $rejectedResult ? $rejectedResult->fetch_assoc()['count'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Faculty - MITAOE Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #4EA685;
            --secondary: #57B894;
            --dark: #2c3e50;
            --white: #ffffff;
            --gray: #6c757d;
            --gray-light: #e9ecef;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 25px 30px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark);
        }

        .back-btn {
            padding: 12px 28px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .back-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(78, 166, 133, 0.4);
        }

        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-weight: 600;
            animation: slideDown 0.3s ease;
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

        .alert-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .alert-error {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-left: 5px solid var(--primary);
        }

        .stat-card h3 {
            font-size: 14px;
            color: var(--gray);
            text-transform: uppercase;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .stat-card .number {
            font-size: 36px;
            font-weight: 800;
            color: var(--primary);
        }

        .pending-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .section-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid var(--gray-light);
        }

        .faculty-card {
            background: var(--gray-light);
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-left: 5px solid var(--warning);
        }

        .faculty-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .faculty-info h3 {
            font-size: 20px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .faculty-info p {
            font-size: 14px;
            color: var(--gray);
            margin: 4px 0;
        }

        .faculty-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .detail-item {
            font-size: 14px;
        }

        .detail-label {
            font-weight: 600;
            color: var(--gray);
            margin-bottom: 4px;
        }

        .detail-value {
            color: var(--dark);
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 28px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-approve {
            background: linear-gradient(135deg, var(--success), #20c997);
            color: white;
        }

        .btn-approve:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
        }

        .btn-reject {
            background: linear-gradient(135deg, var(--danger), #c82333);
            color: white;
        }

        .btn-reject:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 40px;
            border-radius: 20px;
            max-width: 500px;
            width: 100%;
            animation: scaleIn 0.3s ease;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0.8);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .modal h3 {
            font-size: 24px;
            margin-bottom: 20px;
            color: var(--dark);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--gray-light);
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .modal-buttons {
            display: flex;
            gap: 15px;
            margin-top: 25px;
            flex-wrap: wrap;
        }

        .btn-cancel {
            background: var(--gray-light);
            color: var(--dark);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            font-size: 80px;
            color: var(--gray-light);
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 24px;
            color: var(--gray);
            margin-bottom: 10px;
        }

        .empty-state p {
            color: var(--gray);
        }

        @media (max-width: 768px) {
            .header {
                padding: 20px;
            }

            .header h1 {
                font-size: 22px;
            }

            .stat-card {
                padding: 20px;
            }

            .stat-card .number {
                font-size: 28px;
            }

            .pending-section {
                padding: 20px;
            }

            .faculty-card {
                padding: 20px;
            }

            .modal-content {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>👥 Approve Faculty Accounts</h1>
            <a href="admin_dashboard.php" class="back-btn">
                <i class='bx bx-arrow-back'></i>
                <span>Back to Dashboard</span>
            </a>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                ✓ <?php echo htmlspecialchars($_SESSION['success_message']); ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error">
                ✕ <?php echo htmlspecialchars($_SESSION['error_message']); ?>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Pending Approvals</h3>
                <div class="number"><?php echo count($pendingFaculty); ?></div>
            </div>
            <div class="stat-card" style="border-left-color: var(--success);">
                <h3>Total Approved</h3>
                <div class="number" style="color: var(--success);"><?php echo $approvedCount; ?></div>
            </div>
            <div class="stat-card" style="border-left-color: var(--danger);">
                <h3>Total Rejected</h3>
                <div class="number" style="color: var(--danger);"><?php echo $rejectedCount; ?></div>
            </div>
        </div>

        <!-- Pending Approvals -->
        <div class="pending-section">
            <h2 class="section-title">Pending Faculty Approvals</h2>

            <?php if (empty($pendingFaculty)): ?>
                <div class="empty-state">
                    <i class='bx bx-check-circle'></i>
                    <h3>No Pending Approvals</h3>
                    <p>All faculty accounts have been reviewed</p>
                </div>
            <?php else: ?>
                <?php foreach ($pendingFaculty as $faculty): ?>
                    <div class="faculty-card">
                        <div class="faculty-header">
                            <div class="faculty-info">
                                <h3><?php echo htmlspecialchars($faculty['full_name']); ?></h3>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($faculty['email']); ?></p>
                                <p><strong>Registration Date:</strong> <?php echo date('F d, Y h:i A', strtotime($faculty['created_at'])); ?></p>
                            </div>
                        </div>

                        <div class="faculty-details">
                            <div class="detail-item">
                                <div class="detail-label">Phone</div>
                                <div class="detail-value"><?php echo htmlspecialchars($faculty['phone'] ?? 'Not provided'); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Department</div>
                                <div class="detail-value"><?php echo htmlspecialchars($faculty['department'] ?? 'Not provided'); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Designation</div>
                                <div class="detail-value"><?php echo htmlspecialchars($faculty['designation'] ?? 'Not provided'); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Email Verified</div>
                                <div class="detail-value">
                                    <?php echo $faculty['email_verified'] ? '✓ Yes' : '✗ No'; ?>
                                </div>
                            </div>
                            <?php if ($faculty['teacher_id']): ?>
                                <div class="detail-item">
                                    <div class="detail-label">Linked Teacher</div>
                                    <div class="detail-value">
                                        <?php 
                                        $teacherName = trim($faculty['first_name'] . ' ' . ($faculty['middle_name'] ?? '') . ' ' . $faculty['last_name']);
                                        echo htmlspecialchars($teacherName);
                                        ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="action-buttons">
                            <button class="btn btn-approve" onclick="approveFaculty(<?php echo $faculty['id']; ?>, '<?php echo htmlspecialchars($faculty['full_name'], ENT_QUOTES); ?>')">
                                <i class='bx bx-check'></i>
                                <span>Approve</span>
                            </button>
                            <button class="btn btn-reject" onclick="showRejectModal(<?php echo $faculty['id']; ?>, '<?php echo htmlspecialchars($faculty['full_name'], ENT_QUOTES); ?>')">
                                <i class='bx bx-x'></i>
                                <span>Reject</span>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="modal">
        <div class="modal-content">
            <h3>Reject Faculty Account</h3>
            <form id="rejectForm" method="POST">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="faculty_id" id="rejectFacultyId">
                
                <div class="form-group">
                    <label>Faculty Name</label>
                    <input type="text" class="form-control" id="rejectFacultyName" readonly>
                </div>

                <div class="form-group">
                    <label>Reason for Rejection <span style="color: var(--danger);">*</span></label>
                    <textarea class="form-control" name="reason" id="rejectionReason" placeholder="Please provide a clear reason for rejection (minimum 10 characters)..." required></textarea>
                </div>

                <div class="modal-buttons">
                    <button type="button" class="btn btn-cancel" onclick="closeRejectModal()">Cancel</button>
                    <button type="submit" class="btn btn-reject">
                        <i class='bx bx-x'></i>
                        <span>Reject Account</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function approveFaculty(id, name) {
            if (confirm(`Are you sure you want to APPROVE the faculty account for "${name}"?\n\nThey will receive an email notification and will be able to login.`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="faculty_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function showRejectModal(id, name) {
            document.getElementById('rejectFacultyId').value = id;
            document.getElementById('rejectFacultyName').value = name;
            document.getElementById('rejectionReason').value = '';
            document.getElementById('rejectModal').classList.add('active');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.remove('active');
        }

        // Close modal on outside click
        document.getElementById('rejectModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRejectModal();
            }
        });

        // Form validation
        document.getElementById('rejectForm').addEventListener('submit', function(e) {
            const reason = document.getElementById('rejectionReason').value.trim();
            if (reason.length < 10) {
                e.preventDefault();
                alert('Please provide a detailed reason (at least 10 characters)');
                return false;
            }
        });
    </script>
</body>
</html>