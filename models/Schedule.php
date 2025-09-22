<?php
require_once __DIR__ . '/../config/database.php';

class Schedule {
    
    /**
     * Get all schedules with optional filtering and search
     */
    public static function getAll($routeFilter = null, $search = null) {
        try {
            $sql = "SELECT l.*, t.kyHieuTuyen, t.diemDi, t.diemDen 
                    FROM lichtrinh l 
                    JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong";
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
        $sql = "SELECT l.*, t.kyHieuTuyen, t.diemDi, t.diemDen 
                FROM lichtrinh l 
                JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong 
                WHERE l.maLichTrinh = ?";
        return fetch($sql, [$id]);
    }
    
    /**
     * Create new schedule
     */
    public static function create($data) {
        $sql = "INSERT INTO lichtrinh (maTuyenDuong, tenLichTrinh, gioKhoiHanh, gioKetThuc, thuTrongTuan, ngayBatDau, ngayKetThuc, moTa, trangThai) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['maTuyenDuong'],
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
                SET maTuyenDuong = ?, tenLichTrinh = ?, gioKhoiHanh = ?, gioKetThuc = ?, thuTrongTuan = ?, ngayBatDau = ?, ngayKetThuc = ?, moTa = ?, trangThai = ?
                WHERE maLichTrinh = ?";
        
        $params = [
            $data['maTuyenDuong'],
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
        $sql = "SELECT l.maLichTrinh, l.tenLichTrinh, l.gioKhoiHanh, l.ngayBatDau, l.ngayKetThuc, l.thuTrongTuan,
                       t.kyHieuTuyen, t.diemDi, t.diemDen
                FROM lichtrinh l 
                JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong 
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
}
?>
