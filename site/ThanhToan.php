<?php
// Bắt đầu session ở đầu tiên
session_start();

// Include file kết nối
include_once('../connection/connect_database.php');

// --- Khởi tạo biến ---
$is_buy_now_mode = false;
$checkout_items = [];
$tongtien = 0; // Sẽ tính toán lại sau khi có $checkout_items
$flag = false;
$r_us = null;
$last_order_id_for_vnpay = 0;

// --- Kiểm tra kết nối DB ---
if (!isset($conn) || !$conn) {
    error_log("Database connection failed on ThanhToan page.");
    die("Lỗi kết nối cơ sở dữ liệu.");
}
// Đảm bảo kết nối dùng UTF-8 (Nên có)
mysqli_set_charset($conn, 'utf8');

// --- 1. Xác định chế độ và chuẩn bị $checkout_items ---
if (isset($_GET['buy_now']) && $_GET['buy_now'] == '1' && isset($_GET['idSP']) && filter_var($_GET['idSP'], FILTER_VALIDATE_INT)) {
    // --- Chế độ Mua Ngay ---
    $is_buy_now_mode = true;
    $buy_now_id = (int)$_GET['idSP'];
    $buy_now_qty = 1;

    // !!! CẢNH BÁO: NÊN DÙNG PREPARED STATEMENT !!!
    // === SỬA: Lấy thêm GiaKhuyenmai ===
    $sql_buy_now = "SELECT idSP, TenSP, GiaBan, GiaKhuyenmai, SoLuongTonKho FROM sanpham WHERE idSP = " . $buy_now_id;
    $rs_buy_now = mysqli_query($conn, $sql_buy_now);

    if ($rs_buy_now && mysqli_num_rows($rs_buy_now) > 0) {
        $product_buy_now = mysqli_fetch_assoc($rs_buy_now);
        if ($product_buy_now['SoLuongTonKho'] < $buy_now_qty) {
             $_SESSION['error_message'] = "Sản phẩm '" . htmlspecialchars($product_buy_now['TenSP']) . "' không đủ số lượng.";
             header('Location: ../pages/ChiTietSanPham.php?idSP=' . $buy_now_id); // Giữ nguyên link
             exit;
        }

        // === SỬA: Xác định giá sử dụng (Ưu tiên KM) ===
        $price_to_use = (isset($product_buy_now['GiaKhuyenmai']) && is_numeric($product_buy_now['GiaKhuyenmai']) && $product_buy_now['GiaKhuyenmai'] > 0)
                        ? $product_buy_now['GiaKhuyenmai']
                        : $product_buy_now['GiaBan'];
        // =============================================

        // Tạo mảng checkout chỉ chứa sản phẩm này
        $checkout_items[$buy_now_id] = [
            'idSP' => $product_buy_now['idSP'],
            'TenSP' => $product_buy_now['TenSP'],
            'GiaBan' => $product_buy_now['GiaBan'], // Vẫn giữ giá gốc nếu cần
            'price_to_use' => $price_to_use,     // === SỬA: Thêm giá sẽ dùng để tính tiền ===
            'qty' => $buy_now_qty
        ];
         mysqli_free_result($rs_buy_now);
    } else {
        $_SESSION['error_message'] = "Không tìm thấy sản phẩm ID: $buy_now_id.";
        header('Location: ../site/index.php?index=1'); // Giữ nguyên link gốc
        exit;
    }
} else {
    // --- Chế độ Thanh toán Giỏ hàng ---
    $is_buy_now_mode = false;
    if (isset($_SESSION['cart']) && is_array($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        // !!! LƯU Ý QUAN TRỌNG !!!
        // Để giá KM hoạt động cho giỏ hàng, code THÊM VÀO GIỎ HÀNG của bạn
        // PHẢI lưu giá đúng (KM hoặc gốc) vào session cart, ví dụ:
        // $_SESSION['cart'][$idSP]['price_to_use'] = $gia_khuyen_mai_hoac_goc;
        $checkout_items = $_SESSION['cart'];
    } else {
        $checkout_items = [];
    }
}

// --- 2. Lấy thông tin user nếu đã đăng nhập (Giữ nguyên logic gốc) ---
if (isset($_SESSION['IDUser'])) {
    // !!! CẢNH BÁO: NÊN DÙNG PREPARED STATEMENT !!!
    $sql_u = "select * from users where idUser=" . (int)$_SESSION['IDUser'];
    $query_u = mysqli_query($conn, $sql_u);
    if($query_u && mysqli_num_rows($query_u) > 0){
        $r_us = mysqli_fetch_array($query_u);
        $flag = true;
        mysqli_free_result($query_u);
    } else {
        unset($_SESSION['IDUser'],$_SESSION['Username']); $flag = false; $r_us = null;
    }
} else {
    $flag = false;
}

// --- 3. Lấy danh sách phương thức giao hàng (Giữ nguyên logic gốc) ---
$sql_ptgh_display = 'select * from phuongthucgiaohang where AnHien =1';
$rs_ptgh_display = mysqli_query($conn, $sql_ptgh_display); // Biến này sẽ dùng ở HTML

// --- 4. Tính tổng tiền hàng hóa ($tongtien) ---
$tongtien = 0;
if (!empty($checkout_items)) {
    foreach ($checkout_items as $item) {
        // === SỬA: Ưu tiên dùng price_to_use nếu có, không thì dùng GiaBan ===
        $gia_item = 0;
        if (isset($item['price_to_use']) && is_numeric($item['price_to_use'])) {
            $gia_item = $item['price_to_use'];
        } elseif (isset($item['GiaBan']) && is_numeric($item['GiaBan'])) {
            $gia_item = $item['GiaBan']; // Fallback về giá gốc
        }
        // ====================================================================

        // Kiểm tra số lượng và giá hợp lệ (giữ nguyên kiểm tra gốc)
        if (isset($item['qty']) && is_numeric($item['qty']) && $gia_item > 0) {
            $tongtien += (int)$item['qty'] * $gia_item; // Tính tổng bằng giá đúng
        }
    }
}

// --- 5. XỬ LÝ FORM KHI NHẤN NÚT "Đặt hàng" (Giữ nguyên logic gốc) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['OK'])) {

     // Kiểm tra lại giỏ hàng và tổng tiền trước khi xử lý
     if (empty($checkout_items)) { // Kiểm tra mảng rỗng
         echo "<script language='JavaScript'> alert('Giỏ hàng của bạn đang trống!'); window.history.back();</script>";
         exit;
     }
     // Tính lại tổng tiền để đảm bảo chính xác khi POST
     $tongtien_check = 0;
     foreach ($checkout_items as $item) {
         $gia_item_check = 0;
         if (isset($item['price_to_use']) && is_numeric($item['price_to_use'])) { $gia_item_check = $item['price_to_use']; }
         elseif (isset($item['GiaBan']) && is_numeric($item['GiaBan'])) { $gia_item_check = $item['GiaBan']; }
         if (isset($item['qty']) && is_numeric($item['qty']) && $gia_item_check > 0) {
             $tongtien_check += (int)$item['qty'] * $gia_item_check;
         }
     }
     if ($tongtien_check <= 0) {
        echo "<script language='JavaScript'> alert('Tổng tiền đơn hàng không hợp lệ!'); window.history.back();</script>";
        exit;
     }
     $tongtien = $tongtien_check; // Cập nhật tổng tiền hàng cuối cùng

    // Lấy thông tin từ form (Giữ nguyên)
    $selected_ptgh_id = isset($_POST['PTGH']) ? (int)$_POST['PTGH'] : 0;
    $selected_pttt = isset($_POST['PTThanhToan']) ? $_POST['PTThanhToan'] : 'COD';
    $date = date('Y-m-d H:i:s');
    // === THÊM: Lấy ghi chú từ form ===
    $ghiChu = isset($_POST['GhiChu']) ? trim($_POST['GhiChu']) : '';
    // =================================

    $idUser = 0; $tenNguoiNhan = ''; $diaChi = ''; $sdt = ''; $email = ''; $form_valid = false;

    // === SỬA: Lấy và validate thông tin giao hàng (kể cả khi đã đăng nhập) ===
    if ($flag == true && $r_us) {
        $idUser = (int)$r_us['idUser']; // Giữ idUser gốc
        // Lấy thông tin từ form POST (vì user có thể đã sửa)
        $tenNguoiNhan = isset($_POST['HoTen']) ? trim($_POST['HoTen']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $diaChi = isset($_POST['DiaChi']) ? trim($_POST['DiaChi']) : '';
        $sdt = isset($_POST['SDT']) ? trim($_POST['SDT']) : '';
        // Validate thông tin đã sửa
        if (empty($tenNguoiNhan) || empty($email) || empty($diaChi) || empty($sdt) || $selected_ptgh_id <= 0) { echo "<script language='JavaScript'> alert('Vui lòng kiểm tra lại thông tin giao hàng và chọn phương thức giao hàng!'); window.history.back();</script>"; exit; }
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { echo "<script language='JavaScript'> alert('Email không hợp lệ!'); window.history.back();</script>"; exit; }
        elseif (!preg_match('/^[0-9]{10,11}$/', $sdt)) { echo "<script language='JavaScript'> alert('Số điện thoại không hợp lệ!'); window.history.back();</script>"; exit; }
        else { $form_valid = true; }
    } else { // Khách vãng lai (giữ nguyên)
        $tenNguoiNhan = isset($_POST['HoTen']) ? trim($_POST['HoTen']) : ''; $email = isset($_POST['email']) ? trim($_POST['email']) : ''; $diaChi = isset($_POST['DiaChi']) ? trim($_POST['DiaChi']) : ''; $sdt = isset($_POST['SDT']) ? trim($_POST['SDT']) : '';
        if (empty($tenNguoiNhan) || empty($email) || empty($diaChi) || empty($sdt) || $selected_ptgh_id <= 0) { echo "<script language='JavaScript'> alert('Vui lòng nhập đầy đủ thông tin và chọn phương thức giao hàng!'); window.history.back();</script>"; exit; }
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { echo "<script language='JavaScript'> alert('Email không hợp lệ!'); window.history.back();</script>"; exit; }
        elseif (!preg_match('/^[0-9]{10,11}$/', $sdt)) { echo "<script language='JavaScript'> alert('Số điện thoại không hợp lệ!'); window.history.back();</script>"; exit; }
        else { $form_valid = true; }
    }
    // ===================================================================

     // Tính phí vận chuyển (Giữ nguyên)
     $shipping_fee = 0;
     if ($selected_ptgh_id > 0) {
         // !!! CẢNH BÁO: NÊN DÙNG PREPARED STATEMENT !!!
         $sql_get_fee = "SELECT Phi FROM phuongthucgiaohang WHERE idGH = " . $selected_ptgh_id . " AND AnHien = 1";
         $rs_get_fee = mysqli_query($conn, $sql_get_fee);
         if ($rs_get_fee && mysqli_num_rows($rs_get_fee) > 0) {
             $fee_data = mysqli_fetch_assoc($rs_get_fee);
             $shipping_fee = (int)$fee_data['Phi'];
             mysqli_free_result($rs_get_fee);
         } else {
              echo "<script language='JavaScript'> alert('Phương thức giao hàng không hợp lệ!'); window.history.back();</script>"; exit;
         }
     } else {
         // Đã kiểm tra ở trên, nhưng thêm phòng hờ
         if($form_valid) { echo "<script language='JavaScript'> alert('Chưa chọn phương thức giao hàng hợp lệ!'); window.history.back();</script>"; exit; }
     }
     // === SỬA: $tongtien đã được tính theo giá đúng (KM hoặc gốc) ===
     $total_amount_final = $tongtien + $shipping_fee;
     // =============================================================

     // Lưu đơn hàng nếu form hợp lệ (Giữ nguyên)
     if ($form_valid) {
         // Lấy ID đơn hàng mới (Giữ nguyên cách cũ)
         $sl_donhang = "SELECT idDH FROM donhang ORDER BY idDH DESC LIMIT 1";
         $rs_donhang = mysqli_query($conn, $sl_donhang);
         $sodh = 1; if($rs_donhang && $row_sodh = mysqli_fetch_assoc($rs_donhang)){ $sodh = (int)$row_sodh['idDH'] + 1; } if($rs_donhang) mysqli_free_result($rs_donhang);
         $last_order_id_for_vnpay = $sodh;

         mysqli_begin_transaction($conn);
         // !!! CẢNH BÁO SQL INJECTION !!!
         // === SỬA: Thêm cột GhiChu vào câu SQL ===
         $sql_dh = "INSERT INTO donhang (idDH, idUser, ThoiDiemDatHang, TenNguoiNhan, DiaChi, SDT, DaXuLy, idPTGH, TongTien, Email, PTThanhToan, GhiChu) VALUES (" . $sodh . ", " . $idUser . ", '" . $date . "', N'" . mysqli_real_escape_string($conn, $tenNguoiNhan) . "', N'" . mysqli_real_escape_string($conn, $diaChi) . "', '" . mysqli_real_escape_string($conn, $sdt) . "', 0, " . $selected_ptgh_id . ", " . $total_amount_final . ", '" . mysqli_real_escape_string($conn, $email) . "', '" . mysqli_real_escape_string($conn, $selected_pttt) . "', N'" . mysqli_real_escape_string($conn, $ghiChu) . "')"; // Thêm biến $ghiChu
         $rs_dh_success = mysqli_query($conn, $sql_dh);

         if ($rs_dh_success) {
             $last_order_id = $sodh;
             $all_details_saved = true;
             foreach ($checkout_items as $item_id => $item) {
                 // === SỬA: Xác định giá để lưu vào DB (ưu tiên price_to_use) ===
                 $gia_luu_db = isset($item['price_to_use']) ? $item['price_to_use'] : (isset($item['GiaBan']) ? $item['GiaBan'] : 0);
                 if (!is_numeric($gia_luu_db)) { $gia_luu_db = 0; error_log("Invalid price for saving...");}
                 // ============================================================

                 // !!! CẢNH BÁO SQL INJECTION !!!
                 // === SỬA: Dùng $gia_luu_db thay vì $item['GiaBan'] ===
                 $sql_ctdh = "INSERT INTO donhangchitiet(idDH, idSP, TenSP, SoLuong, Gia) VALUES (" . $last_order_id . ", " . $item['idSP'] . ", N'" . mysqli_real_escape_string($conn, $item['TenSP']) . "', " . (int)$item['qty'] . ", " . $gia_luu_db . ")";
                 $rs_ctdh = mysqli_query($conn, $sql_ctdh);

                 // Giữ nguyên update kho (Giữ nguyên SQL gốc)
                 // !!! CẢNH BÁO SQL INJECTION !!!
                 $sql_sanpham = "UPDATE sanpham SET SoLuongTonKho = SoLuongTonKho - " . (int)$item['qty'] . ", SoLanMua = SoLanMua + 1 WHERE idSP = " . $item['idSP'] . " AND SoLuongTonKho >= ".(int)$item['qty'];
                 $rs_sanpham = mysqli_query($conn, $sql_sanpham);
                 $affected_rows = mysqli_affected_rows($conn);

                 if (!$rs_ctdh || !$rs_sanpham || $affected_rows <= 0) {
                     $all_details_saved = false; error_log("Failed detail/stock update..."); break;
                 }
             }

             // Commit/Rollback và chuyển hướng (Giữ nguyên)
             if ($all_details_saved) {
                 mysqli_commit($conn);
                 if (!$is_buy_now_mode) { unset($_SESSION['cart']); }
                 $_SESSION['orderID'] = $last_order_id;

                 if ($selected_pttt == 'VNPay') {
                     // === SỬA: $total_amount_final đã bao gồm KM và ship ===
                     generateVNPayUrl($total_amount_final, "Thanh toan don hang #" . $last_order_id);
                     exit;
                 } else {
                     echo "<script> alert('Đơn hàng của bạn (ID: #".$last_order_id.") đã được ghi nhận!'); location.href='../site/index.php?index=1'; </script>";
                     exit;
                 }
             } else {
                 mysqli_rollback($conn);
                 // Thay vì dùng $_SESSION, dùng lại alert gốc
                 echo "<script language='JavaScript'> alert('Lỗi khi lưu chi tiết đơn hàng hoặc cập nhật kho!'); window.history.back();</script>";
                 // $_SESSION['error_message'] = 'Lỗi khi lưu chi tiết đơn hàng hoặc cập nhật kho!';
                 // header('Location: ' . $_SERVER['PHP_SELF'] . ($is_buy_now_mode ? '?buy_now=1&idSP='.$buy_now_id : ''));
                 exit;
             }
          } else {
                 error_log("Order insert failed: " . mysqli_error($conn));
                 // Thay vì dùng $_SESSION, dùng lại alert gốc
                 echo "<script language='JavaScript'> alert('Lỗi khi tạo đơn hàng!'); window.history.back();</script>";
                 // $_SESSION['error_message'] = 'Lỗi khi tạo đơn hàng!';
                 // header('Location: ' . $_SERVER['PHP_SELF'] . ($is_buy_now_mode ? '?buy_now=1&idSP='.$buy_now_id : ''));
                 exit;
          }
     } // End if ($form_valid)
} // --- Kết thúc xử lý POST ---


// --- Hàm VNPay (Giữ nguyên) ---
function getRandomNumber($length) { /* Giữ nguyên */ $characters = '0123456789'; $randomString = ''; for ($i = 0; $i < $length; $i++) { $randomString .= $characters[rand(0, strlen($characters) - 1)]; } return $randomString; }
function generateVNPayUrl($amount, $orderInfo) {
     global $last_order_id_for_vnpay; // Lấy ID đơn hàng đã lưu
     date_default_timezone_set('Asia/Ho_Chi_Minh');
     $vnp_Version = "2.1.0"; $vnp_Command = "pay"; $orderType = "other";
     // === SỬA: Đặt NCB làm mặc định ===
     $bankCode = "NCB";
     // ==================================
     $vnp_TmnCode = "0S7T01T8"; // !!! THAY TmnCode !!!
     $vnp_TxnRef = $last_order_id_for_vnpay . '_' . time();
     $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
     $vnp_ReturnUrl = "http://localhost/site/xulyvnpay.php"; // Giữ nguyên link
     $secretKey = "BEZLUPOPOTXTDYZHCBGDJBHFJPBLSARL"; // !!! THAY Secret Key !!!

     // === SỬA: $amount đã là tổng cuối cùng (gồm KM, ship) ===
     $vnp_Params = [
         "vnp_Version" => $vnp_Version, "vnp_Command" => $vnp_Command, "vnp_TmnCode" => $vnp_TmnCode,
         "vnp_Amount" => $amount * 100, "vnp_CurrCode" => "VND", "vnp_BankCode" => $bankCode, // Thêm bankCode vào đây
         "vnp_TxnRef" => $vnp_TxnRef, "vnp_OrderInfo" => $orderInfo, "vnp_OrderType" => $orderType,
         "vnp_Locale" => "vn", "vnp_ReturnUrl" => $vnp_ReturnUrl, "vnp_IpAddr" => $vnp_IpAddr,
         "vnp_CreateDate" => date('YmdHis')
     ];
     // Bỏ kiểm tra empty bankCode vì đã gán giá trị
     // if (empty($vnp_Params['vnp_BankCode'])) { unset($vnp_Params['vnp_BankCode']); }

     ksort($vnp_Params); $hashdata = ""; $query = ""; $i = 0;
     foreach ($vnp_Params as $key => $value) {
         if ($value === "" || $value === null) continue;
         if ($i == 1) { $hashdata .= '&' . urlencode($key) . "=" . urlencode($value); }
         else { $hashdata .= urlencode($key) . "=" . urlencode($value); $i = 1; }
         $query .= urlencode($key) . "=" . urlencode($value) . '&';
     }
     $query = rtrim($query, '&'); // Bỏ dấu & cuối
     $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html"; // Sandbox
     $vnp_SecureHash = hash_hmac("sha512", $hashdata, $secretKey);
     $vnp_Url .= "?" . $query . "&vnp_SecureHash=" . $vnp_SecureHash; // Thêm hash vào cuối
     header("Location: " . $vnp_Url); exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php include_once("header.php"); // Giữ nguyên ?>
    <title>Thanh toán</title>
    <?php include_once("header1.php"); // Giữ nguyên ?>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/bootstrap-theme.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style> /* Giữ nguyên CSS gốc + CSS giá KM */
         body { background-color: #f0f0f0; padding-top: 20px; padding-bottom: 20px; } .page-frame { background-color: #fff0f5; padding: 20px; border-radius: 8px; max-width: 1200px; margin: 0 auto; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); } .dancach { margin-bottom: 15px; } .form-control { border-radius: 4px; } textarea.form-control { resize: vertical; } .order-summary { background-color: #f8f9fa; padding: 20px; border-radius: 5px; border: 1px solid #dee2e6; margin-top: 20px; } .order-summary h4 { margin-top: 0; border-bottom: 1px solid #ccc; padding-bottom: 10px; margin-bottom: 15px;} .summary-row { border-bottom: 1px dashed #eee; padding: 8px 0; display: flex; align-items: center; flex-wrap: wrap;} .summary-row:last-of-type { border-bottom: 1px solid #ccc; } .total-row { border-top: 2px solid #333; margin-top: 10px; padding-top: 10px; font-size: 1.1em; display: flex; align-items: center; flex-wrap: wrap;} .error-message { color: red; font-weight: bold; text-align: center; margin-bottom: 15px;} .shipping-info b { display: inline-block; min-width: 120px; } .shipping-info i { font-style: normal; font-weight: normal; } .control-label { text-align: right; padding-top: 7px; font-weight: bold;} @media (max-width: 991px) { .control-label { text-align: left; margin-bottom: 5px;} .order-summary { margin-top: 30px; } } select.form-control, select.check { height: 34px; }
         .summary-product-name { flex-basis: 55%; padding-right: 5px; word-break: break-word; }
         .summary-quantity { flex-basis: 15%; text-align: center; }
         .summary-price { flex-basis: 30%; text-align: right; }
         .summary-label { flex-basis: 60%; }
         .summary-value { flex-basis: 40%; text-align: right; }
         .total-label { flex-basis: 50%; }
         .total-value { flex-basis: 50%; text-align: right; }
         .original-price { text-decoration: line-through; color: #888; font-size: 0.9em; margin-left: 5px;} /* Giá gốc gạch ngang */
         .discounted-price { color: red; font-weight: bold;} /* Giá KM */
    </style>
</head>
<body>
    <div class="page-frame">
        <?php include_once("header2.php"); ?>
        <div class="container">
            <div class="row"> <div class="col-xs-12"> <div class="indexh3 text-center"> <?php if ($flag) echo "<h3>THÔNG TIN ĐẶT HÀNG CỦA BẠN</h3>"; else echo "<h3>Đặt hàng không cần đăng ký</h3>" ?> <div class="sep-wrap center nz-clearfix" style="margin-bottom: 30px;"> <div class="nz-separator solid" style="margin-top:10px; border-bottom: 2px solid #ddd; width:50px; margin-left: auto; margin-right: auto;"></div> </div> </div> <?php /* Bỏ hiển thị lỗi session, giữ alert gốc */ // if (isset($_SESSION['error_message'])) { ... } ?> </div> </div>
            <div class="row">
                 <form class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?><?php echo $is_buy_now_mode ? '?buy_now=1&idSP='.$buy_now_id : ''; ?>" method="post" name="checkoutForm">
                    <div class="col-md-8">

                        <?php if ($flag == true && $r_us): ?>
                            <h4><b>Thông tin giao hàng</b> <small>(Bạn có thể chỉnh sửa nếu cần)</small></h4>
                            <div class="form-group">
                                <label for="HoTen" class="col-sm-3 control-label">Họ tên người nhận<span style="color:red;">*</span>:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="HoTen" name="HoTen" value="<?php echo htmlspecialchars($r_us['HoTenK']); ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                 <label for="email" class="col-sm-3 control-label">Email<span style="color:red;">*</span>:</label>
                                 <div class="col-sm-9">
                                     <input type="email" class="form-control" id="email" name="email" placeholder="abc@gmail.com" value="<?php echo htmlspecialchars($r_us['Email']); ?>" required>
                                 </div>
                            </div>
                            <div class="form-group">
                                 <label for="DiaChi" class="col-sm-3 control-label">Địa chỉ nhận hàng<span style="color:red;">*</span>:</label>
                                 <div class="col-sm-9">
                                     <textarea class="form-control" rows="3" id="DiaChi" name="DiaChi" placeholder="Vui lòng nhập chính xác địa chỉ nhận hàng!" required><?php echo htmlspecialchars($r_us['DiaChi']); ?></textarea>
                                 </div>
                            </div>
                            <div class="form-group">
                                 <label for="SDT" class="col-sm-3 control-label">Số điện thoại<span style="color:red;">*</span>:</label>
                                 <div class="col-sm-9">
                                     <input type="tel" class="form-control" id="SDT" name="SDT" placeholder="Vui lòng nhập số điện thoại" value="<?php echo htmlspecialchars($r_us['DienThoai']); ?>" required pattern="[0-9]{10,11}" title="Số điện thoại gồm 10 hoặc 11 chữ số">
                                 </div>
                            </div>
                            <hr>
                        <?php else: /* Khách vãng lai - Giữ nguyên form gốc */ ?>
                            <h4><b>Thông tin giao hàng</b></h4>
                            <div class="form-group"> <label for="HoTen" class="col-sm-3 control-label">Họ tên người nhận<span style="color:red;">*</span>:</label> <div class="col-sm-9"> <input type="text" class="form-control" id="HoTen" name="HoTen" value="<?php echo isset($_POST['HoTen']) ? htmlspecialchars($_POST['HoTen']) : ''; ?>" required> </div> </div>
                            <div class="form-group"> <label for="email" class="col-sm-3 control-label">Email<span style="color:red;">*</span>:</label> <div class="col-sm-9"> <input type="email" class="form-control" id="email" name="email" placeholder="abc@gmail.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required> </div> </div>
                            <div class="form-group"> <label for="DiaChi" class="col-sm-3 control-label">Địa chỉ nhận hàng<span style="color:red;">*</span>:</label> <div class="col-sm-9"> <textarea class="form-control" rows="3" id="DiaChi" name="DiaChi" placeholder="Vui lòng nhập chính xác địa chỉ nhận hàng!" required><?php echo isset($_POST['DiaChi']) ? htmlspecialchars($_POST['DiaChi']) : ''; ?></textarea> </div> </div>
                            <div class="form-group"> <label for="SDT" class="col-sm-3 control-label">Số điện thoại<span style="color:red;">*</span>:</label> <div class="col-sm-9"> <input type="tel" class="form-control" id="SDT" name="SDT" placeholder="Vui lòng nhập số điện thoại" value="<?php echo isset($_POST['SDT']) ? htmlspecialchars($_POST['SDT']) : ''; ?>" required pattern="[0-9]{10,11}" title="Số điện thoại gồm 10 hoặc 11 chữ số"> </div> </div>
                            <hr>
                        <?php endif; ?>
                        <h4><b>Vận chuyển và Thanh toán</b></h4>
                          <div class="form-group"> <label for="PTGH" class="col-sm-3 control-label">Nhận hàng<span style="color:red;">*</span>:</label> <div class="col-sm-9"> <select name="PTGH" class="form-control" id="PTGH" required> <option value="">-- Chọn phương thức --</option> <?php if (isset($rs_ptgh_display) && $rs_ptgh_display && mysqli_num_rows($rs_ptgh_display) > 0) { mysqli_data_seek($rs_ptgh_display, 0); while ($r_gh_display = $rs_ptgh_display->fetch_assoc()) { $selected_gh = (isset($_POST['PTGH']) && $_POST['PTGH'] == $r_gh_display['idGH']) ? 'selected' : ''; echo "<option value='" . $r_gh_display['idGH'] . "' data-fee='" . (int)$r_gh_display['Phi'] . "' ".$selected_gh.">" . htmlspecialchars($r_gh_display['TenGH']) . ' - ' . number_format($r_gh_display['Phi']) . ' VNĐ' . "</option>"; } /* mysqli_free_result($rs_ptgh_display); ĐÃ FREE Ở TRÊN*/ } else { echo "<option value='' disabled>Không có PTVC</option>"; } ?> </select> </div> </div>
                          <div class="form-group"> <label for="PTThanhToan" class="col-sm-3 control-label">Thanh toán<span style="color:red;">*</span>:</label> <div class="col-sm-9"> <select name="PTThanhToan" class="form-control" id="PTThanhToan" required> <?php $selected_tt = isset($_POST['PTThanhToan']) ? $_POST['PTThanhToan'] : 'COD'; ?> <option value="COD" <?php echo ($selected_tt == 'COD') ? 'selected' : ''; ?>>Thanh toán khi nhận hàng (COD)</option> <option value="VNPay" <?php echo ($selected_tt == 'VNPay') ? 'selected' : ''; ?>>Thanh toán qua VNPay</option> </select> </div> </div>

                         <hr>
                         <h4><b>Ghi chú đơn hàng</b></h4>
                         <div class="form-group">
                            <label for="GhiChu" class="col-sm-3 control-label">Ghi chú:</label>
                            <div class="col-sm-9">
                                <textarea class="form-control" rows="3" id="GhiChu" name="GhiChu" placeholder="Ví dụ: Giao hàng vào giờ hành chính, gọi trước khi giao,..."><?php echo isset($_POST['GhiChu']) ? htmlspecialchars($_POST['GhiChu']) : ''; ?></textarea>
                            </div>
                         </div>
                         <div class="form-group" style="margin-top: 20px;"> <div class="col-sm-9 col-sm-offset-3"> <p class="text-muted"><small>Chúng tôi sẽ liên hệ quý khách theo số điện thoại hoặc email để xác nhận.</small></p> </div> </div>

                    </div> <div class="col-md-4">
                        <div class="order-summary">
                             <h4><b>Thông tin đơn hàng</b></h4>
                            <?php if (!empty($checkout_items)) : ?>
                                <?php foreach ($checkout_items as $item_id => $item) : ?>
                                    <?php
                                    $display_price = isset($item['price_to_use']) ? $item['price_to_use'] : (isset($item['GiaBan']) ? $item['GiaBan'] : 0);
                                    $original_price = isset($item['GiaBan']) ? $item['GiaBan'] : 0;
                                    $has_discount = ($display_price < $original_price && $display_price > 0);
                                    $display_qty = isset($item['qty']) ? (int)$item['qty'] : 0;
                                    $line_total = $display_price * $display_qty;
                                    ?>
                                    <div class="summary-row">
                                        <div class="summary-product-name">
                                            <b><?php echo htmlspecialchars($item['TenSP']); ?></b><br>
                                            <?php if ($has_discount): ?>
                                                <small><span class="discounted-price"><?php echo number_format($display_price, 0); ?> đ</span> <span class="original-price"><?php echo number_format($original_price, 0); ?> đ</span></small>
                                            <?php else: ?>
                                                <small><?php echo number_format($display_price, 0); ?> đ</small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="summary-quantity">x<?php echo $display_qty; ?></div>
                                        <div class="summary-price"><b><?php echo number_format($line_total, 0); ?> VNĐ</b></div>
                                    </div>
                                <?php endforeach; ?>

                                <div class="summary-row"> <div class="summary-label">Tổng tiền hàng:</div> <div class="summary-value"> <b id="subtotal-display"><?php echo number_format($tongtien, 0); ?> VNĐ</b> </div> </div>
                                <div class="summary-row"> <div class="summary-label">Phí vận chuyển:</div> <div class="summary-value"> <b id="shipping-fee-display">Chọn PTVC</b> </div> </div>
                                <div class="total-row"> <div class="total-label"><h4><b style="color: red;">TỔNG CỘNG:</b></h4></div> <div class="total-value"> <h4 id="total-amount-display"><b style="color: red;">---</b></h4> </div> </div>
                                <p class="text-center text-muted" style="margin-top: 10px;"><small>(Tổng cộng sẽ được cập nhật sau khi bạn chọn phương thức nhận hàng)</small></p>
                            <?php else : ?>
                                <p class='text-center text-danger'><b>Giỏ hàng của bạn đang trống!</b></p>
                                <p class='text-center'><a href="../site/index.php?index=1" class="btn btn-primary">Tiếp tục mua sắm</a></p>
                            <?php endif; ?>
                        </div> <div class="row" style="margin-top: 20px;"> <div class="col-xs-12 text-center"> <button type="submit" class="btn btn-danger btn-lg" name="OK" <?php echo (empty($checkout_items)) ? 'disabled' : ''; ?>> <span class="glyphicon glyphicon-check"></span> HOÀN TẤT ĐẶT HÀNG </button> </div> </div>
                    </div> </form>
            </div>
        </div>
        <?php include_once("footer.php"); // Giữ nguyên ?>
    </div>
    <script src="../js/jquery-3.1.1.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script>
    // --- JavaScript gốc để cập nhật phí VC và tổng tiền ---
    $(document).ready(function() {
        var currentSubtotal = <?php echo $tongtien; ?>;
        function formatCurrency(number) {
             if (isNaN(number)) return '---';
             return number.toLocaleString('vi-VN') + ' VNĐ';
        }
        function updateTotals(shippingFee) {
             shippingFee = parseInt(shippingFee, 10);
             if (isNaN(shippingFee) || shippingFee < 0) { shippingFee = 0; }
             var feeDisplay = 'Chọn PTVC';
             if ($('#PTGH').val() !== "") { feeDisplay = (shippingFee === 0) ? 'Miễn phí' : formatCurrency(shippingFee); }
             var totalAmount = currentSubtotal + shippingFee;
             $('#shipping-fee-display').html('<b>' + feeDisplay + '</b>');
             if ($('#PTGH').val() !== "") { $('#total-amount-display').html('<b style="color: red;">' + formatCurrency(totalAmount) + '</b>'); }
             else { $('#total-amount-display').html('<b style="color: red;">' + formatCurrency(currentSubtotal) + '</b>'); }
        }
        var initialShippingSelect = $('#PTGH');
        if (initialShippingSelect.val()) {
             var initialFee = parseInt(initialShippingSelect.find('option:selected').data('fee'), 10);
             if (!isNaN(initialFee)) { updateTotals(initialFee); }
             else { updateTotals(0); $('#shipping-fee-display').html('<b>Chọn PTVC</b>'); }
        } else { updateTotals(0); $('#shipping-fee-display').html('<b>Chọn PTVC</b>'); }
        $('#PTGH').on('change', function() {
             var selectedOption = $(this).find('option:selected');
             var shippingFee = 0;
             if ($(this).val() !== "") {
                 shippingFee = parseInt(selectedOption.data('fee'), 10);
                 if (isNaN(shippingFee)) { shippingFee = 0; console.error("Invalid shipping fee data."); }
             }
             updateTotals(shippingFee);
        });
    });
    </script>
</body>
</html>