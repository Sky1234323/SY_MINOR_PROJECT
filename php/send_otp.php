<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Set to 1 for debugging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db_connect.php';

// Check if PHPMailer exists
if (file_exists(__DIR__ . '/../PHPMailer/src/PHPMailer.php')) {
    require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
    require_once __DIR__ . '/../PHPMailer/src/Exception.php';
} else {
    error_log("PHPMailer not found at: " . __DIR__ . '/../PHPMailer/src/');
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    // Validate email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please enter a valid email address!';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    try {
        // Check if email exists in faculty table
        $query = "SELECT id, full_name FROM faculty WHERE email = ? AND is_active = 1";
        $result = executeQuery($query, "s", array($email));

        if (!$result || $result->num_rows === 0) {
            $response['message'] = 'No account found with this email address!';
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }

        $faculty = $result->fetch_assoc();
        $faculty_id = $faculty['id'];
        $faculty_name = $faculty['full_name'];

        // Generate 6-digit OTP
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        // Delete old OTPs
        $deleteQuery = "DELETE FROM password_resets WHERE email = ? AND user_type = 'faculty'";
        executeUpdate($deleteQuery, "s", array($email));

        // Store new OTP
        $insertQuery = "INSERT INTO password_resets (email, token, user_type, expires_at, used) 
                        VALUES (?, ?, 'faculty', ?, 0)";
        $insertResult = executeUpdate($insertQuery, "sss", array($email, $otp, $expires_at));

        if (!$insertResult) {
            $response['message'] = 'Failed to generate OTP. Please try again!';
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }

        // Send email using PHPMailer
        $mail = new PHPMailer(true);

        try {
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            
            // 🔴🔴🔴 CHANGE THESE TWO LINES WITH YOUR EMAIL AND APP PASSWORD 🔴🔴🔴
            $mail->Username = 'visionxstudio2006@gmail.com';      // ← Your Gmail address
            $mail->Password = 'hhph ylqj bfpq uyxt';     // ← Your 16-char app password
            // 🔴🔴🔴 CHANGE THESE TWO LINES WITH YOUR EMAIL AND APP PASSWORD 🔴🔴🔴
            
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->SMTPDebug = 0; // Set to 2 for debugging

            // Email settings
            $mail->setFrom('visionxstudio2006@gmail.com', 'VisionX Studio'); // ← Same as Username
            $mail->addAddress($email, $faculty_name);
            $mail->Subject = 'Password Reset OTP - MITAOE Portal';
            $mail->isHTML(true);

            // Email HTML body
            $mail->Body = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; background: #f8f9fa; }
                    .header { background: linear-gradient(135deg, #4EA685 0%, #57B894 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { background: white; padding: 30px; }
                    .otp-box { background: #f8f9fa; border: 2px dashed #4EA685; padding: 20px; text-align: center; margin: 20px 0; border-radius: 10px; }
                    .otp-code { font-size: 36px; font-weight: bold; color: #4EA685; letter-spacing: 5px; }
                    .footer { text-align: center; padding: 20px; color: #6c757d; font-size: 12px; }
                    .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>🔐 Password Reset Request</h1>
                    </div>
                    <div class='content'>
                        <p>Hello <strong>$faculty_name</strong>,</p>
                        <p>We received a request to reset your password for your MITAOE Faculty Portal account.</p>
                        <p>Your One-Time Password (OTP) is:</p>
                        
                        <div class='otp-box'>
                            <div class='otp-code'>$otp</div>
                            <p style='margin: 10px 0 0 0; color: #6c757d;'>Valid for 10 minutes</p>
                        </div>

                        <p>Please enter this OTP on the verification page to proceed with resetting your password.</p>

                        <div class='warning'>
                            <strong>⚠️ Security Notice:</strong><br>
                            • If you didn't request this password reset, please ignore this email.<br>
                            • Never share your OTP with anyone.<br>
                            • This OTP will expire in 10 minutes.
                        </div>

                        <p>Best regards,<br><strong>MITAOE IT Team</strong></p>
                    </div>
                    <div class='footer'>
                        <p>This is an automated email. Please do not reply to this message.</p>
                        <p>&copy; " . date('Y') . " MITAOE. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>
            ";

            $mail->send();

            // Log activity
            $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'Unknown';
            $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown';
            $logQuery = "INSERT INTO activity_logs (admin_id, action, details, ip_address, user_agent) 
                         VALUES (?, 'PASSWORD_RESET_OTP_SENT', 'OTP sent via email', ?, ?)";
            executeUpdate($logQuery, "iss", array($faculty_id, $ip, $userAgent));

            $response['success'] = true;
            $response['message'] = 'OTP sent successfully! Please check your email inbox.';

        } catch (Exception $e) {
            error_log("Email Error: " . $mail->ErrorInfo);
            // For testing: show OTP if email fails
            $response['success'] = true;
            $response['message'] = "Email sending failed. Your OTP is: $otp (Valid for 10 minutes)";
        }

    } catch (Exception $e) {
        $response['message'] = 'An error occurred: ' . $e->getMessage();
        error_log("Send OTP Error: " . $e->getMessage());
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>