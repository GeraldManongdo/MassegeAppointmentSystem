<?php
/**
 * General Configuration File
 */

// Timezone
date_default_timezone_set('UTC');

// Site Configuration
define('SITE_NAME', 'Senere Massage');
define('SITE_TAGLINE', 'Your Wellness, Our Priority');
define('SITE_LOCATION', 'Quezon City, Philippines');
define('SITE_URL', 'http://localhost/MassegeAppointmentSystem/');

// Path Configuration
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('MODELS_PATH', ROOT_PATH . '/models');
define('CONTROLLERS_PATH', ROOT_PATH . '/controllers');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
require_once CONFIG_PATH . '/database.php';

/**
 * Autoload models
 */
spl_autoload_register(function ($class_name) {
    $model_file = MODELS_PATH . '/' . $class_name . '.php';
    if (file_exists($model_file)) {
        require_once $model_file;
    }
});

/**
 * Helper function to check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Helper function to check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Helper function to redirect
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Helper function to sanitize output
 */
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Helper function to format date
 */
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

/**
 * Helper function to format time
 */
function formatTime($time) {
    return date('g:i A', strtotime($time));
}

/**
 * Helper function to get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}
?>
