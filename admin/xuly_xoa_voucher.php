<?php
session_start();
include_once('../connection/connect_database.php');

// --- Kiểm tra quyền admin nếu cần ---
/*
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['message'] = "Bạn không có quyền thực hiện hành động này.";
    $_SESSION['message_type'] = "danger";
    header('Location: login.php');
    exit;
}
*/

// --- Kiểm tra ID voucher ---
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $_SESSION['message'] = "ID Voucher không hợp lệ.";
    $_SESSION['message_type'] = "warning";
    header('Location: quanly_voucher.php');
    exit;
}
$voucher_id = (int)$_GET['id'];

// --- Kiểm tra kết nối ---
if (!isset($conn) || !$conn) {
    error_log("Lỗi kết nối CSDL trong xuly_xoa_voucher.php: " . mysqli_connect_error());
    $_SESSION['message'] = "Lỗi hệ thống, không thể kết nối CSDL.";
    $_SESSION['message_type'] = "danger";
    header('Location: quanly_voucher.php');
    exit;
}
mysqli_set_charset($conn, 'utf8');


// --- Thực hiện xóa (Dùng Prepared Statement) ---
$sql_delete = "DELETE FROM vouchers WHERE id = ?";
$stmt_delete = mysqli_prepare($conn, $sql_delete);

if ($stmt_delete) {
    mysqli_stmt_bind_param($stmt_delete, "i", $voucher_id);

    if (mysqli_stmt_execute($stmt_delete)) {
        // Kiểm tra xem có dòng nào bị xóa không
        if (mysqli_stmt_affected_rows($stmt_delete) > 0) {
             $_SESSION['message'] = "Đã xóa voucher (ID: $voucher_id) thành công!";
             $_SESSION['message_type'] = "success";
        } else {
             $_SESSION['message'] = "Không tìm thấy voucher có ID: $voucher_id để xóa.";
             $_SESSION['message_type'] = "warning";
        }
    } else {
         $_SESSION['message'] = "Lỗi khi xóa voucher: " . mysqli_stmt_error($stmt_delete);
         $_SESSION['message_type'] = "danger";
         error_log("Voucher delete failed: " . mysqli_stmt_error($stmt_delete));
    }
    mysqli_stmt_close($stmt_delete);
} else {
    $_SESSION['message'] = "Lỗi hệ thống khi chuẩn bị xóa voucher.";
    $_SESSION['message_type'] = "danger";
    error_log("Prepare failed (delete voucher): " . mysqli_error($conn));
}

mysqli_close($conn);

// --- Chuyển hướng về trang danh sách ---
header('Location: quanly_voucher.php');
exit;

?>