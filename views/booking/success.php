<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="success-container">
    <div class="success-header">
        <div class="success-icon">✅</div>
        <h1>Đặt vé thành công!</h1>
        <p class="success-message">Cảm ơn bạn đã sử dụng dịch vụ của XeGoo. Thông tin đặt vé của bạn như sau:</p>
    </div>

    <div class="booking-info-card">
        <div class="booking-header">
            <h2>Thông tin đặt vé</h2>
            <div class="booking-id">
                <span class="label">Mã đặt vé:</span>
                <span class="value"><?php echo htmlspecialchars($bookingId); ?></span>
            </div>
        </div>

        <?php if (!empty($booking)): ?>
            <?php 
            $trips = [];
            foreach ($booking as $detail) {
                $tripKey = $detail['ngayKhoiHanh'] . '_' . $detail['thoiGianKhoiHanh'] . '_' . $detail['diemDi'] . '_' . $detail['diemDen'];
                if (!isset($trips[$tripKey])) {
                    $trips[$tripKey] = $detail;
                    $trips[$tripKey]['trip_type'] = 'outbound'; // Default to outbound
                }
            }
            
            // For one-way trips, there should only be one trip section
            $tripCount = 0;
            ?>
            
            <?php foreach ($trips as $tripKey => $detail): ?>
                <?php $tripCount++; ?>
                <div class="trip-section">
                    <div class="trip-header">
                        <h3>
                            <?php 
                            if ($tripCount == 1) {
                                echo 'Chuyến đi';
                            } else {
                                echo 'Chuyến về';
                            }
                            ?>
                        </h3>
                        <div class="trip-date">
                            <?php echo date('d/m/Y', strtotime($detail['ngayKhoiHanh'])); ?>
                        </div>
                    </div>

                    <div class="trip-details">
                        <div class="route-info">
                            <div class="route-display">
                                <div class="route-point">
                                    <div class="point-time"><?php echo date('H:i', strtotime($detail['thoiGianKhoiHanh'])); ?></div>
                                    <div class="point-name"><?php echo htmlspecialchars($detail['diemDi']); ?></div>
                                </div>
                                <div class="route-arrow">→</div>
                                <div class="route-point">
                                    <div class="point-time">
                                        <?php 
                                        // Calculate end time based on travel duration
                                        $startTime = strtotime($detail['thoiGianKhoiHanh']);
                                        $endTime = $startTime + (6 * 3600); // Default 6 hours if no duration
                                        echo date('H:i', $endTime);
                                        ?>
                                    </div>
                                    <div class="point-name"><?php echo htmlspecialchars($detail['diemDen']); ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="trip-info-grid">
                            <div class="info-item">
                                <span class="value">Tuyến: <?php echo htmlspecialchars($detail['kyHieuTuyen'] ?? 'N/A'); ?></span>
                            </div>
                        </div>

                        <div class="trip-info-grid">
                            <div class="info-item">
                                <span class="value">Điểm đón: <?php echo htmlspecialchars($detail['diemDonTen'] ?? 'N/A'); ?></span>
                                <span class="label"><?php echo htmlspecialchars($detail['diemDonDiaChi'] ?? 'N/A'); ?></span>
                            </div>
                        </div>
                        
                        <div class="trip-info-grid">
                            <div class="info-item">
                                <span class="value">Điểm trả: <?php echo htmlspecialchars($detail['diemTraTen'] ?? 'N/A'); ?></span>
                                <span class="label"><?php echo htmlspecialchars($detail['diemTraDiaChi'] ?? 'N/A'); ?></span>
                            </div>
                        </div>

                        <div class="trip-info-grid">
                            <div class="info-item">
                                <span class="value">Phương thức thanh toán:</span>
                                <span class="label"><?php echo htmlspecialchars($detail['phuongThucThanhToan'] ?? 'N/A'); ?></span>
                            </div>
                        </div>

                        <!-- thong tin khach hàng -->
                        <?php
                        $sql = "SELECT cd.*, g.soGhe, c.ngayKhoiHanh, c.thoiGianKhoiHanh
                                FROM chitiet_datve cd
                                LEFT JOIN ghe g ON cd.maGhe = g.maGhe
                                LEFT JOIN chuyenxe c ON cd.maChuyenXe = c.maChuyenXe
                                WHERE cd.maDatVe = ? 
                                AND c.ngayKhoiHanh = ? 
                                AND c.thoiGianKhoiHanh = ?
                                ORDER BY g.soGhe";
                        $passengers = fetchAll($sql, [$bookingId, $detail['ngayKhoiHanh'], $detail['thoiGianKhoiHanh']]);
                        ?>

                        <?php if (!empty($passengers)): ?>
                            <div class="passengers-section">
                                <h2>Thông tin hành khách</h2>
                                <div class="passengers-list">
                                    <?php foreach ($passengers as $passenger): ?>
                                        <div class="passenger-item">
                                            <div class="passenger-info">
                                                <div class="passenger-name"><?php echo htmlspecialchars($passenger['hoTenHanhKhach']); ?></div>
                                                <div class="passenger-details">
                                                    <?php if (!empty($passenger['emailHanhKhach'])): ?>
                                                        <span>Email: <?php echo htmlspecialchars($passenger['emailHanhKhach']); ?></span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($passenger['soDienThoaiHanhKhach'])): ?>
                                                        <span>SĐT: <?php echo htmlspecialchars($passenger['soDienThoaiHanhKhach']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="seat-info">
                                                <span class="seat-badge">Ghế <?php echo htmlspecialchars($passenger['soGhe'] ?? 'N/A'); ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Get pricing information from datve table instead of calculating from individual passenger prices -->
                        <?php
                        // Get the main booking record to get correct pricing
                        $bookingSql = "SELECT tongTien, giamGia, tongTienSauGiam FROM datve WHERE maDatVe = ?";
                        $bookingInfo = fetch($bookingSql, [$bookingId]);
                        ?>
                        
                        <div class="price-info">
                            <?php 
                            $totalOriginal = 0;
                            $totalAfterDiscount = 0;
                            foreach ($passengers as $passenger) {
                                $totalOriginal += $passenger['giaVe'] ?? 0;
                                $totalAfterDiscount += $passenger['giaVe'] ?? 0; // Assuming no discount for individual passengers
                            }
                            ?>
                            <div class="price-item">
                                <span class="label">Giá vé:</span>
                                <span class="value"><?php echo number_format($totalOriginal, 0, ',', '.'); ?>đ</span>
                            </div>
                        </div>
                        
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="price-info">
                            <div class="price-item">
                                <span class="label">Tổng tiền:</span>
                                <span class="value"><?php echo number_format($bookingInfo['tongTien'] ?? 0, 0, ',', '.'); ?>đ</span>
                            </div>
                            <?php if (($bookingInfo['giamGia'] ?? 0) > 0): ?>
                                <div class="price-item">
                                    <span class="label">Giảm giá:</span>
                                    <span class="value discount">-<?php echo number_format($bookingInfo['giamGia'], 0, ',', '.'); ?>đ</span>
                                </div>
                            <?php endif; ?>
                            <div class="price-item total">
                                <span class="label">Đã thanh toán:</span>
                                <span class="value price"><?php echo number_format($bookingInfo['tongTienSauGiam'] ?? 0, 0, ',', '.'); ?>đ</span>
                            </div>
                        </div>
            <?php else: ?>

            <div class="no-details">
                <p>Không tìm thấy thông tin đặt vé. Vui lòng liên hệ hỗ trợ khách hàng.</p>
                <p><strong>Mã đặt vé:</strong> <?php echo htmlspecialchars($bookingId); ?></p>
            </div>

        <?php endif; ?>
    </div>

    <div class="action-buttons">
        <a href="<?php echo BASE_URL; ?>/search" class="btn btn-primary">Đặt vé mới</a>
        <a href="<?php echo BASE_URL; ?>/dashboard" class="btn btn-secondary">Xem lịch sử đặt vé</a>
        <button onclick="window.print()" class="btn btn-outline">In vé</button>
    </div>

    <div class="support-info">
        <h4>Cần hỗ trợ?</h4>
        <div class="support-contacts">
            <div class="support-item">
                <span class="icon">📞</span>
                <span>Hotline: 1900 1234</span>
            </div>
            <div class="support-item">
                <span class="icon">✉️</span>
                <span>Email: support@xegoo.com</span>
            </div>
        </div>
        <p class="support-note">
            Vui lòng có mặt tại điểm đón trước giờ khởi hành ít nhất 15 phút và mang theo CCCD/CMND để đối chiếu thông tin.
        </p>
    </div>
</div>

<style>
.success-container {
    max-width: 800px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.success-header {
    text-align: center;
    margin-bottom: 2rem;
}

.success-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.success-header h1 {
    color: #22c55e;
    margin-bottom: 0.5rem;
}

.success-message {
    color: #6b7280;
    font-size: 1.1rem;
}

.booking-info-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    padding: 2rem;
    margin-bottom: 2rem;
}

.booking-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e5e7eb;
}

