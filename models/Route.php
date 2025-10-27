<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/RoutePoint.php';

class Route {
    
    /**
     * Get all routes with optional filtering and search
     */
    public static function getAll($status = null, $search = null) {
    try {
        $sql = "SELECT maTuyenDuong, kyHieuTuyen, diemDi, diemDen, thoiGianDiChuyen, khoangCach, trangThai 
                FROM tuyenduong";
        $params = [];
        $conditions = [];
        
        if (!empty($search)) {
            $conditions[] = "(kyHieuTuyen LIKE ? OR diemDi LIKE ? OR diemDen LIKE ?)";
            $params[] = '%' . $search . '%';
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
        
        $sql .= " ORDER BY maTuyenDuong DESC";
        
        $conn = Database::getInstance();
        if (!$conn) {
            error_log("[Route Search] Database connection failed!");
            return [];
        }
        
        $result = fetchAll($sql, $params);
        return $result;
    } catch (Exception $e) {
        error_log("[Route Search] Exception: " . $e->getMessage());
        return [];
    }
}

    
    /**
     * Get route by ID
     */
    public static function getById($id) {
        $sql = "SELECT * FROM tuyenduong WHERE maTuyenDuong = ?";
        return fetch($sql, [$id]);
    }
    
    /**
     * Get route by ID with pickup/drop-off points
     */
    public static function getByIdWithPoints($id) {
        $route = self::getById($id);
        if ($route) {
            $route['points'] = RoutePoint::getFormattedPoints($id);
        }
        return $route;
    }
    
    /**
     * Create new route
     */
    public static function create($data) {
        $sql = "INSERT INTO tuyenduong (kyHieuTuyen, diemDi, diemDen, thoiGianDiChuyen, khoangCach, moTa, trangThai) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['kyHieuTuyen'],
            $data['diemDi'],
            $data['diemDen'],
            $data['thoiGianDiChuyen'],
            $data['khoangCach'],
            $data['moTa'] ?? '',
            $data['trangThai'] ?? 'Đang hoạt động'
        ];
        
        query($sql, $params);
        return lastInsertId();
    }
    
