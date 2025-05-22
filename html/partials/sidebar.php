<?php

/**
 * Sidebar Partial
 * Contains the navigation sidebar
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include functions if not already included
if (!function_exists('isCurrentPage')) {
    require_once __DIR__ . '/../includes/functions.php';
}

// Get current page
$currentPage = basename($_SERVER['PHP_SELF']);

// Get user role
$userRole = $_SESSION['user_role'] ?? '';

// Database connection status
$dbConnected = $_SESSION['db_connected'] ?? false;
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <h2 class="app-brand">SIAKAD</h2>
    </div>

    <nav class="sidebar-nav">
        <ul class="nav-list">
            <li class="nav-item <?php echo isCurrentPage('dashboard.php') ? 'active' : ''; ?>">
                <a href="/dashboard.php" class="nav-link">
                    <span class="icon-box bg-primary">
                        <i class="fas fa-tachometer-alt"></i>
                    </span>
                    <span class="link-text">Dashboard</span>
                </a>
            </li>

            <li class="nav-item <?php echo isCurrentPage('manage_mahasiswa.php') ? 'active' : ''; ?>">
                <a href="/manage_mahasiswa.php" class="nav-link">
                    <span class="icon-box bg-success">
                        <i class="fas fa-users"></i>
                    </span>
                    <span class="link-text">Data Mahasiswa</span>
                </a>
            </li>

            <li class="nav-item <?php echo isCurrentPage('laporan.php') ? 'active' : ''; ?>">
                <a href="/laporan.php" class="nav-link">
                    <span class="icon-box bg-info">
                        <i class="fas fa-chart-pie"></i>
                    </span>
                    <span class="link-text">Laporan</span>
                </a>
            </li>

            <?php if ($userRole === 'admin'): ?>
            <li class="nav-item <?php echo isCurrentPage('settings.php') ? 'active' : ''; ?>">
                <a href="/settings.php" class="nav-link">
                    <span class="icon-box bg-warning">
                        <i class="fas fa-tools"></i>
                    </span>
                    <span class="link-text">Pengaturan</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <div class="connection-status <?php echo $dbConnected ? 'connected' : 'disconnected'; ?>">
            <i class="status-icon fas <?php echo $dbConnected ? 'fa-plug' : 'fa-unlink'; ?>"></i>
            <span class="status-text">
                <?php echo $dbConnected ? 'Terhubung' : 'Terputus'; ?>
            </span>
        </div>
        <div class="version-info">
            <i class="fas fa-code-branch"></i>
            <span>v1.0.0</span>
        </div>
    </div>
</aside>

<style>
.sidebar {
    background: #ffffff;
    width: 260px;
    height: 100vh;
    box-shadow: 0 0 35px 0 rgba(49, 57, 66, 0.1);
    display: flex;
    flex-direction: column;
}

.sidebar-header {
    padding: 1rem 1.5rem;
    /* Padding lebih kecil */
    border-bottom: 1px solid #eee;
}


.app-brand {
    color: #2c3e50;
    font-size: 1.2rem;
    /* Diperkecil dari 1.5rem */
    font-weight: 600;
    margin: 0;
    letter-spacing: 0.5px;
    /* Jarak huruf lebih rapat */
}

.nav-list {
    padding: 1rem;
    flex-grow: 1;
}

.nav-item {
    margin-bottom: 0.5rem;
    border-radius: 8px;
    overflow: hidden;
}

.nav-item.active {
    background: #f8f9fa;
}

.nav-link {
    display: flex;
    align-items: center;
    padding: 0.875rem 1rem;
    color: #5e6e82;
    text-decoration: none;
    transition: all 0.3s ease;
}

.nav-link:hover {
    background: #f8f9fa;
    color: #2c3e50;
}

.icon-box {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
}

.bg-primary {
    background: #e3f2fd;
    color: #2196f3;
}

.bg-success {
    background: #e8f5e9;
    color: #4caf50;
}

.bg-info {
    background: #e3f2fd;
    color: #00bcd4;
}

.bg-warning {
    background: #fff3e0;
    color: #ff9800;
}

.link-text {
    font-size: 0.95rem;
    font-weight: 500;
}

.sidebar-footer {
    padding: 1rem;
    border-top: 1px solid #eee;
}

.connection-status {
    display: flex;
    align-items: center;
    font-size: 0.85rem;
    padding: 2.20rem;
    border-radius: 7px;
}

.connected {
    background: #e8f5e9;
    color: #4caf50;
}

.disconnected {
    background: #ffebee;
    color: #f44336;
}

.status-icon {
    margin-right: 0.5rem;
}

.version-info {
    margin-top: 0.5rem;
    font-size: 0.8rem;
    color: #6c757d;
    display: flex;
    align-items: center;
}

.version-info i {
    margin-right: 0.5rem;
}
</style>