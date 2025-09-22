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
            
            $returnSeatLayout = null;
            $returnBookedSeats = [];
            if ($returnTrip) {
                $returnSeatLayout = $this->getSeatLayout($returnTrip);
                $returnBookedSeats = $this->getBookedSeats($returnTripId);
            }
            
            error_log("[v0] Seat layout: " . json_encode($seatLayout));
            error_log("[v0] Booked seats: " . json_encode($bookedSeats));
            
            $bookingType = 'single';
            
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
     * Process booking form submission
     */
    public function process() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/search');
            exit;
        }
        
        try {
            $tripId = $_POST['trip_id'] ?? '';
            $selectedSeats = $_POST['selected_seats'] ?? [];
            $pickupPoint = $_POST['pickup_point'] ?? '';
            $dropoffPoint = $_POST['dropoff_point'] ?? '';
            $passengers = $_POST['passengers'] ?? [];
            $bookingType = $_POST['booking_type'] ?? 'outbound';
            
            $errors = $this->validateBookingData($tripId, $selectedSeats, $pickupPoint, $dropoffPoint, $passengers);
            
            if (!empty($errors)) {
                $_SESSION['booking_errors'] = $errors;
                $_SESSION['booking_data'] = $_POST;
                header('Location: ' . BASE_URL . '/booking/' . $tripId);
                exit;
            }
            
            $trip = Trip::getById($tripId);
            if (!$trip) {
                $_SESSION['error'] = 'Không tìm thấy chuyến xe.';
                header('Location: ' . BASE_URL . '/search');
                exit;
            }
            
            $totalPrice = count($selectedSeats) * $trip['giaVe'];
            
            if ($bookingType === 'outbound' && isset($_SESSION['last_search']['is_round_trip']) && $_SESSION['last_search']['is_round_trip']) {
                $_SESSION['booking_outbound_trip'] = [
                    'trip_id' => $tripId,
                    'trip_details' => $trip,
                    'selected_seats' => $selectedSeats,
                    'pickup_point' => $pickupPoint,
                    'dropoff_point' => $dropoffPoint,
                    'passengers' => $passengers,
                    'total_price' => $totalPrice
                ];
                
                $_SESSION['success'] = 'Đã chọn chuyến đi. Vui lòng chọn chuyến về.';
                
                $searchParams = $_SESSION['last_search'];
                $returnSearchUrl = BASE_URL . '/search?' . http_build_query([
                    'from' => $searchParams['to'],
                    'to' => $searchParams['from'],
                    'departure_date' => $searchParams['return_date'],
                    'passengers' => count($selectedSeats),
                    'is_round_trip' => '1',
                    'show_return' => '1'
                ]);
                
                header('Location: ' . $returnSearchUrl);
                exit;
                
            } else {
                $bookingData = [
                    'outbound' => null,
                    'return' => null,
                    'total_price' => 0
                ];
                
                if ($bookingType === 'return' && isset($_SESSION['booking_outbound_trip'])) {
                    $bookingData['outbound'] = $_SESSION['booking_outbound_trip'];
                    $bookingData['return'] = [
                        'trip_id' => $tripId,
                        'trip_details' => $trip,
                        'selected_seats' => $selectedSeats,
                        'pickup_point' => $pickupPoint,
                        'dropoff_point' => $dropoffPoint,
                        'passengers' => $passengers,
                        'total_price' => $totalPrice
                    ];
                    $bookingData['total_price'] = $bookingData['outbound']['total_price'] + $totalPrice;
                    
                    unset($_SESSION['booking_outbound_trip']);
                } else {
                    $bookingData['outbound'] = [
                        'trip_id' => $tripId,
                        'trip_details' => $trip,
                        'selected_seats' => $selectedSeats,
                        'pickup_point' => $pickupPoint,
                        'dropoff_point' => $dropoffPoint,
                        'passengers' => $passengers,
                        'total_price' => $totalPrice
                    ];
                    $bookingData['total_price'] = $totalPrice;
                }
                
                $_SESSION['final_booking_data'] = $bookingData;
                header('Location: ' . BASE_URL . '/booking/confirm');
                exit;
            }
            
        } catch (Exception $e) {
            error_log("BookingController process error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $_SESSION['error'] = 'Có lỗi xảy ra khi xử lý đặt vé: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/search');
            exit;
        }
    }
    
    /**
     * Show booking confirmation page
     */
    public function confirm() {
        if (!isset($_SESSION['final_booking_data'])) {
            $_SESSION['error'] = 'Không tìm thấy thông tin đặt vé.';
            header('Location: ' . BASE_URL . '/search');
            exit;
        }
        
        $bookingData = $_SESSION['final_booking_data'];
        
        include __DIR__ . '/../views/booking/confirm.php';
    }
    
    /**
     * Complete booking and create reservation
     */
    public function complete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/search');
            exit;
        }
        
        if (!isset($_SESSION['final_booking_data'])) {
            $_SESSION['error'] = 'Không tìm thấy thông tin đặt vé.';
            header('Location: ' . BASE_URL . '/search');
            exit;
        }
        
        try {
            $bookingData = $_SESSION['final_booking_data'];
            
            $bookingId = $this->createBooking($bookingData);
            
            if ($bookingId) {
                unset($_SESSION['final_booking_data']);
                unset($_SESSION['booking_outbound_trip']);
                unset($_SESSION['booking_errors']);
                unset($_SESSION['booking_data']);
                
                $_SESSION['success'] = 'Đặt vé thành công! Mã đặt vé: ' . $bookingId;
                header('Location: ' . BASE_URL . '/booking/success/' . $bookingId);
                exit;
            } else {
                $_SESSION['error'] = 'Có lỗi xảy ra khi tạo đặt vé.';
                header('Location: ' . BASE_URL . '/booking/confirm');
                exit;
            }
            
        } catch (Exception $e) {
            error_log("BookingController complete error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $_SESSION['error'] = 'Có lỗi xảy ra khi hoàn tất đặt vé: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/booking/confirm');
            exit;
        }
    }
    
    /**
     * Show booking success page
     */
    public function success($bookingId) {
        $booking = $this->getBookingById($bookingId);
        
        if (!$booking) {
            $_SESSION['error'] = 'Không tìm thấy thông tin đặt vé.';
            header('Location: ' . BASE_URL . '/search');
            exit;
        }
        
        include __DIR__ . '/../views/booking/success.php';
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
            $layout = [
                'total_seats' => (int)($trip['soChoMacDinh'] ?? 20),
                'seat_type' => $trip['loaiChoNgoiMacDinh'] ?? 'Giường đôi',
                'left_columns' => (int)($trip['soCotTrai'] ?? 1),
                'right_columns' => (int)($trip['soCotPhai'] ?? 1),
                'middle_columns' => (int)($trip['soCotGiua'] ?? 0),
                'floors' => (int)($trip['soTang'] ?? 2),
                'rows_per_floor' => (int)($trip['soHang'] ?? 10),
                'vehicle_type' => $trip['tenLoaiPhuongTien'] ?? '',           // FIXED
                'default_seat_type' => $trip['loaiChoNgoiMacDinh'] ?? '',    // FIXED
            ];
            error_log("[v0] Seat layout created: " . json_encode($layout));
            return $layout;
        } catch (Exception $e) {
            error_log("[v0] getSeatLayout error: " . $e->getMessage());
            return $this->getDefaultSeatLayout();
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
            'rows_per_floor' => 10
        ];
    }
    
    /**
     * Get booked seats for a trip
     */
    private function getBookedSeats($tripId) {
        try {
            $checkTable = "SHOW TABLES LIKE 'datve'";
            $tableExists = fetch($checkTable);
            
            if (!$tableExists) {
                error_log("[v0] Booking tables do not exist yet");
                return [];
            }
            
            $sql = "SELECT ghe_so FROM datve_chitiet dc 
                    INNER JOIN datve d ON dc.maDatVe = d.maDatVe 
                    WHERE d.maChuyenXe = ? AND d.trangThai != 'Đã hủy'";
            
            $result = fetchAll($sql, [$tripId]);
            $bookedSeats = array_column($result, 'ghe_so');
            
            error_log("[v0] Found " . count($bookedSeats) . " booked seats for trip $tripId");
            return $bookedSeats;
        } catch (Exception $e) {
            error_log("[v0] getBookedSeats error: " . $e->getMessage());
            // Return empty array if booking tables don't exist yet
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
    
    /**
     * Get booking by ID
     */
    private function getBookingById($bookingId) {
        try {
            $sql = "SELECT d.*, c.ngayKhoiHanh, c.thoiGianKhoiHanh, 
                           t.kyHieuTuyen, t.diemDi, t.diemDen,
                           dd.tenDiem as diemDonTen, dt.tenDiem as diemTraTen
                    FROM datve d
                    INNER JOIN chuyenxe c ON d.maChuyenXe = c.maChuyenXe
                    INNER JOIN lichtrinh l ON c.maLichTrinh = l.maLichTrinh
                    INNER JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong
                    LEFT JOIN tuyenduong_diemdontra dd ON d.maDiemDon = dd.maDiem
                    LEFT JOIN tuyenduong_diemdontra dt ON d.maDiemTra = dt.maDiem
                    WHERE d.maDatVe LIKE ?
                    ORDER BY d.ngayDat DESC";
            
            return fetchAll($sql, [$bookingId . '%']);
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
        
        return fetch($sql, [$tripId]);
    }
}
?>
