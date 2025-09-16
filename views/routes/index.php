<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="page-header">
        <div class="page-title">
            <h1><i class="fas fa-route"></i> Quản lý Tuyến đường</h1>
            <p>Danh sách tất cả tuyến đường trong hệ thống</p>
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
                                   placeholder="Nhập ký hiệu tuyến, điểm đi hoặc điểm đến..." 
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
                            <?php foreach ($statusOptions as $key => $status): ?>
                                <option value="<?php echo $key; ?>" <?php echo (isset($_GET['status']) && $_GET['status'] == $key) ? 'selected' : ''; ?>>
                                    <?php echo $status; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Added advanced search section -->
            <div class="advanced-search" id="advancedSearch" style="display: none;">
                <div class="search-row">
                    <div class="filter-group">
                        <label for="diemDi">Điểm đi:</label>
                        <input type="text" name="diemDi" id="diemDi" 
                               placeholder="VD: TP. Hồ Chí Minh" 
                               value="<?php echo htmlspecialchars($_GET['diemDi'] ?? ''); ?>">
                    </div>
                    <div class="filter-group">
                        <label for="diemDen">Điểm đến:</label>
                        <input type="text" name="diemDen" id="diemDen" 
                               placeholder="VD: Đà Lạt" 
                               value="<?php echo htmlspecialchars($_GET['diemDen'] ?? ''); ?>">
                    </div>
                </div>
                <div class="search-row">
                    <div class="filter-group">
                        <label for="minDistance">Khoảng cách tối thiểu (km):</label>
                        <input type="number" name="minDistance" id="minDistance" min="1" max="2000" 
                               placeholder="VD: 100" value="<?php echo htmlspecialchars($_GET['minDistance'] ?? ''); ?>">
                    </div>
                    <div class="filter-group">
                        <label for="maxDistance">Khoảng cách tối đa (km):</label>
                        <input type="number" name="maxDistance" id="maxDistance" min="1" max="2000" 
                               placeholder="VD: 500" value="<?php echo htmlspecialchars($_GET['maxDistance'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
                <a href="<?php echo BASE_URL; ?>/routes" class="btn btn-outline">
                    <i class="fas fa-times"></i> Xóa bộ lọc
                </a>
            </div>
        </form>
    </div>

    <!-- Added search results summary -->
    <?php if (!empty($_GET['search']) || !empty($_GET['status']) || !empty($_GET['diemDi']) || !empty($_GET['diemDen']) || !empty($_GET['minDistance']) || !empty($_GET['maxDistance'])): ?>
        <div class="search-results-summary">
            <div class="results-info">
                <i class="fas fa-info-circle"></i>
                <span>Tìm thấy <strong><?php echo count($routes); ?></strong> tuyến đường phù hợp với tiêu chí tìm kiếm</span>
            </div>
            <div class="active-filters">
                <?php if (!empty($_GET['search'])): ?>
                    <span class="filter-tag">
                        <i class="fas fa-search"></i> "<?php echo htmlspecialchars($_GET['search']); ?>"
                        <a href="<?php echo BASE_URL; ?>/routes?<?php echo http_build_query(array_diff_key($_GET, ['search' => ''])); ?>" class="remove-filter">×</a>
                    </span>
                <?php endif; ?>
                <?php if (!empty($_GET['status'])): ?>
                    <span class="filter-tag">
                        <i class="fas fa-flag"></i> <?php echo $_GET['status']; ?>
                        <a href="<?php echo BASE_URL; ?>/routes?<?php echo http_build_query(array_diff_key($_GET, ['status' => ''])); ?>" class="remove-filter">×</a>
                    </span>
                <?php endif; ?>
                <?php if (!empty($_GET['diemDi'])): ?>
                    <span class="filter-tag">
                        <i class="fas fa-map-marker-alt"></i> Từ: <?php echo $_GET['diemDi']; ?>
                        <a href="<?php echo BASE_URL; ?>/routes?<?php echo http_build_query(array_diff_key($_GET, ['diemDi' => ''])); ?>" class="remove-filter">×</a>
                    </span>
                <?php endif; ?>
                <?php if (!empty($_GET['diemDen'])): ?>
                    <span class="filter-tag">
                        <i class="fas fa-map-marker-alt"></i> Đến: <?php echo $_GET['diemDen']; ?>
                        <a href="<?php echo BASE_URL; ?>/routes?<?php echo http_build_query(array_diff_key($_GET, ['diemDen' => ''])); ?>" class="remove-filter">×</a>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Routes Table -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
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
                    <?php foreach ($routes as $route): ?>
                        <tr>
                            <td><?php echo $route['maTuyenDuong']; ?></td>
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

<script>
function confirmDelete(routeId) {
    if (confirm('Bạn có chắc chắn muốn chuyển tuyến đường này sang trạng thái ngừng khai thác?')) {
        window.location.href = '<?php echo BASE_URL; ?>/routes/' + routeId + '/delete';
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
