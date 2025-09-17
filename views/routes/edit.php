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

            <!-- Added pickup and drop-off points section with existing data -->
            <div class="form-section">
                <h3><i class="fas fa-map-pin"></i> Điểm đón và trả khách</h3>
                <p class="section-description">Cập nhật các điểm đón và trả khách cụ thể cho tuyến đường này</p>
                
                <!-- Pickup Points -->
                <div class="points-section">
                    <h4><i class="fas fa-play-circle text-success"></i> Điểm đón khách</h4>
                    <div id="pickup-points-container">
                        <?php 
                        $pickupPoints = $route['points']['pickup'] ?? [];
                        if (empty($pickupPoints)): 
                        ?>
                            <div class="point-item">
                                <div class="form-row">
                                    <div class="form-group flex-2">
                                        <label>Tên điểm đón</label>
                                        <input type="text" name="pickup_points[]" 
                                               placeholder="VD: Bến xe Miền Đông">
                                    </div>
                                    <div class="form-group flex-3">
                                        <label>Địa chỉ chi tiết</label>
                                        <input type="text" name="pickup_addresses[]" 
                                               placeholder="VD: 292 Đinh Bộ Lĩnh, P.26, Q.Bình Thạnh">
                                    </div>
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="button" class="btn btn-sm btn-danger remove-point" onclick="removePoint(this)" style="display: none;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($pickupPoints as $index => $point): ?>
                                <div class="point-item">
                                    <div class="form-row">
                                        <div class="form-group flex-2">
                                            <label>Tên điểm đón</label>
                                            <input type="text" name="pickup_points[]" 
                                                   placeholder="VD: Bến xe Miền Đông"
                                                   value="<?php echo htmlspecialchars($point['tenDiem']); ?>">
                                        </div>
                                        <div class="form-group flex-3">
                                            <label>Địa chỉ chi tiết</label>
                                            <input type="text" name="pickup_addresses[]" 
                                                   placeholder="VD: 292 Đinh Bộ Lĩnh, P.26, Q.Bình Thạnh"
                                                   value="<?php echo htmlspecialchars($point['diaChi']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button type="button" class="btn btn-sm btn-danger remove-point" onclick="removePoint(this)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline" onclick="addPickupPoint()">
                        <i class="fas fa-plus"></i> Thêm điểm đón
                    </button>
                </div>

                <!-- Drop-off Points -->
                <div class="points-section">
                    <h4><i class="fas fa-stop-circle text-danger"></i> Điểm trả khách</h4>
                    <div id="dropoff-points-container">
                        <?php 
                        $dropoffPoints = $route['points']['dropoff'] ?? [];
                        if (empty($dropoffPoints)): 
                        ?>
                            <div class="point-item">
                                <div class="form-row">
                                    <div class="form-group flex-2">
                                        <label>Tên điểm trả</label>
                                        <input type="text" name="dropoff_points[]" 
                                               placeholder="VD: Bến xe Đà Lạt">
                                    </div>
                                    <div class="form-group flex-3">
                                        <label>Địa chỉ chi tiết</label>
                                        <input type="text" name="dropoff_addresses[]" 
                                               placeholder="VD: 01 Tô Hiến Thành, P.3, TP.Đà Lạt">
                                    </div>
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="button" class="btn btn-sm btn-danger remove-point" onclick="removePoint(this)" style="display: none;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($dropoffPoints as $index => $point): ?>
                                <div class="point-item">
                                    <div class="form-row">
                                        <div class="form-group flex-2">
                                            <label>Tên điểm trả</label>
                                            <input type="text" name="dropoff_points[]" 
                                                   placeholder="VD: Bến xe Đà Lạt"
                                                   value="<?php echo htmlspecialchars($point['tenDiem']); ?>">
                                        </div>
                                        <div class="form-group flex-3">
                                            <label>Địa chỉ chi tiết</label>
                                            <input type="text" name="dropoff_addresses[]" 
                                                   placeholder="VD: 01 Tô Hiến Thành, P.3, TP.Đà Lạt"
                                                   value="<?php echo htmlspecialchars($point['diaChi']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button type="button" class="btn btn-sm btn-danger remove-point" onclick="removePoint(this)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline" onclick="addDropoffPoint()">
                        <i class="fas fa-plus"></i> Thêm điểm trả
                    </button>
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

