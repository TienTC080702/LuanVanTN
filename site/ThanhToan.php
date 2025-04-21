<?php
// Bắt đầu session ở đầu tiên
session_start();

// Include file kết nối
include_once('../connection/connect_database.php');

// --- Khởi tạo biến ---
$is_buy_now_mode = false;
$checkout_items = [];
$tongtien = 0; // Tổng tiền hàng hóa (chưa có phí ship, chưa giảm giá)
$flag = false; // Cờ kiểm tra đăng nhập
$r_us = null; // Thông tin user
$last_order_id_for_vnpay = 0; // ID đơn hàng cho VNPay
$validated_voucher_code = null; // Để khôi phục trạng thái JS nếu cần
$discount_amount = 0.00;        // Để khôi phục trạng thái JS nếu cần
$available_vouchers = []; // Array để chứa các voucher hợp lệ

// --- Kiểm tra kết nối DB ---
if (!isset($conn) || !$conn) {
    error_log("Database connection failed on ThanhToan page.");
    die("Lỗi kết nối cơ sở dữ liệu.");
}
// Đảm bảo kết nối dùng UTF-8
mysqli_set_charset($conn, 'utf8');

// --- 1. Xác định chế độ và chuẩn bị $checkout_items ---
if (isset($_GET['buy_now']) && $_GET['buy_now'] == '1' && isset($_GET['idSP']) && filter_var($_GET['idSP'], FILTER_VALIDATE_INT)) {
    // --- Chế độ Mua Ngay ---
    $is_buy_now_mode = true;
    $buy_now_id = (int)$_GET['idSP'];
    $buy_now_qty = 1;

    $sql_buy_now = "SELECT idSP, TenSP, GiaBan, GiaKhuyenmai, SoLuongTonKho FROM sanpham WHERE idSP = ?";
    $stmt_buy_now = mysqli_prepare($conn, $sql_buy_now);
    if ($stmt_buy_now) {
        mysqli_stmt_bind_param($stmt_buy_now, "i", $buy_now_id);
        mysqli_stmt_execute($stmt_buy_now);
        $rs_buy_now = mysqli_stmt_get_result($stmt_buy_now);

        if ($rs_buy_now && mysqli_num_rows($rs_buy_now) > 0) {
            $product_buy_now = mysqli_fetch_assoc($rs_buy_now);
            if ($product_buy_now['SoLuongTonKho'] < $buy_now_qty) {
                $_SESSION['error_message'] = "Sản phẩm '" . htmlspecialchars($product_buy_now['TenSP']) . "' không đủ số lượng.";
                header('Location: ../pages/ChiTietSanPham.php?idSP=' . $buy_now_id);
                exit;
            }
            $price_to_use = (isset($product_buy_now['GiaKhuyenmai']) && is_numeric($product_buy_now['GiaKhuyenmai']) && $product_buy_now['GiaKhuyenmai'] > 0)
                            ? $product_buy_now['GiaKhuyenmai']
                            : $product_buy_now['GiaBan'];
            $checkout_items[$buy_now_id] = [
                'idSP' => $product_buy_now['idSP'],
                'TenSP' => $product_buy_now['TenSP'],
                'GiaBan' => $product_buy_now['GiaBan'],
                'price_to_use' => $price_to_use,
                'qty' => $buy_now_qty
            ];
            mysqli_free_result($rs_buy_now);
        } else {
             $_SESSION['error_message'] = "Không tìm thấy sản phẩm ID: $buy_now_id.";
             header('Location: ../site/index.php?index=1');
             exit;
        }
        mysqli_stmt_close($stmt_buy_now);
    } else {
        error_log("Prepare statement failed (buy now product): " . mysqli_error($conn));
        $_SESSION['error_message'] = "Lỗi truy vấn sản phẩm.";
        header('Location: ../site/index.php?index=1');
        exit;
    }

} else {
    // --- Chế độ Thanh toán Giỏ hàng ---
    $is_buy_now_mode = false;
    if (isset($_SESSION['cart']) && is_array($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        $checkout_items = $_SESSION['cart'];
    } else {
        $checkout_items = [];
    }
}


// --- 2. Lấy thông tin user nếu đã đăng nhập ---
if (isset($_SESSION['IDUser'])) {
    $sql_u = "SELECT * FROM users WHERE idUser = ?";
    $stmt_u = mysqli_prepare($conn, $sql_u);
    if($stmt_u){
        $idUser_session = (int)$_SESSION['IDUser'];
        mysqli_stmt_bind_param($stmt_u, "i", $idUser_session);
        mysqli_stmt_execute($stmt_u);
        $query_u = mysqli_stmt_get_result($stmt_u);
        if($query_u && mysqli_num_rows($query_u) > 0){
            $r_us = mysqli_fetch_assoc($query_u);
            $flag = true;
            mysqli_free_result($query_u);
        } else {
            unset($_SESSION['IDUser'], $_SESSION['Username']);
            $flag = false;
            $r_us = null;
        }
         mysqli_stmt_close($stmt_u);
    } else {
         error_log("Prepare statement failed (get user info): " . mysqli_error($conn));
         $flag = false;
         $r_us = null;
    }
} else {
    $flag = false;
}

// --- 3. Lấy danh sách phương thức giao hàng ---
$sql_ptgh_display = 'SELECT idGH, TenGH, Phi FROM phuongthucgiaohang WHERE AnHien = 1';
$rs_ptgh_display = mysqli_query($conn, $sql_ptgh_display);


// --- 4. Tính tổng tiền hàng hóa ($tongtien) dựa trên $checkout_items ---
$tongtien = 0.00;
if (!empty($checkout_items)) {
    foreach ($checkout_items as $item) {
        $gia_item = 0.00;
        if (isset($item['price_to_use']) && is_numeric($item['price_to_use'])) {
            $gia_item = (float)$item['price_to_use'];
        } elseif (isset($item['GiaBan']) && is_numeric($item['GiaBan'])) {
            $gia_item = (float)$item['GiaBan'];
        }
        if (isset($item['qty']) && is_numeric($item['qty']) && (int)$item['qty'] > 0 && $gia_item > 0) {
            $tongtien += (int)$item['qty'] * $gia_item;
        }
    }
}

// --- 4.5 --- Lấy danh sách VOUCHER hợp lệ tiềm năng ---
// ***** SỬA LỖI Ở ĐÂY: Đã bỏ ", mo_ta" khỏi SELECT *****
$sql_vouchers_available = "SELECT id, ma_giam_gia, loai, gia_tri
                           FROM vouchers
                           WHERE trang_thai = 1
                             AND (ngay_bat_dau IS NULL OR ngay_bat_dau <= NOW())
                             AND (ngay_ket_thuc IS NULL OR ngay_ket_thuc >= NOW())
                             AND (gia_tri_don_toi_thieu IS NULL OR gia_tri_don_toi_thieu <= ?)
                             AND (gioi_han_su_dung IS NULL OR so_lan_da_dung < gioi_han_su_dung)";

$stmt_vouchers = mysqli_prepare($conn, $sql_vouchers_available);
if ($stmt_vouchers) {
    mysqli_stmt_bind_param($stmt_vouchers, "d", $tongtien); // Bind $tongtien
    mysqli_stmt_execute($stmt_vouchers);
    $rs_vouchers = mysqli_stmt_get_result($stmt_vouchers);
    if ($rs_vouchers) {
        while ($voucher = mysqli_fetch_assoc($rs_vouchers)) {
            $available_vouchers[] = $voucher;
        }
        mysqli_free_result($rs_vouchers);
    } else {
        error_log("Error fetching available vouchers results: " . mysqli_error($conn));
    }
    mysqli_stmt_close($stmt_vouchers);
} else {
    error_log("Prepare statement failed (fetch available vouchers): " . mysqli_error($conn));
}
// --- KẾT THÚC Mục 4.5 ---


// --- 5. XỬ LÝ FORM KHI NHẤN NÚT "Đặt hàng" ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['OK'])) {

    // A. Kiểm tra lại giỏ hàng và tính lại tổng tiền hàng hóa ($tongtien)
    $checkout_items_on_post = [];
    if ($is_buy_now_mode && isset($checkout_items[$buy_now_id])) {
         $checkout_items_on_post = $checkout_items;
    } elseif (!$is_buy_now_mode && isset($_SESSION['cart'])) {
         $checkout_items_on_post = $_SESSION['cart'];
    } else {
         echo "<script language='JavaScript'> alert('Giỏ hàng của bạn đang trống hoặc có lỗi xảy ra!'); window.history.back();</script>";
         exit;
    }
    if (empty($checkout_items_on_post)) {
        echo "<script language='JavaScript'> alert('Giỏ hàng của bạn đang trống!'); window.history.back();</script>";
        exit;
    }
    $tongtien_check = 0.00;
    foreach ($checkout_items_on_post as $item) {
        $gia_item_check = 0.00;
        if (isset($item['price_to_use']) && is_numeric($item['price_to_use'])) { $gia_item_check = (float)$item['price_to_use']; }
        elseif (isset($item['GiaBan']) && is_numeric($item['GiaBan'])) { $gia_item_check = (float)$item['GiaBan']; }
        if (isset($item['qty']) && is_numeric($item['qty']) && (int)$item['qty'] > 0 && $gia_item_check > 0) {
            $tongtien_check += (int)$item['qty'] * $gia_item_check;
        }
    }
    $tongtien = $tongtien_check; // Re-calculate $tongtien based on current cart/item state


    // B. Lấy thông tin từ form POST
    $selected_ptgh_id = isset($_POST['PTGH']) ? (int)$_POST['PTGH'] : 0;
    $selected_pttt = isset($_POST['PTThanhToan']) ? $_POST['PTThanhToan'] : 'COD';
    $date_now_for_validation = date('Y-m-d H:i:s');
    $date_for_order = $date_now_for_validation;
    $ghiChu = isset($_POST['GhiChu']) ? trim($_POST['GhiChu']) : '';


    // C. --- VOUCHER START: Lấy và *RE-VALIDATE* voucher từ POST (dựa trên selection) ---
    $applied_voucher_code_from_post = isset($_POST['selected_voucher_code']) ? trim($_POST['selected_voucher_code']) : ''; // Read from <select>
    $validated_voucher_code = null;
    $discount_amount = 0.00;
    $voucher_id_to_update = null;

    if (!empty($applied_voucher_code_from_post)) {
        // Re-query to get ALL details needed for final validation, including usage limits
        $sql_voucher_check = "SELECT id, loai, gia_tri, gia_tri_don_toi_thieu, ngay_bat_dau, ngay_ket_thuc, gioi_han_su_dung, so_lan_da_dung, trang_thai
                              FROM vouchers
                              WHERE ma_giam_gia = ? AND trang_thai = 1"; // Check status again
        $stmt_voucher_check = mysqli_prepare($conn, $sql_voucher_check);

        if ($stmt_voucher_check) {
            mysqli_stmt_bind_param($stmt_voucher_check, "s", $applied_voucher_code_from_post);
            mysqli_stmt_execute($stmt_voucher_check);
            $rs_voucher = mysqli_stmt_get_result($stmt_voucher_check);

            if ($rs_voucher && $voucher_data = mysqli_fetch_assoc($rs_voucher)) {
                $is_voucher_valid = true;
                // *** Perform ALL checks again, including usage limit ***
                if ($voucher_data['ngay_bat_dau'] !== null && $date_now_for_validation < $voucher_data['ngay_bat_dau']) { $is_voucher_valid = false; }
                if ($voucher_data['ngay_ket_thuc'] !== null && $date_now_for_validation > $voucher_data['ngay_ket_thuc']) { $is_voucher_valid = false; }
                if ($voucher_data['gia_tri_don_toi_thieu'] !== null && $tongtien < (float)$voucher_data['gia_tri_don_toi_thieu']) { $is_voucher_valid = false; }
                if ($voucher_data['gioi_han_su_dung'] !== null && $voucher_data['so_lan_da_dung'] >= $voucher_data['gioi_han_su_dung']) {
                    $is_voucher_valid = false; // Final check on usage limit
                }

                if ($is_voucher_valid) {
                    $voucher_type = $voucher_data['loai'];
                    $voucher_value = (float)$voucher_data['gia_tri'];
                    if ($voucher_type == 'fixed') { $discount_amount = $voucher_value; }
                    elseif ($voucher_type == 'percent') { $discount_amount = $tongtien * ($voucher_value / 100); }
                    if ($discount_amount > $tongtien) { $discount_amount = $tongtien; }
                    if ($discount_amount < 0) { $discount_amount = 0.00; }
                    $discount_amount = round($discount_amount);
                    $validated_voucher_code = $applied_voucher_code_from_post; // Set the validated code
                    $voucher_id_to_update = (int)$voucher_data['id'];
                    // Store in session for potential page reload / JS state restoration
                    $_SESSION['applied_voucher'] = ['code' => $validated_voucher_code, 'discount' => $discount_amount];
                } else {
                     unset($_SESSION['applied_voucher']); // Failed validation
                     $validated_voucher_code = null; $discount_amount = 0.00; $voucher_id_to_update = null;
                     echo "<script> alert('Mã giảm giá đã chọn không còn hợp lệ (có thể hết hạn, không đủ điều kiện hoặc đã hết lượt sử dụng). Vui lòng chọn lại.'); window.history.back();</script>"; exit;
                }
            } else {
                // Voucher code selected doesn't exist or isn't active anymore
                unset($_SESSION['applied_voucher']);
                echo "<script> alert('Mã giảm giá đã chọn không tồn tại hoặc không hoạt động.'); window.history.back();</script>"; exit;
             }
             if($rs_voucher) mysqli_free_result($rs_voucher);
             mysqli_stmt_close($stmt_voucher_check);
        } else {
             error_log("Prepare statement failed (voucher check on POST): " . mysqli_error($conn));
             unset($_SESSION['applied_voucher']);
             $validated_voucher_code = null; $discount_amount = 0.00; $voucher_id_to_update = null;
             echo "<script> alert('Lỗi khi kiểm tra mã giảm giá. Vui lòng thử lại.'); window.history.back();</script>"; exit;
        }
    } else {
        // No voucher was selected
        unset($_SESSION['applied_voucher']);
        $validated_voucher_code = null; $discount_amount = 0.00; $voucher_id_to_update = null;
    }
    // --- VOUCHER END ---


    // D. Lấy và validate thông tin giao hàng
    $idUser = 0; $tenNguoiNhan = ''; $diaChi = ''; $sdt = ''; $email = ''; $form_valid = false;
    if ($flag == true && $r_us) {
        $idUser = (int)$r_us['idUser'];
        $tenNguoiNhan = isset($_POST['HoTen']) ? trim($_POST['HoTen']) : ''; $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $diaChi = isset($_POST['DiaChi']) ? trim($_POST['DiaChi']) : ''; $sdt = isset($_POST['SDT']) ? trim($_POST['SDT']) : '';
    } else {
        $tenNguoiNhan = isset($_POST['HoTen']) ? trim($_POST['HoTen']) : ''; $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $diaChi = isset($_POST['DiaChi']) ? trim($_POST['DiaChi']) : ''; $sdt = isset($_POST['SDT']) ? trim($_POST['SDT']) : '';
    }
    if (empty($tenNguoiNhan) || empty($diaChi) || empty($sdt) || $selected_ptgh_id <= 0) { echo "<script> alert('Vui lòng nhập đầy đủ Họ tên, Địa chỉ, Số điện thoại và chọn Phương thức giao hàng!'); window.history.back();</script>"; exit; }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) { echo "<script> alert('Email không hợp lệ hoặc bị trống!'); window.history.back();</script>"; exit; }
    if (!preg_match('/^(0[1-9][0-9]{8,9})$/', $sdt)) { echo "<script> alert('Số điện thoại không hợp lệ!'); window.history.back();</script>"; exit; }
    $form_valid = true;

    // E. Tính phí vận chuyển
     $shipping_fee = 0.00;
    if ($form_valid && $selected_ptgh_id > 0) {
        $sql_get_fee = "SELECT Phi FROM phuongthucgiaohang WHERE idGH = ? AND AnHien = 1";
         $stmt_fee = mysqli_prepare($conn, $sql_get_fee);
         if($stmt_fee){
             mysqli_stmt_bind_param($stmt_fee, "i", $selected_ptgh_id);
             mysqli_stmt_execute($stmt_fee); $rs_get_fee = mysqli_stmt_get_result($stmt_fee);
             if ($rs_get_fee && mysqli_num_rows($rs_get_fee) > 0) {
                  $fee_data = mysqli_fetch_assoc($rs_get_fee); $shipping_fee = (float)$fee_data['Phi']; mysqli_free_result($rs_get_fee);
             } else { echo "<script> alert('Phương thức giao hàng không hợp lệ!'); window.history.back();</script>"; exit; }
             mysqli_stmt_close($stmt_fee);
         } else { error_log("Prepare statement failed (get shipping fee): " . mysqli_error($conn)); echo "<script> alert('Lỗi truy vấn phí giao hàng!'); window.history.back();</script>"; exit; }
    } elseif($form_valid && $selected_ptgh_id <= 0) { echo "<script> alert('Chưa chọn phương thức giao hàng hợp lệ!'); window.history.back();</script>"; exit; }


    // F. Tính Tổng Tiền Cuối Cùng (Using validated discount from step C)
    $total_amount_final = $tongtien + $shipping_fee - $discount_amount;
    if ($total_amount_final < 0) { $total_amount_final = 0.00; }


    // G. Lưu đơn hàng vào CSDL (Using validated discount info from step C)
    if ($form_valid) {
        $sl_donhang = "SELECT MAX(idDH) as max_id FROM donhang"; $rs_donhang = mysqli_query($conn, $sl_donhang);
        $sodh = 1; if($rs_donhang && $row_sodh = mysqli_fetch_assoc($rs_donhang)){ $sodh = ($row_sodh['max_id'] !== null) ? (int)$row_sodh['max_id'] + 1 : 1; } if($rs_donhang) mysqli_free_result($rs_donhang);

        mysqli_begin_transaction($conn);
        $all_ok = true; $last_inserted_order_id = null;

        // 1. Insert vào bảng donhang (Dùng tên cột tiếng Việt)
        $sql_dh = "INSERT INTO donhang (idDH, idUser, ThoiDiemDatHang, TenNguoiNhan, DiaChi, SDT, DaXuLy, idPTGH, TongTien, Email, PTThanhToan, GhiChu, ma_giam_gia, so_tien_giam, Tax) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_dh = mysqli_prepare($conn, $sql_dh);
        if ($stmt_dh) {
            $daXuLy_value = 0; $tax_value = 0.00;
            mysqli_stmt_bind_param( $stmt_dh, "iissssiidssssdd", $sodh, $idUser, $date_for_order, $tenNguoiNhan, $diaChi, $sdt, $daXuLy_value, $selected_ptgh_id, $total_amount_final, $email, $selected_pttt, $ghiChu, $validated_voucher_code, $discount_amount, $tax_value );
            if (mysqli_stmt_execute($stmt_dh)) { $last_inserted_order_id = $sodh; $last_order_id_for_vnpay = $last_inserted_order_id; }
            else { $all_ok = false; error_log("Order insert execution failed: " . mysqli_stmt_error($stmt_dh)); }
            mysqli_stmt_close($stmt_dh);
        } else { $all_ok = false; error_log("Prepare statement failed (order insert): " . mysqli_error($conn)); }

        // 2. Insert vào donhangchitiet và Cập nhật kho
        if ($all_ok && $last_inserted_order_id !== null) {
            foreach ($checkout_items_on_post as $item_id => $item) {
                 $gia_luu_db = isset($item['price_to_use']) ? (float)$item['price_to_use'] : (isset($item['GiaBan']) ? (float)$item['GiaBan'] : 0.00);
                 $qty_luu_db = isset($item['qty']) ? (int)$item['qty'] : 0;
                 $idSP_luu_db = isset($item['idSP']) ? (int)$item['idSP'] : 0;
                 $tenSP_luu_db = isset($item['TenSP']) ? $item['TenSP'] : 'N/A';

                 if ($gia_luu_db <= 0 || $qty_luu_db <= 0 || $idSP_luu_db <= 0) {
                      $all_ok = false; error_log("Invalid item data for order ID: " . $last_inserted_order_id . " - Item ID: " . $item_id); break;
                 }

                 // Insert chi tiết
                 $sql_ctdh = "INSERT INTO donhangchitiet(idDH, idSP, TenSP, SoLuong, Gia) VALUES (?, ?, ?, ?, ?)";
                 $stmt_ctdh = mysqli_prepare($conn, $sql_ctdh);
                 if($stmt_ctdh){
                      mysqli_stmt_bind_param($stmt_ctdh, "iisid", $last_inserted_order_id, $idSP_luu_db, $tenSP_luu_db, $qty_luu_db, $gia_luu_db);
                      if (!mysqli_stmt_execute($stmt_ctdh)) { $all_ok = false; error_log("Order detail insert failed for order ID " . $last_inserted_order_id . ": " . mysqli_stmt_error($stmt_ctdh)); }
                      mysqli_stmt_close($stmt_ctdh);
                 } else { $all_ok = false; error_log("Prepare failed (detail insert) for order ID " . $last_inserted_order_id . ": " . mysqli_error($conn)); }
                 if (!$all_ok) break;

                 // Cập nhật kho
                 $sql_sanpham = "UPDATE sanpham SET SoLuongTonKho = SoLuongTonKho - ?, SoLanMua = SoLanMua + 1 WHERE idSP = ? AND SoLuongTonKho >= ?";
                 $stmt_sp = mysqli_prepare($conn, $sql_sanpham);
                  if($stmt_sp){
                       mysqli_stmt_bind_param($stmt_sp, "iii", $qty_luu_db, $idSP_luu_db, $qty_luu_db);
                       if (mysqli_stmt_execute($stmt_sp)) {
                            if (mysqli_stmt_affected_rows($stmt_sp) != 1) { $all_ok = false; error_log("Stock update affected rows != 1 for SP ID: " . $idSP_luu_db . ". Order ID: " . $last_inserted_order_id); }
                       } else { $all_ok = false; error_log("Stock update execution failed for SP ID " . $idSP_luu_db . ": " . mysqli_stmt_error($stmt_sp)); }
                       mysqli_stmt_close($stmt_sp);
                  } else { $all_ok = false; error_log("Prepare failed (stock update) for SP ID " . $idSP_luu_db . ": " . mysqli_error($conn)); }
                  if (!$all_ok) { break; } // Thoát foreach nếu lỗi
            } // end foreach
        } // end if ($all_ok && $last_inserted_order_id !== null)


        // 3. Cập nhật lượt sử dụng voucher (Dùng tên cột tiếng Việt)
        if ($all_ok && $voucher_id_to_update !== null) {
             $sql_update_voucher = "UPDATE vouchers SET so_lan_da_dung = so_lan_da_dung + 1 WHERE id = ? AND (gioi_han_su_dung IS NULL OR so_lan_da_dung < gioi_han_su_dung)";
             $stmt_voucher_update = mysqli_prepare($conn, $sql_update_voucher);
             if($stmt_voucher_update){
                  mysqli_stmt_bind_param($stmt_voucher_update, "i", $voucher_id_to_update);
                  if (!mysqli_stmt_execute($stmt_voucher_update)) {
                      error_log("Failed to update voucher usage count (so_lan_da_dung) for ID: " . $voucher_id_to_update . " - Error: " . mysqli_stmt_error($stmt_voucher_update));
                  }
                  mysqli_stmt_close($stmt_voucher_update);
             } else { error_log("Prepare statement failed (voucher usage update): " . mysqli_error($conn)); }
        }

        // H. Commit hoặc Rollback Transaction
        if ($all_ok && $last_inserted_order_id !== null) {
            mysqli_commit($conn);
            if (!$is_buy_now_mode) { unset($_SESSION['cart']); }
            unset($_SESSION['applied_voucher']); // Clear session voucher state
            $_SESSION['orderID'] = $last_inserted_order_id;
            if ($selected_pttt == 'VNPay') {
                generateVNPayUrl($total_amount_final, "Thanh toan don hang #" . $last_inserted_order_id);
                exit;
            } else {
                echo "<script> alert('Đơn hàng của bạn (ID: #".$last_inserted_order_id.") đã được ghi nhận!'); location.href='../site/index.php?index=1'; </script>";
                exit;
            }
        } else {
            mysqli_rollback($conn);
            echo "<script> alert('Đã có lỗi xảy ra trong quá trình xử lý đơn hàng. Vui lòng thử lại!'); window.history.back();</script>";
            exit;
        }
    } // End if ($form_valid)
} // --- Kết thúc xử lý POST ---


