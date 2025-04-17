<?php
include_once ('../connection/connect_database.php');

// --- Giữ nguyên logic PHP gốc để lấy dữ liệu ---
$dl = null; // Khởi tạo
if (isset($_GET['idNH']) && is_numeric($_GET['idNH'])) { // Thêm kiểm tra idNH hợp lệ
    $idNH_edit = (int)$_GET['idNH'];
    $sl_nh = "SELECT * FROM nhanhieu WHERE idNH = " . $idNH_edit;
    $rs = mysqli_query($conn, $sl_nh);
    if (!$rs) {
        echo "<script language='javascript'>alert('Lỗi truy vấn lấy thông tin nhãn hiệu!');";
        echo "location.href='index_nh.php';</script>";
        exit; // Thêm exit
    }
    if (mysqli_num_rows($rs) > 0) {
        $dl = mysqli_fetch_array($rs); // Dùng fetch_array như gốc
    } else {
         echo "<script language='javascript'>alert('Không tìm thấy nhãn hiệu này!');";
         echo "location.href='index_nh.php';</script>";
         exit; // Thêm exit
    }
} else {
     echo "<script language='javascript'>alert('ID nhãn hiệu không hợp lệ!');";
     echo "location.href='index_nh.php';</script>";
     exit; // Thêm exit
}


// --- Giữ nguyên logic PHP gốc để xử lý XÓA ---
if (isset($_POST['xoa'])) {
    // Cảnh báo: Logic cập nhật nhanhieu thành idNH=1 có thể không đúng, nên xem lại.
    // Giữ nguyên logic cập nhật nhanhieu (CẢNH BÁO BẢO MẬT - SQL Injection)
    $sql_update_nh_on_delete = "UPDATE nhanhieu SET idNH=1 WHERE idNH=" . (int)$_GET['idNH']; // Giữ nguyên logic nhưng ép kiểu ID
    mysqli_query($conn, $sql_update_nh_on_delete); // Thực thi không kiểm tra kết quả chặt chẽ

    // Giữ nguyên logic DELETE (CẢNH BÁO BẢO MẬT - SQL Injection)
    $sl_delete = "DELETE FROM nhanhieu WHERE idNH=" . (int)$_GET['idNH']; // Giữ nguyên logic nhưng ép kiểu ID
    $kq_delete = mysqli_query($conn, $sl_delete);

    if ($kq_delete) {
        echo "<script language='javascript'>alert('Xóa thành công!');";
        echo "location.href='index_nh.php';</script>";
        exit; // Thêm exit
    } else {
        echo "<script language='javascript'>alert('Xóa không thành công!');</script>";
    }
}