    /**
     * Create new route with pickup/drop-off points
     */
    public static function createWithPoints($data, $points = []) {
        try {
            // Create the route first
            $routeId = self::create($data);
            
            // Then create the pickup/drop-off points if provided
            if (!empty($points) && $routeId) {
                RoutePoint::createMultiple($routeId, $points);
            }
            
            return $routeId;
        } catch (Exception $e) {
            error_log("Error creating route with points: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update route
     */
    public static function update($id, $data) {
        $sql = "UPDATE tuyenduong 
                SET kyHieuTuyen = ?, diemDi = ?, diemDen = ?, thoiGianDiChuyen = ?, khoangCach = ?, moTa = ?, trangThai = ?
                WHERE maTuyenDuong = ?";
        
        $params = [
            $data['kyHieuTuyen'],
            $data['diemDi'],
            $data['diemDen'],
            $data['thoiGianDiChuyen'],
            $data['khoangCach'],
            $data['moTa'],
            $data['trangThai'],
            $id
        ];
        
        return query($sql, $params);
    }
    
    /**
     * Update route with pickup/drop-off points
     */
    public static function updateWithPoints($id, $data, $points = []) {
        try {
            // Update the route first
            $result = self::update($id, $data);
            
            // Then update the pickup/drop-off points
            if (!empty($points)) {
                RoutePoint::createMultiple($id, $points);
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Error updating route with points: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Delete route (set to inactive status)
     */
    public static function delete($id) {
        $sql = "UPDATE tuyenduong SET trangThai = 'Ngừng khai thác' WHERE maTuyenDuong = ?";
        return query($sql, [$id]);
    }
    
    /**
     * Check if route has active/ready trips in the future
     * Fixed query to use correct relationship: tuyenduong -> lichtrinh -> chuyenxe
     */
    public static function hasActiveFutureTrips($routeId) {
        $sql = "SELECT COUNT(*) as count FROM chuyenxe cx
                INNER JOIN lichtrinh lt ON cx.maLichTrinh = lt.maLichTrinh
                WHERE lt.maTuyenDuong = ? 
                AND cx.trangThai = 'Sẵn sàng' 
                AND cx.thoiGianKhoiHanh > NOW()";
        $result = fetch($sql, [$routeId]);
        return $result['count'] > 0;
    }
    
    /**
     * Delete route and its pickup/drop-off points
     */
    public static function deleteWithPoints($id) {
        try {
            // Delete pickup/drop-off points first
            RoutePoint::deleteByRouteId($id);
            
            // Then set route to inactive
            return self::delete($id);
        } catch (Exception $e) {
            error_log("Error deleting route with points: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Check if route code exists (for validation)
     */
    public static function routeCodeExists($kyHieuTuyen, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM tuyenduong WHERE kyHieuTuyen = ?";
        $params = [$kyHieuTuyen];
        
        if ($excludeId) {
            $sql .= " AND maTuyenDuong != ?";
            $params[] = $excludeId;
        }
        
        $result = fetch($sql, $params);
        return $result['count'] > 0;
    }
    
    /**
     * Get status options
     */
    public static function getStatusOptions() {
        return [
            'Đang hoạt động' => 'Đang hoạt động',
            'Ngừng khai thác' => 'Ngừng khai thác'
        ];
    }
    
    /**
     * Get statistics
     */
    public static function getStats() {
        $stats = [];
        
        // Total routes
        $result = fetch("SELECT COUNT(*) as total FROM tuyenduong");
        $stats['total'] = $result['total'];
        
        // Active routes
        $result = fetch("SELECT COUNT(*) as active FROM tuyenduong WHERE trangThai = 'Đang hoạt động'");
        $stats['active'] = $result['active'];
        
        // Inactive routes
        $result = fetch("SELECT COUNT(*) as inactive FROM tuyenduong WHERE trangThai = 'Ngừng khai thác'");
        $stats['inactive'] = $result['inactive'];
        
        // Average distance
        $result = fetch("SELECT AVG(khoangCach) as avg_distance FROM tuyenduong WHERE trangThai = 'Đang hoạt động'");
        $stats['avg_distance'] = round($result['avg_distance'], 1);
        
        // Popular destinations
        $stats['popular_destinations'] = fetchAll("
            SELECT diemDen, COUNT(*) as count 
            FROM tuyenduong 
            WHERE trangThai = 'Đang hoạt động' 
            GROUP BY diemDen 
            ORDER BY count DESC 
            LIMIT 5
        ");
        
        return $stats;
    }
    
    /**
     * Advanced search routes with multiple criteria
     */
    public static function search($criteria) {
        try {
            $sql = "SELECT * FROM tuyenduong";
            $params = [];
            $conditions = [];
            
            if (!empty($criteria['search'])) {
                $searchTerm = '%' . trim($criteria['search']) . '%';
                $conditions[] = "(kyHieuTuyen LIKE ? OR diemDi LIKE ? OR diemDen LIKE ? OR moTa LIKE ?)";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            if (!empty($criteria['diemDi'])) {
                $conditions[] = "diemDi LIKE ?";
                $params[] = '%' . $criteria['diemDi'] . '%';
            }
            
            if (!empty($criteria['diemDen'])) {
                $conditions[] = "diemDen LIKE ?";
                $params[] = '%' . $criteria['diemDen'] . '%';
            }
            
            if (!empty($criteria['status'])) {
                $conditions[] = "trangThai = ?";
                $params[] = $criteria['status'];
            }
            
            if (!empty($criteria['minDistance']) && is_numeric($criteria['minDistance'])) {
                $conditions[] = "khoangCach >= ?";
                $params[] = (int)$criteria['minDistance'];
            }
            
            if (!empty($criteria['maxDistance']) && is_numeric($criteria['maxDistance'])) {
                $conditions[] = "khoangCach <= ?";
                $params[] = (int)$criteria['maxDistance'];
            }
            
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }
            
            $sql .= " ORDER BY maTuyenDuong DESC";
            
            error_log("[Route Advanced Search] SQL: " . $sql);
            error_log("[Route Advanced Search] Params: " . json_encode($params));
            
            $conn = Database::getInstance();
            if (!$conn) {
                error_log("[Route Advanced Search] Database connection failed!");
                return [];
            }
            
            $result = fetchAll($sql, $params);
            error_log("[Route Advanced Search] Results count: " . count($result));
            
            return $result;
        } catch (Exception $e) {
            error_log("[Route Advanced Search] Exception: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Format travel time for display
     */
    public static function formatTravelTime($time) {
        $parts = explode(':', $time);
        $hours = (int)$parts[0];
        $minutes = (int)$parts[1];
        
        if ($hours > 0) {
            return $hours . 'h' . ($minutes > 0 ? ' ' . $minutes . 'p' : '');
        } else {
            return $minutes . 'p';
        }
    }
    
    /**
     * Get popular cities for dropdown
     */
    public static function getPopularCities() {
        return [
            'TP. Hồ Chí Minh',
            'Hà Nội',
            'Đà Nẵng',
            'Cần Thơ',
            'Đà Lạt',
            'Nha Trang',
            'Vũng Tàu',
            'Phan Thiết',
            'Quy Nhon',
            'Huế'
        ];
    }
    
    /**
     * Get unique starting points from active routes
     */
    public static function getUniqueStartPoints() {
        $sql = "SELECT DISTINCT diemDi FROM tuyenduong WHERE trangThai = 'Đang hoạt động' ORDER BY diemDi";
        $results = fetchAll($sql);
        return array_column($results, 'diemDi');
    }
    
    /**
     * Get unique end points from active routes
     */
    public static function getUniqueEndPoints() {
        $sql = "SELECT DISTINCT diemDen FROM tuyenduong WHERE trangThai = 'Đang hoạt động' ORDER BY diemDen";
        $results = fetchAll($sql);
        return array_column($results, 'diemDen');
    }
}
