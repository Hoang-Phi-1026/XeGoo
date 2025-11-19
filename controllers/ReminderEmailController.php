<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/EmailService.php';
require_once __DIR__ . '/../models/Booking.php';

/**
 * ReminderEmailController - Xử lý gửi email nhắc khách hàng trước giờ khởi hành
 * Dùng cho cron job chạy định kỳ
 */
class ReminderEmailController {
    private $db;
    private $emailService;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->emailService = new EmailService();
    }
    
    /**
     * Gửi email nhắc nhở 30 phút trước giờ khởi hành
     * Được gọi từ cron job
     */
    public function sendPreDepartureReminders() {
        error_log("[ReminderEmail] === START REMINDER EMAIL PROCESS ===");
        
        try {
            // Lấy thời gian hiện tại của server
            $currentTime = new DateTime('now');
            $currentTimeFormatted = $currentTime->format('Y-m-d H:i:s');
            
            error_log("[ReminderEmail] Current server time: " . $currentTimeFormatted);
            
            // Tính thời gian 30 phút từ bây giờ
            $thirtyMinutesLater = clone $currentTime;
            $thirtyMinutesLater->add(new DateInterval('PT30M'));
            $thirtyMinutesLaterFormatted = $thirtyMinutesLater->format('Y-m-d H:i:s');
            
            error_log("[ReminderEmail] 30 minutes later: " . $thirtyMinutesLaterFormatted);
            
            // === BƯỚC 1: Tìm những chuyến xe khởi hành trong 30 phút nữa ===
            // Join through chitiet_datve to get bookings since datve table doesn't have maChuyenXe
            $tripsSql = "
                SELECT DISTINCT
                    c.maChuyenXe,
                    c.ngayKhoiHanh,
                    c.thoiGianKhoiHanh,
                    c.thoiGianKhoiHanh AS thoiGianKhoiHanhFull,
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
                    c.thoiGianKhoiHanh > ?
                    AND c.thoiGianKhoiHanh <= ?
                    AND c.trangThai IN ('Sẵn sàng', 'Khởi hành')
                    AND dv.trangThai = 'DaThanhToan'
                GROUP BY c.maChuyenXe, c.ngayKhoiHanh, c.thoiGianKhoiHanh, t.kyHieuTuyen, t.diemDi, t.diemDen, l.maTuyenDuong, lp.soChoMacDinh, tx.tenNguoiDung, tx.soDienThoai
                ORDER BY c.thoiGianKhoiHanh ASC
            ";
            
            $trips = fetchAll($tripsSql, [$currentTimeFormatted, $thirtyMinutesLaterFormatted]);

            error_log("[ReminderEmail] Query params - Start: $currentTimeFormatted, End: $thirtyMinutesLaterFormatted");
            error_log("[ReminderEmail] [v0] SQL Query: " . $tripsSql);
            error_log("[ReminderEmail] [v0] Bindings: [" . $currentTimeFormatted . ", " . $thirtyMinutesLaterFormatted . "]");
            
            error_log("[ReminderEmail] Found " . count($trips) . " trips departing in next 30 minutes");
            
            if (empty($trips)) {
                error_log("[ReminderEmail] No upcoming trips found. Process completed.");
                return [
                    'success' => true,
                    'message' => 'Không có chuyến xe khởi hành trong 30 phút tới',
                    'remindersSent' => 0
                ];
            }
            
            $remindersSent = 0;
            $remindersFailed = 0;
            
            // === BƯỚC 2: Với mỗi chuyến xe, tìm khách hàng có vé ===
            foreach ($trips as $trip) {
                $maChuyenXe = $trip['maChuyenXe'];
                $departureDateTime = $trip['thoiGianKhoiHanhFull'];
                
                error_log("[ReminderEmail] Processing trip: $maChuyenXe, departure: $departureDateTime");
                
                // But still need to get individual booking details for email sending
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
                
                error_log("[ReminderEmail] Found " . count($bookings) . " bookings for trip $maChuyenXe");
                
                // === BƯỚC 3: Kiểm tra xem đã gửi nhắc nhở chưa ===
                foreach ($bookings as $booking) {
                    $maDatVe = $booking['maDatVe'];
                    $email = $booking['eMail'];
                    
                    // Kiểm tra xem đã gửi reminder này chưa
                    $checkSql = "
                        SELECT maTracking FROM nhac_nho_email
                        WHERE 
                            maDatVe = ?
                            AND maChuyenXe = ?
                            AND kieuNhacNho = 'pre-departure'
                    ";
                    
                    $existing = fetch($checkSql, [$maDatVe, $maChuyenXe]);
                    
                    if ($existing) {
                        error_log("[ReminderEmail] Reminder already sent for booking $maDatVe, trip $maChuyenXe. Skipping to prevent spam.");
                        continue;
                    }
                    
                    // === BƯỚC 4: Gửi email nhắc nhở ===
                    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $sendResult = $this->sendReminderEmail($booking, $trip);
                        
                        if ($sendResult['success']) {
                            // === BƯỚC 5: Đánh dấu là đã gửi ===
                            $this->recordReminderSent(
                                $maDatVe,
                                $maChuyenXe,
                                $booking['maNguoiDung'],
                                $email,
                                $departureDateTime,
                                'sent'
                            );
                            $remindersSent++;
                            error_log("[ReminderEmail] ✅ Reminder email sent successfully to $email for booking $maDatVe");
                        } else {
                            // Ghi lại lần gửi lỗi
                            $this->recordReminderSent(
                                $maDatVe,
                                $maChuyenXe,
                                $booking['maNguoiDung'],
                                $email,
                                $departureDateTime,
                                'failed',
                                $sendResult['message']
                            );
                            $remindersFailed++;
                            error_log("[ReminderEmail] ❌ Failed to send reminder to $email: " . $sendResult['message']);
                        }
                    } else {
                        error_log("[ReminderEmail] Invalid email for user: " . ($booking['maNguoiDung'] ?? 'Unknown'));
                    }
                }
            }
            
            error_log("[ReminderEmail] === REMINDER EMAIL PROCESS COMPLETED ===");
            error_log("[ReminderEmail] Reminders sent: $remindersSent, Failed: $remindersFailed");
            
            return [
                'success' => true,
                'message' => "Gửi thành công $remindersSent email, lỗi $remindersFailed",
                'remindersSent' => $remindersSent,
                'remindersFailed' => $remindersFailed
            ];
            
        } catch (Exception $e) {
            error_log("[ReminderEmail] ❌ Critical error: " . $e->getMessage());
            error_log("[ReminderEmail] Stack trace: " . $e->getTraceAsString());
            return [
                'success' => false,
                'message' => 'Lỗi khi gửi email nhắc nhở: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Gửi email nhắc nhở cho khách hàng
     * 
     * @param array $booking Thông tin booking
     * @param array $trip Thông tin chuyến xe
     * @return array Result
     */
    private function sendReminderEmail($booking, $trip) {
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
                'tenTaiXe' => $trip['tenTaiXe'] ?? 'N/A',
                'soDienThoaiTaiXe' => $trip['soDienThoaiTaiXe'] ?? 'N/A'
            ];
            
            return $this->emailService->sendPreDepartureReminderEmail($email, $tenNguoiDung, $tripInfo, $soLuongVe);
            
        } catch (Exception $e) {
            error_log("[ReminderEmail] sendReminderEmail error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Ghi lại thông tin gửi nhắc nhở vào database
     * 
     * @param int $maDatVe
     * @param int $maChuyenXe
     * @param int $maNguoiDung
     * @param string $email
     * @param string $thoiGianDuKien
     * @param string $trangThai (sent/failed)
     * @param string $ghiChu
     */
    private function recordReminderSent($maDatVe, $maChuyenXe, $maNguoiDung, $email, $thoiGianDuKien, $trangThai, $ghiChu = null) {
        try {
            $sql = "
                INSERT INTO nhac_nho_email 
                (maDatVe, maChuyenXe, maNguoiDung, emailNguoiDung, kieuNhacNho, thoiGianDuKien, trangThai, ghiChu)
                VALUES 
                (?, ?, ?, ?, 'pre-departure', ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    trangThai = VALUES(trangThai),
                    ghiChu = VALUES(ghiChu),
                    thoiGianGui = CURRENT_TIMESTAMP
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maDatVe, $maChuyenXe, $maNguoiDung, $email, $thoiGianDuKien, $trangThai, $ghiChu]);
            
            error_log("[ReminderEmail] Recorded reminder: booking=$maDatVe, trip=$maChuyenXe, status=$trangThai");
            
            if ($trangThai === 'sent') {
                $this->createInAppNotification($maDatVe, $maChuyenXe, $maNguoiDung, $thoiGianDuKien);
            }
            
        } catch (Exception $e) {
            error_log("[ReminderEmail] Error recording reminder: " . $e->getMessage());
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
                error_log("[ReminderEmail] Trip info not found for maChuyenXe=$maChuyenXe");
                return;
            }
            
            $sql = "
                INSERT INTO thong_bao_khach_hang 
                (maDatVe, maChuyenXe, maNguoiDung, tieu_de, noi_dung, loai_thong_bao, thoiGianKhoiHanh, da_xem)
                VALUES 
                (?, ?, ?, 'Nhắc nhở chuyến xe sắp khởi hành', 'Chuyến xe của bạn sẽ khởi hành trong 30 phút. Vui lòng chuẩn bị sẵn sàng lên xe.', 'reminder-departure', ?, FALSE)
                ON DUPLICATE KEY UPDATE ngayTao = CURRENT_TIMESTAMP
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maDatVe, $maChuyenXe, $maNguoiDung, $thoiGianDuKien]);
            
            error_log("[ReminderEmail] Created in-app notification for user=$maNguoiDung, booking=$maDatVe, trip=$maChuyenXe");
            
        } catch (Exception $e) {
            error_log("[ReminderEmail] Error creating in-app notification: " . $e->getMessage());
        }
    }
}
?>
