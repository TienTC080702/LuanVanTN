<?php
include_once('../connection/connect_database.php'); // Đảm bảo đường dẫn đúng

$error_message = null; // Biến lưu lỗi

// Phần xử lý PHP khi submit form (GIỮ NGUYÊN LOGIC GỐC CỦA BẠN)
// Lưu ý: Logic này có nhiều vấn đề về bảo mật (SQL Injection),
// kiểm tra file (size quá nhỏ, dùng tên gốc), và logic thông báo lỗi.
if (isset($_POST['Them'])) {
    $hinh = null; // Khởi tạo biến hình ảnh
    $upload_success = false; // Cờ kiểm tra upload thành công

    // Xử lý file upload trước
    if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK && !empty($_FILES['file']['name'])) {
         // *** CẢNH BÁO: SIZE 2500 bytes LÀ QUÁ NHỎ CHO ẢNH! Nên tăng lên (vd: 5 * 1024 * 1024 cho 5MB) ***
        $allowed_types = ["image/gif", "image/jpeg", "image/jpg", "image/png", "image/webp"]; // Thêm webp
        $max_size = 5 * 1024 * 1024; // Ví dụ: 5MB

        if (in_array($_FILES['file']['type'], $allowed_types) && $_FILES['file']['size'] <= $max_size) {
            // *** CẢNH BÁO: Dùng tên file gốc có thể gây ghi đè và bảo mật. Nên tạo tên duy nhất. ***
            // *** Thư mục lưu ảnh nên là thư mục dành riêng cho khuyến mãi ***
            $path_image = "../images/khuyenmai/" . basename($_FILES['file']['name']); // Dùng basename() và thư mục khuyenmai
            if (!file_exists('../images/khuyenmai/')) {
                 mkdir('../images/khuyenmai/', 0777, true);
            }

            // Kiểm tra trùng tên (logic gốc) - nên kiểm tra trước khi move
             if (file_exists($path_image)) {
                  $error_message = 'Tên file ảnh đã tồn tại!';
             } else {
                 if (move_uploaded_file($_FILES['file']['tmp_name'], $path_image)) {
                     $hinh = basename($_FILES['file']['name']); // Lấy tên file sau khi upload thành công
                     $upload_success = true;
                 } else {
                     $error_message = 'Lỗi khi tải file lên server!';
                 }
             }
        } else {
             if($_FILES['file']['size'] > $max_size) {
                $error_message = 'Kích thước file quá lớn (tối đa 5MB)!';
             } else {
                $error_message = 'Loại file không hợp lệ (chỉ cho phép JPG, PNG, GIF, WEBP)!';
             }
        }
    } elseif(isset($_FILES['file']) && $_FILES['file']['error'] != UPLOAD_ERR_NO_FILE && $_FILES['file']['error'] != UPLOAD_ERR_OK) {
         $error_message = 'Có lỗi xảy ra trong quá trình upload file: ' . $_FILES['file']['error'];
    }
    // Bỏ else báo tên file không hợp lệ vì có thể người dùng không upload file

    // Chỉ INSERT nếu không có lỗi upload
    if ($error_message === null) {
        // *** CẢNH BÁO: SQL Injection và dùng sai tên cột MotaKM? ***
        // Logic INSERT gốc (sử dụng tên cột `MotaKM` thay vì `Mota`? kiểm tra lại CSDL)
        $mota_km_clean = isset($_POST['MotaKM']) ? mysqli_real_escape_string($conn, $_POST['MotaKM']) : ''; // Sửa thành MotaKM nếu đúng
        $an_hien_clean = isset($_POST['AnHien']) ? intval($_POST['AnHien']) : 1;
        $hinh_sql = ($hinh === null) ? 'NULL' : "'" . mysqli_real_escape_string($conn, $hinh) . "'"; // Xử lý NULL nếu không có hình

        $sl_km = "INSERT INTO khuyenmai(MotaKM, urlHinh, AnHien) VALUES ('$mota_km_clean', $hinh_sql, $an_hien_clean)";
        $kq_km = mysqli_query($conn, $sl_km);

        if ($kq_km) {
            echo "<script language='javascript'>alert('Thêm khuyến mãi thành công!');";
            echo "location.href='KhuyenMai.php';</script>"; // Chuyển về trang danh sách KM
            exit();
        } else {
             // Nếu INSERT lỗi mà đã upload ảnh, nên xóa ảnh
             if ($upload_success && file_exists($path_image)) {
                 unlink($path_image);
             }
             $error_message = "Thêm không thành công! Lỗi: " . mysqli_error($conn);
             // echo "<script language='javascript'>alert('Thêm không thành công!');</script>"; // Bỏ alert
        }
    }
    // Hiển thị lỗi nếu có
    if ($error_message !== null) {
         echo "<script language='javascript'>alert('" . addslashes($error_message) . "');</script>";
    }

} // Đóng if submit
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once("header1.php"); ?>
    <title>Thêm Khuyến Mãi</title>
    <?php include_once('header2.php'); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* CSS cho container form */
        .form-container {
            max-width: 750px;
            margin: 30px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 8px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        /* CSS cho tiêu đề */
        h3.form-title {
            color: #198754; /* Màu xanh lá */
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
    </style>
</head>
<body>
<?php include_once('header3.php'); ?>

<div class="container mt-4">
    <div class="form-container">
        <h3 class="form-title">THÊM CHƯƠNG TRÌNH KHUYẾN MÃI</h3>

        <?php
        // Hiển thị lỗi nếu có từ PHP xử lý form (trước đó đã alert, có thể bỏ)
        // if (!empty($error_message)) {
        //    echo "<div class='alert alert-danger text-center' role='alert'><strong>Lỗi:</strong> " . htmlspecialchars($error_message) . "</div>";
        // }
        ?>

        <form method="post" action="them_km.php" name="them_km" enctype="multipart/form-data"> <div class="mb-3">
                <label for="MotaKM" class="form-label"><strong>Mô tả khuyến mãi:<span class="required-mark">*</span></strong></label>
                <textarea class="form-control" id="MotaKM" name="MotaKM" rows="4" required placeholder="Nhập nội dung mô tả chi tiết chương trình khuyến mãi..."><?php echo isset($_POST['MotaKM']) ? htmlspecialchars($_POST['MotaKM']) : ''; ?></textarea>
            </div>

            <div class="mb-3">
                <label for="file" class="form-label"><strong>Ảnh đại diện:<span class="required-mark">*</span></strong></label>
                <input class="form-control" type="file" id="file" name="file" required accept="image/jpeg, image/png, image/gif, image/webp">
                 <div class="form-text">Chọn ảnh minh họa cho khuyến mãi (JPG, PNG, GIF, WEBP, tối đa 5MB).</div>
            </div>

            <div class="mb-3">
                 <label for="AnHien" class="form-label"><strong>Trạng thái:</strong></label>
                 <select class="form-select" name="AnHien" id="AnHien">
                     <option value="1" <?php echo (!isset($_POST['AnHien']) || (isset($_POST['AnHien']) && $_POST['AnHien'] == 1)) ? 'selected' : ''; ?>>Hiện</option>
                     <option value="0" <?php echo (isset($_POST['AnHien']) && $_POST['AnHien'] == 0) ? 'selected' : ''; ?>>Ẩn</option>
                 </select>
                  <div class="form-text">Chọn "Hiện" để khuyến mãi được áp dụng/hiển thị.</div>
            </div>

             <div class="button-group">
                 <button name="Them" type="submit" class="btn btn-success btn-lg">
                     <i class="fas fa-plus-circle"></i> Thêm KM
                </button>
                 <a href="KhuyenMai.php" class="btn btn-secondary btn-lg"> <i class="fas fa-times"></i> Thoát
                </a>
            </div>

        </form>
    </div> </div> <?php include_once('footer.php'); ?>
</body>
</html>