<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/main.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/prices.css">

<div class="page-container">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-tags"></i>
            Quản lý giá vé
        </h1>
        <div class="page-actions">
            <a href="<?= BASE_URL ?>/prices/export" class="btn btn-success">
                <i class="fas fa-download"></i>
                Xuất Excel
            </a>
            <a href="<?= BASE_URL ?>/prices/create" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Thêm giá vé
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
                    <i class="fas fa-tags"></i>
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
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="stat-card stat-danger">
            <div class="stat-content">
                <div class="stat-info">
                    <h3 class="stat-title">Hết hạn</h3>
                    <div class="stat-number"><?= $stats['expired'] ?? 0 ?></div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
        </div>
        <div class="stat-card stat-info">
            <div class="stat-content">
                <div class="stat-info">
                    <h3 class="stat-title">Giá TB</h3>
                    <div class="stat-number"><?= number_format($stats['avg_price'] ?? 0, 0, ',', '.') ?></div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-money-bill-wave"></i>
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
                               placeholder="Tuyến đường, loại phương tiện..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="route">Tuyến đường:</label>
                        <select class="form-control" id="route" name="route">
                            <option value="">Tất cả tuyến</option>
                            <?php if (!empty($routes)): ?>
                                <?php foreach ($routes as $route): ?>
                                    <option value="<?= $route['maTuyenDuong'] ?>" 
                                            <?= ($_GET['route'] ?? '') == $route['maTuyenDuong'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($route['kyHieuTuyen']) ?> - <?= htmlspecialchars($route['diemDi']) ?> → <?= htmlspecialchars($route['diemDen']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="vehicle">Loại phương tiện:</label>
                        <select class="form-control" id="vehicle" name="vehicle">
                            <option value="">Tất cả loại phương tiện</option>
                            <?php if (!empty($vehicleTypes)): ?>
                                <?php foreach ($vehicleTypes as $vehicleType): ?>
                                    <option value="<?= $vehicleType['maLoaiPhuongTien'] ?>" 
                                            <?= ($_GET['vehicle'] ?? '') == $vehicleType['maLoaiPhuongTien'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($vehicleType['tenLoaiPhuongTien']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="status">Trạng thái:</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">Tất cả trạng thái</option>
                            <?php foreach ($statusOptions as $key => $value): ?>
                                <option value="<?= $key ?>" <?= ($_GET['status'] ?? '') === $key ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($value) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                                Tìm kiếm
                            </button>
                            <a href="<?= BASE_URL ?>/prices" class="btn btn-secondary">
                                <i class="fas fa-refresh"></i>
                                Đặt lại
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Prices Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Danh sách giá vé</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tuyến đường</th>
                            <th>Loại phương tiện</th>
                            <th>Loại chỗ</th>
                            <th>Loại vé</th>
                            <th>Giá vé</th>
                            <th>Giá KM</th>
                            <th>Thời gian</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($prices)): ?>
                            <tr>
                                <td colspan="10" class="text-center empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <p>Không có dữ liệu giá vé</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($prices as $price): ?>
                                <tr>
                                    <td><?= $price['maGiaVe'] ?? 'N/A' ?></td>
                                    <td>
                                        <div class="route-info">
                                            <strong><?= htmlspecialchars($price['kyHieuTuyen'] ?? 'N/A') ?></strong>
                                            <small><?= htmlspecialchars($price['diemDi'] ?? '') ?> → <?= htmlspecialchars($price['diemDen'] ?? '') ?></small>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($price['tenLoaiPhuongTien'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($price['loaiChoNgoi'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="badge badge-<?= ($price['tenLoaiVe'] ?? '') == 'Vé đặc biệt' ? 'danger' : (($price['tenLoaiVe'] ?? '') == 'Vé khuyến mãi' ? 'warning' : 'primary') ?>">
                                            <?= htmlspecialchars($price['tenLoaiVe'] ?? 'N/A') ?>
                                        </span>
                                    </td>
                                    <td class="price-cell">
                                        <strong><?= number_format($price['giaVe'] ?? 0, 0, ',', '.') ?> VNĐ</strong>
                                    </td>
                                    <td class="price-cell">
                                        <?php if (!empty($price['giaVeKhuyenMai'])): ?>
                                            <span class="text-success"><?= number_format($price['giaVeKhuyenMai'], 0, ',', '.') ?> VNĐ</span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small>
                                            <?= date('d/m/Y', strtotime($price['ngayBatDau'] ?? '')) ?><br>
                                            → <?= date('d/m/Y', strtotime($price['ngayKetThuc'] ?? '')) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if (($price['trangThai'] ?? '') == 'Hoạt động'): ?>
                                            <span class="badge badge-success">Hoạt động</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Hết hạn</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="<?= BASE_URL ?>/prices/<?= $price['maGiaVe'] ?>" 
                                               class="btn btn-info btn-sm" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/prices/<?= $price['maGiaVe'] ?>/edit" 
                                               class="btn btn-warning btn-sm" title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if (($price['trangThai'] ?? '') == 'Hoạt động'): ?>
                                                <button type="button" class="btn btn-danger btn-sm" 
                                                        title="Vô hiệu hóa"
                                                        onclick="confirmDelete(<?= $price['maGiaVe'] ?>, '<?= htmlspecialchars($price['kyHieuTuyen'] ?? '') ?>')">
                                                    <i class="fas fa-times"></i>
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
                <h3 class="modal-title">Xác nhận vô hiệu hóa</h3>
                <button type="button" class="modal-close" onclick="closeModal('deleteModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn vô hiệu hóa giá vé cho tuyến <strong id="deletePriceName"></strong>?</p>
                <p class="text-danger">Giá vé sẽ không còn được áp dụng cho các chuyến xe mới.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('deleteModal')">Hủy</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Vô hiệu hóa</a>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(priceId, routeName) {
    console.log('[v0] Opening delete modal for price:', priceId, routeName);
    document.getElementById('deletePriceName').textContent = routeName;
    document.getElementById('confirmDeleteBtn').href = '<?= BASE_URL ?>/prices/' + priceId + '/delete';
    showModal('deleteModal');
}

function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        setTimeout(() => {
            modal.classList.add('show');
        }, 10);
        
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
    
    console.log('[v0] Prices page JavaScript loaded successfully');
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
