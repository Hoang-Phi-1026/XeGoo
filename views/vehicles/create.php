<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="page-header">
        <div class="page-title">
            <h1>Thêm phương tiện mới</h1>
        </div>
        <div class="page-actions">
            <a href="<?php echo BASE_URL; ?>/vehicles" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>
        </div>
    </div>

    <div class="form-container">
        <form method="POST" action="<?php echo BASE_URL; ?>/vehicles/store" class="vehicle-form">
            <div class="form-grid">
                <!-- Updated to use maLoaiPhuongTien instead of loaiPhuongTien -->
                <div class="form-group">
                    <label for="maLoaiPhuongTien">Loại phương tiện <span class="required">*</span></label>
                    <select name="maLoaiPhuongTien" id="maLoaiPhuongTien" required>
                        <option value="">Chọn loại phương tiện</option>
                        <?php foreach ($vehicleTypes as $key => $value): ?>
                            <option value="<?php echo $key; ?>" 
                                    <?php echo (isset($_SESSION['form_data']['maLoaiPhuongTien']) && $_SESSION['form_data']['maLoaiPhuongTien'] == $key) ? 'selected' : ''; ?>>
                                <?php echo $value; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="bienSo">Biển số xe <span class="required">*</span></label>
                    <input type="text" name="bienSo" id="bienSo" required
                           value="<?php echo htmlspecialchars($_SESSION['form_data']['bienSo'] ?? ''); ?>"
                           placeholder="Ví dụ: 51A-12345">
                </div>

                <div class="form-group">
                    <label for="trangThai">Trạng thái</label>
                    <select name="trangThai" id="trangThai">
                        <?php foreach ($statusOptions as $key => $value): ?>
                            <option value="<?php echo $key; ?>"
                                    <?php echo (isset($_SESSION['form_data']['trangThai']) && $_SESSION['form_data']['trangThai'] == $key) ? 'selected' : ($key == 'Đang hoạt động' ? 'selected' : ''); ?>>
                                <?php echo $value; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Added new field for planned operating route selection -->
                <div class="form-group">
                    <label for="tuyen_hoat_dong_du_kien">Tuyến hoạt động dự kiến</label>
                    <select name="tuyen_hoat_dong_du_kien" id="tuyen_hoat_dong_du_kien">
                        <option value="">Chọn tuyến đường</option>
                        <?php foreach ($routes as $route): ?>
                            <option value="<?php echo htmlspecialchars($route['kyHieuTuyen']); ?>"
                                    <?php echo (isset($_SESSION['form_data']['tuyen_hoat_dong_du_kien']) && $_SESSION['form_data']['tuyen_hoat_dong_du_kien'] == $route['kyHieuTuyen']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($route['kyHieuTuyen']); ?> (<?php echo htmlspecialchars($route['diemDi']); ?> → <?php echo htmlspecialchars($route['diemDen']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small>Tuyến đường dự kiến mà phương tiện này sẽ chạy, giúp dễ dàng chọn xe khi sinh chuyến.</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Lưu phương tiện
                </button>
                <a href="<?php echo BASE_URL; ?>/vehicles" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Hủy bỏ
                </a>
            </div>
        </form>
    </div>
</div>

<?php 
// Clear form data after displaying
unset($_SESSION['form_data']); 
?>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
