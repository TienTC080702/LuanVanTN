<?php
session_start();
// Include file kết nối
include_once('../connection/connect_database.php');

// --- Kiểm tra đăng nhập và ID đơn hàng ---
if (!isset($_SESSION['IDUser'])) {
    // Chuyển hướng đến trang đăng nhập hoặc hiển thị thông báo
     header('Location: ../pages/DangNhap.php?err=1'); // Ví dụ chuyển hướng
     exit;
}
if (!isset($_GET['idDH']) || !filter_var($_GET['idDH'], FILTER_VALIDATE_INT)) {
     // Chuyển hướng hoặc báo lỗi thân thiện hơn
     header('Location: DonHang.php?error=invalid_id'); // Ví dụ chuyển hướng về trang đơn hàng
     exit;
}

// --- Đảm bảo kết nối CSDL thành công ---
if (!isset($conn) || !$conn) {
    error_log("Lỗi kết nối CSDL khi xem hóa đơn: " . mysqli_connect_error());
    die("Lỗi hệ thống, không thể kết nối CSDL. Vui lòng thử lại sau.");
}
mysqli_set_charset($conn, 'utf8');


$userID = (int)$_SESSION['IDUser'];
$orderID = (int)$_GET['idDH'];

// --- Lấy thông tin đơn hàng chính (Sử dụng Prepared Statement) ---
$sql_order = "SELECT dh.*, gh.TenGH, gh.Phi AS PhiGH
              FROM donhang dh
              LEFT JOIN phuongthucgiaohang gh ON dh.idPTGH = gh.idGH
              WHERE dh.idDH = ? AND dh.idUser = ?";

$stmt_order = mysqli_prepare($conn, $sql_order);
if (!$stmt_order) {
    error_log("Prepare failed (order): " . mysqli_error($conn));
    die("Lỗi truy vấn đơn hàng (prepare).");
}

mysqli_stmt_bind_param($stmt_order, "ii", $orderID, $userID);
if (!mysqli_stmt_execute($stmt_order)) {
     error_log("Execute failed (order): " . mysqli_stmt_error($stmt_order));
     mysqli_stmt_close($stmt_order);
     die("Lỗi truy vấn đơn hàng (execute).");
}

$rs_order = mysqli_stmt_get_result($stmt_order);
if (!$rs_order) {
     error_log("Get result failed (order): " . mysqli_stmt_error($stmt_order));
     mysqli_stmt_close($stmt_order);
     die("Lỗi truy vấn đơn hàng (get result).");
}

if (mysqli_num_rows($rs_order) == 0) {
     $_SESSION['message'] = "Không tìm thấy đơn hàng hoặc bạn không có quyền xem hóa đơn này.";
     $_SESSION['message_type'] = "warning";
     header('Location: DonHang.php');
     exit;
}
$order = mysqli_fetch_assoc($rs_order);
mysqli_stmt_close($stmt_order);


// --- Lấy thông tin chi tiết đơn hàng (Sử dụng Prepared Statement) ---
$sql_details = "SELECT * FROM donhangchitiet WHERE idDH = ?";
$stmt_details = mysqli_prepare($conn, $sql_details);
if(!$stmt_details){
     error_log("Prepare failed (details): " . mysqli_error($conn));
     die("Lỗi truy vấn chi tiết đơn hàng.");
}

