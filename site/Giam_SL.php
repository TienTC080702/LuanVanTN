<?php
session_start();
// Include DB connection nếu cần cập nhật CSDL
// include_once('../connection/connect_database.php'); // Điều chỉnh đường dẫn

$idSP_to_decrease = isset($_GET['idSP']) ? (int)$_GET['idSP'] : 0;

// Chỉ xử lý nếu ID hợp lệ và sản phẩm có trong giỏ hàng session
if ($idSP_to_decrease > 0 && isset($_SESSION['cart'][$idSP_to_decrease])) {

    // Lấy số lượng hiện tại
    $current_qty = $_SESSION['cart'][$idSP_to_decrease]['qty'];

    // *** THÊM BƯỚC KIỂM TRA: Chỉ giảm nếu số lượng > 1 ***
    if ($current_qty > 1) {
        $new_qty = $current_qty - 1;

        // --- Cập nhật CSDL nếu user đăng nhập (thêm logic này nếu cần) ---
        /*
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            // ... code cập nhật quantity trong bảng user_carts về $new_qty ...
            // Kiểm tra kết quả cập nhật CSDL trước khi cập nhật session
        }
        */
        // -------------------------------------------------------------

        // Cập nhật số lượng trong session
        $_SESSION['cart'][$idSP_to_decrease]['qty'] = $new_qty;

        // *** XÓA THÔNG BÁO CŨ KHÔNG LIÊN QUAN KHI GIẢM THÀNH CÔNG ***
        unset($_SESSION['cart_message']);
        // unset($_SESSION['cart_message_type']); // Nếu bạn dùng type

    } else {
        // Nếu số lượng hiện tại là 1 (hoặc nhỏ hơn, dù không nên xảy ra) -> KHÔNG giảm nữa
        // Chỉ cần xóa thông báo cũ đi là đủ, không cần thông báo gì thêm
        unset($_SESSION['cart_message']);
        // unset($_SESSION['cart_message_type']);
    }

} else {
    // Nếu ID không hợp lệ hoặc sản phẩm không có trong giỏ
    // Xóa thông báo cũ hoặc đặt thông báo cảnh báo mới
    unset($_SESSION['cart_message']); // Xóa đi cho chắc
    // Hoặc:
    // $_SESSION['cart_message'] = "Sản phẩm không hợp lệ hoặc không có trong giỏ.";
    // $_SESSION['cart_message_type'] = 'warning';
}

// *** BỎ DÒNG GÁN $id = 1; ***

// *** SỬA LẠI: Chuyển hướng không cần tham số idSP ***
header("Location: GioHang.php");
exit(); // Luôn dùng exit() sau header redirect

?>