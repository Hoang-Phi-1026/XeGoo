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
    <style>
        /* Enhanced dashboard layout with better visual hierarchy */
        .dashboard-header {
            margin-bottom: 2.5rem;
        }

        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2.5rem;
        }

        .stat-card-enhanced {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.75rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--neutral-200);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card-enhanced::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
        }

        .stat-card-enhanced:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card-enhanced .stat-icon {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 0.75rem;
        }

        .stat-card-enhanced .stat-label {
            font-size: 0.875rem;
            color: var(--neutral-600);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .stat-card-enhanced .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--neutral-900);
            line-height: 1;
        }

        .stat-card-enhanced .stat-subtitle {
            font-size: 0.75rem;
            color: var(--neutral-500);
            margin-top: 0.75rem;
        }

        /* Dashboard sections with improved styling */
        .dashboard-sections {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .dashboard-section {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--neutral-200);
            overflow: hidden;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .dashboard-section:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }

        .section-header {
            padding: 1.5rem;
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary-color) 100%);
            color: white;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .section-header-icon {
            font-size: 1.75rem;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.2);
            border-radius: var(--radius-md);
            flex-shrink: 0;
        }

        .section-header-text h3 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .section-header-text p {
            margin: 0.25rem 0 0 0;
            font-size: 0.875rem;
            opacity: 0.9;
        }

        .section-content {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .stats-display {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 1rem;
        }

        .stat-box {
            text-align: center;
            padding: 1.25rem;
            background: var(--neutral-50);
            border-radius: var(--radius-md);
            border: 1px solid var(--neutral-200);
            transition: all 0.2s ease;
        }

        .stat-box:hover {
            background: var(--neutral-100);
            transform: translateY(-2px);
        }

        .stat-box-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary-color);
            line-height: 1;
            margin-bottom: 0.5rem;
        }

        .stat-box-label {
            font-size: 0.75rem;
            color: var(--neutral-600);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .trips-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            max-height: 350px;
            overflow-y: auto;
        }

        .trip-item {
            padding: 1rem;
            background: var(--neutral-50);
            border-radius: var(--radius-md);
            border-left: 4px solid var(--primary-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.2s ease;
        }

        .trip-item:hover {
            background: var(--neutral-100);
            transform: translateX(4px);
            box-shadow: var(--shadow-sm);
        }

        .trip-item-info h4 {
            margin: 0 0 0.25rem 0;
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--neutral-900);
        }

        .trip-item-info p {
            margin: 0;
            font-size: 0.8rem;
            color: var(--neutral-600);
        }

        .trip-item-time {
            text-align: right;
            font-weight: 700;
            color: var(--primary-color);
            font-size: 0.9rem;
            white-space: nowrap;
            margin-left: 1rem;
        }

        .empty-section {
            text-align: center;
            padding: 2rem 1rem;
            color: var(--neutral-500);
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .empty-section i {
            font-size: 2.5rem;
            color: var(--neutral-300);
            margin-bottom: 0.75rem;
        }

        .empty-section p {
            margin: 0;
            font-size: 0.9rem;
        }

        /* Completed trips section styling */
        .completed-trips-section {
            margin-top: 2.5rem;
            padding-top: 2.5rem;
            border-top: 2px solid var(--neutral-200);
        }

        .section-title-main {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--neutral-900);
            margin: 0 0 1.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-title-main i {
            color: var(--success-color);
        }

        .completed-trips-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.25rem;
        }

        .completed-trip-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.25rem;
            border: 1px solid var(--neutral-200);
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
            border-left: 4px solid var(--success-color);
        }

        .completed-trip-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .completed-trip-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .completed-trip-route {
            flex-grow: 1;
        }

        .completed-trip-route h4 {
            margin: 0 0 0.25rem 0;
            font-size: 1rem;
            font-weight: 700;
            color: var(--neutral-900);
        }

        .completed-trip-route p {
            margin: 0;
            font-size: 0.85rem;
            color: var(--neutral-600);
        }

        .completed-trip-badge {
            background: var(--success-color);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: var(--radius-md);
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            white-space: nowrap;
            margin-left: 0.75rem;
        }

        .completed-trip-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--neutral-200);
        }

        .completed-trip-info-item {
            display: flex;
            flex-direction: column;
        }

        .completed-trip-info-label {
            font-size: 0.75rem;
            color: var(--neutral-500);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .completed-trip-info-value {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--neutral-900);
        }

        .completed-trip-driver {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.85rem;
            color: var(--neutral-600);
        }

        .completed-trip-driver i {
            color: var(--primary-color);
        }

        @media (max-width: 768px) {
            .dashboard-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .dashboard-sections {
                grid-template-columns: 1fr;
            }

            .stats-display {
                grid-template-columns: repeat(3, 1fr);
            }

            .trip-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .trip-item-time {
                text-align: left;
                margin-top: 0.5rem;
                margin-left: 0;
            }

            .completed-trips-grid {
                grid-template-columns: 1fr;
            }

            .completed-trip-header {
                flex-direction: column;
            }

            .completed-trip-badge {
                margin-left: 0;
                margin-top: 0.5rem;
                align-self: flex-start;
            }
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <main class="monitoring-container">
        <!-- Enhanced header with better visual hierarchy -->
        <div class="monitoring-header">
            <div class="header-content">
                <h1 class="page-title">
                    <i class="fas fa-chart-line"></i>
                    Giám sát chuyến xe
                </h1>
                <p class="page-subtitle">Theo dõi và quản lý các chuyến xe hôm nay</p>
            </div>
        </div>

        <!-- Dashboard statistics cards -->
        <div class="dashboard-stats">
            <div class="stat-card-enhanced">
                <div class="stat-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-label">Tổng chuyến hôm nay</div>
                <div class="stat-value"><?php echo $tripStats['totalTripsToday'] ?? 0; ?></div>
                <div class="stat-subtitle">Chuyến có lịch trình</div>
            </div>

            <div class="stat-card-enhanced">
                <div class="stat-icon" style="color: var(--info-color);">
                    <i class="fas fa-hourglass-start"></i>
                </div>
                <div class="stat-label">Chờ khởi hành</div>
                <div class="stat-value" style="color: var(--info-color);">
                    <?php echo (($tripStats['totalTripsToday'] ?? 0) - ($tripStats['departedTrips'] ?? 0) - ($tripStats['completedTrips'] ?? 0)); ?>
                </div>
                <div class="stat-subtitle">Chuyến chưa khởi hành</div>
            </div>

            <div class="stat-card-enhanced">
                <div class="stat-icon" style="color: var(--warning-color);">
                    <i class="fas fa-bus"></i>
                </div>
                <div class="stat-label">Đang di chuyển</div>
                <div class="stat-value" style="color: var(--warning-color);">
                    <?php echo $tripStats['departedTrips'] ?? 0; ?>
                </div>
                <div class="stat-subtitle">Chuyến đã khởi hành</div>
            </div>

            <div class="stat-card-enhanced">
                <div class="stat-icon" style="color: var(--success-color);">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-label">Hoàn thành</div>
                <div class="stat-value" style="color: var(--success-color);">
                    <?php echo $tripStats['completedTrips'] ?? 0; ?>
                </div>
                <div class="stat-subtitle">Chuyến đã hoàn thành</div>
            </div>
        </div>

        <!-- Dashboard sections for trip management -->
        <div class="dashboard-sections">
            <!-- Section 1: Trips Waiting for Departure -->
            <div class="dashboard-section">
                <div class="section-header">
                    <div class="section-header-icon">
                        <i class="fas fa-hourglass-start"></i>
                    </div>
                    <div class="section-header-text">
                        <h3>Chuyến xe chờ khởi hành</h3>
                        <p>Chờ duyệt khởi hành</p>
                    </div>
                </div>
                <div class="section-content">
                    <?php if (empty($reports)): ?>
                        <div class="empty-section">
                            <i class="fas fa-check-circle"></i>
                            <p>Không có chuyến xe nào chờ duyệt</p>
                        </div>
                    <?php else: ?>
                        <div class="trips-list">
                            <?php foreach (array_slice($reports, 0, 5) as $report): ?>
                                <div class="trip-item">
                                    <div class="trip-item-info">
                                        <h4><?php echo htmlspecialchars($report['kyHieuTuyen']); ?></h4>
                                        <p><?php echo htmlspecialchars($report['diemDi']); ?> → <?php echo htmlspecialchars($report['diemDen']); ?></p>
                                    </div>
                                    <div class="trip-item-time">
                                        <?php echo date('H:i', strtotime($report['thoiGianKhoiHanh'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (count($reports) > 5): ?>
                                <div style="text-align: center; padding: 0.75rem; color: var(--neutral-600); font-size: 0.875rem;">
                                    +<?php echo count($reports) - 5; ?> chuyến khác
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Section 2: Approved Departing Trips -->
            <div class="dashboard-section">
                <div class="section-header">
                    <div class="section-header-icon">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div class="section-header-text">
                        <h3>Chuyến xe đã duyệt</h3>
                        <p>Đã xác nhận khởi hành</p>
                    </div>
                </div>
                <div class="section-content">
                    <?php if (empty($approvedTrips)): ?>
                        <div class="empty-section">
                            <i class="fas fa-inbox"></i>
                            <p>Chưa có chuyến xe nào được duyệt</p>
                        </div>
                    <?php else: ?>
                        <div class="trips-list">
                            <?php foreach (array_slice($approvedTrips, 0, 5) as $trip): ?>
                                <div class="trip-item" style="border-left-color: var(--success-color);">
                                    <div class="trip-item-info">
                                        <h4><?php echo htmlspecialchars($trip['kyHieuTuyen']); ?></h4>
                                        <p><?php echo htmlspecialchars($trip['diemDi']); ?> → <?php echo htmlspecialchars($trip['diemDen']); ?></p>
                                    </div>
                                    <div class="trip-item-time" style="color: var(--success-color);">
                                        <?php echo date('H:i', strtotime($trip['thoiGianKhoiHanh'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (count($approvedTrips) > 5): ?>
                                <div style="text-align: center; padding: 0.75rem; color: var(--neutral-600); font-size: 0.875rem;">
                                    +<?php echo count($approvedTrips) - 5; ?> chuyến khác
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Section 3: Completed Trips -->
            <div class="dashboard-section">
                <div class="section-header" style="background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);">
                    <div class="section-header-icon">
                        <i class="fas fa-flag-checkered"></i>
                    </div>
                    <div class="section-header-text">
                        <h3>Chuyến xe hoàn thành</h3>
                        <p>Đã kết thúc hôm nay</p>
                    </div>
                </div>
                <div class="section-content">
                    <?php if (empty($completedTrips)): ?>
                        <div class="empty-section">
                            <i class="fas fa-inbox"></i>
                            <p>Chưa có chuyến xe nào hoàn thành</p>
                        </div>
                    <?php else: ?>
                        <div class="trips-list">
                            <?php foreach (array_slice($completedTrips, 0, 5) as $trip): ?>
                                <div class="trip-item" style="border-left-color: var(--success-color);">
                                    <div class="trip-item-info">
                                        <h4><?php echo htmlspecialchars($trip['kyHieuTuyen']); ?></h4>
                                        <p><?php echo htmlspecialchars($trip['diemDi']); ?> → <?php echo htmlspecialchars($trip['diemDen']); ?></p>
                                    </div>
                                    <div class="trip-item-time" style="color: var(--success-color);">
                                        <?php echo date('H:i', strtotime($trip['thoiGianKetThuc'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (count($completedTrips) > 5): ?>
                                <div style="text-align: center; padding: 0.75rem; color: var(--neutral-600); font-size: 0.875rem;">
                                    +<?php echo count($completedTrips) - 5; ?> chuyến khác
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Detailed list section for pending reports -->
        <div class="completed-trips-section">
            <h2 class="section-title-main">
                <i class="fas fa-list"></i>
                Danh sách chi tiết chuyến xe chờ duyệt
            </h2>

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

                                    <div class="passenger-stats">
                                        <div class="present">
                                            <i class="fas fa-check-circle"></i>
                                            <div class="stat-text">
                                                <span class="stat-label">Có mặt</span>
                                                <span class="stat-count"><?php echo $report['soHanhKhachCoMat']; ?></span>
                                            </div>
                                        </div>
                                        <div class="absent">
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
                                    <a href="<?php echo BASE_URL; ?>/staff/monitoring/<?php echo urlencode(IDEncryptionHelper::encryptId($report['maBaoCao'])); ?>" 
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
