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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - MITAOE Faculty Portal</title>
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

        .nav-actions {
            display: flex;
            gap: 15px;
        }

        .nav-btn {
            padding: 12px 28px;
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(78, 166, 133, 0.3);
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .nav-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(78, 166, 133, 0.4);
        }

        .nav-btn.btn-danger {
            background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }

        .nav-btn.btn-danger:hover {
            box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
        }

        /* Main Container */
        .main-container {
            position: relative;
            z-index: 1;
            max-width: 1200px;
            margin: 0 auto;
            padding: 50px 40px;
        }

        /* Profile Header Card */
        .profile-header {
            background: white;
            padding: 50px;
            border-radius: 30px;
            box-shadow: var(--shadow-xl);
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
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

        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 180px;
            background: white;
            border-radius: 30px 30px 0 0;
        }

        .profile-header-content {
            position: relative;
            display: flex;
            align-items: flex-end;
            gap: 35px;
        }

        .profile-photo {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: white;
            border: 8px solid white;
            box-shadow: var(--shadow-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 80px;
            font-weight: 700;
            color: var(--primary);
            overflow: hidden;
            position: relative;
            flex-shrink: 0;
        }

        .profile-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-photo-placeholder {
            background: linear-gradient(135deg, var(--gray-lighter), white);
        }

        .profile-info {
            flex: 1;
            padding-top: 100px;
        }

        .profile-name {
            font-size: 38px;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 8px;
            line-height: 1.2;
            word-break: break-word;
        }

        .profile-email {
            font-size: 16px;
            color: var(--gray);
            margin-bottom: 15px;
            font-weight: 500;
        }

        .profile-meta {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .meta-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            background: linear-gradient(135deg, rgba(78, 166, 133, 0.15), rgba(87, 184, 148, 0.15));
            border: 2px solid var(--primary);
            border-radius: 50px;
            font-size: 14px;
            font-weight: 700;
            color: var(--primary);
            box-shadow: var(--shadow-sm);
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .info-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: var(--shadow-md);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.8s ease backwards;
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

        .info-card:nth-child(1) { animation-delay: 0.1s; }
        .info-card:nth-child(2) { animation-delay: 0.2s; }
        .info-card:nth-child(3) { animation-delay: 0.3s; }
        .info-card:nth-child(4) { animation-delay: 0.4s; }

        .info-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: var(--gradient-primary);
            transform: scaleY(0);
            transition: transform 0.4s ease;
        }

        .info-card:hover::before {
            transform: scaleY(1);
        }

        .info-card:hover {
            box-shadow: var(--shadow-xl);
            transform: translateY(-8px);
        }

        .card-title {
            font-size: 20px;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card-title-icon {
            font-size: 28px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 18px 0;
            border-bottom: 1px solid var(--gray-light);
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-size: 12px;
            color: var(--gray);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .info-value {
            font-size: 16px;
            color: var(--dark);
            font-weight: 600;
            word-break: break-word;
        }

        .info-value.empty {
            color: var(--gray);
            font-style: italic;
            font-weight: 400;
        }

        /* Professional Links */
        .social-links {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .social-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: var(--gray-lighter);
            border-radius: 50px;
            text-decoration: none;
            color: var(--dark);
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .social-link:hover {
            background: white;
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .social-icon {
            font-size: 18px;
        }

        /* Edit Button Floating */
        .edit-fab {
            position: fixed;
            bottom: 40px;
            right: 40px;
            width: 70px;
            height: 70px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 25px rgba(78, 166, 133, 0.4);
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 999;
            border: none;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        .edit-fab:hover {
            transform: scale(1.1) translateY(-5px);
            box-shadow: 0 12px 35px rgba(78, 166, 133, 0.5);
            animation: none;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-container {
                padding: 30px 20px;
            }

            .profile-header {
                padding: 30px 20px;
            }

            .profile-header-content {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .profile-info {
                padding-top: 20px;
            }

            .profile-name {
                font-size: 28px;
            }

            .profile-meta {
                justify-content: center;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .nav-container {
                flex-direction: column;
                gap: 15px;
                padding: 15px 20px;
            }

            .nav-actions {
                width: 100%;
                flex-direction: column;
            }

            .nav-btn {
                width: 100%;
                justify-content: center;
            }

            .edit-fab {
                bottom: 20px;
                right: 20px;
                width: 60px;
                height: 60px;
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
            <a href="faculty_dashboard.php" class="nav-brand">
                <div class="back-icon">
                    <svg width="20" height="20" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                </div>
                <div>Back to Dashboard</div>
            </a>
            <div class="nav-actions">
                <a href="edit_profile.php" class="nav-btn">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                    <span>Edit Profile</span>
                </a>
                <a href="../php/faculty_logout.php" class="nav-btn btn-danger" onclick="return confirm('Are you sure you want to logout?')">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-header-content">
                <div class="profile-photo">
                    <?php if (!empty($faculty['profile_photo']) && file_exists('../' . $faculty['profile_photo'])): ?>
                        <img src="../<?php echo htmlspecialchars($faculty['profile_photo']); ?>" alt="Profile Photo">
                    <?php else: ?>
                        <div class="profile-photo-placeholder">
                            <?php echo strtoupper(substr($faculty['full_name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="profile-info">
                    <h1 class="profile-name"><?php echo htmlspecialchars($faculty['full_name']); ?></h1>
                    <p class="profile-email"><?php echo htmlspecialchars($faculty['email']); ?></p>
                    <div class="profile-meta">
                        <span class="meta-badge">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <span>Faculty Member</span>
                        </span>
                        <?php if (!empty($faculty['department'])): ?>
                            <span class="meta-badge">
                                <span>🏢</span>
                                <span><?php echo htmlspecialchars($faculty['department']); ?></span>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($faculty['designation'])): ?>
                            <span class="meta-badge">
                                <span>💼</span>
                                <span><?php echo htmlspecialchars($faculty['designation']); ?></span>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Information Grid -->
        <div class="info-grid">
            <!-- Personal Information Card -->
            <div class="info-card">
                <h2 class="card-title">
                    <span class="card-title-icon">👤</span>
                    <span>Personal Information</span>
                </h2>
                <div class="info-item">
                    <div class="info-label">Full Name</div>
                    <div class="info-value"><?php echo htmlspecialchars($faculty['full_name']); ?></div>
                </div>
                <?php if (!empty($faculty['date_of_birth'])): ?>
                    <div class="info-item">
                        <div class="info-label">Date of Birth</div>
                        <div class="info-value"><?php echo date('F d, Y', strtotime($faculty['date_of_birth'])); ?></div>
                    </div>
                <?php endif; ?>
                <?php if (!empty($faculty['gender'])): ?>
                    <div class="info-item">
                        <div class="info-label">Gender</div>
                        <div class="info-value"><?php echo htmlspecialchars($faculty['gender']); ?></div>
                    </div>
                <?php endif; ?>
                <?php if (!empty($faculty['address'])): ?>
                    <div class="info-item">
                        <div class="info-label">Address</div>
                        <div class="info-value"><?php echo htmlspecialchars($faculty['address']); ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Contact Information Card -->
            <div class="info-card">
                <h2 class="card-title">
                    <span class="card-title-icon">📞</span>
                    <span>Contact Information</span>
                </h2>
                <div class="info-item">
                    <div class="info-label">Email Address</div>
                    <div class="info-value"><?php echo htmlspecialchars($faculty['email']); ?></div>
                </div>
                <?php if (!empty($faculty['phone'])): ?>
                    <div class="info-item">
                        <div class="info-label">Phone Number</div>
                        <div class="info-value"><?php echo htmlspecialchars($faculty['phone']); ?></div>
                    </div>
                <?php endif; ?>
                <?php if (!empty($faculty['whatsapp'])): ?>
                    <div class="info-item">
                        <div class="info-label">WhatsApp Number</div>
                        <div class="info-value"><?php echo htmlspecialchars($faculty['whatsapp']); ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Professional Information Card -->
            <div class="info-card">
                <h2 class="card-title">
                    <span class="card-title-icon">💼</span>
                    <span>Professional Information</span>
                </h2>
                <?php if (!empty($faculty['department'])): ?>
                    <div class="info-item">
                        <div class="info-label">Department</div>
                        <div class="info-value"><?php echo htmlspecialchars($faculty['department']); ?></div>
                    </div>
                <?php endif; ?>
                <?php if (!empty($faculty['designation'])): ?>
                    <div class="info-item">
                        <div class="info-label">Designation</div>
                        <div class="info-value"><?php echo htmlspecialchars($faculty['designation']); ?></div>
                    </div>
                <?php endif; ?>
                <?php if (!empty($faculty['experience'])): ?>
                    <div class="info-item">
                        <div class="info-label">Total Experience</div>
                        <div class="info-value"><?php echo htmlspecialchars($faculty['experience']); ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Academic Qualifications Card -->
            <div class="info-card">
                <h2 class="card-title">
                    <span class="card-title-icon">🎓</span>
                    <span>Academic Qualifications</span>
                </h2>
                <?php if (!empty($faculty['qualification'])): ?>
                    <div class="info-item">
                        <div class="info-label">Highest Qualification</div>
                        <div class="info-value"><?php echo htmlspecialchars($faculty['qualification']); ?></div>
                    </div>
                <?php endif; ?>
                <?php if (!empty($faculty['specialization'])): ?>
                    <div class="info-item">
                        <div class="info-label">Specialization</div>
                        <div class="info-value"><?php echo htmlspecialchars($faculty['specialization']); ?></div>
                    </div>
                <?php endif; ?>
                <?php if (!empty($faculty['college'])): ?>
                    <div class="info-item">
                        <div class="info-label">College/University</div>
                        <div class="info-value"><?php echo htmlspecialchars($faculty['college']); ?></div>
                    </div>
                <?php endif; ?>
                <?php if (!empty($faculty['year_of_passing'])): ?>
                    <div class="info-item">
                        <div class="info-label">Year of Passing</div>
                        <div class="info-value"><?php echo htmlspecialchars($faculty['year_of_passing']); ?></div>
                    </div>
                <?php endif; ?>
                <?php if (!empty($faculty['aicte_id'])): ?>
                    <div class="info-item">
                        <div class="info-label">AICTE ID</div>
                        <div class="info-value"><?php echo htmlspecialchars($faculty['aicte_id']); ?></div>
                    </div>
                <?php endif; ?>
                <?php if (!empty($faculty['apaar_id'])): ?>
                    <div class="info-item">
                        <div class="info-label">APAAR ID</div>
                        <div class="info-value"><?php echo htmlspecialchars($faculty['apaar_id']); ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Bio & Professional Links Card -->
            <?php if (!empty($faculty['bio']) || !empty($professionalLinks)): ?>
                <div class="info-card" style="grid-column: 1 / -1;">
                    <h2 class="card-title">
                        <span class="card-title-icon">🔗</span>
                        <span>About & Professional Links</span>
                    </h2>
                    <?php if (!empty($faculty['bio'])): ?>
                        <div class="info-item">
                            <div class="info-label">Bio</div>
                            <div class="info-value"><?php echo nl2br(htmlspecialchars($faculty['bio'])); ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($professionalLinks)): ?>
                        <div class="info-item">
                            <div class="info-label">Professional Links</div>
                            <div class="social-links">
                                <?php foreach ($professionalLinks as $link): ?>
                                    <?php if (!empty($link['platform']) && !empty($link['url'])): ?>
                                        <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" class="social-link">
                                            <span class="social-icon">
                                                <?php
                                                $icons = [
                                                    'LinkedIn' => '💼',
                                                    'GitHub' => '💻',
                                                    'Twitter' => '🐦',
                                                    'Facebook' => '📘',
                                                    'Instagram' => '📷',
                                                    'Medium' => '✍️',
                                                    'Portfolio' => '🌐',
                                                    'ResearchGate' => '🔬',
                                                    'Google Scholar' => '🎓'
                                                ];
                                                echo $icons[$link['platform']] ?? '🔗';
                                                ?>
                                            </span>
                                            <span><?php echo htmlspecialchars($link['platform']); ?></span>
                                        </a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Floating Edit Button -->
    <button class="edit-fab" onclick="window.location.href='edit_profile.php'" title="Edit Profile">
        <svg width="28" height="28" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
        </svg>
    </button>

    <script>
        // Add entrance animations
        window.addEventListener('load', function() {
            // Animate info cards on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.info-card').forEach(card => {
                observer.observe(card);
            });

            // Add ripple effect to buttons
            document.querySelectorAll('.nav-btn, .edit-fab').forEach(button => {
                button.addEventListener('click', function(e) {
                    if (this.classList.contains('btn-danger') && !confirm('Are you sure you want to logout?')) {
                        e.preventDefault();
                        return;
                    }

                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.width = ripple.style.height = size + 'px';
                    ripple.style.left = x + 'px';
                    ripple.style.top = y + 'px';
                    ripple.style.position = 'absolute';
                    ripple.style.borderRadius = '50%';
                    ripple.style.background = 'rgba(255, 255, 255, 0.5)';
                    ripple.style.transform = 'scale(0)';
                    ripple.style.animation = 'ripple 0.6s ease-out';
                    ripple.style.pointerEvents = 'none';
                    
                    this.style.position = 'relative';
                    this.style.overflow = 'hidden';
                    this.appendChild(ripple);
                    
                    setTimeout(() => ripple.remove(), 600);
                });
            });

            // Smooth scroll reveal
            const cards = document.querySelectorAll('.info-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });

        // Add ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>