<?php
session_start(); // Bắt buộc có session để xử lý giỏ hàng

// Kết nối CSDL
include_once('../connection/connect_database.php');

// --- Kiểm tra kết nối ---
if (!isset($conn) || !$conn) {
    error_log("Database connection failed on order_now.php.");
    $_SESSION['error_message'] = "Lỗi kết nối. Không thể xử lý đặt hàng.";
    header('Location: ../index.php?index=1'); // Hoặc trang chủ phù hợp
    exit;
}

// --- Kiểm tra và lấy idSP ---
if (!isset($_GET['idSP']) || !filter_var($_GET['idSP'], FILTER_VALIDATE_INT) || $_GET['idSP'] <= 0) {
    $_SESSION['error_message'] = "Yêu cầu đặt hàng không hợp lệ (ID sản phẩm).";
    header('Location: ../index.php?index=1'); // Chuyển về trang chủ
    exit;
}
$idSP = (int)$_GET['idSP'];
$quantity = 1; // Số lượng mặc định khi nhấn "Đặt hàng ngay"

// --- Kiểm tra sản phẩm tồn tại và còn hàng ---
// !!! CẢNH BÁO: Nên dùng Prepared Statements !!!
$sql_check = "SELECT TenSP, SoLuongTonKho, GiaBan FROM sanpham WHERE idSP = " . $idSP;
$rs_check = mysqli_query($conn, $sql_check);

$product_name = "Sản phẩm"; // Tên mặc định
$stock_quantity = 0;
$product_price = 0;

if ($rs_check && mysqli_num_rows($rs_check) > 0) {
    $product = mysqli_fetch_assoc($rs_check);
    $stock_quantity = (int)$product['SoLuongTonKho'];
    $product_name = $product['TenSP']; // Lấy tên SP
    $product_price = $product['GiaBan']; // Lấy giá SP

    if ($stock_quantity < $quantity) {
        // Hết hàng -> Quay về trang chi tiết với thông báo
        $_SESSION['error_message'] = "Sản phẩm '" . htmlspecialchars($product_name) . "' đã hết hàng.";
        // *** Giả sử file chi tiết nằm trong /pages/ và tên là ChiTietSanPham.php ***
        header('Location: ../pages/ChiTietSanPham.php?idSP=' . $idSP);
        exit;
    }
     mysqli_free_result($rs_check);
} else {
    // Không tìm thấy sản phẩm -> Về trang chủ
     $_SESSION['error_message'] = "Không tìm thấy sản phẩm (ID: $idSP).";
    header('Location: ../index.php?index=1');
    exit;
}


// --- Thêm vào giỏ hàng ---
// **QUAN TRỌNG**: Logic này cần khớp 100% với cách bạn lưu giỏ hàng.
// Ví dụ dưới đây giả định: $_SESSION['cart'][idSP] = ['idSP'=>..., 'TenSP'=>..., 'GiaBan'=>..., 'qty'=>...]
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = array(); // Khởi tạo nếu chưa có
}

if (isset($_SESSION['cart'][$idSP])) {
    // Đã có, tăng số lượng nếu còn đủ hàng
    if (($_SESSION['cart'][$idSP]['qty'] + $quantity) <= $stock_quantity) {
         $_SESSION['cart'][$idSP]['qty'] += $quantity;
    } else {
         // Nếu không đủ, đặt thành tối đa
         $_SESSION['cart'][$idSP]['qty'] = $stock_quantity;
         $_SESSION['warning_message'] = "Số lượng sản phẩm '" . htmlspecialchars($product_name) . "' trong giỏ đã cập nhật thành tối đa còn lại.";
    }
} else {
    // Chưa có, thêm mới (với số lượng kiểm tra với tồn kho)
     $add_qty = ($quantity <= $stock_quantity) ? $quantity : $stock_quantity;
     if ($add_qty > 0) { // Chỉ thêm nếu số lượng > 0
         $_SESSION['cart'][$idSP] = [
            'idSP' => $idSP,
            'TenSP' => $product_name,
            'GiaBan' => $product_price,
            'qty' => $add_qty
            // Thêm 'urlHinh' nếu bạn đã lấy ở query trên và cần nó trong giỏ hàng
            // Ví dụ: 'urlHinh' => $product['urlHinh'] // Nếu bảng sanpham có cột urlHinh
         ];
         if ($add_qty < $quantity) {
              $_SESSION['warning_message'] = "Số lượng sản phẩm '" . htmlspecialchars($product_name) . "' trong giỏ đã cập nhật thành tối đa còn lại.";
         }
     } else {
         // Trường hợp tồn kho = 0 nhưng somehow qua được check ở trên
         $_SESSION['error_message'] = "Sản phẩm '" . htmlspecialchars($product_name) . "' hiện không thể thêm vào giỏ.";
         // Chuyển hướng về trang chi tiết sản phẩm
         header('Location: ../pages/ChiTietSanPham.php?idSP=' . $idSP);
         exit;
     }
}

// --- Chuyển hướng đến trang Thanh toán ---
header('Location: ThanhToan.php'); // Chuyển đến trang thanh toán trong cùng thư mục /site/
exit; // Luôn có exit sau header redirect

?>