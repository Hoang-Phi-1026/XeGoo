<?php
require_once __DIR__ . '/phpqrcode/phpqrcode.php';

class QRCodeGenerator {
    
    /**
     * Generate QR code for ticket detail
     * 
     * @param array $ticketData Array containing ticket information
     * @return string Base64 encoded PNG image
     */
    public static function generateTicketQR($ticketData) {
        try {
            // Create QR code data string with all ticket information
            $qrData = json_encode([
                'maChiTiet' => $ticketData['maChiTiet'] ?? '',
                'maDatVe' => $ticketData['maDatVe'] ?? '',
                'maChuyenXe' => $ticketData['maChuyenXe'] ?? '',
                'maGhe' => $ticketData['maGhe'] ?? '',
                'soGhe' => $ticketData['soGhe'] ?? '',
                'hoTenHanhKhach' => $ticketData['hoTenHanhKhach'] ?? '',
                'emailHanhKhach' => $ticketData['emailHanhKhach'] ?? '',
                'soDienThoaiHanhKhach' => $ticketData['soDienThoaiHanhKhach'] ?? '',
                'giaVe' => $ticketData['seatPrice'] ?? '',
                'kyHieuTuyen' => $ticketData['kyHieuTuyen'] ?? '',
                'diemDi' => $ticketData['diemDi'] ?? '',
                'diemDen' => $ticketData['diemDen'] ?? '',
                'ngayKhoiHanh' => date('Y-m-d', strtotime($ticketData['thoiGianKhoiHanh'] ?? '')),
                'thoiGianKhoiHanh' => date('H:i', strtotime($ticketData['thoiGianKhoiHanh'] ?? '')),
                'diemDon' => $ticketData['diemDonTen'] ?? '',
                'diemTra' => $ticketData['diemTraTen'] ?? '',
                'bienSo' => $ticketData['bienSo'] ?? '',
            ], JSON_UNESCAPED_UNICODE);
            
            // Generate QR code to temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'qr_');
            
            // Generate QR code with error correction level H (high)
            QRcode::png($qrData, $tempFile, QR_ECLEVEL_L, 3, 1);

            
            // Read the generated image
            $imageData = file_get_contents($tempFile);
            
            // Delete temporary file
            unlink($tempFile);
            
            // Return base64 encoded image
            return 'data:image/png;base64,' . base64_encode($imageData);
            
        } catch (Exception $e) {
            error_log("QRCodeGenerator error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Generate QR code and save to file
     * 
     * @param array $ticketData Array containing ticket information
     * @param string $outputPath Path to save the QR code image
     * @return bool Success status
     */
    public static function generateAndSaveTicketQR($ticketData, $outputPath) {
        try {
            $qrData = json_encode([
                'maChiTiet' => $ticketData['maChiTiet'] ?? '',
                'maDatVe' => $ticketData['maDatVe'] ?? '',
                'maChuyenXe' => $ticketData['maChuyenXe'] ?? '',
                'maGhe' => $ticketData['maGhe'] ?? '',
                'soGhe' => $ticketData['soGhe'] ?? '',
                'hoTenHanhKhach' => $ticketData['hoTenHanhKhach'] ?? '',
                'emailHanhKhach' => $ticketData['emailHanhKhach'] ?? '',
                'soDienThoaiHanhKhach' => $ticketData['soDienThoaiHanhKhach'] ?? '',
                'giaVe' => $ticketData['seatPrice'] ?? '',
                'kyHieuTuyen' => $ticketData['kyHieuTuyen'] ?? '',
                'diemDi' => $ticketData['diemDi'] ?? '',
                'diemDen' => $ticketData['diemDen'] ?? '',
                'ngayKhoiHanh' => date('Y-m-d', strtotime($ticketData['thoiGianKhoiHanh'] ?? '')),
                'thoiGianKhoiHanh' => date('H:i', strtotime($ticketData['thoiGianKhoiHanh'] ?? '')),
                'diemDon' => $ticketData['diemDonTen'] ?? '',
                'diemTra' => $ticketData['diemTraTen'] ?? '',
                'bienSo' => $ticketData['bienSo'] ?? '',
            ], JSON_UNESCAPED_UNICODE);
            
            QRcode::png($qrData, $outputPath, QR_ECLEVEL_L, 3, 1);

            
            return true;
            
        } catch (Exception $e) {
            error_log("QRCodeGenerator save error: " . $e->getMessage());
            return false;
        }
    }

    public static function generateBase64($data) {
        try {
            // Encode data chắc chắn là string
            if (!is_string($data)) {
                $data = json_encode($data, JSON_UNESCAPED_UNICODE);
            }

            // Tạo file tạm thời an toàn
            $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'qr_' . uniqid() . '.png';

            // Sinh QR
            QRcode::png($data, $tempFile, QR_ECLEVEL_L, 4, 1);

            // Đọc file vào base64
            $imageData = @file_get_contents($tempFile);
            if (!$imageData) {
                throw new Exception('Không đọc được file QR code.');
            }

            // Xóa file sau khi dùng
            @unlink($tempFile);

            // Trả về base64 image
            return 'data:image/png;base64,' . base64_encode($imageData);

        } catch (Exception $e) {
            error_log("Lỗi QRCodeGenerator::generateBase64: " . $e->getMessage());
            return null;
        }
    }

    public static function generateQRFile($ticketData, $bookingId) {
        try {
            // Create QR data with ticket information
            $qrData = json_encode([
                'maDatVe' => $bookingId,
                'maChiTiet' => $ticketData['maChiTiet'] ?? '',
                'soGhe' => $ticketData['soGhe'] ?? '',
                'hoTenHanhKhach' => $ticketData['hoTenHanhKhach'] ?? '',
                'diemDi' => $ticketData['diemDi'] ?? '',
                'diemDen' => $ticketData['diemDen'] ?? '',
                'ngayKhoiHanh' => date('Y-m-d', strtotime($ticketData['thoiGianKhoiHanh'] ?? '')),
                'thoiGianKhoiHanh' => date('H:i', strtotime($ticketData['thoiGianKhoiHanh'] ?? '')),
                'bienSo' => $ticketData['bienSo'] ?? '',
            ], JSON_UNESCAPED_UNICODE);
            
            // Create temp file path
            $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'qr_ticket_' . ($ticketData['maChiTiet'] ?? uniqid()) . '.png';
            
            // Generate QR code with higher quality for email
            QRcode::png($qrData, $tempFile, QR_ECLEVEL_M, 6, 2);
            
            return $tempFile;
            
        } catch (Exception $e) {
            error_log("Lỗi QRCodeGenerator::generateQRFile: " . $e->getMessage());
            return null;
        }
    }

}
?>
