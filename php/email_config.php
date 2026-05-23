<?php
/**
 * MITAOE Portal - Unified Email Configuration
 * Uses PHPMailer for both signup verification AND password reset
 * 
 * File: php/email_config.php
 */

// Error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/email_error.log');

// Load PHPMailer
if (file_exists(__DIR__ . '/../PHPMailer/src/PHPMailer.php')) {
    require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
    require_once __DIR__ . '/../PHPMailer/src/Exception.php';
} else {
    error_log("PHPMailer not found at: " . __DIR__ . '/../PHPMailer/src/');
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ==============================================================
// EMAIL CONFIGURATION - CHANGE THESE VALUES
// ==============================================================

// Gmail SMTP Settings
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'visionxstudio2006@gmail.com');  // ← YOUR GMAIL
define('SMTP_PASSWORD', 'hhph ylqj bfpq uyxt');           // ← YOUR APP PASSWORD
define('FROM_EMAIL', 'visionxstudio2006@gmail.com');
define('FROM_NAME', 'MITAOE Portal');

// Website URL
define('SITE_URL', 'http://localhost:8080/mitaoe_portal12');

// Admin email for notifications
define('ADMIN_EMAIL', 'visionxstudio2006@gmail.com');

// ==============================================================
// CORE EMAIL SENDING FUNCTION
// ==============================================================

/**
 * Send email using PHPMailer
 * 
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $htmlBody HTML email body
 * @param string $fromName Sender name (optional)
 * @return bool Success status
 */
function sendEmail($to, $subject, $htmlBody, $fromName = 'MITAOE Portal') {
    $mail = new PHPMailer(true);

    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->SMTPDebug = 0; // Set to 2 for debugging

        // Email settings
        $mail->setFrom(FROM_EMAIL, $fromName);
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->isHTML(true);
        $mail->Body = $htmlBody;
        $mail->AltBody = strip_tags($htmlBody);

        // Send email
        $mail->send();
        
        // Log success
        error_log("✅ Email sent successfully to: $to | Subject: $subject");
        return true;
        
    } catch (Exception $e) {
        // Log error
        error_log("❌ Email sending failed to: $to | Error: {$mail->ErrorInfo}");
        return false;
    }
}

// ==============================================================
// TOKEN GENERATION
// ==============================================================

/**
 * Generate secure random token
 * 
 * @return string 64-character hex token
 */
function generateVerificationToken() {
    return bin2hex(random_bytes(32));
}

// ==============================================================
// FACULTY SIGNUP VERIFICATION EMAIL
// ==============================================================

/**
 * Send email verification link to new faculty signup
 * 
 * @param string $email Faculty email
 * @param string $fullName Faculty full name
 * @param string $token Verification token
 * @return bool Success status
 */
