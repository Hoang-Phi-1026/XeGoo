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

    <div class="schedules-detail-container">
        <div class="schedules-detail-grid">
            <!-- Card: Basic Information -->
            <div class="schedules-detail-card">
                <div class="schedules-detail-card-header">
                    <i class="fas fa-info-circle"></i>
                    <span>Thông tin cơ bản</span>
                </div>
                <ul class="schedules-detail-list">
                    <li class="schedules-detail-item">
                        <span class="label">Tên lịch trình:</span>
                        <span><?php echo htmlspecialchars($schedule['tenLichTrinh']); ?></span>
                    </li>
                    <li class="schedules-detail-item">
                        <span class="label">Tuyến đường:</span>
                        <span class="route-info">
                            <strong><?php echo htmlspecialchars($schedule['kyHieuTuyen']); ?></strong><br>
                            <?php echo htmlspecialchars($schedule['diemDi'] . ' → ' . $schedule['diemDen']); ?>
                        </span>
                    </li>
                    <li class="schedules-detail-item">
                        <span class="label">Trạng thái:</span>
                        <span class="schedules-detail-badge <?php echo strtolower($schedule['trangThai']); ?>">
                            <?php echo $schedule['trangThai']; ?>
                        </span>
                    </li>
                </ul>
            </div>

            <!-- Card: Time Information -->
            <div class="schedules-detail-card">
                <div class="schedules-detail-card-header">
                    <i class="fas fa-clock"></i>
                    <span>Thông tin thời gian</span>
                </div>
                <ul class="schedules-detail-list">
                    <li class="schedules-detail-item">
                        <span class="label">Giờ khởi hành:</span>
                        <span><?php echo date('H:i', strtotime($schedule['gioKhoiHanh'])); ?></span>
                    </li>
                    <li class="schedules-detail-item">
                        <span class="label">Giờ kết thúc:</span>
                        <span><?php echo date('H:i', strtotime($schedule['gioKetThuc'])); ?></span>
                    </li>
                    <li class="schedules-detail-item">
                        <span class="label">Thời gian hoạt động:</span>
                        <span><?php echo date('d/m/Y', strtotime($schedule['ngayBatDau'])); ?> - <?php echo date('d/m/Y', strtotime($schedule['ngayKetThuc'])); ?></span>
                    </li>
                    <li class="schedules-detail-item">
                        <span class="label">Thứ trong tuần:</span>
                        <span class="schedules-detail-badge">
                            <?php echo Schedule::formatDaysOfWeek($schedule['thuTrongTuan']); ?>
                        </span>
                    </li>
                </ul>
            </div>

            <!-- Card: Description -->
            <?php if (!empty($schedule['moTa'])): ?>
            <div class="schedules-detail-card full-width">
                <div class="schedules-detail-card-header">
                    <i class="fas fa-file-text"></i>
                    <span>Mô tả</span>
                </div>
                <div class="schedules-detail-list">
                    <div class="schedules-detail-item">
                        <span><?php echo nl2br(htmlspecialchars($schedule['moTa'])); ?></span>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Card: System Information -->
            <div class="schedules-detail-card full-width">
                <div class="schedules-detail-card-header">
                    <i class="fas fa-cog"></i>
                    <span>Thông tin hệ thống</span>
                </div>
                <ul class="schedules-detail-list">
                    <li class="schedules-detail-item">
                        <span class="label">Ngày tạo:</span>
                        <span><?php echo date('d/m/Y H:i', strtotime($schedule['ngayTao'])); ?></span>
                    </li>
                    <li class="schedules-detail-item">
                        <span class="label">Cập nhật lần cuối:</span>
                        <span><?php echo date('d/m/Y H:i', strtotime($schedule['ngayCapNhat'])); ?></span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="schedules-detail-actions">
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