mysqli_stmt_bind_param($stmt_details, "i", $orderID);
mysqli_stmt_execute($stmt_details);
$rs_details = mysqli_stmt_get_result($stmt_details);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hóa Đơn #<?php echo htmlspecialchars($order['idDH']); ?></title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; font-size: 14px; background-color: #f8f9fa; }
        .invoice-box { max-width: 800px; margin: 30px auto; padding: 30px; border: 1px solid #dee2e6; box-shadow: 0 0 15px rgba(0, 0, 0, .07); font-size: 14px; line-height: 22px; color: #555; background-color: #fff; }
        .invoice-box table { width: 100%; line-height: inherit; text-align: left; border-collapse: collapse; }
        .invoice-box table td { padding: 6px 8px; vertical-align: top; }
        .invoice-box table tr td:nth-child(n+2) { text-align: right; } /* Căn phải từ cột thứ 2 */
        .invoice-box table tr.top table td { padding-bottom: 15px; }
        .invoice-box table tr.top table td.title img { width: 100%; max-width: 120px; }
        .invoice-box table tr.information table td { padding-bottom: 30px; }
        .invoice-box table tr.heading td { background: #f1f1f1; border-bottom: 1px solid #ddd; font-weight: bold; text-align: center; padding: 8px;}
        .invoice-box table tr.heading td:first-child { text-align: left;} /* Cột đầu của heading căn trái */
        .invoice-box table tr.details td { padding-bottom: 15px; }
        .invoice-box table tr.item td { border-bottom: 1px solid #eee; text-align: center;}
        .invoice-box table tr.item td:first-child { text-align: left;} /* Cột đầu của item căn trái */
        .invoice-box table tr.item.last td { border-bottom: none; }
        .invoice-box table tr.total td:first-child { text-align: right !important; } /* Cột chữ của dòng total căn phải */
        .invoice-box table tr.total td { border-top: 1px solid #eee; font-weight: bold; text-align: right !important; padding-top: 8px; padding-bottom: 8px;}
        .invoice-box table tr.total:last-child td { border-top: 2px solid #333; font-size: 1.1em;} /* Dòng tổng cộng cuối cùng */

        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .company-details, .customer-details { margin-bottom: 20px; }
        .company-details strong, .customer-details strong { display: block; margin-bottom: 5px;}
        h2 { border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 20px; font-size: 1.5em; text-align: center; }
        .invoice-header{ text-align: right; }
        .invoice-header strong { font-size: 1.2em; }

        @media print {
            body { margin: 0; background-color: #fff; }
            .invoice-box { box-shadow: none; border: none; margin: 0; padding: 10px; max-width: 100%; font-size: 12px; line-height: 18px;}
            .print-button-container { display: none; }
            .page-break { page-break-after: always; }
            table { width: 100% !important; }
            .container, .row, .col-md-12 { width: 100% !important; margin: 0 !important; padding: 0 !important; }
        }
        .print-button-container { margin: 30px 0; text-align: center; }
        .note { margin-top: 30px; font-size: 0.9em; text-align: center; border-top: 1px solid #eee; padding-top: 15px;}
        /* Thêm link Font Awesome nếu bạn dùng icon và header không có */
        /* @import url("https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"); */
    </style>
</head>
<body>

<div class="invoice-box">
    <table cellpadding="0" cellspacing="0">
        <tr class="top">
            <td colspan="4">
                <table>
                    <tr>
                        <td class="title">
                            <img src="../images/logoad2.jpg" alt="Logo Cửa hàng"> </td>
                        <td class="invoice-header">
                            <strong>HÓA ĐƠN BÁN HÀNG</strong><br>
                            Mã HĐ: #<?php echo htmlspecialchars($order['idDH']); ?><br>
                            Ngày đặt: <?php echo date("d/m/Y H:i", strtotime($order['ThoiDiemDatHang'])); ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr class="information">
            <td colspan="4">
                <table>
                    <tr>
                        <td class="company-details">
                            <strong>Từ: TIẾN TC BEAUTY STORE</strong><br>
                            123 Đường 3/2, P. Xuân Khánh<br> Q. Ninh Kiều, Cần Thơ<br>
                            tientcbeauty@gmail.com | 0832 623 352 </td>
                        <td class="customer-details text-right">
                             <strong>Đến: <?php echo htmlspecialchars($order['TenNguoiNhan']); ?></strong><br>
                            <?php echo htmlspecialchars($order['DiaChi']); ?><br>
                            <?php echo htmlspecialchars($order['SDT']); ?><br>
                            <?php echo htmlspecialchars($order['Email']); ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr class="heading">
            <td colspan="2" style="text-align:left;">Phương thức thanh toán</td>
            <td colspan="2" class="text-right">Phương thức vận chuyển</td>
        </tr>

        <tr class="details">
             <td colspan="2" style="text-align:left;"><?php echo htmlspecialchars($order['PTThanhToan']); ?></td>
            <td colspan="2" class="text-right"><?php echo htmlspecialchars($order['TenGH'] ?? 'N/A'); ?></td>
        </tr>

        <tr class="heading">
            <td>Sản phẩm</td>
            <td class="text-center">Số lượng</td>
            <td class="text-right">Đơn giá</td>
            <td class="text-right">Thành tiền</td>
        </tr>

        <?php
        $subtotal = 0;
        if ($rs_details && mysqli_num_rows($rs_details) > 0) {
            while ($item = mysqli_fetch_assoc($rs_details)) {
                $line_total = $item['SoLuong'] * $item['Gia'];
                $subtotal += $line_total;
        ?>
        <tr class="item">
            <td><?php echo htmlspecialchars($item['TenSP']); ?></td>
            <td class="text-center"><?php echo $item['SoLuong']; ?></td>
            <td class="text-right"><?php echo number_format($item['Gia']); ?> đ</td>
            <td class="text-right"><?php echo number_format($line_total); ?> đ</td>
        </tr>
        <?php
            }
             mysqli_free_result($rs_details);
        } else {
             echo '<tr><td colspan="4" class="text-center">Không có chi tiết sản phẩm.</td></tr>';
        }
        mysqli_stmt_close($stmt_details);
        ?>

        <?php /* --- Phần Tổng Cộng (ĐÃ SỬA THEO YÊU CẦU) --- */
            // Tính toán tạm tính = tiền hàng + phí vận chuyển
            $shipping_fee_value = $order['PhiGH'] ?? 0; // Lấy phí VC, mặc định là 0 nếu null
            $subtotal_with_shipping = $subtotal + $shipping_fee_value;
        ?>
        <tr class="total">
            <td colspan="3" class="text-right">Tổng tiền hàng:</td>
            <td><?php echo number_format($subtotal); ?> VNĐ</td>
        </tr>
         <tr class="total">
            <td colspan="3" class="text-right">Phí vận chuyển (<?php echo htmlspecialchars($order['TenGH'] ?? 'N/A'); ?>):</td>
             <td><?php echo number_format($shipping_fee_value); ?> VNĐ</td>
        </tr>
        <?php /* --- THÊM DÒNG TẠM TÍNH --- */ ?>
        <tr class="total">
            <td colspan="3" class="text-right">Tạm tính (Tiền hàng + Phí VC):</td>
            <td><?php echo number_format($subtotal_with_shipping); ?> VNĐ</td>
        </tr>
        <?php /* --- KẾT THÚC DÒNG TẠM TÍNH --- */ ?>

        <?php /* --- HIỂN THỊ GIẢM GIÁ --- */ ?>
        <?php if (!empty($order['ma_giam_gia']) && isset($order['so_tien_giam']) && $order['so_tien_giam'] > 0): ?>
            <tr class="total">
                <td colspan="3" class="text-right">Giảm giá (<?php echo htmlspecialchars($order['ma_giam_gia']); ?>):</td>
                <td>-<?php echo number_format($order['so_tien_giam']); ?> VNĐ</td>
            </tr>
        <?php endif; ?>
        <?php /* --- KẾT THÚC GIẢM GIÁ --- */ ?>

         <tr class="total"> <?php // Dòng tổng cộng cuối cùng (Lấy từ CSDL) ?>
            <td colspan="3" class="text-right"><strong>Tổng cộng thanh toán:</strong></td>
             <td><strong><?php echo number_format($order['TongTien']); ?> VNĐ</strong></td>
        </tr>
        <?php /* --- KẾT THÚC PHẦN TỔNG CỘNG --- */ ?>
    </table>

     <?php if (!empty($order['GhiChu'])): ?>
     <div class="note">
         <strong>Ghi chú đơn hàng:</strong> <?php echo nl2br(htmlspecialchars($order['GhiChu'])); ?>
     </div>
     <?php endif; ?>

     <div class="note">
         Cảm ơn quý khách đã mua hàng tại TIẾN TC BEAUTY STORE!
     </div>
</div>

<div class="print-button-container">
    <?php // Ví dụ thêm icon Font Awesome (cần đảm bảo CSS Font Awesome được load) ?>
    <button onclick="window.print();" class="btn btn-primary"><i class="fas fa-print"></i> In Hóa Đơn</button>
    <a href="DonHang.php" class="btn btn-default"><i class="fas fa-arrow-left"></i> Quay Lại Đơn Hàng</a>
</div>

<?php /* --- Link JS --- */ ?>
<script src="../js/jquery-3.1.1.min.js"></script> <?php // Cần cho Bootstrap nếu dùng JS ?>
<script src="../js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script> <?php // Nếu dùng icon ?>

</body>
</html>
<?php if(isset($conn)) mysqli_close($conn); ?>
