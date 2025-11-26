<?php 
require_once __DIR__ . '/../../helpers/IDEncryptionHelper.php';
include __DIR__ . '/../layouts/header.php'; 
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/driver-report.css">

<div class="driver-report-container">
    <div class="report-header">
        <h1>Báo Cáo Chuyến Đi</h1>
        <p class="driver-name">Tài xế: <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
    </div>

    <div class="upcoming-trips-section">
        <h2>
            <i class="fas fa-bus"></i>
            Danh sách chuyến Đi Hôm Nay
        </h2>
        
        <?php if (empty($upcomingTrips)): ?>
            <div class="no-trips">
                <i class="fas fa-inbox"></i>
                <p>Không có chuyến đi nào hôm nay</p>
            </div>
        <?php else: ?>
            <div class="trips-grid">
                <?php foreach ($upcomingTrips as $trip): ?>
                    <!-- Added notification badge for "Khởi hành" status trips -->
                    <div class="trip-card <?php echo ($trip['trangThai'] === 'Khởi hành') ? 'trip-card-departure' : ''; ?>">
                        <?php if ($trip['trangThai'] === 'Khởi hành'): ?>
                            <div class="departure-notification-badge">
                                <span class="badge-pulse"></span>
                                <span class="badge-text">Sẵn sàng khởi hành</span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="trip-header">
                            <h3><?php echo htmlspecialchars($trip['kyHieuTuyen']); ?></h3>
                            <span class="trip-status <?php echo strtolower(str_replace(' ', '-', $trip['trangThai'])); ?>">
                                <?php echo htmlspecialchars($trip['trangThai']); ?>
                            </span>
                        </div>
                        
                        <div class="trip-route">
                            <div class="route-info">
                                <i class="fas fa-map-marker-alt start"></i>
                                <span><?php echo htmlspecialchars($trip['diemDi']); ?></span>
                            </div>
                            <div class="route-arrow">
                                <i class="fas fa-long-arrow-alt-right"></i>
                            </div>
                            <div class="route-info">
                                <i class="fas fa-map-marker-alt end"></i>
                                <span><?php echo htmlspecialchars($trip['diemDen']); ?></span>
                            </div>
                        </div>
                        
                        <div class="trip-info">
                            <div class="info-row">
                                <i class="fas fa-calendar"></i>
                                <span><?php echo date('d/m/Y', strtotime($trip['ngayKhoiHanh'])); ?></span>
                            </div>
                            <div class="info-row">
                                <i class="fas fa-clock"></i>
                                <span><?php echo date('H:i', strtotime($trip['thoiGianKhoiHanh'])); ?></span>
                            </div>
                            <div class="info-row">
                                <i class="fas fa-bus"></i>
                                <span><?php echo htmlspecialchars($trip['bienSo']); ?></span>
                            </div>
                            <div class="info-row">
                                <i class="fas fa-users"></i>
                                <span><?php echo $trip['soChoDaDat']; ?>/<?php echo $trip['soChoTong']; ?> hành khách</span>
                            </div>
                        </div>
                        
                        <!-- Display different actions based on trip status -->
                        <?php if ($trip['trangThai'] === 'Sẵn sàng'): ?>
                            <?php 
                            $encryptedTripId = IDEncryptionHelper::encryptId($trip['maChuyenXe']);
                            ?>
                            <a href="<?php echo BASE_URL; ?>/driver/report/attendance/<?php echo $encryptedTripId; ?>" class="btn-attendance">
                                <i class="fas fa-clipboard-check"></i>
                                Điểm danh hành khách
                            </a>
                        <?php elseif ($trip['trangThai'] === 'Khởi hành'): ?>
                            <div class="trip-notification">
                                <div class="notification-message">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Chuyến xe đã được xác nhận khởi hành</span>
                                </div>
                                <!-- Fixed form to properly POST to complete-trip endpoint -->
                                <form id="complete-trip-form-<?php echo $trip['maChuyenXe']; ?>" class="complete-trip-form" method="POST" action="<?php echo BASE_URL; ?>/driver/report/complete-trip">
                                    <input type="hidden" name="trip_id" value="<?php echo $trip['maChuyenXe']; ?>">
                                    <button type="button" class="btn-complete-trip" onclick="openCompleteModal(<?php echo $trip['maChuyenXe']; ?>)">
                                        <i class="fas fa-flag-checkered"></i>
                                        Kết thúc chuyến xe
                                    </button>
                                </form>
                            </div>
                        <?php elseif ($trip['trangThai'] === 'Hoàn thành'): ?>
                            <div class="trip-completed">
                                <i class="fas fa-check-double"></i>
                                <span>Chuyến xe đã hoàn thành</span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Updated modal with proper form submission handling -->
<div id="completeModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h2 class="modal-title">Kết thúc chuyến xe</h2>
        </div>
        <div class="modal-body">
            <p>Bạn có chắc chắn muốn kết thúc chuyến xe này không?</p>
            <p>Hành động này không thể hoàn tác.</p>
        </div>
        <div class="modal-footer">
            <button class="modal-btn modal-btn-cancel" onclick="closeCompleteModal()">Hủy</button>
            <button class="modal-btn modal-btn-confirm" onclick="submitCompleteTrip()">Xác nhận</button>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<script>
    let pendingTripId = null;

    function openCompleteModal(tripId) {
        console.log("[v0] Opening modal for trip:", tripId);
        pendingTripId = tripId;
        document.getElementById('completeModal').classList.add('active');
    }

    function closeCompleteModal() {
        console.log("[v0] Closing modal");
        document.getElementById('completeModal').classList.remove('active');
        pendingTripId = null;
    }

    function submitCompleteTrip() {
        if (!pendingTripId) {
            console.error("[v0] No trip ID found");
            alert('Có lỗi xảy ra. Vui lòng thử lại.');
            return;
        }
        
        console.log("[v0] Submitting form for trip:", pendingTripId);
        const formId = 'complete-trip-form-' + pendingTripId;
        const form = document.getElementById(formId);
        
        if (form) {
            closeCompleteModal();
            form.submit();
        } else {
            console.error("[v0] Form not found:", formId);
            alert('Có lỗi xảy ra. Vui lòng thử lại.');
        }
    }

    // Close modal when clicking outside
    document.getElementById('completeModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeCompleteModal();
        }
    });
</script>
