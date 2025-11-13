<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống Kê - Admin XeGoo</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/admin-statistics.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

    

        <!-- Add tabs navigation for 3 main sections -->
        <div class="stats-tabs">
            <button class="tab-button active" data-tab="revenue">
                <i class="fas fa-coins"></i>  Thống kê doanh thu
            </button>
            <button class="tab-button" data-tab="customer">
                <i class="fas fa-users"></i>  Thống kê hành khách
            </button>
            <button class="tab-button" data-tab="trip">
                <i class="fas fa-bus"></i>  Thống kê chuyến xe
            </button>
        </div>

        <!-- ==================== PHẦN I: THỐNG KÊ DOANH THU ==================== -->
        <div class="tab-content active" id="revenue-tab">
            <!-- Key Metrics -->
            <div class="stats-grid">
                <div class="stat-cardd primary">
                    <div class="stat-icon">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= number_format($stats['totalRevenue'], 0, ',', '.') ?></div>
                        <div class="stat-label">Tổng Doanh Thu (VNĐ)</div>
                    </div>
                </div>

                <div class="stat-cardd success">
                    <div class="stat-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $stats['bookings']['total'] ?></div>
                        <div class="stat-label">Tổng Đặt Vé</div>
                    </div>
                </div>

                <div class="stat-cardd info">
                    <div class="stat-icon">
                        <i class="fas fa-route"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= count($stats['topRoutes']) ?></div>
                        <div class="stat-label">Tuyến Xe Hoạt Động</div>
                    </div>
                </div>

                <div class="stat-cardd warning">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= count($stats['revenueByPaymentMethod']) ?></div>
                        <div class="stat-label">Hình Thức TT</div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="charts-section">
                <!-- Monthly Revenue -->
                <div class="chart-card">
                    <div class="card-header">
                        <h3>Doanh Thu Theo Tháng (12 Tháng)</h3>
                    </div>
                    <div class="card-content">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>

                <!-- Revenue by Payment Method -->
                <div class="chart-card">
                    <div class="card-header">
                        <h3>Doanh Thu Theo Hình Thức TT</h3>
                    </div>
                    <div class="card-content">
                        <canvas id="paymentMethodChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Revenue by Route Table -->
            <div class="section-card">
                <div class="card-header">
                    <h3>Doanh Thu Chi Tiết Theo Tuyến Xe</h3>
                </div>
                <div class="card-content">
                    <div class="table-responsive">
                        <table class="stats-table">
                            <thead>
                                <tr>
                                    <th>Ký Hiệu Tuyến</th>
                                    <th>Điểm Đi</th>
                                    <th>Điểm Đến</th>
                                    <th>Vé Bán</th>
                                    <th>Doanh Thu (VNĐ)</th>
                                    <th>Giá Trung Bình</th>
                                    <th>Lợi Nhuận</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($stats['revenueByRoute'])): ?>
                                    <?php foreach ($stats['revenueByRoute'] as $route): ?>
                                        <tr>
                                            <td><span class="badge badge-primary"><?= htmlspecialchars($route['kyHieuTuyen'] ?? '') ?></span></td>
                                            <td><?= htmlspecialchars($route['diemDi'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($route['diemDen'] ?? '') ?></td>
                                            <td class="text-center"><strong><?= $route['veban'] ?? 0 ?></strong></td>
                                            <td class="amount"><?= number_format($route['doanhThu'] ?? 0, 0, ',', '.') ?></td>
                                            <td><?= number_format($route['giaTriTrungBinh'] ?? 0, 0, ',', '.') ?></td>
                                            <td class="amount-success"><?= number_format($route['loiNhuan'] ?? 0, 0, ',', '.') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" class="text-center text-muted">Không có dữ liệu</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Payment Method Table -->
            <div class="section-card">
                <div class="card-header">
                    <h3>Doanh Thu Theo Hình Thức Thanh Toán</h3>
                </div>
                <div class="card-content">
                    <div class="table-responsive">
                        <table class="stats-table">
                            <thead>
                                <tr>
                                    <th>Hình Thức</th>
                                    <th>Số Lần Sử Dụng</th>
                                    <th>Tổng Tiền (VNĐ)</th>
                                    <th>Giá Trung Bình</th>
                                    <th>% So Với Tổng</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($stats['revenueByPaymentMethod'])): ?>
                                    <?php 
                                    $totalAll = array_sum(array_column($stats['revenueByPaymentMethod'], 'totalAmount'));
                                    foreach ($stats['revenueByPaymentMethod'] as $method): 
                                    ?>
                                        <tr>
                                            <td><?= htmlspecialchars($method['paymentMethod'] ?? 'N/A') ?></td>
                                            <td class="text-center"><?= $method['bookingCount'] ?? 0 ?></td>
                                            <td class="amount"><?= number_format($method['totalAmount'] ?? 0, 0, ',', '.') ?></td>
                                            <td><?= number_format($method['avgAmount'] ?? 0, 0, ',', '.') ?></td>
                                            <td><?= $totalAll > 0 ? round(($method['totalAmount'] / $totalAll) * 100, 1) : 0 ?>%</td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center text-muted">Không có dữ liệu</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== PHẦN II: THỐNG KÊ HÀNH KHÁCH ==================== -->
        <div class="tab-content" id="customer-tab">
            <!-- Key Metrics -->
            <div class="stats-grid">
                <div class="stat-cardd success">
                    <div class="stat-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $stats['userStats']['total'] ?></div>
                        <div class="stat-label">Tổng Khách Hàng</div>
                    </div>
                </div>

                <div class="stat-cardd info">
                    <div class="stat-icon">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= count($stats['repeatCustomers']) ?></div>
                        <div class="stat-label">Khách Lặp Lại</div>
                    </div>
                </div>

                <div class="stat-cardd warning">
                    <div class="stat-icon">
                        <i class="fas fa-crown"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= !empty($stats['topCustomers'][0]['soVe']) ? $stats['topCustomers'][0]['soVe'] : 0 ?></div>
                        <div class="stat-label">Vé Top Khách/Tháng</div>
                    </div>
                </div>

                <div class="stat-cardd primary">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $stats['userStats']['active'] ?></div>
                        <div class="stat-label">Khách Hoạt Động</div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="charts-section">
                <!-- New Users by Month -->
                <div class="chart-card">
                    <div class="card-header">
                        <h3>Người Dùng Mới Đăng Ký Theo Tháng</h3>
                    </div>
                    <div class="card-content">
                        <canvas id="newUsersChart"></canvas>
                    </div>
                </div>

                <!-- Customer Distribution -->
                <div class="chart-card">
                    <div class="card-header">
                        <h3>Phân Bố Người Dùng</h3>
                    </div>
                    <div class="card-content">
                        <canvas id="userDistributionChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Customers Table -->
            <div class="section-card">
                <div class="card-header">
                    <h3>Top 10 Khách Hàng Mua Nhiều Vé Nhất</h3>
                </div>
                <div class="card-content">
                    <div class="table-responsive">
                        <table class="stats-table">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Họ Tên</th>
                                    <th>Số Điện Thoại</th>
                                    <th>Số Vé Đã Mua</th>
                                    <th>Tổng Chi Tiêu (VNĐ)</th>
                                    <th>Lần Đặt Cuối</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($stats['topCustomers'])): ?>
                                    <?php foreach ($stats['topCustomers'] as $index => $customer): ?>
                                        <tr>
                                            <td><strong><?= $index + 1 ?></strong></td>
                                            <td><?= htmlspecialchars($customer['hoTen'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($customer['soDienThoai'] ?? '') ?></td>
                                            <td class="text-center"><span class="badge badge-info"><?= $customer['soVe'] ?? 0 ?></span></td>
                                            <td class="amount"><?= number_format($customer['tongTien'] ?? 0, 0, ',', '.') ?></td>
                                            <td><?= !empty($customer['lanDatCuoi']) ? date('d/m/Y H:i', strtotime($customer['lanDatCuoi'])) : 'N/A' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center text-muted">Không có dữ liệu</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Repeat Customers Table -->
            <div class="section-card">
                <div class="card-header">
                    <h3>Khách Hàng Lặp Lại (Quay Lại 2+ Lần)</h3>
                </div>
                <div class="card-content">
                    <div class="table-responsive">
                        <table class="stats-table">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Họ Tên</th>
                                    <th>Số Lần Đặt</th>
                                    <th>Tổng Chi Tiêu (VNĐ)</th>
                                    <th>Trung Bình/Lần</th>
                                    <th>Lần Đặt Gần Nhất</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($stats['repeatCustomers'])): ?>
                                    <?php foreach ($stats['repeatCustomers'] as $index => $customer): ?>
                                        <tr>
                                            <td><strong><?= $index + 1 ?></strong></td>
                                            <td><?= htmlspecialchars($customer['hoTen'] ?? '') ?></td>
                                            <td class="text-center"><span class="badge badge-success"><?= $customer['soLanDat'] ?? 0 ?></span></td>
                                            <td class="amount"><?= number_format($customer['tongChiTieu'] ?? 0, 0, ',', '.') ?></td>
                                            <td><?= number_format($customer['chiTieuTrungBinh'] ?? 0, 0, ',', '.') ?></td>
                                            <td><?= !empty($customer['lanDatCuoi']) ? date('d/m/Y', strtotime($customer['lanDatCuoi'])) : 'N/A' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center text-muted">Không có dữ liệu</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== PHẦN III: THỐNG KÊ CHUYẾN XE ==================== -->
        <div class="tab-content" id="trip-tab">
            <!-- Key Metrics -->
            <div class="stats-grid">
                <div class="stat-cardd info">
                    <div class="stat-icon">
                        <i class="fas fa-bus"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $stats['tripStats']['total'] ?></div>
                        <div class="stat-label">Tổng Chuyến Xe</div>
                    </div>
                </div>

                <div class="stat-cardd success">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $stats['tripStats']['completed'] ?></div>
                        <div class="stat-label">Chuyến Hoàn Thành</div>
                    </div>
                </div>

                <div class="stat-cardd warning">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $stats['tripStats']['delayed'] ?></div>
                        <div class="stat-label">Chuyến Delay</div>
                    </div>
                </div>

                <div class="stat-cardd danger">
                    <div class="stat-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $stats['tripStats']['cancelled'] ?></div>
                        <div class="stat-label">Chuyến Bị Hủy</div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="charts-section">
                <!-- Trip Status -->
                <div class="chart-card">
                    <div class="card-header">
                        <h3>Trạng Thái Chuyến Xe</h3>
                    </div>
                    <div class="card-content">
                        <canvas id="tripStatusChart"></canvas>
                    </div>
                </div>

                <!-- Top Routes by Booking -->
                <div class="chart-card">
                    <div class="card-header">
                        <h3>Top Tuyến (Đặt Nhiều Nhất)</h3>
                    </div>
                    <div class="card-content">
                        <canvas id="topRoutesChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Trip Load Factor Table -->
            <div class="section-card">
                <div class="card-header">
                    <h3>Tỷ Lệ Lấp Đầy Chuyến Xe</h3>
                </div>
                <div class="card-content">
                    <div class="table-responsive">
                        <table class="stats-table" id="tripLoadFactorTable">
                            <thead>
                                <tr>
                                    <th>Tuyến</th>
                                    <th>Ngày Khởi Hành</th>
                                    <th>Giờ Khởi Hành</th>
                                    <th>Tổng Ghế</th>
                                    <th>Ghế Có Người</th>
                                    <th>Tỷ Lệ Lấp Đầy</th>
                                </tr>
                            </thead>
                            <tbody id="tripLoadFactorBody">
                                <!-- Table body will be populated by AJAX -->
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Updated pagination to use AJAX without page reload -->
                    <div class="pagination-controls" id="tripLoadPaginationControls">
                        <!-- Pagination will be populated by AJAX -->
                    </div>
                </div>
            </div>

            <!-- Average Ticket Stats -->
            <div class="detail-cards">
                <div class="detail-card">
                    <div class="detail-icon"><i class="fas fa-ticket-alt"></i></div>
                    <div class="detail-info">
                        <div class="detail-label">Trung Bình Vé/Chuyến</div>
                        <div class="detail-value"><?= !empty($stats['averageTicketPerTrip']['avgTickets']) ? number_format($stats['averageTicketPerTrip']['avgTickets'], 1) : 0 ?> vé</div>
                    </div>
                </div>
                <div class="detail-card">
                    <div class="detail-icon"><i class="fas fa-dollar-sign"></i></div>
                    <div class="detail-info">
                        <div class="detail-label">Trung Bình Doanh Thu/Chuyến</div>
                        <div class="detail-value"><?= !empty($stats['averageTicketPerTrip']['avgRevenue']) ? number_format($stats['averageTicketPerTrip']['avgRevenue'], 0, ',', '.') : 0 ?> VNĐ</div>
                    </div>
                </div>
            </div>

            <!-- Top Routes by Booking Table -->
            <div class="section-card">
                <div class="card-header">
                    <h3>Tuyến Xe Được Đặt Nhiều Nhất</h3>
                </div>
                <div class="card-content">
                    <div class="table-responsive">
                        <table class="stats-table">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Ký Hiệu</th>
                                    <th>Điểm Đi</th>
                                    <th>Điểm Đến</th>
                                    <th>Số Chuyến</th>
                                    <th>Tổng Đặt Vé</th>
                                    <th>Tổng Doanh Thu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($stats['topRoutesByBooking'])): ?>
                                    <?php foreach ($stats['topRoutesByBooking'] as $index => $route): ?>
                                        <tr>
                                            <td><strong><?= $index + 1 ?></strong></td>
                                            <td><span class="badge badge-primary"><?= htmlspecialchars($route['kyHieuTuyen'] ?? '') ?></span></td>
                                            <td><?= htmlspecialchars($route['diemDi'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($route['diemDen'] ?? '') ?></td>
                                            <td class="text-center"><?= $route['totalTrips'] ?? 0 ?></td>
                                            <td class="text-center"><strong><?= $route['totalBookings'] ?? 0 ?></strong></td>
                                            <td class="amount"><?= number_format($route['totalRevenue'] ?? 0, 0, ',', '.') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" class="text-center text-muted">Không có dữ liệu</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Chart Scripts -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab switching
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', function() {
                const tabName = this.getAttribute('data-tab');
                
                document.querySelectorAll('.tab-button').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                this.classList.add('active');
                document.getElementById(tabName + '-tab').classList.add('active');
            });
        });

        // Monthly Revenue Chart
        const monthlyData = <?= json_encode($stats['monthlyRevenue']) ?>;
        if (monthlyData.length > 0) {
            const months = monthlyData.map(d => d.month);
            const revenues = monthlyData.map(d => parseFloat(d.revenue) / 1000000);
            
            new Chart(document.getElementById('revenueChart'), {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Doanh Thu (Triệu VNĐ)',
                        data: revenues,
                        borderColor: '#f4481f',
                        backgroundColor: 'rgba(244, 72, 31, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 5,
                        pointBackgroundColor: '#f4481f'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: true } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }

        // Payment Method Chart
        const paymentData = <?= json_encode($stats['revenueByPaymentMethod']) ?>;
        if (paymentData.length > 0) {
            const methods = paymentData.map(d => d.paymentMethod);
            const amounts = paymentData.map(d => parseFloat(d.totalAmount) / 1000000);
            
            new Chart(document.getElementById('paymentMethodChart'), {
                type: 'doughnut',
                data: {
                    labels: methods,
                    datasets: [{
                        data: amounts,
                        backgroundColor: ['#f4481f', '#10b981', '#3b82f6', '#f59e0b', '#8b5cf6']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }

        // New Users Chart
        const newUsersData = <?= json_encode($stats['newUsersByMonth']) ?>;
        if (newUsersData.length > 0) {
            const months = newUsersData.map(d => d.monthDisplay);
            const counts = newUsersData.map(d => d.count);
            
            new Chart(document.getElementById('newUsersChart'), {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Người Dùng Mới',
                        data: counts,
                        backgroundColor: '#10b981'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: true } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }

        // User Distribution Chart
        const userStats = <?= json_encode($stats['userStats']) ?>;
        new Chart(document.getElementById('userDistributionChart'), {
            type: 'pie',
            data: {
                labels: ['Khách Hàng', 'Tài Xế', 'Nhân Viên'],
                datasets: [{
                    data: [
                        userStats.customers || 0,
                        userStats.drivers || 0,
                        userStats.staff || 0
                    ],
                    backgroundColor: ['#10b981', '#f59e0b', '#3b82f6']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        // Trip Status Chart
        const tripStats = <?= json_encode($stats['tripStats']) ?>;
        new Chart(document.getElementById('tripStatusChart'), {
            type: 'doughnut',
            data: {
                labels: ['Sẵn Sàng', 'Hoàn Thành', 'Bị Hủy', 'Delay'],
                datasets: [{
                    data: [
                        tripStats.ready || 0,
                        tripStats.completed || 0,
                        tripStats.cancelled || 0,
                        tripStats.delayed || 0
                    ],
                    backgroundColor: ['#3b82f6', '#10b981', '#ef4444', '#f59e0b']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        // Top Routes Chart
        const topRoutes = <?= json_encode($stats['topRoutesByBooking']) ?>;
        if (topRoutes.length > 0) {
            const labels = topRoutes.map(r => r.kyHieuTuyen);
            const bookings = topRoutes.map(r => r.totalBookings);
            
            new Chart(document.getElementById('topRoutesChart'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Số Đặt Vé',
                        data: bookings,
                        backgroundColor: '#f4481f'
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: true } },
                    scales: { x: { beginAtZero: true } }
                }
            });
        }
        
        loadTripLoadFactorData(1);
    });
</script>

<!-- Sidebar toggle script -->
<script>
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
    }

    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            if (sidebar && !sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        }
    });
</script>

<!-- AJAX functionality for Trip Load Factor pagination -->
<script>
    let currentTripLoadPage = 1;
    
    function loadTripLoadFactorData(page = 1) {
        currentTripLoadPage = page;
        const tableBody = document.getElementById('tripLoadFactorBody');
        const paginationControls = document.getElementById('tripLoadPaginationControls');
        
        // Show loading state
        tableBody.innerHTML = '<tr><td colspan="6" class="text-center"><i class="fas fa-spinner fa-spin"></i> Đang tải...</td></tr>';
        
        fetch('<?= BASE_URL ?>/admin/trip-load-factor-ajax?page=' + page)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Populate table rows
                    if (data.data.length > 0) {
                        tableBody.innerHTML = data.data.map(trip => `
                            <tr>
                                <td><span class="badge badge-primary">${escapeHtml(trip.kyHieuTuyen || '')}</span></td>
                                <td>${trip.ngayKhoiHanh ? new Date(trip.ngayKhoiHanh).toLocaleDateString('vi-VN') : 'N/A'}</td>
                                <td>${trip.gioKhoiHanh ? formatTime(trip.gioKhoiHanh) : 'N/A'}</td>
                                <td class="text-center">${trip.soChoTong || 0}</td>
                                <td class="text-center">${trip.soChoCoNguoi || 0}</td>
                                <td>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: ${trip.tyLeLapDay || 0}%"></div>
                                    </div>
                                    <small>${parseFloat(trip.tyLeLapDay || 0).toFixed(1)}%</small>
                                </td>
                            </tr>
                        `).join('');
                    } else {
                        tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Không có dữ liệu</td></tr>';
                    }
                    
                    // Populate pagination controls
                    renderPaginationControls(data.pagination, paginationControls);
                    
                    // Scroll to top of table
                    document.getElementById('tripLoadFactorTable').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                } else {
                    tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Lỗi: ' + (data.message || 'Không thể tải dữ liệu') + '</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Lỗi kết nối: ' + error.message + '</td></tr>';
            });
    }
    
    function renderPaginationControls(pagination, container) {
        if (pagination.totalPages <= 1) {
            container.innerHTML = '';
            return;
        }
        
        let html = `
            <div class="pagination-info">
                Trang ${pagination.currentPage} / ${pagination.totalPages} (Tổng: ${pagination.total} chuyến)
            </div>
            <ul class="pagination">
        `;
        
        if (pagination.currentPage > 1) {
            html += `
                <li><a href="javascript:void(0);" onclick="loadTripLoadFactorData(1)" class="btn-page"><i class="fas fa-chevron-left"></i> Đầu</a></li>
                <li><a href="javascript:void(0);" onclick="loadTripLoadFactorData(${pagination.currentPage - 1})" class="btn-page"><i class="fas fa-chevron-left"></i></a></li>
            `;
        }
        
        const startPage = Math.max(1, pagination.currentPage - 2);
        const endPage = Math.min(pagination.totalPages, pagination.currentPage + 2);
        
        for (let i = startPage; i <= endPage; i++) {
            html += `
                <li>
                    <a href="javascript:void(0);" onclick="loadTripLoadFactorData(${i})" class="btn-page ${i === pagination.currentPage ? 'active' : ''}">
                        ${i}
                    </a>
                </li>
            `;
        }
        
        if (pagination.currentPage < pagination.totalPages) {
            html += `
                <li><a href="javascript:void(0);" onclick="loadTripLoadFactorData(${pagination.currentPage + 1})" class="btn-page"><i class="fas fa-chevron-right"></i></a></li>
                <li><a href="javascript:void(0);" onclick="loadTripLoadFactorData(${pagination.totalPages})" class="btn-page">Cuối <i class="fas fa-chevron-right"></i></a></li>
            `;
        }
        
        html += `</ul>`;
        container.innerHTML = html;
    }
    
    function formatTime(timeString) {
        if (!timeString) return 'N/A';
        const parts = timeString.split(':');
        if (parts.length >= 2) {
            return parts[0] + ':' + parts[1];
        }
        return timeString;
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>
</body>
</html>