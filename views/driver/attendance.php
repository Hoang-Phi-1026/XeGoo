<?php 
require_once __DIR__ . '/../../helpers/IDEncryptionHelper.php';
include __DIR__ . '/../layouts/header.php'; 
$encryptedTripId = IDEncryptionHelper::encryptId($tripId);
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/driver-attendance.css">

<div class="attendance-container">
    <div class="attendance-header">
        <a href="<?php echo BASE_URL; ?>/driver/report" class="back-btn">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
        <h1>Điểm Danh Hành Khách</h1>
    </div>

    <div class="trip-summary">
        <h2><i class="fas fa-bus"></i> Thông Tin Chuyến Đi</h2>
        <div class="summary-grid">
            <div class="summary-item">
                <span class="label">Mã chuyến</span>
                <span class="value"><?php echo htmlspecialchars($trip['kyHieuTuyen']); ?></span>
            </div>
            <div class="summary-item">
                <span class="label">Tuyến đường</span>
                <span class="value"><?php echo htmlspecialchars($trip['diemDi'] . ' → ' . $trip['diemDen']); ?></span>
            </div>
            <div class="summary-item">
                <span class="label">Biển số xe</span>
                <span class="value"><?php echo htmlspecialchars($trip['bienSo']); ?></span>
            </div>
            <div class="summary-item">
                <span class="label">Ngày giờ khởi hành</span>
                <span class="value"><?php echo date('d/m/Y H:i', strtotime($trip['thoiGianKhoiHanh'])); ?></span>
            </div>
            <div class="summary-item">
                <span class="label">Tổng số hành khách</span>
                <span class="value highlight"><?php echo count($passengers); ?> / <?php echo $trip['soChoTong']; ?></span>
            </div>
        </div>
    </div>

    <form method="POST" action="<?php echo BASE_URL; ?>/driver/report/confirm-departure" class="attendance-form">
        <input type="hidden" name="trip_id" value="<?php echo $encryptedTripId; ?>">
        
        <div class="passengers-section">
            <h2><i class="fas fa-users"></i> Sơ Đồ Ghế & Điểm Danh</h2>
            
            <?php if (empty($passengers)): ?>
                <div class="no-passengers">
                    <i class="fas fa-users-slash"></i>
                    <p>Chưa có hành khách đặt vé cho chuyến này</p>
                </div>
            <?php else: ?>
                <div class="legend">
                    <div class="legend-item">
                        <div class="legend-box present"></div>
                        <span>Đã lên xe</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-box absent"></div>
                        <span>Vắng mặt</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-box empty"></div>
                        <span>Ghế trống</span>
                    </div>
                </div>
 
                <div class="bus-layout">
                    <div class="bus-header">
                        <h3><i class="fas fa-steering-wheel"></i> Tài Xế</h3>
                    </div>
                    
                    <?php
                    // Create seat map for quick lookup
                    $seatMap = [];
                    foreach ($passengers as $passenger) {
                        $seatMap[$passenger['soGhe']] = $passenger;
                    }
                    
                    // Get seat layout configuration
                    $soTang = $trip['soTang'] ?? 2;
                    $totalSeats = count($allSeats);
                    
                    // For 2-deck buses (like the one in the image)
                    if ($soTang == 2 && $totalSeats == 20):
                        // Upper deck: A11-A20 (2 columns, 5 rows each side)
                        ?>
                        <div class="deck-section">
                            <h4 class="deck-title">Tầng dưới</h4>
                            <div class="deck-layout">
                                <div class="deck-side">
                                    <?php for ($i = 1; $i <= 9; $i += 2): 
                                        $seatNumber = 'A' . str_pad($i, 2, '0', STR_PAD_LEFT);
                                        $passenger = $seatMap[$seatNumber] ?? null;
                                    ?>
                                        <div class="seat-card <?php echo $passenger ? 'occupied' : 'empty'; ?>" 
                                             data-seat="<?php echo $seatNumber; ?>"
                                             id="seat-<?php echo $seatNumber; ?>">
                                            <div class="seat-number"><?php echo $seatNumber; ?></div>
                                            <?php if ($passenger): ?>
                                                <span class="passenger-name"><?php echo htmlspecialchars($passenger['hoTenHanhKhach']); ?></span>
                                            <?php else: ?>
                                                <span class="passenger-name">Trống</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                                <div class="deck-side">
                                    <?php for ($i = 2; $i <= 10; $i += 2): 
                                        $seatNumber = 'A' . str_pad($i, 2, '0', STR_PAD_LEFT);
                                        $passenger = $seatMap[$seatNumber] ?? null;
                                    ?>
                                        <div class="seat-card <?php echo $passenger ? 'occupied' : 'empty'; ?>" 
                                             data-seat="<?php echo $seatNumber; ?>"
                                             id="seat-<?php echo $seatNumber; ?>">
                                            <div class="seat-number"><?php echo $seatNumber; ?></div>
                                            <?php if ($passenger): ?>
                                                <span class="passenger-name"><?php echo htmlspecialchars($passenger['hoTenHanhKhach']); ?></span>
                                            <?php else: ?>
                                                <span class="passenger-name">Trống</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="deck-section">
                            <h4 class="deck-title">Tầng trên</h4>
                            <div class="deck-layout">
                                <div class="deck-side">
                                    <?php for ($i = 11; $i <= 20; $i += 2): 
                                        $seatNumber = 'A' . str_pad($i, 2, '0', STR_PAD_LEFT);
                                        $passenger = $seatMap[$seatNumber] ?? null;
                                    ?>
                                        <div class="seat-card <?php echo $passenger ? 'occupied' : 'empty'; ?>" 
                                             data-seat="<?php echo $seatNumber; ?>"
                                             id="seat-<?php echo $seatNumber; ?>">
                                            <div class="seat-number"><?php echo $seatNumber; ?></div>
                                            <?php if ($passenger): ?>
                                                <span class="passenger-name"><?php echo htmlspecialchars($passenger['hoTenHanhKhach']); ?></span>
                                            <?php else: ?>
                                                <span class="passenger-name">Trống</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                                <div class="deck-side">
                                    <?php for ($i = 12; $i <= 20; $i += 2): 
                                        $seatNumber = 'A' . str_pad($i, 2, '0', STR_PAD_LEFT);
                                        $passenger = $seatMap[$seatNumber] ?? null;
                                    ?>
                                        <div class="seat-card <?php echo $passenger ? 'occupied' : 'empty'; ?>" 
                                             data-seat="<?php echo $seatNumber; ?>"
                                             id="seat-<?php echo $seatNumber; ?>">
                                            <div class="seat-number"><?php echo $seatNumber; ?></div>
                                            <?php if ($passenger): ?>
                                                <span class="passenger-name"><?php echo htmlspecialchars($passenger['hoTenHanhKhach']); ?></span>
                                            <?php else: ?>
                                                <span class="passenger-name">Trống</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>

                    <?php else: 
                        // Generic layout for other bus types
                        ?>
                        <div class="bus-seats">
                            <?php foreach ($allSeats as $seatData): 
                                $seatNumber = $seatData['soGhe'];
                                $passenger = $seatMap[$seatNumber] ?? null;
                            ?>
                                <div class="seat-card <?php echo $passenger ? 'occupied' : 'empty'; ?>" 
                                     data-seat="<?php echo $seatNumber; ?>"
                                     id="seat-<?php echo $seatNumber; ?>">
                                    <div class="seat-number"><?php echo $seatNumber; ?></div>
                                    <?php if ($passenger): ?>
                                        <span class="passenger-name"><?php echo htmlspecialchars($passenger['hoTenHanhKhach']); ?></span>
                                    <?php else: ?>
                                        <span class="passenger-name">Trống</span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="trip-notes-section">
                    <h3><i class="fas fa-sticky-note"></i> Ghi Chú Chuyến Đi</h3>
                    <textarea name="trip_notes" 
                              placeholder="Nhập ghi chú chung cho chuyến đi (nếu có)..."
                              rows="3"
                              class="trip-notes-input"></textarea>
                </div>

                <h3 style="margin-top: var(--space-2xl); margin-bottom: var(--space-lg);">
                    <i class="fas fa-list"></i> Chi Tiết Hành Khách
                </h3>
                <div class="passengers-list">
                    <?php foreach ($passengers as $passenger): ?>
                        <div class="passenger-detail-card">
                            <div class="seat-indicator"> 
                                <?php echo htmlspecialchars($passenger['soGhe']); ?>
                            </div>
                            <div class="passenger-details-content">
                                <h4 style="margin: 0; color: var(--text-primary);">
                                    <?php echo htmlspecialchars($passenger['hoTenHanhKhach']); ?>
                                </h4>
                                <?php if ($passenger['soDienThoaiHanhKhach']): ?>
                                    <div class="detail-row">
                                        <i class="fas fa-phone"></i>
                                        <span><?php echo htmlspecialchars($passenger['soDienThoaiHanhKhach']); ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="detail-row">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><strong>Đón:</strong> <?php echo htmlspecialchars($passenger['diemDonTen']); ?>
                                        <?php if ($passenger['diemDonDiaChi']): ?>
                                            - <?php echo htmlspecialchars($passenger['diemDonDiaChi']); ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="detail-row">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><strong>Trả:</strong> <?php echo htmlspecialchars($passenger['diemTraTen']); ?>
                                        <?php if ($passenger['diemTraDiaChi']): ?>
                                            - <?php echo htmlspecialchars($passenger['diemTraDiaChi']); ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="detail-row">
                                    <i class="fas fa-ticket-alt"></i>
                                    <span>Mã vé: <?php echo htmlspecialchars($passenger['maDatVe']); ?></span>
                                </div>

                                <div class="attendance-controls">
                                    <label class="control-label">Trạng thái điểm danh:</label>
                                    <div class="status-toggle">
                                        <label class="status-option">
                                            <input type="radio" 
                                                   name="attendance[<?php echo $passenger['maChiTiet']; ?>][status]" 
                                                   value="Đã lên xe" 
                                                   data-seat="<?php echo $passenger['soGhe']; ?>"
                                                   class="attendance-radio"
                                                   checked>
                                            <span class="status-btn present-btn">
                                                <i class="fas fa-check-circle"></i> Đã lên xe
                                            </span>
                                        </label>
                                        <label class="status-option">
                                            <input type="radio" 
                                                   name="attendance[<?php echo $passenger['maChiTiet']; ?>][status]" 
                                                   value="Vắng mặt"
                                                   data-seat="<?php echo $passenger['soGhe']; ?>"
                                                   class="attendance-radio">
                                            <span class="status-btn absent-btn">
                                                <i class="fas fa-times-circle"></i> Vắng mặt
                                            </span>
                                        </label>
                                    </div>
                                    <div class="passenger-note">
                                        <textarea name="attendance[<?php echo $passenger['maChiTiet']; ?>][note]" 
                                                  placeholder="Ghi chú cho hành khách này..."
                                                  rows="2"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-confirm" id="confirmDepartureBtn">
                        <i class="fas fa-check-circle"></i>
                        Xác nhận báo cáo
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </form>
</div>
<!-- Added confirmation modal for departure -->
<div id="confirmModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <i class="fas fa-exclamation-triangle"></i>
            <h3>Xác Nhận Báo Cáo</h3>
        </div>
        <div class="modal-body">
            <p>Bạn có chắc chắn muốn xác nhận báo cáo chuyến đi này không?</p>
            <p class="modal-warning">
                <i class="fas fa-info-circle"></i>
                Sau khi xác nhận, báo cáo sẽ được lưu với trạng thái "Chờ khởi hành".
            </p>
            <div class="modal-summary">
                <div class="summary-row">
                    <span>Tổng số hành khách:</span>
                    <strong><?php echo count($passengers); ?> người</strong>
                </div>
                <div class="summary-row">
                    <span>Đã lên xe:</span>
                    <strong id="presentCount">0</strong>
                </div>
                <div class="summary-row">
                    <span>Vắng mặt:</span>
                    <strong id="absentCount">0</strong>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" id="cancelBtn">
                <i class="fas fa-times"></i> Hủy
            </button>
            <button type="button" class="btn-confirm-modal" id="confirmBtn">
                <i class="fas fa-check"></i> Xác Nhận Báo Cáo
            </button>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('[v0] Initializing attendance page');
    
    // Initialize all seats based on default radio selection (Đã lên xe is checked by default)
    document.querySelectorAll('.attendance-radio:checked').forEach(radio => {
        updateSeatColor(radio);
    });
    
    // Restore saved form data if exists
    restoreFormData();
});

