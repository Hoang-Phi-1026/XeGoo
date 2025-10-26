<?php
require_once __DIR__ . '/../config/database.php';

class Staff {
    
    /**
     * Get all trip reports with status "Chờ khởi hành" for today
     */
    public static function getTodayReports() {
        try {
           $sql = "SELECT bc.maBaoCao, bc.maChuyenXe, bc.maTaiXe, bc.tongSoHanhKhach, 
                           bc.soHanhKhachCoMat, bc.soHanhKhachVang, bc.trangThai,
                           bc.ngayTao, bc.ngayCapNhat,
                           cx.ngayKhoiHanh, cx.thoiGianKhoiHanh, cx.thoiGianKetThuc,
                           td.kyHieuTuyen, td.diemDi, td.diemDen,
                           tx.tenNguoiDung as tenTaiXe, tx.soDienThoai as sdtTaiXe
                    FROM baocao_chuyendi bc
                    INNER JOIN (
                        SELECT maChuyenXe, MAX(maBaoCao) AS maxBaoCao
                        FROM baocao_chuyendi
                        WHERE trangThai = 'Chờ khởi hành'
                          AND DATE(ngayTao) = CURDATE()
                        GROUP BY maChuyenXe
                    ) newest ON bc.maChuyenXe = newest.maChuyenXe AND bc.maBaoCao = newest.maxBaoCao
                    INNER JOIN chuyenxe cx ON bc.maChuyenXe = cx.maChuyenXe
                    INNER JOIN lichtrinh lt ON cx.maLichTrinh = lt.maLichTrinh
                    INNER JOIN tuyenduong td ON lt.maTuyenDuong = td.maTuyenDuong
                    INNER JOIN nguoidung tx ON bc.maTaiXe = tx.maNguoiDung
                    ORDER BY cx.thoiGianKhoiHanh ASC";
            
            error_log("[v0] getTodayReports Query executed");
            $result = fetchAll($sql);
            error_log("[v0] getTodayReports Result count: " . count($result));
            
            foreach ($result as $index => $report) {
                error_log("[v0] Report $index - maBaoCao: {$report['maBaoCao']}, tongSoHanhKhach: {$report['tongSoHanhKhach']}, soHanhKhachCoMat: {$report['soHanhKhachCoMat']}, soHanhKhachVang: {$report['soHanhKhachVang']}");

            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Staff::getTodayReports error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get detailed report information
     */
    public static function getReportDetail($reportId) {
        try {
            $sql = "SELECT bc.maBaoCao, bc.maChuyenXe, bc.maTaiXe, bc.tongSoHanhKhach, 
                           bc.soHanhKhachCoMat, bc.soHanhKhachVang, bc.ghiChu, bc.trangThai,
                           bc.ngayTao, bc.ngayCapNhat,
                           cx.ngayKhoiHanh, cx.thoiGianKhoiHanh, cx.thoiGianKetThuc,
                           cx.soChoTong, cx.soChoDaDat,
                           td.kyHieuTuyen, td.diemDi, td.diemDen, td.thoiGianDiChuyen,
                           tx.tenNguoiDung as tenTaiXe, tx.soDienThoai as sdtTaiXe, tx.eMail as emailTaiXe,
                           pt.bienSo, pt.maPhuongTien,
                           lpt.tenLoaiPhuongTien, lpt.soTang, lpt.soHang
                    FROM baocao_chuyendi bc
                    INNER JOIN chuyenxe cx ON bc.maChuyenXe = cx.maChuyenXe
                    INNER JOIN lichtrinh lt ON cx.maLichTrinh = lt.maLichTrinh
                    INNER JOIN tuyenduong td ON lt.maTuyenDuong = td.maTuyenDuong
                    INNER JOIN nguoidung tx ON bc.maTaiXe = tx.maNguoiDung
                    INNER JOIN phuongtien pt ON cx.maPhuongTien = pt.maPhuongTien
                    INNER JOIN loaiphuongtien lpt ON pt.maLoaiPhuongTien = lpt.maLoaiPhuongTien
                    WHERE bc.maBaoCao = ?";
            
            error_log("[v0] getReportDetail Query executed with reportId: " . $reportId);
            $result = fetch($sql, [$reportId]);
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Staff::getReportDetail error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get passenger list for a report (both present and absent)
     * Fetches from baocao_hanhkhach table with proper joins to chitiet_datve
     */
    public static function getReportPassengers($reportId) {
        try {
            $sql = "SELECT bh.maBaoCaoHK, bh.maChiTiet, bh.trangThai, bh.thoiGianLenXe, bh.ghiChu,
                           cd.hoTenHanhKhach, cd.soDienThoaiHanhKhach, cd.emailHanhKhach,
                           g.soGhe, cd.giaVe,
                           dd.tenDiem as diemDonTen, dd.diaChi as diemDonDiaChi,
                           dt.tenDiem as diemTraTen, dt.diaChi as diemTraDiaChi
                    FROM baocao_hanhkhach bh
                    INNER JOIN chitiet_datve cd ON bh.maChiTiet = cd.maChiTiet
                    INNER JOIN ghe g ON cd.maGhe = g.maGhe
                    LEFT JOIN tuyenduong_diemdontra dd ON cd.maDiemDon = dd.maDiem
                    LEFT JOIN tuyenduong_diemdontra dt ON cd.maDiemTra = dt.maDiem
                    WHERE bh.maBaoCao = ?
                    ORDER BY bh.trangThai DESC, g.soGhe ASC";
            
            error_log("[v0] getReportPassengers Query executed with reportId: " . $reportId);
            $result = fetchAll($sql, [$reportId]);
            error_log("[v0] getReportPassengers Result count: " . count($result));
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Staff::getReportPassengers error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Confirm trip departure - update report status and trip status
     */
    public static function confirmDeparture($reportId) {
        try {
            query("START TRANSACTION");
            
            // Get report details
            $reportSql = "SELECT maBaoCao, maChuyenXe FROM baocao_chuyendi WHERE maBaoCao = ?";
            $report = fetch($reportSql, [$reportId]);
            
            if (!$report) {
                throw new Exception("Báo cáo không tồn tại");
            }
            
            // Update report status to "Đang di chuyển"
            $updateReportSql = "UPDATE baocao_chuyendi 
                               SET trangThai = 'Đang di chuyển', 
                                   ngayCapNhat = NOW()
                               WHERE maBaoCao = ?";
            query($updateReportSql, [$reportId]);
            
            // Update trip status to "Khởi hành"
            $updateTripSql = "UPDATE chuyenxe 
                             SET trangThai = 'Khởi hành', 
                                 ngayCapNhat = NOW()
                             WHERE maChuyenXe = ?";
            query($updateTripSql, [$report['maChuyenXe']]);
            
            query("COMMIT");
            return ['success' => true, 'message' => 'Đã xác nhận khởi hành thành công'];
            
        } catch (Exception $e) {
            query("ROLLBACK");
            error_log("Staff::confirmDeparture error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get reports by date range
     */
    public static function getReportsByDateRange($startDate, $endDate) {
        try {
            $sql = "SELECT bc.maBaoCao, bc.maChuyenXe, bc.maTaiXe, bc.tongSoHanhKhach, 
                           bc.soHanhKhachCoMat, bc.soHanhKhachVang, bc.trangThai,
                           bc.ngayTao, bc.ngayCapNhat,
                           cx.ngayKhoiHanh, cx.thoiGianKhoiHanh,
                           td.kyHieuTuyen, td.diemDi, td.diemDen,
                           tx.tenNguoiDung as tenTaiXe
                    FROM baocao_chuyendi bc
                    INNER JOIN chuyenxe cx ON bc.maChuyenXe = cx.maChuyenXe
                    INNER JOIN lichtrinh lt ON cx.maLichTrinh = lt.maLichTrinh
                    INNER JOIN tuyenduong td ON lt.maTuyenDuong = td.maTuyenDuong
                    INNER JOIN nguoidung tx ON bc.maTaiXe = tx.maNguoiDung
                    WHERE DATE(bc.ngayTao) BETWEEN ? AND ?
                    AND bc.trangThai IN ('Chờ khởi hành', 'Đang di chuyển')
                    ORDER BY cx.thoiGianKhoiHanh DESC";
            
            return fetchAll($sql, [$startDate, $endDate]);
            
        } catch (Exception $e) {
            error_log("Staff::getReportsByDateRange error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all group rental requests with status "Chờ duyệt"
     */
    public static function getPendingRentalRequests() {
        try {
            $sql = "SELECT tr.maThuXe, tr.hoTenNguoiThue, 
                           tr.soDienThoaiNguoiThue, tr.emailNguoiThue,
                           tr.diemDi, tr.diemDen, tr.ngayDi, tr.gioDi,
                           tr.diemDonDi, tr.ngayVe, tr.gioVe, tr.diemDonVe,
                           tr.soLuongNguoi, tr.maLoaiPhuongTien, tr.ghiChu, tr.trangThai,
                           tr.ngayTao, tr.ngayCapNhat,
                           lpt.tenLoaiPhuongTien
                    FROM thuexe tr
                    LEFT JOIN loaiphuongtien lpt ON tr.maLoaiPhuongTien = lpt.maLoaiPhuongTien
                    WHERE tr.trangThai = 'Chờ duyệt'
                    ORDER BY tr.ngayTao DESC";
            
            error_log("[v0] getPendingRentalRequests Query executed");
            $result = fetchAll($sql);
            error_log("[v0] getPendingRentalRequests Result count: " . count($result));
            
            return $result;
        } catch (Exception $e) {
            error_log("Staff::getPendingRentalRequests error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get rental request detail
     */
    public static function getRentalRequestDetail($requestId) {
        try {
            $sql = "SELECT tr.maThuXe, tr.hoTenNguoiThue, 
                           tr.soDienThoaiNguoiThue, tr.emailNguoiThue,
                           tr.diemDi, tr.diemDen, tr.ngayDi, tr.gioDi,
                           tr.diemDonDi, tr.ngayVe, tr.gioVe, tr.diemDonVe,
                           tr.soLuongNguoi, tr.maLoaiPhuongTien, tr.ghiChu, tr.trangThai,
                           tr.ngayTao, tr.ngayCapNhat, tr.loaiHanhTrinh,
                           lpt.tenLoaiPhuongTien, lpt.soTang, lpt.soHang, lpt.loaiChoNgoiMacDinh
                    FROM thuexe tr
                    LEFT JOIN loaiphuongtien lpt ON tr.maLoaiPhuongTien = lpt.maLoaiPhuongTien
                    WHERE tr.maThuXe = ?";
            
            error_log("[v0] getRentalRequestDetail Query executed with requestId: " . $requestId);
            $result = fetch($sql, [$requestId]);
            error_log("[v0] getRentalRequestDetail Result: " . json_encode($result));
            
            return $result;
        } catch (Exception $e) {
            error_log("Staff::getRentalRequestDetail error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update rental request status
     */
    public static function updateRentalRequestStatus($requestId, $status) {
        try {
            error_log("[v0] updateRentalRequestStatus - Starting update for requestId: $requestId, status: $status");
            
            $sql = "UPDATE thuexe 
                    SET trangThai = ?, ngayCapNhat = NOW()
                    WHERE maThuXe = ?";
            
            error_log("[v0] updateRentalRequestStatus - SQL: $sql");
            error_log("[v0] updateRentalRequestStatus - Parameters: status=$status, requestId=$requestId");
            
            $result = query($sql, [$status, $requestId]);
            
            error_log("[v0] updateRentalRequestStatus - Query executed successfully");
            error_log("[v0] updateRentalRequestStatus - Result: " . json_encode($result));
            
            return ['success' => true, 'message' => 'Cập nhật trạng thái thành công'];
        } catch (Exception $e) {
            error_log("Staff::updateRentalRequestStatus error: " . $e->getMessage());
            error_log("Staff::updateRentalRequestStatus stack trace: " . $e->getTraceAsString());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get today's trip statistics
     * Returns: total scheduled trips, departed trips, and pending reports
     */
    public static function getTodayTripStats() {
        try {
            $sql = "SELECT 
                        COUNT(DISTINCT cx.maChuyenXe) as totalTripsToday,
                        SUM(CASE WHEN cx.trangThai = 'Khởi hành' THEN 1 ELSE 0 END) as departedTrips,
                        SUM(CASE WHEN cx.trangThai = 'Hoàn thành' THEN 1 ELSE 0 END) as completedTrips
                    FROM chuyenxe cx
                    WHERE DATE(cx.ngayKhoiHanh) = CURDATE()";
            
            error_log("[v0] getTodayTripStats Query executed");
            $result = fetch($sql);
            error_log("[v0] getTodayTripStats Result: " . json_encode($result));
            
            return $result ?: ['totalTripsToday' => 0, 'departedTrips' => 0, 'completedTrips' => 0];
            
        } catch (Exception $e) {
            error_log("Staff::getTodayTripStats error: " . $e->getMessage());
            return ['totalTripsToday' => 0, 'departedTrips' => 0, 'completedTrips' => 0];
        }
    }
    
    /**
     * Get approved departing trips for today
     */
    public static function getApprovedDepartingTrips() {
        try {
            $sql = "SELECT bc.maBaoCao, bc.maChuyenXe, bc.maTaiXe, bc.tongSoHanhKhach, 
                           bc.soHanhKhachCoMat, bc.soHanhKhachVang, bc.trangThai,
                           bc.ngayTao, bc.ngayCapNhat,
                           cx.ngayKhoiHanh, cx.thoiGianKhoiHanh, cx.thoiGianKetThuc,
                           td.kyHieuTuyen, td.diemDi, td.diemDen,
                           tx.tenNguoiDung as tenTaiXe, tx.soDienThoai as sdtTaiXe
                    FROM baocao_chuyendi bc
                    INNER JOIN chuyenxe cx ON bc.maChuyenXe = cx.maChuyenXe
                    INNER JOIN lichtrinh lt ON cx.maLichTrinh = lt.maLichTrinh
                    INNER JOIN tuyenduong td ON lt.maTuyenDuong = td.maTuyenDuong
                    INNER JOIN nguoidung tx ON bc.maTaiXe = tx.maNguoiDung
                    WHERE DATE(cx.ngayKhoiHanh) = CURDATE()
                    AND bc.trangThai IN ('Đang di chuyển', 'Hoàn thành')
                    AND bc.xacNhanKhoiHanh = 1
                    ORDER BY cx.thoiGianKhoiHanh DESC";
            
            error_log("[v0] getApprovedDepartingTrips Query executed");
            $result = fetchAll($sql);
            error_log("[v0] getApprovedDepartingTrips Result count: " . count($result));
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Staff::getApprovedDepartingTrips error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get completed trips for today
     */
    public static function getCompletedTrips() {
        try {
            $sql = "SELECT bc.maBaoCao, bc.maChuyenXe, bc.maTaiXe, bc.tongSoHanhKhach, 
                           bc.soHanhKhachCoMat, bc.soHanhKhachVang, bc.trangThai,
                           bc.ngayTao, bc.ngayCapNhat,
                           cx.ngayKhoiHanh, cx.thoiGianKhoiHanh, cx.thoiGianKetThuc,
                           td.kyHieuTuyen, td.diemDi, td.diemDen,
                           tx.tenNguoiDung as tenTaiXe, tx.soDienThoai as sdtTaiXe
                    FROM baocao_chuyendi bc
                    INNER JOIN chuyenxe cx ON bc.maChuyenXe = cx.maChuyenXe
                    INNER JOIN lichtrinh lt ON cx.maLichTrinh = lt.maLichTrinh
                    INNER JOIN tuyenduong td ON lt.maTuyenDuong = td.maTuyenDuong
                    INNER JOIN nguoidung tx ON bc.maTaiXe = tx.maNguoiDung
                    WHERE DATE(cx.ngayKhoiHanh) = CURDATE()
                    AND cx.trangThai = 'Hoàn thành'
                    ORDER BY cx.thoiGianKetThuc DESC";
            
            error_log("[v0] getCompletedTrips Query executed");
            $result = fetchAll($sql);
            error_log("[v0] getCompletedTrips Result count: " . count($result));
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Staff::getCompletedTrips error: " . $e->getMessage());
            return [];
        }
    }
}
