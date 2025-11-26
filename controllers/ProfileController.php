<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/PasswordHelper.php';

class ProfileController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function index() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        $stmt = $this->db->prepare("SELECT * FROM nguoidung WHERE maNguoiDung = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            session_destroy();
            header('Location: ' . BASE_URL . '/login?error=' . urlencode('Tài khoản không tồn tại'));
            exit;
        }
        
        include 'views/profile/index.php';
    }
    
    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/profile');
            exit;
        }
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        $fullname = trim($_POST['fullname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $gender = $_POST['gender'] ?? '';
        $description = trim($_POST['description'] ?? '');
        
        // Validation
        if (empty($fullname) || empty($email)) {
            header('Location: ' . BASE_URL . '/profile?error=' . urlencode('Vui lòng điền đầy đủ thông tin bắt buộc'));
            exit;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: ' . BASE_URL . '/profile?error=' . urlencode('Email không hợp lệ'));
            exit;
        }
        
        $stmt = $this->db->prepare("SELECT maNguoiDung FROM nguoidung WHERE eMail = ? AND maNguoiDung != ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            header('Location: ' . BASE_URL . '/profile?error=' . urlencode('Email đã được sử dụng bởi tài khoản khác'));
            exit;
        }
        
        // Check if phone exists for other users
        if (!empty($phone)) {
            $stmt = $this->db->prepare("SELECT maNguoiDung FROM nguoidung WHERE soDienThoai = ? AND maNguoiDung != ?");
            $stmt->execute([$phone, $_SESSION['user_id']]);
            if ($stmt->fetch()) {
                header('Location: ' . BASE_URL . '/profile?error=' . urlencode('Số điện thoại đã được sử dụng bởi tài khoản khác'));
                exit;
            }
        }
        
        try {
            $stmt = $this->db->prepare("UPDATE nguoidung SET tenNguoiDung = ?, eMail = ?, soDienThoai = ?, diaChi = ?, gioiTinh = ?, moTa = ? WHERE maNguoiDung = ?");
            $stmt->execute([$fullname, $email, $phone, $address, $gender, $description, $_SESSION['user_id']]);
            
            // Update session data
            $_SESSION['user_fullname'] = $fullname;
            $_SESSION['user_email'] = $email;
            
            header('Location: ' . BASE_URL . '/profile?success=' . urlencode('Cập nhật thông tin thành công'));
        } catch (PDOException $e) {
            header('Location: ' . BASE_URL . '/profile?error=' . urlencode('Có lỗi xảy ra khi cập nhật thông tin'));
        }
        exit;
    }
    
    public function changePassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/profile');
            exit;
        }
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            header('Location: ' . BASE_URL . '/profile?error=' . urlencode('Vui lòng điền đầy đủ thông tin'));
            exit;
        }
        
        if ($newPassword !== $confirmPassword) {
            header('Location: ' . BASE_URL . '/profile?error=' . urlencode('Mật khẩu xác nhận không khớp'));
            exit;
        }
        
        if (strlen($newPassword) < 6) {
            header('Location: ' . BASE_URL . '/profile?error=' . urlencode('Mật khẩu mới phải có ít nhất 6 ký tự'));
            exit;
        }
        
        $stmt = $this->db->prepare("SELECT matKhau FROM nguoidung WHERE maNguoiDung = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || !PasswordHelper::verifyPassword($currentPassword, $user['matKhau'])) {
            header('Location: ' . BASE_URL . '/profile?error=' . urlencode('Mật khẩu hiện tại không đúng'));
            exit;
        }
        
        try {
            $encodedPassword = PasswordHelper::encodePassword($newPassword);
            $stmt = $this->db->prepare("UPDATE nguoidung SET matKhau = ? WHERE maNguoiDung = ?");
            $stmt->execute([$encodedPassword, $_SESSION['user_id']]);
            
            header('Location: ' . BASE_URL . '/profile?success=' . urlencode('Đổi mật khẩu thành công'));
        } catch (PDOException $e) {
            header('Location: ' . BASE_URL . '/profile?error=' . urlencode('Có lỗi xảy ra khi đổi mật khẩu'));
        }
        exit;
    }
    
    public function uploadAvatar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/profile');
            exit;
        }
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            header('Location: ' . BASE_URL . '/profile?error=' . urlencode('Vui lòng chọn file ảnh'));
            exit;
        }
        
        $file = $_FILES['avatar'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        // Validate file type
        if (!in_array($file['type'], $allowedTypes)) {
            header('Location: ' . BASE_URL . '/profile?error=' . urlencode('Chỉ chấp nhận file ảnh (JPG, PNG, GIF)'));
            exit;
        }
        
        // Validate file size
        if ($file['size'] > $maxSize) {
            header('Location: ' . BASE_URL . '/profile?error=' . urlencode('File ảnh không được vượt quá 5MB'));
            exit;
        }
        
        // Create upload directory if not exists
        $uploadDir = 'public/uploads/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'avatar_' . $_SESSION['user_id'] . '_' . time() . '.' . $extension;
        $uploadPath = $uploadDir . $filename;
        
        try {
            $stmt = $this->db->prepare("SELECT avt FROM nguoidung WHERE maNguoiDung = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && $user['avt'] && file_exists($user['avt'])) {
                unlink($user['avt']);
            }
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                // Update database
                $stmt = $this->db->prepare("UPDATE nguoidung SET avt = ? WHERE maNguoiDung = ?");
                $stmt->execute([$uploadPath, $_SESSION['user_id']]);
                
                header('Location: ' . BASE_URL . '/profile?success=' . urlencode('Cập nhật ảnh đại diện thành công'));
            } else {
                header('Location: ' . BASE_URL . '/profile?error=' . urlencode('Có lỗi xảy ra khi tải ảnh lên'));
            }
        } catch (PDOException $e) {
            header('Location: ' . BASE_URL . '/profile?error=' . urlencode('Có lỗi xảy ra khi cập nhật ảnh đại diện'));
        }
        exit;
    }
}
?>
