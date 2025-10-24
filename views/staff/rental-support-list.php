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
    <title>Hỗ trợ thuê xe - XeGoo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/staff-rental-support.css">
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <main class="rental-support-container">
        <div class="rental-support-header">
            <div class="header-content">
                <h1 class="page-title">
                    <i class="fas fa-car"></i>
                    Hỗ trợ thuê xe
                </h1>
                <p class="page-subtitle">Quản lý yêu cầu thuê xe từ khách hàng</p>
            </div>
            <div class="header-stats">
                <div class="stat-card">
                    <span class="stat-value"><?php echo count($requests); ?></span>
                    <span class="stat-label">Chờ duyệt</span>
                </div>
            </div>
        </div>

        <div class="rental-support-content">
            <?php if (empty($requests)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>Không có yêu cầu nào</h3>
                    <p>Hiện tại không có yêu cầu thuê xe nào chờ duyệt</p>
                </div>
            <?php else: ?>
                <div class="requests-grid">
                    <?php foreach ($requests as $request): ?>
                        <div class="request-card">
                            <div class="request-header">
                                <div class="header-top">
                                    <h3 class="customer-name"><?php echo htmlspecialchars($request['hoTenNguoiThue']); ?></h3>
                                    <span class="badge badge-pending">Chờ duyệt</span>
                                </div>
                            </div>

                            <div class="request-body">
                                <div class="route-section">
                                    <div class="route-display">
                                        <span class="route-point"><?php echo htmlspecialchars($request['diemDi']); ?></span>
                                        <i class="fas fa-arrow-right"></i>
                                        <span class="route-point"><?php echo htmlspecialchars($request['diemDen']); ?></span>
                                    </div>
                                </div>

                                <div class="info-grid">
                                    <div class="info-box">
                                        <span class="info-label">
                                            <i class="fas fa-calendar-alt"></i>
                                            Ngày đi
                                        </span>
                                        <span class="info-value"><?php echo date('d/m/Y', strtotime($request['ngayDi'])); ?></span>
                                    </div>
                                    <div class="info-box">
                                        <span class="info-label">
                                            <i class="fas fa-clock"></i>
                                            Giờ đi
                                        </span>
                                        <span class="info-value"><?php echo date('H:i', strtotime($request['gioDi'])); ?></span>
                                    </div>
                                    <div class="info-box">
                                        <span class="info-label">
                                            <i class="fas fa-users"></i>
                                            Số người
                                        </span>
                                        <span class="info-value"><?php echo $request['soLuongNguoi']; ?> người</span>
                                    </div>
                                </div>

                                <?php if ($request['tenLoaiPhuongTien']): ?>
                                    <div class="vehicle-type">
                                        <i class="fas fa-shuttle-van"></i>
                                        <span><?php echo htmlspecialchars($request['tenLoaiPhuongTien']); ?></span>
                                    </div>
                                <?php endif; ?>

                                <div class="contact-section">
                                    <a href="tel:<?php echo htmlspecialchars($request['soDienThoaiNguoiThue']); ?>" class="contact-link phone">
                                        <i class="fas fa-phone"></i>
                                        <?php echo htmlspecialchars($request['soDienThoaiNguoiThue']); ?>
                                    </a>
                                    <a href="mailto:<?php echo htmlspecialchars($request['emailNguoiThue']); ?>" class="contact-link email">
                                        <i class="fas fa-envelope"></i>
                                        Email
                                    </a>
                                </div>
                            </div>

                            <div class="request-footer">
                                <a href="<?php echo BASE_URL; ?>/staff/rental-support/<?php echo $request['maThuXe']; ?>" class="btn-detail">
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
    <script>
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
