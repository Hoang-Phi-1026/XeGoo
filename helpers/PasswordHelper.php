<?php
/**
 * Password Helper - Modern Encryption using Bcrypt/Argon2
 * Sử dụng password_hash và password_verify chuẩn của PHP
 */

class PasswordHelper {
    
    /**
     * Tạo mã băm mật khẩu (Hash)
     * Sử dụng PASSWORD_DEFAULT để luôn dùng thuật toán mạnh nhất hiện có của PHP
     * * @param string $password - Mật khẩu dạng text
     * @return string - Chuỗi hash (thường dài 60 ký tự trở lên)
     */
    public static function hashPassword($password) {
        // PASSWORD_DEFAULT hiện tại là Bcrypt. Trong tương lai nếu PHP cập nhật
        // thuật toán mạnh hơn, code này sẽ tự động dùng thuật toán đó.
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Kiểm tra mật khẩu
     * * @param string $plainPassword - Mật khẩu người dùng nhập
     * @param string $hashedPassword - Chuỗi hash lấy từ database
     * @return bool - True nếu khớp, False nếu không khớp
     */
    public static function verifyPassword($plainPassword, $hashedPassword) {
        return password_verify($plainPassword, $hashedPassword);
    }

    /**
     * Kiểm tra xem mật khẩu có cần băm lại không (Rehash)
     * Cần thiết khi bạn nâng cấp thuật toán hoặc tăng độ khó (cost) sau này.
     * * @param string $hashedPassword - Chuỗi hash hiện tại
     * @return bool - True nếu cần update lại hash mới
     */
    public static function needsRehash($hashedPassword) {
        return password_needs_rehash($hashedPassword, PASSWORD_DEFAULT);
    }
}
?>