<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../lib/EmailService.php';
require_once __DIR__ . '/../helpers/IDEncryptionHelper.php';

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
            error_log("[v0] MoMoController::createPayment() started");
            
            // Kiểm tra thông tin đặt vé
            if (!isset($_SESSION['final_booking_data']) || !isset($_SESSION['held_seats'])) {
                error_log("[v0] No final_booking_data or held_seats in session");
                $_SESSION['error'] = 'Phiên đặt vé đã hết hạn.';
                header('Location: ' . BASE_URL . '/search');
                exit;
            }

            $bookingData = $_SESSION['final_booking_data'];
            $pricing = $this->calculatePricing($bookingData);

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

            error_log("[v0] Sending request to MoMo API");
            error_log("[v0] Order ID: " . $orderId);
            error_log("[v0] Amount: " . $amount);
            
            // Gửi request đến MoMo
            $result = $this->sendRequest($data);

            if ($result && isset($result['payUrl'])) {
                error_log("[v0] MoMo payment URL created successfully");
                // Chuyển hướng đến trang thanh toán MoMo
                header('Location: ' . $result['payUrl']);
                exit;
            } else {
                error_log("[v0] Failed to create MoMo payment URL");
                $_SESSION['error'] = 'Không thể tạo yêu cầu thanh toán MoMo. Vui lòng thử lại.';
                header('Location: ' . BASE_URL . '/payment');
                exit;
            }

        } catch (Exception $e) {
            error_log("[v0] MoMoController createPayment error: " . $e->getMessage());
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
            error_log("[v0] MoMoController::handleReturn() started");
            error_log("[v0] GET parameters: " . json_encode($_GET));
            
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

            error_log("[v0] Calculated hash: " . $expectedSignature);
            error_log("[v0] MoMo hash: " . $signature);
            error_log("[v0] Result code: " . $resultCode);
            error_log("[v0] Transaction ID: " . $transId);

            if ($signature !== $expectedSignature) {
                error_log("[v0] Hash verification failed");
                $_SESSION['error'] = 'Chữ ký không hợp lệ.';
                header('Location: ' . BASE_URL . '/payment');
                exit;
            }

            error_log("[v0] Hash verification successful");

            if ($resultCode == '0') {
                // Thanh toán thành công
                error_log("[v0] Payment successful - Processing booking");
                $bookingId = $this->processSuccessfulPayment($orderId, $transId, $extraData);
                
                if ($bookingId) {
                    // Xác nhận ghế (chuyển từ "Đang giữ" thành "Đã đặt")
                    $this->confirmSeats();
                    
                    // Xử lý điểm tích lũy
                    $this->processLoyaltyPoints($bookingId);
                    if (isset($_SESSION['applied_promotion']) && isset($_SESSION['user_id'])) {
                        $promotion = $_SESSION['applied_promotion'];
                        $pricing = $this->calculatePricing($_SESSION['final_booking_data']);
                        require_once __DIR__ . '/PaymentController.php';
                        $paymentController = new PaymentController();
                        $paymentController->recordPromotionUsage($_SESSION['user_id'], $promotion['maKhuyenMai'], $bookingId, $pricing['discount']);
                    }
                    
                    // Clear session
                    $this->clearBookingSession();
                    
                    $encryptedBookingId = IDEncryptionHelper::encryptId($bookingId);
                    
                    $_SESSION['success'] = 'Thanh toán thành công! Mã đặt vé: ' . $bookingId;
                    header('Location: ' . BASE_URL . '/booking/success/' . $encryptedBookingId);
                    exit;
                } else {
                    throw new Exception('Không thể tạo đơn đặt vé');
                }
            } else {
                // Thanh toán thất bại
                error_log("[v0] Payment failed - Result code: " . $resultCode);
                
                // Tạo đơn đặt vé với trạng thái thất bại
                $this->createFailedBooking($orderId, $amount, $resultCode);
                
                // Giải phóng ghế
                $this->releaseHeldSeats();
                
                // Clear session
                $this->clearBookingSession();
                
                $errorMessage = $this->getMoMoErrorMessage($resultCode);
                $_SESSION['error'] = 'Thanh toán không thành công: ' . $errorMessage;
                header('Location: ' . BASE_URL . '/search');
                exit;
            }

        } catch (Exception $e) {
            error_log("[v0] MoMoController handleReturn error: " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi xử lý kết quả thanh toán.';
            header('Location: ' . BASE_URL . '/payment');
            exit;
        }
    }

    /**
     * Xử lý thông báo IPN từ MoMo
     */
    public function handleNotify() {
        header('Content-Type: application/json');
        
        try {
            error_log("[v0] MoMoController::handleNotify() started");
            
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            if (!$data) {
                error_log("[v0] Invalid JSON data received");
                http_response_code(400);
                echo json_encode(['message' => 'Invalid data']);
                return;
            }

            error_log("[v0] IPN data received: " . json_encode($data));

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

            error_log("[v0] IPN signature verification - Expected: " . $expectedSignature . ", Received: " . $signature);

            if ($signature !== $expectedSignature) {
                error_log("[v0] IPN signature verification failed");
                http_response_code(400);
                echo json_encode(['message' => 'Invalid signature']);
                return;
            }

            error_log("[v0] IPN signature verification successful");

            // Xử lý thông báo
            if ($data['resultCode'] == '0') {
                error_log("[v0] Processing successful IPN notification");
                // Thanh toán thành công, cập nhật database
                $this->processSuccessfulPayment($data['orderId'], $data['transId'], $data['extraData']);
            } else {
                error_log("[v0] IPN notification shows failed payment - Result code: " . $data['resultCode']);
            }

            http_response_code(200);
            echo json_encode(['message' => 'Success']);

        } catch (Exception $e) {
            error_log("[v0] MoMoController handleNotify error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['message' => 'Internal server error']);
        }
    }

    /**
     * Xử lý thanh toán thành công - Updated to match VNPay structure
     */
    private function processSuccessfulPayment($orderId, $transactionNo, $extraData = '') {
        error_log("[v0] ========== processSuccessfulPayment START ==========");
        error_log("[v0] Parameters: orderId=$orderId, transactionNo=$transactionNo");
        
        try {
            $bookingData = $_SESSION['final_booking_data'];
            $pricing = $this->calculatePricing($bookingData);

            if (!$bookingData) {
                throw new Exception('Không tìm thấy thông tin đặt vé');
            }

            error_log("[v0] Starting database transaction");
            query("START TRANSACTION");

            $userId = $_SESSION['user_id'] ?? null;
            $tripType = isset($bookingData['return']) ? 'KhuHoi' : 'MotChieu';

            $sql = "INSERT INTO datve (
                        maNguoiDung, soLuongVe, tongTien, giamGia, tongTienSauGiam, 
                        phuongThucThanhToan, loaiDatVe, trangThai, ngayDat, ghiChu
                    ) VALUES (?, ?, ?, ?, ?, 'MoMo', ?, 'DaThanhToan', NOW(), ?)";
        
            $totalTickets = 0;
            if ($bookingData['outbound']) {
                $totalTickets += count($bookingData['outbound']['passengers']);
            }
            if (isset($bookingData['return'])) {
                $totalTickets += count($bookingData['return']['passengers']);
            }
            
            $note = "MoMo OrderID: $orderId, TransactionNo: $transactionNo";
            
            error_log("[v0] About to insert booking record");
            query($sql, [
                $userId,
                $totalTickets,
                $pricing['original_price'],
                $pricing['discount'],
                $pricing['final_price'],
                $tripType,
                $note
            ]);

            $bookingId = lastInsertId();
            error_log("[v0] *** BOOKING CREATED WITH ID: $bookingId ***");

            error_log("[v0] Creating booking details");
            if ($bookingData['outbound']) {
                $this->createBookingDetail($bookingId, $bookingData['outbound'], 'DaThanhToan');
            }
            
            if (isset($bookingData['return'])) {
                $this->createBookingDetail($bookingId, $bookingData['return'], 'DaThanhToan');
            }

            error_log("[v0] Committing transaction");
            query("COMMIT");
            error_log("[v0] Transaction committed successfully");

            error_log("[v0] ========== EMAIL SENDING PROCESS START ==========");
            error_log("[v0] MoMo - Starting email sending process for booking ID: $bookingId");
            
            try {
                error_log("[v0] MoMo - About to call Booking::getTicketDetailsForEmail");
                $ticketData = Booking::getTicketDetailsForEmail($bookingId);
                error_log("[v0] MoMo - getTicketDetailsForEmail returned: " . ($ticketData ? 'DATA' : 'NULL'));
                
                if ($ticketData) {
                    error_log("[v0] MoMo - Ticket data structure: " . json_encode(array_keys($ticketData)));
                    error_log("[v0] MoMo - User email: '" . ($ticketData['emailNguoiDung'] ?? 'EMPTY') . "'");
                    error_log("[v0] MoMo - Passenger emails: " . json_encode($ticketData['passengerEmails'] ?? []));
                    error_log("[v0] MoMo - Number of tickets: " . (isset($ticketData['tickets']) ? count($ticketData['tickets']) : 0));
                    
                    error_log("[v0] MoMo - Creating EmailService instance");
                    $emailService = new EmailService();
                    error_log("[v0] MoMo - EmailService created successfully");
                    
                    error_log("[v0] MoMo - Calling sendTicketEmail with ticketData");
                    $result = $emailService->sendTicketEmail($ticketData);
                    error_log("[v0] MoMo - sendTicketEmail completed");
                    error_log("[v0] MoMo - Ticket email send result: " . json_encode($result));
                    
                    if ($result['success']) {
                        error_log("[v0] MoMo - ✓ EMAIL SENT SUCCESSFULLY!");
                    } else {
                        error_log("[v0] MoMo - ✗ EMAIL FAILED: " . $result['message']);
                    }
                } else {
                    error_log("[v0] MoMo - ✗ Cannot send email - Ticket data not found");
                }
            } catch (Exception $emailError) {
                error_log("[v0] MoMo - ✗ EMAIL EXCEPTION: " . $emailError->getMessage());
                error_log("[v0] Email error file: " . $emailError->getFile() . " line " . $emailError->getLine());
                error_log("[v0] Email error trace: " . $emailError->getTraceAsString());
            }

            error_log("[v0] ========== EMAIL SENDING PROCESS END ==========");
            error_log("[v0] ========== processSuccessfulPayment END - Returning ID: $bookingId ==========");

            return $bookingId;

        } catch (Exception $e) {
            error_log("[v0] ✗ EXCEPTION in processSuccessfulPayment: " . $e->getMessage());
            error_log("[v0] Exception file: " . $e->getFile() . " line " . $e->getLine());
            error_log("[v0] Exception trace: " . $e->getTraceAsString());
            query("ROLLBACK");
            error_log("[v0] Transaction rolled back");
            throw $e;
        }
    }

    /**
     * Tạo đơn đặt vé thất bại
     */
    private function createFailedBooking($orderId, $amount, $resultCode) {
        try {
            error_log("[v0] Creating failed booking - OrderID: $orderId, Result Code: $resultCode");
            
            $bookingData = $_SESSION['final_booking_data'];
            $pricing = $this->calculatePricing($bookingData);

            query("START TRANSACTION");

            $userId = $_SESSION['user_id'] ?? null;
            $tripType = isset($bookingData['return']) ? 'KhuHoi' : 'MotChieu';
            
            $sql = "INSERT INTO datve (
                        maNguoiDung, soLuongVe, tongTien, giamGia, tongTienSauGiam, 
                        phuongThucThanhToan, loaiDatVe, trangThai, ngayDat,
                        ghiChu
                    ) VALUES (?, ?, ?, ?, ?, 'MoMo', ?, 'DaHuy', NOW(), ?)";
            
            $totalTickets = 0;
            if ($bookingData['outbound']) {
                $totalTickets += count($bookingData['outbound']['passengers']);
            }
            if (isset($bookingData['return'])) {
                $totalTickets += count($bookingData['return']['passengers']);
            }
            
            $note = "MoMo Failed - OrderID: $orderId, Result Code: $resultCode";
            
            query($sql, [
                $userId,
                $totalTickets,
                $pricing['original_price'],
                $pricing['discount'],
                $pricing['final_price'],
                $tripType,
                $note
            ]);
            
            $bookingId = lastInsertId();
            
            if ($bookingData['outbound']) {
                $this->createBookingDetail($bookingId, $bookingData['outbound'], 'DaHuy');
            }
            
            if (isset($bookingData['return'])) {
                $this->createBookingDetail($bookingId, $bookingData['return'], 'DaHuy');
            }

            query("COMMIT");
            error_log("[v0] Successfully created failed booking with ID: $bookingId");
            
        } catch (Exception $e) {
            query("ROLLBACK");
            error_log("[v0] Error creating failed booking: " . $e->getMessage());
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
    private function calculatePricing($bookingData) {
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
        $earnedPoints = floor($originalPrice * 0.0003);

        return [
            'original_price' => $originalPrice,
            'discount' => $discount,
            'final_price' => $finalPrice,
            'earned_points' => $earnedPoints
        ];
    }

    /**
     * Tạo chi tiết đặt vé
     */
    private function createBookingDetail($bookingId, $tripData, $status) {
        foreach ($tripData['passengers'] as $index => $passenger) {
            $seatNumber = $tripData['selected_seats'][$index];
            
            // Lấy ID ghế từ số ghế
            $seatSql = "SELECT g.maGhe FROM ghe g 
                       JOIN chuyenxe cx ON cx.maPhuongTien = g.maPhuongTien 
                       WHERE cx.maChuyenXe = ? AND g.soGhe = ?";
            $seat = fetch($seatSql, [$tripData['trip_id'], $seatNumber]);
            
            if ($seat) {
                $sql = "INSERT INTO chitiet_datve (maDatVe, maChuyenXe, maGhe, hoTenHanhKhach, 
                                                  emailHanhKhach, soDienThoaiHanhKhach, giaVe, 
                                                  maDiemDon, maDiemTra, trangThai, ngayTao)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                
                query($sql, [
                    $bookingId,
                    $tripData['trip_id'],
                    $seat['maGhe'],
                    $passenger['ho_ten'],
                    $passenger['email'] ?? '',
                    $passenger['so_dien_thoai'] ?? '',
                    $tripData['total_price'] / count($tripData['passengers']),
                    $tripData['pickup_point'],
                    $tripData['dropoff_point'],
                    $status
                ]);
            }
        }
    }

    /**
     * Xác nhận ghế (chuyển từ "Đang giữ" thành "Đã đặt")
     */
    private function confirmSeats() {
        if (!isset($_SESSION['held_seats'])) {
            return;
        }
        
        $heldSeats = $_SESSION['held_seats'];
        
        // Cập nhật trạng thái ghế chuyến đi
        if (!empty($heldSeats['trip_id']) && !empty($heldSeats['selected_seats'])) {
            $this->updateSeatsStatus($heldSeats['trip_id'], $heldSeats['selected_seats'], 'Đã đặt');
            $this->updateTripSeatCount($heldSeats['trip_id'], count($heldSeats['selected_seats']));
        }
        
        // Cập nhật trạng thái ghế chuyến về
        if (!empty($heldSeats['return_trip_id']) && !empty($heldSeats['return_selected_seats'])) {
            $this->updateSeatsStatus($heldSeats['return_trip_id'], $heldSeats['return_selected_seats'], 'Đã đặt');
            $this->updateTripSeatCount($heldSeats['return_trip_id'], count($heldSeats['return_selected_seats']));
        }
    }

    /**
     * Giải phóng ghế đang giữ
     */
    private function releaseHeldSeats() {
        if (!isset($_SESSION['held_seats'])) {
            return;
        }
        
        $heldSeats = $_SESSION['held_seats'];
        
        // Giải phóng ghế chuyến đi
        if (!empty($heldSeats['trip_id']) && !empty($heldSeats['selected_seats'])) {
            $this->updateSeatsStatus($heldSeats['trip_id'], $heldSeats['selected_seats'], 'Trống');
            $this->updateTripSeatCount($heldSeats['trip_id'], -count($heldSeats['selected_seats']));
        }
        
        // Giải phóng ghế chuyến về
        if (!empty($heldSeats['return_trip_id']) && !empty($heldSeats['return_selected_seats'])) {
            $this->updateSeatsStatus($heldSeats['return_trip_id'], $heldSeats['return_selected_seats'], 'Trống');
            $this->updateTripSeatCount($heldSeats['return_trip_id'], -count($heldSeats['return_selected_seats']));
        }
    }

    /**
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
            
            if (!$trip) {
                return false;
            }
            
            $vehicleId = $trip['maPhuongTien'];
            
            // Lấy ID ghế từ số ghế
            $placeholders = str_repeat('?,', count($seatNumbers) - 1) . '?';
            $sql = "SELECT maGhe, soGhe FROM ghe WHERE maPhuongTien = ? AND soGhe IN ($placeholders)";
            $params = array_merge([$vehicleId], $seatNumbers);
            $seats = fetchAll($sql, $params);
            
            // Cập nhật trạng thái từng ghế
            foreach ($seats as $seat) {
                $checkSql = "SELECT COUNT(*) as count FROM chuyenxe_ghe WHERE maChuyenXe = ? AND maGhe = ?";
                $result = fetch($checkSql, [$tripId, $seat['maGhe']]);
                
                if ($result['count'] > 0) {
                    $updateSql = "UPDATE chuyenxe_ghe SET trangThai = ?, ngayCapNhat = NOW() WHERE maChuyenXe = ? AND maGhe = ?";
                    query($updateSql, [$status, $tripId, $seat['maGhe']]);
                } else {
                    $insertSql = "INSERT INTO chuyenxe_ghe (maChuyenXe, maGhe, trangThai, ngayTao) VALUES (?, ?, ?, NOW())";
                    query($insertSql, [$tripId, $seat['maGhe'], $status]);
                }
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("[v0] MoMo updateSeatsStatus error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Xử lý điểm tích lũy
     */
    private function processLoyaltyPoints($bookingId) {
        if (!isset($_SESSION['user_id'])) {
            return;
        }

        $userId = $_SESSION['user_id'];
        $bookingData = $_SESSION['final_booking_data'];
        $pricing = $this->calculatePricing($bookingData);

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
            error_log("[v0] MoMo processLoyaltyPoints error: " . $e->getMessage());
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

    /**
     * Lấy thông báo lỗi từ mã result code MoMo
     */
    private function getMoMoErrorMessage($resultCode) {
        $errorMessages = [
            '0' => 'Giao dịch thành công',
            '1' => 'Giao dịch thất bại do người dùng từ chối',
            '4' => 'Giao dịch thất bại',
            '5' => 'Giao dịch bị từ chối',
            '6' => 'Tài khoản MoMo không đủ quỹ',
            '7' => 'Giao dịch bị hủy',
            '8' => 'Liên kết tài khoản ngân hàng thất bại',
            '9' => 'Tài khoản hoặc mật khẩu không chính xác',
            '10' => 'Lỗi giao dịch',
            '11' => 'Giao dịch bị lỗi kỹ thuật',
            '13' => 'Yêu cầu không hợp lệ',
            '20' => 'Giao dịch không được tìm thấy',
            '21' => 'Hoàn tiền bị từ chối',
            '40' => 'Tài khoản không kích hoạt',
            '41' => 'Tài khoản bị khóa',
            '42' => 'Tài khoản bị khóa do quá nhiều lần nhập sai mật khẩu',
            '43' => 'Tài khoản không tồn tại',
            '150' => 'Giao dịch bị từ chối - Bảo mật',
            '1000' => 'Giao dịch bị lỗi hệ thống',
            '9000' => 'Yêu cầu không hợp lệ'
        ];
        
        return $errorMessages[$resultCode] ?? 'Lỗi không xác định (Mã: ' . $resultCode . ')';
    }
}
?>
