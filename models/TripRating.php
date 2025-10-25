<?php

class TripRating {
    
    /**
     * Get existing rating for a trip by user
     */
    public static function getUserRatingForTrip($tripId, $userId) {
        try {
            $sql = "SELECT * FROM danhgia_chuyendi 
                    WHERE maChuyenXe = ? AND maNguoiDung = ?
                    LIMIT 1";
            return fetch($sql, [$tripId, $userId]);
        } catch (Exception $e) {
            error_log("getUserRatingForTrip error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get rating by ID
     */
    public static function getRatingById($ratingId) {
        try {
            $sql = "SELECT * FROM danhgia_chuyendi WHERE maDanhGia = ?";
            return fetch($sql, [$ratingId]);
        } catch (Exception $e) {
            error_log("getRatingById error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Save or update trip rating
     */
    public static function saveRating($tripId, $userId, $ratingData) {
        try {
            // Check if rating already exists
            $existingRating = self::getUserRatingForTrip($tripId, $userId);
            
            if ($existingRating) {
                // Update existing rating: include maChiTiet and maTaiXe so driver ID is stored/updated
                $sql = "UPDATE danhgia_chuyendi 
                        SET maChiTiet = ?,
                            maTaiXe = ?,
                            diemDichVu = ?, 
                            diemTaiXe = ?, 
                            diemPhuongTien = ?, 
                            ghichu = ?,
                            ngayTao = NOW()
                        WHERE maDanhGia = ?";

                query($sql, [
                    $ratingData['maChiTiet'] ?? null,
                    $ratingData['maTaiXe'] ?? null,
                    $ratingData['diemDichVu'] ?? null,
                    $ratingData['diemTaiXe'] ?? null,
                    $ratingData['diemPhuongTien'] ?? null,
                    $ratingData['ghichu'] ?? null,
                    $existingRating['maDanhGia']
                ]);

                return $existingRating['maDanhGia'];
            } else {
                // Insert new rating
                $sql = "INSERT INTO danhgia_chuyendi 
                        (maChuyenXe, maNguoiDung, maChiTiet, maTaiXe, diemDichVu, diemTaiXe, diemPhuongTien, ghichu, ngayTao)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                
                query($sql, [
                    $tripId,
                    $userId,
                    $ratingData['maChiTiet'] ?? null,
                    $ratingData['maTaiXe'] ?? null,
                    $ratingData['diemDichVu'] ?? null,
                    $ratingData['diemTaiXe'] ?? null,
                    $ratingData['diemPhuongTien'] ?? null,
                    $ratingData['ghichu'] ?? null
                ]);
                
                return lastInsertId();
            }
        } catch (Exception $e) {
            error_log("saveRating error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get average ratings for a trip
     */
    public static function getTripAverageRatings($tripId) {
        try {
            $sql = "SELECT 
                        COUNT(*) as totalRatings,
                        ROUND(AVG(diemDichVu), 1) as avgService,
                        ROUND(AVG(diemTaiXe), 1) as avgDriver,
                        ROUND(AVG(diemPhuongTien), 1) as avgVehicle,
                        ROUND((AVG(diemDichVu) + AVG(diemTaiXe) + AVG(diemPhuongTien)) / 3, 1) as avgOverall
                    FROM danhgia_chuyendi 
                    WHERE maChuyenXe = ?";
            
            return fetch($sql, [$tripId]);
        } catch (Exception $e) {
            error_log("getTripAverageRatings error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get all ratings for a trip
     */
    public static function getTripRatings($tripId, $limit = 10, $offset = 0) {
        try {
            $sql = "SELECT d.*, u.tenNguoiDung, u.anhDaiDien
                    FROM danhgia_chuyendi d
                    INNER JOIN nguoidung u ON d.maNguoiDung = u.maNguoiDung
                    WHERE d.maChuyenXe = ?
                    ORDER BY d.ngayTao DESC
                    LIMIT ? OFFSET ?";
            
            return fetchAll($sql, [$tripId, $limit, $offset]);
        } catch (Exception $e) {
            error_log("getTripRatings error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Delete rating
     */
    public static function deleteRating($ratingId) {
        try {
            $sql = "DELETE FROM danhgia_chuyendi WHERE maDanhGia = ?";
            query($sql, [$ratingId]);
            return true;
        } catch (Exception $e) {
            error_log("deleteRating error: " . $e->getMessage());
            return false;
        }
    }
}