// --- Giữ nguyên logic PHP gốc để xử lý SỬA ---
if (isset($_POST['sua'])) {
    if (isset($_POST['TenNH'])) {
        $check = false; // biến kiểm tra trùng tên
        // Giữ nguyên query kiểm tra trùng tên (CẢNH BÁO BẢO MẬT - SQL Injection)
        $sql_nh2 = "SELECT TenNH FROM nhanhieu WHERE idNH <> " . (int)$_GET['idNH']; // Giữ nguyên logic nhưng ép kiểu ID
        $kq_check = mysqli_query($conn, $sql_nh2);
        if ($kq_check) { // Thêm kiểm tra query thành công
            while ($r_check = mysqli_fetch_assoc($kq_check)) {
                 // Giữ nguyên cách kiểm tra trùng
                if ($r_check['TenNH'] == $_POST['TenNH']) {
                    $check = true;
                    break; // Thêm break
                }
            }
        } else {
             echo "<script language='javascript'>alert('Lỗi kiểm tra tên nhãn hiệu!');</script>";
             $check = true; // Coi như trùng để dừng lại nếu không kiểm tra được
        }

        if ($check == false) {
            // Giữ nguyên query UPDATE (CẢNH BÁO BẢO MẬT - SQL Injection)
             // Nên dùng hàm mysqli_real_escape_string cho các giá trị chuỗi $_POST
             $tenNH_escaped = mysqli_real_escape_string($conn, $_POST["TenNH"]);
             $idL_escaped = (int)$_POST["idL"]; // Ép kiểu int
             $thuTu_escaped = (int)$_POST["ThuTu"]; // Ép kiểu int
             $anHien_escaped = (int)$_POST["AnHien"]; // Ép kiểu int
             $idNH_update = (int)$_GET["idNH"]; // Ép kiểu int

             $query_update = "UPDATE nhanhieu SET TenNH = '" . $tenNH_escaped . "',
                              idL = " . $idL_escaped . ",
                              ThuTu = " . $thuTu_escaped . ",
                              AnHien = " . $anHien_escaped . " WHERE idNH = " . $idNH_update;

            $result_nh_update = mysqli_query($conn, $query_update);
            if (!$result_nh_update) {
                // echo "<script language='javascript'>alert('Cập nhật không thành công! Lỗi: ".mysqli_error($conn)."');</script>"; // Hiện lỗi SQL nếu cần debug
                 echo "<script language='javascript'>alert('Cập nhật không thành công!');</script>";
            } else {
                echo "<script language='javascript'>alert('Cập nhật thành công!');";
                echo "location.href='index_nh.php';</script>";
                exit; // Thêm exit
            }
        } else {
             // Sửa lại thông báo cho đúng ngữ cảnh
            echo "<script language='javascript'>alert('Trùng tên nhãn hiệu!');</script>";
        }
    }
     // Cập nhật lại $dl để form hiển thị giá trị vừa nhập bị lỗi
     $dl['TenNH'] = $_POST['TenNH'];
     $dl['idL'] = $_POST['idL'];
     $dl['ThuTu'] = $_POST['ThuTu'];
     $dl['AnHien'] = $_POST['AnHien'];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once("header1.php"); ?>
    <title>Sửa Nhãn Hiệu - ID: <?php echo isset($dl['idNH']) ? $dl['idNH'] : ''; ?></title>
    <?php include_once('header2.php'); ?>
    <style>
        /* CSS tùy chỉnh nhẹ */
        .page-title {
            color: #007bff; /* Màu xanh dương */
            text-align: center;
            margin-bottom: 30px;
        }
        .custom-form-container {
            max-width: 750px;
            margin: 30px auto;
            background-color: #f9f9f9;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border: 1px solid #ddd;
        }
        .button-group {
            text-align: center;
            margin-top: 25px;
        }
        .button-group .btn {
            margin: 0 5px;
        }
         /* Bỏ các style cũ cho input, nút, form-container width */
    </style>
</head>
<body>
<?php include_once('header3.php'); ?>

<div class="container mt-4">
    <div class="custom-form-container">
        <h3 class="page-title">SỬA NHÃN HIỆU</h3>

         <?php if ($dl): // Chỉ hiển thị form nếu có dữ liệu ?>
        <form method="post" action="" name="SuaNHForm" id="SuaNHForm" enctype="multipart/form-data">
             <?php // Giữ nguyên action rỗng để submit về trang hiện tại ?>

            <div class="row mb-3">
                <label for="TenNH" class="col-sm-3 col-form-label"><strong>Tên nhãn hiệu</strong></label>
                <div class="col-sm-9">
                    <input type="text" name="TenNH" id="TenNH" class="form-control" required value="<?php echo htmlspecialchars($dl['TenNH']); // Thêm htmlspecialchars ?>">
                </div>
            </div>

            <div class="row mb-3">
                <label for="idL" class="col-sm-3 col-form-label"><strong>Loại sản phẩm</strong></label> <?php // Sửa label ?>
                <div class="col-sm-9">
                    <?php
                    // Giữ nguyên query lấy loại SP gốc
                    $sl_l = "SELECT * FROM loaisp";
                    $rs_l = mysqli_query($conn, $sl_l);
                    if (!$rs_l) {
                         echo "<div class='text-danger'>Lỗi: Không thể truy vấn Loại sản phẩm!</div>";
                    } else {
                    ?>
                    <select name="idL" id="idL" class="form-select" required> <?php // Thêm class và required ?>
                        <option value="">-- Chọn loại sản phẩm --</option> <?php // Thêm option mặc định ?>
                        <?php while ($r = mysqli_fetch_assoc($rs_l)) { // Dùng fetch_assoc như code gốc xử lý sửa ?>
                            <option value="<?php echo $r["idL"]; ?>" <?php if ($dl['idL'] == $r["idL"]) echo "selected"; ?>><?php echo htmlspecialchars($r['TenL']); // Thêm htmlspecialchars ?></option>
                        <?php } ?>
                    </select>
                    <?php } // end if $rs_l ?>
                </div>
            </div>

            <div class="row mb-3">
                <label for="ThuTu" class="col-sm-3 col-form-label"><strong>Thứ tự</strong></label>
                <div class="col-sm-9">
                    <input type="number" name="ThuTu" id="ThuTu" class="form-control" value="<?php echo htmlspecialchars($dl['ThuTu']); // Thêm htmlspecialchars ?>">
                </div>
            </div>

            <div class="row mb-3">
                <label for="AnHien" class="col-sm-3 col-form-label"><strong>Trạng thái</strong></label> <?php // Sửa label ?>
                <div class="col-sm-9">
                    <select name="AnHien" id="AnHien" class="form-select"> <?php // Thêm class ?>
                        <option value="1" <?php if ($dl['AnHien'] == 1) echo "selected"; ?>>Hiện</option> <?php // Bỏ strong ?>
                        <option value="0" <?php if ($dl['AnHien'] == 0) echo "selected"; ?>>Ẩn</option> <?php // Bỏ strong ?>
                    </select>
                </div>
            </div>

            <div class="button-group">
                 <?php // Giữ nguyên input và class gốc nhưng đổi thành class btn của bootstrap ?>
                <input name="sua" type="submit" value="Lưu thay đổi" class="btn btn-success" /> <?php // Đổi value và class ?>
                <input name="xoa" type="submit" value="Xóa" class="btn btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa nhãn hiệu này? \nLƯU Ý: Thao tác này có thể ảnh hưởng đến các bảng liên quan.');" /> <?php // Thêm onclick confirm ?>
                <input type="button" name="Huy" value="Hủy" class="btn btn-secondary" onclick="getConfirmation();"> <?php // Đổi class ?>
            </div>
        </form>
         <?php else: ?>
             <div class="alert alert-danger text-center">Không tìm thấy dữ liệu cho nhãn hiệu này.</div>
             <div class="text-center">
                  <a href="index_nh.php" class="btn btn-secondary">Trở về danh sách</a>
             </div>
         <?php endif; ?>
    </div></div> <script type="text/javascript">
    // Giữ nguyên hàm JS gốc
    function getConfirmation() {
        var retVal = confirm("Bạn có muốn hủy các thay đổi và quay lại trang danh sách?"); // Sửa lại nội dung confirm
        if (retVal == true) {
            location.href = 'index_nh.php'; // Chuyển về trang danh sách nhãn hiệu
        }
    }
     // Optional: Client-side validation
    document.getElementById('SuaNHForm').addEventListener('submit', function(event) {
        var tenNHInput = document.getElementById('TenNH');
        var idLSelect = document.getElementById('idL');
        if (tenNHInput.value.trim() === '') {
            alert('Vui lòng nhập Tên nhãn hiệu.');
            event.preventDefault();
            tenNHInput.focus();
            return; // Thêm return
        }
        if (idLSelect.value === '') {
             alert('Vui lòng chọn Loại sản phẩm.');
             event.preventDefault();
             idLSelect.focus();
             return; // Thêm return
         }
    });
</script>

<?php include_once('footer.php'); ?>
</body>
</html>