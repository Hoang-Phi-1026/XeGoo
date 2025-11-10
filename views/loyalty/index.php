<?php include 'views/layouts/header.php'; ?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/loyalty.css">

<div class="loyalty-container">
    <div class="loyalty-header">
        <div class="loyalty-header-content">
            <h1><i class="fas fa-star"></i> Điểm tích lũy</h1>
            <p>Quản lý và theo dõi điểm tích lũy của bạn</p>
        </div>
    </div>

    <!-- Added customer badge section -->
    <?php if ($currentBadge): ?>
    <div class="badge-section">
        <div class="badge-container badge-<?php echo htmlspecialchars($currentBadge['level']); ?>">
            <div class="badge-icon">
                <i class="fas fa-<?php echo htmlspecialchars($currentBadge['icon']); ?>"></i>
            </div>
            <div class="badge-content">
                <div class="badge-label">Chứng chỉ khách hàng</div>
                <div class="badge-name"><?php echo htmlspecialchars($currentBadge['name']); ?></div>
                <div class="badge-subtitle">
                    <?php 
                    if ($currentBadge['level'] === 'vip') {
                        echo 'Bạn là khách hàng VIP của XeGoo. Hưởng ưu đãi đặc biệt và hỗ trợ ưu tiên!';
                    } elseif ($currentBadge['level'] === 'gold') {
                        echo 'Chỉ cần ' . number_format(5000 - $totalPoints) . ' điểm nữa để nâng cấp thành khách hàng thân thiết!';
                    } elseif ($currentBadge['level'] === 'silver') {
                        echo 'Chỉ cần ' . number_format(2000 - $totalPoints) . ' điểm nữa để nâng cấp lên hạng cao cấp!';
                    } else {
                        echo 'Tiếp tục mua vé để tích luỹ điểm và nâng cấp hạng khách hàng!';
                    }
                    ?>
                </div>
            </div>
            <?php if ($totalPoints > 0): ?>
            <div class="badge-progress">
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo min(100, ($totalPoints / ($currentBadge['minPoints'] + 5000)) * 100); ?>%"></div>
                </div>
                <div class="progress-info">
                    <span class="progress-current"><?php echo number_format($totalPoints); ?> điểm</span>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Points Summary -->
    <div class="points-summary-card">
        <div class="points-summary-content">
            <div class="points-main">
                <div class="points-icon-large">
                    <i class="fas fa-coins"></i>
                </div>
                <div class="points-details">
                    <div class="points-label">Tổng điểm hiện có</div>
                    <div class="points-value-large"><?php echo number_format($totalPoints); ?></div>
                    <div class="points-subtitle">điểm tích lũy</div>
                </div>
            </div>
            <div class="points-stats">
                <div class="stat-item">
                    <div class="stat-icon earned">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo number_format($earnedPoints); ?></div>
                        <div class="stat-label">Điểm đã nhận</div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon used">
                        <i class="fas fa-minus-circle"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo number_format($usedPoints); ?></div>
                        <div class="stat-label">Điểm đã dùng</div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon value">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo number_format($totalPoints * 100); ?>đ</div>
                        <div class="stat-label">Giá trị quy đổi</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Points Info -->
    <div class="points-info-section">
        <div class="info-card">
            <div class="info-icon">
                <i class="fas fa-info-circle"></i>
            </div>
            <div class="info-content">
                <h3>Cách tích điểm</h3>
                <p>Nhận 0.03% giá trị vé mỗi khi đặt vé thành công. Ví dụ: Vé 100,000đ = 30 điểm</p>
            </div>
        </div>
        <div class="info-card">
            <div class="info-icon">
                <i class="fas fa-gift"></i>
            </div>
            <div class="info-content">
                <h3>Cách sử dụng</h3>
                <p>1 điểm = 100đ giảm giá. Tối đa 50% giá trị đơn hàng khi thanh toán</p>
            </div>
        </div>
        <div class="info-card">
            <div class="info-icon">
                <i class="fas fa-undo"></i>
            </div>
            <div class="info-content">
                <h3>Hoàn điểm khi hủy</h3>
                <p>Nhận 20% giá trị vé dưới dạng điểm khi hủy vé hợp lệ</p>
            </div>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="history-section">
        <div class="history-header">
            <h2><i class="fas fa-history"></i> Lịch sử tích lũy điểm</h2>
            <div class="history-filters">
                <select id="filterType" class="filter-select">
                    <option value="">Tất cả giao dịch</option>
                    <option value="MuaVe">Tích điểm từ mua vé</option>
                    <option value="HuyVe">Sử dụng điểm / Hoàn điểm</option>
                </select>
                <select id="filterSort" class="filter-select">
                    <option value="desc">Mới nhất</option>
                    <option value="asc">Cũ nhất</option>
                </select>
            </div>
        </div>

        <div class="history-list" id="historyList">
            <?php 
            if (empty($history)): 
            ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>Chưa có giao dịch nào</p>
                    <p class="empty-hint">Điểm tích lũy sẽ được cộng khi bạn đặt vé thành công</p>
                </div>
            <?php else: ?>
                <?php foreach ($history as $transaction): ?>
                    <div class="transaction-item" data-type="<?php echo htmlspecialchars($transaction['nguon']); ?>">
                        <div class="transaction-icon <?php echo $transaction['diem'] > 0 ? 'earned' : 'used'; ?>">
                            <i class="fas fa-<?php echo $transaction['diem'] > 0 ? 'plus' : 'minus'; ?>-circle"></i>
                        </div>
                        <div class="transaction-info">
                            <div class="transaction-title">
                                <?php 
                                if ($transaction['nguon'] === 'MuaVe') {
                                    echo 'Tích điểm từ mua vé';
                                } else {
                                    echo $transaction['diem'] > 0 ? 'Hoàn điểm từ hủy vé' : 'Sử dụng điểm thanh toán';
                                }
                                ?>
                            </div>
                            <div class="transaction-details">
                                <span class="transaction-note"><?php echo htmlspecialchars($transaction['ghiChu']); ?></span>
                                <?php if ($transaction['maDatVe']): ?>
                                    <span class="transaction-booking">Mã đặt vé: #<?php echo $transaction['maDatVe']; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="transaction-date">
                                <i class="fas fa-clock"></i>
                                <?php echo date('d/m/Y H:i', strtotime($transaction['ngayTao'])); ?>
                            </div>
                        </div>
                        <div class="transaction-points <?php echo $transaction['diem'] > 0 ? 'positive' : 'negative'; ?>">
                            <?php echo $transaction['diem'] > 0 ? '+' : ''; ?><?php echo number_format($transaction['diem']); ?>
                            <span class="points-label">điểm</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Filter functionality
document.getElementById('filterType')?.addEventListener('change', filterTransactions);
document.getElementById('filterSort')?.addEventListener('change', filterTransactions);

function filterTransactions() {
    const filterType = document.getElementById('filterType').value;
    const filterSort = document.getElementById('filterSort').value;
    const items = Array.from(document.querySelectorAll('.transaction-item'));
    
    // Filter by type
    items.forEach(item => {
        if (filterType === '' || item.dataset.type === filterType) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
    
    // Sort
    const visibleItems = items.filter(item => item.style.display !== 'none');
    const parent = document.getElementById('historyList');
    
    visibleItems.sort((a, b) => {
        const dateA = new Date(a.querySelector('.transaction-date').textContent.trim());
        const dateB = new Date(b.querySelector('.transaction-date').textContent.trim());
        return filterSort === 'desc' ? dateB - dateA : dateA - dateB;
    });
    
    visibleItems.forEach(item => parent.appendChild(item));
}
</script>

<?php include 'views/layouts/footer.php'; ?>
