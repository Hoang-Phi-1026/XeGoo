<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/EmailService.php';

class CancellationNotificationController {
    private $db;
    private $emailService;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->emailService = new EmailService();
    }
    
    /**
     * Gửi email thông báo hủy chuyến xe và hoàn điểm tích lũy cho khách hàng
     */
    public function sendCancellationNotifications() {
        error_log("[CancellationNotification] === START CANCELLATION NOTIFICATION PROCESS ===");
        
        try {
            $currentTime = date('Y-m-d H:i:s');
            error_log("[CancellationNotification] Current server time: " . $currentTime);
            
            // === BƯỚC 1: Tìm chuyến xe bị hủy chưa gửi thông báo ===
            $tripsSql = "
                SELECT DISTINCT
                    c.maChuyenXe,
                    c.ngayKhoiHanh,
                    c.thoiGianKhoiHanh,
                    c.trangThai,
                    t.kyHieuTuyen,
                    t.diemDi,
                    t.diemDen,
                    COUNT(DISTINCT dv.maDatVe) as soBooking
                FROM chuyenxe c
                INNER JOIN lichtrinh l ON c.maLichTrinh = l.maLichTrinh
                INNER JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong
                INNER JOIN chitiet_datve cd ON c.maChuyenXe = cd.maChuyenXe
                INNER JOIN datve dv ON cd.maDatVe = dv.maDatVe
                WHERE 
                    c.trangThai = 'Bị hủy'
                    AND dv.trangThai IN ('DaThanhToan', 'HetHieuLuc')
                    AND cd.trangThai = 'DaThanhToan'
                    AND NOT EXISTS (
                        SELECT 1 FROM nhac_nho_email nne
                        WHERE nne.maChuyenXe = c.maChuyenXe
                        AND nne.kieuNhacNho = 'cancellation-notification'
                        AND nne.trangThai = 'sent'
                    )
                GROUP BY c.maChuyenXe, c.ngayKhoiHanh, c.thoiGianKhoiHanh, c.trangThai, t.kyHieuTuyen, t.diemDi, t.diemDen
                ORDER BY c.ngayKhoiHanh ASC
            ";
            
            $trips = fetchAll($tripsSql, []);
            
            error_log("[CancellationNotification] Found " . count($trips) . " cancelled trips");
            
            if (empty($trips)) {
                error_log("[CancellationNotification] No cancelled trips found. Process completed.");
                return [
                    'success' => true,
                    'message' => 'Không có chuyến xe bị hủy',
                    'notificationsSent' => 0
                ];
            }
            
            $notificationsSent = 0;
            $notificationsFailed = 0;
            
            // === BƯỚC 2: Với mỗi chuyến xe bị hủy ===
            foreach ($trips as $trip) {
                $maChuyenXe = $trip['maChuyenXe'];
                
                error_log("[CancellationNotification] Processing cancelled trip: $maChuyenXe");
                
                // Lấy khách hàng có vé cho chuyến này
                $bookingsSql = "
                    SELECT DISTINCT
                        dv.maDatVe,
                        dv.maNguoiDung,
                        dv.tongTienSauGiam,
                        nd.eMail,
                        nd.tenNguoiDung,
                        nd.soDienThoai,
                        COUNT(cd.maChiTiet) as soLuongVe
                    FROM datve dv
                    INNER JOIN chitiet_datve cd ON dv.maDatVe = cd.maDatVe
                    INNER JOIN nguoidung nd ON dv.maNguoiDung = nd.maNguoiDung
                    WHERE 
                        cd.maChuyenXe = ?
                        AND dv.trangThai IN ('DaThanhToan', 'HetHieuLuc')
                        AND cd.trangThai = 'DaThanhToan'
                    GROUP BY dv.maDatVe, dv.maNguoiDung, dv.tongTienSauGiam, nd.eMail, nd.tenNguoiDung, nd.soDienThoai
                ";
                
                $bookings = fetchAll($bookingsSql, [$maChuyenXe]);
                
                error_log("[CancellationNotification] Found " . count($bookings) . " customers for cancelled trip $maChuyenXe");
                
                // === BƯỚC 3: Xử lý từng khách hàng ===
                foreach ($bookings as $booking) {
                    $maDatVe = $booking['maDatVe'];
                    $maNguoiDung = $booking['maNguoiDung'];
                    $email = $booking['eMail'];
                    $tongTienSauGiam = $booking['tongTienSauGiam'];
                    $tenNguoiDung = $booking['tenNguoiDung'] ?? 'Khách hàng';
                    
                    // Tính điểm tích lũy = 1% của số tiền (ví dụ: 650000 đồng = 6500 điểm)
                    $diemTichLuy = intval($tongTienSauGiam / 100);
                    
                    error_log("[CancellationNotification] Processing booking $maDatVe, amount: $tongTienSauGiam, points: $diemTichLuy");
                    
                    // === BƯỚC 4: Gửi email thông báo hủy ===
                    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $sendResult = $this->sendCancellationEmail($booking, $trip, $diemTichLuy);
                        
                        if ($sendResult['success']) {
                            // === BƯỚC 5: Kiểm tra user có tài khoản không và hoàn điểm ===
                            $userExists = fetch("SELECT maNguoiDung FROM nguoidung WHERE maNguoiDung = ?", [$maNguoiDung]);
                            
                            if ($userExists) {
                                // Thêm điểm tích lũy vào tài khoản
                                $this->addRefundPoints($maNguoiDung, $maDatVe, $diemTichLuy);
                                error_log("[CancellationNotification] ✅ Refund points added to user $maNguoiDung: $diemTichLuy points");
                            } else {
                                error_log("[CancellationNotification] ⚠️ User $maNguoiDung not found in system");
                            }
                            
                            // === BƯỚC 6: Ghi lại thông báo đã gửi ===
                            $this->recordNotificationSent($maDatVe, $maChuyenXe, $maNguoiDung, $email, 'sent');
                            $this->createInAppNotification($maDatVe, $maChuyenXe, $maNguoiDung, $diemTichLuy);
                            
                            $notificationsSent++;
                            error_log("[CancellationNotification] ✅ Cancellation notification sent to $email for booking $maDatVe");
                        } else {
                            $this->recordNotificationSent($maDatVe, $maChuyenXe, $maNguoiDung, $email, 'failed', $sendResult['message']);
                            $notificationsFailed++;
                            error_log("[CancellationNotification] ❌ Failed to send cancellation notification to $email: " . $sendResult['message']);
                        }
                    } else {
                        error_log("[CancellationNotification] Invalid email for user: $maNguoiDung");
                    }
                }
            }
            
            error_log("[CancellationNotification] === CANCELLATION NOTIFICATION PROCESS COMPLETED ===");
            error_log("[CancellationNotification] Notifications sent: $notificationsSent, Failed: $notificationsFailed");
            
            return [
                'success' => true,
                'message' => "Gửi thành công $notificationsSent thông báo, lỗi $notificationsFailed",
                'notificationsSent' => $notificationsSent,
                'notificationsFailed' => $notificationsFailed
            ];
            
        } catch (Exception $e) {
            error_log("[CancellationNotification] ❌ Critical error: " . $e->getMessage());
            error_log("[CancellationNotification] Stack trace: " . $e->getTraceAsString());
            return [
                'success' => false,
                'message' => 'Lỗi khi gửi thông báo hủy chuyến: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Gửi email thông báo hủy chuyến xe
     */
    private function sendCancellationEmail($booking, $trip, $diemTichLuy) {
        try {
            $this->emailService->clearMailer();
            
            $email = $booking['eMail'];
            $tenNguoiDung = $booking['tenNguoiDung'] ?? 'Khách hàng';
            $soLuongVe = $booking['soLuongVe'] ?? 1;
            $tongTienSauGiam = $booking['tongTienSauGiam'];
            
            $tripInfo = [
                'kyHieuTuyen' => $trip['kyHieuTuyen'] ?? 'N/A',
                'diemDi' => $trip['diemDi'] ?? 'N/A',
                'diemDen' => $trip['diemDen'] ?? 'N/A',
                'ngayKhoiHanh' => $trip['ngayKhoiHanh'] ?? '',
                'thoiGianKhoiHanh' => $trip['thoiGianKhoiHanh'] ?? ''
            ];
            
            return $this->emailService->sendCancellationNotificationEmail($email, $tenNguoiDung, $tripInfo, $tongTienSauGiam, $diemTichLuy);
            
        } catch (Exception $e) {
            error_log("[CancellationNotification] sendCancellationEmail error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Hoàn điểm tích lũy vào tài khoản khách hàng
     */
    private function addRefundPoints($maNguoiDung, $maDatVe, $diemTichLuy) {
        try {
            // Kiểm tra xem có record hoàn vé này chưa
            $existingSql = "
                SELECT maDiem FROM diem_tichluy
                WHERE maNguoiDung = ? AND maDatVe = ? AND nguon = 'HoanVe'
            ";
            
            $existing = fetch($existingSql, [$maNguoiDung, $maDatVe]);
            
            if (!$existing) {
                // Thêm điểm hoàn vé
                $sql = "
                    INSERT INTO diem_tichluy 
                    (maNguoiDung, nguon, diem, maDatVe, ghiChu, ngayTao)
                    VALUES 
                    (?, 'HoanVe', ?, ?, 'Hoàn 100% từ hủy chuyến xe', NOW())
                ";
                
                $stmt = query($sql, [$maNguoiDung, $diemTichLuy, $maDatVe]);
                
                error_log("[CancellationNotification] Added refund points: user=$maNguoiDung, points=$diemTichLuy, booking=$maDatVe");
            } else {
                error_log("[CancellationNotification] Refund points already added for user=$maNguoiDung, booking=$maDatVe");
            }
            
        } catch (Exception $e) {
            error_log("[CancellationNotification] Error adding refund points: " . $e->getMessage());
        }
    }
    
    /**
     * Ghi lại thông tin gửi thông báo hủy chuyến vào database
     */
    private function recordNotificationSent($maDatVe, $maChuyenXe, $maNguoiDung, $email, $trangThai, $ghiChu = null) {
        try {
            $sql = "
                INSERT INTO nhac_nho_email 
                (maDatVe, maChuyenXe, maNguoiDung, emailNguoiDung, kieuNhacNho, trangThai, ghiChu)
                VALUES 
                (?, ?, ?, ?, 'cancellation-notification', ?, ?)
                ON DUPLICATE KEY UPDATE 
                    trangThai = VALUES(trangThai),
                    ghiChu = VALUES(ghiChu),
                    thoiGianGui = CURRENT_TIMESTAMP
            ";
            
            $stmt = query($sql, [$maDatVe, $maChuyenXe, $maNguoiDung, $email, $trangThai, $ghiChu]);
            
            error_log("[CancellationNotification] Recorded notification: booking=$maDatVe, trip=$maChuyenXe, status=$trangThai");
            
        } catch (Exception $e) {
            error_log("[CancellationNotification] Error recording notification: " . $e->getMessage());
        }
    }
    
    /**
     * Tạo thông báo trong ứng dụng cho khách hàng
     */
    private function createInAppNotification($maDatVe, $maChuyenXe, $maNguoiDung, $diemTichLuy) {
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
                error_log("[CancellationNotification] Trip info not found for maChuyenXe=$maChuyenXe");
                return;
            }
            
            $noiDung = "Chuyến xe $tripInfo[kyHieuTuyen] từ $tripInfo[diemDi] đến $tripInfo[diemDen] đã bị hủy do sự cố. Bạn sẽ nhận được $diemTichLuy điểm hoàn về tài khoản. Vui lòng liên hệ hotline để được hỗ trợ.";
            
            $sql = "
                INSERT INTO thong_bao_khach_hang 
                (maDatVe, maChuyenXe, maNguoiDung, tieu_de, noi_dung, loai_thong_bao, da_xem)
                VALUES 
                (?, ?, ?, 'Chuyến xe của bạn đã bị hủy', ?, 'cancellation-notification', FALSE)
                ON DUPLICATE KEY UPDATE ngayTao = CURRENT_TIMESTAMP
            ";
            
            $stmt = query($sql, [$maDatVe, $maChuyenXe, $maNguoiDung, $noiDung]);
            
            error_log("[CancellationNotification] Created in-app notification for user=$maNguoiDung, booking=$maDatVe");
            
            $this->createPopupNotification($maDatVe, $maChuyenXe, $maNguoiDung, 'cancellation-popup', $diemTichLuy);
            
        } catch (Exception $e) {
            error_log("[CancellationNotification] Error creating in-app notification: " . $e->getMessage());
        }
    }
    
    /**
     * Tạo thông báo popup cho khách hàng khi chuyến xe bị hủy
     */
    private function createPopupNotification($maDatVe, $maChuyenXe, $maNguoiDung, $loaiThongBao, $diemTichLuy) {
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
                error_log("[CancellationNotification] Trip info not found for popup");
                return;
            }
            
            $noiDung = "Chuyến xe $tripInfo[kyHieuTuyen] từ $tripInfo[diemDi] đến $tripInfo[diemDen] đã bị hủy do sự cố. Bạn sẽ nhận được $diemTichLuy điểm hoàn về tài khoản. Liên hệ hotline để được hỗ trợ.";
            
            $sql = "
                INSERT INTO popup_notifications 
                (maDatVe, maChuyenXe, maNguoiDung, loaiThongBao, tieu_de, noi_dung, icon_class, background_color)
                VALUES 
                (?, ?, ?, ?, 'Chuyến xe của bạn đã bị hủy', ?, 'fas fa-times-circle', '#E74C3C')
                ON DUPLICATE KEY UPDATE thoiGianTao = CURRENT_TIMESTAMP
            ";
            
            $stmt = query($sql, [$maDatVe, $maChuyenXe, $maNguoiDung, $loaiThongBao, $noiDung]);
            
            error_log("[CancellationNotification] Created popup notification for booking=$maDatVe, trip=$maChuyenXe");
            
        } catch (Exception $e) {
            error_log("[CancellationNotification] Error creating popup notification: " . $e->getMessage());
        }
    }
}
?>
