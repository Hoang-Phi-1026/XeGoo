<?php
require_once __DIR__ . '/../config/database.php';

class DriverReport {
    
    /**
     * Get today's trips for driver (all statuses)
     */
    public static function getTodayTrips($driverId) {
        try {
            $sql = "SELECT c.maChuyenXe, c.ngayKhoiHanh, c.thoiGianKhoiHanh, c.thoiGianKetThuc, 
                           c.trangThai, c.soChoTong, c.soChoDaDat, c.maPhuongTien, c.maLichTrinh,
                           c.maTaiXe
                    FROM chuyenxe c
                    WHERE c.maTaiXe = ? 
                    AND DATE(c.ngayKhoiHanh) = CURDATE()
                    AND c.trangThai IN ('Sẵn sàng', 'Khởi hành', 'Hoàn thành', 'Delay')
                    ORDER BY c.thoiGianKhoiHanh ASC";
            
            $trips = fetchAll($sql, [$driverId]);
            error_log("[getTodayTrips] Found " . count($trips) . " trips for driver $driverId");
            
            foreach ($trips as &$trip) {
                // Get route info
                $routeSql = "SELECT t.kyHieuTuyen, t.diemDi, t.diemDen
                            FROM tuyenduong t
                            INNER JOIN lichtrinh l ON l.maTuyenDuong = t.maTuyenDuong
                            WHERE l.maLichTrinh = ?";
                $routeInfo = fetch($routeSql, [$trip['maLichTrinh']]);
                if ($routeInfo) {
                    $trip['kyHieuTuyen'] = $routeInfo['kyHieuTuyen'];
                    $trip['diemDi'] = $routeInfo['diemDi'];
                    $trip['diemDen'] = $routeInfo['diemDen'];
                }
                
                // Get vehicle info
                $vehicleSql = "SELECT bienSo FROM phuongtien WHERE maPhuongTien = ?";
                $vehicleInfo = fetch($vehicleSql, [$trip['maPhuongTien']]);
                if ($vehicleInfo) {
                    $trip['bienSo'] = $vehicleInfo['bienSo'];
                }
            }
            
            return $trips;
            
        } catch (Exception $e) {
            error_log("[getTodayTrips] ERROR: " . $e->getMessage());
            error_log("[getTodayTrips] Stack trace: " . $e->getTraceAsString());
            return [];
        }
    }

    /**
     * Get upcoming trips for driver (backward compatibility)
     */
    public static function getUpcomingTrips($driverId) {
        return self::getTodayTrips($driverId);
    }
    
