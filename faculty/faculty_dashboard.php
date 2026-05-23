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
$query = "SELECT profile_completed, full_name, email, profile_photo FROM faculty WHERE id = ?";
$result = executeQuery($query, "i", array($faculty_id));

if ($result && $result->num_rows > 0) {
    $faculty = $result->fetch_assoc();
    
    // Check if profile is completed
    if ($faculty['profile_completed'] == 0) {
        header("Location: complete_profile.php");
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
    <title>Faculty Dashboard - MITAOE</title>
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
            --gradient-1: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-2: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --gradient-3: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
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
            max-width: 1800px;
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
        }

     .nav-brand-icon {
    width: 45px;
    height: 45px;
    background: transparent;  /* No background */
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 35px;  /* Bigger icon */
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
            gap: 30px;
        }

        .nav-user {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 10px 20px;
            background: var(--gray-lighter);
            border-radius: 50px;
            transition: all 0.3s ease;
        }

        .nav-user:hover {
            background: white;
            box-shadow: var(--shadow-md);
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 700;
            color: white;
            border: 3px solid white;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            position: relative;
        }

        .user-avatar::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transform: rotate(45deg);
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% {
                top: -50%;
                left: -50%;
            }
            100% {
                top: 150%;
                left: 150%;
            }
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-size: 15px;
            font-weight: 700;
            color: var(--dark);
        }

        .user-role {
            font-size: 12px;
            color: var(--gray);
            font-weight: 500;
        }

        .profile-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 28px;
            background: var(--gradient-primary);
            border-radius: 50px;
            text-decoration: none;
            color: white;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(78, 166, 133, 0.3);
            position: relative;
            overflow: hidden;
        }

        .profile-link::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            transform: translate(-50%, -50%);
            transition: width 0.5s, height 0.5s;
        }

        .profile-link:hover::before {
            width: 300px;
            height: 300px;
        }

        .profile-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 25px rgba(78, 166, 133, 0.4);
        }

        .profile-link svg {
            position: relative;
            z-index: 1;
        }

        .profile-link span {
            position: relative;
            z-index: 1;
        }

        /* Main Container */
        .main-container {
            position: relative;
            z-index: 1;
            max-width: 1400px;
            margin: 0 auto;
            padding: 60px 40px;
        }

        /* Welcome Section */
        .welcome-section {
            text-align: center;
            margin-bottom: 60px;
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

        .welcome-section h1 {
            font-size: 48px;
            font-weight: 800;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 15px;
            letter-spacing: -1px;
        }

        .welcome-section p {
            font-size: 18px;
            color: var(--gray);
            font-weight: 500;
        }

        /* Search Section */
        .search-section {
            background: white;
            padding: 60px;
            border-radius: 30px;
            box-shadow: var(--shadow-xl);
            margin-bottom: 50px;
            max-width: 1000px;
            margin-left: auto;
            margin-right: auto;
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.8s ease 0.2s backwards;
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

        .search-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(78, 166, 133, 0.05) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        .search-title {
            text-align: center;
            font-size: 28px;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 35px;
            position: relative;
            z-index: 1;
        }

        .search-title::after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: var(--gradient-primary);
            margin: 15px auto 0;
            border-radius: 2px;
        }

        .search-container {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 15px;
            align-items: stretch;
            position: relative;
            z-index: 1;
        }

        .search-input-wrapper {
            position: relative;
        }

        .search-icon {
            position: absolute;
            left: 25px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 22px;
            color: var(--primary);
            z-index: 1;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(-50%);
            }
            50% {
                transform: translateY(-45%);
            }
        }

        .search-input {
            width: 100%;
            padding: 20px 25px 20px 65px;
            font-size: 16px;
            border: 3px solid var(--gray-light);
            border-radius: 60px;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            transition: all 0.3s ease;
            background: var(--gray-lighter);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 6px rgba(78, 166, 133, 0.1);
            transform: translateY(-2px);
        }

        .search-select {
            padding: 20px 30px;
            font-size: 16px;
            border: 3px solid var(--gray-light);
            border-radius: 60px;
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            color: var(--dark);
            background: var(--gray-lighter);
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 200px;
        }

        .search-select:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 6px rgba(78, 166, 133, 0.1);
        }

        .search-btn {
            padding: 20px 45px;
            font-size: 16px;
            font-weight: 700;
            border: none;
            border-radius: 60px;
            background: var(--gradient-primary);
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(78, 166, 133, 0.3);
            font-family: 'Poppins', sans-serif;
            position: relative;
            overflow: hidden;
        }

        .search-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        .search-btn:hover::before {
            left: 100%;
        }

        .search-btn:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(78, 166, 133, 0.4);
        }

        .search-btn:active {
            transform: translateY(-2px);
        }

        /* Results Section */
        .results-section {
            max-width: 1200px;
            margin: 0 auto;
            animation: fadeInUp 0.8s ease 0.4s backwards;
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .results-title {
            font-size: 28px;
            font-weight: 800;
            color: var(--dark);
        }

        .results-count {
            font-size: 14px;
            color: var(--primary);
            background: white;
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: 700;
            box-shadow: var(--shadow-md);
            border: 2px solid var(--primary);
        }

        .results-container {
            display: none;
        }

        .results-container.active {
            display: block;
        }

        .result-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 20px;
            box-shadow: var(--shadow-md);
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 25px;
            align-items: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border-left: 5px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .result-card::before {
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

        .result-card:hover::before {
            transform: scaleY(1);
        }

        .result-card:hover {
            box-shadow: var(--shadow-xl);
            transform: translateX(8px) translateY(-4px);
        }

        .result-info {
            display: grid;
            grid-template-columns: 2fr 1.2fr 2fr 1.5fr 1fr;
            gap: 25px;
            align-items: center;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .info-label {
            font-size: 11px;
            color: var(--gray);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .info-value {
            font-size: 15px;
            color: var(--dark);
            font-weight: 600;
            line-height: 1.4;
        }

        .info-value.name {
            font-size: 20px;
            font-weight: 800;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .category-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 700;
            box-shadow: var(--shadow-sm);
        }

        .category-badge.student {
            background: linear-gradient(135deg, rgba(78, 166, 133, 0.2), rgba(87, 184, 148, 0.2));
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .category-badge.teacher {
            background: linear-gradient(135deg, rgba(52, 152, 219, 0.2), rgba(41, 128, 185, 0.2));
            color: #3498db;
            border: 2px solid #3498db;
        }

        .view-btn {
            padding: 14px 35px;
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: 60px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(78, 166, 133, 0.3);
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            gap: 10px;
            white-space: nowrap;
        }

        .view-btn:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 10px 30px rgba(78, 166, 133, 0.4);
        }

        /* Empty State */
        .empty-state, .no-results, .loading-state {
            text-align: center;
            padding: 80px 40px;
            background: white;
            border-radius: 30px;
            box-shadow: var(--shadow-lg);
        }

        .empty-icon {
            font-size: 100px;
            margin-bottom: 25px;
            opacity: 0.6;
            animation: float 3s ease-in-out infinite;
        }

        .empty-title {
            font-size: 28px;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 12px;
        }

        .empty-text {
            font-size: 16px;
            color: var(--gray);
            font-weight: 500;
        }

        .no-results {
            display: none;
        }

        .no-results.active {
            display: block;
        }

        /* Loading State */
        .loading-state {
            display: none;
        }

        .loading-state.active {
            display: block;
        }

        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 5px solid var(--gray-light);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 25px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .loading-text {
            font-size: 18px;
            color: var(--gray);
            font-weight: 600;
        }

        /* Alert Messages */
        .alert {
            padding: 18px 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            display: none;
            animation: slideDown 0.5s ease;
            font-weight: 600;
            box-shadow: var(--shadow-md);
        }

        .alert.active {
            display: block;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
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

        .alert-info {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            color: white;
            border-left: 5px solid #0984e3;
        }

       /* ============================================
   COMPREHENSIVE RESPONSIVE DESIGN
   ============================================ */

/* Tablet - 1024px and below */
@media (max-width: 1024px) {
    .nav-container {
        padding: 15px 25px;
    }

    .nav-brand {
        font-size: 20px;
    }

    .search-section {
        padding: 40px 30px;
    }

    .search-container {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .search-select {
        width: 100%;
    }

    .result-info {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Mobile Large - 768px and below */
@media (max-width: 768px) {
    /* Navigation */
    .nav-container {
        flex-direction: column;
        gap: 15px;
        padding: 15px 20px;
    }

    .nav-brand {
        font-size: 18px;
    }

    .nav-brand-icon {
        width: 35px;
        height: 35px;
        font-size: 28px;
    }

    .nav-right {
        width: 100%;
        flex-direction: column;
        gap: 15px;
    }

    .nav-user {
        width: 100%;
        justify-content: center;
        padding: 12px 20px;
    }

    .user-avatar {
        width: 45px;
        height: 45px;
        font-size: 18px;
    }

    .user-info {
        flex: 1;
    }

    .user-name {
        font-size: 14px;
    }

    .user-role {
        font-size: 11px;
    }

    .profile-link {
        width: 100%;
        justify-content: center;
        padding: 12px 25px;
        font-size: 13px;
    }

    /* Main Container */
    .main-container {
        padding: 40px 20px;
    }

    /* Welcome Section */
    .welcome-section {
        margin-bottom: 40px;
    }

    .welcome-section h1 {
        font-size: 32px;
    }

    .welcome-section p {
        font-size: 16px;
    }

    /* Search Section */
    .search-section {
        padding: 40px 25px;
        border-radius: 20px;
    }

    .search-title {
        font-size: 22px;
        margin-bottom: 25px;
    }

    .search-container {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .search-input-wrapper {
        order: 1;
    }

    .search-select {
        order: 2;
        width: 100%;
        min-width: auto;
    }

    .search-btn {
        order: 3;
        width: 100%;
    }

    .search-input {
        padding: 16px 20px 16px 3.25rem;
        font-size: 15px;
    }

    .search-icon {
        left: 20px;
        font-size: 20px;
    }

    .search-select {
        padding: 16px 25px;
        font-size: 15px;
    }

    .search-btn {
        padding: 16px 35px;
        font-size: 15px;
    }

    /* Results Section */
    .results-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
        margin-bottom: 25px;
    }

    .results-title {
        font-size: 22px;
    }

    .results-count {
        font-size: 13px;
        padding: 8px 20px;
    }

    /* Result Cards */
    .result-card {
        grid-template-columns: 1fr;
        padding: 25px;
        gap: 20px;
        border-radius: 15px;
        margin-bottom: 15px;
    }

    .result-info {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .info-value.name {
        font-size: 18px;
    }

    .info-value {
        font-size: 14px;
    }

    .info-label {
        font-size: 10px;
    }

    .category-badge {
        padding: 6px 15px;
        font-size: 12px;
    }

    .view-btn {
        width: 100%;
        justify-content: center;
        padding: 12px 30px;
        font-size: 13px;
    }

    /* Empty State */
    .empty-state,
    .no-results,
    .loading-state {
        padding: 60px 30px;
        border-radius: 20px;
    }

    .empty-icon {
        font-size: 80px;
        margin-bottom: 20px;
    }

    .empty-title {
        font-size: 22px;
        margin-bottom: 10px;
    }

    .empty-text {
        font-size: 15px;
    }

    /* Alert */
    .alert {
        padding: 14px 20px;
        font-size: 14px;
        border-radius: 12px;
    }

    /* Reduce particles on mobile */
    .bg-particles .particle {
        opacity: 0.15 !important;
        animation-duration: 25s !important;
    }
}

/* Mobile Medium - 480px and below */
@media (max-width: 480px) {
    .main-container {
        padding: 30px 15px;
    }

    .welcome-section h1 {
        font-size: 28px;
    }

    .welcome-section p {
        font-size: 15px;
    }

    .search-section {
        padding: 30px 20px;
    }

    .search-title {
        font-size: 20px;
    }

    .search-input {
        padding: 14px 18px 14px 3rem;
        font-size: 14px;
    }

    .search-icon {
        left: 18px;
        font-size: 18px;
    }

    .search-select {
        padding: 14px 20px;
        font-size: 14px;
    }

    .search-btn {
        padding: 14px 30px;
        font-size: 14px;
    }

    .result-card {
        padding: 20px;
    }

    .info-value.name {
        font-size: 16px;
    }

    .info-value {
        font-size: 13px;
    }

    .category-badge {
        padding: 5px 12px;
        font-size: 11px;
    }

    .view-btn {
        padding: 10px 25px;
        font-size: 12px;
    }

    .results-title {
        font-size: 20px;
    }

    .empty-icon {
        font-size: 70px;
    }

    .empty-title {
        font-size: 20px;
    }

    .empty-text {
        font-size: 14px;
    }
}

/* Mobile Small - 360px and below */
@media (max-width: 360px) {
    .welcome-section h1 {
        font-size: 24px;
    }

    .search-section {
        padding: 25px 15px;
    }

    .search-title {
        font-size: 18px;
    }

    .search-input {
        padding: 12px 15px 12px 2.75rem;
        font-size: 13px;
    }

    .search-select {
        padding: 12px 18px;
        font-size: 13px;
    }

    .search-btn {
        padding: 12px 25px;
        font-size: 13px;
    }

    .result-card {
        padding: 18px;
    }

    .info-value.name {
        font-size: 15px;
    }
}

/* Landscape mobile phones */
@media (max-height: 500px) and (orientation: landscape) {
    .welcome-section {
        margin-bottom: 30px;
    }

    .welcome-section h1 {
        font-size: 28px;
    }

    .search-section {
        padding: 30px;
    }

    .empty-state,
    .no-results,
    .loading-state {
        padding: 40px 30px;
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
            <div class="nav-brand">
                <div class="nav-brand-icon">🎓</div>
                <div>MITAOE Faculty Portal</div>
            </div>
            <div class="nav-right">
                <div class="nav-user">
                    <div class="user-avatar">
                        <?php if (!empty($faculty['profile_photo']) && file_exists('../' . $faculty['profile_photo'])): ?>
                            <img src="../<?php echo htmlspecialchars($faculty['profile_photo']); ?>" alt="Profile">
                        <?php else: ?>
                            <?php echo strtoupper(substr($faculty['full_name'], 0, 1)); ?>
                        <?php endif; ?>
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($faculty['full_name']); ?></div>
                        <div class="user-role">Faculty Member</div>
                    </div>
                </div>
                <a href="profile.php" class="profile-link">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <span>My Profile</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <h1>Welcome Back, <?php echo htmlspecialchars(explode(' ', $faculty['full_name'])[0]); ?>! 👋</h1>
            <p>Search for students and teachers in the MITAOE database</p>
        </div>

        <!-- Search Section -->
        <div class="search-section">
            <h2 class="search-title">🔍 Search Database</h2>
            <div class="search-container">
                <div class="search-input-wrapper">
                    <span class="search-icon">🔎</span>
                    <input type="text" 
                           id="searchInput" 
                           class="search-input" 
                           placeholder="Enter search query..."
                           autocomplete="off">
                </div>
                <select id="searchType" class="search-select">
                    <option value="name">Name</option>
                    <option value="surname">Surname</option>
                    <option value="prn">PRN</option>
                    <option value="city">City</option>
                    <option value="email">Email</option>
                    <option value="phone">Mobile Number</option>
                </select>
                <button id="searchBtn" class="search-btn">Search</button>
            </div>
        </div>

        <!-- Alert Messages -->
        <div id="alertMessage" class="alert"></div>

        <!-- Loading State -->
        <div id="loadingState" class="loading-state">
            <div class="loading-spinner"></div>
            <div class="loading-text">Searching database...</div>
        </div>

        <!-- Empty State (Initial) -->
        <div id="emptyState" class="empty-state">
            <div class="empty-icon">📋</div>
            <h3 class="empty-title">Start Your Search</h3>
            <p class="empty-text">Enter a search query above to find students or teachers</p>
        </div>

        <!-- No Results State -->
        <div id="noResults" class="no-results">
            <div class="empty-icon">🔍</div>
            <h3 class="empty-title">No Results Found</h3>
            <p class="empty-text">Try searching with different keywords or filters</p>
        </div>

        <!-- Results Section -->
        <div id="resultsSection" class="results-section" style="display: none;">
            <div class="results-header">
                <h2 class="results-title">Search Results</h2>
                <span id="resultsCount" class="results-count">0 results</span>
            </div>
            <div id="resultsContainer" class="results-container">
                <!-- Results will be populated here by JavaScript -->
            </div>
        </div>
    </div>

    <script>
        // DOM Elements
        const searchInput = document.getElementById('searchInput');
        const searchType = document.getElementById('searchType');
        const searchBtn = document.getElementById('searchBtn');
        const emptyState = document.getElementById('emptyState');
        const loadingState = document.getElementById('loadingState');
        const noResults = document.getElementById('noResults');
        const resultsSection = document.getElementById('resultsSection');
        const resultsContainer = document.getElementById('resultsContainer');
        const resultsCount = document.getElementById('resultsCount');
        const alertMessage = document.getElementById('alertMessage');

        // Search on button click
        searchBtn.addEventListener('click', performSearch);

        // Search on Enter key
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });

        // Perform Search Function
        async function performSearch() {
            const query = searchInput.value.trim();
            const type = searchType.value;

            // Validate input
            if (!query) {
                showAlert('Please enter a search query', 'danger');
                searchInput.focus();
                return;
            }

            // Hide all states
            hideAllStates();

            // Show loading
            loadingState.classList.add('active');

            try {
                const formData = new FormData();
                formData.append('search_query', query);
                formData.append('search_type', type);

                const response = await fetch('../php/faculty_search.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                // Hide loading
                loadingState.classList.remove('active');

                if (data.success && data.results.length > 0) {
                    displayResults(data.results);
                } else {
                    noResults.classList.add('active');
                    if (data.message) {
                        showAlert(data.message, 'info');
                    }
                }
            } catch (error) {
                loadingState.classList.remove('active');
                showAlert('An error occurred while searching. Please try again.', 'danger');
                console.error('Search error:', error);
            }
        }

        // Display Results Function
        function displayResults(results) {
            resultsContainer.innerHTML = '';
            resultsCount.textContent = `${results.length} result${results.length !== 1 ? 's' : ''}`;

            results.forEach((result, index) => {
                const card = createResultCard(result);
                // Add staggered animation delay
                card.style.animation = `fadeInUp 0.5s ease ${index * 0.1}s backwards`;
                resultsContainer.appendChild(card);
            });

            resultsSection.style.display = 'block';
            resultsContainer.classList.add('active');
        }

        // Create Result Card
        function createResultCard(result) {
            const card = document.createElement('div');
            card.className = 'result-card';

            // Determine if student or teacher
            const isStudent = result.type === 'student';
            const category = isStudent ? 'Student' : 'Teacher';
            const categoryClass = isStudent ? 'student' : 'teacher';
            const categoryIcon = isStudent ? '🎓' : '👨‍🏫';

            card.innerHTML = `
                <div class="result-info">
                    <div class="info-item">
                        <div class="info-label">Full Name</div>
                        <div class="info-value name">${escapeHtml(result.full_name)}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">${isStudent ? 'PRN' : 'ID'}</div>
                        <div class="info-value">${escapeHtml(isStudent ? result.prn : 'T' + result.id)}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value">${escapeHtml(result.email)}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Department</div>
                        <div class="info-value">${escapeHtml(result.department || 'N/A')}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Category</div>
                        <div class="category-badge ${categoryClass}">
                            <span>${categoryIcon}</span>
                            <span>${category}</span>
                        </div>
                    </div>
                </div>
                <button class="view-btn" onclick="viewProfile(${result.id}, '${result.type}')">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <span>View Profile</span>
                </button>
            `;

            return card;
        }

        // View Profile Function
        function viewProfile(id, type) {
            // Redirect to view profile page with id and type
            window.location.href = `view_profile.php?id=${id}&type=${type}`;
        }

        // Hide All States
        function hideAllStates() {
            emptyState.style.display = 'none';
            loadingState.classList.remove('active');
            noResults.classList.remove('active');
            resultsSection.style.display = 'none';
            resultsContainer.classList.remove('active');
            alertMessage.classList.remove('active');
        }

        // Show Alert
        function showAlert(message, type) {
            alertMessage.textContent = message;
            alertMessage.className = `alert alert-${type} active`;
            
            setTimeout(() => {
                alertMessage.classList.remove('active');
            }, 5000);

            // Scroll to alert
            alertMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text ? String(text).replace(/[&<>"']/g, m => map[m]) : '';
        }

        // Update search type placeholder
        searchType.addEventListener('change', function() {
            const placeholders = {
                'name': 'Enter first or middle name...',
                'surname': 'Enter last name...',
                'prn': 'Enter PRN number...',
                'city': 'Enter city name...',
                'email': 'Enter email address...',
                'phone': 'Enter mobile number...'
            };
            searchInput.placeholder = placeholders[this.value] || 'Enter search query...';
        });

        // Clear search on escape key
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                hideAllStates();
                emptyState.style.display = 'block';
            }
        });

        // Focus on search input on page load
        window.addEventListener('load', function() {
            searchInput.focus();
            
            // Add entrance animation to main elements
            document.querySelectorAll('.nav-container, .welcome-section, .search-section, .empty-state').forEach((el, index) => {
                el.style.animation = `fadeInUp 0.6s ease ${index * 0.1}s backwards`;
            });
        });

        // Add ripple effect to buttons
        document.querySelectorAll('.search-btn, .profile-link').forEach(button => {
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
                
                this.appendChild(ripple);
                
                setTimeout(() => ripple.remove(), 600);
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