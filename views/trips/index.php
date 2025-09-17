<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="page-header">
        <div class="page-title">
            <h1><i class="fas fa-bus"></i> Quản lý Chuyến Xe</h1>
            <p>Danh sách tất cả chuyến xe trong hệ thống</p>
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
        <div class="stat-card ready">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['ready']; ?></h3>
                <p>Sẵn sàng</p>
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
        <div class="stat-card completed">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['completed']; ?></h3>
                <p>Hoàn thành</p>
            </div>
        </div>
        <div class="stat-card today">
            <div class="stat-icon">
                <i class="fas fa-calendar-day"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['today']; ?></h3>
                <p>Hôm nay</p>
            </div>
        </div>
        <div class="stat-card occupancy">
            <div class="stat-icon">
                <i class="fas fa-percentage"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['avg_occupancy']; ?>%</h3>
                <p>Tỷ lệ lấp đầy TB</p>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="filters-section">
        <div class="search-header">
            <h3><i class="fas fa-search"></i> Tìm kiếm và Lọc</h3>
            <button type="button" class="btn btn-outline btn-sm" onclick="toggleAdvancedSearch()">
                <i class="fas fa-cog"></i> <span id="advancedToggleText">Tìm kiếm nâng cao</span>
            </button>
        </div>
        
        <form method="GET" class="filters-form" id="searchForm">
            <div class="basic-search">
                <div class="search-row">
                    <div class="filter-group flex-2">
                        <label for="search">Tìm kiếm nhanh:</label>
                        <div class="search-input-group">
                            <input type="text" name="search" id="search" 
                                   placeholder="Nhập tên lịch trình, ký hiệu tuyến, biển số xe..." 
                                   value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="filter-group">
                        <label for="status">Trạng thái:</label>
                        <select name="status" id="status">
                            <option value="">Tất cả trạng thái</option>
                            <?php foreach ($statusOptions as $key => $status): ?>
                                <option value="<?php echo $key; ?>" 
                                        <?php echo (isset($_GET['status']) && $_GET['status'] == $key) ? 'selected' : ''; ?>>
                                    <?php echo $status; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="advanced-search" id="advancedSearch" style="display: none;">
                <div class="search-row">
                    <div class="filter-group">
                        <label for="schedule">Lịch trình:</label>
                        <select name="schedule" id="schedule">
                            <option value="">Tất cả lịch trình</option>
                            <?php foreach ($schedules as $schedule): ?>
                                <option value="<?php echo $schedule['maLichTrinh']; ?>" 
                                        <?php echo (isset($_GET['schedule']) && $_GET['schedule'] == $schedule['maLichTrinh']) ? 'selected' : ''; ?>>
                                    <?php echo $schedule['kyHieuTuyen'] . ' - ' . $schedule['tenLichTrinh']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="vehicle">Phương tiện:</label>
                        <select name="vehicle" id="vehicle">
                            <option value="">Tất cả xe</option>
                            <?php foreach ($vehicles as $vehicle): ?>
                                <option value="<?php echo $vehicle['maPhuongTien']; ?>" 
                                        <?php echo (isset($_GET['vehicle']) && $_GET['vehicle'] == $vehicle['maPhuongTien']) ? 'selected' : ''; ?>>
                                    <?php echo $vehicle['tenLoaiPhuongTien'] . ' - ' . $vehicle['bienSo']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
                <a href="<?php echo BASE_URL; ?>/trips" class="btn btn-outline">
                    <i class="fas fa-times"></i> Xóa bộ lọc
                </a>
            </div>
        </form>
    </div>

    <!-- Search Results Summary -->
    <?php if (!empty($_GET['search']) || !empty($_GET['status']) || !empty($_GET['schedule']) || !empty($_GET['vehicle'])): ?>
        <div class="search-results-summary">
            <div class="results-info">
                <i class="fas fa-info-circle"></i>
                <span>Tìm thấy <strong><?php echo count($trips); ?></strong> chuyến xe phù hợp với tiêu chí tìm kiếm</span>
            </div>
            <div class="active-filters">
                <?php if (!empty($_GET['search'])): ?>
                    <span class="filter-tag">
                        <i class="fas fa-search"></i> "<?php echo htmlspecialchars($_GET['search']); ?>"
                        <a href="<?php echo BASE_URL; ?>/trips?<?php echo http_build_query(array_diff_key($_GET, ['search' => ''])); ?>" class="remove-filter">×</a>
                    </span>
                <?php endif; ?>
                <?php if (!empty($_GET['status'])): ?>
                    <span class="filter-tag">
                        <i class="fas fa-flag"></i> <?php echo $_GET['status']; ?>
                        <a href="<?php echo BASE_URL; ?>/trips?<?php echo http_build_query(array_diff_key($_GET, ['status' => ''])); ?>" class="remove-filter">×</a>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Trips Table -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Lịch trình</th>
                    <th>Tuyến đường</th>
                    <th>Xe</th>
                    <th>Ngày khởi hành</th>
                    <th>Giờ</th>
                    <th>Chỗ ngồi</th>
                    <th>Tỷ lệ lấp đầy</th>
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
                    <?php foreach ($trips as $trip): ?>
                        <tr>
                            <td><?php echo $trip['maChuyenXe']; ?></td>
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
                                <?php $occupancy = Trip::calculateOccupancy($trip['soChoDaDat'], $trip['soChoTong']); ?>
                                <div class="occupancy-bar">
                                    <div class="occupancy-fill" style="width: <?php echo $occupancy; ?>%"></div>
                                    <span class="occupancy-text"><?php echo $occupancy; ?>%</span>
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

<script>
function confirmDelete(tripId) {
    if (confirm('Bạn có chắc chắn muốn xóa chuyến xe này? Hành động này không thể hoàn tác.')) {
        window.location.href = '<?php echo BASE_URL; ?>/trips/' + tripId + '/delete';
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
