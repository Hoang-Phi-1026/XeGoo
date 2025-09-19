<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/admin-dashboard.css">

<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <div class="logo-icon">XG</div>
                <span class="logo-text">Admin</span>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <a href="<?= BASE_URL ?>/admin" class="nav-item active">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="<?= BASE_URL ?>/vehicles" class="nav-item">
                <i class="fas fa-bus"></i>
                <span>Phương tiện</span>
            </a>
            <a href="<?= BASE_URL ?>/routes" class="nav-item">
                <i class="fas fa-route"></i>
                <span>Tuyến đường</span>
            </a>
            <a href="<?= BASE_URL ?>/prices" class="nav-item">
                <i class="fas fa-tags"></i>
                <span>Giá vé</span>
            </a>
            <a href="<?= BASE_URL ?>/schedules" class="nav-item">
                <i class="fas fa-calendar-alt"></i>
                <span>Lịch trình</span>
            </a>
            <a href="<?= BASE_URL ?>/trips" class="nav-item">
                <i class="fas fa-map-marked-alt"></i>
                <span>Chuyến xe</span>
            </a>
            <a href="<?= BASE_URL ?>/users" class="nav-item">
                <i class="fas fa-users"></i>
                <span>Người dùng</span>
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <a href="<?= BASE_URL ?>/" class="nav-item">
                <i class="fas fa-home"></i>
                <span>Trang chủ</span>
            </a>
            <a href="<?= BASE_URL ?>/logout" class="nav-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Đăng xuất</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
        <div class="admin-header">
            <h1 class="admin-title">
                <i class="fas fa-tachometer-alt"></i>
                Dashboard Quản Trị
            </h1>
            <p class="admin-subtitle">Chào mừng bạn đến với hệ thống quản lý XeGoo</p>
        </div>

        <!-- Module Cards Grid -->
        <div class="module-grid">
            <!-- Quản lý Phương tiện -->
            <div class="module-card" onclick="location.href='<?= BASE_URL ?>/vehicles'">
                <div class="card-icon vehicles">
                    <i class="fas fa-bus"></i>
                </div>
                <div class="card-content">
                    <h3 class="card-title">Quản lý Phương tiện</h3>
                    <p class="card-description">Quản lý xe buýt, thông tin xe và trạng thái</p>
                    <div class="card-stats">
                        <span class="stat-number"><?= $stats['vehicles'] ?></span>
                        <span class="stat-label">Phương tiện</span>
                    </div>
                </div>
                <div class="card-arrow">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>

            <!-- Quản lý Tuyến đường -->
            <div class="module-card" onclick="location.href='<?= BASE_URL ?>/routes'">
                <div class="card-icon routes">
                    <i class="fas fa-route"></i>
                </div>
                <div class="card-content">
                    <h3 class="card-title">Quản lý Tuyến đường</h3>
                    <p class="card-description">Thiết lập và quản lý các tuyến đường</p>
                    <div class="card-stats">
                        <span class="stat-number"><?= $stats['routes'] ?></span>
                        <span class="stat-label">Tuyến đường</span>
                    </div>
                </div>
                <div class="card-arrow">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>

            <!-- Quản lý Giá vé -->
            <div class="module-card" onclick="location.href='<?= BASE_URL ?>/prices'">
                <div class="card-icon prices">
                    <i class="fas fa-tags"></i>
                </div>
                <div class="card-content">
                    <h3 class="card-title">Quản lý Giá vé</h3>
                    <p class="card-description">Thiết lập giá vé cho các tuyến đường</p>
                    <div class="card-stats">
                        <span class="stat-number"><?= $stats['prices'] ?></span>
                        <span class="stat-label">Bảng giá</span>
                    </div>
                </div>
                <div class="card-arrow">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>

            <!-- Quản lý Lịch trình -->
            <div class="module-card" onclick="location.href='<?= BASE_URL ?>/schedules'">
                <div class="card-icon schedules">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="card-content">
                    <h3 class="card-title">Quản lý Lịch trình</h3>
                    <p class="card-description">Lập lịch trình cho các chuyến xe</p>
                    <div class="card-stats">
                        <span class="stat-number"><?= $stats['schedules'] ?></span>
                        <span class="stat-label">Lịch trình</span>
                    </div>
                </div>
                <div class="card-arrow">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>

            <!-- Quản lý Chuyến xe -->
            <div class="module-card" onclick="location.href='<?= BASE_URL ?>/trips'">
                <div class="card-icon trips">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <div class="card-content">
                    <h3 class="card-title">Quản lý Chuyến xe</h3>
                    <p class="card-description">Theo dõi và quản lý các chuyến xe</p>
                    <div class="card-stats">
                        <span class="stat-number"><?= $stats['trips'] ?></span>
                        <span class="stat-label">Chuyến xe</span>
                    </div>
                </div>
                <div class="card-arrow">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>

            <!-- Quản lý Người dùng -->
            <div class="module-card" onclick="location.href='<?= BASE_URL ?>/users'">
                <div class="card-icon users">
                    <i class="fas fa-users"></i>
                </div>
                <div class="card-content">
                    <h3 class="card-title">Quản lý Người dùng</h3>
                    <p class="card-description">Quản lý tài khoản và phân quyền</p>
                    <div class="card-stats">
                        <span class="stat-number"><?= $stats['users'] ?></span>
                        <span class="stat-label">Người dùng</span>
                    </div>
                </div>
                <div class="card-arrow">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h2 class="section-title">Thao tác nhanh</h2>
            <div class="action-grid">
                <a href="<?= BASE_URL ?>/vehicles/create" class="action-btn">
                    <i class="fas fa-plus"></i>
                    <span>Thêm phương tiện</span>
                </a>
                <a href="<?= BASE_URL ?>/routes/create" class="action-btn">
                    <i class="fas fa-plus"></i>
                    <span>Thêm tuyến đường</span>
                </a>
                <a href="<?= BASE_URL ?>/schedules/create" class="action-btn">
                    <i class="fas fa-plus"></i>
                    <span>Tạo lịch trình</span>
                </a>
                <a href="<?= BASE_URL ?>/users/create" class="action-btn">
                    <i class="fas fa-plus"></i>
                    <span>Thêm người dùng</span>
                </a>
            </div>
        </div>
    </main>
