<?php
require_once __DIR__ . '/../config/database.php';

/**
 * DriverNotificationController - Quản lý thông báo cho tài xế
 */
class DriverNotificationController {
    private $db;
    
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->db = Database::getInstance();
    }
    
    /**
     * Lấy danh sách thông báo chưa đọc cho tài xế
     * GET /api/driver/notifications/unread
     */
    public function getUnreadNotifications() {
        header('Content-Type: application/json; charset=utf-8');
        
        error_log("[v0] DriverNotificationController - getUnreadNotifications called");
        error_log("[v0] Session user_id: " . ($_SESSION['user_id'] ?? 'NOT SET'));
        error_log("[v0] Session user_role: " . ($_SESSION['user_role'] ?? 'NOT SET'));
        
        if (!isset($_SESSION['user_id'])) {
            error_log("[v0] DriverNotificationController - No user_id in session");
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Vui lòng đăng nhập'
            ]);
            return;
        }
        
        try {
            $maTaiXe = $_SESSION['user_id'];
            
            $sql = "
                SELECT 
                    maThongBao,
                    maChuyenXe,
                    tieu_de as tieu_de,
                    noi_dung as noi_dung,
                    loai_thong_bao,
                    thoiGianKhoiHanh,
                    da_xem,
                    ngayTao
                FROM thong_bao_tai_xe
                WHERE 
                    maTaiXe = ?
                    AND da_xem = 0
                ORDER BY ngayTao DESC
                LIMIT 50
            ";
            
            $notifications = fetchAll($sql, [$maTaiXe]);
            
            error_log("[v0] DriverNotificationController - Found " . count($notifications) . " notifications");
            
            echo json_encode([
                'success' => true,
                'notifications' => $notifications,
                'count' => count($notifications)
            ]);
            
        } catch (Exception $e) {
            error_log("[v0] DriverNotificationController Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi khi lấy thông báo'
            ]);
        }
    }
    
    /**
     * Đánh dấu thông báo là đã đọc
     * POST /api/driver/notifications/mark-read
     */
    public function markAsRead() {
        header('Content-Type: application/json; charset=utf-8');
        
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 3) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Không có quyền truy cập'
            ]);
            return;
        }
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $maThongBao = $data['maThongBao'] ?? null;
            
            if (!$maThongBao) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Thiếu ID thông báo'
                ]);
                return;
            }
            
            $maTaiXe = $_SESSION['user_id'];
            
            $checkSql = "SELECT maTaiXe FROM thong_bao_tai_xe WHERE maThongBao = ?";
            $notification = fetch($checkSql, [$maThongBao]);
            
            if (!$notification || $notification['maTaiXe'] != $maTaiXe) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'Không có quyền truy cập thông báo này'
                ]);
                return;
            }
            
            $sql = "
                UPDATE thong_bao_tai_xe 
                SET da_xem = TRUE, ngayXem = CURRENT_TIMESTAMP
                WHERE maThongBao = ?
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maThongBao]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Đã đánh dấu thông báo là đã đọc'
            ]);
            
        } catch (Exception $e) {
            error_log("[v0] DriverNotificationController] Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi khi cập nhật thông báo'
            ]);
        }
    }
}
?>
 