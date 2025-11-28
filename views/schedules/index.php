<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <!-- redesigned header with modern typography and layout -->
    <div class="page-header">
        <div class="page-title">
            <h1>Quản lý Lịch Trình</h1>
            <p class="page-subtitle">Quản lý và theo dõi lịch trình vận hành</p>
        </div>
        <div class="page-actions">
            <a href="<?php echo BASE_URL; ?>/schedules/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm lịch trình
            </a>
            <a href="<?php echo BASE_URL; ?>/schedules/generate-trips" class="btn btn-success">
                <i class="fas fa-cogs"></i> Sinh chuyến xe
            </a>
        </div>
    </div>

    <!-- enhanced statistics grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['total']; ?></h3>
                <p>Tổng lịch trình</p>
            </div>
        </div>
        <div class="stat-card active">
            <div class="stat-icon">
                <i class="fas fa-play-circle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['active']; ?></h3>
                <p>Đang hoạt động</p>
            </div>
        </div>
        <div class="stat-card paused">
            <div class="stat-icon">
                <i class="fas fa-pause-circle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['paused']; ?></h3>
                <p>Tạm dừng</p>
            </div>
        </div>
        <div class="stat-card stopped">
            <div class="stat-icon">
                <i class="fas fa-stop-circle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['stopped']; ?></h3>
                <p>Ngừng hoạt động</p>
            </div>
        </div>
    </div>

    <!-- modern search and filter section -->
    <div class="card search-filters-card">
        <div class="card-header">
            <h3 class="card-title">Tìm kiếm và lọc</h3>
        </div>
        <div class="card-body">
            <form method="GET" class="filter-form" id="searchForm">
                <div class="filter-row">
                    <div class="form-group ">
                        <label for="search">Nhập từ khóa:</label>
                        <input type="text" name="search" id="search" class="form-control"
                               placeholder="Tìm kiếm theo tên, tuyến..." 
                               value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    </div>
                    <!-- Add month filter dropdown -->
                    <div class="form-group">
                        <label for="month">Chọn tháng:</label>
                        <select name="month" id="month" class="form-control">
                            <option value="">-- Chọn tháng --</option>
                            <?php foreach ($months as $monthKey => $monthLabel): ?>
                                <option value="<?php echo $monthKey; ?>" 
                                        <?php echo (isset($_GET['month']) && $_GET['month'] == $monthKey) ? 'selected' : ((!isset($_GET['month']) && $monthKey == date('Y-m')) ? 'selected' : ''); ?>>
                                    Tháng <?php echo $monthLabel; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="route">Chọn tuyến đường:</label>
                        <select name="route" id="route" class="form-control">
                            <option value="">Tất cả tuyến</option>
                            <?php foreach ($routes as $route): ?>
                                <option value="<?php echo $route['maTuyenDuong']; ?>" 
                                        <?php echo (isset($_GET['route']) && $_GET['route'] == $route['maTuyenDuong']) ? 'selected' : ''; ?>>
                                    <?php echo $route['kyHieuTuyen'] . ' - ' . $route['diemDi'] . ' → ' . $route['diemDen']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Tìm kiếm
                        </button>
                        <a href="<?php echo BASE_URL; ?>/schedules" class="btn btn-outline">
                            <i class="fas fa-redo"></i> Đặt lại
                        </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- search results summary -->
    <?php if (!empty($_GET['search']) || !empty($_GET['route']) || !empty($_GET['month'])): ?>
        <div class="search-results-summary">
            <div class="results-info">
                <i class="fas fa-info-circle"></i>
                <span>Tìm thấy <strong><?php echo count($schedules); ?></strong> lịch trình</span>
            </div>
            <div class="active-filters">
                <?php if (!empty($_GET['month'])): ?>
                    <?php 
                    $monthParts = explode('-', $_GET['month']);
                    if (count($monthParts) === 2) {
                        $monthLabel = 'Tháng ' . intval($monthParts[1]) . '/' . $monthParts[0];
                    } else {
                        $monthLabel = $_GET['month'];
                    }
                    ?>
                    <span class="filter-tag">
                        <i class="fas fa-calendar"></i> <?php echo $monthLabel; ?>
                        <a href="<?php echo BASE_URL; ?>/schedules?<?php echo http_build_query(array_diff_key($_GET, ['month' => ''])); ?>" class="remove-filter">×</a>
                    </span>
                <?php endif; ?>
                <?php if (!empty($_GET['search'])): ?>
                    <span class="filter-tag">
                        <i class="fas fa-search"></i> "<?php echo htmlspecialchars($_GET['search']); ?>"
                        <a href="<?php echo BASE_URL; ?>/schedules?<?php echo http_build_query(array_diff_key($_GET, ['search' => ''])); ?>" class="remove-filter">×</a>
                    </span>
                <?php endif; ?>
                <?php if (!empty($_GET['route'])): ?>
                    <?php 
                    $selectedRoute = array_filter($routes, function($r) { return $r['maTuyenDuong'] == $_GET['route']; });
                    $selectedRoute = reset($selectedRoute);
                    ?>
                    <span class="filter-tag">
                        <i class="fas fa-route"></i> <?php echo $selectedRoute['kyHieuTuyen']; ?>
                        <a href="<?php echo BASE_URL; ?>/schedules?<?php echo http_build_query(array_diff_key($_GET, ['route' => ''])); ?>" class="remove-filter">×</a>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- modern card-based list instead of table -->
    <div class="card schedules-list-card">
        <div class="card-header">
            <h3 class="card-title">Danh sách lịch trình</h3>
        </div>
        <div class="card-body">
            <?php if (empty($schedules)): ?>
                <div class="no-data-container">
                    <i class="fas fa-search"></i>
                    <p>Không tìm thấy lịch trình nào</p>
                    <a href="<?php echo BASE_URL; ?>/schedules" class="btn btn-outline btn-sm">
                        <i class="fas fa-list"></i> Xem tất cả
                    </a>
                </div>
            <?php else: ?>
                <!-- card grid layout for better visual hierarchy -->
                <div class="schedules-grid">
                    <?php foreach ($schedules as $schedule): ?>
                        <div class="schedule-card">
                            <div class="card-header-row">
                                <div class="route-badge">
                                    <strong><?php echo htmlspecialchars($schedule['kyHieuTuyen']); ?></strong>
                                </div>
                                <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $schedule['trangThai'])); ?>">
                                    <?php echo $schedule['trangThai']; ?>
                                </span>
                            </div>

                            <h3 class="schedule-name">
                                <?php echo htmlspecialchars($schedule['tenLichTrinh']); ?>
                            </h3>

                            <div class="schedule-info">
                                <div class="info-row">
                                    <span class="label"><i class="fas fa-map-marker-alt"></i> Tuyến:</span>
                                    <span class="value"><?php echo htmlspecialchars($schedule['diemDi'] . ' → ' . $schedule['diemDen']); ?></span>
                                </div>

                                <div class="info-row">
                                    <span class="label"><i class="fas fa-clock"></i> Giờ:</span>
                                    <span class="value"><?php echo date('H:i', strtotime($schedule['gioKhoiHanh'])) . ' - ' . date('H:i', strtotime($schedule['gioKetThuc'])); ?></span>
                                </div>

                                <!-- add driver name display -->
                                <div class="info-row">
                                    <span class="label"><i class="fas fa-user"></i> Tài xế:</span>
                                    <span class="value driver-info">
                                        <?php 
                                        if (!empty($schedule['tenTaiXe'])) {
                                            echo htmlspecialchars($schedule['tenTaiXe']);
                                            if (!empty($schedule['sdtTaiXe'])) {
                                                echo ' <span class="phone">(' . htmlspecialchars($schedule['sdtTaiXe']) . ')</span>';
                                            }
                                        } else {
                                            echo '<span class="no-driver">Chưa phân công</span>';
                                        }
                                        ?>
                                    </span>
                                </div>

                                <div class="info-row">
                                    <span class="label"><i class="fas fa-calendar"></i> Ngày:</span>
                                    <span class="value"><?php echo date('d/m/Y', strtotime($schedule['ngayBatDau'])) . ' - ' . date('d/m/Y', strtotime($schedule['ngayKetThuc'])); ?></span>
                                </div>

                                <div class="info-row">
                                    <span class="label"><i class="fas fa-repeats"></i> Thứ:</span>
                                    <span class="value days-badge">
                                        <?php echo Schedule::formatDaysOfWeek($schedule['thuTrongTuan']); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="card-actions">
                                <a href="<?php echo BASE_URL; ?>/schedules/<?php echo $schedule['maLichTrinh']; ?>" 
                                   class="btn btn-sm btn-info" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i> Xem
                                </a>
                                <a href="<?php echo BASE_URL; ?>/schedules/<?php echo $schedule['maLichTrinh']; ?>/edit" 
                                   class="btn btn-sm btn-warning" title="Chỉnh sửa">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                <?php if ($schedule['trangThai'] != 'Ngừng'): ?>
                                    <button onclick="confirmDelete(<?php echo $schedule['maLichTrinh']; ?>)" 
                                            class="btn btn-sm btn-danger" title="Ngừng lịch trình">
                                        <i class="fas fa-stop"></i> Dừng
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- updated scripts -->
<script>
function confirmDelete(scheduleId) {
    if (confirm('Bạn có chắc chắn muốn ngừng lịch trình này?')) {
        window.location.href = '<?php echo BASE_URL; ?>/schedules/' + scheduleId + '/delete';
    }
}

document.getElementById('route').addEventListener('change', function() {
    document.getElementById('searchForm').submit();
});

document.getElementById('month').addEventListener('change', function() {
    document.getElementById('searchForm').submit();
});

document.getElementById('search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('searchForm').submit();
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
