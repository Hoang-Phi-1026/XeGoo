<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<!-- Added users.css link for consistent styling -->
<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/users.css">

<div class="page-container">
    <div class="page-header">
        <h1 class="page-title">
        Thêm người dùng mới
        </h1>
        <div class="page-actions">
            <a href="<?= BASE_URL ?>/users" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>Quay lại danh sách
            </a>
        </div>
    </div>

    <div class="form-container">
        <form method="POST" action="<?= BASE_URL ?>/users/store" class="user-form" id="createUserForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="tenNguoiDung" class="form-label">
                        Tên người dùng <span class="required">*</span>
                    </label>
                    <input type="text" class="form-control" id="tenNguoiDung" name="tenNguoiDung" 
                           value="<?= htmlspecialchars($_POST['tenNguoiDung'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="maVaiTro" class="form-label">
                        Vai trò <span class="required">*</span>
                    </label>
                    <select class="form-control" id="maVaiTro" name="maVaiTro" required>
                        <option value="">Chọn vai trò</option>
                        <!-- Added null coalescing for roles array -->
                        <?php foreach ($roles ?? [] as $role): ?>
                            <option value="<?= $role['maVaiTro'] ?>" 
                                    <?= ($_POST['maVaiTro'] ?? '') == $role['maVaiTro'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($role['tenVaiTro']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="soDienThoai" class="form-label">
                        Số điện thoại <span class="required">*</span>
                    </label>
                    <input type="tel" class="form-control" id="soDienThoai" name="soDienThoai" 
                           value="<?= htmlspecialchars($_POST['soDienThoai'] ?? '') ?>" 
                           pattern="[0-9]{10,11}" required>
                </div>
                <div class="form-group">
                    <label for="eMail" class="form-label">
                        Email <span class="required">*</span>
                    </label>
                    <input type="email" class="form-control" id="eMail" name="eMail" 
                           value="<?= htmlspecialchars($_POST['eMail'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="matKhau" class="form-label">
                        Mật khẩu <span class="required">*</span>
                    </label>
                    <input type="password" class="form-control" id="matKhau" name="matKhau" 
                           minlength="6" required>
                </div>
                <div class="form-group">
                    <label for="gioiTinh" class="form-label">Giới tính</label>
                    <select class="form-control" id="gioiTinh" name="gioiTinh">
                        <option value="">Chọn giới tính</option>
                        <option value="Nam" <?= ($_POST['gioiTinh'] ?? '') == 'Nam' ? 'selected' : '' ?>>Nam</option>
                        <option value="Nữ" <?= ($_POST['gioiTinh'] ?? '') == 'Nữ' ? 'selected' : '' ?>>Nữ</option>
                        <option value="Khác" <?= ($_POST['gioiTinh'] ?? '') == 'Khác' ? 'selected' : '' ?>>Khác</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="diaChi" class="form-label">Địa chỉ</label>
                <input type="text" class="form-control" id="diaChi" name="diaChi" 
                       value="<?= htmlspecialchars($_POST['diaChi'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="moTa" class="form-label">Mô tả</label>
                <textarea class="form-control" id="moTa" name="moTa" rows="3"><?= htmlspecialchars($_POST['moTa'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>Tạo người dùng
                </button>
                <button type="reset" class="btn btn-secondary">
                    <i class="fas fa-undo"></i>Đặt lại
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Added improved JavaScript for form validation and UX -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createUserForm');
    const phoneInput = document.getElementById('soDienThoai');
    const emailInput = document.getElementById('eMail');
    
    // Phone number validation
    phoneInput.addEventListener('input', function() {
        const value = this.value.replace(/\D/g, '');
        this.value = value;
        
        if (value.length < 10 || value.length > 11) {
            this.setCustomValidity('Số điện thoại phải có 10-11 chữ số');
        } else {
            this.setCustomValidity('');
        }
    });
    
    // Email validation
    emailInput.addEventListener('blur', function() {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (this.value && !emailRegex.test(this.value)) {
            this.setCustomValidity('Vui lòng nhập email hợp lệ');
        } else {
            this.setCustomValidity('');
        }
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>Đang tạo...';
        submitBtn.disabled = true;
    });
    
    // Reset form
    form.addEventListener('reset', function() {
        // Clear custom validations
        form.querySelectorAll('.form-control').forEach(input => {
            input.setCustomValidity('');
        });
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
