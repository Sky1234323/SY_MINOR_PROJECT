<?php
session_start();
if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - MITAOE Portal</title>
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
            --danger: #dc3545;
            --success: #28a745;
            --gradient-primary: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
            --shadow-xl: 0 20px 50px rgba(0, 0, 0, 0.15);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
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
        .particle:nth-child(6) { width: 90px; height: 90px; top: 20%; left: 60%; animation-delay: 10s; }
        .particle:nth-child(7) { width: 65px; height: 65px; top: 70%; left: 40%; animation-delay: 12s; }
        .particle:nth-child(8) { width: 75px; height: 75px; top: 40%; left: 15%; animation-delay: 14s; }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) translateX(0) scale(1);
                opacity: 0.3;
            }
            25% {
                transform: translateY(-30px) translateX(20px) scale(1.1);
                opacity: 0.5;
            }
            50% {
                transform: translateY(-60px) translateX(-20px) scale(0.9);
                opacity: 0.3;
            }
            75% {
                transform: translateY(-30px) translateX(30px) scale(1.05);
                opacity: 0.4;
            }
        }

        /* Login Container */
        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 500px;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Header */
        .login-header {
            background: var(--gradient-primary);
            color: white;
            padding: 40px;
            text-align: center;
        }

        .header-icon {
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .header-icon svg {
            width: 50px;
            height: 50px;
        }

        .login-header h1 {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .login-header p {
            font-size: 16px;
            opacity: 0.9;
            font-weight: 500;
        }

        .admin-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
            margin-top: 10px;
            letter-spacing: 1px;
        }

        /* Form Container */
        .form-container {
            padding: 40px;
        }

        /* Alert Messages */
        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: none;
            animation: slideDown 0.3s ease;
            font-weight: 600;
            font-size: 14px;
        }

        .alert.active {
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

        .toggle-password {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--gray);
            transition: color 0.3s ease;
            z-index: 2;
        }

        .toggle-password:hover {
            color: var(--primary);
        }

        /* Button */
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
            margin-top: 30px;
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

        .btn-primary:hover::before {
            left: 100%;
        }

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

        /* Security Notice */
        .security-notice {
            background: var(--gray-lighter);
            padding: 15px;
            border-radius: 12px;
            margin-top: 25px;
            text-align: center;
            border-left: 5px solid var(--primary);
        }

        .security-notice p {
            font-size: 12px;
            color: var(--gray);
            line-height: 1.6;
            margin: 0;
        }

        .security-notice strong {
            color: var(--dark);
            display: block;
            margin-bottom: 5px;
        }

        /* Responsive */
        /* ===== RESPONSIVE DESIGN - MOBILE & TABLET ===== */

/* Tablet (iPad, tablets) - 1024px and below */
@media (max-width: 1024px) {
    .login-container {
        max-width: 450px;
        margin: 20px;
    }

    .login-header {
        padding: 35px 30px;
    }

    .login-header h1 {
        font-size: 28px;
    }

    .form-container {
        padding: 35px 30px;
    }
}

/* Mobile Large (phones in landscape) - 768px and below */
@media (max-width: 768px) {
    body {
        padding: 10px;
    }

    .login-container {
        max-width: 100%;
        margin: 10px;
        border-radius: 20px;
    }

    .login-header {
        padding: 30px 20px;
    }

    .login-header h1 {
        font-size: 24px;
    }

    .login-header p {
        font-size: 14px;
    }

    .header-icon {
        width: 80px;
        height: 80px;
    }

    .header-icon svg {
        width: 40px;
        height: 40px;
    }

    .form-container {
        padding: 30px 20px;
    }

    .form-control {
        padding: 14px 18px 14px 50px;
        font-size: 14px;
    }

    .input-icon {
        left: 16px;
    }

    .toggle-password {
        right: 16px;
    }

    .btn {
        padding: 16px;
        font-size: 15px;
    }

    .security-notice {
        padding: 12px;
    }

    .security-notice p {
        font-size: 11px;
    }

    /* Reduce particle sizes on mobile */
    .particle {
        opacity: 0.2 !important;
    }

    .particle:nth-child(1) { width: 50px; height: 50px; }
    .particle:nth-child(2) { width: 40px; height: 40px; }
    .particle:nth-child(3) { width: 60px; height: 60px; }
    .particle:nth-child(4) { width: 35px; height: 35px; }
    .particle:nth-child(5) { width: 45px; height: 45px; }
    .particle:nth-child(6) { width: 55px; height: 55px; }
    .particle:nth-child(7) { width: 40px; height: 40px; }
    .particle:nth-child(8) { width: 50px; height: 50px; }
}

