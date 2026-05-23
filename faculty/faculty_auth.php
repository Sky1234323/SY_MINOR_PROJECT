<?php
session_start();
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
    <title>Faculty Portal - MITAOE</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        :root {
            --primary-color: #4EA685;
            --secondary-color: #57B894;
            --black: #000000;
            --white: #ffffff;
            --gray: #efefef;
            --gray-2: #757575;
        }

        * {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100vh;
            overflow: hidden;
        }

        .container {
            position: relative;
            min-height: 100vh;
            overflow: hidden;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            height: 100vh;
        }

        .col {
            width: 50%;
        }

        .align-items-center {
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .flex-col {
            flex-direction: column;
        }

        .form-wrapper {
            width: 100%;
            max-width: 28rem;
        }

        .form {
            padding: 2rem;
            background-color: var(--white);
            border-radius: 1.5rem;
            width: 100%;
            box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
            transform: scale(0);
            transition: .5s ease-in-out;
            transition-delay: 1s;
        }

        .form h2 {
            font-size: 2rem;
            margin-bottom: 1.5rem;
            color: var(--primary-color);
        }

        .input-group {
            position: relative;
            width: 100%;
            margin: 1.2rem 0;
        }

        .input-group i {
            position: absolute;
            top: 50%;
            left: 1rem;
            transform: translateY(-50%);
            font-size: 1.4rem;
            color: var(--gray-2);
        }

        .input-group input {
            width: 100%;
            padding: 1rem 3rem;
            font-size: 1rem;
            background-color: var(--gray);
            border-radius: .5rem;
            border: 0.125rem solid var(--white);
            outline: none;
        }

        .input-group input:focus {
            border: 0.125rem solid var(--primary-color);
        }

        .form button {
            cursor: pointer;
            width: 100%;
            padding: .8rem 0;
            border-radius: .5rem;
            border: none;
            background-color: var(--primary-color);
            color: var(--white);
            font-size: 1.2rem;
            outline: none;
            margin-top: 1rem;
            transition: 0.3s;
        }

        .form button:hover {
            background-color: var(--secondary-color);
        }

        .form p {
            margin: 1rem 0;
            font-size: .9rem;
        }

        .pointer {
            cursor: pointer;
            color: var(--primary-color);
            text-decoration: underline;
        }

        .pointer:hover {
            color: var(--secondary-color);
        }

        .container.sign-in .form.sign-in,
        .container.sign-up .form.sign-up {
            transform: scale(1);
        }

        .content-row {
            position: absolute;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: 6;
            width: 100%;
        }

        .text {
            margin: 4rem;
            color: var(--white);
        }

        .text h2 {
            font-size: 3.5rem;
            font-weight: 800;
            margin: 2rem 0;
            transition: 1s ease-in-out;
        }

        .text p {
            font-weight: 600;
            transition: 1s ease-in-out;
            transition-delay: .2s;
        }

        .text.sign-in h2,
        .text.sign-in p {
            transform: translateX(-250%);
        }

        .text.sign-up h2,
        .text.sign-up p {
            transform: translateX(250%);
        }

        .container.sign-in .text.sign-in h2,
        .container.sign-in .text.sign-in p,
        .container.sign-up .text.sign-up h2,
        .container.sign-up .text.sign-up p {
            transform: translateX(0);
        }

        .container::before {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            height: 100vh;
            width: 300vw;
            transform: translate(35%, 0);
            background-image: linear-gradient(-45deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            transition: 1s ease-in-out;
            z-index: 6;
            box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
            border-bottom-right-radius: max(50vw, 50vh);
            border-top-left-radius: max(50vw, 50vh);
        }

        .container.sign-in::before {
            transform: translate(0, 0);
            right: 50%;
        }

        .container.sign-up::before {
            transform: translate(100%, 0);
            right: 50%;
        }

        .error, .success {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            display: none;
            font-size: 14px;
        }

        .error {
            background: #ffebee;
            color: #d32f2f;
            border-left: 4px solid #d32f2f;
        }

        .success {
            background: #e8f5e9;
            color: #388e3c;
            border-left: 4px solid #388e3c;
        }

        /* ============================================
   COMPREHENSIVE RESPONSIVE DESIGN - FIXED
   ============================================ */

/* Tablet - 1024px and below */
@media only screen and (max-width: 1024px) {
    .row {
        flex-direction: column;
    }

    .col {
        width: 100%;
    }

    .container::before {
        display: none;
    }

    .content-row {
        position: relative;
        pointer-events: all;
    }

    .text {
        margin: 2rem 1rem;
    }

    .text h2 {
        font-size: 2.5rem;
        margin: 1rem 0;
    }

    .text p {
        font-size: 1rem;
    }
}

/* Mobile Large - 768px and below */
@media only screen and (max-width: 768px) {
    html, body {
        height: auto;
        min-height: 100vh;
        overflow: auto;
    }

    .container {
        min-height: 100vh;
        background: var(--white);
        display: flex;
        flex-direction: column;
    }

    /* Remove animated background on mobile */
    .container::before {
        display: none;
    }

    /* ===== GREEN HEADER AT TOP ===== */
    .content-row {
        position: relative;
        width: 100%;
        height: auto;
        background: linear-gradient(-45deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: all;
        z-index: 1;
        padding: 3rem 1.5rem;
        order: 1; /* FORCE TO TOP */
        flex-shrink: 0;
    }

    /* Text Styling */
    .text {
        margin: 0;
        text-align: center;
        color: var(--white);
        transform: none !important;
        opacity: 1 !important;
        transition: none !important;
    }

    .text h2 {
        font-size: 2.25rem;
        font-weight: 800;
        margin: 0 0 0.75rem 0;
        transform: none !important;
        line-height: 1.2;
    }

    .text p {
        font-size: 1.125rem;
        font-weight: 600;
        margin: 0;
        transform: none !important;
        opacity: 0.95;
    }

    /* Hide inactive text */
    .container.sign-in .text.sign-up,
    .container.sign-up .text.sign-in {
        display: none;
    }

    /* Show only active text */
    .container.sign-in .text.sign-in,
    .container.sign-up .text.sign-up {
        display: block;
    }

    /* ===== FORM SECTION BELOW HEADER ===== */
    .row {
        height: auto;
        min-height: auto;
        align-items: flex-start;
        flex-direction: column;
        order: 2; /* BELOW THE GREEN HEADER */
        flex: 1;
        background: var(--white);
    }

    /* Column Layout */
    .col {
        width: 100%;
        position: relative;
        padding: 0;
        background-color: var(--white);
        border-radius: 0;
        transform: none !important;
        transition: none !important;
    }

    /* Hide inactive forms */
    .col.sign-in,
    .col.sign-up {
        display: none;
    }

    .container.sign-in .col.sign-in {
        display: flex;
    }

    .container.sign-up .col.sign-up {
        display: flex;
    }

    /* Form Wrapper */
    .form-wrapper {
        max-width: 100%;
        padding: 2.5rem 1.5rem;
        width: 100%;
    }

    /* Form Styling */
    .form {
        padding: 0;
        margin: 0;
        box-shadow: none;
        background-color: transparent;
        border-radius: 0;
        transform: scale(1) !important;
        transition: none !important;
        width: 100%;
    }

    .form h2 {
        font-size: 1.875rem;
        margin-bottom: 2rem;
        text-align: center;
        font-weight: 700;
        color: var(--primary-color);
    }

    /* Input Groups */
    .input-group {
        margin: 1.25rem 0;
    }

    .input-group i {
        font-size: 1.25rem;
        left: 1rem;
    }

    .input-group input {
        padding: 1rem 1rem 1rem 3rem;
        font-size: 1rem;
        border-radius: 0.625rem;
        border: 0.125rem solid var(--gray);
        background-color: var(--gray);
    }

    .input-group input:focus {
        border: 0.125rem solid var(--primary-color);
        background-color: var(--white);
    }

    /* Button */
    .form button {
        padding: 1rem 0;
        font-size: 1.125rem;
        margin-top: 1.25rem;
        border-radius: 0.625rem;
        font-weight: 700;
    }

    /* Form Text */
    .form p {
        margin: 1rem 0;
        font-size: 0.9375rem;
        text-align: center;
    }

    .pointer {
        font-size: 0.9375rem;
        font-weight: 700;
    }

    /* Error/Success Messages */
    .error, .success {
        padding: 12px 16px;
        font-size: 14px;
        margin-bottom: 16px;
        border-radius: 8px;
    }
}

/* Mobile Medium - 480px and below */
@media only screen and (max-width: 480px) {
    .content-row {
        padding: 2.5rem 1.25rem;
    }

    .text h2 {
        font-size: 1.875rem;
        margin: 0 0 0.625rem 0;
    }

    .text p {
        font-size: 1rem;
    }

    .form-wrapper {
        padding: 2rem 1.25rem;
    }

    .form h2 {
        font-size: 1.625rem;
        margin-bottom: 1.75rem;
    }

    .input-group {
        margin: 1rem 0;
    }

    .input-group i {
        font-size: 1.125rem;
        left: 0.875rem;
    }

    .input-group input {
        padding: 0.875rem 0.875rem 0.875rem 2.75rem;
        font-size: 0.9375rem;
        border-radius: 0.5rem;
    }

    .form button {
        padding: 0.875rem 0;
        font-size: 1rem;
        border-radius: 0.5rem;
    }

    .form p {
        font-size: 0.875rem;
        margin: 0.875rem 0;
    }

    .pointer {
        font-size: 0.875rem;
    }
}

/* Mobile Small - 360px and below */
@media only screen and (max-width: 360px) {
    .content-row {
        padding: 2rem 1rem;
    }

    .text h2 {
        font-size: 1.625rem;
        margin: 0 0 0.5rem 0;
    }

    .text p {
        font-size: 0.9375rem;
    }

    .form-wrapper {
        padding: 1.75rem 1rem;
    }

    .form h2 {
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .input-group {
        margin: 0.875rem 0;
    }

    .input-group input {
        padding: 0.75rem 0.75rem 0.75rem 2.5rem;
        font-size: 0.875rem;
    }

    .form button {
        padding: 0.75rem 0;
        font-size: 0.9375rem;
    }

    .form p {
        font-size: 0.8125rem;
    }
}

/* Landscape mobile phones */
@media only screen and (max-height: 500px) and (orientation: landscape) {
    .content-row {
        padding: 1.5rem 1rem;
    }

    .text h2 {
        font-size: 1.5rem;
        margin: 0 0 0.375rem 0;
    }

    .text p {
        font-size: 0.875rem;
    }

    .form-wrapper {
        padding: 1.5rem 1rem;
    }

    .form h2 {
        font-size: 1.375rem;
        margin-bottom: 1.25rem;
    }

    .input-group {
        margin: 0.75rem 0;
    }

    .input-group input {
        padding: 0.75rem 0.75rem 0.75rem 2.5rem;
        font-size: 0.875rem;
    }

    .form button {
        padding: 0.75rem 0;
        margin-top: 1rem;
        font-size: 0.9375rem;
    }

    .form p {
        margin: 0.75rem 0;
    }
}

/* Fix vertical spacing for very small screens */
@media only screen and (max-width: 360px) and (max-height: 640px) {
    .content-row {
        padding: 1.75rem 1rem;
    }

    .form-wrapper {
        padding: 1.5rem 1rem;
    }

    .input-group {
        margin: 0.75rem 0;
    }

    .form button {
        margin-top: 1rem;
    }
}
    </style>
</head>
<body>
    <div id="container" class="container">
        <!-- FORM SECTION -->
        <div class="row">
            <!-- SIGN UP -->
            <div class="col align-items-center flex-col sign-up">
                <div class="form-wrapper align-items-center">
                    <form class="form sign-up" id="signupForm">
                        <h2>Faculty Sign Up</h2>
                        
                        <div class="error" id="signupError"></div>
                        <div class="success" id="signupSuccess"></div>

                        <div class="input-group">
                            <i class='bx bx-user'></i>
                            <input type="text" name="full_name" placeholder="Full Name" required>
                        </div>
                        <div class="input-group">
                            <i class='bx bx-mail-send'></i>
                            <input type="email" name="email" placeholder="Email" required>
                        </div>
                        <div class="input-group">
                            <i class='bx bx-phone'></i>
                            <input type="tel" name="phone" placeholder="Phone Number">
                        </div>
                        <div class="input-group">
                            <i class='bx bx-building'></i>
                            <input type="text" name="department" placeholder="Department">
                        </div>
                        <div class="input-group">
                            <i class='bx bxs-lock-alt'></i>
                            <input type="password" name="password" placeholder="Password" required>
                        </div>
                        <div class="input-group">
                            <i class='bx bxs-lock-alt'></i>
                            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                        </div>
                        <button type="submit">Sign up</button>
                        <p>
                            <span>Already have an account?</span>
                            <b onclick="toggle()" class="pointer">Sign in here</b>
                        </p>
                    </form>
                </div>
            </div>
            <!-- END SIGN UP -->

            <!-- SIGN IN -->
            <div class="col align-items-center flex-col sign-in">
                <div class="form-wrapper align-items-center">
                    <form class="form sign-in" id="signinForm">
                        <h2>Faculty Sign In</h2>
                        
                        <div class="error" id="signinError"></div>
                        <div class="success" id="signinSuccess"></div>

                        <div class="input-group">
                            <i class='bx bx-mail-send'></i>
                            <input type="email" name="email" placeholder="Email" required>
                        </div>
                        <div class="input-group">
                            <i class='bx bxs-lock-alt'></i>
                            <input type="password" name="password" placeholder="Password" required>
                        </div>
                        <button type="submit">Sign in</button>
                        <p>
                            <b class="pointer" onclick="window.location.href='forgot_password.php'">Forgot password?</b>
                        </p>
                        <p>
                            <span>Don't have an account?</span>
                            <b onclick="toggle()" class="pointer">Sign up here</b>
                        </p>
                    </form>
                </div>
            </div>
            <!-- END SIGN IN -->
        </div>
        <!-- END FORM SECTION -->

        <!-- CONTENT SECTION -->
        <div class="row content-row">
            <!-- SIGN IN CONTENT -->
            <div class="col align-items-center flex-col">
                <div class="text sign-in">
                    <h2>Welcome Back</h2>
                    <p>MITAOE Faculty Portal</p>
                </div>
            </div>
            <!-- END SIGN IN CONTENT -->

            <!-- SIGN UP CONTENT -->
            <div class="col align-items-center flex-col">
                <div class="text sign-up">
                    <h2>Join With Us</h2>
                    <p>MITAOE Faculty Portal</p>
                </div>
            </div>
            <!-- END SIGN UP CONTENT -->
        </div>
        <!-- END CONTENT SECTION -->
    </div>

    <script>
        let container = document.getElementById('container');

        function toggle() {
            container.classList.toggle('sign-in');
            container.classList.toggle('sign-up');
        }

        setTimeout(() => {
            container.classList.add('sign-in');
        }, 200);

        // Sign In
        document.getElementById('signinForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const errorDiv = document.getElementById('signinError');
            const successDiv = document.getElementById('signinSuccess');
            
            errorDiv.style.display = 'none';
            successDiv.style.display = 'none';

            fetch('../php/faculty_login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    successDiv.textContent = data.message;
                    successDiv.style.display = 'block';
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1500);
                } else {
                    errorDiv.textContent = data.message;
                    errorDiv.style.display = 'block';
                }
            })
            .catch(error => {
                errorDiv.textContent = 'An error occurred. Please try again.';
                errorDiv.style.display = 'block';
            });
        });

        // Sign Up
        document.getElementById('signupForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const errorDiv = document.getElementById('signupError');
            const successDiv = document.getElementById('signupSuccess');
            
            errorDiv.style.display = 'none';
            successDiv.style.display = 'none';

            fetch('../php/faculty_signup.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    successDiv.textContent = data.message;
                    successDiv.style.display = 'block';
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1500);
                } else {
                    errorDiv.textContent = data.message;
                    errorDiv.style.display = 'block';
                }
            })
            .catch(error => {
                errorDiv.textContent = 'An error occurred. Please try again.';
                errorDiv.style.display = 'block';
            });
        });
    </script>
</body>
</html>