.booking-id {
    background: #f3f4f6;
    padding: 0.5rem 1rem;
    border-radius: 8px;
}

.booking-id .label {
    font-weight: 600;
    color: #374151;
}

.booking-id .value {
    font-weight: 700;
    color: #1f2937;
    font-family: monospace;
}

.trip-section {
    margin-bottom: 2rem;
    padding: 1.5rem;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
}

.trip-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.trip-header h3 {
    color: #1f2937;
    margin: 0;
}

.trip-date {
    background: #dbeafe;
    color: #1e40af;
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    font-weight: 600;
}

.route-display {
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 1.5rem 0;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 8px;
}

.route-point {
    text-align: center;
    flex: 1;
}

.point-time {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1f2937;
}

.point-name {
    font-weight: 600;
    color: #374151;
    margin-top: 0.25rem;
}

.route-arrow {
    font-size: 1.5rem;
    color: #6b7280;
    margin: 0 2rem;
}

.trip-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin: 1.5rem 0;
}

.info-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f3f4f6;
}

.info-item .label {
    color: #6b7280;
    font-weight: 500;
}

.info-item .value {
    color: #1f2937;
    font-weight: 600;
}

.passengers-section {
    margin: 1.5rem 0;
}

.passengers-section h4 {
    color: #1f2937;
    margin-bottom: 1rem;
}

