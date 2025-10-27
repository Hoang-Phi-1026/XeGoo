<?php
require_once __DIR__ . '/../models/Route.php';
require_once __DIR__ . '/../models/RoutePoint.php';
require_once __DIR__ . '/../config/config.php';

class RouteController {
    
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
     * Display route list
     */
    public function index() {
        $this->checkAdminAccess();
        
        // Build search criteria from GET parameters
        $criteria = [
            'diemDi' => $_GET['diemDi'] ?? '',
            'diemDen' => $_GET['diemDen'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];
        
        // Remove empty criteria
        $criteria = array_filter($criteria, function($value) {
            return !empty($value);
        });
        
        // Get routes using search function
        if (!empty($criteria)) {
            $routes = Route::search($criteria);
        } else {
            // If no search criteria, get all routes
            $routes = Route::getAll();
        }
        
        // Get statistics
        $stats = Route::getStats();
        
        // Get status options for filter
        $statusOptions = Route::getStatusOptions();
        
        // Get unique start and end points for dropdowns
        $startPoints = Route::getUniqueStartPoints();
        $endPoints = Route::getUniqueEndPoints();
        
        // Load view
        include __DIR__ . '/../views/routes/index.php';
    }
    
    /**
     * Show route details
     */
    public function show($id) {
        $this->checkAdminAccess();
        
        $route = Route::getByIdWithPoints($id);
        
        if (!$route) {
            $_SESSION['error'] = 'Không tìm thấy tuyến đường.';
            header('Location: ' . BASE_URL . '/routes');
            exit;
        }
        
        include __DIR__ . '/../views/routes/show.php';
    }
    
    /**
     * Show add route form
     */
    public function create() {
        $this->checkAdminAccess();
        
        $statusOptions = Route::getStatusOptions();
        $popularCities = Route::getPopularCities();
        $pointTypes = RoutePoint::getPointTypes();
        
        include __DIR__ . '/../views/routes/create.php';
    }
    
    /**
     * Handle add route form submission
     */
    public function store() {
        $this->checkAdminAccess();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/routes/create');
            exit;
        }
        
        // Validate input
        $errors = [];
        $data = [];
        
        // Route code
        if (empty($_POST['kyHieuTuyen'])) {
            $errors[] = 'Vui lòng nhập ký hiệu tuyến.';
        } else {
            $kyHieuTuyen = strtoupper(trim($_POST['kyHieuTuyen']));
            if (Route::routeCodeExists($kyHieuTuyen)) {
                $errors[] = 'Ký hiệu tuyến đã tồn tại.';
            } else {
                $data['kyHieuTuyen'] = $kyHieuTuyen;
            }
        }
        
        // Departure point
        if (empty($_POST['diemDi'])) {
            $errors[] = 'Vui lòng nhập điểm đi.';
        } else {
            $data['diemDi'] = trim($_POST['diemDi']);
        }
        
        // Destination point
        if (empty($_POST['diemDen'])) {
            $errors[] = 'Vui lòng nhập điểm đến.';
        } else {
            $data['diemDen'] = trim($_POST['diemDen']);
        }
        
        // Travel time validation
        if (empty($_POST['thoiGianDiChuyen'])) {
            $errors[] = 'Vui lòng nhập thời gian di chuyển.';
        } else {
            $travelTime = trim($_POST['thoiGianDiChuyen']);
            // Validate time format (HH:MM)
            if (!preg_match('/^([0-9]{1,2}):([0-5][0-9])$/', $travelTime)) {
                $errors[] = 'Thời gian di chuyển không đúng định dạng (HH:MM).';
            } else {
                $data['thoiGianDiChuyen'] = $travelTime . ':00'; // Add seconds
            }
        }
        
        // Distance
        if (empty($_POST['khoangCach']) || !is_numeric($_POST['khoangCach']) || $_POST['khoangCach'] <= 0) {
            $errors[] = 'Vui lòng nhập khoảng cách hợp lệ (km).';
        } else {
            $data['khoangCach'] = (int)$_POST['khoangCach'];
        }
        
        // Description (optional)
        $data['moTa'] = trim($_POST['moTa'] ?? '');
        
        // Status
        $data['trangThai'] = $_POST['trangThai'] ?? 'Đang hoạt động';
        
        $points = [];
        
        // Process pickup points
        if (!empty($_POST['pickup_points'])) {
            foreach ($_POST['pickup_points'] as $index => $pointName) {
                if (!empty(trim($pointName))) {
                    $points[] = [
                        'tenDiem' => trim($pointName),
                        'loaiDiem' => 'Đón',
                        'diaChi' => trim($_POST['pickup_addresses'][$index] ?? ''),
                        'thuTu' => $index + 1,
                        'trangThai' => 'Hoạt động'
                    ];
                }
            }
        }
        
        // Process drop-off points
        if (!empty($_POST['dropoff_points'])) {
            foreach ($_POST['dropoff_points'] as $index => $pointName) {
                if (!empty(trim($pointName))) {
                    $points[] = [
                        'tenDiem' => trim($pointName),
                        'loaiDiem' => 'Trả',
                        'diaChi' => trim($_POST['dropoff_addresses'][$index] ?? ''),
                        'thuTu' => $index + 1,
                        'trangThai' => 'Hoạt động'
                    ];
                }
            }
        }
        
        // Validate points
        foreach ($points as $point) {
            $pointErrors = RoutePoint::validatePoint($point);
            $errors = array_merge($errors, $pointErrors);
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . BASE_URL . '/routes/create');
            exit;
        }
        
