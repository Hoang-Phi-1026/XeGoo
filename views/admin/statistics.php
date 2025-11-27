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

        <!-- Add tabs navigation for main sections -->
        <div class="stats-tabs">
            <button class="tab-button active" data-tab="revenue">
                <i class="fas fa-coins"></i> Thống kê doanh thu
            </button>
            <button class="tab-button" data-tab="customer">
                <i class="fas fa-users"></i> Thống kê hành khách
            </button>
            <button class="tab-button" data-tab="trip">
                <i class="fas fa-bus"></i> Thống kê chuyến xe
            </button>
            <button class="tab-button" data-tab="driver">
                <i class="fas fa-user-tie"></i> Doanh thu tài xế
            </button>
        </div>

        <!-- ==================== PHẦN I: THỐNG KÊ DOANH THU ==================== -->
        <div class="tab-content active" id="revenue-tab">
            <!-- Filter section for revenue statistics by date/month/year -->
            <div class="filter-section" style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: flex-end;">
                    <div class="form-group">
                        <label for="revenueStartDate" style="display: block; font-weight: 600; margin-bottom: 8px;">Từ Ngày:</label>
                        <input type="date" id="revenueStartDate" class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    <div class="form-group">
                        <label for="revenueEndDate" style="display: block; font-weight: 600; margin-bottom: 8px;">Đến Ngày:</label>
                        <input type="date" id="revenueEndDate" class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    <div class="form-group">
                        <label for="filterType" style="display: block; font-weight: 600; margin-bottom: 8px;">Kiểu Lọc:</label>
                        <select id="filterType" class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="day">Theo Ngày</option>
                            <option value="month">Theo Tháng</option>
                            <option value="year">Theo Năm</option>
                        </select>
                    </div>
                    <button id="applyRevenueFilter" class="btn btn-primary" style="width: 100%; padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">Lọc</button>
                </div>
            </div>

            <!-- Revenue summary statistics cards (removed duplicate stat cards) -->
            <div class="stats-summary" id="revenueSummary" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
                <div class="stat-card info">
                    <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
                    <div class="stat-content">
                        <div class="stat-number" id="totalRevenueDisplay">0</div>
                        <div class="stat-label">Tổng Doanh Thu (VNĐ)</div>
                    </div>
                </div>
                <div class="stat-card success">
                    <div class="stat-icon"><i class="fas fa-ticket-alt"></i></div>
                    <div class="stat-content">
                        <div class="stat-number" id="totalTicketsDisplay">0</div>
                        <div class="stat-label">Số Giao Dịch</div>
                    </div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-content">
                        <div class="stat-number" id="totalCustomersDisplay">0</div>
                        <div class="stat-label">Số Khách Hàng</div>
                    </div>
                </div>
                <div class="stat-card primary">
                    <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                    <div class="stat-content">
                        <div class="stat-number" id="avgRevenueDisplay">0</div>
                        <div class="stat-label">Giá Trung Bình (VNĐ)</div>
                    </div>
                </div>
            </div>

            <!-- Filtered revenue chart -->
            <div class="chart-card">
                <div class="card-header">
                    <h3>Biểu Đồ Doanh Thu Theo Lọc</h3>
                </div>
                <div class="card-content">
                    <canvas id="filteredRevenueChart" height="80"></canvas>
                </div>
            </div>

            <!-- Add new detailed revenue table with month filter -->
            <div class="section-card">
                <div class="card-header">
                    <h3>Chi Tiết Doanh Thu</h3>
                </div>
                <div class="card-content">
                    <div class="filter-section" style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; align-items: flex-end;">
                        <div>
                            <label for="detailedRevenueStartDate" style="display: block; font-weight: 600; margin-bottom: 8px;">Từ Ngày:</label>
                            <input type="date" id="detailedRevenueStartDate" class="form-control" value="<?= date('Y-m-01') ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
                        <div>
                            <label for="detailedRevenueEndDate" style="display: block; font-weight: 600; margin-bottom: 8px;">Đến Ngày:</label>
                            <input type="date" id="detailedRevenueEndDate" class="form-control" value="<?= date('Y-m-d') ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
                        <div>
                            <button id="filterDetailedRevenueBtn" class="btn btn-primary" style="width: 100%; padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">
                                <i class="fas fa-filter"></i> Lọc
                            </button>
                        </div>
                    </div>

                    <!-- Summary cards for detailed revenue -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
                        <div style="background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107;">
                            <div style="font-size: 14px; color: #666; margin-bottom: 5px;">Tổng Tiền Gộp</div>
                            <div id="summaryTongGop" style="font-size: 20px; font-weight: bold; color: #333;">0 VNĐ</div>
                        </div>
                        <div style="background: #f8d7da; padding: 15px; border-radius: 8px; border-left: 4px solid #dc3545;">
                            <div style="font-size: 14px; color: #666; margin-bottom: 5px;">Giảm Trừ</div>
                            <div id="summaryTongGiam" style="font-size: 20px; font-weight: bold; color: #333;">0 VNĐ</div>
                        </div>
                        <div style="background: #d4edda; padding: 15px; border-radius: 8px; border-left: 4px solid #28a745;">
                            <div style="font-size: 14px; color: #666; margin-bottom: 5px;">Doanh Thu Thực Tế</div>
                            <div id="summaryTongThucTe" style="font-size: 20px; font-weight: bold; color: #333;">0 VNĐ</div>
                        </div>
                        <div style="background: #cce5ff; padding: 15px; border-radius: 8px; border-left: 4px solid #0066cc;">
                            <div style="font-size: 14px; color: #666; margin-bottom: 5px;">Số Giao Dịch</div>
                            <div id="summaryGiaoDich" style="font-size: 20px; font-weight: bold; color: #333;">0</div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="stats-table" id="detailedRevenueTable">
                            <thead>
                                <tr>
                                    <th>Mã Đặt Vé</th>
                                    <th>Ngày</th>
                                    <th>Khách Hàng</th>
                                    <th>Số ĐT</th>
                                    <th>Tuyến Xe</th>
                                    <th>Số Vé</th>
                                    <th>Tổng Tiền (VNĐ)</th>
                                    <th>Giảm Trừ (VNĐ)</th>
                                    <th>Doanh Thu Thực Tế (VNĐ)</th>
                                    <th>P.T Thanh Toán</th>
                                    <th>Trạng Thái</th>
                                </tr>
                            </thead>
                            <tbody id="detailedRevenueTableBody">
                                <tr><td colspan="11" class="text-center text-muted" style="padding: 20px;">Nhấn "Lọc" để xem dữ liệu</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div id="detailedRevenuePagination" style="display: flex; justify-content: center; gap: 10px; margin-top: 20px; flex-wrap: wrap;">
                        <!-- Pagination buttons will be inserted here -->
                    </div>
                </div>
            </div>

            <!-- Revenue by Route Table -->
            <div class="section-card">
                <div class="card-header">
                    <h3>Doanh Thu Chi Tiết Theo Tuyến Xe</h3>
                </div>
                <div class="card-content">
                    <!-- Add month/year filter section -->
                    <div class="filter-section" style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; align-items: flex-end;">
                        <div>
                            <label for="revenueDetailMonth" style="display: block; font-weight: 600; margin-bottom: 8px;">Tháng:</label>
                            <input type="number" id="revenueDetailMonth" class="form-control" min="1" max="12" value="<?= date('m') ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
                        <div>
                            <label for="revenueDetailYear" style="display: block; font-weight: 600; margin-bottom: 8px;">Năm:</label>
                            <input type="number" id="revenueDetailYear" class="form-control" value="<?= date('Y') ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
                        <div>
                            <button id="filterRevenueDetailBtn" class="btn btn-primary" style="width: 100%; padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">
                                <i class="fas fa-filter"></i> Lọc
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="stats-table">
                            <thead>
                                <tr>
                                    <th>Ký Hiệu Tuyến</th>
                                    <th>Điểm Đi</th>
                                    <th>Điểm Đến</th>
                                    <th>Số Giao Dịch</th>
                                    <th>Số Lượng Vé</th>
                                    <th>Doanh Thu (VNĐ)</th>
                                    <th>Giá Trung Bình</th>
                                    <th>Lợi Nhuận</th>
                                </tr>
                            </thead>
                            <tbody id="revenueDetailBody">
                                <?php if (!empty($stats['revenueByRoute'])): ?>
                                    <?php foreach ($stats['revenueByRoute'] as $route): ?>
                                        <tr>
                                            <td><span class="badge badge-primary"><?= htmlspecialchars($route['kyHieuTuyen'] ?? '') ?></span></td>
                                            <td><?= htmlspecialchars($route['diemDi'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($route['diemDen'] ?? '') ?></td>
                                            <td class="text-center"><strong><?= $route['soGiaoDich'] ?? 0 ?></strong></td>
                                            <td class="text-center"><strong><?= $route['soLuongVe'] ?? 0 ?></strong></td>
                                            <td class="amount"><?= number_format($route['doanhThu'] ?? 0, 0, ',', '.') ?></td>
                                            <td><?= number_format($route['giaTriTrungBinh'] ?? 0, 0, ',', '.') ?></td>
                                            <td class="amount-success"><?= number_format($route['loiNhuan'] ?? 0, 0, ',', '.') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="8" class="text-center text-muted">Không có dữ liệu</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Add script to handle month filter -->
                    <script>
                        document.getElementById('filterRevenueDetailBtn').addEventListener('click', function() {
                            loadRevenueDetail();
                        });

                        function loadRevenueDetail() {
                            const month = document.getElementById('revenueDetailMonth').value;
                            const year = document.getElementById('revenueDetailYear').value;
                            
                            if (!month || !year) {
                                alert('Vui lòng chọn tháng và năm');
                                return;
                            }

                             fetch('<?= BASE_URL ?>/admin/revenue-by-route-ajax', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    month: month,
                                    year: year
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                const tbody = document.getElementById('revenueDetailBody');
                                tbody.innerHTML = '';

                                if (data.success && data.data.length > 0) {
                                    data.data.forEach(route => {
                                        const row = document.createElement('tr');
                                        row.innerHTML = `
                                            <td><span class="badge badge-primary">${route.kyHieuTuyen}</span></td>
                                            <td>${route.diemDi}</td>
                                            <td>${route.diemDen}</td>
                                            <td class="text-center"><strong>${route.soGiaoDich}</strong></td>
                                            <td class="text-center"><strong>${route.soLuongVe}</strong></td>
                                            <td class="amount">${new Intl.NumberFormat('vi-VN').format(route.doanhThu)}</td>
                                            <td>${new Intl.NumberFormat('vi-VN').format(route.giaTriTrungBinh)}</td>
                                            <td class="amount-success">${new Intl.NumberFormat('vi-VN').format(route.loiNhuan)}</td>
                                        `;
                                        tbody.appendChild(row);
                                    });
                                } else {
                                    tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Không có dữ liệu</td></tr>';
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('Lỗi khi tải dữ liệu');
                            });
                        }
                    </script>
                </div>
            </div>


            <!-- Revenue by Payment Method Chart -->
            <div class="chart-card">
                <div class="card-header">
                    <h3>Biểu đồ Theo Hình Thức Thanh Toán</h3>
                </div>
                <div class="card-content">
                    <canvas id="paymentMethodChart"></canvas>
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


            

            <!-- Add new section for transaction statistics with month filter -->
            <div class="section-card">
                <div class="card-header">
                    <h3>Thống Kê Giao Dịch</h3>
                </div>
                <div class="card-content">
                    <div class="filter-section" style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; align-items: flex-end;">
                        <div>
                            <label for="transactionStatMonth" style="display: block; font-weight: 600; margin-bottom: 8px;">Tháng:</label>
                            <input type="number" id="transactionStatMonth" class="form-control" min="1" max="12" value="<?= date('m') ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
                        <div>
                            <label for="transactionStatYear" style="display: block; font-weight: 600; margin-bottom: 8px;">Năm:</label>
                            <input type="number" id="transactionStatYear" class="form-control" value="<?= date('Y') ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
                        <div>
                            <button id="filterTransactionStatBtn" class="btn btn-primary" style="width: 100%; padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">
                                <i class="fas fa-filter"></i> Lọc
                            </button>
                        </div>
                    </div>

                    <!-- Transaction stats cards -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        <div style="background: linear-gradient(135deg, #28a745, #20c997); padding: 20px; border-radius: 8px; color: white;">
                            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">Đã Thanh Toán</div>
                            <div id="statDaThanhToan" style="font-size: 32px; font-weight: bold;">0</div>
                        </div>
                        <div style="background: linear-gradient(135deg, #dc3545, #e74c3c); padding: 20px; border-radius: 8px; color: white;">
                            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">Đã Hủy</div>
                            <div id="statDaHuy" style="font-size: 32px; font-weight: bold;">0</div>
                        </div>
                        <div style="background: linear-gradient(135deg, #007bff, #0056b3); padding: 20px; border-radius: 8px; color: white;">
                            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">Đã Hoàn Thành</div>
                            <div id="statDaHoanThanh" style="font-size: 32px; font-weight: bold;">0</div>
                        </div>
                        <div style="background: linear-gradient(135deg, #6c757d, #495057); padding: 20px; border-radius: 8px; color: white;">
                            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">Hết Hiệu Lực</div>
                            <div id="statHetHieuLuc" style="font-size: 32px; font-weight: bold;">0</div>
                        </div>
                        <div style="background: linear-gradient(135deg, #ffc107, #fd7e14); padding: 20px; border-radius: 8px; color: white; display:none;">
                            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">Đang Giữ</div>
                            <div id="statDangGiu" style="font-size: 32px; font-weight: bold;">0</div>
                        </div>
                        <div style="background: linear-gradient(135deg, #17a2b8, #138496); padding: 20px; border-radius: 8px; color: white; display:none;">
                            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">Tổng Cộng</div>
                            <div id="statTongCong" style="font-size: 32px; font-weight: bold;">0</div>
                        </div>
                    </div>

                    <script>
                        document.getElementById('filterTransactionStatBtn').addEventListener('click', function() {
                            loadTransactionStats();
                        });

                        function loadTransactionStats() {
                            const month = document.getElementById('transactionStatMonth').value;
                            const year = document.getElementById('transactionStatYear').value;
                            
                            if (!month || !year) {
                                alert('Vui lòng chọn tháng và năm');
                                return;
                            }

                            const url = `<?= BASE_URL ?>/admin/transaction-stats-ajax?month=${month}&year=${year}`;
                            
                            fetch(url)
                                .then(res => res.json())
                                .then(data => {
                                    if (data.success) {
                                        document.getElementById('statDaThanhToan').textContent = data.data.daThanhToan || 0;
                                        document.getElementById('statDaHuy').textContent = data.data.daHuy || 0;
                                        document.getElementById('statDaHoanThanh').textContent = data.data.daHoanThanh || 0;
                                        document.getElementById('statHetHieuLuc').textContent = data.data.hetHieuLuc || 0;
                                        document.getElementById('statDangGiu').textContent = data.data.dangGiu || 0;
                                        document.getElementById('statTongCong').textContent = data.data.tongCong || 0;
                                    } else {
                                        alert('Lỗi: ' + (data.message || 'Không thể tải dữ liệu'));
                                    }
                                })
                                .catch(err => {
                                    console.error('Error:', err);
                                    alert('Lỗi khi tải dữ liệu');
                                });
                        }

                        // Load stats on page load
                        window.addEventListener('load', function() {
                            loadTransactionStats();
                        });
                    </script>
                </div>
            </div>

            <script>
                document.getElementById('filterDetailedRevenueBtn').addEventListener('click', function() {
                    loadDetailedRevenue(1);
                });

                function loadDetailedRevenue(page = 1) {
                    const startDate = document.getElementById('detailedRevenueStartDate').value;
                    const endDate = document.getElementById('detailedRevenueEndDate').value;
                    
                    if (!startDate || !endDate) {
                        alert('Vui lòng chọn khoảng ngày');
                        return;
                    }

                    const url = `<?= BASE_URL ?>/admin/detailed-revenue-ajax?startDate=${startDate}&endDate=${endDate}&page=${page}`;
                    
                    fetch(url)
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                renderDetailedRevenueTable(data.data);
                                updateDetailedRevenueSummary(data.summary);
                                renderDetailedRevenuePagination(data.pagination, data.dateRange);
                            } else {
                                alert('Lỗi: ' + (data.message || 'Không thể tải dữ liệu'));
                            }
                        })
                        .catch(err => {
                            console.error('Error:', err);
                            alert('Lỗi khi tải dữ liệu');
                        });
                }

                function renderDetailedRevenueTable(data) {
                    const tbody = document.getElementById('detailedRevenueTableBody');
                    
                    if (!data || data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="11" class="text-center text-muted" style="padding: 20px;">Không có dữ liệu</td></tr>';
                        return;
                    }

                    tbody.innerHTML = data.map(row => `
                        <tr>
                            <td><strong>${row.maDatVe}</strong></td>
                            <td>${row.ngayCapNhat}</td>
                            <td>${row.tenNguoiDung || 'N/A'}</td>
                            <td>${row.soDienThoai || 'N/A'}</td>
                            <td><span class="badge badge-info">${row.kyHieuTuyen || 'N/A'}</span></td>
                            <td class="text-center">${row.soLuongVe}</td>
                            <td class="amount">${parseInt(row.doanhThuGop).toLocaleString('vi-VN')}</td>
                            <td class="amount-warning">${parseInt(row.giam).toLocaleString('vi-VN')}</td>
                            <td class="amount-success"><strong>${parseInt(row.doanhThuThucTe).toLocaleString('vi-VN')}</strong></td>
                            <td>${row.phuongThucThanhToan || 'N/A'}</td>
                            <td>
                                <span class="badge ${getStatusBadgeClass(row.trangThai)}">
                                    ${row.trangThai}
                                </span>
                            </td>
                        </tr>
                    `).join('');
                }

                function updateDetailedRevenueSummary(summary) {
                    document.getElementById('summaryTongGop').textContent = 
                        parseInt(summary.tongGop || 0).toLocaleString('vi-VN') + ' VNĐ';
                    document.getElementById('summaryTongGiam').textContent = 
                        parseInt(summary.tongGiam || 0).toLocaleString('vi-VN') + ' VNĐ';
                    document.getElementById('summaryTongThucTe').textContent = 
                        parseInt(summary.tongThucTe || 0).toLocaleString('vi-VN') + ' VNĐ';
                    document.getElementById('summaryGiaoDich').textContent = 
                        (summary.soGiaoDich || 0);
                }

                function renderDetailedRevenuePagination(pagination, dateRange) {
                    const container = document.getElementById('detailedRevenuePagination');
                    let html = '';

                    // Previous button
                    if (pagination.currentPage > 1) {
                        html += `<button class="btn btn-sm btn-outline-primary" onclick="loadDetailedRevenue(${pagination.currentPage - 1})">← Trước</button>`;
                    }

                    // Page numbers
                    const maxPages = Math.min(pagination.totalPages, 10);
                    for (let i = 1; i <= maxPages; i++) {
                        if (i === pagination.currentPage) {
                            html += `<button class="btn btn-sm btn-primary" disabled>${i}</button>`;
                        } else {
                            html += `<button class="btn btn-sm btn-outline-primary" onclick="loadDetailedRevenue(${i})">${i}</button>`;
                        }
                    }

                    // Next button
                    if (pagination.currentPage < pagination.totalPages) {
                        html += `<button class="btn btn-sm btn-outline-primary" onclick="loadDetailedRevenue(${pagination.currentPage + 1})">Sau →</button>`;
                    }

                    // Page info
                    html += `<span style="margin-left: 10px; padding: 5px 10px; background: #f0f0f0; border-radius: 4px; font-size: 14px;">
                        Trang ${pagination.currentPage}/${pagination.totalPages} (Tổng: ${pagination.total} bản ghi)
                    </span>`;

                    container.innerHTML = html;
                }

                function getStatusBadgeClass(status) {
                    const statusMap = {
                        'DaThanhToan': 'badge-success',
                        'DaHoanThanh': 'badge-info',
                        'DaHuy': 'badge-danger',
                        'DangGiu': 'badge-warning',
                        'HetHieuLuc': 'badge-secondary'
                    };
                    return statusMap[status] || 'badge-secondary';
                }
            </script>
        </div>

        <!-- ==================== PHẦN II: THỐNG KÊ HÀNH KHÁCH ==================== -->
        <div class="tab-content" id="customer-tab">
            <!-- Key Metrics -->
            <div class="stats-grid">
                <div class="stat-card success">
                    <div class="stat-icon"><i class="fas fa-user-plus"></i></div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $stats['userStats']['total'] ?></div>
                        <div class="stat-label">Tổng Khách Hàng</div>
                    </div>
                </div>

                <div class="stat-card info">
                    <div class="stat-icon"><i class="fas fa-sync-alt"></i></div>
                    <div class="stat-content">
                        <div class="stat-number"><?= count($stats['repeatCustomers']) ?></div>
                        <div class="stat-label">Khách Lặp Lại</div>
                    </div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-icon"><i class="fas fa-crown"></i></div>
                    <div class="stat-content">
                        <div class="stat-number"><?= !empty($stats['topCustomers'][0]['soVe']) ? $stats['topCustomers'][0]['soVe'] : 0 ?></div>
                        <div class="stat-label">Vé Top Khách/Tháng</div>
                    </div>
                </div>

                <div class="stat-card primary">
                    <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $stats['userStats']['active'] ?></div>
                        <div class="stat-label">Khách Hoạt Động</div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="charts-section">
                <div class="chart-card">
                    <div class="card-header">
                        <h3>Người Dùng Mới Đăng Ký Theo Tháng</h3>
                    </div>
                    <div class="card-content">
                        <canvas id="newUsersChart"></canvas>
                    </div>
                </div>

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
                    <h3>Top Khách Hàng Mua Nhiều Vé Nhất</h3>
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
                                            <td class="text-center"><strong><?= $customer['soVe'] ?? 0 ?></strong></td>
                                            <td class="amount"><?= number_format($customer['tongTien'] ?? 0, 0, ',', '.') ?></td>
                                            <td><?= isset($customer['lanDatCuoi']) ? date('d/m/Y', strtotime($customer['lanDatCuoi'])) : 'N/A' ?></td>
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
            <!-- Ticket Sales by Date -->
            <div class="section-card">
                <div class="card-header">
                    <h3>Khách Hàng Mua Vé</h3>
                </div>
                <div class="card-content">
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
                                    <th>Loại vé</th>
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
                    
                    <div class="pagination" id="todayTicketSalesPagination">
                        <!-- AJAX will populate this -->
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== PHẦN III: THỐNG KÊ CHUYẾN XE ==================== -->
        <div class="tab-content" id="trip-tab">
            <!-- Key Metrics -->
            <div class="stats-grid">
                <div class="stat-card info">
                    <div class="stat-icon"><i class="fas fa-route"></i></div>
                    <div class="stat-content">
                        <div class="stat-number"><?= count($stats['topRoutes']) ?></div>
                        <div class="stat-label">Tuyến Xe Hoạt Động</div>
                    </div>
                </div>

                <div class="stat-card success">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $stats['tripStats']['completed'] ?? 0 ?></div>
                        <div class="stat-label">Chuyến Hoàn Thành</div>
                    </div>
                </div>

                <div class="stat-card primary">
                    <div class="stat-icon"><i class="fas fa-bus"></i></div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $stats['tripStats']['ready'] ?? 0 ?></div>
                        <div class="stat-label">Chuyến Sẵn Sàng</div>
                    </div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $stats['tripStats']['cancelled'] ?? 0 ?></div>
                        <div class="stat-label">Chuyến Bị Hủy</div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="charts-section">
                <div class="chart-card">
                    <div class="card-header">
                        <h3>Trạng Thái Chuyến Xe</h3>
                    </div>
                    <div class="card-content">
                        <canvas id="tripStatusChart"></canvas>
                    </div>
                </div>

                <div class="chart-card">
                    <div class="card-header">
                        <h3>Top Tuyến Xe Được Đặt Nhiều Nhất</h3>
                    </div>
                    <div class="card-content">
                        <canvas id="topRoutesChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="section-card">
                <div class="card-header">
                    <h3>Trạng Thái các Chuyến Xe</h3>
                </div>
                <div class="card-content">
                    <!-- Filter Section -->
                    <div class="filter-section" style="display: flex; gap: 15px; align-items: flex-end; margin-bottom: 20px;">
                        <div class="form-group" style="flex: 1;">
                            <label for="tripStatusStartDate" style="display: block; font-weight: 600; margin-bottom: 8px;">Từ Ngày:</label>
                            <input type="date" id="tripStatusStartDate" class="form-control" value="<?php echo date('Y-m-01'); ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label for="tripStatusEndDate" style="display: block; font-weight: 600; margin-bottom: 8px;">Đến Ngày:</label>
                            <input type="date" id="tripStatusEndDate" class="form-control" value="<?php echo date('Y-m-d'); ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
                        <button id="applyTripStatusFilter" class="btn btn-primary" style="padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">
                            <i class="fas fa-filter"></i> Lọc
                        </button>
                    </div>
                    
                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="stats-table" id="tripStatusTable">
                            <thead>
                                <tr>
                                    <th>Trạng Thái</th>
                                    <th>Số Lượng Chuyến</th>
                                    <th>Tỷ Lệ (%)</th>
                                </tr>
                            </thead>
                            <tbody id="tripStatusTableBody">
                                <!-- JS sẽ populate dữ liệu -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Trip Load Factor Table -->
            <div class="section-card">
                <div class="card-header">
                    <h3>Tỷ Lệ Lấp Đầy Chuyến Xe</h3>
                </div>
                <div class="card-content">
                    <div class="filter-section" style="display: flex; gap: 15px; align-items: flex-end; margin-bottom: 20px;">
                <div class="form-group">
                    <label for="tripStartDate" style="display: block; font-weight: 600; margin-bottom: 8px;">Từ Ngày:</label>
                    <input type="date" id="tripStartDate" class="form-control" value="<?= date('Y-m-01') ?>" style="width: 200px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div class="form-group">
                    <label for="tripEndDate" style="display: block; font-weight: 600; margin-bottom: 8px;">Đến Ngày:</label>
                    <input type="date" id="tripEndDate" class="form-control" value="<?= date('Y-m-d') ?>" style="width: 200px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <button id="applyTripFilter" class="btn btn-primary" style="padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">
                    <i class="fas fa-filter"></i> Lọc
                </button>
            </div>
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

                        <!-- Trip Status Statistics -->
            
        </div>


        <!-- ==================== PHẦN IV: DOANH THU TÀI XẾ ==================== -->
        <div class="tab-content" id="driver-tab">
            <!-- Filter section for driver revenue -->
            <div class="filter-section" style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: flex-end;">
                    <div class="form-group">
                        <label for="driverStartDate" style="display: block; font-weight: 600; margin-bottom: 8px;">Từ Ngày:</label>
                        <input type="date" id="driverStartDate" class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    <div class="form-group">
                        <label for="driverEndDate" style="display: block; font-weight: 600; margin-bottom: 8px;">Đến Ngày:</label>
                        <input type="date" id="driverEndDate" class="form-control" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    </div>
                    <button id="applyDriverFilter" class="btn btn-primary" style="width: 100%; padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">Lọc </button>
                </div>
            </div>


            <!-- Driver Revenue Table -->
            <div class="section-card">
                <div class="card-header">
                    <h3>Doanh Thu Tài Xế Theo Phương Tiện</h3>
                </div>
                <div class="card-content">
                    <div class="table-responsive">
                        <table class="stats-table">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Tên Tài Xế</th>
                                    <th>Số ĐT</th>
                                    <th>Loại Phương Tiện</th>
                                    <th>Biển Số Xe</th>
                                    <th>Số Chuyến</th>
                                    <th>Số Vé</th>
                                    <th>Tổng Doanh Thu</th>
                                    
                                </tr>
                            </thead>
                            <tbody id="driverRevenueBody">
                                <!-- AJAX will populate this -->
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination" id="driverPagination">
                        <!-- AJAX will populate this -->
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>



