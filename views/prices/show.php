<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/main.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/prices.css">

<div class="page-container">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-ticket-alt"></i>
            Chi tiết giá vé
        </h1>
        <div class="page-actions">
            <a href="<?= BASE_URL ?>/prices/<?= $price['maGiaVe'] ?>/edit" class="btn btn-warning">
                <i class="fas fa-edit"></i>
                Chỉnh sửa
            </a>
            <a href="<?= BASE_URL ?>/prices" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </a>
        </div>
    </div>

    <!-- Enhanced hero section with route display -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-route"></i>
                Tuyến đường #<?= $price['maGiaVe'] ?>
            </h3>
            <div class="card-actions">
                <span class="<?= $price['trangThai'] == 'Hoạt động' ? 'status-active' : 'status-inactive' ?>">
                    <?= htmlspecialchars($price['trangThai']) ?>
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="route-display">
                <div class="route-point"><?= htmlspecialchars($price['diemDi']) ?></div>
                <i class="fas fa-arrow-right route-arrow"></i>
                <div class="route-point"><?= htmlspecialchars($price['diemDen']) ?></div>
            </div>
            
            <?php if (!empty($price['giaVeKhuyenMai'])): ?>
                <div class="price-comparison">
                    <div class="savings">
                        Tiết kiệm <?= number_format($price['giaVe'] - $price['giaVeKhuyenMai'], 0, ',', '.') ?> VNĐ
                    </div>
                    <div class="percentage">
                        (<?= round((($price['giaVe'] - $price['giaVeKhuyenMai']) / $price['giaVe']) * 100, 1) ?>% giảm giá)
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Enhanced detail grid with better visual hierarchy -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-info-circle"></i>
                Thông tin chi tiết
            </h3>
        </div>
        <div class="card-body">
            <div class="detail-grid">
                <div class="detail-section">
                    <h4 class="section-title">
                        <i class="fas fa-bus"></i>
                        Thông tin phương tiện
                    </h4>
                    <div class="detail-row">
                        <label>Ký hiệu tuyến:</label>
                        <span class="detail-value"><?= htmlspecialchars($price['kyHieuTuyen']) ?></span>
                    </div>
                    <div class="detail-row">
                        <label>Loại phương tiện:</label>
                        <!-- Fixed undefined array key by using tenLoaiPhuongTien instead of loaiPhuongTien -->
                        <span class="detail-value"><?= htmlspecialchars($price['tenLoaiPhuongTien']) ?></span>
                    </div>
                    <div class="detail-row">
                        <label>Loại chỗ ngồi:</label>
                        <span class="detail-value"><?= htmlspecialchars($price['loaiChoNgoi']) ?></span>
                    </div>
                    <div class="detail-row">
                        <label>Loại vé:</label>
                        <span class="badge badge-<?= $price['tenLoaiVe'] == 'Vé đặc biệt' ? 'danger' : ($price['tenLoaiVe'] == 'Vé khuyến mãi' ? 'warning' : 'primary') ?>">
                            <?= htmlspecialchars($price['tenLoaiVe']) ?>
                        </span>
                    </div>
                </div>

                <div class="detail-section">
                    <h4 class="section-title">
                        <i class="fas fa-money-bill-wave"></i>
                        Thông tin giá
                    </h4>
                    <div class="detail-row">
                        <label>Giá vé thường:</label>
                        <span class="detail-value price-highlight">
                            <?= number_format($price['giaVe'], 0, ',', '.') ?> VNĐ
                        </span>
                    </div>
                    <?php if (!empty($price['giaVeKhuyenMai'])): ?>
                        <div class="detail-row">
                            <label>Giá khuyến mãi:</label>
                            <span class="detail-value price-highlight text-success">
                                <?= number_format($price['giaVeKhuyenMai'], 0, ',', '.') ?> VNĐ
                            </span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="detail-section">
                    <h4 class="section-title">
                        <i class="fas fa-calendar-alt"></i>
                        Thời gian áp dụng
                    </h4>
                    <div class="detail-row">
                        <label>Ngày bắt đầu:</label>
                        <span class="detail-value"><?= date('d/m/Y', strtotime($price['ngayBatDau'])) ?></span>
                    </div>
                    <div class="detail-row">
                        <label>Ngày kết thúc:</label>
                        <span class="detail-value"><?= date('d/m/Y', strtotime($price['ngayKetThuc'])) ?></span>
                    </div>
                    <div class="detail-row">
                        <label>Thời gian còn lại:</label>
                        <span class="detail-value">
                            <?php 
                            $today = new DateTime();
                            $endDate = new DateTime($price['ngayKetThuc']);
                            $diff = $today->diff($endDate);
                            
                            if ($endDate < $today): ?>
                                <span class="time-remaining expired">
                                    <i class="fas fa-times-circle"></i>
                                    Đã hết hạn
                                </span>
                            <?php elseif ($diff->days <= 7): ?>
                                <span class="time-remaining urgent">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <?= $diff->days ?> ngày
                                </span>
                            <?php else: ?>
                                <span class="time-remaining">
                                    <i class="fas fa-clock"></i>
                                    <?= $diff->days ?> ngày
                                </span>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>

                <div class="detail-section">
                    <h4 class="section-title">
                        <i class="fas fa-cog"></i>
                        Thông tin hệ thống
                    </h4>
                    <div class="detail-row">
                        <label>Mã giá vé:</label>
                        <span class="detail-value">#<?= $price['maGiaVe'] ?></span>
                    </div>
                    <div class="detail-row">
                        <label>Ngày tạo:</label>
                        <span class="detail-value"><?= date('d/m/Y H:i:s', strtotime($price['ngayTao'])) ?></span>
                    </div>
                    <div class="detail-row">
                        <label>Trạng thái:</label>
                        <span class="<?= $price['trangThai'] == 'Hoạt động' ? 'status-active' : 'status-inactive' ?>">
                            <?= htmlspecialchars($price['trangThai']) ?>
                        </span>
                    </div>
                </div>

                <?php if (!empty($price['moTa'])): ?>
                    <div class="detail-section full-width">
                        <h4 class="section-title">
                            <i class="fas fa-file-alt"></i>
                            Mô tả chi tiết
                        </h4>
                        <div class="detail-description">
                            <?= nl2br(htmlspecialchars($price['moTa'])) ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('[v0] Enhanced price detail page loaded successfully');
    
    const detailSections = document.querySelectorAll('.detail-section');
    detailSections.forEach((section, index) => {
        section.style.animationDelay = `${index * 0.1}s`;
        section.classList.add('fadeInUp');
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
