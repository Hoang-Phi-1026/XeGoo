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
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/admin-dashboard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/admin-statistics.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="admin-layout">
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>



    <!-- Main Content -->
    <main class="main-content">
        <header class="page-header">
            <div class="page-img">
                <img src="<?= BASE_URL ?>/public/uploads/images/logo-dark.png" alt="XeGoo Logo" class="logo-img">
            </div>
            <div class="page-title">
                <h1>Thống Kê</h1>
                <p>Báo cáo chi tiết hoạt động của hệ thống</p>
            </div>
        </header>

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
                <div class="stat-card primary">
                    <div class="stat-icon">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= number_format($stats['totalRevenue'], 0, ',', '.') ?></div>
                        <div class="stat-label">Tổng Doanh Thu (VNĐ)</div>
                    </div>
                </div>

                <div class="stat-card success">
                    <div class="stat-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $stats['bookings']['total'] ?></div>
                        <div class="stat-label">Tổng Đặt Vé</div>
                    </div>
                </div>

                <div class="stat-card info">
                    <div class="stat-icon">
                        <i class="fas fa-route"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= count($stats['topRoutes']) ?></div>
                        <div class="stat-label">Tuyến Xe Hoạt Động</div>
                    </div>
                </div>

                <div class="stat-card warning">
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

            <!-- Khách Hàng Mua Vé section moved here -->
            <div class="section-card">
                <div class="card-header">
                    <h3>Khách Hàng Mua Vé Hôm Nay</h3>
                </div>
                <div class="card-content">
                    <!-- Date picker to filter ticket sales by date -->
                    <div class="filter-section" style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center;">
                        <label for="ticketSalesDatePicker" style="font-weight: bold;">Chọn Ngày:</label>
                        <input type="date" id="ticketSalesDatePicker" class="form-control" value="<?php echo date('Y-m-d'); ?>" style="max-width: 200px;">
                        <button id="filterTicketSalesBtn" class="btn btn-primary" style="padding: 8px 16px; cursor: pointer;">
                            <i class="fas fa-search"></i> Tìm Kiếm
                        </button>
                    </div>
                    
                    <div id="todayTicketSalesTableContainer">
                        <table class="data-table" id="todayTicketSalesTable">
                            <thead>
                                <tr>
                                    <th>Tên Khách Hàng</th>
                                    <th>Số Điện Thoại</th>
                                    <th>Email</th>
                                    <th>Tuyến Xe</th>
                                    <th>Ngày Khởi Hành</th>
                                    <th>Số Vé</th>
                                    <th>Loại vé</th>
                                    <th>Tổng Tiền</th>
                                    <th>Thanh Toán</th>
                                    <th>Trạng Thái</th>
                                </tr>
                            </thead>
                            <tbody id="todayTicketSalesTableBody">
                                <!-- AJAX will populate this -->
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination controls -->
                    <div class="pagination" id="todayTicketSalesPagination">
                        <!-- AJAX will populate this -->
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== PHẦN II: THỐNG KÊ HÀNH KHÁCH ==================== -->
        <div class="tab-content" id="customer-tab">
            <!-- Key Metrics -->
            <div class="stats-grid">
                <div class="stat-card success">
                    <div class="stat-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $stats['userStats']['total'] ?></div>
                        <div class="stat-label">Tổng Khách Hàng</div>
                    </div>
                </div>

                <div class="stat-card info">
                    <div class="stat-icon">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= count($stats['repeatCustomers']) ?></div>
                        <div class="stat-label">Khách Lặp Lại</div>
                    </div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-icon">
                        <i class="fas fa-crown"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= !empty($stats['topCustomers'][0]['soVe']) ? $stats['topCustomers'][0]['soVe'] : 0 ?></div>
                        <div class="stat-label">Vé Top Khách/Tháng</div>
                    </div>
                </div>

                <div class="stat-card primary">
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
                <div class="stat-card info">
                    <div class="stat-icon">
                        <i class="fas fa-bus"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $stats['tripStats']['total'] ?></div>
                        <div class="stat-label">Tổng Chuyến Xe</div>
                    </div>
                </div>

                <div class="stat-card success">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $stats['tripStats']['completed'] ?></div>
                        <div class="stat-label">Chuyến Hoàn Thành</div>
                    </div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $stats['tripStats']['delayed'] ?></div>
                        <div class="stat-label">Chuyến Delay</div>
                    </div>
                </div>

                <div class="stat-card danger">
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

        <!-- ==================== PHẦN IV: THỐNG KÊ BÁN VÉ TRONG NGÀY ==================== -->
        <!-- This section has been moved to the Revenue Statistics tab -->

        <!-- Add some basic styling for the date filter -->
        <style>
            .filter-section {
                padding: 15px;
                background-color: #f8f9fa;
                border-radius: 5px;
                border-left: 4px solid #f4481f;
            }
            
            .filter-section input[type="date"] {
                padding: 8px 12px;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 14px;
            }
            
            .btn-primary {
                background-color: #f4481f;
                color: white;
                border: none;
                border-radius: 4px;
            }
            
            .btn-primary:hover {
                background-color: #d63612;
            }
        </style>
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

        // Load today's ticket sales data on page load
        loadTodayTicketSalesData(1);

        // Event listener for the date filter button
        document.getElementById('filterTicketSalesBtn').addEventListener('click', function() {
            const selectedDate = document.getElementById('ticketSalesDatePicker').value;
            filterTicketSalesByDate(selectedDate);
        });
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
        tableBody.innerHTML = '<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Đang tải...</td></tr>';
        
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
                        tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Không có dữ liệu</td></tr>';
                    }
                    
                    // Populate pagination controls
                    renderPaginationControls(data.pagination, paginationControls);
                    
                    // Scroll to top of table
                    document.getElementById('tripLoadFactorTable').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                } else {
                    tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Lỗi: ' + (data.message || 'Không thể tải dữ liệu') + '</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Lỗi kết nối: ' + error.message + '</td></tr>';
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
<!-- AJAX functionality for Today's Ticket Sales pagination -->
<script>
    function loadTodayTicketSalesData(page) {
        fetch('<?= BASE_URL ?>/admin/today-ticket-sales-ajax?page=' + page)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderTodayTicketSalesTable(data.data);
                    renderTodayTicketSalesPagination(data.pagination);
                    // Scroll to table
                    document.getElementById('todayTicketSalesTable').scrollIntoView({ behavior: 'smooth' });
                } else {
                    console.error('Error:', data.message);
                    document.getElementById('todayTicketSalesTableBody').innerHTML = '<tr><td colspan="9" style="text-align: center; color: red;">Lỗi khi tải dữ liệu: ' + (data.message || 'Lỗi không xác định') + '</td></tr>';
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                document.getElementById('todayTicketSalesTableBody').innerHTML = '<tr><td colspan="9" style="text-align: center; color: red;">Lỗi kết nối: ' + error.message + '</td></tr>';
            });
    }

    // New function to filter ticket sales by date
    function filterTicketSalesByDate(date) {
        fetch(`<?= BASE_URL ?>/admin/today-ticket-sales-ajax?date=${date}&page=1`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderTodayTicketSalesTable(data.data);
                    renderTodayTicketSalesPagination(data.pagination);
                    document.getElementById('todayTicketSalesTable').scrollIntoView({ behavior: 'smooth' });
                } else {
                    console.error('Error filtering:', data.message);
                    document.getElementById('todayTicketSalesTableBody').innerHTML = `<tr><td colspan="9" style="text-align: center; color: red;">Lỗi khi lọc dữ liệu: ${data.message || 'Lỗi không xác định'}</td></tr>`;
                }
            })
            .catch(error => {
                console.error('Fetch error during filter:', error);
                document.getElementById('todayTicketSalesTableBody').innerHTML = `<tr><td colspan="9" style="text-align: center; color: red;">Lỗi kết nối: ${error.message}</td></tr>`;
            });
    }


    function renderTodayTicketSalesTable(data) {
        const tableBody = document.getElementById('todayTicketSalesTableBody');
        tableBody.innerHTML = '';

        if (data.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="10" style="text-align: center;">Không có khách hàng mua vé vào ngày này</td></tr>';
            return;
        }

        data.forEach(row => {
            const tr = document.createElement('tr');
            const ngayKhoiHanh = row.ngayKhoiHanh ? new Date(row.ngayKhoiHanh).toLocaleDateString('vi-VN') : 'N/A';

            let statusBadge = '';
            if (row.trangThai === 'DaThanhToan') {
                statusBadge = '<span class="badge badge-success">Đã Thanh Toán</span>';
            } else if (row.trangThai === 'DaHoanThanh') {
                statusBadge = '<span class="badge badge-info">Hoàn Thành</span>';
            } else if (row.trangThai === 'DaHuy') {
                statusBadge = '<span class="badge badge-danger">Đã Hủy</span>';
            } else if (row.trangThai === 'ChoThanhToan') {
                statusBadge = '<span class="badge badge-warning">Chờ Thanh Toán</span>';
            } else {
                statusBadge = `<span class="badge badge-secondary">${row.trangThai}</span>`;
            }
            
            let paymentMethod = row.phuongThucThanhToan;
            if (paymentMethod === 'MoMo') {
                paymentMethod = 'MoMo';
            } else if (paymentMethod === 'VNPay') {
                paymentMethod = 'VNPay';
            } else {
                paymentMethod = 'Khác';
            }

            let loaiDatVeDisplay = '';
            if (row.loaiDatVe === 'MotChieu') {
                loaiDatVeDisplay = '<span class="badge" style="background-color: #3b82f6;">Một Chiều</span>';
            } else if (row.loaiDatVe === 'KhuHoi') {
                loaiDatVeDisplay = '<span class="badge" style="background-color: #10b981;">Khứ Hồi</span>';
            } else {
                loaiDatVeDisplay = '<span class="badge" style="background-color: #6b7280;">Khác</span>';
            }
            
            tr.innerHTML = `
                <td><strong>${escapeHtml(row.hoTen || '')}</strong></td>
                <td>${escapeHtml(row.soDienThoai || '')}</td>
                <td>${escapeHtml(row.eMail || '')}</td>
                <td>${escapeHtml(row.kyHieuTuyen || '')}</td>
                <td>${ngayKhoiHanh}</td>
                <td><strong>${row.soVe || 0}</strong></td>
                <td>${loaiDatVeDisplay}</td>
                <td><strong class="text-success">${new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(row.tongTienSauGiam || 0)}</strong></td>
                <td>${paymentMethod}</td>
                <td>${statusBadge}</td>
            `;
            tableBody.appendChild(tr);
        });
    }


    function renderTodayTicketSalesPagination(pagination) {
        const paginationDiv = document.getElementById('todayTicketSalesPagination');
        paginationDiv.innerHTML = '';

        if (pagination.totalPages <= 1) return;

        const currentPage = pagination.currentPage;
        const totalPages = pagination.totalPages;
        const totalItems = pagination.total;

        // First page button
        if (currentPage > 1) {
            const firstBtn = document.createElement('button');
            firstBtn.textContent = 'Đầu';
            firstBtn.className = 'pagination-btn';
            firstBtn.onclick = () => loadTodayTicketSalesData(1);
            paginationDiv.appendChild(firstBtn);

            // Previous page button
            const prevBtn = document.createElement('button');
            prevBtn.textContent = 'Trước';
            prevBtn.className = 'pagination-btn';
            prevBtn.onclick = () => loadTodayTicketSalesData(currentPage - 1);
            paginationDiv.appendChild(prevBtn);
        }

        // Page numbers
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);

        for (let i = startPage; i <= endPage; i++) {
            const pageBtn = document.createElement('button');
            pageBtn.textContent = i;
            pageBtn.className = i === currentPage ? 'pagination-btn active' : 'pagination-btn';
            pageBtn.onclick = () => loadTodayTicketSalesData(i);
            paginationDiv.appendChild(pageBtn);
        }

        // Next page button
        if (currentPage < totalPages) {
            const nextBtn = document.createElement('button');
            nextBtn.textContent = 'Sau';
            nextBtn.className = 'pagination-btn';
            nextBtn.onclick = () => loadTodayTicketSalesData(currentPage + 1);
            paginationDiv.appendChild(nextBtn);

            // Last page button
            const lastBtn = document.createElement('button');
            lastBtn.textContent = 'Cuối';
            lastBtn.className = 'pagination-btn';
            lastBtn.onclick = () => loadTodayTicketSalesData(totalPages);
            paginationDiv.appendChild(lastBtn);
        }

        // Info text
        const infoDiv = document.createElement('div');
        infoDiv.className = 'pagination-info';
        infoDiv.textContent = `Trang ${currentPage} / ${totalPages} (Tổng: ${totalItems} đơn hàng)`;
        paginationDiv.appendChild(infoDiv);
    }
    
    // Helper function for HTML escaping
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>
</body>
</html>
