<?php include __DIR__ . '/../layouts/header.php'; ?>
<div class="container">
    <div class="page-header">
        <div class="page-title">
            <h1>Thêm Lịch Trình Mới</h1>
        </div>
        <div class="page-actions">
            <a href="<?php echo BASE_URL; ?>/schedules" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="form-container">
        <form method="POST" action="<?php echo BASE_URL; ?>/schedules/store" class="schedule-form">
            <div class="form-grid">
                <!-- Basic Information -->
                <div class="form-section">
                    <h3><i class="fas fa-info-circle"></i> Thông tin cơ bản</h3>
                    
                    <div class="form-group">
                        <label for="maTuyenDuong">Tuyến đường <span class="required">*</span></label>
                        <select name="maTuyenDuong" id="maTuyenDuong" required>
                            <option value="">-- Chọn tuyến đường --</option>
                            <?php foreach ($routes as $route): ?>
                                <option value="<?php echo $route['maTuyenDuong']; ?>" 
                                        <?php echo (isset($_SESSION['form_data']['maTuyenDuong']) && $_SESSION['form_data']['maTuyenDuong'] == $route['maTuyenDuong']) ? 'selected' : ''; ?>>
                                    <?php echo $route['kyHieuTuyen'] . ' - ' . $route['diemDi'] . ' → ' . $route['diemDen']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="tenLichTrinh">Tên lịch trình <span class="required">*</span></label>
                        <input type="text" name="tenLichTrinh" id="tenLichTrinh" 
                               placeholder="VD: Lịch trình sáng SG-DL" 
                               value="<?php echo htmlspecialchars($_SESSION['form_data']['tenLichTrinh'] ?? ''); ?>" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="gioKhoiHanh">Giờ khởi hành <span class="required">*</span></label>
                            <input type="time" name="gioKhoiHanh" id="gioKhoiHanh" 
                                   value="<?php echo $_SESSION['form_data']['gioKhoiHanh'] ?? ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="gioKetThuc">Giờ kết thúc <span class="required">*</span></label>
                            <input type="time" name="gioKetThuc" id="gioKetThuc" 
                                   value="<?php echo $_SESSION['form_data']['gioKetThuc'] ?? ''; ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Schedule Period -->
                <div class="form-section">
                    <h3><i class="fas fa-calendar"></i> Thời gian hoạt động</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="ngayBatDau">Ngày bắt đầu <span class="required">*</span></label>
                            <input type="date" name="ngayBatDau" id="ngayBatDau" 
                                   value="<?php echo $_SESSION['form_data']['ngayBatDau'] ?? ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="ngayKetThuc">Ngày kết thúc <span class="required">*</span></label>
                            <input type="date" name="ngayKetThuc" id="ngayKetThuc" 
                                   value="<?php echo $_SESSION['form_data']['ngayKetThuc'] ?? ''; ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Days of Week -->
                <div class="form-section">
                    <h3><i class="fas fa-calendar-week"></i> Thứ trong tuần hoạt động</h3>
                    <p class="form-help">Chọn các ngày trong tuần mà lịch trình này sẽ hoạt động</p>
                    
                    <div class="days-grid">
                        <?php 
                        $days = [
                            '2' => 'Thứ 2',
                            '3' => 'Thứ 3',
                            '4' => 'Thứ 4',
                            '5' => 'Thứ 5',
                            '6' => 'Thứ 6',
                            '7' => 'Thứ 7',
                            'CN' => 'Chủ nhật'
                        ];
                        $selectedDays = isset($_SESSION['form_data']['thuTrongTuan']) ? $_SESSION['form_data']['thuTrongTuan'] : [];
                        ?>
                        <?php foreach ($days as $value => $label): ?>
                            <div class="day-checkbox">
                                <input type="checkbox" name="thuTrongTuan[]" id="day_<?php echo $value; ?>" 
                                       value="<?php echo $value; ?>" 
                                       <?php echo in_array($value, $selectedDays) ? 'checked' : ''; ?>>
                                <label for="day_<?php echo $value; ?>" class="day-label"><?php echo $label; ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="form-section">
                    <h3><i class="fas fa-cog"></i> Thông tin bổ sung</h3>
                    
                    <div class="form-group">
                        <label for="trangThai">Trạng thái</label>
                        <select name="trangThai" id="trangThai">
                            <?php foreach ($statusOptions as $key => $status): ?>
                                <option value="<?php echo $key; ?>" 
                                        <?php echo (isset($_SESSION['form_data']['trangThai']) && $_SESSION['form_data']['trangThai'] == $key) ? 'selected' : ($key == 'Hoạt động' ? 'selected' : ''); ?>>
                                    <?php echo $status; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="moTa">Mô tả</label>
                        <textarea name="moTa" id="moTa" rows="3" 
                                  placeholder="Mô tả thêm về lịch trình này..."><?php echo htmlspecialchars($_SESSION['form_data']['moTa'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Lưu lịch trình
                </button>
                <a href="<?php echo BASE_URL; ?>/schedules" class="btn btn-outline">
                    <i class="fas fa-times"></i> Hủy bỏ
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Auto-generate schedule name based on route selection
document.getElementById('maTuyenDuong').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const tenLichTrinhField = document.getElementById('tenLichTrinh');
    
    if (selectedOption.value && !tenLichTrinhField.value) {
        const routeText = selectedOption.text;
        const routeCode = routeText.split(' - ')[0];
        const timeField = document.getElementById('gioKhoiHanh');
        
        if (timeField.value) {
            tenLichTrinhField.value = `Lịch trình ${routeCode} ${timeField.value}`;
        } else {
            tenLichTrinhField.value = `Lịch trình ${routeCode}`;
        }
    }
    
    calculateEndTime();
});

