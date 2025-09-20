<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/admin-dashboard.css">

<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        
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
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
        <div class="admin-header">
            <h1 class="admin-title">
                Hệ Thống Quản Lý XeGoo
            </h1>
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

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
