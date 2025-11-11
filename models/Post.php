<?php
require_once __DIR__ . '/../config/database.php';

class Post {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Lấy tất cả bài đăng đã duyệt (sắp xếp từ mới nhất)
    public function getApprovedPosts($limit = 20, $offset = 0) {
        try {
            $sql = "SELECT 
                        bd.*, 
                        nd.tenNguoiDung, 
                        nd.eMail,
                        (SELECT COUNT(*) FROM cam_xuc WHERE ma_bai_dang = bd.ma_bai_dang) as so_luong_cam_xuc,
                        (SELECT COUNT(*) FROM binh_luan WHERE ma_bai_dang = bd.ma_bai_dang) as so_luong_binh_luan
                    FROM bai_dang bd
                    JOIN nguoidung nd ON bd.ma_nguoi_dung = nd.maNguoiDung
                    WHERE bd.trang_thai = 'Đã duyệt'
                    ORDER BY bd.ngay_tao DESC
                    LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get approved posts error: " . $e->getMessage());
            return [];
        }
    }

    // Lấy bài đăng chờ duyệt
    public function getPendingPosts($limit = 20, $offset = 0) {
        try {
            $sql = "SELECT 
                        bd.*, 
                        nd.tenNguoiDung, 
                        nd.eMail,
                        nd.soDienThoai
                    FROM bai_dang bd
                    JOIN nguoidung nd ON bd.ma_nguoi_dung = nd.maNguoiDung
                    WHERE bd.trang_thai = 'Chờ duyệt'
                    ORDER BY bd.ngay_tao ASC
                    LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($posts as &$post) {
                if (!empty($post['hinh_anh'])) {
                    $post['hinh_anh'] = json_decode($post['hinh_anh'], true);
                    if (!is_array($post['hinh_anh'])) {
                        $post['hinh_anh'] = [];
                    }
                } else {
                    $post['hinh_anh'] = [];
                }
            }
            unset($post);
            
            return $posts;
        } catch (PDOException $e) {
            error_log("Get pending posts error: " . $e->getMessage());
            return [];
        }
    }

