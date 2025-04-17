<?php
// Đặt include và xử lý PHP ở đầu file
include_once('../connection/connect_database.php');

$error_message = null; // Biến lưu lỗi

// Phần xử lý PHP khi submit form (GIỮ NGUYÊN LOGIC GỐC CỦA BẠN)
if (isset($_POST['Them'])) {
    // Kiểm tra cơ bản các trường bắt buộc (nên dùng !empty(trim(...)) thay vì != "")
    if (isset($_POST['Username']) && $_POST['Username'] != "" &&
        isset($_POST['Password']) && $_POST['Password'] != "" &&
        isset($_POST['Password_1']) && $_POST['Password_1'] != "" &&
        isset($_POST['HoTenK']) && $_POST['HoTenK'] != "" && // Thêm kiểm tra HoTenK nếu bắt buộc
        isset($_POST['DienThoai']) && $_POST['DienThoai'] != "")
    {
        // --- Logic kiểm tra trùng lặp (giữ nguyên) ---
        $sql = "select HoTen, Email, DienThoai from Users"; // Chỉ select cột cần kiểm tra
        $query = mysqli_query($conn, $sql);
        $thongbao = "";
        if ($query) {
            while ($r = $query->fetch_assoc()) {
                if ($r['HoTen'] == $_POST['Username']) {
                     $thongbao .= 'Tên đăng nhập đã tồn tại. ';
                }
                if (!empty($_POST['Email']) && $r['Email'] == $_POST['Email']) { // Kiểm tra email không rỗng trước
                     $thongbao .= 'Email đã tồn tại. ';
                }
                if ($r['DienThoai'] == $_POST['DienThoai']) {
                    $thongbao .= 'Số điện thoại đã tồn tại. ';
                }
            }
             mysqli_free_result($query);
        } else {
            $thongbao .= 'Lỗi truy vấn kiểm tra dữ liệu. ';
        }
        // --- Logic kiểm tra mật khẩu trùng khớp (giữ nguyên) ---
        if (md5($_POST['Password']) != md5($_POST['Password_1'])) {
            $thongbao .= 'Mật khẩu không trùng khớp. ';
        }

        // --- Xử lý kết quả kiểm tra ---
        if (trim($thongbao) != "") {
            $error_message = trim($thongbao); // Gán vào biến lỗi
            // echo "<script language='javascript'>alert('$thongbao');</script>"; // Bỏ alert ở đây
        } else {
            // --- Logic INSERT (giữ nguyên) ---
            // Nên dùng mysqli_real_escape_string cho các giá trị chuỗi
            $username_clean = mysqli_real_escape_string($conn, $_POST['Username']);
            $hotenk_clean = mysqli_real_escape_string($conn, $_POST['HoTenK']);
            $password_hashed = md5($_POST['Password']); // Vẫn dùng md5 theo code gốc
            $diachi_clean = isset($_POST['DiaChi']) ? mysqli_real_escape_string($conn, $_POST['DiaChi']) : '';
            $dienthoai_clean = mysqli_real_escape_string($conn, $_POST['DienThoai']);
            $email_clean = isset($_POST['Email']) ? mysqli_real_escape_string($conn, $_POST['Email']) : '';
            $ngaydangky_clean = date("Y-m-d H:i:s"); // Lấy ngày giờ server

            $sl = "INSERT INTO Users(HoTen, HoTenK, Password, DiaChi, DienThoai, Email, NgayDangKy, idGroup)
                   VALUES ('$username_clean', '$hotenk_clean', '$password_hashed', '$diachi_clean', '$dienthoai_clean', '$email_clean', '$ngaydangky_clean', 2)";

            $kq = mysqli_query($conn, $sl);
            if ($kq) {
                echo "<script language='javascript'>alert('Thêm người dùng thành công!');";
                echo "location.href='index_user.php';</script>"; // Chuyển về trang danh sách user
                exit();
            } else {
                 $error_message = "Lỗi khi thêm người dùng vào CSDL: " . mysqli_error($conn);
                 // echo "<script language='JavaScript'> alert('Thêm không thành công!');</script>"; // Bỏ alert
            }
        }
    } else {
         $error_message = "Vui lòng nhập đầy đủ các trường bắt buộc (*).";
         // echo "<script language='javascript'>alert('Vui lòng nhập đầy đủ thông tin!');</script>"; // Bỏ alert
    }
} // Đóng if submit
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once("header1.php"); ?>
    <title>Thêm Người Dùng Mới</title> <?php include_once('header2.php'); ?>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* CSS cho container form */
        .form-container {
            max-width: 750px; /* Điều chỉnh độ rộng nếu cần */
            margin: 30px auto;
            padding: 30px;
            background-color: #ffffff; /* Nền trắng */
            border-radius: 8px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        /* CSS cho tiêu đề */
        h3.form-title {
            color: #198754; /* Màu xanh lá success của Bootstrap */
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
</header>
<body>
<?php include_once('header3.php'); ?>

<div class="container mt-4">
    <div class="form-container">
        <h3 class="form-title">THÊM NGƯỜI DÙNG MỚI</h3>

        <?php
        // Hiển thị lỗi nếu có
        if (!empty($error_message)) {
            echo "<div class='alert alert-danger text-center' role='alert'><strong>Lỗi:</strong> " . htmlspecialchars($error_message) . "</div>";
        }
        ?>

        <form method="post" action="them_user.php" name="ThemUser"> <div class="mb-3">
                <label for="Username" class="form-label"><strong>Tên đăng nhập:<span class="required-mark">*</span></strong></label>
                <input type="text" class="form-control" id="Username" name="Username" placeholder="ví dụ: nguyenvana" required
                       value="<?php echo isset($_POST['Username']) ? htmlspecialchars($_POST['Username']) : ''; ?>">
            </div>

            <div class="mb-3">
                <label for="HoTenK" class="form-label"><strong>Họ và Tên:<span class="required-mark">*</span></strong></label>
                <input type="text" class="form-control" id="HoTenK" name="HoTenK" placeholder="ví dụ: Nguyễn Văn A" required
                       value="<?php echo isset($_POST['HoTenK']) ? htmlspecialchars($_POST['HoTenK']) : ''; ?>">
            </div>

            <div class="mb-3">
                <label for="Password" class="form-label"><strong>Mật khẩu:<span class="required-mark">*</span></strong></label>
                <input type="password" class="form-control" id="Password" name="Password" placeholder="Nhập mật khẩu (ít nhất 8 ký tự nếu có ràng buộc)" required>
                 </div>

            <div class="mb-3">
                <label for="Password_1" class="form-label"><strong>Nhập lại Mật khẩu:<span class="required-mark">*</span></strong></label>
                <input type="password" class="form-control" id="Password_1" name="Password_1" placeholder="Nhập lại mật khẩu phía trên" required>
            </div>

            <div class="mb-3 form-check">
                 <input type="checkbox" class="form-check-input" id="showPasswordCheckbox" onclick="togglePasswords()">
                 <label class="form-check-label" for="showPasswordCheckbox">Hiển thị mật khẩu</label>
             </div>


            <div class="mb-3">
                <label for="DiaChi" class="form-label"><strong>Địa chỉ:</strong></label>
                <input type="text" class="form-control" id="DiaChi" name="DiaChi" placeholder="Ví dụ: 123 Đường ABC, Quận XYZ, TP HCM"
                       value="<?php echo isset($_POST['DiaChi']) ? htmlspecialchars($_POST['DiaChi']) : ''; ?>">
            </div>

            <div class="mb-3">
                <label for="DienThoai" class="form-label"><strong>Điện thoại:<span class="required-mark">*</span></strong></label>
                <input type="tel" class="form-control" id="DienThoai" name="DienThoai" placeholder="ví dụ: 09xxxxxxxx" required pattern="[0-9]{10,11}" title="Nhập số điện thoại hợp lệ (10-11 số)"
                       value="<?php echo isset($_POST['DienThoai']) ? htmlspecialchars($_POST['DienThoai']) : ''; ?>">
            </div>

            <div class="mb-3">
                <label for="Email" class="form-label"><strong>Email:</strong></label>
                <input type="email" class="form-control" id="Email" name="Email" placeholder="ví dụ: abc@example.com"
                       value="<?php echo isset($_POST['Email']) ? htmlspecialchars($_POST['Email']) : ''; ?>">
            </div>

            <input type="hidden" name="NgayDangKy" value="<?php echo date("Y-m-d H:i:s");?>">


             <div class="button-group">
                <button type="submit" name="Them" class="btn btn-success btn-lg">
                    <i class="fas fa-user-plus"></i> Thêm Người Dùng
                </button>
                 <a href="index_user.php" class="btn btn-secondary btn-lg">
                    <i class="fas fa-arrow-left"></i> Thoát
                </a>
            </div>

        </form>
    </div> </div> <script type="text/javascript">
    // Hàm xác nhận hủy (có thể không cần nếu nút thoát chỉ là link)
    // function getConfirmation(){ ... }

    // Hàm Hiện/Ẩn mật khẩu
    function togglePasswords() {
        var passInput = document.getElementById("Password");
        var confirmPassInput = document.getElementById("Password_1");
        var newType = passInput.type === "password" ? "text" : "password";
        passInput.type = newType;
        if (confirmPassInput) {
            confirmPassInput.type = newType;
        }
    }
</script>

<?php include_once('footer.php'); ?>
</body>
</html>