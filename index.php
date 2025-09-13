<?php
// Thiết lập múi giờ
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Định nghĩa đường dẫn gốc
define('BASE_PATH', __DIR__);
define('BASE_URL', 'http://localhost/xegoo');

// Include database config
require_once BASE_PATH . '/config/database.php';

require_once BASE_PATH . '/router.php';
?>
