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

// Get parameters
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$type = isset($_GET['type']) ? sanitizeInput($_GET['type']) : '';

if (empty($id) || empty($type) || !in_array($type, ['student', 'teacher'])) {
    header("Location: faculty_dashboard.php");
    exit();
}

// Get faculty info
$faculty_id = $_SESSION['faculty_id'];
$facultyQuery = "SELECT full_name, profile_photo FROM faculty WHERE id = ?";
$facultyResult = executeQuery($facultyQuery, "i", array($faculty_id));
$faculty = $facultyResult->fetch_assoc();

// Get profile data based on type
if ($type === 'student') {
    $query = "SELECT * FROM students WHERE id = ? AND is_active = 1";
} else {
    $query = "SELECT * FROM teachers WHERE id = ? AND is_active = 1";
}

$result = executeQuery($query, "i", array($id));

if (!$result || $result->num_rows === 0) {
    header("Location: faculty_dashboard.php");
    exit();
}

$profile = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($profile['first_name'] . ' ' . $profile['last_name']); ?> - Profile</title>
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

        .nav-right {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .nav-user {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 700;
            color: white;
            border: 3px solid white;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-name {
            font-size: 14px;
            font-weight: 700;
            color: var(--dark);
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
            height: 150px;
            background : white;
            border-radius: 30px 30px 0 0;
        }

        .profile-header-content {
            position: relative;
            display: flex;
            align-items: flex-end;
            gap: 30px;
        }

        .profile-photo {
            width: 180px;
            height: 180px;
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
            padding-top: 80px;
        }

 .profile-name {
    font-size: 36px;
    font-weight: 800;
    color: var(--dark);
    margin-bottom: 8px;
    background: var(--gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    word-break: break-word;  /* ADD THIS */
    line-height: 1.2;         /* ADD THIS */
}

        .profile-meta {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .meta-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 20px;
            background: var(--gray-lighter);
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            color: var(--dark);
            box-shadow: var(--shadow-sm);
        }

        .meta-badge.primary {
            background: linear-gradient(135deg, rgba(78, 166, 133, 0.2), rgba(87, 184, 148, 0.2));
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .meta-badge.info {
            background: linear-gradient(135deg, rgba(52, 152, 219, 0.2), rgba(41, 128, 185, 0.2));
            color: #3498db;
            border: 2px solid #3498db;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .info-card {
            background: white;
            padding: 35px;
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
            font-size: 18px;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card-title-icon {
            font-size: 24px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 15px 0;
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
        }

        .info-value.empty {
            color: var(--gray);
            font-style: italic;
            font-weight: 400;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 14px 35px;
            font-size: 14px;
            font-weight: 700;
            border: none;
            border-radius: 60px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
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
                padding: 15px 20px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
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
    </div><!-- Top Navigation -->
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
            <div class="nav-right">
                <div class="nav-user">
                    <div class="user-avatar">
                        <?php if (!empty($faculty['profile_photo']) && file_exists('../' . $faculty['profile_photo'])): ?>
                            <img src="../<?php echo htmlspecialchars($faculty['profile_photo']); ?>" alt="Profile">
                        <?php else: ?>
                            <?php echo strtoupper(substr($faculty['full_name'], 0, 1)); ?>
                        <?php endif; ?>
                    </div>
                    <div class="user-name"><?php echo htmlspecialchars($faculty['full_name']); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-header-content">
                <div class="profile-photo">
                    <?php 
                    $fullName = $profile['first_name'] . ' ' . $profile['last_name'];
                    $initials = strtoupper(substr($profile['first_name'], 0, 1) . substr($profile['last_name'], 0, 1));
                    ?>
                    <div class="profile-photo-placeholder"><?php echo $initials; ?></div>
                </div>
                <div class="profile-info">
                    <h1 class="profile-name">
                        <?php echo htmlspecialchars($profile['first_name'] . ' ' . ($profile['middle_name'] ? $profile['middle_name'] . ' ' : '') . $profile['last_name']); ?>
                    </h1>
                    <div class="profile-meta">
                        <span class="meta-badge primary">
                            <span><?php echo $type === 'student' ? '🎓' : '👨‍🏫'; ?></span>
                            <span><?php echo ucfirst($type); ?></span>
                        </span>
                        <?php if ($type === 'student' && !empty($profile['prn'])): ?>
                            <span class="meta-badge">
                                <span>PRN: <?php echo htmlspecialchars($profile['prn']); ?></span>
                            </span>
                        <?php endif; ?>
                        <?php if (!empty($profile['department'])): ?>
                            <span class="meta-badge info">                                <span><?php echo htmlspecialchars($profile['department']); ?></span>
                            </span>
                        <?php endif; ?>
                    
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
                    <div class="info-value">
                        <?php echo htmlspecialchars($profile['first_name'] . ' ' . ($profile['middle_name'] ? $profile['middle_name'] . ' ' : '') . $profile['last_name']); ?>
                    </div>
                </div>
                <?php if (!empty($profile['date_of_birth'])): ?>
                    <div class="info-item">
                        <div class="info-label">Date of Birth</div>
                        <div class="info-value"><?php echo date('F d, Y', strtotime($profile['date_of_birth'])); ?></div>
                    </div>
                <?php endif; ?>
                <?php if (!empty($profile['gender'])): ?>
                    <div class="info-item">
                        <div class="info-label">Gender</div>
                        <div class="info-value"><?php echo htmlspecialchars($profile['gender']); ?></div>
                    </div>
                <?php endif; ?>
                <?php if ($type === 'student' && !empty($profile['blood_group'])): ?>
                    <div class="info-item">
                        <div class="info-label">Blood Group</div>
                        <div class="info-value"><?php echo htmlspecialchars($profile['blood_group']); ?></div>
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
                    <div class="info-value"><?php echo htmlspecialchars($profile['email']); ?></div>
                </div>
                <?php if (!empty($profile['phone'])): ?>
                    <div class="info-item">
                        <div class="info-label">Phone Number</div>
                        <div class="info-value"><?php echo htmlspecialchars($profile['phone']); ?></div>
                    </div>
                <?php endif; ?>
                <?php if (!empty($profile['city'])): ?>
                    <div class="info-item">
                        <div class="info-label">City</div>
                        <div class="info-value"><?php echo htmlspecialchars($profile['city']); ?></div>
                    </div>
                <?php endif; ?>
                <?php if (!empty($profile['state'])): ?>
                    <div class="info-item">
                        <div class="info-label">State</div>
                        <div class="info-value"><?php echo htmlspecialchars($profile['state']); ?></div>
                    </div>
                <?php endif; ?>
                <?php if (!empty($profile['address'])): ?>
                    <div class="info-item">
                        <div class="info-label">Address</div>
                        <div class="info-value"><?php echo htmlspecialchars($profile['address']); ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Academic/Professional Information Card -->
            <div class="info-card">
                <h2 class="card-title">
                    <span class="card-title-icon"><?php echo $type === 'student' ? '🎓' : '💼'; ?></span>
                    <span><?php echo $type === 'student' ? 'Academic Information' : 'Professional Information'; ?></span>
                </h2>
                <?php if (!empty($profile['department'])): ?>
                    <div class="info-item">
                        <div class="info-label">Department</div>
                        <div class="info-value"><?php echo htmlspecialchars($profile['department']); ?></div>
                    </div>
                <?php endif; ?>
                
                <?php if ($type === 'student'): ?>
                    <?php if (!empty($profile['class'])): ?>
                        <div class="info-item">
                            <div class="info-label">Class</div>
                            <div class="info-value"><?php echo htmlspecialchars($profile['class']); ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($profile['division'])): ?>
                        <div class="info-item">
                            <div class="info-label">Division</div>
                            <div class="info-value"><?php echo htmlspecialchars($profile['division']); ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($profile['roll_number'])): ?>
                        <div class="info-item">
                            <div class="info-label">Roll Number</div>
                            <div class="info-value"><?php echo htmlspecialchars($profile['roll_number']); ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($profile['batch_year'])): ?>
                        <div class="info-item">
                            <div class="info-label">Batch Year</div>
                            <div class="info-value"><?php echo htmlspecialchars($profile['batch_year']); ?></div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <?php if (!empty($profile['designation'])): ?>
                        <div class="info-item">
                            <div class="info-label">Designation</div>
                            <div class="info-value"><?php echo htmlspecialchars($profile['designation']); ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($profile['qualification'])): ?>
                        <div class="info-item">
                            <div class="info-label">Qualification</div>
                            <div class="info-value"><?php echo htmlspecialchars($profile['qualification']); ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($profile['experience'])): ?>
                        <div class="info-item">
                            <div class="info-label">Experience</div>
                            <div class="info-value"><?php echo htmlspecialchars($profile['experience']); ?></div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Additional Information Card (For Students) -->
            <?php if ($type === 'student'): ?>
                <div class="info-card">
                    <h2 class="card-title">
                        <span class="card-title-icon">👨‍👩‍👦</span>
                        <span>Parent/Guardian Information</span>
                    </h2>
                    <?php if (!empty($profile['parent_name'])): ?>
                        <div class="info-item">
                            <div class="info-label">Parent/Guardian Name</div>
                            <div class="info-value"><?php echo htmlspecialchars($profile['parent_name']); ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($profile['parent_phone'])): ?>
                        <div class="info-item">
                            <div class="info-label">Parent/Guardian Phone</div>
                            <div class="info-value"><?php echo htmlspecialchars($profile['parent_phone']); ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if (empty($profile['parent_name']) && empty($profile['parent_phone'])): ?>
                        <div class="info-item">
                            <div class="info-value empty">No parent/guardian information available</div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

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
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(card);
            });

            // Add ripple effect to buttons
            document.querySelectorAll('.btn').forEach(button => {
                button.addEventListener('click', function(e) {
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