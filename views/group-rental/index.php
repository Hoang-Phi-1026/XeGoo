<?php
// Get session data if exists
$rentalData = $_SESSION['rental_data'] ?? [];
$rentalErrors = $_SESSION['rental_errors'] ?? [];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thuê Xe Trọn Gói - XeGoo</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/main.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/grouprental.css">
</head>
<body>
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <h1>Thuê xe trọn gói cùng XeGoo</h1>
            <p>Giải pháp hoàn hảo cho nhóm bạn, gia đình hay doanh nghiệp cần di chuyển cùng nhau.
Linh hoạt về thời gian, thoải mái trên từng hành trình – XeGoo cam kết mang đến trải nghiệm tiện lợi, an toàn và chi phí tối ưu nhất.</p>
        </div>
    </div>

    <!-- Form Section -->
    <div class="container">
        <div class="form-section">
            <!-- Error Messages -->
            <?php if (!empty($rentalErrors)): ?>
                <div class="errors-container">
                    <h3>Vui lòng kiểm tra các lỗi sau:</h3>
                    <ul>
                        <?php foreach ($rentalErrors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo BASE_URL; ?>/group-rental/submit">
                <!-- Contact Information Section -->
                <h2>Thông tin liên hệ</h2>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="ho_ten">Họ tên người thuê xe <span class="required">*</span></label>
                        <input type="text" id="ho_ten" name="ho_ten" 
                               value="<?php echo htmlspecialchars($rentalData['ho_ten'] ?? ''); ?>" 
                               required>
                    </div>
                    <div class="form-group">
                        <label for="so_dien_thoai">Số điện thoại <span class="required">*</span></label>
                        <input type="tel" id="so_dien_thoai" name="so_dien_thoai" 
                               value="<?php echo htmlspecialchars($rentalData['so_dien_thoai'] ?? ''); ?>" 
                               placeholder="0xxxxxxxxx" required>
                    </div>
                </div>

                <div class="form-row full">
                    <div class="form-group">
                        <label for="email">Email <span class="required">*</span></label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($rentalData['email'] ?? ''); ?>" 
                               required>
                    </div>
                </div>

                <!-- Rental Information Section -->
                <h2 style="margin-top: 40px;">Thông tin thuê xe</h2>

                <!-- Trip Type Selection -->
                <div class="form-group">
                    <label>Loại hành trình <span class="required">*</span></label>
                    <div class="trip-type-selector">
                        <div class="trip-type-option">
                            <input type="radio" id="one_way" name="loai_hanh_trinh" value="Một chiều" 
                                   <?php echo (($rentalData['loai_hanh_trinh'] ?? 'Một chiều') === 'Một chiều') ? 'checked' : ''; ?> 
                                   onchange="toggleReturnFields()">
                            <label for="one_way">Một chiều</label>
                        </div>
                        <div class="trip-type-option">
                            <input type="radio" id="round_trip" name="loai_hanh_trinh" value="Khứ hồi" 
                                   <?php echo (($rentalData['loai_hanh_trinh'] ?? '') === 'Khứ hồi') ? 'checked' : ''; ?> 
                                   onchange="toggleReturnFields()">
                            <label for="round_trip">Khứ hồi</label>
                        </div>
                    </div>
                </div>

                <!-- Departure Information -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="diem_di">Điểm đi <span class="required">*</span></label>
                        <input type="text" id="diem_di" name="diem_di" 
                               value="<?php echo htmlspecialchars($rentalData['diem_di'] ?? ''); ?>" 
                               placeholder="Nhập tỉnh/thành phố" required>
                    </div>
                    <div class="form-group">
                        <label for="diem_den">Điểm đến <span class="required">*</span></label>
                        <input type="text" id="diem_den" name="diem_den" 
                               value="<?php echo htmlspecialchars($rentalData['diem_den'] ?? ''); ?>" 
                               placeholder="Nhập tỉnh/thành phố" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="ngay_di">Ngày đi <span class="required">*</span></label>
                        <input type="date" id="ngay_di" name="ngay_di" 
                               value="<?php echo htmlspecialchars($rentalData['ngay_di'] ?? ''); ?>" 
                               required>
                    </div>
                    <div class="form-group">
                        <label for="gio_di">Giờ đi <span class="required">*</span></label>
                        <input type="time" id="gio_di" name="gio_di" 
                               value="<?php echo htmlspecialchars($rentalData['gio_di'] ?? ''); ?>" 
                               required>
                    </div>
                </div>

                <div class="form-row full">
                    <div class="form-group">
                        <label for="diem_don_di">Điểm đón <span class="required">*</span></label>
                        <input type="text" id="diem_don_di" name="diem_don_di" 
                               value="<?php echo htmlspecialchars($rentalData['diem_don_di'] ?? ''); ?>" 
                               placeholder="Nhập địa chỉ cụ thể" required>
                    </div>
                </div>

                <!-- Return Trip Information (Hidden by default) -->
                <div class="return-trip-fields <?php echo (($rentalData['loai_hanh_trinh'] ?? '') === 'Khứ hồi') ? 'active' : ''; ?>">
                    <h3 style="margin-bottom: 20px; color: #2c3e50;">Thông tin chuyến về</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="ngay_ve">Ngày về <span class="required">*</span></label>
                            <input type="date" id="ngay_ve" name="ngay_ve" 
                                   value="<?php echo htmlspecialchars($rentalData['ngay_ve'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="gio_ve">Giờ về <span class="required">*</span></label>
                            <input type="time" id="gio_ve" name="gio_ve" 
                                   value="<?php echo htmlspecialchars($rentalData['gio_ve'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-row full">
                        <div class="form-group">
                            <label for="diem_don_ve">Điểm đón về <span class="required">*</span></label>
                            <input type="text" id="diem_don_ve" name="diem_don_ve" 
                                   value="<?php echo htmlspecialchars($rentalData['diem_don_ve'] ?? ''); ?>" 
                                   placeholder="Nhập địa chỉ cụ thể">
                        </div>
                    </div>
                </div>

                <!-- Passenger and Vehicle Information -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="so_luong_nguoi">Số lượng người <span class="required">*</span></label>
                        <input type="number" id="so_luong_nguoi" name="so_luong_nguoi" 
                               value="<?php echo htmlspecialchars($rentalData['so_luong_nguoi'] ?? ''); ?>" 
                               min="1" placeholder="Nhập số lượng" required>
                    </div>
                    <div class="form-group">
                        <label for="loai_xe">Loại xe <span class="required">*</span></label>
                        <select id="loai_xe" name="loai_xe" required>
                            <option value="">-- Chọn loại xe --</option>
                            <?php foreach ($vehicleTypes as $vehicle): ?>
                                <option value="<?php echo $vehicle['maLoaiPhuongTien']; ?>" 
                                        <?php echo (($rentalData['loai_xe'] ?? '') == $vehicle['maLoaiPhuongTien']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($vehicle['tenLoaiPhuongTien']); ?> 
                                    (<?php echo $vehicle['soChoMacDinh']; ?> chỗ)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Notes -->
                <div class="form-row full">
                    <div class="form-group">
                        <label for="ghi_chu">Ghi chú</label>
                        <textarea id="ghi_chu" name="ghi_chu" 
                                  placeholder="Nhập các yêu cầu đặc biệt (nếu có)..."><?php echo htmlspecialchars($rentalData['ghi_chu'] ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="submit-btn">Thuê Xe & Nhận Báo Giá</button>
            </form>
        </div>
    </div>

    <script>
        function toggleReturnFields() {
            const roundTrip = document.getElementById('round_trip').checked;
            const returnFields = document.querySelector('.return-trip-fields');
            
            if (roundTrip) {
                returnFields.classList.add('active');
            } else {
                returnFields.classList.remove('active');
            }
        }

        // Set minimum date to today
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('ngay_di').setAttribute('min', today);
            document.getElementById('ngay_ve').setAttribute('min', today);
        });
    </script>
</body>
</html>
