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
    <title>Giám sát chuyến xe - XeGoo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/staff-monitoring.css">
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <main class="monitoring-container">
        <div class="monitoring-header">
            <div class="header-content">
                <h1 class="page-title">
                    <i class="fas fa-users-check"></i>
                    Giám sát chuyến xe
                </h1>
                <p class="page-subtitle">Danh sách báo cáo chuyến đi chờ khởi hành hôm nay</p>
            </div>
            <div class="header-stats">
                <div class="stat-card">
                    <span class="stat-label">Tổng chuyến</span>
                    <span class="stat-value"><?php echo count($reports); ?></span>
                </div>
            </div>
        </div>

        <div class="monitoring-content">
            <?php if (empty($reports)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>Không có báo cáo nào</h3>
                    <p>Hiện tại không có chuyến xe nào chờ khởi hành</p>
                </div>
            <?php else: ?>
                <div class="reports-grid">
                    <?php foreach ($reports as $report): ?>
                        <div class="report-card">
                            <div class="report-header">
                                <div class="route-info">
                                    <h3 class="route-name"><?php echo htmlspecialchars($report['kyHieuTuyen']); ?></h3>
                                    <p class="route-details">
                                        <?php echo htmlspecialchars($report['diemDi']); ?> → 
                                        <?php echo htmlspecialchars($report['diemDen']); ?>
                                    </p>
                                </div>
                                <div class="status-badge">
                                    <span class="badge badge-waiting">Chờ khởi hành</span>
                                </div>
                            </div>

                            <div class="report-body">
                                <div class="info-row">
                                    <div class="info-item">
                                        <span class="info-label">
                                            <i class="fas fa-calendar"></i>
                                            Ngày
                                        </span>
                                        <span class="info-value">
                                            <?php echo date('d/m/Y', strtotime($report['ngayKhoiHanh'])); ?>
                                        </span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">
                                            <i class="fas fa-clock"></i>
                                            Giờ khởi hành
                                        </span>
                                        <span class="info-value">
                                            <?php echo date('H:i', strtotime($report['thoiGianKhoiHanh'])); ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Passenger stats section - now properly visible with correct structure -->
                                <div class="passenger-stats">
                                    <div class="present">
                                        <i class="fas fa-check-circle"></i>
                                        <div class="stat-text">
                                            <span class="stat-label">Có mặt</span>
                                            <span class="stat-count"><?php echo $report['soHanhKhachCoMat']; ?></span>
                                        </div>
                                    </div>
                                    <div class=" absent">
                                        <i class="fas fa-times-circle"></i>
                                        <div class="stat-text">
                                            <span class="stat-label">Vắng</span>
                                            <span class="stat-count"><?php echo $report['soHanhKhachVang']; ?></span>
                                        </div>
                                    </div>
                                    <div class="total">
                                        <i class="fas fa-users"></i>
                                        <div class="stat-text">
                                            <span class="stat-label">Tổng</span>
                                            <span class="stat-count"><?php echo $report['tongSoHanhKhach']; ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="driver-info">
                                    <span class="driver-label">Tài xế:</span>
                                    <span class="driver-name"><?php echo htmlspecialchars($report['tenTaiXe']); ?></span>
                                    <span class="driver-phone">
                                        <i class="fas fa-phone"></i>
                                        <?php echo htmlspecialchars($report['sdtTaiXe']); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="report-footer">
                                <a href="<?php echo BASE_URL; ?>/staff/monitoring/<?php echo $report['maBaoCao']; ?>" 
                                   class="btn-detail">
                                    <i class="fas fa-eye"></i>
                                    Xem chi tiết
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>

    <script src="<?php echo BASE_URL; ?>/public/js/notifications.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/js/theme-toggle.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/js/unified-search.js"></script>
    <script>
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
        if (isset($_SESSION['warning'])) {
            echo 'showWarning("' . addslashes($_SESSION['warning']) . '");';
            unset($_SESSION['warning']);
        }
        if (isset($_SESSION['info'])) {
            echo 'showInfo("' . addslashes($_SESSION['info']) . '");';
            unset($_SESSION['info']);
        }
        ?>
    </script>
</body>
</html>
