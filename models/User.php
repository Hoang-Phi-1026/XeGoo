<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/PasswordHelper.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function loginWithIdentifier($identifier, $password) {
        try {
            // Check if identifier is email or phone
            $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);
            
            if ($isEmail) {
                $sql = "SELECT nd.*, vt.tenVaiTro, tt.tenTrangThai 
                        FROM nguoidung nd 
                        LEFT JOIN vaitro vt ON nd.maVaiTro = vt.maVaiTro 
                        LEFT JOIN trangthaitaikhoan tt ON nd.maTrangThai = tt.maTrangThai 
                        WHERE nd.eMail = ? AND nd.maTrangThai = 0";
            } else {
                $sql = "SELECT nd.*, vt.tenVaiTro, tt.tenTrangThai 
                        FROM nguoidung nd 
                        LEFT JOIN vaitro vt ON nd.maVaiTro = vt.maVaiTro 
                        LEFT JOIN trangthaitaikhoan tt ON nd.maTrangThai = tt.maTrangThai 
                        WHERE nd.soDienThoai = ? AND nd.maTrangThai = 0";
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$identifier]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && PasswordHelper::verifyPassword($password, $user['matKhau'])) {
                return $user;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
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

            if ($user && PasswordHelper::verifyPassword($password, $user['matKhau'])) {
                return $user;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }

    public function getUserByEmail($email) {
        try {
            $sql = "SELECT nd.*, vt.tenVaiTro, tt.tenTrangThai 
                    FROM nguoidung nd 
                    LEFT JOIN vaitro vt ON nd.maVaiTro = vt.maVaiTro 
                    LEFT JOIN trangthaitaikhoan tt ON nd.maTrangThai = tt.maTrangThai 
                    WHERE nd.eMail = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get user by email error: " . $e->getMessage());
            return false;
        }
    }

    public function updatePasswordByEmail($email, $newPassword) {
        try {
            $encodedPassword = PasswordHelper::hashPassword($newPassword);
            $sql = "UPDATE nguoidung SET matKhau = ? WHERE eMail = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$encodedPassword, $email]);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Cập nhật mật khẩu thành công!'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi cập nhật mật khẩu!'
                ];
            }
        } catch (PDOException $e) {
            error_log("Update password error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật mật khẩu!'
            ];
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

            $encodedPassword = PasswordHelper::hashPassword($data['matKhau']);

            // Insert new user
            $sql = "INSERT INTO nguoidung (maVaiTro, tenNguoiDung, soDienThoai, eMail, matKhau, gioiTinh, diaChi, ngayTao, maTrangThai) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 0)";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['maVaiTro'],
                $data['tenNguoiDung'],
                $data['soDienThoai'],
                $data['eMail'],
                $encodedPassword,
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

    public static function getDrivers() {
        try {
            $sql = "SELECT maNguoiDung, tenNguoiDung, soDienThoai, eMail, diaChi 
                    FROM nguoidung 
                    WHERE maVaiTro = 3 AND maTrangThai = 0 
                    ORDER BY tenNguoiDung";
            $db = Database::getInstance();
            $stmt = $db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get drivers error: " . $e->getMessage());
            return [];
        }
    }

    public static function getDriverById($id) {
        try {
            $sql = "SELECT maNguoiDung, tenNguoiDung, soDienThoai, eMail, diaChi 
                    FROM nguoidung 
                    WHERE maNguoiDung = ? AND maVaiTro = 3";
            $db = Database::getInstance();
            $stmt = $db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get driver by ID error: " . $e->getMessage());
            return null;
        }
    }

    public function getAllUsers($search = '', $role = '', $status = '') {
        try {
            $sql = "SELECT nd.*, vt.tenVaiTro, tt.tenTrangThai 
                    FROM nguoidung nd 
                    LEFT JOIN vaitro vt ON nd.maVaiTro = vt.maVaiTro 
                    LEFT JOIN trangthaitaikhoan tt ON nd.maTrangThai = tt.maTrangThai 
                    WHERE 1=1";
            
            $params = [];
            
            if (!empty($search)) {
                $sql .= " AND (nd.tenNguoiDung LIKE ? OR nd.soDienThoai LIKE ? OR nd.eMail LIKE ?)";
                $searchParam = "%$search%";
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }
            
            if (!empty($role)) {
                $sql .= " AND nd.maVaiTro = ?";
                $params[] = $role;
            }
            
            if ($status !== '') {
                $sql .= " AND nd.maTrangThai = ?";
                $params[] = $status;
            }
            
            $sql .= " ORDER BY nd.ngayTao DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get all users error: " . $e->getMessage());
            return [];
        }
    }

    public function getAllRoles() {
        try {
            $sql = "SELECT * FROM vaitro ORDER BY maVaiTro";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get roles error: " . $e->getMessage());
            return [];
        }
    }

    public function createUser($data) {
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

            $encodedPassword = PasswordHelper::hashPassword($data['matKhau']);

            // Insert new user
            $sql = "INSERT INTO nguoidung (maVaiTro, tenNguoiDung, soDienThoai, eMail, matKhau, gioiTinh, diaChi, moTa, ngayTao, maTrangThai) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 0)";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['maVaiTro'],
                $data['tenNguoiDung'],
                $data['soDienThoai'],
                $data['eMail'],
                $encodedPassword,
                $data['gioiTinh'],
                $data['diaChi'],
                $data['moTa'] ?? ''
            ]);

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Tạo người dùng thành công!'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi tạo người dùng!'
                ];
            }

        } catch (PDOException $e) {
            error_log("Create user error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo người dùng!'
            ];
        }
    }

    public function updateUser($id, $data) {
        try {
            // Check if phone number already exists (exclude current user)
            $checkPhone = "SELECT maNguoiDung FROM nguoidung WHERE soDienThoai = ? AND maNguoiDung != ?";
            $stmt = $this->db->prepare($checkPhone);
            $stmt->execute([$data['soDienThoai'], $id]);
            
            if ($stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Số điện thoại đã được sử dụng!'
                ];
            }

            // Check if email already exists (exclude current user)
            $checkEmail = "SELECT maNguoiDung FROM nguoidung WHERE eMail = ? AND maNguoiDung != ?";
            $stmt = $this->db->prepare($checkEmail);
            $stmt->execute([$data['eMail'], $id]);
            
            if ($stmt->fetch()) {
                return [
                    'success' => false,
                    'message' => 'Email đã được sử dụng!'
                ];
            }

            $sql = "UPDATE nguoidung SET 
                    maVaiTro = ?,
                    tenNguoiDung = ?, 
                    soDienThoai = ?,
                    eMail = ?, 
                    gioiTinh = ?, 
                    diaChi = ?, 
                    moTa = ?";
            
            $params = [
                $data['maVaiTro'],
                $data['tenNguoiDung'],
                $data['soDienThoai'],
                $data['eMail'],
                $data['gioiTinh'],
                $data['diaChi'],
                $data['moTa'] ?? ''
            ];
            
            if (!empty($data['matKhau'])) {
                $sql .= ", matKhau = ?";
                $params[] = PasswordHelper::hashPassword($data['matKhau']);
            }
            
            $sql .= " WHERE maNguoiDung = ?";
            $params[] = $id;
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Cập nhật người dùng thành công!'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi cập nhật!'
                ];
            }
        } catch (PDOException $e) {
            error_log("Update user error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật!'
            ];
        }
    }

    public function deleteUser($id) {
        try {
            // Set user status to locked (1) and clear password
            $sql = "UPDATE nguoidung SET maTrangThai = 1, matKhau = '' WHERE maNguoiDung = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$id]);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Khóa tài khoản thành công!'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi khóa tài khoản!'
                ];
            }
        } catch (PDOException $e) {
            error_log("Delete user error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi khóa tài khoản!'
            ];
        }
    }

    public function restoreUser($id) {
        try {
            // Restore user status to active (0)
            $sql = "UPDATE nguoidung SET maTrangThai = 0 WHERE maNguoiDung = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$id]);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Khôi phục tài khoản thành công!'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi khôi phục tài khoản!'
                ];
            }
        } catch (PDOException $e) {
            error_log("Restore user error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi khôi phục tài khoản!'
            ];
        }
    }

    public function getUserStats() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN maTrangThai = 0 THEN 1 ELSE 0 END) as active,
                        SUM(CASE WHEN maTrangThai = 1 THEN 1 ELSE 0 END) as locked,
                        SUM(CASE WHEN maVaiTro = 1 THEN 1 ELSE 0 END) as admin,
                        SUM(CASE WHEN maVaiTro = 2 THEN 1 ELSE 0 END) as support,
                        SUM(CASE WHEN maVaiTro = 3 THEN 1 ELSE 0 END) as driver,
                        SUM(CASE WHEN maVaiTro = 4 THEN 1 ELSE 0 END) as customer
                    FROM nguoidung";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get user stats error: " . $e->getMessage());
            return [];
        }
    }
}
?>
