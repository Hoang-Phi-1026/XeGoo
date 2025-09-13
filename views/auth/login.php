<?php
// Include header
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <div class="login-container">
        <h2 class="login-title">Đăng nhập</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo BASE_URL; ?>/login" class="login-form">
            <div class="form-group">
                <label for="sodienthoai">Nhập Số Điện Thoại</label>
                <input type="text" id="sodienthoai" name="sodienthoai" required>
            </div>
            
            <div class="form-group">
                <label for="password">Nhập Mật khẩu</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-login">Đăng nhập</button>
            </div>
            
            <div class="form-footer">
                <a href="<?php echo BASE_URL; ?>/forgot-password" class="forgot-password">Quên mật khẩu?</a>
                <p class="register-link">Chưa có tài khoản? <a href="<?php echo BASE_URL; ?>/register">Đăng ký ngay</a></p>
            </div>
        </form>
    </div>
</div>

<?php
// Include footer
require_once __DIR__ . '/../layouts/footer.php';
?>
