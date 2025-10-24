<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yêu cầu đã được gửi - XeGoo</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/main.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/grouprental-success.css">
</head>
<body>
    <!-- Hero Section -->
    <div class="success-hero">
        <div class="success-hero-content">
            <div class="success-icon">✓</div>
            <h1>Yêu cầu đã được gửi thành công!</h1>
            <p class="subtitle">Cảm ơn bạn đã sử dụng dịch vụ XeGoo</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <div class="success-card">
            <!-- Message Box -->
            <div class="message-box">
                <div class="message-icon">📧</div>
                <p><strong>Mã yêu cầu:</strong> <span class="request-id">#<?php echo htmlspecialchars($maThuXe); ?></span></p>
                <p>Nhân viên của chúng tôi sẽ liên hệ với bạn trong vòng <strong>24 giờ</strong> để xác nhận và cung cấp báo giá chi tiết.</p>
                <p>Vui lòng chú ý <strong>email</strong> hoặc <strong>số điện thoại</strong> để nhận thông tin.</p>
            </div>

            <!-- Info Section -->
            <?php if ($rentalRequest): ?>
                <div class="info-section">
                    <h2>Thông tin yêu cầu</h2>
                    
                    <div class="info-grid">
                        <!-- Contact Info -->
                        <div class="info-card">
                            <h3>👤 Thông tin liên hệ</h3>
                            <div class="info-row">
                                <span class="info-label">Người thuê:</span>
                                <span class="info-value"><?php echo htmlspecialchars($rentalRequest['hoTenNguoiThue']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Số điện thoại:</span>
                                <span class="info-value"><?php echo htmlspecialchars($rentalRequest['soDienThoaiNguoiThue']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Email:</span>
                                <span class="info-value"><?php echo htmlspecialchars($rentalRequest['emailNguoiThue']); ?></span>
                            </div>
                        </div>

                        <!-- Trip Info -->
                        <div class="info-card">
                            <h3>🚌 Thông tin chuyến đi</h3>
                            <div class="info-row">
                                <span class="info-label">Tuyến đường:</span>
                                <span class="info-value">
                                    <?php echo htmlspecialchars($rentalRequest['diemDi']); ?> 
                                    <span class="arrow">→</span> 
                                    <?php echo htmlspecialchars($rentalRequest['diemDen']); ?>
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Loại hành trình:</span>
                                <span class="info-value badge">
                                    <?php echo htmlspecialchars($rentalRequest['loaiHanhTrinh']); ?>
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Ngày đi:</span>
                                <span class="info-value">
                                    <?php echo date('d/m/Y', strtotime($rentalRequest['ngayDi'])); ?> 
                                    <span class="time">lúc <?php echo date('H:i', strtotime($rentalRequest['gioDi'])); ?></span>
                                </span>
                            </div>
                            <?php if ($rentalRequest['loaiHanhTrinh'] === 'Khứ hồi'): ?>
                                <div class="info-row">
                                    <span class="info-label">Ngày về:</span>
                                    <span class="info-value">
                                        <?php echo date('d/m/Y', strtotime($rentalRequest['ngayVe'])); ?> 
                                        <span class="time">lúc <?php echo date('H:i', strtotime($rentalRequest['gioVe'])); ?></span>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Vehicle Info -->
                        <div class="info-card">
                            <h3>🚗 Thông tin xe</h3>
                            <div class="info-row">
                                <span class="info-label">Loại xe:</span>
                                <span class="info-value">
                                    <?php echo htmlspecialchars($rentalRequest['tenLoaiPhuongTien']); ?>
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Số chỗ:</span>
                                <span class="info-value"><?php echo $rentalRequest['soChoMacDinh']; ?> chỗ</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Số lượng người:</span>
                                <span class="info-value"><?php echo htmlspecialchars($rentalRequest['soLuongNguoi']); ?> người</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Trạng thái:</span>
                                <span class="info-value status-badge">
                                    ⏳ <?php echo htmlspecialchars($rentalRequest['trangThai']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="<?php echo BASE_URL; ?>/group-rental" class="btn btn-secondary">
                    <span>➕</span> Gửi yêu cầu khác
                </a>
                <a href="<?php echo BASE_URL; ?>/home" class="btn btn-primary">
                    <span>🏠</span> Về trang chủ
                </a>
            </div>
        </div>
    </div>
</body>
</html>