<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab switching functionality
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', function() {
                const tabName = this.getAttribute('data-tab');
                
                document.querySelectorAll('.tab-button').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                this.classList.add('active');
                document.getElementById(tabName + '-tab').classList.add('active');
            });
        });

        const today = new Date();
        const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
        const lastDayOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        
        document.getElementById('revenueStartDate').value = formatDate(firstDayOfMonth);
        document.getElementById('revenueEndDate').value = formatDate(today);
        document.getElementById('driverStartDate').value = formatDate(firstDayOfMonth);
        document.getElementById('driverEndDate').value = formatDate(today);
        
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

        document.getElementById('applyRevenueFilter').addEventListener('click', function() {
            const startDate = document.getElementById('revenueStartDate').value;
            const endDate = document.getElementById('revenueEndDate').value;
            const filterType = document.getElementById('filterType').value;
            
            if (!startDate || !endDate) {
                alert('Vui lòng chọn khoảng thời gian');
                return;
            }
            
            fetchRevenueData(startDate, endDate, filterType);
        });

        document.getElementById('applyDriverFilter').addEventListener('click', function() {
            const startDate = document.getElementById('driverStartDate').value;
            const endDate = document.getElementById('driverEndDate').value;
            
            if (!startDate || !endDate) {
                alert('Vui lòng chọn khoảng thời gian');
                return;
            }
            
            loadDriverRevenue(1);
        });

        document.getElementById('filterTicketSalesBtn').addEventListener('click', function() {
            const date = document.getElementById('ticketSalesDatePicker').value;
            filterTicketSalesByDate(date);
        });

        // Initial data load
        fetchRevenueData(document.getElementById('revenueStartDate').value, document.getElementById('revenueEndDate').value, 'day');
        loadTodayTicketSalesData(1);
        loadTripLoadFactorData(1);
        loadDriverRevenue(1);
    });

    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function fetchRevenueData(startDate, endDate, filterType) {
        const apiUrl = `<?= BASE_URL ?>/admin/filtered-revenue-ajax?startDate=${startDate}&endDate=${endDate}&filterType=${filterType}`;
        
        document.getElementById('totalRevenueDisplay').textContent = '...';
        document.getElementById('totalTicketsDisplay').textContent = '...';
        document.getElementById('totalCustomersDisplay').textContent = '...';
        document.getElementById('avgRevenueDisplay').textContent = '...';

        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const summary = data.summary || {};
                    document.getElementById('totalRevenueDisplay').textContent = new Intl.NumberFormat('vi-VN').format(summary.tongDoanhThu || 0);
                    document.getElementById('totalTicketsDisplay').textContent = new Intl.NumberFormat('vi-VN').format(summary.soLuongVe || 0);
                    document.getElementById('totalCustomersDisplay').textContent = new Intl.NumberFormat('vi-VN').format(summary.soKhachHang || 0);
                    document.getElementById('avgRevenueDisplay').textContent = new Intl.NumberFormat('vi-VN').format(summary.giaTriTrungBinh || 0);

                    updateFilteredRevenueChart(data.data || [], filterType);
                } else {
                    console.error('Error:', data.message);
                    document.getElementById('totalRevenueDisplay').textContent = 'Lỗi';
                    document.getElementById('totalTicketsDisplay').textContent = 'Lỗi';
                    document.getElementById('totalCustomersDisplay').textContent = 'Lỗi';
                    document.getElementById('avgRevenueDisplay').textContent = 'Lỗi';
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                document.getElementById('totalRevenueDisplay').textContent = 'Lỗi';
                document.getElementById('totalTicketsDisplay').textContent = 'Lỗi';
                document.getElementById('totalCustomersDisplay').textContent = 'Lỗi';
                document.getElementById('avgRevenueDisplay').textContent = 'Lỗi';
            });
    }

    let filteredRevenueChartInstance = null;

    function updateFilteredRevenueChart(chartData, filterType) {
        const ctx = document.getElementById('filteredRevenueChart').getContext('2d');
        
        const labels = chartData.map(item => {
            if (filterType === 'day') return new Date(item.ngay).toLocaleDateString('vi-VN');
            if (filterType === 'month') return item.monthName || item.ngay;
            if (filterType === 'year') return item.year || item.ngay;
            return item.ngay;
        });
        const revenues = chartData.map(item => parseFloat(item.tongDoanhThu) / 1000000);
        
        if (filteredRevenueChartInstance) {
            filteredRevenueChartInstance.destroy();
        }

        filteredRevenueChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Doanh Thu (Triệu VNĐ)',
                    data: revenues,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointBackgroundColor: '#007bff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(context.parsed.y * 1000000);
                            }
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }
</script>

 <Script>
    // Trip Status Stats AJAX
