<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="page-header">
        <div class="page-title">
            <h1>Chi tiết Chuyến Xe</h1>
        </div>
        <div class="page-actions">
            <a href="<?php echo BASE_URL; ?>/trips" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <?php if (!in_array($trip['trangThai'], ['Hoàn thành'])): ?>
                <button onclick="showStatusModal()" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Cập nhật trạng thái
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="detail-container">
        <div class="detail-grid">
            <!-- Trip Information -->
            <div class="detail-section">
                <h3><i class="fas fa-info-circle"></i> Thông tin chuyến xe</h3>
                <div class="detail-content">
                    <div class="detail-item">
                        <label>Mã Chuyến xe:</label>
                        <span><?php echo $trip['maChuyenXe']; ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Lịch trình:</label>
                        <span><?php echo htmlspecialchars($trip['tenLichTrinh']); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Tuyến đường:</label>
                        <span class="route-info">
                            <strong><?php echo htmlspecialchars($trip['kyHieuTuyen']); ?></strong><br>
                            <?php echo htmlspecialchars($trip['diemDi'] . ' → ' . $trip['diemDen']); ?><br>
                            <small>Khoảng cách: <?php echo $trip['khoangCach']; ?> km</small>
                        </span>
                    </div>
                    <div class="detail-item">
                        <label>Trạng thái:</label>
                        <span class="status-badge <?php echo Trip::getStatusBadgeClass($trip['trangThai']); ?>">
                            <?php echo $trip['trangThai']; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Vehicle Information -->
            <div class="detail-section">
                <h3><i class="fas fa-bus"></i> Thông tin phương tiện</h3>
                <div class="detail-content">
                    <div class="detail-item">
                        <label>Biển số xe:</label>
                        <span class="vehicle-plate"><?php echo htmlspecialchars($trip['bienSo']); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Loại xe:</label>
                        <span><?php echo htmlspecialchars($trip['tenLoaiPhuongTien']); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Số chỗ:</label>
                        <span><?php echo $trip['soChoMacDinh']; ?> chỗ</span>
                    </div>
                </div>
            </div>

            <!-- Time Information -->
            <div class="detail-section">
                <h3><i class="fas fa-clock"></i> Thông tin thời gian</h3>
                <div class="detail-content">
                    <div class="detail-item">
                        <label>Ngày khởi hành:</label>
                        <span class="date-info"><?php echo date('d/m/Y', strtotime($trip['ngayKhoiHanh'])); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Giờ khởi hành:</label>
                        <span class="time-info"><?php echo date('H:i', strtotime($trip['thoiGianKhoiHanh'])); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Giờ kết thúc dự kiến:</label>
                        <span class="time-info"><?php echo date('H:i', strtotime($trip['thoiGianKetThuc'])); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Thời gian di chuyển:</label>
                        <span><?php echo $trip['thoiGianDiChuyen']; ?></span>
                    </div>
                </div>
            </div>

            <!-- Booking Information -->
            <div class="detail-section">
                <h3><i class="fas fa-users"></i> Thông tin đặt chỗ</h3>
                <div class="detail-content">
                    <div class="detail-item">
                        <label>Tổng số chỗ:</label>
                        <span><?php echo $trip['soChoTong']; ?> chỗ</span>
                    </div>
                    <div class="detail-item">
                        <label>Số chỗ đã đặt:</label>
                        <span class="booked-seats"><?php echo $trip['soChoDaDat']; ?> chỗ</span>
                    </div>
                    <div class="detail-item">
                        <label>Số chỗ trống:</label>
                        <span class="available-seats"><?php echo $trip['soChoTrong']; ?> chỗ</span>
                    </div>
                    <div class="detail-item">
                        <label>Tỷ lệ lấp đầy:</label>
                        <div class="occupancy-display">
                            <?php $occupancy = Trip::calculateOccupancy($trip['soChoDaDat'], $trip['soChoTong']); ?>
                            <div class="occupancy-bar large">
                                <div class="occupancy-fill" style="width: <?php echo $occupancy; ?>%"></div>
                                <span class="occupancy-text"><?php echo $occupancy; ?>%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pricing Information -->
            <div class="detail-section">
                <h3><i class="fas fa-money-bill"></i> Thông tin giá vé</h3>
                <div class="detail-content">
                    <?php if ($trip['giaVe']): ?>
                        <div class="detail-item">
                            <label>Giá vé:</label>
                            <span class="price-info"><?php echo number_format($trip['giaVe'], 0, ',', '.'); ?> VNĐ</span>
                        </div>
                        <div class="detail-item">
                            <label>Loại vé:</label>
                            <span><?php echo $trip['tenLoaiVe'] ?? 'Vé thường'; ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Loại chỗ ngồi:</label>
                            <span><?php echo $trip['loaiChoNgoi']; ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Doanh thu dự kiến:</label>
                            <span class="revenue-info"><?php echo number_format($trip['giaVe'] * $trip['soChoDaDat'], 0, ',', '.'); ?> VNĐ</span>
                        </div>
                    <?php else: ?>
                        <div class="detail-item">
                            <span class="text-muted">Chưa có thông tin giá vé</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pickup/Dropoff Points -->
            <?php if (!empty($points)): ?>
            <div class="detail-section full-width">
                <h3><i class="fas fa-map-marker-alt"></i> Điểm đón/trả khách</h3>
                <div class="detail-content">
                    <div class="points-grid">
                        <div class="pickup-points">
                            <h4>Điểm đón</h4>
                            <?php foreach ($points as $point): ?>
                                <?php if ($point['loaiDiem'] == 'Đón'): ?>
                                    <div class="point-item">
                                        <strong><?php echo htmlspecialchars($point['tenDiem']); ?></strong>
                                        <?php if ($point['diaChi']): ?>
                                            <br><small><?php echo htmlspecialchars($point['diaChi']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <div class="dropoff-points">
                            <h4>Điểm trả</h4>
                            <?php foreach ($points as $point): ?>
                                <?php if ($point['loaiDiem'] == 'Trả'): ?>
                                    <div class="point-item">
                                        <strong><?php echo htmlspecialchars($point['tenDiem']); ?></strong>
                                        <?php if ($point['diaChi']): ?>
                                            <br><small><?php echo htmlspecialchars($point['diaChi']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- System Information -->
            <div class="detail-section full-width">
                <h3><i class="fas fa-cog"></i> Thông tin hệ thống</h3>
                <div class="detail-content">
                    <div class="detail-row">
                        <div class="detail-item">
                            <label>Ngày tạo:</label>
                            <span><?php echo date('d/m/Y H:i', strtotime($trip['ngayTao'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Cập nhật lần cuối:</label>
                            <span><?php echo date('d/m/Y H:i', strtotime($trip['ngayCapNhat'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="detail-actions">
            <?php if (!in_array($trip['trangThai'], ['Hoàn thành'])): ?>
                <button onclick="showStatusModal()" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Cập nhật trạng thái
                </button>
            <?php endif; ?>
            <?php if (!in_array($trip['trangThai'], ['Đã khởi hành', 'Hoàn thành'])): ?>
                <button onclick="confirmDelete(<?php echo $trip['maChuyenXe']; ?>)" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Xóa chuyến xe
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div id="statusModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Cập nhật trạng thái chuyến xe</h3>
            <button onclick="hideStatusModal()" class="modal-close">&times;</button>
        </div>
        <form method="POST" action="<?php echo BASE_URL; ?>/trips/<?php echo $trip['maChuyenXe']; ?>/update-status">
            <div class="modal-body">
                <div class="form-group">
                    <label for="trangThai">Trạng thái mới:</label>
                    <select name="trangThai" id="trangThai" required>
                        <?php foreach (Trip::getStatusOptions() as $key => $status): ?>
                            <option value="<?php echo $key; ?>" 
                                    <?php echo ($trip['trangThai'] == $key) ? 'selected' : ''; ?>>
                                <?php echo $status; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Cập nhật
                </button>
                <button type="button" onclick="hideStatusModal()" class="btn btn-outline">
                    Hủy
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showStatusModal() {
    document.getElementById('statusModal').style.display = 'flex';
}

function hideStatusModal() {
    document.getElementById('statusModal').style.display = 'none';
}

function confirmDelete(tripId) {
    if (confirm('Bạn có chắc chắn muốn xóa chuyến xe này? Hành động này không thể hoàn tác và sẽ ảnh hưởng đến các vé đã đặt.')) {
        window.location.href = '<?php echo BASE_URL; ?>/trips/' + tripId + '/delete';
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('statusModal');
    if (event.target == modal) {
        hideStatusModal();
    }
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
