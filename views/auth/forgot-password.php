<?php
// Include header
require_once __DIR__ . '/../layouts/header.php';
?>

<!-- Added link to forgot-password.css -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/forgot-password.css">

<div class="container">
    <div class="login-container">
        <div class="verification-icon">🔐</div>
        <h2 class="login-title">Quên mật khẩu</h2>
        
        <div id="alert-container"></div>

        <form id="forgot-password-form" class="login-form">
            <div id="email-step">
                <p style="text-align: center; color: #666; margin-bottom: 24px; font-size: 15px; line-height: 1.6;">
                    Nhập địa chỉ email của bạn và chúng tôi sẽ gửi mã xác nhận để đặt lại mật khẩu
                </p>
                
                <div class="form-group">
                    <label for="email">Địa chỉ Email</label>
                    <input type="email" id="email" name="email" placeholder="example@email.com" required>
                </div>
                
                <div class="form-actions">
                    <button type="button" id="send-code-btn" class="btn-login">
                        <span class="btn-text">Gửi mã xác nhận</span>
                    </button>
                </div>
            </div>

            <div id="code-step" style="display: none;">
                <p style="text-align: center; color: #666; margin-bottom: 24px; font-size: 15px; line-height: 1.6;">
                    Mã xác nhận 6 số đã được gửi đến email của bạn. Vui lòng kiểm tra hộp thư đến hoặc thư rác.
                </p>
                
                <div class="form-group">
                    <label for="code">🔢 Mã xác nhận</label>
                    <input type="text" id="code" name="code" class="code-input" placeholder="000000" maxlength="6" required pattern="[0-9]{6}">
                    <small style="color: #999; display: block; margin-top: 8px; font-size: 13px;">
                        ⏱️ Mã có hiệu lực trong 10 phút
                    </small>
                </div>
                
                <div class="form-actions">
                    <button type="button" id="verify-code-btn" class="btn-login">
                        <span class="btn-text">Xác nhận và đặt lại mật khẩu</span>
                    </button>
                </div>
                
                <div class="resend-section">
                    <p>Không nhận được mã?</p>
                    <button type="button" id="resend-code-btn" class="link-button">
                        Gửi lại mã xác nhận
                    </button>
                </div>
            </div>
            
            <div class="form-footer">
                <a href="<?php echo BASE_URL; ?>/login" class="forgot-password">
                    ← Quay lại đăng nhập
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Removed inline styles, moved to forgot-password.css -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sendCodeBtn = document.getElementById('send-code-btn');
    const verifyCodeBtn = document.getElementById('verify-code-btn');
    const resendCodeBtn = document.getElementById('resend-code-btn');
    const emailStep = document.getElementById('email-step');
    const codeStep = document.getElementById('code-step');
    const emailInput = document.getElementById('email');
    const codeInput = document.getElementById('code');
    const alertContainer = document.getElementById('alert-container');

    function showAlert(message, type) {
        alertContainer.innerHTML = `
            <div class="alert alert-${type}">
                ${message}
            </div>
        `;
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            const alert = alertContainer.querySelector('.alert');
            if (alert) {
                alert.style.animation = 'slideUp 0.3s ease-out';
                setTimeout(() => {
                    alertContainer.innerHTML = '';
                }, 300);
            }
        }, 5000);
    }

    sendCodeBtn.addEventListener('click', async function() {
        const email = emailInput.value.trim();
        
        if (!email) {
            showAlert('Vui lòng nhập địa chỉ email!', 'error');
            emailInput.focus();
            return;
        }

        if (!isValidEmail(email)) {
            showAlert('Địa chỉ email không hợp lệ!', 'error');
            emailInput.focus();
            return;
        }

        sendCodeBtn.disabled = true;
        sendCodeBtn.classList.add('loading');

        try {
            const response = await fetch('<?php echo BASE_URL; ?>/forgot-password/send-code', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email: email })
            });

            const data = await response.json();

            if (data.success) {
                showAlert(data.message, 'success');
                emailStep.style.display = 'none';
                codeStep.style.display = 'block';
                setTimeout(() => codeInput.focus(), 100);
            } else {
                showAlert(data.message, 'error');
            }
        } catch (error) {
            showAlert('Có lỗi xảy ra khi kết nối máy chủ. Vui lòng thử lại!', 'error');
        } finally {
            sendCodeBtn.disabled = false;
            sendCodeBtn.classList.remove('loading');
        }
    });

    verifyCodeBtn.addEventListener('click', async function() {
        const code = codeInput.value.trim();
        
        if (!code) {
            showAlert('Vui lòng nhập mã xác nhận!', 'error');
            codeInput.focus();
            return;
        }

        if (code.length !== 6 || !/^\d{6}$/.test(code)) {
            showAlert('Mã xác nhận phải là 6 chữ số!', 'error');
            codeInput.focus();
            return;
        }

        verifyCodeBtn.disabled = true;
        verifyCodeBtn.classList.add('loading');

        try {
            const response = await fetch('<?php echo BASE_URL; ?>/forgot-password/verify-code', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ code: code })
            });

            const data = await response.json();

            if (data.success) {
                showAlert('✅ ' + data.message + ' Đang chuyển hướng...', 'success');
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 2000);
            } else {
                showAlert(data.message, 'error');
                verifyCodeBtn.disabled = false;
                verifyCodeBtn.classList.remove('loading');
                codeInput.value = '';
                codeInput.focus();
            }
        } catch (error) {
            showAlert('Có lỗi xảy ra khi kết nối máy chủ. Vui lòng thử lại!', 'error');
            verifyCodeBtn.disabled = false;
            verifyCodeBtn.classList.remove('loading');
        }
    });

    let resendCooldown = false;
    resendCodeBtn.addEventListener('click', async function() {
        if (resendCooldown) {
            showAlert('Vui lòng đợi 30 giây trước khi gửi lại mã!', 'info');
            return;
        }

        const email = emailInput.value.trim();
        
        resendCodeBtn.disabled = true;
        const originalText = resendCodeBtn.textContent;
        resendCodeBtn.textContent = 'Đang gửi...';

        try {
            const response = await fetch('<?php echo BASE_URL; ?>/forgot-password/send-code', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email: email })
            });

            const data = await response.json();

            if (data.success) {
                showAlert('✅ Mã xác nhận mới đã được gửi đến email của bạn!', 'success');
                
                // Start cooldown
                resendCooldown = true;
                let countdown = 30;
                const interval = setInterval(() => {
                    countdown--;
                    resendCodeBtn.textContent = `Gửi lại sau ${countdown}s`;
                    if (countdown <= 0) {
                        clearInterval(interval);
                        resendCodeBtn.textContent = originalText;
                        resendCodeBtn.disabled = false;
                        resendCooldown = false;
                    }
                }, 1000);
            } else {
                showAlert(data.message, 'error');
                resendCodeBtn.disabled = false;
                resendCodeBtn.textContent = originalText;
            }
        } catch (error) {
            showAlert('Có lỗi xảy ra. Vui lòng thử lại!', 'error');
            resendCodeBtn.disabled = false;
            resendCodeBtn.textContent = originalText;
        }
    });

    codeInput.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    emailInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            sendCodeBtn.click();
        }
    });

    codeInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            verifyCodeBtn.click();
        }
    });

    // Helper function to validate email
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
});
</script>

<?php
// Include footer
require_once __DIR__ . '/../layouts/footer.php';
?>
