<?php
session_start();
include_once('../connection/connect_database.php');

// --- Kiểm tra đăng nhập và ID đơn hàng ---
if (!isset($_SESSION['IDUser'])) {
    die("Vui lòng đăng nhập để xem hóa đơn.");
}
if (!isset($_GET['idDH']) || !filter_var($_GET['idDH'], FILTER_VALIDATE_INT)) {
    die("ID đơn hàng không hợp lệ.");
}

$userID = (int)$_SESSION['IDUser'];
$orderID = (int)$_GET['idDH'];

// --- Lấy thông tin đơn hàng chính ---
// !!! NÊN DÙNG PREPARED STATEMENT !!!
// Lấy cả thông tin PTGH để hiển thị tên và phí (nếu có)
$sql_order = "SELECT dh.*, gh.TenGH, gh.Phi as PhiGH
              FROM donhang dh
              LEFT JOIN phuongthucgiaohang gh ON dh.idPTGH = gh.idGH
              WHERE dh.idDH = $orderID AND dh.idUser = $userID"; // Chỉ lấy đơn hàng của user này
$rs_order = mysqli_query($conn, $sql_order);

if (!$rs_order || mysqli_num_rows($rs_order) == 0) {
    die("Không tìm thấy đơn hàng hoặc bạn không có quyền xem hóa đơn này.");
}
$order = mysqli_fetch_assoc($rs_order);

// --- Lấy thông tin chi tiết đơn hàng ---
// !!! NÊN DÙNG PREPARED STATEMENT !!!
$sql_details = "SELECT * FROM donhangchitiet WHERE idDH = $orderID";
$rs_details = mysqli_query($conn, $sql_details);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Hóa Đơn #<?php echo $order['idDH']; ?></title>
    <link rel="stylesheet" href="../css/bootstrap.min.css"> <style>
        body { font-family: Arial, sans-serif; margin: 20px; font-size: 14px; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, .15); font-size: 16px; line-height: 24px; color: #555; }
        .invoice-box table { width: 100%; line-height: inherit; text-align: left; border-collapse: collapse; }
        .invoice-box table td { padding: 5px; vertical-align: top; }
        .invoice-box table tr td:nth-child(n+2) { text-align: right; } /* Căn phải các cột sau cột đầu */
        .invoice-box table tr.top table td { padding-bottom: 20px; }
        .invoice-box table tr.top table td.title { font-size: 45px; line-height: 45px; color: #333; }
        .invoice-box table tr.information table td { padding-bottom: 40px; }
        .invoice-box table tr.heading td { background: #eee; border-bottom: 1px solid #ddd; font-weight: bold; text-align: center;}
        .invoice-box table tr.heading td:first-child { text-align: left;} /* Căn trái cột đầu */
        .invoice-box table tr.details td { padding-bottom: 20px; }
        .invoice-box table tr.item td { border-bottom: 1px solid #eee; text-align: center;}
        .invoice-box table tr.item td:first-child { text-align: left;}
        .invoice-box table tr.item.last td { border-bottom: none; }
        .invoice-box table tr.total td { border-top: 2px solid #eee; font-weight: bold; text-align: right !important; } /* Quan trọng !important */

        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .company-details, .customer-details { margin-bottom: 30px; }
        h2 { border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 20px;}

        @media print { /* CSS cho lúc in */
            body { margin: 0; }
            .invoice-box { box-shadow: none; border: none; margin: 0; padding: 0; }
            .print-button { display: none; } /* Ẩn nút in khi in */
        }
        .print-button { margin: 20px; text-align: center; }
    </style>
</head>
<body>

<div class="invoice-box">
    <table cellpadding="0" cellspacing="0">
        <tr class="top">
            <td colspan="4"> <table>
                    <tr>
                        <td class="title">
                            <img src="../images/logoad2.jpg" style="width:100%; max-width:150px;" alt="Logo Cửa hàng"> </td>
                        <td class="text-right">
                            Hóa Đơn #: <?php echo htmlspecialchars($order['idDH']); ?><br>
                            Ngày tạo: <?php echo date("d/m/Y", strtotime($order['ThoiDiemDatHang'])); ?><br>
                            <?php /* Ngày hết hạn: July 1, 2024 */ ?>
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
                            <strong>TIẾN TC BEAUTY STORE</strong><br> <?php // Thay thông tin cửa hàng ?>
                            123 Đường 3/2, Phường Xuân Khánh, Quận Ninh Kiều<br>
                            Cần Thơ, Việt Nam<br>
                            tientcbeauty@gmail.com<br>
                            0832 623 352
                        </td>

                        <td class="customer-details text-right">
                            <strong><?php echo htmlspecialchars($order['TenNguoiNhan']); ?></strong><br>
                            <?php echo htmlspecialchars($order['DiaChi']); ?><br>
                            <?php echo htmlspecialchars($order['SDT']); ?><br>
                            <?php echo htmlspecialchars($order['Email']); ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr class="heading">
            <td colspan="2">Phương thức thanh toán</td>
            <td colspan="2" class="text-right">Phương thức vận chuyển</td>
        </tr>

        <tr class="details">
            <td colspan="2"><?php echo htmlspecialchars($order['PTThanhToan']); ?></td>
             <td colspan="2" class="text-right"><?php echo htmlspecialchars($order['TenGH'] ?? 'N/A'); ?></td>
        </tr>

        <tr class="heading">
            <td>Sản phẩm</td>
            <td class="text-center">Số lượng</td>
            <td class="text-center">Đơn giá</td>
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
            <td class="text-center"><?php echo number_format($item['Gia']); ?></td>
            <td class="text-right"><?php echo number_format($line_total); ?></td>
        </tr>
        <?php
             }
              mysqli_free_result($rs_details);
        }
        ?>

        <tr class="total">
            <td colspan="3">Tổng tiền hàng:</td>
            <td><?php echo number_format($subtotal); ?> VNĐ</td>
        </tr>
         <tr class="total">
            <td colspan="3">Phí vận chuyển:</td>
             <td><?php echo number_format($order['PhiGH'] ?? 0); ?> VNĐ</td> <?php // Lấy phí từ thông tin đơn hàng ?>
        </tr>
         <tr class="total">
            <td colspan="3"><strong>Tổng cộng:</strong></td>
             <td><strong><?php echo number_format($order['TongTien']); ?> VNĐ</strong></td> <?php // Lấy tổng tiền đã lưu ?>
        </tr>
    </table>
</div>

<div class="print-button">
    <button onclick="window.print();" class="btn btn-primary">In Hóa Đơn</button>
    <a href="DonHang.php" class="btn btn-default">Quay Lại Đơn Hàng</a>
</div>

</body>
</html>