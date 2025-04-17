<?php
include_once ('../connection/connect_database.php');

// --- Lấy dữ liệu hiện tại để hiển thị ---
$r_gh = null; // Khởi tạo biến
if (isset($_GET['idPTTT'])) {
    $idPTTT = mysqli_real_escape_string($conn, $_GET['idPTTT']); // Bảo mật cơ bản
    $sl = "select * from phuongthucthanhtoan where idPTTT='" . $idPTTT . "'";
    $kq = mysqli_query($conn, $sl);
    if ($kq && mysqli_num_rows($kq) > 0) {
        $r_gh = mysqli_fetch_array($kq);
    } else {
        // Xử lý trường hợp không tìm thấy idPTTT (ví dụ: chuyển hướng hoặc báo lỗi)
        echo "<script language='javascript'>alert('Không tìm thấy phương thức thanh toán!');";
        echo "location.href='index_pttt.php';</script>";
        exit; // Dừng script nếu không có dữ liệu
    }
} else {
    // Xử lý trường hợp không có idPTTT trên URL
    echo "<script language='javascript'>alert('Thiếu ID phương thức thanh toán!');";
    echo "location.href='index_pttt.php';</script>";
    exit; // Dừng script
}


// --- Xử lý XÓA ---
if (isset($_POST['xoa']))
{
    $idPTTT_del = mysqli_real_escape_string($conn, $_GET['idPTTT']); // Lấy lại ID để xóa
    // Không cần kiểm tra $kq ở đây nữa
    $sl_del = "delete from phuongthucthanhtoan where idPTTT=". $idPTTT_del;
    $kq_del = mysqli_query($conn, $sl_del);
    if($kq_del) {
        echo "<script language='javascript'>alert('Xóa thành công!');";
        echo "location.href='index_pttt.php';</script>";
        exit;
    } else {
        echo "<script language='javascript'>alert('Xóa không thành công! Lỗi: ".mysqli_error($conn)."');</script>";
    }
}

