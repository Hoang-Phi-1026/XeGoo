<?php
// /**
//  * CRON JOB - Gửi email nhắc khách hàng trước 30 phút khởi hành
//  * 
//  * SETUP:
//  * Linux/Mac crontab:
//  *   */5 * * * * /usr/bin/php /path/to/xegoo/cron/send-reminders.php
//  * 
//  * Windows Task Scheduler:
//  *   Program: C:\xampp\php\php.exe
//  *   Arguments: C:\xampp\htdocs\xegoo\cron\send-reminders.php
//  *   Run every 5 minutes
//  * 
//  * XAMPP Windows Setup:
//  * 1. Mở cmd as Administrator
//  * 2. Chạy: cd C:\xampp\php
//  * 3. Tạo task với: 
//  *    schtasks /create /tn "XeGoo_ReminderEmail" /tr "C:\xampp\php\php.exe C:\xampp\htdocs\xegoo\cron\send-reminders.php" /sc minute /mo 5
//  */

// Set up error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/cron-reminder.log');
date_default_timezone_set('Asia/Ho_Chi_Minh');
// Create logs directory if not exists
$logsDir = __DIR__ . '/../logs';
if (!is_dir($logsDir)) {
    mkdir($logsDir, 0755, true);
}

error_log("=".str_repeat("=", 78));
error_log("[Cron-SendReminders] Job started at: " . date('Y-m-d H:i:s'));

// Define BASE_URL for includes
define('BASE_URL', isset($_SERVER['REQUEST_SCHEME']) ? 
    $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] 
    : 'http://localhost/xegoo');

try {
    // Include required files
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../controllers/ReminderEmailController.php';
    
    // Create controller and run
    $reminderController = new ReminderEmailController();
    $result = $reminderController->sendPreDepartureReminders();
    
    // Log result
    error_log("[Cron-SendReminders] Result: " . json_encode($result));
    
} catch (Exception $e) {
    error_log("[Cron-SendReminders] ❌ FATAL ERROR: " . $e->getMessage());
    error_log("[Cron-SendReminders] File: " . $e->getFile() . " Line: " . $e->getLine());
    error_log("[Cron-SendReminders] Stack: " . $e->getTraceAsString());
}

error_log("[Cron-SendReminders] Job completed at: " . date('Y-m-d H:i:s'));
error_log("=".str_repeat("=", 78)."\n");

// Exit with code 0 for success
exit(0);
?>
