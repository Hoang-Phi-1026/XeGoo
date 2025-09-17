<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="page-header">
        <div class="page-title">
            <h1><i class="fas fa-edit"></i> Chỉnh sửa phương tiện</h1>
            <p>Cập nhật thông tin phương tiện <?php echo htmlspecialchars($vehicle['bienSo']); ?></p>
        </div>
        <div class="page-actions">
            <a href="<?php echo BASE_URL; ?>/vehicles/<?php echo $vehicle['maPhuongTien']; ?>" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Quay lại chi tiết
            </a>
        </div>
    </div>

    <div class="form-container">
        <form method="POST" action="<?php echo BASE_URL; ?>/vehicles/<?php echo $vehicle['maPhuongTien']; ?>/update" class="vehicle-form">
            <div class="form-grid">
                <!-- Updated to use maLoaiPhuongTien instead of loaiPhuongTien -->
                <div class="form-group">
                    <label for="maLoaiPhuongTien">Loại phương tiện <span class="required">*</span></label>
                    <select name="maLoaiPhuongTien" id="maLoaiPhuongTien" required>
                        <option value="">Chọn loại phương tiện</option>
                        <?php foreach ($vehicleTypes as $key => $value): ?>
                            <option value="<?php echo $key; ?>" 
                                    <?php echo (isset($_SESSION['form_data']['maLoaiPhuongTien']) ? 
                                        ($_SESSION['form_data']['maLoaiPhuongTien'] == $key ? 'selected' : '') : 
                                        ($vehicle['maLoaiPhuongTien'] == $key ? 'selected' : '')); ?>>
                                <?php echo $value; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="bienSo">Biển số xe <span class="required">*</span></label>
                    <input type="text" name="bienSo" id="bienSo" required
                           value="<?php echo htmlspecialchars($_SESSION['form_data']['bienSo'] ?? $vehicle['bienSo']); ?>"
                           placeholder="Ví dụ: 51A-12345">
                </div>

                <div class="form-group">
                    <label for="trangThai">Trạng thái</label>
                    <select name="trangThai" id="trangThai">
                        <?php foreach ($statusOptions as $key => $value): ?>
                            <option value="<?php echo $key; ?>"
                                    <?php echo (isset($_SESSION['form_data']['trangThai']) ? 
                                        ($_SESSION['form_data']['trangThai'] == $key ? 'selected' : '') : 
                                        ($vehicle['trangThai'] == $key ? 'selected' : '')); ?>>
                                <?php echo $value; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Cập nhật phương tiện
                </button>
                <a href="<?php echo BASE_URL; ?>/vehicles/<?php echo $vehicle['maPhuongTien']; ?>" class="btn btn-secondary">
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
