<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->userModel = new User();
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
        $sodienthoai = trim($_POST['sodienthoai'] ?? '');
        $password = trim($_POST['password'] ?? '');

        // Validate input
        if (empty($sodienthoai) || empty($password)) {
            $_SESSION['error'] = 'Vui lòng nhập đầy đủ thông tin!';
            return;
        }

        // Attempt login
        $user = $this->userModel->login($sodienthoai, $password);
        
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
            $_SESSION['error'] = 'Số điện thoại hoặc mật khẩu không chính xác!';
        }
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

        // Attempt registration
        $result = $this->userModel->register($data);
        
        if ($result['success']) {
            $_SESSION['success'] = 'Đăng ký thành công! Vui lòng đăng nhập.';
            unset($_SESSION['form_data']); // Clear form data
            
            // Redirect to login page
            header('Location: ' . BASE_URL . '/login');
            exit();
        } else {
            $_SESSION['error'] = $result['message'];
            $_SESSION['form_data'] = $data;
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
?>
