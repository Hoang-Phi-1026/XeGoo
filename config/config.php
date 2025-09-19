<?php
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/xegoo');
}

// Turn off error display in production
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

// Log errors instead of displaying them
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

// Set error reporting level
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

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

date_default_timezone_set('Asia/Ho_Chi_Minh');
?>
