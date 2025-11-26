<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/QRCodeGenerator.php';
require_once __DIR__ . '/../lib/EmailService.php';
require_once __DIR__ . '/../helpers/IDEncryptionHelper.php';

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
    public function detail($encryptedBookingId) {
        try {
            $bookingId = IDEncryptionHelper::decryptId($encryptedBookingId);
            
            if (!$bookingId || !IDEncryptionHelper::isValidId($bookingId)) {
                $_SESSION['error'] = 'ID đặt vé không hợp lệ.';
                header('Location: ' . BASE_URL . '/my-tickets');
                exit;
            }
            
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
            $bookingInfo['trangThaiThucTe'] = $this->determineBookingActualStatus($bookingDetails);
            
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
     * Cancel a ticket booking
     */
    public function cancel($encryptedBookingId) {
        header('Content-Type: application/json');
        
        try {
            $bookingId = IDEncryptionHelper::decryptId($encryptedBookingId);
            
            if (!$bookingId || !IDEncryptionHelper::isValidId($bookingId)) {
                echo json_encode(['success' => false, 'message' => 'ID đặt vé không hợp lệ']);
                return;
            }
            
            // Check if user is logged in
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để hủy vé']);
                return;
            }
            
            $userId = $_SESSION['user_id'];
            
            // Get booking details
            $bookingDetails = $this->getBookingDetails($bookingId, $userId);
            
            if (!$bookingDetails) {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin vé hoặc bạn không có quyền hủy vé này']);
                return;
            }
            
            $bookingInfo = $bookingDetails[0];
            
            // Check if booking is already cancelled
            if ($bookingInfo['trangThai'] === 'DaHuy') {
                echo json_encode(['success' => false, 'message' => 'Vé này đã được hủy trước đó']);
                return;
            }
            
            // Check if booking is paid
            if ($bookingInfo['trangThai'] !== 'DaThanhToan') {
                echo json_encode(['success' => false, 'message' => 'Chỉ có thể hủy vé đã thanh toán']);
                return;
            }
            
            // Check cancellation time (must be >36 hours before departure)
            $earliestDeparture = null;
            foreach ($bookingDetails as $detail) {
                $departureTime = strtotime($detail['thoiGianKhoiHanh']);
                if ($earliestDeparture === null || $departureTime < $earliestDeparture) {
                    $earliestDeparture = $departureTime;
                }
            }
            
            $hoursUntilDeparture = ($earliestDeparture - time()) / 3600;
            
            if ($hoursUntilDeparture < 0) {
                echo json_encode(['success' => false, 'message' => 'Không thể hủy vé đã qua ngày khởi hành']);
                return;
            }
            
            if ($hoursUntilDeparture < 36) {
                echo json_encode(['success' => false, 'message' => 'Chỉ có thể hủy vé trước 36 giờ so với giờ khởi hành']);
                return;
            }
            
            // Start transaction
            query("START TRANSACTION");
            
            // Update booking status to cancelled
            $sql = "UPDATE datve SET trangThai = 'DaHuy', ngayCapNhat = NOW() WHERE maDatVe = ?";
            query($sql, [$bookingId]);
            
            // Update ticket details status to cancelled
            $sql = "UPDATE chitiet_datve SET trangThai = 'DaHuy' WHERE maDatVe = ?";
            query($sql, [$bookingId]);
            
            // Release seats for all trips in this booking
            $this->releaseSeatsForBooking($bookingDetails);
            
            // Process refund as loyalty points (20% of ticket price)
            $refundAmount = $bookingInfo['tongTienSauGiam'] * 0.2;
            $refundPoints = floor($refundAmount / 100); // 1 point = 100đ
            
            if ($refundPoints > 0) {
                $sql = "INSERT INTO diem_tichluy (maNguoiDung, nguon, diem, maDatVe, ghiChu, ngayTao)
                        VALUES (?, 'HuyVe', ?, ?, 'Hoàn 20% từ hủy vé', NOW())";
                query($sql, [$userId, $refundPoints, $bookingId]);
                
                // Update user total points
                $this->updateUserTotalPoints($userId);
            }
            
            $emailService = new EmailService();
            
            // Get user email
            $userSql = "SELECT email FROM nguoidung WHERE maNguoiDung = ?";
            $userInfo = fetch($userSql, [$userId]);
            
            // Prepare booking data for email
            $emailBookingData = [
                'maDatVe' => $bookingInfo['maDatVe'],
                'ngayDat' => $bookingInfo['ngayDat'],
                'tongTienSauGiam' => $bookingInfo['tongTienSauGiam'],
                'phuongThucThanhToan' => $bookingInfo['phuongThucThanhToan'],
                'emailNguoiDung' => $userInfo['email'] ?? ''
            ];
            
            // Send cancellation email
            $emailResult = $emailService->sendCancellationEmail($emailBookingData, $bookingDetails, $refundPoints);
            
            if (!$emailResult['success']) {
                error_log("[v0] Failed to send cancellation email: " . $emailResult['message']);
            }
            
            query("COMMIT");
            
            $message = "Hủy vé thành công!";
            if ($refundPoints > 0) {
                $message .= " Bạn nhận được " . number_format($refundPoints) . " điểm tích lũy (tương đương " . number_format($refundAmount) . "đ).";
            }
            
            echo json_encode([
                'success' => true, 
                'message' => $message,
                'refund_points' => $refundPoints
            ]);
            
        } catch (Exception $e) {
            query("ROLLBACK");
            error_log("MyTicketsController cancel error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi hủy vé: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Save trip rating
     */
    public function saveRating() {
        header('Content-Type: application/json');
        
        try {
            // Check if user is logged in
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để đánh giá']);
                return;
            }
            
            $userId = $_SESSION['user_id'];
            
            // Get POST data
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$data) {
                echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
                return;
            }
            
            $tripId = $data['tripId'] ?? null;
            $bookingId = $data['bookingId'] ?? null;
            
            if (!$tripId || !$bookingId) {
                echo json_encode(['success' => false, 'message' => 'Thiếu thông tin chuyến xe hoặc đặt vé']);
                return;
            }
            
            // Verify user owns this booking
            $bookingDetails = $this->getBookingDetails($bookingId, $userId);
            if (!$bookingDetails) {
                echo json_encode(['success' => false, 'message' => 'Bạn không có quyền đánh giá chuyến xe này']);
                return;
            }
            
            // Verify trip belongs to this booking and get the ticket detail
            $tripBelongsToBooking = false;
            $ticketDetail = null;
            $driverInfo = null;
            
            foreach ($bookingDetails as $detail) {
                if ($detail['maChuyenXe'] == $tripId) {
                    $tripBelongsToBooking = true;
                    $ticketDetail = $detail;
                    $driverInfo = [
                        'tenTaiXe' => $detail['tenTaiXe'] ?? null,
                        'maTaiXe' => $detail['maTaiXe'] ?? null
                    ];
                    break;
                }
            }
            
            if (!$tripBelongsToBooking) {
                echo json_encode(['success' => false, 'message' => 'Chuyến xe không thuộc đặt vé này']);
                return;
            }
            
            $ratingData = [
                'diemDichVu' => isset($data['serviceRating']) ? (int)$data['serviceRating'] : null,
                'diemTaiXe' => isset($data['driverRating']) ? (int)$data['driverRating'] : null,
                'diemPhuongTien' => isset($data['vehicleRating']) ? (int)$data['vehicleRating'] : null,
                'ghichu' => isset($data['comment']) ? trim($data['comment']) : null,
                'maChiTiet' => $ticketDetail['maChiTiet'] ?? null,
                'maTaiXe' => $driverInfo['maTaiXe'] ?? null
            ];
            
            // Validate ratings
            if ($ratingData['diemDichVu'] && ($ratingData['diemDichVu'] < 1 || $ratingData['diemDichVu'] > 5)) {
                echo json_encode(['success' => false, 'message' => 'Điểm dịch vụ phải từ 1 đến 5']);
                return;
            }
            
            if ($ratingData['diemTaiXe'] && ($ratingData['diemTaiXe'] < 1 || $ratingData['diemTaiXe'] > 5)) {
                echo json_encode(['success' => false, 'message' => 'Điểm tài xế phải từ 1 đến 5']);
                return;
            }
            
            if ($ratingData['diemPhuongTien'] && ($ratingData['diemPhuongTien'] < 1 || $ratingData['diemPhuongTien'] > 5)) {
                echo json_encode(['success' => false, 'message' => 'Điểm phương tiện phải từ 1 đến 5']);
                return;
            }
            
            // Save rating
            require_once __DIR__ . '/../models/TripRating.php';
            $ratingId = TripRating::saveRating($tripId, $userId, $ratingData);
            
            echo json_encode([
                'success' => true,
                'message' => 'Cảm ơn bạn đã đánh giá chuyến đi!',
                'ratingId' => $ratingId
            ]);
            
        } catch (Exception $e) {
            error_log("MyTicketsController saveRating error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi lưu đánh giá: ' . $e->getMessage()]);
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
                           c.ngayKhoiHanh, c.thoiGianKhoiHanh, c.trangThai as trangThaiChuyenXe,
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
                           c.ngayKhoiHanh, c.thoiGianKhoiHanh, c.maPhuongTien, c.trangThai as trangThaiChuyenXe, c.maTaiXe,
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
                $ticketStatus = $this->determineTicketStatus($ticket);
                
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
                        'trangThaiThucTe' => $ticketStatus,
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
     * Determine ticket status based on trip status and booking status
     */
    private function determineTicketStatus($ticket) {
        // If booking was cancelled by customer, keep it as "Đã hủy"
        if ($ticket['trangThai'] === 'DaHuy') {
            return 'DaHuy';
        }
        
        // If trip was cancelled, ticket becomes "Hết hiệu lực"
        if (isset($ticket['trangThaiChuyenXe']) && $ticket['trangThaiChuyenXe'] === 'Bị hủy') {
            return 'HetHieuLuc';
        }
        
        // If trip is completed, ticket becomes "Đã hoàn thành"
        if (isset($ticket['trangThaiChuyenXe']) && $ticket['trangThaiChuyenXe'] === 'Hoàn thành') {
            return 'DaHoanThanh';
        }
        
        // Otherwise keep original status
        return $ticket['trangThai'];
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
                        'soDienThoaiTaiXe' => $ticket['soDienThoaiTaiXe'],
                        'trangThaiChuyenXe' => $ticket['trangThaiChuyenXe']
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
    
    /**
     * Determine actual booking status based on all trip statuses
     */
    private function determineBookingActualStatus($bookingDetails) {
        // If booking was cancelled by customer, keep it as "Đã hủy"
        if (!empty($bookingDetails[0]['trangThai']) && $bookingDetails[0]['trangThai'] === 'DaHuy') {
            return 'DaHuy';
        }
        
        // Check if any trip was cancelled - if so, booking becomes "Hết hiệu lực"
        foreach ($bookingDetails as $detail) {
            if (isset($detail['trangThaiChuyenXe']) && $detail['trangThaiChuyenXe'] === 'Bị hủy') {
                return 'HetHieuLuc';
            }
        }
        
        // Check if all trips are completed
        $allCompleted = true;
        foreach ($bookingDetails as $detail) {
            if (!isset($detail['trangThaiChuyenXe']) || $detail['trangThaiChuyenXe'] !== 'Hoàn thành') {
                $allCompleted = false;
                break;
            }
        }
        
        if ($allCompleted) {
            return 'DaHoanThanh';
        }
        
        // Otherwise keep original status
        return $bookingDetails[0]['trangThai'] ?? 'DangGiu';
    }
    
    /**
     * Release seats for all trips in a booking
     */
    private function releaseSeatsForBooking($bookingDetails) {
        try {
            // Group by trip
            $tripSeats = [];
            foreach ($bookingDetails as $detail) {
                $tripId = $detail['maChuyenXe'];
                $seatId = $detail['maGhe'];
                
                if (!isset($tripSeats[$tripId])) {
                    $tripSeats[$tripId] = [];
                }
                $tripSeats[$tripId][] = $seatId;
            }
            
            // Release seats for each trip
            foreach ($tripSeats as $tripId => $seatIds) {
                foreach ($seatIds as $seatId) {
                    $sql = "UPDATE chuyenxe_ghe 
                            SET trangThai = 'Trống', ngayCapNhat = NOW() 
                            WHERE maChuyenXe = ? AND maGhe = ?";
                    query($sql, [$tripId, $seatId]);
                }
                
                $seatCount = count($seatIds);
                $sql = "UPDATE chuyenxe 
                        SET soChoDaDat = GREATEST(0, soChoDaDat - ?), ngayCapNhat = NOW() 
                        WHERE maChuyenXe = ?";
                query($sql, [$seatCount, $tripId]);
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("releaseSeatsForBooking error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update user total loyalty points
     */
    private function updateUserTotalPoints($userId) {
        try {
            $sql = "SELECT COALESCE(SUM(diem), 0) as total_points 
                    FROM diem_tichluy 
                    WHERE maNguoiDung = ?";
            
            $result = fetch($sql, [$userId]);
            $totalPoints = max(0, (int)$result['total_points']);
            
            $sql = "UPDATE nguoidung SET diemTichLuy = ? WHERE maNguoiDung = ?";
            query($sql, [$totalPoints, $userId]);
            
        } catch (Exception $e) {
            error_log("updateUserTotalPoints error: " . $e->getMessage());
        }
    }
}
