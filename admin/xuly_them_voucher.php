<?php
session_start();
// Bao gồm file kết nối (điều chỉnh đường dẫn nếu cần)
include_once('../connection/connect_database.php');

// --- BẮT ĐẦU: Thêm kiểm tra quyền admin ở đây nếu cần ---
// Ví dụ:
/*
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['message'] = "Bạn không có quyền truy cập trang này.";
    $_SESSION['message_type'] = "warning";
    header('Location: login.php'); // Thay login.php bằng trang đăng nhập admin
    exit;
}
*/
// --- KẾT THÚC: Thêm kiểm tra quyền admin ---


// Kiểm tra kết nối
if (!isset($conn) || !$conn) {
    // Ghi log lỗi thực tế thay vì chỉ báo cho người dùng
    error_log("Lỗi kết nối CSDL trong xuly_them_voucher.php: " . mysqli_connect_error());
    $_SESSION['message'] = "Lỗi hệ thống, không thể kết nối CSDL."; // Thông báo chung chung
    $_SESSION['message_type'] = "danger";
    header('Location: them_voucher.php'); // Chuyển về trang form
    exit;
}
mysqli_set_charset($conn, 'utf8');

// Chỉ xử lý nếu là phương thức POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Lấy dữ liệu từ form và làm sạch (*** SỬA LẠI KEY TỪ $_POST ***)
    $ma_giam_gia = isset($_POST['ma_giam_gia']) ? trim(strtoupper($_POST['ma_giam_gia'])) : ''; // Chuyển thành chữ hoa
    $loai = isset($_POST['loai']) && in_array($_POST['loai'], ['fixed', 'percent']) ? $_POST['loai'] : null;
    $gia_tri_input = isset($_POST['gia_tri']) ? $_POST['gia_tri'] : null;
    $gia_tri_don_toi_thieu_input = isset($_POST['gia_tri_don_toi_thieu']) ? $_POST['gia_tri_don_toi_thieu'] : '0';
    $ngay_bat_dau_input = isset($_POST['ngay_bat_dau']) && !empty($_POST['ngay_bat_dau']) ? $_POST['ngay_bat_dau'] : null;
    $ngay_ket_thuc_input = isset($_POST['ngay_ket_thuc']) && !empty($_POST['ngay_ket_thuc']) ? $_POST['ngay_ket_thuc'] : null;
    $gioi_han_su_dung_input = isset($_POST['gioi_han_su_dung']) ? trim($_POST['gioi_han_su_dung']) : '';
    $trang_thai = isset($_POST['trang_thai']) && $_POST['trang_thai'] == '0' ? 0 : 1; // Lấy đúng key 'trang_thai'

    // 2. Validate dữ liệu chi tiết
    $errors = [];
    if (empty($ma_giam_gia)) {
        $errors[] = "Mã voucher không được để trống.";
    } elseif (!preg_match('/^[A-Z0-9_]+$/', $ma_giam_gia)) { // Cho phép chữ hoa, số, gạch dưới
        $errors[] = "Mã voucher chỉ được chứa chữ cái viết hoa (A-Z), số (0-9) và dấu gạch dưới (_).";
    }

    if ($loai === null) {
        $errors[] = "Loại giảm giá không hợp lệ.";
    }

    // Validate Giá trị
    $gia_tri = null;
    if ($gia_tri_input === null || $gia_tri_input === '') {
         $errors[] = "Giá trị giảm giá không được để trống.";
    } else {
        $gia_tri = filter_var($gia_tri_input, FILTER_VALIDATE_FLOAT);
        if ($gia_tri === false || $gia_tri < 0) {
             $errors[] = "Giá trị giảm giá không hợp lệ (phải là số không âm).";
        } elseif ($loai === 'percent' && $gia_tri > 100) { // Chỉ kiểm tra > 100 nếu là %
             $errors[] = "Giá trị phần trăm không thể lớn hơn 100.";
        }
    }

    // Validate Giá trị đơn tối thiểu
    $gia_tri_don_toi_thieu = 0.00; // Default
     if ($gia_tri_don_toi_thieu_input !== '' && $gia_tri_don_toi_thieu_input !== null && $gia_tri_don_toi_thieu_input !== '0') { // Chỉ validate nếu có nhập và khác 0
         $gia_tri_don_toi_thieu_temp = filter_var($gia_tri_don_toi_thieu_input, FILTER_VALIDATE_FLOAT);
         if ($gia_tri_don_toi_thieu_temp === false || $gia_tri_don_toi_thieu_temp < 0) {
              $errors[] = "Giá trị đơn tối thiểu không hợp lệ.";
         } else {
              $gia_tri_don_toi_thieu = $gia_tri_don_toi_thieu_temp;
         }
     }

    // Validate Dates
    $ngay_bat_dau = null; // Khởi tạo là null
    if ($ngay_bat_dau_input) {
        $start_date_obj = DateTime::createFromFormat('Y-m-d\TH:i', $ngay_bat_dau_input);
        if (!$start_date_obj) {
             $errors[] = "Định dạng Ngày bắt đầu không hợp lệ (YYYY-MM-DDTHH:MM).";
        } else {
            $ngay_bat_dau = $start_date_obj->format('Y-m-d H:i:s'); // Chuyển sang định dạng MySQL
        }
    }

    $ngay_ket_thuc = null; // Khởi tạo là null
    if ($ngay_ket_thuc_input) {
        $end_date_obj = DateTime::createFromFormat('Y-m-d\TH:i', $ngay_ket_thuc_input);
         if (!$end_date_obj) {
             $errors[] = "Định dạng Ngày kết thúc không hợp lệ (YYYY-MM-DDTHH:MM).";
         } else {
             $ngay_ket_thuc = $end_date_obj->format('Y-m-d H:i:s');
         }
    }

    // So sánh ngày bắt đầu và kết thúc chỉ khi cả hai đều hợp lệ
    if ($ngay_bat_dau && $ngay_ket_thuc && strtotime($ngay_ket_thuc) < strtotime($ngay_bat_dau)) {
        $errors[] = "Ngày kết thúc phải lớn hơn hoặc bằng Ngày bắt đầu.";
    }

    // Validate Giới hạn sử dụng
    $gioi_han_su_dung = null; // Default là NULL (không giới hạn)
    if ($gioi_han_su_dung_input !== '') { // Chỉ validate nếu có nhập
        $gioi_han_su_dung_temp = filter_var($gioi_han_su_dung_input, FILTER_VALIDATE_INT);
        // Cho phép bằng 0 không? Nếu không thì dùng $gioi_han_su_dung_temp <= 0
        if ($gioi_han_su_dung_temp === false || $gioi_han_su_dung_temp < 0) {
             $errors[] = "Giới hạn lượt dùng phải là số nguyên không âm.";
        } else {
             $gioi_han_su_dung = $gioi_han_su_dung_temp;
        }
    }


     // Kiểm tra xem mã voucher đã tồn tại chưa (*** SỬA LẠI CỘT VÀ BIẾN ***)
     if (empty($errors) && !empty($ma_giam_gia)) {
         $sql_check_code = "SELECT id FROM vouchers WHERE ma_giam_gia = ?"; // Dùng cột ma_giam_gia
         $stmt_check = mysqli_prepare($conn, $sql_check_code);
         if ($stmt_check) {
              mysqli_stmt_bind_param($stmt_check, "s", $ma_giam_gia); // Dùng biến $ma_giam_gia
              mysqli_stmt_execute($stmt_check);
              mysqli_stmt_store_result($stmt_check);
              if (mysqli_stmt_num_rows($stmt_check) > 0) {
                  $errors[] = "Mã voucher '$ma_giam_gia' đã tồn tại. Vui lòng chọn mã khác.";
              }
              mysqli_stmt_close($stmt_check);
         } else {
              $errors[] = "Lỗi hệ thống khi kiểm tra mã voucher.";
              error_log("Prepare failed (check code): " . mysqli_error($conn));
         }
     }


    // 3. Xử lý kết quả validate
    if (!empty($errors)) {
        // Nếu có lỗi, lưu lỗi vào session và quay lại trang form
        $_SESSION['message'] = "Thêm voucher thất bại:<br>" . implode('<br>', $errors);
        $_SESSION['message_type'] = "danger";
        // *** Nên lưu lại dữ liệu đã nhập để điền lại form ***
        $_SESSION['form_data'] = $_POST;
        header('Location: them_voucher.php');
        exit;
    } else {
        // 4. Nếu không có lỗi, thực hiện INSERT (*** SỬA LẠI TÊN CỘT VÀ BIẾN ***)
        // date_created dùng NOW(), times_used mặc định là 0 trong CSDL hoặc chèn 0
        $sql_insert = "INSERT INTO vouchers (ma_giam_gia, loai, gia_tri, gia_tri_don_toi_thieu, ngay_bat_dau, ngay_ket_thuc, gioi_han_su_dung, trang_thai, so_lan_da_dung, ngay_tao)
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())"; // Thêm cột so_lan_da_dung, ngay_tao
        $stmt_insert = mysqli_prepare($conn, $sql_insert);

        if ($stmt_insert) {
            // Bind các tham số với biến đã được validate và đổi tên
            mysqli_stmt_bind_param($stmt_insert, "ssddssii", // 8 tham số cần bind
                $ma_giam_gia,
                $loai,
                $gia_tri,
                $gia_tri_don_toi_thieu,
                $ngay_bat_dau,         // Đã là Y-m-d H:i:s hoặc NULL
                $ngay_ket_thuc,        // Đã là Y-m-d H:i:s hoặc NULL
                $gioi_han_su_dung,     // Là số nguyên hoặc NULL
                $trang_thai            // Đã là 0 hoặc 1
            );

            // Thực thi
            if (mysqli_stmt_execute($stmt_insert)) {
                $_SESSION['message'] = "Đã thêm voucher '<b>" . htmlspecialchars($ma_giam_gia) . "</b>' thành công!";
                $_SESSION['message_type'] = "success";
                unset($_SESSION['form_data']); // Xóa dữ liệu form cũ nếu thành công
            } else {
                $_SESSION['message'] = "Lỗi khi thêm voucher vào CSDL: " . mysqli_stmt_error($stmt_insert);
                $_SESSION['message_type'] = "danger";
                error_log("Voucher insert failed: " . mysqli_stmt_error($stmt_insert));
                $_SESSION['form_data'] = $_POST; // Giữ lại dữ liệu form khi lỗi
            }
            mysqli_stmt_close($stmt_insert);
        } else {
             $_SESSION['message'] = "Lỗi hệ thống khi chuẩn bị thêm voucher.";
             $_SESSION['message_type'] = "danger";
             error_log("Prepare failed (insert voucher): " . mysqli_error($conn));
             $_SESSION['form_data'] = $_POST; // Giữ lại dữ liệu form khi lỗi
        }

        // 5. Chuyển hướng về trang danh sách (hoặc trang thêm nếu có lỗi CSDL)
        if($_SESSION['message_type'] == "success") {
             header('Location: quanly_voucher.php');
        } else {
             header('Location: them_voucher.php'); // Quay lại trang thêm nếu lỗi insert
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

mysqli_close($conn); // Đóng kết nối (sẽ không chạy tới đây nếu redirect thành công)
?>