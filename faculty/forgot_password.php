<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, redirect
if (isset($_SESSION['faculty_id'])) {
    header("Location: faculty_dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - MITAOE Faculty Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #4EA685;
            --primary-dark: #3d8a6b;
            --secondary: #57B894;
            --dark: #1a1a2e;
            --white: #ffffff;
            --gray: #6c757d;
            --gray-light: #e9ecef;
            --gray-lighter: #f8f9fa;
            --success: #28a745;
            --danger: #dc3545;
            --gradient-primary: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
            --shadow-xl: 0 20px 50px rgba(0, 0, 0, 0.15);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Background Particles */
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

        /* Top Navigation */
        .top-nav {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            box-shadow: var(--shadow-md);
            border-bottom: 1px solid rgba(78, 166, 133, 0.1);
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 24px;
            font-weight: 800;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .nav-brand:hover {
            transform: translateX(-5px);
        }

        .back-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: var(--gradient-primary);
            border-radius: 50%;
            box-shadow: var(--shadow-md);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        /* Main Container */
        .main-container {
            position: relative;
            z-index: 1;
            max-width: 600px;
            margin: 0 auto;
            padding: 50px 40px;
        }

        /* Page Header */
        .page-header {
            text-align: center;
            margin-bottom: 40px;
            animation: fadeInDown 0.8s ease;
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .page-header h1 {
            font-size: 38px;
            font-weight: 800;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
        }

        .page-header p {
            font-size: 16px;
            color: var(--gray);
            font-weight: 500;
        }

        /* Form Card */
        .form-card {
            background: white;
            padding: 50px;
            border-radius: 30px;
            box-shadow: var(--shadow-xl);
            animation: fadeInUp 0.8s ease;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .icon-box {
            width: 100px;
            height: 100px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            box-shadow: 0 8px 25px rgba(78, 166, 133, 0.3);
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .icon-box svg {
            width: 50px;
            height: 50px;
            stroke: white;
        }

        /* Alert Messages */
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: none;
            animation: slideDown 0.3s ease;
            font-weight: 600;
            font-size: 14px;
        }

        .alert.active { display: block; }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-danger {
            background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
            color: white;
            border-left: 5px solid #c0392b;
        }

        .alert-success {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
            border-left: 5px solid #229954;
        }

        /* Info Box */
        .info-box {
            background: var(--gray-lighter);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            border-left: 5px solid var(--primary);
        }

        .info-box p {
            font-size: 14px;
            color: var(--gray);
            margin: 0;
            line-height: 1.6;
            font-weight: 500;
        }

        /* Form Groups */
        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 10px;
        }

        .form-group label .required {
            color: var(--danger);
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            z-index: 1;
        }

        .form-control {
            width: 100%;
            padding: 16px 20px 16px 55px;
            font-size: 15px;
            border: 3px solid var(--gray-light);
            border-radius: 12px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 6px rgba(78, 166, 133, 0.1);
            transform: translateY(-2px);
        }

        /* Buttons */
        .btn {
            width: 100%;
            padding: 18px;
            font-size: 16px;
            font-weight: 700;
            border: none;
            border-radius: 60px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 6px 20px rgba(78, 166, 133, 0.3);
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        .btn-primary:hover::before { left: 100%; }

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(78, 166, 133, 0.4);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn svg, .btn span {
            position: relative;
            z-index: 1;
        }

        /* Loading Spinner */
        .spinner {
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Back Link */
        .back-link {
            text-align: center;
            margin-top: 25px;
        }

        .back-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            padding: 10px 20px;
            border-radius: 50px;
        }

        .back-link a:hover {
            background: rgba(78, 166, 133, 0.1);
            transform: translateX(-5px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-container { padding: 30px 20px; }
            .form-card { padding: 30px 20px; }
            .nav-container { padding: 15px 20px; }
            .page-header h1 { font-size: 28px; }
        }
    </style>
</head>
<body>
    <!-- Animated Background Particles -->
    <div class="bg-particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <!-- Top Navigation -->
    <div class="top-nav">
        <div class="nav-container">
            <a href="faculty_auth.php" class="nav-brand">
                <div class="back-icon">
                    <svg width="20" height="20" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                </div>
                <div>MITAOE Faculty Portal</div>
            </a>
        </div>
    </div>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>🔐 Forgot Password?</h1>
            <p>Don't worry! We'll help you reset it</p>
        </div>

        <!-- Form Card -->
        <div class="form-card">
            <div class="icon-box">
                <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
            </div>

            <div id="alertMessage" class="alert"></div>

            <div class="info-box">
                <p>📧 Enter your registered email address and we'll send you a 6-digit OTP to reset your password. The OTP will be valid for 10 minutes.</p>
            </div>

            <form id="forgotPasswordForm">
                <div class="form-group">
                    <label>Email Address <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <svg class="input-icon" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your registered email" required autocomplete="email">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <span id="btnText">Send OTP</span>
                    <svg id="btnIcon" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                    <div id="btnSpinner" class="spinner" style="display: none;"></div>
                </button>
            </form>

            <div class="back-link">
                <a href="faculty_auth.php">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    <span>Back to Login</span>
                </a>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('forgotPasswordForm');
        const emailInput = document.getElementById('email');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const btnIcon = document.getElementById('btnIcon');
        const btnSpinner = document.getElementById('btnSpinner');
        const alertMessage = document.getElementById('alertMessage');

        // Auto-focus email input
        window.addEventListener('load', function() {
            emailInput.focus();
        });

        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const email = emailInput.value.trim();

            if (!email) {showAlert('Please enter your email address', 'danger');
                emailInput.focus();
                return;
            }

            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showAlert('Please enter a valid email address', 'danger');
                emailInput.focus();
                return;
            }

            // Disable button and show loading
            submitBtn.disabled = true;
            btnText.textContent = 'Sending OTP...';
            btnIcon.style.display = 'none';
            btnSpinner.style.display = 'block';

            try {
                const formData = new FormData();
                formData.append('email', email);

                const response = await fetch('../php/send_otp.php', {
                    method: 'POST',
                    body: formData
                });

                // Check if response is OK
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Server response:', text);
                    throw new Error('Server returned invalid response');
                }

                const data = await response.json();

                // Re-enable button
                submitBtn.disabled = false;
                btnText.textContent = 'Send OTP';
                btnIcon.style.display = 'block';
                btnSpinner.style.display = 'none';

                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => {
                        window.location.href = `verify_otp.php?email=${encodeURIComponent(email)}`;
                    }, 2000);
                } else {
                    showAlert(data.message || 'Failed to send OTP. Please try again.', 'danger');
                }
            } catch (error) {
                // Re-enable button
                submitBtn.disabled = false;
                btnText.textContent = 'Send OTP';
                btnIcon.style.display = 'block';
                btnSpinner.style.display = 'none';

                console.error('Error:', error);
                showAlert('An error occurred. Please check your internet connection and try again.', 'danger');
            }
        });

        function showAlert(message, type) {
            alertMessage.textContent = message;
            alertMessage.className = `alert alert-${type} active`;
            
            setTimeout(() => {
                alertMessage.classList.remove('active');
            }, 5000);

            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    </script>
</body>
</html>