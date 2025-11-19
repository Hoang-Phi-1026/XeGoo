<?php
// /**
//  * CRON JOB - Gửi thông báo nhắc tài xế trước 30 phút khởi hành
//  * 
//  * SETUP:
//  * Linux/Mac crontab:
//  *   */5 * * * * /usr/bin/php /path/to/xegoo/cron/send-driver-reminders.php
//  * 
//  * Windows Task Scheduler:
//  *   Program: C:\xampp\php\php.exe
//  *   Arguments: C:\xampp\htdocs\xegoo\cron\send-driver-reminders.php
//  *   Run every 5 minutes
//  */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/cron-driver-reminder.log');

date_default_timezone_set('Asia/Ho_Chi_Minh');

$logsDir = __DIR__ . '/../logs';
if (!is_dir($logsDir)) {
    mkdir($logsDir, 0755, true);
}

error_log("=".str_repeat("=", 78));
error_log("[Cron-DriverReminder] Job started at: " . date('Y-m-d H:i:s'));

define('BASE_URL', isset($_SERVER['REQUEST_SCHEME']) ? 
    $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] 
    : 'http://localhost/xegoo');

try {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../controllers/DriverReminderController.php';
    
    $reminderController = new DriverReminderController();
    $result = $reminderController->sendDriverReminders();
    
    error_log("[Cron-DriverReminder] Result: " . json_encode($result));
    
} catch (Exception $e) {
    error_log("[Cron-DriverReminder] ❌ FATAL ERROR: " . $e->getMessage());
    error_log("[Cron-DriverReminder] File: " . $e->getFile() . " Line: " . $e->getLine());
}

error_log("[Cron-DriverReminder] Job completed at: " . date('Y-m-d H:i:s'));
error_log("=".str_repeat("=", 78)."\n");

exit(0);
?>
