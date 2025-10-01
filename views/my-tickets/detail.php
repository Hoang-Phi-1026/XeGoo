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

    <?php if (!empty($bookingDetails)): ?>
        <?php $firstTicket = $bookingDetails[0]; ?>
        
        <div class="detail-container">
            <!-- Trip Information -->
            <div class="detail-section">
                <h2 class="section-title">
                    <i class="fas fa-route"></i> Thông Tin Chuyến Đi
                </h2>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Tuyến đường:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($firstTicket['kyHieuTuyen']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Điểm đi:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($firstTicket['diemDi']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Điểm đến:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($firstTicket['diemDen']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Ngày khởi hành:</span>
                        <span class="detail-value"><?php echo date('d/m/Y', strtotime($firstTicket['thoiGianKhoiHanh'])); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Giờ khởi hành:</span>
                        <span class="detail-value"><?php echo date('H:i', strtotime($firstTicket['thoiGianKhoiHanh'])); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Biển số xe:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($firstTicket['bienSo']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Booking Information -->
            <div class="detail-section">
                <h2 class="section-title">
                    <i class="fas fa-file-invoice"></i> Thông Tin Đặt Vé
                </h2>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Mã đặt vé:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($firstTicket['maDatVe']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Ngày đặt:</span>
                        <span class="detail-value"><?php echo date('d/m/Y H:i', strtotime($firstTicket['ngayDat'])); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Loại vé:</span>
                        <span class="detail-value"><?php echo $firstTicket['loaiDatVe'] === 'KhuHoi' ? 'Khứ hồi' : 'Một chiều'; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Trạng thái:</span>
                        <span class="detail-value status-badge <?php echo $firstTicket['trangThai'] === 'DaThanhToan' ? 'status-paid' : 'status-cancelled'; ?>">
                            <?php echo $firstTicket['trangThai'] === 'DaThanhToan' ? 'Đã thanh toán' : 'Đã hủy'; ?>
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Phương thức thanh toán:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($firstTicket['phuongThucThanhToan']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Số lượng vé:</span>
                        <span class="detail-value"><?php echo count($bookingDetails); ?> vé</span>
                    </div>
                </div>
            </div>

            <!-- Passengers Information -->
            <div class="detail-section">
                <h2 class="section-title">
                    <i class="fas fa-users"></i> Thông Tin Hành Khách
                </h2>
                <div class="passengers-list">
                    <?php foreach ($bookingDetails as $index => $ticket): ?>
                        <div class="passenger-card">
                            <div class="passenger-header">
                                <h3>Hành khách <?php echo $index + 1; ?></h3>
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
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Payment Summary -->
            <div class="detail-section payment-summary">
                <h2 class="section-title">
                    <i class="fas fa-receipt"></i> Tổng Kết Thanh Toán
                </h2>
                <div class="payment-details">
                    <div class="payment-row">
                        <span>Tổng tiền gốc:</span>
                        <span><?php echo number_format($firstTicket['tongTien']); ?>đ</span>
                    </div>
                    <?php if ($firstTicket['giamGia'] > 0): ?>
                        <div class="payment-row discount">
                            <span>Giảm giá:</span>
                            <span>-<?php echo number_format($firstTicket['giamGia']); ?>đ</span>
                        </div>
                    <?php endif; ?>
                    <div class="payment-row total">
                        <span>Tổng thanh toán:</span>
                        <span><?php echo number_format($firstTicket['tongTienSauGiam']); ?>đ</span>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
