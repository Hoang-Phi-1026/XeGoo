<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/main.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/promotional-codes.css">

<div class="page-container">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-tags"></i>
            Quản lý khuyến mãi
        </h1>
        <div class="page-actions">
            <button class="btn btn-primary" onclick="scrollToForm()">
                <i class="fas fa-plus"></i>
                Tạo mã khuyến mãi
            </button>
        </div>
    </div>

    <!-- Create Form Section -->
    <div class="card form-card" id="createFormSection">
        <div class="card-header">
            <h3 class="card-title">Tạo mã khuyến mãi mới</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/promotional-codes/store" class="form-group-container">
                <!-- Removed maKhuyenMai input field -->

                <div class="form-row">
                    <div class="form-group">
                        <label for="tenKhuyenMai">Tên khuyến mãi <span class="required">*</span></label>
                        <input type="text" id="tenKhuyenMai" name="tenKhuyenMai" class="form-control" 
                               placeholder="VD: Giảm giá 20% cho khách hàng mới" 
                               value="<?= $_SESSION['form_data']['tenKhuyenMai'] ?? '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="loai">Loại khuyến mãi <span class="required">*</span></label>
                        <select id="loai" name="loai" class="form-control" required onchange="updateValueLabel()">
                            <option value="">-- Chọn loại --</option>
                            <option value="PhanTram" <?= ($_SESSION['form_data']['loai'] ?? '') === 'PhanTram' ? 'selected' : '' ?>>Giảm theo %</option>
                            <option value="SoTienCoDinh" <?= ($_SESSION['form_data']['loai'] ?? '') === 'SoTienCoDinh' ? 'selected' : '' ?>>Giảm số tiền cố định (đ)</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="giaTri"><span id="valueLabel">Giá trị</span> <span class="required">*</span></label>
                        <div class="input-with-unit">
                            <input type="number" id="giaTri" name="giaTri" class="form-control" 
                                   placeholder="Nhập giá trị" step="0.01" 
                                   value="<?= $_SESSION['form_data']['giaTri'] ?? '' ?>" required>
                            <span class="input-unit" id="unitLabel">%</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="doiTuongApDung">Đối tượng áp dụng <span class="required">*</span></label>
                        <select id="doiTuongApDung" name="doiTuongApDung" class="form-control" required>
                            <option value="">-- Chọn đối tượng --</option>
                            <option value="Tất cả" <?= ($_SESSION['form_data']['doiTuongApDung'] ?? '') === 'Tất cả' ? 'selected' : '' ?>>Tất cả khách hàng</option>
                            <option value="Khách hàng thân thiết" <?= ($_SESSION['form_data']['doiTuongApDung'] ?? '') === 'Khách hàng thân thiết' ? 'selected' : '' ?>>Khách hàng thân thiết</option>
                            <option value="Khách hàng mới" <?= ($_SESSION['form_data']['doiTuongApDung'] ?? '') === 'Khách hàng mới' ? 'selected' : '' ?>>Khách hàng mới</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="ngayBatDau">Ngày bắt đầu <span class="required">*</span></label>
                        <input type="date" id="ngayBatDau" name="ngayBatDau" class="form-control" 
                               value="<?= $_SESSION['form_data']['ngayBatDau'] ?? '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="ngayKetThuc">Ngày kết thúc <span class="required">*</span></label>
                        <input type="date" id="ngayKetThuc" name="ngayKetThuc" class="form-control" 
                               value="<?= $_SESSION['form_data']['ngayKetThuc'] ?? '' ?>" required>
                    </div>
                </div>

                <!-- Added usage limit fields for maximum code uses and per-user limits -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="soLanSuDungToiDa">Số lượng mã giảm giá</label>
                        <input type="number" id="soLanSuDungToiDa" name="soLanSuDungToiDa" class="form-control" 
                               placeholder="Để trống = không giới hạn" 
                               min="1"
                               value="<?= $_SESSION['form_data']['soLanSuDungToiDa'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label for="soLanSuDungToiDaMotNguoiDung">Số lần sử dụng</label>
                        <input type="number" id="soLanSuDungToiDaMotNguoiDung" name="soLanSuDungToiDaMotNguoiDung" class="form-control" 
                               placeholder="Mặc định: 1" 
                               min="1"
                               value="<?= $_SESSION['form_data']['soLanSuDungToiDaMotNguoiDung'] ?? '' ?>">
                    </div>
                </div>

                <!-- Removed dieuKienApDung field -->

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i>
                        Tạo mã khuyến mãi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Tìm kiếm và lọc</h3>
        </div>
        <div class="card-body">
            <form method="GET" class="filter-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="search">Tìm kiếm:</label>
                        <input type="text" id="search" name="search" class="form-control" 
                               placeholder="Tìm theo tên khuyến mãi" 
                               value="<?= $_GET['search'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label for="type">Loại:</label>
                        <select id="type" name="type" class="form-control">
                            <option value="">Tất cả loại</option>
                            <option value="PhanTram" <?= ($_GET['type'] ?? '') === 'PhanTram' ? 'selected' : '' ?>>Giảm %</option>
                            <option value="SoTienCoDinh" <?= ($_GET['type'] ?? '') === 'SoTienCoDinh' ? 'selected' : '' ?>>Giảm số tiền</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="status">Trạng thái:</label>
                        <select id="status" name="status" class="form-control">
                            <option value="">Tất cả trạng thái</option>
                            <option value="active" <?= ($_GET['status'] ?? '') === 'active' ? 'selected' : '' ?>>Đang hoạt động</option>
                            <option value="upcoming" <?= ($_GET['status'] ?? '') === 'upcoming' ? 'selected' : '' ?>>Sắp hoạt động</option>
                            <option value="expired" <?= ($_GET['status'] ?? '') === 'expired' ? 'selected' : '' ?>>Hết hạn</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                                Tìm kiếm
                            </button>
                            <a href="<?= BASE_URL ?>/promotional-codes" class="btn btn-secondary">
                                <i class="fas fa-redo"></i>
                                Đặt lại
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Changed from cards grid to professional table layout -->
    <!-- Promotional Codes List -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Danh sách mã khuyến mãi</h3>
        </div>
        <div class="card-body">
            <?php if (empty($promotionalCodes)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>Không có mã khuyến mãi nào</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Tên khuyến mãi</th>
                                <th>Loại</th>
                                <th>Giá trị</th>
                                <th>Đối tượng</th>
                                <th>Ngày bắt đầu</th>
                                <th>Ngày kết thúc</th>
                                <th>Trạng thái</th>
                                <th>Số lượng mã giảm</th>
                                <th>Số lần sử dụng</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $dem = 0; foreach ($promotionalCodes as $code): ?>
                                <?php
                                    $dem++;
                                    $startDate = strtotime($code['ngayBatDau']);
                                    $endDate = strtotime($code['ngayKetThuc']);
                                    $today = strtotime('today');
                                    
                                    if ($today >= $startDate && $today <= $endDate) {
                                        $status = 'active';
                                        $statusText = 'Đang hoạt động';
                                    } elseif ($today < $startDate) {
                                        $status = 'upcoming';
                                        $statusText = 'Sắp hoạt động';
                                    } else {
                                        $status = 'expired';
                                        $statusText = 'Hết hạn';
                                    }
                                ?>
                                <tr>
                                    <td><?= $dem ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($code['tenKhuyenMai']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $code['loai'] === 'PhanTram' ? 'info' : 'warning' ?>">
                                            <?= $code['loai'] === 'PhanTram' ? 'Giảm %' : 'Giảm tiền' ?>
                                        </span>
                                    </td>
                                    <td class="value-cell">
                                        <strong><?php 
                                            if ($code['loai'] === 'PhanTram') {
                                                echo number_format($code['giaTri'], 0) . '%';
                                            } else {
                                                echo number_format($code['giaTri'], 0, ',', '.') . 'đ';
                                            }
                                        ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($code['doiTuongApDung']) ?></td>
                                    <td><?= date('d/m/Y', $startDate) ?></td>
                                    <td><?= date('d/m/Y', $endDate) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $status ?>">
                                            <?= $statusText ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($code['soLanSuDungToiDa'] ?? 'Không giới hạn') ?></td>
                                    <td><?= htmlspecialchars($code['soLanSuDungToiDaMotNguoiDung'] ?? '1') ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button type="button" class="btn btn-danger btn-sm" 
                                                    title="Xóa"
                                                    onclick="confirmDelete(<?= $code['maKhuyenMai'] ?>, '<?= htmlspecialchars($code['tenKhuyenMai']) ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal" id="deleteModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Xác nhận xóa</h3>
                <button type="button" class="modal-close" onclick="closeModal('deleteModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa mã khuyến mãi <strong id="deleteCodeName"></strong>?</p>
                <p class="text-danger">Hành động này không thể hoàn tác.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('deleteModal')">Hủy</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Xóa</a>
            </div>
        </div>
    </div>
</div>

<script>
function scrollToForm() {
    const formSection = document.getElementById('createFormSection');
    formSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function updateValueLabel() {
    const loai = document.getElementById('loai').value;
    const valueLabel = document.getElementById('valueLabel');
    const unitLabel = document.getElementById('unitLabel');
    const giaTri = document.getElementById('giaTri');
    
    if (loai === 'PhanTram') {
        valueLabel.textContent = 'Phần trăm giảm';
        unitLabel.textContent = '%';
        giaTri.max = '100';
        giaTri.placeholder = 'Nhập từ 0 - 100';
    } else if (loai === 'SoTienCoDinh') {
        valueLabel.textContent = 'Số tiền giảm';
        unitLabel.textContent = 'đ';
        giaTri.max = '';
        giaTri.placeholder = 'Nhập số tiền (VD: 50000)';
    }
}

function confirmDelete(codeId, codeName) {
    document.getElementById('deleteCodeName').textContent = codeName;
    document.getElementById('confirmDeleteBtn').href = '<?= BASE_URL ?>/promotional-codes/' + codeId + '/delete';
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
    updateValueLabel();
    
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            closeModal(e.target.id);
        }
    });
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal[style*="flex"]');
            if (openModal) {
                closeModal(openModal.id);
            }
        }
    });
});
</script>

<?php 
// Clear form data after displaying
unset($_SESSION['form_data']);
require_once __DIR__ . '/../layouts/footer.php'; 
?>
