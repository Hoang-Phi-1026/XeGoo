<?php
require_once __DIR__ . '/../config/database.php';

class Price {
    
    /**
     * Get all prices with optional filtering and search
     */
    public static function getAll($status = null, $search = null, $routeId = null, $vehicleTypeId = null) {
        try {
            $sql = "SELECT g.*, t.kyHieuTuyen, t.diemDi, t.diemDen, l.tenLoaiVe, 
                           lpt.tenLoaiPhuongTien
                    FROM giave g 
                    JOIN tuyenduong t ON g.maTuyenDuong = t.maTuyenDuong 
                    JOIN loaive l ON g.maLoaiVe = l.maLoaiVe
                    JOIN loaiphuongtien lpt ON g.maLoaiPhuongTien = lpt.maLoaiPhuongTien";
            $params = [];
            $conditions = [];
            
            if (!empty($search)) {
                $conditions[] = "(t.kyHieuTuyen LIKE ? OR t.diemDi LIKE ? OR t.diemDen LIKE ? OR lpt.tenLoaiPhuongTien LIKE ?)";
                $params[] = '%' . $search . '%';
                $params[] = '%' . $search . '%';
                $params[] = '%' . $search . '%';
                $params[] = '%' . $search . '%';
            }
            
            if ($status !== null && $status !== '') {
                $conditions[] = "g.trangThai = ?";
                $params[] = $status;
            }
            
            if ($routeId !== null && $routeId !== '') {
                $conditions[] = "g.maTuyenDuong = ?";
                $params[] = $routeId;
            }
            
            if ($vehicleTypeId !== null && $vehicleTypeId !== '') {
                $conditions[] = "g.maLoaiPhuongTien = ?";
                $params[] = $vehicleTypeId;
            }
            
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }
            
            $sql .= " ORDER BY g.maGiaVe DESC";
            
            return fetchAll($sql, $params);
        } catch (Exception $e) {
            error_log("Price::getAll Exception: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get price by ID
     */
    public static function getById($id) {
        $sql = "SELECT g.*, t.kyHieuTuyen, t.diemDi, t.diemDen, l.tenLoaiVe,
                       lpt.tenLoaiPhuongTien
                FROM giave g 
                JOIN tuyenduong t ON g.maTuyenDuong = t.maTuyenDuong 
                JOIN loaive l ON g.maLoaiVe = l.maLoaiVe 
                JOIN loaiphuongtien lpt ON g.maLoaiPhuongTien = lpt.maLoaiPhuongTien
                WHERE g.maGiaVe = ?";
        return fetch($sql, [$id]);
    }
    
    /**
     * Create new price
     */
    public static function create($data) {
        $sql = "INSERT INTO giave (maTuyenDuong, maLoaiPhuongTien, loaiChoNgoi, maLoaiVe, giaVe, giaVeKhuyenMai, ngayBatDau, ngayKetThuc, moTa, trangThai) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['maTuyenDuong'],
            $data['maLoaiPhuongTien'], // Now using vehicle type ID
            $data['loaiChoNgoi'],
            $data['maLoaiVe'],
            $data['giaVe'],
            $data['giaVeKhuyenMai'] ?? null,
            $data['ngayBatDau'],
            $data['ngayKetThuc'],
            $data['moTa'] ?? '',
            $data['trangThai'] ?? 'Hoạt động'
        ];
        
        query($sql, $params);
        return lastInsertId();
    }
    
    /**
     * Update price
     */
    public static function update($id, $data) {
        $sql = "UPDATE giave 
                SET maTuyenDuong = ?, maLoaiPhuongTien = ?, loaiChoNgoi = ?, maLoaiVe = ?, 
                    giaVe = ?, giaVeKhuyenMai = ?, ngayBatDau = ?, ngayKetThuc = ?, 
                    moTa = ?, trangThai = ?
                WHERE maGiaVe = ?";
        
        $params = [
            $data['maTuyenDuong'],
            $data['maLoaiPhuongTien'], // Now using vehicle type ID
            $data['loaiChoNgoi'],
            $data['maLoaiVe'],
            $data['giaVe'],
            $data['giaVeKhuyenMai'] ?? null,
            $data['ngayBatDau'],
            $data['ngayKetThuc'],
            $data['moTa'],
            $data['trangThai'],
            $id
        ];
        
        return query($sql, $params);
    }
    
    /**
     * Delete price (set to expired status)
     */
    public static function delete($id) {
        $sql = "UPDATE giave SET trangThai = 'Hết hạn' WHERE maGiaVe = ?";
        return query($sql, [$id]);
    }
    
    /**
     * Check if price configuration exists (for validation)
     */
    public static function priceExists($routeId, $vehicleTypeId, $seatType, $ticketType, $startDate, $endDate, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM giave 
                WHERE maTuyenDuong = ? AND maLoaiPhuongTien = ? AND loaiChoNgoi = ? AND maLoaiVe = ?
                AND trangThai = 'Hoạt động'
                AND ((ngayBatDau <= ? AND ngayKetThuc >= ?) OR (ngayBatDau <= ? AND ngayKetThuc >= ?))";
        $params = [$routeId, $vehicleTypeId, $seatType, $ticketType, $startDate, $startDate, $endDate, $endDate];
        
        if ($excludeId) {
            $sql .= " AND maGiaVe != ?";
            $params[] = $excludeId;
        }
        
        $result = fetch($sql, $params);
        return $result['count'] > 0;
    }
    
    /**
     * Get all routes for dropdown
     */
    public static function getAllRoutes() {
        $sql = "SELECT maTuyenDuong, kyHieuTuyen, diemDi, diemDen FROM tuyenduong WHERE trangThai = 'Đang hoạt động' ORDER BY kyHieuTuyen";
        return fetchAll($sql);
    }
    
    /**
     * Get all vehicle types for dropdown
     */
    public static function getAllVehicleTypes() {
        $sql = "SELECT maLoaiPhuongTien, tenLoaiPhuongTien, soChoMacDinh, loaiChoNgoiMacDinh, hangXe 
                FROM loaiphuongtien 
                ORDER BY tenLoaiPhuongTien";
        return fetchAll($sql);
    }
    
    /**
     * Get all ticket types for dropdown
     */
    public static function getAllTicketTypes() {
        $sql = "SELECT maLoaiVe, tenLoaiVe FROM loaive WHERE trangThai = 'Hoạt động' ORDER BY maLoaiVe";
        return fetchAll($sql);
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
            'Hoạt động' => 'Hoạt động',
            'Hết hạn' => 'Hết hạn'
        ];
    }
    
