<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="page-header">
        <div class="page-title">
            <h1>Chỉnh sửa Lịch Trình</h1>
        </div>
        <div class="page-actions">
            <a href="<?php echo BASE_URL; ?>/schedules/<?php echo $schedule['maLichTrinh']; ?>" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="form-container">
        <form method="POST" action="<?php echo BASE_URL; ?>/schedules/<?php echo $schedule['maLichTrinh']; ?>/update" class="schedule-form">
            <div class="form-grid">
                <!-- Basic Information -->
                <div class="form-section">
                    <h3><i class="fas fa-info-circle"></i> Thông tin cơ bản</h3>
                    
                    <div class="form-group">
                        <label for="maTuyenDuong">Tuyến đường <span class="required">*</span></label>
                        <select name="maTuyenDuong_display" id="maTuyenDuong" class="form-control" disabled>
                            <?php foreach ($routes as $route): ?>
                                <option value="<?php echo $route['maTuyenDuong']; ?>" 
                                    <?php echo ($schedule['maTuyenDuong'] == $route['maTuyenDuong']) ? 'selected' : ''; ?>>
                                    <?php echo $route['diemDi'] . ' → ' . $route['diemDen']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <!-- Hidden input để form submit được giá trị -->
                        <input type="hidden" name="maTuyenDuong" value="<?php echo $schedule['maTuyenDuong']; ?>">

                    </div>

                    <div class="form-group">
                        <label for="tenLichTrinh">Tên lịch trình <span class="required">*</span></label>
                        <input type="text" name="tenLichTrinh" id="tenLichTrinh" 
                               placeholder="VD: Lịch trình sáng SG-DL" 
                               value="<?php echo htmlspecialchars($schedule['tenLichTrinh']); ?>" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="gioKhoiHanh">Giờ khởi hành <span class="required">*</span></label>
                            <input type="time" name="gioKhoiHanh" id="gioKhoiHanh" 
                                   value="<?php echo date('H:i', strtotime($schedule['gioKhoiHanh'])); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="gioKetThuc">Giờ kết thúc <span class="required">*</span></label>
                            <input type="time" name="gioKetThuc" id="gioKetThuc" 
                                   value="<?php echo date('H:i', strtotime($schedule['gioKetThuc'])); ?>" required>
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
                                   value="<?php echo $schedule['ngayBatDau']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="ngayKetThuc">Ngày kết thúc <span class="required">*</span></label>
                            <input type="date" name="ngayKetThuc" id="ngayKetThuc" 
                                   value="<?php echo $schedule['ngayKetThuc']; ?>" required>
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
                        $selectedDays = explode(',', $schedule['thuTrongTuan']);
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
                                        <?php echo ($schedule['trangThai'] == $key) ? 'selected' : ''; ?>>
                                    <?php echo $status; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="moTa">Mô tả</label>
                        <textarea name="moTa" id="moTa" rows="3" 
                                  placeholder="Mô tả thêm về lịch trình này..."><?php echo htmlspecialchars($schedule['moTa']); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Cập nhật lịch trình
                </button>
                <a href="<?php echo BASE_URL; ?>/schedules/<?php echo $schedule['maLichTrinh']; ?>" class="btn btn-outline">
                    <i class="fas fa-times"></i> Hủy bỏ
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Set minimum date to today for future schedules
const today = new Date().toISOString().split('T')[0];
const startDateField = document.getElementById('ngayBatDau');
const endDateField = document.getElementById('ngayKetThuc');

// Only set minimum date if the current start date is in the future
if (startDateField.value >= today) {
    startDateField.min = today;
}
endDateField.min = startDateField.value;

// Update end date minimum when start date changes
startDateField.addEventListener('change', function() {
    endDateField.min = this.value;
});
</script>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
