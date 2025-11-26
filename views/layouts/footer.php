</main>
    
    <!-- Restructured footer to match CSS classes -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="company-logo">
                    <a href="<?php echo BASE_URL; ?>/" class="logo">
                <!-- LOGO IMAGE HERE -->
                <img src="<?php echo BASE_URL; ?>/public/uploads/images/logo-dark.png" alt="XeGoo Logo" class="logo-img" style="height:40px;margin-right:8px;">
            </a>
                    </div>
                    <p>Nền tảng đặt vé xe liên tỉnh hàng đầu Việt Nam. Chúng tôi cam kết mang đến trải nghiệm di chuyển an toàn, tiện lợi và thoải mái nhất cho khách hàng.</p>
                    <div class="social-links">
                        <a href="#" class="social-link" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link" title="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link" title="YouTube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h3>Dịch vụ</h3>
                    <ul class="footer-links">
                        <li><a href="<?php echo BASE_URL; ?>/search"><i class="fas fa-bus"></i> Đặt vé xe</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/group-rental"><i class="fas fa-car"></i> Thuê xe</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/ticket-lookup"><i class="fas fa-ticket-alt"></i> Tra cứu vé</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/booking-guide#routes"><i class="fas fa-route"></i> Tuyến đường</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/post"><i class="fas fa-users"></i> Cộng đồng</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Hỗ trợ</h3>
                    <ul class="footer-links">
                        <li><a href="<?php echo BASE_URL; ?>/support/ai"><i class="fas fa-life-ring"></i> Trung tâm trợ giúp</a></li>
                         <li><a href="<?php echo BASE_URL; ?>/faq"><i class="fas fa-question-circle"></i> Câu hỏi thường gặp</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/refund"><i class="fas fa-undo"></i> Chính sách hoàn vé</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/terms"><i class="fas fa-file-contract"></i> Điều khoản sử dụng</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Liên hệ</h3>
                    <div class="contact-info">
                        <div class="contact-item">
                            <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                            <span>123 Đường ABC, Quận 1, TP.HCM</span>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon"><i class="fas fa-phone"></i></div>
                            <span>1900 1234</span>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                            <span>support@xegoo.com</span>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon"><i class="fas fa-clock"></i></div>
                            <span>24/7 - Hỗ trợ khách hàng</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> XeGoo. Tất cả quyền được bảo lưu.</p>
                <ul class="footer-bottom-links">
                    <li><a href="<?php echo BASE_URL; ?>/privacy">Chính sách bảo mật</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/terms">Điều khoản</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/sitemap">Sơ đồ trang</a></li>
                </ul>
            </div>
        </div>
    </footer>
    
    <!-- Load main.js after unified-search.js to avoid conflicts -->
    <script src="<?php echo BASE_URL; ?>/public/js/main.js"></script>
    
    <!-- Added debug script for development -->
    <script>
        // Debug unified search system
        if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
            console.log('[XeGoo Debug] Unified Search System loaded');
            console.log('[XeGoo Debug] BASE_URL:', window.BASE_URL);
            console.log('[XeGoo Debug] UnifiedSearch instance:', window.unifiedSearch);
        }
    </script>
</body>
</html>
