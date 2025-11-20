<?php
require_once __DIR__ . '/../config/database.php';

class DriverReminderController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
        date_default_timezone_set('Asia/Ho_Chi_Minh');
    }
    
    /**
     * Gửi thông báo nhắc tài xế trước 30 phút khởi hành
     */
    public function sendDriverReminders() {
        error_log("[DriverReminder] === START DRIVER REMINDER PROCESS ===");
        
        $currentTime = date('Y-m-d H:i:s');
        $thirtyMinutesLater = date('Y-m-d H:i:s', strtotime($currentTime . ' +30 minutes'));
        
        error_log("[DriverReminder] Current server time: " . $currentTime);
        error_log("[DriverReminder] Query params - Start: " . $currentTime . ", End: " . $thirtyMinutesLater);
        
        try {
            // This allows finding trips with NO bookings yet (driver still needs 30-min warning)
            $sql = "
                SELECT DISTINCT
                    c.maChuyenXe,
                    c.ngayKhoiHanh,
                    c.thoiGianKhoiHanh,
                    c.trangThai,
                    t.kyHieuTuyen,
                    t.diemDi,
                    t.diemDen,
                    p.bienSo,
                    tx.maNguoiDung AS maTaiXe,
                    tx.tenNguoiDung AS tenTaiXe,
                    tx.email AS emailTaiXe,
                    COALESCE(COUNT(DISTINCT CASE WHEN dv.trangThai = 'DaThanhToan' THEN dv.maDatVe END), 0) as soHanhKhach
                FROM chuyenxe c
                INNER JOIN lichtrinh l ON c.maLichTrinh = l.maLichTrinh
                INNER JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong
                INNER JOIN phuongtien p ON c.maPhuongTien = p.maPhuongTien
                LEFT JOIN nguoidung tx ON c.maTaiXe = tx.maNguoiDung
                LEFT JOIN chitiet_datve cd ON c.maChuyenXe = cd.maChuyenXe
                LEFT JOIN datve dv ON cd.maDatVe = dv.maDatVe
                WHERE 
                    c.thoiGianKhoiHanh > ?
                    AND c.thoiGianKhoiHanh <= ?
                    AND c.trangThai IN ('Sẵn sàng', 'Khởi hành')
                    AND c.maTaiXe IS NOT NULL
                GROUP BY c.maChuyenXe
                ORDER BY c.thoiGianKhoiHanh ASC
            ";
            
            $trips = fetchAll($sql, [$currentTime, $thirtyMinutesLater]);
            error_log("[DriverReminder] Found " . count($trips) . " trips departing in next 30 minutes");
            
            $remindersSent = 0;
            $remindersFailed = 0;
            
            foreach ($trips as $trip) {
                try {
                    error_log("[DriverReminder] Processing trip: " . $trip['maChuyenXe'] . ", departure: " . $trip['thoiGianKhoiHanh']);
                    
                    $this->createDriverNotification($trip);
                    
                    error_log("[DriverReminder] ✅ Reminder sent to driver: " . $trip['tenTaiXe'] . " for trip " . $trip['maChuyenXe']);
                    $remindersSent++;
                    
                } catch (Exception $e) {
                    error_log("[DriverReminder] ❌ Failed to send reminder for trip " . $trip['maChuyenXe'] . ": " . $e->getMessage());
                    $remindersFailed++;
                }
            }
            
            error_log("[DriverReminder] === DRIVER REMINDER PROCESS COMPLETED ===");
            error_log("[DriverReminder] Reminders sent: " . $remindersSent . ", Failed: " . $remindersFailed);
            
            return [
                'success' => true,
                'message' => "Gửi thành công $remindersSent thông báo, lỗi $remindersFailed",
                'remindersSent' => $remindersSent,
                'remindersFailed' => $remindersFailed
            ];
            
        } catch (Exception $e) {
            error_log("[DriverReminder] ERROR: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Tạo thông báo cho tài xế trong database
     */
    private function createDriverNotification($trip) {
        $sql = "
            INSERT INTO thong_bao_tai_xe (
                maTaiXe,
                maChuyenXe,
                tieu_de,
                noi_dung,
                loai_thong_bao,
                thoiGianKhoiHanh,
                da_xem,
                ngayTao
            ) VALUES (?, ?, ?, ?, ?, ?, FALSE, NOW())
        ";
        
        $tieu_de = "Chuyến xe sắp khởi hành - " . $trip['kyHieuTuyen'];
        
        $noi_dung = sprintf(
            "Chuyến xe %s sắp khởi hành lúc %s. Có %d hành khách. Vui lòng chuẩn bị và điểm danh hành khách.",
            $trip['kyHieuTuyen'],
            date('H:i', strtotime($trip['thoiGianKhoiHanh'])),
            $trip['soHanhKhach']
        );
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $trip['maTaiXe'],
            $trip['maChuyenXe'],
            $tieu_de,
            $noi_dung,
            'departure_reminder',
            $trip['thoiGianKhoiHanh']
        ]);
        
        error_log("[DriverReminder] Created notification for driver " . $trip['maTaiXe'] . " for trip " . $trip['maChuyenXe']);
    }
}
?>
