<?php
session_start();
// // Kiểm tra quyền admin nếu cần
// if (!isset($_SESSION['user_group']) || $_SESSION['user_group'] != 1) {
//     die("Bạn không có quyền truy cập trang này.");
// }

include_once('../connection/connect_database.php');

// --- Kiểm tra ID đơn hàng ---
if (!isset($_GET['idDH']) || !filter_var($_GET['idDH'], FILTER_VALIDATE_INT) || (int)$_GET['idDH'] <= 0) {
    die("ID Đơn hàng không hợp lệ hoặc không được cung cấp.");
}
$idDH = (int)$_GET['idDH'];

// --- Lấy thông tin đơn hàng tổng thể (Prepared Statement) ---
$order_info = null;

// *** SỬA ĐỔI QUERY: Bỏ JOIN với phuongthucthanhtoan, lấy trực tiếp cột PTThanhToan ***
// Giả định bảng donhang có cột PTThanhToan (VARCHAR) lưu tên PTTT
// và cột idPTGH (INT hoặc VARCHAR lưu số) để join lấy tên PTVC
$sql_order = "SELECT dh.*, gh.TenGH
              FROM donhang dh
              LEFT JOIN phuongthucgiaohang gh ON dh.idPTGH = gh.idGH
              WHERE dh.idDH = ?";
// ======================================================================

$stmt_order = mysqli_prepare($conn, $sql_order);
if ($stmt_order) {
    mysqli_stmt_bind_param($stmt_order, 'i', $idDH);
    mysqli_stmt_execute($stmt_order);
    $result_order = mysqli_stmt_get_result($stmt_order);
    if ($result_order && mysqli_num_rows($result_order) > 0) {
        $order_info = mysqli_fetch_assoc($result_order);
    }
    mysqli_free_result($result_order);
    mysqli_stmt_close($stmt_order);
} else {
    error_log("Prepare statement failed (select donhang for print): " . mysqli_error($conn));
    // Hiển thị thông báo lỗi cụ thể hơn (có thể comment lại khi chạy chính thức)
    // die("Lỗi truy vấn thông tin đơn hàng. Chi tiết: " . mysqli_error($conn));
    die("Lỗi truy vấn thông tin đơn hàng."); // Thông báo chung
}

// --- Kiểm tra nếu không tìm thấy đơn hàng ---
if (!$order_info) {
    // Lỗi này xảy ra khi ID không tồn tại hoặc query lỗi
    die("Không tìm thấy đơn hàng #{$idDH}. Vui lòng kiểm tra lại ID hoặc đơn hàng có thể đã bị xóa.");
}

// --- Lấy thông tin chi tiết đơn hàng (Prepared Statement - Phần này giữ nguyên) ---
$order_details = [];
$tong_tien_hang_in = 0;
$sql_details = "SELECT idSP, TenSP, SoLuong, Gia FROM donhangchitiet WHERE idDH = ?";
$stmt_details = mysqli_prepare($conn, $sql_details);
if ($stmt_details) {
     mysqli_stmt_bind_param($stmt_details, 'i', $idDH);
     mysqli_stmt_execute($stmt_details);
     $result_details = mysqli_stmt_get_result($stmt_details);
     while ($row = mysqli_fetch_assoc($result_details)) {
         $order_details[] = $row;
         $tong_tien_hang_in += (int)$row['SoLuong'] * (float)$row['Gia'];
     }
     mysqli_free_result($result_details);
     mysqli_stmt_close($stmt_details);
} else {
    error_log("Prepare statement failed (select donhangchitiet for print): " . mysqli_error($conn));
    die("Lỗi truy vấn chi tiết đơn hàng.");
}

// --- Đóng kết nối ---
mysqli_close($conn);

// --- Tính phí vận chuyển ---
$phi_vc_in = (float)($order_info['TongTien'] ?? 0) - $tong_tien_hang_in;
if ($phi_vc_in < 0) { $phi_vc_in = 0; }

