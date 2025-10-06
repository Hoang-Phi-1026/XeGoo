<?php
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/QRCodeGenerator.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $mailer;
    private $fromEmail;
    private $fromName;
    private $appPassword;
    
    public function __construct() {
        $this->fromEmail = 'xegoosys@gmail.com';
        $this->fromName = 'Xe Khách Xegoo';
        $this->appPassword = 'fwxf msep qxnp sofq'; // App Password bạn tạo
        
        $this->mailer = new PHPMailer(true);
        $this->configureSMTP();
    }
    
    /**
     * Configure SMTP settings for Gmail
     */
    private function configureSMTP() {
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = 'smtp.gmail.com';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->fromEmail;
            $this->mailer->Password = $this->appPassword;
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = 587;
            $this->mailer->CharSet = 'UTF-8';
            
            // Bật debug
            $this->mailer->SMTPDebug = 3; // Log chi tiết SMTP
            $this->mailer->Debugoutput = function($str, $level) {
                error_log("[PHPMailer Debug] Level $level: $str");
            };
            
            // Sender info
            $this->mailer->setFrom($this->fromEmail, $this->fromName);
            
        } catch (Exception $e) {
            error_log("EmailService SMTP configuration error: " . $e->getMessage());
        }
    }
    
    /**
     * Send verification code email for registration
     * 
     * @param string $toEmail Recipient email address
     * @param string $toName Recipient name
     * @param string $verificationCode 6-digit verification code
     * @return array Result with success status and message
     */
    public function sendVerificationEmail($toEmail, $toName, $verificationCode) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($toEmail, $toName);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Mã xác nhận đăng ký tài khoản - Xe Khách Xegoo';
            
            $htmlBody = $this->getVerificationEmailTemplate($toName, $verificationCode);
            $this->mailer->Body = $htmlBody;
            
            // Plain text version
            $this->mailer->AltBody = "Xin chào $toName,\n\n"
                . "Cảm ơn bạn đã đăng ký tài khoản tại Xe Khách Xegoo.\n\n"
                . "Mã xác nhận của bạn là: $verificationCode\n\n"
                . "Mã này có hiệu lực trong 10 phút.\n\n"
                . "Trân trọng,\nĐội ngũ Xe Khách Xegoo";
            
            $this->mailer->send();
            
            return [
                'success' => true,
                'message' => 'Email xác nhận đã được gửi thành công!'
            ];
            
        } catch (Exception $e) {
            error_log("Send verification email error: " . $this->mailer->ErrorInfo);
            return [
                'success' => false,
                'message' => 'Không thể gửi email xác nhận: ' . $this->mailer->ErrorInfo
            ];
        }
    }
    
    /**
     * Send ticket confirmation email with QR code
     * 
     * @param array $ticketData Booking information including emailNguoiDung and passengerEmails
     * @return array Result with success status and message
     */
    public function sendTicketEmail($ticketData) {
        try {
            $this->mailer->clearAddresses();
            
            // Determine recipient emails
            $recipientEmails = [];
            if (!empty($ticketData['emailNguoiDung']) && filter_var($ticketData['emailNguoiDung'], FILTER_VALIDATE_EMAIL)) {
                // Prioritize user email if available
                $recipientEmails[] = $ticketData['emailNguoiDung'];
            } else {
                // Use passenger emails if no user email
                $recipientEmails = $ticketData['passengerEmails'];
            }
            
            if (empty($recipientEmails)) {
                error_log("[v0] No valid recipient emails for booking ID: " . $ticketData['maDatVe']);
                return [
                    'success' => false,
                    'message' => 'Không tìm thấy địa chỉ email hợp lệ để gửi.'
                ];
            }
            
            // Add all valid recipient emails
            foreach ($recipientEmails as $email) {
                $this->mailer->addAddress($email);
            }
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Xác nhận đặt vé - Xe Khách Xegoo - Mã đặt vé: ' . $ticketData['maDatVe'];
            
            $htmlBody = $this->getTicketEmailTemplate($ticketData, $ticketData['tickets']);
            $this->mailer->Body = $htmlBody;
            
            // Plain text version
            $this->mailer->AltBody = $this->getTicketEmailPlainText($ticketData, $ticketData['tickets']);
            
            $this->mailer->send();
            
            error_log("[v0] Ticket email sent successfully to: " . implode(', ', $recipientEmails));
            
            return [
                'success' => true,
                'message' => 'Email xác nhận vé đã được gửi thành công!'
            ];
            
        } catch (Exception $e) {
            error_log("Send ticket email error: " . $this->mailer->ErrorInfo);
            return [
                'success' => false,
                'message' => 'Không thể gửi email xác nhận vé: ' . $this->mailer->ErrorInfo
            ];
        }
    }
    
    /**
     * Get HTML template for verification email
     */
    private function getVerificationEmailTemplate($toName, $verificationCode) {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px; }
                .content { padding: 20px; background: #f9f9f9; border-radius: 10px; margin: 20px 0; }
                .code { font-size: 24px; font-weight: bold; color: #764ba2; text-align: center; padding: 15px; background: white; border: 1px solid #ddd; border-radius: 5px; }
                .footer { text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Xe Khách Xegoo</h2>
                    <p>Mã xác nhận đăng ký tài khoản</p>
                </div>
                <div class="content">
                    <p>Xin chào ' . htmlspecialchars($toName) . ',</p>
                    <p>Cảm ơn bạn đã đăng ký tài khoản tại Xe Khách Xegoo.</p>
                    <p>Mã xác nhận của bạn là:</p>
                    <div class="code">' . $verificationCode . '</div>
                    <p>Mã này có hiệu lực trong 10 phút.</p>
                    <p>Nếu bạn không yêu cầu mã này, vui lòng bỏ qua email này.</p>
                </div>
                <div class="footer">
                    <p>Email này được gửi tự động, vui lòng không trả lời.</p>
                    <p>&copy; 2025 Xe Khách Xegoo. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Get HTML template for ticket email
     */
    private function getTicketEmailTemplate($bookingData, $ticketDetails) {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px; }
                .content { padding: 20px; background: #f9f9f9; border-radius: 10px; margin: 20px 0; }
                .ticket { margin: 15px 0; padding: 15px; background: white; border: 1px solid #ddd; border-radius: 5px; }
                .info-row { margin: 10px 0; }
                .info-label { font-weight: bold; display: inline-block; width: 120px; }
                .info-value { display: inline-block; }
                .qr-code { text-align: center; margin: 20px 0; }
                .warning { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Xe Khách Xegoo</h2>
                    <p>Xác nhận đặt vé - Mã đặt vé: ' . htmlspecialchars($bookingData['maDatVe']) . '</p>
                </div>
                <div class="content">
                    <p>Cảm ơn bạn đã đặt vé tại Xe Khách Xegoo!</p>
                    <h3>Thông tin đặt vé</h3>
                    <div class="info-row">
                        <span class="info-label">Mã đặt vé:</span>
                        <span class="info-value">' . htmlspecialchars($bookingData['maDatVe']) . '</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Ngày đặt:</span>
                        <span class="info-value">' . date('d/m/Y H:i', strtotime($bookingData['ngayDat'])) . '</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tổng tiền:</span>
                        <span class="info-value"><strong>' . number_format($bookingData['tongTienSauGiam'], 0, ',', '.') . ' VNĐ</strong></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Phương thức:</span>
                        <span class="info-value">' . htmlspecialchars($bookingData['phuongThucThanhToan']) . '</span>
                    </div>
                    <h3>Chi tiết vé</h3>';
        
        foreach ($ticketDetails as $index => $ticket) {
            // Generate QR code for each ticket
            $qrData = json_encode([
                'maDatVe' => $bookingData['maDatVe'],
                'maChiTietDatVe' => $ticket['maChiTietDatVe'],
                'soGhe' => $ticket['soGhe'],
                'ngayKhoiHanh' => $ticket['ngayKhoiHanh'],
                'gioKhoiHanh' => $ticket['gioKhoiHanh'],
                'diemDi' => $ticket['diemDi'],
                'diemDen' => $ticket['diemDen']
            ]);
            $qrCode = QRCodeGenerator::generateBase64($qrData);
            
            $html .= '
                    <div class="ticket">
                        <h4>Vé #' . ($index + 1) . ' - Ghế ' . htmlspecialchars($ticket['soGhe']) . '</h4>
                        <div class="info-row">
                            <span class="info-label">Hành khách:</span>
                            <span class="info-value">' . htmlspecialchars($ticket['hoTenHanhKhach']) . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Tuyến:</span>
                            <span class="info-value">' . htmlspecialchars($ticket['diemDi']) . ' → ' . htmlspecialchars($ticket['diemDen']) . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Ngày khởi hành:</span>
                            <span class="info-value">' . date('d/m/Y', strtotime($ticket['thoiGianKhoiHanh'])) . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Giờ khởi hành:</span>
                            <span class="info-value"><strong>' . date('H:i', strtotime($ticket['thoiGianKhoiHanh'])) . '</strong></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Điểm đón:</span>
                            <span class="info-value">' . htmlspecialchars($ticket['diemDonTen']) . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Điểm trả:</span>
                            <span class="info-value">' . htmlspecialchars($ticket['diemTraTen']) . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Biển số xe:</span>
                            <span class="info-value">' . htmlspecialchars($ticket['bienSo']) . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Giá vé:</span>
                            <span class="info-value"><strong>' . number_format($ticket['seatPrice'], 0, ',', '.') . ' VNĐ</strong></span>
                        </div>';
            
            if ($qrCode) {
                $html .= '
                        <div class="qr-code">
                            <p><strong>Mã QR vé của bạn:</strong></p>
                            <img src="' . $qrCode . '" alt="QR Code">
                            <p style="font-size: 12px; color: #666;">Vui lòng xuất trình mã QR này khi lên xe</p>
                        </div>';
            }
            
            $html .= '
                    </div>';
        }
        
        $html .= '
                    <div class="warning">
                        <strong>⚠️ Lưu ý quan trọng:</strong>
                        <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                            <li>Vui lòng có mặt tại điểm đón trước giờ khởi hành <strong>15 phút</strong></li>
                            <li>Mang theo CMND/CCCD để đối chiếu thông tin</li>
                            <li>Xuất trình mã QR hoặc mã đặt vé khi lên xe</li>
                            <li>Liên hệ hotline nếu cần hỗ trợ: <strong>1900-xxxx</strong></li>
                        </ul>
                    </div>
                    
                    <p>Chúc bạn có một chuyến đi an toàn và vui vẻ!</p>
                    <p>Trân trọng,<br><strong>Đội ngũ Xe Khách Xegoo</strong></p>
                </div>
                
                <div class="footer">
                    <p>Email này được gửi tự động, vui lòng không trả lời.</p>
                    <p>&copy; 2025 Xe Khách Xegoo. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>';
        
        return $html;
    }
    
    /**
     * Get plain text version of ticket email
     */
    private function getTicketEmailPlainText($bookingData, $ticketDetails) {
        $text = "XE KHÁCH XEGOO - XÁC NHẬN ĐẶT VÉ\n\n";
        $text .= "Cảm ơn bạn đã đặt vé tại Xe Khách Xegoo!\n\n";
        $text .= "THÔNG TIN ĐẶT VÉ\n";
        $text .= "Mã đặt vé: " . $bookingData['maDatVe'] . "\n";
        $text .= "Ngày đặt: " . date('d/m/Y H:i', strtotime($bookingData['ngayDat'])) . "\n";
        $text .= "Tổng tiền: " . number_format($bookingData['tongTienSauGiam'], 0, ',', '.') . " VNĐ\n";
        $text .= "Phương thức thanh toán: " . $bookingData['phuongThucThanhToan'] . "\n\n";
        
        $text .= "CHI TIẾT VÉ\n";
        foreach ($ticketDetails as $index => $ticket) {
            $text .= "\nVé #" . ($index + 1) . " - Ghế " . $ticket['soGhe'] . "\n";
            $text .= "Hành khách: " . $ticket['hoTenHanhKhach'] . "\n";
            $text .= "Tuyến: " . $ticket['diemDi'] . " → " . $ticket['diemDen'] . "\n";
            $text .= "Ngày khởi hành: " . date('d/m/Y', strtotime($ticket['thoiGianKhoiHanh'])) . "\n";
            $text .= "Giờ khởi hành: " . date('H:i', strtotime($ticket['thoiGianKhoiHanh'])) . "\n";
            $text .= "Điểm đón: " . $ticket['diemDonTen'] . "\n";
            $text .= "Điểm trả: " . $ticket['diemTraTen'] . "\n";
            $text .= "Biển số xe: " . $ticket['bienSo'] . "\n";
            $text .= "Giá vé: " . number_format($ticket['seatPrice'], 0, ',', '.') . " VNĐ\n";
        }
        
        $text .= "\nLƯU Ý QUAN TRỌNG:\n";
        $text .= "- Vui lòng có mặt tại điểm đón trước giờ khởi hành 15 phút\n";
        $text .= "- Mang theo CMND/CCCD để đối chiếu thông tin\n";
        $text .= "- Xuất trình mã đặt vé khi lên xe\n";
        $text .= "- Liên hệ hotline nếu cần hỗ trợ: 1900-xxxx\n\n";
        
        $text .= "Chúc bạn có một chuyến đi an toàn và vui vẻ!\n\n";
        $text .= "Trân trọng,\nĐội ngũ Xe Khách Xegoo";
        
        return $text;
    }
}
?>