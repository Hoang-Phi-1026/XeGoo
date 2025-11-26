<?php
/**
 * ID Encryption Helper - URL-safe encryption for IDs
 * Sử dụng Base64 encoding để mã hóa ID làm URL-safe
 */

class IDEncryptionHelper {
    
    // Khóa bảo mật 
    private static $encryptionKey = 'Hoang-Phi-XeGoo_2025!@#--DroneZ';
    
    /**
     * Mã hóa ID
     * @param int|string $id - ID cần mã hóa
     * @return string - ID đã mã hóa (URL-safe)
     */
    public static function encryptId($id) {
        try {
            // Thêm timestamp để tránh việc mã hóa cùng ID cho kết quả giống nhau
            $data = $id . '|' . time();
            
            // Sử dụng openssl_encrypt nếu có, nếu không dùng base64
            if (function_exists('openssl_encrypt')) {
                $encrypted = openssl_encrypt(
                    $data,
                    'AES-256-CBC',
                    hash('sha256', self::$encryptionKey, true),
                    0,
                    substr(hash('sha256', self::$encryptionKey), 0, 16)
                );
                return base64_encode($encrypted);
            } else {
                // Fallback: dùng base64 đơn giản với hashing
                return base64_encode($id . '|' . hash('sha256', $id . self::$encryptionKey));
            }
        } catch (Exception $e) {
            error_log("[v0] ID Encryption error: " . $e->getMessage());
            return '';
        }
    }
    
    /**
     * Giải mã ID
     * @param string $encryptedId - ID đã mã hóa
     * @return string|null - ID gốc hoặc null nếu giải mã thất bại
     */
    public static function decryptId($encryptedId) {
        try {
            if (function_exists('openssl_decrypt')) {
                $decrypted = openssl_decrypt(
                    base64_decode($encryptedId),
                    'AES-256-CBC',
                    hash('sha256', self::$encryptionKey, true),
                    0,
                    substr(hash('sha256', self::$encryptionKey), 0, 16)
                );
                
                if ($decrypted === false) {
                    return null;
                }
                
                // Tách ID từ timestamp
                $parts = explode('|', $decrypted);
                return isset($parts[0]) ? $parts[0] : null;
            } else {
                // Fallback: kiểm tra base64 đơn giản
                $decoded = base64_decode($encryptedId);
                $parts = explode('|', $decoded);
                
                if (isset($parts[0]) && isset($parts[1])) {
                    $id = $parts[0];
                    $hash = $parts[1];
                    
                    // Kiểm tra hash
                    if (hash('sha256', $id . self::$encryptionKey) === $hash) {
                        return $id;
                    }
                }
                return null;
            }
        } catch (Exception $e) {
            error_log("[v0] ID Decryption error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Kiểm tra ID có hợp lệ không (số nguyên dương)
     * @param mixed $id - ID cần kiểm tra
     * @return bool
     */
    public static function isValidId($id) {
        return is_numeric($id) && $id > 0;
    }
}
?>
