<?php
require_once __DIR__ . '/../config/database.php';

class TripSearch {
    
    /**
     * Search trips based on departure, destination, and date
     * Fixed round-trip logic: return trip swaps departure and destination
     */
    public static function searchTrips($diemDi, $diemDen, $ngayDi, $ngayVe = null, $soKhach = 1) {
        try {
            $sql = "SELECT 
                        c.maChuyenXe,
                        c.ngayKhoiHanh,
                        c.thoiGianKhoiHanh,
                        c.thoiGianKetThuc,
                        c.soChoTong,
                        c.soChoDaDat,
                        c.soChoTrong,
                        c.trangThai as chuyenXeTrangThai,
                        
                        l.maLichTrinh,
                        l.tenLichTrinh,
                        l.gioKhoiHanh as lichTrinhGioKhoiHanh,
                        l.gioKetThuc as lichTrinhGioKetThuc,
                        
                        t.maTuyenDuong,
                        t.kyHieuTuyen,
                        t.diemDi,
                        t.diemDen,
                        t.khoangCach,
                        t.thoiGianDiChuyen,
                        
                        p.maPhuongTien,
                        p.bienSo,
                        p.trangThai as phuongTienTrangThai,
                        
                        lpt.maLoaiPhuongTien,
                        lpt.tenLoaiPhuongTien,
                        lpt.soChoMacDinh,
                        
                        g.maGiaVe,
                        g.giaVe,
                        g.loaiChoNgoi,
                        g.trangThai as giaVeTrangThai,
                        
                        lv.maLoaiVe,
                        lv.tenLoaiVe
                        
                    FROM chuyenxe c
                    INNER JOIN lichtrinh l ON c.maLichTrinh = l.maLichTrinh
                    INNER JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong
                    INNER JOIN phuongtien p ON c.maPhuongTien = p.maPhuongTien
                    INNER JOIN loaiphuongtien lpt ON p.maLoaiPhuongTien = lpt.maLoaiPhuongTien
                    INNER JOIN giave g ON c.maGiaVe = g.maGiaVe
                    LEFT JOIN loaive lv ON g.maLoaiVe = lv.maLoaiVe
                    
                    WHERE c.ngayKhoiHanh = ?
                      AND c.soChoTrong >= ?
                      AND c.trangThai IN ('Sẵn sàng', 'Đang bán vé')
                      AND l.trangThai = 'Hoạt động'
                      AND t.trangThai = 'Đang hoạt động'
                      AND p.trangThai = 'Đang hoạt động'
                      AND g.trangThai = 'Hoạt động'
                      AND (
                          (t.diemDi = ? AND t.diemDen = ?)
                          OR
                          (LOWER(TRIM(t.diemDi)) = LOWER(TRIM(?)) AND LOWER(TRIM(t.diemDen)) = LOWER(TRIM(?)))
                          OR
                          (t.diemDi LIKE ? AND t.diemDen LIKE ?)
                          OR
                          (t.diemDi LIKE ? AND t.diemDen LIKE ?)
                      )
                    ORDER BY c.thoiGianKhoiHanh ASC";
            
            $diemDiNormalized = self::normalizeCityName($diemDi);
            $diemDenNormalized = self::normalizeCityName($diemDen);
            $diemDiAlias = self::getCityAlias($diemDi);
            $diemDenAlias = self::getCityAlias($diemDen);
            
            $outboundParams = [
                $ngayDi,
                $soKhach,
                $diemDi, $diemDen,
                $diemDiNormalized, $diemDenNormalized,
                '%' . $diemDi . '%', '%' . $diemDen . '%',
                '%' . $diemDiAlias . '%', '%' . $diemDenAlias . '%'
            ];
            
            error_log("=== TripSearch Debug ===");
            error_log("Outbound search: '$diemDi' -> '$diemDen' on '$ngayDi'");
            
            $outboundTrips = fetchAll($sql, $outboundParams);
            error_log("Found " . count($outboundTrips) . " outbound trips");
            
            $result = [
                'outbound' => $outboundTrips,
                'return' => []
            ];
            
            if ($ngayVe && !empty($ngayVe)) {
                error_log("Return search: '$diemDen' -> '$diemDi' on '$ngayVe' (swapped)");
                
                $returnParams = [
                    $ngayVe,                    // Return date
                    $soKhach,                   // Available seats
                    $diemDen, $diemDi,         // SWAPPED: destination becomes departure
                    $diemDenNormalized, $diemDiNormalized,  // SWAPPED normalized
                    '%' . $diemDen . '%', '%' . $diemDi . '%',  // SWAPPED partial
                    '%' . $diemDenAlias . '%', '%' . $diemDiAlias . '%'  // SWAPPED alias
                ];
                
                $result['return'] = fetchAll($sql, $returnParams);
                error_log("Found " . count($result['return']) . " return trips");
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("TripSearch searchTrips error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return ['outbound' => [], 'return' => []];
        }
    }
    
    /**
     * Normalize Vietnamese city names for better matching
     */
    private static function normalizeCityName($cityName) {
        if (empty($cityName)) return '';
        
        $cityName = trim($cityName);
        
        $normalized = mb_strtolower($cityName, 'UTF-8');
        
        // Remove common prefixes and suffixes
        $patterns = [
            '/^(tp\.|thành phố|tỉnh|huyện)\s*/iu',
            '/\s*(city|province|thành phố|tỉnh)$/iu'
        ];
        
        foreach ($patterns as $pattern) {
            $normalized = preg_replace($pattern, '', $normalized);
        }
        
        // Remove extra spaces
        $normalized = preg_replace('/\s+/', ' ', trim($normalized));
        
        return $normalized;
    }
    
    /**
     * Get city aliases for common Vietnamese city names
     */
    private static function getCityAlias($cityName) {
        if (empty($cityName)) return '';
        
        $aliases = [
            // Ho Chi Minh City variations
            'sài gòn' => 'TP. Hồ Chí Minh',
            'saigon' => 'TP. Hồ Chí Minh',
            'hcm' => 'TP. Hồ Chí Minh',
            'tp hcm' => 'TP. Hồ Chí Minh',
            'hồ chí minh' => 'TP. Hồ Chí Minh',
            'thành phố hồ chí minh' => 'TP. Hồ Chí Minh',
            'tp. hồ chí minh' => 'Sài Gòn',
            
            // Da Lat variations
            'đà lạt' => 'Đà Lạt',
            'dalat' => 'Đà Lạt',
            'da lat' => 'Đà Lạt',
            
            // Nha Trang variations
            'nha trang' => 'Nha Trang',
            'nhatrang' => 'Nha Trang',
            
            // Can Tho variations
            'cần thơ' => 'Cần Thơ',
            'can tho' => 'Cần Thơ',
            'cantho' => 'Cần Thơ',
            
            // Tay Ninh variations
            'tây ninh' => 'Tây Ninh',
            'tay ninh' => 'Tây Ninh',
            'tayninh' => 'Tây Ninh'
        ];
        
        $normalized = mb_strtolower(trim($cityName), 'UTF-8');
        return $aliases[$normalized] ?? $cityName;
    }

    /**
     * Get available cities from actual database data
     */
    public static function getAvailableCities() {
        try {
            $sql = "SELECT DISTINCT diemDi as city FROM tuyenduong WHERE trangThai = 'Đang hoạt động'
                    UNION
                    SELECT DISTINCT diemDen as city FROM tuyenduong WHERE trangThai = 'Đang hoạt động'
                    ORDER BY city";
            
            $cities = fetchAll($sql);
            
            $formattedCities = [];
            foreach ($cities as $city) {
                $cityName = $city['city'];
                $formattedCities[] = [
                    'id' => $cityName,
                    'name' => $cityName
                ];
            }
            
            return $formattedCities;
        } catch (Exception $e) {
            error_log("TripSearch getAvailableCities error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get popular routes based on actual data
     */
    public static function getPopularRoutes() {
        try {
            $sql = "SELECT 
                        t.diemDi, 
                        t.diemDen, 
                        t.kyHieuTuyen, 
                        COUNT(c.maChuyenXe) as trip_count,
                        MIN(g.giaVe) as min_price
                    FROM tuyenduong t
                    INNER JOIN lichtrinh l ON t.maTuyenDuong = l.maTuyenDuong
                    INNER JOIN chuyenxe c ON l.maLichTrinh = c.maLichTrinh
                    INNER JOIN giave g ON c.maGiaVe = g.maGiaVe
                    WHERE t.trangThai = 'Đang hoạt động'
                      AND l.trangThai = 'Hoạt động'
                      AND c.trangThai IN ('Sẵn sàng', 'Đang bán vé')
                      AND c.ngayKhoiHanh >= CURDATE()
                    GROUP BY t.maTuyenDuong, t.diemDi, t.diemDen, t.kyHieuTuyen
                    HAVING trip_count > 0
                    ORDER BY trip_count DESC, t.diemDi ASC
                    LIMIT 10";
            
            return fetchAll($sql);
        } catch (Exception $e) {
            error_log("TripSearch getPopularRoutes error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get available vehicle types
     */
    public static function getVehicleTypes() {
        try {
            $sql = "SELECT DISTINCT lpt.tenLoaiPhuongTien
                    FROM loaiphuongtien lpt
                    INNER JOIN phuongtien p ON lpt.maLoaiPhuongTien = p.maLoaiPhuongTien
                    INNER JOIN chuyenxe c ON p.maPhuongTien = c.maPhuongTien
                    WHERE p.trangThai = 'Đang hoạt động'
                      AND c.trangThai IN ('Sẵn sàng', 'Đang bán vé')
                      AND c.ngayKhoiHanh >= CURDATE()
                    ORDER BY lpt.tenLoaiPhuongTien";
            
            return fetchAll($sql);
        } catch (Exception $e) {
            error_log("TripSearch getVehicleTypes error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get available seat types
     */
    public static function getSeatTypes() {
        try {
            $sql = "SELECT DISTINCT g.loaiChoNgoi
                    FROM giave g
                    INNER JOIN chuyenxe c ON g.maGiaVe = c.maGiaVe
                    WHERE g.trangThai = 'Hoạt động' 
                      AND g.loaiChoNgoi IS NOT NULL 
                      AND g.loaiChoNgoi != ''
                      AND c.trangThai IN ('Sẵn sàng', 'Đang bán vé')
                      AND c.ngayKhoiHanh >= CURDATE()
                    ORDER BY g.loaiChoNgoi";
            
            return fetchAll($sql);
        } catch (Exception $e) {
            error_log("TripSearch getSeatTypes error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Filter trips by criteria
     */
    public static function filterTrips($trips, $filters = []) {
        if (empty($trips) || empty($filters)) {
            return $trips;
        }
        
        return array_filter($trips, function($trip) use ($filters) {
            // Filter by departure time
            if (!empty($filters['departure_time'])) {
                $departureHour = (int)date('H', strtotime($trip['thoiGianKhoiHanh']));
                switch ($filters['departure_time']) {
                    case 'early_morning':
                        if ($departureHour < 4 || $departureHour >= 8) return false;
                        break;
                    case 'morning':
                        if ($departureHour < 8 || $departureHour >= 12) return false;
                        break;
                    case 'afternoon':
                        if ($departureHour < 12 || $departureHour >= 18) return false;
                        break;
                    case 'evening':
                        if ($departureHour < 18 || $departureHour >= 22) return false;
                        break;
                    case 'night':
                        if ($departureHour < 22 && $departureHour >= 4) return false;
                        break;
                }
            }
            
            // Filter by vehicle type
            if (!empty($filters['vehicle_type'])) {
                if (stripos($trip['tenLoaiPhuongTien'], $filters['vehicle_type']) === false) {
                    return false;
                }
            }
            
            // Filter by price range
            if (!empty($filters['min_price']) && $trip['giaVe'] < $filters['min_price']) {
                return false;
            }
            if (!empty($filters['max_price']) && $trip['giaVe'] > $filters['max_price']) {
                return false;
            }
            
            return true;
        });
    }
    
    /**
     * Get price range for filters
     */
    public static function getPriceRange() {
        try {
            $sql = "SELECT MIN(g.giaVe) as min_price, MAX(g.giaVe) as max_price
                    FROM giave g
                    INNER JOIN chuyenxe c ON g.maGiaVe = c.maGiaVe
                    WHERE g.trangThai = 'Hoạt động' 
                      AND g.giaVe > 0
                      AND c.trangThai IN ('Sẵn sàng', 'Đang bán vé')
                      AND c.ngayKhoiHanh >= CURDATE()";
            
            $result = fetchOne($sql);
            return [
                'min' => $result['min_price'] ?? 0,
                'max' => $result['max_price'] ?? 1000000
            ];
        } catch (Exception $e) {
            error_log("TripSearch getPriceRange error: " . $e->getMessage());
            return ['min' => 0, 'max' => 1000000];
        }
    }
    
    /**
     * Format trip data for display
     */
    public static function formatTripForDisplay($trip) {
        return [
            'id' => $trip['maChuyenXe'],
            'route' => $trip['diemDi'] . ' → ' . $trip['diemDen'],
            'departure_time' => date('H:i', strtotime($trip['thoiGianKhoiHanh'])),
            'departure_date' => date('d/m/Y', strtotime($trip['ngayKhoiHanh'])),
            'arrival_time' => date('H:i', strtotime($trip['thoiGianKetThuc'])),
            'duration' => $trip['thoiGianDiChuyen'],
            'price' => number_format($trip['giaVe'], 0, ',', '.') . ' VNĐ',
            'price_raw' => $trip['giaVe'],
            'vehicle_type' => $trip['tenLoaiPhuongTien'],
            'seat_type' => $trip['loaiChoNgoi'] ?? 'Ghế thường',
            'available_seats' => $trip['soChoTrong'],
            'total_seats' => $trip['soChoTong'],
            'vehicle_number' => $trip['bienSo'],
            'route_code' => $trip['kyHieuTuyen'],
            'schedule_name' => $trip['tenLichTrinh']
        ];
    }
    
    /**
     * Get trip pickup/dropoff points
     */
    public static function getTripPoints($tripId) {
        try {
            $sql = "SELECT 
                        dt.maDiem,
                        dt.tenDiem,
                        dt.loaiDiem,
                        dt.diaChi,
                        dt.thuTu
                    FROM tuyenduong_diemdontra dt
                    INNER JOIN chuyenxe_diemdontra cdt ON dt.maDiem = cdt.maDiem
                    WHERE cdt.maChuyenXe = ?
                      AND dt.trangThai = 'Hoạt động'
                    ORDER BY dt.loaiDiem DESC, dt.thuTu ASC";
            
            return fetchAll($sql, [$tripId]);
        } catch (Exception $e) {
            error_log("TripSearch getTripPoints error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Debug method to check database connectivity and data
     */
    public static function debugDatabase() {
        try {
            error_log("=== Database Debug ===");
            
            // Check tuyenduong table
            $routes = fetchAll("SELECT * FROM tuyenduong WHERE trangThai = 'Đang hoạt động' LIMIT 5");
            error_log("Active routes: " . count($routes));
            foreach ($routes as $route) {
                error_log("Route: {$route['diemDi']} -> {$route['diemDen']} ({$route['kyHieuTuyen']})");
            }
            
            // Check chuyenxe table
            $trips = fetchAll("SELECT COUNT(*) as count FROM chuyenxe WHERE trangThai IN ('Sẵn sàng', 'Đang bán vé') AND ngayKhoiHanh >= CURDATE()");
            error_log("Active trips: " . $trips[0]['count']);
            
            // Check sample trip data
            $sampleTrips = fetchAll("
                SELECT c.*, t.diemDi, t.diemDen, t.kyHieuTuyen 
                FROM chuyenxe c 
                INNER JOIN lichtrinh l ON c.maLichTrinh = l.maLichTrinh
                INNER JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong
                WHERE c.trangThai IN ('Sẵn sàng', 'Đang bán vé') 
                  AND c.ngayKhoiHanh >= CURDATE()
                LIMIT 3
            ");
            
            foreach ($sampleTrips as $trip) {
                error_log("Sample trip: {$trip['diemDi']} -> {$trip['diemDen']} on {$trip['ngayKhoiHanh']}");
            }
            
        } catch (Exception $e) {
            error_log("Debug error: " . $e->getMessage());
        }
    }
}
?>
