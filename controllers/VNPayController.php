<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../lib/EmailService.php';

class VNPayController {
    private $tmnCode;
    private $hashSecret;
    private $url;
    private $returnUrl;
    
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->tmnCode = 'O3RU0JR9'; 
        $this->hashSecret = 'MWQMV1515QDCZFO19Y2N9PNWZVWEJYO2';
        $this->url = 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html';
        $this->returnUrl = BASE_URL . '/payment/vnpay/return';
        
        error_log("[v0] VNPayController initialized with return URL: " . $this->returnUrl);
    }
    
    /**
     * Tạo thanh toán VNPay
     */
    public function createPayment() {
        try {
            error_log("[v0] VNPayController::createPayment() started");
            
            // Kiểm tra thông tin đặt vé
            if (!isset($_SESSION['final_booking_data'])) {
                error_log("[v0] No final_booking_data in session");
                $_SESSION['error'] = 'Không tìm thấy thông tin đặt vé.';
                header('Location: ' . BASE_URL . '/payment');
                exit;
            }
            
            $bookingData = $_SESSION['final_booking_data'];
            
            // Tính toán giá cuối cùng
            $pricing = $this->calculatePricing($bookingData);
            
            $vnp_TxnRef = time() . ""; // Đơn giản hóa theo file tham khảo
            
            // Lưu transaction reference vào session để verify sau
            $_SESSION['vnpay_txn_ref'] = $vnp_TxnRef;
            $_SESSION['selected_payment_method'] = 'VNPay';
            
            $vnp_OrderInfo = 'Thanh toán đơn hàng đặt tại web';
            $vnp_OrderType = 'billpayment';
            $vnp_Amount = $pricing['final_price'] * 100; // VNPay yêu cầu số tiền tính bằng đồng
            $vnp_Locale = 'vn';
            $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
            
            $startTime = date("YmdHis");
            $vnp_ExpireDate = date('YmdHis', strtotime('+15 minutes', strtotime($startTime)));
            
            $vnp_BankCode = 'NCB';
            
            // Thông tin billing từ session hoặc user
            $vnp_Bill_Mobile = '';
            $vnp_Bill_Email = '';
            $vnp_Bill_FirstName = '';
            $vnp_Bill_LastName = '';
            
            if (isset($_SESSION['user_id'])) {
                // Lấy thông tin user từ database
                $userSql = "SELECT * FROM nguoidung WHERE maNguoiDung = ?";
                $user = fetch($userSql, [$_SESSION['user_id']]);
                if ($user) {
                    $vnp_Bill_Mobile = $user['soDienThoai'] ?? '';
                    $vnp_Bill_Email = $user['email'] ?? '';
                    $fullName = trim($user['hoTen'] ?? '');
                    if (!empty($fullName)) {
                        $nameParts = explode(' ', $fullName);
                        $vnp_Bill_FirstName = array_shift($nameParts);
                        $vnp_Bill_LastName = implode(' ', $nameParts);
                    }
                }
            }
            
            $inputData = array(
                "vnp_Version" => "2.1.0",
                "vnp_TmnCode" => $this->tmnCode,
                "vnp_Amount" => $vnp_Amount,
                "vnp_Command" => "pay",
                "vnp_CreateDate" => date('YmdHis'),
                "vnp_CurrCode" => "VND",
                "vnp_IpAddr" => $vnp_IpAddr,
                "vnp_Locale" => $vnp_Locale,
                "vnp_OrderInfo" => $vnp_OrderInfo,
                "vnp_OrderType" => $vnp_OrderType,
                "vnp_ReturnUrl" => $this->returnUrl,
                "vnp_TxnRef" => $vnp_TxnRef,
                "vnp_ExpireDate" => $vnp_ExpireDate
            );
            
            if (isset($vnp_BankCode) && $vnp_BankCode != "") {
                $inputData['vnp_BankCode'] = $vnp_BankCode;
            }
            
            // Thêm thông tin billing chỉ khi không rỗng
            if (!empty($vnp_Bill_Mobile)) {
                $inputData['vnp_Bill_Mobile'] = $vnp_Bill_Mobile;
            }
            if (!empty($vnp_Bill_Email)) {
                $inputData['vnp_Bill_Email'] = $vnp_Bill_Email;
            }
            if (!empty($vnp_Bill_FirstName)) {
                $inputData['vnp_Bill_FirstName'] = $vnp_Bill_FirstName;
            }
            if (!empty($vnp_Bill_LastName)) {
                $inputData['vnp_Bill_LastName'] = $vnp_Bill_LastName;
            }
            
            // Sắp xếp dữ liệu theo thứ tự alphabet (quan trọng cho chữ ký)
            ksort($inputData);
            
            $query = "";
            $i = 0;
            $hashdata = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashdata .= urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
                $query .= urlencode($key) . "=" . urlencode($value) . '&';
            }
            
            $vnp_Url = $this->url . "?" . $query;
            if (isset($this->hashSecret)) {
                $vnpSecureHash = hash_hmac('sha512', $hashdata, $this->hashSecret);
                $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
            }
            
            error_log("[v0] VNPay payment URL created successfully");
            error_log("[v0] Transaction reference: " . $vnp_TxnRef);
            error_log("[v0] Amount: " . $vnp_Amount);
            error_log("[v0] Hash data: " . $hashdata);
            error_log("[v0] Secure hash: " . $vnpSecureHash);
            error_log("[v0] Final URL: " . $vnp_Url);
            
            // Chuyển hướng đến VNPay
            header('Location: ' . $vnp_Url);
            exit;
            
        } catch (Exception $e) {
            error_log("[v0] VNPayController createPayment error: " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi tạo thanh toán VNPay: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/payment');
            exit;
        }
    }
    
    /**
     * Xử lý kết quả trả về từ VNPay
     */
    public function handleReturn() {
        try {
            error_log("[v0] VNPayController::handleReturn() started");
            error_log("[v0] GET parameters: " . json_encode($_GET));
            
            // Lấy secure hash từ VNPay
            $vnp_SecureHash = $_GET['vnp_SecureHash'] ?? '';
            
            // Tạo mảng dữ liệu input từ GET parameters
            $inputData = array();
            foreach ($_GET as $key => $value) {
                if (substr($key, 0, 4) == "vnp_") {
                    $inputData[$key] = $value;
                }
            }
            
            // Loại bỏ vnp_SecureHash khỏi dữ liệu để tính hash
            unset($inputData['vnp_SecureHash']);
            
            // Sắp xếp dữ liệu theo thứ tự alphabet
            ksort($inputData);
            
            $i = 0;
            $hashData = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
            }
            
            // Tính secure hash để so sánh
            $secureHash = hash_hmac('sha512', $hashData, $this->hashSecret);
            
            error_log("[v0] Calculated hash: " . $secureHash);
            error_log("[v0] VNPay hash: " . $vnp_SecureHash);
            error_log("[v0] Hash data: " . $hashData);
            
            // Verify chữ ký
            if ($secureHash === $vnp_SecureHash) {
                error_log("[v0] Hash verification successful");
                
                // Lấy thông tin giao dịch
                $vnp_ResponseCode = $_GET['vnp_ResponseCode'] ?? '';
                $vnp_TransactionStatus = $_GET['vnp_TransactionStatus'] ?? '';
                $vnp_TxnRef = $_GET['vnp_TxnRef'] ?? '';
                $vnp_Amount = ($_GET['vnp_Amount'] ?? 0) / 100; // Chuyển về VND
                $vnp_BankCode = $_GET['vnp_BankCode'] ?? '';
                $vnp_TransactionNo = $_GET['vnp_TransactionNo'] ?? '';
                $vnp_PayDate = $_GET['vnp_PayDate'] ?? '';
                
                error_log("[v0] Response code: " . $vnp_ResponseCode);
                error_log("[v0] Transaction status: " . $vnp_TransactionStatus);
                error_log("[v0] Transaction ref: " . $vnp_TxnRef);
                
                // Kiểm tra mã giao dịch có khớp với session không
                if (isset($_SESSION['vnpay_txn_ref']) && $_SESSION['vnpay_txn_ref'] === $vnp_TxnRef) {
                    
                    if ($vnp_ResponseCode === '00' && $vnp_TransactionStatus === '00') {
                        // Thanh toán thành công
                        error_log("[v0] Payment successful");
                        
                        // Tạo đơn đặt vé
                        $bookingId = $this->createSuccessfulBooking($vnp_TxnRef, $vnp_Amount, $vnp_TransactionNo, $vnp_BankCode, $vnp_PayDate);
                        
                        if ($bookingId) {
                            // Xác nhận ghế (chuyển từ "Đang giữ" thành "Đã đặt")
                            $this->confirmSeats();
                            
                            // Xử lý điểm tích lũy
                            $this->processLoyaltyPoints($bookingId);
                            
                            // Clear session
                            $this->clearBookingSession();
                            
                            $_SESSION['success'] = 'Thanh toán thành công! Mã đặt vé: ' . $bookingId;
                            header('Location: ' . BASE_URL . '/booking/success/' . $bookingId);
                            exit;
                        } else {
                            throw new Exception('Không thể tạo đơn đặt vé');
                        }
                        
                    } else {
                        // Thanh toán thất bại
                        error_log("[v0] Payment failed - Response code: " . $vnp_ResponseCode);
                        
                        // Tạo đơn đặt vé với trạng thái thất bại
                        $this->createFailedBooking($vnp_TxnRef, $vnp_Amount, $vnp_ResponseCode);
                        
                        // Giải phóng ghế
                        $this->releaseHeldSeats();
                        
                        // Clear session
                        $this->clearBookingSession();
                        
                        $errorMessage = $this->getVNPayErrorMessage($vnp_ResponseCode);
                        $_SESSION['error'] = 'Thanh toán thất bại: ' . $errorMessage;
                        header('Location: ' . BASE_URL . '/search');
                        exit;
                    }
                    
                } else {
                    error_log("[v0] Transaction reference mismatch");
                    $_SESSION['error'] = 'Mã giao dịch không hợp lệ.';
                    header('Location: ' . BASE_URL . '/search');
                    exit;
                }
                
            } else {
                // Chữ ký không hợp lệ
                error_log("[v0] Hash verification failed");
                $_SESSION['error'] = 'Chữ ký không hợp lệ. Giao dịch có thể đã bị can thiệp.';
                header('Location: ' . BASE_URL . '/search');
                exit;
            }
            
        } catch (Exception $e) {
            error_log("[v0] VNPayController handleReturn error: " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi xử lý kết quả thanh toán: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/search');
            exit;
        }
    }
    
    /**
     * Xử lý IPN (Instant Payment Notification) từ VNPay
     */
    public function callback() {
        header('Content-Type: application/json');
        
        try {
            error_log("[v0] VNPayController::callback() started");
            
            $inputData = array();
            $returnData = array();
            
            // Lấy dữ liệu từ GET parameters
            foreach ($_GET as $key => $value) {
                if (substr($key, 0, 4) == "vnp_") {
                    $inputData[$key] = $value;
                }
            }
            
            $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
            unset($inputData['vnp_SecureHash']);
            
            // Sắp xếp dữ liệu
            ksort($inputData);
            
            $i = 0;
            $hashData = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
            }
            
            // Verify hash
            $secureHash = hash_hmac('sha512', $hashData, $this->hashSecret);
            
            if ($secureHash === $vnp_SecureHash) {
                // Hash hợp lệ
                $vnp_TxnRef = $inputData['vnp_TxnRef'] ?? '';
                $vnp_Amount = ($inputData['vnp_Amount'] ?? 0) / 100;
                $vnp_ResponseCode = $inputData['vnp_ResponseCode'] ?? '';
                $vnp_TransactionStatus = $inputData['vnp_TransactionStatus'] ?? '';
                
                $orderSql = "SELECT * FROM datve WHERE ghiChu LIKE ? AND trangThai = 'DangGiu' LIMIT 1";
                $order = fetch($orderSql, ["%TxnRef: $vnp_TxnRef%"]);
                
                if ($order) {
                    if ($order['tongTienSauGiam'] == $vnp_Amount) {
                        if ($order['trangThai'] === 'DangGiu') {
                            if ($vnp_ResponseCode === '00' && $vnp_TransactionStatus === '00') {
                                $newStatus = 'DaThanhToan';
                            } else {
                                $newStatus = 'DaHuy';
                            }
                            
                            $updateSql = "UPDATE datve SET trangThai = ?, ngayCapNhat = NOW() WHERE maDatVe = ?";
                            query($updateSql, [$newStatus, $order['maDatVe']]);
                            
                            $returnData['RspCode'] = '00';
                            $returnData['Message'] = 'Confirm Success';
                        } else {
                            $returnData['RspCode'] = '02';
                            $returnData['Message'] = 'Order already confirmed';
                        }
                    } else {
                        $returnData['RspCode'] = '04';
                        $returnData['Message'] = 'Invalid amount';
                    }
                } else {
                    $returnData['RspCode'] = '01';
                    $returnData['Message'] = 'Order not found';
                }
            } else {
                $returnData['RspCode'] = '97';
                $returnData['Message'] = 'Invalid signature';
            }
            
        } catch (Exception $e) {
            error_log("[v0] VNPayController callback error: " . $e->getMessage());
            $returnData['RspCode'] = '99';
            $returnData['Message'] = 'Unknown error';
        }
        
        // Trả về JSON response cho VNPay
        echo json_encode($returnData);
        exit;
    }
    
    /**
     * Tạo đơn đặt vé thành công
     */
    private function createSuccessfulBooking($txnRef, $amount, $transactionNo, $bankCode, $payDate) {
        error_log("[v0] ========== createSuccessfulBooking START ==========");
        error_log("[v0] createSuccessfulBooking - ENTRY POINT - Booking ID will be created");
        error_log("[v0] Parameters: txnRef=$txnRef, amount=$amount, transactionNo=$transactionNo, bankCode=$bankCode, payDate=$payDate");
        
        try {
            error_log("[v0] Creating successful booking");
            
            if (!isset($_SESSION['final_booking_data'])) {
                error_log("[v0] CRITICAL: final_booking_data not in session!");
                throw new Exception('Session data missing');
            }
            
            $bookingData = $_SESSION['final_booking_data'];
            error_log("[v0] Booking data retrieved from session: " . json_encode($bookingData));
            
            $pricing = $this->calculatePricing($bookingData);
            error_log("[v0] Pricing calculated: " . json_encode($pricing));

            error_log("[v0] Starting database transaction");
            query("START TRANSACTION");

            $userId = $_SESSION['user_id'] ?? null;
            $tripType = isset($bookingData['return']) ? 'KhuHoi' : 'MotChieu';
            
            $sql = "INSERT INTO datve (
                        maNguoiDung, soLuongVe, tongTien, giamGia, tongTienSauGiam, 
                        phuongThucThanhToan, loaiDatVe, trangThai, ngayDat,
                        ghiChu
                    ) VALUES (?, ?, ?, ?, ?, 'VNPay', ?, 'DaThanhToan', NOW(), ?)";
            
            $totalTickets = 0;
            if ($bookingData['outbound']) {
                $totalTickets += count($bookingData['outbound']['passengers']);
            }
            if (isset($bookingData['return'])) {
                $totalTickets += count($bookingData['return']['passengers']);
            }
            
            $note = "VNPay TxnRef: $txnRef, TransactionNo: $transactionNo, Bank: $bankCode";
            if ($payDate) {
                $note .= ", PayDate: $payDate";
            }
            
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

            // Create booking details
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
            error_log("[v0] VNPay - Starting email sending process for booking ID: $bookingId");
            
            try {
                error_log("[v0] VNPay - About to call Booking::getTicketDetailsForEmail");
                $ticketData = Booking::getTicketDetailsForEmail($bookingId);
                error_log("[v0] VNPay - getTicketDetailsForEmail returned: " . ($ticketData ? 'DATA' : 'NULL'));
                
                if ($ticketData) {
                    error_log("[v0] VNPay - Ticket data structure: " . json_encode(array_keys($ticketData)));
                    error_log("[v0] VNPay - User email: '" . ($ticketData['emailNguoiDung'] ?? 'EMPTY') . "'");
                    error_log("[v0] VNPay - Passenger emails: " . json_encode($ticketData['passengerEmails'] ?? []));
                    error_log("[v0] VNPay - Number of tickets: " . (isset($ticketData['tickets']) ? count($ticketData['tickets']) : 0));
                    
                    error_log("[v0] VNPay - Creating EmailService instance");
                    $emailService = new EmailService();
                    error_log("[v0] VNPay - EmailService created successfully");
                    
                    error_log("[v0] VNPay - Calling sendTicketEmail with ticketData");
                    $result = $emailService->sendTicketEmail($ticketData);
                    error_log("[v0] VNPay - sendTicketEmail completed");
                    error_log("[v0] VNPay - Ticket email send result: " . json_encode($result));
                    
                    if ($result['success']) {
                        error_log("[v0] VNPay - ✓ EMAIL SENT SUCCESSFULLY!");
                    } else {
                        error_log("[v0] VNPay - ✗ EMAIL FAILED: " . $result['message']);
                    }
                } else {
                    error_log("[v0] VNPay - ✗ Cannot send email - Ticket data not found");
                }
            } catch (Exception $emailError) {
                error_log("[v0] VNPay - ✗ EMAIL EXCEPTION: " . $emailError->getMessage());
                error_log("[v0] Email error file: " . $emailError->getFile() . " line " . $emailError->getLine());
                error_log("[v0] Email error trace: " . $emailError->getTraceAsString());
            }
            
            error_log("[v0] ========== EMAIL SENDING PROCESS END ==========");
            error_log("[v0] ========== createSuccessfulBooking END - Returning ID: $bookingId ==========");
            
            return $bookingId;
            
        } catch (Exception $e) {
            error_log("[v0] ✗ EXCEPTION in createSuccessfulBooking: " . $e->getMessage());
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
    private function createFailedBooking($txnRef, $amount, $responseCode) {
        try {
            error_log("[v0] Creating failed booking");
            
            $bookingData = $_SESSION['final_booking_data'];
            $pricing = $this->calculatePricing($bookingData);

            query("START TRANSACTION");

            $userId = $_SESSION['user_id'] ?? null;
            $tripType = isset($bookingData['return']) ? 'KhuHoi' : 'MotChieu';
            
            $sql = "INSERT INTO datve (
                        maNguoiDung, soLuongVe, tongTien, giamGia, tongTienSauGiam, 
                        phuongThucThanhToan, loaiDatVe, trangThai, ngayDat,
                        ghiChu
                    ) VALUES (?, ?, ?, ?, ?, 'VNPay', ?, 'DaHuy', NOW(), ?)";
            
            $totalTickets = 0;
            if ($bookingData['outbound']) {
                $totalTickets += count($bookingData['outbound']['passengers']);
            }
            if (isset($bookingData['return'])) {
                $totalTickets += count($bookingData['return']['passengers']);
            }
            
            $note = "VNPay Failed - TxnRef: $txnRef, Response Code: $responseCode";
            
            query($sql, [
                $userId,
                $totalTickets,
                $pricing['original_price'],
                $pricing['discount'], // Store the actual discount amount
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
            error_log("[v0] Successfully created failed booking");
            
        } catch (Exception $e) {
            query("ROLLBACK");
            error_log("[v0] Error creating failed booking: " . $e->getMessage());
        }
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
            error_log("[v0] Updating seat count for trip $tripId by $seatCountChange");
            
            // Cập nhật số ghế đã đặt
            $sql = "UPDATE chuyenxe SET soChoDaDat = soChoDaDat + ? WHERE maChuyenXe = ?";
            query($sql, [$seatCountChange, $tripId]);
            
            // Log để kiểm tra
            $checkSql = "SELECT soChoTong, soChoDaDat, soChoTrong FROM chuyenxe WHERE maChuyenXe = ?";
            $result = fetch($checkSql, [$tripId]);
            if ($result) {
                error_log("[v0] Trip $tripId seat count updated - Total: {$result['soChoTong']}, Booked: {$result['soChoDaDat']}, Available: {$result['soChoTrong']}");
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("[v0] updateTripSeatCount error: " . $e->getMessage());
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
            error_log("[v0] updateSeatsStatus error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Tính toán giá cuối cùng
     */
    private function calculatePricing($bookingData) {
        $originalPrice = $bookingData['total_price'];
        $discount = 0;
        $finalPrice = $originalPrice;
        
        // Áp dụng khuyến mãi
        if (isset($_SESSION['applied_promotion'])) {
            $promotion = $_SESSION['applied_promotion'];
            if ($promotion['loai'] === 'PhanTram') {
                $discount += $originalPrice * ($promotion['giaTri'] / 100);
            } else {
                $discount += $promotion['giaTri'];
            }
        }
        
        // Áp dụng điểm tích lũy (1 điểm = 100đ)
        if (isset($_SESSION['used_points'])) {
            $discount += $_SESSION['used_points'] * 100;
        }
        
        $finalPrice = max(0, $originalPrice - $discount);
        
        // Tính điểm tích lũy nhận được (0.1% tổng tiền gốc)
        $earnedPoints = floor($originalPrice * 0.001);
        
        return [
            'original_price' => $originalPrice,
            'discount' => $discount,
            'final_price' => $finalPrice,
            'earned_points' => $earnedPoints
        ];
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
            error_log("[v0] processLoyaltyPoints error: " . $e->getMessage());
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
        unset($_SESSION['vnpay_txn_ref']);
        unset($_SESSION['selected_payment_method']);
    }
    
    /**
     * Lấy thông báo lỗi từ mã response VNPay
     */
    private function getVNPayErrorMessage($responseCode) {
        $errorMessages = [
            '01' => 'Giao dịch chưa hoàn tất',
            '02' => 'Giao dịch bị lỗi',
            '04' => 'Giao dịch đảo (Khách hàng đã bị trừ tiền tại Ngân hàng nhưng GD chưa thành công ở VNPAY)',
            '05' => 'VNPAY đang xử lý giao dịch này (GD hoàn tiền)',
            '06' => 'VNPAY đã gửi yêu cầu hoàn tiền sang Ngân hàng (GD hoàn tiền)',
            '07' => 'Giao dịch bị nghi ngờ gian lận',
            '09' => 'GD Hoàn trả bị từ chối',
            '10' => 'Đã giao hàng',
            '11' => 'Giao dịch không thành công do: Khách hàng nhập sai mật khẩu xác thực giao dịch (OTP). Xin quý khách vui lòng thực hiện lại giao dịch.',
            '12' => 'Giao dịch không thành công do: Thẻ/Tài khoản của khách hàng bị khóa.',
            '13' => 'Giao dịch không thành công do Quý khách nhập sai mật khẩu xác thực giao dịch (OTP). Xin quý khách vui lòng thực hiện lại giao dịch.',
            '21' => 'Giao dịch không thành công do: Tài khoản của quý khách không đủ số dư để thực hiện giao dịch.',
            '22' => 'Giao dịch không thành công do: Thông tin thẻ/tài khoản của Quý khách không chính xác',
            '24' => 'Giao dịch không thành công do: Quý khách đã hủy giao dịch',
            '25' => 'Giao dịch không thành công do: Ngân hàng không hỗ trợ giao dịch trực tuyến.',
            '51' => 'Giao dịch không thành công do: Tài khoản của quý khách không đủ số dư để thực hiện giao dịch.',
            '65' => 'Giao dịch không thành công do: Tài khoản của Quý khách đã vượt quá hạn mức giao dịch trong ngày.',
            '75' => 'Ngân hàng thanh toán đang bảo trì.',
            '79' => 'Giao dịch không thành công do: KH nhập sai mật khẩu thanh toán quá số lần quy định. Xin quý khách vui lòng thực hiện lại giao dịch',
            '99' => 'Các lỗi khác (lỗi còn lại, không có trong danh sách mã lỗi đã liệt kê)'
        ];
        
        return $errorMessages[$responseCode] ?? 'Lỗi không xác định';
    }
}
?>
