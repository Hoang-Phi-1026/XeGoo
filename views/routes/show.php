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
    margin: 2rem 0;
    padding: 2rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
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
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    margin-right: 1rem;
}

.point-info h3 {
    margin: 0 0 0.5rem 0;
    font-size: 1rem;
    font-weight: 600;
    color: #495057;
}

.point-info p {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 500;
    color: #212529;
}

.journey-line {
    flex: 1;
    margin: 0 2rem;
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
    gap: 2rem;
    background: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    border: 2px solid #e9ecef;
    position: relative;
    z-index: 1;
    width: fit-content;
    margin: 0 auto;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
    color: #495057;
}

.info-item i {
    color: #6c757d;
}

.route-description {
    margin: 2rem 0;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #007bff;
}

.route-description h3 {
    margin: 0 0 1rem 0;
    color: #495057;
    font-size: 1.1rem;
}

.route-description p {
    margin: 0;
    line-height: 1.6;
    color: #6c757d;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-top: 1.5rem;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
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
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.route-code {
    background: #007bff;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-weight: 600;
    font-size: 1.1rem;
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
}
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
