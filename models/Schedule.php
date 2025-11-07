<?php
require_once __DIR__ . '/../config/database.php';

class Schedule {
    
    /**
     * Get all schedules with optional filtering and search
     */
    public static function getAll($routeFilter = null, $search = null) {
        try {
            $sql = "SELECT l.*, t.kyHieuTuyen, t.diemDi, t.diemDen,
                           nd.tenNguoiDung as tenTaiXe, nd.soDienThoai as sdtTaiXe
                    FROM lichtrinh l 
                    JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong
                    LEFT JOIN nguoidung nd ON l.maTaiXe = nd.maNguoiDung";
            $params = [];
            $conditions = [];
            
            if (!empty($search)) {
                $conditions[] = "(l.tenLichTrinh LIKE ? OR t.kyHieuTuyen LIKE ? OR t.diemDi LIKE ? OR t.diemDen LIKE ?)";
                $params[] = '%' . $search . '%';
                $params[] = '%' . $search . '%';
                $params[] = '%' . $search . '%';
                $params[] = '%' . $search . '%';
            }
            
            if ($routeFilter !== null && $routeFilter !== '') {
                $conditions[] = "l.maTuyenDuong = ?";
                $params[] = $routeFilter;
            }
            
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }
            
            $sql .= " ORDER BY l.maLichTrinh DESC";
            
            return fetchAll($sql, $params);
        } catch (Exception $e) {
            error_log("Schedule getAll error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get schedule by ID
     */
    public static function getById($id) {
        $sql = "SELECT l.*, t.kyHieuTuyen, t.diemDi, t.diemDen,
                       nd.tenNguoiDung as tenTaiXe, nd.soDienThoai as sdtTaiXe
                FROM lichtrinh l 
                JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong 
                LEFT JOIN nguoidung nd ON l.maTaiXe = nd.maNguoiDung
                WHERE l.maLichTrinh = ?";
        return fetch($sql, [$id]);
    }
    
    /**
     * Create new schedule
     */
    public static function create($data) {
        $sql = "INSERT INTO lichtrinh (maTuyenDuong, maTaiXe, tenLichTrinh, gioKhoiHanh, gioKetThuc, thuTrongTuan, ngayBatDau, ngayKetThuc, moTa, trangThai) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['maTuyenDuong'],
            $data['maTaiXe'] ?? null,
            $data['tenLichTrinh'],
            $data['gioKhoiHanh'],
            $data['gioKetThuc'],
            $data['thuTrongTuan'],
            $data['ngayBatDau'],
            $data['ngayKetThuc'],
            $data['moTa'] ?? '',
            $data['trangThai'] ?? 'Hoạt động'
        ];
        
        query($sql, $params);
        return lastInsertId();
    }
    
    /**
     * Update schedule
     */
    public static function update($id, $data) {
        $sql = "UPDATE lichtrinh 
                SET maTuyenDuong = ?, maTaiXe = ?, tenLichTrinh = ?, gioKhoiHanh = ?, gioKetThuc = ?, thuTrongTuan = ?, ngayBatDau = ?, ngayKetThuc = ?, moTa = ?, trangThai = ?
                WHERE maLichTrinh = ?";
        
        $params = [
            $data['maTuyenDuong'],
            $data['maTaiXe'] ?? null,
            $data['tenLichTrinh'],
            $data['gioKhoiHanh'],
            $data['gioKetThuc'],
            $data['thuTrongTuan'],
            $data['ngayBatDau'],
            $data['ngayKetThuc'],
            $data['moTa'],
            $data['trangThai'],
            $id
        ];
        
        return query($sql, $params);
    }
    
    /**
     * Delete schedule (set to inactive)
     */
    public static function delete($id) {
        $sql = "UPDATE lichtrinh SET trangThai = 'Ngừng' WHERE maLichTrinh = ?";
        return query($sql, [$id]);
    }
    
    /**
     * Get all routes for dropdown
     */
    public static function getAllRoutes() {
        $sql = "SELECT maTuyenDuong, kyHieuTuyen, diemDi, diemDen, thoiGianDiChuyen FROM tuyenduong WHERE trangThai = 'Đang hoạt động' ORDER BY kyHieuTuyen";
        return fetchAll($sql);
    }
    
    /**
     * Get status options
     */
    public static function getStatusOptions() {
        return [
            'Hoạt động' => 'Hoạt động',
            'Tạm dừng' => 'Tạm dừng',
            'Ngừng' => 'Ngừng'
        ];
    }
    
    /**
     * Get statistics
     */
    public static function getStats() {
        $stats = [];
        
        // Total schedules
        $result = fetch("SELECT COUNT(*) as total FROM lichtrinh");
        $stats['total'] = $result['total'];
        
        // Active schedules
        $result = fetch("SELECT COUNT(*) as active FROM lichtrinh WHERE trangThai = 'Hoạt động'");
        $stats['active'] = $result['active'];
        
        // Paused schedules
        $result = fetch("SELECT COUNT(*) as paused FROM lichtrinh WHERE trangThai = 'Tạm dừng'");
        $stats['paused'] = $result['paused'];
        
        // Stopped schedules
        $result = fetch("SELECT COUNT(*) as stopped FROM lichtrinh WHERE trangThai = 'Ngừng'");
        $stats['stopped'] = $result['stopped'];
        
        return $stats;
    }
    
    /**
     * Format days of week for display
     */
    public static function formatDaysOfWeek($thuTrongTuan) {
        $dayMap = [
            '2' => 'T2',
            '3' => 'T3', 
            '4' => 'T4',
            '5' => 'T5',
            '6' => 'T6',
            '7' => 'T7',
            'CN' => 'CN'
        ];
        
        $days = explode(',', $thuTrongTuan);
        $formattedDays = [];
        
        foreach ($days as $day) {
            if (isset($dayMap[$day])) {
                $formattedDays[] = $dayMap[$day];
            }
        }
        
        return implode(', ', $formattedDays);
    }
    
    /**
     * Get schedules for trip generation dropdown
     */
    public static function getSchedulesForGeneration() {
        $sql = "SELECT l.maLichTrinh, l.tenLichTrinh, l.gioKhoiHanh, l.ngayBatDau, l.ngayKetThuc, l.thuTrongTuan, l.maTaiXe,
                       t.kyHieuTuyen, t.diemDi, t.diemDen,
                       nd.tenNguoiDung as tenTaiXe
                FROM lichtrinh l 
                JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong 
                LEFT JOIN nguoidung nd ON l.maTaiXe = nd.maNguoiDung
                WHERE l.trangThai = 'Hoạt động' AND l.ngayKetThuc >= CURDATE()
                ORDER BY t.kyHieuTuyen, l.gioKhoiHanh";
        return fetchAll($sql);
    }
    
    /**
     * Validate schedule data
     */
    public static function validate($data) {
        $errors = [];
        
        if (empty($data['maTuyenDuong'])) {
            $errors[] = 'Vui lòng chọn tuyến đường.';
        }
        
        if (empty($data['tenLichTrinh'])) {
            $errors[] = 'Vui lòng nhập tên lịch trình.';
        }
        
        if (empty($data['gioKhoiHanh'])) {
            $errors[] = 'Vui lòng nhập giờ khởi hành.';
        }
        
        if (empty($data['gioKetThuc'])) {
            $errors[] = 'Vui lòng nhập giờ kết thúc.';
        }
        
        if (!empty($data['gioKhoiHanh']) && !empty($data['gioKetThuc'])) {
            if ($data['gioKhoiHanh'] >= $data['gioKetThuc']) {
                $errors[] = 'Giờ kết thúc phải sau giờ khởi hành.';
            }
        }
        
        if (empty($data['ngayBatDau'])) {
            $errors[] = 'Vui lòng chọn ngày bắt đầu.';
        }
        
        if (empty($data['ngayKetThuc'])) {
            $errors[] = 'Vui lòng chọn ngày kết thúc.';
        }
        
        if (!empty($data['ngayBatDau']) && !empty($data['ngayKetThuc'])) {
            if ($data['ngayBatDau'] > $data['ngayKetThuc']) {
                $errors[] = 'Ngày kết thúc phải sau ngày bắt đầu.';
            }
        }
        
        if (empty($data['thuTrongTuan'])) {
            $errors[] = 'Vui lòng chọn ít nhất một ngày trong tuần.';
        }
        
        return $errors;
    }
    
    /**
     * Check if driver has conflicting schedules
     * @param int $driverId - Driver ID to check
     * @param string $startDate - Schedule start date
     * @param string $endDate - Schedule end date
     * @param string $startTime - Schedule start time
     * @param string $endTime - Schedule end time
     * @param string $daysOfWeek - Comma-separated days (2,3,4,5,6,7,CN)
     * @param int|null $excludeScheduleId - Schedule ID to exclude from check (for editing)
     * @return array - Array of conflicting schedules
     */
    public static function checkDriverConflicts($driverId, $startDate, $endDate, $startTime, $endTime, $daysOfWeek, $excludeScheduleId = null) {
        if (empty($driverId)) {
            return [];
        }
        
        $sql = "SELECT l.*, t.kyHieuTuyen, t.diemDi, t.diemDen
                FROM lichtrinh l
                JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong
                WHERE l.maTaiXe = ? 
                  AND l.trangThai IN ('Hoạt động', 'Tạm dừng')
                  AND (
                    (l.ngayBatDau BETWEEN ? AND ?)
                    OR (l.ngayKetThuc BETWEEN ? AND ?)
                    OR (l.ngayBatDau <= ? AND l.ngayKetThuc >= ?)
                  )";
        
        $params = [$driverId, $startDate, $endDate, $startDate, $endDate, $startDate, $endDate];
        
        if ($excludeScheduleId) {
            $sql .= " AND l.maLichTrinh != ?";
            $params[] = $excludeScheduleId;
        }
        
        $existingSchedules = fetchAll($sql, $params);
        
        $conflicts = [];
        $newDays = explode(',', $daysOfWeek);
        
        foreach ($existingSchedules as $schedule) {
            $existingDays = explode(',', $schedule['thuTrongTuan']);
            
            // Check if there's any day overlap
            $dayOverlap = array_intersect($newDays, $existingDays);
            
            if (!empty($dayOverlap)) {
                // Check if there's time overlap
                if (($startTime < $schedule['gioKetThuc']) && ($endTime > $schedule['gioKhoiHanh'])) {
                    $conflicts[] = $schedule;
                }
            }
        }
        
        return $conflicts;
    }
    
    /**
     * Check if a schedule has already generated trips
     * Returns information about existing trips if found
     */
    public static function hasGeneratedTrips($scheduleId) {
        $sql = "SELECT COUNT(*) as trip_count, MIN(maPhuongTien) as first_vehicle, 
                       GROUP_CONCAT(DISTINCT maPhuongTien) as vehicles,
                       COUNT(DISTINCT maPhuongTien) as vehicle_count
                FROM chuyenxe 
                WHERE maLichTrinh = ?";
        
        $result = fetch($sql, [$scheduleId]);
        
        return [
            'has_trips' => $result['trip_count'] > 0,
            'trip_count' => $result['trip_count'],
            'vehicles' => $result['vehicles'] ? explode(',', $result['vehicles']) : [],
            'vehicle_count' => $result['vehicle_count'],
            'first_vehicle' => $result['first_vehicle']
        ];
    }

    /**
     * Get vehicle details for a specific vehicle ID
     */
    public static function getVehicleDetails($vehicleId) {
        $sql = "SELECT p.maPhuongTien, p.bienSo, lpt.tenLoaiPhuongTien
                FROM phuongtien p
                JOIN loaiphuongtien lpt ON p.maLoaiPhuongTien = lpt.maLoaiPhuongTien
                WHERE p.maPhuongTien = ?";
        
        return fetch($sql, [$vehicleId]);
    }
}
?>