function updateSeatColor(radio) {
    const seatNumber = radio.getAttribute('data-seat');
    const status = radio.value;
    const seatCard = document.getElementById('seat-' + seatNumber);
    
    console.log('[v0] Updating seat:', seatNumber, 'Status:', status);
    
    if (seatCard) {
        // Remove all status classes
        seatCard.classList.remove('occupied', 'present', 'absent', 'empty');
        
        // Add appropriate class based on selection
        if (status === 'Đã lên xe') {
            seatCard.classList.add('present');
        } else if (status === 'Vắng mặt') {
            seatCard.classList.add('absent');
        }
    }
}

document.querySelectorAll('.attendance-radio').forEach(radio => {
    radio.addEventListener('change', function() {
        updateSeatColor(this);
        saveFormData();
    });
});

// Auto-save form data
function saveFormData() {
    const formData = new FormData(document.querySelector('.attendance-form'));
    const data = {};
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }
    localStorage.setItem('attendance_draft_<?php echo $tripId; ?>', JSON.stringify(data));
    console.log('[v0] Form data saved');
}

document.querySelectorAll('textarea').forEach(textarea => {
    textarea.addEventListener('input', saveFormData);
});

// Restore form data
function restoreFormData() {
    const savedData = localStorage.getItem('attendance_draft_<?php echo $tripId; ?>');
    if (savedData) {
        console.log('[v0] Restoring saved form data');
        const data = JSON.parse(savedData);
        for (let [key, value] of Object.entries(data)) {
            const element = document.querySelector(`[name="${key}"]`);
            if (element) {
                if (element.type === 'radio') {
                    const radio = document.querySelector(`[name="${key}"][value="${value}"]`);
                    if (radio) {
                        radio.checked = true;
                        updateSeatColor(radio);
                    }
                } else {
                    element.value = value;
                }
            }
        }
    }
}

