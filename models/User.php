<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function login($sodienthoai, $password) {
        try {
            $sql = "SELECT nd.*, vt.tenVaiTro, tt.tenTrangThai 
                    FROM nguoidung nd 
                    LEFT JOIN vaitro vt ON nd.maVaiTro = vt.maVaiTro 
                    LEFT JOIN trangthaitaikhoan tt ON nd.maTrangThai = tt.maTrangThai 
                    WHERE nd.soDienThoai = ? AND nd.maTrangThai = 0";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$sodienthoai]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && $password === $user['matKhau']) { // Simple password check - in production use password_verify()
                return $user;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }

    public function register($data) {
        try {
            // Check if phone number already exists
            $checkPhone = "SELECT maNguoiDung FROM nguoidung WHERE soDienThoai = ?";
            $stmt = $this->db->prepare($checkPhone);
            $stmt->execute([$data['soDienThoai']]);
            
            if ($stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Số điện thoại đã được sử dụng!'
                ];
            }

            // Check if email already exists
            $checkEmail = "SELECT maNguoiDung FROM nguoidung WHERE eMail = ?";
            $stmt = $this->db->prepare($checkEmail);
            $stmt->execute([$data['eMail']]);
            
            if ($stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Email đã được sử dụng!'
                ];
            }

            // Insert new user
            $sql = "INSERT INTO nguoidung (maVaiTro, tenNguoiDung, soDienThoai, eMail, matKhau, gioiTinh, diaChi, ngayTao, maTrangThai) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 0)";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['maVaiTro'],
                $data['tenNguoiDung'],
                $data['soDienThoai'],
                $data['eMail'],
                $data['matKhau'], // In production, use password_hash()
                $data['gioiTinh'],
                $data['diaChi']
            ]);

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Đăng ký thành công!'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi đăng ký!'
                ];
            }

        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi đăng ký!'
            ];
        }
    }

    public function getUserById($id) {
        try {
            $sql = "SELECT nd.*, vt.tenVaiTro, tt.tenTrangThai 
                    FROM nguoidung nd 
                    LEFT JOIN vaitro vt ON nd.maVaiTro = vt.maVaiTro 
                    LEFT JOIN trangthaitaikhoan tt ON nd.maTrangThai = tt.maTrangThai 
                    WHERE nd.maNguoiDung = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get user error: " . $e->getMessage());
            return false;
        }
    }

    public function updateProfile($id, $data) {
        try {
            $sql = "UPDATE nguoidung SET 
                    tenNguoiDung = ?, 
                    eMail = ?, 
                    gioiTinh = ?, 
                    diaChi = ?, 
                    moTa = ? 
                    WHERE maNguoiDung = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['tenNguoiDung'],
                $data['eMail'],
                $data['gioiTinh'],
                $data['diaChi'],
                $data['moTa'] ?? '',
                $id
            ]);
        } catch (PDOException $e) {
            error_log("Update profile error: " . $e->getMessage());
            return false;
        }
    }
}
?>
