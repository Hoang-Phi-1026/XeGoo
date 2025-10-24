<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class GroupRentalController {
    
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Show group rental form page
     */
    public function index() {
        try {
            // Get vehicle types from database
            $vehicleTypes = $this->getVehicleTypes();
            
            $viewData = compact('vehicleTypes');
            extract($viewData);
            
            $viewFile = __DIR__ . '/../views/group-rental/index.php';
            if (file_exists($viewFile)) {
                include $viewFile;
            } else {
                echo "<h1>Lỗi: Không tìm thấy file view: $viewFile</h1>";
            }
            
        } catch (Exception $e) {
            error_log("[v0] GroupRentalController index error: " . $e->getMessage());
            echo "<h1>Lỗi trong GroupRentalController::index()</h1>";
            echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
        }
    }

    /**
     * Process group rental form submission
     */
    public function submit() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/group-rental');
            exit;
        }

        try {
            error_log("[v0] GroupRentalController::submit() started");
            error_log("[v0] POST data: " . json_encode($_POST));

            // Validate required fields
            $errors = $this->validateFormData($_POST);
            
            if (!empty($errors)) {
                error_log("[v0] Validation errors: " . json_encode($errors));
                $_SESSION['rental_errors'] = $errors;
                $_SESSION['rental_data'] = $_POST;
                header('Location: ' . BASE_URL . '/group-rental');
                exit;
            }

            // Extract and sanitize form data
            $hoTenNguoiThue = trim($_POST['ho_ten'] ?? '');
            $soDienThoaiNguoiThue = trim($_POST['so_dien_thoai'] ?? '');
            $emailNguoiThue = trim($_POST['email'] ?? '');
            $diemDi = trim($_POST['diem_di'] ?? '');
            $diemDen = trim($_POST['diem_den'] ?? '');
            $loaiHanhTrinh = $_POST['loai_hanh_trinh'] ?? 'Một chiều';
            $ngayDi = $_POST['ngay_di'] ?? '';
            $gioDi = $_POST['gio_di'] ?? '';
            $diemDonDi = trim($_POST['diem_don_di'] ?? '');
            $ngayVe = $_POST['ngay_ve'] ?? null;
            $gioVe = $_POST['gio_ve'] ?? null;
            $diemDonVe = trim($_POST['diem_don_ve'] ?? '');
            $soLuongNguoi = (int)($_POST['so_luong_nguoi'] ?? 0);
            $maLoaiPhuongTien = (int)($_POST['loai_xe'] ?? 0);
            $ghiChu = trim($_POST['ghi_chu'] ?? '');

            // Insert into database
            $sql = "INSERT INTO thuexe (
                hoTenNguoiThue, soDienThoaiNguoiThue, emailNguoiThue,
                diemDi, diemDen, loaiHanhTrinh,
                ngayDi, gioDi, diemDonDi,
                ngayVe, gioVe, diemDonVe,
                soLuongNguoi, maLoaiPhuongTien, ghiChu,
                trangThai, ngayTao
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Chờ duyệt', NOW())";

            $params = [
                $hoTenNguoiThue,
                $soDienThoaiNguoiThue,
                $emailNguoiThue,
                $diemDi,
                $diemDen,
                $loaiHanhTrinh,
                $ngayDi,
                $gioDi,
                $diemDonDi,
                $ngayVe,
                $gioVe,
                $loaiHanhTrinh === 'Khứ hồi' ? $diemDonVe : null,
                $soLuongNguoi,
                $maLoaiPhuongTien,
                $ghiChu
            ];

            $result = query($sql, $params);
            $maThuXe = lastInsertId();

            error_log("[v0] Group rental request created with ID: $maThuXe");

            try {
                require_once __DIR__ . '/../lib/EmailService.php';
                
                // Get the rental request data with vehicle type info
                $rentalData = $this->getRentalRequestById($maThuXe);
                
                if ($rentalData) {
                    $emailService = new EmailService();
                    $emailResult = $emailService->sendGroupRentalConfirmationEmail(
                        $emailNguoiThue,
                        $hoTenNguoiThue,
                        $rentalData
                    );
                    
                    if ($emailResult['success']) {
                        error_log("[v0] Confirmation email sent successfully for rental ID: $maThuXe");
                    } else {
                        error_log("[v0] Failed to send confirmation email: " . $emailResult['message']);
                    }
                }
            } catch (Exception $emailError) {
                error_log("[v0] Email sending exception: " . $emailError->getMessage());
                error_log("[v0] Email error trace: " . $emailError->getTraceAsString());
            }

            // Clear session data
            unset($_SESSION['rental_errors']);
            unset($_SESSION['rental_data']);

            // Redirect to success page
            header('Location: ' . BASE_URL . '/group-rental/success/' . $maThuXe);
            exit;

        } catch (Exception $e) {
            error_log("[v0] GroupRentalController submit error: " . $e->getMessage());
            error_log("[v0] Stack trace: " . $e->getTraceAsString());
            $_SESSION['error'] = 'Có lỗi xảy ra khi gửi yêu cầu: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/group-rental');
            exit;
        }
    }

    /**
     * Show success page after submission
     */
    public function success($maThuXe) {
        try {
            $rentalRequest = $this->getRentalRequestById($maThuXe);
            
            if (!$rentalRequest) {
                $_SESSION['error'] = 'Không tìm thấy yêu cầu thuê xe.';
                header('Location: ' . BASE_URL . '/group-rental');
                exit;
            }

            $viewData = compact('rentalRequest', 'maThuXe');
            extract($viewData);

            $viewFile = __DIR__ . '/../views/group-rental/success.php';
            if (file_exists($viewFile)) {
                include $viewFile;
            } else {
                echo "<h1>Lỗi: Không tìm thấy file view: $viewFile</h1>";
            }

        } catch (Exception $e) {
            error_log("[v0] GroupRentalController success error: " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra.';
            header('Location: ' . BASE_URL . '/group-rental');
            exit;
        }
    }

    /**
     * Validate form data
     */
    private function validateFormData($data) {
        $errors = [];

        // Validate contact information
        if (empty($data['ho_ten'] ?? '')) {
            $errors[] = 'Vui lòng nhập họ tên người thuê xe.';
        }

        if (empty($data['so_dien_thoai'] ?? '')) {
            $errors[] = 'Vui lòng nhập số điện thoại.';
        } elseif (!preg_match('/^[0-9]{10,11}$/', $data['so_dien_thoai'])) {
            $errors[] = 'Số điện thoại không hợp lệ.';
        }

        if (empty($data['email'] ?? '')) {
            $errors[] = 'Vui lòng nhập email.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ.';
        }

        // Validate rental information
        if (empty($data['diem_di'] ?? '')) {
            $errors[] = 'Vui lòng chọn điểm đi.';
        }

        if (empty($data['diem_den'] ?? '')) {
            $errors[] = 'Vui lòng chọn điểm đến.';
        }

        if (empty($data['ngay_di'] ?? '')) {
            $errors[] = 'Vui lòng chọn ngày đi.';
        } else {
            $ngayDi = strtotime($data['ngay_di']);
            $today = strtotime(date('Y-m-d'));
            if ($ngayDi < $today) {
                $errors[] = 'Ngày đi không thể là ngày trong quá khứ.';
            }
        }

        if (empty($data['gio_di'] ?? '')) {
            $errors[] = 'Vui lòng chọn giờ đi.';
        }

        if (empty($data['diem_don_di'] ?? '')) {
            $errors[] = 'Vui lòng chọn điểm đón.';
        }

        // Validate round trip fields if applicable
        if (($data['loai_hanh_trinh'] ?? '') === 'Khứ hồi') {
            if (empty($data['ngay_ve'] ?? '')) {
                $errors[] = 'Vui lòng chọn ngày về.';
            } else {
                $ngayVe = strtotime($data['ngay_ve']);
                $ngayDi = strtotime($data['ngay_di'] ?? date('Y-m-d'));
                if ($ngayVe <= $ngayDi) {
                    $errors[] = 'Ngày về phải sau ngày đi.';
                }
            }

            if (empty($data['gio_ve'] ?? '')) {
                $errors[] = 'Vui lòng chọn giờ về.';
            }

            if (empty($data['diem_don_ve'] ?? '')) {
                $errors[] = 'Vui lòng chọn điểm đón về.';
            }
        }

        // Validate number of passengers
        if (empty($data['so_luong_nguoi'] ?? '')) {
            $errors[] = 'Vui lòng nhập số lượng người.';
        } elseif ((int)$data['so_luong_nguoi'] < 1) {
            $errors[] = 'Số lượng người phải lớn hơn 0.';
        }

        // Validate vehicle type
        if (empty($data['loai_xe'] ?? '')) {
            $errors[] = 'Vui lòng chọn loại xe.';
        }

        return $errors;
    }

    /**
     * Get vehicle types from database
     */
    private function getVehicleTypes() {
        try {
            $sql = "SELECT maLoaiPhuongTien, tenLoaiPhuongTien, soChoMacDinh, moTa 
                    FROM loaiphuongtien 
                    ORDER BY soChoMacDinh ASC";
            
            return fetchAll($sql);
        } catch (Exception $e) {
            error_log("[v0] getVehicleTypes error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get rental request by ID
     */
    private function getRentalRequestById($maThuXe) {
        try {
            $sql = "SELECT t.*, lpt.tenLoaiPhuongTien, lpt.soChoMacDinh
                    FROM thuexe t
                    LEFT JOIN loaiphuongtien lpt ON t.maLoaiPhuongTien = lpt.maLoaiPhuongTien
                    WHERE t.maThuXe = ?";
            
            return fetch($sql, [$maThuXe]);
        } catch (Exception $e) {
            error_log("[v0] getRentalRequestById error: " . $e->getMessage());
            return null;
        }
    }
}