// --- Hàm VNPay --- (GIỮ NGUYÊN)
function getRandomNumber($length) { $characters = '0123456789'; $randomString = ''; for ($i = 0; $i < $length; $i++) { $randomString .= $characters[rand(0, strlen($characters) - 1)]; } return $randomString; }
function generateVNPayUrl($amount, $orderInfo) {
    global $last_order_id_for_vnpay; date_default_timezone_set('Asia/Ho_Chi_Minh');
    $vnp_Version = "2.1.0"; $vnp_Command = "pay"; $orderType = "other"; $bankCode = "NCB";
    $vnp_TmnCode = "0S7T01T8"; // !!! THAY TmnCode CỦA BẠN !!!
    $vnp_TxnRef = ($last_order_id_for_vnpay ? $last_order_id_for_vnpay : 'DH') . '_' . time();
    $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
    $vnp_ReturnUrl = "http://localhost/site/xulyvnpay.php"; // !!! KIỂM TRA LẠI URL NÀY !!!
    $secretKey = "BEZLUPOPOTXTDYZHCBGDJBHFJPBLSARL"; // !!! THAY Secret Key CỦA BẠN !!!
    $vnp_Amount = $amount * 100;
    $vnp_Params = [ "vnp_Version" => $vnp_Version, "vnp_Command" => $vnp_Command, "vnp_TmnCode" => $vnp_TmnCode, "vnp_Amount" => $vnp_Amount, "vnp_CurrCode" => "VND", "vnp_TxnRef" => $vnp_TxnRef, "vnp_OrderInfo" => $orderInfo, "vnp_OrderType" => $orderType, "vnp_Locale" => "vn", "vnp_ReturnUrl" => $vnp_ReturnUrl, "vnp_IpAddr" => $vnp_IpAddr, "vnp_CreateDate" => date('YmdHis') ];
    if (!empty($bankCode)) { $vnp_Params['vnp_BankCode'] = $bankCode; }
    ksort($vnp_Params); $hashdata = ""; $query = ""; $i = 0;
    foreach ($vnp_Params as $key => $value) { if ($value === "" || $value === null) continue; if ($i > 0) { $hashdata .= '&' . urlencode($key) . "=" . urlencode($value); } else { $hashdata .= urlencode($key) . "=" . urlencode($value); } $query .= urlencode($key) . "=" . urlencode($value) . '&'; $i = 1; }
    $query = rtrim($query, '&'); $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
    $vnp_SecureHash = hash_hmac("sha512", $hashdata, $secretKey); $vnp_Url .= "?" . $query . "&vnp_SecureHash=" . $vnp_SecureHash;
    echo "<script>window.location.href = '" . $vnp_Url . "';</script>"; exit();
}

