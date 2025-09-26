<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../models/Trip.php';
require_once __DIR__ . '/../models/TripSearch.php';
require_once __DIR__ . '/../models/Vehicle.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class BookingController {
    
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }


    
    
    /**
     * Show booking page for selected trip
     */
    public function show($tripId) {
        try {
            error_log("[v0] BookingController show called with tripId: $tripId");
            
            $returnTripId = $_GET['return_trip'] ?? null;
            $isRoundTrip = isset($_GET['is_round_trip']) && $_GET['is_round_trip'] == '1';
            
            error_log("[v0] Return trip ID: $returnTripId, Is round trip: " . ($isRoundTrip ? 'yes' : 'no'));
            
            // Get outbound trip details
            $outboundTrip = $this->getTripDetails($tripId);
            if (!$outboundTrip) {
                error_log("[v0] Outbound trip not found for ID: $tripId");
                echo "<h1>Lỗi: Không tìm thấy chuyến xe ID: $tripId</h1>";
                return;
            }
            
            $returnTrip = null;
            if ($isRoundTrip && $returnTripId) {
                $returnTrip = $this->getTripDetails($returnTripId);
                if (!$returnTrip) {
                    error_log("[v0] Return trip not found for ID: $returnTripId");
                    echo "<h1>Lỗi: Không tìm thấy chuyến về ID: $returnTripId</h1>";
                    return;
                }
            }
            
            // Use outbound trip for main display (backward compatibility)
            $trip = $outboundTrip;
            
            $routeId = $trip['maTuyenDuong'] ?? null;
            
            $pickupPoints = [];
            $dropoffPoints = [];
            
            if ($routeId) {
                $pickupPoints = $this->getTripPoints($routeId, 'Đón');
                $dropoffPoints = $this->getTripPoints($routeId, 'Trả');
            }
            
            $returnPickupPoints = [];
            $returnDropoffPoints = [];
            if ($returnTrip) {
                $returnRouteId = $returnTrip['maTuyenDuong'] ?? null;
                if ($returnRouteId) {
                    $returnPickupPoints = $this->getTripPoints($returnRouteId, 'Đón');
                    $returnDropoffPoints = $this->getTripPoints($returnRouteId, 'Trả');
                }
            }
            
            error_log("[v0] Route ID: $routeId, Pickup points: " . count($pickupPoints) . ", Dropoff points: " . count($dropoffPoints));
            
            $seatLayout = $this->getSeatLayout($trip);
            $bookedSeats = $this->getBookedSeats($tripId);
            $seatStatuses = $this->getSeatStatuses($tripId);
            
            $returnSeatLayout = null;
            $returnBookedSeats = [];
            $returnSeatStatuses = [];
            if ($returnTrip) {
                $returnSeatLayout = $this->getSeatLayout($returnTrip);
                $returnBookedSeats = $this->getBookedSeats($returnTripId);
                $returnSeatStatuses = $this->getSeatStatuses($returnTripId);
            }
            
            error_log("[v0] Seat layout: " . json_encode($seatLayout));
            error_log("[v0] Booked seats: " . json_encode($bookedSeats));
            
            $bookingType = $isRoundTrip ? 'round' : 'single';
            
            $viewData = compact(
                'trip', 'outboundTrip', 'returnTrip',
                'seatLayout', 'bookedSeats', 'seatStatuses',
                'returnSeatLayout', 'returnBookedSeats', 'returnSeatStatuses',
                'pickupPoints', 'dropoffPoints',
                'returnPickupPoints', 'returnDropoffPoints',
                'isRoundTrip', 'bookingType'
            );
            extract($viewData);
            
            $viewFile = __DIR__ . '/../views/booking/show.php';
            if (file_exists($viewFile)) {
                include $viewFile;
            } else {
                echo "<h1>Lỗi: Không tìm thấy file view: $viewFile</h1>";
                echo "<h2>Thông tin chuyến xe:</h2>";
                echo "<pre>" . print_r($trip, true) . "</pre>";
            }
            
        } catch (Exception $e) {
            error_log("[v0] BookingController show error: " . $e->getMessage());
            error_log("[v0] Stack trace: " . $e->getTraceAsString());
            
            echo "<h1>Lỗi trong BookingController::show()</h1>";
            echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
            echo "<p><strong>File:</strong> " . $e->getFile() . " (Line: " . $e->getLine() . ")</p>";
            echo "<h3>Stack Trace:</h3>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        }
    }
    
    /**
     * Process booking form submission - UPDATED for new payment flow
     */
    public function process() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/search');
            exit;
        }
        
        try {
            error_log("[v0] BookingController::process() started");
            error_log("[v0] POST data: " . json_encode($_POST));
            
            unset($_SESSION['applied_promotion']);
            unset($_SESSION['used_points']);
            error_log("[v0] Cleared old promotion and points session data");
            
            $tripId = $_POST['trip_id'] ?? '';
            $selectedSeats = $_POST['selected_seats'] ?? [];
            $pickupPoint = $_POST['pickup_point'] ?? '';
            $dropoffPoint = $_POST['dropoff_point'] ?? '';
            $passengers = $_POST['passengers'] ?? [];
            $bookingType = $_POST['booking_type'] ?? 'outbound';
            
            error_log("[v0] Trip ID: $tripId, Selected seats: " . json_encode($selectedSeats));
            
            if (is_string($selectedSeats)) {
                $selectedSeats = json_decode($selectedSeats, true) ?? [];
            }
            
            // Handle return trip data for round trip
            $returnTripId = $_POST['return_trip_id'] ?? null;
            $returnSelectedSeats = $_POST['return_selected_seats'] ?? [];
            $returnPickupPoint = $_POST['return_pickup_point'] ?? '';
            $returnDropoffPoint = $_POST['return_dropoff_point'] ?? '';
            $returnPassengers = $_POST['return_passengers'] ?? [];
            $isRoundTrip = isset($_POST['is_round_trip']) && $_POST['is_round_trip'] == '1';
            
            if (is_string($returnSelectedSeats)) {
                $returnSelectedSeats = json_decode($returnSelectedSeats, true) ?? [];
            }

            error_log("[v0] Is round trip: " . ($isRoundTrip ? 'yes' : 'no'));
            error_log("[v0] Return trip ID: " . ($returnTripId ?? 'none'));

            $errors = [];
            
            if (empty($tripId)) {
                $errors[] = 'Không tìm thấy thông tin chuyến xe.';
            }
            
            if (empty($selectedSeats)) {
                $errors[] = 'Vui lòng chọn ít nhất một ghế.';
            }
            
            if (empty($pickupPoint)) {
                $errors[] = 'Vui lòng chọn điểm đón.';
            }
            
            if (empty($dropoffPoint)) {
                $errors[] = 'Vui lòng chọn điểm trả.';
            }
            
            if (empty($passengers)) {
                $errors[] = 'Vui lòng nhập thông tin hành khách.';
            }
            
            // Check return trip data if round trip
            if ($isRoundTrip && $returnTripId) {
                if (empty($returnSelectedSeats)) {
                    $errors[] = 'Vui lòng chọn ghế cho chuyến về.';
                }
                if (empty($returnPickupPoint)) {
                    $errors[] = 'Vui lòng chọn điểm đón cho chuyến về.';
                }
                if (empty($returnDropoffPoint)) {
                    $errors[] = 'Vui lòng chọn điểm trả cho chuyến về.';
                }
                if (empty($returnPassengers)) {
                    $errors[] = 'Vui lòng nhập thông tin hành khách cho chuyến về.';
                }
            }
            
            if (!empty($errors)) {
                error_log("[v0] Validation errors: " . json_encode($errors));
                $_SESSION['booking_errors'] = $errors;
                $_SESSION['booking_data'] = $_POST;
                $redirectUrl = $isRoundTrip ? 
                    BASE_URL . '/booking/' . $tripId . '?return_trip=' . $returnTripId . '&is_round_trip=1' :
                    BASE_URL . '/booking/' . $tripId;
                error_log("[v0] Redirecting back to booking due to errors: $redirectUrl");
                header('Location: ' . $redirectUrl);
                exit;
            }
            
            $trip = Trip::getById($tripId);
            if (!$trip) {
                error_log("[v0] Trip not found: $tripId");
                $_SESSION['error'] = 'Không tìm thấy chuyến xe.';
                header('Location: ' . BASE_URL . '/search');
                exit;
            }
            
            $totalPrice = count($selectedSeats) * $trip['giaVe'];
            error_log("[v0] Outbound total price: $totalPrice");
            
            $bookingData = [
                'outbound' => [
                    'trip_id' => $tripId,
                    'trip_details' => $trip,
                    'selected_seats' => $selectedSeats,
                    'pickup_point' => $pickupPoint,
                    'dropoff_point' => $dropoffPoint,
                    'passengers' => $passengers,
                    'total_price' => $totalPrice
                ],
                'return' => null,
                'total_price' => $totalPrice,
                'booking_type' => $isRoundTrip ? 'round_trip' : 'one_way'
            ];
            
            // Add return trip data if round trip
            if ($isRoundTrip && $returnTripId) {
                $returnTrip = Trip::getById($returnTripId);
                if ($returnTrip) {
                    $returnTotalPrice = count($returnSelectedSeats) * $returnTrip['giaVe'];
                    $bookingData['return'] = [
                        'trip_id' => $returnTripId,
                        'trip_details' => $returnTrip,
                        'selected_seats' => $returnSelectedSeats,
                        'pickup_point' => $returnPickupPoint,
                        'dropoff_point' => $returnDropoffPoint,
                        'passengers' => $returnPassengers,
                        'total_price' => $returnTotalPrice
                    ];
                    $bookingData['total_price'] += $returnTotalPrice;
                    error_log("[v0] Return total price: $returnTotalPrice, Grand total: " . $bookingData['total_price']);
                }
            }
            
            $holdResult = $this->holdSeatsForPayment($bookingData);
            if (!$holdResult['success']) {
                error_log("[v0] Failed to hold seats: " . $holdResult['message']);
                $_SESSION['error'] = $holdResult['message'];
                $redirectUrl = $isRoundTrip ? 
                    BASE_URL . '/booking/' . $tripId . '?return_trip=' . $returnTripId . '&is_round_trip=1' :
                    BASE_URL . '/booking/' . $tripId;
                header('Location: ' . $redirectUrl);
                exit;
            }
            
            $_SESSION['final_booking_data'] = $bookingData;
            error_log("[v0] Stored booking data in session");
            
            error_log("[v0] Successfully created booking session and held seats, redirecting to payment");
            error_log("[v0] Session data - final_booking_data exists: " . (isset($_SESSION['final_booking_data']) ? 'yes' : 'no'));
            error_log("[v0] Session data - held_seats exists: " . (isset($_SESSION['held_seats']) ? 'yes' : 'no'));
            
            header('Location: ' . BASE_URL . '/payment');
            exit;
            
        } catch (Exception $e) {
            error_log("[v0] BookingController process error: " . $e->getMessage());
            error_log("[v0] Stack trace: " . $e->getTraceAsString());
            $_SESSION['error'] = 'Có lỗi xảy ra khi xử lý đặt vé: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/search');
            exit;
        }
    }
    
    /**
     * New method to hold seats when going to payment - Enhanced with error handling
     */
    private function holdSeatsForPayment($bookingData) {
        try {
            // Prepare data for seat holding
            $holdData = [
                'trip_id' => $bookingData['outbound']['trip_id'],
                'selected_seats' => $bookingData['outbound']['selected_seats'],
                'return_trip_id' => null,
                'return_selected_seats' => []
            ];
            
            if ($bookingData['return']) {
                $holdData['return_trip_id'] = $bookingData['return']['trip_id'];
                $holdData['return_selected_seats'] = $bookingData['return']['selected_seats'];
            }
            
            // Actually hold the seats in database
            $result = $this->holdSeatsInDatabase($holdData);
            
            if ($result['success']) {
                // Store session data only if database operation succeeded
                $_SESSION['held_seats'] = [
                    'trip_id' => $holdData['trip_id'],
                    'selected_seats' => $holdData['selected_seats'],
                    'return_trip_id' => $holdData['return_trip_id'],
                    'return_selected_seats' => $holdData['return_selected_seats'],
                    'hold_time' => time(),
                    'expires_at' => time() + (10 * 60) // 10 minutes
                ];
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("holdSeatsForPayment error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Không thể giữ ghế. Vui lòng thử lại.'];
        }
    }
    
    /**
     * Hold seats in database - Enhanced with better error handling
     */
    private function holdSeatsInDatabase($holdData) {
        try {
            query("START TRANSACTION");
            
            // Hold outbound seats
            $outboundResult = $this->holdSeatsForTrip($holdData['trip_id'], $holdData['selected_seats']);
            if (!$outboundResult['success']) {
                query("ROLLBACK");
                return $outboundResult;
            }
            
            // Hold return seats if exists
            if ($holdData['return_trip_id'] && !empty($holdData['return_selected_seats'])) {
                $returnResult = $this->holdSeatsForTrip($holdData['return_trip_id'], $holdData['return_selected_seats']);
                if (!$returnResult['success']) {
                    query("ROLLBACK");
                    return ['success' => false, 'message' => 'Chuyến về: ' . $returnResult['message']];
                }
            }
            
            query("COMMIT");
            return ['success' => true, 'message' => 'Đã giữ ghế thành công'];
            
        } catch (Exception $e) {
            query("ROLLBACK");
            error_log("holdSeatsInDatabase error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi hệ thống khi giữ ghế'];
        }
    }
    
    /**
     * Hold seats for a specific trip - Enhanced with availability check
     */
    private function holdSeatsForTrip($tripId, $selectedSeats) {
        try {
            error_log("[v0] holdSeatsForTrip called with tripId: $tripId, seats: " . json_encode($selectedSeats));
            
            // Get vehicle ID
            $sql = "SELECT maPhuongTien FROM chuyenxe WHERE maChuyenXe = ?";
            $trip = fetch($sql, [$tripId]);
            
            if (!$trip) {
                error_log("[v0] Trip not found: $tripId");
                return ['success' => false, 'message' => "Không tìm thấy chuyến xe"];
            }

            $vehicleId = $trip['maPhuongTien'];
            error_log("[v0] Found vehicle ID: $vehicleId for trip: $tripId");

            // Get seat IDs and check availability
            $placeholders = str_repeat('?,', count($selectedSeats) - 1) . '?';
            $sql = "SELECT g.maGhe, g.soGhe, COALESCE(cxg.trangThai, 'Trống') as trangThai
                    FROM ghe g
                    LEFT JOIN chuyenxe_ghe cxg ON g.maGhe = cxg.maGhe AND cxg.maChuyenXe = ?
                    WHERE g.maPhuongTien = ? AND g.soGhe IN ($placeholders)";
            $params = array_merge([$tripId, $vehicleId], $selectedSeats);
            $seats = fetchAll($sql, $params);
            
            error_log("[v0] Found " . count($seats) . " seats in database for " . count($selectedSeats) . " requested seats");

            if (count($seats) !== count($selectedSeats)) {
                error_log("[v0] Seat count mismatch - requested: " . count($selectedSeats) . ", found: " . count($seats));
                return ['success' => false, 'message' => 'Một số ghế không tồn tại'];
            }

            // Check if any seat is already booked or held
            foreach ($seats as $seat) {
                error_log("[v0] Checking seat {$seat['soGhe']} (ID: {$seat['maGhe']}) - status: {$seat['trangThai']}");
                if ($seat['trangThai'] !== 'Trống') {
                    error_log("[v0] Seat {$seat['soGhe']} is not available - status: {$seat['trangThai']}");
                    return ['success' => false, 'message' => "Ghế {$seat['soGhe']} đã được đặt hoặc đang được giữ"];
                }
            }

            foreach ($seats as $seat) {
                $sql = "INSERT INTO chuyenxe_ghe (maChuyenXe, maGhe, trangThai, ngayTao) 
                        VALUES (?, ?, 'Đang giữ', NOW())
                        ON DUPLICATE KEY UPDATE trangThai = 'Đang giữ', ngayTao = NOW()";
                $result = query($sql, [$tripId, $seat['maGhe']]);
                error_log("[v0] Updated seat {$seat['soGhe']} (ID: {$seat['maGhe']}) to 'Đang giữ' - result: " . ($result ? 'success' : 'failed'));
            }

            error_log("[v0] Successfully held all seats for trip $tripId");
            return ['success' => true, 'message' => 'Đã giữ ghế thành công'];

        } catch (Exception $e) {
            error_log("[v0] holdSeatsForTrip error: " . $e->getMessage());
            error_log("[v0] Stack trace: " . $e->getTraceAsString());
            return ['success' => false, 'message' => 'Lỗi khi giữ ghế: ' . $e->getMessage()];
        }
    }

    /**
     * Check seat availability before booking - New method
     */
    private function checkSeatAvailability($tripId, $selectedSeats) {
        $errors = [];
        
        try {
            // Get vehicle ID
            $sql = "SELECT maPhuongTien FROM chuyenxe WHERE maChuyenXe = ?";
            $trip = fetch($sql, [$tripId]);
            
            if (!$trip) {
                $errors[] = "Không tìm thấy chuyến xe";
                return $errors;
            }
            
            $vehicleId = $trip['maPhuongTien'];
            
            // Check each seat
            foreach ($selectedSeats as $seatNumber) {
                $sql = "SELECT g.maGhe, COALESCE(cxg.trangThai, 'Trống') as trangThai
                        FROM ghe g
                        LEFT JOIN chuyenxe_ghe cxg ON g.maGhe = cxg.maGhe AND cxg.maChuyenXe = ? 
                        WHERE g.maPhuongTien = ? AND g.soGhe = ?";
                
                $seat = fetch($sql, [$tripId, $vehicleId, $seatNumber]);
                
                if (!$seat) {
                    $errors[] = "Ghế $seatNumber không tồn tại";
                } elseif ($seat['trangThai'] !== 'Trống') {
                    $errors[] = "Ghế $seatNumber đã được đặt hoặc đang được giữ";
                }
            }
            
        } catch (Exception $e) {
            error_log("checkSeatAvailability error: " . $e->getMessage());
            $errors[] = "Lỗi khi kiểm tra tình trạng ghế";
        }
        
        return $errors;
    }


    /**
     * Show booking confirmation page - UPDATED
     */
    public function confirm() {
        if (!isset($_SESSION['final_booking_data'])) {
            $_SESSION['error'] = 'Không tìm thấy thông tin đặt vé.';
            header('Location: ' . BASE_URL . '/search');
            exit;
        }
        
        header('Location: ' . BASE_URL . '/payment');
        exit;
    }
    
    /**
     * Complete booking and create reservation - UPDATED
     */
    public function complete() {
        $_SESSION['error'] = 'Phương thức này đã được thay thế bởi hệ thống thanh toán mới.';
        header('Location: ' . BASE_URL . '/search');
        exit;
    }
    
    /**
     * Show booking success page - Enhanced with better data handling
     */
    public function success($bookingId) {
        try {
            if (!isset($_SESSION['user_id'])) {
                $_SESSION['error'] = 'Vui lòng đăng nhập để xem thông tin đặt vé!';
                header('Location: ' . BASE_URL . '/login');
                exit;
            }
            
            $booking = $this->getBookingById($bookingId);
            
            if (!$booking) {
                $_SESSION['error'] = 'Không tìm thấy thông tin đặt vé.';
                header('Location: ' . BASE_URL . '/search');
                exit;
            }
            
            $userId = $_SESSION['user_id'];
            $userRole = $_SESSION['user_role'] ?? 4;
            
            // Get booking owner from first booking record
            $bookingUserId = $booking[0]['maNguoiDung'] ?? null;
            
            if ($userId != $bookingUserId && $userRole != 1) {
                $_SESSION['error'] = 'Bạn không có quyền xem thông tin đặt vé này!';
                header('Location: ' . BASE_URL . '/');
                exit;
            }
            
            // Get booking details with detailed addresses
            $bookingDetails = $this->getBookingDetailsWithAddresses($bookingId);
            
            $viewData = compact('booking', 'bookingDetails', 'bookingId');
            extract($viewData);
            
            include __DIR__ . '/../views/booking/success.php';
            
        } catch (Exception $e) {
            error_log("BookingController success error: " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi hiển thị thông tin đặt vé.';
            header('Location: ' . BASE_URL . '/search');
            exit;
        }
    }

    /**
     * Get detailed booking information - New method
     */
    private function getBookingDetails($bookingId) {
        try {
            $sql = "SELECT d.*, 
                           cd.hoTenHanhKhach, cd.emailHanhKhach, cd.soDienThoaiHanhKhach,
                           cd.giaVe as seatPrice, g.soGhe,
                           c.ngayKhoiHanh, c.thoiGianKhoiHanh, 
                           t.kyHieuTuyen, t.diemDi, t.diemDen,
                           dd.tenDiem as diemDonTen, dt.tenDiem as diemTraTen,
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
                    WHERE d.maDatVe = ? AND d.trangThai = 'DaThanhToan'
                    ORDER BY d.ngayDat DESC, g.soGhe ASC";
            
            return fetchAll($sql, [$bookingId]);
        } catch (Exception $e) {
            error_log("getBookingDetails error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return null;
        }
    }

    /**
     * Get trip booking details - New method
     */
    private function getTripBookingDetails($detailId) {
        try {
            $sql = "SELECT d.*, c.ngayKhoiHanh, c.thoiGianKhoiHanh,
                           t.kyHieuTuyen, t.diemDi, t.diemDen,
                           dd.tenDiem as diemDonTen, dt.tenDiem as diemTraTen,
                           p.bienSo
                    FROM datve d
                    INNER JOIN chuyenxe c ON d.maChuyenXe = c.maChuyenXe
                    INNER JOIN lichtrinh l ON c.maLichTrinh = l.maLichTrinh
                    INNER JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong
                    INNER JOIN phuongtien p ON c.maPhuongTien = p.maPhuongTien
                    LEFT JOIN tuyenduong_diemdontra dd ON d.maDiemDon = dd.maDiem
                    LEFT JOIN tuyenduong_diemdontra dt ON d.maDiemTra = dt.maDiem
                    WHERE d.maDatVe = ? AND d.trangThai = 'DaThanhToan'
                    ORDER BY d.ngayDat DESC";
            
            $booking = fetch($sql, [$detailId]);
            
            if ($booking) {
                // Get passenger details
                $sql = "SELECT * FROM datve_chitiet WHERE maDatVe = ? ORDER BY ghe_so";
                $passengers = fetchAll($sql, [$detailId]);
                $booking['passengers'] = $passengers;
            }
            
            return $booking;
            
        } catch (Exception $e) {
            error_log("getTripBookingDetails error: " . $e->getMessage());
            return null;
        }
    }


    /**
     * Get trip pickup/dropoff points
     */
    private function getTripPoints($routeId, $pointType) {
        try {
            if (!$routeId) {
                error_log("[v0] No route ID provided for getTripPoints");
                return [];
            }
            
            $checkTable = "SHOW TABLES LIKE 'tuyenduong_diemdontra'";
            $tableExists = fetch($checkTable);
            
            if (!$tableExists) {
                error_log("[v0] Table tuyenduong_diemdontra does not exist");
                return [];
            }
            
            $sql = "SELECT maDiem, tenDiem, diaChi, thuTu
                    FROM tuyenduong_diemdontra 
                    WHERE maTuyenDuong = ? AND loaiDiem = ? AND trangThai = 'Hoạt động'
                    ORDER BY thuTu ASC";
            
            $points = fetchAll($sql, [$routeId, $pointType]);
            error_log("[v0] Found " . count($points) . " $pointType points for route $routeId");
            
            return $points;
        } catch (Exception $e) {
            error_log("[v0] getTripPoints error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get seat layout configuration from vehicle type
     */
    private function getSeatLayout($trip) {
        try {
            $vehicleId = $trip['maPhuongTien'] ?? null;
            if (!$vehicleId) {
                error_log("[v0] No vehicle ID found in trip data");
                return $this->getDefaultSeatLayout();
            }

            // Get actual seats for this vehicle from database
            $actualSeats = $this->getActualSeats($vehicleId);
            
            $layout = [
                'total_seats' => (int)($trip['soChoMacDinh'] ?? 20),
                'seat_type' => $trip['loaiChoNgoiMacDinh'] ?? 'Giường đôi',
                'left_columns' => (int)($trip['soCotTrai'] ?? 1),
                'right_columns' => (int)($trip['soCotPhai'] ?? 1),
                'middle_columns' => (int)($trip['soCotGiua'] ?? 0),
                'floors' => (int)($trip['soTang'] ?? 2),
                'rows_per_floor' => (int)($trip['soHang'] ?? 10),
                'vehicle_type' => $trip['tenLoaiPhuongTien'] ?? '',
                'default_seat_type' => $trip['loaiChoNgoiMacDinh'] ?? '',
                'actual_seats' => $actualSeats, // Add actual seat data
            ];
            error_log("[v0] Seat layout created with " . count($actualSeats) . " actual seats: " . json_encode($layout));
            return $layout;
        } catch (Exception $e) {
            error_log("[v0] getSeatLayout error: " . $e->getMessage());
            return $this->getDefaultSeatLayout();
        }
    }
    
    /**
     * Get actual seats from database for a vehicle
     */
    private function getActualSeats($vehicleId) {
        try {
            $sql = "SELECT maGhe, soGhe FROM ghe WHERE maPhuongTien = ? ORDER BY soGhe ASC";
            $seats = fetchAll($sql, [$vehicleId]);
            
            error_log("[v0] Found " . count($seats) . " actual seats for vehicle $vehicleId");
            return $seats;
        } catch (Exception $e) {
            error_log("[v0] getActualSeats error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get default seat layout if vehicle type not found
     */
    private function getDefaultSeatLayout() {
        return [
            'total_seats' => 20,
            'seat_type' => 'Giường đôi',
            'left_columns' => 1,
            'right_columns' => 1,
            'middle_columns' => 0,
            'floors' => 2,
            'rows_per_floor' => 10,
            'actual_seats' => [] // Add empty actual seats for default
        ];
    }
    
    /**
     * Get booked seats for a trip with their status
     */
    private function getBookedSeats($tripId) {
        try {
            $sql = "SELECT cxg.maGhe, g.soGhe, cxg.trangThai 
                    FROM chuyenxe_ghe cxg
                    INNER JOIN ghe g ON cxg.maGhe = g.maGhe
                    WHERE cxg.maChuyenXe = ? AND cxg.trangThai != 'Trống'";
            
            $result = fetchAll($sql, [$tripId]);
            
            $bookedSeats = [];
            foreach ($result as $seat) {
                if (in_array($seat['trangThai'], ['Đã đặt', 'Đang giữ'])) {
                    $bookedSeats[] = $seat['soGhe'];
                }
            }
            
            error_log("[v0] Found " . count($bookedSeats) . " booked/held seats for trip $tripId: " . json_encode($bookedSeats));
            return $bookedSeats;
        } catch (Exception $e) {
            error_log("[v0] getBookedSeats error: " . $e->getMessage());
            // Return empty array if tables don't exist yet
            return [];
        }
    }
    
    private function getSeatStatuses($tripId) {
        try {
            $sql = "SELECT cxg.maGhe, g.soGhe, cxg.trangThai 
                    FROM chuyenxe_ghe cxg
                    INNER JOIN ghe g ON cxg.maGhe = g.maGhe
                    WHERE cxg.maChuyenXe = ? AND cxg.trangThai != 'Trống'";
            
            $result = fetchAll($sql, [$tripId]);
            
            $seatStatuses = [];
            foreach ($result as $seat) {
                $seatStatuses[$seat['soGhe']] = $seat['trangThai']; // Map seat number to status
            }
            
            error_log("[v0] Found " . count($seatStatuses) . " seat statuses for trip $tripId: " . json_encode($seatStatuses));
            return $seatStatuses;
        } catch (Exception $e) {
            error_log("[v0] getSeatStatuses error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Validate booking form data
     */
    private function validateBookingData($tripId, $selectedSeats, $pickupPoint, $dropoffPoint, $passengers) {
        $errors = [];
        
        if (empty($tripId)) {
            $errors[] = 'Không tìm thấy thông tin chuyến xe.';
        }
        
        if (empty($selectedSeats)) {
            $errors[] = 'Vui lòng chọn ít nhất một ghế.';
        }
        
        if (empty($pickupPoint)) {
            $errors[] = 'Vui lòng chọn điểm đón.';
        }
        
        if (empty($dropoffPoint)) {
            $errors[] = 'Vui lòng chọn điểm trả.';
        }
        
        if (empty($passengers)) {
            $errors[] = 'Vui lòng nhập thông tin hành khách.';
        } else {
            $phoneCount = 0;
            $cccdList = [];
            
            foreach ($passengers as $index => $passenger) {
                $passengerNum = $index + 1;
                
                if (empty($passenger['ho_ten'])) {
                    $errors[] = "Vui lòng nhập họ tên hành khách thứ $passengerNum.";
                }
                
                if (empty($passenger['cccd'])) {
                    $errors[] = "Vui lòng nhập CCCD hành khách thứ $passengerNum.";
                } else {
                    if (in_array($passenger['cccd'], $cccdList)) {
                        $errors[] = "CCCD của hành khách thứ $passengerNum đã được sử dụng.";
                    }
                    $cccdList[] = $passenger['cccd'];
                }
                
                if (!empty($passenger['so_dien_thoai'])) {
                    $phoneCount++;
                }
            }
            
            if ($phoneCount === 0) {
                $errors[] = 'Ít nhất một hành khách phải có số điện thoại.';
            }
        }
        
        return $errors;
    }
    
    /**
     * Create booking record in database
     */
    private function createBooking($bookingData) {
        try {
            query("START TRANSACTION");
            
            $bookingId = 'BK' . date('YmdHis') . rand(100, 999);
            
            if ($bookingData['outbound']) {
                $this->createSingleBooking($bookingId . '_OUT', $bookingData['outbound']);
            }
            
            if ($bookingData['return']) {
                $this->createSingleBooking($bookingId . '_RET', $bookingData['return']);
            }
            
            query("COMMIT");
            
            return $bookingId;
            
        } catch (Exception $e) {
            query("ROLLBACK");
            error_log("createBooking error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }
    
    /**
     * Create single trip booking
     */
    private function createSingleBooking($bookingId, $tripData) {
        $sql = "INSERT INTO datve (maDatVe, maChuyenXe, maDiemDon, maDiemTra, tongTien, trangThai, ngayDat)
                VALUES (?, ?, ?, ?, ?, 'Đã đặt', NOW())";
        
        query($sql, [
            $bookingId,
            $tripData['trip_id'],
            $tripData['pickup_point'],
            $tripData['dropoff_point'],
            $tripData['total_price']
        ]);
        
        foreach ($tripData['passengers'] as $index => $passenger) {
            $seatNumber = $tripData['selected_seats'][$index];
            
            $sql = "INSERT INTO datve_chitiet (maDatVe, ghe_so, ho_ten, cccd, so_dien_thoai)
                    VALUES (?, ?, ?, ?, ?)";
            
            query($sql, [
                $bookingId,
                $seatNumber,
                $passenger['ho_ten'],
                $passenger['cccd'],
                $passenger['so_dien_thoai'] ?? null
            ]);
        }
        
        $this->updateTripSeats($tripData['trip_id'], count($tripData['selected_seats']));
    }
    
    /**
     * Update trip seat counts
     */
    private function updateTripSeats($tripId, $bookedSeats) {
        $sql = "UPDATE chuyenxe 
                SET soChoDaDat = soChoDaDat + ?, 
                    soChoTrong = soChoTrong - ? 
                WHERE maChuyenXe = ?";
        
        query($sql, [$bookedSeats, $bookedSeats, $tripId]);
    }
 
    
    private function getBookingDetailsWithAddresses($bookingId) {
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
                    WHERE d.maDatVe = ? AND d.trangThai = 'DaThanhToan'
                    ORDER BY d.ngayDat DESC, g.soGhe ASC";
            
            return fetchAll($sql, [$bookingId]);
        } catch (Exception $e) {
            error_log("getBookingDetailsWithAddresses error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return null;
        }
    }

    /**
     * Get booking by ID - Enhanced with user information for access control
     */
    private function getBookingById($bookingId) {
        try {
            $sql = "SELECT d.*, c.ngayKhoiHanh, c.thoiGianKhoiHanh, 
                           t.kyHieuTuyen, t.diemDi, t.diemDen,
                           dd.tenDiem as diemDonTen, dd.diaChi as diemDonDiaChi,
                           dt.tenDiem as diemTraTen, dt.diaChi as diemTraDiaChi
                    FROM datve d
                    INNER JOIN chitiet_datve cd ON d.maDatVe = cd.maDatVe
                    INNER JOIN chuyenxe c ON cd.maChuyenXe = c.maChuyenXe
                    INNER JOIN lichtrinh l ON c.maLichTrinh = l.maLichTrinh
                    INNER JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong
                    INNER JOIN phuongtien p ON c.maPhuongTien = p.maPhuongTien
                    LEFT JOIN tuyenduong_diemdontra dd ON cd.maDiemDon = dd.maDiem
                    LEFT JOIN tuyenduong_diemdontra dt ON cd.maDiemTra = dt.maDiem
                    WHERE d.maDatVe = ? AND d.trangThai = 'DaThanhToan'
                    ORDER BY d.ngayDat DESC";
            
            return fetchAll($sql, [$bookingId]);
        } catch (Exception $e) {
            error_log("getBookingById error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return null;
        }
    }
    
    /**
     * Get trip details by ID
     */
    private function getTripDetails($tripId) {
        $sql = "SELECT c.*, 
                       l.tenLichTrinh, l.gioKhoiHanh, l.gioKetThuc, l.maTuyenDuong,
                       t.kyHieuTuyen, t.diemDi, t.diemDen, t.thoiGianDiChuyen,
                       p.bienSo, p.maLoaiPhuongTien,
                       lpt.tenLoaiPhuongTien, lpt.soChoMacDinh,
                       lpt.soTang, lpt.soHang, lpt.soCotTrai, lpt.soCotGiua, lpt.soCotPhai,
                       lpt.loaiChoNgoiMacDinh,
                       gv.giaVe, 
                       lv.tenLoaiVe
                FROM chuyenxe c
                LEFT JOIN lichtrinh l ON c.maLichTrinh = l.maLichTrinh
                LEFT JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong
                LEFT JOIN phuongtien p ON c.maPhuongTien = p.maPhuongTien
                LEFT JOIN loaiphuongtien lpt ON p.maLoaiPhuongTien = lpt.maLoaiPhuongTien
                LEFT JOIN giave gv ON c.maGiaVe = gv.maGiaVe
                LEFT JOIN loaive lv ON gv.maLoaiVe = lv.maLoaiVe
                WHERE c.maChuyenXe = ?";
        
        $result = fetch($sql, [$tripId]);
        error_log("[v0] getTripDetails for trip $tripId - loaiChoNgoiMacDinh: " . ($result['loaiChoNgoiMacDinh'] ?? 'NULL'));
        error_log("[v0] Full trip details: " . json_encode($result));
        
        return $result;
    }
}
?>
