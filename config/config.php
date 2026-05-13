<?php
/**
 * Application Configuration
 * Community Blood Donation Portal
 */

// Start session with secure settings
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    session_start();
}

// Base URL Configuration
define('BASE_URL', 'http://localhost/Community%20Blood%20Donation%20Portal%20Update/');
define('SITE_NAME', 'Community Blood Donation Portal');
define('SITE_EMAIL', 'support@blooddonation.com');

// Paths
define('ROOT_PATH', dirname(__DIR__) . '/');
define('UPLOAD_PATH', ROOT_PATH . 'uploads/');
define('PROFILE_IMAGE_PATH', UPLOAD_PATH . 'profiles/');

// File Upload Settings
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/jpg']);

// Pagination
define('RECORDS_PER_PAGE', 10);

// Blood Types
define('BLOOD_TYPES', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']);

// Donation Interval (in days)
define('DONATION_INTERVAL_DAYS', 90);

// Email Configuration (for future implementation)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-password');

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');

// Timezone
date_default_timezone_set('Asia/Colombo');

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
require_once ROOT_PATH . 'config/database.php';

// Helper Functions
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserType() {
    return $_SESSION['user_type'] ?? null;
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('auth/login.php');
    }
}

function requireUserType($type) {
    requireLogin();
    if (getUserType() !== $type) {
        redirect('index.php');
    }
}

function generateCSRFToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function verifyCSRFToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('F j, Y g:i A', strtotime($datetime));
}

function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    if ($difference < 60) {
        return 'just now';
    } elseif ($difference < 3600) {
        $minutes = floor($difference / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($difference < 86400) {
        $hours = floor($difference / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($difference < 604800) {
        $days = floor($difference / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return formatDateTime($datetime);
    }
}

function canDonate($lastDonationDate) {
    if (!$lastDonationDate) {
        return true;
    }
    
    $lastDonation = strtotime($lastDonationDate);
    $nextEligibleDate = strtotime('+' . DONATION_INTERVAL_DAYS . ' days', $lastDonation);
    
    return time() >= $nextEligibleDate;
}

function getNextDonationDate($lastDonationDate) {
    if (!$lastDonationDate) {
        return 'Eligible now';
    }
    
    $nextDate = date('Y-m-d', strtotime($lastDonationDate . ' +' . DONATION_INTERVAL_DAYS . ' days'));
    
    if (strtotime($nextDate) <= time()) {
        return 'Eligible now';
    }
    
    return formatDate($nextDate);
}

// Create upload directories if they don't exist
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}
if (!file_exists(PROFILE_IMAGE_PATH)) {
    mkdir(PROFILE_IMAGE_PATH, 0755, true);
}
