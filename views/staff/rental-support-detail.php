<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../helpers/IDEncryptionHelper.php'; // Include the helper for encryption
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết yêu cầu thuê xe - XeGoo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/staff-rental-support.css">
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <main class="rental-support-detail-container">
        <!-- Header with back button -->
        <div class="detail-header">
            <a href="<?php echo BASE_URL; ?>/staff/rental-support" class="back-button">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <h1 class="page-title">Chi tiết yêu cầu thuê xe</h1>
            <div class="header-status">
                <span class="status-badge badge-<?php echo strtolower(str_replace(' ', '-', $request['trangThai'])); ?>">
                    <?php echo htmlspecialchars($request['trangThai']); ?>
                </span>
            </div>
        </div>

        <div class="detail-content">
            <div class="detail-card">
                <!-- Customer Information Section -->
                <div class="section">
                    <h2 class="section-title">Thông tin khách hàng</h2>
                    <div class="section-body">
                        <div class="info-grid-2">
                            <div class="info-field">
                                <span class="field-label">Họ tên</span>
                                <span class="field-value"><?php echo htmlspecialchars($request['hoTenNguoiThue']); ?></span>
                            </div>
                            <div class="info-field">
                                <span class="field-label">Số điện thoại</span>
                                <a href="tel:<?php echo htmlspecialchars($request['soDienThoaiNguoiThue'] ?? ''); ?>" class="field-value link">
                                    <?php echo htmlspecialchars($request['soDienThoaiNguoiThue'] ?? ''); ?>
                                </a>
                            </div>
                            <div class="info-field">
                                <span class="field-label">Email</span>
                                <a href="mailto:<?php echo htmlspecialchars($request['emailNguoiThue'] ?? ''); ?>" class="field-value link">
                                    <?php echo htmlspecialchars($request['emailNguoiThue'] ?? ''); ?>
                                </a>
                            </div>
                            <div class="info-field">
                                <span class="field-label">Số lượng hành khách</span>
                                <span class="field-value"><?php echo $request['soLuongNguoi']; ?> người</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Trip Type Indicator -->
                <div class="trip-type-section">
                    <div class="trip-type-badge <?php echo ($request['loaiHanhTrinh'] === 'Khứ hồi') ? 'round-trip' : 'one-way'; ?>">
                        <?php echo htmlspecialchars($request['loaiHanhTrinh']); ?>
                    </div>
                </div>

                <!-- Outbound Trip Information Section -->
                <div class="section">
                    <h2 class="section-title">Thông tin chuyến đi</h2>
                    <div class="section-body">
                        <div class="route-box">
                            <div class="route-item">
                                <span class="route-label">Điểm khởi hành</span>
                                <span class="route-value"><?php echo htmlspecialchars($request['diemDi']); ?></span>
                            </div>
                            <div class="route-arrow">→</div>
                            <div class="route-item">
                                <span class="route-label">Điểm đến</span>
                                <span class="route-value"><?php echo htmlspecialchars($request['diemDen']); ?></span>
                            </div>
                        </div>

                        <div class="info-grid-3">
                            <div class="info-field">
                                <span class="field-label">Ngày đi</span>
                                <span class="field-value"><?php echo date('d/m/Y', strtotime($request['ngayDi'])); ?></span>
                            </div>
                            <div class="info-field">
                                <span class="field-label">Giờ đi</span>
                                <span class="field-value"><?php echo date('H:i', strtotime($request['gioDi'])); ?></span>
                            </div>
                            <div class="info-field">
                                <span class="field-label">Điểm đón khách</span>
                                <span class="field-value"><?php echo htmlspecialchars($request['diemDonDi'] ?? 'Không có'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Return Trip Information (only if round-trip) -->
                <?php if ($request['loaiHanhTrinh'] === 'Khứ hồi' && !empty($request['ngayVe']) && $request['ngayVe'] !== '0000-00-00'): ?>
                    <div class="section return-trip-section">
                        <h2 class="section-title">Thông tin chuyến về</h2>
                        <div class="section-body">
                            <div class="route-box">
                                <div class="route-item">
                                    <span class="route-label">Điểm khởi hành</span>
                                    <span class="route-value"><?php echo htmlspecialchars($request['diemDen']); ?></span>
                                </div>
                                <div class="route-arrow">→</div>
                                <div class="route-item">
                                    <span class="route-label">Điểm đến</span>
                                    <span class="route-value"><?php echo htmlspecialchars($request['diemDi']); ?></span>
                                </div>
                            </div>

                            <div class="info-grid-3">
                                <div class="info-field">
                                    <span class="field-label">Ngày về</span>
                                    <span class="field-value"><?php echo date('d/m/Y', strtotime($request['ngayVe'])); ?></span>
                                </div>
                                <div class="info-field">
                                    <span class="field-label">Giờ về</span>
                                    <span class="field-value"><?php echo date('H:i', strtotime($request['gioVe'])); ?></span>
                                </div>
                                <div class="info-field">
                                    <span class="field-label">Điểm đón khách</span>
                                    <span class="field-value"><?php echo htmlspecialchars($request['diemDonVe'] ?? 'Không có'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Vehicle Information Section -->
                <div class="section">
                    <h2 class="section-title">Thông tin xe</h2>
                    <div class="section-body">
                        <div class="info-grid-2">
                            <?php if ($request['tenLoaiPhuongTien']): ?>
                                <div class="info-field">
                                    <span class="field-label">Loại xe yêu cầu</span>
                                    <span class="field-value"><?php echo htmlspecialchars($request['tenLoaiPhuongTien']); ?></span>
                                </div>
                            <?php endif; ?>
                            <!-- Display loaiChoNgoiMacDinh instead of soTang, soHang -->
                            <?php if ($request['loaiChoNgoiMacDinh']): ?>
                                <div class="info-field">
                                    <span class="field-label">Loại chỗ ngồi</span>
                                    <span class="field-value"><?php echo htmlspecialchars($request['loaiChoNgoiMacDinh']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Notes Section -->
                <?php if (!empty($request['ghiChu'])): ?>
                    <div class="section">
                        <h2 class="section-title">Ghi chú</h2>
                        <div class="section-body">
                            <p class="notes-text"><?php echo htmlspecialchars($request['ghiChu']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Request Status Section -->
                <div class="section">
                    <h2 class="section-title">Thông tin yêu cầu</h2>
                    <div class="section-body">
                        <div class="info-grid-2">
                            <div class="status-field">
                                <span class="field-label">Trạng thái</span>
                                <span class="status-badge badge-<?php echo strtolower(str_replace(' ', '-', $request['trangThai'])); ?>">
                                    <?php echo htmlspecialchars($request['trangThai']); ?>
                                </span>
                            </div>
                            <div class="status-field">
                                <span class="field-label">Ngày tạo</span>
                                <span class="field-value"><?php echo date('d/m/Y H:i', strtotime($request['ngayTao'])); ?></span>
                            </div>
                            <div class="status-field">
                                <span class="field-label">Cập nhật lần cuối</span>
                                <span class="field-value"><?php echo date('d/m/Y H:i', strtotime($request['ngayCapNhat'])); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action buttons at bottom -->
            <div class="action-buttons">
                <button class="btn-approve" onclick="updateStatus('Đã duyệt')">
                    Duyệt yêu cầu
                </button>
                <button class="btn-reject" onclick="updateStatus('Từ chối')">
                    Từ chối
                </button>
                <a href="<?php echo BASE_URL; ?>/staff/rental-support" class="btn-cancel">
                    Quay lại
                </a>
            </div>
        </div>
    </main>

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>

    <script src="<?php echo BASE_URL; ?>/public/js/notifications.js"></script>
    <script src="<?php echo BASE_URL; ?>/public/js/theme-toggle.js"></script>
    <script>
        const baseUrl = '<?php echo BASE_URL; ?>';
        const requestId = <?php echo json_encode(IDEncryptionHelper::encryptId($request['maThuXe'])); ?>;

        function updateStatus(status) {
            if (!confirm(`Bạn có chắc chắn muốn cập nhật trạng thái thành "${status}"?`)) {
                return;
            }

            const payload = {
                requestId: requestId,
                status: status
            };

            fetch(baseUrl + '/staff/rental-support/update-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess(data.message);
                    setTimeout(() => {
                        window.location.href = baseUrl + '/staff/rental-support';
                    }, 1500);
                } else {
                    showError(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Có lỗi xảy ra khi cập nhật trạng thái');
            });
        }

        <?php
        if (isset($_SESSION['success'])) {
            echo 'showSuccess("' . addslashes($_SESSION['success']) . '");';
            unset($_SESSION['success']);
        }
        if (isset($_SESSION['error'])) {
            echo 'showError("' . addslashes($_SESSION['error']) . '");';
            unset($_SESSION['error']);
        }
        ?>
    </script>
</body>
</html>