.passengers-list {
    display: grid;
    gap: 0.75rem;
}

.passenger-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 8px;
}

.passenger-name {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

.passenger-details {
    font-size: 0.875rem;
    color: #6b7280;
}

.passenger-details span {
    margin-right: 1rem;
}

.seat-badge {
    background: #dbeafe;
    color: #1e40af;
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.875rem;
}

.price-info {
    margin-top: 1.5rem;
    padding-top: 1rem;
    border-top: 2px solid #e5e7eb;
}

.price-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
}

.price-item .price {
    font-size: 1.25rem;
    font-weight: 700;
    color: #dc2626;
}

.price-item.total {
    font-weight: 700;
    font-size: 1.1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

.discount {
    color: #dc2626;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-bottom: 2rem;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background: #2563eb;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
}

.btn-outline {
    background: white;
    color: #374151;
    border: 2px solid #d1d5db;
}

.btn-outline:hover {
    background: #f9fafb;
}

.support-info {
    background: #f9fafb;
    padding: 1.5rem;
    border-radius: 8px;
    text-align: center;
}

.support-info h4 {
    color: #1f2937;
    margin-bottom: 1rem;
}

.support-contacts {
    display: flex;
    justify-content: center;
    gap: 2rem;
    margin-bottom: 1rem;
}

.support-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #374151;
    font-weight: 500;
}

.support-note {
    color: #6b7280;
    font-size: 0.875rem;
    font-style: italic;
    margin: 0;
}

.no-details {
    text-align: center;
    padding: 2rem;
    color: #6b7280;
}

@media (max-width: 768px) {
    .booking-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .route-display {
        flex-direction: column;
        gap: 1rem;
    }
    
    .route-arrow {
        transform: rotate(90deg);
        margin: 1rem 0;
    }
    
    .trip-info-grid {
        grid-template-columns: 1fr;
    }
    
    .passenger-item {
        flex-direction: column;
        gap: 0.5rem;
        text-align: center;
    }
    
    .support-contacts {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}

@media print {
    .action-buttons,
    .support-info {
        display: none;
    }
    
    .success-container {
        margin: 0;
        padding: 1rem;
    }
    
    .booking-info-card {
        box-shadow: none;
        border: 1px solid #000;
    }
}
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
