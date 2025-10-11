<?php
require_once __DIR__ . '/../config/database.php';

class DriverSchedule {
    
    /**
     * Get driver's schedule for a specific month
     */
    public static function getMonthSchedule($driverId, $month) {
        try {
            $startDate = $month . '-01';
            $endDate = date('Y-m-t', strtotime($startDate));
            
            $sql = "SELECT c.maChuyenXe, c.ngayKhoiHanh, c.thoiGianKhoiHanh, c.thoiGianKetThuc, c.trangThai,
                           t.kyHieuTuyen, t.diemDi, t.diemDen,
                           p.bienSo
                    FROM chuyenxe c
                    INNER JOIN lichtrinh l ON c.maLichTrinh = l.maLichTrinh
                    INNER JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong
                    INNER JOIN phuongtien p ON c.maPhuongTien = p.maPhuongTien
                    WHERE c.maTaiXe = ? 
                    AND c.ngayKhoiHanh BETWEEN ? AND ?
                    AND c.trangThai != 'Bị hủy'
                    ORDER BY c.thoiGianKhoiHanh ASC";
            
            return fetchAll($sql, [$driverId, $startDate, $endDate]);
            
        } catch (Exception $e) {
            error_log("DriverSchedule::getMonthSchedule error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get driver's trips for a specific day
     */
    public static function getDayTrips($driverId, $date) {
        try {
            $sql = "SELECT c.maChuyenXe, c.ngayKhoiHanh, c.thoiGianKhoiHanh, c.thoiGianKetThuc, 
                           c.trangThai, c.soChoTong, c.soChoDaDat, c.soChoTrong,
                           t.kyHieuTuyen, t.diemDi, t.diemDen, t.thoiGianDiChuyen,
                           p.bienSo, p.maLoaiPhuongTien,
                           lpt.tenLoaiPhuongTien,
                           gv.giaVe
                    FROM chuyenxe c
                    INNER JOIN lichtrinh l ON c.maLichTrinh = l.maLichTrinh
                    INNER JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong
                    INNER JOIN phuongtien p ON c.maPhuongTien = p.maPhuongTien
                    INNER JOIN loaiphuongtien lpt ON p.maLoaiPhuongTien = lpt.maLoaiPhuongTien
                    LEFT JOIN giave gv ON c.maGiaVe = gv.maGiaVe
                    WHERE c.maTaiXe = ? 
                    AND c.ngayKhoiHanh = ?
                    AND c.trangThai != 'Bị hủy'
                    ORDER BY c.thoiGianKhoiHanh ASC";
            
            return fetchAll($sql, [$driverId, $date]);
            
        } catch (Exception $e) {
            error_log("DriverSchedule::getDayTrips error: " . $e->getMessage());
            return [];
        }
    }
}
?>
