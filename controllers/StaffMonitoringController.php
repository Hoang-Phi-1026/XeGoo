<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Staff.php';

class StaffMonitoringController {
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Check staff access
    private function checkStaffAccess() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] != 2) {
            $_SESSION['error'] = 'Bạn không có quyền truy cập chức năng này!';
            header('Location: ' . BASE_URL . '/login');
            exit();
        }
    }

    // Display list of reports for today
    public function index() {
        $this->checkStaffAccess();
        
        $reports = Staff::getTodayReports();
        $tripStats = Staff::getTodayTripStats();
        $approvedTrips = Staff::getApprovedDepartingTrips();
        $completedTrips = Staff::getCompletedTrips();
        
        error_log("[v0] Staff reports count: " . count($reports));
        error_log("[v0] Trip stats: " . json_encode($tripStats));
        error_log("[v0] Approved trips count: " . count($approvedTrips));
        error_log("[v0] Completed trips count: " . count($completedTrips));
        
        require_once __DIR__ . '/../views/staff/monitoring-list.php';
    }

    // Display report details
    public function detail($reportId) {
        $this->checkStaffAccess();
        
        $report = Staff::getReportDetail($reportId);
        if (!$report) {
            $_SESSION['error'] = 'Báo cáo không tồn tại!';
            header('Location: ' . BASE_URL . '/staff/monitoring');
            exit();
        }
        
        $passengers = Staff::getReportPassengers($reportId);
        
        require_once __DIR__ . '/../views/staff/monitoring-detail.php';
    }

    // API: Confirm departure
    public function confirmDeparture() {
        $this->checkStaffAccess();
        
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $reportId = $data['reportId'] ?? null;
        
        if (!$reportId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thiếu mã báo cáo']);
            exit();
        }
        
        $result = Staff::confirmDeparture($reportId);
        
        if ($result['success']) {
            http_response_code(200);
        } else {
            http_response_code(400);
        }
        
        echo json_encode($result);
        exit();
    }

    // Get reports by date range (for filtering)
    public function getByDateRange() {
        $this->checkStaffAccess();
        
        header('Content-Type: application/json');
        
        $startDate = $_GET['startDate'] ?? date('Y-m-d');
        $endDate = $_GET['endDate'] ?? date('Y-m-d');
        
        $reports = Staff::getReportsByDateRange($startDate, $endDate);
        
        echo json_encode(['success' => true, 'data' => $reports]);
        exit();
    }
}
