<?php
require_once __DIR__ . '/../models/Chat.php';
require_once __DIR__ . '/../config/config.php';

class ChatController {
    private $chatModel;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->chatModel = new Chat();
    }

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Vui lòng đăng nhập để sử dụng dịch vụ hỗ trợ!';
            header('Location: ' . BASE_URL . '/login');
            exit();
        }

        $maNguoiDung = $_SESSION['user_id'];
        $vaiTro = $_SESSION['user_role'];

        // Create or get existing session
        $session = $this->chatModel->createOrGetSession($maNguoiDung, $vaiTro);

        if (!$session) {
            $_SESSION['error'] = 'Không thể tạo phiên chat!';
            header('Location: ' . BASE_URL . '/');
            exit();
        }

        $maPhien = $session['maPhien'];
        $messages = $this->chatModel->getMessages($maPhien);
        $userRole = $this->chatModel->getUserRoleInSession($maPhien);

        // Mark messages as read
        $this->chatModel->markMessagesAsRead($maPhien);

        require_once __DIR__ . '/../views/chat/customer-support.php';
    }

    public function staffDashboard() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 2) {
            $_SESSION['error'] = 'Bạn không có quyền truy cập!';
            header('Location: ' . BASE_URL . '/');
            exit();
        }

        $sessions = $this->chatModel->getPendingSessions();

        require_once __DIR__ . '/../views/chat/staff-support.php';
    }

    public function getSessions() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit();
        }

        // For staff, get all pending sessions
        if ($_SESSION['user_role'] == 2) {
            $sessions = $this->chatModel->getPendingSessions();
        } else {
            // For customers/drivers, get their own session
            $sessions = [];
        }

        echo json_encode([
            'success' => true,
            'sessions' => $sessions
        ]);
        exit();
    }

    public function getMessages($sessionId = null) {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }

        // Get sessionId from URL parameter or query string
        if ($sessionId === null) {
            $sessionId = intval($_GET['sessionId'] ?? $_GET['maPhien'] ?? 0);
        } else {
            $sessionId = intval($sessionId);
        }

        if ($sessionId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid session']);
            exit();
        }

        $messages = $this->chatModel->getMessages($sessionId);

        echo json_encode([
            'success' => true,
            'messages' => $messages,
            'unreadCount' => $this->chatModel->getUnreadCount($sessionId)
        ]);
        exit();
    }

    public function getPendingCount() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }

        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 2) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }

        $sessions = $this->chatModel->getPendingSessions();
        $totalUnread = 0;

        foreach ($sessions as $session) {
            $totalUnread += $session['unreadCount'] ?? 0;
        }

        echo json_encode([
            'success' => true,
            'count' => $totalUnread
        ]);
        exit();
    }

    public function getPendingSessions() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 2) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }

        $sessions = $this->chatModel->getPendingSessions();

        echo json_encode([
            'success' => true,
            'sessions' => $sessions
        ]);
        exit();
    }

    public function getMessagesApi() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }

        $maPhien = intval($_GET['maPhien'] ?? 0);
        $lastMessageId = intval($_GET['lastMessageId'] ?? 0);

        if ($maPhien <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid session']);
            exit();
        }

        $messages = $this->chatModel->getMessages($maPhien);

        // Filter messages after lastMessageId if provided
        if ($lastMessageId > 0) {
            $messages = array_filter($messages, function($msg) use ($lastMessageId) {
                return $msg['maTinNhan'] > $lastMessageId;
            });
        }

        echo json_encode([
            'success' => true,
            'messages' => array_values($messages)
        ]);
        exit();
    }

    public function assignStaff() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }

        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 2) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $maPhien = intval($input['maPhien'] ?? 0);
        $maNhanVien = $_SESSION['user_id'];

        if ($maPhien <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid session']);
            exit();
        }

        if ($this->chatModel->assignStaffToSession($maPhien, $maNhanVien)) {
            echo json_encode(['success' => true, 'message' => 'Staff assigned']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to assign staff']);
        }
        exit();
    }

    public function sendMessageApi() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $maPhien = intval($input['maPhien'] ?? 0);
        $noiDung = trim($input['noiDung'] ?? '');

        if ($maPhien <= 0 || empty($noiDung)) {
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            exit();
        }

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit();
        }

        $nguoiGui = $_SESSION['user_id'];
        $vaiTro = $_SESSION['user_role'];

        // Map role to Vietnamese
        $vaiTroMap = [1 => 'Nhân viên', 2 => 'Nhân viên', 3 => 'Tài xế', 4 => 'Khách hàng'];
        $vaiTroNguoiGui = $vaiTroMap[$vaiTro] ?? 'Khách hàng';

        $maTinNhan = $this->chatModel->sendMessage($maPhien, $nguoiGui, $vaiTroNguoiGui, $noiDung);

        if ($maTinNhan) {
            echo json_encode([
                'success' => true,
                'maTinNhan' => $maTinNhan,
                'message' => 'Tin nhắn đã được gửi'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send message']);
        }
        exit();
    }

    public function sendMessage() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $maPhien = intval($input['session_id'] ?? $input['maPhien'] ?? 0);
        $noiDung = trim($input['message'] ?? $input['noiDung'] ?? '');

        if ($maPhien <= 0 || empty($noiDung)) {
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            exit();
        }

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit();
        }

        $nguoiGui = $_SESSION['user_id'];
        $vaiTro = $_SESSION['user_role'];

        // Map role to Vietnamese
        $vaiTroMap = [1 => 'Nhân viên', 2 => 'Nhân viên', 3 => 'Tài xế', 4 => 'Khách hàng'];
        $vaiTroNguoiGui = $input['sender_role'] ?? $vaiTroMap[$vaiTro] ?? 'Khách hàng';

        $maTinNhan = $this->chatModel->sendMessage($maPhien, $nguoiGui, $vaiTroNguoiGui, $noiDung);

        if ($maTinNhan) {
            echo json_encode([
                'success' => true,
                'maTinNhan' => $maTinNhan,
                'message' => 'Tin nhắn đã được gửi'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send message']);
        }
        exit();
    }

    public function createSession() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit();
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $maNguoiDung = $input['user_id'] ?? $_SESSION['user_id'];
        $vaiTro = $input['user_role'] ?? $_SESSION['user_role'];

        $session = $this->chatModel->createOrGetSession($maNguoiDung, $vaiTro);

        if ($session) {
            echo json_encode([
                'success' => true,
                'session_id' => $session['maPhien'],
                'message' => 'Session created'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create session']);
        }
        exit();
    }

    public function closeSession() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $maPhien = intval($input['session_id'] ?? $input['maPhien'] ?? 0);
        $danhGia = intval($input['danhGia'] ?? 0);

        if ($maPhien <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid session']);
            exit();
        }

        if ($this->chatModel->closeSession($maPhien, $danhGia > 0 ? $danhGia : null)) {
            echo json_encode(['success' => true, 'message' => 'Session closed']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to close session']);
        }
        exit();
    }

    public function markAsRead() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $maPhien = intval($input['session_id'] ?? $input['maPhien'] ?? 0);

        if ($maPhien <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid session']);
            exit();
        }

        if ($this->chatModel->markMessagesAsRead($maPhien)) {
            echo json_encode(['success' => true, 'message' => 'Messages marked as read']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to mark as read']);
        }
        exit();
    }
}
?>
