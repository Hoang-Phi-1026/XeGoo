<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="page-header">
        <div class="page-title">
            <h1><i class="fas fa-plus"></i> Thêm tuyến đường mới</h1>
            <p>Tạo tuyến đường mới trong hệ thống</p>
        </div>
        <div class="page-actions">
            <a href="<?php echo BASE_URL; ?>/routes" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>
        </div>
    </div>

    <div class="form-container">
        <form method="POST" action="<?php echo BASE_URL; ?>/routes/store" class="form-card">
            <div class="form-section">
                <h3><i class="fas fa-info-circle"></i> Thông tin cơ bản</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="kyHieuTuyen">Ký hiệu tuyến <span class="required">*</span></label>
                        <input type="text" name="kyHieuTuyen" id="kyHieuTuyen" 
                               placeholder="VD: SG-DL" 
                               value="<?php echo htmlspecialchars($_SESSION['form_data']['kyHieuTuyen'] ?? ''); ?>" 
                               required>
                        <small class="form-help">Ký hiệu ngắn gọn để nhận diện tuyến đường</small>
                    </div>
                    <div class="form-group">
                        <label for="trangThai">Trạng thái</label>
                        <select name="trangThai" id="trangThai">
                            <?php foreach ($statusOptions as $key => $status): ?>
                                <option value="<?php echo $key; ?>" 
                                        <?php echo (isset($_SESSION['form_data']['trangThai']) && $_SESSION['form_data']['trangThai'] == $key) ? 'selected' : ''; ?>>
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
                               value="<?php echo htmlspecialchars($_SESSION['form_data']['diemDi'] ?? ''); ?>" 
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
                               value="<?php echo htmlspecialchars($_SESSION['form_data']['diemDen'] ?? ''); ?>" 
                               list="popularCities" required>
                    </div>
                </div>
            </div>

            <!-- Added pickup and drop-off points section -->
            <div class="form-section">
                <h3><i class="fas fa-map-pin"></i> Điểm đón và trả khách</h3>
                <p class="section-description">Thêm các điểm đón và trả khách cụ thể cho tuyến đường này</p>
                
                <!-- Pickup Points -->
                <div class="points-section">
                    <h4><i class="fas fa-play-circle text-success"></i> Điểm đón khách</h4>
                    <div id="pickup-points-container">
                        <div class="point-item">
                            <div class="form-row">
                                <div class="form-group flex-2">
                                    <label>Tên điểm đón</label>
                                    <input type="text" name="pickup_points[]" 
                                           placeholder="VD: Bến xe Miền Đông" 
                                           value="<?php echo htmlspecialchars($_SESSION['form_data']['pickup_points'][0] ?? ''); ?>">
                                </div>
                                <div class="form-group flex-3">
                                    <label>Địa chỉ chi tiết</label>
                                    <input type="text" name="pickup_addresses[]" 
                                           placeholder="VD: 292 Đinh Bộ Lĩnh, P.26, Q.Bình Thạnh" 
                                           value="<?php echo htmlspecialchars($_SESSION['form_data']['pickup_addresses'][0] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-sm btn-danger remove-point" onclick="removePoint(this)" style="display: none;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline" onclick="addPickupPoint()">
                        <i class="fas fa-plus"></i> Thêm điểm đón
                    </button>
                </div>

                <!-- Drop-off Points -->
                <div class="points-section">
                    <h4><i class="fas fa-stop-circle text-danger"></i> Điểm trả khách</h4>
                    <div id="dropoff-points-container">
                        <div class="point-item">
                            <div class="form-row">
                                <div class="form-group flex-2">
                                    <label>Tên điểm trả</label>
                                    <input type="text" name="dropoff_points[]" 
                                           placeholder="VD: Bến xe Đà Lạt" 
                                           value="<?php echo htmlspecialchars($_SESSION['form_data']['dropoff_points'][0] ?? ''); ?>">
                                </div>
                                <div class="form-group flex-3">
                                    <label>Địa chỉ chi tiết</label>
                                    <input type="text" name="dropoff_addresses[]" 
                                           placeholder="VD: 01 Tô Hiến Thành, P.3, TP.Đà Lạt" 
                                           value="<?php echo htmlspecialchars($_SESSION['form_data']['dropoff_addresses'][0] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-sm btn-danger remove-point" onclick="removePoint(this)" style="display: none;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
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
                               value="<?php echo htmlspecialchars($_SESSION['form_data']['khoangCach'] ?? ''); ?>" 
                               required>
                        <small class="form-help">Khoảng cách thực tế của tuyến đường</small>
                    </div>
                    <div class="form-group">
                        <label for="thoiGianDiChuyen">Thời gian di chuyển <span class="required">*</span></label>
                        <input type="time" name="thoiGianDiChuyen" id="thoiGianDiChuyen" 
                               value="<?php echo htmlspecialchars($_SESSION['form_data']['thoiGianDiChuyen'] ?? ''); ?>" 
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
                              placeholder="Mô tả về đặc điểm, cảnh quan, lưu ý của tuyến đường..."><?php echo htmlspecialchars($_SESSION['form_data']['moTa'] ?? ''); ?></textarea>
                    <small class="form-help">Thông tin bổ sung về tuyến đường (không bắt buộc)</small>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Lưu tuyến đường
                </button>
                <a href="<?php echo BASE_URL; ?>/routes" class="btn btn-outline">
                    <i class="fas fa-times"></i> Hủy bỏ
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Added JavaScript for dynamic point management -->
<script>
// Auto-generate route code based on departure and destination
document.getElementById('diemDi').addEventListener('blur', generateRouteCode);
document.getElementById('diemDen').addEventListener('blur', generateRouteCode);

