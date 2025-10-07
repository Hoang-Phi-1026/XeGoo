<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../lib/EmailService.php';
require_once __DIR__ . '/../models/VerificationCode.php';

class AuthController {
    private $userModel;
    private $verificationModel;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->userModel = new User();
        $this->verificationModel = new VerificationCode();
    }

    public function showLogin() {
        // If already logged in, redirect based on role
        if (isset($_SESSION['user_id'])) {
            $this->redirectBasedOnRole();
            exit();
        }
        
        // Process login if POST request
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processLogin();
        }
        
        // Show login form
        require_once __DIR__ . '/../views/auth/login.php';
    }

    public function showRegister() {
        // If already logged in, redirect based on role
        if (isset($_SESSION['user_id'])) {
            $this->redirectBasedOnRole();
            exit();
        }
        
        // Process registration if POST request
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processRegister();
        }
        
        // Show registration form
        require_once __DIR__ . '/../views/auth/register.php';
    }

    private function processLogin() {
        $identifier = trim($_POST['identifier'] ?? ''); // Can be email or phone
        $password = trim($_POST['password'] ?? '');

        // Validate input
        if (empty($identifier) || empty($password)) {
            $_SESSION['error'] = 'Vui lòng nhập đầy đủ thông tin!';
            return;
        }

        // Attempt login with email or phone
        $user = $this->userModel->loginWithIdentifier($identifier, $password);
        
        if ($user) {
            // Login successful
            $_SESSION['user_id'] = $user['maNguoiDung'];
            $_SESSION['user_name'] = $user['tenNguoiDung'];
            $_SESSION['user_role'] = $user['maVaiTro'];
            $_SESSION['user_phone'] = $user['soDienThoai'];
            $_SESSION['user_email'] = $user['eMail'];
            
            $_SESSION['success'] = 'Đăng nhập thành công!';
            
            $this->redirectBasedOnRole();
            exit();
        } else {
            $_SESSION['error'] = 'Email/Số điện thoại hoặc mật khẩu không chính xác!';
        }
    }

    public function showForgotPassword() {
        // If already logged in, redirect
        if (isset($_SESSION['user_id'])) {
            $this->redirectBasedOnRole();
            exit();
        }
        
        require_once __DIR__ . '/../views/auth/forgot-password.php';
    }

    public function sendResetCode() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $email = trim($input['email'] ?? '');
        
        if (empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập email!']);
            exit();
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Email không hợp lệ!']);
            exit();
        }
        
        // Check if email exists
        $user = $this->userModel->getUserByEmail($email);
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Email không tồn tại trong hệ thống!']);
            exit();
        }
        
        // Generate verification code
        $verificationCode = VerificationCode::generateCode();
        
        // Store code in database
        if (!$this->verificationModel->storeCode($email, $verificationCode)) {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra. Vui lòng thử lại!']);
            exit();
        }
        
        // Send verification email
        $emailService = new EmailService();
        $emailResult = $emailService->sendPasswordResetEmail($email, $user['tenNguoiDung'], $verificationCode);
        
        if ($emailResult['success']) {
            $_SESSION['reset_email'] = $email;
            echo json_encode(['success' => true, 'message' => 'Mã xác nhận đã được gửi đến email của bạn!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không thể gửi email. Vui lòng thử lại!']);
        }
        exit();
    }

    public function verifyResetCode() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $code = trim($input['code'] ?? '');
        
        if (empty($code)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mã xác nhận!']);
            exit();
        }
        
        if (!isset($_SESSION['reset_email'])) {
            echo json_encode(['success' => false, 'message' => 'Phiên làm việc đã hết hạn. Vui lòng thử lại!']);
            exit();
        }
        
        $email = $_SESSION['reset_email'];
        
        // Verify code
        if (!$this->verificationModel->verifyCode($email, $code)) {
            echo json_encode(['success' => false, 'message' => 'Mã xác nhận không đúng hoặc đã hết hạn!']);
            exit();
        }
        
        // Generate new random password
        $newPassword = $this->generateRandomPassword();
        
        // Update password in database
        $result = $this->userModel->updatePasswordByEmail($email, $newPassword);
        
        if (!$result['success']) {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi đặt lại mật khẩu!']);
            exit();
        }
        
        // Get user info
        $user = $this->userModel->getUserByEmail($email);
        
        // Send new password via email
        $emailService = new EmailService();
        $emailResult = $emailService->sendNewPasswordEmail($email, $user['tenNguoiDung'], $newPassword);
        
        if ($emailResult['success']) {
            unset($_SESSION['reset_email']);
            echo json_encode([
                'success' => true, 
                'message' => 'Mật khẩu mới đã được gửi đến email của bạn!',
                'redirect' => BASE_URL . '/login'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không thể gửi mật khẩu mới. Vui lòng liên hệ hỗ trợ!']);
        }
        exit();
    }

    private function generateRandomPassword($length = 8) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $password;
    }

    private function processRegister() {
        $data = [
            'tenNguoiDung' => trim($_POST['tenNguoiDung'] ?? ''),
            'soDienThoai' => trim($_POST['soDienThoai'] ?? ''),
            'eMail' => trim($_POST['eMail'] ?? ''),
            'matKhau' => trim($_POST['matKhau'] ?? ''),
            'confirmPassword' => trim($_POST['confirmPassword'] ?? ''),
            'gioiTinh' => $_POST['gioiTinh'] ?? '',
            'diaChi' => trim($_POST['diaChi'] ?? ''),
            'maVaiTro' => 4 // Default to customer role
        ];

        // Validate input
        $errors = $this->validateRegistration($data);
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['form_data'] = $data; // Keep form data for user convenience
            return;
        }

        error_log("[v0] Starting registration process for email: " . $data['eMail']);

        $verificationCode = VerificationCode::generateCode();
        
        error_log("[v0] Generated verification code: " . $verificationCode);
        
        // Store code in database
        $storeResult = $this->verificationModel->storeCode($data['eMail'], $verificationCode);
        
        if (!$storeResult) {
            error_log("[v0] Failed to store verification code in database");
            $_SESSION['error'] = 'Có lỗi xảy ra khi tạo mã xác nhận. Vui lòng kiểm tra xem bảng verification_codes đã được tạo chưa!';
            $_SESSION['form_data'] = $data;
            return;
        }
        
        error_log("[v0] Verification code stored successfully, sending email...");
        
        // Send verification email
        $emailService = new EmailService();
        $emailResult = $emailService->sendVerificationEmail(
            $data['eMail'],
            $data['tenNguoiDung'],
            $verificationCode
        );
        
        if (!$emailResult['success']) {
            error_log("[v0] Failed to send verification email: " . $emailResult['message']);
            $_SESSION['error'] = 'Không thể gửi email xác nhận. Vui lòng thử lại!';
            $_SESSION['form_data'] = $data;
            return;
        }
        
        error_log("[v0] Verification email sent successfully");
        
        $_SESSION['pending_registration'] = $data;
        $_SESSION['show_verification_modal'] = true;
        $_SESSION['success'] = 'Mã xác nhận đã được gửi đến email của bạn. Vui lòng kiểm tra email!';
        
        return;
    }

    public function verifyEmail() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit();
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $code = trim($input['code'] ?? '');
        
        error_log("[v0] Verify email endpoint called with code: " . $code);
        
        if (empty($code)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mã xác nhận!']);
            exit();
        }
        
        if (!isset($_SESSION['pending_registration'])) {
            error_log("[v0] No pending registration found in session");
            echo json_encode(['success' => false, 'message' => 'Phiên đăng ký đã hết hạn. Vui lòng đăng ký lại!']);
            exit();
        }
        
        $registrationData = $_SESSION['pending_registration'];
        
        error_log("[v0] Verifying code for email: " . $registrationData['eMail']);
        
        // Verify code
        if (!$this->verificationModel->verifyCode($registrationData['eMail'], $code)) {
            error_log("[v0] Code verification failed");
            echo json_encode(['success' => false, 'message' => 'Mã xác nhận không đúng hoặc đã hết hạn!']);
            exit();
        }
        
        error_log("[v0] Code verified, proceeding with user registration");
        
        // Code is valid, proceed with registration
        $result = $this->userModel->register($registrationData);
        
        if ($result['success']) {
            error_log("[v0] User registration completed successfully");
            // Clear session data
            unset($_SESSION['pending_registration']);
            unset($_SESSION['form_data']);
            unset($_SESSION['show_verification_modal']);
            
            echo json_encode([
                'success' => true, 
                'message' => 'Đăng ký thành công! Đang chuyển hướng...',
                'redirect' => BASE_URL . '/login'
            ]);
        } else {
            error_log("[v0] User registration failed: " . $result['message']);
            echo json_encode(['success' => false, 'message' => $result['message']]);
        }
        exit();
    }

    public function resendCode() {
        header('Content-Type: application/json');
        
        error_log("[v0] Resend code endpoint called");
        
        if (!isset($_SESSION['pending_registration'])) {
            error_log("[v0] No pending registration found in session");
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin đăng ký']);
            exit();
        }
        
        $registrationData = $_SESSION['pending_registration'];
        $verificationCode = VerificationCode::generateCode();
        
        error_log("[v0] Generated new verification code: " . $verificationCode . " for email: " . $registrationData['eMail']);
        
        // Store new code
        if (!$this->verificationModel->storeCode($registrationData['eMail'], $verificationCode)) {
            error_log("[v0] Failed to store new verification code");
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra. Vui lòng thử lại!']);
            exit();
        }
        
        error_log("[v0] New code stored, sending email...");
        
        // Send email
        $emailService = new EmailService();
        $emailResult = $emailService->sendVerificationEmail(
            $registrationData['eMail'],
            $registrationData['tenNguoiDung'],
            $verificationCode
        );
        
        if ($emailResult['success']) {
            error_log("[v0] Resend email sent successfully");
        } else {
            error_log("[v0] Failed to send resend email: " . $emailResult['message']);
        }
        
        echo json_encode($emailResult);
        exit();
    }

    public function resendVerificationCode() {
        // Keep this method for backward compatibility
        $this->resendCode();
    }

    public function showVerifyEmail() {
        if (!isset($_SESSION['pending_registration'])) {
            $_SESSION['error'] = 'Không tìm thấy thông tin đăng ký. Vui lòng đăng ký lại!';
            header('Location: ' . BASE_URL . '/register');
            exit();
        }
        
        // Process verification if POST request
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processVerification();
        }
        
        // Show verification form
        require_once __DIR__ . '/../views/auth/verify-email.php';
    }

    private function processVerification() {
        $code = trim($_POST['verification_code'] ?? '');
        
        if (empty($code)) {
            $_SESSION['error'] = 'Vui lòng nhập mã xác nhận!';
            return;
        }
        
        if (!isset($_SESSION['pending_registration'])) {
            $_SESSION['error'] = 'Phiên đăng ký đã hết hạn. Vui lòng đăng ký lại!';
            header('Location: ' . BASE_URL . '/register');
            exit();
        }
        
        $registrationData = $_SESSION['pending_registration'];
        
        // Verify code
        if (!$this->verificationModel->verifyCode($registrationData['eMail'], $code)) {
            $_SESSION['error'] = 'Mã xác nhận không đúng hoặc đã hết hạn!';
            return;
        }
        
        // Code is valid, proceed with registration
        $result = $this->userModel->register($registrationData);
        
        if ($result['success']) {
            // Clear session data
            unset($_SESSION['pending_registration']);
            unset($_SESSION['form_data']);
            
            $_SESSION['success'] = 'Đăng ký thành công! Vui lòng đăng nhập.';
            header('Location: ' . BASE_URL . '/login');
            exit();
        } else {
            $_SESSION['error'] = $result['message'];
        }
    }

    private function validateRegistration($data) {
        $errors = [];

        // Check required fields
        if (empty($data['tenNguoiDung'])) {
            $errors[] = 'Vui lòng nhập họ tên!';
        }

        if (empty($data['soDienThoai'])) {
            $errors[] = 'Vui lòng nhập số điện thoại!';
        } elseif (!preg_match('/^[0-9]{10,11}$/', $data['soDienThoai'])) {
            $errors[] = 'Số điện thoại không hợp lệ!';
        }

        if (empty($data['eMail'])) {
            $errors[] = 'Vui lòng nhập email!';
        } elseif (!filter_var($data['eMail'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ!';
        }

        if (empty($data['matKhau'])) {
            $errors[] = 'Vui lòng nhập mật khẩu!';
        } elseif (strlen($data['matKhau']) < 6) {
            $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự!';
        }

        if ($data['matKhau'] !== $data['confirmPassword']) {
            $errors[] = 'Mật khẩu xác nhận không khớp!';
        }

        if (empty($data['gioiTinh'])) {
            $errors[] = 'Vui lòng chọn giới tính!';
        }

        return $errors;
    }

    private function redirectBasedOnRole() {
        $role = $_SESSION['user_role'] ?? 4;
        
        switch ($role) {
            case 1: // Admin - redirect to admin dashboard
                header('Location: ' . BASE_URL . '/admin');
                break;
            case 2: // Support staff - redirect to dashboard
                header('Location: ' . BASE_URL . '/dashboard');
                break;
            case 3: // Driver - redirect to dashboard
                header('Location: ' . BASE_URL . '/dashboard');
                break;
            case 4: // Customer - redirect to home
            default:
                header('Location: ' . BASE_URL . '/');
                break;
        }
    }

    public function logout() {
        session_destroy();
        header('Location: ' . BASE_URL . '/login');
        exit();
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            $_SESSION['error'] = 'Vui lòng đăng nhập để tiếp tục!';
            header('Location: ' . BASE_URL . '/login');
            exit();
        }
    }
}
