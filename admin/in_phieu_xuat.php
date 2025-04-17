<?php
// --- KIỂM SOÁT OUTPUT BUFFERING ---
// Bắt đầu buffer để có thể xóa output rác trước khi gửi header PDF
ob_start();

// --- NẠP THƯ VIỆN VÀ KẾT NỐI CSDL ---
// Đảm bảo các đường dẫn này chính xác trong cấu trúc thư mục của bạn
require_once('../vendor/autoload.php');
include_once('../connection/connect_database.php');

// --- LẤY VÀ KIỂM TRA ID PHIẾU XUẤT TỪ URL ---
$idPX = isset($_GET['idPX']) ? intval($_GET['idPX']) : 0;
if ($idPX <= 0) {
    ob_end_clean(); // Xóa buffer trước khi dừng
    header('Content-Type: text/plain; charset=utf-8'); // Đặt header text để hiển thị lỗi đúng tiếng Việt
    die("Mã phiếu xuất không hợp lệ!");
}

// --- LẤY THÔNG TIN PHIẾU XUẤT (LOGIC GỐC) ---
$query_px = "SELECT * FROM phieuxuat WHERE idPX = $idPX"; // Chỉ lấy từ bảng phieuxuat
$result_px = mysqli_query($conn, $query_px);
if (!$result_px) {
     ob_end_clean();
     header('Content-Type: text/plain; charset=utf-8');
     die("Lỗi truy vấn phiếu xuất: " . mysqli_error($conn));
}
$phieuxuat = mysqli_fetch_assoc($result_px);

if (!$phieuxuat) {
    ob_end_clean();
    header('Content-Type: text/plain; charset=utf-8');
    die("Lỗi: Không tìm thấy thông tin cho phiếu xuất #{$idPX}!");
}
// Không giải phóng $result_px vội nếu còn dùng $phieuxuat['idSP']

// --- LẤY THÔNG TIN SẢN PHẨM TỪ BẢNG SANPHAM (LOGIC GỐC) ---
$tenSP = "N/A";
$giaBanHienTai = 0; // Giá bán lấy từ bảng sản phẩm
if (isset($phieuxuat['idSP'])) {
    $sql_sp = "SELECT TenSP, GiaBan FROM sanpham WHERE idSP = " . intval($phieuxuat['idSP']);
    $rs_sp = mysqli_query($conn, $sql_sp);
    if($rs_sp && $sp = mysqli_fetch_assoc($rs_sp)) {
        $tenSP = htmlspecialchars($sp['TenSP']);
        $giaBanHienTai = floatval($sp['GiaBan']); // Lấy giá bán hiện tại
    }
    if($rs_sp) mysqli_free_result($rs_sp);
}

// --- CHUẨN BỊ DỮ LIỆU ĐỂ HIỂN THỊ (THEO LOGIC GỐC) ---
$ngayXuatFormatted = date('d/m/Y', strtotime($phieuxuat['NgayXuat'])); // *** Chỉ lấy Ngày/Tháng/Năm ***
$tenKH = htmlspecialchars($phieuxuat['TenKhachHang'] ?? 'Khách lẻ');
$soLuong = $phieuxuat['SoLuong'];
// Đơn giá hiển thị là Giá Bán hiện tại của sản phẩm
$donGiaXuatFormatted = number_format($giaBanHienTai, 0, ',', '.');
// Tổng tiền TÍNH LẠI dựa trên Giá Bán hiện tại
$tongTienTinhLai = $giaBanHienTai * $soLuong;
$tongTienFormatted = number_format($tongTienTinhLai, 0, ',', '.');
// Giả sử người lập phiếu là người trong hệ thống (nếu bạn có lưu idNguoiLap)
// Nếu không, bạn cần lấy thông tin người lập từ nguồn khác hoặc để trống
// Ví dụ lấy từ session (nếu người đang xem là người lập)
// session_start(); // Cần có session_start() ở đầu nếu dùng session
// $tenNguoiLap = isset($_SESSION['HoTenK']) ? htmlspecialchars($_SESSION['HoTenK']) : '...........................';
// Hoặc để trống nếu không xác định được
$tenNguoiLap = '...........................'; // Placeholder

// Giải phóng bộ nhớ query phiếu xuất
mysqli_free_result($result_px);


// --- KHỞI TẠO ĐỐI TƯỢNG TCPDF ---
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Tiến TC Beauty Store');
$pdf->SetTitle('Hóa Đơn Phiếu Xuất #' . $idPX);
$pdf->SetSubject('Hóa Đơn Bán Hàng');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(true);
$pdf->SetMargins(15, 10, 15);
$pdf->SetHeaderMargin(0);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 15);
$pdf->SetFont('dejavusans', '', 10);
$pdf->AddPage();

