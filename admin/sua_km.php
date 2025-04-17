<?php
include_once('../connection/connect_database.php'); // Đảm bảo đường dẫn đúng

$km_data = null; // Biến lưu dữ liệu KM
$error_load = null; // Lỗi tải dữ liệu
$error_message = ""; // Thông báo lỗi xử lý form

// --- LẤY DỮ LIỆU KHUYẾN MÃI CẦN SỬA ---
if (!isset($_GET['idKM']) || !is_numeric($_GET['idKM'])) {
    die("Lỗi: ID Khuyến mãi không hợp lệ.");
}
$idKM_get = intval($_GET['idKM']);

$sl_km_select = "SELECT * FROM khuyenmai WHERE idKM=" . $idKM_get;
$kq_km_select = mysqli_query($conn, $sl_km_select);

if (!$kq_km_select) {
    $error_load = "Lỗi truy vấn khuyến mãi: " . mysqli_error($conn);
} else {
    // Dùng fetch_assoc
    $km_data = mysqli_fetch_assoc($kq_km_select);
    if (!$km_data) {
        $error_load = "Không tìm thấy khuyến mãi với ID=" . $idKM_get;
    }
    mysqli_free_result($kq_km_select);
}


// --- XỬ LÝ KHI SUBMIT FORM (LOGIC GỐC CỦA BẠN - GIỮ NGUYÊN) ---
// Lưu ý: Logic xử lý file và update này có nhiều vấn đề cần cải thiện
if (isset($_POST['Sua'])) {
    $hinh = $km_data['urlHinh']; // Giữ lại hình cũ làm mặc định
    $upload_error = false; // Cờ lỗi upload

    // Xử lý file upload nếu người dùng chọn file mới
    if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK && !empty($_FILES['file']['name'])) {
        // *** CẢNH BÁO: Size 2500 bytes LÀ QUÁ NHỎ CHO ẢNH! ***
        $allowed_types = ["image/gif", "image/jpeg", "image/jpg", "image/png", "image/webp"];
        $max_size = 5 * 1024 * 1024; // Ví dụ 5MB

        if (in_array($_FILES['file']['type'], $allowed_types) && $_FILES['file']['size'] <= $max_size) {
             // *** CẢNH BÁO: Nên tạo tên file duy nhất và dùng thư mục riêng ***
             $path_image = "../images/khuyenmai/" . basename($_FILES['file']['name']); // Dùng thư mục khuyenmai
             if (!file_exists('../images/khuyenmai/')) { mkdir('../images/khuyenmai/', 0777, true); }

             // Kiểm tra trùng tên file (nên kiểm tra trước move)
             if (file_exists($path_image)) {
                  echo "<script>alert('Tên file ảnh mới đã tồn tại!');</script>";
                  $upload_error = true; // Đánh dấu lỗi
             } else {
                  if (move_uploaded_file($_FILES['file']['tmp_name'], $path_image)) {
                      $hinh = basename($_FILES['file']['name']); // Lấy tên file mới nếu upload thành công
                      // Xóa ảnh cũ nếu upload thành công và ảnh cũ tồn tại
                       if (!empty($km_data['urlHinh']) && file_exists('../images/khuyenmai/' . $km_data['urlHinh'])) {
                           unlink('../images/khuyenmai/' . $km_data['urlHinh']);
                       }
                  } else {
                      echo "<script>alert('Lỗi khi tải file mới lên server!');</script>";
                      $upload_error = true;
                  }
             }
        } else {
             echo "<script>alert('Loại file không hợp lệ hoặc kích thước quá lớn!');</script>";
             $upload_error = true;
        }
    } elseif(isset($_FILES['file']) && $_FILES['file']['error'] != UPLOAD_ERR_NO_FILE && $_FILES['file']['error'] != UPLOAD_ERR_OK) {
         echo "<script>alert('Có lỗi xảy ra trong quá trình upload file: " . $_FILES['file']['error'] . "');</script>";
         $upload_error = true;
    }
    // Bỏ else báo tên file không hợp lệ khi không chọn file

    // Chỉ thực hiện UPDATE nếu không có lỗi upload
    if (!$upload_error) {
        // *** CẢNH BÁO: SQL Injection ***
        // Lấy các giá trị khác từ form (nên dùng mysqli_real_escape_string)
        $mota_km_clean = isset($_POST['MotaKM']) ? mysqli_real_escape_string($conn, $_POST['MotaKM']) : '';
        $an_hien_clean = isset($_POST['AnHien']) ? intval($_POST['AnHien']) : 0;
        $hinh_sql = ($hinh === null) ? 'NULL' : "'" . mysqli_real_escape_string($conn, $hinh) . "'"; // Tên file ảnh (có thể là cũ hoặc mới)

        // Câu lệnh UPDATE gốc của bạn (đã sửa tên biến $hinh)
         $sl_km_update = "UPDATE khuyenmai SET
                             MotaKM = '$mota_km_clean',
                             urlHinh = $hinh_sql,
                             AnHien = $an_hien_clean
                          WHERE idKM = $idKM_get";

         $kq_km_update = mysqli_query($conn, $sl_km_update);
         if ($kq_km_update) {
             echo "<script language='javascript'>alert('Sửa khuyến mãi thành công!');";
             echo "location.href='KhuyenMai.php';</script>"; // Chuyển về trang danh sách KM
             exit();
         } else {
              // echo "<script language='javascript'>alert('Sửa không thành công!');</script>"; // Hiển thị lỗi cụ thể hơn nếu muốn
              $error_message = "Lỗi khi cập nhật khuyến mãi: " . mysqli_error($conn);
         }
    } // Đóng if (!$upload_error)
} // Đóng if submit
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once("header1.php"); ?>
    <title>Sửa Khuyến Mãi</title>
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
            color: #ffc107; /* Màu vàng warning */
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
         /* Ảnh hiện tại */
         .current-image {
             max-width: 200px; /* Tăng kích thước xem trước */
             max-height: 150px;
             display: block;
             margin-bottom: 10px;
             border: 1px solid #eee;
             padding: 3px;
         }
          /* Bỏ CSS nút cũ */
    </style>