function generateRouteCode() {
    const diemDi = document.getElementById('diemDi').value.trim();
    const diemDen = document.getElementById('diemDen').value.trim();
    const kyHieuTuyen = document.getElementById('kyHieuTuyen');
    
    if (diemDi && diemDen && !kyHieuTuyen.value) {
        const diCode = getLocationCode(diemDi);
        const denCode = getLocationCode(diemDen);
        if (diCode && denCode) {
            kyHieuTuyen.value = diCode + '-' + denCode;
        }
    }
}

function getLocationCode(location) {
    const codes = {
        'TP. Hồ Chí Minh': 'SG',
        'Hồ Chí Minh': 'SG',
        'Sài Gòn': 'SG',
        'Hà Nội': 'HN',
        'Đà Nẵng': 'DN',
        'Cần Thơ': 'CT',
        'Đà Lạt': 'DL',
        'Nha Trang': 'NT',
        'Vũng Tàu': 'VT',
        'Phan Thiết': 'PT',
        'Quy Nhon': 'QN',
        'Huế': 'HUE'
    };
    
    for (const [city, code] of Object.entries(codes)) {
        if (location.toLowerCase().includes(city.toLowerCase())) {
            return code;
        }
    }
    
    // If no match, use first 2 characters
    return location.substring(0, 2).toUpperCase();
}

// Calculate estimated travel time based on distance
document.getElementById('khoangCach').addEventListener('blur', function() {
    const distance = parseInt(this.value);
    const timeInput = document.getElementById('thoiGianDiChuyen');
    
    if (distance && !timeInput.value) {
        // Estimate: 60km/h average speed
        const hours = Math.floor(distance / 60);
        const minutes = Math.round((distance % 60) * 60 / 60);
        
        const timeString = String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0');
        timeInput.value = timeString;
    }
});

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

<!-- Added CSS for points section styling -->
<style>
.points-section {
    margin-bottom: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.points-section h4 {
    margin: 0 0 0.75rem 0;
    color: #495057;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.point-item {
    margin-bottom: 0.75rem;
    padding: 0.75rem;
    background: white;
    border-radius: 6px;
    border: 1px solid #dee2e6;
}

.point-item:last-child {
    margin-bottom: 0;
}

.section-description {
    color: #6c757d;
    font-size: 0.85rem;
    margin-bottom: 1rem;
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
    margin-top: 1rem;
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
