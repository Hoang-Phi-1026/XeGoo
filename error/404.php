<?php
require_once __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Không tìm thấy trang | XeGoo</title>
    <!-- Fixed CSS paths with proper BASE_URL -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/main.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/header.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/404.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
</head>
<body>
    <?php require_once __DIR__ . '/../views/layouts/header.php'; ?>
    
    <main class="error-404-main">
        <div class="error-404-container">
            <div class="error-404-content">
                <div class="error-text">ERROR</div>
                <div class="error-number">404</div>
                <p class="error-message">Rất tiếc! Trang bạn tìm đã bị xóa hoặc không tồn tại.</p>
                <a href="<?php echo BASE_URL; ?>/" class="error-btn">
                    <i class="fas fa-home"></i>
                    Quay lại trang chủ
                </a>
            </div>
        </div>
    </main>
    
    <?php require_once __DIR__ . '/../views/layouts/footer.php'; ?>
</body>
</html>
