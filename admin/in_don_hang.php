<?php
session_start();
// // Kiểm tra quyền admin nếu cần
// if (!isset($_SESSION['user_group']) || $_SESSION['user_group'] != 1) {
//     die("Bạn không có quyền truy cập trang này.");
// }

include_once('../connection/connect_database.php');

// --- Sử dụng PHPMailer ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// *** ĐƯỜNG DẪN ĐÃ SỬA THEO HÌNH ẢNH CẤU TRÚC THƯ MỤC BẠN GỬI ***
// (Giả định file này nằm trong /admin và PHPMailer-6.9.3 nằm ở gốc)
require '../PHPMailer-6.9.3/src/Exception.php';
require '../PHPMailer-6.9.3/src/PHPMailer.php';
require '../PHPMailer-6.9.3/src/SMTP.php';

// Biến lưu trạng thái gửi email và khởi tạo phí vận chuyển
$email_status_message = '';
$phi_vc_in = 0; // Khởi tạo để tránh lỗi undefined variable

// --- Kiểm tra ID đơn hàng ---
// Sử dụng $_REQUEST để nhận cả GET và POST
if (!isset($_REQUEST['idDH']) || !filter_var($_REQUEST['idDH'], FILTER_VALIDATE_INT) || (int)$_REQUEST['idDH'] <= 0) {
    die("ID Đơn hàng không hợp lệ hoặc không được cung cấp.");
}
$idDH = (int)$_REQUEST['idDH'];

// --- Lấy thông tin đơn hàng tổng thể ---
$order_info = null;
$sql_order = "SELECT dh.*, gh.TenGH
              FROM donhang dh
              LEFT JOIN phuongthucgiaohang gh ON dh.idPTGH = gh.idGH
              WHERE dh.idDH = ?";
$stmt_order = mysqli_prepare($conn, $sql_order);
if ($stmt_order) {
    mysqli_stmt_bind_param($stmt_order, 'i', $idDH);
    mysqli_stmt_execute($stmt_order);
    $result_order = mysqli_stmt_get_result($stmt_order);
    if ($result_order && mysqli_num_rows($result_order) > 0) {
        $order_info = mysqli_fetch_assoc($result_order);
    }
    // mysqli_free_result($result_order); // Có thể cần giải phóng nếu dùng lại biến $result_order
    mysqli_stmt_close($stmt_order);
} else {
    error_log("Prepare statement failed (select donhang): " . mysqli_error($conn));
    die("Lỗi truy vấn thông tin đơn hàng.");
}

// --- Kiểm tra nếu không tìm thấy đơn hàng ---
if (!$order_info) {
    die("Không tìm thấy đơn hàng #{$idDH}.");
}

// --- Lấy thông tin chi tiết đơn hàng ---
$order_details = [];
$tong_tien_hang_in = 0; // Khởi tạo lại ở đây cho chắc chắn
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
    error_log("Prepare statement failed (select donhangchitiet): " . mysqli_error($conn));
    // Đóng kết nối trước khi die nếu có lỗi nghiêm trọng
    if (isset($conn) && $conn) { mysqli_close($conn); }
    die("Lỗi truy vấn chi tiết đơn hàng.");
}

// --- Tính phí vận chuyển ---
// Phải tính sau khi đã có $order_info và $tong_tien_hang_in
$phi_vc_in = (float)($order_info['TongTien'] ?? 0) - $tong_tien_hang_in;
if ($phi_vc_in < 0) { $phi_vc_in = 0; }


