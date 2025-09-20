<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="page-header">
        <div class="page-title">
            <h1>Thống kê Chuyến Xe</h1>
        </div>
        <div class="page-actions">
            <a href="<?php echo BASE_URL; ?>/trips" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="filters-section">
        <div class="search-header">
            <h3>Chọn khoảng thời gian</h3>
        </div>
        
        <form method="GET" class="filters-form">
            <div class="basic-search">
                <div class="search-row">
                    <div class="filter-group">
                        <label for="start_date">Từ ngày:</label>
                        <input type="date" name="start_date" id="start_date" 
                               value="<?php echo $_GET['start_date'] ?? date('Y-m-01'); ?>">
                    </div>
                    <div class="filter-group">
                        <label for="end_date">Đến ngày:</label>
                        <input type="date" name="end_date" id="end_date" 
                               value="<?php echo $_GET['end_date'] ?? date('Y-m-d'); ?>">
                    </div>
                    <div class="filter-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Xem thống kê
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Statistics Overview -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-bus"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['total_trips']; ?></h3>
                <p>Tổng chuyến xe</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-chair"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['total_seats']); ?></h3>
                <p>Tổng chỗ ngồi</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['booked_seats']); ?></h3>
                <p>Chỗ đã đặt</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-percentage"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['occupancy_rate']; ?>%</h3>
                <p>Tỷ lệ lấp đầy</p>
            </div>
        </div>
        <div class="stat-card revenue">
            <div class="stat-icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['revenue'], 0, ',', '.'); ?></h3>
                <p>Doanh thu (VNĐ)</p>
            </div>
        </div>
    </div>

    <!-- Status Distribution -->
    <div class="chart-section">
        <h3><i class="fas fa-pie-chart"></i> Phân bố theo trạng thái</h3>
        <div class="status-chart">
            <?php foreach ($statusStats as $status => $count): ?>
                <?php if ($count > 0): ?>
                    <div class="status-item">
                        <div class="status-bar">
                            <div class="status-fill <?php echo Trip::getStatusBadgeClass($status); ?>" 
                                 style="width: <?php echo $stats['total_trips'] > 0 ? ($count / $stats['total_trips']) * 100 : 0; ?>%"></div>
                        </div>
                        <div class="status-info">
                            <span class="status-label"><?php echo $status; ?></span>
                            <span class="status-count"><?php echo $count; ?> chuyến</span>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Trip List -->
    <div class="table-section">
        <h3><i class="fas fa-list"></i> Danh sách chuyến xe trong khoảng thời gian</h3>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Lịch trình</th>
                        <th>Tuyến đường</th>
                        <th>Ngày khởi hành</th>
                        <th>Giờ</th>
                        <th>Chỗ ngồi</th>
                        <th>Tỷ lệ lấp đầy</th>
                        <th>Doanh thu</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($trips)): ?>
                        <tr>
                            <td colspan="9" class="no-data">
                                <i class="fas fa-calendar-times"></i>
                                <p>Không có chuyến xe nào trong khoảng thời gian đã chọn</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $dem = 0; foreach ($trips as $trip): ?>
                            <?php $dem++; ?>
                            <tr>
                                <td>
                                    <?php echo $dem; ?>
                                </td>
                                <td><?php echo htmlspecialchars($trip['tenLichTrinh']); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($trip['kyHieuTuyen']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($trip['diemDi'] . ' → ' . $trip['diemDen']); ?></small>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($trip['ngayKhoiHanh'])); ?></td>
                                <td><?php echo date('H:i', strtotime($trip['thoiGianKhoiHanh'])); ?></td>
                                <td><?php echo $trip['soChoDaDat']; ?>/<?php echo $trip['soChoTong']; ?></td>
                                <td>
                                    <?php $occupancy = Trip::calculateOccupancy($trip['soChoDaDat'], $trip['soChoTong']); ?>
                                    <div class="occupancy-bar">
                                        <div class="occupancy-fill" style="width: <?php echo $occupancy; ?>%"></div>
                                        <span class="occupancy-text"><?php echo $occupancy; ?>%</span>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                    $revenue = ($trip['giaVe'] ?? 0) * $trip['soChoDaDat'];
                                    echo number_format($revenue, 0, ',', '.') . ' VNĐ';
                                    ?>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo Trip::getStatusBadgeClass($trip['trangThai']); ?>">
                                        <?php echo $trip['trangThai']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Set max date to today
document.getElementById('end_date').max = new Date().toISOString().split('T')[0];

// Update end date minimum when start date changes
document.getElementById('start_date').addEventListener('change', function() {
    document.getElementById('end_date').min = this.value;
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
