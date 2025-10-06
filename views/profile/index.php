<?php include 'views/layouts/header.php'; ?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/notifications.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/profile.css">

<?php
$stmt = $this->db->prepare("SELECT COALESCE(SUM(diem), 0) as total_points FROM diem_tichluy WHERE maNguoiDung = ?");
$stmt->execute([$_SESSION['user_id']]);
$loyaltyData = $stmt->fetch(PDO::FETCH_ASSOC);
$totalPoints = max(0, (int)$loyaltyData['total_points']);
?>

<div class="profile-wrapper">
    <div class="profile-cover">
        <div class="cover-gradient"></div>
        <div class="profile-hero">
            <div class="profile-avatar-section">
                <div class="avatar-container">
                    <img id="avatar-preview" 
                         src="<?php echo !empty($user['avt']) ? BASE_URL . '/' . $user['avt'] : BASE_URL . '/public/images/default-avatar.jpg'; ?>" 
                         alt="Avatar">
                    <label for="avatar" class="avatar-edit-btn" title="Thay đổi ảnh đại diện">
                        <i class="fas fa-camera"></i>
                    </label>
                    <form id="avatar-form" action="<?php echo BASE_URL; ?>/profile/upload-avatar" method="POST" enctype="multipart/form-data" style="display: none;">
                        <input type="file" id="avatar" name="avatar" accept="image/*" onchange="document.getElementById('avatar-form').submit()">
                    </form>
                </div>
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($user['tenNguoiDung']); ?></h1>
                    <p class="profile-email"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['eMail']); ?></p>
                    <div class="profile-badges">
                        <span class="badge <?php echo $user['maTrangThai'] == 0 ? 'badge-success' : 'badge-danger'; ?>">
                            <i class="fas fa-circle"></i>
                            <?php echo $user['maTrangThai'] == 0 ? 'Hoạt động' : 'Đã khóa'; ?>
                        </span>
                        <span class="badge badge-primary">
                            <i class="fas fa-user-tag"></i>
                            <?php 
                            $roles = [1 => 'Quản Trị Viên', 2 => 'Nhân Viên', 3 => 'Tài Xế', 4 => 'Khách Hàng'];
                            echo $roles[$user['maVaiTro']] ?? 'Không xác định';
                            ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="loyalty-quick-view">
                <div class="loyalty-points-display">
                    <div class="points-icon">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="points-details">
                        <span class="points-label">Điểm tích lũy</span>
                        <span class="points-value"><?php echo number_format($totalPoints); ?></span>
                        <span class="points-money">≈ <?php echo number_format($totalPoints * 100); ?>đ</span>
                    </div>
                </div>
                <a href="<?php echo BASE_URL; ?>/loyalty" class="btn-view-loyalty">
                    <i class="fas fa-arrow-right"></i>
                    Xem chi tiết
                </a>
            </div>
        </div>
    </div>

    <div class="profile-container">
        <div class="profile-main">
            <section class="profile-section">
                <div class="section-header">
                    <h2><i class="fas fa-user"></i> Thông tin cá nhân</h2>
                    <p>Cập nhật thông tin của bạn</p>
                </div>
                <form action="<?php echo BASE_URL; ?>/profile/update" method="POST" onsubmit="return validateProfileForm(this)">
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="fullname">Họ và tên *</label>
                            <div class="input-with-icon">
                                <i class="fas fa-user"></i>
                                <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['tenNguoiDung']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="email">Email *</label>
                            <div class="input-with-icon">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['eMail']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="phone">Số điện thoại</label>
                            <div class="input-with-icon">
                                <i class="fas fa-phone"></i>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['soDienThoai'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="gender">Giới tính</label>
                            <div class="input-with-icon">
                                <i class="fas fa-venus-mars"></i>
                                <select id="gender" name="gender">
                                    <option value="">Chọn giới tính</option>
                                    <option value="Nam" <?php echo ($user['gioiTinh'] ?? '') === 'Nam' ? 'selected' : ''; ?>>Nam</option>
                                    <option value="Nữ" <?php echo ($user['gioiTinh'] ?? '') === 'Nữ' ? 'selected' : ''; ?>>Nữ</option>
                                    <option value="Khác" <?php echo ($user['gioiTinh'] ?? '') === 'Khác' ? 'selected' : ''; ?>>Khác</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-field full-width">
                            <label for="address">Địa chỉ</label>
                            <div class="input-with-icon">
                                <i class="fas fa-map-marker-alt"></i>
                                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['diaChi'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-field full-width">
                            <label for="description">Giới thiệu bản thân</label>
                            <textarea id="description" name="description" rows="3" placeholder="Viết vài dòng về bản thân..."><?php echo htmlspecialchars($user['moTa'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Lưu thay đổi
                        </button>
                    </div>
                </form>
            </section>
 
            <section class="profile-section">
                <div class="section-header">
                    <h2><i class="fas fa-shield-alt"></i> Bảo mật</h2>
                    <p>Quản lý mật khẩu và bảo mật tài khoản</p>
                </div>
                <form action="<?php echo BASE_URL; ?>/profile/change-password" method="POST" onsubmit="return validatePasswordForm(this)">
                    <div class="form-grid">
                        <div class="form-field full-width">
                            <label for="current_password">Mật khẩu hiện tại *</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="current_password" name="current_password" required>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="new_password">Mật khẩu mới *</label>
                            <div class="input-with-icon">
                                <i class="fas fa-key"></i>
                                <input type="password" id="new_password" name="new_password" minlength="6" required>
                            </div>
                        </div>
                        
                        <div class="form-field">
                            <label for="confirm_password">Xác nhận mật khẩu *</label>
                            <div class="input-with-icon">
                                <i class="fas fa-key"></i>
                                <input type="password" id="confirm_password" name="confirm_password" minlength="6" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-secondary">
                            <i class="fas fa-shield-alt"></i>
                            Đổi mật khẩu
                        </button>
                    </div>
                </form>
            </section>

            <section class="profile-section account-info">
                <div class="section-header">
                    <h2><i class="fas fa-info-circle"></i> Thông tin tài khoản</h2>
                </div>
                <div class="info-list">
                    <div class="info-row">
                        <span class="info-label"><i class="fas fa-calendar-plus"></i> Ngày tạo tài khoản</span>
                        <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($user['ngayTao'])); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label"><i class="fas fa-id-badge"></i> Mã người dùng</span>
                        <span class="info-value">#<?php echo str_pad($user['maNguoiDung'], 6, '0', STR_PAD_LEFT); ?></span>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>/public/js/main.js"></script>

<?php include 'views/layouts/footer.php'; ?>
