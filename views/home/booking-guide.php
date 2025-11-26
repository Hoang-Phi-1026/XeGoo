<?php
// Include header
require_once __DIR__ . '/../layouts/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/booking-guide.css">

<div class="booking-guide-page">

    <section class="guide-hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-badge">
                    <i class="fas fa-book-open"></i>
                    <span>Hướng dẫn chi tiết</span>
                </div>
                <h1 class="hero-title">Hướng dẫn đặt vé xe khách trên <span class="gradient-text">XeGoo</span></h1>
                <p class="hero-subtitle">
                    Tìm hiểu cách đặt vé nhanh chóng, dễ dàng và an toàn. Khám phá các tuyến đường và loại xe có sẵn trong hệ thống của chúng tôi.
                </p>
                <div class="hero-actions">
                    <a href="<?php echo BASE_URL; ?>/search" class="btn btn-primary btn-large">
                        <i class="fas fa-search"></i>
                        Tìm chuyến xe ngay
                    </a>
                    <a href="#steps" class="btn btn-outline btn-large">
                        <i class="fas fa-arrow-down"></i>
                        Xem hướng dẫn
                    </a>
                </div>
            </div>
        </div>
    </section>


    <section class="quick-nav">
        <div class="container">
            <div class="nav-grid">
                <a href="#steps" class="nav-item">
                    <i class="fas fa-list-ol"></i>
                    <span>Các bước đặt vé</span>
                </a>
                <a href="#routes" class="nav-item">
                    <i class="fas fa-route"></i>
                    <span>Tuyến đường</span>
                </a>
                <a href="#vehicles" class="nav-item">
                    <i class="fas fa-bus"></i>
                    <span>Loại xe</span>
                </a>
                <a href="#payment" class="nav-item">
                    <i class="fas fa-credit-card"></i>
                    <span>Thanh toán</span>
                </a>
                <a href="#faq" class="nav-item">
                    <i class="fas fa-question-circle"></i>
                    <span>Câu hỏi thường gặp</span>
                </a>
            </div>
        </div>
    </section>

    <section class="steps-section" id="steps">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Các bước đặt vé <span class="highlight-text">chi tiết</span></h2>
                <p class="section-subtitle">Làm theo 4 bước đơn giản để hoàn tất đặt vé của bạn</p>
            </div>

            <div class="steps-timeline">
                <div class="step-item">
                    <div class="step-number">01</div>
                    <div class="step-content">
                        <div class="step-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3 class="step-title">Tìm kiếm chuyến xe</h3>
                        <p class="step-description">
                            Truy cập trang chủ XeGoo và nhập thông tin tìm kiếm:
                        </p>
                        <ul class="step-list">
                            <li><i class="fas fa-check-circle"></i> Chọn điểm đi (thành phố khởi hành)</li>
                            <li><i class="fas fa-check-circle"></i> Chọn điểm đến (thành phố đích)</li>
                            <li><i class="fas fa-check-circle"></i> Chọn ngày khởi hành</li>
                            <li><i class="fas fa-check-circle"></i> Nhấn "Tìm chuyến xe" để xem kết quả</li>
                        </ul>
                        <div class="step-tip">
                            <i class="fas fa-lightbulb"></i>
                            <span><strong>Mẹo:</strong> Đặt vé trước 1-2 ngày để có nhiều lựa chọn chỗ ngồi hơn</span>
                        </div>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-number">02</div>
                    <div class="step-content">
                        <div class="step-icon">
                            <i class="fas fa-chair"></i>
                        </div>
                        <h3 class="step-title">Chọn chuyến xe và chỗ ngồi</h3>
                        <p class="step-description">
                            Xem danh sách các chuyến xe phù hợp và chọn chuyến bạn muốn:
                        </p>
                        <ul class="step-list">
                            <li><i class="fas fa-check-circle"></i> So sánh giờ khởi hành, giá vé và loại xe</li>
                            <li><i class="fas fa-check-circle"></i> Xem đánh giá và nhà xe vận hành</li>
                            <li><i class="fas fa-check-circle"></i> Nhấn "Chọn chuyến" để xem sơ đồ ghế</li>
                            <li><i class="fas fa-check-circle"></i> Chọn vị trí ghế yêu thích trên sơ đồ</li>
                        </ul>
                        <div class="step-tip">
                            <i class="fas fa-lightbulb"></i>
                            <span><strong>Mẹo:</strong> Ghế màu xanh là ghế trống, màu đỏ là đã có người đặt</span>
                        </div>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-number">03</div>
                    <div class="step-content">
                        <div class="step-icon">
                            <i class="fas fa-user-edit"></i>
                        </div>
                        <h3 class="step-title">Điền thông tin hành khách</h3>
                        <p class="step-description">
                            Cung cấp thông tin cần thiết để hoàn tất đặt vé:
                        </p>
                        <ul class="step-list">
                            <li><i class="fas fa-check-circle"></i> Họ và tên hành khách</li>
                            <li><i class="fas fa-check-circle"></i> Số điện thoại liên hệ</li>
                            <li><i class="fas fa-check-circle"></i> Email nhận vé điện tử</li>
                            <li><i class="fas fa-check-circle"></i> Ghi chú đặc biệt (nếu có)</li>
                        </ul>
                        <div class="step-tip">
                            <i class="fas fa-lightbulb"></i>
                            <span><strong>Lưu ý:</strong> Kiểm tra kỹ thông tin trước khi tiếp tục</span>
                        </div>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-number">04</div>
                    <div class="step-content">
                        <div class="step-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h3 class="step-title">Thanh toán và nhận vé</h3>
                        <p class="step-description">
                            Chọn phương thức thanh toán và hoàn tất đặt vé:
                        </p>
                        <ul class="step-list">
                            <li><i class="fas fa-check-circle"></i> Chọn phương thức: MoMo, VNPay, hoặc tại bến</li>
                            <li><i class="fas fa-check-circle"></i> Xác nhận thông tin và thanh toán</li>
                            <li><i class="fas fa-check-circle"></i> Nhận mã vé qua SMS và Email</li>
                            <li><i class="fas fa-check-circle"></i> Xuất trình mã vé khi lên xe</li>
                        </ul>
                        <div class="step-tip">
                            <i class="fas fa-lightbulb"></i>
                            <span><strong>Quan trọng:</strong> Lưu mã vé để tra cứu và lên xe</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="routes-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Tuyến đường <span class="highlight-text">phổ biến</span></h2>
                <p class="section-subtitle">Hơn 200 tuyến đường trên toàn quốc với hơn 100 nhà xe uy tín</p>
            </div>

            <div class="routes-stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-route"></i>
                    </div>
                    <div class="stat-number">200+</div>
                    <div class="stat-label">Tuyến đường</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <div class="stat-number">63</div>
                    <div class="stat-label">Tỉnh thành</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-number">24/7</div>
                    <div class="stat-label">Hoạt động</div>
                </div>
            </div>

            <?php if (!empty($routes)): ?>
            <div class="routes-selector-container" id="routes">
                <div class="selector-header">
                    <div class="selector-icon">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <div class="selector-info">
                        <h3 class="selector-title">Chọn tuyến đường</h3>
                        <p class="selector-description">Xem thông tin chi tiết và tìm chuyến xe cho tuyến đường bạn quan tâm</p>
                    </div>
                </div>

                <div class="route-dropdown-wrapper">
                    <label for="route-select" class="dropdown-label">
                        <i class="fas fa-route"></i>
                        Các đường có sẵn
                    </label>
                    <select id="route-select" class="route-dropdown">
                        <option value="">Chọn tuyến đường</option>
                        <?php foreach ($routes as $route): ?>
                        <option 
                            value="<?php echo htmlspecialchars($route['maTuyenDuong']); ?>"
                            data-from="<?php echo htmlspecialchars($route['diemDi']); ?>"
                            data-to="<?php echo htmlspecialchars($route['diemDen']); ?>"
                            data-distance="<?php echo number_format($route['khoangCach']); ?>"
                            data-time="<?php echo htmlspecialchars($route['thoiGianDiChuyen']); ?>"
                            data-code="<?php echo htmlspecialchars($route['kyHieuTuyen']); ?>"
                            data-status="<?php echo htmlspecialchars($route['trangThai']); ?>">
                            <?php echo htmlspecialchars($route['kyHieuTuyen']); ?> - 
                            <?php echo htmlspecialchars($route['diemDi']); ?> → 
                            <?php echo htmlspecialchars($route['diemDen']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="route-details" class="route-details-card" style="display: none;">
                    <div class="details-header">
                        <div class="route-code-badge"></div>
                        <div class="route-status-badge"></div>
                    </div>
                    <div class="details-path">
                        <div class="path-point start-point">
                            <i class="fas fa-map-marker-alt"></i>
                            <div class="point-info">
                                <span class="point-label">Điểm đi</span>
                                <span class="point-name" id="detail-from"></span>
                            </div>
                        </div>
                        <div class="path-connector">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                        <div class="path-point end-point">
                            <i class="fas fa-map-marker-alt"></i>
                            <div class="point-info">
                                <span class="point-label">Điểm đến</span>
                                <span class="point-name" id="detail-to"></span>
                            </div>
                        </div>
                    </div>
                    <div class="details-info">
                        <div class="info-item">
                            <i class="fas fa-road"></i>
                            <div class="info-content">
                                <span class="info-label">Khoảng cách</span>
                                <span class="info-value" id="detail-distance"></span>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-clock"></i>
                            <div class="info-content">
                                <span class="info-label">Thời gian</span>
                                <span class="info-value" id="detail-time"></span>
                            </div>
                        </div>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/search" class="details-action-btn">
                        <i class="fas fa-search"></i>
                        Tìm chuyến xe
                    </a>
                </div>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const routeSelect = document.getElementById('route-select');
                const routeDetails = document.getElementById('route-details');
                
                routeSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    
                    if (this.value) {
                        // Get data from selected option
                        const from = selectedOption.dataset.from;
                        const to = selectedOption.dataset.to;
                        const distance = selectedOption.dataset.distance;
                        const time = selectedOption.dataset.time;
                        const code = selectedOption.dataset.code;
                        const status = selectedOption.dataset.status;
                        
                        // Update details card
                        document.querySelector('.route-code-badge').textContent = code;
                        document.getElementById('detail-from').textContent = from;
                        document.getElementById('detail-to').textContent = to;
                        document.getElementById('detail-distance').textContent = distance + ' km';
                        document.getElementById('detail-time').textContent = time;
                        
                        // Update status badge
                        const statusBadge = document.querySelector('.route-status-badge');
                        statusBadge.textContent = status;
                        statusBadge.className = 'route-status-badge ' + (status === 'Đang hoạt động' ? 'active' : 'inactive');
                        
                        // Show details card with animation
                        routeDetails.style.display = 'block';
                        setTimeout(() => {
                            routeDetails.classList.add('show');
                        }, 10);
                    } else {
                        // Hide details card
                        routeDetails.classList.remove('show');
                        setTimeout(() => {
                            routeDetails.style.display = 'none';
                        }, 300);
                    }
                });
            });
            </script>
            <?php endif; ?>

            <div class="routes-cta">
                <p>Không tìm thấy tuyến đường bạn cần?</p>
                <a href="<?php echo BASE_URL; ?>/search" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                    Xem tất cả tuyến đường
                </a>
            </div>
        </div>
    </section>

    <section class="vehicles-section" id="vehicles">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Loại xe <span class="highlight-text">đa dạng</span></h2>
                <p class="section-subtitle">Nhiều loại xe phù hợp với mọi nhu cầu di chuyển của bạn</p>
            </div>

            <div class="vehicles-grid">
                <div class="vehicle-card featured">
                    <div class="vehicle-badge">Phổ biến</div>
                    <div class="vehicle-image">
                        <img src="/placeholder.svg?height=200&width=350" alt="Xe giường nằm">
                    </div>
                    <div class="vehicle-content">
                        <h3 class="vehicle-title">Xe giường nằm</h3>
                        <p class="vehicle-description">
                            Xe giường nằm cao cấp với 40-45 giường, phù hợp cho các chuyến đi dài. Trang bị điều hòa, chăn gối, wifi miễn phí.
                        </p>
                        <div class="vehicle-features">
                            <div class="feature-item">
                                <i class="fas fa-bed"></i>
                                <span>40-45 giường</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-snowflake"></i>
                                <span>Điều hòa</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-wifi"></i>
                                <span>Wifi miễn phí</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-tv"></i>
                                <span>Giải trí</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="vehicle-card">
                    <div class="vehicle-image">
                        <img src="/placeholder.svg?height=200&width=350" alt="Xe ghế ngồi">
                    </div>
                    <div class="vehicle-content">
                        <h3 class="vehicle-title">Xe ghế ngồi</h3>
                        <p class="vehicle-description">
                            Xe ghế ngồi 45 chỗ tiện lợi cho các chuyến đi ngắn và trung bình. Ghế ngồi êm ái, không gian thoáng mát.
                        </p>
                        <div class="vehicle-features">
                            <div class="feature-item">
                                <i class="fas fa-chair"></i>
                                <span>45 chỗ ngồi</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-snowflake"></i>
                                <span>Điều hòa</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-charging-station"></i>
                                <span>Sạc điện thoại</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-water"></i>
                                <span>Nước uống</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="vehicle-card">
                    <div class="vehicle-image">
                        <img src="/placeholder.svg?height=200&width=350" alt="Xe Limousine">
                    </div>
                    <div class="vehicle-content">
                        <h3 class="vehicle-title">Xe Limousine</h3>
                        <p class="vehicle-description">
                            Xe Limousine VIP 9-24 chỗ cao cấp với ghế massage, màn hình riêng. Trải nghiệm sang trọng nhất.
                        </p>
                        <div class="vehicle-features">
                            <div class="feature-item">
                                <i class="fas fa-couch"></i>
                                <span>9-24 chỗ VIP</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-spa"></i>
                                <span>Ghế massage</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-tv"></i>
                                <span>Màn hình riêng</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-concierge-bell"></i>
                                <span>Dịch vụ VIP</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="vehicle-card">
                    <div class="vehicle-image">
                        <img src="/placeholder.svg?height=200&width=350" alt="Xe 16 chỗ">
                    </div>
                    <div class="vehicle-content">
                        <h3 class="vehicle-title">Xe 16 chỗ</h3>
                        <p class="vehicle-description">
                            Xe 16 chỗ linh hoạt, phù hợp cho nhóm nhỏ hoặc gia đình. Có thể thuê theo ngày hoặc theo tuyến.
                        </p>
                        <div class="vehicle-features">
                            <div class="feature-item">
                                <i class="fas fa-users"></i>
                                <span>16 chỗ ngồi</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-snowflake"></i>
                                <span>Điều hòa</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-suitcase"></i>
                                <span>Hành lý rộng</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-calendar-alt"></i>
                                <span>Thuê theo ngày</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="vehicle-card">
                    <div class="vehicle-image">
                        <img src="/placeholder.svg?height=200&width=350" alt="Xe 4-7 chỗ">
                    </div>
                    <div class="vehicle-content">
                        <h3 class="vehicle-title">Xe 4-7 chỗ</h3>
                        <p class="vehicle-description">
                            Xe ô tô 4-7 chỗ cao cấp với tài xế riêng. Linh hoạt lịch trình, phù hợp cho công tác và du lịch.
                        </p>
                        <div class="vehicle-features">
                            <div class="feature-item">
                                <i class="fas fa-car"></i>
                                <span>4-7 chỗ</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-user-tie"></i>
                                <span>Tài xế riêng</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-route"></i>
                                <span>Linh hoạt</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-star"></i>
                                <span>Cao cấp</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="vehicle-card">
                    <div class="vehicle-image">
                        <img src="/placeholder.svg?height=200&width=350" alt="Xe giường đôi">
                    </div>
                    <div class="vehicle-content">
                        <h3 class="vehicle-title">Xe giường đôi</h3>
                        <p class="vehicle-description">
                            Xe giường đôi 2 tầng với 34 giường, phù hợp cho cặp đôi hoặc gia đình. Riêng tư và thoải mái.
                        </p>
                        <div class="vehicle-features">
                            <div class="feature-item">
                                <i class="fas fa-bed"></i>
                                <span>34 giường đôi</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-user-friends"></i>
                                <span>Riêng tư</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-wifi"></i>
                                <span>Wifi</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-tv"></i>
                                <span>Giải trí</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="payment-section" id="payment">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Phương thức <span class="highlight-text">thanh toán</span></h2>
                <p class="section-subtitle">Đa dạng phương thức thanh toán an toàn và tiện lợi</p>
            </div>

            <div class="payment-grid">
                <div class="payment-card">
                    <div class="payment-icon">
                        <img src="/placeholder.svg?height=60&width=60" alt="MoMo">
                    </div>
                    <h3 class="payment-title">Ví MoMo</h3>
                    <p class="payment-description">
                        Thanh toán nhanh chóng qua ví điện tử MoMo. Hỗ trợ quét mã QR hoặc liên kết ví.
                    </p>
                    <div class="payment-features">
                        <span class="feature-tag"><i class="fas fa-check"></i> Nhanh chóng</span>
                        <span class="feature-tag"><i class="fas fa-check"></i> An toàn</span>
                        <span class="feature-tag"><i class="fas fa-check"></i> Ưu đãi</span>
                    </div>
                </div>

                <div class="payment-card">
                    <div class="payment-icon">
                        <img src="/placeholder.svg?height=60&width=60" alt="VNPay">
                    </div>
                    <h3 class="payment-title">VNPay</h3>
                    <p class="payment-description">
                        Thanh toán qua cổng VNPay với thẻ ATM, thẻ tín dụng hoặc ví điện tử.
                    </p>
                    <div class="payment-features">
                        <span class="feature-tag"><i class="fas fa-check"></i> Đa dạng</span>
                        <span class="feature-tag"><i class="fas fa-check"></i> Bảo mật</span>
                        <span class="feature-tag"><i class="fas fa-check"></i> Tiện lợi</span>
                    </div>
                </div>

            </div>

            <div class="payment-note">
                <div class="note-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="note-content">
                    <h4>Bảo mật thanh toán</h4>
                    <p>Mọi giao dịch đều được mã hóa và bảo mật theo tiêu chuẩn quốc tế. Thông tin thẻ của bạn được bảo vệ tuyệt đối.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="faq-section" id="faq">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Câu hỏi <span class="highlight-text">thường gặp</span></h2>
                <p class="section-subtitle">Giải đáp các thắc mắc phổ biến về đặt vé xe khách</p>
            </div>

            <div class="faq-grid">
                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-question-circle"></i>
                        <h4>Làm thế nào để đặt vé trên XeGoo?</h4>
                    </div>
                    <div class="faq-answer">
                        <p>Bạn chỉ cần làm theo 4 bước: (1) Tìm kiếm chuyến xe phù hợp, (2) Chọn chỗ ngồi, (3) Điền thông tin hành khách, (4) Thanh toán và nhận vé điện tử. Toàn bộ quá trình chỉ mất 2-3 phút.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-question-circle"></i>
                        <h4>Tôi có thể hủy vé đã đặt không?</h4>
                    </div>
                    <div class="faq-answer">
                        <p>Có, bạn có thể hủy vé trước giờ khởi hành ít nhất 24 giờ để được hoàn tiền. Phí hủy vé tùy thuộc vào chính sách của từng nhà xe. Vui lòng liên hệ hotline để được hỗ trợ.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-question-circle"></i>
                        <h4>Tôi có nhận được vé giấy không?</h4>
                    </div>
                    <div class="faq-answer">
                        <p>Không cần thiết. Sau khi đặt vé thành công, bạn sẽ nhận mã vé điện tử qua SMS và Email. Chỉ cần xuất trình mã vé này khi lên xe. Tuy nhiên, bạn có thể in vé nếu muốn.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-question-circle"></i>
                        <h4>Thanh toán có an toàn không?</h4>
                    </div>
                    <div class="faq-answer">
                        <p>Hoàn toàn an toàn. XeGoo sử dụng các cổng thanh toán uy tín như MoMo, VNPay với mã hóa SSL. Thông tin thẻ của bạn không được lưu trữ trên hệ thống của chúng tôi.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-question-circle"></i>
                        <h4>Tôi có thể đổi chuyến xe không?</h4>
                    </div>
                    <div class="faq-answer">
                        <p>Có, bạn có thể đổi sang chuyến khác cùng tuyến trước giờ khởi hành 12 giờ (tùy chính sách nhà xe). Liên hệ hotline hoặc vào mục "Quản lý vé" để thực hiện đổi chuyến.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-question-circle"></i>
                        <h4>Hành lý được mang theo như thế nào?</h4>
                    </div>
                    <div class="faq-answer">
                        <p>Mỗi hành khách được mang 1 hành lý xách tay (dưới 7kg) và 1 hành lý ký gửi (dưới 20kg) miễn phí. Hành lý vượt quá sẽ tính phí theo quy định của nhà xe.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-question-circle"></i>
                        <h4>Xe có wifi không?</h4>
                    </div>
                    <div class="faq-answer">
                        <p>Hầu hết các xe hiện đại đều có wifi miễn phí. Tuy nhiên, tùy từng nhà xe và loại xe mà tiện nghi có thể khác nhau. Bạn có thể xem thông tin chi tiết khi chọn chuyến.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-question-circle"></i>
                        <h4>Tôi cần đến bến trước bao lâu?</h4>
                    </div>
                    <div class="faq-answer">
                        <p>Bạn nên có mặt tại bến trước giờ khởi hành 15-30 phút để làm thủ tục lên xe. Đối với các chuyến dài hoặc dịp lễ, nên đến sớm hơn 30-45 phút.</p>
                    </div>
                </div>
            </div>

            <div class="faq-cta">
                <p>Vẫn còn thắc mắc?</p>
                <a href="<?php echo BASE_URL; ?>/search" class="btn btn-outline">
                    <i class="fas fa-headset"></i>
                    Liên hệ hỗ trợ
                </a>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <div class="cta-icon">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <h2 class="cta-title">Sẵn sàng đặt vé ngay?</h2>
                <p class="cta-text">
                    Hãy bắt đầu hành trình của bạn với XeGoo. Đặt vé nhanh chóng, dễ dàng và an toàn.
                </p>
                <div class="cta-buttons">
                    <a href="<?php echo BASE_URL; ?>/search" class="btn btn-primary btn-large">
                        <i class="fas fa-search"></i>
                        Tìm chuyến xe ngay
                    </a>
                    <a href="<?php echo BASE_URL; ?>/about" class="btn btn-white btn-large">
                        <i class="fas fa-info-circle"></i>
                        Về XeGoo
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>

<?php
// Include footer
require_once __DIR__ . '/../layouts/footer.php';
?>
