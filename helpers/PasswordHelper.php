<?php
/**
 * Password Helper - MD5 encryption for user passwords
 * This helper provides functions to encode and compare passwords using MD5
 */

class PasswordHelper {
    /**
     * Encode password using MD5
     * @param string $password - Plain text password
     * @return string - MD5 hashed password
     */
    public static function encodePassword($password) {
        return md5($password);
    }

    /**
     * Verify password against hash
     * @param string $plainPassword - Plain text password to verify
     * @param string $hashedPassword - MD5 hashed password from database
     * @return bool - True if password matches, false otherwise
     */
    public static function verifyPassword($plainPassword, $hashedPassword) {
        return md5($plainPassword) === $hashedPassword;
    }
}
?>
