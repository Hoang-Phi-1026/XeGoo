<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/QRCodeGenerator.php';

class MyTicketsController {
    
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Show active tickets page (paid tickets that haven't departed yet)
     */
    public function index() {
        try {
            // Check if user is logged in
            if (!isset($_SESSION['user_id'])) {
                $_SESSION['error'] = 'Vui lòng đăng nhập để xem vé của bạn!';
                header('Location: ' . BASE_URL . '/login');
                exit;
            }
            
            $userId = $_SESSION['user_id'];
            
            // Get active tickets (paid and future departure)
            $activeTickets = $this->getActiveTickets($userId);
            
            // Group tickets by booking ID
            $groupedTickets = $this->groupTicketsByBooking($activeTickets);
            
            $viewData = compact('groupedTickets');
            extract($viewData);
            
            include __DIR__ . '/../views/my-tickets/index.php';
            
        } catch (Exception $e) {
            error_log("MyTicketsController index error: " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi tải danh sách vé.';
            header('Location: ' . BASE_URL . '/');
            exit;
        }
    }
    
    /**
     * Show booking history page (all past bookings)
     */
    public function history() {
        try {
            // Check if user is logged in
            if (!isset($_SESSION['user_id'])) {
                $_SESSION['error'] = 'Vui lòng đăng nhập để xem lịch sử đặt vé!';
                header('Location: ' . BASE_URL . '/login');
                exit;
            }
            
            $userId = $_SESSION['user_id'];
            
            // Get all bookings history
            $bookingHistory = $this->getBookingHistory($userId);
            
            // Group tickets by booking ID
            $groupedHistory = $this->groupTicketsByBooking($bookingHistory);
            
            $viewData = compact('groupedHistory');
            extract($viewData);
            
            include __DIR__ . '/../views/my-tickets/history.php';
            
        } catch (Exception $e) {
            error_log("MyTicketsController history error: " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi tải lịch sử đặt vé.';
            header('Location: ' . BASE_URL . '/my-tickets');
            exit;
        }
    }
    
    /**
     * Show ticket detail page
     */
    public function detail($bookingId) {
        try {
            // Check if user is logged in
            if (!isset($_SESSION['user_id'])) {
                $_SESSION['error'] = 'Vui lòng đăng nhập để xem chi tiết vé!';
                header('Location: ' . BASE_URL . '/login');
                exit;
            }
            
            $userId = $_SESSION['user_id'];
            
            // Get booking details
            $bookingDetails = $this->getBookingDetails($bookingId, $userId);
            
            if (!$bookingDetails) {
                $_SESSION['error'] = 'Không tìm thấy thông tin vé hoặc bạn không có quyền xem vé này.';
                header('Location: ' . BASE_URL . '/my-tickets');
                exit;
            }
            
            $tripGroups = $this->groupTicketsByTrip($bookingDetails);
            
            foreach ($tripGroups as &$tripGroup) {
                foreach ($tripGroup['tickets'] as &$ticket) {
                    $ticket['qrCode'] = QRCodeGenerator::generateTicketQR($ticket);
                }
                unset($ticket); // Unset reference to prevent bugs
            }
            unset($tripGroup); // Unset reference to prevent bugs
            
            $bookingInfo = $bookingDetails[0];
            
            $viewData = compact('tripGroups', 'bookingInfo', 'bookingId');
            extract($viewData);
            
            include __DIR__ . '/../views/my-tickets/detail.php';
            
        } catch (Exception $e) {
            error_log("MyTicketsController detail error: " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi tải chi tiết vé.';
            header('Location: ' . BASE_URL . '/my-tickets');
            exit;
        }
    }
    
    /**
     * Get active tickets for user (paid and future departure)
     */
    private function getActiveTickets($userId) {
        try {
            $sql = "SELECT d.*, 
                           cd.hoTenHanhKhach, cd.emailHanhKhach, cd.soDienThoaiHanhKhach,
                           cd.giaVe as seatPrice, g.soGhe,
                           c.ngayKhoiHanh, c.thoiGianKhoiHanh, 
                           t.kyHieuTuyen, t.diemDi, t.diemDen,
                           dd.tenDiem as diemDonTen, dd.diaChi as diemDonDiaChi,
                           dt.tenDiem as diemTraTen, dt.diaChi as diemTraDiaChi,
                           p.bienSo
                    FROM datve d
                    INNER JOIN chitiet_datve cd ON d.maDatVe = cd.maDatVe
                    INNER JOIN chuyenxe c ON cd.maChuyenXe = c.maChuyenXe
                    INNER JOIN ghe g ON cd.maGhe = g.maGhe
                    INNER JOIN lichtrinh l ON c.maLichTrinh = l.maLichTrinh
                    INNER JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong
                    INNER JOIN phuongtien p ON c.maPhuongTien = p.maPhuongTien
                    LEFT JOIN tuyenduong_diemdontra dd ON cd.maDiemDon = dd.maDiem
                    LEFT JOIN tuyenduong_diemdontra dt ON cd.maDiemTra = dt.maDiem
                    WHERE d.maNguoiDung = ? 
                    AND d.trangThai = 'DaThanhToan'
                    AND c.thoiGianKhoiHanh >= NOW()
                    ORDER BY c.thoiGianKhoiHanh ASC";
            
            return fetchAll($sql, [$userId]);
        } catch (Exception $e) {
            error_log("getActiveTickets error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all booking history for user
     */
    private function getBookingHistory($userId) {
        try {
            $sql = "SELECT d.*, 
                           cd.maChiTiet, cd.maChuyenXe, cd.maGhe,
                           cd.hoTenHanhKhach, cd.emailHanhKhach, cd.soDienThoaiHanhKhach,
                           cd.giaVe as seatPrice, g.soGhe,
                           c.ngayKhoiHanh, c.thoiGianKhoiHanh, 
                           t.kyHieuTuyen, t.diemDi, t.diemDen,
                           dd.tenDiem as diemDonTen, dd.diaChi as diemDonDiaChi,
                           dt.tenDiem as diemTraTen, dt.diaChi as diemTraDiaChi,
                           p.bienSo
                    FROM datve d
                    INNER JOIN chitiet_datve cd ON d.maDatVe = cd.maDatVe
                    INNER JOIN chuyenxe c ON cd.maChuyenXe = c.maChuyenXe
                    INNER JOIN ghe g ON cd.maGhe = g.maGhe
                    INNER JOIN lichtrinh l ON c.maLichTrinh = l.maLichTrinh
                    INNER JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong
                    INNER JOIN phuongtien p ON c.maPhuongTien = p.maPhuongTien
                    LEFT JOIN tuyenduong_diemdontra dd ON cd.maDiemDon = dd.maDiem
                    LEFT JOIN tuyenduong_diemdontra dt ON cd.maDiemTra = dt.maDiem
                    WHERE d.maNguoiDung = ?
                    ORDER BY d.ngayDat DESC, c.thoiGianKhoiHanh DESC";
            
            return fetchAll($sql, [$userId]);
        } catch (Exception $e) {
            error_log("getBookingHistory error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get booking details by ID
     */
    private function getBookingDetails($bookingId, $userId) {
        try {
            $sql = "SELECT d.*, 
                           cd.maChiTiet, cd.maChuyenXe, cd.maGhe,
                           cd.hoTenHanhKhach, cd.emailHanhKhach, cd.soDienThoaiHanhKhach,
                           cd.giaVe as seatPrice, g.soGhe,
                           c.ngayKhoiHanh, c.thoiGianKhoiHanh, c.maPhuongTien,
                           t.kyHieuTuyen, t.diemDi, t.diemDen,
                           dd.tenDiem as diemDonTen, dd.diaChi as diemDonDiaChi,
                           dt.tenDiem as diemTraTen, dt.diaChi as diemTraDiaChi,
                           p.bienSo, 
                           lp.tenLoaiPhuongTien, lp.soChoMacDinh,
                           tx.tenNguoiDung as tenTaiXe, tx.soDienThoai as soDienThoaiTaiXe
                    FROM datve d
                    INNER JOIN chitiet_datve cd ON d.maDatVe = cd.maDatVe
                    INNER JOIN chuyenxe c ON cd.maChuyenXe = c.maChuyenXe
                    INNER JOIN ghe g ON cd.maGhe = g.maGhe
                    INNER JOIN lichtrinh l ON c.maLichTrinh = l.maLichTrinh
                    INNER JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong
                    INNER JOIN phuongtien p ON c.maPhuongTien = p.maPhuongTien
                    INNER JOIN loaiphuongtien lp ON p.maLoaiPhuongTien = lp.maLoaiPhuongTien
                    LEFT JOIN tuyenduong_diemdontra dd ON cd.maDiemDon = dd.maDiem
                    LEFT JOIN tuyenduong_diemdontra dt ON cd.maDiemTra = dt.maDiem
                    LEFT JOIN nguoidung tx ON c.maTaiXe = tx.maNguoiDung
                    WHERE d.maDatVe = ? AND d.maNguoiDung = ?
                    ORDER BY g.soGhe ASC";
            
            return fetchAll($sql, [$bookingId, $userId]);
        } catch (Exception $e) {
            error_log("getBookingDetails error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Group tickets by booking ID
     */
    private function groupTicketsByBooking($tickets) {
        $grouped = [];
        
        foreach ($tickets as $ticket) {
            $bookingId = $ticket['maDatVe'];
            
            if (!isset($grouped[$bookingId])) {
                $grouped[$bookingId] = [
                    'booking_info' => [
                        'maDatVe' => $ticket['maDatVe'],
                        'ngayDat' => $ticket['ngayDat'],
                        'tongTien' => $ticket['tongTien'],
                        'giamGia' => $ticket['giamGia'],
                        'tongTienSauGiam' => $ticket['tongTienSauGiam'],
                        'phuongThucThanhToan' => $ticket['phuongThucThanhToan'],
                        'loaiDatVe' => $ticket['loaiDatVe'],
                        'trangThai' => $ticket['trangThai'],
                        'kyHieuTuyen' => $ticket['kyHieuTuyen'],
                        'diemDi' => $ticket['diemDi'],
                        'diemDen' => $ticket['diemDen'],
                        'ngayKhoiHanh' => $ticket['ngayKhoiHanh'],
                        'thoiGianKhoiHanh' => $ticket['thoiGianKhoiHanh'],
                        'bienSo' => $ticket['bienSo']
                    ],
                    'tickets' => []
                ];
            }
            
            $grouped[$bookingId]['tickets'][] = [
                'soGhe' => $ticket['soGhe'],
                'hoTenHanhKhach' => $ticket['hoTenHanhKhach'],
                'emailHanhKhach' => $ticket['emailHanhKhach'],
                'soDienThoaiHanhKhach' => $ticket['soDienThoaiHanhKhach'],
                'giaVe' => $ticket['seatPrice'],
                'diemDonTen' => $ticket['diemDonTen'],
                'diemDonDiaChi' => $ticket['diemDonDiaChi'],
                'diemTraTen' => $ticket['diemTraTen'],
                'diemTraDiaChi' => $ticket['diemTraDiaChi']
            ];
        }
        
        return $grouped;
    }
    
    /**
     * Group tickets by trip (for round-trip bookings)
     */
    private function groupTicketsByTrip($tickets) {
        $tripGroups = [];
        
        foreach ($tickets as $ticket) {
            $tripId = $ticket['maChuyenXe'];
            
            if (!isset($tripGroups[$tripId])) {
                $tripGroups[$tripId] = [
                    'trip_info' => [
                        'maChuyenXe' => $ticket['maChuyenXe'],
                        'kyHieuTuyen' => $ticket['kyHieuTuyen'],
                        'diemDi' => $ticket['diemDi'],
                        'diemDen' => $ticket['diemDen'],
                        'ngayKhoiHanh' => $ticket['ngayKhoiHanh'],
                        'thoiGianKhoiHanh' => $ticket['thoiGianKhoiHanh'],
                        'bienSo' => $ticket['bienSo'],
                        'tenLoaiPhuongTien' => $ticket['tenLoaiPhuongTien'],
                        'soChoMacDinh' => $ticket['soChoMacDinh'],
                        'tenTaiXe' => $ticket['tenTaiXe'],
                        'soDienThoaiTaiXe' => $ticket['soDienThoaiTaiXe']
                    ],
                    'tickets' => []
                ];
            }
            
            $tripGroups[$tripId]['tickets'][] = $ticket;
        }
        
        // Sort trips by departure time
        usort($tripGroups, function($a, $b) {
            return strtotime($a['trip_info']['thoiGianKhoiHanh']) - strtotime($b['trip_info']['thoiGianKhoiHanh']);
        });
        
        return $tripGroups;
    }
}
?>
