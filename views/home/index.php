<?php
// Include header
require_once __DIR__ . '/../layouts/header.php';
?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/home.css">

<div class="home-container">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">Đặt vé xe liên tỉnh dễ dàng với XeGoo</h1>
            <p class="hero-subtitle">
                Hệ thống đặt vé xe khách trực tuyến hiện đại, an toàn và tiện lợi. 
                Kết nối bạn đến mọi miền đất nước với dịch vụ chất lượng cao.
            </p>
            <div class="hero-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo BASE_URL; ?>/booking" class="btn-hero-primary">
                        <i class="fas fa-ticket-alt"></i>
                        Đặt vé ngay
                    </a>
                    <a href="<?php echo BASE_URL; ?>/my-tickets" class="btn-hero-secondary">
                        Vé của tôi
                    </a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/register" class="btn-hero-primary">
                        <i class="fas fa-user-plus"></i>
                        Đăng ký ngay
                    </a>
                    <a href="<?php echo BASE_URL; ?>/login" class="btn-hero-secondary">
                        Đăng nhập
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="features-container">
            <h2 class="section-title">Tại sao chọn XeGoo?</h2>
            <p class="section-subtitle">
                Chúng tôi mang đến trải nghiệm đặt vé xe khách tốt nhất với những tính năng vượt trội
            </p>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="feature-title">Đặt vé 24/7</h3>
                    <p class="feature-description">
                        Hệ thống hoạt động 24/7, bạn có thể đặt vé bất cứ lúc nào, ở bất cứ đâu chỉ với vài thao tác đơn giản.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="feature-title">An toàn & Bảo mật</h3>
                    <p class="feature-description">
                        Thông tin cá nhân và thanh toán được bảo mật tuyệt đối với công nghệ mã hóa hiện đại nhất.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-route"></i>
                    </div>
                    <h3 class="feature-title">Nhiều tuyến đường</h3>
                    <p class="feature-description">
                        Kết nối hàng trăm tuyến đường khắp cả nước với đa dạng nhà xe uy tín và chất lượng cao.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3 class="feature-title">Hỗ trợ 24/7</h3>
                    <p class="feature-description">
                        Đội ngũ chăm sóc khách hàng chuyên nghiệp sẵn sàng hỗ trợ bạn mọi lúc, mọi nơi.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3 class="feature-title">Dễ sử dụng</h3>
                    <p class="feature-description">
                        Giao diện thân thiện, dễ sử dụng trên mọi thiết bị từ máy tính đến điện thoại di động.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h3 class="feature-title">Thanh toán linh hoạt</h3>
                    <p class="feature-description">
                        Hỗ trợ nhiều hình thức thanh toán: thẻ ATM, ví điện tử, chuyển khoản ngân hàng.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="stats-container">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number">50K+</div>
                    <div class="stat-label">Khách hàng tin tưởng</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">200+</div>
                    <div class="stat-label">Tuyến đường</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">100+</div>
                    <div class="stat-label">Nhà xe đối tác</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">99%</div>
                    <div class="stat-label">Khách hàng hài lòng</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="cta-content">
            <h2 class="cta-title">Bắt đầu hành trình của bạn ngay hôm nay</h2>
            <p class="cta-description">
                Tham gia cùng hàng nghìn khách hàng đã tin tưởng XeGoo cho những chuyến đi an toàn và tiện lợi.
            </p>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="<?php echo BASE_URL; ?>/register" class="btn-cta">Đăng ký miễn phí</a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>/booking" class="btn-cta">Đặt vé ngay</a>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php
// Include footer
require_once __DIR__ . '/../layouts/footer.php';
?>
