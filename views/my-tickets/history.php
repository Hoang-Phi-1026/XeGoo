<?php include __DIR__ . '/../layouts/header.php'; ?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/my-tickets.css">

<div class="my-tickets-container">
    <div class="page-header">
        <h1 class="page-title">Lịch Sử Đặt Vé</h1>
        <p class="page-subtitle">Tất cả các vé đã đặt trong quá khứ</p>
        <a href="<?php echo BASE_URL; ?>/my-tickets" class="btn-back">
            <i class="fas fa-arrow-left"></i> Quay Lại Vé Của Tôi
        </a>
    </div>

    <?php if (empty($groupedHistory)): ?>
        <div class="empty-state">
            <i class="fas fa-history"></i>
            <h2>Chưa có lịch sử</h2>
            <p>Bạn chưa có lịch sử đặt vé nào.</p>
            <a href="<?php echo BASE_URL; ?>/search" class="btn-primary">
                <i class="fas fa-search"></i> Tìm Chuyến Xe
            </a>
        </div>
    <?php else: ?>
        <div class="tickets-grid">
            <?php foreach ($groupedHistory as $bookingId => $booking): ?>
                <?php
                $isPast = strtotime($booking['booking_info']['thoiGianKhoiHanh']) < time();
                $isCancelled = $booking['booking_info']['trangThai'] === 'DaHuy';
                $statusClass = $isCancelled ? 'status-cancelled' : ($isPast ? 'status-completed' : 'status-active');
                $statusText = $isCancelled ? 'Đã hủy' : ($isPast ? 'Đã hoàn thành' : 'Còn hiệu lực');
                ?>
                <div class="ticket-card <?php echo $isCancelled ? 'cancelled' : ''; ?>">
                    <div class="ticket-header">
                        <div class="route-info">
                            <span class="city-name"><?php echo htmlspecialchars($booking['booking_info']['diemDi']); ?></span>
                            <i class="fas fa-arrow-right"></i>
                            <span class="city-name"><?php echo htmlspecialchars($booking['booking_info']['diemDen']); ?></span>
                        </div>
                        <span class="ticket-status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                    </div>

                    <div class="ticket-body">
                        <div class="ticket-main-info">
                            <div class="info-group">
                                <div class="info-item-compact">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span class="info-value"><?php echo date('d/m/Y', strtotime($booking['booking_info']['thoiGianKhoiHanh'])); ?></span>
                                </div>
                                <div class="info-item-compact">
                                    <i class="fas fa-clock"></i>
                                    <span class="info-value"><?php echo date('H:i', strtotime($booking['booking_info']['thoiGianKhoiHanh'])); ?></span>
                                </div>
                            </div>
                            
                            <div class="info-group">
                                <div class="info-item-compact">
                                    <i class="fas fa-calendar-check"></i>
                                    <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($booking['booking_info']['ngayDat'])); ?></span>
                                </div>
                                <div class="info-item-compact">
                                    <i class="fas fa-credit-card"></i>
                                    <span class="info-value"><?php echo htmlspecialchars($booking['booking_info']['phuongThucThanhToan']); ?></span>
                                </div>
                            </div>
                            
                            <div class="info-group">
                                <div class="info-item-compact">
                                    <i class="fas fa-chair"></i>
                                    <span class="info-value">
                                        <?php 
                                        $seats = array_column($booking['tickets'], 'soGhe');
                                        echo implode(', ', $seats);
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="ticket-footer-info">
                            <div class="booking-code">
                                <i class="fas fa-ticket-alt"></i>
                                <span>Mã: <?php echo htmlspecialchars($booking['booking_info']['maDatVe']); ?></span>
                            </div>
                            <div class="ticket-price">
                                <?php echo number_format($booking['booking_info']['tongTienSauGiam']); ?>đ
                            </div>
                        </div>
                    </div>

                    <div class="ticket-footer">
                        <a href="<?php echo BASE_URL; ?>/my-tickets/detail/<?php echo $bookingId; ?>" class="btn-detail">
                            <i class="fas fa-info-circle"></i> Xem Chi Tiết
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
