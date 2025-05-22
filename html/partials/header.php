<?php

/**
 * Header Partial
 * Contains the top navigation bar and common head elements
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include functions if not already included
if (!function_exists('isLoggedIn')) {
    require_once __DIR__ . '/../includes/functions.php';
}

// Check if user is logged in
$isLoggedIn = isLoggedIn();
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';
$userRole = $isLoggedIn ? $_SESSION['user_role'] : '';

// Get current page
$currentPage = basename($_SERVER['PHP_SELF']);

// Page title mapping
$pageTitles = [
    'index.php' => 'Beranda',
    'dashboard.php' => 'Dashboard',
    'manage_mahasiswa.php' => 'Kelola Data Mahasiswa',
    'laporan.php' => 'Laporan',
    'settings.php' => 'Pengaturan'
];

$pageTitle = $pageTitles[$currentPage] ?? 'Sistem Informasi Mahasiswa';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo escapeHTML($pageTitle); ?> - Sistem Informasi Mahasiswa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/style.css">

</head>


<body>
    <!-- Header -->
    <header class="main-header">
        <div class="header-left">
            <button id="sidebar-toggle" class="sidebar-toggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="logo">
                <a href="/index.php">
                    <i class="fas fa-graduation-cap"></i>
                    <span>SIMahasiswa</span>
                </a>
            </div>
        </div>

        <div class="header-right">
            <?php if ($isLoggedIn): ?>
                <div class="user-info">
                    <div class="user-name">
                        <i class="fas fa-user-circle"></i>
                        <?php echo escapeHTML($userName); ?>
                    </div>
                    <span class="user-role"><?php echo escapeHTML($userRole); ?></span>
                    <a href="/logout.php" class="logout-btn" title="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            <?php else: ?>
                <a href="/login.php" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Login</span>
                </a>
            <?php endif; ?>
        </div>
    </header>

    <div class="main-container">
        <?php if ($isLoggedIn): ?>
            <!-- Include sidebar for logged in users -->
            <?php include 'sidebar.php'; ?>
        <?php endif; ?>

        <main class="main-content<?php echo $isLoggedIn ? '' : ' full-width'; ?>">
            <!-- Display flash messages -->
            <?php displayFlashMessage(); ?>