<?php
require_once __DIR__ . '/../models/Trip.php';
require_once __DIR__ . '/../config/config.php';

class TripController {
    
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
     * Display trip list
     */
    public function index() {
        $this->checkAdminAccess();
        
        // Get filter parameters
        $scheduleFilter = $_GET['schedule'] ?? null;
        $vehicleFilter = $_GET['vehicle'] ?? null;
        $statusFilter = $_GET['status'] ?? null;
        $search = $_GET['search'] ?? '';
        
        // Get trips
        $trips = Trip::getAll($scheduleFilter, $vehicleFilter, $statusFilter, $search);
        
        // Get statistics
        $stats = Trip::getStats();
        
        // Get filter options
        $schedules = Trip::getSchedulesForFilter();
        $vehicles = Trip::getVehiclesForFilter();
        $statusOptions = Trip::getStatusOptions();
        
        // Load view
        include __DIR__ . '/../views/trips/index.php';
    }
    
    /**
     * Show trip details
     */
    public function show($id) {
        $this->checkAdminAccess();
        
        $trip = Trip::getById($id);
        
        if (!$trip) {
            $_SESSION['error'] = 'Không tìm thấy chuyến xe.';
            header('Location: ' . BASE_URL . '/trips');
            exit;
        }
        
        // Get pickup/dropoff points
        $points = Trip::getTripPoints($id);
        
        include __DIR__ . '/../views/trips/show.php';
    }
    
    /**
     * Update trip status
     */
    public function updateStatus($id) {
        $this->checkAdminAccess();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/trips/' . $id);
            exit;
        }
        
        $trip = Trip::getById($id);
        if (!$trip) {
            $_SESSION['error'] = 'Không tìm thấy chuyến xe.';
            header('Location: ' . BASE_URL . '/trips');
            exit;
        }
        
        $newStatus = $_POST['trangThai'] ?? '';
        $validStatuses = array_keys(Trip::getStatusOptions());
        
        if (!in_array($newStatus, $validStatuses)) {
            $_SESSION['error'] = 'Trạng thái không hợp lệ.';
            header('Location: ' . BASE_URL . '/trips/' . $id);
            exit;
        }
        
        try {
            Trip::updateStatus($id, $newStatus);
            $_SESSION['success'] = 'Cập nhật trạng thái chuyến xe thành công.';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Có lỗi xảy ra khi cập nhật trạng thái: ' . $e->getMessage();
        }
        
        header('Location: ' . BASE_URL . '/trips/' . $id);
        exit;
    }
    
    /**
     * Delete trip
     */
    public function delete($id) {
        $this->checkAdminAccess();
        
        $trip = Trip::getById($id);
        if (!$trip) {
            $_SESSION['error'] = 'Không tìm thấy chuyến xe.';
            header('Location: ' . BASE_URL . '/trips');
            exit;
        }
        
        // Check if trip can be deleted (only if not departed or completed)
        if (in_array($trip['trangThai'], ['Đã khởi hành', 'Hoàn thành'])) {
            $_SESSION['error'] = 'Không thể xóa chuyến xe đã khởi hành hoặc hoàn thành.';
            header('Location: ' . BASE_URL . '/trips/' . $id);
            exit;
        }
        
        try {
            Trip::delete($id);
            $_SESSION['success'] = 'Xóa chuyến xe thành công.';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Có lỗi xảy ra khi xóa chuyến xe: ' . $e->getMessage();
        }
        
        header('Location: ' . BASE_URL . '/trips');
        exit;
    }
    
    /**
     * Export trips to CSV
     */
    public function export() {
        $this->checkAdminAccess();
        
        // Get filter parameters
        $scheduleFilter = $_GET['schedule'] ?? null;
        $vehicleFilter = $_GET['vehicle'] ?? null;
        $statusFilter = $_GET['status'] ?? null;
        $search = $_GET['search'] ?? '';
        
        // Get trips data
        $trips = Trip::getAll($scheduleFilter, $vehicleFilter, $statusFilter, $search);
        
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="danh_sach_chuyen_xe_' . date('Y-m-d') . '.csv"');
        
        // Create output stream
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // CSV headers
        fputcsv($output, [
            'ID Chuyến xe',
            'Tên lịch trình',
            'Tuyến đường',
            'Biển số xe',
            'Loại xe',
            'Ngày khởi hành',
            'Giờ khởi hành',
            'Giờ kết thúc',
            'Số chỗ tổng',
            'Số chỗ đã đặt',
            'Số chỗ trống',
            'Tỷ lệ lấp đầy (%)',
            'Giá vé',
            'Trạng thái'
        ]);
        
        // Add data rows
        foreach ($trips as $trip) {
            fputcsv($output, [
                $trip['maChuyenXe'],
                $trip['tenLichTrinh'],
                $trip['kyHieuTuyen'] . ' - ' . $trip['diemDi'] . ' → ' . $trip['diemDen'],
                $trip['bienSo'],
                $trip['tenLoaiPhuongTien'],
                date('d/m/Y', strtotime($trip['ngayKhoiHanh'])),
                date('H:i', strtotime($trip['thoiGianKhoiHanh'])),
                date('H:i', strtotime($trip['thoiGianKetThuc'])),
                $trip['soChoTong'],
                $trip['soChoDaDat'],
                $trip['soChoTrong'],
                Trip::calculateOccupancy($trip['soChoDaDat'], $trip['soChoTong']),
                number_format($trip['giaVe'] ?? 0, 0, ',', '.') . ' VNĐ',
                $trip['trangThai']
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Show trip statistics dashboard
     */
    public function statistics() {
        $this->checkAdminAccess();
        
        // Get date range from parameters
        $startDate = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
        $endDate = $_GET['end_date'] ?? date('Y-m-d'); // Today
        
        // Get trips in date range
        $trips = Trip::getTripsByDateRange($startDate, $endDate);
        
        // Calculate statistics
        $stats = [
            'total_trips' => count($trips),
            'total_seats' => array_sum(array_column($trips, 'soChoTong')),
            'booked_seats' => array_sum(array_column($trips, 'soChoDaDat')),
            'revenue' => 0
        ];
        
        // Calculate revenue and occupancy
        foreach ($trips as $trip) {
            if ($trip['giaVe']) {
                $stats['revenue'] += $trip['giaVe'] * $trip['soChoDaDat'];
            }
        }
        
        $stats['occupancy_rate'] = $stats['total_seats'] > 0 ? 
            round(($stats['booked_seats'] / $stats['total_seats']) * 100, 1) : 0;
        
        // Group by status
        $statusStats = [];
        foreach (Trip::getStatusOptions() as $status => $label) {
            $statusStats[$status] = count(array_filter($trips, function($trip) use ($status) {
                return $trip['trangThai'] == $status;
            }));
        }
        
        include __DIR__ . '/../views/trips/statistics.php';
    }
}
?>