// --- Check if voucher was applied on previous attempt (e.g., form validation fail) ---
if (isset($_SESSION['applied_voucher']) && is_array($_SESSION['applied_voucher'])) {
    $validated_voucher_code = $_SESSION['applied_voucher']['code'];
    $discount_amount = $_SESSION['applied_voucher']['discount'];
}

?>
<!DOCTYPE html>
<html>
<head>
    <?php include_once("header.php"); ?>
    <title>Thanh toán</title>
    <?php include_once("header1.php"); ?>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/bootstrap-theme.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
     /* ... CSS giữ nguyên ... */
         body { background-color: #f0f0f0; padding-top: 20px; padding-bottom: 20px; } .page-frame { background-color: #fff0f5; padding: 20px; border-radius: 8px; max-width: 1200px; margin: 0 auto; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); } .dancach { margin-bottom: 15px; } .form-control { border-radius: 4px; } textarea.form-control { resize: vertical; } .order-summary { background-color: #f8f9fa; padding: 20px; border-radius: 5px; border: 1px solid #dee2e6; margin-top: 20px; } .order-summary h4 { margin-top: 0; border-bottom: 1px solid #ccc; padding-bottom: 10px; margin-bottom: 15px;} .summary-row { border-bottom: 1px dashed #eee; padding: 8px 0; display: flex; align-items: center; flex-wrap: wrap;} .summary-row:last-of-type { border-bottom: 1px solid #ccc; } .total-row { border-top: 2px solid #333; margin-top: 10px; padding-top: 10px; font-size: 1.1em; display: flex; align-items: center; flex-wrap: wrap;} .error-message { color: red; font-weight: bold; text-align: center; margin-bottom: 15px;} .shipping-info b { display: inline-block; min-width: 120px; } .shipping-info i { font-style: normal; font-weight: normal; } .control-label { text-align: right; padding-top: 7px; font-weight: bold;} @media (max-width: 991px) { .control-label { text-align: left; margin-bottom: 5px;} .order-summary { margin-top: 30px; } } select.form-control, select.check { height: 34px; }
         .summary-product-name { flex-basis: 55%; padding-right: 5px; word-break: break-word; }
         .summary-quantity { flex-basis: 15%; text-align: center; }
         .summary-price { flex-basis: 30%; text-align: right; }
         .summary-label { flex-basis: 60%; }
         .summary-value { flex-basis: 40%; text-align: right; }
         .total-label { flex-basis: 50%; }
         .total-value { flex-basis: 50%; text-align: right; }
         .original-price { text-decoration: line-through; color: #888; font-size: 0.9em; margin-left: 5px;}
         .discounted-price { color: red; font-weight: bold;}
         #voucher-select-row .summary-label { flex-basis: 100%; margin-bottom: 5px; }
         #voucher_select { width: 100%; }
         #discount-row .summary-label { color: green; }
         #discount-row .summary-value b { color: green; }
         .btn-danger:disabled { cursor: not-allowed; opacity: 0.65; }
    </style>
</head>
<body>
    <div class="page-frame">
        <?php include_once("header2.php"); ?>
        <div class="container">
             <div class="row"> <div class="col-xs-12"> <div class="indexh3 text-center"> <?php if ($flag) echo "<h3>THÔNG TIN ĐẶT HÀNG CỦA BẠN</h3>"; else echo "<h3>Đặt hàng không cần đăng ký</h3>" ?> <div class="sep-wrap center nz-clearfix" style="margin-bottom: 30px;"> <div class="nz-separator solid" style="margin-top:10px; border-bottom: 2px solid #ddd; width:50px; margin-left: auto; margin-right: auto;"></div> </div> </div> </div> </div>
             <div class="row">
                 <form class="form-horizontal" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?><?php echo $is_buy_now_mode ? '?buy_now=1&idSP='.$buy_now_id : ''; ?>" method="post" name="checkoutForm">
                     <div class="col-md-8">
                         <?php if ($flag == true && $r_us): ?>
                             <h4><b>Thông tin giao hàng</b> <small>(Bạn có thể chỉnh sửa nếu cần)</small></h4>
                             <div class="form-group"> <label for="HoTen" class="col-sm-3 control-label">Họ tên người nhận<span style="color:red;">*</span>:</label> <div class="col-sm-9"> <input type="text" class="form-control" id="HoTen" name="HoTen" value="<?php echo isset($_POST['HoTen']) ? htmlspecialchars($_POST['HoTen']) : (isset($r_us['HoTenK']) ? htmlspecialchars($r_us['HoTenK']) : ''); ?>" required> </div> </div>
                             <div class="form-group"> <label for="email" class="col-sm-3 control-label">Email<span style="color:red;">*</span>:</label> <div class="col-sm-9"> <input type="email" class="form-control" id="email" name="email" placeholder="abc@gmail.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : (isset($r_us['Email']) ? htmlspecialchars($r_us['Email']) : ''); ?>" required> </div> </div>
                             <div class="form-group"> <label for="DiaChi" class="col-sm-3 control-label">Địa chỉ nhận hàng<span style="color:red;">*</span>:</label> <div class="col-sm-9"> <textarea class="form-control" rows="3" id="DiaChi" name="DiaChi" placeholder="Vui lòng nhập chính xác địa chỉ nhận hàng!" required><?php echo isset($_POST['DiaChi']) ? htmlspecialchars($_POST['DiaChi']) : (isset($r_us['DiaChi']) ? htmlspecialchars($r_us['DiaChi']) : ''); ?></textarea> </div> </div>
                             <div class="form-group"> <label for="SDT" class="col-sm-3 control-label">Số điện thoại<span style="color:red;">*</span>:</label> <div class="col-sm-9"> <input type="tel" class="form-control" id="SDT" name="SDT" placeholder="Vui lòng nhập số điện thoại" value="<?php echo isset($_POST['SDT']) ? htmlspecialchars($_POST['SDT']) : (isset($r_us['DienThoai']) ? htmlspecialchars($r_us['DienThoai']) : ''); ?>" required pattern="^(0[1-9][0-9]{8,9})$" title="Số điện thoại Việt Nam hợp lệ (10-11 số, bắt đầu bằng 0)"> </div> </div>
                             <hr>
                         <?php else: ?>
                              <h4><b>Thông tin giao hàng</b></h4>
                              <div class="form-group"> <label for="HoTen" class="col-sm-3 control-label">Họ tên người nhận<span style="color:red;">*</span>:</label> <div class="col-sm-9"> <input type="text" class="form-control" id="HoTen" name="HoTen" value="<?php echo isset($_POST['HoTen']) ? htmlspecialchars($_POST['HoTen']) : ''; ?>" required> </div> </div>
                              <div class="form-group"> <label for="email" class="col-sm-3 control-label">Email<span style="color:red;">*</span>:</label> <div class="col-sm-9"> <input type="email" class="form-control" id="email" name="email" placeholder="abc@gmail.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required> </div> </div>
                              <div class="form-group"> <label for="DiaChi" class="col-sm-3 control-label">Địa chỉ nhận hàng<span style="color:red;">*</span>:</label> <div class="col-sm-9"> <textarea class="form-control" rows="3" id="DiaChi" name="DiaChi" placeholder="Vui lòng nhập chính xác địa chỉ nhận hàng!" required><?php echo isset($_POST['DiaChi']) ? htmlspecialchars($_POST['DiaChi']) : ''; ?></textarea> </div> </div>
                              <div class="form-group"> <label for="SDT" class="col-sm-3 control-label">Số điện thoại<span style="color:red;">*</span>:</label> <div class="col-sm-9"> <input type="tel" class="form-control" id="SDT" name="SDT" placeholder="Vui lòng nhập số điện thoại" value="<?php echo isset($_POST['SDT']) ? htmlspecialchars($_POST['SDT']) : ''; ?>" required pattern="^(0[1-9][0-9]{8,9})$" title="Số điện thoại Việt Nam hợp lệ (10-11 số, bắt đầu bằng 0)"> </div> </div>
                              <hr>
                         <?php endif; ?>

                         <h4><b>Vận chuyển và Thanh toán</b></h4>
                         <div class="form-group">
                             <label for="PTGH" class="col-sm-3 control-label">Nhận hàng<span style="color:red;">*</span>:</label>
                             <div class="col-sm-9">
                                 <select name="PTGH" class="form-control" id="PTGH" required>
                                     <option value="">-- Chọn phương thức --</option>
                                     <?php
                                     if (isset($rs_ptgh_display) && $rs_ptgh_display instanceof mysqli_result && mysqli_num_rows($rs_ptgh_display) > 0) {
                                         mysqli_data_seek($rs_ptgh_display, 0);
                                         while ($r_gh_display = $rs_ptgh_display->fetch_assoc()) {
                                             $selected_gh = '';
                                             if (isset($_POST['PTGH']) && $_POST['PTGH'] == $r_gh_display['idGH']) {
                                                 $selected_gh = 'selected';
                                             } elseif (!isset($_POST['PTGH']) && isset($r_us['idPTGH']) && $r_us['idPTGH'] == $r_gh_display['idGH']) {
                                                 // $selected_gh = 'selected';
                                             }
                                             echo "<option value='" . $r_gh_display['idGH'] . "' data-fee='" . (float)$r_gh_display['Phi'] . "' ".$selected_gh.">" . htmlspecialchars($r_gh_display['TenGH']) . ' - ' . number_format($r_gh_display['Phi']) . ' VNĐ' . "</option>";
                                         }
                                     } else { echo "<option value='' disabled>Không có phương thức vận chuyển</option>"; }
                                     ?>
                                 </select>
                             </div>
                         </div>
                          <div class="form-group">
                              <label for="PTThanhToan" class="col-sm-3 control-label">Thanh toán<span style="color:red;">*</span>:</label>
                              <div class="col-sm-9">
                                     <select name="PTThanhToan" class="form-control" id="PTThanhToan" required>
                                         <?php
                                         $selected_tt = 'COD';
                                         if (isset($_POST['PTThanhToan'])) {
                                             $selected_tt = $_POST['PTThanhToan'];
                                         } elseif (isset($r_us['PTThanhToan']) && !empty($r_us['PTThanhToan'])) {
                                             // $selected_tt = $r_us['PTThanhToan'];
                                         }
                                         ?>
                                         <option value="COD" <?php echo ($selected_tt == 'COD') ? 'selected' : ''; ?>>Thanh toán khi nhận hàng (COD)</option>
                                         <option value="VNPay" <?php echo ($selected_tt == 'VNPay') ? 'selected' : ''; ?>>Thanh toán qua VNPay</option>
                                     </select>
                              </div>
                          </div>
                         <hr>
                         <h4><b>Ghi chú đơn hàng</b></h4>
                          <div class="form-group">
                              <label for="GhiChu" class="col-sm-3 control-label">Ghi chú:</label>
                              <div class="col-sm-9">
                                   <textarea class="form-control" rows="3" id="GhiChu" name="GhiChu" placeholder="Ví dụ: Giao hàng vào giờ hành chính, gọi trước khi giao,..."><?php echo isset($_POST['GhiChu']) ? htmlspecialchars($_POST['GhiChu']) : ''; ?></textarea>
                              </div>
                          </div>
                          <div class="form-group" style="margin-top: 20px;"> <div class="col-sm-9 col-sm-offset-3"> <p class="text-muted"><small>Chúng tôi sẽ liên hệ quý khách theo số điện thoại hoặc email để xác nhận.</small></p> </div> </div>
                     </div>
                     <div class="col-md-4">
                          <div class="order-summary">
                              <h4><b>Thông tin đơn hàng</b></h4>
                              <?php if (!empty($checkout_items)) : ?>
                                  <?php foreach ($checkout_items as $item_id => $item) : ?>
                                      <?php
                                      $display_price = isset($item['price_to_use']) ? $item['price_to_use'] : (isset($item['GiaBan']) ? $item['GiaBan'] : 0);
                                      $original_price = isset($item['GiaBan']) ? $item['GiaBan'] : 0;
                                      $has_discount_item = ($display_price < $original_price && $display_price > 0);
                                      $display_qty = isset($item['qty']) ? (int)$item['qty'] : 0;
                                      $line_total = $display_price * $display_qty;
                                      ?>
                                      <div class="summary-row">
                                          <div class="summary-product-name">
                                              <b><?php echo htmlspecialchars($item['TenSP']); ?></b><br>
                                              <?php if ($has_discount_item): ?>
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

                                  <div class="summary-row" id="voucher-select-row">
                                        <div class="summary-label" style="flex-basis: 100%; margin-bottom: 5px;">Mã giảm giá:</div>
                                        <div style="width: 100%;">
                                            <select name="selected_voucher_code" id="voucher_select" class="form-control">
                                                <option value="" data-discount-type="" data-discount-value="0">-- Chọn mã giảm giá --</option>
                                                <?php if (!empty($available_vouchers)): ?>
                                                    <?php foreach ($available_vouchers as $voucher): ?>
                                                        <?php
                                                            // ***** SỬA LỖI Ở ĐÂY: Đã bỏ xử lý $voucher['mo_ta'] *****
                                                            $display_text = htmlspecialchars($voucher['ma_giam_gia']);
                                                            $discount_info = "";
                                                            if ($voucher['loai'] == 'fixed') {
                                                                $discount_info = "Giảm " . number_format($voucher['gia_tri']) . "đ";
                                                            } elseif ($voucher['loai'] == 'percent') {
                                                                $discount_info = "Giảm " . $voucher['gia_tri'] . "%";
                                                            }
                                                            // Chỉ thêm thông tin giảm giá nếu có
                                                            if (!empty($discount_info)) {
                                                                $display_text .= " (" . $discount_info . ")";
                                                            }
                                                            $selected_voucher_attr = ($validated_voucher_code === $voucher['ma_giam_gia']) ? 'selected' : '';
                                                        ?>
                                                        <option value="<?php echo htmlspecialchars($voucher['ma_giam_gia']); ?>"
                                                                data-discount-type="<?php echo $voucher['loai']; ?>"
                                                                data-discount-value="<?php echo (float)$voucher['gia_tri']; ?>"
                                                                <?php echo $selected_voucher_attr; ?>>
                                                            <?php echo $display_text; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <option value="" disabled>Không có mã giảm giá phù hợp</option>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                  </div>
                                  <div class="summary-row" id="discount-row" style="display: none;"> <div class="summary-label">Giảm giá (<span id="applied-voucher-code"></span>):</div>
                                      <div class="summary-value"> <b id="discount-amount-display">0 VNĐ</b> </div>
                                  </div>
                                  <div class="summary-row"> <div class="summary-label">Phí vận chuyển:</div> <div class="summary-value"> <b id="shipping-fee-display">Chọn PTVC</b> </div> </div>
                                  <div class="total-row"> <div class="total-label"><h4><b style="color: red;">TỔNG CỘNG:</b></h4></div> <div class="total-value"> <h4 id="total-amount-display"><b style="color: red;">---</b></h4> </div> </div>
                                  <p class="text-center text-muted" style="margin-top: 10px;"><small>(Tổng cộng sẽ được cập nhật sau khi bạn chọn phương thức nhận hàng và mã giảm giá)</small></p>
                              <?php else : ?>
                                   <p class='text-center text-danger'><b>Giỏ hàng của bạn đang trống!</b></p>
                                   <p class='text-center'><a href="../site/index.php?index=1" class="btn btn-primary">Tiếp tục mua sắm</a></p>
                              <?php endif; ?>
                          </div>
                           <input type="hidden" name="applied_voucher_code_hidden_for_js" id="hidden_applied_voucher_code" value="<?php echo htmlspecialchars($validated_voucher_code ?? ''); ?>">

                           <div class="row" style="margin-top: 20px;"> <div class="col-xs-12 text-center"> <button type="submit" class="btn btn-danger btn-lg" name="OK" <?php echo (empty($checkout_items)) ? 'disabled' : ''; ?>> <span class="glyphicon glyphicon-check"></span> HOÀN TẤT ĐẶT HÀNG </button> </div> </div>
                     </div>
                 </form>
             </div>
        </div>
        <?php include_once("footer.php"); ?>
    </div>
    <script src="../js/jquery-3.1.1.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script>
     $(document).ready(function() {
         var currentSubtotal = <?php echo $tongtien; ?>;
         var currentShippingFee = 0;
         var currentDiscountAmount = 0;
         var appliedVoucherCode = ''; // JS variable to hold the currently selected code

         function formatCurrency(number) {
             if (isNaN(number)) return '---';
             number = Math.round(number);
             return number.toLocaleString('vi-VN') + ' VNĐ';
         }

         function updateTotals() {
             // 1. Calculate Shipping Fee
             var shippingFee = 0;
             var selectedShippingOption = $('#PTGH').find('option:selected');
             var feeDisplay = 'Chọn PTVC';
             if ($('#PTGH').val() !== "") {
                 shippingFee = parseFloat(selectedShippingOption.data('fee')); // Use parseFloat
                 if (isNaN(shippingFee) || shippingFee < 0) {
                     shippingFee = 0;
                 }
                 feeDisplay = (shippingFee === 0) ? 'Miễn phí' : formatCurrency(shippingFee);
             } else {
                 shippingFee = 0;
             }
             currentShippingFee = shippingFee;
             $('#shipping-fee-display').html('<b>' + feeDisplay + '</b>');

             // 2. Use currentDiscountAmount and appliedVoucherCode (set by voucher dropdown change)
             if (currentDiscountAmount > 0 && appliedVoucherCode) {
                 $('#applied-voucher-code').text(appliedVoucherCode);
                 $('#discount-amount-display').html('<b>-' + formatCurrency(currentDiscountAmount) + '</b>');
                 $('#discount-row').show();
                 $('#hidden_applied_voucher_code').val(appliedVoucherCode); // Update hidden input if needed
             } else {
                 $('#discount-row').hide();
                 $('#applied-voucher-code').text('');
                 $('#discount-amount-display').html('<b>0 VNĐ</b>');
                 $('#hidden_applied_voucher_code').val(''); // Clear hidden input
             }

             // 3. Calculate Final Total
             var totalAmount = currentSubtotal + currentShippingFee - currentDiscountAmount;
             if (totalAmount < 0) totalAmount = 0;
             $('#total-amount-display').html('<b style="color: red;">' + formatCurrency(totalAmount) + '</b>');
         }

         // --- Event handler for voucher dropdown change ---
         $('#voucher_select').on('change', function() {
             var selectedOption = $(this).find('option:selected');
             var code = selectedOption.val();
             var type = selectedOption.data('discount-type');
             var value = parseFloat(selectedOption.data('discount-value'));
             var calculatedDiscount = 0;

             if (code && type && !isNaN(value)) {
                 if (type === 'fixed') {
                     calculatedDiscount = value;
                 } else if (type === 'percent') {
                     calculatedDiscount = currentSubtotal * (value / 100);
                 }

                 // Apply constraints
                 calculatedDiscount = Math.min(calculatedDiscount, currentSubtotal);
                 calculatedDiscount = Math.max(calculatedDiscount, 0);
                 calculatedDiscount = Math.round(calculatedDiscount);

                 appliedVoucherCode = code;
                 currentDiscountAmount = calculatedDiscount;
             } else {
                 appliedVoucherCode = '';
                 currentDiscountAmount = 0;
             }
             updateTotals(); // Update display after selection changes
         });
         // --- End Event handler ---

         // Restore state from PHP if a voucher was pre-selected
         <?php
         if ($validated_voucher_code !== null && $discount_amount >= 0) {
             $js_voucher_code = json_encode($validated_voucher_code);
             $js_discount_amount = json_encode($discount_amount);

             echo "appliedVoucherCode = $js_voucher_code;\n";
             echo "currentDiscountAmount = parseFloat($js_discount_amount);\n";
             echo "if (isNaN(currentDiscountAmount)) currentDiscountAmount = 0;\n";
             echo "$('#voucher_select').val(appliedVoucherCode);\n";
             echo "// console.log('Restoring state: Code=', appliedVoucherCode, 'Discount=', currentDiscountAmount);\n";
         }
         ?>

         // Update totals when shipping method changes
         $('#PTGH').on('change', function() {
             updateTotals();
         });

         // Initial calculation and display update on page load
         updateTotals();
         // If a shipping method was already selected, ensure totals reflect it
         if ($('#PTGH').val()) {
             updateTotals();
         }

     });
    </script>
</body>
</html>