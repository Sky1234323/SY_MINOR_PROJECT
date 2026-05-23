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

// Get email from URL
$email = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '';

if (empty($email)) {
    header("Location: forgot_password.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - MITAOE Faculty Portal</title>
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

        .nav-brand:hover { transform: translateX(-5px); }

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

        .main-container {
            position: relative;
            z-index: 1;
            max-width: 700px;
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

        .email-display {
            background: var(--gray-lighter);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 35px;
            text-align: center;
            border-left: 5px solid var(--primary);
        }

        .email-display p {
            font-size: 13px;
            color: var(--gray);
            margin-bottom: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .email-display strong {
            font-size: 16px;
            color: var(--dark);
            font-weight: 700;
        }

        .otp-inputs {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 35px;
        }

        .otp-input {
            width: 65px;
            height: 70px;
            text-align: center;
            font-size: 28px;
            font-weight: 800;
            border: 3px solid var(--gray-light);
            border-radius: 12px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            background: white;
            color: var(--primary);
        }

        .otp-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 6px rgba(78, 166, 133, 0.15);
            transform: scale(1.05);
        }

        .otp-input:disabled {
            background: var(--gray-lighter);
            cursor: not-allowed;
            opacity: 0.6;
        }

        .timer-box {
            text-align: center;
            margin-bottom: 30px;
        }

        .timer {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 25px;
            background: var(--gray-lighter);
            border-radius: 50px;
            font-weight: 700;
            color: var(--primary);
            font-size: 16px;
            box-shadow: var(--shadow-md);
        }

        .timer.expired {
            color: var(--danger);
            background: #ffe5e5;
        }

        .resend-box {
            text-align: center;
            margin-bottom: 30px;
        }

        .resend-box p {
            font-size: 14px;
            color: var(--gray);
            margin-bottom: 10px;
            font-weight: 500;
        }

        .resend-btn {
            background: none;
            border: none;
            color: var(--primary);
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            padding: 8px 20px;
            border-radius: 50px;
            transition: all 0.3s ease;
        }

        .resend-btn:hover:not(:disabled) {
            background: rgba(78, 166, 133, 0.1);
            transform: scale(1.05);
        }

        .resend-btn:disabled {
            color: var(--gray);
            cursor: not-allowed;
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

        @media (max-width: 768px) {
            .main-container { padding: 30px 20px; }
            .form-card { padding: 30px 20px; }
            .nav-container { padding: 15px 20px; }
            .page-header h1 { font-size: 28px; }
            .otp-inputs { gap: 10px; }
            .otp-input { width: 50px; height: 60px; font-size: 24px; }
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
            <a href="forgot_password.php" class="nav-brand">
                <div class="back-icon">
                    <svg width="20" height="20" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                </div>
                <div>MITAOE Faculty Portal</div>
            </a>
        </div>
    </div>

    <div class="main-container">
        <div class="page-header">
            <h1>📧 Verify OTP</h1>
            <p>Enter the 6-digit code we sent to your email</p>
        </div>

        <div class="form-card">
            <div class="icon-box">
                <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
            </div>

            <div id="alertMessage" class="alert"></div>

            <div class="email-display">
                <p>OTP Sent To:</p>
                <strong><?php echo $email; ?></strong>
            </div>

            <form id="verifyOtpForm">
                <input type="hidden" name="email" value="<?php echo $email; ?>">
                
                <div class="otp-inputs">
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                </div>

                <div class="timer-box">
                    <div class="timer" id="timer">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <span id="timeLeft">10:00</span>
                    </div>
                </div>

                <div class="resend-box">
                    <p>Didn't receive the code?</p>
                    <button type="button" class="resend-btn" id="resendBtn" disabled>
                        <span id="resendText">Resend OTP (wait <span id="resendTimer">60</span>s)</span>
                    </button>
                </div>

                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <span id="btnText">Verify OTP</span>
                    <svg id="btnIcon" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <div id="btnSpinner" class="spinner" style="display: none;"></div>
                </button>
            </form>

            <div class="back-link">
                <a href="forgot_password.php">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    <span>Back to Forgot Password</span>
                </a>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('verifyOtpForm');
        const otpInputs = document.querySelectorAll('.otp-input');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const btnIcon = document.getElementById('btnIcon');
        const btnSpinner = document.getElementById('btnSpinner');
        const alertMessage = document.getElementById('alertMessage');
        const timerDisplay = document.getElementById('timeLeft');
        const timerBox = document.getElementById('timer');
        const resendBtn = document.getElementById('resendBtn');
        const resendText = document.getElementById('resendText');
        const resendTimerSpan = document.getElementById('resendTimer');
        const email = '<?php echo $email; ?>';

        let timeLeft = 600; // 10 minutes
        let resendTimeLeft = 60; // 60 seconds
        let timerInterval;
        let resendInterval;

        otpInputs[0].focus();

        // OTP Input Navigation
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
                
                if (this.value.length === 1 && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !this.value && index > 0) {
                    otpInputs[index - 1].focus();
                }
                
                if (e.key === 'ArrowLeft' && index > 0) {
                    otpInputs[index - 1].focus();
                }
                
                if (e.key === 'ArrowRight' && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            });

            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '');
                
                for (let i = 0; i < pastedData.length && (index + i) < otpInputs.length; i++) {
                    otpInputs[index + i].value = pastedData[i];
                }
                
                if (index + pastedData.length < otpInputs.length) {
                    otpInputs[index + pastedData.length].focus();
                } else {
                    otpInputs[otpInputs.length - 1].focus();
                }
            });
        });

        // Timer Countdown
        function startTimer() {
            timerInterval = setInterval(() => {
                timeLeft--;
                
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                timerDisplay.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                
                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    timerBox.classList.add('expired');
                    timerDisplay.textContent = 'Expired';
                    showAlert('OTP has expired! Please request a new one.', 'danger');
                    
                    otpInputs.forEach(input => input.disabled = true);
                    submitBtn.disabled = true;
                }
            }, 1000);
        }

        // Resend Timer
        function startResendTimer() {
            resendTimeLeft = 60;
            resendBtn.disabled = true;
            
            resendInterval = setInterval(() => {
                resendTimeLeft--;
                resendTimerSpan.textContent = resendTimeLeft;
                
                if (resendTimeLeft <= 0) {
                    clearInterval(resendInterval);
                    resendBtn.disabled = false;
                    resendText.textContent = 'Resend OTP';
                }
            }, 1000);
        }

        startTimer();
        startResendTimer();

        // Resend OTP
        resendBtn.addEventListener('click', async function() {
            this.disabled = true;
            
            try {
                const formData = new FormData();
                formData.append('email', email);

                const response = await fetch('../php/send_otp.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    showAlert('New OTP sent successfully!', 'success');
                    
                    timeLeft = 600;
                    clearInterval(timerInterval);
                    startTimer();
                    timerBox.classList.remove('expired');
                    
                    otpInputs.forEach(input => {
                        input.disabled = false;
                        input.value = '';
                    });
                    otpInputs[0].focus();
                    submitBtn.disabled = false;
                    
                    startResendTimer();
                } else {
                    showAlert(data.message, 'danger');
                    this.disabled = false;
                }
            } catch (error) {
                showAlert('An error occurred. Please try again.', 'danger');
                this.disabled = false;
                console.error('Error:', error);
            }
        });

        // Form Submit
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            let otp = '';
            otpInputs.forEach(input => {
                otp += input.value;
            });

            if (otp.length !== 6) {
                showAlert('Please enter all 6 digits', 'danger');
                return;
            }

            submitBtn.disabled = true;
            btnText.textContent = 'Verifying...';
            btnIcon.style.display = 'none';
            btnSpinner.style.display = 'block';

            try {
                const formData = new FormData();
                formData.append('email', email);
                formData.append('otp', otp);

                const response = await fetch('../php/verify_otp.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                submitBtn.disabled = false;
                btnText.textContent = 'Verify OTP';
                btnIcon.style.display = 'block';
                btnSpinner.style.display = 'none';

                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => {
                        window.location.href = `reset_password.php?email=${encodeURIComponent(email)}&token=${data.token}`;
                    }, 1500);
                } else {
                    showAlert(data.message, 'danger');
                    
                    otpInputs.forEach(input => input.value = '');
                    otpInputs[0].focus();
                }
            } catch (error) {
                submitBtn.disabled = false;
                btnText.textContent = 'Verify OTP';
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

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        window.addEventListener('beforeunload', function() {
            clearInterval(timerInterval);
            clearInterval(resendInterval);
        });
    </script>
</body>
</html>