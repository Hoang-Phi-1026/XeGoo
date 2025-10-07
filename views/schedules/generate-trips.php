<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="page-header">
        <div class="page-title">
            <h1>Sinh Chuyến Xe</h1>
        </div>
        <div class="page-actions">
            <a href="<?php echo BASE_URL; ?>/schedules" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="form-container">
        <div class="generation-info">
            <div class="info-card">
                <h3><i class="fas fa-info-circle"></i> Hướng dẫn sinh chuyến xe</h3>
                <ul>
                    <li>Chọn lịch trình có trạng thái "Hoạt động" và còn hiệu lực</li>
                    <li>Tài xế đã được phân công trong lịch trình sẽ tự động áp dụng</li>
                    <li>Chọn xe cụ thể để thực hiện lịch trình</li>
                    <li>Hệ thống sẽ tự động tạo các chuyến xe theo ngày và giờ đã định</li>
                    <li>Các điểm đón/trả sẽ được sao chép từ tuyến đường</li>
                </ul>
            </div>
        </div>

        <form method="POST" action="<?php echo BASE_URL; ?>/schedules/process-generate-trips" class="generation-form">
            <div class="form-steps">
                <div class="form-step">
                    <div class="step-header">
                        <span class="step-number">1</span>
                        <h3>Chọn lịch trình</h3>
                    </div>
                    
                    <div class="form-group">
                        <label for="maLichTrinh">Lịch trình <span class="required">*</span></label>
                        <select name="maLichTrinh" id="maLichTrinh" required>
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
                                    <?php echo $schedule['kyHieuTuyen'] . ' - ' . date('H:i', strtotime($schedule['gioKhoiHanh'])) . ' (' . date('d/m/Y', strtotime($schedule['ngayBatDau'])) . ' → ' . date('d/m/Y', strtotime($schedule['ngayKetThuc'])) . ', ' . Schedule::formatDaysOfWeek($schedule['thuTrongTuan']) . ')'; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="scheduleInfo" class="schedule-info" style="display: none;">
                        <div class="info-grid">
                            <div class="info-item">
                                <label>Tuyến đường:</label>
                                <span id="routeInfo"></span>
                            </div>
                            <div class="info-item">
                                <label>Giờ khởi hành:</label>
                                <span id="timeInfo"></span>
                            </div>
                            <div class="info-item">
                                <label>Thời gian hoạt động:</label>
                                <span id="periodInfo"></span>
                            </div>
                            <div class="info-item">
                                <label>Ngày trong tuần:</label>
                                <span id="daysInfo"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-step">
                    <div class="step-header">
                        <span class="step-number">2</span>
                        <h3>Thông tin tài xế được phân công</h3>
                    </div>
                    
                    <div id="driverInfoSection" class="driver-info-section" style="display: none;">
                        <div class="driver-card">
                            <div class="driver-icon">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="driver-details">
                                <div class="driver-name">
                                    <label>Tên tài xế:</label>
                                    <span id="driverNameDisplay"></span>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="maTaiXe" id="maTaiXeHidden">
                        <input type="hidden" name="loaiChoNgoi" id="loaiChoNgoiHidden">
                        <p class="driver-note">
                            <i class="fas fa-info-circle"></i> 
                            Tài xế này đã được phân công trong lịch trình và sẽ tự động áp dụng cho các chuyến xe được sinh ra.
                        </p>
                    </div>

                    <div id="noDriverWarning" class="no-driver-warning" style="display: none;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Lịch trình này chưa có tài xế được phân công. Vui lòng quay lại chỉnh sửa lịch trình để phân công tài xế trước khi sinh chuyến xe.</p>
                    </div>
                </div>
                <div class="form-step">
                    <div class="step-header">
                        <span class="step-number">3</span>
                        <h3>Chọn xe cụ thể</h3>
                    </div>
                    
                    <div class="form-group">
                        <label for="maPhuongTien">Phương tiện <span class="required">*</span></label>
                        <select name="maPhuongTien" id="maPhuongTien" required>
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

                    <div id="vehicleInfo" class="vehicle-info" style="display: none;">
                        <div class="info-grid">
                            <div class="info-item">
                                <label>Loại xe:</label>
                                <span id="vehicleType"></span>
                            </div>
                            <div class="info-item">
                                <label>Số chỗ:</label>
                                <span id="seatCount"></span>
                            </div>
                            <div class="info-item">
                                <label>Loại chỗ ngồi:</label>
                                <span id="seatType"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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

<style>
.validation-messages {
    margin: 20px 0;
    padding: 0;
}

.validation-loading {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
    color: #6c757d;
}

.validation-success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    border-radius: 8px;
    padding: 15px;
    color: #155724;
}

.validation-success i {
    color: #28a745;
    margin-right: 8px;
}

.validation-errors {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    border-radius: 8px;
    padding: 15px;
    color: #721c24;
}

.validation-errors h4 {
    margin: 0 0 10px 0;
    font-size: 16px;
}

.validation-errors i {
    color: #dc3545;
    margin-right: 8px;
}

.validation-errors ul {
    margin: 0;
    padding-left: 20px;
}

.validation-errors li {
    margin-bottom: 5px;
    line-height: 1.4;
}

.btn-disabled {
    opacity: 0.6;
    cursor: not-allowed;
    pointer-events: none;
}

.validation-loading i {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Updated driver info section styling to match schedule info box */
.driver-info-section {
    margin-top: 15px;
}

.driver-card label {
  margin: 0;             /* bỏ margin mặc định */
  padding: 0;            /* gọn luôn */
  font-weight: 600;      /* cho nổi lên nếu muốn */
  display: inline;       /* để label và span nằm cùng hàng */
}


.driver-card {
  display: flex;
  align-items: center; /* căn giữa theo chiều dọc */
  justify-content: flex-start; /* căn trái theo chiều ngang */
  gap: 20px;
  background: #fef5e7;
  border: 2px solid #e74c3c;
  border-radius: 10px;
}


.driver-icon {
    font-size: 48px;
    color: #e74c3c;
}

.driver-details {
    flex: 1;
}

.driver-name,
.driver-phone {
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.driver-name label,
.driver-phone label {
    font-weight: 600;
    color: #495057;
    min-width: 120px;
}

.driver-name span,
.driver-phone span {
    color: #212529;
    font-size: 16px;
}

.driver-note {
    margin-top: 15px;
    padding: 12px;
    background: #fef5e7;
    border-left: 4px solid #e74c3c;
    border-radius: 4px;
    color: #721c24;
    font-size: 14px;
}

.driver-note i {
    margin-right: 8px;
    color: #e74c3c;
}

.no-driver-warning {
    background: #fff3cd;
    border: 2px solid #ffc107;
    border-radius: 8px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    color: #856404;
}

.no-driver-warning i {
    font-size: 32px;
    color: #ffc107;
}

.no-driver-warning p {
    margin: 0;
    font-size: 15px;
    line-height: 1.5;
}
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
