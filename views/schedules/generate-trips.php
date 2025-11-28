<?php include __DIR__ . '/../layouts/header.php'; ?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/schedules-generate-trips.css">

<div class="container">
    <!-- Redesigned page header with modern card layout -->
    <div class="page-header">
        <div class="page-header-content">
            <h1>Sinh Chuyến Xe</h1>
            <p class="page-subtitle">Tạo các chuyến xe tự động từ lịch trình đã cấu hình</p>
        </div>
        <div class="page-actions">
            <a href="<?php echo BASE_URL; ?>/schedules" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="generation-container">
        <!-- Guidance card with improved visual hierarchy -->
        <div class="guidance-card">
            <div class="guidance-header">
                <i class="fas fa-lightbulb"></i>
                <h3>Hướng dẫn sinh chuyến xe</h3>
            </div>
            <ul class="guidance-list">
                <li><strong>Bước 1:</strong> Chọn lịch trình có trạng thái "Hoạt động" và còn hiệu lực</li>
                <li><strong>Bước 2:</strong> Tài xế đã được phân công sẽ tự động áp dụng cho các chuyến xe</li>
                <li><strong>Bước 3:</strong> Chọn phương tiện để thực hiện lịch trình</li>
                <li><strong>Bước 4:</strong> Hệ thống sẽ tạo chuyến xe theo ngày và giờ đã định</li>
            </ul>
        </div>

        <!-- Step-by-step form with card layout -->
        <form method="POST" action="<?php echo BASE_URL; ?>/schedules/process-generate-trips" class="generation-form">
            <!-- Step 1: Select Schedule -->
            <div class="form-step-card">
                <div class="step-number-badge">1</div>
                <div class="step-content">
                    <h2 class="step-title">Chọn Lịch Trình</h2>
                    
                    <div class="form-group">
                        <label for="maLichTrinh" class="form-label">Lịch trình <span class="required">*</span></label>
                        <select name="maLichTrinh" id="maLichTrinh" required class="form-select">
                            <option value="">-- Chọn lịch trình --</option>
                            <?php foreach ($schedules as $schedule): ?>
                                <option value="<?php echo $schedule['maLichTrinh']; ?>" 
                                        data-route="<?php echo htmlspecialchars($schedule['kyHieuTuyen']); ?>"
                                        data-time="<?php echo date('H:i', strtotime($schedule['gioKhoiHanh'])); ?>"
                                        data-period="<?php echo date('d/m/Y', strtotime($schedule['ngayBatDau'])) . ' → ' . date('d/m/Y', strtotime($schedule['ngayKetThuc'])); ?>"
                                        data-days="<?php echo Schedule::formatDaysOfWeek($schedule['thuTrongTuan']); ?>"
                                        data-driver="<?php echo htmlspecialchars($schedule['tenTaiXe'] ?? 'Chưa phân công'); ?>"
                                        data-driver-phone="<?php echo htmlspecialchars($schedule['soDienThoai'] ?? ''); ?>"
                                        data-driver-id="<?php echo $schedule['maTaiXe'] ?? ''; ?>">
                                    <?php echo $schedule['kyHieuTuyen'] . ' - ' . date('H:i', strtotime($schedule['gioKhoiHanh'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Improved schedule info display -->
                    <div id="scheduleInfo" class="schedule-info-box" style="display: none;">
                        <div class="info-grid">
                            <div class="info-cell">
                                <span class="info-label"><i class="fas fa-map-signs"></i> Tuyến đường</span>
                                <span class="info-value" id="routeInfo"></span>
                            </div>
                            <div class="info-cell">
                                <span class="info-label"><i class="fas fa-clock"></i> Giờ khởi hành</span>
                                <span class="info-value" id="timeInfo"></span>
                            </div>
                            <div class="info-cell">
                                <span class="info-label"><i class="fas fa-calendar"></i> Thời gian hoạt động</span>
                                <span class="info-value" id="periodInfo"></span>
                            </div>
                            <div class="info-cell">
                                <span class="info-label"><i class="fas fa-repeat"></i> Ngày trong tuần</span>
                                <span class="info-value" id="daysInfo"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Driver Information -->
            <div class="form-step-card">
                <div class="step-number-badge">2</div>
                <div class="step-content">
                    <h2 class="step-title">Thông Tin Tài Xế</h2>
                    
                    <div id="driverInfoSection" class="driver-info-card" style="display: none;">
                        <div class="driver-header">
                            <i class="fas fa-user-circle"></i>
                            <div class="driver-name-section">
                                <span class="driver-label">Tài xế được phân công</span>
                                <span class="driver-name" id="driverNameDisplay"></span>
                            </div>
                        </div>
                        <input type="hidden" name="maTaiXe" id="maTaiXeHidden">
                        <input type="hidden" name="loaiChoNgoi" id="loaiChoNgoiHidden">
                    </div>

                    <div id="noDriverWarning" class="warning-card" style="display: none;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <div class="warning-content">
                            <p class="warning-title">Chưa phân công tài xế</p>
                            <p class="warning-description">Lịch trình này chưa có tài xế. Vui lòng chỉnh sửa lịch trình để phân công tài xế trước.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: Select Vehicle -->
            <div class="form-step-card">
                <div class="step-number-badge">3</div>
                <div class="step-content">
                    <h2 class="step-title">Chọn Phương Tiện</h2>
                    
                    <div class="form-group">
                        <label for="maPhuongTien" class="form-label">Phương tiện <span class="required">*</span></label>
                        <select name="maPhuongTien" id="maPhuongTien" required class="form-select">
                            <option value="">-- Chọn xe --</option>
                            <?php foreach ($vehicles as $vehicle): ?>
                                <option value="<?php echo $vehicle['maPhuongTien']; ?>"
                                        data-seats="<?php echo $vehicle['soChoMacDinh']; ?>"
                                        data-type="<?php echo htmlspecialchars($vehicle['tenLoaiPhuongTien']); ?>"
                                        data-seat-type="<?php echo htmlspecialchars($vehicle['loaiChoNgoiMacDinh']); ?>">
                                    <?php echo $vehicle['tenLoaiPhuongTien'] . ' - ' . $vehicle['bienSo'] . ' (' . $vehicle['soChoMacDinh'] . ' chỗ)'; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Improved vehicle info display -->
                    <div id="vehicleInfo" class="vehicle-info-box" style="display: none;">
                        <div class="info-grid">
                            <div class="info-cell">
                                <span class="info-label"><i class="fas fa-car"></i> Loại xe</span>
                                <span class="info-value" id="vehicleType"></span>
                            </div>
                            <div class="info-cell">
                                <span class="info-label"><i class="fas fa-chair"></i> Số chỗ</span>
                                <span class="info-value" id="seatCount"></span>
                            </div>
                            <div class="info-cell">
                                <span class="info-label"><i class="fas fa-couch"></i> Loại chỗ ngồi</span>
                                <span class="info-value" id="seatType"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Validation messages section -->
            <div id="validationMessages" class="validation-messages"></div>

            <!-- Improved form actions layout -->
            <div class="form-actions">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-cogs"></i> Sinh chuyến xe
                </button>
                <a href="<?php echo BASE_URL; ?>/schedules" class="btn btn-outline">
                    <i class="fas fa-times"></i> Hủy bỏ
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('maLichTrinh').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const scheduleInfo = document.getElementById('scheduleInfo');
    const driverInfoSection = document.getElementById('driverInfoSection');
    const noDriverWarning = document.getElementById('noDriverWarning');
    
    if (this.value) {
        // Show schedule basic info
        document.getElementById('routeInfo').textContent = selectedOption.dataset.route;
        document.getElementById('timeInfo').textContent = selectedOption.dataset.time;
        document.getElementById('periodInfo').textContent = selectedOption.dataset.period;
        document.getElementById('daysInfo').textContent = selectedOption.dataset.days;
        scheduleInfo.style.display = 'block';
        
        // Show driver info in step 2
        const driverId = selectedOption.dataset.driverId;
        const driverName = selectedOption.dataset.driver;
        
        if (driverId && driverName !== 'Chưa phân công') {
            document.getElementById('driverNameDisplay').textContent = driverName;
            document.getElementById('maTaiXeHidden').value = driverId;
            driverInfoSection.style.display = 'block';
            noDriverWarning.style.display = 'none';
        } else {
            driverInfoSection.style.display = 'none';
            noDriverWarning.style.display = 'block';
            document.getElementById('maTaiXeHidden').value = '';
        }
        
        // Validate when both schedule and vehicle are selected
        validateTripGeneration();
    } else {
        scheduleInfo.style.display = 'none';
        driverInfoSection.style.display = 'none';
        noDriverWarning.style.display = 'none';
    }
});

// Handle vehicle selection
document.getElementById('maPhuongTien').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const vehicleInfo = document.getElementById('vehicleInfo');
    
    if (this.value) {
        document.getElementById('vehicleType').textContent = selectedOption.dataset.type;
        document.getElementById('seatCount').textContent = selectedOption.dataset.seats + ' chỗ';
        document.getElementById('seatType').textContent = selectedOption.dataset.seatType;
        document.getElementById('loaiChoNgoiHidden').value = selectedOption.dataset.seatType;
        
        vehicleInfo.style.display = 'block';
        
        // Validate when both schedule and vehicle are selected
        validateTripGeneration();
    } else {
        vehicleInfo.style.display = 'none';
        clearValidationMessages();
    }
});