        // Create route with points
        try {
            $routeId = Route::createWithPoints($data, $points);
            $_SESSION['success'] = 'Thêm tuyến đường mới thành công.';
            header('Location: ' . BASE_URL . '/routes/' . $routeId);
        } catch (Exception $e) {
            $_SESSION['error'] = 'Có lỗi xảy ra khi thêm tuyến đường: ' . $e->getMessage();
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . BASE_URL . '/routes/create');
        }
        exit;
    }
    
    /**
     * Show edit route form
     */
    public function edit($id) {
        $this->checkAdminAccess();
        
        $route = Route::getByIdWithPoints($id);
        
        if (!$route) {
            $_SESSION['error'] = 'Không tìm thấy tuyến đường.';
            header('Location: ' . BASE_URL . '/routes');
            exit;
        }
        
        $statusOptions = Route::getStatusOptions();
        $popularCities = Route::getPopularCities();
        $pointTypes = RoutePoint::getPointTypes();
        
        include __DIR__ . '/../views/routes/edit.php';
    }
    
    /**
     * Handle edit route form submission
     */
    public function update($id) {
        $this->checkAdminAccess();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/routes/' . $id . '/edit');
            exit;
        }
        
        $route = Route::getById($id);
        if (!$route) {
            $_SESSION['error'] = 'Không tìm thấy tuyến đường.';
            header('Location: ' . BASE_URL . '/routes');
            exit;
        }
        
        // Validate input
        $errors = [];
        $data = [];
        
        // Route code
        if (empty($_POST['kyHieuTuyen'])) {
            $errors[] = 'Vui lòng nhập ký hiệu tuyến.';
        } else {
            $kyHieuTuyen = strtoupper(trim($_POST['kyHieuTuyen']));
            if (Route::routeCodeExists($kyHieuTuyen, $id)) {
                $errors[] = 'Ký hiệu tuyến đã tồn tại.';
            } else {
                $data['kyHieuTuyen'] = $kyHieuTuyen;
            }
        }
        
        // Departure point
        if (empty($_POST['diemDi'])) {
            $errors[] = 'Vui lòng nhập điểm đi.';
        } else {
            $data['diemDi'] = trim($_POST['diemDi']);
        }
        
        // Destination point
        if (empty($_POST['diemDen'])) {
            $errors[] = 'Vui lòng nhập điểm đến.';
        } else {
            $data['diemDen'] = trim($_POST['diemDen']);
        }
        
        // Travel time validation
        if (empty($_POST['thoiGianDiChuyen'])) {
            $errors[] = 'Vui lòng nhập thời gian di chuyển.';
        } else {
            $travelTime = trim($_POST['thoiGianDiChuyen']);
            // Validate time format (HH:MM)
            if (!preg_match('/^([0-9]{1,2}):([0-5][0-9])$/', $travelTime)) {
                $errors[] = 'Thời gian di chuyển không đúng định dạng (HH:MM).';
            } else {
                $data['thoiGianDiChuyen'] = $travelTime . ':00'; // Add seconds
            }
        }
        
        // Distance
        if (empty($_POST['khoangCach']) || !is_numeric($_POST['khoangCach']) || $_POST['khoangCach'] <= 0) {
            $errors[] = 'Vui lòng nhập khoảng cách hợp lệ (km).';
        } else {
            $data['khoangCach'] = (int)$_POST['khoangCach'];
        }
        
        // Description (optional)
        $data['moTa'] = trim($_POST['moTa'] ?? '');
        
        // Status
        $data['trangThai'] = $_POST['trangThai'] ?? 'Đang hoạt động';
        
        if ($data['trangThai'] === 'Ngừng khai thác' && $route['trangThai'] !== 'Ngừng khai thác') {
            if (Route::hasActiveFutureTrips($id)) {
                $errors[] = 'Không thể ngừng khai thác tuyến đường vì vẫn còn chuyến xe ở trạng thái "Sẵn sàng" trong tương lai. Vui lòng hủy hoặc hoàn thành các chuyến xe trước.';
            }
        }
        
        $points = [];
        
        // Process pickup points
        if (!empty($_POST['pickup_points'])) {
            foreach ($_POST['pickup_points'] as $index => $pointName) {
                if (!empty(trim($pointName))) {
                    $points[] = [
                        'tenDiem' => trim($pointName),
                        'loaiDiem' => 'Đón',
                        'diaChi' => trim($_POST['pickup_addresses'][$index] ?? ''),
                        'thuTu' => $index + 1,
                        'trangThai' => 'Hoạt động'
                    ];
                }
            }
        }
        
        // Process drop-off points
        if (!empty($_POST['dropoff_points'])) {
            foreach ($_POST['dropoff_points'] as $index => $pointName) {
                if (!empty(trim($pointName))) {
                    $points[] = [
                        'tenDiem' => trim($pointName),
                        'loaiDiem' => 'Trả',
                        'diaChi' => trim($_POST['dropoff_addresses'][$index] ?? ''),
                        'thuTu' => $index + 1,
                        'trangThai' => 'Hoạt động'
                    ];
                }
            }
        }
        
        // Validate points
        foreach ($points as $point) {
            $pointErrors = RoutePoint::validatePoint($point);
            $errors = array_merge($errors, $pointErrors);
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . BASE_URL . '/routes/' . $id . '/edit');
            exit;
        }
        
        // Update route with points
        try {
            Route::updateWithPoints($id, $data, $points);
            $_SESSION['success'] = 'Cập nhật tuyến đường thành công.';
            header('Location: ' . BASE_URL . '/routes/' . $id);
        } catch (Exception $e) {
            $_SESSION['error'] = 'Có lỗi xảy ra khi cập nhật tuyến đường: ' . $e->getMessage();
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . BASE_URL . '/routes/' . $id . '/edit');
        }
        exit;
    }
    
    /**
     * Delete route (set to inactive)
     */
    public function delete($id) {
        $this->checkAdminAccess();
        
        $route = Route::getById($id);
        if (!$route) {
            $_SESSION['error'] = 'Không tìm thấy tuyến đường.';
            header('Location: ' . BASE_URL . '/routes');
            exit;
        }
        
        if (Route::hasActiveFutureTrips($id)) {
            $_SESSION['error'] = 'Không thể ngừng khai thác tuyến đường vì vẫn còn chuyến xe ở trạng thái "Sẵn sàng" trong tương lai. Vui lòng hủy hoặc hoàn thành các chuyến xe trước.';
            header('Location: ' . BASE_URL . '/routes');
            exit;
        }
        
        try {
            Route::deleteWithPoints($id);
            $_SESSION['success'] = 'Đã chuyển tuyến đường sang trạng thái ngừng khai thác.';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Có lỗi xảy ra khi xóa tuyến đường: ' . $e->getMessage();
        }
        
        header('Location: ' . BASE_URL . '/routes');
        exit;
    }
}
