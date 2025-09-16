<?php
require_once __DIR__ . '/../config/database.php';

class Vehicle {
    
    /**
     * Get all vehicles with optional filtering and search
     */
    public static function getAll($status = null, $search = null) {
        try {
            $sql = "SELECT * FROM phuongtien";
            $params = [];
            $conditions = [];
            
            if (!empty($search)) {
                $conditions[] = "(bienSo LIKE ? OR loaiPhuongTien LIKE ?)";
                $params[] = '%' . $search . '%';
                $params[] = '%' . $search . '%';
            }
            
            if ($status !== null && $status !== '') {
                $conditions[] = "trangThai = ?";
                $params[] = $status;
            }
            
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }
            
            $sql .= " ORDER BY maPhuongTien DESC";
            
            error_log("[Vehicle Search] SQL: " . $sql);
            error_log("[Vehicle Search] Params: " . json_encode($params));
            
            // Test database connection
            $conn = Database::getInstance();
            if (!$conn) {
                error_log("[Vehicle Search] Database connection failed!");
                return [];
            }
            
            $result = fetchAll($sql, $params);
            error_log("[Vehicle Search] Results count: " . count($result));
            error_log("[Vehicle Search] First few results: " . json_encode(array_slice($result, 0, 3)));
            
            return $result;
        } catch (Exception $e) {
            error_log("[Vehicle Search] Exception: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get vehicle by ID
     */
    public static function getById($id) {
        $sql = "SELECT * FROM phuongtien WHERE maPhuongTien = ?";
        return fetch($sql, [$id]);
    }
    
    /**
     * Create new vehicle
     */
    public static function create($data) {
        $sql = "INSERT INTO phuongtien (loaiPhuongTien, soChoNgoi, loaiChoNgoi, bienSo, trangThai) 
                VALUES (?, ?, ?, ?, ?)";
        
        $params = [
            $data['loaiPhuongTien'],
            $data['soChoNgoi'],
            $data['loaiChoNgoi'],
            $data['bienSo'],
            $data['trangThai'] ?? 'Đang hoạt động'
        ];
        
        query($sql, $params);
        return lastInsertId();
    }
    
    /**
     * Update vehicle
     */
    public static function update($id, $data) {
        $sql = "UPDATE phuongtien 
                SET loaiPhuongTien = ?, soChoNgoi = ?, loaiChoNgoi = ?, bienSo = ?, trangThai = ?
                WHERE maPhuongTien = ?";
        
        $params = [
            $data['loaiPhuongTien'],
            $data['soChoNgoi'],
            $data['loaiChoNgoi'],
            $data['bienSo'],
            $data['trangThai'],
            $id
        ];
        
        return query($sql, $params);
    }
    
    /**
     * Delete vehicle (set to maintenance status)
     */
    public static function delete($id) {
        $sql = "UPDATE phuongtien SET trangThai = 'Bảo trì' WHERE maPhuongTien = ?";
        return query($sql, [$id]);
    }
    
    /**
     * Check if license plate exists (for validation)
     */
    public static function licensePlateExists($bienSo, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM phuongtien WHERE bienSo = ?";
        $params = [$bienSo];
        
        if ($excludeId) {
            $sql .= " AND maPhuongTien != ?";
            $params[] = $excludeId;
        }
        
        $result = fetch($sql, $params);
        return $result['count'] > 0;
    }
    
    /**
     * Get vehicle types
     */
    public static function getVehicleTypes() {
        return [
            '7 chỗ' => '7 chỗ',
            '16 chỗ' => '16 chỗ',
            'Limousine' => 'Limousine',
            'Ghế ngồi 32 chỗ' => 'Ghế ngồi 32 chỗ',
            'Ghế ngồi 40 chỗ' => 'Ghế ngồi 40 chỗ',
            'Giường nằm đơn' => 'Giường nằm đơn',
            'Giường nằm đôi' => 'Giường nằm đôi'
        ];
    }
    
    /**
     * Get seat types
     */
    public static function getSeatTypes() {
        return [
            'Ghế ngồi' => 'Ghế ngồi',
            'Ghế ngồi VIP' => 'Ghế ngồi VIP',
            'Giường nằm đơn' => 'Giường nằm đơn',
            'Giường nằm đôi' => 'Giường nằm đôi'
        ];
    }
    
    /**
     * Get status options
     */
    public static function getStatusOptions() {
        return [
            'Đang hoạt động' => 'Đang hoạt động',
            'Bảo trì' => 'Bảo trì'
        ];
    }
    
    /**
     * Get statistics
     */
    public static function getStats() {
        $stats = [];
        
        // Total vehicles
        $result = fetch("SELECT COUNT(*) as total FROM phuongtien");
        $stats['total'] = $result['total'];
        
        // Active vehicles
        $result = fetch("SELECT COUNT(*) as active FROM phuongtien WHERE trangThai = 'Đang hoạt động'");
        $stats['active'] = $result['active'];
        
        // Maintenance vehicles
        $result = fetch("SELECT COUNT(*) as maintenance FROM phuongtien WHERE trangThai = 'Bảo trì'");
        $stats['maintenance'] = $result['maintenance'];
        
        // By vehicle type
        $stats['by_type'] = fetchAll("SELECT loaiPhuongTien, COUNT(*) as count FROM phuongtien GROUP BY loaiPhuongTien ORDER BY count DESC");
        
        return $stats;
    }
    
    /**
     * Advanced search vehicles with multiple criteria
     */
    public static function search($criteria) {
        try {
            $sql = "SELECT * FROM phuongtien";
            $params = [];
            $conditions = [];
            
            if (!empty($criteria['search'])) {
                $searchTerm = '%' . trim($criteria['search']) . '%';
                $conditions[] = "(bienSo LIKE ? OR loaiPhuongTien LIKE ? OR loaiChoNgoi LIKE ?)";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            if (!empty($criteria['vehicleType'])) {
                $conditions[] = "loaiPhuongTien = ?";
                $params[] = $criteria['vehicleType'];
            }
            
            if (!empty($criteria['seatType'])) {
                $conditions[] = "loaiChoNgoi = ?";
                $params[] = $criteria['seatType'];
            }
            
            if (!empty($criteria['status'])) {
                $conditions[] = "trangThai = ?";
                $params[] = $criteria['status'];
            }
            
            if (!empty($criteria['minSeats']) && is_numeric($criteria['minSeats'])) {
                $conditions[] = "soChoNgoi >= ?";
                $params[] = (int)$criteria['minSeats'];
            }
            
            if (!empty($criteria['maxSeats']) && is_numeric($criteria['maxSeats'])) {
                $conditions[] = "soChoNgoi <= ?";
                $params[] = (int)$criteria['maxSeats'];
            }
            
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }
            
            $sql .= " ORDER BY maPhuongTien DESC";
            
            error_log("[Vehicle Advanced Search] SQL: " . $sql);
            error_log("[Vehicle Advanced Search] Params: " . json_encode($params));
            error_log("[Vehicle Advanced Search] Criteria: " . json_encode($criteria));
            
            // Test database connection
            $conn = Database::getInstance();
            if (!$conn) {
                error_log("[Vehicle Advanced Search] Database connection failed!");
                return [];
            }
            
            $result = fetchAll($sql, $params);
            error_log("[Vehicle Advanced Search] Results count: " . count($result));
            
            return $result;
        } catch (Exception $e) {
            error_log("[Vehicle Advanced Search] Exception: " . $e->getMessage());
            return [];
        }
    }
}
?>
