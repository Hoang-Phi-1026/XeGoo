<?php
require_once __DIR__ . '/../config/database.php';

class Chat {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Create or get existing chat session
    public function createOrGetSession($maNguoiDung, $vaiTro) {
        try {
            // Check if user has an open chat session
            $sql = "SELECT * FROM chat_phien 
                    WHERE maNguoiDung = ? AND trangThai IN ('Chờ', 'Đang chat')
                    ORDER BY ngayTao DESC LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maNguoiDung]);
            $session = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($session) {
                return $session;
            }
            
            // Create new session
            $sql = "INSERT INTO chat_phien (maNguoiDung, tieuDe, trangThai, ngayTao, ngayCapNhat) 
                    VALUES (?, ?, 'Chờ', NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maNguoiDung, 'Hỗ trợ khách hàng']);
            
            $maPhien = $this->db->lastInsertId();
            
            return $this->getSessionById($maPhien);
        } catch (PDOException $e) {
            error_log("Create session error: " . $e->getMessage());
            return false;
        }
    }

    // Get session by ID
    public function getSessionById($maPhien) {
        try {
            $tableCheckSql = "SELECT 1 FROM information_schema.TABLES 
                            WHERE TABLE_SCHEMA = DATABASE() 
                            AND TABLE_NAME = 'nhanvien' LIMIT 1";
            $tableCheckStmt = $this->db->prepare($tableCheckSql);
            $tableCheckStmt->execute();
            $tableExists = $tableCheckStmt->rowCount() > 0;
            
            if ($tableExists) {
                $sql = "SELECT cp.*, nd.tenNguoiDung, nd.soDienThoai, nd.eMail, nv.tenNhanVien
                        FROM chat_phien cp
                        LEFT JOIN nguoidung nd ON cp.maNguoiDung = nd.maNguoiDung
                        LEFT JOIN nhanvien nv ON cp.maNhanVien = nv.maNhanVien
                        WHERE cp.maPhien = ?";
            } else {
                $sql = "SELECT cp.*, nd.tenNguoiDung, nd.soDienThoai, nd.eMail, NULL as tenNhanVien
                        FROM chat_phien cp
                        LEFT JOIN nguoidung nd ON cp.maNguoiDung = nd.maNguoiDung
                        WHERE cp.maPhien = ?";
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maPhien]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get session error: " . $e->getMessage());
            return false;
        }
    }

    // Get all pending sessions for staff
    public function getPendingSessions() {
        try {
            $sql = "SELECT cp.*, nd.tenNguoiDung, nd.soDienThoai, nd.eMail,
                    (SELECT COUNT(*) FROM chat_tinnhan WHERE maPhien = cp.maPhien AND daDoc = 0) as unreadCount
                    FROM chat_phien cp
                    LEFT JOIN nguoidung nd ON cp.maNguoiDung = nd.maNguoiDung
                    WHERE cp.trangThai IN ('Chờ', 'Đang chat')
                    ORDER BY cp.ngayCapNhat DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get pending sessions error: " . $e->getMessage());
            return [];
        }
    }

    // Get user role for a chat session
    public function getUserRoleInSession($maPhien) {
        try {
            $sql = "SELECT nd.maVaiTro, vt.tenVaiTro
                    FROM chat_phien cp
                    LEFT JOIN nguoidung nd ON cp.maNguoiDung = nd.maNguoiDung
                    LEFT JOIN vaitro vt ON nd.maVaiTro = vt.maVaiTro
                    WHERE cp.maPhien = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maPhien]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get user role error: " . $e->getMessage());
            return false;
        }
    }

    // Send message
    public function sendMessage($maPhien, $nguoiGui, $vaiTroNguoiGui, $noiDung, $loaiTinNhan = 'Text', $duongDanFile = null) {
        try {
            $sql = "INSERT INTO chat_tinnhan (maPhien, nguoiGui, vaiTroNguoiGui, noiDung, loaiTinNhan, duongDanFile, daDoc, ngayTao)
                    VALUES (?, ?, ?, ?, ?, ?, 0, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$maPhien, $nguoiGui, $vaiTroNguoiGui, $noiDung, $loaiTinNhan, $duongDanFile]);
            
            if ($result) {
                // Update session status and timestamp
                $this->updateSessionStatus($maPhien, 'Đang chat');
                return $this->db->lastInsertId();
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Send message error: " . $e->getMessage());
            return false;
        }
    }

    // Get messages for a session
    public function getMessages($maPhien, $limit = 50, $offset = 0) {
        try {
            $limit = intval($limit);
            $offset = intval($offset);
            
            // Check if nhanvien table exists first
            $tableCheckSql = "SELECT 1 FROM information_schema.TABLES 
                            WHERE TABLE_SCHEMA = DATABASE() 
                            AND TABLE_NAME = 'nhanvien' LIMIT 1";
            $tableCheckStmt = $this->db->prepare($tableCheckSql);
            $tableCheckStmt->execute();
            $tableExists = $tableCheckStmt->rowCount() > 0;
            
            if ($tableExists) {
                $sql = "SELECT ct.*, nd.tenNguoiDung, nv.tenNhanVien
                        FROM chat_tinnhan ct
                        LEFT JOIN nguoidung nd ON ct.nguoiGui = nd.maNguoiDung AND ct.vaiTroNguoiGui IN ('Khách hàng', 'Tài xế')
                        LEFT JOIN nhanvien nv ON ct.nguoiGui = nv.maNhanVien AND ct.vaiTroNguoiGui = 'Nhân viên'
                        WHERE ct.maPhien = ?
                        ORDER BY ct.ngayTao ASC
                        LIMIT " . $limit . " OFFSET " . $offset;
            } else {
                // Fallback query without nhanvien table
                $sql = "SELECT ct.*, nd.tenNguoiDung, NULL as tenNhanVien
                        FROM chat_tinnhan ct
                        LEFT JOIN nguoidung nd ON ct.nguoiGui = nd.maNguoiDung
                        WHERE ct.maPhien = ?
                        ORDER BY ct.ngayTao ASC
                        LIMIT " . $limit . " OFFSET " . $offset;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maPhien]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get messages error: " . $e->getMessage());
            return [];
        }
    }

    // Get unread messages count
    public function getUnreadCount($maPhien) {
        try {
            $sql = "SELECT COUNT(*) as count FROM chat_tinnhan WHERE maPhien = ? AND daDoc = 0";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maPhien]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            error_log("Get unread count error: " . $e->getMessage());
            return 0;
        }
    }

    // Mark messages as read
    public function markMessagesAsRead($maPhien) {
        try {
            $sql = "UPDATE chat_tinnhan SET daDoc = 1 WHERE maPhien = ? AND daDoc = 0";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$maPhien]);
        } catch (PDOException $e) {
            error_log("Mark as read error: " . $e->getMessage());
            return false;
        }
    }

    // Assign staff to session
    public function assignStaffToSession($maPhien, $maNhanVien) {
        try {
            $sql = "UPDATE chat_phien SET maNhanVien = ?, trangThai = 'Đang chat', ngayCapNhat = NOW() 
                    WHERE maPhien = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$maNhanVien, $maPhien]);
        } catch (PDOException $e) {
            error_log("Assign staff error: " . $e->getMessage());
            return false;
        }
    }

    // Update session status
    public function updateSessionStatus($maPhien, $trangThai) {
        try {
            $sql = "UPDATE chat_phien SET trangThai = ?, ngayCapNhat = NOW() WHERE maPhien = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$trangThai, $maPhien]);
        } catch (PDOException $e) {
            error_log("Update session status error: " . $e->getMessage());
            return false;
        }
    }

    // Close session
    public function closeSession($maPhien, $danhGia = null) {
        try {
            $sql = "UPDATE chat_phien SET trangThai = 'Đã đóng', ngayDong = NOW()";
            $params = [];
            
            if ($danhGia !== null) {
                $sql .= ", danhGia = ?";
                $params[] = $danhGia;
            }
            
            $sql .= " WHERE maPhien = ?";
            $params[] = $maPhien;
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Close session error: " . $e->getMessage());
            return false;
        }
    }

    // Get staff sessions
    public function getStaffSessions($maNhanVien) {
        try {
            $sql = "SELECT cp.*, nd.tenNguoiDung, nd.soDienThoai, nd.eMail,
                    (SELECT COUNT(*) FROM chat_tinnhan WHERE maPhien = cp.maPhien AND daDoc = 0) as unreadCount
                    FROM chat_phien cp
                    LEFT JOIN nguoidung nd ON cp.maNguoiDung = nd.maNguoiDung
                    WHERE cp.maNhanVien = ? AND cp.trangThai IN ('Chờ', 'Đang chat')
                    ORDER BY cp.ngayCapNhat DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$maNhanVien]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get staff sessions error: " . $e->getMessage());
            return [];
        }
    }
}
