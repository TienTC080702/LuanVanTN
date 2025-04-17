<?php
// --- KIỂM SOÁT OUTPUT BUFFERING ---
ob_start();

// --- NẠP THƯ VIỆN VÀ KẾT NỐI CSDL ---
require_once('../vendor/autoload.php');
include_once('../connection/connect_database.php');

// --- LẤY VÀ KIỂM TRA ID PHIẾU NHẬP TỪ URL ---
$idPN = isset($_GET['idPN']) ? intval($_GET['idPN']) : 0;
if ($idPN <= 0) {
    ob_end_clean();
    header('Content-Type: text/plain; charset=utf-8');
    die("Mã phiếu nhập không hợp lệ!");
}

// --- TRUY VẤN CƠ SỞ DỮ LIỆU ---
$query = "SELECT
            pn.idPN, pn.NgayNhap, pn.SoLuong, pn.DonGiaNhap, pn.TongTien, pn.SoHoaDonNCC, pn.GhiChuPN,
            ncc.TenNCC, ncc.DiaChiNCC, ncc.DienThoaiNCC,
            sp.TenSP,
            usr.HoTenK AS TenNguoiLap
          FROM phieunhap AS pn
          LEFT JOIN nhacungcap AS ncc ON pn.idNCC = ncc.idNCC
          LEFT JOIN sanpham AS sp ON pn.idSP = sp.idSP
          LEFT JOIN users AS usr ON pn.idNguoiLap = usr.idUser
          WHERE pn.idPN = $idPN";

$result = mysqli_query($conn, $query);
if (!$result) {
     ob_end_clean();
     header('Content-Type: text/plain; charset=utf-8');
     die("Lỗi truy vấn CSDL: " . mysqli_error($conn));
}
$phieunhap = mysqli_fetch_assoc($result);
if (!$phieunhap) {
    ob_end_clean();
    header('Content-Type: text/plain; charset=utf-8');
    die("Lỗi: Không tìm thấy thông tin cho phiếu nhập #{$idPN}!");
}
mysqli_free_result($result);

// --- CHUẨN BỊ DỮ LIỆU ĐỂ HIỂN THỊ TRONG PDF ---
// *** SỬA ĐỊNH DẠNG NGÀY: Bỏ H:i ***
$ngayNhapFormatted = date('d/m/Y', strtotime($phieunhap['NgayNhap'])); // Chỉ lấy Ngày/Tháng/Năm
$tenNCC = htmlspecialchars($phieunhap['TenNCC'] ?? 'N/A');
$diaChiNCC = htmlspecialchars($phieunhap['DiaChiNCC'] ?? '');
$dienThoaiNCC = htmlspecialchars($phieunhap['DienThoaiNCC'] ?? '');
$soHoaDonNCC = htmlspecialchars($phieunhap['SoHoaDonNCC'] ?? 'Không có');
$ghiChuPN = !empty($phieunhap['GhiChuPN']) ? nl2br(htmlspecialchars($phieunhap['GhiChuPN'])) : '<i>Không có ghi chú.</i>';
$tenSP = htmlspecialchars($phieunhap['TenSP'] ?? 'N/A');
$soLuong = $phieunhap['SoLuong'];
$donGiaNhapFormatted = number_format($phieunhap['DonGiaNhap'] ?? 0, 0, ',', '.');
$tongTienFormatted = number_format($phieunhap['TongTien'] ?? 0, 0, ',', '.');
$tenNguoiLap = htmlspecialchars($phieunhap['TenNguoiLap'] ?? 'N/A');

// --- KHỞI TẠO ĐỐI TƯỢNG TCPDF (Giữ nguyên) ---
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Tiến TC Beauty Store');
$pdf->SetTitle('Phiếu Nhập Kho #' . $idPN);
$pdf->SetSubject('Phiếu Nhập Kho Sản Phẩm');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(true);
$pdf->SetMargins(15, 10, 15);
$pdf->SetHeaderMargin(0);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 15);
$pdf->SetFont('dejavusans', '', 10);
$pdf->AddPage();

// --- VẼ HEADER TÙY CHỈNH (Giữ nguyên) ---
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
$pdf->Cell(0, 10, 'PHIẾU NHẬP KHO', 0, 1, 'C');
$pdf->SetFont('dejavusans', '', 11);
$pdf->Cell(0, 5, 'Số: PN' . $idPN, 0, 1, 'C');
$pdf->Ln(8);

