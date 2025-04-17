<?php
// Kết nối CSDL đặt ở đầu
include_once('../connection/connect_database.php');

// Phần xử lý PHP khi submit form (GIỮ NGUYÊN LOGIC GỐC)
if (isset($_POST['Ok']) && isset($_POST['NoiDung']) && isset($_POST['TieuDe']) && isset($_POST['MoTa']) && isset($_POST['idSP'])) { // Thêm kiểm tra idSP
    // --- Xử lý file upload ---
    $upload_ok = false;
    $hinh_anh_name = null; // Tên file để lưu vào DB, mặc định là NULL
    if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK && $_FILES['file']['size'] > 0) {
        $path = '../images/baiviet/'; // *** Thư mục lưu ảnh bài viết (Nên tạo thư mục này) ***
        if (!file_exists($path)) {
            mkdir($path, 0777, true); // Tạo thư mục nếu chưa có
        }

        $tmp_name = $_FILES['file']['tmp_name'];
        // Tạo tên file mới duy nhất để tránh ghi đè và vấn đề bảo mật
        $original_name = $_FILES['file']['name'];
        $file_extension = pathinfo($original_name, PATHINFO_EXTENSION);
        // Chỉ cho phép một số định dạng ảnh phổ biến
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array(strtolower($file_extension), $allowed_extensions)) {
             $error_message = "Lỗi: Chỉ cho phép tải lên file ảnh (jpg, jpeg, png, gif, webp).";
        } else {
            $hinh_anh_name = uniqid('bv_', true) . '.' . $file_extension; // Tên file mới: bv_ + unique id + extension
            $destination = $path . $hinh_anh_name;

             // Kiểm tra size (ví dụ < 5MB)
             $max_size = 5 * 1024 * 1024; // 5MB
            if ($_FILES['file']['size'] > $max_size) {
                 $error_message = "Lỗi: Kích thước file ảnh quá lớn (tối đa 5MB).";
            } else {
                 // Di chuyển file đã upload
                 if (move_uploaded_file($tmp_name, $destination)) {
                     $upload_ok = true;
                 } else {
                     $error_message = "Lỗi: Không thể di chuyển file ảnh đã tải lên.";
                 }
            }
        }
    } elseif (isset($_FILES['file']) && $_FILES['file']['error'] != UPLOAD_ERR_NO_FILE && $_FILES['file']['error'] != UPLOAD_ERR_OK) {
         // Có lỗi xảy ra trong quá trình upload (ngoài lỗi không chọn file)
         $error_message = "Lỗi tải file lên: Mã lỗi " . $_FILES['file']['error'];
    }
     // Nếu không có lỗi upload (hoặc không có file), mới tiến hành insert DB
     if (!isset($error_message)) {
         // Làm sạch dữ liệu text (nên dùng mysqli_real_escape_string)
         $idSP_clean = intval($_POST['idSP']);
         $tieuDe_clean = mysqli_real_escape_string($conn, $_POST['TieuDe']);
         $moTa_clean = mysqli_real_escape_string($conn, $_POST['MoTa']);   // CKEditor có thể tự escape? Cần kiểm tra lại
         $noiDung_clean = mysqli_real_escape_string($conn, $_POST['NoiDung']); // CKEditor có thể tự escape? Cần kiểm tra lại
         $ngayCapNhat_clean = date("Y-m-d H:i:s"); // Lấy ngày giờ hiện tại từ server
         $anHien_clean = intval($_POST['AnHien']); // 0 hoặc 1

         // Xử lý tên ảnh cho câu lệnh SQL
         $hinh_anh_sql = ($upload_ok && $hinh_anh_name !== null) ? "'$hinh_anh_name'" : 'NULL'; // Nếu upload ok thì lấy tên mới, ko thì NULL

         // Câu lệnh INSERT
         $q_baiviet = "INSERT INTO baiviet (idSP, TieuDe, NoiDung, NgayCapNhat, AnHien, MoTa, img)
                       VALUES ($idSP_clean, '$tieuDe_clean', '$noiDung_clean', '$ngayCapNhat_clean', $anHien_clean, '$moTa_clean', $hinh_anh_sql)";

         $rs = mysqli_query($conn, $q_baiviet);

         if ($rs) {
             // Nếu insert thành công và CÓ upload file thất bại trước đó (hiếm gặp), cần thông báo
             if (isset($error_message_upload)) {
                  echo "<script language='javascript'>alert('Thêm bài viết thành công, nhưng có lỗi khi tải ảnh lên: " . addslashes($error_message_upload) . "');";
                  echo "location.href = 'baiviet.php';</script>";
             } else {
                 echo "<script language='javascript'>alert('Thêm bài viết thành công!');";
                 echo "location.href = 'baiviet.php';</script>";
             }
             exit();
         } else {
             // Nếu INSERT thất bại, và đã upload ảnh thành công trước đó, nên cân nhắc xóa ảnh đã upload
              if ($upload_ok && file_exists($destination)) {
                  unlink($destination);
              }
             $error_message = "Lỗi khi thêm bài viết vào CSDL: " . mysqli_error($conn);
         }
     } // Đóng if (!isset($error_message))
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once("header1.php"); ?>
    <title>Thêm Bài Viết Mới</title>
    <?php include_once('header2.php'); ?>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script type="text/javascript" src="../ckeditor/ckeditor.js"></script> <style>
        /* CSS cho container form */
        .form-container {
            max-width: 900px; /* Tăng độ rộng cho CKEditor */
            margin: 30px auto;
            padding: 30px;
            background-color: #ffffff; /* Nền trắng */
            border-radius: 8px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        /* CSS cho tiêu đề */
        h3.form-title {
            color: #0d6efd; /* Màu xanh dương primary của Bootstrap */
            text-align: center;
            margin-bottom: 2rem;
            font-weight: bold;
        }
        /* Căn giữa nút */
        .button-group {
            text-align: center;
            margin-top: 25px;
        }
         .button-group .btn {
             margin: 0 10px;
             padding: 10px 25px;
         }
         .required-mark { color: red; margin-left: 2px; font-weight: bold;}
         /* Bỏ CSS nút cũ */
         /* Đảm bảo CKEditor hiển thị tốt */
         .ck-editor__editable { min-height: 150px; }

    </style>
</header>
<body>
<?php include_once('header3.php'); ?>

<div class="container mt-4">
    <div class="form-container">
        <h3 class="form-title">THÊM BÀI VIẾT MỚI</h3>

        <?php
        // Hiển thị lỗi nếu có
        if (!empty($error_message)) {
            echo "<div class='alert alert-danger text-center' role='alert'><strong>Lỗi:</strong> " . htmlspecialchars($error_message) . "</div>";
        }
        ?>

        <form method="post" action="them_baiviet.php" name="ThemBaiViet" enctype="multipart/form-data">

            <div class="mb-3">
                <label for="idSP" class="form-label"><strong>Sản phẩm liên quan:<span class="required-mark">*</span></strong></label>
                <select class="form-select" name="idSP" id="idSP" required>
                    <option value="" disabled selected>-- Chọn sản phẩm --</option>
                    <?php
                    $sl_sanpham = "SELECT idSP, TenSP FROM sanpham ORDER BY TenSP ASC";
                    $rs_sanpham = mysqli_query($conn, $sl_sanpham);
                    if (!$rs_sanpham) {
                        echo "<option value='' disabled>Lỗi tải danh sách sản phẩm</option>";
                    } else {
                        while ($r = $rs_sanpham->fetch_assoc()) {
                            // Giữ lại lựa chọn nếu form lỗi
                            $selected = (isset($_POST['idSP']) && $_POST['idSP'] == $r['idSP']) ? 'selected' : '';
                            echo "<option value='{$r['idSP']}' {$selected}>" . htmlspecialchars($r['TenSP']) . "</option>";
                        }
                        mysqli_free_result($rs_sanpham);
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="TieuDe" class="form-label"><strong>Tiêu đề bài viết:<span class="required-mark">*</span></strong></label>
                <input type="text" class="form-control" id="TieuDe" name="TieuDe" required value="<?php echo isset($_POST['TieuDe']) ? htmlspecialchars($_POST['TieuDe']) : ''; ?>">
            </div>

            <div class="mb-3">
                <label for="file" class="form-label"><strong>Hình đại diện:</strong></label>
                <input class="form-control" type="file" id="file" name="file" accept="image/jpeg, image/png, image/gif, image/webp"> <div class="form-text">Chọn ảnh đại diện cho bài viết (nếu có). Định dạng: JPG, PNG, GIF, WEBP. Kích thước tối đa: 5MB.</div>
                </div>

            <div class="mb-3">
                <label for="MoTa" class="form-label"><strong>Mô tả ngắn:<span class="required-mark">*</span></strong></label>
                <textarea class="form-control" name="MoTa" id="MoTa" rows="5"><?php echo isset($_POST['MoTa']) ? htmlspecialchars($_POST['MoTa']) : ''; ?></textarea>
                <script type="text/javascript">
                    CKEDITOR.replace( 'MoTa' ); // Áp dụng CKEditor
                </script>
            </div>

            <div class="mb-3">
                 <label for="NoiDung" class="form-label"><strong>Nội dung chi tiết:<span class="required-mark">*</span></strong></label>
                 <textarea class="form-control" name="NoiDung" id="NoiDung" rows="10"><?php echo isset($_POST['NoiDung']) ? htmlspecialchars($_POST['NoiDung']) : ''; ?></textarea>
                 <script type="text/javascript">
                     CKEDITOR.replace( 'NoiDung' ); // Áp dụng CKEditor
                 </script>
            </div>

            <div class="mb-3">
                 <label for="NgayCapNhatView" class="form-label"><strong>Ngày tạo/cập nhật:</strong></label>
                 <input type="text" class="form-control" id="NgayCapNhatView" name="NgayCapNhatView" readonly value="<?php echo date("d/m/Y H:i:s"); ?>">
             </div>


            <div class="mb-3">
                 <label for="AnHien" class="form-label"><strong>Trạng thái:</strong></label>
                 <select class="form-select" name="AnHien" id="AnHien">
                     <option value="1" <?php echo (isset($_POST['AnHien']) && $_POST['AnHien'] == 1) ? 'selected' : ''; ?>>Hiện</option>
                     <option value="0" <?php echo (isset($_POST['AnHien']) && $_POST['AnHien'] == 0) ? 'selected' : ''; ?>>Ẩn</option>
                 </select>
            </div>

             <div class="button-group">
                <button type="submit" name="Ok" class="btn btn-success btn-lg">
                    <i class="fas fa-save"></i> Lưu Bài Viết
                </button>
                <button type="button" name="Huy" value="Hủy" class="btn btn-secondary btn-lg" onclick="getConfirmation();">
                    <i class="fas fa-times"></i> Hủy
                </button>
            </div>

        </form>
    </div> </div> <script type="text/javascript">
    function getConfirmation(){
        var retVal = confirm("Bạn có chắc chắn muốn hủy và quay lại danh sách?");
        if( retVal == true ){
            // Chuyển hướng về trang danh sách thay vì history.back()
            window.location.href = 'baiviet.php';
        }
    }
</script>

<?php include_once('footer.php'); ?>
</body>
</html>