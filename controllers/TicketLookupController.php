<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/QRCodeGenerator.php';

class TicketLookupController {
    
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Show ticket lookup form
     */
    public function index() {
        try {
            include __DIR__ . '/../views/ticket-lookup/index.php';
        } catch (Exception $e) {
            error_log("TicketLookupController index error: " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi tải trang tra cứu vé.';
            header('Location: ' . BASE_URL . '/');
            exit;
        }
    }
    
    /**
     * Search for ticket by code and phone/email
     */
    public function search() {
        header('Content-Type: application/json');
        
        try {
            error_log("[v0] TicketLookup - Search started");
            
            // Get POST data
            $ticketCode = $_POST['ticket_code'] ?? '';
            $contact = $_POST['contact'] ?? '';
            
            error_log("[v0] TicketLookup - Ticket code: $ticketCode, Contact: $contact");
            
            // Validate input
            if (empty($ticketCode) || empty($contact)) {
                error_log("[v0] TicketLookup - Validation failed: empty fields");
                echo json_encode([
                    'success' => false,
                    'message' => 'Vui lòng nhập đầy đủ mã vé và số điện thoại/email'
                ]);
                return;
            }
            
            // Search for ticket
            $ticketDetails = $this->findTicket($ticketCode, $contact);
            
            error_log("[v0] TicketLookup - Ticket found: " . ($ticketDetails ? 'yes' : 'no'));
            
            if (!$ticketDetails) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Không tìm thấy vé với thông tin đã nhập. Vui lòng kiểm tra lại mã vé và số điện thoại/email.'
                ]);
                return;
            }
            
            // Generate QR code for the ticket
            $ticketDetails['qrCode'] = QRCodeGenerator::generateTicketQR($ticketDetails);
            
            // Format the response
            $response = [
                'success' => true,
                'ticket' => $this->formatTicketResponse($ticketDetails)
            ];
            
            error_log("[v0] TicketLookup - Response prepared successfully");
            echo json_encode($response);
            
        } catch (Exception $e) {
            error_log("[v0] TicketLookup search error: " . $e->getMessage());
            error_log("[v0] TicketLookup stack trace: " . $e->getTraceAsString());
            echo json_encode([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tra cứu vé. Vui lòng thử lại sau.'
            ]);
        }
    }
    
    /**
     * Find ticket by code and contact info
     */
    private function findTicket($ticketCode, $contact) {
        try {
            error_log("[v0] TicketLookup - Executing SQL query");
            
            $sql = "SELECT cd.*, 
                           d.maDatVe, d.ngayDat, d.tongTien, d.giamGia, d.tongTienSauGiam,
                           d.phuongThucThanhToan, d.loaiDatVe, d.trangThai as trangThaiDatVe,
                           g.soGhe,
                           c.ngayKhoiHanh, c.thoiGianKhoiHanh, c.maChuyenXe,
                           t.kyHieuTuyen, t.diemDi, t.diemDen,
                           l.gioKetThuc,
                           dd.tenDiem as diemDonTen, dd.diaChi as diemDonDiaChi,
                           dt.tenDiem as diemTraTen, dt.diaChi as diemTraDiaChi,
                           p.bienSo,
                           lp.tenLoaiPhuongTien, lp.soChoMacDinh
                    FROM chitiet_datve cd
                    INNER JOIN datve d ON cd.maDatVe = d.maDatVe
                    INNER JOIN chuyenxe c ON cd.maChuyenXe = c.maChuyenXe
                    INNER JOIN ghe g ON cd.maGhe = g.maGhe
                    INNER JOIN lichtrinh l ON c.maLichTrinh = l.maLichTrinh
                    INNER JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong
                    INNER JOIN phuongtien p ON c.maPhuongTien = p.maPhuongTien
                    INNER JOIN loaiphuongtien lp ON p.maLoaiPhuongTien = lp.maLoaiPhuongTien
                    LEFT JOIN tuyenduong_diemdontra dd ON cd.maDiemDon = dd.maDiem
                    LEFT JOIN tuyenduong_diemdontra dt ON cd.maDiemTra = dt.maDiem
                    WHERE cd.maChiTiet = ?
                    AND (cd.soDienThoaiHanhKhach = ? OR cd.emailHanhKhach = ?)
                    LIMIT 1";
            
            $result = fetch($sql, [$ticketCode, $contact, $contact]);
            
            error_log("[v0] TicketLookup - Query executed, result: " . ($result ? 'found' : 'not found'));
            
            return $result;
            
        } catch (Exception $e) {
            error_log("[v0] TicketLookup findTicket error: " . $e->getMessage());
            error_log("[v0] TicketLookup findTicket trace: " . $e->getTraceAsString());
            return null;
        }
    }
    
    /**
     * Format ticket data for response
     */
    private function formatTicketResponse($ticket) {
        return [
            'maChiTiet' => $ticket['maChiTiet'],
            'maDatVe' => $ticket['maDatVe'],
            'kyHieuTuyen' => $ticket['kyHieuTuyen'],
            'diemDi' => $ticket['diemDi'],
            'diemDen' => $ticket['diemDen'],
            'bienSo' => $ticket['bienSo'],
            'tenLoaiPhuongTien' => $ticket['tenLoaiPhuongTien'],
            'soChoMacDinh' => $ticket['soChoMacDinh'],
            'ngayKhoiHanh' => date('d/m/Y', strtotime($ticket['thoiGianKhoiHanh'])),
            'gioKhoiHanh' => date('H:i', strtotime($ticket['thoiGianKhoiHanh'])),
            'gioKetThuc' => date('H:i', strtotime($ticket['gioKetThuc'])),
            'thoiGianKhoiHanh' => $ticket['thoiGianKhoiHanh'],
            'hoTenHanhKhach' => $ticket['hoTenHanhKhach'],
            'emailHanhKhach' => $ticket['emailHanhKhach'],
            'soDienThoaiHanhKhach' => $ticket['soDienThoaiHanhKhach'],
            'soGhe' => $ticket['soGhe'],
            'giaVe' => $ticket['giaVe'],
            'diemDonTen' => $ticket['diemDonTen'] ?? '',
            'diemDonDiaChi' => $ticket['diemDonDiaChi'] ?? '',
            'diemTraTen' => $ticket['diemTraTen'] ?? '',
            'diemTraDiaChi' => $ticket['diemTraDiaChi'] ?? '',
            'trangThai' => $ticket['trangThai'],
            'trangThaiDatVe' => $ticket['trangThaiDatVe'],
            'phuongThucThanhToan' => $ticket['phuongThucThanhToan'],
            'tongTienSauGiam' => $ticket['tongTienSauGiam'],
            'qrCode' => $ticket['qrCode']
        ];
    }
}
?>