// --- TẠO NỘI DUNG HTML CHO PHẦN THÂN PHIẾU ---
$pdf->SetFont('dejavusans', '', 10);

$html = <<<HTML
<style>
    /* CSS chung (Giữ nguyên) */
    body { font-family: 'dejavusans', sans-serif; font-size: 10pt; color: #333; line-height: 1.4; }
    table { border-collapse: collapse; width: 100%; }

    /* Bảng chi tiết sản phẩm (Giữ nguyên) */
    table.details { border: 1px solid #888; }
    table.details th, table.details td {
        padding: 10px 8px; /* Padding */
        vertical-align: middle; /* Căn giữa dọc */
        font-size: 10pt;
        border: 1px solid #ccc;
    }
    table.details th {
        background-color: #f2f2f2;
        color: #333;
        font-weight: bold;
    }

    /* Bảng thông tin chung (Giữ nguyên) */
    table.info-table td { border: none; padding: 3px 0px; vertical-align: top; }
    table.info-table strong { font-weight: bold; }

    /* Khu vực tổng cộng (Giữ nguyên) */
    .total-section { margin-top: 25px; }
    .total-section table { border: none; }
    .total-section table td { border: none; font-size: 11pt; padding: 5px 0px; }
    .total-section strong { font-weight: bold; }
    .total-row td { border-top: 1px solid #666; padding-top: 8px; }

    /* Ghi chú (Giữ nguyên) */
    .notes-section { margin-top: 15px; }
    .notes { padding: 10px; border: 1px solid #eee; margin-top: 5px; min-height: 30px; background-color:#fdfdfd; }

    /* Chữ ký (Giữ nguyên) */
    .signature-section { margin-top: 40px; }
    .signature-section table td { border: none; text-align: center; padding-top: 10px; }
    .signature-section strong { font-weight: bold; }
    .signature-space { height: 50px; display: block; }

    /* Tiêu đề mục (Giữ nguyên) */
    h3.section-title { font-size: 11pt; font-weight: bold; margin-top: 15px; margin-bottom: 8px; border-bottom: 1px solid #ccc; padding-bottom: 3px; color: #333; }
</style>

<h3 class="section-title">THÔNG TIN CHUNG</h3>
<table class="info-table">
    <tr><td width="25%"><strong>Ngày nhập:</strong></td><td width="75%">{$ngayNhapFormatted}</td></tr>
    <tr><td><strong>Nhà cung cấp:</strong></td><td><strong>{$tenNCC}</strong></td></tr>
    <tr><td><strong>Địa chỉ NCC:</strong></td><td>{$diaChiNCC}</td></tr>
    <tr><td><strong>Điện thoại NCC:</strong></td><td>{$dienThoaiNCC}</td></tr>
    <tr><td><strong>Số HĐ NCC:</strong></td><td>{$soHoaDonNCC}</td></tr>
    <tr><td><strong>Người lập phiếu:</strong></td><td>{$tenNguoiLap}</td></tr>
</table>
<br>

<h3 class="section-title">CHI TIẾT HÀNG NHẬP</h3>
<table class="details">
    <thead>
        <tr>
            <th align="center">Tên Sản Phẩm</th>
            <th align="center">Số Lượng</th>
            <th align="center">Đơn Giá Nhập (VNĐ)</th>
            <th align="center">Thành Tiền (VNĐ)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td align="center">{$tenSP}</td>
            <td align="center">{$soLuong}</td>
            <td align="center">{$donGiaNhapFormatted}</td>
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

<div class="notes-section">
    <strong>Ghi Chú:</strong>
    <div class="notes">{$ghiChuPN}</div>
</div>
<br><br>

<div class="signature-section">
    <table class="no-border">
         <tr>
             <td width="50%" align="center"><strong>Người lập phiếu</strong><br><br><br><br><i>(Ký, ghi rõ họ tên)</i><br><br><strong>{$tenNguoiLap}</strong></td>
             <td width="50%" align="center"><strong>Đại diện Nhà cung cấp</strong><br><br><br><br><i>(Ký, ghi rõ họ tên)</i><br><br>{$tenNCC}</td>
         </tr>
    </table>
</div>

HTML;

// --- GHI NỘI DUNG HTML VÀO PDF ---
$pdf->writeHTML($html, true, false, true, false, '');

// --- DỌN DẸP BUFFER VÀ XUẤT PDF ---
ob_end_clean();
$pdf->Output('PhieuNhapKho_' . $idPN . '.pdf', 'I');

mysqli_close($conn);
exit;
?>