<?php include __DIR__ . '/../layouts/header.php'; ?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/driver-report.css">

<div class="driver-report-container">
    <div class="report-header">
        <h1>Báo Cáo Chuyến Đi</h1>
        <p class="driver-name">Tài xế: <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
    </div>

    <div class="upcoming-trips-section">
        <h2>Danh sách chuyến Đi Sắp Tới</h2>
        
        <?php if (empty($upcomingTrips)): ?>
            <div class="no-trips">
                <i class="fas fa-bus"></i>
                <p>Không có chuyến đi nào sắp tới</p>
            </div>
        <?php else: ?>
            <div class="trips-grid">
                <?php foreach ($upcomingTrips as $trip): ?>
                    <div class="trip-card">
                        <div class="trip-header">
                            <h3><?php echo htmlspecialchars($trip['kyHieuTuyen']); ?></h3>
                            <span class="trip-status <?php echo strtolower($trip['trangThai']); ?>">
                                <?php echo htmlspecialchars($trip['trangThai']); ?>
                            </span>
                        </div>
                        
                        <div class="trip-route">
                            <div class="route-info">
                                <i class="fas fa-map-marker-alt start"></i>
                                <span><?php echo htmlspecialchars($trip['diemDi']); ?></span>
                            </div>
                            <div class="route-arrow">
                                <i class="fas fa-long-arrow-alt-right"></i>
                            </div>
                            <div class="route-info">
                                <i class="fas fa-map-marker-alt end"></i>
                                <span><?php echo htmlspecialchars($trip['diemDen']); ?></span>
                            </div>
                        </div>
                        
                        <div class="trip-info">
                            <div class="info-row">
                                <i class="fas fa-calendar"></i>
                                <span><?php echo date('d/m/Y', strtotime($trip['ngayKhoiHanh'])); ?></span>
                            </div>
                            <div class="info-row">
                                <i class="fas fa-clock"></i>
                                <span><?php echo date('H:i', strtotime($trip['thoiGianKhoiHanh'])); ?></span>
                            </div>
                            <div class="info-row">
                                <i class="fas fa-bus"></i>
                                <span><?php echo htmlspecialchars($trip['bienSo']); ?></span>
                            </div>
                            <div class="info-row">
                                <i class="fas fa-users"></i>
                                <span><?php echo $trip['soChoDaDat']; ?>/<?php echo $trip['soChoTong']; ?> hành khách</span>
                            </div>
                        </div>
                        
                        <?php if ($trip['trangThai'] === 'Sẵn sàng'): ?>
                            <a href="<?php echo BASE_URL; ?>/driver/report/attendance/<?php echo $trip['maChuyenXe']; ?>" class="btn-attendance">
                                <i class="fas fa-clipboard-check"></i>
                                Điểm danh hành khách
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
