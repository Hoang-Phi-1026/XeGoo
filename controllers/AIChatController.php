<?php
require_once __DIR__ . '/../models/AIChat.php';

class AIChatController {
    private $aiChatModel;
    
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->aiChatModel = new AIChat();
    }

    /**
     * Show AI chat page
     */
    public function index() {
        // Users can access AI chat without login, but we track their session
        $isLoggedIn = isset($_SESSION['user_id']);
        $userName = $isLoggedIn ? ($_SESSION['user_name'] ?? 'Khách') : 'Khách';
        
        require_once __DIR__ . '/../views/aichat/ai-support.php';
    }

    /**
     * API endpoint to get AI response
     */
    public function askAI() {
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Phương thức không hợp lệ']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $userMessage = trim($input['message'] ?? '');
        
        if (empty($userMessage)) {
            echo json_encode(['error' => 'Tin nhắn không hợp lệ']);
            exit;
        }
        
        // Get AI response
        $response = $this->aiChatModel->askAI($userMessage);
        
        echo json_encode($response);
        exit;
    }

    /**
     * Switch to live chat with staff
     */
    public function switchToStaffChat() {
        header('Content-Type: application/json; charset=utf-8');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Phương thức không hợp lệ']);
            exit;
        }
        
        // If user is not logged in, redirect to login
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['return_url'] = BASE_URL . '/support';
            echo json_encode([
                'success' => false,
                'requireLogin' => true,
                'message' => 'Vui lòng đăng nhập để trò chuyện với nhân viên'
            ]);
            exit;
        }
        
        // Create a chat session for the user
        require_once __DIR__ . '/../models/Chat.php';
        $chatModel = new Chat();
        $session = $chatModel->createOrGetSession($_SESSION['user_id'], $_SESSION['user_role']);
        
        if ($session) {
            echo json_encode([
                'success' => true,
                'sessionId' => $session['maPhien'],
                'redirectUrl' => BASE_URL . '/support'
            ]);
        } else {
            echo json_encode(['error' => 'Không thể tạo phiên chat']);
        }
        exit;
    }
}
?>
