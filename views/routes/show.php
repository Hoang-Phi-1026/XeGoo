<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="page-header">
        <div class="page-title">
            <h1><i class="fas fa-route"></i> Chi tiết tuyến đường</h1>
            <p>Thông tin chi tiết tuyến đường: <?php echo htmlspecialchars($route['kyHieuTuyen']); ?></p>
        </div>
        <div class="page-actions">
            <a href="<?php echo BASE_URL; ?>/routes/<?php echo $route['maTuyenDuong']; ?>/edit" class="btn btn-warning">
                <i class="fas fa-edit"></i> Chỉnh sửa
            </a>
            <?php if ($route['trangThai'] == 'Đang hoạt động'): ?>
                <button onclick="confirmDelete(<?php echo $route['maTuyenDuong']; ?>)" class="btn btn-danger">
                    <i class="fas fa-pause"></i> Ngừng khai thác
                </button>
            <?php endif; ?>
            <a href="<?php echo BASE_URL; ?>/routes" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>
        </div>
    </div>

    <div class="detail-container">
        <div class="detail-card">
            <div class="detail-header">
                <div class="route-badge">
                    <span class="route-code"><?php echo htmlspecialchars($route['kyHieuTuyen']); ?></span>
                    <span class="status-badge <?php echo $route['trangThai'] == 'Đang hoạt động' ? 'active' : 'inactive'; ?>">
                        <?php echo $route['trangThai']; ?>
                    </span>
                </div>
            </div>

            <div class="detail-content">
                <div class="route-journey">
                    <div class="journey-point departure">
                        <div class="point-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="point-info">
                            <h3>Điểm đi</h3>
                            <p><?php echo htmlspecialchars($route['diemDi']); ?></p>
                        </div>
                    </div>
                    
                    <div class="journey-line">
                        <div class="line-info">
                            <div class="info-item">
                                <i class="fas fa-road"></i>
                                <span><?php echo $route['khoangCach']; ?> km</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-clock"></i>
                                <span><?php echo Route::formatTravelTime($route['thoiGianDiChuyen']); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="journey-point destination">
                        <div class="point-icon">
                            <i class="fas fa-flag-checkered"></i>
                        </div>
                        <div class="point-info">
                            <h3>Điểm đến</h3>
                            <p><?php echo htmlspecialchars($route['diemDen']); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Added pickup and drop-off points display section -->
                <?php if (!empty($route['points']['pickup']) || !empty($route['points']['dropoff'])): ?>
                    <div class="route-points-section">
                        <h3><i class="fas fa-map-pin"></i> Điểm đón và trả khách</h3>
                        
                        <div class="points-grid">
                            <!-- Pickup Points -->
                            <?php if (!empty($route['points']['pickup'])): ?>
                                <div class="points-column">
                                    <h4><i class="fas fa-play-circle text-success"></i> Điểm đón khách</h4>
                                    <div class="points-list">
                                        <?php foreach ($route['points']['pickup'] as $point): ?>
                                            <div class="point-card pickup">
                                                <div class="point-header">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    <h5><?php echo htmlspecialchars($point['tenDiem']); ?></h5>
                                                </div>
                                                <?php if (!empty($point['diaChi'])): ?>
                                                    <div class="point-address">
                                                        <i class="fas fa-location-arrow"></i>
                                                        <span><?php echo htmlspecialchars($point['diaChi']); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="point-status">
                                                    <span class="status-badge <?php echo $point['trangThai'] == 'Hoạt động' ? 'active' : 'inactive'; ?>">
                                                        <?php echo $point['trangThai']; ?>
                                                    </span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Drop-off Points -->
                            <?php if (!empty($route['points']['dropoff'])): ?>
                                <div class="points-column">
                                    <h4><i class="fas fa-stop-circle text-danger"></i> Điểm trả khách</h4>
                                    <div class="points-list">
                                        <?php foreach ($route['points']['dropoff'] as $point): ?>
                                            <div class="point-card dropoff">
                                                <div class="point-header">
                                                    <i class="fas fa-flag-checkered"></i>
                                                    <h5><?php echo htmlspecialchars($point['tenDiem']); ?></h5>
                                                </div>
                                                <?php if (!empty($point['diaChi'])): ?>
                                                    <div class="point-address">
                                                        <i class="fas fa-location-arrow"></i>
                                                        <span><?php echo htmlspecialchars($point['diaChi']); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="point-status">
                                                    <span class="status-badge <?php echo $point['trangThai'] == 'Hoạt động' ? 'active' : 'inactive'; ?>">
                                                        <?php echo $point['trangThai']; ?>
                                                    </span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($route['moTa'])): ?>
                    <div class="route-description">
                        <h3><i class="fas fa-info-circle"></i> Mô tả tuyến đường</h3>
                        <p><?php echo nl2br(htmlspecialchars($route['moTa'])); ?></p>
                    </div>
                <?php endif; ?>

                <div class="route-details">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Mã tuyến đường:</label>
                            <span><?php echo $route['maTuyenDuong']; ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Ký hiệu tuyến:</label>
                            <span class="route-code"><?php echo htmlspecialchars($route['kyHieuTuyen']); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Khoảng cách:</label>
                            <span><?php echo $route['khoangCach']; ?> km</span>
                        </div>
                        <div class="detail-item">
                            <label>Thời gian di chuyển:</label>
                            <span><?php echo Route::formatTravelTime($route['thoiGianDiChuyen']); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Tốc độ trung bình:</label>
                            <span>
                                <?php 
                                $timeInHours = (strtotime($route['thoiGianDiChuyen']) - strtotime('00:00:00')) / 3600;
                                $avgSpeed = $timeInHours > 0 ? round($route['khoangCach'] / $timeInHours, 1) : 0;
                                echo $avgSpeed; 
                                ?> km/h
                            </span>
                        </div>
                        <div class="detail-item">
                            <label>Trạng thái:</label>
                            <span class="status-badge <?php echo $route['trangThai'] == 'Đang hoạt động' ? 'active' : 'inactive'; ?>">
                                <?php echo $route['trangThai']; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(routeId) {
    if (confirm('Bạn có chắc chắn muốn chuyển tuyến đường này sang trạng thái ngừng khai thác?')) {
        window.location.href = '<?php echo BASE_URL; ?>/routes/' + routeId + '/delete';
    }
}
</script>

