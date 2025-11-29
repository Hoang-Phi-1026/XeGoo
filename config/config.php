<?php
ini_set('display_errors', 1);
if (!defined('BASE_URL')) {
    // Auto-detect base URL if possible
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $basePath = dirname($scriptName);
    
    // Clean up base path
    if ($basePath === '/' || $basePath === '\\') {
        $basePath = '';
    }
    
    $autoBaseUrl = $protocol . '://' . $host . $basePath;
    
    // Use auto-detected URL or fallback to localhost
    define('BASE_URL', $autoBaseUrl !== 'http://localhost' ? $autoBaseUrl : 'http://localhost/xegoo');
}

// Enhanced error reporting for debugging
if (isset($_GET['debug']) || isset($_SESSION['debug_mode'])) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    // Turn off error display in production
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
}

// Log errors instead of displaying them
ini_set('log_errors', 1);
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
ini_set('error_log', $logDir . '/php_errors.log');

// Set error reporting level
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);


error_log("[v0] Config loaded - BASE_URL: " . BASE_URL);

if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'xegoo_db');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', '');
}

if (!defined('RECAPTCHA_SITE_KEY')) {
    define('RECAPTCHA_SITE_KEY', '6LeRdugrAAAAAPQM0iFjiYo5fvnsuxBZVAd05W8P');
}
if (!defined('RECAPTCHA_SECRET_KEY')) {
    define('RECAPTCHA_SECRET_KEY', '6LeRdugrAAAAAPRrTUxosxFZ97Dlq1KtnboxILL0');
}
if (!defined('GEMINI_API_KEY')) {
    // IMPORTANT: Move this to environment variable or .env file in production
    define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: '****');
}
if (!defined('GEMINI_API_URL')) {
    define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent');
}
date_default_timezone_set('Asia/Ho_Chi_Minh');
?>
