<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="page-header">
        <div class="page-title">
            <h1>Chi tiết Lịch Trình</h1>
        </div>
        <div class="page-actions">
            <a href="<?php echo BASE_URL; ?>/schedules" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <a href="<?php echo BASE_URL; ?>/schedules/<?php echo $schedule['maLichTrinh']; ?>/edit" class="btn btn-warning">
                <i class="fas fa-edit"></i> Chỉnh sửa
            </a>
        </div>
    </div>

    <div class="detail-container">
        <div class="detail-grid">
            <!-- Basic Information -->
            <div class="detail-section">
                <h3><i class="fas fa-info-circle"></i> Thông tin cơ bản</h3>
                <div class="detail-content">
                    <div class="detail-item">
                        <label>Tên lịch trình:</label>
                        <span><?php echo htmlspecialchars($schedule['tenLichTrinh']); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Tuyến đường:</label>
                        <span class="route-info">
                            <strong><?php echo htmlspecialchars($schedule['kyHieuTuyen']); ?></strong><br>
                            <?php echo htmlspecialchars($schedule['diemDi'] . ' → ' . $schedule['diemDen']); ?>
                        </span>
                    </div>
                    <div class="detail-item">
                        <label>Trạng thái:</label>
                        <span class="status-badge <?php echo strtolower($schedule['trangThai']); ?>">
                            <?php echo $schedule['trangThai']; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Time Information -->
            <div class="detail-section">
                <h3><i class="fas fa-clock"></i> Thông tin thời gian</h3>
                <div class="detail-content">
                    <div class="detail-item">
                        <label>Giờ khởi hành:</label>
                        <span class="time-info"><?php echo date('H:i', strtotime($schedule['gioKhoiHanh'])); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Giờ kết thúc:</label>
                        <span class="time-info"><?php echo date('H:i', strtotime($schedule['gioKetThuc'])); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Thời gian hoạt động:</label>
                        <span><?php echo date('d/m/Y', strtotime($schedule['ngayBatDau'])); ?> - <?php echo date('d/m/Y', strtotime($schedule['ngayKetThuc'])); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Thứ trong tuần:</label>
                        <span class="days-badge"><?php echo Schedule::formatDaysOfWeek($schedule['thuTrongTuan']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <?php if (!empty($schedule['moTa'])): ?>
            <div class="detail-section full-width">
                <h3><i class="fas fa-file-text"></i> Mô tả</h3>
                <div class="detail-content">
                    <p><?php echo nl2br(htmlspecialchars($schedule['moTa'])); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- System Information -->
            <div class="detail-section full-width">
                <h3><i class="fas fa-cog"></i> Thông tin hệ thống</h3>
                <div class="detail-content">
                    <div class="detail-row">
                        <div class="detail-item">
                            <label>Ngày tạo:</label>
                            <span><?php echo date('d/m/Y H:i', strtotime($schedule['ngayTao'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Cập nhật lần cuối:</label>
                            <span><?php echo date('d/m/Y H:i', strtotime($schedule['ngayCapNhat'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="detail-actions">
            <a href="<?php echo BASE_URL; ?>/schedules/<?php echo $schedule['maLichTrinh']; ?>/edit" class="btn btn-warning">
                <i class="fas fa-edit"></i> Chỉnh sửa lịch trình
            </a>
            <?php if ($schedule['trangThai'] != 'Ngừng'): ?>
                <button onclick="confirmDelete(<?php echo $schedule['maLichTrinh']; ?>)" class="btn btn-danger">
                    <i class="fas fa-stop"></i> Ngừng lịch trình
                </button>
            <?php endif; ?>
            <a href="<?php echo BASE_URL; ?>/schedules/generate-trips?schedule=<?php echo $schedule['maLichTrinh']; ?>" class="btn btn-success">
                <i class="fas fa-cogs"></i> Sinh chuyến xe từ lịch trình này
            </a>
        </div>
    </div>
</div>

<script>
function confirmDelete(scheduleId) {
    if (confirm('Bạn có chắc chắn muốn ngừng lịch trình này? Hành động này sẽ ảnh hưởng đến các chuyến xe đã được tạo.')) {
        window.location.href = '<?php echo BASE_URL; ?>/schedules/' + scheduleId + '/delete';
    }
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
