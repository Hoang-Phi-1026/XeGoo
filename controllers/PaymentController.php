<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class PaymentController {
    
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Hiển thị trang thanh toán
     */
    public function show() {
        
        try {
            error_log("[v0] PaymentController::show() started");
            error_log("[v0] Session data - final_booking_data exists: " . (isset($_SESSION['final_booking_data']) ? 'yes' : 'no'));
            error_log("[v0] Session data - held_seats exists: " . (isset($_SESSION['held_seats']) ? 'yes' : 'no'));
            
            if (!isset($_SESSION['final_booking_data'])) {
                error_log("[v0] No final_booking_data in session, redirecting to search");
                $_SESSION['error'] = 'Không tìm thấy thông tin đặt vé. Vui lòng thử lại.';
                header('Location: ' . BASE_URL . '/search');
                exit;
            }

            if (!isset($_SESSION['held_seats'])) {
                $_SESSION['held_seats'] = [
                    'expires_at' => time() + (5 * 60),
                    'hold_time' => time()
                ];
            } else {
                // Force lại expires_at về tối đa 5 phút kể từ hiện tại nếu lớn hơn
                $now = time();
                if ($_SESSION['held_seats']['expires_at'] - $now > 5*60) {
                    $_SESSION['held_seats']['expires_at'] = $now + 5*60;
                    $_SESSION['held_seats']['hold_time'] = $now;
                }
            }

            $bookingData = $_SESSION['final_booking_data'];
            $heldSeats = $_SESSION['held_seats'];
            
            error_log("[v0] Booking data loaded successfully");
            error_log("[v0] Total price: " . $bookingData['total_price']);

            $promotions = [];
            $userPoints = 0;
            $isLoggedIn = isset($_SESSION['user_id']);
            
            try {
                $promotions = $this->getActivePromotions();
                error_log("[v0] Found " . count($promotions) . " active promotions");
            } catch (Exception $e) {
                error_log("[v0] Error loading promotions: " . $e->getMessage());
            }

            if ($isLoggedIn) {
                try {
                    $userPoints = $this->getUserPoints($_SESSION['user_id']);
                    error_log("[v0] User points: $userPoints");
                } catch (Exception $e) {
                    error_log("[v0] Error loading user points: " . $e->getMessage());
                }
            }

            // Tính toán giá
            $pricing = $this->calculatePricing($bookingData);
            error_log("[v0] Pricing calculated - Original: " . $pricing['original_price'] . ", Final: " . $pricing['final_price']);

            $viewData = compact(
                'bookingData', 'heldSeats', 'promotions', 'userPoints', 'pricing', 'isLoggedIn'
            );
            extract($viewData);

            error_log("[v0] About to include payment view");
            include __DIR__ . '/../views/payment/show.php';
            error_log("[v0] Payment view included successfully");

        } catch (Exception $e) {
            error_log("[v0] PaymentController show error: " . $e->getMessage());
            error_log("[v0] Stack trace: " . $e->getTraceAsString());
            $_SESSION['error'] = 'Có lỗi xảy ra khi hiển thị trang thanh toán: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/search');
            exit;
        }
    }

    /**
     * Xử lý áp dụng khuyến mãi
     */
    public function applyPromotion() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $promotionId = $input['promotion_id'] ?? '';

            if (empty($promotionId)) {
                echo json_encode(['success' => false, 'message' => 'Vui lòng chọn mã khuyến mãi']);
                return;
            }

            // Lấy thông tin khuyến mãi
            $promotion = $this->getPromotionById($promotionId);
            if (!$promotion) {
                echo json_encode(['success' => false, 'message' => 'Mã khuyến mãi không tồn tại']);
                return;
            }

            // Kiểm tra thời hạn
            $currentDate = date('Y-m-d');
            if ($currentDate < $promotion['ngayBatDau'] || $currentDate > $promotion['ngayKetThuc']) {
                echo json_encode(['success' => false, 'message' => 'Mã khuyến mãi đã hết hạn']);
                return;
            }

            if (isset($_SESSION['user_id'])) {
                $usageCheck = $this->checkPromotionUsage($_SESSION['user_id'], $promotionId);
                if (!$usageCheck['can_use']) {
                    echo json_encode(['success' => false, 'message' => $usageCheck['message']]);
                    return;
                }
            }

            if ($promotion['doiTuongApDung'] === 'Khách hàng thân thiết') {
                if (!isset($_SESSION['user_id'])) {
                    echo json_encode(['success' => false, 'message' => 'Bạn phải đăng nhập để sử dụng mã khuyến mãi này']);
                    return;
                }
                
                if (!$this->isLoyalCustomer($_SESSION['user_id'])) {
                    echo json_encode(['success' => false, 'message' => 'Bạn chưa đủ điều kiện để sử dụng mã khuyến mãi này. Mã này chỉ dành cho khách hàng thân thiết (≥5000 điểm tích lũy từ mua vé).']);
                    return;
                }
            }

            // Lưu vào session
            $_SESSION['applied_promotion'] = $promotion;

            // Tính lại giá
            $bookingData = $_SESSION['final_booking_data'];
            $pricing = $this->calculatePricing($bookingData);

            echo json_encode([
                'success' => true, 
                'message' => 'Áp dụng khuyến mãi thành công',
                'pricing' => $pricing
            ]);

        } catch (Exception $e) {
            error_log("[v0] PaymentController applyPromotion error: " . $e->getMessage() . " | Stack: " . $e->getTraceAsString());
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Xử lý sử dụng điểm tích lũy
     */
    public function usePoints() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $pointsToUse = (int)($input['points'] ?? 0);

            if ($pointsToUse <= 0) {
                echo json_encode(['success' => false, 'message' => 'Số điểm không hợp lệ']);
                return;
            }

            // Kiểm tra điểm có sẵn
            $userPoints = 0;
            if (isset($_SESSION['user_id'])) {
                $userPoints = $this->getUserPoints($_SESSION['user_id']);
            }

            if ($pointsToUse > $userPoints) {
                echo json_encode(['success' => false, 'message' => 'Không đủ điểm tích lũy']);
                return;
            }

            $bookingData = $_SESSION['final_booking_data'];
            $maxPointsAllowed = floor($bookingData['total_price'] / 200); // 50% of order price
            
            if ($pointsToUse > $maxPointsAllowed) {
                error_log("[v0] Points exceeded 50% limit - Requested: $pointsToUse, Max allowed: $maxPointsAllowed");
                echo json_encode([
                    'success' => false, 
                    'message' => 'Tối đa chỉ được sử dụng 50% giá trị đơn hàng (' . $maxPointsAllowed . ' điểm)'
                ]);
                return;
            }

            // Lưu vào session
            $_SESSION['used_points'] = $pointsToUse;

            // Tính lại giá
            $pricing = $this->calculatePricing($bookingData);

            echo json_encode([
                'success' => true, 
                'message' => 'Sử dụng điểm tích lũy thành công',
                'pricing' => $pricing
            ]);

        } catch (Exception $e) {
            error_log("PaymentController usePoints error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Xử lý bỏ khuyến mãi - New method
     */
    public function removePromotion() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        try {
            // Remove promotion from session
            unset($_SESSION['applied_promotion']);

            // Recalculate pricing
            $bookingData = $_SESSION['final_booking_data'];
            $pricing = $this->calculatePricing($bookingData);

            echo json_encode([
                'success' => true, 
                'message' => 'Đã bỏ chọn mã giảm giá',
                'pricing' => $pricing
            ]);

        } catch (Exception $e) {
            error_log("PaymentController removePromotion error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Xử lý bỏ sử dụng điểm tích lũy - New method
     */
    public function removePoints() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        try {
            // Remove points from session
            unset($_SESSION['used_points']);

            // Recalculate pricing
            $bookingData = $_SESSION['final_booking_data'];
            $pricing = $this->calculatePricing($bookingData);

            echo json_encode([
                'success' => true, 
                'message' => 'Đã bỏ sử dụng điểm tích lũy',
                'pricing' => $pricing
            ]);

        } catch (Exception $e) {
            error_log("PaymentController removePoints error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Xử lý thanh toán - Updated to NOT create booking until payment success
     */
    public function process() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/search');
            exit;
        }

        try {
            $paymentMethod = $_POST['payment_method'] ?? '';
            
            if (empty($paymentMethod) || !in_array($paymentMethod, ['MoMo', 'VNPay'])) {
                $_SESSION['error'] = 'Vui lòng chọn phương thức thanh toán.';
                header('Location: ' . BASE_URL . '/payment');
                exit;
            }

            // Kiểm tra thông tin đặt vé và ghế giữ
            if (!isset($_SESSION['final_booking_data']) || !isset($_SESSION['held_seats'])) {
                $_SESSION['error'] = 'Phiên đặt vé đã hết hạn.';
                header('Location: ' . BASE_URL . '/search');
                exit;
            }

            // Kiểm tra ghế có còn được giữ không
            $heldSeats = $_SESSION['held_seats'];
            if (time() > $heldSeats['expires_at']) {
                $_SESSION['error'] = 'Phiên giữ ghế đã hết hạn.';
                header('Location: ' . BASE_URL . '/search');
                exit;
            }

            // Chuyển hướng đến cổng thanh toán mà KHÔNG tạo đơn đặt vé
            if ($paymentMethod === 'MoMo') {
                require_once __DIR__ . '/MoMoController.php';
                $momoController = new MoMoController();
                $momoController->createPayment();
            } elseif ($paymentMethod === 'VNPay') {
                require_once __DIR__ . '/VNPayController.php';
                $vnpayController = new VNPayController();
                $vnpayController->createPayment();
            }

        } catch (Exception $e) {
            error_log("PaymentController process error: " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/payment');
            exit;
        }
    }

    /**
     * Hủy thanh toán và giải phóng ghế - Updated to save cancelled booking
     */
    public function cancel() {
        try {
            error_log("[v0] PaymentController::cancel() called");
            
            if (isset($_SESSION['held_seats'])) {
                error_log("[v0] Found held seats, releasing them");
                $heldSeats = $_SESSION['held_seats'];
                
                $this->releaseSeatsDirectly($heldSeats);
            } else {
                error_log("[v0] No held seats found in session");
            }

            // Clear session
            $this->clearBookingSession();

            $_SESSION['info'] = 'Đã hủy thanh toán và giải phóng ghế.';
            header('Location: ' . BASE_URL . '/search');
            exit;

        } catch (Exception $e) {
            error_log("PaymentController cancel error: " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi hủy thanh toán: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/search');
            exit;
        }
    }

    /**
     * Giải phóng ghế trực tiếp - NEW SIMPLE METHOD
     */
    private function releaseSeatsDirectly($heldSeats) {
        try {
            error_log("[v0] releaseSeatsDirectly() called");
            
            // Start transaction for atomic operation
            query("START TRANSACTION");
            
            $totalReleased = 0;
            
            // Release outbound trip seats
            if (!empty($heldSeats['trip_id']) && !empty($heldSeats['selected_seats'])) {
                error_log("[v0] Releasing outbound seats for trip: " . $heldSeats['trip_id']);
                $released = $this->releaseSeatsForTripDirect($heldSeats['trip_id'], $heldSeats['selected_seats']);
                $totalReleased += $released;
                error_log("[v0] Released $released outbound seats");
            }
            
            // Release return trip seats
            if (!empty($heldSeats['return_trip_id']) && !empty($heldSeats['return_selected_seats'])) {
                error_log("[v0] Releasing return seats for trip: " . $heldSeats['return_trip_id']);
                $released = $this->releaseSeatsForTripDirect($heldSeats['return_trip_id'], $heldSeats['return_selected_seats']);
                $totalReleased += $released;
                error_log("[v0] Released $released return seats");
            }
            
            query("COMMIT");
            error_log("[v0] Successfully released $totalReleased seats total");
            
            if ($totalReleased === 0) {
                throw new Exception("No seats were released");
            }
            
        } catch (Exception $e) {
            query("ROLLBACK");
            error_log("[v0] Error in releaseSeatsDirectly: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Giải phóng ghế cho một chuyến xe cụ thể - NEW DIRECT METHOD
     */
    private function releaseSeatsForTripDirect($tripId, $seatNumbers) {
        try {
            error_log("[v0] releaseSeatsForTripDirect() - Trip: $tripId, Seats: " . implode(',', $seatNumbers));
            
            // Get vehicle ID from trip
            $sql = "SELECT maPhuongTien FROM chuyenxe WHERE maChuyenXe = ?";
            $trip = fetch($sql, [$tripId]);
            
            if (!$trip) {
                error_log("[v0] Trip not found: $tripId");
                return 0;
            }
            
            $vehicleId = $trip['maPhuongTien'];
            error_log("[v0] Vehicle ID: $vehicleId");
            
            // Get seat IDs from seat numbers
            $placeholders = str_repeat('?,', count($seatNumbers) - 1) . '?';
            $sql = "SELECT maGhe, soGhe FROM ghe WHERE maPhuongTien = ? AND soGhe IN ($placeholders)";
            $params = array_merge([$vehicleId], $seatNumbers);
            $seats = fetchAll($sql, $params);
            
            error_log("[v0] Found " . count($seats) . " seats in database");
            
            if (empty($seats)) {
                error_log("[v0] No seats found for the given seat numbers");
                return 0;
            }
            
            $releasedCount = 0;
            
            // Release each seat directly
            foreach ($seats as $seat) {
                $sql = "UPDATE chuyenxe_ghe 
                        SET trangThai = 'Trống' 
                        WHERE maChuyenXe = ? AND maGhe = ? AND trangThai = 'Đang giữ'";
                
                $result = query($sql, [$tripId, $seat['maGhe']]);
                
                if ($result) {
                    $releasedCount++;
                    error_log("[v0] Released seat {$seat['soGhe']} (ID: {$seat['maGhe']})");
                } else {
                    error_log("[v0] Failed to release seat {$seat['soGhe']} (ID: {$seat['maGhe']})");
                }
            }
            
            error_log("[v0] Released $releasedCount out of " . count($seats) . " seats");
            return $releasedCount;
            
        } catch (Exception $e) {
            error_log("[v0] Error in releaseSeatsForTripDirect: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Heartbeat để duy trì session giữ ghế
     */
    public function heartbeat() {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['held_seats'])) {
                echo json_encode(['success' => false, 'message' => 'No seats held']);
                return;
            }

            $heldSeats = $_SESSION['held_seats'];
            
            // Check if seats are still within hold time
            if (time() > $heldSeats['expires_at']) {
                // Expired, release seats
                $this->releaseSeats();
                return;
            }
            
            // Update last heartbeat time
            $_SESSION['held_seats']['last_heartbeat'] = time();
            
            echo json_encode(['success' => true, 'message' => 'Heartbeat received']);
            
        } catch (Exception $e) {
            error_log("PaymentController heartbeat error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Heartbeat error']);
        }
    }

    /**
     * Lấy danh sách khuyến mãi đang hoạt động
     */
    private function getActivePromotions() {
        try {
            $currentDate = date('Y-m-d');
            $sql = "SELECT * FROM khuyenmai 
                    WHERE ngayBatDau <= ? AND ngayKetThuc >= ? 
                    ORDER BY tenKhuyenMai ASC";
            
            $allPromotions = fetchAll($sql, [$currentDate, $currentDate]);
            
            // Only show promotions to loyal customers if required
            if (!empty($allPromotions) && isset($_SESSION['user_id'])) {
                $isLoyalCustomer = $this->isLoyalCustomer($_SESSION['user_id']);
                
                $filteredPromotions = [];
                foreach ($allPromotions as $promotion) {
                    // "Tất cả" = show to everyone
                    // "Khách hàng thân thiết" = only show to loyal customers (≥5000 points from purchases)
                    if ($promotion['doiTuongApDung'] === 'Tất cả' || 
                        ($promotion['doiTuongApDung'] === 'Khách hàng thân thiết' && $isLoyalCustomer)) {
                        $promotion['has_used'] = $this->checkUserUsedPromotion($_SESSION['user_id'], $promotion['maKhuyenMai']);
                        $filteredPromotions[] = $promotion;
                    }
                }
                return $filteredPromotions;
            }
            
            // For non-logged-in users, show only "Tất cả" promotions
            return array_filter($allPromotions, function($p) {
                return $p['doiTuongApDung'] === 'Tất cả';
            });

        } catch (Exception $e) {
            error_log("getActivePromotions error: " . $e->getMessage());
            return [];
        }
    }

    private function checkUserUsedPromotion($userId, $promotionId) {
        try {
            $sql = "SELECT COUNT(*) as usage_count FROM khuyenmai_sudung 
                    WHERE maKhuyenMai = ? AND maNguoiDung = ? AND trangThai = 'SuDung'";
            $result = fetch($sql, [$promotionId, $userId]);
            $userUsage = (int)$result['usage_count'];
            
            // Lấy thông tin khuyến mãi để kiểm tra số lần tối đa
            $promotion = $this->getPromotionById($promotionId);
            if (!$promotion) {
                return false;
            }
            
            return $userUsage >= $promotion['soLanSuDungToiDaMotNguoiDung'];
        } catch (Exception $e) {
            error_log("[v0] checkUserUsedPromotion error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * New method to check if user is a loyal customer
     * Loyal customer = total points from purchases (MuaVe source) >= 5000
     */
    private function isLoyalCustomer($userId) {
        try {
            $sql = "SELECT COALESCE(SUM(diem), 0) as total_loyalty_points 
                    FROM diem_tichluy 
                    WHERE maNguoiDung = ? AND nguon = 'MuaVe'";
            
            $result = fetch($sql, [$userId]);
            $totalPoints = (int)$result['total_loyalty_points'];
            
            error_log("[v0] User $userId loyalty check - Total points from purchases: $totalPoints (Loyal: " . ($totalPoints >= 5000 ? 'yes' : 'no') . ")");
            
            return $totalPoints >= 5000;

        } catch (Exception $e) {
            error_log("isLoyalCustomer error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy thông tin khuyến mãi theo ID
     */
    private function getPromotionById($promotionId) {
        try {
            $sql = "SELECT * FROM khuyenmai WHERE maKhuyenMai = ?";
            return fetch($sql, [$promotionId]);

        } catch (Exception $e) {
            error_log("getPromotionById error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Lấy điểm tích lũy của người dùng
     */
    private function getUserPoints($userId) {
        try {
            $sql = "SELECT COALESCE(SUM(diem), 0) as total_points 
                    FROM diem_tichluy 
                    WHERE maNguoiDung = ?";
            
            $result = fetch($sql, [$userId]);
            return (int)$result['total_points'];

        } catch (Exception $e) {
            error_log("getUserPoints error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Tính toán giá cuối cùng
     */
    private function calculatePricing($bookingData) {
        $originalPrice = $bookingData['total_price'];
        $promotionDiscount = 0;
        $pointsDiscount = 0;
        $finalPrice = $originalPrice;

        // Áp dụng khuyến mãi
        if (isset($_SESSION['applied_promotion'])) {
            $promotion = $_SESSION['applied_promotion'];
            if ($promotion['loai'] === 'PhanTram') {
                $promotionDiscount = $originalPrice * ($promotion['giaTri'] / 100);
            } else {
                $promotionDiscount = $promotion['giaTri'];
            }
        }

        // Áp dụng điểm tích lũy (1 điểm = 100đ)
        if (isset($_SESSION['used_points'])) {
            $pointsDiscount = $_SESSION['used_points'] * 100;
        }

        $totalDiscount = $promotionDiscount + $pointsDiscount;
        $finalPrice = max(0, $originalPrice - $totalDiscount);

        // Tính điểm tích lũy nhận được (0.03% tổng tiền gốc)
        $earnedPoints = floor($originalPrice * 0.0003);

        return [
            'original_price' => $originalPrice,
            'promotion_discount' => $promotionDiscount,
            'points_discount' => $pointsDiscount,
            'discount' => $totalDiscount,
            'final_price' => $finalPrice,
            'earned_points' => $earnedPoints
        ];
    }

    /**
     * Tạo chi tiết đặt vé cho một chuyến - Fixed to use existing booking ID
     */
    private function createBookingDetailFixed($bookingId, $tripData, $status = 'DaThanhToan') {
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
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                
                query($sql, [
                    $bookingId, // Use the main booking ID, not a separate detail ID
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
        
        require_once __DIR__ . '/SeatController.php';
        $seatController = new SeatController();
        
        // Cập nhật trạng thái ghế chuyến đi
        $this->updateSeatsStatus($heldSeats['trip_id'], $heldSeats['selected_seats'], 'Đã đặt');
        
        // Cập nhật trạng thái ghế chuyến về
        if (!empty($heldSeats['return_trip_id']) && !empty($heldSeats['return_selected_seats'])) {
            $this->updateSeatsStatus($heldSeats['return_trip_id'], $heldSeats['return_selected_seats'], 'Đã đặt');
        }
    }

    /**
     * Cập nhật trạng thái ghế - Fixed version
     */
    private function updateSeatsStatus($tripId, $seatNumbers, $status) {
        try {
            error_log("[v0] updateSeatsStatus called - Trip: $tripId, Status: $status, Seats: " . implode(',', $seatNumbers));
            
            // Lấy thông tin phương tiện
            $sql = "SELECT maPhuongTien FROM chuyenxe WHERE maChuyenXe = ?";
            $trip = fetch($sql, [$tripId]);
            
            if (!$trip) {
                error_log("[v0] Trip not found: $tripId");
                return false;
            }

            $vehicleId = $trip['maPhuongTien'];
            error_log("[v0] Vehicle ID: $vehicleId");
            
            // Lấy ID ghế từ số ghế
            $placeholders = str_repeat('?,', count($seatNumbers) - 1) . '?';
            $sql = "SELECT maGhe, soGhe FROM ghe WHERE maPhuongTien = ? AND soGhe IN ($placeholders)";
            $params = array_merge([$vehicleId], $seatNumbers);
            $seats = fetchAll($sql, $params);
            
            error_log("[v0] Found " . count($seats) . " seats in database");
            
            // Cập nhật trạng thái từng ghế
            foreach ($seats as $seat) {
                // Kiểm tra xem bản ghi đã tồn tại chưa
                $checkSql = "SELECT COUNT(*) as count FROM chuyenxe_ghe WHERE maChuyenXe = ? AND maGhe = ?";
                $result = fetch($checkSql, [$tripId, $seat['maGhe']]);
                
                if ($result['count'] > 0) {
                    if ($status === 'Đã đặt') {
                        $updateSql = "UPDATE chuyenxe_ghe SET trangThai = ?, ngayCapNhat = NOW() WHERE maChuyenXe = ? AND maGhe = ?";
                    } else {
                        // For holds, don't update ngayCapNhat to preserve the original hold time
                        $updateSql = "UPDATE chuyenxe_ghe SET trangThai = ? WHERE maChuyenXe = ? AND maGhe = ?";
                    }
                    $updateResult = query($updateSql, [$status, $tripId, $seat['maGhe']]);
                    error_log("[v0] Updated seat {$seat['soGhe']} (ID: {$seat['maGhe']}) to status: $status");
                } else {
                    $insertSql = "INSERT INTO chuyenxe_ghe (maChuyenXe, maGhe, trangThai, ngayTao) VALUES (?, ?, ?, NOW())";
                    $insertResult = query($insertSql, [$tripId, $seat['maGhe'], $status]);
                    error_log("[v0] Created new seat record {$seat['soGhe']} (ID: {$seat['maGhe']}) with status: $status");
                }
            }
            
            return true;

        } catch (Exception $e) {
            error_log("[v0] updateSeatsStatus error: " . $e->getMessage());
            return false;
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
        unset($_SESSION['booking_outbound_trip']);
        unset($_SESSION['booking_errors']);
        unset($_SESSION['booking_data']);
        unset($_SESSION['pending_booking_id']);
    }

    /**
     * Kiểm tra xem người dùng có thể sử dụng khuyến mãi không
     * New method to check promotion usage limits
     */
    private function checkPromotionUsage($userId, $promotionId) {
        try {
            $promotion = $this->getPromotionById($promotionId);
            
            if (!$promotion) {
                return [
                    'can_use' => false,
                    'message' => 'Mã khuyến mãi không tồn tại'
                ];
            }

            // Kiểm tra số lần sử dụng tối đa toàn bộ
            $totalUsageSql = "SELECT COUNT(*) as usage_count FROM khuyenmai_sudung 
                              WHERE maKhuyenMai = ? AND trangThai = 'SuDung'";
            $totalUsageResult = fetch($totalUsageSql, [$promotionId]);
            $totalUsage = (int)$totalUsageResult['usage_count'];

            if ($totalUsage >= $promotion['soLanSuDungToiDa']) {
                return [
                    'can_use' => false,
                    'message' => 'Mã khuyến mãi đã hết lượt sử dụng'
                ];
            }

            // Kiểm tra số lần sử dụng tối đa với một người dùng
            $userUsageSql = "SELECT COUNT(*) as usage_count FROM khuyenmai_sudung 
                             WHERE maKhuyenMai = ? AND maNguoiDung = ? AND trangThai = 'SuDung'";
            $userUsageResult = fetch($userUsageSql, [$promotionId, $userId]);
            $userUsage = (int)$userUsageResult['usage_count'];

            if ($userUsage >= $promotion['soLanSuDungToiDaMotNguoiDung']) {
                return [
                    'can_use' => false,
                    'message' => 'Bạn đã sử dụng hết lần cho mã khuyến mãi này. Mỗi mã chỉ được sử dụng ' . $promotion['soLanSuDungToiDaMotNguoiDung'] . ' lần.'
                ];
            }

            return [
                'can_use' => true,
                'message' => 'OK'
            ];

        } catch (Exception $e) {
            error_log("[v0] checkPromotionUsage error: " . $e->getMessage());
            return [
                'can_use' => false,
                'message' => 'Lỗi kiểm tra mã khuyến mãi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Ghi lại lịch sử sử dụng khuyến mãi
     * Record promotion usage to khuyenmai_sudung table when payment is successful
     */
    public function recordPromotionUsage($userId, $promotionId, $bookingId, $discountAmount) {
        try {
            error_log("[v0] recordPromotionUsage called - User: $userId, Promotion: $promotionId, Booking: $bookingId, Discount: $discountAmount");
            
            // Kiểm tra xem bản ghi đã tồn tại chưa
            $checkSql = "SELECT * FROM khuyenmai_sudung 
                        WHERE maNguoiDung = ? AND maKhuyenMai = ? AND maDatVe = ?";
            $existing = fetch($checkSql, [$userId, $promotionId, $bookingId]);
            
            if ($existing) {
                error_log("[v0] Promotion usage record already exists");
                return true;
            }
            
            // Thêm bản ghi sử dụng khuyến mãi
            $sql = "INSERT INTO khuyenmai_sudung (maNguoiDung, maKhuyenMai, maDatVe, soTienGiam, trangThai, thoiGianSuDung) 
                    VALUES (?, ?, ?, ?, 'SuDung', NOW())";
            
            query($sql, [$userId, $promotionId, $bookingId, $discountAmount]);
            
            error_log("[v0] Promotion usage recorded successfully");
            return true;
            
        } catch (Exception $e) {
            error_log("[v0] recordPromotionUsage error: " . $e->getMessage());
            // Don't throw exception here - payment should not fail just because recording usage failed
            return false;
        }
    }
}
?>
