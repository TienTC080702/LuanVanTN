<?php
include_once ('../connection/connect_database.php');

// --- Lấy ID loại sản phẩm cần sửa ---
if (!isset($_GET['idL']) || !is_numeric($_GET['idL'])) {
    echo "<script language='javascript'>alert('ID loại sản phẩm không hợp lệ!'); location.href='index_ds_loaisp.php';</script>";
    exit;
}
$idL_edit = (int)$_GET['idL'];
$message = ''; // Biến lưu thông báo
$error = false; // Biến cờ lỗi

// --- Lấy thông tin loại sản phẩm hiện tại (Dùng Prepared Statement) ---
$r_lsp = null; // Khởi tạo biến
$sql_fetch = "SELECT * FROM loaisp WHERE idL = ?";
$stmt_fetch = mysqli_prepare($conn, $sql_fetch);
if ($stmt_fetch) {
    mysqli_stmt_bind_param($stmt_fetch, "i", $idL_edit);
    mysqli_stmt_execute($stmt_fetch);
    $result_fetch = mysqli_stmt_get_result($stmt_fetch);
    if (mysqli_num_rows($result_fetch) > 0) {
        $r_lsp = mysqli_fetch_assoc($result_fetch); // Dùng assoc cho dễ đọc
    } else {
        echo "<script language='javascript'>alert('Không tìm thấy loại sản phẩm này!'); location.href='index_ds_loaisp.php';</script>";
        exit;
    }
    mysqli_stmt_close($stmt_fetch);
} else {
    die("Lỗi chuẩn bị câu lệnh lấy dữ liệu: " . mysqli_error($conn));
}


// --- Xử lý XÓA ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['xoa'])) {
    $idL_delete = (int)$_POST['idL_hidden']; // Lấy ID từ hidden input

    // --- Kiểm tra ràng buộc (ví dụ: sản phẩm nào đang dùng loại này?) ---
    $sql_check_sp = "SELECT COUNT(*) as count FROM sanpham WHERE idL = ?";
    $stmt_check_sp = mysqli_prepare($conn, $sql_check_sp);
    mysqli_stmt_bind_param($stmt_check_sp, "i", $idL_delete);
    mysqli_stmt_execute($stmt_check_sp);
    $result_check_sp = mysqli_stmt_get_result($stmt_check_sp);
    $row_check_sp = mysqli_fetch_assoc($result_check_sp);
    mysqli_stmt_close($stmt_check_sp);

    if ($row_check_sp['count'] > 0) {
        // Có sản phẩm đang sử dụng loại này -> Không cho xóa
        echo "<script language='javascript'>alert('Không thể xóa loại sản phẩm này vì đang có sản phẩm sử dụng!');</script>";
    } else {
        // Không có ràng buộc, tiến hành xóa
        // Lưu ý: Phần cập nhật nhanhieu có vẻ không đúng logic, nên comment lại hoặc sửa thành cập nhật sanpham nếu cần
        /*
        // Cập nhật các bản ghi liên quan (CẦN XEM LẠI LOGIC NÀY - có thể là bảng sanpham?)
        $sql_update_related = "UPDATE nhanhieu SET idL = 1 WHERE idL = ?"; // Giả sử 1 là ID mặc định
        $stmt_update_related = mysqli_prepare($conn, $sql_update_related);
        if ($stmt_update_related) {
            mysqli_stmt_bind_param($stmt_update_related, "i", $idL_delete);
            mysqli_stmt_execute($stmt_update_related); // Thực thi không cần kiểm tra kết quả chặt chẽ ở đây
            mysqli_stmt_close($stmt_update_related);
        } else {
             echo "<script language='javascript'>alert('Lỗi cập nhật bảng liên quan!');</script>";
             // Có thể dừng lại ở đây nếu việc cập nhật này là bắt buộc
        }
        */

        // Thực hiện xóa loại sản phẩm (Dùng Prepared Statement)
        $sql_delete = "DELETE FROM loaisp WHERE idL = ?";
        $stmt_delete = mysqli_prepare($conn, $sql_delete);
        if ($stmt_delete) {
            mysqli_stmt_bind_param($stmt_delete, "i", $idL_delete);
            if (mysqli_stmt_execute($stmt_delete)) {
                mysqli_stmt_close($stmt_delete);
                mysqli_close($conn);
                echo "<script language='javascript'>alert('Xóa loại sản phẩm thành công!'); location.href='index_ds_loaisp.php';</script>";
                exit;
            } else {
                $message = "Xóa không thành công! Lỗi: " . mysqli_stmt_error($stmt_delete);
                $error = true;
            }
             if(isset($stmt_delete) && $stmt_delete) mysqli_stmt_close($stmt_delete);
        } else {
             $message = "Lỗi chuẩn bị câu lệnh xóa: " . mysqli_error($conn);
             $error = true;
        }
    }
}