document.getElementById('applyTripStatusFilter').addEventListener('click', function() {
    const startDate = document.getElementById('tripStatusStartDate').value;
    const endDate = document.getElementById('tripStatusEndDate').value;
    
    if (!startDate || !endDate) {
        alert('Vui lòng chọn khoảng thời gian');
        return;
    }
    
    fetchTripStatusStats(startDate, endDate);
});

// Function to fetch and render trip status stats
function fetchTripStatusStats(startDate, endDate) {
    fetch(`<?= BASE_URL ?>/admin/trip-status-stats-ajax?startDate=${startDate}&endDate=${endDate}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderTripStatusTable(data.data);
            } else {
                document.getElementById('tripStatusTableBody').innerHTML = '<tr><td colspan="3" class="text-center text-danger">Lỗi: ' + (data.message || 'Không thể tải dữ liệu') + '</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('tripStatusTableBody').innerHTML = '<tr><td colspan="3" class="text-center text-danger">Lỗi kết nối</td></tr>';
        });
}

// Render table
function renderTripStatusTable(stats) {
    const tbody = document.getElementById('tripStatusTableBody');
    tbody.innerHTML = '';
    
    const total = Object.values(stats).reduce((a, b) => a + b, 0);
    
    for (const [status, count] of Object.entries(stats)) {
        const percentage = total > 0 ? ((count / total) * 100).toFixed(1) : 0;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><span class="badge badge-${getStatusBadgeClass(status)}">${status}</span></td>
            <td class="text-center">${count}</td>
            <td class="text-center">${percentage}%</td>
        `;
        tbody.appendChild(tr);
    }
}

