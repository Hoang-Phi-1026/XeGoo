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
            
            QRcode::png($qrData, $tempFile, QR_ECLEVEL_L, 3, 1);

            
            return true;
            
        } catch (Exception $e) {
            error_log("QRCodeGenerator save error: " . $e->getMessage());
            return false;
        }
    }
}
?>
