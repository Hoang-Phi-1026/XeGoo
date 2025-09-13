<?php
// Include header
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <div class="register-container">
        <h2 class="register-title">Đăng ký tài khoản</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo BASE_URL; ?>/register" class="register-form" id="registerForm">
            <div class="form-group">
                <label for="tenNguoiDung">Họ và tên</label>
                <input type="text" id="tenNguoiDung" name="tenNguoiDung" required value="<?php echo isset($_SESSION['form_data']['tenNguoiDung']) ? htmlspecialchars($_SESSION['form_data']['tenNguoiDung']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="soDienThoai">Số điện thoại</label>
                <input type="tel" id="soDienThoai" name="soDienThoai" required value="<?php echo isset($_SESSION['form_data']['soDienThoai']) ? htmlspecialchars($_SESSION['form_data']['soDienThoai']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="eMail">Email</label>
                <input type="email" id="eMail" name="eMail" required value="<?php echo isset($_SESSION['form_data']['eMail']) ? htmlspecialchars($_SESSION['form_data']['eMail']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="gioiTinh">Giới tính</label>
                <select id="gioiTinh" name="gioiTinh" required>
                    <option value="">Chọn giới tính</option>
                    <option value="NAM" <?php echo (isset($_SESSION['form_data']['gioiTinh']) && $_SESSION['form_data']['gioiTinh'] === 'NAM') ? 'selected' : ''; ?>>Nam</option>
                    <option value="NU" <?php echo (isset($_SESSION['form_data']['gioiTinh']) && $_SESSION['form_data']['gioiTinh'] === 'NU') ? 'selected' : ''; ?>>Nữ</option>
                    <option value="KHAC" <?php echo (isset($_SESSION['form_data']['gioiTinh']) && $_SESSION['form_data']['gioiTinh'] === 'KHAC') ? 'selected' : ''; ?>>Khác</option>
                </select>
            </div>

            <div class="form-group">
                <label for="diaChi">Địa chỉ</label>
                <textarea id="diaChi" name="diaChi" rows="3" placeholder="Nhập địa chỉ của bạn"><?php echo isset($_SESSION['form_data']['diaChi']) ? htmlspecialchars($_SESSION['form_data']['diaChi']) : ''; ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="matKhau">Mật khẩu</label>
                <div class="password-input-wrapper">
                    <input type="password" id="matKhau" name="matKhau" required>
                    <button type="button" class="password-toggle" aria-label="Hiển thị mật khẩu">
                        <i class="far fa-eye"></i>
                    </button>
                </div>
                <div class="password-strength">
                    <div class="strength-bar">
                        <div class="strength-indicator" style="width: 0%"></div>
                    </div>
                    <span class="strength-text">Độ mạnh mật khẩu</span>
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirmPassword">Xác nhận mật khẩu</label>
                <div class="password-input-wrapper">
                    <input type="password" id="confirmPassword" name="confirmPassword" required>
                    <button type="button" class="password-toggle" aria-label="Hiển thị mật khẩu">
                        <i class="far fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-group terms-checkbox">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">Tôi đồng ý với <a href="<?php echo BASE_URL; ?>/terms" target="_blank">Điều khoản dịch vụ</a> và <a href="<?php echo BASE_URL; ?>/privacy" target="_blank">Chính sách bảo mật</a></label>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-register">Đăng ký</button>
            </div>
            
            <div class="form-footer">
                <p class="login-link">
                    Đã có tài khoản? <a href="<?php echo BASE_URL; ?>/login">Đăng nhập ngay</a>
                </p>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    const toggleButtons = document.querySelectorAll('.password-toggle');
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            
            // Toggle icon
            const icon = this.querySelector('i');
            if (type === 'password') {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        });
    });

    // Simple password strength indicator (for UI only)
    const passwordInput = document.getElementById('matKhau');
    const strengthIndicator = document.querySelector('.strength-indicator');
    const strengthText = document.querySelector('.strength-text');

    passwordInput.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        
        if (password.length >= 8) strength += 25;
        if (password.match(/[A-Z]/)) strength += 25;
        if (password.match(/[0-9]/)) strength += 25;
        if (password.match(/[^A-Za-z0-9]/)) strength += 25;
        
        strengthIndicator.style.width = strength + '%';
        
        if (strength <= 25) {
            strengthIndicator.style.backgroundColor = '#ff4d4d';
            strengthText.textContent = 'Yếu';
        } else if (strength <= 50) {
            strengthIndicator.style.backgroundColor = '#ffa64d';
            strengthText.textContent = 'Trung bình';
        } else if (strength <= 75) {
            strengthIndicator.style.backgroundColor = '#ffff4d';
            strengthText.textContent = 'Khá';
        } else {
            strengthIndicator.style.backgroundColor = '#4dff4d';
            strengthText.textContent = 'Mạnh';
        }
    });
    
    // Password confirmation validation
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const registerForm = document.getElementById('registerForm');
    
    registerForm.addEventListener('submit', function(event) {
        if (passwordInput.value !== confirmPasswordInput.value) {
            event.preventDefault();
            alert('Mật khẩu xác nhận không khớp!');
            confirmPasswordInput.focus();
        }
    });
});
</script>

<?php
unset($_SESSION['form_data']);
// Include footer
require_once __DIR__ . '/../layouts/footer.php';
?>