// Helper to get badge class based on status
function getStatusBadgeClass(status) {
    switch (status) {
        case 'Sẵn sàng': return 'info';
        case 'Khởi hành': return 'primary';
        case 'Hoàn thành': return 'success';
        case 'Bị hủy': return 'danger';
        case 'Delay': return 'warning';
        default: return 'secondary';
    }
}

// Load initial data on page load
document.addEventListener('DOMContentLoaded', function() {
    const startDate = document.getElementById('tripStatusStartDate').value;
    const endDate = document.getElementById('tripStatusEndDate').value;
    fetchTripStatusStats(startDate, endDate);
});
 </Script>

<!-- Ticket Sales AJAX Scripts -->
<script>
    function loadTodayTicketSalesData(page = 1) {
        fetch('<?= BASE_URL ?>/admin/today-ticket-sales-ajax?page=' + page)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderTodayTicketSalesTable(data.data || []);
                    renderTodayTicketSalesPagination(data.pagination);
                } else {
                    document.getElementById('todayTicketSalesTableBody').innerHTML = '<tr><td colspan="10" class="text-center text-danger">Lỗi: ' + (data.message || 'Không thể tải dữ liệu') + '</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('todayTicketSalesTableBody').innerHTML = '<tr><td colspan="10" class="text-center text-danger">Lỗi kết nối</td></tr>';
            });
    }

    function filterTicketSalesByDate(date) {
        fetch(`<?= BASE_URL ?>/admin/today-ticket-sales-ajax?date=${date}&page=1`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderTodayTicketSalesTable(data.data || []);
                    renderTodayTicketSalesPagination(data.pagination);
                } else {
                    document.getElementById('todayTicketSalesTableBody').innerHTML = '<tr><td colspan="10" class="text-center text-danger">Lỗi: ' + (data.message || 'Không thể tải dữ liệu') + '</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('todayTicketSalesTableBody').innerHTML = '<tr><td colspan="10" class="text-center text-danger">Lỗi kết nối</td></tr>';
            });
    }

    function renderTodayTicketSalesTable(data) {
        const tbody = document.getElementById('todayTicketSalesTableBody');
        tbody.innerHTML = '';

        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted">Không có dữ liệu</td></tr>';
            return;
        }

        data.forEach(row => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${row.tenKhachHang || 'N/A'}</td>
                <td>${row.soDienThoai || 'N/A'}</td>
                <td>${row.email || 'N/A'}</td>
                <td><span class="badge badge-primary">${row.kyHieuTuyen || 'N/A'}</span></td>
                <td>${row.ngayKhoiHanh ? new Date(row.ngayKhoiHanh).toLocaleDateString('vi-VN') : 'N/A'}</td>
                <td class="text-center">${row.soVe || 0}</td>
                <td>${row.loaiDatVe || 'N/A'}</td>
                <td class="amount">${new Intl.NumberFormat('vi-VN').format(row.tongTien || 0)}</td>
                <td>${row.phuongThucThanhToan || 'N/A'}</td>
                <td><span class="badge badge-${row.trangThai === 'DaThanhToan' ? 'success' : row.trangThai === 'DaHuy' ? 'danger' : 'warning'}">${row.trangThai || 'N/A'}</span></td>
            `;
            tbody.appendChild(tr);
        });
    }

    function renderTodayTicketSalesPagination(pagination) {
        const container = document.getElementById('todayTicketSalesPagination');
        container.innerHTML = '';

        if (!pagination || pagination.totalPages <= 1) return;

        for (let i = 1; i <= pagination.totalPages; i++) {
            const button = document.createElement('button');
            button.textContent = i;
            button.style.cssText = `padding: 8px 12px; border: 1px solid #ddd; background: ${i === pagination.currentPage ? '#007bff' : '#fff'}; color: ${i === pagination.currentPage ? '#fff' : '#000'}; cursor: pointer; border-radius: 4px; margin: 0 2px;`;
            button.addEventListener('click', () => loadTodayTicketSalesData(i));
            container.appendChild(button);
        }
    }