<!-- Added JavaScript for dynamic point management (same as create.php) -->
<script>
// Dynamic point management functions
function addPickupPoint() {
    const container = document.getElementById('pickup-points-container');
    const pointItem = document.createElement('div');
    pointItem.className = 'point-item';
    pointItem.innerHTML = `
        <div class="form-row">
            <div class="form-group flex-2">
                <label>Tên điểm đón</label>
                <input type="text" name="pickup_points[]" placeholder="VD: Bến xe Miền Đông">
            </div>
            <div class="form-group flex-3">
                <label>Địa chỉ chi tiết</label>
                <input type="text" name="pickup_addresses[]" placeholder="VD: 292 Đinh Bộ Lĩnh, P.26, Q.Bình Thạnh">
            </div>
            <div class="form-group">
                <label>&nbsp;</label>
                <button type="button" class="btn btn-sm btn-danger remove-point" onclick="removePoint(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    container.appendChild(pointItem);
    updateRemoveButtons();
}

function addDropoffPoint() {
    const container = document.getElementById('dropoff-points-container');
    const pointItem = document.createElement('div');
    pointItem.className = 'point-item';
    pointItem.innerHTML = `
        <div class="form-row">
            <div class="form-group flex-2">
                <label>Tên điểm trả</label>
                <input type="text" name="dropoff_points[]" placeholder="VD: Bến xe Đà Lạt">
            </div>
            <div class="form-group flex-3">
                <label>Địa chỉ chi tiết</label>
                <input type="text" name="dropoff_addresses[]" placeholder="VD: 01 Tô Hiến Thành, P.3, TP.Đà Lạt">
            </div>
            <div class="form-group">
                <label>&nbsp;</label>
                <button type="button" class="btn btn-sm btn-danger remove-point" onclick="removePoint(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    container.appendChild(pointItem);
    updateRemoveButtons();
}

function removePoint(button) {
    const pointItem = button.closest('.point-item');
    pointItem.remove();
    updateRemoveButtons();
}

function updateRemoveButtons() {
    // Show/hide remove buttons for pickup points
    const pickupItems = document.querySelectorAll('#pickup-points-container .point-item');
    pickupItems.forEach((item, index) => {
        const removeBtn = item.querySelector('.remove-point');
        if (pickupItems.length > 1) {
            removeBtn.style.display = 'block';
        } else {
            removeBtn.style.display = 'none';
        }
    });
    
    // Show/hide remove buttons for dropoff points
    const dropoffItems = document.querySelectorAll('#dropoff-points-container .point-item');
    dropoffItems.forEach((item, index) => {
        const removeBtn = item.querySelector('.remove-point');
        if (dropoffItems.length > 1) {
            removeBtn.style.display = 'block';
        } else {
            removeBtn.style.display = 'none';
        }
    });
}

// Initialize remove button visibility
document.addEventListener('DOMContentLoaded', function() {
    updateRemoveButtons();
});
</script>

<!-- Added CSS for points section styling (same as create.php) -->
<style>
.points-section {
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.points-section h4 {
    margin: 0 0 1rem 0;
    color: #495057;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.point-item {
    margin-bottom: 1rem;
    padding: 1rem;
    background: white;
    border-radius: 6px;
    border: 1px solid #dee2e6;
}

.point-item:last-child {
    margin-bottom: 0;
}

.section-description {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 1.5rem;
    font-style: italic;
}

.flex-2 {
    flex: 2;
}

.flex-3 {
    flex: 3;
}

.text-success {
    color: #28a745 !important;
}

.text-danger {
    color: #dc3545 !important;
}

.remove-point {
    margin-top: 1.5rem;
}

@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
    }
    
    .flex-2, .flex-3 {
        flex: 1;
    }
}
</style>

<?php 
// Clear form data after displaying
unset($_SESSION['form_data']);
include __DIR__ . '/../layouts/footer.php'; 
?>