</head>
<body>
<?php include_once('header3.php'); ?>

<div class="container mt-4">
    <div class="form-container">
        <h3 class="form-title">SỬA THÔNG TIN KHUYẾN MÃI</h3>

        <?php
        // Hiển thị lỗi tải dữ liệu hoặc lỗi xử lý form
        if (!empty($error_load)) {
            echo "<div class='alert alert-danger text-center'><strong>Lỗi tải dữ liệu:</strong> " . htmlspecialchars($error_load) . "</div>";
        } elseif (!empty($error_message)) {
             // Hiển thị lỗi xử lý từ PHP nếu có
            echo "<div class='alert alert-danger text-center'><strong>Lỗi xử lý:</strong> " . htmlspecialchars($error_message) . "</div>";
        }

        // Chỉ hiển thị form nếu tải dữ liệu thành công
        if ($km_data):
        ?>

        <form method="post" action="sua_km.php?idKM=<?php echo $idKM_get; ?>" name="sua_km" enctype="multipart/form-data">

            <div class="mb-3">
                <label for="MotaKM" class="form-label"><strong>Mô tả khuyến mãi:<span class="required-mark">*</span></strong></label>
                 <textarea class="form-control" id="MotaKM" name="MotaKM" rows="4" required><?php echo htmlspecialchars($km_data['MotaKM']); ?></textarea>
            </div>

            <div class="mb-3">
                <label for="file" class="form-label"><strong>Ảnh đại diện:</strong></label>
                 <div>
                     <?php if (!empty($km_data['urlHinh'])): ?>
                         <label>Hình hiện tại:</label><br>
                         <img src="../images/khuyenmai/<?php echo htmlspecialchars($km_data['urlHinh']); ?>" alt="Ảnh hiện tại" class="current-image img-thumbnail">
                     <?php else: ?>
                         <p class="text-muted"><em>Chưa có ảnh đại diện.</em></p>
                     <?php endif; ?>
                 </div>
                 <label for="file" class="form-label mt-2">Chọn ảnh mới (để trống nếu không muốn thay đổi):</label>
                <input class="form-control" type="file" id="file" name="file" accept="image/jpeg, image/png, image/gif, image/webp">
                 <div class="form-text">Định dạng: JPG, PNG, GIF, WEBP. Tối đa 5MB.</div>
                 </div>

            <div class="mb-3">
                 <label for="AnHien" class="form-label"><strong>Trạng thái:</strong></label>
                 <select class="form-select" name="AnHien" id="AnHien">
                     <option value="1" <?php if($km_data['AnHien'] == 1) echo "selected"; ?>>Hiện</option>
                     <option value="0" <?php if($km_data['AnHien'] == 0) echo "selected"; ?>>Ẩn</option>
                 </select>
            </div>

             <div class="button-group">
                 <button name="Sua" type="submit" class="btn btn-primary btn-lg">
                     <i class="fas fa-save"></i> Lưu Thay Đổi
                </button>
                 <a href="KhuyenMai.php" class="btn btn-secondary btn-lg"> <i class="fas fa-arrow-left"></i> Thoát
                </a>
            </div>

        </form>
        <?php
        endif; // Đóng if ($km_data)
        ?>

    </div> </div> <?php include_once('footer.php'); ?>
</body>
</html>