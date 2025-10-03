<?php include __DIR__ . '/../layouts/header.php'; ?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/my-tickets.css">

<div class="my-tickets-container">
    <div class="page-header">
        <h1 class="page-title">Chi Tiết Vé</h1>
        <p class="page-subtitle">Mã đặt vé: <?php echo htmlspecialchars($bookingId); ?></p>
        <a href="<?php echo BASE_URL; ?>/my-tickets" class="btn-back">
            <i class="fas fa-arrow-left"></i> Quay Lại
        </a>
    </div>

    <?php if (!empty($tripGroups)): ?>
        <div class="detail-container">
            <!-- Booking Information -->
            <div class="detail-section">
                <h2 class="section-title">
                    <i class="fas fa-file-invoice"></i> Thông Tin Đặt Vé
                </h2>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Mã đặt vé:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($bookingInfo['maDatVe']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Ngày đặt:</span>
                        <span class="detail-value"><?php echo date('d/m/Y H:i', strtotime($bookingInfo['ngayDat'])); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Loại vé:</span>
                        <span class="detail-value"><?php echo $bookingInfo['loaiDatVe'] === 'KhuHoi' ? 'Khứ hồi' : 'Một chiều'; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Trạng thái:</span>
                        <span class="detail-value status-badge <?php echo $bookingInfo['trangThai'] === 'DaThanhToan' ? 'status-paid' : 'status-cancelled'; ?>">
                            <?php echo $bookingInfo['trangThai'] === 'DaThanhToan' ? 'Đã thanh toán' : 'Đã hủy'; ?>
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Phương thức thanh toán:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($bookingInfo['phuongThucThanhToan']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Số lượng chuyến:</span>
                        <span class="detail-value"><?php echo count($tripGroups); ?> chuyến</span>
                    </div>
                </div>
            </div>

            <!-- Trip Details -->
            <?php foreach ($tripGroups as $index => $tripGroup): ?>
                <?php $tripInfo = $tripGroup['trip_info']; ?>
                <?php $tripLabel = count($tripGroups) > 1 ? ($index === 0 ? 'Chuyến Đi' : 'Chuyến Về') : 'Thông Tin Chuyến'; ?>
                
                <div class="detail-section trip-section <?php echo $index === 0 ? 'trip-outbound' : 'trip-return'; ?>">
                    <h2 class="section-title">
                        <i class="fas fa-<?php echo $index === 0 ? 'plane-departure' : 'plane-arrival'; ?>"></i> 
                        <?php echo $tripLabel; ?>
                    </h2>
                    
                    <!-- Trip Information -->
                    <div class="trip-info-card">
                        <div class="detail-grid">
                            <div class="detail-item">
                                <span class="detail-label">Tuyến đường:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($tripInfo['kyHieuTuyen']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Biển số xe:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($tripInfo['bienSo']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Điểm đi:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($tripInfo['diemDi']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Điểm đến:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($tripInfo['diemDen']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Ngày khởi hành:</span>
                                <span class="detail-value"><?php echo date('d/m/Y', strtotime($tripInfo['thoiGianKhoiHanh'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Giờ khởi hành:</span>
                                <span class="detail-value"><?php echo date('H:i', strtotime($tripInfo['thoiGianKhoiHanh'])); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Passengers for this trip -->
                    <div class="passengers-section">
                        <h3 class="passengers-title">
                            <i class="fas fa-users"></i> 
                            Hành khách (<?php echo count($tripGroup['tickets']); ?> người)
                        </h3>
                        <div class="passengers-list">
                            <?php foreach ($tripGroup['tickets'] as $passengerIndex => $ticket): ?>
                                <div class="passenger-card">
                                    <div class="passenger-header">
                                        <h4>Hành khách <?php echo $passengerIndex + 1; ?></h4>
                                        <span class="seat-badge">Ghế <?php echo htmlspecialchars($ticket['soGhe']); ?></span>
                                    </div>
                                    <div class="passenger-info">
                                        <div class="passenger-item">
                                            <i class="fas fa-user"></i>
                                            <span><?php echo htmlspecialchars($ticket['hoTenHanhKhach']); ?></span>
                                        </div>
                                        <?php if (!empty($ticket['emailHanhKhach'])): ?>
                                            <div class="passenger-item">
                                                <i class="fas fa-envelope"></i>
                                                <span><?php echo htmlspecialchars($ticket['emailHanhKhach']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($ticket['soDienThoaiHanhKhach'])): ?>
                                            <div class="passenger-item">
                                                <i class="fas fa-phone"></i>
                                                <span><?php echo htmlspecialchars($ticket['soDienThoaiHanhKhach']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <div class="passenger-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span>Điểm đón: <?php echo htmlspecialchars($ticket['diemDonTen']); ?></span>
                                        </div>
                                        <div class="passenger-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span>Điểm trả: <?php echo htmlspecialchars($ticket['diemTraTen']); ?></span>
                                        </div>
                                        <div class="passenger-item price-item">
                                            <i class="fas fa-money-bill-wave"></i>
                                            <span>Giá vé: <?php echo number_format($ticket['seatPrice']); ?>đ</span>
                                        </div>
                                    </div>

                                    <?php if (!empty($ticket['qrCode'])): ?>
                                        <div class="qr-code-container">
                                            <div class="qr-code-header">
                                                <i class="fas fa-qrcode"></i>
                                                <span>Mã vé: <?php echo htmlspecialchars($ticket['maChiTiet']); ?></span>
                                            </div>
                                            <div class="qr-code-wrapper">
                                                <img src="<?php echo $ticket['qrCode']; ?>" alt="QR Code vé" class="qr-code-image">
                                            </div>
                                            <p class="qr-code-note">
                                                <i class="fas fa-info-circle"></i>
                                                Vui lòng xuất trình mã QR này khi lên xe
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Payment Summary -->
            <div class="detail-section payment-summary">
                <h2 class="section-title">
                    <i class="fas fa-receipt"></i> Tổng Kết Thanh Toán
                </h2>
                <div class="payment-details">
                    <div class="payment-row">
                        <span>Tổng tiền gốc:</span>
                        <span><?php echo number_format($bookingInfo['tongTien']); ?>đ</span>
                    </div>
                    <?php if ($bookingInfo['giamGia'] > 0): ?>
                        <div class="payment-row discount">
                            <span>Giảm giá:</span>
                            <span>-<?php echo number_format($bookingInfo['giamGia']); ?>đ</span>
                        </div>
                    <?php endif; ?>
                    <div class="payment-row total">
                        <span>Tổng thanh toán:</span>
                        <span><?php echo number_format($bookingInfo['tongTienSauGiam']); ?>đ</span>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