/* Mobile Small (iPhone SE, small phones) - 480px and below */
@media (max-width: 480px) {
    .login-container {
        border-radius: 15px;
        margin: 5px;
    }

    .login-header {
        padding: 25px 15px;
    }

    .login-header h1 {
        font-size: 22px;
    }

    .login-header p {
        font-size: 13px;
    }

    .header-icon {
        width: 70px;
        height: 70px;
        margin-bottom: 15px;
    }

    .header-icon svg {
        width: 35px;
        height: 35px;
    }

    .admin-badge {
        font-size: 11px;
        padding: 5px 14px;
    }

    .form-container {
        padding: 25px 15px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        font-size: 13px;
        margin-bottom: 8px;
    }

    .form-control {
        padding: 12px 16px 12px 45px;
        font-size: 13px;
        border-radius: 10px;
    }

    .input-icon {
        left: 14px;
    }

    .toggle-password {
        right: 14px;
    }

    .btn {
        padding: 14px;
        font-size: 14px;
        border-radius: 50px;
    }

    .alert {
        padding: 12px 16px;
        font-size: 13px;
    }

    .security-notice {
        padding: 10px;
        margin-top: 20px;
    }

    .security-notice strong {
        font-size: 12px;
    }

    .security-notice p {
        font-size: 10px;
    }
}

/* Mobile Extra Small (very small phones) - 360px and below */
@media (max-width: 360px) {
    .login-header h1 {
        font-size: 20px;
    }

    .login-header p {
        font-size: 12px;
    }

    .header-icon {
        width: 60px;
        height: 60px;
    }

    .form-container {
        padding: 20px 12px;
    }

    .form-control {
        padding: 11px 14px 11px 42px;
        font-size: 12px;
    }

    .btn {
        padding: 12px;
        font-size: 13px;
    }
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
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>
    <!-- Login Container -->
    <div class="login-container">
        <!-- Header -->
        <div class="login-header">
            <div class="header-icon">
                <svg fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                    <path d="M2 17l10 5 10-5M2 12l10 5 10-5"></path>
                </svg>
            </div>
            <h1>MITAOE Portal</h1>
            <p>Administrator Access</p>
            <div class="admin-badge"> SECURE LOGIN</div>
        </div>

        <!-- Form Container -->
        <div class="form-container">
            <div id="errorMsg" class="alert alert-danger"></div>
            <div id="successMsg" class="alert alert-success"></div>

            <form id="loginForm">
                <div class="form-group">
                    <label>Username / Email <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <svg class="input-icon" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Enter admin username or email" required autocomplete="username">
                    </div>
                </div>

                <div class="form-group">
                    <label>Password <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <svg class="input-icon" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter admin password" required autocomplete="current-password">
                        <svg class="toggle-password" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" onclick="togglePassword()">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" id="loginBtn">
                    <span id="btnText">Login to Dashboard</span>
                    <svg id="btnIcon" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                    <div id="btnSpinner" class="spinner" style="display: none;"></div>
                </button>
            </form>

            <div class="security-notice">
                <strong> Security Notice</strong>
                <p>This is a restricted area. Only authorized administrators can access this system. All login attempts are monitored and logged.</p>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('loginForm');
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');
        const loginBtn = document.getElementById('loginBtn');
        const btnText = document.getElementById('btnText');
        const btnIcon = document.getElementById('btnIcon');
        const btnSpinner = document.getElementById('btnSpinner');
        const errorMsg = document.getElementById('errorMsg');
        const successMsg = document.getElementById('successMsg');

        // Auto-focus username input
        window.addEventListener('load', function() {
            usernameInput.focus();
        });

        // Toggle password visibility
        function togglePassword() {
            const icon = event.currentTarget;
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
            } else {
                passwordInput.type = 'password';
                icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
            }
        }

        // Form submit
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const username = usernameInput.value.trim();
            const password = passwordInput.value;

            // Validation
            if (!username || !password) {
                showAlert('Please enter both username and password', 'danger');
                return;
            }

            // Disable button and show loading
            loginBtn.disabled = true;
            btnText.textContent = 'Authenticating...';
            btnIcon.style.display = 'none';
            btnSpinner.style.display = 'block';

            try {
                const formData = new FormData();
                formData.append('username', username);
                formData.append('password', password);

                const response = await fetch('../php/admin_auth.php', {
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
                loginBtn.disabled = false;
                btnText.textContent = 'Login to Dashboard';
                btnIcon.style.display = 'block';
                btnSpinner.style.display = 'none';

                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1500);
                } else {
                    showAlert(data.message, 'danger');
                    passwordInput.value = '';
                    passwordInput.focus();
                }
            } catch (error) {
                // Re-enable button
                loginBtn.disabled = false;
                btnText.textContent = 'Login to Dashboard';
                btnIcon.style.display = 'block';
                btnSpinner.style.display = 'none';

                console.error('Error:', error);
                showAlert('An error occurred. Please try again.', 'danger');
            }
        });

        function showAlert(message, type) {
            const alertBox = type === 'danger' ? errorMsg : successMsg;
            const otherBox = type === 'danger' ? successMsg : errorMsg;
            
            // Hide other alert
            otherBox.classList.remove('active');
            
            // Show current alert
            alertBox.textContent = message;
            alertBox.classList.add('active');
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                alertBox.classList.remove('active');
            }, 5000);

            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    </script>
</body>
</html>