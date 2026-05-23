<?php
session_start();
require_once '../php/db_connect.php';
require_once '../php/email_config.php';

$token = $_GET['token'] ?? '';
$message = '';
$messageType = '';

if (empty($token)) {
    $message = 'Invalid verification link.';
    $messageType = 'error';
} else {
    // Check if token exists and is valid
    $query = "SELECT id, full_name, email, phone, department, designation, email_verified, approval_status, email_token_expiry, created_at
              FROM faculty 
              WHERE email_verification_token = ? 
              AND email_verified = 0";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $faculty = $result->fetch_assoc();
        
        // Check if token has expired
        if (strtotime($faculty['email_token_expiry']) < time()) {
            $message = 'Verification link has expired. Please register again.';
            $messageType = 'error';
        } else {
            // Mark email as verified
            $updateQuery = "UPDATE faculty 
                           SET email_verified = 1, 
                               email_verification_token = NULL, 
                               email_token_expiry = NULL 
                           WHERE id = ?";
            
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("i", $faculty['id']);
            
            if ($updateStmt->execute()) {
                // Prepare faculty data for admin notification
                $facultyData = array(
                    'id' => $faculty['id'],
                    'full_name' => $faculty['full_name'],
                    'email' => $faculty['email'],
                    'phone' => $faculty['phone'] ?? '',
                    'department' => $faculty['department'] ?? '',
                    'designation' => $faculty['designation'] ?? '',
                    'created_at' => $faculty['created_at']
                );
                
                // Send admin approval notification
                sendAdminApprovalNotification($facultyData);
                
                // Create approval request record
                $requestQuery = "INSERT INTO faculty_approval_requests 
                                (faculty_id, full_name, email, phone, department, designation, status) 
                                VALUES (?, ?, ?, ?, ?, ?, 'pending')";
                $requestStmt = $conn->prepare($requestQuery);
                $requestStmt->bind_param("isssss", 
                    $faculty['id'],
                    $faculty['full_name'],
                    $faculty['email'],
                    $facultyData['phone'],
                    $facultyData['department'],
                    $facultyData['designation']
                );
                $requestStmt->execute();
                
                // Log activity
                $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
                $logQuery = "INSERT INTO activity_logs (admin_id, action, details, ip_address) 
                            VALUES (0, 'EMAIL_VERIFIED', ?, ?)";
                $logStmt = $conn->prepare($logQuery);
                $logDetails = "Faculty email verified: {$faculty['full_name']} ({$faculty['email']})";
                $logStmt->bind_param("ss", $logDetails, $ipAddress);
                $logStmt->execute();
                
                $message = 'Email verified successfully! Your account is now pending admin approval. You will receive an email once approved.';
                $messageType = 'success';
            } else {
                $message = 'Verification failed. Please try again.';
                $messageType = 'error';
            }
        }
    } else {
        $message = 'Invalid or already verified link.';
        $messageType = 'error';
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - MITAOE Faculty Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #4EA685;
            --secondary: #57B894;
            --success: #28a745;
            --danger: #dc3545;
            --dark: #1a1a2e;
            --white: #ffffff;
            --gray: #6c757d;
            --gray-light: #e9ecef;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            width: 100%;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            animation: fadeIn 0.6s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 40px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .content {
            padding: 50px 40px;
            text-align: center;
        }

        .icon {
            width: 100px;
            height: 100px;
            margin: 0 auto 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
            animation: scaleIn 0.5s ease 0.3s backwards;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }

        .icon.success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .icon.error {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }

        .message {
            font-size: 18px;
            color: var(--dark);
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .btn {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(78, 166, 133, 0.3);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(78, 166, 133, 0.4);
        }

        .footer {
            padding: 20px;
            text-align: center;
            font-size: 13px;
            color: var(--gray);
            border-top: 1px solid var(--gray-light);
        }

        @media (max-width: 768px) {
            .header {
                padding: 30px 20px;
            }

            .header h1 {
                font-size: 24px;
            }

            .content {
                padding: 40px 30px;
            }

            .icon {
                width: 80px;
                height: 80px;
                font-size: 40px;
            }

            .message {
                font-size: 16px;
            }

            .btn {
                padding: 12px 30px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎓 MITAOE Faculty Portal</h1>
            <p>Email Verification</p>
        </div>
        
        <div class="content">
            <?php if ($messageType === 'success'): ?>
                <div class="icon success">✓</div>
                <p class="message"><?php echo htmlspecialchars($message); ?></p>
                <a href="faculty_auth.php" class="btn">Go to Login</a>
            <?php else: ?>
                <div class="icon error">✕</div>
                <p class="message"><?php echo htmlspecialchars($message); ?></p>
                <a href="faculty_auth.php" class="btn">Back to Login</a>
            <?php endif; ?>
        </div>
        
        <div class="footer">
            <p>&copy; 2024 MITAOE Student Information Portal. All rights reserved.</p>
        </div>
    </div>
</body>
</html>