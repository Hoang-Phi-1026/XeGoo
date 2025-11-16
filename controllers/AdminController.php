<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class AdminController {
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    // kiểm tra admin
    private function checkAdminAccess() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
            $_SESSION['error'] = 'Bạn không có quyền truy cập chức năng này!';
            header('Location: ' . BASE_URL . '/login');
            exit();
        }
    }

    // Admin dashboard with module cards
    public function index() {
        $this->checkAdminAccess();
        
        // Get statistics for dashboard cards
        $stats = $this->getDashboardStats();
        
        require_once __DIR__ . '/../views/admin/dashboard.php';
    }

    // Get dashboard statistics
    private function getDashboardStats() {
        try {
            $stats = [
                'vehicles' => 0,
                'routes' => 0,
                'schedules' => 0,
                'trips' => 0,
                'trips_today' => 0,
                'users' => 0,
                'prices' => 0
            ];

            // Count vehicles - using correct status value
            $result = fetch("SELECT COUNT(*) as count FROM phuongtien WHERE trangThai = 'Đang hoạt động'");
            $stats['vehicles'] = $result ? $result['count'] : 0;

            // Count routes - using correct status value
            $result = fetch("SELECT COUNT(*) as count FROM tuyenduong WHERE trangThai = 'Đang hoạt động'");
            $stats['routes'] = $result ? $result['count'] : 0;

            // Count schedules - using correct status value
            $result = fetch("SELECT COUNT(*) as count FROM lichtrinh WHERE trangThai = 'Hoạt động'");
            $stats['schedules'] = $result ? $result['count'] : 0;

            $result = fetch("SELECT COUNT(*) as count FROM chuyenxe WHERE trangThai IN ('Sẵn sàng', 'Đang bán vé', 'Đã khởi hành')");
            $stats['trips'] = $result ? $result['count'] : 0;

            // Count users - using maTrangThai field with numeric value (0 = active)
            $result = fetch("SELECT COUNT(*) as count FROM nguoidung WHERE maTrangThai = 0 OR maTrangThai IS NULL");
            $stats['users'] = $result ? $result['count'] : 0;

            // Count prices - using correct status value
            $result = fetch("SELECT COUNT(*) as count FROM giave WHERE trangThai = 'Hoạt động'");
            $stats['prices'] = $result ? $result['count'] : 0;

            $result = fetch("SELECT COUNT(*) as count FROM chuyenxe WHERE trangThai IN ('Sẵn sàng', 'Khởi hành', 'Hoàn thành', 'Bị hủy', 'Delay') AND DATE(ngayKhoiHanh) = CURDATE()");
            $stats['trips_today'] = $result ? $result['count'] : 0;

            error_log("[AdminController] Dashboard stats: " . json_encode($stats));

            return $stats;
        } catch (Exception $e) {
            error_log("Error getting dashboard stats: " . $e->getMessage());
            
            return [
                'vehicles' => 0,
                'routes' => 0,
                'schedules' => 0,
                'trips' => 0,
                'trips_today' => 0,
                'users' => 0,
                'prices' => 0
            ];
        }
    }
}
?>
