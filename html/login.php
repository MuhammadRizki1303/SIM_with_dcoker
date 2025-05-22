<?php

/**
 * Login Page
 * Handles user authentication
 */

// Include necessary files
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

// Process login form submission
$error = '';
if (isFormSubmitted()) {
    // Check CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token keamanan tidak valid. Silakan coba lagi.';
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        // Validate input
        if (empty($username) || empty($password)) {
            $error = 'Username dan password harus diisi.';
        } else {
            // Attempt to authenticate user
            if (authenticateUser($username, $password)) {
                // Redirect to dashboard on successful login
                redirect('dashboard.php');
            } else {
                $error = 'Username atau password tidak valid.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Informasi Mahasiswa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1><i class="fas fa-graduation-cap"></i> SIMahasiswa</h1>
                <p>Sistem Informasi Manajemen Data Mahasiswa</p>
            </div>

            <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <?php echo escapeHTML($error); ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo escapeHTML($_SERVER['PHP_SELF']); ?>" class="login-form">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-user"></i></span>
                        <input type="text" id="username" name="username" class="form-control" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-icon"><i class="fas fa-lock"></i></span>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </div>
            </form>

            <div class="login-footer">
                <p>© <?php echo date('Y'); ?> Sistem Informasi Mahasiswa</p>
                <p>Gunakan username: admin, password: password untuk login sebagai admin</p>
            </div>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>

</html>