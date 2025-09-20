<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="page-header">
        <div class="page-title">
            <h1>Quản lý Chuyến Xe</h1>
        </div>
        <div class="page-actions">
            <a href="<?php echo BASE_URL; ?>/schedules/generate-trips" class="btn btn-success">
                <i class="fas fa-plus"></i> Sinh chuyến xe mới
            </a>
            <a href="<?php echo BASE_URL; ?>/trips/export<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''; ?>" class="btn btn-outline">
                <i class="fas fa-download"></i> Xuất Excel
            </a>
            <a href="<?php echo BASE_URL; ?>/trips/statistics" class="btn btn-info">
                <i class="fas fa-chart-bar"></i> Thống kê
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-bus"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['total']; ?></h3>
                <p>Tổng chuyến xe</p>
            </div>
        </div>
        <div class="stat-card completed">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['completed']; ?></h3>
                <p>Số chuyến xe đã hoàn thành</p>
            </div>
        </div>
        <div class="stat-card today">
            <div class="stat-icon">
                <i class="fas fa-calendar-day"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['today']; ?></h3>
                <p>Số chuyến xe hôm nay</p>
            </div>
        </div>
    </div>

    <!-- Search and Filter Form -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Tìm kiếm và lọc</h3>
        </div>
        <div class="card-body">
            <form method="GET" class="filter-form" id="searchForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="from_date">Từ ngày:</label>
                        <input type="date" class="form-control" name="from_date" id="from_date" 
                               value="<?php echo htmlspecialchars($_GET['from_date'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="to_date">Đến ngày:</label>
                        <input type="date" class="form-control" name="to_date" id="to_date" 
                               value="<?php echo htmlspecialchars($_GET['to_date'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="route">Tuyến đường:</label>
                        <select class="form-control" name="route" id="route">
                            <option value="">Tất cả tuyến đường</option>
                            <?php foreach ($routes as $route): ?>
                                <option value="<?php echo $route['maTuyenDuong']; ?>" 
                                        <?php echo (isset($_GET['route']) && $_GET['route'] == $route['maTuyenDuong']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($route['kyHieuTuyen'] . ' - ' . $route['diemDi'] . ' → ' . $route['diemDen']); ?>
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
                            <a href="<?php echo BASE_URL; ?>/trips" class="btn btn-secondary">
                                <i class="fas fa-refresh"></i>
                                Đặt lại
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Search results summary -->
    <?php if (!empty($_GET['from_date']) || !empty($_GET['to_date']) || !empty($_GET['route'])): ?>
        <div class="search-results-summary">
            <div class="results-info">
                <i class="fas fa-info-circle"></i>
                <span>Tìm thấy <strong><?php echo count($trips); ?></strong> chuyến xe phù hợp với tiêu chí tìm kiếm</span>
            </div>
            <div class="active-filters">
                <?php if (!empty($_GET['from_date'])): ?>
                    <span class="filter-tag">
                        <i class="fas fa-calendar"></i> Từ: <?php echo date('d/m/Y', strtotime($_GET['from_date'])); ?>
                        <a href="<?php echo BASE_URL; ?>/trips?<?php echo http_build_query(array_diff_key($_GET, ['from_date' => ''])); ?>" class="remove-filter">×</a>
                    </span>
                <?php endif; ?>
                <?php if (!empty($_GET['to_date'])): ?>
                    <span class="filter-tag">
                        <i class="fas fa-calendar"></i> Đến: <?php echo date('d/m/Y', strtotime($_GET['to_date'])); ?>
                        <a href="<?php echo BASE_URL; ?>/trips?<?php echo http_build_query(array_diff_key($_GET, ['to_date' => ''])); ?>" class="remove-filter">×</a>
                    </span>
                <?php endif; ?>
                <?php if (!empty($_GET['route'])): ?>
                    <?php 
                    $selectedRoute = null;
                    foreach ($routes as $route) {
                        if ($route['maTuyenDuong'] == $_GET['route']) {
                            $selectedRoute = $route;
                            break;
                        }
                    }
                    ?>
                    <?php if ($selectedRoute): ?>
                    <span class="filter-tag">
                        <i class="fas fa-route"></i> <?php echo htmlspecialchars($selectedRoute['kyHieuTuyen'] . ' - ' . $selectedRoute['diemDi'] . ' → ' . $selectedRoute['diemDen']); ?>
                        <a href="<?php echo BASE_URL; ?>/trips?<?php echo http_build_query(array_diff_key($_GET, ['route' => ''])); ?>" class="remove-filter">×</a>
                    </span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- search -->

    <!-- Trips Table -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-bus"></i> Danh sách chuyến xe</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Lịch trình</th>
                    <th>Tuyến đường</th>
                    <th>Xe</th>
                    <th>Ngày khởi hành</th>
                    <th>Giờ</th>
                    <th>Chỗ ngồi</th>
                    <th>Giá vé</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($trips)): ?>
                    <tr>
                        <td colspan="11" class="no-data">
                            <i class="fas fa-search"></i>
                            <p>Không tìm thấy chuyến xe nào phù hợp với tiêu chí tìm kiếm</p>
                            <a href="<?php echo BASE_URL; ?>/trips" class="btn btn-outline btn-sm">
                                <i class="fas fa-list"></i> Xem tất cả chuyến xe
                            </a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $dem = 0; foreach ($trips as $trip): ?>
                        <?php $dem++; ?>
                        <tr>
                            <td>
                                <?php echo $dem; ?>
                            </td>
                            <td>
                                <div class="schedule-info">
                                    <strong><?php echo htmlspecialchars($trip['tenLichTrinh']); ?></strong>
                                </div>
                            </td>
                            <td>
                                <div class="route-info">
                                    <strong><?php echo htmlspecialchars($trip['kyHieuTuyen']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($trip['diemDi'] . ' → ' . $trip['diemDen']); ?></small>
                                </div>
                            </td>
                            <td>
                                <div class="vehicle-info">
                                    <strong><?php echo htmlspecialchars($trip['bienSo']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($trip['tenLoaiPhuongTien']); ?></small>
                                </div>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($trip['ngayKhoiHanh'])); ?></td>
                            <td>
                                <div class="time-info">
                                    <?php echo date('H:i', strtotime($trip['thoiGianKhoiHanh'])); ?><br>
                                    <small><?php echo date('H:i', strtotime($trip['thoiGianKetThuc'])); ?></small>
                                </div>
                            </td>
                            <td>
                                <div class="seat-info">
                                    <strong><?php echo $trip['soChoDaDat']; ?>/<?php echo $trip['soChoTong']; ?></strong><br>
                                    <small>Trống: <?php echo $trip['soChoTrong']; ?></small>
                                </div>
                            </td>
                            <td>
                                <?php if ($trip['giaVe']): ?>
                                    <strong><?php echo number_format($trip['giaVe'], 0, ',', '.'); ?> VNĐ</strong><br>
                                    <small><?php echo $trip['tenLoaiVe'] ?? 'Vé thường'; ?></small>
                                <?php else: ?>
                                    <span class="text-muted">Chưa có giá</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-badge <?php echo Trip::getStatusBadgeClass($trip['trangThai']); ?>">
                                    <?php echo $trip['trangThai']; ?>
                                </span>
                            </td>
                            <td class="actions">
                                <a href="<?php echo BASE_URL; ?>/trips/<?php echo $trip['maChuyenXe']; ?>" 
                                   class="btn btn-sm btn-info" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if (!in_array($trip['trangThai'], ['Đã khởi hành', 'Hoàn thành'])): ?>
                                    <button onclick="confirmDelete(<?php echo $trip['maChuyenXe']; ?>)" 
                                            class="btn btn-sm btn-danger" title="Xóa chuyến xe">
                                        <i class="fas fa-trash"></i>
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
function confirmDelete(tripId) {
    if (confirm('Bạn có chắc chắn muốn xóa chuyến xe này? Hành động này không thể hoàn tác.')) {
        window.location.href = '<?php echo BASE_URL; ?>/trips/' + tripId + '/delete';
    }
}

// Auto submit when date or route changes
document.getElementById('from_date').addEventListener('change', function() {
    // Auto submit if there's already a to_date or route selected
    if (document.getElementById('to_date').value || document.getElementById('route').value) {
        document.getElementById('searchForm').submit();
    }
});

document.getElementById('to_date').addEventListener('change', function() {
    // Auto submit if there's already a from_date or route selected
    if (document.getElementById('from_date').value || document.getElementById('route').value) {
        document.getElementById('searchForm').submit();
    }
});

document.getElementById('route').addEventListener('change', function() {
    if (this.value) {
        document.getElementById('searchForm').submit();
    }
});

// Validate date range
document.getElementById('searchForm').addEventListener('submit', function(e) {
    const fromDate = document.getElementById('from_date').value;
    const toDate = document.getElementById('to_date').value;
    
    if (fromDate && toDate && fromDate > toDate) {
        e.preventDefault();
        alert('Ngày bắt đầu không thể lớn hơn ngày kết thúc!');
        return false;
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
