<?php
// Include header
require_once __DIR__ . '/../layouts/header.php';
$isLoggedIn = isset($_SESSION['user_id']);
?>

<!-- Add hidden element to indicate if user is logged in (for JS detection) -->
<?php if ($isLoggedIn): ?>
    <div data-user-id="<?php echo htmlspecialchars($_SESSION['user_id']); ?>" style="display: none;"></div>
<?php endif; ?>

<div class="home-container">
    <div class="min-h-screen bg-gray-50">
        <!-- Updated search form to use unified CSS classes and structure -->
        <div class="bg-white shadow-sm border-b">
            <div class="container mx-auto px-4 py-6">
                <div class="search-form-container">
                    <form id="searchForm" action="<?php echo BASE_URL; ?>/search" method="GET" class="search-form">
                        <!-- Trip Type Selection -->
                        <div class="trip-type-selector">
                            <label class="trip-type-option">
                                <input type="radio" name="trip_type" value="one_way" checked>
                                <span>Một chiều</span>
                            </label>
                            <label class="trip-type-option">
                                <input type="radio" name="trip_type" value="round_trip">
                                <span>Khứ hồi</span>
                            </label>
                            <div class="trip-type-guide">
                                <a href="<?php echo BASE_URL; ?>/booking-guide" class="text-orange-500 text-sm hover:underline">Hướng dẫn mua vé</a>
                            </div>
                        </div>

                        <div class="form-grid" style="display: flex; gap: 15px; align-items: end; flex-wrap: wrap;">
                            <div class="form-group" style="flex: 1; min-width: 200px;">
                                <label class="form-label">Điểm đi</label>
                                <select name="from" class="form-select" required>
                                    <option value="">Chọn điểm đi</option>
                                </select>
                            </div>
                            
                            <div class="form-group" style="flex: 1; min-width: 200px;">
                                <label class="form-label">Điểm đến</label>
                                <select name="to" class="form-select" required>
                                    <option value="">Chọn điểm đến</option>
                                </select>
                            </div>
                            
                            <div class="form-group" style="flex: 0 0 150px;">
                                <label class="form-label">Ngày đi</label>
                                <input type="date" name="departure_date" class="form-input" 
                                       value="<?php echo date('Y-m-d'); ?>" 
                                       min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            
                            <div class="form-group return-date-group" style="flex: 0 0 150px; display: none;">
                                <label class="form-label">Ngày về</label>
                                <input type="date" name="return_date" class="form-input" 
                                       min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                            </div>
                        </div>

                        <!-- Search button on separate row -->
                        <div class="text-center" style="margin-top: 20px;">
                            <button type="submit" class="search-button">
                                <i class="fas fa-search"></i>
                                Tìm chuyến xe
                            </button>
                        </div>

                    <input type="hidden" name="passengers" value="1">
                    <input type="hidden" name="is_round_trip" value="0">
                </form>
            </div>
        </div>
    </div>

    <!-- Hero Actions Section -->
    <section class="hero-actions-section">
        <div class="container">
            <div class="hero-actions">
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo BASE_URL; ?>/register" class="btn btn-secondary btn-lg">
                        <i class="fas fa-user-plus"></i>
                        Đăng ký ngay
                    </a>
                    <a href="<?php echo BASE_URL; ?>/login" class="btn btn-outline btn-lg">
                        <i class="fas fa-sign-in-alt"></i>
                        Đăng nhập
                    </a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/my-tickets" class="btn btn-secondary btn-lg">
                        <i class="fas fa-list"></i>
                        Vé của tôi
                    </a>
                    <a href="<?php echo BASE_URL; ?>/profile" class="btn btn-outline btn-lg">
                        <i class="fas fa-user"></i>
                        Tài khoản
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<!-- Updated promotional banner section to use system colors -->
<!-- Promotional Banner Section -->
<section class="promo-banner-section">
    <div class="container">
        <div class="promo-banner-grid">
            <div class="promo-banner-large">
                <img src="<?php echo BASE_URL; ?>/public/uploads/images/banner-main.png" alt="Khám phá Việt Nam cùng XeGoo" />
                <div class="promo-banner-overlay">
                    <h3 class="promo-banner-title">Khám phá Việt Nam cùng XeGoo</h3>
                    <p class="promo-banner-text">Đặt vé ngay - Nhận ưu đãi hấp dẫn</p>
                    <a href="<?php echo BASE_URL; ?>/search" class="promo-banner-btn">
                        <i class="fas fa-ticket-alt"></i>
                        Đặt vé ngay
                    </a>
                </div>
            </div>
            
            <div class="promo-banner-small">
                <img src="<?php echo BASE_URL; ?>/public/uploads/images/banner-comfort.png" alt="Xe sang - Tiện nghi" />
                <div class="promo-banner-overlay">
                    <h4 class="promo-banner-subtitle">Xe sang - Tiện nghi</h4>
                    <p class="promo-banner-small-text">Ghế nằm cao cấp</p>
                </div>
            </div>
            
            <div class="promo-banner-small">
                <img src="<?php echo BASE_URL; ?>/public/uploads/images/banner-happy.png" alt="An toàn - Tin cậy" />
                <div class="promo-banner-overlay">
                    <h4 class="promo-banner-subtitle">An toàn - Tin cậy</h4>
                    <p class="promo-banner-small-text">Hàng nghìn khách hài lòng</p>
                </div>
            </div>

            <div class="promo-banner-small">
                <img src="<?php echo BASE_URL; ?>/public/uploads/images/banner-happy-2.png" alt="An toàn - Tin cậy" />
                <div class="promo-banner-overlay">
                    <h4 class="promo-banner-subtitle">An toàn - Tin cậy</h4>
                    <p class="promo-banner-small-text">Hàng nghìn khách hài lòng</p>
                </div>
            </div>

            <div class="promo-banner-small">
                <img src="<?php echo BASE_URL; ?>/public/uploads/images/banner-happy-3.png" alt="An toàn - Tin cậy" />
                <div class="promo-banner-overlay">
                    <h4 class="promo-banner-subtitle">An toàn - Tin cậy</h4>
                    <p class="promo-banner-small-text">Hàng nghìn khách hài lòng</p>
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
                    <div class="stat-number">50K+</div>
                    <div class="stat-label">Khách hàng tin tưởng</div>
                </div>
                <div class="stat-item text-center">
                    <div class="stat-number">200+</div>
                    <div class="stat-label">Tuyến đường</div>
                </div>
                <div class="stat-item text-center">
                    <div class="stat-number">100+</div>
                    <div class="stat-label">Nhà xe đối tác</div>
                </div>
                <div class="stat-item text-center">
                    <div class="stat-number">99%</div>
                    <div class="stat-label">Khách hàng hài lòng</div>
                </div>
            </div>
        </div>
    </section>

