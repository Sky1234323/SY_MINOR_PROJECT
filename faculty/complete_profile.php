<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../php/db_connect.php';

// Check if faculty is logged in
if (!isset($_SESSION['faculty_id'])) {
    header("Location: faculty_auth.php");
    exit();
}

// Check if profile already completed
$faculty_id = $_SESSION['faculty_id'];
$query = "SELECT profile_completed, full_name, email FROM faculty WHERE id = ?";
$result = executeQuery($query, "i", array($faculty_id));

if ($result && $result->num_rows > 0) {
    $faculty = $result->fetch_assoc();
    if ($faculty['profile_completed'] == 1) {
        header("Location: faculty_dashboard.php");
        exit();
    }
} else {
    header("Location: faculty_auth.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Profile - MITAOE Faculty Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #4EA685;
            --secondary: #57B894;
            --dark: #1a1a2e;
            --light: #f8f9fa;
            --white: #ffffff;
            --gray: #6c757d;
            --gray-light: #e9ecef;
            --success: #28a745;
            --danger: #dc3545;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #ffffff;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        /* Top Navigation Bar */
        .top-nav {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-brand {
            font-size: 24px;
            font-weight: 700;
        }

        .nav-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .nav-user span {
            font-size: 14px;
            opacity: 0.9;
        }

        /* Main Container */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 30px;
        }

        /* Page Header */
        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-header h1 {
            font-size: 32px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 10px;
        }

        .page-header p {
            font-size: 16px;
            color: var(--gray);
        }

        /* Progress Steps */
        .progress-container {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .progress-steps {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            max-width: 800px;
            margin: 0 auto;
        }

        .progress-line {
            position: absolute;
            top: 24px;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gray-light);
            z-index: 0;
        }

        .progress-line-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            transition: width 0.4s ease;
            border-radius: 4px;
        }

        .step {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
        }

        .step-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: white;
            border: 4px solid var(--gray-light);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 18px;
            color: var(--gray);
            transition: all 0.3s ease;
        }

        .step.active .step-circle {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-color: var(--primary);
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(78, 166, 133, 0.4);
        }

        .step.completed .step-circle {
            background: var(--success);
            color: white;
            border-color: var(--success);
        }

        .step-label {
            margin-top: 12px;
            font-size: 14px;
            font-weight: 600;
            color: var(--gray);
            text-align: center;
        }

        .step.active .step-label {
            color: var(--primary);
        }

        .step.completed .step-label {
            color: var(--success);
        }

        /* Form Container */
        .form-container {
            background: white;
            padding: 50px;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            max-width: 1200px;
            margin: 0 auto;
        }

        .form-step {
            display: none;
            animation: fadeIn 0.4s ease;
        }

        .form-step.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .step-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 10px;
        }

        .step-subtitle {
            font-size: 15px;
            color: var(--gray);
            margin-bottom: 40px;
        }

        /* Photo Upload */
        .photo-upload-section {
            text-align: center;
            margin-bottom: 50px;
            padding: 40px;
            background: linear-gradient(135deg, rgba(78, 166, 133, 0.05), rgba(87, 184, 148, 0.05));
            border-radius: 16px;
        }

        .photo-preview {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            margin: 0 auto 25px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 5px solid white;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            position: relative;
            transition: transform 0.3s ease;
        }

        .photo-preview:hover {
            transform: scale(1.05);
        }

        .photo-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-placeholder {
            font-size: 80px;
            color: var(--gray-light);
        }

        .upload-btn {
            display: inline-block;
            padding: 14px 35px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(78, 166, 133, 0.3);
            border: none;
        }

        .upload-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(78, 166, 133, 0.4);
        }

        #photoInput {
            display: none;
        }

        /* Form Groups */
        .form-section {
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--gray-light);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 0;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 10px;
        }

        .form-group label .required {
            color: var(--danger);
            margin-left: 4px;
        }

        .form-control {
            width: 100%;
            padding: 14px 18px;
            font-size: 14px;
            border: 2px solid var(--gray-light);
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(78, 166, 133, 0.1);
        }

        .form-control:disabled {
            background: var(--light);
            cursor: not-allowed;
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        /* Radio Buttons */
        .radio-group {
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .radio-option input[type="radio"] {
            width: 22px;
            height: 22px;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .radio-option label {
            margin: 0;
            cursor: pointer;
            font-weight: 500;
        }

        /* Professional Links */
        .links-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .link-item {
            display: grid;
            grid-template-columns: 250px 1fr auto;
            gap: 20px;
            align-items: start;
            padding: 20px;
            background: var(--light);
            border-radius: 12px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .remove-link {
            padding: 10px 20px;
            background: var(--danger);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: 28px;
            font-family: 'Poppins', sans-serif;
        }

        .remove-link:hover {
            background: #c82333;
            transform: scale(1.05);
        }

        .add-link-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 28px;
            background: white;
            border: 2px dashed var(--primary);
            color: var(--primary);
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: 15px;
            font-family: 'Poppins', sans-serif;
        }

        .add-link-btn:hover {
            background: rgba(78, 166, 133, 0.1);
            border-style: solid;
        }

        /* Review Section */
        .review-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 25px;
        }

        .review-card {
            background: var(--light);
            padding: 30px;
            border-radius: 12px;
            border-left: 5px solid var(--primary);
        }

        .review-card h3 {
            font-size: 20px;
            color: var(--primary);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .review-item {
            margin: 15px 0;
            padding: 12px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .review-item:last-child {
            border-bottom: none;
        }

        .review-label {
            font-size: 13px;
            color: var(--gray);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .review-value {
            font-size: 15px;
            color: var(--dark);
            font-weight: 500;
        }

        /* Buttons */
        .button-group {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-top: 50px;
            padding-top: 30px;
            border-top: 2px solid var(--gray-light);
        }

        .btn {
            padding: 16px 40px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }

        .btn-prev {
            background: var(--gray-light);
            color: var(--dark);
        }

        .btn-prev:hover:not(:disabled) {
            background: #dee2e6;
            transform: translateX(-5px);
        }

        .btn-next, .btn-submit {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            box-shadow: 0 4px 15px rgba(78, 166, 133, 0.3);
        }

        .btn-next:hover:not(:disabled), .btn-submit:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(78, 166, 133, 0.4);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Success Message */
        .success-message {
            display: none;
            text-align: center;
            padding: 80px 40px;
        }

        .success-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, var(--success), #20c997);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: scaleIn 0.6s ease;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0) rotate(0deg);
            }
            to {
                transform: scale(1) rotate(360deg);
            }
        }

        .success-icon svg {
            width: 60px;
            height: 60px;
            stroke: white;
            stroke-width: 4;
            stroke-linecap: round;
            stroke-linejoin: round;
            fill: none;
        }

        .success-message h2 {
            font-size: 32px;
            color: var(--dark);
            margin-bottom: 15px;
        }

        .success-message p {
            font-size: 18px;
            color: var(--gray);
            margin-bottom: 30px;
        }

        /* Alert Messages */
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: none;
            animation: slideDown 0.3s ease;
            font-weight: 500;
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
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #f5c6cb;
        }

        /* Loading Overlay */
        .loading-overlay {
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

        .loading-content {
            text-align: center;
        }

        .loading-spinner {
            width: 70px;
            height: 70px;
            border: 5px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .loading-text {
            color: white;
            font-size: 16px;
            font-weight: 500;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-container {
                padding: 20px 15px;
            }

            .form-container {
                padding: 30px 20px;
            }

            .progress-container {
                padding: 25px 15px;
            }

            .link-item {
                grid-template-columns: 1fr;
            }

            .review-grid {
                grid-template-columns: 1fr;
            }

            .button-group {
                flex-direction: column-reverse;
            }

            .btn {
                width: 100%;
            }

            .step-label {
                font-size: 11px;
            }

            .step-circle {
                width: 40px;
                height: 40px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <div class="top-nav">
        <div class="nav-container">
            <div class="nav-brand">🎓 MITAOE Faculty Portal</div>
            <div class="nav-user">
                <span>Welcome, <strong><?php echo htmlspecialchars($faculty['full_name']); ?></strong></span>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>Complete Your Profile</h1>
            <p>Please fill in all the required information to complete your faculty profile setup</p>
        </div>

        <!-- Progress Steps -->
        <div class="progress-container">
            <div class="progress-steps">
                <div class="progress-line">
                    <div class="progress-line-fill" id="progressFill" style="width: 25%;"></div>
                </div>
                <div class="step active" data-step="1">
                    <div class="step-circle">1</div>
                    <div class="step-label">Basic Info</div>
                </div>
                <div class="step" data-step="2">
                    <div class="step-circle">2</div>
                    <div class="step-label">Education</div>
                </div>
                <div class="step" data-step="3">
                    <div class="step-circle">3</div>
                    <div class="step-label">Professional</div>
                </div>
                <div class="step" data-step="4">
                    <div class="step-circle">4</div>
                    <div class="step-label">Review</div>
                </div>
            </div>
        </div>

        <!-- Form Container -->
        <div class="form-container">
            <div id="errorMessage" class="alert alert-danger"></div>
            
            <form id="profileForm" enctype="multipart/form-data">
                <!-- Step 1: Basic Information -->
                <div class="form-step active" data-step="1">
                    <h2 class="step-title">Basic Information</h2>
                    <p class="step-subtitle">Tell us about yourself and your contact details</p>

                    <!-- Photo Upload -->
                    <div class="photo-upload-section">
                        <div class="photo-preview" id="photoPreview">
                            <span class="photo-placeholder">👤</span>
                        </div>
                        <label for="photoInput" class="upload-btn">
                            📷 Upload Profile Photo
                        </label>
                        <input type="file" id="photoInput" name="profile_photo" accept="image/*">
                        <p style="margin-top: 15px; font-size: 13px; color: var(--gray);">Max size: 5MB | Formats: JPG, PNG, GIF</p>
                    </div>

                    <!-- Personal Details -->
                    <div class="form-section">
                        <h3 class="section-title">📋 Personal Details</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Full Name <span class="required">*</span></label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($faculty['full_name']); ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label>Email Address <span class="required">*</span></label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($faculty['email']); ?>" disabled>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Phone Number <span class="required">*</span></label>
                                <input type="tel" class="form-control" name="phone" id="phone" placeholder="Enter 10-digit phone number" required maxlength="10">
                            </div>
                            <div class="form-group">
                                <label>WhatsApp Number</label>
                                <input type="tel" class="form-control" name="whatsapp" placeholder="Enter WhatsApp number" maxlength="10">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Date of Birth <span class="required">*</span></label>
                                <input type="date" class="form-control" name="dob" id="dob" required>
                            </div>
                            <div class="form-group">
                                <label>Gender <span class="required">*</span></label>
                                <div class="radio-group">
                                    <div class="radio-option">
                                        <input type="radio" id="male" name="gender" value="Male" required>
                                        <label for="male">Male</label>
                                    </div>
                                    <div class="radio-option">
                                        <input type="radio" id="female" name="gender" value="Female" required>
                                        <label for="female">Female</label>
                                    </div>
                                    <div class="radio-option">
                                        <input type="radio" id="other" name="gender" value="Other" required>
                                        <label for="other">Other</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Professional Details -->
                    <div class="form-section">
                        <h3 class="section-title">💼 Professional Details</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Department <span class="required">*</span></label>
                                <select class="form-control" name="department" id="department" required>
                                    <option value="">Select Department</option>
                                    <option value="Computer Engineering">Computer Engineering</option>
                                    <option value="Mechanical Engineering">Mechanical Engineering</option>
                                    <option value="Civil Engineering">Civil Engineering</option>
                                    <option value="Electrical Engineering">Electrical Engineering</option>
                                    <option value="Electronics Engineering">Electronics Engineering</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Designation <span class="required">*</span></label>
                                <select class="form-control" name="designation" id="designation" required>
                                    <option value="">Select Designation</option>
                                    <option value="Professor">Professor</option>
                                    <option value="Associate Professor">Associate Professor</option>
                                    <option value="Assistant Professor">Assistant Professor</option>
                                    <option value="Lecturer">Lecturer</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Address</label>
                            <textarea class="form-control" name="address" placeholder="Enter your complete address"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Education Details -->
                <div class="form-step" data-step="2">
                    <h2 class="step-title">Education Details</h2>
                    <p class="step-subtitle">Your academic qualifications and experience</p>

                    <div class="form-section">
                        <h3 class="section-title">🎓 Academic Qualifications</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Highest Qualification <span class="required">*</span></label>
                                <select class="form-control" name="qualification" id="qualification" required>
                                    <option value="">Select Qualification</option>
                                    <option value="PhD">PhD</option>
                                    <option value="Post Graduation">Post Graduation</option>
                                    <option value="Graduation">Graduation</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Specialization</label>
                                <input type="text" class="form-control" name="specialization" placeholder="e.g., Computer Science, Artificial Intelligence">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>College/University</label>
                                <input type="text" class="form-control" name="college" placeholder="Enter college/university name">
                            </div>
                            <div class="form-group">
                                <label>Year of Passing</label>
                                <input type="text" class="form-control" name="year_of_passing" placeholder="e.g., 2020" maxlength="4">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Total Experience (in years) <span class="required">*</span></label>
                            <input type="text" class="form-control" name="experience" id="experience" placeholder="e.g., 5 years" required>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">🆔 Additional IDs</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label>AICTE Internship ID</label>
                                <input type="text" class="form-control" name="aicte_id" placeholder="Enter AICTE Internship ID">
                            </div>
                            <div class="form-group">
                                <label>APAAR ID</label>
                                <input type="text" class="form-control" name="apaar_id" placeholder="Enter APAAR ID">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Professional Links -->
                <div class="form-step" data-step="3">
                    <h2 class="step-title">Professional Links</h2>
                    <p class="step-subtitle">Add your professional social media profiles and portfolio (optional)</p>

                    <div class="form-section">
                        <h3 class="section-title">🔗 Social Media & Portfolio Links</h3>
                        <div id="linksContainer" class="links-container">
                            <div class="link-item">
                                <div class="form-group">
                                    <label>Platform</label>
                                    <select class="form-control platform-select">
                                        <option value="">Select Platform</option>
                                        <option value="LinkedIn">LinkedIn</option>
                                        <option value="GitHub">GitHub</option>
                                        <option value="Twitter">Twitter</option>
                                        <option value="Facebook">Facebook</option>
                                        <option value="Instagram">Instagram</option>
                                        <option value="Medium">Medium</option>
                                        <option value="Portfolio">Portfolio Website</option>
                                        <option value="ResearchGate">ResearchGate</option>
                                        <option value="Google Scholar">Google Scholar</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Profile URL</label>
                                    <input type="url" class="form-control url-input" placeholder="https://example.com/yourprofile">
                                </div>
                                <button type="button" class="remove-link" onclick="removeLink(this)" style="display: none;">✕ Remove</button>
                            </div>
                        </div>

                        <button type="button" class="add-link-btn" onclick="addLink()">
                            ➕ Add Another Link
                        </button>
                    </div>

                    <div class="form-section" style="margin-top: 40px;">
                        <h3 class="section-title">📝 About You</h3>
                        <div class="form-group">
                            <label>Short Bio</label>
                            <textarea class="form-control" name="bio" placeholder="Tell us about yourself, your interests, areas of expertise, and achievements..." style="min-height: 150px;"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Review & Submit -->
                <div class="form-step" data-step="4">
                    <h2 class="step-title">Review Your Information</h2>
                    <p class="step-subtitle">Please review all details carefully before submitting</p>

                    <div id="reviewContent" class="review-grid">
                        <!-- Review content will be populated by JavaScript -->
                    </div>
                </div>

                <!-- Button Navigation -->
                <div class="button-group">
                    <button type="button" class="btn btn-prev" id="prevBtn" onclick="changeStep(-1)" style="display: none;">
                        ← Previous Step
                    </button>
                    <button type="button" class="btn btn-next" id="nextBtn" onclick="changeStep(1)">
                        Next Step →
                    </button>
                    <button type="submit" class="btn btn-submit" id="submitBtn" style="display: none;">
                        💾 Save Profile & Continue
                    </button>
                </div>
            </form>

            <!-- Success Message -->
            <div class="success-message" id="successMessage">
                <div class="success-icon">
                    <svg viewBox="0 0 52 52">
                        <path d="M14 27l7.5 7.5L38 18"/>
                    </svg>
                </div>
                <h2>Profile Completed Successfully! 🎉</h2>
                <p>Your profile has been saved. Redirecting you to the dashboard...</p>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <div class="loading-text">Saving your profile...</div>
        </div>
    </div>
    <script>
        let currentStep = 1;
        const totalSteps = 4;
        let professionalLinks = [];

        // Photo Upload Preview with Validation
        document.getElementById('photoInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file size (max 5MB)
                if (file.size > 5242880) {
                    showError('Photo size must be less than 5MB!');
                    this.value = '';
                    return;
                }

                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    showError('Only JPG, JPEG, PNG, and GIF files are allowed!');
                    this.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(event) {
                    const preview = document.getElementById('photoPreview');
                    preview.innerHTML = `<img src="${event.target.result}" alt="Profile Photo">`;
                };
                reader.readAsDataURL(file);
            }
        });

        // Change Step Function
        function changeStep(direction) {
            const currentStepEl = document.querySelector(`.form-step[data-step="${currentStep}"]`);
            
            // Validate current step before moving forward
            if (direction === 1) {
                if (!validateStep(currentStep)) {
                    return;
                }
            }

            // Mark current step as completed
            const currentStepCircle = document.querySelector(`.step[data-step="${currentStep}"]`);
            currentStepCircle.classList.remove('active');
            if (direction === 1) {
                currentStepCircle.classList.add('completed');
            }

            // Hide current step
            currentStepEl.classList.remove('active');

            // Update current step
            currentStep += direction;

            // Show new step
            const newStepEl = document.querySelector(`.form-step[data-step="${currentStep}"]`);
            newStepEl.classList.add('active');
            
            const newStepCircle = document.querySelector(`.step[data-step="${currentStep}"]`);
            newStepCircle.classList.add('active');

            // Update progress bar
            const progress = (currentStep / totalSteps) * 100;
            document.getElementById('progressFill').style.width = progress + '%';

            // Update buttons
            document.getElementById('prevBtn').style.display = currentStep === 1 ? 'none' : 'block';
            document.getElementById('nextBtn').style.display = currentStep === totalSteps ? 'none' : 'block';
            document.getElementById('submitBtn').style.display = currentStep === totalSteps ? 'block' : 'none';

            // If on review step, populate review content
            if (currentStep === 4) {
                populateReview();
            }

            // Scroll to top smoothly
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Validate Step
        function validateStep(step) {
            const stepEl = document.querySelector(`.form-step[data-step="${step}"]`);
            const inputs = stepEl.querySelectorAll('input[required], select[required], textarea[required]');
            
            for (let input of inputs) {
                if (input.type === 'radio') {
                    const radioGroup = stepEl.querySelectorAll(`input[name="${input.name}"]`);
                    const isChecked = Array.from(radioGroup).some(radio => radio.checked);
                    if (!isChecked) {
                        showError(`Please select your ${input.name}.`);
                        return false;
                    }
                } else {
                    if (!input.value.trim()) {
                        showError(`Please fill in all required fields.`);
                        input.focus();
                        return false;
                    }
                }
            }

            // Additional validation for step 1
            if (step === 1) {
                const phone = document.getElementById('phone').value;
                if (phone && !/^\d{10}$/.test(phone)) {
                    showError('Please enter a valid 10-digit phone number.');
                    document.getElementById('phone').focus();
                    return false;
                }
            }

            return true;
        }

        // Add Professional Link
        function addLink() {
            const container = document.getElementById('linksContainer');
            const linkCount = container.children.length;

            if (linkCount >= 5) {
                showError('You can add maximum 5 professional links.');
                return;
            }

            const linkItem = document.createElement('div');
            linkItem.className = 'link-item';
            linkItem.innerHTML = `
                <div class="form-group">
                    <label>Platform</label>
                    <select class="form-control platform-select">
                        <option value="">Select Platform</option>
                        <option value="LinkedIn">LinkedIn</option>
                        <option value="GitHub">GitHub</option>
                        <option value="Twitter">Twitter</option>
                        <option value="Facebook">Facebook</option>
                        <option value="Instagram">Instagram</option>
                        <option value="Medium">Medium</option>
                        <option value="Portfolio">Portfolio Website</option>
                        <option value="ResearchGate">ResearchGate</option>
                        <option value="Google Scholar">Google Scholar</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Profile URL</label>
                    <input type="url" class="form-control url-input" placeholder="https://example.com/yourprofile">
                </div>
                <button type="button" class="remove-link" onclick="removeLink(this)">✕ Remove</button>
            `;
            container.appendChild(linkItem);

            // Show remove button on first item when second is added
            if (linkCount === 1) {
                container.children[0].querySelector('.remove-link').style.display = 'block';
            }
        }

        // Remove Professional Link
        function removeLink(button) {
            const container = document.getElementById('linksContainer');
            const linkCount = container.children.length;
            
            if (linkCount > 1) {
                button.closest('.link-item').remove();
                
                // Hide remove button if only one link remains
                if (container.children.length === 1) {
                    container.children[0].querySelector('.remove-link').style.display = 'none';
                }
            }
        }

        // Populate Review Content
        function populateReview() {
            const form = document.getElementById('profileForm');
            const formData = new FormData(form);
            let reviewHTML = '';

            // Basic Information Card
            reviewHTML += `<div class="review-card">`;
            reviewHTML += `<h3>📋 Basic Information</h3>`;
            reviewHTML += `<div class="review-item"><div class="review-label">Phone Number</div><div class="review-value">${formData.get('phone') || 'Not provided'}</div></div>`;
            reviewHTML += `<div class="review-item"><div class="review-label">WhatsApp Number</div><div class="review-value">${formData.get('whatsapp') || 'Not provided'}</div></div>`;
            reviewHTML += `<div class="review-item"><div class="review-label">Date of Birth</div><div class="review-value">${formData.get('dob') || 'Not provided'}</div></div>`;
            reviewHTML += `<div class="review-item"><div class="review-label">Gender</div><div class="review-value">${formData.get('gender') || 'Not provided'}</div></div>`;
            reviewHTML += `<div class="review-item"><div class="review-label">Department</div><div class="review-value">${formData.get('department') || 'Not provided'}</div></div>`;
            reviewHTML += `<div class="review-item"><div class="review-label">Designation</div><div class="review-value">${formData.get('designation') || 'Not provided'}</div></div>`;
            if (formData.get('address')) {
                reviewHTML += `<div class="review-item"><div class="review-label">Address</div><div class="review-value">${formData.get('address')}</div></div>`;
            }
            reviewHTML += `</div>`;

            // Education Details Card
            reviewHTML += `<div class="review-card">`;
            reviewHTML += `<h3>🎓 Education Details</h3>`;
            reviewHTML += `<div class="review-item"><div class="review-label">Highest Qualification</div><div class="review-value">${formData.get('qualification') || 'Not provided'}</div></div>`;
            if (formData.get('specialization')) {
                reviewHTML += `<div class="review-item"><div class="review-label">Specialization</div><div class="review-value">${formData.get('specialization')}</div></div>`;
            }
            if (formData.get('college')) {
                reviewHTML += `<div class="review-item"><div class="review-label">College/University</div><div class="review-value">${formData.get('college')}</div></div>`;
            }
            if (formData.get('year_of_passing')) {
                reviewHTML += `<div class="review-item"><div class="review-label">Year of Passing</div><div class="review-value">${formData.get('year_of_passing')}</div></div>`;
            }
            reviewHTML += `<div class="review-item"><div class="review-label">Total Experience</div><div class="review-value">${formData.get('experience') || 'Not provided'}</div></div>`;
            if (formData.get('aicte_id')) {
                reviewHTML += `<div class="review-item"><div class="review-label">AICTE ID</div><div class="review-value">${formData.get('aicte_id')}</div></div>`;
            }
            if (formData.get('apaar_id')) {
                reviewHTML += `<div class="review-item"><div class="review-label">APAAR ID</div><div class="review-value">${formData.get('apaar_id')}</div></div>`;
            }
            reviewHTML += `</div>`;

            // Professional Links Card
            const platforms = document.querySelectorAll('.platform-select');
            const urls = document.querySelectorAll('.url-input');
            let linksHTML = '';
            
            platforms.forEach((platform, index) => {
                if (platform.value && urls[index].value) {
                    linksHTML += `<div class="review-item"><div class="review-label">${platform.value}</div><div class="review-value"><a href="${urls[index].value}" target="_blank" style="color: var(--primary); text-decoration: none;">${urls[index].value}</a></div></div>`;
                }
            });

            reviewHTML += `<div class="review-card">`;
            reviewHTML += `<h3>🔗 Professional Links</h3>`;
            if (linksHTML) {
                reviewHTML += linksHTML;
            } else {
                reviewHTML += '<div class="review-item"><div class="review-value">No professional links added</div></div>';
            }
            if (formData.get('bio')) {
                reviewHTML += `<div class="review-item"><div class="review-label">Bio</div><div class="review-value">${formData.get('bio')}</div></div>`;
            }
            reviewHTML += `</div>`;

            document.getElementById('reviewContent').innerHTML = reviewHTML;
        }

        // Form Submit
        document.getElementById('profileForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            
            // Collect professional links
            const platforms = document.querySelectorAll('.platform-select');
            const urls = document.querySelectorAll('.url-input');
            professionalLinks = [];
            
            platforms.forEach((platform, index) => {
                if (platform.value && urls[index].value) {
                    professionalLinks.push({
                        platform: platform.value,
                        url: urls[index].value
                    });
                }
            });

            formData.append('professional_links', JSON.stringify(professionalLinks));

            // Show loading
            document.getElementById('loadingOverlay').style.display = 'flex';

            try {
                const response = await fetch('../php/complete_profile.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                document.getElementById('loadingOverlay').style.display = 'none';

                if (data.success) {
                    // Hide form, show success message
                    document.querySelector('.form-container form').style.display = 'none';
                    document.querySelector('.progress-container').style.display = 'none';
                    document.getElementById('successMessage').style.display = 'block';

                    // Redirect to dashboard after 3 seconds
                    setTimeout(() => {
                        window.location.href = 'faculty_dashboard.php';
                    }, 3000);
                } else {
                    showError(data.message || 'Failed to save profile. Please try again.');
                }
            } catch (error) {
                document.getElementById('loadingOverlay').style.display = 'none';
                showError('An error occurred. Please try again.');
                console.error('Error:', error);
            }
        });

        // Show Error Message
        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            
            setTimeout(() => {
                errorDiv.style.display = 'none';
            }, 5000);

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Set max date for DOB (18 years ago)
        const today = new Date();
        const maxDate = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());
        document.getElementById('dob').max = maxDate.toISOString().split('T')[0];
    </script>
</body>
</html>