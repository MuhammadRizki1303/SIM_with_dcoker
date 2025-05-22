<?php

/**
 * Logout Page
 * Handles user logout
 */

// Include necessary files
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Log out user
logoutUser();

// Redirect to login page
setFlashMessage('success', 'Anda telah berhasil logout.');
redirect('login.php');