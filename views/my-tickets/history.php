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
    <div class="filter-section">
        <div class="filter-header">
            <i class="fas fa-filter"></i>
            <span>Lọc theo trạng thái</span>
        </div>
        <div class="filter-buttons">
            <button class="filter-btn active" data-status="all">
                <i class="fas fa-list"></i> Tất cả
            </button>
            <button class="filter-btn" data-status="active">
                <i class="fas fa-check-circle"></i> Chờ khởi hành
            </button>
            <button class="filter-btn" data-status="completed">
                <i class="fas fa-check-double"></i> Đã hoàn thành
            </button>
            <button class="filter-btn" data-status="cancelled">
                <i class="fas fa-times-circle"></i> Đã hủy
            </button>
            <button class="filter-btn" data-status="invalid">
                <i class="fas fa-ban"></i> Hết hiệu lực
            </button>
        </div>
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
                $actualStatus = $booking['booking_info']['trangThaiThucTe'];
                
                if ($actualStatus === 'DaHuy') {
                    $statusClass = 'status-cancelled';
                    $statusText = 'Đã hủy';
                    $statusIcon = 'times-circle';
                    $dataStatus = 'cancelled';
                } elseif ($actualStatus === 'HetHieuLuc') {
                    $statusClass = 'status-invalid';
                    $statusText = 'Hết hiệu lực';
                    $statusIcon = 'ban';
                    $dataStatus = 'invalid';
                } elseif ($actualStatus === 'DaHoanThanh') {
                    $statusClass = 'status-completed';
                    $statusText = 'Đã hoàn thành';
                    $statusIcon = 'check-double';
                    $dataStatus = 'completed';
                } else {
                    $statusClass = 'status-active';
                    $statusText = 'Chờ khởi hành';
                    $statusIcon = 'check-circle';
                    $dataStatus = 'active';
                }
                ?>
                <div class="ticket-card-modern <?php echo $actualStatus === 'DaHuy' || $actualStatus === 'HetHieuLuc' ? 'cancelled-ticket' : ''; ?>" data-status="<?php echo $dataStatus; ?>">
                    <div class="ticket-image">
                        <img src="<?php echo BASE_URL; ?>/public/images/bus-placeholder.jpg" alt="Bus" onerror="this.src='https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?w=400&h=250&fit=crop'">
                        <div class="ticket-status-overlay <?php echo $statusClass; ?>">
                            <i class="fas fa-<?php echo $statusIcon; ?>"></i> <?php echo $statusText; ?>
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
                                    <i class="fas fa-calendar-check"></i>
                                    <span><?php echo date('d/m/Y', strtotime($booking['booking_info']['ngayDat'])); ?></span>
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
                            <a href="<?php echo BASE_URL; ?>/my-tickets/detail/<?php echo $bookingId; ?>" class="btn-view-detail">
                                Xem Chi Tiết <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="no-results" style="display: none;">
            <i class="fas fa-search"></i>
            <p>Không tìm thấy vé nào với trạng thái này</p>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const ticketCards = document.querySelectorAll('.ticket-card-modern');
    const noResults = document.querySelector('.no-results');
    const ticketsGrid = document.querySelector('.tickets-grid');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Update active button
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            const filterStatus = this.getAttribute('data-status');
            let visibleCount = 0;
            
            // Filter tickets
            ticketCards.forEach(card => {
                if (filterStatus === 'all') {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    const cardStatus = card.getAttribute('data-status');
                    if (cardStatus === filterStatus) {
                        card.style.display = 'block';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                }
            });
            
            // Show/hide no results message
            if (visibleCount === 0) {
                ticketsGrid.style.display = 'none';
                noResults.style.display = 'flex';
            } else {
                ticketsGrid.style.display = 'grid';
                noResults.style.display = 'none';
            }
        });
    });
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