function validateTripGeneration() {
    const scheduleId = document.getElementById('maLichTrinh').value;
    const vehicleId = document.getElementById('maPhuongTien').value;
    
    if (!scheduleId || !vehicleId) {
        clearValidationMessages();
        return;
    }
    
    // Show loading indicator
    showValidationLoading();
    
    // Make AJAX request to validate
    const formData = new FormData();
    formData.append('maLichTrinh', scheduleId);
    formData.append('maPhuongTien', vehicleId);
    
    fetch('<?php echo BASE_URL; ?>/schedules/validate-trips', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideValidationLoading();
        
        if (data.success) {
            showValidationSuccess();
        } else {
            showValidationErrors(data.errors);
        }
    })
    .catch(error => {
        hideValidationLoading();
        console.error('Validation error:', error);
        showValidationErrors(['Có lỗi xảy ra khi kiểm tra validation']);
    });
}

function showValidationLoading() {
    clearValidationMessages();
    const container = getValidationContainer();
    container.innerHTML = `
        <div class="validation-loading">
            <i class="fas fa-spinner fa-spin"></i> Đang kiểm tra ràng buộc...
        </div>
    `;
    container.style.display = 'block';
}

function hideValidationLoading() {
    const loading = document.querySelector('.validation-loading');
    if (loading) {
        loading.remove();
    }
}

