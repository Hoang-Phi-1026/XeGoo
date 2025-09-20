<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="page-header">
        <div class="page-title">
            <h1> Quản lý Lịch Trình</h1>
        </div>
        <div class="page-actions">
            <a href="<?php echo BASE_URL; ?>/schedules/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm lịch trình mới
            </a>
            <a href="<?php echo BASE_URL; ?>/schedules/generate-trips" class="btn btn-success">
                <i class="fas fa-cogs"></i> Sinh chuyến xe
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
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

    <!-- Search and Filters -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Tìm kiếm và lọc</h3>
        </div>
        <div class="card-body">
            <form method="GET" class="filter-form" id="searchForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="search">Tìm kiếm nhanh:</label>
                        <input type="text" name="search" id="search" class="form-control"
                               placeholder="Nhập tên lịch trình, ký hiệu tuyến..." 
                               value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="route">Tuyến đường:</label>
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
                        <label>&nbsp;</label>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                                Tìm kiếm
                            </button>
                            <a href="<?php echo BASE_URL; ?>/schedules" class="btn btn-secondary">
                                <i class="fas fa-refresh"></i>
                                Đặt lại
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Search Results Summary -->
    <?php if (!empty($_GET['search']) || !empty($_GET['route'])): ?>
        <div class="search-results-summary">
            <div class="results-info">
                <i class="fas fa-info-circle"></i>
                <span>Tìm thấy <strong><?php echo count($schedules); ?></strong> lịch trình phù hợp với tiêu chí tìm kiếm</span>
            </div>
            <div class="active-filters">
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

    <!-- Schedules Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Danh sách lịch trình</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Tên lịch trình</th>
                            <th>Tuyến đường</th>
                            <th>Giờ khởi hành</th>
                            <th>Ngày hoạt động</th>
                            <th>Thứ trong tuần</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($schedules)): ?>
                            <tr>
                                <td colspan="9" class="no-data">
                                    <i class="fas fa-search"></i>
                                    <p>Không tìm thấy lịch trình nào phù hợp với tiêu chí tìm kiếm</p>
                                    <a href="<?php echo BASE_URL; ?>/schedules" class="btn btn-outline btn-sm">
                                        <i class="fas fa-list"></i> Xem tất cả lịch trình
                                    </a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $dem = 0; foreach ($schedules as $schedule): ?>
                                <?php $dem++; ?>
                                <tr>
                                    <td>
                                        <?php echo $dem; ?>
                                    </td>
                                    <td class="schedule-name"><?php echo htmlspecialchars($schedule['tenLichTrinh']); ?></td>
                                    <td>
                                        <div class="route-info">
                                            <strong><?php echo htmlspecialchars($schedule['kyHieuTuyen']); ?></strong><br>
                                            <small><?php echo htmlspecialchars($schedule['diemDi'] . ' → ' . $schedule['diemDen']); ?></small>
                                        </div>
                                    </td>
                                    <td><?php echo date('H:i', strtotime($schedule['gioKhoiHanh'])); ?></td>
                                    <td>
                                        <small><?php echo date('d/m/Y', strtotime($schedule['ngayBatDau'])); ?> - <?php echo date('d/m/Y', strtotime($schedule['ngayKetThuc'])); ?></small>
                                    </td>
                                    <td>
                                        <span class="days-badge"><?php echo Schedule::formatDaysOfWeek($schedule['thuTrongTuan']); ?></span>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo strtolower($schedule['trangThai']); ?>">
                                            <?php echo $schedule['trangThai']; ?>
                                        </span>
                                    </td>
                                    <td class="actions">
                                        <a href="<?php echo BASE_URL; ?>/schedules/<?php echo $schedule['maLichTrinh']; ?>" 
                                           class="btn btn-sm btn-info" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>/schedules/<?php echo $schedule['maLichTrinh']; ?>/edit" 
                                           class="btn btn-sm btn-warning" title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($schedule['trangThai'] != 'Ngừng'): ?>
                                            <button onclick="confirmDelete(<?php echo $schedule['maLichTrinh']; ?>)" 
                                                    class="btn btn-sm btn-danger" title="Ngừng lịch trình">
                                                <i class="fas fa-stop"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(scheduleId) {
    if (confirm('Bạn có chắc chắn muốn ngừng lịch trình này?')) {
        window.location.href = '<?php echo BASE_URL; ?>/schedules/' + scheduleId + '/delete';
    }
}

document.getElementById('route').addEventListener('change', function() {
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
