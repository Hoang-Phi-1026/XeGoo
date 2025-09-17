<?php
require_once __DIR__ . '/../config/database.php';

class RoutePoint {
    
    /**
     * Get all pickup/drop-off points for a specific route
     */
    public static function getByRouteId($routeId) {
        $sql = "SELECT * FROM tuyenduong_diemdontra 
                WHERE maTuyenDuong = ? 
                ORDER BY loaiDiem ASC, thuTu ASC";
        return fetchAll($sql, [$routeId]);
    }
    
    /**
     * Get pickup points for a specific route
     */
    public static function getPickupPoints($routeId) {
        $sql = "SELECT * FROM tuyenduong_diemdontra 
                WHERE maTuyenDuong = ? AND loaiDiem = 'Đón' 
                ORDER BY thuTu ASC";
        return fetchAll($sql, [$routeId]);
    }
    
    /**
     * Get drop-off points for a specific route
     */
    public static function getDropOffPoints($routeId) {
        $sql = "SELECT * FROM tuyenduong_diemdontra 
                WHERE maTuyenDuong = ? AND loaiDiem = 'Trả' 
                ORDER BY thuTu ASC";
        return fetchAll($sql, [$routeId]);
    }
    
    /**
     * Create new pickup/drop-off point
     */
    public static function create($data) {
        $sql = "INSERT INTO tuyenduong_diemdontra (maTuyenDuong, tenDiem, loaiDiem, diaChi, thuTu, trangThai) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['maTuyenDuong'],
            $data['tenDiem'],
            $data['loaiDiem'],
            $data['diaChi'] ?? '',
            $data['thuTu'] ?? 0,
            $data['trangThai'] ?? 'Hoạt động'
        ];
        
        query($sql, $params);
        return lastInsertId();
    }
    
    /**
     * Update pickup/drop-off point
     */
    public static function update($id, $data) {
        $sql = "UPDATE tuyenduong_diemdontra 
                SET tenDiem = ?, loaiDiem = ?, diaChi = ?, thuTu = ?, trangThai = ?
                WHERE maDiem = ?";
        
        $params = [
            $data['tenDiem'],
            $data['loaiDiem'],
            $data['diaChi'],
            $data['thuTu'],
            $data['trangThai'],
            $id
        ];
        
        return query($sql, $params);
    }
    
    /**
     * Delete pickup/drop-off point
     */
    public static function delete($id) {
        $sql = "DELETE FROM tuyenduong_diemdontra WHERE maDiem = ?";
        return query($sql, [$id]);
    }
    
    /**
     * Delete all points for a specific route
     */
    public static function deleteByRouteId($routeId) {
        $sql = "DELETE FROM tuyenduong_diemdontra WHERE maTuyenDuong = ?";
        return query($sql, [$routeId]);
    }
    
    /**
     * Create multiple points for a route
     */
    public static function createMultiple($routeId, $points) {
        try {
            // First, delete existing points for this route
            self::deleteByRouteId($routeId);
            
            // Then create new points
            foreach ($points as $point) {
                $point['maTuyenDuong'] = $routeId;
                self::create($point);
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error creating multiple route points: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get point types
     */
    public static function getPointTypes() {
        return [
            'Đón' => 'Điểm đón khách',
            'Trả' => 'Điểm trả khách'
        ];
    }
    
    /**
     * Get status options
     */
    public static function getStatusOptions() {
        return [
            'Hoạt động' => 'Hoạt động',
            'Ngừng' => 'Ngừng hoạt động'
        ];
    }
    
    /**
     * Validate point data
     */
    public static function validatePoint($data) {
        $errors = [];
        
        if (empty($data['tenDiem'])) {
            $errors[] = 'Tên điểm không được để trống.';
        }
        
        if (empty($data['loaiDiem']) || !in_array($data['loaiDiem'], ['Đón', 'Trả'])) {
            $errors[] = 'Loại điểm không hợp lệ.';
        }
        
        if (isset($data['thuTu']) && (!is_numeric($data['thuTu']) || $data['thuTu'] < 0)) {
            $errors[] = 'Thứ tự phải là số không âm.';
        }
        
        return $errors;
    }
    
    /**
     * Get formatted points for display
     */
    public static function getFormattedPoints($routeId) {
        $points = self::getByRouteId($routeId);
        $formatted = [
            'pickup' => [],
            'dropoff' => []
        ];
        
        foreach ($points as $point) {
            if ($point['loaiDiem'] == 'Đón') {
                $formatted['pickup'][] = $point;
            } else {
                $formatted['dropoff'][] = $point;
            }
        }
        
        return $formatted;
    }
}
?>
