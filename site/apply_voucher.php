<?php
session_start();
include_once('../connection/connect_database.php'); // Đảm bảo đường dẫn đúng

header('Content-Type: application/json'); // Trả về JSON

$response = ['success' => false, 'message' => 'Có lỗi xảy ra.'];

if (!isset($conn) || !$conn) {
    error_log("Database connection failed in apply_voucher.php."); // Ghi log lỗi
    $response['message'] = 'Lỗi kết nối cơ sở dữ liệu.';
    echo json_encode($response);
    exit;
}
mysqli_set_charset($conn, 'utf8');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['voucher_code']) && isset($_POST['subtotal'])) {
    // Lấy mã voucher từ POST
    $voucher_code_input = trim($_POST['voucher_code']);
    $subtotal = filter_var($_POST['subtotal'], FILTER_VALIDATE_FLOAT);

    if (empty($voucher_code_input)) {
        $response['message'] = 'Mã voucher không được để trống.';
        unset($_SESSION['applied_voucher']); // Xóa session
        echo json_encode($response);
        exit;
    }

    if ($subtotal === false || $subtotal < 0) {
        $response['message'] = 'Tổng tiền hàng không hợp lệ.';
        unset($_SESSION['applied_voucher']); // Xóa session
        echo json_encode($response);
        exit;
    }

    // Sử dụng tên cột tiếng Việt trong SQL
    $sql = "SELECT id, ma_giam_gia, loai, gia_tri, gia_tri_don_toi_thieu, ngay_bat_dau, ngay_ket_thuc, gioi_han_su_dung, so_lan_da_dung, trang_thai
            FROM vouchers
            WHERE ma_giam_gia = ? AND trang_thai = TRUE"; // Hoặc trang_thai = 1

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        error_log("Prepare statement failed: " . mysqli_error($conn));
        $response['message'] = 'Lỗi truy vấn CSDL (prepare).';
        echo json_encode($response);
        exit;
    }

    mysqli_stmt_bind_param($stmt, "s", $voucher_code_input);

    if(!mysqli_stmt_execute($stmt)) {
        error_log("Execute statement failed: " . mysqli_stmt_error($stmt));
        $response['message'] = 'Lỗi truy vấn CSDL (execute).';
        mysqli_stmt_close($stmt);
        echo json_encode($response);
        exit;
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        error_log("Get result failed: " . mysqli_stmt_error($stmt));
        $response['message'] = 'Lỗi truy vấn CSDL (get result).';
        mysqli_stmt_close($stmt);
        echo json_encode($response);
        exit;
    }


    if (mysqli_num_rows($result) > 0) {
        $voucher = mysqli_fetch_assoc($result);
        $now = date('Y-m-d H:i:s');
        $error_msg = ''; // Biến chứa thông báo lỗi cụ thể

        // Kiểm tra điều kiện bằng tên cột tiếng Việt
        // 1. Ngày hiệu lực
        if (($voucher['ngay_bat_dau'] && $now < $voucher['ngay_bat_dau'])) {
            $error_msg = 'Mã voucher chưa đến ngày sử dụng.';
        } elseif (($voucher['ngay_ket_thuc'] && $now > $voucher['ngay_ket_thuc'])) {
             $error_msg = 'Mã voucher đã hết hạn.';
        }
        // 2. Giới hạn sử dụng
        elseif ($voucher['gioi_han_su_dung'] !== null && $voucher['so_lan_da_dung'] >= $voucher['gioi_han_su_dung']) {
            $error_msg = 'Mã voucher đã hết lượt sử dụng.';
        }
        // 3. Giá trị đơn hàng tối thiểu
        elseif ($subtotal < (float)$voucher['gia_tri_don_toi_thieu']) {
            $min_order_formatted = number_format($voucher['gia_tri_don_toi_thieu'], 0) . ' VNĐ';
            $error_msg = 'Đơn hàng chưa đủ giá trị tối thiểu (' . $min_order_formatted . ') để áp dụng voucher này.';
        }

        // Nếu không có lỗi -> Voucher hợp lệ
        if (empty($error_msg)) {
            $discount_amount = 0.00;
            if ($voucher['loai'] === 'fixed') {
                $discount_amount = (float)$voucher['gia_tri'];
            } elseif ($voucher['loai'] === 'percent') {
                $discount_amount = ($subtotal * (float)$voucher['gia_tri']) / 100;
            }

            if ($discount_amount > $subtotal) {
                $discount_amount = $subtotal;
            }
            if ($discount_amount < 0) { // Đảm bảo không âm
                 $discount_amount = 0.00;
            }

            // *** Làm tròn tiền giảm giá (ví dụ: làm tròn đến hàng đơn vị) ***
            $discount_amount = round($discount_amount);

            // *** Lưu vào session với key 'code' nhất quán ***
            $_SESSION['applied_voucher'] = [
                'id' => $voucher['id'],
                'code' => $voucher['ma_giam_gia'], // Sử dụng key 'code'
                'discount_amount' => $discount_amount,
                'min_order_value' => (float)$voucher['gia_tri_don_toi_thieu']
            ];

            // Trả về response thành công
            $response['success'] = true;
            $response['message'] = 'Áp dụng voucher thành công!';
            $response['code'] = $voucher['ma_giam_gia']; // Vẫn trả về 'code' cho JS
            $response['discount_amount'] = $discount_amount;

        } else {
             // Voucher không hợp lệ
             $response['message'] = $error_msg;
             unset($_SESSION['applied_voucher']); // Xóa session
        }

    } else {
        // Không tìm thấy voucher hoặc voucher không active
        $response['message'] = 'Mã voucher không hợp lệ hoặc không tồn tại.';
        unset($_SESSION['applied_voucher']); // Xóa session
    }

    mysqli_free_result($result); // Giải phóng kết quả
    mysqli_stmt_close($stmt); // Đóng statement

} else {
    $response['message'] = 'Yêu cầu không hợp lệ hoặc thiếu dữ liệu.';
     unset($_SESSION['applied_voucher']); // Xóa session nếu request không hợp lệ
}

mysqli_close($conn); // Đóng kết nối DB
echo json_encode($response);
exit;

?>