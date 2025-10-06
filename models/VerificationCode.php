<?php
require_once __DIR__ . '/../config/database.php';

class VerificationCode {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Generate a random 6-digit verification code
     */
    public static function generateCode() {
        return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Store verification code in database
     */
    public function storeCode($email, $code) {
        try {
            error_log("[v0] Attempting to store verification code for email: " . $email);
            error_log("[v0] Generated code: " . $code);
            
            // Delete any existing codes for this email
            $this->deleteCodeByEmail($email);
            
            // Insert new code with 10 minute expiration
            $sql = "INSERT INTO verification_codes (email, code, expires_at, created_at) 
                    VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE), NOW())";
            
            error_log("[v0] Executing SQL: " . $sql);
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$email, $code]);
            
            if ($result) {
                error_log("[v0] Verification code stored successfully");
            } else {
                error_log("[v0] Failed to store verification code");
            }
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("[v0] Store verification code error: " . $e->getMessage());
            error_log("[v0] Error code: " . $e->getCode());
            error_log("[v0] SQL State: " . $e->errorInfo[0]);
            return false;
        }
    }

    /**
     * Verify code for email
     */
    public function verifyCode($email, $code) {
        try {
            error_log("[v0] Verifying code for email: " . $email . ", code: " . $code);
            
            $sql = "SELECT * FROM verification_codes 
                    WHERE email = ? AND code = ? AND expires_at > NOW() AND is_used = 0";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email, $code]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                error_log("[v0] Code verified successfully");
                // Mark code as used
                $this->markCodeAsUsed($result['id']);
                return true;
            } else {
                error_log("[v0] Code verification failed - no matching code found");
            }
            
            return false;
            
        } catch (PDOException $e) {
            error_log("[v0] Verify code error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark code as used
     */
    private function markCodeAsUsed($id) {
        try {
            $sql = "UPDATE verification_codes SET is_used = 1 WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            error_log("[v0] Code marked as used: " . $id);
        } catch (PDOException $e) {
            error_log("[v0] Mark code as used error: " . $e->getMessage());
        }
    }

    /**
     * Delete code by email
     */
    public function deleteCodeByEmail($email) {
        try {
            $sql = "DELETE FROM verification_codes WHERE email = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$email]);
            error_log("[v0] Deleted existing codes for email: " . $email);
        } catch (PDOException $e) {
            error_log("[v0] Delete code error: " . $e->getMessage());
        }
    }

    /**
     * Clean up expired codes
     */
    public function cleanupExpiredCodes() {
        try {
            $sql = "DELETE FROM verification_codes WHERE expires_at < NOW()";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("[v0] Cleanup expired codes error: " . $e->getMessage());
        }
    }
}
?>
