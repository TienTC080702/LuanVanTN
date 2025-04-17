<?php
// 1. BẮT BUỘC KHỞI ĐỘNG SESSION ĐẦU TIÊN
session_start();

include '../connection/connect_database.php';

// --- Kiểm tra kết nối DB ---
if (!isset($conn) || !$conn) {
    error_log("VNPay Return: Database connection failed.");
    // Có thể hiển thị trang lỗi chung chung thay vì alert
    die("Lỗi kết nối cơ sở dữ liệu.");
}

// Lấy các tham số trả về từ VNPay
$secretKey = "BEZLUPOPOTXTDYZHCBGDJBHFJPBLSARL"; // !!! THAY SECRET KEY THỰC TẾ !!!
$vnp_SecureHash = isset($_GET['vnp_SecureHash']) ? $_GET['vnp_SecureHash'] : '';
$vnp_Params = $_GET; // Lấy tất cả tham số GET

// Xóa hash ra khỏi mảng để kiểm tra chữ ký
unset($vnp_Params['vnp_SecureHash']);

// Sắp xếp dữ liệu theo key
ksort($vnp_Params);

// Tạo chuỗi dữ liệu để hash
$hashData = "";
$i = 0;
foreach ($vnp_Params as $key => $value) {
    // Bỏ qua các tham số trống theo tài liệu VNPay
    if ($value === "" || $value === null) continue;
    if ($i == 1) {
        $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashdata .= urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
}

// Tạo chữ ký xác thực
$vnp_SecureHashVerify = hash_hmac('sha512', $hashdata, $secretKey);

// --- Xác thực chữ ký ---
if ($vnp_SecureHashVerify === $vnp_SecureHash) {

    $vnp_ResponseCode = isset($vnp_Params['vnp_ResponseCode']) ? $vnp_Params['vnp_ResponseCode'] : '99'; // Lấy mã phản hồi
    $vnp_TxnRef = isset($vnp_Params['vnp_TxnRef']) ? $vnp_Params['vnp_TxnRef'] : ''; // Lấy mã giao dịch của bạn (có thể chứa ID đơn hàng)

    // --- Xử lý kết quả thanh toán ---
    if ($vnp_ResponseCode == '00') { // Thanh toán thành công
        
        // Lấy orderID từ session (đảm bảo nó được set đúng ở trang ThanhToan.php)
        $orderID = isset($_SESSION['orderID']) ? (int)$_SESSION['orderID'] : 0;

        // Kiểm tra lại xem orderID có hợp lệ không và có khớp với vnp_TxnRef không (nếu bạn có lưu TxnRef dạng orderID_time)
        $order_id_from_txnref = 0;
        if (strpos($vnp_TxnRef, '_') !== false) {
            $parts = explode('_', $vnp_TxnRef);
            $order_id_from_txnref = (int)$parts[0];
        }
        
        // Chỉ cập nhật nếu orderID từ session hợp lệ và khớp (hoặc gần khớp) với mã giao dịch trả về
        if ($orderID > 0 && $orderID === $order_id_from_txnref) {

            // --- Cập nhật trạng thái đơn hàng (DÙNG PREPARED STATEMENT) ---
            $sql_update = "UPDATE donhang SET DaXuLy = 1 WHERE idDH = ?"; // DaXuLy = 1: Đã thanh toán VNPay thành công
            $stmt_update = mysqli_prepare($conn, $sql_update);

            if($stmt_update) {
                mysqli_stmt_bind_param($stmt_update, 'i', $orderID);
                $update_success = mysqli_stmt_execute($stmt_update);
                mysqli_stmt_close($stmt_update);

                if ($update_success) {
                    // 2. DI CHUYỂN unset($_SESSION['cart']) VÀO ĐÂY
                    // Chỉ xóa giỏ hàng nếu không phải chế độ mua ngay (nếu cần phân biệt)
                    // Giả sử VNPay luôn là thanh toán giỏ hàng thì xóa luôn, hoặc kiểm tra thêm cờ $is_buy_now_mode nếu có lưu trong session
                    unset($_SESSION['cart']); 
                    unset($_SESSION['orderID']); // Xóa ID đơn hàng khỏi session sau khi xử lý

                    // Thông báo và chuyển hướng thành công
                    echo "<script>alert('Thanh toán thành công cho đơn hàng #".$orderID."!'); window.location.href='index.php';</script>";
                    exit; // Dừng script sau khi chuyển hướng
                } else {
                    // Lỗi cập nhật DB
                    error_log("VNPay Return: Failed to update order status for order ID: $orderID. Error: " . mysqli_error($conn));
                    echo "<script>alert('Thanh toán thành công nhưng có lỗi khi cập nhật đơn hàng! Vui lòng liên hệ cửa hàng.'); window.location.href='index.php';</script>";
                    exit;
                }
            } else {
                 error_log("VNPay Return: Prepare statement failed for order update: " . mysqli_error($conn));
                 echo "<script>alert('Lỗi hệ thống khi cập nhật đơn hàng!'); window.location.href='index.php';</script>";
                 exit;
            }

        } else {
             error_log("VNPay Return: Invalid order ID found in session or mismatch with TxnRef. Session Order ID: " . $orderID . ", TxnRef: " . $vnp_TxnRef);
             echo "<script>alert('Lỗi: Không tìm thấy hoặc mã đơn hàng không khớp!'); window.location.href='index.php';</script>";
             exit;
        }

    } else { // Thanh toán không thành công (bị hủy, lỗi...)
        // Không cần cập nhật DB, chỉ cần thông báo
        unset($_SESSION['orderID']); // Có thể xóa orderID nếu muốn
        echo "<script>alert('Thanh toán không thành công! Mã lỗi VNPay: " . htmlspecialchars($vnp_ResponseCode) . "'); window.location.href='index.php';</script>";
        exit;
    }
} else { // Chữ ký không hợp lệ
    error_log("VNPay Return: Invalid signature. Expected: " . $vnp_SecureHashVerify . ", Received: " . $vnp_SecureHash . ", HashData: " . $hashdata);
    echo "<script>alert('Lỗi xác thực chữ ký từ VNPay! Giao dịch không hợp lệ.'); window.location.href='index.php';</script>";
    exit;
}

// Đóng kết nối DB nếu cần (thường không cần nếu script kết thúc)
// mysqli_close($conn);
?>