</script>

<!-- Trip Load Factor AJAX Scripts -->
    <script>
    // ========== LỌC & TẢI DỮ LIỆU TỶ LỆ LẤP ĐẦY CHUYẾN XE ==========
    document.getElementById('applyTripFilter').addEventListener('click', function () {
        loadTripLoadFactorData(1); // luôn về trang 1 khi lọc mới
    });

    function loadTripLoadFactorData(page = 1) {
        const startDate = document.getElementById('tripStartDate').value || '<?= date('Y-m-01') ?>';
        const endDate   = document.getElementById('tripEndDate').value   || '<?= date('Y-m-d') ?>';

        const tableBody = document.getElementById('tripLoadFactorBody');
        const paginationControls = document.getElementById('tripLoadPaginationControls');

        tableBody.innerHTML = '<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Đang tải...</td></tr>';

        fetch(`<?= BASE_URL ?>/admin/trip-load-factor-ajax?page=${page}&startDate=${startDate}&endDate=${endDate}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.data.length === 0) {
                        tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Không có chuyến xe nào trong khoảng ngày này</td></tr>';
                    } else {
                        tableBody.innerHTML = data.data.map(trip => `
                            <tr>
                                <td><span class="badge badge-primary">${escapeHtml(trip.kyHieuTuyen || 'N/A')}</span></td>
                                <td>${trip.ngayKhoiHanh ? new Date(trip.ngayKhoiHanh).toLocaleDateString('vi-VN') : 'N/A'}</td>
                                <td class="text-center">${trip.soChoTong || 0}</td>
                                <td class="text-center">${trip.soChoCoNguoi || 0}</td>
                                <td>
                                    <div class="progress-bar" style="background:#e9ecef;height:20px;border-radius:4px;overflow:hidden;">
                                        <div class="progress-fill" style="width: ${trip.tyLeLapDay || 0}%; background:#28a745; height:100%;"></div>
                                    </div>
                                    <small>${Number(trip.tyLeLapDay || 0).toFixed(1)}%</small>
                                </td>
                            </tr>
                        `).join('');
                    }
                    renderPaginationControls(data.pagination, paginationControls);
                } else {
                    tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Lỗi tải dữ liệu</td></tr>';
                }
            })
            .catch(err => {
                console.error(err);
                tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Lỗi kết nối</td></tr>';
            });
    }

    function renderPaginationControls(pagination, container) {
        if (!pagination || pagination.totalPages <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = `<div style="margin-top:15px; display:flex; justify-content:space-between; align-items:center;">
                        <div>Trang ${pagination.currentPage} / ${pagination.totalPages} (Tổng: ${pagination.total} chuyến)</div>
                        <div class="pagination">`;

        if (pagination.currentPage > 1) {
            html += `<button onclick="loadTripLoadFactorData(1)" class="btn-page">« Đầu</button>`;
            html += `<button onclick="loadTripLoadFactorData(${pagination.currentPage - 1})" class="btn-page">‹</button>`;
        }

        const start = Math.max(1, pagination.currentPage - 2);
        const end   = Math.min(pagination.totalPages, pagination.currentPage + 2);

        for (let i = start; i <= end; i++) {
            html += `<button onclick="loadTripLoadFactorData(${i})" class="btn-page ${i === pagination.currentPage ? 'active' : ''}">${i}</button>`;
        }

        if (pagination.currentPage < pagination.totalPages) {
            html += `<button onclick="loadTripLoadFactorData(${pagination.currentPage + 1})" class="btn-page">›</button>`;
            html += `<button onclick="loadTripLoadFactorData(${pagination.totalPages})" class="btn-page">Cuối »</button>`;
        }

        html += `</div></div>`;
        container.innerHTML = html;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Tự động load lần đầu khi mở trang
    document.addEventListener('DOMContentLoaded', function () {
        loadTripLoadFactorData(1);
    });
</script>
<!-- Driver Revenue AJAX Scripts -->
<script>
    function loadDriverRevenue(page = 1) {
        const startDate = document.getElementById('driverStartDate').value;
        const endDate = document.getElementById('driverEndDate').value;

        if (!startDate || !endDate) {
            return;
        }

        fetch(`<?= BASE_URL ?>/admin/driver-revenue-ajax?page=${page}&startDate=${startDate}&endDate=${endDate}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderDriverRevenueTable(data.data || []);
                    renderDriverRevenuePagination(data.pagination, page);
                } else {
                    document.getElementById('driverRevenueBody').innerHTML = '<tr><td colspan="10" class="text-center text-danger">Lỗi: ' + (data.message || 'Không thể tải dữ liệu') + '</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('driverRevenueBody').innerHTML = '<tr><td colspan="10" class="text-center text-danger">Lỗi kết nối</td></tr>';
            });
    }

    function renderDriverRevenueTable(data) {
        const tbody = document.getElementById('driverRevenueBody');
        tbody.innerHTML = '';

        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted">Không có dữ liệu</td></tr>';
            return;
        }

        data.forEach((row, index) => {
            const hoaHong = (row.tongDoanhThu || 0) * 0.1;
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><strong>${index + 1}</strong></td>
                <td>${row.tenTaiXe || 'N/A'}</td>
                <td>${row.soDienThoai || 'N/A'}</td>
                <td>${row.tenLoaiPhuongTien || 'N/A'}</td>
                <td><span class="badge badge-info">${row.bienSo || 'N/A'}</span></td>
                <td class="text-center">${row.soChuyenXe || 0}</td>
                <td class="text-center">${row.soVe || 0}</td>
                <td class="amount">${new Intl.NumberFormat('vi-VN').format(row.tongDoanhThu || 0)}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    function renderDriverRevenuePagination(pagination, currentPage) {
        const container = document.getElementById('driverPagination');
        container.innerHTML = '';

        if (!pagination || pagination.totalPages <= 1) return;

        for (let i = 1; i <= pagination.totalPages; i++) {
            const button = document.createElement('button');
            button.textContent = i;
            button.style.cssText = `padding: 8px 12px; border: 1px solid #ddd; background: ${i === currentPage ? '#007bff' : '#fff'}; color: ${i === currentPage ? '#fff' : '#000'}; cursor: pointer; border-radius: 4px; margin: 0 2px;`;
            button.addEventListener('click', () => loadDriverRevenue(i));
            container.appendChild(button);
        }
    }
</script>

<!-- Sidebar toggle script -->
<script>
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar'); // Assuming 'sidebar' is the ID of your sidebar element

    // Check if both elements exist before adding the event listener
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    } else {
        console.warn("Sidebar toggle button or sidebar element not found. Sidebar toggle functionality might not work.");
    }
</script>

</body>
</html>
