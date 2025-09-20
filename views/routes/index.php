<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="page-header">
        <div class="page-title">
            <h1>Quản lý Tuyến đường</h1>
        </div>
        <div class="page-actions">
            <a href="<?php echo BASE_URL; ?>/routes/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm tuyến đường mới
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-route"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['total']; ?></h3>
                <p>Tổng tuyến đường</p>
            </div>
        </div>
        <div class="stat-card active">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['active']; ?></h3>
                <p>Đang hoạt động</p>
            </div>
        </div>
        <div class="stat-card inactive">
            <div class="stat-icon">
                <i class="fas fa-pause-circle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['inactive']; ?></h3>
                <p>Ngừng khai thác</p>
            </div>
        </div>
        <div class="stat-card distance">
            <div class="stat-icon">
                <i class="fas fa-road"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['avg_distance']; ?> km</h3>
                <p>Khoảng cách TB</p>
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
                        <label for="diemDi">Điểm đi:</label>
                        <select class="form-control" name="diemDi" id="diemDi">
                            <option value="">Tất cả điểm đi</option>
                            <?php foreach ($startPoints as $startPoint): ?>
                                <option value="<?php echo htmlspecialchars($startPoint); ?>" 
                                        <?php echo (isset($_GET['diemDi']) && $_GET['diemDi'] == $startPoint) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($startPoint); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="diemDen">Điểm đến:</label>
                        <select class="form-control" name="diemDen" id="diemDen">
                            <option value="">Tất cả điểm đến</option>
                            <?php foreach ($endPoints as $endPoint): ?>
                                <option value="<?php echo htmlspecialchars($endPoint); ?>" 
                                        <?php echo (isset($_GET['diemDen']) && $_GET['diemDen'] == $endPoint) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($endPoint); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="status">Trạng thái:</label>
                        <select class="form-control" name="status" id="status">
                            <option value="">Tất cả trạng thái</option>
                            <?php foreach ($statusOptions as $key => $status): ?>
                                <option value="<?php echo $key; ?>" <?php echo (isset($_GET['status']) && $_GET['status'] == $key) ? 'selected' : ''; ?>>
                                    <?php echo $status; ?>
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
                            <a href="<?php echo BASE_URL; ?>/routes" class="btn btn-secondary">
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
    <?php if (!empty($_GET['diemDi']) || !empty($_GET['diemDen']) || !empty($_GET['status'])): ?>
        <div class="search-results-summary">
            <div class="results-info">
                <i class="fas fa-info-circle"></i>
                <span>Tìm thấy <strong><?php echo count($routes); ?></strong> tuyến đường phù hợp với tiêu chí tìm kiếm</span>
            </div>
            <div class="active-filters">
                <?php if (!empty($_GET['diemDi'])): ?>
                    <span class="filter-tag">
                        <i class="fas fa-map-marker-alt"></i> Từ: <?php echo htmlspecialchars($_GET['diemDi']); ?>
                        <a href="<?php echo BASE_URL; ?>/routes?<?php echo http_build_query(array_diff_key($_GET, ['diemDi' => ''])); ?>" class="remove-filter">×</a>
                    </span>
                <?php endif; ?>
                <?php if (!empty($_GET['diemDen'])): ?>
                    <span class="filter-tag">
                        <i class="fas fa-map-marker-alt"></i> Đến: <?php echo htmlspecialchars($_GET['diemDen']); ?>
                        <a href="<?php echo BASE_URL; ?>/routes?<?php echo http_build_query(array_diff_key($_GET, ['diemDen' => ''])); ?>" class="remove-filter">×</a>
                    </span>
                <?php endif; ?>
                <?php if (!empty($_GET['status'])): ?>
                    <span class="filter-tag">
                        <i class="fas fa-flag"></i> <?php echo $_GET['status']; ?>
                        <a href="<?php echo BASE_URL; ?>/routes?<?php echo http_build_query(array_diff_key($_GET, ['status' => ''])); ?>" class="remove-filter">×</a>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Routes Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Danh sách tuyến đường</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Ký hiệu tuyến</th>
                    <th>Điểm đi</th>
                    <th>Điểm đến</th>
                    <th>Khoảng cách</th>
                    <th>Thời gian</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($routes)): ?>
                    <tr>
                        <td colspan="8" class="no-data">
                            <i class="fas fa-search"></i>
                            <p>Không tìm thấy tuyến đường nào phù hợp với tiêu chí tìm kiếm</p>
                            <a href="<?php echo BASE_URL; ?>/routes" class="btn btn-outline btn-sm">
                                <i class="fas fa-list"></i> Xem tất cả tuyến đường
                            </a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $dem = 0; foreach ($routes as $route): ?>
                        <?php $dem++; ?>
                        <tr>
                            <td>
                                <?php echo $dem; ?>
                            </td>
                            <td class="route-code"><?php echo htmlspecialchars($route['kyHieuTuyen']); ?></td>
                            <td><?php echo htmlspecialchars($route['diemDi']); ?></td>
                            <td><?php echo htmlspecialchars($route['diemDen']); ?></td>
                            <td><?php echo $route['khoangCach']; ?> km</td>
                            <td><?php echo Route::formatTravelTime($route['thoiGianDiChuyen']); ?></td>
                            <td>
                                <span class="status-badge <?php echo $route['trangThai'] == 'Đang hoạt động' ? 'active' : 'inactive'; ?>">
                                    <?php echo $route['trangThai']; ?>
                                </span>
                            </td>
                            <td class="actions">
                                <a href="<?php echo BASE_URL; ?>/routes/<?php echo $route['maTuyenDuong']; ?>" 
                                   class="btn btn-sm btn-info" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?php echo BASE_URL; ?>/routes/<?php echo $route['maTuyenDuong']; ?>/edit" 
                                   class="btn btn-sm btn-warning" title="Chỉnh sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($route['trangThai'] == 'Đang hoạt động'): ?>
                                    <button onclick="confirmDelete(<?php echo $route['maTuyenDuong']; ?>)" 
                                            class="btn btn-sm btn-danger" title="Ngừng khai thác">
                                        <i class="fas fa-pause"></i>
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
function confirmDelete(routeId) {
    if (confirm('Bạn có chắc chắn muốn chuyển tuyến đường này sang trạng thái ngừng khai thác?')) {
        window.location.href = '<?php echo BASE_URL; ?>/routes/' + routeId + '/delete';
    }
}

// Auto submit form when dropdown changes
document.getElementById('diemDi').addEventListener('change', function() {
    if (this.value || document.getElementById('diemDen').value) {
        document.getElementById('searchForm').submit();
    }
});

document.getElementById('diemDen').addEventListener('change', function() {
    if (this.value || document.getElementById('diemDi').value) {
        document.getElementById('searchForm').submit();
    }
});

document.getElementById('status').addEventListener('change', function() {
    document.getElementById('searchForm').submit();
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
