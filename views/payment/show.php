<?php include __DIR__ . '/../layouts/header.php'; ?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/payment.css">

<div class="payment-container">
    <!-- Header -->
    <div class="payment-header">
        <div class="header-content">
            <h1>Thanh toán vé xe</h1>
            <div class="timer-section">
                <span class="timer-label">Giữ chỗ trong:</span>
                <span id="countdown" class="countdown-timer">5:00</span>
            </div>
        </div>
        <a href="<?php echo BASE_URL; ?>/search" class="back-link">← Quay lại tìm kiếm</a>
    </div>

    <div class="payment-content">
        <!-- Left Section -->
        <div class="payment-left">
            <!-- Trip Information -->
            <div class="info-section">
                <h2 class="section-title">Thông tin chuyến đi</h2>
                
                <?php if ($bookingData['outbound']): ?>
                    <div class="trip-details">
                        <!-- Outbound Trip -->
                        <div class="trip-card outbound">
                            <div class="trip-label">Chuyến đi</div>
                            
                            <!-- Route -->
                            <div class="route-info">
                                <div class="route-segment">
                                    <div class="time"><?php echo date('H:i', strtotime($bookingData['outbound']['trip_details']['thoiGianKhoiHanh'])); ?></div>
                                    <div class="location"><?php echo htmlspecialchars($bookingData['outbound']['trip_details']['diemDi']); ?></div>
                                </div>
                                <div class="route-divider">
                                    <div class="line"></div>
                                    <div class="dot"></div>
                                </div>
                                <div class="route-segment">
                                    <div class="time"><?php echo date('H:i', strtotime($bookingData['outbound']['trip_details']['gioKetThuc'])); ?></div>
                                    <div class="location"><?php echo htmlspecialchars($bookingData['outbound']['trip_details']['diemDen']); ?></div>
                                </div>
                            </div>

                            <!-- Details Grid -->
                            <div class="details-grid">
                                <div class="detail-item">
                                    <span class="label">Tuyến</span>
                                    <span class="value"><?php echo htmlspecialchars($bookingData['outbound']['trip_details']['kyHieuTuyen'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="label">Ngày khởi hành</span>
                                    <span class="value"><?php echo date('d/m/Y', strtotime($bookingData['outbound']['trip_details']['ngayKhoiHanh'])); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="label">Loại ghế</span>
                                    <?php 
                                    $seatType = $bookingData['outbound']['trip_details']['loaiChoNgoiMacDinh'] 
                                        ?? $bookingData['outbound']['trip_details']['default_seat_type'] 
                                        ?? 'Chưa xác định';
                                    ?>
                                    <span class="value"><?php echo htmlspecialchars($seatType); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="label">Ghế đặt</span>
                                    <span class="value"><?php echo implode(', ', $bookingData['outbound']['selected_seats']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="label">Biển số xe</span>
                                    <span class="value"><?php echo htmlspecialchars($bookingData['outbound']['trip_details']['bienSo']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="label">Giá/ghế</span>
                                    <span class="value"><?php echo number_format($bookingData['outbound']['trip_details']['giaVe'], 0, ',', '.'); ?>đ</span>
                                </div>
                            </div>
                        </div>

                        <!-- Return Trip (if exists) -->
                        <?php if (isset($bookingData['return'])): ?>
                            <div class="trip-card return">
                                <div class="trip-label">Chuyến về</div>
                                
                                <div class="route-info">
                                    <div class="route-segment">
                                        <div class="time"><?php echo date('H:i', strtotime($bookingData['return']['trip_details']['thoiGianKhoiHanh'])); ?></div>
                                        <div class="location"><?php echo htmlspecialchars($bookingData['return']['trip_details']['diemDi']); ?></div>
                                    </div>
                                    <div class="route-divider">
                                        <div class="line"></div>
                                        <div class="dot"></div>
                                    </div>
                                    <div class="route-segment">
                                        <div class="time"><?php echo date('H:i', strtotime($bookingData['return']['trip_details']['gioKetThuc'])); ?></div>
                                        <div class="location"><?php echo htmlspecialchars($bookingData['return']['trip_details']['diemDen']); ?></div>
                                    </div>
                                </div>

                                <div class="details-grid">
                                    <div class="detail-item">
                                        <span class="label">Tuyến</span>
                                        <span class="value"><?php echo htmlspecialchars($bookingData['return']['trip_details']['kyHieuTuyen'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="label">Ngày khởi hành</span>
                                        <span class="value"><?php echo date('d/m/Y', strtotime($bookingData['return']['trip_details']['ngayKhoiHanh'])); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="label">Loại ghế</span>
                                        <?php 
                                        $returnSeatType = $bookingData['return']['trip_details']['loaiChoNgoiMacDinh'] 
                                            ?? $bookingData['return']['trip_details']['default_seat_type'] 
                                            ?? 'Chưa xác định';
                                        ?>
                                        <span class="value"><?php echo htmlspecialchars($returnSeatType); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="label">Ghế đặt</span>
                                        <span class="value"><?php echo implode(', ', $bookingData['return']['selected_seats']); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="label">Biển số xe</span>
                                        <span class="value"><?php echo htmlspecialchars($bookingData['return']['trip_details']['bienSo']); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="label">Giá/ghế</span>
                                        <span class="value"><?php echo number_format($bookingData['return']['trip_details']['giaVe'], 0, ',', '.'); ?>đ</span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Promotion Codes Section -->
            <?php if ($isLoggedIn): ?>
            <div class="info-section">
                <h2 class="section-title">Mã giảm giá</h2>
                <div class="promo-section">
                    <?php if (!empty($promotions)): ?>
                        <div class="promo-list-wrapper">
                            <div class="promo-list" id="promotionList">
                                <?php foreach ($promotions as $promotion): ?>
                                    <?php $hasUsed = $promotion['has_used'] ?? false; ?>
                                    <div class="promo-cards <?php echo $hasUsed ? 'used' : ''; ?>" data-promotion-id="<?php echo $promotion['maKhuyenMai']; ?>">
                                        <div class="promo-left">
                                            <div class="promo-name"><?php echo htmlspecialchars($promotion['tenKhuyenMai']); ?></div>
                                            <div class="promo-discount">
                                                <?php if ($promotion['loai'] === 'PhanTram'): ?>
                                                    Giảm <strong><?php echo $promotion['giaTri']; ?>%</strong>
                                                <?php else: ?>
                                                    Giảm <strong><?php echo number_format($promotion['giaTri'], 0, ',', '.'); ?>đ</strong>
                                                <?php endif; ?>
                                            </div>
                                            <div class="promo-meta">
                                                <span class="expire">Hết: <?php echo date('d/m', strtotime($promotion['ngayKetThuc'])); ?></span>
                                                <?php if ($promotion['doiTuongApDung'] === 'Khách hàng thân thiết'): ?>
                                                    <span class="vip-badge">VIP</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="promo-right">
                                            <?php if ($hasUsed): ?>
                                                <div class="used-label">Đã dùng</div>
                                            <?php endif; ?>
                                            <button type="button" class="promo-btn" <?php echo $hasUsed ? 'disabled' : ''; ?>>
                                                <?php echo $hasUsed ? 'Không thể dùng' : 'Chọn'; ?>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="no-promo">
                            <p>Hiện tại không có mã giảm giá nào khả dụng</p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="selected-promo" id="selectedPromotion" style="display: none;">
                        <div class="selected-promo-info">
                            <span class="selected-promo-name"></span>
                            <span class="selected-promo-value"></span>
                        </div>
                        <button type="button" class="remove-promo-btn" id="removePromotion">Bỏ chọn</button>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="info-section">
                <h2 class="section-title">Mã giảm giá</h2>
                <div class="login-section">
                    <p>Vui lòng đăng nhập để xem các mã giảm giá khuyến mãi</p>
                    <a href="<?php echo BASE_URL; ?>/login" class="login-btn">Đăng nhập</a>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right Section -->
        <div class="payment-right">
            <!-- Loyalty Points Section - Kept original style -->
            <?php if ($isLoggedIn): ?>
            <div class="info-section">
                <h2 class="section-title">Điểm tích lũy</h2>
                <div class="loyalty-section">
                    <div class="loyalty-header">
                        <div class="loyalty-info">
                            <span class="loyalty-label">Điểm có sẵn:</span>
                            <span class="loyalty-amount"><?php echo number_format($userPoints, 0, ',', '.'); ?> điểm</span>
                        </div>
                        <div class="loyalty-rate">1 điểm = 100đ</div>
                    </div>

                    <?php if ($userPoints > 0): ?>
                        <div class="loyalty-form">
                            <?php 
                            $originalPrice = $bookingData['total_price'];
                            $maxPointsAllowed = floor($originalPrice / 200);
                            ?>
                            <div class="form-group">
                                <input type="number" 
                                       id="pointsInput" 
                                       class="points-input" 
                                       placeholder="Nhập số điểm muốn sử dụng"
                                       min="0" 
                                       max="<?php echo min($userPoints, $maxPointsAllowed); ?>"
                                       data-max-points="<?php echo $maxPointsAllowed; ?>">
                                <button type="button" class="use-btn" id="usePointsBtn">Sử dụng</button>
                            </div>
                            <div class="loyalty-tips">
                                <p>Tối đa: <strong><?php echo min($userPoints, $maxPointsAllowed); ?></strong> điểm (50% tổng giá)</p>
                                <div class="quick-buttons">
                                    <button type="button" class="quick-btn" data-points="100">100 điểm</button>
                                    <button type="button" class="quick-btn" data-points="500">500 điểm</button>
                                    <button type="button" class="quick-btn quick-all" data-points="<?php echo min($userPoints, $maxPointsAllowed); ?>">Tất cả (50%)</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="used-points" id="usedPoints" style="display: none;">
                            <div class="used-points-info">
                                <span>Đã sử dụng: <span id="usedPointsValue">0</span> điểm</span>
                            </div>
                            <button type="button" class="remove-points-btn" id="removePoints">Bỏ</button>
                        </div>
                    <?php else: ?>
                        <div class="no-loyalty">Bạn chưa có điểm tích lũy</div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Price Summary -->
            <div class="info-section price-section">
                <h2 class="section-title">Chi tiết giá</h2>
                <div class="price-details">
                    <div class="price-row">
                        <span class="price-label">Tổng tiền:</span>
                        <span class="price-value" id="originalPrice"><?php echo number_format($pricing['original_price'], 0, ',', '.'); ?>đ</span>
                    </div>
                    <div class="price-row discount" id="promotionDiscount" style="display: none;">
                        <span class="price-label">Khuyến mãi:</span>
                        <span class="price-value">-<span id="promotionDiscountValue">0</span>đ</span>
                    </div>
                    <div class="price-row discount" id="pointsDiscountItem" style="display: none;">
                        <span class="price-label">Điểm:</span>
                        <span class="price-value">-<span id="pointsDiscountValue">0</span>đ</span>
                    </div>
                    <div class="price-row total">
                        <span class="price-label">Thanh toán:</span>
                        <span class="price-value final" id="finalPrice"><?php echo number_format($pricing['final_price'], 0, ',', '.'); ?>đ</span>
                    </div>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="info-section">
                <h2 class="section-title">Phương thức thanh toán</h2>
                <form method="POST" action="<?php echo BASE_URL; ?>/payment/process" id="paymentForm">
                    <div class="payment-methods">
                        <div class="payment-option">
                            <input type="radio" id="momo" name="payment_method" value="MoMo" required>
                            <label for="momo" class="option-label">
                                <div class="option-icon">
                                    <img src="<?php echo BASE_URL; ?>\public\uploads\images\momo-logo.png" alt="MoMo" onerror="this.style.display='none';">
                                </div>
                                <div class="option-text">
                                    <div class="option-name">Ví MoMo</div>
                                    <div class="option-desc">Qua ví điện tử</div>
                                </div>
                            </label>
                        </div>

                        <div class="payment-option">
                            <input type="radio" id="vnpay" name="payment_method" value="VNPay" required>
                            <label for="vnpay" class="option-label">
                                <div class="option-icon">
                                    <img src="<?php echo BASE_URL; ?>\public\uploads\images\vnpay-logo.png" alt="VNPay" onerror="this.style.display='none';">
                                </div>
                                <div class="option-text">
                                    <div class="option-name">VNPay</div>
                                    <div class="option-desc">Cổng thanh toán</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button type="button" class="btn-cancel" id="cancelPayment">Hủy</button>
                        <button type="submit" class="btn-confirm" id="confirmPayment">Thanh toán</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>/public/js/payment-seat-manager.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Countdown timer
    const expiresAt = <?php echo $heldSeats['expires_at']; ?>;
    const countdownElement = document.getElementById('countdown');
    
    function updateCountdown() {
        const now = Math.floor(Date.now() / 1000);
        const remaining = expiresAt - now;
        
        if (remaining <= 0) {
            alert('Thời gian giữ ghế đã hết. Bạn sẽ được chuyển về trang tìm kiếm.');
            window.location.href = '<?php echo BASE_URL; ?>/search';
            return;
        }
        
        const minutes = Math.floor(remaining / 60);
        const seconds = remaining % 60;
        countdownElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        
        if (remaining <= 120) countdownElement.classList.add('warning');
        if (remaining <= 60) countdownElement.classList.add('danger');
    }
    
    updateCountdown();
    setInterval(updateCountdown, 1000);
    
    // Promotion buttons
    document.querySelectorAll('.promo-btn:not(:disabled)').forEach(btn => {
        btn.addEventListener('click', function() {
            const item = this.closest('.promo-cards');
            const promotionId = item.dataset.promotionId;
            const promotionName = item.querySelector('.promo-name').textContent;
            const promotionValue = item.querySelector('.promo-discount').textContent;
            
            applyPromotion(promotionId, promotionName, promotionValue);
        });
    });
    
    document.getElementById('removePromotion')?.addEventListener('click', removePromotion);
    document.getElementById('usePointsBtn')?.addEventListener('click', function() {
        const points = parseInt(document.getElementById('pointsInput').value) || 0;
        if (points > 0) usePoints(points);
    });
    
    document.querySelectorAll('.quick-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const points = parseInt(this.dataset.points);
            const maxPoints = parseInt(document.getElementById('pointsInput').getAttribute('data-max-points'));
            document.getElementById('pointsInput').value = Math.min(points, maxPoints);
        });
    });
    
    document.getElementById('removePoints')?.addEventListener('click', removePoints);
    
    document.getElementById('cancelPayment').addEventListener('click', function() {
        if (confirm('Hủy thanh toán?')) {
            window.location.href = '<?php echo BASE_URL; ?>/payment/cancel';
        }
    });
    
    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        const selectedMethod = document.querySelector('input[name="payment_method"]:checked');
        if (!selectedMethod) {
            e.preventDefault();
            alert('Vui lòng chọn phương thức thanh toán.');
        }
    });
    
    function applyPromotion(promotionId, promotionName, promotionValue) {
        fetch('<?php echo BASE_URL; ?>/payment/apply-promotion', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({promotion_id: promotionId})
        })
        .then(response => {
            console.log("[v0] Apply promotion response status:", response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return response.json();
        })
        .then(data => {
            console.log("[v0] Apply promotion response data:", data);
            
            if (data.success) {
                const selectedPromoElement = document.getElementById('selectedPromotion');
                const promotionListWrapper = document.getElementById('promotionList')?.closest('.promo-list-wrapper');
                
                if (selectedPromoElement) {
                    selectedPromoElement.style.display = 'flex';
                }
                
                const promo_name_el = document.querySelector('.selected-promo-name');
                const promo_value_el = document.querySelector('.selected-promo-value');
                
                if (promo_name_el) promo_name_el.textContent = promotionName;
                if (promo_value_el) promo_value_el.textContent = promotionValue;
                
                if (promotionListWrapper) {
                    promotionListWrapper.style.display = 'none';
                }
                
                if (data.pricing) {
                    updatePricing(data.pricing);
                }
                
                alert(data.message || 'Áp dụng thành công');
            } else {
                console.error("[v0] Apply promotion failed:", data.message);
                alert(data.message || 'Không thể áp dụng mã giảm giá');
            }
        })
        .catch(error => {
            console.error('[v0] Apply promotion error:', error);
            alert('Lỗi kết nối: ' + error.message);
        });
    }
    
    function removePromotion() {
        fetch('<?php echo BASE_URL; ?>/payment/remove-promotion', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'}
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('selectedPromotion').style.display = 'none';
                document.getElementById('promotionList').closest('.promo-list-wrapper').style.display = 'block';
                updatePricing(data.pricing);
                alert('Đã bỏ chọn mã giảm giá');
            } else {
                alert(data.message);
            }
        })
        .catch(() => location.reload());
    }
    
    function usePoints(points) {
        const maxPoints = parseInt(document.getElementById('pointsInput').getAttribute('data-max-points'));
        if (points > maxPoints) points = maxPoints;
        
        fetch('<?php echo BASE_URL; ?>/payment/use-points', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({points: points})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('usedPoints').style.display = 'flex';
                document.getElementById('usedPointsValue').textContent = points.toLocaleString();
                document.querySelector('.loyalty-form').style.display = 'none';
                updatePricing(data.pricing);
                alert(data.message);
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra.');
        });
    }
    
    function removePoints() {
        fetch('<?php echo BASE_URL; ?>/payment/remove-points', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'}
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('usedPoints').style.display = 'none';
                document.querySelector('.loyalty-form').style.display = 'block';
                document.getElementById('pointsInput').value = '';
                updatePricing(data.pricing);
                alert('Đã bỏ sử dụng điểm tích lũy');
            } else {
                alert(data.message);
            }
        })
        .catch(() => location.reload());
    }
    
    function updatePricing(pricing) {
        const originalPriceEl = document.getElementById('originalPrice');
        const finalPriceEl = document.getElementById('finalPrice');
        const selectedPromoEl = document.getElementById('selectedPromotion');
        const usedPointsEl = document.getElementById('usedPoints');
        
        if (originalPriceEl && pricing.original_price !== undefined) {
            originalPriceEl.textContent = pricing.original_price.toLocaleString() + 'đ';
        }
        
        if (finalPriceEl && pricing.final_price !== undefined) {
            finalPriceEl.textContent = pricing.final_price.toLocaleString() + 'đ';
        }
        
        if (selectedPromoEl && selectedPromoEl.style.display !== 'none') {
            const promotionDiscountEl = document.getElementById('promotionDiscount');
            const promotionDiscountValueEl = document.getElementById('promotionDiscountValue');
            
            if (promotionDiscountEl) {
                promotionDiscountEl.style.display = 'flex';
            }
            if (promotionDiscountValueEl) {
                promotionDiscountValueEl.textContent = Math.round(pricing.promotion_discount || 0).toLocaleString();
            }
        } else if (document.getElementById('promotionDiscount')) {
            document.getElementById('promotionDiscount').style.display = 'none';
        }
        
        if (usedPointsEl && usedPointsEl.style.display !== 'none') {
            const pointsDiscountItemEl = document.getElementById('pointsDiscountItem');
            const pointsDiscountValueEl = document.getElementById('pointsDiscountValue');
            
            if (pointsDiscountItemEl) {
                pointsDiscountItemEl.style.display = 'flex';
            }
            if (pointsDiscountValueEl) {
                pointsDiscountValueEl.textContent = Math.round(pricing.points_discount || 0).toLocaleString();
            }
        } else if (document.getElementById('pointsDiscountItem')) {
            document.getElementById('pointsDiscountItem').style.display = 'none';
        }
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