function sendFacultyVerificationEmail($email, $fullName, $token) {
    $verificationLink = SITE_URL . "/faculty/verify_email.php?token=" . $token;
    
    $subject = "Verify Your Email - MITAOE Faculty Portal";
    
    $htmlBody = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: 'Arial', sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; background: #f8f9fa; }
            .header { 
                background: linear-gradient(135deg, #4EA685 0%, #57B894 100%); 
                color: white; 
                padding: 40px 30px; 
                text-align: center; 
                border-radius: 10px 10px 0 0; 
            }
            .header h1 { margin: 0; font-size: 28px; font-weight: 700; }
            .content { background: white; padding: 40px 30px; }
            .content p { margin: 15px 0; font-size: 16px; }
            .button-container { text-align: center; margin: 35px 0; }
            .verify-button { 
                display: inline-block; 
                padding: 16px 40px; 
                background: linear-gradient(135deg, #4EA685, #57B894); 
                color: white; 
                text-decoration: none; 
                border-radius: 50px; 
                font-weight: 700; 
                font-size: 16px; 
                box-shadow: 0 4px 15px rgba(78, 166, 133, 0.3);
            }
            .verify-button:hover { box-shadow: 0 6px 20px rgba(78, 166, 133, 0.4); }
            .link-box { 
                background: #f8f9fa; 
                border: 2px dashed #4EA685; 
                padding: 20px; 
                border-radius: 10px; 
                margin: 25px 0; 
                word-wrap: break-word;
            }
            .link-box p { margin: 5px 0; font-size: 13px; color: #6c757d; }
            .link-box a { color: #4EA685; font-weight: 600; }
            .warning { 
                background: #fff3cd; 
                border-left: 5px solid #ffc107; 
                padding: 15px; 
                margin: 25px 0; 
                border-radius: 5px;
            }
            .warning p { margin: 5px 0; font-size: 14px; }
            .footer { 
                text-align: center; 
                padding: 25px; 
                color: #6c757d; 
                font-size: 13px; 
                border-top: 1px solid #e9ecef;
            }
            .footer p { margin: 5px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎓 Welcome to MITAOE Portal</h1>
                <p style='margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;'>Email Verification Required</p>
            </div>
            
            <div class='content'>
                <p>Hello <strong>" . htmlspecialchars($fullName) . "</strong>,</p>
                
                <p>Thank you for registering for the MITAOE Faculty Portal! To complete your registration, please verify your email address by clicking the button below:</p>
                
                <div class='button-container'>
                    <a href='" . $verificationLink . "' class='verify-button'>
                        ✓ Verify Email Address
                    </a>
                </div>
                
                <p>Or copy and paste this link into your browser:</p>
                
                <div class='link-box'>
                    <p><strong>Verification Link:</strong></p>
                    <a href='" . $verificationLink . "'>" . $verificationLink . "</a>
                </div>
                
                <div class='warning'>
                    <p><strong>⚠️ Important:</strong></p>
                    <p>• This verification link will expire in <strong>24 hours</strong></p>
                    <p>• After verification, your account will be sent for admin approval</p>
                    <p>• You'll receive another email once your account is approved</p>
                    <p>• If you didn't create this account, please ignore this email</p>
                </div>
                
                <p>Once your email is verified and your account is approved by the admin, you'll be able to access the faculty portal.</p>
                
                <p style='margin-top: 30px;'>Best regards,<br><strong>MITAOE IT Team</strong></p>
            </div>
            
            <div class='footer'>
                <p>This is an automated email. Please do not reply to this message.</p>
                <p>&copy; " . date('Y') . " MITAOE Student Information Portal. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($email, $subject, $htmlBody);
}

// ==============================================================
// ADMIN APPROVAL NOTIFICATION
// ==============================================================

/**
 * Send notification to admin when new faculty verifies email
 * 
 * @param array $facultyData Faculty information
 * @return bool Success status
 */
function sendAdminApprovalNotification($facultyData) {
    $approvalLink = SITE_URL . "/admin/approve_faculty.php";
    
    $subject = "New Faculty Approval Request - " . $facultyData['full_name'];
    
    $htmlBody = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: 'Arial', sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; background: #f8f9fa; }
            .header { 
                background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); 
                color: white; 
                padding: 30px; 
                text-align: center; 
                border-radius: 10px 10px 0 0; 
            }
            .content { background: white; padding: 30px; }
            .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            .info-table td { padding: 12px; border-bottom: 1px solid #e9ecef; }
            .info-table td:first-child { font-weight: 700; color: #6c757d; width: 40%; }
            .button-container { text-align: center; margin: 30px 0; }
            .approve-button { 
                display: inline-block; 
                padding: 15px 35px; 
                background: linear-gradient(135deg, #4EA685, #57B894); 
                color: white; 
                text-decoration: none; 
                border-radius: 50px; 
                font-weight: 700; 
                font-size: 16px;
            }
            .footer { text-align: center; padding: 20px; color: #6c757d; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>👤 New Faculty Approval Request</h1>
            </div>
            
            <div class='content'>
                <p>A new faculty member has verified their email and is waiting for approval:</p>
                
                <table class='info-table'>
                    <tr>
                        <td>Full Name:</td>
                        <td><strong>" . htmlspecialchars($facultyData['full_name']) . "</strong></td>
                    </tr>
                    <tr>
                        <td>Email:</td>
                        <td>" . htmlspecialchars($facultyData['email']) . "</td>
                    </tr>
                    <tr>
                        <td>Phone:</td>
                        <td>" . htmlspecialchars($facultyData['phone'] ?? 'Not provided') . "</td>
                    </tr>
                    <tr>
                        <td>Department:</td>
                        <td>" . htmlspecialchars($facultyData['department'] ?? 'Not provided') . "</td>
                    </tr>
                    <tr>
                        <td>Designation:</td>
                        <td>" . htmlspecialchars($facultyData['designation'] ?? 'Not provided') . "</td>
                    </tr>
                    <tr>
                        <td>Registration Date:</td>
                        <td>" . date('F d, Y H:i', strtotime($facultyData['created_at'])) . "</td>
                    </tr>
                </table>
                
                <div class='button-container'>
                    <a href='" . $approvalLink . "' class='approve-button'>
                        Review & Approve
                    </a>
                </div>
                
                <p style='color: #6c757d; font-size: 14px; margin-top: 20px;'>
                    Please review this request and approve or reject the faculty account.
                </p>
            </div>
            
            <div class='footer'>
                <p>&copy; " . date('Y') . " MITAOE Admin Portal</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail(ADMIN_EMAIL, $subject, $htmlBody);
}

// ==============================================================
// FACULTY APPROVAL/REJECTION EMAILS
// ==============================================================

/**
 * Send approval email to faculty
 * 
 * @param string $email Faculty email
 * @param string $fullName Faculty full name
 * @return bool Success status
 */
function sendFacultyApprovedEmail($email, $fullName) {
    $loginLink = SITE_URL . "/faculty/faculty_auth.php";
    
    $subject = "Account Approved - MITAOE Faculty Portal";
    
    $htmlBody = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: 'Arial', sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; background: #f8f9fa; }
            .header { 
                background: linear-gradient(135deg, #28a745 0%, #20c997 100%); 
                color: white; 
                padding: 40px 30px; 
                text-align: center; 
                border-radius: 10px 10px 0 0; 
            }
            .header h1 { margin: 0; font-size: 32px; }
            .content { background: white; padding: 40px 30px; }
            .success-icon { 
                width: 80px; 
                height: 80px; 
                background: #28a745; 
                border-radius: 50%; 
                margin: 0 auto 25px; 
                display: flex; 
                align-items: center; 
                justify-content: center; 
                font-size: 50px;
            }
            .button-container { text-align: center; margin: 35px 0; }
            .login-button { 
                display: inline-block; 
                padding: 16px 40px; 
                background: linear-gradient(135deg, #4EA685, #57B894); 
                color: white; 
                text-decoration: none; 
                border-radius: 50px; 
                font-weight: 700; 
                font-size: 16px;
            }
            .footer { text-align: center; padding: 25px; color: #6c757d; font-size: 13px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎉 Account Approved!</h1>
            </div>
            
            <div class='content'>
                <div class='success-icon' style='color: white;'>✓</div>
                
                <p>Dear <strong>" . htmlspecialchars($fullName) . "</strong>,</p>
                
                <p>Great news! Your MITAOE Faculty Portal account has been <strong>approved by the administrator</strong>.</p>
                
                <p>You can now access the faculty portal using your registered email and password:</p>
                
                <div class='button-container'>
                    <a href='" . $loginLink . "' class='login-button'>
                        Login to Portal
                    </a>
                </div>
                
                <p style='background: #e7f3ff; border-left: 4px solid #2196F3; padding: 15px; margin: 25px 0;'>
                    <strong>📧 Login Email:</strong> " . htmlspecialchars($email) . "<br>
                    <strong>🔐 Password:</strong> The password you set during registration
                </p>
                
                <p>If you have any questions or need assistance, please contact the IT department.</p>
                
                <p style='margin-top: 30px;'>Welcome aboard!<br><strong>MITAOE IT Team</strong></p>
            </div>
            
            <div class='footer'>
                <p>&copy; " . date('Y') . " MITAOE Faculty Portal</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($email, $subject, $htmlBody);
}

/**
 * Send rejection email to faculty
 * 
 * @param string $email Faculty email
 * @param string $fullName Faculty full name
 * @param string $reason Rejection reason
 * @return bool Success status
 */
function sendFacultyRejectedEmail($email, $fullName, $reason) {
    $subject = "Account Registration Update - MITAOE Faculty Portal";
    
    $htmlBody = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: 'Arial', sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; background: #f8f9fa; }
            .header { 
                background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); 
                color: white; 
                padding: 40px 30px; 
                text-align: center; 
                border-radius: 10px 10px 0 0; 
            }
            .content { background: white; padding: 40px 30px; }
            .reason-box { 
                background: #fff3cd; 
                border-left: 5px solid #ffc107; 
                padding: 20px; 
                margin: 25px 0; 
                border-radius: 5px;
            }
            .footer { text-align: center; padding: 25px; color: #6c757d; font-size: 13px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Registration Status Update</h1>
            </div>
            
            <div class='content'>
                <p>Dear <strong>" . htmlspecialchars($fullName) . "</strong>,</p>
                
                <p>Thank you for your interest in the MITAOE Faculty Portal. After reviewing your application, we regret to inform you that your registration could not be approved at this time.</p>
                
                <div class='reason-box'>
                    <p style='margin: 0;'><strong>Reason:</strong></p>
                    <p style='margin: 10px 0 0 0;'>" . htmlspecialchars($reason) . "</p>
                </div>
                
                <p>If you believe this decision was made in error or if you have additional information to provide, please contact the administrator at <a href='mailto:" . ADMIN_EMAIL . "'>" . ADMIN_EMAIL . "</a>.</p>
                
                <p style='margin-top: 30px;'>Best regards,<br><strong>MITAOE IT Team</strong></p>
            </div>
            
            <div class='footer'>
                <p>&copy; " . date('Y') . " MITAOE Faculty Portal</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($email, $subject, $htmlBody);
}

// ==============================================================
// EMAIL LOGGING (FOR DEBUGGING)
// ==============================================================

/**
 * Log email attempt to file (for debugging)
 */
function logEmailToFile($to, $subject, $status) {
    $logFile = __DIR__ . '/email_logs.txt';
    $timestamp = date('Y-m-d H:i:s');
    $statusText = $status ? 'SUCCESS' : 'FAILED';
    $logEntry = "[$timestamp] [$statusText] To: $to | Subject: $subject\n";
    
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

?>