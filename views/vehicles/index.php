<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="page-header">
        <div class="page-title">
            <h1><i class="fas fa-bus"></i> Quản lý Phương tiện</h1>
            <p>Danh sách tất cả phương tiện trong hệ thống</p>
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

    <!-- Enhanced Search and Filters -->
    <div class="filters-section">
        <div class="search-header">
            <h3><i class="fas fa-search"></i> Tìm kiếm và Lọc</h3>
            <button type="button" class="btn btn-outline btn-sm" onclick="toggleAdvancedSearch()">
                <i class="fas fa-cog"></i> <span id="advancedToggleText">Tìm kiếm nâng cao</span>
            </button>
        </div>
        
        <form method="GET" class="filters-form" id="searchForm">
            <!-- Enhanced basic search section -->
            <div class="basic-search">
                <div class="search-row">
                    <div class="filter-group flex-2">
                        <label for="search">Tìm kiếm nhanh:</label>
                        <div class="search-input-group">
                            <input type="text" name="search" id="search" 
                                   placeholder="Nhập biển số hoặc loại xe..." 
                                   value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="filter-group">
                        <label for="status">Trạng thái:</label>
                        <select name="status" id="status">
                            <option value="">Tất cả</option>
                            <option value="Đang hoạt động" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Đang hoạt động') ? 'selected' : ''; ?>>Đang hoạt động</option>
                            <option value="Bảo trì" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Bảo trì') ? 'selected' : ''; ?>>Bảo trì</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Added advanced search section -->
            <div class="advanced-search" id="advancedSearch" style="display: none;">
                <div class="search-row">
                    <div class="filter-group">
                        <label for="vehicleType">Loại phương tiện:</label>
                        <select name="vehicleType" id="vehicleType">
                            <option value="">Tất cả loại xe</option>
                            <?php foreach ($vehicleTypes as $key => $type): ?>
                                <option value="<?php echo $key; ?>" <?php echo (isset($_GET['vehicleType']) && $_GET['vehicleType'] == $key) ? 'selected' : ''; ?>>
                                    <?php echo $type; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="seatType">Loại chỗ ngồi:</label>
                        <select name="seatType" id="seatType">
                            <option value="">Tất cả loại chỗ</option>
                            <?php foreach ($seatTypes as $key => $type): ?>
                                <option value="<?php echo $key; ?>" <?php echo (isset($_GET['seatType']) && $_GET['seatType'] == $key) ? 'selected' : ''; ?>>
                                    <?php echo $type; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="search-row">
                    <div class="filter-group">
                        <label for="minSeats">Số chỗ tối thiểu:</label>
                        <input type="number" name="minSeats" id="minSeats" min="1" max="100" 
                               placeholder="VD: 7" value="<?php echo htmlspecialchars($_GET['minSeats'] ?? ''); ?>">
                    </div>
                    <div class="filter-group">
                        <label for="maxSeats">Số chỗ tối đa:</label>
                        <input type="number" name="maxSeats" id="maxSeats" min="1" max="100" 
                               placeholder="VD: 40" value="<?php echo htmlspecialchars($_GET['maxSeats'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
                <a href="<?php echo BASE_URL; ?>/vehicles" class="btn btn-outline">
                    <i class="fas fa-times"></i> Xóa bộ lọc
                </a>
            </div>
        </form>
    </div>

    <!-- Added search results summary -->
    <?php if (!empty($_GET['search']) || !empty($_GET['status']) || !empty($_GET['vehicleType']) || !empty($_GET['seatType']) || !empty($_GET['minSeats']) || !empty($_GET['maxSeats'])): ?>
        <div class="search-results-summary">
            <div class="results-info">
                <i class="fas fa-info-circle"></i>
                <span>Tìm thấy <strong><?php echo count($vehicles); ?></strong> phương tiện phù hợp với tiêu chí tìm kiếm</span>
            </div>
            <div class="active-filters">
                <?php if (!empty($_GET['search'])): ?>
                    <span class="filter-tag">
                        <i class="fas fa-search"></i> "<?php echo htmlspecialchars($_GET['search']); ?>"
                        <a href="<?php echo BASE_URL; ?>/vehicles?<?php echo http_build_query(array_diff_key($_GET, ['search' => ''])); ?>" class="remove-filter">×</a>
                    </span>
                <?php endif; ?>
                <?php if (!empty($_GET['status'])): ?>
                    <span class="filter-tag">
                        <i class="fas fa-flag"></i> <?php echo $_GET['status']; ?>
                        <a href="<?php echo BASE_URL; ?>/vehicles?<?php echo http_build_query(array_diff_key($_GET, ['status' => ''])); ?>" class="remove-filter">×</a>
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
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
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
                    <?php foreach ($vehicles as $vehicle): ?>
                        <tr>
                            <td><?php echo $vehicle['maPhuongTien']; ?></td>
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

<script>
function confirmDelete(vehicleId) {
    if (confirm('Bạn có chắc chắn muốn chuyển phương tiện này sang trạng thái bảo trì?')) {
        window.location.href = '<?php echo BASE_URL; ?>/vehicles/' + vehicleId + '/delete';
    }
}

function toggleAdvancedSearch() {
    const advancedSearch = document.getElementById('advancedSearch');
    const toggleText = document.getElementById('advancedToggleText');
    
    if (advancedSearch.style.display === 'none') {
        advancedSearch.style.display = 'block';
        toggleText.textContent = 'Ẩn tìm kiếm nâng cao';
    } else {
        advancedSearch.style.display = 'none';
        toggleText.textContent = 'Tìm kiếm nâng cao';
    }
}

function exportResults() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'csv');
    window.location.href = '<?php echo BASE_URL; ?>/vehicles?' + params.toString();
}

document.getElementById('status').addEventListener('change', function() {
    if (!document.getElementById('advancedSearch').style.display || document.getElementById('advancedSearch').style.display === 'none') {
        document.getElementById('searchForm').submit();
    }
});

document.getElementById('search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('searchForm').submit();
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
