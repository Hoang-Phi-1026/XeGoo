<?php include 'views/layouts/header.php'; ?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/notifications.css">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/profile.css">

<div class="profile-container">
    <div class="profile-header">
        <h1>Thông tin cá nhân</h1>
        <p>Quản lý thông tin tài khoản và bảo mật của bạn</p>
    </div>

    <div class="profile-content">
        <!-- Avatar Section -->
        <div class="profile-card">
            <div class="card-header">
                <h2>Ảnh đại diện</h2>
            </div>
            <div class="card-body">
                <div class="avatar-section">
                    <div class="avatar-preview">
                        <!-- Updated to use avt field from nguoidung table -->
                        <img id="avatar-preview" 
                             src="<?php echo !empty($user['avt']) ? BASE_URL . '/' . $user['avt'] : BASE_URL . '/public/images/default-avatar.jpg'; ?>" 
                             alt="Avatar">
                    </div>
                    <form action="<?php echo BASE_URL; ?>/profile/upload-avatar" method="POST" enctype="multipart/form-data" class="avatar-form">
                        <div class="form-group">
                            <label for="avatar" class="file-label">
                                <i class="icon-upload"></i>
                                Chọn ảnh mới
                            </label>
                            <input type="file" id="avatar" name="avatar" accept="image/*" onchange="previewImage(this)" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Cập nhật ảnh</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Personal Information -->
        <div class="profile-card">
            <div class="card-header">
                <h2>Thông tin cá nhân</h2>
            </div>
            <div class="card-body">
                <form action="<?php echo BASE_URL; ?>/profile/update" method="POST" onsubmit="return validateProfileForm(this)">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fullname">Họ và tên *</label>
                            <!-- Updated to use tenNguoiDung field -->
                            <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['tenNguoiDung']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <!-- Updated to use eMail field -->
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['eMail']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Số điện thoại</label>
                            <!-- Updated to use soDienThoai field -->
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['soDienThoai'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="address">Địa chỉ</label>
                            <!-- Updated to use diaChi field -->
                            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['diaChi'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <!-- Added gender and description fields from database -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="gender">Giới tính</label>
                            <select id="gender" name="gender">
                                <option value="">Chọn giới tính</option>
                                <option value="Nam" <?php echo ($user['gioiTinh'] ?? '') === 'Nam' ? 'selected' : ''; ?>>Nam</option>
                                <option value="Nữ" <?php echo ($user['gioiTinh'] ?? '') === 'Nữ' ? 'selected' : ''; ?>>Nữ</option>
                                <option value="Khác" <?php echo ($user['gioiTinh'] ?? '') === 'Khác' ? 'selected' : ''; ?>>Khác</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Mô tả</label>
                        <textarea id="description" name="description" rows="3" placeholder="Giới thiệu về bản thân..."><?php echo htmlspecialchars($user['moTa'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Cập nhật thông tin</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Change Password -->
        <div class="profile-card">
            <div class="card-header">
                <h2>Đổi mật khẩu</h2>
            </div>
            <div class="card-body">
                <form action="<?php echo BASE_URL; ?>/profile/change-password" method="POST" onsubmit="return validatePasswordForm(this)">
                    <div class="form-group">
                        <label for="current_password">Mật khẩu hiện tại *</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="new_password">Mật khẩu mới *</label>
                            <input type="password" id="new_password" name="new_password" minlength="6" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Xác nhận mật khẩu *</label>
                            <input type="password" id="confirm_password" name="confirm_password" minlength="6" required>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-secondary">Đổi mật khẩu</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Account Information -->
        <div class="profile-card">
            <div class="card-header">
                <h2>Thông tin tài khoản</h2>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <label>Ngày tạo tài khoản:</label>
                        <!-- Updated to use ngayTao field -->
                        <span><?php echo date('d/m/Y H:i', strtotime($user['ngayTao'])); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Trạng thái tài khoản:</label>
                        <!-- Updated to show status based on maTrangThai -->
                        <span class="<?php echo $user['maTrangThai'] == 0 ? 'status-active' : 'status-locked'; ?>">
                            <?php echo $user['maTrangThai'] == 0 ? 'Đang hoạt động' : 'Đã khóa'; ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <label>Vai trò:</label>
                        <!-- Added role display based on maVaiTro -->
                        <span>
                            <?php 
                            $roles = [1 => 'Quản Trị Viên', 2 => 'Nhân Viên Hỗ Trợ', 3 => 'Tài Xế', 4 => 'Khách Hàng'];
                            echo $roles[$user['maVaiTro']] ?? 'Không xác định';
                            ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fixed JavaScript path to use BASE_URL for consistency -->
<script src="<?php echo BASE_URL; ?>/public/js/main.js"></script>

<?php include 'views/layouts/footer.php'; ?>
