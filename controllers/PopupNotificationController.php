<?php
require_once __DIR__ . '/../config/database.php';

/**
 * PopupNotificationController - Quản lý popup notifications
 * Hiển thị các popup thông báo về delay và hủy chuyến trên hệ thống
 */
class PopupNotificationController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Lấy danh sách popup notifications chưa hiển thị cho người dùng
     * GET /api/popup-notifications/pending
     */
    public function getPendingNotifications() {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                return;
            }
            
            $sql = "
                SELECT 
                    pn.maPopupNotification,
                    pn.maDatVe,
                    pn.maChuyenXe,
                    pn.tieu_de,
                    pn.noi_dung,
                    pn.loaiThongBao,
                    pn.icon_class,
                    pn.background_color,
                    pn.thoiGianDelay,
                    c.ngayKhoiHanh,
                    c.thoiGianKhoiHanh,
                    t.kyHieuTuyen,
                    t.diemDi,
                    t.diemDen,
                    dv.soLuongVe
                FROM popup_notifications pn
                INNER JOIN chuyenxe c ON pn.maChuyenXe = c.maChuyenXe
                INNER JOIN lichtrinh l ON c.maLichTrinh = l.maLichTrinh
                INNER JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong
                LEFT JOIN (
                    SELECT maDatVe, COUNT(*) as soLuongVe
                    FROM chitiet_datve
                    WHERE maChuyenXe = pn.maChuyenXe
                    GROUP BY maDatVe
                ) dv ON pn.maDatVe = dv.maDatVe
                WHERE 
                    pn.maNguoiDung = ?
                    AND pn.trangThai IN ('pending', 'shown')
                ORDER BY pn.thoiGianTao DESC
                LIMIT 10
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("[PopupNotificationController] Found " . count($notifications) . " pending notifications for user $userId");
            
            echo json_encode([
                'success' => true,
                'notifications' => $notifications,
                'count' => count($notifications)
            ]);
            
        } catch (Exception $e) {
            error_log("[PopupNotificationController] Error getting pending notifications: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error getting notifications']);
        }
    }
    
    /**
     * Cập nhật trạng thái popup notification khi người dùng đóng/xem
     * POST /api/popup-notifications/update-status
     */
    public function updateNotificationStatus() {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                return;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $maPopupNotification = $data['maPopupNotification'] ?? null;
            $trangThai = $data['trangThai'] ?? 'shown'; // pending, shown, dismissed
            
            if (!$maPopupNotification) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Missing notification ID']);
                return;
            }
            
            // Verify ownership
            $check = fetch(
                "SELECT maNguoiDung FROM popup_notifications WHERE maPopupNotification = ?",
                [$maPopupNotification]
            );
            
            if (!$check || $check['maNguoiDung'] != $userId) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Forbidden']);
                return;
            }
            
            $updateSql = "
                UPDATE popup_notifications 
                SET trangThai = ?, 
                    thoiGianDong = ?,
                    lan_hien_thi = lan_hien_thi + 1
                WHERE maPopupNotification = ?
            ";
            
            $stmt = $this->db->prepare($updateSql);
            $stmt->execute([
                $trangThai,
                ($trangThai === 'dismissed') ? date('Y-m-d H:i:s') : null,
                $maPopupNotification
            ]);
            
            error_log("[PopupNotificationController] Updated notification $maPopupNotification status to $trangThai");
            
            echo json_encode(['success' => true, 'message' => 'Status updated']);
            
        } catch (Exception $e) {
            error_log("[PopupNotificationController] Error updating status: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error updating status']);
        }
    }
    
    /**
     * Đánh dấu tất cả popup notifications là đã xem
     * POST /api/popup-notifications/mark-all-shown
     */
    public function markAllAsShown() {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                return;
            }
            
            $sql = "
                UPDATE popup_notifications 
                SET trangThai = 'shown'
                WHERE maNguoiDung = ? AND trangThai = 'pending'
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            
            error_log("[PopupNotificationController] Marked all notifications as shown for user $userId");
            
            echo json_encode(['success' => true, 'message' => 'All marked as shown']);
            
        } catch (Exception $e) {
            error_log("[PopupNotificationController] Error marking all as shown: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error marking notifications']);
        }
    }
}
?>
