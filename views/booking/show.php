<?php include __DIR__ . '/../layouts/header.php'; ?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/booking.css">

<div class="booking-container">
    <!-- Fixed booking header to show both trips for round trip -->
    <div class="booking-header">
        <h1><?php echo ($isRoundTrip && $returnTrip) ? 'Chi tiết vé khứ hồi' : 'Chi tiết chuyến xe'; ?></h1>

        <?php if ($isRoundTrip && $returnTrip): ?>
            <div class="round-trip-info">
                <div class="trip-section outbound-trip">
                    <div class="trip-label">Chuyến đi</div>
                    <div class="route-info">
                        <span><?php echo htmlspecialchars($trip['diemDi']); ?></span>
                        <span class="route-arrow">→</span>
                        <span><?php echo htmlspecialchars($trip['diemDen']); ?></span>
                        <span>•</span>
                        <span><?php echo date('d/m/Y', strtotime($trip['ngayKhoiHanh'])); ?></span>
                        <span>•</span>
                        <span><?php echo date('H:i', strtotime($trip['thoiGianKhoiHanh'])); ?></span>
                    </div>
                </div>
                <div class="trip-section return-trip">
                    <div class="trip-label">Chuyến về</div>
                    <div class="route-info">
                        <span><?php echo htmlspecialchars($returnTrip['diemDi']); ?></span>
                        <span class="route-arrow">→</span>
                        <span><?php echo htmlspecialchars($returnTrip['diemDen']); ?></span>
                        <span>•</span>
                        <span><?php echo date('d/m/Y', strtotime($returnTrip['ngayKhoiHanh'])); ?></span>
                        <span>•</span>
                        <span><?php echo date('H:i', strtotime($returnTrip['thoiGianKhoiHanh'])); ?></span>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="route-info">
                <span><?php echo htmlspecialchars($trip['diemDi']); ?></span>
                <span class="route-arrow">→</span>
                <span><?php echo htmlspecialchars($trip['diemDen']); ?></span>
                <span>•</span>
                <span><?php echo date('d/m/Y', strtotime($trip['ngayKhoiHanh'])); ?></span>
                <span>•</span>
                <span><?php echo date('H:i', strtotime($trip['thoiGianKhoiHanh'])); ?></span>
            </div>
        <?php endif; ?>

        <a href="<?php echo BASE_URL; ?>/search" class="back-button">← Quay lại trang tìm kiếm</a>
    </div>

    <form method="POST" action="<?php echo BASE_URL; ?>/booking/process" id="bookingForm">
        <input type="hidden" name="trip_id" value="<?php echo $trip['maChuyenXe']; ?>">
        <?php if ($isRoundTrip && $returnTrip): ?>
            <input type="hidden" name="return_trip_id" value="<?php echo $returnTrip['maChuyenXe']; ?>">
            <input type="hidden" name="is_round_trip" value="1">
        <?php endif; ?>
        <input type="hidden" name="booking_type" value="<?php echo $bookingType; ?>">
        
        <!-- Added tabs for round trip seat selection -->
        <?php if ($isRoundTrip && $returnTrip): ?>
            <div class="trip-tabs">
                <button type="button" class="tab-btn active" data-trip="outbound">Chọn ghế chuyến đi</button>
                <button type="button" class="tab-btn" data-trip="return">Chọn ghế chuyến về</button>
            </div>
        <?php endif; ?>
        
        <!-- New responsive grid layout with modern card design -->
        <div class="booking-content">
            <!-- Left Column - Booking Steps -->
            <div class="booking-steps">
                <!-- Step 1: Seat Selection -->
                <div class="step-card">
                    <div class="step-header">
                        <div class="step-number">1</div>
                        <h2 class="step-title">Chọn ghế ngồi</h2>
                    </div>
                    
                    <!-- Fixed seat map includes to pass seat statuses -->
                    <div class="trip-seat-map" id="outbound-seats">
                        <h4>Chuyến đi: <?php echo htmlspecialchars($trip['diemDi'] . ' → ' . $trip['diemDen']); ?></h4>
                        <?php 
                        $currentSeatLayout = $seatLayout; 
                        $currentBookedSeats = $bookedSeats;
                        $currentTripType = 'outbound';
                        include __DIR__ . '/seat-map-partial.php'; 
                        ?>
                    </div>
                    
                    <!-- Added return trip seat map -->
                    <?php if ($isRoundTrip && $returnTrip): ?>
                        <div class="trip-seat-map" id="return-seats" style="display: none;">
                            <h4>Chuyến về: <?php echo htmlspecialchars($returnTrip['diemDi'] . ' → ' . $returnTrip['diemDen']); ?></h4>
                            <?php 
                            $currentSeatLayout = $returnSeatLayout; 
                            $currentBookedSeats = $returnBookedSeats;
                            $currentTripType = 'return';
                            include __DIR__ . '/seat-map-partial.php'; 
                            ?>
                        </div>
                    <?php endif; ?>
                    
                </div>
                
                <!-- Step 2: Pickup/Dropoff Points -->
                <div class="step-card">
                    <div class="step-header">
                        <div class="step-number">2</div>
                        <h2 class="step-title">Điểm đón/trả</h2>
                    </div>
                    
                    <!-- Updated logic to separate pickup/dropoff points and passenger information -->
                    <div class="points-section">
                        <div class="points-card">
                            <h4>Điểm đón/trả - Chuyến đi</h4>
                            <div class="points-grid">
                                <div class="point-group">
                                    <h5>Điểm đón *</h5>
                                    <select name="pickup_point" class="point-select" required>
                                        <option value="">Chọn điểm đón</option>
                                        <?php foreach ($pickupPoints as $point): ?>
                                            <option value="<?php echo $point['maDiem']; ?>">
                                                <?php echo htmlspecialchars($point['tenDiem']); ?> - <?php echo htmlspecialchars($point['diaChi']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="point-group">
                                    <h5>Điểm trả *</h5>
                                    <select name="dropoff_point" class="point-select" required>
                                        <option value="">Chọn điểm trả</option>
                                        <?php foreach ($dropoffPoints as $point): ?>
                                            <option value="<?php echo $point['maDiem']; ?>">
                                                <?php echo htmlspecialchars($point['tenDiem']); ?> - <?php echo htmlspecialchars($point['diaChi']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <?php if ($isRoundTrip && $returnTrip): ?>
                            <div class="points-card">
                                <h4>Điểm đón/trả - Chuyến về</h4>
                                <div class="points-grid">
                                    <div class="point-group">
                                        <h5>Điểm đón *</h5>
                                        <select name="return_pickup_point" class="point-select" required>
                                            <option value="">Chọn điểm đón</option>
                                            <?php foreach ($returnPickupPoints as $point): ?>
                                                <option value="<?php echo $point['maDiem']; ?>">
                                                    <?php echo htmlspecialchars($point['tenDiem']); ?> - <?php echo htmlspecialchars($point['diaChi']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="point-group">
                                        <h5>Điểm trả *</h5>
                                        <select name="return_dropoff_point" class="point-select" required>
                                            <option value="">Chọn điểm trả</option>
                                            <?php foreach ($returnDropoffPoints as $point): ?>
                                                <option value="<?php echo $point['maDiem']; ?>">
                                                    <?php echo htmlspecialchars($point['tenDiem']); ?> - <?php echo htmlspecialchars($point['diaChi']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Step 3: Passenger Information -->
                <div class="step-card">
                    <div class="step-header">
                        <div class="step-number">3</div>
                        <h2 class="step-title">Thông tin hành khách</h2>
                    </div>
                    
                    <div id="selectedSeatsDisplay" class="selected-seats-display">
                        Chưa chọn ghế nào
                    </div>
                    
                    <div class="passenger-section">
                        <div class="passenger-card">
                            <h4>Thông tin hành khách - Chuyến đi</h4>
                            <div id="outboundPassengerForms">
                                <!-- Outbound passenger forms will be dynamically added here -->
                            </div>
                        </div>

                        <?php if ($isRoundTrip && $returnTrip): ?>
                            <div class="passenger-card">
                                <h4>Thông tin hành khách - Chuyến về</h4>
                                <div id="returnPassengerForms">
                                    <!-- Return passenger forms will be dynamically added here -->
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="selected_seats" id="selectedSeatsInput">
                    <?php if ($isRoundTrip && $returnTrip): ?>
                        <input type="hidden" name="return_selected_seats" id="returnSelectedSeatsInput">
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Right Column - Price Summary -->
            <div class="price-summary">
                <h3>Tạm Tính</h3>
                <div class="price-item">
                    <span class="price-label">Chuyến đi: </span>
                    <span class="price-value" id="outboundPrice">0đ</span>
                </div>
                <?php if ($isRoundTrip && $returnTrip): ?>
                    <div class="price-item">
                        <span class="price-label">Chuyến về: </span>
                        <span class="price-value" id="returnPrice">0đ</span>
                    </div>
                <?php endif; ?>
                <div class="price-item total-row">
                    <span class="price-label">Tổng cộng:</span>
                    <span class="price-value total-price" id="totalPrice">0đ</span>
                </div>
                
                <button type="submit" class="btn-success" id="submitBtn" disabled>
                    Thanh toán
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Updated JavaScript to handle round trip booking properly -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('[v0] Booking page loaded');
    
    const outboundSeats = [];
    const returnSeats = [];
    const outboundPrice = <?php echo $trip['giaVe']; ?>;
    const returnPrice = <?php echo $returnTrip ? $returnTrip['giaVe'] : 0; ?>;
    const isRoundTrip = <?php echo ($isRoundTrip && $returnTrip) ? 'true' : 'false'; ?>;
    
    const userData = {
        name: '<?php echo isset($_SESSION['user_name']) ? addslashes($_SESSION['user_name']) : ''; ?>',
        phone: '<?php echo isset($_SESSION['user_phone']) ? addslashes($_SESSION['user_phone']) : ''; ?>',
        email: '<?php echo isset($_SESSION['user_email']) ? addslashes($_SESSION['user_email']) : ''; ?>'
    };
    const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    
    console.log('[v0] User data:', userData);
    console.log('[v0] Is logged in:', isLoggedIn);
    console.log('[v0] Is round trip:', isRoundTrip);
    
    // Tab switching for round trip
    if (isRoundTrip) {
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const tripType = this.dataset.trip;
                console.log('[v0] Switching to tab:', tripType);
                
                // Update tab buttons
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                // Show/hide seat maps
                const outboundSeats = document.getElementById('outbound-seats');
                const returnSeats = document.getElementById('return-seats');
                
                if (outboundSeats) {
                    outboundSeats.style.display = tripType === 'outbound' ? 'block' : 'none';
                }
                if (returnSeats) {
                    returnSeats.style.display = tripType === 'return' ? 'block' : 'none';
                }
            });
        });
    }
    
    // Seat selection handling - Fixed to work with both trip types
    function attachSeatListeners() {
        document.querySelectorAll('.seat.available').forEach(btn => {
            btn.addEventListener('click', function() {
                const seatNum = this.dataset.seat;
                const isReturnSeat = this.closest('#return-seats') !== null;
                const currentSeats = isReturnSeat ? returnSeats : outboundSeats;
                
                console.log('[v0] Seat clicked:', seatNum, 'isReturn:', isReturnSeat);
                
                if (this.classList.contains('selected')) {
                    this.classList.remove('selected');
                    this.classList.add('available');
                    const index = currentSeats.indexOf(seatNum);
                    if (index > -1) {
                        currentSeats.splice(index, 1);
                    }
                } else {
                    this.classList.remove('available');
                    this.classList.add('selected');
                    currentSeats.push(seatNum);
                }
                
                console.log('[v0] Current seats - outbound:', outboundSeats, 'return:', returnSeats);
                
                updateSelectedSeats();
                updatePassengerForms();
                updatePriceSummary();
            });
        });
    }
    
    // Initial attachment
    attachSeatListeners();
    
    function updateSelectedSeats() {
        const display = document.getElementById('selectedSeatsDisplay');
        const outboundInput = document.getElementById('selectedSeatsInput');
        const returnInput = document.getElementById('returnSelectedSeatsInput');
        
        let displayText = '';
        if (outboundSeats.length > 0) {
            displayText += `<strong> Chuyến đi: </strong> Ghế ${outboundSeats.sort().join(', ')}`;
        }
        if (returnSeats.length > 0) {
            if (displayText) displayText += '<br>';
            displayText += `<strong> Chuyến về: </strong> Ghế ${returnSeats.sort().join(', ')}`;
        }
        
        if (!displayText) {
            display.textContent = 'Chưa chọn ghế nào';
            display.className = 'selected-seats-display';
        } else {
            display.innerHTML = displayText;
            display.className = 'selected-seats-display has-seats';
        }
        
        outboundInput.value = JSON.stringify(outboundSeats);
        if (returnInput) {
            returnInput.value = JSON.stringify(returnSeats);
        }
    }
    
    function updatePassengerForms() {
        const outboundContainer = document.getElementById('outboundPassengerForms');
        const returnContainer = document.getElementById('returnPassengerForms');
        
        if (outboundContainer) {
            outboundContainer.innerHTML = '';
            outboundSeats.forEach((seat, index) => {
                const form = document.createElement('div');
                form.className = 'passenger-form';
                const isFirstTicket = index === 0;
                
                const nameValue = (isLoggedIn && isFirstTicket && userData.name) ? userData.name : '';
                const emailValue = (isLoggedIn && isFirstTicket && userData.email) ? userData.email : '';
                const phoneValue = (isLoggedIn && isFirstTicket && userData.phone) ? userData.phone : '';
                
                form.innerHTML = `
                    <div class="passenger-title">
                        Hành khách ${index + 1} 
                        <span class="seat-badge"> Ghế ${seat} (đi)</span>
                    </div>
                    <div class="passenger-fields">
                        <div class="form-group">
                            <label class="form-label">Họ và tên *</label>
                            <input type="text" name="passengers[${index}][ho_ten]" class="form-input" 
                                   value="${nameValue}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email *</label>
                            <input type="email" name="passengers[${index}][email]" class="form-input" 
                                   value="${emailValue}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Số điện thoại *</label>
                            <input type="tel" name="passengers[${index}][so_dien_thoai]" class="form-input" 
                                   value="${phoneValue}" required>
                        </div>
                    </div>
                `;
                outboundContainer.appendChild(form);
            });
        }

        if (returnContainer) {
            returnContainer.innerHTML = '';
            returnSeats.forEach((seat, index) => {
                const form = document.createElement('div');
                form.className = 'passenger-form';
                const isFirstTicket = index === 0;
                
                const nameValue = (isLoggedIn && isFirstTicket && userData.name) ? userData.name : '';
                const emailValue = (isLoggedIn && isFirstTicket && userData.email) ? userData.email : '';
                const phoneValue = (isLoggedIn && isFirstTicket && userData.phone) ? userData.phone : '';
                
                form.innerHTML = `
                    <div class="passenger-title">
                        Hành khách ${index + 1} 
                        <span class="seat-badge"> Ghế ${seat} (về)</span>
                    </div>
                    <div class="passenger-fields">
                        <div class="form-group">
                            <label class="form-label">Họ và tên *</label>
                            <input type="text" name="return_passengers[${index}][ho_ten]" class="form-input" 
                                   value="${nameValue}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email *</label>
                            <input type="email" name="return_passengers[${index}][email]" class="form-input" 
                                   value="${emailValue}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Số điện thoại *</label>
                            <input type="tel" name="return_passengers[${index}][so_dien_thoai]" class="form-input" 
                                   value="${phoneValue}" required>
                        </div>
                    </div>
                `;
                returnContainer.appendChild(form);
            });
        }
    }
    
    function updatePriceSummary() {
        const outboundTotal = outboundSeats.length * outboundPrice;
        const returnTotal = returnSeats.length * returnPrice;
        const grandTotal = outboundTotal + returnTotal;
        
        document.getElementById('outboundPrice').textContent = new Intl.NumberFormat('vi-VN').format(outboundTotal) + 'đ';
        if (document.getElementById('returnPrice')) {
            document.getElementById('returnPrice').textContent = new Intl.NumberFormat('vi-VN').format(returnTotal) + 'đ';
        }
        document.getElementById('totalPrice').textContent = new Intl.NumberFormat('vi-VN').format(grandTotal) + 'đ';
        
        const submitBtn = document.getElementById('submitBtn');
        const hasRequiredSeats = isRoundTrip ? (outboundSeats.length > 0 && returnSeats.length > 0) : outboundSeats.length > 0;
        submitBtn.disabled = !hasRequiredSeats;
    }
    
    // Form validation
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        console.log('[v0] Form submission started');
        console.log('[v0] Outbound seats:', outboundSeats);
        console.log('[v0] Return seats:', returnSeats);
        
        const hasRequiredSeats = isRoundTrip ? (outboundSeats.length > 0 && returnSeats.length > 0) : outboundSeats.length > 0;
        
        if (!hasRequiredSeats) {
            e.preventDefault();
            alert(isRoundTrip ? 'Vui lòng chọn ghế cho cả chuyến đi và chuyến về.' : 'Vui lòng chọn ít nhất một ghế.');
            return;
        }
        
        // Validate pickup/dropoff points
        const requiredSelects = isRoundTrip ? 
            ['pickup_point', 'dropoff_point', 'return_pickup_point', 'return_dropoff_point'] :
            ['pickup_point', 'dropoff_point'];
            
        for (const selectName of requiredSelects) {
            const select = document.querySelector(`select[name="${selectName}"]`);
            if (select && !select.value) {
                e.preventDefault();
                alert('Vui lòng chọn đầy đủ điểm đón và điểm trả.');
                return;
            }
        }
        
        const passengerInputs = document.querySelectorAll('#outboundPassengerForms input[required]');
        for (const input of passengerInputs) {
            if (!input.value.trim()) {
                e.preventDefault();
                alert('Vui lòng điền đầy đủ thông tin hành khách.');
                return;
            }
        }
        
        if (isRoundTrip) {
            const returnPassengerInputs = document.querySelectorAll('#returnPassengerForms input[required]');
            for (const input of returnPassengerInputs) {
                if (!input.value.trim()) {
                    e.preventDefault();
                    alert('Vui lòng điền đầy đủ thông tin hành khách cho chuyến về.');
                    return;
                }
            }
        }
        
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Đang xử lý...';
        
        console.log('[v0] Form validation passed, submitting...');
        console.log('[v0] Form action:', this.action);
        console.log('[v0] Form method:', this.method);
        
        // Log all form data
        const formData = new FormData(this);
        for (let [key, value] of formData.entries()) {
            console.log('[v0] Form data -', key + ':', value);
        }
        
        setTimeout(() => {
            console.log('[v0] Submitting form now...');
        }, 100);
    });
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
