<?php include __DIR__ . '/../layouts/header.php'; ?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/my-tickets.css">

<div class="my-tickets-container">
    <div class="page-header">
        <h1 class="page-title">Vé Của Tôi</h1>
        <p class="page-subtitle">Danh sách vé chờ khởi hành</p>
        <a href="<?php echo BASE_URL; ?>/my-tickets/history" class="btn-history">
            <i class="fas fa-history"></i> Xem Lịch Sử Đặt Vé
        </a>
    </div>

    <?php if (empty($groupedTickets)): ?>
        <div class="empty-state">
            <i class="fas fa-ticket-alt"></i>
            <h2>Chưa có vé nào</h2>
            <p>Bạn chưa có vé nào chờ khởi hành. Hãy đặt vé ngay!</p>
            <a href="<?php echo BASE_URL; ?>/search" class="btn-primary">
                <i class="fas fa-search"></i> Tìm Chuyến Xe
            </a>
        </div>
    <?php else: ?>
        <div class="tickets-grid">
            <?php foreach ($groupedTickets as $bookingId => $booking): ?>
                <div class="ticket-card-modern">
                    <div class="ticket-image">
                        <img src="<?php echo BASE_URL; ?>/public/images/bus-placeholder.jpg" alt="Bus" onerror="this.src='https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?w=400&h=250&fit=crop'">
                        <div class="ticket-status-overlay status-active">
                            <i class="fas fa-check-circle"></i> Chờ khởi hành
                        </div>
                    </div>
                    
                    <div class="ticket-content">
                        <div class="ticket-route">
                            <div class="route-cities">
                                <span class="city-from"><?php echo htmlspecialchars($booking['booking_info']['diemDi']); ?></span>
                                <div class="route-arrow">
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                                <span class="city-to"><?php echo htmlspecialchars($booking['booking_info']['diemDen']); ?></span>
                            </div>
                        </div>

                        <div class="ticket-details">
                            <div class="detail-row">
                                <div class="detail-col">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span><?php echo date('d/m/Y', strtotime($booking['booking_info']['thoiGianKhoiHanh'])); ?></span>
                                </div>
                                <div class="detail-col">
                                    <i class="fas fa-clock"></i>
                                    <span><?php echo date('H:i', strtotime($booking['booking_info']['thoiGianKhoiHanh'])); ?></span>
                                </div>
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-col">
                                    <i class="fas fa-bus"></i>
                                    <span><?php echo htmlspecialchars($booking['booking_info']['bienSo']); ?></span>
                                </div>
                                <div class="detail-col">
                                    <i class="fas fa-chair"></i>
                                    <span>
                                        <?php 
                                        $seats = array_column($booking['tickets'], 'soGhe');
                                        echo implode(', ', $seats);
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="ticket-footer-modern">
                            <div class="booking-info">
                                <span class="booking-code">
                                    <i class="fas fa-ticket-alt"></i>
                                    <?php echo htmlspecialchars($booking['booking_info']['maDatVe']); ?>
                                </span>
                                <span class="ticket-price-modern">
                                    <?php echo number_format($booking['booking_info']['tongTienSauGiam']); ?>đ
                                </span>
                            </div>
                            <?php 
                            require_once __DIR__ . '/../../helpers/IDEncryptionHelper.php';
                            $encryptedBookingId = IDEncryptionHelper::encryptId($bookingId);
                            ?>
                            <a href="<?php echo BASE_URL; ?>/my-tickets/detail/<?php echo $encryptedBookingId; ?>" class="btn-view-detail">
                                Xem Chi Tiết <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
