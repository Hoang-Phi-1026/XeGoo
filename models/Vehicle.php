<?php
require_once __DIR__ . '/../config/database.php';

class Vehicle {
    
    /**
     * Get all vehicles with optional filtering and search
     */
    public static function getAll($status = null, $search = null) {
        try {
            $sql = "SELECT p.*, lpt.tenLoaiPhuongTien, lpt.soChoMacDinh, lpt.loaiChoNgoiMacDinh, lpt.hangXe
                    FROM phuongtien p 
                    JOIN loaiphuongtien lpt ON p.maLoaiPhuongTien = lpt.maLoaiPhuongTien";
            $params = [];
            $conditions = [];
            
            if (!empty($search)) {
                $conditions[] = "(p.bienSo LIKE ? OR lpt.tenLoaiPhuongTien LIKE ?)";
                $params[] = '%' . $search . '%';
                $params[] = '%' . $search . '%';
            }
            
            if ($status !== null && $status !== '') {
                $conditions[] = "p.trangThai = ?";
                $params[] = $status;
            }
            
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }
            
            $sql .= " ORDER BY p.maPhuongTien DESC";
            
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
        $sql = "SELECT p.*, lpt.tenLoaiPhuongTien, lpt.soChoMacDinh, lpt.loaiChoNgoiMacDinh, lpt.hangXe
                FROM phuongtien p 
                JOIN loaiphuongtien lpt ON p.maLoaiPhuongTien = lpt.maLoaiPhuongTien
                WHERE p.maPhuongTien = ?";
        return fetch($sql, [$id]);
    }
    
    public static function generateSeats($vehicleId) {
        $sql = "CALL sp_generate_ghe(?)";
        return query($sql, [$vehicleId]);
    }
    
    /**
     * Create new vehicle
     */
    public static function create($data) {
        $sql = "INSERT INTO phuongtien (maLoaiPhuongTien, bienSo, trangThai) 
                VALUES (?, ?, ?)";
        
        $params = [
            $data['maLoaiPhuongTien'],
            $data['bienSo'],
            $data['trangThai'] ?? 'Đang hoạt động'
        ];
        
        query($sql, $params);
        $vehicleId = lastInsertId();
    
        // 🔥 Gọi procedure generate ghế sau khi tạo xe
        self::generateSeats($vehicleId);
    
        return $vehicleId;
    }
    
    
    /**
     * Update vehicle
     */
    public static function update($id, $data) {
        $sql = "UPDATE phuongtien 
                SET maLoaiPhuongTien = ?, bienSo = ?, trangThai = ?
                WHERE maPhuongTien = ?";
        
        $params = [
            $data['maLoaiPhuongTien'],
            $data['bienSo'],
            $data['trangThai'],
            $id
        ];
        
        query($sql, $params);

        $old = self::getById($id);
        if ($data['maLoaiPhuongTien'] != $old['maLoaiPhuongTien']) {
            // Xóa ghế cũ
            query("DELETE FROM ghe WHERE maPhuongTien = ?", [$id]);
            // Sinh lại ghế mới
            self::generateSeats($id);
        }

        return true;
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
     * Get vehicle types from loaiphuongtien table
     */
    public static function getVehicleTypes() {
        $sql = "SELECT maLoaiPhuongTien, tenLoaiPhuongTien FROM loaiphuongtien ORDER BY tenLoaiPhuongTien";
        $types = fetchAll($sql);
        
        $result = [];
        foreach ($types as $type) {
            $result[$type['maLoaiPhuongTien']] = $type['tenLoaiPhuongTien'];
        }
        
        return $result;
    }
    
    /**
     * Get seat types
     */
    public static function getSeatTypes() {
        return [
            'Ghế ngồi' => 'Ghế ngồi',
            'Ghế VIP' => 'Ghế VIP',
            'Giường đơn' => 'Giường đơn',
            'Giường đôi' => 'Giường đôi'
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
        $stats['by_type'] = fetchAll("SELECT lpt.tenLoaiPhuongTien, COUNT(*) as count 
                                      FROM phuongtien p 
                                      JOIN loaiphuongtien lpt ON p.maLoaiPhuongTien = lpt.maLoaiPhuongTien 
                                      GROUP BY lpt.tenLoaiPhuongTien 
                                      ORDER BY count DESC");
        
        return $stats;
    }
    
    /**
     * Advanced search vehicles with multiple criteria
     */
    public static function search($criteria) {
        try {
            $sql = "SELECT p.*, lpt.tenLoaiPhuongTien, lpt.soChoMacDinh, lpt.loaiChoNgoiMacDinh, lpt.hangXe
                    FROM phuongtien p 
                    JOIN loaiphuongtien lpt ON p.maLoaiPhuongTien = lpt.maLoaiPhuongTien";
            $params = [];
            $conditions = [];
            
            if (!empty($criteria['search'])) {
                $searchTerm = '%' . trim($criteria['search']) . '%';
                $conditions[] = "(p.bienSo LIKE ? OR lpt.tenLoaiPhuongTien LIKE ? OR lpt.loaiChoNgoiMacDinh LIKE ?)";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            if (!empty($criteria['vehicleType'])) {
                $conditions[] = "p.maLoaiPhuongTien = ?";
                $params[] = $criteria['vehicleType'];
            }
            
            if (!empty($criteria['seatType'])) {
                $conditions[] = "lpt.loaiChoNgoiMacDinh = ?";
                $params[] = $criteria['seatType'];
            }
            
            if (!empty($criteria['status'])) {
                $conditions[] = "p.trangThai = ?";
                $params[] = $criteria['status'];
            }
            
            if (!empty($criteria['minSeats']) && is_numeric($criteria['minSeats'])) {
                $conditions[] = "lpt.soChoMacDinh >= ?";
                $params[] = (int)$criteria['minSeats'];
            }
            
            if (!empty($criteria['maxSeats']) && is_numeric($criteria['maxSeats'])) {
                $conditions[] = "lpt.soChoMacDinh <= ?";
                $params[] = (int)$criteria['maxSeats'];
            }
            
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }
            
            $sql .= " ORDER BY p.maPhuongTien DESC";
            
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