<style>
.route-journey {
    display: flex;
    align-items: center;
    margin: 1rem 0;
    padding: 1rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 10px;
    border: 1px solid #dee2e6;
}

.journey-point {
    display: flex;
    align-items: center;
    flex: 0 0 auto;
}

.journey-point.departure .point-icon {
    background: #28a745;
    color: white;
}

.journey-point.destination .point-icon {
    background: #dc3545;
    color: white;
}

.point-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    margin-right: 0.75rem;
}

.point-info h3 {
    margin: 0 0 0.25rem 0;
    font-size: 0.95rem;
    font-weight: 600;
    color: #495057;
}

.point-info p {
    margin: 0;
    font-size: 0.95rem;
    font-weight: 500;
    color: #212529;
}

.journey-line {
    flex: 1;
    margin: 0 1rem;
    position: relative;
}

.journey-line::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #28a745 0%, #ffc107 50%, #dc3545 100%);
    border-radius: 2px;
    transform: translateY(-50%);
}

.line-info {
    display: flex;
    justify-content: center;
    gap: 1rem;
    background: white;
    padding: 0.25rem 0.75rem;
    border-radius: 16px;
    border: 1px solid #e9ecef;
    position: relative;
    z-index: 1;
    width: fit-content;
    margin: 0 auto;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-weight: 500;
    color: #495057;
    font-size: 0.9rem;
}

.info-item i {
    color: #6c757d;
}

/* Added styles for pickup/drop-off points display */
.route-points-section {
    margin: 1rem 0;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.route-points-section h3 {
    margin: 0 0 0.75rem 0;
    color: #495057;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.points-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.points-column h4 {
    margin: 0 0 0.75rem 0;
    color: #495057;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.points-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.point-card {
    background: white;
    border-radius: 8px;
    padding: 0.75rem;
    border: 1px solid #dee2e6;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.point-card.pickup {
    border-left: 4px solid #28a745;
}

.point-card.dropoff {
    border-left: 4px solid #dc3545;
}

.point-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.point-header i {
    color: #6c757d;
}

.point-header h5 {
    margin: 0;
    font-size: 0.95rem;
    font-weight: 600;
    color: #212529;
}

.point-address {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    font-size: 0.85rem;
    color: #6c757d;
}

.point-address i {
    margin-top: 0.2rem;
    flex-shrink: 0;
}

.point-status {
    display: flex;
    justify-content: flex-end;
}

.text-success {
    color: #28a745 !important;
}

.text-danger {
    color: #dc3545 !important;
}

.route-description {
    margin: 1rem 0;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 3px solid #007bff;
}

.route-description h3 {
    margin: 0 0 0.75rem 0;
    color: #495057;
    font-size: 1rem;
}

.route-description p {
    margin: 0;
    line-height: 1.5;
    color: #6c757d;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 0.75rem;
    margin-top: 1rem;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0.75rem;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.detail-item label {
    font-weight: 500;
    color: #6c757d;
    margin: 0;
}

.detail-item span {
    font-weight: 600;
    color: #212529;
}

.route-badge {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.route-code {
    background: #007bff;
    color: white;
    padding: 0.35rem 0.75rem;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.95rem;
}

@media (max-width: 768px) {
    .route-journey {
        flex-direction: column;
        text-align: center;
    }
    
    .journey-line {
        margin: 1rem 0;
        width: 100%;
    }
    
    .journey-line::before {
        width: 3px;
        height: 60px;
        left: 50%;
        top: 0;
        transform: translateX(-50%);
        background: linear-gradient(180deg, #28a745 0%, #ffc107 50%, #dc3545 100%);
    }
    
    .line-info {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .points-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
}
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
