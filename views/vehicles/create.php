<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="page-header">
        <div class="page-title">
            <h1><i class="fas fa-plus"></i> Thêm phương tiện mới</h1>
            <p>Nhập thông tin phương tiện mới vào hệ thống</p>
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
                <div class="form-group">
                    <label for="loaiPhuongTien">Loại phương tiện <span class="required">*</span></label>
                    <select name="loaiPhuongTien" id="loaiPhuongTien" required>
                        <option value="">Chọn loại phương tiện</option>
                        <?php foreach ($vehicleTypes as $key => $value): ?>
                            <option value="<?php echo $key; ?>" 
                                    <?php echo (isset($_SESSION['form_data']['loaiPhuongTien']) && $_SESSION['form_data']['loaiPhuongTien'] == $key) ? 'selected' : ''; ?>>
                                <?php echo $value; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="soChoNgoi">Số chỗ ngồi <span class="required">*</span></label>
                    <input type="number" name="soChoNgoi" id="soChoNgoi" min="1" max="100" required
                           value="<?php echo htmlspecialchars($_SESSION['form_data']['soChoNgoi'] ?? ''); ?>"
                           placeholder="Nhập số chỗ ngồi">
                </div>

                <div class="form-group">
                    <label for="loaiChoNgoi">Loại chỗ ngồi <span class="required">*</span></label>
                    <select name="loaiChoNgoi" id="loaiChoNgoi" required>
                        <option value="">Chọn loại chỗ ngồi</option>
                        <?php foreach ($seatTypes as $key => $value): ?>
                            <option value="<?php echo $key; ?>"
                                    <?php echo (isset($_SESSION['form_data']['loaiChoNgoi']) && $_SESSION['form_data']['loaiChoNgoi'] == $key) ? 'selected' : ''; ?>>
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
