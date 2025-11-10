<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class PromotionalCodeController {
    
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

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
     * Display promotional codes list and creation form
     */
    public function index() {
        $this->checkAdminAccess();

        // Get filter parameters
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $type = $_GET['type'] ?? '';

        // Get all promotional codes
        $promotionalCodes = $this->getAllPromotionalCodes($search, $status, $type);

        // Get statistics
        $stats = $this->getPromotionalStats();

        // Load view
        include __DIR__ . '/../views/promotional-codes/index.php';
    }

    /**
     * Handle promotional code creation
     */
    public function store() {
        $this->checkAdminAccess();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/promotional-codes');
            exit;
        }

        // Validate input
        $errors = [];
        $data = [];

        // Promotion name (tenKhuyenMai)
        if (empty($_POST['tenKhuyenMai'])) {
            $errors[] = 'Vui lòng nhập tên khuyến mãi.';
        } else {
            $data['tenKhuyenMai'] = trim($_POST['tenKhuyenMai']);
        }

        // Promotion type (loai)
        if (empty($_POST['loai']) || !in_array($_POST['loai'], ['PhanTram', 'SoTienCoDinh'])) {
            $errors[] = 'Vui lòng chọn loại khuyến mãi hợp lệ.';
        } else {
            $data['loai'] = $_POST['loai'];
        }

        // Promotion value (giaTri)
        if (empty($_POST['giaTri']) || !is_numeric($_POST['giaTri']) || $_POST['giaTri'] <= 0) {
            $errors[] = 'Vui lòng nhập giá trị khuyến mãi hợp lệ.';
        } else {
            $data['giaTri'] = (float)$_POST['giaTri'];
        }

        // Validate giaTri based on loai
        if ($data['loai'] === 'PhanTram' && $data['giaTri'] > 100) {
            $errors[] = 'Phần trăm giảm không được vượt quá 100%.';
        }

        // Start date (ngayBatDau)
        if (empty($_POST['ngayBatDau'])) {
            $errors[] = 'Vui lòng chọn ngày bắt đầu.';
        } else {
            $data['ngayBatDau'] = $_POST['ngayBatDau'];
        }

        // End date (ngayKetThuc)
        if (empty($_POST['ngayKetThuc'])) {
            $errors[] = 'Vui lòng chọn ngày kết thúc.';
        } else {
            $data['ngayKetThuc'] = $_POST['ngayKetThuc'];

            if (!empty($data['ngayBatDau']) && $data['ngayKetThuc'] <= $data['ngayBatDau']) {
                $errors[] = 'Ngày kết thúc phải sau ngày bắt đầu.';
            }
        }

        // Target audience (doiTuongApDung)
        if (empty($_POST['doiTuongApDung']) || !in_array($_POST['doiTuongApDung'], ['Tất cả', 'Khách hàng thân thiết'])) {
            $errors[] = 'Vui lòng chọn đối tượng áp dụng hợp lệ.';
        } else {
            $data['doiTuongApDung'] = $_POST['doiTuongApDung'];
        }

        $data['soLanSuDungToiDa'] = !empty($_POST['soLanSuDungToiDa']) && is_numeric($_POST['soLanSuDungToiDa']) && $_POST['soLanSuDungToiDa'] > 0 ? (int)$_POST['soLanSuDungToiDa'] : 999999;
        $data['soLanSuDungToiDaMotNguoiDung'] = !empty($_POST['soLanSuDungToiDaMotNguoiDung']) && is_numeric($_POST['soLanSuDungToiDaMotNguoiDung']) && $_POST['soLanSuDungToiDaMotNguoiDung'] > 0 ? (int)$_POST['soLanSuDungToiDaMotNguoiDung'] : 1;

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . BASE_URL . '/promotional-codes');
            exit;
        }

        // Create promotional code
        try {
            $sql = "INSERT INTO khuyenmai (tenKhuyenMai, loai, giaTri, ngayBatDau, ngayKetThuc, doiTuongApDung, soLanSuDungToiDa, soLanSuDungToiDaMotNguoiDung) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            query($sql, [
                $data['tenKhuyenMai'],
                $data['loai'],
                $data['giaTri'],
                $data['ngayBatDau'],
                $data['ngayKetThuc'],
                $data['doiTuongApDung'],
                $data['soLanSuDungToiDa'],
                $data['soLanSuDungToiDaMotNguoiDung']
            ]);
            
            $_SESSION['success'] = 'Thêm mã khuyến mãi mới thành công.';
            header('Location: ' . BASE_URL . '/promotional-codes');
        } catch (Exception $e) {
            $_SESSION['error'] = 'Có lỗi xảy ra khi thêm mã khuyến mãi: ' . $e->getMessage();
            $_SESSION['form_data'] = $_POST;
            header('Location: ' . BASE_URL . '/promotional-codes');
        }
        exit;
    }

    /**
     * Delete promotional code
     */
    public function delete($id) {
        $this->checkAdminAccess();

        try {
            $sql = "DELETE FROM khuyenmai WHERE maKhuyenMai = ?";
            query($sql, [$id]);
            
            $_SESSION['success'] = 'Xóa mã khuyến mãi thành công.';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Có lỗi xảy ra khi xóa mã khuyến mãi: ' . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/promotional-codes');
        exit;
    }

    /**
     * Get all promotional codes with filtering
     */
    private function getAllPromotionalCodes($search = '', $status = '', $type = '') {
        $sql = "SELECT * FROM khuyenmai WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (tenKhuyenMai LIKE ? OR maKhuyenMai LIKE ?)";
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }

        if (!empty($type)) {
            $sql .= " AND loai = ?";
            $params[] = $type;
        }

        if (!empty($status)) {
            if ($status === 'active') {
                $sql .= " AND ngayBatDau <= CURDATE() AND ngayKetThuc >= CURDATE()";
            } elseif ($status === 'inactive') {
                $sql .= " AND (ngayBatDau > CURDATE() OR ngayKetThuc < CURDATE())";
            }
        }

        $sql .= " ORDER BY ngayBatDau DESC";

        return fetchAll($sql, $params);
    }

    /**
     * Get promotional statistics
     */
    private function getPromotionalStats() {
        $stats = [];

        // Total promotional codes
        $result = fetch("SELECT COUNT(*) as total FROM khuyenmai");
        $stats['total'] = $result['total'] ?? 0;

        // Active promotional codes
        $result = fetch(
            "SELECT COUNT(*) as active FROM khuyenmai 
             WHERE ngayBatDau <= CURDATE() AND ngayKetThuc >= CURDATE()"
        );
        $stats['active'] = $result['active'] ?? 0;

        // Inactive promotional codes
        $result = fetch(
            "SELECT COUNT(*) as inactive FROM khuyenmai 
             WHERE ngayBatDau > CURDATE() OR ngayKetThuc < CURDATE()"
        );
        $stats['inactive'] = $result['inactive'] ?? 0;

        // Percentage discount count
        $result = fetch("SELECT COUNT(*) as percent FROM khuyenmai WHERE loai = 'PhanTram'");
        $stats['percent'] = $result['percent'] ?? 0;

        return $stats;
    }
}
?>
