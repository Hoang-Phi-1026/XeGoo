<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/config.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết báo cáo - XeGoo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/main.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/header.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/footer.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/staff-monitoring.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>
    
    <main class="monitoring-detail-container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="<?php echo BASE_URL; ?>/staff/monitoring" class="breadcrumb-link">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách
            </a>
        </div>

        <!-- Trip Header -->
        <div class="detail-header">
            <div class="trip-header-info">
                <h1 class="trip-route">
                    <?php echo htmlspecialchars($report['kyHieuTuyen']); ?>
                </h1>
                <p class="trip-route-details">
                    <i class="fas fa-map-marker-alt"></i>
                    <?php echo htmlspecialchars($report['diemDi']); ?> → 
                    <?php echo htmlspecialchars($report['diemDen']); ?>
                </p>
            </div>
            <div class="trip-status">
                <span class="badge badge-waiting">Chờ khởi hành</span>
            </div>
        </div>

        <div class="detail-content">
            <!-- Trip Information Section -->
            <section class="info-section">
                <h2 class="section-title">
                    <i class="fas fa-info-circle"></i>
                    Thông tin chuyến xe
                </h2>
                <div class="info-grid">
                    <div class="info-card">
                        <span class="info-label">Ngày khởi hành</span>
                        <span class="info-value">
                            <?php echo date('d/m/Y', strtotime($report['ngayKhoiHanh'])); ?>
                        </span>
                    </div>
                    <div class="info-card">
                        <span class="info-label">Giờ khởi hành</span>
                        <span class="info-value">
                            <?php echo date('H:i', strtotime($report['thoiGianKhoiHanh'])); ?>
                        </span>
                    </div>
                    <div class="info-card">
                        <span class="info-label">Giờ kết thúc dự kiến</span>
                        <span class="info-value">
                            <?php echo date('H:i', strtotime($report['thoiGianKetThuc'])); ?>
                        </span>
                    </div>
                    <div class="info-card">
                        <span class="info-label">Biển số xe</span>
                        <span class="info-value">
                            <?php echo htmlspecialchars($report['bienSo']); ?>
                        </span>
                    </div>
                    <div class="info-card">
                        <span class="info-label">Loại xe</span>
                        <span class="info-value">
                            <?php echo htmlspecialchars($report['tenLoaiPhuongTien']); ?>
                        </span>
                    </div>
                    <div class="info-card">
                        <span class="info-label">Tổng ghế</span>
                        <span class="info-value">
                            <?php echo $report['soChoTong']; ?> ghế
                        </span>
                    </div>
                </div>
            </section>

            <!-- Driver Information Section -->
            <section class="info-section">
                <h2 class="section-title">
                    <i class="fas fa-user-tie"></i>
                    Thông tin tài xế
                </h2>
                <div class="driver-card">
                    <div class="driver-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="driver-details">
                        <h3 class="driver-name"><?php echo htmlspecialchars($report['tenTaiXe']); ?></h3>
                        <div class="driver-contact">
                            <p>
                                <i class="fas fa-phone"></i>
                                <a href="tel:<?php echo htmlspecialchars($report['sdtTaiXe']); ?>">
                                    <?php echo htmlspecialchars($report['sdtTaiXe']); ?>
                                </a>
                            </p>
                            <p>
                                <i class="fas fa-envelope"></i>
                                <a href="mailto:<?php echo htmlspecialchars($report['emailTaiXe']); ?>">
                                    <?php echo htmlspecialchars($report['emailTaiXe']); ?>
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Passenger Statistics Section -->
            <section class="info-section">
                <h2 class="section-title">
                    <i class="fas fa-chart-bar"></i>
                    Thống kê hành khách
                </h2>
                <div class="stats-grid">
                    <div class="stat-card stat-present">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-label">Có mặt</span>
                            <span class="stat-value"><?php echo $report['soHanhKhachCoMat']; ?></span>
                        </div>
                    </div>
                    <div class="stat-card stat-absent">
                        <div class="stat-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-label">Vắng mặt</span>
                            <span class="stat-value"><?php echo $report['soHanhKhachVang']; ?></span>
                        </div>
                    </div>
                    <div class="stat-card stat-total">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-label">Tổng hành khách</span>
                            <span class="stat-value"><?php echo $report['tongSoHanhKhach']; ?></span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Passenger List Section -->
            <section class="info-section">
                <h2 class="section-title">
                    <i class="fas fa-list"></i>
                    Danh sách hành khách
                </h2>
                
                <?php if (empty($passengers)): ?>
                    <div class="empty-state">
                        <p>Không có hành khách nào</p>
                    </div>
                <?php else: ?>
                    <div class="passengers-container">
                        <!-- Present Passengers -->
                        <div class="passenger-group">
                            <h3 class="group-title">
                                <i class="fas fa-check-circle"></i>
                                Hành khách có mặt (<?php echo count(array_filter($passengers, fn($p) => $p['trangThai'] === 'Đã lên xe')); ?>)
                            </h3>
                            <div class="passenger-list">
                                <?php foreach ($passengers as $passenger): ?>
                                    <?php if ($passenger['trangThai'] === 'Đã lên xe'): ?>
                                        <div class="passenger-item present">
                                            <div class="passenger-seat">
                                                <span class="seat-number">Ghế <?php echo $passenger['soGhe']; ?></span>
                                            </div>
                                            <div class="passenger-info">
                                                <h4 class="passenger-name">
                                                    <?php echo htmlspecialchars($passenger['hoTenHanhKhach']); ?>
                                                </h4>
                                                <p class="passenger-phone">
                                                    <i class="fas fa-phone"></i>
                                                    <a href="tel:<?php echo htmlspecialchars($passenger['soDienThoaiHanhKhach']); ?>">
                                                        <?php echo htmlspecialchars($passenger['soDienThoaiHanhKhach']); ?>
                                                    </a>
                                                </p>
                                                <p class="passenger-route">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    <?php echo htmlspecialchars($passenger['diemDonTen']); ?> → 
                                                    <?php echo htmlspecialchars($passenger['diemTraTen']); ?>
                                                </p>
                                            </div>
                                            <div class="passenger-status">
                                                <span class="badge badge-present">Đã lên xe</span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Absent Passengers -->
                        <div class="passenger-group">
                            <h3 class="group-title">
                                <i class="fas fa-times-circle"></i>
                                Hành khách vắng mặt (<?php echo count(array_filter($passengers, fn($p) => $p['trangThai'] === 'Vắng mặt')); ?>)
                            </h3>
                            <div class="passenger-list">
                                <?php foreach ($passengers as $passenger): ?>
                                    <?php if ($passenger['trangThai'] === 'Vắng mặt'): ?>
                                        <div class="passenger-item absent">
                                            <div class="passenger-seat">
                                                <span class="seat-number">Ghế <?php echo $passenger['soGhe']; ?></span>
                                            </div>
                                            <div class="passenger-info">
                                                <h4 class="passenger-name">
                                                    <?php echo htmlspecialchars($passenger['hoTenHanhKhach']); ?>
                                                </h4>
                                                <p class="passenger-phone">
                                                    <i class="fas fa-phone"></i>
                                                    <a href="tel:<?php echo htmlspecialchars($passenger['soDienThoaiHanhKhach']); ?>">
                                                        <?php echo htmlspecialchars($passenger['soDienThoaiHanhKhach']); ?>
                                                    </a>
                                                </p>
                                                <p class="passenger-route">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    <?php echo htmlspecialchars($passenger['diemDonTen']); ?> → 
                                                    <?php echo htmlspecialchars($passenger['diemTraTen']); ?>
                                                </p>
                                            </div>
                                            <div class="passenger-status">
                                                <span class="badge badge-absent">Vắng mặt</span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Action Section -->
            <section class="action-section">
                <button id="confirmDepartureBtn" class="btn-confirm-departure">
                    <i class="fas fa-check"></i>
                    Xác nhận khởi hành
                </button>
                <a href="<?php echo BASE_URL; ?>/staff/monitoring" class="btn-cancel">
                    <i class="fas fa-times"></i>
                    Hủy
                </a>
            </section>
        </div>
    </main>

    <!-- Added confirmation modal for departure -->
    <div id="confirmModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h2 class="modal-title">Xác nhận khởi hành</h2>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xác nhận khởi hành chuyến xe <strong><?php echo htmlspecialchars($report['kyHieuTuyen']); ?></strong> không?</p>
                <p>Hành động này không thể hoàn tác.</p>
            </div>
            <div class="modal-footer">
                <button class="modal-btn modal-btn-cancel" onclick="closeConfirmModal()">Hủy</button>
                <button class="modal-btn modal-btn-confirm" onclick="confirmDeparture()">Xác nhận</button>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>
    
    <script src="<?php echo BASE_URL; ?>/public/js/notifications.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/js/theme-toggle.js"></script>
    <script>
        const reportId = <?php echo json_encode(IDEncryptionHelper::encryptId($report['maBaoCao'])); ?>;
        const baseUrl = '<?php echo BASE_URL; ?>';

        function openConfirmModal() {
            document.getElementById('confirmModal').classList.add('active');
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').classList.remove('active');
        }

        function confirmDeparture() {
            closeConfirmModal();
            performConfirmDeparture();
        }

        async function performConfirmDeparture() {
            try {
                const response = await fetch(baseUrl + '/staff/monitoring/confirm-departure', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        reportId: reportId
                    })
                });

                const data = await response.json();

                if (data.success) {
                    showSuccess('Đã xác nhận khởi hành thành công!');
                    setTimeout(() => {
                        window.location.href = baseUrl + '/staff/monitoring';
                    }, 1500);
                } else {
                    showError(data.message || 'Có lỗi xảy ra');
                }
            } catch (error) {
                console.error('Error:', error);
                showError('Có lỗi xảy ra khi xác nhận khởi hành');
            }
        }

        document.getElementById('confirmDepartureBtn').addEventListener('click', openConfirmModal);

        // Close modal when clicking outside
        document.getElementById('confirmModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeConfirmModal();
            }
        });

        // Display session messages
        <?php
        if (isset($_SESSION['success'])) {
            echo 'showSuccess("' . addslashes($_SESSION['success']) . '");';
            unset($_SESSION['success']);
        }
        if (isset($_SESSION['error'])) {
            echo 'showError("' . addslashes($_SESSION['error']) . '");';
            unset($_SESSION['error']);
        }
        ?>
    </script>
</body>
</html>
