<?php include __DIR__ . '/../layouts/header.php'; ?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/my-tickets.css">

<div class="my-tickets-container">
    <div class="page-header">
        <h1 class="page-title">Chi Tiết Vé</h1>
        <p class="page-subtitle">Mã đặt vé: XG-<?php echo htmlspecialchars($bookingId); ?></p>
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
                        <span class="detail-value">XG-<?php echo htmlspecialchars($bookingInfo['maDatVe']); ?></span>
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
                
                <?php 
                $canCancel = false;
                $cancelMessage = '';
                
                if ($bookingInfo['trangThai'] === 'DaThanhToan') {
                    // Get earliest departure time
                    $earliestDeparture = null;
                    foreach ($tripGroups as $tripGroup) {
                        $departureTime = strtotime($tripGroup['trip_info']['thoiGianKhoiHanh']);
                        if ($earliestDeparture === null || $departureTime < $earliestDeparture) {
                            $earliestDeparture = $departureTime;
                        }
                    }
                    
                    $hoursUntilDeparture = ($earliestDeparture - time()) / 3600;
                    
                    if ($hoursUntilDeparture < 0) {
                        $cancelMessage = 'Không thể hủy vé đã qua ngày khởi hành';
                    } elseif ($hoursUntilDeparture < 36) {
                        $cancelMessage = 'Chỉ có thể hủy vé trước 36 giờ so với giờ khởi hành';
                    } else {
                        $canCancel = true;
                    }
                }
                ?>
                
                <?php if ($canCancel): ?>
                    <div class="cancel-ticket-section">
                        <button type="button" class="btn-cancel-ticket" onclick="showCancelModal()">
                            <i class="fas fa-times-circle"></i> Hủy Vé
                        </button>
                    </div>
                <?php elseif (!empty($cancelMessage) && $bookingInfo['trangThai'] === 'DaThanhToan'): ?>
                    <div class="cancel-ticket-section">
                        <p class="cancel-message-info">
                            <i class="fas fa-info-circle"></i> <?php echo $cancelMessage; ?>
                        </p>
                    </div>
                <?php endif; ?>
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
                                <span class="detail-label">Loại xe:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($tripInfo['tenLoaiPhuongTien']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Biển số xe:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($tripInfo['bienSo']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Số chỗ:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($tripInfo['soChoMacDinh']); ?> chỗ</span>
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
                            <?php if (!empty($tripInfo['tenTaiXe'])): ?>
                                <div class="detail-item">
                                    <span class="detail-label">Tài xế:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($tripInfo['tenTaiXe']); ?></span>
                                </div>
                                <?php if (!empty($tripInfo['soDienThoaiTaiXe'])): ?>
                                    <div class="detail-item">
                                        <span class="detail-label">SĐT tài xế:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($tripInfo['soDienThoaiTaiXe']); ?></span>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
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
                                        <div class="qr-code-container <?php echo $bookingInfo['trangThai'] === 'DaHuy' ? 'qr-disabled' : ''; ?>">
                                            <div class="qr-code-header">
                                                <i class="fas fa-qrcode"></i>
                                                <span>Mã vé: <?php echo htmlspecialchars($ticket['maChiTiet']); ?></span>
                                            </div>
                                            <div class="qr-code-wrapper">
                                                <img src="<?php echo $ticket['qrCode']; ?>" alt="QR Code vé" class="qr-code-image">
                                                <?php if ($bookingInfo['trangThai'] === 'DaHuy'): ?>
                                                    <div class="qr-disabled-overlay">
                                                        <div class="qr-disabled-icon">
                                                            <i class="fas fa-times"></i>
                                                        </div>
                                                        <span class="qr-disabled-text">Vé đã bị hủy</span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <p class="qr-code-note">
                                                <i class="fas fa-info-circle"></i>
                                                <?php if ($bookingInfo['trangThai'] === 'DaHuy'): ?>
                                                    Vé này đã bị hủy và không còn hiệu lực
                                                <?php else: ?>
                                                    Vui lòng xuất trình mã QR này khi lên xe
                                                <?php endif; ?>
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

<?php // Add cancellation modal ?>
<div id="cancelModal" class="cancel-modal" style="display: none;">
    <div class="cancel-modal-content">
        <div class="cancel-modal-header">
            <h3><i class="fas fa-exclamation-triangle"></i> Xác Nhận Hủy Vé</h3>
            <button type="button" class="cancel-modal-close" onclick="closeCancelModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="cancel-modal-body">
            <p class="cancel-confirm-text">Bạn có chắc chắn muốn hủy vé này?</p>
            
            <div class="cancel-policy-box">
                <h4><i class="fas fa-info-circle"></i> Chính Sách Hoàn Tiền</h4>
                <ul class="cancel-policy-list">
                    <li><strong>Phí hoàn:</strong> 20% giá vé sẽ được quy đổi thành điểm tích lũy</li>
                    <li><strong>Điều kiện:</strong> Chỉ hoàn điểm cho khách hàng có tài khoản</li>
                    <li><strong>Thời gian:</strong> Chỉ được hủy trước 36 giờ so với giờ khởi hành</li>
                    <li><strong>Vé đã sử dụng:</strong> Không thể hủy vé đã quét QR, đã lên xe hoặc qua ngày khởi hành</li>
                </ul>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="refund-calculation">
                        <p><strong>Tổng tiền vé:</strong> <?php echo number_format($bookingInfo['tongTienSauGiam']); ?>đ</p>
                        <p><strong>Hoàn lại (20%):</strong> <?php echo number_format($bookingInfo['tongTienSauGiam'] * 0.2); ?>đ</p>
                        <p><strong>Điểm tích lũy nhận được:</strong> <?php echo floor($bookingInfo['tongTienSauGiam'] * 0.2 / 100); ?> điểm</p>
                        <p class="refund-note"><em>(1 điểm = 100đ)</em></p>
                    </div>
                <?php else: ?>
                    <div class="refund-warning">
                        <i class="fas fa-exclamation-circle"></i>
                        <p>Bạn chưa đăng nhập. Vé sẽ bị hủy nhưng không được hoàn tiền.</p>
                    </div>
                <?php endif; ?>
                
                <p class="terms-link">
                    <a href="<?php echo BASE_URL; ?>/booking-guide#cancellation-policy" target="_blank">
                        <i class="fas fa-external-link-alt"></i> Xem thêm về điều khoản hủy vé
                    </a>
                </p>
            </div>
        </div>
        
        <div class="cancel-modal-footer">
            <button type="button" class="btn-cancel-action" onclick="closeCancelModal()">
                <i class="fas fa-arrow-left"></i> Quay Lại
            </button>
            <button type="button" class="btn-confirm-cancel" onclick="confirmCancelTicket()">
                <i class="fas fa-check"></i> Xác Nhận Hủy Vé
            </button>
        </div>
    </div>
</div>

<script>
function showCancelModal() {
    document.getElementById('cancelModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeCancelModal() {
    document.getElementById('cancelModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function confirmCancelTicket() {
    const bookingId = <?php echo $bookingId; ?>;
    const confirmBtn = document.querySelector('.btn-confirm-cancel');
    
    // Disable button to prevent double clicks
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
    
    fetch('<?php echo BASE_URL; ?>/my-tickets/cancel/' + bookingId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert('Lỗi: ' + data.message);
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="fas fa-check"></i> Xác Nhận Hủy Vé';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi hủy vé. Vui lòng thử lại.');
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = '<i class="fas fa-check"></i> Xác Nhận Hủy Vé';
    });
}

// Close modal when clicking outside
document.getElementById('cancelModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeCancelModal();
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
