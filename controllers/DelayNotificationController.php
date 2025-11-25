<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/EmailService.php';

/**
 * DelayNotificationController - Xử lý gửi email thông báo delay cho khách hàng
 * Dùng cho cron job chạy định kỳ
 */
class DelayNotificationController {
    private $db;
    private $emailService;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->emailService = new EmailService();
    }
    
    /**
     * Gửi email thông báo delay cho khách hàng khi chuyến xe có trạng thái "Delay"
     * Được gọi từ cron job
     */
    public function sendDelayNotifications() {
        error_log("[DelayNotification] === START DELAY NOTIFICATION PROCESS ===");
        
        try {
            // Lấy thời gian hiện tại của server
            $currentTime = date('Y-m-d H:i:s');
            error_log("[DelayNotification] Current server time: " . $currentTime);
            
            // === BƯỚC 1: Tìm những chuyến xe có trạng thái "Delay" ===
            $tripsSql = "
                SELECT DISTINCT
                    c.maChuyenXe,
                    c.ngayKhoiHanh,
                    c.thoiGianKhoiHanh,
                    c.trangThai,
                    c.thoiGianDelay,
                    t.kyHieuTuyen,
                    t.diemDi,
                    t.diemDen,
                    l.maTuyenDuong,
                    lp.soChoMacDinh,
                    tx.tenNguoiDung AS tenTaiXe,
                    tx.soDienThoai AS soDienThoaiTaiXe,
                    COUNT(DISTINCT dv.maDatVe) as soBooking
                FROM chuyenxe c
                INNER JOIN lichtrinh l ON c.maLichTrinh = l.maLichTrinh
                INNER JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong
                INNER JOIN phuongtien p ON c.maPhuongTien = p.maPhuongTien
                INNER JOIN loaiphuongtien lp ON p.maLoaiPhuongTien = lp.maLoaiPhuongTien
                LEFT JOIN nguoidung tx ON c.maTaiXe = tx.maNguoiDung
                INNER JOIN chitiet_datve cd ON c.maChuyenXe = cd.maChuyenXe
                INNER JOIN datve dv ON cd.maDatVe = dv.maDatVe
                WHERE 
                    c.trangThai = 'Delay'
                    AND dv.trangThai = 'DaThanhToan'
                GROUP BY c.maChuyenXe, c.ngayKhoiHanh, c.thoiGianKhoiHanh, c.trangThai, c.thoiGianDelay, t.kyHieuTuyen, t.diemDi, t.diemDen, l.maTuyenDuong, lp.soChoMacDinh, tx.tenNguoiDung, tx.soDienThoai
                ORDER BY c.thoiGianKhoiHanh ASC
            ";
            
            $trips = fetchAll($tripsSql, []);
            
            error_log("[DelayNotification] Found " . count($trips) . " trips with Delay status");
            
            if (empty($trips)) {
                error_log("[DelayNotification] No delayed trips found. Process completed.");
                return [
                    'success' => true,
                    'message' => 'Không có chuyến xe delay',
                    'notificationsSent' => 0
                ];
            }
            
            $notificationsSent = 0;
            $notificationsFailed = 0;
            
            // === BƯỚC 2: Với mỗi chuyến xe delay, tìm khách hàng có vé ===
            foreach ($trips as $trip) {
                $maChuyenXe = $trip['maChuyenXe'];
                $departureDateTime = $trip['thoiGianKhoiHanh'];
                
                error_log("[DelayNotification] Processing delayed trip: $maChuyenXe, status: " . $trip['trangThai']);
                
                // Lấy chi tiết khách hàng có vé
                $bookingsSql = "
                    SELECT DISTINCT
                        dv.maDatVe,
                        dv.maNguoiDung,
                        nd.eMail,
                        nd.tenNguoiDung,
                        nd.soDienThoai,
                        COUNT(cd.maChiTiet) as soLuongVe
                    FROM datve dv
                    INNER JOIN chitiet_datve cd ON dv.maDatVe = cd.maDatVe
                    INNER JOIN nguoidung nd ON dv.maNguoiDung = nd.maNguoiDung
                    WHERE 
                        cd.maChuyenXe = ?
                        AND dv.trangThai = 'DaThanhToan'
                    GROUP BY dv.maDatVe, dv.maNguoiDung, nd.eMail, nd.tenNguoiDung, nd.soDienThoai
                ";
                
                $bookings = fetchAll($bookingsSql, [$maChuyenXe]);
                
                error_log("[DelayNotification] Found " . count($bookings) . " customers for delayed trip $maChuyenXe");
                
                // === BƯỚC 3: Kiểm tra xem đã gửi thông báo delay chưa ===
                foreach ($bookings as $booking) {
                    $maDatVe = $booking['maDatVe'];
                    $email = $booking['eMail'];
                    
                    // Kiểm tra xem đã gửi delay notification này chưa
                    $checkSql = "
                        SELECT maTracking FROM nhac_nho_email
                        WHERE 
                            maDatVe = ?
                            AND maChuyenXe = ?
                            AND kieuNhacNho = 'delay-notification'
                    ";
                    
                    $existing = fetch($checkSql, [$maDatVe, $maChuyenXe]);
                    
                    if ($existing) {
                        error_log("[DelayNotification] Delay notification already sent for booking $maDatVe, trip $maChuyenXe. Skipping to prevent spam.");
                        continue;
                    }
                    
                    // === BƯỚC 4: Gửi email thông báo delay ===
                    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $sendResult = $this->sendDelayEmail($booking, $trip);
                        
                        if ($sendResult['success']) {
                            // === BƯỚC 5: Đánh dấu là đã gửi ===
                            $this->recordNotificationSent(
                                $maDatVe,
                                $maChuyenXe,
                                $booking['maNguoiDung'],
                                $email,
                                $departureDateTime,
                                'sent'
                            );
                            $notificationsSent++;
                            error_log("[DelayNotification] ✅ Delay notification sent to $email for booking $maDatVe");
                        } else {
                            // Ghi lại lần gửi lỗi
                            $this->recordNotificationSent(
                                $maDatVe,
                                $maChuyenXe,
                                $booking['maNguoiDung'],
                                $email,
                                $departureDateTime,
                                'failed',
                                $sendResult['message']
                            );
                            $notificationsFailed++;
                            error_log("[DelayNotification] ❌ Failed to send delay notification to $email: " . $sendResult['message']);
                        }
                    } else {
                        error_log("[DelayNotification] Invalid email for user: " . ($booking['maNguoiDung'] ?? 'Unknown'));
                    }
                }
            }
            
            error_log("[DelayNotification] === DELAY NOTIFICATION PROCESS COMPLETED ===");
            error_log("[DelayNotification] Notifications sent: $notificationsSent, Failed: $notificationsFailed");
            
            return [
                'success' => true,
                'message' => "Gửi thành công $notificationsSent thông báo, lỗi $notificationsFailed",
                'notificationsSent' => $notificationsSent,
                'notificationsFailed' => $notificationsFailed
            ];
            
        } catch (Exception $e) {
            error_log("[DelayNotification] ❌ Critical error: " . $e->getMessage());
            error_log("[DelayNotification] Stack trace: " . $e->getTraceAsString());
            return [
                'success' => false,
                'message' => 'Lỗi khi gửi thông báo delay: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Gửi email thông báo delay cho khách hàng
     * 
     * @param array $booking Thông tin booking
     * @param array $trip Thông tin chuyến xe
     * @return array Result
     */
    private function sendDelayEmail($booking, $trip) {
        try {
            $this->emailService->clearMailer();
            
            $email = $booking['eMail'];
            $tenNguoiDung = $booking['tenNguoiDung'] ?? 'Khách hàng';
            $soLuongVe = $booking['soLuongVe'] ?? 1;
            
            $tripInfo = [
                'kyHieuTuyen' => $trip['kyHieuTuyen'] ?? 'N/A',
                'diemDi' => $trip['diemDi'] ?? 'N/A',
                'diemDen' => $trip['diemDen'] ?? 'N/A',
                'ngayKhoiHanh' => $trip['ngayKhoiHanh'] ?? '',
                'thoiGianKhoiHanh' => $trip['thoiGianKhoiHanh'] ?? '',
                'thoiGianDelay' => $trip['thoiGianDelay'] ?? null,
                'tenTaiXe' => $trip['tenTaiXe'] ?? 'N/A',
                'soDienThoaiTaiXe' => $trip['soDienThoaiTaiXe'] ?? 'N/A'
            ];
            
            return $this->emailService->sendDelayNotificationEmail($email, $tenNguoiDung, $tripInfo, $soLuongVe);
            
        } catch (Exception $e) {
            error_log("[DelayNotification] sendDelayEmail error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Ghi lại thông tin gửi thông báo delay vào database
     * 
     * @param int $maDatVe
     * @param int $maChuyenXe
     * @param int $maNguoiDung
     * @param string $email
     * @param string $thoiGianDuKien
     * @param string $trangThai (sent/failed)
     * @param string $ghiChu
     */
    private function recordNotificationSent($maDatVe, $maChuyenXe, $maNguoiDung, $email, $thoiGianDuKien, $trangThai, $ghiChu = null) {
        try {
            $sql = "
                INSERT INTO nhac_nho_email 
                (maDatVe, maChuyenXe, maNguoiDung, emailNguoiDung, kieuNhacNho, thoiGianDuKien, trangThai, ghiChu)
                VALUES 
                (?, ?, ?, ?, 'delay-notification', ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    trangThai = VALUES(trangThai),
                    ghiChu = VALUES(ghiChu),
                    thoiGianGui = CURRENT_TIMESTAMP
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maDatVe, $maChuyenXe, $maNguoiDung, $email, $thoiGianDuKien, $trangThai, $ghiChu]);
            
            error_log("[DelayNotification] Recorded notification: booking=$maDatVe, trip=$maChuyenXe, status=$trangThai");
            
            if ($trangThai === 'sent') {
                $this->createInAppNotification($maDatVe, $maChuyenXe, $maNguoiDung, $thoiGianDuKien);
                $this->createPopupNotification($maDatVe, $maChuyenXe, $maNguoiDung, 'delay-popup', $thoiGianDuKien);
            }
            
        } catch (Exception $e) {
            error_log("[DelayNotification] Error recording notification: " . $e->getMessage());
        }
    }
    
    /**
     * Tạo thông báo trong ứng dụng cho khách hàng
     * 
     * @param int $maDatVe
     * @param int $maChuyenXe
     * @param int $maNguoiDung
     * @param string $thoiGianDuKien
     */
    private function createInAppNotification($maDatVe, $maChuyenXe, $maNguoiDung, $thoiGianDuKien) {
        try {
            $tripSql = "
                SELECT 
                    t.kyHieuTuyen,
                    t.diemDi,
                    t.diemDen
                FROM chuyenxe c
                INNER JOIN lichtrinh l ON c.maLichTrinh = l.maLichTrinh
                INNER JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong
                WHERE c.maChuyenXe = ?
            ";
            
            $tripInfo = fetch($tripSql, [$maChuyenXe]);
            
            if (!$tripInfo) {
                error_log("[DelayNotification] Trip info not found for maChuyenXe=$maChuyenXe");
                return;
            }
            
            $sql = "
                INSERT INTO thong_bao_khach_hang 
                (maDatVe, maChuyenXe, maNguoiDung, tieu_de, noi_dung, loai_thong_bao, thoiGianKhoiHanh, da_xem)
                VALUES 
                (?, ?, ?, 'Chuyến xe của bạn đang delay', 'Chuyến xe sắp khởi hành của bạn đã gặp sự cố và đang delay ít phút. Vui lòng chờ thêm một vài phút để khởi hành.', 'delay-notification', ?, FALSE)
                ON DUPLICATE KEY UPDATE ngayTao = CURRENT_TIMESTAMP
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maDatVe, $maChuyenXe, $maNguoiDung, $thoiGianDuKien]);
            
            error_log("[DelayNotification] Created in-app notification for user=$maNguoiDung, booking=$maDatVe, trip=$maChuyenXe");
            
        } catch (Exception $e) {
            error_log("[DelayNotification] Error creating in-app notification: " . $e->getMessage());
        }
    }

    /**
     * Tạo thông báo popup cho khách hàng khi chuyến xe delay
     */
    private function createPopupNotification($maDatVe, $maChuyenXe, $maNguoiDung, $loaiThongBao, $thoiGianDelay) {
        try {
            $tripSql = "
                SELECT 
                    t.kyHieuTuyen,
                    t.diemDi,
                    t.diemDen,
                    c.ngayKhoiHanh,
                    c.thoiGianKhoiHanh
                FROM chuyenxe c
                INNER JOIN lichtrinh l ON c.maLichTrinh = l.maLichTrinh
                INNER JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong
                WHERE c.maChuyenXe = ?
            ";
            
            $tripInfo = fetch($tripSql, [$maChuyenXe]);
            
            if (!$tripInfo) {
                error_log("[DelayNotification] Trip info not found for popup");
                return;
            }
            
            $sql = "
                INSERT INTO popup_notifications 
                (maDatVe, maChuyenXe, maNguoiDung, loaiThongBao, tieu_de, noi_dung, icon_class, background_color, thoiGianDelay)
                VALUES 
                (?, ?, ?, ?, 'Chuyến xe của bạn đang delay', 'Chuyến xe sắp khởi hành của bạn đã gặp sự cố và đang delay. Vui lòng chờ thêm thời gian để khởi hành.', 'fas fa-hourglass-half', '#F39C12', ?)
                ON DUPLICATE KEY UPDATE thoiGianTao = CURRENT_TIMESTAMP
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maDatVe, $maChuyenXe, $maNguoiDung, $loaiThongBao, $thoiGianDelay]);
            
            error_log("[DelayNotification] Created popup notification for booking=$maDatVe, trip=$maChuyenXe");
            
        } catch (Exception $e) {
            error_log("[DelayNotification] Error creating popup notification: " . $e->getMessage());
        }
    }
}
?>
