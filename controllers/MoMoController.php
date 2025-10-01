<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class MoMoController {
    
    private $partnerCode;
    private $accessKey;
    private $secretKey;
    private $endpoint;
    
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->partnerCode = 'MOMOBKUN20180529';
        $this->accessKey = 'klm05TvNBzhg7h7j';
        $this->secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';
        $this->endpoint = 'https://test-payment.momo.vn/v2/gateway/api/create';
    }

    /**
     * Tạo yêu cầu thanh toán MoMo
     */
    public function createPayment() {
        try {
            // Kiểm tra thông tin đặt vé
            if (!isset($_SESSION['final_booking_data']) || !isset($_SESSION['held_seats'])) {
                $_SESSION['error'] = 'Phiên đặt vé đã hết hạn.';
                header('Location: ' . BASE_URL . '/search');
                exit;
            }

            $bookingData = $_SESSION['final_booking_data'];
            $pricing = $this->calculateFinalPricing($bookingData);

            $orderId = 'XeGoo_' . date('YmdHis') . '_' . rand(1000, 9999);
            $requestId = $orderId;
            $amount = (int)$pricing['final_price'];
            $orderInfo = $this->generateOrderInfo($bookingData);
            $redirectUrl = BASE_URL . '/payment/momo/return';
            $ipnUrl = BASE_URL . '/payment/momo/notify';
            $extraData = base64_encode(json_encode([
                'booking_data' => $bookingData,
                'pricing' => $pricing
            ]));

            $rawHash = "accessKey=" . $this->accessKey . 
                      "&amount=" . $amount . 
                      "&extraData=" . $extraData . 
                      "&ipnUrl=" . $ipnUrl . 
                      "&orderId=" . $orderId . 
                      "&orderInfo=" . $orderInfo . 
                      "&partnerCode=" . $this->partnerCode . 
                      "&redirectUrl=" . $redirectUrl . 
                      "&requestId=" . $requestId . 
                      "&requestType=captureWallet";

            $signature = hash_hmac("sha256", $rawHash, $this->secretKey);

            // Dữ liệu gửi đến MoMo
            $data = [
                'partnerCode' => $this->partnerCode,
                'partnerName' => 'XeGoo',
                'storeId' => 'XeGooStore',
                'requestId' => $requestId,
                'amount' => $amount,
                'orderId' => $orderId,
                'orderInfo' => $orderInfo,
                'redirectUrl' => $redirectUrl,
                'ipnUrl' => $ipnUrl,
                'lang' => 'vi',
                'extraData' => $extraData,
                'requestType' => 'captureWallet',
                'signature' => $signature
            ];

            // Lưu thông tin order vào session
            $_SESSION['momo_order'] = [
                'orderId' => $orderId,
                'amount' => $amount,
                'orderInfo' => $orderInfo,
                'created_at' => time()
            ];

            // Gửi request đến MoMo
            $result = $this->sendRequest($data);

            if ($result && isset($result['payUrl'])) {
                // Chuyển hướng đến trang thanh toán MoMo
                header('Location: ' . $result['payUrl']);
                exit;
            } else {
                $_SESSION['error'] = 'Không thể tạo yêu cầu thanh toán MoMo. Vui lòng thử lại.';
                header('Location: ' . BASE_URL . '/payment');
                exit;
            }

        } catch (Exception $e) {
            error_log("MoMoController createPayment error: " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi tạo yêu cầu thanh toán: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/payment');
            exit;
        }
    }

    /**
     * Xử lý kết quả trả về từ MoMo
     */
    public function handleReturn() {
        try {
            $partnerCode = $_GET['partnerCode'] ?? '';
            $orderId = $_GET['orderId'] ?? '';
            $requestId = $_GET['requestId'] ?? '';
            $amount = $_GET['amount'] ?? '';
            $orderInfo = $_GET['orderInfo'] ?? '';
            $orderType = $_GET['orderType'] ?? '';
            $transId = $_GET['transId'] ?? '';
            $resultCode = $_GET['resultCode'] ?? '';
            $message = $_GET['message'] ?? '';
            $payType = $_GET['payType'] ?? '';
            $responseTime = $_GET['responseTime'] ?? '';
            $extraData = $_GET['extraData'] ?? '';
            $signature = $_GET['signature'] ?? '';

            $rawHash = "accessKey=" . $this->accessKey . 
                      "&amount=" . $amount . 
                      "&extraData=" . $extraData . 
                      "&message=" . $message . 
                      "&orderId=" . $orderId . 
                      "&orderInfo=" . $orderInfo . 
                      "&orderType=" . $orderType . 
                      "&partnerCode=" . $partnerCode . 
                      "&payType=" . $payType . 
                      "&requestId=" . $requestId . 
                      "&responseTime=" . $responseTime . 
                      "&resultCode=" . $resultCode . 
                      "&transId=" . $transId;

            $expectedSignature = hash_hmac("sha256", $rawHash, $this->secretKey);

            if ($signature !== $expectedSignature) {
                $_SESSION['error'] = 'Chữ ký không hợp lệ.';
                header('Location: ' . BASE_URL . '/payment');
                exit;
            }

            if ($resultCode == '0') {
                // Thanh toán thành công
                $this->processSuccessfulPayment($orderId, $transId, $extraData);
            } else {
                // Thanh toán thất bại
                $_SESSION['error'] = 'Thanh toán không thành công: ' . $message;
                header('Location: ' . BASE_URL . '/payment');
                exit;
            }

        } catch (Exception $e) {
            error_log("MoMoController handleReturn error: " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi xử lý kết quả thanh toán.';
            header('Location: ' . BASE_URL . '/payment');
            exit;
        }
    }

    /**
     * Xử lý thông báo IPN từ MoMo
     */
    public function handleNotify() {
        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            if (!$data) {
                http_response_code(400);
                echo json_encode(['message' => 'Invalid data']);
                return;
            }

            // Xác thực chữ ký IPN
            $signature = $data['signature'] ?? '';
            unset($data['signature']);
            
            ksort($data);
            $rawHash = '';
            foreach ($data as $key => $value) {
                $rawHash .= $key . '=' . $value . '&';
            }
            $rawHash = rtrim($rawHash, '&');
            
            $expectedSignature = hash_hmac('sha256', $rawHash, $this->secretKey);

            if ($signature !== $expectedSignature) {
                http_response_code(400);
                echo json_encode(['message' => 'Invalid signature']);
                return;
            }

            // Xử lý thông báo
            if ($data['resultCode'] == '0') {
                // Thanh toán thành công, cập nhật database
                $this->processSuccessfulPayment($data['orderId'], $data['transId'], $data['extraData']);
            }

            http_response_code(200);
            echo json_encode(['message' => 'Success']);

        } catch (Exception $e) {
            error_log("MoMoController handleNotify error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['message' => 'Internal server error']);
        }
    }

    /**
     * Xử lý thanh toán thành công - Updated to use proper booking ID
     */
    private function processSuccessfulPayment($orderId, $transactionNo, $extraData = '') {
        try {
            $bookingData = $_SESSION['final_booking_data'];
            $pricing = $this->calculateFinalPricing($bookingData);

            if (!$bookingData) {
                throw new Exception('Không tìm thấy thông tin đặt vé');
            }

            query("START TRANSACTION");

            $userId = $_SESSION['user_id'] ?? null;
            $tripType = isset($bookingData['return']) ? 'KhuHoi' : 'MotChieu';

            // Create main booking record
            $sql = "INSERT INTO datve (
                        maNguoiDung, soLuongVe, tongTien, giamGia, tongTienSauGiam, 
                        phuongThucThanhToan, loaiDatVe, trangThai, ngayDat
                    ) VALUES (?, ?, ?, ?, ?, 'MoMo', ?, 'DaThanhToan', NOW())";
        
            $totalTickets = 0;
            if ($bookingData['outbound']) {
                $totalTickets += count($bookingData['outbound']['passengers']);
            }
            if (isset($bookingData['return'])) {
                $totalTickets += count($bookingData['return']['passengers']);
            }
        
            query($sql, [
                $userId,
                $totalTickets,
                $pricing['original_price'],
                $pricing['discount'], // Added discount amount
                $pricing['final_price'],
                $tripType
            ]);

            $bookingId = lastInsertId();
            error_log("[v0] Created successful MoMo booking with ID: $bookingId");

            // Create payment record
            $this->createPaymentRecord($bookingId, $pricing, $orderId, $transactionNo);

            // Create booking details
            $this->createBookingDetails($bookingId, $bookingData);

            // Xác nhận ghế (chuyển từ "Đang giữ" thành "Đã đặt")
            $this->confirmSeats();

            // Xử lý điểm tích lũy
            $this->processLoyaltyPoints($bookingId, $pricing);

            query("COMMIT");

            // Xóa session
            $this->clearBookingSession();

            $_SESSION['success'] = 'Thanh toán thành công! Mã đặt vé: ' . $bookingId;
            header('Location: ' . BASE_URL . '/booking/success/' . $bookingId);
            exit;

        } catch (Exception $e) {
            query("ROLLBACK");
            error_log("processSuccessfulPayment error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Gửi request đến MoMo API
     */
    private function sendRequest($data) {
        $ch = curl_init($this->endpoint);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($data))
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception("MoMo API returned HTTP code: $httpCode");
        }

        return json_decode($result, true);
    }

    /**
     * Tạo thông tin đơn hàng
     */
    private function generateOrderInfo($bookingData) {
        $info = 'Đặt vé XeGoo';
        
        if ($bookingData['outbound']) {
            $trip = $bookingData['outbound']['trip_details'];
            $departureDate = date('d/m/Y', strtotime($trip['ngayDi']));
            
            // Lấy tên tỉnh thành từ điểm đi và điểm đến
            $fromCity = $this->getCityFromLocation($trip['diemDi']);
            $toCity = $this->getCityFromLocation($trip['diemDen']);
            
            $info .= ', tuyến ' . $fromCity . ' - ' . $toCity . ', ngày ' . $departureDate;
        }
        
        if (isset($bookingData['return'])) {
            $info .= ' ( Khứ hồi )';
        } else {
            $info .= ' ( Một chiều )';
        }
        
        return $info;
    }

    /**
     * Lấy tên tỉnh thành từ địa điểm
     */
    private function getCityFromLocation($location) {
        // Mapping các địa điểm thành tên tỉnh thành ngắn gọn
        $cityMapping = [
            'TP. Hồ Chí Minh' => 'SG',
            'Hồ Chí Minh' => 'SG', 
            'Sài Gòn' => 'SG',
            'Đà Lạt' => 'DL',
            'Dalat' => 'DL',
            'Hà Nội' => 'HN',
            'Hanoi' => 'HN',
            'Đà Nẵng' => 'DN',
            'Da Nang' => 'DN',
            'Nha Trang' => 'NT',
            'Vũng Tàu' => 'VT',
            'Cần Thơ' => 'CT',
            'Huế' => 'HUE',
            'Quy Nhon' => 'QN'
        ];
        
        // Tìm kiếm trong mapping
        foreach ($cityMapping as $fullName => $shortName) {
            if (stripos($location, $fullName) !== false) {
                return $shortName;
            }
        }
        
        // Nếu không tìm thấy, trả về 3 ký tự đầu
        return strtoupper(substr($location, 0, 3));
    }

    /**
     * Tính giá cuối cùng
     */
    private function calculateFinalPricing($bookingData) {
        $originalPrice = $bookingData['total_price'];
        $discount = 0;

        // Áp dụng khuyến mãi
        if (isset($_SESSION['applied_promotion'])) {
            $promotion = $_SESSION['applied_promotion'];
            if ($promotion['loai'] === 'PhanTram') {
                $discount += $originalPrice * ($promotion['giaTri'] / 100);
            } else {
                $discount += $promotion['giaTri'];
            }
        }

        // Áp dụng điểm tích lũy
        if (isset($_SESSION['used_points'])) {
            $discount += $_SESSION['used_points'] * 100;
        }

        $finalPrice = max(0, $originalPrice - $discount);
        $earnedPoints = floor($originalPrice * 0.001);

        return [
            'original_price' => $originalPrice,
            'discount' => $discount,
            'final_price' => $finalPrice,
            'earned_points' => $earnedPoints
        ];
    }

    /**
     * Tạo bản ghi thanh toán
     */
    private function createPaymentRecord($bookingId, $pricing, $orderId, $transactionNo) {
        $sql = "INSERT INTO datve_thanhtoan (
                    maDatVe, tongTienGoc, tongTienSauGiam, maKhuyenMai, 
                    diemSuDung, diemNhanDuoc, phuongThucThanhToan, 
                    loaiDatVe, trangThai, ngayDat, ghiChu
                ) VALUES (?, ?, ?, ?, ?, ?, 'MoMo', ?, 'DaThanhToan', NOW(), ?)";
        
        $promotionId = isset($_SESSION['applied_promotion']) ? $_SESSION['applied_promotion']['maKhuyenMai'] : null;
        $usedPoints = $_SESSION['used_points'] ?? 0;
        $tripType = isset($_SESSION['final_booking_data']['return']) ? 'KhuHoi' : 'MotChieu';
        $note = "MoMo OrderID: $orderId, TransactionNo: $transactionNo";
        
        query($sql, [
            $bookingId,
            $pricing['original_price'],
            $pricing['final_price'],
            $promotionId,
            $usedPoints,
            $pricing['earned_points'],
            $tripType,
            $note
        ]);
    }

    /**
     * Tạo chi tiết đặt vé - Updated to use main booking ID
     */
    private function createBookingDetails($bookingId, $bookingData) {
        if ($bookingData['outbound']) {
            $this->createSingleBookingDetail($bookingId, $bookingData['outbound']);
        }
        
        if (isset($bookingData['return'])) {
            $this->createSingleBookingDetail($bookingId, $bookingData['return']);
        }
    }

    /**
     * Tạo chi tiết đặt vé cho một chuyến - Updated to use main booking ID
     */
    private function createSingleBookingDetail($bookingId, $tripData) {
        foreach ($tripData['passengers'] as $index => $passenger) {
            $seatNumber = $tripData['selected_seats'][$index];
            
            // Get seat ID from seat number
            $seatSql = "SELECT g.maGhe FROM ghe g 
                       JOIN chuyenxe cx ON cx.maPhuongTien = g.maPhuongTien 
                       WHERE cx.maChuyenXe = ? AND g.soGhe = ?";
            $seat = fetch($seatSql, [$tripData['trip_id'], $seatNumber]);
            
            if ($seat) {
                $sql = "INSERT INTO chitiet_datve (maDatVe, maChuyenXe, maGhe, hoTenHanhKhach, 
                                                  emailHanhKhach, soDienThoaiHanhKhach, giaVe, 
                                                  maDiemDon, maDiemTra, trangThai, ngayTao)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'DaThanhToan', NOW())";
                
                query($sql, [
                    $bookingId, // Use main booking ID
                    $tripData['trip_id'],
                    $seat['maGhe'],
                    $passenger['ho_ten'],
                    $passenger['email'] ?? '',
                    $passenger['so_dien_thoai'] ?? '',
                    $tripData['total_price'] / count($tripData['passengers']),
                    $tripData['pickup_point'],
                    $tripData['dropoff_point']
                ]);
            }
        }
    }

    /**
     * Xác nhận ghế
     */
    private function confirmSeats() {
        if (!isset($_SESSION['held_seats'])) {
            return;
        }

        $heldSeats = $_SESSION['held_seats'];
        
        // Cập nhật trạng thái ghế chuyến đi
        $this->updateSeatsStatus($heldSeats['trip_id'], $heldSeats['selected_seats'], 'Đã đặt');
        $this->updateTripSeatCount($heldSeats['trip_id'], count($heldSeats['selected_seats']));
        
        // Cập nhật trạng thái ghế chuyến về
        if (!empty($heldSeats['return_trip_id']) && !empty($heldSeats['return_selected_seats'])) {
            $this->updateSeatsStatus($heldSeats['return_trip_id'], $heldSeats['return_selected_seats'], 'Đã đặt');
            $this->updateTripSeatCount($heldSeats['return_trip_id'], count($heldSeats['return_selected_seats']));
        }
    }

    /**
     * Add new method to update trip seat count
     * Cập nhật số ghế đã đặt trong bảng chuyenxe
     */
    private function updateTripSeatCount($tripId, $seatCountChange) {
        try {
            error_log("[v0] MoMo - Updating seat count for trip $tripId by $seatCountChange");
            
            // Cập nhật số ghế đã đặt
            $sql = "UPDATE chuyenxe SET soChoDaDat = soChoDaDat + ? WHERE maChuyenXe = ?";
            query($sql, [$seatCountChange, $tripId]);
            
            // Log để kiểm tra
            $checkSql = "SELECT soChoTong, soChoDaDat, soChoTrong FROM chuyenxe WHERE maChuyenXe = ?";
            $result = fetch($checkSql, [$tripId]);
            if ($result) {
                error_log("[v0] MoMo - Trip $tripId seat count updated - Total: {$result['soChoTong']}, Booked: {$result['soChoDaDat']}, Available: {$result['soChoTrong']}");
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("[v0] MoMo updateTripSeatCount error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cập nhật trạng thái ghế
     */
    private function updateSeatsStatus($tripId, $seatNumbers, $status) {
        try {
            // Lấy thông tin phương tiện
            $sql = "SELECT maPhuongTien FROM chuyenxe WHERE maChuyenXe = ?";
            $trip = fetch($sql, [$tripId]);
            
            if (!$trip) return;

            $vehicleId = $trip['maPhuongTien'];
            
            // Lấy ID ghế
            $placeholders = str_repeat('?,', count($seatNumbers) - 1) . '?';
            $sql = "SELECT maGhe FROM ghe WHERE maPhuongTien = ? AND soGhe IN ($placeholders)";
            $params = array_merge([$vehicleId], $seatNumbers);
            $seats = fetchAll($sql, $params);
            
            // Cập nhật trạng thái
            foreach ($seats as $seat) {
                $sql = "UPDATE chuyenxe_ghe SET trangThai = ?, ngayCapNhat = NOW() 
                        WHERE maChuyenXe = ? AND maGhe = ?";
                query($sql, [$status, $tripId, $seat['maGhe']]);
            }

        } catch (Exception $e) {
            error_log("updateSeatsStatus error: " . $e->getMessage());
        }
    }

    /**
     * Xử lý điểm tích lũy
     */
    private function processLoyaltyPoints($bookingId, $pricing) {
        if (!isset($_SESSION['user_id'])) {
            return;
        }

        $userId = $_SESSION['user_id'];

        try {
            // Trừ điểm đã sử dụng
            if (isset($_SESSION['used_points']) && $_SESSION['used_points'] > 0) {
                $sql = "INSERT INTO diem_tichluy (maNguoiDung, nguon, diem, maDatVe, ghiChu, ngayTao)
                        VALUES (?, 'HuyVe', ?, ?, 'Sử dụng điểm thanh toán', NOW())";
                query($sql, [$userId, -$_SESSION['used_points'], $bookingId]);
            }

            // Cộng điểm tích lũy mới
            if ($pricing['earned_points'] > 0) {
                $sql = "INSERT INTO diem_tichluy (maNguoiDung, nguon, diem, maDatVe, ghiChu, ngayTao)
                        VALUES (?, 'MuaVe', ?, ?, 'Tích lũy từ mua vé', NOW())";
                query($sql, [$userId, $pricing['earned_points'], $bookingId]);
            }

        } catch (Exception $e) {
            error_log("processLoyaltyPoints error: " . $e->getMessage());
        }
    }

    /**
     * Xóa session đặt vé
     */
    private function clearBookingSession() {
        unset($_SESSION['final_booking_data']);
        unset($_SESSION['held_seats']);
        unset($_SESSION['applied_promotion']);
        unset($_SESSION['used_points']);
        unset($_SESSION['momo_order']);
        unset($_SESSION['booking_outbound_trip']);
        unset($_SESSION['booking_errors']);
        unset($_SESSION['booking_data']);
    }
}
?>
