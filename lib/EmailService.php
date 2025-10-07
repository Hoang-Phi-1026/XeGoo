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
        $this->fromName = 'Hệ thống XeGoo';
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
            $this->mailer->Subject = 'Mã xác nhận đăng ký tài khoản - Xegoo';
            
            $htmlBody = $this->getVerificationEmailTemplate($toName, $verificationCode);
            $this->mailer->Body = $htmlBody;
            
            // Plain text version
            $this->mailer->AltBody = "Xin chào $toName,\n\n"
                . "Cảm ơn bạn đã đăng ký tài khoản tại Xegoo.\n\n"
                . "Mã xác nhận của bạn là: $verificationCode\n\n"
                . "Mã này có hiệu lực trong 10 phút.\n\n"
                . "Nếu bạn không yêu cầu mã này, vui lòng bỏ qua email này.\n\n"
                . "Trân trọng,\nĐội ngũ Xegoo";
            
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
            $this->mailer->Subject = 'Xác nhận đặt vé - Xegoo - Mã đặt vé: ' . $ticketData['maDatVe'];
            
            $qrCodeCIDs = [];
            foreach ($ticketData['tickets'] as $index => $ticket) {
                $qrFilePath = QRCodeGenerator::generateQRFile($ticket, $ticketData['maDatVe']);
                if ($qrFilePath && file_exists($qrFilePath)) {
                    $cid = 'qr_' . $index . '_' . uniqid();
                    $this->mailer->addEmbeddedImage($qrFilePath, $cid, 'qr_code_' . $index . '.png');
                    $qrCodeCIDs[$index] = $cid;
                }
            }
            
            $htmlBody = $this->getTicketEmailTemplate($ticketData, $ticketData['tickets'], $qrCodeCIDs);
            $this->mailer->Body = $htmlBody;
            
            // Plain text version
            $this->mailer->AltBody = $this->getTicketEmailPlainText($ticketData, $ticketData['tickets']);
            
            $this->mailer->send();
            
            foreach ($ticketData['tickets'] as $index => $ticket) {
                $qrFilePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'qr_ticket_' . $ticket['maChiTiet'] . '.png';
                if (file_exists($qrFilePath)) {
                    @unlink($qrFilePath);
                }
            }
            
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
     * Send cancellation confirmation email
     * 
     * @param array $bookingData Booking information
     * @param array $ticketDetails Ticket details
     * @param int $refundPoints Refund points amount
     * @return array Result with success status and message
     */
    public function sendCancellationEmail($bookingData, $ticketDetails, $refundPoints = 0) {
        try {
            $this->mailer->clearAddresses();
            
            // Determine recipient emails
            $recipientEmails = [];
            if (!empty($bookingData['emailNguoiDung']) && filter_var($bookingData['emailNguoiDung'], FILTER_VALIDATE_EMAIL)) {
                $recipientEmails[] = $bookingData['emailNguoiDung'];
            } else {
                // Use passenger emails if no user email
                foreach ($ticketDetails as $ticket) {
                    if (!empty($ticket['emailHanhKhach']) && filter_var($ticket['emailHanhKhach'], FILTER_VALIDATE_EMAIL)) {
                        $recipientEmails[] = $ticket['emailHanhKhach'];
                    }
                }
                $recipientEmails = array_unique($recipientEmails);
            }
            
            if (empty($recipientEmails)) {
                error_log("[v0] No valid recipient emails for cancellation of booking ID: " . $bookingData['maDatVe']);
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
            $this->mailer->Subject = 'Xác nhận hủy vé - Xegoo - Mã đặt vé: ' . $bookingData['maDatVe'];
            
            $htmlBody = $this->getCancellationEmailTemplate($bookingData, $ticketDetails, $refundPoints);
            $this->mailer->Body = $htmlBody;
            
            // Plain text version
            $this->mailer->AltBody = $this->getCancellationEmailPlainText($bookingData, $ticketDetails, $refundPoints);
            
            $this->mailer->send();
            
            error_log("[v0] Cancellation email sent successfully to: " . implode(', ', $recipientEmails));
            
            return [
                'success' => true,
                'message' => 'Email xác nhận hủy vé đã được gửi thành công!'
            ];
            
        } catch (Exception $e) {
            error_log("Send cancellation email error: " . $this->mailer->ErrorInfo);
            return [
                'success' => false,
                'message' => 'Không thể gửi email xác nhận hủy vé: ' . $this->mailer->ErrorInfo
            ];
        }
    }
    /**
     * Send password reset verification code email
     * 
     * @param string $toEmail Recipient email address
     * @param string $toName Recipient name
     * @param string $verificationCode 6-digit verification code
     * @return array Result with success status and message
     */
    public function sendPasswordResetEmail($toEmail, $toName, $verificationCode) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($toEmail, $toName);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Mã xác nhận đặt lại mật khẩu - XeGoo';
            
            $htmlBody = $this->getPasswordResetEmailTemplate($toName, $verificationCode);
            $this->mailer->Body = $htmlBody;
            
            // Plain text version
            $this->mailer->AltBody = "Xin chào $toName,\n\n"
                . "Bạn đã yêu cầu đặt lại mật khẩu tại XeGoo.\n\n"
                . "Mã xác nhận của bạn là: $verificationCode\n\n"
                . "Mã này có hiệu lực trong 10 phút.\n\n"
                . "Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.\n\n"
                . "Trân trọng,\nĐội ngũ Xegoo";
            
            $this->mailer->send();
            
            return [
                'success' => true,
                'message' => 'Email xác nhận đặt lại mật khẩu đã được gửi thành công!'
            ];
            
        } catch (Exception $e) {
            error_log("Send password reset email error: " . $this->mailer->ErrorInfo);
            return [
                'success' => false,
                'message' => 'Không thể gửi email xác nhận đặt lại mật khẩu: ' . $this->mailer->ErrorInfo
            ];
        }
    }
    
    /**
     * Send new password email
     * 
     * @param string $toEmail Recipient email address
     * @param string $toName Recipient name
     * @param string $newPassword New password
     * @return array Result with success status and message
     */
    public function sendNewPasswordEmail($toEmail, $toName, $newPassword) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($toEmail, $toName);
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Mật khẩu mới của bạn - XeGoo';
            
            $htmlBody = $this->getNewPasswordEmailTemplate($toName, $newPassword);
            $this->mailer->Body = $htmlBody;
            
            // Plain text version
            $this->mailer->AltBody = "Xin chào $toName,\n\n"
                . "Mật khẩu mới của bạn là: $newPassword\n\n"
                . "Vui lòng đăng nhập và đổi mật khẩu ngay sau khi đăng nhập.\n\n"
                . "Trân trọng,\nĐội ngũXegoo";
            
            $this->mailer->send();
            
            return [
                'success' => true,
                'message' => 'Email mật khẩu mới đã được gửi thành công!'
            ];
            
        } catch (Exception $e) {
            error_log("Send new password email error: " . $this->mailer->ErrorInfo);
            return [
                'success' => false,
                'message' => 'Không thể gửi email mật khẩu mới: ' . $this->mailer->ErrorInfo
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
                .header { background: linear-gradient(135deg, #f4481f 0%, #ff6b35 100%); color: white; padding: 30px; text-align: center; border-radius: 10px; }
                .content { padding: 20px; background: #f9f9f9; border-radius: 10px; margin: 20px 0; }
                .code { font-size: 24px; font-weight: bold; color: #f4481f; text-align: center; padding: 15px; background: white; border: 1px solid #ddd; border-radius: 5px; }
                .footer { text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>XeGoo</h2>
                    <p>Mã xác nhận đăng ký tài khoản</p>
                </div>
                <div class="content">
                    <p>Xin chào ' . htmlspecialchars($toName) . ',</p>
                    <p>Cảm ơn bạn đã đăng ký tài khoản tại Xegoo.</p>
                    <p>Mã xác nhận của bạn là:</p>
                    <div class="code">' . $verificationCode . '</div>
                    <p>Mã này có hiệu lực trong 10 phút.</p>
                    <p>Nếu bạn không yêu cầu mã này, vui lòng bỏ qua email này.</p>
                </div>
                <div class="footer">
                    <p>Email này được gửi tự động, vui lòng không trả lời.</p>
                    <p>&copy; 2025 XeGoo. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Get HTML template for ticket email
     */
    private function getTicketEmailTemplate($bookingData, $ticketDetails, $qrCodeCIDs = []) {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background: #f5f5f5; }
                .container { max-width: 650px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #f4481f 0%, #ff6b35 100%); color: white; padding: 35px; text-align: center; border-radius: 12px 12px 0 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
                .header h2 { margin: 0 0 10px 0; font-size: 28px; }
                .header p { margin: 0; font-size: 16px; opacity: 0.95; }
                .content { padding: 30px; background: white; border-radius: 0 0 12px 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
                .section-title { font-size: 20px; font-weight: bold; color: #f4481f; margin: 25px 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #f4481f; }
                .ticket { margin: 20px 0; padding: 20px; background: #fffef0; border: 2px solid #f4481f; border-radius: 10px; }
                .ticket h4 { margin: 0 0 15px 0; color: #f4481f; font-size: 18px; }
                .info-row { margin: 10px 0; display: flex; }
                .info-label { font-weight: bold; min-width: 140px; color: #555; }
                .info-value { color: #333; flex: 1; }
                .qr-code { text-align: center; margin: 20px 0; padding: 20px; background: white; border-radius: 8px; }
                .qr-code img { max-width: 200px; height: auto; border: 3px solid #f4481f; border-radius: 8px; padding: 10px; }
                .qr-code p { margin: 10px 0 0 0; font-size: 13px; color: #666; }
                .warning { background: #fff3cd; padding: 20px; border-radius: 8px; margin: 25px 0; border-left: 4px solid #ffc107; }
                .warning strong { color: #856404; display: block; margin-bottom: 10px; font-size: 16px; }
                .warning ul { margin: 10px 0 0 0; padding-left: 20px; }
                .warning li { margin: 8px 0; color: #856404; }
                .driver-info { background: #e8f5e9; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #4caf50; }
                .driver-info .info-row { margin: 8px 0; }
                .footer { text-align: center; font-size: 12px; color: #666; margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; }
                .highlight { color: #f4481f; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>🚌 Xegoo</h2>
                    <p>Xác nhận đặt vé - Mã đặt vé: ' . htmlspecialchars($bookingData['maDatVe']) . '</p>
                </div>
                <div class="content">
                    <p style="font-size: 16px; margin-bottom: 20px;">Cảm ơn bạn đã đặt vé tại Xegoo!</p>
                    
                    <div class="section-title">📋 Thông tin đặt vé</div>
                    <div class="info-row">
                        <span class="info-label">Mã đặt vé:</span>
                        <span class="info-value highlight">XG-' . htmlspecialchars($bookingData['maDatVe']) . '</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Ngày đặt:</span>
                        <span class="info-value">' . date('d/m/Y H:i', strtotime($bookingData['ngayDat'])) . '</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tổng tiền:</span>
                        <span class="info-value highlight">' . number_format($bookingData['tongTienSauGiam'], 0, ',', '.') . ' VNĐ</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Phương thức:</span>
                        <span class="info-value">' . htmlspecialchars($bookingData['phuongThucThanhToan']) . '</span>
                    </div>
                    
                    <div class="section-title">🎫 Chi tiết vé</div>';
        
        foreach ($ticketDetails as $index => $ticket) {
            $html .= '
                    <div class="ticket">
                        <h4>Vé #' . ($index + 1) . ' - Ghế ' . htmlspecialchars($ticket['soGhe']) . '</h4>
                        <div class="info-row">
                            <span class="info-label">👤 Hành khách:</span>
                            <span class="info-value">' . htmlspecialchars($ticket['hoTenHanhKhach']) . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">📍 Tuyến:</span>
                            <span class="info-value">' . htmlspecialchars($ticket['diemDi']) . ' → ' . htmlspecialchars($ticket['diemDen']) . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">📅 Ngày khởi hành:</span>
                            <span class="info-value">' . date('d/m/Y', strtotime($ticket['thoiGianKhoiHanh'])) . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">🕐 Giờ khởi hành:</span>
                            <span class="info-value highlight">' . date('H:i', strtotime($ticket['thoiGianKhoiHanh'])) . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">📍 Điểm đón:</span>
                            <span class="info-value">' . htmlspecialchars($ticket['diemDonTen']) . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">📍 Điểm trả:</span>
                            <span class="info-value">' . htmlspecialchars($ticket['diemTraTen']) . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">🚌 Biển số xe:</span>
                            <span class="info-value">' . htmlspecialchars($ticket['bienSo']) . '</span>
                        </div>';
            
            if (!empty($ticket['tenTaiXe'])) {
                $html .= '
                        <div class="driver-info">
                            <strong style="color: #2e7d32; margin-bottom: 8px; display: block;">👨‍✈️ Thông tin tài xế</strong>
                            <div class="info-row">
                                <span class="info-label">Tên tài xế:</span>
                                <span class="info-value">' . htmlspecialchars($ticket['tenTaiXe']) . '</span>
                            </div>';
                
                if (!empty($ticket['soDienThoaiTaiXe'])) {
                    $html .= '
                            <div class="info-row">
                                <span class="info-label">📞 SĐT tài xế:</span>
                                <span class="info-value">' . htmlspecialchars($ticket['soDienThoaiTaiXe']) . '</span>
                            </div>';
                }
                
                $html .= '
                        </div>';
            }
            
            $html .= '
                        <div class="info-row">
                            <span class="info-label">💰 Giá vé:</span>
                            <span class="info-value highlight">' . number_format($ticket['seatPrice'], 0, ',', '.') . ' VNĐ</span>
                        </div>';
            
            if (isset($qrCodeCIDs[$index])) {
                $html .= '
                        <div class="qr-code">
                            <p style="font-weight: bold; color: #f4481f; margin-bottom: 15px; font-size: 16px;">📱 Mã QR vé của bạn</p>
                            <img src="cid:' . $qrCodeCIDs[$index] . '" alt="QR Code">
                            <p>Vui lòng xuất trình mã QR này khi lên xe</p>
                        </div>';
            }
            
            $html .= '
                    </div>';
        }
        
        $html .= '
                    <div class="warning">
                        <strong>⚠️ Lưu ý quan trọng:</strong>
                        <ul>
                            <li>Vui lòng có mặt tại điểm đón trước giờ khởi hành <strong>15 phút</strong></li>
                            <li>Mang theo CMND/CCCD để đối chiếu thông tin</li>
                            <li>Xuất trình mã QR hoặc mã đặt vé khi lên xe</li>
                            <li>Liên hệ tài xế hoặc hotline <strong>1900-xxxx</strong> nếu cần hỗ trợ</li>
                        </ul>
                    </div>
                    
                    <p style="font-size: 16px; margin-top: 25px;">Chúc bạn có một chuyến đi an toàn và vui vẻ! 🎉</p>
                    <p style="margin-top: 15px;">Trân trọng,<br><strong style="color: #f4481f;">Đội ngũ Xegoo</strong></p>
                </div>
                
                <div class="footer">
                    <p>Email này được gửi tự động, vui lòng không trả lời.</p>
                    <p>&copy; 2025 Xegoo. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>';
        
        return $html;
    }
    
    /**
     * Get HTML template for cancellation email
     */
    private function getCancellationEmailTemplate($bookingData, $ticketDetails, $refundPoints) {
        $refundAmount = $bookingData['tongTienSauGiam'] * 0.2;
        
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background: #f5f5f5; }
                .container { max-width: 650px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%); color: white; padding: 35px; text-align: center; border-radius: 12px 12px 0 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
                .header h2 { margin: 0 0 10px 0; font-size: 28px; }
                .header p { margin: 0; font-size: 16px; opacity: 0.95; }
                .content { padding: 30px; background: white; border-radius: 0 0 12px 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
                .alert-box { background: #fee2e2; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #dc2626; }
                .alert-box strong { color: #991b1b; display: block; margin-bottom: 10px; font-size: 18px; }
                .section-title { font-size: 20px; font-weight: bold; color: #dc2626; margin: 25px 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #dc2626; }
                .info-box { background: #fffef0; padding: 20px; border-radius: 8px; margin: 20px 0; border: 2px solid #f4481f; }
                .info-row { margin: 10px 0; display: flex; }
                .info-label { font-weight: bold; min-width: 140px; color: #555; }
                .info-value { color: #333; flex: 1; }
                .refund-box { background: #d1fae5; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #10b981; }
                .refund-box strong { color: #065f46; display: block; margin-bottom: 10px; font-size: 18px; }
                .ticket-list { margin: 15px 0; }
                .ticket-item { background: #f9fafb; padding: 12px; margin: 8px 0; border-radius: 6px; border-left: 3px solid #6b7280; }
                .footer { text-align: center; font-size: 12px; color: #666; margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; }
                .highlight { color: #dc2626; font-weight: bold; }
                .success-highlight { color: #10b981; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>❌ Hệ thống Xegoo</h2>
                    <p>Xác nhận hủy vé - Mã đặt vé: ' . htmlspecialchars($bookingData['maDatVe']) . '</p>
                </div>
                <div class="content">
                    <div class="alert-box">
                        <strong>🔔 Vé của bạn đã được hủy thành công</strong>
                        <p style="margin: 5px 0 0 0; color: #991b1b;">Chúng tôi đã nhận được yêu cầu hủy vé của bạn và đã xử lý thành công.</p>
                    </div>
                    
                    <div class="section-title">📋 Thông tin đặt vé đã hủy</div>
                    <div class="info-box">
                        <div class="info-row">
                            <span class="info-label">Mã đặt vé:</span>
                            <span class="info-value highlight">XG-' . htmlspecialchars($bookingData['maDatVe']) . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Ngày đặt:</span>
                            <span class="info-value">' . date('d/m/Y H:i', strtotime($bookingData['ngayDat'])) . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Ngày hủy:</span>
                            <span class="info-value">' . date('d/m/Y H:i') . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Tổng tiền vé:</span>
                            <span class="info-value">' . number_format($bookingData['tongTienSauGiam'], 0, ',', '.') . ' VNĐ</span>
                        </div>
                    </div>
                    
                    <div class="section-title">🎫 Danh sách vé đã hủy</div>
                    <div class="ticket-list">';
        
        foreach ($ticketDetails as $index => $ticket) {
            $html .= '
                        <div class="ticket-item">
                            <strong>Vé #' . ($index + 1) . ' - Ghế ' . htmlspecialchars($ticket['soGhe']) . '</strong><br>
                            👤 ' . htmlspecialchars($ticket['hoTenHanhKhach']) . '<br>
                            📍 ' . htmlspecialchars($ticket['diemDi']) . ' → ' . htmlspecialchars($ticket['diemDen']) . '<br>
                            📅 ' . date('d/m/Y H:i', strtotime($ticket['thoiGianKhoiHanh'])) . '<br>
                            💰 ' . number_format($ticket['seatPrice'], 0, ',', '.') . ' VNĐ
                        </div>';
        }
        
        $html .= '
                    </div>';
        
        if ($refundPoints > 0) {
            $html .= '
                    <div class="refund-box">
                        <strong>💰 Thông tin hoàn tiền</strong>
                        <div class="info-row">
                            <span class="info-label">Số tiền hoàn (20%):</span>
                            <span class="info-value success-highlight">' . number_format($refundAmount, 0, ',', '.') . ' VNĐ</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Điểm tích lũy nhận được:</span>
                            <span class="info-value success-highlight">' . number_format($refundPoints) . ' điểm</span>
                        </div>
                        <p style="margin: 15px 0 0 0; color: #065f46; font-size: 14px;">
                            ℹ️ Điểm tích lũy đã được cộng vào tài khoản của bạn và có thể sử dụng cho các đặt vé tiếp theo (1 điểm = 100đ).
                        </p>
                    </div>';
        } else {
            $html .= '
                    <div class="info-box">
                        <p style="margin: 0; color: #666;">
                            ℹ️ Vé đã được hủy nhưng không có hoàn tiền do bạn chưa đăng nhập tài khoản khi đặt vé.
                        </p>
                    </div>';
        }
        
        $html .= '
                    <div style="background: #f3f4f6; padding: 20px; border-radius: 8px; margin: 25px 0;">
                        <p style="margin: 0 0 10px 0; font-weight: bold; color: #374151;">📞 Cần hỗ trợ?</p>
                        <p style="margin: 0; color: #6b7280;">
                            Nếu bạn có bất kỳ thắc mắc nào về việc hủy vé hoặc hoàn tiền, vui lòng liên hệ:<br>
                            <strong>Hotline:</strong> 1900-xxxx<br>
                            <strong>Email:</strong> support@xegoo.com
                        </p>
                    </div>
                    
                    <p style="font-size: 16px; margin-top: 25px;">Cảm ơn bạn đã sử dụng dịch vụ của Xegoo. Chúng tôi hy vọng được phục vụ bạn trong những chuyến đi tiếp theo! 🚌</p>
                    <p style="margin-top: 15px;">Trân trọng,<br><strong style="color: #f4481f;">Đội ngũ Xegoo</strong></p>
                </div>
                
                <div class="footer">
                    <p>Email này được gửi tự động, vui lòng không trả lời.</p>
                    <p>&copy; 2025  Xegoo. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>';
        
        return $html;
    }
    /**
     * Get HTML template for password reset email
     */
    private function getPasswordResetEmailTemplate($toName, $verificationCode) {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #f4481f 0%, #ff6b35 100%); color: white; padding: 30px; text-align: center; border-radius: 10px; }
                .content { padding: 20px; background: #f9f9f9; border-radius: 10px; margin: 20px 0; }
                .code { font-size: 24px; font-weight: bold; color: #f4481f; text-align: center; padding: 15px; background: white; border: 1px solid #ddd; border-radius: 5px; }
                .warning { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ffc107; }
                .footer { text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>🔐 XeGoo</h2>
                    <p>Đặt lại mật khẩu</p>
                </div>
                <div class="content">
                    <p>Xin chào ' . htmlspecialchars($toName) . ',</p>
                    <p>Bạn đã yêu cầu đặt lại mật khẩu tại Xegoo.</p>
                    <p>Mã xác nhận của bạn là:</p>
                    <div class="code">' . $verificationCode . '</div>
                    <p>Mã này có hiệu lực trong <strong>10 phút</strong>.</p>
                    <div class="warning">
                        <strong>⚠️ Lưu ý:</strong> Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này và mật khẩu của bạn sẽ không bị thay đổi.
                    </div>
                </div>
                <div class="footer">
                    <p>Email này được gửi tự động, vui lòng không trả lời.</p>
                    <p>&copy; 2025 Xegoo. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Get HTML template for new password email
     */
    private function getNewPasswordEmailTemplate($toName, $newPassword) {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #f4481f 0%, #ff6b35 100%); color: white; padding: 30px; text-align: center; border-radius: 10px; }
                .content { padding: 20px; background: #f9f9f9; border-radius: 10px; margin: 20px 0; }
                .password-box { font-size: 20px; font-weight: bold; color: #f4481f; text-align: center; padding: 20px; background: white; border: 2px solid #f4481f; border-radius: 5px; margin: 20px 0; }
                .warning { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ffc107; }
                .footer { text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>✅ XeGoo</h2>
                    <p>Mật khẩu mới của bạn</p>
                </div>
                <div class="content">
                    <p>Xin chào ' . htmlspecialchars($toName) . ',</p>
                    <p>Mật khẩu của bạn đã được đặt lại thành công!</p>
                    <p>Mật khẩu mới của bạn là:</p>
                    <div class="password-box">' . htmlspecialchars($newPassword) . '</div>
                    <div class="warning">
                        <strong>🔒 Bảo mật:</strong>
                        <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                            <li>Vui lòng đăng nhập và đổi mật khẩu ngay sau khi nhận được email này</li>
                            <li>Không chia sẻ mật khẩu với bất kỳ ai</li>
                            <li>Sử dụng mật khẩu mạnh kết hợp chữ, số và ký tự đặc biệt</li>
                        </ul>
                    </div>
                    <p style="text-align: center; margin-top: 20px;">
                        <a href="' . BASE_URL . '/login" style="display: inline-block; padding: 12px 30px; background: #f4481f; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">Đăng nhập ngay</a>
                    </p>
                </div>
                <div class="footer">
                    <p>Email này được gửi tự động, vui lòng không trả lời.</p>
                    <p>&copy; 2025 XeGoo. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>';
    }
    /**
     * Get plain text version of ticket email
     */
    private function getTicketEmailPlainText($bookingData, $ticketDetails) {
        $text = "HỆ THỐNG XEGOO - XÁC NHẬN ĐẶT VÉ\n\n";
        $text .= "Cảm ơn bạn đã đặt vé tại Xegoo!\n\n";
        $text .= "THÔNG TIN ĐẶT VÉ CỦA BẠN\n";
        $text .= "Mã đặt vé: XG-" . $bookingData['maDatVe'] . "\n";
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
            
            if (!empty($ticket['tenTaiXe'])) {
                $text .= "Tài xế: " . $ticket['tenTaiXe'] . "\n";
                if (!empty($ticket['soDienThoaiTaiXe'])) {
                    $text .= "SĐT tài xế: " . $ticket['soDienThoaiTaiXe'] . "\n";
                }
            }
            
            $text .= "Giá vé: " . number_format($ticket['seatPrice'], 0, ',', '.') . " VNĐ\n";
        }
        
        $text .= "\nLƯU Ý QUAN TRỌNG:\n";
        $text .= "- Vui lòng có mặt tại điểm đón trước giờ khởi hành 15 phút\n";
        $text .= "- Mang theo CMND/CCCD để đối chiếu thông tin\n";
        $text .= "- Xuất trình mã QR hoặc mã đặt vé khi lên xe\n";
        $text .= "- Liên hệ tài xế hoặc hotline 1900-xxxx nếu cần hỗ trợ\n\n";
        
        $text .= "Chúc bạn có một chuyến đi an toàn và vui vẻ!\n\n";
        $text .= "Trân trọng,\nĐội ngũ Xegoo";
        
        return $text;
    }
    
    /**
     * Get plain text version of cancellation email
     */
    private function getCancellationEmailPlainText($bookingData, $ticketDetails, $refundPoints) {
        $refundAmount = $bookingData['tongTienSauGiam'] * 0.2;
        
        $text = "HỆ THỐNG XEGOO - XÁC NHẬN HỦY VÉ\n\n";
        $text .= "Vé của bạn đã được hủy thành công!\n\n";
        $text .= "THÔNG TIN ĐẶT VÉ ĐÃ HỦY\n";
        $text .= "Mã đặt vé: XG-" . $bookingData['maDatVe'] . "\n";
        $text .= "Ngày đặt: " . date('d/m/Y H:i', strtotime($bookingData['ngayDat'])) . "\n";
        $text .= "Ngày hủy: " . date('d/m/Y H:i') . "\n";
        $text .= "Tổng tiền vé: " . number_format($bookingData['tongTienSauGiam'], 0, ',', '.') . " VNĐ\n\n";
        
        $text .= "DANH SÁCH VÉ ĐÃ HỦY\n";
        foreach ($ticketDetails as $index => $ticket) {
            $text .= "\nVé #" . ($index + 1) . " - Ghế " . $ticket['soGhe'] . "\n";
            $text .= "Hành khách: " . $ticket['hoTenHanhKhach'] . "\n";
            $text .= "Tuyến: " . $ticket['diemDi'] . " → " . $ticket['diemDen'] . "\n";
            $text .= "Ngày khởi hành: " . date('d/m/Y H:i', strtotime($ticket['thoiGianKhoiHanh'])) . "\n";
            $text .= "Giá vé: " . number_format($ticket['seatPrice'], 0, ',', '.') . " VNĐ\n";
        }
        
        if ($refundPoints > 0) {
            $text .= "\nTHÔNG TIN HOÀN TIỀN\n";
            $text .= "Số tiền hoàn (20%): " . number_format($refundAmount, 0, ',', '.') . " VNĐ\n";
            $text .= "Điểm tích lũy nhận được: " . number_format($refundPoints) . " điểm\n";
            $text .= "Điểm tích lũy đã được cộng vào tài khoản của bạn (1 điểm = 100đ).\n";
        } else {
            $text .= "\nVé đã được hủy nhưng không có hoàn tiền do bạn chưa đăng nhập tài khoản.\n";
        }
        
        $text .= "\nCẦN HỖ TRỢ?\n";
        $text .= "Hotline: 1900-xxxx\n";
        $text .= "Email: support@xegoo.com\n\n";
        
        $text .= "Cảm ơn bạn đã sử dụng dịch vụ của Xegoo!\n\n";
        $text .= "Trân trọng,\nĐội ngũ Xegoo";
        
        return $text;
    }
}
?>
