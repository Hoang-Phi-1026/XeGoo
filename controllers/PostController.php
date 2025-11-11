<?php
require_once __DIR__ . '/../models/Post.php';

class PostController {
    private $postModel;
    private $uploadDir = __DIR__ . '/../public/uploads/posts';

    public function __construct() {
        $this->postModel = new Post();
        // Ensure upload directory exists
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function index() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $posts = $this->postModel->getApprovedPostsWithImages($limit, $offset);
        
        $total_approved = $this->postModel->countApprovedPosts();
        $total_pages = ceil($total_approved / $limit);
        
        foreach ($posts as &$post) {
            $post['reactions'] = $this->postModel->getReactionStats($post['ma_bai_dang']);
            $post['comments'] = $this->postModel->getComments($post['ma_bai_dang']);
        }
        unset($post);
        require_once __DIR__ . '/../views/posts/index.php';
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Bạn phải đăng nhập để đăng bài!']);
                return;
            }

            $noi_dung = isset($_POST['noi_dung']) ? trim($_POST['noi_dung']) : '';

            if (empty($noi_dung)) {
                echo json_encode(['success' => false, 'message' => 'Nội dung không được để trống!']);
                return;
            }

            $hinh_anh = [];
            if (!empty($_FILES['images']['name'][0])) {
                $uploaded_count = 0;
                $max_images = 4;
                
                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    if ($uploaded_count >= $max_images) break;
                    
                    if (empty($_FILES['images']['name'][$key])) continue;
                    
                    // Validate file
                    $file_type = strtolower(pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION));
                    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    
                    if (!in_array($file_type, $allowed_types)) {
                        continue;
                    }
                    
                    $file_size = $_FILES['images']['size'][$key];
                    $max_size = 5 * 1024 * 1024; // 5MB per image
                    
                    if ($file_size > $max_size) {
                        continue;
                    }
                    
                    // Generate unique filename
                    $filename = 'post_' . $_SESSION['user_id'] . '_' . time() . '_' . rand(1000, 9999) . '.' . $file_type;
                    $filepath = $this->uploadDir . '/' . $filename;
                    
                    if (move_uploaded_file($tmp_name, $filepath)) {
                        $hinh_anh[] = '/uploads/posts/' . $filename;
                        $uploaded_count++;
                    }
                }
            }

            $result = $this->postModel->createPostWithImages($_SESSION['user_id'], $noi_dung, $hinh_anh);
            echo json_encode($result);
        }
    }

    // Thêm bình luận
    public function addComment() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Bạn phải đăng nhập để bình luận!']);
                return;
            }

            $ma_bai_dang = isset($_POST['ma_bai_dang']) ? (int)$_POST['ma_bai_dang'] : 0;
            $noi_dung = isset($_POST['noi_dung']) ? trim($_POST['noi_dung']) : '';

            if (!$ma_bai_dang || empty($noi_dung)) {
                echo json_encode(['success' => false, 'message' => 'Thông tin không hợp lệ!']);
                return;
            }

            $result = $this->postModel->addComment($ma_bai_dang, $_SESSION['user_id'], $noi_dung);
            echo json_encode($result);
        }
    }

    // Thêm cảm xúc
    public function addReaction() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Bạn phải đăng nhập!']);
                return;
            }

            $ma_bai_dang = isset($_POST['ma_bai_dang']) ? (int)$_POST['ma_bai_dang'] : 0;
            $loai_cam_xuc = isset($_POST['loai_cam_xuc']) ? $_POST['loai_cam_xuc'] : 'Thích';

            if (!$ma_bai_dang) {
                echo json_encode(['success' => false, 'message' => 'Bài đăng không hợp lệ!']);
                return;
            }

            $result = $this->postModel->addReaction($ma_bai_dang, $_SESSION['user_id'], $loai_cam_xuc);
            echo json_encode($result);
        }
    }

    // Duyệt bài đăng (chỉ staff)
    public function approve() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 2) {
                echo json_encode(['success' => false, 'message' => 'Bạn không có quyền!']);
                return;
            }

            $ma_bai_dang = isset($_POST['ma_bai_dang']) ? (int)$_POST['ma_bai_dang'] : 0;
            if (!$ma_bai_dang) {
                echo json_encode(['success' => false, 'message' => 'Bài đăng không hợp lệ!']);
                return;
            }

            $result = $this->postModel->approvePost($ma_bai_dang, $_SESSION['user_id']);
            echo json_encode($result);
        }
    }

    // Từ chối bài đăng (chỉ staff)
    public function reject() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 2) {
                echo json_encode(['success' => false, 'message' => 'Bạn không có quyền!']);
                return;
            }

            $ma_bai_dang = isset($_POST['ma_bai_dang']) ? (int)$_POST['ma_bai_dang'] : 0;
            if (!$ma_bai_dang) {
                echo json_encode(['success' => false, 'message' => 'Bài đăng không hợp lệ!']);
                return;
            }

            $ghi_chu = isset($_POST['ghi_chu']) ? trim($_POST['ghi_chu']) : '';
            $result = $this->postModel->rejectPost($ma_bai_dang, $_SESSION['user_id'], $ghi_chu);
            echo json_encode($result);
        }
    }

    // Hiển thị trang quản lý bài đăng (chỉ staff)
    public function moderation() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 2) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $pending_posts = $this->postModel->getPendingPosts($limit, $offset);
        $total_pending = $this->postModel->countPendingPosts();
        $total_pages = ceil($total_pending / $limit);

        require_once __DIR__ . '/../views/posts/moderation.php';
    }
}
?>