    /**
     * Get trip details for driver
     */
    public static function getTripDetails($tripId, $driverId) {
        try {
            $sql = "SELECT c.*, 
                           t.kyHieuTuyen, t.diemDi, t.diemDen, t.thoiGianDiChuyen,
                           p.bienSo, p.maPhuongTien,
                           lpt.tenLoaiPhuongTien, lpt.soTang, lpt.soHang, 
                           lpt.soCotTrai, lpt.soCotGiua, lpt.soCotPhai
                    FROM chuyenxe c
                    INNER JOIN lichtrinh l ON c.maLichTrinh = l.maLichTrinh
                    INNER JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong
                    INNER JOIN phuongtien p ON c.maPhuongTien = p.maPhuongTien
                    INNER JOIN loaiphuongtien lpt ON p.maLoaiPhuongTien = lpt.maLoaiPhuongTien
                    WHERE c.maChuyenXe = ? AND c.maTaiXe = ?";
            
            return fetch($sql, [$tripId, $driverId]);
            
        } catch (Exception $e) {
            error_log("DriverReport::getTripDetails error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get passenger list for a trip
     */
    public static function getTripPassengers($tripId) {
        try {
            $sql = "SELECT cd.maChiTiet, cd.hoTenHanhKhach, cd.emailHanhKhach, cd.soDienThoaiHanhKhach,
                           g.soGhe, cd.giaVe,
                           dd.tenDiem as diemDonTen, dd.diaChi as diemDonDiaChi,
                           dt.tenDiem as diemTraTen, dt.diaChi as diemTraDiaChi,
                           d.maDatVe
                    FROM chitiet_datve cd
                    INNER JOIN datve d ON cd.maDatVe = d.maDatVe
                    INNER JOIN ghe g ON cd.maGhe = g.maGhe
                    LEFT JOIN tuyenduong_diemdontra dd ON cd.maDiemDon = dd.maDiem
                    LEFT JOIN tuyenduong_diemdontra dt ON cd.maDiemTra = dt.maDiem
                    WHERE cd.maChuyenXe = ? 
                    AND cd.trangThai = 'DaThanhToan'
                    ORDER BY g.soGhe ASC";
            
            return fetchAll($sql, [$tripId]);
            
        } catch (Exception $e) {
            error_log("DriverReport::getTripPassengers error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Save attendance records
     */
    public static function saveAttendance($tripId, $attendanceData) {
        try {
            query("START TRANSACTION");
            
            // Create attendance table if not exists
            $createTableSql = "CREATE TABLE IF NOT EXISTS diemdanh_hankhach (
                maDiemDanh INT AUTO_INCREMENT PRIMARY KEY,
                maChiTiet INT NOT NULL,
                maChuyenXe INT NOT NULL,
                trangThaiDiemDanh ENUM('Đã lên xe', 'Vắng mặt') NOT NULL,
                ghiChu TEXT,
                thoiGianDiemDanh DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (maChiTiet) REFERENCES chitiet_datve(maChiTiet),
                FOREIGN KEY (maChuyenXe) REFERENCES chuyenxe(maChuyenXe)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            query($createTableSql);
            
            // Save each attendance record
            foreach ($attendanceData as $ticketId => $data) {
                $status = $data['status'] ?? 'Vắng mặt';
                $note = $data['note'] ?? '';
                
                $sql = "INSERT INTO diemdanh_hankhach (maChiTiet, maChuyenXe, trangThaiDiemDanh, ghiChu)
                        VALUES (?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE 
                        trangThaiDiemDanh = VALUES(trangThaiDiemDanh),
                        ghiChu = VALUES(ghiChu),
                        thoiGianDiemDanh = CURRENT_TIMESTAMP";
                
                query($sql, [$ticketId, $tripId, $status, $note]);
            }
            
            query("COMMIT");
            return ['success' => true, 'message' => 'Đã lưu điểm danh thành công'];
            
        } catch (Exception $e) {
            query("ROLLBACK");
            error_log("DriverReport::saveAttendance error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Save attendance report to baocao_chuyendi and baocao_hanhkhach
     */
    public static function saveAttendanceReport($tripId, $driverId, $attendanceData, $tripNotes = '') {
        try {
            error_log("[v0] DriverReport::saveAttendanceReport - Starting for trip $tripId");
            error_log("[v0] Attendance data count: " . count($attendanceData));
            
            query("START TRANSACTION");
            
            $deleteOldReportsSql = "SELECT maBaoCao FROM baocao_chuyendi 
                                   WHERE maChuyenXe = ? AND trangThai = 'Chờ khởi hành'";
            $oldReports = fetchAll($deleteOldReportsSql, [$tripId]);
            
            foreach ($oldReports as $oldReport) {
                // Delete passenger records for old reports
                $deletePassengersSql = "DELETE FROM baocao_hanhkhach WHERE maBaoCao = ?";
                query($deletePassengersSql, [$oldReport['maBaoCao']]);
                
                // Delete old report
                $deleteReportSql = "DELETE FROM baocao_chuyendi WHERE maBaoCao = ?";
                query($deleteReportSql, [$oldReport['maBaoCao']]);
                
                error_log("[v0] Deleted old report: " . $oldReport['maBaoCao']);
            }
            
            $totalPassengers = count($attendanceData);
            $presentCount = 0;
            $absentCount = 0;
            
            foreach ($attendanceData as $data) {
                if (($data['status'] ?? '') === 'Đã lên xe') {
                    $presentCount++;
                } else {
                    $absentCount++;
                }
            }
            
            error_log("[v0] Stats - Total: $totalPassengers, Present: $presentCount, Absent: $absentCount");
            
            $reportSql = "INSERT INTO baocao_chuyendi 
                         (maChuyenXe, maTaiXe, xacNhanKhoiHanh, thoiGianXacNhan, 
                          tongSoHanhKhach, soHanhKhachCoMat, soHanhKhachVang, ghiChu, trangThai)
                         VALUES (?, ?, 1, NOW(), ?, ?, ?, ?, 'Chờ khởi hành')";
            
            query($reportSql, [$tripId, $driverId, $totalPassengers, $presentCount, $absentCount, $tripNotes]);
            
            $reportId = lastInsertId();
            error_log("[v0] Created report with ID: $reportId");
            
            foreach ($attendanceData as $ticketId => $data) {
                $status = ($data['status'] ?? 'Vắng mặt') === 'Đã lên xe' ? 'Đã lên xe' : 'Vắng mặt';
                $note = $data['note'] ?? '';
                
                error_log("[v0] Processing ticket $ticketId - Status: $status");
                
                if ($status === 'Đã lên xe') {
                    $passengerSql = "INSERT INTO baocao_hanhkhach 
                                    (maBaoCao, maChiTiet, trangThai, thoiGianLenXe, ghiChu)
                                    VALUES (?, ?, ?, NOW(), ?)";
                    query($passengerSql, [$reportId, $ticketId, $status, $note]);
                } else {
                    $passengerSql = "INSERT INTO baocao_hanhkhach 
                                    (maBaoCao, maChiTiet, trangThai, ghiChu)
                                    VALUES (?, ?, ?, ?)";
                    query($passengerSql, [$reportId, $ticketId, $status, $note]);
                }
            }
            
            query("COMMIT");
            error_log("[v0] DriverReport::saveAttendanceReport - Success!");
            return ['success' => true, 'message' => 'Đã lưu báo cáo thành công', 'reportId' => $reportId];
            
        } catch (Exception $e) {
            query("ROLLBACK");
            error_log("[v0] DriverReport::saveAttendanceReport error: " . $e->getMessage());
            error_log("[v0] Stack trace: " . $e->getTraceAsString());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Update trip status
     */
    public static function updateTripStatus($tripId, $status) {
        try {
            $sql = "UPDATE chuyenxe SET trangThai = ? WHERE maChuyenXe = ?";
            return query($sql, [$status, $tripId]);
            
        } catch (Exception $e) {
            error_log("DriverReport::updateTripStatus error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Complete a trip (update status to Hoàn thành)
     */
    public static function completeTrip($tripId, $driverId) {
        try {
            // Verify trip belongs to driver
            $trip = self::getTripDetails($tripId, $driverId);
            if (!$trip) {
                return ['success' => false, 'message' => 'Không tìm thấy chuyến đi'];
            }

            // Only allow completing trips with status "Khởi hành"
            if ($trip['trangThai'] !== 'Khởi hành') {
                return ['success' => false, 'message' => 'Chỉ có thể kết thúc chuyến xe đang khởi hành'];
            }

            $sql = "UPDATE chuyenxe SET trangThai = 'Hoàn thành', thoiGianKetThuc = NOW() WHERE maChuyenXe = ?";
            query($sql, [$tripId]);

            return ['success' => true, 'message' => 'Đã kết thúc chuyến xe thành công'];

        } catch (Exception $e) {
            error_log("DriverReport::completeTrip error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get all seats for a specific vehicle
     */
    public static function getVehicleSeats($vehicleId) {
        try {
            $sql = "SELECT soGhe FROM ghe WHERE maPhuongTien = ? ORDER BY soGhe ASC";
            return fetchAll($sql, [$vehicleId]);
        } catch (Exception $e) {
            error_log("DriverReport::getVehicleSeats error: " . $e->getMessage());
            return [];
        }
    }
}
