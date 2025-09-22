<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="schedule-detail-page">
    <!-- Main Content -->
    <div class="container">
        <div class="page-header">
        <div class="page-title">
            <h1 class="hero-title">Chi tiết Lịch Trình</h1>
        </div>
        <div class="page-actions">
            <a href="<?php echo BASE_URL; ?>/schedules" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
            <a href="<?php echo BASE_URL; ?>/schedules/<?php echo $schedule['maLichTrinh']; ?>/edit" class="btn btn-primary">
                <i class="fas fa-edit"></i> Chỉnh sửa
            </a>
        </div>
    </div>
        <div class="schedule-content">
            <!-- Schedule Overview Card -->
            <div class="schedule-overview">
                <div class="overview-header">
                    <div class="schedule-title">
                        <h2><?php echo htmlspecialchars($schedule['tenLichTrinh']); ?></h2>
                        <span class="schedule-status <?php echo strtolower($schedule['trangThai']); ?>">
                            <?php echo $schedule['trangThai']; ?>
                        </span>
                    </div>
                    <div class="route-badge">
                        <strong><?php echo htmlspecialchars($schedule['kyHieuTuyen']); ?></strong>
                    </div>
                </div>
                
                <div class="route-journey">
                    <div class="journey-point start">
                        <div class="point-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="point-info">
                            <span class="point-label">Điểm đi</span>
                            <span class="point-name"><?php echo htmlspecialchars($schedule['diemDi']); ?></span>
                        </div>
                    </div>
                    
                    <div class="journey-line">
                        <div class="line-animated"></div>
                    </div>
                    
                    <div class="journey-point end">
                        <div class="point-icon">
                            <i class="fas fa-flag-checkered"></i>
                        </div>
                        <div class="point-info">
                            <span class="point-label">Điểm đến</span>
                            <span class="point-name"><?php echo htmlspecialchars($schedule['diemDen']); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Information Grid -->
            <div class="info-grid">
                <!-- Time Information -->
                <div class="info-card">
                    <div class="card-header">
                        <i class="fas fa-clock"></i>
                        <h3>Thông tin thời gian</h3>
                    </div>
                    <div class="card-content">
                        <div class="time-display">
                            <div class="time-item">
                                <span class="time-label">Khởi hành</span>
                                <span class="time-value"><?php echo date('H:i', strtotime($schedule['gioKhoiHanh'])); ?></span>
                            </div>
                            <div class="time-separator">→</div>
                            <div class="time-item">
                                <span class="time-label">Kết thúc</span>
                                <span class="time-value"><?php echo date('H:i', strtotime($schedule['gioKetThuc'])); ?></span>
                            </div>
                        </div>
                        
                        <div class="date-range">
                            <div class="date-item">
                                <i class="fas fa-calendar-alt"></i>
                                <span><?php echo date('d/m/Y', strtotime($schedule['ngayBatDau'])); ?> - <?php echo date('d/m/Y', strtotime($schedule['ngayKetThuc'])); ?></span>
                            </div>
                        </div>
                        
                        <div class="days-display">
                            <span class="days-label">Hoạt động:</span>
                            <div class="days-badges">
                                <?php 
                                $days = Schedule::formatDaysOfWeek($schedule['thuTrongTuan']);
                                $dayArray = explode(', ', $days);
                                foreach($dayArray as $day): 
                                ?>
                                    <span class="day-badge"><?php echo trim($day); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Information -->
                <div class="info-card">
                    <div class="card-header">
                        <i class="fas fa-info-circle"></i>
                        <h3>Thông tin hệ thống</h3>
                    </div>
                    <div class="card-content">
                        <div class="system-info">
                            <div class="system-item">
                                <span class="system-label">Ngày tạo</span>
                                <span class="system-value"><?php echo date('d/m/Y H:i', strtotime($schedule['ngayTao'])); ?></span>
                            </div>
                            <div class="system-item">
                                <span class="system-label">Cập nhật cuối</span>
                                <span class="system-value"><?php echo date('d/m/Y H:i', strtotime($schedule['ngayCapNhat'])); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description (if exists) -->
                <?php if (!empty($schedule['moTa'])): ?>
                <div class="info-card full-width">
                    <div class="card-header">
                        <i class="fas fa-file-text"></i>
                        <h3>Mô tả</h3>
                    </div>
                    <div class="card-content">
                        <div class="description-text">
                            <?php echo nl2br(htmlspecialchars($schedule['moTa'])); ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Action Buttons -->
            <div class="action-section">
                <div class="action-buttons">
                    <a href="<?php echo BASE_URL; ?>/schedules/<?php echo $schedule['maLichTrinh']; ?>/edit" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Chỉnh sửa lịch trình
                    </a>
                    <?php if ($schedule['trangThai'] != 'Ngừng'): ?>
                        <button onclick="confirmDelete(<?php echo $schedule['maLichTrinh']; ?>)" class="btn btn-danger">
                            <i class="fas fa-stop"></i> Ngừng lịch trình
                        </button>
                    <?php endif; ?>
                    <a href="<?php echo BASE_URL; ?>/schedules/generate-trips?schedule=<?php echo $schedule['maLichTrinh']; ?>>" class="btn btn-success">
                        <i class="fas fa-cogs"></i> Sinh chuyến xe
                    </a>
                </div>
            </div>
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