// --- Xử lý SỬA (UPDATE) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['sua'])) {
     $idL_update = (int)$_POST['idL_hidden']; // Lấy ID từ hidden input
     $tenL_new = isset($_POST['TenL']) ? trim($_POST['TenL']) : '';
     $thuTu_new = isset($_POST['ThuTu']) ? (int)$_POST['ThuTu'] : 0;
     $anHien_new = isset($_POST['AnHien']) ? (int)$_POST['AnHien'] : 1;

    if (empty($tenL_new)) {
        $message = "Vui lòng nhập Tên loại sản phẩm.";
        $error = true;
    } else {
        // --- Kiểm tra tên trùng (với các loại khác loại đang sửa) ---
        $sql_check_update = "SELECT COUNT(*) as count FROM loaisp WHERE TenL = ? AND idL <> ?";
        $stmt_check_update = mysqli_prepare($conn, $sql_check_update);
        $is_duplicate_update = false;
        if ($stmt_check_update) {
            mysqli_stmt_bind_param($stmt_check_update, "si", $tenL_new, $idL_update);
            mysqli_stmt_execute($stmt_check_update);
            $result_check_update = mysqli_stmt_get_result($stmt_check_update);
            $row_count_update = mysqli_fetch_assoc($result_check_update);
            if ($row_count_update['count'] > 0) {
                $is_duplicate_update = true;
            }
            mysqli_stmt_close($stmt_check_update);
        } else {
             $message = "Lỗi truy vấn kiểm tra tên trùng khi cập nhật: " . mysqli_error($conn);
             $error = true;
        }
        // --- Kết thúc kiểm tra trùng ---

        if ($is_duplicate_update) {
            $message = "Tên loại sản phẩm này đã tồn tại ở một loại khác. Vui lòng chọn tên khác.";
            $error = true;
        } elseif (!$error) {
            // --- Thực hiện UPDATE (Dùng Prepared Statement) ---
            $sql_update = "UPDATE loaisp SET TenL = ?, ThuTu = ?, AnHien = ? WHERE idL = ?";
            $stmt_update = mysqli_prepare($conn, $sql_update);
            if ($stmt_update) {
                mysqli_stmt_bind_param($stmt_update, "siii", $tenL_new, $thuTu_new, $anHien_new, $idL_update);
                if (mysqli_stmt_execute($stmt_update)) {
                    mysqli_stmt_close($stmt_update);
                    mysqli_close($conn);
                    echo "<script language='javascript'>alert('Cập nhật loại sản phẩm thành công!'); location.href='index_ds_loaisp.php';</script>";
                    exit;
                } else {
                    $message = "Cập nhật không thành công! Lỗi: " . mysqli_stmt_error($stmt_update);
                    $error = true;
                }
                 if(isset($stmt_update) && $stmt_update) mysqli_stmt_close($stmt_update);
            } else {
                 $message = "Lỗi chuẩn bị câu lệnh UPDATE: " . mysqli_error($conn);
                 $error = true;
            }
        }
    }
     // Nếu có lỗi, cập nhật lại $r_lsp để form hiển thị giá trị vừa nhập bị lỗi
     if ($error) {
         $r_lsp['TenL'] = $tenL_new; // Cập nhật lại giá trị để hiển thị trên form
         $r_lsp['ThuTu'] = $thuTu_new;
         $r_lsp['AnHien'] = $anHien_new;
     }
}

