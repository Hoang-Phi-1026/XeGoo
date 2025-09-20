<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="page-header">
        <div class="page-title">
            <h1>Quản lý Phương tiện</h1>
        </div>
        <div class="page-actions">
            <a href="<?php echo BASE_URL; ?>/vehicles/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm phương tiện mới
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
                <p>Tổng phương tiện</p>
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
        <div class="stat-card maintenance">
            <div class="stat-icon">
                <i class="fas fa-tools"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['maintenance']; ?></h3>
                <p>Đang bảo trì</p>
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
                        <label for="search">Biển số xe:</label>
                        <input type="text" class="form-control" name="search" id="search" 
                               placeholder="Nhập biển số xe..." 
                               value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="vehicleType">Loại phương tiện:</label>
                        <select class="form-control" name="vehicleType" id="vehicleType">
                            <option value="">Tất cả loại xe</option>
                            <?php foreach ($vehicleTypes as $key => $type): ?>
                                <option value="<?php echo $key; ?>" <?php echo (isset($_GET['vehicleType']) && $_GET['vehicleType'] == $key) ? 'selected' : ''; ?>>
                                    <?php echo $type; ?>
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
                            <a href="<?php echo BASE_URL; ?>/vehicles" class="btn btn-secondary">
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
    <?php if (!empty($_GET['search']) || !empty($_GET['vehicleType'])): ?>
        <div class="search-results-summary">
            <div class="results-info">
                <i class="fas fa-info-circle"></i>
                <span>Tìm thấy <strong><?php echo count($vehicles); ?></strong> phương tiện phù hợp với tiêu chí tìm kiếm</span>
            </div>
            <div class="active-filters">
                <?php if (!empty($_GET['search'])): ?>
                    <span class="filter-tag">
                        <i class="fas fa-search"></i> Biển số: "<?php echo htmlspecialchars($_GET['search']); ?>"
                        <a href="<?php echo BASE_URL; ?>/vehicles?<?php echo http_build_query(array_diff_key($_GET, ['search' => ''])); ?>" class="remove-filter">×</a>
                    </span>
                <?php endif; ?>
                <?php if (!empty($_GET['vehicleType'])): ?>
                    <span class="filter-tag">
                        <i class="fas fa-bus"></i> <?php echo $vehicleTypes[$_GET['vehicleType']] ?? $_GET['vehicleType']; ?>
                        <a href="<?php echo BASE_URL; ?>/vehicles?<?php echo http_build_query(array_diff_key($_GET, ['vehicleType' => ''])); ?>" class="remove-filter">×</a>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Vehicles Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Danh sách phương tiện</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Biển số</th>
                    <th>Loại phương tiện</th>
                    <th>Số chỗ</th>
                    <th>Loại chỗ ngồi</th>
                    <th>Hãng xe</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($vehicles)): ?>
                    <tr>
                        <td colspan="8" class="no-data">
                            <i class="fas fa-search"></i>
                            <p>Không tìm thấy phương tiện nào phù hợp với tiêu chí tìm kiếm</p>
                            <a href="<?php echo BASE_URL; ?>/vehicles" class="btn btn-outline btn-sm">
                                <i class="fas fa-list"></i> Xem tất cả phương tiện
                            </a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $dem = 0; ?>
                    <?php foreach ($vehicles as $vehicle): ?>
                        <?php $dem++; ?>
                        <tr>
                            <td>
                                <?php echo $dem; ?>
                            </td>
                            <td class="license-plate"><?php echo htmlspecialchars($vehicle['bienSo']); ?></td>
                            <!-- Updated to display vehicle type information from new table structure -->
                            <td><?php echo htmlspecialchars($vehicle['tenLoaiPhuongTien']); ?></td>
                            <td><?php echo $vehicle['soChoMacDinh']; ?></td>
                            <td><?php echo htmlspecialchars($vehicle['loaiChoNgoiMacDinh']); ?></td>
                            <td><?php echo htmlspecialchars($vehicle['hangXe'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="status-badge <?php echo $vehicle['trangThai'] == 'Đang hoạt động' ? 'active' : 'maintenance'; ?>">
                                    <?php echo $vehicle['trangThai']; ?>
                                </span>
                            </td>
                            <td class="actions">
                                <a href="<?php echo BASE_URL; ?>/vehicles/<?php echo $vehicle['maPhuongTien']; ?>" 
                                   class="btn btn-sm btn-info" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?php echo BASE_URL; ?>/vehicles/<?php echo $vehicle['maPhuongTien']; ?>/edit" 
                                   class="btn btn-sm btn-warning" title="Chỉnh sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($vehicle['trangThai'] == 'Đang hoạt động'): ?>
                                    <button onclick="confirmDelete(<?php echo $vehicle['maPhuongTien']; ?>)" 
                                            class="btn btn-sm btn-danger" title="Chuyển sang bảo trì">
                                        <i class="fas fa-tools"></i>
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
function confirmDelete(vehicleId) {
    if (confirm('Bạn có chắc chắn muốn chuyển phương tiện này sang trạng thái bảo trì?')) {
        window.location.href = '<?php echo BASE_URL; ?>/vehicles/' + vehicleId + '/delete';
    }
}

// Auto submit form when vehicle type changes
document.getElementById('vehicleType').addEventListener('change', function() {
    document.getElementById('searchForm').submit();
});

// Submit form when enter key is pressed in search input
document.getElementById('search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('searchForm').submit();
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