// --- VẼ HEADER TÙY CHỈNH (Thông tin cửa hàng, tiêu đề phiếu) ---
$pdf->SetY(10);
$pdf->SetFont('dejavusans', 'B', 18);
$pdf->Cell(0, 9, 'TIẾN TC BEAUTY STORE', 0, 1, 'C');
$pdf->SetFont('dejavusans', '', 10);
$pdf->Cell(0, 5, 'Địa chỉ: Hẻm 51, P.Xuân Khánh, Q.Ninh Kiều, TP.Cần Thơ', 0, 1, 'C');
$pdf->Cell(0, 5, 'Điện thoại: 0832 623 352', 0, 1, 'C');
$pdf->Ln(4);
$pdf->SetLineStyle(array('width' => 0.5, 'color' => array(50, 50, 50)));
$pdf->Line(15, $pdf->GetY(), $pdf->GetPageWidth() - 15, $pdf->GetY());
$pdf->Ln(5);
$pdf->SetFont('dejavusans', 'B', 15);
$pdf->Cell(0, 10, 'HÓA ĐƠN BÁN HÀNG', 0, 1, 'C');
$pdf->SetFont('dejavusans', '', 11);
$pdf->Cell(0, 5, 'Số: PX' . $idPX, 0, 1, 'C');
$pdf->Ln(8);

// --- TẠO NỘI DUNG HTML CHO PHẦN THÂN PHIẾU ---
$pdf->SetFont('dejavusans', '', 10);

$html = <<<HTML
<style>
    /* CSS chung */
    body { font-family: 'dejavusans', sans-serif; font-size: 10pt; color: #333; line-height: 1.4; }
    table { border-collapse: collapse; width: 100%; }

    /* Bảng chi tiết sản phẩm */
    table.details { border: 1px solid #888; }
    table.details th, table.details td {
        padding: 8px; /* Padding đồng đều */
        vertical-align: middle;
        font-size: 10pt;
        border: 1px solid #ccc; /* *** Viền kẻ ô màu xám nhạt *** */
    }
    table.details th {
        background-color: #f2f2f2; /* Nền xám nhạt cho header */
        color: #333; /* Chữ đen */
        font-weight: bold;
        /* text-align sẽ dùng align attribute trong HTML */
    }

    /* Bảng thông tin chung */
    table.info-table td { border: none; padding: 3px 0px; vertical-align: top; }
    table.info-table strong { font-weight: bold; }

    /* Khu vực tổng cộng */
    .total-section { margin-top: 25px; } /* Khoảng cách trên */
    .total-section table { border: none; } /* Bảng tổng cộng không có viền ngoài */
    .total-section table td { border: none; font-size: 11pt; padding: 5px 0px; }
    .total-section strong { font-weight: bold; }
    .total-row td { border-top: 1px solid #666; padding-top: 8px; } /* Chỉ có đường kẻ trên */

    /* Ghi chú */
    .notes-section { margin-top: 15px; }
    .notes { padding: 10px; border: 1px solid #eee; margin-top: 5px; min-height: 30px; background-color:#fdfdfd; }

    /* Chữ ký */
    .signature-section { margin-top: 40px; }
    .signature-section table td { border: none; text-align: center; padding-top: 10px; }
    .signature-section strong { font-weight: bold; }
    .signature-space { height: 50px; display: block; }

    /* Tiêu đề mục */
    h3.section-title { font-size: 11pt; font-weight: bold; margin-top: 15px; margin-bottom: 8px; border-bottom: 1px solid #ccc; padding-bottom: 3px; color: #333; }
</style>

<h3 class="section-title">THÔNG TIN KHÁCH HÀNG & GIAO DỊCH</h3>
<table class="info-table">
    <tr><td width="25%"><strong>Ngày xuất:</strong></td><td width="75%">{$ngayXuatFormatted}</td></tr>
    <tr><td><strong>Khách hàng:</strong></td><td><strong>{$tenKH}</strong></td></tr>
    </table>
<br>

<h3 class="section-title">CHI TIẾT SẢN PHẨM</h3>
<table class="details">
    <thead>
        <tr>
            <th align="center">Tên Sản Phẩm</th>
            <th align="center">Số Lượng</th>
            <th align="center">Đơn Giá (VNĐ)</th>
            <th align="center">Thành Tiền (VNĐ)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td align="center">{$tenSP}</td>
            <td align="center">{$soLuong}</td>
            <td align="center">{$donGiaXuatFormatted}</td>
            <td align="center">{$tongTienFormatted}</td>
        </tr>
    </tbody>
</table>
<br> <div class="total-section"> <table class="no-border">
         <tr class="total-row">
             <td align="right"><strong>TỔNG CỘNG THANH TOÁN:</strong></td>
             <td align="right"><strong>{$tongTienFormatted} VNĐ</strong></td>
         </tr>
    </table>
</div>

<br><br> <div class="signature-section">
    <table class="no-border">
         <tr>
             <td width="33%" align="center">
                 <strong>Người lập phiếu</strong><br><br><br><br>
                 <i>(Ký, ghi rõ họ tên)</i><br><br>
                 <strong>{$tenNguoiLap}</strong> </td>
             <td width="34%" align="center">
                 <strong>Người giao hàng</strong><br><br><br><br>
                 <i>(Ký, ghi rõ họ tên)</i><br><br>
                 <strong>{$tenNguoiLap}</strong> </td>
             <td width="33%" align="center">
                 <strong>Khách hàng</strong><br><br><br><br>
                 <i>(Ký, ghi rõ họ tên)</i><br><br>
                 {$tenKH} </td>
         </tr>
    </table>
</div>

HTML;

// --- GHI NỘI DUNG HTML VÀO PDF ---
$pdf->writeHTML($html, true, false, true, false, '');

// --- DỌN DẸP BUFFER VÀ XUẤT PDF ---
ob_end_clean();
$pdf->Output('PhieuXuatKho_' . $idPX . '.pdf', 'I');

mysqli_close($conn);
exit;
?>