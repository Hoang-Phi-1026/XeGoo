<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<!-- Ensure main.css is loaded first, then prices.css -->
<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/main.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/prices.css">

<div class="page-container">
    <div class="page-header">
        <h1 class="page-title">
            Thêm giá vé mới
        </h1>
        <div class="page-actions">
            <a href="<?= BASE_URL ?>/prices" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Thông tin giá vé</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/prices/store" class="price-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="maTuyenDuong">Tuyến đường <span class="required">*</span></label>
                        <select class="form-control" id="maTuyenDuong" name="maTuyenDuong" required>
                            <option value="">Chọn tuyến đường</option>
                            <?php foreach ($routes as $route): ?>
                                <option value="<?= $route['maTuyenDuong'] ?>" 
                                        <?= ($_SESSION['form_data']['maTuyenDuong'] ?? '') == $route['maTuyenDuong'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($route['kyHieuTuyen']) ?> - <?= htmlspecialchars($route['diemDi']) ?> → <?= htmlspecialchars($route['diemDen']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <!-- Updated field name from maPhuongTien to maLoaiPhuongTien and using vehicleTypes instead of vehicles -->
                        <label for="maLoaiPhuongTien">Loại phương tiện <span class="required">*</span></label>
                        <select class="form-control" id="maLoaiPhuongTien" name="maLoaiPhuongTien" required>
                            <option value="">Chọn loại phương tiện</option>
                            <?php foreach ($vehicleTypes as $vehicleType): ?>
                                <option value="<?= $vehicleType['maLoaiPhuongTien'] ?>" 
                                        data-seat-type="<?= htmlspecialchars($vehicleType['loaiChoNgoiMacDinh']) ?>"
                                        <?= ($_SESSION['form_data']['maLoaiPhuongTien'] ?? '') == $vehicleType['maLoaiPhuongTien'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($vehicleType['tenLoaiPhuongTien']) ?> - <?= htmlspecialchars($vehicleType['hangXe']) ?> (<?= $vehicleType['soChoMacDinh'] ?> chỗ)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="loaiChoNgoi">Loại chỗ ngồi <span class="required">*</span></label>
                        <select class="form-control" id="loaiChoNgoi" name="loaiChoNgoi" required>
                            <option value="">Chọn loại chỗ ngồi</option>
                            <?php foreach ($seatTypes as $key => $value): ?>
                                <option value="<?= $key ?>" 
                                        <?= ($_SESSION['form_data']['loaiChoNgoi'] ?? '') === $key ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($value) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="maLoaiVe">Loại vé <span class="required">*</span></label>
                        <select class="form-control" id="maLoaiVe" name="maLoaiVe" required>
                            <option value="">Chọn loại vé</option>
                            <?php foreach ($ticketTypes as $ticketType): ?>
                                <option value="<?= $ticketType['maLoaiVe'] ?>" 
                                        <?= ($_SESSION['form_data']['maLoaiVe'] ?? '') == $ticketType['maLoaiVe'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ticketType['tenLoaiVe']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="giaVe">Giá vé (VNĐ) <span class="required">*</span></label>
                        <input type="number" class="form-control" id="giaVe" name="giaVe" 
                               min="0" step="1000" required
                               value="<?= htmlspecialchars($_SESSION['form_data']['giaVe'] ?? '') ?>"
                               placeholder="Nhập giá vé">
                    </div>
                    <div class="form-group">
                        <label for="giaVeKhuyenMai">Giá khuyến mãi (VNĐ)</label>
                        <input type="number" class="form-control" id="giaVeKhuyenMai" name="giaVeKhuyenMai" 
                               min="0" step="1000"
                               value="<?= htmlspecialchars($_SESSION['form_data']['giaVeKhuyenMai'] ?? '') ?>"
                               placeholder="Nhập giá khuyến mãi (tùy chọn)">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="ngayBatDau">Ngày bắt đầu <span class="required">*</span></label>
                        <input type="date" class="form-control" id="ngayBatDau" name="ngayBatDau" required
                               value="<?= htmlspecialchars($_SESSION['form_data']['ngayBatDau'] ?? date('Y-m-d')) ?>">
                    </div>
                    <div class="form-group">
                        <label for="ngayKetThuc">Ngày kết thúc <span class="required">*</span></label>
                        <input type="date" class="form-control" id="ngayKetThuc" name="ngayKetThuc" required
                               value="<?= htmlspecialchars($_SESSION['form_data']['ngayKetThuc'] ?? date('Y-m-d', strtotime('+1 year'))) ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="trangThai">Trạng thái</label>
                        <select class="form-control" id="trangThai" name="trangThai">
                            <?php foreach ($statusOptions as $key => $value): ?>
                                <option value="<?= $key ?>" 
                                        <?= ($_SESSION['form_data']['trangThai'] ?? 'Hoạt động') === $key ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($value) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="moTa">Mô tả</label>
                    <textarea class="form-control" id="moTa" name="moTa" rows="3" 
                              placeholder="Nhập mô tả về giá vé (tùy chọn)"><?= htmlspecialchars($_SESSION['form_data']['moTa'] ?? '') ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Lưu giá vé
                    </button>
                    <a href="<?= BASE_URL ?>/prices" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Hủy bỏ
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.querySelector('.price-form');
    const giaVeInput = document.getElementById('giaVe');
    const giaKhuyenMaiInput = document.getElementById('giaVeKhuyenMai');
    const ngayBatDauInput = document.getElementById('ngayBatDau');
    const ngayKetThucInput = document.getElementById('ngayKetThuc');
    const vehicleTypeSelect = document.getElementById('maLoaiPhuongTien');
    const seatTypeSelect = document.getElementById('loaiChoNgoi');

    // Auto-select seat type based on vehicle type selection
    vehicleTypeSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const vehicleSeatType = selectedOption.getAttribute('data-seat-type');
            if (vehicleSeatType) {
                // Try to match the vehicle type's seat type with available options
                for (let option of seatTypeSelect.options) {
                    if (option.value === vehicleSeatType) {
                        seatTypeSelect.value = vehicleSeatType;
                        break;
                    }
                }
            }
        }
    });

    // Validate promotional price
    giaKhuyenMaiInput.addEventListener('input', function() {
        const giaVe = parseFloat(giaVeInput.value) || 0;
        const giaKhuyenMai = parseFloat(this.value) || 0;
        
        if (giaKhuyenMai > 0 && giaKhuyenMai >= giaVe) {
            this.setCustomValidity('Giá khuyến mãi phải nhỏ hơn giá vé thường');
        } else {
            this.setCustomValidity('');
        }
    });

    // Validate date range
    ngayKetThucInput.addEventListener('change', function() {
        const startDate = new Date(ngayBatDauInput.value);
        const endDate = new Date(this.value);
        
        if (endDate <= startDate) {
            this.setCustomValidity('Ngày kết thúc phải sau ngày bắt đầu');
        } else {
            this.setCustomValidity('');
        }
    });

    // Format price inputs
    [giaVeInput, giaKhuyenMaiInput].forEach(input => {
        input.addEventListener('input', function() {
            // Remove non-numeric characters except for the decimal point
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });

    console.log('[v0] Create price form JavaScript loaded successfully');
});
</script>

<?php 
// Clear form data after displaying
unset($_SESSION['form_data']);
require_once __DIR__ . '/../layouts/footer.php'; 
?>
