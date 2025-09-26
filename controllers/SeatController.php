<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class SeatController {
    
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Giữ ghế trong 10 phút khi khách hàng chuyển đến trang thanh toán
     */
    public function holdSeats() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $tripId = $input['trip_id'] ?? '';
            $selectedSeats = $input['selected_seats'] ?? [];
            $returnTripId = $input['return_trip_id'] ?? null;
            $returnSelectedSeats = $input['return_selected_seats'] ?? [];

            if (empty($tripId) || empty($selectedSeats)) {
                echo json_encode(['success' => false, 'message' => 'Thiếu thông tin chuyến xe hoặc ghế']);
                return;
            }

            // Bắt đầu transaction
            query("START TRANSACTION");

            // Giữ ghế chuyến đi
            $holdResult = $this->holdSeatsForTrip($tripId, $selectedSeats);
            if (!$holdResult['success']) {
                query("ROLLBACK");
                echo json_encode($holdResult);
                return;
            }

            // Giữ ghế chuyến về nếu có
            if ($returnTripId && !empty($returnSelectedSeats)) {
                $returnHoldResult = $this->holdSeatsForTrip($returnTripId, $returnSelectedSeats);
                if (!$returnHoldResult['success']) {
                    query("ROLLBACK");
                    echo json_encode($returnHoldResult);
                    return;
                }
            }

            query("COMMIT");

            // Lưu thông tin vào session để sử dụng trong trang thanh toán
            $_SESSION['held_seats'] = [
                'trip_id' => $tripId,
                'selected_seats' => $selectedSeats,
                'return_trip_id' => $returnTripId,
                'return_selected_seats' => $returnSelectedSeats,
                'hold_time' => time(),
                'expires_at' => time() + (10 * 60) // 10 phút
            ];

            echo json_encode([
                'success' => true, 
                'message' => 'Đã giữ ghế thành công',
                'expires_at' => $_SESSION['held_seats']['expires_at']
            ]);

        } catch (Exception $e) {
            query("ROLLBACK");
            error_log("SeatController holdSeats error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Hủy giữ ghế và chuyển về trạng thái trống
     */
    public function releaseSeats() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        try {
            if (!isset($_SESSION['held_seats'])) {
                echo json_encode(['success' => false, 'message' => 'Không có ghế nào đang được giữ']);
                return;
            }

            $heldSeats = $_SESSION['held_seats'];
            
            // Bắt đầu transaction
            query("START TRANSACTION");

            // Hủy giữ ghế chuyến đi
            $releaseResult = $this->releaseSeatsForTrip($heldSeats['trip_id'], $heldSeats['selected_seats']);
            if (!$releaseResult['success']) {
                query("ROLLBACK");
                echo json_encode($releaseResult);
                return;
            }

            // Hủy giữ ghế chuyến về nếu có
            if (!empty($heldSeats['return_trip_id']) && !empty($heldSeats['return_selected_seats'])) {
                $returnReleaseResult = $this->releaseSeatsForTrip($heldSeats['return_trip_id'], $heldSeats['return_selected_seats']);
                if (!$returnReleaseResult['success']) {
                    query("ROLLBACK");
                    echo json_encode($returnReleaseResult);
                    return;
                }
            }

            query("COMMIT");

            // Xóa thông tin ghế giữ khỏi session
            unset($_SESSION['held_seats']);

            echo json_encode(['success' => true, 'message' => 'Đã hủy giữ ghế thành công']);

        } catch (Exception $e) {
            query("ROLLBACK");
            error_log("SeatController releaseSeats error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }


    /**
     * Kiểm tra và tự động hủy ghế hết hạn
     */
    public function checkExpiredSeats() {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['held_seats'])) {
                echo json_encode(['success' => true, 'expired' => false]);
                return;
            }

            $heldSeats = $_SESSION['held_seats'];
            $currentTime = time();

            if ($currentTime > $heldSeats['expires_at']) {
                // Ghế đã hết hạn, tự động hủy
                $this->releaseSeats();
                echo json_encode(['success' => true, 'expired' => true, 'message' => 'Ghế đã hết hạn giữ']);
                return;
            }

            $remainingTime = $heldSeats['expires_at'] - $currentTime;
            echo json_encode([
                'success' => true, 
                'expired' => false, 
                'remaining_time' => $remainingTime,
                'remaining_minutes' => ceil($remainingTime / 60)
            ]);

        } catch (Exception $e) {
            error_log("SeatController checkExpiredSeats error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Giữ ghế cho một chuyến xe cụ thể
     */
    private function holdSeatsForTrip($tripId, $selectedSeats) {
        try {
            // Lấy thông tin ghế từ database
            $seatIds = $this->getSeatIdsByNumbers($tripId, $selectedSeats);
            
            if (count($seatIds) !== count($selectedSeats)) {
                return ['success' => false, 'message' => 'Một số ghế không tồn tại'];
            }

            // Kiểm tra ghế có đang trống không
            foreach ($seatIds as $seatId) {
                $currentStatus = $this->getSeatStatus($tripId, $seatId);
                if ($currentStatus !== 'Trống') {
                    return ['success' => false, 'message' => 'Một số ghế đã được đặt hoặc đang được giữ'];
                }
            }

            // Cập nhật trạng thái ghế thành "Đang giữ"
            foreach ($seatIds as $seatId) {
                $this->updateSeatStatus($tripId, $seatId, 'Đang giữ');
            }

            return ['success' => true];

        } catch (Exception $e) {
            error_log("holdSeatsForTrip error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Có lỗi xảy ra khi giữ ghế'];
        }
    }

    /**
     * Hủy giữ ghế cho một chuyến xe cụ thể
     */
    private function releaseSeatsForTrip($tripId, $selectedSeats) {
        try {
            error_log("[v0] releaseSeatsForTrip called - Trip: $tripId, Seats: " . implode(',', $selectedSeats));
            
            // Lấy thông tin ghế từ database
            $seatIds = $this->getSeatIdsByNumbers($tripId, $selectedSeats);
            
            if (empty($seatIds)) {
                error_log("[v0] No seat IDs found for trip $tripId and seats " . implode(',', $selectedSeats));
                return ['success' => false, 'message' => 'Không tìm thấy ghế trong hệ thống'];
            }
            
            error_log("[v0] Found " . count($seatIds) . " seat IDs: " . implode(',', $seatIds));
            
            // Cập nhật trạng thái ghế thành "Trống"
            $updatedCount = 0;
            foreach ($seatIds as $seatId) {
                try {
                    $this->updateSeatStatus($tripId, $seatId, 'Trống');
                    $updatedCount++;
                    error_log("[v0] Updated seat ID $seatId to Trống");
                } catch (Exception $e) {
                    error_log("[v0] Failed to update seat ID $seatId: " . $e->getMessage());
                }
            }
            
            if ($updatedCount === 0) {
                return ['success' => false, 'message' => 'Không thể cập nhật trạng thái ghế'];
            }
            
            error_log("[v0] Successfully updated $updatedCount seats to Trống");
            return ['success' => true];

        } catch (Exception $e) {
            error_log("[v0] releaseSeatsForTrip error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Có lỗi xảy ra khi hủy giữ ghế: ' . $e->getMessage()];
        }
    }

    /**
     * Lấy ID ghế từ số ghế
     */
    private function getSeatIdsByNumbers($tripId, $seatNumbers) {
        try {
            error_log("[v0] getSeatIdsByNumbers called - Trip: $tripId, Seats: " . implode(',', $seatNumbers));
            
            // Lấy thông tin phương tiện của chuyến xe
            $sql = "SELECT maPhuongTien FROM chuyenxe WHERE maChuyenXe = ?";
            $trip = fetch($sql, [$tripId]);
            
            if (!$trip) {
                error_log("[v0] Trip not found: $tripId");
                throw new Exception("Không tìm thấy chuyến xe");
            }

            $vehicleId = $trip['maPhuongTien'];
            error_log("[v0] Vehicle ID: $vehicleId");
            
            // Lấy ID ghế từ số ghế
            $placeholders = str_repeat('?,', count($seatNumbers) - 1) . '?';
            $sql = "SELECT maGhe, soGhe FROM ghe WHERE maPhuongTien = ? AND soGhe IN ($placeholders)";
            $params = array_merge([$vehicleId], $seatNumbers);
            
            error_log("[v0] SQL: $sql");
            error_log("[v0] Params: " . implode(',', $params));
            
            $seats = fetchAll($sql, $params);
            
            error_log("[v0] Found " . count($seats) . " seats in database");
            
            $seatIds = [];
            foreach ($seats as $seat) {
                $seatIds[] = $seat['maGhe'];
                error_log("[v0] Seat number {$seat['soGhe']} has ID {$seat['maGhe']}");
            }
            
            return $seatIds;

        } catch (Exception $e) {
            error_log("[v0] getSeatIdsByNumbers error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lấy trạng thái hiện tại của ghế
     */
    private function getSeatStatus($tripId, $seatId) {
        try {
            $sql = "SELECT trangThai FROM chuyenxe_ghe WHERE maChuyenXe = ? AND maGhe = ?";
            $result = fetch($sql, [$tripId, $seatId]);
            
            return $result ? $result['trangThai'] : 'Trống';

        } catch (Exception $e) {
            error_log("getSeatStatus error: " . $e->getMessage());
            return 'Trống';
        }
    }

    /**
     * Cập nhật trạng thái ghế
     */
    private function updateSeatStatus($tripId, $seatId, $status) {
        try {
            error_log("[v0] updateSeatStatus called - Trip: $tripId, Seat ID: $seatId, Status: $status");
            
            // Kiểm tra xem bản ghi đã tồn tại chưa
            $sql = "SELECT COUNT(*) as count FROM chuyenxe_ghe WHERE maChuyenXe = ? AND maGhe = ?";
            $result = fetch($sql, [$tripId, $seatId]);
            
            if ($result['count'] > 0) {
                // Cập nhật trạng thái
                $sql = "UPDATE chuyenxe_ghe SET trangThai = ?, ngayCapNhat = NOW() WHERE maChuyenXe = ? AND maGhe = ?";
                $updateResult = query($sql, [$status, $tripId, $seatId]);
                error_log("[v0] Updated existing seat record - Result: " . ($updateResult ? 'success' : 'failed'));
            } else {
                // Tạo bản ghi mới
                $sql = "INSERT INTO chuyenxe_ghe (maChuyenXe, maGhe, trangThai, ngayTao, ngayCapNhat) VALUES (?, ?, ?, NOW(), NOW())";
                $insertResult = query($sql, [$tripId, $seatId, $status]);
                error_log("[v0] Created new seat record - Result: " . ($insertResult ? 'success' : 'failed'));
            }

        } catch (Exception $e) {
            error_log("[v0] updateSeatStatus error: " . $e->getMessage());
            throw $e;
        }
    }
}
?>
