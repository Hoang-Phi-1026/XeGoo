<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class StatisticsController {
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Kiểm tra admin
    private function checkAdminAccess() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
            $_SESSION['error'] = 'Bạn không có quyền truy cập chức năng này!';
            header('Location: ' . BASE_URL . '/login');
            exit();
        }
    }

    // Trang thống kê chính
    public function index() {
        $this->checkAdminAccess();
        
        $stats = [
            // Phần I: Thống kê doanh thu
            'totalRevenue' => $this->getTotalRevenue(),
            'monthlyRevenue' => $this->getMonthlyRevenue(),
            'revenueByRoute' => $this->getRevenueByRoute(),
            'revenueByPaymentMethod' => $this->getRevenueByPaymentMethod(),
            'topRoutes' => $this->getTopRoutes(),
            
            // Phần II: Thống kê hành khách
            'userStats' => $this->getUserStats(),
            'newUsersByMonth' => $this->getNewUsersByMonth(),
            'repeatCustomers' => $this->getRepeatCustomers(),
            'topCustomers' => $this->getTopCustomers(),
            
            // Phần III: Thống kê chuyến xe
            'tripStats' => $this->getTripStats(),
            'tripStatusDetailed' => $this->getTripStatusDetailed(),
            'tripLoadFactor' => $this->getTripLoadFactor(),
            'averageTicketPerTrip' => $this->getAverageTicketPerTrip(),
            'topRoutesByBooking' => $this->getTopRoutesByBooking(),
            'tripPerformance' => $this->getTripPerformance(),
            
            // Thông tin phụ
            'bookings' => $this->getBookingStats(),
            'bookingStatus' => $this->getBookingStatus(),
            'driverStats' => $this->getDriverStats(),
            'vehicleStats' => $this->getVehicleStats(),
            'routeStats' => $this->getRouteStats(),
        ];
        
        require_once __DIR__ . '/../views/admin/statistics.php';
    }

    // Tính tổng doanh thu
    private function getTotalRevenue() {
        try {
            $result = fetch(
                "SELECT SUM(tongTienSauGiam) as total FROM datve WHERE trangThai IN ('DaThanhToan', 'DaHoanThanh')"
            );
            return $result ? (float)$result['total'] : 0;
        } catch (Exception $e) {
            error_log("Error getting revenue: " . $e->getMessage());
            return 0;
        }
    }

    // Thống kê booking
    private function getBookingStats() {
        try {
            $total = fetch("SELECT COUNT(*) as count FROM datve");
            $completed = fetch("SELECT COUNT(*) as count FROM datve WHERE trangThai = 'DaThanhToan'");
            $cancelled = fetch("SELECT COUNT(*) as count FROM datve WHERE trangThai = 'DaHuy'");
            $pending = fetch("SELECT COUNT(*) as count FROM datve WHERE trangThai = 'DangGiu'");
            
            return [
                'total' => $total ? $total['count'] : 0,
                'completed' => $completed ? $completed['count'] : 0,
                'cancelled' => $cancelled ? $cancelled['count'] : 0,
                'pending' => $pending ? $pending['count'] : 0,
            ];
        } catch (Exception $e) {
            error_log("Error getting booking stats: " . $e->getMessage());
            return ['total' => 0, 'completed' => 0, 'cancelled' => 0, 'pending' => 0];
        }
    }

    // Thống kê người dùng
    private function getUserStats() {
        try {
            $total = fetch("SELECT COUNT(*) as count FROM nguoidung");
            $customers = fetch("SELECT COUNT(*) as count FROM nguoidung WHERE maVaiTro = 4");
            $drivers = fetch("SELECT COUNT(*) as count FROM nguoidung WHERE maVaiTro = 3");
            $staff = fetch("SELECT COUNT(*) as count FROM nguoidung WHERE maVaiTro = 2");
            
            return [
                'total' => $total ? $total['count'] : 0,
                'active' => $total ? $total['count'] : 0,
                'customers' => $customers ? $customers['count'] : 0,
                'drivers' => $drivers ? $drivers['count'] : 0,
                'staff' => $staff ? $staff['count'] : 0,
            ];
        } catch (Exception $e) {
            error_log("Error getting user stats: " . $e->getMessage());
            return ['total' => 0, 'active' => 0, 'customers' => 0, 'drivers' => 0, 'staff' => 0];
        }
    }

    // Thống kê tài xế
    private function getDriverStats() {
        try {
            $total = fetch("SELECT COUNT(*) as count FROM nguoidung WHERE maVaiTro = 3");
            $active = fetch("SELECT COUNT(*) as count FROM nguoidung WHERE maVaiTro = 3 AND (maTrangThai = 0 OR maTrangThai IS NULL)");
            $tripsCompleted = fetch("SELECT COUNT(DISTINCT maTaiXe) as count FROM chuyenxe WHERE trangThai = 'Hoàn thành'");
            
            return [
                'total' => $total ? $total['count'] : 0,
                'active' => $active ? $active['count'] : 0,
                'tripsCompleted' => $tripsCompleted ? $tripsCompleted['count'] : 0,
            ];
        } catch (Exception $e) {
            error_log("Error getting driver stats: " . $e->getMessage());
            return ['total' => 0, 'active' => 0, 'tripsCompleted' => 0];
        }
    }

    // Thống kê phương tiện
    private function getVehicleStats() {
        try {
            $total = fetch("SELECT COUNT(*) as count FROM phuongtien");
            $active = fetch("SELECT COUNT(*) as count FROM phuongtien WHERE trangThai = 'Đang hoạt động'");
            $maintenance = fetch("SELECT COUNT(*) as count FROM phuongtien WHERE trangThai = 'Bảo dưỡng'");
            
            return [
                'total' => $total ? $total['count'] : 0,
                'active' => $active ? $active['count'] : 0,
                'maintenance' => $maintenance ? $maintenance['count'] : 0,
            ];
        } catch (Exception $e) {
            error_log("Error getting vehicle stats: " . $e->getMessage());
            return ['total' => 0, 'active' => 0, 'maintenance' => 0];
        }
    }

    // Thống kê chuyến
    private function getTripStats() {
        try {
            $total = fetch("SELECT COUNT(*) as count FROM chuyenxe");
            $ready = fetch("SELECT COUNT(*) as count FROM chuyenxe WHERE trangThai = 'Sẵn sàng'");
            $completed = fetch("SELECT COUNT(*) as count FROM chuyenxe WHERE trangThai = 'Hoàn thành'");
            $cancelled = fetch("SELECT COUNT(*) as count FROM chuyenxe WHERE trangThai = 'Bị hủy'");
            $delayed = fetch("SELECT COUNT(*) as count FROM chuyenxe WHERE trangThai = 'Delay'");
            
            return [
                'total' => $total ? $total['count'] : 0,
                'ready' => $ready ? $ready['count'] : 0,
                'completed' => $completed ? $completed['count'] : 0,
                'cancelled' => $cancelled ? $cancelled['count'] : 0,
                'delayed' => $delayed ? $delayed['count'] : 0,
            ];
        } catch (Exception $e) {
            error_log("Error getting trip stats: " . $e->getMessage());
            return ['total' => 0, 'ready' => 0, 'completed' => 0, 'cancelled' => 0, 'delayed' => 0];
        }
    }

    // Thống kê tuyến đường
    private function getRouteStats() {
        try {
            $total = fetch("SELECT COUNT(*) as count FROM tuyenduong");
            $active = fetch("SELECT COUNT(*) as count FROM tuyenduong WHERE trangThai = 'Đang hoạt động'");
            
            return [
                'total' => $total ? $total['count'] : 0,
                'active' => $active ? $active['count'] : 0,
            ];
        } catch (Exception $e) {
            error_log("Error getting route stats: " . $e->getMessage());
            return ['total' => 0, 'active' => 0];
        }
    }

    // Doanh thu theo tháng
    private function getMonthlyRevenue() {
        try {
            $results = fetchAll(
                "SELECT 
                    DATE_FORMAT(ngayCapNhat, '%Y-%m') as month,
                    SUM(tongTienSauGiam) as revenue,
                    COUNT(*) as count
                FROM datve 
                WHERE trangThai IN ('DaThanhToan', 'DaHoanThanh')
                    AND ngayCapNhat >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(ngayCapNhat, '%Y-%m')
                ORDER BY month ASC"
            );
            
            return $results ? $results : [];
        } catch (Exception $e) {
            error_log("Error getting monthly revenue: " . $e->getMessage());
            return [];
        }
    }

    // Doanh thu theo tuyến xe
    private function getRevenueByRoute() {
        try {
            $results = fetchAll(
                "SELECT 
                    td.maTuyenDuong,
                    td.kyHieuTuyen,
                    td.diemDi,
                    td.diemDen,
                    COUNT(cdv.maChiTiet) as veban,
                    SUM(cdv.giaVe) as doanhThu,
                    AVG(cdv.giaVe) as giaTriTrungBinh,
                    ROUND(SUM(cdv.giaVe) * 0.9, 0) as loiNhuan
                FROM tuyenduong td
                LEFT JOIN lichtrinh lt ON td.maTuyenDuong = lt.maTuyenDuong
                LEFT JOIN chuyenxe cx ON lt.maLichTrinh = cx.maLichTrinh
                LEFT JOIN chitiet_datve cdv ON cx.maChuyenXe = cdv.maChuyenXe
                LEFT JOIN datve dv ON cdv.maDatVe = dv.maDatVe
                WHERE cdv.trangThai = 'DaThanhToan'
                GROUP BY td.maTuyenDuong
                ORDER BY doanhThu DESC
                LIMIT 20"
            );
            return $results ? $results : [];
        } catch (Exception $e) {
            error_log("Error getting revenue by route: " . $e->getMessage());
            return [];
        }
    }

    // Doanh thu theo hình thức thanh toán
    private function getRevenueByPaymentMethod() {
        try {
            $results = fetchAll(
                "SELECT 
                    phuongThucThanhToan as paymentMethod,
                    COUNT(maDatVe) as bookingCount,
                    SUM(tongTienSauGiam) as totalAmount,
                    AVG(tongTienSauGiam) as avgAmount
                FROM datve
                WHERE trangThai IN ('DaThanhToan', 'DaHoanThanh')
                GROUP BY phuongThucThanhToan
                ORDER BY totalAmount DESC"
            );
            return $results ? $results : [];
        } catch (Exception $e) {
            error_log("Error getting revenue by payment method: " . $e->getMessage());
            return [];
        }
    }

    // Người dùng mới theo tháng
    private function getNewUsersByMonth() {
        try {
            $results = fetchAll(
                "SELECT 
                    DATE_FORMAT(ngayTao, '%Y-%m') as month,
                    DATE_FORMAT(ngayTao, '%m/%Y') as monthDisplay,
                    COUNT(*) as count,
                    SUM(CASE WHEN maVaiTro = 4 THEN 1 ELSE 0 END) as customers,
                    SUM(CASE WHEN maVaiTro = 3 THEN 1 ELSE 0 END) as drivers
                FROM nguoidung
                WHERE maVaiTro IN (3, 4)
                GROUP BY DATE_FORMAT(ngayTao, '%Y-%m')
                ORDER BY month DESC
                LIMIT 12"
            );
            return $results ? array_reverse($results) : [];
        } catch (Exception $e) {
            error_log("Error getting new users by month: " . $e->getMessage());
            return [];
        }
    }

    // Khách hàng lặp lại
    private function getRepeatCustomers() {
        try {
            $results = fetchAll(
                "SELECT 
                    nd.maNguoiDung,
                    nd.tenNguoiDung as hoTen,
                    nd.soDienThoai,
                    COUNT(dv.maDatVe) as soLanDat,
                    SUM(dv.tongTienSauGiam) as tongChiTieu,
                    MAX(dv.ngayCapNhat) as lanDatCuoi,
                    AVG(dv.tongTienSauGiam) as chiTieuTrungBinh
                FROM nguoidung nd
                LEFT JOIN datve dv ON nd.maNguoiDung = dv.maNguoiDung AND dv.trangThai IN ('DaThanhToan', 'DaHoanThanh')
                WHERE nd.maVaiTro = 4
                GROUP BY nd.maNguoiDung
                HAVING COUNT(dv.maDatVe) > 1
                ORDER BY soLanDat DESC
                LIMIT 20"
            );
            return $results ? $results : [];
        } catch (Exception $e) {
            error_log("Error getting repeat customers: " . $e->getMessage());
            return [];
        }
    }

    // Top khách hàng mua nhiều vé
    private function getTopCustomers() {
        try {
            $results = fetchAll(
                "SELECT 
                    nd.maNguoiDung,
                    nd.tenNguoiDung as hoTen,
                    nd.soDienThoai,
                    COUNT(dv.maDatVe) as soVe,
                    SUM(dv.tongTienSauGiam) as tongTien,
                    MAX(dv.ngayCapNhat) as lanDatCuoi
                FROM nguoidung nd
                LEFT JOIN datve dv ON nd.maNguoiDung = dv.maNguoiDung AND dv.trangThai IN ('DaThanhToan', 'DaHoanThanh')
                WHERE nd.maVaiTro = 4
                GROUP BY nd.maNguoiDung
                ORDER BY tongTien DESC
                LIMIT 10"
            );
            return $results ? $results : [];
        } catch (Exception $e) {
            error_log("Error getting top customers: " . $e->getMessage());
            return [];
        }
    }

    // Chuyến xe theo trạng thái chi tiết
    private function getTripStatusDetailed() {
        try {
            $results = fetchAll(
                "SELECT 
                    trangThai as status,
                    COUNT(*) as count
                FROM chuyenxe
                GROUP BY trangThai"
            );
            return $results ? $results : [];
        } catch (Exception $e) {
            error_log("Error getting trip status detailed: " . $e->getMessage());
            return [];
        }
    }

    // Tỷ lệ lấp đầy chuyến
    private function getTripLoadFactor() {
        try {
            // Get pagination parameters
            $page = isset($_GET['trip_load_page']) ? (int)$_GET['trip_load_page'] : 1;
            $perPage = 15;
            $offset = ($page - 1) * $perPage;
            
            // Get total count
            $countResult = fetch(
                "SELECT COUNT(*) as total FROM chuyenxe cx
                LEFT JOIN lichtrinh lt ON cx.maLichTrinh = lt.maLichTrinh
                LEFT JOIN tuyenduong td ON lt.maTuyenDuong = td.maTuyenDuong"
            );
            $totalRows = $countResult ? $countResult['total'] : 0;
            $totalPages = ceil($totalRows / $perPage);
            
            // Fetch paginated data with departure date
            $results = fetchAll(
                "SELECT 
                    cx.maChuyenXe,
                    td.kyHieuTuyen,
                    cx.ngayKhoiHanh,
                    cx.thoiGianKhoiHanh as gioKhoiHanh,
                    cx.soChoTong,
                    cx.soChoDaDat as soChoCoNguoi,
                    ROUND((cx.soChoDaDat / cx.soChoTong) * 100, 2) as tyLeLapDay
                FROM chuyenxe cx
                LEFT JOIN lichtrinh lt ON cx.maLichTrinh = lt.maLichTrinh
                LEFT JOIN tuyenduong td ON lt.maTuyenDuong = td.maTuyenDuong
                ORDER BY cx.ngayKhoiHanh DESC, cx.thoiGianKhoiHanh DESC
                LIMIT " . $offset . ", " . $perPage
            );
            
            return [
                'data' => $results ? $results : [],
                'pagination' => [
                    'currentPage' => $page,
                    'totalPages' => $totalPages,
                    'total' => $totalRows,
                    'perPage' => $perPage
                ]
            ];
        } catch (Exception $e) {
            error_log("Error getting trip load factor: " . $e->getMessage());
            return ['data' => [], 'pagination' => ['currentPage' => 1, 'totalPages' => 1, 'total' => 0, 'perPage' => 15]];
        }
    }

    // Trung bình vé bán mỗi chuyến
    private function getAverageTicketPerTrip() {
        try {
            $result = fetch(
                "SELECT 
                    COUNT(DISTINCT cx.maChuyenXe) as totalTrips,
                    COUNT(DISTINCT cdv.maDatVe) as totalTickets,
                    ROUND(COUNT(DISTINCT cdv.maDatVe) / COUNT(DISTINCT cx.maChuyenXe), 2) as avgTickets,
                    ROUND(SUM(cdv.giaVe) / COUNT(DISTINCT cx.maChuyenXe), 0) as avgRevenue
                FROM chuyenxe cx
                LEFT JOIN chitiet_datve cdv ON cx.maChuyenXe = cdv.maChuyenXe
                WHERE cdv.trangThai = 'DaThanhToan'"
            );
            return $result ? $result : ['totalTrips' => 0, 'totalTickets' => 0, 'avgTickets' => 0, 'avgRevenue' => 0];
        } catch (Exception $e) {
            error_log("Error getting average ticket per trip: " . $e->getMessage());
            return [];
        }
    }

    // Tuyến xe được đặt nhiều nhất
    private function getTopRoutesByBooking() {
        try {
            $results = fetchAll(
                "SELECT 
                    td.maTuyenDuong,
                    td.kyHieuTuyen,
                    td.diemDi,
                    td.diemDen,
                    COUNT(DISTINCT cx.maChuyenXe) as totalTrips,
                    COUNT(DISTINCT cdv.maDatVe) as totalBookings,
                    SUM(cdv.giaVe) as totalRevenue
                FROM tuyenduong td
                LEFT JOIN lichtrinh lt ON td.maTuyenDuong = lt.maTuyenDuong
                LEFT JOIN chuyenxe cx ON lt.maLichTrinh = cx.maLichTrinh
                LEFT JOIN chitiet_datve cdv ON cx.maChuyenXe = cdv.maChuyenXe
                WHERE cdv.trangThai = 'DaThanhToan'
                GROUP BY td.maTuyenDuong
                ORDER BY totalBookings DESC
                LIMIT 10"
            );
            return $results ? $results : [];
        } catch (Exception $e) {
            error_log("Error getting top routes by booking: " . $e->getMessage());
            return [];
        }
    }

    // Chuyến chạy bình thường vs bất thường
    private function getTripPerformance() {
        try {
            $results = fetchAll(
                "SELECT 
                    trangThai as status,
                    COUNT(*) as count,
                    ROUND((COUNT(*) / (SELECT COUNT(*) FROM chuyenxe)) * 100, 2) as percentage
                FROM chuyenxe
                GROUP BY trangThai"
            );
            return $results ? $results : [];
        } catch (Exception $e) {
            error_log("Error getting trip performance: " . $e->getMessage());
            return [];
        }
    }

    // Top tuyến đường
    private function getTopRoutes() {
        try {
            $results = fetchAll(
                "SELECT 
                    td.kyHieuTuyen,
                    td.diemDi,
                    td.diemDen,
                    COUNT(cx.maChuyenXe) as tripCount,
                    SUM(cdv.giaVe) as totalRevenue
                FROM tuyenduong td
                LEFT JOIN lichtrinh lt ON td.maTuyenDuong = lt.maTuyenDuong
                LEFT JOIN chuyenxe cx ON lt.maLichTrinh = cx.maLichTrinh
                LEFT JOIN chitiet_datve cdv ON cx.maChuyenXe = cdv.maChuyenXe
                WHERE cdv.trangThai = 'DaThanhToan'
                GROUP BY td.maTuyenDuong
                ORDER BY totalRevenue DESC
                LIMIT 10"
            );
            
            return $results ? $results : [];
        } catch (Exception $e) {
            error_log("Error getting top routes: " . $e->getMessage());
            return [];
        }
    }

    // Trạng thái booking
    private function getBookingStatus() {
        try {
            $results = fetchAll(
                "SELECT 
                    trangThai as status,
                    COUNT(*) as count
                FROM datve
                GROUP BY trangThai"
            );
            
            return $results ? $results : [];
        } catch (Exception $e) {
            error_log("Error getting booking status: " . $e->getMessage());
            return [];
        }
    }

    // API endpoint for fetching trip load factor data via AJAX
    public function getTripLoadFactorAjax() {
        $this->checkAdminAccess();
        header('Content-Type: application/json');
        
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = 15;
            $offset = ($page - 1) * $perPage;
            
            // Get total count
            $countResult = fetch(
                "SELECT COUNT(*) as total FROM chuyenxe cx
                LEFT JOIN lichtrinh lt ON cx.maLichTrinh = lt.maLichTrinh
                LEFT JOIN tuyenduong td ON lt.maTuyenDuong = td.maTuyenDuong"
            );
            $totalRows = $countResult ? $countResult['total'] : 0;
            $totalPages = ceil($totalRows / $perPage);
            
            // Fetch paginated data
            $results = fetchAll(
                "SELECT 
                    cx.maChuyenXe,
                    td.kyHieuTuyen,
                    cx.ngayKhoiHanh,
                    cx.thoiGianKhoiHanh as gioKhoiHanh,
                    cx.soChoTong,
                    cx.soChoDaDat as soChoCoNguoi,
                    ROUND((cx.soChoDaDat / cx.soChoTong) * 100, 2) as tyLeLapDay
                FROM chuyenxe cx
                LEFT JOIN lichtrinh lt ON cx.maLichTrinh = lt.maLichTrinh
                LEFT JOIN tuyenduong td ON lt.maTuyenDuong = td.maTuyenDuong
                ORDER BY cx.ngayKhoiHanh DESC, cx.thoiGianKhoiHanh DESC
                LIMIT " . $offset . ", " . $perPage
            );
            
            echo json_encode([
                'success' => true,
                'data' => $results ? $results : [],
                'pagination' => [
                    'currentPage' => $page,
                    'totalPages' => $totalPages,
                    'total' => $totalRows,
                    'perPage' => $perPage
                ]
            ]);
        } catch (Exception $e) {
            error_log("Error in getTripLoadFactorAjax: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi khi tải dữ liệu'
            ]);
        }
        exit();
    }
}
?>