function showValidationSuccess() {
    const container = getValidationContainer();
    container.innerHTML = `
        <div class="validation-success">
            <i class="fas fa-check-circle"></i> Tất cả ràng buộc đều hợp lệ. Có thể sinh chuyến xe.
        </div>
    `;
    container.style.display = 'block';
    
    // Enable submit button
    const submitBtn = document.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.classList.remove('btn-disabled');
    }
}

function showValidationErrors(errors) {
    const container = getValidationContainer();
    let errorHtml = '<div class="validation-errors"><h4><i class="fas fa-exclamation-triangle"></i> Phát hiện các ràng buộc vi phạm:</h4><ul>';
    
    errors.forEach(error => {
        errorHtml += `<li>${error}</li>`;
    });
    
    errorHtml += '</ul></div>';
    container.innerHTML = errorHtml;
    container.style.display = 'block';
    
    // Disable submit button
    const submitBtn = document.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.classList.add('btn-disabled');
    }
}

function clearValidationMessages() {
    const container = getValidationContainer();
    container.innerHTML = '';
    container.style.display = 'none';
    
    // Enable submit button by default
    const submitBtn = document.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.classList.remove('btn-disabled');
    }
}

function getValidationContainer() {
    let container = document.getElementById('validationMessages');
    if (!container) {
        container = document.createElement('div');
        container.id = 'validationMessages';
        container.className = 'validation-messages';
        
        // Insert after vehicle info
        const vehicleInfo = document.getElementById('vehicleInfo');
        vehicleInfo.parentNode.insertBefore(container, vehicleInfo.nextSibling);
    }
    return container;
}

// Enhanced form validation
document.querySelector('.generation-form').addEventListener('submit', function(e) {
    const schedule = document.getElementById('maLichTrinh').value;
    const vehicle = document.getElementById('maPhuongTien').value;
    const driverId = document.getElementById('maTaiXeHidden').value;
    
    if (!schedule || !vehicle) {
        e.preventDefault();
        alert('Vui lòng điền đầy đủ thông tin trước khi sinh chuyến xe.');
        return;
    }
    
    if (!driverId) {
        e.preventDefault();
        alert('Lịch trình chưa có tài xế được phân công. Vui lòng chỉnh sửa lịch trình để phân công tài xế trước.');
        return;
    }
    
    // Check if there are validation errors
    const errorContainer = document.querySelector('.validation-errors');
    if (errorContainer) {
        e.preventDefault();
        alert('Vui lòng khắc phục các ràng buộc vi phạm trước khi sinh chuyến xe.');
        return;
    }
    
    if (!confirm('Bạn có chắc chắn muốn sinh chuyến xe từ lịch trình này? Hành động này sẽ tạo ra nhiều chuyến xe mới.')) {
        e.preventDefault();
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
