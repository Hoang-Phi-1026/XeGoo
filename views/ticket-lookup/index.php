<?php include __DIR__ . '/../layouts/header.php'; ?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/ticket-lookup.css">

<div class="ticket-lookup-container">
    <div class="lookup-header">
        <div class="header-icon">
            <i class="fas fa-search"></i>
        </div>
        <h1 class="page-title">Tra Cứu Vé</h1>
        <p class="page-subtitle">Tra cứu thông tin vé nhanh chóng mà không cần đăng nhập</p>
    </div>

    <div class="lookup-form-wrapper">
        <form id="ticketLookupForm" class="lookup-form">
            <div class="form-group">
                <label for="ticket_code">
                    <i class="fas fa-ticket-alt"></i>
                    Mã vé
                </label>
                <input 
                    type="text" 
                    id="ticket_code" 
                    name="ticket_code" 
                    placeholder="Nhập mã vé của bạn" 
                    required
                    autocomplete="off"
                >
                <small class="form-hint">Mã vé được gửi qua email sau khi đặt vé thành công</small>
            </div>

            <div class="form-group">
                <label for="contact">
                    <i class="fas fa-envelope"></i>
                    Số điện thoại hoặc Email
                </label>
                <input 
                    type="text" 
                    id="contact" 
                    name="contact" 
                    placeholder="Nhập số điện thoại hoặc email đã đặt vé" 
                    required
                    autocomplete="off"
                >
                <small class="form-hint">Nhập thông tin liên hệ bạn đã sử dụng khi đặt vé</small>
            </div>

            <button type="submit" class="btn-search">
                <i class="fas fa-search"></i>
                Tra Cứu Vé
            </button>
        </form>

        <div class="lookup-tips">
            <h3><i class="fas fa-info-circle"></i> Hướng dẫn tra cứu</h3>
            <ul>
                <li>Mã vé được gửi qua email sau khi bạn đặt vé thành công</li>
                <li>Nhập chính xác số điện thoại hoặc email đã sử dụng khi đặt vé</li>
                <li>Nếu không tìm thấy vé, vui lòng kiểm tra lại thông tin hoặc liên hệ hỗ trợ</li>
            </ul>
        </div>
    </div>

    <div id="ticketResult" class="ticket-result" style="display: none;">
        <div class="result-header">
            <h2><i class="fas fa-check-circle"></i> Thông Tin Vé</h2>
            <button id="closeResult" class="btn-close-result">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="ticket-card">
            <div class="ticket-header">
                <div class="ticket-code-badge">
                    <span class="badge-label">Mã vé</span>
                    <span class="badge-code" id="ticketCodeDisplay"></span>
                </div>
                <span class="ticket-status" id="ticketStatus"></span>
            </div>
            <div class="ticket-body">
                <div class="route-display">
                    <div class="route-point">
                        <div class="route-time" id="departureTime"></div>
                        <div class="route-city" id="cityFrom"></div>
                        <div class="route-date" id="departureDate"></div>
                    </div>
                    
                    <div class="route-connector">
                        <div class="connector-line"></div>
                        <div class="connector-icon">
                            <i class="fas fa-bus"></i>
                        </div>
                        <div class="connector-line"></div>
                    </div>
                    
                    <div class="route-point">
                        <div class="route-time" id="arrivalTime">--:--</div>
                        <div class="route-city" id="cityTo"></div>
                        <div class="route-label">Điểm đến</div>
                    </div>
                </div> 
                <div class="trip-info-grid">
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-route"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Tuyến đường</div>
                            <div class="info-value" id="routeCode"></div>
                        </div>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-bus-alt"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Loại xe</div>
                            <div class="info-value" id="vehicleType"></div>
                        </div>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Biển số xe</div>
                            <div class="info-value" id="licensePlate"></div>
                        </div>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-chair"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Số chỗ</div>
                            <div class="info-value" id="seatCapacity"></div>
                        </div>
                    </div>
                </div>
 
                <div class="passenger-section">
                    <h3 class="section-title">
                        <i class="fas fa-user-circle"></i>
                        Thông tin hành khách
                    </h3>
                    <div class="passenger-details">
                        <div class="passenger-row">
                            <div class="passenger-item">
                                <i class="fas fa-user"></i>
                                <span id="passengerName"></span>
                            </div>
                            <div class="passenger-item">
                                <i class="fas fa-envelope"></i>
                                <span id="passengerEmail"></span>
                            </div>
                            <div class="passenger-item">
                                <i class="fas fa-phone"></i>
                                <span id="passengerPhone"></span>
                            </div>
                        </div>
                        <div class="location-row">
                            <div class="location-item">
                                <div class="location-icon pickup">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="location-info">
                                    <div class="location-label">Điểm đón</div>
                                    <div class="location-value" id="pickupPoint"></div>
                                </div>
                            </div>
                            <div class="location-item">
                                <div class="location-icon dropoff">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="location-info">
                                    <div class="location-label">Điểm trả</div>
                                    <div class="location-value" id="dropoffPoint"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="qr-section">
                    <div class="qr-container">
                        <div class="qr-code-box" id="qrCodeContainer"></div>
                        <div class="qr-instructions">
                            <i class="fas fa-info-circle"></i>
                            <p>Xuất trình mã QR này khi lên xe</p>
                        </div>
                    </div>
                </div>
            </div>
 
            <div class="ticket-footer">
                <div class="footer-pattern"></div>
                <p class="footer-text">Cảm ơn bạn đã sử dụng dịch vụ XeGoo</p>
            </div>
        </div>
    </div>

    <div id="errorMessage" class="error-message" style="display: none;">
        <i class="fas fa-exclamation-circle"></i>
        <p id="errorText"></p>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>/public/js/ticket-lookup.js"></script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
