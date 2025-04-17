<?php
// File: site/xuly_nhanhang.php (Ví dụ đường dẫn)
session_start(); // Bắt buộc có session_start() ở đầu

// Include file kết nối CSDL
include_once('../connection/connect_database.php');

// --- 1. Kiểm tra đăng nhập ---
if (!isset($_SESSION['IDUser'])) {
    // Lưu thông báo lỗi vào session và chuyển hướng nếu chưa đăng nhập
    $_SESSION['error_message'] = 'Vui lòng đăng nhập để thực hiện thao tác này.';
    header('Location: DangNhap.php'); // Chuyển đến trang đăng nhập
    exit;
}
$userID = (int)$_SESSION['IDUser']; // Lấy ID user đang đăng nhập

// --- 2. Kiểm tra action và idDH từ URL ---
if (isset($_GET['action'], $_GET['idDH']) && $_GET['action'] == 'confirm' && filter_var($_GET['idDH'], FILTER_VALIDATE_INT) && (int)$_GET['idDH'] > 0) {
    $idDH = (int)$_GET['idDH'];
    $new_status = 2; // Mã trạng thái mới: "Đã nhận hàng"
    $required_current_status = 1; // Yêu cầu trạng thái hiện tại phải là "Đang giao hàng"

    // --- 3. Kiểm tra kết nối DB ---
    if (!isset($conn) || !$conn) {
        error_log("Lỗi kết nối CSDL trong xuly_nhanhang.php");
        $_SESSION['error_message'] = "Lỗi kết nối cơ sở dữ liệu.";
        // Chuyển hướng về trang danh sách (thay tên file nếu cần)
        header('Location: DonHang.php'); // Hoặc don_hang_cua_toi.php
        exit;
    }

    // --- 4. Cập nhật trạng thái đơn hàng (Dùng Prepared Statement) ---
    // Chỉ cập nhật nếu đơn hàng thuộc user này VÀ đang ở trạng thái $required_current_status (Đang giao hàng)
    $sql_update = "UPDATE donhang SET DaXuLy = ? WHERE idDH = ? AND idUser = ? AND DaXuLy = ?";
    $stmt = mysqli_prepare($conn, $sql_update);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'iiii', $new_status, $idDH, $userID, $required_current_status);
        mysqli_stmt_execute($stmt);

        // Kiểm tra xem có dòng nào được cập nhật không
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            // *** Đặt thông báo thành công vào SESSION ***
            $_SESSION['success_message'] = "Xác nhận đã nhận đơn hàng #" . $idDH . " thành công!";
            // ------------------------------------------

            // // Optional: Cập nhật DaXuLy trong donhangchitiet nếu cần đồng bộ
            // $sql_update_details = "UPDATE donhangchitiet SET DaXuLy = ? WHERE idDH = ?";
            // $stmt_details = mysqli_prepare($conn, $sql_update_details);
            // if($stmt_details){
            //     mysqli_stmt_bind_param($stmt_details, 'ii', $new_status, $idDH);
            //     mysqli_stmt_execute($stmt_details);
            //     mysqli_stmt_close($stmt_details);
            // }

        } else {
             // Không có dòng nào được cập nhật
             // Kiểm tra lý do: có thể đơn hàng không tồn tại, không thuộc user, hoặc trạng thái không phải là 1
             $sql_check_status = "SELECT DaXuLy FROM donhang WHERE idDH = ? AND idUser = ?";
             $stmt_check = mysqli_prepare($conn, $sql_check_status);
             if($stmt_check){
                 mysqli_stmt_bind_param($stmt_check, 'ii', $idDH, $userID);
                 mysqli_stmt_execute($stmt_check);
                 $res_check = mysqli_stmt_get_result($stmt_check);
                 if(mysqli_num_rows($res_check) > 0){
                     $row_check = mysqli_fetch_assoc($res_check);
                     if($row_check['DaXuLy'] != $required_current_status){
                         $_SESSION['error_message'] = "Không thể xác nhận đơn hàng #{$idDH}. Đơn hàng không ở trạng thái 'Đang giao hàng'.";
                     } else {
                          $_SESSION['error_message'] = "Lỗi không xác định khi xác nhận đơn hàng #{$idDH}.";
                     }
                 } else {
                      $_SESSION['error_message'] = "Không tìm thấy đơn hàng #{$idDH} hoặc đơn hàng không thuộc về bạn.";
                 }
                 mysqli_free_result($res_check);
                 mysqli_stmt_close($stmt_check);
             } else {
                 $_SESSION['error_message'] = "Lỗi kiểm tra trạng thái đơn hàng.";
             }
        }
        mysqli_stmt_close($stmt); // Đóng statement update chính
    } else {
        // Lỗi chuẩn bị câu lệnh
        error_log("Prepare statement failed (confirm order): " . mysqli_error($conn));
        $_SESSION['error_message'] = "Lỗi hệ thống khi xác nhận đơn hàng.";
    }
    mysqli_close($conn); // Đóng kết nối
} else {
    // Action hoặc idDH không hợp lệ
    $_SESSION['error_message'] = "Yêu cầu không hợp lệ.";
}

// --- 5. Chuyển hướng về trang danh sách đơn hàng ---
// !!! Thay 'DonHang.php' bằng tên file danh sách đơn hàng thực tế của bạn !!!
header('Location: DonHang.php'); // Hoặc don_hang_cua_toi.php
exit; // Dừng script sau khi chuyển hướng

?>