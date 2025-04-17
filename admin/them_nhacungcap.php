<?php
// Bao gồm file kết nối và kiểm tra session admin nếu cần
// include_once('session_check_admin.php'); // Ví dụ kiểm tra session
include_once('../connection/connect_database.php'); // Đảm bảo đường dẫn đúng

$error_message = null;
$success_message = null;

// Xử lý khi form được gửi đi (nhấn nút Lưu Nhà Cung Cấp)
if (isset($_POST['submit'])) {
    // Lấy dữ liệu từ form và làm sạch cơ bản
    // trim() để loại bỏ khoảng trắng thừa, mysqli_real_escape_string để chống SQL injection cơ bản
    $tenNCC = isset($_POST['TenNCC']) ? trim(mysqli_real_escape_string($conn, $_POST['TenNCC'])) : '';
    $diaChi = isset($_POST['DiaChiNCC']) ? trim(mysqli_real_escape_string($conn, $_POST['DiaChiNCC'])) : null;
    $dienThoai = isset($_POST['DienThoaiNCC']) ? trim(mysqli_real_escape_string($conn, $_POST['DienThoaiNCC'])) : null;
    $email = isset($_POST['EmailNCC']) ? trim(mysqli_real_escape_string($conn, $_POST['EmailNCC'])) : null;
    $ghiChu = isset($_POST['GhiChuNCC']) ? trim(mysqli_real_escape_string($conn, $_POST['GhiChuNCC'])) : null;

    // --- Kiểm tra dữ liệu bắt buộc ---
    if (!empty($tenNCC)) { // Tên NCC là bắt buộc

        // --- (Tùy chọn) Kiểm tra xem tên NCC đã tồn tại chưa ---
        // $check_sql = "SELECT idNCC FROM nhacungcap WHERE TenNCC = '$tenNCC'";
        // $check_result = mysqli_query($conn, $check_sql);
        // if ($check_result && mysqli_num_rows($check_result) > 0) {
        //     $error_message = "Tên nhà cung cấp này đã tồn tại trong hệ thống.";
        // } else {
            // --- Xử lý giá trị NULL cho các cột không bắt buộc trước khi INSERT ---
            // Nếu giá trị là null hoặc rỗng, chèn NULL vào DB, ngược lại chèn chuỗi đã escape
            $diaChi_sql = ($diaChi === null || $diaChi === '') ? 'NULL' : "'$diaChi'";
            $dienThoai_sql = ($dienThoai === null || $dienThoai === '') ? 'NULL' : "'$dienThoai'";
            $email_sql = ($email === null || $email === '') ? 'NULL' : "'$email'"; // Cần kiểm tra định dạng email hợp lệ nếu muốn
            $ghiChu_sql = ($ghiChu === null || $ghiChu === '') ? 'NULL' : "'$ghiChu'";

            // --- Câu lệnh INSERT ---
            $sql_insert = "INSERT INTO nhacungcap (TenNCC, DiaChiNCC, DienThoaiNCC, EmailNCC, GhiChu)
                           VALUES ('$tenNCC', $diaChi_sql, $dienThoai_sql, $email_sql, $ghiChu_sql)";

            // Thực thi INSERT
            if (mysqli_query($conn, $sql_insert)) {
                $success_message = "Đã thêm nhà cung cấp '<strong>" . htmlspecialchars($tenNCC) . "</strong>' thành công!";
                // Xóa giá trị đã nhập khỏi biến $_POST để làm trống form
                $_POST = array();
                // Bạn có thể thêm chuyển hướng về trang danh sách tại đây nếu muốn
                // echo "<script>alert('Thêm thành công!'); window.location='ds_nhacungcap.php';</script>";
                // exit();
            } else {
                // Hiển thị lỗi SQL chi tiết (chỉ nên dùng khi đang phát triển)
                // $error_message = "Lỗi khi thêm nhà cung cấp: " . mysqli_error($conn);
                // Hiển thị lỗi chung chung hơn
                 $error_message = "Đã xảy ra lỗi khi thêm nhà cung cấp vào cơ sở dữ liệu. Vui lòng thử lại.";
            }
        // } // Đóng else của check trùng tên (nếu có dùng)
    } else {
        // Thông báo lỗi nếu thiếu tên NCC
        $error_message = "Tên nhà cung cấp là thông tin bắt buộc, không được để trống.";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Nhà Cung Cấp Mới</title>
    <?php include_once("header1.php"); ?> <?php include_once('header2.php'); ?> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
         /* CSS cơ bản cho form */
         .form-container {
             max-width: 750px; /* Độ rộng tối đa của form */
             margin: 30px auto; /* Căn giữa form */
             padding: 30px;
             background-color: #ffffff; /* Nền trắng */
             border-radius: 8px;
             box-shadow: 0 2px 5px rgba(0,0,0,0.1);
             border: 1px solid #e0e0e0;
         }
         h3.form-title {
             color: #28a745; /* Màu xanh lá */
             text-align: center;
             margin-bottom: 1.5rem;
             font-weight: bold;
         }
         .button-group {
             text-align: center;
             margin-top: 25px;
         }
         .button-group .btn {
             margin: 0 8px; /* Khoảng cách giữa nút */
             padding: 10px 25px; /* Kích thước nút */
          }
         .required-mark { /* Dấu * màu đỏ cho trường bắt buộc */
             color: red;
             margin-left: 2px;
         }
    </style>
</head>
<body>
<?php include_once('header3.php'); ?> <div class="container mt-4"> <div class="form-container"> <h3 class="form-title">THÊM NHÀ CUNG CẤP MỚI</h3>

    <?php if(!empty($success_message)): ?>
        <div class="alert alert-success text-center"><?php echo $success_message; ?></div>
    <?php endif; ?>
    <?php if(!empty($error_message)): ?>
        <div class="alert alert-danger text-center"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form method="post" action="them_nhacungcap.php"> <div class="mb-3">
            <label for="TenNCC" class="form-label"><strong>Tên Nhà Cung Cấp <span class="required-mark">*</span></strong></label>
            <input type="text" class="form-control" id="TenNCC" name="TenNCC" required value="<?php echo isset($_POST['TenNCC']) ? htmlspecialchars($_POST['TenNCC']) : ''; ?>" placeholder="Nhập tên đầy đủ của nhà cung cấp">
        </div>
        <div class="mb-3">
            <label for="DiaChiNCC" class="form-label"><strong>Địa Chỉ</strong></label>
            <textarea class="form-control" id="DiaChiNCC" name="DiaChiNCC" rows="3" placeholder="Ví dụ: 123 Đường ABC, Quận XYZ, Thành phố HCM"><?php echo isset($_POST['DiaChiNCC']) ? htmlspecialchars($_POST['DiaChiNCC']) : ''; ?></textarea>
        </div>
         <div class="mb-3">
            <label for="DienThoaiNCC" class="form-label"><strong>Điện Thoại</strong></label>
            <input type="tel" class="form-control" id="DienThoaiNCC" name="DienThoaiNCC" value="<?php echo isset($_POST['DienThoaiNCC']) ? htmlspecialchars($_POST['DienThoaiNCC']) : ''; ?>" placeholder="Số điện thoại liên hệ">
        </div>
         <div class="mb-3">
            <label for="EmailNCC" class="form-label"><strong>Email</strong></label>
            <input type="email" class="form-control" id="EmailNCC" name="EmailNCC" value="<?php echo isset($_POST['EmailNCC']) ? htmlspecialchars($_POST['EmailNCC']) : ''; ?>" placeholder="Địa chỉ email (nếu có)">
        </div>
         <div class="mb-3">
            <label for="GhiChuNCC" class="form-label"><strong>Ghi Chú</strong></label>
            <textarea class="form-control" id="GhiChuNCC" name="GhiChuNCC" rows="2" placeholder="Thông tin thêm về nhà cung cấp (nếu có)"><?php echo isset($_POST['GhiChuNCC']) ? htmlspecialchars($_POST['GhiChuNCC']) : ''; ?></textarea>
        </div>
        <div class="button-group">
            <button type="submit" name="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Lưu Nhà Cung Cấp
            </button>
            <a href="ds_nhacungcap.php" class="btn btn-secondary">
               <i class="fas fa-arrow-left"></i> Quay Lại Danh Sách
            </a>
        </div>
    </form>
 </div> </div> <?php include_once('footer.php'); ?>
</body>
</html>