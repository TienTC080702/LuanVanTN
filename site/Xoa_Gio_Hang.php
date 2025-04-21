<?php
session_start();

// Lấy ID sản phẩm cần xóa một cách an toàn
$id_to_delete = isset($_GET['idSP']) ? (int)$_GET['idSP'] : 0;
$deleted_product_name = 'Sản phẩm không xác định'; // Giá trị mặc định

// Chỉ xử lý nếu ID hợp lệ và sản phẩm tồn tại trong giỏ hàng
if ($id_to_delete > 0 && isset($_SESSION['cart'][$id_to_delete])) {

    // Lấy tên sản phẩm TRƯỚC KHI xóa để đưa vào thông báo
    // Sử dụng ?? để tránh lỗi nếu 'TenSP' không tồn tại trong mảng
    $deleted_product_name = $_SESSION['cart'][$id_to_delete]['TenSP'] ?? 'Sản phẩm';

    // Thực hiện xóa sản phẩm khỏi session cart
    unset($_SESSION['cart'][$id_to_delete]);

    // *** THÊM DÒNG NÀY: Đặt thông báo xóa thành công ***
    $_SESSION['cart_message'] = "Đã xóa sản phẩm '" . htmlspecialchars($deleted_product_name) . "' khỏi giỏ hàng.";
    // Bạn có thể thêm loại thông báo nếu muốn phân biệt màu sắc
    // $_SESSION['cart_message_type'] = 'success';

    // --- Nếu bạn có hệ thống đăng nhập, thêm code xóa khỏi CSDL ở đây ---
    /*
    if (isset($_SESSION['user_id'])) {
        // include_once('../connection/connect_database.php');
        // $user_id = $_SESSION['user_id'];
        // $sql_delete_db = "DELETE FROM user_carts WHERE user_id = ? AND product_id = ?";
        // $stmt_delete = mysqli_prepare($conn, $sql_delete_db);
        // mysqli_stmt_bind_param($stmt_delete, "ii", $user_id, $id_to_delete);
        // mysqli_stmt_execute($stmt_delete);
        // mysqli_stmt_close($stmt_delete);
        // mysqli_close($conn); // Cân nhắc việc đóng kết nối ở đây hay không
    }
    */
    // --------------------------------------------------------------------

} else {
    // Đặt thông báo lỗi nếu ID không hợp lệ hoặc sản phẩm không có trong giỏ
    $_SESSION['cart_message'] = "Không thể xóa: Sản phẩm không hợp lệ hoặc không tìm thấy trong giỏ.";
    // $_SESSION['cart_message_type'] = 'warning';
}

// *** SỬA LẠI DÒNG NÀY: Chuyển hướng về trang giỏ hàng mà không cần tham số idSP ***
header("Location: GioHang.php");
exit(); // Luôn dùng exit() sau header redirect

?>