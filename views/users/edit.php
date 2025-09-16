<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="page-container">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-user-edit"></i>
            Chỉnh sửa người dùng
        </h1>
        <div class="page-actions">
            <a href="<?= BASE_URL ?>/users" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Thông tin người dùng</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/users/update/<?= $user['maNguoiDung'] ?>" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="tenNguoiDung">Tên người dùng <span class="required">*</span></label>
                        <input type="text" class="form-control" id="tenNguoiDung" name="tenNguoiDung" 
                               value="<?= htmlspecialchars($user['tenNguoiDung'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="soDienThoai">Số điện thoại <span class="required">*</span></label>
                        <input type="tel" class="form-control" id="soDienThoai" name="soDienThoai" 
                               value="<?= htmlspecialchars($user['soDienThoai'] ?? '') ?>" 
                               pattern="[0-9]{10,11}" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="eMail">Email <span class="required">*</span></label>
                        <input type="email" class="form-control" id="eMail" name="eMail" 
                               value="<?= htmlspecialchars($user['eMail'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="maVaiTro">Vai trò <span class="required">*</span></label>
                        <select class="form-control" id="maVaiTro" name="maVaiTro" required>
                            <option value="">Chọn vai trò</option>
                            <?php if (!empty($roles)): ?>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['maVaiTro'] ?>" 
                                            <?= ($user['maVaiTro'] ?? '') == $role['maVaiTro'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($role['tenVaiTro']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="gioiTinh">Giới tính</label>
                        <select class="form-control" id="gioiTinh" name="gioiTinh">
                            <option value="">Chọn giới tính</option>
                            <option value="Nam" <?= ($user['gioiTinh'] ?? '') == 'Nam' ? 'selected' : '' ?>>Nam</option>
                            <option value="Nữ" <?= ($user['gioiTinh'] ?? '') == 'Nữ' ? 'selected' : '' ?>>Nữ</option>
                            <option value="Khác" <?= ($user['gioiTinh'] ?? '') == 'Khác' ? 'selected' : '' ?>>Khác</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="matKhau">Mật khẩu mới</label>
                        <input type="password" class="form-control" id="matKhau" name="matKhau" 
                               placeholder="Để trống nếu không muốn thay đổi">
                        <small class="form-text">Để trống nếu không muốn thay đổi mật khẩu</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="diaChi">Địa chỉ</label>
                    <textarea class="form-control" id="diaChi" name="diaChi" rows="3" 
                              placeholder="Nhập địa chỉ"><?= htmlspecialchars($user['diaChi'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="moTa">Mô tả</label>
                    <textarea class="form-control" id="moTa" name="moTa" rows="4" 
                              placeholder="Nhập mô tả về người dùng"><?= htmlspecialchars($user['moTa'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="avatar">Ảnh đại diện</label>
                    <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                    <?php if (!empty($user['avt'])): ?>
                        <div class="current-avatar">
                            <p>Ảnh hiện tại:</p>
                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($user['avt']) ?>" 
                                 alt="Avatar hiện tại" class="avatar-preview">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Cập nhật
                    </button>
                    <a href="<?= BASE_URL ?>/users" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
