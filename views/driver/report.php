<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="driver-report-container">
    <div class="report-header">
        <h1>Báo Cáo Chuyến Đi</h1>
        <p class="driver-name">Tài xế: <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
    </div>

    <div class="upcoming-trips-section">
        <h2>Chuyến Đi Sắp Tới</h2>
        
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

<style>
.driver-report-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.report-header {
    text-align: center;
    margin-bottom: 2rem;
}

.report-header h1 {
    font-size: 2rem;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.driver-name {
    color: #7f8c8d;
    font-size: 1.1rem;
}

.upcoming-trips-section {
    background: white;
    border-radius: 8px;
    padding: 2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.upcoming-trips-section h2 {
    color: #2c3e50;
    margin-bottom: 1.5rem;
    font-size: 1.5rem;
}

.no-trips {
    text-align: center;
    padding: 3rem;
    color: #95a5a6;
}

.no-trips i {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.trips-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
}

.trip-card {
    border: 1px solid #ecf0f1;
    border-radius: 8px;
    padding: 1.5rem;
    transition: all 0.3s;
}

.trip-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.trip-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #ecf0f1;
}

.trip-header h3 {
    color: #2c3e50;
    font-size: 1.3rem;
    margin: 0;
}

.trip-status {
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.trip-status.sẵn-sàng {
    background: #d5f4e6;
    color: #27ae60;
}

.trip-status.delay {
    background: #fff3cd;
    color: #f39c12;
}

.trip-route {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 6px;
}

.route-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex: 1;
}

.route-info i.start {
    color: #27ae60;
}

.route-info i.end {
    color: #e74c3c;
}

.route-arrow {
    color: #95a5a6;
    font-size: 1.2rem;
}

.trip-info {
    display: grid;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.info-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: #2c3e50;
}

.info-row i {
    width: 20px;
    color: #3498db;
}

.btn-attendance {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
    padding: 0.75rem;
    background: #3498db;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 600;
    transition: background 0.3s;
}

.btn-attendance:hover {
    background: #2980b9;
}

@media (max-width: 768px) {
    .driver-report-container {
        padding: 1rem;
    }
    
    .trips-grid {
        grid-template-columns: 1fr;
    }
    
    .trip-route {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .route-arrow {
        transform: rotate(90deg);
    }
}
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
