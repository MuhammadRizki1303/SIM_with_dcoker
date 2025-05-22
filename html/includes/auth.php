<?php

/**
 * Authentication Functions
 * Handles user authentication and session management
 */

// Require database connection
require_once 'db_connect.php';
require_once 'functions.php';

/**
 * Authenticate user with username and password
 * @param string $username Username
 * @param string $password Password
 * @return bool Authentication result
 */
function authenticateUser($username, $password)
{
    $sql = "SELECT * FROM users WHERE username = :username LIMIT 1";
    $user = executeQuerySingle($sql, ['username' => $username]);

    if ($user && password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];

        // Update last login
        $updateSql = "UPDATE users SET last_login = NOW() WHERE id = :id";
        executeNonQuery($updateSql, ['id' => $user['id']]);

        return true;
    }

    return false;
}

/**
 * Log out current user
 */
function logoutUser()
{
    // Unset all session variables
    $_SESSION = [];

    // Destroy the session
    session_destroy();
}

/**
 * Check if user has permission to access a page
 * @param array $allowedRoles Roles allowed to access
 * @return bool Whether user has permission
 */
function hasPermission($allowedRoles = ['admin', 'staff'])
{
    // Check if user is logged in
    if (!isLoggedIn()) {
        return false;
    }

    // Check if user role is in allowed roles
    return in_array($_SESSION['user_role'], $allowedRoles);
}

/**
 * Require user to be logged in
 * @param string $redirectPage Page to redirect to if not logged in
 */
function requireLogin($redirectPage = '/login.php')
{
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Silakan login terlebih dahulu untuk mengakses halaman ini.');
        redirect($redirectPage);
    }
}

/**
 * Require user to have specific role
 * @param array $allowedRoles Roles allowed to access
 * @param string $redirectPage Page to redirect to if not allowed
 */
function requireRole($allowedRoles = ['admin'], $redirectPage = '/index.php')
{
    requireLogin();

    if (!hasPermission($allowedRoles)) {
        setFlashMessage('error', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        redirect($redirectPage);
    }
}

/**
 * Change user password
 * @param int $userId User ID
 * @param string $currentPassword Current password
 * @param string $newPassword New password
 * @return bool|string Success status or error message
 */
function changePassword($userId, $currentPassword, $newPassword)
{
    // Get user
    $sql = "SELECT * FROM users WHERE id = :id LIMIT 1";
    $user = executeQuerySingle($sql, ['id' => $userId]);

    if (!$user) {
        return 'Pengguna tidak ditemukan.';
    }

    // Verify current password
    if (!password_verify($currentPassword, $user['password'])) {
        return 'Password saat ini tidak valid.';
    }

    // Hash new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update password
    $updateSql = "UPDATE users SET password = :password, updated_at = NOW() WHERE id = :id";
    $success = executeNonQuery($updateSql, [
        'password' => $hashedPassword,
        'id' => $userId
    ]);

    return $success ? true : 'Gagal memperbarui password.';
}