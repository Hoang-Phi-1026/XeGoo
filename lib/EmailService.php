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
        $this->fromEmail = 'xegoo.notifications@gmail.com';
        $this->fromName = 'Hệ thống XeGoo';
        $this->appPassword = 'jwsi bxtp ugfh lcvv'; // App Password bạn tạo
        
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
            
            $this->mailer->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
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
            error_log("[v0] EmailService::sendTicketEmail - START");
            error_log("[v0] Ticket data keys: " . json_encode(array_keys($ticketData)));
            
            if (empty($ticketData['maDatVe'])) {
                error_log("[v0] Missing maDatVe in ticket data");
                return [
                    'success' => false,
                    'message' => 'Thiếu mã đặt vé.'
                ];
            }
            
            if (empty($ticketData['tickets']) || !is_array($ticketData['tickets'])) {
                error_log("[v0] Missing or invalid tickets array");
                return [
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin vé.'
                ];
            }
            
            error_log("[v0] Number of tickets: " . count($ticketData['tickets']));
            
            $this->mailer->clearAddresses();
            
            // Determine recipient emails
            $recipientEmails = [];
            
            if (!empty($ticketData['emailNguoiDung'])) {
                $email = trim($ticketData['emailNguoiDung']);
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $recipientEmails[] = $email;
                    error_log("[v0] Added user email: " . $email);
                } else {
                    error_log("[v0] Invalid user email format: " . $email);
                }
            }
            
            if (empty($recipientEmails) && !empty($ticketData['passengerEmails'])) {
                foreach ($ticketData['passengerEmails'] as $email) {
                    $email = trim($email);
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $recipientEmails[] = $email;
                        error_log("[v0] Added passenger email: " . $email);
                    }
                }
            }
            
            if (empty($recipientEmails)) {
                error_log("[v0] No emails in emailNguoiDung or passengerEmails, checking tickets");
                foreach ($ticketData['tickets'] as $ticket) {
                    if (!empty($ticket['emailHanhKhach'])) {
                        $email = trim($ticket['emailHanhKhach']);
                        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $recipientEmails[] = $email;
                            error_log("[v0] Added email from ticket: " . $email);
                        }
                    }
                }
                $recipientEmails = array_unique($recipientEmails);
            }
            
            if (empty($recipientEmails)) {
                error_log("[v0] No valid recipient emails found for booking ID: " . $ticketData['maDatVe']);
                return [
                    'success' => false,
                    'message' => 'Không tìm thấy địa chỉ email hợp lệ để gửi.'
                ];
            }
            
            error_log("[v0] Final recipient emails: " . json_encode($recipientEmails));
            
            // Add all valid recipient emails
            foreach ($recipientEmails as $email) {
                $this->mailer->addAddress($email);
            }
            
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Xác nhận đặt vé - XeGoo - Mã đặt vé: ' . $ticketData['maDatVe'];
            
            $qrCodeCIDs = [];
            foreach ($ticketData['tickets'] as $index => $ticket) {
                try {
                    if (empty($ticket['maChiTiet'])) {
                        error_log("[v0] Ticket #$index missing maChiTiet");
                        continue;
                    }
                    
                    $qrFilePath = QRCodeGenerator::generateQRFile($ticket, $ticketData['maDatVe']);
                    if ($qrFilePath && file_exists($qrFilePath)) {
                        $cid = 'qr_' . $index . '_' . uniqid();
                        $this->mailer->addEmbeddedImage($qrFilePath, $cid, 'qr_code_' . $index . '.png');
                        $qrCodeCIDs[$index] = $cid;
                        error_log("[v0] QR code generated for ticket #$index");
                    } else {
                        error_log("[v0] Failed to generate QR code for ticket #$index");
                    }
                } catch (Exception $qrError) {
                    error_log("[v0] QR generation error for ticket #$index: " . $qrError->getMessage());
                }
            }
            
            $htmlBody = $this->getTicketEmailTemplate($ticketData, $ticketData['tickets'], $qrCodeCIDs);
            $this->mailer->Body = $htmlBody;
            
            // Plain text version
            $this->mailer->AltBody = $this->getTicketEmailPlainText($ticketData, $ticketData['tickets']);
            
            error_log("[v0] Attempting to send email...");
            $this->mailer->send();
            error_log("[v0] Email sent successfully!");
            
            foreach ($ticketData['tickets'] as $ticket) {
                if (!empty($ticket['maChiTiet'])) {
                    $qrFilePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'qr_ticket_' . $ticket['maChiTiet'] . '.png';
                    if (file_exists($qrFilePath)) {
                        @unlink($qrFilePath);
                    }
                }
            }
            
            error_log("[v0] Ticket email sent successfully to: " . implode(', ', $recipientEmails));
            
            return [
                'success' => true,
                'message' => 'Email xác nhận vé đã được gửi thành công!'
            ];
            
        } catch (Exception $e) {
            error_log("[v0] Send ticket email error: " . $e->getMessage());
            error_log("[v0] Error file: " . $e->getFile() . " line " . $e->getLine());
            error_log("[v0] PHPMailer ErrorInfo: " . $this->mailer->ErrorInfo);
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
                body { 
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                    line-height: 1.6; 
                    color: #1a1a1a; 
                    background: #f8f9fa;
                    margin: 0;
                    padding: 0;
                }
                .container { 
                    max-width: 600px; 
                    margin: 40px auto; 
                    background: #ffffff;
                    border-radius: 12px;
                    overflow: hidden;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
                }
                .header { 
                    background: linear-gradient(135deg, #f4481f 0%, #ff6b35 100%); 
                    color: white; 
                    padding: 40px 30px; 
                    text-align: center;
                }
                .header h1 { 
                    margin: 0 0 8px 0; 
                    font-size: 32px; 
                    font-weight: 700;
                    letter-spacing: -0.5px;
                }
                .header p { 
                    margin: 0; 
                    font-size: 15px; 
                    opacity: 0.95;
                }
                .content { 
                    padding: 40px 30px;
                }
                .content p {
                    margin: 0 0 16px 0;
                    color: #4a5568;
                    font-size: 15px;
                }
                .code-box { 
                    background: #f7fafc;
                    border: 2px solid #e2e8f0;
                    border-radius: 8px;
                    padding: 24px;
                    text-align: center;
                    margin: 28px 0;
                }
                .code-box p {
                    margin: 0 0 12px 0;
                    font-size: 13px;
                    color: #718096;
                    font-weight: 600;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }
                .code { 
                    font-size: 36px; 
                    font-weight: 700; 
                    color: #f4481f;
                    letter-spacing: 8px;
                    font-family: "Courier New", monospace;
                }
                .info-box {
                    background: #fffbeb;
                    border-left: 4px solid #f59e0b;
                    padding: 16px 20px;
                    border-radius: 4px;
                    margin: 24px 0;
                }
                .info-box p {
                    margin: 0;
                    font-size: 14px;
                    color: #92400e;
                }
                .footer { 
                    text-align: center; 
                    padding: 24px 30px;
                    background: #f7fafc;
                    border-top: 1px solid #e2e8f0;
                }
                .footer p {
                    margin: 4px 0;
                    font-size: 13px; 
                    color: #718096;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>XeGoo</h1>
                    <p>Xác thực tài khoản của bạn</p>
                </div>
                <div class="content">
                    <p>Xin chào <strong>' . htmlspecialchars($toName) . '</strong>,</p>
                    <p>Cảm ơn bạn đã đăng ký tài khoản tại XeGoo. Để hoàn tất quá trình đăng ký, vui lòng sử dụng mã xác thực bên dưới:</p>
                    
                    <div class="code-box">
                        <p>Mã xác thực của bạn</p>
                        <div class="code">' . $verificationCode . '</div>
                    </div>
                    
                    <div class="info-box">
                        <p><strong>Lưu ý:</strong> Mã này có hiệu lực trong 10 phút. Nếu bạn không yêu cầu mã này, vui lòng bỏ qua email.</p>
                    </div>
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
        $maDatVe = htmlspecialchars($bookingData['maDatVe'] ?? 'N/A');
        $ngayDat = isset($bookingData['ngayDat']) ? date('d/m/Y H:i', strtotime($bookingData['ngayDat'])) : 'N/A';
        $tongTien = isset($bookingData['tongTienSauGiam']) ? number_format($bookingData['tongTienSauGiam'], 0, ',', '.') : '0';
        $phuongThuc = htmlspecialchars($bookingData['phuongThucThanhToan'] ?? 'N/A');
        
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body { 
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                    line-height: 1.6; 
                    color: #1a1a1a; 
                    background: #f8f9fa;
                    margin: 0;
                    padding: 0;
                }
                .container { 
                    max-width: 650px; 
                    margin: 40px auto; 
                    background: #ffffff;
                    border-radius: 12px;
                    overflow: hidden;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
                }
                .header { 
                    background: linear-gradient(135deg, #f4481f 0%, #ff6b35 100%); 
                    color: white; 
                    padding: 40px 30px; 
                    text-align: center;
                }
                .header h1 { 
                    margin: 0 0 8px 0; 
                    font-size: 32px; 
                    font-weight: 700;
                    letter-spacing: -0.5px;
                }
                .header p { 
                    margin: 0; 
                    font-size: 15px; 
                    opacity: 0.95;
                }
                .content { 
                    padding: 40px 30px;
                }
                .success-message {
                    background: #d1fae5;
                    border-left: 4px solid #10b981;
                    padding: 16px 20px;
                    border-radius: 4px;
                    margin-bottom: 32px;
                }
                .success-message p {
                    margin: 0;
                    color: #065f46;
                    font-size: 15px;
                    font-weight: 500;
                }
                .section-title { 
                    font-size: 18px; 
                    font-weight: 700; 
                    color: #1a1a1a; 
                    margin: 32px 0 16px 0; 
                    padding-bottom: 8px; 
                    border-bottom: 2px solid #e2e8f0;
                }
                .booking-summary {
                    background: #f7fafc;
                    border-radius: 8px;
                    padding: 20px;
                    margin: 20px 0;
                }
                .info-row { 
                    display: flex;
                    padding: 10px 0;
                    border-bottom: 1px solid #e2e8f0;
                }
                .info-row:last-child {
                    border-bottom: none;
                }
                .info-label { 
                    font-weight: 600; 
                    min-width: 160px; 
                    color: #4a5568;
                    font-size: 14px;
                }
                .info-value { 
                    color: #1a1a1a; 
                    flex: 1;
                    font-size: 14px;
                }
                .highlight { 
                    color: #f4481f; 
                    font-weight: 700;
                }
                .ticket-card { 
                    background: #ffffff;
                    border: 2px solid #e2e8f0;
                    border-radius: 12px;
                    padding: 24px;
                    margin: 20px 0;
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
                }
                .ticket-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 20px;
                    padding-bottom: 16px;
                    border-bottom: 2px dashed #e2e8f0;
                }
                .ticket-number {
                    font-size: 20px;
                    font-weight: 700;
                    color: #1a1a1a;
                }
                .ticket-code {
                    background: #f4481f;
                    color: white;
                    padding: 6px 12px;
                    border-radius: 6px;
                    font-size: 13px;
                    font-weight: 600;
                    font-family: "Courier New", monospace;
                }
                .route-info {
                    background: #eff6ff;
                    border-radius: 8px;
                    padding: 16px;
                    margin: 16px 0;
                }
                .route-info .info-row {
                    border-bottom: none;
                    padding: 6px 0;
                }
                .location-box {
                    background: #fef3c7;
                    border-radius: 8px;
                    padding: 16px;
                    margin: 16px 0;
                }
                .location-box .location-item {
                    margin: 12px 0;
                }
                .location-box .location-label {
                    font-weight: 700;
                    color: #92400e;
                    font-size: 13px;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    margin-bottom: 4px;
                }
                .location-box .location-name {
                    font-weight: 600;
                    color: #1a1a1a;
                    font-size: 15px;
                    margin-bottom: 2px;
                }
                .location-box .location-address {
                    color: #4a5568;
                    font-size: 13px;
                    line-height: 1.5;
                }
                .driver-info {
                    background: #ecfdf5;
                    border-radius: 8px;
                    padding: 16px;
                    margin: 16px 0;
                }
                .driver-info .info-row {
                    border-bottom: none;
                    padding: 6px 0;
                }
                .qr-section { 
                    text-align: center; 
                    margin: 24px 0;
                    padding: 24px;
                    background: #f7fafc;
                    border-radius: 8px;
                }
                .qr-section p {
                    margin: 0 0 16px 0;
                    font-weight: 600;
                    color: #4a5568;
                    font-size: 14px;
                }
                .qr-section img { 
                    max-width: 180px; 
                    height: auto;
                    border: 3px solid #f4481f;
                    border-radius: 8px;
                    padding: 12px;
                    background: white;
                }
                .warning-box {
                    background: #fef3f2;
                    border-left: 4px solid #ef4444;
                    padding: 20px;
                    border-radius: 4px;
                    margin: 32px 0;
                }
                .warning-box strong {
                    color: #991b1b;
                    display: block;
                    margin-bottom: 12px;
                    font-size: 16px;
                }
                .warning-box ul {
                    margin: 0;
                    padding-left: 20px;
                }
                .warning-box li {
                    margin: 8px 0;
                    color: #7f1d1d;
                    font-size: 14px;
                }
                .footer { 
                    text-align: center; 
                    padding: 24px 30px;
                    background: #f7fafc;
                    border-top: 1px solid #e2e8f0;
                }
                .footer p {
                    margin: 4px 0;
                    font-size: 13px; 
                    color: #718096;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>XeGoo</h1>
                    <p>Xác nhận đặt vé thành công</p>
                </div>
                <div class="content">
                    <div class="success-message">
                        <p>Cảm ơn bạn đã đặt vé tại XeGoo! Vé của bạn đã được xác nhận.</p>
                    </div>
                    
                    <div class="section-title">Thông tin đặt vé</div>
                    <div class="booking-summary">
                        <div class="info-row">
                            <span class="info-label">Mã đặt vé</span>
                            <span class="info-value highlight">XG-' . $maDatVe . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Ngày đặt</span>
                            <span class="info-value">' . $ngayDat . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Tổng tiền</span>
                            <span class="info-value highlight">' . $tongTien . ' VNĐ</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Phương thức thanh toán</span>
                            <span class="info-value">' . $phuongThuc . '</span>
                        </div>
                    </div>
                    
                    <div class="section-title">Chi tiết vé</div>';
        
        foreach ($ticketDetails as $index => $ticket) {
            $maChiTiet = htmlspecialchars($ticket['maChiTiet'] ?? 'N/A');
            $soGhe = htmlspecialchars($ticket['soGhe'] ?? 'N/A');
            $hoTen = htmlspecialchars($ticket['hoTenHanhKhach'] ?? 'N/A');
            $diemDi = htmlspecialchars($ticket['diemDi'] ?? 'N/A');
            $diemDen = htmlspecialchars($ticket['diemDen'] ?? 'N/A');
            $ngayKhoiHanh = isset($ticket['thoiGianKhoiHanh']) ? date('d/m/Y', strtotime($ticket['thoiGianKhoiHanh'])) : 'N/A';
            $gioKhoiHanh = isset($ticket['thoiGianKhoiHanh']) ? date('H:i', strtotime($ticket['thoiGianKhoiHanh'])) : 'N/A';
            $bienSo = htmlspecialchars($ticket['bienSo'] ?? 'N/A');
            $diemDonTen = htmlspecialchars($ticket['diemDonTen'] ?? 'Chưa có thông tin');
            $diemDonDiaChi = htmlspecialchars($ticket['diemDonDiaChi'] ?? '');
            $diemTraTen = htmlspecialchars($ticket['diemTraTen'] ?? 'Chưa có thông tin');
            $diemTraDiaChi = htmlspecialchars($ticket['diemTraDiaChi'] ?? '');
            $giaVe = isset($ticket['seatPrice']) ? number_format($ticket['seatPrice'], 0, ',', '.') : '0';
            
            $html .= '
                    <div class="ticket-card">
                        <div class="ticket-header">
                            <div class="ticket-number">Vé #' . ($index + 1) . ' - Ghế ' . $soGhe . '</div>
                            <div class="ticket-code">MÃ VÉ: ' . $maChiTiet . '</div>
                        </div>
                        
                        <div class="info-row">
                            <span class="info-label">Hành khách</span>
                            <span class="info-value"><strong>' . $hoTen . '</strong></span>
                        </div>
                        
                        <div class="route-info">
                            <div class="info-row">
                                <span class="info-label">Tuyến đường</span>
                                <span class="info-value"><strong>' . $diemDi . ' → ' . $diemDen . '</strong></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Ngày khởi hành</span>
                                <span class="info-value">' . $ngayKhoiHanh . '</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Giờ khởi hành</span>
                                <span class="info-value highlight">' . $gioKhoiHanh . '</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Biển số xe</span>
                                <span class="info-value">' . $bienSo . '</span>
                            </div>
                        </div>
                        
                        <div class="location-box">
                            <div class="location-item">
                                <div class="location-label">Điểm đón</div>
                                <div class="location-name">' . $diemDonTen . '</div>';
            
            if (!empty($ticket['diemDonDiaChi'])) {
                $html .= '
                                <div class="location-address">' . $diemDonDiaChi . '</div>';
            }
            
            $html .= '
                            </div>
                            <div class="location-item" style="margin-top: 16px;">
                                <div class="location-label">Điểm trả</div>
                                <div class="location-name">' . $diemTraTen . '</div>';
            
            if (!empty($ticket['diemTraDiaChi'])) {
                $html .= '
                                <div class="location-address">' . $diemTraDiaChi . '</div>';
            }
            
            $html .= '
                            </div>
                        </div>';
            
            if (!empty($ticket['tenTaiXe'])) {
                $tenTaiXe = htmlspecialchars($ticket['tenTaiXe']);
                $html .= '
                        <div class="driver-info">
                            <div class="info-row">
                                <span class="info-label">Tài xế</span>
                                <span class="info-value"><strong>' . $tenTaiXe . '</strong></span>
                            </div>';
                
                if (!empty($ticket['soDienThoaiTaiXe'])) {
                    $sdtTaiXe = htmlspecialchars($ticket['soDienThoaiTaiXe']);
                    $html .= '
                            <div class="info-row">
                                <span class="info-label">Số điện thoại</span>
                                <span class="info-value">' . $sdtTaiXe . '</span>
                            </div>';
                }
                
                $html .= '
                        </div>';
            }
            
            $html .= '
                        <div class="info-row" style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #e2e8f0;">
                            <span class="info-label">Giá vé</span>
                            <span class="info-value highlight" style="font-size: 18px;">' . $giaVe . ' VNĐ</span>
                        </div>';
            
            if (isset($qrCodeCIDs[$index])) {
                $html .= '
                        <div class="qr-section">
                            <p>Mã QR vé của bạn</p>
                            <img src="cid:' . $qrCodeCIDs[$index] . '" alt="QR Code">
                            <p style="margin-top: 12px; font-size: 13px; color: #718096;">Vui lòng xuất trình mã này khi lên xe</p>
                        </div>';
            }
            
            $html .= '
                    </div>';
        }
        
        $html .= '
                    <div class="warning-box">
                        <strong>Lưu ý quan trọng</strong>
                        <ul>
                            <li>Có mặt tại điểm đón trước giờ khởi hành <strong>15 phút</strong></li>
                            <li>Mang theo CMND/CCCD để đối chiếu thông tin</li>
                            <li>Xuất trình mã QR hoặc mã vé khi lên xe</li>
                            <li>Liên hệ tài xế hoặc hotline <strong>1900-xxxx</strong> nếu cần hỗ trợ</li>
                        </ul>
                    </div>
                    
                    <p style="font-size: 15px; margin-top: 32px; color: #4a5568;">Chúc bạn có một chuyến đi an toàn và vui vẻ!</p>
                    <p style="margin-top: 16px; color: #1a1a1a;">Trân trọng,<br><strong style="color: #f4481f;">Đội ngũ XeGoo</strong></p>
                </div>
                
                <div class="footer">
                    <p>Email này được gửi tự động, vui lòng không trả lời.</p>
                    <p>&copy; 2025 XeGoo. All rights reserved.</p>
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
                body { 
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                    line-height: 1.6; 
                    color: #1a1a1a; 
                    background: #f8f9fa;
                    margin: 0;
                    padding: 0;
                }
                .container { 
                    max-width: 650px; 
                    margin: 40px auto; 
                    background: #ffffff;
                    border-radius: 12px;
                    overflow: hidden;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
                }
                .header { 
                    background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%); 
                    color: white; 
                    padding: 40px 30px; 
                    text-align: center;
                }
                .header h1 { 
                    margin: 0 0 8px 0; 
                    font-size: 32px; 
                    font-weight: 700;
                    letter-spacing: -0.5px;
                }
                .header p { 
                    margin: 0; 
                    font-size: 15px; 
                    opacity: 0.95;
                }
                .content { 
                    padding: 40px 30px;
                }
                .alert-box { 
                    background: #fef2f2; 
                    border-left: 4px solid #dc2626;
                    padding: 20px; 
                    border-radius: 4px; 
                    margin: 20px 0;
                }
                .alert-box strong { 
                    color: #991b1b; 
                    display: block; 
                    margin-bottom: 8px; 
                    font-size: 16px;
                    font-weight: 700;
                }
                .alert-box p {
                    margin: 0;
                    color: #7f1d1d;
                    font-size: 14px;
                }
                .section-title { 
                    font-size: 18px; 
                    font-weight: 700; 
                    color: #1a1a1a; 
                    margin: 32px 0 16px 0; 
                    padding-bottom: 8px; 
                    border-bottom: 2px solid #e2e8f0;
                }
                .info-box { 
                    background: #f7fafc;
                    border-radius: 8px;
                    padding: 20px;
                    margin: 20px 0;
                }
                .info-row { 
                    display: flex;
                    padding: 10px 0;
                    border-bottom: 1px solid #e2e8f0;
                }
                .info-row:last-child {
                    border-bottom: none;
                }
                .info-label { 
                    font-weight: 600; 
                    min-width: 160px; 
                    color: #4a5568;
                    font-size: 14px;
                }
                .info-value { 
                    color: #1a1a1a; 
                    flex: 1;
                    font-size: 14px;
                }
                .refund-box { 
                    background: #d1fae5;
                    border-left: 4px solid #10b981;
                    padding: 20px; 
                    border-radius: 4px; 
                    margin: 20px 0;
                }
                .refund-box strong { 
                    color: #065f46; 
                    display: block; 
                    margin-bottom: 12px; 
                    font-size: 16px;
                    font-weight: 700;
                }
                .refund-box .info-row {
                    border-bottom: none;
                    padding: 8px 0;
                }
                .refund-box p {
                    margin: 0;
                    color: #065f46;
                    font-size: 13px;
                }
                .ticket-list { 
                    margin: 16px 0;
                }
                .ticket-item { 
                    background: #f7fafc;
                    padding: 16px; 
                    margin: 12px 0; 
                    border-radius: 8px;
                    border-left: 3px solid #6b7280;
                }
                .ticket-item strong {
                    display: block;
                    margin-bottom: 8px;
                    color: #1a1a1a;
                    font-size: 15px;
                }
                .ticket-item div {
                    margin: 4px 0;
                    color: #4a5568;
                    font-size: 14px;
                }
                .support-box {
                    background: #f7fafc;
                    padding: 20px;
                    border-radius: 8px;
                    margin: 24px 0;
                }
                .support-box p {
                    margin: 8px 0;
                    color: #4a5568;
                    font-size: 14px;
                }
                .support-box strong {
                    color: #1a1a1a;
                }
                .footer { 
                    text-align: center; 
                    padding: 24px 30px;
                    background: #f7fafc;
                    border-top: 1px solid #e2e8f0;
                }
                .footer p {
                    margin: 4px 0;
                    font-size: 13px; 
                    color: #718096;
                }
                .highlight { 
                    color: #dc2626; 
                    font-weight: 700;
                }
                .success-highlight { 
                    color: #10b981; 
                    font-weight: 700;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>XeGoo</h1>
                    <p>Xác nhận hủy vé</p>
                </div>
                <div class="content">
                    <div class="alert-box">
                        <strong>Vé của bạn đã được hủy thành công</strong>
                        <p>Chúng tôi đã nhận được yêu cầu hủy vé của bạn và đã xử lý thành công.</p>
                    </div>
                    
                    <div class="section-title">Thông tin đặt vé đã hủy</div>
                    <div class="info-box">
                        <div class="info-row">
                            <span class="info-label">Mã đặt vé</span>
                            <span class="info-value highlight">XG-' . htmlspecialchars($bookingData['maDatVe']) . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Ngày đặt</span>
                            <span class="info-value">' . date('d/m/Y H:i', strtotime($bookingData['ngayDat'])) . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Ngày hủy</span>
                            <span class="info-value">' . date('d/m/Y H:i') . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Tổng tiền vé</span>
                            <span class="info-value">' . number_format($bookingData['tongTienSauGiam'], 0, ',', '.') . ' VNĐ</span>
                        </div>
                    </div>
                    
                    <div class="section-title">Danh sách vé đã hủy</div>
                    <div class="ticket-list">';
        
        foreach ($ticketDetails as $index => $ticket) {
            $html .= '
                        <div class="ticket-item">
                            <strong>Vé #' . ($index + 1) . ' - Ghế ' . htmlspecialchars($ticket['soGhe']) . ' (Mã vé: ' . htmlspecialchars($ticket['maChiTiet']) . ')</strong>
                            <div>' . htmlspecialchars($ticket['hoTenHanhKhach']) . '</div>
                            <div>' . htmlspecialchars($ticket['diemDi']) . ' → ' . htmlspecialchars($ticket['diemDen']) . '</div>
                            <div>' . date('d/m/Y H:i', strtotime($ticket['thoiGianKhoiHanh'])) . '</div>
                            <div style="font-weight: 600; margin-top: 4px;">' . number_format($ticket['seatPrice'], 0, ',', '.') . ' VNĐ</div>
                        </div>';
        }
        
        $html .= '
                    </div>';
        
        if ($refundPoints > 0) {
            $html .= '
                    <div class="refund-box">
                        <strong>Thông tin hoàn tiền</strong>
                        <div class="info-row">
                            <span class="info-label">Số tiền hoàn (20%)</span>
                            <span class="info-value success-highlight">' . number_format($refundAmount, 0, ',', '.') . ' VNĐ</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Điểm tích lũy nhận được</span>
                            <span class="info-value success-highlight">' . number_format($refundPoints) . ' điểm</span>
                        </div>
                        <p>Điểm tích lũy đã được cộng vào tài khoản của bạn và có thể sử dụng cho các đặt vé tiếp theo (1 điểm = 100đ).</p>
                    </div>';
        } else {
            $html .= '
                    <div class="info-box">
                        <p style="margin: 0; color: #4a5568;">Vé đã được hủy nhưng không có hoàn tiền do bạn chưa đăng nhập tài khoản khi đặt vé.</p>
                    </div>';
        }
        
        $html .= '
                    <div class="support-box">
                        <p style="margin: 0 0 12px 0; font-weight: 700; color: #1a1a1a;">Cần hỗ trợ?</p>
                        <p>Nếu bạn có bất kỳ thắc mắc nào về việc hủy vé hoặc hoàn tiền, vui lòng liên hệ:</p>
                        <p><strong>Hotline:</strong> 1900-xxxx</p>
                        <p><strong>Email:</strong> support@xegoo.com</p>
                    </div>
                    
                    <p style="font-size: 15px; margin-top: 32px; color: #4a5568;">Cảm ơn bạn đã sử dụng dịch vụ của XeGoo. Chúng tôi hy vọng được phục vụ bạn trong những chuyến đi tiếp theo!</p>
                    <p style="margin-top: 16px; color: #1a1a1a;">Trân trọng,<br><strong style="color: #f4481f;">Đội ngũ XeGoo</strong></p>
                </div>
                
                <div class="footer">
                    <p>Email này được gửi tự động, vui lòng không trả lời.</p>
                    <p>&copy; 2025 XeGoo. All rights reserved.</p>
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
                body { 
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                    line-height: 1.6; 
                    color: #1a1a1a; 
                    background: #f8f9fa;
                    margin: 0;
                    padding: 0;
                }
                .container { 
                    max-width: 600px; 
                    margin: 40px auto; 
                    background: #ffffff;
                    border-radius: 12px;
                    overflow: hidden;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
                }
                .header { 
                    background: linear-gradient(135deg, #f4481f 0%, #ff6b35 100%); 
                    color: white; 
                    padding: 40px 30px; 
                    text-align: center;
                }
                .header h1 { 
                    margin: 0 0 8px 0; 
                    font-size: 32px; 
                    font-weight: 700;
                    letter-spacing: -0.5px;
                }
                .header p { 
                    margin: 0; 
                    font-size: 15px; 
                    opacity: 0.95;
                }
                .content { 
                    padding: 40px 30px;
                }
                .content p {
                    margin: 0 0 16px 0;
                    color: #4a5568;
                    font-size: 15px;
                }
                .code-box { 
                    background: #f7fafc;
                    border: 2px solid #e2e8f0;
                    border-radius: 8px;
                    padding: 24px;
                    text-align: center;
                    margin: 28px 0;
                }
                .code-box p {
                    margin: 0 0 12px 0;
                    font-size: 13px;
                    color: #718096;
                    font-weight: 600;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }
                .code { 
                    font-size: 36px; 
                    font-weight: 700; 
                    color: #f4481f;
                    letter-spacing: 8px;
                    font-family: "Courier New", monospace;
                }
                .warning { 
                    background: #fffbeb;
                    border-left: 4px solid #f59e0b;
                    padding: 16px 20px;
                    border-radius: 4px;
                    margin: 24px 0;
                }
                .warning strong {
                    display: block;
                    margin-bottom: 8px;
                    color: #92400e;
                    font-size: 14px;
                    font-weight: 700;
                }
                .warning p {
                    margin: 0;
                    color: #92400e;
                    font-size: 14px;
                }
                .footer { 
                    text-align: center; 
                    padding: 24px 30px;
                    background: #f7fafc;
                    border-top: 1px solid #e2e8f0;
                }
                .footer p {
                    margin: 4px 0;
                    font-size: 13px; 
                    color: #718096;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>XeGoo</h1>
                    <p>Đặt lại mật khẩu</p>
                </div>
                <div class="content">
                    <p>Xin chào <strong>' . htmlspecialchars($toName) . '</strong>,</p>
                    <p>Bạn đã yêu cầu đặt lại mật khẩu tại XeGoo. Để tiếp tục, vui lòng sử dụng mã xác thực bên dưới:</p>
                    
                    <div class="code-box">
                        <p>Mã xác thực của bạn</p>
                        <div class="code">' . $verificationCode . '</div>
                    </div>
                    
                    <div class="warning">
                        <strong>Lưu ý</strong>
                        <p>Mã này có hiệu lực trong 10 phút. Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này và mật khẩu của bạn sẽ không bị thay đổi.</p>
                    </div>
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
                body { 
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                    line-height: 1.6; 
                    color: #1a1a1a; 
                    background: #f8f9fa;
                    margin: 0;
                    padding: 0;
                }
                .container { 
                    max-width: 600px; 
                    margin: 40px auto; 
                    background: #ffffff;
                    border-radius: 12px;
                    overflow: hidden;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
                }
                .header { 
                    background: linear-gradient(135deg, #10b981 0%, #059669 100%); 
                    color: white; 
                    padding: 40px 30px; 
                    text-align: center;
                }
                .header h1 { 
                    margin: 0 0 8px 0; 
                    font-size: 32px; 
                    font-weight: 700;
                    letter-spacing: -0.5px;
                }
                .header p { 
                    margin: 0; 
                    font-size: 15px; 
                    opacity: 0.95;
                }
                .content { 
                    padding: 40px 30px;
                }
                .content p {
                    margin: 0 0 16px 0;
                    color: #4a5568;
                    font-size: 15px;
                }
                .password-box { 
                    background: #f7fafc;
                    border: 2px solid #10b981;
                    border-radius: 8px;
                    padding: 24px;
                    text-align: center;
                    margin: 28px 0;
                }
                .password-box p {
                    margin: 0 0 12px 0;
                    font-size: 13px;
                    color: #718096;
                    font-weight: 600;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }
                .password { 
                    font-size: 28px; 
                    font-weight: 700; 
                    color: #10b981;
                    letter-spacing: 2px;
                    font-family: "Courier New", monospace;
                    word-break: break-all;
                }
                .security-box { 
                    background: #fffbeb;
                    border-left: 4px solid #f59e0b;
                    padding: 20px;
                    border-radius: 4px;
                    margin: 24px 0;
                }
                .security-box strong {
                    display: block;
                    margin-bottom: 12px;
                    color: #92400e;
                    font-size: 15px;
                    font-weight: 700;
                }
                .security-box ul {
                    margin: 0;
                    padding-left: 20px;
                }
                .security-box li {
                    margin: 8px 0;
                    color: #92400e;
                    font-size: 14px;
                }
                .cta-button {
                    text-align: center;
                    margin: 28px 0;
                }
                .cta-button a {
                    display: inline-block;
                    padding: 14px 32px;
                    background: #f4481f;
                    color: white;
                    text-decoration: none;
                    border-radius: 8px;
                    font-weight: 600;
                    font-size: 15px;
                }
                .footer { 
                    text-align: center; 
                    padding: 24px 30px;
                    background: #f7fafc;
                    border-top: 1px solid #e2e8f0;
                }
                .footer p {
                    margin: 4px 0;
                    font-size: 13px; 
                    color: #718096;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>XeGoo</h1>
                    <p>Mật khẩu mới của bạn</p>
                </div>
                <div class="content">
                    <p>Xin chào <strong>' . htmlspecialchars($toName) . '</strong>,</p>
                    <p>Mật khẩu của bạn đã được đặt lại thành công! Dưới đây là mật khẩu mới của bạn:</p>
                    
                    <div class="password-box">
                        <p>Mật khẩu mới</p>
                        <div class="password">' . htmlspecialchars($newPassword) . '</div>
                    </div>
                    
                    <div class="security-box">
                        <strong>Bảo mật tài khoản</strong>
                        <ul>
                            <li>Vui lòng đăng nhập và đổi mật khẩu ngay sau khi nhận được email này</li>
                            <li>Không chia sẻ mật khẩu với bất kỳ ai</li>
                            <li>Sử dụng mật khẩu mạnh kết hợp chữ, số và ký tự đặc biệt</li>
                        </ul>
                    </div>
                    
                    <div class="cta-button">
                        <a href="' . BASE_URL . '/login">Đăng nhập ngay</a>
                    </div>
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
        
        $text .= "Cảm ơn bạn đã sử dụng dịch vụ của XeGoo!\n\n";
        $text .= "Trân trọng,\nĐội ngũ Xegoo";
        
        return $text;
    }
}
?>
