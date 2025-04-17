<?php
// Đặt include và lấy dữ liệu ở đầu
include_once('../connection/connect_database.php');

$baiviet_data = null; // Khởi tạo biến chứa dữ liệu bài viết
$error_load = null; // Biến lỗi khi tải dữ liệu

if (!isset($_GET['idBV']) || !is_numeric($_GET['idBV'])) {
    die("Lỗi: ID Bài viết không hợp lệ."); // Nên dừng hẳn nếu ID sai
}
$idBV_get = intval($_GET['idBV']);

// Lấy dữ liệu bài viết cần sửa
$sl_baiviet = "SELECT * FROM baiviet WHERE idBV =" . $idBV_get;
$rs_baiviet = mysqli_query($conn, $sl_baiviet);

if (!$rs_baiviet) {
    $error_load = "Lỗi truy vấn bài viết: " . mysqli_error($conn);
} else {
    $baiviet_data = mysqli_fetch_assoc($rs_baiviet); // Dùng fetch_assoc
    if (!$baiviet_data) {
        $error_load = "Không tìm thấy bài viết với ID=" . $idBV_get;
    }
}
// Không giải phóng $rs_baiviet vội nếu dùng $baiviet_data['img'] ở dưới

// Phần xử lý PHP khi submit form (GIỮ NGUYÊN LOGIC GỐC CỦA BẠN)
// Chỉ đặt vào đây để code hoàn chỉnh, bạn giữ nguyên logic xử lý update/delete của bạn
$error_message = null; // Biến lưu lỗi xử lý form
$ngaycapnhat = date('Y-m-d H:i:s');
$anhcu = $baiviet_data['img'] ?? null; // Lấy ảnh cũ từ dữ liệu đã fetch

// Xử lý Cập nhật
if (isset($_POST['update']) && isset($_POST['TieuDe']) && isset($_POST['NoiDung']) && isset($_POST['idSP']) && $baiviet_data) { // Thêm kiểm tra $baiviet_data tồn tại
    $idSP_post = intval($_POST['idSP']);
    $tieuDe_post = mysqli_real_escape_string($conn, $_POST['TieuDe']);
    $moTa_post = mysqli_real_escape_string($conn, $_POST['MoTa']);
    $noiDung_post = mysqli_real_escape_string($conn, $_POST['NoiDung']);
    $anHien_post = intval($_POST['AnHien']);
    $new_img_name = null;
    $upload_proceed = false;

    // Xử lý upload ảnh mới nếu có
    if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK && $_FILES['file']['size'] > 0) {
        $path = '../images/baiviet/'; // Thư mục lưu ảnh bài viết
         if (!file_exists($path)) { mkdir($path, 0777, true); }

        $tmp_name = $_FILES['file']['tmp_name'];
        $original_name = $_FILES['file']['name'];
        $file_extension = pathinfo($original_name, PATHINFO_EXTENSION);
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array(strtolower($file_extension), $allowed_extensions)) {
             $error_message = "Lỗi: Chỉ cho phép tải lên file ảnh (jpg, jpeg, png, gif, webp).";
        } else {
             $new_img_name = 'bv_' . $idBV_get . '_' . time() . '.' . $file_extension; // Tên file mới theo ID bài viết và timestamp
             $destination = $path . $new_img_name;
             $max_size = 5 * 1024 * 1024; // 5MB

            if ($_FILES['file']['size'] > $max_size) {
                 $error_message = "Lỗi: Kích thước file ảnh quá lớn (tối đa 5MB).";
            } else {
                 // Chỉ di chuyển nếu các kiểm tra khác OK
                 $upload_proceed = true;
            }
        }
    } elseif (isset($_FILES['file']) && $_FILES['file']['error'] != UPLOAD_ERR_NO_FILE && $_FILES['file']['error'] != UPLOAD_ERR_OK) {
         $error_message = "Lỗi tải file lên: Mã lỗi " . $_FILES['file']['error'];
    }

    // Nếu không có lỗi upload hoặc không upload ảnh mới
    if (!isset($error_message)) {
         $sql_update = "UPDATE baiviet SET
                           idSP = $idSP_post,
                           TieuDe = '$tieuDe_post',
                           NoiDung = '$noiDung_post',
                           NgayCapNhat = '$ngaycapnhat',
                           AnHien = $anHien_post,
                           MoTa = '$moTa_post'";

         // Chỉ cập nhật cột img nếu có upload ảnh mới thành công
         if ($upload_proceed && $new_img_name) {
             if (move_uploaded_file($tmp_name, $destination)) {
                 $sql_update .= ", img = '$new_img_name'"; // Thêm cập nhật ảnh
                 // Xóa ảnh cũ nếu upload ảnh mới thành công và ảnh cũ tồn tại
                  if ($anhcu && file_exists($path . $anhcu)) {
                      unlink($path . $anhcu);
                  }
             } else {
                 $error_message = "Lỗi khi di chuyển ảnh mới.";
                 $upload_proceed = false; // Đánh dấu upload thất bại
             }
         }

         // Hoàn thành câu lệnh UPDATE và thực thi
         $sql_update .= " WHERE idBV = $idBV_get";

         // Chỉ thực thi update nếu không có lỗi upload trước đó
         if (!isset($error_message)) {
             if (mysqli_query($conn, $sql_update)) {
                 echo "<script language='javascript'>alert('Cập nhật bài viết thành công!');";
                 echo "location.href = 'baiviet.php';</script>";
                 exit();
             } else {
                 $error_message = "Lỗi khi cập nhật bài viết: " . mysqli_error($conn);
                  // Nếu update lỗi mà đã upload ảnh thành công, nên xóa ảnh vừa up
                 if ($upload_proceed && file_exists($destination)) {
                     unlink($destination);
                 }
             }
         }
    }
}

