<?php
require_once __DIR__ . '/../models/Schedule.php';
require_once __DIR__ . '/../config/config.php';

class ScheduleController {
    
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
     * Display schedule list
     */
    public function index() {
        $this->checkAdminAccess();
        
        // Get filter parameters
        $routeFilter = $_GET['route'] ?? null;
        $search = $_GET['search'] ?? '';
        
        // Get schedules
        $schedules = Schedule::getAll($routeFilter, $search);
        
        // Get statistics
        $stats = Schedule::getStats();
        
        // Get routes for filter dropdown
        $routes = Schedule::getAllRoutes();
        
        // Get status options for filter
        $statusOptions = Schedule::getStatusOptions();
        
        // Load view
        include __DIR__ . '/../views/schedules/index.php';
    }
    
    /**
     * Show schedule details
     */
    public function show($id) {
        $this->checkAdminAccess();
        
        $schedule = Schedule::getById($id);
        
        if (!$schedule) {
            $_SESSION['error'] = 'Không tìm thấy lịch trình.';
            header('Location: ' . BASE_URL . '/schedules');
            exit;
        }
        
        include __DIR__ . '/../views/schedules/show.php';
    }
    
    /**
     * Show add schedule form
     */
    public function create() {
        $this->checkAdminAccess();
        
        $routes = Schedule::getAllRoutes();
        $statusOptions = Schedule::getStatusOptions();
        
        include __DIR__ . '/../views/schedules/create.php';
    }
    
    /**
     * Handle add schedule form submission
     */
    public function store() {
        $this->checkAdminAccess();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/schedules/create');
            exit;
        }
        
        // Prepare data
        $data = [
            'maTuyenDuong' => $_POST['maTuyenDuong'] ?? '',
            'tenLichTrinh' => trim($_POST['tenLichTrinh'] ?? ''),
            'gioKhoiHanh' => $_POST['gioKhoiHanh'] ?? '',
            'gioKetThuc' => $_POST['gioKetThuc'] ?? '',
            'ngayBatDau' => $_POST['ngayBatDau'] ?? '',
            'ngayKetThuc' => $_POST['ngayKetThuc'] ?? '',
            'moTa' => trim($_POST['moTa'] ?? ''),
            'trangThai' => $_POST['trangThai'] ?? 'Hoạt động'
        ];
        
        // Process days of week
        $selectedDays = $_POST['thuTrongTuan'] ?? [];
        $data['thuTrongTuan'] = implode(',', $selectedDays);
        
