<?php
// Configuration file for Adidas Store

// Session settings - must be set before session_start
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

// Start session after setting session configuration
session_start();

// Database connection settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'adidas_store');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application settings
define('SITE_NAME', 'Adidas Store');
define('SITE_URL', 'http://localhost/adidas_store');
define('ADMIN_EMAIL', 'admin@example.com');

// Error reporting - enable for development, disable for production
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection function
function getDbConnection() {
    try {
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Helper functions
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

function checkAdmin() {
    if (!isAdmin()) {
        redirect('login.php?error=unauthorized');
    }
}

// Flash messages
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Currency formatting
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}
?>