// --- Xử lý SỬA ---
if (isset($_POST['sua']))
{
    if (isset($_POST['TenPhuongThucTT']) && isset($_GET['idPTTT'])) // Kiểm tra cả idPTTT
    {
        $idPTTT_upd = mysqli_real_escape_string($conn, $_GET['idPTTT']);
        $tenPTTT_upd = mysqli_real_escape_string($conn, $_POST['TenPhuongThucTT']);
        $ghiChu_upd = mysqli_real_escape_string($conn, $_POST['GhiChu']);
        $anHien_upd = mysqli_real_escape_string($conn, $_POST['AnHien']);

        $check = false; // kiểm tra trùng tên
        // Chỉ kiểm tra trùng tên với các bản ghi KHÁC bản ghi hiện tại
        $sql_check = "select TenPhuongThucTT from phuongthucthanhtoan WHERE idPTTT <> ".$idPTTT_upd;
        $kq_check = mysqli_query($conn, $sql_check);

        if ($kq_check) { // Kiểm tra query check có thành công không
            while ($r_check = $kq_check->fetch_assoc())
            {
                if ($r_check['TenPhuongThucTT'] == $tenPTTT_upd) {
                    $check = true; // trùng tên
                    break;
                }
            }
        } else {
             echo "<script language='javascript'>alert('Lỗi khi kiểm tra tên!');</script>";
        }


        if ($check == false) {
            $query_upd = "UPDATE phuongthucthanhtoan SET TenPhuongThucTT ='". $tenPTTT_upd ."', GhiChu='".$ghiChu_upd."', AnHien=".$anHien_upd." WHERE idPTTT=".$idPTTT_upd;
            $result_upd = mysqli_query($conn, $query_upd);

            if ($result_upd) {
                echo "<script language='javascript'>alert('Cập nhật thành công!');";
                // Lấy lại dữ liệu mới nhất sau khi cập nhật để hiển thị
                $sl_new = "select * from phuongthucthanhtoan where idPTTT='" . $idPTTT_upd . "'";
                $kq_new = mysqli_query($conn, $sl_new);
                $r_gh = mysqli_fetch_array($kq_new); // Cập nhật lại $r_gh
                // Không chuyển hướng ngay để user thấy thông báo và dữ liệu mới
                // echo "location.href='index_pttt.php';</script>";
                 echo "location.href = 'sua_xoa_pttt.php?idPTTT=".$idPTTT_upd."';</script>"; // Tải lại trang sửa
                 exit;

            } else {
                 echo "<script language='javascript'>alert('Cập nhật không thành công! Lỗi: ".mysqli_error($conn)."');</script>";
            }
        } else {
            echo "<script language='javascript'>alert('Trùng tên Phương Thức Thanh Toán với một mục khác!');</script>";
        }
    } else {
         echo "<script language='javascript'>alert('Thiếu thông tin để cập nhật!');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <?php include_once ("header1.php");?>
    <title>Sửa Phương Thức Thanh Toán</title>
    <?php include_once('header2.php');?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
         body {
            background-color: #e9ecef;
        }
        .form-wrapper {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 8px;
            max-width: 700px;
            margin: auto;
        }
        .form-title-custom {
             color: #2E7D32;
             font-weight: bold;
             margin-bottom: 30px;
             text-align: center;
        }
        .form-label {
            margin-bottom: 0.5rem;
            font-weight: 500;
             display: block;
        }
        .required-star {
            color: red;
        }
        .helper-text {
            font-size: 0.875em;
            color: #6c757d;
             margin-top: 0.25rem;
             display: block;
        }
        .form-control, .form-select {
            border-color: #ced4da;
            border-radius: 4px;
        }
        .form-control:focus, .form-select:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .button-group {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
        }
        .btn-custom {
            padding: 10px 25px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 1rem;
            margin: 0 5px;
            border: 1px solid transparent;
            transition: all 0.2s ease-in-out;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        /* Nút Lưu (Sửa) */
        .btn-custom-save {
            background-color: #4CAF50;
            color: white;
            border-color: #4CAF50;
        }
        .btn-custom-save:hover {
            background-color: #45a049;
            border-color: #45a049;
            color: white;
        }
        /* Nút Xóa */
        .btn-custom-delete {
            background-color: #e74c3c; /* Màu đỏ */
            color: white;
            border-color: #e74c3c;
        }
        .btn-custom-delete:hover {
            background-color: #c0392b;
            border-color: #c0392b;
            color: white;
        }
        /* Nút Hủy/Trở về */
        .btn-custom-cancel {
            background-color: white;
            color: #343a40;
            border-color: #343a40;
        }
         .btn-custom-cancel:hover {
            background-color: #f8f9fa;
            color: #343a40;
            border-color: #343a40;
         }
    </style>
</head>
<body>
<?php include_once ('header3.php');?>

<div class="container mt-5 mb-5">
    <div class="form-wrapper shadow-sm">
        <h3 class="form-title-custom">CHỈNH SỬA PHƯƠNG THỨC THANH TOÁN</h3>

        <?php if ($r_gh): // Chỉ hiển thị form nếu có dữ liệu ?>
        <form method="post" action="sua_xoa_pttt.php?idPTTT=<?php echo htmlspecialchars($_GET['idPTTT']); ?>" name="SuaXoaPTTT" id="editPaymentMethodForm">
            <div class="mb-4">
                <label for="TenPhuongThucTT" class="form-label">
                    Tên phương thức thanh toán <span class="required-star">*</span>
                </label>
                <input type="text" class="form-control" id="TenPhuongThucTT" name="TenPhuongThucTT" value="<?php echo htmlspecialchars($r_gh['TenPhuongThucTT']); ?>" required>
            </div>

            <div class="mb-4">
                <label for="GhiChu" class="form-label">Ghi Chú</label>
                <input type="text" class="form-control" id="GhiChu" name="GhiChu" value="<?php echo htmlspecialchars($r_gh['GhiChu']); ?>">
                <small class="helper-text">Thông tin bổ sung hoặc hướng dẫn (nếu có).</small>
            </div>

            <div class="mb-4">
                <label for="AnHien" class="form-label">Trạng thái</label>
                <select class="form-select" id="AnHien" name="AnHien">
                    <option value="1" <?php echo ($r_gh['AnHien'] == 1 ? "selected" : "")?>>Hiện</option>
                    <option value="0" <?php echo ($r_gh['AnHien'] == 0 ? "selected" : "")?>>Ẩn</option>
                </select>
                <small class="helper-text">Chọn "Hiện" để áp dụng phương thức này cho khách hàng.</small>
            </div>

            <div class="button-group">
                 <button name="sua" type="submit" class="btn btn-custom btn-custom-save">
                     <i class="bi bi-check-lg"></i> Lưu Thay Đổi
                 </button>
                 <button name="xoa" type="submit" class="btn btn-custom btn-custom-delete" onclick="return confirm('Bạn có chắc chắn muốn xóa phương thức thanh toán này? Dữ liệu không thể phục hồi.');">
                     <i class="bi bi-trash"></i> Xóa
                 </button>
                  <button type="button" name="trove" class="btn btn-custom btn-custom-cancel" onclick="location.href='index_pttt.php'">
                     <i class="bi bi-arrow-left"></i> Trở Về
                 </button>
            </div>

        </form>
         <?php else: ?>
            <p class="text-center text-danger">Không tải được dữ liệu phương thức thanh toán.</p>
         <?php endif; ?>

    </div> </div>

<script type="text/javascript">
    // Không cần hàm getConfirmation() nữa nếu nút Trở về dùng location.href trực tiếp
    // Hàm confirm cho nút xóa đã được đặt inline trong thuộc tính onclick
</script>

<?php include_once ('footer.php');?>
</body>
</html>