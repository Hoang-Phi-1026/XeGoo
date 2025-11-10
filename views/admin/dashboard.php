<?php
// Kiểm tra quyền admin trước khi load gì cả
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/admin-dashboard.css">
<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php
// Use stats from controller if available, otherwise set defaults
$totalUsers = isset($stats) ? $stats['users'] : 0;
$totalVehicles = isset($stats) ? $stats['vehicles'] : 0; 
$totalRoutes = isset($stats) ? $stats['routes'] : 0;
$totalTripsToday = isset($stats) ? $stats['trips'] : 0;
$totalPricesToday = isset($stats) ? $stats['prices'] : 0;
$totalSchedulesToday = isset($stats) ? $stats['schedules'] : 0;
?>

<div class="admin-layout">
    <!-- Sidebar Toggle Button for Mobile -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-content">
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <h2 class="nav-title">Tổng quan</h2>
                    <a href="<?= BASE_URL ?>/admin" class="nav-link active">
                        <div class="nav-icon"><i class="fas fa-tachometer-alt"></i></div>
                        <span class="nav-text">Dashboard</span>
                    </a>
                    <a href="<?= BASE_URL ?>/admin/statistics" class="nav-link">
                        <div class="nav-icon"><i class="fas fa-chart-line"></i></div>
                        <span class="nav-text">Thống kê</span>
                    </a>
                </div>

                <div class="nav-section">
                    <h3 class="nav-title">Quản lý</h3>
                    <a href="<?= BASE_URL ?>/vehicles" class="nav-link">
                        <div class="nav-icon"><i class="fas fa-bus"></i></div>
                        <span class="nav-text">Quản Lý Phương Tiện</span>
                        <span class="nav-badge"><?php echo $totalVehicles; ?></span>
                    </a>
                    <a href="<?= BASE_URL ?>/routes" class="nav-link">
                        <div class="nav-icon"><i class="fas fa-route"></i></div>
                        <span class="nav-text">Quản Lý Tuyến Đường</span>
                        <span class="nav-badge"><?php echo $totalRoutes; ?></span>
                    </a>
                     <a href="<?= BASE_URL ?>/prices" class="nav-link">
                        <div class="nav-icon"><i class="fas fa-tags"></i></div>
                        <span class="nav-text">Quản Lý Giá Vé</span>
                        <span class="nav-badge"><?php echo $totalPricesToday; ?></span>
                    </a>
                    <a href="<?= BASE_URL ?>/schedules" class="nav-link">
                        <div class="nav-icon"><i class="fas fa-calendar-alt"></i></div>
                        <span class="nav-text">Quản Lý Lịch Trình</span>
                        <span class="nav-badge"><?php echo $totalSchedulesToday; ?></span>
                        
                    </a>
                    <a href="<?= BASE_URL ?>/trips" class="nav-link">
                        <div class="nav-icon"><i class="fas fa-map-marked-alt"></i></div>
                        <span class="nav-text">Quản Lý Chuyến Xe</span>
                        <span class="nav-badge"><?php echo $totalTripsToday; ?></span>
                    </a>
                   <a href="<?= BASE_URL ?>/promotional-codes" class="nav-link">
                        <div class="nav-icon"><i class="fas fa-gift"></i></div>
                        <span class="nav-text">Quản Lý Khuyến Mãi</span>
                        <span class="nav-badge"><?php echo $stats['prices'] ?? 0; ?></span>
                    </a>
                    
                </div>

                <div class="nav-section">
                    <h3 class="nav-title">Người dùng</h3>
                    <a href="<?= BASE_URL ?>/users" class="nav-link">
                        <div class="nav-icon"><i class="fas fa-users"></i></div>
                        <span class="nav-text">Quản lý user</span>
                        <span class="nav-badge"><?php echo $totalUsers; ?></span>
                    </a>
                    <a href="<?= BASE_URL ?>/admin/reports" class="nav-link">
                        <div class="nav-icon"><i class="fas fa-file-alt"></i></div>
                        <span class="nav-text">Báo cáo</span>
                    </a>
                </div>

                <div class="nav-section">
                    <h3 class="nav-title">Hệ thống</h3>
                    <a href="<?= BASE_URL ?>/admin/settings" class="nav-link">
                        <div class="nav-icon"><i class="fas fa-cog"></i></div>
                        <span class="nav-text">Cài đặt</span>
                    </a>
                    <a href="<?= BASE_URL ?>/admin/logs" class="nav-link">
                        <div class="nav-icon"><i class="fas fa-list"></i></div>
                        <span class="nav-text">Nhật ký</span>
                    </a>
                </div>
            </nav>
        </div>
    </aside>    <!-- Main Content Area -->
    <main class="main-content">
        <!-- Page Header -->
        <header class="page-header">
                <!-- LOGO IMAGE HERE -->
            <div class="page-img">
                <img src="<?php echo BASE_URL; ?>/public/uploads/images/logo-dark.png" alt="XeGoo Logo" class="logo-img" style="height:40px;margin-right:8px;">
            </div>
            <div class="page-title">
                <h1>Dashboard</h1>
                <p>Chào mừng bạn đến với hệ thống quản lý XeGoo</p>
            </div>
        </header>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo $totalUsers; ?></div>
                    <div class="stat-label">Người dùng</div>
                </div>
            </div>

            <div class="stat-card success">
                <div class="stat-icon">
                    <i class="fas fa-bus"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo $totalVehicles; ?></div>
                    <div class="stat-label">Phương tiện</div>
                </div>
            </div>

            <div class="stat-card warning">
                <div class="stat-icon">
                    <i class="fas fa-route"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo $totalRoutes; ?></div>
                    <div class="stat-label">Tuyến đường</div>
                </div>
            </div>

            <div class="stat-card info">
                <div class="stat-icon">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo $totalTripsToday; ?></div>
                    <div class="stat-label">Chuyến hôm nay</div>
                </div>
            </div>
        </div>

        <!-- Main Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Quick Actions Card -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Thao tác nhanh</h3>
                    <button class="card-menu">
                        <i class="fas fa-ellipsis-h"></i>
                    </button>
                </div>
                <div class="card-content">
                    <div class="quick-actions">
                        <a href="<?= BASE_URL ?>/vehicles/create" class="quick-action">
                            <div class="action-icon primary">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div class="action-text">
                                <span class="action-title">Thêm phương tiện</span>
                                <span class="action-desc">Đăng ký xe mới</span>
                            </div>
                        </a>
                        <a href="<?= BASE_URL ?>/routes/create" class="quick-action">
                            <div class="action-icon success">
                                <i class="fas fa-route"></i>
                            </div>
                            <div class="action-text">
                                <span class="action-title">Tạo tuyến đường</span>
                                <span class="action-desc">Thiết lập tuyến mới</span>
                            </div>
                        </a>
                        <a href="<?= BASE_URL ?>/schedules/create" class="quick-action">
                            <div class="action-icon warning">
                                <i class="fas fa-calendar-plus"></i>
                            </div>
                            <div class="action-text">
                                <span class="action-title">Lập lịch trình</span>
                                <span class="action-desc">Tạo lịch chạy xe</span>
                            </div>
                        </a>
                        <a href="<?= BASE_URL ?>/users/create" class="quick-action">
                            <div class="action-icon info">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="action-text">
                                <span class="action-title">Thêm người dùng</span>
                                <span class="action-desc">Tạo tài khoản mới</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Hoạt động gần đây</h3>
                    <a href="#" class="card-link">Xem tất cả</a>
                </div>
                <div class="card-content">
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon primary">
                                <i class="fas fa-bus"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">Xe mới được thêm</div>
                                <div class="activity-desc">Xe khách 45 chỗ - BKS: 51B-12345</div>
                                <div class="activity-time">2 giờ trước</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon success">
                                <i class="fas fa-route"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">Tuyến đường cập nhật</div>
                                <div class="activity-desc">Tuyến HCM - Đà Lạt được cập nhật giá</div>
                                <div class="activity-time">4 giờ trước</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon warning">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">Người dùng mới đăng ký</div>
                                <div class="activity-desc">Nguyễn Văn A đã tạo tài khoản</div>
                                <div class="activity-time">6 giờ trước</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Admin Scripts -->
