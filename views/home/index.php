<?php
// Include header
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="home-container">
    <!-- Modern Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <div class="hero-badge">
                    <span class="badge-text">🚌 Nền tảng đặt vé #1 Việt Nam</span>
                </div>
                <h1 class="hero-title">
                    Đặt vé xe liên tỉnh 
                    <span class="text-primary">dễ dàng</span> 
                    với XeGoo
                </h1>
                <p class="hero-subtitle">
                    Hệ thống đặt vé xe khách trực tuyến hiện đại, an toàn và tiện lợi. 
                    Kết nối bạn đến mọi miền đất nước với dịch vụ chất lượng cao.
                </p>
                
                <!-- Quick Booking Form -->
                <div class="quick-booking-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Điểm đi</label>
                            <select class="form-input">
                                <option>Chọn điểm đi</option>
                                <option>Hà Nội</option>
                                <option>TP. Hồ Chí Minh</option>
                                <option>Đà Nẵng</option>
                                <option>Hải Phòng</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Điểm đến</label>
                            <select class="form-input">
                                <option>Chọn điểm đến</option>
                                <option>Hà Nội</option>
                                <option>TP. Hồ Chí Minh</option>
                                <option>Đà Nẵng</option>
                                <option>Hải Phòng</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Ngày đi</label>
                            <input type="date" class="form-input" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <button class="btn btn-primary btn-lg">
                                <i class="fas fa-search"></i>
                                Tìm chuyến xe
                            </button>
                        </div>
                    </div>
                </div>

                <div class="hero-actions">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?php echo BASE_URL; ?>/booking" class="btn btn-primary btn-lg">
                            <i class="fas fa-ticket-alt"></i>
                            Đặt vé ngay
                        </a>
                        <a href="<?php echo BASE_URL; ?>/my-tickets" class="btn btn-secondary btn-lg">
                            Vé của tôi
                        </a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/register" class="btn btn-primary btn-lg">
                            <i class="fas fa-user-plus"></i>
                            Đăng ký ngay
                        </a>
                        <a href="<?php echo BASE_URL; ?>/login" class="btn btn-secondary btn-lg">
                            Đăng nhập
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="hero-image">
                <img src="/placeholder.svg?height=500&width=600" alt="XeGoo Bus Travel" class="hero-img">
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <div class="section-header text-center">
                <h2 class="section-title">Tại sao chọn XeGoo?</h2>
                <p class="section-subtitle">
                    Chúng tôi mang đến trải nghiệm đặt vé xe khách tốt nhất với những tính năng vượt trội
                </p>
            </div>
            
            <div class="features-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-lg">
                <div class="feature-card card">
                    <div class="card-body text-center">
                        <div class="feature-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3 class="feature-title">Đặt vé 24/7</h3>
                        <p class="feature-description">
                            Hệ thống hoạt động 24/7, bạn có thể đặt vé bất cứ lúc nào, ở bất cứ đâu chỉ với vài thao tác đơn giản.
                        </p>
                    </div>
                </div>
                
                <div class="feature-card card">
                    <div class="card-body text-center">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3 class="feature-title">An toàn & Bảo mật</h3>
                        <p class="feature-description">
                            Thông tin cá nhân và thanh toán được bảo mật tuyệt đối với công nghệ mã hóa hiện đại nhất.
                        </p>
                    </div>
                </div>
                
                <div class="feature-card card">
                    <div class="card-body text-center">
                        <div class="feature-icon">
                            <i class="fas fa-route"></i>
                        </div>
                        <h3 class="feature-title">Nhiều tuyến đường</h3>
                        <p class="feature-description">
                            Kết nối hàng trăm tuyến đường khắp cả nước với đa dạng nhà xe uy tín và chất lượng cao.
                        </p>
                    </div>
                </div>
                
                <div class="feature-card card">
                    <div class="card-body text-center">
                        <div class="feature-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h3 class="feature-title">Hỗ trợ 24/7</h3>
                        <p class="feature-description">
                            Đội ngũ chăm sóc khách hàng chuyên nghiệp sẵn sàng hỗ trợ bạn mọi lúc, mọi nơi.
                        </p>
                    </div>
                </div>
                
                <div class="feature-card card">
                    <div class="card-body text-center">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h3 class="feature-title">Dễ sử dụng</h3>
                        <p class="feature-description">
                            Giao diện thân thiện, dễ sử dụng trên mọi thiết bị từ máy tính đến điện thoại di động.
                        </p>
                    </div>
                </div>
                
                <div class="feature-card card">
                    <div class="card-body text-center">
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
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section bg-primary-light">
        <div class="container">
            <div class="stats-grid grid grid-cols-2 lg:grid-cols-4 gap-lg">
                <div class="stat-item text-center">
                    <div class="stat-number text-primary">50K+</div>
                    <div class="stat-label">Khách hàng tin tưởng</div>
                </div>
                <div class="stat-item text-center">
                    <div class="stat-number text-primary">200+</div>
                    <div class="stat-label">Tuyến đường</div>
                </div>
                <div class="stat-item text-center">
                    <div class="stat-number text-primary">100+</div>
                    <div class="stat-label">Nhà xe đối tác</div>
                </div>
                <div class="stat-item text-center">
                    <div class="stat-number text-primary">99%</div>
                    <div class="stat-label">Khách hàng hài lòng</div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works-section">
        <div class="container">
            <div class="section-header text-center">
                <h2 class="section-title">Cách thức hoạt động</h2>
                <p class="section-subtitle">
                    Đặt vé xe khách chỉ với 3 bước đơn giản
                </p>
            </div>
            
            <div class="steps-grid grid grid-cols-1 md:grid-cols-3 gap-xl">
                <div class="step-item text-center">
                    <div class="step-number">1</div>
                    <div class="step-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3 class="step-title">Tìm kiếm chuyến xe</h3>
                    <p class="step-description">
                        Nhập điểm đi, điểm đến và ngày khởi hành để tìm kiếm các chuyến xe phù hợp.
                    </p>
                </div>
                
                <div class="step-item text-center">
                    <div class="step-number">2</div>
                    <div class="step-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h3 class="step-title">Chọn vé và thanh toán</h3>
                    <p class="step-description">
                        Chọn chuyến xe ưng ý, chọn ghế và thanh toán an toàn qua nhiều hình thức.
                    </p>
                </div>
                
                <div class="step-item text-center">
                    <div class="step-number">3</div>
                    <div class="step-icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <h3 class="step-title">Nhận vé và lên xe</h3>
                    <p class="step-description">
                        Nhận vé điện tử qua email/SMS và xuất trình khi lên xe. Thật đơn giản!
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section bg-primary">
        <div class="container">
            <div class="cta-content text-center">
                <h2 class="cta-title">Bắt đầu hành trình của bạn ngay hôm nay</h2>
                <p class="cta-description">
                    Tham gia cùng hàng nghìn khách hàng đã tin tưởng XeGoo cho những chuyến đi an toàn và tiện lợi.
                </p>
                <div class="cta-actions">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="<?php echo BASE_URL; ?>/register" class="btn btn-lg" style="background: white; color: var(--primary);">
                            <i class="fas fa-user-plus"></i>
                            Đăng ký miễn phí
                        </a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/booking" class="btn btn-lg" style="background: white; color: var(--primary);">
                            <i class="fas fa-ticket-alt"></i>
                            Đặt vé ngay
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
// Add interactivity to the homepage
document.addEventListener('DOMContentLoaded', function() {
    // Animate stats on scroll
    const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px 0px -100px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);
    
    // Observe all feature cards and stat items
    document.querySelectorAll('.feature-card, .stat-item, .step-item').forEach(el => {
        observer.observe(el);
    });
    
    // Quick booking form functionality
    const quickBookingForm = document.querySelector('.quick-booking-form');
    if (quickBookingForm) {
        const searchBtn = quickBookingForm.querySelector('.btn-primary');
        searchBtn.addEventListener('click', function(e) {
            e.preventDefault();
            showInfo('Tính năng tìm kiếm sẽ được triển khai sớm!');
        });
    }
});
</script>

<?php
// Include footer
require_once __DIR__ . '/../layouts/footer.php';
?>