<!-- Updated promotional cards section to use system colors -->
<!-- Promotional Cards Section -->
<section class="promo-cards-section">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title">Ưu đãi đặc biệt</h2>
            <p class="section-subtitle">
                Những chương trình khuyến mãi hấp dẫn dành cho bạn
            </p>
        </div>
        
        <div class="promo-cards-grid">
            <div class="promo-card">
                <div class="promo-card-image">
                    <img src="<?php echo BASE_URL; ?>/public/uploads/images/promo-discount.jpg" alt="Giảm giá cho khách hàng mới" />
                    <div class="promo-badge">Giảm 20%</div>
                </div>
                <div class="promo-card-content">
                    <h3 class="promo-card-title">Giảm giá cho khách hàng mới</h3>
                    <p class="promo-card-description">
                        Đăng ký tài khoản mới và nhận ngay mã giảm giá 30% cho chuyến đi đầu tiên của bạn.
                    </p>
                    <a href="<?php echo BASE_URL; ?>/register" class="promo-card-link">
                        Đăng ký ngay <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="promo-card">
                <div class="promo-card-image">
                    <img src="<?php echo BASE_URL; ?>/public/uploads/images/promo-loyalty.jpg" alt="Chương trình khách hàng thân thiết" />
                    <div class="promo-badge promo-badge-secondary">Tích điểm</div>
                </div>
                <div class="promo-card-content">
                    <h3 class="promo-card-title">Chương trình khách hàng thân thiết</h3>
                    <p class="promo-card-description">
                        Tích điểm mỗi chuyến đi và đổi quà tặng hấp dẫn. Càng đi nhiều, càng nhận nhiều ưu đãi.
                    </p>
                    <a href="<?php echo BASE_URL; ?>/loyalty" class="promo-card-link">
                        Tìm hiểu thêm <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="promo-card">
                <div class="promo-card-image">
                    <img src="<?php echo BASE_URL; ?>/public/uploads/images/promo-weekend.jpg" alt="Ưu đãi cuối tuần" />
                    <div class="promo-badge promo-badge-success">Cuối tuần</div>
                </div>
                <div class="promo-card-content">
                    <h3 class="promo-card-title">Ưu đãi cuối tuần</h3>
                    <p class="promo-card-description">
                        Giảm giá đặc biệt cho các chuyến đi vào thứ 7 và chủ nhật. Đặt vé sớm để nhận ưu đãi tốt nhất.
                    </p>
                    <a href="<?php echo BASE_URL; ?>/search" class="promo-card-link">
                        Xem chuyến xe <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="testimonials-section">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title">Khách hàng nói gì về chúng tôi</h2>
            <p class="section-subtitle">
                Những đánh giá chân thực từ khách hàng
            </p>
        </div>
        
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p class="testimonial-text">
                    "Dịch vụ tuyệt vời! Xe sạch sẽ, tài xế lịch sự và đúng giờ. Tôi sẽ tiếp tục sử dụng XeGoo cho những chuyến đi sau."
                </p>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="author-info">
                        <div class="author-name">Nguyễn Văn A</div>
                        <div class="author-location">Hà Nội</div>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p class="testimonial-text">
                    "Đặt vé rất dễ dàng, thanh toán nhanh chóng. Giá cả hợp lý và có nhiều ưu đãi. Rất hài lòng với trải nghiệm này!"
                </p>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="author-info">
                        <div class="author-name">Trần Thị B</div>
                        <div class="author-location">TP. Hồ Chí Minh</div>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <p class="testimonial-text">
                    "Chương trình tích điểm rất hấp dẫn. Tôi đã đổi được nhiều quà tặng từ những chuyến đi của mình. Cảm ơn XeGoo!"
                </p>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="author-info">
                        <div class="author-name">Lê Văn C</div>
                        <div class="author-location">Đà Nẵng</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Added button to view community forum -->
        <div class="testimonials-cta">
            <p class="testimonials-cta-text">Bạn có câu hỏi hoặc muốn chia sẻ trải nghiệm của mình?</p>
            <a href="<?php echo BASE_URL; ?>/post" class="testimonials-cta-btn">
                <i class="fas fa-comments"></i>
                Tham gia diễn đàn cộng đồng
            </a>
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
                    <a href="<?php echo BASE_URL; ?>/register" class="btn btn-lg">
                        <i class="fas fa-user-plus"></i>
                        Đăng ký miễn phí
                    </a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/search" class="btn btn-lg">
                        <i class="fas fa-ticket-alt"></i>
                        Đặt vé ngay
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

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
    
    // Observe all feature cards, stat items, and testimonial cards