<script>
// Sidebar toggle functionality
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');

if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        
        // On mobile, show/hide sidebar
        if (window.innerWidth <= 768) {
            sidebar.classList.toggle('show');
        }
    });
}

// Theme toggle
const themeToggle = document.getElementById('themeToggle');
if (themeToggle) {
    themeToggle.addEventListener('click', function() {
        const html = document.documentElement;
        const currentTheme = html.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        html.setAttribute('data-theme', newTheme);
        localStorage.setItem('admin-theme', newTheme);
        
        // Update icon
        const icon = this.querySelector('i');
        icon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    });
    
    // Load saved theme
    const savedTheme = localStorage.getItem('admin-theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    const icon = themeToggle.querySelector('i');
    icon.className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
}

// User menu toggle
const userMenu = document.getElementById('userMenu');
if (userMenu) {
    userMenu.addEventListener('click', function(e) {
        e.stopPropagation();
        this.classList.toggle('active');
    });
    
    // Close user menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!userMenu.contains(e.target)) {
            userMenu.classList.remove('active');
        }
    });
}

// Close sidebar on mobile when clicking outside
document.addEventListener('click', function(e) {
    if (window.innerWidth <= 768) {
        if (sidebar && !sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
            sidebar.classList.remove('show');
        }
    }
});

// Responsive handling
window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        if (sidebar) {
            sidebar.classList.remove('show');
        }
    }
});

// Auto-close mobile menu after navigation
const navLinks = document.querySelectorAll('.nav-link');
navLinks.forEach(link => {
    link.addEventListener('click', function() {
        if (window.innerWidth <= 768 && sidebar) {
            sidebar.classList.remove('show');
        }
    });
});
</script>
</body>
</html>