        // Validate input
        $errors = Schedule::validate($data);
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . BASE_URL . '/schedules/create');
            exit;
        }
        
        // Create schedule
        try {
            $scheduleId = Schedule::create($data);
            $_SESSION['success'] = 'Thêm lịch trình mới thành công.';
            header('Location: ' . BASE_URL . '/schedules/' . $scheduleId);
        } catch (Exception $e) {
            $_SESSION['error'] = 'Có lỗi xảy ra khi thêm lịch trình: ' . $e->getMessage();
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . BASE_URL . '/schedules/create');
        }
        exit;
    }
    
    /**
     * Show edit schedule form
     */
    public function edit($id) {
        $this->checkAdminAccess();
        
        $schedule = Schedule::getById($id);
        
        if (!$schedule) {
            $_SESSION['error'] = 'Không tìm thấy lịch trình.';
            header('Location: ' . BASE_URL . '/schedules');
            exit;
        }
        
        $routes = Schedule::getAllRoutes();
        $statusOptions = Schedule::getStatusOptions();
        
        include __DIR__ . '/../views/schedules/edit.php';
    }
    
    /**
     * Handle edit schedule form submission
     */
    public function update($id) {
        $this->checkAdminAccess();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/schedules/' . $id . '/edit');
            exit;
        }
        
        $schedule = Schedule::getById($id);
        if (!$schedule) {
            $_SESSION['error'] = 'Không tìm thấy lịch trình.';
            header('Location: ' . BASE_URL . '/schedules');
            exit;
        }
        
        // Prepare data
        $data = [
            'maTuyenDuong' => $_POST['maTuyenDuong'] ?? '',
            'tenLichTrinh' => trim($_POST['tenLichTrinh'] ?? ''),
            'gioKhoiHanh' => $_POST['gioKhoiHanh'] ?? '',
            'gioKetThuc' => $_POST['gioKetThuc'] ?? '',
            'ngayBatDau' => $_POST['ngayBatDau'] ?? '',
            'ngayKetThuc' => $_POST['ngayKetThuc'] ?? '',
            'moTa' => trim($_POST['moTa'] ?? ''),
            'trangThai' => $_POST['trangThai'] ?? 'Hoạt động'
        ];
        
        // Process days of week
        $selectedDays = $_POST['thuTrongTuan'] ?? [];
        $data['thuTrongTuan'] = implode(',', $selectedDays);
        
        // Validate input
        $errors = Schedule::validate($data);
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . BASE_URL . '/schedules/' . $id . '/edit');
            exit;
        }
        
        // Update schedule
        try {
            Schedule::update($id, $data);
            $_SESSION['success'] = 'Cập nhật lịch trình thành công.';
            header('Location: ' . BASE_URL . '/schedules/' . $id);
        } catch (Exception $e) {
            $_SESSION['error'] = 'Có lỗi xảy ra khi cập nhật lịch trình: ' . $e->getMessage();
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . BASE_URL . '/schedules/' . $id . '/edit');
        }
        exit;
    }
    
    /**
     * Delete schedule (set to inactive)
     */
    public function delete($id) {
        $this->checkAdminAccess();
        
        $schedule = Schedule::getById($id);
        if (!$schedule) {
            $_SESSION['error'] = 'Không tìm thấy lịch trình.';
            header('Location: ' . BASE_URL . '/schedules');
            exit;
        }
        
        try {
            Schedule::delete($id);
            $_SESSION['success'] = 'Đã ngừng lịch trình thành công.';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Có lỗi xảy ra khi xóa lịch trình: ' . $e->getMessage();
        }
        
        header('Location: ' . BASE_URL . '/schedules');
        exit;
    }
    
    /**
     * Show trip generation form
     */
    public function generateTrips() {
        $this->checkAdminAccess();
        
        $schedules = Schedule::getSchedulesForGeneration();
        $vehicles = $this->getAvailableVehicles();
        
        include __DIR__ . '/../views/schedules/generate-trips.php';
    }
    
    /**
     * Handle trip generation
     */
    public function processGenerateTrips() {
        $this->checkAdminAccess();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/schedules/generate-trips');
            exit;
        }
        
        $scheduleId = $_POST['maLichTrinh'] ?? '';
        $vehicleId = $_POST['maPhuongTien'] ?? '';
        $seatType = $_POST['loaiChoNgoi'] ?? '';
        
        if (empty($scheduleId) || empty($vehicleId) || empty($seatType)) {
            $_SESSION['error'] = 'Vui lòng điền đầy đủ thông tin.';
            header('Location: ' . BASE_URL . '/schedules/generate-trips');
            exit;
        }
        
        $validationErrors = $this->validateTripGeneration($scheduleId, $vehicleId);
        if (!empty($validationErrors)) {
            $_SESSION['error'] = implode('<br>', $validationErrors);
            header('Location: ' . BASE_URL . '/schedules/generate-trips');
            exit;
        }
        
        try {
            // Call stored procedure to generate trips
            $sql = "CALL sp_generate_chuyenxe(?, ?, ?)";
            query($sql, [$scheduleId, $vehicleId, $seatType]);
        
            // Lấy danh sách các chuyến xe mới tạo (cùng lịch trình + phương tiện)
            $newTrips = fetchAll("
                SELECT maChuyenXe 
                FROM chuyenxe 
                WHERE maLichTrinh = ? 
                  AND maPhuongTien = ?
                  AND ngayTao >= NOW() - INTERVAL 1 MINUTE
            ", [$scheduleId, $vehicleId]);
        
            // Gọi procedure sinh ghế cho từng chuyến xe
            foreach ($newTrips as $trip) {
                query("CALL sp_generate_chuyenxe_ghe(?)", [$trip['maChuyenXe']]);
            }
        
            $_SESSION['success'] = 'Sinh chuyến xe thành công! Các chuyến đã có danh sách ghế.';
            header('Location: ' . BASE_URL . '/trips');
        } catch (Exception $e) {
            $_SESSION['error'] = 'Có lỗi xảy ra khi sinh chuyến xe: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/schedules/generate-trips');
        }
        exit;
    }
    
    /**
     * AJAX endpoint to validate trip generation
     */
    public function validateTrips() {
        $this->checkAdminAccess();
        
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'errors' => ['Phương thức không hợp lệ']]);
            exit;
        }
        
        $scheduleId = $_POST['maLichTrinh'] ?? '';
        $vehicleId = $_POST['maPhuongTien'] ?? '';
        
        if (empty($scheduleId) || empty($vehicleId)) {
            echo json_encode(['success' => false, 'errors' => ['Thiếu thông tin lịch trình hoặc phương tiện']]);
            exit;
        }
        
        $errors = $this->validateTripGeneration($scheduleId, $vehicleId);
        
        if (empty($errors)) {
            echo json_encode(['success' => true, 'message' => 'Validation passed']);
        } else {
            echo json_encode(['success' => false, 'errors' => $errors]);
        }
        exit;
    }
    
    /**
     * Validate trip generation constraints
     */
    private function validateTripGeneration($scheduleId, $vehicleId) {
        $errors = [];
        
        // 1. Kiểm tra xe có trạng thái khả dụng
        $vehicleStatus = $this->checkVehicleStatus($vehicleId);
        if (!$vehicleStatus['available']) {
            $errors[] = "Xe không thể sinh chuyến: " . $vehicleStatus['reason'];
        }
        
        // 2. Kiểm tra xung đột thời gian
        $timeConflicts = $this->checkTimeConflicts($scheduleId, $vehicleId);
        if (!empty($timeConflicts)) {
            foreach ($timeConflicts as $conflict) {
                $errors[] = "Xung đột thời gian: Xe đã có chuyến từ " . 
                           date('H:i d/m/Y', strtotime($conflict['thoiGianKhoiHanh'])) . 
                           " đến " . date('H:i d/m/Y', strtotime($conflict['thoiGianKetThuc'])) . 
                           " (Lịch trình: " . $conflict['tenLichTrinh'] . ")";
            }
        }
        
        // 3. Kiểm tra xe có kịp quay lại điểm xuất phát
        $returnTimeIssues = $this->checkVehicleReturnTime($scheduleId, $vehicleId);
        if (!empty($returnTimeIssues)) {
            foreach ($returnTimeIssues as $issue) {
                $errors[] = "Xe không kịp quay lại: " . $issue;
            }
        }
        
        return $errors;
    }
    
    /**
     * Check if vehicle is available for trip generation
     */
    private function checkVehicleStatus($vehicleId) {
        $sql = "SELECT trangThai, bienSo FROM phuongtien WHERE maPhuongTien = ?";
        $vehicle = fetch($sql, [$vehicleId]);
        
        if (!$vehicle) {
            return ['available' => false, 'reason' => 'Không tìm thấy phương tiện'];
        }
        
        if ($vehicle['trangThai'] !== 'Đang hoạt động') {
            $statusMap = [
                'Bảo trì' => 'Xe đang trong tình trạng bảo trì',
                'Hỏng hóc' => 'Xe đang bị hỏng hóc'
            ];
            $reason = $statusMap[$vehicle['trangThai']] ?? 'Xe không ở trạng thái hoạt động';
            return ['available' => false, 'reason' => $reason . " (Biển số: " . $vehicle['bienSo'] . ")"];
        }
        
        return ['available' => true, 'reason' => ''];
    }
    
    /**
     * Check for time conflicts with existing trips
     */
    private function checkTimeConflicts($scheduleId, $vehicleId) {
        // Lấy thông tin lịch trình mới
        $newSchedule = fetch("
            SELECT l.*, t.diemDi, t.diemDen 
            FROM lichtrinh l 
            JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong 
            WHERE l.maLichTrinh = ?
        ", [$scheduleId]);
        
        if (!$newSchedule) {
            return [];
        }
        
        // Kiểm tra các chuyến xe hiện có của xe này trong khoảng thời gian
        $sql = "
            SELECT c.*, l.tenLichTrinh, t.diemDi, t.diemDen
            FROM chuyenxe c
            JOIN lichtrinh l ON c.maLichTrinh = l.maLichTrinh
            JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong
            WHERE c.maPhuongTien = ? 
            AND c.ngayKhoiHanh BETWEEN ? AND ?
            AND c.trangThai NOT IN ('Hủy', 'Hoàn thành')
        ";
        
        $existingTrips = fetchAll($sql, [
            $vehicleId, 
            $newSchedule['ngayBatDau'], 
            $newSchedule['ngayKetThuc']
        ]);
        
        $conflicts = [];
        
        foreach ($existingTrips as $trip) {
            // Kiểm tra overlap thời gian trong ngày
            $newStart = $newSchedule['gioKhoiHanh'];
            $newEnd = $newSchedule['gioKetThuc'];
            $existingStart = date('H:i:s', strtotime($trip['thoiGianKhoiHanh']));
            $existingEnd = date('H:i:s', strtotime($trip['thoiGianKetThuc']));
            
            // Kiểm tra xem có overlap không
            if (($newStart < $existingEnd) && ($newEnd > $existingStart)) {
                $conflicts[] = $trip;
            }
        }
        
        return $conflicts;
    }
    
    /**
     * Check if vehicle can return to starting point in time
     */
    private function checkVehicleReturnTime($scheduleId, $vehicleId) {
        $issues = [];
        
        // Lấy thông tin lịch trình mới
        $newSchedule = fetch("
            SELECT l.*, t.diemDi, t.diemDen, t.thoiGianDiChuyen
            FROM lichtrinh l 
            JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong 
            WHERE l.maLichTrinh = ?
        ", [$scheduleId]);
        
        if (!$newSchedule) {
            return $issues;
        }
        
        // Lấy các chuyến xe hiện có của xe này
        $sql = "
            SELECT c.*, l.tenLichTrinh, t.diemDi, t.diemDen, t.thoiGianDiChuyen
            FROM chuyenxe c
            JOIN lichtrinh l ON c.maLichTrinh = l.maLichTrinh
            JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong
            WHERE c.maPhuongTien = ? 
            AND c.ngayKhoiHanh BETWEEN ? AND ?
            AND c.trangThai NOT IN ('Hủy', 'Hoàn thành')
            ORDER BY c.thoiGianKhoiHanh
        ";
        
        $existingTrips = fetchAll($sql, [
            $vehicleId, 
            $newSchedule['ngayBatDau'], 
            $newSchedule['ngayKetThuc']
        ]);
        
        // Tạo danh sách tất cả các chuyến (bao gồm cả chuyến mới sẽ được tạo)
        $allTrips = $existingTrips;
        
        // Mô phỏng các chuyến mới sẽ được tạo
        $currentDate = new DateTime($newSchedule['ngayBatDau']);
        $endDate = new DateTime($newSchedule['ngayKetThuc']);
        $daysOfWeek = explode(',', $newSchedule['thuTrongTuan']);
        
        while ($currentDate <= $endDate) {
            $dayOfWeek = $this->getDayOfWeekCode($currentDate->format('w'));
            
            if (in_array($dayOfWeek, $daysOfWeek)) {
                $newTrip = [
                    'ngayKhoiHanh' => $currentDate->format('Y-m-d'),
                    'thoiGianKhoiHanh' => $currentDate->format('Y-m-d') . ' ' . $newSchedule['gioKhoiHanh'],
                    'thoiGianKetThuc' => $currentDate->format('Y-m-d') . ' ' . $newSchedule['gioKetThuc'],
                    'diemDi' => $newSchedule['diemDi'],
                    'diemDen' => $newSchedule['diemDen'],
                    'tenLichTrinh' => $newSchedule['tenLichTrinh'],
                    'thoiGianDiChuyen' => $newSchedule['thoiGianDiChuyen']
                ];
                $allTrips[] = $newTrip;
            }
            
            $currentDate->add(new DateInterval('P1D'));
        }
        
        // Sắp xếp theo thời gian
        usort($allTrips, function($a, $b) {
            return strtotime($a['thoiGianKhoiHanh']) - strtotime($b['thoiGianKhoiHanh']);
        });
        
        // Kiểm tra từng cặp chuyến liên tiếp
        for ($i = 0; $i < count($allTrips) - 1; $i++) {
            $currentTrip = $allTrips[$i];
            $nextTrip = $allTrips[$i + 1];
            
            // Kiểm tra nếu điểm đến của chuyến hiện tại khác điểm đi của chuyến tiếp theo
            if ($currentTrip['diemDen'] !== $nextTrip['diemDi']) {
                // Cần thời gian để xe quay lại
                $currentEndTime = new DateTime($currentTrip['thoiGianKetThuc']);
                $nextStartTime = new DateTime($nextTrip['thoiGianKhoiHanh']);
                
                // Thời gian cần thiết để quay lại (giả sử bằng thời gian di chuyển của tuyến)
                $returnTimeNeeded = new DateInterval('PT' . $this->timeToMinutes($currentTrip['thoiGianDiChuyen']) . 'M');
                $earliestNextStart = clone $currentEndTime;
                $earliestNextStart->add($returnTimeNeeded);
                
                if ($nextStartTime < $earliestNextStart) {
                    $issues[] = "Xe không kịp di chuyển từ " . $currentTrip['diemDen'] . 
                               " về " . $nextTrip['diemDi'] . " trước chuyến " . 
                               date('H:i d/m/Y', strtotime($nextTrip['thoiGianKhoiHanh'])) . 
                               " (Cần thêm " . $this->minutesToTime($this->timeToMinutes($currentTrip['thoiGianDiChuyen'])) . " để quay lại)";
                }
            }
        }
        
        return $issues;
    }
    
    /**
     * Convert day of week number to code
     */
    private function getDayOfWeekCode($dayNumber) {
        $map = [
            0 => 'CN', // Sunday
            1 => '2',  // Monday
            2 => '3',  // Tuesday
            3 => '4',  // Wednesday
            4 => '5',  // Thursday
            5 => '6',  // Friday
            6 => '7'   // Saturday
        ];
        return $map[$dayNumber] ?? '';
    }
    
    /**
     * Convert time string to minutes
     */
    private function timeToMinutes($timeString) {
        $parts = explode(':', $timeString);
        return ($parts[0] * 60) + $parts[1];
    }
    
    /**
     * Convert minutes to time string
     */
    private function minutesToTime($minutes) {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return sprintf('%02d:%02d', $hours, $mins);
    }
    
    /**
     * Get available vehicles for trip generation
     */
    private function getAvailableVehicles() {
        $sql = "SELECT p.maPhuongTien, p.bienSo, lpt.tenLoaiPhuongTien, lpt.soChoMacDinh, lpt.loaiChoNgoiMacDinh
                FROM phuongtien p 
                JOIN loaiphuongtien lpt ON p.maLoaiPhuongTien = lpt.maLoaiPhuongTien 
                WHERE p.trangThai = 'Đang hoạt động'
                ORDER BY lpt.tenLoaiPhuongTien, p.bienSo";
        return fetchAll($sql);
    }
}
?>
