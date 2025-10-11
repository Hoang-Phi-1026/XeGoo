<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/DriverReport.php';

class DriverReportController {
    
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check if user is logged in and is a driver
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 3) {
            $_SESSION['error'] = 'Bạn không có quyền truy cập trang này.';
            header('Location: ' . BASE_URL . '/');
            exit;
        }
    }
    
    /**
     * Show trip report/attendance page
     */
    public function index() {
        try {
            $driverId = $_SESSION['user_id'];
            
            // Get upcoming trips for this driver
            $upcomingTrips = DriverReport::getUpcomingTrips($driverId);
            
            $viewData = compact('upcomingTrips', 'driverId');
            extract($viewData);
            
            include __DIR__ . '/../views/driver/report.php';
            
        } catch (Exception $e) {
            error_log("DriverReportController index error: " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi tải danh sách chuyến đi.';
            header('Location: ' . BASE_URL . '/');
            exit;
        }
    }
    
    /**
     * Show attendance check page for a specific trip
     */
    public function attendance($tripId) {
        try {
            $driverId = $_SESSION['user_id'];
            
            // Verify this trip belongs to this driver
            $trip = DriverReport::getTripDetails($tripId, $driverId);
            if (!$trip) {
                $_SESSION['error'] = 'Không tìm thấy chuyến đi hoặc bạn không có quyền truy cập.';
                header('Location: ' . BASE_URL . '/driver/report');
                exit;
            }
            
            // Get passenger list with ticket details
            $passengers = DriverReport::getTripPassengers($tripId);
            
            $allSeats = DriverReport::getVehicleSeats($trip['maPhuongTien']);
            
            $viewData = compact('trip', 'passengers', 'tripId', 'allSeats');
            extract($viewData);
            
            include __DIR__ . '/../views/driver/attendance.php';
            
        } catch (Exception $e) {
            error_log("DriverReportController attendance error: " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi tải thông tin chuyến đi.';
            header('Location: ' . BASE_URL . '/driver/report');
            exit;
        }
    }
    
    /**
     * Save attendance and confirm departure
     */
    public function confirmDeparture() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/driver/report');
            exit;
        }
        
        try {
            $tripId = $_POST['trip_id'] ?? '';
            $attendanceData = $_POST['attendance'] ?? [];
            $tripNotes = $_POST['trip_notes'] ?? '';
            $driverId = $_SESSION['user_id'];
            
            // Verify trip belongs to driver
            $trip = DriverReport::getTripDetails($tripId, $driverId);
            if (!$trip) {
                $_SESSION['error'] = 'Không tìm thấy chuyến đi.';
                header('Location: ' . BASE_URL . '/driver/report');
                exit;
            }
            
            $result = DriverReport::saveAttendanceReport($tripId, $driverId, $attendanceData, $tripNotes);
            
            if ($result['success']) {
                // Update trip status to "Khởi hành"
                $updateResult = DriverReport::updateTripStatus($tripId, 'Khởi hành');
                
                if ($updateResult) {
                    $_SESSION['success'] = 'Đã xác nhận khởi hành chuyến đi thành công!';
                } else {
                    $_SESSION['warning'] = 'Đã lưu điểm danh nhưng không thể cập nhật trạng thái chuyến đi.';
                }
            } else {
                $_SESSION['error'] = 'Có lỗi xảy ra khi lưu điểm danh: ' . $result['message'];
            }
            
            header('Location: ' . BASE_URL . '/driver/report');
            exit;
            
        } catch (Exception $e) {
            error_log("DriverReportController confirmDeparture error: " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi xác nhận khởi hành.';
            header('Location: ' . BASE_URL . '/driver/report');
            exit;
        }
    }
}
?>