// Xử lý Xóa
if (isset($_POST['delete']) && $baiviet_data) {
    $q_delete_bv = "DELETE FROM baiviet WHERE idBV =" . $idBV_get;
    if (mysqli_query($conn, $q_delete_bv)) {
        // Xóa ảnh liên quan nếu xóa bài viết thành công
        if ($anhcu && file_exists('../images/baiviet/' . $anhcu)) {
            unlink('../images/baiviet/' . $anhcu);
        }
        echo "<script language='javascript'>alert('Xóa bài viết thành công!');";
        echo "location.href = 'baiviet.php';</script>";
        exit();
    } else {
        $error_message = "Lỗi khi xóa bài viết: " . mysqli_error($conn);
    }
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once ("header1.php");?>
    <title>Cập nhật Bài viết</title>
    <?php include_once('header2.php');?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script type="text/javascript" src="../ckeditor/ckeditor.js"></script>
    <style>
         /* CSS cho container form */
        .form-container {
            max-width: 900px;
            margin: 30px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 8px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        /* CSS cho tiêu đề */
        h3.form-title {
            color: #ffc107; /* Màu vàng cảnh báo cho sửa/xóa */
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
         /* Hiển thị ảnh hiện tại */
         .current-image {
             max-width: 150px; /* Giới hạn chiều rộng ảnh */
             max-height: 150px;
             display: block; /* Để margin auto hoạt động nếu cần */
             margin-top: 5px;
             border: 1px solid #eee;
             padding: 3px;
             margin-bottom: 10px;
         }
          /* Bỏ CSS nút cũ */
    </style>
</head>
<body>
<?php include_once ('header3.php');?>

<div class="container mt-4">
    <div class="form-container">
        <h3 class="form-title">CẬP NHẬT BÀI VIẾT</h3>

        <?php
        // Hiển thị lỗi tải dữ liệu hoặc lỗi xử lý form
        if (!empty($error_load)) {
            echo "<div class='alert alert-danger text-center'><strong>Lỗi tải dữ liệu:</strong> " . htmlspecialchars($error_load) . "</div>";
        } elseif (!empty($error_message)) {
            echo "<div class='alert alert-danger text-center'><strong>Lỗi xử lý:</strong> " . htmlspecialchars($error_message) . "</div>";
        }

        // Chỉ hiển thị form nếu tải dữ liệu thành công
        if ($baiviet_data):
        ?>

        <form method="post" action="sua_xoa_baiviet.php?idBV=<?php echo $idBV_get; ?>" name="CapNhatBaiViet" enctype="multipart/form-data">

            <div class="mb-3">
                <label for="idSP" class="form-label"><strong>Sản phẩm liên quan:<span class="required-mark">*</span></strong></label>
                <select class="form-select" name="idSP" id="idSP" required>
                    <option value="" disabled>-- Chọn sản phẩm --</option>
                    <?php
                    $sl_sanpham = "select idSP, TenSP from sanpham ORDER BY TenSP ASC";
                    $rs_sanpham = mysqli_query($conn, $sl_sanpham);
                    if ($rs_sanpham) {
                        while ($r = $rs_sanpham->fetch_assoc()) {
                            // *** SỬA LOGIC SELECTED ***
                            $selected = ($r["idSP"] == $baiviet_data['idSP']) ? 'selected' : '';
                            echo "<option value='{$r['idSP']}' {$selected}>" . htmlspecialchars($r['TenSP']) . "</option>";
                        }
                        mysqli_free_result($rs_sanpham);
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="TieuDe" class="form-label"><strong>Tiêu đề bài viết:<span class="required-mark">*</span></strong></label>
                <input type="text" class="form-control" id="TieuDe" name="TieuDe" required value="<?php echo htmlspecialchars($baiviet_data['TieuDe']); ?>">
            </div>

             <div class="mb-3">
                 <label for="file" class="form-label"><strong>Hình đại diện:</strong></label>
                 <div>
                     <?php if (!empty($baiviet_data['img'])): ?>
                         <label>Hình hiện tại:</label><br>
                         <img src="../images/baiviet/<?php echo htmlspecialchars($baiviet_data['img']); ?>" alt="Hình hiện tại" class="current-image">
                         <input type="hidden" name="anh_cu" value="<?php echo htmlspecialchars($baiviet_data['img']); ?>"> <?php else: ?>
                         <span class="text-muted">Chưa có hình đại diện.</span><br>
                     <?php endif; ?>
                 </div>
                 <label for="file" class="form-label mt-2">Chọn ảnh mới (nếu muốn thay đổi):</label>
                 <input class="form-control" type="file" id="file" name="file" accept="image/jpeg, image/png, image/gif, image/webp">
                 <div class="form-text">Để trống nếu không muốn thay đổi ảnh. Định dạng: JPG, PNG, GIF, WEBP. Tối đa 5MB.</div>
            </div>


            <div class="mb-3">
                <label for="MoTa" class="form-label"><strong>Mô tả ngắn:<span class="required-mark">*</span></strong></label>
                <textarea class="form-control" name="MoTa" id="MoTa" rows="5"><?php echo htmlspecialchars($baiviet_data['MoTa']); ?></textarea>
                <script type="text/javascript"> CKEDITOR.replace('MoTa'); </script>
            </div>

            <div class="mb-3">
                 <label for="NoiDung" class="form-label"><strong>Nội dung chi tiết:<span class="required-mark">*</span></strong></label>
                 <textarea class="form-control" name="NoiDung" id="NoiDung" rows="10"><?php echo htmlspecialchars($baiviet_data['NoiDung']); ?></textarea>
                 <script type="text/javascript"> CKEDITOR.replace('NoiDung'); </script>
            </div>

            <div class="mb-3">
                 <label class="form-label"><strong>Ngày cập nhật lần cuối:</strong></label>
                 <p class="form-control-plaintext"><?php echo date('d/m/Y H:i:s', strtotime($baiviet_data['NgayCapNhat'])); ?></p>
                 </div>


            <div class="mb-3">
                 <label for="AnHien" class="form-label"><strong>Trạng thái:</strong></label>
                 <select class="form-select" name="AnHien" id="AnHien">
                     <option value="1" <?php if($baiviet_data['AnHien'] == 1) echo "selected"; ?>>Hiện</option>
                     <option value="0" <?php if($baiviet_data['AnHien'] == 0) echo "selected"; ?>>Ẩn</option>
                 </select>
            </div>

             <div class="button-group">
                <button type="submit" name="update" class="btn btn-primary btn-lg">
                     <i class="fas fa-save"></i> Cập Nhật
                </button>
                <button type="submit" name="delete" class="btn btn-danger btn-lg" onclick="return confirm('Bạn có chắc chắn muốn xóa bài viết này không? Hành động này không thể hoàn tác!');">
                     <i class="fas fa-trash"></i> Xóa
                </button>
                <button type="button" class="btn btn-secondary btn-lg" onclick="window.location.href='baiviet.php';"> <i class="fas fa-arrow-left"></i> Trở Về
                </button>
            </div>
        </form>

        <?php
        endif; // Đóng if ($baiviet_data)
        ?>

    </div> </div> <?php include_once ('footer.php');?>
</body>
</html>