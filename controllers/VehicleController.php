<?php
require_once __DIR__ . '/../models/Vehicle.php';
require_once __DIR__ . '/../config/config.php';

class VehicleController {
    
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
     * Display vehicle list
     */
    public function index() {
        $this->checkAdminAccess();
        
        // Get simple search parameters
        $search = $_GET['search'] ?? '';
        $vehicleType = $_GET['vehicleType'] ?? '';
        
        // Build search criteria - only use the simple fields
        $criteria = [
            'search' => $search,
            'vehicleType' => $vehicleType
        ];
        
        // Remove empty criteria
        $criteria = array_filter($criteria, function($value) {
            return !empty($value);
        });
        
        // Get vehicles using search
        if (!empty($criteria)) {
            $vehicles = Vehicle::search($criteria);
        } else {
            $vehicles = Vehicle::getAll();
        }
        
        // Get statistics
        $stats = Vehicle::getStats();
        
        // Get dropdown options
        $vehicleTypes = Vehicle::getVehicleTypes();
        
        // Load view
        include __DIR__ . '/../views/vehicles/index.php';
    }
    
    /**
     * Show vehicle details
     */
    public function show($id) {
        $this->checkAdminAccess();
        
        $vehicle = Vehicle::getById($id);
        
        if (!$vehicle) {
            $_SESSION['error'] = 'Không tìm thấy phương tiện.';
            header('Location: ' . BASE_URL . '/vehicles');
            exit;
        }
        
        include __DIR__ . '/../views/vehicles/show.php';
    }
    
    /**
     * Show add vehicle form
     */
    public function create() {
        $this->checkAdminAccess();
        
        $vehicleTypes = Vehicle::getVehicleTypes();
        $statusOptions = Vehicle::getStatusOptions();
        
        include __DIR__ . '/../views/vehicles/create.php';
    }
    
    /**
     * Handle add vehicle form submission
     */
    public function store() {
        $this->checkAdminAccess();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/vehicles/create');
            exit;
        }
        
        // Validate input
        $errors = [];
        $data = [];
        
        // Vehicle type
        if (empty($_POST['maLoaiPhuongTien'])) {
            $errors[] = 'Vui lòng chọn loại phương tiện.';
        } else {
            $data['maLoaiPhuongTien'] = (int)$_POST['maLoaiPhuongTien'];
        }
        
        // License plate
        if (empty($_POST['bienSo'])) {
            $errors[] = 'Vui lòng nhập biển số xe.';
        } else {
            $bienSo = trim($_POST['bienSo']);
            if (Vehicle::licensePlateExists($bienSo)) {
                $errors[] = 'Biển số xe đã tồn tại.';
            } else {
                $data['bienSo'] = $bienSo;
            }
        }
        
        // Status
        $data['trangThai'] = $_POST['trangThai'] ?? 'Đang hoạt động';
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . BASE_URL . '/vehicles/create');
            exit;
        }
        
        // Create vehicle
        try {
            $vehicleId = Vehicle::create($data);
            $_SESSION['success'] = 'Thêm phương tiện mới thành công.';
            header('Location: ' . BASE_URL . '/vehicles/' . $vehicleId);
        } catch (Exception $e) {
            $_SESSION['error'] = 'Có lỗi xảy ra khi thêm phương tiện: ' . $e->getMessage();
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . BASE_URL . '/vehicles/create');
        }
        exit;
    }
    
    /**
     * Show edit vehicle form
     */
    public function edit($id) {
        $this->checkAdminAccess();
        
        $vehicle = Vehicle::getById($id);
        
        if (!$vehicle) {
            $_SESSION['error'] = 'Không tìm thấy phương tiện.';
            header('Location: ' . BASE_URL . '/vehicles');
            exit;
        }
        
        $vehicleTypes = Vehicle::getVehicleTypes();
        $statusOptions = Vehicle::getStatusOptions();
        
        include __DIR__ . '/../views/vehicles/edit.php';
    }
    
    /**
     * Handle edit vehicle form submission
     */
    public function update($id) {
        $this->checkAdminAccess();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/vehicles/' . $id . '/edit');
            exit;
        }
        
        $vehicle = Vehicle::getById($id);
        if (!$vehicle) {
            $_SESSION['error'] = 'Không tìm thấy phương tiện.';
            header('Location: ' . BASE_URL . '/vehicles');
            exit;
        }
        
        // Validate input
        $errors = [];
        $data = [];
        
        // Vehicle type
        if (empty($_POST['maLoaiPhuongTien'])) {
            $errors[] = 'Vui lòng chọn loại phương tiện.';
        } else {
            $data['maLoaiPhuongTien'] = (int)$_POST['maLoaiPhuongTien'];
        }
        
        // License plate
        if (empty($_POST['bienSo'])) {
            $errors[] = 'Vui lòng nhập biển số xe.';
        } else {
            $bienSo = trim($_POST['bienSo']);
            if (Vehicle::licensePlateExists($bienSo, $id)) {
                $errors[] = 'Biển số xe đã tồn tại.';
            } else {
                $data['bienSo'] = $bienSo;
            }
        }
        
        // Status
        $data['trangThai'] = $_POST['trangThai'] ?? 'Đang hoạt động';
        
        if ($data['trangThai'] === 'Bảo trì' && $vehicle['trangThai'] !== 'Bảo trì') {
            if (Vehicle::hasTripsWithBookings($id)) {
                $errors[] = 'Không thể chuyển phương tiện sang trạng thái bảo trì vì phương tiện này có các chuyến xe đã có khách hàng mua vé.';
            }
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . BASE_URL . '/vehicles/' . $id . '/edit');
            exit;
        }
        
        // Update vehicle
        try {
            Vehicle::update($id, $data);
            $_SESSION['success'] = 'Cập nhật phương tiện thành công.';
            header('Location: ' . BASE_URL . '/vehicles/' . $id);
        } catch (Exception $e) {
            $_SESSION['error'] = 'Có lỗi xảy ra khi cập nhật phương tiện: ' . $e->getMessage();
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . BASE_URL . '/vehicles/' . $id . '/edit');
        }
        exit;
    }
    
    /**
     * Delete vehicle (set to maintenance)
     */
    public function delete($id) {
        $this->checkAdminAccess();
        
        $vehicle = Vehicle::getById($id);
        if (!$vehicle) {
            $_SESSION['error'] = 'Không tìm thấy phương tiện.';
            header('Location: ' . BASE_URL . '/vehicles');
            exit;
        }
        
        if (Vehicle::hasTripsWithBookings($id)) {
            $_SESSION['error'] = 'Không thể chuyển phương tiện sang trạng thái bảo trì vì phương tiện này có các chuyến xe đã có khách hàng mua vé.';
            header('Location: ' . BASE_URL . '/vehicles/' . $id);
            exit;
        }
        
        try {
            Vehicle::delete($id);
            $_SESSION['success'] = 'Đã chuyển phương tiện sang trạng thái bảo trì.';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Có lỗi xảy ra khi xóa phương tiện: ' . $e->getMessage();
        }
        
        header('Location: ' . BASE_URL . '/vehicles');
        exit;
    }
}
?>