// Hiển thị thông báo lỗi nếu có sau khi xử lý POST
if ($error && !empty($message)) {
     echo "<script language='javascript'>alert('" . addslashes($message) . "');</script>";
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once("header1.php"); ?>
    <title>Sửa Loại Sản Phẩm - ID: <?php echo $idL_edit; ?></title>
    <?php include_once('header2.php'); ?>
    <style>
        .page-title {
            color: rgb(141, 35, 35); /* Giữ màu cũ */
            text-align: center;
            margin-bottom: 30px;
        }
        .custom-form-container {
            max-width: 700px;
            margin: 30px auto; /* Thêm margin top/bottom */
            background-color: #f9f9f9;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border: 1px solid #ddd;
        }
         .button-group {
            text-align: center;
            margin-top: 20px;
        }
        .button-group .btn {
            margin: 0 5px;
        }
    </style>
</head>
<body>
    <?php include_once('header3.php'); ?>

    <div class="container mt-4">
        <h3 class="page-title">SỬA LOẠI SẢN PHẨM</h3>

        <div class="custom-form-container">
             <?php if ($r_lsp): // Chỉ hiển thị form nếu lấy được dữ liệu ?>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?idL=" . $idL_edit; ?>" name="SuaLSPForm" id="SuaLSPForm">
                <?php // Input hidden để gửi idL khi submit form ?>
                <input type="hidden" name="idL_hidden" value="<?php echo $idL_edit; ?>">

                <div class="row mb-3">
                    <label for="TenL" class="col-sm-3 col-form-label"><strong>Tên loại</strong></label>
                    <div class="col-sm-9">
                        <input type="text" name="TenL" id="TenL" class="form-control" required value="<?php echo htmlspecialchars($r_lsp['TenL']); ?>">
                         <?php if($error && strpos($message, 'Tên loại') !== false) echo '<div class="text-danger small mt-1">'.$message.'</div>'; ?>
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="ThuTu" class="col-sm-3 col-form-label"><strong>Thứ tự</strong></label>
                    <div class="col-sm-9">
                        <input type="number" name="ThuTu" id="ThuTu" class="form-control" value="<?php echo htmlspecialchars($r_lsp['ThuTu']); ?>">
                    </div>
                </div>

                <div class="row mb-3">
                    <label for="AnHien" class="col-sm-3 col-form-label"><strong>Trạng thái</strong></label>
                    <div class="col-sm-9">
                        <select name="AnHien" id="AnHien" class="form-select">
                            <option value="1" <?php echo ($r_lsp['AnHien'] == 1) ? "selected" : ""; ?>>Hiện</option>
                            <option value="0" <?php echo ($r_lsp['AnHien'] == 0) ? "selected" : ""; ?>>Ẩn</option>
                        </select>
                    </div>
                </div>

                <div class="button-group">
                     <button name="sua" type="submit" class="btn btn-success">
                         <i class="fas fa-save me-1"></i> Lưu thay đổi
                     </button>
                     <button name="xoa" type="submit" class="btn btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa loại sản phẩm này? \nLƯU Ý: Hành động này có thể không thực hiện được nếu có sản phẩm đang sử dụng loại này.');">
                         <i class="fas fa-trash-alt me-1"></i> Xóa
                     </button>
                     <button type="button" name="trove" class="btn btn-secondary" onclick="getConfirmation();">
                          <i class="fas fa-arrow-left me-1"></i> Trở về
                     </button>
                </div>
            </form>
             <?php else: ?>
                 <div class="alert alert-danger text-center">Không tìm thấy dữ liệu cho loại sản phẩm này.</div>
                 <div class="text-center">
                      <a href="index_ds_loaisp.php" class="btn btn-secondary">Trở về danh sách</a>
                 </div>
             <?php endif; ?>
        </div> </div> <script type="text/javascript">
        function getConfirmation() {
            var retVal = confirm("Bạn có muốn hủy bỏ các thay đổi và quay lại trang danh sách?");
            if (retVal == true) {
                window.location.href = 'index_ds_loaisp.php';
            }
        }
         // Optional: Client-side validation
        document.getElementById('SuaLSPForm').addEventListener('submit', function(event) {
            var tenLInput = document.getElementById('TenL');
            if (tenLInput.value.trim() === '') {
                alert('Vui lòng nhập Tên loại sản phẩm.');
                event.preventDefault();
                tenLInput.focus();
            }
             // Thêm xác nhận cho nút Xóa nếu chưa có onclick
            if (document.activeElement && document.activeElement.name === 'xoa') {
                 if (!confirm('Bạn có chắc chắn muốn xóa loại sản phẩm này? \nLƯU Ý: Hành động này có thể không thực hiện được nếu có sản phẩm đang sử dụng loại này.')) {
                     event.preventDefault();
                 }
            }
        });
    </script>

    <?php include_once('footer.php'); ?>
</body>
</html>