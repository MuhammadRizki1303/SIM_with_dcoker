<?php

/**
 * Utility Functions
 * Contains common functions used throughout the application
 */

/**
 * Safely output text to prevent XSS
 * @param string $text Text to output
 * @return string Escaped text
 */
function escapeHTML($text)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to another page
 * @param string $page Page to redirect to
 */
function redirect($page)
{
    header("Location: $page");
    exit;
}

/**
 * Check if user is logged in
 * @return bool Whether user is logged in
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

/**
 * Check if user has admin role
 * @return bool Whether user is admin
 */
function isAdmin()
{
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Set flash message
 * @param string $type Message type (success, error, warning, info)
 * @param string $message Message content
 */
function setFlashMessage($type, $message)
{
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 * @return array|null Flash message or null if none exists
 */
function getFlashMessage()
{
    if (isset($_SESSION['flash_message'])) {
        $flashMessage = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flashMessage;
    }
    return null;
}

/**
 * Display flash message HTML
 */
function displayFlashMessage()
{
    $flashMessage = getFlashMessage();
    if ($flashMessage) {
        // Warna berdasarkan tipe pesan
        $colors = [
            'success' => '#4caf50',   // hijau
            'error' => '#f44336',     // merah
            'warning' => '#ff9800',   // oranye
            'info' => '#2196f3'       // biru
        ];
        $color = $colors[$flashMessage['type']] ?? '#2196f3';

        // Card popup HTML
        echo '
        <div id="flash-card" style="
            position: fixed;
            top: 20px;
            right: 20px;
            max-width: 320px;
            background-color: white;
            border-left: 6px solid ' . $color . ';
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            padding: 16px 24px;
            font-family: Arial, sans-serif;
            font-size: 15px;
            color: #333;
            z-index: 99999;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: default;
        ">
            <div style="flex-grow:1; padding-right: 12px;">
                ' . htmlspecialchars($flashMessage['message']) . '
            </div>
            <button id="flash-close-btn" aria-label="Tutup notifikasi" style="
                background: transparent;
                border: none;
                font-size: 20px;
                line-height: 1;
                color: #666;
                cursor: pointer;
                padding: 0;
            ">&times;</button>
        </div>
        ';
    }
}


/**
 * Check if form is submitted
 * @param string $method HTTP method (POST, GET)
 * @return bool Whether form is submitted
 */
function isFormSubmitted($method = 'POST')
{
    return $_SERVER['REQUEST_METHOD'] === $method;
}

/**
 * Generate CSRF token
 * @return string CSRF token
 */
function generateCSRFToken()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token Token to verify
 * @return bool Whether token is valid
 */
function verifyCSRFToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Format date to Indonesian format
 * @param string $date Date in Y-m-d format
 * @return string Formatted date
 */
function formatDateID($date)
{
    $timestamp = strtotime($date);
    $months = [
        'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    ];

    $day = date('d', $timestamp);
    $month = $months[date('n', $timestamp) - 1];
    $year = date('Y', $timestamp);

    return "$day $month $year";
}

/**
 * Get current page URL
 * @return string Current page URL
 */
function getCurrentURL()
{
    return $_SERVER['PHP_SELF'];
}

/**
 * Check if current page matches given page
 * @param string $page Page to check
 * @return bool Whether current page matches
 */
function isCurrentPage($page)
{
    return basename($_SERVER['PHP_SELF']) === $page;
}
