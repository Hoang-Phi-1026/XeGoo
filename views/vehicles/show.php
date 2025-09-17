<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="page-header">
        <div class="page-title">
            <h1><i class="fas fa-bus"></i> Chi tiết phương tiện</h1>
            <p>Thông tin chi tiết của phương tiện <?php echo htmlspecialchars($vehicle['bienSo']); ?></p>
        </div>
        <div class="page-actions">
            <a href="<?php echo BASE_URL; ?>/vehicles" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>
            <a href="<?php echo BASE_URL; ?>/vehicles/<?php echo $vehicle['maPhuongTien']; ?>/edit" class="btn btn-warning">
                <i class="fas fa-edit"></i> Chỉnh sửa
            </a>
        </div>
    </div>

    <div class="detail-container">
        <div class="detail-card">
            <div class="detail-header">
                <div class="vehicle-info">
                    <h2><?php echo htmlspecialchars($vehicle['bienSo']); ?></h2>
                    <span class="status-badge <?php echo $vehicle['trangThai'] == 'Đang hoạt động' ? 'active' : 'maintenance'; ?>">
                        <?php echo $vehicle['trangThai']; ?>
                    </span>
                </div>
                <div class="vehicle-icon">
                    <i class="fas fa-bus"></i>
                </div>
            </div>

            <div class="detail-content">
                <div class="detail-grid">
                    <div class="detail-item">
                        <label>Mã phương tiện:</label>
                        <span><?php echo $vehicle['maPhuongTien']; ?></span>
                    </div>
                    <!-- Updated to display vehicle type information from new table structure -->
                    <div class="detail-item">
                        <label>Loại phương tiện:</label>
                        <span><?php echo htmlspecialchars($vehicle['tenLoaiPhuongTien']); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Số chỗ ngồi:</label>
                        <span><?php echo $vehicle['soChoMacDinh']; ?> chỗ</span>
                    </div>
                    <div class="detail-item">
                        <label>Loại chỗ ngồi:</label>
                        <span><?php echo htmlspecialchars($vehicle['loaiChoNgoiMacDinh']); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Hãng xe:</label>
                        <span><?php echo htmlspecialchars($vehicle['hangXe'] ?? 'Không xác định'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Biển số xe:</label>
                        <span class="license-plate"><?php echo htmlspecialchars($vehicle['bienSo']); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Trạng thái:</label>
                        <span class="status-badge <?php echo $vehicle['trangThai'] == 'Đang hoạt động' ? 'active' : 'maintenance'; ?>">
                            <?php echo $vehicle['trangThai']; ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="detail-actions">
                <a href="<?php echo BASE_URL; ?>/vehicles/<?php echo $vehicle['maPhuongTien']; ?>/edit" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Chỉnh sửa thông tin
                </a>
                <?php if ($vehicle['trangThai'] == 'Đang hoạt động'): ?>
                    <button onclick="confirmDelete(<?php echo $vehicle['maPhuongTien']; ?>)" class="btn btn-danger">
                        <i class="fas fa-tools"></i> Chuyển sang bảo trì
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(vehicleId) {
    if (confirm('Bạn có chắc chắn muốn chuyển phương tiện này sang trạng thái bảo trì?')) {
        window.location.href = '<?php echo BASE_URL; ?>/vehicles/' + vehicleId + '/delete';
    }
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