// --- XỬ LÝ GỬI EMAIL KHI NHẤN NÚT ---
if (isset($_POST['send_email'])) {
    $customer_email = $order_info['Email'];
    $customer_name = $order_info['TenNguoiNhan'];

    if (!empty($customer_email) && filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
        // --- Tạo nội dung email HTML ---
        $email_subject = "Xác nhận đơn hàng #{$idDH} từ Tiến TC Beauty Store";
        $email_body = "<!DOCTYPE html><html lang='vi'><head><meta charset='UTF-8'><title>{$email_subject}</title>";
        $email_body .= "<style> body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 15px;} h2, h3 {color: #0d6efd;} table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 10pt;} th, td { border: 1px solid #ddd; padding: 8px; text-align: left; } th { background-color: #f2f2f2; } .text-end { text-align: right; } .total { font-weight: bold; color:#dc3545;} </style>";
        $email_body .= "</head><body>";
        $email_body .= "<h2>Kính gửi " . htmlspecialchars($customer_name) . ",</h2>";
        $email_body .= "<p>Cảm ơn bạn đã đặt hàng tại Tiến TC Beauty Store. Đơn hàng của bạn (<strong>#{$idDH}</strong>) đã được xác nhận với các thông tin chi tiết như sau:</p>";
        $email_body .= "<h3>Thông tin giao hàng</h3>";
        $email_body .= "<p><strong>Người nhận:</strong> " . htmlspecialchars($order_info['TenNguoiNhan']) . "</p>";
        $email_body .= "<p><strong>Địa chỉ:</strong> " . htmlspecialchars($order_info['DiaChi']) . "</p>";
        $email_body .= "<p><strong>Điện thoại:</strong> " . htmlspecialchars($order_info['SDT']) . "</p>";
        if (!empty($order_info['GhiChu'])) { $email_body .= "<p><strong>Ghi chú:</strong> " . nl2br(htmlspecialchars($order_info['GhiChu'])) . "</p>"; }
        $email_body .= "<h3>Chi tiết đơn hàng</h3>";
        $email_body .= "<table><thead><tr><th>Sản phẩm</th><th>SL</th><th>Đơn giá</th><th>Thành tiền</th></tr></thead><tbody>";
        foreach ($order_details as $item) { $thanh_tien_item_mail = (int)$item['SoLuong'] * (float)$item['Gia']; $email_body .= "<tr>"; $email_body .= "<td>" . htmlspecialchars($item['TenSP']) . "</td>"; $email_body .= "<td style='text-align:center;'>" . $item['SoLuong'] . "</td>"; $email_body .= "<td class='text-end'>" . number_format((float)$item['Gia'], 0, ',', '.') . " đ</td>"; $email_body .= "<td class='text-end'>" . number_format($thanh_tien_item_mail, 0, ',', '.') . " đ</td>"; $email_body .= "</tr>"; } $email_body .= "</tbody></table>";
        // Dùng lại $phi_vc_in đã tính trước đó
        $email_body .= "<div style='text-align: right; width: 50%; margin-left: auto; padding-top: 10px; border-top: 1px solid #eee;'>"; // Định dạng khối tổng tiền
        $email_body .= "<p><strong>Tổng tiền hàng:</strong> <span style='float:right;'>" . number_format($tong_tien_hang_in, 0, ',', '.') . " đ</span></p>";
        $email_body .= "<p style='clear:both;'><strong>Phí vận chuyển:</strong> <span style='float:right;'>" . ($phi_vc_in > 0 ? number_format($phi_vc_in, 0, ',', '.') . ' đ' : 'Miễn phí') . "</span></p>";
        $email_body .= "<p class='total' style='clear:both; font-size: 1.1em;'><strong>Tổng thanh toán:</strong> <span style='float:right;'>" . number_format((float)$order_info['TongTien'], 0, ',', '.') . " đ</span></p>";
        $email_body .= "</div><div style='clear: both;'></div>"; // Clear float
        $email_body .= "<p><strong>Phương thức thanh toán:</strong> " . htmlspecialchars($order_info['PTThanhToan'] ?? 'N/A') . "</p>";
        $email_body .= "<p>Chúng tôi sẽ xử lý và giao hàng cho bạn trong thời gian sớm nhất.</p>";
        $email_body .= "<p>Trân trọng,<br>Tiến TC Beauty Store</p>";
        $email_body .= "</body></html>";

        // --- Gửi email bằng PHPMailer ---
        $mail = new PHPMailer(true);
        try {
            // Cài đặt Server cho GMAIL (*** THAY THẾ USERNAME VÀ APP PASSWORD ***)
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Bật để debug nếu vẫn lỗi
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';         // Host cho Gmail
            $mail->SMTPAuth   = true;
            $mail->Username   = 'tychuot01278080008@gmail.com';  // <<< ĐIỀN ĐỊA CHỈ GMAIL ĐẦY ĐỦ CỦA BẠN VÀO ĐÂY
            $mail->Password   = 'ycfanxakizosznyz'; // <<< DÁN MẬT KHẨU ỨNG DỤNG (16 KÝ TỰ KHÔNG CÓ DẤU CÁCH) BẠN ĐÃ TẠO VÀO ĐÂY
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Khuyến nghị dùng TLS
            $mail->Port       = 587;                        // Port cho TLS

            // // Hoặc thử dùng SMTPS/SSL nếu TLS không hoạt động:
            // $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
            // $mail->Port       = 465;                        // Port cho SSL

            $mail->CharSet = 'UTF-8';

            // Người gửi và người nhận
            // *** LƯU Ý: Khi dùng Gmail, email Người gửi (setFrom) nên trùng với Username ***
            $mail->setFrom('your_email@gmail.com', 'Tiến TC Beauty Store'); // <<< ĐIỀN LẠI EMAIL GỬI VÀ TÊN CỬA HÀNG
            $mail->addAddress($customer_email, $customer_name);     // Email và tên người nhận

            // Nội dung
            $mail->isHTML(true);
            $mail->Subject = $email_subject;
            $mail->Body    = $email_body;
            $mail->AltBody = strip_tags($email_body);

            $mail->send();
            $email_status_message = '<div class="alert alert-success no-print">Email đã được gửi thành công đến ' . htmlspecialchars($customer_email) . '!</div>';
        } catch (Exception $e) {
            // Hiển thị lỗi chi tiết hơn để dễ debug
            $email_status_message = "<div class='alert alert-danger no-print'>Gửi email thất bại. Lỗi PHPMailer: {$mail->ErrorInfo}</div>";
            error_log("PHPMailer Error for order #{$idDH} to {$customer_email}: " . $mail->ErrorInfo);
        }
    } else {
        $email_status_message = "<div class='alert alert-warning no-print'>Địa chỉ email khách hàng không hợp lệ hoặc không tìm thấy. Không thể gửi email.</div>";
    }
}
// --- Kết thúc xử lý gửi email ---


