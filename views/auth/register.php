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

<div id="verificationModal" class="modal" style="display: <?php echo isset($_SESSION['show_verification_modal']) && $_SESSION['show_verification_modal'] ? 'flex' : 'none'; ?>;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Xác nhận email</h3>
        </div>
        <div class="modal-body">
            <p>Mã xác nhận đã được gửi đến email: <strong><?php echo isset($_SESSION['pending_registration']['eMail']) ? htmlspecialchars($_SESSION['pending_registration']['eMail']) : ''; ?></strong></p>
            <p>Vui lòng nhập mã xác nhận 6 số:</p>
            
            <div id="verificationAlert" class="alert" style="display: none;"></div>
            
            <div class="verification-input-group">
                <input type="text" id="verificationCode" maxlength="6" placeholder="000000" autocomplete="off">
            </div>
            
            <div class="verification-timer">
                <span id="timerText">Mã có hiệu lực trong: <strong id="countdown">10:00</strong></span>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn-verify" id="btnVerify">Xác nhận</button>
                <button type="button" class="btn-resend" id="btnResend" disabled>
                    <i class="fas fa-redo"></i> Gửi lại mã
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    align-items: center;
    justify-content: center;
}

.modal-content {
    background-color: white;
    border-radius: 12px;
    max-width: 500px;
    width: 90%;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-header {
    padding: 20px 30px;
    border-bottom: 1px solid #e0e0e0;
}

.modal-header h3 {
    margin: 0;
    color: #333;
    font-size: 24px;
}

.modal-body {
    padding: 30px;
}

.modal-body p {
    margin-bottom: 15px;
    color: #666;
    line-height: 1.6;
}

.verification-input-group {
    margin: 20px 0;
}

.verification-input-group input {
    width: 100%;
    padding: 15px;
    font-size: 24px;
    text-align: center;
    letter-spacing: 10px;
    border: 2px solid #ddd;
    border-radius: 8px;
    font-weight: bold;
}

.verification-input-group input:focus {
    outline: none;
    border-color: var(--primary-color);
}

.verification-timer {
    text-align: center;
    margin: 15px 0;
    color: #666;
}

.verification-timer strong {
    color: var(--primary-color);
}

.modal-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.btn-verify, .btn-resend {
    flex: 1;
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-verify {
    background: var(--primary-color);
    color: white;
}

.btn-verify:hover {
    background: var(--primary-dark);
}

.btn-resend {
    background: #f0f0f0;
    color: #666;
}

.btn-resend:hover:not(:disabled) {
    background: #e0e0e0;
}

.btn-resend:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

#verificationAlert {
    margin-bottom: 15px;
    padding: 12px;
    border-radius: 6px;
}

#verificationAlert.alert-error {
    background: #fee;
    color: #c33;
    border: 1px solid #fcc;
}

#verificationAlert.alert-success {
    background: #efe;
    color: #3c3;
    border: 1px solid #cfc;
}
</style>

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
    
    const modal = document.getElementById('verificationModal');
    const verificationCode = document.getElementById('verificationCode');
    const btnVerify = document.getElementById('btnVerify');
    const btnResend = document.getElementById('btnResend');
    const countdownEl = document.getElementById('countdown');
    const verificationAlert = document.getElementById('verificationAlert');
    
    let countdownTimer;
    let timeLeft = 600; // 10 minutes in seconds
    
    function startCountdown() {
        countdownTimer = setInterval(function() {
            timeLeft--;
            
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            countdownEl.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeLeft <= 0) {
                clearInterval(countdownTimer);
                showAlert('Mã xác nhận đã hết hạn. Vui lòng gửi lại mã mới.', 'error');
                btnResend.disabled = false;
            }
            
            // Enable resend button after 1 minute
            if (timeLeft === 540) {
                btnResend.disabled = false;
            }
        }, 1000);
    }
    
    function showAlert(message, type) {
        verificationAlert.textContent = message;
        verificationAlert.className = 'alert alert-' + type;
        verificationAlert.style.display = 'block';
    }
    
    function hideAlert() {
        verificationAlert.style.display = 'none';
    }
    
    if (modal.style.display === 'flex') {
        startCountdown();
    }
    
    // Verify button click
    btnVerify.addEventListener('click', function() {
        const code = verificationCode.value.trim();
        
        if (code.length !== 6) {
            showAlert('Vui lòng nhập đầy đủ 6 số mã xác nhận!', 'error');
            return;
        }
        
        btnVerify.disabled = true;
        btnVerify.textContent = 'Đang xác nhận...';
        hideAlert();
        
        fetch('<?php echo BASE_URL; ?>/auth/verify-email', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ code: code })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                setTimeout(function() {
                    window.location.href = data.redirect;
                }, 1500);
            } else {
                showAlert(data.message, 'error');
                btnVerify.disabled = false;
                btnVerify.textContent = 'Xác nhận';
            }
        })
        .catch(error => {
            showAlert('Có lỗi xảy ra. Vui lòng thử lại!', 'error');
            btnVerify.disabled = false;
            btnVerify.textContent = 'Xác nhận';
        });
    });
    
    // Resend button click
    btnResend.addEventListener('click', function() {
        btnResend.disabled = true;
        btnResend.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang gửi...';
        hideAlert();
        
        fetch('<?php echo BASE_URL; ?>/auth/resend-code', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Mã xác nhận mới đã được gửi đến email của bạn!', 'success');
                timeLeft = 600;
                clearInterval(countdownTimer);
                startCountdown();
                setTimeout(function() {
                    btnResend.disabled = false;
                    btnResend.innerHTML = '<i class="fas fa-redo"></i> Gửi lại mã';
                }, 60000); // Enable after 1 minute
            } else {
                showAlert(data.message, 'error');
                btnResend.disabled = false;
                btnResend.innerHTML = '<i class="fas fa-redo"></i> Gửi lại mã';
            }
        })
        .catch(error => {
            showAlert('Có lỗi xảy ra. Vui lòng thử lại!', 'error');
            btnResend.disabled = false;
            btnResend.innerHTML = '<i class="fas fa-redo"></i> Gửi lại mã';
        });
    });
    
    // Allow Enter key to verify
    verificationCode.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            btnVerify.click();
        }
    });
    
    // Only allow numbers
    verificationCode.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
});
</script>

<?php
unset($_SESSION['show_verification_modal']);
unset($_SESSION['form_data']);
// Include footer
require_once __DIR__ . '/../layouts/footer.php';
?>
