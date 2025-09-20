<?php
require_once __DIR__ . '/../config/database.php';

class Trip {
    
    /**
     * Get all trips with optional filtering and search
     */
    public static function getAll($scheduleFilter = null, $vehicleFilter = null, $statusFilter = null, $search = null, $fromDate = null, $toDate = null, $routeFilter = null) {
        try {
            $sql = "SELECT c.*, l.tenLichTrinh, l.gioKhoiHanh as lichTrinhGioKhoiHanh,
                           t.kyHieuTuyen, t.diemDi, t.diemDen,
                           p.bienSo, lpt.tenLoaiPhuongTien,
                           g.giaVe, lv.tenLoaiVe
                    FROM chuyenxe c
                    JOIN lichtrinh l ON c.maLichTrinh = l.maLichTrinh
                    JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong
                    JOIN phuongtien p ON c.maPhuongTien = p.maPhuongTien
                    JOIN loaiphuongtien lpt ON p.maLoaiPhuongTien = lpt.maLoaiPhuongTien
                    LEFT JOIN giave g ON c.maGiaVe = g.maGiaVe
                    LEFT JOIN loaive lv ON g.maLoaiVe = lv.maLoaiVe";
            
            $params = [];
            $conditions = [];
            
            if (!empty($search)) {
                $conditions[] = "(l.tenLichTrinh LIKE ? OR t.kyHieuTuyen LIKE ? OR p.bienSo LIKE ?)";
                $params[] = '%' . $search . '%';
                $params[] = '%' . $search . '%';
                $params[] = '%' . $search . '%';
            }
            
            if (!empty($fromDate)) {
                $conditions[] = "c.ngayKhoiHanh >= ?";
                $params[] = $fromDate;
            }
            
            if (!empty($toDate)) {
                $conditions[] = "c.ngayKhoiHanh <= ?";
                $params[] = $toDate;
            }
            
            if (!empty($routeFilter)) {
                $conditions[] = "l.maTuyenDuong = ?";
                $params[] = $routeFilter;
            }
            
            if ($scheduleFilter !== null && $scheduleFilter !== '') {
                $conditions[] = "c.maLichTrinh = ?";
                $params[] = $scheduleFilter;
            }
            
            if ($vehicleFilter !== null && $vehicleFilter !== '') {
                $conditions[] = "c.maPhuongTien = ?";
                $params[] = $vehicleFilter;
            }
            
            if ($statusFilter !== null && $statusFilter !== '') {
                $conditions[] = "c.trangThai = ?";
                $params[] = $statusFilter;
            }
            
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }
            
            $sql .= " ORDER BY c.ngayKhoiHanh DESC, c.thoiGianKhoiHanh DESC";
            
            return fetchAll($sql, $params);
        } catch (Exception $e) {
            error_log("Trip getAll error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get trip by ID with full details
     */
    public static function getById($id) {
        $sql = "SELECT c.*, l.tenLichTrinh, l.gioKhoiHanh as lichTrinhGioKhoiHanh, l.thuTrongTuan,
                       t.kyHieuTuyen, t.diemDi, t.diemDen, t.khoangCach, t.thoiGianDiChuyen,
                       p.bienSo, lpt.tenLoaiPhuongTien, lpt.soChoMacDinh,
                       g.giaVe, g.loaiChoNgoi, lv.tenLoaiVe
                FROM chuyenxe c
                JOIN lichtrinh l ON c.maLichTrinh = l.maLichTrinh
                JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong
                JOIN phuongtien p ON c.maPhuongTien = p.maPhuongTien
                JOIN loaiphuongtien lpt ON p.maLoaiPhuongTien = lpt.maLoaiPhuongTien
                LEFT JOIN giave g ON c.maGiaVe = g.maGiaVe
                LEFT JOIN loaive lv ON g.maLoaiVe = lv.maLoaiVe
                WHERE c.maChuyenXe = ?";
        return fetch($sql, [$id]);
    }
    
    /**
     * Get pickup/dropoff points for a trip
     */
    public static function getTripPoints($tripId) {
        $sql = "SELECT d.maDiem, d.tenDiem, d.loaiDiem, d.diaChi, d.thuTu
                FROM chuyenxe_diemdontra cd
                JOIN tuyenduong_diemdontra d ON cd.maDiem = d.maDiem
                WHERE cd.maChuyenXe = ?
                ORDER BY d.loaiDiem, d.thuTu";
        return fetchAll($sql, [$tripId]);
    }
    
    /**
     * Update trip status
     */
    public static function updateStatus($id, $status) {
        $sql = "UPDATE chuyenxe SET trangThai = ? WHERE maChuyenXe = ?";
        return query($sql, [$status, $id]);
    }
    
    /**
     * Delete trip
     */
    public static function delete($id) {
        try {
            // Delete trip points first
            $sql = "DELETE FROM chuyenxe_diemdontra WHERE maChuyenXe = ?";
            query($sql, [$id]);
            
            // Then delete the trip
            $sql = "DELETE FROM chuyenxe WHERE maChuyenXe = ?";
            return query($sql, [$id]);
        } catch (Exception $e) {
            error_log("Trip delete error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get status options
     */
    public static function getStatusOptions() {
        return [
            'Sẵn sàng' => 'Sẵn sàng',
            'Đang bán vé' => 'Đang bán vé',
            'Đã khởi hành' => 'Đã khởi hành',
            'Hoàn thành' => 'Hoàn thành',
            'Hủy' => 'Hủy',
            'Delay' => 'Delay'
        ];
    }
    
    /**
     * Get statistics
     */
    public static function getStats() {
        $stats = [];
        
        // Total trips
        $result = fetch("SELECT COUNT(*) as total FROM chuyenxe");
        $stats['total'] = $result['total'];
        
        // Ready trips
        $result = fetch("SELECT COUNT(*) as ready FROM chuyenxe WHERE trangThai = 'Sẵn sàng'");
        $stats['ready'] = $result['ready'];
        
        // Active trips (selling tickets or departed)
        $result = fetch("SELECT COUNT(*) as active FROM chuyenxe WHERE trangThai IN ('Đang bán vé', 'Đã khởi hành')");
        $stats['active'] = $result['active'];
        
        // Completed trips
        $result = fetch("SELECT COUNT(*) as completed FROM chuyenxe WHERE trangThai = 'Hoàn thành'");
        $stats['completed'] = $result['completed'];
        
        // Cancelled trips
        $result = fetch("SELECT COUNT(*) as cancelled FROM chuyenxe WHERE trangThai = 'Hủy'");
        $stats['cancelled'] = $result['cancelled'];
        
        // Today's trips
        $result = fetch("SELECT COUNT(*) as today FROM chuyenxe WHERE DATE(ngayKhoiHanh) = CURDATE()");
        $stats['today'] = $result['today'];
        
        // Average occupancy
        $result = fetch("SELECT AVG((soChoDaDat / soChoTong) * 100) as avg_occupancy FROM chuyenxe WHERE soChoTong > 0");
        $stats['avg_occupancy'] = round($result['avg_occupancy'], 1);
        
        return $stats;
    }
    
    /**
     * Get schedules for filter dropdown
     */
    public static function getSchedulesForFilter() {
        $sql = "SELECT DISTINCT l.maLichTrinh, l.tenLichTrinh, t.kyHieuTuyen
                FROM chuyenxe c
                JOIN lichtrinh l ON c.maLichTrinh = l.maLichTrinh
                JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong
                ORDER BY t.kyHieuTuyen, l.tenLichTrinh";
        return fetchAll($sql);
    }
    
    /**
     * Get vehicles for filter dropdown
     */
    public static function getVehiclesForFilter() {
        $sql = "SELECT DISTINCT p.maPhuongTien, p.bienSo, lpt.tenLoaiPhuongTien
                FROM chuyenxe c
                JOIN phuongtien p ON c.maPhuongTien = p.maPhuongTien
                JOIN loaiphuongtien lpt ON p.maLoaiPhuongTien = lpt.maLoaiPhuongTien
                ORDER BY lpt.tenLoaiPhuongTien, p.bienSo";
        return fetchAll($sql);
    }
    
    /**
     * Get routes for filter dropdown
     */
    public static function getRoutesForFilter() {
        $sql = "SELECT DISTINCT t.maTuyenDuong, t.kyHieuTuyen, t.diemDi, t.diemDen
                FROM chuyenxe c
                JOIN lichtrinh l ON c.maLichTrinh = l.maLichTrinh
                JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong
                ORDER BY t.kyHieuTuyen";
        return fetchAll($sql);
    }

    
    /**
     * Get trip status badge class
     */
    public static function getStatusBadgeClass($status) {
        $classes = [
            'Sẵn sàng' => 'ready',
            'Đang bán vé' => 'selling',
            'Đã khởi hành' => 'departed',
            'Hoàn thành' => 'completed',
            'Hủy' => 'cancelled',
            'Delay' => 'delayed'
        ];
        
        return $classes[$status] ?? 'default';
    }
    
    /**
     * Calculate occupancy percentage
     */
    public static function calculateOccupancy($soChoDaDat, $soChoTong) {
        if ($soChoTong == 0) return 0;
        return round(($soChoDaDat / $soChoTong) * 100, 1);
    }
    
    /**
     * Get trips by date range
     */
    public static function getTripsByDateRange($startDate, $endDate, $filters = []) {
        $sql = "SELECT c.*, l.tenLichTrinh, t.kyHieuTuyen, t.diemDi, t.diemDen,
                       p.bienSo, lpt.tenLoaiPhuongTien
                FROM chuyenxe c
                JOIN lichtrinh l ON c.maLichTrinh = l.maLichTrinh
                JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong
                JOIN phuongtien p ON c.maPhuongTien = p.maPhuongTien
                JOIN loaiphuongtien lpt ON p.maLoaiPhuongTien = lpt.maLoaiPhuongTien
                WHERE c.ngayKhoiHanh BETWEEN ? AND ?";
        
        $params = [$startDate, $endDate];
        
        if (!empty($filters['status'])) {
            $sql .= " AND c.trangThai = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['schedule'])) {
            $sql .= " AND c.maLichTrinh = ?";
            $params[] = $filters['schedule'];
        }
        
        $sql .= " ORDER BY c.ngayKhoiHanh, c.thoiGianKhoiHanh";
        
        return fetchAll($sql, $params);
    }
}
?>
