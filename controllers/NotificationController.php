<?php
require_once __DIR__ . '/../config/database.php';

/**
 * NotificationController - Quản lý thông báo trong ứng dụng cho khách hàng
 * Lấy danh sách thông báo chưa đọc, đánh dấu đã đọc
 */
class NotificationController {
    private $db;
    
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->db = Database::getInstance();
    }
    
    /**
     * Lấy danh sách thông báo chưa đọc cho người dùng hiện tại
     * GET /api/notifications/unread
     */
    public function getUnreadNotifications() {
        header('Content-Type: application/json; charset=utf-8');
        
        // Kiểm tra người dùng đã đăng nhập
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Không có quyền truy cập'
            ]);
            return;
        }
        
        try {
            $maNguoiDung = $_SESSION['user_id'];
            
            // Lấy tất cả thông báo chưa đọc, sắp xếp theo thời gian mới nhất
            $sql = "
                SELECT 
                    tkh.maThongBao,
                    tkh.maDatVe,
                    tkh.maChuyenXe,
                    tkh.tieu_de,
                    tkh.noi_dung,
                    tkh.loai_thong_bao,
                    tkh.thoiGianKhoiHanh,
                    tkh.da_xem,
                    tkh.ngayTao,
                    t.kyHieuTuyen,
                    t.diemDi,
                    t.diemDen,
                    c.ngayKhoiHanh,
                    c.thoiGianKhoiHanh AS thoiGianKhoiHanh_trip,
                    dv.soLuongVe,
                    dv.trangThai as trangThaiDatVe
                FROM thong_bao_khach_hang tkh
                INNER JOIN chuyenxe c ON tkh.maChuyenXe = c.maChuyenXe
                INNER JOIN lichtrinh l ON c.maLichTrinh = l.maLichTrinh
                INNER JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong
                INNER JOIN datve dv ON tkh.maDatVe = dv.maDatVe
                WHERE 
                    tkh.maNguoiDung = ?
                    AND tkh.da_xem = FALSE
                ORDER BY tkh.ngayTao DESC
                LIMIT 100
            ";
            
            $notifications = fetchAll($sql, [$maNguoiDung]);
            
            echo json_encode([
                'success' => true,
                'notifications' => $notifications,
                'count' => count($notifications)
            ]);
            
        } catch (Exception $e) {
            error_log("[NotificationController] Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi khi lấy thông báo'
            ]);
        }
    }
    
    /**
     * Đánh dấu một thông báo là đã đọc
     * POST /api/notifications/mark-read
     */
    public function markAsRead() {
        header('Content-Type: application/json; charset=utf-8');
        
        // Kiểm tra người dùng đã đăng nhập
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Không có quyền truy cập'
            ]);
            return;
        }
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['maThongBao'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Thiếu ID thông báo'
                ]);
                return;
            }
            
            $maThongBao = $data['maThongBao'];
            $maNguoiDung = $_SESSION['user_id'];
            
            // Kiểm tra quyền sở hữu thông báo
            $checkSql = "SELECT maNguoiDung FROM thong_bao_khach_hang WHERE maThongBao = ?";
            $notification = fetch($checkSql, [$maThongBao]);
            
            if (!$notification || $notification['maNguoiDung'] != $maNguoiDung) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'Không có quyền truy cập thông báo này'
                ]);
                return;
            }
            
            // Cập nhật thông báo là đã đọc
            $sql = "
                UPDATE thong_bao_khach_hang 
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
            error_log("[NotificationController] Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi khi cập nhật thông báo'
            ]);
        }
    }
    
    /**
     * Đánh dấu tất cả thông báo là đã đọc
     * POST /api/notifications/mark-all-read
     */
    public function markAllAsRead() {
        header('Content-Type: application/json; charset=utf-8');
        
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Không có quyền truy cập'
            ]);
            return;
        }
        
        try {
            $maNguoiDung = $_SESSION['user_id'];
            
            $sql = "
                UPDATE thong_bao_khach_hang 
                SET da_xem = TRUE, ngayXem = CURRENT_TIMESTAMP
                WHERE maNguoiDung = ? AND da_xem = FALSE
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maNguoiDung]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Đã đánh dấu tất cả thông báo là đã đọc'
            ]);
            
        } catch (Exception $e) {
            error_log("[NotificationController] Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi khi cập nhật thông báo'
            ]);
        }
    }
}
?>
