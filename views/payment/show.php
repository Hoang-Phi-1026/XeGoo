<?php include __DIR__ . '/../layouts/header.php'; ?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/payment.css">

<div class="payment-container">
    <!-- Header với countdown timer -->
    <div class="payment-header">
        <h1>Thanh toán</h1>
        <div class="timer-section">
            <div class="timer-icon">⏰</div>
            <div class="timer-text">
                <span>Thời gian giữ ghế còn lại:</span>
                <span id="countdown" class="countdown-timer">5:00</span>
            </div>
        </div>
        <a href="<?php echo BASE_URL; ?>/search" class="back-button">← Quay lại tìm kiếm</a>
    </div>

    <div class="payment-content">
        <!-- Bên trái: Thông tin chi tiết -->
        <div class="payment-left">
            <!-- Thông tin chuyến đi -->
            <div class="info-card">
                <h3 class="card-title">Thông tin chuyến đi</h3>
                
                <!-- Chuyến đi -->
                <?php if ($bookingData['outbound']): ?>
                    <div class="trip-info">
                        <div class="trip-header">
                            <h4>Chuyến đi</h4>
                            <span class="trip-date"><?php echo date('d/m/Y', strtotime($bookingData['outbound']['trip_details']['ngayKhoiHanh'])); ?></span>
                        </div>
                        
                        <!-- Enhanced trip information display -->
                        <div class="trip-route-info">
                            <div class="route-name">
                                <strong>Tuyến: <?php echo htmlspecialchars($bookingData['outbound']['trip_details']['kyHieuTuyen'] ?? 'N/A'); ?></strong>
                            </div>
                        </div>
                        
                        <div class="route-display">
                            <div class="route-point">
                                <div class="point-time"><?php echo date('H:i', strtotime($bookingData['outbound']['trip_details']['thoiGianKhoiHanh'])); ?></div>
                                <div class="point-name"><?php echo htmlspecialchars($bookingData['outbound']['trip_details']['diemDi']); ?></div>
                            </div>
                            <div class="route-arrow">
                                <div class="arrow-line"></div>
                                <div class="arrow-head">→</div>
                            </div>
                            <div class="route-point">
                                <div class="point-time"><?php echo date('H:i', strtotime($bookingData['outbound']['trip_details']['gioKetThuc'])); ?></div>
                                <div class="point-name"><?php echo htmlspecialchars($bookingData['outbound']['trip_details']['diemDen']); ?></div>
                            </div>
                        </div>
                        
                        <!-- Added detailed pickup/dropoff points -->
                        <div class="trip-details">
                            <?php 
                            // Get pickup point name
                            $pickupPointName = 'N/A';
                            if (!empty($bookingData['outbound']['pickup_point'])) {
                                $sql = "SELECT tenDiem FROM tuyenduong_diemdontra WHERE maDiem = ?";
                                $pickupPoint = fetch($sql, [$bookingData['outbound']['pickup_point']]);
                                $pickupPointName = $pickupPoint ? $pickupPoint['tenDiem'] : 'N/A';
                            }
                            
                            // Get dropoff point name
                            $dropoffPointName = 'N/A';
                            if (!empty($bookingData['outbound']['dropoff_point'])) {
                                $sql = "SELECT tenDiem FROM tuyenduong_diemdontra WHERE maDiem = ?";
                                $dropoffPoint = fetch($sql, [$bookingData['outbound']['dropoff_point']]);
                                $dropoffPointName = $dropoffPoint ? $dropoffPoint['tenDiem'] : 'N/A';
                            }
                            ?>
                            <div class="detail-item">
                                <span class="detail-label">Điểm đón:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($pickupPointName); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Điểm trả:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($dropoffPointName); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Số ghế:</span>
                                <span class="detail-value"><?php echo implode(', ', $bookingData['outbound']['selected_seats']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Số lượng ghế:</span>
                                <span class="detail-value"><?php echo count($bookingData['outbound']['selected_seats']); ?> ghế</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Loại xe:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($bookingData['outbound']['trip_details']['tenLoaiPhuongTien'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Loại chỗ ngồi:</span>
                                <?php 
                                $seatType = null;
                                
                                // Try multiple sources for seat type data
                                if (isset($bookingData['outbound']['trip_details']['loaiChoNgoiMacDinh'])) {
                                    $seatType = $bookingData['outbound']['trip_details']['loaiChoNgoiMacDinh'];
                                    error_log("[v0] Payment page - seat type from loaiChoNgoiMacDinh: " . $seatType);
                                } elseif (isset($bookingData['outbound']['trip_details']['default_seat_type'])) {
                                    $seatType = $bookingData['outbound']['trip_details']['default_seat_type'];
                                    error_log("[v0] Payment page - seat type from default_seat_type: " . $seatType);
                                } elseif (isset($bookingData['outbound']['trip_details']['tenLoaiPhuongTien'])) {
                                    // Fallback to vehicle type if seat type is not available
                                    $vehicleType = $bookingData['outbound']['trip_details']['tenLoaiPhuongTien'];
                                    if (strpos($vehicleType, 'đôi') !== false) {
                                        $seatType = 'Giường đôi';
                                    } elseif (strpos($vehicleType, 'đơn') !== false) {
                                        $seatType = 'Giường đơn';
                                    } elseif (strpos($vehicleType, 'VIP') !== false) {
                                        $seatType = 'Ghế VIP';
                                    } else {
                                        $seatType = 'Ghế ngồi';
                                    }
                                    error_log("[v0] Payment page - seat type derived from vehicle type: " . $seatType);
                                }
                                
                                error_log("[v0] Payment page - final seat type value: " . ($seatType ?? 'NULL'));
                                error_log("[v0] Payment page - trip details keys: " . json_encode(array_keys($bookingData['outbound']['trip_details'] ?? [])));
                                ?>
                                <span class="detail-value"><?php echo htmlspecialchars($seatType ?: 'Chưa xác định'); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Biển số xe:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($bookingData['outbound']['trip_details']['bienSo']); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Chuyến về (nếu có) -->
                <?php if (isset($bookingData['return'])): ?>
                    <div class="trip-info return-trip">
                        <div class="trip-header">
                            <h4>Chuyến về</h4>
                            <span class="trip-date"><?php echo date('d/m/Y', strtotime($bookingData['return']['trip_details']['ngayKhoiHanh'])); ?></span>
                        </div>
                        
                        <!-- Enhanced return trip information display -->
                        <div class="trip-route-info">
                            <div class="route-name">
                                <strong>Tuyến: <?php echo htmlspecialchars($bookingData['return']['trip_details']['kyHieuTuyen'] ?? 'N/A'); ?></strong>
                            </div>
                        </div>
                        
                        <div class="route-display">
                            <div class="route-point">
                                <div class="point-time"><?php echo date('H:i', strtotime($bookingData['return']['trip_details']['thoiGianKhoiHanh'])); ?></div>
                                <div class="point-name"><?php echo htmlspecialchars($bookingData['return']['trip_details']['diemDi']); ?></div>
                            </div>
                            <div class="route-arrow">
                                <div class="arrow-line"></div>
                                <div class="arrow-head">→</div>
                            </div>
                            <div class="route-point">
                                <div class="point-time"><?php echo date('H:i', strtotime($bookingData['return']['trip_details']['gioKetThuc'])); ?></div>
                                <div class="point-name"><?php echo htmlspecialchars($bookingData['return']['trip_details']['diemDen']); ?></div>
                            </div>
                        </div>
                        
                        <!-- Added detailed pickup/dropoff points for return trip -->
                        <div class="trip-details">
                            <?php 
                            // Get return pickup point name
                            $returnPickupPointName = 'N/A';
                            if (!empty($bookingData['return']['pickup_point'])) {
                                $sql = "SELECT tenDiem FROM tuyenduong_diemdontra WHERE maDiem = ?";
                                $returnPickupPoint = fetch($sql, [$bookingData['return']['pickup_point']]);
                                $returnPickupPointName = $returnPickupPoint ? $returnPickupPoint['tenDiem'] : 'N/A';
                            }
                            
                            // Get return dropoff point name
                            $returnDropoffPointName = 'N/A';
                            if (!empty($bookingData['return']['dropoff_point'])) {
                                $sql = "SELECT tenDiem FROM tuyenduong_diemdontra WHERE maDiem = ?";
                                $returnDropoffPoint = fetch($sql, [$bookingData['return']['dropoff_point']]);
                                $returnDropoffPointName = $returnDropoffPoint ? $returnDropoffPoint['tenDiem'] : 'N/A';
                            }
                            ?>
                            <div class="detail-item">
                                <span class="detail-label">Điểm đón:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($returnPickupPointName); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Điểm trả:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($returnDropoffPointName); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Số ghế:</span>
                                <span class="detail-value"><?php echo implode(', ', $bookingData['return']['selected_seats']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Số lượng ghế:</span>
                                <span class="detail-value"><?php echo count($bookingData['return']['selected_seats']); ?> ghế</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Loại xe:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($bookingData['return']['trip_details']['tenLoaiPhuongTien'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Loại chỗ ngồi:</span>
                                <?php 
                                $returnSeatType = null;
                                
                                // Try multiple sources for return trip seat type data
                                if (isset($bookingData['return']['trip_details']['loaiChoNgoiMacDinh'])) {
                                    $returnSeatType = $bookingData['return']['trip_details']['loaiChoNgoiMacDinh'];
                                    error_log("[v0] Payment page - return seat type from loaiChoNgoiMacDinh: " . $returnSeatType);
                                } elseif (isset($bookingData['return']['trip_details']['default_seat_type'])) {
                                    $returnSeatType = $bookingData['return']['trip_details']['default_seat_type'];
                                    error_log("[v0] Payment page - return seat type from default_seat_type: " . $returnSeatType);
                                } elseif (isset($bookingData['return']['trip_details']['tenLoaiPhuongTien'])) {
                                    // Fallback to vehicle type if seat type is not available
                                    $vehicleType = $bookingData['return']['trip_details']['tenLoaiPhuongTien'];
                                    if (strpos($vehicleType, 'đôi') !== false) {
                                        $returnSeatType = 'Giường đôi';
                                    } elseif (strpos($vehicleType, 'đơn') !== false) {
                                        $returnSeatType = 'Giường đơn';
                                    } elseif (strpos($vehicleType, 'VIP') !== false) {
                                        $returnSeatType = 'Ghế VIP';
                                    } else {
                                        $returnSeatType = 'Ghế ngồi';
                                    }
                                    error_log("[v0] Payment page - return seat type derived from vehicle type: " . $returnSeatType);
                                }
                                
                                error_log("[v0] Payment page - final return seat type value: " . ($returnSeatType ?? 'NULL'));
                                ?>
                                <span class="detail-value"><?php echo htmlspecialchars($returnSeatType ?: 'Chưa xác định'); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Biển số xe:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($bookingData['return']['trip_details']['bienSo']); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Thông tin hành khách -->
            <div class="info-card">
                <h3 class="card-title">Thông tin hành khách</h3>
                
                <?php if ($bookingData['outbound']): ?>
                    <div class="passenger-section">
                        <h4>Chuyến đi</h4>
                        <?php foreach ($bookingData['outbound']['passengers'] as $index => $passenger): ?>
                            <div class="passenger-item">
                                <div class="passenger-header">
                                    <span class="passenger-number">Hành khách <?php echo $index + 1; ?></span>
                                    <span class="seat-badge">Ghế <?php echo $bookingData['outbound']['selected_seats'][$index]; ?></span>
                                </div>
                                <div class="passenger-details">
                                    <div class="passenger-info">
                                        <strong><?php echo htmlspecialchars($passenger['ho_ten']); ?></strong>
                                        <span><?php echo htmlspecialchars($passenger['email'] ?? ''); ?></span>
                                        <span><?php echo htmlspecialchars($passenger['so_dien_thoai'] ?? ''); ?></span>
                                    </div>
                                    <div class="ticket-price">
                                        <?php echo number_format($bookingData['outbound']['trip_details']['giaVe'], 0, ',', '.'); ?>đ
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($bookingData['return'])): ?>
                    <div class="passenger-section">
                        <h4>Chuyến về</h4>
                        <?php foreach ($bookingData['return']['passengers'] as $index => $passenger): ?>
                            <div class="passenger-item">
                                <div class="passenger-header">
                                    <span class="passenger-number">Hành khách <?php echo $index + 1; ?></span>
                                    <span class="seat-badge">Ghế <?php echo $bookingData['return']['selected_seats'][$index]; ?></span>
                                </div>
                                <div class="passenger-details">
                                    <div class="passenger-info">
                                        <strong><?php echo htmlspecialchars($passenger['ho_ten']); ?></strong>
                                        <span><?php echo htmlspecialchars($passenger['email'] ?? ''); ?></span>
                                        <span><?php echo htmlspecialchars($passenger['so_dien_thoai'] ?? ''); ?></span>
                                    </div>
                                    <div class="ticket-price">
                                        <?php echo number_format($bookingData['return']['trip_details']['giaVe'], 0, ',', '.'); ?>đ
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Mã giảm giá -->
            <?php if ($isLoggedIn): ?>
            <div class="info-card">
                <h3 class="card-title">Mã giảm giá</h3>
                <div class="promotion-section">
                    <?php if (!empty($promotions)): ?>
                        <div class="promotion-list" id="promotionList">
                            <?php foreach ($promotions as $promotion): ?>
                                <div class="promotion-item" data-promotion-id="<?php echo $promotion['maKhuyenMai']; ?>">
                                    <div class="promotion-info">
                                        <div class="promotion-name"><?php echo htmlspecialchars($promotion['tenKhuyenMai']); ?></div>
                                        <div class="promotion-value">
                                            <?php if ($promotion['loai'] === 'PhanTram'): ?>
                                                Giảm <?php echo $promotion['giaTri']; ?>%
                                            <?php else: ?>
                                                Giảm <?php echo number_format($promotion['giaTri'], 0, ',', '.'); ?>đ
                                            <?php endif; ?>
                                        </div>
                                        <div class="promotion-expire">
                                            Hết hạn: <?php echo date('d/m/Y', strtotime($promotion['ngayKetThuc'])); ?>
                                        </div>
                                    </div>
                                    <button type="button" class="promotion-btn">Chọn</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-promotions">Hiện tại không có mã giảm giá nào</div>
                    <?php endif; ?>
                    
                    <div class="selected-promotion" id="selectedPromotion" style="display: none;">
                        <div class="selected-promotion-info">
                            <span class="selected-promotion-name"></span>
                            <span class="selected-promotion-value"></span>
                        </div>
                        <button type="button" class="remove-promotion-btn" id="removePromotion">Bỏ chọn</button>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Điểm tích lũy -->
            <?php if ($isLoggedIn): ?>
            <div class="info-card">
                <h3 class="card-title">Điểm tích lũy</h3>
                <div class="points-section">
                    <div class="points-info">
                        <div class="available-points">
                            <span class="points-label">Điểm có sẵn:</span>
                            <span class="points-value"><?php echo number_format($userPoints, 0, ',', '.'); ?> điểm</span>
                        </div>
                        <div class="points-rate">1 điểm = 100đ giảm giá</div>
                    </div>
                    
                    <?php if ($userPoints > 0): ?>
                        <div class="points-input-section">
                            <div class="input-group">
                                <input type="number" 
                                       id="pointsInput" 
                                       class="points-input" 
                                       placeholder="Nhập số điểm muốn sử dụng"
                                       min="0" 
                                       max="<?php echo $userPoints; ?>">
                                <button type="button" class="use-points-btn" id="usePointsBtn">Sử dụng</button>
                            </div>
                            <div class="points-shortcuts">
                                <button type="button" class="points-shortcut" data-points="<?php echo min(100, $userPoints); ?>">100 điểm</button>
                                <button type="button" class="points-shortcut" data-points="<?php echo min(500, $userPoints); ?>">500 điểm</button>
                                <button type="button" class="points-shortcut" data-points="<?php echo $userPoints; ?>">Tất cả</button>
                            </div>
                        </div>
                        
                        <div class="used-points" id="usedPoints" style="display: none;">
                            <div class="used-points-info">
                                <span>Đã sử dụng: <span id="usedPointsValue">0</span> điểm</span>
                                <span>Giảm: <span id="pointsDiscount">0</span>đ</span>
                            </div>
                            <button type="button" class="remove-points-btn" id="removePoints">Bỏ sử dụng</button>
                        </div>
                    <?php else: ?>
                        <div class="no-points">Bạn chưa có điểm tích lũy nào</div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Tổng kết giá -->
            <div class="info-card price-summary-card">
                <h3 class="card-title">Chi tiết giá</h3>
                <div class="price-breakdown">
                    <div class="price-item">
                        <span class="price-label">Tổng tiền gốc:</span>
                        <span class="price-value" id="originalPrice"><?php echo number_format($pricing['original_price'], 0, ',', '.'); ?>đ</span>
                    </div>
                    <div class="price-item discount-item" id="promotionDiscount" style="display: none;">
                        <span class="price-label">Giảm giá khuyến mãi:</span>
                        <span class="price-value discount-value">-<span id="promotionDiscountValue">0</span>đ</span>
                    </div>
                    <div class="price-item discount-item" id="pointsDiscountItem" style="display: none;">
                        <span class="price-label">Giảm giá điểm tích lũy:</span>
                        <span class="price-value discount-value">-<span id="pointsDiscountValue">0</span>đ</span>
                    </div>
                    <div class="price-item total-item">
                        <span class="price-label">Tổng thanh toán:</span>
                        <span class="price-value total-price" id="finalPrice"><?php echo number_format($pricing['final_price'], 0, ',', '.'); ?>đ</span>
                    </div>
                    <div class="earned-points">
                        <span class="points-label">Điểm tích lũy nhận được:</span>
                        <span class="points-value" id="earnedPoints"><?php echo $pricing['earned_points']; ?> điểm</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bên phải: Phương thức thanh toán -->
        <div class="payment-right">
            <div class="payment-methods-card">
                <h3 class="card-title">Phương thức thanh toán</h3>
                
                <form method="POST" action="<?php echo BASE_URL; ?>/payment/process" id="paymentForm">
                    <div class="payment-methods">
                        <div class="payment-method" data-method="MoMo">
                            <input type="radio" id="momo" name="payment_method" value="MoMo" required>
                            <label for="momo" class="payment-method-label">
                                <div class="payment-method-icon">
                                    <img src="<?php echo BASE_URL; ?>/public/images/momo-logo.png" alt="MoMo" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                    <div class="payment-method-text" style="display: none;">MoMo</div>
                                </div>
                                <div class="payment-method-info">
                                    <div class="payment-method-name">Ví MoMo</div>
                                    <div class="payment-method-desc">Thanh toán qua ví điện tử MoMo</div>
                                </div>
                                <div class="payment-method-check">✓</div>
                            </label>
                        </div>

                        <div class="payment-method" data-method="VNPay">
                            <input type="radio" id="vnpay" name="payment_method" value="VNPay" required>
                            <label for="vnpay" class="payment-method-label">
                                <div class="payment-method-icon">
                                    <img src="<?php echo BASE_URL; ?>/public/images/vnpay-logo.png" alt="VNPay" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                    <div class="payment-method-text" style="display: none;">VNPay</div>
                                </div>
                                <div class="payment-method-info">
                                    <div class="payment-method-name">VNPay</div>
                                    <div class="payment-method-desc">Thanh toán qua cổng VNPay</div>
                                </div>
                                <div class="payment-method-check">✓</div>
                            </label>
                        </div>
                    </div>

                    <div class="payment-actions">
                        <button type="button" class="cancel-btn" id="cancelPayment">Hủy</button>
                        <button type="submit" class="confirm-btn" id="confirmPayment">Xác nhận thanh toán</button>
                    </div>
                </form>
            </div>

            <!-- Thông tin hỗ trợ -->
            <div class="support-card">
                <h4>Cần hỗ trợ?</h4>
                <div class="support-info">
                    <div class="support-item">
                        <span class="support-icon">📞</span>
                        <span>Hotline: 1900 1234</span>
                    </div>
                    <div class="support-item">
                        <span class="support-icon">✉️</span>
                        <span>Email: support@xegoo.com</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript xử lý trang thanh toán -->
<script src="<?php echo BASE_URL; ?>/public/js/payment-seat-manager.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('[v0] Payment page loaded');
    
    // Countdown timer
    const expiresAt = <?php echo $heldSeats['expires_at']; ?>;
    const countdownElement = document.getElementById('countdown');
    
    function updateCountdown() {
        const now = Math.floor(Date.now() / 1000);
        const remaining = expiresAt - now;
        
        if (remaining <= 0) {
            // Hết thời gian, chuyển hướng
            alert('Thời gian giữ ghế đã hết. Bạn sẽ được chuyển về trang tìm kiếm.');
            window.location.href = '<?php echo BASE_URL; ?>/search';
            return;
        }
        
        const minutes = Math.floor(remaining / 60);
        const seconds = remaining % 60;
        countdownElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        
        // Đổi màu khi còn ít thời gian
        if (remaining <= 120) { // 2 phút
            countdownElement.classList.add('warning');
        }
        if (remaining <= 60) { // 1 phút
            countdownElement.classList.add('danger');
        }
    }
    
    // Cập nhật countdown mỗi giây
    updateCountdown();
    const countdownInterval = setInterval(updateCountdown, 1000);
    
    document.querySelectorAll('.promotion-item').forEach(item => {
        const btn = item.querySelector('.promotion-btn');
        btn.addEventListener('click', function() {
            const promotionId = item.dataset.promotionId;
            const promotionName = item.querySelector('.promotion-name').textContent;
            const promotionValue = item.querySelector('.promotion-value').textContent;
            
            applyPromotion(promotionId, promotionName, promotionValue);
        });
    });
    
    document.getElementById('removePromotion')?.addEventListener('click', function() {
        removePromotion();
    });
    
    // Xử lý điểm tích lũy
    document.querySelectorAll('.points-shortcut').forEach(btn => {
        btn.addEventListener('click', function() {
            const points = parseInt(this.dataset.points);
            document.getElementById('pointsInput').value = points;
        });
    });
    
    document.getElementById('usePointsBtn')?.addEventListener('click', function() {
        const points = parseInt(document.getElementById('pointsInput').value) || 0;
        if (points > 0) {
            usePoints(points);
        }
    });
    
    document.getElementById('removePoints')?.addEventListener('click', function() {
        removePoints();
    });
    
    // Xử lý hủy thanh toán
    document.getElementById('cancelPayment').addEventListener('click', function() {
        if (confirm('Bạn có chắc chắn muốn hủy thanh toán? Ghế sẽ được giải phóng.')) {
            window.location.href = '<?php echo BASE_URL; ?>/payment/cancel';
        }
    });
    
    // Xử lý form thanh toán
    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        const selectedMethod = document.querySelector('input[name="payment_method"]:checked');
        if (!selectedMethod) {
            e.preventDefault();
            alert('Vui lòng chọn phương thức thanh toán.');
            return;
        }
        
        // Hiển thị loading
        const submitBtn = document.getElementById('confirmPayment');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Đang xử lý...';
    });
    
    // Functions
    function applyPromotion(promotionId, promotionName, promotionValue) {
        fetch('<?php echo BASE_URL; ?>/payment/apply-promotion', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                promotion_id: promotionId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Hiển thị khuyến mãi đã chọn
                document.getElementById('selectedPromotion').style.display = 'block';
                document.querySelector('.selected-promotion-name').textContent = promotionName;
                document.querySelector('.selected-promotion-value').textContent = promotionValue;
                
                // Ẩn danh sách khuyến mãi
                document.getElementById('promotionList').style.display = 'none';
                
                // Cập nhật giá
                updatePricing(data.pricing);
                
                alert(data.message);
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi áp dụng khuyến mãi.');
        });
    }
    
    function removePromotion() {
        fetch('<?php echo BASE_URL; ?>/payment/remove-promotion', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Ẩn khuyến mãi đã chọn
                document.getElementById('selectedPromotion').style.display = 'none';
                // Hiển thị lại danh sách khuyến mãi
                document.getElementById('promotionList').style.display = 'block';
                
                // Cập nhật giá
                updatePricing(data.pricing);
                
                alert('Đã bỏ chọn mã giảm giá');
            } else {
                alert(data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Fallback: reload page
            location.reload();
        });
    }
    
    function usePoints(points) {
        fetch('<?php echo BASE_URL; ?>/payment/use-points', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                points: points
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Hiển thị điểm đã sử dụng
                document.getElementById('usedPoints').style.display = 'block';
                document.getElementById('usedPointsValue').textContent = points.toLocaleString();
                document.getElementById('pointsDiscount').textContent = (points * 100).toLocaleString();
                
                // Ẩn phần nhập điểm
                document.querySelector('.points-input-section').style.display = 'none';
                
                // Cập nhật giá
                updatePricing(data.pricing);
                
                alert(data.message);
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi sử dụng điểm tích lũy.');
        });
    }
    
    function removePoints() {
        fetch('<?php echo BASE_URL; ?>/payment/remove-points', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Ẩn điểm đã sử dụng
                document.getElementById('usedPoints').style.display = 'none';
                // Hiển thị lại phần nhập điểm
                document.querySelector('.points-input-section').style.display = 'block';
                document.getElementById('pointsInput').value = '';
                
                // Cập nhật giá
                updatePricing(data.pricing);
                
                alert('Đã bỏ sử dụng điểm tích lũy');
            } else {
                alert(data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Fallback: reload page
            location.reload();
        });
    }
    
    function updatePricing(pricing) {
        document.getElementById('originalPrice').textContent = pricing.original_price.toLocaleString() + 'đ';
        document.getElementById('finalPrice').textContent = pricing.final_price.toLocaleString() + 'đ';
        document.getElementById('earnedPoints').textContent = pricing.earned_points;
        
        // Hiển thị/ẩn các mục giảm giá
        const totalDiscount = pricing.original_price - pricing.final_price;
        if (totalDiscount > 0) {
            // Show promotion discount if exists
            if (document.getElementById('selectedPromotion').style.display !== 'none') {
                document.getElementById('promotionDiscount').style.display = 'flex';
                document.getElementById('promotionDiscountValue').textContent = totalDiscount.toLocaleString();
            }
            
            // Show points discount if exists
            if (document.getElementById('usedPoints').style.display !== 'none') {
                document.getElementById('pointsDiscountItem').style.display = 'flex';
                const pointsUsed = parseInt(document.getElementById('usedPointsValue').textContent.replace(/,/g, ''));
                document.getElementById('pointsDiscountValue').textContent = (pointsUsed * 100).toLocaleString();
            }
        } else {
            document.getElementById('promotionDiscount').style.display = 'none';
            document.getElementById('pointsDiscountItem').style.display = 'none';
        }
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