</div>

<script>
// Enhanced admin dashboard interactions
document.addEventListener('DOMContentLoaded', function() {
    // Module card enhanced interactions
    const moduleCards = document.querySelectorAll('.module-card');
    moduleCards.forEach((card, index) => {
        // Add staggered animation delay
        card.style.animationDelay = `${index * 0.1}s`;
        
        // Enhanced hover effects
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
            this.style.zIndex = '10';
            
            // Add ripple effect
            const ripple = document.createElement('div');
            ripple.style.cssText = `
                position: absolute;
                border-radius: 50%;
                background: rgba(16, 255, 239, 0.3);
                transform: scale(0);
                animation: ripple 0.6s linear;
                pointer-events: none;
                top: 50%;
                left: 50%;
                width: 20px;
                height: 20px;
                margin-left: -10px;
                margin-top: -10px;
            `;
            this.appendChild(ripple);
            
            setTimeout(() => {
                if (ripple.parentNode) {
                    ripple.parentNode.removeChild(ripple);
                }
            }, 600);
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
            this.style.zIndex = '1';
        });

        // Click animation
        card.addEventListener('click', function(e) {
            // Add click ripple effect
            const rect = this.getBoundingClientRect();
            const ripple = document.createElement('div');
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.cssText = `
                position: absolute;
                border-radius: 50%;
                background: rgba(16, 255, 239, 0.5);
                transform: scale(0);
                animation: ripple 0.6s linear;
                pointer-events: none;
                left: ${x}px;
                top: ${y}px;
                width: ${size}px;
                height: ${size}px;
            `;
            this.appendChild(ripple);
            
            setTimeout(() => {
                if (ripple.parentNode) {
                    ripple.parentNode.removeChild(ripple);
                }
            }, 600);
        });
    });

    // Action button interactions
    const actionBtns = document.querySelectorAll('.action-btn');
    actionBtns.forEach((btn, index) => {
        btn.style.animationDelay = `${0.7 + index * 0.1}s`;
        
        btn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px) scale(1.02)';
        });
        
        btn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });

    // Sidebar navigation active state with smooth transitions
    const currentPath = window.location.pathname;
    const navItems = document.querySelectorAll('.sidebar-nav .nav-item');
    navItems.forEach(item => {
        if (item.getAttribute('href') === currentPath) {
            item.classList.add('active');
        }
        
        // Add click animation
        item.addEventListener('click', function() {
            // Remove active from all items
            navItems.forEach(nav => nav.classList.remove('active'));
            // Add active to clicked item
            this.classList.add('active');
        });
    });

    // Add loading state simulation for stats
    const statNumbers = document.querySelectorAll('.stat-number');
    statNumbers.forEach(stat => {
        const finalValue = parseInt(stat.textContent);
        let currentValue = 0;
        const increment = finalValue / 30; // 30 frames for smooth animation
        
        const counter = setInterval(() => {
            currentValue += increment;
            if (currentValue >= finalValue) {
                stat.textContent = finalValue;
                clearInterval(counter);
            } else {
                stat.textContent = Math.floor(currentValue);
            }
        }, 50);
    });

    // Add parallax effect to background
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const parallax = document.querySelector('.admin-layout::before');
        if (parallax) {
            parallax.style.transform = `translateY(${scrolled * 0.5}px)`;
        }
    });

    // Add theme-aware animations
    const theme = document.documentElement.getAttribute('data-theme');
    if (theme === 'dark') {
        document.body.classList.add('dark-theme');
    }
});

// Add CSS for ripple animation
const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    .module-card {
        position: relative;
        overflow: hidden;
    }
    
    .dark-theme .module-card:hover {
        box-shadow: 
            0 20px 40px rgba(0, 0, 0, 0.4),
            0 8px 32px rgba(16, 255, 239, 0.3),
            inset 0 1px 0 rgba(16, 255, 239, 0.2);
    }
`;
document.head.appendChild(style);
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