document.querySelectorAll('.feature-card, .stat-item, .promo-card, .promo-banner-large, .promo-banner-small, .testimonial-card').forEach(el => {
    observer.observe(el);
});
    
    // Load cities for dropdowns
    loadCities();
    
    // Trip type toggle functionality
    const tripTypeInputs = document.querySelectorAll('input[name="trip_type"]');
    const returnDateGroup = document.querySelector('.return-date-group');
    const returnDateInput = document.querySelector('input[name="return_date"]');
    const isRoundTripInput = document.querySelector('input[name="is_round_trip"]');
    
    tripTypeInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.value === 'round_trip') {
                returnDateGroup.style.display = 'block';
                returnDateInput.required = true;
                isRoundTripInput.value = '1';
            } else {
                returnDateGroup.style.display = 'none';
                returnDateInput.required = false;
                returnDateInput.value = '';
                isRoundTripInput.value = '0';
            }
        });
    });

    // Date validation
    const departureDateInput = document.querySelector('input[name="departure_date"]');
    
    departureDateInput.addEventListener('change', function() {
        const departureDate = new Date(this.value);
        const nextDay = new Date(departureDate);
        nextDay.setDate(nextDay.getDate() + 1);
        
        returnDateInput.min = nextDay.toISOString().split('T')[0];
        
        // Clear return date if it's before departure date
        if (returnDateInput.value && new Date(returnDateInput.value) <= departureDate) {
            returnDateInput.value = '';
        }
    });
});
</script>

<script>
// Load cities from API
async function loadCities() {
    try {
        const response = await fetch('<?php echo BASE_URL; ?>/search/cities');
        const cities = await response.json();
        
        const fromSelect = document.querySelector('select[name="from"]');
        const toSelect = document.querySelector('select[name="to"]');
        fromSelect.innerHTML = '<option value="">Chọn điểm đi</option>';
        toSelect.innerHTML = '<option value="">Chọn điểm đến</option>';
        cities.forEach(city => {
            const option1 = new Option(city.name, city.id);
            const option2 = new Option(city.name, city.id);
            fromSelect.add(option1);
            toSelect.add(option2);
        });
    } catch (error) {
        console.error('Error loading cities:', error);
        // Fallback to static cities
        const staticCities = [
            {id: 'Hà Nội', name: 'Hà Nội'},
            {id: 'TP. Hồ Chí Minh', name: 'TP. Hồ Chí Minh'},
            {id: 'Đà Nẵng', name: 'Đà Nẵng'},
            {id: 'Hải Phòng', name: 'Hải Phòng'},
            {id: 'Cần Thơ', name: 'Cần Thơ'},
            {id: 'Nha Trang', name: 'Nha Trang'},
            {id: 'Đà Lạt', name: 'Đà Lạt'},
            {id: 'Vũng Tàu', name: 'Vũng Tàu'}
        ];
        
        const fromSelect = document.querySelector('select[name="from"]');
        const toSelect = document.querySelector('select[name="to"]');
        
        staticCities.forEach(city => {
            const option1 = new Option(city.name, city.id);
            const option2 = new Option(city.name, city.id);
            fromSelect.add(option1);
            toSelect.add(option2);
        });
    }
}
</script>

<?php
// Include footer
require_once __DIR__ . '/../layouts/footer.php';
if ($isLoggedIn):
?>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/reminder-popup.css">
    <script src="<?php echo BASE_URL; ?>/public/js/reminder-popup.js"></script>
    <!-- added driver reminder popup for drivers on home page -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/driver-reminder-popup.css">
    <script src="<?php echo BASE_URL; ?>/public/js/driver-reminder-popup.js"></script>
<?php endif; ?>
