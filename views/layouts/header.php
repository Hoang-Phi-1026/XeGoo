<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XeGoo - Đặt vé xe liên tỉnh</title>
    <!-- Updated CSS includes with new design system -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/main.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/header.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/footer.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/notifications.css">
    <!-- Added unified search CSS for consistent styling across all pages -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/search.css">
    <!-- Added theme toggle CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/theme-toggle.css">
    <!-- Added vehicle management CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/vehicles.css">
    <!-- Added schedule and trip management CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/schedules.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/trips.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="<?php echo BASE_URL; ?>/public/favicon.ico" type="image/x-icon">
    
    <?php
    // Add page-specific CSS
    $current_url = $_SERVER['REQUEST_URI'];
    if (strpos($current_url, '/login') !== false) {
        echo '<link rel="stylesheet" href="' . BASE_URL . '/public/css/login.css">';
    } elseif (strpos($current_url, '/register') !== false) {
        echo '<link rel="stylesheet" href="' . BASE_URL . '/public/css/register.css">';
    } elseif (strpos($current_url, '/profile') !== false) {
        echo '<link rel="stylesheet" href="' . BASE_URL . '/public/css/profile.css">';
    } elseif (strpos($current_url, '/admin') !== false) {
        echo '<link rel="stylesheet" href="' . BASE_URL . '/public/css/admin-dashboard.css">';
    } elseif (strpos($current_url, '/search') !== false) {
        // echo '<link rel="stylesheet" href="' . BASE_URL . '/public/css/search.css">';
    } else {
        echo '<link rel="stylesheet" href="' . BASE_URL . '/public/css/home.css">';
    }
    ?>
    
    <!-- Added BASE_URL JavaScript variable for use in unified search -->
    <script>
        window.BASE_URL = '<?php echo BASE_URL; ?>';
    </script>
</head>
<body>
    <!-- Completely restructured header to match CSS classes -->
    <header class="header">
        <div class="header-container">
            <a href="<?php echo BASE_URL; ?>/" class="logo">
                <div class="logo-icon">XG</div>
                <span>XeGoo</span>
            </a>
            
            <nav class="nav-menu">
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a href="<?php echo BASE_URL; ?>/" class="nav-link">Trang chủ</a></li>
                    <li class="nav-item"><a href="<?php echo BASE_URL; ?>/about" class="nav-link">Giới thiệu</a></li>
                <?php else: ?>
                    <?php
                    $vai_tro = $_SESSION['user_role'] ?? 4;
                    switch ($vai_tro) {
                        case 1: // Quản Trị Viên
                            ?>

                            <!-- Quản lý chung -->
                            
                            <li class="nav-item"><a href="<?php echo BASE_URL; ?>/admin" class="nav-link">Quản Lý Chung</a></li>
                            <?php
                            break;
                        case 2: // Nhân Viên Hỗ Trợ
                            ?>
                            <li class="nav-item"><a href="<?php echo BASE_URL; ?>/support" class="nav-link">Hỗ trợ</a></li>
                            <li class="nav-item"><a href="<?php echo BASE_URL; ?>/dashboard" class="nav-link">Dashboard</a></li>
                            <?php
                            break;
                        case 3: // Tài Xế
                            ?>
                            <li class="nav-item"><a href="<?php echo BASE_URL; ?>/driver" class="nav-link">Tài xế</a></li>
                            <li class="nav-item"><a href="<?php echo BASE_URL; ?>/dashboard" class="nav-link">Dashboard</a></li>
                            <?php
                            break;
                        case 4: // Khách Hàng
                            ?>
                            <li class="nav-item"><a href="<?php echo BASE_URL; ?>/" class="nav-link">Trang chủ</a></li>
                            <li class="nav-item"><a href="<?php echo BASE_URL; ?>/search" class="nav-link">Đặt vé</a></li>
                            <li class="nav-item"><a href="<?php echo BASE_URL; ?>/my-tickets" class="nav-link">Vé của tôi</a></li>
                            <li class="nav-item"><a href="<?php echo BASE_URL; ?>/profile" class="nav-link">Hồ sơ</a></li>
                            <?php
                            break;
                    }
                    ?>
                <?php endif; ?>
            </nav>

            <div class="user-menu">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="user-info">
                        <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?></div>
                        <span>Xin chào, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Người dùng'); ?></span>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/logout" class="btn-secondary">Đăng xuất</a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/login" class="btn-primary">Đăng nhập</a>
                <?php endif; ?>
                
                <button class="theme-toggle" title="Chuyển đổi chế độ tối/sáng">
                    <i class="fas fa-moon"></i>
                </button>
                
                <button class="mobile-menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
        
        <!-- Mobile menu -->
        <div class="mobile-menu">
            <ul class="mobile-nav-menu">
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <li><a href="<?php echo BASE_URL; ?>/" class="mobile-nav-link">Trang chủ</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/about" class="mobile-nav-link">Giới thiệu</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/login" class="mobile-nav-link">Đăng nhập</a></li>
                <?php else: ?>
                    <li><a href="<?php echo BASE_URL; ?>/" class="mobile-nav-link">Trang chủ</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/logout" class="mobile-nav-link">Đăng xuất</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </header>

    <main>
        <!-- Replaced PHP alert divs with JavaScript notification system -->
        <script src="<?php echo BASE_URL; ?>/public/js/notifications.js"></script>
        <!-- Added theme toggle JavaScript -->
        <script src="<?php echo BASE_URL; ?>/public/js/theme-toggle.js"></script>
        <!-- Added unified search JavaScript for consistent functionality -->
        <script src="<?php echo BASE_URL; ?>/public/js/unified-search.js"></script>
        <script>
        // Display session messages using JavaScript notifications
        <?php
        if (isset($_SESSION['success'])) {
            echo 'showSuccess("' . addslashes($_SESSION['success']) . '");';
            unset($_SESSION['success']);
        }
        if (isset($_SESSION['error'])) {
            echo 'showError("' . addslashes($_SESSION['error']) . '");';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['warning'])) {
            echo 'showWarning("' . addslashes($_SESSION['warning']) . '");';
            unset($_SESSION['warning']);
        }
        if (isset($_SESSION['info'])) {
            echo 'showInfo("' . addslashes($_SESSION['info']) . '");';
            unset($_SESSION['info']);
        }
        ?>
        </script>
    </main>
</body>
</html>
