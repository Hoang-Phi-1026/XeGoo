<?php
require_once __DIR__ . '/../config/database.php';

class Booking {

    /**
     * Lấy thông tin chi tiết vé để gửi email
     * 
     * @param int $bookingId ID đặt vé
     * @return array|null Dữ liệu đặt vé hoặc null nếu lỗi
     */
    public static function getTicketDetailsForEmail($bookingId) {
        try {
            error_log("[v0] Booking::getTicketDetailsForEmail() - Bắt đầu lấy thông tin đặt vé ID: $bookingId");

            // === LẤY THÔNG TIN ĐẶT VÉ CHÍNH ===
            $bookingSql = "
                SELECT 
                    dv.maDatVe,
                    dv.maNguoiDung,
                    dv.soLuongVe,
                    dv.tongTien,
                    dv.giamGia,
                    dv.tongTienSauGiam,
                    dv.phuongThucThanhToan,
                    dv.loaiDatVe,
                    dv.trangThai,
                    dv.ngayDat,
                    nd.tenNguoiDung,
                    nd.eMail AS emailNguoiDung,
                    nd.soDienThoai
                FROM datve dv
                LEFT JOIN nguoidung nd ON dv.maNguoiDung = nd.maNguoiDung
                WHERE dv.maDatVe = ?
            ";

            $booking = fetch($bookingSql, [$bookingId]);
            if (!$booking) {
                error_log("[v0] ❌ Không tìm thấy đặt vé với ID: $bookingId");
                return null;
            }

            // === LẤY CHI TIẾT VÉ (CÓ GIÁ VÉ) ===
            $ticketsSql = "
                SELECT 
                    dv.maDatVe,
                    dv.soLuongVe,
                    dv.tongTienSauGiam,
                    dv.phuongThucThanhToan,
                    dv.trangThai AS datVeTrangThai,
                    dv.ngayDat,

                    cd.maChiTiet,
                    cd.maChuyenXe,
                    cd.maGhe,
                    cd.hoTenHanhKhach,
                    cd.emailHanhKhach,
                    cd.soDienThoaiHanhKhach,
                    cd.giaVe AS seatPrice,

                    g.soGhe,
                    c.ngayKhoiHanh,
                    c.thoiGianKhoiHanh,
                    c.maPhuongTien,

                    t.kyHieuTuyen,
                    t.diemDi,
                    t.diemDen,

                    dd.tenDiem AS diemDonTen,
                    dd.diaChi AS diemDonDiaChi,
                    dt.tenDiem AS diemTraTen,
                    dt.diaChi AS diemTraDiaChi,

                    p.bienSo,
                    lp.tenLoaiPhuongTien,
                    lp.soChoMacDinh
                FROM datve dv
                INNER JOIN chitiet_datve cd ON dv.maDatVe = cd.maDatVe
                INNER JOIN ghe g ON cd.maGhe = g.maGhe
                INNER JOIN chuyenxe c ON cd.maChuyenXe = c.maChuyenXe
                INNER JOIN lichtrinh l ON c.maLichTrinh = l.maLichTrinh
                INNER JOIN tuyenduong t ON l.maTuyenDuong = t.maTuyenDuong
                INNER JOIN phuongtien p ON c.maPhuongTien = p.maPhuongTien
                INNER JOIN loaiphuongtien lp ON p.maLoaiPhuongTien = lp.maLoaiPhuongTien
                LEFT JOIN tuyenduong_diemdontra dd ON cd.maDiemDon = dd.maDiem
                LEFT JOIN tuyenduong_diemdontra dt ON cd.maDiemTra = dt.maDiem
                WHERE dv.maDatVe = ?
                ORDER BY c.thoiGianKhoiHanh ASC
            ";

            $tickets = [];
            try {
                $tickets = fetchAll($ticketsSql, [$bookingId]);
            } catch (Exception $ex) {
                error_log("💥 Lỗi Booking::getTicketDetailsForEmail (ticket query): " . $ex->getMessage());
                return null;
            }

            if (empty($tickets)) {
                error_log("[v0] ⚠️ Không tìm thấy vé cho ID đặt vé: $bookingId");
                return null;
            }

            // === LẤY EMAIL HÀNH KHÁCH ===
            $passengerEmails = [];
            foreach ($tickets as $ticket) {
                if (!empty($ticket['emailHanhKhach']) && filter_var($ticket['emailHanhKhach'], FILTER_VALIDATE_EMAIL)) {
                    $passengerEmails[] = $ticket['emailHanhKhach'];
                }
            }
            $passengerEmails = array_unique($passengerEmails);

            $result = [
                'maDatVe' => $booking['maDatVe'],
                'ngayDat' => $booking['ngayDat'],
                'tongTienSauGiam' => $booking['tongTienSauGiam'],
                'phuongThucThanhToan' => $booking['phuongThucThanhToan'],
                'emailNguoiDung' => $booking['emailNguoiDung'],
                'passengerEmails' => $passengerEmails,
                'tickets' => $tickets
            ];

            error_log("[v0] ✅ Đã lấy dữ liệu vé thành công cho booking ID: $bookingId - Tổng vé: " . count($tickets));
            return $result;

        } catch (Exception $e) {
            error_log("💥 Lỗi Booking::getTicketDetailsForEmail: " . $e->getMessage());
            return null;
        }
    }
}
?>
