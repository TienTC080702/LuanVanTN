<?php
include_once('../connection/connect_database.php'); // Đảm bảo đường dẫn đúng

$user_data = null; // Biến lưu dữ liệu user
$error_load = null; // Lỗi tải dữ liệu
$error_message = null; // Lỗi xử lý form
$thongbao = ""; // Biến thông báo gốc của bạn

// --- LẤY DỮ LIỆU NGƯỜI DÙNG CẦN SỬA ---
if (!isset($_GET['idUser']) || !is_numeric($_GET['idUser'])) {
    die("Lỗi: ID Người dùng không hợp lệ.");
}
$idUser_get = intval($_GET['idUser']);

$sl = "SELECT * FROM Users WHERE idUser=" . $idUser_get;
$kq = mysqli_query($conn, $sl);

if (!$kq) {
    $error_load = "Lỗi truy vấn người dùng: " . mysqli_error($conn);
} else {
    // Dùng fetch_assoc thay vì fetch_array
    $user_data = mysqli_fetch_assoc($kq);
    if (!$user_data) {
        $error_load = "Không tìm thấy người dùng với ID=" . $idUser_get;
    }
    mysqli_free_result($kq);
}

// --- XỬ LÝ KHI SUBMIT FORM (LOGIC GỐC GIỮ NGUYÊN + SỬA NHỎ NGÀY ĐK) ---
if (isset($_POST['Sua'])) {
    // Kiểm tra các trường cơ bản có tồn tại không
    if (isset($_POST['Username'], $_POST['HoTenK'], $_POST['Password_old'], $_POST['Password'], $_POST['Password_1'], $_POST['DiaChi'], $_POST['DienThoai'], $_POST['Email']) && $user_data)
    {
        // Lấy dữ liệu từ POST (nên làm sạch kỹ hơn)
        $username_post = mysqli_real_escape_string($conn, $_POST['Username']);
        $hotenk_post = mysqli_real_escape_string($conn, $_POST['HoTenK']);
        $password_old_post = $_POST['Password_old'];
        $password_new_post = $_POST['Password'];
        $password_confirm_post = $_POST['Password_1'];
        $diachi_post = mysqli_real_escape_string($conn, $_POST['DiaChi']);
        $dienthoai_post = mysqli_real_escape_string($conn, $_POST['DienThoai']);
        $email_post = mysqli_real_escape_string($conn, $_POST['Email']);


        // --- Logic kiểm tra (Giữ nguyên của bạn) ---
        // Kiểm tra mật khẩu cũ
        if (md5($password_old_post) != $user_data['Password']) {
            $thongbao .= "Mật khẩu cũ không chính xác. ";
        }
        // Kiểm tra mật khẩu mới trùng khớp
        if (md5($password_new_post) != md5($password_confirm_post)) {
            $thongbao .= "Mật khẩu mới không trùng khớp. ";
        }
        // Kiểm tra trùng lặp (Inefficient)
        $query_check = mysqli_query($conn, "SELECT idUser, HoTen, Email, DienThoai FROM Users");
        if ($query_check) {
             while ($r = mysqli_fetch_assoc($query_check)) {
                // Kiểm tra trùng username (trừ user hiện tại)
                if ($r['HoTen'] == $username_post && $r['idUser'] != $idUser_get) {
                    $thongbao .= 'Username đã tồn tại. ';
                }
                // Kiểm tra trùng email (trừ user hiện tại, và email không rỗng)
                 if (!empty($email_post) && $r['Email'] == $email_post && $r['idUser'] != $idUser_get) {
                     $thongbao .= 'Email đã tồn tại. ';
                 }
                // Kiểm tra trùng điện thoại (trừ user hiện tại)
                 if ($r['DienThoai'] == $dienthoai_post && $r['idUser'] != $idUser_get) {
                    $thongbao .= 'Số điện thoại đã tồn tại. ';
                 }
             }
             mysqli_free_result($query_check);
        } else {
             $thongbao .= 'Lỗi kiểm tra dữ liệu trùng lặp. ';
        }

        // --- Xử lý kết quả kiểm tra ---
        if (trim($thongbao) != "") {
            $error_message = trim($thongbao);
            // echo "<script>alert('$thongbao');</script>"; // Bỏ alert
        } else {
            // --- Cập nhật thông tin (Vẫn dùng MD5 và ghép chuỗi theo code gốc) ---
            // *** Bỏ cập nhật NgayDangKy ra khỏi câu lệnh UPDATE ***
            $sl1 = "UPDATE Users SET
                        HoTen='" . $username_post . "',
                        HoTenK='" . $hotenk_post . "',
                        Password='" . md5($password_new_post) . "',
                        DiaChi='" . $diachi_post . "',
                        DienThoai='" . $dienthoai_post . "',
                        Email='" . $email_post . "'
                    WHERE idUser=" . $idUser_get; // Nên dùng WHERE idUser = $idUser_get

            $kq1 = mysqli_query($conn, $sl1);
            if ($kq1) {
                echo "<script>alert('Sửa thông tin người dùng thành công!'); location.href='index_user.php';</script>";
                exit();
            } else {
                 $error_message = "Lỗi khi cập nhật thông tin người dùng: " . mysqli_error($conn);
                 // echo "<script>alert('Sửa không thành công!');</script>"; // Bỏ alert
            }
        }
    } else {
        // Thông báo lỗi nếu thiếu dữ liệu POST hoặc không tìm thấy user ban đầu
        if (!$user_data) {
             $error_message = "Không thể xử lý vì không tải được dữ liệu người dùng.";
        } else {
            // $error_message = "Vui lòng nhập đầy đủ thông tin bắt buộc."; // Thông báo lỗi chung chung hơn
             // echo "<script>alert('Vui lòng nhập đầy đủ thông tin!!!');</script>"; // Bỏ alert
        }
    }
} // Đóng if submit

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once("header1.php"); ?>
    <title>Sửa Thông Tin Người Dùng</title>
    <?php include_once('header2.php'); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* CSS cho container form */
        .form-container {
            max-width: 800px; /* Tăng độ rộng một chút */
            margin: 30px auto;
            padding: 30px;
            background-color: #ffffff; /* Nền trắng */
            border-radius: 8px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        /* CSS cho tiêu đề */
        h3.form-title {
            color: #0d6efd; /* Màu xanh dương */
            text-align: center;
            margin-bottom: 2rem;
            font-weight: bold;
        }
        /* Căn giữa nút */
        .button-group {
            text-align: center;
            margin-top: 30px; /* Tăng khoảng cách trên nút */
        }
         .button-group .btn {
             margin: 0 10px;
             padding: 10px 25px;
         }
         .required-mark { color: red; margin-left: 2px; font-weight: bold;}
         /* Bỏ CSS nút tùy chỉnh cũ */
    </style>
</head>
<body>
<?php include_once('header3.php'); ?>

<div class="container mt-4">
    <div class="form-container">
        <h3 class="form-title">SỬA THÔNG TIN NGƯỜI DÙNG</h3>

        <?php
        // Hiển thị lỗi tải dữ liệu hoặc lỗi xử lý form
        if (!empty($error_load)) {
            echo "<div class='alert alert-danger text-center'><strong>Lỗi tải dữ liệu:</strong> " . htmlspecialchars($error_load) . "</div>";
        } elseif (!empty($error_message)) {
            echo "<div class='alert alert-danger text-center'><strong>Lỗi xử lý:</strong> " . htmlspecialchars($error_message) . "</div>";
        }

        // Chỉ hiển thị form nếu tải dữ liệu thành công
        if ($user_data):
        ?>

        <form method="post" action="sua_user.php?idUser=<?php echo $idUser_get; ?>" name="SuaUser"> <div class="mb-3">
                <label for="Username" class="form-label"><strong>Tên đăng nhập:<span class="required-mark">*</span></strong></label>
                <input type="text" class="form-control" id="Username" name="Username" required value="<?php echo htmlspecialchars($user_data['HoTen']); ?>">
            </div>

            <div class="mb-3">
                <label for="HoTenK" class="form-label"><strong>Họ và Tên:<span class="required-mark">*</span></strong></label>
                <input type="text" class="form-control" id="HoTenK" name="HoTenK" required value="<?php echo htmlspecialchars($user_data['HoTenK']); ?>">
            </div>

             <div class="mb-3">
                <label for="Password_old" class="form-label"><strong>Mật khẩu cũ:<span class="required-mark">*</span></strong></label>
                <input type="password" class="form-control" id="Password_old" name="Password_old" placeholder="Nhập mật khẩu hiện tại để xác nhận thay đổi" required>
                <div class="form-text">Cần nhập đúng mật khẩu cũ để lưu thay đổi (kể cả khi không đổi mật khẩu).</div>
            </div>

            <div class="mb-3">
                <label for="Password" class="form-label"><strong>Mật khẩu mới:<span class="required-mark">*</span></strong></label>
                <input type="password" class="form-control" id="Password" name="Password" placeholder="Nhập mật khẩu mới" required>
            </div>

            <div class="mb-3">
                <label for="Password_1" class="form-label"><strong>Nhập lại mật khẩu mới:<span class="required-mark">*</span></strong></label>
                <input type="password" class="form-control" id="Password_1" name="Password_1" placeholder="Nhập lại mật khẩu mới phía trên" required>
            </div>

             <div class="mb-3 form-check">
                 <input type="checkbox" class="form-check-input" id="showPasswordCheckbox" onclick="toggleNewPasswords()">
                 <label class="form-check-label" for="showPasswordCheckbox">Hiển thị mật khẩu mới</label>
             </div>

            <div class="mb-3">
                <label for="DiaChi" class="form-label"><strong>Địa chỉ:</strong></label>
                <input type="text" class="form-control" id="DiaChi" name="DiaChi" value="<?php echo htmlspecialchars($user_data['DiaChi']); ?>">
            </div>

            <div class="mb-3">
                <label for="DienThoai" class="form-label"><strong>Điện thoại:<span class="required-mark">*</span></strong></label>
                <input type="tel" class="form-control" id="DienThoai" name="DienThoai" required pattern="[0-9]{10,11}" title="Nhập số điện thoại hợp lệ (10-11 số)" value="<?php echo htmlspecialchars($user_data['DienThoai']); ?>">
            </div>

            <div class="mb-3">
                <label for="Email" class="form-label"><strong>Email:</strong></label>
                <input type="email" class="form-control" id="Email" name="Email" value="<?php echo htmlspecialchars($user_data['Email']); ?>">
            </div>

            <div class="mb-3">
                 <label class="form-label"><strong>Ngày Đăng Ký:</strong></label>
                 <p class="form-control-plaintext"><?php echo date('d/m/Y H:i:s', strtotime($user_data['NgayDangKy'])); ?></p>
                 </div>

             <div class="button-group">
                <button type="submit" name="Sua" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Lưu Thay Đổi
                </button>
                <a href="index_user.php" class="btn btn-secondary btn-lg"> <i class="fas fa-arrow-left"></i> Thoát
                </a>
            </div>

        </form>

        <?php
        endif; // Đóng if ($user_data)
        ?>

    </div> </div> <script type="text/javascript">
    function toggleNewPasswords() {
        var passInput = document.getElementById("Password"); // ID của ô mật khẩu mới
        var confirmPassInput = document.getElementById("Password_1"); // ID của ô nhập lại MK mới
        var newType = passInput.type === "password" ? "text" : "password";
        passInput.type = newType;
        if (confirmPassInput) {
            confirmPassInput.type = newType;
        }
        // Không thay đổi ô mật khẩu cũ
    }
</script>


<?php include_once('footer.php'); ?>
</body>
</html>