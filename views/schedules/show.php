<?php include __DIR__ . '/../layouts/header.php'; ?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/schedules-show.css">

<div class="schedule-detail-container">
    <div class="container">
        <!-- Modern header with improved layout -->
        <div class="detail-page-header">
            <div class="header-content">
                <h1 class="detail-title">Chi Tiết Lịch Trình</h1>
                <p class="detail-subtitle">Quản lý và theo dõi thông tin lịch trình</p>
            </div>
            <div class="header-actions">
                <a href="<?php echo BASE_URL; ?>/schedules" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
                <a href="<?php echo BASE_URL; ?>/schedules/<?php echo $schedule['maLichTrinh']; ?>/edit" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Chỉnh sửa
                </a>
            </div>
        </div>

        <!-- Main schedule overview card with journey visualization -->
        <div class="schedule-hero-card">
            <div class="schedule-header-row">
                <div class="schedule-title-section">
                    <h2 class="schedule-name"><?php echo htmlspecialchars($schedule['tenLichTrinh']); ?></h2>
                    <span class="schedule-status-badge <?php echo strtolower(str_replace(' ', '-', $schedule['trangThai'])); ?>">
                        <i class="fas fa-check-circle"></i> <?php echo $schedule['trangThai']; ?>
                    </span>
                </div>
                <div class="route-badge-large">
                    <span class="route-code"><?php echo htmlspecialchars($schedule['kyHieuTuyen']); ?></span>
                </div>
            </div>

            <!-- Route journey visualization -->
            <div class="route-journey-section">
                <div class="journey-point journey-start">
                    <div class="point-marker">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="point-details">
                        <span class="point-label">Điểm Đi</span>
                        <span class="point-name"><?php echo htmlspecialchars($schedule['diemDi']); ?></span>
                    </div>
                </div>

                <div class="journey-connector">
                    <div class="connector-line"></div>
                    <div class="connector-bus">
                        <i class="fas fa-bus"></i>
                    </div>
                </div>

                <div class="journey-point journey-end">
                    <div class="point-marker">
                        <i class="fas fa-flag-checkered"></i>
                    </div>
                    <div class="point-details">
                        <span class="point-label">Điểm Đến</span>
                        <span class="point-name"><?php echo htmlspecialchars($schedule['diemDen']); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Information sections in card grid layout -->
        <div class="info-cards-grid">
            <!-- Time Information Card -->
            <div class="info-card-item">
                <div class="card-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="card-header">
                    <h3>Thông Tin Thời Gian</h3>
                </div>
                <div class="card-body">
                    <div class="time-display-section">
                        <div class="time-column">
                            <span class="time-label">Khởi Hành</span>
                            <span class="time-value"><?php echo date('H:i', strtotime($schedule['gioKhoiHanh'])); ?></span>
                        </div>
                        <div class="time-arrow">→</div>
                        <div class="time-column">
                            <span class="time-label">Kết Thúc</span>
                            <span class="time-value"><?php echo date('H:i', strtotime($schedule['gioKetThuc'])); ?></span>
                        </div>
                    </div>

                    <div class="date-range-section">
                        <i class="fas fa-calendar-alt"></i>
                        <span class="date-range-text">
                            <?php echo date('d/m/Y', strtotime($schedule['ngayBatDau'])); ?> - <?php echo date('d/m/Y', strtotime($schedule['ngayKetThuc'])); ?>
                        </span>
                    </div>

                    <div class="days-section">
                        <span class="days-label">Hoạt Động:</span>
                        <div class="days-tags">
                            <?php 
                            $days = Schedule::formatDaysOfWeek($schedule['thuTrongTuan']);
                            $dayArray = explode(', ', $days);
                            foreach($dayArray as $day): 
                            ?>
                                <span class="day-tag"><?php echo trim($day); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Driver Information Card -->
            <div class="info-card-item">
                <div class="card-icon driver-icon">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="card-header">
                    <h3>Thông Tin Tài Xế</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($schedule['tenTaiXe'])): ?>
                        <div class="driver-info-content">
                            <div class="driver-row">
                                <span class="driver-label">Tên Tài Xế</span>
                                <span class="driver-value"><?php echo htmlspecialchars($schedule['tenTaiXe']); ?></span>
                            </div>
                            <div class="driver-row">
                                <span class="driver-label">Số Điện Thoại</span>
                                <span class="driver-value driver-phone">
                                    <i class="fas fa-phone"></i> <?php echo htmlspecialchars($schedule['sdtTaiXe']); ?>
                                </span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-user-slash"></i>
                            <p>Chưa phân công tài xế</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- System Information Card -->
            <div class="info-card-item">
                <div class="card-icon system-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="card-header">
                    <h3>Thông Tin Hệ Thống</h3>
                </div>
                <div class="card-body">
                    <div class="system-row">
                        <span class="system-label">Ngày Tạo</span>
                        <span class="system-value"><?php echo date('d/m/Y H:i', strtotime($schedule['ngayTao'])); ?></span>
                    </div>
                    <div class="system-row">
                        <span class="system-label">Cập Nhật Cuối</span>
                        <span class="system-value"><?php echo date('d/m/Y H:i', strtotime($schedule['ngayCapNhat'])); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Description section full-width if exists -->
        <?php if (!empty($schedule['moTa'])): ?>
        <div class="description-card">
            <div class="card-icon">
                <i class="fas fa-file-text"></i>
            </div>
            <div class="card-header">
                <h3>Mô Tả</h3>
            </div>
            <div class="card-body">
                <p class="description-text">
                    <?php echo nl2br(htmlspecialchars($schedule['moTa'])); ?>
                </p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Action buttons section -->
        <div class="action-buttons-section">
            <h3 class="action-title">Thao Tác</h3>
            <div class="button-grid">
                <a href="<?php echo BASE_URL; ?>/schedules/<?php echo $schedule['maLichTrinh']; ?>/edit" class="action-btn btn-edit">
                    <i class="fas fa-edit"></i>
                    <div class="btn-text">
                        <span class="btn-label">Chỉnh sửa</span>
                        <span class="btn-desc">Cập nhật thông tin lịch trình</span>
                    </div>
                </a>
                <?php if ($schedule['trangThai'] != 'Ngừng'): ?>
                <button onclick="confirmDelete(<?php echo $schedule['maLichTrinh']; ?>)" class="action-btn btn-stop">
                    <i class="fas fa-stop"></i>
                    <div class="btn-text">
                        <span class="btn-label">Ngừng</span>
                        <span class="btn-desc">Dừng lịch trình này</span>
                    </div>
                </button>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>/schedules/generate-trips?schedule=<?php echo $schedule['maLichTrinh']; ?>" class="action-btn btn-generate">
                    <i class="fas fa-cogs"></i>
                    <div class="btn-text">
                        <span class="btn-label">Sinh Chuyến Xe</span>
                        <span class="btn-desc">Tạo chuyến từ lịch trình</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(scheduleId) {
    if (confirm('Bạn có chắc chắn muốn ngừng lịch trình này?')) {
        window.location.href = '<?php echo BASE_URL; ?>/schedules/' + scheduleId + '/delete';
    }
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
