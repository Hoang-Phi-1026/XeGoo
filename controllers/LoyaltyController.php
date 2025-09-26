<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class LoyaltyController {
    
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Lấy điểm tích lũy của người dùng
     */
    public function getUserPoints() {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
                return;
            }

            $userId = $_SESSION['user_id'];
            $points = $this->calculateUserPoints($userId);
            $history = $this->getPointsHistory($userId, 10); // Lấy 10 giao dịch gần nhất

            echo json_encode([
                'success' => true,
                'total_points' => $points,
                'history' => $history
            ]);

        } catch (Exception $e) {
            error_log("LoyaltyController getUserPoints error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi lấy thông tin điểm tích lũy']);
        }
    }

    /**
     * Sử dụng điểm tích lũy
     */
    public function usePoints() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        try {
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
                return;
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $pointsToUse = (int)($input['points'] ?? 0);

            if ($pointsToUse <= 0) {
                echo json_encode(['success' => false, 'message' => 'Số điểm không hợp lệ']);
                return;
            }

            $userId = $_SESSION['user_id'];
            $availablePoints = $this->calculateUserPoints($userId);

            if ($pointsToUse > $availablePoints) {
                echo json_encode(['success' => false, 'message' => 'Không đủ điểm tích lũy']);
                return;
            }

            // Kiểm tra giới hạn sử dụng điểm
            $bookingData = $_SESSION['final_booking_data'] ?? null;
            if (!$bookingData) {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin đặt vé']);
                return;
            }

            $maxPointsCanUse = $this->getMaxPointsCanUse($bookingData['total_price']);
            if ($pointsToUse > $maxPointsCanUse) {
                echo json_encode([
                    'success' => false, 
                    'message' => "Chỉ có thể sử dụng tối đa {$maxPointsCanUse} điểm cho đơn hàng này"
                ]);
                return;
            }

            // Lưu vào session
            $_SESSION['used_points'] = $pointsToUse;

            // Tính lại giá
            $pricing = $this->calculatePricingWithPoints($bookingData, $pointsToUse);

            echo json_encode([
                'success' => true, 
                'message' => 'Sử dụng điểm tích lũy thành công',
                'used_points' => $pointsToUse,
                'discount_amount' => $pointsToUse * 100,
                'pricing' => $pricing
            ]);

        } catch (Exception $e) {
            error_log("LoyaltyController usePoints error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Hủy sử dụng điểm tích lũy
     */
    public function removePoints() {
        header('Content-Type: application/json');
        
        try {
            // Xóa điểm sử dụng khỏi session
            unset($_SESSION['used_points']);

            // Tính lại giá
            $bookingData = $_SESSION['final_booking_data'] ?? null;
            if (!$bookingData) {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin đặt vé']);
                return;
            }

            $pricing = $this->calculateBasePricing($bookingData);

            echo json_encode([
                'success' => true, 
                'message' => 'Đã hủy sử dụng điểm tích lũy',
                'pricing' => $pricing
            ]);

        } catch (Exception $e) {
            error_log("LoyaltyController removePoints error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Thêm điểm tích lũy khi hoàn thành đặt vé
     */
    public function addPoints($userId, $bookingId, $originalPrice) {
        try {
            // Tính điểm tích lũy (0.1% tổng tiền gốc)
            $earnedPoints = floor($originalPrice * 0.001);
            
            if ($earnedPoints > 0) {
                $sql = "INSERT INTO diem_tichluy (maNguoiDung, nguon, diem, maDatVe, ghiChu, ngayTao)
                        VALUES (?, 'MuaVe', ?, ?, 'Tích lũy từ mua vé', NOW())";
                query($sql, [$userId, $earnedPoints, $bookingId]);

                // Cập nhật tổng điểm trong bảng nguoidung
                $this->updateUserTotalPoints($userId);
            }

            return $earnedPoints;

        } catch (Exception $e) {
            error_log("addPoints error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Trừ điểm khi sử dụng
     */
    public function deductPoints($userId, $bookingId, $pointsUsed) {
        try {
            if ($pointsUsed > 0) {
                $sql = "INSERT INTO diem_tichluy (maNguoiDung, nguon, diem, maDatVe, ghiChu, ngayTao)
                        VALUES (?, 'HuyVe', ?, ?, 'Sử dụng điểm thanh toán', NOW())";
                query($sql, [$userId, -$pointsUsed, $bookingId]);

                // Cập nhật tổng điểm trong bảng nguoidung
                $this->updateUserTotalPoints($userId);
            }

            return true;

        } catch (Exception $e) {
            error_log("deductPoints error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Tính tổng điểm tích lũy của người dùng
     */
    private function calculateUserPoints($userId) {
        try {
            $sql = "SELECT COALESCE(SUM(diem), 0) as total_points 
                    FROM diem_tichluy 
                    WHERE maNguoiDung = ?";
            
            $result = fetch($sql, [$userId]);
            return max(0, (int)$result['total_points']);

        } catch (Exception $e) {
            error_log("calculateUserPoints error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Lấy lịch sử điểm tích lũy
     */
    private function getPointsHistory($userId, $limit = 10) {
        try {
            $sql = "SELECT nguon, diem, ghiChu, ngayTao
                    FROM diem_tichluy 
                    WHERE maNguoiDung = ?
                    ORDER BY ngayTao DESC
                    LIMIT ?";
            
            return fetchAll($sql, [$userId, $limit]);

        } catch (Exception $e) {
            error_log("getPointsHistory error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Tính số điểm tối đa có thể sử dụng (tối đa 50% giá trị đơn hàng)
     */
    private function getMaxPointsCanUse($totalPrice) {
        $maxDiscountAmount = $totalPrice * 0.5; // Tối đa 50% giá trị đơn hàng
        return floor($maxDiscountAmount / 100); // 1 điểm = 100đ
    }

    /**
     * Tính giá với điểm tích lũy
     */
    private function calculatePricingWithPoints($bookingData, $pointsUsed) {
        $originalPrice = $bookingData['total_price'];
        
        // Tính giảm giá từ khuyến mãi nếu có
        $promotionDiscount = 0;
        if (isset($_SESSION['applied_promotion'])) {
            $promotion = $_SESSION['applied_promotion'];
            if ($promotion['loai'] === 'PhanTram') {
                $promotionDiscount = $originalPrice * ($promotion['giaTri'] / 100);
            } else {
                $promotionDiscount = $promotion['giaTri'];
            }
        }

        // Tính giảm giá từ điểm
        $pointsDiscount = $pointsUsed * 100; // 1 điểm = 100đ

        $totalDiscount = $promotionDiscount + $pointsDiscount;
        $finalPrice = max(0, $originalPrice - $totalDiscount);

        // Tính điểm tích lũy nhận được (0.1% tổng tiền gốc)
        $earnedPoints = floor($originalPrice * 0.001);

        return [
            'original_price' => $originalPrice,
            'promotion_discount' => $promotionDiscount,
            'points_discount' => $pointsDiscount,
            'total_discount' => $totalDiscount,
            'final_price' => $finalPrice,
            'earned_points' => $earnedPoints
        ];
    }

    /**
     * Tính giá cơ bản
     */
    private function calculateBasePricing($bookingData) {
        $originalPrice = $bookingData['total_price'];
        
        // Tính giảm giá từ khuyến mãi nếu có
        $promotionDiscount = 0;
        if (isset($_SESSION['applied_promotion'])) {
            $promotion = $_SESSION['applied_promotion'];
            if ($promotion['loai'] === 'PhanTram') {
                $promotionDiscount = $originalPrice * ($promotion['giaTri'] / 100);
            } else {
                $promotionDiscount = $promotion['giaTri'];
            }
        }

        $finalPrice = max(0, $originalPrice - $promotionDiscount);

        // Tính điểm tích lũy nhận được (0.1% tổng tiền gốc)
        $earnedPoints = floor($originalPrice * 0.001);

        return [
            'original_price' => $originalPrice,
            'promotion_discount' => $promotionDiscount,
            'points_discount' => 0,
            'total_discount' => $promotionDiscount,
            'final_price' => $finalPrice,
            'earned_points' => $earnedPoints
        ];
    }

    /**
     * Cập nhật tổng điểm trong bảng nguoidung
     */
    private function updateUserTotalPoints($userId) {
        try {
            $totalPoints = $this->calculateUserPoints($userId);
            $sql = "UPDATE nguoidung SET diemTichLuy = ? WHERE maNguoiDung = ?";
            query($sql, [$totalPoints, $userId]);

        } catch (Exception $e) {
            error_log("updateUserTotalPoints error: " . $e->getMessage());
        }
    }
}
?>
