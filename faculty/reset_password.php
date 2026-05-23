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

// Get email and token from URL
$email = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '';
$token = isset($_GET['token']) ? htmlspecialchars($_GET['token']) : '';

if (empty($email) || empty($token)) {
    header("Location: forgot_password.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - MITAOE Faculty Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
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
            --warning: #ffc107;
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
            justify-content: center;
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
        }

        .main-container {
            position: relative;
            z-index: 1;
            max-width: 650px;
            margin: 0 auto;
            padding: 50px 40px;
        }

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

        /* Professional Password Strength Meter */
        .password-strength {
            margin-top: 12px;
            display: none;
        }

        .password-strength.active { display: block; }

        .strength-bar {
            height: 4px;
            background: var(--gray-light);
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .strength-fill {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-text {
            font-size: 12px;
            font-weight: 600;
        }

        .strength-weak {
            width: 33%;
            background: var(--danger);
        }

        .strength-fair {
            width: 50%;
            background: #ff9800;
        }

        .strength-good {
            width: 75%;
            background: #4caf50;
        }

        .strength-strong {
            width: 100%;
            background: var(--success);
        }

        /* Optional Password Tips (Not Requirements) */
        .password-tips {
            background: var(--gray-lighter);
            padding: 20px;
            border-radius: 15px;
            margin-top: 20px;
            border-left: 5px solid var(--primary);
        }

        .password-tips p {
            font-size: 13px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 12px;
        }

        .tip {
            font-size: 12px;
            color: var(--gray);
            margin: 6px 0;
            padding-left: 20px;
            position: relative;
        }

        .tip::before {
            content: '💡';
            position: absolute;
            left: 0;
        }

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

        .success-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .success-modal.active { display: flex; }

        .success-content {
            background: white;
            padding: 60px 50px;
            border-radius: 30px;
            text-align: center;
            max-width: 450px;
            animation: scaleIn 0.5s ease;
        }

        @keyframes scaleIn {
            from { transform: scale(0); }
            to { transform: scale(1); }
        }

        .success-icon-large {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, var(--success), #20c997);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: bounceIn 0.6s ease;
        }

        @keyframes bounceIn {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .success-icon-large svg {
            width: 60px;
            height: 60px;
            stroke: white;
        }

        .success-content h2 {
            font-size: 32px;
            color: var(--dark);
            margin-bottom: 15px;
            font-weight: 800;
        }

        .success-content p {
            font-size: 16px;
            color: var(--gray);
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .redirect-timer {
            font-size: 15px;
            color: var(--primary);
            font-weight: 700;
            padding: 12px 25px;
            background: rgba(78, 166, 133, 0.1);
            border-radius: 50px;
            display: inline-block;
        }

        @media (max-width: 768px) {
            .main-container { padding: 30px 20px; }
            .form-card { padding: 30px 20px; }
            .nav-container { padding: 15px 20px; }
            .page-header h1 { font-size: 28px; }
            .success-content { padding: 40px 30px; margin: 20px; }
        }
    </style>
</head>
<body>
    <div class="bg-particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <div class="top-nav">
        <div class="nav-container">
            <div class="nav-brand">
                🎓 MITAOE Faculty Portal
            </div>
        </div>
    </div>
    <div class="main-container">
        <div class="page-header">
            <h1>🔐 Reset Password</h1>
            <p>Create a new strong password for your account</p>
        </div>

        <div class="form-card">
            <div class="icon-box">
                <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
            </div>

            <div id="alertMessage" class="alert"></div>

            <form id="resetPasswordForm">
                <input type="hidden" name="email" value="<?php echo $email; ?>">
                <input type="hidden" name="token" value="<?php echo $token; ?>">

                <div class="form-group">
                    <label>New Password <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <svg class="input-icon" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        <input type="password" class="form-control" id="newPassword" name="new_password" placeholder="Enter new password (minimum 8 characters)" required>
                        <svg class="toggle-password" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" onclick="togglePassword('newPassword', this)">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </div>
                    <div class="password-strength" id="passwordStrength">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <div class="strength-text" id="strengthText"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Confirm Password <span class="required">*</span></label>
                    <div class="input-wrapper">
                        <svg class="input-icon" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password" placeholder="Confirm new password" required>
                        <svg class="toggle-password" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" onclick="togglePassword('confirmPassword', this)">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </div>
                </div>

                <div class="password-tips">
                    <p>💡 Tips for a stronger password:</p>
                    <div class="tip">Use a mix of letters, numbers, and symbols</div>
                    <div class="tip">Avoid common words or personal information</div>
                    <div class="tip">Make it at least 12 characters for better security</div>
                    <div class="tip">Use a unique password you haven't used before</div>
                </div>

                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <span id="btnText">Reset Password</span>
                    <svg id="btnIcon" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                    <div id="btnSpinner" class="spinner" style="display: none;"></div>
                </button>
            </form>
        </div>
    </div>

    <div class="success-modal" id="successModal">
        <div class="success-content">
            <div class="success-icon-large">
                <svg fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <h2>Password Reset Successfully! 🎉</h2>
            <p>Your password has been changed successfully. You can now login with your new password.</p>
            <div class="redirect-timer">Redirecting to login in <span id="countdown">3</span>s...</div>
        </div>
    </div>

    <script>
        const form = document.getElementById('resetPasswordForm');
        const newPasswordInput = document.getElementById('newPassword');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const btnIcon = document.getElementById('btnIcon');
        const btnSpinner = document.getElementById('btnSpinner');
        const alertMessage = document.getElementById('alertMessage');
        const passwordStrength = document.getElementById('passwordStrength');
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');
        const successModal = document.getElementById('successModal');

        // Professional Password Strength Checker (like Google/Facebook)
        newPasswordInput.addEventListener('input', function() {
            const password = this.value;
            
            if (password.length > 0) {
                passwordStrength.classList.add('active');
                
                let strength = 0;
                
                // Length scoring (most important)
                if (password.length >= 8) strength += 1;
                if (password.length >= 12) strength += 1;
                if (password.length >= 16) strength += 1;
                
                // Character variety scoring
                if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 1; // Mixed case
                if (/[0-9]/.test(password)) strength += 1; // Numbers
                if (/[^A-Za-z0-9]/.test(password)) strength += 1; // Special chars
                
                // Complexity scoring
                if (password.length >= 12 && /[a-z]/.test(password) && /[A-Z]/.test(password) && /[0-9]/.test(password) && /[^A-Za-z0-9]/.test(password)) {
                    strength += 1; // Bonus for well-rounded password
                }
                
                // Penalty for common patterns
                if (/^[0-9]+$/.test(password)) strength -= 2; // Only numbers
                if (/^[a-zA-Z]+$/.test(password)) strength -= 1; // Only letters
                if (/(.)\1{2,}/.test(password)) strength -= 1; // Repeated characters (aaa, 111)
                if (/^(password|123456|qwerty|admin)/i.test(password)) strength -= 3; // Common passwords
                
                // Normalize strength to 0-4 range
                strength = Math.max(0, Math.min(4, strength));
                
                // Update strength bar and text
                strengthFill.className = 'strength-fill';
                
                if (strength === 0 || strength === 1) {
                    strengthFill.classList.add('strength-weak');
                    strengthText.textContent = 'Weak password';
                    strengthText.style.color = '#dc3545';
                } else if (strength === 2) {
                    strengthFill.classList.add('strength-fair');
                    strengthText.textContent = 'Fair password';
                    strengthText.style.color = '#ff9800';
                } else if (strength === 3) {
                    strengthFill.classList.add('strength-good');
                    strengthText.textContent = 'Good password';
                    strengthText.style.color = '#4caf50';
                } else {
                    strengthFill.classList.add('strength-strong');
                    strengthText.textContent = 'Strong password';
                    strengthText.style.color = '#28a745';
                }
            } else {
                passwordStrength.classList.remove('active');
            }
        });

        // Toggle password visibility
        function togglePassword(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
            } else {
                input.type = 'password';
                icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
            }
        }

        // Form Submit - ONLY check minimum 8 characters
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const newPassword = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;

            // Only requirement: Minimum 8 characters
            if (newPassword.length < 8) {
                showAlert('Password must be at least 8 characters long!', 'danger');
                newPasswordInput.focus();
                return;
            }

            // Validate password match
            if (newPassword !== confirmPassword) {
                showAlert('Passwords do not match!', 'danger');
                confirmPasswordInput.focus();
                return;
            }

            // Disable button and show loading
            submitBtn.disabled = true;
            btnText.textContent = 'Resetting Password...';
            btnIcon.style.display = 'none';
            btnSpinner.style.display = 'block';

            try {
                const formData = new FormData(this);

                const response = await fetch('../php/reset_password.php', {
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
                btnText.textContent = 'Reset Password';
                btnIcon.style.display = 'block';
                btnSpinner.style.display = 'none';

                if (data.success) {
                    // Show success modal
                    successModal.classList.add('active');
                    
                    // Countdown timer
                    let seconds = 3;
                    const countdownElement = document.getElementById('countdown');
                    const countdownInterval = setInterval(() => {
                        seconds--;
                        countdownElement.textContent = seconds;
                        
                        if (seconds <= 0) {
                            clearInterval(countdownInterval);
                            window.location.href = 'faculty_auth.php';
                        }
                    }, 1000);
                } else {
                    showAlert(data.message, 'danger');
                }
            } catch (error) {
                // Re-enable button
                submitBtn.disabled = false;
                btnText.textContent = 'Reset Password';
                btnIcon.style.display = 'block';
                btnSpinner.style.display = 'none';

                showAlert('An error occurred. Please try again.', 'danger');
                console.error('Error:', error);
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

        // Auto-focus first password input
        window.addEventListener('load', function() {
            newPasswordInput.focus();
        });
    </script>
</body>
</html>
