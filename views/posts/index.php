<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/config.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cộng đồng member - XeGoo</title>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>
    <!-- Fix CSS file name from posts.css to post.css -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/post.css">
</head>
<body>
    <div class="posts-wrapper">
        <!-- Header Section -->
        <div class="posts-header">
            <div class="header-content">
                <h1 class="header-title">XeGoo Member Hub | Diễn đàn thảo luận & chia sẻ hành trình</h1>
            </div>
        </div>

        <div class="posts-container">
            <!-- Main Feed -->
            <main class="posts-feed">
                <!-- Create Post Section -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="create-post-card">
                        <div class="create-post-header">
                            <div class="user-avatar-small"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?></div>
                            <textarea 
                                id="postContent" 
                                class="post-input"
                                placeholder="Chia sẻ trải nghiệm của bạn..."
                            ></textarea>
                        </div>
                        
                        <div class="create-post-footer">
                            <div class="image-upload-section">
                                <input 
                                    type="file" 
                                    id="imageInput" 
                                    multiple 
                                    accept="image/*"
                                    style="display: none;"
                                >
                                <button type="button" class="upload-btn" onclick="document.getElementById('imageInput').click();">
                                    <span>Thêm ảnh</span>
                                </button>
                                <div id="imagePreview" class="image-preview"></div>
                            </div>
                            <button type="button" class="submit-btn" onclick="submitPost()">Đăng bài</button>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="login-prompt-card">
                        <p class="login-prompt-text">
                            Hãy <a href="<?php echo BASE_URL; ?>/login" class="login-link">đăng nhập</a> để chia sẻ bài đăng
                        </p>
                    </div>
                <?php endif; ?>

                <!-- Posts List -->
                <div id="postsList" class="posts-list">
                    <?php if (empty($posts)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">📝</div>
                            <h3 class="empty-title">Chưa có bài đăng</h3>
                            <p class="empty-desc">Hãy là người đầu tiên chia sẻ trải nghiệm của bạn</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($posts as $post): ?>
                            <div class="post-card" id="post-<?php echo $post['ma_bai_dang']; ?>">
                                <!-- Post Header -->
                                <div class="post-card-header">
                                    <div class="post-user-info">
                                        <div class="user-avatar-lg"><?php echo strtoupper(substr($post['tenNguoiDung'], 0, 1)); ?></div>
                                        <div class="user-details">
                                            <h4 class="user-name"><?php echo htmlspecialchars($post['tenNguoiDung']); ?></h4>
                                            <p class="post-time"><?php echo date('d/m/Y H:i', strtotime($post['ngay_tao'])); ?></p>
                                        </div>
                                    </div>
                                    <span class="verified-badge">Đã xác nhận</span>
                                </div>

                                <!-- Post Content -->
                                <div class="post-content">
                                    <p><?php echo nl2br(htmlspecialchars($post['noi_dung'])); ?></p>
                                </div>

                                <!-- Post Images -->
                                <?php if (!empty($post['hinh_anh']) && is_array($post['hinh_anh'])): ?>
                                    <div class="post-images" data-image-count="<?php echo count($post['hinh_anh']); ?>">
                                        <?php foreach (array_slice($post['hinh_anh'], 0, 4) as $index => $image): ?>
                                            <div class="image-item" style="background-image: url('<?php echo BASE_URL . htmlspecialchars($image); ?>'); background-size: cover; background-position: center;"></div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Post Stats -->
                                <div class="post-stats">
                                    <?php if ($post['so_luong_cam_xuc'] > 0): ?>
                                        <span class="stat-items">
                                            <span class="stat-icon">👍</span>
                                            <span class="stat-count"><?php echo $post['so_luong_cam_xuc']; ?></span>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($post['so_luong_binh_luan'] > 0): ?>
                                        <span class="stat-items">
                                            <span class="stat-count"><?php echo $post['so_luong_binh_luan']; ?> bình luận</span>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Post Actions -->
                                <div class="post-actions">
                                    <div class="reaction-menu-wrapper">
                                        <button class="action-btn reaction-btn" onclick="toggleReactionMenu(<?php echo $post['ma_bai_dang']; ?>)">
                                            <span>👍 Thích</span>
                                        </button>
                                        <div class="reaction-menu" id="reaction-menu-<?php echo $post['ma_bai_dang']; ?>" style="display: none;">
                                            <button class="reaction-option" onclick="addReaction(<?php echo $post['ma_bai_dang']; ?>, 'Thích')">👍 Thích</button>
                                            <button class="reaction-option" onclick="addReaction(<?php echo $post['ma_bai_dang']; ?>, 'Yêu thích')">❤️ Yêu thích</button>
                                            <button class="reaction-option" onclick="addReaction(<?php echo $post['ma_bai_dang']; ?>, 'Haha')">😂 Haha</button>
                                            <button class="reaction-option" onclick="addReaction(<?php echo $post['ma_bai_dang']; ?>, 'Wow')">😮 Wow</button>
                                            <button class="reaction-option" onclick="addReaction(<?php echo $post['ma_bai_dang']; ?>, 'Buồn')">😢 Buồn</button>
                                            <button class="reaction-option" onclick="addReaction(<?php echo $post['ma_bai_dang']; ?>, 'Giận dữ')">😠 Giận dữ</button>
                                        </div>
                                    </div>
                                    <button class="action-btn" onclick="focusComment(<?php echo $post['ma_bai_dang']; ?>)">
                                        <span>Bình luận</span>
                                    </button>
                                </div>

                                <!-- Comments Section -->
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <div class="comment-form-wrapper">
                                        <form class="comment-form" onsubmit="addComment(event, <?php echo $post['ma_bai_dang']; ?>)">
                                            <input 
                                                type="text" 
                                                class="comment-input comment-input-<?php echo $post['ma_bai_dang']; ?>" 
                                                placeholder="Viết bình luận..."
                                                required
                                            >
                                            <button type="submit" class="comment-submit">Gửi</button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <div class="comment-form-wrapper">
                                        <form class="comment-form" onsubmit="showLoginAlert(event)">
                                            <input 
                                                type="text" 
                                                class="comment-input" 
                                                placeholder="Viết bình luận..."
                                                disabled
                                            >
                                            <button type="submit" class="comment-submit">Gửi</button>
                                        </form>
                                    </div>
                                <?php endif; ?>

                                <!-- Comments List -->
                                <div class="comments-list" id="comments-<?php echo $post['ma_bai_dang']; ?>">
                                    <?php if (empty($post['comments'])): ?>
                                        <p class="no-comments">Chưa có bình luận</p>
                                    <?php else: ?>
                                        <?php foreach ($post['comments'] as $comment): ?>
                                            <!-- Updated comment structure to include avatar and better layout -->
                                            <div class="comment-item">
                                                <div class="comment-avatar"><?php echo strtoupper(substr($comment['tenNguoiDung'], 0, 1)); ?></div>
                                                <div class="comment-content">
                                                    <div class="comment-author"><?php echo htmlspecialchars($comment['tenNguoiDung']); ?></div>
                                                    <div class="comment-text"><?php echo nl2br(htmlspecialchars($comment['noi_dung'])); ?></div>
                                                    <div class="comment-time"><?php echo date('d/m/Y H:i', strtotime($comment['ngay_tao'])); ?></div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php
                        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                        
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
            </main>

            <!-- Sidebar -->
            <aside class="posts-sidebar">
                <!-- Community Guidelines -->
                <div class="sidebar-card">
                    <h3 class="sidebar-title">Quy tắc cộng đồng</h3>
                    <ul class="guidelines-list">
                        <li class="guideline-item">Chia sẻ trải nghiệm tích cực</li>
                        <li class="guideline-item">Tôn trọng ý kiến của mọi người</li>
                        <li class="guideline-item">Không spam hoặc quảng cáo</li>
                        <li class="guideline-item">Bài đăng sẽ được duyệt trước</li>
                    </ul>
                </div>

                <!-- Community Stats -->
                <div class="sidebar-card">
                    <h3 class="sidebar-title">Thống kê</h3>
                    <div class="stats-grid">
                        <div class="stat-box">
                            <div class="stat-number"><?php echo $total_approved; ?></div>
                            <div class="stat-label">Bài đăng</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-number"><?php echo count(array_unique(array_column($posts, 'ma_nguoi_dung'))); ?></div>
                            <div class="stat-label">Thành viên</div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <!-- Image Preview Container -->
    <script>
        let selectedImages = [];

        document.getElementById('imageInput')?.addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            const maxImages = 4;
            
            // Limit to 4 images
            selectedImages = files.slice(0, maxImages);
            
            const preview = document.getElementById('imagePreview');
            preview.innerHTML = '';
            
            selectedImages.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('div');
                    img.className = 'preview-image';
                    img.style.backgroundImage = `url(${e.target.result})`;
                    
                    const removeBtn = document.createElement('button');
                    removeBtn.className = 'remove-image';
                    removeBtn.innerHTML = '×';
                    removeBtn.type = 'button';
                    removeBtn.onclick = function() {
                        selectedImages.splice(index, 1);
                        img.remove();
                    };
                    
                    img.appendChild(removeBtn);
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });

        function submitPost() {
            const content = document.getElementById('postContent').value.trim();
            
            if (!content) {
                alert('Vui lòng nhập nội dung bài đăng!');
                return;
            }

            const formData = new FormData();
            formData.append('noi_dung', content);
            
            // Add selected images
            selectedImages.forEach((file, index) => {
                formData.append('images[]', file);
            });

            fetch('<?php echo BASE_URL; ?>/api/posts/create', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Bài đăng của bạn đã được gửi để duyệt!');
                    document.getElementById('postContent').value = '';
                    document.getElementById('imageInput').value = '';
                    document.getElementById('imagePreview').innerHTML = '';
                    selectedImages = [];
                    location.reload();
                } else {
                    alert(data.message || 'Có lỗi xảy ra!');
                }
            });
        }

        function focusComment(postId) {
            const input = document.querySelector('.comment-input-' + postId);
            if (input) {
                input.focus();
                input.scrollIntoView({ behavior: 'smooth' });
            }
        }

        function addComment(e, postId) {
            e.preventDefault();
            
            const input = document.querySelector('.comment-input-' + postId);
            const content = input.value.trim();

            if (!content) return;

            fetch('<?php echo BASE_URL; ?>/api/posts/add-comment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'ma_bai_dang=' + postId + '&noi_dung=' + encodeURIComponent(content)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    input.value = '';
                    location.reload();
                } else {
                    alert(data.message || 'Có lỗi xảy ra!');
                }
            });
        }

        function showLoginAlert(e) {
            e.preventDefault();
            
            const existingAlert = document.getElementById('loginAlert');
            if (existingAlert) {
                existingAlert.remove();
            }
            
            const alert = document.createElement('div');
            alert.id = 'loginAlert';
            alert.className = 'alert-notification';
            alert.innerHTML = 'Hãy <a href="<?php echo BASE_URL; ?>/login">đăng nhập</a> để chia sẻ bài đăng';
            
            document.body.insertBefore(alert, document.body.firstChild);
            
            // Auto-remove alert after 5 seconds
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 5000);
            
            // Scroll to top to show alert
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function toggleReactionMenu(postId) {
            const menu = document.getElementById('reaction-menu-' + postId);
            if (menu.style.display === 'none') {
                menu.style.display = 'flex';
            } else {
                menu.style.display = 'none';
            }
        }

        function addReaction(postId, emotionType) {
            <?php if (isset($_SESSION['user_id'])): ?>
                fetch('<?php echo BASE_URL; ?>/api/posts/add-reaction', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'ma_bai_dang=' + postId + '&loai_cam_xuc=' + encodeURIComponent(emotionType)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Hide reaction menu after selection
                        const menu = document.getElementById('reaction-menu-' + postId);
                        if (menu) {
                            menu.style.display = 'none';
                        }
                        
                        // Reload to show updated reactions count
                        setTimeout(() => {
                            location.reload();
                        }, 300);
                    } else {
                        alert(data.message || 'Có lỗi xảy ra!');
                    }
                });
            <?php else: ?>
                showLoginAlert(new Event('submit'));
            <?php endif; ?>
        }
    </script>
</body>
  <?php require_once __DIR__ . '/../layouts/footer.php'; ?>
</html>