// Hàm lấy text trạng thái
function getStatusText($status_code) {
     switch ($status_code) {
        case 0: return "Chưa xử lý"; case 1: return "Đã xử lý/Đang chuẩn bị"; case 2: return "Đang giao hàng";
        case 3: return "Yêu cầu hủy"; case 4: return "Đã hủy"; case 5: return "Đã hoàn thành";
        default: return "Không xác định";
    }
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In Đơn Hàng #<?php echo $idDH; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        /* CSS giữ nguyên như phiên bản trước */
        body { font-family: 'Arial', sans-serif; font-size: 11pt; line-height: 1.4; margin: 0; padding: 0; background-color: #fff; }
        .container-print { width: 100%; max-width: 800px; margin: 15px auto; padding: 15px; border: 1px solid #eee; }
        .store-info h1 { font-size: 16pt; margin-bottom: 5px; text-align: center; font-weight: bold; text-transform: uppercase; }
        .store-info p { text-align: center; margin: 1px 0; font-size: 10pt; }
        h2 { font-size: 13pt; font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 4px; margin-top: 15px; margin-bottom: 8px; }
        h2.text-center { border-bottom: none; margin-bottom: 15px;}
        .order-info p, .customer-info p, .payment-shipping-info p { margin-bottom: 3px; font-size: 11pt; }
        .order-info strong, .customer-info strong, .payment-shipping-info strong { display: inline-block; min-width: 150px;}
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 10.5pt; }
        th, td { border: 1px solid #999; padding: 4px 6px; text-align: left; vertical-align: top;}
        th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
        td.text-end { text-align: right; } td.text-center { text-align: center; } td.fw-bold { font-weight: bold; }
        .summary { margin-top: 15px; } .summary-table { width: 45%; float: right; }
        .summary-table td { border: none; padding: 3px 6px;} .summary-table td:first-child { text-align: right; padding-right: 10px;}
        .summary-total td { border-top: 1px solid #000; font-weight: bold; padding-top: 5px;}
        .footer-print { margin-top: 25px; text-align: center; font-style: italic; font-size: 9pt; border-top: 1px dashed #ccc; padding-top: 10px;}
        .signature-section { margin-top: 40px; display: flex; justify-content: space-around; text-align: center; font-size: 10pt; }
        .signature-section div { width: 40%; } .signature-section .signer-name { margin-top: 50px; font-weight: bold;}
        @media print {
            body { font-size: 10pt; margin: 0; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .container-print { width: 100%; margin: 0; padding: 5mm; border: none; box-shadow: none; }
            .no-print { display: none; } h1 {font-size: 15pt;} h2 {font-size: 12pt;} table { font-size: 9.5pt; }
            table, tr, td, th, tbody, thead, tfoot { page-break-inside: avoid !important; }
            thead { display: table-header-group; }
        }
    </style>
</head>
<body>
    <div class="container-print">
        <div class="store-info">
             <h1>TIẾN TC BEAUTY STORE</h1>
            <p>Địa chỉ: 123, Đường 3/2, Phường Xuân Khánh, Quận Ninh Kiều, TP.Cần Thơ</p>
            <p>Điện thoại: 0832 623 352 - Email: tientcbeauty@gmail.com</p>
             </div>

        <h2 class="text-center">PHIẾU GIAO HÀNG / HÓA ĐƠN BÁN LẺ</h2>
        <p class="text-center small"><i>(Không phải hóa đơn GTGT)</i></p>

        <div class="row order-info">
            <div class="col-6"><p><strong>Mã đơn hàng:</strong> #<?php echo $order_info['idDH']; ?></p></div>
            <div class="col-6 text-end"><p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order_info['ThoiDiemDatHang'])); ?></p></div>
            <div class="col-12"><p><strong>Trạng thái:</strong> <?php echo getStatusText($order_info['DaXuLy']); ?></p></div>
        </div>

        <div class="customer-info">
            <h2>Thông Tin Khách Hàng</h2>
            <p><strong>Người nhận:</strong> <?php echo htmlspecialchars($order_info['TenNguoiNhan']); ?></p>
            <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order_info['DiaChi']); ?></p>
            <p><strong>Điện thoại:</strong> <?php echo htmlspecialchars($order_info['SDT']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($order_info['Email']); ?></p>
            <?php if (!empty($order_info['GhiChu'])): ?>
                <p><strong>Ghi chú:</strong> <?php echo nl2br(htmlspecialchars($order_info['GhiChu'])); ?></p>
            <?php endif; ?>
        </div>

         <div class="payment-shipping-info">
            <h2>Thông Tin Giao Hàng & Thanh Toán</h2>
            <p><strong>Phương thức vận chuyển:</strong> <?php echo htmlspecialchars($order_info['TenGH'] ?? 'N/A'); // Lấy từ JOIN ?></p>
            <?php // *** SỬA ĐỔI: Lấy tên PTTT từ cột PTThanhToan (VARCHAR) *** ?>
            <p><strong>Phương thức thanh toán:</strong> <?php echo htmlspecialchars($order_info['PTThanhToan'] ?? 'N/A'); ?></p>
            <?php // ====================================================== ?>
        </div>

        <h2>Chi Tiết Sản Phẩm</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">STT</th> <th style="width: 45%;">Tên Sản Phẩm</th> <th style="width: 10%;">SL</th>
                    <th style="width: 20%;">Đơn Giá</th> <th style="width: 20%;">Thành Tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php $stt = 1; foreach ($order_details as $item): ?>
                     <?php $thanh_tien_item_in = (int)$item['SoLuong'] * (float)$item['Gia']; ?>
                    <tr>
                        <td class="text-center"><?php echo $stt++; ?></td>
                        <td><?php echo htmlspecialchars($item['TenSP']); ?></td>
                        <td class="text-center"><?php echo $item['SoLuong']; ?></td>
                        <td class="text-end"><?php echo number_format((float)$item['Gia'], 0); ?> đ</td>
                        <td class="text-end"><?php echo number_format($thanh_tien_item_in, 0); ?> đ</td>
                    </tr>
                <?php endforeach; ?>
                 <?php if(count($order_details) < 3){ for($i=0; $i < (3 - count($order_details)); $i++) echo '<tr><td>&nbsp;</td><td></td><td></td><td></td><td></td></tr>'; } ?>
            </tbody>
        </table>

        <div class="summary mt-3">
            <table class="summary-table float-end">
                <tbody>
                    <tr><td>Tổng tiền hàng:</td><td class="text-end fw-bold"><?php echo number_format($tong_tien_hang_in, 0); ?> đ</td></tr>
                    <tr><td>Phí vận chuyển:</td><td class="text-end fw-bold"><?php echo ($phi_vc_in > 0) ? number_format($phi_vc_in, 0) . ' đ' : 'Miễn phí'; ?></td></tr>
                    <tr class="summary-total"><td>Tổng thanh toán:</td><td class="text-end text-danger"><?php echo number_format((float)$order_info['TongTien'], 0); ?> đ</td></tr>
                </tbody>
            </table>
            <div style="clear: both;"></div>
        </div>

        <div class="signature-section">
             <div><strong>Khách hàng</strong><br><i>(Ký, ghi rõ họ tên)</i><div class="signer-name"></div></div>
             <div><strong>Người giao hàng</strong><br><i>(Ký, ghi rõ họ tên)</i><div class="signer-name"></div></div>
        </div>

        <div class="footer-print">
            <p>Cảm ơn quý khách đã tin tưởng và mua hàng!</p>
            <p>--- In ngày: <?php echo date('d/m/Y H:i:s'); ?> ---</p>
             <div class="no-print mt-3">
                 <button onclick="window.print();" class="btn btn-primary btn-sm"><i class="bi bi-printer"></i> In lại</button>
                 <button onclick="window.close();" class="btn btn-secondary btn-sm">Đóng</button>
            </div>
        </div>

    </div> </body>
</html>