<?php
// Include header
require_once __DIR__ . '/../layouts/header.php';
?>

<!-- About Page Styles -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/about.css">

<div class="about-page">
    <!-- Hero Section -->
    <section class="about-hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <div class="hero-badge">
                        <i class="fas fa-star"></i>
                        <span>Nền tảng đặt vé hàng đầu Việt Nam</span>
                    </div>
                    <h1 class="hero-title">
                        Kết nối hành trình, <span class="gradient-text">Tạo nên trải nghiệm</span>
                    </h1>
                    <p class="hero-subtitle">
                        XeGoo là nền tảng đặt vé xe khách và dịch vụ di chuyển toàn diện, mang đến cho bạn những chuyến đi an toàn, tiện lợi và đáng tin cậy trên khắp Việt Nam.
                    </p>
                    <div class="hero-actions">
                        <a href="<?php echo BASE_URL; ?>/search" class="btn btn-primary btn-large">
                            <i class="fas fa-ticket-alt"></i>
                            Đặt vé ngay
                        </a>
                        <a href="#services" class="btn btn-outline btn-large">
                            <i class="fas fa-info-circle"></i>
                            Khám phá dịch vụ
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-decoration">
            <div class="decoration-circle circle-1"></div>
            <div class="decoration-circle circle-2"></div>
            <div class="decoration-circle circle-3"></div>
        </div>
    </section>

    <!-- Quick Stats Banner -->
    <section class="quick-stats">
        <div class="container">
            <div class="stats-row">
                <div class="stat-box">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">50,000+</div>
                        <div class="stat-label">Khách hàng tin tưởng</div>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon">
                        <i class="fas fa-route"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">200+</div>
                        <div class="stat-label">Tuyến đường phủ sóng</div>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon">
                        <i class="fas fa-bus"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">100+</div>
                        <div class="stat-label">Nhà xe đối tác</div>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon">
                        <i class="fas fa-smile"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number">99%</div>
                        <div class="stat-label">Hài lòng</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Story Section -->
    <section class="story-section">
        <div class="container">
            <div class="story-grid">
                <div class="story-image">
                    <div class="image-wrapper">
                        <img src="/placeholder.svg?height=500&width=600" alt="XeGoo Bus Interior">
                        <div class="image-badge">
                            <i class="fas fa-award"></i>
                            <span>Chất lượng hàng đầu</span>
                        </div>
                    </div>
                </div>
                <div class="story-content">
                    <div class="section-label">
                        <i class="fas fa-book-open"></i>
                        <span>Câu chuyện của chúng tôi</span>
                    </div>
                    <h2 class="section-title">Hành trình khởi đầu từ <span class="highlight-text">đam mê</span></h2>
                    <p class="story-text">
                        Được thành lập vào năm 2020 với sứ mệnh cách mạng hóa ngành vận tải hành khách tại Việt Nam, XeGoo ra đời từ mong muốn mang đến trải nghiệm đặt vé xe khách hiện đại, minh bạch và thuận tiện nhất cho người dùng.
                    </p>
                    <p class="story-text">
                        Chúng tôi hiểu rằng mỗi chuyến đi không chỉ là việc di chuyển từ điểm A đến điểm B, mà còn là những kỷ niệm, những khoảnh khắc đáng nhớ. Vì vậy, XeGoo cam kết đồng hành cùng bạn trong mọi hành trình, từ những chuyến công tác quan trọng đến những chuyến du lịch đáng nhớ.
                    </p>
                    <div class="story-features">
                        <div class="feature-badge">
                            <i class="fas fa-check-circle"></i>
                            <span>Công nghệ hiện đại</span>
                        </div>
                        <div class="feature-badge">
                            <i class="fas fa-check-circle"></i>
                            <span>Dịch vụ tận tâm</span>
                        </div>
                        <div class="feature-badge">
                            <i class="fas fa-check-circle"></i>
                            <span>Giá cả minh bạch</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services-section" id="services">
        <div class="container">
            <div class="section-header">
                <div class="section-label">
                    <i class="fas fa-concierge-bell"></i>
                    <span>Dịch vụ của chúng tôi</span>
                </div>
                <h2 class="section-title">Giải pháp di chuyển <span class="highlight-text">toàn diện</span></h2>
                <p class="section-subtitle">
                    XeGoo cung cấp đa dạng dịch vụ để đáp ứng mọi nhu cầu di chuyển của bạn
                </p>
            </div>
            <div class="services-grid">
                <div class="service-card featured">
                    <div class="service-icon">
                        <i class="fas fa-bus-alt"></i>
                    </div>
                    <div class="service-badge">Phổ biến nhất</div>
                    <h3 class="service-title">Đặt vé xe khách</h3>
                    <p class="service-description">
                        Đặt vé xe khách liên tỉnh dễ dàng với hơn 200 tuyến đường trên toàn quốc. Chọn chỗ ngồi, thanh toán online và nhận vé điện tử ngay lập tức.
                    </p>
                    <ul class="service-features">
                        <li><i class="fas fa-check"></i> Hơn 200 tuyến đường</li>
                        <li><i class="fas fa-check"></i> Chọn chỗ ngồi tự do</li>
                        <li><i class="fas fa-check"></i> Thanh toán đa dạng</li>
                        <li><i class="fas fa-check"></i> Vé điện tử tiện lợi</li>
                    </ul>
                    <a href="<?php echo BASE_URL; ?>/search" class="service-link">
                        Đặt vé ngay <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-car"></i>
                    </div>
                    <h3 class="service-title">Thuê xe theo ngày</h3>
                    <p class="service-description">
                        Thuê xe 4-7 chỗ theo ngày với tài xế chuyên nghiệp. Linh hoạt lịch trình, phù hợp cho du lịch gia đình, công tác hoặc sự kiện.
                    </p>
                    <ul class="service-features">
                        <li><i class="fas fa-check"></i> Xe 4-7 chỗ đời mới</li>
                        <li><i class="fas fa-check"></i> Tài xế kinh nghiệm</li>
                        <li><i class="fas fa-check"></i> Linh hoạt lịch trình</li>
                        <li><i class="fas fa-check"></i> Giá cả cạnh tranh</li>
                    </ul>
                    <a href="<?php echo BASE_URL; ?>/search" class="service-link">
                        Tìm hiểu thêm <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-shuttle-van"></i>
                    </div>
                    <h3 class="service-title">Thuê xe 16-29 chỗ</h3>
                    <p class="service-description">
                        Dịch vụ thuê xe du lịch 16-29 chỗ cho đoàn, công ty. Xe đời mới, trang bị đầy đủ tiện nghi, phù hợp cho các chuyến đi dài ngày.
                    </p>
                    <ul class="service-features">
                        <li><i class="fas fa-check"></i> Xe 16-29 chỗ</li>
                        <li><i class="fas fa-check"></i> Trang bị hiện đại</li>
                        <li><i class="fas fa-check"></i> Phù hợp đoàn thể</li>
                        <li><i class="fas fa-check"></i> Hỗ trợ lịch trình</li>
                    </ul>
                    <a href="<?php echo BASE_URL; ?>/search" class="service-link">
                        Tìm hiểu thêm <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-bus"></i>
                    </div>
                    <h3 class="service-title">Thuê xe 45 chỗ</h3>
                    <p class="service-description">
                        Xe khách 45 chỗ cao cấp cho các chuyến đi đông người. Phù hợp cho tour du lịch, sự kiện công ty, đưa đón nhân viên.
                    </p>
                    <ul class="service-features">
                        <li><i class="fas fa-check"></i> Xe 45 chỗ cao cấp</li>
                        <li><i class="fas fa-check"></i> Điều hòa 2 chiều</li>
                        <li><i class="fas fa-check"></i> Ghế ngồi êm ái</li>
                        <li><i class="fas fa-check"></i> Giá ưu đãi đoàn</li>
                    </ul>
                    <a href="<?php echo BASE_URL; ?>/search" class="service-link">
                        Tìm hiểu thêm <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h3 class="service-title">Vận chuyển hàng hóa</h3>
                    <p class="service-description">
                        Dịch vụ vận chuyển hàng hóa an toàn, nhanh chóng. Giao hàng liên tỉnh với giá cước hợp lý, bảo hiểm hàng hóa.
                    </p>
                    <ul class="service-features">
                        <li><i class="fas fa-check"></i> Giao hàng toàn quốc</li>
                        <li><i class="fas fa-check"></i> Bảo hiểm hàng hóa</li>
                        <li><i class="fas fa-check"></i> Theo dõi đơn hàng</li>
                        <li><i class="fas fa-check"></i> Giá cước hợp lý</li>
                    </ul>
                    <a href="<?php echo BASE_URL; ?>/search" class="service-link">
                        Tìm hiểu thêm <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3 class="service-title">Hỗ trợ 24/7</h3>
                    <p class="service-description">
                        Đội ngũ chăm sóc khách hàng chuyên nghiệp, sẵn sàng hỗ trợ bạn mọi lúc, mọi nơi. Giải đáp thắc mắc và xử lý vấn đề nhanh chóng.
                    </p>
                    <ul class="service-features">
                        <li><i class="fas fa-check"></i> Hỗ trợ 24/7</li>
                        <li><i class="fas fa-check"></i> Đa kênh liên hệ</li>
                        <li><i class="fas fa-check"></i> Phản hồi nhanh chóng</li>
                        <li><i class="fas fa-check"></i> Tư vấn tận tình</li>
                    </ul>
                    <a href="<?php echo BASE_URL; ?>/search" class="service-link">
                        Liên hệ ngay <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Booking Guide Section -->
    <section class="booking-guide-section">
        <div class="container">
            <div class="guide-content">
                <div class="guide-text">
                    <div class="section-label">
                        <i class="fas fa-book"></i>
                        <span>Hướng dẫn đặt vé</span>
                    </div>
                    <h2 class="section-title">Đặt vé chỉ với <span class="highlight-text">4 bước đơn giản</span></h2>
                    <p class="guide-description">
                        Quy trình đặt vé nhanh chóng, dễ dàng và an toàn. Chỉ mất 2 phút để hoàn tất đặt vé của bạn.
                    </p>
                    <div class="guide-steps">
                        <div class="guide-step">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h4 class="step-title">Tìm kiếm chuyến xe</h4>
                                <p class="step-text">Nhập điểm đi, điểm đến và ngày khởi hành để tìm chuyến xe phù hợp</p>
                            </div>
                        </div>
                        <div class="guide-step">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h4 class="step-title">Chọn chỗ ngồi</h4>
                                <p class="step-text">Xem sơ đồ ghế và chọn vị trí ngồi yêu thích của bạn</p>
                            </div>
                        </div>
                        <div class="guide-step">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h4 class="step-title">Điền thông tin</h4>
                                <p class="step-text">Nhập thông tin hành khách và chọn phương thức thanh toán</p>
                            </div>
                        </div>
                        <div class="guide-step">
                            <div class="step-number">4</div>
                            <div class="step-content">
                                <h4 class="step-title">Nhận vé điện tử</h4>
                                <p class="step-text">Thanh toán và nhận vé điện tử qua email hoặc SMS ngay lập tức</p>
                            </div>
                        </div>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/booking-guide" class="btn btn-primary btn-large">
                        <i class="fas fa-book-open"></i>
                        Xem hướng dẫn chi tiết
                    </a>
                </div>
                <div class="guide-visual">
                    <div class="visual-card">
                        <img src="/placeholder.svg?height=400&width=350" alt="Booking Guide">
                        <div class="visual-badge">
                            <i class="fas fa-mobile-alt"></i>
                            <span>Đặt vé mọi lúc, mọi nơi</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Vision Section -->
    <section class="mission-section">
        <div class="container">
            <div class="mission-grid">
                <div class="mission-card">
                    <h3 class="mission-title">Sứ mệnh</h3>
                    <p class="mission-text">
                        Kết nối mọi người với những chuyến đi an toàn, tiện lợi và đáng tin cậy thông qua công nghệ hiện đại và dịch vụ tận tâm. Chúng tôi cam kết mang đến trải nghiệm di chuyển tốt nhất cho mọi khách hàng.
                    </p>
                </div>
                <div class="mission-card">
                    <h3 class="mission-title">Tầm nhìn</h3>
                    <p class="mission-text">
                        Trở thành nền tảng đặt vé và dịch vụ di chuyển số 1 Việt Nam, đặt tiêu chuẩn mới cho ngành vận tải hành khách với trải nghiệm khách hàng xuất sắc và công nghệ tiên tiến.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="values-section">
        <div class="container">
            <div class="section-header">
                <div class="section-label">
                    <i class="fas fa-gem"></i>
                    <span>Giá trị cốt lõi</span>
                </div>
                <h2 class="section-title">Những giá trị định hướng <span class="highlight-text">mọi hoạt động</span></h2>
                <p class="section-subtitle">
                    Cam kết của chúng tôi với khách hàng trong từng chuyến đi
                </p>
            </div>
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="value-title">An toàn</h3>
                    <p class="value-text">
                        Đảm bảo an toàn tuyệt đối cho mọi hành khách với đội ngũ lái xe chuyên nghiệp và xe được bảo dưỡng định kỳ.
                    </p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h3 class="value-title">Tin cậy</h3>
                    <p class="value-text">
                        Minh bạch trong mọi giao dịch, đúng giờ trong mọi chuyến đi, và luôn giữ lời hứa với khách hàng.
                    </p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-couch"></i>
                    </div>
                    <h3 class="value-title">Thoải mái</h3>
                    <p class="value-text">
                        Ghế ngồi êm ái, không gian rộng rãi, điều hòa mát mẻ để bạn có trải nghiệm tốt nhất.
                    </p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3 class="value-title">Tiện lợi</h3>
                    <p class="value-text">
                        Đặt vé dễ dàng chỉ với vài thao tác, thanh toán linh hoạt, và hỗ trợ 24/7.
                    </p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h3 class="value-title">Đổi mới</h3>
                    <p class="value-text">
                        Không ngừng cải tiến công nghệ và dịch vụ để mang đến trải nghiệm tốt nhất cho khách hàng.
                    </p>
                </div>
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3 class="value-title">Tận tâm</h3>
                    <p class="value-text">
                        Đặt khách hàng làm trung tâm, lắng nghe và đáp ứng mọi nhu cầu một cách chu đáo nhất.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <div class="cta-icon">
                    <i class="fas fa-rocket"></i>
                </div>
                <h2 class="cta-title">Sẵn sàng cho chuyến đi tiếp theo?</h2>
                <p class="cta-text">
                    Hãy để XeGoo đồng hành cùng bạn trong mọi hành trình. Đặt vé ngay hôm nay để trải nghiệm dịch vụ tốt nhất!
                </p>
                <div class="cta-buttons">
                    <a href="<?php echo BASE_URL; ?>/search" class="btn btn-primary btn-large">
                        <i class="fas fa-ticket-alt"></i>
                        Đặt vé ngay
                    </a>
                    <a href="<?php echo BASE_URL; ?>/register" class="btn btn-white btn-large">
                        <i class="fas fa-user-plus"></i>
                        Đăng ký tài khoản
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