// Clear localStorage on submit
document.querySelector('.attendance-form').addEventListener('submit', function() {
    localStorage.removeItem('attendance_draft_<?php echo $tripId; ?>');
    console.log('[v0] Form submitted, cleared saved data');
});
const modal = document.getElementById('confirmModal');
const confirmDepartureBtn = document.getElementById('confirmDepartureBtn');
const cancelBtn = document.getElementById('cancelBtn');
const confirmBtn = document.getElementById('confirmBtn');
const attendanceForm = document.querySelector('.attendance-form');

confirmDepartureBtn.addEventListener('click', function() {
    // Calculate attendance statistics
    const presentRadios = document.querySelectorAll('.attendance-radio[value="Đã lên xe"]:checked');
    const absentRadios = document.querySelectorAll('.attendance-radio[value="Vắng mặt"]:checked');
    
    document.getElementById('presentCount').textContent = presentRadios.length + ' người';
    document.getElementById('absentCount').textContent = absentRadios.length + ' người';
    
    // Show modal
    modal.style.display = 'flex';
});

cancelBtn.addEventListener('click', function() {
    modal.style.display = 'none';
});

confirmBtn.addEventListener('click', function() {
    // Submit the form
    attendanceForm.submit();
});

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    if (event.target === modal) {
        modal.style.display = 'none';
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape' && modal.style.display === 'flex') {
        modal.style.display = 'none';
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
