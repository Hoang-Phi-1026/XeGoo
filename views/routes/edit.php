<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="page-header">
        <div class="page-title">
            <h1><i class="fas fa-edit"></i> Chỉnh sửa tuyến đường</h1>
            <p>Cập nhật thông tin tuyến đường: <?php echo htmlspecialchars($route['kyHieuTuyen']); ?></p>
        </div>
        <div class="page-actions">
            <a href="<?php echo BASE_URL; ?>/routes/<?php echo $route['maTuyenDuong']; ?>" class="btn btn-outline">
                <i class="fas fa-eye"></i> Xem chi tiết
            </a>
            <a href="<?php echo BASE_URL; ?>/routes" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>
        </div>
    </div>

    <div class="form-container">
        <form method="POST" action="<?php echo BASE_URL; ?>/routes/<?php echo $route['maTuyenDuong']; ?>/update" class="form-card">
            <div class="form-section">
                <h3><i class="fas fa-info-circle"></i> Thông tin cơ bản</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="kyHieuTuyen">Ký hiệu tuyến <span class="required">*</span></label>
                        <input type="text" name="kyHieuTuyen" id="kyHieuTuyen" 
                               placeholder="VD: SG-DL" 
                               value="<?php echo htmlspecialchars($_SESSION['form_data']['kyHieuTuyen'] ?? $route['kyHieuTuyen']); ?>" 
                               required>
                        <small class="form-help">Ký hiệu ngắn gọn để nhận diện tuyến đường</small>
                    </div>
                    <div class="form-group">
                        <label for="trangThai">Trạng thái</label>
                        <select name="trangThai" id="trangThai">
                            <?php foreach ($statusOptions as $key => $status): ?>
                                <option value="<?php echo $key; ?>" 
                                        <?php echo (isset($_SESSION['form_data']['trangThai']) ? 
                                                   ($_SESSION['form_data']['trangThai'] == $key ? 'selected' : '') : 
                                                   ($route['trangThai'] == $key ? 'selected' : '')); ?>>
                                    <?php echo $status; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3><i class="fas fa-map-marker-alt"></i> Điểm đi và điểm đến</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="diemDi">Điểm đi <span class="required">*</span></label>
                        <input type="text" name="diemDi" id="diemDi" 
                               placeholder="VD: TP. Hồ Chí Minh" 
                               value="<?php echo htmlspecialchars($_SESSION['form_data']['diemDi'] ?? $route['diemDi']); ?>" 
                               list="popularCities" required>
                        <datalist id="popularCities">
                            <?php foreach ($popularCities as $city): ?>
                                <option value="<?php echo $city; ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    <div class="form-group">
                        <label for="diemDen">Điểm đến <span class="required">*</span></label>
                        <input type="text" name="diemDen" id="diemDen" 
                               placeholder="VD: Đà Lạt" 
                               value="<?php echo htmlspecialchars($_SESSION['form_data']['diemDen'] ?? $route['diemDen']); ?>" 
                               list="popularCities" required>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3><i class="fas fa-road"></i> Thông tin hành trình</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="khoangCach">Khoảng cách (km) <span class="required">*</span></label>
                        <input type="number" name="khoangCach" id="khoangCach" 
                               min="1" max="2000" 
                               placeholder="VD: 310" 
                               value="<?php echo htmlspecialchars($_SESSION['form_data']['khoangCach'] ?? $route['khoangCach']); ?>" 
                               required>
                        <small class="form-help">Khoảng cách thực tế của tuyến đường</small>
                    </div>
                    <div class="form-group">
                        <label for="thoiGianDiChuyen">Thời gian di chuyển <span class="required">*</span></label>
                        <?php 
                        $timeValue = $_SESSION['form_data']['thoiGianDiChuyen'] ?? substr($route['thoiGianDiChuyen'], 0, 5);
                        ?>
                        <input type="time" name="thoiGianDiChuyen" id="thoiGianDiChuyen" 
                               value="<?php echo htmlspecialchars($timeValue); ?>" 
                               required>
                        <small class="form-help">Thời gian di chuyển ước tính (giờ:phút)</small>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3><i class="fas fa-comment"></i> Mô tả</h3>
                
                <div class="form-group">
                    <label for="moTa">Mô tả tuyến đường</label>
                    <textarea name="moTa" id="moTa" rows="4" 
                              placeholder="Mô tả về đặc điểm, cảnh quan, lưu ý của tuyến đường..."><?php echo htmlspecialchars($_SESSION['form_data']['moTa'] ?? $route['moTa']); ?></textarea>
                    <small class="form-help">Thông tin bổ sung về tuyến đường (không bắt buộc)</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Cập nhật tuyến đường
                </button>
                <a href="<?php echo BASE_URL; ?>/routes/<?php echo $route['maTuyenDuong']; ?>" class="btn btn-outline">
                    <i class="fas fa-times"></i> Hủy bỏ
                </a>
            </div>
        </form>
    </div>
</div>

<?php 
// Clear form data after displaying
unset($_SESSION['form_data']);
include __DIR__ . '/../layouts/footer.php'; 
?>
