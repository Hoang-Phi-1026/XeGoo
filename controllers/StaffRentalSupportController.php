<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Staff.php';
require_once __DIR__ . '/../helpers/IDEncryptionHelper.php';

class StaffRentalSupportController {
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

    // Display list of pending rental requests
    public function index() {
        $this->checkStaffAccess();
        
        $requests = Staff::getPendingRentalRequests();
        error_log("[v0] StaffRentalSupportController::index - Requests count: " . count($requests));
        
        require_once __DIR__ . '/../views/staff/rental-support-list.php';
    }

    // Display rental request details
    public function detail($requestId) {
        $this->checkStaffAccess();
        
        $decryptedRequestId = IDEncryptionHelper::decryptId($requestId);
        if (!$decryptedRequestId) {
            $_SESSION['error'] = 'ID yêu cầu không hợp lệ!';
            header('Location: ' . BASE_URL . '/staff/rental-support');
            exit();
        }
        
        $request = Staff::getRentalRequestDetail($decryptedRequestId);
        if (!$request) {
            $_SESSION['error'] = 'Yêu cầu thuê xe không tồn tại!';
            header('Location: ' . BASE_URL . '/staff/rental-support');
            exit();
        }
        
        require_once __DIR__ . '/../views/staff/rental-support-detail.php';
    }

    // API: Update rental request status
    public function updateStatus() {
        $this->checkStaffAccess();
        
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $requestId = $data['requestId'] ?? null;
        $status = $data['status'] ?? null;
        
        error_log("[v0] StaffRentalSupportController::updateStatus - requestId: $requestId, status: $status");
        
        if (!$requestId || !$status) {
            http_response_code(400);
            error_log("[v0] StaffRentalSupportController::updateStatus - Missing required fields");
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin cần thiết']);
            exit();
        }
        
        $decryptedRequestId = IDEncryptionHelper::decryptId($requestId);
        if (!$decryptedRequestId) {
            $decryptedRequestId = $requestId;
        }
        
        $result = Staff::updateRentalRequestStatus($decryptedRequestId, $status);
        
        error_log("[v0] StaffRentalSupportController::updateStatus - Result: " . json_encode($result));
        
        if ($result['success']) {
            http_response_code(200);
        } else {
            http_response_code(400);
        }
        
        echo json_encode($result);
        exit();
    }
}
?>
