<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<!-- Added users.css link for consistent styling -->
<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/users.css">

<div class="page-container">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-user"></i>Chi tiết người dùng
        </h1>
        <div class="page-actions">
            <a href="<?= BASE_URL ?>/users" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>Quay lại danh sách
            </a>
        </div>
    </div>

    <?php if (isset($user) && !empty($user)): ?>
    <div class="user-detail-container">
        <div class="user-detail-card">
            <div class="user-profile-section">
                <div class="user-avatar-section">
                    <!-- Added null coalescing operator to prevent array offset errors -->
                    <?php if (!empty($user['avt'] ?? '')): ?>
                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($user['avt']) ?>" 
                             alt="Avatar" class="user-detail-avatar">
                    <?php else: ?>
                        <div class="user-detail-avatar-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                    
                    <h2 class="user-name"><?= htmlspecialchars($user['tenNguoiDung'] ?? 'N/A') ?></h2>
                    <p class="user-role"><?= htmlspecialchars($user['tenVaiTro'] ?? 'N/A') ?></p>
                    
                    <!-- Fixed array access with null coalescing -->
                    <?php if (($user['maTrangThai'] ?? 1) == 0): ?>
                        <span class="badge badge-success">Hoạt động</span>
                    <?php else: ?>
                        <span class="badge badge-danger">Đã khóa</span>
                    <?php endif; ?>
                </div>
                
                <div class="user-info-section">
                    <div class="info-table">
                        <div class="info-row">
                            <span class="info-label">Mã người dùng:</span>
                            <span class="info-value"><?= htmlspecialchars($user['maNguoiDung'] ?? 'N/A') ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Số điện thoại:</span>
                            <span class="info-value"><?= htmlspecialchars($user['soDienThoai'] ?? 'N/A') ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Email:</span>
                            <span class="info-value"><?= htmlspecialchars($user['eMail'] ?? 'N/A') ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Giới tính:</span>
                            <span class="info-value"><?= htmlspecialchars($user['gioiTinh'] ?? 'Chưa cập nhật') ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Địa chỉ:</span>
                            <span class="info-value"><?= htmlspecialchars($user['diaChi'] ?? 'Chưa cập nhật') ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Mô tả:</span>
                            <span class="info-value"><?= htmlspecialchars($user['moTa'] ?? 'Chưa có mô tả') ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Ngày tạo:</span>
                            <span class="info-value">
                                <?= isset($user['ngayTao']) ? date('d/m/Y H:i:s', strtotime($user['ngayTao'])) : 'N/A' ?>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Trạng thái:</span>
                            <span class="info-value"><?= htmlspecialchars($user['tenTrangThai'] ?? 'N/A') ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="user-actions">
                <a href="<?= BASE_URL ?>/users/edit/<?= $user['maNguoiDung'] ?? '' ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i>Chỉnh sửa
                </a>
                <!-- Added session check with null coalescing -->
                <?php if (($user['maTrangThai'] ?? 1) == 0): ?>
                    <?php if (($user['maNguoiDung'] ?? 0) != ($_SESSION['user']['maNguoiDung'] ?? 0)): ?>
                        <button type="button" class="btn btn-danger" 
                                onclick="confirmDelete(<?= $user['maNguoiDung'] ?? 0 ?>, '<?= htmlspecialchars($user['tenNguoiDung'] ?? '') ?>')">
                            <i class="fas fa-lock"></i>Khóa tài khoản
                        </button>
                    <?php endif; ?>
                <?php else: ?>
                    <button type="button" class="btn btn-success" 
                            onclick="confirmRestore(<?= $user['maNguoiDung'] ?? 0 ?>, '<?= htmlspecialchars($user['tenNguoiDung'] ?? '') ?>')">
                        <i class="fas fa-unlock"></i>Khôi phục
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-user-slash"></i>
        <h3>Không tìm thấy người dùng</h3>
        <p>Người dùng không tồn tại hoặc đã bị xóa.</p>
    </div>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal" id="deleteModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận khóa tài khoản</h5>
                <button type="button" class="modal-close" onclick="closeModal('deleteModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn khóa tài khoản của <strong id="deleteUserName"></strong>?</p>
                <p class="text-danger"><small>Tài khoản sẽ bị khóa và mật khẩu sẽ bị xóa.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('deleteModal')">Hủy</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Khóa tài khoản</a>
            </div>
        </div>
    </div>
</div>

<!-- Restore Confirmation Modal -->
<div class="modal" id="restoreModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận khôi phục tài khoản</h5>
                <button type="button" class="modal-close" onclick="closeModal('restoreModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn khôi phục tài khoản của <strong id="restoreUserName"></strong>?</p>
                <p class="text-info"><small>Tài khoản sẽ được kích hoạt lại nhưng cần đặt lại mật khẩu.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('restoreModal')">Hủy</button>
                <a href="#" id="confirmRestoreBtn" class="btn btn-success">Khôi phục</a>
            </div>
        </div>
    </div>
</div>

<!-- Improved JavaScript with better error handling and animations -->
<script>
function confirmDelete(userId, userName) {
    if (!userId || !userName) {
        console.error('[v0] Missing userId or userName for delete confirmation');
        return;
    }
    
    document.getElementById('deleteUserName').textContent = userName;
    document.getElementById('confirmDeleteBtn').href = '<?= BASE_URL ?>/users/delete/' + userId;
    showModal('deleteModal');
}

function confirmRestore(userId, userName) {
    if (!userId || !userName) {
        console.error('[v0] Missing userId or userName for restore confirmation');
        return;
    }
    
    document.getElementById('restoreUserName').textContent = userName;
    document.getElementById('confirmRestoreBtn').href = '<?= BASE_URL ?>/users/restore/' + userId;
    showModal('restoreModal');
}

function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.style.opacity = '1';
        }, 10);
        
        // Close modal when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal(modalId);
            }
        });
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.style.display = 'none';
        }, 200);
    }
}

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal('deleteModal');
        closeModal('restoreModal');
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