    // Tạo bài đăng mới
    public function createPost($ma_nguoi_dung, $noi_dung) {
        try {
            $sql = "INSERT INTO bai_dang (ma_nguoi_dung, noi_dung, trang_thai, ngay_tao)
                    VALUES (?, ?, 'Chờ duyệt', NOW())";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$ma_nguoi_dung, $noi_dung]);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Bài đăng của bạn đã được gửi để duyệt!',
                    'post_id' => $this->db->lastInsertId()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi tạo bài đăng!'
                ];
            }
        } catch (PDOException $e) {
            error_log("Create post error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo bài đăng!'
            ];
        }
    }

    // Tạo bài đăng mới với hình ảnh
    public function createPostWithImages($ma_nguoi_dung, $noi_dung, $hinh_anh = []) {
        try {
            $hinh_anh_json = !empty($hinh_anh) ? json_encode($hinh_anh) : NULL;
            
            $sql = "INSERT INTO bai_dang (ma_nguoi_dung, noi_dung, hinh_anh, trang_thai, ngay_tao)
                    VALUES (?, ?, ?, 'Chờ duyệt', NOW())";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$ma_nguoi_dung, $noi_dung, $hinh_anh_json]);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Bài đăng của bạn đã được gửi để duyệt!',
                    'post_id' => $this->db->lastInsertId()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi tạo bài đăng!'
                ];
            }
        } catch (PDOException $e) {
            error_log("Create post error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo bài đăng!'
            ];
        }
    }

    // Duyệt bài đăng
    public function approvePost($ma_bai_dang, $ma_nhan_vien) {
        try {
            $sql = "UPDATE bai_dang 
                    SET trang_thai = 'Đã duyệt', 
                        ngay_duyet = NOW(), 
                        ma_nhan_vien_duyet = ?
                    WHERE ma_bai_dang = ?";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$ma_nhan_vien, $ma_bai_dang]);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Bài đăng đã được duyệt!'
                ];
            }
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi duyệt bài đăng!'
            ];
        } catch (PDOException $e) {
            error_log("Approve post error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi duyệt bài đăng!'
            ];
        }
    }

    // Từ chối bài đăng
    public function rejectPost($ma_bai_dang, $ma_nhan_vien, $ghi_chu = '') {
        try {
            $sql = "UPDATE bai_dang 
                    SET trang_thai = 'Từ chối', 
                        ghi_chu_tu_choi = ?,
                        ngay_duyet = NOW(), 
                        ma_nhan_vien_duyet = ?
                    WHERE ma_bai_dang = ?";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$ghi_chu, $ma_nhan_vien, $ma_bai_dang]);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Bài đăng đã bị từ chối!'
                ];
            }
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi từ chối bài đăng!'
            ];
        } catch (PDOException $e) {
            error_log("Reject post error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi từ chối bài đăng!'
            ];
        }
    }

    // Thêm bình luận
    public function addComment($ma_bai_dang, $ma_nguoi_dung, $noi_dung) {
        try {
            $sql = "INSERT INTO binh_luan (ma_bai_dang, ma_nguoi_dung, noi_dung, ngay_tao)
                    VALUES (?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$ma_bai_dang, $ma_nguoi_dung, $noi_dung]);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Bình luận đã được thêm!',
                    'comment_id' => $this->db->lastInsertId()
                ];
            }
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi thêm bình luận!'
            ];
        } catch (PDOException $e) {
            error_log("Add comment error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi thêm bình luận!'
            ];
        }
    }

    // Lấy bình luận của bài đăng
    public function getComments($ma_bai_dang) {
        try {
            $sql = "SELECT 
                        bl.*, 
                        nd.tenNguoiDung
                    FROM binh_luan bl
                    JOIN nguoidung nd ON bl.ma_nguoi_dung = nd.maNguoiDung
                    WHERE bl.ma_bai_dang = ?
                    ORDER BY bl.ngay_tao DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_bai_dang]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get comments error: " . $e->getMessage());
            return [];
        }
    }

    // Thêm cảm xúc (reaction)
    public function addReaction($ma_bai_dang, $ma_nguoi_dung, $loai_cam_xuc) {
        try {
            $sql = "INSERT INTO cam_xuc (ma_bai_dang, ma_nguoi_dung, loai_cam_xuc, ngay_tao)
                    VALUES (?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE loai_cam_xuc = ?";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$ma_bai_dang, $ma_nguoi_dung, $loai_cam_xuc, $loai_cam_xuc]);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Cảm xúc đã được cập nhật!'
                ];
            }
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra!'
            ];
        } catch (PDOException $e) {
            error_log("Add reaction error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra!'
            ];
        }
    }

    // Lấy các cảm xúc của bài đăng (tính tổng theo loại)
    public function getReactionStats($ma_bai_dang) {
        try {
            $sql = "SELECT 
                        loai_cam_xuc, 
                        COUNT(*) as so_luong
                    FROM cam_xuc
                    WHERE ma_bai_dang = ?
                    GROUP BY loai_cam_xuc";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ma_bai_dang]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get reaction stats error: " . $e->getMessage());
            return [];
        }
    }

    // Xóa bài đăng
    public function deletePost($ma_bai_dang) {
        try {
            $sql = "DELETE FROM bai_dang WHERE ma_bai_dang = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$ma_bai_dang]);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Bài đăng đã bị xóa!'
                ];
            }
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa bài đăng!'
            ];
        } catch (PDOException $e) {
            error_log("Delete post error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa bài đăng!'
            ];
        }
    }

    // Đếm bài đăng chờ duyệt
    public function countPendingPosts() {
        try {
            $sql = "SELECT COUNT(*) as total FROM bai_dang WHERE trang_thai = 'Chờ duyệt'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Count pending posts error: " . $e->getMessage());
            return 0;
        }
    }

    // Đếm bài đăng đã duyệt
    public function countApprovedPosts() {
        try {
            $sql = "SELECT COUNT(*) as total FROM bai_dang WHERE trang_thai = 'Đã duyệt'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Count approved posts error: " . $e->getMessage());
            return 0;
        }
    }

    // Lấy tất cả bài đăng đã duyệt với hình ảnh đã phân tích
    public function getApprovedPostsWithImages($limit = 20, $offset = 0) {
        try {
            $sql = "SELECT 
                        bd.*, 
                        nd.tenNguoiDung, 
                        nd.eMail,
                        (SELECT COUNT(*) FROM cam_xuc WHERE ma_bai_dang = bd.ma_bai_dang) as so_luong_cam_xuc,
                        (SELECT COUNT(*) FROM binh_luan WHERE ma_bai_dang = bd.ma_bai_dang) as so_luong_binh_luan
                    FROM bai_dang bd
                    JOIN nguoidung nd ON bd.ma_nguoi_dung = nd.maNguoiDung
                    WHERE bd.trang_thai = 'Đã duyệt'
                    ORDER BY bd.ngay_tao DESC
                    LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
            // Phân tích JSON hình ảnh
            foreach ($posts as &$post) {
                if (!empty($post['hinh_anh'])) {
                    $post['hinh_anh'] = json_decode($post['hinh_anh'], true);
                } else {
                    $post['hinh_anh'] = [];
                }
            }
        
            return $posts;
        } catch (PDOException $e) {
            error_log("Get approved posts error: " . $e->getMessage());
            return [];
        }
    }
}
?>
