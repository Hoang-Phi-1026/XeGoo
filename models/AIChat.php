<?php
require_once __DIR__ . '/../config/database.php';

class AIChat {
    private $db;
    private const GEMINI_API_KEY = 'AIzaSyAg6zoJgGaDnc0yKzJxxHwkMXxomt3C-oo';
    private const GEMINI_API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent';
    private $conversationHistory = [];
    private $lastSearchResults = null;

    public function __construct() {
        $this->db = Database::getInstance();
        if (isset($_SESSION['aichat_history'])) {
            $this->conversationHistory = $_SESSION['aichat_history'];
        }
        if (isset($_SESSION['aichat_last_results'])) {
            $this->lastSearchResults = $_SESSION['aichat_last_results'];
        }
    }

    private function fetchAll($sql, $params = []) {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("[AIChat] Database error: " . $e->getMessage());
            return [];
        }
    }

    private function extractDateFromMessage($message) {
        $today = new DateTime();
        
        if (preg_match('/(?:ngày\s+)?(\d{1,2})[\/\-](\d{1,2})(?:[\/\-](\d{4}))?/', $message, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $year = isset($matches[3]) ? $matches[3] : $today->format('Y');
            return "$year-$month-$day";
        }
        
        if (preg_match('/(?:ngày\s+|day\s+)?(\d{1,2})(?:\s|$|[?]|!|,)/', $message, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $month = $today->format('m');
            $year = $today->format('Y');
            
            $possibleDate = new DateTime("$year-$month-$day");
            if ($possibleDate < $today) {
                $possibleDate->add(new DateInterval('P1M'));
            }
            return $possibleDate->format('Y-m-d');
        }
        
        if (preg_match('/hôm\s+nay|ngày\s+hôm\s+nay/', $message)) {
            return $today->format('Y-m-d');
        }
        if (preg_match('/ngày\s+mai|hôm\s+sau/', $message)) {
            $tomorrow = clone $today;
            $tomorrow->add(new DateInterval('P1D'));
            return $tomorrow->format('Y-m-d');
        }
        
        return $today->format('Y-m-d');
    }

    private function extractRouteFromMessage($message) {
        $normalized = strtoupper(trim($message));
        
        if (preg_match('/(SG-DL|DL-SG|SG-VT|VT-SG)/', $normalized, $matches)) {
            return $matches[1];
        }
        
        if (preg_match('/(sài gòn|hcm|tp\.?\s*hồ chí minh|đà lạt|vũng tàu).*?(sài gòn|hcm|tp\.?\s*hồ chí minh|đà lạt|vũng tàu)/i', $message, $matches)) {
            if (preg_match('/(sài gòn|hcm|tp\.?\s*hồ chí minh).*?(đà lạt)/i', $message)) {
                return 'SG-DL';
            }
            if (preg_match('/(đà lạt).*?(sài gòn|hcm|tp\.?\s*hồ chí minh)/i', $message)) {
                return 'DL-SG';
            }
            if (preg_match('/(sài gòn|hcm|tp\.?\s*hồ chí minh).*?(vũng tàu)/i', $message)) {
                return 'SG-VT';
            }
            if (preg_match('/(vũng tàu).*?(sài gòn|hcm|tp\.?\s*hồ chí minh)/i', $message)) {
                return 'VT-SG';
            }
        }
        
        return null;
    }

    private function getQuestionType($message) {
        $normalized = strtolower(trim($message));
        
        if (preg_match('/(bao nhiêu|mấy|có.*tuyến|tuyến.*nào)/', $normalized) && 
            preg_match('/(tuyến|route)/', $normalized)) {
            return 'routes';
        }
        
        if (preg_match('/(thanh toán|phương thức|trả tiền|thanh toán.*bằng)/', $normalized)) {
            return 'payment';
        }
        
        if (preg_match('/(đặt vé|mua vé|book|order)/', $normalized)) {
            return 'booking';
        }
        
        return 'trips';
    }

    private function checkDateHasTrips($date) {
        try {
            $result = $this->fetchAll("
                SELECT COUNT(*) as count
                FROM chuyenxe
                WHERE DATE(ngayKhoiHanh) = ? AND trangThai IN ('Sẵn sàng', 'Khởi hành')
            ", [$date]);
            return isset($result[0]['count']) && $result[0]['count'] > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    private function formatTripsList($trips, $grouped = true) {
        if (empty($trips)) {
            return "⚠️ Hiện chưa có chuyến xe hoạt động.\nVui lòng chọn ngày khác hoặc liên hệ:\n📞 Hotline: 0800 1234 567\n💬 Chat: xegoo.vn/support";
        }

        $output = "";
        
        if ($grouped) {
            $byRoute = [];
            foreach ($trips as $t) {
                if (!isset($byRoute[$t['kyHieuTuyen']])) {
                    $byRoute[$t['kyHieuTuyen']] = [];
                }
                $byRoute[$t['kyHieuTuyen']][] = $t;
            }

            foreach ($byRoute as $route => $routeTrips) {
                $output .= "🚌 TUYẾN: " . $route . "\n";
                $output .= "📍 Từ: " . $routeTrips[0]['diemDi'] . " → Đến: " . $routeTrips[0]['diemDen'] . "\n";
                $output .= "📊 Tổng chuyến: " . count($routeTrips) . " chuyến\n\n";
                
                foreach ($routeTrips as $idx => $trip) {
                    $soChoTrong = $trip['tongCho'] - $trip['soChoDaDat'];
                    $percent = $trip['tongCho'] > 0 ? round(($trip['soChoDaDat'] / $trip['tongCho']) * 100, 1) : 0;
                    $giaVe = $trip['giaVe'] ? number_format($trip['giaVe'], 0, ',', '.') . "đ" : "Liên hệ";
                    
                    $output .= "━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                    $output .= "Chuyến #" . ($idx + 1) . ":\n";
                    $output .= "⏰ Giờ khởi hành: " . date('H:i', strtotime($trip['thoiGianKhoiHanh'])) . "\n";
                    $output .= "🚗 Loại xe: " . $trip['tenLoaiPhuongTien'] . "\n";
                    $output .= "🔢 Biển số xe: " . $trip['bienSo'] . "\n";
                    $output .= "💰 Giá vé: " . $giaVe . "\n";
                    $output .= "📊 Chỗ trống: " . $soChoTrong . "/" . $trip['tongCho'] . " (" . $percent . "% đã đặt)\n";
                    $output .= "✅ Trạng thái: " . $trip['trangThai'] . "\n";
                    $output .= "🎫 [Đặt vé ngay](xegoo.vn/booking)\n";
                }
                $output .= "\n";
            }
        } else {
            foreach ($trips as $trip) {
                $soChoTrong = $trip['tongCho'] - $trip['soChoDaDat'];
                $percent = $trip['tongCho'] > 0 ? round(($trip['soChoDaDat'] / $trip['tongCho']) * 100, 1) : 0;
                $giaVe = $trip['giaVe'] ? number_format($trip['giaVe'], 0, ',', '.') . "đ" : "Liên hệ";
                
                $output .= "⏰ " . date('H:i', strtotime($trip['thoiGianKhoiHanh'])) . " | ";
                $output .= "💰 " . $giaVe . " | ";
                $output .= "📊 Chỗ: " . $soChoTrong . "/" . $trip['tongCho'] . "\n";
            }
        }
        
        return $output;
    }

    private function getBusinessContext($userMessage) {
        $today = new DateTime();
        $normalized_message = strtolower(trim($userMessage));
        $requestedDate = $this->extractDateFromMessage($normalized_message);
        $requestedRoute = $this->extractRouteFromMessage($normalized_message);
        $questionType = $this->getQuestionType($normalized_message);
        
        $useLastResults = false;
        if (preg_match('/(những chuyến đó|chuyến đó|những chuyến này|các chuyến đó|những chuyến|chuyến đã hỏi)/', $normalized_message) && $this->lastSearchResults) {
            $useLastResults = true;
            $requestedDate = $this->lastSearchResults['date'];
            $requestedRoute = $this->lastSearchResults['route'];
            error_log("[AIChat] Using last search results - Date: " . $requestedDate . ", Route: " . ($requestedRoute ?? 'ANY'));
        }
        
        error_log("[AIChat] Requested date: " . $requestedDate . " | Route: " . ($requestedRoute ?? 'ANY') . " | Type: " . $questionType . " | UseLastResults: " . ($useLastResults ? 'true' : 'false'));
        
        $context = "Bạn là trợ lý AI của nhà xe XeGoo - dịch vụ vận tải hành khách chuyên nghiệp tại Việt Nam.\n\n";
        
        $context .= "### HỨA HẸN CHÍNH XÁC ===\n";
        $context .= "- Trả lời CHÍNH XÁC 100% dựa trên dữ liệu thực tế\n";
        $context .= "- Cung cấp đầy đủ: Giờ khởi hành, loại xe, biển số, giá vé, số chỗ còn\n";
        $context .= "- Nếu không có dữ liệu cho ngày được hỏi, SAY RÕ thay vì phát sinh thông tin\n";
        $context .= "- Luôn kèm theo thông tin liên lạc\n\n";

        $context .= "### PHƯƠNG THỨC THANH TOÁN ===\n";
        $context .= "XeGoo hỗ trợ 2 phương thức thanh toán điện tử:\n";
        $context .= "- MoMo - Ví điện tử di động\n";
        $context .= "  • Tải ứng dụng MoMo\n";
        $context .= "  • Liên kết số điện thoại và tài khoản ngân hàng\n";
        $context .= "  • Chọn MoMo khi thanh toán vé\n";
        $context .= "  • An toàn, nhanh chóng, không phí giao dịch\n\n";
        $context .= "- VNPay - Cổng thanh toán quốc tế\n";
        $context .= "  • Hỗ trợ thẻ tín dụng, thẻ ghi nợ (Visa, Mastercard)\n";
        $context .= "  • Chuyển khoản ngân hàng nội địa\n";
        $context .= "  • Ví điện tử VNPay\n";
        $context .= "  • Bảo mật theo tiêu chuẩn quốc tế\n\n";

        $context .= "### HƯỚNG DẪN MUA VÉ ===\n";
        $context .= "Truy cập: xegoo.vn/booking-guide để xem hướng dẫn chi tiết:\n";
        $context .= "- Quy trình đặt vé:\n";
        $context .= "  1. Chọn điểm đi & điểm đến\n";
        $context .= "  2. Chọn ngày khởi hành\n";
        $context .= "  3. Xem danh sách chuyến xe có sẵn\n";
        $context .= "  4. Chọn chuyến xe & loại vé phù hợp\n";
        $context .= "  5. Nhập thông tin hành khách\n";
        $context .= "  6. Chọn phương thức thanh toán (MoMo/VNPay)\n";
        $context .= "  7. Thanh toán & nhận mã QR vé\n";
        $context .= "  8. Mã QR sẽ được gửi qua email\n\n";

        $context .= "### DỊCH VỤ THUÊ XE - ĐẶT XE CHO GIA ĐÌNH ===\n";
        $context .= "Truy cập: xegoo.vn/group-rental để xem thông tin chi tiết\n";
        $context .= "- XeGoo cung cấp dịch vụ thuê xe toàn bộ cho gia đình, nhóm du lịch:\n";
        $context .= "  • Giá nhóm ưu tiên (từ 10+ người trở lên)\n";
        $context .= "  • Xe đẹp, hiện đại, an toàn\n";
        $context .= "  • Tài xế chuyên nghiệp, kinh nghiệm\n";
        $context .= "  • Linh hoạt lịch trình theo nhu cầu\n";
        $context .= "  • Hỗ trợ thêm dịch vụ: hướng dẫn viên, ăn uống\n";
        $context .= "  • Liên hệ ngay: Hotline 0800 1234 567 hoặc email support@xegoo.vn\n\n";

        $context .= "### TRA CỨU VÉ - TRA CỨU NHANH ===\n";
        $context .= "Truy cập: xegoo.vn/ticket-lookup để tra cứu vé của bạn\n";
        $context .= "- Chỉ cần nhập:\n";
        $context .= "  • Mã vé (hoặc mã QR)\n";
        $context .= "  • Email hoặc Số điện thoại\n";
        $context .= "- Hệ thống sẽ hiển thị:\n";
        $context .= "  • Thông tin chi tiết chuyến xe\n";
        $context .= "  • Giờ khởi hành, chỗ ngồi\n";
        $context .= "  • Trạng thái vé (hoạt động, đã sử dụng, hủy)\n";
        $context .= "  • Thông tin thanh toán\n\n";

        $context .= "### CHƯƠNG TRÌNH KHUYẾN MÃI ===\n";
        $context .= "- XeGoo có chương trình khuyến mãi liên tục:\n";
        $context .= "  • Mã khuyến mãi được dành riêng cho bạn\n";
        $context .= "  • Nhận ưu đãi khi mua vé thường xuyên\n";
        $context .= "  • Giảm giá khi đặt nhóm (5+ người)\n";
        $context .= "  • Ưu đãi đặc biệt cho khách VIP\n";
        $context .= "- Cách áp dụng mã khuyến mãi:\n";
        $context .= "  1. Chọn chuyến xe & loại vé\n";
        $context .= "  2. Nhập mã khuyến mãi vào ô 'Mã khuyến mãi'\n";
        $context .= "  3. Nhấn 'Áp dụng' - giảm giá sẽ hiển thị ngay\n";
        $context .= "  4. Tiến hành thanh toán\n";
        $context .= "- Lưu ý: Mỗi mã chỉ áp dụng 1 lần, không kết hợp với ưu đãi khác\n\n";

        $context .= "### CHÍNH SÁCH HỦY VÉ ===\n";
        $context .= "- Điều kiện hủy vé:\n";
        $context .= "  • Chỉ có thể hủy vé TRƯỚC 36 GIỜ so với giờ khởi hành\n";
        $context .= "  • Ví dụ: Chuyến xe 10h ngày 1/1/2025 → Hủy tối đa lúc 10h ngày 30/12/2024\n";
        $context .= "  • Vé đã sử dụng KHÔNG thể hủy\n";
        $context .= "  • Vé đã hủy KHÔNG thể đặt lại\n\n";
        $context .= "- Quy tắc hoàn tiền:\n";
        $context .= "  • Hủy thành công → Nhận hoàn 20% giá vé dựa trên ĐIỂM TÍCH LŨY:\n";
        $context .= "    • Cách tính: Hoàn tiền = (Giá vé × 20%) ÷ 100\n";
        $context .= "    • Ví dụ: Vé 500.000đ → Hoàn = 500.000 × 20% = 100.000đ\n";
        $context .= "    • Tiền hoàn sẽ được cộng vào tài khoản Điểm tích lũy\n";
        $context .= "- Cách hủy vé:\n";
        $context .= "  1. Truy cập 'Vé của tôi' (xegoo.vn/my-tickets)\n";
        $context .= "  2. Chọn vé muốn hủy\n";
        $context .= "  3. Nhấn nút 'Hủy vé'\n";
        $context .= "  4. Xác nhận hủy\n";
        $context .= "  5. Tiền hoàn về điểm tích lũy trong 24h\n\n";

        $context .= "### HỆ THỐNG ĐIỂM TÍCH LŨY ===\n";
        $context .= "- Tích điểm khi mua vé:\n";
        $context .= "  • Mỗi lần mua vé = tự động tích lũy điểm\n";
        $context .= "  • Công thức: Điểm tích lũy = (Giá vé × 0.03%) ÷ 100\n";
        $context .= "  • Ví dụ: Mua vé 500.000đ → Tích được 500.000 × 0.03% = 150 điểm\n\n";
        $context .= "- Đổi điểm lấy tiền giảm giá:\n";
        $context .= "  • 1 điểm = 100đ tiền giảm giá khi mua vé tiếp theo\n";
        $context .= "  • Ví dụ: 150 điểm = 15.000đ (giảm khi mua vé tiếp theo)\n";
        $context .= "  • Điểm không có thời hạn, có thể sử dụng bất cứ lúc nào\n";
        $context .= "  • Có thể kết hợp với các mã khuyến mãi khác\n\n";
        $context .= "- Xem điểm tích lũy:\n";
        $context .= "  1. Đăng nhập tài khoản XeGoo\n";
        $context .= "  2. Vào mục 'Tài khoản của tôi' → 'Điểm tích lũy'\n";
        $context .= "  3. Xem số điểm hiện tại & lịch sử tích lũy\n\n";

        $context .= "### NHẬN VÉ ĐIỆN TỬ ===\n";
        $context .= "- Nhận vé trực tiếp sau khi đặt:\n";
        $context .= "  • Sau khi thanh toán thành công → Mã QR vé được gửi ngay\n";
        $context .= "  • Kiểm tra Email (hoặc Spam nếu không thấy)\n";
        $context .= "  • Mã QR có thể xem ngay trên điện thoại\n";
        $context .= "- Lưu trữ vé trên APP/WEB:\n";
        $context .= "  • Vào xegoo.vn/my-tickets → 'Vé của tôi'\n";
        $context .= "  • Xem tất cả vé đã đặt (sắp tới, đã sử dụng, hủy)\n";
        $context .= "  • Mã QR vé luôn có sẵn để tải xuống hoặc in\n\n";

        $context .= "### XEM VÉ ĐÃ ĐẶT - LỊCH SỬ ===\n";
        $context .= "- Truy cập lịch sử vé:\n";
        $context .= "  • Trang: xegoo.vn/my-tickets/history\n";
        $context .= "  • Xem toàn bộ vé đã từng đặt (hoạt động, đã sử dụng, hủy)\n";
        $context .= "  • Sắp xếp theo ngày đặt hoặc ngày khởi hành\n";
        $context .= "  • Tải lại mã QR nếu bị mất\n";
        $context .= "  • Kiểm tra chi tiết chuyến xe & tài chính\n\n";

        $context .= "### THỦ TỤC CHECK-IN & LÊN XE ===\n";
        $context .= "- Quy trình kiểm tra & lên xe:\n";
        $context .= "  1. Đến đúng giờ khởi hành (Có mặt ít nhất 15 phút trước)\n";
        $context .= "  2. Kiểm tra - Tìm chuyến xe của bạn theo:\n";
        $context .= "    • Tuyến đường\n";
        $context .= "    • Thời gian khởi hành\n";
        $context .= "    • Biển số xe\n";
        $context .= "  3. Xử lý - Đưa MÃ QR cho tài xế:\n";
        $context .= "    • Xuất trình mã QR trên điện thoại hoặc in ra\n";
        $context .= "    • Tài xế quét mã để xác nhận\n";
        $context .= "  4. Lên xe - Ngồi vào chỗ ngồi đúng theo vé:\n";
        $context .= "    • Kiểm tra số ghế trên vé\n";
        $context .= "    • Cố định hành lý\n";
        $context .= "    • Chuẩn bị cho cuộc hành trình\n\n";
        $context .= "- Lưu ý quan trọng:\n";
        $context .= "  • Muộn >15p sau giờ khởi hành = XE KHỞI HÀNH MÀ KHÔNG CHỜ\n";
        $context .= "  • Hành lý cá nhân tự chịu trách nhiệm bảo quản\n";
        $context .= "  • Tuân thủ quy định an toàn & văn minh trên xe\n\n";

        $context .= "### LIÊN HỆ HỖ TRỢ & XỬ LÝ SỰ CỐ ===\n";
        $context .= "- Khi cần hỗ trợ đặc biệt:\n";
        $context .= "  • Liên hệ trực tiếp:\n";
        $context .= "    • Gọi Hotline: 0800 1234 567\n";
        $context .= "    • Chat hỗ trợ: xegoo.vn/support\n";
        $context .= "  • Thông báo sự cố:\n";
        $context .= "    • Mã vé của bạn\n";
        $context .= "    • Lý do không thể lên xe\n";
        $context .= "    • Nhu cầu của bạn (hoàn vé, đặt lại, v.v)\n";
        $context .= "- Nhân viên hỗ trợ sẽ giải quyết:\n";
        $context .= "  • Hỗ trợ đặt lại chuyến khác\n";
        $context .= "  • Hoàn tiền hoặc trừ lệ phí\n";
        $context .= "  • Ghi chú sự cố vào tài khoản\n\n";

        $context .= "### TRƯỜNG HỢP KHÔNG THỂ LÊN XE ===\n";
        $context .= "- Nếu bạn không thể lên xe (bị ốm, sự cố, v.v):\n";
        $context .= "  1. Liên hệ ngay trước khi xe khởi hành:\n";
        $context .= "    • Gọi Hotline: 0800 1234 567\n";
        $context .= "    • Chat hỗ trợ: xegoo.vn/support\n";
        $context .= "  2. Thông báo sự cố:\n";
        $context .= "    • Mã vé của bạn\n";
        $context .= "    • Lý do không thể lên xe\n";
        $context .= "    • Nhu cầu của bạn (hoàn vé, đặt lại, v.v)\n";
        $context .= "  3. Nhân viên hỗ trợ sẽ giải quyết:\n";
        $context .= "    • Hỗ trợ đặt lại chuyến khác\n";
        $context .= "    • Hoàn tiền hoặc trừ lệ phí\n";
        $context .= "    • Ghi chú sự cố vào tài khoản\n\n";

        $context .= "\n### DANH SÁCH LOẠI PHƯƠNG TIỆN ===\n";
        try {
            $vehicleTypes = $this->fetchAll("
                SELECT 
                    maLoaiPhuongTien,
                    tenLoaiPhuongTien,
                    soChoMacDinh,
                    loaiChoNgoiMacDinh AS loaiChoNgoi,
                    hangXe
                FROM loaiphuongtien
                ORDER BY soChoMacDinh DESC
            ");
            if (!empty($vehicleTypes)) {
                foreach ($vehicleTypes as $vt) {
                    $context .= "- {$vt['tenLoaiPhuongTien']} ({$vt['soChoMacDinh']} chỗ, loại: {$vt['loaiChoNgoi']}, hãng: {$vt['hangXe']})\n";
                }
            } else {
                $context .= "Chưa có thông tin loại phương tiện.\n";
            }
        } catch (Exception $e) {
            error_log("[AIChat] Error vehicle types: " . $e->getMessage());
        }

        $context .= "\n### DANH SÁCH TUYẾN ĐƯỜNG ===\n";
        try {
            $routes = $this->fetchAll("
                SELECT maTuyenDuong, kyHieuTuyen, diemDi, diemDen, khoangCach, thoiGianDiChuyen
                FROM tuyenduong
                WHERE trangThai = 'Đang hoạt động'
                ORDER BY kyHieuTuyen
            ");
            foreach ($routes as $r) {
                $context .= "- 📍 {$r['kyHieuTuyen']}: {$r['diemDi']} → {$r['diemDen']} ({$r['khoangCach']} km, {$r['thoiGianDiChuyen']})\n";
            }
        } catch (Exception $e) {
            error_log("[AIChat] Error routes: " . $e->getMessage());
        }

        $context .= "\n### DANH SÁCH CHUYẾN XE NGÀY " . date('d/m/Y', strtotime($requestedDate)) . " ===\n";
        try {
            $routeCondition = $requestedRoute ? " AND t.kyHieuTuyen = ? " : "";
            $params = $requestedRoute ? [$requestedDate, $requestedRoute] : [$requestedDate];
            
            $trips = $this->fetchAll("
                SELECT 
                    c.maChuyenXe,
                    t.kyHieuTuyen,
                    t.diemDi,
                    t.diemDen,
                    c.ngayKhoiHanh,
                    c.thoiGianKhoiHanh,
                    p.bienSo,
                    lpt.tenLoaiPhuongTien,
                    lpt.soChoMacDinh AS tongCho,
                    c.soChoDaDat,
                    g.giaVe,
                    c.trangThai
                FROM chuyenxe c
                JOIN lichtrinh lt ON c.maLichTrinh = lt.maLichTrinh
                JOIN tuyenduong t ON lt.maTuyenDuong = t.maTuyenDuong
                JOIN phuongtien p ON c.maPhuongTien = p.maPhuongTien
                JOIN loaiphuongtien lpt ON p.maLoaiPhuongTien = lpt.maLoaiPhuongTien
                LEFT JOIN giave g ON c.maGiaVe = g.maGiaVe
                WHERE DATE(c.ngayKhoiHanh) = ? $routeCondition AND c.trangThai IN ('Sẵn sàng', 'Khởi hành')
                ORDER BY t.kyHieuTuyen, c.thoiGianKhoiHanh
            ", $params);

            error_log("[AIChat] Found " . count($trips) . " trips for date " . $requestedDate . ($requestedRoute ? " on route " . $requestedRoute : ""));

            $this->lastSearchResults = [
                'date' => $requestedDate,
                'route' => $requestedRoute,
                'trips' => $trips,
                'timestamp' => time()
            ];
            $_SESSION['aichat_last_results'] = $this->lastSearchResults;

            if (!empty($trips)) {
                $context .= $this->formatTripsList($trips, true);
            } else {
                $context .= "⚠️ Hiện chưa có chuyến xe hoạt động cho ngày " . date('d/m/Y', strtotime($requestedDate));
                if ($requestedRoute) {
                    $context .= " trên tuyến $requestedRoute";
                }
                $context .= ".\n\nVui lòng chọn ngày khác hoặc liên hệ:\n";
                $context .= "📞 Hotline: 0800 1234 567\n";
                $context .= "💬 Chat: xegoo.vn/support\n";
            }
        } catch (Exception $e) {
            error_log("[AIChat] Error trips query: " . $e->getMessage());
            $context .= "⚠️ Lỗi khi truy vấn dữ liệu chuyến xe.\n";
        }

        if (preg_match('/(giá|vé|bao nhiêu|chi phí|tiền)/', $normalized_message)) {
            $context .= "\n### BẢNG GIÁ VÉ ===\n";
            try {
                if ($useLastResults && !empty($this->lastSearchResults['trips'])) {
                    $context .= "📍 Giá vé cho các chuyến trên:\n\n";
                    $prices = $this->lastSearchResults['trips'];
                } else {
                    $prices = $this->fetchAll("
                        SELECT DISTINCT
                            t.kyHieuTuyen,
                            t.diemDi,
                            t.diemDen,
                            lpt.tenLoaiPhuongTien,
                            g.loaiChoNgoi,
                            g.giaVe
                        FROM giave g
                        JOIN tuyenduong t ON g.maTuyenDuong = t.maTuyenDuong
                        JOIN loaiphuongtien lpt ON g.maLoaiPhuongTien = lpt.maLoaiPhuongTien
                        WHERE g.trangThai = 'Hoạt động'
                        ORDER BY t.kyHieuTuyen, g.giaVe
                    ");
                    $context .= "📍 Bảng giá vé XeGoo:\n\n";
                }
                
                if (!empty($prices)) {
                    foreach ($prices as $p) {
                        $giaVe = $p['giaVe'] ? number_format($p['giaVe'], 0, ',', '.') . "đ" : "Liên hệ";
                        $context .= "🚌 Tuyến: " . (isset($p['kyHieuTuyen']) ? $p['kyHieuTuyen'] : 'N/A') . "\n";
                        $context .= "📍 Từ: " . (isset($p['diemDi']) ? $p['diemDi'] : '') . " → Đến: " . (isset($p['diemDen']) ? $p['diemDen'] : '') . "\n";
                        $context .= "🚗 Loại xe: " . (isset($p['tenLoaiPhuongTien']) ? $p['tenLoaiPhuongTien'] : '') . "\n";
                        $context .= "💺 Loại chỗ: " . (isset($p['loaiChoNgoi']) ? $p['loaiChoNgoi'] : '') . "\n";
                        $context .= "💰 Giá: " . $giaVe . "\n";
                        $context .= "━━━━━━━━━━━━━━━━━━━━━━\n\n";
                    }
                }
            } catch (Exception $e) {
                error_log("[AIChat] Error prices: " . $e->getMessage());
            }
        }

        $context .= "\n### LIÊN HỆ HỖ TRỢ ===\n";
        $context .= "📞 Hotline: 0800 1234 567 (24/7)\n";
        $context .= "📧 Email: support@xegoo.vn\n";
        $context .= "💬 Chat hỗ trợ: xegoo.vn/support\n";
        $context .= "🎫 Đặt vé: xegoo.vn/booking\n";
        $context .= "📱 Tra cứu vé: xegoo.vn/ticket-lookup\n";
        
        return $context;
    }

    private function callGeminiAPI($prompt) {
        $data = [
            'contents' => [['parts' => [['text' => $prompt]]]]
        ];

        $ch = curl_init(self::GEMINI_API_URL . '?key=' . self::GEMINI_API_KEY);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error) {
            error_log("[AIChat] Curl error: " . $error);
            return ['error' => 'Lỗi kết nối API: ' . $error];
        }
        
        $result = json_decode($response, true);
        
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            return ['reply' => trim($result['candidates'][0]['content']['parts'][0]['text'])];
        }
        
        if (isset($result['error']['message'])) {
            error_log("[AIChat] Gemini API error: " . $result['error']['message']);
            return ['error' => 'Lỗi API Gemini: ' . $result['error']['message']];
        }
        
        error_log("[AIChat] Invalid response from Gemini (HTTP $http): " . $response);
        return ['error' => "Phản hồi không hợp lệ từ Gemini (HTTP $http)"];
    }

    public function askAI($msg) {
        if (empty(trim($msg))) {
            return ['error' => 'Tin nhắn trống'];
        }
        
        error_log("[AIChat] Processing message: " . $msg);
        
        $this->conversationHistory[] = [
            'role' => 'user',
            'content' => $msg,
            'timestamp' => time()
        ];
        $_SESSION['aichat_history'] = $this->conversationHistory;
        
        $context = $this->getBusinessContext($msg);
        
        $systemPrompt = "Bạn là trợ lý AI của XeGoo - nhà xe uy tín hàng đầu Việt Nam.\n\n"
            . "📋 QUYẾT TẮC TRẢ LỜI:\n"
            . "1️⃣ CHÍNH XÁC 100%\n"
            . "   - Chỉ dùng dữ liệu được cung cấp\n"
            . "   - Không phát sinh thông tin\n"
            . "   - Nếu không có dữ liệu → SAY RÕ RÀNG\n\n"
            . "2️⃣ ĐỊNH DẠNG TRẬN ĐẸP\n"
            . "   - MỖI THÔNG TIN 1 DÒNG RIÊNG\n"
            . "   - Sử dụng emoji & heading rõ ràng\n"
            . "   - Không nén nhiều info trên 1 dòng\n"
            . "   - Sử dụng ━━━━━ để tách biệt\n\n"
            . "3️⃣ CẤU TRÚC TRẢ LỜI\n"
            . "   ### Tiêu đề chính\n"
            . "   📍 [Thông tin]\n"
            . "   ⏰ [Giờ]\n"
            . "   💰 [Giá]\n"
            . "   📊 [Chỗ còn]\n"
            . "   [Liên kết giúp đỡ]\n\n"
            . "4️⃣ LIÊN KẾT VÀ HƯỚNG DẪN\n"
            . "   🎫 [Đặt vé ngay](xegoo.vn/booking)\n"
            . "   📱 [Tra cứu vé](xegoo.vn/ticket-lookup)\n"
            . "   💬 [Chat với hỗ trợ](xegoo.vn/support)\n"
            . "   📞 Hotline: 0800 1234 567\n\n"
            . "5️⃣ HIỂU NGỮ CẢNH\n"
            . "   - Nếu user hỏi 'giá vé của những chuyến đó'\n"
            . "   - Hiểu = giá của chuyến xe từ câu hỏi trước\n"
            . "   - KHÔNG hỏi lại ngày/tuyến mà dùng kết quả trước\n";
        
        $prompt = $systemPrompt . "\n" . $context . "\n\nCâu hỏi của khách hàng: " . $msg;
        
        error_log("[AIChat] Sending prompt to Gemini API");
        $response = $this->callGeminiAPI($prompt);
        
        if (isset($response['reply'])) {
            $this->conversationHistory[] = [
                'role' => 'assistant',
                'content' => $response['reply'],
                'timestamp' => time()
            ];
            $_SESSION['aichat_history'] = $this->conversationHistory;
        }
        
        return $response;
    }
}
?>
