<?php
require_once __DIR__ . '/../models/DriverReport.php';

class DriverReportController {
    
    /**
     * Display driver's report page
     */
    public function index() {
        // Check if user is logged in and is a driver (role id 3)
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] != 3) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        try {
            $driverId = $_SESSION['user_id'];
            error_log("[DriverReportController] Loading trips for driver: $driverId");
            
            $upcomingTrips = DriverReport::getTodayTrips($driverId);
            error_log("[DriverReportController] Loaded " . count($upcomingTrips) . " trips");
            
            include __DIR__ . '/../views/driver/report.php';
            
        } catch (Exception $e) {
            error_log("[DriverReportController] ERROR in index: " . $e->getMessage());
            error_log("[DriverReportController] Stack trace: " . $e->getTraceAsString());
            $_SESSION['error'] = 'Có lỗi xảy ra khi tải danh sách chuyến đi: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/');
            exit;
        }
    }
    
    /**
     * Display attendance page for a specific trip
     * Updated to receive tripId as parameter from router instead of $_GET
     */
    public function attendance($tripId = '') {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] != 3) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        try {
            $driverId = $_SESSION['user_id'];
            
            if (!$tripId) {
                $_SESSION['error'] = 'Không tìm thấy chuyến đi.';
                header('Location: ' . BASE_URL . '/driver/report');
                exit;
            }

            $trip = DriverReport::getTripDetails($tripId, $driverId);
            if (!$trip) {
                $_SESSION['error'] = 'Không tìm thấy chuyến đi.';
                header('Location: ' . BASE_URL . '/driver/report');
                exit;
            }

            // Check if trip status is "Sẵn sàng" - only allow attendance for this status
            if ($trip['trangThai'] !== 'Sẵn sàng') {
                $_SESSION['error'] = 'Chỉ có thể điểm danh cho chuyến xe ở trạng thái "Sẵn sàng".';
                header('Location: ' . BASE_URL . '/driver/report');
                exit;
            }

            $passengers = DriverReport::getTripPassengers($tripId);
            $allSeats = DriverReport::getVehicleSeats($trip['maPhuongTien']);
            
            include __DIR__ . '/../views/driver/attendance.php';
            
        } catch (Exception $e) {
            error_log("DriverReportController attendance error: " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi tải trang điểm danh.';
            header('Location: ' . BASE_URL . '/driver/report');
            exit;
        }
    }
    
    /**
     * Confirm departure (save attendance report)
     */
    public function confirmDeparture() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/driver/report');
            exit;
        }

        try {
            $tripId = $_POST['trip_id'] ?? '';
            $driverId = $_SESSION['user_id'];
            $attendanceData = $_POST['attendance'] ?? [];
            $tripNotes = $_POST['trip_notes'] ?? '';

            if (!$tripId) {
                $_SESSION['error'] = 'Không tìm thấy chuyến đi.';
                header('Location: ' . BASE_URL . '/driver/report');
                exit;
            }

            $result = DriverReport::saveAttendanceReport($tripId, $driverId, $attendanceData, $tripNotes);

            if ($result['success']) {
                $_SESSION['success'] = $result['message'];
            } else {
                $_SESSION['error'] = $result['message'];
            }

            header('Location: ' . BASE_URL . '/driver/report');
            exit;

        } catch (Exception $e) {
            error_log("DriverReportController confirmDeparture error: " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi lưu báo cáo.';
            header('Location: ' . BASE_URL . '/driver/report');
            exit;
        }
    }

    /**
     * Complete a trip
     */
    public function completeTrip() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/driver/report');
            exit;
        }

        try {
            $tripId = $_POST['trip_id'] ?? '';
            $driverId = $_SESSION['user_id'];

            $result = DriverReport::completeTrip($tripId, $driverId);

            if ($result['success']) {
                $_SESSION['success'] = $result['message'];
            } else {
                $_SESSION['error'] = $result['message'];
            }

            header('Location: ' . BASE_URL . '/driver/report');
            exit;

        } catch (Exception $e) {
            error_log("DriverReportController completeTrip error: " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi kết thúc chuyến xe.';
            header('Location: ' . BASE_URL . '/driver/report');
            exit;
        }
    }
}