// --- Hàm lấy text trạng thái ---
function getStatusText($status_code) {
    switch ($status_code) { case 0: return "Chưa xử lý"; case 1: return "Đã xử lý/Đang chuẩn bị"; case 2: return "Đang giao hàng"; case 3: return "Yêu cầu hủy"; case 4: return "Đã hủy"; case 5: return "Đã hoàn thành"; default: return "Không xác định"; }
}

// --- Đóng kết nối CSDL (nếu chưa đóng) ---
if (isset($conn) && $conn && mysqli_ping($conn)) { // Kiểm tra xem kết nối còn tồn tại không
    mysqli_close($conn);
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
        /* CSS (Giữ nguyên) */
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
         /* CSS cho thông báo email */
         .alert { padding: 0.75rem 1.25rem; margin-bottom: 1rem; border: 1px solid transparent; border-radius: 0.25rem; }
         .alert-success { color: #0f5132; background-color: #d1e7dd; border-color: #badbcc; }
         .alert-danger { color: #842029; background-color: #f8d7da; border-color: #f5c2c7; }
         .alert-warning { color: #664d03; background-color: #fff3cd; border-color: #ffecb5; }
        @media print {
            body { font-size: 10pt; margin: 0; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .container-print { width: 100%; margin: 0; padding: 5mm; border: none; box-shadow: none; }
            .no-print { display: none !important; }
             h1 {font-size: 15pt;} h2 {font-size: 12pt;} table { font-size: 9.5pt; }
            table, tr, td, th, tbody, thead, tfoot { page-break-inside: avoid !important; }
            thead { display: table-header-group; }
        }
    </style>
</head>
<body>
    <div class="container-print">

         <?php if (!empty($email_status_message)) echo $email_status_message; ?>

        <div class="store-info"><h1>TIẾN TC BEAUTY STORE</h1> <p>Địa chỉ: 123, Đường 3/2, Phường Xuân Khánh, Quận Ninh Kiều, TP.Cần Thơ</p> <p>Điện thoại: 0832 623 352 - Email: tientcbeauty@gmail.com</p> </div>
        <h2 class="text-center">PHIẾU GIAO HÀNG / HÓA ĐƠN BÁN LẺ</h2> <p class="text-center small"><i>(Không phải hóa đơn GTGT)</i></p>
        <div class="row order-info"> <div class="col-6"><p><strong>Mã đơn hàng:</strong> #<?php echo $order_info['idDH']; ?></p></div> <div class="col-6 text-end"><p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order_info['ThoiDiemDatHang'])); ?></p></div> <div class="col-12"><p><strong>Trạng thái:</strong> <?php echo getStatusText($order_info['DaXuLy']); ?></p></div> </div>
        <div class="customer-info"> <h2>Thông Tin Khách Hàng</h2> <p><strong>Người nhận:</strong> <?php echo htmlspecialchars($order_info['TenNguoiNhan']); ?></p> <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order_info['DiaChi']); ?></p> <p><strong>Điện thoại:</strong> <?php echo htmlspecialchars($order_info['SDT']); ?></p> <p><strong>Email:</strong> <?php echo htmlspecialchars($order_info['Email']); ?></p> <?php if (!empty($order_info['GhiChu'])): ?> <p><strong>Ghi chú:</strong> <?php echo nl2br(htmlspecialchars($order_info['GhiChu'])); ?></p> <?php endif; ?> </div>
        <div class="payment-shipping-info"> <h2>Thông Tin Giao Hàng & Thanh Toán</h2> <p><strong>Phương thức vận chuyển:</strong> <?php echo htmlspecialchars($order_info['TenGH'] ?? 'N/A'); ?></p> <p><strong>Phương thức thanh toán:</strong> <?php echo htmlspecialchars($order_info['PTThanhToan'] ?? 'N/A'); ?></p> </div>
        <h2>Chi Tiết Sản Phẩm</h2>
        <table> <thead> <tr> <th style="width: 5%;">STT</th> <th style="width: 45%;">Tên Sản Phẩm</th> <th style="width: 10%;">SL</th> <th style="width: 20%;">Đơn Giá</th> <th style="width: 20%;">Thành Tiền</th> </tr> </thead> <tbody> <?php $stt = 1; foreach ($order_details as $item): ?> <?php $thanh_tien_item_in = (int)$item['SoLuong'] * (float)$item['Gia']; ?> <tr> <td class="text-center"><?php echo $stt++; ?></td> <td><?php echo htmlspecialchars($item['TenSP']); ?></td> <td class="text-center"><?php echo $item['SoLuong']; ?></td> <td class="text-end"><?php echo number_format((float)$item['Gia'], 0, ',', '.'); ?> đ</td> <td class="text-end"><?php echo number_format($thanh_tien_item_in, 0, ',', '.'); ?> đ</td> </tr> <?php endforeach; ?> <?php if (count($order_details) < 3) { for ($i = 0; $i < (3 - count($order_details)); $i++) echo '<tr><td>&nbsp;</td><td></td><td></td><td></td><td></td></tr>'; } ?> </tbody> </table>
        <div class="summary mt-3"> <table class="summary-table float-end"> <tbody> <tr><td>Tổng tiền hàng:</td><td class="text-end fw-bold"><?php echo number_format($tong_tien_hang_in, 0, ',', '.'); ?> đ</td></tr> <tr><td>Phí vận chuyển:</td><td class="text-end fw-bold"><?php echo ($phi_vc_in > 0) ? number_format($phi_vc_in, 0, ',', '.') . ' đ' : 'Miễn phí'; ?></td></tr> <tr class="summary-total"><td>Tổng thanh toán:</td><td class="text-end text-danger"><?php echo number_format((float)$order_info['TongTien'], 0, ',', '.'); ?> đ</td></tr> </tbody> </table> <div style="clear: both;"></div> </div>
        <div class="signature-section"> <div><strong>Khách hàng</strong><br><i>(Ký, ghi rõ họ tên)</i><div class="signer-name"></div></div> <div><strong>Người giao hàng</strong><br><i>(Ký, ghi rõ họ tên)</i><div class="signer-name"></div></div> </div>

        <div class="footer-print">
            <p>Cảm ơn quý khách đã tin tưởng và mua hàng!</p>
            <p>--- In ngày: <?php echo date('d/m/Y H:i:s'); ?> ---</p>
             <div class="no-print mt-3">
                 <form method="post" action="" style="display: inline-block;">
                     <input type="hidden" name="idDH" value="<?php echo $idDH; ?>">
                     <button type="submit" name="send_email" class="btn btn-success btn-sm">
                         <i class="bi bi-envelope-fill"></i> Gửi Email Xác Nhận
                     </button>
                 </form>
                 <button onclick="window.print();" class="btn btn-primary btn-sm ms-2">
                     <i class="bi bi-printer"></i> In lại
                 </button>
                 <button onclick="window.close();" class="btn btn-secondary btn-sm ms-2">Đóng</button>
             </div>
        </div>

    </div> </body>
</html>