<?php
// Include header
require_once __DIR__ . '/../layouts/header.php';
?>

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
                                <a href="#" class="text-orange-500 text-sm hover:underline">Hướng dẫn mua vé</a>
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
    const returnDateInput = document.querySelector('input[name="return_date"]');
    
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
?>
