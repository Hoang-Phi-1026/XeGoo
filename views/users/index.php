<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<!-- Updated page structure to match XeGoo design system instead of Bootstrap -->
<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/users.css">

<div class="page-container">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-users"></i>
            Quản lý người dùng
        </h1>
        <div class="page-actions">
            <a href="<?= BASE_URL ?>/users/export" class="btn btn-success">
                <i class="fas fa-download"></i>
                Xuất Excel
            </a>
            <a href="<?= BASE_URL ?>/users/create" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Thêm người dùng
            </a>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card stat-primary">
            <div class="stat-content">
                <div class="stat-info">
                    <h3 class="stat-title">Tổng số</h3>
                    <div class="stat-number"><?= $stats['total'] ?? 0 ?></div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        <div class="stat-card stat-success">
            <div class="stat-content">
                <div class="stat-info">
                    <h3 class="stat-title">Hoạt động</h3>
                    <div class="stat-number"><?= $stats['active'] ?? 0 ?></div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-user-check"></i>
                </div>
            </div>
        </div>
        <div class="stat-card stat-danger">
            <div class="stat-content">
                <div class="stat-info">
                    <h3 class="stat-title">Đã khóa</h3>
                    <div class="stat-number"><?= $stats['locked'] ?? 0 ?></div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-user-lock"></i>
                </div>
            </div>
        </div>
        <div class="stat-card stat-info">
            <div class="stat-content">
                <div class="stat-info">
                    <h3 class="stat-title">Khách hàng</h3>
                    <div class="stat-number"><?= $stats['customer'] ?? 0 ?></div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-user-friends"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Form -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Tìm kiếm và lọc</h3>
        </div>
        <div class="card-body">
            <form method="GET" class="filter-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="search">Tìm kiếm:</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Tên, SĐT, Email..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="role">Vai trò:</label>
                        <select class="form-control" id="role" name="role">
                            <option value="">Tất cả vai trò</option>
                            <?php if (!empty($roles)): ?>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['maVaiTro'] ?>" 
                                            <?= ($_GET['role'] ?? '') == $role['maVaiTro'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($role['tenVaiTro']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="status">Trạng thái:</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">Tất cả trạng thái</option>
                            <option value="0" <?= ($_GET['status'] ?? '') === '0' ? 'selected' : '' ?>>Hoạt động</option>
                            <option value="1" <?= ($_GET['status'] ?? '') === '1' ? 'selected' : '' ?>>Đã khóa</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                                Tìm kiếm
                            </button>
                            <a href="<?= BASE_URL ?>/users" class="btn btn-secondary">
                                <i class="fas fa-refresh"></i>
                                Đặt lại
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Danh sách người dùng</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên người dùng</th>
                            <th>Số điện thoại</th>
                            <th>Email</th>
                            <th>Vai trò</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="8" class="text-center empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <p>Không có dữ liệu người dùng</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= $user['maNguoiDung'] ?? 'N/A' ?></td>
                                    <td>
                                        <div class="user-info">
                                            <?php if (!empty($user['avt'])): ?>
                                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($user['avt']) ?>" 
                                                     alt="Avatar" class="user-avatar">
                                            <?php else: ?>
                                                <div class="user-avatar-placeholder">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            <?php endif; ?>
                                            <span><?= htmlspecialchars($user['tenNguoiDung'] ?? 'N/A') ?></span>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($user['soDienThoai'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($user['eMail'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="badge badge-<?= ($user['maVaiTro'] ?? 0) == 1 ? 'danger' : (($user['maVaiTro'] ?? 0) == 2 ? 'warning' : (($user['maVaiTro'] ?? 0) == 3 ? 'info' : 'secondary')) ?>">
                                            <?= htmlspecialchars($user['tenVaiTro'] ?? 'Không xác định') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (($user['maTrangThai'] ?? 1) == 0): ?>
                                            <span class="badge badge-success">Hoạt động</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Đã khóa</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= isset($user['ngayTao']) ? date('d/m/Y H:i', strtotime($user['ngayTao'])) : 'N/A' ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="<?= BASE_URL ?>/users/show/<?= $user['maNguoiDung'] ?>" 
                                               class="btn btn-info btn-sm" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/users/edit/<?= $user['maNguoiDung'] ?>" 
                                               class="btn btn-warning btn-sm" title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if (($user['maTrangThai'] ?? 1) == 0): ?>
                                                <?php if ($user['maNguoiDung'] != ($_SESSION['user']['maNguoiDung'] ?? 0)): ?>
                                                    <button type="button" class="btn btn-danger btn-sm" 
                                                            title="Khóa tài khoản"
                                                            onclick="confirmDelete(<?= $user['maNguoiDung'] ?>, '<?= htmlspecialchars($user['tenNguoiDung'] ?? '') ?>')">
                                                        <i class="fas fa-lock"></i>
                                                    </button>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-success btn-sm" 
                                                        title="Khôi phục tài khoản"
                                                        onclick="confirmRestore(<?= $user['maNguoiDung'] ?>, '<?= htmlspecialchars($user['tenNguoiDung'] ?? '') ?>')">
                                                    <i class="fas fa-unlock"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal" id="deleteModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Xác nhận khóa tài khoản</h3>
                <button type="button" class="modal-close" onclick="closeModal('deleteModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn khóa tài khoản của <strong id="deleteUserName"></strong>?</p>
                <p class="text-danger">Tài khoản sẽ bị khóa và mật khẩu sẽ bị xóa.</p>
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
                <h3 class="modal-title">Xác nhận khôi phục tài khoản</h3>
                <button type="button" class="modal-close" onclick="closeModal('restoreModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn khôi phục tài khoản của <strong id="restoreUserName"></strong>?</p>
                <p class="text-info">Tài khoản sẽ được kích hoạt lại nhưng cần đặt lại mật khẩu.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('restoreModal')">Hủy</button>
                <a href="#" id="confirmRestoreBtn" class="btn btn-success">Khôi phục</a>
            </div>
        </div>
    </div>
</div>

<script>
// Enhanced modal functionality
function confirmDelete(userId, userName) {
    console.log('[v0] Opening delete modal for user:', userId, userName);
    document.getElementById('deleteUserName').textContent = userName;
    document.getElementById('confirmDeleteBtn').href = '<?= BASE_URL ?>/users/delete/' + userId;
    showModal('deleteModal');
}

function confirmRestore(userId, userName) {
    console.log('[v0] Opening restore modal for user:', userId, userName);
    document.getElementById('restoreUserName').textContent = userName;
    document.getElementById('confirmRestoreBtn').href = '<?= BASE_URL ?>/users/restore/' + userId;
    showModal('restoreModal');
}

function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Add animation class
        setTimeout(() => {
            modal.classList.add('show');
        }, 10);
        
        // Focus trap
        const focusableElements = modal.querySelectorAll('button, a, input, select, textarea');
        if (focusableElements.length > 0) {
            focusableElements[0].focus();
        }
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }, 300);
    }
}

// Enhanced event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            closeModal(e.target.id);
        }
    });
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal[style*="flex"]');
            if (openModal) {
                closeModal(openModal.id);
            }
        }
    });
    
    // Enhanced table row hover effects
    const tableRows = document.querySelectorAll('.data-table tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Form validation enhancement
    const searchForm = document.querySelector('.filter-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const searchInput = this.querySelector('input[name="search"]');
            if (searchInput && searchInput.value.trim().length > 0 && searchInput.value.trim().length < 2) {
                e.preventDefault();
                alert('Từ khóa tìm kiếm phải có ít nhất 2 ký tự');
                searchInput.focus();
            }
        });
    }
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
});

// Loading state for action buttons
function setButtonLoading(button, loading = true) {
    if (loading) {
        button.disabled = true;
        const originalText = button.innerHTML;
        button.dataset.originalText = originalText;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
    } else {
        button.disabled = false;
        button.innerHTML = button.dataset.originalText || button.innerHTML;
    }
}

// Enhanced error handling
window.addEventListener('error', function(e) {
    console.error('[v0] JavaScript error:', e.error);
});

console.log('[v0] Users page JavaScript loaded successfully');
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
