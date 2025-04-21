<?php
session_start();
// Bao gồm file kết nối (điều chỉnh đường dẫn nếu cần)
include_once('../connection/connect_database.php');

// --- Kiểm tra quyền admin nếu cần ---
/*
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['message'] = "Bạn không có quyền truy cập."; $_SESSION['message_type'] = "warning";
    header('Location: login.php'); exit;
}
*/

// Kiểm tra kết nối
if (!isset($conn) || !$conn) {
    error_log("Lỗi kết nối CSDL trong xuly_sua_voucher.php: " . mysqli_connect_error());
    $_SESSION['message'] = "Lỗi hệ thống, không thể kết nối CSDL."; $_SESSION['message_type'] = "danger";
    header('Location: quanly_voucher.php'); // Chuyển về trang danh sách nếu lỗi kết nối
    exit;
}
mysqli_set_charset($conn, 'utf8');

// Chỉ xử lý nếu là phương thức POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Lấy dữ liệu từ form (bao gồm cả ID)
    $id = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT) : null;
    $ma_giam_gia = isset($_POST['ma_giam_gia']) ? trim(strtoupper($_POST['ma_giam_gia'])) : '';
    $loai = isset($_POST['loai']) && in_array($_POST['loai'], ['fixed', 'percent']) ? $_POST['loai'] : null;
    $gia_tri_input = isset($_POST['gia_tri']) ? $_POST['gia_tri'] : null;
    $gia_tri_don_toi_thieu_input = isset($_POST['gia_tri_don_toi_thieu']) ? $_POST['gia_tri_don_toi_thieu'] : '0';
    $ngay_bat_dau_input = isset($_POST['ngay_bat_dau']) && !empty($_POST['ngay_bat_dau']) ? $_POST['ngay_bat_dau'] : null;
    $ngay_ket_thuc_input = isset($_POST['ngay_ket_thuc']) && !empty($_POST['ngay_ket_thuc']) ? $_POST['ngay_ket_thuc'] : null;
    $gioi_han_su_dung_input = isset($_POST['gioi_han_su_dung']) ? trim($_POST['gioi_han_su_dung']) : '';
    $trang_thai = isset($_POST['trang_thai']) && $_POST['trang_thai'] == '0' ? 0 : 1;

    // 2. Validate dữ liệu chi tiết
    $errors = [];
    if ($id === null || $id === false) {
        $errors[] = "ID Voucher không hợp lệ.";
    }
    // --- Reuse validation logic ---
    if (empty($ma_giam_gia)) { $errors[] = "Mã voucher không được để trống."; }
    elseif (!preg_match('/^[A-Z0-9_]+$/', $ma_giam_gia)) { $errors[] = "Mã voucher chỉ được chứa chữ cái viết hoa (A-Z), số (0-9) và dấu gạch dưới (_)."; }
    if ($loai === null) { $errors[] = "Loại giảm giá không hợp lệ."; }

    $gia_tri = null;
    if ($gia_tri_input === null || $gia_tri_input === '') { $errors[] = "Giá trị giảm giá không được để trống."; }
    else { $gia_tri = filter_var($gia_tri_input, FILTER_VALIDATE_FLOAT); if ($gia_tri === false || $gia_tri < 0) { $errors[] = "Giá trị giảm giá không hợp lệ."; } elseif ($loai === 'percent' && $gia_tri > 100) { $errors[] = "Giá trị phần trăm không thể lớn hơn 100."; } }

    $gia_tri_don_toi_thieu = 0.00;
    if ($gia_tri_don_toi_thieu_input !== '' && $gia_tri_don_toi_thieu_input !== null && $gia_tri_don_toi_thieu_input !== '0') { $gia_tri_don_toi_thieu_temp = filter_var($gia_tri_don_toi_thieu_input, FILTER_VALIDATE_FLOAT); if ($gia_tri_don_toi_thieu_temp === false || $gia_tri_don_toi_thieu_temp < 0) { $errors[] = "Giá trị đơn tối thiểu không hợp lệ."; } else { $gia_tri_don_toi_thieu = $gia_tri_don_toi_thieu_temp; } }

    $ngay_bat_dau = null;
    if ($ngay_bat_dau_input) { $start_date_obj = DateTime::createFromFormat('Y-m-d\TH:i', $ngay_bat_dau_input); if (!$start_date_obj) { $errors[] = "Định dạng Ngày bắt đầu không hợp lệ."; } else { $ngay_bat_dau = $start_date_obj->format('Y-m-d H:i:s'); } }

    $ngay_ket_thuc = null;
    if ($ngay_ket_thuc_input) { $end_date_obj = DateTime::createFromFormat('Y-m-d\TH:i', $ngay_ket_thuc_input); if (!$end_date_obj) { $errors[] = "Định dạng Ngày kết thúc không hợp lệ."; } else { $ngay_ket_thuc = $end_date_obj->format('Y-m-d H:i:s'); } }

    if ($ngay_bat_dau && $ngay_ket_thuc && strtotime($ngay_ket_thuc) < strtotime($ngay_bat_dau)) { $errors[] = "Ngày kết thúc phải lớn hơn hoặc bằng Ngày bắt đầu."; }

    $gioi_han_su_dung = null;
    if ($gioi_han_su_dung_input !== '') { $gioi_han_su_dung_temp = filter_var($gioi_han_su_dung_input, FILTER_VALIDATE_INT); if ($gioi_han_su_dung_temp === false || $gioi_han_su_dung_temp < 0) { $errors[] = "Giới hạn lượt dùng phải là số nguyên không âm."; } else { $gioi_han_su_dung = $gioi_han_su_dung_temp; } }

     // Kiểm tra trùng lặp mã voucher (*** NGOẠI TRỪ ID HIỆN TẠI ***)
     if (empty($errors) && !empty($ma_giam_gia) && $id !== null) {
         $sql_check_code = "SELECT id FROM vouchers WHERE ma_giam_gia = ? AND id != ?";
         $stmt_check = mysqli_prepare($conn, $sql_check_code);
         if ($stmt_check) {
              mysqli_stmt_bind_param($stmt_check, "si", $ma_giam_gia, $id);
              mysqli_stmt_execute($stmt_check);
              mysqli_stmt_store_result($stmt_check);
              if (mysqli_stmt_num_rows($stmt_check) > 0) {
                  $errors[] = "Mã voucher '$ma_giam_gia' đã được sử dụng bởi một voucher khác.";
              }
              mysqli_stmt_close($stmt_check);
         } else { // Nếu prepare thất bại
              $errors[] = "Lỗi hệ thống khi kiểm tra mã voucher.";
              // *** SỬA LỖI Ở ĐÂY ***
              error_log("Prepare failed (check code): " . mysqli_error($conn));
         }
     }


    // 3. Xử lý kết quả validate
    if (!empty($errors)) {
        // Nếu có lỗi, lưu lỗi và dữ liệu form vào session, quay lại trang sửa
        $_SESSION['message'] = "Cập nhật voucher thất bại:<br>" . implode('<br>', $errors);
        $_SESSION['message_type'] = "danger";
        $_SESSION['form_data'] = $_POST; // Lưu lại dữ liệu đã nhập
        // Đảm bảo $id có giá trị hợp lệ trước khi redirect
        if ($id !== null && $id !== false) {
            header('Location: sua_voucher.php?id=' . $id);
        } else {
            // Nếu ID không hợp lệ ngay từ đầu, quay về trang danh sách
            header('Location: quanly_voucher.php');
        }
        exit;
    } else {
        // 4. Nếu không có lỗi, thực hiện UPDATE
        $sql_update = "UPDATE vouchers SET
                        ma_giam_gia = ?, loai = ?, gia_tri = ?, gia_tri_don_toi_thieu = ?,
                        ngay_bat_dau = ?, ngay_ket_thuc = ?, gioi_han_su_dung = ?, trang_thai = ?
                       WHERE id = ?";
        $stmt_update = mysqli_prepare($conn, $sql_update);

        if ($stmt_update) {
            // Bind các tham số (8 cho SET + 1 cho WHERE = 9)
            mysqli_stmt_bind_param($stmt_update, "ssddssiii",
                $ma_giam_gia, $loai, $gia_tri, $gia_tri_don_toi_thieu,
                $ngay_bat_dau, $ngay_ket_thuc, $gioi_han_su_dung, $trang_thai,
                $id // id cho WHERE clause
            );

            // Thực thi
            if (mysqli_stmt_execute($stmt_update)) {
                $_SESSION['message'] = "Đã cập nhật voucher '<b>" . htmlspecialchars($ma_giam_gia) . "</b>' thành công!";
                $_SESSION['message_type'] = "success";
                unset($_SESSION['form_data']); // Xóa dữ liệu form cũ nếu thành công
            } else {
                $_SESSION['message'] = "Lỗi khi cập nhật voucher: " . mysqli_stmt_error($stmt_update);
                $_SESSION['message_type'] = "danger";
                error_log("Voucher update failed: " . mysqli_stmt_error($stmt_update));
                $_SESSION['form_data'] = $_POST; // Giữ lại dữ liệu nếu lỗi
            }
            mysqli_stmt_close($stmt_update);
        } else {
             $_SESSION['message'] = "Lỗi hệ thống khi chuẩn bị cập nhật voucher.";
             $_SESSION['message_type'] = "danger";
             error_log("Prepare failed (update voucher): " . mysqli_error($conn));
             $_SESSION['form_data'] = $_POST; // Giữ lại dữ liệu nếu lỗi
        }

        // 5. Chuyển hướng
        if($_SESSION['message_type'] == "success") {
             header('Location: quanly_voucher.php'); // Về trang danh sách nếu thành công
        } else {
            // Quay lại trang sửa nếu lỗi CSDL, đảm bảo ID hợp lệ
            if ($id !== null && $id !== false) {
                header('Location: sua_voucher.php?id=' . $id);
            } else {
                header('Location: quanly_voucher.php'); // Fallback về danh sách nếu ID lỗi
            }
        }
        exit;
    }

} else {
    // Nếu không phải POST request, chuyển hướng về trang danh sách
    $_SESSION['message'] = "Yêu cầu không hợp lệ.";
    $_SESSION['message_type'] = "warning";
    header('Location: quanly_voucher.php');
    exit;
}

mysqli_close($conn);
?>