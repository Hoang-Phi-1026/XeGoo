<?php
require_once __DIR__ . '/../models/Price.php';
require_once __DIR__ . '/../config/config.php';

class PriceController {

    /**
     * Check if user is admin
     */
    private function checkAdminAccess() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
            $_SESSION['error'] = 'Bạn không có quyền truy cập chức năng này.';
            header('Location: ' . BASE_URL . '/');
            exit;
        }
    }

    /**
     * Helper: Call sync stored procedure
     */
    private function syncChuyenXeGiaVe() {
        // Sử dụng PDO hoặc mysqli để gọi thủ tục
        // Ví dụ dùng mysqli:
        global $conn; // $conn phải là kết nối mysqli, có thể cần sửa lại cho đúng hệ thống bạn
        if ($conn) {
            $conn->query("CALL sp_sync_giave_chuyenxe();");
            // Nếu dùng PDO, có thể là:
            // $pdo->query("CALL sp_sync_giave_chuyenxe();");
        }
    }

    /**
     * Display price list
     */
    public function index() {
        $this->checkAdminAccess();

        // Get filter parameters
        $status = $_GET['status'] ?? null;
        $search = $_GET['search'] ?? '';
        $routeId = $_GET['route'] ?? null;
        $vehicleTypeId = $_GET['vehicle_type'] ?? null;

        // Get prices
        $prices = Price::getAll($status, $search, $routeId, $vehicleTypeId);

        // Get filter options
        $routes = Price::getAllRoutes();
        $vehicleTypes = Price::getAllVehicleTypes();
        $statusOptions = Price::getStatusOptions();

        // Get statistics
        $stats = Price::getStats();

        // Load view
        include __DIR__ . '/../views/prices/index.php';
    }

    /**
     * Show price details
     */
    public function show($id) {
        $this->checkAdminAccess();

        $price = Price::getById($id);

        if (!$price) {
            $_SESSION['error'] = 'Không tìm thấy giá vé.';
            header('Location: ' . BASE_URL . '/prices');
            exit;
        }

        include __DIR__ . '/../views/prices/show.php';
    }

    /**
     * Show add price form
     */
    public function create() {
        $this->checkAdminAccess();

        $routes = Price::getAllRoutes();
        $ticketTypes = Price::getAllTicketTypes();
        $vehicleTypes = Price::getAllVehicleTypes();
        $seatTypes = Price::getSeatTypes();
        $statusOptions = Price::getStatusOptions();

        include __DIR__ . '/../views/prices/create.php';
    }

    /**
     * Handle add price form submission
     */
    public function store() {
        $this->checkAdminAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/prices/create');
            exit;
        }

        // Validate input
        $errors = [];
        $data = [];

        // Route
        if (empty($_POST['maTuyenDuong'])) {
            $errors[] = 'Vui lòng chọn tuyến đường.';
        } else {
            $data['maTuyenDuong'] = (int)$_POST['maTuyenDuong'];
        }

        // Vehicle Type
        if (empty($_POST['maLoaiPhuongTien'])) {
            $errors[] = 'Vui lòng chọn loại phương tiện.';
        } else {
            $data['maLoaiPhuongTien'] = (int)$_POST['maLoaiPhuongTien'];
        }

        // Seat type
        if (empty($_POST['loaiChoNgoi'])) {
            $errors[] = 'Vui lòng chọn loại chỗ ngồi.';
        } else {
            $data['loaiChoNgoi'] = trim($_POST['loaiChoNgoi']);
        }

        // Ticket type
        if (empty($_POST['maLoaiVe'])) {
            $errors[] = 'Vui lòng chọn loại vé.';
        } else {
            $data['maLoaiVe'] = (int)$_POST['maLoaiVe'];
        }

        // Main price
        if (empty($_POST['giaVe']) || !is_numeric($_POST['giaVe']) || $_POST['giaVe'] <= 0) {
            $errors[] = 'Vui lòng nhập giá vé hợp lệ.';
        } else {
            $data['giaVe'] = (float)$_POST['giaVe'];
        }

        // Promotional price (optional)
        if (!empty($_POST['giaVeKhuyenMai'])) {
            if (!is_numeric($_POST['giaVeKhuyenMai']) || $_POST['giaVeKhuyenMai'] <= 0) {
                $errors[] = 'Giá vé khuyến mãi không hợp lệ.';
            } else {
                $data['giaVeKhuyenMai'] = (float)$_POST['giaVeKhuyenMai'];
                if ($data['giaVeKhuyenMai'] >= $data['giaVe']) {
                    $errors[] = 'Giá vé khuyến mãi phải nhỏ hơn giá vé thường.';
                }
            }
        }

        // Start date
        if (empty($_POST['ngayBatDau'])) {
            $errors[] = 'Vui lòng chọn ngày bắt đầu.';
        } else {
            $data['ngayBatDau'] = $_POST['ngayBatDau'];
        }

        // End date
        if (empty($_POST['ngayKetThuc'])) {
            $errors[] = 'Vui lòng chọn ngày kết thúc.';
        } else {
            $data['ngayKetThuc'] = $_POST['ngayKetThuc'];

            // Validate date range
            if (!empty($data['ngayBatDau']) && $data['ngayKetThuc'] <= $data['ngayBatDau']) {
                $errors[] = 'Ngày kết thúc phải sau ngày bắt đầu.';
            }
        }

        // Description
        $data['moTa'] = trim($_POST['moTa'] ?? '');

        // Status
        $data['trangThai'] = $_POST['trangThai'] ?? 'Hoạt động';

        // Check for overlapping price configurations
        if (empty($errors)) {
            if (Price::priceExists(
                $data['maTuyenDuong'],
                $data['maLoaiPhuongTien'],
                $data['loaiChoNgoi'],
                $data['maLoaiVe'],
                $data['ngayBatDau'],
                $data['ngayKetThuc']
            )) {
                $errors[] = 'Đã tồn tại cấu hình giá vé tương tự cho loại phương tiện này trong khoảng thời gian này.';
            }
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . BASE_URL . '/prices/create');
            exit;
        }

        // Create price
        try {
            $priceId = Price::create($data);
            $this->syncChuyenXeGiaVe(); // gọi SP đồng bộ sau khi thêm giá vé
            $_SESSION['success'] = 'Thêm giá vé mới thành công.';
            header('Location: ' . BASE_URL . '/prices/' . $priceId);
        } catch (Exception $e) {
            $_SESSION['error'] = 'Có lỗi xảy ra khi thêm giá vé: ' . $e->getMessage();
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . BASE_URL . '/prices/create');
        }
        exit;
    }

    /**
     * Show edit price form
     */
    public function edit($id) {
        $this->checkAdminAccess();

        $price = Price::getById($id);

        if (!$price) {
            $_SESSION['error'] = 'Không tìm thấy giá vé.';
            header('Location: ' . BASE_URL . '/prices');
            exit;
        }

        $routes = Price::getAllRoutes();
        $ticketTypes = Price::getAllTicketTypes();
        $vehicleTypes = Price::getAllVehicleTypes();
        $seatTypes = Price::getSeatTypes();
        $statusOptions = Price::getStatusOptions();

        include __DIR__ . '/../views/prices/edit.php';
    }

    /**
     * Handle edit price form submission
     */
    public function update($id) {
        $this->checkAdminAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/prices/' . $id . '/edit');
            exit;
        }

        $price = Price::getById($id);
        if (!$price) {
            $_SESSION['error'] = 'Không tìm thấy giá vé.';
            header('Location: ' . BASE_URL . '/prices');
            exit;
        }

        // Validate input (same as create but updated for vehicle type ID)
        $errors = [];
        $data = [];

        // Route
        if (empty($_POST['maTuyenDuong'])) {
            $errors[] = 'Vui lòng chọn tuyến đường.';
        } else {
            $data['maTuyenDuong'] = (int)$_POST['maTuyenDuong'];
        }

        // Vehicle Type
        if (empty($_POST['maLoaiPhuongTien'])) {
            $errors[] = 'Vui lòng chọn loại phương tiện.';
        } else {
            $data['maLoaiPhuongTien'] = (int)$_POST['maLoaiPhuongTien'];
        }

        // Seat type
        if (empty($_POST['loaiChoNgoi'])) {
            $errors[] = 'Vui lòng chọn loại chỗ ngồi.';
        } else {
            $data['loaiChoNgoi'] = trim($_POST['loaiChoNgoi']);
        }

        // Ticket type
        if (empty($_POST['maLoaiVe'])) {
            $errors[] = 'Vui lòng chọn loại vé.';
        } else {
            $data['maLoaiVe'] = (int)$_POST['maLoaiVe'];
        }

        // Main price
        if (empty($_POST['giaVe']) || !is_numeric($_POST['giaVe']) || $_POST['giaVe'] <= 0) {
            $errors[] = 'Vui lòng nhập giá vé hợp lệ.';
        } else {
            $data['giaVe'] = (float)$_POST['giaVe'];
        }

        // Promotional price (optional)
        if (!empty($_POST['giaVeKhuyenMai'])) {
            if (!is_numeric($_POST['giaVeKhuyenMai']) || $_POST['giaVeKhuyenMai'] <= 0) {
                $errors[] = 'Giá vé khuyến mãi không hợp lệ.';
            } else {
                $data['giaVeKhuyenMai'] = (float)$_POST['giaVeKhuyenMai'];
                if ($data['giaVeKhuyenMai'] >= $data['giaVe']) {
                    $errors[] = 'Giá vé khuyến mãi phải nhỏ hơn giá vé thường.';
                }
            }
        }

        // Start date
        if (empty($_POST['ngayBatDau'])) {
            $errors[] = 'Vui lòng chọn ngày bắt đầu.';
        } else {
            $data['ngayBatDau'] = $_POST['ngayBatDau'];
        }

        // End date
        if (empty($_POST['ngayKetThuc'])) {
            $errors[] = 'Vui lòng chọn ngày kết thúc.';
        } else {
            $data['ngayKetThuc'] = $_POST['ngayKetThuc'];

            // Validate date range
            if (!empty($data['ngayBatDau']) && $data['ngayKetThuc'] <= $data['ngayBatDau']) {
                $errors[] = 'Ngày kết thúc phải sau ngày bắt đầu.';
            }
        }

        // Description
        $data['moTa'] = trim($_POST['moTa'] ?? '');

        // Status
        $data['trangThai'] = $_POST['trangThai'] ?? 'Hoạt động';

        // Check for overlapping price configurations (exclude current record)
        if (empty($errors)) {
            if (Price::priceExists(
                $data['maTuyenDuong'],
                $data['maLoaiPhuongTien'],
                $data['loaiChoNgoi'],
                $data['maLoaiVe'],
                $data['ngayBatDau'],
                $data['ngayKetThuc'],
                $id
            )) {
                $errors[] = 'Đã tồn tại cấu hình giá vé tương tự cho loại phương tiện này trong khoảng thời gian này.';
            }
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . BASE_URL . '/prices/' . $id . '/edit');
            exit;
        }

        // Update price
        try {
            Price::update($id, $data);
            $this->syncChuyenXeGiaVe(); // gọi SP đồng bộ sau khi cập nhật giá vé
            $_SESSION['success'] = 'Cập nhật giá vé thành công.';
            header('Location: ' . BASE_URL . '/prices/' . $id);
        } catch (Exception $e) {
            $_SESSION['error'] = 'Có lỗi xảy ra khi cập nhật giá vé: ' . $e->getMessage();
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . BASE_URL . '/prices/' . $id . '/edit');
        }
        exit;
    }

    /**
     * Delete price (set to expired)
     */
    public function delete($id) {
        $this->checkAdminAccess();

        $price = Price::getById($id);
        if (!$price) {
            $_SESSION['error'] = 'Không tìm thấy giá vé.';
            header('Location: ' . BASE_URL . '/prices');
            exit;
        }

        try {
            Price::delete($id);
            $this->syncChuyenXeGiaVe(); // gọi SP đồng bộ sau khi xóa giá vé
            $_SESSION['success'] = 'Đã vô hiệu hóa giá vé thành công.';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Có lỗi xảy ra khi xóa giá vé: ' . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/prices');
        exit;
    }

    /**
     * Advanced search
     */
    public function search() {
        $this->checkAdminAccess();

        $criteria = [
            'search' => $_GET['search'] ?? '',
            'routeId' => $_GET['route'] ?? '',
            'vehicleTypeId' => $_GET['vehicle_type'] ?? '',
            'seatType' => $_GET['seat_type'] ?? '',
            'ticketType' => $_GET['ticket_type'] ?? '',
            'status' => $_GET['status'] ?? '',
            'minPrice' => $_GET['min_price'] ?? '',
            'maxPrice' => $_GET['max_price'] ?? '',
            'dateFrom' => $_GET['date_from'] ?? '',
            'dateTo' => $_GET['date_to'] ?? ''
        ];

        $prices = Price::search($criteria);

        // Get filter options
        $routes = Price::getAllRoutes();
        $ticketTypes = Price::getAllTicketTypes();
        $vehicleTypes = Price::getAllVehicleTypes();
        $seatTypes = Price::getSeatTypes();
        $statusOptions = Price::getStatusOptions();

        // Get statistics
        $stats = Price::getStats();

        include __DIR__ . '/../views/prices/index.php';
    }

    /**
     * Export prices to CSV
     */
    public function export() {
        $this->checkAdminAccess();

        $prices = Price::getAll();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="danh_sach_gia_ve_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // Add BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // CSV headers
        fputcsv($output, [
            'Mã giá vé',
            'Tuyến đường',
            'Phương tiện',
            'Loại chỗ ngồi',
            'Loại vé',
            'Giá vé (VNĐ)',
            'Giá khuyến mãi (VNĐ)',
            'Ngày bắt đầu',
            'Ngày kết thúc',
            'Trạng thái',
            'Mô tả'
        ]);

        // CSV data
        foreach ($prices as $price) {
            fputcsv($output, [
                $price['maGiaVe'],
                $price['kyHieuTuyen'] . ' (' . $price['diemDi'] . ' - ' . $price['diemDen'] . ')',
                $price['tenLoaiPhuongTien'],
                $price['loaiChoNgoi'],
                $price['tenLoaiVe'],
                $price['giaVe'],
                $price['giaVeKhuyenMai'] ?? '',
                $price['ngayBatDau'],
                $price['ngayKetThuc'],
                $price['trangThai'],
                $price['moTa']
            ]);
        }

        fclose($output);
        exit;
    }
}
?>