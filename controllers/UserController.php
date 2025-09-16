<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/config.php';

class UserController {
    private $userModel;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->userModel = new User();
    }

    // Check if user is admin
    private function checkAdminAccess() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] != 1) {
            $_SESSION['error'] = 'Bạn không có quyền truy cập chức năng này!';
            header('Location: ' . BASE_URL . '/login');
            exit();
        }
    }

    // Display users list
    public function index() {
        $this->checkAdminAccess();
        
        $search = $_GET['search'] ?? '';
        $role = $_GET['role'] ?? '';
        $status = $_GET['status'] ?? '';
        
        $users = $this->userModel->getAllUsers($search, $role, $status);
        $roles = $this->userModel->getAllRoles();
        $stats = $this->userModel->getUserStats();
        
        require_once __DIR__ . '/../views/users/index.php';
    }

    // Show create user form
    public function create() {
        $this->checkAdminAccess();
        
        $roles = $this->userModel->getAllRoles();
        require_once __DIR__ . '/../views/users/create.php';
    }

    // Handle create user form submission
    public function store() {
        $this->checkAdminAccess();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'maVaiTro' => $_POST['maVaiTro'] ?? '',
                'tenNguoiDung' => $_POST['tenNguoiDung'] ?? '',
                'soDienThoai' => $_POST['soDienThoai'] ?? '',
                'eMail' => $_POST['eMail'] ?? '',
                'matKhau' => $_POST['matKhau'] ?? '',
                'gioiTinh' => $_POST['gioiTinh'] ?? '',
                'diaChi' => $_POST['diaChi'] ?? '',
                'moTa' => $_POST['moTa'] ?? ''
            ];

            // Validate required fields
            if (empty($data['tenNguoiDung']) || empty($data['soDienThoai']) || 
                empty($data['eMail']) || empty($data['matKhau']) || empty($data['maVaiTro'])) {
                $_SESSION['error'] = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
                header('Location: ' . BASE_URL . '/users/create');
                exit();
            }

            // Validate phone number format
            if (!preg_match('/^[0-9]{10,11}$/', $data['soDienThoai'])) {
                $_SESSION['error'] = 'Số điện thoại không hợp lệ!';
                header('Location: ' . BASE_URL . '/users/create');
                exit();
            }

            // Validate email format
            if (!filter_var($data['eMail'], FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = 'Email không hợp lệ!';
                header('Location: ' . BASE_URL . '/users/create');
                exit();
            }

            $result = $this->userModel->createUser($data);
            
            if ($result['success']) {
                $_SESSION['success'] = $result['message'];
                header('Location: ' . BASE_URL . '/users');
            } else {
                $_SESSION['error'] = $result['message'];
                header('Location: ' . BASE_URL . '/users/create');
            }
            exit();
        }
    }

    // Show user details
    public function show($id) {
        $this->checkAdminAccess();
        
        $user = $this->userModel->getUserById($id);
        if (!$user) {
            $_SESSION['error'] = 'Không tìm thấy người dùng!';
            header('Location: ' . BASE_URL . '/users');
            exit();
        }
        
        require_once __DIR__ . '/../views/users/show.php';
    }

    // Show edit user form
    public function edit($id) {
        $this->checkAdminAccess();
        
        $user = $this->userModel->getUserById($id);
        if (!$user) {
            $_SESSION['error'] = 'Không tìm thấy người dùng!';
            header('Location: ' . BASE_URL . '/users');
            exit();
        }
        
        $roles = $this->userModel->getAllRoles();
        require_once __DIR__ . '/../views/users/edit.php';
    }

    // Handle edit user form submission
    public function update($id) {
        $this->checkAdminAccess();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'maVaiTro' => $_POST['maVaiTro'] ?? '',
                'tenNguoiDung' => $_POST['tenNguoiDung'] ?? '',
                'soDienThoai' => $_POST['soDienThoai'] ?? '',
                'eMail' => $_POST['eMail'] ?? '', // Optional for update
                'matKhau' => $_POST['matKhau'] ?? '',
                'gioiTinh' => $_POST['gioiTinh'] ?? '',
                'diaChi' => $_POST['diaChi'] ?? '',
                'moTa' => $_POST['moTa'] ?? ''
            ];

            // Validate required fields
            if (empty($data['tenNguoiDung']) || empty($data['soDienThoai']) || 
                empty($data['eMail']) || empty($data['maVaiTro'])) {
                $_SESSION['error'] = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
                header("Location: " . BASE_URL . "/users/edit/$id");
                exit();
            }

            // Validate phone number format
            if (!preg_match('/^[0-9]{10,11}$/', $data['soDienThoai'])) {
                $_SESSION['error'] = 'Số điện thoại không hợp lệ!';
                header("Location: " . BASE_URL . "/users/edit/$id");
                exit();
            }

            // Validate email format
            if (!filter_var($data['eMail'], FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = 'Email không hợp lệ!';
                header("Location: " . BASE_URL . "/users/edit/$id");
                exit();
            }

            $result = $this->userModel->updateUser($id, $data);
            
            if ($result['success']) {
                $_SESSION['success'] = $result['message'];
                header('Location: ' . BASE_URL . '/users');
            } else {
                $_SESSION['error'] = $result['message'];
                header("Location: " . BASE_URL . "/users/edit/$id");
            }
            exit();
        }
    }

    // Handle delete user (soft delete - lock account and clear password)
    public function delete($id) {
        $this->checkAdminAccess();
        
        // Prevent admin from deleting themselves
        if ($id == $_SESSION['user_id']) {
            $_SESSION['error'] = 'Không thể khóa tài khoản của chính mình!';
            header('Location: ' . BASE_URL . '/users');
            exit();
        }
        
        $result = $this->userModel->deleteUser($id);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['message'];
        }
        
        header('Location: ' . BASE_URL . '/users');
        exit();
    }

    // Handle restore user
    public function restore($id) {
        $this->checkAdminAccess();
        
        $result = $this->userModel->restoreUser($id);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['message'];
        }
        
        header('Location: ' . BASE_URL . '/users');
        exit();
    }

    // Export users to CSV
    public function export() {
        $this->checkAdminAccess();
        
        $users = $this->userModel->getAllUsers();
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="danh_sach_nguoi_dung_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // CSV headers
        fputcsv($output, [
            'Mã người dùng',
            'Tên người dùng', 
            'Số điện thoại',
            'Email',
            'Giới tính',
            'Địa chỉ',
            'Vai trò',
            'Trạng thái',
            'Ngày tạo'
        ]);
        
        // CSV data
        foreach ($users as $user) {
            fputcsv($output, [
                $user['maNguoiDung'],
                $user['tenNguoiDung'],
                $user['soDienThoai'],
                $user['eMail'],
                $user['gioiTinh'],
                $user['diaChi'],
                $user['tenVaiTro'],
                $user['tenTrangThai'],
                $user['ngayTao']
            ]);
        }
        
        fclose($output);
        exit();
    }
}
?>
