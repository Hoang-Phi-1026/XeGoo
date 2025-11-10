<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class PromotionController {
    
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Lấy danh sách khuyến mãi có thể áp dụng
     */
    public function getAvailablePromotions() {
        header('Content-Type: application/json');
        
        try {
            $currentDate = date('Y-m-d');
            $sql = "SELECT maKhuyenMai, tenKhuyenMai, loai, giaTri, ngayBatDau, ngayKetThuc, dieuKienApDung
                    FROM khuyenmai 
                    WHERE ngayBatDau <= ? AND ngayKetThuc >= ?
                    ORDER BY 
                        CASE WHEN loai = 'PhanTram' THEN giaTri ELSE giaTri/10000 END DESC,
                        tenKhuyenMai ASC";
            
            $promotions = fetchAll($sql, [$currentDate, $currentDate]);
            
            // Thêm thông tin mô tả chi tiết
            foreach ($promotions as &$promotion) {
                $promotion['description'] = $this->getPromotionDescription($promotion);
                $promotion['can_apply'] = $this->canApplyPromotion($promotion);
            }
            
            echo json_encode([
                'success' => true,
                'promotions' => $promotions
            ]);

        } catch (Exception $e) {
            error_log("PromotionController getAvailablePromotions error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi lấy danh sách khuyến mãi']);
        }
    }

    /**
     * Kiểm tra và áp dụng khuyến mãi
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

            // Kiểm tra điều kiện áp dụng
            $canApply = $this->validatePromotionConditions($promotion);
            if (!$canApply['valid']) {
                echo json_encode(['success' => false, 'message' => $canApply['message']]);
                return;
            }

            // Lưu vào session
            $_SESSION['applied_promotion'] = $promotion;

            // Tính lại giá
            $bookingData = $_SESSION['final_booking_data'] ?? null;
            if (!$bookingData) {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin đặt vé']);
                return;
            }

            $pricing = $this->calculatePricingWithPromotion($bookingData, $promotion);

            echo json_encode([
                'success' => true, 
                'message' => 'Áp dụng khuyến mãi thành công',
                'promotion' => $promotion,
                'pricing' => $pricing
            ]);

        } catch (Exception $e) {
            error_log("PromotionController applyPromotion error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Hủy áp dụng khuyến mãi
     */
    public function removePromotion() {
        header('Content-Type: application/json');
        
        try {
            // Xóa khuyến mãi khỏi session
            unset($_SESSION['applied_promotion']);

            // Tính lại giá
            $bookingData = $_SESSION['final_booking_data'] ?? null;
            if (!$bookingData) {
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin đặt vé']);
                return;
            }

            $pricing = $this->calculateBasePricing($bookingData);

            echo json_encode([
                'success' => true, 
                'message' => 'Đã hủy áp dụng khuyến mãi',
                'pricing' => $pricing
            ]);

        } catch (Exception $e) {
            error_log("PromotionController removePromotion error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
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
     * Tạo mô tả chi tiết cho khuyến mãi
     */
    private function getPromotionDescription($promotion) {
        $description = $promotion['tenKhuyenMai'];
        
        if ($promotion['loai'] === 'PhanTram') {
            $description .= " - Giảm {$promotion['giaTri']}%";
        } else {
            $description .= " - Giảm " . number_format($promotion['giaTri'], 0, ',', '.') . "đ";
        }
        
        $description .= " (Hết hạn: " . date('d/m/Y', strtotime($promotion['ngayKetThuc'])) . ")";
        
        return $description;
    }

    /**
     * Kiểm tra có thể áp dụng khuyến mãi không
     */
    private function canApplyPromotion($promotion) {
        // Kiểm tra cơ bản về thời hạn
        $currentDate = date('Y-m-d');
        if ($currentDate < $promotion['ngayBatDau'] || $currentDate > $promotion['ngayKetThuc']) {
            return false;
        }

        // Có thể thêm các điều kiện khác ở đây
        return true;
    }

    /**
     * Kiểm tra điều kiện áp dụng khuyến mãi chi tiết
     */
    private function validatePromotionConditions($promotion) {
        $bookingData = $_SESSION['final_booking_data'] ?? null;
        if (!$bookingData) {
            return ['valid' => false, 'message' => 'Không tìm thấy thông tin đặt vé'];
        }

        $totalPrice = $bookingData['total_price'];

        // Kiểm tra điều kiện theo loại khuyến mãi
        switch ($promotion['maKhuyenMai']) {
            case 1: // Giảm giá 10% cho khách hàng mới
                if (isset($_SESSION['user_id'])) {
                    $isNewCustomer = $this->isNewCustomer($_SESSION['user_id']);
                    if (!$isNewCustomer) {
                        return ['valid' => false, 'message' => 'Khuyến mãi chỉ dành cho khách hàng mới'];
                    }
                }
                break;
                
            case 2: // Giảm 50,000đ cho đơn hàng từ 500,000đ
                if ($totalPrice < 500000) {
                    return ['valid' => false, 'message' => 'Đơn hàng phải có giá trị từ 500,000đ trở lên'];
                }
                break;
                
            case 3: // Khuyến mãi cuối tuần
                $dayOfWeek = date('N'); // 1 = Monday, 7 = Sunday
                if ($dayOfWeek < 6) { // Không phải thứ 7 hoặc chủ nhật
                    return ['valid' => false, 'message' => 'Khuyến mãi chỉ áp dụng vào cuối tuần'];
                }
                break;
                
            case 4: // Giảm 100,000đ cho vé khứ hồi
                if (!isset($bookingData['return']) || $totalPrice < 1000000) {
                    return ['valid' => false, 'message' => 'Khuyến mãi chỉ áp dụng cho vé khứ hồi có giá trị từ 1,000,000đ'];
                }
                break;
                
            case 5: // Ưu đãi sinh viên
                // Giả sử cần kiểm tra thông tin sinh viên (có thể thêm logic sau)
                break;
        }

        return ['valid' => true, 'message' => 'Có thể áp dụng khuyến mãi'];
    }

    /**
     * Kiểm tra khách hàng mới
     */
    private function isNewCustomer($userId) {
        try {
            $sql = "SELECT COUNT(*) as booking_count FROM datve_thanhtoan dt
                    INNER JOIN datve d ON dt.maDatVe LIKE CONCAT(d.maDatVe, '%')
                    INNER JOIN datve_chitiet dc ON d.maDatVe = dc.maDatVe
                    WHERE dt.trangThai = 'DaThanhToan'";
            
            // Tạm thời return true để test
            return true;

        } catch (Exception $e) {
            error_log("isNewCustomer error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Tính giá với khuyến mãi
     */
    private function calculatePricingWithPromotion($bookingData, $promotion) {
        $originalPrice = $bookingData['total_price'];
        $promotionDiscount = 0;

        // Tính giảm giá từ khuyến mãi
        if ($promotion['loai'] === 'PhanTram') {
            $promotionDiscount = $originalPrice * ($promotion['giaTri'] / 100);
        } else {
            $promotionDiscount = $promotion['giaTri'];
        }

        // Áp dụng điểm tích lũy nếu có
        $pointsDiscount = 0;
        if (isset($_SESSION['used_points'])) {
            $pointsDiscount = $_SESSION['used_points'] * 100; // 1 điểm = 100đ
        }

        $totalDiscount = $promotionDiscount + $pointsDiscount;
        $finalPrice = max(0, $originalPrice - $totalDiscount);

        // Tính điểm tích lũy nhận được (0.03% tổng tiền gốc)
        $earnedPoints = floor($originalPrice * 0.0003);

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
     * Tính giá cơ bản không có khuyến mãi
     */
    private function calculateBasePricing($bookingData) {
        $originalPrice = $bookingData['total_price'];
        
        // Chỉ áp dụng điểm tích lũy nếu có
        $pointsDiscount = 0;
        if (isset($_SESSION['used_points'])) {
            $pointsDiscount = $_SESSION['used_points'] * 100; // 1 điểm = 100đ
        }

        $finalPrice = max(0, $originalPrice - $pointsDiscount);

        // Tính điểm tích lũy nhận được (0.03% tổng tiền gốc)
        $earnedPoints = floor($originalPrice * 0.0003);

        return [
            'original_price' => $originalPrice,
            'promotion_discount' => 0,
            'points_discount' => $pointsDiscount,
            'total_discount' => $pointsDiscount,
            'final_price' => $finalPrice,
            'earned_points' => $earnedPoints
        ];
    }
}
?>
