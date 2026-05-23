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

// Get faculty info
$faculty_id = $_SESSION['faculty_id'];
$query = "SELECT * FROM faculty WHERE id = ?";
$result = executeQuery($query, "i", array($faculty_id));

if ($result && $result->num_rows > 0) {
    $faculty = $result->fetch_assoc();
} else {
    header("Location: faculty_auth.php");
    exit();
}

// Parse professional links if exists
$professionalLinks = [];
if (!empty($faculty['professional_links'])) {
    $professionalLinks = json_decode($faculty['professional_links'], true) ?? [];
}

// Ensure at least one empty link field
if (empty($professionalLinks)) {
    $professionalLinks = [['platform' => '', 'url' => '']];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - MITAOE Faculty Portal</title>
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
            --accent: #2ecc71;
            --dark: #1a1a2e;
            --dark-light: #2d2d44;
            --white: #ffffff;
            --gray: #6c757d;
            --gray-light: #e9ecef;
            --gray-lighter: #f8f9fa;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --gradient-primary: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 10px 30px rgba(0, 0, 0, 0.12);
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

        /* Top Navigation Bar */
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
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        .page-title {
            font-size: 14px;
            color: var(--gray);
            font-weight: 500;
        }

        /* Main Container */
        .main-container {
            position: relative;
            z-index: 1;
            max-width: 1000px;
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
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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

        /* Form Container */
        .form-container {
            background: white;
            padding: 50px;
            border-radius: 30px;
            box-shadow: var(--shadow-xl);
            animation: fadeInUp 0.8s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Photo Upload Section */
        .photo-section {
            text-align: center;
            margin-bottom: 40px;
            padding: 40px;
            background: linear-gradient(135deg, rgba(78, 166, 133, 0.05), rgba(87, 184, 148, 0.05));
            border-radius: 20px;
        }

        .photo-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            margin: 0 auto 20px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 5px solid white;
            box-shadow: var(--shadow-lg);
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
            font-size: 60px;
            font-weight: 700;
            color: var(--primary);
            background: var(--gray-lighter);
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .upload-btn {
            display: inline-block;
            padding: 12px 30px;
            background: var(--gradient-primary);
            color: white;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(78, 166, 133, 0.3);
        }

        .upload-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(78, 166, 133, 0.4);
        }

        #photoInput {
            display: none;
        }

        /* Form Sections */
        .form-section {
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 20px;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid var(--gray-light);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-icon {
            font-size: 24px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 0;
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
            margin-left: 4px;
        }

        .form-control {
            width: 100%;
            padding: 14px 18px;
            font-size: 14px;
            border: 2px solid var(--gray-light);
            border-radius: 12px;
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
            background: var(--gray-lighter);
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
            font-weight: 600;
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
            background: var(--gray-lighter);
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
            font-weight: 700;
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
            border-radius: 12px;
            cursor: pointer;
            font-weight: 700;
            transition: all 0.3s ease;
            margin-top: 15px;
            font-family: 'Poppins', sans-serif;
        }

        .add-link-btn:hover {
            background: rgba(78, 166, 133, 0.1);
            border-style: solid;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 50px;
            padding-top: 40px;
            border-top: 3px solid var(--gray-light);
        }

        .btn {
            padding: 16px 45px;
            font-size: 16px;
            font-weight: 700;
            border: none;
            border-radius: 60px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 6px 20px rgba(78, 166, 133, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(78, 166, 133, 0.4);
        }

        .btn-secondary {
            background: var(--gray-lighter);
            color: var(--dark);
            box-shadow: var(--shadow-sm);
        }

        .btn-secondary:hover {
            background: var(--gray-light);
            transform: translateY(-3px);
        }

        /* Alert Messages */
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: none;
            animation: slideDown 0.3s ease;
            font-weight: 600;
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

        .loading-overlay.active {
            display: flex;
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
            font-size: 18px;
            font-weight: 600;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-container {
                padding: 30px 20px;
            }

            .form-container {
                padding: 30px 20px;
            }

            .link-item {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .nav-container {
                padding: 15px 20px;
            }

            .page-header h1 {
                font-size: 28px;
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
    </div>
    <!-- Top Navigation -->
    <div class="top-nav">
        <div class="nav-container">
            <a href="profile.php" class="nav-brand">
                <div class="back-icon">
                    <svg width="20" height="20" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                </div>
                <div>Back to Profile</div>
            </a>
            <div class="page-title">Edit your profile information</div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>✏️ Edit Profile</h1>
            <p>Update your personal and professional information</p>
        </div>

        <!-- Form Container -->
        <div class="form-container">
            <div id="alertMessage" class="alert"></div>

            <form id="editProfileForm" enctype="multipart/form-data">
                <!-- Photo Upload Section -->
                <div class="photo-section">
                    <div class="photo-preview" id="photoPreview">
                        <?php if (!empty($faculty['profile_photo']) && file_exists('../' . $faculty['profile_photo'])): ?>
                            <img src="../<?php echo htmlspecialchars($faculty['profile_photo']); ?>" alt="Profile Photo" id="currentPhoto">
                        <?php else: ?>
                            <div class="photo-placeholder" id="photoPlaceholder">
                                <?php echo strtoupper(substr($faculty['full_name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <label for="photoInput" class="upload-btn">
                        📷 Change Profile Photo
                    </label>
                    <input type="file" id="photoInput" name="profile_photo" accept="image/*">
                    <p style="margin-top: 15px; font-size: 13px; color: var(--gray);">Max size: 5MB | Formats: JPG, PNG, GIF</p>
                </div>

                <!-- Personal Information Section -->
                <div class="form-section">
                    <h2 class="section-title">
                        <span class="section-icon">👤</span>
                        <span>Personal Information</span>
                    </h2>
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
                            <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($faculty['phone'] ?? ''); ?>" placeholder="Enter 10-digit phone number" required maxlength="10">
                        </div>
                        <div class="form-group">
                            <label>WhatsApp Number</label>
                            <input type="tel" class="form-control" name="whatsapp" value="<?php echo htmlspecialchars($faculty['whatsapp'] ?? ''); ?>" placeholder="Enter WhatsApp number" maxlength="10">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Date of Birth <span class="required">*</span></label>
                            <input type="date" class="form-control" name="dob" value="<?php echo htmlspecialchars($faculty['date_of_birth'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Gender <span class="required">*</span></label>
                            <div class="radio-group">
                                <div class="radio-option">
                                    <input type="radio" id="male" name="gender" value="Male" <?php echo (isset($faculty['gender']) && $faculty['gender'] === 'Male') ? 'checked' : ''; ?> required>
                                    <label for="male">Male</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" id="female" name="gender" value="Female" <?php echo (isset($faculty['gender']) && $faculty['gender'] === 'Female') ? 'checked' : ''; ?> required>
                                    <label for="female">Female</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" id="other" name="gender" value="Other" <?php echo (isset($faculty['gender']) && $faculty['gender'] === 'Other') ? 'checked' : ''; ?> required>
                                    <label for="other">Other</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <textarea class="form-control" name="address" placeholder="Enter your complete address"><?php echo htmlspecialchars($faculty['address'] ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- Professional Information Section -->
                <div class="form-section">
                    <h2 class="section-title">
                        <span class="section-icon">💼</span>
                        <span>Professional Information</span>
                    </h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Department <span class="required">*</span></label>
                            <select class="form-control" name="department" required>
                                <option value="">Select Department</option>
                                <option value="Computer Engineering" <?php echo (isset($faculty['department']) && $faculty['department'] === 'Computer Engineering') ? 'selected' : ''; ?>>Computer Engineering</option>
                                <option value="Mechanical Engineering" <?php echo (isset($faculty['department']) && $faculty['department'] === 'Mechanical Engineering') ? 'selected' : ''; ?>>Mechanical Engineering</option>
                                <option value="Civil Engineering" <?php echo (isset($faculty['department']) && $faculty['department'] === 'Civil Engineering') ? 'selected' : ''; ?>>Civil Engineering</option>
                                <option value="Electrical Engineering" <?php echo (isset($faculty['department']) && $faculty['department'] === 'Electrical Engineering') ? 'selected' : ''; ?>>Electrical Engineering</option>
                                <option value="Electronics Engineering" <?php echo (isset($faculty['department']) && $faculty['department'] === 'Electronics Engineering') ? 'selected' : ''; ?>>Electronics Engineering</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Designation <span class="required">*</span></label>
                            <select class="form-control" name="designation" required>
                                <option value="">Select Designation</option>
                                <option value="Professor" <?php echo (isset($faculty['designation']) && $faculty['designation'] === 'Professor') ? 'selected' : ''; ?>>Professor</option>
                                <option value="Associate Professor" <?php echo (isset($faculty['designation']) && $faculty['designation'] === 'Associate Professor') ? 'selected' : ''; ?>>Associate Professor</option>
                                <option value="Assistant Professor" <?php echo (isset($faculty['designation']) && $faculty['designation'] === 'Assistant Professor') ? 'selected' : ''; ?>>Assistant Professor</option>
                                <option value="Lecturer" <?php echo (isset($faculty['designation']) && $faculty['designation'] === 'Lecturer') ? 'selected' : ''; ?>>Lecturer</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Total Experience (in years) <span class="required">*</span></label>
                        <input type="text" class="form-control" name="experience" value="<?php echo htmlspecialchars($faculty['experience'] ?? ''); ?>" placeholder="e.g., 5 years" required>
                    </div>
                </div>

                <!-- Academic Qualifications Section -->
                <div class="form-section">
                    <h2 class="section-title">
                        <span class="section-icon">🎓</span>
                        <span>Academic Qualifications</span>
                    </h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Highest Qualification <span class="required">*</span></label>
                            <select class="form-control" name="qualification" required>
                                <option value="">Select Qualification</option>
                                <option value="PhD" <?php echo (isset($faculty['qualification']) && $faculty['qualification'] === 'PhD') ? 'selected' : ''; ?>>PhD</option>
                                <option value="Post Graduation" <?php echo (isset($faculty['qualification']) && $faculty['qualification'] === 'Post Graduation') ? 'selected' : ''; ?>>Post Graduation</option>
                                <option value="Graduation" <?php echo (isset($faculty['qualification']) && $faculty['qualification'] === 'Graduation') ? 'selected' : ''; ?>>Graduation</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Specialization</label>
                            <input type="text" class="form-control" name="specialization" value="<?php echo htmlspecialchars($faculty['specialization'] ?? ''); ?>" placeholder="e.g., Computer Science">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>College/University</label>
                            <input type="text" class="form-control" name="college" value="<?php echo htmlspecialchars($faculty['college'] ?? ''); ?>" placeholder="Enter college/university name">
                        </div>
                        <div class="form-group">
                            <label>Year of Passing</label>
                            <input type="text" class="form-control" name="year_of_passing" value="<?php echo htmlspecialchars($faculty['year_of_passing'] ?? ''); ?>" placeholder="e.g., 2020" maxlength="4">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>AICTE Internship ID</label>
                            <input type="text" class="form-control" name="aicte_id" value="<?php echo htmlspecialchars($faculty['aicte_id'] ?? ''); ?>" placeholder="Enter AICTE ID">
                        </div>
                        <div class="form-group">
                            <label>APAAR ID</label>
                            <input type="text" class="form-control" name="apaar_id" value="<?php echo htmlspecialchars($faculty['apaar_id'] ?? ''); ?>" placeholder="Enter APAAR ID">
                        </div>
                    </div>
                </div>

                <!-- Professional Links Section -->
                <div class="form-section">
                    <h2 class="section-title">
                        <span class="section-icon">🔗</span>
                        <span>Professional Links</span>
                    </h2>
                    <div id="linksContainer" class="links-container">
                        <?php foreach ($professionalLinks as $index => $link): ?>
                            <div class="link-item">
                                <div class="form-group">
                                    <label>Platform</label>
                                    <select class="form-control platform-select">
                                        <option value="">Select Platform</option>
                                        <option value="LinkedIn" <?php echo ($link['platform'] === 'LinkedIn') ? 'selected' : ''; ?>>LinkedIn</option>
                                        <option value="GitHub" <?php echo ($link['platform'] === 'GitHub') ? 'selected' : ''; ?>>GitHub</option>
                                        <option value="Twitter" <?php echo ($link['platform'] === 'Twitter') ? 'selected' : ''; ?>>Twitter</option>
                                        <option value="Facebook" <?php echo ($link['platform'] === 'Facebook') ? 'selected' : ''; ?>>Facebook</option>
                                        <option value="Instagram" <?php echo ($link['platform'] === 'Instagram') ? 'selected' : ''; ?>>Instagram</option>
                                        <option value="Medium" <?php echo ($link['platform'] === 'Medium') ? 'selected' : ''; ?>>Medium</option>
                                        <option value="Portfolio" <?php echo ($link['platform'] === 'Portfolio') ? 'selected' : ''; ?>>Portfolio Website</option>
                                        <option value="ResearchGate" <?php echo ($link['platform'] === 'ResearchGate') ? 'selected' : ''; ?>>ResearchGate</option>
                                        <option value="Google Scholar" <?php echo ($link['platform'] === 'Google Scholar') ? 'selected' : ''; ?>>Google Scholar</option>
                                        <option value="Other" <?php echo ($link['platform'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Profile URL</label>
                                    <input type="url" class="form-control url-input" value="<?php echo htmlspecialchars($link['url'] ?? ''); ?>" placeholder="https://example.com/yourprofile">
                                </div>
                                <button type="button" class="remove-link" onclick="removeLink(this)" style="<?php echo ($index === 0 && count($professionalLinks) === 1) ? 'display: none;' : ''; ?>">✕ Remove</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="add-link-btn" onclick="addLink()">
                        ➕ Add Another Link
                    </button>
                </div>

                <!-- Bio Section -->
                <div class="form-section">
                    <h2 class="section-title">
                        <span class="section-icon">📝</span>
                        <span>About You</span>
                    </h2>
                    <div class="form-group">
                        <label>Short Bio</label>
                        <textarea class="form-control" name="bio" placeholder="Tell us about yourself, your interests, areas of expertise, and achievements..." style="min-height: 150px;"><?php echo htmlspecialchars($faculty['bio'] ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='profile.php'">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M19 12H5M12 19l-7-7 7-7"/>
                        </svg>
                        <span>Cancel</span>
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        <span>Save Changes</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <div class="loading-text">Updating your profile...</div>
        </div>
    </div>

    <script>
        // Photo Upload Preview
        document.getElementById('photoInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file size (max 5MB)
                if (file.size > 5242880) {
                    showAlert('Photo size must be less than 5MB!', 'danger');
                    this.value = '';
                    return;
                }

                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    showAlert('Only JPG, JPEG, PNG, and GIF files are allowed!', 'danger');
                    this.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(event) {
                    const preview = document.getElementById('photoPreview');
                    preview.innerHTML = `<img src="${event.target.result}" alt="Profile Photo" id="currentPhoto">`;
                };
                reader.readAsDataURL(file);
            }
        });

        // Add Professional Link
        function addLink() {
            const container = document.getElementById('linksContainer');
            const linkCount = container.children.length;

            if (linkCount >= 5) {
                showAlert('You can add maximum 5 professional links.', 'danger');
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

        // Form Submit
        document.getElementById('editProfileForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            // Validate required fields
            const phone = document.querySelector('input[name="phone"]').value;
            if (phone && !/^\d{10}$/.test(phone)) {
                showAlert('Please enter a valid 10-digit phone number.', 'danger');
                return;
            }

            const formData = new FormData(this);
            
            // Collect professional links
            const platforms = document.querySelectorAll('.platform-select');
            const urls = document.querySelectorAll('.url-input');
            const professionalLinks = [];
            
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
            document.getElementById('loadingOverlay').classList.add('active');

            try {
                const response = await fetch('../php/update_profile.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                document.getElementById('loadingOverlay').classList.remove('active');

                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => {
                        window.location.href = 'profile.php';
                    }, 2000);
                } else {
                    showAlert(data.message || 'Failed to update profile. Please try again.', 'danger');
                }
            } catch (error) {
                document.getElementById('loadingOverlay').classList.remove('active');
                showAlert('An error occurred. Please try again.', 'danger');
                console.error('Error:', error);
            }
        });

        // Show Alert
        function showAlert(message, type) {
            const alertDiv = document.getElementById('alertMessage');
            alertDiv.textContent = message;
            alertDiv.className = `alert alert-${type} active`;
            
            setTimeout(() => {
                alertDiv.classList.remove('active');
            }, 5000);

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Set max date for DOB (18 years ago)
        const today = new Date();
        const maxDate = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());
        document.querySelector('input[name="dob"]').max = maxDate.toISOString().split('T')[0];
    </script>
</body>
</html>