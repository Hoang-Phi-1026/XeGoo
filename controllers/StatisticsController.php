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
            
            // Bán vé trong hôm nay
            'todayTicketSales' => $this->getTodayTicketSales(),
        ];
        
        require_once __DIR__ . '/../views/admin/statistics.php';
    }

    // Tính tổng doanh thu
    private function getTotalRevenue() {
        try {
            $result = fetch(
                "SELECT SUM(tongTienSauGiam) as total FROM datve WHERE trangThai IN ('DangGiu', 'DaThanhToan', 'HetHieuLuc', 'DaHoanThanh', 'DaHuy')"
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
            // This prevents duplication from chitiet_datve having multiple rows per booking
            $results = fetchAll(
                "SELECT 
                    td.maTuyenDuong,
                    td.kyHieuTuyen,
                    td.diemDi,
                    td.diemDen,
                    COUNT(DISTINCT bookings.maDatVe) as soGiaoDich,
                    SUM(bookings.soLuongVe) as soLuongVe,
                    SUM(bookings.tongTienSauGiam) as doanhThu,
                    ROUND(AVG(bookings.tongTienSauGiam), 0) as giaTriTrungBinh,
                    SUM(bookings.tongTienSauGiam) - SUM(bookings.giamGia) as loiNhuan
                FROM (
                    SELECT DISTINCT
                        dv.maDatVe,
                        dv.soLuongVe,
                        dv.tongTienSauGiam,
                        dv.giamGia,
                        lt.maTuyenDuong
                    FROM datve dv
                    INNER JOIN chitiet_datve cdv ON dv.maDatVe = cdv.maDatVe
                    INNER JOIN chuyenxe cx ON cdv.maChuyenXe = cx.maChuyenXe
                    INNER JOIN lichtrinh lt ON cx.maLichTrinh = lt.maLichTrinh
                    WHERE dv.trangThai IN ('DangGiu', 'DaThanhToan', 'HetHieuLuc', 'DaHoanThanh', 'DaHuy')
                ) as bookings
                INNER JOIN tuyenduong td ON bookings.maTuyenDuong = td.maTuyenDuong
                GROUP BY td.maTuyenDuong, td.kyHieuTuyen, td.diemDi, td.diemDen
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
                WHERE trangThai IN ('DaThanhToan', 'HetHieuLuc', 'DaHoanThanh', 'DaHuy')
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
            $startDate = isset($_GET['trip_start_date']) ? $_GET['trip_start_date'] : date('Y-m-01');
            $endDate = isset($_GET['trip_end_date']) ? $_GET['trip_end_date'] : date('Y-m-d');
            $perPage = 15;
            $offset = ($page - 1) * $perPage;
            
            // Format dates for query
            $startDateStr = date('Y-m-d', strtotime($startDate));
            $endDateStr = date('Y-m-d', strtotime($endDate));
            
            // Get total count with date filter
            $countResult = fetch(
                "SELECT COUNT(*) as total FROM chuyenxe cx
                LEFT JOIN lichtrinh lt ON cx.maLichTrinh = lt.maLichTrinh
                LEFT JOIN tuyenduong td ON lt.maTuyenDuong = td.maTuyenDuong
                WHERE DATE(cx.ngayKhoiHanh) BETWEEN '" . $startDateStr . "' AND '" . $endDateStr . "'"
            );
            $totalRows = $countResult ? $countResult['total'] : 0;
            $totalPages = ceil($totalRows / $perPage);
            
            // Fetch paginated data with departure date filter
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
                WHERE DATE(cx.ngayKhoiHanh) BETWEEN '" . $startDateStr . "' AND '" . $endDateStr . "'
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
                ],
                'dateRange' => [
                    'startDate' => $startDateStr,
                    'endDate' => $endDateStr
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
        // API endpoint for fetching trip load factor data via AJAX (có lọc ngày)
    public function getTripLoadFactorAjax() {
        $this->checkAdminAccess();
        header('Content-Type: application/json');

        try {
            $page      = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $startDate = $_GET['startDate'] ?? date('Y-m-01');
            $endDate   = $_GET['endDate']   ?? date('Y-m-d');
            $perPage   = 15;
            $offset    = ($page - 1) * $perPage;

            // Chuẩn hóa ngày (và escape nếu dùng mysqli, giả sử fetch escape rồi)
            $startDate = date('Y-m-d', strtotime($startDate));
            $endDate   = date('Y-m-d', strtotime($endDate));

            // Đếm tổng số chuyến trong khoảng ngày
            $countResult = fetch(
                "SELECT COUNT(*) AS total 
                FROM chuyenxe cx
                WHERE DATE(cx.ngayKhoiHanh) BETWEEN '" . $startDate . "' AND '" . $endDate . "'"
            );

            $totalRows  = $countResult['total'] ?? 0;
            $totalPages = ceil($totalRows / $perPage);

            // Lấy dữ liệu phân trang (sửa query dùng COUNT từ chitiet_datve)
            $results = fetchAll(
                "SELECT 
                    cx.maChuyenXe,
                    td.kyHieuTuyen,
                    cx.ngayKhoiHanh,
                    cx.thoiGianKhoiHanh AS gioKhoiHanh,
                    cx.soChoTong,
                    COUNT(cdv.maChiTiet) AS soChoCoNguoi,
                    ROUND((COUNT(cdv.maChiTiet) / NULLIF(cx.soChoTong, 0)) * 100, 2) AS tyLeLapDay
                FROM chuyenxe cx
                LEFT JOIN lichtrinh lt ON cx.maLichTrinh = lt.maLichTrinh
                LEFT JOIN tuyenduong td ON lt.maTuyenDuong = td.maTuyenDuong
                LEFT JOIN chitiet_datve cdv ON cx.maChuyenXe = cdv.maChuyenXe AND cdv.trangThai = 'DaThanhToan'
                WHERE DATE(cx.ngayKhoiHanh) BETWEEN '" . $startDate . "' AND '" . $endDate . "'
                GROUP BY cx.maChuyenXe
                ORDER BY cx.ngayKhoiHanh DESC, cx.thoiGianKhoiHanh DESC
                LIMIT " . $offset . ", " . $perPage
            );

            echo json_encode([
                'success' => true,
                'data'    => $results ?: [],
                'pagination' => [
                    'currentPage' => $page,
                    'totalPages'  => $totalPages,
                    'total'       => $totalRows,
                    'perPage'     => $perPage
                ],
                'dateRange' => [
                    'startDate' => $startDate,
                    'endDate'   => $endDate
                ]
            ]);

        } catch (Exception $e) {
            error_log("Trip Load Factor AJAX Error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi khi tải dữ liệu: ' . $e->getMessage()
            ]);
        }
        exit();
    }


    // Bán vé trong hôm nay - Updated method to accept date parameter instead of just today
    private function getTodayTicketSales($selectedDate = null) {
        try {
            // Use provided date or current date
            $dateFilter = $selectedDate ? date('Y-m-d', strtotime($selectedDate)) : date('Y-m-d');
            
            $results = fetchAll(
                "SELECT 
                    dv.maDatVe,
                    COALESCE(nd.maNguoiDung, cdv.maChiTiet) as userIdentifier,
                    COALESCE(nd.tenNguoiDung, cdv.hoTenHanhKhach) as hoTen,
                    COALESCE(nd.soDienThoai, cdv.soDienThoaiHanhKhach) as soDienThoai,
                    COALESCE(nd.eMail, cdv.emailHanhKhach) as eMail,
                    COALESCE(vt.tenVaiTro, 'Khách Hàng') as vaiTro,
                    td.kyHieuTuyen,
                    cx.ngayKhoiHanh,
                    cx.thoiGianKhoiHanh as gioKhoiHanh,
                    COUNT(DISTINCT cdv.maChiTiet) as soVe,
                    dv.tongTienSauGiam,
                    dv.loaiDatVe,
                    dv.phuongThucThanhToan,
                    dv.trangThai,
                    dv.ngayDat,
                    nd.maNguoiDung
                FROM datve dv
                LEFT JOIN nguoidung nd ON dv.maNguoiDung = nd.maNguoiDung
                LEFT JOIN vaitro vt ON nd.maVaiTro = vt.maVaiTro
                INNER JOIN chitiet_datve cdv ON dv.maDatVe = cdv.maDatVe
                INNER JOIN chuyenxe cx ON cdv.maChuyenXe = cx.maChuyenXe
                INNER JOIN lichtrinh lt ON cx.maLichTrinh = lt.maLichTrinh
                INNER JOIN tuyenduong td ON lt.maTuyenDuong = td.maTuyenDuong
                WHERE DATE(dv.ngayDat) = '" . $dateFilter . "'
                    AND dv.trangThai IN ('DaThanhToan', 'DaHoanThanh', 'DaHuy')
                GROUP BY dv.maDatVe, COALESCE(nd.maNguoiDung, cdv.maChiTiet)
                ORDER BY dv.ngayDat DESC"
            );
            return $results ? $results : [];
        } catch (Exception $e) {
            error_log("Error getting ticket sales by date: " . $e->getMessage());
            return [];
        }
    }

    // API endpoint for fetching today's ticket sales via AJAX
    public function getTodayTicketSalesAjax() {
        $this->checkAdminAccess();
        header('Content-Type: application/json');
        
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $selectedDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
            $perPage = 15;
            $offset = ($page - 1) * $perPage;
            
            // Validate and format date
            $dateFilter = date('Y-m-d', strtotime($selectedDate));
            
            // Get total count
            $countResult = fetch(
                "SELECT COUNT(DISTINCT dv.maDatVe) as total 
                FROM datve dv
                INNER JOIN chitiet_datve cdv ON dv.maDatVe = cdv.maDatVe
                WHERE DATE(dv.ngayDat) = '" . $dateFilter . "'
                    AND dv.trangThai IN ('DaThanhToan', 'DaHoanThanh', 'DaHuy')"
            );
            $totalRows = $countResult ? $countResult['total'] : 0;
            $totalPages = ceil($totalRows / $perPage);
            
            $results = fetchAll(
                "SELECT 
                    dv.maDatVe,
                    IF(nd.maNguoiDung IS NOT NULL, nd.tenNguoiDung, cdv.hoTenHanhKhach) as tenKhachHang,
                    IF(nd.maNguoiDung IS NOT NULL, nd.soDienThoai, cdv.soDienThoaiHanhKhach) as soDienThoai,
                    IF(nd.maNguoiDung IS NOT NULL, nd.eMail, cdv.emailHanhKhach) as email,
                    td.kyHieuTuyen,
                    cx.ngayKhoiHanh,
                    COUNT(DISTINCT cdv.maChiTiet) as soVe,
                    dv.loaiDatVe,
                    dv.tongTienSauGiam as tongTien,
                    dv.phuongThucThanhToan,
                    dv.trangThai
                FROM datve dv
                LEFT JOIN nguoidung nd ON dv.maNguoiDung = nd.maNguoiDung
                INNER JOIN chitiet_datve cdv ON dv.maDatVe = cdv.maDatVe
                INNER JOIN chuyenxe cx ON cdv.maChuyenXe = cx.maChuyenXe
                INNER JOIN lichtrinh lt ON cx.maLichTrinh = lt.maLichTrinh
                INNER JOIN tuyenduong td ON lt.maTuyenDuong = td.maTuyenDuong
                WHERE DATE(dv.ngayDat) = '" . $dateFilter . "'
                    AND dv.trangThai IN ('DaThanhToan', 'DaHoanThanh', 'DaHuy')
                GROUP BY dv.maDatVe
                ORDER BY dv.ngayDat DESC
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
                ],
                'selectedDate' => $dateFilter
            ]);
        } catch (Exception $e) {
            error_log("Error in getTodayTicketSalesAjax: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi khi tải dữ liệu'
            ]);
        }
        exit();
    }

    // API endpoint for fetching ticket sales by date via AJAX
    public function getTicketSalesByDateAjax() {
        $this->checkAdminAccess();
        header('Content-Type: application/json');
        
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $selectedDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
            $perPage = 15;
            $offset = ($page - 1) * $perPage;
            
            // Validate and format date
            $dateFilter = date('Y-m-d', strtotime($selectedDate));
            
            // Get total count
            $countResult = fetch(
                "SELECT COUNT(DISTINCT dv.maDatVe) as total 
                FROM datve dv
                INNER JOIN chitiet_datve cdv ON dv.maDatVe = cdv.maDatVe
                WHERE DATE(dv.ngayDat) = '" . $dateFilter . "'
                    AND dv.trangThai IN ('DaThanhToan', 'DaHoanThanh', 'DaHuy')"
            );
            $totalRows = $countResult ? $countResult['total'] : 0;
            $totalPages = ceil($totalRows / $perPage);
            
            $results = fetchAll(
                "SELECT 
                    dv.maDatVe,
                    IF(nd.maNguoiDung IS NOT NULL, nd.tenNguoiDung, cdv.hoTenHanhKhach) as tenKhachHang,
                    IF(nd.maNguoiDung IS NOT NULL, nd.soDienThoai, cdv.soDienThoaiHanhKhach) as soDienThoai,
                    IF(nd.maNguoiDung IS NOT NULL, nd.eMail, cdv.emailHanhKhach) as email,
                    td.kyHieuTuyen,
                    cx.ngayKhoiHanh,
                    COUNT(DISTINCT cdv.maChiTiet) as soVe,
                    dv.loaiDatVe,
                    dv.tongTienSauGiam as tongTien,
                    dv.phuongThucThanhToan,
                    dv.trangThai
                FROM datve dv
                LEFT JOIN nguoidung nd ON dv.maNguoiDung = nd.maNguoiDung
                INNER JOIN chitiet_datve cdv ON dv.maDatVe = cdv.maDatVe
                INNER JOIN chuyenxe cx ON cdv.maChuyenXe = cx.maChuyenXe
                INNER JOIN lichtrinh lt ON cx.maLichTrinh = lt.maLichTrinh
                INNER JOIN tuyenduong td ON lt.maTuyenDuong = td.maTuyenDuong
                WHERE DATE(dv.ngayDat) = '" . $dateFilter . "'
                    AND dv.trangThai IN ('DaThanhToan', 'DaHoanThanh', 'DaHuy')
                GROUP BY dv.maDatVe
                ORDER BY dv.ngayDat DESC
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
                ],
                'selectedDate' => $dateFilter
            ]);
        } catch (Exception $e) {
            error_log("Error in getTicketSalesByDateAjax: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi khi tải dữ liệu'
            ]);
        }
        exit();
    }

    // Doanh thu theo ngày/tháng/năm với lọc
    private function getRevenueByDateRange($startDate = null, $endDate = null, $filterType = 'day') {
        try {
            // Nếu không có ngày, sử dụng ngày hôm nay
            if (!$startDate) {
                $startDate = date('Y-m-d');
            }
            if (!$endDate) {
                $endDate = $startDate;
            }
            
            // Định dạng ngày
            $startDate = date('Y-m-d', strtotime($startDate));
            $endDate = date('Y-m-d', strtotime($endDate));
            
            $results = fetchAll(
                "SELECT 
                    DATE_FORMAT(dv.ngayCapNhat, '%Y-%m-%d') as ngay,
                    SUM(dv.tongTienSauGiam) as tongDoanhThu,
                    COUNT(DISTINCT dv.maDatVe) as soLuongVe,
                    COUNT(DISTINCT nd.maNguoiDung) as soKhachHang
                FROM datve dv
                LEFT JOIN nguoidung nd ON dv.maNguoiDung = nd.maNguoiDung
                WHERE dv.trangThai IN ('DaThanhToan', 'DaHoanThanh')
                    AND DATE(dv.ngayCapNhat) BETWEEN '" . $startDate . "' AND '" . $endDate . "'
                GROUP BY DATE(dv.ngayCapNhat)
                ORDER BY dv.ngayCapNhat ASC"
            );
            
            return $results ? $results : [];
        } catch (Exception $e) {
            error_log("Error getting revenue by date range: " . $e->getMessage());
            return [];
        }
    }

    // Tổng doanh thu theo khoảng thời gian
    private function getTotalRevenueByDateRange($startDate = null, $endDate = null) {
        try {
            if (!$startDate) {
                $startDate = date('Y-m-d');
            }
            if (!$endDate) {
                $endDate = $startDate;
            }
            
            $startDate = date('Y-m-d', strtotime($startDate));
            $endDate = date('Y-m-d', strtotime($endDate));
            
            $result = fetch(
                "SELECT 
                    SUM(dv.tongTienSauGiam) as tongDoanhThu,
                    COUNT(DISTINCT dv.maDatVe) as soLuongVe,
                    COUNT(DISTINCT nd.maNguoiDung) as soKhachHang,
                    AVG(dv.tongTienSauGiam) as giaTriTrungBinh
                FROM datve dv
                LEFT JOIN nguoidung nd ON dv.maNguoiDung = nd.maNguoiDung
                WHERE dv.trangThai IN ('DaThanhToan', 'HetHieuLuc', 'DaHoanThanh', 'DaHuy')
                    AND DATE(dv.ngayCapNhat) BETWEEN '" . $startDate . "' AND '" . $endDate . "'"
            );
            
            return $result ? $result : ['tongDoanhThu' => 0, 'soLuongVe' => 0, 'soKhachHang' => 0, 'giaTriTrungBinh' => 0];
        } catch (Exception $e) {
            error_log("Error getting total revenue by date range: " . $e->getMessage());
            return ['tongDoanhThu' => 0, 'soLuongVe' => 0, 'soKhachHang' => 0, 'giaTriTrungBinh' => 0];
        }
    }

    // Thống kê doanh thu theo tài xế và phương tiện
    private function getRevenueByDriverAndVehicle($startDate = null, $endDate = null) {
        try {
            if (!$startDate) {
                $startDate = date('Y-m-01');
            }
            if (!$endDate) {
                $endDate = date('Y-m-d');
            }
            
            $startDate = date('Y-m-d', strtotime($startDate));
            $endDate = date('Y-m-d', strtotime($endDate));
            
            $results = fetchAll(
                "SELECT 
                    nd.maNguoiDung,
                    nd.tenNguoiDung as tenTaiXe,
                    nd.soDienThoai,
                    lpt.tenLoaiPhuongTien,
                    pt.bienSo,
                    COUNT(DISTINCT cx.maChuyenXe) as soChuyenDen,
                    COUNT(DISTINCT cdv.maDatVe) as soVe,
                    SUM(cdv.giaVe) as tongDoanhThu,
                    AVG(cdv.giaVe) as giaTriTrungBinh,
                    ROUND(SUM(cdv.giaVe) * 0.1, 0) as hoanHong
                FROM chuyenxe cx
                INNER JOIN lichtrinh lt ON cx.maLichTrinh = lt.maLichTrinh
                INNER JOIN tuyenduong td ON lt.maTuyenDuong = td.maTuyenDuong
                INNER JOIN phuongtien pt ON cx.maPhuongTien = pt.maPhuongTien
                INNER JOIN loaiphuongtien lpt ON pt.maLoaiPhuongTien = lpt.maLoaiPhuongTien
                LEFT JOIN baocao_chuyendi bc ON cx.maChuyenXe = bc.maChuyenXe
                LEFT JOIN nguoidung nd ON bc.maTaiXe = nd.maNguoiDung
                LEFT JOIN chitiet_datve cdv ON cx.maChuyenXe = cdv.maChuyenXe
                WHERE cdv.trangThai = 'DaThanhToan'
                    AND DATE(cx.ngayKhoiHanh) BETWEEN '" . $startDate . "' AND '" . $endDate . "'
                GROUP BY nd.maNguoiDung, pt.maPhuongTien, lpt.maLoaiPhuongTien
                ORDER BY tongDoanhThu DESC"
            );
            
            return $results ? $results : [];
        } catch (Exception $e) {
            error_log("Error getting revenue by driver and vehicle: " . $e->getMessage());
            return [];
        }
    }

    // AJAX endpoint để lấy doanh thu lọc theo ngày/tháng/năm
    public function getFilteredRevenueAjax() {
        $this->checkAdminAccess();
        header('Content-Type: application/json');
        
        try {
            $startDate = isset($_GET['startDate']) ? $_GET['startDate'] : null;
            $endDate = isset($_GET['endDate']) ? $_GET['endDate'] : null;
            $filterType = isset($_GET['filterType']) ? $_GET['filterType'] : 'day'; // day, month, year
            
            // Lấy dữ liệu doanh thu
            $revenueData = $this->getRevenueByDateRange($startDate, $endDate, $filterType);
            $totalRevenue = $this->getTotalRevenueByDateRange($startDate, $endDate);
            
            echo json_encode([
                'success' => true,
                'data' => $revenueData,
                'summary' => $totalRevenue,
                'dateRange' => [
                    'startDate' => date('d/m/Y', strtotime($startDate)),
                    'endDate' => date('d/m/Y', strtotime($endDate))
                ]
            ]);
        } catch (Exception $e) {
            error_log("Error in getFilteredRevenueAjax: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi khi tải dữ liệu'
            ]);
        }
        exit();
    }

    // AJAX endpoint để lấy thống kê doanh thu tài xế
   public function getDriverRevenueAjax()
{
    $this->checkAdminAccess();
    header('Content-Type: application/json');

    try {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $startDate = isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-01');
        $endDate = isset($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-d');
        $perPage = 15;
        $offset = ($page - 1) * $perPage;

        // Chuẩn hoá ngày
        $start = date('Y-m-d', strtotime($startDate));
        $end = date('Y-m-d', strtotime($endDate));

        // =================================================================
        // 1. COUNT - Đếm số bộ (tài xế + xe) có doanh thu trong khoảng thời gian
        // =================================================================
        $countQuery = fetch("
            SELECT COUNT(*) AS total FROM (
                SELECT DISTINCT nd.maNguoiDung, pt.maPhuongTien
                FROM chuyenxe cx
                INNER JOIN lichtrinh lt ON cx.maLichTrinh = lt.maLichTrinh
                INNER JOIN phuongtien pt ON cx.maPhuongTien = pt.maPhuongTien
                INNER JOIN nguoidung nd ON lt.maTaiXe = nd.maNguoiDung  -- Dùng tài xế từ lịch trình
                INNER JOIN chitiet_datve cdv ON cx.maChuyenXe = cdv.maChuyenXe
                WHERE cdv.trangThai = 'DaThanhToan'
                  AND DATE(cx.ngayKhoiHanh) BETWEEN '$start' AND '$end'
            ) AS sub
        ");

        $totalRows = $countQuery ? $countQuery['total'] : 0;
        $totalPages = ceil($totalRows / $perPage);

        // =================================================================
        // 2. DATA QUERY - Lấy doanh thu chi tiết (tính cả chuyến chưa hoàn thành)
        // =================================================================
        $dataQuery = fetchAll("
            SELECT 
                nd.maNguoiDung,
                nd.tenNguoiDung AS tenTaiXe,
                nd.soDienThoai,
                lpt.tenLoaiPhuongTien,
                pt.bienSo,

                COUNT(DISTINCT cx.maChuyenXe) AS soChuyenXe,
                COUNT(cdv.maChiTiet) AS soVe,
                COALESCE(SUM(cdv.giaVe), 0) AS tongDoanhThu,
                COALESCE(AVG(cdv.giaVe), 0) AS giaTriTrungBinh,
                ROUND(COALESCE(SUM(cdv.giaVe), 0) * 0.1, 0) AS hoanHong

            FROM chuyenxe cx
            INNER JOIN lichtrinh lt ON cx.maLichTrinh = lt.maLichTrinh
            INNER JOIN phuongtien pt ON cx.maPhuongTien = pt.maPhuongTien
            INNER JOIN loaiphuongtien lpt ON pt.maLoaiPhuongTien = lpt.maLoaiPhuongTien
            INNER JOIN nguoidung nd ON lt.maTaiXe = nd.maNguoiDung  -- Tài xế từ lịch trình
            INNER JOIN chitiet_datve cdv ON cx.maChuyenXe = cdv.maChuyenXe
                AND cdv.trangThai = 'DaThanhToan'
                

            WHERE DATE(cx.ngayKhoiHanh) BETWEEN '$start' AND '$end'
              AND nd.maVaiTro = 3  -- Đảm bảo là tài xế

            GROUP BY nd.maNguoiDung, pt.maPhuongTien
            HAVING tongDoanhThu > 0
            ORDER BY tongDoanhThu DESC
            LIMIT $offset, $perPage
        ");

        echo json_encode([
            'success' => true,
            'data' => $dataQuery ?: [],
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'total' => $totalRows,
                'perPage' => $perPage
            ],
            'dateRange' => [
                'startDate' => date('d/m/Y', strtotime($start)),
                'endDate' => date('d/m/Y', strtotime($end))
            ]
        ]);

    } catch (Exception $e) {
        error_log("Driver Revenue AJAX Error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi tải dữ liệu: ' . $e->getMessage()
        ]);
    }
    exit();
}
    // Thống kê trạng thái chuyến xe theo khoảng ngày
private function getTripStatusStats($startDate = null, $endDate = null) {
    try {
        if (!$startDate) $startDate = date('Y-m-01'); // Mặc định tháng hiện tại
        if (!$endDate) $endDate = date('Y-m-d');
        
        $startDate = date('Y-m-d', strtotime($startDate));
        $endDate = date('Y-m-d', strtotime($endDate));
        
        $results = fetchAll(
            "SELECT 
                trangThai as status,
                COUNT(*) as count
            FROM chuyenxe
            WHERE DATE(ngayKhoiHanh) BETWEEN '" . $startDate . "' AND '" . $endDate . "'
            GROUP BY trangThai
            ORDER BY FIELD(trangThai, 'Sẵn sàng', 'Khởi hành', 'Hoàn thành', 'Bị hủy', 'Delay')"
        );
        
        // Đảm bảo có tất cả trạng thái, nếu thiếu thì count = 0
        $statuses = ['Sẵn sàng', 'Khởi hành', 'Hoàn thành', 'Bị hủy', 'Delay'];
        $stats = [];
        foreach ($statuses as $status) {
            $found = array_filter($results, function($row) use ($status) {
                return $row['status'] === $status;
            });
            $stats[$status] = !empty($found) ? reset($found)['count'] : 0;
        }
        
        return $stats;
    } catch (Exception $e) {
        error_log("Error getting trip status stats: " . $e->getMessage());
        return array_fill_keys(['Sẵn sàng', 'Khởi hành', 'Hoàn thành', 'Bị hủy', 'Delay'], 0);
    }
}

// AJAX endpoint để lấy thống kê trạng thái chuyến xe
public function getTripStatusStatsAjax() {
    $this->checkAdminAccess();
    header('Content-Type: application/json');
    
    try {
        $startDate = isset($_GET['startDate']) ? $_GET['startDate'] : null;
        $endDate = isset($_GET['endDate']) ? $_GET['endDate'] : null;
        
        $stats = $this->getTripStatusStats($startDate, $endDate);
        
        echo json_encode([
            'success' => true,
            'data' => $stats,
            'dateRange' => [
                'startDate' => date('d/m/Y', strtotime($startDate)),
                'endDate' => date('d/m/Y', strtotime($endDate))
            ]
        ]);
    } catch (Exception $e) {
        error_log("Error in getTripStatusStatsAjax: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi tải dữ liệu'
        ]);
    }
    exit();
}

    private function getDetailedRevenue($startDate = null, $endDate = null) {
        try {
            if (!$startDate) {
                $startDate = date('Y-m-01');
            }
            if (!$endDate) {
                $endDate = date('Y-m-d');
            }
            
            $startDate = date('Y-m-d', strtotime($startDate));
            $endDate = date('Y-m-d', strtotime($endDate));
            
            $results = fetchAll(
                "SELECT 
                    dv.maDatVe,
                    DATE_FORMAT(dv.ngayCapNhat, '%d/%m/%Y') as ngayCapNhat,
                    nd.tenNguoiDung,
                    nd.soDienThoai,
                    td.kyHieuTuyen,
                    dv.soLuongVe,
                    dv.tongTien as doanhThuGop,
                    dv.giamGia as giam,
                    dv.tongTienSauGiam as doanhThuThucTe,
                    dv.phuongThucThanhToan,
                    dv.trangThai
                FROM datve dv
                LEFT JOIN nguoidung nd ON dv.maNguoiDung = nd.maNguoiDung
                LEFT JOIN chitiet_datve cdv ON dv.maDatVe = cdv.maDatVe
                LEFT JOIN chuyenxe cx ON cdv.maChuyenXe = cx.maChuyenXe
                LEFT JOIN lichtrinh lt ON cx.maLichTrinh = lt.maLichTrinh
                LEFT JOIN tuyenduong td ON lt.maTuyenDuong = td.maTuyenDuong
                WHERE dv.trangThai IN ('DangGiu', 'DaThanhToan', 'HetHieuLuc', 'DaHoanThanh', 'DaHuy')
                    AND DATE(dv.ngayCapNhat) BETWEEN '" . $startDate . "' AND '" . $endDate . "'
                GROUP BY dv.maDatVe
                ORDER BY dv.ngayCapNhat DESC, dv.maDatVe DESC"
            );
            
            return $results ? $results : [];
        } catch (Exception $e) {
            error_log("Error getting detailed revenue: " . $e->getMessage());
            return [];
        }
    }

    public function getDetailedRevenueAjax() {
        $this->checkAdminAccess();
        header('Content-Type: application/json');
        
        try {
            $startDate = isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-01');
            $endDate = isset($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-d');
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            
            $startDate = date('Y-m-d', strtotime($startDate));
            $endDate = date('Y-m-d', strtotime($endDate));
            
            $perPage = 20;
            $offset = ($page - 1) * $perPage;
            
            // Get total count
            $countResult = fetch(
                "SELECT COUNT(DISTINCT dv.maDatVe) as total FROM datve dv
                WHERE dv.trangThai IN ('DangGiu', 'DaThanhToan', 'HetHieuLuc', 'DaHoanThanh', 'DaHuy')
                    AND DATE(dv.ngayCapNhat) BETWEEN '" . $startDate . "' AND '" . $endDate . "'"
            );
            
            $totalRows = $countResult ? $countResult['total'] : 0;
            $totalPages = ceil($totalRows / $perPage);
            
            // Get paginated data
            $revenueData = fetchAll(
                "SELECT DISTINCT
                    dv.maDatVe,
                    DATE_FORMAT(dv.ngayCapNhat, '%d/%m/%Y') as ngayCapNhat,
                    nd.tenNguoiDung,
                    nd.soDienThoai,
                    COALESCE(td.kyHieuTuyen, 'N/A') as kyHieuTuyen,
                    dv.soLuongVe,
                    dv.tongTien as doanhThuGop,
                    dv.giamGia as giam,
                    dv.tongTienSauGiam as doanhThuThucTe,
                    dv.phuongThucThanhToan,
                    dv.trangThai
                FROM datve dv
                LEFT JOIN nguoidung nd ON dv.maNguoiDung = nd.maNguoiDung
                LEFT JOIN (
                    SELECT DISTINCT maDatVe, maChuyenXe
                    FROM chitiet_datve
                ) cdv ON dv.maDatVe = cdv.maDatVe
                LEFT JOIN chuyenxe cx ON cdv.maChuyenXe = cx.maChuyenXe
                LEFT JOIN lichtrinh lt ON cx.maLichTrinh = lt.maLichTrinh
                LEFT JOIN tuyenduong td ON lt.maTuyenDuong = td.maTuyenDuong
                WHERE dv.trangThai IN ('DangGiu', 'DaThanhToan', 'HetHieuLuc', 'DaHoanThanh', 'DaHuy')
                    AND DATE(dv.ngayCapNhat) BETWEEN '" . $startDate . "' AND '" . $endDate . "'
                ORDER BY dv.ngayCapNhat DESC, dv.maDatVe DESC
                LIMIT " . $offset . ", " . $perPage
            );
            
            // Calculate totals for summary
            $summaryResult = fetch(
                "SELECT 
                    SUM(dv.tongTien) as tongGop,
                    SUM(dv.giamGia) as tongGiam,
                    SUM(dv.tongTienSauGiam) as tongThucTe,
                    COUNT(DISTINCT dv.maDatVe) as soGiaoDich
                FROM datve dv
                WHERE dv.trangThai IN ('DangGiu', 'DaThanhToan', 'HetHieuLuc', 'DaHoanThanh', 'DaHuy')
                    AND DATE(dv.ngayCapNhat) BETWEEN '" . $startDate . "' AND '" . $endDate . "'"
            );
            
            echo json_encode([
                'success' => true,
                'data' => $revenueData ?: [],
                'summary' => $summaryResult ?: ['tongGop' => 0, 'tongGiam' => 0, 'tongThucTe' => 0, 'soGiaoDich' => 0],
                'pagination' => [
                    'currentPage' => $page,
                    'totalPages' => $totalPages,
                    'total' => $totalRows,
                    'perPage' => $perPage
                ],
                'dateRange' => [
                    'startDate' => $startDate,
                    'endDate' => $endDate
                ]
            ]);
        } catch (Exception $e) {
            error_log("Error in getDetailedRevenueAjax: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi khi tải dữ liệu: ' . $e->getMessage()
            ]);
        }
        exit();
    }

    private function getTransactionStatsByMonth($month = null, $year = null) {
        try {
            if (!$month) {
                $month = date('m');
            }
            if (!$year) {
                $year = date('Y');
            }
            
            $stats = fetch(
                "SELECT 
                    SUM(CASE WHEN trangThai = 'DaThanhToan' THEN 1 ELSE 0 END) as daThanhToan,
                    SUM(CASE WHEN trangThai = 'DaHuy' THEN 1 ELSE 0 END) as daHuy,
                    SUM(CASE WHEN trangThai = 'DaHoanThanh' THEN 1 ELSE 0 END) as daHoanThanh,
                    SUM(CASE WHEN trangThai = 'HetHieuLuc' THEN 1 ELSE 0 END) as hetHieuLuc,
                    SUM(CASE WHEN trangThai = 'DangGiu' THEN 1 ELSE 0 END) as dangGiu,
                    COUNT(*) as tongCong
                FROM datve
                WHERE MONTH(ngayCapNhat) = '" . $month . "' AND YEAR(ngayCapNhat) = '" . $year . "'"
            );
            
            return $stats ?: [
                'daThanhToan' => 0,
                'daHuy' => 0,
                'daHoanThanh' => 0,
                'hetHieuLuc' => 0,
                'dangGiu' => 0,
                'tongCong' => 0
            ];
        } catch (Exception $e) {
            error_log("Error getting transaction stats: " . $e->getMessage());
            return [
                'daThanhToan' => 0,
                'daHuy' => 0,
                'daHoanThanh' => 0,
                'hetHieuLuc' => 0,
                'dangGiu' => 0,
                'tongCong' => 0
            ];
        }
    }

    public function getTransactionStatsByStatusAjax() {
        $this->checkAdminAccess();
        header('Content-Type: application/json');
        
        try {
            $month = isset($_GET['month']) ? str_pad($_GET['month'], 2, '0', STR_PAD_LEFT) : date('m');
            $year = isset($_GET['year']) ? $_GET['year'] : date('Y');
            
            $stats = $this->getTransactionStatsByMonth($month, $year);
            
            echo json_encode([
                'success' => true,
                'data' => $stats,
                'month' => $month,
                'year' => $year
            ]);
        } catch (Exception $e) {
            error_log("Error in getTransactionStatsByStatusAjax: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi khi tải dữ liệu'
            ]);
        }
        exit();
    }
}
?>
