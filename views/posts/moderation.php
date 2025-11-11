<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Post.php';

// Check staff permission
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 2) {
    header('Location: ' . BASE_URL . '/login');
    exit;
}

// The controller already passes $pending_posts and $total_pages
if (empty($pending_posts)) {
    $pending_posts = [];
}
if (empty($total_pending)) {
    $total_pending = 0;
}
if (empty($total_pages)) {
    $total_pages = 1;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiểm duyệt bài đăng - XeGoo</title>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>
    <!-- Fixed CSS file name from posts.css to post.css -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/post.css">
</head>
<body>
    <div class="moderation-wrapper">
        <!-- Header -->
        <div class="moderation-header">
            <div class="header-content">
                <h1 class="header-title">Kiểm duyệt bài đăng</h1>
                <p class="header-subtitle">Duyệt các bài đăng từ cộng đồng</p>
            </div>
            <div class="pending-badge"><?php echo $total_pending; ?> bài chờ duyệt</div>
        </div>

        <div class="moderation-container">
            <?php if (empty($pending_posts)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <h3 class="empty-title">Không có bài đăng chờ duyệt</h3>
                    <p class="empty-desc">Tất cả bài đăng đã được kiểm duyệt</p>
                </div>
            <?php else: ?>
                <div class="moderation-posts">
                    <?php foreach ($pending_posts as $post): ?>
                        <div class="moderation-card" id="post-<?php echo $post['ma_bai_dang']; ?>">
                            <!-- Header -->
                            <div class="moderation-card-header">
                                <div class="moderation-user">
                                    <div class="user-avatar-lg"><?php echo strtoupper(substr($post['tenNguoiDung'], 0, 1)); ?></div>
                                    <div class="user-meta">
                                        <h4 class="user-name"><?php echo htmlspecialchars($post['tenNguoiDung']); ?></h4>
                                        <p class="user-email"><?php echo htmlspecialchars($post['eMail']); ?></p>
                                        <p class="user-phone"><?php echo htmlspecialchars($post['soDienThoai']); ?></p>
                                    </div>
                                </div>
                                <div class="submission-date"><?php echo date('d/m/Y H:i', strtotime($post['ngay_tao'])); ?></div>
                            </div>

                            <!-- Content -->
                            <div class="moderation-content">
                                <p><?php echo nl2br(htmlspecialchars($post['noi_dung'])); ?></p>
                            </div>

                            <!-- Images if exists -->
                            <?php if (!empty($post['hinh_anh']) && is_array($post['hinh_anh'])): ?>
                                <div class="moderation-images" data-image-count="<?php echo count($post['hinh_anh']); ?>">
                                    <?php foreach (array_slice($post['hinh_anh'], 0, 4) as $image): ?>
                                        <div class="image-item" style="background-image: url('<?php echo BASE_URL . htmlspecialchars($image); ?>'); background-size: cover; background-position: center;"></div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Actions -->
                            <div class="moderation-actions">
                                <button class="action-approve" onclick="approvePost(<?php echo $post['ma_bai_dang']; ?>)">
                                    Duyệt
                                </button>
                                <button class="action-reject" onclick="openRejectionModal(<?php echo $post['ma_bai_dang']; ?>)">
                                    Từ chối
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php
                        if ($page > 1) {
                            echo '<a href="?page=' . ($page - 1) . '" class="page-link">Trang trước</a>';
                        }
                        
                        for ($i = 1; $i <= $total_pages; $i++) {
                            if ($i == $page) {
                                echo '<span class="page-active">' . $i . '</span>';
                            } else {
                                echo '<a href="?page=' . $i . '" class="page-link">' . $i . '</a>';
                            }
                        }
                        
                        if ($page < $total_pages) {
                            echo '<a href="?page=' . ($page + 1) . '" class="page-link">Trang sau</a>';
                        }
                        ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Rejection Modal -->
    <div class="modal-overlay" id="rejectionModal">
        <div class="modal-content">
            <h2 class="modal-title">Lý do từ chối</h2>
            <form class="modal-form" id="rejectionForm">
                <textarea 
                    id="rejectionReason" 
                    class="modal-textarea"
                    placeholder="Nhập lý do từ chối bài đăng..."
                    required
                ></textarea>
                <div class="modal-actions">
                    <button type="button" class="modal-btn modal-cancel" onclick="closeRejectionModal()">Hủy</button>
                    <button type="button" class="modal-btn modal-confirm" onclick="confirmRejection()">Xác nhận</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentPostId = null;

        function approvePost(postId) {
            if (confirm('Bạn có chắc muốn duyệt bài đăng này?')) {
                fetch('<?php echo BASE_URL; ?>/api/posts/approve', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'ma_bai_dang=' + postId
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Bài đăng đã được duyệt!');
                        document.getElementById('post-' + postId).style.opacity = '0.5';
                        setTimeout(() => location.reload(), 500);
                    } else {
                        alert(data.message || 'Có lỗi xảy ra!');
                    }
                });
            }
        }

        function openRejectionModal(postId) {
            currentPostId = postId;
            document.getElementById('rejectionModal').classList.add('active');
        }

        function closeRejectionModal() {
            document.getElementById('rejectionModal').classList.remove('active');
            document.getElementById('rejectionForm').reset();
            currentPostId = null;
        }

        function confirmRejection() {
            const reason = document.getElementById('rejectionReason').value.trim();
            if (!reason) {
                alert('Vui lòng nhập lý do từ chối!');
                return;
            }

            fetch('<?php echo BASE_URL; ?>/api/posts/reject', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'ma_bai_dang=' + currentPostId + '&ghi_chu=' + encodeURIComponent(reason)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Bài đăng đã bị từ chối!');
                    document.getElementById('post-' + currentPostId).style.opacity = '0.5';
                    setTimeout(() => location.reload(), 500);
                } else {
                    alert(data.message || 'Có lỗi xảy ra!');
                }
            });
        }

        // Close modal when clicking outside
        document.getElementById('rejectionModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRejectionModal();
            }
        });
    </script>
</body>
</html>