    /**
     * Get statistics
     */
    public static function getStats() {
        $stats = [];
        
        // Total prices
        $result = fetch("SELECT COUNT(*) as total FROM giave");
        $stats['total'] = $result['total'];
        
        // Active prices
        $result = fetch("SELECT COUNT(*) as active FROM giave WHERE trangThai = 'Hoạt động'");
        $stats['active'] = $result['active'];
        
        // Expired prices
        $result = fetch("SELECT COUNT(*) as expired FROM giave WHERE trangThai = 'Hết hạn'");
        $stats['expired'] = $result['expired'];
        
        // Average price
        $result = fetch("SELECT AVG(giaVe) as avg_price FROM giave WHERE trangThai = 'Hoạt động'");
        $stats['avg_price'] = round($result['avg_price'], 0);
        
        // Price by ticket type
        $stats['by_ticket_type'] = fetchAll("
            SELECT l.tenLoaiVe, COUNT(*) as count, AVG(g.giaVe) as avg_price
            FROM giave g 
            JOIN loaive l ON g.maLoaiVe = l.maLoaiVe 
            WHERE g.trangThai = 'Hoạt động'
            GROUP BY l.tenLoaiVe 
            ORDER BY count DESC
        ");
        
        return $stats;
    }
    
    
    /**
     * Advanced search prices with multiple criteria
     */
    public static function search($criteria) {
        try {
            $sql = "SELECT g.*, t.kyHieuTuyen, t.diemDi, t.diemDen, l.tenLoaiVe,
                           lpt.tenLoaiPhuongTien
                    FROM giave g 
                    JOIN tuyenduong t ON g.maTuyenDuong = t.maTuyenDuong 
                    JOIN loaive l ON g.maLoaiVe = l.maLoaiVe
                    JOIN loaiphuongtien lpt ON g.maLoaiPhuongTien = lpt.maLoaiPhuongTien";
            $params = [];
            $conditions = [];
            
            if (!empty($criteria['search'])) {
                $searchTerm = '%' . trim($criteria['search']) . '%';
                $conditions[] = "(t.kyHieuTuyen LIKE ? OR t.diemDi LIKE ? OR t.diemDen LIKE ? OR lpt.tenLoaiPhuongTien LIKE ? OR g.moTa LIKE ?)";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            if (!empty($criteria['routeId'])) {
                $conditions[] = "g.maTuyenDuong = ?";
                $params[] = $criteria['routeId'];
            }
            
            if (!empty($criteria['vehicleTypeId'])) {
                $conditions[] = "g.maLoaiPhuongTien = ?";
                $params[] = $criteria['vehicleTypeId'];
            }
            
            if (!empty($criteria['seatType'])) {
                $conditions[] = "g.loaiChoNgoi = ?";
                $params[] = $criteria['seatType'];
            }
            
            if (!empty($criteria['ticketType'])) {
                $conditions[] = "g.maLoaiVe = ?";
                $params[] = $criteria['ticketType'];
            }
            
            if (!empty($criteria['status'])) {
                $conditions[] = "g.trangThai = ?";
                $params[] = $criteria['status'];
            }
            
            if (!empty($criteria['minPrice']) && is_numeric($criteria['minPrice'])) {
                $conditions[] = "g.giaVe >= ?";
                $params[] = (float)$criteria['minPrice'];
            }
            
            if (!empty($criteria['maxPrice']) && is_numeric($criteria['maxPrice'])) {
                $conditions[] = "g.giaVe <= ?";
                $params[] = (float)$criteria['maxPrice'];
            }
            
            if (!empty($criteria['dateFrom'])) {
                $conditions[] = "g.ngayKetThuc >= ?";
                $params[] = $criteria['dateFrom'];
            }
            
            if (!empty($criteria['dateTo'])) {
                $conditions[] = "g.ngayBatDau <= ?";
                $params[] = $criteria['dateTo'];
            }
            
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }
            
            $sql .= " ORDER BY g.maGiaVe DESC";
            
            return fetchAll($sql, $params);
        } catch (Exception $e) {
            error_log("Price::search Exception: " . $e->getMessage());
            return [];
        }
    }
}
?>