document.getElementById('gioKhoiHanh').addEventListener('change', function() {
    const routeField = document.getElementById('maTuyenDuong');
    const tenLichTrinhField = document.getElementById('tenLichTrinh');
    
    if (routeField.value && this.value) {
        const selectedOption = routeField.options[routeField.selectedIndex];
        const routeCode = selectedOption.text.split(' - ')[0];
        tenLichTrinhField.value = `Lịch trình ${routeCode} ${this.value}`;
    }
    
    calculateEndTime();
});

// Set minimum date to today
document.getElementById('ngayBatDau').min = new Date().toISOString().split('T')[0];
document.getElementById('ngayKetThuc').min = new Date().toISOString().split('T')[0];

// Update end date minimum when start date changes
document.getElementById('ngayBatDau').addEventListener('change', function() {
    document.getElementById('ngayKetThuc').min = this.value;
});

// Force 24-hour format for time inputs
document.getElementById('gioKhoiHanh').setAttribute('step', '60'); 
document.getElementById('gioKetThuc').setAttribute('step', '60');

let routeData = {};
<?php foreach ($routes as $route): ?>
routeData[<?php echo $route['maTuyenDuong']; ?>] = {
    duration: '<?php echo $route['thoiGianDiChuyen']; ?>' // ví dụ: 06:30:00
};
<?php endforeach; ?>

function calculateEndTime() {
    const routeSelect = document.getElementById('maTuyenDuong');
    const departureInput = document.getElementById('gioKhoiHanh');
    const endTimeInput = document.getElementById('gioKetThuc');

    const routeId = routeSelect.value;
    const departureTime = departureInput.value;

    if (!routeId || !departureTime || !routeData[routeId]) {
        return;
    }

    const duration = routeData[routeId].duration;

    // Parse departure time (HH:MM)
    const [depHour, depMin] = departureTime.split(':').map(Number);

    // Parse duration (HH:MM:SS hoặc HH:MM)
    const parts = duration.split(':').map(Number);
    const durHour = parts[0] || 0;
    const durMin = parts[1] || 0;
    const durSec = parts[2] || 0;

    // Tính tổng
    let endHour = depHour + durHour;
    let endMin = depMin + durMin + Math.floor(durSec / 60);

    // xử lý tràn phút
    if (endMin >= 60) {
        endHour += Math.floor(endMin / 60);
        endMin = endMin % 60;
    }

    // xử lý tràn giờ
    endHour = endHour % 24;

    // Format kết quả
    const endTime = String(endHour).padStart(2, '0') + ':' + String(endMin).padStart(2, '0');
    endTimeInput.value = endTime;
}
</script>
<?php 
// Clear form data after displaying
unset($_SESSION['form_data']);
include __DIR__ . '/../layouts/footer.php'; 
?>
