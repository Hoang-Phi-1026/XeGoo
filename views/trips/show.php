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

    <div class="trips-detail-container">
        <div class="trips-detail-grid">
            <!-- Card: Trip Information -->
            <div class="trips-detail-card">
                <div class="trips-detail-card-header">
                    <i class="fas fa-info-circle"></i>
                    <span>Thông tin chuyến xe</span>
                </div>
                <ul class="trips-detail-list">
                    <li class="trips-detail-item">
                        <span class="label">Mã Chuyến xe:</span>
                        <span><?php echo $trip['maChuyenXe']; ?></span>
                    </li>
                    <li class="trips-detail-item">
                        <span class="label">Lịch trình:</span>
                        <span><?php echo htmlspecialchars($trip['tenLichTrinh']); ?></span>
                    </li>
                    <li class="trips-detail-item">
                        <span class="label">Tuyến đường:</span>
                        <span class="route-info">
                            <strong><?php echo htmlspecialchars($trip['kyHieuTuyen']); ?></strong><br>
                            <?php echo htmlspecialchars($trip['diemDi'] . ' → ' . $trip['diemDen']); ?><br>
                            <small>Khoảng cách: <?php echo $trip['khoangCach']; ?> km</small>
                        </span>
                    </li>
                    <li class="trips-detail-item">
                        <span class="label">Trạng thái:</span>
                        <span class="trips-detail-badge <?php echo Trip::getStatusBadgeClass($trip['trangThai']); ?>">
                            <?php echo $trip['trangThai']; ?>
                        </span>
                    </li>
                </ul>
            </div>

            <!-- Card: Vehicle Information -->
            <div class="trips-detail-card">
                <div class="trips-detail-card-header">
                    <i class="fas fa-bus"></i>
                    <span>Thông tin phương tiện</span>
                </div>
                <ul class="trips-detail-list">
                    <li class="trips-detail-item">
                        <span class="label">Biển số xe:</span>
                        <span><?php echo htmlspecialchars($trip['bienSo']); ?></span>
                    </li>
                    <li class="trips-detail-item">
                        <span class="label">Loại xe:</span>
                        <span><?php echo htmlspecialchars($trip['tenLoaiPhuongTien']); ?></span>
                    </li>
                    <li class="trips-detail-item">
                        <span class="label">Số chỗ:</span>
                        <span><?php echo $trip['soChoMacDinh']; ?> chỗ</span>
                    </li>
                </ul>
            </div>

            <!-- Card: Time Information -->
            <div class="trips-detail-card">
                <div class="trips-detail-card-header">
                    <i class="fas fa-clock"></i>
                    <span>Thông tin thời gian</span>
                </div>
                <ul class="trips-detail-list">
                    <li class="trips-detail-item">
                        <span class="label">Ngày khởi hành:</span>
                        <span><?php echo date('d/m/Y', strtotime($trip['ngayKhoiHanh'])); ?></span>
                    </li>
                    <li class="trips-detail-item">
                        <span class="label">Giờ khởi hành:</span>
                        <span><?php echo date('H:i', strtotime($trip['thoiGianKhoiHanh'])); ?></span>
                    </li>
                    <li class="trips-detail-item">
                        <span class="label">Giờ kết thúc dự kiến:</span>
                        <span><?php echo date('H:i', strtotime($trip['thoiGianKetThuc'])); ?></span>
                    </li>
                    <li class="trips-detail-item">
                        <span class="label">Thời gian di chuyển:</span>
                        <span><?php echo $trip['thoiGianDiChuyen']; ?></span>
                    </li>
                </ul>
            </div>

            <!-- Card: Booking Information -->
            <div class="trips-detail-card">
                <div class="trips-detail-card-header">
                    <i class="fas fa-users"></i>
                    <span>Thông tin đặt chỗ</span>
                </div>
                <ul class="trips-detail-list">
                    <li class="trips-detail-item">
                        <span class="label">Tổng số chỗ:</span>
                        <span><?php echo $trip['soChoTong']; ?> chỗ</span>
                    </li>
                    <li class="trips-detail-item">
                        <span class="label">Số chỗ đã đặt:</span>
                        <span><?php echo $trip['soChoDaDat']; ?> chỗ</span>
                    </li>
                    <li class="trips-detail-item">
                        <span class="label">Số chỗ trống:</span>
                        <span><?php echo $trip['soChoTrong']; ?> chỗ</span>
                    </li>
                    <li class="trips-detail-item">
                        <span class="label">Tỷ lệ lấp đầy:</span>
                        <div class="occupancy-display">
                            <?php $occupancy = Trip::calculateOccupancy($trip['soChoDaDat'], $trip['soChoTong']); ?>
                            <div class="occupancy-bar large">
                                <div class="occupancy-fill" style="width: <?php echo $occupancy; ?>%"></div>
                                <span class="occupancy-text"><?php echo $occupancy; ?>%</span>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Card: Pricing Information -->
            <div class="trips-detail-card">
                <div class="trips-detail-card-header">
                    <i class="fas fa-money-bill"></i>
                    <span>Thông tin giá vé</span>
                </div>
                <ul class="trips-detail-list">
                    <?php if ($trip['giaVe']): ?>
                        <li class="trips-detail-item">
                            <span class="label">Giá vé:</span>
                            <span><?php echo number_format($trip['giaVe'], 0, ',', '.'); ?> VNĐ</span>
                        </li>
                        <li class="trips-detail-item">
                            <span class="label">Loại vé:</span>
                            <span><?php echo $trip['tenLoaiVe'] ?? 'Vé thường'; ?></span>
                        </li>
                        <li class="trips-detail-item">
                            <span class="label">Loại chỗ ngồi:</span>
                            <span><?php echo $trip['loaiChoNgoi']; ?></span>
                        </li>
                        <li class="trips-detail-item">
                            <span class="label">Doanh thu dự kiến:</span>
                            <span><?php echo number_format($trip['giaVe'] * $trip['soChoDaDat'], 0, ',', '.'); ?> VNĐ</span>
                        </li>
                    <?php else: ?>
                        <li class="trips-detail-item">
                            <span class="text-muted">Chưa có thông tin giá vé</span>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Card: Pickup/Dropoff Points -->
            <?php if (!empty($points)): ?>
            <div class="trips-detail-card full-width">
                <div class="trips-detail-card-header">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Điểm đón/trả khách</span>
                </div>
                <div class="trips-detail-list">
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

            <!-- Card: System Information -->
            <div class="trips-detail-card full-width">
                <div class="trips-detail-card-header">
                    <i class="fas fa-cog"></i>
                    <span>Thông tin hệ thống</span>
                </div>
                <ul class="trips-detail-list">
                    <li class="trips-detail-item">
                        <span class="label">Ngày tạo:</span>
                        <span><?php echo date('d/m/Y H:i', strtotime($trip['ngayTao'])); ?></span>
                    </li>
                    <li class="trips-detail-item">
                        <span class="label">Cập nhật lần cuối:</span>
                        <span><?php echo date('d/m/Y H:i', strtotime($trip['ngayCapNhat'])); ?></span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="trips-detail-actions">
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
